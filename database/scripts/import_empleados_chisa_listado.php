<?php
/**
 * Importación única — listado trabajadores Chisa (oficina central)
 * Ejecutar: php database/scripts/import_empleados_chisa_listado.php
 * Opciones: --dry-run (solo muestra, no inserta)
 */
declare(strict_types=1);

$dryRun = in_array('--dry-run', $argv ?? [], true);

$db = [
    'host' => 'localhost',
    'user' => 'st32477_chisa',
    'pass' => 'hADJXjtLjYp4ykTtRzEG',
    'name' => 'st32477_chisa',
];

$rows = [
    ['cla' => 1,  'nombre' => 'SANCHEZ MARTINEZ ESAHU ENRIQUE',     'puesto' => 'SUPERINTENDENTE', 'depto' => 'OFICINA CENTRAL',    'estatus' => 'Alta',      'rfc' => 'SAME870409RA6', 'nss' => '45058738159', 'cp' => '',     'curp' => 'SAME870409HDFNRS07', 'cp_fiscal' => '08220'],
    ['cla' => 3,  'nombre' => 'JIMENEZ RAMIREZ TEODORO',            'puesto' => 'OFICIAL',         'depto' => 'OFICINA CENTRAL',    'estatus' => 'Alta',      'rfc' => 'JIRT881122QU5', 'nss' => '4508819130',  'cp' => '',     'curp' => 'JIRT881122HMCMMD08', 'cp_fiscal' => '08220'],
    ['cla' => 4,  'nombre' => 'SANCHEZ CHIMAL JORGE',               'puesto' => 'CHOFER',          'depto' => 'OFICINA CENTRAL',    'estatus' => 'Reingreso', 'rfc' => 'SACJ7902091PA', 'nss' => '42957901830', 'cp' => '',     'curp' => 'SACJ790209HMCBHR04', 'cp_fiscal' => '08220'],
    ['cla' => 8,  'nombre' => 'LUGO GARCIA MARCELO',                'puesto' => 'OFICIAL',         'depto' => 'OFICINA CENTRAL',    'estatus' => 'Reingreso', 'rfc' => 'LUGM690116N80', 'nss' => '42946805688', 'cp' => '',     'curp' => 'LUGM690116HMCGRR05', 'cp_fiscal' => '50500'],
    ['cla' => 13, 'nombre' => 'AVILA LEAL MAURO',                   'puesto' => 'OFICIAL',         'depto' => 'OFICINA CENTRAL',    'estatus' => 'Alta',      'rfc' => 'AILM8101153M7', 'nss' => '88978100845', 'cp' => '',     'curp' => 'AILM810115HMCVLR07', 'cp_fiscal' => '03660'],
    ['cla' => 11, 'nombre' => 'SANCHEZ MARTINEZ MIGUEL IVAN',       'puesto' => 'AUXILIAR',        'depto' => 'OFICINA CENTRAL',    'estatus' => 'Alta',      'rfc' => 'SAMM9211125F6', 'nss' => '03179205707', 'cp' => '50100', 'curp' => 'SAMM921112HDFNRG01', 'cp_fiscal' => '50100'],
    ['cla' => 14, 'nombre' => 'GALVAN FRANCO CAROLINA',             'puesto' => 'SECRETARIA',      'depto' => 'OFICINA CENTRAL',    'estatus' => 'Reingreso', 'rfc' => 'GAFC7509239Q7', 'nss' => '28957505077', 'cp' => '55120', 'curp' => 'GAFC750923MDFLRR05', 'cp_fiscal' => '55120'],
    ['cla' => 16, 'nombre' => 'MARTINEZ HERNANDEZ FRANCISCO',       'puesto' => 'OFICIAL',         'depto' => 'OFICINA CENTRAL',    'estatus' => 'Alta',      'rfc' => 'MAHF781006GK1', 'nss' => '45047805036', 'cp' => '50780', 'curp' => 'MAHF781006HMCRRR01', 'cp_fiscal' => '08220'],
    ['cla' => 16, 'nombre' => 'ALMAZAN FELIPE GERARDO',             'puesto' => 'OFICIAL',         'depto' => 'OFICINA CENTRAL',    'estatus' => 'Reingreso', 'rfc' => 'AAFG880507FF3', 'nss' => '4508821664',  'cp' => '50783', 'curp' => 'AAFG880507HMCLLR01', 'cp_fiscal' => '08220'],
    ['cla' => 22, 'nombre' => 'NEVAREZ MARTINEZ RIGO ANTONIO',      'puesto' => 'OFICIAL',         'depto' => 'OFICINA CENTRAL',    'estatus' => 'Alta',      'rfc' => 'NEMR7808202T3', 'nss' => '42967800840', 'cp' => '',     'curp' => 'NEMR780820HDGVRG03', 'cp_fiscal' => '29045'],
    ['cla' => 22, 'nombre' => 'SANCHEZ COLIN MIGUEL',               'puesto' => 'CHOFER',          'depto' => 'OFICINA CENTRAL',    'estatus' => 'Alta',      'rfc' => 'SACM780827AAA', 'nss' => '92007802017', 'cp' => '',     'curp' => 'SACM780827HMCNLG04', 'cp_fiscal' => '54010'],
    ['cla' => 22, 'nombre' => 'GALINDO PEREZ OSCAR',                'puesto' => 'ADMINISTRATIVO',  'depto' => 'OFICINA CENTRAL',    'estatus' => 'Alta',      'rfc' => 'GAPO790101AT1', 'nss' => '68937904560', 'cp' => '',     'curp' => 'GAPO790101HDFLRS05', 'cp_fiscal' => '09900'],
    ['cla' => 22, 'nombre' => 'QUEZADA MARTINEZ ILIANA',           'puesto' => 'LIMPIEZA',        'depto' => 'OFICINA CENTRAL',    'estatus' => 'Alta',      'rfc' => 'QUMI830806BN5', 'nss' => '20058310598', 'cp' => '',     'curp' => 'QUMI830806MDFZRL00', 'cp_fiscal' => '57130'],
];

function parseNombre(string $full): array
{
    $parts = preg_split('/\s+/', trim($full)) ?: [];
    $n = count($parts);
    if ($n <= 2) {
        return [
            'apellido_paterno' => $parts[0] ?? 'SIN',
            'apellido_materno' => $parts[1] ?? '',
            'nombre' => $parts[1] ?? 'SIN NOMBRE',
        ];
    }
    if ($n === 3) {
        return [
            'apellido_paterno' => $parts[0],
            'apellido_materno' => $parts[1],
            'nombre' => $parts[2],
        ];
    }
    return [
        'apellido_paterno' => $parts[0],
        'apellido_materno' => $parts[1],
        'nombre' => implode(' ', array_slice($parts, 2)),
    ];
}

function fechaDesdeCurp(string $curp): string
{
    $curp = strtoupper(trim($curp));
    if (strlen($curp) < 10) {
        return '1990-01-01';
    }
    $yy = (int) substr($curp, 4, 2);
    $mm = substr($curp, 6, 2);
    $dd = substr($curp, 8, 2);
    $year = $yy <= (int) date('y') ? 2000 + $yy : 1900 + $yy;
    return sprintf('%04d-%s-%s', $year, $mm, $dd);
}

function generoDesdeCurp(string $curp): string
{
    $curp = strtoupper(trim($curp));
    if (strlen($curp) < 11) {
        return 'Otro';
    }
    $g = $curp[10];
    if ($g === 'H') return 'M';
    if ($g === 'M') return 'F';
    return 'Otro';
}

function normalizarNss(string $nss): ?string
{
    $digits = preg_replace('/\D/', '', $nss);
    if ($digits === '') return null;
    if (strlen($digits) > 11) $digits = substr($digits, 0, 11);
    return str_pad($digits, 11, '0', STR_PAD_LEFT);
}

function tituloCase(string $s): string
{
    return mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
}

function siguienteNumeroEmpleado(mysqli $mysqli, int $year): string
{
    $pref = "EMP-$year-";
    $res = $mysqli->query("SELECT numero_empleado FROM empleados WHERE numero_empleado LIKE '{$pref}%' ORDER BY id DESC LIMIT 1");
    $num = 1;
    if ($res && ($row = $res->fetch_assoc())) {
        $num = (int) substr($row['numero_empleado'], -4) + 1;
    }
    return sprintf('EMP-%d-%04d', $year, $num);
}

$mysqli = new mysqli($db['host'], $db['user'], $db['pass'], $db['name']);
if ($mysqli->connect_error) {
    fwrite(STDERR, "Error DB: {$mysqli->connect_error}\n");
    exit(1);
}
$mysqli->set_charset('utf8mb4');

// Departamento Oficina Central
$deptoNombre = 'Oficina Central';
$res = $mysqli->query("SELECT id FROM departamentos WHERE nombre = '" . $mysqli->real_escape_string($deptoNombre) . "' LIMIT 1");
$deptoId = ($res && ($r = $res->fetch_assoc())) ? (int) $r['id'] : 0;
if (!$deptoId && !$dryRun) {
    $mysqli->query("INSERT INTO departamentos (nombre, descripcion, estatus, fecha_alta) VALUES ('Oficina Central', 'Oficina central / operaciones', 1, CURDATE())");
    $deptoId = (int) $mysqli->insert_id;
    echo "Departamento creado: Oficina Central (id $deptoId)\n";
} elseif ($deptoId) {
    echo "Departamento existente: Oficina Central (id $deptoId)\n";
}

$hoy = date('Y-m-d');
$salarioMensual = 9000.00;
$salarioDiario = round($salarioMensual / 30, 2);
$relojPin = 2;
$importados = 0;
$omitidos = 0;
$jefeId = null;

foreach ($rows as $i => $row) {
    $rfc = strtoupper(trim($row['rfc']));
    $curp = strtoupper(trim($row['curp']));

    $chk = $mysqli->query("SELECT id, numero_empleado FROM empleados WHERE rfc = '{$mysqli->real_escape_string($rfc)}' OR curp = '{$mysqli->real_escape_string($curp)}' LIMIT 1");
    if ($chk && ($ex = $chk->fetch_assoc())) {
        echo "OMITIDO (ya existe): {$row['nombre']} — {$ex['numero_empleado']}\n";
        $omitidos++;
        if ($row['cla'] === 1 && !$jefeId) {
            $jefeId = (int) $ex['id'];
        }
        continue;
    }

    $nom = parseNombre($row['nombre']);
    $estatus = (strcasecmp($row['estatus'], 'Reingreso') === 0) ? 2 : 1;
    $cp = $row['cp'] !== '' ? $row['cp'] : null;
    $cpFiscal = $row['cp_fiscal'] !== '' ? $row['cp_fiscal'] : null;
    $nss = normalizarNss($row['nss']);
    $numero = siguienteNumeroEmpleado($mysqli, (int) date('Y'));
    $relojNombre = mb_substr(trim($nom['nombre'] . ' ' . $nom['apellido_paterno']), 0, 24);

    $tipoTrabajador = 'Planta';
    $tipoNomina = 'Quincenal';
    $formaPago = 'Transferencia';
    $nacionalidad = 'Mexicana';
    $fechaNac = fechaDesdeCurp($curp);
    $genero = generoDesdeCurp($curp);
    $nombre = tituloCase($nom['nombre']);
    $apPat = tituloCase($nom['apellido_paterno']);
    $apMat = tituloCase($nom['apellido_materno']);
    $puesto = tituloCase($row['puesto']);
    $jefe = ($row['cla'] === 1) ? null : $jefeId;

    echo ($dryRun ? '[DRY] ' : '') . "IMPORT: {$numero} | {$nombre} {$apPat} | {$row['puesto']} | {$row['estatus']} | RFC {$rfc}\n";

    if ($dryRun) {
        $importados++;
        continue;
    }

    if ($dryRun) {
        $importados++;
        continue;
    }

    $esc = static function (?string $v) use ($mysqli): string {
        return $v === null ? 'NULL' : "'" . $mysqli->real_escape_string($v) . "'";
    };
    $jefeSql = $jefe ? (int) $jefe : 'NULL';
    $cpSql = $cp !== null && $cp !== '' ? $esc($cp) : 'NULL';
    $cpFiscalSql = $cpFiscal !== null && $cpFiscal !== '' ? $esc($cpFiscal) : 'NULL';
    $nssSql = $nss !== null ? $esc($nss) : 'NULL';

    $sql = "INSERT INTO empleados (
            numero_empleado, reloj_pin, reloj_nombre_meta,
            nombre, apellido_paterno, apellido_materno,
            fecha_nacimiento, genero, nacionalidad,
            codigo_postal, codigo_postal_fiscal, rfc, curp, nss,
            tipo_trabajador, departamento_id, puesto, jefe_directo_id,
            fecha_ingreso, salario_base_mensual, salario_base_diario,
            tipo_nomina, forma_pago, estatus, fecha_alta
        ) VALUES (
            {$esc($numero)}, {$relojPin}, {$esc($relojNombre)},
            {$esc($nombre)}, {$esc($apPat)}, {$esc($apMat)},
            {$esc($fechaNac)}, {$esc($genero)}, {$esc($nacionalidad)},
            {$cpSql}, {$cpFiscalSql}, {$esc($rfc)}, {$esc($curp)}, {$nssSql},
            {$esc($tipoTrabajador)}, {$deptoId}, {$esc($puesto)}, {$jefeSql},
            {$esc($hoy)}, {$salarioMensual}, {$salarioDiario},
            {$esc($tipoNomina)}, {$esc($formaPago)}, {$estatus}, {$esc($hoy)}
        )";

    if (!$mysqli->query($sql)) {
        fwrite(STDERR, "ERROR {$row['nombre']}: {$mysqli->error}\n");
        continue;
    }

    $newId = (int) $mysqli->insert_id;
    if ($row['cla'] === 1) {
        $jefeId = $newId;
    }

    // Contrato inicial básico
    $mysqli->query("
        INSERT INTO contratos_empleados (
            empleado_id, version, tipo_contrato, vigente, puesto, departamento,
            tipo_trabajador, salario_base_mensual, salario_base_diario, tipo_nomina,
            jornada_laboral, fecha_inicio, fecha_creacion, motivo_cambio
        ) VALUES (
            {$newId}, 1, 'Inicial', 1,
            '" . $mysqli->real_escape_string($puesto) . "',
            'Oficina Central',
            'Planta', {$salarioMensual}, {$salarioDiario}, 'Quincenal',
            'Tiempo Completo', '{$hoy}', NOW(), 'Alta importada desde listado Chisa'
        )
    ");

    $importados++;
    $relojPin++;
}

// Asignar jefe directo al superintendente a los demás si se importaron después
if (!$dryRun && $jefeId) {
    $mysqli->query("UPDATE empleados SET jefe_directo_id = {$jefeId} WHERE departamento_id = {$deptoId} AND id != {$jefeId} AND jefe_directo_id IS NULL");
}

echo "\n=== Resumen ===\n";
echo "Importados: {$importados}\n";
echo "Omitidos:   {$omitidos}\n";
echo "Salario placeholder: \${$salarioMensual} mensual — actualizar en RH si aplica.\n";
echo "Reloj PIN asignados secuencialmente desde 2 (revisar duplicados de Cla en listado original).\n";

$mysqli->close();
