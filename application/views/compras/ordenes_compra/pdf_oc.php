<?php
$emp = $empresa ?? null;
$logoUrl = !empty($emp->logo) ? base_url($emp->logo) : base_url('assets/dist/img/brands/chisa_recubrimientos_logo.jpg');

$nombreCliente = $emp->razon_social ?? 'Chisa Recubrimientos';
$nombreComercialCliente = trim($emp->nombre_comercial ?? '');
$rfcCliente = trim($emp->rfc ?? '');
$telefonoCliente = trim($emp->telefono ?? '');
$emailCliente = trim($emp->email ?? '');

$direccionCliente = trim(implode(', ', array_filter([
    trim(($emp->calle ?? '') . ' ' . ($emp->numero_exterior ?? '') . ' ' . ($emp->numero_interior ?? '')),
    $emp->colonia ?? '',
    $emp->ciudad ?? '',
    $emp->estado ?? '',
    $emp->codigo_postal ?? '',
])));
if (!$direccionCliente) {
    $direccionCliente = 'México';
}

$direccionProveedor = trim(implode(', ', array_filter([
    trim($orden->direccion_proveedor ?? ''),
    $orden->ciudad_proveedor ?? '',
    $orden->estado_proveedor ?? '',
    $orden->cp_proveedor ?? '',
])));

$fechaElaboracion = !empty($orden->fecha_orden)
    ? date('d/m/Y', strtotime($orden->fecha_orden))
    : date('d/m/Y');

$subtotal = (float) ($orden->subtotal ?? 0);
$iva = (float) ($orden->iva ?? 0);
$total = (float) ($orden->total ?? 0);
$importeLetra = function_exists('numero_a_letras_mxn') ? numero_a_letras_mxn($total) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Orden de Compra - <?=htmlspecialchars($orden->folio)?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #222; background: #fff; }
    table { border-collapse: collapse; width: 100%; }
    td, th { vertical-align: top; }

    .page { width: 100%; max-width: 800px; margin: 0 auto; padding: 16px 18px; }

    .hdr-logo { width: 28%; }
    .hdr-logo img { max-height: 68px; max-width: 170px; }
    .hdr-title { width: 44%; text-align: center; }
    .hdr-title .titulo { font-size: 20px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #1a1a1a; }
    .hdr-title .folio { font-size: 14px; font-weight: bold; margin-top: 4px; color: #c0392b; }
    .hdr-title .fecha { font-size: 11px; margin-top: 6px; color: #444; }
    .hdr-meta { width: 28%; text-align: right; font-size: 10px; color: #555; }
    .hdr-meta strong { color: #333; }

    .sep { border-bottom: 2px solid #2c3e50; height: 2px; line-height: 2px; margin: 10px 0 14px; }

    .bloque-titulo { background: #2c3e50; color: #fff; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; padding: 5px 8px; }
    .bloque-cuerpo { border: 1px solid #bbb; border-top: none; padding: 8px 10px; font-size: 11px; line-height: 1.55; min-height: 110px; }
    .bloque-cuerpo p { margin-bottom: 3px; }
    .bloque-cuerpo .lbl { font-weight: bold; color: #444; }

    .items { margin-top: 14px; border: 1px solid #999; }
    .items thead th { background: #2c3e50; color: #fff; font-size: 10px; text-transform: uppercase; padding: 7px 6px; border: 1px solid #2c3e50; text-align: center; }
    .items tbody td { border: 1px solid #ccc; padding: 6px 7px; font-size: 11px; }
    .items tbody tr:nth-child(even) td { background: #f7f8fa; }
    .items .col-cant { width: 10%; text-align: center; }
    .items .col-unid { width: 10%; text-align: center; }
    .items .col-desc { width: 46%; }
    .items .col-pu { width: 14%; text-align: right; }
    .items .col-imp { width: 14%; text-align: right; }
    .items .codigo-linea { display: block; font-size: 9px; color: #777; margin-top: 2px; }

    .letra-box { margin-top: 12px; border: 1px solid #999; padding: 8px 10px; font-size: 11px; line-height: 1.5; }
    .letra-box .lbl { font-weight: bold; text-transform: uppercase; font-size: 10px; color: #444; }

    .totales { margin-top: 10px; }
    .totales td { padding: 4px 8px; font-size: 11px; }
    .totales .lbl { text-align: right; font-weight: bold; padding-right: 12px; white-space: nowrap; }
    .totales .val { text-align: right; width: 120px; border-bottom: 1px solid #ddd; }
    .totales .total-row .lbl,
    .totales .total-row .val { font-size: 13px; font-weight: bold; border-top: 2px solid #2c3e50; border-bottom: 2px solid #2c3e50; padding-top: 6px; padding-bottom: 6px; }

    .notas { margin-top: 12px; border: 1px solid #e0a040; background: #fffbf0; padding: 8px 10px; font-size: 10px; line-height: 1.5; }
    .notas strong { display: block; margin-bottom: 3px; color: #b7791f; text-transform: uppercase; font-size: 10px; }

    .firmas { margin-top: 36px; }
    .firmas td { width: 33%; text-align: center; padding: 0 10px; }
    .firmas .linea { border-top: 1px solid #333; margin-bottom: 5px; height: 1px; }
    .firmas .etq { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #444; }

    .print-btn { position: fixed; top: 12px; right: 12px; background: #2c3e50; color: #fff; border: none; padding: 9px 18px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; z-index: 99; }

    @media print {
      .print-btn { display: none; }
      body { font-size: 10px; }
      .page { padding: 0; max-width: none; }
      @page { margin: 1cm 1.2cm; size: A4 portrait; }
    }
  </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>

<div class="page">

  <!-- ENCABEZADO -->
  <table>
    <tr>
      <td class="hdr-logo">
        <img src="<?=$logoUrl?>" alt="<?=htmlspecialchars($nombreCliente)?>">
      </td>
      <td class="hdr-title">
        <div class="titulo">Orden de Compra</div>
        <div class="folio">N&deg; <?=htmlspecialchars($orden->folio)?></div>
        <div class="fecha">Fecha de elaboraci&oacute;n: <?=$fechaElaboracion?></div>
      </td>
      <td class="hdr-meta">
        <?php if (!empty($orden->fecha_entrega_estimada)): ?>
        <div><strong>Entrega estimada:</strong><br><?=date('d/m/Y', strtotime($orden->fecha_entrega_estimada))?></div><br>
        <?php endif; ?>
        <?php if (!empty($orden->forma_pago)): ?>
        <div><strong>Forma de pago:</strong><br><?=htmlspecialchars($orden->forma_pago)?></div>
        <?php endif; ?>
        <?php if (!empty($orden->condiciones_pago)): ?>
        <div style="margin-top:4px;"><strong>Condiciones:</strong><br><?=htmlspecialchars($orden->condiciones_pago)?></div>
        <?php endif; ?>
      </td>
    </tr>
  </table>
  <div class="sep"></div>

  <!-- PROVEEDOR | CLIENTE -->
  <table>
    <tr>
      <td style="width:50%; padding-right:8px;">
        <div class="bloque-titulo">Proveedor</div>
        <div class="bloque-cuerpo">
          <?php if (!empty($orden->attn)): ?>
          <p><span class="lbl">At&rsquo;n:</span> <?=htmlspecialchars($orden->attn)?></p>
          <?php endif; ?>
          <p><span class="lbl">Raz&oacute;n social:</span> <?=htmlspecialchars($orden->razon_social ?? '—')?></p>
          <?php if (!empty($orden->nombre_comercial)): ?>
          <p><span class="lbl">Nombre comercial:</span> <?=htmlspecialchars($orden->nombre_comercial)?></p>
          <?php endif; ?>
          <?php if (!empty($orden->rfc_proveedor)): ?>
          <p><span class="lbl">RFC:</span> <?=htmlspecialchars($orden->rfc_proveedor)?></p>
          <?php endif; ?>
          <?php if (!empty($orden->telefono_proveedor)): ?>
          <p><span class="lbl">Tel&eacute;fono:</span> <?=htmlspecialchars($orden->telefono_proveedor)?></p>
          <?php endif; ?>
          <?php if (!empty($orden->email_proveedor)): ?>
          <p><span class="lbl">Correo:</span> <?=htmlspecialchars($orden->email_proveedor)?></p>
          <?php endif; ?>
          <?php if ($direccionProveedor): ?>
          <p><span class="lbl">Direcci&oacute;n:</span> <?=htmlspecialchars($direccionProveedor)?></p>
          <?php endif; ?>
        </div>
      </td>
      <td style="width:50%; padding-left:8px;">
        <div class="bloque-titulo">Cliente</div>
        <div class="bloque-cuerpo">
          <p><span class="lbl">Raz&oacute;n social:</span> <?=htmlspecialchars($nombreCliente)?></p>
          <?php if ($nombreComercialCliente): ?>
          <p><span class="lbl">Nombre comercial:</span> <?=htmlspecialchars($nombreComercialCliente)?></p>
          <?php endif; ?>
          <?php if ($rfcCliente): ?>
          <p><span class="lbl">RFC:</span> <?=htmlspecialchars($rfcCliente)?></p>
          <?php endif; ?>
          <?php if ($telefonoCliente): ?>
          <p><span class="lbl">Tel&eacute;fono:</span> <?=htmlspecialchars($telefonoCliente)?></p>
          <?php endif; ?>
          <?php if ($emailCliente): ?>
          <p><span class="lbl">Correo:</span> <?=htmlspecialchars($emailCliente)?></p>
          <?php endif; ?>
          <p><span class="lbl">Direcci&oacute;n:</span> <?=htmlspecialchars($direccionCliente)?></p>
        </div>
      </td>
    </tr>
  </table>

  <!-- DETALLE -->
  <table class="items">
    <thead>
      <tr>
        <th class="col-cant">Cantidad</th>
        <th class="col-unid">Unidad</th>
        <th class="col-desc">Descripci&oacute;n</th>
        <th class="col-pu">P.U.</th>
        <th class="col-imp">Importe</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($detalles)): ?>
      <tr>
        <td colspan="5" style="text-align:center; color:#888; padding:12px;">Sin partidas registradas</td>
      </tr>
      <?php else: ?>
      <?php foreach ($detalles as $item):
        $codigo = trim($item->codigo_proveedor ?: ($item->codigo ?? ''));
        $descripcion = $item->nombre_proveedor ?: $item->nombre_tecnico;
      ?>
      <tr>
        <td class="col-cant"><?=number_format((float) $item->cantidad_solicitada, 2)?></td>
        <td class="col-unid"><?=htmlspecialchars($item->unidad_medida)?></td>
        <td class="col-desc">
          <?=htmlspecialchars($descripcion)?>
          <?php if ($codigo): ?>
          <span class="codigo-linea">C&oacute;digo: <?=htmlspecialchars($codigo)?></span>
          <?php endif; ?>
        </td>
        <td class="col-pu">$<?=number_format((float) $item->precio_unitario, 2)?></td>
        <td class="col-imp">$<?=number_format((float) $item->subtotal, 2)?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- IMPORTE CON LETRA -->
  <?php if ($importeLetra): ?>
  <div class="letra-box">
    <span class="lbl">Importe con letra:</span>
    <?=htmlspecialchars($importeLetra)?>
  </div>
  <?php endif; ?>

  <!-- TOTALES -->
  <table class="totales">
    <tr>
      <td style="width:60%;"></td>
      <td class="lbl">Subtotal:</td>
      <td class="val">$<?=number_format($subtotal, 2)?></td>
    </tr>
    <tr>
      <td></td>
      <td class="lbl">IVA (16%):</td>
      <td class="val">$<?=number_format($iva, 2)?></td>
    </tr>
    <tr class="total-row">
      <td></td>
      <td class="lbl">Total:</td>
      <td class="val">$<?=number_format($total, 2)?> MXN</td>
    </tr>
  </table>

  <!-- OBSERVACIONES -->
  <?php if (!empty($orden->observaciones)): ?>
  <div class="notas">
    <strong>Observaciones / Condiciones</strong>
    <?=nl2br(htmlspecialchars($orden->observaciones))?>
  </div>
  <?php endif; ?>

  <!-- FIRMAS -->
  <table class="firmas">
    <tr>
      <td>
        <div class="linea"></div>
        <div class="etq">Solicit&oacute;</div>
      </td>
      <td>
        <div class="linea"></div>
        <div class="etq">Elabor&oacute;</div>
      </td>
      <td>
        <div class="linea"></div>
        <div class="etq">Autoriz&oacute;</div>
      </td>
    </tr>
  </table>

</div>
</body>
</html>
