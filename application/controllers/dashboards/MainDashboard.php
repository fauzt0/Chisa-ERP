<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MainDashboard extends CI_Controller {
    
  public $viewData = [];
  public $outputData = [];
  
  public function __construct() 
  {
    parent::__construct();
    //cargamos los proceso de inicio
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


	public function index()
	{
    //preparamos los datos de la vista
    setViewSuccess('Dashboard cargado correctamente');
    $this->viewData['pageTitle'] = 'Dashboard';
    $this->viewData['headTitle'] = 'Dashboard';
    $this->viewData['breadcrumb'] = 'Inicio > Dashboard';
    $this->viewData['response'] = [];//no hay datos por el momento  
    $this->viewData['pageView'] = 'dashboards/mainDashboard';
    $this->viewData['pageScript'] = 'dashboards/mainDashboard_script';

    //render views
	  $this->load->view('layouts/general_template', $this->viewData);
	}
    
}
