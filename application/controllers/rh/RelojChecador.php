<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RelojChecador - Controlador Web del Módulo de Reloj Checador Biométrico
 * 
 * Gestiona la interfaz gráfica para:
 * - Dashboard con estadísticas del módulo
 * - CRUD de dispositivos ZKTeco
 * - Cola de comandos para los relojes
 * - Historial de sincronización
 * - Reportes de asistencia (diario/mensual)
 * - Cálculo de asistencias (cruce con horarios)
 * 
 * @package ChisaERP
 * @subpackage Reloj
 * @author Chisa Recubrimientos
 * @version 1.0
 */
class RelojChecador extends MY_Controller {

    protected $modulo = 'Reloj Checador';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Reloj/RelojModel');
        $this->load->model('Reloj/RelojSyncRhModel');
        $this->load->model('RH/EmpleadoModel');
        $this->load->model('RH/HorariosModel');
        $this->load->model('RH/DepartamentoModel');
        $this->load->helper('httpstatus_helper');
    }

    // ========================================================================
    // VISTA PRINCIPAL — DASHBOARD
    // ========================================================================

    /**
     * Muestra el dashboard con estadísticas generales del módulo
     * Permiso requerido: reloj_ver_dashboard
     */
    public function index()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_ver_dashboard')) {
            setViewError('No tienes permiso para acceder a esta sección.');
            redirect('deny');
        }

        setViewSuccess('Módulo de Reloj Checador cargado correctamente');

        $this->viewData['pageTitle']   = 'Reloj Checador';
        $this->viewData['headTitle']   = 'Dashboard del Reloj Checador';
        $this->viewData['breadcrumb']  = 'Inicio > Reloj Checador';

        // Estadísticas del dashboard
        $stats = $this->RelojModel->get_estadisticas_dashboard();
        $dispositivos = $this->RelojModel->get_dispositivos(true);
        $ultimo_sync = $this->RelojModel->get_sync_log(10);
        $ultimas_checadas = $this->RelojModel->get_ultimas_checadas(10);
        foreach ($ultimas_checadas as &$c) {
            $c->metodo_html = $this->RelojModel->badge_metodo_checada_html($c->metodo);
        }
        unset($c);

        foreach ($dispositivos as &$d) {
            $d->online = $this->RelojModel->dispositivo_esta_online($d->ultima_conexion);
        }
        unset($d);

        $dispositivos_online = count(array_filter($dispositivos, function ($d) {
            return !empty($d->online);
        }));

        $user_id = $this->session->userdata('id');
        $this->viewData['response'] = [
            'stats'               => $stats,
            'dispositivos'        => $dispositivos,
            'dispositivos_online' => $dispositivos_online,
            'ultimo_sync'      => $ultimo_sync,
            'ultimas_checadas' => $ultimas_checadas,
            'permisos'         => [
                'reportes'      => $this->init_controller->has_permission($user_id, 'reloj_ver_reportes'),
                'gestionar'     => $this->init_controller->has_permission($user_id, 'reloj_gestionar'),
                'sync_empleados'=> $this->init_controller->has_permission($user_id, 'reloj_sync_empleados_rh'),
            ],
        ];

        $this->viewData['validate']  = '';
        $this->viewData['pageView']  = 'rh/reloj_checador/dashboard';
        $this->viewData['pageScript']= 'rh/reloj_checador/dashboard_scripts';

        $this->load->view('layouts/general_template', $this->viewData);
    }

    /**
     * Stats del dashboard en tiempo real (AJAX)
     * Permiso: reloj_ver_dashboard
     */
    public function dashboard_stats_ajax()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_ver_dashboard')) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'No autorizado']));
            return;
        }

        $stats = $this->RelojModel->get_estadisticas_dashboard();
        $dispositivos = $this->RelojModel->get_dispositivos(true);
        $ultimas_checadas = $this->RelojModel->get_ultimas_checadas(10);

        $dispositivos_status = [];
        foreach ($dispositivos as $d) {
            $dispositivos_status[] = [
                'sn'      => $d->sn,
                'alias'   => $d->alias,
                'online'  => $this->RelojModel->dispositivo_esta_online($d->ultima_conexion),
                'ultima'  => $d->ultima_conexion,
            ];
        }

        $this->output->set_output(json_encode([
            'success'          => true,
            'stats'            => $stats,
            'dispositivos'     => $dispositivos_status,
            'ultimas_checadas' => array_map(function ($c) {
                return [
                    'numero_empleado' => $c->numero_empleado ?? $c->usuario_id,
                    'empleado_nombre' => $c->empleado_nombre ?? ('PIN ' . $c->usuario_id),
                    'hora'            => date('H:i:s', strtotime($c->fecha_hora)),
                    'metodo'          => (int)$c->metodo,
                    'metodo_html'     => $this->RelojModel->badge_metodo_checada_html($c->metodo),
                ];
            }, $ultimas_checadas),
        ]));
    }

    // ========================================================================
    // GESTIÓN DE DISPOSITIVOS
    // ========================================================================

    /**
     * Vista de gestión de dispositivos
     * Permiso requerido: reloj_gestionar
     */
    public function dispositivos()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_gestionar')) {
            setViewError('No tienes permiso para acceder a esta sección.');
            redirect('deny');
        }

        $this->viewData['pageTitle']   = 'Dispositivos';
        $this->viewData['headTitle']   = 'Gestión de Dispositivos ZKTeco';
        $this->viewData['breadcrumb']  = 'Inicio > Reloj Checador > Dispositivos';
        $this->viewData['validate']    = '';
        $this->viewData['pageView']    = 'rh/reloj_checador/dispositivos';
        $this->viewData['pageScript']  = 'rh/reloj_checador/dispositivos_scripts';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    /**
     * DataTables SSR — Listado de dispositivos (AJAX)
     */
    public function search_dispositivos()
    {
        $list = $this->RelojModel->get_dispositivos_datatables();
        $data = [];
        $no = $_POST['start'];

        foreach ($list as $d) {
            $no++;
            $row = [];

            $row[] = $d->sn;
            $row[] = $d->alias ?: '<span class="text-muted">—</span>';
            $row[] = $d->ubicacion ?: '<span class="text-muted">—</span>';

            $ultima_conexion = $d->ultima_conexion
                ? '<span title="' . htmlspecialchars($d->ultima_conexion) . '">' . date('d/m/Y H:i', strtotime($d->ultima_conexion)) . '</span>'
                : '<span class="text-muted">Nunca</span>';
            $row[] = $ultima_conexion;

            if ($d->activo == 1) {
                $row[] = '<span class="badge bg-success">Activo</span>';
            } else {
                $row[] = '<span class="badge bg-danger">Inactivo</span>';
            }

            $acciones = '
                <button type="button" class="btn btn-sm btn-primary" onclick="ver_dispositivo(' . $d->id . ')" title="Ver detalle">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-warning" onclick="editar_dispositivo(' . $d->id . ')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminar_dispositivo(' . $d->id . ')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>';
            $row[] = $acciones;

            $data[] = $row;
        }

        $output = [
            "draw"            => $_POST['draw'],
            "recordsTotal"    => $this->RelojModel->count_dispositivos_all(),
            "recordsFiltered" => $this->RelojModel->count_dispositivos_filtered(),
            "data"            => $data,
        ];

        echo json_encode($output);
    }

    /**
     * Obtiene detalle de un dispositivo (AJAX)
     */
    public function dispositivo_detail()
    {
        $id = $this->input->post('id');
        $dispositivo = $this->db
            ->where('id', $id)
            ->get('reloj_dispositivos')
            ->row();

        if (!$dispositivo) {
            echo json_encode(['success' => false, 'message' => 'Dispositivo no encontrado']);
            return;
        }

        // Estadísticas del dispositivo
        $comandos_stats = $this->RelojModel->get_estadisticas_comandos($dispositivo->sn);

        $asistencias_hoy = $this->db
            ->where('dispositivo_sn', $dispositivo->sn)
            ->where('DATE(fecha_hora)', date('Y-m-d'))
            ->count_all_results('asistencias');

        echo json_encode([
            'success'      => true,
            'detalle'      => $dispositivo,
            'comandos'     => $comandos_stats,
            'checadas_hoy' => $asistencias_hoy,
        ]);
    }

    /**
     * Guarda un dispositivo (crear o actualizar) — AJAX
     */
    public function guardar_dispositivo()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_gestionar')) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'No tienes permiso para realizar esta acción.']));
            return;
        }

        $id = $this->input->post('id');
        $sn = trim($this->input->post('sn'));
        $alias = trim($this->input->post('alias'));
        $ubicacion = trim($this->input->post('ubicacion'));
        $activo = $this->input->post('activo') ? 1 : 0;

        if (empty($sn)) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'El número de serie (SN) es obligatorio']));
            return;
        }

        if ($id) {
            $ok = $this->db->where('id', $id)->update('reloj_dispositivos', [
                'sn'        => $sn,
                'alias'     => $alias,
                'ubicacion' => $ubicacion,
                'activo'    => $activo,
            ]);

            if (!$ok) {
                $this->output->set_output(json_encode([
                    'success' => false,
                    'message' => 'No se pudo actualizar el dispositivo',
                ]));
                return;
            }

            $this->output->set_output(json_encode(['success' => true, 'message' => 'Dispositivo actualizado correctamente']));
            return;
        }

        $existe = $this->db->where('sn', $sn)->get('reloj_dispositivos')->row();
        if ($existe) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'El SN ya está registrado']));
            return;
        }

        $token = bin2hex(random_bytes(32));

        $ok = $this->db->insert('reloj_dispositivos', [
            'sn'         => $sn,
            'alias'      => $alias,
            'ubicacion'  => $ubicacion,
            'api_token'  => $token,
            'activo'     => $activo,
            'fecha_alta' => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {
            $db_error = $this->db->error();
            $message = !empty($db_error['message'])
                ? 'Error al guardar: ' . $db_error['message']
                : 'Error al guardar el dispositivo en la base de datos';

            $this->output->set_output(json_encode(['success' => false, 'message' => $message]));
            return;
        }

        $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Dispositivo creado correctamente',
            'token'   => $token,
        ]));
    }

    /**
     * Elimina (desactiva) un dispositivo — AJAX
     */
    public function eliminar_dispositivo()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_gestionar')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso.']);
            return;
        }

        $id = $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $this->db->where('id', $id)->update('reloj_dispositivos', ['activo' => 0]);
        echo json_encode(['success' => true, 'message' => 'Dispositivo desactivado correctamente']);
    }

    /**
     * Regenera el token API de un dispositivo — AJAX
     */
    public function regenerar_token()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_gestionar')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso.']);
            return;
        }

        $id = $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $token = bin2hex(random_bytes(32));
        $this->db->where('id', $id)->update('reloj_dispositivos', ['api_token' => $token]);

        echo json_encode(['success' => true, 'message' => 'Token regenerado correctamente', 'token' => $token]);
    }

    // ========================================================================
    // COLA DE COMANDOS
    // ========================================================================

    /**
     * Vista de cola de comandos
     * Permiso requerido: reloj_gestionar
     */
    public function comandos()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_gestionar')) {
            setViewError('No tienes permiso para acceder a esta sección.');
            redirect('deny');
        }

        $dispositivos = $this->RelojModel->get_dispositivos(true);

        $this->viewData['pageTitle']   = 'Comandos';
        $this->viewData['headTitle']   = 'Cola de Comandos';
        $this->viewData['breadcrumb']  = 'Inicio > Reloj Checador > Comandos';
        $this->viewData['validate']    = '';
        $this->viewData['response']    = ['dispositivos' => $dispositivos];
        $this->viewData['pageView']    = 'rh/reloj_checador/comandos';
        $this->viewData['pageScript']  = 'rh/reloj_checador/comandos_scripts';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    /**
     * DataTables SSR — Listado de comandos (AJAX)
     */
    public function search_comandos()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        $list = $this->RelojModel->get_comandos_datatables();
        $data = [];
        $no = isset($_POST['start']) ? (int)$_POST['start'] : 0;

        foreach ($list as $c) {
            $no++;
            $row = [];

            $row[] = $c->dispositivo_sn;
            $comando_vista = str_replace($this->RelojModel->adms_tab(), '⇥', $c->comando);
            $row[] = '<code title="⇥ = tabulador ADMS">' . htmlspecialchars(substr($comando_vista, 0, 80)) . (strlen($comando_vista) > 80 ? '...' : '') . '</code>';

            // Badge de estado
            switch ($c->estado) {
                case 'pendiente':
                    $badge = '<span class="badge bg-warning text-dark">Pendiente</span>';
                    break;
                case 'enviado':
                    $badge = '<span class="badge bg-info">Enviado</span>';
                    break;
                case 'ejecutado':
                    $badge = '<span class="badge bg-success">Ejecutado</span>';
                    break;
                case 'fallido':
                    $badge = '<span class="badge bg-danger">Fallido</span>';
                    break;
                default:
                    $badge = '<span class="badge bg-secondary">' . $c->estado . '</span>';
                    break;
            }
            $row[] = $badge;

            $row[] = (int)$c->intentos;

            // Respuesta (truncada)
            $respuesta = $c->respuesta
                ? '<span title="' . htmlspecialchars($c->respuesta) . '">' . htmlspecialchars(substr($c->respuesta, 0, 50)) . (strlen($c->respuesta) > 50 ? '...' : '') . '</span>'
                : '<span class="text-muted">—</span>';
            $row[] = $respuesta;

            $row[] = $c->fecha_creacion ? date('d/m/Y H:i', strtotime($c->fecha_creacion)) : '—';
            $row[] = $c->fecha_ejecucion ? date('d/m/Y H:i', strtotime($c->fecha_ejecucion)) : '—';
            $row[] = $c->creado_por_usuario ?: '<span class="text-muted">API</span>';

            $data[] = $row;
        }

        $output = [
            "draw"            => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
            "recordsTotal"    => $this->RelojModel->count_comandos_all(),
            "recordsFiltered" => $this->RelojModel->count_comandos_filtered(),
            "data"            => $data,
        ];

        $this->output->set_output(json_encode($output));
    }

    /**
     * Vacía toda la cola reloj_comandos (AJAX) — permiso reloj_gestionar
     */
    public function vaciar_todos_comandos()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_gestionar')) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'No autorizado']));
            return;
        }

        $n = $this->RelojModel->vaciar_todos_comandos();
        $this->output->set_output(json_encode([
            'success'   => true,
            'message'   => 'Cola vaciada: ' . $n . ' comando(s) eliminado(s)',
            'eliminados' => $n,
        ]));
    }

    /**
     * Encola un nuevo comando manualmente — AJAX
     */
    public function encolar_comando()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_gestionar')) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'No tienes permiso.']));
            return;
        }

        $dispositivo_sn = $this->input->post('dispositivo_sn');
        $comando = trim($this->input->post('comando'));

        if (empty($dispositivo_sn) || empty($comando)) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']));
            return;
        }

        $user_id = $this->session->userdata('id');
        $resultado = $this->RelojModel->encolar_comando($dispositivo_sn, $comando, $user_id);

        if ($resultado) {
            $this->output->set_output(json_encode(['success' => true, 'message' => 'Comando encolado correctamente']));
        } else {
            $msg = $this->RelojModel->get_ultimo_error_encolar() ?: 'Error al encolar el comando';
            $this->output->set_output(json_encode(['success' => false, 'message' => $msg]));
        }
    }

    // ========================================================================
    // HISTORIAL DE SINCRONIZACIÓN
    // ========================================================================

    /**
     * Vista de historial de sincronización
     * Permiso requerido: reloj_ver_dashboard
     */
    public function sync_log()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_ver_dashboard')) {
            setViewError('No tienes permiso para acceder a esta sección.');
            redirect('deny');
        }

        $dispositivos = $this->RelojModel->get_dispositivos(true);

        $this->viewData['pageTitle']   = 'Historial de Sincronización';
        $this->viewData['headTitle']   = 'Bitácora de Sincronización';
        $this->viewData['breadcrumb']  = 'Inicio > Reloj Checador > Sincronización';
        $this->viewData['validate']    = '';
        $this->viewData['response']    = ['dispositivos' => $dispositivos];
        $this->viewData['pageView']    = 'rh/reloj_checador/sync_log';
        $this->viewData['pageScript']  = 'rh/reloj_checador/sync_log_scripts';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    /**
     * DataTables SSR — Historial de sincronización (AJAX)
     */
    public function search_sync_log()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        $list = $this->RelojModel->get_sync_log_datatables();
        $data = [];
        $no = isset($_POST['start']) ? (int)$_POST['start'] : 0;

        foreach ($list as $s) {
            $no++;
            $row = [];

            $row[] = $s->dispositivo_sn ?: '<span class="text-muted">—</span>';

            // Badge de tipo
            switch ($s->tipo) {
                case 'asistencias':
                    $badge_tipo = '<span class="badge bg-success">Asistencias</span>';
                    break;
                case 'comandos':
                    $badge_tipo = '<span class="badge bg-info">Comandos</span>';
                    break;
                case 'resultado':
                    $badge_tipo = '<span class="badge bg-primary">Resultado</span>';
                    break;
                case 'conexion':
                    $badge_tipo = '<span class="badge bg-secondary">Conexión</span>';
                    break;
                case 'error':
                    $badge_tipo = '<span class="badge bg-danger">Error</span>';
                    break;
                default:
                    $badge_tipo = '<span class="badge bg-dark">' . $s->tipo . '</span>';
                    break;
            }
            $row[] = $badge_tipo;

            $row[] = $s->payload_resumen ?: '<span class="text-muted">—</span>';
            $row[] = $s->registros_afectados ?: 0;
            $row[] = $s->ip_origen ?: '—';
            $row[] = date('d/m/Y H:i:s', strtotime($s->fecha));

            $data[] = $row;
        }

        $output = [
            "draw"            => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
            "recordsTotal"    => $this->RelojModel->count_sync_log_all(),
            "recordsFiltered" => $this->RelojModel->count_sync_log_filtered(),
            "data"            => $data,
        ];

        $this->output->set_output(json_encode($output));
    }

    // ========================================================================
    // REPORTES DE ASISTENCIA
    // ========================================================================

    /**
     * Reporte diario de asistencias
     * Permiso requerido: reloj_ver_reportes
     */
    public function reporte_diario()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_ver_reportes')) {
            setViewError('No tienes permiso para acceder a esta sección.');
            redirect('deny');
        }

        $empleados = $this->EmpleadoModel->get_lista_empleados_activos();
        $departamentos = $this->DepartamentoModel->get_lista_departamentos();

        $this->viewData['pageTitle']   = 'Reporte Diario';
        $this->viewData['headTitle']   = 'Reporte Diario de Asistencias';
        $this->viewData['breadcrumb']  = 'Inicio > Reloj Checador > Reporte Diario';
        $this->viewData['validate']    = '';
        $this->viewData['response']    = [
            'empleados'     => $empleados,
            'departamentos' => $departamentos,
        ];
        $this->viewData['pageView']    = 'rh/reloj_checador/reporte_diario';
        $this->viewData['pageScript']  = 'rh/reloj_checador/reporte_diario_scripts';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    /**
     * DataTables SSR — Resumen diario por empleado (AJAX)
     */
    public function search_asistencias_diario()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_ver_reportes')) {
            $this->output->set_output(json_encode([
                'draw'            => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'No autorizado',
            ]));
            return;
        }

        $list = $this->RelojModel->get_resumen_diario_datatables();
        $data = [];

        foreach ($list as $r) {
            $retardo_html = $r->retardo
                ? '<span class="text-danger fw-semibold">' . (int)$r->minutos_retardo . ' min</span>'
                : '<span class="text-muted">—</span>';

            $data[] = [
                htmlspecialchars($r->numero_empleado),
                htmlspecialchars($r->empleado_nombre),
                htmlspecialchars($r->departamento_nombre ?: '—'),
                $r->entrada ?: '<span class="text-muted">—</span>',
                $r->salida_comida ?: '<span class="text-muted">—</span>',
                $r->entrada_comida ?: '<span class="text-muted">—</span>',
                $r->salida ?: '<span class="text-muted">—</span>',
                $this->RelojModel->badge_estado_asistencia_html($r->estado),
                $retardo_html,
                '<strong>' . htmlspecialchars($r->horas_trabajadas) . '</strong>',
                (int)$r->empleado_id,
            ];
        }

        $output = [
            'draw'            => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
            'recordsTotal'    => $this->RelojModel->count_resumen_diario_all(),
            'recordsFiltered' => $this->RelojModel->count_resumen_diario_filtered(),
            'data'            => $data,
        ];

        $this->output->set_output(json_encode($output));
    }

    /**
     * Reporte mensual de asistencias (resumen por empleado)
     * Permiso requerido: reloj_ver_reportes
     */
    public function reporte_mensual()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_ver_reportes')) {
            setViewError('No tienes permiso para acceder a esta sección.');
            redirect('deny');
        }

        $empleados = $this->EmpleadoModel->get_lista_empleados_activos();
        $departamentos = $this->DepartamentoModel->get_lista_departamentos();
        $meses = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
            '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
            '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
            '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre',
        ];

        $this->viewData['pageTitle']   = 'Reporte Mensual';
        $this->viewData['headTitle']   = 'Reporte Mensual de Asistencias';
        $this->viewData['breadcrumb']  = 'Inicio > Reloj Checador > Reporte Mensual';
        $this->viewData['validate']    = '';
        $this->viewData['response']    = [
            'empleados'     => $empleados,
            'departamentos' => $departamentos,
            'meses'         => $meses,
            'anio_actual'   => date('Y'),
        ];
        $this->viewData['pageView']    = 'rh/reloj_checador/reporte_mensual';
        $this->viewData['pageScript']  = 'rh/reloj_checador/reporte_mensual_scripts';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    /**
     * DataTables SSR — Resumen mensual de asistencias (AJAX)
     */
    public function search_asistencias_mensual()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        $list = $this->RelojModel->get_asistencias_mensual_datatables();
        $db_error = $this->db->error();
        if (!empty($db_error['code'])) {
            $this->output->set_output(json_encode([
                'draw'            => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => $db_error['message'],
            ]));
            return;
        }
        $data = [];
        $no = isset($_POST['start']) ? (int)$_POST['start'] : 0;

        foreach ($list as $e) {
            $no++;
            $row = [];

            $row[] = $e->numero_empleado;
            $row[] = $e->empleado_nombre;
            $row[] = $e->puesto ?: '<span class="text-muted">—</span>';
            $row[] = $e->departamento_nombre ?: '<span class="text-muted">—</span>';
            $row[] = (int)$e->dias_trabajados;
            $row[] = (int)$e->dias_laborales;

            // Porcentaje de asistencia
            $pct = ($e->dias_laborales > 0)
                ? round(($e->dias_trabajados / $e->dias_laborales) * 100, 1)
                : 0;
            $bar_color = $pct >= 90 ? 'bg-success' : ($pct >= 75 ? 'bg-warning' : 'bg-danger');
            $row[] = '<div class="d-flex align-items-center">
                <div class="progress progress-sm flex-fill me-2" style="min-width: 60px;">
                    <div class="progress-bar ' . $bar_color . '" role="progressbar" style="width: ' . $pct . '%"></div>
                </div>
                <small>' . $pct . '%</small>
            </div>';

            // Primera y última checada del mes
            $row[] = $e->primera_checada ?: '—';
            $row[] = $e->ultima_checada ?: '—';

            $data[] = $row;
        }

        $output = [
            "draw"            => isset($_POST['draw']) ? (int)$_POST['draw'] : 0,
            "recordsTotal"    => $this->RelojModel->count_asistencias_mensual_all(),
            "recordsFiltered" => $this->RelojModel->count_asistencias_mensual_filtered(),
            "data"            => $data,
        ];

        $this->output->set_output(json_encode($output));
    }

    // ========================================================================
    // CÁLCULO DE ASISTENCIAS (AJAX)
    // ========================================================================

    /**
     * Obtiene asistencia detallada de un empleado en un día (AJAX)
     * Incluye cruce con horario para detectar retardos/entradas/salidas
     */
    public function asistencia_detalle_dia()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_ver_reportes')) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            return;
        }

        $empleado_id = $this->input->post('empleado_id');
        $fecha = $this->input->post('fecha') ?: date('Y-m-d');

        if (!$empleado_id) {
            echo json_encode(['success' => false, 'message' => 'Empleado requerido']);
            return;
        }

        // Obtener checadas del día
        $checadas = $this->RelojModel->get_asistencias_rango($fecha, $fecha, $empleado_id);

        // Obtener horario del empleado para el día de la semana
        $horarios = $this->HorariosModel->get_horario_empleado($empleado_id);
        $dia_semana_es = [
            'Monday'    => 'Lunes',
            'Tuesday'   => 'Martes',
            'Wednesday' => 'Miércoles',
            'Thursday'  => 'Jueves',
            'Friday'    => 'Viernes',
            'Saturday'  => 'Sábado',
            'Sunday'    => 'Domingo',
        ];
        $dia_actual = $dia_semana_es[date('l', strtotime($fecha))];

        $horario_hoy = null;
        foreach ($horarios as $h) {
            if ($h->dia_semana === $dia_actual) {
                $horario_hoy = $h;
                break;
            }
        }

        // Calcular asistencia diaria
        $calculo = $this->RelojModel->calcular_asistencia_diaria($empleado_id, $fecha, $horario_hoy);
        $checadas_etiquetadas = $this->RelojModel->etiquetar_checadas_secuencia($checadas);

        echo json_encode([
            'success'    => true,
            'fecha'      => $fecha,
            'dia_semana' => $dia_actual,
            'checadas'   => $checadas,
            'checadas_etiquetadas' => $checadas_etiquetadas,
            'horario'    => $horario_hoy,
            'calculo'    => $calculo,
        ]);
    }

    // ========================================================================
    // SINCRONIZACIÓN FORZADA EMPLEADOS RH → RELOJ
    // ========================================================================

    /**
     * Vista: sincronización forzada de empleados activos hacia el reloj
     * Permiso: reloj_sync_empleados_rh
     */
    public function sync_empleados_rh()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_sync_empleados_rh')) {
            setViewError('No tienes permiso para acceder a esta sección.');
            redirect('deny');
        }

        $dispositivos = $this->RelojModel->get_dispositivos(true);
        $preview = $this->RelojSyncRhModel->generar_preview();

        $this->viewData['pageTitle']   = 'Sync Empleados RH';
        $this->viewData['headTitle']   = 'Sincronización Forzada — Empleados RH';
        $this->viewData['breadcrumb']  = 'Inicio > Reloj Checador > Sync Empleados RH';
        $this->viewData['validate']    = '';
        $this->viewData['response']    = [
            'dispositivos'      => $dispositivos,
            'preview'           => $preview,
            'campos_reloj_ok'   => $this->RelojSyncRhModel->empleados_tiene_campos_reloj(),
            'pin_admin'         => RelojModel::PIN_ADMIN_RELOJ,
        ];
        $this->viewData['pageView']    = 'rh/reloj_checador/sync_empleados_rh';
        $this->viewData['pageScript']  = 'rh/reloj_checador/sync_empleados_rh_scripts';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    /**
     * Aplica columnas reloj_* en empleados (AJAX)
     */
    public function aplicar_migracion_sync_empleados_rh()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_sync_empleados_rh')) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'No autorizado']));
            return;
        }

        $result = $this->RelojSyncRhModel->aplicar_migracion_empleados_reloj();
        $this->output->set_output(json_encode($result));
    }

    /**
     * Vista previa AJAX (empleados + conteo de borrados)
     */
    public function preview_sync_empleados_rh()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_sync_empleados_rh')) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'No autorizado']));
            return;
        }

        $preview = $this->RelojSyncRhModel->generar_preview();
        $preview['success'] = true;
        $preview['campos_reloj_ok'] = $this->RelojSyncRhModel->empleados_tiene_campos_reloj();

        $this->output->set_output(json_encode($preview));
    }

    /**
     * Vacía cola pendiente/enviada del dispositivo (AJAX)
     */
    /**
     * Reencola comandos en estado enviado (proxy no reportó resultado)
     */
    public function reencolar_cola_sync_empleados_rh()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_sync_empleados_rh')) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'No autorizado']));
            return;
        }

        $dispositivo_sn = trim((string)$this->input->post('dispositivo_sn'));
        if ($dispositivo_sn === '') {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Seleccione un dispositivo']));
            return;
        }

        $corregidos = $this->RelojModel->corregir_fallidos_con_respuesta_exitosa($dispositivo_sn);
        $n = $this->RelojModel->reencolar_comandos_enviados($dispositivo_sn);
        $msg = $n . ' devuelto(s) a pendiente';
        if ($corregidos > 0) {
            $msg .= '; ' . $corregidos . ' marcado(s) como ejecutado (respuesta OK)';
        }
        $this->output->set_output(json_encode([
            'success'     => true,
            'message'     => $msg,
            'reencolados' => $n,
            'corregidos'  => $corregidos,
        ]));
    }

    public function vaciar_cola_sync_empleados_rh()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_sync_empleados_rh')) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'No autorizado']));
            return;
        }

        $dispositivo_sn = trim((string)$this->input->post('dispositivo_sn'));
        if ($dispositivo_sn === '') {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Seleccione un dispositivo']));
            return;
        }

        $n = $this->RelojModel->vaciar_cola_comandos_dispositivo($dispositivo_sn);
        $this->output->set_output(json_encode([
            'success' => true,
            'message' => 'Cola vaciada: ' . $n . ' comando(s) eliminado(s)',
            'eliminados' => $n,
        ]));
    }

    /**
     * Encola DATA USER por empleado activo (PIN = numero_empleado, formato proxy planta)
     */
    public function ejecutar_sync_empleados_rh()
    {
        $this->output->set_content_type('application/json', 'utf-8');

        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_sync_empleados_rh')) {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'No autorizado']));
            return;
        }

        $dispositivo_sn = $this->input->post('dispositivo_sn');
        $creado_por = $this->session->userdata('id');

        $result = $this->RelojSyncRhModel->ejecutar_sincronizacion_forzada($dispositivo_sn, $creado_por);

        $this->output->set_output(json_encode($result));
    }

    // ========================================================================
    // EXPORTACIÓN
    // ========================================================================

    /**
     * Exporta asistencias diarias a CSV
     */
    public function exportar_diario_csv()
    {
        if (!$this->init_controller->has_permission($this->session->userdata('id'), 'reloj_ver_reportes')) {
            show_error('No autorizado', 403);
            return;
        }

        $fecha = $this->input->get('fecha') ?: date('Y-m-d');
        $empleado_id = $this->input->get('empleado_id');
        $departamento_id = $this->input->get('departamento_id');
        $estado = $this->input->get('estado');

        $resumen = $this->RelojModel->get_resumen_diario_empleados(
            $fecha,
            $departamento_id ?: null,
            $empleado_id ?: null,
            $estado ?: null
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="asistencias_resumen_' . $fecha . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'No. Empleado', 'Nombre', 'Departamento', 'Entrada', 'Salida comida',
            'Entrada comida', 'Salida', 'Estado', 'Retardo (min)', 'Horas trabajadas', 'Checadas',
        ]);

        foreach ($resumen as $r) {
            fputcsv($output, [
                $r->numero_empleado,
                $r->empleado_nombre,
                $r->departamento_nombre,
                $r->entrada ?: '',
                $r->salida_comida ?: '',
                $r->entrada_comida ?: '',
                $r->salida ?: '',
                $r->estado,
                $r->retardo ? $r->minutos_retardo : '',
                $r->horas_trabajadas,
                $r->total_checadas,
            ]);
        }

        fclose($output);
        exit;
    }
}
