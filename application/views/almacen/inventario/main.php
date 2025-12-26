<div class="container-fluid p-0">
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-boxes"></i> <?= $headTitle ?></h3>
    </div>
  </div>

  <!-- Tabs: Insumos y Productos -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <a class="nav-link active" id="tab-insumos" data-bs-toggle="tab" href="#insumos" role="tab">
                <i class="fas fa-box"></i> Insumos
                <span class="badge bg-primary ms-2"><?= count($response['insumos']) ?></span>
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link" id="tab-productos" data-bs-toggle="tab" href="#productos" role="tab">
                <i class="fas fa-cubes"></i> Productos
                <span class="badge bg-success ms-2"><?= count($response['productos']) ?></span>
              </a>
            </li>
          </ul>
        </div>
        <div class="card-body">
          <div class="tab-content">
            <!-- Tab: Insumos -->
            <div class="tab-pane fade show active" id="insumos" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover" id="tabla-insumos">
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Nombre</th>
                      <th>Categoría</th>
                      <th>Stock Actual</th>
                      <th>Stock Mínimo</th>
                      <th>Nivel</th>
                      <th>Valor</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($response['insumos'] as $insumo): ?>
                    <tr>
                      <td><strong><?= $insumo->codigo ?></strong></td>
                      <td><?= $insumo->nombre_tecnico ?></td>
                      <td><?= $insumo->categoria_nombre ?? '-' ?></td>
                      <td><strong><?= $insumo->stock_actual ?></strong> <?= $insumo->unidad_medida ?></td>
                      <td><?= $insumo->stock_minimo ?></td>
                      <td>
                        <?php
                        $badge_class = 'bg-success';
                        $texto = 'Normal';
                        if($insumo->nivel_stock == 'critico') {
                          $badge_class = 'bg-danger';
                          $texto = 'Crítico';
                        } elseif($insumo->nivel_stock == 'bajo') {
                          $badge_class = 'bg-warning';
                          $texto = 'Bajo';
                        } elseif($insumo->nivel_stock == 'exceso') {
                          $badge_class = 'bg-info';
                          $texto = 'Exceso';
                        }
                        ?>
                        <span class="badge <?= $badge_class ?>"><?= $texto ?></span>
                      </td>
                      <td>$<?= number_format($insumo->stock_actual * $insumo->precio_promedio, 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Tab: Productos -->
            <div class="tab-pane fade" id="productos" role="tabpanel">
              <div class="table-responsive">
                <table class="table table-hover" id="tabla-productos">
                  <thead>
                    <tr>
                      <th>Código</th>
                      <th>Nombre</th>
                      <th>Categoría</th>
                      <th>Stock Actual</th>
                      <th>Stock Mínimo</th>
                      <th>Nivel</th>
                      <th>Valor</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($response['productos'] as $producto): ?>
                    <tr>
                      <td><strong><?= $producto->codigo ?></strong></td>
                      <td><?= $producto->nombre ?></td>
                      <td><?= $producto->categoria_nombre ?? '-' ?></td>
                      <td><strong><?= $producto->stock_actual ?></strong> <?= $producto->unidad_venta ?></td>
                      <td><?= $producto->stock_minimo ?></td>
                      <td>
                        <?php
                        $badge_class = 'bg-success';
                        $texto = 'Normal';
                        if($producto->nivel_stock == 'critico') {
                          $badge_class = 'bg-danger';
                          $texto = 'Crítico';
                        } elseif($producto->nivel_stock == 'bajo') {
                          $badge_class = 'bg-warning';
                          $texto = 'Bajo';
                        } elseif($producto->nivel_stock == 'exceso') {
                          $badge_class = 'bg-info';
                          $texto = 'Exceso';
                        }
                        ?>
                        <span class="badge <?= $badge_class ?>"><?= $texto ?></span>
                      </td>
                      <td>$<?= number_format($producto->stock_actual * $producto->precio_venta, 2) ?></td>
                      <td>
                        <button class="btn btn-sm btn-primary" onclick="abrirModalAjuste(<?= $producto->id ?>, '<?= addslashes($producto->nombre) ?>', <?= $producto->stock_actual ?>)">
                          <i class="fas fa-edit"></i> Ajustar
                        </button>
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
    </div>
  </div>
</div>

<!-- Modal: Ajustar Stock -->
<div class="modal fade" id="modalAjustarStock" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-edit"></i> Ajustar Stock</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAjustarStock">
          <input type="hidden" id="producto_id_ajuste">
          
          <div class="mb-3">
            <label class="form-label">Producto</label>
            <input type="text" class="form-control" id="producto_nombre_ajuste" readonly>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Stock Actual</label>
            <input type="text" class="form-control" id="stock_actual_ajuste" readonly>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Tipo de Ajuste *</label>
            <select class="form-select" id="tipo_movimiento_ajuste" required>
              <option value="Entrada">Entrada (+)</option>
              <option value="Salida">Salida (-)</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Cantidad *</label>
            <input type="number" class="form-control" id="cantidad_ajuste" min="0.01" step="0.01" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Motivo *</label>
            <select class="form-select" id="motivo_ajuste" required>
              <option value="">Seleccione...</option>
              <option value="Ajuste por inventario físico">Ajuste por inventario físico</option>
              <option value="Merma por caducidad">Merma por caducidad</option>
              <option value="Robo">Robo</option>
              <option value="Error de conteo">Error de conteo</option>
              <option value="Devolución">Devolución</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" id="observaciones_ajuste" rows="2"></textarea>
          </div>
          
          <div class="alert alert-info" id="nuevo_stock_preview" style="display:none;">
            <strong>Nuevo Stock:</strong> <span id="nuevo_stock_valor"></span>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="procesarAjuste()">
          <i class="fas fa-save"></i> Guardar Ajuste
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Variables globales
var stockActualGlobal = 0;

// Función de inicialización con corrección de jQuery
function initInventario() {
  // Inicializar DataTables
  if($('#tabla-insumos').length) {
    $('#tabla-insumos').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
      },
      order: [[5, 'desc']], // Ordenar por nivel de stock
      pageLength: 25
    });
  }
  
  if($('#tabla-productos').length) {
    $('#tabla-productos').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
      },
      order: [[5, 'desc']], // Ordenar por nivel de stock
      pageLength: 25
    });
  }
  
  // Calcular nuevo stock al cambiar cantidad o tipo
  $('#cantidad_ajuste, #tipo_movimiento_ajuste').on('change keyup', function() {
    calcularNuevoStock();
  });
}

// Esperar a que jQuery esté disponible (corrección de error)
if (typeof jQuery !== 'undefined') {
  $(document).ready(initInventario);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initInventario);
    }
  });
}

function abrirModalAjuste(producto_id, producto_nombre, stock_actual) {
  $('#producto_id_ajuste').val(producto_id);
  $('#producto_nombre_ajuste').val(producto_nombre);
  $('#stock_actual_ajuste').val(stock_actual);
  stockActualGlobal = parseFloat(stock_actual);
  
  // Limpiar formulario
  $('#formAjustarStock')[0].reset();
  $('#producto_id_ajuste').val(producto_id);
  $('#producto_nombre_ajuste').val(producto_nombre);
  $('#stock_actual_ajuste').val(stock_actual);
  $('#nuevo_stock_preview').hide();
  
  $('#modalAjustarStock').modal('show');
}

function calcularNuevoStock() {
  var cantidad = parseFloat($('#cantidad_ajuste').val()) || 0;
  var tipo = $('#tipo_movimiento_ajuste').val();
  
  if(cantidad > 0) {
    var nuevoStock = tipo == 'Entrada' ? 
      stockActualGlobal + cantidad : 
      stockActualGlobal - cantidad;
    
    $('#nuevo_stock_valor').text(nuevoStock.toFixed(2));
    $('#nuevo_stock_preview').show();
  } else {
    $('#nuevo_stock_preview').hide();
  }
}

function procesarAjuste() {
  // Validar
  if(!$('#formAjustarStock')[0].checkValidity()) {
    $('#formAjustarStock')[0].reportValidity();
    return;
  }
  
  var cantidad = parseFloat($('#cantidad_ajuste').val());
  if(cantidad <= 0) {
    notifyShow('La cantidad debe ser mayor a 0', 'warning');
    return;
  }
  
  $.post('<?= base_url('almacen/Inventario/ajustar_stock_ajax') ?>', {
    producto_id: $('#producto_id_ajuste').val(),
    tipo_movimiento: $('#tipo_movimiento_ajuste').val(),
    cantidad: cantidad,
    motivo: $('#motivo_ajuste').val(),
    observaciones: $('#observaciones_ajuste').val(),
    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      notifyShow(result.message, 'success');
      $('#modalAjustarStock').modal('hide');
      location.reload();
    } else {
      notifyShow(result.message, 'danger');
    }
  });
}
</script>
