<?php
/**
 * Cotizaciones - Solicitud y comparación de cotizaciones de compra
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Cotizaciones extends MY_Controller {

    protected $modulo = 'Compras';

    public function __construct() {
        parent::__construct();
        $this->load->helper('permissions');
        $this->load->model('Compras/CotizacionesModel');
        $this->load->model('Compras/ProveedoresModel');
        $this->load->model('Compras/InsumosModel');
    }

    private function puede_consultar() {
        return tiene_permiso('compras_cotizaciones_consult') || tiene_permiso('compras_ordenes_consult');
    }

    private function puede_editar() {
        return tiene_permiso('compras_cotizaciones_edit') || tiene_permiso('compras_ordenes_edit');
    }

    private function puede_crear() {
        return tiene_permiso('compras_cotizaciones_add') || tiene_permiso('compras_ordenes_add');
    }

    private function puede_eliminar() {
        return tiene_permiso('compras_cotizaciones_delete') || tiene_permiso('compras_ordenes_delete');
    }

    private function requiere_consulta($mensaje = null) {
        if (!$this->puede_consultar()) {
            $msg = $mensaje ?: 'No tienes permiso para consultar cotizaciones';
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => $msg]);
                exit;
            }
            redirect('deny');
        }
    }

    public function index() {
        $this->requiere_consulta();

        $this->viewData['pageTitle'] = 'Cotizaciones de Compra';
        $this->viewData['headTitle'] = 'Cotizaciones de Compra';
        $this->viewData['breadcrumb'] = 'Inicio > Compras > Cotizaciones';
        $this->viewData['response'] = [
            'stats' => $this->CotizacionesModel->get_estadisticas(),
            'grupos' => $this->CotizacionesModel->get_grupos_recientes(30),
        ];
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'compras/cotizaciones/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function comparar($grupo_folio = '') {
        $this->requiere_consulta();

        $grupo_folio = $grupo_folio ?: $this->input->get('grupo');
        if (!$grupo_folio) {
            redirect('compras/Cotizaciones');
            return;
        }

        $comparacion = $this->CotizacionesModel->get_comparacion($grupo_folio);
        if (!$comparacion) {
            show_404();
            return;
        }

        $this->viewData['pageTitle'] = 'Comparar Cotizaciones';
        $this->viewData['headTitle'] = 'Comparación de Cotizaciones';
        $this->viewData['breadcrumb'] = 'Inicio > Compras > Cotizaciones > Comparar';
        $this->viewData['response'] = ['comparacion' => $comparacion];
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'compras/cotizaciones/comparar';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function lista_ajax() {
        $this->requiere_consulta();

        $list = $this->CotizacionesModel->get_datatables();
        $data = [];
        $no = (int) ($this->input->post('start') ?? 0);

        foreach ($list as $cot) {
            $no++;
            $row = [];

            $row[] = '<strong>' . htmlspecialchars($cot->folio) . '</strong>';
            $row[] = $cot->grupo_folio ? '<a href="' . base_url('compras/Cotizaciones/comparar/' . rawurlencode($cot->grupo_folio)) . '">' . htmlspecialchars($cot->grupo_folio) . '</a>' : '-';

            $nombre = '<strong>' . htmlspecialchars($cot->razon_social) . '</strong>';
            if ($cot->nombre_comercial) {
                $nombre .= '<br><small class="text-muted">' . htmlspecialchars($cot->nombre_comercial) . '</small>';
            }
            $row[] = $nombre;

            $row[] = date('d/m/Y', strtotime($cot->fecha_solicitud));
            $row[] = $cot->fecha_respuesta ? date('d/m/Y', strtotime($cot->fecha_respuesta)) : '-';
            $row[] = '$' . number_format($cot->total, 2);

            $badges = [
                'Pendiente' => 'warning',
                'Recibida' => 'info',
                'Rechazada' => 'danger',
                'Aprobada' => 'success',
            ];
            $b = $badges[$cot->estatus] ?? 'secondary';
            $row[] = '<span class="badge bg-' . $b . '">' . $cot->estatus . '</span>';

            $acciones = '<div class="btn-acciones-crm">';
            if ($this->puede_consultar()) {
                $acciones .= '<button type="button" class="btn btn-sm btn-secondary" onclick="verCotizacion(' . $cot->id . ')" title="Ver"><i class="fas fa-eye"></i></button>';
            }
            if ($cot->grupo_folio) {
                $acciones .= '<a href="' . base_url('compras/Cotizaciones/comparar/' . rawurlencode($cot->grupo_folio)) . '" class="btn btn-sm btn-info" title="Comparar"><i class="fas fa-columns"></i></a>';
            }
            if ($this->puede_editar() && in_array($cot->estatus, ['Pendiente', 'Recibida'], true)) {
                $acciones .= '<button type="button" class="btn btn-sm btn-primary" onclick="editarCotizacion(' . $cot->id . ')" title="Editar"><i class="fas fa-edit"></i></button>';
            }
            if ($this->puede_crear() && in_array($cot->estatus, ['Pendiente', 'Recibida'], true)) {
                $acciones .= '<button type="button" class="btn btn-sm btn-success" onclick="aprobarCotizacion(' . $cot->id . ')" title="Aprobar → OC"><i class="fas fa-check"></i></button>';
            }
            if ($this->puede_eliminar() && $cot->estatus !== 'Aprobada') {
                $acciones .= '<button type="button" class="btn btn-sm btn-danger" onclick="eliminarCotizacion(' . $cot->id . ')" title="Eliminar"><i class="fas fa-trash"></i></button>';
            }
            if ($cot->orden_compra_id) {
                $acciones .= '<a href="' . base_url('compras/OrdenesCompra') . '" class="btn btn-sm btn-outline-success" title="Ver OC"><i class="fas fa-file-invoice"></i></a>';
            }
            $acciones .= '</div>';
            $row[] = $acciones;

            $data[] = $row;
        }

        echo json_encode([
            'draw' => (int) ($this->input->post('draw') ?? 0),
            'recordsTotal' => $this->CotizacionesModel->count_all(),
            'recordsFiltered' => $this->CotizacionesModel->count_filtered(),
            'data' => $data,
        ]);
    }

    public function get_ajax($id = 0) {
        $this->requiere_consulta();

        $cot = $this->CotizacionesModel->get_cotizacion((int) $id);
        if (!$cot) {
            echo json_encode(['success' => false, 'message' => 'Cotización no encontrada']);
            return;
        }
        echo json_encode(['success' => true, 'cotizacion' => $cot]);
    }

    public function proveedores_ajax() {
        $this->requiere_consulta();
        echo json_encode(['success' => true, 'proveedores' => $this->ProveedoresModel->listar_activos()]);
    }

    public function insumos_ajax() {
        $this->requiere_consulta();
        echo json_encode(['success' => true, 'insumos' => $this->InsumosModel->get_all_insumos(['estatus' => 'Activo'])]);
    }

    public function crear_solicitud_ajax() {
        if (!$this->puede_crear()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear cotizaciones']);
            return;
        }

        $proveedores = $this->input->post('proveedores');
        $insumos_raw = $this->input->post('insumos');
        $observaciones = $this->input->post('observaciones');

        if (!is_array($proveedores) || empty($proveedores)) {
            echo json_encode(['success' => false, 'message' => 'Seleccione al menos un proveedor']);
            return;
        }

        $insumos = [];
        if (is_array($insumos_raw)) {
            foreach ($insumos_raw as $item) {
                if (empty($item['insumo_id']) || empty($item['cantidad'])) {
                    continue;
                }
                $insumos[] = [
                    'insumo_id' => (int) $item['insumo_id'],
                    'cantidad' => (float) $item['cantidad'],
                    'precio_unitario' => (float) ($item['precio_unitario'] ?? 0),
                ];
            }
        }

        if (empty($insumos)) {
            echo json_encode(['success' => false, 'message' => 'Agregue al menos un insumo con cantidad']);
            return;
        }

        $result = $this->CotizacionesModel->crear_solicitud(
            array_map('intval', $proveedores),
            $insumos,
            $observaciones,
            $this->session->userdata('id')
        );

        if (!empty($result['success'])) {
            $this->registrar_bitacora(
                'Solicitud de cotización creada: grupo ' . $result['grupo_folio'],
                'Compras'
            );
        }

        echo json_encode($result);
    }

    public function crear_ajax() {
        if (!$this->puede_crear()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para crear cotizaciones']);
            return;
        }

        $proveedor_id = (int) $this->input->post('proveedor_id');
        if (!$proveedor_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor requerido']);
            return;
        }

        $insumos_raw = $this->input->post('insumos');
        $insumos = [];
        if (is_array($insumos_raw)) {
            foreach ($insumos_raw as $item) {
                if (empty($item['insumo_id'])) {
                    continue;
                }
                $insumos[] = [
                    'insumo_id' => (int) $item['insumo_id'],
                    'cantidad' => (float) ($item['cantidad'] ?? 0),
                    'precio_unitario' => (float) ($item['precio_unitario'] ?? 0),
                ];
            }
        }

        if (empty($insumos)) {
            echo json_encode(['success' => false, 'message' => 'Agregue al menos un insumo']);
            return;
        }

        $cot_id = $this->CotizacionesModel->crear_cotizacion([
            'proveedor_id' => $proveedor_id,
            'fecha_solicitud' => $this->input->post('fecha_solicitud') ?: date('Y-m-d'),
            'observaciones' => $this->input->post('observaciones'),
            'creado_por' => $this->session->userdata('id'),
        ], $insumos);

        if ($cot_id) {
            $cot = $this->CotizacionesModel->get_cotizacion($cot_id);
            $this->registrar_bitacora('Cotización creada: ' . ($cot->folio ?? $cot_id), 'Compras');
            echo json_encode(['success' => true, 'message' => 'Cotización creada', 'cotizacion_id' => $cot_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear cotización']);
        }
    }

    public function editar_ajax() {
        if (!$this->puede_editar()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar cotizaciones']);
            return;
        }

        $id = (int) $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $data = [];
        if ($this->input->post('observaciones') !== null) {
            $data['observaciones'] = $this->input->post('observaciones');
        }
        if ($this->input->post('fecha_solicitud')) {
            $data['fecha_solicitud'] = $this->input->post('fecha_solicitud');
        }

        $detalles_precios = $this->input->post('detalles_precios');
        if (is_array($detalles_precios)) {
            foreach ($detalles_precios as $det_id => $precio) {
                $this->CotizacionesModel->actualizar_detalle((int) $det_id, ['precio_unitario' => (float) $precio]);
            }
        }

        $estatus = $this->input->post('estatus');
        if ($estatus === 'Recibida') {
            $result = $this->CotizacionesModel->marcar_recibida($id);
            echo json_encode($result);
            return;
        }

        if (!empty($data)) {
            $this->CotizacionesModel->actualizar_cotizacion($id, $data);
        }

        $cot = $this->CotizacionesModel->get_cotizacion($id);
        $this->registrar_bitacora('Cotización actualizada: ' . ($cot->folio ?? $id), 'Compras');
        echo json_encode(['success' => true, 'message' => 'Cotización actualizada']);
    }

    public function eliminar_ajax() {
        if (!$this->puede_eliminar()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar cotizaciones']);
            return;
        }

        $id = (int) $this->input->post('id');
        $cot = $this->CotizacionesModel->get_cotizacion($id);
        $result = $this->CotizacionesModel->eliminar_cotizacion($id);
        if (!empty($result['success'])) {
            $this->registrar_bitacora('Cotización eliminada: ' . ($cot->folio ?? $id), 'Compras');
        }
        echo json_encode($result);
    }

    public function marcar_recibida_ajax() {
        if (!$this->puede_editar()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso']);
            return;
        }

        $id = (int) $this->input->post('id');
        $precios = $this->input->post('detalles_precios');
        $result = $this->CotizacionesModel->marcar_recibida($id, is_array($precios) ? $precios : []);
        if (!empty($result['success'])) {
            $cot = $this->CotizacionesModel->get_cotizacion($id);
            $this->registrar_bitacora('Cotización recibida: ' . ($cot->folio ?? $id), 'Compras');
        }
        echo json_encode($result);
    }

    public function rechazar_ajax() {
        if (!$this->puede_editar()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso']);
            return;
        }

        $id = (int) $this->input->post('id');
        $result = $this->CotizacionesModel->rechazar($id);
        if (!empty($result['success'])) {
            $cot = $this->CotizacionesModel->get_cotizacion($id);
            $this->registrar_bitacora('Cotización rechazada: ' . ($cot->folio ?? $id), 'Compras');
        }
        echo json_encode($result);
    }

    public function aprobar_ajax() {
        if (!$this->puede_crear()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para aprobar cotizaciones']);
            return;
        }

        $id = (int) $this->input->post('id');
        $result = $this->CotizacionesModel->aprobar($id, $this->session->userdata('id'));
        if (!empty($result['success'])) {
            $this->registrar_bitacora(
                'Cotización aprobada → OC ' . ($result['orden_folio'] ?? $result['orden_compra_id']),
                'Compras'
            );
        }
        echo json_encode($result);
    }

    public function comparar_ajax() {
        $this->requiere_consulta();

        $grupo = $this->input->post('grupo_folio') ?: $this->input->get('grupo');
        if (!$grupo) {
            echo json_encode(['success' => false, 'message' => 'Grupo requerido']);
            return;
        }

        $comparacion = $this->CotizacionesModel->get_comparacion($grupo);
        if (!$comparacion) {
            echo json_encode(['success' => false, 'message' => 'Grupo no encontrado']);
            return;
        }
        echo json_encode(['success' => true, 'comparacion' => $comparacion]);
    }

    public function subir_archivo_ajax() {
        if (!$this->puede_editar()) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso']);
            return;
        }

        $id = (int) $this->input->post('cotizacion_id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Cotización requerida']);
            return;
        }
        if (empty($_FILES['archivo']['name'])) {
            echo json_encode(['success' => false, 'message' => 'Seleccione un archivo']);
            return;
        }

        $upload_path = './uploads/cotizaciones/' . $id . '/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|jpg|jpeg|png|webp|doc|docx|xls|xlsx',
            'max_size' => 10240,
            'encrypt_name' => true,
        ];
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('archivo')) {
            echo json_encode(['success' => false, 'message' => strip_tags($this->upload->display_errors('', ''))]);
            return;
        }

        $upload_data = $this->upload->data();
        $ruta = 'uploads/cotizaciones/' . $id . '/' . $upload_data['file_name'];

        $ok = $this->CotizacionesModel->actualizar_archivo($id, $upload_data['orig_name'], $ruta);
        if ($ok) {
            $cot = $this->CotizacionesModel->get_cotizacion($id);
            $this->registrar_bitacora('Archivo adjunto en cotización ' . ($cot->folio ?? $id), 'Compras');
            echo json_encode([
                'success' => true,
                'message' => 'Archivo subido',
                'archivo_nombre' => $upload_data['orig_name'],
                'archivo_ruta' => $ruta,
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar archivo']);
        }
    }
}
