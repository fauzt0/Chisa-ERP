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
        $this->load->model('RH/EmpleadoModel');
        $this->load->model('RH/PlantillaModel');
        
        $empleado = $this->EmpleadoModel->get_by_id($empleado_id);
        if (!$empleado) return false;
        
        // Buscar plantilla por defecto
        $plantilla = $this->db->where('nombre', 'Contrato Individual Estándar')->get('contrato_plantillas')->row();
        $plantilla_id = $plantilla ? $plantilla->id : NULL;
        $contenido_base = $plantilla ? $plantilla->contenido : '';
        
        // Obtener nombre del departamento
        $this->load->model('RH/DepartamentoModel');
        $departamento = $this->DepartamentoModel->get_by_id($empleado->departamento_id);
        $nombre_departamento = $departamento ? $departamento->nombre : 'Sin departamento';
        
        // Preparar datos del contrato
        $contrato_data = [
            'empleado_id' => $empleado_id,
            'plantilla_id' => $plantilla_id,
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
        
        // Generar texto del contrato usando la plantilla
        if ($contenido_base) {
            // Inyectar metadata de la plantilla para placeholders como color, logo, domicilio
            $contrato_data['color_corporativo'] = $plantilla->color_corporativo ?? '#1a3a5c';
            $contrato_data['domicilio_empresa'] = $plantilla->domicilio_empresa ?? null;
            $contrato_data['logo'] = $plantilla->logo ?? null;
            $contrato_data['contrato_texto'] = $this->procesar_plantilla($contenido_base, $empleado, $contrato_data);
        } else {
            // Fallback si no hay plantilla (para compatibilidad)
            $contrato_data['contrato_texto'] = $this->generar_texto_legacy($empleado, $contrato_data);
        }
        
        return $this->insert_contrato($contrato_data);
    }
    
    /**
     * Inserta contrato omitiendo columnas inexistentes en BD (p. ej. plantilla_id).
     */
    private function insert_contrato(array $contrato_data) {
        static $columnas = null;
        if ($columnas === null) {
            $columnas = array_flip($this->db->list_fields($this->tableName));
        }
        unset($contrato_data['color_corporativo'], $contrato_data['domicilio_empresa'], $contrato_data['logo']);
        $contrato_data = array_intersect_key($contrato_data, $columnas);
        return $this->db->insert($this->tableName, $contrato_data);
    }
    
    /**
     * Crea un nuevo contrato (Manual o Renovación)
     */
    public function crear_nuevo_contrato($empleado_id, $tipo_contrato, $motivo = '', $plantilla_id = NULL, $contenido_personalizado = NULL) {
        $this->db->where('empleado_id', $empleado_id)->where('vigente', 1)
                 ->update($this->tableName, ['vigente' => 0, 'fecha_fin' => date('Y-m-d')]);
        
        $ultima = $this->db->select('MAX(version) as v')->where('empleado_id', $empleado_id)->get($this->tableName)->row();
        $nueva_version = ($ultima && $ultima->v) ? $ultima->v + 1 : 1;
        
        $this->load->model('RH/EmpleadoModel');
        $empleado = $this->EmpleadoModel->get_by_id($empleado_id);
        if (!$empleado) return false;
        
        $this->load->model('RH/DepartamentoModel');
        $dep = $this->DepartamentoModel->get_by_id($empleado->departamento_id);
        
        $contrato_data = [
            'empleado_id' => $empleado_id,
            'plantilla_id' => $plantilla_id,
            'version' => $nueva_version,
            'tipo_contrato' => $tipo_contrato,
            'vigente' => 1,
            'puesto' => $empleado->puesto,
            'departamento' => $dep ? $dep->nombre : 'Sin departamento',
            'tipo_trabajador' => $empleado->tipo_trabajador,
            'salario_base_mensual' => $empleado->salario_base_mensual,
            'salario_base_diario' => $empleado->salario_base_diario,
            'tipo_nomina' => $empleado->tipo_nomina ?? 'Quincenal',
            'jornada_laboral' => 'Tiempo Completo',
            'fecha_inicio' => date('Y-m-d'),
            'fecha_fin' => NULL,
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'motivo_cambio' => $motivo ?: "Actualización: $tipo_contrato",
            'creado_por' => $this->session->userdata('id') ?? NULL,
        ];
        
        // Definir contenido: Personalizado > Plantilla > Legacy
        if ($contenido_personalizado) {
            $contrato_data['contrato_texto'] = $contenido_personalizado;
        } elseif ($plantilla_id) {
            $plantilla = $this->db->where('id', $plantilla_id)->get('contrato_plantillas')->row();
            if ($plantilla) {
                // Inyectar metadata de la plantilla para placeholders como color, logo, domicilio
                $contrato_data['color_corporativo'] = $plantilla->color_corporativo ?? '#1a3a5c';
                $contrato_data['domicilio_empresa'] = $plantilla->domicilio_empresa ?? null;
                $contrato_data['logo'] = $plantilla->logo ?? null;
                $contrato_data['contrato_texto'] = $this->procesar_plantilla($plantilla->contenido, $empleado, $contrato_data);
            } else {
                $contrato_data['contrato_texto'] = $this->generar_texto_legacy($empleado, $contrato_data);
            }
        } else {
            $contrato_data['contrato_texto'] = $this->generar_texto_legacy($empleado, $contrato_data);
        }
        
        return $this->insert_contrato($contrato_data);
    }

    /**
     * Obtiene el historial de contratos de un empleado
     */
    public function get_historial($empleado_id, $fecha_inicio = null, $fecha_fin = null) {
        $this->db->where('empleado_id', $empleado_id);
        
        if ($fecha_inicio) {
            $this->db->where('DATE(fecha_creacion) >=', $fecha_inicio);
        }
        
        if ($fecha_fin) {
            $this->db->where('DATE(fecha_creacion) <=', $fecha_fin);
        }
        
        return $this->db->order_by('version', 'DESC')
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
     * Procesa una plantilla reemplazando placeholders con datos reales
     */
    public function procesar_plantilla($contenido, $empleado, $contrato_data = []) {
        $reemplazos = $this->obtener_reemplazos($empleado, $contrato_data);
        return str_replace(array_keys($reemplazos), array_values($reemplazos), $contenido);
    }
    
    /**
     * Define el mapa de placeholders disponible
     */
    public function obtener_reemplazos($empleado, $contrato_data = []) {
        // Datos por defecto si contrato_data está incompleto
        $version = $contrato_data['version'] ?? '?';
        $tipo_c = $contrato_data['tipo_contrato'] ?? 'Indefinido';
        $puesto = $contrato_data['puesto'] ?? $empleado->puesto;
        $salario_m = $contrato_data['salario_base_mensual'] ?? $empleado->salario_base_mensual;
        $salario_d = $contrato_data['salario_base_diario'] ?? $empleado->salario_base_diario;

        // Calcular edad
        $edad = $this->EmpleadoModel->calcular_edad($empleado->fecha_nacimiento) ?? 'N/D';

        // Beneficiarios formato texto
        $beneficiarios = $empleado->beneficiarios ?: 'No designados';
        $domicilio = $empleado->direccion ?: 'No registrado';
        $lugar_pago = $empleado->lugar_pago ?: 'Transferencia Bancaria a cuenta del trabajador';

        // Domicilio empresa (Buscar en la plantilla actual si existe, o usar default)
        $domicilio_empresa = $contrato_data['domicilio_empresa'] ?? 'DOMICILIO FISCAL NO REGISTRADO';

        // Datos de contacto adicionales
        $email = $empleado->email_personal ?? $empleado->email_corporativo ?? 'N/A';
        $telefono = $empleado->telefono ?? 'N/A';

        return [
            '{{version}}' => $version,
            '{{tipo_contrato}}' => $tipo_c,
            '{{nombre_completo}}' => $empleado->nombre . ' ' . $empleado->apellido_paterno . ' ' . $empleado->apellido_materno,
            '{{nacionalidad}}' => $empleado->nacionalidad ?: 'Mexicana',
            '{{edad}}' => $edad,
            '{{genero}}' => $empleado->genero ?: 'N/D',
            '{{sexo}}' => $empleado->genero ?: 'N/D', // Alias
            '{{estado_civil}}' => $empleado->estado_civil ?: 'Soltero(a)',
            '{{domicilio}}' => $domicilio,
            '{{rfc}}' => $empleado->rfc,
            '{{curp}}' => $empleado->curp,
            '{{nss}}' => $empleado->nss ?? 'N/A',
            '{{beneficiarios}}' => $beneficiarios,
            '{{numero_empleado}}' => $empleado->numero_empleado ?? 'N/A',
            '{{telefono}}' => $telefono,
            '{{email}}' => $email,
            '{{puesto}}' => $puesto,
            '{{departamento}}' => $contrato_data['departamento'] ?? 'N/A',
            '{{tipo_trabajador}}' => $contrato_data['tipo_trabajador'] ?? $empleado->tipo_trabajador,
            '{{salario_base_mensual}}' => '$' . number_format($salario_m, 2) . ' MXN',
            '{{salario_base_diario}}' => '$' . number_format($salario_d, 2) . ' MXN',
            '{{tipo_nomina}}' => $contrato_data['tipo_nomina'] ?? $empleado->tipo_nomina,
            '{{lugar_pago}}' => $lugar_pago,
            '{{jornada_laboral}}' => $contrato_data['jornada_laboral'] ?? 'Tiempo Completo',
            '{{fecha_inicio}}' => $contrato_data['fecha_inicio'] ?? date('Y-m-d'),
            '{{motivo_cambio}}' => $contrato_data['motivo_cambio'] ?? '',
            '{{ciudad_contrato}}' => $contrato_data['ciudad_contrato'] ?? 'CD. JUÁREZ, CHIHUAHUA',
            '{{color_corporativo}}' => $contrato_data['color_corporativo'] ?? '#1a3a5c',
            '{{logo_empresa}}' => !empty($contrato_data['logo']) 
                ? '<img src="' . base_url($contrato_data['logo']) . '" style="max-height:80px; max-width:180px;" alt="Logo Empresa">' 
                : '',
            '{{domicilio_empresa}}' => $domicilio_empresa,
            '{{fecha_generacion}}' => date('d/m/Y H:i:s'),
            '{{firma_empleado_espacio}}' => '<br><br><div style="border-top: 1px solid #000; width: 250px; text-align: center; padding-top: 5px; margin: 0 auto;">Firma del Trabajador<br><small>'.$empleado->nombre.' '.$empleado->apellido_paterno.'</small></div>',
            '{{firma_empresa_espacio}}' => '<br><br><div style="border-top: 1px solid #000; width: 250px; text-align: center; padding-top: 5px; margin: 0 auto;">Por la Empresa<br><small>Representante Legal</small></div>',
            '{{firma_testigo1}}' => '<br><br><div style="border-top:1px solid #999; width:200px; text-align:center; padding-top:5px; margin:0 auto;">TESTIGO<br><small>Nombre y Firma</small></div>',
            '{{firma_testigo2}}' => '<br><br><div style="border-top:1px solid #999; width:200px; text-align:center; padding-top:5px; margin:0 auto;">TESTIGO<br><small>Nombre y Firma</small></div>',
        ];
    }
    
    /**
     * Legacy generator (Backup)
     */
    private function generar_texto_legacy($empleado, $contrato_data) {
        $texto = "CONTRATO INDIVIDUAL DE TRABAJO\n\n";
        $texto .= "CONTRATO No. {$contrato_data['version']}\n";
        $texto .= "Tipo: {$contrato_data['tipo_contrato']}\n\n";
        $texto .= "Entre CHISA RECUBRIMIENTOS (en adelante \"LA EMPRESA\") y:\n\n";
        $texto .= "NOMBRE: {$empleado->nombre} {$empleado->apellido_paterno} {$empleado->apellido_materno}\n";
        $texto .= "RFC: {$empleado->rfc}\n";
        $texto .= "CURP: {$empleado->curp}\n";
        if ($empleado->nss) $texto .= "NSS: {$empleado->nss}\n";
        $texto .= "\nCLÁUSULAS:\n\n";
        $texto .= "PRIMERA.- PUESTO: {$contrato_data['puesto']}\n";
        $texto .= "SEGUNDA.- DEPARTAMENTO: {$contrato_data['departamento']}\n";
        $texto .= "TERCERA.- TIPO DE TRABAJADOR: {$contrato_data['tipo_trabajador']}\n";
        $texto .= "CUARTA.- SALARIO BASE MENSUAL: $" . number_format($contrato_data['salario_base_mensual'], 2) . " MXN\n";
        $texto .= "QUINTA.- SALARIO BASE DIARIO: $" . number_format($contrato_data['salario_base_diario'], 2) . " MXN\n";
        $texto .= "SEXTA.- TIPO DE NÓMINA: {$contrato_data['tipo_nomina']}\n";
        $texto .= "SÉPTIMA.- JORNADA LABORAL: {$contrato_data['jornada_laboral']}\n\n";
        $texto .= "FECHA DE INICIO: {$contrato_data['fecha_inicio']}\n";
        if ($contrato_data['motivo_cambio']) $texto .= "MOTIVO: {$contrato_data['motivo_cambio']}\n";
        $texto .= "\nFecha de generación: " . date('d/m/Y H:i:s');
        return $texto;
    }
}