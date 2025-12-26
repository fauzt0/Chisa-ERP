<div class="container-fluid p-0">
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-truck"></i> <?= $headTitle ?></h3>
    </div>
  </div>

  <!-- Tabs: Órdenes de Venta y Obras -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <a class="nav-link active" id="tab-ordenes" data-bs-toggle="tab" href="#ordenes" role="tab">
                <i class="fas fa-shopping-cart"></i> Órdenes de Venta
                <span class="badge bg-primary ms-2"><?= count($response['ordenes_pendientes']) ?></span>
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link" id="tab-obras" data-bs-toggle="tab" href="#obras" role="tab">
                <i class="fas fa-hard-hat"></i> Obras
                <span class="badge bg-warning ms-2"><?= count($response['obras_pendientes']) ?></span>
              </a>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <!-- Tab: Órdenes de Venta -->
            <div class="tab-pane fade show active" id="ordenes" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover" id="tabla-ordenes">
                  <thead>
                    <tr>
                      <th>Folio</th>
                      <th>Cliente</th>
                      <th>Fecha</th>
                      <th>Total</th>
                      <th>Productos</th>
                      <th>Progreso</th>
                      <th>Estatus</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if(empty($response['ordenes_pendientes'])): ?>
                    <tr>
                      <td colspan="8" class="text-center text-muted">No hay órdenes pendientes de entrega</td>
                    </tr>
                    <?php else: ?>
                      <?php foreach($response['ordenes_pendientes'] as $orden): ?>
                      <tr>
                        <td><strong><?= $orden->folio ?></strong></td>
                        <td><?= $orden->cliente_nombre ?></td>
                        <td><?= date('d/m/Y', strtotime($orden->fecha_orden)) ?></td>
                        <td>$<?= number_format($orden->total, 2) ?></td>
                        <td><?= $orden->total_productos ?> items</td>
                        <td>
                          <?php 
                          $porcentaje = ($orden->cantidad_total > 0) ? 
                            round(($orden->cantidad_entregada_total / $orden->cantidad_total) * 100) : 0;
                          ?>
                          <div class="progress">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?= $porcentaje ?>%">
                              <?= $porcentaje ?>%
                            </div>
                          </div>
                        </td>
                        <td>
                          <?php
                          $badge_class = $orden->estatus == 'Confirmada' ? 'bg-info' : 'bg-warning';
                          ?>
                          <span class="badge <?= $badge_class ?>"><?= $orden->estatus ?></span>
                        </td>
                        <td>
                          <button class="btn btn-sm btn-success" onclick="abrirModalEntregaOrden(<?= $orden->id ?>)">
                            <i class="fas fa-truck"></i> Entregar
                          </button>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Tab: Obras -->
            <div class="tab-pane fade" id="obras" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover" id="tabla-obras">
                  <thead>
                    <tr>
                      <th>Folio</th>
                      <th>Nombre</th>
                      <th>Cliente</th>
                      <th>Productos</th>
                      <th>Progreso</th>
                      <th>Estatus</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if(empty($response['obras_pendientes'])): ?>
                    <tr>
                      <td colspan="7" class="text-center text-muted">No hay obras pendientes de entrega</td>
                    </tr>
                    <?php else: ?>
                      <?php foreach($response['obras_pendientes'] as $obra): ?>
                      <tr>
                        <td><strong><?= $obra->folio ?></strong></td>
                        <td><?= $obra->nombre ?></td>
                        <td><?= $obra->cliente_nombre ?></td>
                        <td><?= $obra->total_productos ?> items</td>
                        <td>
                          <?php 
                          $porcentaje = ($obra->cantidad_total > 0) ? 
                            round(($obra->cantidad_entregada_total / $obra->cantidad_total) * 100) : 0;
                          ?>
                          <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: <?= $porcentaje ?>%">
                              <?= $porcentaje ?>%
                            </div>
                          </div>
                        </td>
                        <td>
                          <?php
                          $badge_class = $obra->estatus == 'Confirmada' ? 'bg-info' : 'bg-warning';
                          ?>
                          <span class="badge <?= $badge_class ?>"><?= $obra->estatus ?></span>
                        </td>
                        <td>
                          <button class="btn btn-sm btn-success" onclick="abrirModalEntregaObra(<?= $obra->id ?>)">
                            <i class="fas fa-truck"></i> Entregar
                          </button>
                        </td>
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
    </div>
  </div>
</div>

<!-- Modal: Entregar Orden -->
<div class="modal fade" id="modalEntregaOrden" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-truck"></i> Entregar Orden de Venta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="entrega-orden-content">
          <div class="text-center">
            <div class="spinner-border" role="status"></div>
            <p>Cargando...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="procesarEntregaOrden()">
          <i class="fas fa-check"></i> Confirmar Entrega
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Entregar Obra -->
<div class="modal fade" id="modalEntregaObra" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="fas fa-truck"></i> Entregar Obra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="entrega-obra-content">
          <div class="text-center">
            <div class="spinner-border" role="status"></div>
            <p>Cargando...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="procesarEntregaObra()">
          <i class="fas fa-check"></i> Confirmar Entrega
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Variables globales
var ordenActual = null;
var obraActual = null;

// Función de inicialización con corrección de jQuery
function initEntregas() {
  // Inicializar DataTables solo si hay filas de datos
  if($('#tabla-ordenes').length && $('#tabla-ordenes tbody tr').length > 0) {
    // Verificar que no sea la fila de "no hay datos"
    var hasData = !$('#tabla-ordenes tbody tr td[colspan]').length;
    if(hasData) {
      $('#tabla-ordenes').DataTable({
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
        },
        order: [[2, 'desc']], // Ordenar por fecha
        pageLength: 10
      });
    }
  }
  
  if($('#tabla-obras').length && $('#tabla-obras tbody tr').length > 0) {
    // Verificar que no sea la fila de "no hay datos"
    var hasData = !$('#tabla-obras tbody tr td[colspan]').length;
    if(hasData) {
      $('#tabla-obras').DataTable({
        language: {
          url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
        },
        order: [[0, 'desc']], // Ordenar por folio
        pageLength: 10
      });
    }
  }
}

// Esperar a que jQuery esté disponible (corrección de error)
if (typeof jQuery !== 'undefined') {
  $(document).ready(initEntregas);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initEntregas);
    }
  });
}

// =====================================================
// ENTREGAS DE ÓRDENES
// =====================================================

function abrirModalEntregaOrden(orden_id) {
  $.post('<?= base_url('almacen/Entregas/get_orden_detalle_ajax') ?>', {
    orden_id: orden_id,
    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      ordenActual = result.orden;
      mostrarDetalleOrden(result.orden);
      $('#modalEntregaOrden').modal('show');
    } else {
      notifyShow(result.message, 'danger');
    }
  });
}

function mostrarDetalleOrden(orden) {
  var html = '<div class="mb-3">' +
    '<h6>Orden: <strong>' + orden.folio + '</strong></h6>' +
    '<p class="mb-0">Cliente: ' + orden.cliente_nombre + '</p>' +
    '<p class="mb-0">Fecha: ' + orden.fecha_orden + '</p>' +
    '</div>' +
    '<div class="table-responsive">' +
    '<table class="table table-sm">' +
    '<thead>' +
    '<tr>' +
    '<th>Producto</th>' +
    '<th>Pedido</th>' +
    '<th>Entregado</th>' +
    '<th>Pendiente</th>' +
    '<th>Stock</th>' +
    '<th>A Entregar</th>' +
    '</tr>' +
    '</thead>' +
    '<tbody>';
  
  orden.productos.forEach(function(prod, index) {
    var stock_ok = prod.stock_actual >= prod.pendiente_entregar;
    var badge_stock = stock_ok ? 'bg-success' : 'bg-danger';
    
    html += '<tr>' +
      '<td>' +
      '<strong>' + prod.producto_codigo + '</strong><br>' +
      '<small class="text-muted">' + prod.producto_nombre + '</small>' +
      '</td>' +
      '<td>' + prod.cantidad + '</td>' +
      '<td>' + prod.cantidad_entregada + '</td>' +
      '<td><strong>' + prod.pendiente_entregar + '</strong></td>' +
      '<td><span class="badge ' + badge_stock + '">' + prod.stock_actual + ' ' + prod.unidad_venta + '</span></td>' +
      '<td>' +
      '<input type="number" class="form-control form-control-sm" ' +
      'id="cantidad_orden_' + index + '" ' +
      'data-detalle-id="' + prod.id + '" ' +
      'data-producto-id="' + prod.producto_id + '" ' +
      'min="0" max="' + Math.min(prod.pendiente_entregar, prod.stock_actual) + '" ' +
      'value="' + Math.min(prod.pendiente_entregar, prod.stock_actual) + '" ' +
      'style="width: 100px;">' +
      '</td>' +
      '</tr>';
  });
  
  html += '</tbody></table></div>' +
    '<div class="mb-3">' +
    '<label class="form-label">Observaciones</label>' +
    '<textarea class="form-control" id="observaciones_orden" rows="2"></textarea>' +
    '</div>';
  
  $('#entrega-orden-content').html(html);
}

function procesarEntregaOrden() {
  if(!ordenActual) {
    notifyShow('No hay orden cargada', 'warning');
    return;
  }
  
  var productos = [];
  ordenActual.productos.forEach(function(prod, index) {
    var cantidad = parseFloat($('#cantidad_orden_' + index).val()) || 0;
    if(cantidad > 0) {
      productos.push({
        detalle_orden_id: prod.id,
        producto_id: prod.producto_id,
        cantidad_entregar: cantidad
      });
    }
  });
  
  if(productos.length == 0) {
    notifyShow('Debe ingresar al menos una cantidad a entregar', 'warning');
    return;
  }
  
  $.post('<?= base_url('almacen/Entregas/entregar_orden_ajax') ?>', {
    orden_id: ordenActual.id,
    productos: productos,
    observaciones: $('#observaciones_orden').val(),
    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      notifyShow(result.message + ' - Folio: ' + result.folio, 'success');
      $('#modalEntregaOrden').modal('hide');
      location.reload();
    } else {
      notifyShow(result.message, 'danger');
    }
  });
}

// =====================================================
// ENTREGAS DE OBRAS
// =====================================================

function abrirModalEntregaObra(obra_id) {
  $.post('<?= base_url('almacen/Entregas/get_obra_detalle_ajax') ?>', {
    obra_id: obra_id,
    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      obraActual = result.obra;
      mostrarDetalleObra(result.obra);
      $('#modalEntregaObra').modal('show');
    } else {
      notifyShow(result.message, 'danger');
    }
  });
}

function mostrarDetalleObra(obra) {
  var html = '<div class="mb-3">' +
    '<h6>Obra: <strong>' + obra.folio + '</strong></h6>' +
    '<p class="mb-0">Nombre: ' + obra.nombre + '</p>' +
    '<p class="mb-0">Cliente: ' + obra.cliente_nombre + '</p>' +
    '</div>' +
    '<div class="table-responsive">' +
    '<table class="table table-sm">' +
    '<thead>' +
    '<tr>' +
    '<th>Producto</th>' +
    '<th>Pedido</th>' +
    '<th>Entregado</th>' +
    '<th>Pendiente</th>' +
    '<th>Stock</th>' +
    '<th>A Entregar</th>' +
    '</tr>' +
    '</thead>' +
    '<tbody>';
  
  obra.productos.forEach(function(prod, index) {
    var stock_ok = prod.stock_actual >= prod.pendiente_entregar;
    var badge_stock = stock_ok ? 'bg-success' : 'bg-danger';
    
    html += '<tr>' +
      '<td>' +
      '<strong>' + prod.producto_codigo + '</strong><br>' +
      '<small class="text-muted">' + prod.producto_nombre + '</small>' +
      '</td>' +
      '<td>' + prod.cantidad + '</td>' +
      '<td>' + prod.cantidad_entregada + '</td>' +
      '<td><strong>' + prod.pendiente_entregar + '</strong></td>' +
      '<td><span class="badge ' + badge_stock + '">' + prod.stock_actual + ' ' + prod.unidad_venta + '</span></td>' +
      '<td>' +
      '<input type="number" class="form-control form-control-sm" ' +
      'id="cantidad_obra_' + index + '" ' +
      'data-obra-producto-id="' + prod.id + '" ' +
      'data-producto-id="' + prod.producto_id + '" ' +
      'min="0" max="' + Math.min(prod.pendiente_entregar, prod.stock_actual) + '" ' +
      'value="' + Math.min(prod.pendiente_entregar, prod.stock_actual) + '" ' +
      'style="width: 100px;">' +
      '</td>' +
      '</tr>';
  });
  
  html += '</tbody></table></div>' +
    '<div class="mb-3">' +
    '<label class="form-label">Observaciones</label>' +
    '<textarea class="form-control" id="observaciones_obra" rows="2"></textarea>' +
    '</div>';
  
  $('#entrega-obra-content').html(html);
}

function procesarEntregaObra() {
  if(!obraActual) {
    notifyShow('No hay obra cargada', 'warning');
    return;
  }
  
  var productos = [];
  obraActual.productos.forEach(function(prod, index) {
    var cantidad = parseFloat($('#cantidad_obra_' + index).val()) || 0;
    if(cantidad > 0) {
      productos.push({
        obra_producto_id: prod.id,
        producto_id: prod.producto_id,
        cantidad_entregar: cantidad
      });
    }
  });
  
  if(productos.length == 0) {
    notifyShow('Debe ingresar al menos una cantidad a entregar', 'warning');
    return;
  }
  
  $.post('<?= base_url('almacen/Entregas/entregar_obra_ajax') ?>', {
    obra_id: obraActual.id,
    productos: productos,
    observaciones: $('#observaciones_obra').val(),
    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      notifyShow(result.message + ' - Folio: ' + result.folio, 'success');
      $('#modalEntregaObra').modal('hide');
      location.reload();
    } else {
      notifyShow(result.message, 'danger');
    }
  });
}
</script>
