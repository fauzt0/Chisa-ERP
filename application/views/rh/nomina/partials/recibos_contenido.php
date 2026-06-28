<?php defined('BASEPATH') OR exit('No direct script access allowed');
$montos_lote = $montos_lote ?? [];
include __DIR__ . '/recibos_estilos.php';
?>
<div class="recibos-nomina-wrap">
  <?php if (!empty($sin_pagos)): ?>
    <div class="recibos-empty">
      <p><strong>No hay recibos de pago para mostrar.</strong></p>
      <p>Los recibos se generan cuando al menos un empleado tiene pago registrado en esta nómina.</p>
    </div>
  <?php elseif (!empty($nomina->detalle)): ?>
    <?php foreach ($nomina->detalle as $det):
      include __DIR__ . '/recibo_item.php';
    endforeach; ?>
  <?php else: ?>
    <div class="recibos-empty"><p>No hay empleados en esta nómina.</p></div>
  <?php endif; ?>
</div>
