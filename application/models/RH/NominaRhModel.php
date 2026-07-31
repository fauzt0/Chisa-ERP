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

    /**
     * Determina el lugar de origen para un empleado según su departamento.
     *
     * Reglas:
     * - Si el departamento NO es "Obras" → "Oficina"
     * - Si es "Obras" y tiene lugar_pago definido → el nombre de la obra (lugar_pago)
     * - Si es "Obras" y lugar_pago está vacío → "Obra"
     *
     * @param object $empleado  Row de la tabla empleados (debe incluir departamento_id y lugar_pago)
     * @return string
     */
    public function get_lugar_origen_empleado($empleado) {
        if (empty($empleado->departamento_id)) {
            return 'Oficina';
        }

        $dept = $this->db
            ->select('nombre')
            ->where('id', (int)$empleado->departamento_id)
            ->get('departamentos')
            ->row();

        $es_obras = $dept && strtolower(trim($dept->nombre)) === 'obras';

        if (!$es_obras) {
            return 'Oficina';
        }

        $lugar = trim((string)($empleado->lugar_pago ?? ''));
        return $lugar !== '' ? $lugar : 'Obra';
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
            ->select('id, lugar_pago, forma_pago, departamento_id')
            ->from('empleados')
            ->where_in('estatus', EmpleadoModel::estatus_laborales_activos())
            ->where('tipo_nomina', $tipo_nomina)
            ->get()
            ->result();

        foreach ($empleados as $emp) {
            $this->db->insert('nominas_detalle', [
                'nomina_id'   => $nomina_id,
                'empleado_id' => $emp->id,
                'lugar_origen'=> $this->get_lugar_origen_empleado($emp),
                'forma_pago'  => $emp->forma_pago ?? 'Transferencia',
            ]);
        }

        return count($empleados);
    }

    public function calcular_nomina($nomina_id) {
        $nomina = $this->db->get_where('nominas', ['id' => (int)$nomina_id])->row();
        if (!$nomina || $nomina->estatus !== 'Borrador') {
            return ['success' => false, 'message' => 'Nómina no válida para cálculo'];
        }

        $config = $this->get_configuracion_automatizacion();
        $flags = [
            'infonavit' => $config && isset($config->aplicar_infonavit) ? (bool)$config->aplicar_infonavit : true,
            'isr'       => $config && isset($config->aplicar_isr) ? (bool)$config->aplicar_isr : false,
            'imss'      => $config && isset($config->aplicar_imss) ? (bool)$config->aplicar_imss : true,
        ];

        $this->db->select('
            nd.id, nd.empleado_id,
            e.salario_base_mensual, e.salario_base_diario,
            e.isr_porcentaje, e.imss_cuota,
            e.pension_alimenticia_porcentaje, e.pension_alimenticia_monto,
            e.descuento_infonavit, e.tiene_infonavit, e.infonavit_aportacion,
            e.costo_hora_extra,
            e.lugar_pago, e.departamento_id
        ');
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
                $nomina->periodo_fin,
                $flags
            );
            $deducciones = array_sum(array_column(array_filter($conceptos, function ($c) {
                return $c['tipo'] === 'Deducción';
            }), 'monto'));
            $percepciones = array_sum(array_column(array_filter($conceptos, function ($c) {
                return $c['tipo'] === 'Percepción';
            }), 'monto'));
            $neto = $percepciones - $deducciones;

            // Extraer ISR, IMSS e INFONAVIT de los conceptos calculados (respetan flags)
            $isr_calculado = 0;
            $imss_calculado = 0;
            $infonavit_calculado = 0;
            foreach ($conceptos as $c) {
                if ($c['concepto'] === 'ISR')       $isr_calculado       = $c['monto'];
                if ($c['concepto'] === 'IMSS')      $imss_calculado      = $c['monto'];
                if ($c['concepto'] === 'INFONAVIT') $infonavit_calculado = $c['monto'];
            }

            $this->db->where('id', $det->id)->update('nominas_detalle', [
                'dias_trabajados'     => $dias,
                'sueldo_base'         => $sueldo,
                'sueldo_diario'       => round((float)$det->salario_base_diario, 2),
                'lugar_origen'        => $this->get_lugar_origen_empleado($det),
                'percepciones'        => $percepciones,
                'deducciones'         => $deducciones,
                'infonavit_descuento' => $infonavit_calculado,
                'isr'                 => $isr_calculado,
                'imss'                => $imss_calculado,
                'neto'                => $neto,
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

    /**
     * Obtiene detalle completo de nómina con las nuevas columnas para el modal informativo.
     * @param int $nomina_id
     * @return object|null
     */
    public function get_nomina_detalle_completo($nomina_id) {
        $this->db->select('
        n.*,
        nd.id as detalle_id, nd.empleado_id, nd.lugar_origen,
        nd.dias_trabajados, nd.sueldo_base, nd.sueldo_diario,
        nd.horas_extras, nd.costo_hora_extra, nd.monto_horas_extras,
        nd.comidas, nd.viaticos_pasajes, nd.prima, nd.otros_bonos, nd.otros_ingresos,
        nd.percepciones, nd.deducciones,
        nd.infonavit_descuento, nd.isr, nd.imss,
        nd.prestamo_personal, nd.otros_descuentos,
        nd.neto, nd.monto_pagado, nd.estatus,
        nd.forma_pago as detalle_forma_pago,
        e.numero_empleado, e.nombre, e.apellido_paterno, e.apellido_materno,
        e.puesto, e.rfc, e.curp, e.nss,
        e.forma_pago, e.banco, e.cuenta_bancaria
    ');
        $this->db->from('nominas_detalle nd');
        $this->db->join('nominas n', 'n.id = nd.nomina_id');
        $this->db->join('empleados e', 'e.id = nd.empleado_id');
        $this->db->where('nd.nomina_id', (int)$nomina_id);
        $this->db->order_by('nd.lugar_origen', 'ASC');
        $this->db->order_by('e.nombre', 'ASC');
        $result = $this->db->get()->result();

        // Adjuntar cuentas bancarias múltiples + cuenta default efectiva
        foreach ($result as &$row) {
            $row->cuentas_bancarias = $this->get_cuentas_empleado($row->empleado_id);
            $row->cuenta_default = null;
            foreach ($row->cuentas_bancarias as $cta) {
                if ((int)$cta->es_default === 1) {
                    $row->cuenta_default = $cta;
                    break;
                }
            }
            if (!$row->cuenta_default && !empty($row->cuentas_bancarias)) {
                $row->cuenta_default = $row->cuentas_bancarias[0];
            }
            // Preferir datos de cuenta default sobre campos legacy del empleado
            if ($row->cuenta_default) {
                $row->banco_pago = $row->cuenta_default->banco ?: $row->banco;
                $row->cuenta_pago = $row->cuenta_default->numero_cuenta ?: $row->cuenta_bancaria;
            } else {
                $row->banco_pago = $row->banco;
                $row->cuenta_pago = $row->cuenta_bancaria;
            }

            // Forma de pago: prioridad al detalle (editable por nómina), fallback al perfil del empleado
            $row->forma_pago = !empty($row->detalle_forma_pago) ? $row->detalle_forma_pago : $row->forma_pago;
        }
        unset($row);

        return $result;
    }

    /**
     * Obtiene cuentas bancarias de un empleado.
     */
    public function get_cuentas_empleado($empleado_id) {
        return $this->db
            ->select('ecb.*, cb.banco')
            ->from('empleados_cuentas_bancarias ecb')
            ->join('cuentas_bancarias cb', 'cb.id = ecb.cuenta_bancaria_id', 'left')
            ->where('ecb.empleado_id', (int)$empleado_id)
            ->where('ecb.estatus', 1)
            ->get()
            ->result();
    }

    /**
     * Guarda o actualiza cuenta bancaria de empleado.
     */
    public function guardar_cuenta_empleado($data) {
        if (!empty($data['id'])) {
            $this->db->where('id', (int)$data['id'])
                     ->update('empleados_cuentas_bancarias', $data);
            return (int)$data['id'];
        }
        $this->db->insert('empleados_cuentas_bancarias', $data);
        return $this->db->insert_id();
    }

    /**
     * Elimina cuenta bancaria de empleado.
     */
    public function eliminar_cuenta_empleado($id) {
        return $this->db->where('id', (int)$id)
                        ->update('empleados_cuentas_bancarias', ['estatus' => 0]);
    }

    /**
     * Establece cuenta default para depósito.
     */
    public function set_cuenta_default($empleado_id, $cuenta_id) {
        $this->db->where('empleado_id', (int)$empleado_id)
                 ->update('empleados_cuentas_bancarias', ['es_default' => 0]);
        $this->db->where('id', (int)$cuenta_id)
                 ->update('empleados_cuentas_bancarias', ['es_default' => 1]);
        return true;
    }

    /**
     * Actualiza campos editables de un detalle de nómina.
     * Se llama vía AJAX desde el modal.
     */
    public function actualizar_detalle_nomina($detalle_id, $data) {
        $allowed = [
            'lugar_origen', 'horas_extras', 'costo_hora_extra', 'monto_horas_extras',
            'comidas', 'viaticos_pasajes', 'prima', 'otros_bonos', 'otros_ingresos',
            'prestamo_personal', 'otros_descuentos', 'forma_pago',
        ];
        $update = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }
        if (empty($update)) {
            return ['success' => false, 'message' => 'Sin cambios'];
        }

        // Recalcular totales
        $det = $this->db->get_where('nominas_detalle', ['id' => (int)$detalle_id])->row();
        if (!$det) {
            return ['success' => false, 'message' => 'Detalle no encontrado'];
        }

        $percepciones = round(
            (float)($update['monto_horas_extras'] ?? $det->monto_horas_extras) +
            (float)($update['comidas'] ?? $det->comidas) +
            (float)($update['viaticos_pasajes'] ?? $det->viaticos_pasajes) +
            (float)($update['prima'] ?? $det->prima) +
            (float)($update['otros_bonos'] ?? $det->otros_bonos) +
            (float)($update['otros_ingresos'] ?? $det->otros_ingresos) +
            (float)$det->sueldo_base,
        2);

        $deducciones = round(
            (float)($update['prestamo_personal'] ?? $det->prestamo_personal) +
            (float)($update['otros_descuentos'] ?? $det->otros_descuentos) +
            (float)$det->infonavit_descuento +
            (float)$det->isr +
            (float)$det->imss +
            (float)($det->deducciones - $det->infonavit_descuento - $det->isr - $det->imss - $det->prestamo_personal - $det->otros_descuentos),
        2);

        $update['percepciones'] = $percepciones;
        $update['deducciones'] = $deducciones;
        $update['neto'] = round($percepciones - $deducciones, 2);

        // Recalcular monto_horas_extras si se editaron horas o costo
        if (array_key_exists('horas_extras', $update) || array_key_exists('costo_hora_extra', $update)) {
            $h = (float)($update['horas_extras'] ?? $det->horas_extras);
            $c = (float)($update['costo_hora_extra'] ?? $det->costo_hora_extra);
            $update['monto_horas_extras'] = round($h * $c, 2);
        }

        $this->db->where('id', (int)$detalle_id)->update('nominas_detalle', $update);

        // Actualizar totales de la cabecera de nómina
        $this->actualizar_totales_nomina($det->nomina_id);

        return ['success' => true, 'message' => 'Actualizado'];
    }

    /**
     * Recalcula totales de la cabecera nominas.
     */
    public function actualizar_totales_nomina($nomina_id) {
        $totales = $this->db
            ->select('SUM(percepciones) as p, SUM(deducciones) as d, SUM(neto) as n')
            ->from('nominas_detalle')
            ->where('nomina_id', (int)$nomina_id)
            ->get()->row();

        $this->db->where('id', (int)$nomina_id)->update('nominas', [
            'total_percepciones' => round((float)$totales->p, 2),
            'total_deducciones'  => round((float)$totales->d, 2),
            'total_neto'         => round((float)$totales->n, 2),
        ]);
    }

    /**
     * Obtiene configuración de automatización de nóminas.
     */
    public function get_configuracion_automatizacion() {
        return $this->db->order_by('id', 'DESC')->limit(1)
                        ->get('nomina_configuracion')->row();
    }

    /**
     * Guarda configuración de automatización.
     */
    public function guardar_configuracion_automatizacion($data) {
        $config = $this->db->order_by('id', 'DESC')->limit(1)->get('nomina_configuracion')->row();
        if ($config) {
            $this->db->where('id', $config->id)->update('nomina_configuracion', $data);
        } else {
            $this->db->insert('nomina_configuracion', $data);
        }
        return true;
    }

    /**
     * Verifica si corresponde crear nómina(s) automática(s).
     * Evalúa Semanal, Quincenal y Mensual según calendario; solo crea un tipo
     * si hay empleados activos con ese tipo_nomina y le corresponde el periodo.
     *
     * @param string|null $fecha_ref Fecha de referencia Y-m-d (para pruebas). Default: hoy.
     * @return array<int, array> Lista de payloads para insert (puede estar vacía).
     */
    public function verificar_creaciones_automaticas($fecha_ref = null) {
        $config = $this->get_configuracion_automatizacion();
        if (!$config || !$config->auto_crear || !$config->activo) {
            return [];
        }

        $hoy = $fecha_ref ? date('Y-m-d', strtotime($fecha_ref)) : date('Y-m-d');
        $dias_antes = max(0, (int)$config->crear_dias_antes);
        $creaciones = [];

        foreach (['Semanal', 'Quincenal', 'Mensual'] as $tipo) {
            $datos = $this->_verificar_creacion_por_tipo($tipo, $hoy, $dias_antes);
            if ($datos && $this->contar_empleados_activos_tipo($tipo) > 0) {
                $creaciones[] = $datos;
            }
        }

        return $creaciones;
    }

    /**
     * @deprecated Use verificar_creaciones_automaticas(). Retorna la primera creación pendiente.
     */
    public function verificar_creacion_automatica($fecha_ref = null) {
        $lista = $this->verificar_creaciones_automaticas($fecha_ref);
        return $lista[0] ?? null;
    }

    /**
     * Cuenta empleados activos con un tipo de nómina dado.
     */
    public function contar_empleados_activos_tipo($tipo_nomina) {
        return (int)$this->db
            ->from('empleados')
            ->where_in('estatus', EmpleadoModel::estatus_laborales_activos())
            ->where('tipo_nomina', $tipo_nomina)
            ->count_all_results();
    }

    /**
     * ¿Le corresponde pago en este periodo a un empleado según su tipo_nomina?
     * (Usado al agregar empleados: la cabecera y el tipo del trabajador deben coincidir.)
     */
    public function tipo_nomina_corresponde_periodo($tipo_nomina, $periodo_inicio, $periodo_fin) {
        $periodo = $this->_periodo_para_fecha_inicio($tipo_nomina, $periodo_inicio);
        if (!$periodo) {
            return false;
        }
        return $periodo['inicio'] === $periodo_inicio && $periodo['fin'] === $periodo_fin;
    }

    private function _verificar_creacion_por_tipo($tipo_nomina, $hoy, $dias_antes) {
        $inicio_objetivo = date('Y-m-d', strtotime($hoy . ' +' . $dias_antes . ' days'));
        $periodo = $this->_periodo_para_fecha_inicio($tipo_nomina, $inicio_objetivo);
        if (!$periodo) {
            return null;
        }

        $inicio_ventana = date('Y-m-d', strtotime($periodo['inicio'] . ' -' . $dias_antes . ' days'));
        if ($hoy < $inicio_ventana || $hoy > $periodo['inicio']) {
            return null;
        }

        $existe = $this->db
            ->where('periodo_inicio', $periodo['inicio'])
            ->where('periodo_fin', $periodo['fin'])
            ->where('tipo_nomina', $tipo_nomina)
            ->count_all_results('nominas');

        if ($existe > 0) {
            return null;
        }

        return [
            'periodo_inicio' => $periodo['inicio'],
            'periodo_fin'    => $periodo['fin'],
            'tipo_nomina'    => $tipo_nomina,
            'fecha_pago'     => $periodo['fin'],
        ];
    }

    /**
     * Calcula inicio/fin de periodo si $fecha es exactamente el día de inicio
     * de un periodo según la frecuencia.
     */
    private function _periodo_para_fecha_inicio($frecuencia, $fecha) {
        $ts = strtotime($fecha);
        $dia = (int)date('d', $ts);

        switch ($frecuencia) {
            case 'Semanal':
                // El periodo semanal inicia en lunes
                if ((int)date('N', $ts) !== 1) {
                    return null;
                }
                return [
                    'inicio' => date('Y-m-d', $ts),
                    'fin'    => date('Y-m-d', strtotime('sunday this week', $ts)),
                ];
            case 'Quincenal':
                if ($dia === 1) {
                    return [
                        'inicio' => date('Y-m-01', $ts),
                        'fin'    => date('Y-m-15', $ts),
                    ];
                }
                if ($dia === 16) {
                    return [
                        'inicio' => date('Y-m-16', $ts),
                        'fin'    => date('Y-m-t', $ts),
                    ];
                }
                return null;
            case 'Mensual':
                if ($dia !== 1) {
                    return null;
                }
                return [
                    'inicio' => date('Y-m-01', $ts),
                    'fin'    => date('Y-m-t', $ts),
                ];
            default:
                return null;
        }
    }

    /**
     * Crea nómina(s) automática(s), agrega empleados y calcula.
     *
     * @param string|null $fecha_ref Fecha de referencia Y-m-d (para pruebas).
     * @return array|null
     */
    public function crear_nomina_automatica($fecha_ref = null) {
        $pendientes = $this->verificar_creaciones_automaticas($fecha_ref);
        if (empty($pendientes)) {
            return null;
        }

        $creadas = [];
        foreach ($pendientes as $datos) {
            $resultado = $this->_insertar_nomina_automatica($datos);
            if ($resultado) {
                $creadas[] = $resultado;
            }
        }

        if (empty($creadas)) {
            return null;
        }

        if (count($creadas) === 1) {
            return $creadas[0];
        }

        return [
            'multiple' => true,
            'creadas'  => $creadas,
            'nomina_id' => $creadas[0]['nomina_id'],
            'estatus'   => $creadas[0]['estatus'],
            'calculada' => !empty($creadas[0]['calculada']),
            'message'   => count($creadas) . ' nóminas automáticas creadas',
        ];
    }

    /**
     * Inserta una nómina automática individual.
     */
    private function _insertar_nomina_automatica(array $datos) {
        $datos['folio'] = $this->generar_folio();
        $datos['usuario_creacion'] = 1;
        $datos['estatus'] = 'Borrador';
        $datos['observaciones'] = 'Generada automáticamente';

        $this->db->insert('nominas', $datos);
        $nomina_id = (int)$this->db->insert_id();
        if (!$nomina_id) {
            return null;
        }

        $this->agregar_empleados_nomina($nomina_id, $datos['tipo_nomina']);

        $calc = $this->calcular_nomina($nomina_id);
        $calculada = !empty($calc['success']);
        $estatus = $calculada ? 'Calculada' : 'Borrador';

        $this->db->update('nomina_configuracion', [
            'ultima_ejecucion' => date('Y-m-d H:i:s'),
        ]);

        $this->_crear_alerta_nomina_automatica($nomina_id, $datos, $calculada, $calc);

        return [
            'nomina_id'   => $nomina_id,
            'tipo_nomina' => $datos['tipo_nomina'],
            'periodo'     => $datos['periodo_inicio'] . ' — ' . $datos['periodo_fin'],
            'estatus'     => $estatus,
            'calculada'   => $calculada,
            'totales'     => $calc['totales'] ?? null,
            'message'     => $calculada
                ? ('Nómina ' . $datos['tipo_nomina'] . ' automática creada y calculada')
                : ('Nómina ' . $datos['tipo_nomina'] . ' automática creada; cálculo pendiente'),
        ];
    }

    /**
     * Crea alerta interna al crear nómina automática.
     */
    private function _crear_alerta_nomina_automatica($nomina_id, $datos, $calculada = false, $calc = []) {
        if (!$this->db->table_exists('alertas_internas')) return;

        $nomina = $this->db->get_where('nominas', ['id' => (int)$nomina_id])->row();
        if (!$nomina) return;

        $neto = isset($calc['totales']['neto'])
            ? '$' . number_format((float)$calc['totales']['neto'], 2)
            : '—';

        $mensaje = $calculada
            ? "Se generó y calculó la nómina {$nomina->folio} ({$datos['tipo_nomina']}) para el periodo {$datos['periodo_inicio']} al {$datos['periodo_fin']}. Neto: {$neto}. Revise y procese el pago."
            : "Se generó la nómina {$nomina->folio} ({$datos['tipo_nomina']}) para el periodo {$datos['periodo_inicio']} al {$datos['periodo_fin']}. Estatus: Pendiente de cálculo.";

        $this->db->insert('alertas_internas', [
            'tipo'        => $calculada ? 'nomina_calculada' : 'nomina_creada',
            'titulo'      => $calculada ? 'Nómina automática lista para revisar' : 'Nómina generada automáticamente',
            'mensaje'     => $mensaje,
            'modulo'      => 'Recursos Humanos',
            'url'         => 'rh/Nomina',
            'icono'       => 'fa-money-bill-wave',
            'fecha'       => date('Y-m-d H:i:s'),
            'leida'       => 0,
        ]);
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

    private function calcular_conceptos_empleado($empleado, $sueldo, $tipo_nomina, $periodo_inicio = null, $periodo_fin = null, $flags = []) {
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

        // Flags de deducciones (desde configuración de automatización)
        $aplicar_isr       = isset($flags['isr'])       ? (bool)$flags['isr']       : false;
        $aplicar_imss      = isset($flags['imss'])      ? (bool)$flags['imss']      : true;
        $aplicar_infonavit = isset($flags['infonavit']) ? (bool)$flags['infonavit'] : true;

        $isr_pct = (float)$empleado->isr_porcentaje;
        $isr = ($aplicar_isr && $isr_pct > 0) ? round($sueldo * ($isr_pct / 100), 2) : 0;

        $imss = $aplicar_imss ? (float)$empleado->imss_cuota : 0;
        if ($imss <= 0 && (float)$empleado->salario_base_diario > 0) {
            $imss = 0;
        }

        $pension_pct = (float)$empleado->pension_alimenticia_porcentaje;
        $pension_monto = (float)$empleado->pension_alimenticia_monto;
        if ($pension_pct > 0) {
            $pension_monto += round($sueldo * ($pension_pct / 100), 2);
        }

        $infonavit = 0;
        if ($aplicar_infonavit && !empty($empleado->tiene_infonavit) && (float)$empleado->descuento_infonavit > 0) {
            $infonavit = round((float)$empleado->descuento_infonavit, 2);
        }

        // Siempre se agregan los conceptos de deducción; si la bandera está apagada, el monto es 0
        $conceptos[] = ['tipo' => 'Deducción', 'concepto' => 'ISR',       'monto' => $isr];
        $conceptos[] = ['tipo' => 'Deducción', 'concepto' => 'IMSS',      'monto' => $imss];
        $conceptos[] = ['tipo' => 'Deducción', 'concepto' => 'INFONAVIT', 'monto' => $infonavit];

        if ($pension_monto > 0) {
            $conceptos[] = ['tipo' => 'Deducción', 'concepto' => 'Pensión Alimenticia', 'monto' => round($pension_monto, 2)];
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

    public function cancelar_nomina($nomina_id, $motivo) {
        $nomina = $this->db->get_where('nominas', ['id' => (int)$nomina_id])->row();
        if (!$nomina) {
            return ['success' => false, 'message' => 'Nómina no encontrada'];
        }
        if (in_array($nomina->estatus, ['Pagada', 'Parcial'])) {
            return ['success' => false, 'message' => 'No se puede cancelar una nómina con pagos procesados. Use el sistema de notas de ajuste.'];
        }
        if ($nomina->estatus === 'Cancelada') {
            return ['success' => false, 'message' => 'La nómina ya está cancelada'];
        }

        $this->db->trans_start();
        $this->db->where('id', (int)$nomina_id)->update('nominas', ['estatus' => 'Cancelada']);
        $usuario_id = $this->session->userdata('id') ?: $this->session->userdata('user_id');
        $this->db->insert('nominas_cancelaciones', [
            'nomina_id' => (int)$nomina_id,
            'motivo' => substr(trim($motivo), 0, 500),
            'usuario_id' => $usuario_id ?: null,
        ]);
        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Error al cancelar la nómina'];
        }
        return ['success' => true, 'message' => 'Nómina cancelada correctamente'];
    }

    public function get_notas_nomina($nomina_id) {
        return $this->db
            ->select('nn.*, u.nombre as usuario_nombre')
            ->from('nominas_notas nn')
            ->join('usuarios u', 'u.id = nn.usuario_id', 'left')
            ->where('nn.nomina_id', (int)$nomina_id)
            ->order_by('nn.created_at', 'DESC')
            ->get()
            ->result();
    }

    public function agregar_nota_nomina($nomina_id, $data) {
        $usuario_id = $this->session->userdata('id') ?: $this->session->userdata('user_id');
        $this->db->insert('nominas_notas', [
            'nomina_id' => (int)$nomina_id,
            'tipo' => in_array($data['tipo'] ?? '', ['Ajuste','Corrección','Reclasificación']) ? $data['tipo'] : 'Ajuste',
            'descripcion' => trim($data['descripcion'] ?? ''),
            'monto' => isset($data['monto']) && $data['monto'] !== '' ? round((float)$data['monto'], 2) : null,
            'usuario_id' => $usuario_id ?: null,
        ]);
        return ['success' => true, 'message' => 'Nota agregada correctamente', 'id' => $this->db->insert_id()];
    }

    /**
     * Genera la lista de periodos del mes con su nómina asociada (si existe).
     *
     * @param int $mes  1-12
     * @param int $anio YYYY
     * @param string $tipo Semanal|Quincenal|Mensual
     * @return array  Cada elemento: ['inicio','fin','label','nomina'=>null|object]
     */
    public function get_planeador_mensual($mes, $anio, $tipo) {
        $periodos = $this->_generar_periodos_mes($mes, $anio, $tipo);
        if (empty($periodos)) {
            return [];
        }

        $primer_inicio = $periodos[0]['inicio'];
        $ultimo_fin    = $periodos[count($periodos) - 1]['fin'];

        $nominas = $this->db
            ->select('id, folio, tipo_nomina, periodo_inicio, periodo_fin, estatus, total_neto')
            ->from('nominas')
            ->where('periodo_inicio >=', $primer_inicio)
            ->where('periodo_inicio <=', $ultimo_fin)
            ->get()
            ->result();

        $mapa = [];
        foreach ($nominas as $nom) {
            $mapa[$nom->periodo_inicio] = $nom;
        }

        foreach ($periodos as &$p) {
            $p['nomina'] = $mapa[$p['inicio']] ?? null;
        }

        return $periodos;
    }

    /**
     * Genera los periodos del mes según el tipo de nómina.
     *
     * @param int $mes
     * @param int $anio
     * @param string $tipo
     * @return array
     */
    private function _generar_periodos_mes($mes, $anio, $tipo) {
        $periodos = [];
        $primer_dia = sprintf('%04d-%02d-01', $anio, $mes);
        $ultimo_dia = date('Y-m-t', strtotime($primer_dia));

        switch ($tipo) {
            case 'Semanal':
                $cursor = date('Y-m-d', strtotime('monday this week', strtotime($primer_dia)));
                if ($cursor < $primer_dia) {
                    $cursor = date('Y-m-d', strtotime($cursor . ' +7 days'));
                }
                while ($cursor <= $ultimo_dia) {
                    $fin = date('Y-m-d', strtotime($cursor . ' +6 days'));
                    $periodos[] = [
                        'inicio' => $cursor,
                        'fin'    => $fin,
                        'label'  => date('d M', strtotime($cursor)) . ' – ' . date('d M', strtotime($fin)),
                    ];
                    $cursor = date('Y-m-d', strtotime($cursor . ' +7 days'));
                }
                break;

            case 'Quincenal':
                $periodos[] = [
                    'inicio' => date('Y-m-01', strtotime($primer_dia)),
                    'fin'    => date('Y-m-15', strtotime($primer_dia)),
                    'label'  => '1ra Quincena: ' . date('d M', strtotime(date('Y-m-01', strtotime($primer_dia)))) . ' – ' . date('d M', strtotime(date('Y-m-15', strtotime($primer_dia)))),
                ];
                $periodos[] = [
                    'inicio' => date('Y-m-16', strtotime($primer_dia)),
                    'fin'    => $ultimo_dia,
                    'label'  => '2da Quincena: ' . date('d M', strtotime(date('Y-m-16', strtotime($primer_dia)))) . ' – ' . date('d M', strtotime($ultimo_dia)),
                ];
                break;

            case 'Mensual':
                $periodos[] = [
                    'inicio' => $primer_dia,
                    'fin'    => $ultimo_dia,
                    'label'  => date('F Y', strtotime($primer_dia)),
                ];
                break;
        }

        return $periodos;
    }
}
