<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class EntrenamientoProductos extends MY_Controller {

    protected $modulo = 'Producción';

    public function __construct() {
        parent::__construct();
        $this->load->helper('permissions');
        $this->load->model('Produccion/ProductosModel');
        $this->ProductosModel->asegurar_entrenamiento_producto();
    }

    public function index() {
        $busqueda = trim((string) $this->input->get('busqueda'));
        $revisado = trim((string) $this->input->get('revisado'));
        $filtros = [];
        if ($busqueda !== '') {
            $filtros['busqueda'] = $busqueda;
        }
        if ($revisado === '0' || $revisado === '1') {
            $filtros['entrenamiento_revisado'] = (int) $revisado;
        }
        $this->viewData['pageTitle'] = 'Entrenamiento de productos';
        $this->viewData['headTitle'] = 'Entrenamiento temporal de productos';
        $this->viewData['breadcrumb'] = 'Inicio > Producción > Entrenamiento de productos';
        $this->viewData['response'] = [
            'busqueda' => $busqueda,
            'revisado' => $revisado,
            'filas' => $this->ProductosModel->listar_entrenamiento($filtros),
        ];
        $this->viewData['pageView'] = 'produccion/entrenamiento_productos/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function ver($id = 0) {
        $id = (int) $id;
        $p = $id ? $this->db->where('id', $id)->get('productos')->row() : null;
        if (!$p) {
            show_404();
            return;
        }
        $activa = $this->db->where('producto_id', $id)->where('es_activa', 1)->get('formulaciones')->row();
        $comps = $activa ? $this->ProductosModel->get_componentes_formulacion($activa->id) : [];
        $hist = [];
        if (!empty($p->entrenamiento_principal_ok)) {
            $hist = $this->ProductosModel->get_historial_formulaciones($id);
        }
        $this->viewData['pageTitle'] = 'Entrenar ' . $p->codigo;
        $this->viewData['headTitle'] = $p->nombre;
        $this->viewData['breadcrumb'] = 'Inicio > Producción > Entrenamiento de productos';
        $this->viewData['response'] = [
            'producto' => $p,
            'activa' => $activa,
            'componentes' => $comps,
            'historial' => $hist,
            'aviso' => $this->session->flashdata('ep_msg'),
            'aviso_ok' => $this->session->flashdata('ep_ok') === '1',
        ];
        $this->viewData['pageView'] = 'produccion/entrenamiento_productos/ver';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function guardar_ajax() {
        $id = (int) $this->input->post('id');
        $data = [];
        $est = $this->input->post('estatus');
        if (in_array($est, ['Activo', 'Inactivo'], true)) {
            $data['estatus'] = $est;
        }
        if ($this->input->post('precio_venta') !== null && is_numeric($this->input->post('precio_venta'))) {
            $data['precio_venta'] = (float) $this->input->post('precio_venta');
        }
        $uv = $this->input->post('unidad_venta');
        if ($uv) {
            $data['unidad_venta'] = $uv;
        }
        if ($this->input->post('entrenamiento_revisado') !== null) {
            $data['entrenamiento_revisado'] = ((int) $this->input->post('entrenamiento_revisado') === 1) ? 1 : 0;
        }
        if ($this->input->post('entrenamiento_principal_ok') !== null) {
            $data['entrenamiento_principal_ok'] = ((int) $this->input->post('entrenamiento_principal_ok') === 1) ? 1 : 0;
        }
        $ok = $this->ProductosModel->guardar_entrenamiento_producto($id, $data);
        $this->session->set_flashdata('ep_msg', $ok ? 'Guardado' : 'Sin cambios');
        $this->session->set_flashdata('ep_ok', $ok ? '1' : '0');
        redirect('produccion/EntrenamientoProductos/ver/' . $id);
    }

    public function materia_prima_ajax() {
        $id = (int) $this->input->post('id');
        $res = $this->ProductosModel->pasar_a_materia_prima($id, (int) $this->session->userdata('id'));
        $this->session->set_flashdata('ep_msg', $res['message'] ?? '');
        $this->session->set_flashdata('ep_ok', !empty($res['success']) ? '1' : '0');
        redirect('produccion/EntrenamientoProductos/ver/' . $id);
    }

    public function componente_ajax() {
        $pid = (int) $this->input->post('producto_id');
        $res = $this->ProductosModel->actualizar_cantidad_componente(
            (int) $this->input->post('detalle_id'),
            $this->input->post('cantidad'),
            true,
            $pid
        );
        $this->session->set_flashdata('ep_msg', $res['message'] ?? '');
        $this->session->set_flashdata('ep_ok', !empty($res['success']) ? '1' : '0');
        redirect('produccion/EntrenamientoProductos/ver/' . $pid);
    }

    public function historial_componente_ajax() {
        $pid = (int) $this->input->post('producto_id');
        $res = $this->ProductosModel->actualizar_cantidad_componente(
            (int) $this->input->post('detalle_id'),
            $this->input->post('cantidad'),
            true,
            $pid
        );
        $this->session->set_flashdata('ep_msg', $res['message'] ?? '');
        $this->session->set_flashdata('ep_ok', !empty($res['success']) ? '1' : '0');
        redirect('produccion/EntrenamientoProductos/ver/' . $pid);
    }
}
