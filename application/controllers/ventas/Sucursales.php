<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Papelería de sucursal: logo, icono, marca de agua y datos de contacto.
 */
class Sucursales extends MY_Controller {

    protected $modulo = 'Ventas';

    public function __construct() {
        parent::__construct();
        $this->load->helper('permissions');
        $this->load->model('Ventas/SucursalesModel');
    }

    private function puede_editar() {
        return tiene_permiso('ventas_cotizaciones')
            || tiene_permiso('ventas_ordenes_edit')
            || tiene_permiso('ventas_ordenes_add');
    }

    public function index() {
        $this->viewData['pageTitle'] = 'Sucursales';
        $this->viewData['headTitle'] = 'Sucursales y papelería';
        $this->viewData['breadcrumb'] = 'Inicio > CRM Ventas > Sucursales';
        $this->viewData['response'] = [
            'sucursales' => $this->SucursalesModel->listar_todas(),
            'puede_editar' => $this->puede_editar(),
        ];
        $this->viewData['pageView'] = 'ventas/sucursales/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function guardar_ajax() {
        if (!$this->puede_editar()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar sucursales']);
            return;
        }
        $id = (int) $this->input->post('id');
        $result = $this->SucursalesModel->actualizar($id, [
            'nombre' => $this->input->post('nombre'),
            'direccion' => $this->input->post('direccion'),
            'ciudad' => $this->input->post('ciudad'),
            'telefono' => $this->input->post('telefono'),
            'email' => $this->input->post('email'),
            'sitio_web' => $this->input->post('sitio_web'),
            'contacto' => $this->input->post('contacto'),
            'texto_marca_agua' => $this->input->post('texto_marca_agua'),
            'estatus' => $this->input->post('estatus'),
        ]);
        echo json_encode($result);
    }

    public function subir_ajax() {
        if (!$this->puede_editar()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar sucursales']);
            return;
        }
        $result = $this->SucursalesModel->guardar_archivo(
            (int) $this->input->post('id'),
            (string) $this->input->post('slot')
        );
        echo json_encode($result);
    }
}
