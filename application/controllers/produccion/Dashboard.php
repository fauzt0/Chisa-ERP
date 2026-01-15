<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {
    
    protected $modulo = 'Producción';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Produccion/ProduccionModel');
        
        // El controlador base ya maneja la sesión y los permisos del módulo
    }
    
    /**
     * Vista principal del dashboard de producción
     */
    public function index() {
        setViewSuccess('Dashboard de Producción cargado correctamente');
        $this->viewData['pageTitle'] = 'Dashboard de Producción';
        $this->viewData['headTitle'] = 'Dashboard de Producción';
        $this->viewData['breadcrumb'] = 'Inicio > Producción > Dashboard';
        
        // Obtener filtros de la petición
        $filtros = [];
        
        // Búsqueda
        $busqueda = $this->input->get_post('busqueda');
        if($busqueda) {
            $filtros['busqueda'] = $busqueda;
        }
        
        // Estatus (puede ser array de checkboxes)
        $estatus = $this->input->get_post('estatus');
        if($estatus && is_array($estatus) && !empty($estatus)) {
            $filtros['estatus'] = $estatus;
        }
        
        // Obtener estadísticas y órdenes con filtros
        $stats = $this->ProduccionModel->get_estadisticas();
        $ordenes = $this->ProduccionModel->get_ordenes_dashboard($filtros);
        
        $this->viewData['response'] = [
            'stats' => $stats,
            'ordenes' => $ordenes,
            'filtros_activos' => $filtros // Para mantener estado en la vista
        ];
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'produccion/dashboard/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene órdenes para el dashboard (AJAX)
     */
    public function get_ordenes_ajax() {
        $filtro = $this->input->get('filtro') ?: 'todas';
        
        $ordenes = $this->ProduccionModel->get_ordenes_dashboard($filtro);
        
        echo json_encode([
            'success' => true,
            'ordenes' => $ordenes
        ]);
    }
    
    /**
     * Vista detallada de una orden de venta o obra con productos y formulaciones
     */
    public function detalle($tipo, $id) {
        setViewSuccess('Detalle cargado correctamente');
        
        if($tipo === 'orden_venta' || $tipo === 'orden') {
            // Orden de venta
            $registro = $this->ProduccionModel->get_orden_venta_detalle($id);
            $titulo = 'Orden ' . ($registro ? $registro->folio : '');
            $tipo_display = 'Orden de Venta';
        } else if($tipo === 'obra') {
            // Obra
            $registro = $this->ProduccionModel->get_obra_detalle($id);
            $titulo = 'Obra ' . ($registro ? $registro->folio : '');
            $tipo_display = 'Obra';
        } else {
            show_404();
            return;
        }
        
        if(!$registro) {
            show_404();
            return;
        }
        
        $this->viewData['pageTitle'] = 'Detalle - ' . $titulo;
        $this->viewData['headTitle'] = 'Detalle de ' . $tipo_display;
        $this->viewData['breadcrumb'] = 'Inicio > Producción > Dashboard > Detalle';
        
        $this->viewData['response'] = [
            'registro' => $registro,
            'tipo' => $tipo
        ];
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'produccion/dashboard/detalle';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Actualiza el estatus de una orden de venta (AJAX)
     */
    public function actualizar_estatus_ajax() {
        $orden_id = $this->input->post('orden_id');
        $nuevo_estatus = $this->input->post('estatus');
        
        if(!$orden_id || !$nuevo_estatus) {
            echo json_encode([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
            return;
        }
        
        // Actualizar el estatus de la orden
        $this->db->where('id', $orden_id);
        $result = $this->db->update('ordenes_venta', [
            'estatus' => $nuevo_estatus
        ]);
        
        if($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Estatus actualizado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el estatus'
            ]);
        }
    }
    
    /**
     * Verifica si hay nuevas órdenes (AJAX - para actualización en tiempo real)
     */
    public function check_nuevas_ordenes_ajax() {
        $ultima_verificacion = $this->input->get('ultima_verificacion');
        $filtros_json = $this->input->get('filtros');
        
        // Decodificar filtros
        $filtros = $filtros_json ? json_decode($filtros_json, true) : [];
        
        // Obtener órdenes actuales con filtros
        $ordenes_actuales = $this->ProduccionModel->get_ordenes_dashboard($filtros);
        
        // Si hay timestamp de última verificación, buscar órdenes nuevas
        $ordenes_nuevas = [];
        if($ultima_verificacion) {
            foreach($ordenes_actuales as $orden) {
                $fecha_creacion_timestamp = strtotime($orden->fecha_creacion);
                if($fecha_creacion_timestamp > $ultima_verificacion) {
                    $ordenes_nuevas[] = $orden;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'hay_nuevas' => count($ordenes_nuevas) > 0,
            'cantidad_nuevas' => count($ordenes_nuevas),
            'ordenes_nuevas' => $ordenes_nuevas,
            'total_ordenes' => count($ordenes_actuales),
            'timestamp' => time()
        ]);
    }
    
    /**
     * Obtiene detalle de una formulación (AJAX)
     */
    public function get_formulacion_detalle_ajax() {
        $formulacion_id = $this->input->post('formulacion_id');
        
        if(!$formulacion_id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $this->load->model('Produccion/ProductosModel');
        $formulacion = $this->ProductosModel->get_formulacion_completa($formulacion_id);
        
        if($formulacion) {
            echo json_encode(['success' => true, 'formulacion' => $formulacion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Formulación no encontrada']);
        }
    }
}
