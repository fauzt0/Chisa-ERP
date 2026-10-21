<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * ExportacionObraModel - Exportación Excel/PDF de presupuestos de obra,
 * con marca de agua de sucursal (D1/D2).
 */
class ExportacionObraModel extends CI_Model {

    private $vistas = [
        'presupuesto' => 'presupuesto',
        'resumen' => 'resumen',
        'catalogo' => 'catalogo',
        'unitario' => 'unitario',
        'generador' => 'generador',
        'precios_actuales' => 'precios_actuales',
        'revision_cuantificacion' => 'revision_cuantificacion',
        'datos_obra' => 'datos_obra',
    ];

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function asegurar_infraestructura() {
        $this->load->model('Obras/ConceptosObraModel');
        $this->load->model('Obras/PresupuestosObraModel');
        $this->load->model('Obras/ApuModel');
        $this->load->model('Obras/GeneradoresModel');
        $this->load->model('Obras/RevisionCuantificacionModel');
        $this->ConceptosObraModel->asegurar_infraestructura();
        $this->PresupuestosObraModel->asegurar_infraestructura();
        $this->ApuModel->asegurar_infraestructura();
        $this->GeneradoresModel->asegurar_infraestructura();
        $this->RevisionCuantificacionModel->asegurar_infraestructura();
    }

    public function nombre_vista($tipo) {
        return $this->vistas[$tipo] ?? $this->vistas['presupuesto'];
    }

    public function get_marca_agua($presupuesto_id) {
        $this->load->model('Ventas/SucursalesModel');
        $sucursal_id = $this->PresupuestosObraModel->resolver_sucursal_id($presupuesto_id);
        return $this->SucursalesModel->get_marca_agua($sucursal_id);
    }

    public function preparar_datos($tipo, $presupuesto_id) {
        $this->asegurar_infraestructura();
        $this->load->model('Config/EmpresaModel');

        $presupuesto = $this->PresupuestosObraModel->get_detalle((int) $presupuesto_id);
        if (!$presupuesto) {
            return null;
        }

        $empresa = $this->EmpresaModel->get_config();
        $marca = $this->get_marca_agua((int) $presupuesto_id);

        $obra = null;
        if (!empty($presupuesto->obra_id)) {
            $this->load->model('Obras/ObrasModel');
            $obra = $this->ObrasModel->get_obra_detalle((int) $presupuesto->obra_id);
        }

        $apu = [];
        foreach ($presupuesto->conceptos as $concepto) {
            if (!empty($concepto->concepto_id)) {
                $apu[$concepto->id] = $this->ApuModel->calcular_precio_unitario((int) $concepto->concepto_id);
            }
        }

        $generadores = [];
        if ($obra) {
            $generadores = $this->GeneradoresModel->listar(['obra_id' => $obra->id]);
        }

        $revisiones = [];
        if ($obra) {
            $this->RevisionCuantificacionModel->comparar($obra->id);
            $revisiones = $this->RevisionCuantificacionModel->listar($obra->id);
        }

        return [
            'tipo' => $tipo,
            'presupuesto' => $presupuesto,
            'obra' => $obra,
            'empresa' => $empresa,
            'marca_agua' => $marca,
            'apu' => $apu,
            'generadores' => $generadores,
            'revisiones' => $revisiones,
            'precios_actuales' => $this->_precios_actuales(),
            'cliente' => $this->_cliente_del_presupuesto($presupuesto),
        ];
    }

    private function _cliente_del_presupuesto($presupuesto) {
        if (!empty($presupuesto->cliente_id)) {
            $this->db->where('id', (int) $presupuesto->cliente_id);
            return $this->db->get('clientes')->row();
        }
        if (!empty($presupuesto->obra_id)) {
            $this->db->select('c.*');
            $this->db->from('clientes c');
            $this->db->join('obras o', 'o.cliente_id = c.id');
            $this->db->where('o.id', (int) $presupuesto->obra_id);
            return $this->db->get()->row();
        }
        return null;
    }

    private function _precios_actuales() {
        $this->db->select("id, codigo, nombre AS descripcion, 'producto' AS tipo, unidad_venta AS unidad, precio_venta AS precio, fecha_modificacion AS fecha");
        $this->db->from('productos');
        $this->db->where('estatus', 'Activo');
        $this->db->where('precio_venta >', 0);
        $this->db->order_by('codigo', 'ASC');
        $productos = $this->db->get()->result_array();

        $this->db->select("id, codigo, nombre_tecnico AS descripcion, 'insumo' AS tipo, unidad_medida AS unidad, precio_promedio AS precio, fecha_registro AS fecha");
        $this->db->from('insumos');
        $this->db->where('estatus', 'Activo');
        $this->db->where('precio_promedio >', 0);
        $this->db->order_by('codigo', 'ASC');
        $insumos = $this->db->get()->result_array();

        return array_merge($productos, $insumos);
    }

    /**
     * Exporta a PDF el presupuesto.
     * Motor preferido: mPDF server-side (composer require mpdf/mpdf — doc/REGLAS_TECNICAS.md).
     * Fallback: HTML imprimible + html2pdf.js en el navegador (dompdf no instalado por advisory PKSA).
     *
     * @param string $tipo            Tipo de vista C1..C8
     * @param int    $presupuesto_id  Presupuesto
     * @param bool   $devolver_string Si true, regresa ['pdf' => binario] sin enviar cabeceras (pruebas/CLI)
     * @return array|null
     */
    public function exportar_pdf($tipo, $presupuesto_id, $devolver_string = false) {
        $data = $this->preparar_datos($tipo, $presupuesto_id);
        if (!$data) {
            show_404();
            return null;
        }

        $vista = $this->nombre_vista($tipo);
        $html = $this->load->view('obras/' . $vista, $data, true);
        $folio = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) ($data['presupuesto']->folio ?? 'presupuesto'));
        $nombreArchivo = $folio . '_' . strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', $vista)) . '.pdf';

        // 1) mPDF server-side
        if (class_exists('Mpdf\\Mpdf')) {
            try {
                $htmlPdf = $this->_preparar_html_para_mpdf($html);

                $tempDir = FCPATH . 'uploads/tmp';
                if (!is_dir($tempDir)) {
                    @mkdir($tempDir, 0755, true);
                }

                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'letter',
                    'margin_left' => 8,
                    'margin_right' => 8,
                    'margin_top' => 8,
                    'margin_bottom' => 8,
                    'tempDir' => is_writable($tempDir) ? $tempDir : null,
                ]);
                $mpdf->showImageErrors = false;

                // Marca de agua de sucursal en TODAS las páginas (motor mPDF, diagonal)
                $marcaTexto = trim((string) ($data['marca_agua']['texto'] ?? ''));
                if ($marcaTexto !== '') {
                    $mpdf->SetWatermarkText($marcaTexto, 0.08);
                    $mpdf->showWatermarkText = true;
                }

                $mpdf->WriteHTML($htmlPdf);
                $pdf = $mpdf->Output($nombreArchivo, \Mpdf\Output\Destination::STRING_RETURN);

                if ($devolver_string) {
                    return ['success' => true, 'pdf' => $pdf, 'output' => 'pdf', 'engine' => 'mpdf', 'nombre' => $nombreArchivo];
                }

                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
                header('Content-Length: ' . strlen($pdf));
                echo $pdf;
                return ['success' => true, 'output' => 'pdf', 'engine' => 'mpdf', 'nombre' => $nombreArchivo];
            } catch (\Throwable $e) {
                // Caer al fallback HTML+html2pdf.js
                log_message('error', 'ExportacionObraModel::exportar_pdf mPDF fallo: ' . $e->getMessage());
            }
        }

        // 2) Fallback: HTML imprimible + html2pdf.js en el navegador
        return ['success' => true, 'content' => $html, 'filename' => $folio . '_' . $vista . '.html', 'output' => 'html'];
    }

    /**
     * Adaptaciones del HTML de las vistas C1..C8 para mPDF:
     * - rutas locales de imagenes (no depender de red),
     * - footer no absoluto (mPDF no respeta absolute dentro de contenedor),
     * - sin toolbar/scripts (son de la vista HTML),
     * - sin div de marca de agua (la pone el watermark nativo de mPDF).
     */
    private function _preparar_html_para_mpdf($html) {
        $base = rtrim(base_url(), '/');
        if ($base !== '' && $base !== rtrim(FCPATH, '/')) {
            $html = str_replace($base, rtrim(FCPATH, '/'), $html);
        }
        $html = str_replace('.pg-footer { position: absolute;', '.pg-footer { position: relative;', $html);
        $html = preg_replace('#<div class="toolbar">.*?</div>#s', '', $html);
        $html = preg_replace('#<script\b[^>]*>.*?</script>#si', '', $html);
        $html = preg_replace('#<div class="marca-agua">.*?</div>#su', '', $html);
        return $html;
    }
    /**
     * Exportación Excel (PhpSpreadsheet) con marca de agua de sucursal.
     * DECISIÓN: encabezado de página con texto de sucursal (setOddHeader) porque
     * PhpSpreadsheet no soporta marca de agua diagonal nativa en celda.
     */
    public function exportar_excel($tipo, $presupuesto_id, $hojas = null) {
        $data = $this->preparar_datos($tipo, (int) $presupuesto_id);
        if (!$data) {
            show_404();
            return;
        }

        if (empty($hojas) || !is_array($hojas)) {
            $hojas = [$this->nombre_vista($tipo)];
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('ERP CHISA')->setTitle('Presupuesto ' . $data['presupuesto']->folio);

        $marca_texto = $data['marca_agua']['texto'] ?? '';
        $presupuesto = $data['presupuesto'];

        $sheet_index = 0;
        foreach ($hojas as $hoja) {
            $hoja = $this->nombre_vista($hoja);
            if ($sheet_index === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }
            $sheet->setTitle(substr(ucfirst($hoja), 0, 31));
            $this->_escribir_hoja_excel($sheet, $hoja, $data);
            $sheet_index++;
        }

        foreach ($spreadsheet->getAllSheets() as $sh) {
            $sh->getHeaderFooter()->setOddHeader('&C&8' . $marca_texto);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'PRES_' . str_replace('/', '_', $presupuesto->folio) . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    private function _escribir_hoja_excel($sheet, $hoja, $data) {
        $empresa = $data['empresa'];
        $presupuesto = $data['presupuesto'];
        $cliente = $data['cliente'];

        $sheet->setCellValue('A1', $empresa->razon_social ?? 'Chisa Recubrimientos');
        $sheet->setCellValue('A2', 'RFC: ' . ($empresa->rfc ?? ''));
        $sheet->setCellValue('A3', 'Folio: ' . $presupuesto->folio . '    Fecha: ' . date('d/m/Y'));
        if ($cliente) {
            $sheet->setCellValue('A4', 'Cliente: ' . $cliente->razon_social);
        }

        switch ($hoja) {
            case 'presupuesto':
            case 'catalogo':
                $this->_hoja_partidas($sheet, $data, $hoja === 'catalogo');
                break;
            case 'resumen':
                $this->_hoja_resumen($sheet, $data);
                break;
            case 'unitario':
                $this->_hoja_unitario($sheet, $data);
                break;
            case 'generador':
                $this->_hoja_generador($sheet, $data);
                break;
            case 'precios_actuales':
                $this->_hoja_precios_actuales($sheet, $data);
                break;
            case 'revision_cuantificacion':
                $this->_hoja_revision($sheet, $data);
                break;
            case 'datos_obra':
                $this->_hoja_datos_obra($sheet, $data);
                break;
        }
    }

    private function _hoja_partidas($sheet, $data, $solo_catalogo) {
        $presupuesto = $data['presupuesto'];
        $sheet->setCellValue('A6', 'CODIGO')->getStyle('A6')->getFont()->setBold(true);
        $sheet->setCellValue('B6', 'DESCRIPCION')->getStyle('B6')->getFont()->setBold(true);
        $sheet->setCellValue('C6', 'UNIDAD')->getStyle('C6')->getFont()->setBold(true);
        $sheet->setCellValue('D6', 'CANT.')->getStyle('D6')->getFont()->setBold(true);
        $sheet->setCellValue('E6', 'PRECIO U.')->getStyle('E6')->getFont()->setBold(true);
        $sheet->setCellValue('F6', 'IMPORTE')->getStyle('F6')->getFont()->setBold(true);

        $row = 7;
        foreach ($presupuesto->conceptos as $c) {
            $sheet->setCellValue('A' . $row, $c->codigo);
            $sheet->setCellValue('B' . $row, $c->descripcion);
            $sheet->setCellValue('C' . $row, $c->unidad);
            $sheet->setCellValue('D' . $row, (float) $c->cantidad);
            $sheet->setCellValue('E' . $row, (float) $c->precio_unitario);
            $sheet->setCellValue('F' . $row, (float) $c->importe);
            $row++;
        }

        if (!$solo_catalogo) {
            $sheet->setCellValue('E' . $row, 'SUBTOTAL');
            $sheet->setCellValue('F' . $row, (float) $presupuesto->subtotal);
            $row++;
            $sheet->setCellValue('E' . $row, 'IVA (' . $presupuesto->iva_porcentaje . '%)');
            $sheet->setCellValue('F' . $row, (float) $presupuesto->iva_monto);
            $row++;
            $sheet->setCellValue('E' . $row, 'TOTAL');
            $sheet->setCellValue('F' . $row, (float) $presupuesto->total);
        }
    }

    private function _hoja_resumen($sheet, $data) {
        $presupuesto = $data['presupuesto'];
        $sheet->setCellValue('A6', 'SECCION');
        $sheet->setCellValue('B6', 'FASE');
        $sheet->setCellValue('C6', 'DESCRIPCION');
        $sheet->setCellValue('D6', 'IMPORTE');
        $row = 7;
        foreach ($presupuesto->conceptos as $c) {
            $sheet->setCellValue('A' . $row, $c->seccion ?? '-');
            $sheet->setCellValue('B' . $row, $c->fase ?? '-');
            $sheet->setCellValue('C' . $row, $c->descripcion);
            $sheet->setCellValue('D' . $row, (float) $c->importe);
            $row++;
        }
    }

    private function _hoja_unitario($sheet, $data) {
        $sheet->setCellValue('A6', 'CODIGO');
        $sheet->setCellValue('B6', 'DESCRIPCION');
        $sheet->setCellValue('C6', 'MATERIAL');
        $sheet->setCellValue('D6', 'MANO OBRA');
        $sheet->setCellValue('E6', 'COSTO DIRECTO');
        $sheet->setCellValue('F6', 'P.UNITARIO');
        $row = 7;
        foreach ($data['apu'] as $apu) {
            $sheet->setCellValue('C' . $row, (float) $apu['subtotal_material']);
            $sheet->setCellValue('D' . $row, (float) $apu['subtotal_mo']);
            $sheet->setCellValue('E' . $row, (float) $apu['costo_directo']);
            $sheet->setCellValue('F' . $row, (float) $apu['precio_unitario']);
            $row++;
        }
    }

    private function _hoja_generador($sheet, $data) {
        $sheet->setCellValue('A6', 'HOJA');
        $sheet->setCellValue('B6', 'CONCEPTO');
        $sheet->setCellValue('C6', 'SUMA HOJA');
        $sheet->setCellValue('D6', 'ACUMULADO');
        $sheet->setCellValue('E6', 'TOTAL');
        $row = 7;
        foreach ($data['generadores'] as $g) {
            $sheet->setCellValue('A' . $row, $g->hoja_no . ' de ' . $g->hoja_de);
            $sheet->setCellValue('B' . $row, $g->concepto_texto);
            $sheet->setCellValue('C' . $row, (float) $g->suma_hoja);
            $sheet->setCellValue('D' . $row, (float) $g->acumulado_anterior);
            $sheet->setCellValue('E' . $row, (float) $g->total);
            $row++;
        }
    }

    private function _hoja_precios_actuales($sheet, $data) {
        $sheet->setCellValue('A6', 'CODIGO');
        $sheet->setCellValue('B6', 'DESCRIPCION');
        $sheet->setCellValue('C6', 'UNIDAD');
        $sheet->setCellValue('D6', 'PRECIO');
        $sheet->setCellValue('E6', 'FECHA');
        $row = 7;
        foreach ($data['precios_actuales'] as $p) {
            $sheet->setCellValue('A' . $row, $p['codigo']);
            $sheet->setCellValue('B' . $row, $p['descripcion']);
            $sheet->setCellValue('C' . $row, $p['unidad']);
            $sheet->setCellValue('D' . $row, (float) $p['precio']);
            $sheet->setCellValue('E' . $row, $p['fecha']);
            $row++;
        }
    }

    private function _hoja_revision($sheet, $data) {
        $sheet->setCellValue('A6', 'DESC');
        $sheet->setCellValue('B6', 'TOTAL CUANTI');
        $sheet->setCellValue('C6', 'COTIZADO');
        $sheet->setCellValue('D6', 'DIFERENCIA');
        $sheet->setCellValue('E6', 'COMENTARIOS');
        $row = 7;
        foreach ($data['revisiones'] as $r) {
            $sheet->setCellValue('A' . $row, $r->descripcion);
            $sheet->setCellValue('B' . $row, (float) $r->total_cuantificado);
            $sheet->setCellValue('C' . $row, (float) $r->total_cotizado);
            $sheet->setCellValue('D' . $row, (float) $r->diferencia);
            $sheet->setCellValue('E' . $row, $r->comentario);
            $row++;
        }
    }

    private function _hoja_datos_obra($sheet, $data) {
        $obra = $data['obra'];
        $cliente = $data['cliente'];
        $sheet->setCellValue('A6', 'NOMBRE DE LA EMPRESA');
        $sheet->setCellValue('B6', $cliente ? $cliente->razon_social : '-');
        $sheet->setCellValue('A7', 'DIRECCION');
        $sheet->setCellValue('B7', $obra ? $obra->direccion : '-');
        $sheet->setCellValue('A8', 'CONTACTO DE OFICINA');
        $sheet->setCellValue('B8', $cliente ? $cliente->contacto_nombre : '-');
        $sheet->setCellValue('A9', 'TELEFONOS');
        $sheet->setCellValue('B9', $cliente ? $cliente->telefono : '-');
    }
}



