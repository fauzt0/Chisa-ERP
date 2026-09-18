<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Puente de solo lectura: documentos operativos → pólizas en borrador.
 * No escribe en ventas, compras, facturación ni RH.
 */
#[AllowDynamicProperties]
class OrigenesModel extends CI_Model {

    private $cuentas_base = [
        ['codigo' => '1.1.01.001', 'nombre' => 'Caja', 'tipo_cuenta' => 'Activo', 'naturaleza' => 'Deudora', 'clave' => 'caja'],
        ['codigo' => '1.1.01.003', 'nombre' => 'Bancos', 'tipo_cuenta' => 'Activo', 'naturaleza' => 'Deudora', 'clave' => 'bancos'],
        ['codigo' => '1.1.02.001', 'nombre' => 'Clientes', 'tipo_cuenta' => 'Activo', 'naturaleza' => 'Deudora', 'clave' => 'clientes'],
        ['codigo' => '1.1.03.001', 'nombre' => 'Inventario de insumos', 'tipo_cuenta' => 'Activo', 'naturaleza' => 'Deudora', 'clave' => 'inventario'],
        ['codigo' => '1.1.04.001', 'nombre' => 'IVA acreditable', 'tipo_cuenta' => 'Activo', 'naturaleza' => 'Deudora', 'clave' => 'iva_acreditable'],
        ['codigo' => '2.1.01', 'nombre' => 'Proveedores', 'tipo_cuenta' => 'Pasivo', 'naturaleza' => 'Acreedora', 'clave' => 'proveedores'],
        ['codigo' => '2.1.02', 'nombre' => 'IVA trasladado', 'tipo_cuenta' => 'Pasivo', 'naturaleza' => 'Acreedora', 'clave' => 'iva_trasladado'],
        ['codigo' => '2.1.03', 'nombre' => 'Acreedores nómina', 'tipo_cuenta' => 'Pasivo', 'naturaleza' => 'Acreedora', 'clave' => 'acreedores_nomina'],
        ['codigo' => '2.1.05', 'nombre' => 'ISR por Pagar', 'tipo_cuenta' => 'Pasivo', 'naturaleza' => 'Acreedora', 'clave' => 'isr_por_pagar'],
        ['codigo' => '2.1.06', 'nombre' => 'IMSS por Pagar', 'tipo_cuenta' => 'Pasivo', 'naturaleza' => 'Acreedora', 'clave' => 'imss_por_pagar'],
        ['codigo' => '3.1.01', 'nombre' => 'Capital social', 'tipo_cuenta' => 'Capital', 'naturaleza' => 'Acreedora', 'clave' => 'capital'],
        ['codigo' => '4.1.01', 'nombre' => 'Ventas', 'tipo_cuenta' => 'Ingresos', 'naturaleza' => 'Acreedora', 'clave' => 'ventas'],
        ['codigo' => '5.1.01', 'nombre' => 'Compras de insumos', 'tipo_cuenta' => 'Costos', 'naturaleza' => 'Deudora', 'clave' => 'compras'],
        ['codigo' => '6.1.01', 'nombre' => 'Sueldos y Salarios', 'tipo_cuenta' => 'Egresos', 'naturaleza' => 'Deudora', 'clave' => 'sueldos'],
    ];

    public function __construct() {
        parent::__construct();
        $this->load->model('Contabilidad/ContabilidadModel');
    }

    public function asegurar_infraestructura() {
        $this->_crear_tabla_mapeo();
        $this->_asegurar_cuentas();
        $this->_asegurar_ejercicio(date('Y'));
    }

    public function listar_pendientes() {
        $this->asegurar_infraestructura();
        return [
            'facturas' => $this->_facturas_cfdi(),
            'compras' => $this->_ordenes_compra(),
            'nominas' => $this->_nominas_pagadas(),
            'mapeo' => $this->get_mapeo(),
        ];
    }

    public function get_mapeo() {
        if (!$this->db->table_exists('contabilidad_mapeo_cuentas')) {
            return [];
        }
        $this->db->select('m.clave, m.cuenta_id, c.codigo, c.nombre');
        $this->db->from('contabilidad_mapeo_cuentas m');
        $this->db->join('cuentas_contables c', 'c.id = m.cuenta_id');
        $this->db->order_by('m.clave', 'ASC');
        return $this->db->get()->result();
    }

    public function resumen_iva($fecha_inicio, $fecha_fin) {
        $trasladado = 0;
        $acreditable = 0;
        $base_ingresos = 0;
        $base_compras = 0;

        $this->db->select('COALESCE(SUM(subtotal),0) as base, COALESCE(SUM(iva),0) as iva');
        $this->db->from('facturas');
        $this->db->where('estatus', 'Emitida');
        if ($fecha_inicio) {
            $this->db->where('DATE(fecha_emision) >=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $this->db->where('DATE(fecha_emision) <=', $fecha_fin);
        }
        $f = $this->db->get()->row();
        $base_ingresos = (float) $f->base;
        $trasladado = (float) $f->iva;

        $this->db->select('COALESCE(SUM(subtotal),0) as base, COALESCE(SUM(iva),0) as iva');
        $this->db->from('ordenes_compra');
        $this->db->where_in('estatus', ['Recibida', 'Recibida Parcial']);
        if ($fecha_inicio) {
            $this->db->where('fecha_orden >=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $this->db->where('fecha_orden <=', $fecha_fin);
        }
        $c = $this->db->get()->row();
        $base_compras = (float) $c->base;
        $acreditable = (float) $c->iva;

        return [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'base_ingresos' => $base_ingresos,
            'iva_trasladado' => $trasladado,
            'base_compras' => $base_compras,
            'iva_acreditable' => $acreditable,
            'iva_a_cargo' => round($trasladado - $acreditable, 2),
        ];
    }

    /**
     * @param string $origen facturas|compras|nominas|todos
     * @return array{creadas:int,omitidas:int,errores:array}
     */
    public function generar_polizas($origen = 'todos', $autorizar = false) {
        $this->asegurar_infraestructura();
        $creadas = 0;
        $omitidas = 0;
        $errores = [];

        $fuentes = [];
        if ($origen === 'todos' || $origen === 'facturas') {
            $fuentes = array_merge($fuentes, $this->_propuestas_facturas());
        }
        if ($origen === 'todos' || $origen === 'compras') {
            $fuentes = array_merge($fuentes, $this->_propuestas_compras());
        }
        if ($origen === 'todos' || $origen === 'nominas') {
            $fuentes = array_merge($fuentes, $this->_propuestas_nominas());
        }

        foreach ($fuentes as $p) {
            if (!empty($p['ya_existe'])) {
                $omitidas++;
                continue;
            }
            if (!empty($p['error'])) {
                $errores[] = $p['error'];
                continue;
            }
            $id = $this->_guardar_poliza($p, $autorizar);
            if ($id) {
                $creadas++;
            } else {
                $errores[] = 'No se pudo guardar póliza ' . $p['referencia'];
            }
        }

        return ['creadas' => $creadas, 'omitidas' => $omitidas, 'errores' => $errores];
    }

    public function propuestas($origen = 'todos') {
        $this->asegurar_infraestructura();
        $out = [];
        if ($origen === 'todos' || $origen === 'facturas') {
            $out = array_merge($out, $this->_propuestas_facturas());
        }
        if ($origen === 'todos' || $origen === 'compras') {
            $out = array_merge($out, $this->_propuestas_compras());
        }
        if ($origen === 'todos' || $origen === 'nominas') {
            $out = array_merge($out, $this->_propuestas_nominas());
        }
        return $out;
    }

    private function _crear_tabla_mapeo() {
        $sql = "CREATE TABLE IF NOT EXISTS contabilidad_mapeo_cuentas (
            clave VARCHAR(50) NOT NULL,
            cuenta_id INT(11) NOT NULL,
            descripcion VARCHAR(200) NULL,
            PRIMARY KEY (clave),
            KEY idx_cuenta (cuenta_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        COMMENT='Mapeo interno ERP → catálogo de cuentas (I4)'";
        $this->db->query($sql);
    }

    private function _asegurar_cuentas() {
        foreach ($this->cuentas_base as $def) {
            $this->db->where('codigo', $def['codigo']);
            $row = $this->db->get('cuentas_contables')->row();
            if (!$row) {
                $this->db->insert('cuentas_contables', [
                    'codigo' => $def['codigo'],
                    'nombre' => $def['nombre'],
                    'tipo_cuenta' => $def['tipo_cuenta'],
                    'naturaleza' => $def['naturaleza'],
                    'nivel' => substr_count($def['codigo'], '.') + 1,
                    'es_afectable' => 1,
                    'estatus' => 'Activa',
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                ]);
                $cuenta_id = (int) $this->db->insert_id();
            } else {
                $cuenta_id = (int) $row->id;
            }
            $this->db->replace('contabilidad_mapeo_cuentas', [
                'clave' => $def['clave'],
                'cuenta_id' => $cuenta_id,
                'descripcion' => $def['nombre'],
            ]);
        }
    }

    private function _asegurar_ejercicio($anio) {
        $anio = (int) $anio;
        $this->db->where('año', $anio);
        $ej = $this->db->get('ejercicios_fiscales')->row();
        if (!$ej) {
            $this->db->insert('ejercicios_fiscales', [
                'año' => $anio,
                'fecha_inicio' => $anio . '-01-01',
                'fecha_fin' => $anio . '-12-31',
                'estatus' => 'Abierto',
                'fecha_creacion' => date('Y-m-d H:i:s'),
            ]);
            $ej_id = (int) $this->db->insert_id();
        } else {
            $ej_id = (int) $ej->id;
        }

        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        for ($n = 1; $n <= 12; $n++) {
            $ini = sprintf('%04d-%02d-01', $anio, $n);
            $fin = date('Y-m-t', strtotime($ini));
            $this->db->where('ejercicio_id', $ej_id);
            $this->db->where('numero_periodo', $n);
            if ($this->db->count_all_results('periodos_contables') > 0) {
                continue;
            }
            $this->db->insert('periodos_contables', [
                'ejercicio_id' => $ej_id,
                'numero_periodo' => $n,
                'nombre' => $meses[$n - 1],
                'fecha_inicio' => $ini,
                'fecha_fin' => $fin,
                'estatus' => 'Abierto',
            ]);
        }
    }

    private function _cuenta($clave) {
        $this->db->select('cuenta_id');
        $this->db->from('contabilidad_mapeo_cuentas');
        $this->db->where('clave', $clave);
        $row = $this->db->get()->row();
        return $row ? (int) $row->cuenta_id : 0;
    }

    private function _facturas_cfdi() {
        $this->db->select('f.id, f.folio, f.folio_fiscal, f.fecha_emision, f.subtotal, f.iva, f.total, f.razon_social, f.estatus');
        $this->db->from('facturas f');
        $this->db->where('f.estatus', 'Emitida');
        $this->db->order_by('f.fecha_emision', 'DESC');
        $rows = $this->db->get()->result();
        foreach ($rows as $r) {
            $r->ya_poliza = $this->ContabilidadModel->poliza_origen_existente('facturas', $r->id);
        }
        return $rows;
    }

    private function _ordenes_compra() {
        $this->db->select('oc.id, oc.folio, oc.fecha_orden, oc.subtotal, oc.iva, oc.total, oc.estatus, p.razon_social');
        $this->db->from('ordenes_compra oc');
        $this->db->join('proveedores p', 'p.id = oc.proveedor_id', 'left');
        $this->db->where_in('oc.estatus', ['Recibida', 'Recibida Parcial']);
        $this->db->order_by('oc.fecha_orden', 'DESC');
        $rows = $this->db->get()->result();
        foreach ($rows as $r) {
            $r->ya_poliza = $this->ContabilidadModel->poliza_origen_existente('compras', $r->id);
        }
        return $rows;
    }

    private function _nominas_pagadas() {
        $this->db->select('id, folio, fecha_pago, tipo_nomina, total_percepciones, total_deducciones, total_neto, estatus');
        $this->db->from('nominas');
        $this->db->where_in('estatus', ['Pagada', 'Parcial']);
        $this->db->where('total_neto >', 0);
        $this->db->order_by('fecha_pago', 'DESC');
        $rows = $this->db->get()->result();
        foreach ($rows as $r) {
            $r->ya_poliza = $this->ContabilidadModel->poliza_origen_existente('nominas', $r->id);
        }
        return $rows;
    }

    private function _propuestas_facturas() {
        $out = [];
        foreach ($this->_facturas_cfdi() as $f) {
            $fecha = date('Y-m-d', strtotime($f->fecha_emision));
            $base = [
                'origen' => 'facturas',
                'origen_id' => (int) $f->id,
                'tipo_poliza' => 'Ingresos',
                'fecha' => $fecha,
                'referencia' => $f->folio,
                'concepto' => 'CFDI ' . $f->folio . ' ' . $f->razon_social,
                'ya_existe' => $f->ya_poliza,
                'detalle' => [],
            ];
            $periodo = $this->ContabilidadModel->get_periodo_por_fecha($fecha);
            if (!$periodo) {
                $base['error'] = 'Sin periodo contable para ' . $fecha . ' (factura ' . $f->folio . ')';
                $out[] = $base;
                continue;
            }
            $base['periodo_id'] = (int) $periodo->id;
            $sub = round((float) $f->subtotal, 2);
            $iva = round((float) $f->iva, 2);
            $tot = round((float) $f->total, 2);
            $base['detalle'] = [
                $this->_linea($this->_cuenta('clientes'), 'Clientes ' . $f->folio, $tot, 0, 1, 'cliente', null),
                $this->_linea($this->_cuenta('ventas'), 'Ventas ' . $f->folio, 0, $sub, 2),
            ];
            if ($iva > 0.009) {
                $base['detalle'][] = $this->_linea($this->_cuenta('iva_trasladado'), 'IVA trasladado 16%', 0, $iva, 3);
            }
            $out[] = $base;
        }
        return $out;
    }

    private function _propuestas_compras() {
        $out = [];
        foreach ($this->_ordenes_compra() as $oc) {
            $fecha = $oc->fecha_orden;
            $base = [
                'origen' => 'compras',
                'origen_id' => (int) $oc->id,
                'tipo_poliza' => 'Diario',
                'fecha' => $fecha,
                'referencia' => $oc->folio,
                'concepto' => 'OC ' . $oc->folio . ' ' . ($oc->razon_social ?: ''),
                'ya_existe' => $oc->ya_poliza,
                'detalle' => [],
            ];
            $periodo = $this->ContabilidadModel->get_periodo_por_fecha($fecha);
            if (!$periodo) {
                $base['error'] = 'Sin periodo contable para ' . $fecha . ' (OC ' . $oc->folio . ')';
                $out[] = $base;
                continue;
            }
            $base['periodo_id'] = (int) $periodo->id;
            $sub = round((float) $oc->subtotal, 2);
            $iva = round((float) $oc->iva, 2);
            $tot = round((float) $oc->total, 2);
            $n = 1;
            $base['detalle'][] = $this->_linea($this->_cuenta('inventario'), 'Insumos ' . $oc->folio, $sub, 0, $n++, 'proveedor', null);
            if ($iva > 0.009) {
                $base['detalle'][] = $this->_linea($this->_cuenta('iva_acreditable'), 'IVA acreditable 16%', $iva, 0, $n++);
            }
            $base['detalle'][] = $this->_linea($this->_cuenta('proveedores'), 'Proveedores ' . $oc->folio, 0, $tot, $n);
            $out[] = $base;
        }
        return $out;
    }

    private function _propuestas_nominas() {
        $out = [];
        foreach ($this->_nominas_pagadas() as $n) {
            $fecha = $n->fecha_pago;
            $base = [
                'origen' => 'nominas',
                'origen_id' => (int) $n->id,
                'tipo_poliza' => 'Egresos',
                'fecha' => $fecha,
                'referencia' => $n->folio,
                'concepto' => 'Nómina ' . $n->folio . ' ' . $n->tipo_nomina,
                'ya_existe' => $n->ya_poliza,
                'detalle' => [],
            ];
            $periodo = $this->ContabilidadModel->get_periodo_por_fecha($fecha);
            if (!$periodo) {
                $base['error'] = 'Sin periodo contable para ' . $fecha . ' (nómina ' . $n->folio . ')';
                $out[] = $base;
                continue;
            }
            $base['periodo_id'] = (int) $periodo->id;
            $perc = round((float) $n->total_percepciones, 2);
            $ded = round((float) $n->total_deducciones, 2);
            $neto = round((float) $n->total_neto, 2);
            if ($perc <= 0) {
                $perc = $neto + $ded;
            }
            $i = 1;
            $base['detalle'][] = $this->_linea($this->_cuenta('sueldos'), 'Sueldos ' . $n->folio, $perc, 0, $i++);
            $base['detalle'][] = $this->_linea($this->_cuenta('bancos'), 'Pago neto ' . $n->folio, 0, $neto, $i++);
            if ($ded > 0.009) {
                $base['detalle'][] = $this->_linea($this->_cuenta('acreedores_nomina'), 'Deducciones ' . $n->folio, 0, $ded, $i);
            }
            $out[] = $base;
        }
        return $out;
    }

    private function _linea($cuenta_id, $concepto, $debe, $haber, $orden, $aux_tipo = null, $aux_id = null) {
        return [
            'cuenta_id' => (int) $cuenta_id,
            'concepto' => $concepto,
            'debe' => round((float) $debe, 2),
            'haber' => round((float) $haber, 2),
            'orden' => (int) $orden,
            'auxiliar_tipo' => $aux_tipo,
            'auxiliar_id' => $aux_id,
        ];
    }

    private function _guardar_poliza(array $p, $autorizar) {
        foreach ($p['detalle'] as $ln) {
            if (empty($ln['cuenta_id'])) {
                return false;
            }
        }
        $debe = 0;
        $haber = 0;
        foreach ($p['detalle'] as $ln) {
            $debe += $ln['debe'];
            $haber += $ln['haber'];
        }
        if (abs($debe - $haber) > 0.05) {
            return false;
        }
        $data = [
            'folio' => $this->ContabilidadModel->generar_folio_poliza($p['tipo_poliza']),
            'tipo_poliza' => $p['tipo_poliza'],
            'fecha' => $p['fecha'],
            'periodo_id' => $p['periodo_id'],
            'concepto' => $p['concepto'],
            'referencia' => $p['referencia'],
            'origen' => $p['origen'],
            'origen_id' => $p['origen_id'],
            'total_debe' => round($debe, 2),
            'total_haber' => round($haber, 2),
            'estatus' => $autorizar ? 'Autorizada' : 'Borrador',
            'usuario_creacion' => $this->session->userdata('user_id'),
        ];
        if ($autorizar) {
            $data['usuario_autorizacion'] = $this->session->userdata('user_id');
            $data['fecha_autorizacion'] = date('Y-m-d H:i:s');
        }
        return $this->ContabilidadModel->crear_poliza($data, $p['detalle']);
    }
}
