<?php
/**
 * Inventario - Controlador de inventario consolidado
 * Muestra insumos y productos con opción de ajustes
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Inventario extends MY_Controller {
    
    protected $modulo = 'Almacén';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Almacen/AlmacenModel');
        $this->load->model('Produccion/ProductosModel');
        
        // El controlador base ya maneja la sesión y los permisos del módulo
    }
    
    /**
     * Vista principal de inventario
     */
    public function index() {
        // Preparar datos de la vista
        $this->viewData['pageTitle'] = 'Inventario';
        $this->viewData['headTitle'] = 'Inventario';
        $this->viewData['breadcrumb'] = 'Almacén > Inventario';
        
        // Obtener datos
        $insumos = $this->AlmacenModel->get_insumos();
        $productos = $this->AlmacenModel->get_productos();
        
        $this->viewData['response'] = [
            'insumos' => $insumos,
            'productos' => $productos
        ];
        
        $this->viewData['pageView'] = 'almacen/inventario/main';
        
        // Render view
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Ajusta stock de producto (AJAX)
     */
    public function ajustar_stock_ajax() {
        $producto_id = $this->input->post('producto_id');
        $tipo_movimiento = $this->input->post('tipo_movimiento');
        $cantidad = $this->input->post('cantidad');
        $motivo = $this->input->post('motivo');
        $observaciones = $this->input->post('observaciones');
        
        // Validar
        if(empty($producto_id) || empty($cantidad) || $cantidad <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        // Preparar datos
        $data = [
            'producto_id' => $producto_id,
            'tipo_movimiento' => $tipo_movimiento,
            'cantidad' => $cantidad,
            'motivo' => $motivo . ($observaciones ? ': ' . $observaciones : ''),
            'usuario_id' => $this->session->userdata('user_id')
        ];
        
        // Registrar movimiento
        $result = $this->ProductosModel->registrar_movimiento($data);
        
        echo json_encode($result);
    }
}
