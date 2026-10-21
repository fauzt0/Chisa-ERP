<?php
$cotizaciones = $response['cotizaciones'] ?? [];
$puede = !empty($response['puede_editar']);
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h1 class="h3 mb-1"><?=htmlspecialchars($headTitle)?></h1>
      <p class="text-muted mb-0">Presupuesto de suministro y aplicación que la vendedora arma en tabla. No sustituye la cotización de mostrador ni el presupuesto PRES- de ingeniería.</p>
    </div>
    <?php if ($puede): ?>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-secondary" id="btnSembrar">Cargar ejemplos ligeros</button>
      <a class="btn btn-primary" href="<?=base_url('ventas/CotizacionesObra/editar')?>">Nueva cotización</a>
    </div>
    <?php endif; ?>
  </div>
  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>Folio</th>
            <th>Obra</th>
            <th>Constructora</th>
            <th>Cliente</th>
            <th>Sucursal</th>
            <th>Fecha</th>
            <th>Estatus</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$cotizaciones): ?>
          <tr><td colspan="8" class="text-muted">Aún no hay cotizaciones comerciales de obra.</td></tr>
          <?php endif; ?>
          <?php foreach ($cotizaciones as $c): ?>
          <tr>
            <td><strong><?=htmlspecialchars($c->folio)?></strong></td>
            <td><?=htmlspecialchars($c->obra_nombre ?: '')?></td>
            <td><?=htmlspecialchars($c->constructora ?: '')?></td>
            <td><?=htmlspecialchars($c->razon_social ?: '')?></td>
            <td><?=htmlspecialchars($c->sucursal_nombre ?: '')?></td>
            <td><?=htmlspecialchars($c->fecha ?: '')?></td>
            <td><?=htmlspecialchars($c->estatus)?></td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="<?=base_url('ventas/CotizacionesObra/editar/'.$c->id)?>">Editar</a>
              <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?=base_url('ventas/CotizacionesObra/imprimir/'.$c->id)?>">Ver</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
document.getElementById('btnSembrar')?.addEventListener('click', function () {
  var btn = this;
  btn.disabled = true;
  $.post('<?=base_url('ventas/CotizacionesObra/sembrar_ejemplos_ajax')?>', {}, function (res) {
    res = typeof res === 'string' ? JSON.parse(res) : res;
    alert(res.message || 'Listo');
    if (res.success) location.reload();
    else btn.disabled = false;
  });
});
</script>
