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
     * Guarda una formulación completa (cabecera + componentes) en una sola llamada
     * transaccional. Modo 'nueva' crea versión nueva; 'actualizar' sobreescribe la actual.
     */
    public function guardar_formulacion_completa_ajax() {
        $modo           = $this->input->post('modo') === 'actualizar' ? 'actualizar' : 'nueva';
        $formulacion_id = $this->input->post('formulacion_id') ?: null;
        $producto_id    = $this->input->post('producto_id');
        $componentes_json = $this->input->post('componentes');

        if (empty($producto_id) || empty($this->input->post('cantidad_producida'))) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos: producto o cantidad producida.']);
            return;
        }

        $componentes = json_decode((string)$componentes_json, true);
        if (!is_array($componentes) || count($componentes) === 0) {
            echo json_encode(['success' => false, 'message' => 'Agregue al menos un componente.']);
            return;
        }

        if ($modo === 'actualizar' && !$formulacion_id) {
            echo json_encode(['success' => false, 'message' => 'No hay una versión seleccionada para actualizar.']);
            return;
        }

        $cabecera = [
            'producto_id'           => $producto_id,
            'cliente_id'            => $this->input->post('cliente_id') ?: null,
            'referencia_cliente'    => $this->input->post('referencia_cliente') ?: null,
            'nombre_version'        => $this->input->post('nombre_version') ?: 'V1',
            'descripcion'           => $this->input->post('descripcion'),
            'comentarios'           => $this->input->post('comentarios') ?: null,
            'cantidad_producida'    => $this->input->post('cantidad_producida'),
            'unidad_produccion'     => $this->input->post('unidad_produccion') ?: 'Kg',
            'rendimiento_m2_por_kg' => $this->input->post('rendimiento_m2_por_kg') ?: null,
            'costo_mano_obra'       => $this->input->post('costo_mano_obra') ?: 0,
            'costo_indirecto'       => $this->input->post('costo_indirecto') ?: 0,
            'usuario_creacion'      => $this->session->userdata('user_id'),
        ];

        $res = $this->ProductosModel->guardar_formulacion_completa($cabecera, $componentes, $modo, $formulacion_id);
        echo json_encode($res);
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
        // Incluye nombres secundarios (insumos.alias + tabla insumos_alias) para que el
        // picker permita buscar insumos por como los conocen los trabajadores en planta.
        $this->db->select("i.id, i.codigo, i.nombre_tecnico, i.alias, i.unidad_medida, i.precio_promedio, i.stock_actual,
            (SELECT GROUP_CONCAT(ia.alias SEPARATOR ' | ') FROM insumos_alias ia WHERE ia.insumo_id = i.id AND ia.estatus = 'Activo') AS alias_secundarios", false);
        $this->db->from('insumos i');
        $this->db->where('i.estatus', 'Activo');
        $this->db->order_by('i.nombre_tecnico', 'ASC');
        $insumos = $this->db->get()->result();

        // Texto de búsqueda combinado (nombre + código + todos los alias) para el frontend.
        foreach ($insumos as $ins) {
            $ins->buscar = trim(implode(' ', array_filter([
                $ins->nombre_tecnico, $ins->codigo, $ins->alias, $ins->alias_secundarios,
            ])));
        }

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
     * Importa UN archivo Excel arbitrario desde CLI, omitiendo duplicados por defecto.
     * Uso: php index.php produccion/Productos importar_archivo_cli "doc/CHISA GLASS 2021.xls"
     * Opcional: agregar "todo" como 2º segmento para NO omitir duplicados.
     */
    public function importar_archivo_cli($ruta_rel = null, $modo = 'dedup') {
        if (!is_cli()) {
            show_error('Este método solo puede ejecutarse desde la línea de comandos.');
            return;
        }
        // La ruta se pasa por variable de entorno IMPORT_FILE para evitar que CI
        // parta rutas con espacios/slashes en segmentos de URI.
        $env_file = getenv('IMPORT_FILE');
        if ($env_file) { $ruta_rel = $env_file; }
        $env_modo = getenv('IMPORT_MODE');
        if ($env_modo) { $modo = $env_modo; }
        $ruta_rel = $ruta_rel ? urldecode($ruta_rel) : null;
        if (!$ruta_rel) {
            echo "ERROR: indica la ruta relativa a public_html/ del archivo.\n";
            return;
        }

        $ruta = (strpos($ruta_rel, '/') === 0) ? $ruta_rel : realpath(FCPATH . $ruta_rel);
        if (!$ruta || !is_readable($ruta)) {
            echo "ERROR: no se puede leer el archivo: $ruta_rel\n";
            return;
        }

        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        $opciones = ['omitir_duplicados' => ($modo !== 'todo')];

        echo "=== Importando: " . basename($ruta) . " (modo=" . ($opciones['omitir_duplicados'] ? 'dedup' : 'todo') . ") ===\n";
        try {
            $resultado = $this->_procesar_importacion_excel($ruta, basename($ruta), $ext, $opciones);
            $r = $resultado['response'];
            echo $r['message'] . "\n";
            echo "Importados: " . count($r['importados'] ?? []) . "\n";
            echo "Omitidos:   " . count($r['omitidos'] ?? []) . "\n";
            echo "Errores:    " . count($r['errores'] ?? []) . "\n";
            foreach (array_slice($r['errores'] ?? [], 0, 15) as $err) {
                echo "  ERROR: $err\n";
            }
            foreach (array_slice($r['importados'] ?? [], 0, 200) as $imp) {
                $nom = is_array($imp) ? ($imp['producto'] ?? json_encode($imp)) : $imp;
                echo "  + $nom\n";
            }
        } catch (Exception $e) {
            echo "ERROR FATAL: " . $e->getMessage() . "\n";
        }
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
            $ref_cliente_pre = null;
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
                    $ref_cliente_pre = null;
                    continue;
                }

                // Nombre de cliente en filas previas al bloque KILOS (ej. "HOSPITAL JUAREZ")
                $col_b_vacia = ($col_b_raw === null || $col_b_raw === '' || $col_b_raw === 0);
                if ($col_b_vacia && $this->_parece_nombre_cliente_import($col_a, $col_a_raw) && $cli_nombre === null) {
                    $cli_nombre = $col_a;
                }
                if ($col_b_vacia && $this->_parece_referencia_cliente_import($col_a)) {
                    $ref_cliente_pre = $col_a;
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
                        // Fallback fichas históricas: en hojas viejas el total del lote no está
                        // en la celda contigua al "KILOS", sino como único número aislado en la
                        // columna de kg unas filas más abajo (antes del primer componente).
                        $total_kg = $this->_total_kg_fallback($matrix, $r, $kilos_col + 1, $maxRow);
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
                        'row'               => $r,
                        'ref'               => $ref,
                        'total_kg'          => $total_kg,
                        'cliente'           => $cli_nombre,
                        'referencia_cliente'=> $this->_extraer_referencia_cliente_import($ref, $ref_cliente_pre),
                        'cubetas'           => $cli_cubetas,
                        'descripcion'   => '',
                        'grupos'        => [],
                        'formato'       => $formato,
                        'kilos_col'     => $kilos_col,
                        'hoja_origen'   => $nombre_hoja,
                        'archivo_origen'=> $nombre_archivo,
                    ];
                    $cli_nombre  = null;
                    $cli_cubetas = 1;
                    $ref_cliente_pre = null;
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
                $total_kg_bloque = (float)($bloques[$bi]['total_kg'] ?? 0);
                $kg_derivado_en_bloque = false;

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

                    // Fallback fichas históricas: si el componente trae % pero no kg,
                    // derivamos kg = total_lote × proporción (el % es la fuente de verdad y
                    // el simulador recalcula kg de todos modos). Se marca para trazabilidad.
                    if ($col_a !== '' && $pct_raw !== null && ($kg === null || $kg <= 0)
                        && $total_kg_bloque > 0 && $pct_raw > 0 && $pct_raw <= 1.02) {
                        $kg = round($total_kg_bloque * $pct_raw, 6);
                        $kg_derivado_en_bloque = true;
                    }

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
                $bloques[$bi]['kg_derivado'] = $kg_derivado_en_bloque;
                // Solo agregar bloques con al menos un componente
                if (!empty($grupos)) {
                    $todos[] = $bloques[$bi];
                }
            }
        }

        return $todos;
    }

    /**
     * Fallback para fichas históricas sin total en la celda contigua a "KILOS".
     * Busca en las filas posteriores al encabezado el primer número aislado en la
     * columna de kg ($kg_col) donde col A y col B están vacías (patrón "total bajo
     * encabezado"). Si encuentra MÁS de un número aislado antes del primer componente
     * (col B numérica), lo considera ambiguo y devuelve 0 para no importar datos dudosos.
     */
    private function _total_kg_fallback($matrix, $r_header, $kg_col, $maxRow) {
        $candidatos = [];
        for ($r = $r_header + 1; $r <= min($maxRow, $r_header + 6); $r++) {
            $a = trim((string)($matrix[$r][1] ?? ''));
            $b = $matrix[$r][2] ?? null;
            $kg = $matrix[$r][$kg_col] ?? null;
            // Si aparece un componente (col A texto + col B numérica) detenemos la búsqueda.
            if ($a !== '' && is_numeric($b)) break;
            // Fila de total aislada: A vacía, B vacía, kg numérico > 0.
            if ($a === '' && ($b === null || $b === '') && is_numeric($kg) && (float)$kg > 0) {
                $candidatos[] = (float)$kg;
            }
        }
        // Solo aceptamos un único candidato para evitar confundir con valores de fase acuosa.
        return (count($candidatos) === 1) ? $candidatos[0] : 0.0;
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
    private function _parece_nombre_cliente_import($col_a, $col_a_raw) {
        if ($col_a === '') {
            return false;
        }
        if (is_int($col_a_raw) && (int) $col_a_raw > 40000) {
            return false;
        }
        if (preg_match('/^\d+$/', $col_a)) {
            return false;
        }
        if (preg_match('/^\d+\s+(CUBETAS?|TAMBOS?)\b/i', $col_a)) {
            return false;
        }
        if (preg_match('/^\d+\s+LTS?\b/i', $col_a)) {
            return false;
        }
        if (stripos($col_a, 'CHISA') !== false) {
            return false;
        }
        if (stripos($col_a, 'KILOS') !== false) {
            return false;
        }
        if (preg_match('/^HOJA\b/i', $col_a)) {
            return false;
        }
        return true;
    }

    private function _parece_referencia_cliente_import($texto) {
        $t = trim((string) $texto);
        if ($t === '' || preg_match('/^\d{5}$/', $t) || preg_match('/^\d+$/', $t)) {
            return false;
        }
        return (bool) preg_match('/^[A-Z]{1,4}[\s.\-–]*\d/i', $t);
    }

    private function _normalizar_referencia_cliente_import($ref) {
        $ref = trim((string) $ref);
        $ref = preg_replace('/\s*-\s*/', '-', $ref);
        return trim(preg_replace('/\s+/', ' ', $ref));
    }

    private function _extraer_referencia_cliente_import($ref_producto, $pre = null) {
        if (preg_match('/\bREF\.?\s*(.+)$/i', $ref_producto, $m)) {
            return $this->_normalizar_referencia_cliente_import(trim($m[1]));
        }
        if ($pre && $this->_parece_referencia_cliente_import($pre)) {
            return $this->_normalizar_referencia_cliente_import($pre);
        }
        if (preg_match('/\b([A-Z]{1,4}\s*[-–.]?\s*\d{2}\s*[-–.]?\s*\d+)\b/i', $ref_producto, $m)) {
            return $this->_normalizar_referencia_cliente_import($m[1]);
        }
        return null;
    }

    private function _guardar_formulacion_importada($pdata) {
        $ref            = $pdata['ref'];
        $total_kg       = $pdata['total_kg'];
        $cliente_nom    = $pdata['cliente'] ?? null;
        $referencia_cli = $pdata['referencia_cliente'] ?? null;
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
            'referencia_cliente' => $referencia_cli,
            'nombre_version'        => 'V' . $version . ($cliente_nom ? " – $cliente_nom" : ''),
            'descripcion'           => $descripcion,
            'comentarios'           => implode(' | ', array_filter([
                $cliente_nom ? "Cliente: $cliente_nom" : 'Importado desde Excel.',
                $archivo_origen ? "Archivo: $archivo_origen" : null,
                $hoja_origen    ? "Hoja: $hoja_origen"    : null,
                !empty($pdata['kg_derivado']) ? 'kg calculado desde % (ficha histórica sin kg): verificar en planta.' : null,
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
                    'kg_fase_acuosa'         => $item['kg_fase'] ?? null,
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

    // =========================================================================
    // IMPORTADOR JSON DESDE CLI — Fase 2 (Entrenamiento 3)
    // Rama: iteracion-3  |  Decisiones: A1–A11 de decisiones_pendientes.md
    // =========================================================================

    /**
     * Importa formulaciones desde los manifiestos JSON del Entrenamiento 3.
     * Fase 2: solo --dry-run habilitado. NO escribe nada en la BD.
     *
     * Uso:  php index.php produccion/Productos importar_formulaciones_json_cli --dry-run
     * Flags: --dry-run (default/único) | --solo=<imagen.jpeg> | --excluir=<a,b,...>
     *        --out=<ruta.txt> | --json | --activar-nuevas (declarado, APAGADO en Fase 2)
     * Env:   IMPORT_MANIFEST_DIR  (ruta base, default: public_html/doc/entrenamiento_3)
     */
    public function importar_formulaciones_json_cli()
    {
        if (!is_cli()) {
            show_error('Este método solo puede ejecutarse desde la línea de comandos.');
            return;
        }

        // ── 1. Argumentos ─────────────────────────────────────────────────
        $argv     = $_SERVER['argv'] ?? [];
        $dry_run  = in_array('--dry-run', $argv, true);
        $solo     = null;
        $excluir  = [];
        $out_file = null;
        $json_out = in_array('--json', $argv, true);
        // Fase 3: --aplicar escribe en BD; --activar-nuevas implica escribir y activa la V1 de
        // los productos que no tenían ninguna formulación previa (política de activación).
        $activar_nuevas = in_array('--activar-nuevas', $argv, true);
        $aplicar        = in_array('--aplicar', $argv, true) || $activar_nuevas;
        foreach ($argv as $arg) {
            if (strpos($arg, '--solo=')    === 0) { $solo     = substr($arg, 7); }
            if (strpos($arg, '--excluir=') === 0) { $excluir  = array_map('trim', explode(',', substr($arg, 10))); }
            if (strpos($arg, '--out=')     === 0) { $out_file = substr($arg, 6); }
        }
        // Env vars como alternativa para rutas con puntos/slashes que CI bloquea en URI
        if (getenv('IMPORT_SOLO'))    { $solo     = getenv('IMPORT_SOLO'); }
        if (getenv('IMPORT_EXCLUIR')) { $excluir  = array_map('trim', explode(',', getenv('IMPORT_EXCLUIR'))); }
        if (getenv('IMPORT_OUT'))     { $out_file = getenv('IMPORT_OUT'); }
        if (!$aplicar) {
            $dry_run = true;   // Fase 2: sin --aplicar / --activar-nuevas no se escribe nada
        }
        echo $dry_run
            ? "[MODO DRY-RUN] No se escribirá en la BD.\n\n"
            : "[MODO ESCRITURA] Formulaciones + catálogos + links (BD PRODUCCIÓN).\n\n";

        // ── 2. Directorios ────────────────────────────────────────────────
        $base_dir = rtrim(
            getenv('IMPORT_MANIFEST_DIR') ?: realpath(FCPATH . 'doc/entrenamiento_3'),
            '/'
        );
        $man_dir  = $base_dir . '/manifiestos';
        $ocr_dir  = $base_dir . '/ocr/parsed';

        // ── 3. Cargar manifiestos ─────────────────────────────────────────
        $pm_raw   = json_decode(file_get_contents($man_dir . '/productos_match.json'),   true) ?: [];
        $im_raw   = json_decode(file_get_contents($man_dir . '/insumos_match.json'),     true) ?: [];
        $comp_raw = json_decode(file_get_contents($man_dir . '/comparativa.json'),       true) ?: [];

        // Índices por OCR nombre (uppercase)
        $comp_idx = [];
        foreach ($comp_raw as $c) { $comp_idx[$c['imagen']] = $c; }

        $pm_idx = [];
        foreach ($pm_raw as $p) { $pm_idx[strtoupper(trim($p['ocr_nombre']))] = $p; }

        $im_idx = [];
        foreach ($im_raw as $ins) { $im_idx[strtoupper(trim($ins['ocr_nombre']))] = $ins; }

        // ── 4. Cargar OCR parsed JSONs ────────────────────────────────────
        $ocr_idx = [];
        foreach (glob($ocr_dir . '/batch_*.json') as $f) {
            foreach ((json_decode(file_get_contents($f), true) ?: []) as $item) {
                if (!empty($item['imagen'])) { $ocr_idx[$item['imagen']] = $item; }
            }
        }
        $lp_raw = file_exists($ocr_dir . '/lista_precios.json')
            ? (json_decode(file_get_contents($ocr_dir . '/lista_precios.json'), true) ?: [])
            : [];

        // ── 5. GUARD-RAIL: contador de escrituras (DEBE quedar en 0) ──────
        $write_counter = 0;

        // ── 6. Tablas de decisiones A3-A6 ────────────────────────────────
        $ins_overrides = $this->_insumo_overrides_json();

        // ── 7. Colecciones de resultado ───────────────────────────────────
        $prods_usar   = [];   // id   => nombre
        $prods_crear  = [];   // nom  => params
        $ins_vincular = [];   // id   => nombre_tecnico
        $ins_crear    = [];   // nom_canonico => unidad
        $links_f3     = $this->_links_f3_json();
        $fms_crear    = [];
        $fms_omit     = [];
        $plan         = [];   // detalle completo por formulación (solo se usa en modo escritura)

        // ── 8. Lista de imágenes a procesar ───────────────────────────────
        // Orden: comparativa primero (garantiza 11→V2 y 15→V3 de #215)
        $imagenes = array_column($comp_raw, 'imagen');
        foreach (['entrenamiento1.jpeg', 'entrenamiento5.jpeg'] as $x) {
            if (!in_array($x, $imagenes, true)) { $imagenes[] = $x; }
        }

        // Exclusiones hardcoded (decisiones A6.3 y A11)
        $excluir_hard = [
            'entrenamiento22.jpeg' => 'A6.3: PARAFINA CLORADA S-25 vs S-52 sin confirmar — CHISA PLUS diferido hasta resolución con planta',
            'entrenamiento18.jpeg' => 'A11: duplicado exacto de entrenamiento16.jpeg (BASE ORGANICA BLANCA, 1000 kg) — se carga una sola vez como entrenamiento16',
        ];

        // ── 9. Procesar formulaciones ─────────────────────────────────────
        foreach ($imagenes as $imagen) {
            // Filtros CLI
            if ($solo !== null && $imagen !== $solo)  { continue; }
            if (in_array($imagen, $excluir, true))    { continue; }

            $ocr  = $ocr_idx[$imagen]  ?? null;
            $comp = $comp_idx[$imagen] ?? null;

            // Saltar si no es formulacion
            if (!$ocr) { continue; }
            $tipo_doc = $ocr['tipo_documento'] ?? '';
            if (in_array($tipo_doc, ['rendimientos', 'lista_precios'], true)) { continue; }

            // Exclusiones hardcoded
            if (isset($excluir_hard[$imagen])) {
                $fms_omit[] = ['imagen' => $imagen, 'razon' => $excluir_hard[$imagen]];
                continue;
            }

            // coincide_exacto → omitir (D de decisiones_pendientes.md)
            if ($comp && $comp['estado'] === 'coincide_exacto') {
                $dup = !empty($comp['duplicado_de'])
                    ? " + A11 duplicado de {$comp['duplicado_de']}"
                    : '';
                $fms_omit[] = [
                    'imagen'  => $imagen,
                    'prod_id' => $comp['producto_id'],
                    'razon'   => "coincide_exacto: receta idéntica a V{$comp['version_que_coincide']}"
                               . " (activa=V{$comp['version_activa']}){$dup}",
                ];
                continue;
            }

            // ── Resolver producto ─────────────────────────────────────────
            $ocr_prod = $ocr['producto']['nombre'] ?? '?';
            $prod_info = $this->_resolver_producto_json($ocr_prod, $pm_idx, $comp, $imagen);

            if ($prod_info['accion'] === 'excluir') {
                $fms_omit[] = ['imagen' => $imagen, 'razon' => $prod_info['razon']];
                continue;
            }

            if ($prod_info['accion'] === 'usar_existente') {
                $prods_usar[(int)$prod_info['producto_id']] = $prod_info['producto_nombre'];
            } else {
                $prods_crear[$prod_info['nombre_propuesto']] = $prod_info;
            }

            // ── Versión siguiente ─────────────────────────────────────────
            $version = $this->_proxima_version_json($prod_info, $imagen);

            // ── Componentes / grupos ──────────────────────────────────────
            $grupos_pdata = ['__default__' => []];

            if ($tipo_doc === 'formulacion_grupo') {
                // entrenamiento20-grupo.jpeg → A1/A2
                $grupos_t034 = $this->_grupos_t034_json();
                $grupos_pdata = [];
                foreach ($grupos_t034 as $gnom => $gitems) {
                    $grupos_pdata[$gnom] = [];
                    foreach ($gitems as $gi) {
                        if ($gi['crear']) {
                            $ins_crear[$gi['nombre_canonico']] = 'Kg';
                        } else {
                            $ins_vincular[$gi['insumo_id']] = $gi['nombre_canonico'];
                        }
                        $grupos_pdata[$gnom][] = [
                            'nombre'     => $gi['nombre_canonico'],
                            'porcentaje' => round($gi['kg'] / 19.35 * 100, 4),
                            'kg'         => $gi['kg'],
                            'pct_fase'   => $gi['pct_fase'],
                            'kg_fase'    => $gi['pct_fase'] ? $gi['kg'] : null, // A2: kg de la fase acuosa del color
                            'insumo_id'  => $gi['crear'] ? null : (int)$gi['insumo_id'],
                            'insumo_crear' => (bool)$gi['crear'],
                        ];
                    }
                }
            } else {
                foreach ($ocr['componentes'] as $cmp) {
                    $res = $this->_resolver_insumo_json($cmp['nombre'], $ins_overrides, $im_idx);
                    if ($res['accion'] === 'vincular' && $res['insumo_id']) {
                        $ins_vincular[(int)$res['insumo_id']] = $res['nombre_canonico'];
                    } elseif ($res['accion'] === 'crear') {
                        $ins_crear[$res['nombre_canonico']] = 'Kg';
                    }
                    $grupos_pdata['__default__'][] = [
                        'nombre'     => $res['nombre_canonico'],
                        'porcentaje' => (float)($cmp['porcentaje'] ?? 0),
                        'kg'         => (float)($cmp['cantidad']  ?? 0),
                        'pct_fase'   => null,
                        'kg_fase'    => null,
                        'insumo_id'  => $res['insumo_id'] ? (int)$res['insumo_id'] : null,
                        'insumo_crear' => ($res['accion'] === 'crear'),
                    ];
                }
            }

            // ── _formulacion_ya_existe (read-only, guard incluido) ────────
            $ref_chk = ($prod_info['accion'] === 'usar_existente')
                ? $prod_info['producto_nombre']
                : ($prod_info['nombre_propuesto'] ?? '');
            $pdata_chk = [
                'ref'      => $ref_chk,
                'total_kg' => (float)($ocr['lote']['cantidad'] ?? 0),
                'grupos'   => $grupos_pdata,
            ];
            $ya_existe = $this->_formulacion_ya_existe($pdata_chk);

            // ── rendimiento_m2_por_kg (A9) ────────────────────────────────
            $rend_m2 = $this->_rendimiento_m2_json($prod_info, $pm_idx);

            // ── Contar filas de detalle ───────────────────────────────────
            $n_filas = 0;
            foreach ($grupos_pdata as $gc) { $n_filas += count($gc); }
            $n_grupos = count(array_filter(array_keys($grupos_pdata), fn ($k) => $k !== '__default__'));

            // A2: kg de fase acuosa por grupo (solo grupos de color)
            $fase_grupos = [];
            foreach ($grupos_pdata as $gnom => $gitems) {
                if ($gnom === '__default__') { continue; }
                foreach ($gitems as $gi) {
                    if (!empty($gi['pct_fase'])) {
                        $fase_grupos[$gnom] = ['kg' => $gi['kg'], 'pct' => $gi['pct_fase']];
                    }
                }
            }

            $fms_crear[] = [
                'imagen'        => $imagen,
                'producto'      => $prod_info['accion'] === 'usar_existente'
                    ? "#{$prod_info['producto_id']} {$prod_info['producto_nombre']}"
                    : "(NUEVO) {$prod_info['nombre_propuesto']}",
                'version'       => $version,
                'lote_kg'       => (float)($ocr['lote']['cantidad'] ?? 0),
                'n_ocr_comp'    => count($ocr['componentes'] ?? []),
                'n_filas_detalle'=> $n_filas,
                'grupos_color'  => $n_grupos ?: count($ocr['variantes'] ?? []),
                'fase_acuosa'   => $fase_grupos,
                'rend_m2_kg'    => $rend_m2,
                'ya_existe'     => $ya_existe ? '⚠ SÍ (revisar fingerprint)' : 'NO',
                'confianza_ocr' => $ocr['confianza'] ?? null,
            ];

            // Detalle completo (solo lo consume el modo escritura)
            $plan[] = [
                'imagen'    => $imagen,
                'prod_info' => $prod_info,
                'version'   => $version,
                'total_kg'  => (float)($ocr['lote']['cantidad'] ?? 0),
                'grupos'    => $grupos_pdata,
                'notas'     => $comp['recomendacion'] ?? '',
                'confianza' => $ocr['confianza'] ?? null,
            ];
        } // foreach imagenes

        // ── 10. GUARD-RAIL: en dry-run asegurar cero escrituras ───────────
        if ($dry_run && $write_counter !== 0) {
            $msg = "ERROR FATAL: {$write_counter} escritura(s) detectadas en modo dry-run. ABORTANDO.\n";
            echo $msg;
            exit(1);
        }

        // ── 11. Lista de precios 2025 (A8/A9) ────────────────────────────
        $lista_2025 = $this->_lista_2025_json($pm_idx);

        // ── 12. Generar reporte ───────────────────────────────────────────
        $reporte = $this->_reporte_dryrun_json([
            'dry_run'      => $dry_run,
            'prods_usar'   => $prods_usar,
            'prods_crear'  => $prods_crear,
            'ins_vincular' => $ins_vincular,
            'ins_crear'    => $ins_crear,
            'links_f3'     => $links_f3,
            'fms_crear'    => $fms_crear,
            'fms_omit'     => $fms_omit,
            'lista_2025'   => $lista_2025,
            'write_counter'=> $write_counter,
        ]);

        echo $reporte;

        // Guardar reporte (D1): la corrida completa escribe el artefacto por defecto; --solo
        // genera su propio archivo para no pisar la corrida general (antes la sobreescribía).
        // En Fase 3 el reporte va a un archivo propio para conservar el dry-run aprobado.
        $default_out = $man_dir . ($dry_run ? '/dry_run_fase2.txt' : '/fase3_aplicacion.txt');
        $targets     = [];
        if ($solo === null) {
            $targets[] = $default_out;
        } else {
            $targets[] = $man_dir . '/dry_run_fase2_solo_'
                       . preg_replace('/[^A-Za-z0-9._-]+/', '_', $solo) . '.txt';
        }
        if ($out_file) { $targets[] = $out_file; }
        foreach (array_unique($targets) as $t) {
            @file_put_contents($t, $reporte);
            echo "\n[Reporte guardado en: $t]\n";
        }
        if ($json_out) {
            $json_path = $out_file
                ? preg_replace('/\.txt$/i', '.json', $out_file)
                : $man_dir . '/dry_run_fase2.json';
            @file_put_contents($json_path, json_encode([
                'prods_usar'   => $prods_usar,
                'prods_crear'  => $prods_crear,
                'ins_vincular' => $ins_vincular,
                'ins_crear'    => $ins_crear,
                'links_f3'     => $links_f3,
                'fms_crear'    => $fms_crear,
                'fms_omit'     => $fms_omit,
                'lista_2025'   => $lista_2025,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo "[JSON guardado en: $json_path]\n";
        }

        // ── 13. FASE 3: aplicar el plan (solo con --aplicar / --activar-nuevas) ──
        if (!$dry_run) {
            $preflight = in_array('--preflight', $argv, true);   // valida sin escribir
            $res = $this->_aplicar_plan_json($plan, $prods_crear, $ins_crear, $lista_2025, $activar_nuevas, $links_f3, $preflight);
            echo $res['reporte'];
            foreach (array_unique($targets) as $t) {
                @file_put_contents($t, $res['reporte'], FILE_APPEND);
            }
            if (empty($res['ok'])) {
                exit(1);
            }
        }
    }

    // ── Helpers privados del importador JSON (Fase 2) ─────────────────────

    /**
     * Tabla de decisiones A3-A6: mapeo nombre OCR (UPPER) → [accion, insumo_id, nombre_canonico].
     * Tiene prioridad sobre insumos_match.json.
     */
    private function _insumo_overrides_json(): array
    {
        return [
            // A4: AGUA → #126 INS-AGUA-001
            'AGUA'                       => ['vincular', 126, 'AGUA'],
            // A5: RESINA ambigua → #128 RESINA W4535
            'RESINA'                     => ['vincular', 128, 'RESINA W4535'],
            // A5: insumos nuevos
            'BERMOCOL481FQ'              => ['crear', null, 'BERMOCOLL 481 FQ'],
            'BERMOCOLL481FQ'             => ['crear', null, 'BERMOCOLL 481 FQ'],
            'BERMOCOLL481-FQ'            => ['crear', null, 'BERMOCOLL 481 FQ'],
            'RESINA QE2383(W-595)'       => ['crear', null, 'RESINA QE-2383 W-595'],
            'RESINA QE 2383(W-595)'      => ['crear', null, 'RESINA QE-2383 W-595'],
            'RESINA QE 220 S(W-394)'     => ['crear', null, 'RESINA QE-220S'],
            'RESINA QE-220S'             => ['crear', null, 'RESINA QE-220S'],
            'RESINA QE220-S'             => ['crear', null, 'RESINA QE-220S'],
            'RESINA QE-2383'             => ['crear', null, 'RESINA QE-2383 W-595'],
            'RESINA QE2383'              => ['crear', null, 'RESINA QE-2383 W-595'],
            // A6.1: BIOXIDO DE TITANEO → #15 PIG-001
            'BIOXIDO DE TITANEO'         => ['vincular', 15, 'Dióxido de Titanio R-902 (TiO2)'],
            'BIOXIDODETITANEO'           => ['vincular', 15, 'Dióxido de Titanio R-902 (TiO2)'],
            'BIOXIDODETITANEO R-902'     => ['vincular', 15, 'Dióxido de Titanio R-902 (TiO2)'],
            'BIOXIDO DETITANEO'          => ['vincular', 15, 'Dióxido de Titanio R-902 (TiO2)'],
            // A6.4: CAOLIN → #136; AEROSIL → #91
            'CAOLIN'                     => ['vincular', 136, 'CAOLIN M-325'],
            'SOLUCION DE AEROSIL'        => ['vincular', 91,  'SOLUCION DE AEROSIL 200'],
            'SOLUCION DEAEROSIL200'      => ['vincular', 91,  'SOLUCION DE AEROSIL 200'],
            // A3: semielaborados usados como insumo (tipo_componente=Insumo, igual que V1)
            'SOLUCION DERESINA'          => ['vincular', 83,  'SOLUCION DE RESINA'],
            'SOLUCION DE RESINA'         => ['vincular', 83,  'SOLUCION DE RESINA'],
            'TINTANEGRA'                 => ['vincular', 105, 'TINTA NEGRA'],
            'TINTA AMARILLO OXIDO'       => ['vincular', 96,  'TINTA AMARILLO OXIDO'],
            // A6.4/A3: FASE ACUOSA → crear insumo nuevo y enlazarlo a #215 en Fase 3
            'FASE ACUOSA'                => ['crear', null, 'FASE ACUOSA'],
            // A6.5: colores T-034
            'BLANCO'                     => ['vincular', 61,  'BLANCO'],
            'NEGRO'                      => ['vincular', 18,  'Negro de Humo (Carbon Black)'],
            'ROJO'                       => ['vincular', 16,  'Óxido de Hierro Rojo (Fe2O3)'],
            'AMARILLO'                   => ['vincular', 17,  'Óxido de Hierro Amarillo'],
            'AZUL'                       => ['vincular', 19,  'Pigmento Azul Ftalocianinico'],
            'VERDE'                      => ['vincular', 20,  'Pasta Colorante Verde (base agua)'],
            // A6.5: tintas
            'ROJO OXIDO'                 => ['vincular', 77,  'ROJO OXIDO'],
            'NEGRO OXIDO'                => ['vincular', 94,  'NEGRO OXIDO'],
            'VERDE CROMO'                => ['vincular', 101, 'VERDE CROMO'],
            'VERDECROMO'                 => ['vincular', 101, 'VERDE CROMO'],
            'AZUL DE FTALOZANINA'        => ['vincular', 100, 'AZUL DE FTALOZANINA'],
            'AZULDEFTALOZANINA'          => ['vincular', 100, 'AZUL DE FTALOZANINA'],
            // Variantes de espaciado OCR no cubiertas por insumos_match.json
            'CARBONATO DEOMYA CAR 1-T'   => ['vincular', 102, 'CARBONATO DE OMYA CAR 1-T'],
            'CARBONATODEOMYA CAR1-T'     => ['vincular', 102, 'CARBONATO DE OMYA CAR 1-T'],
            'CARBONATO DEOMYA CAR1-T'    => ['vincular', 102, 'CARBONATO DE OMYA CAR 1-T'],
            'CARBONATO DEOMYA CAR 1T'    => ['vincular', 102, 'CARBONATO DE OMYA CAR 1-T'],
            'TIXO GELEZ-100'             => ['vincular', 85,  'TIXO GEL EZ-100'],
            'GOMADEXHATAN'               => ['vincular', 114, 'GOMA DE XHATAN'],
            'LECITINADESOYA'             => ['vincular', 89,  'LECITINA DE SOYA'],
        ];
    }

    /**
     * Resuelve un nombre OCR de insumo: prioritiza decisiones A3-A6, luego manifiesto, luego BD.
     */
    private function _resolver_insumo_json(string $ocr_nombre, array $overrides, array $im_idx): array
    {
        $upper = strtoupper(trim(preg_replace('/\s{2,}/', ' ', $ocr_nombre)));

        // 1. Overlay de decisiones (A3-A6, máxima prioridad)
        if (isset($overrides[$upper])) {
            [$accion, $id, $nom] = $overrides[$upper];
            return ['accion' => $accion, 'insumo_id' => $id, 'nombre_canonico' => $nom, 'fuente' => 'decision'];
        }

        // 2. Manifiesto insumos_match.json
        if (isset($im_idx[$upper])) {
            $e = $im_idx[$upper];
            if (in_array($e['accion'], ['vincular', 'crear'], true)) {
                return [
                    'accion'          => $e['accion'],
                    'insumo_id'       => $e['accion'] === 'vincular' ? (int)$e['insumo_id'] : null,
                    'nombre_canonico' => $e['nombre_canonico'],
                    'fuente'          => 'manifiesto',
                ];
            }
        }

        // 3. Fallback BD (read-only)
        $ins = $this->_buscar_insumo($ocr_nombre);
        if ($ins) {
            return ['accion' => 'vincular', 'insumo_id' => (int)$ins->id, 'nombre_canonico' => $ins->nombre_tecnico, 'fuente' => 'bd'];
        }

        // 4. No encontrado → crear
        return ['accion' => 'crear', 'insumo_id' => null, 'nombre_canonico' => $this->_normalizar_nombre_insumo($ocr_nombre), 'fuente' => 'no_encontrado'];
    }

    /**
     * Resuelve el producto OCR a su acción (usar_existente | crear | excluir).
     * Aplica decisiones A7 (VITROGLASS ECOLOGICO, SELLADOR INICIAL, entrenamiento2=#224).
     */
    private function _resolver_producto_json(string $ocr_nombre, array $pm_idx, ?array $comp, string $imagen): array
    {
        $upper = strtoupper(trim($ocr_nombre));

        // A7.1: VITROGLASS ECOLOGICO (ficha entrenamiento1)
        if ($imagen === 'entrenamiento1.jpeg') {
            return ['accion' => 'crear', 'nombre_propuesto' => 'VITROGLASS ECOLOGICO',
                    'tipo_producto' => 'Fabricado', 'presentacion' => 'Cubeta',
                    'contenido_neto' => 19.0, 'unidad' => 'Kg', 'categoria_ocr' => 'SELLADORES',
                    'precio_venta' => 5023.57];
        }

        // A7.2: SELLADOR INICIAL (ficha entrenamiento5)
        if ($imagen === 'entrenamiento5.jpeg') {
            return ['accion' => 'crear', 'nombre_propuesto' => 'SELLADOR INICIAL',
                    'tipo_producto' => 'Fabricado', 'presentacion' => 'Cubeta',
                    'contenido_neto' => 19.0, 'unidad' => 'Kg', 'categoria_ocr' => 'SELLADORES',
                    'precio_venta' => null];
        }

        // A7.3 / A10: entrenamiento2 → PRAIMER GLASS = versión nueva de #224 PINTURA VINILICA
        if ($imagen === 'entrenamiento2.jpeg') {
            return ['accion' => 'usar_existente', 'producto_id' => 224, 'producto_nombre' => 'PINTURA VINILICA'];
        }

        // Usar comparativa (difiere → versión nueva inactiva, A10)
        if ($comp && in_array($comp['estado'], ['difiere', 'coincide_exacto'], true) && !empty($comp['producto_id'])) {
            $nom = '';
            foreach ($pm_idx as $e) {
                if ((int)($e['producto_id'] ?? 0) === (int)$comp['producto_id']) { $nom = $e['producto_nombre_bd'] ?? $ocr_nombre; break; }
            }
            return ['accion' => 'usar_existente', 'producto_id' => (int)$comp['producto_id'], 'producto_nombre' => $nom ?: $ocr_nombre];
        }

        // Buscar en pm_idx
        if (isset($pm_idx[$upper]) && $pm_idx[$upper]['accion'] === 'usar_existente') {
            $p = $pm_idx[$upper];
            return ['accion' => 'usar_existente', 'producto_id' => (int)$p['producto_id'], 'producto_nombre' => $p['producto_nombre_bd']];
        }

        return ['accion' => 'excluir', 'razon' => "Sin mapeo para \"$ocr_nombre\" en $imagen — no cubierto por A1-A11"];
    }

    /**
     * Calcula la versión siguiente consultando BD (read-only).
     * A11: entrenamiento11 → V(max+1), entrenamiento15 → V(max+2) del producto #215.
     */
    private function _proxima_version_json(array $prod_info, string $imagen): string
    {
        if ($prod_info['accion'] === 'crear') {
            return 'V1';
        }
        $pid = (int)$prod_info['producto_id'];
        $row = $this->db->select_max('version')->where('producto_id', $pid)->get('formulaciones')->row();
        $max = (int)($row->version ?? 0);

        // Cache estático para simular secuencia sin escribir (A11: 11→V(max+1), 15→V(max+2))
        static $vcache = [];
        $k = "p{$pid}";
        if (!isset($vcache[$k])) {
            $vcache[$k] = $max + 1;
        } else {
            $vcache[$k]++;
        }
        return 'V' . $vcache[$k];
    }

    /**
     * Estructura de grupos para entrenamiento20-grupo.jpeg (decisiones A1/A2/A6.5).
     * Sub-recetas verificadas contra el OCR (dudas del JSON). pct_fase=45.00 por grupo.
     */
    private function _grupos_t034_json(): array
    {
        // Colores A6.5: BLANCO=#61, NEGRO=#18, ROJO=#16, AMARILLO=#17, AZUL=#19, VERDE=#20
        return [
            'NEGRO'    => [
                ['insumo_id' => null, 'nombre_canonico' => 'FASE ACUOSA',                     'crear' => true,  'kg' => 0.401, 'pct_fase' => 45.00],
                ['insumo_id' => 18,   'nombre_canonico' => 'Negro de Humo (Carbon Black)',    'crear' => false, 'kg' => 0.015, 'pct_fase' => null],
                ['insumo_id' => 20,   'nombre_canonico' => 'Pasta Colorante Verde (base agua)','crear'=> false, 'kg' => 0.475, 'pct_fase' => null],
            ],
            'BLANCO'   => [
                ['insumo_id' => null, 'nombre_canonico' => 'FASE ACUOSA',                     'crear' => true,  'kg' => 7.401, 'pct_fase' => 45.00],
                ['insumo_id' => 17,   'nombre_canonico' => 'Óxido de Hierro Amarillo',        'crear' => false, 'kg' => 0.181, 'pct_fase' => null],
                ['insumo_id' => 61,   'nombre_canonico' => 'BLANCO',                          'crear' => false, 'kg' => 8.865, 'pct_fase' => null],
            ],
            'AZUL'     => [
                ['insumo_id' => null, 'nombre_canonico' => 'FASE ACUOSA',                     'crear' => true,  'kg' => 0.453, 'pct_fase' => 45.00],
                ['insumo_id' => 18,   'nombre_canonico' => 'Negro de Humo (Carbon Black)',    'crear' => false, 'kg' => 0.039, 'pct_fase' => null],
                ['insumo_id' => 17,   'nombre_canonico' => 'Óxido de Hierro Amarillo',        'crear' => false, 'kg' => 0.029, 'pct_fase' => null],
                ['insumo_id' => 16,   'nombre_canonico' => 'Óxido de Hierro Rojo (Fe2O3)',    'crear' => false, 'kg' => 0.098, 'pct_fase' => null],
                ['insumo_id' => 19,   'nombre_canonico' => 'Pigmento Azul Ftalocianinico',    'crear' => false, 'kg' => 0.082, 'pct_fase' => null],
                ['insumo_id' => 61,   'nombre_canonico' => 'BLANCO',                          'crear' => false, 'kg' => 0.306, 'pct_fase' => null],
            ],
            'AMARILLO' => [
                ['insumo_id' => null, 'nombre_canonico' => 'FASE ACUOSA',                     'crear' => true,  'kg' => 0.453, 'pct_fase' => 45.00],
                ['insumo_id' => 18,   'nombre_canonico' => 'Negro de Humo (Carbon Black)',    'crear' => false, 'kg' => 0.054, 'pct_fase' => null],
                ['insumo_id' => 16,   'nombre_canonico' => 'Óxido de Hierro Rojo (Fe2O3)',    'crear' => false, 'kg' => 0.138, 'pct_fase' => null],
                ['insumo_id' => 17,   'nombre_canonico' => 'Óxido de Hierro Amarillo',        'crear' => false, 'kg' => 0.149, 'pct_fase' => null],
                ['insumo_id' => 61,   'nombre_canonico' => 'BLANCO',                          'crear' => false, 'kg' => 0.213, 'pct_fase' => null],
            ],
        ];
    }

    /**
     * Links insumo→semielaborado a ejecutar en Fase 3 (A3, UPDATE puntual, solo reporte aquí).
     */
    private function _links_f3_json(): array
    {
        return [
            ['insumo_id' => 91,   'codigo' => 'IMP-D0012F55', 'nombre_insumo' => 'SOLUCION DE AEROSIL 200', 'producto_id' => 204, 'nombre_producto' => 'SOLUCION DE AEROSIL 200'],
            ['insumo_id' => 96,   'codigo' => 'IMP-7A375D05', 'nombre_insumo' => 'TINTA AMARILLO OXIDO',    'producto_id' => 206, 'nombre_producto' => 'TINTA AMARILLO OXIDO'],
            ['insumo_id' => 105,  'codigo' => 'IMP-AF1B06D8', 'nombre_insumo' => 'TINTA NEGRA',             'producto_id' => 205, 'nombre_producto' => 'TINTA NEGRA'],
            ['insumo_id' => 83,   'codigo' => 'IMP-7FB3924F', 'nombre_insumo' => 'SOLUCION DE RESINA',      'producto_id' => 214, 'nombre_producto' => 'SOLUCION DE RESINA EC-1',  'nota' => 'Validar equivalencia EC-1'],
            ['insumo_id' => null, 'codigo' => '(NUEVO)',      'nombre_insumo' => 'FASE ACUOSA',             'producto_id' => 215, 'nombre_producto' => 'SOLUCION FASE ACUOSA',    'nota' => 'Crear insumo FASE ACUOSA primero, luego enlazar a #215'],
        ];
    }

    /**
     * Calcula rendimiento_m2_por_kg del producto desde la lista de precios (A9).
     * Fórmula: punto_medio_m2_cubeta / contenido_neto (19 kg).
     */
    private function _rendimiento_m2_json(array $prod_info, array $pm_idx): ?string
    {
        $bus = strtoupper(trim(
            $prod_info['accion'] === 'crear'
                ? ($prod_info['nombre_propuesto'] ?? '')
                : ($prod_info['producto_nombre'] ?? '')
        ));

        foreach ($pm_idx as $e) {
            if (strtoupper(trim($e['ocr_nombre'] ?? '')) !== $bus
                && strtoupper(trim($e['producto_nombre_bd'] ?? '')) !== $bus) {
                continue;
            }
            foreach ($e['presentaciones'] ?? [] as $pres) {
                if (strtoupper(trim($pres['presentacion'] ?? '')) === 'CUBETA'
                    && !empty($pres['rendimiento_teorico'])) {
                    $cn = $this->_contenido_neto_producto_json($prod_info);
                    return $this->_calc_m2_por_kg_json($pres['rendimiento_teorico'], (float)$cn);
                }
            }
        }
        return null;
    }

    /**
     * Calcula m2/kg desde el texto de rendimiento de la lista de precios (A9).
     * - Tasa de CONSUMO ("250gr./1m2" → 0,25 kg/m² → 4 m²/kg): no depende del envase.
     * - Cobertura del envase ("180-190m2", "30m2"): requiere el contenido neto real (kg).
     */
    private function _calc_m2_por_kg_json(string $texto, float $kg = 0): ?string
    {
        // R8: tasa de consumo "250gr./1m2" → m² por kg de producto (invertida)
        if (preg_match('/(\d+(?:\.\d+)?)\s*(gr|g|kg)\s*\.?\s*\/\s*(\d+(?:\.\d+)?)?\s*m2/i', $texto, $m)) {
            $kg_prod = strtolower($m[2]) === 'kg' ? (float)$m[1] : (float)$m[1] / 1000;
            $m2      = (isset($m[3]) && (float)$m[3] > 0) ? (float)$m[3] : 1.0;
            if ($kg_prod <= 0) { return null; }
            return round($m2 / $kg_prod, 2) . ' m²/kg';
        }

        if ($kg <= 0) { return null; } // el resto de formatos necesita contenido neto conocido

        // "180-190m2" → (180+190)/2 / kg
        if (preg_match('/(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)\s*m2/i', $texto, $m)) {
            return round(((float)$m[1] + (float)$m[2]) / 2 / $kg, 2) . ' m²/kg';
        }
        // "30m2"
        if (preg_match('/(\d+(?:\.\d+)?)\s*m2/i', $texto, $m)) {
            return round((float)$m[1] / $kg, 2) . ' m²/kg';
        }
        return null;
    }

    /**
     * Contenido neto (kg) real del producto para derivar m²/kg (A9/D2).
     * Solo devuelve número si es dato fiable:
     *  - ficha nueva con contenido declarado en A7, o
     *  - producto existente con `unidad_venta = 'Cubeta'` (ahí `contenido_neto` sí es el envase).
     * NO se usa `contenido_neto` de productos con `unidad_venta = 'Kg'`: en producción guardan
     * el lote (#223=135, #222=945, #225=380.35), no el peso del envase. Esos casos quedan
     * FALTANTE y su derivación va a la propuesta de Fase 3 (aprobación de negocio).
     */
    private function _contenido_neto_producto_json(array $prod_info): ?float
    {
        static $cache = [];

        if (($prod_info['accion'] ?? '') === 'crear') {
            // Fichas nuevas con contenido declarado en A7 (mismo dato que _resolver_producto_json)
            $fichas_cn = ['VITROGLASS ECOLOGICO' => 19.0, 'SELLADOR INICIAL' => 19.0];
            $nom = strtoupper(trim($prod_info['nombre_propuesto'] ?? ''));
            if (isset($fichas_cn[$nom])) { return $fichas_cn[$nom]; }
            if (!empty($prod_info['contenido_neto'])) { return (float)$prod_info['contenido_neto']; }
            return null;
        }

        $pid = (int)($prod_info['producto_id'] ?? 0);
        if ($pid > 0) {
            if (!array_key_exists($pid, $cache)) {
                $row = $this->db->select('contenido_neto, unidad_venta')->where('id', $pid)->get('productos')->row();
                $es_envase = $row && strtolower(trim((string)$row->unidad_venta)) === 'cubeta';
                $cache[$pid] = ($es_envase && (float)$row->contenido_neto > 0) ? (float)$row->contenido_neto : null;
            }
            return $cache[$pid];
        }
        return null;
    }

    /**
     * Lista de productos de la lista 2025 (A8/A9): 28 a crear + 3 mapeados.
     * Fuente: pm_idx (entradas con presentaciones no vacías).
     */
    private function _lista_2025_json(array $pm_idx): array
    {
        // Mapeados por decisión A8
        $mapeados_ids = [222 => 'ARENA SILICA', 404 => 'CHISA GLASS TEXTURADO Y MICRO', 3 => 'CHISA GLASS MICRO'];
        $result = [];

        foreach ($pm_idx as $e) {
            if (empty($e['presentaciones'])) { continue; }

            $pid = (int)($e['producto_id'] ?? 0);
            $mapeado_a = null;
            if (isset($mapeados_ids[$pid])) {
                $mapeado_a = ['id' => $pid, 'nombre' => $mapeados_ids[$pid]];
            } elseif ($e['accion'] === 'usar_existente' && $pid) {
                $mapeado_a = ['id' => $pid, 'nombre' => $e['producto_nombre_bd'] ?? ''];
            }

            $cn_base = $this->_contenido_neto_producto_json([
                'accion'           => $mapeado_a ? 'usar_existente' : 'crear',
                'producto_id'      => $pid ?: null,
                'nombre_propuesto' => $e['ocr_nombre'],
                'producto_nombre'  => $e['producto_nombre_bd'] ?? '',
            ]);

            $presentaciones = [];
            foreach ($e['presentaciones'] as $pres) {
                $rend      = $pres['rendimiento_teorico'] ?? null;
                $es_cubeta = strtoupper(trim($pres['presentacion'] ?? '')) === 'CUBETA';
                // D2: el contenido_neto del producto corresponde a su presentación base; solo se
                // usa para CUBETA. Galón y "(sin pres.)" quedan FALTANTE (no se inventan kg).
                $cn = ($es_cubeta && $cn_base) ? $cn_base : null;
                $presentaciones[] = [
                    'presentacion'       => $pres['presentacion'] ?? null,
                    'precio_sin_iva'     => $pres['precio_sin_iva'] ?? null,
                    'rendimiento_teorico'=> $rend,
                    'contenido_neto_kg'  => $cn,
                    'rendimiento_m2_kg'  => $rend ? $this->_calc_m2_por_kg_json($rend, (float)$cn) : null,
                    'categoria'          => $pres['categoria'] ?? null,
                ];
            }

            $result[] = [
                'ocr_nombre'    => $e['ocr_nombre'],
                'categoria'     => $presentaciones[0]['categoria'] ?? null,
                'mapeado_a'     => $mapeado_a,
                'accion'        => $mapeado_a ? 'usar_existente' : 'crear',
                'presentaciones'=> $presentaciones,
            ];
        }
        return $result;
    }

    /**
     * Genera el texto del reporte dry-run (ENTREGABLE 2 de Fase 2).
     */
    private function _reporte_dryrun_json(array $d): string
    {
        $ln  = [];
        $s1  = str_repeat('─', 80);
        $s2  = str_repeat('═', 80);

        $ln[] = $s2;
        $ln[] = (!empty($d['dry_run']) ? 'DRY-RUN FASE 2' : 'APLICACIÓN FASE 3')
              . ' — ENTRENAMIENTO 3 — CHISA RECUBRIMIENTOS';
        $ln[] = 'Generado: ' . date('Y-m-d H:i:s') . '  |  BD: PRODUCCIÓN'
              . (!empty($d['dry_run']) ? ' (solo lectura)' : ' (ESCRITURA)');
        $ln[] = $s2;

        // §1 — Productos a USAR
        $ln[] = '';
        $ln[] = '§1  PRODUCTOS A USAR (id + nombre en BD)';
        $ln[] = $s1;
        if (empty($d['prods_usar'])) {
            $ln[] = '  (ninguno)';
        } else {
            foreach ($d['prods_usar'] as $id => $nom) {
                $ln[] = sprintf('  #%-5d %s', $id, $nom);
            }
        }

        // §2 — Productos a CREAR
        $ln[] = '';
        $ln[] = '§2  PRODUCTOS A CREAR (fichas con producto nuevo)';
        $ln[] = $s1;
        if (empty($d['prods_crear'])) {
            $ln[] = '  (ninguno)';
        } else {
            foreach ($d['prods_crear'] as $nom => $info) {
                $ln[] = "  NOMBRE:         $nom";
                $ln[] = "    tipo_producto: " . ($info['tipo_producto']  ?? 'Fabricado');
                $ln[] = "    presentacion:  " . ($info['presentacion']   ?? 'FALTANTE');
                $cn = isset($info['contenido_neto']) ? $info['contenido_neto'] . ' Kg' : 'FALTANTE';
                $ln[] = "    contenido_neto: $cn";
                $pv = isset($info['precio_venta']) ? '$' . number_format($info['precio_venta'], 2) : 'FALTANTE';
                $ln[] = "    precio_venta:  $pv";
                $ln[] = "    categoria:     " . ($info['categoria_ocr'] ?? 'FALTANTE');
                $ln[] = '';
            }
        }

        // §3 — Insumos a VINCULAR
        $ln[] = '§3  INSUMOS A VINCULAR (id + nombre_tecnico en BD)';
        $ln[] = $s1;
        if (empty($d['ins_vincular'])) {
            $ln[] = '  (ninguno)';
        } else {
            ksort($d['ins_vincular']);
            foreach ($d['ins_vincular'] as $id => $nom) {
                $alerta = strpos($nom, 'IMP-') !== false ? '  ⚠ CÓDIGO IMP- (preferir canónico)' : '';
                $ln[] = sprintf('  #%-5d %s%s', $id, $nom, $alerta);
            }
        }

        // §4 — Insumos a CREAR
        $ln[] = '';
        $ln[] = '§4  INSUMOS A CREAR (nombre canónico + unidad)';
        $ln[] = $s1;
        if (empty($d['ins_crear'])) {
            $ln[] = '  (ninguno)';
        } else {
            foreach ($d['ins_crear'] as $nom => $unidad) {
                $ln[] = "  NUEVO: $nom  [unidad=$unidad]";
            }
        }

        // §5 — Links insumo→semielaborado (Fase 3)
        $ln[] = '';
        $ln[] = '§5  ENLACES INSUMO→SEMIELABORADO A EJECUTAR EN FASE 3 (UPDATE puntual)';
        $ln[] = $s1;
        foreach ($d['links_f3'] as $l) {
            $nota  = isset($l['nota'])    ? "  [NOTA: {$l['nota']}]" : '';
            $ins_s = $l['insumo_id']      ? "insumo #{$l['insumo_id']} ({$l['codigo']})" : "insumo NUEVO ({$l['nombre_insumo']})";
            $ln[] = "  $ins_s → tipo='fabricado', producto_id={$l['producto_id']} ({$l['nombre_producto']}){$nota}";
        }

        // §6 — Formulaciones a CREAR
        $ln[] = '';
        $ln[] = '§6  FORMULACIONES A CREAR';
        $ln[] = $s1;
        if (empty($d['fms_crear'])) {
            $ln[] = '  (ninguna)';
        } else {
            foreach ($d['fms_crear'] as $f) {
                $gc  = $f['grupos_color']   > 0 ? "  grupos_color={$f['grupos_color']}" : '';
                $rm  = $f['rend_m2_kg']    ? "  rendimiento_m2_kg={$f['rend_m2_kg']}" : '';
                $con = $f['confianza_ocr'] !== null ? "  conf_ocr={$f['confianza_ocr']}" : '';
                $ln[] = sprintf('  %-38s → %s', $f['imagen'], $f['producto']);
                $ln[] = sprintf('    versión=%s  lote=%.2f kg  comp_ocr=%d  filas_detalle=%d%s%s%s',
                    $f['version'], $f['lote_kg'], $f['n_ocr_comp'], $f['n_filas_detalle'], $gc, $rm, $con);
                if (!empty($f['fase_acuosa'])) {
                    $partes = [];
                    foreach ($f['fase_acuosa'] as $g => $fa) {
                        $partes[] = sprintf('%s=%.3f Kg (%.2f%%)', $g, $fa['kg'], $fa['pct']);
                    }
                    $ln[] = '    fase_acuosa (A2): ' . implode(' | ', $partes);
                }
                $ln[] = "    ya_existe: {$f['ya_existe']}";
            }
        }

        // §7 — Formulaciones OMITIDAS
        $ln[] = '';
        $ln[] = '§7  FORMULACIONES OMITIDAS';
        $ln[] = $s1;
        if (empty($d['fms_omit'])) {
            $ln[] = '  (ninguna)';
        } else {
            foreach ($d['fms_omit'] as $o) {
                $pid = isset($o['prod_id']) ? " (prod #{$o['prod_id']})" : '';
                $ln[] = "  {$o['imagen']}{$pid}: {$o['razon']}";
            }
        }

        // §8 — Precios 2025 (A8/A9)
        $ln[] = '';
        $ln[] = '§8  PRECIOS LISTA 2025 + RENDIMIENTOS CALCULADOS (A8/A9)';
        $ln[] = $s1;
        $n_crear_l  = 0;
        $n_mapea_l  = 0;
        foreach ($d['lista_2025'] as $p) {
            $tag = $p['mapeado_a']
                ? sprintf('MAPEA→#%d %-30s', $p['mapeado_a']['id'], $p['mapeado_a']['nombre'])
                : 'CREAR';
            if ($p['mapeado_a']) { $n_mapea_l++; } else { $n_crear_l++; }
            $cat = $p['categoria'] ? " [{$p['categoria']}]" : '';
            $ln[] = "  $tag  {$p['ocr_nombre']}$cat";
            foreach ($p['presentaciones'] as $pr) {
                $pnm = $pr['presentacion'] ?? '(sin pres.)';
                $prc = isset($pr['precio_sin_iva']) ? '$' . number_format($pr['precio_sin_iva'], 2) : '?';
                $rnd = $pr['rendimiento_teorico'] ?? '?';
                $rm2 = $pr['rendimiento_m2_kg']
                    ? " → {$pr['rendimiento_m2_kg']}"
                    : (!empty($pr['rendimiento_teorico']) ? ' → FALTANTE (sin contenido_neto de esa presentación)' : '');
                $ln[] = "    $pnm: $prc  ($rnd)$rm2";
            }
        }

        // §9 — Totales
        $ln[] = '';
        $ln[] = $s2;
        $ln[] = 'RESUMEN';
        $ln[] = $s1;
        $ln[] = '  Productos a USAR:             ' . count($d['prods_usar']);
        $ln[] = '  Productos a CREAR (fichas):   ' . count($d['prods_crear']);
        $ln[] = '  Productos a CREAR (lista2025):'  . $n_crear_l;
        $ln[] = '  Productos MAPEADOS (lista2025):' . $n_mapea_l;
        $ln[] = '  Insumos a VINCULAR:           ' . count($d['ins_vincular']);
        $ln[] = '  Insumos a CREAR:              ' . count($d['ins_crear']);
        $ln[] = '  Links Fase 3 (UPDATE):        ' . count($d['links_f3']);
        $ln[] = '  Formulaciones a CREAR:        ' . count($d['fms_crear']);
        $ln[] = '  Formulaciones OMITIDAS:       ' . count($d['fms_omit']);
        if (!empty($d['dry_run'])) {
            $ln[] = '  Escrituras detectadas:        ' . $d['write_counter'];
            $ln[] = '';
            $ln[] = 'NADA ESCRITO (DRY-RUN)';
        } else {
            $ln[] = '  MODO:                         ESCRITURA REAL (Fase 3)';
        }
        $ln[] = $s2;

        return implode("\n", $ln) . "\n";
    }

    // ═══════════════════════════════════════════════════════════════════════
    // FASE 3 — Aplicación real del plan aprobado en el dry-run
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Aplica el plan: insumos y productos nuevos, formulaciones (versiones nuevas) y
     * enlaces insumo→semielaborado, todo en una transacción. No escribe precios ni
     * rendimiento_m2_por_kg (A9: van a propuesta de negocio).
     */
    private function _aplicar_plan_json(array $plan, array $prods_crear, array $ins_crear, array $lista_2025, bool $activar_nuevas, array $links_f3, bool $solo_preflight = false): array
    {
        $log = [];
        $log[] = str_repeat('═', 80);
        $log[] = 'FASE 3 — APLICACIÓN REAL — ENTRENAMIENTO 3';
        $log[] = str_repeat('═', 80);

        // ── 1. Pre-flight: nada fuera del dry-run aprobado ────────────────
        $errores = [];
        foreach ($plan as $p) {
            $pi = $p['prod_info'];
            if ($pi['accion'] === 'usar_existente') {
                $row = $this->db->select('id, nombre')->where('id', (int)$pi['producto_id'])->get('productos')->row();
                if (!$row) { $errores[] = "Producto #{$pi['producto_id']} ({$pi['producto_nombre']}) no existe en productos."; }
            } elseif (!isset($prods_crear[$pi['nombre_propuesto']])) {
                $errores[] = "Producto a crear no aprobado en el dry-run: {$pi['nombre_propuesto']}";
            }
            foreach ($p['grupos'] as $items) {
                foreach ($items as $it) {
                    if ($it['insumo_crear']) {
                        if (!isset($ins_crear[$it['nombre']])) { $errores[] = "Insumo a crear no aprobado en el dry-run: {$it['nombre']}"; }
                    } elseif (empty($it['insumo_id'])) {
                        $errores[] = "Componente sin resolución aprobada: {$it['nombre']}";
                    } else {
                        $row = $this->db->select('id')->where('id', (int)$it['insumo_id'])->get('insumos')->row();
                        if (!$row) { $errores[] = "Insumo #{$it['insumo_id']} ({$it['nombre']}) no existe en insumos."; }
                    }
                }
            }
        }
        if ($errores) {
            $log[] = '';
            $log[] = 'PRE-FLIGHT FALLÓ — ABORTADO SIN ESCRIBIR:';
            foreach ($errores as $e) { $log[] = '  ✗ ' . $e; }
            return ['ok' => false, 'reporte' => implode("\n", $log) . "\n"];
        }
        $log[] = '';
        $log[] = sprintf('PRE-FLIGHT OK: %d formulaciones, %d productos y %d insumos por crear (todo dentro del dry-run aprobado).',
            count($plan), count($prods_crear), count($ins_crear));

        if ($solo_preflight) {
            $log[] = 'PRE-FLIGHT ONLY: no se escribió nada (--preflight).';
            return ['ok' => true, 'reporte' => implode("\n", $log) . "\n"];
        }

        // ── 2. Escrituras en una sola transacción ─────────────────────────
        $this->db->trans_start();

        $ins_ids  = [];
        $prod_ids = [];
        $n_forms  = 0;

        $log[] = '';
        $log[] = 'INSUMOS NUEVOS:';
        foreach ($ins_crear as $nom => $unidad) {
            $id = $this->_crear_insumo_json($nom, $unidad);
            if (!$id) { $errores[] = "No se pudo crear el insumo \"$nom\""; break; }
            $ins_ids[$nom] = $id;
            $log[] = sprintf('  + insumo #%d  %s  [%s]', $id, $nom, $unidad);
        }

        if (!$errores) {
            $log[] = '';
            $log[] = 'PRODUCTOS NUEVOS (fichas):';
            foreach ($prods_crear as $nom => $info) {
                $id = $this->_crear_producto_json(
                    $nom,
                    $info['categoria_ocr'] ?? '',
                    isset($info['precio_venta']) ? (float)$info['precio_venta'] : null,
                    'Cubeta',
                    $info['presentacion'] ?? null,
                    isset($info['contenido_neto']) ? (float)$info['contenido_neto'] : null,
                    $log
                );
                if (!$id) { $errores[] = "No se pudo crear el producto \"$nom\""; break; }
                $prod_ids[$nom] = $id;
            }
        }

        if (!$errores) {
            $log[] = '';
            $log[] = 'PRODUCTOS NUEVOS (lista 2025, sin precio — PASO 3 asigna precios):';
            foreach ($lista_2025 as $p) {
                if ($p['accion'] !== 'crear' || isset($prod_ids[$p['ocr_nombre']])) { continue; }
                $id = $this->_crear_producto_json(
                    $p['ocr_nombre'],
                    $p['categoria'] ?? '',
                    null,
                    $this->_unidad_venta_lista_json($p['presentaciones'] ?? []),
                    null,
                    null,
                    $log
                );
                if ($id) { $prod_ids[$p['ocr_nombre']] = $id; }
            }
        }

        if (!$errores) {
            $log[] = '';
            $log[] = 'FORMULACIONES (versiones nuevas, comentarios con la imagen de origen):';
            foreach ($plan as $f) {
                $pi  = $f['prod_info'];
                $pid = $pi['accion'] === 'usar_existente'
                     ? (int)$pi['producto_id']
                     : (int)($prod_ids[$pi['nombre_propuesto']] ?? 0);
                if (!$pid) { $errores[] = "Sin producto para {$f['imagen']}"; break; }

                $tiene_previas = $this->db->where('producto_id', $pid)->count_all_results('formulaciones') > 0;
                $activar       = $activar_nuevas && !$tiene_previas;

                $fid = $this->_insertar_formulacion_json($f, $pid, $ins_ids, $activar, $log);
                if (!$fid) { $errores[] = "No se pudo insertar la formulación de {$f['imagen']}"; break; }
                $n_forms++;
            }
        }

        if (!$errores) {
            $log[] = '';
            $log[] = 'ENLACES INSUMO→SEMIELABORADO (UPDATE puntual):';
            foreach ($links_f3 as $l) {
                if ((int)($l['insumo_id'] ?? 0) === 83) {
                    $log[] = '  ⏭ #83 → #214 OMITIDO (equivalencia SOLUCION DE RESINA ≡ EC-1 pendiente de validar)';
                    continue;
                }
                $iid = $l['insumo_id'] ? (int)$l['insumo_id'] : (int)($ins_ids['FASE ACUOSA'] ?? 0);
                if (!$iid) { $errores[] = "Sin insumo para el enlace al producto #{$l['producto_id']}"; break; }
                $this->db->where('id', $iid)->update('insumos', [
                    'tipo'        => 'fabricado',
                    'producto_id' => (int)$l['producto_id'],
                ]);
                $log[] = sprintf('  ✓ insumo #%d → tipo=fabricado, producto_id=%d (%s)', $iid, $l['producto_id'], $l['nombre_producto']);
            }
        }

        // ── 3. Bitácora + cierre de transacción ───────────────────────────
        if (!$errores) {
            $this->db->insert('log_importaciones', [
                'archivo'               => 'ENTRENAMIENTO_3 — ' . count($plan) . ' imágenes (JSON)',
                'usuario_id'            => 0,
                'productos_importados'  => count($prod_ids),
                'formulaciones_creadas' => $n_forms,
                'insumos_creados'       => count($ins_ids),
                'insumos_no_encontrados'=> 0,
                'errores'               => null,
                'estatus'               => 'Exitoso',
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false || $errores) {
            $log[] = '';
            $log[] = 'ERROR — TRANSACCIÓN REVERTIDA (no quedó nada escrito):';
            foreach ($errores as $e) { $log[] = '  ✗ ' . $e; }
            return ['ok' => false, 'reporte' => implode("\n", $log) . "\n"];
        }

        $log[] = '';
        $log[] = 'RESUMEN FASE 3:';
        $log[] = sprintf('  Insumos creados:        %d', count($ins_ids));
        $log[] = sprintf('  Productos creados:      %d', count($prod_ids));
        $log[] = sprintf('  Formulaciones nuevas:   %d (activas solo V1 de productos sin formulación previa)', $n_forms);
        $log[] = '  Enlaces aplicados:      4 (el #83 queda pendiente)';
        $log[] = '  rendimiento_m2_por_kg:  diferido a propuesta (A9)';
        $log[] = '  Precios lista 2025:     PASO 3';
        $log[] = str_repeat('═', 80);

        return ['ok' => true, 'reporte' => implode("\n", $log) . "\n"];
    }

    /**
     * Crea un insumo con código canónico INS-<slug> (nunca IMP-).
     */
    private function _crear_insumo_json(string $nombre, string $unidad): ?int
    {
        $base = 'INS-' . trim(substr(strtoupper(preg_replace('/[^A-Z0-9]+/i', '-', $nombre)), 0, 30), '-');
        $codigo = $base;
        $sufijo = 1;
        while ($this->db->where('codigo', $codigo)->count_all_results('insumos') > 0) {
            $sufijo++;
            $codigo = $base . '-' . $sufijo;
        }

        $ok = $this->db->insert('insumos', [
            'codigo'          => $codigo,
            'nombre_tecnico'  => $nombre,
            'alias'           => $nombre,
            'descripcion'     => 'Insumo creado por la carga Entrenamiento 3 (Fase 3).',
            'unidad_medida'   => in_array($unidad, ['Kg', 'L', 'Pza'], true) ? $unidad : 'Kg',
            'tipo'            => 'comprado',
            'precio_promedio' => 0,
            'estatus'         => 'Activo',
            'fecha_registro'  => date('Y-m-d H:i:s'),
        ]);

        return $ok ? (int)$this->db->insert_id() : null;
    }

    /**
     * Crea un producto Fabricado con código único, categoría resuelta por nombre y
     * precio solo si viene aprobado (null = pendiente).
     */
    private function _crear_producto_json(string $nombre, string $categoria_ocr, ?float $precio, string $unidad_venta, ?string $presentacion, ?float $contenido_neto, array &$log): ?int
    {
        $base = trim(substr(strtoupper(preg_replace('/[^A-Z0-9]+/i', '-', $nombre)), 0, 40), '-');
        $codigo = $base;
        $sufijo = 1;
        while ($this->db->where('codigo', $codigo)->count_all_results('productos') > 0) {
            $sufijo++;
            $codigo = $base . '-' . $sufijo;
        }

        $ok = $this->db->insert('productos', [
            'codigo'                 => $codigo,
            'nombre'                 => $nombre,
            'alias'                  => $nombre,
            'descripcion'            => 'Producto creado por la carga Entrenamiento 3. Completar ficha técnica.',
            'categoria_id'           => $this->_categoria_id_json($categoria_ocr),
            'tipo_producto'          => 'Fabricado',
            'unidad_venta'           => in_array($unidad_venta, ['Cubeta', 'Galon', 'Litro', 'Kg', 'Pieza'], true) ? $unidad_venta : 'Cubeta',
            'presentacion_principal' => $presentacion,
            'contenido_neto'         => $contenido_neto,
            'unidad_contenido'       => $contenido_neto !== null ? 'Kg' : null,
            'precio_venta'           => $precio,
            'estatus'                => 'Activo',
            'usuario_creacion'       => null,
            'fecha_creacion'         => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) { return null; }

        $id = (int)$this->db->insert_id();
        $log[] = sprintf('  + producto #%d  %-34s [%s] codigo=%s precio=%s',
            $id, $nombre, $categoria_ocr,
            $codigo,
            $precio === null ? 'PENDIENTE' : '$' . number_format($precio, 2));
        return $id;
    }

    /**
     * Resuelve categoria_id por nombre (mapa de A8; fallback Pinturas) sin hardcodear IDs.
     */
    private function _categoria_id_json(string $categoria_ocr): int
    {
        $map = [
            'SELLADORES'                      => 'Selladores',
            'PINTURAS ARQUITECTONICAS'        => 'Pinturas',
            'PASTAS ARQUITECTONICAS'          => 'Pastas',
            'PREPARADORES DE SUPERFICIE'      => 'Preparadores de Superficies',
            'IMPERMEABILIZANTE'               => 'Impermeabilizantes',
            'CHISA GLASS'                     => 'Recubrimientos',
            'POLYCOLOR'                       => 'Recubrimientos',
            'GRANOS DE MARMOL'                => 'Pastas',
            'SHELL HARD (CASCARA DE NARANJA)' => 'Recubrimientos',
        ];
        $nombre = $map[strtoupper(trim($categoria_ocr))] ?? 'Pinturas';
        $row = $this->db->where('LOWER(nombre)', strtolower($nombre))->get('categorias_productos')->row();
        return $row ? (int)$row->id : 1;
    }

    /**
     * unidad_venta del producto de la lista 2025 según sus presentaciones.
     */
    private function _unidad_venta_lista_json(array $presentaciones): string
    {
        foreach ($presentaciones as $p) {
            $nom = strtoupper(trim($p['presentacion'] ?? ''));
            if ($nom === 'CUBETA') { return 'Cubeta'; }
        }
        foreach ($presentaciones as $p) {
            $nom = strtoupper(trim($p['presentacion'] ?? ''));
            if ($nom === 'GALON') { return 'Galon'; }
            if ($nom === 'LITRO') { return 'Litro'; }
        }
        return 'Cubeta';
    }

    /**
     * Inserta la versión nueva y su detalle con los insumo_id aprobados (sin auto-crear nada).
     */
    private function _insertar_formulacion_json(array $f, int $producto_id, array $ins_ids, bool $activar, array &$log): ?int
    {
        $comentarios = 'Importado del entrenamiento 3 — imagen ' . $f['imagen'];
        if (!empty($f['notas']))     { $comentarios .= ' | ' . $f['notas']; }
        if (!empty($f['confianza'])) { $comentarios .= ' | OCR confianza ' . $f['confianza']; }

        $ok = $this->db->insert('formulaciones', [
            'producto_id'           => $producto_id,
            'cliente_id'            => null,
            'variante_descripcion'  => null,
            'referencia_cliente'    => null,
            'version'               => (int)ltrim($f['version'], 'Vv'),
            'nombre_version'        => $f['version'],
            'descripcion'           => 'Versión nueva cargada desde la ficha ' . $f['imagen'],
            'comentarios'           => $comentarios,
            'cantidad_producida'    => $f['total_kg'],
            'unidad_produccion'     => 'Kg',
            'rendimiento_m2_por_kg' => null, // A9: diferido a propuesta de negocio
            'es_activa'             => $activar ? 1 : 0,
            'fecha_creacion'        => date('Y-m-d H:i:s'),
            'usuario_creacion'      => null,
        ]);
        if (!$ok) { return null; }

        $fid   = (int)$this->db->insert_id();
        $orden = 0;
        $n     = 0;

        foreach ($f['grupos'] as $grupo => $items) {
            $grupo_label = ($grupo === '__default__') ? null : $grupo;

            foreach ($items as $it) {
                $insumo_id = $it['insumo_crear'] ? (int)($ins_ids[$it['nombre']] ?? 0) : (int)$it['insumo_id'];
                if (!$insumo_id) { return null; }

                $ins = $this->db->select('precio_promedio')->where('id', $insumo_id)->get('insumos')->row();

                $ok = $this->db->insert('detalle_formulacion', [
                    'formulacion_id'         => $fid,
                    'tipo_componente'        => 'Insumo',   // A3 opción (A): insumo enlazado a su fabricado
                    'insumo_id'              => $insumo_id,
                    'producto_id'            => null,
                    'cantidad'               => $it['kg'],
                    'unidad'                 => 'Kg',
                    'porcentaje'             => round((float)$it['porcentaje'], 2),
                    'costo_unitario'         => $ins ? (float)$ins->precio_promedio : 0,
                    'observaciones'          => null,
                    'grupo_color'            => $grupo_label,
                    'porcentaje_fase_acuosa' => $it['pct_fase'],
                    'kg_fase_acuosa'         => $it['kg_fase'],
                    'orden'                  => $orden++,
                ]);
                if (!$ok) { return null; }
                $n++;
            }
        }

        $log[] = sprintf('  → formulación #%d  producto #%d  %s  %d componentes%s',
            $fid, $producto_id, $f['version'], $n, $activar ? '  [ACTIVA — V1 de producto nuevo]' : '  [inactiva]');
        return $fid;
    }
}
