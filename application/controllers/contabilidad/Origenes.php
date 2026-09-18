<?php
defined('BASEPATH') OR exit('No direct script access allowed');

#[AllowDynamicProperties]
class Origenes extends MY_Controller {

    protected $modulo = 'Contabilidad';

    public function __construct() {
        parent::__construct();
        $this->load->model('Contabilidad/OrigenesModel');
        $this->load->model('Contabilidad/ContabilidadModel');
    }

    public function index() {
        $this->OrigenesModel->asegurar_infraestructura();
        setViewSuccess('Orígenes operativos (solo lectura) listos');
        $this->viewData['pageTitle'] = 'Orígenes contables';
        $this->viewData['headTitle'] = 'Orígenes operativos';
        $this->viewData['breadcrumb'] = 'Inicio > Contabilidad > Orígenes';
        $this->viewData['periodo_actual'] = $this->ContabilidadModel->get_periodo_actual();
        $this->viewData['datos'] = $this->OrigenesModel->listar_pendientes();
        $this->viewData['pageView'] = 'contabilidad/origenes/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function generar_ajax() {
        $origen = $this->input->post('origen') ?: 'todos';
        $autorizar = in_array($this->input->post('autorizar'), ['1', 1, 'true', true], true);
        if (!in_array($origen, ['todos', 'facturas', 'compras', 'nominas'], true)) {
            echo json_encode(['success' => false, 'message' => 'Origen no válido']);
            return;
        }
        $res = $this->OrigenesModel->generar_polizas($origen, $autorizar);
        echo json_encode([
            'success' => true,
            'message' => 'Pólizas creadas: ' . $res['creadas'] . '. Omitidas (ya existían): ' . $res['omitidas'] . '.',
            'data' => $res,
        ]);
    }

    /**
     * CLI: php index.php contabilidad/Origenes/cli_sync
     * Crea catálogo/ejercicio 2026 y pólizas en borrador (idempotente).
     */
    public function cli_sync() {
        if (!is_cli()) {
            show_error('Solo CLI', 403);
            return;
        }
        $this->OrigenesModel->asegurar_infraestructura();
        $res = $this->OrigenesModel->generar_polizas('todos', false);
        echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    }
}
