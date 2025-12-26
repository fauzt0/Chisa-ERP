<div class="container-fluid p-0">
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3>Inicio ERP</h3>
    </div>

  
  <?php
  // Extraer datos del response
  $ventas_stats = $response['ventas_stats'] ?? [];
  $produccion_stats = $response['produccion_stats'] ?? [];
  $ultimas_ordenes = $response['ultimas_ordenes'] ?? [];
  ?>

  <div class="row">
    <div class="col-12 col-sm-6 col-xxl-3 d-flex">
      <div class="card illustration flex-fill">
        <div class="card-body p-0 d-flex flex-fill">
          <div class="row g-0 w-100">
            <div class="col-6">
              <div class="illustration-text p-3 m-1">
                <h4 class="illustration-text">Bienvenido!</h4>
                <p class="mb-0">Dashboard ERP</p>
              </div>
            </div>
            <div class="col-6 align-self-end text-end">
              <img src="<?php echo base_url();?>assets/dist/img/illustrations/customer-support.png" alt="Customer Support" class="img-fluid illustration-img">
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-body py-4">
          <div class="d-flex align-items-start">
            <div class="flex-grow-1">
              <h3 class="mb-2">$ <?=number_format($ventas_stats['monto_mes'] ?? 0, 2)?></h3>
              <p class="mb-2">Ventas del Mes</p>
              <div class="mb-0">
                <span class="text-success small"> <i class="mdi mdi-arrow-bottom-right"></i> <?=number_format($ventas_stats['ventas_mes'] ?? 0)?> Ventas </span>
              </div>
            </div>
            <div class="d-inline-block ms-3">
              <div class="stat">
                <i class="align-middle text-success" data-lucide="dollar-sign"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-body py-4">
          <div class="d-flex align-items-start">
            <div class="flex-grow-1">
              <h3 class="mb-2"><?=$produccion_stats['en_proceso'] ?? 0?></h3>
              <p class="mb-2">Órdenes en Producción</p>
              <div class="mb-0">
                 <span class="text-warning small"> <i class="mdi mdi-arrow-bottom-right"></i> En Proceso </span>
              </div>
            </div>
            <div class="d-inline-block ms-3">
              <div class="stat">
                <i class="align-middle text-warning" data-lucide="shopping-bag"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-xxl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-body py-4">
          <div class="d-flex align-items-start">
            <div class="flex-grow-1">
              <h3 class="mb-2">$ <?=number_format($ventas_stats['monto_hoy'] ?? 0, 2)?></h3>
              <p class="mb-2">Ventas de Hoy</p>
              <div class="mb-0">
                <span class="text-info small"> <i class="mdi mdi-arrow-bottom-right"></i> <?=number_format($ventas_stats['ventas_hoy'] ?? 0)?> Ventas </span>
              </div>
            </div>
            <div class="d-inline-block ms-3">
              <div class="stat">
                <i class="align-middle text-info" data-lucide="dollar-sign"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 d-flex">
      <div class="card flex-fill w-100">
        <div class="card-header">
          <h5 class="card-title mb-0">Nuevos Clientes (Año Actual)</h5>
        </div>
        <div class="card-body d-flex w-100">
          <div class="align-self-center chart chart-lg">
            <canvas id="chartjs-dashboard-bar"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">

  <div class="card flex-fill">
    <div class="card-header">
      <h5 class="card-title mb-0">Últimas Órdenes de Venta</h5>
    </div>
    <div class="table-responsive">
      <table class="table table-hover my-0">
        <thead>
          <tr>
            <th>Folio</th>
            <th class="d-none d-xl-table-cell">Cliente</th>
            <th class="d-none d-xl-table-cell">Fecha</th>
            <th>Estatus</th>
            <th class="text-end">Total</th>
            <th class="text-end">Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($ultimas_ordenes as $orden): 
              $badgeColor = 'secondary';
              if($orden->estatus == 'Confirmada') $badgeColor = 'warning';
              elseif($orden->estatus == 'En Proceso') $badgeColor = 'primary';
              elseif($orden->estatus == 'Completada') $badgeColor = 'success';
              elseif($orden->estatus == 'Entregada') $badgeColor = 'info';
              elseif($orden->estatus == 'Cancelada') $badgeColor = 'danger';
          ?>
          <tr>
            <td><strong><?=$orden->folio?></strong></td>
            <td class="d-none d-xl-table-cell"><?=$orden->cliente?></td>
            <td class="d-none d-xl-table-cell"><?=date('d/m/Y', strtotime($orden->fecha_creacion))?></td>
            <td><span class="badge bg-<?=$badgeColor?>"><?=$orden->estatus?></span></td>
            <td class="text-end">$<?=number_format($orden->total, 2)?></td>
            <td class="text-end">
              <a href="<?=base_url()?>produccion/Dashboard/detalle/<?=$orden->id?>" class="btn btn-sm btn-light">Ver</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($ultimas_ordenes)): ?>
          <tr><td colspan="6" class="text-center">No hay órdenes recientes</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

