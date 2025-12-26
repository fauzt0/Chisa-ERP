<div class="container-fluid p-0">
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-warehouse"></i> <?= $headTitle ?></h3>
    </div>
  </div>

  <!-- Resumen de Inventario -->
  <div class="row">
    <!-- Insumos -->
    <div class="col-md-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col mt-0">
              <h5 class="card-title">Insumos</h5>
            </div>
            <div class="col-auto">
              <div class="stat text-primary">
                <i class="fas fa-boxes fa-2x"></i>
              </div>
            </div>
          </div>
          <h1 class="mt-1 mb-3"><?= number_format($response['resumen']['insumos']['total_items']) ?></h1>
          <div class="mb-0">
            <span class="text-success">$<?= number_format($response['resumen']['insumos']['valor_total'], 2) ?></span>
            <span class="text-muted">Valor total</span>
          </div>
          <?php if($response['resumen']['insumos']['stock_bajo'] > 0): ?>
          <div class="mt-2">
            <span class="badge bg-warning"><?= $response['resumen']['insumos']['stock_bajo'] ?> con stock bajo</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Productos -->
    <div class="col-md-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col mt-0">
              <h5 class="card-title">Productos</h5>
            </div>
            <div class="col-auto">
              <div class="stat text-success">
                <i class="fas fa-box fa-2x"></i>
              </div>
            </div>
          </div>
          <h1 class="mt-1 mb-3"><?= number_format($response['resumen']['productos']['total_items']) ?></h1>
          <div class="mb-0">
            <span class="text-success">$<?= number_format($response['resumen']['productos']['valor_total'], 2) ?></span>
            <span class="text-muted">Valor total</span>
          </div>
          <?php if($response['resumen']['productos']['stock_bajo'] > 0): ?>
          <div class="mt-2">
            <span class="badge bg-warning"><?= $response['resumen']['productos']['stock_bajo'] ?> con stock bajo</span>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Órdenes Pendientes -->
    <div class="col-md-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col mt-0">
              <h5 class="card-title">Órdenes Pendientes</h5>
            </div>
            <div class="col-auto">
              <div class="stat text-info">
                <i class="fas fa-shopping-cart fa-2x"></i>
              </div>
            </div>
          </div>
          <h1 class="mt-1 mb-3"><?= $response['resumen']['ordenes_pendientes'] ?></h1>
          <div class="mb-0">
            <span class="text-muted">Por entregar</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Obras Pendientes -->
    <div class="col-md-6 col-xl-3">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col mt-0">
              <h5 class="card-title">Obras Pendientes</h5>
            </div>
            <div class="col-auto">
              <div class="stat text-warning">
                <i class="fas fa-hard-hat fa-2x"></i>
              </div>
            </div>
          </div>
          <h1 class="mt-1 mb-3"><?= $response['resumen']['obras_pendientes'] ?></h1>
          <div class="mb-0">
            <span class="text-muted">Por entregar</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Últimos Movimientos -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="fas fa-exchange-alt"></i> Últimos Movimientos</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Tipo</th>
                  <th>Producto</th>
                  <th>Cantidad</th>
                  <th>Stock Anterior</th>
                  <th>Stock Nuevo</th>
                  <th>Usuario</th>
                  <th>Motivo</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($response['ultimos_movimientos'])): ?>
                <tr>
                  <td colspan="8" class="text-center text-muted">No hay movimientos registrados</td>
                </tr>
                <?php else: ?>
                  <?php foreach($response['ultimos_movimientos'] as $mov): ?>
                  <tr>
                    <td><?= date('d/m/Y H:i', strtotime($mov->fecha_movimiento)) ?></td>
                    <td>
                      <?php
                      $badge_class = in_array($mov->tipo_movimiento, ['Entrada', 'Produccion', 'Devolucion']) ? 'bg-success' : 'bg-danger';
                      ?>
                      <span class="badge <?= $badge_class ?>"><?= $mov->tipo_movimiento ?></span>
                    </td>
                    <td>
                      <strong><?= $mov->producto_codigo ?></strong><br>
                      <small class="text-muted"><?= $mov->producto_nombre ?></small>
                    </td>
                    <td>
                      <?php
                      $signo = in_array($mov->tipo_movimiento, ['Entrada', 'Produccion', 'Devolucion']) ? '+' : '-';
                      $color = in_array($mov->tipo_movimiento, ['Entrada', 'Produccion', 'Devolucion']) ? 'text-success' : 'text-danger';
                      ?>
                      <span class="<?= $color ?>"><?= $signo ?><?= $mov->cantidad ?></span>
                    </td>
                    <td><?= $mov->stock_anterior ?></td>
                    <td><strong><?= $mov->stock_nuevo ?></strong></td>
                    <td><?= $mov->usuario_nombre ?? 'Sistema' ?></td>
                    <td><small><?= $mov->motivo ?></small></td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Accesos Rápidos -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="fas fa-bolt"></i> Accesos Rápidos</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <a href="<?= base_url('almacen/Inventario') ?>" class="btn btn-primary w-100 mb-2">
                <i class="fas fa-list"></i> Ver Inventario
              </a>
            </div>
            <div class="col-md-3">
              <a href="<?= base_url('almacen/Entregas') ?>" class="btn btn-success w-100 mb-2">
                <i class="fas fa-truck"></i> Entregas
              </a>
            </div>
            <div class="col-md-3">
              <a href="<?= base_url('almacen/Movimientos') ?>" class="btn btn-info w-100 mb-2">
                <i class="fas fa-exchange-alt"></i> Movimientos
              </a>
            </div>
            <div class="col-md-3">
              <a href="<?= base_url('almacen/Inventario') ?>" class="btn btn-warning w-100 mb-2">
                <i class="fas fa-exclamation-triangle"></i> Alertas de Stock
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
