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
        $this->viewData['proveedores_servicios'] = $this->ProveedoresModel->listar_por_tipo('Servicios');
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
        $servicios = $this->ServiciosRecurrentesModel->listar_servicios($filtros);
        $data = [];
        foreach ($servicios as $s) {
            $tipo_badge = $this->badge_tipo($s->tipo_servicio);
            $row = [];
            $row[] = '<strong>' . htmlspecialchars($s->nombre_servicio) . '</strong>';
            $row[] = '<span class="badge bg-' . $tipo_badge . '">' . htmlspecialchars($s->tipo_servicio) . '</span>';
            $row[] = htmlspecialchars($s->proveedor_nombre ?: '—');
            $row[] = htmlspecialchars($s->frecuencia);
            $row[] = 'Día ' . (int) $s->dia_vencimiento;
            $row[] = '$' . number_format((float) $s->monto_estimado, 2);
            $row[] = '<span class="badge bg-' . ($s->activo ? 'success' : 'secondary') . '">' . ($s->activo ? 'Activo' : 'Inactivo') . '</span>';
            $acciones = '<button type="button" class="btn btn-sm btn-info me-1" onclick="verSeguimientoServicio(' . (int) $s->id . ')" title="Seguimiento mensual"><i class="fas fa-calendar-alt"></i></button>';
            $acciones .= '<button type="button" class="btn btn-sm btn-success" onclick="abrirModalPagoServicio(null, ' . (int) $s->id . ')" title="Registrar pago del mes"><i class="fas fa-dollar-sign"></i></button>';
            $row[] = $acciones;
            $data[] = $row;
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
            if (in_array($p->estatus, ['Pendiente', 'Vencido'], true) && tiene_permiso('compras_pagos')) {
                $acciones = '<button type="button" class="btn btn-sm btn-success" onclick="abrirModalPagoServicio(' . (int) $p->id . ')" title="Marcar pagado"><i class="fas fa-check"></i></button>';
            } elseif ($p->estatus === 'Pagado') {
                $acciones = '<span class="text-success small"><i class="fas fa-check-circle"></i></span>';
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

        $data = [
            'proveedor_id' => $this->input->post('proveedor_id') ?: null,
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

        $result = $this->ServiciosRecurrentesModel->registrar_pago_servicio($pago_id, [
            'fecha_pago' => $this->input->post('fecha_pago') ?: date('Y-m-d'),
            'monto' => $this->input->post('monto'),
            'referencia' => $this->input->post('referencia'),
            'notas' => $this->input->post('notas'),
            'usuario_registro' => $this->session->userdata('id'),
        ]);

        if (!empty($result['success'])) {
            $pago = $this->ServiciosRecurrentesModel->get_pago($pago_id);
            $this->registrar_bitacora(
                'Pago servicio recurrente: ' . ($pago->nombre_servicio ?? '') . ' — $' . number_format((float)$this->input->post('monto'), 2),
                'Compras'
            );
        }
        echo json_encode($result);
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
