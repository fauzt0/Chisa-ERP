<?php
$f = $response['fila'] ?? null;
$aviso = $response['aviso'] ?? '';
$aviso_ok = !empty($response['aviso_ok']);
$proveedores_act = $response['proveedores_activos'] ?? [];
if (!$f) {
    echo '<p class="text-danger">Material no encontrado.</p>';
    return;
}
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
$ums = ['Kg','g','mg','L','mL','Pza','Cubeta','Tambo','Galón','m²','m³','Ton','Servicio','Otro'];
$prod = $f['producto'] ?? null;
?>
<div class="container-fluid p-0 em-ficha">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
    <h1 class="h3 mb-0"><?= htmlspecialchars($headTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('compras/EntrenamientoMateriales?busqueda=' . rawurlencode($f['codigo'])) ?>">Volver al listado</a>
  </div>
  <?php if ($aviso): ?>
    <div class="alert alert-<?= $aviso_ok ? 'success' : 'danger' ?> py-2"><?= htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <div class="alert alert-warning py-2 small">El precio de venta del producto es de lista al cliente. No es el costo de compra. Marcar fabricado enlaza un SKU y no borra el insumo.</div>

  <form class="card mb-3" method="post" action="<?= base_url('compras/EntrenamientoMateriales/guardar_insumo_ajax') ?>">
    <div class="card-body">
      <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="id" value="<?= (int) $f['id'] ?>">
      <input type="hidden" name="volver" value="1">
      <div class="row g-2 mb-3">
        <div class="col-12 col-md-3">
          <label class="form-label">Código</label>
          <input class="form-control" value="<?= htmlspecialchars($f['codigo'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Nombre técnico</label>
          <input class="form-control" value="<?= htmlspecialchars($f['nombre_tecnico'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Tipo (solo lectura)</label>
          <input class="form-control" value="<?= htmlspecialchars($f['tipo'], ENT_QUOTES, 'UTF-8') ?>" readonly>
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label">Estatus</label>
          <select class="form-select" name="estatus" <?= $f['estatus'] === 'Descontinuado' ? 'disabled' : '' ?>>
            <option value="Activo" <?= $f['estatus'] === 'Activo' ? 'selected' : '' ?>>Activo</option>
            <option value="Inactivo" <?= $f['estatus'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
          </select>
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label">Clase de uso</label>
          <select class="form-select" name="clase_uso">
            <option value="">Sin clasificar</option>
            <option value="materia_prima" <?= $f['clase_uso'] === 'materia_prima' ? 'selected' : '' ?>>Materia prima</option>
            <option value="insumo" <?= $f['clase_uso'] === 'insumo' ? 'selected' : '' ?>>Insumo</option>
          </select>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Alias interno</label>
          <input class="form-control" name="alias_interno" value="<?= htmlspecialchars($f['alias_interno'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label">Unidad</label>
          <select class="form-select" name="unidad_medida">
            <?php foreach ($ums as $u): ?>
              <option value="<?= $u ?>" <?= ($f['unidad_medida'] ?? '') === $u ? 'selected' : '' ?>><?= $u ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-4">
          <label class="form-label">Revisado</label>
          <select class="form-select" name="entrenamiento_revisado">
            <option value="0" <?= empty($f['entrenamiento_revisado']) ? 'selected' : '' ?>>No revisado</option>
            <option value="1" <?= !empty($f['entrenamiento_revisado']) ? 'selected' : '' ?>>Revisado</option>
          </select>
        </div>
      </div>
      <button class="btn btn-primary" type="submit">Guardar datos generales</button>
    </div>
  </form>

  <form class="mb-3" method="post" action="<?= base_url('compras/EntrenamientoMateriales/marcar_producto_ajax') ?>">
    <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="insumo_id" value="<?= (int) $f['id'] ?>">
    <input type="hidden" name="volver" value="1">
    <button class="btn btn-outline-info" type="submit">Marcar como producto fabricado (enlaza, no borra el insumo)</button>
    <span class="small text-muted d-block d-md-inline mt-1">No cambia el tipo comprado/fabricado del insumo.</span>
  </form>

  <div class="card mb-3">
    <div class="card-header">Aliases adicionales</div>
    <div class="card-body">
      <?php if (!empty($f['aliases'])): ?>
        <ul class="small mb-3">
          <?php foreach ($f['aliases'] as $a): ?>
            <li><?= htmlspecialchars($a->alias, ENT_QUOTES, 'UTF-8') ?> <em class="text-muted">(<?= htmlspecialchars($a->tipo, ENT_QUOTES, 'UTF-8') ?>)</em></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-muted small">Sin aliases adicionales.</p>
      <?php endif; ?>
      <form class="row g-2 align-items-end" method="post" action="<?= base_url('compras/EntrenamientoMateriales/guardar_alias_ajax') ?>">
        <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="insumo_id" value="<?= (int) $f['id'] ?>">
        <input type="hidden" name="volver" value="1">
        <div class="col-12 col-md-3">
          <label class="form-label">Tipo</label>
          <select class="form-select" name="tipo">
            <option value="Genérico">Genérico</option>
            <option value="Proveedor">Proveedor</option>
            <option value="Comercial">Comercial</option>
            <option value="Técnico">Técnico</option>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Proveedor (si tipo Proveedor)</label>
          <select class="form-select" name="proveedor_id">
            <option value="">—</option>
            <?php foreach ($proveedores_act as $pv): ?>
              <option value="<?= (int) $pv->id ?>"><?= htmlspecialchars($pv->razon_social, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Alias</label>
          <input class="form-control" name="alias" required>
        </div>
        <div class="col-12 col-md-2">
          <button class="btn btn-success w-100" type="submit">Agregar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Precio de compra por proveedor</div>
    <div class="card-body">
      <?php if (!empty($f['proveedores_raw'])): ?>
        <div class="table-responsive mb-3">
          <table class="table table-sm">
            <thead><tr><th>Proveedor</th><th>Precio</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($f['proveedores_raw'] as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p->razon_social ?: ($p->nombre_comercial ?: $p->prov_codigo), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <form class="d-flex gap-1" method="post" action="<?= base_url('compras/EntrenamientoMateriales/guardar_proveedor_precio_ajax') ?>">
                    <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="insumo_id" value="<?= (int) $f['id'] ?>">
                    <input type="hidden" name="proveedor_id" value="<?= (int) $p->proveedor_id ?>">
                    <input type="hidden" name="volver" value="1">
                    <input class="form-control form-control-sm" type="number" min="0" step="0.01" name="precio_compra" value="<?= htmlspecialchars((string) $p->precio_compra, ENT_QUOTES, 'UTF-8') ?>">
                    <button class="btn btn-sm btn-outline-success" type="submit">OK</button>
                  </form>
                </td>
                <td></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      <form class="row g-2 align-items-end" method="post" action="<?= base_url('compras/EntrenamientoMateriales/guardar_proveedor_precio_ajax') ?>">
        <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="insumo_id" value="<?= (int) $f['id'] ?>">
        <input type="hidden" name="volver" value="1">
        <div class="col-12 col-md-6">
          <label class="form-label">Proveedor</label>
          <select class="form-select" name="proveedor_id" required>
            <option value="">— Seleccionar —</option>
            <?php foreach ($proveedores_act as $pv): ?>
              <option value="<?= (int) $pv->id ?>"><?= htmlspecialchars($pv->razon_social, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-8 col-md-4">
          <label class="form-label">Precio compra ($)</label>
          <input class="form-control" type="number" min="0" step="0.01" name="precio_compra" value="0">
        </div>
        <div class="col-4 col-md-2">
          <button class="btn btn-success w-100" type="submit">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <?php if ($prod): ?>
  <form class="card mb-3" method="post" action="<?= base_url('compras/EntrenamientoMateriales/guardar_producto_ajax') ?>">
    <div class="card-header">Producto terminado enlazado (precio de lista, no costo)</div>
    <div class="card-body">
      <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="insumo_id" value="<?= (int) $f['id'] ?>">
      <input type="hidden" name="volver" value="1">
      <div class="row g-2">
        <div class="col-6 col-md-3">
          <label class="form-label">Unidad de venta</label>
          <?php $uvs = ['Cubeta','Litro','Galon','Kg','Pieza','Caja','Metro','M2']; ?>
          <select class="form-select" name="unidad_venta">
            <?php foreach ($uvs as $u): ?>
              <option value="<?= $u ?>" <?= (($prod->unidad_venta ?? '') === $u) ? 'selected' : '' ?>><?= $u ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Presentación</label>
          <input class="form-control" name="presentacion_principal" value="<?= htmlspecialchars((string) ($prod->presentacion_principal ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Contenido neto (kg)</label>
          <input class="form-control" type="number" step="0.01" min="0" name="contenido_neto" value="<?= htmlspecialchars((string) ($prod->contenido_neto ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Precio venta lista</label>
          <input class="form-control" type="number" step="0.01" min="0" name="precio_venta" value="<?= htmlspecialchars((string) ($prod->precio_venta ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Rendimiento</label>
          <input class="form-control" name="rendimiento" value="<?= htmlspecialchars((string) ($prod->rendimiento ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <button class="btn btn-primary mt-3" type="submit">Guardar producto</button>
    </div>
  </form>
  <?php endif; ?>
</div>
