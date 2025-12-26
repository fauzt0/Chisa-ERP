<?php
/**
 * Productos - Controlador de gestión de productos terminados
 * 
 * Gestiona productos fabricados y de reventa con formulaciones (BOM)
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Productos extends CI_Controller {
    
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
        $this->viewData['breadcrumb'] = 'Inicio > Producción > Productos';
        
        // Obtener estadísticas
        $stats = $this->ProductosModel->get_estadisticas();
        $this->viewData['response'] = [
            'stats' => $stats,
            'puede_ver_costos' => puede_ver_costos()
        ];
        
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'produccion/productos/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Lista de productos para DataTables (AJAX)
     */
    public function lista_ajax() {
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
                           onerror="this.src=\'' . base_url('assets/img/no-image.png') . '\'">';
            
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
            'nombre_version' => $this->input->post('nombre_version'),
            'descripcion' => $this->input->post('descripcion'),
            'cantidad_producida' => $this->input->post('cantidad_producida'),
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
            'observaciones' => $this->input->post('observaciones'),
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
        
        if(!$producto_id) {
            echo json_encode(['success' => false, 'message' => 'Producto ID requerido']);
            return;
        }
        
        $formulaciones = $this->ProductosModel->get_historial_formulaciones($producto_id, $busqueda);
        
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
     * Obtiene productos base (no variantes) para selector (AJAX)
     */
    public function get_productos_base_ajax() {
        $this->db->select('id, codigo, nombre');
        $this->db->where('es_variante', 0);
        $this->db->where('estatus', 'Activo');
        $this->db->order_by('nombre', 'ASC');
        $productos = $this->db->get('productos')->result();
        
        echo json_encode(['success' => true, 'productos' => $productos]);
    }
}
