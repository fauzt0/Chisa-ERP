<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ExpedienteRaya extends MY_Controller {

    protected $modulo = 'Contabilidad';

    public function __construct() {
        parent::__construct();
        $this->load->model('RH/ExpedienteRayaModel');
        $this->ExpedienteRayaModel->indexar_dossier();
        $this->ExpedienteRayaModel->registrar_pendientes_monterrey();
    }

    public function index() {
        $tipo = trim((string) $this->input->get('tipo'));
        $this->viewData['pageTitle'] = 'Expediente de lista de raya';
        $this->viewData['headTitle'] = 'Expediente RH / Contabilidad';
        $this->viewData['breadcrumb'] = 'Inicio > Contabilidad > Expediente de raya';
        $this->viewData['response'] = [
            'tipo' => $tipo,
            'conteos' => $this->ExpedienteRayaModel->conteos(),
            'archivos' => $this->ExpedienteRayaModel->listar($tipo),
            'pendientes' => $this->ExpedienteRayaModel->pendientes_alta(),
        ];
        $this->viewData['pageView'] = 'contabilidad/expediente_raya/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
}
