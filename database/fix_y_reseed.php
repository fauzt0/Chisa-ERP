<?php
/**
 * Reset + Re-seed con SOLO datos reales del entrenamiento1.jpg
 *
 * PASO 1: Pone en ceros TODOS los salarios de empleados
 * PASO 2: Asigna datos reales SOLO a los 11 empleados de entrenamiento1.jpg
 * PASO 3: Reejecuta el seeder
 * PASO 4: Verifica
 *
 * CLI: php database/fix_y_reseed.php
 */

$cfgFile = __DIR__ . '/../application/config/database.php';
if (!file_exists($cfgFile)) {
    exit("No se encontró database.php\n");
}
$content = file_get_contents($cfgFile);
preg_match("/'hostname'\s*=>\s*'([^']+)'/", $content, $host);
preg_match("/'username'\s*=>\s*'([^']+)'/", $content, $user);
preg_match("/'password'\s*=>\s*'([^']+)'/", $content, $pass);
preg_match("/'database'\s*=>\s*'([^']+)'/", $content, $dbname);

$mysqli = @new mysqli($host[1], $user[1], $pass[1], $dbname[1]);
if ($mysqli->connect_error) {
    exit('Error de conexión: ' . $mysqli->connect_error . "\n");
}
$mysqli->set_charset('utf8mb4');

// ====================================================================
// PASO 1: Zero ALL employee salaries
// ====================================================================
echo "=== PASO 1: Poniendo en ceros TODOS los salarios ===\n";

$mysqli->query("UPDATE empleados SET
    salario_base_mensual = 0.00,
    salario_base_diario  = 0.00,
    tiene_infonavit = 0,
    descuento_infonavit = 0.00,
    costo_hora_extra = 0.00,
    tipo_nomina = 'Semanal',
    lugar_pago = 'OFICINA',
    fecha_edicion = CURDATE()
");

echo "  ✅ " . $mysqli->affected_rows . " empleados puestos en cero\n";

// ====================================================================
// PASO 2: Data real SOLO para los 11 empleados de entrenamiento1.jpg
// ====================================================================
echo "\n=== PASO 2: Asignando datos reales SOLO de entrenamiento1.jpg ===\n";

// Datos exactos de entrenamiento1.jpg (semana 06-12 jul 2026)
$reales = [
    5  => ['nombre' => 'ESAHU ENRIQUE SANCHEZ MARTINEZ',  'diario' => 1849.15, 'lugar' => 'OFICINA',         'infonavit' => 0,     'costo_he' => 0],
    16 => ['nombre' => 'OSCAR GALINDO PEREZ',              'diario' => 866.47,  'lugar' => 'OFICINA',         'infonavit' => 0,     'costo_he' => 0],
    9  => ['nombre' => 'MAURO AVILA LEAL',                 'diario' => 1055.05, 'lugar' => 'OFICINA',         'infonavit' => 240.54,'costo_he' => 0],
    11 => ['nombre' => 'CAROLINA GALVAN FRANCO',           'diario' => 584.28,  'lugar' => 'OFICINA',         'infonavit' => 307.87,'costo_he' => 0],
    7  => ['nombre' => 'JORGE SANCHEZ CHIMAL',             'diario' => 1210.39, 'lugar' => 'OFICINA',         'infonavit' => 0,     'costo_he' => 0],
    15 => ['nombre' => 'MIGUEL SANCHEZ COLIN',             'diario' => 546.65,  'lugar' => 'OFICINA',         'infonavit' => 0,     'costo_he' => 136.66],
    17 => ['nombre' => 'ILIANA QUEZADA MARTINEZ',          'diario' => 315.04,  'lugar' => 'OFICINA',         'infonavit' => 0,     'costo_he' => 0],
    10 => ['nombre' => 'MIGUEL IVAN SANCHEZ MARTINEZ',     'diario' => 1055.50, 'lugar' => 'OFICINA',         'infonavit' => 0,     'costo_he' => 0],
    12 => ['nombre' => 'FRANCISCO MARTINEZ HERNANDEZ',     'diario' => 772.40,  'lugar' => 'LAGUNAS OAXACA',  'infonavit' => 0,     'costo_he' => 0],
    8  => ['nombre' => 'MARCELO LUGO GARCIA',              'diario' => 763.00,  'lugar' => 'TUXTLA',          'infonavit' => 1584.36,'costo_he' => 0],
    6  => ['nombre' => 'TEODORO JIMENEZ RAMIREZ',          'diario' => 634.56,  'lugar' => 'PRODUCCION',      'infonavit' => 1145.52,'costo_he' => 0],
];

foreach ($reales as $id => $d) {
    $mensual = round($d['diario'] * 30, 2);
    $tiene = $d['infonavit'] > 0 ? 1 : 0;
    $sql = "UPDATE empleados SET
        salario_base_diario = {$d['diario']},
        salario_base_mensual = $mensual,
        lugar_pago = '{$d['lugar']}',
        tipo_nomina = 'Semanal',
        tiene_infonavit = $tiene,
        descuento_infonavit = {$d['infonavit']},
        costo_hora_extra = {$d['costo_he']},
        fecha_edicion = CURDATE()
    WHERE id = $id";

    if ($mysqli->query($sql)) {
        $inf = $d['infonavit'] > 0 ? " INFONAVIT=\${$d['infonavit']}" : "";
        echo "  ✅ ID $id ({$d['nombre']}): diario=\${$d['diario']}, lugar={$d['lugar']}$inf\n";
    } else {
        echo "  ❌ Error ID $id: " . $mysqli->error . "\n";
    }
}

// ====================================================================
// PASO 2b: Verificar el estado final
// ====================================================================
echo "\n=== Verificación de empleados ===\n";
echo sprintf("%-4s %-40s %10s %10s %-16s %s\n", "ID", "Nombre", "Diario", "Mensual", "Lugar", "INFONAVIT");
echo str_repeat("-", 100) . "\n";

$res = $mysqli->query("
    SELECT id, CONCAT(nombre,' ',IFNULL(apellido_paterno,''),' ',IFNULL(apellido_materno,'')) AS nom,
           salario_base_diario, salario_base_mensual, lugar_pago, descuento_infonavit, estatus
    FROM empleados ORDER BY id
");
$enImagen = 0;
$fueraImagen = 0;
while ($row = $res->fetch_assoc()) {
    $diario = (float)$row['salario_base_diario'];
    $enLista = isset($reales[(int)$row['id']]);
    $icono = $enLista ? '📋' : ($diario == 0 ? '  ' : '⚠️');
    printf("%s %-4d %-40s %10.2f %10.2f %-16s %s\n",
        $icono,
        $row['id'],
        mb_substr($row['nom'], 0, 40),
        $diario,
        (float)$row['salario_base_mensual'],
        $row['lugar_pago'],
        $enLista ? ('$' . number_format((float)$row['descuento_infonavit'], 2)) : ($diario == 0 ? '$0.00' : '⚠️ REVISAR')
    );
    if ($enLista) $enImagen++; else $fueraImagen++;
}
$res->free();
echo "\n📋 En la imagen (con datos reales): $enImagen empleados\n";
echo "   Fuera de la imagen (salario $0):  $fueraImagen empleados\n";

$mysqli->close();

// ====================================================================
// PASO 3: Ejecutar seeder
// ====================================================================
echo "\n=== PASO 3: Ejecutando seeder (run_seed_nominas_reset_demo.php) ===\n";
echo "Esto puede tomar unos segundos...\n\n";

$seederCmd = '/usr/local/php82/bin/php ' . __DIR__ . '/run_seed_nominas_reset_demo.php apply 2>&1';
$output = [];
$exitCode = 0;
exec($seederCmd, $output, $exitCode);

echo implode("\n", $output) . "\n";

if ($exitCode !== 0) {
    echo "\n⚠️  Seeder terminó con código $exitCode\n";
}

// ====================================================================
// PASO 4: Verificar nóminas Calculadas y Borrador
// ====================================================================
echo "\n=== PASO 4: Verificando nóminas ===\n";

$mysqli2 = @new mysqli($host[1], $user[1], $pass[1], $dbname[1]);
$mysqli2->set_charset('utf8mb4');
if (!$mysqli2->connect_error) {
    // Todas las nóminas recientes
    $res2 = $mysqli2->query("
        SELECT n.id, n.folio, n.estatus, n.total_neto, n.total_percepciones, n.total_deducciones,
               n.periodo_inicio, n.periodo_fin,
               COUNT(nd.id) AS num_emp
        FROM nominas n
        LEFT JOIN nominas_detalle nd ON nd.nomina_id = n.id
        WHERE n.folio IN ('NOM000021','NOM000022','NOM000023','NOM000024','NOM000025','NOM000026','NOM000120')
        GROUP BY n.id
        ORDER BY n.periodo_inicio
    ");
    if ($res2) {
        printf("%-4s %-12s %-25s %-10s %12s %6s\n", "ID", "Folio", "Periodo", "Estatus", "Neto", "Emp");
        echo str_repeat("-", 75) . "\n";
        while ($row = $res2->fetch_assoc()) {
            $icon = ((float)$row['total_neto'] > 0) ? '✅' : ($row['estatus'] === 'Borrador' ? '⬜' : '❌');
            printf("%s %-4d %-12s %s→%s %-10s %12.2f %6d\n",
                $icon,
                $row['id'],
                $row['folio'],
                substr($row['periodo_inicio'], 5),
                substr($row['periodo_fin'], 5),
                $row['estatus'],
                (float)$row['total_neto'],
                $row['num_emp']
            );
        }
        $res2->free();
    }

    // Stats
    $res3 = $mysqli2->query("SELECT estatus, COUNT(*) AS c, SUM(total_neto) AS neto FROM nominas GROUP BY estatus ORDER BY estatus");
    echo "\n=== Resumen por estatus ===\n";
    if ($res3) {
        while ($row = $res3->fetch_assoc()) {
            printf("  %-12s: %2d nóminas | Neto total: \$%s\n",
                $row['estatus'], $row['c'],
                number_format((float)$row['neto'], 2)
            );
        }
        $res3->free();
    }

    $mysqli2->close();
}

echo "\n=== COMPLETADO ===\n";
echo "📋 " . $enImagen . " empleados con datos reales (entrenamiento1.jpg)\n";
echo "   " . $fueraImagen . " empleados con salario \$0.00 (no aparecen en la imagen)\n";
