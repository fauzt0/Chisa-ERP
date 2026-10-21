<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ApuModel - Análisis de Precio Unitario (APU).
 * Fórmula replicada de los archivos fuente (ver doc/REGLAS_TECNICAS.md).
 * P.UNITARIO = COSTO DIRECTO + INDIRECTO (INDIRECTO = COSTO DIRECTO × indirecto%).
 * COSTO DIRECTO = material + mano de obra + IMSS + RCYV + HERRAMIENTA.
 * (ISN se calcula y se muestra, pero NO se suma al costo directo, igual que los archivos.)
 */
class ApuModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function asegurar_infraestructura() {
        if (!$this->db->table_exists('concepto_apu_materiales')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS concepto_apu_materiales (
                id INT(11) NOT NULL AUTO_INCREMENT,
                concepto_id INT(11) NOT NULL,
                insumo_id INT(11) NULL,
                producto_id INT(11) NULL,
                descripcion_libre VARCHAR(255) NULL,
                unidad VARCHAR(20) NULL,
                cantidad DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                costo_unitario DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                importe DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                origen ENUM('formulacion','manual') NOT NULL DEFAULT 'manual',
                orden INT(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                KEY idx_apum_concepto (concepto_id),
                KEY idx_apum_insumo (insumo_id),
                KEY idx_apum_producto (producto_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Materiales del APU de un concepto de obra'");
        }

        if (!$this->db->table_exists('concepto_apu_cuadrillas')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS concepto_apu_cuadrillas (
                id INT(11) NOT NULL AUTO_INCREMENT,
                concepto_id INT(11) NOT NULL,
                categoria VARCHAR(120) NULL,
                unidad VARCHAR(20) NOT NULL DEFAULT 'JOR',
                salario_semanal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                cant_cuadrilla DECIMAL(6,2) NOT NULL DEFAULT 1.00,
                rendimiento_jor DECIMAL(10,4) NOT NULL DEFAULT 1.0000,
                salario_jor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                costo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                PRIMARY KEY (id),
                KEY idx_apuc_concepto (concepto_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Cuadrillas (mano de obra) del APU de un concepto'");
        }

        if (!$this->db->table_exists('parametros_apu')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS parametros_apu (
                id INT(11) NOT NULL AUTO_INCREMENT,
                imss DECIMAL(6,3) NOT NULL DEFAULT 32.792,
                rcyv DECIMAL(6,3) NOT NULL DEFAULT 27.208,
                isn DECIMAL(6,3) NOT NULL DEFAULT 3.000,
                herramienta DECIMAL(6,3) NOT NULL DEFAULT 9.000,
                indirecto_utilidad DECIMAL(6,3) NOT NULL DEFAULT 24.000,
                vigente_desde DATE NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (id),
                KEY idx_parametros_apu_activo (activo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Factores parametrizados del APU (NO hardcodear)'");
        }

        // Upgrade de precisión (decidido en la revisión de Fase 5): los archivos
        // fuente usan costos con 4 decimales (Z-01C: adhesivo 7.9802/kg → 4.38911),
        // por lo que DECIMAL(12,2) truncaba y desplazaba el P.UNITARIO en 0.0001364.
        // Idempotente: solo altera si la columna aún tiene escala 2.
        if ($this->db->table_exists('concepto_apu_materiales')) {
            $col = $this->db->query(
                "SELECT NUMERIC_PRECISION AS p, NUMERIC_SCALE AS s
                   FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'concepto_apu_materiales'
                    AND COLUMN_NAME = 'costo_unitario'"
            )->row();
            if ($col && ((int) $col->p !== 12 || (int) $col->s !== 4)) {
                $this->db->query(
                    'ALTER TABLE concepto_apu_materiales
                        MODIFY costo_unitario DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                        MODIFY importe DECIMAL(12,4) NOT NULL DEFAULT 0.0000'
                );
            }
        }

        // Seed con los valores observados en los archivos fuente (ZOCLOS → 306.66)
        $this->db->where('activo', 1);
        if ($this->db->count_all_results('parametros_apu') === 0) {
            $this->db->insert('parametros_apu', [
                'imss' => 32.792,
                'rcyv' => 27.208,
                'isn' => 3.000,
                'herramienta' => 9.000,
                'indirecto_utilidad' => 24.000,
                'vigente_desde' => date('Y-m-d'),
                'activo' => 1,
            ]);
        }
    }

    public function get_parametros_activos() {
        $this->asegurar_infraestructura();
        $this->db->where('activo', 1);
        $this->db->order_by('vigente_desde', 'DESC');
        $this->db->order_by('id', 'DESC');
        return $this->db->get('parametros_apu')->row();
    }

    public function get_materiales($concepto_id) {
        $this->db->where('concepto_id', (int) $concepto_id);
        $this->db->order_by('orden', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('concepto_apu_materiales')->result();
    }

    public function get_cuadrillas($concepto_id) {
        $this->db->where('concepto_id', (int) $concepto_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('concepto_apu_cuadrillas')->result();
    }


    /**
     * Calcula el P.UNITARIO de un concepto leyendo parametros_apu activos.
     * Devuelve el desglose completo (materiales, mano_obra, prorrateos, etc.).
     * DECISIÓN: se recalcula SIEMPRE (no se cachea en el concepto) para que
     * cualquier cambio de material/cuadrilla/parámetro se refleje al instante.
     */
    public function calcular_precio_unitario($concepto_id) {
        $this->asegurar_infraestructura();
        $params = $this->get_parametros_activos();
        if (!$params) {
            $params = (object) ['imss' => 32.792, 'rcyv' => 27.208, 'isn' => 3.000, 'herramienta' => 9.000, 'indirecto_utilidad' => 24.000];
        }

        $materiales = $this->get_materiales($concepto_id);
        $cuadrillas = $this->get_cuadrillas($concepto_id);

        $subtotal_material = 0.0;
        $materiales_out = [];
        foreach ($materiales as $m) {
            $importe = (float) $m->cantidad * (float) $m->costo_unitario;
            $subtotal_material += $importe;
            $materiales_out[] = [
                'id' => (int) $m->id,
                'descripcion' => $m->descripcion_libre,
                'unidad' => $m->unidad,
                'cantidad' => (float) $m->cantidad,
                'costo_unitario' => (float) $m->costo_unitario,
                'importe' => $importe,
                'origen' => $m->origen,
            ];
        }

        $subtotal_mo = 0.0;
        $mano_obra_out = [];
        foreach ($cuadrillas as $c) {
            $salario_jor = (float) $c->salario_semanal / 7.0;
            $rendimiento = (float) $c->rendimiento_jor;
            $costo = $rendimiento > 0 ? ($salario_jor / $rendimiento) * (float) $c->cant_cuadrilla : 0.0;
            $subtotal_mo += $costo;
            $mano_obra_out[] = [
                'id' => (int) $c->id,
                'categoria' => $c->categoria,
                'unidad' => $c->unidad,
                'salario_semanal' => (float) $c->salario_semanal,
                'cant_cuadrilla' => (float) $c->cant_cuadrilla,
                'salario_jor' => $salario_jor,
                'rendimiento_jor' => $rendimiento,
                'costo' => $costo,
            ];
        }

        $imss = $subtotal_mo * ((float) $params->imss / 100);
        $rcyv = $subtotal_mo * ((float) $params->rcyv / 100);
        $isn = $subtotal_mo * ((float) $params->isn / 100);
        $herramienta = $subtotal_mo * ((float) $params->herramienta / 100);

        // Igual que los archivos fuente: ISN se muestra pero NO suma al costo directo.
        $costo_directo = $subtotal_material + $subtotal_mo + $imss + $rcyv + $herramienta;
        $indirecto = $costo_directo * ((float) $params->indirecto_utilidad / 100);
        $precio_unitario = $costo_directo + $indirecto;

        return [
            'success' => true,
            'concepto_id' => (int) $concepto_id,
            'materiales' => $materiales_out,
            'mano_obra' => $mano_obra_out,
            'subtotal_material' => $subtotal_material,
            'subtotal_mo' => $subtotal_mo,
            'imss' => $imss,
            'rcyv' => $rcyv,
            'isn' => $isn,
            'herramienta' => $herramienta,
            'costo_directo' => $costo_directo,
            'indirecto' => $indirecto,
            'precio_unitario' => $precio_unitario,
            'precio_unitario_redondeado' => round($precio_unitario, 2),
            'parametros' => [
                'imss' => (float) $params->imss,
                'rcyv' => (float) $params->rcyv,
                'isn' => (float) $params->isn,
                'herramienta' => (float) $params->herramienta,
                'indirecto_utilidad' => (float) $params->indirecto_utilidad,
            ],
        ];
    }

    public function agregar_material(array $data) {
        $this->asegurar_infraestructura();
        $concepto_id = (int) ($data['concepto_id'] ?? 0);
        if ($concepto_id <= 0) {
            return ['success' => false, 'message' => 'Concepto inválido'];
        }
        $cantidad = (float) ($data['cantidad'] ?? 0);
        $costo = (float) ($data['costo_unitario'] ?? 0);
        $this->db->insert('concepto_apu_materiales', [
            'concepto_id' => $concepto_id,
            'insumo_id' => !empty($data['insumo_id']) ? (int) $data['insumo_id'] : null,
            'producto_id' => !empty($data['producto_id']) ? (int) $data['producto_id'] : null,
            'descripcion_libre' => trim((string) ($data['descripcion_libre'] ?? '')) ?: null,
            'unidad' => trim((string) ($data['unidad'] ?? '')) ?: null,
            'cantidad' => $cantidad,
            'costo_unitario' => $costo,
            'importe' => round($cantidad * $costo, 4),
            'origen' => ($data['origen'] ?? 'manual') === 'formulacion' ? 'formulacion' : 'manual',
            'orden' => (int) ($data['orden'] ?? 0),
        ]);
        return ['success' => true, 'id' => (int) $this->db->insert_id()];
    }

    public function eliminar_material($id) {
        $this->asegurar_infraestructura();
        $this->db->where('id', (int) $id);
        $this->db->delete('concepto_apu_materiales');
        return ['success' => true];
    }

    public function agregar_cuadrilla(array $data) {
        $this->asegurar_infraestructura();
        $concepto_id = (int) ($data['concepto_id'] ?? 0);
        if ($concepto_id <= 0) {
            return ['success' => false, 'message' => 'Concepto inválido'];
        }
        $salario_semanal = (float) ($data['salario_semanal'] ?? 0);
        $rendimiento = (float) ($data['rendimiento_jor'] ?? 1);
        $salario_jor = $salario_semanal / 7.0;
        $costo = $rendimiento > 0 ? ($salario_jor / $rendimiento) * (float) ($data['cant_cuadrilla'] ?? 1) : 0;
        $this->db->insert('concepto_apu_cuadrillas', [
            'concepto_id' => $concepto_id,
            'categoria' => trim((string) ($data['categoria'] ?? '')) ?: null,
            'unidad' => $data['unidad'] ?? 'JOR',
            'salario_semanal' => $salario_semanal,
            'cant_cuadrilla' => (float) ($data['cant_cuadrilla'] ?? 1),
            'rendimiento_jor' => $rendimiento,
            'salario_jor' => round($salario_jor, 2),
            'costo' => round($costo, 2),
        ]);
        return ['success' => true, 'id' => (int) $this->db->insert_id()];
    }

    public function eliminar_cuadrilla($id) {
        $this->asegurar_infraestructura();
        $this->db->where('id', (int) $id);
        $this->db->delete('concepto_apu_cuadrillas');
        return ['success' => true];
    }

    /**
     * Precarga materiales desde el motor de formulaciones (origen='formulacion').
     * Usa ObrasModel::calcular_materiales_linea_obra() y vuelca el resultado.
     */
    public function precargar_materiales_desde_formulacion($concepto_id, $producto_id, $area_m2, $factor = 1.0, $formulacion_id = null) {
        $this->asegurar_infraestructura();
        $this->load->model('Obras/ObrasModel');
        $res = $this->ObrasModel->calcular_materiales_linea_obra($producto_id, $area_m2, $factor, $formulacion_id);

        if (empty($res['success']) || empty($res['insumos'])) {
            return ['success' => false, 'message' => $res['message'] ?? 'No se pudieron calcular insumos'];
        }

        $orden = 0;
        $insertados = 0;
        foreach ($res['insumos'] as $ins) {
            $orden++;
            $this->db->insert('concepto_apu_materiales', [
                'concepto_id' => (int) $concepto_id,
                'insumo_id' => !empty($ins['insumo_id']) ? (int) $ins['insumo_id'] : null,
                'producto_id' => (int) $producto_id,
                'descripcion_libre' => $ins['insumo'] ?? ($ins['nombre'] ?? 'Insumo'),
                'unidad' => $ins['unidad'] ?? 'Kg',
                'cantidad' => (float) ($ins['cantidad'] ?? 0),
                'costo_unitario' => (float) ($ins['costo'] ?? 0),
                'importe' => round((float) ($ins['cantidad'] ?? 0) * (float) ($ins['costo'] ?? 0), 4),
                'origen' => 'formulacion',
                'orden' => $orden,
            ]);
            $insertados++;
        }

        return ['success' => true, 'insertados' => $insertados];
    }
}

