<?php
/**
 * Productos - Controlador de gestión de productos terminados
 * 
 * Gestiona productos fabricados y de reventa con formulaciones (BOM)
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Productos extends MY_Controller {

    protected $modulo = 'Producción';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Produccion/ProductosModel');
        $this->load->model('Compras/InsumosModel');
        $this->load->helper('permissions');
    }
    
    /**
     * Vista principal
     */
    public function index() {
        $this->viewData['pageTitle'] = 'Productos';
        $this->viewData['headTitle'] = 'Gestión de Productos';
        $this->viewData['breadcrumb'] = 'Inicio > Producción';
        
        // Obtener estadísticas
        $stats = $this->ProductosModel->get_estadisticas();
        $this->viewData['response'] = [
            'stats' => $stats,
            'puede_ver_costos' => puede_ver_costos()
        ];
        
        $this->viewData['validate'] = '';
        $this->viewData['pageView']  = 'produccion/productos/main';
        // Cargar JS después de app.js (jQuery) vía pageScript en general_template
        $this->viewData['pageScript'] = 'produccion/productos/scripts';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Lista de productos para DataTables (AJAX)
     */
    public function lista_ajax() {
        try {
        $list = $this->ProductosModel->get_datatables();
        $data = array();
        $no = isset($_POST['start']) ? $_POST['start'] : 0;

        foreach ($list as $producto) {
            $no++;
            $row = array();
            
            // Código
            $row[] = '<strong>' . $producto->codigo . '</strong>';
            
            // Imagen con zoom
            $imagen = $producto->foto_producto 
                ? base_url($producto->foto_producto) 
                : base_url('assets/img/no-image.png');
            
            $row[] = '<img src="' . $imagen . '" 
                           alt="' . htmlspecialchars($producto->nombre) . '" 
                           class="img-thumbnail" 
                           style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;"
                           onclick="verImagenProductoZoom(\'' . $imagen . '\', \'' . addslashes($producto->nombre) . '\')"
                           onerror="this.onerror=null; this.src=\'' . base_url('assets/img/no-image.png') . '\'">';
            
            // Nombre con badges de variante
            $nombre_display = $producto->nombre;
            
            // Si es variante - agregar badge
            if($producto->es_variante == 1 && $producto->variante_valor) {
                $badge_color = 'info';
                $icon = 'fa-link';
                
                switch($producto->variante_tipo) {
                    case 'color': $badge_color = 'info'; $icon = 'fa-palette'; break;
                    case 'tamaño': $badge_color = 'warning'; $icon = 'fa-ruler'; break;
                    case 'acabado': $badge_color = 'secondary'; $icon = 'fa-paint-brush'; break;
                    case 'textura': $badge_color = 'primary'; $icon = 'fa-th'; break;
                }
                
                $nombre_display .= ' <span class="badge bg-'.$badge_color.'">
                    <i class="fas '.$icon.'"></i> '.$producto->variante_valor.'
                </span>';
            }
            
            // Si tiene variantes - mostrar contador
            if($producto->es_variante == 0) {
                $this->db->where('producto_padre_id', $producto->id);
                $num_variantes = $this->db->count_all_results('productos');
                
                if($num_variantes > 0) {
                    $nombre_display .= ' <span class="badge bg-success">
                        <i class="fas fa-sitemap"></i> '.$num_variantes.' '.($num_variantes == 1 ? 'variante' : 'variantes').'
                    </span>';
                }
            }
            
            $row[] = $nombre_display;
            
            // Alias
            $row[] = $producto->alias ? '<small class="text-muted">' . $producto->alias . '</small>' : '<span class="text-muted">-</span>';
            
            // Categoría
            $row[] = $producto->categoria_nombre;
            
            // Tipo
            $badge_tipo = $producto->tipo_producto == 'Fabricado' ? 'primary' : 'info';
            $row[] = '<span class="badge bg-' . $badge_tipo . '">' . $producto->tipo_producto . '</span>';
            
            // Stock
            $badge_stock = '';
            if($producto->stock_actual <= $producto->stock_minimo * 0.5) {
                $badge_stock = 'danger';
            } elseif($producto->stock_actual <= $producto->stock_minimo) {
                $badge_stock = 'warning';
            } else {
                $badge_stock = 'success';
            }
            $row[] = '<span class="badge bg-' . $badge_stock . '">' . $producto->stock_actual . ' ' . $producto->unidad_venta . '</span>';
            
            // Precio - solo visible con permiso
            $row[] = ocultar_precio($producto->precio_venta, 2);
            
            // Estatus
            $badge_estatus = '';
            switch($producto->estatus) {
                case 'Activo': $badge_estatus = 'success'; break;
                case 'Inactivo': $badge_estatus = 'secondary'; break;
                case 'Descontinuado': $badge_estatus = 'danger'; break;
            }
            $row[] = '<span class="badge bg-' . $badge_estatus . '">' . $producto->estatus . '</span>';
            
            // Acciones
            $acciones = '
                <button type="button" class="btn btn-sm btn-info" onclick="verProducto('.$producto->id.')" title="Ver Detalle">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-primary" onclick="editarProducto('.$producto->id.')" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>';
            
            // Botón de formulación solo para productos fabricados
            if($producto->tipo_producto == 'Fabricado') {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-success" onclick="gestionarFormulacion('.$producto->id.')" title="Gestionar Formulación">
                    <i class="fas fa-flask"></i>
                </button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="verHistorialFormulaciones('.$producto->id.')" title="Historial de Formulaciones">
                    <i class="fas fa-history"></i>
                </button>';
            }
            
            // Botones de variantes - solo para productos NO variantes
            if($producto->es_variante == 0) {
                $acciones .= '
                <button type="button" class="btn btn-sm btn-warning" onclick="crearVariante('.$producto->id.')" title="Crear Variante">
                    <i class="fas fa-palette"></i>
                </button>';
                
                // Si tiene variantes - botón ver familia
                $this->db->where('producto_padre_id', $producto->id);
                $num_variantes = $this->db->count_all_results('productos');
                
                if($num_variantes > 0) {
                    $acciones .= '
                    <button type="button" class="btn btn-sm btn-success" onclick="verFamiliaProductos('.$producto->id.')" title="Ver Familia de Productos">
                        <i class="fas fa-sitemap"></i>
                    </button>';
                }
            }
            
            $acciones .= '
                <button type="button" class="btn btn-sm btn-danger" onclick="eliminarProducto('.$producto->id.')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>';
            
            $row[] = $acciones;
            
            $data[] = $row;
        }

        $output = array(
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
            "recordsTotal" => $this->ProductosModel->count_all(),
            "recordsFiltered" => $this->ProductosModel->count_filtered(),
            "data" => $data,
        );

        echo json_encode($output);
        } catch (Exception $e) {
            log_message('error', 'lista_ajax productos: ' . $e->getMessage());
            echo json_encode([
                'draw'            => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => [],
                'error'           => 'Error al cargar productos: ' . $e->getMessage(),
            ]);
        }
    }
    
    /**
     * Obtiene un producto específico (AJAX)
     */
    public function get_producto_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $producto = $this->ProductosModel->get_producto($id);
        if($producto) {
            echo json_encode(['success' => true, 'producto' => $producto]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        }
    }
    
    /**
     * Crea un nuevo producto (AJAX)
     */
    public function crear_ajax() {
        // Manejar subida de imagen
        $foto_producto = null;
        if (!empty($_FILES['foto_producto']['name'])) {
            $config['upload_path'] = './uploads/productos/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif|webp';
            $config['max_size'] = 2048; // 2MB
            $config['encrypt_name'] = TRUE;
            
            // Crear directorio si no existe
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }
            
            $this->load->library('upload', $config);
            
            if ($this->upload->do_upload('foto_producto')) {
                $upload_data = $this->upload->data();
                $foto_producto = 'uploads/productos/' . $upload_data['file_name'];
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al subir imagen: ' . $this->upload->display_errors('', '')]);
                return;
            }
        }
        
        // Manejar subida de catálogo PDF
        $catalogo_pdf = null;
        if (!empty($_FILES['catalogo_pdf']['name'])) {
            $config_pdf['upload_path'] = './uploads/catalogos/';
            $config_pdf['allowed_types'] = 'pdf';
            $config_pdf['max_size'] = 5120; // 5MB
            $config_pdf['encrypt_name'] = TRUE;
            
            // Crear directorio si no existe
            if (!is_dir($config_pdf['upload_path'])) {
                mkdir($config_pdf['upload_path'], 0755, true);
            }
            
            $this->upload->initialize($config_pdf);
            
            if ($this->upload->do_upload('catalogo_pdf')) {
                $upload_data = $this->upload->data();
                $catalogo_pdf = 'uploads/catalogos/' . $upload_data['file_name'];
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al subir catálogo PDF: ' . $this->upload->display_errors('', '')]);
                return;
            }
        }
        
        $data = [
            'nombre' => $this->input->post('nombre'),
            'alias' => $this->input->post('alias'),
            'descripcion' => $this->input->post('descripcion'),
            'categoria_id' => $this->input->post('categoria_id'),
            'tipo_producto' => $this->input->post('tipo_producto'),
            'unidad_venta' => $this->input->post('unidad_venta'),
            'presentacion_principal' => $this->input->post('presentacion_principal'),
            'contenido_neto' => $this->input->post('contenido_neto'),
            'unidad_contenido' => $this->input->post('unidad_contenido'),
            'codigo_barras' => $this->input->post('codigo_barras'),
            'sku' => $this->input->post('sku'),
            'stock_minimo' => $this->input->post('stock_minimo') ?: 0,
            'stock_maximo' => $this->input->post('stock_maximo') ?: 0,
            'precio_venta' => $this->input->post('precio_venta') ?: 0,
            'margen_utilidad' => $this->input->post('margen_utilidad') ?: 0,
            'rendimiento' => $this->input->post('rendimiento'),
            'peso_bruto' => $this->input->post('peso_bruto'),
            'tiempo_secado' => $this->input->post('tiempo_secado'),
            'colores_disponibles' => $this->input->post('colores_disponibles'),
            'caracteristicas' => $this->input->post('caracteristicas'),
            'texturas' => $this->input->post('texturas'),
            'forma' => $this->input->post('forma'),
            'dimensiones' => $this->input->post('dimensiones'),
            'resistencia' => $this->input->post('resistencia'),
            'colocacion' => $this->input->post('colocacion'),
            'mantenimiento_preventivo' => $this->input->post('mantenimiento_preventivo'),
            'mantenimiento_correctivo' => $this->input->post('mantenimiento_correctivo'),
            'observaciones' => $this->input->post('observaciones'),
            'producto_padre_id' => $this->input->post('producto_padre_id') ?: NULL,
            'es_variante' => $this->input->post('producto_padre_id') ? 1 : 0,
            'variante_tipo' => $this->input->post('variante_tipo'),
            'variante_valor' => $this->input->post('variante_valor'),
            'proveedor_id' => $this->input->post('proveedor_id') ?: NULL, // NULL si está vacío
            'foto_producto' => $foto_producto,
            'catalogo_pdf' => $catalogo_pdf,
            'fecha_actualizacion_catalogo' => $catalogo_pdf ? date('Y-m-d H:i:s') : NULL,
            'usuario_creacion' => $this->session->userdata('user_id')
        ];
        
        // Validaciones
        if(empty($data['nombre']) || empty($data['categoria_id'])) {
            echo json_encode(['success' => false, 'message' => 'Nombre y categoría son requeridos']);
            return;
        }
        
        $result = $this->ProductosModel->crear_producto($data);
        
        if($result) {
            $producto_id = $this->db->insert_id();
            echo json_encode(['success' => true, 'message' => 'Producto creado correctamente', 'producto_id' => $producto_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear producto']);
        }
    }
    
    /**
     * Actualiza un producto (AJAX)
     */
    public function editar_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $data = [
            'nombre' => $this->input->post('nombre'),
            'alias' => $this->input->post('alias'),
            'descripcion' => $this->input->post('descripcion'),
            'categoria_id' => $this->input->post('categoria_id'),
            'unidad_venta' => $this->input->post('unidad_venta'),
            'presentacion_principal' => $this->input->post('presentacion_principal'),
            'contenido_neto' => $this->input->post('contenido_neto'),
            'codigo_barras' => $this->input->post('codigo_barras'),
            'sku' => $this->input->post('sku'),
            'stock_minimo' => $this->input->post('stock_minimo'),
            'stock_maximo' => $this->input->post('stock_maximo'),
            'precio_venta' => $this->input->post('precio_venta'),
            'margen_utilidad' => $this->input->post('margen_utilidad'),
            'rendimiento' => $this->input->post('rendimiento'),
            'peso_bruto' => $this->input->post('peso_bruto'),
            'tiempo_secado' => $this->input->post('tiempo_secado'),
            'colores_disponibles' => $this->input->post('colores_disponibles'),
            'caracteristicas' => $this->input->post('caracteristicas'),
            'texturas' => $this->input->post('texturas'),
            'forma' => $this->input->post('forma'),
            'dimensiones' => $this->input->post('dimensiones'),
            'resistencia' => $this->input->post('resistencia'),
            'colocacion' => $this->input->post('colocacion'),
            'mantenimiento_preventivo' => $this->input->post('mantenimiento_preventivo'),
            'mantenimiento_correctivo' => $this->input->post('mantenimiento_correctivo'),
            'observaciones' => $this->input->post('observaciones'),
            'estatus' => $this->input->post('estatus')
        ];
        
        $result = $this->ProductosModel->actualizar_producto($id, $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar producto']);
        }
    }
    
    /**
     * Elimina un producto (AJAX)
     */
    public function eliminar_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $result = $this->ProductosModel->eliminar_producto($id);
        echo json_encode($result);
    }
    
    // =====================================================
    // GESTIÓN DE FORMULACIONES (BOM)
    // =====================================================
    
    /**
     * Obtiene la formulación activa de un producto (AJAX)
     */
    public function get_formulacion_ajax() {
        $producto_id = $this->input->post('producto_id');
        if(!$producto_id) {
            echo json_encode(['success' => false, 'message' => 'Producto ID requerido']);
            return;
        }
        
        $formulacion = $this->ProductosModel->get_formulacion_activa($producto_id);
        
        if($formulacion) {
            echo json_encode(['success' => true, 'formulacion' => $formulacion]);
        } else {
            echo json_encode(['success' => true, 'formulacion' => null, 'message' => 'Sin formulación']);
        }
    }
    
    /**
     * Crea una nueva formulación (AJAX)
     */
    public function crear_formulacion_ajax() {
        $data = [
            'producto_id' => $this->input->post('producto_id'),
            'cliente_id' => $this->input->post('cliente_id') ?: NULL,
            'nombre_version' => $this->input->post('nombre_version'),
            'descripcion' => $this->input->post('descripcion'),
            'comentarios' => $this->input->post('comentarios') ?: NULL,
            'cantidad_producida' => $this->input->post('cantidad_producida'),
            'rendimiento_m2_por_kg' => $this->input->post('rendimiento_m2_por_kg') ?: NULL,
            'unidad_produccion' => $this->input->post('unidad_produccion'),
            'costo_mano_obra' => $this->input->post('costo_mano_obra') ?: 0,
            'costo_indirecto' => $this->input->post('costo_indirecto') ?: 0,
            'usuario_creacion' => $this->session->userdata('user_id')
        ];
        
        if(empty($data['producto_id']) || empty($data['cantidad_producida'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $result = $this->ProductosModel->crear_formulacion($data);
        
        if($result) {
            $formulacion_id = $this->db->insert_id();
            echo json_encode(['success' => true, 'message' => 'Formulación creada', 'formulacion_id' => $formulacion_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear formulación']);
        }
    }
    
    /**
     * Agrega un componente a la formulación (AJAX)
     */
    public function agregar_componente_ajax() {
        $formulacion_id = $this->input->post('formulacion_id');
        $tipo_componente = $this->input->post('tipo_componente');
        
        if(!$formulacion_id || !$tipo_componente) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $data = [
            'tipo_componente' => $tipo_componente,
            'cantidad' => $this->input->post('cantidad'),
            'unidad' => $this->input->post('unidad'),
            'porcentaje' => $this->input->post('porcentaje') !== '' ? $this->input->post('porcentaje') : null,
            'observaciones' => $this->input->post('observaciones'),
            'grupo_color' => $this->input->post('grupo_color') ?: NULL,
            'porcentaje_fase_acuosa' => $this->input->post('porcentaje_fase_acuosa') !== '' ? $this->input->post('porcentaje_fase_acuosa') : null,
            'kg_fase_acuosa' => $this->input->post('kg_fase_acuosa') !== '' ? $this->input->post('kg_fase_acuosa') : null,
            'orden' => $this->input->post('orden') ?: 0
        ];
        
        if($tipo_componente == 'Insumo') {
            $data['insumo_id'] = $this->input->post('insumo_id');
            $data['producto_id'] = null;
        } else {
            $data['producto_id'] = $this->input->post('producto_id');
            $data['insumo_id'] = null;
        }
        
        $result = $this->ProductosModel->agregar_componente($formulacion_id, $data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Componente agregado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al agregar componente']);
        }
    }
    
    /**
     * Elimina un componente de la formulación (AJAX)
     */
    public function eliminar_componente_ajax() {
        $id = $this->input->post('id');
        if(!$id) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            return;
        }
        
        $result = $this->ProductosModel->eliminar_componente($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Componente eliminado']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
    }
    
    /**
     * Obtiene lista de insumos para select (AJAX)
     */
    public function get_insumos_select_ajax() {
        $this->db->select('id, codigo, nombre_tecnico, unidad_medida, precio_promedio, stock_actual');
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('nombre_tecnico', 'ASC');
        $insumos = $this->db->get('insumos')->result();
        
        echo json_encode(['success' => true, 'insumos' => $insumos]);
    }
    
    /**
     * Obtiene lista de productos para select (AJAX)
     */
    public function get_productos_select_ajax() {
        $this->db->select('id, codigo, nombre, unidad_venta, costo_produccion');
        $this->db->where('tipo_producto', 'Fabricado');
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('nombre', 'ASC');
        $productos = $this->db->get('productos')->result();
        
        echo json_encode(['success' => true, 'productos' => $productos]);
    }
    
    /**
     * Obtiene categorías para select (AJAX)
     */
    public function get_categorias_select_ajax() {
        $categorias = $this->ProductosModel->get_categorias_select();
        echo json_encode(['success' => true, 'categorias' => $categorias]);
    }
    
    // =====================================================
    // MOVIMIENTOS Y ESCANEO
    // =====================================================
    
    /**
     * Registra salida por escaneo de código (AJAX)
     */
    public function registrar_salida_escaneo_ajax() {
        $codigo = $this->input->post('codigo');
        $cantidad = $this->input->post('cantidad');
        $user_id = $this->session->userdata('user_id');
        
        if(!$codigo || !$cantidad) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $result = $this->ProductosModel->registrar_salida_escaneo($codigo, $cantidad, $user_id);
        echo json_encode($result);
    }
    
    /**
     * Ajusta el stock de un producto (AJAX)
     */
    public function ajustar_stock_ajax() {
        $producto_id = $this->input->post('producto_id');
        $tipo_movimiento = $this->input->post('tipo_movimiento');
        $cantidad = $this->input->post('cantidad');
        $motivo = $this->input->post('motivo');
        
        if(!$producto_id || !$tipo_movimiento || !$cantidad) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }
        
        $data = [
            'producto_id' => $producto_id,
            'tipo_movimiento' => $tipo_movimiento,
            'cantidad' => $cantidad,
            'motivo' => $motivo,
            'usuario_id' => $this->session->userdata('user_id')
        ];
        
        $result = $this->ProductosModel->registrar_movimiento($data);
        echo json_encode($result);
    }
    
    /**
     * Obtiene historial de formulaciones de un producto (AJAX)
     */
    public function get_historial_formulaciones_ajax() {
        $producto_id = $this->input->post('producto_id');
        $busqueda = $this->input->post('busqueda');
        $cliente_id = $this->input->post('cliente_id') ?: null;
        $fecha_inicio = $this->input->post('fecha_inicio') ?: null;
        $fecha_fin = $this->input->post('fecha_fin') ?: null;
        
        if(!$producto_id) {
            echo json_encode(['success' => false, 'message' => 'Producto ID requerido']);
            return;
        }
        
        $formulaciones = $this->ProductosModel->get_historial_formulaciones($producto_id, $busqueda, $cliente_id, $fecha_inicio, $fecha_fin);
        
        // Para cada formulación, obtener historial de ventas
        foreach($formulaciones as &$formulacion) {
            // Obtener ventas de órdenes
            $this->db->select('
                "venta" as tipo,
                ov.folio,
                ov.fecha_creacion,
                c.razon_social as cliente,
                dov.cantidad
            ');
            $this->db->from('detalle_orden_venta dov');
            $this->db->join('ordenes_venta ov', 'ov.id = dov.orden_venta_id');
            $this->db->join('clientes c', 'c.id = ov.cliente_id', 'left');
            $this->db->where('dov.formulacion_id', $formulacion->id);
            $this->db->where('dov.producto_id', $producto_id);
            $this->db->order_by('ov.fecha_creacion', 'DESC');
            $this->db->limit(10); // Últimas 10 ventas
            $ventas = $this->db->get()->result();
            
            // Obtener ventas de obras
            $this->db->select('
                "obra" as tipo,
                o.folio,
                o.fecha_creacion,
                c.razon_social as cliente,
                op.cantidad_ajustada as cantidad
            ');
            $this->db->from('obras_productos op');
            $this->db->join('obras o', 'o.id = op.obra_id');
            $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
            $this->db->where('op.formulacion_id', $formulacion->id);
            $this->db->where('op.producto_id', $producto_id);
            $this->db->order_by('o.fecha_creacion', 'DESC');
            $this->db->limit(10); // Últimas 10 obras
            $obras = $this->db->get()->result();
            
            // Combinar y ordenar
            $formulacion->ventas = array_merge($ventas, $obras);
            usort($formulacion->ventas, function($a, $b) {
                return strtotime($b->fecha_creacion) - strtotime($a->fecha_creacion);
            });
            $formulacion->ventas = array_slice($formulacion->ventas, 0, 10);
            
            // Calcular totales
            $formulacion->total_vendido = array_reduce($formulacion->ventas, function($carry, $item) {
                return $carry + $item->cantidad;
            }, 0);
            $formulacion->num_ventas = count($formulacion->ventas);
            $formulacion->ultima_venta = !empty($formulacion->ventas) ? $formulacion->ventas[0]->fecha_creacion : null;
        }
        
        echo json_encode(['success' => true, 'formulaciones' => $formulaciones]);
    }
    
    /**
     * Obtiene detalle completo de una formulación (AJAX)
     */
    public function get_detalle_formulacion_ajax() {
        $formulacion_id = $this->input->post('formulacion_id');
        
        if(!$formulacion_id) {
            echo json_encode(['success' => false, 'message' => 'Formulación ID requerido']);
            return;
        }
        
        $formulacion = $this->ProductosModel->get_formulacion_completa($formulacion_id);
        
        if($formulacion) {
            echo json_encode(['success' => true, 'formulacion' => $formulacion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Formulación no encontrada']);
        }
    }
    
    // =====================================================
    // GESTIÓN DE VARIANTES DE PRODUCTOS
    // =====================================================
    
    /**
     * Obtiene las variantes de un producto (AJAX)
     */
    public function get_variantes_ajax() {
        $producto_id = $this->input->post('producto_id');
        
        if(!$producto_id) {
            echo json_encode(['success' => false, 'message' => 'Producto ID requerido']);
            return;
        }
        
        $this->db->select('
            id, codigo, nombre, variante_tipo, variante_valor,
            stock_actual, unidad_venta
        ');
        $this->db->where('producto_padre_id', $producto_id);
        $this->db->where('es_variante', 1);
        $this->db->order_by('variante_valor', 'ASC');
        $variantes = $this->db->get('productos')->result();
        
        // Para cada variante, verificar si tiene formulación activa
        foreach($variantes as &$v) {
            $this->db->where('producto_id', $v->id);
            $this->db->where('es_activa', 1);
            $formulacion = $this->db->get('formulaciones')->row();
            $v->tiene_formulacion = $formulacion ? true : false;
            $v->formulacion_version = $formulacion ? $formulacion->version : null;
        }
        
        echo json_encode(['success' => true, 'variantes' => $variantes]);
    }
    
    /**
     * Activa una formulación como la versión por defecto (AJAX)
     */
    public function activar_formulacion_ajax() {
        $formulacion_id = $this->input->post('formulacion_id');
        if(!$formulacion_id) {
            echo json_encode(['success' => false, 'message' => 'ID de formulación requerido']);
            return;
        }
        
        $result = $this->ProductosModel->activar_formulacion($formulacion_id);
        echo json_encode($result);
    }

    /**
     * Obtiene productos base (no variantes) para selector (AJAX)
     */
    public function get_productos_base_ajax() {
        $this->db->select('id, codigo, nombre');
        $this->db->where('es_variante', 0);
        $this->db->where('estatus', 'Activo');
        $this->db->where('tipo_producto', 'Fabricado');
        $this->db->order_by('nombre', 'ASC');
        $productos = $this->db->get('productos')->result();
        
        echo json_encode(['success' => true, 'productos' => $productos]);
    }

    /**
     * Calcula y escala los insumos requeridos para un proyecto (AJAX)
     */
    public function calcular_insumos_ajax() {
        $formulacion_id = $this->input->post('formulacion_id');
        $cubetas = $this->input->post('cubetas');
        $m2 = $this->input->post('m2');

        if(!$formulacion_id) {
            echo json_encode(['success' => false, 'message' => 'Formulación ID requerido']);
            return;
        }

        $resultado = $this->ProductosModel->calcular_insumos_para_proyecto($formulacion_id, $cubetas, $m2);

        if($resultado) {
            echo json_encode(['success' => true, 'datos' => $resultado]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al realizar el cálculo']);
        }
    }

    /**
     * Genera pre-órdenes de compra automáticas a partir de los insumos
     * faltantes detectados en calcular_insumos_ajax() (AJAX).
     *
     * Requiere el permiso 'produccion_preordenes'. Las pre-órdenes quedan en
     * estatus 'Pendiente' y requieren autorización de un administrador de
     * Compras antes de convertirse en una Orden de Compra real.
     */
    public function generar_preorden_ajax() {
        if (!tiene_permiso('produccion_preordenes')) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso para generar pre-órdenes de compra']);
            return;
        }

        $formulacion_id   = $this->input->post('formulacion_id');
        $insumos_json     = $this->input->post('insumos_faltantes');
        $notas            = $this->input->post('notas');
        $user_id          = $this->session->userdata('id');

        if (!$formulacion_id || !$insumos_json) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $insumos_faltantes = json_decode($insumos_json, true);
        if (!is_array($insumos_faltantes) || count($insumos_faltantes) === 0) {
            echo json_encode(['success' => false, 'message' => 'No se recibieron insumos faltantes válidos']);
            return;
        }

        $this->load->model('Compras/PreordenesModel');

        $resultado = $this->PreordenesModel->crear_preordenes_desde_faltantes(
            $insumos_faltantes,
            'produccion',
            $formulacion_id,
            $user_id,
            $notas ?: ('Generada automáticamente desde el cálculo de insumos de la formulación #' . $formulacion_id)
        );

        if (!empty($resultado['success'])) {
            $folios = array_map(function ($p) { return $p->folio ?? $p->id; }, $resultado['creadas'] ?? []);
            $this->init_controller->insert_log(
                'Pre-órdenes generadas desde producción (formulación #' . $formulacion_id . '): ' . implode(', ', $folios),
                $this->session->userdata('email') ?: $this->session->userdata('username'),
                'Compras'
            );
        }

        echo json_encode($resultado);
    }

    /**
     * Guarda los cambios editados inline en la tabla Excel del simulador (AJAX)
     * Puede crear nueva versión o actualizar los componentes de la actual.
     */
    public function guardar_excel_ajax() {
        $formulacion_id = $this->input->post('formulacion_id');
        $producto_id    = $this->input->post('producto_id');
        $es_nueva       = (int) $this->input->post('es_nueva');
        $componentes_json = $this->input->post('componentes');

        if (!$formulacion_id || !$producto_id || !$componentes_json) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $componentes = json_decode($componentes_json, true);
        if (!is_array($componentes) || count($componentes) === 0) {
            echo json_encode(['success' => false, 'message' => 'Sin componentes para guardar']);
            return;
        }

        // Obtener formulación original
        $formulacion_orig = $this->db->where('id', $formulacion_id)->get('formulaciones')->row();
        if (!$formulacion_orig) {
            echo json_encode(['success' => false, 'message' => 'Formulación no encontrada']);
            return;
        }

        $this->db->trans_start();

        if ($es_nueva) {
            // ── Crear nueva versión ──────────────────────────────────────
            $this->db->select_max('version');
            $this->db->where('producto_id', $producto_id);
            $max = $this->db->get('formulaciones')->row();
            $nueva_version = ($max->version ?? 0) + 1;

            $nueva_data = [
                'producto_id'          => $producto_id,
                'nombre_version'       => 'V' . $nueva_version . ' (desde Excel)',
                'descripcion'          => $formulacion_orig->descripcion,
                'comentarios'          => $formulacion_orig->comentarios,
                'cantidad_producida'   => $formulacion_orig->cantidad_producida,
                'unidad_produccion'    => $formulacion_orig->unidad_produccion,
                'rendimiento_m2_por_kg'=> $formulacion_orig->rendimiento_m2_por_kg,
                'cliente_id'           => $formulacion_orig->cliente_id,
                'costo_mano_obra'      => $formulacion_orig->costo_mano_obra,
                'costo_indirecto'      => $formulacion_orig->costo_indirecto,
                'version'              => $nueva_version,
                'es_activa'            => FALSE,
                'usuario_creacion'     => $this->session->userdata('user_id'),
                'fecha_creacion'       => date('Y-m-d H:i:s'),
            ];

            $this->db->insert('formulaciones', $nueva_data);
            $nueva_id = $this->db->insert_id();

            // Copiar componentes con valores editados
            foreach ($componentes as $comp) {
                $comp_orig = $this->db->where('id', $comp['id'])
                                      ->get('detalle_formulacion')->row();
                if (!$comp_orig) continue;

                $nuevo_comp = [
                    'formulacion_id'         => $nueva_id,
                    'insumo_id'              => $comp_orig->insumo_id,
                    'producto_id'            => $comp_orig->producto_id,
                    'tipo_componente'        => $comp_orig->tipo_componente,
                    'cantidad'               => $comp['cantidad'],
                    'unidad'                 => $comp_orig->unidad,
                    'porcentaje'             => $comp['porcentaje'] ?: null,
                    'grupo_color'            => $comp_orig->grupo_color,
                    'porcentaje_fase_acuosa' => $comp['porcentaje_fase_acuosa'] ?: null,
                    'kg_fase_acuosa'         => $comp_orig->kg_fase_acuosa,
                    'observaciones'          => $comp_orig->observaciones,
                    'orden'                  => $comp_orig->orden,
                    'costo_unitario'         => $comp_orig->costo_unitario,
                ];
                $this->db->insert('detalle_formulacion', $nuevo_comp);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                echo json_encode(['success' => false, 'message' => 'Error al crear nueva versión']);
                return;
            }

            echo json_encode([
                'success'              => true,
                'message'              => 'Nueva versión V' . $nueva_version . ' creada correctamente',
                'nueva_formulacion_id' => $nueva_id
            ]);

        } else {
            // ── Actualizar componentes de la versión actual ──────────────
            foreach ($componentes as $comp) {
                $this->db->where('id', $comp['id'])
                         ->update('detalle_formulacion', [
                             'cantidad'               => $comp['cantidad'],
                             'porcentaje'             => $comp['porcentaje'] ?: null,
                             'porcentaje_fase_acuosa' => $comp['porcentaje_fase_acuosa'] ?: null,
                         ]);
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar componentes']);
                return;
            }

            echo json_encode(['success' => true, 'message' => 'Formulación actualizada correctamente']);
        }
    }

    /**
     * Importación masiva de formulaciones desde Excel (AJAX)
     */
    /**
     * Importación masiva de formulaciones desde Excel (AJAX)
     * Formato esperado: "CHISA GLASS REF [REF]" | KILOS | [kg_total] en fila header
     * Columnas insumo: A=nombre | B=%BOM | C=%FaseAcuosa | D=nombre(repeat) | E=kg/cubeta
     */
    public function importar_formulacion_excel_ajax() {
        if (empty($_FILES['excel_file']['name'])) {
            echo json_encode(['success' => false, 'message' => 'No se ha seleccionado ningún archivo']);
            return;
        }

        $ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            echo json_encode(['success' => false, 'message' => 'Solo se aceptan archivos .xlsx o .xls']);
            return;
        }

        $tmp = $_FILES['excel_file']['tmp_name'];
        if (!is_readable($tmp)) {
            echo json_encode(['success' => false, 'message' => 'No se pudo leer el archivo subido']);
            return;
        }

        try {
            $resultado = $this->_procesar_importacion_excel($tmp, $_FILES['excel_file']['name'], $ext);
            echo json_encode($resultado['response']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Importación masiva desde CLI (carpeta entrenamiento/).
     * Uso: php index.php produccion/Productos importar_entrenamiento_cli
     */
    public function importar_entrenamiento_cli() {
        if (!is_cli()) {
            show_error('Este método solo puede ejecutarse desde la línea de comandos.');
            return;
        }

        $base = realpath(FCPATH . '../entrenamiento');
        if (!$base || !is_dir($base)) {
            echo "ERROR: No se encontró la carpeta entrenamiento/\n";
            return;
        }

        $archivos = [
            ['ruta' => $base . '/PASTA SERGIO.xlsx', 'opciones' => []],
            ['ruta' => $base . '/ficha masa roca.xlsx', 'opciones' => []],
            ['ruta' => $base . '/FICHAS DE PINTURA Y PASTA.xlsx', 'opciones' => ['hojas_excluir' => ['Hoja1']]],
            ['ruta' => $base . '/T034.xls', 'opciones' => [
                'hojas_solo'        => ['SEMANA 4'],
                'omitir_duplicados' => true,
            ]],
            ['ruta' => $base . '/archivo_principal_entrenamiento.xls', 'opciones' => [
                'hojas_excluir'     => ['FICHAS CHISA 2014'],
                'omitir_duplicados' => true,
            ]],
        ];

        $totales = ['importados' => 0, 'omitidos' => 0, 'errores' => 0];

        foreach ($archivos as $item) {
            if (!is_readable($item['ruta'])) {
                echo "OMITIDO (no existe): " . basename($item['ruta']) . "\n";
                continue;
            }

            echo "\n=== Importando: " . basename($item['ruta']) . " ===\n";
            $ext = strtolower(pathinfo($item['ruta'], PATHINFO_EXTENSION));

            try {
                $resultado = $this->_procesar_importacion_excel(
                    $item['ruta'],
                    basename($item['ruta']),
                    $ext,
                    $item['opciones']
                );
                $r = $resultado['response'];
                $totales['importados'] += count($r['importados'] ?? []);
                $totales['omitidos']   += count($r['omitidos'] ?? []);
                $totales['errores']    += count($r['errores'] ?? []);

                echo $r['message'] . "\n";
                if (!empty($r['omitidos'])) {
                    echo '  Omitidos (duplicados): ' . count($r['omitidos']) . "\n";
                }
                if (!empty($r['errores'])) {
                    foreach (array_slice($r['errores'], 0, 5) as $err) {
                        echo "  ERROR: $err\n";
                    }
                    if (count($r['errores']) > 5) {
                        echo '  ... y ' . (count($r['errores']) - 5) . " errores más\n";
                    }
                }
            } catch (Exception $e) {
                echo "ERROR FATAL: " . $e->getMessage() . "\n";
                $totales['errores']++;
            }
        }

        echo "\n=== RESUMEN FINAL ===\n";
        echo "Importados: {$totales['importados']}\n";
        echo "Omitidos:   {$totales['omitidos']}\n";
        echo "Errores:    {$totales['errores']}\n";
    }

    /**
     * Procesa un archivo Excel y persiste las formulaciones en BD.
     */
    private function _procesar_importacion_excel($ruta, $nombre_archivo, $ext, $opciones = []) {
        $productos_parseados = $this->_leer_excel_formulaciones($ruta, $ext, $opciones);

        if (empty($productos_parseados)) {
            return [
                'response' => [
                    'success'    => false,
                    'message'    => 'No se encontraron productos en el archivo (¿falta fila con "KILOS"?)',
                    'importados' => [],
                    'omitidos'   => [],
                    'errores'    => [],
                ],
            ];
        }

        $importados          = [];
        $omitidos            = [];
        $advertencias        = [];
        $errores             = [];
        $insumos_creados_cnt = 0;
        $omitir_duplicados   = !empty($opciones['omitir_duplicados']);

        foreach ($productos_parseados as $pdata) {
            if (empty($pdata['grupos'])) {
                $omitidos[] = ['ref' => $pdata['ref'], 'motivo' => 'Sin componentes'];
                continue;
            }

            if ($omitir_duplicados && $this->_formulacion_ya_existe($pdata)) {
                $omitidos[] = ['ref' => $pdata['ref'], 'motivo' => 'Duplicado existente'];
                continue;
            }

            $resultado = $this->_guardar_formulacion_importada($pdata);
            if ($resultado['success']) {
                $importados[] = $resultado['detalle'];
                if (!empty($resultado['advertencias'])) {
                    $advertencias = array_merge($advertencias, $resultado['advertencias']);
                }
                $insumos_creados_cnt += $resultado['insumos_creados'] ?? 0;
            } else {
                $errores[] = $resultado['message'];
            }
        }

        $this->db->insert('log_importaciones', [
            'archivo'                => $nombre_archivo,
            'usuario_id'             => (int)($this->session->userdata('user_id') ?: 0),
            'formulaciones_creadas'  => count($importados),
            'productos_importados'   => count(array_unique(array_column($importados, 'producto'))),
            'insumos_creados'        => $insumos_creados_cnt,
            'insumos_no_encontrados' => 0,
            'errores'                => count($errores) > 0 ? json_encode(array_merge($errores, $advertencias)) : null,
            'estatus'                => count($errores) === 0 ? 'Exitoso' : (count($importados) > 0 ? 'Parcial' : 'Error'),
            'fecha'                  => date('Y-m-d H:i:s'),
        ]);

        return [
            'response' => [
                'success'      => count($importados) > 0 || count($omitidos) > 0,
                'message'      => count($importados) . ' formulación(es) importada(s), ' . count($omitidos) . ' omitida(s)',
                'importados'   => $importados,
                'omitidos'     => $omitidos,
                'advertencias' => $advertencias,
                'errores'      => $errores,
            ],
        ];
    }

    /**
     * Detecta si ya existe una formulación idéntica (mismo producto, kg lote y fingerprint BOM).
     */
    private function _formulacion_ya_existe($pdata) {
        $producto = $this->_buscar_producto($pdata['ref']);
        if (!$producto) {
            return false;
        }

        $fingerprint_nueva = $this->_fingerprint_formulacion($pdata);

        $formulaciones = $this->db
            ->where('producto_id', $producto->id)
            ->where('ABS(cantidad_producida - ' . (float)$pdata['total_kg'] . ') <', 0.5)
            ->get('formulaciones')
            ->result();

        foreach ($formulaciones as $f) {
            $componentes = $this->db
                ->select('df.porcentaje, i.nombre_tecnico')
                ->from('detalle_formulacion df')
                ->join('insumos i', 'i.id = df.insumo_id', 'left')
                ->where('df.formulacion_id', $f->id)
                ->order_by('df.orden', 'ASC')
                ->get()
                ->result();

            $items = [];
            foreach ($componentes as $c) {
                $items[] = [
                    'nombre'     => $c->nombre_tecnico,
                    'porcentaje' => (float)$c->porcentaje,
                ];
            }

            if ($this->_fingerprint_formulacion(['grupos' => ['__default__' => $items]]) === $fingerprint_nueva) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera un hash de comparación para detectar formulaciones duplicadas.
     */
    private function _fingerprint_formulacion($pdata) {
        $items = [];
        foreach ($pdata['grupos'] ?? [] as $grupo => $componentes) {
            foreach ($componentes as $comp) {
                $nombre = $comp['nombre'] ?? '';
                if ($nombre === '') continue;
                $items[] = strtoupper(trim($nombre)) . ':' . round((float)($comp['porcentaje'] ?? 0), 2);
            }
        }
        sort($items);
        return md5(implode('|', $items));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MÉTODOS PRIVADOS — PARSER DE EXCEL
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lee el archivo Excel y devuelve un array con todos los productos/formulaciones
     * encontrados en todas las hojas.
     *
     * Formatos soportados:
     *   chisa_glass    — A=producto D/E=KILOS; componentes: A=nombre B=% C=%fase D=nombre E=kg
     *   bases_organicas— A=producto C=KILOS D=kg; componentes: A=nombre B=% C=nombre D=kg
     *   pintura_pasta  — A=producto C=KILOS E=kg (D vacía); componentes: A=nombre B=% C=nombre E=kg
     *   pasta_sergio   — B=KILOS D=kg (A vacía); componentes: A=% B=nombre D=kg
     *   masa_roca      — A=producto R6 D=kg (sin KILOS); componentes: A=nombre B=% D=kg
     */
    private function _leer_excel_formulaciones($ruta, $ext, $opciones = []) {
        $readerType = ($ext === 'xlsx') ? 'Xlsx' : 'Xls';
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($readerType);
        $reader->setReadDataOnly(false);
        $spreadsheet = $reader->load($ruta);

        $nombre_archivo = basename($ruta);
        $hojas_solo     = $opciones['hojas_solo'] ?? null;
        $hojas_excluir  = $opciones['hojas_excluir'] ?? [];
        $todos = [];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $nombre_hoja = $sheet->getTitle();

            if (is_array($hojas_solo) && !in_array($nombre_hoja, $hojas_solo, true)) {
                continue;
            }
            if (in_array($nombre_hoja, $hojas_excluir, true)) {
                continue;
            }

            $maxRow      = $sheet->getHighestRow();

            // Saltar hojas vacías o de soporte (Hoja2, Hoja3 sin datos)
            if ($maxRow < 5) continue;

            // Cargar filas con valores calculados (fórmulas resueltas)
            $matrix = [];
            for ($r = 1; $r <= $maxRow; $r++) {
                $fila = [];
                for ($c = 1; $c <= 9; $c++) {
                    try {
                        $cell = $sheet->getCellByColumnAndRow($c, $r);
                        $val  = $cell->getCalculatedValue();
                    } catch (Exception $e) {
                        $val = $sheet->getCellByColumnAndRow($c, $r)->getValue();
                    }
                    if ($val instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                        $val = $val->getPlainText();
                    }
                    $fila[$c] = $val;
                }
                $matrix[$r] = $fila;
            }

            // ── PERFIL PASTA_SERGIO: B=KILOS (sin nombre en A) ──────────────
            // Detectar antes del loop general porque usa col B en lugar de C-E
            $es_pasta_sergio = false;
            for ($r = 1; $r <= min($maxRow, 10); $r++) {
                if (strtoupper(trim((string)($matrix[$r][2] ?? ''))) === 'KILOS') {
                    $es_pasta_sergio = true;
                    break;
                }
            }
            if ($es_pasta_sergio) {
                $todos = array_merge($todos, $this->_parsear_pasta_sergio($matrix, $maxRow, $nombre_hoja, $nombre_archivo));
                continue;
            }

            // ── PERFIL MASA_ROCA: nombre en A fila 1 o 6, total en D, sin KILOS ─
            $es_masa_roca = false;
            $nombre_prod_masa = trim((string)($matrix[1][1] ?? ''));
            if ($nombre_prod_masa !== '' && !is_numeric($matrix[1][1] ?? null)) {
                // Verificar que en alguna de las primeras 10 filas col B ≈ 1 (fila de total)
                for ($r = 2; $r <= min($maxRow, 15); $r++) {
                    $b = $matrix[$r][2] ?? null;
                    if (is_numeric($b) && (float)$b >= 0.99 && (float)$b <= 1.01) {
                        // Y que NO haya ningún KILOS en toda la hoja
                        $tiene_kilos = false;
                        for ($rk = 1; $rk <= min($maxRow, 20); $rk++) {
                            for ($ck = 2; $ck <= 6; $ck++) {
                                if (strtoupper(trim((string)($matrix[$rk][$ck] ?? ''))) === 'KILOS') {
                                    $tiene_kilos = true; break 2;
                                }
                            }
                        }
                        if (!$tiene_kilos) { $es_masa_roca = true; break; }
                    }
                }
            }
            if ($es_masa_roca) {
                $todos = array_merge($todos, $this->_parsear_masa_roca($matrix, $maxRow, $nombre_hoja, $nombre_archivo));
                continue;
            }

            // ── LOOP GENERAL: formatos chisa_glass / bases_organicas / pintura_pasta ──
            $bloques    = [];
            $cli_nombre = null;
            $cli_cubetas= 1;
            $refs_vistos = []; // deduplicar bloques con mismo nombre en la misma hoja

            $ultima_col_a_texto = '';
            for ($r = 1; $r <= $maxRow; $r++) {
                $col_a_raw = $matrix[$r][1] ?? '';
                $col_a     = trim((string)$col_a_raw);
                $col_b_raw = $matrix[$r][2] ?? null;
                $col_b     = trim((string)($col_b_raw ?? ''));

                // Saltar filas donde Col A es serial de fecha Excel
                if (is_int($col_a_raw) && (int)$col_a_raw > 40000) {
                    $ultima_col_a_texto = '';
                    continue;
                }

                // Detectar "N CUBETA(S) [TEXTO CLIENTE]" — solo en col A
                if (preg_match('/^(\d+)\s+(CUBETAS?|TAMBOS?)\b/i', $col_a, $m)) {
                    $cli_cubetas = (int) $m[1];
                    $resto = trim(substr($col_a, strlen($m[0])));
                    if ($resto !== '') {
                        $cli_nombre = trim(preg_replace('/^(VENTA\s+)?/i', '', $resto));
                    }
                    $ultima_col_a_texto = '';
                    continue;
                }

                // Detectar fila de producto: "KILOS" en cols 3-5
                $kilos_col = null;
                for ($kc = 3; $kc <= 5; $kc++) {
                    if (strtoupper(trim((string)($matrix[$r][$kc] ?? ''))) === 'KILOS') {
                        $kilos_col = $kc;
                        break;
                    }
                }

                // Ignorar encabezados de sección ("HOJA Nº 1") o sin nombre
                $col_a_upper = strtoupper($col_a);
                if ($kilos_col !== null && (strpos($col_a_upper, 'HOJA') === 0 || $col_a === '')) {
                    $kilos_col = null;
                }

                // Ignorar si Col B es serial de fecha
                if ($kilos_col !== null && is_int($col_b_raw) && (int)$col_b_raw > 40000) {
                    $kilos_col = null;
                }

                if ($kilos_col !== null) {
                    $total_kg = (float) ($matrix[$r][$kilos_col + 1] ?? 0);

                    // Perfil pintura_pasta: KILOS en col C pero kg en col E (col D vacía)
                    $formato = ($kilos_col === 3) ? 'bases_organicas' : 'chisa_glass';
                    if ($kilos_col === 3 && $total_kg <= 0) {
                        $total_kg_e = (float) ($matrix[$r][$kilos_col + 2] ?? 0);
                        if ($total_kg_e > 0) {
                            $total_kg = $total_kg_e;
                            $formato  = 'pintura_pasta';
                        }
                    }

                    if ($total_kg <= 0) {
                        $ultima_col_a_texto = $col_a;
                        continue;
                    }

                    // Construir referencia del producto
                    $ref = '';
                    if ($col_a !== '' && $ultima_col_a_texto !== ''
                        && preg_match('/^[Yy]\s+\S/', $col_a)) {
                        $ref = $ultima_col_a_texto . ' ' . $col_a;
                    } elseif ($formato === 'chisa_glass' && strpos($col_a_upper, 'CHISA') !== false
                              && $col_b !== '' && !is_numeric($col_b_raw)) {
                        $ref = $col_a . ' ' . $col_b;
                    } else {
                        $ref = $col_a;
                    }
                    $ref = trim($ref);

                    // Deduplicar: si ya vimos este mismo producto en esta hoja, omitir
                    $clave_dedup = strtoupper($ref) . '|' . round($total_kg, 1);
                    if (isset($refs_vistos[$clave_dedup])) {
                        $ultima_col_a_texto = '';
                        continue;
                    }
                    $refs_vistos[$clave_dedup] = true;

                    $bloques[] = [
                        'row'           => $r,
                        'ref'           => $ref,
                        'total_kg'      => $total_kg,
                        'cliente'       => $cli_nombre,
                        'cubetas'       => $cli_cubetas,
                        'descripcion'   => '',
                        'grupos'        => [],
                        'formato'       => $formato,
                        'kilos_col'     => $kilos_col,
                        'hoja_origen'   => $nombre_hoja,
                        'archivo_origen'=> $nombre_archivo,
                    ];
                    $cli_nombre  = null;
                    $cli_cubetas = 1;
                    $ultima_col_a_texto = '';
                } else {
                    if ($col_a !== '' && !is_numeric($col_a_raw)) {
                        $ultima_col_a_texto = $col_a;
                    }
                }
            }

            // Para cada bloque, extraer descripción y componentes
            for ($bi = 0; $bi < count($bloques); $bi++) {
                $r_ini = $bloques[$bi]['row'] + 1;
                $r_fin = isset($bloques[$bi + 1])
                    ? $bloques[$bi + 1]['row'] - 1
                    : $maxRow;

                // ¿Hay descripción en la primera fila? (texto en Col A, Col B vacía, no es insumo)
                $fila_desc  = $matrix[$r_ini] ?? [];
                $txt_desc   = trim((string)($fila_desc[1] ?? ''));
                $val_b_desc = $fila_desc[2] ?? null;
                $es_texto_puro = $txt_desc !== '' && ($val_b_desc === null || $val_b_desc === '');
                $es_solo_fecha = is_int($val_b_desc ?? null) && ($val_b_desc ?? 0) > 40000;
                if ($es_texto_puro && !$es_solo_fecha) {
                    $bloques[$bi]['descripcion'] = $txt_desc;
                    $r_ini++;
                }

                // Parsear grupos e insumos (el desplazamiento de columnas depende del formato)
                $grupos       = [];
                $grupo_actual = '__default__';
                $fmt          = $bloques[$bi]['formato'] ?? 'chisa_glass';

                // Offsets por formato:
                //   chisa_glass:    B=prop, C=%fase, D=nombre_repetido, E=kg
                //   bases_organicas:B=prop, C=nombre, D=kg (precio en E)
                //   pintura_pasta:  B=prop, C=nombre, E=kg (D vacía)
                switch ($fmt) {
                    case 'bases_organicas':
                        $col_nombre_offset = 3; $col_kg_offset = 4; $col_fa_offset = null; break;
                    case 'pintura_pasta':
                        $col_nombre_offset = 3; $col_kg_offset = 5; $col_fa_offset = null; break;
                    default: // chisa_glass
                        $col_nombre_offset = 4; $col_kg_offset = 5; $col_fa_offset = 3;    break;
                }

                for ($r = $r_ini; $r <= $r_fin; $r++) {
                    $row = $matrix[$r];

                    $col_a = trim((string)($row[1] ?? ''));
                    $col_b = $row[2]; // proporción 0-1 (BOM)
                    $col_c = ($col_fa_offset !== null) ? $row[$col_fa_offset] : null;
                    $col_d = trim((string)($row[$col_nombre_offset] ?? ''));
                    $col_e = $row[$col_kg_offset]; // kg/lote

                    // Saltar filas completamente vacías
                    if ($col_a === '' && ($col_b === null || $col_b === '') && ($col_e === null || $col_e === '')) {
                        continue;
                    }
                    // Saltar fechas seriales en Col A
                    if (is_int($row[1] ?? null) && ($row[1] ?? 0) > 40000) continue;
                    // Saltar filas de cliente ("N cubeta(s) / N tambo(s) …")
                    if ($col_a !== '' && preg_match('/^\d+\s+(CUBETAS?|TAMBOS?)\b/i', $col_a)) continue;

                    $b_num = is_numeric($col_b) ? (float) $col_b : null;

                    // Fila de total: Col A vacía y Col B ≈ 1.0
                    if ($col_a === '' && $b_num !== null && $b_num >= 0.99 && $b_num <= 1.02) {
                        continue;
                    }

                    // ── CORRECCIÓN BUG BASE ROW ──────────────────────────────────────
                    // En formato chisa_glass la fila "BLANCO | 1.0 | pct_fa | BLANCO | kg"
                    // es la referencia al semielaborado BASE, NO un componente de color.
                    // Si B ≈ 1.0 con texto en A (y no es la fila total) → omitir.
                    if ($fmt === 'chisa_glass' && $col_a !== '' && $b_num !== null && $b_num >= 0.995) {
                        continue;
                    }

                    // Fila de grupo: Col A tiene texto, Col B vacía/nula
                    $b_vacia = ($col_b === null || $col_b === '' || $col_b === 0);
                    if ($col_a !== '' && $b_vacia && ($col_d === $col_a || $col_d === '')) {
                        $grupo_actual = $col_a;
                        if (!isset($grupos[$grupo_actual])) {
                            $grupos[$grupo_actual] = [];
                        }
                        continue;
                    }

                    // Fila de insumo: Col A nombre, Col B proporción, Col E kg > 0
                    $pct_raw = is_numeric($col_b) ? (float) $col_b : null;
                    $kg      = is_numeric($col_e) ? (float) $col_e : null;
                    $pct_fa  = is_numeric($col_c) ? (float) $col_c : null;

                    if ($col_a !== '' && $pct_raw !== null && $kg !== null && $kg > 0) {
                        if (!isset($grupos[$grupo_actual])) {
                            $grupos[$grupo_actual] = [];
                        }
                        $pct_pct    = round($pct_raw * 100, 4);
                        $pct_fa_pct = ($pct_fa !== null) ? round($pct_fa * 100, 4) : null;

                        $grupos[$grupo_actual][] = [
                            'nombre'    => $this->_normalizar_nombre_insumo($col_a),
                            'porcentaje'=> $pct_pct,
                            'pct_fase'  => $pct_fa_pct,
                            'kg'        => round($kg, 6),
                        ];
                    }
                }

                $bloques[$bi]['grupos'] = $grupos;
                // Solo agregar bloques con al menos un componente
                if (!empty($grupos)) {
                    $todos[] = $bloques[$bi];
                }
            }
        }

        return $todos;
    }

    /**
     * Convierte un valor de celda a porcentaje (0–100 float).
     * Maneja: 0.3842 (raw decimal), "38.42%", "38.42", NULL.
     * Devuelve NULL si no es interpretable como %.
     */
    private function _a_porcentaje($val) {
        if ($val === null || $val === '') return null;

        if (is_numeric($val)) {
            $n = (float) $val;
            // Si es < 1.5 asumimos que está en formato decimal (0.3842 → 38.42%)
            if ($n > 0 && $n < 1.5) return round($n * 100, 4);
            // Si es > 1.5 y <= 100 asumimos que ya es porcentaje
            if ($n >= 1.5 && $n <= 100) return $n;
        }

        if (is_string($val)) {
            $clean = str_replace(['%', ' '], '', $val);
            if (is_numeric($clean)) {
                $n = (float) $clean;
                if ($n >= 0 && $n <= 100) return $n;
            }
        }

        return null;
    }

    /**
     * Guarda una formulación parseada del Excel en la BD.
     */
    private function _guardar_formulacion_importada($pdata) {
        $ref            = $pdata['ref'];
        $total_kg       = $pdata['total_kg'];
        $cliente_nom    = $pdata['cliente'] ?? null;
        $descripcion    = $pdata['descripcion'] ?? '';
        $grupos         = $pdata['grupos'] ?? [];
        $hoja_origen    = $pdata['hoja_origen'] ?? null;
        $archivo_origen = $pdata['archivo_origen'] ?? null;
        $rendimiento_m2 = $pdata['rendimiento_m2'] ?? null;

        $advertencias = [];

        // ── Buscar producto en BD; auto-crearlo si no existe ─────────────
        $producto = $this->_buscar_producto($ref);
        $producto_creado = false;
        if (!$producto) {
            $producto_id = $this->_auto_crear_producto($ref, $total_kg);
            if (!$producto_id) {
                return ['success' => false, 'message' => "No se pudo crear el producto para referencia: \"$ref\""];
            }
            $producto = $this->db->get_where('productos', ['id' => $producto_id])->row();
            $producto_creado = true;
            $advertencias[] = "Producto creado automáticamente: \"{$producto->nombre}\" (código {$producto->codigo}). Completa sus datos en el catálogo.";
        }

        // ── Buscar o dejar NULL el cliente ────────────────────────────────
        $cliente_id = null;
        if ($cliente_nom) {
            $cli = $this->db
                ->group_start()
                    ->like('razon_social', $cliente_nom)
                    ->or_like('nombre_comercial', $cliente_nom)
                ->group_end()
                ->get('clientes')->row();
            if ($cli) {
                $cliente_id = $cli->id;
            } else {
                $advertencias[] = "Cliente \"$cliente_nom\" no encontrado; formulación guardada sin cliente.";
            }
        }

        // ── Siguiente número de versión ───────────────────────────────────
        $this->db->select_max('version');
        $this->db->where('producto_id', $producto->id);
        $max_v   = $this->db->get('formulaciones')->row();
        $version = ($max_v->version ?? 0) + 1;

        $this->db->trans_start();
        $insumos_nuevos = 0;

        // ── Crear formulación ─────────────────────────────────────────────
        $this->db->insert('formulaciones', [
            'producto_id'        => $producto->id,
            'cliente_id'         => $cliente_id,
            'nombre_version'        => 'V' . $version . ($cliente_nom ? " – $cliente_nom" : ''),
            'descripcion'           => $descripcion,
            'comentarios'           => implode(' | ', array_filter([
                $cliente_nom ? "Cliente: $cliente_nom" : 'Importado desde Excel.',
                $archivo_origen ? "Archivo: $archivo_origen" : null,
                $hoja_origen    ? "Hoja: $hoja_origen"    : null,
            ])),
            'cantidad_producida'    => $total_kg,
            'unidad_produccion'     => 'Kg',
            'rendimiento_m2_por_kg' => $rendimiento_m2,
            'version'            => $version,
            'es_activa'          => FALSE,
            'usuario_creacion'   => $this->session->userdata('user_id'),
            'fecha_creacion'     => date('Y-m-d H:i:s'),
        ]);
        $formulacion_id = $this->db->insert_id();

        // ── Insertar componentes ──────────────────────────────────────────
        $orden           = 0;
        $num_componentes = 0;

        foreach ($grupos as $grupo_nombre => $items) {
            $grupo_label = ($grupo_nombre === '__default__') ? null : $grupo_nombre;

            foreach ($items as $item) {
                // Buscar insumo; si no existe, crearlo
                $insumo = $this->_buscar_insumo($item['nombre']);
                if (!$insumo) {
                    $nuevo_codigo = 'IMP-' . strtoupper(substr(md5($item['nombre'] . microtime(true) . rand()), 0, 8));
                    $this->db->insert('insumos', [
                        'nombre_tecnico' => $item['nombre'],
                        'codigo'         => $nuevo_codigo,
                        'unidad_medida'  => 'Kg',
                        'estatus'        => 'Activo',
                        'precio_promedio'=> 0,
                    ]);
                    $insumo_id = $this->db->insert_id();
                    $insumos_nuevos++;
                } else {
                    $insumo_id = $insumo->id;
                }

                $this->db->insert('detalle_formulacion', [
                    'formulacion_id'         => $formulacion_id,
                    'insumo_id'              => $insumo_id,
                    'producto_id'            => null,
                    'tipo_componente'        => 'Insumo',
                    'cantidad'               => $item['kg'],
                    'unidad'                 => 'Kg',
                    'porcentaje'             => $item['porcentaje'],
                    'grupo_color'            => $grupo_label,
                    'porcentaje_fase_acuosa' => $item['pct_fase'],
                    'kg_fase_acuosa'         => null,
                    'observaciones'          => null,
                    'orden'                  => $orden++,
                    'costo_unitario'         => isset($insumo->precio_promedio) ? $insumo->precio_promedio : 0,
                ]);
                $num_componentes++;
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => "Error de BD al guardar formulación para \"$ref\""];
        }

        return [
            'success'         => true,
            'advertencias'    => $advertencias,
            'insumos_creados' => $insumos_nuevos,
            'detalle'         => [
                'ref'              => $ref,
                'producto'         => $producto->nombre,
                'producto_creado'  => $producto_creado,
                'version'          => $version,
                'formulacion_id'   => $formulacion_id,
                'cliente'          => $cliente_nom,
                'num_componentes'  => $num_componentes,
                'num_grupos'       => count(array_filter(array_keys($grupos), function($k) { return $k !== '__default__'; })),
            ],
        ];
    }

    /**
     * Busca un producto por su referencia de Excel.
     * Busca en: codigo, alias, nombre (contiene la ref).
     */
    private function _buscar_producto($ref) {
        // Limpiar el ref: quitar el prefijo "CHISA GLASS REF " si viene completo
        $ref_limpio = trim(preg_replace('/^CHISA\s+GLASS\s+REF\s+/i', '', $ref));
        if ($ref_limpio === '') $ref_limpio = $ref;

        // Búsqueda exacta primero
        $producto = $this->db
            ->group_start()
                ->where('codigo', $ref_limpio)
                ->or_where('alias', $ref_limpio)
                ->or_like('nombre', $ref_limpio)
            ->group_end()
            ->where('tipo_producto', 'Fabricado')
            ->get('productos')->row();

        if (!$producto && $ref_limpio !== $ref) {
            // Intento con el texto completo
            $producto = $this->db
                ->group_start()
                    ->like('nombre', $ref)
                    ->or_like('alias', $ref)
                ->group_end()
                ->where('tipo_producto', 'Fabricado')
                ->get('productos')->row();
        }

        return $producto;
    }

    /**
     * Normaliza el nombre de un insumo antes de buscarlo/insertarlo:
     * colapsa espacios múltiples y aplica la tabla de sinónimos conocidos.
     */
    private function _normalizar_nombre_insumo($nombre) {
        // Colapsar espacios internos múltiples y trim
        $n = trim(preg_replace('/\s{2,}/', ' ', $nombre));

        // Tabla de sinónimos: nombre exacto en Excel → nombre canónico en BD
        $sinonimos = [
            'TRPOLIFOSFATO DE POTASIO'   => 'TRIPOLIFOSFATO DE POTASIO',
            'MONOETILIENGLICOL'          => 'MONOETILENGLICOL',
            'MONOETILENGLICOL'           => 'MONOETILENGLICOL',
            'CAOLIN M-325'               => 'CAOLIN M-325',
            'CAOLIN M 325'               => 'CAOLIN M-325',
            'CARBONATO M-325'            => 'CARBONATO M-325',
            'CARBONATO  M-325'           => 'CARBONATO M-325',
            'CARBONATO M 325'            => 'CARBONATO M-325',
            'CEMENTOGRIS'                => 'CEMENTO GRIS',
            'ACRONAL       295-D'        => 'ACRONAL 295-D',
            'ACRONAL 295 D'              => 'ACRONAL 295-D',
        ];

        $n_upper = strtoupper($n);
        foreach ($sinonimos as $raw => $canon) {
            if (strtoupper(trim(preg_replace('/\s{2,}/', ' ', $raw))) === $n_upper) {
                return $canon;
            }
        }
        return $n;
    }

    /**
     * Busca un insumo por nombre técnico (case-insensitive, con sinónimos).
     * Primero busca el nombre normalizado exacto, luego LIKE amplio.
     */
    private function _buscar_insumo($nombre) {
        $nombre = $this->_normalizar_nombre_insumo($nombre);

        // Búsqueda exacta primero (más precisa)
        $exact = $this->db->where('LOWER(nombre_tecnico)', strtolower($nombre))->get('insumos')->row();
        if ($exact) return $exact;

        // Fallback: LIKE con el nombre normalizado
        return $this->db
            ->like('nombre_tecnico', $nombre, 'both')
            ->get('insumos')->row();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PARSERS ESPECIALIZADOS — Formatos no estándar
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Parser Perfil D — PASTA SERGIO
     * Header: B=KILOS, D=kg_lote (A vacía)
     * Componentes: A=% BOM (decimal), B=nombre, D=kg
     */
    private function _parsear_pasta_sergio($matrix, $maxRow, $hoja, $archivo) {
        $todos = [];
        // Nombre del producto = nombre del archivo (sin extensión) o de la hoja
        $ref_prod = pathinfo($archivo, PATHINFO_FILENAME);

        for ($r = 1; $r <= $maxRow; $r++) {
            if (strtoupper(trim((string)($matrix[$r][2] ?? ''))) !== 'KILOS') continue;
            $total_kg = (float)($matrix[$r][4] ?? 0);
            if ($total_kg <= 0) continue;

            $grupos  = [];
            $default = [];
            for ($ri = $r + 1; $ri <= min($r + 30, $maxRow); $ri++) {
                $pct_raw = $matrix[$ri][1] ?? null;
                $nombre  = trim((string)($matrix[$ri][2] ?? ''));
                $kg_val  = (float)($matrix[$ri][4] ?? 0);

                if (!is_numeric($pct_raw) || $nombre === '') continue;
                $pct = (float)$pct_raw;
                if ($pct >= 0.99 && $pct <= 1.01) break; // fila de total

                $default[] = [
                    'nombre'    => $this->_normalizar_nombre_insumo($nombre),
                    'porcentaje'=> round($pct * 100, 4),
                    'pct_fase'  => null,
                    'kg'        => round($kg_val, 6),
                ];
            }
            if (!empty($default)) {
                $grupos['__default__'] = $default;
                $todos[] = [
                    'ref'           => $ref_prod,
                    'total_kg'      => $total_kg,
                    'cliente'       => null,
                    'cubetas'       => 1,
                    'descripcion'   => '',
                    'grupos'        => $grupos,
                    'formato'       => 'pasta_sergio',
                    'hoja_origen'   => $hoja,
                    'archivo_origen'=> $archivo,
                    'rendimiento_m2'=> null,
                ];
            }
            break; // solo un bloque por hoja
        }
        return $todos;
    }

    /**
     * Parser Perfil E — MASA ROCA
     * Header: A=nombre (fila 1 o 6), D=kg_lote (sin marcador KILOS)
     * Componentes: A=nombre, B=%, D=kg
     * Extra: línea "M2 X.XX" → rendimiento_m2_por_kg
     */
    private function _parsear_masa_roca($matrix, $maxRow, $hoja, $archivo) {
        $todos = [];
        $ref_prod   = trim((string)($matrix[1][1] ?? ''));
        $total_kg   = 0.0;
        $rendimiento_m2 = null;
        $r_inicio   = 1;

        // Buscar fila del producto (nombre en A, kg en D)
        for ($r = 1; $r <= min($maxRow, 10); $r++) {
            $a = trim((string)($matrix[$r][1] ?? ''));
            $d = (float)($matrix[$r][4] ?? 0);
            if ($a !== '' && !is_numeric($matrix[$r][1]) && $d > 0
                && strtoupper($a) !== 'P.U' && strtoupper($a) !== 'TOTAL') {
                // Verificar que no sea serial de fecha en A
                if (is_int($matrix[$r][1] ?? null) && (int)$matrix[$r][1] > 40000) continue;
                $ref_prod = $a;
                $total_kg = $d;
                $r_inicio = $r + 1;
                break;
            }
        }
        if ($total_kg <= 0) return $todos;

        $default = [];
        for ($r = $r_inicio; $r <= min($r_inicio + 20, $maxRow); $r++) {
            $a   = trim((string)($matrix[$r][1] ?? ''));
            $b   = $matrix[$r][2] ?? null;
            $kg  = (float)($matrix[$r][4] ?? 0);
            $b_n = is_numeric($b) ? (float)$b : null;

            // Fila de total: B = 1.0
            if ($b_n !== null && $b_n >= 0.99 && $b_n <= 1.01) break;

            // Saltar filas auxiliares (KG DE …, MANO, …) — M2 se busca después
            if ($a !== '' && preg_match('/^(KG\s+DE|MANO|SELLADOR|M\s*2)/i', $a)) continue;

            if ($a !== '' && $b_n !== null && $b_n > 0 && $kg > 0) {
                $default[] = [
                    'nombre'    => $this->_normalizar_nombre_insumo($a),
                    'porcentaje'=> round($b_n * 100, 4),
                    'pct_fase'  => null,
                    'kg'        => round($kg, 6),
                ];
            }
        }

        // Buscar rendimiento m² en cualquier fila de la hoja (suele estar después del total)
        for ($r = 1; $r <= $maxRow; $r++) {
            $a = trim((string)($matrix[$r][1] ?? ''));
            if ($a !== '' && preg_match('/^M\s*2\s+([\d.]+)/i', $a, $mm)) {
                $rendimiento_m2 = round((float)$mm[1] / $total_kg, 4);
                break;
            }
        }

        if (!empty($default)) {
            $todos[] = [
                'ref'           => $ref_prod,
                'total_kg'      => $total_kg,
                'cliente'       => null,
                'cubetas'       => 1,
                'descripcion'   => '',
                'grupos'        => ['__default__' => $default],
                'formato'       => 'masa_roca',
                'hoja_origen'   => $hoja,
                'archivo_origen'=> $archivo,
                'rendimiento_m2'=> $rendimiento_m2,
            ];
        }
        return $todos;
    }

    /**
     * Crea un producto básico en el catálogo a partir de la referencia del Excel.
     * Devuelve el nuevo ID o null en caso de error.
     */
    private function _auto_crear_producto($ref, $kg_lote = 0) {
        // Determinar categoría por palabras clave en el nombre
        $ref_upper = strtoupper($ref);
        if (strpos($ref_upper, 'CHISA GLASS') !== false || strpos($ref_upper, 'RECUBRIMIENTO') !== false
            || strpos($ref_upper, 'CHISA MAR') !== false || strpos($ref_upper, 'MASA ROCA') !== false) {
            $categoria_id = 2; // Recubrimientos
        } elseif (strpos($ref_upper, 'ESMALTE') !== false) {
            $categoria_id = 8; // Esmaltes
        } elseif (strpos($ref_upper, 'IMPERMEABI') !== false) {
            $categoria_id = 9; // Impermeabilizantes
        } elseif (strpos($ref_upper, 'SELLADOR') !== false || strpos($ref_upper, 'ACABADO') !== false) {
            $categoria_id = 5; // Selladores
        } elseif (strpos($ref_upper, 'PASTA') !== false || strpos($ref_upper, 'CASCARA') !== false
               || strpos($ref_upper, 'GRANO') !== false || strpos($ref_upper, 'CERO ') !== false) {
            $categoria_id = 4; // Pastas/Texturizados
        } elseif (strpos($ref_upper, 'VINILICA') !== false || strpos($ref_upper, 'VINYL') !== false) {
            $categoria_id = 7; // Vinílicas
        } elseif (strpos($ref_upper, 'RUGOSO') !== false || strpos($ref_upper, 'PINTU FLEX') !== false
               || strpos($ref_upper, 'FLEX') !== false) {
            $categoria_id = 1; // Pinturas especiales
        } elseif (strpos($ref_upper, 'PREPARADOR') !== false || strpos($ref_upper, 'SOLUCION') !== false
               || strpos($ref_upper, 'SOLUCIÓN') !== false || strpos($ref_upper, 'BASE ORGANICA') !== false) {
            $categoria_id = 3; // Preparadores
        } else {
            $categoria_id = 1; // Pinturas (default)
        }

        // Generar código único basado en la referencia
        $codigo_base = strtoupper(preg_replace('/[^A-Z0-9]+/', '-', $ref_upper));
        $codigo_base = trim($codigo_base, '-');
        $codigo_base = substr($codigo_base, 0, 30);
        // Si ya existe ese código, añadir sufijo numérico
        $codigo = $codigo_base;
        $sufijo = 1;
        while ($this->db->where('codigo', $codigo)->count_all_results('productos') > 0) {
            $codigo = $codigo_base . '-' . $sufijo;
            $sufijo++;
        }

        $ok = $this->db->insert('productos', [
            'codigo'         => $codigo,
            'nombre'         => $ref,
            'alias'          => $ref,
            'descripcion'    => 'Producto importado desde Excel. Completa los datos en el catálogo.',
            'categoria_id'   => $categoria_id,
            'tipo_producto'  => 'Fabricado',
            'unidad_venta'   => 'Kg',
            'contenido_neto' => $kg_lote > 0 ? $kg_lote : null,
            'unidad_contenido' => 'Kg',
            'estatus'        => 'Activo',
            'usuario_creacion' => (int)($this->session->userdata('user_id') ?: 0),
            'fecha_creacion' => date('Y-m-d H:i:s'),
        ]);

        return $ok ? $this->db->insert_id() : null;
    }
}
