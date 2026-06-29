<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NominaModel extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('RH/EmpleadoModel');
    }
    
    /**
     * Obtiene nóminas con filtros
     */
    public function get_nominas($filtros = []) {
        $this->db->select('*');
        $this->db->from('nominas');
        
        if(!empty($filtros['tipo_nomina'])) {
            $this->db->where('tipo_nomina', $filtros['tipo_nomina']);
        }
        
        if(!empty($filtros['estatus'])) {
            $this->db->where('estatus', $filtros['estatus']);
        }
        
        if(!empty($filtros['fecha_inicio'])) {
            $this->db->where('fecha_pago >=', $filtros['fecha_inicio']);
        }
        
        if(!empty($filtros['fecha_fin'])) {
            $this->db->where('fecha_pago <=', $filtros['fecha_fin']);
        }
        
        $this->db->order_by('fecha_pago', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene una nómina completa con detalle
     */
    public function get_nomina_completa($id) {
        // Nómina
        $this->db->select('*');
        $this->db->from('nominas');
        $this->db->where('id', $id);
        $nomina = $this->db->get()->row();
        
        if($nomina) {
            // Detalle con datos de empleados
            $this->db->select('nd.*, e.numero_empleado, e.nombre, e.apellido_paterno, e.apellido_materno, e.puesto, e.rfc, e.curp, e.nss, e.tipo_nomina as emp_tipo_nomina');
            $this->db->from('nominas_detalle nd');
            $this->db->join('empleados e', 'nd.empleado_id = e.id');
            $this->db->where('nd.nomina_id', $id);
            $this->db->order_by('e.nombre', 'ASC');
            $nomina->detalle = $this->db->get()->result();
            
            // Para cada empleado, obtener conceptos
            foreach($nomina->detalle as &$det) {
                $this->db->select('*');
                $this->db->from('nominas_conceptos');
                $this->db->where('nomina_detalle_id', $det->id);
                $det->conceptos = $this->db->get()->result();
            }
            unset($det);
        }
        
        return $nomina;
    }
    
    /**
     * Obtiene empleados activos por tipo de nómina
     */
    public function get_empleados_activos($tipo_nomina = null) {
        $this->db->select('*');
        $this->db->from('empleados');
        $this->db->where_in('estatus', EmpleadoModel::estatus_laborales_activos());
        
        if($tipo_nomina) {
            $this->db->where('tipo_nomina', $tipo_nomina);
        }
        
        $this->db->order_by('nombre', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene vacaciones de un empleado
     */
    public function get_vacaciones_empleado($empleado_id, $año = null) {
        $this->db->select('*');
        $this->db->from('vacaciones');
        $this->db->where('empleado_id', $empleado_id);
        
        if($año) {
            $this->db->where('YEAR(periodo_inicio)', $año);
        }
        
        $this->db->order_by('fecha_solicitud', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Calcula días de vacaciones disponibles
     */
    public function calcular_dias_vacaciones($empleado_id) {
        // Obtener empleado
        $empleado = $this->db->get_where('empleados', ['id' => $empleado_id])->row();
        
        if(!$empleado) return 0;
        
        // Calcular años de antigüedad
        $fecha_ingreso = new DateTime($empleado->fecha_ingreso);
        $hoy = new DateTime();
        $años = $hoy->diff($fecha_ingreso)->y;
        
        // Días según LFT (Ley Federal del Trabajo)
        $dias_por_año = [
            1 => 12, 2 => 14, 3 => 16, 4 => 18, 5 => 20,
            6 => 22, 7 => 24, 8 => 26, 9 => 28, 10 => 30
        ];
        
        $dias_totales = isset($dias_por_año[$años]) ? $dias_por_año[$años] : 30;
        
        // Restar días ya tomados este año
        $this->db->select('SUM(dias_solicitados) as dias_tomados');
        $this->db->from('vacaciones');
        $this->db->where('empleado_id', $empleado_id);
        $this->db->where('YEAR(periodo_inicio)', date('Y'));
        $this->db->where_in('estatus', ['Autorizada', 'Tomada']);
        $tomados = $this->db->get()->row();
        
        $dias_disponibles = $dias_totales - ($tomados->dias_tomados ?: 0);
        
        return max(0, $dias_disponibles);
    }
}
