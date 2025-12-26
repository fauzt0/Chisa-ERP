<?php
/**
 * Descuentos Controller - Gestión de descuentos
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Descuentos extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Ventas/DescuentosModel');
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Descuentos';
        $this->viewData['headTitle'] = 'Gestión de Descuentos';
        $this->viewData['breadcrumb'] = 'Inicio > CRM Ventas > Descuentos';
        
        $this->viewData['response'] = [];
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'ventas/descuentos/main';
        
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Lista para DataTables (AJAX)
     */
    public function lista_ajax() {
        $list = $this->DescuentosModel->get_datatables();
        $data = [];
        
        foreach($list as $descuento) {
            $row = [];
            
            $row[] = $descuento->nombre;
            $row[] = $descuento->descripcion;
            
            // Tipo y valor
            if($descuento->tipo_descuento == 'Porcentaje') {
                $row[] = '<span class="badge bg-info">' . $descuento->valor . '%</span>';
            } else {
                $row[] = '<span class="badge bg-success">$' . number_format($descuento->valor, 2) . '</span>';
            }
            
            // Estatus
            $badge = $descuento->estatus == 'Activo' ? 'success' : 'secondary';
            $row[] = '<span class="badge bg-' . $badge . '">' . $descuento->estatus . '</span>';
            
            // Acciones
            $row[] = '
            <button class="btn btn-sm btn-warning" onclick="editarDescuento('.$descuento->id.')" title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="eliminarDescuento('.$descuento->id.')" title="Eliminar">
                <i class="fas fa-trash"></i>
            </button>';
            
            $data[] = $row;
        }
        
        $output = [
            'draw' => intval($this->input->post('draw')),
            'recordsTotal' => $this->DescuentosModel->count_all(),
            'recordsFiltered' => $this->DescuentosModel->count_filtered(),
            'data' => $data
        ];
        
        echo json_encode($output);
    }
    
    /**
     * Obtiene un descuento (AJAX)
     */
    public function get_descuento_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $descuento = $this->DescuentosModel->get_by_id($id);
        
        if($descuento) {
            echo json_encode(['success' => true, 'descuento' => $descuento]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Descuento no encontrado']);
        }
    }
    
    /**
     * Crea un descuento (AJAX)
     */
    public function crear_ajax() {
        $data = [
            'nombre' => $this->input->post('nombre'),
            'descripcion' => $this->input->post('descripcion'),
            'tipo_descuento' => $this->input->post('tipo_descuento'),
            'valor' => $this->input->post('valor'),
            'estatus' => $this->input->post('estatus')
        ];
        
        $result = $this->DescuentosModel->insert($data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Descuento creado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear descuento']);
        }
    }
    
    /**
     * Edita un descuento (AJAX)
     */
    public function editar_ajax() {
        $id = $this->input->post('id');
        
        $data = [
            'nombre' => $this->input->post('nombre'),
            'descripcion' => $this->input->post('descripcion'),
            'tipo_descuento' => $this->input->post('tipo_descuento'),
            'valor' => $this->input->post('valor'),
            'estatus' => $this->input->post('estatus')
        ];
        
        $result = $this->DescuentosModel->update($id, $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Descuento actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar descuento']);
        }
    }
    
    /**
     * Elimina un descuento (AJAX)
     */
    public function eliminar_ajax() {
        $id = $this->input->post('id');
        
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $result = $this->DescuentosModel->delete($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Descuento eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar descuento']);
        }
    }
    
    /**
     * Obtiene descuentos activos para select (AJAX)
     */
    public function get_descuentos_activos_ajax() {
        $descuentos = $this->DescuentosModel->get_descuentos_activos();
        echo json_encode(['success' => true, 'descuentos' => $descuentos]);
    }
}
