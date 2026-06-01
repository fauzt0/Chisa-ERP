<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lotes - Historial global de fabricación
 */
class Lotes extends MY_Controller {
    
    protected $modulo = 'Producción';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Produccion/ProduccionModel');
    }
    
    /**
     * Vista principal del historial de lotes
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Control de Lotes';
        $this->viewData['headTitle'] = 'Historial de Fabricación';
        $this->viewData['breadcrumb'] = 'Inicio > Producción > Control de Lotes';
        
        $this->viewData['pageView'] = 'produccion/lotes/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * AJAX para DataTables de lotes
     */
    public function lista_ajax() {
        $filtros = [
            'search' => $this->input->post('search')['value']
        ];
        
        $list = $this->ProduccionModel->get_lotes_global_datatables($filtros);
        $data = array();
        $no = $this->input->post('start');

        foreach ($list as $lote) {
            $no++;
            $row = array();
            
            $row[] = $lote->id;
            $row[] = '<strong>' . $lote->codigo_barras . '</strong>';
            $row[] = $lote->producto_nombre . '<br><small class="text-muted">' . $lote->producto_codigo . '</small>';
            $row[] = number_format($lote->cantidad, 2) . ' ' . $lote->unidad;
            // Origen (Trazabilidad)
            $origen = '<span class="text-muted">—</span>';
            if ($lote->ov_folio) {
                $origen = '<span class="badge bg-primary"><i class="fas fa-shopping-cart"></i> ' . $lote->ov_folio . '</span>';
            } elseif ($lote->obra_folio) {
                $origen = '<span class="badge bg-warning text-dark"><i class="fas fa-hard-hat"></i> ' . $lote->obra_folio . '</span>';
            }
            $row[] = $origen;
            
            $row[] = date('d/m/Y H:i', strtotime($lote->fecha_produccion));
            
            $badgeColor = 'success';
            if ($lote->estatus === 'Merma') $badgeColor = 'danger';
            if ($lote->estatus === 'En Almacén') $badgeColor = 'info';
            
            $row[] = '<span class="badge bg-' . $badgeColor . '">' . $lote->estatus . '</span>';
            
            // Acciones
            $row[] = '
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-info" onclick="verEtiqueta(' . $lote->id . ', \'' . $lote->codigo_barras . '\')" title="Ver Etiqueta">
                        <i class="fas fa-barcode"></i>
                    </button>
                    <a href="' . base_url('produccion/Dashboard/etiqueta_lote/' . $lote->id) . '" target="_blank" class="btn btn-sm btn-primary" title="Imprimir">
                        <i class="fas fa-print"></i>
                    </a>
                </div>';

            $data[] = $row;
        }

        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->ProduccionModel->count_all_lotes(),
            "recordsFiltered" => $this->ProduccionModel->count_filtered_lotes($filtros),
            "data" => $data,
        );
        echo json_encode($output);
    }
}
