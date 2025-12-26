<?php
/**
 * ObrasVentas Controller - Vista de Obras desde CRM de Ventas
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class ObrasVentas extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Obras/ObrasModel');
        $this->load->model('Ventas/VentasModel');
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
        $data['response'] = ['stats' => $stats];
        
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
        $search = $this->input->post('search')['value'];
        $order_column = $this->input->post('order')[0]['column'];
        $order_dir = $this->input->post('order')[0]['dir'];
        
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
            o.iva_monto
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        
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
            
            $data[] = [
                $obra->folio,
                $obra->nombre,
                $obra->cliente,
                $estatus_badge,
                date('d/m/Y', strtotime($obra->fecha_creacion)),
                '$' . number_format($obra->total, 2),
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
        // Obtener datos de la obra
        $this->db->select('
            o.*,
            c.razon_social as cliente,
            c.nombre_comercial,
            c.telefono,
            c.email,
            c.rfc
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->where('o.id', $obra_id);
        $obra = $this->db->get()->row();
        
        if(!$obra) {
            show_404();
            return;
        }
        
        // Obtener productos
        $this->db->select('
            op.*,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo,
            p.foto_producto,
            f.version as formulacion_version,
            f.nombre_version as formulacion_nombre
        ');
        $this->db->from('obras_productos op');
        $this->db->join('productos p', 'p.id = op.producto_id');
        $this->db->join('formulaciones f', 'f.id = op.formulacion_id', 'left');
        $this->db->where('op.obra_id', $obra_id);
        $this->db->order_by('op.fecha_agregado', 'DESC');
        
        $obra->productos = $this->db->get()->result();
        
        // Obtener pagos
        $this->db->where('obra_id', $obra_id);
        $this->db->order_by('fecha_pago', 'DESC');
        $obra->pagos = $this->db->get('obras_pagos')->result();
        
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
        
        // Total de obras
        $stats['total'] = $this->db->count_all('obras');
        
        // Obras en cotización
        $this->db->where('estatus', 'En Cotización');
        $stats['en_cotizacion'] = $this->db->count_all_results('obras');
        
        // Obras aprobadas
        $this->db->where('estatus', 'Aprobada');
        $stats['aprobadas'] = $this->db->count_all_results('obras');
        
        // Obras en ejecución
        $this->db->where('estatus', 'En Ejecución');
        $stats['en_ejecucion'] = $this->db->count_all_results('obras');
        
        // Obras completadas
        $this->db->where('estatus', 'Completada');
        $stats['completadas'] = $this->db->count_all_results('obras');
        
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
            'creado_por' => $this->session->userdata('user_id') ?? 1
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
}
