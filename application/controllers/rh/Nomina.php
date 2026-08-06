<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Nomina extends MY_Controller {

    protected $modulo = 'Recursos Humanos';

    public function __construct() {
        parent::__construct();
        $this->load->model('RH/NominaRhModel');
        $this->load->model('Contabilidad/NominaModel');
        $this->load->helper('permissions');
        $this->config->load('permissions');
    }

    public function index() {
        $this->requiere_permiso('rh_nomina', 'No tienes permiso para gestionar nóminas.');
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
        $this->requiere_permiso('rh_nomina');
        header('Content-Type: application/json; charset=utf-8');

        $filtro_folio = trim((string)$this->input->post('filtro_folio'));
        $filtro_tipo = trim((string)$this->input->post('filtro_tipo'));
        $filtro_estatus = trim((string)$this->input->post('filtro_estatus'));
        $filtro_periodo_desde = trim((string)$this->input->post('filtro_periodo_desde'));
        $filtro_periodo_hasta = trim((string)$this->input->post('filtro_periodo_hasta'));

        $this->db->select('*');
        $this->db->from('nominas');

        if ($filtro_folio !== '') {
            $this->db->like('folio', $filtro_folio);
        }
        if ($filtro_tipo !== '') {
            $this->db->where('tipo_nomina', $filtro_tipo);
        }
        if ($filtro_estatus !== '') {
            $this->db->where('estatus', $filtro_estatus);
        }
        if ($filtro_periodo_desde !== '') {
            $this->db->where('periodo_fin >=', $filtro_periodo_desde);
        }
        if ($filtro_periodo_hasta !== '') {
            $this->db->where('periodo_inicio <=', $filtro_periodo_hasta);
        }

        $this->db->order_by('periodo_inicio', 'DESC');
        $this->db->order_by('id', 'DESC');
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
            $acciones .= $this->btn_tabla('verNomina(' . $id . ')', 'btn-outline-primary', 'fa-eye', 'Ver / editar detalle');

            if ($nomina->estatus === 'Borrador') {
                $acciones .= $this->btn_tabla('eliminarNomina(' . $id . ')', 'btn-outline-danger', 'fa-trash', 'Eliminar borrador');
            } elseif ($nomina->estatus === 'Calculada') {
                $folio_js = str_replace("'", "\\'", $nomina->folio);
                $acciones .= $this->btn_tabla('pedirCancelarNomina(' . $id . ', \'' . $folio_js . '\')', 'btn-outline-warning', 'fa-ban', 'Cancelar nómina');
            } elseif (in_array($nomina->estatus, ['Parcial', 'Pagada'], true)) {
                $acciones .= $this->btn_tabla('verNomina(' . $id . ', true)', 'btn-outline-warning', 'fa-sticky-note', 'Notas de ajuste');
            }
            if (in_array($nomina->estatus, ['Calculada', 'Parcial', 'Pagada'], true)) {
                $acciones .= $this->btn_tabla('exportarExcel(' . $id . ')', 'btn-outline-success', 'fa-file-excel', 'Exportar Excel');
            }
            if (in_array($nomina->estatus, ['Parcial', 'Pagada'], true)) {
                $acciones .= $this->btn_tabla('verRecibosNomina(' . $id . ')', 'btn-outline-secondary', 'fa-print', 'Recibos de pago');
            }
            if ($nomina->estatus === 'Pagada' && !empty($nomina->poliza_id)) {
                $acciones .= '<a href="' . base_url('contabilidad/Polizas') . '" class="btn btn-outline-info" title="Ver póliza #' . (int)$nomina->poliza_id . '"><i class="fas fa-book"></i></a>';
            }
            $acciones .= '</div>';

            $periodoSortKey = (int)date('Ymd', strtotime($nomina->periodo_inicio));
            $periodoHtml = date('d/m/Y', strtotime($nomina->periodo_inicio))
                . ' — '
                . date('d/m/Y', strtotime($nomina->periodo_fin));
            $fechaPagoHtml = date('d/m/Y', strtotime($nomina->fecha_pago));

            $data[] = [
                '<strong>' . htmlspecialchars($nomina->folio) . '</strong>',
                '<span class="badge bg-' . $badge_tipo . '">' . htmlspecialchars($nomina->tipo_nomina) . '</span>',
                $periodoHtml,
                $fechaPagoHtml,
                '<span class="text-end d-block" data-order="' . (float)($nomina->total_percepciones ?? 0) . '">$' . number_format((float)($nomina->total_percepciones ?? 0), 2) . '</span>',
                '<span class="text-end d-block text-danger" data-order="' . (float)($nomina->total_deducciones ?? 0) . '">$' . number_format((float)($nomina->total_deducciones ?? 0), 2) . '</span>',
                '<strong class="text-end d-block" data-order="' . (float)($nomina->total_neto ?? 0) . '">$' . number_format((float)($nomina->total_neto ?? 0), 2) . '</strong>',
                '<span class="badge bg-' . $badge_estatus . '">' . htmlspecialchars($nomina->estatus) . '</span>',
                $btn_pago,
                $acciones,
                $periodoSortKey,
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
        $this->requiere_permiso('rh_nomina');
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
        $this->requiere_permiso('rh_nomina');
        $nomina_id = (int)$this->input->post('id');
        $result = $this->NominaRhModel->calcular_nomina($nomina_id);
        echo json_encode($result);
    }

    public function pagar_ajax() {
        $this->requiere_permiso('rh_nomina');
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
        $this->requiere_permiso('rh_nomina');
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
        $this->requiere_permiso('rh_nomina');
        $id = (int)$this->input->post('id');
        $nomina = $this->NominaModel->get_nomina_completa($id);

        if (!$nomina) {
            echo json_encode(['success' => false, 'message' => 'Nómina no encontrada']);
            return;
        }

        echo json_encode(['success' => true, 'nomina' => $nomina]);
    }

    public function eliminar_ajax() {
        $this->requiere_permiso('rh_nomina');
        $id = (int)$this->input->post('id');
        $nomina = $this->db->get_where('nominas', ['id' => $id])->row();
        if (!$nomina) {
            echo json_encode(['success' => false, 'message' => 'Nómina no encontrada']);
            return;
        }
        // Borrador → hard delete (igual que antes)
        if ($nomina->estatus === 'Borrador') {
            $detalle_ids = $this->db->select('id')->from('nominas_detalle')->where('nomina_id', $id)->get()->result();
            foreach ($detalle_ids as $det) {
                $this->db->where('nomina_detalle_id', $det->id)->delete('nominas_conceptos');
            }
            $this->db->where('nomina_id', $id)->delete('nominas_detalle');
            $this->db->where('nomina_id', $id)->delete('nominas_pagos_log');
            $this->db->where('id', $id)->delete('nominas');
            echo json_encode(['success' => true, 'message' => 'Nómina eliminada permanentemente']);
            return;
        }
        // Calculada → soft delete (cancelar)
        if ($nomina->estatus === 'Calculada') {
            $motivo = trim((string)$this->input->post('motivo'));
            if (strlen($motivo) < 10) {
                echo json_encode(['success' => false, 'message' => 'El motivo de cancelación debe tener al menos 10 caracteres']);
                return;
            }
            $result = $this->NominaRhModel->cancelar_nomina($id, $motivo);
            echo json_encode($result);
            return;
        }
        // Pagada / Parcial → no permitir
        echo json_encode(['success' => false, 'message' => 'No se puede cancelar una nómina con pagos procesados. Use el sistema de notas de ajuste.']);
    }

    public function get_notas_ajax() {
        $this->requiere_permiso('rh_nomina');
        $nomina_id = (int)$this->input->post('nomina_id');
        $notas = $this->NominaRhModel->get_notas_nomina($nomina_id);
        echo json_encode(['success' => true, 'notas' => $notas]);
    }

    public function agregar_nota_ajax() {
        $this->requiere_permiso('rh_nomina');
        $nomina_id = (int)$this->input->post('nomina_id');
        $data = [
            'tipo' => $this->input->post('tipo'),
            'descripcion' => $this->input->post('descripcion'),
            'monto' => $this->input->post('monto'),
        ];
        if (empty($data['descripcion'])) {
            echo json_encode(['success' => false, 'message' => 'La descripción es requerida']);
            return;
        }
        $result = $this->NominaRhModel->agregar_nota_nomina($nomina_id, $data);
        echo json_encode($result);
    }

    public function imprimir_recibos($id = null, $detalle_id = null) {
        $this->requiere_permiso('rh_nomina');
        $data = $this->preparar_datos_recibos($id, $this->filtros_recibos_desde_request(true, $detalle_id));
        if (!$data) {
            show_404();
        }

        $this->viewData = array_merge($this->viewData, $data);
        $this->load->view('rh/nomina/recibos', $this->viewData);
    }

    public function get_recibos_ajax() {
        $this->requiere_permiso('rh_nomina');
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

    public function get_recibo_individual_ajax() {
        $this->requiere_permiso('rh_nomina');
        $detalle_id = (int)$this->input->post('detalle_id');

        $this->db->select('nd.*, n.folio, n.periodo_inicio, n.periodo_fin, n.fecha_pago, n.tipo_nomina,
            e.nombre, e.apellido_paterno, e.apellido_materno, e.puesto, e.numero_empleado, e.rfc, e.curp, e.nss,
            e.forma_pago, e.banco, e.cuenta_bancaria');
        $this->db->from('nominas_detalle nd');
        $this->db->join('nominas n', 'n.id = nd.nomina_id');
        $this->db->join('empleados e', 'e.id = nd.empleado_id');
        $this->db->where('nd.id', $detalle_id);
        $detalle = $this->db->get()->row();

        if (!$detalle) {
            $this->responder_json(['success' => false, 'message' => 'Detalle no encontrado']);
            return;
        }

        $conceptos = $this->db->get_where('nominas_conceptos', ['nomina_detalle_id' => $detalle_id])->result();
        $empresa = $this->_get_empresa_config();

        $nomina = (object)[
            'folio'          => $detalle->folio,
            'tipo_nomina'    => $detalle->tipo_nomina,
            'periodo_inicio' => $detalle->periodo_inicio,
            'periodo_fin'    => $detalle->periodo_fin,
            'fecha_pago'     => $detalle->fecha_pago,
        ];
        $det = $detalle;
        $det->conceptos = $conceptos;

        $html = $this->load->view('rh/nomina/partials/recibos_estilos', [], true);
        $html .= '<div class="recibos-nomina-wrap">';
        $html .= $this->load->view('rh/nomina/partials/recibo_item', [
            'det'         => $det,
            'nomina'      => $nomina,
            'empresa'     => $empresa,
            'montos_lote' => [],
        ], true);
        $html .= '</div>';

        $this->responder_json(['success' => true, 'html' => $html]);
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
            'empresa'      => $this->_get_empresa_config(),
        ];
    }

    /**
     * Datos de empresa (logo, razón social, RFC, etc.) desde configuracion_empresa.
     */
    private function _get_empresa_config() {
        $this->load->model('Config/EmpresaModel');
        return $this->EmpresaModel->get_config();
    }

    /**
     * Ruta absoluta del logo de empresa (fallback a marca CHISA).
     */
    private function _get_logo_abs_path($empresa = null) {
        $empresa = $empresa ?: $this->_get_empresa_config();
        $rel = !empty($empresa->logo)
            ? $empresa->logo
            : 'assets/dist/img/brands/chisa_recubrimientos_logo.jpg';
        $rel = ltrim(str_replace(['\\'], '/', $rel), '/');
        $abs = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        if (!is_file($abs)) {
            $fallback = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR
                . 'assets' . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'img'
                . DIRECTORY_SEPARATOR . 'brands' . DIRECTORY_SEPARATOR . 'chisa_recubrimientos_logo.jpg';
            return is_file($fallback) ? $fallback : null;
        }
        return $abs;
    }

    private function _empresa_direccion($empresa) {
        return trim(implode(', ', array_filter([
            $empresa->calle ?? '',
            $empresa->numero_exterior ?? '',
            !empty($empresa->numero_interior) ? ('Int. ' . $empresa->numero_interior) : '',
            $empresa->colonia ?? '',
            $empresa->ciudad ?? '',
            $empresa->estado ?? '',
            $empresa->codigo_postal ?? '',
        ], 'strlen')));
    }

    /**
     * Exporta nómina a Excel compatible con Aspel NOI / importación de nómina.
     */
    public function exportar_excel($id = null) {
        $this->requiere_permiso('rh_nomina_exportar');
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

    public function get_nomina_detalle_completo_ajax() {
        $this->requiere_permiso('rh_nomina');
        $id = (int)$this->input->post('id');
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        $detalle = $this->NominaRhModel->get_nomina_detalle_completo($id);
        $nomina = $this->db->get_where('nominas', ['id' => $id])->row();
        echo json_encode([
            'success' => true,
            'nomina'  => $nomina,
            'detalle' => $detalle,
        ]);
    }

    public function get_nomina_cuentas_ajax() {
        $this->requiere_permiso('rh_nomina_cuentas');
        $empleado_id = (int)$this->input->post('empleado_id');
        $cuentas = $this->NominaRhModel->get_cuentas_empleado($empleado_id);
        echo json_encode(['success' => true, 'cuentas' => $cuentas]);
    }

    public function guardar_cuenta_empleado_ajax() {
        $this->requiere_permiso('rh_nomina_cuentas');
        $data = [
            'id'                => $this->input->post('id') ? (int)$this->input->post('id') : null,
            'empleado_id'       => (int)$this->input->post('empleado_id'),
            'cuenta_bancaria_id'=> $this->input->post('cuenta_bancaria_id') ? (int)$this->input->post('cuenta_bancaria_id') : null,
            'numero_cuenta'     => $this->input->post('numero_cuenta'),
            'clabe'             => $this->input->post('clabe'),
            'tipo'              => $this->input->post('tipo') ?: 'cuenta',
            'numero_tarjeta'    => $this->input->post('numero_tarjeta') ?: null,
            'es_default'        => (int)$this->input->post('es_default'),
        ];
        if (empty($data['empleado_id']) || (empty($data['numero_cuenta']) && $data['tipo'] === 'cuenta')) {
            echo json_encode(['success' => false, 'message' => 'Complete los campos requeridos']);
            return;
        }
        if ($data['tipo'] === 'tarjeta' && empty($data['numero_tarjeta'])) {
            echo json_encode(['success' => false, 'message' => 'Ingrese el número de tarjeta']);
            return;
        }
        $id = $this->NominaRhModel->guardar_cuenta_empleado($data);
        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Cuenta guardada']);
    }

    public function eliminar_cuenta_empleado_ajax() {
        $this->requiere_permiso('rh_nomina_cuentas');
        $id = (int)$this->input->post('id');
        $this->NominaRhModel->eliminar_cuenta_empleado($id);
        echo json_encode(['success' => true, 'message' => 'Cuenta eliminada']);
    }

    public function set_cuenta_default_ajax() {
        $this->requiere_permiso('rh_nomina_cuentas');
        $empleado_id = (int)$this->input->post('empleado_id');
        $cuenta_id   = (int)$this->input->post('cuenta_id');
        $this->NominaRhModel->set_cuenta_default($empleado_id, $cuenta_id);
        echo json_encode(['success' => true, 'message' => 'Cuenta principal actualizada']);
    }

    /**
     * Retorna datos completos del empleado para edición desde el panel de nómina.
     */
    public function get_empleado_edit_ajax() {
        $this->requiere_permiso('rh_nomina');
        $id = (int)$this->input->post('id');
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        $empleado = $this->NominaRhModel->get_empleado_para_edicion($id);
        if (!$empleado) {
            echo json_encode(['success' => false, 'message' => 'Empleado no encontrado']);
            return;
        }
        echo json_encode(['success' => true, 'empleado' => $empleado]);
    }

    /**
     * Guarda datos del empleado desde el panel de edición de nómina.
     */
    public function guardar_empleado_desde_nomina_ajax() {
        $this->requiere_permiso('rh_nomina');
        $id = (int)$this->input->post('id');
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }
        $data = [
            'tipo_nomina'                   => $this->input->post('tipo_nomina'),
            'salario_base_diario'           => $this->input->post('salario_base_diario'),
            'salario_base_mensual'          => $this->input->post('salario_base_diario')
                ? round((float)$this->input->post('salario_base_diario') * 30, 2)
                : 0,
            'forma_pago'                    => $this->input->post('forma_pago'),
            'banco'                         => $this->input->post('banco'),
            'cuenta_bancaria'               => $this->input->post('cuenta_bancaria'),
            'descuento_infonavit'           => $this->input->post('descuento_infonavit'),
            'tiene_infonavit'               => ((float)$this->input->post('descuento_infonavit') > 0) ? 1 : 0,
            'isr_porcentaje'                => $this->input->post('isr_porcentaje'),
            'imss_cuota'                    => $this->input->post('imss_cuota'),
            'pension_alimenticia_porcentaje'=> $this->input->post('pension_alimenticia_porcentaje'),
        ];
        $result = $this->NominaRhModel->guardar_empleado_desde_nomina($id, $data);
        echo json_encode($result);
    }

    public function actualizar_detalle_ajax() {
        $this->requiere_permiso('rh_nomina_editar_detalle');
        $detalle_id = (int)$this->input->post('detalle_id');
        $data = $this->input->post();
        unset($data['detalle_id']);
        $result = $this->NominaRhModel->actualizar_detalle_nomina($detalle_id, $data);
        echo json_encode($result);
    }

    public function get_configuracion_ajax() {
        $this->requiere_permiso('rh_nomina_configurar');
        $config = $this->NominaRhModel->get_configuracion_automatizacion();
        echo json_encode(['success' => true, 'config' => $config]);
    }

    public function guardar_configuracion_ajax() {
        $this->requiere_permiso('rh_nomina_configurar', 'No tienes permiso para configurar la automatización.');
        $data = [
            'frecuencia'        => $this->input->post('frecuencia'),
            'auto_crear'        => (int)$this->input->post('auto_crear'),
            'crear_dias_antes'  => (int)$this->input->post('crear_dias_antes'),
            'aplicar_infonavit' => (int)$this->input->post('aplicar_infonavit'),
            'aplicar_isr'       => (int)$this->input->post('aplicar_isr'),
            'aplicar_imss'      => (int)$this->input->post('aplicar_imss'),
        ];
        $this->NominaRhModel->guardar_configuracion_automatizacion($data);
        echo json_encode(['success' => true, 'message' => 'Configuración guardada']);
    }

    /**
     * Verifica/crea nómina automática (AJAX o CRON CLI).
     * Cron sugerido:
     *   0 7 * * * php index.php rh/Nomina verificar_auto_nomina_ajax
     * Prueba con fecha:
     *   php index.php rh/Nomina verificar_auto_nomina_ajax 2026-07-26
     */
    public function verificar_auto_nomina_ajax($fecha_ref = null) {
        if (!is_cli()) {
            $this->requiere_permiso('rh_nomina');
            $fecha_ref = $this->input->post('fecha_ref') ?: $fecha_ref;
        }

        $resultado = $this->NominaRhModel->crear_nomina_automatica($fecha_ref ?: null);
        if ($resultado && is_array($resultado)) {
            $payload = [
                'success'   => true,
                'creada'    => true,
                'nomina_id' => $resultado['nomina_id'],
                'calculada' => !empty($resultado['calculada']),
                'estatus'   => $resultado['estatus'] ?? null,
                'totales'   => $resultado['totales'] ?? null,
                'message'   => $resultado['message'] ?? 'Nómina automática creada',
            ];
            if (!empty($resultado['multiple'])) {
                $payload['multiple'] = true;
                $payload['creadas'] = $resultado['creadas'];
            }
        } elseif ($resultado) {
            // Compatibilidad si algún entorno aún devolviera solo el ID
            $payload = ['success' => true, 'creada' => true, 'nomina_id' => (int)$resultado, 'message' => 'Nómina automática creada'];
        } else {
            $payload = ['success' => true, 'creada' => false, 'message' => 'No corresponde crear nómina hoy'];
        }

        if (is_cli()) {
            echo json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL;
            return;
        }
        echo json_encode($payload);
    }

    public function get_catalogo_bancos_ajax() {
        $this->requiere_permiso('rh_nomina');
        $bancos = $this->db
            ->select('id, banco')
            ->from('cuentas_bancarias')
            ->where('estatus', 'Activa')
            ->group_by('banco')
            ->order_by('banco', 'ASC')
            ->get()->result();
        echo json_encode(['success' => true, 'bancos' => $bancos]);
    }

    /**
     * Retorna todas las nóminas de un mes para el planeador visual.
     * GET params: mes (1-12), anio (YYYY), tipo (Semanal|Quincenal|Mensual)
     */
    public function planeador_mensual_ajax() {
        $this->requiere_permiso('rh_nomina');
        $mes  = (int)$this->input->get('mes') ?: (int)date('m');
        $anio = (int)$this->input->get('anio') ?: (int)date('Y');
        $tipo = $this->input->get('tipo') ?: 'Semanal';

        $result = $this->NominaRhModel->get_planeador_mensual($mes, $anio, $tipo);
        $proximas_auto = $this->NominaRhModel->get_proxima_auto_nomina_preview();
        echo json_encode([
            'success'       => true,
            'periodos'      => $result,
            'proximas_auto' => $proximas_auto,
            'mes'           => $mes,
            'anio'          => $anio,
            'tipo'          => $tipo
        ]);
    }

    /**
     * Exporta nómina a Excel (diseño mejorado, multi-hoja).
     * Contiene la misma información operativa que los reportes del contador
     * (relación, transferencias por banco, resumen de pago), sin clonar celdas exactas.
     */
    public function exportar_detalle_excel($id = null) {
        $this->requiere_permiso('rh_nomina_exportar');
        $id = (int)$id;
        $detalle = $this->NominaRhModel->get_nomina_detalle_completo($id);
        $nomina = $this->db->get_where('nominas', ['id' => $id])->row();

        if (!$detalle || !$nomina) {
            setViewError('Nómina no encontrada');
            redirect('rh/Nomina');
            return;
        }

        $periodoTxt = date('d/m/Y', strtotime($nomina->periodo_inicio))
            . ' al ' . date('d/m/Y', strtotime($nomina->periodo_fin));
        $fechaPagoTxt = date('d/m/Y', strtotime($nomina->fecha_pago));
        $empresa = $this->_get_empresa_config();

        $spreadsheet = new Spreadsheet();

        // —— Hoja 1: Relación de nómina (agrupada por obra) ——
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Relacion Nomina');
        $this->_excel_hoja_relacion($sheet, $nomina, $detalle, $periodoTxt, $fechaPagoTxt, $empresa);

        // —— Hoja 2: Transferencias / depósitos (por banco) ——
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Transferencias');
        $this->_excel_hoja_transferencias($sheet2, $nomina, $detalle, $periodoTxt, $fechaPagoTxt, $empresa);

        // —— Hoja 3: Resumen de desembolso ——
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Resumen Pago');
        $this->_excel_hoja_resumen_pago($sheet3, $nomina, $detalle, $periodoTxt, $fechaPagoTxt, $empresa);

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Nomina_' . $nomina->folio . '_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /** Estilos base reutilizables para exportación de nómina. */
    private function _excel_styles() {
        return [
            'title' => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'subtitle' => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '2D5A8E']],
            ],
            'meta' => [
                'font' => ['size' => 10, 'color' => ['rgb' => '555555']],
            ],
            'header' => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
            'header_perc' => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '198754']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
            'header_ded' => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC3545']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
            'group' => [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1E3A5F']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8EEF5']],
            ],
            'subtotal' => [
                'font' => ['bold' => true, 'size' => 9],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F4F8']],
            ],
            'total' => [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            ],
            'money' => ['numberFormat' => ['formatCode' => '#,##0.00']],
            'thin' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
            ],
        ];
    }

    private function _excel_nombre_empleado($d) {
        return trim(($d->nombre ?? '') . ' ' . ($d->apellido_paterno ?? '') . ' ' . ($d->apellido_materno ?? ''));
    }

    private function _excel_forma_pago($d) {
        $f = trim($d->forma_pago ?? '');
        return $f !== '' ? $f : 'Sin definir';
    }

    private function _excel_banco($d) {
        return trim($d->banco_pago ?? $d->banco ?? '') ?: '—';
    }

    private function _excel_cuenta($d) {
        return trim($d->cuenta_pago ?? $d->cuenta_bancaria ?? '') ?: '—';
    }

    /**
     * Cabecera con logo + datos de empresa (mismo origen que recibos/POS).
     * @return int Fila donde deben iniciar los encabezados de tabla
     */
    private function _excel_cabecera_empresa($sheet, $titulo, $periodoTxt, $fechaPagoTxt, $nomina, $lastCol, $empresa = null) {
        $s = $this->_excel_styles();
        $empresa = $empresa ?: $this->_get_empresa_config();
        $razon = strtoupper($empresa->razon_social ?: ($empresa->nombre_comercial ?: 'CHISA RECUBRIMIENTOS, SA DE CV'));
        $rfc = trim($empresa->rfc ?? '');
        $tel = trim($empresa->telefono ?? '');
        $email = trim($empresa->email ?? '');
        $web = trim($empresa->sitio_web ?? '');
        $dir = $this->_empresa_direccion($empresa);

        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(16);
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(12);

        $logoAbs = $this->_get_logo_abs_path($empresa);
        if ($logoAbs) {
            try {
                $drawing = new Drawing();
                $drawing->setName('Logo ' . ($empresa->nombre_comercial ?: 'Empresa'));
                $drawing->setDescription($razon);
                $drawing->setPath($logoAbs);
                $drawing->setHeight(58);
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(4);
                $drawing->setOffsetY(4);
                $drawing->setWorksheet($sheet);
            } catch (\Throwable $e) {
                log_message('error', 'Excel nómina: no se pudo incrustar logo: ' . $e->getMessage());
            }
        }

        $infoStart = 'C';
        $sheet->mergeCells($infoStart . '1:' . $lastCol . '1');
        $sheet->setCellValue($infoStart . '1', $razon);
        $sheet->getStyle($infoStart . '1')->applyFromArray($s['title']);

        $linea2 = array_filter([
            $rfc !== '' ? ('RFC: ' . $rfc) : null,
            $tel !== '' ? ('Tel: ' . $tel) : null,
            $email !== '' ? $email : null,
            $web !== '' ? $web : null,
        ]);
        $sheet->mergeCells($infoStart . '2:' . $lastCol . '2');
        $sheet->setCellValue($infoStart . '2', implode('  ·  ', $linea2));
        $sheet->getStyle($infoStart . '2')->applyFromArray($s['meta']);

        $sheet->mergeCells($infoStart . '3:' . $lastCol . '3');
        $sheet->setCellValue($infoStart . '3', $dir !== '' ? $dir : '');
        $sheet->getStyle($infoStart . '3')->applyFromArray($s['meta']);

        $sheet->mergeCells('A4:' . $lastCol . '4');
        $sheet->setCellValue('A4', $titulo);
        $sheet->getStyle('A4')->applyFromArray($s['subtitle']);
        $sheet->getRowDimension(4)->setRowHeight(20);

        $sheet->setCellValue('A5', 'Periodo: ' . $periodoTxt);
        $sheet->setCellValue('C5', 'Fecha pago: ' . $fechaPagoTxt);
        $sheet->setCellValue('E5', 'Folio: ' . $nomina->folio . ' · ' . $nomina->tipo_nomina . ' · ' . $nomina->estatus);
        $sheet->getStyle('A5:E5')->applyFromArray($s['meta']);
        $sheet->getRowDimension(6)->setRowHeight(8);

        return 7;
    }

    /** Hoja principal: relación agrupada por obra/lugar. */
    private function _excel_hoja_relacion($sheet, $nomina, $detalle, $periodoTxt, $fechaPagoTxt, $empresa = null) {
        $s = $this->_excel_styles();
        $lastCol = 'R';
        $headerRow = $this->_excel_cabecera_empresa(
            $sheet, 'RELACIÓN DE NÓMINA', $periodoTxt, $fechaPagoTxt, $nomina, $lastCol, $empresa
        );

        $headers = [
            'A' => 'Obra / Origen', 'B' => 'No.', 'C' => 'Nombre del trabajador',
            'D' => 'Sueldo diario', 'E' => 'Sueldo periodo', 'F' => 'Horas extras',
            'G' => 'Monto H.E.', 'H' => 'Comidas', 'I' => 'Pasajes / Viáticos',
            'J' => 'Prima', 'K' => 'Otros bonos', 'L' => 'Otros',
            'M' => 'Total percepciones', 'N' => 'INFONAVIT', 'O' => 'Préstamo',
            'P' => 'Otros desc.', 'Q' => 'Total deducciones', 'R' => 'Sueldo neto',
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $headerRow, $label);
        }
        $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->applyFromArray($s['header']);
        $sheet->getStyle('F' . $headerRow . ':M' . $headerRow)->applyFromArray($s['header_perc']);
        $sheet->getStyle('N' . $headerRow . ':Q' . $headerRow)->applyFromArray($s['header_ded']);
        $sheet->getStyle('R' . $headerRow)->applyFromArray($s['header']);
        $sheet->getRowDimension($headerRow)->setRowHeight(32);
        $sheet->freezePane('D' . ($headerRow + 1));

        $grupos = [];
        foreach ($detalle as $d) {
            $key = trim($d->lugar_origen ?? '') ?: 'SIN ASIGNAR';
            $grupos[$key][] = $d;
        }
        ksort($grupos, SORT_NATURAL | SORT_FLAG_CASE);

        $row = $headerRow + 1;
        $granTotal = array_fill_keys(
            ['diario','periodo','he','mhe','comidas','pasajes','prima','bonos','otros','perc','inf','prest','odesc','ded','neto'],
            0
        );
        $nGlobal = 0;

        foreach ($grupos as $obra => $empleados) {
            $sheet->mergeCells('A' . $row . ':' . $lastCol . $row);
            $sheet->setCellValue('A' . $row, strtoupper($obra) . '  (' . count($empleados) . ')');
            $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray($s['group']);
            $row++;

            $sub = array_fill_keys(array_keys($granTotal), 0);
            $n = 0;
            foreach ($empleados as $d) {
                $n++;
                $nGlobal++;
                $vals = [
                    'diario'  => (float)$d->sueldo_diario,
                    'periodo' => (float)$d->sueldo_base,
                    'he'      => (float)$d->horas_extras,
                    'mhe'     => (float)$d->monto_horas_extras,
                    'comidas' => (float)$d->comidas,
                    'pasajes' => (float)$d->viaticos_pasajes,
                    'prima'   => (float)$d->prima,
                    'bonos'   => (float)$d->otros_bonos,
                    'otros'   => (float)$d->otros_ingresos,
                    'perc'    => (float)$d->percepciones,
                    'inf'     => (float)$d->infonavit_descuento,
                    'prest'   => (float)$d->prestamo_personal,
                    'odesc'   => (float)$d->otros_descuentos,
                    'ded'     => (float)$d->deducciones,
                    'neto'    => (float)$d->neto,
                ];
                foreach ($vals as $k => $v) {
                    $sub[$k] += $v;
                    $granTotal[$k] += $v;
                }

                $sheet->setCellValue('A' . $row, $obra);
                $sheet->setCellValue('B' . $row, $n);
                $sheet->setCellValue('C' . $row, $this->_excel_nombre_empleado($d));
                $sheet->setCellValue('D' . $row, $vals['diario']);
                $sheet->setCellValue('E' . $row, $vals['periodo']);
                $sheet->setCellValue('F' . $row, $vals['he']);
                $sheet->setCellValue('G' . $row, $vals['mhe']);
                $sheet->setCellValue('H' . $row, $vals['comidas']);
                $sheet->setCellValue('I' . $row, $vals['pasajes']);
                $sheet->setCellValue('J' . $row, $vals['prima']);
                $sheet->setCellValue('K' . $row, $vals['bonos']);
                $sheet->setCellValue('L' . $row, $vals['otros']);
                $sheet->setCellValue('M' . $row, $vals['perc']);
                $sheet->setCellValue('N' . $row, $vals['inf']);
                $sheet->setCellValue('O' . $row, $vals['prest']);
                $sheet->setCellValue('P' . $row, $vals['odesc']);
                $sheet->setCellValue('Q' . $row, $vals['ded']);
                $sheet->setCellValue('R' . $row, $vals['neto']);
                $sheet->getStyle('D' . $row . ':R' . $row)->applyFromArray($s['money']);
                if ($n % 2 === 0) {
                    $sheet->getStyle('A' . $row . ':R' . $row)->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FAFBFC');
                }
                $row++;
            }

            $sheet->setCellValue('C' . $row, 'Subtotal ' . strtoupper($obra));
            $sheet->setCellValue('D' . $row, $sub['diario']);
            $sheet->setCellValue('E' . $row, $sub['periodo']);
            $sheet->setCellValue('F' . $row, $sub['he']);
            $sheet->setCellValue('G' . $row, $sub['mhe']);
            $sheet->setCellValue('H' . $row, $sub['comidas']);
            $sheet->setCellValue('I' . $row, $sub['pasajes']);
            $sheet->setCellValue('J' . $row, $sub['prima']);
            $sheet->setCellValue('K' . $row, $sub['bonos']);
            $sheet->setCellValue('L' . $row, $sub['otros']);
            $sheet->setCellValue('M' . $row, $sub['perc']);
            $sheet->setCellValue('N' . $row, $sub['inf']);
            $sheet->setCellValue('O' . $row, $sub['prest']);
            $sheet->setCellValue('P' . $row, $sub['odesc']);
            $sheet->setCellValue('Q' . $row, $sub['ded']);
            $sheet->setCellValue('R' . $row, $sub['neto']);
            $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray($s['subtotal']);
            $sheet->getStyle('D' . $row . ':R' . $row)->applyFromArray($s['money']);
            $row++;
        }

        $sheet->setCellValue('C' . $row, 'TOTAL NÓMINA (' . $nGlobal . ' empleados)');
        $sheet->setCellValue('D' . $row, $granTotal['diario']);
        $sheet->setCellValue('E' . $row, $granTotal['periodo']);
        $sheet->setCellValue('F' . $row, $granTotal['he']);
        $sheet->setCellValue('G' . $row, $granTotal['mhe']);
        $sheet->setCellValue('H' . $row, $granTotal['comidas']);
        $sheet->setCellValue('I' . $row, $granTotal['pasajes']);
        $sheet->setCellValue('J' . $row, $granTotal['prima']);
        $sheet->setCellValue('K' . $row, $granTotal['bonos']);
        $sheet->setCellValue('L' . $row, $granTotal['otros']);
        $sheet->setCellValue('M' . $row, $granTotal['perc']);
        $sheet->setCellValue('N' . $row, $granTotal['inf']);
        $sheet->setCellValue('O' . $row, $granTotal['prest']);
        $sheet->setCellValue('P' . $row, $granTotal['odesc']);
        $sheet->setCellValue('Q' . $row, $granTotal['ded']);
        $sheet->setCellValue('R' . $row, $granTotal['neto']);
        $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray($s['total']);
        $sheet->getStyle('D' . $row . ':R' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $headerRow . ':R' . $row)->applyFromArray($s['thin']);

        foreach (range('A', 'R') as $c) {
            if (!in_array($c, ['A', 'B'], true)) {
                $sheet->getColumnDimension($c)->setAutoSize(true);
            }
        }
        $sheet->getColumnDimension('C')->setWidth(32);
    }

    /** Hoja de transferencias/depósitos: listado por banco. */
    private function _excel_hoja_transferencias($sheet, $nomina, $detalle, $periodoTxt, $fechaPagoTxt, $empresa = null) {
        $s = $this->_excel_styles();
        $headerRow = $this->_excel_cabecera_empresa(
            $sheet, 'RELACIÓN DE TRANSFERENCIAS / DEPÓSITOS', $periodoTxt, $fechaPagoTxt, $nomina, 'E', $empresa
        );

        $filas = [];
        foreach ($detalle as $d) {
            $banco = $this->_excel_banco($d);
            $cuenta = $this->_excel_cuenta($d);
            if ($banco === '—' && $cuenta === '—') {
                continue;
            }
            $filas[] = [
                'nombre' => $this->_excel_nombre_empleado($d),
                'banco'  => $banco,
                'cuenta' => $cuenta,
                'forma'  => $this->_excel_forma_pago($d),
                'neto'   => (float)$d->neto,
            ];
        }

        usort($filas, function ($a, $b) {
            $c = strcasecmp($a['banco'], $b['banco']);
            return $c !== 0 ? $c : strcasecmp($a['nombre'], $b['nombre']);
        });

        $headers = ['No.', 'Empleado', 'Banco', 'Cuenta o tarjeta', 'Total'];
        foreach (['A', 'B', 'C', 'D', 'E'] as $i => $col) {
            $sheet->setCellValue($col . $headerRow, $headers[$i]);
        }
        $sheet->getStyle('A' . $headerRow . ':E' . $headerRow)->applyFromArray($s['header']);
        $sheet->freezePane('A' . ($headerRow + 1));

        $row = $headerRow + 1;
        $bancoActual = null;
        $subBanco = 0;
        $n = 0;
        $granTotal = 0;

        $flushSubtotal = function () use (&$sheet, &$row, &$subBanco, &$bancoActual, $s) {
            if ($bancoActual === null) return;
            $sheet->setCellValue('B' . $row, 'Subtotal ' . $bancoActual);
            $sheet->setCellValue('E' . $row, $subBanco);
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($s['subtotal']);
            $sheet->getStyle('E' . $row)->applyFromArray($s['money']);
            $row++;
            $subBanco = 0;
        };

        foreach ($filas as $f) {
            if ($bancoActual !== null && strcasecmp($bancoActual, $f['banco']) !== 0) {
                $flushSubtotal();
            }
            if ($bancoActual === null || strcasecmp($bancoActual, $f['banco']) !== 0) {
                $bancoActual = $f['banco'];
                $sheet->mergeCells('A' . $row . ':E' . $row);
                $sheet->setCellValue('A' . $row, strtoupper($bancoActual));
                $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($s['group']);
                $row++;
            }

            $n++;
            $sheet->setCellValue('A' . $row, $n);
            $sheet->setCellValue('B' . $row, $f['nombre']);
            $sheet->setCellValue('C' . $row, $f['banco']);
            $sheet->setCellValue('D' . $row, $f['cuenta']);
            $sheet->setCellValue('E' . $row, $f['neto']);
            $sheet->getStyle('E' . $row)->applyFromArray($s['money']);
            $subBanco += $f['neto'];
            $granTotal += $f['neto'];
            $row++;
        }
        $flushSubtotal();

        if ($n === 0) {
            $sheet->setCellValue('A' . $row, 'Sin empleados con transferencia/cuenta registrada en esta nómina.');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $row++;
        } else {
            $sheet->setCellValue('B' . $row, 'TOTAL TRANSFERENCIAS (' . $n . ')');
            $sheet->setCellValue('E' . $row, $granTotal);
            $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray($s['total']);
            $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        }
        $sheet->getStyle('A' . $headerRow . ':E' . max($headerRow, $row - 1))->applyFromArray($s['thin']);

        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(36);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(14);
    }

    /** Resumen de desembolso: cheque vs transferencia vs efectivo. */
    private function _excel_hoja_resumen_pago($sheet, $nomina, $detalle, $periodoTxt, $fechaPagoTxt, $empresa = null) {
        $s = $this->_excel_styles();
        $headerRow = $this->_excel_cabecera_empresa(
            $sheet, 'RESUMEN DE DESEMBOLSO', $periodoTxt, $fechaPagoTxt, $nomina, 'D', $empresa
        );

        $porForma = [];
        $porBanco = [];
        $totalNeto = 0;
        foreach ($detalle as $d) {
            $forma = $this->_excel_forma_pago($d);
            $banco = $this->_excel_banco($d);
            $neto = (float)$d->neto;
            if (!isset($porForma[$forma])) $porForma[$forma] = ['count' => 0, 'neto' => 0];
            $porForma[$forma]['count']++;
            $porForma[$forma]['neto'] += $neto;
            if (!isset($porBanco[$banco])) $porBanco[$banco] = ['count' => 0, 'neto' => 0];
            $porBanco[$banco]['count']++;
            $porBanco[$banco]['neto'] += $neto;
            $totalNeto += $neto;
        }

        $row = $headerRow;
        $sheet->setCellValue('A' . $row, 'Por forma de pago');
        $sheet->getStyle('A' . $row)->applyFromArray($s['subtitle']);
        $row++;
        $hdrForma = $row;
        $sheet->setCellValue('A' . $row, 'Forma de pago');
        $sheet->setCellValue('B' . $row, 'Empleados');
        $sheet->setCellValue('C' . $row, 'Monto');
        $sheet->setCellValue('D' . $row, '% del total');
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($s['header']);
        $row++;

        ksort($porForma);
        foreach ($porForma as $forma => $info) {
            $sheet->setCellValue('A' . $row, $forma);
            $sheet->setCellValue('B' . $row, $info['count']);
            $sheet->setCellValue('C' . $row, $info['neto']);
            $pct = $totalNeto > 0 ? round(($info['neto'] / $totalNeto) * 100, 1) : 0;
            $sheet->setCellValue('D' . $row, $pct . '%');
            $sheet->getStyle('C' . $row)->applyFromArray($s['money']);
            $row++;
        }
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->setCellValue('B' . $row, count($detalle));
        $sheet->setCellValue('C' . $row, $totalNeto);
        $sheet->setCellValue('D' . $row, '100%');
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($s['total']);
        $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle('A' . $hdrForma . ':D' . $row)->applyFromArray($s['thin']);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Por banco / medio de depósito');
        $sheet->getStyle('A' . $row)->applyFromArray($s['subtitle']);
        $row++;
        $hdr = $row;
        $sheet->setCellValue('A' . $row, 'Banco');
        $sheet->setCellValue('B' . $row, 'Empleados');
        $sheet->setCellValue('C' . $row, 'Monto');
        $sheet->setCellValue('D' . $row, '% del total');
        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($s['header']);
        $row++;

        uasort($porBanco, function ($a, $b) { return $b['neto'] <=> $a['neto']; });
        foreach ($porBanco as $banco => $info) {
            $sheet->setCellValue('A' . $row, $banco);
            $sheet->setCellValue('B' . $row, $info['count']);
            $sheet->setCellValue('C' . $row, $info['neto']);
            $pct = $totalNeto > 0 ? round(($info['neto'] / $totalNeto) * 100, 1) : 0;
            $sheet->setCellValue('D' . $row, $pct . '%');
            $sheet->getStyle('C' . $row)->applyFromArray($s['money']);
            $row++;
        }
        $sheet->getStyle('A' . $hdr . ':D' . ($row - 1))->applyFromArray($s['thin']);

        $row += 2;
        $sheet->setCellValue('A' . $row, 'Guía de desembolso sugerida');
        $sheet->getStyle('A' . $row)->applyFromArray($s['subtitle']);
        $row++;
        $cheque = $porForma['Cheque']['neto'] ?? 0;
        $transfer = $porForma['Transferencia']['neto'] ?? 0;
        $deposito = $porForma['Depósito']['neto'] ?? 0;
        $efectivo = $porForma['Efectivo']['neto'] ?? 0;
        $otros = $totalNeto - $cheque - $transfer - $deposito - $efectivo;

        $guia = [
            ['CHEQUE (sueldos en cheque)', $cheque],
            ['TRANSFERENCIA BANCARIA', $transfer],
            ['DEPÓSITO', $deposito],
            ['EFECTIVO', $efectivo],
        ];
        if ($otros > 0.009) {
            $guia[] = ['OTROS / SIN DEFINIR', $otros];
        }
        $guia[] = ['TOTAL A DESEMBOLSAR', $totalNeto];

        $sheet->setCellValue('A' . $row, 'Concepto');
        $sheet->setCellValue('B' . $row, 'Monto');
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray($s['header']);
        $row++;
        $startGuia = $row;
        foreach ($guia as $i => $g) {
            $sheet->setCellValue('A' . $row, $g[0]);
            $sheet->setCellValue('B' . $row, $g[1]);
            $sheet->getStyle('B' . $row)->applyFromArray($s['money']);
            if ($i === count($guia) - 1) {
                $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray($s['total']);
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            }
            $row++;
        }
        $sheet->getStyle('A' . ($startGuia - 1) . ':B' . ($row - 1))->applyFromArray($s['thin']);

        $sheet->getColumnDimension('A')->setWidth(36);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(12);
    }
}
