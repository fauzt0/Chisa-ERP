<?php $viaticos = $viaticos ?? []; ?>
<div class="card mb-4" id="viaticosObra">
  <div class="card-header">
    <strong><i class="fas fa-receipt"></i> Viáticos y gastos de obra</strong>
  </div>
  <div class="card-body">
    <p class="text-muted small mb-2">
      Comidas, pasajes y gasolina se capturan en nómina y en el expediente de lista de raya.
      Los archivos clasificados como viáticos del entrenamiento quedan aquí para consulta.
    </p>
    <?php if (!$viaticos): ?>
      <p class="mb-2">No hay archivos de viáticos indexados todavía.</p>
    <?php else: ?>
      <ul class="mb-2">
        <?php foreach ($viaticos as $v): ?>
          <li><?= htmlspecialchars($v->nombre, ENT_QUOTES, 'UTF-8') ?>
            <?php if (!empty($v->semana)): ?>
              <span class="text-muted">· <?= htmlspecialchars($v->semana, ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
    <a class="btn btn-sm btn-outline-primary" href="<?= base_url('contabilidad/ExpedienteRaya') ?>">Abrir expediente de lista de raya</a>
  </div>
</div>
