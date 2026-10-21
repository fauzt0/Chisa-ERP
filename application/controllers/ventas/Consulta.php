<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Consulta extends MY_Controller {

    protected $modulo = 'Ventas';

    public function __construct() {
        parent::__construct();
        $this->load->model('Ventas/CapturaPosModel');
        $this->load->model('Ventas/CotizacionesObraModel');
    }

    public function index() {
        $filtros = [
            'q' => $this->input->get('q'),
            'cliente' => $this->input->get('cliente'),
            'desde' => $this->input->get('desde'),
            'hasta' => $this->input->get('hasta'),
            'lugar' => $this->input->get('lugar'),
            'tipo' => $this->input->get('tipo') ?: 'todos',
            'solo_aplicacion' => $this->input->get('aplicacion') === '1',
        ];
        $buscar = $this->input->get('q') !== null;
        $this->viewData['pageTitle'] = 'Consulta de ventas';
        $this->viewData['headTitle'] = 'Consulta de ventas';
        $this->viewData['breadcrumb'] = 'Inicio > CRM Ventas > Consulta';
        $this->viewData['response'] = [
            'filtros' => $filtros,
            'filas' => $buscar ? $this->CapturaPosModel->buscar($filtros) : [],
            'busco' => $buscar,
        ];
        $this->viewData['pageView'] = 'ventas/consulta/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
}
