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
        $this->load->model('Ventas/SucursalesModel');
        $this->SucursalesModel->asegurar_infraestructura();
    }
    
    /**
     * Vista principal del POS
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Punto de Venta';
        $this->viewData['headTitle'] = 'Point of Sale (POS)';
        $this->viewData['breadcrumb'] = 'Inicio > CRM Ventas > POS';
        
        $sucursales = $this->SucursalesModel->listar_activas();
        $sucursal_id = (int) $this->session->userdata('pos_sucursal_id');
        if ($sucursal_id <= 0 && !empty($sucursales)) {
            $sucursal_id = (int) $sucursales[0]->id;
            $this->session->set_userdata('pos_sucursal_id', $sucursal_id);
        }
        $this->viewData['sucursales'] = $sucursales;
        $this->viewData['sucursal_id'] = $sucursal_id;
        
        $stats = $this->VentasModel->get_estadisticas($sucursal_id ?: null);
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
        $cliente_id = $this->input->post('cliente_id');
        $descuentos = $this->DescuentosModel->get_descuentos_activos($cliente_id);
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
        $sucursal_id = (int) ($this->input->post('sucursal_id') ?: $this->session->userdata('pos_sucursal_id'));
        
        if(!$cliente_id || !$detalles || count($detalles) == 0) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $sucursal = $this->SucursalesModel->get($sucursal_id);
        if (!$sucursal || $sucursal->estatus !== 'Activa') {
            echo json_encode(['success' => false, 'message' => 'Seleccione una sucursal activa']);
            return;
        }

        $err_precio = $this->VentasModel->validar_precios_pos($detalles);
        if ($err_precio) {
            echo json_encode(['success' => false, 'message' => $err_precio]);
            return;
        }
        $detalles = $this->VentasModel->aplicar_precios_catalogo($detalles);

        // Determinar estatus (antes de crear OV) para validar stock PT en mostrador
        $estatus_solicitado = $this->input->post('estatus');
        if ($estatus_solicitado == 'Cotización') {
            $estatus_preview = 'Cotización';
        } elseif ($tipo_venta == 'Mostrador') {
            $estatus_preview = 'Entregada';
        } else {
            $estatus_preview = 'Confirmada';
        }
        if ($estatus_preview === 'Entregada') {
            $chk_stock = $this->VentasModel->validar_stock_pt_lineas($detalles);
            if (!$chk_stock['ok']) {
                echo json_encode(['success' => false, 'message' => $chk_stock['message']]);
                return;
            }
        }

        $descuento_id = $this->input->post('descuento_id');
        $descuento_nombre = $this->input->post('descuento_nombre');
        $descuento_tipo = $this->input->post('descuento_tipo');
        $descuento_valor = $this->input->post('descuento_valor');
        
        $estatus_final = $estatus_preview;
        
        // Crear orden
        $data_orden = [
            'cliente_id' => $cliente_id,
            'sucursal_id' => $sucursal_id,
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
            'descuento_valor' => $descuento_valor ?: 0,
            'creado_por' => (int) ($this->session->userdata('id') ?: 0),
        ];
        
        $orden_id = $this->VentasModel->crear_orden($data_orden);
        
        if(!$orden_id) {
            echo json_encode(['success' => false, 'message' => 'Error al crear orden']);
            return;
        }
        
        // Agregar detalles
        $this->VentasModel->agregar_detalle($orden_id, $detalles);
        
        // Verificar insumos: solo consulta en cotización; pre-órdenes al comprometer venta
        $usuario_id = (int) ($this->session->userdata('id') ?: 0);
        $insumos_result = null;
        if ($usuario_id > 0) {
            $es_compromiso = in_array($estatus_final, ['Confirmada', 'En Preparación', 'Entregada'], true);
            if ($es_compromiso) {
                $insumos_result = $this->VentasModel->verificar_insumos_y_preordenes_venta($orden_id, $usuario_id);
            } else {
                $insumos_result = $this->VentasModel->consultar_insumos_venta($orden_id);
            }
        }
        
        // Obtener orden con totales calculados
        $orden_creada = $this->VentasModel->get_orden_completa($orden_id);
        
        // Inicializar campos de pago SIEMPRE
        $this->db->where('id', $orden_id);
        $this->db->update('ordenes_venta', [
            'saldo_pendiente' => $orden_creada->total,
            'monto_pagado' => 0,
            'estatus_pago' => 'Pendiente'
        ]);
        
        // Si es venta de Mostrador y Entregada, descontar stock PT (con validación)
        if ($estatus_final == 'Entregada') {
            $entrega = $this->VentasModel->entregar_orden($orden_id);
            if (empty($entrega['success'])) {
                $this->db->where('id', $orden_id);
                $this->db->update('ordenes_venta', ['estatus' => 'En Preparación']);
                echo json_encode([
                    'success' => false,
                    'message' => $entrega['message'] ?? 'No se pudo entregar la orden.',
                    'orden_id' => $orden_id,
                ]);
                return;
            }

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
        // Cotización no es compromiso: no emitir factura.
        $cliente = $this->ClientesModel->get_cliente($cliente_id);
        if($estatus_final !== 'Cotización' && $cliente && $cliente->rfc && $cliente->razon_social) {
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
        
        $this->registrar_bitacora(
            ($estatus_final === 'Cotización' ? 'Cotización' : 'Venta') . ' ' . $orden->folio . ' creada (cliente ID ' . $cliente_id . ')',
            'Ventas'
        );
        
        echo json_encode([
            'success' => true, 
            'message' => 'Venta registrada correctamente',
            'orden_id' => $orden_id,
            'folio' => $orden->folio,
            'insumos' => $this->VentasModel->formatear_insumos_respuesta_json($insumos_result),
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

    public function cli_bootstrap() {
        if (!is_cli()) {
            show_error('Solo CLI', 403);
            return;
        }
        $this->SucursalesModel->asegurar_infraestructura();
        $n = $this->db->count_all('sucursales');
        $col = $this->db->field_exists('sucursal_id', 'ordenes_venta') ? 'si' : 'no';
        echo json_encode(['sucursales' => $n, 'ov_sucursal_id' => $col], JSON_UNESCAPED_UNICODE) . PHP_EOL;
    }

    public function seleccionar_sucursal_ajax() {
        $id = (int) $this->input->post('sucursal_id');
        $s = $this->SucursalesModel->get($id);
        if (!$s || $s->estatus !== 'Activa') {
            echo json_encode(['success' => false, 'message' => 'Sucursal no válida']);
            return;
        }
        $this->session->set_userdata('pos_sucursal_id', $id);
        echo json_encode(['success' => true, 'sucursal' => $s]);
    }

    public function crear_sucursal_ajax() {
        $res = $this->SucursalesModel->crear([
            'codigo' => $this->input->post('codigo'),
            'nombre' => $this->input->post('nombre'),
            'direccion' => $this->input->post('direccion'),
            'telefono' => $this->input->post('telefono'),
        ]);
        if (!empty($res['success'])) {
            $this->session->set_userdata('pos_sucursal_id', $res['id']);
        }
        echo json_encode($res);
    }
    
}
