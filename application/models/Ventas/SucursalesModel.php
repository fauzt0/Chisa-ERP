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
}
