<?php
$f = $response['filtros'] ?? [];
$filas = $response['filas'] ?? [];
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <h1 class="h3 mb-3"><?=htmlspecialchars($headTitle)?></h1>
  <form class="card mb-3" method="get">
    <div class="card-body row g-2">
      <div class="col-md-4">
        <label class="form-label">Folio, concepto o producto</label>
        <input class="form-control" name="q" value="<?=htmlspecialchars($f['q'] ?? '')?>" placeholder="CCO, OV, arena, aplicación">
      </div>
      <div class="col-md-3">
        <label class="form-label">Cliente o constructora</label>
        <input class="form-control" name="cliente" value="<?=htmlspecialchars($f['cliente'] ?? '')?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Desde</label>
        <input type="date" class="form-control" name="desde" value="<?=htmlspecialchars($f['desde'] ?? '')?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Hasta</label>
        <input type="date" class="form-control" name="hasta" value="<?=htmlspecialchars($f['hasta'] ?? '')?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Lugar</label>
        <input class="form-control" name="lugar" value="<?=htmlspecialchars($f['lugar'] ?? '')?>" placeholder="Ciudad, obra, dirección">
      </div>
      <div class="col-md-3">
        <label class="form-label">Documento</label>
        <select class="form-select" name="tipo">
          <?php foreach (['todos' => 'Todo', 'orden' => 'Órdenes de venta', 'cotizacion' => 'Cotizaciones de obra', 'obra' => 'Obras'] as $k => $etiq): ?>
          <option value="<?=$k?>" <?= ($f['tipo'] ?? '') === $k ? 'selected' : '' ?>><?=$etiq?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="aplicacion" value="1" id="soloApl" <?= !empty($f['solo_aplicacion']) ? 'checked' : '' ?>>
          <label class="form-check-label" for="soloApl">Solo con aplicaciones</label>
        </div>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Buscar</button>
      </div>
    </div>
  </form>
  <div class="card">
    <div class="table-responsive">
      <table class="table table-striped mb-0">
        <thead><tr><th>Tipo</th><th>Folio</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Monto</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($response['busco'])): ?>
          <tr><td colspan="7" class="text-muted">Escribe un cliente, un folio, un concepto o un lugar.</td></tr>
        <?php elseif (!$filas): ?>
          <tr><td colspan="7" class="text-muted">Sin resultados.</td></tr>
        <?php endif; ?>
        <?php foreach ($filas as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['tipo'])?></td>
            <td><strong><?=htmlspecialchars($r['folio'])?></strong></td>
            <td><?=htmlspecialchars($r['fecha'] ?: '')?></td>
            <td><?=htmlspecialchars($r['quien'] ?: '')?></td>
            <td><?=htmlspecialchars($r['detalle'] ?: '')?></td>
            <td><?= $r['monto'] !== null ? '$'.number_format((float)$r['monto'], 2) : '—' ?></td>
            <td><a class="btn btn-sm btn-outline-primary" href="<?=htmlspecialchars($r['url'])?>">Abrir</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
