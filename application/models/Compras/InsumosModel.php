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
        'table'         => 'insumos',
        'column_order'  => ['insumos.codigo', 'insumos.nombre_tecnico', 'categoria_nombre', 'insumos.marca', 'insumos.unidad_medida', 'insumos.stock_actual', 'insumos.precio_promedio', 'insumos.estatus', null],
        'column_search' => ['insumos.codigo', 'insumos.nombre_tecnico', 'insumos.alias', 'insumos.marca'],
        'order'         => ['insumos.nombre_tecnico' => 'ASC']
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
        $this->db->join('insumos_alias ia', 'ia.insumo_id = insumos.id', 'left');
        $this->db->group_by('insumos.id');
        
        // Búsqueda (incluye alias de la tabla insumos_alias)
        if(isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
            $search = $_POST['search']['value'];
            $this->db->group_start();
            $this->db->like('insumos.codigo', $search);
            $this->db->or_like('insumos.nombre_tecnico', $search);
            $this->db->or_like('insumos.alias', $search);
            $this->db->or_like('insumos.marca', $search);
            $this->db->or_like('ia.alias', $search);
            $this->db->group_end();
        }
        
        // Ordenamiento
        if(isset($_POST['order'])) {
            $column_index = $_POST['order'][0]['column'];
            $column_name  = $this->datatableConfig['column_order'][$column_index];
            if($column_name) {
                $this->db->order_by($column_name, $_POST['order'][0]['dir']);
            }
        } elseif (isset($this->datatableConfig['order'])) {
            $order = $this->datatableConfig['order'];
            $this->db->order_by(key($order), $order[key($order)]);
        }

        if (!empty($_POST['filtro_categoria'])) {
            $this->db->where('insumos.categoria_id', (int) $_POST['filtro_categoria']);
        }
        if (!empty($_POST['filtro_estatus'])) {
            $this->db->where('insumos.estatus', $_POST['filtro_estatus']);
        }
        if (!empty($_POST['filtro_stock_bajo']) && $_POST['filtro_stock_bajo'] === '1') {
            $this->db->where('insumos.stock_minimo >', 0);
            $this->db->where('insumos.stock_actual <= insumos.stock_minimo', null, false);
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
            $busqueda = $filtros['busqueda'];
            $alias_sub = "EXISTS (SELECT 1 FROM insumos_alias ia WHERE ia.insumo_id = insumos.id AND ia.alias LIKE '%" . $this->db->escape_like_str($busqueda) . "%')";
            $this->db->group_start();
            $this->db->like('insumos.nombre_tecnico', $busqueda);
            $this->db->or_like('insumos.alias', $busqueda);
            $this->db->or_like('insumos.codigo', $busqueda);
            $this->db->or_like('insumos.marca', $busqueda);
            $this->db->or_where($alias_sub, null, false);
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
        
        $this->db->where('estatus', 'Activo');
        $stats['total_activos'] = $this->db->count_all_results($this->tableName);
        
        $this->db->where('stock_actual <=', 'stock_minimo', FALSE);
        $this->db->where('estatus', 'Activo');
        $stats['stock_bajo'] = $this->db->count_all_results($this->tableName);
        
        $this->db->select('SUM(stock_actual * precio_promedio) as valor_total', FALSE);
        $this->db->where('estatus', 'Activo');
        $result = $this->db->get($this->tableName)->row();
        $stats['valor_inventario'] = $result->valor_total ?? 0;
        
        return $stats;
    }
    
    // =============================================
    // GESTIÓN DE ALIAS MÚLTIPLES (insumos_alias)
    // =============================================
    
    /**
     * Obtiene todos los alias de un insumo
     */
    public function get_aliases_insumo($insumo_id) {
        $this->db->select('ia.*, p.razon_social as proveedor_nombre');
        $this->db->from('insumos_alias ia');
        $this->db->join('proveedores p', 'p.id = ia.proveedor_id', 'left');
        $this->db->where('ia.insumo_id', $insumo_id);
        $this->db->order_by('ia.tipo, ia.alias', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Agrega un alias a un insumo
     */
    public function agregar_alias($data) {
        $this->db->where('insumo_id', $data['insumo_id']);
        $this->db->where('alias', $data['alias']);
        $existente = $this->db->get('insumos_alias')->row();
        if($existente) {
            return ['success' => false, 'message' => 'Ya existe ese alias para este insumo'];
        }
        $result = $this->db->insert('insumos_alias', $data);
        return [
            'success' => $result,
            'message' => $result ? 'Alias agregado correctamente' : 'Error al agregar alias',
            'id'      => $result ? $this->db->insert_id() : null
        ];
    }
    
    /**
     * Elimina un alias
     */
    public function eliminar_alias($alias_id, $insumo_id) {
        $this->db->where('id', $alias_id);
        $this->db->where('insumo_id', $insumo_id);
        $result = $this->db->delete('insumos_alias');
        return [
            'success' => $result,
            'message' => $result ? 'Alias eliminado' : 'Error al eliminar alias'
        ];
    }
    
    /**
     * Busca insumos por nombre, código o cualquier alias (autocomplete)
     */
    public function buscar_insumos($termino) {
        $sql = "SELECT DISTINCT insumos.id, insumos.codigo, insumos.nombre_tecnico,
                       insumos.unidad_medida, insumos.stock_actual, insumos.precio_promedio
                FROM insumos
                LEFT JOIN insumos_alias ia ON ia.insumo_id = insumos.id
                WHERE insumos.estatus = 'Activo'
                AND (
                    insumos.nombre_tecnico LIKE ?
                    OR insumos.alias LIKE ?
                    OR insumos.codigo LIKE ?
                    OR ia.alias LIKE ?
                )
                ORDER BY insumos.nombre_tecnico ASC
                LIMIT 30";
        $like = '%' . $termino . '%';
        return $this->db->query($sql, [$like, $like, $like, $like])->result();
    }

    /**
     * Obtiene los proveedores asociados a un insumo, ordenados por proveedor
     * principal primero y luego por precio de compra ascendente.
     *
     * NOTA: la tabla real de relación es `proveedor_insumo` (singular), NO
     * `proveedores_insumos`. Corregido en Iteración 4 (el método existía con
     * el nombre de tabla equivocado y nunca había sido ejecutado con éxito).
     */
    public function get_proveedores_por_insumo($insumo_id) {
        $this->db->select('p.id, p.razon_social, pi.precio_compra, pi.tiempo_entrega_dias, pi.cantidad_minima, pi.es_proveedor_principal');
        $this->db->from('proveedores p');
        $this->db->join('proveedor_insumo pi', 'pi.proveedor_id = p.id', 'inner');
        $this->db->where('pi.insumo_id', $insumo_id);
        $this->db->where('pi.estatus', 'Activo');
        $this->db->where('p.estatus', 'Activo');
        $this->db->order_by('pi.es_proveedor_principal', 'DESC');
        $this->db->order_by('pi.precio_compra', 'ASC');
        return $this->db->get()->result();
    }
}