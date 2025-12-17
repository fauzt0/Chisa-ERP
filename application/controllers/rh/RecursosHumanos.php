<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class RecursosHumanos extends CI_Controller {

  public $viewData = [];
  public $outputData = [];
  
  public function __construct() 
  {
    parent::__construct();
    //cargamos los procesos, librerias, helpers y variables de inicio
    $this->load->library("Init_controller");//libreria de funciones generales
    $this->load->model("rh/RecursosHumanosModel"); //modelo de usuarios
    //$this->config->load('permissions'); // Load permissions configuration
    

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
    $this->session->email = "soporte2@chisarecubrimientos.com"; 

  }//end __construct
    

}