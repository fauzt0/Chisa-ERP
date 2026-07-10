<?php
/**
 * ProveedoresModel - Modelo de gestión de proveedores
 * 
 * Gestiona catálogo de proveedores y relación con insumos
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class ProveedoresModel extends MY_Model {
    
    protected $tableName = 'proveedores';
    
    // Configuración para DataTables
    protected $datatableConfig = [
        'table' => 'proveedores',
        'column_order' => ['codigo', 'razon_social', 'rfc', 'telefono', 'ciudad', 'tipo_proveedor', null, 'estatus', null],
        'column_search' => ['proveedores.codigo', 'proveedores.razon_social', 'proveedores.nombre_comercial', 'proveedores.rfc', 'proveedores.telefono', 'proveedores.email', 'proveedores.contacto_principal', 'proveedores.ciudad'],
        'order' => ['razon_social' => 'ASC']
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Override de _get_datatables_query
     */
    protected function _get_datatables_query() {
        $this->db->select('proveedores.*,
            (SELECT COUNT(*) FROM proveedor_insumo pi WHERE pi.proveedor_id = proveedores.id) AS total_insumos,
            (SELECT COUNT(*) FROM ordenes_compra oc WHERE oc.proveedor_id = proveedores.id) AS total_ordenes,
            (SELECT MAX(oc2.fecha_orden) FROM ordenes_compra oc2 WHERE oc2.proveedor_id = proveedores.id) AS ultima_orden', false);
        $this->db->from($this->tableName);

        if(isset($_POST['filtro_estatus']) && $_POST['filtro_estatus'] !== '') {
            $this->db->where('proveedores.estatus', $_POST['filtro_estatus']);
        }

        if(isset($_POST['filtro_tipo_proveedor']) && $_POST['filtro_tipo_proveedor'] !== '') {
            $this->db->where('proveedores.tipo_proveedor', $_POST['filtro_tipo_proveedor']);
        }

        if(isset($_POST['filtro_con_ordenes']) && $_POST['filtro_con_ordenes'] === 'si') {
            $this->db->where('(SELECT COUNT(*) FROM ordenes_compra oc WHERE oc.proveedor_id = proveedores.id) >', 0, false);
        } elseif(isset($_POST['filtro_con_ordenes']) && $_POST['filtro_con_ordenes'] === 'no') {
            $this->db->where('(SELECT COUNT(*) FROM ordenes_compra oc WHERE oc.proveedor_id = proveedores.id) =', 0, false);
        }
        
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
        if(isset($_POST['order']) && isset($_POST['order'][0])) {
            $column_index = $_POST['order'][0]['column'];
            $column_name = $this->datatableConfig['column_order'][$column_index] ?? null;
            if($column_name) {
                $orderCol = strpos($column_name, '.') !== false ? $column_name : 'proveedores.' . $column_name;
                $this->db->order_by($orderCol, $_POST['order'][0]['dir']);
            }
        } elseif (isset($this->datatableConfig['order'])) {
            $order = $this->datatableConfig['order'];
            $this->db->order_by('proveedores.' . key($order), $order[key($order)]);
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
     * Obtiene un proveedor por ID
     */
    public function get_proveedor($id) {
        $this->db->where('id', $id);
        return $this->db->get($this->tableName)->row();
    }
    
    /**
     * Crea un nuevo proveedor
     */
    public function crear_proveedor($data) {
        // Generar código si no existe
        if(empty($data['codigo'])) {
            $data['codigo'] = $this->generar_codigo();
        }
        
        return $this->db->insert($this->tableName, $data);
    }
    
    /**
     * Actualiza un proveedor
     */
    public function actualizar_proveedor($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, $data);
    }
    
    /**
     * Elimina un proveedor (validar dependencias)
     */
    public function eliminar_proveedor($id) {
        // Verificar que no tenga órdenes de compra
        $this->db->where('proveedor_id', $id);
        if($this->db->count_all_results('ordenes_compra') > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: tiene órdenes de compra asociadas'];
        }
        
        // Verificar que no tenga insumos relacionados
        $this->db->where('proveedor_id', $id);
        $count = $this->db->count_all_results('proveedor_insumo');
        
        if($count > 0) {
            return ['success' => false, 'message' => 'No se puede eliminar: tiene ' . $count . ' insumos relacionados. Elimine las relaciones primero.'];
        }
        
        $this->db->where('id', $id);
        $result = $this->db->delete($this->tableName);
        
        return ['success' => $result, 'message' => $result ? 'Proveedor eliminado' : 'Error al eliminar'];
    }
    
    /**
     * Obtiene insumos de un proveedor
     */
    public function get_insumos_proveedor($proveedor_id) {
        $this->db->select('proveedor_insumo.*, insumos.codigo, insumos.nombre_tecnico, insumos.unidad_medida');
        $this->db->from('proveedor_insumo');
        $this->db->join('insumos', 'insumos.id = proveedor_insumo.insumo_id');
        $this->db->where('proveedor_insumo.proveedor_id', $proveedor_id);
        $this->db->where('proveedor_insumo.estatus', 'Activo');
        $this->db->order_by('insumos.nombre_tecnico', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene proveedores de un insumo
     */
    public function get_proveedores_insumo($insumo_id) {
        $this->db->select('proveedor_insumo.*, proveedores.codigo, proveedores.razon_social, proveedores.nombre_comercial');
        $this->db->from('proveedor_insumo');
        $this->db->join('proveedores', 'proveedores.id = proveedor_insumo.proveedor_id');
        $this->db->where('proveedor_insumo.insumo_id', $insumo_id);
        $this->db->where('proveedores.estatus', 'Activo');
        $this->db->order_by('proveedor_insumo.precio_compra', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Agrega un insumo a un proveedor
     */
    public function agregar_insumo($proveedor_id, $insumo_id, $data) {
        // Verificar que no exista ya la relación
        $this->db->where('proveedor_id', $proveedor_id);
        $this->db->where('insumo_id', $insumo_id);
        if($this->db->count_all_results('proveedor_insumo') > 0) {
            return ['success' => false, 'message' => 'Este insumo ya está relacionado con el proveedor'];
        }
        
        $data['proveedor_id'] = $proveedor_id;
        $data['insumo_id'] = $insumo_id;
        $data['estatus'] = 'Activo';

        if (!empty($data['es_proveedor_principal'])) {
            $this->db->where('insumo_id', $insumo_id);
            $this->db->update('proveedor_insumo', ['es_proveedor_principal' => 0]);
        }
        
        $result = $this->db->insert('proveedor_insumo', $data);
        return ['success' => $result, 'message' => $result ? 'Insumo agregado' : 'Error al agregar'];
    }
    
    /**
     * Actualiza precio/datos de un insumo del proveedor
     */
    public function actualizar_precio_insumo($proveedor_id, $insumo_id, $data) {
        if (!empty($data['es_proveedor_principal'])) {
            $this->db->where('insumo_id', $insumo_id);
            $this->db->where('proveedor_id !=', $proveedor_id);
            $this->db->update('proveedor_insumo', ['es_proveedor_principal' => 0]);
        }

        $this->db->where('proveedor_id', $proveedor_id);
        $this->db->where('insumo_id', $insumo_id);
        $result = $this->db->update('proveedor_insumo', $data);
        
        return ['success' => $result, 'message' => $result ? 'Precio actualizado' : 'Error al actualizar'];
    }
    
    /**
     * Elimina un insumo de un proveedor
     */
    public function eliminar_insumo($proveedor_id, $insumo_id) {
        $this->db->where('proveedor_id', $proveedor_id);
        $this->db->where('insumo_id', $insumo_id);
        $result = $this->db->delete('proveedor_insumo');
        
        return ['success' => $result, 'message' => $result ? 'Insumo eliminado de proveedor' : 'Error al eliminar'];
    }
    
    /**
     * Genera código único para proveedor
     */
    private function generar_codigo() {
        $prefijo = 'PROV';
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
     * Lista proveedores por tipo (ej. Servicios)
     */
    public function listar_por_tipo($tipo) {
        $this->db->from($this->tableName);
        $this->db->where('tipo_proveedor', $tipo);
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('razon_social', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Obtiene estadísticas de proveedores
     */
    public function get_estadisticas() {
        $stats = [];
        
        // Total de proveedores activos
        $this->db->where('estatus', 'Activo');
        $stats['total_activos'] = $this->db->count_all_results($this->tableName);
        
        // Total de proveedores inactivos
        $this->db->where('estatus', 'Inactivo');
        $stats['total_inactivos'] = $this->db->count_all_results($this->tableName);
        
        // Total de relaciones proveedor-insumo
        $stats['total_relaciones'] = $this->db->count_all_results('proveedor_insumo');

        $stats['total_ordenes'] = $this->db->count_all_results('ordenes_compra');

        $this->db->select('COUNT(DISTINCT proveedor_id) AS total');
        $this->db->from('ordenes_compra');
        $row = $this->db->get()->row();
        $stats['proveedores_con_ordenes'] = (int) ($row->total ?? 0);
        
        return $stats;
    }
}
