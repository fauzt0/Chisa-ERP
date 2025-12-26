<?php
/**
 * DescuentosModel - Gestión de descuentos y precios especiales
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class DescuentosModel extends MY_Model {
    
    protected $tableName = 'descuentos';
    
    protected $datatableConfig = [
        'column_order' => ['id', 'nombre', 'tipo_descuento', 'valor', 'estatus'],
        'column_search' => ['nombre', 'descripcion'],
        'order' => ['id' => 'desc']
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtiene descuentos activos para select
     */
    public function get_descuentos_activos() {
        $this->db->select('id, nombre, descripcion, tipo_descuento, valor');
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get($this->tableName)->result();
    }
    
    /**
     * Override de _get_datatables_query
     */
    protected function _get_datatables_query() {
        $this->db->select('descuentos.*');
        $this->db->from($this->tableName);
        
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
            $column_name = $this->datatableConfig['column_order'][$column_index];
            if($column_name) {
                $this->db->order_by($column_name, $_POST['order'][0]['dir']);
            }
        } elseif (isset($this->datatableConfig['order'])) {
            $order = $this->datatableConfig['order'];
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }
}
