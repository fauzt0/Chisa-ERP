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
            $columnas = [
                'email' => "ALTER TABLE sucursales ADD COLUMN email VARCHAR(120) NULL",
                'sitio_web' => "ALTER TABLE sucursales ADD COLUMN sitio_web VARCHAR(180) NULL",
                'contacto' => "ALTER TABLE sucursales ADD COLUMN contacto VARCHAR(150) NULL",
                'ciudad' => "ALTER TABLE sucursales ADD COLUMN ciudad VARCHAR(120) NULL",
                'logo' => "ALTER TABLE sucursales ADD COLUMN logo VARCHAR(255) NULL",
                'icono' => "ALTER TABLE sucursales ADD COLUMN icono VARCHAR(255) NULL",
                'pie_cotizacion' => "ALTER TABLE sucursales ADD COLUMN pie_cotizacion TEXT NULL",
                'notas_cotizacion' => "ALTER TABLE sucursales ADD COLUMN notas_cotizacion TEXT NULL",
                'forma_pago' => "ALTER TABLE sucursales ADD COLUMN forma_pago TEXT NULL",
            ];
            foreach ($columnas as $campo => $sql) {
                if (!$this->db->field_exists($campo, 'sucursales')) {
                    $this->db->query($sql);
                }
            }
        }

        $this->load->helper('papeleria_hapa');
        $this->asegurar_sucursal_chisa();
        $this->asegurar_sucursal_hapa();
        $this->asegurar_empresa_matriz_chisa();
    }

    /**
     * Empresa matriz = CHISA. Sucursal SUC-MATRIZ lleva domicilio y papelería CHISA.
     */
    private function asegurar_sucursal_chisa() {
        $logo = 'assets/dist/img/brands/chisa_recubrimientos_logo.jpg';
        $datos = [
            'nombre' => 'CHISA Recubrimientos (Matriz)',
            'direccion' => 'Andrés Molina Enríquez #156, Col. San Pedro Iztacalco, C.P. 08220',
            'ciudad' => 'Ciudad de México',
            'telefono' => '55-5579-9434',
            'email' => 'info@chisarecubrimientos.com.mx',
            'sitio_web' => 'www.chisarecubrimientos.com.mx',
            'estatus' => 'Activa',
            'texto_marca_agua' => 'CHISA',
            'logo' => $logo,
            'logo_marca_agua' => $logo,
            'pie_cotizacion' => 'CHISA Recubrimientos, S.A. de C.V.',
            'notas_cotizacion' => papeleria_chisa_notas(),
            'forma_pago' => papeleria_hapa_forma_pago(),
        ];
        $this->db->where('codigo', 'SUC-MATRIZ');
        if ($this->db->count_all_results('sucursales') === 0) {
            $datos['codigo'] = 'SUC-MATRIZ';
            $this->db->insert('sucursales', $datos);
            return;
        }
        $this->db->where('codigo', 'SUC-MATRIZ')->update('sucursales', $datos);
    }

    /**
     * HAPA es otra sucursal (papelería de obra). No usa el logo de CHISA.
     */
    private function asegurar_sucursal_hapa() {
        $datos = [
            'nombre' => 'HAPA Soluciones Integrales en Recubrimientos',
            'direccion' => 'Calle Moras 506 Int. 10, Col. Del Valle, Benito Juárez',
            'ciudad' => 'Ciudad de México',
            'telefono' => '55-5909-4381 / 729-107-7726',
            'email' => 'contacto@hapasoluciones.com',
            'sitio_web' => 'www.hapasoluciones.com',
            'estatus' => 'Activa',
            'texto_marca_agua' => 'HAPA',
            'pie_cotizacion' => 'HAPA Soluciones Integrales en Recubrimientos.',
            'notas_cotizacion' => papeleria_hapa_notas(),
            'forma_pago' => papeleria_hapa_forma_pago(),
        ];
        $actual = $this->get_por_codigo('SUC-HAPA');
        if (!$actual) {
            $datos['codigo'] = 'SUC-HAPA';
            $this->db->insert('sucursales', $datos);
            return;
        }
        foreach (['logo', 'logo_marca_agua'] as $campo) {
            $ruta = (string) ($actual->$campo ?? '');
            if ($ruta === '' || strpos($ruta, 'chisa_recubrimientos') !== false) {
                $datos[$campo] = null;
            }
        }
        $this->db->where('codigo', 'SUC-HAPA')->update('sucursales', $datos);
    }

    private function asegurar_empresa_matriz_chisa() {
        if (!$this->db->table_exists('configuracion_empresa')) {
            return;
        }
        $row = $this->db->where('id', 1)->get('configuracion_empresa')->row();
        if (!$row) {
            return;
        }
        $upd = [];
        if (empty($row->razon_social) || $row->razon_social === 'Chisa Recubrimientos') {
            $upd['razon_social'] = 'CHISA Recubrimientos, S.A. de C.V.';
        }
        if (empty($row->nombre_comercial)) {
            $upd['nombre_comercial'] = 'CHISA';
        }
        if (empty($row->calle)) {
            $upd['calle'] = 'Andrés Molina Enríquez';
            $upd['numero_exterior'] = '156';
            $upd['colonia'] = 'San Pedro Iztacalco';
            $upd['ciudad'] = 'Ciudad de México';
            $upd['estado'] = 'CDMX';
            $upd['codigo_postal'] = '08220';
        }
        if (empty($row->telefono)) {
            $upd['telefono'] = '55-5579-9434';
        }
        if (empty($row->email)) {
            $upd['email'] = 'info@chisarecubrimientos.com.mx';
        }
        if (empty($row->sitio_web)) {
            $upd['sitio_web'] = 'www.chisarecubrimientos.com.mx';
        }
        if (empty($row->logo)) {
            $upd['logo'] = 'assets/dist/img/brands/chisa_recubrimientos_logo.jpg';
        }
        if ($upd) {
            $this->db->where('id', 1)->update('configuracion_empresa', $upd);
        }
    }

    public function listar_activas() {
        $this->asegurar_infraestructura();
        return $this->db->query(
            "SELECT * FROM sucursales WHERE estatus = 'Activa'
             ORDER BY CASE codigo WHEN 'SUC-MATRIZ' THEN 0 WHEN 'SUC-HAPA' THEN 1 ELSE 2 END, nombre ASC"
        )->result();
    }

    public function get($id) {
        $this->db->where('id', (int) $id);
        return $this->db->get('sucursales')->row();
    }

    public function get_por_codigo($codigo) {
        $codigo = strtoupper(trim((string) $codigo));
        if ($codigo === '') {
            return null;
        }
        return $this->db->where('codigo', $codigo)->get('sucursales')->row();
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
        return $this->db->query(
            "SELECT * FROM sucursales
             ORDER BY CASE estatus WHEN 'Activa' THEN 0 ELSE 1 END,
                      CASE codigo WHEN 'SUC-MATRIZ' THEN 0 WHEN 'SUC-HAPA' THEN 1 ELSE 2 END,
                      nombre ASC"
        )->result();
    }

    /**
     * Actualiza campos de una sucursal, incluida su marca de agua
     * (texto_marca_agua / logo_marca_agua) usada por ExportacionObraModel.
     * Cadena vacía = NULL. get_marca_agua() usa el logo de la empresa matriz
     * (CHISA) solo si la sucursal no es HAPA.
     */
    public function actualizar($id, array $data) {
        $this->asegurar_infraestructura();
        if (!$this->get($id)) {
            return ['success' => false, 'message' => 'Sucursal no encontrada'];
        }

        $permitidas = [
            'nombre', 'direccion', 'telefono', 'estatus', 'texto_marca_agua', 'logo_marca_agua',
            'email', 'sitio_web', 'contacto', 'ciudad', 'logo', 'icono', 'pie_cotizacion',
            'notas_cotizacion', 'forma_pago',
        ];
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
     * Sin sucursal_id usa SUC-MATRIZ (CHISA). HAPA no hereda el logo de la matriz.
     */
    public function get_marca_agua($sucursal_id = null) {
        $this->asegurar_infraestructura();

        $sucursal = null;
        if (!empty($sucursal_id)) {
            $sucursal = $this->get($sucursal_id);
        }
        if (!$sucursal) {
            $sucursal = $this->get_por_codigo('SUC-MATRIZ');
        }

        $logo = null;
        $texto = null;
        $es_hapa = false;
        if ($sucursal) {
            $es_hapa = strtoupper((string) $sucursal->codigo) === 'SUC-HAPA';
            $logo = $sucursal->logo_marca_agua ?? null;
            $texto = $sucursal->texto_marca_agua ?? null;
            if (empty($texto)) {
                $texto = 'Sucursal: ' . $sucursal->nombre;
            }
        }

        if (empty($logo) && !$es_hapa) {
            $this->load->model('Config/EmpresaModel');
            $empresa = $this->EmpresaModel->get_config();
            $logo = $empresa->logo ?? null;
        }

        $logo_doc = null;
        $icono = null;
        if ($sucursal) {
            $logo_doc = $sucursal->logo ?? null;
            $icono = $sucursal->icono ?? null;
        }
        if (empty($logo_doc) && !$es_hapa) {
            $logo_doc = $logo;
        }

        return [
            'logo' => $logo,
            'texto' => $texto ?: '',
            'logo_documento' => $logo_doc,
            'icono' => $icono,
            'codigo' => $sucursal->codigo ?? '',
            'nombre' => $sucursal->nombre ?? '',
            'direccion' => $sucursal->direccion ?? '',
            'telefono' => $sucursal->telefono ?? '',
            'email' => $sucursal->email ?? '',
            'sitio_web' => $sucursal->sitio_web ?? '',
            'ciudad' => $sucursal->ciudad ?? '',
            'contacto' => $sucursal->contacto ?? '',
            'pie_cotizacion' => $sucursal->pie_cotizacion ?? '',
            'notas_cotizacion' => $sucursal->notas_cotizacion ?? '',
            'forma_pago' => $sucursal->forma_pago ?? '',
        ];
    }

    /**
     * Guarda logo, icono o marca de agua de una sucursal.
     * $slot: logo | icono | logo_marca_agua
     */
    public function guardar_archivo($id, $slot) {
        $this->asegurar_infraestructura();
        $slots = ['logo', 'icono', 'logo_marca_agua'];
        if (!in_array($slot, $slots, true)) {
            return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
        }
        if (!$this->get($id)) {
            return ['success' => false, 'message' => 'Sucursal no encontrada'];
        }
        if (empty($_FILES['archivo']['name'])) {
            return ['success' => false, 'message' => 'Selecciona una imagen'];
        }

        $dir = FCPATH . 'uploads/sucursales/' . (int) $id . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $CI =& get_instance();
        $CI->load->library('upload');
        $CI->upload->initialize([
            'upload_path' => $dir,
            'allowed_types' => 'gif|jpg|jpeg|png|webp',
            'encrypt_name' => true,
            'max_size' => 4096,
        ]);
        if (!$CI->upload->do_upload('archivo')) {
            return ['success' => false, 'message' => strip_tags($CI->upload->display_errors('', ''))];
        }
        $info = $CI->upload->data();
        $ruta = 'uploads/sucursales/' . (int) $id . '/' . $info['file_name'];
        $this->db->where('id', (int) $id)->update('sucursales', [$slot => $ruta]);
        return ['success' => true, 'message' => 'Imagen actualizada', 'ruta' => $ruta, 'sucursal' => $this->get($id)];
    }
}

