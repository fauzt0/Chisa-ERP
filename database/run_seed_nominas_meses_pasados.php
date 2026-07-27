<?php
/**
 * Seed de nóminas de meses anteriores (abril–junio 2026) para pruebas.
 *
 * Genera semanas Semanal con:
 *  - Pagada (mayoría)
 *  - Calculada (pendiente de pago)
 *  - Parcial (algunos empleados pagados)
 *  - Borrador
 *
 * CLI:  php run_seed_nominas_meses_pasados.php apply
 *       php run_seed_nominas_meses_pasados.php revert
 * Web:  /database/run_seed_nominas_meses_pasados.php?action=apply&key=chisa_demo_seed
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
    $msg = "Uso: php run_seed_nominas_meses_pasados.php apply|revert\n";
    exit($isCli ? $msg : nl2br($msg));
}

require dirname(__DIR__) . '/application/config/database.php';
$cfg = $db['default'];

$mysqli = @new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($mysqli->connect_error) {
    exit('Error de conexión: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

/** Folios reservados para este seed: NOM000100–NOM000199 */
const FOLIO_MIN = 100;
const FOLIO_MAX = 199;

$empleadoIds = [5, 6, 7, 8, 9, 10, 11, 12, 15, 16, 17];

function folio_seed($n) {
    return 'NOM' . str_pad((string)$n, 6, '0', STR_PAD_LEFT);
}

function revert_seed(mysqli $db) {
    $folios = [];
    for ($i = FOLIO_MIN; $i <= FOLIO_MAX; $i++) {
        $folios[] = "'" . folio_seed($i) . "'";
    }
    $in = implode(',', $folios);
    $db->query("DELETE nd FROM nominas_detalle nd INNER JOIN nominas n ON n.id = nd.nomina_id WHERE n.folio IN ($in)");
    $db->query("DELETE FROM nominas WHERE folio IN ($in)");
    return $db->affected_rows;
}

if ($action === 'revert') {
    $deleted = revert_seed($mysqli);
    $out = ['action' => 'revert', 'deleted_nominas_approx' => $deleted, 'folios' => 'NOM000100–NOM000199'];
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . ($isCli ? "\n" : '');
    exit(0);
}

// --- APPLY ---
revert_seed($mysqli);

// Cargar empleados con sueldos actuales
$ids = implode(',', array_map('intval', $empleadoIds));
$res = $mysqli->query("
    SELECT id, salario_base_diario, lugar_pago, costo_hora_extra,
           tiene_infonavit, descuento_infonavit
    FROM empleados WHERE id IN ($ids)
");
if (!$res || $res->num_rows === 0) {
    exit("No hay empleados con sueldos. Ejecuta primero empleados_sueldos_entrenamiento1.sql\n");
}
$empleados = [];
while ($row = $res->fetch_assoc()) {
    $empleados[(int)$row['id']] = $row;
}
$res->free();

/**
 * Semanas lunes–domingo a generar.
 * estatus_cabecera: Pagada | Calculada | Parcial | Borrador
 */
$semanas = [
    // FEBRERO 2026
    ['2026-02-02', '2026-02-08', 'Pagada',    'Seed histórico — feb sem 1'],
    ['2026-02-09', '2026-02-15', 'Pagada',    'Seed histórico — feb sem 2'],
    ['2026-02-16', '2026-02-22', 'Pagada',    'Seed histórico — feb sem 3'],
    ['2026-02-23', '2026-03-01', 'Pagada',    'Seed histórico — feb sem 4'],
    // MARZO 2026
    ['2026-03-02', '2026-03-08', 'Pagada',    'Seed histórico — mar sem 1'],
    ['2026-03-09', '2026-03-15', 'Pagada',    'Seed histórico — mar sem 2'],
    ['2026-03-16', '2026-03-22', 'Pagada',    'Seed histórico — mar sem 3'],
    ['2026-03-23', '2026-03-29', 'Calculada', 'Seed histórico — mar sem 4 (pendiente pago)'],
    // ABRIL 2026
    ['2026-03-30', '2026-04-05', 'Pagada',    'Seed histórico — abr sem 1'],
    ['2026-04-06', '2026-04-12', 'Pagada',    'Seed histórico — abr sem 2'],
    ['2026-04-13', '2026-04-19', 'Pagada',    'Seed histórico — abr sem 3'],
    ['2026-04-20', '2026-04-26', 'Pagada',    'Seed histórico — abr sem 4'],
    ['2026-04-27', '2026-05-03', 'Parcial',   'Seed histórico — abr/may (pago parcial)'],
    // MAYO 2026
    ['2026-05-04', '2026-05-10', 'Pagada',    'Seed histórico — may sem 1'],
    ['2026-05-11', '2026-05-17', 'Pagada',    'Seed histórico — may sem 2'],
    ['2026-05-18', '2026-05-24', 'Pagada',    'Seed histórico — may sem 3'],
    ['2026-05-25', '2026-05-31', 'Calculada', 'Seed histórico — may sem 4 (pendiente pago)'],
    // JUNIO 2026
    ['2026-06-01', '2026-06-07', 'Pagada',    'Seed histórico — jun sem 1'],
    ['2026-06-08', '2026-06-14', 'Pagada',    'Seed histórico — jun sem 2'],
    ['2026-06-15', '2026-06-21', 'Pagada',    'Seed histórico — jun sem 3'],
    ['2026-06-22', '2026-06-28', 'Calculada', 'Seed histórico — jun sem 4 (pendiente pago)'],
];

/**
 * Variaciones ficticias por semana (se ciclan si hay más semanas que filas).
 * [dias, comidas Mauro/Francisco/Marcelo, bono Teodoro, HE Miguel (horas)]
 */
$variacionesBase = [
    [7, 1700, 2000, 0],
    [7, 1700,    0, 0],
    [7,    0, 1500, 5],
    [6, 1700, 2000, 0],
    [7, 1700, 1000, 3],
    [7, 1700, 2000, 0],
    [7,    0,    0, 8],
    [7, 1700, 2500, 0],
    [7, 1700, 2000, 0],
    [7, 1700,    0, 4],
    [6, 1700, 2000, 0],
    [7, 1700, 1800, 6],
    [7, 1700, 2000, 0],
];
$variaciones = [];
for ($i = 0; $i < count($semanas); $i++) {
    $variaciones[] = $variacionesBase[$i % count($variacionesBase)];
}

$creadas = [];
$folioNum = FOLIO_MIN;
$stmtNom = $mysqli->prepare("
    INSERT INTO nominas (
        folio, periodo_inicio, periodo_fin, tipo_nomina, fecha_pago,
        total_percepciones, total_deducciones, total_neto,
        estatus, observaciones, usuario_creacion, fecha_creacion
    ) VALUES (?, ?, ?, 'Semanal', ?, 0, 0, 0, ?, ?, 1, ?)
");

foreach ($semanas as $idx => $sem) {
    [$inicio, $fin, $estatusCab, $obs] = $sem;
    [$dias, $comidasBase, $bonoTeodoro, $horasMiguel] = $variaciones[$idx];

    $folio = folio_seed($folioNum++);
    $fechaCreacion = $fin . ' 17:30:00';
    $fechaPagoCab  = $fin;

    $stmtNom->bind_param('sssssss', $folio, $inicio, $fin, $fechaPagoCab, $estatusCab, $obs, $fechaCreacion);
    if (!$stmtNom->execute()) {
        exit("Error insert nominas $folio: " . $stmtNom->error . "\n");
    }
    $nominaId = (int)$mysqli->insert_id;

    $empIndex = 0;
    $sumP = $sumD = $sumN = 0;
    $pagados = $pendientes = 0;

    foreach ($empleados as $empId => $e) {
        $empIndex++;
        $diario = (float)$e['salario_base_diario'];
        $lugar  = $mysqli->real_escape_string($e['lugar_pago'] ?: 'OFICINA');
        $costoHe = (float)$e['costo_hora_extra'];
        $infonavit = (!empty($e['tiene_infonavit']) && (float)$e['descuento_infonavit'] > 0)
            ? (float)$e['descuento_infonavit'] : 0.0;

        if ($estatusCab === 'Borrador') {
            $sql = "INSERT INTO nominas_detalle (nomina_id, empleado_id, lugar_origen, estatus)
                    VALUES ($nominaId, $empId, '$lugar', 'Pendiente')";
            $mysqli->query($sql);
            $pendientes++;
            continue;
        }

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
        $otrosDesc = ($empId === 5 && $idx % 3 === 0) ? 235.53 : 0.0;
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
            if ($empIndex <= 6) {
                $estDet = 'Pagado';
                $montoPagado = $neto;
                $fechaPagoSql = "'" . $fin . " 18:00:00'";
                $pagados++;
            } else {
                $pendientes++;
            }
        } else {
            // Calculada
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
        if (!$mysqli->query($sql)) {
            exit("Error detalle $folio emp $empId: " . $mysqli->error . "\n");
        }

        $sumP += $percepciones;
        $sumD += $deducciones;
        $sumN += $neto;
    }

    $mysqli->query(sprintf(
        "UPDATE nominas SET total_percepciones=%.2f, total_deducciones=%.2f, total_neto=%.2f WHERE id=%d",
        $sumP, $sumD, $sumN, $nominaId
    ));

    // Si Parcial y todos pendientes o todos pagados, ajustar cabecera
    if ($estatusCab === 'Parcial' && $pendientes === 0) {
        $mysqli->query("UPDATE nominas SET estatus='Pagada' WHERE id=$nominaId");
        $estatusCab = 'Pagada';
    }

    $creadas[] = [
        'folio'     => $folio,
        'periodo'   => "$inicio → $fin",
        'estatus'   => $estatusCab,
        'neto'      => round($sumN, 2),
        'empleados' => count($empleados),
        'pagados'   => $pagados,
        'pendientes'=> $pendientes,
    ];
}

$stmtNom->close();

$out = [
    'action'   => 'apply',
    'created'  => count($creadas),
    'nominas'  => $creadas,
];

if ($isCli) {
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
exit(0);
