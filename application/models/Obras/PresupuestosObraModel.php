<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PresupuestosObraModel - Presupuestos/cotizaciones de VENTA (entidad nueva).
 * NO usa las tablas `cotizaciones`/`cotizaciones_detalle` (esas son de COMPRAS).
 */
class PresupuestosObraModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Folio PRES-##### replicando EXACTAMENTE ObrasModel::generar_folio().
     * Evita la colisión de folios no numéricos con CAST(SUBSTRING(folio, 6)).
     */
    public function generar_folio() {
        $row = $this->db
            ->select('MAX(CAST(SUBSTRING(folio, 6) AS UNSIGNED)) AS max_folio', false)
            ->from('presupuestos_obra')
            ->like('folio', 'PRES-', 'after')
            ->get()->row();

        $nuevo_numero = ($row && $row->max_folio !== null) ? (int) $row->max_folio + 1 : 1;

        return 'PRES-' . str_pad($nuevo_numero, 5, '0', STR_PAD_LEFT);
    }

    public function crear(array $data) {
        $this->asegurar_infraestructura();
        $data['folio'] = $this->generar_folio();
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        $data['activo'] = 1;
        $data['estatus'] = $data['estatus'] ?? 'Borrador';
        $data['tipo'] = $data['tipo'] ?? 'Presupuesto';
        $data['iva_porcentaje'] = $data['iva_porcentaje'] ?? 16.00;
        $data['descuento_porcentaje'] = $data['descuento_porcentaje'] ?? 0.00;

        $this->db->insert('presupuestos_obra', $data);
        $id = (int) $this->db->insert_id();
        if ($id) {
            $this->recalcular_totales($id);
        }
        return ['success' => (bool) $id, 'id' => $id, 'folio' => $data['folio'], 'message' => $id ? 'Presupuesto creado' : 'Error al crear presupuesto'];
    }

    public function get($id) {
        $this->db->select('p.*, o.folio as obra_folio, o.nombre as obra_nombre, c.razon_social as cliente');
        $this->db->from('presupuestos_obra p');
        $this->db->join('obras o', 'o.id = p.obra_id', 'left');
        $this->db->join('clientes c', 'c.id = p.cliente_id', 'left');
        $this->db->where('p.id', (int) $id);
        return $this->db->get()->row();
    }

    public function get_detalle($id) {
        $presupuesto = $this->get($id);
        if ($presupuesto) {
            $presupuesto->conceptos = $this->listar_conceptos($id);
        }
        return $presupuesto;
    }

    public function listar($filtros = []) {
        $this->asegurar_infraestructura();
        $this->db->select('p.*, o.folio as obra_folio, o.nombre as obra_nombre, c.razon_social as cliente');
        $this->db->from('presupuestos_obra p');
        $this->db->join('obras o', 'o.id = p.obra_id', 'left');
        $this->db->join('clientes c', 'c.id = p.cliente_id', 'left');
        if (!isset($filtros['incluir_inactivos']) || !$filtros['incluir_inactivos']) {
            $this->db->where('p.activo', 1);
        }
        if (!empty($filtros['obra_id'])) {
            $this->db->where('p.obra_id', (int) $filtros['obra_id']);
        }
        $this->db->order_by('p.fecha_creacion', 'DESC');
        return $this->db->get()->result();
    }

    public function asegurar_infraestructura() {
        if (!$this->db->table_exists('presupuestos_obra')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS presupuestos_obra (
                id INT(11) NOT NULL AUTO_INCREMENT,
                folio VARCHAR(50) NOT NULL,
                obra_id INT(11) NULL,
                cliente_id INT(11) NULL,
                sucursal_id INT(11) NULL,
                tipo ENUM('Presupuesto','Cotizacion') NOT NULL DEFAULT 'Presupuesto',
                version DECIMAL(3,1) NOT NULL DEFAULT 1.0,
                fecha DATE NULL,
                validez_dias INT(11) NOT NULL DEFAULT 15,
                pres_ref VARCHAR(60) NULL,
                atencion VARCHAR(150) NULL,
                subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                descuento_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0.00,
                descuento_monto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                iva_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 16.00,
                iva_monto DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                estatus ENUM('Borrador','Enviado','Aprobado','Rechazado') NOT NULL DEFAULT 'Borrador',
                condiciones_pago TEXT NULL,
                notas_legales TEXT NULL,
                orden_venta_id INT(11) NULL,
                creado_por INT(11) NULL,
                fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (id),
                UNIQUE KEY uk_presupuestos_folio (folio),
                KEY idx_presupuestos_obra (obra_id),
                KEY idx_presupuestos_cliente (cliente_id),
                KEY idx_presupuestos_sucursal (sucursal_id),
                KEY idx_presupuestos_estatus (estatus)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Presupuestos y cotizaciones de venta (entidad nueva, no cotizaciones de compras)'");
        }

        if (!$this->db->table_exists('presupuesto_obra_conceptos')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS presupuesto_obra_conceptos (
                id INT(11) NOT NULL AUTO_INCREMENT,
                presupuesto_id INT(11) NOT NULL,
                concepto_id INT(11) NULL,
                codigo VARCHAR(40) NULL,
                descripcion TEXT NULL,
                unidad VARCHAR(20) NULL,
                cantidad DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                importe DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                seccion VARCHAR(60) NULL,
                fase VARCHAR(60) NULL,
                generador_id INT(11) NULL,
                orden INT(11) NOT NULL DEFAULT 0,
                notas TEXT NULL,
                PRIMARY KEY (id),
                KEY idx_poc_presupuesto (presupuesto_id),
                KEY idx_poc_concepto (concepto_id),
                KEY idx_poc_generador (generador_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Partidas (conceptos) de un presupuesto de obra'");
        }

        // A9: obras.sucursal_id (idempotente)
        if ($this->db->table_exists('obras') && !$this->db->field_exists('sucursal_id', 'obras')) {
            $this->db->query("ALTER TABLE obras
                ADD COLUMN sucursal_id INT(11) NULL DEFAULT NULL AFTER cliente_id,
                ADD KEY idx_obras_sucursal (sucursal_id)");
        }
    }


    /* ─── Conceptos (partidas) ─────────────────────────────────────────── */

    public function listar_conceptos($presupuesto_id) {
        $this->db->where('presupuesto_id', (int) $presupuesto_id);
        $this->db->order_by('orden', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('presupuesto_obra_conceptos')->result();
    }

    public function agregar_concepto(array $data) {
        $this->asegurar_infraestructura();
        $presupuesto_id = (int) ($data['presupuesto_id'] ?? 0);
        if ($presupuesto_id <= 0) {
            return ['success' => false, 'message' => 'Presupuesto inválido'];
        }
        $cantidad = (float) ($data['cantidad'] ?? 0);
        $precio = (float) ($data['precio_unitario'] ?? 0);
        $orden = (int) ($data['orden'] ?? 0);
        if ($orden === 0) {
            $this->db->select_max('orden');
            $this->db->where('presupuesto_id', $presupuesto_id);
            $orden = ((int) $this->db->get('presupuesto_obra_conceptos')->row()->orden) + 1;
        }
        $this->db->insert('presupuesto_obra_conceptos', [
            'presupuesto_id' => $presupuesto_id,
            'concepto_id' => !empty($data['concepto_id']) ? (int) $data['concepto_id'] : null,
            'codigo' => trim((string) ($data['codigo'] ?? '')) ?: null,
            'descripcion' => trim((string) ($data['descripcion'] ?? '')) ?: null,
            'unidad' => trim((string) ($data['unidad'] ?? '')) ?: null,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'importe' => round($cantidad * $precio, 2),
            'seccion' => trim((string) ($data['seccion'] ?? '')) ?: null,
            'fase' => trim((string) ($data['fase'] ?? '')) ?: null,
            'generador_id' => !empty($data['generador_id']) ? (int) $data['generador_id'] : null,
            'orden' => $orden,
            'notas' => trim((string) ($data['notas'] ?? '')) ?: null,
        ]);
        $id = (int) $this->db->insert_id();
        $this->recalcular_totales($presupuesto_id);
        return ['success' => (bool) $id, 'id' => $id, 'message' => $id ? 'Partida agregada' : 'Error al agregar partida'];
    }

    public function actualizar_concepto($id, array $data) {
        $this->asegurar_infraestructura();
        $this->db->where('id', (int) $id);
        $linea = $this->db->get('presupuesto_obra_conceptos')->row();
        if (!$linea) {
            return ['success' => false, 'message' => 'Partida no encontrada'];
        }
        $set = [];
        foreach (['codigo', 'descripcion', 'unidad', 'seccion', 'fase', 'notas'] as $campo) {
            if (array_key_exists($campo, $data)) {
                $set[$campo] = trim((string) $data[$campo]) === '' ? null : trim((string) $data[$campo]);
            }
        }
        if (isset($data['cantidad'])) {
            $set['cantidad'] = (float) $data['cantidad'];
        }
        if (isset($data['precio_unitario'])) {
            $set['precio_unitario'] = (float) $data['precio_unitario'];
        }
        $cantidad = $set['cantidad'] ?? (float) $linea->cantidad;
        $precio = $set['precio_unitario'] ?? (float) $linea->precio_unitario;
        $set['importe'] = round($cantidad * $precio, 2);
        if (!empty($set)) {
            $this->db->where('id', (int) $id);
            $this->db->update('presupuesto_obra_conceptos', $set);
        }
        $this->recalcular_totales((int) $linea->presupuesto_id);
        return ['success' => true, 'message' => 'Partida actualizada'];
    }

    public function eliminar_concepto($id) {
        $this->asegurar_infraestructura();
        $this->db->where('id', (int) $id);
        $linea = $this->db->get('presupuesto_obra_conceptos')->row();
        if (!$linea) {
            return ['success' => false, 'message' => 'Partida no encontrada'];
        }
        $this->db->where('id', (int) $id);
        $this->db->delete('presupuesto_obra_conceptos');
        $this->recalcular_totales((int) $linea->presupuesto_id);
        return ['success' => true, 'message' => 'Partida eliminada'];
    }



    /**
     * Recalcula subtotal, descuento, IVA y total al centavo.
     * subtotal = Σ importe; descuento_monto = subtotal × descuento%/100;
     * base = subtotal - descuento_monto; iva = base × iva%/100; total = base + iva.
     */
    public function recalcular_totales($presupuesto_id) {
        $this->db->select('COALESCE(SUM(importe), 0) AS subtotal');
        $this->db->where('presupuesto_id', (int) $presupuesto_id);
        $subtotal = (float) $this->db->get('presupuesto_obra_conceptos')->row()->subtotal;

        $this->db->select('descuento_porcentaje, iva_porcentaje');
        $this->db->where('id', (int) $presupuesto_id);
        $p = $this->db->get('presupuestos_obra')->row();
        if (!$p) {
            return false;
        }

        $descuento_pct = (float) ($p->descuento_porcentaje ?: 0);
        $iva_pct = (float) ($p->iva_porcentaje ?: 0);

        $descuento_monto = round($subtotal * $descuento_pct / 100, 2);
        $base = $subtotal - $descuento_monto;
        $iva_monto = round($base * $iva_pct / 100, 2);
        $total = round($base + $iva_monto, 2);

        $this->db->where('id', (int) $presupuesto_id);
        $this->db->update('presupuestos_obra', [
            'subtotal' => round($subtotal, 2),
            'descuento_monto' => $descuento_monto,
            'iva_monto' => $iva_monto,
            'total' => $total,
        ]);
        return ['subtotal' => round($subtotal, 2), 'descuento_monto' => $descuento_monto, 'iva_monto' => $iva_monto, 'total' => $total];
    }

    /**
     * Resolución de sucursal (prioridad documentada en Fase D2):
     * presupuesto.sucursal_id → obra.sucursal_id → usuario en sesión → primera activa.
     */
    public function resolver_sucursal_id($presupuesto_id) {
        $presupuesto = $this->get($presupuesto_id);
        $sucursal_id = null;
        if ($presupuesto && !empty($presupuesto->sucursal_id)) {
            $sucursal_id = (int) $presupuesto->sucursal_id;
        } elseif ($presupuesto && !empty($presupuesto->obra_id)) {
            $this->db->select('sucursal_id');
            $this->db->where('id', (int) $presupuesto->obra_id);
            $obra = $this->db->get('obras')->row();
            if ($obra && !empty($obra->sucursal_id)) {
                $sucursal_id = (int) $obra->sucursal_id;
            }
        }
        if (!$sucursal_id) {
            $sucursal_id = $this->session->userdata('sucursal_id');
        }
        if (!$sucursal_id) {
            $this->db->where('estatus', 'Activa');
            $this->db->order_by('id', 'ASC');
            $suc = $this->db->get('sucursales')->row();
            $sucursal_id = $suc ? (int) $suc->id : null;
        }
        return $sucursal_id;
    }
}
