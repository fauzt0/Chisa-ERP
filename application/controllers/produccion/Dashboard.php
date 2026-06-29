<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {
    
    protected $modulo = 'Producción';
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Produccion/ProduccionModel');
        
        // El controlador base ya maneja la sesión y los permisos del módulo
    }
    
    /**
     * Vista principal del dashboard de producción
     */
    public function index() {
        setViewSuccess('Dashboard de Producción cargado correctamente');
        $this->viewData['pageTitle'] = 'Dashboard de Producción';
        $this->viewData['headTitle'] = 'Dashboard de Producción';
        $this->viewData['breadcrumb'] = 'Inicio > Producción > Dashboard';
        
        // Obtener filtros de la petición
        $filtros = [];
        
        // Búsqueda
        $busqueda = $this->input->get_post('busqueda');
        if($busqueda) {
            $filtros['busqueda'] = $busqueda;
        }
        
        // Estatus (puede ser array de checkboxes)
        $estatus = $this->input->get_post('estatus');
        if($estatus && is_array($estatus) && !empty($estatus)) {
            $filtros['estatus'] = $estatus;
        }
        
        // Obtener estadísticas y órdenes con filtros
        $stats = $this->ProduccionModel->get_estadisticas();
        $ordenes = $this->ProduccionModel->get_ordenes_dashboard($filtros);
        
        $this->viewData['response'] = [
            'stats' => $stats,
            'ordenes' => $ordenes,
            'filtros_activos' => $filtros // Para mantener estado en la vista
        ];
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'produccion/dashboard/main';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Obtiene órdenes para el dashboard (AJAX)
     */
    public function get_ordenes_ajax() {
        $filtro = $this->input->get('filtro') ?: 'todas';
        
        $ordenes = $this->ProduccionModel->get_ordenes_dashboard($filtro);
        
        echo json_encode([
            'success' => true,
            'ordenes' => $ordenes
        ]);
    }
    
    /**
     * Vista detallada de una orden de venta o obra con productos y formulaciones
     */
    public function detalle($tipo, $id) {
        setViewSuccess('Detalle cargado correctamente');
        
        if($tipo === 'orden_venta' || $tipo === 'orden') {
            // Orden de venta
            $registro = $this->ProduccionModel->get_orden_venta_detalle($id);
            $titulo = 'Orden ' . ($registro ? $registro->folio : '');
            $tipo_display = 'Orden de Venta';
        } else if($tipo === 'obra') {
            // Obra
            $registro = $this->ProduccionModel->get_obra_detalle($id);
            $titulo = 'Obra ' . ($registro ? $registro->folio : '');
            $tipo_display = 'Obra';
        } else {
            show_404();
            return;
        }
        
        if(!$registro) {
            show_404();
            return;
        }
        
        $this->viewData['pageTitle'] = 'Detalle - ' . $titulo;
        $this->viewData['headTitle'] = 'Detalle de ' . $tipo_display;
        $this->viewData['breadcrumb'] = 'Inicio > Producción > Dashboard > Detalle';
        
        $this->viewData['response'] = [
            'registro' => $registro,
            'tipo' => $tipo
        ];
        $this->viewData['validate'] = '';
        $this->viewData['pageView'] = 'produccion/dashboard/detalle';
        
        // Render views
        $this->load->view('layouts/general_template', $this->viewData);
    }
    
    /**
     * Actualiza el estatus de una orden de venta u obra (AJAX)
     * Si el estatus es 'Completada', genera lotes de producción automáticamente.
     */
    public function actualizar_estatus_ajax() {
        $orden_id      = $this->input->post('orden_id');
        $nuevo_estatus  = $this->input->post('estatus');
        $tipo           = $this->input->post('tipo') ?: 'venta'; // 'venta' o 'obra'

        if (!$orden_id || !$nuevo_estatus) {
            echo json_encode([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
            return;
        }

        // Determinar tabla según tipo
        $tabla = ($tipo === 'obra') ? 'obras' : 'ordenes_venta';

        // Actualizar el estatus de la orden y la fecha de completado
        $actualizar_data = [
            'estatus' => $nuevo_estatus,
            'fecha_completado_produccion' => ($nuevo_estatus === 'Completada') ? date('Y-m-d H:i:s') : null
        ];
        
        $this->db->where('id', $orden_id);
        $result = $this->db->update($tabla, $actualizar_data);

        if (!$result) {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar el estatus'
            ]);
            return;
        }

        // ── Si se marcó como Completada → generar lotes automáticamente ──
        $lotes_generados = [];

        if ($nuevo_estatus === 'Completada') {
            // Verificar si ya existen lotes para esta orden
            if ($tipo === 'obra') {
                $ya_tiene_lotes = $this->db
                    ->like('observaciones', 'obra_id:' . $orden_id)
                    ->count_all_results('lotes_produccion') > 0;
            } else {
                $ya_tiene_lotes = $this->db
                    ->where('orden_produccion_id', $orden_id)
                    ->count_all_results('lotes_produccion') > 0;
            }

            if (!$ya_tiene_lotes) {
                // Obtener productos de la orden
                if ($tipo === 'obra') {
                    $this->db->select('op.producto_id, op.cantidad_ajustada as cantidad, op.unidad, op.formulacion_id,
                                       p.nombre as producto_nombre, p.codigo as producto_codigo,
                                       f.nombre_version as formulacion_nombre');
                    $this->db->from('obras_productos op');
                    $this->db->join('productos p', 'p.id = op.producto_id', 'left');
                    $this->db->join('formulaciones f', 'f.id = op.formulacion_id', 'left');
                    $this->db->where('op.obra_id', $orden_id);
                } else {
                    $this->db->select('dov.producto_id, dov.cantidad, dov.unidad, dov.formulacion_id,
                                       p.nombre as producto_nombre, p.codigo as producto_codigo,
                                       f.nombre_version as formulacion_nombre');
                    $this->db->from('detalle_orden_venta dov');
                    $this->db->join('productos p', 'p.id = dov.producto_id', 'left');
                    $this->db->join('formulaciones f', 'f.id = dov.formulacion_id', 'left');
                    $this->db->where('dov.orden_venta_id', $orden_id);
                }
                $productos_orden = $this->db->get()->result();

                foreach ($productos_orden as $prod) {
                    // Intentar llamar al SP sp_generar_codigo_barras(producto_id INT, OUT codigo)
                    $codigo_barras = null;
                    try {
                        $producto_id_int = (int) $prod->producto_id;
                        $this->db->query("CALL sp_generar_codigo_barras({$producto_id_int}, @sp_codigo)");
                        $res = $this->db->query('SELECT @sp_codigo AS codigo');
                        if ($res) {
                            $row_sp = $res->row();
                            $codigo_barras = ($row_sp && $row_sp->codigo) ? $row_sp->codigo : null;
                        }
                    } catch (Exception $e) {
                        $codigo_barras = null; // Usar fallback
                    }

                    // Fallback: código único garantizado
                    if (!$codigo_barras) {
                        $codigo_barras = 'PROD-' . date('Ymd') . '-' . $prod->producto_id . '-' . rand(1000, 9999);
                        // Re-verificar unicidad
                        while ($this->db->where('codigo_barras', $codigo_barras)->count_all_results('lotes_produccion') > 0) {
                            $codigo_barras = 'PROD-' . date('Ymd') . '-' . $prod->producto_id . '-' . rand(1000, 9999);
                        }
                    }

                    $obs = ($tipo === 'obra')
                        ? 'Generado al completar obra. obra_id:' . $orden_id
                        : 'Generado al completar orden de venta.';

                    // Cantidad: para obras usar cantidad_ajustada (si existe) sino calculada
                    $cantidad_lote = !empty($prod->cantidad) ? $prod->cantidad : 1;

                    $lote_data = [
                        'codigo_barras'       => $codigo_barras,
                        'orden_produccion_id' => null,   // Ya no es obligatorio
                        'orden_venta_id'      => ($tipo === 'venta') ? $orden_id : null,
                        'obra_id'             => ($tipo === 'obra') ? $orden_id : null,
                        'producto_id'         => $prod->producto_id,
                        'formulacion_id'      => $prod->formulacion_id ?: null,
                        'cantidad'            => $cantidad_lote,             // ← columna real
                        'unidad'              => $prod->unidad ?: 'pz',
                        'fecha_produccion'    => date('Y-m-d H:i:s'),
                        'estatus'             => 'Producido',             // ← ENUM real
                        'observaciones'       => $obs,
                    ];

                    $this->db->insert('lotes_produccion', $lote_data);
                    $lote_id = $this->db->insert_id();

                    $lotes_generados[] = [
                        'id'            => $lote_id,
                        'codigo_barras' => $codigo_barras,
                        'producto'      => $prod->producto_nombre,
                        'producto_id'   => $prod->producto_id, // Añadido para procesamiento de inventario
                        'cantidad'      => $cantidad_lote,
                        'unidad'        => $prod->unidad ?: 'pz',
                    ];
                }

                // ── PROCESAR MOVIMIENTOS DE INVENTARIO (Entradas/Salidas) ──
                if (!empty($lotes_generados)) {
                    $this->ProduccionModel->procesar_inventario_por_produccion($orden_id, $tipo, $lotes_generados);
                }
            }
        }

        echo json_encode([
            'success'         => true,
            'message'         => 'Estatus actualizado correctamente',
            'lotes_generados' => $lotes_generados,
        ]);
    }

    /**
     * Verifica si hay nuevas órdenes (AJAX - para actualización en tiempo real)
     */
    public function check_nuevas_ordenes_ajax() {
        $ultima_verificacion = $this->input->get('ultima_verificacion');
        $filtros_json = $this->input->get('filtros');
        
        // Decodificar filtros
        $filtros = $filtros_json ? json_decode($filtros_json, true) : [];
        
        // Obtener órdenes actuales con filtros
        $ordenes_actuales = $this->ProduccionModel->get_ordenes_dashboard($filtros);
        
        // Si hay timestamp de última verificación, buscar órdenes nuevas
        $ordenes_nuevas = [];
        if($ultima_verificacion) {
            foreach($ordenes_actuales as $orden) {
                $fecha_creacion_timestamp = strtotime($orden->fecha_creacion);
                if($fecha_creacion_timestamp > $ultima_verificacion) {
                    $ordenes_nuevas[] = $orden;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'hay_nuevas' => count($ordenes_nuevas) > 0,
            'cantidad_nuevas' => count($ordenes_nuevas),
            'ordenes_nuevas' => $ordenes_nuevas,
            'total_ordenes' => count($ordenes_actuales),
            'timestamp' => time()
        ]);
    }
    
    /**
     * Obtiene detalle de una formulación (AJAX)
     */
    public function get_formulacion_detalle_ajax() {
        $formulacion_id = $this->input->post('formulacion_id');
        
        if(!$formulacion_id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }
        
        $this->load->model('Produccion/ProductosModel');
        $formulacion = $this->ProductosModel->get_formulacion_completa($formulacion_id);
        
        if($formulacion) {
            echo json_encode(['success' => true, 'formulacion' => $formulacion]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Formulación no encontrada']);
        }
    }

    // =====================================================
    // NUEVOS ENDPOINTS: VERIFICACIÓN DE STOCK Y PRE-ÓRDENES
    // =====================================================

    /**
     * Verifica el stock de insumos para una orden/obra (AJAX)
     * Retorna la tabla de insumos requeridos vs disponibles
     */
    public function verificar_stock_ajax() {
        $orden_id = $this->input->post('orden_id');
        $tipo     = $this->input->post('tipo'); // 'venta' o 'obra'

        if (!$orden_id || !$tipo) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            return;
        }

        $resultado = $this->ProduccionModel->get_insumos_requeridos_para_orden($orden_id, $tipo);

        echo json_encode([
            'success'          => true,
            'stock_suficiente' => $resultado['stock_suficiente'],
            'insumos'          => $resultado['insumos'],
            'sin_formulacion'  => $resultado['sin_formulacion'],
        ]);
    }

    /**
     * Genera pre-órdenes de compra (Borrador) para los insumos faltantes (AJAX)
     * Agrupa automáticamente por proveedor principal
     */
    public function generar_preorden_compra_ajax() {
        $orden_id    = $this->input->post('orden_id');
        $tipo        = $this->input->post('tipo');
        $folio_origen = $this->input->post('folio_origen');

        if (!$orden_id || !$tipo) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            return;
        }

        // Obtener insumos faltantes
        $resultado = $this->ProduccionModel->get_insumos_requeridos_para_orden($orden_id, $tipo);
        $insumos_faltantes = array_filter($resultado['insumos'], fn($i) => !$i['disponible']);

        if (empty($insumos_faltantes)) {
            echo json_encode(['success' => false, 'message' => 'No hay insumos faltantes para generar pre-orden']);
            return;
        }

        // Agrupar por proveedor principal
        $por_proveedor = [];
        $sin_proveedor = [];

        foreach ($insumos_faltantes as $insumo) {
            $this->db->select('pi.proveedor_id, pr.razon_social as proveedor_nombre');
            $this->db->from('proveedor_insumo pi');
            $this->db->join('proveedores pr', 'pr.id = pi.proveedor_id');
            $this->db->where('pi.insumo_id', $insumo['insumo_id']);
            $this->db->where('pi.es_proveedor_principal', 1);
            $this->db->limit(1);
            $prov = $this->db->get()->row();

            if ($prov) {
                $por_proveedor[$prov->proveedor_id]['nombre']   = $prov->proveedor_nombre;
                $por_proveedor[$prov->proveedor_id]['insumos'][] = $insumo;
            } else {
                // Sin proveedor principal, intentar cualquier proveedor
                $this->db->select('pi.proveedor_id, pr.razon_social as proveedor_nombre');
                $this->db->from('proveedor_insumo pi');
                $this->db->join('proveedores pr', 'pr.id = pi.proveedor_id');
                $this->db->where('pi.insumo_id', $insumo['insumo_id']);
                $this->db->limit(1);
                $prov2 = $this->db->get()->row();

                if ($prov2) {
                    $por_proveedor[$prov2->proveedor_id]['nombre']   = $prov2->proveedor_nombre;
                    $por_proveedor[$prov2->proveedor_id]['insumos'][] = $insumo;
                } else {
                    $sin_proveedor[] = $insumo['insumo_nombre'];
                }
            }
        }

        // Crear una orden de compra (Borrador) por cada proveedor
        $ordenes_creadas = [];
        $this->db->trans_start();

        foreach ($por_proveedor as $proveedor_id => $datos) {
            // Generar folio
            $count = $this->db->count_all('ordenes_compra') + 1;
            $folio = 'OC-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            // Calcular total estimado
            $total = array_sum(array_map(fn($i) => $i['costo_estimado_faltante'], $datos['insumos']));

            // Crear orden de compra en Borrador
            $oc_data = [
                'folio'         => $folio,
                'proveedor_id'  => $proveedor_id,
                'estatus'       => 'Borrador',
                'tipo_orden'    => 'Insumos',
                'total'         => $total,
                'observaciones' => 'Pre-orden generada automáticamente desde producción. Orden origen: ' . $folio_origen,
                'origen'        => 'Produccion',
                'fecha_creacion'=> date('Y-m-d H:i:s'),
            ];

            $this->db->insert('ordenes_compra', $oc_data);
            $oc_id = $this->db->insert_id();

            // Agregar los detalles de la orden
            foreach ($datos['insumos'] as $insumo) {
                $this->db->insert('detalle_orden_compra', [
                    'orden_compra_id' => $oc_id,
                    'insumo_id'       => $insumo['insumo_id'],
                    'cantidad'        => $insumo['faltante'],
                    'unidad'          => $insumo['unidad'],
                    'precio_unitario' => $insumo['precio_promedio'],
                    'subtotal'        => $insumo['costo_estimado_faltante'],
                ]);
            }

            $ordenes_creadas[] = [
                'id'             => $oc_id,
                'folio'          => $folio,
                'proveedor'      => $datos['nombre'],
                'total'          => $total,
                'num_insumos'    => count($datos['insumos']),
            ];
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Error al crear las pre-órdenes de compra']);
            return;
        }

        echo json_encode([
            'success'         => true,
            'message'         => count($ordenes_creadas) . ' pre-orden(es) creada(s) correctamente',
            'ordenes_creadas' => $ordenes_creadas,
            'sin_proveedor'   => $sin_proveedor,
        ]);
    }

    /**
     * Obtiene el estado de stock de múltiples órdenes para el dashboard (AJAX)
     */
    public function get_stock_estado_ordenes_ajax() {
        $ordenes_json = $this->input->post('ordenes');
        if (!$ordenes_json) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            return;
        }

        $ordenes  = json_decode($ordenes_json, true);
        $estados = $this->ProduccionModel->get_estado_stock_multiple($ordenes);

        echo json_encode(['success' => true, 'estados' => $estados]);
    }

    /**
     * Obtiene los lotes generados para una orden (AJAX — para mostrar etiquetas)
     */
    public function get_lotes_orden_ajax() {
        $orden_id = $this->input->post('orden_id');
        $tipo     = $this->input->post('tipo');

        if (!$orden_id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        // Buscar lotes de producción asociados a esta orden
        $this->db->select('lp.*, p.nombre as producto_nombre, p.codigo as producto_codigo');
        $this->db->from('lotes_produccion lp');
        $this->db->join('productos p', 'p.id = lp.producto_id', 'left'); // LEFT para no omitir si producto fue eliminado

        if ($tipo === 'obra') {
            $this->db->where('lp.obra_id', $orden_id);
        } else {
            $this->db->where('lp.orden_venta_id', $orden_id);
        }

        $this->db->order_by('lp.fecha_produccion', 'DESC');
        $lotes = $this->db->get()->result();

        echo json_encode(['success' => true, 'lotes' => $lotes]);
    }

    /**
     * Genera la vista de etiqueta imprimible para un lote (sin layout)
     */
    public function etiqueta_lote($lote_id) {
        $this->db->select('lp.*, p.nombre as producto_nombre, p.codigo as producto_codigo, f.nombre_version as formulacion_nombre');
        $this->db->from('lotes_produccion lp');
        $this->db->join('productos p', 'p.id = lp.producto_id');
        $this->db->join('formulaciones f', 'f.id = lp.formulacion_id', 'left');
        $this->db->where('lp.id', $lote_id);
        $lote = $this->db->get()->row();

        if (!$lote) {
            show_404();
            return;
        }

        $data['lote'] = $lote;
        // Render sin layout (standalone para impresión)
        $this->load->view('produccion/dashboard/etiqueta_lote', $data);
    }
    // =====================================================
    // ENDPOINTS BOM EXPLOSION + CATÁLOGO TOUCHSCREEN
    // =====================================================

    /**
     * Explota el BOM de una formulación y retorna árbol JSON (AJAX)
     */
    public function explotar_bom_ajax() {
        $formulacion_id = (int)$this->input->post('formulacion_id');
        $cantidad_kg    = (float)($this->input->post('cantidad_kg') ?: 1);

        if (!$formulacion_id) {
            echo json_encode(['success' => false, 'message' => 'ID de formulación requerido']);
            return;
        }

        $this->load->model('Produccion/ProductosModel');
        $visitados = [];
        $arbol = $this->ProductosModel->explotar_bom_arbol($formulacion_id, $cantidad_kg, 0, $visitados);
        $plano = $this->ProductosModel->explotar_bom_plano($formulacion_id, $cantidad_kg, 0, $visitados);

        echo json_encode([
            'success' => true,
            'arbol'   => $arbol,
            'plano'   => $plano,
            'cantidad_kg' => $cantidad_kg,
        ]);
    }

    /**
     * Busca formulaciones por término (AJAX - panel touchscreen)
     */
    public function buscar_formulaciones_ajax() {
        $termino = trim($this->input->post('termino') ?: '');
        $this->load->model('Produccion/ProductosModel');
        $resultados = $this->ProductosModel->buscar_formulaciones($termino, 40);

        echo json_encode(['success' => true, 'formulaciones' => $resultados]);
    }

    /**
     * Obtiene catálogo de productos para el panel touchscreen (AJAX)
     */
    public function get_catalogo_ajax() {
        $categoria_id = $this->input->post('categoria_id') ?: null;
        $termino      = trim($this->input->post('termino') ?: '');

        $this->load->model('Produccion/ProductosModel');
        $productos    = $this->ProductosModel->get_catalogo_touchscreen($categoria_id, $termino);
        $categorias   = $this->ProductosModel->get_categorias_select();

        echo json_encode([
            'success'   => true,
            'productos' => $productos,
            'categorias'=> $categorias,
        ]);
    }

    /**
     * Historial de órdenes completadas (AJAX)
     */
    public function get_historial_ajax() {
        $busqueda = trim($this->input->get_post('busqueda') ?: '');
        $this->load->model('Produccion/ProductosModel');
        $historial = $this->ProductosModel->get_historial_ordenes_produccion([
            'busqueda' => $busqueda,
            'limite'   => 60,
        ]);

        echo json_encode(['success' => true, 'historial' => $historial]);
    }

    public function aplicar_formulacion_orden_ajax() {
        $orden_id = $this->input->post('orden_id');
        $tipo_orden = $this->input->post('tipo_orden');
        $producto_id = $this->input->post('producto_id');
        $formulacion_id = $this->input->post('formulacion_id');
        $set_as_default = $this->input->post('set_as_default');

        if(!$orden_id || !$producto_id || !$formulacion_id) {
            echo json_encode(['success' => false, 'message' => 'Faltan parámetros requeridos']);
            return;
        }

        $this->db->trans_start();

        // 1. Actualizar la formulación de la orden actual
        if ($tipo_orden === 'venta') {
            $this->db->where('orden_venta_id', $orden_id);
            $this->db->where('producto_id', $producto_id);
            $this->db->update('detalle_orden_venta', ['formulacion_id' => $formulacion_id]);
        } else {
            $this->db->where('obra_id', $orden_id);
            $this->db->where('producto_id', $producto_id);
            $this->db->update('obras_productos', ['formulacion_id' => $formulacion_id]);
        }

        // 2. Si el usuario desea establecerla como default, usar el modelo
        if ($set_as_default == 1) {
            $this->load->model('Produccion/ProductosModel');
            $this->ProductosModel->activar_formulacion($formulacion_id);
        }

        // 3. Registrar en log (Opcional pero recomendado, simulado aquí actualizando fecha)
        // Puedes agregar una entrada en tu log de auditoría si tienes tabla para ello
        
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'message' => 'Error al aplicar la formulación en la base de datos']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Formulación aplicada correctamente a la orden']);
        }
    }
}
