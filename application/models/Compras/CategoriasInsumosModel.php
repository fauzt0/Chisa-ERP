<?php
/**
 * CategoriasInsumosModel - Modelo de gestión de categorías de insumos
 * 
 * Gestiona categorías jerárquicas para clasificar insumos
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class CategoriasInsumosModel extends MY_Model {
    
    protected $tableName = 'categorias_insumos';
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Obtiene todas las categorías activas
     */
    public function get_all_categorias() {
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('orden', 'ASC');
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get($this->tableName)->result();
    }
    
    /**
     * Obtiene solo categorías raíz (sin padre)
     */
    public function get_categorias_raiz() {
        $this->db->where('categoria_padre_id IS NULL');
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('orden', 'ASC');
        return $this->db->get($this->tableName)->result();
    }
    
    /**
     * Obtiene subcategorías de una categoría padre
     */
    public function get_subcategorias($padre_id) {
        $this->db->where('categoria_padre_id', $padre_id);
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('orden', 'ASC');
        return $this->db->get($this->tableName)->result();
    }
    
    /**
     * Obtiene una categoría por ID
     */
    public function get_categoria($id) {
        return $this->db->get_where($this->tableName, ['id' => $id])->row();
    }
    
    /**
     * Construye árbol jerárquico de categorías
     */
    public function get_arbol_categorias() {
        $categorias_raiz = $this->get_categorias_raiz();
        $arbol = [];
        
        foreach($categorias_raiz as $categoria) {
            $arbol[] = $this->construir_nodo($categoria);
        }
        
        return $arbol;
    }
    
    /**
     * Construye un nodo del árbol con sus hijos (recursivo)
     */
    private function construir_nodo($categoria) {
        $nodo = [
            'id' => $categoria->id,
            'nombre' => $categoria->nombre,
            'tipo' => $categoria->tipo,
            'descripcion' => $categoria->descripcion,
            'icono' => $categoria->icono,
            'orden' => $categoria->orden,
            'subcategorias' => []
        ];
        
        $hijos = $this->get_subcategorias($categoria->id);
        foreach($hijos as $hijo) {
            $nodo['subcategorias'][] = $this->construir_nodo($hijo);
        }
        
        return $nodo;
    }
    
    /**
     * Crea una nueva categoría
     */
    public function crear_categoria($data) {
        return $this->db->insert($this->tableName, $data);
    }
    
    /**
     * Actualiza una categoría
     */
    public function actualizar_categoria($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, $data);
    }
    
    /**
     * Elimina una categoría (solo si no tiene dependencias)
     */
    public function eliminar_categoria($id) {
        // Verificar que no tenga subcategorías
        if($this->tiene_subcategorias($id)) {
            return ['success' => false, 'message' => 'No se puede eliminar: tiene subcategorías'];
        }
        
        // Verificar que no tenga insumos
        if($this->tiene_insumos($id)) {
            return ['success' => false, 'message' => 'No se puede eliminar: tiene insumos asociados'];
        }
        
        $this->db->where('id', $id);
        $result = $this->db->delete($this->tableName);
        
        return ['success' => $result, 'message' => $result ? 'Categoría eliminada' : 'Error al eliminar'];
    }
    
    /**
     * Verifica si una categoría tiene subcategorías
     */
    public function tiene_subcategorias($id) {
        $this->db->where('categoria_padre_id', $id);
        return $this->db->count_all_results($this->tableName) > 0;
    }
    
    /**
     * Verifica si una categoría tiene insumos asociados
     */
    public function tiene_insumos($id) {
        $this->db->where('categoria_id', $id);
        return $this->db->count_all_results('insumos') > 0;
    }
    
    /**
     * Obtiene categorías para select (excluyendo una específica y sus descendientes)
     */
    public function get_categorias_para_select($excluir_id = null) {
        $categorias = $this->get_all_categorias();
        $opciones = [];
        
        // Si hay ID a excluir, obtener sus descendientes
        $excluir_ids = [];
        if($excluir_id) {
            $excluir_ids = $this->get_descendientes($excluir_id);
            $excluir_ids[] = $excluir_id;
        }
        
        foreach($categorias as $cat) {
            if(!in_array($cat->id, $excluir_ids)) {
                $nivel = $this->get_nivel_categoria($cat->id);
                $prefijo = str_repeat('&nbsp;&nbsp;&nbsp;', $nivel);
                $opciones[$cat->id] = $prefijo . $cat->nombre . ' (' . $cat->tipo . ')';
            }
        }
        
        return $opciones;
    }
    
    /**
     * Obtiene todos los descendientes de una categoría
     */
    private function get_descendientes($id) {
        $descendientes = [];
        $hijos = $this->get_subcategorias($id);
        
        foreach($hijos as $hijo) {
            $descendientes[] = $hijo->id;
            $descendientes = array_merge($descendientes, $this->get_descendientes($hijo->id));
        }
        
        return $descendientes;
    }
    
    /**
     * Obtiene el nivel de profundidad de una categoría
     */
    private function get_nivel_categoria($id, $nivel = 0) {
        $categoria = $this->get_categoria($id);
        if(!$categoria || !$categoria->categoria_padre_id) {
            return $nivel;
        }
        return $this->get_nivel_categoria($categoria->categoria_padre_id, $nivel + 1);
    }
}
