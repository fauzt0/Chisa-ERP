<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cotización comercial de obra (papelería de la vendedora).
 * No es ordenes_venta.estatus=Cotización, ni cotizaciones de proveedor, ni PRES-.
 */
class CotizacionesObraModel extends CI_Model {

    public function asegurar_infraestructura() {
        if (!$this->db->table_exists('cotizaciones_comerciales')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS cotizaciones_comerciales (
                id INT(11) NOT NULL AUTO_INCREMENT,
                folio VARCHAR(40) NOT NULL,
                sucursal_id INT(11) NULL,
                cliente_id INT(11) NULL,
                obra_id INT(11) NULL,
                constructora VARCHAR(200) NULL,
                atencion VARCHAR(150) NULL,
                obra_nombre VARCHAR(200) NULL,
                ubicacion VARCHAR(255) NULL,
                pres_ref VARCHAR(80) NULL,
                fecha DATE NULL,
                introduccion VARCHAR(500) NULL,
                estatus ENUM('Borrador','Enviada','Aceptada','Rechazada','Cancelada') NOT NULL DEFAULT 'Borrador',
                observaciones TEXT NULL,
                creado_por INT(11) NULL,
                fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_cco_folio (folio),
                KEY idx_cco_cliente (cliente_id),
                KEY idx_cco_sucursal (sucursal_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Cotización comercial de obra (CRM), distinta de PRES- y de cotizaciones de compra'");
        }
        if (!$this->db->table_exists('cotizaciones_comerciales_detalle')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS cotizaciones_comerciales_detalle (
                id INT(11) NOT NULL AUTO_INCREMENT,
                cotizacion_id INT(11) NOT NULL,
                orden INT(11) NOT NULL DEFAULT 0,
                grupo VARCHAR(160) NULL,
                concepto TEXT NULL,
                unidad VARCHAR(20) NULL,
                cantidad DECIMAL(14,2) NOT NULL DEFAULT 0,
                precio_unitario DECIMAL(14,2) NOT NULL DEFAULT 0,
                importe DECIMAL(14,2) NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                KEY idx_cco_det (cotizacion_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        if (!$this->db->table_exists('conceptos_comerciales')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS conceptos_comerciales (
                id INT(11) NOT NULL AUTO_INCREMENT,
                concepto VARCHAR(500) NOT NULL,
                unidad VARCHAR(20) NULL,
                precio_referencia DECIMAL(14,2) NOT NULL DEFAULT 0,
                modelo VARCHAR(80) NULL,
                huella CHAR(40) NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (id),
                UNIQUE KEY uk_concepto_huella (huella)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            COMMENT='Catálogo editable de conceptos para cotizaciones comerciales de obra'");
        }
    }

    public function generar_folio() {
        $this->asegurar_infraestructura();
        $prefijo = 'CCO-' . date('Y') . '-';
        $pos = strlen($prefijo) + 1;
        $like = $this->db->escape($prefijo . '%');
        $this->db->query('SET NAMES utf8mb4');
        $row = $this->db->query(
            "SELECT MAX(CAST(SUBSTRING(folio, {$pos}) AS UNSIGNED)) AS max_folio
             FROM cotizaciones_comerciales
             WHERE folio LIKE {$like}"
        )->row();
        $numero = ($row && $row->max_folio !== null) ? (int) $row->max_folio + 1 : 1;
        return $prefijo . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    public function listar($limite = 80) {
        $this->asegurar_infraestructura();
        $this->db->select('c.*, clientes.razon_social, sucursales.nombre AS sucursal_nombre');
        $this->db->from('cotizaciones_comerciales c');
        $this->db->join('clientes', 'clientes.id = c.cliente_id', 'left');
        $this->db->join('sucursales', 'sucursales.id = c.sucursal_id', 'left');
        $this->db->order_by('c.id', 'DESC');
        $this->db->limit((int) $limite);
        return $this->db->get()->result();
    }

    public function get($id) {
        $this->asegurar_infraestructura();
        $cot = $this->db->where('id', (int) $id)->get('cotizaciones_comerciales')->row();
        if (!$cot) {
            return null;
        }
        $cot->detalles = $this->db->where('cotizacion_id', (int) $id)
            ->order_by('orden', 'ASC')->order_by('id', 'ASC')
            ->get('cotizaciones_comerciales_detalle')->result();
        $cot->total = 0;
        foreach ($cot->detalles as $d) {
            $cot->total += (float) $d->importe;
        }
        return $cot;
    }

    public function guardar($data, $lineas, $user_id) {
        $this->asegurar_infraestructura();
        $id = (int) ($data['id'] ?? 0);
        $cab = [
            'sucursal_id' => (int) ($data['sucursal_id'] ?? 0) ?: null,
            'cliente_id' => (int) ($data['cliente_id'] ?? 0) ?: null,
            'obra_id' => (int) ($data['obra_id'] ?? 0) ?: null,
            'constructora' => $this->texto($data['constructora'] ?? '', 200),
            'atencion' => $this->texto($data['atencion'] ?? '', 150),
            'obra_nombre' => $this->texto($data['obra_nombre'] ?? '', 200),
            'ubicacion' => $this->texto($data['ubicacion'] ?? '', 255),
            'pres_ref' => $this->texto($data['pres_ref'] ?? '', 80),
            'fecha' => $this->fecha($data['fecha'] ?? ''),
            'introduccion' => $this->texto($data['introduccion'] ?? '', 500),
            'observaciones' => $this->texto($data['observaciones'] ?? '', 2000),
        ];
        if ($cab['obra_nombre'] === null && $cab['constructora'] === null) {
            return ['success' => false, 'message' => 'Indica la obra o la constructora'];
        }

        $this->db->trans_start();
        if ($id > 0) {
            $actual = $this->db->where('id', $id)->get('cotizaciones_comerciales')->row();
            if (!$actual) {
                return ['success' => false, 'message' => 'Cotización no encontrada'];
            }
            if (in_array($actual->estatus, ['Cancelada', 'Aceptada'], true)) {
                return ['success' => false, 'message' => 'Esta cotización ya no se puede editar'];
            }
            $this->db->where('id', $id)->update('cotizaciones_comerciales', $cab);
            $folio = $actual->folio;
            $this->db->where('cotizacion_id', $id)->delete('cotizaciones_comerciales_detalle');
        } else {
            $folio = $this->generar_folio();
            $cab['folio'] = $folio;
            $cab['estatus'] = 'Borrador';
            $cab['creado_por'] = (int) $user_id ?: null;
            $cab['fecha_creacion'] = date('Y-m-d H:i:s');
            $this->db->insert('cotizaciones_comerciales', $cab);
            $id = (int) $this->db->insert_id();
        }

        $orden = 1;
        foreach ((array) $lineas as $linea) {
            $concepto = trim((string) ($linea['concepto'] ?? ''));
            if ($concepto === '') {
                continue;
            }
            $cantidad = (float) ($linea['cantidad'] ?? 0);
            $precio = (float) ($linea['precio_unitario'] ?? 0);
            $this->db->insert('cotizaciones_comerciales_detalle', [
                'cotizacion_id' => $id,
                'orden' => $orden++,
                'grupo' => $this->texto($linea['grupo'] ?? '', 160),
                'concepto' => $concepto,
                'unidad' => $this->texto($linea['unidad'] ?? '', 20),
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'importe' => round($cantidad * $precio, 2),
            ]);
            $this->upsert_concepto($concepto, $linea['unidad'] ?? '', $precio);
        }
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            return ['success' => false, 'message' => 'No se pudo guardar la cotización'];
        }
        return ['success' => true, 'message' => 'Cotización ' . $folio . ' guardada', 'id' => $id, 'folio' => $folio];
    }

    public function cambiar_estatus($id, $estatus) {
        $this->asegurar_infraestructura();
        $validos = ['Borrador', 'Enviada', 'Aceptada', 'Rechazada', 'Cancelada'];
        if (!in_array($estatus, $validos, true)) {
            return ['success' => false, 'message' => 'Estatus no válido'];
        }
        $cot = $this->db->where('id', (int) $id)->get('cotizaciones_comerciales')->row();
        if (!$cot) {
            return ['success' => false, 'message' => 'Cotización no encontrada'];
        }
        $this->db->where('id', (int) $id)->update('cotizaciones_comerciales', ['estatus' => $estatus]);
        return ['success' => true, 'message' => 'Estatus actualizado a ' . $estatus, 'folio' => $cot->folio];
    }

    public function buscar_conceptos($q, $limite = 20) {
        $this->asegurar_infraestructura();
        $q = trim((string) $q);
        if ($q !== '') {
            $this->db->like('concepto', $q);
        }
        $this->db->where('activo', 1);
        $this->db->order_by('id', 'DESC');
        $this->db->limit((int) $limite);
        return $this->db->get('conceptos_comerciales')->result();
    }

    public function guardar_concepto_catalogo($data) {
        $this->asegurar_infraestructura();
        $concepto = trim((string) ($data['concepto'] ?? ''));
        if ($concepto === '') {
            return ['success' => false, 'message' => 'El concepto es obligatorio'];
        }
        $id = $this->upsert_concepto($concepto, $data['unidad'] ?? '', (float) ($data['precio_referencia'] ?? 0), $data['modelo'] ?? '');
        return ['success' => true, 'id' => $id, 'message' => 'Concepto guardado en el catálogo'];
    }

    public function buscar_clientes($q) {
        $q = trim((string) $q);
        $this->db->select('id, codigo, razon_social, rfc, email');
        $this->db->from('clientes');
        $this->db->where('estatus', 'Activo');
        if ($q !== '') {
            $this->db->group_start();
            $this->db->like('razon_social', $q);
            $this->db->or_like('nombre_comercial', $q);
            $this->db->or_like('rfc', $q);
            $this->db->group_end();
        }
        $this->db->order_by('razon_social', 'ASC');
        $this->db->limit(25);
        return $this->db->get()->result();
    }

    /**
     * Lee como máximo los 5 xlsx más ligeros (<= 200 KB) de entrenamiento 5.
     */
    public function sembrar_ejemplos($directorio, $max_archivos = 5, $max_bytes = 200000) {
        $this->asegurar_infraestructura();
        if (!is_dir($directorio)) {
            return ['success' => false, 'message' => 'No está la carpeta de ejemplos'];
        }
        $files = [];
        foreach (scandir($directorio) as $nombre) {
            if ($nombre === '.' || $nombre === '..') {
                continue;
            }
            $path = $directorio . DIRECTORY_SEPARATOR . $nombre;
            if (!is_file($path)) {
                continue;
            }
            $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            if ($ext !== 'xlsx') {
                continue;
            }
            $sz = filesize($path);
            if ($sz <= 0 || $sz > $max_bytes) {
                continue;
            }
            $files[] = ['path' => $path, 'nombre' => $nombre, 'sz' => $sz];
        }
        usort($files, function ($a, $b) {
            return $a['sz'] <=> $b['sz'];
        });
        $files = array_slice($files, 0, (int) $max_archivos);

        require_once APPPATH . '../vendor/autoload.php';
        $resumen = ['archivos' => [], 'conceptos_nuevos' => 0, 'clientes_nuevos' => 0, 'cotizaciones' => []];
        foreach ($files as $file) {
            $leido = $this->leer_xlsx_ligero($file['path']);
            $nombre_cliente = $leido['constructora'] ?: $this->cliente_desde_nombre($file['nombre']);
            $cliente_id = null;
            if ($nombre_cliente) {
                $alta = $this->asegurar_cliente($nombre_cliente);
                if ($alta['nuevo']) {
                    $resumen['clientes_nuevos']++;
                }
                $cliente_id = $alta['id'];
            }
            $nuevos = 0;
            foreach ($leido['lineas'] as $linea) {
                $antes = $this->db->where('huella', $this->huella($linea['concepto']))->count_all_results('conceptos_comerciales');
                $this->upsert_concepto($linea['concepto'], $linea['unidad'], $linea['precio_unitario'], $linea['modelo'] ?? '');
                if ($antes === 0) {
                    $nuevos++;
                }
            }
            $resumen['conceptos_nuevos'] += $nuevos;
            $cot_id = null;
            if (count($leido['lineas']) >= 1) {
                $ref = $this->texto($file['nombre'], 80);
                $existe = $this->db->where('pres_ref', $ref)->get('cotizaciones_comerciales')->row();
                if ($existe) {
                    if (empty($existe->cliente_id) && $cliente_id) {
                        $this->db->where('id', $existe->id)->update('cotizaciones_comerciales', [
                            'cliente_id' => $cliente_id,
                            'constructora' => $existe->constructora ?: $nombre_cliente,
                        ]);
                    }
                } else {
                    $guardado = $this->guardar([
                        'cliente_id' => $cliente_id,
                        'constructora' => $leido['constructora'] ?: $this->cliente_desde_nombre($file['nombre']),
                        'atencion' => $leido['atencion'],
                        'obra_nombre' => $leido['obra'] ?: $this->cliente_desde_nombre($file['nombre']),
                        'pres_ref' => $ref,
                        'fecha' => date('Y-m-d'),
                        'introduccion' => $leido['introduccion'] ?: 'Presupuesto de suministro y aplicación.',
                        'observaciones' => 'Cargada desde entrenamiento 5 (archivo ligero)',
                    ], $leido['lineas'], 1);
                    $cot_id = $guardado['folio'] ?? null;
                }
                if ($existe) {
                    $cot_id = $existe->folio;
                }
            }
            $resumen['archivos'][] = $file['nombre'] . ' (' . $file['sz'] . ' B, ' . count($leido['lineas']) . ' líneas)';
            if ($cot_id) {
                $resumen['cotizaciones'][] = $cot_id;
            }
        }
        $resumen['success'] = true;
        $resumen['message'] = 'Ejemplos leídos: ' . count($files) . ' archivos ligeros';
        return $resumen;
    }

    private function leer_xlsx_ligero($path) {
        $wb = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $ws = $wb->getSheet(0);
        $max = min(120, (int) $ws->getHighestRow());
        $out = [
            'constructora' => null,
            'atencion' => null,
            'obra' => null,
            'introduccion' => null,
            'lineas' => [],
        ];
        $colConcepto = null;
        $colUnidad = null;
        $colCantidad = null;
        $colPrecio = null;
        $colModelo = null;
        for ($r = 1; $r <= $max; $r++) {
            $vals = [];
            foreach (range('A', 'L') as $col) {
                $v = $ws->getCell($col . $r)->getValue();
                if ($v === null || $v === '') {
                    continue;
                }
                $vals[$col] = trim(preg_replace('/\s+/', ' ', (string) $v));
            }
            if (!$vals) {
                continue;
            }
            $texto = implode(' ', $vals);
            if ($colConcepto === null) {
                if (stripos($texto, 'CONSTRUCTORA') !== false) {
                    $out['constructora'] = $this->despues_de($texto, 'CONSTRUCTORA');
                } elseif (preg_match('/AT[´\'’]?N/iu', $texto)) {
                    $out['atencion'] = trim(preg_replace('/^.*AT[´\'’]?N\s*:?\s*/iu', '', $texto));
                } elseif (stripos($texto, 'OBRA:') !== false) {
                    $out['obra'] = $this->despues_de($texto, 'OBRA');
                } elseif (stripos($texto, 'PRESENTAMOS') !== false) {
                    $out['introduccion'] = $texto;
                }
                foreach ($vals as $col => $v) {
                    if (mb_strtoupper($v) === 'CONCEPTO') {
                        $colConcepto = $col;
                    } elseif (in_array(mb_strtoupper($v), ['UNIDAD', 'VOLUMEN'], true)) {
                        $colUnidad = $col;
                    } elseif (mb_strtoupper($v) === 'CANTIDAD') {
                        $colCantidad = $col;
                    } elseif (mb_strtoupper($v) === 'MODELO') {
                        $colModelo = $col;
                    } elseif (preg_match('/PRECIO/i', $v)) {
                        $colPrecio = $col;
                    }
                }
                continue;
            }
            $concepto = $vals[$colConcepto] ?? '';
            if ($concepto === '' || mb_strtoupper($concepto) === 'CONCEPTO') {
                continue;
            }
            if (stripos($concepto, 'SUBTOTAL') !== false || stripos($concepto, 'TOTAL') === 0) {
                continue;
            }
            $unidad = $colUnidad ? ($vals[$colUnidad] ?? '') : '';
            $cantidad = $colCantidad ? $this->numero($vals[$colCantidad] ?? 0) : 0;
            $precio = 0;
            if ($colPrecio && isset($vals[$colPrecio]) && is_numeric($vals[$colPrecio])) {
                $precio = (float) $vals[$colPrecio];
            }
            if ($precio <= 0) {
                foreach ($vals as $v) {
                    if (is_numeric($v) && (float) $v > 1 && (float) $v < 1000000 && (float) $v != $cantidad) {
                        $precio = (float) $v;
                    }
                }
            }
            $out['lineas'][] = [
                'concepto' => mb_substr($concepto, 0, 500),
                'unidad' => mb_substr($unidad, 0, 20),
                'cantidad' => $cantidad,
                'precio_unitario' => $precio,
                'modelo' => $colModelo ? mb_substr($vals[$colModelo] ?? '', 0, 80) : '',
                'grupo' => '',
            ];
            if (count($out['lineas']) >= 80) {
                break;
            }
        }
        $wb->disconnectWorksheets();
        return $out;
    }

    private function asegurar_cliente($razon) {
        $razon = trim($razon);
        $row = $this->db->where('razon_social', $razon)->get('clientes')->row();
        if ($row) {
            return ['id' => (int) $row->id, 'nuevo' => false];
        }
        $hash = strtoupper(substr(md5($razon), 0, 10));
        $this->db->insert('clientes', [
            'codigo' => 'CCO-' . substr($hash, 0, 8),
            'razon_social' => mb_substr($razon, 0, 200),
            'rfc' => 'X' . substr($hash, 0, 12),
            'tipo_cliente' => 'Empresa',
            'estatus' => 'Activo',
        ]);
        return ['id' => (int) $this->db->insert_id(), 'nuevo' => true];
    }

    private function cliente_desde_nombre($nombre) {
        $base = preg_replace('/\.(xlsx|xls)$/i', '', $nombre);
        $base = preg_replace('/^\d+(?:-[A-Z])?\s+/', '', $base);
        $base = trim(str_replace(['-', '_'], ' ', $base));
        return $base !== '' ? $base : $nombre;
    }

    private function upsert_concepto($concepto, $unidad, $precio, $modelo = '') {
        $concepto = trim((string) $concepto);
        $huella = $this->huella($concepto);
        $row = $this->db->where('huella', $huella)->get('conceptos_comerciales')->row();
        $data = [
            'concepto' => mb_substr($concepto, 0, 500),
            'unidad' => $this->texto($unidad, 20),
            'precio_referencia' => (float) $precio,
            'modelo' => $this->texto($modelo, 80),
            'activo' => 1,
        ];
        if ($row) {
            if ((float) $precio > 0) {
                $this->db->where('id', $row->id)->update('conceptos_comerciales', $data);
            }
            return (int) $row->id;
        }
        $data['huella'] = $huella;
        $this->db->insert('conceptos_comerciales', $data);
        return (int) $this->db->insert_id();
    }

    private function huella($concepto) {
        $n = mb_strtoupper(trim(preg_replace('/\s+/', ' ', (string) $concepto)));
        return sha1($n);
    }

    private function texto($valor, $max) {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }
        return mb_substr($valor, 0, $max);
    }

    private function fecha($valor) {
        $valor = trim((string) $valor);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return $valor;
        }
        return date('Y-m-d');
    }

    private function numero($valor) {
        if (is_numeric($valor)) {
            return (float) $valor;
        }
        $valor = str_replace([',', '$', ' '], '', (string) $valor);
        return is_numeric($valor) ? (float) $valor : 0;
    }

    private function despues_de($texto, $etiqueta) {
        $pos = stripos($texto, $etiqueta);
        if ($pos === false) {
            return null;
        }
        $resto = trim(substr($texto, $pos + strlen($etiqueta)));
        $resto = ltrim($resto, " :.-");
        return $resto !== '' ? $resto : null;
    }
}
