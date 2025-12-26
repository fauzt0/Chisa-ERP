<?php
/**
 * AlmacenModel - Modelo de gestión de almacén
 * 
 * Gestiona inventario de insumos y productos, movimientos,
 * y entregas de órdenes de venta y obras
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class AlmacenModel extends CI_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    // =====================================================
    // DASHBOARD Y ESTADÍSTICAS
    // =====================================================
    
    /**
     * Obtiene resumen de inventario
     */
    public function get_resumen_inventario() {
        $resumen = [];
        
        // Insumos
        $this->db->select('
            COUNT(*) as total_items,
            SUM(stock_actual * precio_promedio) as valor_total,
            SUM(CASE WHEN stock_actual <= stock_minimo THEN 1 ELSE 0 END) as stock_bajo
        ');
        $this->db->where('estatus', 'Activo');
        $insumos = $this->db->get('insumos')->row();
        
        $resumen['insumos'] = [
            'total_items' => $insumos->total_items ?? 0,
            'valor_total' => $insumos->valor_total ?? 0,
            'stock_bajo' => $insumos->stock_bajo ?? 0
        ];
        
        // Productos
        $this->db->select('
            COUNT(*) as total_items,
            SUM(stock_actual * precio_venta) as valor_total,
            SUM(CASE WHEN stock_actual <= stock_minimo THEN 1 ELSE 0 END) as stock_bajo
        ');
        $this->db->where('estatus', 'Activo');
        $productos = $this->db->get('productos')->row();
        
        $resumen['productos'] = [
            'total_items' => $productos->total_items ?? 0,
            'valor_total' => $productos->valor_total ?? 0,
            'stock_bajo' => $productos->stock_bajo ?? 0
        ];
        
        // Órdenes pendientes
        $this->db->where_in('estatus', ['Confirmada', 'En Preparación']);
        $resumen['ordenes_pendientes'] = $this->db->count_all_results('ordenes_venta');
        
        // Obras pendientes
        $this->db->where_in('estatus', ['Confirmada', 'En Proceso']);
        $resumen['obras_pendientes'] = $this->db->count_all_results('obras');
        
        return $resumen;
    }
    
    /**
     * Obtiene últimos movimientos
     */
    public function get_ultimos_movimientos($limit = 10) {
        $this->db->select('
            mp.id,
            mp.fecha_movimiento,
            mp.tipo_movimiento,
            mp.cantidad,
            mp.stock_anterior,
            mp.stock_nuevo,
            mp.motivo,
            p.codigo as producto_codigo,
            p.nombre as producto_nombre,
            u.nombre as usuario_nombre
        ');
        $this->db->from('movimientos_productos mp');
        $this->db->join('productos p', 'p.id = mp.producto_id');
        $this->db->join('administradores u', 'u.id = mp.usuario_id', 'left');
        $this->db->order_by('mp.fecha_movimiento', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }
    
    // =====================================================
    // INVENTARIO
    // =====================================================
    
    /**
     * Obtiene lista de insumos con stock
     */
    public function get_insumos($filtros = []) {
        $this->db->select('
            i.*,
            ci.nombre as categoria_nombre,
            CASE 
                WHEN i.stock_actual <= i.stock_minimo * 0.5 THEN "critico"
                WHEN i.stock_actual <= i.stock_minimo THEN "bajo"
                WHEN i.stock_maximo IS NOT NULL AND i.stock_actual >= i.stock_maximo THEN "exceso"
                ELSE "normal"
            END as nivel_stock
        ');
        $this->db->from('insumos i');
        $this->db->join('categorias_insumos ci', 'ci.id = i.categoria_id', 'left');
        $this->db->where('i.estatus', 'Activo');
        
        // Filtros
        if(!empty($filtros['categoria_id'])) {
            $this->db->where('i.categoria_id', $filtros['categoria_id']);
        }
        
        if(!empty($filtros['stock_bajo'])) {
            $this->db->where('i.stock_actual <=', 'i.stock_minimo', FALSE);
        }
        
        if(!empty($filtros['busqueda'])) {
            $this->db->group_start();
            $this->db->like('i.codigo', $filtros['busqueda']);
            $this->db->or_like('i.nombre_tecnico', $filtros['busqueda']);
            $this->db->group_end();
        }
        
        $this->db->order_by('i.nombre_tecnico', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene lista de productos con stock
     */
    public function get_productos($filtros = []) {
        $this->db->select('
            p.*,
            cp.nombre as categoria_nombre,
            CASE 
                WHEN p.stock_actual <= p.stock_minimo * 0.5 THEN "critico"
                WHEN p.stock_actual <= p.stock_minimo THEN "bajo"
                WHEN p.stock_maximo IS NOT NULL AND p.stock_actual >= p.stock_maximo THEN "exceso"
                ELSE "normal"
            END as nivel_stock
        ');
        $this->db->from('productos p');
        $this->db->join('categorias_productos cp', 'cp.id = p.categoria_id', 'left');
        $this->db->where('p.estatus', 'Activo');
        
        // Filtros
        if(!empty($filtros['categoria_id'])) {
            $this->db->where('p.categoria_id', $filtros['categoria_id']);
        }
        
        if(!empty($filtros['tipo_producto'])) {
            $this->db->where('p.tipo_producto', $filtros['tipo_producto']);
        }
        
        if(!empty($filtros['stock_bajo'])) {
            $this->db->where('p.stock_actual <=', 'p.stock_minimo', FALSE);
        }
        
        if(!empty($filtros['busqueda'])) {
            $this->db->group_start();
            $this->db->like('p.codigo', $filtros['busqueda']);
            $this->db->or_like('p.nombre', $filtros['busqueda']);
            $this->db->group_end();
        }
        
        $this->db->order_by('p.nombre', 'ASC');
        
        return $this->db->get()->result();
    }
    
    // =====================================================
    // ENTREGAS - ÓRDENES DE VENTA
    // =====================================================
    
    /**
     * Obtiene órdenes pendientes de entrega
     */
    public function get_ordenes_pendientes() {
        $this->db->select('
            ov.*,
            c.razon_social as cliente_nombre,
            COUNT(dov.id) as total_productos,
            SUM(dov.cantidad) as cantidad_total,
            SUM(dov.cantidad_entregada) as cantidad_entregada_total
        ');
        $this->db->from('ordenes_venta ov');
        $this->db->join('clientes c', 'c.id = ov.cliente_id');
        $this->db->join('detalle_orden_venta dov', 'dov.orden_venta_id = ov.id');
        $this->db->where_in('ov.estatus', ['Confirmada', 'En Preparación']);
        $this->db->group_by('ov.id');
        $this->db->order_by('ov.fecha_orden', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene detalle de orden con productos y stock
     */
    public function get_orden_detalle($orden_id) {
        // Datos de la orden
        $this->db->select('ov.*, c.razon_social as cliente_nombre');
        $this->db->from('ordenes_venta ov');
        $this->db->join('clientes c', 'c.id = ov.cliente_id');
        $this->db->where('ov.id', $orden_id);
        $orden = $this->db->get()->row();
        
        if(!$orden) {
            return null;
        }
        
        // Productos de la orden
        $this->db->select('
            dov.*,
            p.codigo as producto_codigo,
            p.nombre as producto_nombre,
            p.stock_actual,
            p.unidad_venta,
            (dov.cantidad - dov.cantidad_entregada) as pendiente_entregar
        ');
        $this->db->from('detalle_orden_venta dov');
        $this->db->join('productos p', 'p.id = dov.producto_id');
        $this->db->where('dov.orden_venta_id', $orden_id);
        $orden->productos = $this->db->get()->result();
        
        return $orden;
    }
    
    // =====================================================
    // ENTREGAS - OBRAS
    // =====================================================
    
    /**
     * Obtiene obras pendientes de entrega
     */
    public function get_obras_pendientes() {
        $this->db->select('
            o.*,
            c.razon_social as cliente_nombre,
            COUNT(op.id) as total_productos,
            SUM(op.cantidad_ajustada) as cantidad_total,
            SUM(COALESCE(op.cantidad_entregada, 0)) as cantidad_entregada_total
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id');
        $this->db->join('obras_productos op', 'op.obra_id = o.id');
        $this->db->where_in('o.estatus', ['Confirmada', 'En Proceso']);
        $this->db->group_by('o.id');
        $this->db->order_by('o.fecha_creacion', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene detalle de obra con productos y stock
     */
    public function get_obra_detalle($obra_id) {
        // Datos de la obra
        $this->db->select('o.*, c.razon_social as cliente_nombre');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id');
        $this->db->where('o.id', $obra_id);
        $obra = $this->db->get()->row();
        
        if(!$obra) {
            return null;
        }
        
        // Productos de la obra
        $this->db->select('
            op.*,
            op.cantidad_ajustada as cantidad,
            COALESCE(op.cantidad_entregada, 0) as cantidad_entregada,
            p.codigo as producto_codigo,
            p.nombre as producto_nombre,
            p.stock_actual,
            p.unidad_venta,
            (op.cantidad_ajustada - COALESCE(op.cantidad_entregada, 0)) as pendiente_entregar
        ');
        $this->db->from('obras_productos op');
        $this->db->join('productos p', 'p.id = op.producto_id');
        $this->db->where('op.obra_id', $obra_id);
        $obra->productos = $this->db->get()->result();
        
        return $obra;
    }
    
    // =====================================================
    // REGISTRAR ENTREGA
    // =====================================================
    
    /**
     * Registra una entrega (orden o obra)
     */
    public function registrar_entrega($data) {
        $this->db->trans_start();
        
        try {
            // Generar folio
            $this->db->query('CALL sp_generar_folio_entrega(@folio)');
            $folio = $this->db->query('SELECT @folio as folio')->row()->folio;
            
            // Crear entrega
            $entrega_data = [
                'folio' => $folio,
                'tipo_origen' => $data['tipo_origen'],
                'orden_venta_id' => $data['orden_venta_id'] ?? null,
                'obra_id' => $data['obra_id'] ?? null,
                'fecha_entrega' => date('Y-m-d H:i:s'),
                'usuario_id' => $data['usuario_id'],
                'observaciones' => $data['observaciones'] ?? null
            ];
            
            $this->db->insert('entregas_almacen', $entrega_data);
            $entrega_id = $this->db->insert_id();
            
            // Procesar productos
            foreach($data['productos'] as $producto) {
                if($producto['cantidad_entregar'] <= 0) continue;
                
                // Registrar movimiento de salida
                $movimiento_data = [
                    'producto_id' => $producto['producto_id'],
                    'tipo_movimiento' => 'Salida',
                    'cantidad' => $producto['cantidad_entregar'],
                    'motivo' => $data['tipo_origen'] == 'Orden Venta' ? 
                        'Entrega de orden ' . $folio : 
                        'Entrega de obra ' . $folio,
                    'usuario_id' => $data['usuario_id'],
                    'referencia_tipo' => 'Entrega',
                    'referencia_id' => $entrega_id
                ];
                
                // El modelo de productos maneja el registro
                $this->load->model('Produccion/ProductosModel');
                $result = $this->ProductosModel->registrar_movimiento($movimiento_data);
                
                if(!$result['success']) {
                    throw new Exception($result['message']);
                }
                
                $movimiento_id = $this->db->insert_id();
                
                // Registrar detalle de entrega
                $detalle_data = [
                    'entrega_id' => $entrega_id,
                    'tipo_detalle' => $data['tipo_origen'],
                    'detalle_orden_id' => $producto['detalle_orden_id'] ?? null,
                    'obra_producto_id' => $producto['obra_producto_id'] ?? null,
                    'producto_id' => $producto['producto_id'],
                    'cantidad_entregada' => $producto['cantidad_entregar'],
                    'movimiento_id' => $movimiento_id
                ];
                
                $this->db->insert('detalle_entregas_almacen', $detalle_data);
                // El trigger actualiza cantidad_entregada y estatus automáticamente
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return ['success' => false, 'message' => 'Error al registrar entrega'];
            }
            
            return ['success' => true, 'message' => 'Entrega registrada correctamente', 'folio' => $folio];
            
        } catch(Exception $e) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Obtiene historial de entregas
     */
    public function get_historial_entregas($filtros = []) {
        $this->db->select('
            ea.*,
            CASE 
                WHEN ea.tipo_origen = "Orden Venta" THEN ov.folio
                WHEN ea.tipo_origen = "Obra" THEN o.folio
            END as origen_folio,
            CASE 
                WHEN ea.tipo_origen = "Orden Venta" THEN c1.razon_social
                WHEN ea.tipo_origen = "Obra" THEN c2.razon_social
            END as cliente_nombre,
            u.nombre as usuario_nombre
        ');
        $this->db->from('entregas_almacen ea');
        $this->db->join('ordenes_venta ov', 'ov.id = ea.orden_venta_id', 'left');
        $this->db->join('obras o', 'o.id = ea.obra_id', 'left');
        $this->db->join('clientes c1', 'c1.id = ov.cliente_id', 'left');
        $this->db->join('clientes c2', 'c2.id = o.cliente_id', 'left');
        $this->db->join('administradores u', 'u.id = ea.usuario_id', 'left');
        
        if(!empty($filtros['tipo_origen'])) {
            $this->db->where('ea.tipo_origen', $filtros['tipo_origen']);
        }
        
        if(!empty($filtros['fecha_desde'])) {
            $this->db->where('DATE(ea.fecha_entrega) >=', $filtros['fecha_desde']);
        }
        
        if(!empty($filtros['fecha_hasta'])) {
            $this->db->where('DATE(ea.fecha_entrega) <=', $filtros['fecha_hasta']);
        }
        
        $this->db->order_by('ea.fecha_entrega', 'DESC');
        
        return $this->db->get()->result();
    }
}
