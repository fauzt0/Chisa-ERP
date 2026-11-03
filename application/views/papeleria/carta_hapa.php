<?php
$this->load->helper('papeleria_hapa');
$marca = $marca ?? [];
$empresa = is_object($empresa ?? null) ? $empresa : (object) [];
$paraPdf = !empty($para_pdf);
$meses = [1=>'ENERO',2=>'FEBRERO',3=>'MARZO',4=>'ABRIL',5=>'MAYO',6=>'JUNIO',7=>'JULIO',8=>'AGOSTO',9=>'SEPTIEMBRE',10=>'OCTUBRE',11=>'NOVIEMBRE',12=>'DICIEMBRE'];
$fecha = $fecha ?? date('Y-m-d');
$ts = strtotime($fecha);
$ciudad = $marca['ciudad'] ?: 'CIUDAD DE MÉXICO';
$fechaTxt = mb_strtoupper($ciudad) . ' A ' . date('j', $ts) . ' DE ' . $meses[(int) date('n', $ts)] . ' DEL ' . date('Y', $ts);
$logo = $marca['logo_documento'] ?? ($empresa->logo ?? '');
$icono = $marca['icono'] ?? '';
$agua = $marca['logo'] ?? '';
$nombreDoc = $marca['nombre'] ?: ($empresa->nombre_comercial ?? $empresa->razon_social ?? 'HAPA');
$src = function ($ruta) use ($paraPdf) {
    if (!$ruta) {
        return '';
    }
    if ($paraPdf) {
        $abs = FCPATH . ltrim($ruta, '/');
        return is_file($abs) ? $abs : '';
    }
    return base_url($ruta);
};
$presente = implode('&nbsp;&nbsp;', str_split('PRESENTE'));
$lineas = $lineas ?? [];
$total = (float) ($total ?? 0);
$iva = isset($iva) ? (float) $iva : null;
$iva_pct = isset($iva_pct) ? (float) $iva_pct : null;
$folio = $folio ?? '';
$pres_ref = $pres_ref ?? $folio;
$notas = trim((string) ($notas_legales ?? ''));
if ($notas === '') {
    $notas = papeleria_hapa_notas();
}
$forma = trim((string) ($forma_pago ?? ''));
if ($forma === '') {
    $forma = papeleria_hapa_forma_pago();
}
$pie = trim((string) ($pie_texto ?? ($marca['pie_cotizacion'] ?? '')));
$toolbar_pdf = $toolbar_pdf ?? '';
$toolbar_excel = $toolbar_excel ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?=htmlspecialchars($folio ?: $pres_ref)?></title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; color: #163a63; margin: <?= $paraPdf ? '0' : '24px 32px' ?>; font-size: 11px; }
  .hoja { position: relative; }
  .agua { position: fixed; top: 34%; left: 18%; width: 64%; opacity: .10; }
  .agua-texto { position: fixed; top: 40%; left: 0; right: 0; text-align: center; font-size: 64px; letter-spacing: .2em; color: rgba(22,58,99,.08); transform: rotate(-18deg); }
  .contenido { position: relative; }
  table.carta { width: 100%; border-collapse: collapse; }
  table.carta td { border: none; background: transparent; padding: 0; vertical-align: top; color: #163a63; }
  .wordmark { font-size: 22px; letter-spacing: .12em; font-weight: 700; color: #163a63; }
  .wordmark span { display: block; font-size: 9px; letter-spacing: .18em; font-weight: 400; }
  .contacto { text-align: right; font-size: 10px; line-height: 1.45; color: #5c6b7a; }
  .fecha { text-align: right; margin: 22px 0 16px; letter-spacing: .06em; color: #1a4f86; font-size: 11px; }
  .bloque { margin: 7px 0; font-weight: 700; letter-spacing: .03em; }
  .atn { text-align: right; color: #1a4f86; }
  .intro { margin: 14px 0 10px; font-size: 11px; }
  table.partidas { width: 100%; border-collapse: collapse; font-size: 10px; line-height: 1.25; }
  table.partidas th { text-align: left; font-size: 10px; letter-spacing: .06em; color: #163a63; border-bottom: 1.5px solid #163a63; padding: 3px 4px 3px 0; background: transparent; }
  table.partidas td { vertical-align: top; padding: 3px 4px 3px 0; border: none; background: transparent; color: #163a63; }
  table.partidas td.unidad { background-color: #e7f1fb; text-align: center; font-weight: 700; color: #1a4f86; width: 68px; }
  table.partidas .num { text-align: right; white-space: nowrap; color: #1a4f86; }
  table.partidas .concepto { width: 56%; }
  table.partidas .fase { font-weight: 700; letter-spacing: .04em; color: #1a4f86; padding-top: 12px; }
  table.partidas .sub td { font-weight: 700; color: #1a4f86; padding-top: 4px; }
  .pie { margin-top: 18px; font-size: 9px; color: #5c6b7a; }
  .legal { page-break-before: always; padding-top: 12px; font-size: 10px; line-height: 1.45; color: #163a63; }
  .legal h3 { text-align: center; letter-spacing: .12em; font-size: 12px; margin: 18px 0 10px; }
  .legal p { text-align: justify; margin: 0 0 10px; }
  .firma { text-align: center; margin-top: 36px; letter-spacing: .08em; font-weight: 700; }
  .toolbar { margin-bottom: 12px; }
  .interno { color: #888; font-size: 10px; }
  @media print { .toolbar, .interno { display: none !important; } body { margin: 12px; } }
</style>
</head>
<body>
<?php if (!$paraPdf): ?>
<div class="toolbar">
  <button type="button" onclick="window.print()">Imprimir</button>
  <?php if ($toolbar_pdf): ?><a href="<?=htmlspecialchars($toolbar_pdf)?>">Descargar PDF</a><?php endif; ?>
  <?php if ($toolbar_excel): ?><a href="<?=htmlspecialchars($toolbar_excel)?>">Excel</a><?php endif; ?>
</div>
<?php endif; ?>
<div class="hoja">
  <?php if ($src($agua)): ?><img class="agua" src="<?=htmlspecialchars($src($agua))?>" alt=""><?php endif; ?>
  <?php if (!$paraPdf && !empty($marca['texto'])): ?><div class="agua-texto"><?=htmlspecialchars($marca['texto'])?></div><?php endif; ?>
  <div class="contenido">
    <table class="carta">
      <tr>
        <td style="width:48%">
          <?php if ($src($icono)): ?><img src="<?=htmlspecialchars($src($icono))?>" alt="" style="max-height:64px;margin-right:8px"><?php endif; ?>
          <?php if ($src($logo)): ?><img src="<?=htmlspecialchars($src($logo))?>" alt="" style="max-height:64px"><?php endif; ?>
          <?php if (!$src($icono) && !$src($logo)): ?>
            <div class="wordmark"><?=htmlspecialchars($nombreDoc)?><span>SOLUCIONES INTEGRALES EN RECUBRIMIENTOS</span></div>
          <?php endif; ?>
        </td>
        <td class="contacto">
          <?php if (!empty($marca['direccion'])): ?><div><?=htmlspecialchars($marca['direccion'])?></div><?php endif; ?>
          <?php if (!empty($marca['ciudad'])): ?><div><?=htmlspecialchars($marca['ciudad'])?></div><?php endif; ?>
          <?php if (!empty($marca['telefono'])): ?><div><?=htmlspecialchars($marca['telefono'])?></div><?php endif; ?>
          <?php if (!empty($marca['email'])): ?><div><?=htmlspecialchars($marca['email'])?></div><?php endif; ?>
          <?php if (!empty($marca['sitio_web'])): ?><div><?=htmlspecialchars($marca['sitio_web'])?></div><?php endif; ?>
        </td>
      </tr>
    </table>
    <div class="fecha"><?=htmlspecialchars($fechaTxt)?></div>
    <div class="bloque">CONSTRUCTORA: <?=htmlspecialchars($constructora ?? '')?></div>
    <div class="bloque"><?=$presente?></div>
    <div class="bloque atn">AT'N: <?=htmlspecialchars($atencion ?? '')?></div>
    <div class="bloque">OBRA: <?=htmlspecialchars($obra_nombre ?? '')?><?= !empty($ubicacion) ? ', '.htmlspecialchars($ubicacion) : '' ?></div>
    <div class="bloque">PRES. REF. <?=htmlspecialchars($pres_ref)?></div>
    <p class="intro"><?=htmlspecialchars($introduccion ?: 'PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.')?></p>
    <table class="partidas">
      <tbody>
        <tr>
          <th>CONCEPTO</th>
          <th style="text-align:center">UNIDAD</th>
          <th class="num">CANTIDAD</th>
          <th class="num">PRECIO U.</th>
          <th class="num">IMPORTE</th>
        </tr>
      <?php
        $grupo = null;
        $sub = 0;
        $pintarSub = function ($nombre, $monto) {
            if ($nombre === null || $nombre === '') {
                return;
            }
            echo '<tr class="sub"><td colspan="4" style="text-align:right">SUBTOTAL '.htmlspecialchars($nombre).'</td><td class="num">$ '.number_format($monto, 2).'</td></tr>';
        };
        foreach ($lineas as $d):
            $g = trim((string) ($d['grupo'] ?? ''));
            if ($g !== (string) $grupo) {
                $pintarSub($grupo, $sub);
                $sub = 0;
                $grupo = $g;
                if ($g !== '') {
                    echo '<tr><td class="fase" colspan="5">'.htmlspecialchars($g).'</td></tr>';
                }
            }
            $sub += (float) $d['importe'];
      ?>
        <tr>
          <td class="concepto"><?=nl2br(htmlspecialchars($d['concepto']))?></td>
          <td class="unidad"><?=htmlspecialchars($d['unidad'] ?? '')?></td>
          <td class="num"><?=number_format((float)$d['cantidad'], 2)?></td>
          <td class="num">$ <?=number_format((float)$d['precio_unitario'], 2)?></td>
          <td class="num">$ <?=number_format((float)$d['importe'], 2)?></td>
        </tr>
      <?php endforeach; $pintarSub($grupo, $sub); ?>
        <tr class="sub">
          <td colspan="4" style="text-align:right">SUBTOTAL</td>
          <td class="num">$ <?=number_format((float)$total, 2)?></td>
        </tr>
        <?php if ($iva !== null): ?>
        <tr class="sub">
          <td colspan="4" style="text-align:right">I.V.A.<?= $iva_pct !== null ? ' '.number_format($iva_pct, 0).'%' : '' ?></td>
          <td class="num">$ <?=number_format($iva, 2)?></td>
        </tr>
        <tr class="sub">
          <td colspan="4" style="text-align:right">TOTAL</td>
          <td class="num">$ <?=number_format((float)$total + $iva, 2)?></td>
        </tr>
        <?php else: ?>
        <tr class="sub">
          <td colspan="4" style="text-align:right">TOTAL</td>
          <td class="num">$ <?=number_format((float)$total, 2)?></td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <?php if (!empty($nota_interna)): ?>
    <p class="interno"><?=htmlspecialchars($nota_interna)?></p>
    <?php endif; ?>
    <?php if ($pie !== ''): ?>
    <p class="pie"><?=nl2br(htmlspecialchars($pie))?></p>
    <?php endif; ?>
  </div>
</div>
<div class="legal">
  <div class="bloque">FORMA DE PAGO Y CONSIDERACIONES GENERALES CORRESPONDIENTES AL PRESUPUESTO REF. <?=htmlspecialchars($pres_ref)?></div>
  <div class="bloque">OBRA: <?=htmlspecialchars($obra_nombre ?? '')?></div>
  <h3>FORMA DE PAGO</h3>
  <p><?=nl2br(htmlspecialchars($forma))?></p>
  <h3>N O T A S</h3>
  <p><?=nl2br(htmlspecialchars($notas))?></p>
  <p>SIN MAS POR EL MOMENTO, LE REITERO A USTED, LAS SEGURIDADES DE MI MAS DISTINGUIDA CONSIDERACIÓN.</p>
  <div class="firma">ATENTAMENTE<br><br><?=htmlspecialchars(mb_strtoupper($nombreDoc))?></div>
</div>
</body>
</html>
