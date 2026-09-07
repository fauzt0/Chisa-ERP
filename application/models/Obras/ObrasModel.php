<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ObrasModel extends CI_Model {
    
    /**
     * Genera el siguiente folio de obra (OB-00001, OB-00002, …)
     *
     * FIX BUG-1 (2026-09-07): la versión anterior usaba ORDER BY id DESC LIMIT 1
     * con intval(), lo que devolvía 0 para folios no numéricos (p. ej. OB-TEST-002)
     * y colisionaba con OB-00001 (UNIQUE). Ahora se calcula el MAX numérico sobre
     * TODAS las filas (activas e inactivas) porque el índice UNIQUE incluye ambas.
     * Folios no numéricos castean a 0 y nunca distorsionan el MAX.
     */
    public function generar_folio() {
        $row = $this->db
            ->select('MAX(CAST(SUBSTRING(folio, 4) AS UNSIGNED)) AS max_folio', false)
            ->from('obras')
            ->like('folio', 'OB-', 'after')
            ->get()->row();

        $nuevo_numero = ($row && $row->max_folio !== null) ? (int) $row->max_folio + 1 : 1;

        return 'OB-' . str_pad($nuevo_numero, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Obtiene lista de obras con filtros
     */
    public function get_obras($filtros = []) {
        $this->db->select('
            o.id,
            o.folio,
            o.nombre,
            o.cliente_id,
            c.razon_social as cliente,
            c.nombre_comercial,
            o.direccion,
            o.ciudad,
            o.estado,
            o.estatus,
            o.porcentaje_avance,
            o.fecha_inicio_estimada,
            o.fecha_fin_estimada,
            o.fecha_creacion,
            o.activo
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->where('o.activo', 1);
        
        // Aplicar filtros
        if(!empty($filtros['busqueda'])) {
            $this->db->group_start();
            $this->db->like('o.folio', $filtros['busqueda']);
            $this->db->or_like('o.nombre', $filtros['busqueda']);
            $this->db->or_like('c.razon_social', $filtros['busqueda']);
            $this->db->or_like('o.ciudad', $filtros['busqueda']);
            $this->db->group_end();
        }
        
        if(!empty($filtros['estatus']) && is_array($filtros['estatus'])) {
            $this->db->where_in('o.estatus', $filtros['estatus']);
        }
        
        if(!empty($filtros['cliente_id'])) {
            $this->db->where('o.cliente_id', $filtros['cliente_id']);
        }
        
        $this->db->order_by('o.fecha_creacion', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene detalle completo de una obra
     */
    public function get_obra_detalle($obra_id) {
        $this->db->select('
            o.*,
            c.razon_social as cliente,
            c.nombre_comercial,
            c.telefono as cliente_telefono,
            c.telefono,
            c.email as cliente_email,
            c.email,
            c.rfc as cliente_rfc,
            c.rfc,
            ov.folio as orden_venta_folio,
            ov.estatus as orden_venta_estatus,
            ov.total as orden_venta_total
        ');
        $this->db->from('obras o');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->join('ordenes_venta ov', 'ov.id = o.orden_venta_id', 'left');
        $this->db->where('o.id', $obra_id);
        
        $obra = $this->db->get()->row();
        
        if($obra) {
            // Obtener productos de la obra
            $obra->productos = $this->get_productos_obra($obra_id);
            
            // Obtener archivos
            $obra->archivos = $this->get_archivos_obra($obra_id);
            
            // Obtener comentarios
            $obra->comentarios = $this->get_comentarios_obra($obra_id);
        }
        
        return $obra;
    }
    
    /**
     * Crea una nueva obra
     */
    public function crear_obra($data) {
        // Generar folio automático
        $data['folio'] = $this->generar_folio();
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        $data['activo'] = 1;
        
        // Inicializar campos de pago
        $data['total_pagado'] = 0;
        $data['estatus_pago'] = 'Pendiente';
        
        $this->db->insert('obras', $data);
        $obra_id = $this->db->insert_id();
        
        // Calcular totales iniciales
        if($obra_id) {
            $this->calcular_totales_obra($obra_id);
            
            // Asegurar que saldo_pendiente se inicialice correctamente
            $this->db->select('total');
            $this->db->where('id', $obra_id);
            $obra = $this->db->get('obras')->row();
            
            if($obra) {
                $this->db->where('id', $obra_id);
                $this->db->update('obras', [
                    'saldo_pendiente' => $obra->total
                ]);
            }
        }
        
        return $obra_id;
    }
    
    /**
     * Actualiza una obra existente
     */
    public function actualizar_obra($obra_id, $data) {
        $data['fecha_modificacion'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $obra_id);
        return $this->db->update('obras', $data);
    }
    
    /**
     * Elimina una obra (soft delete)
     */
    public function eliminar_obra($obra_id, $usuario_id) {
        $this->db->where('id', $obra_id);
        return $this->db->update('obras', [
            'activo' => 0,
            'modificado_por' => $usuario_id,
            'fecha_modificacion' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Obtiene los productos de una obra
     */
    public function get_productos_obra($obra_id) {
        $this->db->select('
            op.*,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo,
            p.foto_producto,
            f.version as formulacion_version,
            f.nombre_version as formulacion_nombre
        ');
        $this->db->from('obras_productos op');
        $this->db->join('productos p', 'p.id = op.producto_id');
        $this->db->join('formulaciones f', 'f.id = op.formulacion_id', 'left');
        $this->db->where('op.obra_id', $obra_id);
        $this->db->order_by('op.fecha_agregado', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Agrega un producto a la obra
     */
    public function agregar_producto($data) {
        $data['fecha_agregado'] = date('Y-m-d H:i:s');
        
        $this->db->insert('obras_productos', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Actualiza un producto de la obra
     */
    public function actualizar_producto($producto_obra_id, $data) {
        $data['fecha_modificacion'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $producto_obra_id);
        return $this->db->update('obras_productos', $data);
    }
    
    /**
     * Elimina un producto de la obra
     */
    public function eliminar_producto($producto_obra_id) {
        $this->db->where('id', $producto_obra_id);
        return $this->db->delete('obras_productos');
    }
    
    /**
     * Obtiene archivos de una obra
     */
    public function get_archivos_obra($obra_id, $categoria = null) {
        $this->db->select('oa.*');
        $this->db->from('obras_archivos oa');
        $this->db->where('oa.obra_id', $obra_id);
        
        if($categoria) {
            $this->db->where('oa.categoria', $categoria);
        }
        
        $this->db->order_by('oa.fecha_subida', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Guarda información de un archivo subido
     */
    public function guardar_archivo($data) {
        $data['fecha_subida'] = date('Y-m-d H:i:s');
        
        $this->db->insert('obras_archivos', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Elimina un archivo de la obra
     */
    public function eliminar_archivo($archivo_id) {
        // Primero obtener la ruta del archivo para eliminarlo físicamente
        $this->db->select('ruta_archivo');
        $this->db->where('id', $archivo_id);
        $archivo = $this->db->get('obras_archivos')->row();
        
        if($archivo && file_exists($archivo->ruta_archivo)) {
            unlink($archivo->ruta_archivo);
        }
        
        $this->db->where('id', $archivo_id);
        return $this->db->delete('obras_archivos');
    }
    
    /**
     * Obtiene comentarios de una obra
     */
    public function get_comentarios_obra($obra_id) {
        $this->db->select('oc.*');
        $this->db->from('obras_comentarios oc');
        $this->db->where('oc.obra_id', $obra_id);
        $this->db->order_by('oc.fecha_comentario', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Agrega un comentario a la obra
     */
    public function agregar_comentario($data) {
        $data['fecha_comentario'] = date('Y-m-d H:i:s');
        $data['editado'] = 0;
        
        $this->db->insert('obras_comentarios', $data);
        return $this->db->insert_id();
    }
    
    /**
     * Actualiza un comentario
     */
    public function actualizar_comentario($comentario_id, $comentario) {
        $this->db->where('id', $comentario_id);
        return $this->db->update('obras_comentarios', [
            'comentario' => $comentario,
            'editado' => 1,
            'fecha_edicion' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Obtiene estadísticas de obras
     */
    public function get_estadisticas() {
        $stats = [];
        
        // Total de obras activas
        $this->db->where('activo', 1);
        $stats['total'] = $this->db->count_all_results('obras');
        
        // Por estatus
        $estatus_list = ['Planificación', 'En Cotización', 'Aprobada', 'En Ejecución', 'Pausada', 'Completada'];
        foreach($estatus_list as $estatus) {
            $this->db->where('estatus', $estatus);
            $this->db->where('activo', 1);
            $stats[strtolower(str_replace(' ', '_', $estatus))] = $this->db->count_all_results('obras');
        }
        
        return $stats;
    }
    
    /**
     * Calcula automáticamente los totales financieros de una obra
     */
    public function calcular_totales_obra($obra_id) {
        // Obtener todos los productos de la obra
        $productos = $this->get_productos_obra($obra_id);
        
        // Calcular subtotal sumando todos los productos
        $subtotal_productos = 0;
        foreach($productos as $producto) {
            $cantidad = $producto->cantidad_ajustada ?: $producto->cantidad_calculada;
            $precio = $producto->precio_unitario ?: 0;
            $subtotal_productos += $cantidad * $precio;
        }
        
        // Obtener datos actuales de la obra para descuentos, IVA y costo estimado
        $this->db->select('descuento_porcentaje, iva_porcentaje, anticipo_porcentaje, costo_real, costo_estimado');
        $this->db->where('id', $obra_id);
        $obra = $this->db->get('obras')->row();
        
        if(!$obra) {
            return false;
        }
        
        // Subtotal = Costo Estimado + Productos
        $costo_estimado = $obra->costo_estimado ?: 0;
        $subtotal = $costo_estimado + $subtotal_productos;
        
        // Calcular descuento
        $descuento_porcentaje = $obra->descuento_porcentaje ?: 0;
        $descuento_monto = $subtotal * ($descuento_porcentaje / 100);
        
        // Calcular IVA
        $iva_porcentaje = $obra->iva_porcentaje ?: 16;
        $base_iva = $subtotal - $descuento_monto;
        $iva_monto = $base_iva * ($iva_porcentaje / 100);
        
        // Calcular total
        $total = $base_iva + $iva_monto;
        
        // Calcular utilidad y margen
        $costo_real = $obra->costo_real ?: 0;
        $utilidad_neta = $total - $costo_real;
        $margen_utilidad = $total > 0 ? ($utilidad_neta / $total) * 100 : 0;
        
        // Calcular anticipo
        $anticipo_porcentaje = $obra->anticipo_porcentaje ?: 0;
        $anticipo_monto = $total * ($anticipo_porcentaje / 100);
        
        // Actualizar la obra con los cálculos
        $data = [
            'subtotal' => $subtotal,
            'descuento_monto' => $descuento_monto,
            'iva_monto' => $iva_monto,
            'total' => $total,
            'utilidad_neta' => $utilidad_neta,
            'margen_utilidad' => $margen_utilidad,
            'anticipo_monto' => $anticipo_monto
        ];
        
        $this->db->where('id', $obra_id);
        return $this->db->update('obras', $data);
    }
    
    /**
     * Genera el siguiente folio de recibo (REC-00001, REC-00002, etc.)
     */
    public function generar_folio_recibo() {
        $this->db->select('folio_recibo');
        $this->db->from('obras_pagos');
        $this->db->like('folio_recibo', 'REC-', 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        
        $result = $this->db->get()->row();
        
        if($result) {
            $ultimo_numero = intval(substr($result->folio_recibo, 4));
            $nuevo_numero = $ultimo_numero + 1;
        } else {
            $nuevo_numero = 1;
        }
        
        return 'REC-' . str_pad($nuevo_numero, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Registra un nuevo pago
     */
    public function registrar_pago($data) {
        $data['folio_recibo'] = $this->generar_folio_recibo();
        $data['fecha_registro'] = date('Y-m-d H:i:s');
        $data['activo'] = 1;
        
        $this->db->insert('obras_pagos', $data);
        $pago_id = $this->db->insert_id();
        
        if($pago_id) {
            // Actualizar totales de la obra
            $this->actualizar_totales_pago($data['obra_id']);
        }
        
        return $pago_id;
    }
    
    /**
     * Obtiene los pagos de una obra
     */
    public function get_pagos_obra($obra_id) {
        $this->db->select('*');
        $this->db->from('obras_pagos');
        $this->db->where('obra_id', $obra_id);
        $this->db->where('activo', 1);
        $this->db->order_by('fecha_pago', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Obtiene un pago por ID
     */
    public function get_pago($pago_id) {
        $this->db->select('op.*, o.folio as obra_folio, o.nombre as obra_nombre, o.total, c.razon_social as cliente');
        $this->db->from('obras_pagos op');
        $this->db->join('obras o', 'o.id = op.obra_id');
        $this->db->join('clientes c', 'c.id = o.cliente_id', 'left');
        $this->db->where('op.id', $pago_id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Actualiza los totales de pago de una obra
     */
    public function actualizar_totales_pago($obra_id) {
        // Calcular total pagado
        $this->db->select('SUM(monto) as total_pagado');
        $this->db->from('obras_pagos');
        $this->db->where('obra_id', $obra_id);
        $this->db->where('activo', 1);
        $result = $this->db->get()->row();
        
        $total_pagado = $result->total_pagado ?: 0;
        
        // Obtener total de la obra
        $this->db->select('total, anticipo_monto');
        $this->db->where('id', $obra_id);
        $obra = $this->db->get('obras')->row();
        
        if(!$obra) {
            return false;
        }
        
        $total = $obra->total ?: 0;
        $anticipo_monto = $obra->anticipo_monto ?: 0;
        $saldo_pendiente = $total - $total_pagado;
        
        // Determinar estatus de pago
        if($total_pagado == 0) {
            $estatus_pago = 'Pendiente';
        } elseif($total_pagado >= $total) {
            $estatus_pago = 'Pagado';
        } elseif($total_pagado >= $anticipo_monto) {
            $estatus_pago = 'Parcialmente Pagado';
        } else {
            $estatus_pago = 'Anticipo Recibido';
        }
        
        // Actualizar obra
        $this->db->where('id', $obra_id);
        return $this->db->update('obras', [
            'total_pagado' => $total_pagado,
            'saldo_pendiente' => $saldo_pendiente,
            'estatus_pago' => $estatus_pago
        ]);
    }
    
    /**
     * Cancela un pago
     */
    public function cancelar_pago($pago_id) {
        // Obtener obra_id antes de cancelar
        $this->db->select('obra_id');
        $this->db->where('id', $pago_id);
        $pago = $this->db->get('obras_pagos')->row();
        
        if(!$pago) {
            return false;
        }
        
        // Cancelar pago (soft delete)
        $this->db->where('id', $pago_id);
        $result = $this->db->update('obras_pagos', ['activo' => 0]);
        
        if($result) {
            // Actualizar totales
            $this->actualizar_totales_pago($pago->obra_id);
        }
        
        return $result;
    }

    /**
     * Obtiene la obra vinculada a una orden de venta
     */
    public function get_obra_por_orden_venta($orden_venta_id) {
        $this->db->select('id, folio, nombre, estatus');
        $this->db->from('obras');
        $this->db->where('orden_venta_id', $orden_venta_id);
        $this->db->where('activo', 1);
        $this->db->limit(1);
        return $this->db->get()->row();
    }

    /**
     * Órdenes de venta del cliente disponibles para vincular
     */
    public function get_ordenes_venta_disponibles($cliente_id, $obra_id = null) {
        $this->db->select('ov.id, ov.folio, ov.estatus, ov.total, ov.fecha_orden');
        $this->db->from('ordenes_venta ov');
        $this->db->join('obras o', 'o.orden_venta_id = ov.id AND o.activo = 1', 'left');
        $this->db->where('ov.cliente_id', $cliente_id);
        $this->db->where_in('ov.estatus', ['Cotización', 'Confirmada', 'En Preparación']);
        $this->db->group_start();
        $this->db->where('o.id IS NULL', null, false);
        if ($obra_id) {
            $this->db->or_where('o.id', $obra_id);
        }
        $this->db->group_end();
        $this->db->order_by('ov.fecha_creacion', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Vincula una orden de venta existente a la obra
     */
    public function vincular_orden_venta($obra_id, $orden_venta_id) {
        $obra = $this->db->where('id', $obra_id)->where('activo', 1)->get('obras')->row();
        if (!$obra) {
            return ['success' => false, 'message' => 'Obra no encontrada'];
        }
        if (!empty($obra->orden_venta_id)) {
            return ['success' => false, 'message' => 'La obra ya tiene una orden de venta vinculada'];
        }

        $orden = $this->db->where('id', $orden_venta_id)->get('ordenes_venta')->row();
        if (!$orden) {
            return ['success' => false, 'message' => 'Orden de venta no encontrada'];
        }
        if ((int) $orden->cliente_id !== (int) $obra->cliente_id) {
            return ['success' => false, 'message' => 'La orden de venta pertenece a otro cliente'];
        }

        $vinculada = $this->db->where('orden_venta_id', $orden_venta_id)
            ->where('activo', 1)
            ->where('id !=', $obra_id)
            ->count_all_results('obras');
        if ($vinculada > 0) {
            return ['success' => false, 'message' => 'Esa orden de venta ya está vinculada a otra obra'];
        }

        $this->db->where('id', $obra_id);
        $this->db->update('obras', ['orden_venta_id' => $orden_venta_id]);

        return [
            'success' => true,
            'message' => 'Orden de venta ' . $orden->folio . ' vinculada correctamente',
            'orden_venta_id' => $orden_venta_id,
            'orden_venta_folio' => $orden->folio
        ];
    }

    /**
     * Genera una orden de venta desde los productos calculados de la obra
     */
    public function generar_orden_venta_desde_obra($obra_id, $usuario_id) {
        $obra = $this->get_obra_detalle($obra_id);
        if (!$obra) {
            return ['success' => false, 'message' => 'Obra no encontrada'];
        }
        if (!empty($obra->orden_venta_id)) {
            return ['success' => false, 'message' => 'La obra ya tiene una orden de venta vinculada'];
        }
        if (empty($obra->productos)) {
            return ['success' => false, 'message' => 'La obra no tiene productos para generar la orden'];
        }

        $this->load->model('Ventas/VentasModel');

        $direccion = trim($obra->direccion);
        if ($obra->ciudad) {
            $direccion .= ', ' . $obra->ciudad;
        }
        if ($obra->estado) {
            $direccion .= ', ' . $obra->estado;
        }

        $orden_id = $this->VentasModel->crear_orden([
            'cliente_id' => $obra->cliente_id,
            'fecha_orden' => date('Y-m-d'),
            'fecha_entrega_estimada' => $obra->fecha_fin_estimada,
            'subtotal' => $obra->subtotal,
            'iva' => $obra->iva_monto,
            'total' => $obra->total,
            'saldo_pendiente' => $obra->total,
            'estatus' => 'Cotización',
            'tipo_venta' => 'Pedido',
            'observaciones' => 'Generada desde obra ' . $obra->folio,
            'direccion_envio' => $direccion,
            'condiciones_pago' => $obra->condiciones_pago,
            'creado_por' => $usuario_id
        ]);

        $detalles = [];
        foreach ($obra->productos as $producto) {
            $cantidad = $producto->cantidad_ajustada ?: $producto->cantidad_calculada;
            $precio = $producto->precio_unitario ?: 0;
            $detalles[] = [
                'producto_id' => $producto->producto_id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'subtotal' => $cantidad * $precio,
                'formulacion_id' => $producto->formulacion_id,
                'formulacion_version' => $producto->formulacion_version,
                'observaciones' => $producto->notas
            ];
        }
        $this->VentasModel->agregar_detalle($orden_id, $detalles);

        $tiene_preordenes_obra = $this->db->where('origen_tipo', 'obra')
            ->where('origen_id', (int) $obra_id)
            ->where('estatus', 'Pendiente')
            ->count_all_results('preordenes') > 0;

        if ($tiene_preordenes_obra) {
            $insumos_result = $this->VentasModel->consultar_insumos_venta($orden_id);
        } else {
            $insumos_result = $this->VentasModel->verificar_insumos_y_preordenes_venta($orden_id, $usuario_id);
        }

        $this->db->where('id', $obra_id);
        $this->db->update('obras', ['orden_venta_id' => $orden_id]);

        $orden = $this->VentasModel->get_orden_completa($orden_id);
        return [
            'success' => true,
            'message' => 'Orden de venta ' . $orden->folio . ' generada correctamente',
            'orden_venta_id' => $orden_id,
            'orden_venta_folio' => $orden->folio,
            'insumos' => [
                'ok'              => !($insumos_result['bloqueada'] ?? false),
                'mensaje_resumen' => $insumos_result['mensaje_resumen'] ?? '',
            ],
        ];
    }

    /**
     * Solo consulta disponibilidad (sin pre-órdenes). Para obra en borrador.
     */
    public function consultar_insumos_obra($obra_id) {
        $this->load->model('Produccion/ProductosModel');
        return $this->ProductosModel->consultar_verificacion_insumos((int) $obra_id, 'obra');
    }

    /**
     * Verifica insumos y genera pre-órdenes. Solo al aprobar obra o en flujos de compromiso.
     */
    public function verificar_insumos_y_preordenes_obra($obra_id, $usuario_id) {
        $this->load->model('Produccion/ProductosModel');

        $obra = $this->db->select('folio')->where('id', (int) $obra_id)->get('obras')->row();
        $notas = $obra
            ? ('Verificación automática obra ' . $obra->folio)
            : ('Verificación automática obra ID ' . (int) $obra_id);

        return $this->ProductosModel->procesar_verificacion_insumos_post_creacion(
            (int) $obra_id,
            'obra',
            (int) $usuario_id,
            $notas
        );
    }

    /**
     * Crea solicitudes de producción cuando se aprueba una obra
     */
    public function crear_solicitudes_produccion_desde_obra($obra_id) {
        $obra = $this->get_obra_detalle($obra_id);
        if (!$obra || empty($obra->productos)) {
            return false;
        }

        $CI =& get_instance();
        $usuario_id = $CI->session->userdata('user_id') ?: 1;

        foreach ($obra->productos as $producto) {
            $cantidad = $producto->cantidad_ajustada ?: $producto->cantidad_calculada;
            if ($cantidad <= 0) {
                continue;
            }

            $this->db->where('producto_id', $producto->producto_id);
            $this->db->where('estatus', 'Pendiente');
            if (!empty($obra->orden_venta_id)) {
                $this->db->where('orden_venta_id', $obra->orden_venta_id);
            } else {
                $this->db->like('observaciones', 'obra_id:' . $obra_id);
            }
            if ($this->db->count_all_results('solicitudes_produccion') > 0) {
                continue;
            }

            $this->db->query("CALL sp_generar_folio_solicitud_produccion(@nuevo_folio)");
            $folio = $this->db->query("SELECT @nuevo_folio as folio")->row()->folio;

            $this->db->insert('solicitudes_produccion', [
                'folio' => $folio,
                'orden_venta_id' => $obra->orden_venta_id,
                'producto_id' => $producto->producto_id,
                'formulacion_id' => $producto->formulacion_id,
                'cantidad_solicitada' => $cantidad,
                'fecha_solicitud' => date('Y-m-d'),
                'fecha_requerida' => $obra->fecha_fin_estimada,
                'estatus' => 'Pendiente',
                'prioridad' => 'Media',
                'observaciones' => 'Solicitud desde obra ' . $obra->folio . '. obra_id:' . $obra_id,
                'creado_por' => $usuario_id,
                'fecha_creacion' => date('Y-m-d H:i:s')
            ]);
        }

        return true;
    }

    // =====================================================
    // P8 — CÁLCULO m² → kg → insumos (motor ProductosModel)
    // =====================================================

    /**
     * Calcula materiales para una línea de obra: m² → kg → cubetas → insumos.
     * No usa el fallback rendimiento=1.0 del simulador general.
     *
     * @param int        $producto_id
     * @param float|null $area_m2
     * @param float      $factor_desperdicio
     * @param int|null   $formulacion_id
     * @param float|null $rendimiento_override_m2_kg  Captura en línea si falta en formulación
     */
    public function calcular_materiales_linea_obra($producto_id, $area_m2, $factor_desperdicio = 1.0, $formulacion_id = null, $rendimiento_override_m2_kg = null) {
        $this->load->model('Produccion/ProductosModel');

        $producto_id = (int) $producto_id;
        $area_m2 = $area_m2 !== null && $area_m2 !== '' ? (float) $area_m2 : 0;
        $factor_desperdicio = max(1.0, (float) ($factor_desperdicio ?: 1.0));

        if ($area_m2 <= 0) {
            return ['success' => false, 'message' => 'Ingrese el área de aplicación en m².'];
        }

        if ($formulacion_id) {
            $formulacion = $this->ProductosModel->get_formulacion_completa((int) $formulacion_id);
        } else {
            $formulacion = $this->ProductosModel->get_formulacion_activa($producto_id);
            if ($formulacion) {
                $formulacion = $this->ProductosModel->get_formulacion_completa($formulacion->id);
            }
        }

        if (!$formulacion) {
            return [
                'success' => false,
                'message' => 'El producto no tiene formulación activa. Asigne una en Producción > Productos.',
                'requiere_formulacion' => true,
                'producto_id' => $producto_id,
            ];
        }

        $rendimiento_form = !empty($formulacion->rendimiento_m2_por_kg) ? (float) $formulacion->rendimiento_m2_por_kg : 0;
        $rendimiento_override = $rendimiento_override_m2_kg !== null && $rendimiento_override_m2_kg !== ''
            ? (float) $rendimiento_override_m2_kg
            : 0;
        $rendimiento_efectivo = $rendimiento_form > 0 ? $rendimiento_form : $rendimiento_override;

        if ($rendimiento_efectivo <= 0) {
            return [
                'success' => false,
                'requiere_rendimiento' => true,
                'message' => 'La formulación no tiene rendimiento m²/kg. Captúrelo en la formulación o en el campo de esta línea.',
                'formulacion_id' => (int) $formulacion->id,
                'formulacion_nombre' => $formulacion->nombre_version ?? '',
                'producto_id' => $producto_id,
                'url_formulacion' => base_url('produccion/Productos'),
            ];
        }

        $m2_bruto = $area_m2;
        $m2_efectivo = round($m2_bruto * $factor_desperdicio, 4);
        $kg_necesarios = round($m2_efectivo / $rendimiento_efectivo, 4);
        $cantidad_lote = (float) ($formulacion->cantidad_producida ?: 0);
        if ($cantidad_lote <= 0) {
            return [
                'success' => false,
                'message' => 'La formulación no define cantidad_producida (kg por cubeta/lote).',
                'formulacion_id' => (int) $formulacion->id,
            ];
        }

        $cubetas = (int) ceil($kg_necesarios / $cantidad_lote);
        $unidad_cantidad = $this->_unidad_cubeta_formulacion($formulacion);

        $motor = $this->ProductosModel->calcular_insumos_para_proyecto((int) $formulacion->id, $cubetas, null);
        if (!$motor) {
            return ['success' => false, 'message' => 'No se pudo calcular insumos con el motor de formulaciones.'];
        }

        $insumos_top = $this->_extraer_insumos_top_desde_motor($motor, 15);

        return [
            'success' => true,
            'producto_id' => $producto_id,
            'formulacion_id' => (int) $formulacion->id,
            'formulacion_version' => $formulacion->version ?? null,
            'formulacion_nombre' => $formulacion->nombre_version ?? '',
            'rendimiento_m2_por_kg' => $rendimiento_efectivo,
            'rendimiento_origen' => $rendimiento_form > 0 ? 'formulacion' : 'linea',
            'factor_desperdicio' => $factor_desperdicio,
            'm2_bruto' => $m2_bruto,
            'm2_efectivo' => $m2_efectivo,
            'kg_necesarios' => $kg_necesarios,
            'cubetas' => $cubetas,
            'cantidad_calculada' => $cubetas,
            'unidad' => $unidad_cantidad,
            'cantidad_producida_lote' => $cantidad_lote,
            'unidad_produccion' => $formulacion->unidad_produccion ?? 'Kg',
            'insumos' => $insumos_top,
            'hay_insumos_faltantes' => !empty($motor['hay_insumos_faltantes']),
            'hay_insumos_revision_manual' => !empty($motor['hay_insumos_revision_manual']),
            'formula_texto' => 'kg = (m² × factor) ÷ rendimiento_m²/kg; cubetas = ⌈kg ÷ cantidad_producida⌉',
        ];
    }

    /**
     * Calcula y consolida materiales de todas las líneas de una obra.
     */
    public function calcular_materiales_obra($obra_id) {
        $obra = $this->get_obra_detalle($obra_id);
        if (!$obra) {
            return ['success' => false, 'message' => 'Obra no encontrada'];
        }

        if (empty($obra->productos)) {
            return ['success' => false, 'message' => 'La obra no tiene productos para calcular.'];
        }

        $lineas = [];
        $mapa_insumos = [];
        $errores = [];
        $totales = ['m2_bruto' => 0, 'm2_efectivo' => 0, 'kg' => 0, 'cubetas' => 0];

        foreach ($obra->productos as $producto) {
            $area = $producto->area_aplicacion !== null ? (float) $producto->area_aplicacion : 0;
            $factor = (float) ($producto->factor_desperdicio ?: 1.0);

            if ($area <= 0) {
                $cantidad = (float) ($producto->cantidad_ajustada ?: $producto->cantidad_calculada ?: 0);
                if ($cantidad <= 0) {
                    $errores[] = ($producto->producto_nombre ?? 'Producto') . ': sin área m² ni cantidad.';
                    continue;
                }

                $this->load->model('Produccion/ProductosModel');
                $formulacion_id = (int) ($producto->formulacion_id ?: 0);
                if (!$formulacion_id) {
                    $fa = $this->ProductosModel->get_formulacion_activa($producto->producto_id);
                    $formulacion_id = $fa ? (int) $fa->id : 0;
                }
                if (!$formulacion_id) {
                    $errores[] = ($producto->producto_nombre ?? 'Producto') . ': sin formulación.';
                    continue;
                }

                $motor = $this->ProductosModel->calcular_insumos_para_proyecto($formulacion_id, $cantidad, null);
                if (!$motor) {
                    $errores[] = ($producto->producto_nombre ?? 'Producto') . ': error al calcular por cubetas.';
                    continue;
                }

                $linea_res = [
                    'success' => true,
                    'producto_nombre' => $producto->producto_nombre,
                    'producto_codigo' => $producto->producto_codigo,
                    'seccion_obra' => $producto->seccion_obra,
                    'modo' => 'cubetas_directas',
                    'cubetas' => $cantidad,
                    'kg_necesarios' => $motor['kg_necesarios'] ?? null,
                    'm2_bruto' => null,
                    'm2_efectivo' => null,
                    'insumos' => $this->_extraer_insumos_top_desde_motor($motor, 8),
                ];
            } else {
                $linea_res = $this->calcular_materiales_linea_obra(
                    (int) $producto->producto_id,
                    $area,
                    $factor,
                    !empty($producto->formulacion_id) ? (int) $producto->formulacion_id : null,
                    $producto->rendimiento_teorico
                );
                $linea_res['producto_nombre'] = $producto->producto_nombre;
                $linea_res['producto_codigo'] = $producto->producto_codigo;
                $linea_res['seccion_obra'] = $producto->seccion_obra;
                $linea_res['modo'] = 'm2';
            }

            if (empty($linea_res['success'])) {
                $errores[] = ($producto->producto_nombre ?? 'Producto') . ': ' . ($linea_res['message'] ?? 'Error de cálculo');
                $lineas[] = $linea_res;
                continue;
            }

            $lineas[] = $linea_res;
            if (!empty($linea_res['m2_bruto'])) {
                $totales['m2_bruto'] += (float) $linea_res['m2_bruto'];
                $totales['m2_efectivo'] += (float) $linea_res['m2_efectivo'];
            }
            if (!empty($linea_res['kg_necesarios'])) {
                $totales['kg'] += (float) $linea_res['kg_necesarios'];
            }
            $totales['cubetas'] += (float) ($linea_res['cubetas'] ?? 0);

            foreach ($linea_res['insumos'] as $ins) {
                $key = (int) $ins['insumo_id'];
                if (!isset($mapa_insumos[$key])) {
                    $mapa_insumos[$key] = $ins;
                } else {
                    $mapa_insumos[$key]['cantidad'] += (float) $ins['cantidad'];
                }
            }
        }

        $insumos_consolidados = array_values($mapa_insumos);
        usort($insumos_consolidados, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);

        return [
            'success' => empty($errores) || !empty($lineas),
            'obra_id' => (int) $obra_id,
            'obra_folio' => $obra->folio,
            'lineas' => $lineas,
            'totales' => $totales,
            'insumos_consolidados' => array_slice($insumos_consolidados, 0, 25),
            'errores' => $errores,
            'hay_errores' => !empty($errores),
        ];
    }

    /**
     * Prepara datos validados para insertar en obras_productos (servidor).
     */
    public function preparar_linea_producto_obra(array $input) {
        $producto_id = (int) ($input['producto_id'] ?? 0);
        $area = isset($input['area_aplicacion']) && $input['area_aplicacion'] !== ''
            ? (float) $input['area_aplicacion']
            : 0;
        $factor = (float) ($input['factor_desperdicio'] ?? 1.10);
        $formulacion_id = !empty($input['formulacion_id']) ? (int) $input['formulacion_id'] : null;
        $rendimiento_override = isset($input['rendimiento_teorico']) && $input['rendimiento_teorico'] !== ''
            ? (float) $input['rendimiento_teorico']
            : null;

        if ($area <= 0) {
            return [
                'success' => false,
                'message' => 'El área de aplicación (m²) es obligatoria para el cálculo automático.',
            ];
        }

        $calculo = $this->calcular_materiales_linea_obra(
            $producto_id,
            $area,
            $factor,
            $formulacion_id,
            $rendimiento_override
        );

        if (empty($calculo['success'])) {
            return $calculo;
        }

        return [
            'success' => true,
            'data' => [
                'producto_id' => $producto_id,
                'cantidad_calculada' => $calculo['cantidad_calculada'],
                'unidad' => $calculo['unidad'],
                'area_aplicacion' => $area,
                'rendimiento_teorico' => $calculo['rendimiento_m2_por_kg'],
                'factor_desperdicio' => $factor,
                'formulacion_id' => $calculo['formulacion_id'],
                'formulacion_version' => $calculo['formulacion_version'],
                'notas' => $input['notas'] ?? null,
                'seccion_obra' => $input['seccion_obra'] ?? null,
                'precio_unitario' => $input['precio_unitario'] ?? null,
                'cantidad_ajustada' => !empty($input['cantidad_ajustada']) ? (float) $input['cantidad_ajustada'] : null,
            ],
            'calculo' => $calculo,
        ];
    }

    /**
     * Obtiene resumen de entregas de productos de una obra
     * Devuelve cada obra_producto con cantidad calculada, ajustada, entregada y pendiente.
     */
    public function get_entregas_obra($obra_id) {
        $this->db->select('
            op.id as obra_producto_id,
            p.nombre as producto_nombre,
            p.codigo as producto_codigo,
            op.seccion_obra,
            op.unidad,
            op.cantidad_calculada,
            op.cantidad_ajustada,
            COALESCE(op.cantidad_entregada, 0) as cantidad_entregada,
            (COALESCE(op.cantidad_ajustada, op.cantidad_calculada) - COALESCE(op.cantidad_entregada, 0)) as cantidad_pendiente,
            op.precio_unitario
        ');
        $this->db->from('obras_productos op');
        $this->db->join('productos p', 'p.id = op.producto_id');
        $this->db->where('op.obra_id', (int) $obra_id);
        $this->db->order_by('op.fecha_agregado', 'ASC');

        $productos = $this->db->get()->result();

        // Historial de entregas para esta obra
        $this->db->select('
            ea.folio,
            ea.fecha_entrega,
            ea.estatus,
            dea.obra_producto_id,
            dea.cantidad_entregada,
            p.nombre as producto_nombre
        ');
        $this->db->from('entregas_almacen ea');
        $this->db->join('detalle_entregas_almacen dea', 'dea.entrega_id = ea.id');
        $this->db->join('productos p', 'p.id = dea.producto_id');
        $this->db->where('ea.obra_id', (int) $obra_id);
        $this->db->where('ea.estatus', 'Activa');
        $this->db->order_by('ea.fecha_entrega', 'DESC');
        $historial = $this->db->get()->result();

        return [
            'productos' => $productos,
            'historial' => $historial,
        ];
    }

    private function _unidad_cubeta_formulacion($formulacion) {
        $u = strtolower(trim((string) ($formulacion->unidad_produccion ?? '')));
        if (in_array($u, ['pza', 'pz', 'pieza', 'cubeta', 'caja'], true)) {
            return ucfirst($u === 'pz' ? 'Pza' : ($u === 'pza' ? 'Pza' : $u));
        }
        return 'Cubeta';
    }

    private function _extraer_insumos_top_desde_motor(array $motor, $limite = 15) {
        $lista = [];
        foreach ($motor['componentes'] ?? [] as $comp) {
            if (($comp->tipo_componente ?? '') !== 'Insumo' || empty($comp->insumo_id)) {
                continue;
            }
            $cantidad = (float) ($comp->cantidad_en_unidad_insumo ?? $comp->cantidad_escalada ?? 0);
            if ($cantidad <= 0) {
                continue;
            }
            $lista[] = [
                'insumo_id' => (int) $comp->insumo_id,
                'insumo_codigo' => $comp->insumo_codigo ?? '',
                'insumo_nombre' => $comp->insumo_nombre ?? '',
                'unidad' => $comp->insumo_unidad_medida ?? ($comp->unidad ?? ''),
                'cantidad' => round($cantidad, 4),
            ];
        }
        usort($lista, fn($a, $b) => $b['cantidad'] <=> $a['cantidad']);
        return array_slice($lista, 0, (int) $limite);
    }
}
