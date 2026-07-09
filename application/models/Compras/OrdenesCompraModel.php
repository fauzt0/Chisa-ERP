<?php
/**
 * OrdenesCompraModel - Modelo de gestión de órdenes de compra
 * 
 * Gestiona órdenes de compra con detalles, workflow de estatus e integración con inventario
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class OrdenesCompraModel extends MY_Model {
    
    protected $tableName = 'ordenes_compra';
    
    // Configuración para DataTables
    protected $datatableConfig = [
        'table' => 'ordenes_compra',
        'column_order' => ['ordenes_compra.folio', 'ordenes_compra.fecha_orden', 'proveedores.razon_social', 'ordenes_compra.total', 'ordenes_compra.estatus', null],
        'column_search' => ['ordenes_compra.folio', 'proveedores.razon_social', 'proveedores.rfc'],
        'order' => ['fecha_orden' => 'DESC']
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Override de _get_datatables_query para agregar join con proveedores
     */
    protected function _get_datatables_query() {
        $this->db->select('ordenes_compra.*, proveedores.razon_social');
        $this->db->from($this->tableName);
        $this->db->join('proveedores', 'proveedores.id = ordenes_compra.proveedor_id', 'left');
        
        // Búsqueda
        $i = 0;
        if(isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
            foreach ($this->datatableConfig['column_search'] as $column) {
                if($i === 0) {
                    $this->db->group_start();
                    $this->db->like($column, $_POST['search']['value']);
                } else {
                    $this->db->or_like($column, $_POST['search']['value']);
                }
                
                if(count($this->datatableConfig['column_search']) - 1 == $i) {
                    $this->db->group_end();
                }
                $i++;
            }
        }
        
        // Ordenamiento
        if(isset($_POST['order'])) {
            $column_index = $_POST['order'][0]['column'];
            $column_name = $this->datatableConfig['column_order'][$column_index];
            $this->db->order_by($column_name, $_POST['order'][0]['dir']);
        } elseif (isset($this->datatableConfig['order'])) {
            $order = $this->datatableConfig['order'];
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }
    
    /**
     * Override de get_datatables
     */
    public function get_datatables() {
        $this->_get_datatables_query();
        if(isset($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * Override de count_filtered
     */
    public function count_filtered() {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }
    
    /**
     * Override de count_all
     */
    public function count_all($where = []) {
        $this->db->from($this->tableName);
        return $this->db->count_all_results();
    }
    
    /**
     * Obtiene una orden completa con detalles
     */
    public function get_orden($id) {
        $this->db->select('ordenes_compra.*, proveedores.razon_social, proveedores.nombre_comercial,
            proveedores.rfc AS rfc_proveedor, proveedores.telefono AS telefono_proveedor,
            proveedores.email AS email_proveedor, proveedores.direccion AS direccion_proveedor,
            proveedores.ciudad AS ciudad_proveedor, proveedores.estado AS estado_proveedor,
            proveedores.codigo_postal AS cp_proveedor');
        $this->db->from($this->tableName);
        $this->db->join('proveedores', 'proveedores.id = ordenes_compra.proveedor_id', 'left');
        $this->db->where('ordenes_compra.id', $id);
        $orden = $this->db->get()->row();
        
        if($orden) {
            $orden->detalles = $this->get_detalles($id);
        }
        
        return $orden;
    }
    
    /**
     * Obtiene detalles de una orden
     */
    public function get_detalles($orden_id) {
        $this->db->select('detalle_orden_compra.*, insumos.codigo, insumos.nombre_tecnico, insumos.unidad_medida, detalle_orden_compra.nombre_proveedor, detalle_orden_compra.codigo_proveedor');
        $this->db->from('detalle_orden_compra');
        $this->db->join('insumos', 'insumos.id = detalle_orden_compra.insumo_id');
        $this->db->where('detalle_orden_compra.orden_compra_id', $orden_id);
        $this->db->order_by('detalle_orden_compra.id', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Crea una nueva orden de compra
     */
    public function crear_orden($data) {
        // Generar folio si no existe
        if(empty($data['folio'])) {
            $data['folio'] = $this->generar_folio();
        }
        
        // Datos por defecto
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        $data['estatus'] = 'Borrador';
        
        return $this->db->insert($this->tableName, $data);
    }
    
    /**
     * Actualiza una orden de compra
     */
    public function actualizar_orden($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, $data);
    }
    
    /**
     * Elimina una orden (solo si está en Borrador)
     */
    public function eliminar_orden($id) {
        // Verificar estatus
        $orden = $this->get_orden($id);
        if(!$orden) {
            return ['success' => false, 'message' => 'Orden no encontrada'];
        }
        
        if($orden->estatus != 'Borrador') {
            return ['success' => false, 'message' => 'Solo se pueden eliminar órdenes en estado Borrador'];
        }
        
        // Eliminar detalles (cascade automático)
        $this->db->where('id', $id);
        $result = $this->db->delete($this->tableName);
        
        return ['success' => $result, 'message' => $result ? 'Orden eliminada' : 'Error al eliminar'];
    }
    
    /**
     * Agrega un detalle a la orden
     */
    public function agregar_detalle($orden_id, $data) {
        $data['orden_compra_id'] = $orden_id;
        
        // El trigger calculará el subtotal automáticamente
        $result = $this->db->insert('detalle_orden_compra', $data);
        
        if($result) {
            // Recalcular totales de la orden
            $this->recalcular_totales($orden_id);
        }
        
        return $result;
    }
    
    /**
     * Actualiza un detalle
     */
    public function actualizar_detalle($id, $data) {
        $this->db->where('id', $id);
        $result = $this->db->update('detalle_orden_compra', $data);
        
        if($result) {
            // Obtener orden_id para recalcular
            $detalle = $this->db->where('id', $id)->get('detalle_orden_compra')->row();
            if($detalle) {
                $this->recalcular_totales($detalle->orden_compra_id);
            }
        }
        
        return $result;
    }
    
    /**
     * Elimina un detalle
     */
    public function eliminar_detalle($id) {
        // Obtener orden_id antes de eliminar
        $detalle = $this->db->where('id', $id)->get('detalle_orden_compra')->row();
        
        $this->db->where('id', $id);
        $result = $this->db->delete('detalle_orden_compra');
        
        if($result && $detalle) {
            $this->recalcular_totales($detalle->orden_compra_id);
        }
        
        return $result;
    }
    
    /**
     * Recalcula totales de una orden
     */
    public function recalcular_totales($orden_id) {
        $this->db->select('SUM(subtotal) as subtotal_total');
        $this->db->where('orden_compra_id', $orden_id);
        $result = $this->db->get('detalle_orden_compra')->row();
        
        $subtotal = $result->subtotal_total ?? 0;
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;
        
        $this->db->where('id', $orden_id);
        $this->db->update($this->tableName, [
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total
        ]);
    }
    
    /**
     * Cambia el estatus de una orden
     */
    public function cambiar_estatus($id, $nuevo_estatus, $user_id = null) {
        $orden = $this->get_orden($id);
        if(!$orden) {
            return ['success' => false, 'message' => 'Orden no encontrada'];
        }
        
        $data = ['estatus' => $nuevo_estatus];
        
        // Si se aprueba, registrar usuario y fecha
        if($nuevo_estatus == 'Enviada' && $orden->estatus == 'Borrador') {
            $data['aprobado_por'] = $user_id;
            $data['fecha_aprobacion'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where('id', $id);
        $result = $this->db->update($this->tableName, $data);
        
        return ['success' => $result, 'message' => $result ? 'Estatus actualizado' : 'Error al actualizar'];
    }
    
    /**
     * Recibe mercancía y actualiza inventario
     */
    public function recibir_mercancia($orden_id, $detalles_recibidos, $user_id = null) {
        $orden = $this->get_orden($orden_id);
        if(!$orden) {
            return ['success' => false, 'message' => 'Orden no encontrada'];
        }
        
        $this->db->trans_start();
        
        $todo_recibido = true;
        
        foreach($detalles_recibidos as $detalle) {
            $detalle_id = $detalle['detalle_id'];
            $cantidad_recibida = $detalle['cantidad_recibida'];
            
            // Obtener detalle actual
            $detalle_actual = $this->db->where('id', $detalle_id)->get('detalle_orden_compra')->row();
            
            if(!$detalle_actual) continue;
            
            // Actualizar cantidad recibida
            $nueva_cantidad_recibida = $detalle_actual->cantidad_recibida + $cantidad_recibida;
            
            $this->db->where('id', $detalle_id);
            $this->db->update('detalle_orden_compra', [
                'cantidad_recibida' => $nueva_cantidad_recibida
            ]);
            
            // Verificar si todo está recibido
            if($nueva_cantidad_recibida < $detalle_actual->cantidad_solicitada) {
                $todo_recibido = false;
            }
            
            // Obtener insumo actual
            $insumo = $this->db->where('id', $detalle_actual->insumo_id)->get('insumos')->row();
            
            // Crear movimiento de inventario (Polimórfico: Insumos)
            $movimiento = [
                'insumo_id' => $detalle_actual->insumo_id,
                'producto_id' => null, // Explicitly null
                'tipo_movimiento' => 'Entrada',
                'cantidad' => $cantidad_recibida,
                'stock_anterior' => $insumo->stock_actual,
                'stock_nuevo' => $insumo->stock_actual + $cantidad_recibida,
                'costo_unitario' => $detalle_actual->precio_unitario,
                'costo_total' => $cantidad_recibida * $detalle_actual->precio_unitario,
                'orden_compra_id' => $orden_id,
                'motivo' => 'Recepción de orden de compra ' . $orden->folio,
                'usuario_id' => $user_id,
                'fecha_movimiento' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('movimientos_inventario', $movimiento);
            
            // El trigger actualiza stock_actual automáticamente
        }
        
        // Actualizar estatus de la orden
        $nuevo_estatus = $todo_recibido ? 'Recibida' : 'Recibida Parcial';
        $this->db->where('id', $orden_id);
        $this->db->update($this->tableName, [
            'estatus' => $nuevo_estatus,
            'fecha_entrega_real' => date('Y-m-d')
        ]);
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Error al recibir mercancía'];
        }
        
        return ['success' => true, 'message' => 'Mercancía recibida correctamente'];
    }
    
    /**
     * Genera folio único para orden
     */
    private function generar_folio() {
        $year = date('Y');
        $prefijo = 'OC-' . $year . '-';
        
        $this->db->select('folio');
        $this->db->from($this->tableName);
        $this->db->like('folio', $prefijo, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $ultimo = $this->db->get()->row();
        
        if($ultimo) {
            $numero = intval(substr($ultimo->folio, strlen($prefijo))) + 1;
        } else {
            $numero = 1;
        }
        
        return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Obtiene órdenes de un proveedor
     */
    public function get_ordenes_proveedor($proveedor_id) {
        $this->db->where('proveedor_id', $proveedor_id);
        $this->db->order_by('fecha_orden', 'DESC');
        return $this->db->get($this->tableName)->result();
    }

    /**
     * Historial paginado de órdenes de compra de un proveedor
     */
    public function get_historial_ordenes($proveedor_id, $limit = 10, $offset = 0) {
        $this->db->select('id, folio, fecha_orden, total, estatus');
        $this->db->from($this->tableName);
        $this->db->where('proveedor_id', $proveedor_id);
        $this->db->order_by('fecha_orden', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    /**
     * Cuenta el historial de órdenes de compra de un proveedor
     */
    public function count_historial_ordenes($proveedor_id) {
        $this->db->where('proveedor_id', $proveedor_id);
        return $this->db->count_all_results($this->tableName);
    }
    
    /**
     * Obtiene estadísticas de órdenes
     */
    public function get_estadisticas() {
        $stats = [];
        
        // Total de órdenes
        $stats['total_ordenes'] = $this->db->count_all_results($this->tableName);
        
        // Órdenes pendientes (Enviada, Confirmada, En Tránsito)
        $this->db->where_in('estatus', ['Enviada', 'Confirmada', 'En Tránsito']);
        $stats['ordenes_pendientes'] = $this->db->count_all_results($this->tableName);
        
        // Órdenes recibidas este mes
        $this->db->where('estatus', 'Recibida');
        $this->db->where('MONTH(fecha_entrega_real)', date('m'));
        $this->db->where('YEAR(fecha_entrega_real)', date('Y'));
        $stats['recibidas_mes'] = $this->db->count_all_results($this->tableName);
        
        // Total gastado este mes
        $this->db->select('SUM(total) as total_mes');
        $this->db->where('MONTH(fecha_orden)', date('m'));
        $this->db->where('YEAR(fecha_orden)', date('Y'));
        $this->db->where_in('estatus', ['Enviada', 'Confirmada', 'En Tránsito', 'Recibida Parcial', 'Recibida']);
        $result = $this->db->get($this->tableName)->row();
        $stats['total_mes'] = $result->total_mes ?? 0;
        
        return $stats;
    }
}
