<?php
/**
 * Proveedores - Controlador de gestión de proveedores
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Proveedores extends MY_Controller {
    
    protected $modulo = 'Proveedores';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Compras/ProveedoresModel');
        $this->load->model('Compras/InsumosModel');
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Catálogo de Proveedores';
        $this->viewData['headTitle'] = 'Gestión de Proveedores';
        $this->viewData['breadcrumb'] = 'Inicio > Compras > Proveedores';
        
        // Obtener estadísticas
        $stats = $this->ProveedoresModel->get_estadisticas();
        $this->viewData['response'] = ['stats' => $stats];
        
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'compras/proveedores/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Lista de proveedores para DataTables (AJAX)
     */
    public function lista_ajax() {
        $list = $this->ProveedoresModel->get_datatables();
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $proveedor) {
            $no++;
            $row = array();
            
            // Código
            $row[] = $proveedor->codigo;
            
            // Razón Social + Nombre Comercial
            $nombre = '<strong>' . $proveedor->razon_social . '</strong>';
            if($proveedor->nombre_comercial) {
                $nombre .= '<br><small class="text-muted">' . $proveedor->nombre_comercial . '</small>';
            }
            $row[] = $nombre;
            
            // RFC
            $row[] = $proveedor->rfc;
            
            // Contacto (teléfono + email)
            $contacto = '';
            if($proveedor->telefono) {
                $contacto .= '<i class="fas fa-phone"></i> ' . $proveedor->telefono;
            }
            if($proveedor->email) {
                $contacto .= '<br><i class="fas fa-envelope"></i> ' . $proveedor->email;
            }
            $row[] = $contacto ?: '-';
            
            // Ciudad, Estado
            $ubicacion = '';
            if($proveedor->ciudad) {
                $ubicacion = $proveedor->ciudad;
                if($proveedor->estado) {
                    $ubicacion .= ', ' . $proveedor->estado;
                }
            }
            $row[] = $ubicacion ?: '-';
            
            // Estatus
            if($proveedor->estatus == 'Activo') {
                $row[] = '<span class="badge bg-success">Activo</span>';
            } else {
                $row[] = '<span class="badge bg-secondary">Inactivo</span>';
            }
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-info" onclick="mostrarModalInsumos('.$proveedor->id.')" title="Insumos">
                    <i class="fas fa-boxes"></i>
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="mostrarModalEditar('.$proveedor->id.')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProveedor('.$proveedor->id.')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>';
            
            $row[] = $acciones;
            
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->ProveedoresModel->count_all(),
            "recordsFiltered" => $this->ProveedoresModel->count_filtered(),
            "data" => $data,
        );

        echo json_encode($output);
    }
    
    /**
     * Obtiene un proveedor específico (AJAX)
     */
    public function get_proveedor_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $proveedor = $this->ProveedoresModel->get_proveedor($id);
        if($proveedor) {
            echo json_encode(['success' => true, 'proveedor' => $proveedor]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Proveedor no encontrado']);
        }
    }
    
    /**
     * Crea un nuevo proveedor (AJAX)
     */
    public function crear_ajax() {
        $data = [
            'codigo' => $this->input->post('codigo'),
            'razon_social' => $this->input->post('razon_social'),
            'nombre_comercial' => $this->input->post('nombre_comercial'),
            'rfc' => $this->input->post('rfc'),
            'tipo_proveedor' => $this->input->post('tipo_proveedor') ?: 'Mixto',
            'contacto_principal' => $this->input->post('contacto_principal'),
            'telefono' => $this->input->post('telefono'),
            'telefono_alternativo' => $this->input->post('telefono_alternativo'),
            'email' => $this->input->post('email'),
            'sitio_web' => $this->input->post('sitio_web'),
            'direccion' => $this->input->post('direccion'),
            'ciudad' => $this->input->post('ciudad'),
            'estado' => $this->input->post('estado'),
            'codigo_postal' => $this->input->post('codigo_postal'),
            'pais' => $this->input->post('pais') ?: 'México',
            'dias_credito' => $this->input->post('dias_credito') ?: 0,
            'limite_credito' => $this->input->post('limite_credito') ?: 0,
            'banco' => $this->input->post('banco'),
            'cuenta_bancaria' => $this->input->post('cuenta_bancaria'),
            'observaciones' => $this->input->post('observaciones'),
            'calificacion' => 3,
            'estatus' => 'Activo'
        ];
        
        // Validaciones
        if(empty($data['razon_social'])) {
            echo json_encode(['success' => false, 'message' => 'La razón social es requerida']);
            return;
        }
        
        if(empty($data['rfc'])) {
            echo json_encode(['success' => false, 'message' => 'El RFC es requerido']);
            return;
        }
        
        $result = $this->ProveedoresModel->crear_proveedor($data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Proveedor creado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear proveedor']);
        }
    }
    
    /**
     * Actualiza un proveedor (AJAX)
     */
    public function editar_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $data = [
            'codigo' => $this->input->post('codigo'),
            'razon_social' => $this->input->post('razon_social'),
            'nombre_comercial' => $this->input->post('nombre_comercial'),
            'rfc' => $this->input->post('rfc'),
            'tipo_proveedor' => $this->input->post('tipo_proveedor'),
            'contacto_principal' => $this->input->post('contacto_principal'),
            'telefono' => $this->input->post('telefono'),
            'telefono_alternativo' => $this->input->post('telefono_alternativo'),
            'email' => $this->input->post('email'),
            'sitio_web' => $this->input->post('sitio_web'),
            'direccion' => $this->input->post('direccion'),
            'ciudad' => $this->input->post('ciudad'),
            'estado' => $this->input->post('estado'),
            'codigo_postal' => $this->input->post('codigo_postal'),
            'pais' => $this->input->post('pais'),
            'dias_credito' => $this->input->post('dias_credito'),
            'limite_credito' => $this->input->post('limite_credito'),
            'banco' => $this->input->post('banco'),
            'cuenta_bancaria' => $this->input->post('cuenta_bancaria'),
            'observaciones' => $this->input->post('observaciones'),
            'estatus' => $this->input->post('estatus')
        ];
        
        $result = $this->ProveedoresModel->actualizar_proveedor($id, $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Proveedor actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar proveedor']);
        }
    }
    
    /**
     * Elimina un proveedor (AJAX)
     */
    public function eliminar_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $result = $this->ProveedoresModel->eliminar_proveedor($id);
        echo json_encode($result);
    }
    
    /**
     * Obtiene insumos de un proveedor (AJAX)
     */
    public function get_insumos_proveedor_ajax() {
        $id = $this->input->post('proveedor_id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $insumos = $this->ProveedoresModel->get_insumos_proveedor($id);
        echo json_encode(['success' => true, 'insumos' => $insumos]);
    }
    
    /**
     * Agrega un insumo a un proveedor (AJAX)
     */
    public function agregar_insumo_ajax() {
        $proveedor_id = $this->input->post('proveedor_id');
        $insumo_id = $this->input->post('insumo_id');
        
        if(!$proveedor_id || !$insumo_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor e insumo requeridos']);
            return;
        }
        
        $data = [
            'precio_compra' => $this->input->post('precio_compra'),
            'tiempo_entrega_dias' => $this->input->post('tiempo_entrega_dias') ?: 0,
            'cantidad_minima' => $this->input->post('cantidad_minima') ?: 1,
            'codigo_proveedor' => $this->input->post('codigo_proveedor'),
            'observaciones' => $this->input->post('observaciones')
        ];
        
        $result = $this->ProveedoresModel->agregar_insumo($proveedor_id, $insumo_id, $data);
        echo json_encode($result);
    }
    
    /**
     * Actualiza precio de un insumo del proveedor (AJAX)
     */
    public function actualizar_precio_insumo_ajax() {
        $proveedor_id = $this->input->post('proveedor_id');
        $insumo_id = $this->input->post('insumo_id');
        
        if(!$proveedor_id || !$insumo_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor e insumo requeridos']);
            return;
        }
        
        $data = [
            'precio_compra' => $this->input->post('precio_compra'),
            'tiempo_entrega_dias' => $this->input->post('tiempo_entrega_dias'),
            'cantidad_minima' => $this->input->post('cantidad_minima'),
            'codigo_proveedor' => $this->input->post('codigo_proveedor'),
            'observaciones' => $this->input->post('observaciones')
        ];
        
        $result = $this->ProveedoresModel->actualizar_precio_insumo($proveedor_id, $insumo_id, $data);
        echo json_encode($result);
    }
    
    /**
     * Elimina un insumo de un proveedor (AJAX)
     */
    public function eliminar_insumo_ajax() {
        $proveedor_id = $this->input->post('proveedor_id');
        $insumo_id = $this->input->post('insumo_id');
        
        if(!$proveedor_id || !$insumo_id) {
            echo json_encode(['success' => false, 'message' => 'Proveedor e insumo requeridos']);
            return;
        }
        
        $result = $this->ProveedoresModel->eliminar_insumo($proveedor_id, $insumo_id);
        echo json_encode($result);
    }
    
    /**
     * Obtiene lista de insumos para select (AJAX)
     */
    public function get_insumos_select_ajax() {
        $insumos = $this->InsumosModel->get_all_insumos(['estatus' => 'Activo']);
        
        $opciones = [];
        foreach($insumos as $ins) {
            $opciones[] = [
                'id' => $ins->id,
                'text' => $ins->codigo . ' - ' . $ins->nombre_tecnico . ' (' . $ins->unidad_medida . ')'
            ];
        }
        
        echo json_encode(['success' => true, 'insumos' => $opciones]);
    }
}
