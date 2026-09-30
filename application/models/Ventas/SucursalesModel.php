<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sucursales de mostrador (POS). El inventario PT sigue global;
 * la sucursal etiqueta la OV para caja, recibo y reportes.
 */
class SucursalesModel extends CI_Model {

    public function asegurar_infraestructura() {
        if (!$this->db->table_exists('sucursales')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS sucursales (
                id INT(11) NOT NULL AUTO_INCREMENT,
                codigo VARCHAR(20) NOT NULL,
                nombre VARCHAR(120) NOT NULL,
                direccion VARCHAR(255) NULL,
                telefono VARCHAR(50) NULL,
                estatus ENUM('Activa','Inactiva') NOT NULL DEFAULT 'Activa',
                fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_sucursal_codigo (codigo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Sucursales / cajas POS'");
        }

        if ($this->db->table_exists('ordenes_venta') && !$this->db->field_exists('sucursal_id', 'ordenes_venta')) {
            $this->db->query("ALTER TABLE ordenes_venta
                ADD COLUMN sucursal_id INT(11) NULL DEFAULT NULL AFTER cliente_id,
                ADD KEY idx_ov_sucursal (sucursal_id)");
        }

        // Fase 5: marca de agua para exportaciones de obras/presupuestos
        if ($this->db->table_exists('sucursales')) {
            if (!$this->db->field_exists('logo_marca_agua', 'sucursales')) {
                $this->db->query("ALTER TABLE sucursales ADD COLUMN logo_marca_agua VARCHAR(255) NULL");
            }
            if (!$this->db->field_exists('texto_marca_agua', 'sucursales')) {
                $this->db->query("ALTER TABLE sucursales ADD COLUMN texto_marca_agua VARCHAR(120) NULL");
            }
        }

        $this->db->where('codigo', 'SUC-MATRIZ');
        if ($this->db->count_all_results('sucursales') === 0) {
            $this->db->insert('sucursales', [
                'codigo' => 'SUC-MATRIZ',
                'nombre' => 'Matriz CDMX',
                'direccion' => 'Andrés Molina Enríquez #156, Col. San Pedro Iztacalco, C.P. 08220, CDMX',
                'telefono' => '55-5579-9434',
                'estatus' => 'Activa',
            ]);
        }
    }

    public function listar_activas() {
        $this->asegurar_infraestructura();
        $this->db->where('estatus', 'Activa');
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get('sucursales')->result();
    }

    public function get($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('sucursales')->row();
    }

    public function crear($data) {
        $codigo = strtoupper(trim((string) ($data['codigo'] ?? '')));
        $nombre = trim((string) ($data['nombre'] ?? ''));
        if ($codigo === '' || $nombre === '') {
            return ['success' => false, 'message' => 'Código y nombre son obligatorios'];
        }
        $this->db->where('codigo', $codigo);
        if ($this->db->count_all_results('sucursales') > 0) {
            return ['success' => false, 'message' => 'Ese código de sucursal ya existe'];
        }
        $this->db->insert('sucursales', [
            'codigo' => $codigo,
            'nombre' => $nombre,
            'direccion' => trim((string) ($data['direccion'] ?? '')) ?: null,
            'telefono' => trim((string) ($data['telefono'] ?? '')) ?: null,
            'estatus' => 'Activa',
        ]);
        return ['success' => true, 'id' => (int) $this->db->insert_id(), 'message' => 'Sucursal creada'];
    }

    /**
     * Todas las sucursales (activas e inactivas) para el panel de configuración
     * de marca de agua (usuarios/GestionUsuarios/empresa).
     */
    public function listar_todas() {
        $this->asegurar_infraestructura();
        $this->db->order_by('estatus', 'ASC');
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get('sucursales')->result();
    }

    /**
     * Actualiza campos de una sucursal, incluida su marca de agua
     * (texto_marca_agua / logo_marca_agua) usada por ExportacionObraModel.
     * Cadena vacía = NULL, para que get_marca_agua() aplique el fallback
     * ("Sucursal: <nombre>" + logo de configuracion_empresa).
     */
    public function actualizar($id, array $data) {
        $this->asegurar_infraestructura();
        if (!$this->get($id)) {
            return ['success' => false, 'message' => 'Sucursal no encontrada'];
        }

        $permitidas = ['nombre', 'direccion', 'telefono', 'estatus', 'texto_marca_agua', 'logo_marca_agua'];
        $update = [];
        foreach ($permitidas as $campo) {
            if (!array_key_exists($campo, $data)) {
                continue;
            }
            $valor = is_string($data[$campo]) ? trim($data[$campo]) : $data[$campo];
            if ($campo === 'estatus') {
                if (!in_array($valor, ['Activa', 'Inactiva'], true)) {
                    continue;
                }
                $update[$campo] = $valor;
                continue;
            }
            $update[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }

        if (empty($update)) {
            return ['success' => false, 'message' => 'No hay cambios que guardar'];
        }

        $this->db->where('id', (int) $id)->update('sucursales', $update);

        return ['success' => true, 'message' => 'Sucursal actualizada', 'sucursal' => $this->get($id)];
    }

    /**
     * Marca de agua de una sucursal para exportaciones (Excel/PDF).
     * Devuelve {logo, texto} con fallback al logo de configuracion_empresa y
     * al nombre de la sucursal.
     */
    public function get_marca_agua($sucursal_id = null) {
        $this->asegurar_infraestructura();

        $sucursal = null;
        if (!empty($sucursal_id)) {
            $sucursal = $this->get($sucursal_id);
        }
        if (!$sucursal) {
            $this->db->where('estatus', 'Activa');
            $this->db->order_by('id', 'ASC');
            $sucursal = $this->db->get('sucursales')->row();
        }

        $logo = null;
        $texto = null;
        if ($sucursal) {
            $logo = $sucursal->logo_marca_agua ?? null;
            $texto = $sucursal->texto_marca_agua ?? null;
            if (empty($texto)) {
                $texto = 'Sucursal: ' . $sucursal->nombre;
            }
        }

        if (empty($logo)) {
            $this->load->model('Config/EmpresaModel');
            $empresa = $this->EmpresaModel->get_config();
            $logo = $empresa->logo ?? null;
        }

        return [
            'logo' => $logo,
            'texto' => $texto ?: '',
        ];
    }
}

