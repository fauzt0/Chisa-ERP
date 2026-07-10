<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller - Controlador base para seguridad global
 * 
 * Este controlador automatiza:
 * 1. Verificación de sesión activa.
 * 2. Verificación de permisos por módulo.
 * 3. Logging de accesos.
 */
class MY_Controller extends CI_Controller {

    public $viewData = [];
    public $outputData = [];
    protected $modulo = null; // Definir en cada controlador hijo

    public function __construct() {
        parent::__construct();
        
        // Cargar librerías necesarias
        $this->load->library('Init_controller');
        $this->load->model('Users/UserModel');

        // 1. Verificar Sesión (omitir en CLI — importaciones/cron desde terminal)
        if (!is_cli()) {
            $this->init_controller->check_session();

            // 2. Verificar Permisos del Módulo (si está definido)
            if ($this->modulo !== null) {
                $this->check_permissions();
            }
        }

        // Inicializar ViewData por defecto (estándar del proyecto)
        $this->viewData = [
            'pageTitle' => '',
            'headTitle' => '',
            'breadcrumb' => '',
            'pageView' => '',
            'pageScript' => '',
            'validate' => '',
            'response' => []
        ];
    }

    /**
     * Verifica los permisos del usuario para el módulo actual
     */
    protected function check_permissions() {
        $user_id = $this->session->userdata('id');
        if (!$this->init_controller->has_module_access($user_id, $this->modulo)) {
            if ($this->input->is_ajax_request()) {
                echo json_encode([
                    'success' => 0,
                    'msg' => 'No tienes permisos para acceder a este módulo.'
                ]);
                exit;
            } else {
                redirect('deny');
            }
        }
    }
}
