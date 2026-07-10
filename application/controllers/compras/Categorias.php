<?php
/**
 * Categorias - Controlador de gestión de categorías de insumos
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Categorias extends MY_Controller {
    
    protected $modulo = 'Compras';
    
    public function __construct() {
        parent::__construct();
        $this->load->helper('permissions');
        $this->load->model('Compras/CategoriasInsumosModel');
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Categorías de Insumos';
        $this->viewData['headTitle'] = 'Categorías de Insumos';
        $this->viewData['breadcrumb'] = 'Inicio > Compras > Categorías';
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'compras/categorias/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene árbol de categorías (AJAX)
     */
    public function lista_ajax() {
        $arbol = $this->CategoriasInsumosModel->get_arbol_categorias();
        echo json_encode(['success' => true, 'categorias' => $arbol]);
    }
    
    /**
     * Obtiene una categoría específica (AJAX)
     */
    public function get_categoria_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $categoria = $this->CategoriasInsumosModel->get_categoria($id);
        if($categoria) {
            echo json_encode(['success' => true, 'categoria' => $categoria]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
        }
    }
    
    /**
     * Obtiene categorías para select (AJAX)
     */
    public function get_select_padres_ajax() {
        $excluir_id = $this->input->post('excluir_id');
        $categorias = $this->CategoriasInsumosModel->get_categorias_para_select($excluir_id);
        echo json_encode(['success' => true, 'categorias' => $categorias]);
    }
    
    /**
     * Crea una nueva categoría (AJAX)
     */
    public function crear_ajax() {
        $this->requiere_permiso('compras_categorias', 'No tienes permiso para gestionar categorías');

        $data = [
            'nombre' => $this->input->post('nombre'),
            'categoria_padre_id' => $this->input->post('categoria_padre_id') ?: null,
            'tipo' => $this->input->post('tipo'),
            'descripcion' => $this->input->post('descripcion'),
            'icono' => $this->input->post('icono') ?: 'fa-box',
            'orden' => $this->input->post('orden') ?: 0,
            'estatus' => 'Activo'
        ];
        
        // Validaciones
        if(empty($data['nombre'])) {
            echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
            return;
        }
        
        if(empty($data['tipo'])) {
            echo json_encode(['success' => false, 'message' => 'El tipo es requerido']);
            return;
        }
        
        $result = $this->CategoriasInsumosModel->crear_categoria($data);
        
        if($result) {
            $this->registrar_bitacora('Categoría de insumo creada: ' . $data['nombre'], 'Compras');
            echo json_encode(['success' => true, 'message' => 'Categoría creada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear categoría']);
        }
    }
    
    /**
     * Actualiza una categoría (AJAX)
     */
    public function editar_ajax() {
        $this->requiere_permiso('compras_categorias', 'No tienes permiso para gestionar categorías');

        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $data = [
            'nombre' => $this->input->post('nombre'),
            'categoria_padre_id' => $this->input->post('categoria_padre_id') ?: null,
            'tipo' => $this->input->post('tipo'),
            'descripcion' => $this->input->post('descripcion'),
            'icono' => $this->input->post('icono') ?: 'fa-box',
            'orden' => $this->input->post('orden') ?: 0
        ];
        
        // Validaciones
        if(empty($data['nombre'])) {
            echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
            return;
        }
        
        $result = $this->CategoriasInsumosModel->actualizar_categoria($id, $data);
        
        if($result) {
            $this->registrar_bitacora('Categoría de insumo actualizada: ' . $data['nombre'] . ' (#' . $id . ')', 'Compras');
            echo json_encode(['success' => true, 'message' => 'Categoría actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar categoría']);
        }
    }
    
    /**
     * Elimina una categoría (AJAX)
     */
    public function eliminar_ajax() {
        $this->requiere_permiso('compras_categorias', 'No tienes permiso para gestionar categorías');

        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $result = $this->CategoriasInsumosModel->eliminar_categoria($id);
        if (!empty($result['success'])) {
            $this->registrar_bitacora('Categoría de insumo eliminada (#' . $id . ')', 'Compras');
        }
        echo json_encode($result);
    }
}
