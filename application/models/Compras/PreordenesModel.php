<?php
/**
 * PreordenesModel - Modelo de gestión de pre-órdenes de compra
 *
 * Flujo (Iteración 4 - Pre-órdenes Automáticas Producción → Compras):
 *   1. Producción detecta insumos faltantes al calcular una formulación/proyecto
 *      y genera pre-órdenes con estatus 'Pendiente' (tabla `preordenes`).
 *   2. Un administrador de Compras autoriza o rechaza cada pre-orden.
 *   3. Al autorizar, se genera una Orden de Compra real (`ordenes_compra`) en
 *      estatus 'Borrador' — TODAVÍA requiere que Compras la revise/edite y la
 *      envíe manualmente al proveedor (cambio de estatus a 'Enviada'), por lo
 *      que la autorización de admin es un paso previo obligatorio y separado
 *      del envío al proveedor.
 *
 * @extends MY_Model
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class PreordenesModel extends MY_Model {

    protected $tableName = 'preordenes';

    public function __construct() {
        parent::__construct();
        $this->load->model('Compras/OrdenesCompraModel');
        $this->load->model('Compras/InsumosModel');
    }

    /**
     * Crea una única pre-orden.
     */
    public function crear_preorden($data) {
        $data['fecha_solicitud'] = date('Y-m-d H:i:s');
        $data['estatus'] = 'Pendiente';

        $ok = $this->db->insert($this->tableName, $data);
        return $ok ? $this->db->insert_id() : null;
    }

    /**
     * Crea pre-órdenes a partir de la lista de insumos faltantes calculada por
     * ProductosModel::calcular_insumos_para_proyecto().
     *
     * @param array  $insumos_faltantes  Cada item: insumo_id, cantidad_faltante, unidad_insumo
     * @param string $origen_tipo        'produccion' | 'venta' | 'obra' | 'interno'
     * @param int    $origen_id          ID de referencia del origen (ej. formulacion_id)
     * @param int    $usuario_id         Usuario que solicita
     * @param string $notas              Notas opcionales (ej. nombre del producto/proyecto)
     * @return array Resultado con folios creados y posibles errores por insumo
     */
    public function crear_preordenes_desde_faltantes($insumos_faltantes, $origen_tipo, $origen_id, $usuario_id, $notas = null) {
        $creadas = [];
        $errores = [];

        if (empty($insumos_faltantes)) {
            return ['success' => false, 'message' => 'No se recibieron insumos faltantes', 'creadas' => [], 'errores' => []];
        }

        $this->db->trans_start();

        foreach ($insumos_faltantes as $item) {
            $insumo_id = $item['insumo_id'] ?? null;
            $cantidad  = isset($item['cantidad_faltante']) ? (float) $item['cantidad_faltante'] : 0;
            $unidad    = $item['unidad_insumo'] ?? null;

            if (!$insumo_id || $cantidad <= 0 || !$unidad) {
                $errores[] = "Insumo ID {$insumo_id}: datos incompletos, se omitió";
                continue;
            }

            // Evitar duplicar pre-orden pendiente para el mismo insumo + mismo origen
            $existente = $this->db->where('insumo_id', $insumo_id)
                                   ->where('origen_tipo', $origen_tipo)
                                   ->where('origen_id', $origen_id)
                                   ->where('estatus', 'Pendiente')
                                   ->get($this->tableName)->row();
            if ($existente) {
                $errores[] = "Insumo ID {$insumo_id}: ya existe una pre-orden pendiente ({$existente->folio}) para este origen";
                continue;
            }

            // Proveedor sugerido: el de menor precio_compra activo para este insumo
            $proveedores = $this->InsumosModel->get_proveedores_por_insumo($insumo_id);
            $proveedor_sugerido_id = !empty($proveedores) ? $proveedores[0]->id : null;

            $preorden_id = $this->crear_preorden([
                'origen_tipo'           => $origen_tipo,
                'origen_id'             => $origen_id,
                'insumo_id'             => $insumo_id,
                'cantidad_solicitada'   => $cantidad,
                'unidad'                => $unidad,
                'proveedor_sugerido_id' => $proveedor_sugerido_id,
                'usuario_solicita_id'   => $usuario_id,
                'notas'                 => $notas,
            ]);

            if ($preorden_id) {
                $creadas[] = $this->get_preorden($preorden_id);
            } else {
                $errores[] = "Insumo ID {$insumo_id}: error al insertar la pre-orden";
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Error de base de datos al generar pre-órdenes', 'creadas' => [], 'errores' => $errores];
        }

        return [
            'success' => count($creadas) > 0,
            'message' => count($creadas) . ' pre-orden(es) generada(s) correctamente',
            'creadas' => $creadas,
            'errores' => $errores,
        ];
    }

    /**
     * Obtiene una pre-orden con datos de insumo y proveedor sugerido.
     */
    public function get_preorden($id) {
        $this->db->select('
            preordenes.*,
            insumos.codigo AS insumo_codigo,
            insumos.nombre_tecnico AS insumo_nombre,
            insumos.unidad_medida AS insumo_unidad_medida,
            insumos.stock_actual AS insumo_stock_actual,
            insumos.precio_promedio AS insumo_precio_promedio,
            proveedores.razon_social AS proveedor_sugerido_nombre
        ');
        $this->db->from($this->tableName);
        $this->db->join('insumos', 'insumos.id = preordenes.insumo_id', 'left');
        $this->db->join('proveedores', 'proveedores.id = preordenes.proveedor_sugerido_id', 'left');
        $this->db->where('preordenes.id', $id);
        return $this->db->get()->row();
    }

    /**
     * Lista de pre-órdenes con filtro opcional de estatus, para DataTables o dashboard.
     */
    public function listar($estatus = null, $limite = 50) {
        $this->db->select('
            preordenes.*,
            insumos.codigo AS insumo_codigo,
            insumos.nombre_tecnico AS insumo_nombre,
            proveedores.razon_social AS proveedor_sugerido_nombre
        ');
        $this->db->from($this->tableName);
        $this->db->join('insumos', 'insumos.id = preordenes.insumo_id', 'left');
        $this->db->join('proveedores', 'proveedores.id = preordenes.proveedor_sugerido_id', 'left');

        if ($estatus) {
            $this->db->where('preordenes.estatus', $estatus);
        }

        $this->db->order_by('preordenes.fecha_solicitud', 'DESC');
        $this->db->limit($limite);
        return $this->db->get()->result();
    }

    /**
     * Cuenta pre-órdenes pendientes de autorización (para notificaciones/badges).
     */
    public function contar_pendientes() {
        $this->db->where('estatus', 'Pendiente');
        return $this->db->count_all_results($this->tableName);
    }

    /**
     * Autoriza una pre-orden: genera la Orden de Compra real (en 'Borrador',
     * aún requiere que Compras la envíe manualmente al proveedor) y marca la
     * pre-orden como 'Convertida'.
     *
     * @param int   $preorden_id
     * @param int   $usuario_id        Usuario (admin de Compras) que autoriza
     * @param float $cantidad_aprobada Cantidad aprobada (si difiere de la solicitada)
     * @param int   $proveedor_id      Proveedor a usar (si difiere del sugerido)
     */
    public function aprobar($preorden_id, $usuario_id, $cantidad_aprobada = null, $proveedor_id = null) {
        $preorden = $this->get_preorden($preorden_id);
        if (!$preorden) {
            return ['success' => false, 'message' => 'Pre-orden no encontrada'];
        }
        if ($preorden->estatus !== 'Pendiente') {
            return ['success' => false, 'message' => 'Solo se pueden autorizar pre-órdenes en estatus Pendiente'];
        }

        $proveedor_final_id = $proveedor_id ?: $preorden->proveedor_sugerido_id;
        if (!$proveedor_final_id) {
            return ['success' => false, 'message' => 'No hay proveedor sugerido ni seleccionado; asigna un proveedor antes de autorizar'];
        }

        $cantidad_final = $cantidad_aprobada !== null && $cantidad_aprobada > 0
            ? (float) $cantidad_aprobada
            : (float) $preorden->cantidad_solicitada;

        // Precio: el pactado con ese proveedor si existe, si no el precio promedio del insumo
        $precio_unitario = $preorden->insumo_precio_promedio ?: 0;
        $this->db->where('insumo_id', $preorden->insumo_id);
        $this->db->where('proveedor_id', $proveedor_final_id);
        $rel = $this->db->get('proveedor_insumo')->row();
        if ($rel && $rel->precio_compra > 0) {
            $precio_unitario = $rel->precio_compra;
        }

        $this->db->trans_start();

        $orden_data = [
            'proveedor_id'   => $proveedor_final_id,
            'fecha_orden'    => date('Y-m-d'),
            'observaciones'  => 'Generada desde pre-orden ' . $preorden->folio . ' (origen: ' . $preorden->origen_tipo . ')',
            'creado_por'     => $usuario_id,
            'origen'         => 'Produccion',
            'origen_tipo'    => $preorden->origen_tipo,
            'preorden_id'    => $preorden_id,
        ];
        $this->OrdenesCompraModel->crear_orden($orden_data);
        $orden_compra_id = $this->db->insert_id();

        $this->OrdenesCompraModel->agregar_detalle($orden_compra_id, [
            'insumo_id'           => $preorden->insumo_id,
            'nombre_proveedor'    => $preorden->insumo_nombre,
            'codigo_proveedor'    => $preorden->insumo_codigo,
            'cantidad_solicitada' => $cantidad_final,
            'precio_unitario'     => $precio_unitario,
        ]);

        $this->db->where('id', $preorden_id);
        $this->db->update($this->tableName, [
            'estatus'            => 'Convertida',
            'cantidad_aprobada'  => $cantidad_final,
            'orden_compra_id'    => $orden_compra_id,
            'usuario_aprueba_id' => $usuario_id,
            'fecha_respuesta'    => date('Y-m-d H:i:s'),
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Error de base de datos al autorizar la pre-orden'];
        }

        return [
            'success' => true,
            'message' => 'Pre-orden autorizada. Orden de compra ' . $this->OrdenesCompraModel->get_orden($orden_compra_id)->folio . ' creada en Borrador.',
            'orden_compra_id' => $orden_compra_id,
        ];
    }

    /**
     * Rechaza una pre-orden pendiente.
     */
    public function rechazar($preorden_id, $usuario_id, $motivo) {
        $preorden = $this->get_by_id_raw($preorden_id);
        if (!$preorden) {
            return ['success' => false, 'message' => 'Pre-orden no encontrada'];
        }
        if ($preorden->estatus !== 'Pendiente') {
            return ['success' => false, 'message' => 'Solo se pueden rechazar pre-órdenes en estatus Pendiente'];
        }

        $this->db->where('id', $preorden_id);
        $result = $this->db->update($this->tableName, [
            'estatus'            => 'Rechazada',
            'motivo_rechazo'     => $motivo,
            'usuario_aprueba_id' => $usuario_id,
            'fecha_respuesta'    => date('Y-m-d H:i:s'),
        ]);

        return ['success' => (bool) $result, 'message' => $result ? 'Pre-orden rechazada' : 'Error al rechazar la pre-orden'];
    }

    /**
     * Obtiene el registro crudo de preordenes (sin joins) por ID.
     */
    private function get_by_id_raw($id) {
        return $this->db->where('id', $id)->get($this->tableName)->row();
    }
}
