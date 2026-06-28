<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Nomina extends MY_Controller {

    protected $modulo = 'Recursos Humanos';

    public function __construct() {
        parent::__construct();
        $this->load->model('RH/NominaRhModel');
        $this->load->model('Contabilidad/NominaModel');
    }

    public function index() {
        setViewSuccess('Nómina cargada correctamente');
        $this->viewData['pageTitle'] = 'Nómina';
        $this->viewData['headTitle'] = 'Pago de Nómina';
        $this->viewData['breadcrumb'] = 'Inicio > Recursos Humanos > Nómina';
        $this->viewData['stats'] = $this->NominaRhModel->get_estadisticas_dashboard();
        $this->viewData['requiere_migracion_pago'] = $this->NominaRhModel->requiere_migracion_pago_parcial();
        $this->viewData['pageView'] = 'rh/nomina/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function lista_ajax() {
        header('Content-Type: application/json; charset=utf-8');

        $this->db->select('*');
        $this->db->from('nominas');
        $this->db->order_by('fecha_pago', 'DESC');
        $nominas = $this->db->get()->result();

        $data = [];
        foreach ($nominas as $nomina) {
            $badge_tipo = $this->badge_tipo($nomina->tipo_nomina);
            $badge_estatus = $this->badge_estatus($nomina->estatus);
            $id = (int)$nomina->id;

            $puede_pagar = in_array($nomina->estatus, ['Calculada', 'Parcial'], true);
            if ($puede_pagar) {
                $btn_pago = $this->btn_tabla(
                    'abrirModalPago(' . $id . ')',
                    'btn-success text-nowrap',
                    'fa-money-bill-wave',
                    'Procesar Pago',
                    'full'
                );
            } elseif ($nomina->estatus === 'Borrador') {
                $btn_pago = $this->btn_tabla(
                    'calcularNomina(' . $id . ')',
                    'btn-outline-warning text-nowrap',
                    'fa-calculator',
                    'Calcular',
                    'full'
                );
            } else {
                $btn_pago = '<span class="text-muted small">—</span>';
            }

            $acciones = '<div class="btn-group btn-group-sm flex-nowrap" role="group">';
            $acciones .= $this->btn_tabla('verNomina(' . $id . ')', 'btn-outline-primary', 'fa-eye', 'Ver detalle');

            if ($nomina->estatus === 'Borrador') {
                $acciones .= $this->btn_tabla('calcularNomina(' . $id . ')', 'btn-outline-success', 'fa-calculator', 'Calcular');
                $acciones .= $this->btn_tabla('eliminarNomina(' . $id . ')', 'btn-outline-danger', 'fa-trash', 'Eliminar');
            }
            if ($puede_pagar) {
                $acciones .= $this->btn_tabla('exportarExcel(' . $id . ')', 'btn-outline-success', 'fa-file-excel', 'Exportar Excel NOI');
            }
            if (in_array($nomina->estatus, ['Parcial', 'Pagada'], true)) {
                $acciones .= $this->btn_tabla('verRecibosNomina(' . $id . ')', 'btn-outline-secondary', 'fa-print', 'Recibos de pago');
            }
            if ($nomina->estatus === 'Pagada') {
                $acciones .= $this->btn_tabla('exportarExcel(' . $id . ')', 'btn-outline-success', 'fa-file-excel', 'Exportar Excel NOI');
                if (!empty($nomina->poliza_id)) {
                    $acciones .= '<a href="' . base_url('contabilidad/Polizas') . '" class="btn btn-outline-info" title="Póliza #' . (int)$nomina->poliza_id . '"><i class="fas fa-book"></i></a>';
                }
            }
            $acciones .= '</div>';

            $data[] = [
                '<strong>' . htmlspecialchars($nomina->folio) . '</strong>',
                '<span class="badge bg-' . $badge_tipo . '">' . htmlspecialchars($nomina->tipo_nomina) . '</span>',
                date('d/m/Y', strtotime($nomina->periodo_inicio)) . ' — ' . date('d/m/Y', strtotime($nomina->periodo_fin)),
                date('d/m/Y', strtotime($nomina->fecha_pago)),
                '<span class="text-end d-block">$' . number_format((float)($nomina->total_percepciones ?? 0), 2) . '</span>',
                '<span class="text-end d-block text-danger">$' . number_format((float)($nomina->total_deducciones ?? 0), 2) . '</span>',
                '<strong class="text-end d-block">$' . number_format((float)($nomina->total_neto ?? 0), 2) . '</strong>',
                '<span class="badge bg-' . $badge_estatus . '">' . htmlspecialchars($nomina->estatus) . '</span>',
                $btn_pago,
                $acciones,
            ];
        }

        echo json_encode([
            'draw'            => isset($_POST['draw']) ? (int)$_POST['draw'] : 1,
            'recordsTotal'    => count($nominas),
            'recordsFiltered' => count($nominas),
            'data'            => $data,
        ]);
    }

    public function crear_ajax() {
        $data = [
            'folio'            => $this->NominaRhModel->generar_folio(),
            'periodo_inicio'   => $this->input->post('periodo_inicio'),
            'periodo_fin'      => $this->input->post('periodo_fin'),
            'tipo_nomina'      => $this->input->post('tipo_nomina'),
            'fecha_pago'       => $this->input->post('fecha_pago'),
            'usuario_creacion' => $this->session->userdata('id'),
        ];

        if (!$data['periodo_inicio'] || !$data['periodo_fin'] || !$data['tipo_nomina'] || !$data['fecha_pago']) {
            echo json_encode(['success' => false, 'message' => 'Complete todos los campos requeridos']);
            return;
        }

        $this->db->insert('nominas', $data);
        if (!$this->db->insert_id()) {
            echo json_encode(['success' => false, 'message' => 'Error al crear nómina']);
            return;
        }

        $nomina_id = $this->db->insert_id();
        $total_empleados = $this->NominaRhModel->agregar_empleados_nomina($nomina_id, $data['tipo_nomina']);

        echo json_encode([
            'success'         => true,
            'message'         => 'Nómina creada con ' . $total_empleados . ' empleado(s)',
            'nomina_id'       => $nomina_id,
            'total_empleados' => $total_empleados,
        ]);
    }

    public function calcular_ajax() {
        $nomina_id = (int)$this->input->post('id');
        $result = $this->NominaRhModel->calcular_nomina($nomina_id);
        echo json_encode($result);
    }

    public function pagar_ajax() {
        $nomina_id = (int)$this->input->post('id');
        $pagos = $this->input->post('pagos');
        if (is_string($pagos)) {
            $pagos = json_decode($pagos, true) ?: [];
        }

        if (!empty($pagos) && is_array($pagos)) {
            $result = $this->NominaRhModel->procesar_pagos_nomina($nomina_id, $pagos);
            echo json_encode($result);
            return;
        }

        $detalle_ids = $this->input->post('detalle_ids');
        if (is_string($detalle_ids)) {
            $detalle_ids = json_decode($detalle_ids, true) ?: [];
        }
        if (!is_array($detalle_ids)) {
            $detalle_ids = [];
        }
        $opciones = ['incluir_adeudos' => (bool)$this->input->post('incluir_adeudos')];
        $result = $this->NominaRhModel->pagar_empleados_seleccionados($nomina_id, $detalle_ids, $opciones);
        echo json_encode($result);
    }

    public function detalle_pago_ajax() {
        $id = (int)$this->input->post('id');
        $data = $this->NominaRhModel->get_detalle_para_pago($id);
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Nómina no disponible para pago. Verifique que esté en estatus Calculada o Parcial.']);
            return;
        }
        if (!empty($data['error'])) {
            echo json_encode(['success' => false, 'message' => $data['message']]);
            return;
        }
        echo json_encode(['success' => true, 'data' => $data]);
    }

    public function get_nomina_ajax() {
        $id = (int)$this->input->post('id');
        $nomina = $this->NominaModel->get_nomina_completa($id);

        if (!$nomina) {
            echo json_encode(['success' => false, 'message' => 'Nómina no encontrada']);
            return;
        }

        echo json_encode(['success' => true, 'nomina' => $nomina]);
    }

    public function eliminar_ajax() {
        $id = (int)$this->input->post('id');
        $nomina = $this->db->get_where('nominas', ['id' => $id])->row();

        if (!$nomina || $nomina->estatus !== 'Borrador') {
            echo json_encode(['success' => false, 'message' => 'Solo se pueden eliminar nóminas en borrador']);
            return;
        }

        $detalle_ids = $this->db->select('id')->from('nominas_detalle')->where('nomina_id', $id)->get()->result();
        foreach ($detalle_ids as $det) {
            $this->db->where('nomina_detalle_id', $det->id)->delete('nominas_conceptos');
        }
        $this->db->where('nomina_id', $id)->delete('nominas_detalle');
        $this->db->where('id', $id)->delete('nominas');

        echo json_encode(['success' => true, 'message' => 'Nómina eliminada']);
    }

    public function imprimir_recibos($id = null, $detalle_id = null) {
        $data = $this->preparar_datos_recibos($id, $this->filtros_recibos_desde_request(true, $detalle_id));
        if (!$data) {
            show_404();
        }

        $this->viewData = array_merge($this->viewData, $data);
        $this->load->view('rh/nomina/recibos', $this->viewData);
    }

    public function get_recibos_ajax() {
        $id = (int)$this->input->post('id');
        if ($id <= 0) {
            $this->responder_json(['success' => false, 'message' => 'Nómina no especificada']);
            return;
        }

        try {
            $data = $this->preparar_datos_recibos($id, $this->filtros_recibos_desde_request(false));

            if (!$data) {
                $this->responder_json(['success' => false, 'message' => 'Nómina no encontrada']);
                return;
            }

            if (!empty($data['sin_pagos'])) {
                $this->responder_json([
                    'success' => false,
                    'message' => 'No hay recibos de pago. Registre al menos un pago antes de generar recibos.',
                ]);
                return;
            }

            $html = $this->load->view('rh/nomina/partials/recibos_contenido', $data, true);

            $this->responder_json([
                'success'  => true,
                'html'     => $html,
                'folio'    => $data['nomina']->folio,
                'count'    => count($data['nomina']->detalle),
                'filename' => 'Recibos_' . $data['nomina']->folio . '_' . date('Ymd') . '.pdf',
            ]);
        } catch (Throwable $e) {
            log_message('error', 'get_recibos_ajax: ' . $e->getMessage());
            $this->responder_json([
                'success' => false,
                'message' => 'Error al generar los recibos. Intente de nuevo o contacte al administrador.',
            ]);
        }
    }

    /**
     * Respuesta JSON limpia (sin salida previa que rompa el parseo en el navegador).
     */
    private function responder_json(array $data) {
        if (ob_get_length()) {
            ob_clean();
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            $json = json_encode([
                'success' => false,
                'message' => 'Error al codificar la respuesta: ' . json_last_error_msg(),
            ]);
        }

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output($json);
    }

    /**
     * Filtros para recibos desde GET (impresión directa) o POST (modal AJAX).
     */
    private function filtros_recibos_desde_request($use_get = false, $detalle_id_uri = null) {
        if ($use_get) {
            $detalle_id = $detalle_id_uri ? (int)$detalle_id_uri : (int)$this->input->get('detalle_id');
            $ids_raw = $this->input->get('ids');
            $montos_raw = $this->input->get('montos');
            $solo_pagados = $this->input->get('pagados') !== '0';
        } else {
            $detalle_id = (int)$this->input->post('detalle_id');
            $ids_raw = $this->input->post('ids');
            $montos_raw = $this->input->post('montos');
            $solo_pagados = $this->input->post('pagados') !== '0';
        }

        $ids = [];
        if (is_array($ids_raw)) {
            $ids = array_filter(array_map('intval', $ids_raw));
        } elseif (is_string($ids_raw) && $ids_raw !== '') {
            if ($ids_raw[0] === '[') {
                $decoded = json_decode($ids_raw, true);
                if (is_array($decoded)) {
                    $ids = array_filter(array_map('intval', $decoded));
                }
            } else {
                $ids = array_filter(array_map('intval', explode(',', $ids_raw)));
            }
        }

        $montos_lote = [];
        if (is_array($montos_raw)) {
            foreach ($montos_raw as $did => $monto) {
                $montos_lote[(int)$did] = round((float)$monto, 2);
            }
        } elseif (is_string($montos_raw) && $montos_raw !== '') {
            $decoded = json_decode($montos_raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $did => $monto) {
                    $montos_lote[(int)$did] = round((float)$monto, 2);
                }
            }
        }

        return [
            'detalle_id'    => $detalle_id,
            'ids'           => $ids,
            'montos_lote'   => $montos_lote,
            'solo_pagados'  => $solo_pagados,
        ];
    }

    private function preparar_datos_recibos($nomina_id, array $filtros) {
        $nomina = $this->NominaModel->get_nomina_completa((int)$nomina_id);
        if (!$nomina) {
            return null;
        }

        $detalle = $nomina->detalle ?? [];
        $detalle_id = (int)($filtros['detalle_id'] ?? 0);
        $ids = $filtros['ids'] ?? [];
        $solo_pagados = !empty($filtros['solo_pagados']);

        if ($detalle_id > 0) {
            $detalle = array_values(array_filter($detalle, function ($d) use ($detalle_id) {
                return (int)$d->id === $detalle_id;
            }));
        } elseif (!empty($ids)) {
            $detalle = array_values(array_filter($detalle, function ($d) use ($ids) {
                return in_array((int)$d->id, $ids, true);
            }));
        } elseif ($solo_pagados) {
            $detalle = array_values(array_filter($detalle, function ($d) {
                if ((float)($d->monto_pagado ?? 0) > 0) {
                    return true;
                }
                return in_array($d->estatus ?? '', ['Pagado', 'Parcial'], true);
            }));
        }

        $nomina->detalle = $detalle;

        return [
            'nomina'       => $nomina,
            'sin_pagos'    => empty($detalle),
            'montos_lote'  => $filtros['montos_lote'] ?? [],
        ];
    }

    /**
     * Exporta nómina a Excel compatible con Aspel NOI / importación de nómina.
     */
    public function exportar_excel($id = null) {
        $id = (int)$id;
        $datos = $this->NominaRhModel->get_datos_exportacion_noi($id);
        if (!$datos || empty($datos['filas'])) {
            setViewError('No hay datos para exportar. Calcule la nómina primero.');
            redirect('rh/Nomina');
            return;
        }

        $nomina = $datos['nomina'];
        $spreadsheet = new Spreadsheet();

        // Hoja 1: Resumen
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen');
        $sheet->setCellValue('A1', 'EXPORTACIÓN NÓMINA — ASPEL NOI');
        $sheet->setCellValue('A2', 'Folio:');
        $sheet->setCellValue('B2', $nomina->folio);
        $sheet->setCellValue('A3', 'Tipo:');
        $sheet->setCellValue('B3', $nomina->tipo_nomina);
        $sheet->setCellValue('A4', 'Periodo:');
        $sheet->setCellValue('B4', $nomina->periodo_inicio . ' al ' . $nomina->periodo_fin);
        $sheet->setCellValue('A5', 'Fecha pago:');
        $sheet->setCellValue('B5', $nomina->fecha_pago);
        $sheet->setCellValue('A6', 'Percepciones:');
        $sheet->setCellValue('B6', $nomina->total_percepciones);
        $sheet->setCellValue('A7', 'Deducciones:');
        $sheet->setCellValue('B7', $nomina->total_deducciones);
        $sheet->setCellValue('A8', 'Neto:');
        $sheet->setCellValue('B8', $nomina->total_neto);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Hoja 2: Detalle empleados (formato NOI)
        $detalle = $spreadsheet->createSheet();
        $detalle->setTitle('Detalle Empleados');
        $headers = [
            'A' => 'No. Empleado', 'B' => 'Nombre Completo', 'C' => 'RFC', 'D' => 'CURP', 'E' => 'NSS',
            'F' => 'Puesto', 'G' => 'Días Trabajados', 'H' => 'Sueldo Base', 'I' => 'Percepciones',
            'J' => 'ISR', 'K' => 'IMSS', 'L' => 'INFONAVIT', 'M' => 'Pensión Alimenticia',
            'N' => 'Otras Deducciones', 'O' => 'Total Deducciones', 'P' => 'Neto a Pagar',
        ];
        $col = 1;
        foreach ($headers as $colLetter => $label) {
            $detalle->setCellValue($colLetter . '1', $label);
        }
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $detalle->getStyle('A1:P1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($datos['filas'] as $f) {
            $detalle->setCellValue('A' . $row, $f['numero_empleado']);
            $detalle->setCellValue('B' . $row, $f['nombre_completo']);
            $detalle->setCellValue('C' . $row, $f['rfc']);
            $detalle->setCellValue('D' . $row, $f['curp']);
            $detalle->setCellValue('E' . $row, $f['nss']);
            $detalle->setCellValue('F' . $row, $f['puesto']);
            $detalle->setCellValue('G' . $row, $f['dias_trabajados']);
            $detalle->setCellValue('H' . $row, $f['sueldo_base']);
            $detalle->setCellValue('I' . $row, $f['percepciones']);
            $detalle->setCellValue('J' . $row, $f['isr']);
            $detalle->setCellValue('K' . $row, $f['imss']);
            $detalle->setCellValue('L' . $row, $f['infonavit']);
            $detalle->setCellValue('M' . $row, $f['pension']);
            $detalle->setCellValue('N' . $row, $f['otras_deducciones']);
            $detalle->setCellValue('O' . $row, $f['deducciones']);
            $detalle->setCellValue('P' . $row, $f['neto']);
            $row++;
        }

        foreach (range('A', 'P') as $c) {
            $detalle->getColumnDimension($c)->setAutoSize(true);
        }

        // Hoja 3: Conceptos (para referencia Aspel)
        $conceptos = $spreadsheet->createSheet();
        $conceptos->setTitle('Conceptos NOI');
        $conceptos->setCellValue('A1', 'Clave Concepto');
        $conceptos->setCellValue('B1', 'Descripción');
        $conceptos->setCellValue('C1', 'Tipo');
        $ref = [
            ['001', 'Sueldo Base', 'Percepción'],
            ['002', 'ISR', 'Deducción'],
            ['003', 'IMSS', 'Deducción'],
            ['004', 'INFONAVIT', 'Deducción'],
            ['005', 'Pensión Alimenticia', 'Deducción'],
        ];
        $r = 2;
        foreach ($ref as $item) {
            $conceptos->setCellValue('A' . $r, $item[0]);
            $conceptos->setCellValue('B' . $r, $item[1]);
            $conceptos->setCellValue('C' . $r, $item[2]);
            $r++;
        }
        $conceptos->getStyle('A1:C1')->applyFromArray($headerStyle);

        $spreadsheet->setActiveSheetIndex(1);
        $filename = 'Nomina_' . $nomina->folio . '_NOI_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function badge_tipo($tipo) {
        $map = [
            'Semanal'        => 'primary',
            'Quincenal'      => 'success',
            'Mensual'        => 'info',
            'Extraordinaria' => 'warning',
            'Aguinaldo'      => 'danger',
            'Finiquito'      => 'dark',
        ];
        return $map[$tipo] ?? 'secondary';
    }

    private function badge_estatus($estatus) {
        $map = [
            'Borrador'   => 'secondary',
            'Calculada'  => 'warning',
            'Parcial'    => 'info',
            'Pagada'     => 'success',
            'Cancelada'  => 'danger',
        ];
        return $map[$estatus] ?? 'secondary';
    }

    /**
     * Botón de tabla con icono Font Awesome (visible en contenido AJAX de DataTables).
     */
    private function btn_tabla($onclick, $class, $icon, $title, $with_label = false) {
        if ($with_label === 'full') {
            $label = ' <span class="ms-1">' . htmlspecialchars($title) . '</span>';
        } elseif ($with_label) {
            $label = ' <span class="d-none d-xl-inline ms-1">' . htmlspecialchars($title) . '</span>';
        } else {
            $label = '';
        }
        return '<button type="button" class="btn btn-sm ' . $class . '" onclick="' . $onclick . '" title="' . htmlspecialchars($title) . '">'
            . '<i class="fas ' . $icon . '"></i>' . $label . '</button>';
    }
}
