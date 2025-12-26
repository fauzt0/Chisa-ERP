<?php
/**
 * OrdenesCompra - Controlador de gestión de órdenes de compra
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class OrdenesCompra extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Compras/OrdenesCompraModel');
        $this->load->model('Compras/ProveedoresModel');
        $this->load->model('Compras/InsumosModel');
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
        $this->viewData['response'] = ['stats' => $stats];
        
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
        $no = $_POST['start'];

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
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-info" onclick="verOrden('.$orden->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>';
            
            // Solo editar si está en Borrador
            if($orden->estatus == 'Borrador') {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-primary" onclick="editarOrden('.$orden->id.')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-success" onclick="aprobarOrden('.$orden->id.')" title="Aprobar y Enviar">
                    <i class="fas fa-check"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarOrden('.$orden->id.')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>';
            }
            
            // Recibir mercancía si está Enviada, Confirmada o En Tránsito
            if(in_array($orden->estatus, ['Enviada', 'Confirmada', 'En Tránsito', 'Recibida Parcial'])) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-warning" onclick="recibirMercancia('.$orden->id.')" title="Recibir Mercancía">
                    <i class="fas fa-truck-loading"></i>
                </button>';
            }
            
            $row[] = $acciones;
            
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
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
     * Crea una nueva orden (AJAX)
     */
    public function crear_ajax() {
        $data = [
            'proveedor_id' => $this->input->post('proveedor_id'),
            'fecha_orden' => $this->input->post('fecha_orden') ?: date('Y-m-d'),
            'fecha_entrega_estimada' => $this->input->post('fecha_entrega_estimada'),
            'forma_pago' => $this->input->post('forma_pago') ?: 'Transferencia',
            'condiciones_pago' => $this->input->post('condiciones_pago'),
            'observaciones' => $this->input->post('observaciones'),
            'creado_por' => $this->session->userdata('user_id')
        ];
        
        // Validaciones
        if(empty($data['proveedor_id'])) {
            echo json_encode(['success' => false, 'message' => 'El proveedor es requerido']);
            return;
        }
        
        $result = $this->OrdenesCompraModel->crear_orden($data);
        
        if($result) {
            $orden_id = $this->db->insert_id();
            echo json_encode(['success' => true, 'message' => 'Orden creada correctamente', 'orden_id' => $orden_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear orden']);
        }
    }
    
    /**
     * Actualiza una orden (AJAX)
     */
    public function editar_ajax() {
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
            echo json_encode(['success' => true, 'message' => 'Orden actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar orden']);
        }
    }
    
    /**
     * Elimina una orden (AJAX)
     */
    public function eliminar_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $result = $this->OrdenesCompraModel->eliminar_orden($id);
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
        $id = $this->input->post('id');
        $estatus = $this->input->post('estatus');
        
        if(!$id || !$estatus) {
            echo json_encode(['success' => false, 'message' => 'ID y estatus requeridos']);
            return;
        }
        
        $user_id = $this->session->userdata('user_id');
        $result = $this->OrdenesCompraModel->cambiar_estatus($id, $estatus, $user_id);
        
        echo json_encode($result);
    }
    
    /**
     * Recibe mercancía (AJAX)
     */
    public function recibir_mercancia_ajax() {
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
        
        $user_id = $this->session->userdata('user_id');
        $result = $this->OrdenesCompraModel->recibir_mercancia($orden_id, $detalles, $user_id);
        
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
}
