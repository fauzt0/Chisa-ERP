<?php
/**
 * CotizacionesModel - Cotizaciones de compra a proveedores
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class CotizacionesModel extends MY_Model {

    protected $tableName = 'cotizaciones';
    protected $statusField = 'activo';

    protected $datatableConfig = [
        'table' => 'cotizaciones',
        'column_order' => ['cotizaciones.folio', 'cotizaciones.grupo_folio', 'proveedores.razon_social', 'cotizaciones.fecha_solicitud', 'cotizaciones.total', 'cotizaciones.estatus', null],
        'column_search' => ['cotizaciones.folio', 'cotizaciones.grupo_folio', 'proveedores.razon_social', 'proveedores.rfc'],
        'order' => ['fecha_solicitud' => 'DESC']
    ];

    public function __construct() {
        parent::__construct();
        $this->load->model('Compras/OrdenesCompraModel');
    }

    protected function _get_datatables_query() {
        $this->db->select('cotizaciones.*, proveedores.razon_social, proveedores.nombre_comercial');
        $this->db->from($this->tableName);
        $this->db->join('proveedores', 'proveedores.id = cotizaciones.proveedor_id', 'left');
        $this->db->where('cotizaciones.activo', 1);

        if (!empty($_POST['filtro_estatus'])) {
            $this->db->where('cotizaciones.estatus', $_POST['filtro_estatus']);
        }
        if (!empty($_POST['filtro_grupo'])) {
            $this->db->where('cotizaciones.grupo_folio', $_POST['filtro_grupo']);
        }

        $i = 0;
        if (isset($_POST['search']['value']) && $_POST['search']['value'] !== '') {
            foreach ($this->datatableConfig['column_search'] as $column) {
                if ($i === 0) {
                    $this->db->group_start();
                    $this->db->like($column, $_POST['search']['value']);
                } else {
                    $this->db->or_like($column, $_POST['search']['value']);
                }
                if (count($this->datatableConfig['column_search']) - 1 === $i) {
                    $this->db->group_end();
                }
                $i++;
            }
        }

        if (isset($_POST['order'])) {
            $column_index = (int) $_POST['order'][0]['column'];
            $column_name = $this->datatableConfig['column_order'][$column_index];
            if ($column_name) {
                $this->db->order_by($column_name, $_POST['order'][0]['dir']);
            }
        } elseif (isset($this->datatableConfig['order'])) {
            $order = $this->datatableConfig['order'];
            $this->db->order_by('cotizaciones.' . key($order), $order[key($order)]);
        }
    }

    public function get_datatables() {
        $this->_get_datatables_query();
        if (isset($_POST['length']) && $_POST['length'] !== -1) {
            $this->db->limit((int) $_POST['length'], (int) $_POST['start']);
        }
        return $this->db->get()->result();
    }

    public function count_filtered() {
        $this->_get_datatables_query();
        return $this->db->get()->num_rows();
    }

    public function count_all($where = []) {
        $this->db->where('activo', 1);
        if (!empty($where)) {
            $this->db->where($where);
        }
        return $this->db->count_all_results($this->tableName);
    }

    public function get_cotizacion($id) {
        $this->db->select('cotizaciones.*, proveedores.razon_social, proveedores.nombre_comercial, proveedores.rfc');
        $this->db->from($this->tableName);
        $this->db->join('proveedores', 'proveedores.id = cotizaciones.proveedor_id', 'left');
        $this->db->where('cotizaciones.id', $id);
        $this->db->where('cotizaciones.activo', 1);
        $cot = $this->db->get()->row();
        if ($cot) {
            $cot->detalles = $this->get_detalles($id);
        }
        return $cot;
    }

    public function get_detalles($cotizacion_id) {
        $this->db->select('cotizaciones_detalle.*, insumos.codigo, insumos.nombre_tecnico, insumos.unidad_medida');
        $this->db->from('cotizaciones_detalle');
        $this->db->join('insumos', 'insumos.id = cotizaciones_detalle.insumo_id');
        $this->db->where('cotizaciones_detalle.cotizacion_id', $cotizacion_id);
        $this->db->order_by('cotizaciones_detalle.id', 'ASC');
        return $this->db->get()->result();
    }

    public function generar_folio() {
        $prefijo = 'COT-' . date('Y') . '-';
        $pos = strlen($prefijo) + 1;

        $row = $this->db
            ->select('MAX(CAST(SUBSTRING(folio, ' . $pos . ') AS UNSIGNED)) AS max_folio', false)
            ->from($this->tableName)
            ->like('folio', $prefijo, 'after')
            ->get()->row();

        $numero = ($row && $row->max_folio !== null) ? (int) $row->max_folio + 1 : 1;

        return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    public function generar_grupo_folio() {
        $prefijo = 'GRP-COT-' . date('Y') . '-';
        $pos = strlen($prefijo) + 1;

        $row = $this->db
            ->select('MAX(CAST(SUBSTRING(grupo_folio, ' . $pos . ') AS UNSIGNED)) AS max_folio', false)
            ->from($this->tableName)
            ->like('grupo_folio', $prefijo, 'after')
            ->get()->row();

        $numero = ($row && $row->max_folio !== null) ? (int) $row->max_folio + 1 : 1;

        return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    public function crear_cotizacion($cabecera, $detalles, $grupo_folio = null) {
        if (empty($cabecera['folio'])) {
            $cabecera['folio'] = $this->generar_folio();
        }
        if ($grupo_folio) {
            $cabecera['grupo_folio'] = $grupo_folio;
        }
        $cabecera['fecha_solicitud'] = $cabecera['fecha_solicitud'] ?? date('Y-m-d');
        $cabecera['estatus'] = $cabecera['estatus'] ?? 'Pendiente';
        $cabecera['activo'] = 1;
        $cabecera['fecha_creacion'] = date('Y-m-d H:i:s');

        $this->db->insert($this->tableName, $cabecera);
        $cotizacion_id = $this->db->insert_id();
        if (!$cotizacion_id) {
            return false;
        }

        foreach ($detalles as $det) {
            $this->agregar_detalle($cotizacion_id, $det);
        }

        return $cotizacion_id;
    }

    public function crear_solicitud($proveedores_ids, $insumos, $observaciones, $user_id) {
        if (empty($proveedores_ids) || empty($insumos)) {
            return ['success' => false, 'message' => 'Seleccione al menos un proveedor y un insumo'];
        }

        $grupo_folio = $this->generar_grupo_folio();
        $cotizacion_ids = [];

        $this->db->trans_start();

        foreach ($proveedores_ids as $proveedor_id) {
            $cot_id = $this->crear_cotizacion([
                'proveedor_id' => (int) $proveedor_id,
                'observaciones' => $observaciones,
                'creado_por' => $user_id,
            ], $insumos, $grupo_folio);

            if (!$cot_id) {
                $this->db->trans_complete();
                return ['success' => false, 'message' => 'Error al crear cotización para proveedor #' . $proveedor_id];
            }
            $cotizacion_ids[] = $cot_id;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['success' => false, 'message' => 'Error de base de datos al crear la solicitud'];
        }

        return [
            'success' => true,
            'message' => count($cotizacion_ids) . ' cotizaciones creadas en grupo ' . $grupo_folio,
            'grupo_folio' => $grupo_folio,
            'cotizacion_ids' => $cotizacion_ids,
        ];
    }

    public function agregar_detalle($cotizacion_id, $data) {
        $cantidad = (float) ($data['cantidad'] ?? 0);
        $precio = (float) ($data['precio_unitario'] ?? 0);
        $row = [
            'cotizacion_id' => $cotizacion_id,
            'insumo_id' => (int) $data['insumo_id'],
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $cantidad * $precio,
        ];
        $ok = $this->db->insert('cotizaciones_detalle', $row);
        if ($ok) {
            $this->recalcular_total($cotizacion_id);
        }
        return $ok;
    }

    public function actualizar_detalle($id, $data) {
        if (isset($data['cantidad']) || isset($data['precio_unitario'])) {
            $det = $this->db->where('id', $id)->get('cotizaciones_detalle')->row();
            if ($det) {
                $cantidad = isset($data['cantidad']) ? (float) $data['cantidad'] : (float) $det->cantidad;
                $precio = isset($data['precio_unitario']) ? (float) $data['precio_unitario'] : (float) $det->precio_unitario;
                $data['subtotal'] = $cantidad * $precio;
            }
        }
        $this->db->where('id', $id);
        $ok = $this->db->update('cotizaciones_detalle', $data);
        if ($ok) {
            $det = $this->db->where('id', $id)->get('cotizaciones_detalle')->row();
            if ($det) {
                $this->recalcular_total($det->cotizacion_id);
            }
        }
        return $ok;
    }

    public function eliminar_detalle($id) {
        $det = $this->db->where('id', $id)->get('cotizaciones_detalle')->row();
        if (!$det) {
            return false;
        }
        $this->db->where('id', $id);
        $ok = $this->db->delete('cotizaciones_detalle');
        if ($ok) {
            $this->recalcular_total($det->cotizacion_id);
        }
        return $ok;
    }

    public function recalcular_total($cotizacion_id) {
        $this->db->select_sum('subtotal', 'total');
        $this->db->where('cotizacion_id', $cotizacion_id);
        $row = $this->db->get('cotizaciones_detalle')->row();
        $total = (float) ($row->total ?? 0);
        $this->db->where('id', $cotizacion_id);
        $this->db->update($this->tableName, ['total' => $total]);
    }

    public function actualizar_cotizacion($id, $data) {
        $this->db->where('id', $id);
        $this->db->where('activo', 1);
        return $this->db->update($this->tableName, $data);
    }

    public function eliminar_cotizacion($id) {
        $cot = $this->get_cotizacion($id);
        if (!$cot) {
            return ['success' => false, 'message' => 'Cotización no encontrada'];
        }
        if (in_array($cot->estatus, ['Aprobada'], true)) {
            return ['success' => false, 'message' => 'No se puede eliminar una cotización aprobada'];
        }
        $this->db->where('id', $id);
        $ok = $this->db->update($this->tableName, ['activo' => 0]);
        return ['success' => (bool) $ok, 'message' => $ok ? 'Cotización eliminada' : 'Error al eliminar'];
    }

    public function marcar_recibida($id, $detalles_precios = []) {
        $cot = $this->get_cotizacion($id);
        if (!$cot) {
            return ['success' => false, 'message' => 'Cotización no encontrada'];
        }
        if ($cot->estatus === 'Aprobada') {
            return ['success' => false, 'message' => 'La cotización ya fue aprobada'];
        }

        $this->db->trans_start();

        foreach ($detalles_precios as $det_id => $precio) {
            $this->actualizar_detalle((int) $det_id, ['precio_unitario' => (float) $precio]);
        }

        $this->db->where('id', $id);
        $this->db->update($this->tableName, [
            'estatus' => 'Recibida',
            'fecha_respuesta' => date('Y-m-d'),
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['success' => false, 'message' => 'Error al marcar como recibida'];
        }

        return ['success' => true, 'message' => 'Cotización marcada como recibida'];
    }

    public function rechazar($id) {
        $cot = $this->get_cotizacion($id);
        if (!$cot) {
            return ['success' => false, 'message' => 'Cotización no encontrada'];
        }
        if ($cot->estatus === 'Aprobada') {
            return ['success' => false, 'message' => 'No se puede rechazar una cotización aprobada'];
        }
        $this->db->where('id', $id);
        $ok = $this->db->update($this->tableName, [
            'estatus' => 'Rechazada',
            'fecha_respuesta' => date('Y-m-d'),
        ]);
        return ['success' => (bool) $ok, 'message' => $ok ? 'Cotización rechazada' : 'Error al rechazar'];
    }

    public function aprobar($cotizacion_id, $user_id) {
        $cot = $this->get_cotizacion($cotizacion_id);
        if (!$cot) {
            return ['success' => false, 'message' => 'Cotización no encontrada'];
        }
        if ($cot->estatus === 'Aprobada') {
            return ['success' => false, 'message' => 'La cotización ya fue aprobada'];
        }
        if ($cot->estatus === 'Rechazada') {
            return ['success' => false, 'message' => 'No se puede aprobar una cotización rechazada'];
        }
        if (empty($cot->detalles)) {
            return ['success' => false, 'message' => 'La cotización no tiene insumos'];
        }

        $this->db->trans_start();

        $orden_data = [
            'proveedor_id' => $cot->proveedor_id,
            'fecha_orden' => date('Y-m-d'),
            'observaciones' => 'Generada desde cotización ' . $cot->folio . ($cot->grupo_folio ? ' (grupo ' . $cot->grupo_folio . ')' : ''),
            'creado_por' => $user_id,
            'origen' => 'Compras',
            'origen_tipo' => 'cotizacion',
        ];
        $this->OrdenesCompraModel->crear_orden($orden_data);
        $orden_id = $this->db->insert_id();

        foreach ($cot->detalles as $det) {
            $precio = (float) $det->precio_unitario;
            if ($precio <= 0) {
                $this->db->where('insumo_id', $det->insumo_id);
                $this->db->where('proveedor_id', $cot->proveedor_id);
                $rel = $this->db->get('proveedor_insumo')->row();
                if ($rel && $rel->precio_compra > 0) {
                    $precio = (float) $rel->precio_compra;
                }
            }
            $this->OrdenesCompraModel->agregar_detalle($orden_id, [
                'insumo_id' => $det->insumo_id,
                'nombre_proveedor' => $det->nombre_tecnico,
                'codigo_proveedor' => $det->codigo,
                'cantidad_solicitada' => $det->cantidad,
                'precio_unitario' => $precio,
            ]);
        }

        $this->db->where('id', $cotizacion_id);
        $this->db->update($this->tableName, [
            'estatus' => 'Aprobada',
            'orden_compra_id' => $orden_id,
            'fecha_respuesta' => date('Y-m-d'),
        ]);

        if ($cot->grupo_folio) {
            $this->db->where('grupo_folio', $cot->grupo_folio);
            $this->db->where('id !=', $cotizacion_id);
            $this->db->where_in('estatus', ['Pendiente', 'Recibida']);
            $this->db->update($this->tableName, [
                'estatus' => 'Rechazada',
                'fecha_respuesta' => date('Y-m-d'),
            ]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return ['success' => false, 'message' => 'Error al generar la orden de compra'];
        }

        $orden = $this->OrdenesCompraModel->get_orden($orden_id);
        return [
            'success' => true,
            'message' => 'Orden de compra ' . $orden->folio . ' creada en Borrador',
            'orden_compra_id' => $orden_id,
            'orden_folio' => $orden->folio,
        ];
    }

    public function get_comparacion($grupo_folio) {
        $this->db->select('cotizaciones.*, proveedores.razon_social, proveedores.nombre_comercial');
        $this->db->from($this->tableName);
        $this->db->join('proveedores', 'proveedores.id = cotizaciones.proveedor_id', 'left');
        $this->db->where('cotizaciones.grupo_folio', $grupo_folio);
        $this->db->where('cotizaciones.activo', 1);
        $this->db->order_by('proveedores.razon_social', 'ASC');
        $cotizaciones = $this->db->get()->result();

        if (empty($cotizaciones)) {
            return null;
        }

        $proveedores = [];
        foreach ($cotizaciones as $cot) {
            $proveedores[] = [
                'proveedor_id' => (int) $cot->proveedor_id,
                'nombre' => $cot->razon_social,
                'cotizacion_id' => (int) $cot->id,
                'folio' => $cot->folio,
                'estatus' => $cot->estatus,
                'total' => (float) $cot->total,
                'orden_compra_id' => $cot->orden_compra_id,
            ];
        }

        $insumos_map = [];
        foreach ($cotizaciones as $cot) {
            $detalles = $this->get_detalles($cot->id);
            foreach ($detalles as $det) {
                $key = (int) $det->insumo_id;
                if (!isset($insumos_map[$key])) {
                    $insumos_map[$key] = [
                        'insumo_id' => $key,
                        'codigo' => $det->codigo,
                        'nombre' => $det->nombre_tecnico,
                        'unidad_medida' => $det->unidad_medida,
                        'cantidad' => (float) $det->cantidad,
                        'precios' => [],
                    ];
                }
                $insumos_map[$key]['precios'][$cot->proveedor_id] = [
                    'precio_unitario' => (float) $det->precio_unitario,
                    'subtotal' => (float) $det->subtotal,
                    'detalle_id' => (int) $det->id,
                    'cotizacion_id' => (int) $cot->id,
                ];
            }
        }

        $insumos = [];
        foreach ($insumos_map as $insumo) {
            $mejor_precio = null;
            $mejor_proveedor_id = null;
            foreach ($insumo['precios'] as $prov_id => $pdata) {
                $p = $pdata['precio_unitario'];
                if ($p > 0 && ($mejor_precio === null || $p < $mejor_precio)) {
                    $mejor_precio = $p;
                    $mejor_proveedor_id = $prov_id;
                }
            }
            $insumo['mejor_precio'] = $mejor_precio;
            $insumo['mejor_proveedor_id'] = $mejor_proveedor_id;
            $insumos[] = $insumo;
        }

        return [
            'grupo_folio' => $grupo_folio,
            'proveedores' => $proveedores,
            'insumos' => $insumos,
            'cotizaciones' => $cotizaciones,
        ];
    }

    public function get_grupos_recientes($limit = 50) {
        $this->db->select('grupo_folio, MIN(fecha_solicitud) AS fecha_solicitud, COUNT(*) AS total_cotizaciones');
        $this->db->from($this->tableName);
        $this->db->where('activo', 1);
        $this->db->where('grupo_folio IS NOT NULL');
        $this->db->where('grupo_folio !=', '');
        $this->db->group_by('grupo_folio');
        $this->db->order_by('fecha_solicitud', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    public function get_estadisticas() {
        $stats = [];
        $this->db->where('activo', 1);
        $stats['total'] = $this->db->count_all_results($this->tableName);

        $this->db->where('activo', 1);
        $this->db->where('estatus', 'Pendiente');
        $stats['pendientes'] = $this->db->count_all_results($this->tableName);

        $this->db->where('activo', 1);
        $this->db->where('estatus', 'Recibida');
        $stats['recibidas'] = $this->db->count_all_results($this->tableName);

        $this->db->where('activo', 1);
        $this->db->where('estatus', 'Aprobada');
        $stats['aprobadas'] = $this->db->count_all_results($this->tableName);

        return $stats;
    }

    public function actualizar_archivo($id, $nombre, $ruta) {
        $this->db->where('id', $id);
        return $this->db->update($this->tableName, [
            'archivo_nombre' => $nombre,
            'archivo_ruta' => $ruta,
        ]);
    }
}
