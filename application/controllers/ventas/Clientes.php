<?php
/**
 * Clientes Controller
 * 
 * Gestión de clientes del sistema CRM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Clientes extends MY_Controller {
    
    protected $modulo = 'Clientes (CRM)';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Ventas/ClientesModel');
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Clientes';
        $this->viewData['headTitle'] = 'Gestión de Clientes';
        $this->viewData['breadcrumb'] = 'Inicio > CRM Ventas > Clientes';
        
        // Obtener estadísticas
        $stats = $this->ClientesModel->get_estadisticas();
        $this->viewData['response'] = ['stats' => $stats];
        
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'ventas/clientes/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Lista de clientes para DataTables (AJAX)
     */
    public function lista_ajax() {
        $list = $this->ClientesModel->get_datatables();
        $data = [];
        
        foreach ($list as $cliente) {
            $row = [];
            
            // Código
            $row[] = $cliente->codigo;
            
            // Razón Social / Nombre Comercial
            $nombre = '<strong>' . $cliente->razon_social . '</strong>';
            if($cliente->nombre_comercial) {
                $nombre .= '<br><small class="text-muted">' . $cliente->nombre_comercial . '</small>';
            }
            if((int)($cliente->total_ordenes ?? 0) > 0) {
                $nombre .= '<br><span class="badge bg-light text-dark mt-1" style="font-size:0.7rem;"><i class="fas fa-shopping-cart"></i> ' . (int)$cliente->total_ordenes . ' órdenes</span>';
            }
            $row[] = $nombre;
            
            // RFC
            $row[] = $cliente->rfc;
            
            // Contacto
            $contacto = '';
            if($cliente->telefono) {
                $contacto .= '<i class="fas fa-phone"></i> ' . $cliente->telefono . '<br>';
            }
            if($cliente->email) {
                $contacto .= '<i class="fas fa-envelope"></i> ' . $cliente->email;
            }
            $row[] = $contacto ?: '<span class="text-muted">Sin datos</span>';

            // Ciudad
            $ubicacion = '';
            if($cliente->ciudad) {
                $ubicacion = $cliente->ciudad;
                if($cliente->estado) {
                    $ubicacion .= ', ' . $cliente->estado;
                }
            }
            $row[] = $ubicacion ?: '<span class="text-muted">—</span>';

            // Saldo pendiente
            $saldo = (float) ($cliente->saldo_pendiente ?? 0);
            if($saldo > 0) {
                $row[] = '<span class="text-danger fw-semibold">$' . number_format($saldo, 2) . '</span>';
            } else {
                $row[] = '<span class="text-muted">$0.00</span>';
            }
            
            // Tipo de cliente
            $tipo_badges = [
                'Regular' => 'primary',
                'Mostrador' => 'secondary',
                'Gobierno' => 'success',
                'Licitación' => 'warning',
                'Distribuidor' => 'info'
            ];
            $badge_color = $tipo_badges[$cliente->tipo_cliente] ?? 'secondary';
            $row[] = '<span class="badge bg-' . $badge_color . '">' . $cliente->tipo_cliente . '</span>';
            
            // Estatus
            $estatus_badges = [
                'Activo' => 'success',
                'Inactivo' => 'secondary',
                'Suspendido' => 'danger'
            ];
            $badge_color = $estatus_badges[$cliente->estatus] ?? 'secondary';
            $row[] = '<span class="badge bg-' . $badge_color . '">' . $cliente->estatus . '</span>';
            
            // Acciones
            $acciones = '<div class="btn-acciones-crm">
            <button type="button" class="btn btn-sm btn-info" onclick="verCliente('.$cliente->id.')" title="Ver detalles">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-sm btn-primary" onclick="editarCliente('.$cliente->id.')" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarCliente('.$cliente->id.')" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>
            </div>';
            
            $row[] = $acciones;
            
            $data[] = $row;
        }
        
        $output = [
            'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
            'recordsTotal' => $this->ClientesModel->count_all(),
            'recordsFiltered' => $this->ClientesModel->count_filtered(),
            'data' => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Obtiene un cliente (AJAX)
     */
    public function get_cliente_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $cliente = $this->ClientesModel->get_cliente($id);
        
        if($cliente) {
            echo json_encode(['success' => true, 'cliente' => $cliente]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        }
    }
    
    /**
     * Crea un nuevo cliente (AJAX)
     */
    public function crear_ajax() {
        $data = [
            'razon_social' => $this->input->post('razon_social'),
            'nombre_comercial' => $this->input->post('nombre_comercial'),
            'rfc' => strtoupper($this->input->post('rfc')),
            'regimen_fiscal' => $this->input->post('regimen_fiscal'),
            'contacto_nombre' => $this->input->post('contacto_nombre'),
            'telefono' => $this->input->post('telefono'),
            'email' => $this->input->post('email'),
            'calle' => $this->input->post('calle'),
            'numero_exterior' => $this->input->post('numero_exterior'),
            'numero_interior' => $this->input->post('numero_interior'),
            'colonia' => $this->input->post('colonia'),
            'ciudad' => $this->input->post('ciudad'),
            'estado' => $this->input->post('estado'),
            'codigo_postal' => $this->input->post('codigo_postal'),
            'limite_credito' => $this->input->post('limite_credito') ?: 0,
            'dias_credito' => $this->input->post('dias_credito') ?: 0,
            'tipo_cliente' => $this->input->post('tipo_cliente'),
            'estatus' => $this->input->post('estatus')
        ];
        
        $result = $this->ClientesModel->crear_cliente($data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Cliente creado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear cliente']);
        }
    }
    
    /**
     * Actualiza un cliente (AJAX)
     */
    public function editar_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $data = [
            'razon_social' => $this->input->post('razon_social'),
            'nombre_comercial' => $this->input->post('nombre_comercial'),
            'rfc' => strtoupper($this->input->post('rfc')),
            'regimen_fiscal' => $this->input->post('regimen_fiscal'),
            'contacto_nombre' => $this->input->post('contacto_nombre'),
            'telefono' => $this->input->post('telefono'),
            'email' => $this->input->post('email'),
            'calle' => $this->input->post('calle'),
            'numero_exterior' => $this->input->post('numero_exterior'),
            'numero_interior' => $this->input->post('numero_interior'),
            'colonia' => $this->input->post('colonia'),
            'ciudad' => $this->input->post('ciudad'),
            'estado' => $this->input->post('estado'),
            'codigo_postal' => $this->input->post('codigo_postal'),
            'limite_credito' => $this->input->post('limite_credito') ?: 0,
            'dias_credito' => $this->input->post('dias_credito') ?: 0,
            'tipo_cliente' => $this->input->post('tipo_cliente'),
            'estatus' => $this->input->post('estatus')
        ];
        
        $result = $this->ClientesModel->actualizar_cliente($id, $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar cliente']);
        }
    }
    
    /**
     * Elimina un cliente (AJAX)
     */
    public function eliminar_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $result = $this->ClientesModel->eliminar_cliente($id);
        echo json_encode($result);
    }
    
    /**
     * Obtiene clientes para select (AJAX)
     */
    public function get_clientes_select_ajax() {
        $clientes = $this->ClientesModel->get_clientes_select();
        echo json_encode(['success' => true, 'clientes' => $clientes]);
    }

    /**
     * Órdenes de venta de un cliente (AJAX)
     */
    public function get_ordenes_cliente_ajax() {
        $cliente_id = $this->input->post('cliente_id');
        if(!$cliente_id) {
            echo json_encode(['success' => false, 'message' => 'Cliente requerido']);
            return;
        }

        $ordenes = $this->ClientesModel->get_ordenes_cliente($cliente_id);
        echo json_encode(['success' => true, 'ordenes' => $ordenes]);
    }
}
