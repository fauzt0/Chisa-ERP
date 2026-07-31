<?php
/**
 * Reset limpio + seed demo unificado de nóminas.
 *
 * RESET: vacía nominas_pagos_log, nominas_conceptos, nominas_detalle, nominas.
 * NO toca empleados, usuarios, cuentas bancarias ni nomina_configuracion.
 *
 * CLI:  php run_seed_nominas_reset_demo.php apply
 *       php run_seed_nominas_reset_demo.php revert
 * Web:  /database/run_seed_nominas_reset_demo.php?action=apply&key=chisa_demo_seed
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');

$action = $argv[1] ?? ($_GET['action'] ?? '');
$isCli  = (PHP_SAPI === 'cli');

if (!$isCli && ($_GET['key'] ?? '') !== 'chisa_demo_seed') {
    http_response_code(403);
    exit('Forbidden');
}

if (!in_array($action, ['apply', 'revert'], true)) {
    $msg = "Uso: php run_seed_nominas_reset_demo.php apply|revert\n";
    exit($isCli ? $msg : nl2br($msg));
}

require dirname(__DIR__) . '/application/config/database.php';
$cfg = $db['default'];

$mysqli = @new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($mysqli->connect_error) {
    exit('Error de conexión: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

const OBS_PREFIX = 'Seed demo reset — ';
const FALLBACK_EMPLEADOS = [5, 6, 7, 8, 9, 10, 11, 12, 15, 16, 17];

/** Montos fijos entrenamiento1.jpg (NOM000022, semana 06–12 jul 2026). */
const ENTRENAMIENTO1_DETALLE = [
    5  => ['lugar' => 'OFICINA',         'dias' => 7, 'sueldo_base' => 10160.02, 'sueldo_diario' => 1849.15, 'he' => 0, 'costo_he' => 0, 'monto_he' => 0, 'comidas' => 0, 'otros_bonos' => 0, 'percepciones' => 10160.02, 'deducciones' => 235.53, 'otros_desc' => 235.53, 'infonavit' => 0, 'neto' => 9924.49],
    16 => ['lugar' => 'OFICINA',         'dias' => 7, 'sueldo_base' => 5051.41,  'sueldo_diario' => 866.47,  'he' => 0, 'costo_he' => 0, 'monto_he' => 0, 'comidas' => 0, 'otros_bonos' => 0, 'percepciones' => 5051.41,  'deducciones' => 0,      'otros_desc' => 0,      'infonavit' => 0, 'neto' => 5051.41],
    9  => ['lugar' => 'OFICINA',         'dias' => 7, 'sueldo_base' => 6175.40,  'sueldo_diario' => 1055.05, 'he' => 0, 'costo_he' => 0, 'monto_he' => 0, 'comidas' => 1700, 'otros_bonos' => 0, 'percepciones' => 7875.40,  'deducciones' => 240.54, 'otros_desc' => 0,      'infonavit' => 240.54, 'neto' => 7634.86],
    11 => ['lugar' => 'OFICINA',         'dias' => 7, 'sueldo_base' => 3551.65,  'sueldo_diario' => 584.28,  'he' => 0, 'costo_he' => 0, 'monto_he' => 0, 'comidas' => 0, 'otros_bonos' => 0, 'percepciones' => 3551.65,  'deducciones' => 307.87, 'otros_desc' => 0,      'infonavit' => 307.87, 'neto' => 3243.78],
    7  => ['lugar' => 'OFICINA',         'dias' => 7, 'sueldo_base' => 5000.00,  'sueldo_diario' => 1210.39, 'he' => 0, 'costo_he' => 0, 'monto_he' => 0, 'comidas' => 0, 'otros_bonos' => 0, 'percepciones' => 5000.00,  'deducciones' => 0,      'otros_desc' => 0,      'infonavit' => 0, 'neto' => 5000.00],
    15 => ['lugar' => 'OFICINA',         'dias' => 7, 'sueldo_base' => 3341.59,  'sueldo_diario' => 546.65,  'he' => 7, 'costo_he' => 136.66, 'monto_he' => 956.64, 'comidas' => 0, 'otros_bonos' => 0, 'percepciones' => 4298.23,  'deducciones' => 0,      'otros_desc' => 0,      'infonavit' => 0, 'neto' => 4298.23],
    17 => ['lugar' => 'OFICINA',         'dias' => 7, 'sueldo_base' => 2211.34,  'sueldo_diario' => 315.04,  'he' => 0, 'costo_he' => 0, 'monto_he' => 0, 'comidas' => 0, 'otros_bonos' => 0, 'percepciones' => 2211.34,  'deducciones' => 0,      'otros_desc' => 0,      'infonavit' => 0, 'neto' => 2211.34],
    10 => ['lugar' => 'OFICINA',         'dias' => 7, 'sueldo_base' => 6055.60,  'sueldo_diario' => 1055.50, 'he' => 0, 'costo_he' => 0, 'monto_he' => 0, 'comidas' => 0, 'otros_bonos' => 0, 'percepciones' => 6055.60,  'deducciones' => 0,      'otros_desc' => 0,      'infonavit' => 0, 'neto' => 6055.60],
    12 => ['lugar' => 'LAGUNAS OAXACA',  'dias' => 7, 'sueldo_base' => 4526.92,  'sueldo_diario' => 772.40,  'he' => 0, 'costo_he' => 0, 'monto_he' => 0, 'comidas' => 1700, 'otros_bonos' => 0, 'percepciones' => 6226.92,  'deducciones' => 0,      'otros_desc' => 0,      'infonavit' => 0, 'neto' => 6226.92],
    8  => ['lugar' => 'TUXTLA',          'dias' => 7, 'sueldo_base' => 4501.65,  'sueldo_diario' => 763.00,  'he' => 0, 'costo_he' => 0, 'monto_he' => 0, 'comidas' => 1700, 'otros_bonos' => 0, 'percepciones' => 6201.65,  'deducciones' => 1584.36,'otros_desc' => 0,      'infonavit' => 1584.36,'neto' => 4617.29],
    6  => ['lugar' => 'PRODUCCION',      'dias' => 7, 'sueldo_base' => 3740.67,  'sueldo_diario' => 634.56,  'he' => 0, 'costo_he' => 0, 'monto_he' => 0, 'comidas' => 0, 'otros_bonos' => 2000, 'percepciones' => 5740.67,  'deducciones' => 1145.52,'otros_desc' => 0,      'infonavit' => 1145.52,'neto' => 4595.15],
];

const ENTRENAMIENTO1_TOTALES = [
    'percepciones' => 62372.89,
    'deducciones'  => 3513.82,
    'neto'         => 58859.07,
];

function conteos_protegidos(mysqli $db): array
{
    $tablas = ['empleados', 'usuarios', 'empleados_cuentas_bancarias', 'nomina_configuracion'];
    $out = [];
    foreach ($tablas as $t) {
        if (!$db->query("SHOW TABLES LIKE '$t'")->num_rows) {
            $out[$t] = null;
            continue;
        }
        $r = $db->query("SELECT COUNT(*) AS c FROM `$t`");
        $out[$t] = $r ? (int)$r->fetch_assoc()['c'] : null;
    }
    return $out;
}

function conteos_nomina(mysqli $db): array
{
    $out = [];
    foreach (['nominas_pagos_log', 'nominas_conceptos', 'nominas_detalle', 'nominas'] as $t) {
        if (!$db->query("SHOW TABLES LIKE '$t'")->num_rows) {
            $out[$t] = 0;
            continue;
        }
        $r = $db->query("SELECT COUNT(*) AS c FROM `$t`");
        $out[$t] = $r ? (int)$r->fetch_assoc()['c'] : 0;
    }
    $r = $db->query("SELECT estatus, COUNT(*) AS c FROM nominas GROUP BY estatus ORDER BY estatus");
    $out['nominas_por_estatus'] = [];
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $out['nominas_por_estatus'][$row['estatus']] = (int)$row['c'];
        }
    }
    return $out;
}

function reset_nominas(mysqli $db): array
{
    $orden = ['nominas_pagos_log', 'nominas_conceptos', 'nominas_detalle', 'nominas'];
    $deleted = [];

    $db->begin_transaction();
    try {
        foreach ($orden as $tabla) {
            if (!$db->query("SHOW TABLES LIKE '$tabla'")->num_rows) {
                $deleted[$tabla] = 0;
                continue;
            }
            $db->query("DELETE FROM `$tabla`");
            if ($db->errno) {
                throw new RuntimeException("Error al vaciar $tabla: " . $db->error);
            }
            $deleted[$tabla] = $db->affected_rows;
            $db->query("ALTER TABLE `$tabla` AUTO_INCREMENT = 1");
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollback();
        throw $e;
    }

    return $deleted;
}

function cargar_empleados_semanal(mysqli $db): array
{
    $res = $db->query("
        SELECT id, salario_base_diario, lugar_pago, costo_hora_extra,
               tiene_infonavit, descuento_infonavit
        FROM empleados
        WHERE estatus IN (1, 2) AND tipo_nomina = 'Semanal'
        ORDER BY id
    ");
    $empleados = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $empleados[(int)$row['id']] = $row;
        }
        $res->free();
    }
    if (empty($empleados)) {
        $ids = implode(',', array_map('intval', FALLBACK_EMPLEADOS));
        $res = $db->query("
            SELECT id, salario_base_diario, lugar_pago, costo_hora_extra,
                   tiene_infonavit, descuento_infonavit
            FROM empleados WHERE id IN ($ids)
        ");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $empleados[(int)$row['id']] = $row;
            }
            $res->free();
        }
    }
    return $empleados;
}

function cargar_empleados_quincenal(mysqli $db): array
{
    $res = $db->query("
        SELECT id, salario_base_diario, lugar_pago, costo_hora_extra,
               tiene_infonavit, descuento_infonavit
        FROM empleados
        WHERE estatus IN (1, 2) AND tipo_nomina = 'Quincenal'
        ORDER BY id
    ");
    $empleados = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $empleados[(int)$row['id']] = $row;
        }
        $res->free();
    }
    return $empleados;
}

function folio_num($n): string
{
    return 'NOM' . str_pad((string)$n, 6, '0', STR_PAD_LEFT);
}

/**
 * Genera semanas lunes–domingo consecutivas.
 *
 * @return array<int, array{0:string,1:string}>
 */
function semanas_consecutivas(string $inicio, int $cantidad): array
{
    $d = new DateTime($inicio);
    $semanas = [];
    for ($i = 0; $i < $cantidad; $i++) {
        $ini = $d->format('Y-m-d');
        $fin = (clone $d)->modify('+6 days')->format('Y-m-d');
        $semanas[] = [$ini, $fin];
        $d->modify('+7 days');
    }
    return $semanas;
}

function matriz_nominas(): array
{
    $nominas = [];
    $folio = 100;

    // Feb–may 2026: 16 semanas; semana 12 (abr 20–26) = Parcial
    $semanasHist = semanas_consecutivas('2026-02-02', 16);
    foreach ($semanasHist as $idx => [$ini, $fin]) {
        $esParcial = ($idx === 11);
        $nominas[] = [
            'folio'          => folio_num($folio++),
            'periodo_inicio' => $ini,
            'periodo_fin'    => $fin,
            'tipo_nomina'    => 'Semanal',
            'fecha_pago'     => $fin,
            'estatus'        => $esParcial ? 'Parcial' : 'Pagada',
            'observaciones'  => OBS_PREFIX . ($esParcial ? 'abr sem 4 (pago parcial histórico)' : 'histórico feb–may 2026'),
            'modo'           => $esParcial ? 'parcial' : 'calculado',
            'parcial_pagados'=> 6,
            'variacion_idx'  => $idx,
            'pagos_log'      => $esParcial,
        ];
    }

    // Puente junio
    foreach ([
        ['2026-05-25', '2026-05-31', 'Pagada', 'may sem 5'],
        ['2026-06-01', '2026-06-07', 'Pagada', 'jun sem 1'],
        ['2026-06-08', '2026-06-14', 'Pagada', 'jun sem 2'],
        ['2026-06-15', '2026-06-21', 'Pagada', 'jun sem 3'],
    ] as $i => [$ini, $fin, $est, $label]) {
        $nominas[] = [
            'folio'          => folio_num($folio++),
            'periodo_inicio' => $ini,
            'periodo_fin'    => $fin,
            'tipo_nomina'    => 'Semanal',
            'fecha_pago'     => $fin,
            'estatus'        => $est,
            'observaciones'  => OBS_PREFIX . $label,
            'modo'           => 'calculado',
            'parcial_pagados'=> 0,
            'variacion_idx'  => 16 + $i,
            'pagos_log'      => false,
        ];
    }

    // Jun tardía — Calculada (campana danger si vencida)
    $nominas[] = [
        'folio'          => folio_num($folio++),
        'periodo_inicio' => '2026-06-22',
        'periodo_fin'    => '2026-06-28',
        'tipo_nomina'    => 'Semanal',
        'fecha_pago'     => '2026-06-28',
        'estatus'        => 'Calculada',
        'observaciones'  => OBS_PREFIX . 'jun sem 4 (pendiente de pago)',
        'modo'           => 'calculado',
        'parcial_pagados'=> 0,
        'variacion_idx'  => 20,
        'pagos_log'      => false,
    ];

    // Ciclo actual jul–ago (folios bajos según spec)
    $recientes = [
        ['NOM000021', '2026-06-29', '2026-07-05', 'Pagada',    'puente a julio',           'calculado', 0, 21, false],
        ['NOM000022', '2026-07-06', '2026-07-12', 'Pagada',    'entrenamiento1.jpg',       'entrenamiento1', 0, 22, false],
        ['NOM000023', '2026-07-13', '2026-07-19', 'Pagada',    'semana reciente pagada',   'calculado', 0, 23, false],
        ['NOM000024', '2026-07-20', '2026-07-26', 'Parcial',   'en proceso de pago',       'parcial', 6, 24, true],
        ['NOM000025', '2026-07-27', '2026-08-02', 'Calculada', 'pendiente — campana',      'calculado', 0, 25, false],
        ['NOM000026', '2026-08-03', '2026-08-09', 'Borrador',  'lista para calcular',      'borrador', 0, 26, false],
    ];
    foreach ($recientes as [$fol, $ini, $fin, $est, $label, $modo, $pp, $vidx, $plog]) {
        $nominas[] = [
            'folio'          => $fol,
            'periodo_inicio' => $ini,
            'periodo_fin'    => $fin,
            'tipo_nomina'    => 'Semanal',
            'fecha_pago'     => $fin,
            'estatus'        => $est,
            'observaciones'  => OBS_PREFIX . $label,
            'modo'           => $modo,
            'parcial_pagados'=> $pp,
            'variacion_idx'  => $vidx,
            'pagos_log'      => $plog,
        ];
    }

    return $nominas;
}

/** @return array{0:float,1:float,2:float,3:int,4:int,5:array} */
function insertar_detalle_calculado(
    mysqli $db,
    int $nominaId,
    array $empleados,
    string $estatusCab,
    string $fin,
    int $variacionIdx,
    int $parcialPagados
): array {
    $variacionesBase = [
        [7, 1700, 2000, 0],
        [7, 1700, 0, 0],
        [7, 0, 1500, 5],
        [6, 1700, 2000, 0],
        [7, 1700, 1000, 3],
        [7, 1700, 2000, 0],
        [7, 0, 0, 8],
        [7, 1700, 2500, 0],
        [7, 1700, 2000, 0],
        [7, 1700, 0, 4],
        [6, 1700, 2000, 0],
        [7, 1700, 1800, 6],
        [7, 1700, 2000, 0],
    ];
    $v = $variacionesBase[$variacionIdx % count($variacionesBase)];
    [$dias, $comidasBase, $bonoTeodoro, $horasMiguel] = $v;

    $sumP = $sumD = $sumN = 0.0;
    $pagados = $pendientes = 0;
    $detalleIdsPagados = [];
    $empIndex = 0;
    $totalEmpleados = count($empleados);

    foreach ($empleados as $empId => $e) {
        $empIndex++;
        $diario = (float)$e['salario_base_diario'];
        $lugar  = $db->real_escape_string($e['lugar_pago'] ?: 'OFICINA');
        $costoHe = (float)$e['costo_hora_extra'];
        $infonavit = (!empty($e['tiene_infonavit']) && (float)$e['descuento_infonavit'] > 0)
            ? (float)$e['descuento_infonavit'] : 0.0;

        $sueldoBase = round($diario * $dias, 2);
        $comidas = (in_array($empId, [9, 12, 8], true) && $comidasBase > 0) ? (float)$comidasBase : 0.0;
        $otrosBonos = ($empId === 6) ? (float)$bonoTeodoro : 0.0;
        $horasExtras = 0.0;
        $montoHe = 0.0;
        if ($empId === 15 && $horasMiguel > 0 && $costoHe > 0) {
            $horasExtras = (float)$horasMiguel;
            $montoHe = round($horasExtras * $costoHe, 2);
        }
        $percepciones = round($sueldoBase + $comidas + $otrosBonos + $montoHe, 2);
        $otrosDesc = ($empId === 5 && $variacionIdx % 3 === 0) ? 235.53 : 0.0;
        $deducciones = round($infonavit + $otrosDesc, 2);
        $neto = round($percepciones - $deducciones, 2);

        $estDet = 'Pendiente';
        $montoPagado = 0.0;
        $fechaPagoSql = 'NULL';

        if ($estatusCab === 'Pagada') {
            $estDet = 'Pagado';
            $montoPagado = $neto;
            $fechaPagoSql = "'" . $fin . " 18:00:00'";
            $pagados++;
        } elseif ($estatusCab === 'Parcial') {
            $limite = $parcialPagados > 0 ? $parcialPagados : (int)ceil($totalEmpleados / 2);
            if ($empIndex <= $limite) {
                $estDet = 'Pagado';
                $montoPagado = $neto;
                $fechaPagoSql = "'" . $fin . " 18:00:00'";
                $pagados++;
            } else {
                $pendientes++;
            }
        } else {
            $pendientes++;
        }

        $sql = sprintf(
            "INSERT INTO nominas_detalle (
                nomina_id, empleado_id, lugar_origen, dias_trabajados,
                sueldo_base, sueldo_diario, horas_extras, costo_hora_extra, monto_horas_extras,
                comidas, viaticos_pasajes, prima, otros_bonos, otros_ingresos,
                percepciones, deducciones, prestamo_personal, otros_descuentos, infonavit_descuento,
                neto, monto_pagado, fecha_pago, estatus
            ) VALUES (
                %d, %d, '%s', %.2f,
                %.2f, %.2f, %.2f, %.2f, %.2f,
                %.2f, 0, 0, %.2f, 0,
                %.2f, %.2f, 0, %.2f, %.2f,
                %.2f, %.2f, %s, '%s'
            )",
            $nominaId, $empId, $lugar, $dias,
            $sueldoBase, $diario, $horasExtras, $costoHe, $montoHe,
            $comidas, $otrosBonos,
            $percepciones, $deducciones, $otrosDesc, $infonavit,
            $neto, $montoPagado, $fechaPagoSql, $estDet
        );
        if (!$db->query($sql)) {
            throw new RuntimeException("Error detalle emp $empId: " . $db->error);
        }
        $detId = (int)$db->insert_id;
        if ($estDet === 'Pagado') {
            $detalleIdsPagados[] = ['id' => $detId, 'empleado_id' => $empId, 'monto' => $neto];
        }

        $sumP += $percepciones;
        $sumD += $deducciones;
        $sumN += $neto;
    }

    return [$sumP, $sumD, $sumN, $pagados, $pendientes, $detalleIdsPagados];
}

function insertar_detalle_entrenamiento1(mysqli $db, int $nominaId, string $fin): array
{
    $sumP = $sumD = $sumN = 0.0;
    $detalleIdsPagados = [];

    foreach (ENTRENAMIENTO1_DETALLE as $empId => $d) {
        $lugar = $db->real_escape_string($d['lugar']);
        $sql = sprintf(
            "INSERT INTO nominas_detalle (
                nomina_id, empleado_id, lugar_origen, dias_trabajados,
                sueldo_base, sueldo_diario, horas_extras, costo_hora_extra, monto_horas_extras,
                comidas, viaticos_pasajes, prima, otros_bonos, otros_ingresos,
                percepciones, deducciones, prestamo_personal, otros_descuentos, infonavit_descuento,
                neto, monto_pagado, fecha_pago, estatus
            ) VALUES (
                %d, %d, '%s', %.2f,
                %.2f, %.2f, %.2f, %.2f, %.2f,
                %.2f, 0, 0, %.2f, 0,
                %.2f, %.2f, 0, %.2f, %.2f,
                %.2f, %.2f, '%s 18:00:00', 'Pagado'
            )",
            $nominaId, $empId, $lugar, $d['dias'],
            $d['sueldo_base'], $d['sueldo_diario'], $d['he'], $d['costo_he'], $d['monto_he'],
            $d['comidas'], $d['otros_bonos'],
            $d['percepciones'], $d['deducciones'], $d['otros_desc'], $d['infonavit'],
            $d['neto'], $d['neto'], $fin
        );
        if (!$db->query($sql)) {
            throw new RuntimeException("Error entrenamiento1 emp $empId: " . $db->error);
        }
        $detId = (int)$db->insert_id;
        $detalleIdsPagados[] = ['id' => $detId, 'empleado_id' => $empId, 'monto' => $d['neto']];
        $sumP += $d['percepciones'];
        $sumD += $d['deducciones'];
        $sumN += $d['neto'];
    }

    // Totales exactos del entrenamiento
    return [
        ENTRENAMIENTO1_TOTALES['percepciones'],
        ENTRENAMIENTO1_TOTALES['deducciones'],
        ENTRENAMIENTO1_TOTALES['neto'],
        count(ENTRENAMIENTO1_DETALLE),
        0,
        $detalleIdsPagados,
    ];
}

function insertar_detalle_borrador(mysqli $db, int $nominaId, array $empleados): void
{
    foreach ($empleados as $empId => $e) {
        $lugar = $db->real_escape_string($e['lugar_pago'] ?: 'OFICINA');
        $sql = "INSERT INTO nominas_detalle (nomina_id, empleado_id, lugar_origen, estatus)
                VALUES ($nominaId, $empId, '$lugar', 'Pendiente')";
        if (!$db->query($sql)) {
            throw new RuntimeException("Error borrador emp $empId: " . $db->error);
        }
    }
}

function insertar_pagos_log(mysqli $db, int $nominaId, array $detallePagados, string $fin): int
{
    if (!$db->query("SHOW TABLES LIKE 'nominas_pagos_log'")->num_rows) {
        return 0;
    }
    $n = 0;
    foreach ($detallePagados as $d) {
        $sql = sprintf(
            "INSERT INTO nominas_pagos_log (nomina_id, nomina_detalle_id, empleado_id, monto, monto_periodo, monto_adeudos, poliza_id, usuario_id, fecha_pago)
             VALUES (%d, %d, %d, %.2f, %.2f, 0, NULL, 1, '%s 18:00:00')",
            $nominaId, $d['id'], $d['empleado_id'], $d['monto'], $d['monto'], $fin
        );
        if (!$db->query($sql)) {
            throw new RuntimeException('Error pagos_log: ' . $db->error);
        }
        $n++;
    }
    return $n;
}

function seed_nominas(mysqli $db): array
{
    $empleadosSem = cargar_empleados_semanal($db);
    if (empty($empleadosSem)) {
        throw new RuntimeException('No hay empleados activos Semanal. Ejecuta empleados_sueldos_entrenamiento1.sql');
    }

    $matriz = matriz_nominas();
    $creadas = [];
    $pagosLogTotal = 0;

    $stmtNom = $db->prepare("
        INSERT INTO nominas (
            folio, periodo_inicio, periodo_fin, tipo_nomina, fecha_pago,
            total_percepciones, total_deducciones, total_neto,
            poliza_id, estatus, observaciones, usuario_creacion, fecha_creacion
        ) VALUES (?, ?, ?, ?, ?, 0, 0, 0, NULL, ?, ?, 1, ?)
    ");

    foreach ($matriz as $nom) {
        $fin = $nom['periodo_fin'];
        $fechaCreacion = $fin . ' 17:30:00';
        $estatusCab = $nom['estatus'];

        $stmtNom->bind_param(
            'ssssssss',
            $nom['folio'],
            $nom['periodo_inicio'],
            $nom['periodo_fin'],
            $nom['tipo_nomina'],
            $nom['fecha_pago'],
            $estatusCab,
            $nom['observaciones'],
            $fechaCreacion
        );
        if (!$stmtNom->execute()) {
            throw new RuntimeException('Error insert nominas ' . $nom['folio'] . ': ' . $stmtNom->error);
        }
        $nominaId = (int)$db->insert_id;

        $detallePagados = [];
        $pagados = $pendientes = 0;
        $sumP = $sumD = $sumN = 0.0;

        if ($nom['modo'] === 'borrador') {
            insertar_detalle_borrador($db, $nominaId, $empleadosSem);
            $pendientes = count($empleadosSem);
        } elseif ($nom['modo'] === 'entrenamiento1') {
            [$sumP, $sumD, $sumN, $pagados, $pendientes, $detallePagados] =
                insertar_detalle_entrenamiento1($db, $nominaId, $fin);
        } elseif ($nom['modo'] === 'parcial') {
            [$sumP, $sumD, $sumN, $pagados, $pendientes, $detallePagados] = insertar_detalle_calculado(
                $db, $nominaId, $empleadosSem, 'Parcial', $fin, $nom['variacion_idx'], $nom['parcial_pagados']
            );
            if ($pendientes === 0) {
                $estatusCab = 'Pagada';
            }
        } else {
            [$sumP, $sumD, $sumN, $pagados, $pendientes, $detallePagados] = insertar_detalle_calculado(
                $db, $nominaId, $empleadosSem, $estatusCab, $fin, $nom['variacion_idx'], 0
            );
        }

        if ($nom['modo'] !== 'borrador') {
            $db->query(sprintf(
                "UPDATE nominas SET total_percepciones=%.2f, total_deducciones=%.2f, total_neto=%.2f, estatus='%s' WHERE id=%d",
                $sumP, $sumD, $sumN, $db->real_escape_string($estatusCab), $nominaId
            ));
        }

        if (!empty($nom['pagos_log']) && !empty($detallePagados)) {
            $pagosLogTotal += insertar_pagos_log($db, $nominaId, $detallePagados, $fin);
        }

        $creadas[] = [
            'folio'      => $nom['folio'],
            'periodo'    => $nom['periodo_inicio'] . ' → ' . $nom['periodo_fin'],
            'estatus'    => $estatusCab,
            'neto'       => round($sumN, 2),
            'empleados'  => count($empleadosSem),
            'pagados'    => $pagados,
            'pendientes' => $pendientes,
        ];
    }

    // Quincenal opcional (NOM000027)
    $empleadosQ = cargar_empleados_quincenal($db);
    if (!empty($empleadosQ)) {
        $folioQ = 'NOM000027';
        $iniQ = '2026-07-01';
        $finQ = '2026-07-15';
        $obsQ = OBS_PREFIX . 'quincenal jul 1–15 (diversidad tipo)';
        $fechaCreacionQ = $finQ . ' 10:00:00';
        $estatusQ = 'Calculada';
        $tipoQ = 'Quincenal';

        $stmtNom->bind_param('ssssssss', $folioQ, $iniQ, $finQ, $tipoQ, $finQ, $estatusQ, $obsQ, $fechaCreacionQ);
        if (!$stmtNom->execute()) {
            throw new RuntimeException('Error insert quincenal: ' . $stmtNom->error);
        }
        $nominaIdQ = (int)$db->insert_id;

        $sumP = $sumD = $sumN = 0.0;
        foreach ($empleadosQ as $empId => $e) {
            $diario = (float)$e['salario_base_diario'];
            $lugar = $db->real_escape_string($e['lugar_pago'] ?: 'OFICINA');
            $dias = 15;
            $infonavit = (!empty($e['tiene_infonavit']) && (float)$e['descuento_infonavit'] > 0)
                ? (float)$e['descuento_infonavit'] : 0.0;
            $sueldoBase = round($diario * $dias, 2);
            $percepciones = $sueldoBase;
            $deducciones = round($infonavit, 2);
            $neto = round($percepciones - $deducciones, 2);

            $sql = sprintf(
                "INSERT INTO nominas_detalle (
                    nomina_id, empleado_id, lugar_origen, dias_trabajados,
                    sueldo_base, sueldo_diario, percepciones, deducciones, infonavit_descuento,
                    neto, monto_pagado, estatus
                ) VALUES (%d, %d, '%s', %.2f, %.2f, %.2f, %.2f, %.2f, %.2f, %.2f, 0, 'Pendiente')",
                $nominaIdQ, $empId, $lugar, $dias, $sueldoBase, $diario,
                $percepciones, $deducciones, $infonavit, $neto
            );
            if (!$db->query($sql)) {
                throw new RuntimeException('Error detalle quincenal: ' . $db->error);
            }
            $sumP += $percepciones;
            $sumD += $deducciones;
            $sumN += $neto;
        }
        $db->query(sprintf(
            "UPDATE nominas SET total_percepciones=%.2f, total_deducciones=%.2f, total_neto=%.2f WHERE id=%d",
            $sumP, $sumD, $sumN, $nominaIdQ
        ));
        $creadas[] = [
            'folio'      => $folioQ,
            'periodo'    => "$iniQ → $finQ",
            'estatus'    => 'Calculada',
            'neto'       => round($sumN, 2),
            'empleados'  => count($empleadosQ),
            'pagados'    => 0,
            'pendientes' => count($empleadosQ),
        ];
    }

    $stmtNom->close();

    return [
        'created'         => count($creadas),
        'nominas'         => $creadas,
        'pagos_log_rows'  => $pagosLogTotal,
        'empleados_semanal' => count($empleadosSem),
    ];
}

function verificar_integridad(mysqli $db, array $conteosAntes): array
{
    $despues = conteos_protegidos($db);
    $checks = [];
    foreach (['empleados', 'usuarios', 'empleados_cuentas_bancarias'] as $t) {
        $checks["{$t}_sin_cambio"] = ($conteosAntes[$t] === $despues[$t]);
    }
    if ($conteosAntes['nomina_configuracion'] !== null) {
        $checks['nomina_configuracion_sin_cambio'] = ($conteosAntes['nomina_configuracion'] === $despues['nomina_configuracion']);
    }

    $r = $db->query("
        SELECT COUNT(*) AS c FROM nominas_detalle nd
        LEFT JOIN nominas n ON n.id = nd.nomina_id WHERE n.id IS NULL
    ");
    $checks['detalle_huerfano'] = $r ? (int)$r->fetch_assoc()['c'] : -1;

    if ($db->query("SHOW TABLES LIKE 'nominas_conceptos'")->num_rows) {
        $r = $db->query("
            SELECT COUNT(*) AS c FROM nominas_conceptos nc
            LEFT JOIN nominas_detalle nd ON nd.id = nc.nomina_detalle_id
            WHERE nd.id IS NULL
        ");
        $checks['conceptos_huerfanos'] = $r ? (int)$r->fetch_assoc()['c'] : -1;
    }

    if ($db->query("SHOW TABLES LIKE 'nominas_pagos_log'")->num_rows) {
        $r = $db->query("
            SELECT COUNT(*) AS c FROM nominas_pagos_log pl
            LEFT JOIN nominas n ON n.id = pl.nomina_id WHERE n.id IS NULL
        ");
        $checks['pagos_log_huerfanos'] = $r ? (int)$r->fetch_assoc()['c'] : -1;
    }

    return $checks;
}

// --- Ejecución ---
$conteosAntes = conteos_protegidos($mysqli);
$out = ['action' => $action, 'conteos_antes' => $conteosAntes];

try {
    if ($action === 'revert') {
        $out['reset'] = reset_nominas($mysqli);
        $out['conteos_despues'] = conteos_nomina($mysqli);
        $out['verificacion'] = verificar_integridad($mysqli, $conteosAntes);
    } else {
        $out['reset'] = reset_nominas($mysqli);
        $out['seed'] = seed_nominas($mysqli);
        $out['conteos_despues'] = conteos_nomina($mysqli);
        $out['verificacion'] = verificar_integridad($mysqli, $conteosAntes);
    }
} catch (Throwable $e) {
    $out['success'] = false;
    $out['error'] = $e->getMessage();
    emitir($out, $isCli, 1);
}

$out['success'] = true;
$failedChecks = array_filter($out['verificacion'], static fn($v, $k) => $v !== true && $v !== 0, ARRAY_FILTER_USE_BOTH);
if (!empty($failedChecks)) {
    $out['success'] = false;
    $out['checks_fallidos'] = $failedChecks;
}

emitir($out, $isCli, $out['success'] ? 0 : 1);

function emitir(array $out, bool $isCli, int $code): void
{
    if ($isCli) {
        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    exit($code);
}
