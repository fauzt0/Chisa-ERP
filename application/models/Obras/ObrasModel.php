<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ObrasModel extends CI_Model {
    
    /**
     * Genera el siguiente folio de obra (OB-00001, OB-00002, etc.)
     */
    public function generar_folio() {
        $this->db->select('folio');
        $this->db->from('obras');
        $this->db->like('folio', 'OB-', 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        
        $result = $this->db->get()->row();
        
        if($result) {
            // Extraer el número del último folio
            $ultimo_numero = intval(substr($result->folio, 3));
            $nuevo_numero = $ultimo_numero + 1;
        } else {
            $nuevo_numero = 1;
        }
        
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

        $this->db->where('id', $obra_id);
        $this->db->update('obras', ['orden_venta_id' => $orden_id]);

        $orden = $this->VentasModel->get_orden_completa($orden_id);
        return [
            'success' => true,
            'message' => 'Orden de venta ' . $orden->folio . ' generada correctamente',
            'orden_venta_id' => $orden_id,
            'orden_venta_folio' => $orden->folio
        ];
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
}
