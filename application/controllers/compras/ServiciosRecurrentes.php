<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ServiciosRecurrentes extends MY_Controller {

    protected $modulo = 'Compras';

    public function __construct() {
        parent::__construct();
        $this->load->helper('permissions');
        $this->load->model('Compras/ServiciosRecurrentesModel');
        $this->load->model('Compras/ProveedoresModel');
    }

    public function index() {
        if (!tiene_permiso('compras_servicios_recurrentes') && !tiene_permiso('compras_ordenes_consult')) {
            show_error('No tienes permiso para ver servicios recurrentes', 403);
            return;
        }

        $periodo = date('Y-m');
        $this->viewData['pageTitle'] = 'Servicios Recurrentes';
        $this->viewData['headTitle'] = 'Servicios Recurrentes';
        $this->viewData['breadcrumb'] = 'Inicio > Compras > Servicios Recurrentes';
        $this->viewData['mes_actual'] = $periodo;
        $this->viewData['resumen'] = $this->ServiciosRecurrentesModel->get_resumen_mes($periodo);
        $this->viewData['proveedores_servicios'] = $this->ServiciosRecurrentesModel->get_proveedores_con_servicios();
        $this->viewData['proveedores_activos'] = $this->ProveedoresModel->listar_activos();
        $this->viewData['pageView'] = 'compras/servicios_recurrentes/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }

    public function lista_servicios_ajax() {
        if (!tiene_permiso('compras_servicios_recurrentes') && !tiene_permiso('compras_ordenes_consult')) {
            echo json_encode(['success' => false, 'message' => 'Sin permiso']);
            return;
        }

        $filtros = [];
        if ($this->input->post('proveedor_id')) {
            $filtros['proveedor_id'] = (int) $this->input->post('proveedor_id');
        }
        if ($this->input->post('tipo_servicio')) {
            $filtros['tipo_servicio'] = $this->input->post('tipo_servicio');
        }
        $servicios = $this->ServiciosRecurrentesModel->listar_servicios($filtros);
        $puede_gestion = tiene_permiso('compras_servicios_recurrentes');
        $data = [];
        foreach ($servicios as $s) {
            $tipo_badge = $this->badge_tipo($s->tipo_servicio);
            $sid = (int) $s->id;
            $nombre = htmlspecialchars($s->nombre_servicio);
            $servicio_col = '<strong>' . $nombre . '</strong>';
            if ($puede_gestion) {
                $servicio_col = '<a href="#" class="text-decoration-none" onclick="abrirModalEditarServicio(' . $sid . '); return false;" title="Editar servicio">'
                    . '<strong>' . $nombre . '</strong> <i class="fas fa-pen small text-primary"></i></a>';
            }

            $acciones = '<div class="d-flex flex-wrap gap-1">';
            if ($puede_gestion) {
                $acciones .= '<button type="button" class="btn btn-sm btn-warning text-dark" onclick="abrirModalEditarServicio(' . $sid . ')" title="Editar servicio"><i class="fas fa-edit me-1"></i>Editar</button>';
            }
            $acciones .= '<button type="button" class="btn btn-sm btn-outline-info" onclick="verSeguimientoServicio(' . $sid . ')" title="Seguimiento"><i class="fas fa-calendar-alt"></i></button>';
            $acciones .= '<button type="button" class="btn btn-sm btn-outline-success" onclick="abrirModalPagoServicio(null, ' . $sid . ')" title="Registrar pago"><i class="fas fa-dollar-sign"></i></button>';
            $acciones .= '</div>';

            $data[] = [
                $servicio_col,
                $acciones,
                '<span class="badge bg-' . $tipo_badge . '">' . htmlspecialchars($s->tipo_servicio) . '</span>',
                htmlspecialchars($s->proveedor_nombre ?: '—'),
                htmlspecialchars($s->frecuencia),
                'Día ' . (int) $s->dia_vencimiento,
                '$' . number_format((float) $s->monto_estimado, 2),
                '<span class="badge bg-' . ($s->activo ? 'success' : 'secondary') . '">' . ($s->activo ? 'Activo' : 'Inactivo') . '</span>',
            ];
        }

        echo json_encode([
            'draw' => (int) ($this->input->post('draw') ?? 1),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data,
        ]);
    }

    public function lista_pagos_ajax() {
        if (!tiene_permiso('compras_servicios_recurrentes') && !tiene_permiso('compras_ordenes_consult')) {
            echo json_encode(['success' => false, 'message' => 'Sin permiso']);
            return;
        }

        $periodo = $this->input->post('periodo') ?: date('Y-m');
        $proveedor_id = $this->input->post('proveedor_id') ?: null;
        $pagos = $this->ServiciosRecurrentesModel->listar_pagos($periodo, $proveedor_id);
        $data = [];
        foreach ($pagos as $p) {
            $estatus_badge = ['Pendiente' => 'warning', 'Pagado' => 'success', 'Vencido' => 'danger', 'Cancelado' => 'secondary'];
            $eb = $estatus_badge[$p->estatus] ?? 'secondary';
            $row = [];
            $row[] = htmlspecialchars($p->nombre_servicio);
            $row[] = htmlspecialchars($p->proveedor_nombre ?: '—');
            $row[] = date('d/m/Y', strtotime($p->fecha_vencimiento));
            $row[] = '$' . number_format((float) $p->monto, 2);
            $row[] = $p->fecha_pago ? date('d/m/Y', strtotime($p->fecha_pago)) : '—';
            $row[] = '<span class="badge bg-' . $eb . '">' . $p->estatus . '</span>';

            $acciones = '';
            if (!empty($p->comprobante_ruta)) {
                $acciones .= '<a class="btn btn-sm btn-outline-primary me-1" href="' . base_url('compras/ServiciosRecurrentes/descargar_comprobante/' . (int) $p->id) . '" title="Ver comprobante" target="_blank"><i class="fas fa-file-alt"></i></a>';
            }
            if (in_array($p->estatus, ['Pendiente', 'Vencido'], true) && tiene_permiso('compras_pagos')) {
                $acciones .= '<button type="button" class="btn btn-sm btn-success me-1" onclick="abrirModalPagoServicio(' . (int) $p->id . ')" title="Registrar pago"><i class="fas fa-dollar-sign"></i></button>';
            } elseif ($p->estatus === 'Pagado') {
                $acciones .= '<button type="button" class="btn btn-sm btn-outline-success me-1" onclick="abrirModalPagoServicio(' . (int) $p->id . ', null, null, true)" title="Ver pago / comprobante"><i class="fas fa-check-circle"></i></button>';
                if (empty($p->comprobante_ruta) && tiene_permiso('compras_pagos')) {
                    $acciones .= '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="abrirModalPagoServicio(' . (int) $p->id . ', null, null, true)" title="Subir comprobante"><i class="fas fa-paperclip"></i></button>';
                }
            }
            $row[] = $acciones ?: '—';
            $data[] = $row;
        }

        echo json_encode([
            'draw' => (int) ($this->input->post('draw') ?? 1),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data,
        ]);
    }

    public function pagos_proveedor_ajax() {
        if (!tiene_permiso('compras_servicios_recurrentes') && !tiene_permiso('proveedores_consult')) {
            echo json_encode(['success' => false, 'message' => 'Sin permiso']);
            return;
        }

        $proveedor_id = (int) $this->input->post('proveedor_id');
        if (!$proveedor_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor requerido']);
            return;
        }

        $servicios = $this->ServiciosRecurrentesModel->listar_servicios(['proveedor_id' => $proveedor_id]);
        $pagos = $this->ServiciosRecurrentesModel->listar_pagos(date('Y-m'), $proveedor_id);
        echo json_encode(['success' => true, 'servicios' => $servicios, 'pagos_mes' => $pagos]);
    }

    public function crear_servicio_ajax() {
        $this->requiere_permiso('compras_servicios_recurrentes', 'No tienes permiso para crear servicios recurrentes');

        $nombre = trim((string) $this->input->post('nombre_servicio'));
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Nombre del servicio requerido']);
            return;
        }

        $proveedor_id = (int) $this->input->post('proveedor_id');
        if (!$proveedor_id) {
            echo json_encode(['success' => false, 'message' => 'Seleccione el proveedor del servicio']);
            return;
        }

        $data = [
            'proveedor_id' => $proveedor_id,
            'nombre_servicio' => $nombre,
            'descripcion' => $this->input->post('descripcion'),
            'tipo_servicio' => $this->input->post('tipo_servicio') ?: 'Otros',
            'frecuencia' => $this->input->post('frecuencia') ?: 'Mensual',
            'dia_vencimiento' => (int) ($this->input->post('dia_vencimiento') ?: 1),
            'monto_estimado' => (float) ($this->input->post('monto_estimado') ?: 0),
            'fecha_inicio' => $this->input->post('fecha_inicio') ?: date('Y-m-01'),
            'notas' => $this->input->post('notas'),
            'activo' => 1,
            'usuario_creacion' => $this->session->userdata('id'),
        ];

        $result = $this->ServiciosRecurrentesModel->crear_servicio($data);
        if (!empty($result['success'])) {
            $this->registrar_bitacora('Servicio recurrente creado: ' . $nombre, 'Compras');
        }
        echo json_encode($result);
    }

    public function get_servicio_ajax() {
        if (!tiene_permiso('compras_servicios_recurrentes')) {
            echo json_encode(['success' => false, 'message' => 'Sin permiso']);
            return;
        }

        $servicio_id = (int) $this->input->post('servicio_id');
        $servicio = $this->ServiciosRecurrentesModel->get_servicio($servicio_id);
        if (!$servicio) {
            echo json_encode(['success' => false, 'message' => 'Servicio no encontrado']);
            return;
        }

        echo json_encode(['success' => true, 'servicio' => $servicio]);
    }

    public function actualizar_servicio_ajax() {
        $this->requiere_permiso('compras_servicios_recurrentes', 'No tienes permiso para editar servicios recurrentes');

        $servicio_id = (int) $this->input->post('servicio_id');
        if (!$servicio_id) {
            echo json_encode(['success' => false, 'message' => 'Servicio requerido']);
            return;
        }

        $nombre = trim((string) $this->input->post('nombre_servicio'));
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'Nombre del servicio requerido']);
            return;
        }

        $dia = (int) ($this->input->post('dia_vencimiento') ?: 1);
        if ($dia < 1 || $dia > 31) {
            echo json_encode(['success' => false, 'message' => 'Día de pago inválido (1-31)']);
            return;
        }

        $monto = (float) ($this->input->post('monto_estimado') ?: 0);
        if ($monto <= 0) {
            echo json_encode(['success' => false, 'message' => 'El monto mensual debe ser mayor a cero']);
            return;
        }

        $proveedor_id = (int) $this->input->post('proveedor_id');
        if (!$proveedor_id) {
            echo json_encode(['success' => false, 'message' => 'Seleccione el proveedor del servicio']);
            return;
        }

        $data = [
            'proveedor_id' => $proveedor_id,
            'nombre_servicio' => $nombre,
            'descripcion' => $this->input->post('descripcion'),
            'tipo_servicio' => $this->input->post('tipo_servicio') ?: 'Otros',
            'frecuencia' => $this->input->post('frecuencia') ?: 'Mensual',
            'dia_vencimiento' => $dia,
            'monto_estimado' => $monto,
            'notas' => $this->input->post('notas'),
        ];

        $result = $this->ServiciosRecurrentesModel->actualizar_servicio($servicio_id, $data);
        if (!empty($result['success'])) {
            $this->registrar_bitacora('Servicio recurrente actualizado: ' . $nombre, 'Compras');
        }
        echo json_encode($result);
    }

    public function get_pago_ajax() {
        if (!tiene_permiso('compras_servicios_recurrentes') && !tiene_permiso('compras_ordenes_consult')) {
            echo json_encode(['success' => false, 'message' => 'Sin permiso']);
            return;
        }

        $pago_id = (int) $this->input->post('pago_id');
        $pago = $this->ServiciosRecurrentesModel->get_pago($pago_id);
        if (!$pago) {
            echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
            return;
        }

        echo json_encode([
            'success' => true,
            'pago' => [
                'id' => (int) $pago->id,
                'servicio_id' => (int) $pago->servicio_recurrente_id,
                'nombre_servicio' => $pago->nombre_servicio,
                'proveedor_nombre' => $pago->proveedor_nombre,
                'periodo' => $pago->periodo,
                'fecha_vencimiento' => $pago->fecha_vencimiento,
                'fecha_pago' => $pago->fecha_pago,
                'monto' => (float) $pago->monto,
                'referencia' => $pago->referencia,
                'notas' => $pago->notas,
                'estatus' => $pago->estatus,
                'comprobante_ruta' => $pago->comprobante_ruta,
                'comprobante_nombre' => $pago->comprobante_nombre,
                'comprobante_url' => !empty($pago->comprobante_ruta)
                    ? base_url('compras/ServiciosRecurrentes/descargar_comprobante/' . (int) $pago->id)
                    : null,
            ],
        ]);
    }

    public function registrar_pago_ajax() {
        $this->requiere_permiso('compras_pagos', 'No tienes permiso para registrar pagos');

        $pago_id = (int) $this->input->post('pago_id');
        if (!$pago_id) {
            $servicio_id = (int) $this->input->post('servicio_id');
            $periodo = $this->input->post('periodo') ?: date('Y-m');
            if ($servicio_id) {
                $pagos = $this->ServiciosRecurrentesModel->listar_pagos($periodo, null, $servicio_id);
                $pago_id = !empty($pagos[0]) ? (int) $pagos[0]->id : 0;
            }
        }

        if (!$pago_id) {
            echo json_encode(['success' => false, 'message' => 'Pago no encontrado para el periodo']);
            return;
        }

        $pago_actual = $this->ServiciosRecurrentesModel->get_pago($pago_id);
        if (!$pago_actual) {
            echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
            return;
        }

        $solo_comprobante = $this->input->post('solo_comprobante') === '1';
        $result = ['success' => true, 'message' => 'Comprobante actualizado'];

        if (!$solo_comprobante) {
            if ($pago_actual->estatus === 'Pagado') {
                echo json_encode(['success' => false, 'message' => 'Este pago ya fue registrado']);
                return;
            }

            $result = $this->ServiciosRecurrentesModel->registrar_pago_servicio($pago_id, [
                'fecha_pago' => $this->input->post('fecha_pago') ?: date('Y-m-d'),
                'monto' => $this->input->post('monto'),
                'referencia' => $this->input->post('referencia'),
                'notas' => $this->input->post('notas'),
                'usuario_registro' => $this->session->userdata('id'),
            ]);

            if (empty($result['success'])) {
                echo json_encode($result);
                return;
            }
        } elseif ($pago_actual->estatus !== 'Pagado') {
            echo json_encode(['success' => false, 'message' => 'Primero registre el pago antes de adjuntar comprobante']);
            return;
        }

        $upload_result = $this->_procesar_comprobante_pago($pago_id);
        if (!empty($_FILES['comprobante']['name']) && empty($upload_result['success'])) {
            echo json_encode($upload_result);
            return;
        }

        if (!empty($result['success'])) {
            $pago = $this->ServiciosRecurrentesModel->get_pago($pago_id);
            $this->registrar_bitacora(
                ($solo_comprobante ? 'Comprobante servicio recurrente: ' : 'Pago servicio recurrente: ')
                . ($pago->nombre_servicio ?? '') . ' — $' . number_format((float) ($pago->monto ?? 0), 2),
                'Compras'
            );
        }

        echo json_encode([
            'success' => true,
            'message' => $solo_comprobante ? 'Comprobante guardado' : ($result['message'] ?? 'Pago registrado'),
            'comprobante' => $upload_result['comprobante'] ?? null,
        ]);
    }

    public function eliminar_comprobante_ajax() {
        $this->requiere_permiso('compras_pagos', 'No tienes permiso para eliminar comprobantes');

        $pago_id = (int) $this->input->post('pago_id');
        $result = $this->ServiciosRecurrentesModel->eliminar_comprobante_pago($pago_id);
        if (!empty($result['success'])) {
            $this->registrar_bitacora('Comprobante eliminado — pago servicio #' . $pago_id, 'Compras');
        }
        echo json_encode($result);
    }

    public function descargar_comprobante($pago_id = 0) {
        if (!tiene_permiso('compras_servicios_recurrentes') && !tiene_permiso('compras_ordenes_consult')) {
            show_error('Sin permiso', 403);
            return;
        }

        $pago = $this->ServiciosRecurrentesModel->get_pago((int) $pago_id);
        if (!$pago || empty($pago->comprobante_ruta)) {
            show_error('Comprobante no encontrado', 404);
            return;
        }

        $ruta = FCPATH . ltrim($pago->comprobante_ruta, '/');
        if (!is_file($ruta)) {
            show_error('Archivo no encontrado en el servidor', 404);
            return;
        }

        $mime = $pago->comprobante_mime ?: 'application/octet-stream';
        $nombre = $pago->comprobante_nombre ?: basename($ruta);
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . rawurlencode($nombre) . '"');
        header('Content-Length: ' . filesize($ruta));
        readfile($ruta);
        exit;
    }

    private function _procesar_comprobante_pago($pago_id) {
        if (empty($_FILES['comprobante']['name'])) {
            return ['success' => true];
        }

        $upload_path = './uploads/servicios_recurrentes/' . (int) $pago_id . '/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config = [
            'upload_path' => $upload_path,
            'allowed_types' => 'pdf|jpg|jpeg|png|webp',
            'max_size' => 10240,
            'encrypt_name' => true,
        ];
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('comprobante')) {
            return ['success' => false, 'message' => strip_tags($this->upload->display_errors('', ''))];
        }

        $upload_data = $this->upload->data();
        $pago = $this->ServiciosRecurrentesModel->get_pago($pago_id);
        if ($pago && !empty($pago->comprobante_ruta)) {
            $viejo = FCPATH . ltrim($pago->comprobante_ruta, '/');
            if (is_file($viejo)) {
                @unlink($viejo);
            }
        }

        $ruta = 'uploads/servicios_recurrentes/' . (int) $pago_id . '/' . $upload_data['file_name'];
        $result = $this->ServiciosRecurrentesModel->guardar_comprobante_pago($pago_id, [
            'comprobante_ruta' => $ruta,
            'comprobante_nombre' => $upload_data['orig_name'],
            'comprobante_mime' => $upload_data['file_type'],
        ]);

        if (!empty($result['success'])) {
            $result['comprobante'] = [
                'nombre' => $upload_data['orig_name'],
                'url' => base_url('compras/ServiciosRecurrentes/descargar_comprobante/' . (int) $pago_id),
            ];
        }

        return $result;
    }

    public function resumen_ajax() {
        $periodo = $this->input->post('periodo') ?: date('Y-m');
        echo json_encode([
            'success' => true,
            'resumen' => $this->ServiciosRecurrentesModel->get_resumen_mes($periodo),
        ]);
    }

    /**
     * Matriz de seguimiento mensual (últimos 12 meses por servicio).
     */
    public function seguimiento_mensual_ajax() {
        if (!tiene_permiso('compras_servicios_recurrentes') && !tiene_permiso('compras_ordenes_consult')) {
            echo json_encode(['success' => false, 'message' => 'Sin permiso']);
            return;
        }

        $meses = min(24, max(3, (int) ($this->input->post('meses') ?: 12)));
        $proveedor_id = $this->input->post('proveedor_id') ?: null;
        $data = $this->ServiciosRecurrentesModel->get_seguimiento_mensual($meses, $proveedor_id);
        echo json_encode(['success' => true] + $data);
    }

    public function historial_servicio_ajax() {
        if (!tiene_permiso('compras_servicios_recurrentes') && !tiene_permiso('compras_ordenes_consult')) {
            echo json_encode(['success' => false, 'message' => 'Sin permiso']);
            return;
        }

        $servicio_id = (int) $this->input->post('servicio_id');
        $historial = $this->ServiciosRecurrentesModel->get_historial_servicio($servicio_id, 12);
        if (!$historial) {
            echo json_encode(['success' => false, 'message' => 'Servicio no encontrado']);
            return;
        }
        echo json_encode(['success' => true, 'historial' => $historial]);
    }

    public function generar_periodos_ajax() {
        $this->requiere_permiso('compras_servicios_recurrentes', 'No tienes permiso');

        $servicio_id = (int) $this->input->post('servicio_id');
        $meses = min(24, max(6, (int) ($this->input->post('meses') ?: 12)));

        if ($servicio_id) {
            $this->ServiciosRecurrentesModel->generar_pagos_futuros($servicio_id, $meses);
            $msg = 'Periodos generados para el servicio';
        } else {
            $n = $this->ServiciosRecurrentesModel->generar_periodos_todos($meses);
            $msg = "Periodos generados para $n servicios";
        }

        $this->registrar_bitacora($msg, 'Compras');
        echo json_encode(['success' => true, 'message' => $msg]);
    }

    private function badge_tipo($tipo) {
        $map = [
            'Telecomunicaciones' => 'primary',
            'Suscripciones' => 'info',
            'Soporte Técnico' => 'dark',
            'Recolección de Basura' => 'secondary',
            'Servicios Públicos' => 'primary',
            'Renta' => 'success',
            'Seguros' => 'warning',
            'Mantenimiento' => 'secondary',
        ];
        return $map[$tipo] ?? 'secondary';
    }
}
