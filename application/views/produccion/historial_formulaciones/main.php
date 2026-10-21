<?php
$filtros   = $response['filtros'] ?? [];
$filas     = $response['filas'] ?? [];
$productos = $response['productos'] ?? [];
$clientes  = $response['clientes'] ?? [];
$cliente_sel = null;
foreach ($clientes as $cl) {
    if ((int) ($filtros['cliente_id'] ?? 0) === (int) $cl->id) {
        $cliente_sel = $cl;
        break;
    }
}
$nombre_cliente = $cliente_sel
    ? ($cliente_sel->razon_social ?: $cliente_sel->nombre_comercial)
    : '';

function em_extraer_archivo($comentarios) {
    $comentarios = (string) $comentarios;
    if ($comentarios === '') {
        return null;
    }
    $pos = stripos($comentarios, 'Archivo:');
    if ($pos === false) {
        return null;
    }
    return trim(substr($comentarios, $pos));
}
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

  <h1 class="h3 mb-2"><?= htmlspecialchars($headTitle, ENT_QUOTES, 'UTF-8') ?></h1>
  <p class="text-muted small mb-3">Consulta de versiones de formulación. No se editan cantidades ni se activan versiones desde aquí.</p>

  <form class="card mb-3" method="get" action="<?= base_url('produccion/HistorialFormulaciones') ?>">
    <input type="hidden" name="filtrar" value="1">
    <div class="card-body row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label" for="producto_id">Producto</label>
        <select class="form-select" name="producto_id" id="producto_id">
          <option value="">— Todos —</option>
          <?php foreach ($productos as $p): ?>
            <option value="<?= (int) $p->id ?>" <?= (int)($filtros['producto_id'] ?? 0) === (int)$p->id ? 'selected' : '' ?>>
              <?= htmlspecialchars($p->codigo . ' — ' . $p->nombre, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="cliente_id">Cliente</label>
        <select class="form-select" name="cliente_id" id="cliente_id">
          <option value="">— Todos los clientes —</option>
          <?php foreach ($clientes as $cl):
            $etiq = $cl->razon_social ?: $cl->nombre_comercial;
          ?>
            <option value="<?= (int) $cl->id ?>" <?= (int)($filtros['cliente_id'] ?? 0) === (int)$cl->id ? 'selected' : '' ?>>
              <?= htmlspecialchars($etiq, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label" for="texto_comentarios">Texto en comentarios</label>
        <input type="search" class="form-control" name="texto_comentarios" id="texto_comentarios"
               value="<?= htmlspecialchars($filtros['texto_comentarios'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="Ej. Archivo:, Importado…">
      </div>
      <div class="col-md-2">
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="solo_activas" value="1" id="solo_activas"
                 <?= !empty($filtros['solo_activas']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="solo_activas">Solo activas</label>
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filtrar</button>
      </div>
    </div>
  </form>

  <?php if ($cliente_sel): ?>
  <div class="alert alert-secondary py-2 small">
    Historial de <strong><?= htmlspecialchars($nombre_cliente, ENT_QUOTES, 'UTF-8') ?></strong>:
    versiones ya asociadas y el producto de cada una.
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header d-flex justify-content-between">
      <span><?= $cliente_sel ? 'Versiones del cliente' : 'Versiones' ?></span>
      <span class="text-muted small"><?= count($filas) ?> registro(s)</span>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-sm mb-0" id="tablaHistorialFormulaciones">
        <thead class="table-light">
          <tr>
            <th>Producto</th>
            <th>Versión</th>
            <th>Activa</th>
            <th>Rendimiento m²/kg</th>
            <th>Comentarios</th>
            <th>Origen (archivo)</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($filas)): ?>
          <tr><td colspan="7" class="text-muted py-3">No hay formulaciones con los filtros actuales.</td></tr>
        <?php else: ?>
          <?php foreach ($filas as $row):
            $archivo = em_extraer_archivo($row->comentarios ?? '');
            $prod = trim(($row->producto_codigo ?? '') . ' ' . ($row->producto_nombre ?? ''));
          ?>
          <tr>
            <td><?= htmlspecialchars($prod, ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <strong>v<?= (int) $row->version ?></strong>
              <?php if (!empty($row->nombre_version)): ?>
                <br><small class="text-muted"><?= htmlspecialchars($row->nombre_version, ENT_QUOTES, 'UTF-8') ?></small>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($row->es_activa)): ?>
                <span class="badge bg-success">Sí</span>
              <?php else: ?>
                <span class="badge bg-secondary">No</span>
              <?php endif; ?>
            </td>
            <td>
              <?= $row->rendimiento_m2_por_kg !== null && $row->rendimiento_m2_por_kg !== ''
                ? htmlspecialchars((string) $row->rendimiento_m2_por_kg, ENT_QUOTES, 'UTF-8')
                : '—' ?>
            </td>
            <td class="small" style="max-width:280px">
              <?= $row->comentarios
                ? nl2br(htmlspecialchars($row->comentarios, ENT_QUOTES, 'UTF-8'))
                : '<span class="text-muted">—</span>' ?>
            </td>
            <td class="small">
              <?= $archivo
                ? htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8')
                : '<span class="text-muted">—</span>' ?>
            </td>
            <td>
              <a class="btn btn-sm btn-outline-primary"
                 href="<?= base_url('produccion/HistorialFormulaciones/ver/' . (int) $row->id) ?>">
                Ver
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
  if ($.fn.DataTable && $('#tablaHistorialFormulaciones tbody tr td').length > 1) {
    $('#tablaHistorialFormulaciones').DataTable({
      pageLength: 25,
      order: [[0, 'asc']],
      columnDefs: [{ orderable: false, targets: -1 }],
      language: { url: 'https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' }
    });
  }
});
</script>
