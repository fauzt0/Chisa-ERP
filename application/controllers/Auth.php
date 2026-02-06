<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

  public $viewData    = [];
  public $outputData  = [];

  public function __construct() {
    parent::__construct();
    //cargamos los proceso de inicio
    $this->load->library("Init_controller");//libreria de funciones generales
    $this->load->model("Users/UserModel"); // Load model globally for the controller

    //general viewdata for view files
    $this->viewData = [
      'success'     => true,
      'statusCode'  => get_status_code_by_result('emptyresult'), // 204
      'message'     => 'Respuesta sin contenido',  
      'error'       => '',
      'pageTitle'   => '',      
      'headTitle'   => '',   
      'pageView'    => '',
      'pageScript'  => '',
      'breadcrumb'  => '',//establecemos breadcrumbs para la vista en general               
      'validate'    => '',//validation messages or scripts injection from controllers (script, html, etc)
      'response'    => [], //empty response
    ];     

    $this->outputData = [
      'success'     => true,  
      'statusCode'  => get_status_code_by_result('emptyresult'), // 204
      'message'     => 'Respuesta sin contenido',
      'error'       => '',      
      'response'    => [], //empty response
    ];    
  }

  public function index() {
    //cargamos los datos de la vista
    $this->viewData['pageTitle'] = 'ERP/CHISA - Login';    
    //cargamos la vista principal
    $this->load->view('auth/login', $this->viewData);
  }

  public function authenticate(){ 
    //cargamos los datos de la vista
    $this->viewData['pageTitle'] = 'ERP/CHISA - Login';    
    
    $this->form_validation->set_rules('username', 'Email', 'required');
    $this->form_validation->set_rules('password', 'Contraseña', 'required');

    //validamos el formulario 
    if(!$this->form_validation->run())
    {
      $this->viewData['validate'] =  $this->init_controller->alert("danger","Ingrese usuario y contraseña");      
      $this->load->view('auth/login', $this->viewData);
      
      return 0; 
    }

    //validamos los datos de acceso
    $user_post = $this->input->post('username');
    $user_pass = $this->input->post('password');    
    
    if(!$this->UserModel->mod_resolve_login($user_post, $user_pass))
    {           
      $this->init_controller->insert_log("Intento de acceso erróneo",$user_post,"Ingreso erroneo");      
      $this->viewData['validate'] =  $this->init_controller->alert("danger","Usuario o contraseña erróneos");      
      $this->load->view('auth/login', $this->viewData);

      return 0; 
    }

    //en caso de exito, cargamos los datos del usuario
    $user_data = $this->UserModel->get_user_data_from_username($user_post);
    $current_ip = $this->input->ip_address();

    /** 
     * SEGURIDAD 2FA:
     * Si la IP actual no coincide con la última registrada, solicitamos 2FA
     * MODIFICACION: En development se salta este paso
     */
    if (ENVIRONMENT !== 'development' && $user_data->last_ip !== $current_ip && !empty($user_data->last_ip)) {
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->UserModel->mod_update_2fa($user_post, $code, $current_ip);
        
        // Enviamos el código por correo
        $this->_send_2fa_email($user_post, $code);
        
        // Marcamos en sesión que este email está pendiente de 2FA
        $this->session->set_userdata('pending_2fa_email', $user_post);
        
        redirect('Auth/verify_2fa');
        return;
    }

    // Si la IP es la misma o es el primer acceso, O estamos en desarrollo y saltamos el 2FA
    // Aseguramos que la IP quede registrada como válida/actual
    if (empty($user_data->last_ip) || (ENVIRONMENT === 'development' && $user_data->last_ip !== $current_ip)) {
        $this->UserModel->mod_update_2fa($user_post, NULL, $current_ip);
    }

    $this->init_controller->insert_log("Ingreso al sistema",$user_post,"Ingreso correcto");
    $this->_create_user_session($user_data, $user_post);
    redirect('dashboard'); 

    return 0;
  }

  /**
   * Vista de verificación de código 2FA
   */
  public function verify_2fa() {
      $email = $this->session->userdata('pending_2fa_email');
      if (!$email) {
          redirect('admin');
      }

      $this->viewData['pageTitle'] = 'ERP/CHISA - Verificación 2FA';
      $this->viewData['email_oculto'] = substr($email, 0, 3) . '****' . substr($email, strpos($email, '@'));
      $this->load->view('auth/verify_2fa', $this->viewData);
  }

  /**
   * Procesa el código 2FA ingresado
   */
  public function check_2fa() {
      $email = $this->session->userdata('pending_2fa_email');
      $code = $this->input->post('code');

      if (!$email || !$code) {
          redirect('admin');
      }

      if ($this->UserModel->mod_verify_2fa($email, $code)) {
          $user_data = $this->UserModel->get_user_data_from_username($email);
          $this->init_controller->insert_log("Verificación 2FA Correcta", $email, "Seguridad");
          
          $this->session->unset_userdata('pending_2fa_email');
          $this->_create_user_session($user_data, $email);
          redirect('dashboard');
      } else {
          $this->init_controller->insert_log("Código 2FA Incorrecto", $email, "Seguridad - Intento");
          $this->session->set_flashdata('error', 'El código ingresado es incorrecto o ha expirado.');
          redirect('Auth/verify_2fa');
      }
  }

  /**
   * Envía el código 2FA por correo electrónico
   */
  private function _send_2fa_email($email, $code) {
      $this->load->library('email');
      
      // Intentar enviar email (será efectivo si el servidor está configurado)
      $this->email->from('no-reply@chisarecubrimientos.com.mx', 'ERP CHISA Security');
      $this->email->to($email);
      $this->email->subject('Tu código de seguridad CHISA');
      $this->email->message("Tu código de verificación es: " . $code . "\nEste código expira en 10 minutos.");
      
      $this->email->send();
      
      /** 
       * FALLBACK/DEBUG:
       * Como no tenemos certeza de la configuración SMTP, registramos el envío en bitácora
       * para que el usuario pueda verlo en caso de fallo del servicio de correo.
       */
      $this->init_controller->insert_log("2FA Código Enviado a: " . $email . " (Código: " . $code . ")", $email, "Seguridad");
  }

  private function _create_user_session($user_data, $email){
    $create_session = [
        'id' => isset($user_data->id) ? (int) $user_data->id : 0,
        'role' => 1,
        'name' => $user_data->nombre ?? '',
        'email' => $email,
        'departamento' => $user_data->departamento ?? '',
    ];
    
    $this->session->set_userdata($create_session);
  }
  

  public function logout(){
    $array_items = array('id','role','name','email');
    $this->session->unset_userdata($array_items);
    $this->session->sess_destroy();
    redirect(base_url().'admin');
  }
  

}
?>