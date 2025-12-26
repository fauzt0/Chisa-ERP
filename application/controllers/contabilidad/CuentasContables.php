<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CuentasContables extends CI_Controller {
    
    public $viewData = [];
    public $outputData = [];
    
    public function __construct() {
        parent::__construct();
        $this->load->library("Init_controller");
        $this->load->model('Contabilidad/ContabilidadModel');
        
        // General viewdata for view files
        $this->viewData = [
            'success'     => true,
            'statusCode'  => get_status_code_by_result('emptyresult'),
            'message'     => 'Respuesta sin contenido',  
            'error'       => '',
            'pageTitle'   => '',      
            'headTitle'   => '',   
            'pageView'    => '',
            'pageScript'  => '',
            'breadcrumb'  => '',
            'validate'    => '',
            'response'    => [],
        ];     

        $this->outputData = [
            'success'     => true,  
            'statusCode'  => get_status_code_by_result('emptyresult'),
            'message'     => 'Respuesta sin contenido',
            'error'       => '',      
            'response'    => [],
        ];
    }
    
    /**
     * Vista principal del catálogo de cuentas
     */
    public function index() {
        setViewSuccess('Catálogo de Cuentas cargado correctamente');
        $this->viewData['pageTitle'] = 'Catálogo de Cuentas';
        $this->viewData['headTitle'] = 'Catálogo de Cuentas Contables';
        $this->viewData['breadcrumb'] = 'Inicio > Contabilidad > Catálogo de Cuentas';
        
        // Cargar vista
        $this->viewData['pageView'] = 'contabilidad/cuentas/main';
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene cuentas para DataTable (AJAX)
     */
    public function lista_ajax() {
        $filtros = [
            'tipo_cuenta' => $this->input->post('filtro_tipo'),
            'estatus' => $this->input->post('filtro_estatus'),
        ];
        
        $cuentas = $this->ContabilidadModel->get_cuentas($filtros);
        
        $data = [];
        foreach($cuentas as $cuenta) {
            $row = [];
            
            // Código
            $row[] = '<strong>' . $cuenta->codigo . '</strong>';
            
            // Nombre con indentación según nivel
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $cuenta->nivel - 1);
            $row[] = $indent . $cuenta->nombre;
            
            // Tipo
            $badge_tipo = '';
            switch($cuenta->tipo_cuenta) {
                case 'Activo': $badge_tipo = 'primary'; break;
                case 'Pasivo': $badge_tipo = 'danger'; break;
                case 'Capital': $badge_tipo = 'success'; break;
                case 'Ingresos': $badge_tipo = 'info'; break;
                case 'Egresos': $badge_tipo = 'warning'; break;
                case 'Costos': $badge_tipo = 'secondary'; break;
            }
            $row[] = '<span class="badge bg-'.$badge_tipo.'">'.$cuenta->tipo_cuenta.'</span>';
            
            // Naturaleza
            $row[] = $cuenta->naturaleza;
            
            // Afectable
            $row[] = $cuenta->es_afectable ? 
                '<span class="badge bg-success">Sí</span>' : 
                '<span class="badge bg-secondary">No</span>';
            
            // Estatus
            $badge_estatus = $cuenta->estatus == 'Activa' ? 'success' : 'secondary';
            $row[] = '<span class="badge bg-'.$badge_estatus.'">'.$cuenta->estatus.'</span>';
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-info" onclick="verCuenta('.$cuenta->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="editarCuenta('.$cuenta->id.')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>';
            
            if($cuenta->es_afectable) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-secondary" onclick="verMovimientos('.$cuenta->id.')" title="Ver Movimientos">
                    <i class="fas fa-list"></i>
                </button>';
            }
            
            $row[] = $acciones;
            
            $data[] = $row;
        }
        
        $output = [
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            "recordsTotal" => count($cuentas),
            "recordsFiltered" => count($cuentas),
            "data" => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Obtiene una cuenta por ID (AJAX)
     */
    public function get_cuenta_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $cuenta = $this->ContabilidadModel->get_cuenta($id);
        
        if($cuenta) {
            echo json_encode(['success' => true, 'cuenta' => $cuenta]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cuenta no encontrada']);
        }
    }
    
    /**
     * Crea una nueva cuenta (AJAX)
     */
    public function crear_ajax() {
        $data = [
            'codigo' => $this->input->post('codigo'),
            'nombre' => $this->input->post('nombre'),
            'tipo_cuenta' => $this->input->post('tipo_cuenta'),
            'subtipo' => $this->input->post('subtipo'),
            'naturaleza' => $this->input->post('naturaleza'),
            'nivel' => $this->input->post('nivel'),
            'cuenta_padre_id' => $this->input->post('cuenta_padre_id') ?: NULL,
            'es_afectable' => $this->input->post('es_afectable') ? 1 : 0,
            'requiere_auxiliar' => $this->input->post('requiere_auxiliar') ? 1 : 0,
            'tipo_auxiliar' => $this->input->post('tipo_auxiliar'),
            'saldo_inicial' => $this->input->post('saldo_inicial') ?: 0,
            'usuario_creacion' => $this->session->userdata('user_id')
        ];
        
        // Validaciones
        if(empty($data['codigo']) || empty($data['nombre'])) {
            echo json_encode(['success' => false, 'message' => 'Código y nombre son requeridos']);
            return;
        }
        
        $result = $this->db->insert('cuentas_contables', $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Cuenta creada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear cuenta']);
        }
    }
    
    /**
     * Actualiza una cuenta (AJAX)
     */
    public function editar_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $data = [
            'nombre' => $this->input->post('nombre'),
            'subtipo' => $this->input->post('subtipo'),
            'es_afectable' => $this->input->post('es_afectable') ? 1 : 0,
            'requiere_auxiliar' => $this->input->post('requiere_auxiliar') ? 1 : 0,
            'tipo_auxiliar' => $this->input->post('tipo_auxiliar'),
            'estatus' => $this->input->post('estatus')
        ];
        
        $this->db->where('id', $id);
        $result = $this->db->update('cuentas_contables', $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Cuenta actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar cuenta']);
        }
    }
    
    /**
     * Obtiene cuentas padre para select (AJAX)
     */
    public function get_cuentas_padre_ajax() {
        $this->db->select('id, codigo, nombre, nivel');
        $this->db->from('cuentas_contables');
        $this->db->where('estatus', 'Activa');
        $this->db->order_by('codigo', 'ASC');
        $cuentas = $this->db->get()->result();
        
        echo json_encode(['success' => true, 'cuentas' => $cuentas]);
    }
}
