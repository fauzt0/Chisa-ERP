<?php
$cartera = $response['cartera'] ?? [];
$cartera_resumen = $response['cartera_resumen'] ?? ['documentos' => 0, 'saldo' => 0, 'criticos' => 0];
if (empty($cartera) && (int) ($cartera_resumen['documentos'] ?? 0) === 0) {
    return;
}
?>
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-danger">
      <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Cartera por cobrar</h5>
        <span>
          <?= (int) $cartera_resumen['documentos'] ?> documento(s)
          · saldo $<?= number_format((float) $cartera_resumen['saldo'], 2) ?>
          <?php if ((int) $cartera_resumen['criticos'] > 0): ?>
            · <strong><?= (int) $cartera_resumen['criticos'] ?> con ≥7 días</strong>
          <?php endif; ?>
        </span>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Tipo</th>
                <th>Folio</th>
                <th>Cliente</th>
                <th>Contacto</th>
                <th class="text-end">Saldo</th>
                <th>Pago</th>
                <th>Antigüedad</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($cartera as $c): ?>
              <tr class="<?= $c->severidad === 'danger' ? 'table-danger' : ($c->severidad === 'warning' ? 'table-warning' : '') ?>">
                <td><?= $c->tipo === 'obra' ? 'Obra' : 'Venta' ?></td>
                <td><strong><?= htmlspecialchars($c->folio, ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td>
                  <?= htmlspecialchars($c->cliente, ENT_QUOTES, 'UTF-8') ?>
                  <?php if (!empty($c->nombre_comercial)): ?>
                    <br><small class="text-muted"><?= htmlspecialchars($c->nombre_comercial, ENT_QUOTES, 'UTF-8') ?></small>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($c->telefono ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-end text-danger fw-bold">$<?= number_format((float) $c->saldo, 2) ?></td>
                <td><span class="badge bg-<?= $c->severidad ?>"><?= htmlspecialchars($c->estatus_pago, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= (int) $c->dias ?> d</td>
                <td>
                  <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($c->link, ENT_QUOTES, 'UTF-8') ?>">Cobrar</a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
