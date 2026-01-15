<?php
/**
 * Ordenes Controller - Gestión de Órdenes de Venta
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Ordenes extends MY_Controller {
    
    protected $modulo = 'Ventas';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Ventas/VentasModel');
        $this->load->model('Ventas/ClientesModel');
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Órdenes de Venta';
        $this->viewData['headTitle'] = 'Gestión de Órdenes de Venta';
        $this->viewData['breadcrumb'] = 'Inicio > CRM Ventas > Órdenes';
        
        $stats = $this->VentasModel->get_estadisticas();
        $this->viewData['response'] = ['stats' => $stats];
        
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'ventas/ordenes/main';
        
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Lista de órdenes para DataTables (AJAX)
     */
    public function lista_ajax() {
        $start = $this->input->post('start') ?? 0;
        $length = $this->input->post('length') ?? 10;
        $search = $this->input->post('search')['value'] ?? '';
        $order_column = $this->input->post('order')[0]['column'] ?? 0;
        $order_dir = $this->input->post('order')[0]['dir'] ?? 'desc';
        
        // Filtros
        $filtro_estatus = $this->input->post('filtro_estatus');
        $filtro_tipo = $this->input->post('filtro_tipo');
        $filtro_fecha_desde = $this->input->post('filtro_fecha_desde');
        $filtro_fecha_hasta = $this->input->post('filtro_fecha_hasta');
        
        // Construir query
        $this->db->select('ordenes_venta.*, clientes.razon_social, clientes.nombre_comercial');
        $this->db->from('ordenes_venta');
        $this->db->join('clientes', 'clientes.id = ordenes_venta.cliente_id');
        
        // Aplicar filtros
        if($filtro_estatus) {
            $this->db->where('ordenes_venta.estatus', $filtro_estatus);
        }
        
        if($filtro_tipo) {
            $this->db->where('ordenes_venta.tipo_venta', $filtro_tipo);
        }
        
        if($filtro_fecha_desde) {
            $this->db->where('ordenes_venta.fecha_orden >=', $filtro_fecha_desde);
        }
        
        if($filtro_fecha_hasta) {
            $this->db->where('ordenes_venta.fecha_orden <=', $filtro_fecha_hasta);
        }
        
        // Búsqueda
        if($search) {
            $this->db->group_start();
            $this->db->like('ordenes_venta.folio', $search);
            $this->db->or_like('clientes.razon_social', $search);
            $this->db->or_like('clientes.nombre_comercial', $search);
            $this->db->group_end();
        }
        
        // Total filtrado
        $total_filtered = $this->db->count_all_results('', false);
        
        // Ordenamiento
        $columns = ['folio', 'fecha_orden', 'razon_social', 'total', 'estatus'];
        if(isset($columns[$order_column])) {
            $this->db->order_by($columns[$order_column], $order_dir);
        }
        
        // Paginación
        $this->db->limit($length, $start);
        $ordenes = $this->db->get()->result();
        
        // Total sin filtros
        $this->db->from('ordenes_venta');
        $total_records = $this->db->count_all_results();
        
        // Pre-cargar facturas para evitar consultas en el loop
        $facturas_map = [];
        if($this->db->table_exists('facturas')) {
            $orden_ids = array_column($ordenes, 'id');
            if(!empty($orden_ids)) {
                $this->db->select('orden_venta_id, id');
                $this->db->where_in('orden_venta_id', $orden_ids);
                $facturas = $this->db->get('facturas')->result();
                foreach($facturas as $f) {
                    $facturas_map[$f->orden_venta_id] = $f;
                }
            }
        }
        
        // Formatear datos
        $data = [];
        foreach($ordenes as $orden) {
            $row = [];
            
            // Folio
            $row[] = $orden->folio;
            
            // Fecha
            $row[] = date('d/m/Y', strtotime($orden->fecha_orden));
            
            // Cliente
            $cliente = $orden->razon_social;
            if($orden->nombre_comercial) {
                $cliente .= '<br><small class="text-muted">' . $orden->nombre_comercial . '</small>';
            }
            $row[] = $cliente;
            
            // Total
            $row[] = '$' . number_format($orden->total, 2);
            
            // Saldo Pendiente
            $saldo_class = $orden->saldo_pendiente > 0 ? 'text-danger' : 'text-success';
            $row[] = '<span class="' . $saldo_class . '">$' . number_format($orden->saldo_pendiente, 2) . '</span>';
            
            // Estatus Pago
            $pago_badges = [
                'Pendiente' => 'danger',
                'Parcial' => 'warning',
                'Pagado' => 'success'
            ];
            $badge = $pago_badges[$orden->estatus_pago] ?? 'secondary';
            $row[] = '<span class="badge bg-' . $badge . '">' . $orden->estatus_pago . '</span>';
            
            // Tipo
            $tipo_badges = [
                'Mostrador' => 'secondary',
                'Pedido' => 'primary'
            ];
            $badge = $tipo_badges[$orden->tipo_venta] ?? 'secondary';
            $row[] = '<span class="badge bg-' . $badge . '">' . $orden->tipo_venta . '</span>';
            
            // Estatus
            $estatus_badges = [
                'Cotización' => 'warning',
                'Confirmada' => 'info',
                'En Preparación' => 'primary',
                'Entregada' => 'success',
                'Cancelada' => 'danger'
            ];
            $badge = $estatus_badges[$orden->estatus] ?? 'secondary';
            $row[] = '<span class="badge bg-' . $badge . '">' . $orden->estatus . '</span>';
            
            // Acciones
            $acciones = '
            <button class="btn btn-sm btn-info" onclick="verOrden('.$orden->id.')" title="Ver detalles">
                <i class="fas fa-eye"></i>
            </button>';
            
            // DEBUG: Agregar información de depuración como atributo data
            $debug_info = 'data-debug="saldo:'.$orden->saldo_pendiente.' estatus:'.$orden->estatus.'"';
            
            // Botón de pago si tiene saldo pendiente
            if($orden->saldo_pendiente > 0 && $orden->estatus != 'Cancelada') {
                $acciones .= '
                <button class="btn btn-sm btn-success" onclick="mostrarModalPago('.$orden->id.', '.$orden->saldo_pendiente.')" title="Registrar pago" '.$debug_info.'>
                    <i class="fas fa-dollar-sign"></i>
                </button>';
            } else {
                // DEBUG: Agregar comentario HTML para ver por qué no se muestra
                $acciones .= '<!-- Botón pago NO mostrado: saldo='.$orden->saldo_pendiente.' estatus='.$orden->estatus.' -->';
            }
            
            if($orden->estatus == 'Cotización') {
                $acciones .= '
                <button class="btn btn-sm btn-success" onclick="confirmarOrden('.$orden->id.')" title="Confirmar">
                    <i class="fas fa-check"></i>
                </button>';
            }
            
            if($orden->estatus != 'Entregada' && $orden->estatus != 'Cancelada') {
                $acciones .= '
                <button class="btn btn-sm btn-danger" onclick="cancelarOrden('.$orden->id.')" title="Cancelar">
                    <i class="fas fa-times"></i>
                </button>';
            } else {
                // DEBUG: Agregar comentario HTML
                $acciones .= '<!-- Botón cancelar NO mostrado: estatus='.$orden->estatus.' -->';
            }
            
            $acciones .= '
            <a href="'.base_url().'ventas/Pos/imprimir_recibo/'.$orden->id.'" target="_blank" class="btn btn-sm btn-secondary" title="Imprimir">
                <i class="fas fa-print"></i>
            </a>';
            
            // Verificar si tiene factura simulada usando el mapa pre-cargado
            if(isset($facturas_map[$orden->id])) {
                $acciones .= '
                <a href="'.base_url().'ventas/Pos/imprimir_factura/'.$orden->id.'" target="_blank" class="btn btn-sm btn-primary" title="Ver Factura">
                    <i class="fas fa-file-invoice"></i>
                </a>';
            }
            
            $row[] = $acciones;
            
            $data[] = $row;
        }
        
        $output = [
            'draw' => intval($this->input->post('draw')),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_filtered,
            'data' => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Confirma una orden (AJAX)
     */
    public function confirmar_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $result = $this->VentasModel->confirmar_orden($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Orden confirmada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al confirmar orden']);
        }
    }
    
    /**
     * Cancela una orden (AJAX)
     */
    public function cancelar_ajax() {
        $id = $this->input->post('id');
        $motivo = $this->input->post('motivo');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $data = [
            'estatus' => 'Cancelada',
            'motivo_cancelacion' => $motivo
        ];
        
        $this->db->where('id', $id);
        $result = $this->db->update('ordenes_venta', $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Orden cancelada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cancelar orden']);
        }
    }
    
    /**
     * Registra un pago (AJAX)
     */
    public function registrar_pago_ajax() {
        $orden_id = $this->input->post('orden_id');
        $monto = $this->input->post('monto');
        $metodo_pago = $this->input->post('metodo_pago');
        $fecha_pago = $this->input->post('fecha_pago');
        $referencia = $this->input->post('referencia');
        $notas = $this->input->post('notas');
        
        if(!$orden_id || !$monto || !$metodo_pago || !$fecha_pago) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        // Verificar que el monto no exceda el saldo pendiente
        $this->db->select('saldo_pendiente');
        $this->db->where('id', $orden_id);
        $orden = $this->db->get('ordenes_venta')->row();
        
        if(!$orden) {
            echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
            return;
        }
        
        if($monto > $orden->saldo_pendiente) {
            echo json_encode(['success' => false, 'message' => 'El monto excede el saldo pendiente']);
            return;
        }
        
        // Generar folio
        $this->db->query("CALL sp_generar_folio_pago(@nuevo_folio)");
        $result = $this->db->query("SELECT @nuevo_folio as folio")->row();
        
        // Insertar pago
        $data_pago = [
            'orden_venta_id' => $orden_id,
            'folio' => $result->folio,
            'fecha_pago' => $fecha_pago,
            'monto' => $monto,
            'metodo_pago' => $metodo_pago,
            'referencia' => $referencia,
            'notas' => $notas
        ];
        
        $this->db->insert('pagos_ordenes', $data_pago);
        
        if($this->db->affected_rows() > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Pago registrado correctamente',
                'folio' => $result->folio
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar pago']);
        }
    }
    
    /**
     * Obtiene pagos de una orden (AJAX)
     */
    public function get_pagos_orden_ajax() {
        $orden_id = $this->input->post('orden_id');
        
        if(!$orden_id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $this->db->select('*');
        $this->db->where('orden_venta_id', $orden_id);
        $this->db->order_by('fecha_pago', 'DESC');
        $pagos = $this->db->get('pagos_ordenes')->result();
        
        echo json_encode(['success' => true, 'pagos' => $pagos]);
    }
}
