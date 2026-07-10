<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ServiciosRecurrentesModel - Pagos recurrentes (internet, soporte, rentas, etc.)
 */
class ServiciosRecurrentesModel extends CI_Model {

    public function listar_servicios($filtros = []) {
        $this->db->select('sr.*, p.razon_social AS proveedor_nombre, p.nombre_comercial AS proveedor_comercial');
        $this->db->from('servicios_recurrentes sr');
        $this->db->join('proveedores p', 'p.id = sr.proveedor_id', 'left');
        if (!empty($filtros['proveedor_id'])) {
            $this->db->where('sr.proveedor_id', (int) $filtros['proveedor_id']);
        }
        if (isset($filtros['activo'])) {
            $this->db->where('sr.activo', (int) $filtros['activo']);
        }
        $this->db->order_by('sr.nombre_servicio', 'ASC');
        return $this->db->get()->result();
    }

    public function get_servicio($id) {
        $this->db->select('sr.*, p.razon_social AS proveedor_nombre');
        $this->db->from('servicios_recurrentes sr');
        $this->db->join('proveedores p', 'p.id = sr.proveedor_id', 'left');
        $this->db->where('sr.id', (int) $id);
        return $this->db->get()->row();
    }

    public function crear_servicio($data) {
        $data['fecha_creacion'] = date('Y-m-d H:i:s');
        if (empty($data['fecha_inicio'])) {
            $data['fecha_inicio'] = date('Y-m-01');
        }
        $ok = $this->db->insert('servicios_recurrentes', $data);
        if (!$ok) {
            return ['success' => false, 'message' => 'No se pudo crear el servicio'];
        }
        $id = $this->db->insert_id();
        $this->generar_pagos_futuros($id, 12);
        return ['success' => true, 'id' => $id, 'message' => 'Servicio recurrente creado'];
    }

    public function actualizar_servicio($id, $data) {
        $this->db->where('id', (int) $id);
        $ok = $this->db->update('servicios_recurrentes', $data);
        return ['success' => (bool) $ok, 'message' => $ok ? 'Servicio actualizado' : 'Error al actualizar'];
    }

    public function listar_pagos($periodo = null, $proveedor_id = null, $servicio_id = null) {
        $this->db->select('psr.*, sr.nombre_servicio, sr.tipo_servicio, sr.proveedor_id, p.razon_social AS proveedor_nombre');
        $this->db->from('pagos_servicios_recurrentes psr');
        $this->db->join('servicios_recurrentes sr', 'sr.id = psr.servicio_recurrente_id');
        $this->db->join('proveedores p', 'p.id = sr.proveedor_id', 'left');
        if ($periodo) {
            $this->db->where('psr.periodo', $periodo);
        }
        if ($proveedor_id) {
            $this->db->where('sr.proveedor_id', (int) $proveedor_id);
        }
        if ($servicio_id) {
            $this->db->where('psr.servicio_recurrente_id', (int) $servicio_id);
        }
        $this->db->order_by('psr.fecha_vencimiento', 'ASC');
        return $this->db->get()->result();
    }

    public function get_pago($id) {
        $this->db->select('psr.*, sr.nombre_servicio, sr.tipo_servicio, sr.proveedor_id, p.razon_social AS proveedor_nombre');
        $this->db->from('pagos_servicios_recurrentes psr');
        $this->db->join('servicios_recurrentes sr', 'sr.id = psr.servicio_recurrente_id');
        $this->db->join('proveedores p', 'p.id = sr.proveedor_id', 'left');
        $this->db->where('psr.id', (int) $id);
        return $this->db->get()->row();
    }

    public function registrar_pago_servicio($pago_id, $data) {
        $pago = $this->db->where('id', (int) $pago_id)->get('pagos_servicios_recurrentes')->row();
        if (!$pago) {
            return ['success' => false, 'message' => 'Pago no encontrado'];
        }
        if ($pago->estatus === 'Pagado') {
            return ['success' => false, 'message' => 'Este pago ya fue registrado'];
        }

        $update = [
            'fecha_pago' => $data['fecha_pago'] ?? date('Y-m-d'),
            'monto' => round((float) ($data['monto'] ?? $pago->monto), 2),
            'referencia' => $data['referencia'] ?? null,
            'notas' => $data['notas'] ?? null,
            'estatus' => 'Pagado',
            'usuario_registro' => $data['usuario_registro'] ?? null,
        ];

        $this->db->where('id', (int) $pago_id);
        $ok = $this->db->update('pagos_servicios_recurrentes', $update);
        return [
            'success' => (bool) $ok,
            'message' => $ok ? 'Pago de servicio registrado' : 'Error al registrar pago',
        ];
    }

    public function marcar_pago_vencidos() {
        $this->db->where('estatus', 'Pendiente');
        $this->db->where('fecha_vencimiento <', date('Y-m-d'));
        return $this->db->update('pagos_servicios_recurrentes', ['estatus' => 'Vencido']);
    }

    public function get_resumen_mes($periodo) {
        $this->marcar_pago_vencidos();
        $this->db->select('COUNT(*) AS total,
            SUM(CASE WHEN estatus = "Pendiente" THEN 1 ELSE 0 END) AS pendientes,
            SUM(CASE WHEN estatus = "Pagado" THEN 1 ELSE 0 END) AS pagados,
            SUM(CASE WHEN estatus = "Vencido" THEN 1 ELSE 0 END) AS vencidos,
            COALESCE(SUM(monto), 0) AS total_monto,
            COALESCE(SUM(CASE WHEN estatus = "Pagado" THEN monto ELSE 0 END), 0) AS monto_pagado', false);
        $this->db->from('pagos_servicios_recurrentes');
        $this->db->where('periodo', $periodo);
        return $this->db->get()->row();
    }

    public function generar_pagos_futuros($servicio_id, $meses = 12) {
        $servicio = $this->db->where('id', (int) $servicio_id)->get('servicios_recurrentes')->row();
        if (!$servicio) {
            return false;
        }

        $fecha_inicio = $servicio->fecha_inicio ?: date('Y-m-d');
        for ($i = 0; $i < $meses; $i++) {
            $periodo = date('Y-m', strtotime($fecha_inicio . " +$i months"));
            $anio = (int) substr($periodo, 0, 4);
            $mes = (int) substr($periodo, 5, 2);
            $dia = min((int) $servicio->dia_vencimiento, cal_days_in_month(CAL_GREGORIAN, $mes, $anio));
            $fecha_vencimiento = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);

            $existe = $this->db->get_where('pagos_servicios_recurrentes', [
                'servicio_recurrente_id' => $servicio_id,
                'periodo' => $periodo,
            ])->row();

            if (!$existe) {
                $estatus = ($fecha_vencimiento < date('Y-m-d')) ? 'Vencido' : 'Pendiente';
                $this->db->insert('pagos_servicios_recurrentes', [
                    'servicio_recurrente_id' => $servicio_id,
                    'periodo' => $periodo,
                    'fecha_vencimiento' => $fecha_vencimiento,
                    'monto' => $servicio->monto_estimado,
                    'estatus' => $estatus,
                ]);
            }
        }
        return true;
    }

    /**
     * Seguimiento mensual: historial de pagos por servicio (últimos N meses).
     */
    public function get_seguimiento_mensual($meses = 12, $proveedor_id = null) {
        $this->marcar_pago_vencidos();
        $servicios = $this->listar_servicios(['activo' => 1]);
        if ($proveedor_id) {
            $servicios = array_values(array_filter($servicios, function ($s) use ($proveedor_id) {
                return (int) $s->proveedor_id === (int) $proveedor_id;
            }));
        }

        $periodos = [];
        for ($i = $meses - 1; $i >= 0; $i--) {
            $periodos[] = date('Y-m', strtotime("-$i months"));
        }

        $resultado = [];
        foreach ($servicios as $s) {
            $this->db->select('psr.*');
            $this->db->from('pagos_servicios_recurrentes psr');
            $this->db->where('psr.servicio_recurrente_id', (int) $s->id);
            $this->db->where_in('psr.periodo', $periodos);
            $this->db->order_by('psr.periodo', 'ASC');
            $pagos = $this->db->get()->result();
            $mapa = [];
            foreach ($pagos as $p) {
                $mapa[$p->periodo] = $p;
            }

            $meses_data = [];
            foreach ($periodos as $per) {
                $p = $mapa[$per] ?? null;
                $meses_data[] = [
                    'periodo' => $per,
                    'periodo_label' => $this->label_periodo($per),
                    'pago_id' => $p ? (int) $p->id : null,
                    'monto' => $p ? (float) $p->monto : (float) $s->monto_estimado,
                    'estatus' => $p ? $p->estatus : 'Sin registro',
                    'fecha_vencimiento' => $p ? $p->fecha_vencimiento : null,
                    'fecha_pago' => $p ? $p->fecha_pago : null,
                    'poliza_id' => $p ? $p->poliza_id : null,
                ];
            }

            $resultado[] = [
                'servicio_id' => (int) $s->id,
                'nombre_servicio' => $s->nombre_servicio,
                'tipo_servicio' => $s->tipo_servicio,
                'proveedor_nombre' => $s->proveedor_nombre,
                'monto_estimado' => (float) $s->monto_estimado,
                'dia_vencimiento' => (int) $s->dia_vencimiento,
                'meses' => $meses_data,
            ];
        }

        return ['periodos' => $periodos, 'servicios' => $resultado];
    }

    public function get_historial_servicio($servicio_id, $meses = 12) {
        $servicio = $this->get_servicio($servicio_id);
        if (!$servicio) {
            return null;
        }
        $data = $this->get_seguimiento_mensual($meses, null);
        foreach ($data['servicios'] as $s) {
            if ($s['servicio_id'] === (int) $servicio_id) {
                return ['servicio' => $servicio, 'meses' => $s['meses']];
            }
        }
        return ['servicio' => $servicio, 'meses' => []];
    }

    public function generar_periodos_todos($meses = 12) {
        $servicios = $this->listar_servicios(['activo' => 1]);
        $count = 0;
        foreach ($servicios as $s) {
            $this->generar_pagos_futuros($s->id, $meses);
            $count++;
        }
        return $count;
    }

    private function label_periodo($periodo) {
        $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $p = explode('-', $periodo);
        $m = (int) ($p[1] ?? 1);
        return ($meses[$m] ?? $m) . ' ' . ($p[0] ?? '');
    }
}
