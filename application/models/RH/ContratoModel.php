<?php
/**
 * ContratoModel - Modelo de contratos de empleados
 * 
 * Gestiona el historial de contratos y versionamiento automático
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class ContratoModel extends MY_Model {
    
    protected $tableName = 'contratos_empleados';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Crea el contrato inicial al dar de alta un empleado
     */
    public function crear_contrato_inicial($empleado_id) {
        // Obtener datos del empleado
        $this->load->model('RH/EmpleadoModel');
        $empleado = $this->EmpleadoModel->get_by_id($empleado_id);
        
        if (!$empleado) {
            return false;
        }
        
        // Obtener nombre del departamento
        $this->load->model('RH/DepartamentoModel');
        $departamento = $this->DepartamentoModel->get_by_id($empleado->departamento_id);
        $nombre_departamento = $departamento ? $departamento->nombre : 'Sin departamento';
        
        // Preparar datos del contrato
        $contrato_data = [
            'empleado_id' => $empleado_id,
            'version' => 1,
            'tipo_contrato' => 'Inicial',
            'vigente' => 1,
            'puesto' => $empleado->puesto,
            'departamento' => $nombre_departamento,
            'tipo_trabajador' => $empleado->tipo_trabajador,
            'salario_base_mensual' => $empleado->salario_base_mensual,
            'salario_base_diario' => $empleado->salario_base_diario,
            'tipo_nomina' => $empleado->tipo_nomina ?? 'Quincenal',
            'jornada_laboral' => 'Tiempo Completo',
            'fecha_inicio' => $empleado->fecha_ingreso,
            'fecha_fin' => NULL,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'motivo_cambio' => 'Contrato inicial de trabajo',
            'creado_por' => $this->session->userdata('id') ?? NULL,
        ];
        
        // Generar texto del contrato
        $contrato_data['contrato_texto'] = $this->generar_texto_contrato($empleado, $contrato_data);
        
        // Insertar contrato
        return $this->db->insert($this->tableName, $contrato_data);
    }
    
    /**
     * Crea un nuevo contrato cuando hay cambios significativos
     */
    public function crear_nuevo_contrato($empleado_id, $tipo_contrato, $motivo = '') {
        // Marcar contrato anterior como no vigente
        $this->db->where('empleado_id', $empleado_id)
                 ->where('vigente', 1)
                 ->update($this->tableName, [
                     'vigente' => 0,
                     'fecha_fin' => date('Y-m-d')
                 ]);
        
        // Obtener última versión
        $ultima_version = $this->db->select('MAX(version) as max_version')
                                   ->where('empleado_id', $empleado_id)
                                   ->get($this->tableName)
                                   ->row();
        
        $nueva_version = ($ultima_version && $ultima_version->max_version) ? $ultima_version->max_version + 1 : 1;
        
        // Obtener datos actuales del empleado
        $this->load->model('RH/EmpleadoModel');
        $empleado = $this->EmpleadoModel->get_by_id($empleado_id);
        
        if (!$empleado) {
            return false;
        }
        
        // Obtener nombre del departamento
        $this->load->model('RH/DepartamentoModel');
        $departamento = $this->DepartamentoModel->get_by_id($empleado->departamento_id);
        $nombre_departamento = $departamento ? $departamento->nombre : 'Sin departamento';
        
        // Preparar datos del nuevo contrato
        $contrato_data = [
            'empleado_id' => $empleado_id,
            'version' => $nueva_version,
            'tipo_contrato' => $tipo_contrato,
            'vigente' => 1,
            'puesto' => $empleado->puesto,
            'departamento' => $nombre_departamento,
            'tipo_trabajador' => $empleado->tipo_trabajador,
            'salario_base_mensual' => $empleado->salario_base_mensual,
            'salario_base_diario' => $empleado->salario_base_diario,
            'tipo_nomina' => $empleado->tipo_nomina ?? 'Quincenal',
            'jornada_laboral' => 'Tiempo Completo',
            'fecha_inicio' => date('Y-m-d'),
            'fecha_fin' => NULL,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'motivo_cambio' => $motivo ?: "Actualización de contrato: $tipo_contrato",
            'creado_por' => $this->session->userdata('id') ?? NULL,
        ];
        
        // Generar texto del contrato
        $contrato_data['contrato_texto'] = $this->generar_texto_contrato($empleado, $contrato_data);
        
        // Insertar nuevo contrato
        return $this->db->insert($this->tableName, $contrato_data);
    }
    
    /**
     * Obtiene el historial de contratos de un empleado
     */
    public function get_historial($empleado_id) {
        return $this->db->where('empleado_id', $empleado_id)
                        ->order_by('version', 'DESC')
                        ->get($this->tableName)
                        ->result();
    }
    
    /**
     * Obtiene el contrato vigente de un empleado
     */
    public function get_contrato_vigente($empleado_id) {
        return $this->db->where('empleado_id', $empleado_id)
                        ->where('vigente', 1)
                        ->get($this->tableName)
                        ->row();
    }
    
    /**
     * Obtiene un contrato específico por ID
     */
    public function get_contrato_by_id($id) {
        return $this->db->where('id', $id)
                        ->get($this->tableName)
                        ->row();
    }
    
    /**
     * Genera el texto del contrato
     */
    private function generar_texto_contrato($empleado, $contrato_data) {
        $texto = "CONTRATO INDIVIDUAL DE TRABAJO\n\n";
        $texto .= "CONTRATO No. {$contrato_data['version']}\n";
        $texto .= "Tipo: {$contrato_data['tipo_contrato']}\n\n";
        
        $texto .= "Entre CHISA RECUBRIMIENTOS (en adelante \"LA EMPRESA\") y:\n\n";
        $texto .= "NOMBRE: {$empleado->nombre} {$empleado->apellido_paterno} {$empleado->apellido_materno}\n";
        $texto .= "RFC: {$empleado->rfc}\n";
        $texto .= "CURP: {$empleado->curp}\n";
        if ($empleado->nss) {
            $texto .= "NSS: {$empleado->nss}\n";
        }
        $texto .= "\n";
        
        $texto .= "CLÁUSULAS:\n\n";
        $texto .= "PRIMERA.- PUESTO: {$contrato_data['puesto']}\n";
        $texto .= "SEGUNDA.- DEPARTAMENTO: {$contrato_data['departamento']}\n";
        $texto .= "TERCERA.- TIPO DE TRABAJADOR: {$contrato_data['tipo_trabajador']}\n";
        $texto .= "CUARTA.- SALARIO BASE MENSUAL: $" . number_format($contrato_data['salario_base_mensual'], 2) . " MXN\n";
        $texto .= "QUINTA.- SALARIO BASE DIARIO: $" . number_format($contrato_data['salario_base_diario'], 2) . " MXN\n";
        $texto .= "SEXTA.- TIPO DE NÓMINA: {$contrato_data['tipo_nomina']}\n";
        $texto .= "SÉPTIMA.- JORNADA LABORAL: {$contrato_data['jornada_laboral']}\n\n";
        
        $texto .= "FECHA DE INICIO: {$contrato_data['fecha_inicio']}\n";
        if ($contrato_data['motivo_cambio']) {
            $texto .= "MOTIVO: {$contrato_data['motivo_cambio']}\n";
        }
        
        $texto .= "\nFecha de generación: " . date('d/m/Y H:i:s');
        
        return $texto;
    }
}
