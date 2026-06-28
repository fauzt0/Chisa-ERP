<?php
/**
 * EmpleadoModel - Modelo de empleados para Recursos Humanos
 * 
 * Gestiona empleados con cumplimiento de regulaciones mexicanas:
 * - RFC, CURP, NSS (IMSS)
 * - Nómina base
 * - Datos laborales y fiscales
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class EmpleadoModel extends MY_Model {
    
    protected $tableName = 'empleados';
    
    /**
     * Configuración para DataTables
     */
    protected $datatableConfig = [
        'column_order' => ['numero_empleado', 'nombre', 'apellido_paterno', 'puesto', 'departamento_id', 'estatus', null],
        'column_search' => ['numero_empleado', 'nombre', 'apellido_paterno', 'apellido_materno', 'rfc', 'curp', 'puesto', 'email_personal', 'email_corporativo'],
        'order' => ['fecha_ingreso' => 'desc']
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    // ========================================================================
    // MÉTODOS CRUD
    // ========================================================================
    
    /**
     * Agrega un nuevo empleado
     */
    public function mod_add($data) {
        // Iniciar transacción
        $this->db->trans_start();
        
        // Generar número de empleado automático
        $data['numero_empleado'] = $this->generar_numero_empleado();
        
        // Calcular salario diario integrado
        if (isset($data['salario_base_mensual'])) {
            $data['salario_base_diario'] = $this->calcular_salario_diario($data['salario_base_mensual']);
        }
        
        // Agregar usuario que da de alta
        $data['usuario_alta_id'] = $this->session->userdata('user_id') ?? null;
        
        // Insertar empleado (automáticamente agrega fecha_alta y estatus=1)
        $id = $this->insert($data);
        
        // Crear contrato inicial
        if ($id) {
            $this->load->model('RH/ContratoModel');
            $this->ContratoModel->crear_contrato_inicial($id);
        }
        
        // Completar transacción
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE || !$id) {
            return $this->error_response("Error al agregar empleado");
        }
        
        return $this->success_response("Empleado agregado exitosamente con número: " . $data['numero_empleado'], ['id' => $id, 'numero_empleado' => $data['numero_empleado']]);
    }
    
    /**
     * Actualiza un empleado
     */
    public function mod_update($id, $data) {
        // Verificar que existe
        if (!$this->exists($id)) {
            return $this->not_found_response("El empleado no existe");
        }
        
        // Obtener datos anteriores para detectar cambios
        $empleado_anterior = $this->get_by_id($id);
        
        // Iniciar transacción
        $this->db->trans_start();
        
        // Recalcular salario diario si cambió el mensual
        if (isset($data['salario_base_mensual'])) {
            $data['salario_base_diario'] = $this->calcular_salario_diario($data['salario_base_mensual']);
        }
        
        // Agregar usuario que edita
        $data['usuario_edicion_id'] = $this->session->userdata('user_id') ?? null;
        
        // Actualizar (automáticamente agrega fecha_edicion)
        $this->update($id, $data);
        
        // Detectar cambios significativos para crear nuevo contrato
        $tipo_contrato = null;
        $motivo = '';
        
        if (isset($data['salario_base_mensual']) && $data['salario_base_mensual'] != $empleado_anterior->salario_base_mensual) {
            $tipo_contrato = 'Modificación Salarial';
            $motivo = 'Cambio de salario de $' . number_format($empleado_anterior->salario_base_mensual, 2) . ' a $' . number_format($data['salario_base_mensual'], 2);
        } elseif (isset($data['puesto']) && $data['puesto'] != $empleado_anterior->puesto) {
            $tipo_contrato = 'Cambio de Puesto';
            $motivo = 'Cambio de puesto de ' . $empleado_anterior->puesto . ' a ' . $data['puesto'];
        } elseif (isset($data['departamento_id']) && $data['departamento_id'] != $empleado_anterior->departamento_id) {
            $tipo_contrato = 'Cambio de Departamento';
            $motivo = 'Cambio de departamento';
        }
        
        // Crear nuevo contrato si hubo cambios significativos
        if ($tipo_contrato) {
            $this->load->model('RH/ContratoModel');
            $this->ContratoModel->crear_nuevo_contrato($id, $tipo_contrato, $motivo);
        }
        
        // Completar transacción
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return $this->error_response("Error al actualizar empleado");
        }
        
        return $this->success_response("Empleado actualizado correctamente");
    }
    
    /**
     * Da de baja un empleado (soft delete)
     */
    public function mod_delete($id, $motivo_baja = null) {
        // Verificar que existe y está activo
        if (!$this->exists_active($id)) {
            return $this->not_found_response("El empleado no existe o ya está inactivo");
        }
        
        // Preparar datos de baja
        $data = [
            'estatus' => 0,
            'fecha_baja' => date('Y-m-d'),
            'motivo_baja' => $motivo_baja
        ];
        
        $success = $this->update($id, $data, false);
        
        if ($success) {
            return $this->success_response("Empleado dado de baja correctamente");
        }
        
        return $this->error_response("Error al dar de baja al empleado");
    }
    
    /**
     * Reactiva un empleado
     */
    public function mod_restore($id) {
        $empleado = $this->get_by_id($id);
        
        if (empty($empleado)) {
            return $this->not_found_response("El empleado no existe");
        }
        
        if ($empleado->estatus == 1) {
            return $this->error_response("El empleado ya está activo");
        }
        
        $data = [
            'estatus' => 1,
            'fecha_baja' => null,
            'motivo_baja' => null
        ];
        
        $success = $this->update($id, $data, false);
        
        if ($success) {
            return $this->success_response("Empleado reactivado correctamente");
        }
        
        return $this->error_response("Error al reactivar empleado");
    }
    
    // ========================================================================
    // MÉTODOS AUXILIARES
    // ========================================================================
    
    /**
     * Genera número de empleado único
     * Formato: EMP-YYYY-####
     */
    private function generar_numero_empleado() {
        $year = date('Y');
        
        $ultimo = $this->db->select('numero_empleado')
                          ->like('numero_empleado', "EMP-$year-", 'after')
                          ->order_by('id', 'DESC')
                          ->limit(1)
                          ->get($this->tableName)
                          ->row();
        
        if ($ultimo) {
            $numero = intval(substr($ultimo->numero_empleado, -4)) + 1;
        } else {
            $numero = 1;
        }
        
        return sprintf("EMP-%s-%04d", $year, $numero);
    }
    
    /**
     * Calcula salario diario integrado
     * Fórmula básica: Salario mensual / 30 días
     */
    public function calcular_salario_diario($salario_mensual) {
        return round($salario_mensual / 30, 2);
    }
    
    /**
     * Calcula edad a partir de fecha de nacimiento
     */
    public function calcular_edad($fecha_nacimiento) {
        if (empty($fecha_nacimiento)) return null;
        $dob = new DateTime($fecha_nacimiento);
        $now = new DateTime();
        return $now->diff($dob)->y;
    }
    
    // ========================================================================
    // VALIDACIONES MÉXICO
    // ========================================================================
    
    /**
     * Valida RFC mexicano
     * Persona Física: 13 caracteres (AAAA######XXX)
     * Persona Moral: 12 caracteres (AAA######XXX)
     */
    public function validar_rfc($rfc) {
        $rfc = strtoupper(trim($rfc));
        $patron = '/^[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}$/';
        return preg_match($patron, $rfc) === 1;
    }
    
    /**
     * Valida CURP mexicana
     * 18 caracteres
     */
    public function validar_curp($curp) {
        $curp = strtoupper(trim($curp));
        $patron = '/^[A-Z]{4}\d{6}[HM][A-Z]{5}[0-9A-Z]\d$/';
        return preg_match($patron, $curp) === 1;
    }
    
    /**
     * Valida NSS (IMSS)
     * 11 dígitos
     */
    public function validar_nss($nss) {
        $nss = trim($nss);
        return preg_match('/^\d{11}$/', $nss) === 1;
    }
    
    // ========================================================================
    // CONSULTAS ESPECÍFICAS
    // ========================================================================
    
    /**
     * Obtiene empleados por departamento
     */
    public function get_by_departamento($departamento_id, $solo_activos = true) {
        $where = ['departamento_id' => $departamento_id];
        
        if ($solo_activos) {
            return $this->get_active($where);
        }
        
        return $this->get_all($where);
    }
    
    /**
     * Obtiene empleado con datos de departamento
     */
    public function get_empleado_completo($id) {
        $this->db->select('empleados.*, departamentos.nombre as departamento_nombre');
        $this->db->from($this->tableName);
        $this->db->join('departamentos', 'departamentos.id = empleados.departamento_id', 'left');
        $this->db->where('empleados.id', $id);
        return $this->db->get()->row();
    }
    
    /**
     * Obtiene lista de empleados para select (jefe directo)
     */
    public function get_lista_empleados_activos() {
        return $this->db->select("id, numero_empleado, nombre, apellido_paterno, apellido_materno, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo")
                        ->where('estatus', 1)
                        ->order_by('nombre', 'ASC')
                        ->get($this->tableName)
                        ->result();
    }
    
    // ========================================================================
    // ESTADÍSTICAS
    // ========================================================================
    
    /**
     * Obtiene estadísticas de RH
     */
    public function get_estadisticas_rh() {
        $stats = [];
        
        // Total de empleados
        $stats['total_empleados'] = $this->db->count_all_results($this->tableName);
        
        // Empleados activos
        $stats['empleados_activos'] = $this->db->where('estatus', 1)
                                               ->count_all_results($this->tableName);
        
        // Empleados inactivos
        $stats['empleados_inactivos'] = $this->db->where('estatus', 0)
                                                 ->count_all_results($this->tableName);
        
        // Por tipo de trabajador
        $stats['por_tipo'] = $this->db->select('tipo_trabajador, COUNT(*) as total')
                                      ->where('estatus', 1)
                                      ->group_by('tipo_trabajador')
                                      ->get($this->tableName)
                                      ->result();
        
        // Nuevos ingresos (30 días)
        $fecha_limite = date('Y-m-d', strtotime('-30 days'));
        $stats['nuevos_ingresos'] = $this->db->where('fecha_ingreso >=', $fecha_limite)
                                             ->where('estatus', 1)
                                             ->count_all_results($this->tableName);
        
        // Nómina total mensual
        $nomina = $this->db->select('SUM(salario_base_mensual) as total')
                          ->where('estatus', 1)
                          ->get($this->tableName)
                          ->row();
        $stats['nomina_total'] = $nomina->total ?? 0;
        
        // Porcentaje de activos
        $stats['porcentaje_activos'] = $stats['total_empleados'] > 0 
            ? round(($stats['empleados_activos'] / $stats['total_empleados']) * 100, 1) 
            : 0;
        
        return $stats;
    }
    
    /**
     * Obtiene empleados por departamento para estadísticas
     */
    public function get_empleados_por_departamento() {
        return $this->db->select('departamentos.nombre as departamento, COUNT(empleados.id) as total')
                        ->from($this->tableName)
                        ->join('departamentos', 'departamentos.id = empleados.departamento_id', 'left')
                        ->where('empleados.estatus', 1)
                        ->group_by('departamentos.id')
                        ->order_by('total', 'DESC')
                        ->get()
                        ->result();
    }

    /**
     * Obtiene empleados activos con datos fiscales faltantes (RFC, CURP, NSS)
     */
    public function get_empleados_datos_faltantes() {
        $empleados = $this->db->select("id, nombre, apellido_paterno, apellido_materno, rfc, curp, nss, email_corporativo, telefono, fecha_nacimiento, genero")
                        ->where('estatus', 1)
                        ->group_start()
                            ->where('rfc IS NULL', null, false)
                            ->or_where('rfc', '')
                            ->or_where('curp IS NULL', null, false)
                            ->or_where('curp', '')
                            ->or_where('nss IS NULL', null, false)
                            ->or_where('nss', '')
                            ->or_where('email_corporativo IS NULL', null, false)
                            ->or_where('email_corporativo', '')
                            ->or_where('fecha_nacimiento IS NULL', null, false)
                            ->or_where('fecha_nacimiento', '')
                        ->group_end()
                        ->get($this->tableName)
                        ->result();

        $resultado = [];
        $campos_revisar = [
            'rfc' => 'RFC',
            'curp' => 'CURP',
            'nss' => 'NSS',
            'email_corporativo' => 'Email Corp.',
            'fecha_nacimiento' => 'Fecha Nac.',
        ];

        foreach ($empleados as $emp) {
            $faltantes = [];
            foreach ($campos_revisar as $campo => $etiqueta) {
                if (empty($emp->$campo)) {
                    $faltantes[] = $etiqueta;
                }
            }
            if (!empty($faltantes)) {
                $resultado[] = [
                    'id' => $emp->id,
                    'nombre' => $emp->nombre . ' ' . $emp->apellido_paterno . ' ' . $emp->apellido_materno,
                    'numero_empleado' => $emp->id,
                    'faltantes' => $faltantes,
                    'total_faltantes' => count($faltantes),
                    'rfc' => $emp->rfc,
                    'curp' => $emp->curp,
                    'nss' => $emp->nss,
                    'email' => $emp->email_corporativo,
                    'fecha_nacimiento' => $emp->fecha_nacimiento,
                ];
            }
        }

        return $resultado;
    }
    
    /**
     * Sobrescribe el método de DataTables para incluir JOIN con departamentos
     */
    protected function _get_datatables_query() {
        $this->db->select('empleados.*, departamentos.nombre as departamento_nombre');
        $this->db->from($this->tableName);
        $this->db->join('departamentos', 'departamentos.id = empleados.departamento_id', 'left');
        
        // Búsqueda general
        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $search = $_POST['search'];
            if (is_array($search)) {
                $search = $search['value'];
            }
            
            if (!empty($search)) {
                $this->db->group_start();
                foreach ($this->datatableConfig['column_search'] as $i => $column) {
                    if ($i === 0) {
                        $this->db->like($column, $search);
                    } else {
                        $this->db->or_like($column, $search);
                    }
                }
                $this->db->group_end();
            }
        }

        // Filtro de Estatus (si existe en POST)
        if (isset($_POST['estatus']) && $_POST['estatus'] !== 'all') {
            $this->db->where('empleados.estatus', $_POST['estatus']);
        }

        // Filtro de Departamento (si existe en POST)
        if (isset($_POST['departamento_id']) && $_POST['departamento_id'] !== 'all') {
            $this->db->where('empleados.departamento_id', $_POST['departamento_id']);
        }
        
        // Ordenamiento
        if (isset($_POST['order'])) {
            $column_index = $_POST['order'][0]['column'];
            $column_name = $this->datatableConfig['column_order'][$column_index];
            $direction = $_POST['order'][0]['dir'];
            $this->db->order_by($column_name, $direction);
        } elseif (isset($this->datatableConfig['order'])) {
            $order = $this->datatableConfig['order'];
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }
}
