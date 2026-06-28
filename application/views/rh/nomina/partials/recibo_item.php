<?php defined('BASEPATH') OR exit('No direct script access allowed');
$monto_pagado_total = (float)($det->monto_pagado ?? 0);
$monto_recibo = isset($montos_lote[(int)$det->id])
    ? (float)$montos_lote[(int)$det->id]
    : $monto_pagado_total;
$neto = (float)$det->neto;
if ($monto_recibo <= 0 && in_array($det->estatus ?? '', ['Pagado', 'Parcial'], true)) {
    $monto_recibo = $neto;
}
if ($monto_pagado_total <= 0 && $monto_recibo > 0) {
    $monto_pagado_total = $monto_recibo;
}
$pendiente = max(0, round($neto - $monto_pagado_total, 2));
$folio_empleado = (string)($det->numero_empleado ?? $det->id);
$folio_recibo = $nomina->folio . '-' . str_pad($folio_empleado, 6, '0', STR_PAD_LEFT);
$nombre = trim(implode(' ', array_filter([
    $det->nombre ?? '',
    $det->apellido_paterno ?? '',
    $det->apellido_materno ?? '',
], 'strlen')));
$fecha_pago_det = !empty($det->fecha_pago)
    ? date('d/m/Y H:i', strtotime((string)$det->fecha_pago))
    : (!empty($nomina->fecha_pago) ? date('d/m/Y', strtotime((string)$nomina->fecha_pago)) : date('d/m/Y'));
?>
<div class="recibo">
  <div class="folio-recibo">Recibo: <strong><?= htmlspecialchars($folio_recibo) ?></strong></div>
  <div class="header">
    <h1>CHISA RECUBRIMIENTOS S.A. DE C.V.</h1>
    <h2>RECIBO DE PAGO DE NÓMINA</h2>
    <p>Nómina <?= htmlspecialchars($nomina->folio) ?> · <?= htmlspecialchars($nomina->tipo_nomina) ?></p>
  </div>

  <div class="meta">
    <table>
      <tr><td><strong>Periodo:</strong></td><td><?= date('d/m/Y', strtotime($nomina->periodo_inicio)) ?> — <?= date('d/m/Y', strtotime($nomina->periodo_fin)) ?></td></tr>
      <tr><td><strong>Fecha de pago:</strong></td><td><?= $fecha_pago_det ?></td></tr>
      <tr><td><strong>Días trabajados:</strong></td><td><?= number_format((float)$det->dias_trabajados, 1) ?></td></tr>
    </table>
    <table>
      <tr><td><strong>N° Empleado:</strong></td><td><?= htmlspecialchars($det->numero_empleado ?? '—') ?></td></tr>
      <?php if (!empty($det->rfc)): ?><tr><td><strong>RFC:</strong></td><td><?= htmlspecialchars($det->rfc) ?></td></tr><?php endif; ?>
      <?php if (!empty($det->nss)): ?><tr><td><strong>NSS:</strong></td><td><?= htmlspecialchars($det->nss) ?></td></tr><?php endif; ?>
      <tr><td><strong>Estatus:</strong></td><td><?= htmlspecialchars($det->estatus ?? 'Pendiente') ?></td></tr>
    </table>
  </div>

  <div class="empleado-nombre">
    <?= htmlspecialchars($nombre) ?>
    <?php if (!empty($det->puesto)): ?>
      <span style="font-size:10pt;font-weight:normal;color:#666;"> · <?= htmlspecialchars($det->puesto) ?></span>
    <?php endif; ?>
  </div>

  <table class="conceptos">
    <thead>
      <tr><th>Concepto</th><th style="width:90px;">Tipo</th><th style="width:110px;text-align:right;">Importe</th></tr>
    </thead>
    <tbody>
      <?php if (!empty($det->conceptos)): ?>
        <?php foreach ($det->conceptos as $c):
          $cls = $c->tipo === 'Percepción' ? 'percepcion' : 'deduccion';
        ?>
          <tr class="<?= $cls ?>">
            <td><?= htmlspecialchars($c->concepto) ?></td>
            <td><?= htmlspecialchars($c->tipo) ?></td>
            <td class="monto">$<?= number_format((float)$c->monto, 2) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr class="percepcion">
          <td>Sueldo del periodo</td><td>Percepción</td>
          <td class="monto">$<?= number_format((float)$det->percepciones, 2) ?></td>
        </tr>
        <?php if ((float)$det->deducciones > 0): ?>
        <tr class="deduccion">
          <td>Total deducciones</td><td>Deducción</td>
          <td class="monto">$<?= number_format((float)$det->deducciones, 2) ?></td>
        </tr>
        <?php endif; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="totales">
    <table>
      <tr><td>Total percepciones del periodo</td><td>$<?= number_format((float)$det->percepciones, 2) ?></td></tr>
      <tr><td>Total deducciones del periodo</td><td>-$<?= number_format((float)$det->deducciones, 2) ?></td></tr>
      <tr><td>Neto del periodo</td><td>$<?= number_format($neto, 2) ?></td></tr>
      <tr class="pago"><td><strong>IMPORTE PAGADO (este recibo)</strong></td><td><strong>$<?= number_format($monto_recibo, 2) ?></strong></td></tr>
      <?php if ($monto_pagado_total > $monto_recibo + 0.01): ?>
      <tr><td>Total pagado acumulado del periodo</td><td>$<?= number_format($monto_pagado_total, 2) ?></td></tr>
      <?php endif; ?>
      <?php if ($pendiente > 0.01): ?>
      <tr class="pendiente"><td>Saldo pendiente del periodo</td><td>$<?= number_format($pendiente, 2) ?></td></tr>
      <?php endif; ?>
    </table>
  </div>

  <p class="leyenda">
    Recibo no fiscal. El trabajador manifiesta haber recibido la cantidad indicada como pago de nómina correspondiente al periodo señalado.
    Conserve este comprobante para sus archivos personales.
  </p>

  <div class="firma">
    <div class="firma-linea">Firma del trabajador</div>
    <div class="firma-linea">Recursos Humanos</div>
  </div>
</div>
