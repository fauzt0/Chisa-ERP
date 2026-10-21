<?php
$cotizaciones = $response['cotizaciones'] ?? [];
$puede = !empty($response['puede_editar']);
$q = $response['q'] ?? '';
$consulta = !empty($response['consulta']);
$totalConsulta = (int) ($response['total_consulta'] ?? 0);
$badge = ['Borrador'=>'secondary','Enviada'=>'info','Aceptada'=>'success','Rechazada'=>'danger','Cancelada'=>'dark'];
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h1 class="h3 mb-1"><?=htmlspecialchars($headTitle)?></h1>
      <p class="text-muted mb-0">Cada cotización tiene folio. Desde aquí se da seguimiento, se arman adiciones y se consulta el archivo de entrenamiento.</p>
    </div>
    <?php if ($puede): ?>
    <div class="d-flex gap-2 flex-wrap">
      <button type="button" class="btn btn-outline-secondary" id="btnConsulta">Incorporar archivos para consulta</button>
      <a class="btn btn-primary" href="<?=base_url('ventas/CotizacionesObra/editar')?>">Nueva cotización</a>
    </div>
    <?php endif; ?>
  </div>
  <form class="row g-2 mb-3" method="get">
    <div class="col-md-6">
      <input class="form-control" name="q" value="<?=htmlspecialchars($q)?>" placeholder="Buscar folio, constructora, obra o archivo">
    </div>
    <div class="col-md-auto">
      <button class="btn btn-outline-primary">Buscar</button>
      <a class="btn btn-outline-secondary" href="<?=base_url('ventas/CotizacionesObra')?>">Trabajo actual</a>
      <a class="btn btn-outline-secondary" href="<?=base_url('ventas/CotizacionesObra?consulta=1')?>">Archivo (<?=$totalConsulta?>)</a>
    </div>
  </form>
  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>Folio</th>
            <th>Obra</th>
            <th>Constructora</th>
            <th>Cliente</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Estatus</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$cotizaciones): ?>
          <tr><td colspan="8" class="text-muted"><?= $consulta ? 'Todavía no se incorporó el archivo. Usa el botón de arriba.' : 'Aún no hay cotizaciones de trabajo.' ?></td></tr>
          <?php endif; ?>
          <?php foreach ($cotizaciones as $c): ?>
          <tr>
            <td>
              <strong><?=htmlspecialchars($c->folio)?></strong>
              <?php if (!empty($c->padre_folio)): ?><div class="small text-muted">Adición de <?=htmlspecialchars($c->padre_folio)?></div><?php endif; ?>
              <?php if (!empty($c->es_consulta)): ?><div class="small text-muted">Consulta de archivo</div><?php endif; ?>
            </td>
            <td><?=htmlspecialchars($c->obra_nombre ?: '')?></td>
            <td><?=htmlspecialchars($c->constructora ?: '')?></td>
            <td><?=htmlspecialchars($c->razon_social ?: '')?></td>
            <td><?=htmlspecialchars($c->fecha ?: '')?></td>
            <td><?= $c->total !== null ? '$'.number_format((float)$c->total, 2) : '—' ?></td>
            <td><span class="badge bg-<?=$badge[$c->estatus] ?? 'secondary'?>"><?=htmlspecialchars($c->estatus)?></span></td>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="<?=base_url('ventas/CotizacionesObra/editar/'.$c->id)?>"><?= !empty($c->es_consulta) ? 'Consultar' : 'Editar' ?></a>
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
document.getElementById('btnConsulta')?.addEventListener('click', function () {
  var btn = this;
  if (!confirm('Se registran los Excel de entrenamiento para consultarlos, sin abrir cada archivo.')) return;
  btn.disabled = true;
  $.post('<?=base_url('ventas/Seguimiento/registrar_consulta_ajax')?>', {}, function (res) {
    res = typeof res === 'string' ? JSON.parse(res) : res;
    alert(res.message || 'Listo');
    if (res.success) location.href = '<?=base_url('ventas/CotizacionesObra?consulta=1')?>';
    else btn.disabled = false;
  });
});
</script>
