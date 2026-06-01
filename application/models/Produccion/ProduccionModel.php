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
        
        // Si pasa a "Completada", primero descontar stock de insumos
        if($nuevo_estatus == 'Completada') {
            // Intentar descontar stock de materia prima
            $resultado_descuento = $this->descontar_stock_produccion($orden_id);
            
            // Si falla el descuento de stock, NO completar la orden
            if(!$resultado_descuento['success']) {
                return $resultado_descuento; // Retornar el error tal cual
            }
            
            // Si el descuento fue exitoso, registrar fecha_completado
            $data['fecha_completado'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where('id', $orden_id);
        $actualizado = $this->db->update('ordenes_produccion', $data);
        
        // Si la actualización fue exitosa y era una completación, incluir detalles del descuento
        if($actualizado && $nuevo_estatus == 'Completada' && isset($resultado_descuento)) {
            return [
                'success' => true,
                'message' => 'Orden completada y stock descontado correctamente',
                'detalles_descuento' => $resultado_descuento['detalles']
            ];
        }
        
        return $actualizado;
    }
    
    /**
     * Descuenta el stock de insumos al completar una orden de producción
     * Usa transacciones para garantizar atomicidad (todo o nada)
     * 
     * @param int $orden_id ID de la orden de producción
     * @return array ['success' => bool, 'message' => string, 'detalles' => array]
     */
    public function descontar_stock_produccion($orden_id) {
        // Iniciar transacción
        $this->db->trans_start();
        
        try {
            // 1. Obtener detalles de la orden de producción
            $this->db->select('op.*, p.nombre as producto_nombre, p.codigo as producto_codigo');
            $this->db->from('ordenes_produccion op');
            $this->db->join('productos p', 'p.id = op.producto_id');
            $this->db->where('op.id', $orden_id);
            $orden = $this->db->get()->row();
            
            if(!$orden) {
                $this->db->trans_rollback();
                return [
                    'success' => false,
                    'message' => 'Orden de producción no encontrada',
                    'detalles' => []
                ];
            }
            
            // 2. Obtener la formulación activa del producto
            $this->db->select('id');
            $this->db->where('producto_id', $orden->producto_id);
            $this->db->where('es_activa', 1);
            $this->db->order_by('version', 'DESC');
            $this->db->limit(1);
            $formulacion = $this->db->get('formulaciones')->row();
            
            if(!$formulacion) {
                $this->db->trans_rollback();
                return [
                    'success' => false,
                    'message' => 'No existe formulación activa para el producto: ' . $orden->producto_nombre,
                    'detalles' => []
                ];
            }
            
            // 3. Obtener componentes (insumos) de la formulación
            $componentes = $this->get_componentes_formulacion($formulacion->id, $orden->cantidad);
            
            if(empty($componentes)) {
                $this->db->trans_rollback();
                return [
                    'success' => false,
                    'message' => 'La formulación no tiene insumos definidos',
                    'detalles' => []
                ];
            }
            
            $detalles_descuento = [];
            $insumos_insuficientes = [];
            
            // 4. Validar stock disponible para TODOS los insumos primero
            foreach($componentes as $componente) {
                $this->db->select('id, codigo, nombre_tecnico, stock_actual, unidad_medida');
                $this->db->where('id', $componente->insumo_id);
                $insumo = $this->db->get('insumos')->row();
                
                if(!$insumo) {
                    $this->db->trans_rollback();
                    return [
                        'success' => false,
                        'message' => 'Insumo ID ' . $componente->insumo_id . ' no encontrado',
                        'detalles' => []
                    ];
                }
                
                $cantidad_necesaria = $componente->cantidad_necesaria;
                $stock_disponible = $insumo->stock_actual;
                
                // Verificar si hay suficiente stock
                if($stock_disponible < $cantidad_necesaria) {
                    $insumos_insuficientes[] = [
                        'codigo' => $insumo->codigo,
                        'nombre' => $insumo->nombre_tecnico,
                        'necesario' => number_format($cantidad_necesaria, 2),
                        'disponible' => number_format($stock_disponible, 2),
                        'faltante' => number_format($cantidad_necesaria - $stock_disponible, 2),
                        'unidad' => $insumo->unidad_medida
                    ];
                }
                
                $detalles_descuento[] = [
                    'insumo_id' => $insumo->id,
                    'codigo' => $insumo->codigo,
                    'nombre' => $insumo->nombre_tecnico,
                    'cantidad_necesaria' => $cantidad_necesaria,
                    'stock_anterior' => $stock_disponible,
                    'stock_nuevo' => $stock_disponible - $cantidad_necesaria,
                    'unidad' => $insumo->unidad_medida
                ];
            }
            
            // 5. Si hay insumos insuficientes, abortar transacción
            if(!empty($insumos_insuficientes)) {
                $this->db->trans_rollback();
                
                $mensaje = "Stock insuficiente para completar la orden:\n\n";
                foreach($insumos_insuficientes as $ins) {
                    $mensaje .= "• {$ins['codigo']} - {$ins['nombre']}: ";
                    $mensaje .= "Necesario: {$ins['necesario']} {$ins['unidad']}, ";
                    $mensaje .= "Disponible: {$ins['disponible']} {$ins['unidad']}, ";
                    $mensaje .= "Faltante: {$ins['faltante']} {$ins['unidad']}\n";
                }
                
                return [
                    'success' => false,
                    'message' => $mensaje,
                    'detalles' => $insumos_insuficientes,
                    'tipo_error' => 'stock_insuficiente'
                ];
            }
            
            // 6. Descontar stock de TODOS los insumos (atomicidad garantizada)
            foreach($detalles_descuento as $detalle) {
                // Actualizar stock del insumo
                $this->db->where('id', $detalle['insumo_id']);
                $this->db->set('stock_actual', 'stock_actual - ' . $detalle['cantidad_necesaria'], FALSE);
                $this->db->update('insumos');
                
                // TODO: Registrar movimiento en tabla de auditoría (cuando se cree)
                // Por ahora, el registro queda implícito en la orden de producción
            }
            
            // 7. Commit de la transacción
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                return [
                    'success' => false,
                    'message' => 'Error al descontar stock. La transacción fue revertida.',
                    'detalles' => []
                ];
            }
            
            return [
                'success' => true,
                'message' => 'Stock descontado correctamente para ' . count($detalles_descuento) . ' insumo(s)',
                'detalles' => $detalles_descuento
            ];
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return [
                'success' => false,
                'message' => 'Error al descontar stock: ' . $e->getMessage(),
                'detalles' => []
            ];
        }
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

        // 1. Obtener productos de la orden según su tipo
        if ($tipo === 'obra') {
            $this->db->select('
                op.producto_id,
                op.cantidad_ajustada as cantidad,
                p.nombre as producto_nombre,
                p.codigo as producto_codigo,
                op.formulacion_id
            ');
            $this->db->from('obras_productos op');
            $this->db->join('productos p', 'p.id = op.producto_id');
            $this->db->where('op.obra_id', $orden_id);
            $productos = $this->db->get()->result();
        } else {
            $this->db->select('
                dov.producto_id,
                dov.cantidad,
                p.nombre as producto_nombre,
                p.codigo as producto_codigo,
                dov.formulacion_id
            ');
            $this->db->from('detalle_orden_venta dov');
            $this->db->join('productos p', 'p.id = dov.producto_id');
            $this->db->where('dov.orden_venta_id', $orden_id);
            $productos = $this->db->get()->result();
        }

        if (empty($productos)) {
            return ['stock_suficiente' => true, 'insumos' => [], 'sin_formulacion' => false, 'sin_productos' => true];
        }

        // 2. Para cada producto, obtener su formulación activa y escalar insumos
        $mapa_insumos = [];   // insumo_id => datos acumulados
        $sin_formulacion = false;

        foreach ($productos as $prod) {
            // Usar formulacion_id específica de la línea si existe, o la activa del producto
            if (!empty($prod->formulacion_id)) {
                $formulacion_id = $prod->formulacion_id;
            } else {
                $this->db->select('id, cantidad_producida');
                $this->db->where('producto_id', $prod->producto_id);
                $this->db->where('es_activa', 1);
                $this->db->limit(1);
                $form = $this->db->get('formulaciones')->row();
                if (!$form) {
                    $sin_formulacion = true;
                    continue;
                }
                $formulacion_id = $form->id;
            }

            // Obtener datos de la formulación (cantidad base que produce)
            $formulacion = $this->db->select('id, cantidad_producida, unidad_produccion')
                ->where('id', $formulacion_id)->get('formulaciones')->row();
            if (!$formulacion) { $sin_formulacion = true; continue; }

            // Calcular el factor de escala
            // Ej: formulación produce 19L, se piden 10 unidades (cubetas de 19L) → factor = 10
            // Si las unidades son directas (Kg) y la formulación produce en la misma unidad, factor = cantidad / cantidad_producida
            $factor = ($formulacion->cantidad_producida > 0)
                ? ($prod->cantidad / $formulacion->cantidad_producida)
                : $prod->cantidad;

            // Obtener los insumos de la formulación con porcentaje
            $this->db->select('
                df.insumo_id,
                df.cantidad,
                df.unidad,
                df.porcentaje,
                i.nombre_tecnico as insumo_nombre,
                i.codigo as insumo_codigo,
                i.stock_actual,
                i.unidad_medida,
                i.precio_promedio
            ');
            $this->db->from('detalle_formulacion df');
            $this->db->join('insumos i', 'i.id = df.insumo_id');
            $this->db->where('df.formulacion_id', $formulacion_id);
            $this->db->where('df.tipo_componente', 'Insumo');
            $this->db->order_by('df.orden', 'ASC');
            $componentes = $this->db->get()->result();

            foreach ($componentes as $comp) {
                $cantidad_requerida = $comp->cantidad * $factor;

                if (isset($mapa_insumos[$comp->insumo_id])) {
                    // Si el insumo ya apareció en otro producto, acumular
                    $mapa_insumos[$comp->insumo_id]['cantidad_requerida'] += $cantidad_requerida;
                } else {
                    $mapa_insumos[$comp->insumo_id] = [
                        'insumo_id'          => $comp->insumo_id,
                        'insumo_nombre'      => $comp->insumo_nombre,
                        'insumo_codigo'      => $comp->insumo_codigo,
                        'porcentaje'         => $comp->porcentaje,
                        'unidad'             => $comp->unidad,
                        'cantidad_por_unidad'=> $comp->cantidad,            // Kg por cubeta
                        'cantidad_requerida' => $cantidad_requerida,        // Total necesario
                        'stock_actual'       => (float)$comp->stock_actual,
                        'precio_promedio'    => (float)$comp->precio_promedio,
                    ];
                }
            }
        }

        // 3. Determinar disponibilidad para cada insumo acumulado
        $resultado_insumos = [];
        $stock_suficiente = true;

        foreach ($mapa_insumos as $datos) {
            $faltante = max(0, $datos['cantidad_requerida'] - $datos['stock_actual']);
            $disponible = $faltante == 0;
            if (!$disponible) $stock_suficiente = false;

            $resultado_insumos[] = array_merge($datos, [
                'faltante'   => $faltante,
                'disponible' => $disponible,
                'costo_estimado_faltante' => round($faltante * $datos['precio_promedio'], 2),
            ]);
        }

        // Ordenar: primero los faltantes
        usort($resultado_insumos, fn($a, $b) => $a['disponible'] - $b['disponible']);

        return [
            'stock_suficiente' => $stock_suficiente,
            'insumos'          => $resultado_insumos,
            'sin_formulacion'  => $sin_formulacion,
            'sin_productos'    => false,
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
            if ($verificacion['sin_formulacion']) {
                $resultado[$key] = 'sin_formulacion';
            } elseif ($verificacion['stock_suficiente']) {
                $resultado[$key] = 'ok';
            } else {
                $resultado[$key] = 'faltante';
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

        // 1. OBTENER INSUMOS REQUERIDOS (Para descargar stock)
        $resultado_insumos = $this->get_insumos_requeridos_para_orden($id, $tipo);
        
        foreach ($resultado_insumos['insumos'] as $insumo) {
            $cantidad = $insumo['cantidad_requerida'];
            
            // Insertar movimiento de inventario (Salida de insumo)
            // El trigger 'trg_actualizar_stock_movimiento' actualizará el stock_actual
            $mov_insumo = [
                'insumo_id'       => $insumo['insumo_id'],
                'tipo_movimiento' => 'Salida',
                'cantidad'        => $cantidad,
                'stock_anterior'  => $insumo['stock_actual'],
                'stock_nuevo'     => $insumo['stock_actual'] - $cantidad,
                'motivo'          => 'Consumo por producción (' . ($tipo == 'obra' ? 'Obra' : 'Venta') . ' ID: ' . $id . ')',
                'fecha_movimiento'=> date('Y-m-d H:i:s'),
                'usuario_id'      => $this->session->userdata('user_id') ?: 1
            ];
            $this->db->insert('movimientos_inventario', $mov_insumo);
        }

        // 2. REGISTRAR ENTRADA DE PRODUCTOS TERMINADOS (Vía Lotes)
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
}
