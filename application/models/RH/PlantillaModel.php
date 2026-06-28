<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PlantillaModel extends MY_Model {
    
    protected $tableName = 'contrato_plantillas';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * Obtiene todas las plantillas activas
     */
    public function get_todas_activas() {
        return $this->db->where('estatus', 1)
                        ->order_by('nombre', 'ASC')
                        ->get($this->tableName)
                        ->result();
    }
    
    /**
     * Guarda una nueva plantilla
     */
    public function guardar($data) {
        $plantilla = [
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? '',
            'contenido' => $data['contenido'],
            'logo' => $data['logo'] ?? NULL,
            'color_corporativo' => $data['color_corporativo'] ?? '#1a3a5c',
            'domicilio_empresa' => $data['domicilio_empresa'] ?? NULL,
            'estatus' => 1,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'usuario_id' => $this->session->userdata('id')
        ];
        
        $this->db->insert($this->tableName, $plantilla);
        return $this->db->insert_id();
    }
    
    /**
     * Actualiza una plantilla existente
     */
    public function actualizar($id, $data) {
        $plantilla = [
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? '',
            'contenido' => $data['contenido'],
            'color_corporativo' => $data['color_corporativo'] ?? '#1a3a5c',
            'domicilio_empresa' => $data['domicilio_empresa'] ?? NULL,
        ];
        
        if(isset($data['logo'])){
            $plantilla['logo'] = $data['logo'];
        }
        
        return $this->db->where('id', $id)
                        ->update($this->tableName, $plantilla);
    }
    
    /**
     * Duplica una plantilla existente
     */
    public function duplicar($id, $nuevo_nombre) {
        $original = $this->get_by_id($id);
        if (!$original) return false;
        
        $copia = [
            'nombre' => $nuevo_nombre,
            'descripcion' => "Copia de: " . $original->nombre,
            'contenido' => $original->contenido,
            'estatus' => 1,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'usuario_id' => $this->session->userdata('id')
        ];
        
        $this->db->insert($this->tableName, $copia);
        return $this->db->insert_id();
    }
    
    /**
     * Desactiva (elimina lógicamente) una plantilla
     */
    public function desactivar($id) {
        return $this->db->where('id', $id)
                        ->update($this->tableName, ['estatus' => 0]);
    }
}
