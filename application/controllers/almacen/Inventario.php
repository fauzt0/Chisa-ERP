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
     *
     * Movimiento manual de inventario (Entrada/Salida) desde Almacén > Inventario.
     * Se valida en servidor el tipo permitido, la cantidad, el motivo obligatorio y el
     * usuario de sesión (clave `id`, ver Auth::_create_user_session()).
     */
    public function ajustar_stock_ajax() {
        $producto_id = (int) $this->input->post('producto_id');
        $tipo_movimiento = (string) $this->input->post('tipo_movimiento');
        $cantidad = $this->input->post('cantidad');
        $motivo = trim((string) $this->input->post('motivo'));
        $observaciones = trim((string) $this->input->post('observaciones'));
        $usuario_id = (int) $this->session->userdata('id');
        
        // Validar
        if(empty($producto_id) || !is_numeric($cantidad) || (float) $cantidad <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        if(!in_array($tipo_movimiento, ['Entrada', 'Salida'], true)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de movimiento no válido']);
            return;
        }
        
        if($motivo === '') {
            echo json_encode(['success' => false, 'message' => 'El motivo del ajuste es obligatorio']);
            return;
        }
        
        // Preparar datos
        $data = [
            'producto_id' => $producto_id,
            'tipo_movimiento' => $tipo_movimiento,
            'cantidad' => (float) $cantidad,
            'motivo' => $motivo . ($observaciones !== '' ? ': ' . $observaciones : ''),
            'usuario_id' => $usuario_id ?: null
        ];
        
        // Registrar movimiento
        $result = $this->ProductosModel->registrar_movimiento($data);
        
        // Informar el stock resultante para que el operador verifique la coherencia
        if(!empty($result['success'])) {
            $producto = $this->db->select('stock_actual')->where('id', $producto_id)->get('productos')->row();
            if($producto) {
                $result['stock_actual'] = (float) $producto->stock_actual;
                $result['message'] = 'Movimiento registrado. Stock nuevo: ' . number_format((float) $producto->stock_actual, 2);
            }
        }
        
        echo json_encode($result);
    }
}
