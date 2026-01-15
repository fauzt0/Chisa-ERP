<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MainDashboard extends MY_Controller {
    
  protected $modulo = 'dashboard_main';
  
  public function __construct() 
  {
    parent::__construct();
    
    // Sobrescribir o añadir claves específicas si es necesario
    $this->viewData['success'] = true;
    $this->viewData['statusCode'] = get_status_code_by_result('emptyresult');
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
