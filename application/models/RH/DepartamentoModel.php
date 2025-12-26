<?php
/**
 * DepartamentoModel - Modelo de departamentos
 * 
 * Gestiona departamentos/áreas de la empresa
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class DepartamentoModel extends MY_Model {
    
    protected $tableName = 'departamentos';
    
    /**
     * Configuración para DataTables
     */
    protected $datatableConfig = [
        'column_order' => ['id', 'nombre', 'descripcion', 'estatus', null],
        'column_search' => ['nombre', 'descripcion'],
        'order' => ['nombre' => 'asc']
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtiene todos los departamentos activos para selects
     */
    public function get_lista_departamentos() {
        return $this->db->select('id, nombre')
                        ->where('estatus', 1)
                        ->order_by('nombre', 'ASC')
                        ->get($this->tableName)
                        ->result();
    }
    
    /**
     * Cuenta empleados por departamento
     */
    public function count_empleados($departamento_id) {
        return $this->db->where('departamento_id', $departamento_id)
                        ->where('estatus', 1)
                        ->count_all_results('empleados');
    }
    
    /**
     * Agrega un nuevo departamento
     */
    public function mod_add($data) {
        $id = $this->insert($data);
        
        if ($id) {
            return $this->success_response("Departamento agregado exitosamente", ['id' => $id]);
        }
        
        return $this->error_response("Error al agregar departamento");
    }
    
    /**
     * Actualiza un departamento
     */
    public function mod_update($id, $data) {
        if (!$this->exists($id)) {
            return $this->not_found_response("El departamento no existe");
        }
        
        $success = $this->update($id, $data);
        
        if ($success) {
            return $this->success_response("Departamento actualizado correctamente");
        }
        
        return $this->error_response("Error al actualizar departamento");
    }
    
    /**
     * Elimina un departamento (soft delete)
     */
    public function mod_delete($id) {
        // Verificar si tiene empleados asignados
        $empleados_count = $this->count_empleados($id);
        
        if ($empleados_count > 0) {
            return $this->error_response("No se puede eliminar. Tiene $empleados_count empleado(s) asignado(s)");
        }
        
        if (!$this->exists_active($id)) {
            return $this->not_found_response("El departamento no existe o ya está inactivo");
        }
        
        $success = $this->soft_delete($id);
        
        if ($success) {
            return $this->success_response("Departamento eliminado correctamente");
        }
        
        return $this->error_response("Error al eliminar departamento");
    }
    
    /**
     * Obtiene departamento con conteo de empleados
     */
    public function get_departamento_completo($id) {
        $dept = $this->get_by_id($id);
        
        if ($dept) {
            $dept->empleados_count = $this->count_empleados($id);
        }
        
        return $dept;
    }
    
    /**
     * Obtiene estadísticas de departamentos
     */
    public function get_estadisticas() {
        $stats = [];
        
        $stats['total_departamentos'] = $this->db->count_all_results($this->tableName);
        $stats['departamentos_activos'] = $this->db->where('estatus', 1)
                                                   ->count_all_results($this->tableName);
        $stats['departamentos_inactivos'] = $this->db->where('estatus', 0)
                                                     ->count_all_results($this->tableName);
        
        return $stats;
    }
}
