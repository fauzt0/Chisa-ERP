<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kardex de almacén (insumos y producto terminado). Solo consulta.
 */
class Movimientos extends MY_Controller {

    protected $modulo = 'Almacén';

    public function __construct() {
        parent::__construct();
        $this->load->model('Almacen/AlmacenModel');
    }

    public function index() {
        $this->viewData['pageTitle'] = 'Movimientos de almacén';
        $this->viewData['headTitle'] = 'Movimientos de almacén';
        $this->viewData['breadcrumb'] = 'Almacén > Movimientos';
        $this->viewData['response'] = [
            'movimientos' => $this->AlmacenModel->listar_movimientos(200),
        ];
        $this->viewData['pageView'] = 'almacen/movimientos/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
}
