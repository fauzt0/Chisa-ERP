<?php
define('BASEPATH', true);
require __DIR__ . '/../application/helpers/cotizacion_pdf_helper.php';

$dir = __DIR__ . '/../doc/entrenamiento_6/COTIZACIONES 2026 PDF';
$files = glob($dir . '/*.pdf');
if (!$files) {
    fwrite(STDERR, "sin PDFs\n");
    exit(1);
}
$ok = 0;
$empty = [];
foreach ($files as $path) {
    $texto = cotizacion_pdf_texto($path);
    $p = cotizacion_pdf_parsear($texto, basename($path));
    if (count($p['lineas']) > 0) {
        $ok++;
    } else {
        $empty[] = basename($path);
    }
}
echo json_encode([
    'archivos' => count($files),
    'con_partidas' => $ok,
    'sin_partidas' => count($empty),
    'vacios' => $empty,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
if ($ok < 100) {
    exit(2);
}
