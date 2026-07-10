<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Perfil extends MY_Controller {

  protected $modulo = 'Mi Perfil';

  public function __construct() {
    parent::__construct();
    $this->load->model('Users/UserModel');
    $this->load->model('RH/EmpleadoModel');
    $this->load->model('RH/VacacionesModel');
  }

  public function index() {
    setViewSuccess('Mi Perfil cargado correctamente');
    $this->viewData['pageTitle'] = 'Mi Perfil';
    $this->viewData['headTitle'] = 'Mi Información Personal';
    $this->viewData['breadcrumb'] = 'Inicio > Mi Perfil';

    $user_id = $this->session->userdata('id');
    $user = $this->UserModel->mod_get_user_from_id($user_id);

    $empleado = null;
    $vacaciones = null;
    $solicitudes = [];
    $asistencia = null;
    $comunicacion = null;
    $mensaje_vinculo = '';

    if (!empty($user->empleado_id)) {
        $empleado = $this->EmpleadoModel->get_empleado_completo($user->empleado_id);
        if ($empleado) {
            $vacaciones = $this->VacacionesModel->get_balance_actual($user->empleado_id);
            $solicitudes = $this->VacacionesModel->get_solicitudes_empleado($user->empleado_id);
            $asistencia = $this->_get_resumen_asistencia_empleado($user->empleado_id, $solicitudes);
            if (in_array((int)$empleado->estatus, [1, 2], true)) {
                $this->load->model('RH/ComunicacionModel');
                $comunicacion = $this->ComunicacionModel->get_resumen($user->empleado_id);
            }
        } else {
            $mensaje_vinculo = 'Tu usuario está vinculado a un expediente de empleado que ya no existe. Contacta a RRHH.';
        }
    } else {
        $mensaje_vinculo = 'Tu usuario no está vinculado a un expediente de empleado. Para solicitar vacaciones desde aquí, pide a un administrador que te vincule en Recursos Humanos.';
    }

    $this->viewData['response'] = [
        'user' => $user,
        'empleado' => $empleado,
        'vacaciones' => $vacaciones,
        'solicitudes' => $solicitudes,
        'asistencia' => $asistencia,
        'comunicacion' => $comunicacion,
        'mensaje_vinculo' => $mensaje_vinculo,
    ];

    $this->viewData['pageView'] = 'usuarios/perfil/main_perfil';
    $this->load->view('layouts/general_template', $this->viewData);
  }

  /**
   * Actualiza nombre, email (username) y contraseña del usuario en sesión
   */
  public function actualizar_cuenta() {
    $user_id = $this->session->userdata('id');
    $data = [
        'nombre' => trim($this->input->post('nombre')),
        'apellidos' => trim($this->input->post('apellidos')),
        'username' => trim($this->input->post('username')),
    ];
    $password = $this->input->post('password');
    $password_confirm = $this->input->post('password_confirm');

    if (empty($data['nombre']) || empty($data['apellidos']) || empty($data['username'])) {
        echo json_encode(['success' => false, 'message' => 'Nombre, apellidos y correo son obligatorios.']);
        return;
    }

    if ($password !== '' || $password_confirm !== '') {
        if ($password !== $password_confirm) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            return;
        }
        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            return;
        }
        $data['password'] = $password;
    }

    $result = $this->UserModel->update_perfil($user_id, $data);
    if (!empty($result['success'])) {
        $this->session->set_userdata([
            'name' => $data['nombre'],
            'email' => $data['username'],
        ]);
    }
    echo json_encode([
        'success' => !empty($result['success']),
        'message' => $result['msg'] ?? ($result['message'] ?? 'Error al guardar'),
    ]);
  }

  /**
   * Procesa la solicitud de vacaciones desde Mi Perfil
   */
  public function solicitar_vacaciones() {
    $user_id = $this->session->userdata('id');
    $user = $this->UserModel->mod_get_user_from_id($user_id);

    if (empty($user->empleado_id)) {
        echo json_encode(['success' => false, 'message' => 'No estás vinculado a un empleado.']);
        return;
    }

    $fecha_inicio = $this->input->post('fecha_inicio');
    $fecha_fin = $this->input->post('fecha_fin');
    $observaciones = $this->input->post('observaciones');

    if (!$fecha_inicio || !$fecha_fin) {
        echo json_encode(['success' => false, 'message' => 'Fechas requeridas.']);
        return;
    }

    $periodo = $this->VacacionesModel->get_balance_actual($user->empleado_id);

    if (!$periodo) {
        echo json_encode(['success' => false, 'message' => 'No hay período de vacaciones activo. Verifica tu fecha de ingreso con RRHH.']);
        return;
    }

    $dias = $this->VacacionesModel->calcular_dias_habiles($fecha_inicio, $fecha_fin);

    if ($dias <= 0) {
        echo json_encode(['success' => false, 'message' => 'El rango de fechas no contiene días hábiles.']);
        return;
    }

    if ($dias > $periodo->dias_disponibles) {
        echo json_encode(['success' => false, 'message' => "No tienes suficientes días. Solicitas $dias y tienes {$periodo->dias_disponibles}."]);
        return;
    }

    $data = [
        'empleado_id' => $user->empleado_id,
        'periodo_vacaciones_id' => $periodo->id,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'dias_solicitados' => $dias,
        'observaciones' => $observaciones,
    ];

    echo json_encode($this->VacacionesModel->solicitar_vacaciones($data));
  }

  /**
   * Resumen de asistencia para Mi Perfil (empleados vinculados al usuario en sesión).
   */
  private function _get_resumen_asistencia_empleado($empleado_id, $solicitudes = [])
  {
    $this->load->model('Reloj/RelojModel');
    $this->load->model('RH/HorariosModel');
    $this->load->model('RH/IncidenciasModel');

    $hoy = date('Y-m-d');
    $inicio_mes = date('Y-m-01');
    $inicio_30 = date('Y-m-d', strtotime('-30 days'));

    $mapa_dia = [
      'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles',
      'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo',
    ];
    $dia_es = $mapa_dia[date('l')] ?? '';

    $horario_hoy = null;
    foreach ($this->HorariosModel->get_horario_empleado($empleado_id) as $h) {
      if ($h->dia_semana === $dia_es && (int)$h->es_dia_laboral === 1) {
        $horario_hoy = $h;
        break;
      }
    }

    $hoy_calc = $this->RelojModel->calcular_asistencia_diaria($empleado_id, $hoy, $horario_hoy);

    $checadas_mes = $this->RelojModel->get_asistencias_rango($inicio_mes, $hoy, $empleado_id);
    $dias_con_checada = [];
    $ultima = null;
    foreach ($checadas_mes as $c) {
      $dias_con_checada[date('Y-m-d', strtotime($c->fecha_hora))] = true;
      if (!$ultima || strtotime($c->fecha_hora) > strtotime($ultima)) {
        $ultima = $c->fecha_hora;
      }
    }

    $solicitudes_pendientes = 0;
    foreach ($solicitudes as $s) {
      if (($s->estatus ?? '') === 'Pendiente') {
        $solicitudes_pendientes++;
      }
    }

    $incidencias_stats = $this->IncidenciasModel->get_estadisticas_empleado($empleado_id);
    $incidencias_mes = $this->IncidenciasModel->get_incidencias_empleado($empleado_id, [
      'fecha_desde' => $inicio_mes,
      'fecha_hasta' => $hoy,
      'estatus' => 'Activa',
    ]);

    return [
      'hoy' => $hoy_calc,
      'horario_hoy' => $horario_hoy,
      'dias_trabajados_mes' => count($dias_con_checada),
      'total_checadas_30' => $this->RelojModel->contar_checadas_empleado($empleado_id, $inicio_30, $hoy),
      'ultima_checada' => $ultima,
      'ultima_checada_fmt' => $ultima ? date('d/m/Y H:i', strtotime($ultima)) : null,
      'incidencias_mes' => count($incidencias_mes),
      'incidencias_anio' => (int)($incidencias_stats['total'] ?? 0),
      'solicitudes_pendientes' => $solicitudes_pendientes,
    ];
  }
}
