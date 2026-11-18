<?php
$busqueda          = $response['busqueda']            ?? '';
$filas             = $response['filas']               ?? [];
$total             = (int) ($response['total']        ?? 0);
$proveedores_act   = $response['proveedores_activos'] ?? [];
$csrf_name         = $this->security->get_csrf_token_name();
$csrf_hash         = $this->security->get_csrf_hash();
?>
<div class="container-fluid p-0 compras-page">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

  <h1 class="h3 mb-2"><?= htmlspecialchars($headTitle, ENT_QUOTES, 'UTF-8') ?></h1>
  <p class="text-muted mb-3 small">
    Consulta y edición ligera: estatus, alias, precio de proveedor y datos de producto.
    El tipo de insumo (comprado/fabricado/semielaborado) y el stock solo se modifican desde Insumos.
  </p>

  <div class="alert alert-warning py-2 small mb-3">
    Precio de venta del producto = precio de lista al cliente, no el costo de compra del insumo.
    El lápiz abre la ficha (unidad, revisado, producto fabricado) y no borra el insumo.
  </div>

  <?php $fotos_mp = $response['fotos_mp'] ?? []; ?>
  <?php if (!empty($fotos_mp)): ?>
  <div class="card mb-3">
    <div class="card-header">Fotos de materia prima del proveedor (<?= count($fotos_mp) ?>). Referencia visual; aún no están ligadas a un insumo en base de datos.</div>
    <div class="card-body">
      <div class="row g-2">
        <?php foreach ($fotos_mp as $foto): ?>
        <div class="col-6 col-md-3 col-lg-2">
          <a href="<?= base_url('compras/EntrenamientoMateriales/foto_mp?f=' . rawurlencode($foto)) ?>" target="_blank" rel="noopener">
            <img src="<?= base_url('compras/EntrenamientoMateriales/foto_mp?f=' . rawurlencode($foto)) ?>"
                 alt="" class="img-fluid rounded border" style="height:110px;width:100%;object-fit:cover;">
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Búsqueda -->
  <?php $revisado = $response['revisado'] ?? ''; ?>
  <form class="card mb-3" method="get" action="<?= base_url('compras/EntrenamientoMateriales') ?>">
    <div class="card-body row g-2 align-items-end">
      <div class="col-md-5">
        <label class="form-label" for="busqueda">Buscar por nombre técnico, alias o código</label>
        <input type="search" class="form-control" id="busqueda" name="busqueda"
               value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>"
               placeholder="BERMOCOLL, Agua desmineralizada, INS-001…">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="revisado">Semáforo</label>
        <select class="form-select" id="revisado" name="revisado">
          <option value="">Todos</option>
          <option value="0" <?= $revisado === '0' ? 'selected' : '' ?>>No revisado (rojo)</option>
          <option value="1" <?= $revisado === '1' ? 'selected' : '' ?>>Revisado (verde)</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Buscar</button>
      </div>
      <div class="col-md-2">
        <a class="btn btn-outline-secondary w-100" href="<?= base_url('compras/EntrenamientoMateriales') ?>">Ver todos</a>
      </div>
    </div>
  </form>

  <!-- Tabla -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Materiales</span>
      <span class="text-muted small"><?= $total ?> resultado(s)</span>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-sm mb-0" id="tablaEM">
        <thead class="table-light">
          <tr>
            <th>Rev.</th>
            <th>Código</th>
            <th>Nombre técnico</th>
            <th>Alias interno</th>
            <th>Aliases catálogo</th>
            <th>Estatus</th>
            <th>Categoría</th>
            <th>Tipo</th>
            <th>Clase uso</th>
            <th>Proveedores (precio compra)</th>
            <th>Último precio hist.</th>
            <th>Producto enlazado</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($filas)): ?>
          <tr><td colspan="13" class="text-muted py-3">Sin resultados.</td></tr>
        <?php else: ?>
          <?php foreach ($filas as $f): ?>
          <tr>
            <td class="text-center">
              <?php if (!empty($f['entrenamiento_revisado'])): ?>
                <span class="badge rounded-pill bg-success" title="Revisado">●</span>
              <?php else: ?>
                <span class="badge rounded-pill bg-danger" title="No revisado">●</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($f['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($f['nombre_tecnico'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= $f['alias_interno'] !== '' ? htmlspecialchars($f['alias_interno'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
            <td class="small">
              <?php if (!empty($f['aliases'])):
                $txt = array_map(function($a) { return htmlspecialchars($a->alias, ENT_QUOTES, 'UTF-8') . ' <em class="text-muted">('.$a->tipo.')</em>'; }, $f['aliases']);
                echo implode('<br>', $txt);
              else: echo '—'; endif; ?>
            </td>
            <td>
              <?php $ecls = $f['estatus'] === 'Activo' ? 'success' : ($f['estatus'] === 'Descontinuado' ? 'secondary' : 'warning'); ?>
              <span class="badge bg-<?= $ecls ?>"><?= htmlspecialchars($f['estatus'], ENT_QUOTES, 'UTF-8') ?></span>
            </td>
            <td><?= $f['categoria'] !== '' ? htmlspecialchars($f['categoria'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
            <td><?= htmlspecialchars($f['tipo'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <?php $cu = $f['clase_uso']; ?>
              <?= $cu !== '' ? '<span class="badge bg-info text-dark">'.htmlspecialchars($cu, ENT_QUOTES, 'UTF-8').'</span>' : '—' ?>
            </td>
            <td class="small">
              <?php if (!empty($f['proveedores'])): ?>
                <ul class="list-unstyled mb-0"><?php foreach ($f['proveedores'] as $pt): ?><li><?= $pt ?></li><?php endforeach; ?></ul>
              <?php else: ?><span class="text-muted">Sin proveedores</span><?php endif; ?>
            </td>
            <td class="small">
              <?php if (!empty($f['historial_precio'])):
                $h = $f['historial_precio']; ?>
                $<?= number_format((float) $h->precio, 2) ?>
                <?php if (!empty($h->fecha)): ?><br><small class="text-muted"><?= htmlspecialchars($h->fecha, ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
              <?php else: ?><span class="text-muted">sin historial</span><?php endif; ?>
            </td>
            <td class="small">
              <?php if (!empty($f['producto'])):
                $p = $f['producto']; ?>
                <strong>UV:</strong> <?= htmlspecialchars((string)($p->unidad_venta ?? '—'), ENT_QUOTES, 'UTF-8') ?><br>
                <strong>Pres.:</strong> <?= htmlspecialchars((string)($p->presentacion_principal ?? '—'), ENT_QUOTES, 'UTF-8') ?><br>
                <strong>Cont.:</strong> <?= $p->contenido_neto !== null ? htmlspecialchars((string)$p->contenido_neto, ENT_QUOTES, 'UTF-8') : '—' ?><br>
                <strong>Rend.:</strong> <?= $p->rendimiento !== null && $p->rendimiento !== '' ? htmlspecialchars((string)$p->rendimiento, ENT_QUOTES, 'UTF-8') : '—' ?><br>
                <strong class="text-muted">PV (lista):</strong> $<?= number_format((float)($p->precio_venta ?? 0), 2) ?>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <a class="btn btn-sm btn-outline-primary"
                 href="<?= base_url('compras/EntrenamientoMateriales/editar/' . (int)$f['id']) ?>"
                 title="Abrir ficha">
                <i class="fas fa-pencil-alt"></i>
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
$(function () {
  if ($.fn.DataTable && $('#tablaEM tbody tr td').length > 1) {
    $('#tablaEM').DataTable({
      pageLength: 25,
      order: [[1, 'asc']],
      columnDefs: [{ orderable: false, targets: -1 }],
      language: { url: '/assets/dist/js/i18n/datatables-es.json' }
    });
  }
});
</script>
