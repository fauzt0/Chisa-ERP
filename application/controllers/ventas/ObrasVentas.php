<?php
/**
 * ObrasVentas Controller - Vista de Obras desde CRM de Ventas
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class ObrasVentas extends MY_Controller {
    
    protected $modulo = 'Obras';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Obras/ObrasModel');
        $this->load->model('Ventas/VentasModel');
        $this->load->model('Ventas/CarteraModel');
        $this->load->model('Obras/ConceptosObraModel');
        $this->load->model('Obras/PresupuestosObraModel');
        $this->load->model('Obras/ApuModel');
        $this->load->model('Obras/GeneradoresModel');
        $this->load->model('Obras/RevisionCuantificacionModel');
    }
    
    /**
     * Vista principal - Lista de obras
     */
    public function index() {
        $data['pageTitle'] = 'Obras';
        $data['headTitle'] = 'Gestión de Obras';
        $data['breadcrumb'] = 'Inicio > CRM Ventas > Obras';
        
        // Obtener estadísticas de obras
        $stats = $this->get_estadisticas_obras();
        $data['response'] = [
            'stats' => $stats,
            'cartera' => $this->CarteraModel->get_pendientes(12),
            'cartera_resumen' => $this->CarteraModel->get_resumen(),
        ];
        
        $data['validate'] = '';
        $data['pageView'] = 'ventas/obras/main';
        
        $this->load->view('layouts/general_template', $data);
    }
    
    /**
     * Lista de obras para DataTables (AJAX)
     */
    public function lista_ajax() {
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search')['value'] ?? '';
        $order_column = $this->input->post('order')[0]['column'] ?? 4;
        $order_dir = $this->input->post('order')[0]['dir'] ?? 'desc';
        
        // Columnas para ordenar
        $columns = ['folio', 'nombre', 'cliente', 'estatus', 'fecha_creacion', 'total'];
        $order_by = $columns[$order_column] ?? 'fecha_creacion';
        
        // Obtener obras
        $this->db->select('
            o.id,
            o.folio,
            o.nombre,
            c.razon_social as cliente,
            o.estatus,
            o.fecha_creacion,
            o.total,
            o.subtotal,
            o.iva_monto,
            o.saldo_pendiente,
            o.estatus_pago
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->where('o.activo', 1);
        
        // Búsqueda
        if(!empty($search)) {
            $this->db->group_start();
            $this->db->like('o.folio', $search);
            $this->db->or_like('o.nombre', $search);
            $this->db->or_like('c.razon_social', $search);
            $this->db->group_end();
        }
        
        // Total de registros
        $total_records = $this->db->count_all_results('', FALSE);
        
        // Ordenar y paginar
        $this->db->order_by($order_by, $order_dir);
        $this->db->limit($length, $start);
        
        $obras = $this->db->get()->result();
        
        // Formatear datos para DataTables
        $data = [];
        foreach($obras as $obra) {
            // Badge de estatus
            $badge_class = $this->get_badge_class($obra->estatus);
            $estatus_badge = '<span class="badge bg-' . $badge_class . '">' . $obra->estatus . '</span>';
            
            // Botones de acción
            $acciones = '
                <a href="' . base_url('ventas/ObrasVentas/detalle/' . $obra->id) . '" 
                   class="btn btn-sm btn-primary" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </a>
            ';
            
            $saldo = (float) ($obra->saldo_pendiente ?? 0);
            $pago = $obra->estatus_pago ?? 'Pendiente';
            $pago_badge = 'secondary';
            if ($saldo > 0.009) {
                $pago_badge = $pago === 'Parcialmente Pagado' || $pago === 'Parcial' ? 'warning' : 'danger';
            } elseif ($pago === 'Pagado') {
                $pago_badge = 'success';
            }

            $data[] = [
                $obra->folio,
                $obra->nombre,
                $obra->cliente ?: 'Sin cliente',
                $estatus_badge,
                date('d/m/Y', strtotime($obra->fecha_creacion)),
                '$' . number_format($obra->total, 2),
                '<span class="' . ($saldo > 0 ? 'text-danger fw-bold' : 'text-success') . '">$' . number_format($saldo, 2) . '</span>',
                '<span class="badge bg-' . $pago_badge . '">' . htmlspecialchars($pago, ENT_QUOTES, 'UTF-8') . '</span>',
                $acciones
            ];
        }
        
        echo json_encode([
            'draw' => intval($draw),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_records,
            'data' => $data
        ]);
    }
    
    /**
     * Vista de detalle de una obra
     */
    public function detalle($obra_id) {
        $obra = $this->ObrasModel->get_obra_detalle($obra_id);
        
        if(!$obra) {
            show_404();
            return;
        }

        if (isset($obra->activo) && (int) $obra->activo === 0) {
            show_error('Esta obra fue eliminada y ya no está disponible en CRM Ventas.', 404, 'Obra eliminada');
            return;
        }

        $obra->pagos = $this->ObrasModel->get_pagos_obra($obra_id);
        
        $data['pageTitle'] = 'Detalle de Obra - ' . $obra->folio;
        $data['headTitle'] = 'Detalle de Obra';
        $data['breadcrumb'] = 'Inicio > CRM Ventas > Obras > Detalle';
        
        $data['response'] = ['obra' => $obra];
        $data['validate'] = '';
        $data['pageView'] = 'ventas/obras/detalle';
        
        $this->load->view('layouts/general_template', $data);
    }
    
    /**
     * Obtiene estadísticas de obras
     */
    private function get_estadisticas_obras() {
        $stats = [];
        
        $this->db->where('activo', 1);
        $stats['total'] = $this->db->count_all_results('obras');
        
        $this->db->where('activo', 1);
        $this->db->where('estatus', 'En Cotización');
        $stats['en_cotizacion'] = $this->db->count_all_results('obras');
        
        $this->db->where('activo', 1);
        $this->db->where('estatus', 'Aprobada');
        $stats['aprobadas'] = $this->db->count_all_results('obras');
        
        $this->db->where('activo', 1);
        $this->db->where('estatus', 'En Ejecución');
        $stats['en_ejecucion'] = $this->db->count_all_results('obras');
        
        $this->db->where('activo', 1);
        $this->db->where('estatus', 'Completada');
        $stats['completadas'] = $this->db->count_all_results('obras');
        
        // Porcentajes para progress bars
        $total = $stats['total'] > 0 ? $stats['total'] : 1;
        
        $stats['porcentaje_cotizacion'] = round(($stats['en_cotizacion'] / $total) * 100);
        $stats['porcentaje_aprobadas'] = round(($stats['aprobadas'] / $total) * 100);
        $stats['porcentaje_ejecucion'] = round(($stats['en_ejecucion'] / $total) * 100);
        $stats['porcentaje_completadas'] = round(($stats['completadas'] / $total) * 100);
        
        return $stats;
    }
    
    /**
     * Obtiene la clase de badge según el estatus
     */
    private function get_badge_class($estatus) {
        $classes = [
            'Planificación' => 'secondary',
            'En Cotización' => 'warning',
            'Aprobada' => 'success',
            'En Ejecución' => 'primary',
            'Pausada' => 'danger',
            'Completada' => 'info',
            'Cancelada' => 'dark'
        ];
        
        return $classes[$estatus] ?? 'secondary';
    }
    
    /**
     * Genera una factura simulada para una obra (AJAX)
     */
    public function generar_factura_ajax() {
        $obra_id = $this->input->post('obra_id');
        
        if(!$obra_id) {
            echo json_encode(['success' => false, 'message' => 'ID de obra no proporcionado']);
            return;
        }
        
        // Verificar que la obra existe
        $this->db->select('o.*, c.rfc, c.razon_social');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->where('o.id', $obra_id);
        $obra = $this->db->get()->row();
        
        if(!$obra) {
            echo json_encode(['success' => false, 'message' => 'Obra no encontrada']);
            return;
        }

        if (isset($obra->activo) && (int) $obra->activo === 0) {
            echo json_encode(['success' => false, 'message' => 'Obra eliminada']);
            return;
        }
        
        // Verificar que no tenga factura ya
        $this->db->where('obra_id', $obra_id);
        $factura_existente = $this->db->get('facturas_obras')->row();
        
        if($factura_existente) {
            echo json_encode(['success' => false, 'message' => 'Esta obra ya tiene una factura generada']);
            return;
        }
        
        // Generar folio de factura
        $this->db->select_max('id');
        $last_factura = $this->db->get('facturas_obras')->row();
        $next_id = ($last_factura->id ?? 0) + 1;
        $folio = 'F-OB-' . str_pad($next_id, 5, '0', STR_PAD_LEFT);
        
        // Obtener datos fiscales del formulario (editables por el usuario)
        $rfc_emisor = $this->input->post('rfc_emisor') ?: 'XAXX010101000';
        $razon_social_emisor = $this->input->post('razon_social_emisor') ?: 'Mi Empresa S.A. de C.V.';
        $direccion_emisor = $this->input->post('direccion_emisor') ?: '';
        
        $rfc_receptor = $this->input->post('rfc_receptor') ?: ($obra->rfc ?? 'XAXX010101000');
        $razon_social_receptor = $this->input->post('razon_social_receptor') ?: ($obra->razon_social ?? 'Público General');
        $direccion_receptor = $this->input->post('direccion_receptor') ?: ($obra->direccion ?? '');
        
        $notas = $this->input->post('notas') ?: '';
        
        // Crear factura con datos personalizados
        $data_factura = [
            'obra_id' => $obra_id,
            'folio' => $folio,
            'fecha_emision' => date('Y-m-d H:i:s'),
            'subtotal' => $obra->subtotal ?? 0,
            'iva' => $obra->iva_monto ?? 0,
            'total' => $obra->total,
            'rfc_emisor' => $rfc_emisor,
            'razon_social_emisor' => $razon_social_emisor,
            'direccion_emisor' => $direccion_emisor,
            'rfc_receptor' => $rfc_receptor,
            'razon_social_receptor' => $razon_social_receptor,
            'direccion_receptor' => $direccion_receptor,
            'notas' => $notas,
            'creado_por' => $this->session->userdata('id') ?? 1
        ];
        
        $this->db->insert('facturas_obras', $data_factura);
        
        echo json_encode(['success' => true, 'message' => 'Factura generada correctamente', 'folio' => $folio]);
    }
    
    /**
     * Vista de impresión de factura
     */
    public function imprimir_factura($obra_id) {
        // Obtener obra completa
        $this->db->select('
            o.*,
            c.razon_social as cliente,
            c.nombre_comercial,
            c.rfc,
            c.telefono,
            c.email
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->where('o.id', $obra_id);
        $obra = $this->db->get()->row();
        
        if(!$obra) {
            echo 'Obra no encontrada';
            return;
        }
        
        // Obtener productos
        $this->db->select('
            op.*,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo
        ');
        $this->db->from('obras_productos op');
        $this->db->join('productos p', 'p.id = op.producto_id');
        $this->db->where('op.obra_id', $obra_id);
        $obra->productos = $this->db->get()->result();
        
        // Obtener factura
        $this->db->where('obra_id', $obra_id);
        $factura = $this->db->get('facturas_obras')->row();
        
        if(!$factura) {
            echo 'No hay factura generada para esta obra';
            return;
        }
        
        $data['obra'] = $obra;
        $data['factura'] = $factura;
        $this->load->view('ventas/obras/factura', $data);
    }
    
    /**
     * Obtiene HTML del recibo para mostrar en modal (AJAX)
     */
    public function get_recibo_ajax() {
        $pago_id = $this->input->post('pago_id');
        
        if(!$pago_id) {
            echo json_encode(['success' => false, 'message' => 'ID de pago no proporcionado']);
            return;
        }
        
        // Obtener pago
        $this->db->where('id', $pago_id);
        $pago = $this->db->get('obras_pagos')->row();
        
        if(!$pago) {
            echo json_encode(['success' => false, 'message' => 'Pago no encontrado']);
            return;
        }
        
        // Obtener obra
        $this->db->select('
            o.*,
            c.razon_social as cliente,
            c.nombre_comercial,
            c.rfc,
            c.telefono,
            c.email
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->where('o.id', $pago->obra_id);
        $obra = $this->db->get()->row();
        
        if(!$obra) {
            echo json_encode(['success' => false, 'message' => 'Obra no encontrada']);
            return;
        }
        
        $data['pago'] = $pago;
        $data['obra'] = $obra;
        
        $html = $this->load->view('ventas/obras/recibo', $data, TRUE);
        echo json_encode(['success' => true, 'html' => $html]);
    }

    /**
     * Documento PDF profesional de la obra
     */
    public function exportar_pdf($obra_id) {
        redirect('obras/Obras/exportar_pdf/' . $obra_id);
    }

    public function get_ordenes_venta_disponibles_ajax() {
        $obra_id = $this->input->get('obra_id');
        $obra = $this->ObrasModel->get_obra_detalle($obra_id);
        if (!$obra) {
            echo json_encode(['success' => false, 'message' => 'Obra no encontrada']);
            return;
        }
        $ordenes = $this->ObrasModel->get_ordenes_venta_disponibles($obra->cliente_id, $obra_id);
        echo json_encode(['success' => true, 'ordenes' => $ordenes]);
    }

    public function vincular_orden_venta_ajax() {
        $obra_id = $this->input->post('obra_id');
        $orden_venta_id = $this->input->post('orden_venta_id');
        if (!$obra_id || !$orden_venta_id) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        echo json_encode($this->ObrasModel->vincular_orden_venta($obra_id, $orden_venta_id));
    }

    public function generar_orden_venta_ajax() {
        $obra_id = $this->input->post('obra_id');
        if (!$obra_id) {
            echo json_encode(['success' => false, 'message' => 'ID de obra no proporcionado']);
            return;
        }
        $usuario_id = $this->session->userdata('id') ?: 1;
        echo json_encode($this->ObrasModel->generar_orden_venta_desde_obra($obra_id, $usuario_id));
    }

    public function confirmar_orden_venta_ajax() {
        $obra_id = $this->input->post('obra_id');
        $obra = $this->ObrasModel->get_obra_detalle($obra_id);
        if (!$obra || empty($obra->orden_venta_id)) {
            echo json_encode(['success' => false, 'message' => 'La obra no tiene orden de venta vinculada']);
            return;
        }
        $this->VentasModel->confirmar_orden($obra->orden_venta_id);
        echo json_encode([
            'success' => true,
            'message' => 'Orden ' . $obra->orden_venta_folio . ' confirmada y enviada a producción'
        ]);
    }

    /* ══════════════════════════════════════════════════════════════════
       FASE 5 · OBRAS Y PRESUPUESTOS (crear/consultar obra + cotizar)
       ══════════════════════════════════════════════════════════════════ */

    /**
     * Formulario para crear una obra (carátula V8) desde Ventas.
     */
    public function crear() {
        $this->load->model('Ventas/SucursalesModel');
        $data['pageTitle'] = 'Nueva Obra';
        $data['headTitle'] = 'Nueva Obra';
        $data['breadcrumb'] = 'Inicio > CRM Ventas > Obras > Nueva';
        $data['clientes'] = $this->db->where('estatus', 'Activo')->order_by('razon_social', 'ASC')->get('clientes')->result();
        $data['sucursales'] = $this->SucursalesModel->listar_activas();
        $data['estatus_validos'] = ObrasModel::ESTATUS_OBRA_VALIDOS;
        $data['validate'] = '';
        $data['pageView'] = 'ventas/obras/crear';
        $this->load->view('layouts/general_template', $data);
    }

    /**
     * Crea una obra desde Ventas (AJAX). Folio OB-##### automático.
     */
    public function guardar_obra_ajax() {
        $nombre = trim((string) $this->input->post('nombre'));
        if ($nombre === '') {
            echo json_encode(['success' => false, 'message' => 'El nombre de la obra es obligatorio']);
            return;
        }
        $cliente_id = (int) $this->input->post('cliente_id');
        if ($cliente_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un cliente']);
            return;
        }
        $direccion = trim((string) $this->input->post('direccion'));
        if ($direccion === '') {
            echo json_encode(['success' => false, 'message' => 'La dirección es obligatoria']);
            return;
        }

        $estatus = (string) ($this->input->post('estatus') ?: 'Planificación');
        if (!$this->ObrasModel->estatus_obra_valido($estatus)) {
            echo json_encode(['success' => false, 'message' => 'Estatus no válido']);
            return;
        }

        $sucursal_id = $this->input->post('sucursal_id');
        $data = [
            'nombre' => $nombre,
            'cliente_id' => $cliente_id,
            'direccion' => $direccion,
            'ciudad' => $this->input->post('ciudad'),
            'estado' => $this->input->post('estado'),
            'codigo_postal' => $this->input->post('codigo_postal'),
            'coordenadas_gps' => $this->input->post('coordenadas_gps'),
            'area_total' => $this->input->post('area_total') ?: null,
            'tipo_superficie' => $this->input->post('tipo_superficie'),
            'condiciones_ambientales' => $this->input->post('condiciones_ambientales'),
            'especificaciones_tecnicas' => $this->input->post('especificaciones_tecnicas'),
            'estatus' => $estatus,
            'fecha_inicio_estimada' => $this->input->post('fecha_inicio_estimada') ?: null,
            'fecha_fin_estimada' => $this->input->post('fecha_fin_estimada') ?: null,
            'descripcion' => $this->input->post('descripcion'),
            'notas_internas' => $this->input->post('notas_internas'),
            'anticipo_porcentaje' => $this->input->post('anticipo_porcentaje') ?: 0,
            'condiciones_pago' => $this->input->post('condiciones_pago'),
            'descuento_porcentaje' => $this->input->post('descuento_porcentaje') ?: 0,
            'iva_porcentaje' => $this->input->post('iva_porcentaje') ?: 16,
            'costo_estimado' => $this->input->post('costo_estimado') ?: 0,
            'creado_por' => $this->session->userdata('id') ?: 1,
        ];
        if (!empty($sucursal_id)) {
            $data['sucursal_id'] = (int) $sucursal_id;
        }

        $obra_id = $this->ObrasModel->crear_obra($data);
        if ($obra_id) {
            $this->ObrasModel->calcular_totales_obra($obra_id);
            echo json_encode(['success' => true, 'message' => 'Obra creada correctamente', 'obra_id' => $obra_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear la obra']);
        }
    }

    /* ─── Presupuestos ─────────────────────────────────────────────── */

    public function crear_presupuesto_ajax() {
        $obra = $this->ObrasModel->get_obra_detalle((int) $this->input->post('obra_id'));
        if (!$obra) {
            echo json_encode(['success' => false, 'message' => 'Obra no encontrada']);
            return;
        }
        $res = $this->PresupuestosObraModel->crear([
            'obra_id' => (int) $obra->id,
            'cliente_id' => (int) $obra->cliente_id,
            'sucursal_id' => $this->input->post('sucursal_id') ?: null,
            'tipo' => $this->input->post('tipo') ?: 'Presupuesto',
            'fecha' => $this->input->post('fecha') ?: date('Y-m-d'),
            'validez_dias' => (int) ($this->input->post('validez_dias') ?: 15),
            'pres_ref' => $this->input->post('pres_ref'),
            'atencion' => $this->input->post('atencion'),
            'condiciones_pago' => $this->input->post('condiciones_pago'),
            'notas_legales' => $this->input->post('notas_legales'),
            'descuento_porcentaje' => $this->input->post('descuento_porcentaje') ?: 0,
            'iva_porcentaje' => $this->input->post('iva_porcentaje') ?: 16,
            'creado_por' => $this->session->userdata('id') ?: 1,
        ]);
        echo json_encode($res);
    }

    public function listar_presupuestos_ajax() {
        $obra_id = (int) $this->input->get('obra_id');
        $presupuestos = $this->PresupuestosObraModel->listar(['obra_id' => $obra_id]);
        echo json_encode(['success' => true, 'presupuestos' => $presupuestos]);
    }

    public function get_presupuesto_ajax() {
        $presupuesto_id = (int) $this->input->get('presupuesto_id');
        $presupuesto = $this->PresupuestosObraModel->get_detalle($presupuesto_id);
        if (!$presupuesto) {
            echo json_encode(['success' => false, 'message' => 'Presupuesto no encontrado']);
            return;
        }
        $apu = [];
        foreach ($presupuesto->conceptos as $c) {
            if (!empty($c->concepto_id)) {
                $apu[$c->id] = $this->ApuModel->calcular_precio_unitario((int) $c->concepto_id);
            }
        }
        $conceptos = $this->ConceptosObraModel->listar();
        echo json_encode(['success' => true, 'presupuesto' => $presupuesto, 'apu' => $apu, 'conceptos' => $conceptos]);
    }

    /* ─── Partidas (conceptos del presupuesto) ──────────────────────── */

    public function agregar_partida_ajax() {
        $res = $this->PresupuestosObraModel->agregar_concepto($this->input->post());
        echo json_encode($res);
    }

    public function actualizar_partida_ajax() {
        $id = (int) $this->input->post('id');
        $res = $this->PresupuestosObraModel->actualizar_concepto($id, $this->input->post());
        echo json_encode($res);
    }

    public function eliminar_partida_ajax() {
        $id = (int) $this->input->post('id');
        $res = $this->PresupuestosObraModel->eliminar_concepto($id);
        echo json_encode($res);
    }

    /**
     * Usa el P.UNITARIO del APU de un concepto como P.U. de la partida.
     */
    public function usar_pu_partida_ajax() {
        $partida_id = (int) $this->input->post('partida_id');
        $this->db->where('id', $partida_id);
        $partida = $this->db->get('presupuesto_obra_conceptos')->row();
        if (!$partida || empty($partida->concepto_id)) {
            echo json_encode(['success' => false, 'message' => 'La partida no tiene concepto con APU']);
            return;
        }
        $apu = $this->ApuModel->calcular_precio_unitario((int) $partida->concepto_id);
        $this->PresupuestosObraModel->actualizar_concepto($partida_id, ['precio_unitario' => round($apu['precio_unitario'], 2)]);
        echo json_encode(['success' => true, 'precio_unitario' => round($apu['precio_unitario'], 2)]);
    }

    /* ─── APU (Unitarios) ──────────────────────────────────────────── */

    public function get_apu_ajax() {
        $concepto_id = (int) $this->input->get('concepto_id');
        $apu = $this->ApuModel->calcular_precio_unitario($concepto_id);
        $apu['materiales_lista'] = $this->ApuModel->get_materiales($concepto_id);
        $apu['cuadrillas_lista'] = $this->ApuModel->get_cuadrillas($concepto_id);
        echo json_encode($apu);
    }

    public function agregar_material_apu_ajax() {
        echo json_encode($this->ApuModel->agregar_material($this->input->post()));
    }

    public function eliminar_material_apu_ajax() {
        echo json_encode($this->ApuModel->eliminar_material((int) $this->input->post('id')));
    }

    public function agregar_cuadrilla_apu_ajax() {
        echo json_encode($this->ApuModel->agregar_cuadrilla($this->input->post()));
    }

    public function eliminar_cuadrilla_apu_ajax() {
        echo json_encode($this->ApuModel->eliminar_cuadrilla((int) $this->input->post('id')));
    }

    /* ─── Generadores ───────────────────────────────────────────────── */

    public function listar_generadores_ajax() {
        $obra_id = (int) $this->input->get('obra_id');
        $generadores = $this->GeneradoresModel->listar(['obra_id' => $obra_id]);
        foreach ($generadores as $g) {
            $g->lineas = $this->GeneradoresModel->listar_lineas($g->id);
        }
        echo json_encode(['success' => true, 'generadores' => $generadores]);
    }

    public function crear_generador_ajax() {
        echo json_encode($this->GeneradoresModel->crear($this->input->post()));
    }

    public function agregar_linea_generador_ajax() {
        echo json_encode($this->GeneradoresModel->agregar_linea($this->input->post()));
    }

    public function eliminar_linea_generador_ajax() {
        echo json_encode($this->GeneradoresModel->eliminar_linea((int) $this->input->post('id')));
    }

    public function aplicar_generador_ajax() {
        echo json_encode($this->GeneradoresModel->aplicar_a_partida((int) $this->input->post('generador_id')));
    }

    /* ─── Revisión de cuantificación ────────────────────────────────── */

    public function comparar_revision_ajax() {
        $obra_id = (int) $this->input->post('obra_id');
        $res = $this->RevisionCuantificacionModel->comparar($obra_id);
        $res['revisiones'] = $this->RevisionCuantificacionModel->listar($obra_id);
        echo json_encode($res);
    }

    public function actualizar_revision_ajax() {
        echo json_encode($this->RevisionCuantificacionModel->actualizar_revision((int) $this->input->post('id'), $this->input->post()));
    }

    public function listar_conceptos_ajax() {
        echo json_encode(['success' => true, 'conceptos' => $this->ConceptosObraModel->listar()]);
    }

    /* ─── Fase 5 · Exportación desde Ventas ─────────────────────────── */

    public function exportar_pdf_presupuesto($presupuesto_id, $tipo = 'presupuesto') {
        $this->load->model('Obras/ExportacionObraModel');
        $this->ExportacionObraModel->exportar_pdf($tipo, (int) $presupuesto_id);
    }

    public function exportar_excel($presupuesto_id, $tipo = 'presupuesto') {
        $this->load->model('Obras/ExportacionObraModel');
        $hojas = $this->input->get('hojas') ?: null;
        if (is_string($hojas)) {
            $hojas = array_filter(array_map('trim', explode(',', $hojas)));
        }
        $this->ExportacionObraModel->exportar_excel($tipo, (int) $presupuesto_id, $hojas);
    }

    /**
     * Renderiza una vista de impresión C1..C8 (HTML con marca de agua, ?auto=1 imprime directo).
     * Duplicado intencional de obras/Obras::imprimir_presupuesto() para que la pestaña de
     * presupuestos de Ventas no dependa de la ruta del módulo Obras.
     */
    public function imprimir_presupuesto($tipo, $presupuesto_id) {
        $this->load->model('Obras/ExportacionObraModel');
        $data = $this->ExportacionObraModel->preparar_datos($tipo, (int) $presupuesto_id);
        if (!$data) {
            show_404();
            return;
        }
        $vista = $this->ExportacionObraModel->nombre_vista($tipo);
        $this->load->view('obras/' . $vista, $data);
    }

    /**
     * Documentos (archivos adjuntos) de la obra — AJAX.
     * Alimenta la pestaña "Documentos" de la vista de obra en Ventas.
     */
    public function documentos_ajax() {
        $obra_id = (int) $this->input->get('obra_id');
        if ($obra_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Obra inválida']);
            return;
        }
        $this->load->model('Obras/ObrasModel');
        echo json_encode([
            'success' => true,
            'archivos' => $this->ObrasModel->get_archivos_obra($obra_id),
        ]);
    }

    /**
     * Sube un documento a la obra (AJAX) — misma regla de subida que
     * obras/Obras::subir_archivo_ajax, expuesta aquí para no exigir al usuario
     * de Ventas permisos sobre el módulo Obras.
     */
    public function subir_documento_ajax() {
        $obra_id = (int) $this->input->post('obra_id');

        if ($obra_id <= 0 || !isset($_FILES['archivo'])) {
            echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo']);
            return;
        }
        if (!$this->ObrasModel->get_obra_detalle($obra_id)) {
            echo json_encode(['success' => false, 'message' => 'Obra no encontrada']);
            return;
        }

        $config['upload_path'] = './uploads/obras/' . $obra_id . '/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif|pdf|doc|docx|xls|xlsx|dwg|dxf';
        $config['max_size'] = 10240;
        $config['encrypt_name'] = true;

        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('archivo')) {
            echo json_encode(['success' => false, 'message' => $this->upload->display_errors('', '')]);
            return;
        }

        $upload_data = $this->upload->data();
        $id = $this->ObrasModel->guardar_archivo([
            'obra_id' => $obra_id,
            'nombre_original' => $upload_data['orig_name'],
            'nombre_archivo' => $upload_data['file_name'],
            'ruta_archivo' => 'uploads/obras/' . $obra_id . '/' . $upload_data['file_name'],
            'tipo_archivo' => $upload_data['file_type'],
            'extension' => $upload_data['file_ext'],
            'tamano' => $upload_data['file_size'] * 1024,
            'categoria' => $this->input->post('categoria') ?: 'Otro',
            'descripcion' => $this->input->post('descripcion'),
            'etiquetas' => $this->input->post('etiquetas'),
            'subido_por' => $this->session->userdata('id') ?: 1,
        ]);

        echo json_encode($id
            ? ['success' => true, 'message' => 'Documento subido correctamente']
            : ['success' => false, 'message' => 'Error al guardar la información del documento']);
    }

    /**
     * Elimina un documento de la obra (AJAX).
     */
    public function eliminar_documento_ajax() {
        $archivo_id = (int) $this->input->post('archivo_id');
        $result = $archivo_id > 0 ? $this->ObrasModel->eliminar_archivo($archivo_id) : false;
        echo json_encode($result
            ? ['success' => true, 'message' => 'Documento eliminado']
            : ['success' => false, 'message' => 'Error al eliminar el documento']);
    }
}



