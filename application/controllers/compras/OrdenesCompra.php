<?php
/**
 * OrdenesCompra - Controlador de gestión de órdenes de compra
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class OrdenesCompra extends MY_Controller {
    
    protected $modulo = 'Compras';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Compras/OrdenesCompraModel');
        $this->load->model('Compras/ProveedoresModel');
        $this->load->model('Compras/InsumosModel');
        $this->load->model('Compras/PreordenesModel');
        $this->load->helper('permissions');
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Órdenes de Compra';
        $this->viewData['headTitle'] = 'Gestión de Órdenes de Compra';
        $this->viewData['breadcrumb'] = 'Inicio > Compras > Órdenes de Compra';
        
        // Obtener estadísticas
        $stats = $this->OrdenesCompraModel->get_estadisticas();
        $this->viewData['response'] = [
            'stats' => $stats,
            'nueva_proveedor' => (int) $this->input->get('nueva_proveedor'),
        ];
        
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'compras/ordenes_compra/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Lista de órdenes para DataTables (AJAX)
     */
    public function lista_ajax() {
        $list = $this->OrdenesCompraModel->get_datatables();
        $data = array();
        $no = (int) ($this->input->post('start') ?? 0);

        foreach ($list as $orden) {
            $no++;
            $row = array();
            
            // Folio
            $row[] = '<strong>' . $orden->folio . '</strong>';
            
            // Fecha
            $row[] = date('d/m/Y', strtotime($orden->fecha_orden));
            
            // Proveedor
            $row[] = $orden->razon_social;
            
            // Total
            $row[] = '$' . number_format($orden->total, 2);

            // Estatus de pago / adeudo
            $pago_badges = [
                'Pendiente' => 'danger',
                'Parcial' => 'warning',
                'Pagado' => 'success',
                'Sin adeudo' => 'secondary',
            ];
            $ep = $orden->estatus_pago ?? 'Sin adeudo';
            $pb = $pago_badges[$ep] ?? 'secondary';
            $pago_html = '<span class="badge bg-' . $pb . '">' . $ep . '</span>';
            if (!in_array($orden->estatus, ['Borrador', 'Cancelada'], true) && (float)($orden->saldo_pendiente ?? 0) > 0) {
                $pago_html .= '<br><small class="text-danger">Adeudo: $' . number_format($orden->saldo_pendiente, 2) . '</small>';
            } elseif ($ep === 'Pagado' && (float)($orden->monto_pagado ?? 0) > 0) {
                $pago_html .= '<br><small class="text-muted">$' . number_format($orden->monto_pagado, 2) . '</small>';
            }
            $row[] = $pago_html;
            
            // Estatus con badge
            $badge_class = '';
            switch($orden->estatus) {
                case 'Borrador': $badge_class = 'secondary'; break;
                case 'Enviada': $badge_class = 'primary'; break;
                case 'Confirmada': $badge_class = 'info'; break;
                case 'En Tránsito': $badge_class = 'warning'; break;
                case 'Recibida Parcial': $badge_class = 'warning'; break;
                case 'Recibida': $badge_class = 'success'; break;
                case 'Cancelada': $badge_class = 'danger'; break;
                default: $badge_class = 'secondary';
            }
            $row[] = '<span class="badge bg-' . $badge_class . '">' . $orden->estatus . '</span>';
            
            // Acciones según permisos
            $acciones = '';
            if (tiene_permiso('compras_ordenes_consult')) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-info" onclick="verOrden('.$orden->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>';
            }
            
            if($orden->estatus == 'Borrador') {
                if (tiene_permiso('compras_ordenes_edit')) {
                    $acciones .= '
                <button type="button" class="btn btn-sm btn-primary" onclick="editarOrden('.$orden->id.')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-success" onclick="aprobarOrden('.$orden->id.')" title="Aprobar y Enviar">
                    <i class="fas fa-check"></i>
                </button>';
                }
                if (tiene_permiso('compras_ordenes_delete')) {
                    $acciones .= '
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarOrden('.$orden->id.')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>';
                }
            }
            
            if(in_array($orden->estatus, ['Enviada', 'Confirmada', 'En Tránsito', 'Recibida Parcial']) && tiene_permiso('compras_recepcion')) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-warning" onclick="recibirMercancia('.$orden->id.')" title="Recibir Mercancía">
                    <i class="fas fa-truck-loading"></i>
                </button>';
            }

            if(in_array($orden->estatus, ['Enviada', 'Confirmada']) && tiene_permiso('compras_ordenes_consult')) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="simularCorreoProveedor('.$orden->id.')" title="Enviar solicitud (simulación)">
                    <i class="fas fa-envelope"></i>
                </button>';
            }
            
            if (tiene_permiso('compras_documentos') || tiene_permiso('compras_ordenes_edit')) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="gestionarOrden('.$orden->id.', \''.addslashes($orden->folio).'\')" title="Documentos y comentarios">
                    <i class="fas fa-paperclip"></i>
                </button>';
            }
            if (tiene_permiso('compras_ordenes_consult')) {
                $acciones .= '
                <a href="'.base_url('compras/OrdenesCompra/generar_pdf/'.$orden->id).'" target="_blank" class="btn btn-sm btn-danger" title="Generar PDF">
                    <i class="fas fa-file-pdf"></i>
                </a>';
            }

            if (tiene_permiso('compras_pagos') && !in_array($orden->estatus, ['Borrador', 'Cancelada'], true) && (float)($orden->saldo_pendiente ?? 0) > 0) {
                $saldo = (float) $orden->saldo_pendiente;
                $acciones .= '
                <button type="button" class="btn btn-sm btn-outline-success" onclick="mostrarModalPagoOc('.$orden->id.', '.$saldo.', \''.addslashes($orden->folio).'\')" title="Registrar pago">
                    <i class="fas fa-dollar-sign"></i>
                </button>';
            }

            $row[] = $acciones ?: '<span class="text-muted small">—</span>';
            
            $data[] = $row;
        }

        $output = array(
            "draw" => (int) ($this->input->post('draw') ?? 0),
            "recordsTotal" => $this->OrdenesCompraModel->count_all(),
            "recordsFiltered" => $this->OrdenesCompraModel->count_filtered(),
            "data" => $data,
        );

        echo json_encode($output);
    }
    
    /**
     * Obtiene una orden específica con detalles (AJAX)
     */
    public function get_orden_ajax() {
        $this->requiere_permiso('compras_ordenes_consult', 'No tienes permiso para consultar órdenes de compra');

        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $orden = $this->OrdenesCompraModel->get_orden($id);
        if($orden) {
            echo json_encode(['success' => true, 'orden' => $orden]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
        }
    }
    
    /**
     * Genera la vista imprimible / PDF de una orden de compra
     */
    public function generar_pdf($id = null) {
        if(!$id) show_404();

        $this->requiere_permiso('compras_ordenes_consult', 'No tienes permiso para consultar órdenes de compra');
        
        $orden = $this->OrdenesCompraModel->get_orden($id);
        if(!$orden) show_404();
        
        $detalles = $orden->detalles ?? [];
        
        $this->load->model('Config/EmpresaModel');
        $empresa = $this->EmpresaModel->get_config();
        
        $this->load->view('compras/ordenes_compra/pdf_oc', [
            'orden'    => $orden,
            'detalles' => $detalles,
            'empresa'  => $empresa,
        ]);
    }
    
    /**
     * Crea una nueva orden (AJAX)
     */
    public function crear_ajax() {
        $this->requiere_permiso('compras_ordenes_add', 'No tienes permiso para crear órdenes de compra');

        $data = [
            'proveedor_id' => $this->input->post('proveedor_id'),
            'fecha_orden' => $this->input->post('fecha_orden') ?: date('Y-m-d'),
            'fecha_entrega_estimada' => $this->input->post('fecha_entrega_estimada'),
            'forma_pago' => $this->input->post('forma_pago') ?: 'Transferencia',
            'condiciones_pago' => $this->input->post('condiciones_pago'),
            'observaciones' => $this->input->post('observaciones'),
            'creado_por' => $this->session->userdata('id')
        ];
        
        // Validaciones
        if(empty($data['proveedor_id'])) {
            echo json_encode(['success' => false, 'message' => 'El proveedor es requerido']);
            return;
        }
        
        $result = $this->OrdenesCompraModel->crear_orden($data);
        
        if($result) {
            $orden_id = $this->db->insert_id();
            $orden = $this->OrdenesCompraModel->get_orden($orden_id);
            $this->registrar_bitacora(
                'Orden de compra creada: ' . ($orden->folio ?? $orden_id) . ' (proveedor #' . $data['proveedor_id'] . ')',
                'Compras'
            );
            echo json_encode(['success' => true, 'message' => 'Orden creada correctamente', 'orden_id' => $orden_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear orden']);
        }
    }
    
    /**
     * Actualiza una orden (AJAX)
     */
    public function editar_ajax() {
        $this->requiere_permiso('compras_ordenes_edit', 'No tienes permiso para editar órdenes de compra');

        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $data = [
            'proveedor_id' => $this->input->post('proveedor_id'),
            'fecha_orden' => $this->input->post('fecha_orden'),
            'fecha_entrega_estimada' => $this->input->post('fecha_entrega_estimada'),
            'forma_pago' => $this->input->post('forma_pago'),
            'condiciones_pago' => $this->input->post('condiciones_pago'),
            'observaciones' => $this->input->post('observaciones')
        ];
        
        $result = $this->OrdenesCompraModel->actualizar_orden($id, $data);
        
        if($result) {
            $orden = $this->OrdenesCompraModel->get_orden($id);
            $this->registrar_bitacora(
                'Orden de compra actualizada: ' . ($orden->folio ?? $id),
                'Compras'
            );
            echo json_encode(['success' => true, 'message' => 'Orden actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar orden']);
        }
    }
    
    /**
     * Elimina una orden (AJAX)
     */
    public function eliminar_ajax() {
        $this->requiere_permiso('compras_ordenes_delete', 'No tienes permiso para eliminar órdenes de compra');

        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $orden = $this->OrdenesCompraModel->get_orden($id);
        $result = $this->OrdenesCompraModel->eliminar_orden($id);
        if (!empty($result['success'])) {
            $this->registrar_bitacora(
                'Orden de compra eliminada: ' . ($orden->folio ?? $id),
                'Compras'
            );
        }
        echo json_encode($result);
    }
    
    /**
     * Obtiene insumos de un proveedor con precios (AJAX)
     */
    public function get_insumos_proveedor_ajax() {
        $proveedor_id = $this->input->post('proveedor_id');
        if(!$proveedor_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor requerido']);
            return;
        }
        
        $insumos = $this->ProveedoresModel->get_insumos_proveedor($proveedor_id);
        echo json_encode(['success' => true, 'insumos' => $insumos]);
    }
    
    /**
     * Agrega un detalle a la orden (AJAX)
     */
    public function agregar_detalle_ajax() {
        $this->requiere_permiso('compras_ordenes_edit', 'No tienes permiso para editar órdenes de compra');

        $orden_id = $this->input->post('orden_id');
        $insumo_id = $this->input->post('insumo_id');
        
        if(!$orden_id || !$insumo_id) {
            echo json_encode(['success' => false, 'message' => 'Orden e insumo requeridos']);
            return;
        }
        
        $data = [
            'insumo_id' => $insumo_id,
            'cantidad_solicitada' => $this->input->post('cantidad_solicitada'),
            'precio_unitario' => $this->input->post('precio_unitario'),
            'nombre_proveedor' => $this->input->post('nombre_proveedor'),
            'codigo_proveedor' => $this->input->post('codigo_proveedor'),
            'observaciones' => $this->input->post('observaciones')
        ];
        
        $result = $this->OrdenesCompraModel->agregar_detalle($orden_id, $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Insumo agregado a la orden']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al agregar insumo']);
        }
    }
    
    /**
     * Actualiza un detalle (AJAX)
     */
    public function actualizar_detalle_ajax() {
        $this->requiere_permiso('compras_ordenes_edit', 'No tienes permiso para editar órdenes de compra');

        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $data = [
            'cantidad_solicitada' => $this->input->post('cantidad_solicitada'),
            'precio_unitario' => $this->input->post('precio_unitario'),
            'observaciones' => $this->input->post('observaciones')
        ];
        
        $result = $this->OrdenesCompraModel->actualizar_detalle($id, $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Detalle actualizado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        }
    }
    
    /**
     * Elimina un detalle (AJAX)
     */
    public function eliminar_detalle_ajax() {
        $this->requiere_permiso('compras_ordenes_edit', 'No tienes permiso para editar órdenes de compra');

        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $result = $this->OrdenesCompraModel->eliminar_detalle($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Detalle eliminado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
    }
    
    /**
     * Elimina todos los detalles de una orden (AJAX) - Para edición
     */
    public function eliminar_todos_detalles_ajax() {
        $this->requiere_permiso('compras_ordenes_edit', 'No tienes permiso para editar órdenes de compra');

        $orden_id = $this->input->post('orden_id');
        if(!$orden_id) {
            echo json_encode(['success' => false, 'message' => 'Orden ID requerido']);
            return;
        }
        
        // Eliminar todos los detalles de la orden
        $this->db->where('orden_compra_id', $orden_id);
        $result = $this->db->delete('detalle_orden_compra');
        
        if($result) {
            // Recalcular totales (quedarán en 0)
            $this->OrdenesCompraModel->recalcular_totales($orden_id);
            echo json_encode(['success' => true, 'message' => 'Detalles eliminados']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar detalles']);
        }
    }
    
    /**
     * Cambia el estatus de una orden (AJAX)
     */
    public function cambiar_estatus_ajax() {
        $this->requiere_permiso('compras_ordenes_edit', 'No tienes permiso para cambiar el estatus de órdenes de compra');

        $id = $this->input->post('id');
        $estatus = $this->input->post('estatus');
        
        if(!$id || !$estatus) {
            echo json_encode(['success' => false, 'message' => 'ID y estatus requeridos']);
            return;
        }
        
        $user_id = $this->session->userdata('id');
        $orden = $this->OrdenesCompraModel->get_orden($id);
        $result = $this->OrdenesCompraModel->cambiar_estatus($id, $estatus, $user_id);

        if (!empty($result['success'])) {
            $this->registrar_bitacora(
                'Orden ' . ($orden->folio ?? $id) . ' cambió a estatus: ' . $estatus,
                'Compras'
            );
        }
        
        echo json_encode($result);
    }
    
    /**
     * Recibe mercancía (AJAX)
     */
    public function recibir_mercancia_ajax() {
        $this->requiere_permiso('compras_recepcion', 'No tienes permiso para recibir mercancía');

        $orden_id = $this->input->post('orden_id');
        $detalles_json = $this->input->post('detalles');
        
        if(!$orden_id || !$detalles_json) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        // Decodificar JSON de detalles
        $detalles = json_decode($detalles_json, true);
        
        if(!$detalles || !is_array($detalles)) {
            echo json_encode(['success' => false, 'message' => 'Formato de detalles inválido']);
            return;
        }
        
        $user_id = $this->session->userdata('id');
        $orden = $this->OrdenesCompraModel->get_orden($orden_id);
        $result = $this->OrdenesCompraModel->recibir_mercancia($orden_id, $detalles, $user_id);

        if (!empty($result['success'])) {
            $this->registrar_bitacora(
                'Mercancía recibida — OC ' . ($orden->folio ?? $orden_id),
                'Compras'
            );
        }
        
        echo json_encode($result);
    }
    
    /**
     * Obtiene lista de proveedores para select (AJAX)
     */
    public function get_proveedores_select_ajax() {
        $this->db->select('id, razon_social, nombre_comercial');
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('razon_social', 'ASC');
        $proveedores = $this->db->get('proveedores')->result();
        
        $opciones = [];
        foreach($proveedores as $prov) {
            $texto = $prov->razon_social;
            if($prov->nombre_comercial) {
                $texto .= ' (' . $prov->nombre_comercial . ')';
            }
            $opciones[] = [
                'id' => $prov->id,
                'text' => $texto
            ];
        }
        
        echo json_encode(['success' => true, 'proveedores' => $opciones]);
    }

    // =====================================================
    // PRE-ÓRDENES (Iteración 4: Producción → Compras)
    // =====================================================

    /**
     * Lista pre-órdenes, opcionalmente filtradas por estatus (AJAX)
     */
    public function lista_preordenes_ajax() {
        $estatus = $this->input->post('estatus') ?: 'Pendiente';
        $preordenes = $this->PreordenesModel->listar($estatus);
        echo json_encode(['success' => true, 'preordenes' => $preordenes]);
    }

    /**
     * Obtiene el detalle de una pre-orden específica (AJAX)
     */
    public function get_preorden_ajax() {
        $id = $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $preorden = $this->PreordenesModel->get_preorden($id);
        if ($preorden) {
            echo json_encode(['success' => true, 'preorden' => $preorden]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Pre-orden no encontrada']);
        }
    }

    /**
     * Lista pre-órdenes procesadas (historial) — AJAX
     */
    public function lista_preordenes_historial_ajax() {
        $preordenes = $this->PreordenesModel->listar_historial();
        echo json_encode(['success' => true, 'preordenes' => $preordenes]);
    }

    /**
     * Proveedores vinculados a un insumo (para editar/autorizar pre-orden) — AJAX
     */
    public function get_proveedores_insumo_ajax() {
        $insumo_id = $this->input->post('insumo_id');
        if (!$insumo_id) {
            echo json_encode(['success' => false, 'message' => 'Insumo requerido']);
            return;
        }

        $proveedores = $this->InsumosModel->get_proveedores_por_insumo($insumo_id);
        echo json_encode(['success' => true, 'proveedores' => $proveedores]);
    }

    /**
     * Edita una pre-orden pendiente (cantidad, proveedor, notas) — AJAX
     */
    public function editar_preorden_ajax() {
        if (!tiene_permiso('compras_autorizar_preordenes') && !tiene_permiso('compras_preordenes_edit') && !tiene_permiso('compras_ordenes_edit')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar pre-órdenes']);
            return;
        }

        $id = $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $preorden_antes = $this->PreordenesModel->get_preorden($id);
        $result = $this->PreordenesModel->actualizar_pendiente($id, [
            'cantidad_solicitada'   => $this->input->post('cantidad_solicitada'),
            'proveedor_sugerido_id' => $this->input->post('proveedor_sugerido_id'),
            'notas'                 => $this->input->post('notas'),
        ]);

        if (!empty($result['success'])) {
            $this->registrar_bitacora(
                'Pre-orden editada: ' . ($preorden_antes->folio ?? $id),
                'Compras'
            );
        }
        echo json_encode($result);
    }

    /**
     * Autoriza una pre-orden y genera la Orden de Compra real en Borrador (AJAX)
     * Requiere el permiso 'compras_autorizar_preordenes'.
     */
    public function autorizar_preorden_ajax() {
        if (!tiene_permiso('compras_autorizar_preordenes')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para autorizar pre-órdenes de compra']);
            return;
        }

        $id = $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $cantidad_aprobada = $this->input->post('cantidad_aprobada') ?: null;
        $proveedor_id = $this->input->post('proveedor_id') ?: null;
        $user_id = $this->session->userdata('id');

        $preorden = $this->PreordenesModel->get_preorden($id);
        $result = $this->PreordenesModel->aprobar($id, $user_id, $cantidad_aprobada, $proveedor_id);
        if (!empty($result['success'])) {
            $oc_folio = '';
            if (!empty($result['orden_compra_id'])) {
                $oc = $this->OrdenesCompraModel->get_orden($result['orden_compra_id']);
                $oc_folio = $oc->folio ?? $result['orden_compra_id'];
            }
            $this->registrar_bitacora(
                'Pre-orden autorizada: ' . ($preorden->folio ?? $id) . ' → OC ' . $oc_folio,
                'Compras'
            );
        }
        echo json_encode($result);
    }

    /**
     * Rechaza una pre-orden (AJAX)
     * Requiere el permiso 'compras_autorizar_preordenes'.
     */
    public function rechazar_preorden_ajax() {
        if (!tiene_permiso('compras_autorizar_preordenes')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para rechazar pre-órdenes de compra']);
            return;
        }

        $id = $this->input->post('id');
        $motivo = $this->input->post('motivo');
        if (!$id || !$motivo) {
            echo json_encode(['success' => false, 'message' => 'ID y motivo de rechazo son requeridos']);
            return;
        }

        $user_id = $this->session->userdata('id');
        $preorden = $this->PreordenesModel->get_preorden($id);
        $result = $this->PreordenesModel->rechazar($id, $user_id, $motivo);
        if (!empty($result['success'])) {
            $this->registrar_bitacora(
                'Pre-orden rechazada: ' . ($preorden->folio ?? $id) . ' — ' . $motivo,
                'Compras'
            );
        }
        echo json_encode($result);
    }

    // =====================================================
    // SIMULACIÓN DE CORREO Y REPORTES (Demo presentación)
    // =====================================================

    /**
     * Genera vista previa de correo al proveedor (simulación, NO envía SMTP).
     */
    public function simular_correo_ajax() {
        $this->requiere_permiso('compras_ordenes_consult', 'No tienes permiso para simular envío de órdenes');

        $id = $this->input->post('id');
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }

        $orden = $this->OrdenesCompraModel->get_orden($id);
        $resultado = $this->OrdenesCompraModel->construir_correo_simulado($id);
        if (!empty($resultado['success'])) {
            $this->registrar_bitacora(
                'Correo simulado al proveedor — OC ' . ($orden->folio ?? $id),
                'Compras'
            );
        }
        echo json_encode($resultado);
    }

    /**
     * Datos del reporte de compras por periodo (AJAX).
     */
    public function reporte_ajax() {
        if (!tiene_permiso('reportes_compras') && !tiene_permiso('compras_ordenes_consult')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para ver reportes de compras']);
            return;
        }

        $periodo = $this->input->post('periodo') ?: 'mes';
        $fecha_inicio = $this->input->post('fecha_inicio');
        $fecha_fin = $this->input->post('fecha_fin');

        $reporte = $this->OrdenesCompraModel->get_reporte_compras($periodo, $fecha_inicio, $fecha_fin);
        echo json_encode(['success' => true, 'reporte' => $reporte]);
    }

    /**
     * Exporta el reporte de compras a CSV.
     */
    public function exportar_reporte_csv() {
        if (!tiene_permiso('reportes_compras') && !tiene_permiso('compras_ordenes_consult')) {
            show_error('No tienes permiso para exportar reportes de compras', 403);
            return;
        }

        $periodo = $this->input->get('periodo') ?: 'mes';
        $fecha_inicio = $this->input->get('fecha_inicio');
        $fecha_fin = $this->input->get('fecha_fin');

        $reporte = $this->OrdenesCompraModel->get_reporte_compras($periodo, $fecha_inicio, $fecha_fin);

        $filename = 'reporte_compras_' . $reporte['fecha_inicio'] . '_' . $reporte['fecha_fin'] . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, ['Reporte de Compras']);
        fputcsv($out, ['Periodo', $reporte['fecha_inicio'] . ' — ' . $reporte['fecha_fin']]);
        fputcsv($out, ['Monto gastado', '$' . number_format($reporte['monto_gastado'], 2)]);
        fputcsv($out, ['OC recibidas', $reporte['oc_recibidas']]);
        fputcsv($out, ['OC pendientes', $reporte['oc_pendientes']]);
        fputcsv($out, []);

        fputcsv($out, ['Desglose por proveedor']);
        fputcsv($out, ['Proveedor', 'Órdenes', 'Monto total']);
        foreach ($reporte['por_proveedor'] as $row) {
            fputcsv($out, [
                $row->razon_social,
                $row->num_ordenes,
                '$' . number_format((float) $row->monto_total, 2),
            ]);
        }
        fputcsv($out, []);

        fputcsv($out, ['Detalle de órdenes']);
        fputcsv($out, ['Folio', 'Fecha', 'Proveedor', 'Total', 'Estatus']);
        foreach ($reporte['ordenes'] as $oc) {
            fputcsv($out, [
                $oc->folio,
                $oc->fecha_orden,
                $oc->razon_social,
                '$' . number_format((float) $oc->total, 2),
                $oc->estatus,
            ]);
        }

        $this->registrar_bitacora(
            'Reporte de compras exportado CSV (' . $reporte['fecha_inicio'] . ' — ' . $reporte['fecha_fin'] . ')',
            'Compras'
        );

        fclose($out);
        exit;
    }

    public function listar_comentarios_ajax() {
        $this->requiere_permiso('compras_ordenes_consult', 'No tienes permiso para consultar órdenes de compra');

        $orden_id = (int) $this->input->post('orden_id');
        if (!$orden_id) {
            echo json_encode(['success' => false, 'message' => 'Orden requerida']);
            return;
        }
        echo json_encode([
            'success' => true,
            'comentarios' => $this->OrdenesCompraModel->get_comentarios($orden_id),
        ]);
    }

    public function agregar_comentario_ajax() {
        if (!tiene_permiso('compras_documentos') && !tiene_permiso('compras_ordenes_edit')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para agregar comentarios']);
            return;
        }

        $orden_id = (int) $this->input->post('orden_id');
        $comentario = trim((string) $this->input->post('comentario'));
        if (!$orden_id || $comentario === '') {
            echo json_encode(['success' => false, 'message' => 'Comentario requerido']);
            return;
        }
        $ok = $this->OrdenesCompraModel->agregar_comentario($orden_id, $comentario, $this->session->userdata('id'));
        if ($ok) {
            $orden = $this->OrdenesCompraModel->get_orden($orden_id);
            $this->registrar_bitacora(
                'Comentario agregado en OC ' . ($orden->folio ?? $orden_id),
                'Compras'
            );
        }
        echo json_encode([
            'success' => $ok,
            'message' => $ok ? 'Comentario agregado' : 'No se pudo guardar el comentario',
        ]);
    }

    public function listar_documentos_ajax() {
        $this->requiere_permiso('compras_ordenes_consult', 'No tienes permiso para consultar órdenes de compra');

        $orden_id = (int) $this->input->post('orden_id');
        if (!$orden_id) {
            echo json_encode(['success' => false, 'message' => 'Orden requerida']);
            return;
        }
        echo json_encode([
            'success' => true,
            'documentos' => $this->OrdenesCompraModel->get_documentos($orden_id),
        ]);
    }

    public function subir_documento_ajax() {
        if (!tiene_permiso('compras_documentos') && !tiene_permiso('compras_ordenes_edit')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para adjuntar documentos']);
            return;
        }

        $orden_id = (int) $this->input->post('orden_id');
        if (!$orden_id) {
            echo json_encode(['success' => false, 'message' => 'Orden requerida']);
            return;
        }
        if (empty($_FILES['archivo']['name'])) {
            echo json_encode(['success' => false, 'message' => 'Seleccione un archivo']);
            return;
        }

        $upload_path = './uploads/ordenes_compra/' . $orden_id . '/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }

        $config = [
            'upload_path'   => $upload_path,
            'allowed_types' => 'pdf|jpg|jpeg|png|webp|doc|docx|xls|xlsx',
            'max_size'      => 10240,
            'encrypt_name'  => true,
        ];
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('archivo')) {
            echo json_encode(['success' => false, 'message' => strip_tags($this->upload->display_errors('', ''))]);
            return;
        }

        $upload_data = $this->upload->data();
        $tipo = $this->input->post('tipo') ?: 'Otro';
        $tipos_validos = ['Factura', 'Nota de remisión', 'Cotización', 'Otro'];
        if (!in_array($tipo, $tipos_validos, true)) {
            $tipo = 'Otro';
        }

        $ok = $this->OrdenesCompraModel->agregar_documento($orden_id, [
            'tipo'           => $tipo,
            'nombre_archivo' => $upload_data['orig_name'],
            'ruta'           => 'uploads/ordenes_compra/' . $orden_id . '/' . $upload_data['file_name'],
            'mime_type'      => $upload_data['file_type'],
            'tamano_bytes'   => (int) ($upload_data['file_size'] * 1024),
            'notas'          => $this->input->post('notas'),
            'subido_por'     => $this->session->userdata('id'),
        ]);

        if ($ok) {
            $orden = $this->OrdenesCompraModel->get_orden($orden_id);
            $this->registrar_bitacora(
                'Documento adjuntado (' . $tipo . ') en OC ' . ($orden->folio ?? $orden_id) . ': ' . $upload_data['orig_name'],
                'Compras'
            );
        }

        echo json_encode([
            'success' => (bool) $ok,
            'message' => $ok ? 'Documento cargado correctamente' : 'Error al registrar el documento',
        ]);
    }

    public function eliminar_documento_ajax() {
        if (!tiene_permiso('compras_documentos') && !tiene_permiso('compras_ordenes_edit')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar documentos']);
            return;
        }

        $id = (int) $this->input->post('id');
        $doc = $this->OrdenesCompraModel->get_documento($id);
        if (!$doc) {
            echo json_encode(['success' => false, 'message' => 'Documento no encontrado']);
            return;
        }
        $ruta = FCPATH . $doc->ruta;
        if (is_file($ruta)) {
            @unlink($ruta);
        }
        $ok = $this->OrdenesCompraModel->eliminar_documento($id);
        if ($ok) {
            $this->registrar_bitacora(
                'Documento eliminado de OC #' . $doc->orden_compra_id . ': ' . $doc->nombre_archivo,
                'Compras'
            );
        }
        echo json_encode([
            'success' => (bool) $ok,
            'message' => $ok ? 'Documento eliminado' : 'Error al eliminar',
        ]);
    }

    public function get_pagos_orden_ajax() {
        $this->requiere_permiso('compras_ordenes_consult', 'No tienes permiso para consultar pagos');

        $orden_id = (int) $this->input->post('orden_id');
        if (!$orden_id) {
            echo json_encode(['success' => false, 'message' => 'Orden requerida']);
            return;
        }
        echo json_encode([
            'success' => true,
            'pagos' => $this->OrdenesCompraModel->get_pagos($orden_id),
        ]);
    }

    public function registrar_pago_ajax() {
        $this->requiere_permiso('compras_pagos', 'No tienes permiso para registrar pagos de compras');

        $orden_id = (int) $this->input->post('orden_id');
        $result = $this->OrdenesCompraModel->registrar_pago($orden_id, [
            'monto' => $this->input->post('monto'),
            'fecha_pago' => $this->input->post('fecha_pago'),
            'metodo_pago' => $this->input->post('metodo_pago'),
            'referencia' => $this->input->post('referencia'),
            'notas' => $this->input->post('notas'),
            'registrado_por' => $this->session->userdata('id'),
        ]);

        if (!empty($result['success'])) {
            $orden = $this->OrdenesCompraModel->get_orden($orden_id);
            $this->registrar_bitacora(
                'Pago registrado en OC ' . ($orden->folio ?? $orden_id) . ' — $' . number_format((float)$this->input->post('monto'), 2),
                'Compras'
            );
        }
        echo json_encode($result);
    }

    public function marcar_pagado_ajax() {
        $this->requiere_permiso('compras_pagos', 'No tienes permiso para registrar pagos de compras');

        $orden_id = (int) $this->input->post('orden_id');
        $result = $this->OrdenesCompraModel->marcar_pagado_completo($orden_id, [
            'fecha_pago' => $this->input->post('fecha_pago') ?: date('Y-m-d'),
            'metodo_pago' => $this->input->post('metodo_pago'),
            'referencia' => $this->input->post('referencia'),
            'notas' => $this->input->post('notas') ?: 'Marcado como pagado',
            'registrado_por' => $this->session->userdata('id'),
        ]);

        if (!empty($result['success'])) {
            $orden = $this->OrdenesCompraModel->get_orden($orden_id);
            $this->registrar_bitacora(
                'OC ' . ($orden->folio ?? $orden_id) . ' marcada como pagada',
                'Compras'
            );
        }
        echo json_encode($result);
    }
}
