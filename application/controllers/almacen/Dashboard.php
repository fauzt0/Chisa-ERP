<?php
/**
 * Dashboard - Controlador del dashboard de almacén
 * Muestra resumen de inventario, alertas y últimos movimientos
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
    
    public $viewData = [];
    public $outputData = [];
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Almacen/AlmacenModel');
        
        // Verificar sesión
        /*if(!$this->session->userdata('logged_in')) {
            redirect('login');
        }*/
        
        // Inicializar viewData
        $this->viewData = [
            'success'     => true,
            'statusCode'  => get_status_code_by_result('emptyresult'),
            'message'     => 'Respuesta sin contenido',  
            'error'       => '',
            'pageTitle'   => '',      
            'headTitle'   => '',   
            'pageView'    => '',
            'pageScript'  => '',
            'breadcrumb'  => '',
            'validate'    => '',
            'response'    => [],
        ];
        
        $this->outputData = [ 
            'success'     => true,  
            'statusCode'  => get_status_code_by_result('emptyresult'),
            'message'     => 'Respuesta sin contenido',
            'error'       => '',      
            'response'    => [],
        ];
    }
    
    /**
     * Vista principal del dashboard
     */
    public function index() {
        // Preparar datos de la vista
        $this->viewData['pageTitle'] = 'Dashboard de Almacén';
        $this->viewData['headTitle'] = 'Dashboard de Almacén';
        $this->viewData['breadcrumb'] = 'Almacén > Dashboard';
        
        // Obtener datos
        $resumen = $this->AlmacenModel->get_resumen_inventario();
        $ultimos_movimientos = $this->AlmacenModel->get_ultimos_movimientos(10);
        
        $this->viewData['response'] = [
            'resumen' => $resumen,
            'ultimos_movimientos' => $ultimos_movimientos
        ];
        
        $this->viewData['pageView'] = 'almacen/dashboard/main';
        
        // Render view
        $this->load->view('layouts/general_template', $this->viewData);
    }
}
