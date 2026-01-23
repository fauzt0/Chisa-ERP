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

    // Obtener ID del usuario actual
    $user_id = $this->session->userdata('id');
    
    // Obtener datos del usuario para ver si tiene empleado vinculado
    $user = $this->UserModel->mod_get_user_from_id($user_id);
    
    $empleado = null;
    $vacaciones = null;
    $mensaje_error = '';

    if (!empty($user->empleado_id)) {
        // Obtener datos del empleado
        $empleado = $this->EmpleadoModel->get_empleado_completo($user->empleado_id);
        
        if ($empleado) {
            // Obtener balance de vacaciones
            $vacaciones = $this->VacacionesModel->get_balance_actual($user->empleado_id);
            // Si no hay periodo, intentar generarlo (o mostrar vacío)
             if (!$vacaciones && $empleado->fecha_ingreso) {
                 // Lógica simple para ver si debería tener
                 // Por ahora solo null
             }
        } else {
            $mensaje_error = 'El usuario está vinculado a un empleado que no existe.';
        }
    } else {
        $mensaje_error = 'Tu usuario no está vinculado a ningún expediente de empleado. Contacta a RRHH.';
    }

    $this->viewData['response'] = [
        'user' => $user,
        'empleado' => $empleado,
        'vacaciones' => $vacaciones,
        'mensaje_error' => $mensaje_error
    ];
    
    $this->viewData['pageView'] = 'usuarios/perfil/main_perfil';
    $this->load->view('layouts/general_template', $this->viewData);
  }

  /**
   * Procesa la solicitud de vacaciones desde Mi Perfil
   */
  public function solicitar_vacaciones() {
    $user_id = $this->session->userdata('id');
    $user = $this->UserModel->mod_get_user_from_id($user_id);
    
    if(empty($user->empleado_id)) {
        echo json_encode(['success' => false, 'message' => 'No estás vinculado a un empleado.']);
        return;
    }

    $fecha_inicio = $this->input->post('fecha_inicio');
    $fecha_fin = $this->input->post('fecha_fin');
    $observaciones = $this->input->post('observaciones');

    if(!$fecha_inicio || !$fecha_fin) {
        echo json_encode(['success' => false, 'message' => 'Fechas requeridas.']);
        return;
    }

    // Obtener período actual
    $periodo = $this->VacacionesModel->get_balance_actual($user->empleado_id);
    
    if(!$periodo){
      echo json_encode(['success' => false, 'message' => 'No hay período de vacaciones activo o no tienes antigüedad suficiente.']);
      return;
    }

    // Calcular días habiles
    $dias = $this->VacacionesModel->calcular_dias_habiles($fecha_inicio, $fecha_fin);
    
    if($dias <= 0) {
        echo json_encode(['success' => false, 'message' => 'El rango de fechas no contiene días hábiles.']);
        return;
    }

    if($dias > $periodo->dias_disponibles) {
        echo json_encode(['success' => false, 'message' => "No tienes suficientes días disponibles. Solicitas $dias y tienes {$periodo->dias_disponibles}."]);
        return;
    }
    
    // Crear solicitud
    $data = [
      'empleado_id' => $user->empleado_id,
      'periodo_vacaciones_id' => $periodo->id,
      'fecha_inicio' => $fecha_inicio,
      'fecha_fin' => $fecha_fin,
      'dias_solicitados' => $dias,
      'observaciones' => $observaciones
    ];
    
    $result = $this->VacacionesModel->solicitar_vacaciones($data);
    
    echo json_encode($result);
  }
}
