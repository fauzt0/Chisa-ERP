<?php
/**
 * Main Dashboard — permission-aware, customizable widget grid.
 *
 * The controller only passes widgets the user is authorized to see (in
 * $response['widgets']) together with their data. This view therefore can only
 * ever render authorized widgets; restricted metrics are never emitted to HTML.
 */
$widgets           = $response['widgets']          ?? [];
$ventas_stats      = $response['ventas_stats']     ?? [];
$produccion_stats  = $response['produccion_stats'] ?? [];
$compras_stats     = $response['compras_stats']    ?? [];
$proveedores_stats = $response['proveedores_stats']?? [];
$insumos_stats     = $response['insumos_stats']    ?? [];
$empleados_stats   = $response['empleados_stats']  ?? [];
$ultimas_ordenes   = $response['ultimas_ordenes']  ?? [];
$alertas_stock     = $response['alertas_stock']    ?? [];
$cartera           = $response['cartera']          ?? [];
$cartera_resumen   = $response['cartera_resumen']  ?? ['documentos' => 0, 'saldo' => 0, 'criticos' => 0];
$parcialidades_alerta = $response['parcialidades_alerta'] ?? [];

// Column size per widget (kept on the element so reordering preserves layout).
$widget_sizes = [
  'welcome'             => 'col-12 col-sm-6 col-xxl-3',
  'ventas_mes'          => 'col-12 col-sm-6 col-xxl-3',
  'ventas_hoy'          => 'col-12 col-sm-6 col-xxl-3',
  'produccion_ordenes'  => 'col-12 col-sm-6 col-xxl-3',
  'compras_resumen'     => 'col-12 col-sm-6 col-xxl-3',
  'proveedores_resumen' => 'col-12 col-sm-6 col-xxl-3',
  'insumos_resumen'     => 'col-12 col-sm-6 col-xxl-3',
  'empleados_resumen'   => 'col-12 col-sm-6 col-xxl-3',
  'stock_bajo'          => 'col-12',
  'ventas_chart'          => 'col-12 col-lg-6',
  'clientes_chart'        => 'col-12 col-lg-6',
  'compras_chart'         => 'col-12 col-lg-6',
  'proveedores_top_chart' => 'col-12 col-lg-6',
  'proveedores_tipo_chart'=> 'col-12 col-lg-4',
  'ultimas_ordenes'     => 'col-12',
  'cartera_cobros'      => 'col-12 col-lg-7',
  'obras_parcialidades' => 'col-12 col-lg-5',
];
?>

<style>
/* --- Dashboard customization UI (scoped) --------------------------------- */
#dashboard-widgets .dashboard-widget { display: flex; }
#dashboard-widgets .dashboard-widget > .card { width: 100%; }
#dashboard-widgets .dashboard-widget.widget-hidden { display: none !important; }
.dash-stat-icon { width: 44px; height: 44px; border-radius: 10px; display: inline-flex;
  align-items: center; justify-content: center; font-size: 1.15rem; }
/* Fixed-height, responsive chart area so canvases size correctly in the grid. */
.dash-chart-wrap { position: relative; width: 100%; height: 280px; }
.dash-chart-wrap canvas { max-width: 100%; }
.dash-toolbar .btn { white-space: nowrap; }
/* Config modal list */
#dashboardConfigList .dash-config-item { display: flex; align-items: center; gap: .75rem;
  padding: .6rem .75rem; border: 1px solid var(--bs-border-color); border-radius: 8px;
  margin-bottom: .5rem; background: var(--bs-body-bg); }
#dashboardConfigList .dash-config-item .dash-drag-handle { cursor: grab; color: var(--bs-secondary-color); }
#dashboardConfigList .dash-config-item.gu-mirror { list-style: none; }
#dashboardConfigList .dash-config-item .form-check { margin: 0; }
#dashboardConfigList .dash-config-title { flex: 1 1 auto; font-weight: 600; }
#dashboardConfigList .dash-config-group { font-size: .75rem; color: var(--bs-secondary-color); }
.gu-mirror { position: fixed !important; margin: 0 !important; z-index: 9999 !important; opacity: .9; list-style: none; }
</style>

<div class="container-fluid p-0">

  <!-- Toolbar -->
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3 class="mb-0">Inicio ERP</h3>
      <p class="text-muted mb-0 small">Tablero personalizable — solo se muestran los módulos autorizados para tu usuario.</p>
    </div>
    <div class="col-auto ms-auto text-end mt-n1 dash-toolbar">
      <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#dashboardConfigModal">
        <i class="fas fa-sliders-h me-1"></i> Personalizar
      </button>
    </div>
  </div>

  <?php if (empty($widgets)): ?>
    <div class="alert alert-info">
      <i class="fas fa-info-circle me-1"></i>
      No tienes módulos autorizados para mostrar en el tablero. Contacta a un administrador.
    </div>
  <?php endif; ?>

  <div class="row" id="dashboard-widgets">
  <?php foreach ($widgets as $w):
      $id    = $w['id'];
      $title = $w['title'];
      $size  = $widget_sizes[$id] ?? 'col-12 col-sm-6 col-xxl-3';
  ?>
    <div class="<?= $size ?> dashboard-widget" data-widget-id="<?= $id ?>" data-widget-title="<?= htmlspecialchars($title) ?>" data-widget-group="<?= htmlspecialchars($w['group']) ?>">
      <?php switch ($id):

        case 'welcome': ?>
        <div class="card illustration flex-fill">
          <div class="card-body p-0 d-flex flex-fill">
            <div class="row g-0 w-100">
              <div class="col-6">
                <div class="illustration-text p-3 m-1">
                  <h4 class="illustration-text">¡Bienvenido!</h4>
                  <p class="mb-0">Dashboard ERP</p>
                </div>
              </div>
              <div class="col-6 align-self-end text-end">
                <img src="<?= base_url() ?>assets/dist/img/illustrations/customer-support.png" alt="ERP" class="img-fluid illustration-img">
              </div>
            </div>
          </div>
        </div>
        <?php break;

        case 'ventas_mes': ?>
        <div class="card flex-fill">
          <div class="card-body py-4">
            <div class="d-flex align-items-start">
              <div class="flex-grow-1">
                <h3 class="mb-2">$<?= number_format($ventas_stats['monto_mes'] ?? 0, 2) ?></h3>
                <p class="mb-2">Ventas del Mes</p>
                <span class="text-success small"><i class="fas fa-arrow-up"></i> <?= number_format($ventas_stats['ventas_mes'] ?? 0) ?> ventas</span>
              </div>
              <div class="dash-stat-icon bg-success-subtle text-success ms-3"><i class="fas fa-dollar-sign"></i></div>
            </div>
          </div>
        </div>
        <?php break;

        case 'ventas_hoy': ?>
        <div class="card flex-fill">
          <div class="card-body py-4">
            <div class="d-flex align-items-start">
              <div class="flex-grow-1">
                <h3 class="mb-2">$<?= number_format($ventas_stats['monto_hoy'] ?? 0, 2) ?></h3>
                <p class="mb-2">Ventas de Hoy</p>
                <span class="text-info small"><i class="fas fa-receipt"></i> <?= number_format($ventas_stats['ventas_hoy'] ?? 0) ?> ventas</span>
              </div>
              <div class="dash-stat-icon bg-info-subtle text-info ms-3"><i class="fas fa-calendar-day"></i></div>
            </div>
          </div>
        </div>
        <?php break;

        case 'produccion_ordenes': ?>
        <div class="card flex-fill">
          <div class="card-body py-4">
            <div class="d-flex align-items-start">
              <div class="flex-grow-1">
                <h3 class="mb-2"><?= (int)($produccion_stats['en_proceso'] ?? 0) ?></h3>
                <p class="mb-2">Órdenes en Producción</p>
                <span class="text-warning small"><i class="fas fa-cogs"></i> En proceso</span>
              </div>
              <div class="dash-stat-icon bg-warning-subtle text-warning ms-3"><i class="fas fa-industry"></i></div>
            </div>
          </div>
        </div>
        <?php break;

        case 'compras_resumen': ?>
        <div class="card flex-fill">
          <div class="card-body py-4">
            <div class="d-flex align-items-start">
              <div class="flex-grow-1">
                <h3 class="mb-2">$<?= number_format($compras_stats['total_mes'] ?? 0, 2) ?></h3>
                <p class="mb-2">Compras del Mes</p>
                <span class="text-primary small"><i class="fas fa-clock"></i> <?= (int)($compras_stats['ordenes_pendientes'] ?? 0) ?> OC pendientes</span>
              </div>
              <div class="dash-stat-icon bg-primary-subtle text-primary ms-3"><i class="fas fa-file-invoice"></i></div>
            </div>
          </div>
        </div>
        <?php break;

        case 'proveedores_resumen': ?>
        <div class="card flex-fill">
          <div class="card-body py-4">
            <div class="d-flex align-items-start">
              <div class="flex-grow-1">
                <h3 class="mb-2"><?= (int)($proveedores_stats['total_activos'] ?? 0) ?></h3>
                <p class="mb-2">Proveedores Activos</p>
                <span class="text-danger small"><i class="fas fa-hand-holding-usd"></i> $<?= number_format($proveedores_stats['total_adeudo'] ?? 0, 2) ?> por pagar</span>
              </div>
              <div class="dash-stat-icon bg-danger-subtle text-danger ms-3"><i class="fas fa-truck"></i></div>
            </div>
          </div>
        </div>
        <?php break;

        case 'insumos_resumen': ?>
        <div class="card flex-fill">
          <div class="card-body py-4">
            <div class="d-flex align-items-start">
              <div class="flex-grow-1">
                <h3 class="mb-2"><?= (int)($insumos_stats['total_activos'] ?? 0) ?></h3>
                <p class="mb-2">Insumos Activos</p>
                <span class="<?= (int)($insumos_stats['stock_bajo'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?> small">
                  <i class="fas fa-boxes"></i> <?= (int)($insumos_stats['stock_bajo'] ?? 0) ?> con stock bajo
                </span>
              </div>
              <div class="dash-stat-icon bg-secondary-subtle text-secondary ms-3"><i class="fas fa-warehouse"></i></div>
            </div>
          </div>
        </div>
        <?php break;

        case 'empleados_resumen': ?>
        <div class="card flex-fill">
          <div class="card-body py-4">
            <div class="d-flex align-items-start">
              <div class="flex-grow-1">
                <h3 class="mb-2"><?= (int)($empleados_stats['total_empleados'] ?? 0) ?></h3>
                <p class="mb-2">Empleados</p>
                <span class="text-success small"><i class="fas fa-user-check"></i> <?= (int)($empleados_stats['empleados_activos'] ?? 0) ?> activos</span>
              </div>
              <div class="dash-stat-icon bg-info-subtle text-info ms-3"><i class="fas fa-users"></i></div>
            </div>
          </div>
        </div>
        <?php break;

        case 'stock_bajo': ?>
        <?php if (!empty($alertas_stock)): ?>
        <div class="card flex-fill">
          <div class="dropdown w-100">
            <button class="btn btn-light border border-danger w-100 d-flex justify-content-between align-items-center py-2 px-3"
                    type="button" id="dropdownStockBajo" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
              <span class="text-danger fw-semibold">
                <i class="fas fa-exclamation-triangle"></i> Stock bajo: <?= count($alertas_stock) ?> insumo<?= count($alertas_stock) !== 1 ? 's' : '' ?>
              </span>
              <span class="badge bg-danger rounded-pill"><?= count($alertas_stock) ?></span>
            </button>
            <div class="dropdown-menu dropdown-menu-lg p-0 w-100 shadow border-danger" aria-labelledby="dropdownStockBajo" style="max-height: 380px; overflow-y: auto;">
              <div class="px-3 py-2 border-bottom bg-light"><small class="text-muted">Insumos por debajo del mínimo</small></div>
              <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                  <thead class="table-light sticky-top"><tr><th>Código</th><th>Insumo</th><th>Stock</th><th>Mín.</th><th></th></tr></thead>
                  <tbody>
                    <?php foreach ($alertas_stock as $insumo): ?>
                    <tr>
                      <td><strong><?= htmlspecialchars($insumo->codigo) ?></strong></td>
                      <td><?= htmlspecialchars($insumo->nombre_tecnico) ?></td>
                      <td class="text-danger fw-bold"><?= $insumo->stock_actual ?> <?= $insumo->unidad_medida ?></td>
                      <td><?= $insumo->stock_minimo ?></td>
                      <td><a href="<?= base_url() ?>compras/OrdenesCompra" class="btn btn-sm btn-outline-danger"><i class="fas fa-shopping-cart"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div class="card flex-fill">
          <div class="card-body py-3 d-flex align-items-center">
            <i class="fas fa-check-circle text-success me-2"></i>
            <span class="text-muted">Sin alertas de stock: todos los insumos están por encima del mínimo.</span>
          </div>
        </div>
        <?php endif; ?>
        <?php break;

        case 'ventas_chart': ?>
        <div class="card flex-fill w-100">
          <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-chart-line me-1 text-success"></i>Ventas Mensuales (Año Actual)</h5></div>
          <div class="card-body">
            <div class="dash-chart-wrap"><canvas id="chart-ventas-mensuales"></canvas></div>
          </div>
        </div>
        <?php break;

        case 'clientes_chart': ?>
        <div class="card flex-fill w-100">
          <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-user-plus me-1 text-primary"></i>Nuevos Clientes (Año Actual)</h5></div>
          <div class="card-body">
            <div class="dash-chart-wrap"><canvas id="chartjs-dashboard-bar"></canvas></div>
          </div>
        </div>
        <?php break;

        case 'compras_chart': ?>
        <div class="card flex-fill w-100">
          <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-chart-bar me-1 text-warning"></i>Compras por Mes (12 meses)</h5></div>
          <div class="card-body">
            <div class="dash-chart-wrap"><canvas id="chart-compras-mes"></canvas></div>
          </div>
        </div>
        <?php break;

        case 'proveedores_top_chart': ?>
        <div class="card flex-fill w-100">
          <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-trophy me-1 text-warning"></i>Top 5 Proveedores (por monto)</h5></div>
          <div class="card-body">
            <div class="dash-chart-wrap"><canvas id="chart-top-proveedores"></canvas></div>
          </div>
        </div>
        <?php break;

        case 'proveedores_tipo_chart': ?>
        <div class="card flex-fill w-100">
          <div class="card-header"><h5 class="card-title mb-0"><i class="fas fa-chart-pie me-1 text-info"></i>Proveedores por Tipo</h5></div>
          <div class="card-body d-flex align-items-center justify-content-center">
            <div class="dash-chart-wrap"><canvas id="chart-distribucion-prov"></canvas></div>
          </div>
        </div>
        <?php break;

        case 'ultimas_ordenes': ?>
        <div class="card flex-fill w-100">
          <div class="card-header"><h5 class="card-title mb-0">Últimas Órdenes de Venta</h5></div>
          <div class="table-responsive">
            <table class="table table-hover my-0">
              <thead><tr>
                <th>Folio</th>
                <th class="d-none d-xl-table-cell">Cliente</th>
                <th class="d-none d-xl-table-cell">Fecha</th>
                <th>Estatus</th>
                <th class="text-end">Total</th>
                <th class="text-end">Acción</th>
              </tr></thead>
              <tbody>
                <?php foreach ($ultimas_ordenes as $orden):
                    $badgeColor = 'secondary';
                    if ($orden->estatus == 'Confirmada') $badgeColor = 'warning';
                    elseif ($orden->estatus == 'En Proceso') $badgeColor = 'primary';
                    elseif ($orden->estatus == 'Completada') $badgeColor = 'success';
                    elseif ($orden->estatus == 'Entregada') $badgeColor = 'info';
                    elseif ($orden->estatus == 'Cancelada') $badgeColor = 'danger';
                ?>
                <tr>
                  <td><strong><?= $orden->folio ?></strong></td>
                  <td class="d-none d-xl-table-cell"><?= htmlspecialchars($orden->cliente ?? '') ?></td>
                  <td class="d-none d-xl-table-cell"><?= date('d/m/Y', strtotime($orden->fecha_creacion)) ?></td>
                  <td><span class="badge bg-<?= $badgeColor ?>"><?= $orden->estatus ?></span></td>
                  <td class="text-end">$<?= number_format($orden->total, 2) ?></td>
                  <td class="text-end"><a href="<?= base_url() ?>produccion/Dashboard/detalle/<?= $orden->id ?>" class="btn btn-sm btn-light">Ver</a></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($ultimas_ordenes)): ?>
                <tr><td colspan="6" class="text-center text-muted">No hay órdenes recientes</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php break;

        case 'cartera_cobros': ?>
        <div class="card flex-fill border-danger">
          <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-hand-holding-usd me-1"></i>Clientes con falta de pago</h5>
            <span class="small">
              <?= (int)($cartera_resumen['documentos'] ?? 0) ?> docs
              · $<?= number_format((float)($cartera_resumen['saldo'] ?? 0), 2) ?>
            </span>
          </div>
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead class="table-light"><tr><th>Cliente</th><th>Folio</th><th class="text-end">Saldo</th><th></th></tr></thead>
              <tbody>
              <?php if (empty($cartera)): ?>
                <tr><td colspan="4" class="text-muted text-center">Sin saldos pendientes</td></tr>
              <?php else: foreach ($cartera as $cx): ?>
                <tr class="<?= $cx->severidad === 'danger' ? 'table-danger' : '' ?>">
                  <td>
                    <?= htmlspecialchars($cx->cliente, ENT_QUOTES, 'UTF-8') ?>
                    <?php if (!empty($cx->rfc)): ?><br><small class="text-muted"><?= htmlspecialchars($cx->rfc, ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($cx->folio, ENT_QUOTES, 'UTF-8') ?><br><small><?= $cx->tipo === 'obra' ? 'Obra' : 'Venta' ?> · <?= (int)$cx->dias ?> d</small></td>
                  <td class="text-end text-danger fw-bold">$<?= number_format((float)$cx->saldo, 2) ?></td>
                  <td><a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($cx->link, ENT_QUOTES, 'UTF-8') ?>">Cobrar</a></td>
                </tr>
              <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php break;

        case 'obras_parcialidades': ?>
        <div class="card flex-fill border-warning">
          <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="fas fa-calendar-alt me-1"></i>Parcialidades de obra (7 días / vencidas)</h5>
          </div>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="table-light"><tr><th>Obra / cliente</th><th>Fecha</th><th class="text-end">Monto</th></tr></thead>
              <tbody>
              <?php if (empty($parcialidades_alerta)): ?>
                <tr><td colspan="3" class="text-muted text-center">Sin cobros programados próximos</td></tr>
              <?php else: foreach ($parcialidades_alerta as $par): ?>
                <tr class="<?= $par->estatus === 'Vencida' ? 'table-danger' : '' ?>">
                  <td>
                    <a href="<?= htmlspecialchars($par->link, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($par->folio, ENT_QUOTES, 'UTF-8') ?></a>
                    <br><small><?= htmlspecialchars($par->cliente ?: '', ENT_QUOTES, 'UTF-8') ?></small>
                  </td>
                  <td><?= htmlspecialchars(date('d/m/Y', strtotime($par->fecha_programada)), ENT_QUOTES, 'UTF-8') ?>
                    <br><span class="badge bg-<?= $par->estatus === 'Vencida' ? 'danger' : 'warning' ?>"><?= htmlspecialchars($par->estatus, ENT_QUOTES, 'UTF-8') ?></span>
                  </td>
                  <td class="text-end">$<?= number_format((float)$par->monto, 2) ?></td>
                </tr>
              <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php break;

      endswitch; ?>
    </div>
  <?php endforeach; ?>
  </div>
</div>

<!-- Customization modal -->
<div class="modal fade" id="dashboardConfigModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-sliders-h me-2"></i>Personalizar tablero</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">
          Activa o desactiva las tarjetas y arrástralas para cambiar su orden. Tu configuración se guarda en este dispositivo.
        </p>
        <div id="dashboardConfigList"><!-- filled by JS from authorized widgets --></div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-secondary" id="dashboardConfigReset">
          <i class="fas fa-undo me-1"></i> Restablecer
        </button>
        <div>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="dashboardConfigSave">Guardar</button>
        </div>
      </div>
    </div>
  </div>
</div>
