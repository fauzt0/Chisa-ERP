<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Comunicación interna RH — mensajes y tareas entre empleados vinculados.
 */
class Comunicacion extends MY_Controller {

    protected $modulo = null;

    public function __construct() {
        parent::__construct();
        $this->load->model('RH/ComunicacionModel');
        $this->load->model('RH/EmpleadoUsuarioModel');
        $this->load->model('Users/UserModel');
    }

    public function index() {
        $ctx = $this->_contexto_empleado();
        if (!$ctx) {
            setViewError('Para usar comunicación interna necesitas un expediente de empleado vinculado a tu usuario.');
            redirect('usuarios/Perfil');
            return;
        }

        setViewSuccess('Comunicación interna cargada');
        $this->viewData['pageTitle'] = 'Comunicación Interna';
        $this->viewData['headTitle'] = 'Mensajes y Tareas';
        $this->viewData['breadcrumb'] = 'Inicio > Recursos Humanos > Comunicación Interna';
        $this->viewData['response'] = [
            'empleado' => $ctx['empleado'],
            'resumen' => $this->ComunicacionModel->get_resumen($ctx['empleado']->id),
            'contactos' => $this->ComunicacionModel->get_contactos($ctx['empleado']->id),
            'tablas_listas' => $this->ComunicacionModel->tablas_listas(),
        ];
        $this->viewData['pageView'] = 'rh/comunicacion/main';
        $this->viewData['pageScript'] = 'rh/comunicacion/main_scripts';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function listar_mensajes() {
        $this->_json_empleado(function ($empleado) {
            $bandeja = $this->input->post('bandeja') === 'enviados' ? 'enviados' : 'recibidos';
            $mensajes = $this->ComunicacionModel->get_mensajes($empleado->id, $bandeja);
            echo json_encode(['success' => true, 'mensajes' => $mensajes]);
        });
    }

    public function enviar_mensaje() {
        $this->_json_empleado(function ($empleado, $user_id) {
            $para = (int)$this->input->post('para_empleado_id');
            $texto = $this->input->post('mensaje');
            echo json_encode($this->ComunicacionModel->enviar_mensaje($empleado->id, $para, $texto, $user_id));
        });
    }

    public function marcar_leido() {
        $this->_json_empleado(function ($empleado) {
            $id = (int)$this->input->post('mensaje_id');
            $ok = $this->ComunicacionModel->marcar_mensaje_leido($id, $empleado->id);
            echo json_encode(['success' => (bool)$ok]);
        });
    }

    public function listar_tareas() {
        $this->_json_empleado(function ($empleado) {
            $vista = $this->input->post('vista') === 'enviadas' ? 'enviadas' : 'asignadas';
            $tareas = $this->ComunicacionModel->get_tareas($empleado->id, $vista);
            echo json_encode(['success' => true, 'tareas' => $tareas]);
        });
    }

    public function crear_tarea() {
        $this->_json_empleado(function ($empleado, $user_id) {
            $para = (int)$this->input->post('para_empleado_id');
            $titulo = $this->input->post('titulo');
            $descripcion = $this->input->post('descripcion');
            $fecha_limite = $this->input->post('fecha_limite');
            if ($fecha_limite === '' || $fecha_limite === false) {
                $fecha_limite = null;
            }
            echo json_encode($this->ComunicacionModel->crear_tarea(
                $empleado->id, $para, $titulo, $descripcion, $fecha_limite, $user_id
            ));
        });
    }

    public function actualizar_tarea() {
        $this->_json_empleado(function ($empleado) {
            $id = (int)$this->input->post('tarea_id');
            $estatus = $this->input->post('estatus');
            echo json_encode($this->ComunicacionModel->actualizar_estatus_tarea($id, $empleado->id, $estatus));
        });
    }

    public function resumen_ajax() {
        $this->_json_empleado(function ($empleado) {
            echo json_encode([
                'success' => true,
                'resumen' => $this->ComunicacionModel->get_resumen($empleado->id),
            ]);
        });
    }

    /**
     * Resumen para icono de barra superior (mensajes y tareas pendientes).
     */
    public function navbar_ajax() {
        $this->output->set_content_type('application/json', 'utf-8');
        $ctx = $this->_contexto_empleado();
        if (!$ctx) {
            $this->output->set_output(json_encode([
                'success' => true,
                'linked' => false,
            ]));
            return;
        }
        if (!$this->ComunicacionModel->tablas_listas()) {
            $this->output->set_output(json_encode([
                'success' => true,
                'linked' => true,
                'installed' => false,
            ]));
            return;
        }

        $data = $this->ComunicacionModel->get_navbar_data($ctx['empleado']->id);
        $this->output->set_output(json_encode([
            'success' => true,
            'linked' => true,
            'installed' => true,
            'url' => base_url('rh/Comunicacion'),
            'resumen' => $data['resumen'],
            'total_pendiente' => $data['total_pendiente'],
            'mensajes' => $data['mensajes'],
            'tareas' => $data['tareas'],
        ]));
    }

    private function _contexto_empleado() {
        $user_id = (int)$this->session->userdata('id');
        $user = $this->UserModel->mod_get_user_from_id($user_id);
        if (empty($user->empleado_id)) {
            return null;
        }
        $empleado = $this->EmpleadoUsuarioModel->get_empleado_por_usuario($user_id);
        if (!$empleado || !in_array((int)$empleado->estatus, [1, 2], true)) {
            return null;
        }
        return ['user' => $user, 'empleado' => $empleado];
    }

    private function _json_empleado($callback) {
        $this->output->set_content_type('application/json', 'utf-8');
        $ctx = $this->_contexto_empleado();
        if (!$ctx) {
            $this->output->set_output(json_encode([
                'success' => false,
                'message' => 'Debes tener un expediente de empleado vinculado.',
            ]));
            return;
        }
        ob_start();
        $callback($ctx['empleado'], (int)$ctx['user']->id);
        $out = ob_get_clean();
        $this->output->set_output($out);
    }
}
