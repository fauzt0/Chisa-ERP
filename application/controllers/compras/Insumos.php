<?php
/**
 * Insumos - Controlador de gestión de insumos
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Insumos extends MY_Controller {
    
    protected $modulo = 'Almacén';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Compras/InsumosModel');
        $this->load->model('Compras/CategoriasInsumosModel');
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Catálogo de Insumos';
        $this->viewData['headTitle'] = 'Gestión de Insumos';
        $this->viewData['breadcrumb'] = 'Inicio > Compras > Insumos';
        
        // Obtener estadísticas
        $stats = $this->InsumosModel->get_estadisticas();
        $this->viewData['response'] = ['stats' => $stats];
        
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'compras/insumos/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Lista de insumos para DataTables (AJAX)
     */
    public function lista_ajax() {
        $list = $this->InsumosModel->get_datatables();
        $data = array();
        $no = $_POST['start'];

        foreach ($list as $insumo) {
            $no++;
            $row = array();
            
            // Código
            $row[] = $insumo->codigo;
            
            // Nombre (técnico + alias)
            $nombre = '<strong>' . $insumo->nombre_tecnico . '</strong>';
            if($insumo->alias) {
                $nombre .= '<br><small class="text-muted">' . $insumo->alias . '</small>';
            }
            $row[] = $nombre;
            
            // Categoría
            $row[] = $insumo->categoria_nombre ?? '<span class="text-muted">Sin categoría</span>';
            
            // Marca
            $row[] = $insumo->marca ?? '-';
            
            // Unidad de medida
            $row[] = $insumo->unidad_medida;
            
            // Stock (actual / mínimo + badge)
            $badge = '';
            if($insumo->stock_actual <= $insumo->stock_minimo) {
                $badge = '<span class="badge bg-danger">Bajo</span>';
            } else if($insumo->stock_actual <= $insumo->stock_minimo * 1.5) {
                $badge = '<span class="badge bg-warning">Medio</span>';
            } else {
                $badge = '<span class="badge bg-success">OK</span>';
            }
            $row[] = $insumo->stock_actual . ' / ' . $insumo->stock_minimo . ' ' . $badge;
            
            // Precio promedio
            $row[] = '$' . number_format($insumo->precio_promedio ?? 0, 2);
            
            // Estatus
            if($insumo->estatus == 'Activo') {
                $row[] = '<span class="badge bg-success">Activo</span>';
            } else {
                $row[] = '<span class="badge bg-secondary">Inactivo</span>';
            }
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-primary" onclick="mostrarModalEditar('.$insumo->id.')">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarInsumo('.$insumo->id.')">
                    <i class="fas fa-trash"></i>
                </button>';
            
            $row[] = $acciones;
            
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->InsumosModel->count_all(),
            "recordsFiltered" => $this->InsumosModel->count_filtered(),
            "data" => $data,
        );

        echo json_encode($output);
    }
    
    /**
     * Obtiene un insumo específico (AJAX)
     */
    public function get_insumo_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $insumo = $this->InsumosModel->get_insumo($id);
        if($insumo) {
            echo json_encode(['success' => true, 'insumo' => $insumo]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Insumo no encontrado']);
        }
    }
    
    /**
     * Crea un nuevo insumo (AJAX)
     */
    public function crear_ajax() {
        $data = [
            'codigo' => $this->input->post('codigo'),
            'nombre_tecnico' => $this->input->post('nombre_tecnico'),
            'alias' => $this->input->post('alias'),
            'marca' => $this->input->post('marca'),
            'categoria_id' => $this->input->post('categoria_id'),
            'descripcion' => $this->input->post('descripcion'),
            'unidad_medida' => $this->input->post('unidad_medida'),
            'stock_actual' => $this->input->post('stock_actual') ?: 0,
            'stock_minimo' => $this->input->post('stock_minimo') ?: 0,
            'stock_maximo' => $this->input->post('stock_maximo') ?: null,
            'precio_promedio' => 0,
            'estatus' => 'Activo'
        ];
        
        // Validaciones
        if(empty($data['nombre_tecnico'])) {
            echo json_encode(['success' => false, 'message' => 'El nombre técnico es requerido']);
            return;
        }
        
        if(empty($data['categoria_id'])) {
            echo json_encode(['success' => false, 'message' => 'La categoría es requerida']);
            return;
        }
        
        if(empty($data['unidad_medida'])) {
            echo json_encode(['success' => false, 'message' => 'La unidad de medida es requerida']);
            return;
        }
        
        // Validar stock
        if($data['stock_actual'] < 0) {
            echo json_encode(['success' => false, 'message' => 'El stock actual no puede ser negativo']);
            return;
        }
        
        if($data['stock_minimo'] < 0) {
            echo json_encode(['success' => false, 'message' => 'El stock mínimo no puede ser negativo']);
            return;
        }
        
        if($data['stock_maximo'] && $data['stock_maximo'] < $data['stock_minimo']) {
            echo json_encode(['success' => false, 'message' => 'El stock máximo debe ser mayor al stock mínimo']);
            return;
        }
        
        $result = $this->InsumosModel->crear_insumo($data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Insumo creado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear insumo']);
        }
    }
    
    /**
     * Actualiza un insumo (AJAX)
     */
    public function editar_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $data = [
            'codigo' => $this->input->post('codigo'),
            'nombre_tecnico' => $this->input->post('nombre_tecnico'),
            'alias' => $this->input->post('alias'),
            'marca' => $this->input->post('marca'),
            'categoria_id' => $this->input->post('categoria_id'),
            'descripcion' => $this->input->post('descripcion'),
            'unidad_medida' => $this->input->post('unidad_medida'),
            'stock_actual' => $this->input->post('stock_actual'),
            'stock_minimo' => $this->input->post('stock_minimo'),
            'stock_maximo' => $this->input->post('stock_maximo') ?: null,
            'estatus' => $this->input->post('estatus')
        ];
        
        // Validaciones (mismas que crear)
        if(empty($data['nombre_tecnico'])) {
            echo json_encode(['success' => false, 'message' => 'El nombre técnico es requerido']);
            return;
        }
        
        $result = $this->InsumosModel->actualizar_insumo($id, $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Insumo actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar insumo']);
        }
    }
    
    /**
     * Elimina un insumo (AJAX)
     */
    public function eliminar_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $result = $this->InsumosModel->eliminar_insumo($id);
        echo json_encode($result);
    }
    
    /**
     * Obtiene categorías para select (AJAX)
     */
    public function get_categorias_select_ajax() {
        $categorias = $this->CategoriasInsumosModel->get_all_categorias();
        
        $opciones = [];
        foreach($categorias as $cat) {
            $nivel = 0; // Calcular nivel si es necesario
            $prefijo = str_repeat('&nbsp;&nbsp;&nbsp;', $nivel);
            $opciones[] = [
                'id' => $cat->id,
                'text' => $cat->nombre . ' (' . $cat->tipo . ')'
            ];
        }
        
        echo json_encode(['success' => true, 'categorias' => $opciones]);
    }
    
    /**
     * Obtiene insumos con stock bajo (AJAX)
     */
    public function stock_bajo_ajax() {
        $insumos = $this->InsumosModel->get_insumos_stock_bajo();
        echo json_encode(['success' => true, 'insumos' => $insumos, 'total' => count($insumos)]);
    }
}
