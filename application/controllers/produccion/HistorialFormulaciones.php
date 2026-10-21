<?php
/**
 * Historial de formulaciones — consulta de versiones y componentes (solo lectura).
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class HistorialFormulaciones extends MY_Controller {

    protected $modulo = 'Producción';

    public function __construct() {
        parent::__construct();
        $this->load->model('Produccion/ProductosModel');
        $this->load->model('Ventas/ClientesModel');
    }

    /**
     * Listado con filtros GET (sin escritura).
     */
    public function index() {
        // Primera carga: solo activas por defecto. Tras enviar el formulario (filtrar=1),
        // la casilla desmarcada no envía solo_activas → se listan todas.
        if ($this->input->get('filtrar') !== '1') {
            $solo_activas = true;
        } else {
            $solo_activas = $this->input->get('solo_activas') === '1';
        }

        $filtros = [
            'producto_id'        => (int) $this->input->get('producto_id') ?: null,
            'cliente_id'         => (int) $this->input->get('cliente_id') ?: null,
            'texto_comentarios'  => trim((string) $this->input->get('texto_comentarios')),
            'solo_activas'       => $solo_activas,
        ];
        if ($filtros['texto_comentarios'] === '') {
            $filtros['texto_comentarios'] = null;
        }

        $filas = $this->ProductosModel->listar_historial_formulaciones_consulta($filtros);

        $this->viewData['pageTitle']  = 'Historial de formulaciones';
        $this->viewData['headTitle']  = 'Historial de formulaciones';
        $this->viewData['breadcrumb'] = 'Inicio > Producción > Historial de formulaciones';
        $this->viewData['response']   = [
            'filtros'   => [
                'producto_id'       => $filtros['producto_id'],
                'cliente_id'        => $filtros['cliente_id'],
                'texto_comentarios' => $filtros['texto_comentarios'] ?? '',
                'solo_activas'      => $solo_activas,
            ],
            'filas'     => $filas,
            'productos' => $this->ProductosModel->get_productos_select_fabricados(),
            'clientes'  => $this->ClientesModel->get_clientes_select(),
        ];
        $this->viewData['pageView'] = 'produccion/historial_formulaciones/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    /**
     * Detalle de una versión: cabecera + componentes (solo lectura).
     */
    public function ver($formulacion_id = null) {
        $id = (int) $formulacion_id;
        if ($id <= 0) {
            redirect('produccion/HistorialFormulaciones');
            return;
        }

        $cabecera = $this->ProductosModel->get_formulacion_cabecera_consulta($id);
        if (!$cabecera) {
            show_404();
            return;
        }

        $componentes = $this->ProductosModel->get_componentes_formulacion($id);

        $this->viewData['pageTitle']  = 'Formulación v' . (int) $cabecera->version;
        $this->viewData['headTitle']  = 'Detalle de formulación';
        $this->viewData['breadcrumb'] = 'Inicio > Producción > Historial de formulaciones > Versión ' . (int) $cabecera->version;
        $this->viewData['response']   = [
            'cabecera'    => $cabecera,
            'componentes' => $componentes,
            'archivo'     => $this->_extraer_archivo_comentarios($cabecera->comentarios ?? ''),
            'clientes'    => $this->ClientesModel->get_clientes_select(),
            'aviso'       => $this->session->flashdata('historial_msg'),
            'aviso_ok'    => $this->session->flashdata('historial_ok') === '1',
        ];
        $this->viewData['pageView'] = 'produccion/historial_formulaciones/ver';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    /**
     * POST: asocia o quita el cliente de una versión (solo formulaciones.cliente_id).
     */
    public function asociar_cliente() {
        if (strtolower($this->input->method()) !== 'post') {
            show_error('Método no permitido', 405);
            return;
        }

        $id = (int) $this->input->post('formulacion_id');
        $raw = $this->input->post('cliente_id');
        $cliente_id = ($raw === null || $raw === '') ? null : (int) $raw;

        if ($id <= 0) {
            $this->session->set_flashdata('historial_msg', 'Formulación no válida');
            $this->session->set_flashdata('historial_ok', '0');
            redirect('produccion/HistorialFormulaciones');
            return;
        }
        if ($cliente_id !== null && $cliente_id <= 0) {
            $this->session->set_flashdata('historial_msg', 'Cliente no válido');
            $this->session->set_flashdata('historial_ok', '0');
            redirect('produccion/HistorialFormulaciones/ver/' . $id);
            return;
        }

        $res = $this->ProductosModel->asociar_cliente_formulacion($id, $cliente_id);
        $this->session->set_flashdata('historial_msg', $res['message']);
        $this->session->set_flashdata('historial_ok', !empty($res['success']) ? '1' : '0');
        redirect('produccion/HistorialFormulaciones/ver/' . $id);
    }

    /**
     * Si comentarios contiene "Archivo: ", devuelve el fragmento a partir de ahí.
     *
     * @param  string $comentarios
     * @return string|null
     */
    private function _extraer_archivo_comentarios($comentarios) {
        $comentarios = (string) $comentarios;
        if ($comentarios === '') {
            return null;
        }
        $pos = stripos($comentarios, 'Archivo:');
        if ($pos === false) {
            return null;
        }
        return trim(substr($comentarios, $pos));
    }
}
