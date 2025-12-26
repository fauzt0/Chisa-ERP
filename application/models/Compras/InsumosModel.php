<?php
/**
 * InsumosModel - Modelo de gestión de insumos
 * 
 * Gestiona catálogo de insumos con control de stock
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class InsumosModel extends MY_Model {
    
    protected $tableName = 'insumos';
    
    // Configuración para DataTables (sin join, se hace en los métodos)
    protected $datatableConfig = [
        'table' => 'insumos',
        'column_order' => ['codigo', 'nombre_tecnico', 'categoria_nombre', 'marca', 'unidad_medida', 'stock_actual', 'precio_promedio', 'estatus', null],
        'column_search' => ['codigo', 'nombre_tecnico', 'alias', 'marca'],
        'order' => ['nombre_tecnico' => 'ASC']
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Override de _get_datatables_query para agregar join
     */
    /**
     * Override de _get_datatables_query para agregar join
     */
    protected function _get_datatables_query() {
        $this->db->select('insumos.*, categorias_insumos.nombre as categoria_nombre');
        $this->db->from($this->tableName);
        $this->db->join('categorias_insumos', 'categorias_insumos.id = insumos.categoria_id', 'left');
        
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
     * Obtiene todos los insumos con filtros
     */
    public function get_all_insumos($filtros = []) {
        $this->db->select('insumos.*, categorias_insumos.nombre as categoria_nombre');
        $this->db->from($this->tableName);
        $this->db->join('categorias_insumos', 'categorias_insumos.id = insumos.categoria_id', 'left');
        
        // Aplicar filtros
        if(!empty($filtros['categoria_id'])) {
            $this->db->where('insumos.categoria_id', $filtros['categoria_id']);
        }
        
        if(!empty($filtros['estatus'])) {
            $this->db->where('insumos.estatus', $filtros['estatus']);
        }
        
        if(isset($filtros['stock_bajo']) && $filtros['stock_bajo'] == '1') {
            $this->db->where('insumos.stock_actual <=', 'insumos.stock_minimo', FALSE);
        }
        
        if(!empty($filtros['busqueda'])) {
            $this->db->group_start();
            $this->db->like('insumos.nombre_tecnico', $filtros['busqueda']);
            $this->db->or_like('insumos.alias', $filtros['busqueda']);
            $this->db->or_like('insumos.codigo', $filtros['busqueda']);
            $this->db->or_like('insumos.marca', $filtros['busqueda']);
            $this->db->group_end();
        }
        
        $this->db->order_by('insumos.nombre_tecnico', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene un insumo por ID
     */
    public function get_insumo($id) {
        $this->db->select('insumos.*, categorias_insumos.nombre as categoria_nombre');
        $this->db->from($this->tableName);
        $this->db->join('categorias_insumos', 'categorias_insumos.id = insumos.categoria_id', 'left');
        $this->db->where('insumos.id', $id);
        return $this->db->get()->row();
    }
    
    /**
     * Crea un nuevo insumo
     */
    public function crear_insumo($data) {
        // Generar código si no existe
        if(empty($data['codigo'])) {
            $data['codigo'] = $this->generar_codigo();
        }
        
        return $this->db->insert($this->tableName, $data);
    }
    
    /**
     * Actualiza un insumo
     */
    public function actualizar_insumo($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, $data);
    }
    
    /**
     * Elimina un insumo (validar dependencias)
     */
    public function eliminar_insumo($id) {
        // Verificar que no tenga órdenes de compra
        $this->db->where('insumo_id', $id);
        if($this->db->count_all_results('detalle_orden_compra') > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: tiene órdenes de compra asociadas'];
        }
        
        // Verificar que no tenga movimientos de inventario
        $this->db->where('insumo_id', $id);
        if($this->db->count_all_results('movimientos_inventario') > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: tiene movimientos de inventario'];
        }
        
        $this->db->where('id', $id);
        $result = $this->db->delete($this->tableName);
        
        return ['success' => $result, 'message' => $result ? 'Insumo eliminado' : 'Error al eliminar'];
    }
    
    /**
     * Obtiene insumos con stock bajo
     */
    public function get_insumos_stock_bajo() {
        $this->db->select('insumos.*, categorias_insumos.nombre as categoria_nombre');
        $this->db->from($this->tableName);
        $this->db->join('categorias_insumos', 'categorias_insumos.id = insumos.categoria_id', 'left');
        $this->db->where('insumos.stock_actual <=', 'insumos.stock_minimo', FALSE);
        $this->db->where('insumos.estatus', 'Activo');
        $this->db->order_by('insumos.stock_actual', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene insumos por categoría
     */
    public function get_insumos_por_categoria($categoria_id) {
        $this->db->where('categoria_id', $categoria_id);
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('nombre_tecnico', 'ASC');
        return $this->db->get($this->tableName)->result();
    }
    
    /**
     * Actualiza stock de un insumo
     */
    public function actualizar_stock($id, $cantidad, $tipo = 'ajuste') {
        $insumo = $this->get_insumo($id);
        if(!$insumo) {
            return false;
        }
        
        $nuevo_stock = $insumo->stock_actual + $cantidad;
        
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, ['stock_actual' => $nuevo_stock]);
    }
    
    /**
     * Genera código único para insumo
     */
    private function generar_codigo() {
        $prefijo = 'INS';
        $this->db->select('codigo');
        $this->db->from($this->tableName);
        $this->db->like('codigo', $prefijo, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $ultimo = $this->db->get()->row();
        
        if($ultimo) {
            $numero = intval(substr($ultimo->codigo, strlen($prefijo))) + 1;
        } else {
            $numero = 1;
        }
        
        return $prefijo . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Obtiene estadísticas de insumos
     */
    public function get_estadisticas() {
        $stats = [];
        
        // Total de insumos activos
        $this->db->where('estatus', 'Activo');
        $stats['total_activos'] = $this->db->count_all_results($this->tableName);
        
        // Insumos con stock bajo
        $this->db->where('stock_actual <=', 'stock_minimo', FALSE);
        $this->db->where('estatus', 'Activo');
        $stats['stock_bajo'] = $this->db->count_all_results($this->tableName);
        
        // Valor total de inventario (aproximado)
        $this->db->select('SUM(stock_actual * precio_promedio) as valor_total', FALSE);
        $this->db->where('estatus', 'Activo');
        $result = $this->db->get($this->tableName)->row();
        $stats['valor_inventario'] = $result->valor_total ?? 0;
        
        return $stats;
    }
}