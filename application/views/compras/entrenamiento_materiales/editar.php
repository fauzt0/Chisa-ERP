<?php
$f = $response['fila'] ?? null;
$aviso = $response['aviso'] ?? '';
$aviso_ok = !empty($response['aviso_ok']);
if (!$f) {
    echo '<p class="text-danger">Material no encontrado.</p>';
    return;
}
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><?= htmlspecialchars($headTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('compras/EntrenamientoMateriales?busqueda=' . rawurlencode($f['codigo'])) ?>">Volver al listado</a>
  </div>
  <?php if ($aviso): ?>
    <div class="alert alert-<?= $aviso_ok ? 'success' : 'danger' ?> py-2"><?= htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <div class="alert alert-warning py-2 small">El precio de venta del producto es de lista al cliente. No es el costo de compra. El tipo comprado/fabricado no se cambia aquí.</div>
  <form class="card" method="post" action="<?= base_url('compras/EntrenamientoMateriales/guardar_insumo_ajax') ?>">
    <div class="card-body">
      <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
      <input type="hidden" name="volver" value="1">
      <div class="row g-2 mb-3">
        <div class="col-md-3">
          <label class="form-label">Código</label>
          <input class="form-control" value="<?= htmlspecialchars($f['codigo'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        </div>
        <div class="col-md-6">
          <label class="form-label">Nombre técnico</label>
          <input class="form-control" value="<?= htmlspecialchars($f['nombre_tecnico'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        </div>
        <div class="col-md-3">
          <label class="form-label">Tipo (solo lectura)</label>
          <input class="form-control" value="<?= htmlspecialchars($f['tipo'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        </div>
        <div class="col-md-4">
          <label class="form-label">Estatus</label>
          <select class="form-select" name="estatus" <?= $f['estatus'] === 'Descontinuado' ? 'disabled' : '' ?>>
            <option value="Activo" <?= $f['estatus'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
            <option value="Inactivo" <?= $f['estatus'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Clase de uso</label>
          <select class="form-select" name="clase_uso">
            <option value="">Sin clasificar</option>
            <option value="materia_prima" <?= $f['clase_uso'] === 'materia_prima' ? 'selected' : '' ?>>Materia prima</option>
            <option value="insumo" <?= $f['clase_uso'] === 'insumo' ? 'selected' : '' ?>>Insumo</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Alias interno</label>
          <input class="form-control" name="alias_interno" value="<?= htmlspecialchars($f['alias_interno'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <button class="btn btn-primary" type="submit">Guardar datos generales</button>
    </div>
  </form>
  <?php if (!empty($f['proveedores'])): ?>
  <div class="card mt-3">
    <div class="card-header">Precio de compra del proveedor (solo lectura en esta ficha)</div>
    <ul class="list-group list-group-flush">
      <?php foreach ($f['proveedores'] as $pt): ?><li class="list-group-item"><?= $pt ?></li><?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>
