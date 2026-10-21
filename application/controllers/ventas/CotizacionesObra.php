<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Cotizaciones comerciales de obra para la vendedora (CRM).
 * Distinta de PRES-, de la cotización de venta (OV) y de la cotización a proveedor.
 */
class CotizacionesObra extends MY_Controller {

    protected $modulo = 'Ventas';

    public function __construct() {
        parent::__construct();
        $this->load->helper('permissions');
        $this->load->model('Ventas/CotizacionesObraModel');
        $this->load->model('Ventas/SucursalesModel');
        $this->load->model('Config/EmpresaModel');
    }

    private function puede_editar() {
        return tiene_permiso('ventas_cotizaciones')
            || tiene_permiso('ventas_ordenes_edit')
            || tiene_permiso('ventas_ordenes_add');
    }

    public function index() {
        $this->viewData['pageTitle'] = 'Cotizaciones de obra';
        $this->viewData['headTitle'] = 'Cotizaciones comerciales de obra';
        $this->viewData['breadcrumb'] = 'Inicio > CRM Ventas > Cotizaciones de obra';
        $q = trim((string) $this->input->get('q'));
        $consulta = $this->input->get('consulta') === '1';
        $this->viewData['response'] = [
            'cotizaciones' => $this->CotizacionesObraModel->listar($consulta ? 80 : 40, $q, $consulta),
            'puede_editar' => $this->puede_editar(),
            'q' => $q,
            'consulta' => $consulta,
            'total_consulta' => $this->CotizacionesObraModel->contar_consulta(),
        ];
        $this->viewData['pageView'] = 'ventas/cotizaciones_obra/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function editar($id = 0) {
        $cot = null;
        if ((int) $id > 0) {
            $cot = $this->CotizacionesObraModel->get($id);
            if (!$cot) {
                show_404();
                return;
            }
        }
        $obras = [];
        if ($this->db->table_exists('obras')) {
            $obras = $this->db->select('id, folio, nombre, cliente_id')->order_by('id', 'DESC')->limit(100)->get('obras')->result();
        }
        $this->viewData['pageTitle'] = $cot ? $cot->folio : 'Nueva cotización de obra';
        $this->viewData['headTitle'] = $cot ? ('Editar ' . $cot->folio) : 'Nueva cotización de obra';
        $this->viewData['breadcrumb'] = 'Inicio > CRM Ventas > Cotizaciones de obra';
        $seguimiento = null;
        if ($cot) {
            $this->load->model('Ventas/SeguimientoModel');
            $seguimiento = $this->SeguimientoModel->paquete('cotizacion', $cot->id);
        }
        $this->viewData['response'] = [
            'cotizacion' => $cot,
            'sucursales' => $this->SucursalesModel->listar_activas(),
            'clientes' => $this->CotizacionesObraModel->buscar_clientes(''),
            'conceptos' => $this->CotizacionesObraModel->buscar_conceptos('', 120),
            'obras' => $obras,
            'puede_editar' => $this->puede_editar(),
            'seguimiento' => $seguimiento,
        ];
        $this->viewData['pageView'] = 'ventas/cotizaciones_obra/editar';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function guardar_ajax() {
        if (!$this->puede_editar()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar cotizaciones']);
            return;
        }
        $lineas = json_decode($this->input->post('lineas') ?: '[]', true);
        $result = $this->CotizacionesObraModel->guardar(
            $this->input->post(),
            is_array($lineas) ? $lineas : [],
            $this->session->userdata('id')
        );
        if (!empty($result['success']) && !empty($result['id'])) {
            $grupos = [];
            foreach ((array) $lineas as $linea) {
                if (!empty($linea['grupo'])) {
                    $grupos[] = $linea['grupo'];
                }
            }
            $this->load->model('Ventas/SeguimientoModel');
            $this->SeguimientoModel->sincronizar_fases_desde_grupos($result['id'], $grupos);
        }
        echo json_encode($result);
    }

    public function estatus_ajax() {
        if (!$this->puede_editar()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso']);
            return;
        }
        $res = $this->CotizacionesObraModel->cambiar_estatus(
            (int) $this->input->post('id'),
            (string) $this->input->post('estatus')
        );
        if (!empty($res['success'])) {
            $this->load->model('Ventas/SeguimientoModel');
            $this->SeguimientoModel->evento(
                'cotizacion',
                (int) $this->input->post('id'),
                'estatus',
                'Estatus de cotización: ' . $this->input->post('estatus'),
                $this->session->userdata('id')
            );
        }
        echo json_encode($res);
    }

    public function conceptos_ajax() {
        echo json_encode([
            'success' => true,
            'conceptos' => $this->CotizacionesObraModel->buscar_conceptos($this->input->post('q') ?: $this->input->get('q')),
        ]);
    }

    public function guardar_concepto_ajax() {
        if (!$this->puede_editar()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso']);
            return;
        }
        echo json_encode($this->CotizacionesObraModel->guardar_concepto_catalogo($this->input->post()));
    }

    public function clientes_ajax() {
        echo json_encode([
            'success' => true,
            'clientes' => $this->CotizacionesObraModel->buscar_clientes($this->input->get('q')),
        ]);
    }

    public function sembrar_ejemplos_ajax() {
        if (!$this->puede_editar() && !is_cli()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso']);
            return;
        }
        $dir = FCPATH . 'doc/entrenamiento_5/COTIZACIONES 2026';
        echo json_encode($this->CotizacionesObraModel->sembrar_ejemplos($dir, 15, 80000));
    }

    public function imprimir($id = 0) {
        $cot = $this->CotizacionesObraModel->get($id);
        if (!$cot) {
            show_404();
            return;
        }
        $this->load->view('ventas/cotizaciones_obra/imprimir', [
            'cotizacion' => $cot,
            'marca' => $this->SucursalesModel->get_marca_agua($cot->sucursal_id),
            'empresa' => $this->EmpresaModel->get_config(),
        ]);
    }

    public function pdf($id = 0) {
        $cot = $this->CotizacionesObraModel->get($id);
        if (!$cot) {
            show_404();
            return;
        }
        if (!class_exists(\Mpdf\Mpdf::class)) {
            redirect('ventas/CotizacionesObra/imprimir/' . (int) $id);
            return;
        }
        $marca = $this->SucursalesModel->get_marca_agua($cot->sucursal_id);
        $html = $this->load->view('ventas/cotizaciones_obra/imprimir', [
            'cotizacion' => $cot,
            'marca' => $marca,
            'empresa' => $this->EmpresaModel->get_config(),
            'para_pdf' => true,
        ], true);
        $tempDir = FCPATH . 'uploads/tmp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'Letter',
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 12,
            'margin_right' => 12,
            'tempDir' => $tempDir,
        ]);
        $texto = trim((string) ($marca['texto'] ?? ''));
        $agua = trim((string) ($marca['logo'] ?? ''));
        $aguaAbs = $agua !== '' ? FCPATH . ltrim($agua, '/') : '';
        if ($texto !== '' && !is_file($aguaAbs)) {
            $mpdf->SetWatermarkText($texto, 0.06);
            $mpdf->showWatermarkText = true;
        }
        $mpdf->WriteHTML($html);
        $mpdf->Output($cot->folio . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
    }

    public function excel($id = 0) {
        $cot = $this->CotizacionesObraModel->get($id);
        if (!$cot) {
            show_404();
            return;
        }
        $marca = $this->SucursalesModel->get_marca_agua($cot->sucursal_id);
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('Cotizacion');
        $sh->setCellValue('A1', $marca['nombre'] ?: 'Sucursal');
        $sh->setCellValue('A2', trim(($marca['direccion'] ?? '') . ' ' . ($marca['ciudad'] ?? '')));
        $sh->setCellValue('A3', trim(($marca['telefono'] ?? '') . '  ' . ($marca['email'] ?? '') . '  ' . ($marca['sitio_web'] ?? '')));
        $sh->setCellValue('A5', 'CONSTRUCTORA: ' . $cot->constructora);
        $sh->setCellValue('A6', 'P R E S E N T E');
        $sh->setCellValue('A8', "AT'N: " . $cot->atencion);
        $sh->setCellValue('A10', 'OBRA: ' . $cot->obra_nombre);
        $sh->setCellValue('A11', 'PRES. REF. ' . ($cot->pres_ref ?: $cot->folio));
        $sh->setCellValue('A13', $cot->introduccion);
        $sh->setCellValue('A15', 'CONCEPTO');
        $sh->setCellValue('B15', 'UNIDAD');
        $sh->setCellValue('C15', 'CANTIDAD');
        $sh->setCellValue('D15', 'PRECIO U.');
        $sh->setCellValue('E15', 'IMPORTE');
        $fila = 16;
        $grupo = null;
        $sub = 0;
        foreach ($cot->detalles as $d) {
            $g = (string) $d->grupo;
            if ($g !== '' && $g !== $grupo) {
                if ($grupo !== null) {
                    $sh->setCellValue('D' . $fila, 'SUBTOTAL ' . $grupo);
                    $sh->setCellValue('E' . $fila, $sub);
                    $fila++;
                    $sub = 0;
                }
                $grupo = $g;
                $sh->setCellValue('A' . $fila, $g);
                $sh->getStyle('A' . $fila)->getFont()->setBold(true);
                $fila++;
            }
            $sh->setCellValue('A' . $fila, $d->concepto);
            $sh->setCellValue('B' . $fila, $d->unidad);
            $sh->setCellValue('C' . $fila, (float) $d->cantidad);
            $sh->setCellValue('D' . $fila, (float) $d->precio_unitario);
            $sh->setCellValue('E' . $fila, (float) $d->importe);
            $sub += (float) $d->importe;
            $fila++;
        }
        if ($grupo) {
            $sh->setCellValue('D' . $fila, 'SUBTOTAL ' . $grupo);
            $sh->setCellValue('E' . $fila, $sub);
            $fila++;
        }
        $sh->setCellValue('D' . $fila, 'TOTAL');
        $sh->setCellValue('E' . $fila, (float) $cot->total);
        $sh->getStyle('A15:E15')->getFont()->setBold(true);
        foreach (range('A', 'E') as $col) {
            $sh->getColumnDimension($col)->setWidth($col === 'A' ? 70 : 16);
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $cot->folio . '.xlsx"');
        (new Xlsx($ss))->save('php://output');
        exit;
    }
}
