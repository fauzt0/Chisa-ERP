<?php
$cabecera    = $response['cabecera'] ?? null;
$componentes = $response['componentes'] ?? [];
$archivo     = $response['archivo'] ?? null;
$clientes    = $response['clientes'] ?? [];
$aviso       = $response['aviso'] ?? '';
$aviso_ok    = !empty($response['aviso_ok']);
if (!$cabecera) {
    echo '<p class="text-danger">Formulación no encontrada.</p>';
    return;
}
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

  <?php if ($aviso !== '' && $aviso !== null): ?>
    <div class="alert alert-<?= $aviso_ok ? 'success' : 'danger' ?> py-2"><?= htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><?= htmlspecialchars($headTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    <a href="<?= base_url('produccion/HistorialFormulaciones') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Volver al historial
    </a>
  </div>

  <div class="card mb-3">
    <div class="card-header"><strong>Datos de la versión</strong> <span class="text-muted">(solo lectura)</span></div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <small class="text-muted d-block">ID formulación</small>
          <strong><?= (int) $cabecera->id ?></strong>
        </div>
        <div class="col-md-5">
          <small class="text-muted d-block">Producto</small>
          <strong><?= htmlspecialchars(trim(($cabecera->producto_codigo ?? '') . ' — ' . ($cabecera->producto_nombre ?? '')), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="col-md-2">
          <small class="text-muted d-block">Versión</small>
          <strong>v<?= (int) $cabecera->version ?></strong>
        </div>
        <div class="col-md-2">
          <small class="text-muted d-block">¿Activa?</small>
          <?php if (!empty($cabecera->es_activa)): ?>
            <span class="badge bg-success">Sí (es_activa=1)</span>
          <?php else: ?>
            <span class="badge bg-secondary">No</span>
          <?php endif; ?>
        </div>
        <div class="col-md-3">
          <small class="text-muted d-block">Rendimiento m²/kg</small>
          <?= $cabecera->rendimiento_m2_por_kg !== null
            ? htmlspecialchars((string) $cabecera->rendimiento_m2_por_kg, ENT_QUOTES, 'UTF-8')
            : '—' ?>
        </div>
        <div class="col-md-3">
          <small class="text-muted d-block">Fecha creación</small>
          <?= htmlspecialchars($cabecera->fecha_creacion ?? '—', ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div class="col-12">
          <small class="text-muted d-block">Cliente asociado</small>
          <?= htmlspecialchars($cabecera->cliente_nombre ?? $cabecera->cliente_nombre_comercial ?? 'Sin cliente', ENT_QUOTES, 'UTF-8') ?>
          <form class="row g-2 align-items-end mt-2" method="post"
                action="<?= base_url('produccion/HistorialFormulaciones/asociar_cliente') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>"
                   value="<?= $this->security->get_csrf_hash() ?>">
            <input type="hidden" name="formulacion_id" value="<?= (int) $cabecera->id ?>">
            <div class="col-md-6">
              <label class="form-label" for="cliente_asociar">Asociar un cliente existente</label>
              <select class="form-select form-select-sm" name="cliente_id" id="cliente_asociar">
                <option value="">— Quitar asociación —</option>
                <?php foreach ($clientes as $cl):
                  $etiq = $cl->razon_social ?: $cl->nombre_comercial;
                ?>
                  <option value="<?= (int) $cl->id ?>" <?= (int)($cabecera->cliente_id ?? 0) === (int)$cl->id ? 'selected' : '' ?>>
                    <?= htmlspecialchars($etiq, ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <button type="submit" class="btn btn-primary btn-sm">Guardar cliente</button>
            </div>
          </form>
        </div>
        <?php if ($archivo): ?>
        <div class="col-12">
          <small class="text-muted d-block">Origen (desde comentarios)</small>
          <code><?= htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8') ?></code>
        </div>
        <?php endif; ?>
        <div class="col-12">
          <small class="text-muted d-block">Comentarios</small>
          <?= $cabecera->comentarios
            ? nl2br(htmlspecialchars($cabecera->comentarios, ENT_QUOTES, 'UTF-8'))
            : '<span class="text-muted">—</span>' ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><strong>Componentes (BOM)</strong> <span class="text-muted">— solo consulta</span></div>
    <div class="table-responsive">
      <table class="table table-sm table-striped mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Tipo</th>
            <th>Código</th>
            <th>Componente</th>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>%</th>
            <th>Grupo color</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($componentes)): ?>
          <tr><td colspan="8" class="text-muted">Sin componentes registrados.</td></tr>
        <?php else: ?>
          <?php foreach ($componentes as $i => $c):
            $es_insumo = ($c->tipo_componente ?? '') === 'Insumo';
            $codigo = $es_insumo ? ($c->insumo_codigo ?? '') : ($c->producto_codigo ?? '');
            $nombre = $es_insumo ? ($c->insumo_nombre ?? '') : ($c->producto_nombre ?? '');
          ?>
          <tr>
            <td><?= (int) ($c->orden ?? ($i + 1)) ?></td>
            <td><?= htmlspecialchars($c->tipo_componente ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $c->cantidad, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($c->unidad ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= $c->porcentaje !== null ? htmlspecialchars((string) $c->porcentaje, ENT_QUOTES, 'UTF-8') : '—' ?></td>
            <td><?= htmlspecialchars($c->grupo_color ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
