<?php
/**
 * VacacionesModel - Modelo de gestión de vacaciones
 * 
 * Gestiona períodos de vacaciones y solicitudes conforme a la LFT México
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class VacacionesModel extends MY_Model {
    
    protected $tableName = 'vacaciones_empleados';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Calcula días de vacaciones según antigüedad (LFT México)
     */
    public function calcular_dias_por_antiguedad($anios) {
        if ($anios == 1) return 6;
        if ($anios == 2) return 8;
        if ($anios == 3) return 10;
        if ($anios == 4) return 12;
        if ($anios >= 5 && $anios <= 9) return 14;
        if ($anios >= 10 && $anios <= 14) return 16;
        if ($anios >= 15 && $anios <= 19) return 18;
        if ($anios >= 20 && $anios <= 24) return 20;
        
        // 25 años en adelante: 20 días base + 2 días cada 5 años adicionales
        $anios_extra = $anios - 20;
        $incrementos = floor($anios_extra / 5);
        return 20 + ($incrementos * 2);
    }
    
    /**
     * Genera período de vacaciones para un empleado
     */
    public function generar_periodo_anual($empleado_id, $dias_adicionales = 0) {
        $this->load->model('RH/EmpleadoModel');
        $empleado = $this->EmpleadoModel->get_by_id($empleado_id);
        
        if (!$empleado) {
            return ['success' => false, 'message' => 'Empleado no encontrado'];
        }
        
        // Calcular años de antigüedad
        $fecha_ingreso = new DateTime($empleado->fecha_ingreso);
        $hoy = new DateTime();
        $antiguedad = $fecha_ingreso->diff($hoy)->y;
        
        // Si tiene menos de 1 año, no genera período aún
        if ($antiguedad < 1) {
            return ['success' => false, 'message' => 'El empleado tiene menos de 1 año de antigüedad (' . $fecha_ingreso->diff($hoy)->m . ' meses)'];
        }
        
        // Calcular días correspondientes por ley
        $dias_ley = $this->calcular_dias_por_antiguedad($antiguedad);
        
        // Calcular fechas del período (aniversario a aniversario)
        $periodo_inicio = clone $fecha_ingreso;
        $periodo_inicio->modify("+{$antiguedad} years");
        $periodo_fin = clone $periodo_inicio;
        $periodo_fin->modify("+1 year -1 day");
        
        // Verificar si ya existe un período para este año
        $existe = $this->db->where('empleado_id', $empleado_id)
                           ->where('periodo_inicio', $periodo_inicio->format('Y-m-d'))
                           ->count_all_results($this->tableName);
        
        if ($existe > 0) {
            return ['success' => false, 'message' => 'Ya existe un período generado para el ciclo ' . $periodo_inicio->format('Y')]; 
        }
        
        // Preparar datos del período
        $dias_totales = $dias_ley + $dias_adicionales;
        $data = [
            'empleado_id' => $empleado_id,
            'periodo_inicio' => $periodo_inicio->format('Y-m-d'),
            'periodo_fin' => $periodo_fin->format('Y-m-d'),
            'anios_antiguedad' => $antiguedad,
            'dias_correspondientes' => $dias_ley,
            'dias_adicionales' => $dias_adicionales,
            'dias_totales' => $dias_totales,
            'dias_tomados' => 0,
            'dias_disponibles' => $dias_totales,
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];
        
        if($this->db->insert($this->tableName, $data)){
             return ['success' => true, 'message' => 'Período generado correctamente'];
        } else {
             return ['success' => false, 'message' => 'Error de base de datos al generar período'];
        }
    }
    
    /**
     * Obtiene el período actual de vacaciones de un empleado
     */
    public function get_periodo_actual($empleado_id) {
        $hoy = date('Y-m-d');
        
        return $this->db->where('empleado_id', $empleado_id)
                        ->where('periodo_inicio <=', $hoy)
                        ->where('periodo_fin >=', $hoy)
                        ->get($this->tableName)
                        ->row();
    }
    
    /**
     * Obtiene todos los períodos de un empleado
     */
    public function get_historial_periodos($empleado_id) {
        return $this->db->where('empleado_id', $empleado_id)
                        ->order_by('periodo_inicio', 'DESC')
                        ->get($this->tableName)
                        ->result();
    }
    
    /**
     * Obtiene el balance actual de vacaciones
     */
    public function get_balance_actual($empleado_id) {
        $periodo = $this->get_periodo_actual($empleado_id);
        
        if (!$periodo) {
            // Si no tiene período actual, intentar generar uno
            $this->generar_periodo_anual($empleado_id);
            $periodo = $this->get_periodo_actual($empleado_id);
        }
        
        return $periodo;
    }
    
    /**
     * Registra una solicitud de vacaciones
     */
    public function solicitar_vacaciones($data) {
        // Validar que tenga días disponibles
        $periodo = $this->get_by_id($data['periodo_vacaciones_id']);
        
        if (!$periodo || $periodo->dias_disponibles < $data['dias_solicitados']) {
            return [
                'success' => false,
                'message' => 'No hay suficientes días disponibles'
            ];
        }
        
        // Preparar datos de solicitud
        $solicitud = [
            'empleado_id' => $data['empleado_id'],
            'periodo_vacaciones_id' => $data['periodo_vacaciones_id'],
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'dias_solicitados' => $data['dias_solicitados'],
            'estatus' => 'Pendiente',
            'fecha_solicitud' => date('Y-m-d H:i:s'),
            'observaciones' => $data['observaciones'] ?? null
        ];
        
        // Insertar solicitud
        $this->db->insert('solicitudes_vacaciones', $solicitud);
        $solicitud_id = $this->db->insert_id();
        
        // Descontar días provisionalmente
        $this->db->where('id', $data['periodo_vacaciones_id'])
                 ->set('dias_disponibles', 'dias_disponibles - ' . $data['dias_solicitados'], FALSE)
                 ->set('dias_tomados', 'dias_tomados + ' . $data['dias_solicitados'], FALSE)
                 ->update($this->tableName);
        
        return [
            'success' => true,
            'message' => 'Solicitud registrada correctamente',
            'solicitud_id' => $solicitud_id
        ];
    }
    
    /**
     * Obtiene solicitudes de un empleado
     */
    public function get_solicitudes_empleado($empleado_id) {
        return $this->db->select('s.*, v.periodo_inicio, v.periodo_fin')
                        ->from('solicitudes_vacaciones s')
                        ->join('vacaciones_empleados v', 'v.id = s.periodo_vacaciones_id')
                        ->where('s.empleado_id', $empleado_id)
                        ->order_by('s.fecha_solicitud', 'DESC')
                        ->get()
                        ->result();
    }

    /**
     * Obtiene todas las solicitudes del sistema (para administrador)
     */
    public function get_todas_solicitudes($estatus = null) {
        $this->db->select('s.*, e.nombre, e.apellido_paterno, e.numero_empleado, v.periodo_inicio, v.periodo_fin');
        $this->db->from('solicitudes_vacaciones s');
        $this->db->join('empleados e', 'e.id = s.empleado_id');
        $this->db->join('vacaciones_empleados v', 'v.id = s.periodo_vacaciones_id');
        
        if ($estatus) {
            $this->db->where('s.estatus', $estatus);
        }
        
        return $this->db->order_by('s.fecha_solicitud', 'DESC')->get()->result();
    }
    
    /**
     * Aprueba una solicitud de vacaciones
     */
    public function aprobar_solicitud($solicitud_id, $admin_id) {
        $data = [
            'estatus' => 'Aprobada',
            'aprobado_por' => $admin_id,
            'fecha_aprobacion' => date('Y-m-d H:i:s')
        ];
        
        $this->db->where('id', $solicitud_id);
        return $this->db->update('solicitudes_vacaciones', $data);
    }
    
    /**
     * Rechaza una solicitud de vacaciones y restaura el balance
     */
    public function rechazar_solicitud($solicitud_id, $motivo, $admin_id) {
        // Obtener datos de la solicitud para restaurar balance
        $solicitud = $this->db->get_where('solicitudes_vacaciones', ['id' => $solicitud_id])->row();
        
        if (!$solicitud) return false;
        
        $this->db->trans_start();
        
        // 1. Actualizar estatus de la solicitud
        $data = [
            'estatus' => 'Rechazada',
            'motivo_rechazo' => $motivo,
            'aprobado_por' => $admin_id,
            'fecha_aprobacion' => date('Y-m-d H:i:s')
        ];
        $this->db->where('id', $solicitud_id);
        $this->db->update('solicitudes_vacaciones', $data);
        
        // 2. Restaurar días en el período
        $this->db->where('id', $solicitud->periodo_vacaciones_id)
                 ->set('dias_disponibles', 'dias_disponibles + ' . $solicitud->dias_solicitados, FALSE)
                 ->set('dias_tomados', 'dias_tomados - ' . $solicitud->dias_solicitados, FALSE)
                 ->update($this->tableName);
                 
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    /**
     * Calcula días hábiles entre dos fechas (excluye fines de semana)
     */
    public function calcular_dias_habiles($fecha_inicio, $fecha_fin) {
        $inicio = new DateTime($fecha_inicio);
        $fin = new DateTime($fecha_fin);
        $dias = 0;
        
        while ($inicio <= $fin) {
            $dia_semana = $inicio->format('w');
            if ($dia_semana != 0 && $dia_semana != 6) {
                $dias++;
            }
            $inicio->modify('+1 day');
        }
        
        return $dias;
    }
}
