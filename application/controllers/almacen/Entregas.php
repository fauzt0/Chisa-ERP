<?php
/**
 * Entregas - Controlador de entregas de almacén
 * Maneja entregas de órdenes de venta y obras
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Entregas extends CI_Controller {
    
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
     * Vista principal de entregas
     */
    public function index() {
        // Preparar datos de la vista
        $this->viewData['pageTitle'] = 'Entregas de Almacén';
        $this->viewData['headTitle'] = 'Entregas de Almacén';
        $this->viewData['breadcrumb'] = 'Almacén > Entregas';
        
        // Obtener datos
        $ordenes_pendientes = $this->AlmacenModel->get_ordenes_pendientes();
        $obras_pendientes = $this->AlmacenModel->get_obras_pendientes();
        
        $this->viewData['response'] = [
            'ordenes_pendientes' => $ordenes_pendientes,
            'obras_pendientes' => $obras_pendientes
        ];
        
        $this->viewData['pageView'] = 'almacen/entregas/main';
        
        // Render view
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene detalle de orden para entrega (AJAX)
     */
    public function get_orden_detalle_ajax() {
        $orden_id = $this->input->post('orden_id');
        
        $orden = $this->AlmacenModel->get_orden_detalle($orden_id);
        
        if(!$orden) {
            echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
            return;
        }
        
        echo json_encode(['success' => true, 'orden' => $orden]);
    }
    
    /**
     * Obtiene detalle de obra para entrega (AJAX)
     */
    public function get_obra_detalle_ajax() {
        $obra_id = $this->input->post('obra_id');
        
        $obra = $this->AlmacenModel->get_obra_detalle($obra_id);
        
        if(!$obra) {
            echo json_encode(['success' => false, 'message' => 'Obra no encontrada']);
            return;
        }
        
        echo json_encode(['success' => true, 'obra' => $obra]);
    }
    
    /**
     * Registra entrega de orden de venta (AJAX)
     */
    public function entregar_orden_ajax() {
        $orden_id = $this->input->post('orden_id');
        $productos = $this->input->post('productos');
        $observaciones = $this->input->post('observaciones');
        
        // Validar
        if(empty($productos)) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar al menos un producto']);
            return;
        }
        
        // Preparar datos
        $data = [
            'tipo_origen' => 'Orden Venta',
            'orden_venta_id' => $orden_id,
            'usuario_id' => $this->session->userdata('user_id'),
            'observaciones' => $observaciones,
            'productos' => []
        ];
        
        // Procesar productos
        foreach($productos as $prod) {
            if($prod['cantidad_entregar'] > 0) {
                $data['productos'][] = [
                    'detalle_orden_id' => $prod['detalle_orden_id'],
                    'producto_id' => $prod['producto_id'],
                    'cantidad_entregar' => $prod['cantidad_entregar']
                ];
            }
        }
        
        if(empty($data['productos'])) {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar cantidades a entregar']);
            return;
        }
        
        // Registrar entrega
        $result = $this->AlmacenModel->registrar_entrega($data);
        
        echo json_encode($result);
    }
    
    /**
     * Registra entrega de obra (AJAX)
     */
    public function entregar_obra_ajax() {
        $obra_id = $this->input->post('obra_id');
        $productos = $this->input->post('productos');
        $observaciones = $this->input->post('observaciones');
        
        // Validar
        if(empty($productos)) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar al menos un producto']);
            return;
        }
        
        // Preparar datos
        $data = [
            'tipo_origen' => 'Obra',
            'obra_id' => $obra_id,
            'usuario_id' => $this->session->userdata('user_id'),
            'observaciones' => $observaciones,
            'productos' => []
        ];
        
        // Procesar productos
        foreach($productos as $prod) {
            if($prod['cantidad_entregar'] > 0) {
                $data['productos'][] = [
                    'obra_producto_id' => $prod['obra_producto_id'],
                    'producto_id' => $prod['producto_id'],
                    'cantidad_entregar' => $prod['cantidad_entregar']
                ];
            }
        }
        
        if(empty($data['productos'])) {
            echo json_encode(['success' => false, 'message' => 'Debe ingresar cantidades a entregar']);
            return;
        }
        
        // Registrar entrega
        $result = $this->AlmacenModel->registrar_entrega($data);
        
        echo json_encode($result);
    }
    
    /**
     * Obtiene historial de entregas (AJAX)
     */
    public function get_historial_ajax() {
        $filtros = [
            'tipo_origen' => $this->input->post('tipo_origen'),
            'fecha_desde' => $this->input->post('fecha_desde'),
            'fecha_hasta' => $this->input->post('fecha_hasta')
        ];
        
        $entregas = $this->AlmacenModel->get_historial_entregas($filtros);
        
        echo json_encode(['success' => true, 'entregas' => $entregas]);
    }
}
