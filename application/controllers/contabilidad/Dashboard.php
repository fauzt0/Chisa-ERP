<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
    
    public $viewData = [];
    public $outputData = [];
    
    public function __construct() {
        parent::__construct();
        $this->load->library("Init_controller");
        $this->load->model('Contabilidad/ContabilidadModel');
        
        // General viewdata for view files
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
     * Vista principal del dashboard de contabilidad
     */
    public function index() {
        setViewSuccess('Dashboard de Contabilidad cargado correctamente');
        $this->viewData['pageTitle'] = 'Dashboard de Contabilidad';
        $this->viewData['headTitle'] = 'Dashboard de Contabilidad';
        $this->viewData['breadcrumb'] = 'Inicio > Contabilidad > Dashboard';
        
        // Obtener periodo actual
        $periodo_actual = $this->ContabilidadModel->get_periodo_actual();
        $this->viewData['periodo_actual'] = $periodo_actual;
        
        // Obtener resumen financiero
        if($periodo_actual) {
            $resumen = $this->ContabilidadModel->get_resumen_financiero($periodo_actual->id);
            $this->viewData['resumen_financiero'] = $resumen;
        } else {
            $this->viewData['resumen_financiero'] = [
                'ingresos' => 0,
                'egresos' => 0,
                'utilidad' => 0
            ];
        }
        
        // Obtener pólizas pendientes
        $this->viewData['polizas_pendientes'] = $this->ContabilidadModel->get_polizas_pendientes();
        
        // Cargar vista
        $this->viewData['pageView'] = 'contabilidad/dashboard/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene datos para gráficas (AJAX)
     */
    public function get_datos_graficas_ajax() {
        $ejercicio = $this->ContabilidadModel->get_ejercicio_actual();
        
        if(!$ejercicio) {
            echo json_encode(['success' => false, 'message' => 'No hay ejercicio activo']);
            return;
        }
        
        // Obtener periodos del ejercicio
        $periodos = $this->ContabilidadModel->get_periodos_ejercicio($ejercicio->id);
        
        $datos_ingresos = [];
        $datos_egresos = [];
        $labels = [];
        
        foreach($periodos as $periodo) {
            $resumen = $this->ContabilidadModel->get_resumen_financiero($periodo->id);
            
            $labels[] = $periodo->nombre;
            $datos_ingresos[] = $resumen['ingresos'];
            $datos_egresos[] = $resumen['egresos'];
        }
        
        echo json_encode([
            'success' => true,
            'labels' => $labels,
            'ingresos' => $datos_ingresos,
            'egresos' => $datos_egresos
        ]);
    }
}
