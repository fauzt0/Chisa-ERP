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
$src = function ($ruta) use ($paraPdf) {
    if (!$ruta) return '';
    if ($paraPdf) {
        $abs = FCPATH . ltrim($ruta, '/');
        return is_file($abs) ? $abs : '';
    }
    return base_url($ruta);
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title><?=htmlspecialchars($c->folio)?></title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; color: #1d4e89; margin: 24px; font-size: 12px; }
  .hoja { position: relative; }
  .agua { position: fixed; top: 38%; left: 15%; width: 70%; opacity: .12; z-index: 0; }
  .agua-texto { position: fixed; top: 42%; left: 0; right: 0; text-align: center; font-size: 54px; color: rgba(29,78,137,.08); transform: rotate(-18deg); z-index: 0; }
  .contenido { position: relative; z-index: 1; }
  .cabecera { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; }
  .marcas img { max-height: 72px; margin-right: 8px; }
  .contacto { text-align: right; font-size: 11px; line-height: 1.45; }
  .fecha { text-align: right; margin: 28px 0 18px; letter-spacing: .04em; }
  .bloque { margin: 8px 0; font-weight: 700; letter-spacing: .04em; }
  .intro { margin: 16px 0; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; font-size: 11px; letter-spacing: .06em; border-bottom: 1px solid #1d4e89; }
  td { vertical-align: top; padding: 8px 6px 8px 0; }
  .num { text-align: right; white-space: nowrap; }
  .fase { font-weight: 700; letter-spacing: .05em; padding-top: 14px; }
  .sub { font-weight: 700; }
  .toolbar { margin-bottom: 12px; }
  .toolbar a, .toolbar button { margin-right: 8px; }
  @media print { .toolbar { display: none; } body { margin: 0; } }
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
  <?php if (!empty($marca['texto'])): ?><div class="agua-texto"><?=htmlspecialchars($marca['texto'])?></div><?php endif; ?>
  <div class="contenido">
    <div class="cabecera">
      <div class="marcas">
        <?php if ($src($icono)): ?><img src="<?=htmlspecialchars($src($icono))?>" alt="icono"><?php endif; ?>
        <?php if ($src($logo)): ?><img src="<?=htmlspecialchars($src($logo))?>" alt="logo"><?php endif; ?>
        <?php if (!$src($icono) && !$src($logo)): ?><strong><?=htmlspecialchars($empresa->nombre_comercial ?? $empresa->razon_social ?? 'CHISA')?></strong><?php endif; ?>
      </div>
      <div class="contacto">
        <?php if (!empty($marca['direccion'])): ?><div><?=htmlspecialchars($marca['direccion'])?></div><?php endif; ?>
        <?php if (!empty($marca['ciudad'])): ?><div><?=htmlspecialchars($marca['ciudad'])?></div><?php endif; ?>
        <?php if (!empty($marca['telefono'])): ?><div><?=htmlspecialchars($marca['telefono'])?></div><?php endif; ?>
        <?php if (!empty($marca['email'])): ?><div><?=htmlspecialchars($marca['email'])?></div><?php endif; ?>
        <?php if (!empty($marca['sitio_web'])): ?><div><?=htmlspecialchars($marca['sitio_web'])?></div><?php endif; ?>
      </div>
    </div>
    <div class="fecha"><?=htmlspecialchars($fechaTxt)?></div>
    <div class="bloque">CONSTRUCTORA: <?=htmlspecialchars($c->constructora ?: '')?></div>
    <div class="bloque">P R E S E N T E</div>
    <div class="bloque" style="text-align:right">AT'N: <?=htmlspecialchars($c->atencion ?: '')?></div>
    <div class="bloque">OBRA: <?=htmlspecialchars($c->obra_nombre ?: '')?><?= $c->ubicacion ? ', '.htmlspecialchars($c->ubicacion) : '' ?></div>
    <div class="bloque">PRES. REF. <?=htmlspecialchars($c->pres_ref ?: $c->folio)?></div>
    <p class="intro"><?=htmlspecialchars($c->introduccion ?: '')?></p>
    <table>
      <thead>
        <tr>
          <th>CONCEPTO</th>
          <th>UNIDAD</th>
          <th class="num">CANTIDAD</th>
          <th class="num">PRECIO U.</th>
          <th class="num">IMPORTE</th>
        </tr>
      </thead>
      <tbody>
      <?php
        $grupo = null;
        $sub = 0;
        $pintarSub = function ($nombre, $monto) {
            if ($nombre === null || $nombre === '') return;
            echo '<tr class="sub"><td colspan="4" style="text-align:right">SUBTOTAL '.htmlspecialchars($nombre).'</td><td class="num">$'.number_format($monto, 2).'</td></tr>';
        };
        foreach ($c->detalles as $d):
            $g = (string) $d->grupo;
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
          <td><?=nl2br(htmlspecialchars($d->concepto))?></td>
          <td><?=htmlspecialchars($d->unidad ?: '')?></td>
          <td class="num"><?=number_format((float)$d->cantidad, 2)?></td>
          <td class="num">$<?=number_format((float)$d->precio_unitario, 2)?></td>
          <td class="num">$<?=number_format((float)$d->importe, 2)?></td>
        </tr>
      <?php endforeach; $pintarSub($grupo, $sub); ?>
        <tr class="sub">
          <td colspan="4" style="text-align:right">TOTAL</td>
          <td class="num">$<?=number_format((float)$c->total, 2)?></td>
        </tr>
      </tbody>
    </table>
    <p style="margin-top:28px;font-size:11px">Folio interno <?=htmlspecialchars($c->folio)?> · Estatus <?=htmlspecialchars($c->estatus)?></p>
  </div>
</div>
</body>
</html>
