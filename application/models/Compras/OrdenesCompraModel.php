<?php
/**
 * OrdenesCompraModel - Modelo de gestión de órdenes de compra
 * 
 * Gestiona órdenes de compra con detalles, workflow de estatus e integración con inventario
 * 
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class OrdenesCompraModel extends MY_Model {
    
    protected $tableName = 'ordenes_compra';
    
    // Configuración para DataTables
    protected $datatableConfig = [
        'table' => 'ordenes_compra',
        'column_order' => ['ordenes_compra.folio', 'ordenes_compra.fecha_orden', 'proveedores.razon_social', 'ordenes_compra.total', 'ordenes_compra.estatus_pago', 'ordenes_compra.estatus', null],
        'column_search' => ['ordenes_compra.folio', 'proveedores.razon_social', 'proveedores.rfc'],
        'order' => ['fecha_orden' => 'DESC']
    ];
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Override de _get_datatables_query para agregar join con proveedores
     */
    protected function _get_datatables_query() {
        $this->db->select('ordenes_compra.*, proveedores.razon_social');
        $this->db->from($this->tableName);
        $this->db->join('proveedores', 'proveedores.id = ordenes_compra.proveedor_id', 'left');
        
        // Filtro por estatus (enviado desde la vista)
        if (!empty($_POST['filtro_estatus'])) {
            $this->db->where('ordenes_compra.estatus', $_POST['filtro_estatus']);
        }

        if (!empty($_POST['filtro_estatus_pago'])) {
            $this->db->where('ordenes_compra.estatus_pago', $_POST['filtro_estatus_pago']);
        }
        
        // Búsqueda
        $i = 0;
        if(isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
            foreach ($this->datatableConfig['column_search'] as $column) {
                if($i === 0) {
                    $this->db->group_start();
                    $this->db->like($column, $_POST['search']['value']);
                } else {
                    $this->db->or_like($column, $_POST['search']['value']);
                }
                
                if(count($this->datatableConfig['column_search']) - 1 == $i) {
                    $this->db->group_end();
                }
                $i++;
            }
        }
        
        // Ordenamiento
        if(isset($_POST['order'])) {
            $column_index = $_POST['order'][0]['column'];
            $column_name = $this->datatableConfig['column_order'][$column_index];
            $this->db->order_by($column_name, $_POST['order'][0]['dir']);
        } elseif (isset($this->datatableConfig['order'])) {
            $order = $this->datatableConfig['order'];
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }
    
    /**
     * Override de get_datatables
     */
    public function get_datatables() {
        $this->_get_datatables_query();
        if(isset($_POST['length']) && $_POST['length'] != -1) {
            $this->db->limit($_POST['length'], $_POST['start']);
        }
        $query = $this->db->get();
        return $query->result();
    }
    
    /**
     * Override de count_filtered
     */
    public function count_filtered() {
        $this->_get_datatables_query();
        $query = $this->db->get();
        return $query->num_rows();
    }
    
    /**
     * Override de count_all
     */
    public function count_all($where = []) {
        $this->db->from($this->tableName);
        return $this->db->count_all_results();
    }
    
    /**
     * Obtiene una orden completa con detalles
     */
    public function get_orden($id) {
        $this->db->select('ordenes_compra.*, proveedores.razon_social, proveedores.nombre_comercial,
            proveedores.rfc AS rfc_proveedor, proveedores.telefono AS telefono_proveedor,
            proveedores.email AS email_proveedor, proveedores.direccion AS direccion_proveedor,
            proveedores.ciudad AS ciudad_proveedor, proveedores.estado AS estado_proveedor,
            proveedores.codigo_postal AS cp_proveedor');
        $this->db->from($this->tableName);
        $this->db->join('proveedores', 'proveedores.id = ordenes_compra.proveedor_id', 'left');
        $this->db->where('ordenes_compra.id', $id);
        $orden = $this->db->get()->row();
        
        if($orden) {
            $orden->detalles = $this->get_detalles($id);
        }
        
        return $orden;
    }
    
    /**
     * Obtiene detalles de una orden
     */
    public function get_detalles($orden_id) {
        $this->db->select('detalle_orden_compra.*, insumos.codigo, insumos.nombre_tecnico, insumos.unidad_medida, detalle_orden_compra.nombre_proveedor, detalle_orden_compra.codigo_proveedor');
        $this->db->from('detalle_orden_compra');
        $this->db->join('insumos', 'insumos.id = detalle_orden_compra.insumo_id');
        $this->db->where('detalle_orden_compra.orden_compra_id', $orden_id);
        $this->db->order_by('detalle_orden_compra.id', 'ASC');
        return $this->db->get()->result();
    }
    
    /**
     * Crea una nueva orden de compra
     */
    public function crear_orden($data) {
        // Generar folio si no existe
        if(empty($data['folio'])) {
            $data['folio'] = $this->generar_folio();
        }
        
        // Datos por defecto
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        $data['estatus'] = 'Borrador';
        
        return $this->db->insert($this->tableName, $data);
    }
    
    /**
     * Actualiza una orden de compra
     */
    public function actualizar_orden($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, $data);
    }
    
    /**
     * Elimina una orden (solo si está en Borrador)
     */
    public function eliminar_orden($id) {
        // Verificar estatus
        $orden = $this->get_orden($id);
        if(!$orden) {
            return ['success' => false, 'message' => 'Orden no encontrada'];
        }
        
        if($orden->estatus != 'Borrador') {
            return ['success' => false, 'message' => 'Solo se pueden eliminar órdenes en estado Borrador'];
        }
        
        // Eliminar detalles (cascade automático)
        $this->db->where('id', $id);
        $result = $this->db->delete($this->tableName);
        
        return ['success' => $result, 'message' => $result ? 'Orden eliminada' : 'Error al eliminar'];
    }
    
    /**
     * Agrega un detalle a la orden
     */
    public function agregar_detalle($orden_id, $data) {
        $data['orden_compra_id'] = $orden_id;
        
        // El trigger calculará el subtotal automáticamente
        $result = $this->db->insert('detalle_orden_compra', $data);
        
        if($result) {
            // Recalcular totales de la orden
            $this->recalcular_totales($orden_id);
        }
        
        return $result;
    }
    
    /**
     * Actualiza un detalle
     */
    public function actualizar_detalle($id, $data) {
        $this->db->where('id', $id);
        $result = $this->db->update('detalle_orden_compra', $data);
        
        if($result) {
            // Obtener orden_id para recalcular
            $detalle = $this->db->where('id', $id)->get('detalle_orden_compra')->row();
            if($detalle) {
                $this->recalcular_totales($detalle->orden_compra_id);
            }
        }
        
        return $result;
    }
    
    /**
     * Elimina un detalle
     */
    public function eliminar_detalle($id) {
        // Obtener orden_id antes de eliminar
        $detalle = $this->db->where('id', $id)->get('detalle_orden_compra')->row();
        
        $this->db->where('id', $id);
        $result = $this->db->delete('detalle_orden_compra');
        
        if($result && $detalle) {
            $this->recalcular_totales($detalle->orden_compra_id);
        }
        
        return $result;
    }
    
    /**
     * Recalcula totales de una orden
     */
    public function recalcular_totales($orden_id) {
        $this->db->select('SUM(subtotal) as subtotal_total');
        $this->db->where('orden_compra_id', $orden_id);
        $result = $this->db->get('detalle_orden_compra')->row();
        
        $subtotal = $result->subtotal_total ?? 0;
        $iva = $subtotal * 0.16;
        $total = $subtotal + $iva;
        
        $this->db->where('id', $orden_id);
        $this->db->update($this->tableName, [
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total
        ]);

        $this->actualizar_estatus_pago($orden_id);
    }
    
    /**
     * Cambia el estatus de una orden
     */
    public function cambiar_estatus($id, $nuevo_estatus, $user_id = null) {
        $orden = $this->get_orden($id);
        if(!$orden) {
            return ['success' => false, 'message' => 'Orden no encontrada'];
        }
        
        $data = ['estatus' => $nuevo_estatus];
        
        // Si se aprueba, registrar usuario y fecha
        if($nuevo_estatus == 'Enviada' && $orden->estatus == 'Borrador') {
            $data['aprobado_por'] = $user_id;
            $data['fecha_aprobacion'] = date('Y-m-d H:i:s');
        }
        
        $this->db->where('id', $id);
        $result = $this->db->update($this->tableName, $data);

        if ($result && !in_array($nuevo_estatus, ['Borrador', 'Cancelada'], true)) {
            $this->actualizar_estatus_pago($id);
        }

        return ['success' => $result, 'message' => $result ? 'Estatus actualizado' : 'Error al actualizar'];
    }
    
    /**
     * Recibe mercancía y actualiza inventario
     */
    public function recibir_mercancia($orden_id, $detalles_recibidos, $user_id = null) {
        $orden = $this->get_orden($orden_id);
        if(!$orden) {
            return ['success' => false, 'message' => 'Orden no encontrada'];
        }
        
        $this->db->trans_start();
        
        $todo_recibido = true;
        
        foreach($detalles_recibidos as $detalle) {
            $detalle_id = $detalle['detalle_id'];
            $cantidad_recibida = $detalle['cantidad_recibida'];
            
            // Obtener detalle actual
            $detalle_actual = $this->db->where('id', $detalle_id)->get('detalle_orden_compra')->row();
            
            if(!$detalle_actual) continue;
            
            // Actualizar cantidad recibida
            $nueva_cantidad_recibida = $detalle_actual->cantidad_recibida + $cantidad_recibida;
            
            $this->db->where('id', $detalle_id);
            $this->db->update('detalle_orden_compra', [
                'cantidad_recibida' => $nueva_cantidad_recibida
            ]);
            
            // Verificar si todo está recibido
            if($nueva_cantidad_recibida < $detalle_actual->cantidad_solicitada) {
                $todo_recibido = false;
            }
            
            // Obtener insumo actual
            $insumo = $this->db->where('id', $detalle_actual->insumo_id)->get('insumos')->row();
            
            // Crear movimiento de inventario (Polimórfico: Insumos)
            $movimiento = [
                'insumo_id' => $detalle_actual->insumo_id,
                'producto_id' => null, // Explicitly null
                'tipo_movimiento' => 'Entrada',
                'cantidad' => $cantidad_recibida,
                'stock_anterior' => $insumo->stock_actual,
                'stock_nuevo' => $insumo->stock_actual + $cantidad_recibida,
                'costo_unitario' => $detalle_actual->precio_unitario,
                'costo_total' => $cantidad_recibida * $detalle_actual->precio_unitario,
                'orden_compra_id' => $orden_id,
                'motivo' => 'Recepción de orden de compra ' . $orden->folio,
                'usuario_id' => $user_id,
                'fecha_movimiento' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('movimientos_inventario', $movimiento);
            
            // El trigger actualiza stock_actual automáticamente
        }
        
        // Actualizar estatus de la orden
        $nuevo_estatus = $todo_recibido ? 'Recibida' : 'Recibida Parcial';
        $this->db->where('id', $orden_id);
        $this->db->update($this->tableName, [
            'estatus' => $nuevo_estatus,
            'fecha_entrega_real' => date('Y-m-d')
        ]);
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Error al recibir mercancía'];
        }

        $orden_actualizada = $this->get_orden($orden_id);
        return [
            'success' => true,
            'message' => 'Mercancía recibida correctamente',
            'estatus' => $nuevo_estatus,
            'orden' => $orden_actualizada,
        ];
    }
    
    /**
     * Genera folio único para orden
     */
    private function generar_folio() {
        $year = date('Y');
        $prefijo = 'OC-' . $year . '-';
        
        $this->db->select('folio');
        $this->db->from($this->tableName);
        $this->db->like('folio', $prefijo, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $ultimo = $this->db->get()->row();
        
        if($ultimo) {
            $numero = intval(substr($ultimo->folio, strlen($prefijo))) + 1;
        } else {
            $numero = 1;
        }
        
        return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Obtiene órdenes de un proveedor
     */
    public function get_ordenes_proveedor($proveedor_id) {
        $this->db->where('proveedor_id', $proveedor_id);
        $this->db->order_by('fecha_orden', 'DESC');
        return $this->db->get($this->tableName)->result();
    }

    /**
     * Historial paginado de órdenes de compra de un proveedor
     */
    public function get_historial_ordenes($proveedor_id, $limit = 10, $offset = 0) {
        $this->db->select('id, folio, fecha_orden, total, estatus, estatus_pago, saldo_pendiente, monto_pagado');
        $this->db->from($this->tableName);
        $this->db->where('proveedor_id', $proveedor_id);
        $this->db->order_by('fecha_orden', 'DESC');
        $this->db->limit($limit, $offset);
        return $this->db->get()->result();
    }

    /**
     * Cuenta el historial de órdenes de compra de un proveedor
     */
    public function count_historial_ordenes($proveedor_id) {
        $this->db->where('proveedor_id', $proveedor_id);
        return $this->db->count_all_results($this->tableName);
    }
    
    /**
     * Obtiene estadísticas de órdenes
     */
    public function get_estadisticas() {
        $stats = [];
        
        // Total de órdenes
        $stats['total_ordenes'] = $this->db->count_all_results($this->tableName);
        
        // Órdenes pendientes (Enviada, Confirmada, En Tránsito)
        $this->db->where_in('estatus', ['Enviada', 'Confirmada', 'En Tránsito']);
        $stats['ordenes_pendientes'] = $this->db->count_all_results($this->tableName);
        
        // Órdenes recibidas este mes
        $this->db->where('estatus', 'Recibida');
        $this->db->where('MONTH(fecha_entrega_real)', date('m'));
        $this->db->where('YEAR(fecha_entrega_real)', date('Y'));
        $stats['recibidas_mes'] = $this->db->count_all_results($this->tableName);
        
        // Total gastado este mes
        $this->db->select('SUM(total) as total_mes');
        $this->db->where('MONTH(fecha_orden)', date('m'));
        $this->db->where('YEAR(fecha_orden)', date('Y'));
        $this->db->where_in('estatus', ['Enviada', 'Confirmada', 'En Tránsito', 'Recibida Parcial', 'Recibida']);
        $result = $this->db->get($this->tableName)->row();
        $stats['total_mes'] = $result->total_mes ?? 0;
        
        return $stats;
    }

    /**
     * Rango de fechas según periodo solicitado (mes, trimestre, personalizado).
     */
    public function resolver_rango_periodo($periodo, $fecha_inicio = null, $fecha_fin = null) {
        $hoy = date('Y-m-d');

        switch ($periodo) {
            case 'trimestre':
                $mes = (int) date('n');
                $trimestre_inicio = (int) (floor(($mes - 1) / 3) * 3 + 1);
                $inicio = date('Y') . '-' . str_pad($trimestre_inicio, 2, '0', STR_PAD_LEFT) . '-01';
                $fin_mes = $trimestre_inicio + 2;
                $fin = date('Y-m-t', strtotime(date('Y') . '-' . str_pad($fin_mes, 2, '0', STR_PAD_LEFT) . '-01'));
                break;
            case 'personalizado':
                $inicio = $fecha_inicio ?: date('Y-m-01');
                $fin = $fecha_fin ?: $hoy;
                break;
            case 'mes':
            default:
                $inicio = date('Y-m-01');
                $fin = date('Y-m-t');
                break;
        }

        if (strtotime($inicio) > strtotime($fin)) {
            $tmp = $inicio;
            $inicio = $fin;
            $fin = $tmp;
        }

        return ['inicio' => $inicio, 'fin' => $fin];
    }

    /**
     * Reporte de compras: totales, desglose por proveedor y listado de OC del periodo.
     */
    public function get_reporte_compras($periodo = 'mes', $fecha_inicio = null, $fecha_fin = null) {
        $rango = $this->resolver_rango_periodo($periodo, $fecha_inicio, $fecha_fin);

        // Monto gastado: OC Recibida o Recibida Parcial en el periodo (por fecha de orden)
        $this->db->select('COALESCE(SUM(total), 0) AS monto_gastado', false);
        $this->db->from($this->tableName);
        $this->db->where('fecha_orden >=', $rango['inicio']);
        $this->db->where('fecha_orden <=', $rango['fin']);
        $this->db->where_in('estatus', ['Recibida', 'Recibida Parcial']);
        $monto_row = $this->db->get()->row();
        $monto_gastado = (float) ($monto_row->monto_gastado ?? 0);

        // OC recibidas completas
        $this->db->where('fecha_orden >=', $rango['inicio']);
        $this->db->where('fecha_orden <=', $rango['fin']);
        $this->db->where('estatus', 'Recibida');
        $oc_recibidas = $this->db->count_all_results($this->tableName);

        // OC pendientes de recibir
        $this->db->where('fecha_orden >=', $rango['inicio']);
        $this->db->where('fecha_orden <=', $rango['fin']);
        $this->db->where_in('estatus', ['Enviada', 'Confirmada', 'En Tránsito', 'Recibida Parcial']);
        $oc_pendientes = $this->db->count_all_results($this->tableName);

        // Desglose por proveedor (solo OC con gasto efectivo)
        $this->db->select('proveedores.id, proveedores.razon_social,
            COUNT(ordenes_compra.id) AS num_ordenes,
            COALESCE(SUM(ordenes_compra.total), 0) AS monto_total', false);
        $this->db->from($this->tableName);
        $this->db->join('proveedores', 'proveedores.id = ordenes_compra.proveedor_id', 'left');
        $this->db->where('ordenes_compra.fecha_orden >=', $rango['inicio']);
        $this->db->where('ordenes_compra.fecha_orden <=', $rango['fin']);
        $this->db->where_in('ordenes_compra.estatus', ['Recibida', 'Recibida Parcial']);
        $this->db->group_by('proveedores.id, proveedores.razon_social');
        $this->db->order_by('monto_total', 'DESC');
        $por_proveedor = $this->db->get()->result();

        // Listado de OC del periodo
        $this->db->select('ordenes_compra.id, ordenes_compra.folio, ordenes_compra.fecha_orden,
            ordenes_compra.total, ordenes_compra.estatus, proveedores.razon_social');
        $this->db->from($this->tableName);
        $this->db->join('proveedores', 'proveedores.id = ordenes_compra.proveedor_id', 'left');
        $this->db->where('ordenes_compra.fecha_orden >=', $rango['inicio']);
        $this->db->where('ordenes_compra.fecha_orden <=', $rango['fin']);
        $this->db->where_not_in('ordenes_compra.estatus', ['Cancelada']);
        $this->db->order_by('ordenes_compra.fecha_orden', 'DESC');
        $ordenes = $this->db->get()->result();

        return [
            'periodo'       => $periodo,
            'fecha_inicio'  => $rango['inicio'],
            'fecha_fin'     => $rango['fin'],
            'monto_gastado' => $monto_gastado,
            'oc_recibidas'  => $oc_recibidas,
            'oc_pendientes' => $oc_pendientes,
            'por_proveedor' => $por_proveedor,
            'ordenes'       => $ordenes,
        ];
    }

    /**
     * Construye el contenido de un correo simulado para solicitar productos al proveedor.
     */
    public function construir_correo_simulado($orden_id) {
        $orden = $this->get_orden($orden_id);
        if (!$orden) {
            return ['success' => false, 'message' => 'Orden no encontrada'];
        }

        if (!in_array($orden->estatus, ['Enviada', 'Confirmada'])) {
            return ['success' => false, 'message' => 'Solo se puede simular correo para órdenes Enviadas o Confirmadas'];
        }

        $CI =& get_instance();
        $CI->load->model('Config/EmpresaModel');
        $empresa = $CI->EmpresaModel->get_config();

        $nombre_empresa = $empresa->nombre_comercial ?: $empresa->razon_social;
        $destinatario = $orden->email_proveedor ?: '(sin email registrado)';
        $asunto = 'Solicitud de productos — OC ' . $orden->folio . ' — ' . $nombre_empresa;

        $filas_html = '';
        $filas_texto = '';
        foreach ($orden->detalles as $det) {
            $nombre_prod = $det->nombre_proveedor ?: $det->nombre_tecnico;
            $cantidad = number_format((float) $det->cantidad_solicitada, 2);
            $precio = number_format((float) $det->precio_unitario, 2);
            $subtotal = number_format((float) $det->subtotal, 2);
            $um = htmlspecialchars($det->unidad_medida ?? '', ENT_QUOTES, 'UTF-8');

            $filas_html .= '<tr>'
                . '<td style="padding:8px;border:1px solid #dee2e6;">' . htmlspecialchars($nombre_prod, ENT_QUOTES, 'UTF-8') . '</td>'
                . '<td style="padding:8px;border:1px solid #dee2e6;text-align:center;">' . $cantidad . ' ' . $um . '</td>'
                . '<td style="padding:8px;border:1px solid #dee2e6;text-align:right;">$' . $precio . '</td>'
                . '<td style="padding:8px;border:1px solid #dee2e6;text-align:right;">$' . $subtotal . '</td>'
                . '</tr>';

            $filas_texto .= "- {$nombre_prod}: {$cantidad} {$det->unidad_medida} × \${$precio} = \${$subtotal}\n";
        }

        $total_fmt = number_format((float) $orden->total, 2);
        $fecha_fmt = date('d/m/Y', strtotime($orden->fecha_orden));
        $entrega = $orden->fecha_entrega_estimada
            ? date('d/m/Y', strtotime($orden->fecha_entrega_estimada))
            : 'Por confirmar';

        $cuerpo_html = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#333;">'
            . '<p>Estimado proveedor <strong>' . htmlspecialchars($orden->razon_social, ENT_QUOTES, 'UTF-8') . '</strong>,</p>'
            . '<p>Por medio del presente solicitamos el suministro de los siguientes productos conforme a la orden de compra <strong>' . htmlspecialchars($orden->folio, ENT_QUOTES, 'UTF-8') . '</strong>:</p>'
            . '<table style="width:100%;border-collapse:collapse;margin:16px 0;">'
            . '<thead><tr style="background:#f8f9fa;">'
            . '<th style="padding:8px;border:1px solid #dee2e6;text-align:left;">Producto (nombre proveedor)</th>'
            . '<th style="padding:8px;border:1px solid #dee2e6;">Cantidad</th>'
            . '<th style="padding:8px;border:1px solid #dee2e6;text-align:right;">Precio unit.</th>'
            . '<th style="padding:8px;border:1px solid #dee2e6;text-align:right;">Subtotal</th>'
            . '</tr></thead><tbody>' . $filas_html . '</tbody>'
            . '<tfoot><tr><td colspan="3" style="padding:8px;border:1px solid #dee2e6;text-align:right;"><strong>Total (IVA incl.):</strong></td>'
            . '<td style="padding:8px;border:1px solid #dee2e6;text-align:right;"><strong>$' . $total_fmt . '</strong></td></tr></tfoot>'
            . '</table>'
            . '<p><strong>Fecha de orden:</strong> ' . $fecha_fmt . '<br>'
            . '<strong>Fecha de entrega estimada:</strong> ' . $entrega . '</p>'
            . ($orden->observaciones ? '<p><strong>Observaciones:</strong> ' . nl2br(htmlspecialchars($orden->observaciones, ENT_QUOTES, 'UTF-8')) . '</p>' : '')
            . '<p>Quedamos atentos a su confirmación.</p>'
            . '<p>Saludos cordiales,<br><strong>' . htmlspecialchars($nombre_empresa, ENT_QUOTES, 'UTF-8') . '</strong></p>'
            . '</div>';

        $cuerpo_texto = "Estimado proveedor {$orden->razon_social},\n\n"
            . "Solicitamos el suministro de los siguientes productos (OC {$orden->folio}):\n\n"
            . $filas_texto
            . "\nTotal (IVA incl.): \${$total_fmt}\n"
            . "Fecha de orden: {$fecha_fmt}\n"
            . "Fecha de entrega estimada: {$entrega}\n\n"
            . "Saludos cordiales,\n{$nombre_empresa}";

        return [
            'success'      => true,
            'destinatario' => $destinatario,
            'asunto'       => $asunto,
            'cuerpo_html'  => $cuerpo_html,
            'cuerpo_texto' => $cuerpo_texto,
            'folio'        => $orden->folio,
            'proveedor'    => $orden->razon_social,
        ];
    }

    public function get_comentarios($orden_id) {
        $this->db->select('c.*, CONCAT(a.nombre, " ", a.apellidos) AS autor_nombre', false);
        $this->db->from('ordenes_compra_comentarios c');
        $this->db->join('administradores a', 'a.id = c.creado_por', 'left');
        $this->db->where('c.orden_compra_id', (int) $orden_id);
        $this->db->order_by('c.creado_en', 'DESC');
        return $this->db->get()->result();
    }

    public function agregar_comentario($orden_id, $comentario, $usuario_id) {
        return $this->db->insert('ordenes_compra_comentarios', [
            'orden_compra_id' => (int) $orden_id,
            'comentario'      => $comentario,
            'creado_por'      => $usuario_id ? (int) $usuario_id : null,
            'creado_en'       => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_documentos($orden_id) {
        $this->db->from('ordenes_compra_documentos');
        $this->db->where('orden_compra_id', (int) $orden_id);
        $this->db->order_by('fecha_subida', 'DESC');
        return $this->db->get()->result();
    }

    public function agregar_documento($orden_id, $data) {
        $data['orden_compra_id'] = (int) $orden_id;
        $data['fecha_subida'] = date('Y-m-d H:i:s');
        return $this->db->insert('ordenes_compra_documentos', $data);
    }

    public function get_documento($id) {
        return $this->db->where('id', (int) $id)->get('ordenes_compra_documentos')->row();
    }

    public function eliminar_documento($id) {
        return $this->db->where('id', (int) $id)->delete('ordenes_compra_documentos');
    }

    /**
     * Recalcula monto pagado, saldo y estatus de pago de una OC.
     */
    public function actualizar_estatus_pago($orden_id) {
        $orden = $this->db->where('id', (int) $orden_id)->get($this->tableName)->row();
        if (!$orden) {
            return false;
        }

        if (in_array($orden->estatus, ['Borrador', 'Cancelada'], true) || (float) $orden->total <= 0) {
            $this->db->where('id', $orden_id);
            return $this->db->update($this->tableName, [
                'monto_pagado' => 0,
                'saldo_pendiente' => 0,
                'estatus_pago' => 'Sin adeudo',
            ]);
        }

        $row = $this->db->select('COALESCE(SUM(monto), 0) AS total_pagado', false)
            ->where('orden_compra_id', (int) $orden_id)
            ->get('pagos_ordenes_compra')
            ->row();

        $pagado = (float) ($row->total_pagado ?? 0);
        $total = (float) $orden->total;
        $saldo = max(0, round($total - $pagado, 2));

        if ($saldo <= 0 && $total > 0) {
            $estatus_pago = 'Pagado';
        } elseif ($pagado > 0) {
            $estatus_pago = 'Parcial';
        } else {
            $estatus_pago = 'Pendiente';
        }

        $this->db->where('id', $orden_id);
        return $this->db->update($this->tableName, [
            'monto_pagado' => $pagado,
            'saldo_pendiente' => $saldo,
            'estatus_pago' => $estatus_pago,
        ]);
    }

    public function get_pagos($orden_id) {
        $this->db->from('pagos_ordenes_compra');
        $this->db->where('orden_compra_id', (int) $orden_id);
        $this->db->order_by('fecha_pago', 'DESC');
        return $this->db->get()->result();
    }

    public function generar_folio_pago() {
        $anio = date('Y');
        $this->db->select('folio');
        $this->db->from('pagos_ordenes_compra');
        $this->db->like('folio', 'PAGC-' . $anio . '-', 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $ultimo = $this->db->get()->row();

        $num = 0;
        if ($ultimo && preg_match('/PAGC-' . $anio . '-(\d+)/', $ultimo->folio, $m)) {
            $num = (int) $m[1];
        }

        return 'PAGC-' . $anio . '-' . str_pad($num + 1, 4, '0', STR_PAD_LEFT);
    }

    public function registrar_pago($orden_id, $data) {
        $orden = $this->get_orden($orden_id);
        if (!$orden) {
            return ['success' => false, 'message' => 'Orden no encontrada'];
        }
        if (in_array($orden->estatus, ['Borrador', 'Cancelada'], true)) {
            return ['success' => false, 'message' => 'No se pueden registrar pagos en órdenes en Borrador o Cancelada'];
        }

        $monto = round((float) ($data['monto'] ?? 0), 2);
        if ($monto <= 0) {
            return ['success' => false, 'message' => 'El monto debe ser mayor a cero'];
        }

        $saldo = (float) ($orden->saldo_pendiente ?? $orden->total);
        if ($monto > $saldo + 0.01) {
            return ['success' => false, 'message' => 'El monto excede el saldo pendiente ($' . number_format($saldo, 2) . ')'];
        }

        $insert = [
            'orden_compra_id' => (int) $orden_id,
            'folio' => $this->generar_folio_pago(),
            'fecha_pago' => $data['fecha_pago'] ?? date('Y-m-d'),
            'monto' => $monto,
            'metodo_pago' => $data['metodo_pago'] ?? 'Transferencia',
            'referencia' => $data['referencia'] ?? null,
            'notas' => $data['notas'] ?? null,
            'registrado_por' => $data['registrado_por'] ?? null,
        ];

        $ok = $this->db->insert('pagos_ordenes_compra', $insert);
        if (!$ok) {
            return ['success' => false, 'message' => 'Error al registrar el pago'];
        }

        $this->actualizar_estatus_pago($orden_id);
        return [
            'success' => true,
            'message' => 'Pago registrado correctamente',
            'folio' => $insert['folio'],
            'orden' => $this->get_orden($orden_id),
        ];
    }

    public function marcar_pagado_completo($orden_id, $data = []) {
        $orden = $this->get_orden($orden_id);
        if (!$orden) {
            return ['success' => false, 'message' => 'Orden no encontrada'];
        }

        $saldo = (float) ($orden->saldo_pendiente ?? 0);
        if ($saldo <= 0) {
            return ['success' => false, 'message' => 'Esta orden no tiene saldo pendiente'];
        }

        return $this->registrar_pago($orden_id, [
            'monto' => $saldo,
            'fecha_pago' => $data['fecha_pago'] ?? date('Y-m-d'),
            'metodo_pago' => $data['metodo_pago'] ?? ($orden->forma_pago ?: 'Transferencia'),
            'referencia' => $data['referencia'] ?? 'Pago total',
            'notas' => $data['notas'] ?? 'Marcado como pagado',
            'registrado_por' => $data['registrado_por'] ?? null,
        ]);
    }
}
