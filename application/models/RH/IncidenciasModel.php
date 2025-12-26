<?php
/**
 * IncidenciasModel - Modelo de gestión de incidencias de empleados
 * 
 * Gestiona el registro y seguimiento de incidencias laborales
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class IncidenciasModel extends MY_Model {
    
    protected $tableName = 'incidencias_empleados';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Registra una nueva incidencia
     */
    public function registrar_incidencia($data) {
        return $this->db->insert($this->tableName, $data);
    }
    
    /**
     * Obtiene incidencias de un empleado
     */
    public function get_incidencias_empleado($empleado_id, $filtros = []) {
        $this->db->select('i.*');
        $this->db->from($this->tableName . ' i');
        $this->db->where('i.empleado_id', $empleado_id);
        
        // Aplicar filtros opcionales
        if (!empty($filtros['tipo_incidencia'])) {
            $this->db->where('i.tipo_incidencia', $filtros['tipo_incidencia']);
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $this->db->where('i.fecha_incidencia >=', $filtros['fecha_desde']);
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $this->db->where('i.fecha_incidencia <=', $filtros['fecha_hasta']);
        }
        
        if (!empty($filtros['estatus'])) {
            $this->db->where('i.estatus', $filtros['estatus']);
        }
        
        $this->db->order_by('i.fecha_incidencia', 'DESC');
        $this->db->order_by('i.fecha_registro', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene estadísticas de incidencias de un empleado
     */
    public function get_estadisticas_empleado($empleado_id, $anio = null) {
        if (!$anio) {
            $anio = date('Y');
        }
        
        $stats = [];
        
        // Total por tipo de incidencia
        $this->db->select('tipo_incidencia, COUNT(*) as total');
        $this->db->where('empleado_id', $empleado_id);
        $this->db->where('YEAR(fecha_incidencia)', $anio);
        $this->db->where('estatus', 'Activa');
        $this->db->group_by('tipo_incidencia');
        $stats['por_tipo'] = $this->db->get($this->tableName)->result();
        
        // Total general
        $this->db->where('empleado_id', $empleado_id);
        $this->db->where('YEAR(fecha_incidencia)', $anio);
        $this->db->where('estatus', 'Activa');
        $stats['total'] = $this->db->count_all_results($this->tableName);
        
        // Total con descuento
        $this->db->select('SUM(monto_descuento) as total_descuentos');
        $this->db->where('empleado_id', $empleado_id);
        $this->db->where('YEAR(fecha_incidencia)', $anio);
        $this->db->where('tiene_descuento', 1);
        $this->db->where('estatus', 'Activa');
        $result = $this->db->get($this->tableName)->row();
        $stats['total_descuentos'] = $result->total_descuentos ?? 0;
        
        return $stats;
    }
    
    /**
     * Actualiza una incidencia
     */
    public function actualizar_incidencia($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, $data);
    }
    
    /**
     * Cancela una incidencia
     */
    public function cancelar_incidencia($id) {
        return $this->actualizar_incidencia($id, ['estatus' => 'Cancelada']);
    }
    
    /**
     * Marca una incidencia como procesada
     */
    public function marcar_procesada($id) {
        return $this->actualizar_incidencia($id, ['estatus' => 'Procesada']);
    }
    
    /**
     * Obtiene una incidencia por ID
     */
    public function get_incidencia($id) {
        return $this->db->get_where($this->tableName, ['id' => $id])->row();
    }
    
    /**
     * Obtiene todas las incidencias (para reportes)
     */
    public function get_todas_incidencias($filtros = []) {
        $this->db->select('i.*, e.numero_empleado, e.nombre, e.apellido_paterno, e.apellido_materno, d.nombre as departamento');
        $this->db->from($this->tableName . ' i');
        $this->db->join('empleados e', 'e.id = i.empleado_id');
        $this->db->join('departamentos d', 'd.id = e.departamento_id', 'left');
        
        // Aplicar filtros
        if (!empty($filtros['tipo_incidencia'])) {
            $this->db->where('i.tipo_incidencia', $filtros['tipo_incidencia']);
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $this->db->where('i.fecha_incidencia >=', $filtros['fecha_desde']);
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $this->db->where('i.fecha_incidencia <=', $filtros['fecha_hasta']);
        }
        
        if (!empty($filtros['departamento_id'])) {
            $this->db->where('e.departamento_id', $filtros['departamento_id']);
        }
        
        if (!empty($filtros['estatus'])) {
            $this->db->where('i.estatus', $filtros['estatus']);
        }
        
        $this->db->order_by('i.fecha_incidencia', 'DESC');
        
        return $this->db->get()->result();
    }
}
