<?php
$empresa = is_object($empresa ?? null) ? $empresa : (object) [];
$marca = $marca_agua ?? [];
$presupuesto = $presupuesto ?? null;
$titulo = $titulo ?? 'Presupuesto de Obra';
$fixUtf8 = static function ($s) {
    $s = (string) $s;
    if ($s !== '' && preg_match('/Ã|Â|â€/', $s)) {
        $f = @utf8_decode($s);
        if (is_string($f) && $f !== '') return $f;
    }
    return $s;
};
$logoUrl = base_url(!empty($empresa->logo) ? $empresa->logo : 'assets/dist/img/brands/chisa_recubrimientos_logo.jpg');
$marcaTexto = $marca['texto'] ?? '';
$marcaLogo = $marca['logo'] ?? '';
$folio = $presupuesto ? $presupuesto->folio : 'SIN-FOLIO';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=htmlspecialchars($titulo)?> - <?=htmlspecialchars($folio)?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #e8ecf1; color: #1a1a2e; padding: 20px; font-size: 10px; }
        .toolbar { max-width: 1100px; margin: 0 auto 14px; display: flex; gap: 8px; }
        .btn { border: none; border-radius: 6px; padding: 9px 16px; font-size: 13px; font-weight: 600; cursor: pointer; color: #fff; }
        .btn-primary { background: #1565C0; }
        .btn-secondary { background: #546E7A; }
        #pdfDocument { max-width: 1100px; margin: 0 auto; }
        .pdf-page { background: #fff; padding: 28px 32px 22px; margin-bottom: 20px; box-shadow: 0 4px 20px rgba(0,0,0,.08); min-height: 680px; position: relative; page-break-after: always; }
        .pdf-page:last-child { page-break-after: auto; }
        .pg-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1565C0; padding-bottom: 12px; margin-bottom: 16px; }
        .pg-header .logo img { max-height: 56px; max-width: 180px; object-fit: contain; }
        .pg-meta { text-align: right; font-size: 9px; line-height: 1.65; color: #374151; }
        .pg-meta .lbl { font-weight: 700; color: #111; }
        .pg-title { text-align: center; font-size: 16px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #111; margin-bottom: 14px; padding: 6px 0; border-top: 1px solid #d1d5db; border-bottom: 1px solid #d1d5db; }
        .pg-footer { position: absolute; bottom: 16px; left: 32px; right: 32px; border-top: 1px solid #d1d5db; padding-top: 8px; font-size: 8px; color: #6b7280; display: flex; justify-content: space-between; align-items: center; }
        table { border-collapse: collapse; width: 100%; }
        table.tabla-datos th, table.tabla-datos td { border: 1px solid #cbd5e1; padding: 4px 6px; font-size: 8.5px; }
        table.tabla-datos th { background: #e8eef5; text-transform: uppercase; font-size: 8px; }
        .bg-total { background: #e8eef5; font-weight: 700; }
        .text-right { text-align: right; } .text-center { text-align: center; }
        .firmas { display: flex; gap: 16px; margin-top: 20px; }
        .firma-block { flex: 1; border: 1px solid #9ca3af; padding: 10px 8px; min-height: 80px; text-align: center; }
        .firma-block .titulo { font-size: 7.5px; font-weight: 700; text-transform: uppercase; margin-bottom: 30px; }
        .firma-line { border-top: 1px solid #374151; margin: 0 10px; padding-top: 4px; font-size: 8px; }
        .simbologia { font-size: 7.5px; border: 1px solid #d1d5db; padding: 8px; margin-top: 10px; }
        .simbologia span { display: inline-block; margin-right: 12px; margin-bottom: 3px; }
        .marca-agua { position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 60px; font-weight: 700; color: rgba(0,0,0,0.06); white-space: nowrap; pointer-events: none; z-index: 1; text-transform: uppercase; }
        @media print { body { background: #fff; padding: 0; } .toolbar { display: none; } .pdf-page { box-shadow: none; margin: 0; } }
    </style>
</head>
<body>
<div class="toolbar">
    <button class="btn btn-primary" id="btnDescargarPdf">Descargar PDF</button>
    <button class="btn btn-secondary" onclick="window.print()">Imprimir</button>
</div>
<div id="pdfDocument">
<div class="pdf-page">
<?php if ($marcaTexto): ?><div class="marca-agua"><?=htmlspecialchars($marcaTexto)?></div><?php endif; ?>
<div class="pg-header">
    <div class="logo"><img src="<?=$logoUrl?>" alt="Logo" crossorigin="anonymous" onerror="this.style.display='none'"></div>
    <div class="pg-meta">
        <div><span class="lbl">RAZÓN SOCIAL:</span> <?=htmlspecialchars($fixUtf8($empresa->razon_social ?? 'Chisa Recubrimientos'))?></div>
        <?php if(!empty($empresa->rfc)): ?><div><span class="lbl">RFC:</span> <?=htmlspecialchars($fixUtf8($empresa->rfc))?></div><?php endif; ?>
        <div><span class="lbl">FOLIO:</span> <?=htmlspecialchars($folio)?></div>
        <?php if ($marcaTexto): ?><div><span class="lbl">SUCURSAL:</span> <?=htmlspecialchars($marcaTexto)?></div><?php endif; ?>
    </div>
</div>
<div class="pg-title"><?=htmlspecialchars(strtoupper($titulo))?></div>
