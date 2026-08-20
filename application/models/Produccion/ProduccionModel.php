<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProduccionModel extends CI_Model {
    
    /**
     * Obtiene órdenes de venta Y obras para el dashboard de producción
     */
    public function get_ordenes_dashboard($filtros = []) {
        $registros = [];
        
        // Valores por defecto
        $defaults = [
            'busqueda' => '',
            'estatus' => ['Confirmada', 'En Preparación', 'En Ejecución', 'Aprobada'] // Incluye estatus de obras
        ];
        
        $filtros = array_merge($defaults, $filtros);
        
        // 1. OBTENER ÓRDENES DE VENTA
        $this->db->select('
            ov.id,
            ov.folio,
            ov.fecha_creacion,
            ov.tipo_venta,
            ov.estatus,
            ov.total,
            c.razon_social as cliente,
            c.nombre_comercial,
            "orden_venta" as tipo_registro
        ');
        $this->db->from('ordenes_venta ov');
        $this->db->join('clientes c', 'c.id = ov.cliente_id', 'left');
        
        // Aplicar filtro de búsqueda
        if(!empty($filtros['busqueda'])) {
            $this->db->group_start();
            $this->db->like('ov.folio', $filtros['busqueda']);
            $this->db->or_like('c.razon_social', $filtros['busqueda']);
            $this->db->or_like('c.nombre_comercial', $filtros['busqueda']);
            $this->db->group_end();
        }
        
        // Aplicar filtro de estatus para órdenes
        if(!empty($filtros['estatus']) && is_array($filtros['estatus'])) {
            $estatus_ordenes = array_intersect($filtros['estatus'], ['Confirmada', 'En Preparación', 'Entregada']);
            if(!empty($estatus_ordenes)) {
                $this->db->where_in('ov.estatus', $estatus_ordenes);
            }
        }
        
        $this->db->order_by('ov.fecha_creacion', 'DESC');
        $ordenes_venta = $this->db->get()->result();
        
        // Agregar conteo de productos a cada orden
        foreach($ordenes_venta as $orden) {
            $this->db->where('orden_venta_id', $orden->id);
            $orden->total_productos = $this->db->count_all_results('detalle_orden_venta');
        }
        
        // 2. OBTENER OBRAS
        $this->db->select('
            o.id,
            o.folio,
            o.fecha_creacion,
            "Obra" as tipo_venta,
            o.estatus,
            o.total,
            c.razon_social as cliente,
            o.nombre as nombre_comercial,
            "obra" as tipo_registro
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        
        // Aplicar filtro de búsqueda para obras
        if(!empty($filtros['busqueda'])) {
            $this->db->group_start();
            $this->db->like('o.folio', $filtros['busqueda']);
            $this->db->or_like('o.nombre', $filtros['busqueda']);
            $this->db->or_like('c.razon_social', $filtros['busqueda']);
            $this->db->group_end();
        }
        
        // Aplicar filtro de estatus para obras
        if(!empty($filtros['estatus']) && is_array($filtros['estatus'])) {
            $estatus_obras = array_intersect($filtros['estatus'], ['Planificación', 'En Cotización', 'Aprobada', 'En Ejecución', 'Pausada', 'Completada']);
            if(!empty($estatus_obras)) {
                $this->db->where_in('o.estatus', $estatus_obras);
            }
        }
        
        $this->db->order_by('o.fecha_creacion', 'DESC');
        $obras = $this->db->get()->result();
        
        // Agregar conteo de productos a cada obra
        foreach($obras as $obra) {
            $this->db->where('obra_id', $obra->id);
            $obra->total_productos = $this->db->count_all_results('obras_productos');
        }
        
        // 3. COMBINAR Y ORDENAR POR FECHA
        $registros = array_merge($ordenes_venta, $obras);
        
        usort($registros, function($a, $b) {
            return strtotime($b->fecha_creacion) - strtotime($a->fecha_creacion);
        });
        
        return $registros;
    }
    
    /**
     * Obtiene detalle completo de una orden de venta con productos y formulaciones
     */
    public function get_orden_venta_detalle($orden_id) {
        // Obtener datos de la orden
        $this->db->select('
            ov.*,
            c.razon_social as cliente,
            c.nombre_comercial,
            c.telefono,
            c.email
        ');
        $this->db->from('ordenes_venta ov');
        $this->db->join('clientes c', 'c.id = ov.cliente_id', 'left');
        $this->db->where('ov.id', $orden_id);
        
        $orden = $this->db->get()->row();
        
        if(!$orden) {
            return null;
        }
        
        // Obtener productos de la orden con la formulación seleccionada
        $this->db->select('
            dov.*,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo,
            p.descripcion as producto_descripcion,
            p.unidad_venta,
            p.foto_producto,
            dov.formulacion_id,
            f.version as formulacion_version,
            f.nombre_version as formulacion_nombre
        ');
        $this->db->from('detalle_orden_venta dov');
        $this->db->join('productos p', 'p.id = dov.producto_id');
        $this->db->join('formulaciones f', 'f.id = dov.formulacion_id', 'left');
        $this->db->where('dov.orden_venta_id', $orden_id);
        
        $orden->productos = $this->db->get()->result();
        
        return $orden;
    }
    
    /**
     * Obtiene detalle completo de una obra con productos y formulaciones
     */
    public function get_obra_detalle($obra_id) {
        // Obtener datos de la obra con cliente
        $this->db->select('
            o.*,
            c.razon_social as cliente,
            c.nombre_comercial,
            c.telefono,
            c.email
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->where('o.id', $obra_id);
        $obra = $this->db->get()->row();
        
        if(!$obra) {
            return null;
        }
        
        // Obtener productos de la obra con formulaciones
        $this->db->select('
            op.*,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo,
            p.descripcion as producto_descripcion,
            p.unidad_venta,
            p.foto_producto,
            f.id as formulacion_id,
            f.version as formulacion_version,
            f.nombre_version as formulacion_nombre,
            f.costo_total as formulacion_costo
        ');
        $this->db->from('obras_productos op');
        $this->db->join('productos p', 'p.id = op.producto_id');
        $this->db->join('formulaciones f', 'f.id = op.formulacion_id', 'left');
        $this->db->where('op.obra_id', $obra_id);
        $this->db->order_by('op.fecha_agregado', 'DESC');
        
        $productos = $this->db->get()->result();
        
        // Agregar componentes a cada formulación
        $this->load->model('Produccion/ProductosModel');
        foreach($productos as $producto) {
            if($producto->formulacion_id) {
                $formulacion = $this->ProductosModel->get_formulacion_completa($producto->formulacion_id);
                $producto->formulacion_componentes = $formulacion ? $formulacion->componentes : [];
            } else {
                $producto->formulacion_componentes = [];
            }
        }
        
        $obra->productos = $productos;
        
        return $obra;
    }
    
    /**
     * Obtiene detalle completo de una orden de producción
     */
    public function get_orden_detalle($orden_id) {
        // Obtener datos de la orden
        $this->db->select('op.*');
        $this->db->from('ordenes_produccion op');
        $this->db->where('op.id', $orden_id);
        
        $orden = $this->db->get()->row();
        
        if($orden) {
            // Obtener cliente si hay orden_venta_id
            if($orden->orden_venta_id) {
                $this->db->select('c.razon_social, c.nombre_comercial, c.telefono, c.email');
                $this->db->from('ordenes_venta ov');
                $this->db->join('clientes c', 'c.id = ov.cliente_id');
                $this->db->where('ov.id', $orden->orden_venta_id);
                $cliente = $this->db->get()->row();
                
                if($cliente) {
                    $orden->cliente = $cliente->razon_social;
                    $orden->nombre_comercial = $cliente->nombre_comercial;
                    $orden->telefono = $cliente->telefono;
                    $orden->email = $cliente->email;
                }
            } else {
                $orden->cliente = 'Producción interna';
            }
            
            // Obtener productos con formulaciones
            $orden->productos = $this->get_productos_con_formulacion($orden_id);
        }
        
        return $orden;
    }
    
    /**
     * Obtiene productos con su formulación completa
     */
    public function get_productos_con_formulacion($orden_id) {
        $this->db->select('
            dop.*,
            p.nombre as producto_nombre,
            p.codigo,
            p.unidad_venta,
            p.descripcion,
            f.id as formulacion_id,
            f.version as formulacion_version,
            f.descripcion as formulacion_descripcion
        ');
        $this->db->from('detalle_orden_produccion dop');
        $this->db->join('productos p', 'p.id = dop.producto_id');
        $this->db->join('formulaciones f', 'f.id = dop.formulacion_id', 'left');
        $this->db->where('dop.orden_produccion_id', $orden_id);
        
        $productos = $this->db->get()->result();
        
        // Obtener componentes de cada formulación
        foreach($productos as $producto) {
            if($producto->formulacion_id) {
                $producto->componentes = $this->get_componentes_formulacion(
                    $producto->formulacion_id, 
                    $producto->cantidad
                );
            } else {
                $producto->componentes = [];
            }
        }
        
        return $productos;
    }
    
    /**
     * Obtiene componentes de una formulación con cantidades escaladas
     */
    public function get_componentes_formulacion($formulacion_id, $cantidad_producto) {
        $this->db->select('
            df.*,
            i.nombre_tecnico as insumo_nombre,
            i.codigo as insumo_codigo,
            df.unidad as unidad_medida
        ');
        $this->db->from('detalle_formulacion df');
        $this->db->join('insumos i', 'i.id = df.insumo_id AND df.tipo_componente = "Insumo"', 'left');
        $this->db->where('df.formulacion_id', $formulacion_id);
        $this->db->where('df.tipo_componente', 'Insumo');
        $this->db->order_by('df.orden', 'ASC');
        
        $componentes = $this->db->get()->result();
        
        // Escalar cantidades según la cantidad a producir
        foreach($componentes as $componente) {
            $componente->cantidad_necesaria = $componente->cantidad * $cantidad_producto;
        }
        
        return $componentes;
    }
    
    /**
     * Actualiza el estatus de una orden de producción
     */
    public function actualizar_estatus($orden_id, $nuevo_estatus) {
        $data = ['estatus' => $nuevo_estatus];
        
        // Si pasa a "En Proceso", registrar fecha_inicio
        if($nuevo_estatus == 'En Proceso') {
            $data['fecha_inicio'] = date('Y-m-d H:i:s');
        }
        
        // Si pasa a "Completada", validar insumos/pesaje (P6) — no descontar aquí
        if($nuevo_estatus == 'Completada') {
            $this->db->select('orden_venta_id');
            $this->db->where('id', $orden_id);
            $op_row = $this->db->get('ordenes_produccion')->row();

            if ($op_row && !empty($op_row->orden_venta_id)) {
                $validacion = $this->puede_completar_produccion($op_row->orden_venta_id, 'venta', false);
                if (empty($validacion['ok'])) {
                    return [
                        'success' => false,
                        'message' => $validacion['message'],
                        'tipo_error' => 'insumos_pendientes',
                    ];
                }
            } elseif (!$this->insumos_ya_consumidos('op', $orden_id)) {
                return [
                    'success' => false,
                    'message' => 'Debe confirmar el pesaje y descontar insumos antes de completar.',
                    'tipo_error' => 'insumos_pendientes',
                ];
            }

            $data['fecha_completado'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where('id', $orden_id);
        $actualizado = $this->db->update('ordenes_produccion', $data);
        
        // Si la actualización fue exitosa y era una completación
        if($actualizado && $nuevo_estatus == 'Completada') {
            return [
                'success' => true,
                'message' => 'Orden completada correctamente',
            ];
        }
        
        return $actualizado;
    }
    
    /**
     * Descuenta insumos para una orden de producción legacy (tabla ordenes_produccion).
     * Usa motor P5.1 (BOM multinivel) y movimientos_inventario. No descuenta dos veces.
     *
     * @param int $orden_id ID de la orden de producción
     * @return array ['success' => bool, 'message' => string, 'detalles' => array]
     */
    public function descontar_stock_produccion($orden_id) {
        $orden_id = (int) $orden_id;

        $this->db->select('op.*, p.nombre as producto_nombre, p.codigo as producto_codigo, p.unidad_venta');
        $this->db->from('ordenes_produccion op');
        $this->db->join('productos p', 'p.id = op.producto_id');
        $this->db->where('op.id', $orden_id);
        $orden = $this->db->get()->row();

        if (!$orden) {
            return ['success' => false, 'message' => 'Orden de producción no encontrada', 'detalles' => []];
        }

        if (!empty($orden->orden_venta_id) && $this->insumos_ya_consumidos('venta', $orden->orden_venta_id)) {
            return [
                'success' => true,
                'message' => 'Insumos ya descontados vía pesaje de la orden de venta vinculada.',
                'detalles'  => [],
                'omitido'   => true,
            ];
        }

        if ($this->insumos_ya_consumidos('op', $orden_id)) {
            return [
                'success' => true,
                'message' => 'Insumos ya descontados para esta orden de producción.',
                'detalles'  => [],
                'omitido'   => true,
            ];
        }

        $this->load->model('Produccion/ProductosModel');

        if (!empty($orden->orden_venta_id)) {
            $verificacion = $this->get_insumos_requeridos_para_orden($orden->orden_venta_id, 'venta');
            $ref_tipo = 'venta';
            $ref_id = (int) $orden->orden_venta_id;
        } else {
            $verificacion = $this->ProductosModel->verificar_insumos_linea_produccion(
                (int) $orden->formulacion_id,
                (float) $orden->cantidad_programada,
                $orden->unidad_medida,
                (int) $orden->producto_id
            );
            $ref_tipo = 'op';
            $ref_id = $orden_id;
        }

        if (!empty($verificacion['revision_manual'])) {
            return [
                'success' => false,
                'message' => 'Hay insumos con unidades ambiguas que requieren revisión manual.',
                'detalles' => $verificacion['revision_manual'],
                'tipo_error' => 'revision_manual',
            ];
        }

        $insumos = $verificacion['insumos'] ?? array_merge(
            $verificacion['faltantes'] ?? [],
            $verificacion['suficientes'] ?? []
        );

        if (empty($insumos)) {
            return ['success' => true, 'message' => 'No hay insumos que descontar.', 'detalles' => []];
        }

        $faltantes = array_filter($insumos, fn($i) => empty($i['disponible']) && (($i['cantidad_faltante'] ?? $i['faltante'] ?? 0) > 0));
        if (!empty($faltantes)) {
            $mensaje = "Stock insuficiente para completar la orden:\n\n";
            foreach ($faltantes as $ins) {
                $req = $ins['cantidad_en_unidad_insumo'] ?? $ins['cantidad_requerida'] ?? 0;
                $fal = $ins['cantidad_faltante'] ?? $ins['faltante'] ?? 0;
                $mensaje .= '• ' . ($ins['insumo_codigo'] ?? '') . ' - ' . ($ins['insumo_nombre'] ?? '') .
                    ': Necesario ' . round($req, 3) . ' ' . ($ins['unidad_insumo'] ?? $ins['unidad'] ?? '') .
                    ', Faltante ' . round($fal, 3) . "\n";
            }
            return ['success' => false, 'message' => $mensaje, 'detalles' => array_values($faltantes), 'tipo_error' => 'stock_insuficiente'];
        }

        $usuario_id = (int) ($this->session->userdata('id') ?: $this->session->userdata('user_id') ?: 1);
        $ref = $this->referencia_pesaje($ref_tipo, $ref_id);
        $pesajes = [];

        foreach ($insumos as $ins) {
            $cantidad = (float) ($ins['cantidad_en_unidad_insumo'] ?? $ins['cantidad_requerida'] ?? 0);
            if ($cantidad <= 0) {
                continue;
            }
            $pesajes[] = [
                'insumo_id'      => (int) $ins['insumo_id'],
                'cantidad_real'  => $cantidad,
            ];
        }

        if (empty($pesajes)) {
            return ['success' => true, 'message' => 'No hay cantidades teóricas para descontar.', 'detalles' => []];
        }

        if ($ref_tipo === 'venta') {
            $resultado = $this->confirmar_pesaje_y_descontar($ref_id, 'venta', $pesajes, $usuario_id);
        } else {
            $resultado = $this->_descontar_insumos_por_referencia($ref, $pesajes, $usuario_id, 'OP #' . $orden_id);
        }

        if (empty($resultado['success'])) {
            return $resultado;
        }

        return [
            'success' => true,
            'message' => $resultado['message'],
            'detalles' => $resultado['detalles'] ?? [],
        ];
    }
    
    /**
     * Genera un código de barras único
     */
    public function generar_codigo_barras($producto_id) {
        // Llamar al stored procedure
        $this->db->query("CALL sp_generar_codigo_barras(?, @codigo)", [$producto_id]);
        $result = $this->db->query("SELECT @codigo as codigo")->row();
        
        return $result ? $result->codigo : null;
    }
    
    /**
     * Crea un lote de producción
     */
    public function crear_lote($data) {
        $this->db->insert('lotes_produccion', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Obtiene lotes de una orden de producción
     */
    public function get_lotes_orden($orden_id) {
        $this->db->select('
            lp.*,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo
        ');
        $this->db->from('lotes_produccion lp');
        $this->db->join('productos p', 'p.id = lp.producto_id');
        $this->db->where('lp.orden_produccion_id', $orden_id);
        $this->db->order_by('lp.fecha_produccion', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene estadísticas para el dashboard
     */
    public function get_estadisticas() {
        $stats = [];
        
        // Órdenes confirmadas
        $this->db->where('estatus', 'Confirmada');
        $stats['confirmadas'] = $this->db->count_all_results('ordenes_venta');
        
        // Órdenes en proceso
        $this->db->where('estatus', 'En Proceso');
        $stats['en_proceso'] = $this->db->count_all_results('ordenes_venta');
        
        // Órdenes completadas hoy
        $this->db->where('estatus', 'Completada');
        $this->db->where('DATE(fecha_creacion)', date('Y-m-d'));
        $stats['completadas_hoy'] = $this->db->count_all_results('ordenes_venta');
        
        return $stats;
    }

    // =====================================================
    // VERIFICACIÓN DE STOCK PARA PRODUCCIÓN
    // =====================================================

    /**
     * Obtiene los insumos requeridos para producir todos los productos de una orden/obra,
     * comparando con el stock actual de cada insumo.
     *
     * @param int    $orden_id  ID de la orden de venta u obra
     * @param string $tipo      'venta' | 'obra'
     * @return array ['stock_suficiente' => bool, 'insumos' => [...], 'sin_formulacion' => bool]
     */
    public function get_insumos_requeridos_para_orden($orden_id, $tipo = 'venta') {
        $this->load->model('Produccion/ProductosModel');
        $verificacion = $this->ProductosModel->verificar_disponibilidad_insumos_para_orden($orden_id, $tipo);

        if (!empty($verificacion['sin_productos_fabricados'])) {
            return [
                'stock_suficiente' => true,
                'insumos'          => [],
                'sin_formulacion'  => $verificacion['sin_formulacion'],
                'sin_productos'    => true,
                'bloqueada'        => false,
            ];
        }

        $resultado_insumos = [];

        foreach (array_merge($verificacion['faltantes'], $verificacion['suficientes']) as $item) {
            $cantidad_req = $item['cantidad_en_unidad_insumo'] ?? $item['cantidad_requerida'];
            $faltante = (float) ($item['cantidad_faltante'] ?? 0);
            $disponible = $faltante <= 0;

            $resultado_insumos[] = [
                'insumo_id'               => $item['insumo_id'],
                'insumo_nombre'           => $item['insumo_nombre'],
                'insumo_codigo'           => $item['insumo_codigo'],
                'unidad'                  => $item['unidad_insumo'],
                'unidad_medida'           => $item['unidad_insumo'],
                'porcentaje'              => null,
                'cantidad_por_unidad'     => null,
                'cantidad_requerida'      => $cantidad_req,
                'stock_actual'            => $item['stock_disponible'],
                'precio_promedio'         => $item['precio_promedio'] ?? 0,
                'faltante'                => $faltante,
                'disponible'              => $disponible,
                'costo_estimado_faltante' => round($faltante * ($item['precio_promedio'] ?? 0), 2),
            ];
        }

        usort($resultado_insumos, fn($a, $b) => $a['disponible'] - $b['disponible']);

        return [
            'stock_suficiente' => $verificacion['ok'],
            'insumos'          => $resultado_insumos,
            'sin_formulacion'  => $verificacion['sin_formulacion'],
            'sin_productos'    => false,
            'bloqueada'        => !$verificacion['ok'] || !empty($verificacion['revision_manual']),
            'revision_manual'  => $verificacion['revision_manual'],
        ];
    }

    /**
     * Verificación rápida: ¿hay stock suficiente para producir la orden?
     *
     * @param int    $orden_id
     * @param string $tipo  'venta' | 'obra'
     * @return bool
     */
    public function tiene_stock_suficiente($orden_id, $tipo = 'venta') {
        $resultado = $this->get_insumos_requeridos_para_orden($orden_id, $tipo);
        return $resultado['stock_suficiente'];
    }

    /**
     * Obtiene el estado de stock para múltiples órdenes (optimizado para el dashboard)
     *
     * @param array $ordenes  [['id' => X, 'tipo' => 'venta|obra'], ...]
     * @return array  [ 'tipo_id' => 'ok|faltante|sin_formulacion', ... ]
     */
    public function get_estado_stock_multiple($ordenes) {
        $resultado = [];
        foreach ($ordenes as $orden) {
            $id   = $orden['id'];
            $tipo = $orden['tipo'];
            $key  = $tipo . '_' . $id;

            $verificacion = $this->get_insumos_requeridos_para_orden($id, $tipo);
            $origen_tipo = ($tipo === 'obra') ? 'obra' : 'venta';

            if ($verificacion['sin_formulacion'] && empty($verificacion['insumos'])) {
                $resultado[$key] = 'sin_formulacion';
            } elseif ($verificacion['stock_suficiente'] && empty($verificacion['revision_manual'])) {
                $resultado[$key] = 'ok';
            } elseif ($this->_tiene_preordenes_pendientes_origen($origen_tipo, $id)) {
                $resultado[$key] = 'bloqueada_preorden';
            } elseif (!$verificacion['stock_suficiente'] || !empty($verificacion['revision_manual'])) {
                $resultado[$key] = 'faltante';
            } else {
                $resultado[$key] = 'ok';
            }
        }
        return $resultado;
    }

    /**
     * Obtiene lotes para DataTables global
     */
    public function get_lotes_global_datatables($filtros = []) {
        $this->db->select('lp.*, p.nombre as producto_nombre, p.codigo as producto_codigo, f.nombre_version as formulacion_nombre, 
                           ov.folio as ov_folio, o.folio as obra_folio');
        $this->db->from('lotes_produccion lp');
        $this->db->join('productos p', 'p.id = lp.producto_id', 'left');
        $this->db->join('formulaciones f', 'f.id = lp.formulacion_id', 'left');
        $this->db->join('ordenes_venta ov', 'ov.id = lp.orden_venta_id', 'left');
        $this->db->join('obras o', 'o.id = lp.obra_id', 'left');

        if (!empty($filtros['search'])) {
            $this->db->group_start();
            $this->db->like('lp.codigo_barras', $filtros['search']);
            $this->db->or_like('p.nombre', $filtros['search']);
            $this->db->or_like('p.codigo', $filtros['search']);
            $this->db->or_like('ov.folio', $filtros['search']);
            $this->db->or_like('o.folio', $filtros['search']);
            $this->db->group_end();
        }

        if (!empty($_POST['order'])) {
            $columns = ['lp.id', 'lp.codigo_barras', 'p.nombre', 'lp.cantidad', 'lp.fecha_produccion', 'lp.estatus'];
            $this->db->order_by($columns[(int)$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else {
            $this->db->order_by('lp.fecha_produccion', 'DESC');
        }

        if (isset($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }

        return $this->db->get()->result();
    }

    public function count_filtered_lotes($filtros = []) {
        $this->db->from('lotes_produccion lp');
        $this->db->join('productos p', 'p.id = lp.producto_id', 'left');
        
        if (!empty($filtros['search'])) {
            $this->db->group_start();
            $this->db->like('lp.codigo_barras', $filtros['search']);
            $this->db->or_like('p.nombre', $filtros['search']);
            $this->db->or_like('p.codigo', $filtros['search']);
            $this->db->group_end();
        }
        
        return $this->db->count_all_results();
    }

    public function count_all_lotes() {
        return $this->db->count_all('lotes_produccion');
    }

    /**
     * Procesa la descarga de insumos e ingreso de productos terminados en el inventario.
     * 
     * @param int    $id   ID de la orden (venta u obra)
     * @param string $tipo 'venta' | 'obra'
     * @param array  $lotes Datos de los lotes generados
     * @return array Resultado del proceso
     */
    public function procesar_inventario_por_produccion($id, $tipo, $lotes) {
        $this->db->trans_start();

        // P6: los insumos se descuentan SOLO vía confirmar_pesaje_y_descontar().
        // Aquí únicamente se registra la ENTRADA de producto terminado (lotes).

        foreach ($lotes as $lote) {
            // Obtener stock actual del producto
            $this->db->select('stock_actual');
            $this->db->where('id', $lote['producto_id']);
            $producto = $this->db->get('productos')->row();
            $stock_anterior = $producto ? $producto->stock_actual : 0;

            // Insertar movimiento de producto (Entrada por producción)
            // El trigger 'tr_actualizar_stock_producto' actualizará el stock_actual
            $mov_prod = [
                'producto_id'     => $lote['producto_id'],
                'tipo_movimiento' => 'Produccion',
                'cantidad'        => $lote['cantidad'],
                'stock_anterior'  => $stock_anterior,
                'stock_nuevo'     => $stock_anterior + $lote['cantidad'],
                'motivo'          => 'Folio Lote: ' . $lote['codigo_barras'],
                'fecha_movimiento'=> date('Y-m-d H:i:s'),
                'usuario_id'      => $this->session->userdata('user_id') ?: 1
            ];
            
            // Vincular con OV u Obra según corresponda
            if ($tipo == 'venta') {
                $mov_prod['venta_id'] = $id;
            } else {
                $mov_prod['motivo'] .= ' (Obra ID: ' . $id . ')';
            }

            $this->db->insert('movimientos_productos', $mov_prod);
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // =====================================================
    // P6 — PESAJE REAL Y DESCUENTO DE MATERIA PRIMA
    // =====================================================

    /** Referencia única de movimientos de pesaje por orden/obra/OP */
    public function referencia_pesaje($tipo, $id) {
        $id = (int) $id;
        if ($tipo === 'op') {
            return 'PESAJE-op-' . $id;
        }
        $tipo = ($tipo === 'obra') ? 'obra' : 'venta';
        return 'PESAJE-' . $tipo . '-' . $id;
    }

    /**
     * Indica si ya se registró el consumo de insumos por pesaje para esta orden/obra.
     */
    public function insumos_ya_consumidos($tipo, $id) {
        $ref = $this->referencia_pesaje($tipo, $id);
        $this->db->where('tipo_movimiento', 'Salida');

        if ($this->db->field_exists('referencia', 'movimientos_inventario')) {
            $this->db->where('referencia', $ref);
        } else {
            if ($tipo === 'op') {
                $this->db->like('motivo', 'Pesaje producción OP #' . (int) $id, 'after');
            } else {
                $this->db->like('motivo', 'Pesaje producción ' . strtoupper($tipo) . ' #' . (int) $id, 'after');
            }
        }

        return $this->db->count_all_results('movimientos_inventario') > 0;
    }

    /**
     * Obtiene movimientos de pesaje ya registrados para mostrar restante consumido.
     */
    public function get_movimientos_pesaje_orden($tipo, $id) {
        $ref = $this->referencia_pesaje($tipo, $id);
        $this->db->select('movimientos_inventario.*, insumos.codigo AS insumo_codigo, insumos.nombre_tecnico AS insumo_nombre, insumos.unidad_medida');
        $this->db->from('movimientos_inventario');
        $this->db->join('insumos', 'insumos.id = movimientos_inventario.insumo_id', 'left');
        $this->db->where('movimientos_inventario.tipo_movimiento', 'Salida');

        if ($this->db->field_exists('referencia', 'movimientos_inventario')) {
            $this->db->where('movimientos_inventario.referencia', $ref);
        } else {
            $this->db->like('movimientos_inventario.motivo', 'Pesaje producción ' . strtoupper($tipo) . ' #' . (int) $id, 'after');
        }

        $this->db->order_by('movimientos_inventario.fecha_movimiento', 'ASC');
        return $this->db->get()->result();
    }

    /**
     * Estado de pesaje + insumos teóricos (motor P5.1) para la UI touch.
     */
    public function get_estado_pesaje_orden($id, $tipo) {
        $this->load->helper('permissions');
        $tipo_db = ($tipo === 'obra') ? 'obra' : 'venta';

        $verificacion = $this->get_insumos_requeridos_para_orden($id, $tipo_db);
        $consumido = $this->insumos_ya_consumidos($tipo_db, $id);
        $movimientos = $consumido ? $this->get_movimientos_pesaje_orden($tipo_db, $id) : [];

        $mapa_consumido = [];
        foreach ($movimientos as $mov) {
            $mapa_consumido[$mov->insumo_id] = (float) $mov->cantidad;
        }

        $insumos_ui = [];
        foreach ($verificacion['insumos'] as $ins) {
            $teorico = (float) ($ins['cantidad_requerida'] ?? 0);
            $pesado = $mapa_consumido[$ins['insumo_id']] ?? null;
            $stock_actual = (float) ($ins['stock_actual'] ?? 0);

            $insumos_ui[] = array_merge($ins, [
                'cantidad_teorica'  => $teorico,
                'cantidad_pesada'   => $pesado,
                'stock_restante'    => $consumido ? $stock_actual : max(0, $stock_actual - $teorico),
                'merma_max_pct'     => 20,
                'merma_max_cantidad'=> round($teorico * 1.20, 6),
            ]);
        }

        return [
            'consumido'          => $consumido,
            'movimientos'        => $movimientos,
            'insumos'            => $insumos_ui,
            'stock_suficiente'   => $verificacion['stock_suficiente'],
            'bloqueada'          => $consumido ? false : ($verificacion['bloqueada'] ?? !$verificacion['stock_suficiente']),
            'revision_manual'    => $verificacion['revision_manual'] ?? [],
            'sin_formulacion'    => $verificacion['sin_formulacion'],
            'puede_forzar'       => function_exists('tiene_permiso') && tiene_permiso('produccion_ordenes'),
            'mercancia_lista'    => $consumido || ($verificacion['stock_suficiente'] && empty($verificacion['revision_manual'])),
        ];
    }

    /**
     * Confirma pesaje real, valida merma y descuenta insumos (transaccional).
     *
     * @param int    $id
     * @param string $tipo 'venta'|'obra'
     * @param array  $pesajes [{insumo_id, cantidad_real}]
     * @param int    $usuario_id
     */
    public function confirmar_pesaje_y_descontar($id, $tipo, array $pesajes, $usuario_id) {
        $this->load->helper('permissions');
        $tipo_db = ($tipo === 'obra') ? 'obra' : 'venta';

        if ($this->insumos_ya_consumidos($tipo_db, $id)) {
            return ['success' => false, 'message' => 'Los insumos de esta orden ya fueron descontados por pesaje anterior.'];
        }

        if (empty($pesajes)) {
            return ['success' => false, 'message' => 'No se recibieron cantidades de pesaje.'];
        }

        $estado = $this->get_estado_pesaje_orden($id, $tipo_db);
        $teoricos = [];
        foreach ($estado['insumos'] as $ins) {
            $teoricos[(int) $ins['insumo_id']] = $ins;
        }

        $pesados_ids = [];
        foreach ($pesajes as $item) {
            $iid = (int) ($item['insumo_id'] ?? 0);
            if ($iid > 0) {
                $pesados_ids[$iid] = true;
            }
        }
        foreach ($teoricos as $insumo_id => $teo) {
            if (empty($teo['disponible'])) {
                continue;
            }
            if (!isset($pesados_ids[$insumo_id])) {
                return [
                    'success' => false,
                    'message' => 'Debe capturar el pesaje de todos los insumos: ' . ($teo['insumo_nombre'] ?? 'insumo'),
                ];
            }
        }

        $ref = $this->referencia_pesaje($tipo_db, $id);
        $merma_max_pct = 0.20;
        $puede_exceder = function_exists('tiene_permiso') && tiene_permiso('produccion_ordenes');

        $this->db->trans_start();
        $detalles = [];
        $bajo_minimo = [];

        foreach ($pesajes as $item) {
            $insumo_id = (int) ($item['insumo_id'] ?? 0);
            $cantidad_real = isset($item['cantidad_real']) ? (float) $item['cantidad_real'] : 0;

            if (!$insumo_id || $cantidad_real <= 0) {
                continue;
            }

            if (!isset($teoricos[$insumo_id])) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Insumo ID ' . $insumo_id . ' no pertenece a esta orden.'];
            }

            $teo = $teoricos[$insumo_id];
            $cantidad_teorica = (float) ($teo['cantidad_teorica'] ?? 0);
            $max_permitido = $cantidad_teorica * (1 + $merma_max_pct);

            if ($cantidad_real > $max_permitido && !$puede_exceder) {
                $this->db->trans_rollback();
                return [
                    'success' => false,
                    'message' => 'El pesaje de ' . ($teo['insumo_nombre'] ?? 'insumo') .
                        ' excede la merma permitida (máx. ' . round($max_permitido, 3) . ' ' . ($teo['unidad'] ?? '') . ').',
                ];
            }

            $insumo = $this->db->select('id, codigo, nombre_tecnico, stock_actual, stock_minimo, unidad_medida, precio_promedio')
                ->where('id', $insumo_id)
                ->get('insumos')
                ->row();

            if (!$insumo) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Insumo ID ' . $insumo_id . ' no encontrado.'];
            }

            $stock_anterior = (float) $insumo->stock_actual;
            if ($cantidad_real > $stock_anterior) {
                $this->db->trans_rollback();
                return [
                    'success' => false,
                    'message' => 'Stock insuficiente de ' . $insumo->nombre_tecnico .
                        ' (disponible: ' . round($stock_anterior, 3) . ' ' . $insumo->unidad_medida . ').',
                ];
            }

            $stock_nuevo = round($stock_anterior - $cantidad_real, 6);
            $mov = [
                'insumo_id'       => $insumo_id,
                'tipo_movimiento' => 'Salida',
                'cantidad'        => $cantidad_real,
                'stock_anterior'  => $stock_anterior,
                'stock_nuevo'     => $stock_nuevo,
                'motivo'          => 'Pesaje producción ' . strtoupper($tipo_db) . ' #' . (int) $id,
                'fecha_movimiento'=> date('Y-m-d H:i:s'),
                'usuario_id'      => (int) $usuario_id,
            ];

            if ($this->db->field_exists('referencia', 'movimientos_inventario')) {
                $mov['referencia'] = $ref;
            }

            if ($this->db->field_exists('costo_unitario', 'movimientos_inventario')) {
                $mov['costo_unitario'] = (float) ($insumo->precio_promedio ?? 0);
                $mov['costo_total'] = round($cantidad_real * ($insumo->precio_promedio ?? 0), 2);
            }

            $this->db->insert('movimientos_inventario', $mov);

            $detalles[] = [
                'insumo_id'        => $insumo_id,
                'insumo_codigo'    => $insumo->codigo,
                'insumo_nombre'    => $insumo->nombre_tecnico,
                'unidad'           => $insumo->unidad_medida,
                'cantidad_teorica' => $cantidad_teorica,
                'cantidad_real'    => $cantidad_real,
                'stock_anterior'   => $stock_anterior,
                'stock_restante'   => $stock_nuevo,
                'merma_pct'        => $cantidad_teorica > 0
                    ? round((($cantidad_real - $cantidad_teorica) / $cantidad_teorica) * 100, 2)
                    : 0,
            ];

            if ($stock_nuevo < (float) $insumo->stock_minimo) {
                $bajo_minimo[] = [
                    'insumo_id'         => $insumo_id,
                    'cantidad_faltante' => max(0, round((float) $insumo->stock_minimo - $stock_nuevo, 6)),
                    'unidad_insumo'     => $insumo->unidad_medida,
                    'insumo_nombre'     => $insumo->nombre_tecnico,
                ];
            }
        }

        if (empty($detalles)) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'No hay líneas de pesaje válidas para descontar.'];
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['success' => false, 'message' => 'Error de base de datos al registrar el pesaje.'];
        }

        $preordenes = null;
        if (!empty($bajo_minimo) && function_exists('tiene_permiso') && tiene_permiso('produccion_preordenes')) {
            $this->load->model('Compras/PreordenesModel');
            $faltantes_canonicos = [];
            foreach ($bajo_minimo as $b) {
                if ($b['cantidad_faltante'] > 0) {
                    $faltantes_canonicos[] = [
                        'insumo_id'         => $b['insumo_id'],
                        'cantidad_faltante' => $b['cantidad_faltante'],
                        'unidad_insumo'     => $b['unidad_insumo'],
                    ];
                }
            }
            if (!empty($faltantes_canonicos)) {
                $preordenes = $this->PreordenesModel->crear_preordenes_desde_faltantes(
                    $faltantes_canonicos,
                    'produccion',
                    (int) $id,
                    (int) $usuario_id,
                    'Reposición post-pesaje ' . strtoupper($tipo_db) . ' #' . (int) $id
                );
            }
        }

        return [
            'success'  => true,
            'message'  => 'Pesaje confirmado. Se descontaron ' . count($detalles) . ' insumo(s).',
            'detalles' => $detalles,
            'preordenes' => $preordenes,
            'bajo_minimo' => $bajo_minimo,
        ];
    }

    /**
     * Valida si la orden puede marcarse Completada.
     */
    public function puede_completar_produccion($id, $tipo, $forzar = false) {
        $tipo_db = ($tipo === 'obra') ? 'obra' : 'venta';

        // 5) Forzar solo con permiso + flag explícito (parseado en el controlador)
        if ($forzar) {
            $this->load->helper('permissions');
            if (!function_exists('tiene_permiso') || !tiene_permiso('produccion_ordenes')) {
                return ['ok' => false, 'message' => 'No tiene permiso para completar sin verificación de insumos.'];
            }
            return ['ok' => true, 'message' => 'Completado con autorización de producción.'];
        }

        // 1) Pesaje ya confirmado — no revalidar stock (ya fue descontado)
        if ($this->insumos_ya_consumidos($tipo_db, $id)) {
            return ['ok' => true, 'message' => 'Pesaje confirmado. Lista para completar.'];
        }

        $verificacion = $this->get_insumos_requeridos_para_orden($id, $tipo_db);

        // 2) Sin insumos que requieran pesaje
        if (!empty($verificacion['sin_productos'])) {
            return ['ok' => true, 'message' => 'Sin productos fabricados que requieran insumos.'];
        }
        if (empty($verificacion['insumos'])) {
            return ['ok' => true, 'message' => 'Sin insumos calculables para esta orden.'];
        }

        // 3) Faltantes o revisión manual
        if (!empty($verificacion['revision_manual'])) {
            return [
                'ok' => false,
                'message' => 'No se puede completar: hay insumos con unidades ambiguas que requieren revisión manual.',
                'bloqueada' => true,
            ];
        }
        if (!$verificacion['stock_suficiente']) {
            return [
                'ok' => false,
                'message' => 'No se puede completar: faltan insumos. Genere pre-órdenes o confirme recepción de materia prima.',
                'bloqueada' => true,
            ];
        }

        // 4) Stock OK pero sin pesaje confirmado
        return [
            'ok' => false,
            'message' => 'Debe confirmar el pesaje y descontar los insumos antes de marcar como Completada.',
            'bloqueada' => true,
        ];
    }

    /**
     * Descuenta insumos registrando movimientos con referencia explícita (OP legacy u otros).
     */
    private function _descontar_insumos_por_referencia($referencia, array $pesajes, $usuario_id, $motivo_suffix = '') {
        $this->db->trans_start();
        $detalles = [];

        foreach ($pesajes as $item) {
            $insumo_id = (int) ($item['insumo_id'] ?? 0);
            $cantidad_real = isset($item['cantidad_real']) ? (float) $item['cantidad_real'] : 0;
            if (!$insumo_id || $cantidad_real <= 0) {
                continue;
            }

            $insumo = $this->db->select('id, codigo, nombre_tecnico, stock_actual, precio_promedio, unidad_medida')
                ->where('id', $insumo_id)
                ->get('insumos')
                ->row();

            if (!$insumo) {
                $this->db->trans_rollback();
                return ['success' => false, 'message' => 'Insumo ID ' . $insumo_id . ' no encontrado.'];
            }

            $stock_anterior = (float) $insumo->stock_actual;
            if ($cantidad_real > $stock_anterior) {
                $this->db->trans_rollback();
                return [
                    'success' => false,
                    'message' => 'Stock insuficiente de ' . $insumo->nombre_tecnico .
                        ' (disponible: ' . round($stock_anterior, 3) . ' ' . $insumo->unidad_medida . ').',
                ];
            }

            $stock_nuevo = round($stock_anterior - $cantidad_real, 6);
            $mov = [
                'insumo_id'       => $insumo_id,
                'tipo_movimiento' => 'Salida',
                'cantidad'        => $cantidad_real,
                'stock_anterior'  => $stock_anterior,
                'stock_nuevo'     => $stock_nuevo,
                'motivo'          => 'Pesaje producción ' . $motivo_suffix,
                'fecha_movimiento'=> date('Y-m-d H:i:s'),
                'usuario_id'      => (int) $usuario_id,
            ];

            if ($this->db->field_exists('referencia', 'movimientos_inventario')) {
                $mov['referencia'] = $referencia;
            }

            if ($this->db->field_exists('costo_unitario', 'movimientos_inventario')) {
                $mov['costo_unitario'] = (float) ($insumo->precio_promedio ?? 0);
                $mov['costo_total'] = round($cantidad_real * ($insumo->precio_promedio ?? 0), 2);
            }

            $this->db->insert('movimientos_inventario', $mov);

            $detalles[] = [
                'insumo_id'      => $insumo_id,
                'insumo_codigo'  => $insumo->codigo,
                'insumo_nombre'  => $insumo->nombre_tecnico,
                'cantidad_real'  => $cantidad_real,
                'stock_restante' => $stock_nuevo,
                'unidad'         => $insumo->unidad_medida,
            ];
        }

        if (empty($detalles)) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'No hay líneas válidas para descontar.'];
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['success' => false, 'message' => 'Error de base de datos al registrar movimientos.'];
        }

        return [
            'success'  => true,
            'message'  => 'Se descontaron ' . count($detalles) . ' insumo(s).',
            'detalles' => $detalles,
        ];
    }

    /**
     * Indica si hay pre-órdenes Pendiente ligadas a un origen venta/obra.
     */
    private function _tiene_preordenes_pendientes_origen($origen_tipo, $origen_id) {
        if (!$this->db->table_exists('preordenes')) {
            return false;
        }

        return $this->db->where('origen_tipo', $origen_tipo)
            ->where('origen_id', (int) $origen_id)
            ->where('estatus', 'Pendiente')
            ->count_all_results('preordenes') > 0;
    }

    // =====================================================
    // P7 — ETIQUETAS DE LOTE Y CONSULTA POR CÓDIGO DE BARRAS
    // =====================================================

    /**
     * Datos completos de un lote para etiqueta o consulta por escaneo.
     */
    public function get_lote_etiqueta_datos($lote_id) {
        $row = $this->_fetch_lote_etiqueta_row(['lp.id' => (int) $lote_id]);
        return $row ? $this->_normalizar_datos_etiqueta_lote($row) : null;
    }

    /**
     * Consulta un lote por código de barras (AJAX almacén / producción).
     */
    public function consultar_lote_por_codigo_barras($codigo_barras) {
        $codigo_barras = trim((string) $codigo_barras);
        if ($codigo_barras === '') {
            return ['success' => false, 'message' => 'Código de barras requerido'];
        }

        $row = $this->_fetch_lote_etiqueta_row(['lp.codigo_barras' => $codigo_barras]);
        if (!$row) {
            return ['success' => false, 'message' => 'Lote no encontrado: ' . $codigo_barras];
        }

        return [
            'success' => true,
            'lote'    => $this->_normalizar_datos_etiqueta_lote($row),
        ];
    }

    /**
     * Query base para lote + producto + formulación + origen venta/obra.
     */
    private function _fetch_lote_etiqueta_row(array $where) {
        $select = 'lp.*, p.nombre AS producto_nombre, p.codigo AS producto_codigo, p.unidad_venta,
            f.nombre_version AS formulacion_nombre, f.version AS formulacion_version';

        if ($this->db->field_exists('orden_venta_id', 'lotes_produccion')) {
            $select .= ', ov.folio AS orden_venta_folio, ov.id AS ov_id';
        }
        if ($this->db->field_exists('obra_id', 'lotes_produccion')) {
            $select .= ', o.folio AS obra_folio, o.nombre AS obra_nombre, o.id AS obra_id_col';
        }

        $this->db->select($select, false);
        $this->db->from('lotes_produccion lp');
        $this->db->join('productos p', 'p.id = lp.producto_id', 'left');
        $this->db->join('formulaciones f', 'f.id = lp.formulacion_id', 'left');

        if ($this->db->field_exists('orden_venta_id', 'lotes_produccion')) {
            $this->db->join('ordenes_venta ov', 'ov.id = lp.orden_venta_id', 'left');
        }
        if ($this->db->field_exists('obra_id', 'lotes_produccion')) {
            $this->db->join('obras o', 'o.id = lp.obra_id', 'left');
        }

        foreach ($where as $col => $val) {
            $this->db->where($col, $val);
        }

        return $this->db->get()->row();
    }

    /**
     * Normaliza fila de lote a estructura estándar para etiqueta y scan.
     */
    private function _normalizar_datos_etiqueta_lote($row) {
        $cantidad = (float) ($row->cantidad ?? 0);
        $unidad   = trim((string) ($row->unidad ?: 'kg'));

        $orden_venta_folio = $row->orden_venta_folio ?? null;
        $obra_folio        = $row->obra_folio ?? null;
        $origen_tipo       = null;
        $origen_folio      = null;
        $origen_etiqueta   = '—';

        if (!empty($orden_venta_folio)) {
            $origen_tipo  = 'venta';
            $origen_folio = $orden_venta_folio;
            $origen_etiqueta = 'OV ' . $orden_venta_folio;
        } elseif (!empty($obra_folio)) {
            $origen_tipo  = 'obra';
            $origen_folio = $obra_folio;
            $origen_etiqueta = 'Obra ' . $obra_folio;
        } elseif (!empty($row->observaciones) && preg_match('/obra_id:(\d+)/', $row->observaciones, $m)) {
            $obra = $this->db->select('folio, nombre')->where('id', (int) $m[1])->get('obras')->row();
            if ($obra) {
                $origen_tipo     = 'obra';
                $origen_folio    = $obra->folio;
                $obra_folio      = $obra->folio;
                $origen_etiqueta = 'Obra ' . $obra->folio;
            }
        }

        $unidad_norm = strtolower($unidad);
        $es_contenedor = in_array($unidad_norm, ['pza', 'pz', 'pieza', 'cubeta', 'caja', 'bote', 'cubetas'], true);
        $cubeta_display = number_format($cantidad, 2) . ' ' . $unidad;
        if ($es_contenedor && $cantidad == 1) {
            $cubeta_display = '1 ' . ($unidad_norm === 'pza' || $unidad_norm === 'pz' ? 'Cubeta' : ucfirst($unidad));
        }

        $fecha_raw = $row->fecha_produccion ?? null;
        $fecha_display = $fecha_raw ? date('d/m/Y H:i', strtotime($fecha_raw)) : '—';

        return [
            'id'                  => (int) $row->id,
            'codigo_barras'       => (string) $row->codigo_barras,
            'producto_nombre'     => (string) ($row->producto_nombre ?? ''),
            'producto_codigo'     => (string) ($row->producto_codigo ?? ''),
            'formulacion_nombre'  => (string) ($row->formulacion_nombre ?? ''),
            'formulacion_version' => (string) ($row->formulacion_version ?? ''),
            'cantidad'            => $cantidad,
            'unidad'              => $unidad,
            'cantidad_display'    => number_format($cantidad, 2) . ' ' . $unidad,
            'cubeta'              => $cubeta_display,
            'fecha_produccion'    => $fecha_raw,
            'fecha_display'       => $fecha_display,
            'origen_tipo'         => $origen_tipo,
            'origen_folio'        => $origen_folio,
            'origen_etiqueta'     => $origen_etiqueta,
            'orden_venta_folio'   => $orden_venta_folio,
            'obra_folio'          => $obra_folio,
            'estatus'             => (string) ($row->estatus ?? ''),
            'observaciones'       => (string) ($row->observaciones ?? ''),
        ];
    }
}
