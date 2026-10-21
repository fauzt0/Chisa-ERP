<?php
$c = $cotizacion;
$marca = $marca ?? [];
$empresa = is_object($empresa ?? null) ? $empresa : (object) [];
$paraPdf = !empty($para_pdf);
$meses = [1=>'ENERO',2=>'FEBRERO',3=>'MARZO',4=>'ABRIL',5=>'MAYO',6=>'JUNIO',7=>'JULIO',8=>'AGOSTO',9=>'SEPTIEMBRE',10=>'OCTUBRE',11=>'NOVIEMBRE',12=>'DICIEMBRE'];
$ts = strtotime($c->fecha ?: date('Y-m-d'));
$ciudad = $marca['ciudad'] ?: 'CIUDAD DE MÉXICO';
$fechaTxt = mb_strtoupper($ciudad) . ' A ' . date('j', $ts) . ' DE ' . $meses[(int) date('n', $ts)] . ' DEL ' . date('Y', $ts);
$logo = $marca['logo_documento'] ?? ($empresa->logo ?? '');
$icono = $marca['icono'] ?? '';
$agua = $marca['logo'] ?? '';
$nombreDoc = $empresa->nombre_comercial ?? $empresa->razon_social ?? ($marca['nombre'] ?: 'CHISA');
$src = function ($ruta) use ($paraPdf) {
    if (!$ruta) return '';
    if ($paraPdf) {
        $abs = FCPATH . ltrim($ruta, '/');
        return is_file($abs) ? $abs : '';
    }
    return base_url($ruta);
};
$presente = implode('&nbsp;&nbsp;', str_split('PRESENTE'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?=htmlspecialchars($c->folio)?></title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; color: #163a63; margin: <?= $paraPdf ? '0' : '24px 32px' ?>; font-size: 11px; }
  .hoja { position: relative; }
  .agua { position: fixed; top: 34%; left: 18%; width: 64%; opacity: .10; }
  .agua-texto { position: fixed; top: 40%; left: 0; right: 0; text-align: center; font-size: 64px; letter-spacing: .2em; color: rgba(22,58,99,.08); transform: rotate(-18deg); }
  .contenido { position: relative; }
  table.carta { width: 100%; border-collapse: collapse; }
  table.carta td { border: none; background: transparent; padding: 0; vertical-align: top; color: #163a63; }
  .wordmark { font-size: 28px; letter-spacing: .18em; font-weight: 700; color: #163a63; }
  .wordmark span { display: block; font-size: 9px; letter-spacing: .22em; font-weight: 400; }
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
  .pie { margin-top: 26px; font-size: 9px; color: #8aa; }
  .toolbar { margin-bottom: 12px; }
  .toolbar a, .toolbar button { margin-right: 8px; }
  @media print { .toolbar { display: none; } body { margin: 12px; } }
</style>
</head>
<body>
<?php if (!$paraPdf): ?>
<div class="toolbar">
  <button onclick="window.print()">Imprimir</button>
  <a href="<?=base_url('ventas/CotizacionesObra/pdf/'.$c->id)?>">Descargar PDF</a>
  <a href="<?=base_url('ventas/CotizacionesObra/excel/'.$c->id)?>">Excel</a>
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
            <div class="wordmark"><?=htmlspecialchars($nombreDoc)?><span>RECUBRIMIENTOS</span></div>
          <?php endif; ?>
        </td>
        <td class="contacto">
          <?php if (!empty($marca['direccion'])): ?><div><?=htmlspecialchars($marca['direccion'])?></div><?php endif; ?>
          <?php if (!empty($marca['ciudad'])): ?><div><?=htmlspecialchars($marca['ciudad'])?></div><?php endif; ?>
          <?php if (!empty($marca['telefono'])): ?><div><?=htmlspecialchars($marca['telefono'])?></div><?php endif; ?>
          <?php if (!empty($marca['email'])): ?><div><?=htmlspecialchars($marca['email'])?></div><?php endif; ?>
          <?php if (!empty($marca['sitio_web'])): ?><div><?=htmlspecialchars($marca['sitio_web'])?></div><?php endif; ?>
          <?php if ($paraPdf && !empty($marca['sitio_web']) && class_exists(\Mpdf\QrCode\QrCode::class)): ?>
            <div style="margin-top:4px"><barcode code="<?=htmlspecialchars($marca['sitio_web'])?>" type="QR" size="0.6" error="M" disableborder="1" /></div>
          <?php endif; ?>
        </td>
      </tr>
    </table>
    <div class="fecha"><?=htmlspecialchars($fechaTxt)?></div>
    <div class="bloque">CONSTRUCTORA: <?=htmlspecialchars($c->constructora ?: '')?></div>
    <div class="bloque"><?=$presente?></div>
    <div class="bloque atn">AT'N: <?=htmlspecialchars($c->atencion ?: '')?></div>
    <div class="bloque">OBRA: <?=htmlspecialchars($c->obra_nombre ?: '')?><?= $c->ubicacion ? ', '.htmlspecialchars($c->ubicacion) : '' ?></div>
    <?php
    $refVisible = trim((string) ($c->pres_ref ?: ''));
    if ($refVisible === '' || preg_match('/\.(xlsx|xls)$/i', $refVisible)) {
        $refVisible = $c->folio;
    }
    ?>
    <div class="bloque">PRES. REF. <?=htmlspecialchars($refVisible)?></div>
    <p class="intro"><?=htmlspecialchars($c->introduccion ?: '')?></p>
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
            if ($nombre === null || $nombre === '') return;
            echo '<tr class="sub"><td colspan="4" style="text-align:right">SUBTOTAL '.htmlspecialchars($nombre).'</td><td class="num">$ '.number_format($monto, 2).'</td></tr>';
        };
        foreach ($c->detalles as $d):
            $g = trim((string) $d->grupo);
            if ($g !== (string) $grupo) {
                $pintarSub($grupo, $sub);
                $sub = 0;
                $grupo = $g;
                if ($g !== '') {
                    echo '<tr><td class="fase" colspan="5">'.htmlspecialchars($g).'</td></tr>';
                }
            }
            $sub += (float) $d->importe;
      ?>
        <tr>
          <td class="concepto"><?=nl2br(htmlspecialchars($d->concepto))?></td>
          <td class="unidad"><?=htmlspecialchars($d->unidad ?: '')?></td>
          <td class="num"><?=number_format((float)$d->cantidad, 2)?></td>
          <td class="num">$ <?=number_format((float)$d->precio_unitario, 2)?></td>
          <td class="num">$ <?=number_format((float)$d->importe, 2)?></td>
        </tr>
      <?php endforeach; $pintarSub($grupo, $sub); ?>
        <tr class="sub">
          <td colspan="4" style="text-align:right">TOTAL</td>
          <td class="num">$ <?=number_format((float)$c->total, 2)?></td>
        </tr>
      </tbody>
    </table>
    <p class="pie">Folio <?=htmlspecialchars($c->folio)?> · <?=htmlspecialchars($c->estatus)?></p>
  </div>
</div>
</body>
</html>
