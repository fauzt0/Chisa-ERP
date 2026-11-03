<?php $filas = $response['movimientos'] ?? []; ?>
<div class="container-fluid p-0">
  <div class="row mb-3">
    <div class="col">
      <h3><i class="fas fa-exchange-alt"></i> <?= htmlspecialchars($headTitle, ENT_QUOTES, 'UTF-8') ?></h3>
      <p class="text-muted mb-0">Entradas, salidas y ajustes de insumos y de producto terminado. Esta pantalla no modifica existencias.</p>
    </div>
  </div>
  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped table-sm mb-0" id="tablaMovimientosAlmacen">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Origen</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Cantidad</th>
            <th>Stock anterior</th>
            <th>Stock nuevo</th>
            <th>Motivo</th>
            <th>Referencia</th>
          </tr>
        </thead>
        <tbody>
        <?php if (!$filas): ?>
          <tr><td colspan="10" class="text-muted">No hay movimientos registrados.</td></tr>
        <?php else: foreach ($filas as $m): ?>
          <tr>
            <td><?= htmlspecialchars((string) $m->fecha_movimiento, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $m->origen, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($m->codigo ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($m->nombre ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $m->tipo_movimiento, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $m->cantidad, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($m->stock_anterior ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($m->stock_nuevo ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($m->motivo ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($m->referencia ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
$(function () {
  if ($.fn.DataTable && $('#tablaMovimientosAlmacen tbody tr td').length > 1) {
    $('#tablaMovimientosAlmacen').DataTable({
      pageLength: 25,
      order: [[0, 'desc']],
      language: { url: '<?= base_url('assets/dist/js/i18n/datatables-es.json') ?>' }
    });
  }
});
</script>
