<?php
/**
 * Vista principal de Órdenes de Compra
 * Listado de órdenes con DataTables
 */
?>
<div class="container-fluid p-0">

  <!-- Breadcrumb -->
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
   
  <!-- Titulo de la pagina -->
  <h1 class="h3 mb-3"><?php echo $headTitle;?></h1>

  <!-- Cards de estadísticas -->
  <div class="row">
    <!-- Total Órdenes -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Total Órdenes</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['total_ordenes']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-file-invoice text-primary" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Órdenes en sistema</small>
        </div>
      </div>
    </div>

    <!-- Órdenes Pendientes -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Pendientes</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['ordenes_pendientes']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-clock text-warning" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Por recibir</small>
        </div>
      </div>
    </div>

    <!-- Recibidas Este Mes -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Recibidas (Mes)</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-12">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['recibidas_mes']; ?>
              </h3>
            </div>
          </div>
          <small class="text-muted">Órdenes completadas</small>
        </div>
      </div>
    </div>

    <!-- Total Gastado Este Mes -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Gasto (Mes)</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-12">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                $<?php echo number_format($response['stats']['total_mes'], 2); ?>
              </h3>
            </div>
          </div>
          <small class="text-muted">Total en compras</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla de órdenes -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Órdenes de Compra</h5>
            <button class="btn btn-primary btn-sm" onclick="mostrarModalNuevo()">
              <i class="fas fa-plus"></i> Nueva Orden
            </button>
          </div>
        </div>
        <div class="card-body">
          <!-- Filtros -->
          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">Estatus</label>
              <select class="form-select form-select-sm" id="filtroEstatus">
                <option value="">Todos</option>
                <option value="Borrador">Borrador</option>
                <option value="Enviada">Enviada</option>
                <option value="Confirmada">Confirmada</option>
                <option value="En Tránsito">En Tránsito</option>
                <option value="Recibida Parcial">Recibida Parcial</option>
                <option value="Recibida">Recibida</option>
                <option value="Cancelada">Cancelada</option>
              </select>
            </div>
          </div>

          <!-- DataTable -->
          <table id="tablaOrdenes" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Total</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>              
            </tbody>
            <tfoot>
              <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Total</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Nueva/Editar Orden -->
<div class="modal fade" id="modalOrden" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalOrdenTitle">Nueva Orden de Compra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formOrden">
          <input type="hidden" name="id" id="orden_id">
          
          <div class="row">
            <!-- Columna Izquierda -->
            <div class="col-md-6">
              <h6 class="mb-3 text-primary">Información General</h6>
              
              <div class="mb-3">
                <label class="form-label">Folio</label>
                <input type="text" class="form-control" id="orden_folio" readonly placeholder="Auto-generado">
              </div>

              <div class="mb-3">
                <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                <select class="form-select" name="proveedor_id" id="orden_proveedor_id" required onchange="cargarInsumosProveedor()">
                  <option value="">-- Seleccionar --</option>
                </select>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Fecha de Orden <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" name="fecha_orden" id="orden_fecha_orden" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Fecha Entrega Estimada</label>
                  <input type="date" class="form-control" name="fecha_entrega_estimada" id="orden_fecha_entrega_estimada">
                </div>
              </div>
            </div>

            <!-- Columna Derecha -->
            <div class="col-md-6">
              <h6 class="mb-3 text-primary">Condiciones de Pago</h6>

              <div class="mb-3">
                <label class="form-label">Forma de Pago</label>
                <select class="form-select" name="forma_pago" id="orden_forma_pago">
                  <option value="Transferencia">Transferencia</option>
                  <option value="Efectivo">Efectivo</option>
                  <option value="Cheque">Cheque</option>
                  <option value="Crédito">Crédito</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Condiciones de Pago</label>
                <textarea class="form-control" name="condiciones_pago" id="orden_condiciones_pago" rows="2" placeholder="Ej: 50% anticipo, 50% contra entrega"></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea class="form-control" name="observaciones" id="orden_observaciones" rows="2"></textarea>
              </div>
            </div>
          </div>

          <hr>

          <!-- Sección de Detalles -->
          <h6 class="mb-3 text-primary">Detalle de Insumos</h6>
          
          <div class="mb-3">
            <button type="button" class="btn btn-success btn-sm" onclick="mostrarFormAgregarDetalle()" id="btnAgregarDetalle" disabled>
              <i class="fas fa-plus"></i> Agregar Insumo
            </button>
          </div>

          <!-- Formulario agregar detalle (oculto) -->
          <div id="formAgregarDetalle" style="display:none;">
            <div class="card mb-3">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4">
                    <label class="form-label">Insumo <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="detalle_insumo_id">
                      <option value="">-- Seleccionar --</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm" id="detalle_cantidad" step="0.01" min="0.01" onchange="calcularSubtotalDetalle()">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Precio Unit. <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm" id="detalle_precio" step="0.01" min="0.01" onchange="calcularSubtotalDetalle()">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Subtotal</label>
                    <input type="text" class="form-control form-control-sm" id="detalle_subtotal" readonly>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                      <button type="button" class="btn btn-success btn-sm" onclick="agregarDetalleATabla()">
                        <i class="fas fa-check"></i>
                      </button>
                      <button type="button" class="btn btn-secondary btn-sm" onclick="cancelarAgregarDetalle()">
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabla de detalles -->
          <div class="table-responsive">
            <table class="table table-sm table-hover" id="tablaDetalles">
              <thead class="table-light">
                <tr>
                  <th>Código</th>
                  <th>Insumo</th>
                  <th>Cantidad</th>
                  <th>Precio Unit.</th>
                  <th>Subtotal</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr id="noDetalles">
                  <td colspan="6" class="text-center text-muted">No hay insumos agregados</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Totales -->
          <div class="row">
            <div class="col-md-8"></div>
            <div class="col-md-4">
              <table class="table table-sm">
                <tr>
                  <td class="text-end"><strong>Subtotal:</strong></td>
                  <td class="text-end" id="orden_subtotal_display">$0.00</td>
                </tr>
                <tr>
                  <td class="text-end"><strong>IVA (16%):</strong></td>
                  <td class="text-end" id="orden_iva_display">$0.00</td>
                </tr>
                <tr class="table-primary">
                  <td class="text-end"><strong>TOTAL:</strong></td>
                  <td class="text-end"><strong id="orden_total_display">$0.00</strong></td>
                </tr>
              </table>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarOrden()">
          <i class="fas fa-save"></i> Guardar Borrador
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Recibir Mercancía -->
<div class="modal fade" id="modalRecibir" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Recibir Mercancía: <span id="folioRecibir"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="recibir_orden_id">
        
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> Ingrese las cantidades recibidas. Si recibe menos de lo solicitado, la orden quedará como "Recibida Parcial".
        </div>

        <!-- Tabla de detalles para recibir -->
        <div class="table-responsive">
          <table class="table table-sm" id="tablaRecibirDetalles">
            <thead class="table-light">
              <tr>
                <th>Insumo</th>
                <th>Solicitada</th>
                <th>Ya Recibida</th>
                <th>Recibir Ahora</th>
                <th>Pendiente</th>
              </tr>
            </thead>
            <tbody>
              <!-- Se llena dinámicamente -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" onclick="guardarRecepcion()">
          <i class="fas fa-truck-loading"></i> Confirmar Recepción
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';
  
  let tabla;
  let ordenEditando = null;
  let detallesTemporales = []; // Para órdenes nuevas
  let insumosProveedor = [];

  function initOrdenesCompra() {
    inicializarDataTable();
    cargarProveedoresSelect();
    
    // Filtros
    $('#filtroEstatus').on('change', function() {
      tabla.ajax.reload();
    });

    // Fecha de orden por defecto: hoy
    $('#orden_fecha_orden').val(new Date().toISOString().split('T')[0]);
    
    // Event listener para cambio de insumo
    $('#detalle_insumo_id').on('change', function() {
      const selected = $(this).find(':selected');
      const precio = selected.data('precio');
      if(precio) {
        $('#detalle_precio').val(precio);
        calcularSubtotalDetalle();
      }
    });
  }

  function inicializarDataTable() {
    tabla = $('#tablaOrdenes').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '<?=base_url();?>compras/OrdenesCompra/lista_ajax',
        type: 'POST',
        data: function(d) {
          d.peticion = 'ajax';
          d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
          d.filtro_estatus = $('#filtroEstatus').val();
        }
      },
      columns: [
        { data: 0 },  // Folio
        { data: 1 },  // Fecha
        { data: 2 },  // Proveedor
        { data: 3 },  // Total
        { data: 4 },  // Estatus
        { data: 5, orderable: false }  // Acciones
      ],
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
      },
      pageLength: 25,
      order: [[1, 'desc']]
    });
  }

  function cargarProveedoresSelect() {
    $.post('<?=base_url();?>compras/OrdenesCompra/get_proveedores_select_ajax', {
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        let html = '<option value="">-- Seleccionar --</option>';
        result.proveedores.forEach(function(prov) {
          html += `<option value="${prov.id}">${prov.text}</option>`;
        });
        $('#orden_proveedor_id').html(html);
      }
    });
  }

  window.cargarInsumosProveedor = function() {
    const proveedorId = $('#orden_proveedor_id').val();
    if(!proveedorId) {
      $('#btnAgregarDetalle').prop('disabled', true);
      return;
    }

    $.post('<?=base_url();?>compras/OrdenesCompra/get_insumos_proveedor_ajax', {
      'proveedor_id': proveedorId,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        insumosProveedor = result.insumos;
        $('#btnAgregarDetalle').prop('disabled', false);
        
        let html = '<option value="">-- Seleccionar --</option>';
        result.insumos.forEach(function(ins) {
          html += `<option value="${ins.insumo_id}" data-precio="${ins.precio_compra}" data-codigo="${ins.codigo}" data-nombre="${ins.nombre_tecnico}" data-um="${ins.unidad_medida}">${ins.codigo} - ${ins.nombre_tecnico} ($${parseFloat(ins.precio_compra).toFixed(2)})</option>`;
        });
        $('#detalle_insumo_id').html(html);
      }
    });
  };

  window.mostrarModalNuevo = function() {
    ordenEditando = null;
    detallesTemporales = [];
    $('#modalOrdenTitle').text('Nueva Orden de Compra');
    $('#formOrden')[0].reset();
    $('#orden_id').val('');
    $('#orden_folio').val('Auto-generado');
    $('#orden_fecha_orden').val(new Date().toISOString().split('T')[0]);
    $('#orden_forma_pago').val('Transferencia');
    $('#btnAgregarDetalle').prop('disabled', true);
    $('#tablaDetalles tbody').html('<tr id="noDetalles"><td colspan="6" class="text-center text-muted">No hay insumos agregados</td></tr>');
    actualizarTotales();
    $('#modalOrden').modal('show');
  };

  window.mostrarFormAgregarDetalle = function() {
    $('#formAgregarDetalle').slideDown();
    $('#detalle_insumo_id').val('');
    $('#detalle_cantidad').val('');
    $('#detalle_precio').val('');
    $('#detalle_subtotal').val('');
  };

  window.cancelarAgregarDetalle = function() {
    $('#formAgregarDetalle').slideUp();
  };

  window.calcularSubtotalDetalle = function() {
    const cantidad = parseFloat($('#detalle_cantidad').val()) || 0;
    const precio = parseFloat($('#detalle_precio').val()) || 0;
    const subtotal = cantidad * precio;
    $('#detalle_subtotal').val('$' + subtotal.toFixed(2));
  };

  window.agregarDetalleATabla = function() {
    const insumoId = $('#detalle_insumo_id').val();
    const cantidad = parseFloat($('#detalle_cantidad').val());
    const precio = parseFloat($('#detalle_precio').val());

    if(!insumoId || !cantidad || !precio) {
      notifyShow('Complete todos los campos', 'warning');
      return;
    }

    const selected = $('#detalle_insumo_id').find(':selected');
    const codigo = selected.data('codigo');
    const nombre = selected.data('nombre');
    const um = selected.data('um');
    const subtotal = cantidad * precio;

    const detalle = {
      insumo_id: insumoId,
      codigo: codigo,
      nombre: nombre,
      unidad_medida: um,
      cantidad: cantidad,
      precio: precio,
      subtotal: subtotal
    };

    detallesTemporales.push(detalle);
    renderizarDetalles();
    cancelarAgregarDetalle();
  };

  function renderizarDetalles() {
    if(detallesTemporales.length === 0) {
      $('#tablaDetalles tbody').html('<tr id="noDetalles"><td colspan="6" class="text-center text-muted">No hay insumos agregados</td></tr>');
    } else {
      let html = '';
      detallesTemporales.forEach(function(det, index) {
        html += `
          <tr>
            <td>${det.codigo}</td>
            <td>${det.nombre} <small class="text-muted">(${det.unidad_medida})</small></td>
            <td>${det.cantidad}</td>
            <td>$${det.precio.toFixed(2)}</td>
            <td>$${det.subtotal.toFixed(2)}</td>
            <td>
              <button class="btn btn-sm btn-danger" onclick="eliminarDetalleTemporal(${index})">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        `;
      });
      $('#tablaDetalles tbody').html(html);
    }
    actualizarTotales();
  }

  window.eliminarDetalleTemporal = function(index) {
    detallesTemporales.splice(index, 1);
    renderizarDetalles();
  };

  function actualizarTotales() {
    let subtotal = 0;
    detallesTemporales.forEach(function(det) {
      subtotal += det.subtotal;
    });
    
    const iva = subtotal * 0.16;
    const total = subtotal + iva;

    $('#orden_subtotal_display').text('$' + subtotal.toFixed(2));
    $('#orden_iva_display').text('$' + iva.toFixed(2));
    $('#orden_total_display').text('$' + total.toFixed(2));
  }

  window.guardarOrden = function() {
    if(detallesTemporales.length === 0) {
      notifyShow('Agregue al menos un insumo a la orden', 'warning');
      return;
    }

    const formData = $('#formOrden').serialize();
    
    $.post('<?=base_url();?>compras/OrdenesCompra/crear_ajax',
      formData + '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>',
      function(result) {
        result = JSON.parse(result);
        if(result.success) {
          const ordenId = result.orden_id;
          
          // Guardar detalles
          let detallesGuardados = 0;
          detallesTemporales.forEach(function(det) {
            $.post('<?=base_url();?>compras/OrdenesCompra/agregar_detalle_ajax', {
              'orden_id': ordenId,
              'insumo_id': det.insumo_id,
              'cantidad_solicitada': det.cantidad,
              'precio_unitario': det.precio,
              'peticion': 'ajax',
              '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
            }, function() {
              detallesGuardados++;
              if(detallesGuardados === detallesTemporales.length) {
                notifyShow('Orden creada correctamente', 'success');
                $('#modalOrden').modal('hide');
                tabla.ajax.reload();
              }
            });
          });
        } else {
          notifyShow('Error: ' + result.message, 'danger');
        }
      }
    );
  };

  window.verOrden = function(id) {
    $.post('<?=base_url();?>compras/OrdenesCompra/get_orden_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        const orden = result.orden;
        
        // Llenar formulario en modo solo lectura
        $('#modalOrdenTitle').text('Ver Orden: ' + orden.folio);
        $('#orden_id').val(orden.id);
        $('#orden_folio').val(orden.folio);
        $('#orden_proveedor_id').val(orden.proveedor_id).prop('disabled', true);
        $('#orden_fecha_orden').val(orden.fecha_orden).prop('disabled', true);
        $('#orden_fecha_entrega_estimada').val(orden.fecha_entrega_estimada).prop('disabled', true);
        $('#orden_forma_pago').val(orden.forma_pago).prop('disabled', true);
        $('#orden_condiciones_pago').val(orden.condiciones_pago).prop('disabled', true);
        $('#orden_observaciones').val(orden.observaciones).prop('disabled', true);
        
        // Ocultar botones de edición
        $('#btnAgregarDetalle').hide();
        $('#formAgregarDetalle').hide();
        
        // Mostrar detalles
        let html = '';
        orden.detalles.forEach(function(det) {
          html += `
            <tr>
              <td>${det.codigo}</td>
              <td>${det.nombre_tecnico} <small class="text-muted">(${det.unidad_medida})</small></td>
              <td>${det.cantidad_solicitada}</td>
              <td>$${parseFloat(det.precio_unitario).toFixed(2)}</td>
              <td>$${parseFloat(det.subtotal).toFixed(2)}</td>
              <td>-</td>
            </tr>
          `;
        });
        $('#tablaDetalles tbody').html(html);
        
        // Mostrar totales
        $('#orden_subtotal_display').text('$' + parseFloat(orden.subtotal).toFixed(2));
        $('#orden_iva_display').text('$' + parseFloat(orden.iva).toFixed(2));
        $('#orden_total_display').text('$' + parseFloat(orden.total).toFixed(2));
        
        // Cambiar botón de guardar por cerrar
        $('.modal-footer').html('<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>');
        
        $('#modalOrden').modal('show');
      }
    });
  };

  window.editarOrden = function(id) {
    ordenEditando = id;
    
    $.post('<?=base_url();?>compras/OrdenesCompra/get_orden_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        const orden = result.orden;
        
        // Llenar formulario
        $('#modalOrdenTitle').text('Editar Orden: ' + orden.folio);
        $('#orden_id').val(orden.id);
        $('#orden_folio').val(orden.folio);
        $('#orden_proveedor_id').val(orden.proveedor_id).prop('disabled', false);
        $('#orden_fecha_orden').val(orden.fecha_orden).prop('disabled', false);
        $('#orden_fecha_entrega_estimada').val(orden.fecha_entrega_estimada).prop('disabled', false);
        $('#orden_forma_pago').val(orden.forma_pago).prop('disabled', false);
        $('#orden_condiciones_pago').val(orden.condiciones_pago).prop('disabled', false);
        $('#orden_observaciones').val(orden.observaciones).prop('disabled', false);
        
        // Cargar insumos del proveedor
        cargarInsumosProveedor();
        
        // Mostrar botón agregar
        $('#btnAgregarDetalle').show().prop('disabled', false);
        
        // Cargar detalles existentes en array temporal
        detallesTemporales = [];
        orden.detalles.forEach(function(det) {
          detallesTemporales.push({
            id: det.id, // ID del detalle para actualizar
            insumo_id: det.insumo_id,
            codigo: det.codigo,
            nombre: det.nombre_tecnico,
            unidad_medida: det.unidad_medida,
            cantidad: parseFloat(det.cantidad_solicitada),
            precio: parseFloat(det.precio_unitario),
            subtotal: parseFloat(det.subtotal)
          });
        });
        
        renderizarDetalles();
        
        // Restaurar botones normales
        $('.modal-footer').html(`
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="actualizarOrden()">
            <i class="fas fa-save"></i> Actualizar Orden
          </button>
        `);
        
        $('#modalOrden').modal('show');
      }
    });
  };

  window.actualizarOrden = function() {
    if(detallesTemporales.length === 0) {
      notifyShow('Agregue al menos un insumo a la orden', 'warning');
      return;
    }

    const ordenId = $('#orden_id').val();
    const formData = $('#formOrden').serialize();
    
    // Actualizar datos de la orden
    $.post('<?=base_url();?>compras/OrdenesCompra/editar_ajax',
      formData + '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>',
      function(result) {
        result = JSON.parse(result);
        if(result.success) {
          // Eliminar detalles antiguos y agregar nuevos
          // Por simplicidad, eliminamos todos y agregamos los nuevos
          $.post('<?=base_url();?>compras/OrdenesCompra/eliminar_todos_detalles_ajax', {
            'orden_id': ordenId,
            'peticion': 'ajax',
            '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
          }, function() {
            // Agregar nuevos detalles
            let detallesGuardados = 0;
            detallesTemporales.forEach(function(det) {
              $.post('<?=base_url();?>compras/OrdenesCompra/agregar_detalle_ajax', {
                'orden_id': ordenId,
                'insumo_id': det.insumo_id,
                'cantidad_solicitada': det.cantidad,
                'precio_unitario': det.precio,
                'peticion': 'ajax',
                '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
              }, function() {
                detallesGuardados++;
                if(detallesGuardados === detallesTemporales.length) {
                  notifyShow('Orden actualizada correctamente', 'success');
                  $('#modalOrden').modal('hide');
                  tabla.ajax.reload();
                }
              });
            });
          });
        } else {
          notifyShow('Error: ' + result.message, 'danger');
        }
      }
    );
  };

  window.aprobarOrden = function(id) {
    if(!confirm('¿Aprobar y enviar esta orden al proveedor?')) return;

    $.post('<?=base_url();?>compras/OrdenesCompra/cambiar_estatus_ajax', {
      'id': id,
      'estatus': 'Enviada',
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if(result.success) {
        tabla.ajax.reload();
      }
    });
  };

  window.eliminarOrden = function(id) {
    if(!confirm('¿Eliminar esta orden? Solo se pueden eliminar órdenes en Borrador.')) return;

    $.post('<?=base_url();?>compras/OrdenesCompra/eliminar_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if(result.success) {
        tabla.ajax.reload();
      }
    });
  };

  window.recibirMercancia = function(id) {
    $('#recibir_orden_id').val(id);
    
    $.post('<?=base_url();?>compras/OrdenesCompra/get_orden_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        const orden = result.orden;
        $('#folioRecibir').text(orden.folio);
        
        let html = '';
        orden.detalles.forEach(function(det) {
          const pendiente = det.cantidad_solicitada - det.cantidad_recibida;
          html += `
            <tr>
              <td>${det.nombre_tecnico} <small class="text-muted">(${det.unidad_medida})</small></td>
              <td>${det.cantidad_solicitada}</td>
              <td>${det.cantidad_recibida}</td>
              <td>
                <input type="number" class="form-control form-control-sm recibir-cantidad" 
                       data-detalle-id="${det.id}" 
                       data-max="${pendiente}"
                       value="${pendiente}" 
                       min="0" 
                       max="${pendiente}" 
                       step="0.01">
              </td>
              <td class="pendiente-${det.id}">${pendiente}</td>
            </tr>
          `;
        });
        
        $('#tablaRecibirDetalles tbody').html(html);
        
        // Calcular pendiente en tiempo real
        $('.recibir-cantidad').on('input', function() {
          const detalleId = $(this).data('detalle-id');
          const max = parseFloat($(this).data('max'));
          const recibir = parseFloat($(this).val()) || 0;
          const pendiente = max - recibir;
          $('.pendiente-' + detalleId).text(pendiente.toFixed(2));
        });
        
        $('#modalRecibir').modal('show');
      }
    });
  };

  window.guardarRecepcion = function() {
    const ordenId = $('#recibir_orden_id').val();
    const detalles = [];
    
    $('.recibir-cantidad').each(function() {
      const cantidad = parseFloat($(this).val()) || 0;
      if(cantidad > 0) {
        detalles.push({
          detalle_id: $(this).data('detalle-id'),
          cantidad_recibida: cantidad
        });
      }
    });

    if(detalles.length === 0) {
      notifyShow('Ingrese al menos una cantidad a recibir', 'warning');
      return;
    }

    // Enviar como JSON string para que PHP lo reciba correctamente
    $.ajax({
      url: '<?=base_url();?>compras/OrdenesCompra/recibir_mercancia_ajax',
      type: 'POST',
      data: {
        'orden_id': ordenId,
        'detalles': JSON.stringify(detalles),
        'peticion': 'ajax',
        '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
      },
      success: function(result) {
        result = JSON.parse(result);
        notifyShow(result.message, result.success ? 'success' : 'danger');
        if(result.success) {
          $('#modalRecibir').modal('hide');
          tabla.ajax.reload();
        }
      },
      error: function() {
        notifyShow('Error al procesar la recepción', 'danger');
      }
    });
  };

  // Inicializar
  if (typeof jQuery !== 'undefined') {
    $(document).ready(initOrdenesCompra);
  } else {
    window.addEventListener('load', function() {
      if (typeof jQuery !== 'undefined') {
        $(document).ready(initOrdenesCompra);
      }
    });
  }
})();
</script>
