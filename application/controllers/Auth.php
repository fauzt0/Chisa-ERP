<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

  protected $viewData    = [];
  protected $outputData  = [];

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
    $this->init_controller->insert_log("Ingreso al sistema",$user_post,"Ingreso correcto");
    $result = $this->UserModel->get_user_data_from_username($user_post);
    
    $this->_create_user_session($result, $user_post);
    redirect('dashboard'); //redireccionamos al dashboard

    return 0;
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