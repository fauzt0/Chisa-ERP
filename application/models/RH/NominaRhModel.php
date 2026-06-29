<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Lógica de nómina para el módulo de Recursos Humanos.
 * Usa las tablas compartidas nominas / nominas_detalle / nominas_conceptos.
 */
class NominaRhModel extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('RH/EmpleadoModel');
    }

    public function generar_folio() {
        $ultima = $this->db
            ->select('folio')
            ->from('nominas')
            ->like('folio', 'NOM', 'after')
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        $numero = $ultima ? ((int)substr($ultima->folio, 3) + 1) : 1;
        return 'NOM' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function agregar_empleados_nomina($nomina_id, $tipo_nomina) {
        $empleados = $this->db
            ->select('id')
            ->from('empleados')
            ->where_in('estatus', EmpleadoModel::estatus_laborales_activos())
            ->where('tipo_nomina', $tipo_nomina)
            ->get()
            ->result();

        foreach ($empleados as $emp) {
            $this->db->insert('nominas_detalle', [
                'nomina_id'   => $nomina_id,
                'empleado_id' => $emp->id,
            ]);
        }

        return count($empleados);
    }

    public function calcular_nomina($nomina_id) {
        $nomina = $this->db->get_where('nominas', ['id' => (int)$nomina_id])->row();
        if (!$nomina || $nomina->estatus !== 'Borrador') {
            return ['success' => false, 'message' => 'Nómina no válida para cálculo'];
        }

        $this->db->select('nd.id, nd.empleado_id, e.salario_base_mensual, e.salario_base_diario, e.isr_porcentaje, e.imss_cuota, e.pension_alimenticia_porcentaje, e.pension_alimenticia_monto, e.descuento_infonavit, e.tiene_infonavit');
        $this->db->from('nominas_detalle nd');
        $this->db->join('empleados e', 'nd.empleado_id = e.id');
        $this->db->where('nd.nomina_id', (int)$nomina_id);
        $detalles = $this->db->get()->result();

        if (empty($detalles)) {
            return ['success' => false, 'message' => 'No hay empleados en esta nómina'];
        }

        $total_percepciones = 0;
        $total_deducciones = 0;
        $total_neto = 0;

        foreach ($detalles as $det) {
            $dias = $this->calcular_dias_periodo($nomina->periodo_inicio, $nomina->periodo_fin, $nomina->tipo_nomina);
            $sueldo = $this->calcular_sueldo_periodo($det, $nomina->tipo_nomina, $dias);
            $conceptos = $this->calcular_conceptos_empleado(
                $det,
                $sueldo,
                $nomina->tipo_nomina,
                $nomina->periodo_inicio,
                $nomina->periodo_fin
            );
            $deducciones = array_sum(array_column(array_filter($conceptos, function ($c) {
                return $c['tipo'] === 'Deducción';
            }), 'monto'));
            $percepciones = array_sum(array_column(array_filter($conceptos, function ($c) {
                return $c['tipo'] === 'Percepción';
            }), 'monto'));
            $neto = $percepciones - $deducciones;

            $this->db->where('id', $det->id)->update('nominas_detalle', [
                'dias_trabajados' => $dias,
                'sueldo_base'     => $sueldo,
                'percepciones'    => $percepciones,
                'deducciones'     => $deducciones,
                'neto'            => $neto,
            ]);

            $this->db->where('nomina_detalle_id', $det->id)->delete('nominas_conceptos');
            foreach ($conceptos as $concepto) {
                $this->db->insert('nominas_conceptos', [
                    'nomina_detalle_id' => $det->id,
                    'tipo'              => $concepto['tipo'],
                    'concepto'          => $concepto['concepto'],
                    'monto'             => $concepto['monto'],
                ]);
            }

            $this->load->model('RH/IncidenciasModel');
            $this->IncidenciasModel->marcar_procesadas_periodo(
                $det->empleado_id,
                $nomina->periodo_inicio,
                $nomina->periodo_fin
            );

            $total_percepciones += $percepciones;
            $total_deducciones += $deducciones;
            $total_neto += $neto;
        }

        $this->db->where('id', (int)$nomina_id)->update('nominas', [
            'total_percepciones' => $total_percepciones,
            'total_deducciones'  => $total_deducciones,
            'total_neto'         => $total_neto,
            'estatus'            => 'Calculada',
        ]);

        return [
            'success' => true,
            'message' => 'Nómina calculada correctamente',
            'totales' => [
                'percepciones' => $total_percepciones,
                'deducciones'  => $total_deducciones,
                'neto'         => $total_neto,
            ],
        ];
    }

    private function tiene_pago_parcial() {
        static $ok = null;
        if ($ok === null) {
            $ok = $this->db->field_exists('monto_pagado', 'nominas_detalle');
        }
        return $ok;
    }

    public function requiere_migracion_pago_parcial() {
        return !$this->tiene_pago_parcial();
    }

    public function get_estadisticas_dashboard() {
        $stats = [
            'total_nominas'    => 0,
            'pendientes_pago'  => 0,
            'pagadas_mes'      => 0,
            'neto_pendiente'   => 0,
        ];

        $stats['total_nominas'] = (int)$this->db->count_all('nominas');

        $pendientes = $this->db
            ->select('COUNT(*) as total, COALESCE(SUM(total_neto), 0) as neto')
            ->from('nominas')
            ->where_in('estatus', ['Borrador', 'Calculada', 'Parcial'])
            ->get()
            ->row();
        $stats['pendientes_pago'] = (int)($pendientes->total ?? 0);
        $stats['neto_pendiente'] = (float)($pendientes->neto ?? 0);

        $mes = date('Y-m');
        $pagadas = $this->db
            ->where('estatus', 'Pagada')
            ->like('fecha_pago', $mes, 'after')
            ->count_all_results('nominas');
        $stats['pagadas_mes'] = (int)$pagadas;

        return $stats;
    }

    private function calcular_dias_periodo($inicio, $fin, $tipo) {
        $dias = ((strtotime($fin) - strtotime($inicio)) / 86400) + 1;
        switch ($tipo) {
            case 'Semanal':
                return min($dias, 7);
            case 'Quincenal':
                return min($dias, 15);
            case 'Mensual':
                return min($dias, 30);
            default:
                return $dias;
        }
    }

    private function calcular_sueldo_periodo($empleado, $tipo_nomina, $dias) {
        $mensual = (float)$empleado->salario_base_mensual;
        $diario = (float)$empleado->salario_base_diario;
        if ($diario <= 0 && $mensual > 0) {
            $diario = $mensual / 30;
        }

        switch ($tipo_nomina) {
            case 'Semanal':
                return round($diario * $dias, 2);
            case 'Quincenal':
                return round($mensual / 2, 2);
            case 'Mensual':
                return round($mensual, 2);
            default:
                return round($diario * $dias, 2);
        }
    }

    private function calcular_conceptos_empleado($empleado, $sueldo, $tipo_nomina, $periodo_inicio = null, $periodo_fin = null) {
        $conceptos = [[
            'tipo'     => 'Percepción',
            'concepto' => 'Sueldo Base',
            'monto'    => round($sueldo, 2),
        ]];

        if ($periodo_inicio && $periodo_fin && !empty($empleado->empleado_id)) {
            $this->load->model('RH/IncidenciasModel');
            $incidencias = $this->IncidenciasModel->get_incidencias_nomina_periodo(
                $empleado->empleado_id,
                $periodo_inicio,
                $periodo_fin
            );
            foreach ($incidencias as $inc) {
                $monto = round((float)$inc->monto_descuento, 2);
                if ($monto <= 0) {
                    continue;
                }
                if ($inc->tipo_incidencia === 'Horas Extras') {
                    $conceptos[] = [
                        'tipo'     => 'Percepción',
                        'concepto' => 'Horas Extras (' . date('d/m', strtotime($inc->fecha_incidencia)) . ')',
                        'monto'    => $monto,
                    ];
                } elseif (!empty($inc->tiene_descuento)) {
                    $conceptos[] = [
                        'tipo'     => 'Deducción',
                        'concepto' => $inc->tipo_incidencia . ' (' . date('d/m', strtotime($inc->fecha_incidencia)) . ')',
                        'monto'    => $monto,
                    ];
                }
            }
        }

        $isr_pct = (float)$empleado->isr_porcentaje;
        $isr = $isr_pct > 0 ? round($sueldo * ($isr_pct / 100), 2) : round($sueldo * 0.10, 2);

        $imss = (float)$empleado->imss_cuota;
        if ($imss <= 0) {
            $imss = round($sueldo * 0.0235, 2);
        }

        $pension_pct = (float)$empleado->pension_alimenticia_porcentaje;
        $pension_monto = (float)$empleado->pension_alimenticia_monto;
        if ($pension_pct > 0) {
            $pension_monto += round($sueldo * ($pension_pct / 100), 2);
        }

        $infonavit = 0;
        if (!empty($empleado->tiene_infonavit) && (float)$empleado->descuento_infonavit > 0) {
            $infonavit = round((float)$empleado->descuento_infonavit, 2);
        }

        $conceptos[] = ['tipo' => 'Deducción', 'concepto' => 'ISR', 'monto' => $isr];
        $conceptos[] = ['tipo' => 'Deducción', 'concepto' => 'IMSS', 'monto' => $imss];

        if ($pension_monto > 0) {
            $conceptos[] = ['tipo' => 'Deducción', 'concepto' => 'Pensión Alimenticia', 'monto' => round($pension_monto, 2)];
        }
        if ($infonavit > 0) {
            $conceptos[] = ['tipo' => 'Deducción', 'concepto' => 'INFONAVIT', 'monto' => $infonavit];
        }

        return $conceptos;
    }

    /**
     * Obtiene detalle de empleados listo para el modal de pago (con desglose y adeudos).
     */
    public function get_detalle_para_pago($nomina_id) {
        if ($this->requiere_migracion_pago_parcial()) {
            return ['error' => 'migracion', 'message' => 'Ejecute la migración database/nomina_pago_parcial.sql antes de procesar pagos.'];
        }

        $this->load->model('Contabilidad/NominaModel');
        $nomina = $this->NominaModel->get_nomina_completa($nomina_id);
        if (!$nomina || !in_array($nomina->estatus, ['Calculada', 'Parcial'], true)) {
            return null;
        }

        $empleados = [];
        $totales = [
            'pendientes'    => 0,
            'pagados'       => 0,
            'neto_pendiente'=> 0,
            'neto_pagado'   => 0,
            'porcentaje'    => 0,
        ];

        foreach ($nomina->detalle as $det) {
            $monto_pagado = (float)($det->monto_pagado ?? 0);
            $neto = (float)$det->neto;
            $pendiente = max(0, round($neto - $monto_pagado, 2));
            $pct_pagado = $neto > 0 ? round(($monto_pagado / $neto) * 100, 1) : 0;
            $adeudos = $this->get_adeudos_empleado($det->empleado_id, $nomina_id);

            $conceptos = [];
            if (!empty($det->conceptos)) {
                foreach ($det->conceptos as $c) {
                    $conceptos[] = [
                        'tipo'     => $c->tipo,
                        'concepto' => $c->concepto,
                        'monto'    => (float)$c->monto,
                    ];
                }
            }

            $puede_pagar = $pendiente > 0 && !in_array($det->estatus, ['Pagado', 'Cancelado'], true);

            if ($puede_pagar) {
                $totales['pendientes']++;
                $totales['neto_pendiente'] += $pendiente;
            } else {
                $totales['pagados']++;
                $totales['neto_pagado'] += $monto_pagado;
            }

            $empleados[] = [
                'detalle_id'       => (int)$det->id,
                'empleado_id'      => (int)$det->empleado_id,
                'numero_empleado'  => $det->numero_empleado ?? '',
                'nombre'           => trim($det->nombre . ' ' . $det->apellido_paterno . ' ' . ($det->apellido_materno ?? '')),
                'puesto'           => $det->puesto ?? '',
                'dias_trabajados'  => (float)$det->dias_trabajados,
                'sueldo_base'      => (float)$det->sueldo_base,
                'percepciones'     => (float)$det->percepciones,
                'deducciones'      => (float)$det->deducciones,
                'neto'             => $neto,
                'monto_pagado'     => $monto_pagado,
                'pendiente'        => $pendiente,
                'porcentaje_pagado'=> $pct_pagado,
                'estatus'          => $det->estatus,
                'puede_pagar'      => $puede_pagar,
                'seleccionado'     => $puede_pagar,
                'monto_sugerido'   => $pendiente,
                'max_pago'         => round($pendiente + (float)$adeudos['total'], 2),
                'adeudos'          => $adeudos,
                'conceptos'        => $conceptos,
            ];
        }

        $total_neto = (float)$nomina->total_neto;
        $totales['porcentaje'] = $total_neto > 0
            ? round(($totales['neto_pagado'] / $total_neto) * 100, 1)
            : 0;

        return [
            'nomina'    => $nomina,
            'empleados' => $empleados,
            'totales'   => $totales,
        ];
    }

    /**
     * Adeudos de nóminas anteriores no pagadas (Pendiente/Parcial).
     */
    public function get_adeudos_empleado($empleado_id, $exclude_nomina_id = null) {
        $select = 'nd.id, nd.nomina_id, nd.neto, nd.estatus, n.folio, n.periodo_inicio, n.periodo_fin, n.fecha_pago';
        if ($this->tiene_pago_parcial()) {
            $select .= ', nd.monto_pagado';
        }
        $this->db->select($select);
        $this->db->from('nominas_detalle nd');
        $this->db->join('nominas n', 'n.id = nd.nomina_id');
        $this->db->where('nd.empleado_id', (int)$empleado_id);
        $this->db->where_in('nd.estatus', ['Pendiente', 'Parcial']);
        $this->db->where_in('n.estatus', ['Calculada', 'Parcial']);
        if ($exclude_nomina_id) {
            $this->db->where('nd.nomina_id !=', (int)$exclude_nomina_id);
        }
        $this->db->order_by('n.periodo_inicio', 'ASC');
        $rows = $this->db->get()->result();

        $items = [];
        $total = 0;
        foreach ($rows as $row) {
            if ($this->tiene_pago_parcial()) {
                $pendiente = max(0, (float)$row->neto - (float)($row->monto_pagado ?? 0));
            } else {
                $pendiente = in_array($row->estatus, ['Pagado', 'Cancelado'], true) ? 0 : (float)$row->neto;
            }
            if ($pendiente <= 0) {
                continue;
            }
            $items[] = [
                'detalle_id' => (int)$row->id,
                'nomina_id'  => (int)$row->nomina_id,
                'folio'      => $row->folio,
                'periodo'    => $row->periodo_inicio . ' — ' . $row->periodo_fin,
                'pendiente'  => round($pendiente, 2),
            ];
            $total += $pendiente;
        }

        return ['total' => round($total, 2), 'items' => $items];
    }

    /**
     * Procesa pagos con monto editable y opción de consolidar adeudos.
     * $pagos: [['detalle_id'=>int, 'monto'=>float, 'incluir_adeudos'=>bool], ...]
     */
    public function procesar_pagos_nomina($nomina_id, array $pagos) {
        if ($this->requiere_migracion_pago_parcial()) {
            return ['success' => false, 'message' => 'Ejecute la migración database/nomina_pago_parcial.sql antes de procesar pagos.'];
        }

        $nomina = $this->db->get_where('nominas', ['id' => (int)$nomina_id])->row();
        if (!$nomina || !in_array($nomina->estatus, ['Calculada', 'Parcial'], true)) {
            return ['success' => false, 'message' => 'La nómina no está disponible para pago'];
        }

        if (empty($pagos)) {
            return ['success' => false, 'message' => 'No hay pagos para procesar'];
        }

        $ahora = date('Y-m-d H:i:s');
        $usuario_id = $this->session->userdata('id') ?: $this->session->userdata('user_id');
        $lineas_poliza = [];
        $procesados = 0;
        $neto_lote = 0;
        $detalle_ids_pagados = [];
        $pagos_lote = [];
        $nomina_ids_afectadas = [(int)$nomina_id];

        foreach ($pagos as $pago) {
            $detalle_id = (int)($pago['detalle_id'] ?? 0);
            $monto = round((float)($pago['monto'] ?? 0), 2);
            $incluir_adeudos = !empty($pago['incluir_adeudos']);

            if ($detalle_id <= 0 || $monto <= 0) {
                continue;
            }

            $det = $this->db->get_where('nominas_detalle', [
                'id'        => $detalle_id,
                'nomina_id' => (int)$nomina_id,
            ])->row();

            if (!$det || in_array($det->estatus, ['Pagado', 'Cancelado'], true)) {
                continue;
            }

            $pendiente_periodo = max(0, round((float)$det->neto - (float)($det->monto_pagado ?? 0), 2));
            $adeudos = $this->get_adeudos_empleado($det->empleado_id, $nomina_id);
            $max_adeudos = $incluir_adeudos ? (float)$adeudos['total'] : 0;
            $max_permitido = round($pendiente_periodo + $max_adeudos, 2);

            if ($monto > $max_permitido + 0.01) {
                return [
                    'success' => false,
                    'message' => 'El monto $' . number_format($monto, 2) . ' excede el máximo permitido ($' . number_format($max_permitido, 2) . ') para el empleado seleccionado.',
                ];
            }

            $restante = $monto;
            $monto_adeudos = 0;
            $adeudos_liquidados = [];

            if ($incluir_adeudos && $restante > 0 && !empty($adeudos['items'])) {
                foreach ($adeudos['items'] as $item) {
                    if ($restante <= 0) {
                        break;
                    }
                    $aplicar = min($restante, (float)$item['pendiente']);
                    if ($aplicar <= 0) {
                        continue;
                    }

                    $ok = $this->aplicar_monto_a_detalle((int)$item['detalle_id'], $aplicar, $ahora);
                    if ($ok) {
                        $restante -= $aplicar;
                        $monto_adeudos += $aplicar;
                        $adeudos_liquidados[] = [
                            'detalle_id' => $item['detalle_id'],
                            'nomina_id'  => $item['nomina_id'],
                            'folio'      => $item['folio'],
                            'monto'      => $aplicar,
                        ];
                        $nomina_ids_afectadas[] = (int)$item['nomina_id'];
                    }
                }
            }

            $monto_periodo = 0;
            if ($restante > 0 && $pendiente_periodo > 0) {
                $aplicar_periodo = min($restante, $pendiente_periodo);
                $this->aplicar_monto_a_detalle($detalle_id, $aplicar_periodo, $ahora);
                $monto_periodo = $aplicar_periodo;
                $restante -= $aplicar_periodo;
            }

            $monto_efectivo = $monto_periodo + $monto_adeudos;
            if ($monto_efectivo <= 0) {
                continue;
            }

            $conceptos = $this->db->where('nomina_detalle_id', $detalle_id)->get('nominas_conceptos')->result();
            $ratio = (float)$det->neto > 0 ? ($monto_periodo / (float)$det->neto) : 0;

            $lineas_poliza[] = [
                'detalle'        => $det,
                'conceptos'      => $conceptos,
                'neto'           => $monto_efectivo,
                'neto_periodo'   => $monto_periodo,
                'neto_adeudos'   => $monto_adeudos,
                'ratio_periodo'  => $ratio,
            ];

            $this->registrar_pago_log([
                'nomina_id'            => (int)$nomina_id,
                'nomina_detalle_id'    => $detalle_id,
                'empleado_id'          => (int)$det->empleado_id,
                'monto'                => $monto_efectivo,
                'monto_periodo'        => $monto_periodo,
                'monto_adeudos'        => $monto_adeudos,
                'detalle_adeudos_json' => !empty($adeudos_liquidados) ? json_encode($adeudos_liquidados) : null,
                'usuario_id'           => $usuario_id,
                'fecha_pago'           => $ahora,
            ]);

            $procesados++;
            $neto_lote += $monto_efectivo;
            $detalle_ids_pagados[] = $detalle_id;
            $pagos_lote[$detalle_id] = $monto_efectivo;
        }

        if ($procesados === 0) {
            return ['success' => false, 'message' => 'No se procesó ningún pago válido'];
        }

        foreach (array_unique($nomina_ids_afectadas) as $nid) {
            $this->actualizar_estatus_nomina_cabecera($nid);
        }

        $this->load->model('Contabilidad/ContabilidadModel');
        $poliza_id = $this->generar_poliza_pago_lote($nomina, $lineas_poliza, $procesados);

        if ($poliza_id && $this->db->table_exists('nominas_pagos_log')) {
            $this->db->where('nomina_id', (int)$nomina_id)
                ->where('fecha_pago', $ahora)
                ->where('poliza_id IS NULL', null, false)
                ->update('nominas_pagos_log', ['poliza_id' => $poliza_id]);
        }

        if ($poliza_id && empty($nomina->poliza_id)) {
            $this->db->where('id', (int)$nomina_id)->update('nominas', ['poliza_id' => $poliza_id]);
        }

        $nomina_actualizada = $this->db->get_where('nominas', ['id' => (int)$nomina_id])->row();
        $msg = "Pago registrado: {$procesados} empleado(s), $" . number_format($neto_lote, 2);
        if (!$poliza_id) {
            $msg .= ', pero no se generó póliza contable (verifique periodo y cuentas).';
        } elseif ($nomina_actualizada->estatus === 'Pagada') {
            $msg .= '. Nómina del periodo completada al 100%.';
        } else {
            $msg .= '. Quedan saldos pendientes en este periodo.';
        }

        return [
            'success'     => true,
            'message'     => $msg,
            'pagados'     => $procesados,
            'neto'        => $neto_lote,
            'poliza_id'   => $poliza_id ?: null,
            'estatus'     => $nomina_actualizada->estatus,
            'nomina_id'   => (int)$nomina_id,
            'detalle_ids' => $detalle_ids_pagados,
            'pagos_lote'  => $pagos_lote,
        ];
    }

    /**
     * Aplica un monto al detalle (pago parcial o total).
     */
    private function aplicar_monto_a_detalle($detalle_id, $monto, $fecha) {
        $det = $this->db->get_where('nominas_detalle', ['id' => (int)$detalle_id])->row();
        if (!$det) {
            return false;
        }

        $nuevo_pagado = round((float)($det->monto_pagado ?? 0) + (float)$monto, 2);
        $neto = (float)$det->neto;

        if ($nuevo_pagado >= $neto - 0.01) {
            $estatus = 'Pagado';
            $nuevo_pagado = $neto;
        } elseif ($nuevo_pagado > 0) {
            $estatus = 'Parcial';
        } else {
            $estatus = 'Pendiente';
        }

        $this->db->where('id', (int)$detalle_id)->update('nominas_detalle', [
            'monto_pagado' => $nuevo_pagado,
            'estatus'      => $estatus,
            'fecha_pago'   => $fecha,
        ]);

        return true;
    }

    private function registrar_pago_log(array $data) {
        if (!$this->db->table_exists('nominas_pagos_log')) {
            return;
        }
        $this->db->insert('nominas_pagos_log', $data);
    }

    /**
     * Compatibilidad: pago total del pendiente por empleado seleccionado.
     */
    public function pagar_empleados_seleccionados($nomina_id, array $detalle_ids, array $opciones = []) {
        $pagos = [];
        $data = $this->get_detalle_para_pago($nomina_id);
        if (!$data) {
            return ['success' => false, 'message' => 'Nómina no disponible'];
        }

        foreach ($data['empleados'] as $emp) {
            if (!in_array($emp['detalle_id'], array_map('intval', $detalle_ids), true)) {
                continue;
            }
            $incluir = !empty($opciones['incluir_adeudos']) && $emp['adeudos']['total'] > 0;
            $monto = $emp['pendiente'] + ($incluir ? $emp['adeudos']['total'] : 0);
            $pagos[] = [
                'detalle_id'       => $emp['detalle_id'],
                'monto'            => $monto,
                'incluir_adeudos'  => $incluir,
            ];
        }

        return $this->procesar_pagos_nomina($nomina_id, $pagos);
    }

    /** @deprecated Usar pagar_empleados_seleccionados */
    public function marcar_pagada($nomina_id) {
        $data = $this->get_detalle_para_pago($nomina_id);
        if (!$data) {
            return ['success' => false, 'message' => 'Nómina no disponible para pago'];
        }
        $ids = [];
        foreach ($data['empleados'] as $emp) {
            if ($emp['puede_pagar']) {
                $ids[] = $emp['detalle_id'];
            }
        }
        return $this->pagar_empleados_seleccionados($nomina_id, $ids);
    }

    private function actualizar_estatus_nomina_cabecera($nomina_id) {
        if ($this->tiene_pago_parcial()) {
            $this->db->select('COUNT(*) as total, SUM(CASE WHEN estatus="Pagado" THEN 1 ELSE 0 END) as pagados, SUM(monto_pagado) as pagado, SUM(neto) as neto_total');
        } else {
            $this->db->select('COUNT(*) as total, SUM(CASE WHEN estatus="Pagado" THEN 1 ELSE 0 END) as pagados, 0 as pagado, SUM(neto) as neto_total');
        }
        $this->db->where('nomina_id', (int)$nomina_id);
        $stats = $this->db->get('nominas_detalle')->row();

        $estatus = 'Calculada';
        if ((int)$stats->pagados === (int)$stats->total && (int)$stats->total > 0) {
            $estatus = 'Pagada';
        } elseif ((int)$stats->pagados > 0 || (float)$stats->pagado > 0) {
            $estatus = 'Parcial';
        }

        $this->db->where('id', (int)$nomina_id)->update('nominas', ['estatus' => $estatus]);
    }

    /**
     * Póliza contable solo por el lote de empleados pagados en esta operación.
     */
    private function generar_poliza_pago_lote($nomina, array $lineas, $num_empleados) {
        $this->load->model('Contabilidad/ContabilidadModel');
        $periodo = $this->ContabilidadModel->get_periodo_actual();
        if (!$periodo) {
            return false;
        }

        $total_percepciones = 0;
        $total_neto = 0;
        $deducciones_map = [];

        foreach ($lineas as $linea) {
            $ratio = (float)($linea['ratio_periodo'] ?? 1);
            $neto_adeudos = (float)($linea['neto_adeudos'] ?? 0);
            $total_percepciones += (float)$linea['detalle']->percepciones * $ratio + $neto_adeudos;
            $total_neto += (float)$linea['neto'];
            foreach ($linea['conceptos'] as $c) {
                if ($c->tipo !== 'Deducción') {
                    continue;
                }
                $deducciones_map[$c->concepto] = ($deducciones_map[$c->concepto] ?? 0) + (float)$c->monto * $ratio;
            }
        }

        $usuario_id = $this->session->userdata('id') ?: $this->session->userdata('user_id');
        $folio_poliza = 'NOM-' . $nomina->folio . '-' . date('His');

        $data_poliza = [
            'folio'                => $folio_poliza,
            'tipo_poliza'          => 'Egresos',
            'fecha'                => date('Y-m-d'),
            'periodo_id'           => $periodo->id,
            'concepto'             => "Pago nómina {$nomina->folio} ({$num_empleados} empleado(s))",
            'origen'               => 'nomina',
            'origen_id'            => $nomina->id,
            'total_debe'           => $total_percepciones,
            'total_haber'          => $total_percepciones,
            'usuario_creacion'     => $usuario_id,
            'estatus'              => 'Autorizada',
            'usuario_autorizacion' => $usuario_id,
            'fecha_autorizacion'   => date('Y-m-d H:i:s'),
        ];

        $cuenta_sueldos = $this->get_cuenta_id('6.1.01');
        $cuenta_bancos  = $this->get_cuenta_id('1.1.01.003');
        if (!$cuenta_sueldos || !$cuenta_bancos) {
            return false;
        }

        $detalle_poliza = [[
            'cuenta_id' => $cuenta_sueldos,
            'concepto'  => 'Sueldos — lote ' . $nomina->folio,
            'debe'      => $total_percepciones,
            'haber'     => 0,
            'orden'     => 1,
        ]];

        $orden = 2;
        $mapa = ['ISR' => '2.1.05', 'IMSS' => '2.1.06', 'INFONAVIT' => '2.1.07', 'Pensión Alimenticia' => '2.1.08'];
        foreach ($deducciones_map as $concepto => $monto) {
            if ($monto <= 0) continue;
            $cuenta_id = $this->get_cuenta_id($mapa[$concepto] ?? '2.1.05') ?: $this->get_cuenta_id('2.1.05');
            if (!$cuenta_id) continue;
            $detalle_poliza[] = [
                'cuenta_id' => $cuenta_id,
                'concepto'  => "{$concepto} retenido — {$nomina->folio}",
                'debe'      => 0,
                'haber'     => round($monto, 2),
                'orden'     => $orden++,
            ];
        }

        $detalle_poliza[] = [
            'cuenta_id' => $cuenta_bancos,
            'concepto'  => 'Pago neto nómina — ' . $nomina->folio,
            'debe'      => 0,
            'haber'     => round($total_neto, 2),
            'orden'     => $orden++,
        ];

        return $this->ContabilidadModel->crear_poliza($data_poliza, $detalle_poliza);
    }

    /**
     * Genera póliza de egreso por pago total de nómina (legacy).
     */
    public function generar_poliza_nomina($nomina) {
        if (is_numeric($nomina)) {
            $nomina = $this->db->get_where('nominas', ['id' => (int)$nomina])->row();
        }
        if (!$nomina) {
            return false;
        }

        $this->load->model('Contabilidad/ContabilidadModel');
        $periodo = $this->ContabilidadModel->get_periodo_actual();
        if (!$periodo) {
            return false;
        }

        $deducciones = $this->get_totales_deducciones_por_concepto($nomina->id);
        $usuario_id = $this->session->userdata('id') ?: $this->session->userdata('user_id');

        $data_poliza = [
            'folio'                => 'NOM-' . $nomina->folio,
            'tipo_poliza'          => 'Egresos',
            'fecha'                => $nomina->fecha_pago,
            'periodo_id'           => $periodo->id,
            'concepto'             => 'Pago de nómina ' . $nomina->tipo_nomina . ' — ' . $nomina->folio,
            'origen'               => 'nomina',
            'origen_id'            => $nomina->id,
            'total_debe'           => (float)$nomina->total_percepciones,
            'total_haber'          => (float)$nomina->total_percepciones,
            'usuario_creacion'     => $usuario_id,
            'estatus'              => 'Autorizada',
            'usuario_autorizacion' => $usuario_id,
            'fecha_autorizacion'   => date('Y-m-d H:i:s'),
        ];

        $cuenta_sueldos = $this->get_cuenta_id('6.1.01');
        $cuenta_bancos  = $this->get_cuenta_id('1.1.01.003');
        if (!$cuenta_sueldos || !$cuenta_bancos) {
            return false;
        }

        $detalle = [[
            'cuenta_id' => $cuenta_sueldos,
            'concepto'  => 'Sueldos y salarios — ' . $nomina->folio,
            'debe'      => (float)$nomina->total_percepciones,
            'haber'     => 0,
            'orden'     => 1,
        ]];

        $orden = 2;
        $mapa_cuentas = [
            'ISR'                 => '2.1.05',
            'IMSS'                => '2.1.06',
            'INFONAVIT'           => '2.1.07',
            'Pensión Alimenticia' => '2.1.08',
        ];
        $pasivo_default = '2.1.05';
        $total_haber_deducciones = 0;

        foreach ($deducciones as $concepto => $monto) {
            if ($monto <= 0) {
                continue;
            }
            $codigo = $mapa_cuentas[$concepto] ?? $pasivo_default;
            $cuenta_id = $this->get_cuenta_id($codigo) ?: $this->get_cuenta_id($pasivo_default);
            if (!$cuenta_id) {
                continue;
            }
            $detalle[] = [
                'cuenta_id' => $cuenta_id,
                'concepto'  => $concepto . ' retenido — ' . $nomina->folio,
                'debe'      => 0,
                'haber'     => round($monto, 2),
                'orden'     => $orden++,
            ];
            $total_haber_deducciones += round($monto, 2);
        }

        $neto = round((float)$nomina->total_neto, 2);
        $detalle[] = [
            'cuenta_id' => $cuenta_bancos,
            'concepto'  => 'Pago neto de nómina — ' . $nomina->folio,
            'debe'      => 0,
            'haber'     => $neto,
            'orden'     => $orden++,
        ];

        $total_haber = $total_haber_deducciones + $neto;
        $total_debe = (float)$nomina->total_percepciones;

        if (abs($total_haber - $total_debe) > 0.05) {
            $diff = round($total_debe - $total_haber, 2);
            if ($diff != 0) {
                $cuenta_ajuste = $this->get_cuenta_id($pasivo_default);
                if ($cuenta_ajuste) {
                    $detalle[] = [
                        'cuenta_id' => $cuenta_ajuste,
                        'concepto'  => 'Ajuste deducciones nómina — ' . $nomina->folio,
                        'debe'      => 0,
                        'haber'     => $diff,
                        'orden'     => $orden++,
                    ];
                }
            }
        }

        return $this->ContabilidadModel->crear_poliza($data_poliza, $detalle);
    }

    private function get_totales_deducciones_por_concepto($nomina_id) {
        $rows = $this->db
            ->select('nc.concepto, SUM(nc.monto) as total')
            ->from('nominas_conceptos nc')
            ->join('nominas_detalle nd', 'nd.id = nc.nomina_detalle_id')
            ->where('nd.nomina_id', (int)$nomina_id)
            ->where('nc.tipo', 'Deducción')
            ->group_by('nc.concepto')
            ->get()
            ->result();

        $totales = [];
        foreach ($rows as $row) {
            $totales[$row->concepto] = (float)$row->total;
        }
        return $totales;
    }

    private function get_cuenta_id($codigo) {
        $cuenta = $this->db->get_where('cuentas_contables', ['codigo' => $codigo, 'estatus' => 'Activa'])->row();
        return $cuenta ? (int)$cuenta->id : null;
    }

    /**
     * Datos de nómina formateados para exportación Aspel NOI.
     */
    public function get_datos_exportacion_noi($nomina_id) {
        $this->load->model('Contabilidad/NominaModel');
        $nomina = $this->NominaModel->get_nomina_completa($nomina_id);
        if (!$nomina) {
            return null;
        }

        $filas = [];
        foreach ($nomina->detalle as $det) {
            $conceptos = ['ISR' => 0, 'IMSS' => 0, 'INFONAVIT' => 0, 'Pensión Alimenticia' => 0, 'Otras' => 0];
            if (!empty($det->conceptos)) {
                foreach ($det->conceptos as $c) {
                    if ($c->tipo !== 'Deducción') {
                        continue;
                    }
                    if (isset($conceptos[$c->concepto])) {
                        $conceptos[$c->concepto] += (float)$c->monto;
                    } else {
                        $conceptos['Otras'] += (float)$c->monto;
                    }
                }
            }

            $filas[] = [
                'numero_empleado'  => $det->numero_empleado ?? '',
                'nombre_completo'  => trim($det->nombre . ' ' . $det->apellido_paterno . ' ' . ($det->apellido_materno ?? '')),
                'rfc'              => $det->rfc ?? '',
                'curp'             => $det->curp ?? '',
                'nss'              => $det->nss ?? '',
                'puesto'           => $det->puesto ?? '',
                'dias_trabajados'  => (float)$det->dias_trabajados,
                'sueldo_base'      => (float)$det->sueldo_base,
                'percepciones'     => (float)$det->percepciones,
                'isr'              => $conceptos['ISR'],
                'imss'             => $conceptos['IMSS'],
                'infonavit'        => $conceptos['INFONAVIT'],
                'pension'          => $conceptos['Pensión Alimenticia'],
                'otras_deducciones'=> $conceptos['Otras'],
                'deducciones'      => (float)$det->deducciones,
                'neto'             => (float)$det->neto,
            ];
        }

        return ['nomina' => $nomina, 'filas' => $filas];
    }
}
