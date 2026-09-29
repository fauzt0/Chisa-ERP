<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * GeneradoresModel - Generadores de cuantificación por obra.
 * Bloques 1..3 con PZAS|N°|LARGO|ALTURA|AREA|DESCUENTO y simbología.
 */
class GeneradoresModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function asegurar_infraestructura() {
        if (!$this->db->table_exists('obra_generadores')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS obra_generadores (
                id INT(11) NOT NULL AUTO_INCREMENT,
                obra_id INT(11) NOT NULL,
                concepto_id INT(11) NULL,
                hoja_no INT(11) NOT NULL DEFAULT 1,
                hoja_de INT(11) NOT NULL DEFAULT 1,
                periodo_inicio DATE NULL,
                periodo_fin DATE NULL,
                concepto_texto TEXT NULL,
                ubicacion VARCHAR(180) NULL,
                suma_hoja DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                acumulado_anterior DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                estatus ENUM('Borrador','Aplicado') NOT NULL DEFAULT 'Borrador',
                fecha DATE NULL,
                PRIMARY KEY (id),
                KEY idx_generadores_obra (obra_id),
                KEY idx_generadores_concepto (concepto_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Generadores de cuantificación por obra'");
        }

        if (!$this->db->table_exists('obra_generador_lineas')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS obra_generador_lineas (
                id INT(11) NOT NULL AUTO_INCREMENT,
                generador_id INT(11) NOT NULL,
                bloque TINYINT(4) NOT NULL DEFAULT 1,
                pzas DECIMAL(6,2) NOT NULL DEFAULT 0.00,
                n INT(11) NOT NULL DEFAULT 0,
                largo DECIMAL(10,3) NOT NULL DEFAULT 0.000,
                alto DECIMAL(10,3) NOT NULL DEFAULT 0.000,
                area DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                descuento DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
                simbolo ENUM('','P','CV','HM','BOQH','BOQV','C','V','GE','VA','O') NOT NULL DEFAULT '',
                PRIMARY KEY (id),
                KEY idx_generador_lineas_generador (generador_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Líneas de medición (bloques 1..3) de un generador'");
        }
    }

    public function crear(array $data) {
        $this->asegurar_infraestructura();
        $obra_id = (int) ($data['obra_id'] ?? 0);
        if ($obra_id <= 0) {
            return ['success' => false, 'message' => 'Obra inválida'];
        }
        $this->db->insert('obra_generadores', [
            'obra_id' => $obra_id,
            'concepto_id' => !empty($data['concepto_id']) ? (int) $data['concepto_id'] : null,
            'hoja_no' => (int) ($data['hoja_no'] ?? 1),
            'hoja_de' => (int) ($data['hoja_de'] ?? 1),
            'periodo_inicio' => !empty($data['periodo_inicio']) ? $data['periodo_inicio'] : null,
            'periodo_fin' => !empty($data['periodo_fin']) ? $data['periodo_fin'] : null,
            'concepto_texto' => trim((string) ($data['concepto_texto'] ?? '')) ?: null,
            'ubicacion' => trim((string) ($data['ubicacion'] ?? '')) ?: null,
            'acumulado_anterior' => (float) ($data['acumulado_anterior'] ?? 0),
            'estatus' => 'Borrador',
            'fecha' => !empty($data['fecha']) ? $data['fecha'] : date('Y-m-d'),
        ]);
        $id = (int) $this->db->insert_id();
        if ($id) {
            $this->totalizar($id);
        }
        return ['success' => (bool) $id, 'id' => $id, 'message' => $id ? 'Generador creado' : 'Error al crear generador'];
    }

    public function get($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('obra_generadores')->row();
    }

    public function get_detalle($id) {
        $gen = $this->get($id);
        if ($gen) {
            $gen->lineas = $this->listar_lineas($id);
        }
        return $gen;
    }

    public function listar($filtros = []) {
        $this->asegurar_infraestructura();
        if (!empty($filtros['obra_id'])) {
            $this->db->where('obra_id', (int) $filtros['obra_id']);
        }
        $this->db->order_by('id', 'DESC');
        return $this->db->get('obra_generadores')->result();
    }

    public function eliminar($id) {
        $this->asegurar_infraestructura();
        $this->db->where('generador_id', (int) $id);
        $this->db->delete('obra_generador_lineas');
        $this->db->where('id', (int) $id);
        $this->db->delete('obra_generadores');
        return ['success' => true, 'message' => 'Generador eliminado'];
    }

    public function listar_lineas($generador_id) {
        $this->db->where('generador_id', (int) $generador_id);
        $this->db->order_by('bloque', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get('obra_generador_lineas')->result();
    }

    public function agregar_linea(array $data) {
        $this->asegurar_infraestructura();
        $generador_id = (int) ($data['generador_id'] ?? 0);
        if ($generador_id <= 0) {
            return ['success' => false, 'message' => 'Generador inválido'];
        }
        $pzas = (float) ($data['pzas'] ?? 1);
        if ($pzas <= 0) {
            $pzas = 1;
        }
        $largo = (float) ($data['largo'] ?? 0);
        $alto = (float) ($data['alto'] ?? 0);
        $descuento = (float) ($data['descuento'] ?? 0);
        $area = $largo * $alto * $pzas;
        $this->db->insert('obra_generador_lineas', [
            'generador_id' => $generador_id,
            'bloque' => (int) ($data['bloque'] ?? 1),
            'pzas' => $pzas,
            'n' => (int) ($data['n'] ?? 0),
            'largo' => $largo,
            'alto' => $alto,
            'area' => $area,
            'descuento' => $descuento,
            'simbolo' => ($data['simbolo'] ?? ''),
        ]);
        $id = (int) $this->db->insert_id();
        $this->totalizar($generador_id);
        return ['success' => (bool) $id, 'id' => $id];
    }

    public function eliminar_linea($id) {
        $this->asegurar_infraestructura();
        $this->db->where('id', (int) $id);
        $linea = $this->db->get('obra_generador_lineas')->row();
        if (!$linea) {
            return ['success' => false, 'message' => 'Línea no encontrada'];
        }
        $this->db->where('id', (int) $id);
        $this->db->delete('obra_generador_lineas');
        $this->totalizar((int) $linea->generador_id);
        return ['success' => true];
    }

    /**
     * totalizar = SUMA(area - descuento) + acumulado_anterior.
     */
    public function totalizar($generador_id) {
        $this->asegurar_infraestructura();
        $this->db->select('COALESCE(SUM(area - descuento), 0) AS suma');
        $this->db->where('generador_id', (int) $generador_id);
        $suma = (float) $this->db->get('obra_generador_lineas')->row()->suma;

        $gen = $this->get($generador_id);
        if (!$gen) {
            return ['success' => false];
        }
        $acumulado = (float) $gen->acumulado_anterior;
        $total = round($suma + $acumulado, 2);

        $this->db->where('id', (int) $generador_id);
        $this->db->update('obra_generadores', [
            'suma_hoja' => round($suma, 2),
            'total' => $total,
        ]);

        return ['success' => true, 'suma_hoja' => round($suma, 2), 'acumulado_anterior' => $acumulado, 'total' => $total];
    }

    /**
     * Escribe la cantidad del generador en presupuesto_obra_conceptos (por generador_id)
     * y recalcula importe = cantidad × precio_unitario.
     */
    public function aplicar_a_partida($generador_id) {
        $this->asegurar_infraestructura();
        $gen = $this->get_detalle($generador_id);
        if (!$gen) {
            return ['success' => false, 'message' => 'Generador no encontrado'];
        }
        $tot = $this->totalizar($generador_id);
        $cantidad = $tot['total'];

        // Marcar el generador como Aplicado
        $this->db->where('id', (int) $generador_id);
        $this->db->update('obra_generadores', ['estatus' => 'Aplicado']);

        // Actualizar la partida vinculada por generador_id
        $this->db->where('generador_id', (int) $generador_id);
        $linea = $this->db->get('presupuesto_obra_conceptos')->row();
        if (!$linea) {
            return ['success' => false, 'message' => 'El generador no está vinculado a ninguna partida'];
        }

        $precio = (float) $linea->precio_unitario;
        $this->db->where('id', (int) $linea->id);
        $this->db->update('presupuesto_obra_conceptos', [
            'cantidad' => $cantidad,
            'importe' => round($cantidad * $precio, 2),
        ]);

        $this->load->model('Obras/PresupuestosObraModel');
        $this->PresupuestosObraModel->recalcular_totales((int) $linea->presupuesto_id);

        return ['success' => true, 'cantidad' => $cantidad, 'message' => 'Cantidad aplicada a la partida'];
    }
}

