<?php
/**
 * POS Controller - Point of Sale
 * 
 * Gestión de punto de venta
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Pos extends MY_Controller {
    
    protected $modulo = 'Ventas';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Ventas/VentasModel');
        $this->load->model('Ventas/ClientesModel');
        $this->load->model('Produccion/ProductosModel');
        $this->load->model('Ventas/DescuentosModel');
    }
    
    /**
     * Vista principal del POS
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Punto de Venta';
        $this->viewData['headTitle'] = 'Point of Sale (POS)';
        $this->viewData['breadcrumb'] = 'Inicio > CRM Ventas > POS';
        
        // Obtener estadísticas
        $stats = $this->VentasModel->get_estadisticas();
        $this->viewData['response'] = ['stats' => $stats];
        
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'ventas/pos/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene productos para el POS (AJAX)
     */
    public function get_productos_ajax() {
        $busqueda = $this->input->post('busqueda');
        $categoria_id = $this->input->post('categoria_id');
        
        $productos = $this->VentasModel->get_productos_pos($busqueda, $categoria_id);
        
        echo json_encode(['success' => true, 'productos' => $productos]);
    }
    
    /**
     * Obtiene clientes para select (AJAX)
     */
    public function get_clientes_ajax() {
        $clientes = $this->ClientesModel->get_clientes_select();
        
        // Agregar cliente MOSTRADOR al inicio
        $mostrador = $this->ClientesModel->get_cliente_mostrador();
        if($mostrador) {
            array_unshift($clientes, $mostrador);
        }
        
        echo json_encode(['success' => true, 'clientes' => $clientes]);
    }
    
    /**
     * Obtiene descuentos activos para select (AJAX)
     */
    public function get_descuentos_ajax() {
        $descuentos = $this->DescuentosModel->get_descuentos_activos();
        echo json_encode(['success' => true, 'descuentos' => $descuentos]);
    }
    
    /**
     * Crea una orden de venta (AJAX)
     */
    public function crear_orden_ajax() {
        $cliente_id = $this->input->post('cliente_id');
        $tipo_venta = $this->input->post('tipo_venta'); // Mostrador o Pedido
        $forma_pago = $this->input->post('forma_pago');
        $estatus = $this->input->post('estatus'); // Cotización o Entregada
        $observaciones = $this->input->post('observaciones');
        $detalles = json_decode($this->input->post('detalles'), true);
        
        // Descuento
        $descuento_id = $this->input->post('descuento_id');
        $descuento_nombre = $this->input->post('descuento_nombre');
        $descuento_tipo = $this->input->post('descuento_tipo');
        $descuento_valor = $this->input->post('descuento_valor');
        
        if(!$cliente_id || !$detalles || count($detalles) == 0) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        // Determinar estatus correcto según tipo de venta
        $estatus_solicitado = $this->input->post('estatus'); // Cotización o Entregada
        
        // Lógica de estatus:
        // - Cotización: siempre queda como Cotización
        // - Mostrador + Cobrar: Entregada (se entrega inmediatamente)
        // - Pedido + Cobrar: Confirmada (requiere preparación/envío)
        if($estatus_solicitado == 'Cotización') {
            $estatus_final = 'Cotización';
        } else {
            // Si se está cobrando (no es cotización)
            if($tipo_venta == 'Mostrador') {
                $estatus_final = 'Entregada'; // Mostrador se entrega inmediato
            } else {
                $estatus_final = 'Confirmada'; // Pedido requiere preparación
            }
        }
        
        // Crear orden
        $data_orden = [
            'cliente_id' => $cliente_id,
            'fecha_orden' => date('Y-m-d'),
            'fecha_entrega_estimada' => $this->input->post('fecha_entrega_estimada'),
            'forma_pago' => $forma_pago,
            'estatus' => $estatus_final,
            'tipo_venta' => $tipo_venta,
            'direccion_envio' => $this->input->post('direccion_envio'),
            'costo_envio' => $this->input->post('costo_envio') ?: 0,
            'observaciones' => $observaciones,
            'descuento_id' => $descuento_id ?: null,
            'descuento_nombre' => $descuento_nombre ?: null,
            'descuento_tipo' => $descuento_tipo ?: null,
            'descuento_valor' => $descuento_valor ?: 0
        ];
        
        $orden_id = $this->VentasModel->crear_orden($data_orden);
        
        if(!$orden_id) {
            echo json_encode(['success' => false, 'message' => 'Error al crear orden']);
            return;
        }
        
        // Agregar detalles
        $this->VentasModel->agregar_detalle($orden_id, $detalles);
        
        // Obtener orden con totales calculados
        $orden_creada = $this->VentasModel->get_orden_completa($orden_id);
        
        // Inicializar campos de pago SIEMPRE
        $this->db->where('id', $orden_id);
        $this->db->update('ordenes_venta', [
            'saldo_pendiente' => $orden_creada->total,
            'monto_pagado' => 0,
            'estatus_pago' => 'Pendiente'
        ]);
        
        // Si es venta de Mostrador y Entregada, descontar stock
        if($estatus_final == 'Entregada') {
            $this->VentasModel->entregar_orden($orden_id);
            
            // Si NO es crédito, registrar el pago automáticamente
            if($forma_pago != 'Crédito') {
                // Generar folio de pago
                $this->db->query("CALL sp_generar_folio_pago(@nuevo_folio)");
                $result = $this->db->query("SELECT @nuevo_folio as folio")->row();
                
                $data_pago = [
                    'orden_venta_id' => $orden_id,
                    'folio' => $result->folio,
                    'fecha_pago' => date('Y-m-d'),
                    'monto' => $orden_creada->total,
                    'metodo_pago' => $forma_pago,
                    'referencia' => 'Pago automático POS',
                    'notas' => 'Pago registrado automáticamente al cobrar en POS'
                ];
                
                $this->db->insert('pagos_ordenes', $data_pago);
                // El trigger actualizará automáticamente saldo_pendiente y estatus_pago
            }
        }
        
        // --- Generar Factura Simulada (Snapshot) ---
        // Obtener datos fiscales del cliente
        $cliente = $this->ClientesModel->get_cliente($cliente_id);
        
        // Solo facturar si tiene RFC válido (no genérico XAXX...) o si es Mostrador pero se piden datos
        // Simplificación: Generamos factura para todos los clientes registrados que tengan RFC/Razón Social
        if($cliente && $cliente->rfc && $cliente->razon_social) {
            $orden_creada = $this->VentasModel->get_orden_completa($orden_id);
            
            // Generar UUID simulado
            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            
            $data_factura = [
                'orden_venta_id' => $orden_id,
                'cliente_id' => $cliente_id,
                'rfc' => $cliente->rfc,
                'razon_social' => $cliente->razon_social,
                'regimen_fiscal' => $cliente->regimen_fiscal ?: '616',
                'uso_cfdi' => $cliente->uso_cfdi ?: 'G03',
                'codigo_postal' => $cliente->codigo_postal ?: '',
                'folio_fiscal' => $uuid,
                'folio' => 'F-' . $orden_creada->folio, // Folio interno factura
                'fecha_emision' => date('Y-m-d H:i:s'),
                'subtotal' => $orden_creada->subtotal,
                'iva' => $orden_creada->iva,
                'total' => $orden_creada->total,
                'estatus' => 'Emitida'
            ];
            
            $this->db->insert('facturas', $data_factura);
        }
        
        // Obtener orden completa para respuesta
        $orden = $this->VentasModel->get_orden_completa($orden_id);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Venta registrada correctamente',
            'orden_id' => $orden_id,
            'folio' => $orden->folio
        ]);
    }
    
    /**
     * Obtiene una orden para ver/imprimir (AJAX)
     */
    public function get_orden_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $orden = $this->VentasModel->get_orden_completa($id);
        
        if($orden) {
            echo json_encode(['success' => true, 'orden' => $orden]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
        }
    }
    
    /**
     * Vista de impresión de recibo
     */
    public function imprimir_recibo($orden_id) {
        $orden = $this->VentasModel->get_orden_completa($orden_id);
        
        if(!$orden) {
            show_404();
            return;
        }
        
        $data['orden'] = $orden;
        $this->load->view('ventas/pos/recibo', $data);
    }
    /**
     * Obtiene productos top para POS (AJAX)
     */
    public function get_top_productos_ajax() {
        $productos = $this->VentasModel->get_top_productos(6);
        echo json_encode(['success' => true, 'productos' => $productos]);
    }
    
    /**
     * Guarda datos fiscales del cliente desde POS (AJAX)
     */
    public function guardar_datos_fiscales_ajax() {
        $cliente_id = $this->input->post('cliente_id');
        $rfc = $this->input->post('rfc');
        $razon_social = $this->input->post('razon_social');
        $regimen_fiscal = $this->input->post('regimen_fiscal');
        $codigo_postal = $this->input->post('codigo_postal');
        $uso_cfdi = $this->input->post('uso_cfdi');
        $email = $this->input->post('email_facturacion');
        
        if(!$cliente_id || !$rfc || !$razon_social) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $data = [
            'rfc' => $rfc,
            'razon_social' => $razon_social,
            'regimen_fiscal' => $regimen_fiscal,
            'codigo_postal' => $codigo_postal,
            'uso_cfdi' => $uso_cfdi,
            'email_facturacion' => $email
        ];
        
        $result = $this->ClientesModel->actualizar_cliente($cliente_id, $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Datos actualizados correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar datos']);
        }
    }
    
    /**
     * Vista de impresión de factura simulada
     */
    public function imprimir_factura($orden_id) {
        $orden = $this->VentasModel->get_orden_completa($orden_id);
        
        if(!$orden) {
            show_404();
            return;
        }
        
        // Obtener datos de factura
        $this->db->where('orden_venta_id', $orden_id);
        $factura = $this->db->get('facturas')->row();
        
        if(!$factura) {
            echo "No hay factura generada para esta orden.";
            return;
        }
        
        $data['orden'] = $orden;
        $data['factura'] = $factura;
        $this->load->view('ventas/pos/factura', $data);
    }
    
    /**
     * Obtiene la formulación activa de un producto (AJAX)
     */
    public function get_formulacion_ajax() {
        $producto_id = $this->input->post('producto_id');
        
        if(!$producto_id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $formulacion = $this->ProductosModel->get_formulacion_activa($producto_id);
        
        if($formulacion) {
            echo json_encode(['success' => true, 'formulacion' => $formulacion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Este producto no tiene una formulación activa asignada.']);
        }
    }
    
    /**
     * Obtiene historial de formulaciones de un producto (AJAX)
     */
    public function get_historial_formulaciones_ajax() {
        $producto_id = $this->input->post('producto_id');
        
        if(!$producto_id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $historial = $this->ProductosModel->get_historial_formulaciones($producto_id);
        
        // Obtener también componentes para cada una, o permitir cargarlos on-demand
        // Para simplificar, devolvemos el historial básico y si selecciona una, usamos get_formulacion_completa
        
        if($historial) {
            echo json_encode(['success' => true, 'historial' => $historial]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró historial de formulaciones']);
        }
    }
    
    /**
     * Vista de impresión de recibo con selector de template (1=Factura, 2=Remisión, 3=Moderno)
     */
    public function imprimir_recibo_template($orden_id, $template = 1) {
        $orden = $this->VentasModel->get_orden_completa($orden_id);
        if(!$orden) { show_404(); return; }
        $template = intval($template);
        if($template < 1 || $template > 3) $template = 1;

        $this->load->model('Config/EmpresaModel');
        $empresa = $this->EmpresaModel->get_config();

        $data['orden']    = $orden;
        $data['template'] = $template;
        $data['empresa']  = $empresa;
        $this->load->view('ventas/pos/recibo_template', $data);
    }

    /**
     * Obtiene formulación específica por ID (AJAX)
     */
    public function get_formulacion_detalle_ajax() {
        $formulacion_id = $this->input->post('formulacion_id');
        
        if(!$formulacion_id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $formulacion = $this->ProductosModel->get_formulacion_completa($formulacion_id);
        
        if($formulacion) {
            echo json_encode(['success' => true, 'formulacion' => $formulacion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Formulación no encontrada']);
        }
    }
    
}
