<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RolesModel extends MY_Model {
    
    protected $tableName = 'roles';
    
    public function __construct() {
        parent::__construct();
        $this->check_table();
    }
    
    /**
     * Verifica si la tabla existe, si no, la crea
     */
    public function check_table() {
        if (!$this->db->table_exists($this->tableName)) {
            $sql = "CREATE TABLE `roles` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `nombre` varchar(100) NOT NULL,
                `descripcion` varchar(255) DEFAULT NULL,
                `permisos` text NOT NULL COMMENT 'JSON array',
                `estatus` tinyint(1) DEFAULT 1,
                `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
                `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $this->db->query($sql);
        }
    }
    
    public function get_all_roles() {
        $this->db->where('estatus', 1);
        return $this->db->get($this->tableName)->result();
    }
    
    public function get_role($id) {
        $this->db->where('id', $id);
        return $this->db->get($this->tableName)->row();
    }
    
    public function create_role($data) {
        return $this->db->insert($this->tableName, $data);
    }
    
    public function update_role($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, $data);
    }
    
    public function delete_role($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->tableName);
    }
}
