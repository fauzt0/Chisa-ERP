<?php
/**
 * HorariosModel - Modelo de gestión de horarios laborales
 * 
 * Gestiona los horarios de trabajo de los empleados por día de la semana
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class HorariosModel extends MY_Model {
    
    protected $tableName = 'horarios_empleados';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtiene el horario vigente de un empleado
     */
    public function get_horario_empleado($empleado_id) {
        $fecha_actual = date('Y-m-d');
        
        $this->db->where('empleado_id', $empleado_id);
        $this->db->where('estatus', 'Activo');
        $this->db->where('fecha_inicio <=', $fecha_actual);
        $this->db->group_start();
            $this->db->where('fecha_fin >=', $fecha_actual);
            $this->db->or_where('fecha_fin IS NULL');
        $this->db->group_end();
        $this->db->order_by('dia_semana', 'ASC');
        
        return $this->db->get($this->tableName)->result();
    }
    
    /**
     * Guarda o actualiza el horario completo de un empleado
     */
    public function guardar_horario($empleado_id, $horarios, $fecha_inicio, $creado_por = null) {
        // Iniciar transacción
        $this->db->trans_start();
        
        // Desactivar horarios anteriores
        $this->db->where('empleado_id', $empleado_id);
        $this->db->where('estatus', 'Activo');
        $this->db->update($this->tableName, [
            'estatus' => 'Inactivo',
            'fecha_fin' => date('Y-m-d', strtotime($fecha_inicio . ' -1 day'))
        ]);
        
        // Insertar nuevos horarios
        foreach($horarios as $dia => $horario) {
            // Para días no laborales, usar valores por defecto si están vacíos
            $es_laboral = $horario['es_dia_laboral'] ? 1 : 0;
            
            $data = [
                'empleado_id' => $empleado_id,
                'dia_semana' => $dia,
                'hora_entrada' => $horario['hora_entrada'] ?: '00:00:00',
                'hora_salida' => $horario['hora_salida'] ?: '00:00:00',
                'hora_entrada_comida' => $horario['hora_entrada_comida'] ?: null,
                'hora_salida_comida' => $horario['hora_salida_comida'] ?: null,
                'es_dia_laboral' => $es_laboral,
                'turno' => $horario['turno'] ?: null,
                'fecha_inicio' => $fecha_inicio,
                'observaciones' => $horario['observaciones'] ?: null,
                'creado_por' => $creado_por,
                'estatus' => 'Activo'
            ];
            
            $this->db->insert($this->tableName, $data);
        }
        
        // Finalizar transacción
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    /**
     * Obtiene el resumen del horario (horas totales por semana)
     */
    public function get_resumen_horario($empleado_id) {
        $horarios = $this->get_horario_empleado($empleado_id);
        
        $resumen = [
            'total_horas_semana' => 0,
            'dias_laborales' => 0,
            'dias_descanso' => 0,
            'turno' => null
        ];
        
        foreach($horarios as $horario) {
            if($horario->es_dia_laboral) {
                $resumen['dias_laborales']++;
                
                // Calcular horas del día
                $entrada = strtotime($horario->hora_entrada);
                $salida = strtotime($horario->hora_salida);
                $horas_dia = ($salida - $entrada) / 3600;
                
                // Restar tiempo de comida si existe
                if($horario->hora_entrada_comida && $horario->hora_salida_comida) {
                    $comida_inicio = strtotime($horario->hora_entrada_comida);
                    $comida_fin = strtotime($horario->hora_salida_comida);
                    $horas_comida = ($comida_fin - $comida_inicio) / 3600;
                    $horas_dia -= $horas_comida;
                }
                
                $resumen['total_horas_semana'] += $horas_dia;
                
                if(!$resumen['turno'] && $horario->turno) {
                    $resumen['turno'] = $horario->turno;
                }
            } else {
                $resumen['dias_descanso']++;
            }
        }
        
        return $resumen;
    }
    
    /**
     * Obtiene el historial de horarios de un empleado
     */
    public function get_historial_horarios($empleado_id) {
        $this->db->select('fecha_inicio, fecha_fin, turno, estatus, COUNT(*) as dias_configurados');
        $this->db->where('empleado_id', $empleado_id);
        $this->db->group_by(['fecha_inicio', 'fecha_fin', 'turno', 'estatus']);
        $this->db->order_by('fecha_inicio', 'DESC');
        
        return $this->db->get($this->tableName)->result();
    }
    
    /**
     * Verifica si un empleado tiene horario configurado
     */
    public function tiene_horario($empleado_id) {
        $this->db->where('empleado_id', $empleado_id);
        $this->db->where('estatus', 'Activo');
        return $this->db->count_all_results($this->tableName) > 0;
    }
    
    /**
     * Crea un horario estándar (Lun-Vie 9-6, Sab-Dom descanso)
     */
    public function crear_horario_estandar($empleado_id, $creado_por = null) {
        $horarios = [];
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        
        foreach($dias as $dia) {
            $es_laboral = !in_array($dia, ['Sábado', 'Domingo']);
            
            $horarios[$dia] = [
                'hora_entrada' => $es_laboral ? '09:00:00' : '00:00:00',
                'hora_salida' => $es_laboral ? '18:00:00' : '00:00:00',
                'hora_entrada_comida' => $es_laboral ? '14:00:00' : null,
                'hora_salida_comida' => $es_laboral ? '15:00:00' : null,
                'es_dia_laboral' => $es_laboral,
                'turno' => $es_laboral ? 'Matutino' : null,
                'observaciones' => null
            ];
        }
        
        return $this->guardar_horario($empleado_id, $horarios, date('Y-m-d'), $creado_por);
    }
}
