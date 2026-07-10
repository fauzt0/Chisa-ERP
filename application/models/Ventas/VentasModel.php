<?php
/**
 * VentasModel - Modelo de gestión de órdenes de venta
 * 
 * Gestiona órdenes de venta, cotizaciones y punto de venta
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class VentasModel extends MY_Model {
    
    protected $tableName = 'ordenes_venta';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Genera folio único para orden de venta
     */
    public function generar_folio() {
        $this->db->query("CALL sp_generar_folio_orden_venta(@nuevo_folio)");
        $result = $this->db->query("SELECT @nuevo_folio as folio")->row();
        return $result->folio;
    }
    
    /**
     * Crea una nueva orden de venta
     */
    public function crear_orden($data) {
        $data['folio'] = $this->generar_folio();
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        
        $this->db->insert('ordenes_venta', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Agrega detalle a una orden
     */
    public function agregar_detalle($orden_id, $detalles) {
        foreach($detalles as $detalle) {
            $detalle['orden_venta_id'] = $orden_id;
            
            // Obtener stock actual del producto
            $this->db->select('stock_actual');
            $this->db->where('id', $detalle['producto_id']);
            $producto = $this->db->get('productos')->row();
            
            $detalle['stock_disponible_al_crear'] = $producto ? $producto->stock_actual : 0;
            
            // Verificar si requiere producción
            if($producto && $producto->stock_actual < $detalle['cantidad']) {
                $detalle['requiere_produccion'] = true;
            }
            
            $this->db->insert('detalle_orden_venta', $detalle);
        }
        
        return true;
    }
    
    /**
     * Obtiene una orden con su detalle
     */
    public function get_orden_completa($id) {
        $this->db->select('ordenes_venta.*, clientes.razon_social, clientes.rfc');
        $this->db->from('ordenes_venta');
        $this->db->join('clientes', 'clientes.id = ordenes_venta.cliente_id');
        $this->db->where('ordenes_venta.id', $id);
        $orden = $this->db->get()->row();
        
        if($orden) {
            // Obtener detalle con formulación
            $select_fields = 'detalle_orden_venta.*, productos.nombre, productos.codigo';
            
            // Verificar si existen las columnas de formulación
            if($this->db->field_exists('formulacion_id', 'detalle_orden_venta')) {
                $select_fields .= ', detalle_orden_venta.formulacion_id, detalle_orden_venta.formulacion_version';
            }
            
            $this->db->select($select_fields);
            $this->db->from('detalle_orden_venta');
            $this->db->join('productos', 'productos.id = detalle_orden_venta.producto_id');
            $this->db->where('detalle_orden_venta.orden_venta_id', $id);
            $orden->detalles = $this->db->get()->result();

            if ($this->db->field_exists('orden_venta_id', 'obras')) {
                $this->load->model('Obras/ObrasModel');
                $orden->obra = $this->ObrasModel->get_obra_por_orden_venta($id);
            }
        }
        
        return $orden;
    }
    
    /**
     * Confirma una orden (cambia de Cotización a Confirmada)
     */
    public function confirmar_orden($id) {
        // Verificar si requiere producción
        $this->db->where('orden_venta_id', $id);
        $this->db->where('requiere_produccion', true);
        $requiere_produccion = $this->db->count_all_results('detalle_orden_venta') > 0;
        
        $data = [
            'estatus' => $requiere_produccion ? 'En Preparación' : 'Confirmada',
            'requiere_produccion' => $requiere_produccion
        ];
        
        $this->db->where('id', $id);
        $this->db->update('ordenes_venta', $data);
        
        // Si requiere producción, crear solicitudes
        if($requiere_produccion) {
            $this->crear_solicitudes_produccion($id);
        }
        
        return true;
    }
    
    /**
     * Crea solicitudes de producción para productos sin stock
     */
    private function crear_solicitudes_produccion($orden_id) {
        $this->db->select('detalle_orden_venta.*, ordenes_venta.fecha_entrega_estimada');
        $this->db->from('detalle_orden_venta');
        $this->db->join('ordenes_venta', 'ordenes_venta.id = detalle_orden_venta.orden_venta_id');
        $this->db->where('detalle_orden_venta.orden_venta_id', $orden_id);
        $this->db->where('detalle_orden_venta.requiere_produccion', true);
        $detalles = $this->db->get()->result();
        
        foreach($detalles as $detalle) {
            // Generar folio de solicitud
            $this->db->query("CALL sp_generar_folio_solicitud_produccion(@nuevo_folio)");
            $result = $this->db->query("SELECT @nuevo_folio as folio")->row();
            
            $cantidad_faltante = $detalle->cantidad - $detalle->stock_disponible_al_crear;
            
            $solicitud = [
                'folio' => $result->folio,
                'orden_venta_id' => $orden_id,
                'producto_id' => $detalle->producto_id,
                'formulacion_id' => $detalle->formulacion_id ?? null, // Propagar formulación seleccionada
                'cantidad_solicitada' => $cantidad_faltante,
                'fecha_solicitud' => date('Y-m-d'),
                'fecha_requerida' => $detalle->fecha_entrega_estimada,
                'estatus' => 'Pendiente',
                'prioridad' => 'Media'
            ];
            
            $this->db->insert('solicitudes_produccion', $solicitud);
        }
    }
    
    /**
     * Entrega una orden (descuenta stock)
     */
    public function entregar_orden($id) {
        // Obtener detalles
        $this->db->where('orden_venta_id', $id);
        $detalles = $this->db->get('detalle_orden_venta')->result();
        
        foreach($detalles as $detalle) {
            // Descontar stock
            $this->db->set('stock_actual', 'stock_actual - ' . $detalle->cantidad, FALSE);
            $this->db->where('id', $detalle->producto_id);
            $this->db->update('productos');
            
            // Registrar movimiento de inventario
            $movimiento = [
                'producto_id' => $detalle->producto_id,
                'tipo_movimiento' => 'Salida',
                'cantidad' => $detalle->cantidad,
                'motivo' => 'Venta - Orden ' . $id,
                'fecha_movimiento' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('movimientos_inventario', $movimiento);
        }
        
        // Actualizar orden
        $data = [
            'estatus' => 'Entregada',
            'fecha_entrega_real' => date('Y-m-d')
        ];
        
        $this->db->where('id', $id);
        return $this->db->update('ordenes_venta', $data);
    }
    
    /**
     * Obtiene productos para el POS
     */
    public function get_productos_pos($busqueda = '', $categoria_id = null) {
        $this->db->select('productos.*, categorias_productos.nombre as categoria_nombre');
        $this->db->from('productos');
        $this->db->join('categorias_productos', 'categorias_productos.id = productos.categoria_id', 'left');
        $this->db->where('productos.estatus', 'Activo');
        
        if($busqueda) {
            $this->db->group_start();
            $this->db->like('productos.nombre', $busqueda);
            $this->db->or_like('productos.codigo', $busqueda);
            $this->db->or_like('productos.codigo_barras', $busqueda);
            $this->db->or_like('productos.alias', $busqueda);
            $this->db->group_end();
        }
        
        if($categoria_id) {
            $this->db->where('productos.categoria_id', $categoria_id);
        }
        
        $this->db->order_by('productos.nombre', 'ASC');
        $this->db->limit(50);
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene estadísticas de ventas
     */
    public function get_estadisticas() {
        $stats = [];
        
        // Ventas del día
        $this->db->select('COUNT(*) as total, SUM(total) as monto');
        $this->db->where('DATE(fecha_orden)', date('Y-m-d'));
        $this->db->where('estatus !=', 'Cancelada');
        $hoy = $this->db->get('ordenes_venta')->row();
        $stats['ventas_hoy'] = $hoy->total ?? 0;
        $stats['monto_hoy'] = $hoy->monto ?? 0;

        // Ventas dia anterior (para progreso)
        $this->db->select('COUNT(*) as total, SUM(total) as monto');
        $this->db->where('DATE(fecha_orden)', date('Y-m-d', strtotime('-1 day')));
        $this->db->where('estatus !=', 'Cancelada');
        $ayer = $this->db->get('ordenes_venta')->row();
        $stats['ventas_ayer'] = $ayer->total ?? 0;
        $stats['monto_ayer'] = $ayer->monto ?? 0;
        
        // Porcentaje dia vs ayer
        $stats['porcentaje_hoy'] = ($stats['monto_ayer'] > 0) 
            ? min(100, round(($stats['monto_hoy'] / $stats['monto_ayer']) * 100)) 
            : 100;

        // Ventas del mes
        $this->db->select('COUNT(*) as total, SUM(total) as monto');
        $this->db->where('MONTH(fecha_orden)', date('m'));
        $this->db->where('YEAR(fecha_orden)', date('Y'));
        $this->db->where('estatus !=', 'Cancelada');
        $mes = $this->db->get('ordenes_venta')->row();
        $stats['ventas_mes'] = $mes->total ?? 0;
        $stats['monto_mes'] = $mes->monto ?? 0;

        // Ventas mes anterior (para progreso)
        $this->db->select('COUNT(*) as total, SUM(total) as monto');
        $this->db->where('MONTH(fecha_orden)', date('m', strtotime('first day of last month')));
        $this->db->where('YEAR(fecha_orden)', date('Y', strtotime('first day of last month')));
        $this->db->where('estatus !=', 'Cancelada');
        $mes_ant = $this->db->get('ordenes_venta')->row();
        $stats['monto_mes_anterior'] = $mes_ant->monto ?? 0;

         // Porcentaje mes vs mes anterior (ajustado a dias transcurridos aprox o simple raw)
         // Simple raw para meta de superar mes anterior
        $stats['porcentaje_mes'] = ($stats['monto_mes_anterior'] > 0) 
             ? min(100, round(($stats['monto_mes'] / $stats['monto_mes_anterior']) * 100)) 
             : 100;
        
        // Cotizaciones pendientes
        $this->db->where('estatus', 'Cotización');
        $stats['cotizaciones_pendientes'] = $this->db->count_all_results('ordenes_venta');

        // Total Ordenes Activas (para % de cotizaciones vs total)
        $this->db->where_in('estatus', ['Cotización', 'Confirmada', 'En Preparación']);
        $total_activas = $this->db->count_all_results('ordenes_venta');
        
        $stats['porcentaje_cotizaciones'] = ($total_activas > 0) 
            ? round(($stats['cotizaciones_pendientes'] / $total_activas) * 100)
            : 0;
        
        // Órdenes en preparación
        $this->db->where('estatus', 'En Preparación');
        $stats['ordenes_preparacion'] = $this->db->count_all_results('ordenes_venta');

        $stats['porcentaje_preparacion'] = ($total_activas > 0) 
            ? round(($stats['ordenes_preparacion'] / $total_activas) * 100)
            : 0;
        
        return $stats;
    }

    /**
     * Obtiene los productos más vendidos
     */
    public function get_top_productos($limit = 3) {
        $this->db->select('p.id, p.nombre, p.codigo, p.precio_venta, p.stock_actual, p.codigo_barras, COUNT(dov.id) as ventas');
        $this->db->from('productos p');
        $this->db->join('detalle_orden_venta dov', 'dov.producto_id = p.id', 'left');
        $this->db->where('p.estatus', 'Activo');
        $this->db->group_by('p.id');
        $this->db->order_by('ventas', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Obtiene las últimas órdenes para el dashboard principal
     */
    public function get_ultimas_ordenes($limit = 5) {
        $this->db->select('
            ov.id,
            ov.folio,
            ov.fecha_creacion,
            ov.estatus,
            ov.total,
            c.razon_social as cliente
        ');
        $this->db->from('ordenes_venta ov');
        $this->db->join('clientes c', 'c.id = ov.cliente_id', 'left');
        $this->db->order_by('ov.fecha_creacion', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Obtiene ventas mensuales del año actual para graficar
     */
    public function get_ventas_mensuales_anio() {
        $anio = date('Y');
        $query = $this->db->query("
            SELECT 
                MONTH(fecha_orden) as mes,
                SUM(total) as total
            FROM ordenes_venta
            WHERE YEAR(fecha_orden) = ?
            AND estatus != 'Cancelada'
            GROUP BY MONTH(fecha_orden)
            ORDER BY mes ASC
        ", [$anio]);
        
        $resultados = $query->result();
        
        // Formatear array con 12 meses inicializados en 0
        $datos_mensuales = array_fill(1, 12, 0);
        
        foreach($resultados as $fila) {
            $datos_mensuales[$fila->mes] = (float)$fila->total;
        }
        
        return array_values($datos_mensuales); // Retornar indexado desde 0 para JS
    }
}
