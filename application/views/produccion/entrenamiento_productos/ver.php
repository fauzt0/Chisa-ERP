<?php
$p = $response['producto'];
$activa = $response['activa'] ?? null;
$comps = $response['componentes'] ?? [];
$hist = $response['historial'] ?? [];
$csrf_name = $this->security->get_csrf_token_name();
$csrf_hash = $this->security->get_csrf_hash();
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <a href="<?= base_url('produccion/EntrenamientoProductos') ?>" class="btn btn-sm btn-outline-secondary mb-2">Volver</a>
  <h1 class="h3"><?= htmlspecialchars($p->nombre, ENT_QUOTES, 'UTF-8') ?> <small class="text-muted"><?= htmlspecialchars($p->codigo, ENT_QUOTES, 'UTF-8') ?></small></h1>
  <?php if (!empty($response['aviso'])): ?>
    <div class="alert alert-<?= !empty($response['aviso_ok']) ? 'success' : 'danger' ?> py-2"><?= htmlspecialchars($response['aviso'], ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form class="card mb-3" method="post" action="<?= base_url('produccion/EntrenamientoProductos/guardar_ajax') ?>">
    <div class="card-body row g-2">
      <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="id" value="<?= (int) $p->id ?>">
      <div class="col-md-2">
        <label class="form-label">Estatus</label>
        <select class="form-select" name="estatus">
          <option value="Activo" <?= $p->estatus === 'Activo' ? 'selected' : '' ?>>Activo</option>
          <option value="Inactivo" <?= $p->estatus === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Unidad venta</label>
        <?php $uvs = ['Cubeta','Litro','Galon','Kg','Pieza','Caja','Metro','M2']; ?>
        <select class="form-select" name="unidad_venta">
          <?php foreach ($uvs as $u): ?>
            <option value="<?= $u ?>" <?= $p->unidad_venta === $u ? 'selected' : '' ?>><?= $u ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Precio venta</label>
        <input class="form-control" name="precio_venta" type="number" step="0.01" value="<?= htmlspecialchars((string) $p->precio_venta, ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Revisado</label>
        <select class="form-select" name="entrenamiento_revisado">
          <option value="0" <?= empty($p->entrenamiento_revisado) ? 'selected' : '' ?>>No</option>
          <option value="1" <?= !empty($p->entrenamiento_revisado) ? 'selected' : '' ?>>Sí</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Formulación principal verificada</label>
        <select class="form-select" name="entrenamiento_principal_ok">
          <option value="0" <?= empty($p->entrenamiento_principal_ok) ? 'selected' : '' ?>>Aún no</option>
          <option value="1" <?= !empty($p->entrenamiento_principal_ok) ? 'selected' : '' ?>>Verificada</option>
        </select>
      </div>
      <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100">Guardar</button></div>
    </div>
  </form>

  <?php if ($p->tipo_producto !== 'Fabricado'): ?>
  <form class="mb-3" method="post" action="<?= base_url('produccion/EntrenamientoProductos/materia_prima_ajax') ?>">
    <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="id" value="<?= (int) $p->id ?>">
    <button class="btn btn-outline-warning btn-sm">Pasar a materia prima (solo reventa)</button>
  </form>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-header">Formulación principal <?= $activa ? '(v' . (int) $activa->version . ')' : '(sin activa)' ?></div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>Insumo</th><th>Unidad</th><th>Cantidad</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($comps as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c->insumo_codigo . ' ' . $c->insumo_nombre, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $c->insumo_unidad_medida, ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <form method="post" action="<?= base_url('produccion/EntrenamientoProductos/componente_ajax') ?>" class="d-flex gap-1">
                <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="producto_id" value="<?= (int) $p->id ?>">
                <input type="hidden" name="detalle_id" value="<?= (int) $c->id ?>">
                <input class="form-control form-control-sm" name="cantidad" value="<?= htmlspecialchars((string) $c->cantidad, ENT_QUOTES, 'UTF-8') ?>">
                <button class="btn btn-sm btn-outline-primary">OK</button>
              </form>
            </td>
            <td></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if (empty($p->entrenamiento_principal_ok)): ?>
    <div class="alert alert-info">El historial de otras versiones se habilita cuando marques la formulación principal como verificada.</div>
  <?php else: ?>
    <div class="card">
      <div class="card-header">Historial (consulta; no se activa otra fórmula)</div>
      <ul class="list-group list-group-flush">
        <?php foreach ($hist as $h): ?>
          <li class="list-group-item">
            v<?= (int) $h->version ?> <?= !empty($h->es_activa) ? '<span class="badge bg-success">activa</span>' : '' ?>
            <?= htmlspecialchars((string) ($h->cliente_nombre ?: $h->referencia_cliente ?: ''), ENT_QUOTES, 'UTF-8') ?>
            <small class="text-muted"><?= htmlspecialchars((string) $h->fecha_creacion, ENT_QUOTES, 'UTF-8') ?></small>
            <?php if (empty($h->es_activa)):
              $hcomps = $this->ProductosModel->get_componentes_formulacion($h->id);
              foreach ($hcomps as $c): ?>
                <form method="post" action="<?= base_url('produccion/EntrenamientoProductos/historial_componente_ajax') ?>" class="d-flex gap-1 my-1">
                  <input type="hidden" name="<?= htmlspecialchars($csrf_name, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars($csrf_hash, ENT_QUOTES, 'UTF-8') ?>">
                  <input type="hidden" name="producto_id" value="<?= (int) $p->id ?>">
                  <input type="hidden" name="detalle_id" value="<?= (int) $c->id ?>">
                  <span class="small flex-grow-1"><?= htmlspecialchars($c->insumo_nombre, ENT_QUOTES, 'UTF-8') ?></span>
                  <input class="form-control form-control-sm" style="max-width:90px" name="cantidad" value="<?= htmlspecialchars((string) $c->cantidad, ENT_QUOTES, 'UTF-8') ?>">
                  <button class="btn btn-sm btn-outline-secondary">Ajustar</button>
                </form>
              <?php endforeach; endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</div>
