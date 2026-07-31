<?php
/**
 * Fix B2 + Re-seed: Asigna salarios a empleados demo y reejecuta el seeder.
 *
 * CLI: php database/fix_y_reseed.php
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');

$cfgFile = __DIR__ . '/../application/config/database.php';
if (!file_exists($cfgFile)) {
    exit("No se encontró database.php\n");
}

// Extraer config sin incluir (evita BASEPATH check de CI)
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

echo "=== PASO 1: Corrigiendo salarios de empleados demo ===\n";

$fixes = [
    2  => ['Pedro Lopez Morales',          850.00, 'OFICINA',    1],
    3  => ['Ana Karina Roman Martinez',     720.00, 'OFICINA',    1],
    4  => ['Maria Pilar Gomez',             650.00, 'OFICINA',    1],
    13 => ['Gerardo Almazan Felipe',        900.00, 'PRODUCCION', null],
    14 => ['Rigo Antonio Nevarez Martinez', 780.00, 'PRODUCCION', null],
];

foreach ($fixes as $id => [$nombre, $diario, $lugar, $estatus]) {
    $mensual = round($diario * 30, 2);
    $sql = "UPDATE empleados SET 
        salario_base_diario = $diario,
        salario_base_mensual = $mensual,
        lugar_pago = '$lugar',
        tipo_nomina = 'Semanal',
        tiene_infonavit = 0,
        descuento_infonavit = 0.00,
        fecha_edicion = CURDATE()";
    if ($estatus !== null) {
        $sql .= ", estatus = $estatus";
    }
    $sql .= " WHERE id = $id";
    
    if ($mysqli->query($sql)) {
        echo "  ✅ ID $id ($nombre): diario=\$$diario, mensual=\$$mensual, lugar=$lugar" . ($estatus !== null ? ", activado" : "") . "\n";
    } else {
        echo "  ❌ Error ID $id: " . $mysqli->error . "\n";
    }
}

echo "\n=== PASO 2: Verificando empleados corregidos ===\n";
$res = $mysqli->query("
    SELECT id, CONCAT(nombre,' ',IFNULL(apellido_paterno,''),' ',IFNULL(apellido_materno,'')) AS nombre_completo,
           salario_base_diario, salario_base_mensual, estatus, tipo_nomina
    FROM empleados WHERE id IN (2,3,4,13,14) ORDER BY id
");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        printf("  ID %2d | %-45s | mensual=%10.2f | diario=%8.2f | estatus=%d | %s\n",
            $row['id'], $row['nombre_completo'],
            (float)$row['salario_base_mensual'], (float)$row['salario_base_diario'],
            $row['estatus'], $row['tipo_nomina']
        );
    }
    $res->free();
}

$mysqli->close();

echo "\n=== PASO 3: Ejecutando seeder (run_seed_nominas_reset_demo.php) ===\n";
echo "Ejecutando...\n\n";

// Run the seeder
$seederCmd = '/usr/local/php82/bin/php ' . __DIR__ . '/run_seed_nominas_reset_demo.php apply 2>&1';
$output = [];
$exitCode = 0;
exec($seederCmd, $output, $exitCode);

echo implode("\n", $output) . "\n";

if ($exitCode !== 0) {
    echo "\n⚠️  Seeder terminó con código $exitCode\n";
}

echo "\n=== PASO 4: Verificando nómina en Borrador ===\n";
$mysqli2 = @new mysqli($host[1], $user[1], $pass[1], $dbname[1]);
$mysqli2->set_charset('utf8mb4');
if (!$mysqli2->connect_error) {
    $res2 = $mysqli2->query("
        SELECT n.id, n.folio, n.estatus, n.total_neto, n.total_percepciones, n.total_deducciones,
               COUNT(nd.id) AS num_empleados
        FROM nominas n
        LEFT JOIN nominas_detalle nd ON nd.nomina_id = n.id
        WHERE n.estatus IN ('Borrador', 'Calculada')
        GROUP BY n.id
        ORDER BY n.id
    ");
    if ($res2) {
        $found = false;
        while ($row = $res2->fetch_assoc()) {
            $found = true;
            $icon = ((float)$row['total_neto'] > 0) ? '✅' : '❌';
            printf("  %s ID %d | %s | %s | neto=%.2f | emp=%d\n",
                $icon, $row['id'], $row['folio'], $row['estatus'],
                (float)$row['total_neto'], $row['num_empleados']
            );
        }
        if (!$found) {
            echo "  No se encontraron nóminas en Borrador/Calculada.\n";
        }
        $res2->free();
    }
    $mysqli2->close();
}

echo "\n=== COMPLETADO ===\n";
