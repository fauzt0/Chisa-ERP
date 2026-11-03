<?php
$conteos = $response['conteos'] ?? [];
$archivos = $response['archivos'] ?? [];
$pendientes = $response['pendientes'] ?? [];
$tipo = $response['tipo'] ?? '';
$etiquetas = [
    'comidas' => 'Comidas / viáticos',
    'pagos' => 'Pagos / bancos',
    'asistencia' => 'Asistencia',
    'imss' => 'IMSS',
    'impuestos' => 'Impuestos',
    'trabajadores' => 'Trabajadores',
    'clientes' => 'Ventas / clientes',
    'precios' => 'Precios',
    'viaticos' => 'Gastos de obra',
    'adjunto' => 'Otros adjuntos',
];
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <h1 class="h3 mb-2"><?= htmlspecialchars($headTitle, ENT_QUOTES, 'UTF-8') ?></h1>
  <p class="text-muted small">Los Excel de lista de raya se indexan como adjuntos. No reemplazan la nómina del sistema ni inventan CURP/NSS. Si el RFC ya está en RH, se marca En RH. <em>LASTA DE PRECIOS.xlsx</em> es una matriz de materia prima × proveedor (marca X), no una lista de precios; no se copió a insumos.</p>

  <div class="row g-2 mb-3">
    <?php foreach ($etiquetas as $k => $lab): ?>
      <div class="col-auto">
        <a class="btn btn-sm <?= $tipo === $k ? 'btn-primary' : 'btn-outline-secondary' ?>" href="<?= base_url('contabilidad/ExpedienteRaya?tipo=' . rawurlencode($k)) ?>">
          <?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?> (<?= (int) ($conteos[$k] ?? 0) ?>)
        </a>
      </div>
    <?php endforeach; ?>
    <div class="col-auto">
      <a class="btn btn-sm btn-outline-dark" href="<?= base_url('contabilidad/ExpedienteRaya') ?>">Todos</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Cuadrilla Monterrey (alta pendiente en RH)</div>
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead><tr><th>Nombre</th><th>RFC</th><th>Puesto</th><th>En RH</th></tr></thead>
        <tbody>
        <?php foreach ($pendientes as $p): ?>
          <tr>
            <td><?= htmlspecialchars($p->nombre, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $p->rfc, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $p->puesto, ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <?php if ($p->estatus === 'En RH'): ?>
                <span class="badge bg-success">Ya existe (id <?= (int) $p->empleado_id ?>)</span>
              <?php else: ?>
                <span class="badge bg-danger">Pendiente de alta — no se creó ficha para no inventar fechas/salario</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Archivos del dossier (<?= count($archivos) ?>)</div>
    <div class="table-responsive">
      <table class="table table-striped table-sm mb-0">
        <thead><tr><th>Tipo</th><th>Archivo</th><th>Carpeta</th><th>KB</th></tr></thead>
        <tbody>
        <?php foreach ($archivos as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a->tipo, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($a->nombre, ENT_QUOTES, 'UTF-8') ?></td>
            <td class="small"><?= htmlspecialchars((string) $a->semana, ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= number_format(((int) $a->tamano) / 1024, 1) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
