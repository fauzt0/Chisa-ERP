<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ConceptosObraModel - Catálogo de conceptos (partidas) de obra.
 * El P.U. SIEMPRE se calcula desde el APU (ApuModel); aquí no se guardan precios.
 */
class ConceptosObraModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Crea infraestructura idempotente (patrón SucursalesModel).
     * Se invoca al inicio de los métodos públicos de escritura.
     */
    public function asegurar_infraestructura() {
        if (!$this->db->table_exists('conceptos_obra')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS conceptos_obra (
                id INT(11) NOT NULL AUTO_INCREMENT,
                codigo VARCHAR(40) NOT NULL,
                descripcion TEXT NULL,
                unidad ENUM('M2','ML','M3','PZA','LOTE','SERVICIO') NOT NULL DEFAULT 'M2',
                tipo ENUM('Suministro','Aplicacion','Suministro y aplicacion','Otro') NOT NULL DEFAULT 'Suministro y aplicacion',
                seccion VARCHAR(60) NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                creado_por INT(11) NULL,
                fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_modificacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_conceptos_codigo (codigo),
                KEY idx_conceptos_activo (activo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Conceptos de obra (partidas) con código, unidad y tipo'");
        }

        if (!$this->db->table_exists('precios_insumo_historial')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS precios_insumo_historial (
                id INT(11) NOT NULL AUTO_INCREMENT,
                insumo_id INT(11) NULL,
                precio DECIMAL(12,2) NOT NULL DEFAULT 0.00,
                fecha DATE NULL,
                proveedor_id INT(11) NULL,
                origen ENUM('compra','captura','PRECIO_ACT') NOT NULL DEFAULT 'captura',
                notas VARCHAR(255) NULL,
                PRIMARY KEY (id),
                KEY idx_ph_insumo (insumo_id),
                KEY idx_ph_proveedor (proveedor_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Historial de precios de insumos (PRECIO_ACT)'");
        }
    }

    public function crear(array $data) {
        $this->asegurar_infraestructura();
        $codigo = strtoupper(trim((string) ($data['codigo'] ?? '')));
        if ($codigo === '') {
            return ['success' => false, 'message' => 'El código es obligatorio'];
        }
        $this->db->where('codigo', $codigo);
        if ($this->db->count_all_results('conceptos_obra') > 0) {
            return ['success' => false, 'message' => 'Ese código de concepto ya existe'];
        }
        $this->db->insert('conceptos_obra', [
            'codigo' => $codigo,
            'descripcion' => trim((string) ($data['descripcion'] ?? '')) ?: null,
            'unidad' => $data['unidad'] ?? 'M2',
            'tipo' => $data['tipo'] ?? 'Suministro y aplicacion',
            'seccion' => trim((string) ($data['seccion'] ?? '')) ?: null,
            'activo' => 1,
            'creado_por' => $data['creado_por'] ?? null,
        ]);
        return ['success' => true, 'id' => (int) $this->db->insert_id(), 'message' => 'Concepto creado'];
    }

    public function actualizar($id, array $data) {
        $this->asegurar_infraestructura();
        $set = [];
        foreach (['descripcion', 'unidad', 'tipo', 'seccion'] as $campo) {
            if (array_key_exists($campo, $data)) {
                $set[$campo] = $data[$campo] === '' ? null : $data[$campo];
            }
        }
        if (isset($data['codigo']) && trim((string) $data['codigo']) !== '') {
            $set['codigo'] = strtoupper(trim((string) $data['codigo']));
        }
        if (empty($set)) {
            return ['success' => false, 'message' => 'Sin cambios'];
        }
        $this->db->where('id', (int) $id);
        $this->db->update('conceptos_obra', $set);
        return ['success' => true, 'message' => 'Concepto actualizado'];
    }

    public function eliminar($id) {
        $this->asegurar_infraestructura();
        $this->db->where('id', (int) $id);
        $this->db->update('conceptos_obra', ['activo' => 0]);
        return ['success' => true, 'message' => 'Concepto eliminado'];
    }

    public function get($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('conceptos_obra')->row();
    }

    public function get_por_codigo($codigo) {
        $this->db->where('codigo', strtoupper(trim((string) $codigo)));
        return $this->db->get('conceptos_obra')->row();
    }

    public function listar($filtros = []) {
        $this->asegurar_infraestructura();
        if (!isset($filtros['incluir_inactivos']) || !$filtros['incluir_inactivos']) {
            $this->db->where('activo', 1);
        }
        if (!empty($filtros['busqueda'])) {
            $this->db->group_start();
            $this->db->like('codigo', $filtros['busqueda']);
            $this->db->or_like('descripcion', $filtros['busqueda']);
            $this->db->group_end();
        }
        $this->db->order_by('codigo', 'ASC');
        return $this->db->get('conceptos_obra')->result();
    }


    /**
     * Carga inicial de conceptos observados en los archivos de entrenamiento
     * (sección 3.2). No inventa precios: el P.U. sale del APU.
     */
    public function precargar_conceptos_iniciales($creado_por = null) {
        $this->asegurar_infraestructura();
        $conceptos = [
            ['codigo' => 'R1A', 'descripcion' => 'Recubrimiento pétreo/vítreo en muro (IMSS) — ver APU', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'IMSS'],
            ['codigo' => 'R2A', 'descripcion' => 'Recubrimiento pétreo/vítreo en muro (IMSS)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'IMSS'],
            ['codigo' => 'R2B', 'descripcion' => 'Recubrimiento pétreo/vítreo en muro (IMSS)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'IMSS'],
            ['codigo' => 'Z1A', 'descripcion' => 'Zoclo / recubrimiento (IMSS)', 'unidad' => 'ML', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'IMSS'],
            ['codigo' => 'Z9', 'descripcion' => 'Zoclo / recubrimiento (IMSS)', 'unidad' => 'ML', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'IMSS'],
            ['codigo' => 'Z-01C', 'descripcion' => 'Zoclo Z-01C cuerpo cerámico, interceramic rectificado liso, mate, formato grande 60x120 cm, de 10 cm de alto, fijado con adhesivo interceramic select piso con protector antibacterial para boquilla.', 'unidad' => 'ML', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Zoclos'],
            ['codigo' => 'Z-02', 'descripcion' => 'Zoclo Z-02 porcelánico rectificado, estilo piedra, interceramic stonewalk marfil formato grande.', 'unidad' => 'ML', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Zoclos'],
            ['codigo' => 'Z-03', 'descripcion' => 'Zoclo Z-03 cuerpo cerámico, interceramic maxima semibrillante formato medio 40x40 cm de 10 cm.', 'unidad' => 'ML', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Zoclos'],
            ['codigo' => 'Z-14', 'descripcion' => 'Zoclo Z-14 de concreto f\'c = 250 kg/cm2 con acabado pulido, hecho en obra.', 'unidad' => 'ML', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Zoclos'],
            ['codigo' => 'Z-17', 'descripcion' => 'Zoclo Z-17 cuerpo cerámico, Interceramic Montesoro brillante formato grande 50x50 cm fijado con adhesivo.', 'unidad' => 'ML', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Zoclos'],
            ['codigo' => 'PL-01', 'descripcion' => 'Piso PL-01 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'PL-02', 'descripcion' => 'Piso PL-02 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'PL-03', 'descripcion' => 'Piso PL-03 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'PL-04', 'descripcion' => 'Piso PL-04 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'PL-05', 'descripcion' => 'Piso PL-05 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'PL-06', 'descripcion' => 'Piso PL-06 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'PL-07', 'descripcion' => 'Piso PL-07 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'P-2', 'descripcion' => 'Partida P-2 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'P-3', 'descripcion' => 'Partida P-3 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'P-4', 'descripcion' => 'Partida P-4 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'P-5', 'descripcion' => 'Partida P-5 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'P-6', 'descripcion' => 'Partida P-6 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'P-7', 'descripcion' => 'Partida P-7 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'P-8', 'descripcion' => 'Partida P-8 (resumen)', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Resumen'],
            ['codigo' => 'ACA-001', 'descripcion' => 'Concepto de catálogo ACA-001', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Catálogo'],
            ['codigo' => 'ALB-001', 'descripcion' => 'Concepto de catálogo ALB-001', 'unidad' => 'M2', 'tipo' => 'Suministro y aplicacion', 'seccion' => 'Catálogo'],
        ];

        $insertados = 0;
        foreach ($conceptos as $c) {
            $this->db->where('codigo', $c['codigo']);
            if ($this->db->count_all_results('conceptos_obra') > 0) {
                continue;
            }
            $this->db->insert('conceptos_obra', [
                'codigo' => $c['codigo'],
                'descripcion' => $c['descripcion'],
                'unidad' => $c['unidad'],
                'tipo' => $c['tipo'],
                'seccion' => $c['seccion'],
                'activo' => 1,
                'creado_por' => $creado_por,
            ]);
            $insertados++;
        }
        return ['success' => true, 'insertados' => $insertados];
    }
}
