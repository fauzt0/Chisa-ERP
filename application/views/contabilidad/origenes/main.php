<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$datos = $datos ?? ['facturas' => [], 'compras' => [], 'nominas' => [], 'mapeo' => []];
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
?>
<div class="container-fluid p-0">
  <div class="row mb-3">
    <div class="col-md-8">
      <h3><i class="fas fa-link"></i> <?= $h($headTitle) ?></h3>
      <p class="text-muted mb-0">Lee CFDI, OC recibidas y nóminas pagadas. No modifica Ventas, Compras ni RH. Las pólizas quedan en borrador salvo que autorice.</p>
    </div>
    <div class="col-md-4 text-end">
      <button type="button" class="btn btn-primary" onclick="generarOrigen('todos', false)">
        <i class="fas fa-file-import"></i> Generar pólizas (borrador)
      </button>
      <button type="button" class="btn btn-outline-success" onclick="generarOrigen('todos', true)">
        Generar y autorizar
      </button>
    </div>
  </div>

  <?php if (!empty($periodo_actual)): ?>
  <div class="alert alert-info">Periodo abierto que cubre hoy: <?= $h($periodo_actual->nombre) ?> <?= $h($periodo_actual->año) ?></div>
  <?php else: ?>
  <div class="alert alert-warning">No hay periodo que cubra la fecha de hoy. Al generar se usa el periodo de cada documento.</div>
  <?php endif; ?>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-fac" type="button">CFDI ingresos (<?= count($datos['facturas']) ?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-oc" type="button">Compras recibidas (<?= count($datos['compras']) ?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-nom" type="button">Nóminas pagadas (<?= count($datos['nominas']) ?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-map" type="button">Cuentas mapeadas</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tab-fac">
      <p class="small">Asiento: Clientes (total) / Ventas (subtotal) + IVA trasladado. Solo <code>facturas</code> Emitida. OV sin CFDI no se póliza (evitar duplicar).</p>
      <div class="table-responsive">
        <table class="table table-sm table-striped">
          <thead><tr><th>Folio</th><th>Fecha</th><th>Cliente</th><th class="text-end">Subtotal</th><th class="text-end">IVA</th><th class="text-end">Total</th><th>Póliza</th></tr></thead>
          <tbody>
          <?php foreach ($datos['facturas'] as $f): ?>
            <tr>
              <td><?= $h($f->folio) ?></td>
              <td><?= $h(date('d/m/Y', strtotime($f->fecha_emision))) ?></td>
              <td><?= $h($f->razon_social) ?></td>
              <td class="text-end"><?= number_format((float)$f->subtotal, 2) ?></td>
              <td class="text-end"><?= number_format((float)$f->iva, 2) ?></td>
              <td class="text-end"><?= number_format((float)$f->total, 2) ?></td>
              <td><?= $f->ya_poliza ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">Pendiente</span>' ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($datos['facturas'])): ?><tr><td colspan="7" class="text-muted">Sin CFDI emitidos.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <button type="button" class="btn btn-sm btn-primary" onclick="generarOrigen('facturas', false)">Pólizas de CFDI</button>
    </div>

    <div class="tab-pane fade" id="tab-oc">
      <p class="small">Asiento: Inventario + IVA acreditable / Proveedores. Solo OC Recibida o Recibida Parcial.</p>
      <div class="table-responsive">
        <table class="table table-sm table-striped">
          <thead><tr><th>Folio</th><th>Fecha</th><th>Proveedor</th><th>Estatus</th><th class="text-end">Total</th><th>Póliza</th></tr></thead>
          <tbody>
          <?php foreach ($datos['compras'] as $oc): ?>
            <tr>
              <td><?= $h($oc->folio) ?></td>
              <td><?= $h(date('d/m/Y', strtotime($oc->fecha_orden))) ?></td>
              <td><?= $h($oc->razon_social) ?></td>
              <td><?= $h($oc->estatus) ?></td>
              <td class="text-end"><?= number_format((float)$oc->total, 2) ?></td>
              <td><?= $oc->ya_poliza ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">Pendiente</span>' ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($datos['compras'])): ?><tr><td colspan="6" class="text-muted">Sin OC recibidas.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <button type="button" class="btn btn-sm btn-primary" onclick="generarOrigen('compras', false)">Pólizas de OC</button>
    </div>

    <div class="tab-pane fade" id="tab-nom">
      <p class="small">Asiento: Sueldos / Bancos (neto) + Acreedores (deducciones). Estatus Pagada o Parcial. No escribe <code>nominas.poliza_id</code>.</p>
      <div class="table-responsive">
        <table class="table table-sm table-striped">
          <thead><tr><th>Folio</th><th>Pago</th><th>Tipo</th><th class="text-end">Percepciones</th><th class="text-end">Neto</th><th>Póliza</th></tr></thead>
          <tbody>
          <?php foreach ($datos['nominas'] as $n): ?>
            <tr>
              <td><?= $h($n->folio) ?></td>
              <td><?= $h(date('d/m/Y', strtotime($n->fecha_pago))) ?></td>
              <td><?= $h($n->tipo_nomina) ?></td>
              <td class="text-end"><?= number_format((float)$n->total_percepciones, 2) ?></td>
              <td class="text-end"><?= number_format((float)$n->total_neto, 2) ?></td>
              <td><?= $n->ya_poliza ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">Pendiente</span>' ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($datos['nominas'])): ?><tr><td colspan="6" class="text-muted">Sin nóminas pagadas con neto &gt; 0.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <button type="button" class="btn btn-sm btn-primary" onclick="generarOrigen('nominas', false)">Pólizas de nómina</button>
    </div>

    <div class="tab-pane fade" id="tab-map">
      <p class="small">Catálogo mínimo SAT-estilo. Las cuentas que ya existían (Bancos, ISR, IMSS, sueldos) se reutilizan.</p>
      <table class="table table-sm">
        <thead><tr><th>Clave</th><th>Código</th><th>Cuenta</th></tr></thead>
        <tbody>
        <?php foreach ($datos['mapeo'] as $m): ?>
          <tr><td><?= $h($m->clave) ?></td><td><?= $h($m->codigo) ?></td><td><?= $h($m->nombre) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
function generarOrigen(origen, autorizar) {
  if (autorizar && !confirm('¿Autorizar las pólizas nuevas? Entrarán a balanza y balance.')) return;
  $.post('<?= base_url() ?>contabilidad/Origenes/generar_ajax', {
    origen: origen,
    autorizar: autorizar ? '1' : '0',
    peticion: 'ajax',
    '<?= $h($csrf_name) ?>': '<?= $h($csrf_hash) ?>'
  }, function (raw) {
    var r = (typeof raw === 'string') ? JSON.parse(raw) : raw;
    alert(r.message || (r.success ? 'Listo' : 'Error'));
    if (r.success) location.reload();
  }).fail(function () { alert('Error de red'); });
}
</script>
