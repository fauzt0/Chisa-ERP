<?php
$filas = $response['filas'] ?? [];
$busqueda = $response['busqueda'] ?? '';
$revisado = $response['revisado'] ?? '';
$hueco = $response['hueco'] ?? '';
$huecos = $response['huecos'] ?? [];
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <h1 class="h3 mb-2"><?= htmlspecialchars($headTitle, ENT_QUOTES, 'UTF-8') ?></h1>
  <p class="small text-muted">Pantalla temporal de superadmin. Flujo: revisar fórmula principal → marcar verificada → historial operativo. Completar lote sigue exigiendo pesaje en Lotes. No activa otra fórmula al consultar.</p>
  <?php if (!empty($huecos)): ?>
  <p class="small mb-2">
    Fabricados <?= (int)($huecos['fabricados'] ?? 0) ?> ·
    sin fórmula <?= (int)($huecos['sin_formula'] ?? 0) ?> ·
    con versiones pero sin activa <?= (int)($huecos['sin_activa'] ?? 0) ?> ·
    fórmulas con cliente <?= (int)($huecos['form_con_cliente'] ?? 0) ?>/<?= (int)($huecos['form_total'] ?? 0) ?>
  </p>
  <?php endif; ?>
  <form class="card mb-3" method="get">
    <div class="card-body row g-2 align-items-end">
      <div class="col-md-4">
        <input class="form-control" name="busqueda" value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>" placeholder="Código o nombre">
      </div>
      <div class="col-md-2">
        <select class="form-select" name="revisado">
          <option value="">Todos</option>
          <option value="0" <?= $revisado === '0' ? 'selected' : '' ?>>No revisado</option>
          <option value="1" <?= $revisado === '1' ? 'selected' : '' ?>>Revisado</option>
        </select>
      </div>
      <div class="col-md-3">
        <select class="form-select" name="hueco">
          <option value="">Fórmula: todas</option>
          <option value="sin_formula" <?= $hueco === 'sin_formula' ? 'selected' : '' ?>>Sin fórmula</option>
          <option value="sin_activa" <?= $hueco === 'sin_activa' ? 'selected' : '' ?>>Sin activa</option>
        </select>
      </div>
      <div class="col-md-3"><button class="btn btn-primary w-100">Filtrar</button></div>
    </div>
  </form>
  <div class="card">
    <div class="table-responsive">
      <table class="table table-sm table-striped mb-0" id="tablaEP">
        <thead><tr><th>Rev.</th><th>Código</th><th>Nombre</th><th>Tipo</th><th>Estatus</th><th>Fórmulas</th><th>Activa</th><th>Principal OK</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($filas as $f): ?>
          <tr>
            <td><?= !empty($f->entrenamiento_revisado) ? '<span class="badge bg-success">●</span>' : '<span class="badge bg-danger">●</span>' ?></td>
            <td><?= htmlspecialchars($f->codigo, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($f->nombre, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($f->tipo_producto, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($f->estatus, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int) ($f->n_formulaciones ?? 0) ?></td>
            <td><?= !empty($f->n_activas) ? 'Sí' : 'No' ?></td>
            <td><?= !empty($f->entrenamiento_principal_ok) ? 'Sí' : 'No' ?></td>
            <td><a class="btn btn-sm btn-outline-primary" href="<?= base_url('produccion/EntrenamientoProductos/ver/' . (int) $f->id) ?>">Abrir</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
$(function () {
  if ($.fn.DataTable && $('#tablaEP tbody tr').length) {
    $('#tablaEP').DataTable({
      pageLength: 25,
      order: [[2, 'asc']],
      columnDefs: [{ orderable: false, targets: -1 }],
      language: { url: '/assets/dist/js/i18n/datatables-es.json' }
    });
  }
});
</script>
