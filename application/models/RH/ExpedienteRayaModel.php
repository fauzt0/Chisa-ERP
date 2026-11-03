<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Catálogo de archivos de lista de raya / comidas / bancos del dossier.
 * No sustituye nominas ni inventa CURP/NSS.
 */
class ExpedienteRayaModel extends CI_Model {

    public function asegurar() {
        if (!$this->db->table_exists('rh_expediente_raya')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS rh_expediente_raya (
                id INT(11) NOT NULL AUTO_INCREMENT,
                tipo VARCHAR(40) NOT NULL,
                nombre VARCHAR(255) NOT NULL,
                ruta VARCHAR(500) NOT NULL,
                semana VARCHAR(120) NULL,
                tamano INT(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                UNIQUE KEY uk_raya_ruta (ruta(191))
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        if (!$this->db->table_exists('rh_alta_pendiente')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS rh_alta_pendiente (
                id INT(11) NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(200) NOT NULL,
                rfc VARCHAR(13) NULL,
                curp VARCHAR(18) NULL,
                nss VARCHAR(11) NULL,
                puesto VARCHAR(100) NULL,
                origen VARCHAR(255) NULL,
                empleado_id INT(11) NULL,
                estatus VARCHAR(20) NOT NULL DEFAULT 'Pendiente',
                PRIMARY KEY (id),
                UNIQUE KEY uk_alta_rfc (rfc)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
    }

    public function clasificar_nombre($nombre) {
        $n = mb_strtoupper((string) $nombre, 'UTF-8');
        if (strpos($n, 'COMIDA') !== false) {
            return 'comidas';
        }
        if (strpos($n, 'BANCO') !== false || strpos($n, 'CUENTAS BANORTE') !== false) {
            return 'pagos';
        }
        if (strpos($n, 'ASISTENCIA') !== false) {
            return 'asistencia';
        }
        if (strpos($n, 'IMSS') !== false) {
            return 'imss';
        }
        if (strpos($n, 'IMPUESTO') !== false) {
            return 'impuestos';
        }
        if (strpos($n, 'EMPLEADO') !== false) {
            return 'trabajadores';
        }
        if (strpos($n, 'VENTAS AL') !== false) {
            return 'clientes';
        }
        if (strpos($n, 'PRECIO') !== false || strpos($n, 'LASTA DE PRECIOS') !== false) {
            return 'precios';
        }
        if (strpos($n, 'GASTOS PARA OBRAS') !== false || strpos($n, 'VIATIC') !== false || strpos($n, 'GASOLINA') !== false) {
            return 'viaticos';
        }
        return 'adjunto';
    }

    public function indexar_dossier() {
        $this->asegurar();
        $root = FCPATH . 'doc/entrenamiento_6/contabilidad';
        if (!is_dir($root)) {
            return 0;
        }
        $n = 0;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (!$file->isFile()) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['xlsx', 'xlsm', 'xls', 'pdf', 'doc', 'docx'], true)) {
                continue;
            }
            $full = $file->getPathname();
            $rel = 'doc/entrenamiento_6/contabilidad/' . ltrim(str_replace('\\', '/', substr($full, strlen($root))), '/');
            $existe = $this->db->where('ruta', $rel)->count_all_results('rh_expediente_raya') > 0;
            if ($existe) {
                continue;
            }
            $this->db->insert('rh_expediente_raya', [
                'tipo' => $this->clasificar_nombre($file->getFilename()),
                'nombre' => $file->getFilename(),
                'ruta' => $rel,
                'semana' => basename(dirname($full)),
                'tamano' => (int) $file->getSize(),
            ]);
            $n++;
        }
        return $n;
    }

    public function listar($tipo = '') {
        $this->asegurar();
        if ($tipo !== '') {
            $this->db->where('tipo', $tipo);
        }
        $this->db->order_by('tipo', 'ASC');
        $this->db->order_by('nombre', 'ASC');
        return $this->db->get('rh_expediente_raya')->result();
    }

    public function conteos() {
        $this->asegurar();
        $rows = $this->db->select('tipo, COUNT(*) as n')->group_by('tipo')->get('rh_expediente_raya')->result();
        $out = [];
        foreach ($rows as $r) {
            $out[$r->tipo] = (int) $r->n;
        }
        return $out;
    }

    public function registrar_pendientes_monterrey() {
        $this->asegurar();
        $filas = [
            ['GERARDO ALMAZAN FELIPE', 'AAFG880507FF3', 'AAFG880507HMCLLR01', '45068821664', 'APLICADOR'],
            ['FIDEL MARTINEZ MORALES', 'MAMF780424V31', 'MAMF780424HMCRRD10', '45997804765', 'APLICADOR'],
            ['LUIS ROBERTO FELIPE SALVADOR', 'FESL990810UE2', 'FESL990810HMCLLS04', '07169997629', 'APLICADOR'],
            ['RAYMUNDO MARTINEZ GIL', 'MAGR991213BU9', 'MAGR991213HMCRLY03', '37169993278', 'APLICADOR'],
            ['OMAR ALEXANDER HERNANDEZ MONTES', 'HEMO9709038N1', 'HEMO970903HNTRNM01', '75169781244', 'SUPERINTENDENTE'],
        ];
        $n = 0;
        foreach ($filas as $f) {
            $emp = $this->db->where('rfc', $f[1])->get('empleados')->row();
            $row = [
                'nombre' => $f[0],
                'rfc' => $f[1],
                'curp' => $f[2],
                'nss' => $f[3],
                'puesto' => $f[4],
                'origen' => 'doc/entrenamiento_6/contabilidad/.../EMPLEADOS PARA MONTERREY 30-09-2026.xlsx',
                'empleado_id' => $emp ? (int) $emp->id : null,
                'estatus' => $emp ? 'En RH' : 'Pendiente',
            ];
            $ya = $this->db->where('rfc', $f[1])->get('rh_alta_pendiente')->row();
            if ($ya) {
                $this->db->where('id', $ya->id)->update('rh_alta_pendiente', $row);
            } else {
                $this->db->insert('rh_alta_pendiente', $row);
                $n++;
            }
        }
        return $n;
    }

    public function pendientes_alta() {
        $this->asegurar();
        return $this->db->order_by('estatus', 'DESC')->get('rh_alta_pendiente')->result();
    }

    public function listar_tipo($tipo, $limite = 20) {
        $this->asegurar();
        $this->db->where('tipo', $tipo);
        $this->db->order_by('nombre', 'ASC');
        $this->db->limit((int) $limite);
        return $this->db->get('rh_expediente_raya')->result();
    }
}
