<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * RevisionCuantificacionModel - Revisión de cuantificación:
 * cruza total cuantificado (generadores) contra total cotizado (presupuesto).
 */
class RevisionCuantificacionModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function asegurar_infraestructura() {
        if (!$this->db->table_exists('obra_revisiones_cuantificacion')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS obra_revisiones_cuantificacion (
                id INT(11) NOT NULL AUTO_INCREMENT,
                obra_id INT(11) NOT NULL,
                concepto_id INT(11) NULL,
                descripcion TEXT NULL,
                total_cuantificado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                total_cotizado DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                diferencia DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                diferencia_pct DECIMAL(6,2) NOT NULL DEFAULT 0.00,
                comentario TEXT NULL,
                estatus ENUM('OK','Aclarar','Pendiente') NOT NULL DEFAULT 'Pendiente',
                revisado_por INT(11) NULL,
                fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_revisiones_obra (obra_id),
                KEY idx_revisiones_concepto (concepto_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Revisión de cuantificación: total cuantificado vs cotizado'");
        }
    }

    /**
     * Cruza total_cuantificado (generadores aplicados) contra total_cotizado
     * (cantidad de partidas del presupuesto) y persiste una fila por concepto.
     */
    public function comparar($obra_id) {
        $this->asegurar_infraestructura();
        $obra_id = (int) $obra_id;

        // Conceptos cotizados: partidas de todos los presupuestos de la obra
        $this->db->select('poc.concepto_id, poc.codigo, poc.descripcion, COALESCE(SUM(poc.cantidad), 0) AS cotizado');
        $this->db->from('presupuesto_obra_conceptos poc');
        $this->db->join('presupuestos_obra p', 'p.id = poc.presupuesto_id');
        $this->db->where('p.obra_id', $obra_id);
        $this->db->where('p.activo', 1);
        $this->db->group_by('poc.concepto_id, poc.codigo, poc.descripcion');
        $conceptos = $this->db->get()->result();

        $resultado = [];
        foreach ($conceptos as $c) {
            // Total cuantificado desde generadores aplicados
            $cuantificado = 0.0;
            if (!empty($c->concepto_id)) {
                $this->db->select('COALESCE(SUM(total), 0) AS total');
                $this->db->where('obra_id', $obra_id);
                $this->db->where('concepto_id', (int) $c->concepto_id);
                $cuantificado = (float) $this->db->get('obra_generadores')->row()->total;
            }

            $cotizado = (float) $c->cotizado;
            $diferencia = round($cuantificado - $cotizado, 2);
            $diferencia_pct = $cotizado != 0 ? round(($diferencia / $cotizado) * 100, 2) : 0.00;

            $this->db->where('obra_id', $obra_id);
            $this->db->where('concepto_id', (int) $c->concepto_id);
            $existe = $this->db->get('obra_revisiones_cuantificacion')->row();

            $data = [
                'total_cuantificado' => $cuantificado,
                'total_cotizado' => $cotizado,
                'diferencia' => $diferencia,
                'diferencia_pct' => $diferencia_pct,
            ];

            if ($existe) {
                $this->db->where('id', (int) $existe->id);
                $this->db->update('obra_revisiones_cuantificacion', $data);
                $id = (int) $existe->id;
            } else {
                $data['obra_id'] = $obra_id;
                $data['concepto_id'] = (int) $c->concepto_id;
                $data['descripcion'] = $c->codigo . ' - ' . $c->descripcion;
                $data['estatus'] = 'Pendiente';
                $this->db->insert('obra_revisiones_cuantificacion', $data);
                $id = (int) $this->db->insert_id();
            }

            $resultado[] = [
                'id' => $id,
                'concepto_id' => (int) $c->concepto_id,
                'codigo' => $c->codigo,
                'descripcion' => $c->descripcion,
                'total_cuantificado' => $cuantificado,
                'total_cotizado' => $cotizado,
                'diferencia' => $diferencia,
                'diferencia_pct' => $diferencia_pct,
            ];
        }

        return ['success' => true, 'revisiones' => $resultado];
    }

    public function listar($obra_id) {
        $this->asegurar_infraestructura();
        $this->db->where('obra_id', (int) $obra_id);
        $this->db->order_by('id', 'ASC');
        return $this->db->get('obra_revisiones_cuantificacion')->result();
    }

    public function actualizar_revision($id, array $data) {
        $this->asegurar_infraestructura();
        $set = [];
        foreach (['total_cuantificado', 'total_cotizado', 'diferencia', 'diferencia_pct', 'comentario', 'estatus'] as $campo) {
            if (array_key_exists($campo, $data)) {
                $set[$campo] = $data[$campo];
            }
        }
        if (!empty($set)) {
            $this->db->where('id', (int) $id);
            $this->db->update('obra_revisiones_cuantificacion', $set);
        }
        return ['success' => true];
    }
}
