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
    // Cargar modelos necesarios
    $this->load->model('Ventas/VentasModel');
    $this->load->model('Produccion/ProduccionModel');
    $this->load->model('Ventas/ClientesModel');

    //preparamos los datos de la vista
    setViewSuccess('Dashboard cargado correctamente');
    $this->viewData['pageTitle'] = 'Dashboard';
    $this->viewData['headTitle'] = 'Dashboard';
    $this->viewData['breadcrumb'] = 'Inicio > Dashboard';

    // Obtener estadísticas reales
    $ventas_stats = $this->VentasModel->get_estadisticas();
    $produccion_stats = $this->ProduccionModel->get_estadisticas();
    $ultimas_ordenes = $this->VentasModel->get_ultimas_ordenes(5);
    
    // Obtener datos para la gráfica (Nuevos Clientes)
    $datos_grafica = $this->ClientesModel->get_nuevos_clientes_mensuales_anio();

    $this->viewData['response'] = [
        'ventas_stats' => $ventas_stats,
        'produccion_stats' => $produccion_stats,
        'ultimas_ordenes' => $ultimas_ordenes,
        'datos_grafica' => $datos_grafica // Array de 12 valores para cada mes
    ];
    $this->viewData['pageView'] = 'dashboards/mainDashboard';
    $this->viewData['pageScript'] = 'dashboards/mainDashboard_script';

    //render views
	  $this->load->view('layouts/general_template', $this->viewData);
	}
    
}
