<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orden de Compra - <?=$orden->folio?></title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
    
    /* Contenido principal */
    .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; }
    
    /* Encabezado */
    .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #2c3e50; padding-bottom: 15px; margin-bottom: 20px; }
    .company-info h1 { font-size: 22px; color: #2c3e50; margin-bottom: 4px; }
    .company-info p { color: #666; font-size: 11px; line-height: 1.5; }
    .document-info { text-align: right; }
    .document-info .doc-title { font-size: 18px; font-weight: bold; color: #2c3e50; text-transform: uppercase; }
    .document-info .folio { font-size: 14px; font-weight: bold; color: #e74c3c; margin-top: 4px; }
    .document-info .doc-date { font-size: 11px; color: #666; margin-top: 4px; }

    /* Badges de estatus */
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    .badge-pendiente { background: #ffeaa7; color: #fdcb6e; border: 1px solid #fdcb6e; }
    .badge-aprobada { background: #dfe6e9; color: #636e72; border: 1px solid #b2bec3; }
    .badge-enviada  { background: #74b9ff44; color: #0984e3; border: 1px solid #74b9ff; }
    .badge-recibida { background: #55efc444; color: #00b894; border: 1px solid #55efc4; }
    .badge-cancelada{ background: #fd79a844; color: #d63031; border: 1px solid #fd79a8; }

    /* Sección de datos */
    .section { margin-bottom: 16px; }
    .section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #888; border-bottom: 1px solid #eee; padding-bottom: 4px; margin-bottom: 8px; font-weight: bold; }

    /* Grid de 2 columnas */
    .row-2 { display: flex; gap: 20px; }
    .col { flex: 1; }
    .info-block { background: #f8f9fa; border-left: 3px solid #2c3e50; padding: 10px 12px; border-radius: 0 4px 4px 0; }
    .info-block p { margin-bottom: 4px; line-height: 1.5; }
    .info-block strong { display: inline-block; min-width: 90px; color: #555; }

    /* Tabla de ítems */
    .items-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    .items-table thead tr { background: #2c3e50; color: white; }
    .items-table thead th { padding: 8px 10px; text-align: left; font-size: 11px; }
    .items-table thead th.text-right { text-align: right; }
    .items-table tbody tr:nth-child(even) { background: #f8f9fa; }
    .items-table tbody td { padding: 7px 10px; border-bottom: 1px solid #eee; vertical-align: top; font-size: 11px; }
    .items-table tbody td.text-right { text-align: right; }
    .items-table tbody td small { display: block; color: #888; font-size: 10px; }

    /* Totales */
    .totals { margin-top: 12px; display: flex; justify-content: flex-end; }
    .totals-box { width: 280px; }
    .totals-row { display: flex; justify-content: space-between; padding: 5px 10px; font-size: 12px; }
    .totals-row.subtotal { border-top: 1px solid #eee; }
    .totals-row.grand-total { background: #2c3e50; color: white; font-weight: bold; font-size: 14px; border-radius: 4px; padding: 8px 10px; margin-top: 6px; }

    /* Notas */
    .notes { margin-top: 16px; padding: 10px 12px; background: #fffbf0; border: 1px solid #f39c12; border-radius: 4px; font-size: 11px; }
    .notes strong { display: block; margin-bottom: 4px; color: #e67e22; }

    /* Footer de firma */
    .signature-area { margin-top: 40px; display: flex; gap: 30px; }
    .signature-line { flex: 1; text-align: center; }
    .signature-line .line { border-top: 1px solid #333; margin-bottom: 6px; }
    .signature-line span { font-size: 11px; color: #555; }

    /* Botón de impresión (solo en pantalla) */
    .print-btn { position: fixed; top: 15px; right: 15px; background: #2c3e50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; }
    .print-btn:hover { background: #34495e; }

    @media print {
      .print-btn { display: none; }
      body { font-size: 11px; }
      .container { padding: 10px; }
      @page { margin: 1cm 1.5cm; }
    }
  </style>
</head>
<body>

<button class="print-btn" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>

<div class="container">

  <!-- ENCABEZADO -->
  <div class="header">
    <div class="company-info">
      <h1>Chisa Recubrimientos</h1>
      <p>
        Industria de la Pintura y Recubrimientos<br>
        México
      </p>
    </div>
    <div class="document-info">
      <div class="doc-title">Orden de Compra</div>
      <div class="folio"><?=$orden->folio?></div>
      <div class="doc-date">
        Fecha: <?=date('d/m/Y', strtotime($orden->fecha_orden))?><br>
        <?php
          $estatus = $orden->estatus;
          $badge_class = strtolower(str_replace(' ', '-', $estatus));
        ?>
        Estatus: <span class="badge badge-<?=$badge_class?>"><?=$estatus?></span>
      </div>
    </div>
  </div>

  <!-- DATOS DEL PROVEEDOR Y DE LA ORDEN -->
  <div class="row-2 section">
    <div class="col">
      <div class="section-title">Datos del Proveedor</div>
      <div class="info-block">
        <p><strong>Razón Social:</strong> <?=$orden->razon_social ?? '—'?></p>
        <?php if(!empty($orden->nombre_comercial)): ?>
        <p><strong>Nombre Comercial:</strong> <?=$orden->nombre_comercial?></p>
        <?php endif; ?>
        <?php if(!empty($orden->rfc_proveedor)): ?>
        <p><strong>RFC:</strong> <?=$orden->rfc_proveedor?></p>
        <?php endif; ?>
      </div>
    </div>
    <div class="col">
      <div class="section-title">Datos de la Orden</div>
      <div class="info-block">
        <p><strong>Fecha Orden:</strong> <?=date('d/m/Y', strtotime($orden->fecha_orden))?></p>
        <?php if(!empty($orden->fecha_entrega_esperada)): ?>
        <p><strong>Entrega Esperada:</strong> <?=date('d/m/Y', strtotime($orden->fecha_entrega_esperada))?></p>
        <?php endif; ?>
        <?php if(!empty($orden->condiciones_pago)): ?>
        <p><strong>Cond. Pago:</strong> <?=$orden->condiciones_pago?></p>
        <?php endif; ?>
        <?php if(!empty($orden->moneda)): ?>
        <p><strong>Moneda:</strong> <?=$orden->moneda?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- TABLA DE PRODUCTOS -->
  <div class="section">
    <div class="section-title">Detalle de Artículos</div>
    <table class="items-table">
      <thead>
        <tr>
          <th style="width:8%">#</th>
          <th style="width:15%">Código</th>
          <th>Descripción</th>
          <th style="width:10%">Unidad</th>
          <th class="text-right" style="width:10%">Cantidad</th>
          <th class="text-right" style="width:12%">P. Unitario</th>
          <th class="text-right" style="width:12%">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php $i = 1; foreach($detalles as $item): ?>
        <tr>
          <td><?=$i++?></td>
          <td>
            <?=htmlspecialchars($item->codigo_proveedor ?: $item->codigo)?>
          </td>
          <td>
            <?=htmlspecialchars($item->nombre_proveedor ?: $item->nombre_tecnico)?>
            <?php if(!empty($item->nombre_proveedor) && $item->nombre_proveedor != $item->nombre_tecnico): ?>
            <small>(Interno: <?=htmlspecialchars($item->nombre_tecnico)?>)</small>
            <?php endif; ?>
          </td>
          <td><?=htmlspecialchars($item->unidad_medida)?></td>
          <td class="text-right"><?=number_format($item->cantidad_solicitada, 2)?></td>
          <td class="text-right">$<?=number_format($item->precio_unitario, 2)?></td>
          <td class="text-right">$<?=number_format($item->subtotal, 2)?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- TOTALES -->
  <div class="totals">
    <div class="totals-box">
      <div class="totals-row subtotal">
        <span>Subtotal:</span>
        <span>$<?=number_format($orden->subtotal ?? 0, 2)?></span>
      </div>
      <?php if(isset($orden->descuento) && $orden->descuento > 0): ?>
      <div class="totals-row">
        <span>Descuento:</span>
        <span>-$<?=number_format($orden->descuento, 2)?></span>
      </div>
      <?php endif; ?>
      <?php if(isset($orden->iva) && $orden->iva > 0): ?>
      <div class="totals-row">
        <span>IVA (16%):</span>
        <span>$<?=number_format($orden->iva, 2)?></span>
      </div>
      <?php endif; ?>
      <div class="totals-row grand-total">
        <span>TOTAL:</span>
        <span>$<?=number_format($orden->total, 2)?> MXN</span>
      </div>
    </div>
  </div>

  <!-- OBSERVACIONES -->
  <?php if(!empty($orden->observaciones)): ?>
  <div class="notes">
    <strong>Observaciones / Condiciones:</strong>
    <?=htmlspecialchars($orden->observaciones)?>
  </div>
  <?php endif; ?>

  <!-- FIRMAS -->
  <div class="signature-area">
    <div class="signature-line">
      <div class="line"></div>
      <span>Elaboró / Compras</span>
    </div>
    <div class="signature-line">
      <div class="line"></div>
      <span>Autorizó / Gerencia</span>
    </div>
    <div class="signature-line">
      <div class="line"></div>
      <span>Aceptó / Proveedor</span>
    </div>
  </div>

</div><!-- /container -->

</body>
</html>
