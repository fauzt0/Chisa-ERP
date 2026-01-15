<?php
/**
 * Vista principal de Gestión de Órdenes de Venta
 */
$stats = $response['stats'] ?? [];
?>

<!-- Breadcrumb -->
<div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
        <li class="breadcrumb-item"><a href="#">CRM Ventas</a></li>
        <li class="breadcrumb-item active">Órdenes de Venta</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Título -->
<div class="row mb-3">
  <div class="col-md-6">
    <h2><i class="fas fa-file-invoice"></i> Gestión de Órdenes de Venta</h2>
  </div>
  <div class="col-md-6 text-end">
    <a href="<?=base_url();?>ventas/Pos" class="btn btn-primary">
      <i class="fas fa-cash-register"></i> Ir al POS
    </a>
  </div>
</div>

<!-- Estadísticas -->
<!-- Estadísticas -->
<div class="row mb-4">
  <!-- Ventas Hoy -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Ventas Hoy</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?php echo number_format($stats['ventas_hoy'] ?? 0); ?>
            </h3>
          </div>
          <div class="col-4 text-end">
             <i class="fas fa-shopping-cart text-success" style="font-size: 1.5rem;"></i>
          </div>
        </div>
        
        <div class="progress progress-sm shadow-sm mb-1">
          <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $stats['porcentaje_hoy'] ?? 0; ?>%"></div>
        </div>
        
        <small class="text-muted">Monto: $<?=number_format($stats['monto_hoy'] ?? 0, 2)?> | vs Ayer: <?php echo $stats['porcentaje_hoy'] ?? 0; ?>%</small>
      </div>
    </div>
  </div>

  <!-- Ventas del Mes -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Ventas del Mes</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?php echo number_format($stats['ventas_mes'] ?? 0); ?>
            </h3>
          </div>
          <div class="col-4 text-end">
             <span class="badge bg-primary"><?php echo $stats['porcentaje_mes'] ?? 0; ?>%</span>
          </div>
        </div>
        
        <div class="progress progress-sm shadow-sm mb-1">
          <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $stats['porcentaje_mes'] ?? 0; ?>%"></div>
        </div>
        
        <small class="text-muted">Monto: $<?=number_format($stats['monto_mes'] ?? 0, 2)?> | vs Mes Ant.</small>
      </div>
    </div>
  </div>

  <!-- Cotizaciones -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Cotizaciones</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?php echo number_format($stats['cotizaciones_pendientes'] ?? 0); ?>
            </h3>
          </div>
          <div class="col-4 text-end">
            <i class="fas fa-file-invoice text-warning" style="font-size: 1.5rem;"></i>
          </div>
        </div>
        
        <div class="progress progress-sm shadow-sm mb-1">
          <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $stats['porcentaje_cotizaciones'] ?? 0; ?>%"></div>
        </div>

        <small class="text-muted"><?php echo $stats['porcentaje_cotizaciones'] ?? 0; ?>% del total de activas</small>
      </div>
    </div>
  </div>

  <!-- En Preparación -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">En Preparación</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?php echo number_format($stats['ordenes_preparacion'] ?? 0); ?>
            </h3>
          </div>
          <div class="col-4 text-end">
            <i class="fas fa-boxes text-info" style="font-size: 1.5rem;"></i>
          </div>
        </div>
        
        <div class="progress progress-sm shadow-sm mb-1">
          <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $stats['porcentaje_preparacion'] ?? 0; ?>%"></div>
        </div>
        
        <small class="text-muted">Listas para envío (<?php echo $stats['porcentaje_preparacion'] ?? 0; ?>%)</small>
      </div>
    </div>
  </div>
</div>

<!-- Tabla de Órdenes -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-list"></i> Lista de Órdenes</h3>
  </div>
  <div class="card-body">
    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-2">
        <label class="form-label">Estatus</label>
        <select class="form-select" id="filtro_estatus">
          <option value="">Todos</option>
          <option value="Cotización">Cotización</option>
          <option value="Confirmada">Confirmada</option>
          <option value="En Preparación">En Preparación</option>
          <option value="Entregada">Entregada</option>
          <option value="Cancelada">Cancelada</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Tipo</label>
        <select class="form-select" id="filtro_tipo">
          <option value="">Todos</option>
          <option value="Mostrador">Mostrador</option>
          <option value="Pedido">Pedido</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Fecha Desde</label>
        <input type="date" class="form-control" id="filtro_fecha_desde">
      </div>
      <div class="col-md-3">
        <label class="form-label">Fecha Hasta</label>
        <input type="date" class="form-control" id="filtro_fecha_hasta">
      </div>
      <div class="col-md-2">
        <label class="form-label">&nbsp;</label>
        <button type="button" class="btn btn-secondary w-100" onclick="limpiarFiltros()">
          <i class="fas fa-eraser"></i> Limpiar
        </button>
      </div>
    </div>
    
    <table id="tablaOrdenes" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Folio</th>
          <th>Fecha</th>
          <th>Cliente</th>
          <th>Total</th>
          <th>Saldo Pendiente</th>
          <th>Estatus Pago</th>
          <th>Tipo</th>
          <th>Estatus</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal: Ver Orden -->
<div class="modal fade" id="modalVerOrden" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalles de la Orden</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detalleOrdenBody">
        <div class="text-center py-5">
          <i class="fas fa-spinner fa-spin fa-3x"></i>
          <p>Cargando...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Cancelar Orden -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cancelar Orden</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="cancelar_orden_id">
        <div class="mb-3">
          <label class="form-label">Motivo de Cancelación</label>
          <textarea class="form-control" id="cancelar_motivo" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="confirmarCancelacion()">Cancelar Orden</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Registrar Pago -->
<div class="modal fade" id="modalPago" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-dollar-sign"></i> Registrar Pago</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="pago_orden_id">
        
        <div class="alert alert-info">
          <strong>Saldo Pendiente:</strong> <span id="pago_saldo_pendiente">$0.00</span>
        </div>
        
        <form id="formPago">
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Monto a Pagar <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control" id="pago_monto" required>
              <small class="text-muted">Máximo: <span id="pago_monto_maximo"></span></small>
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="pago_fecha" required>
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
              <select class="form-select" id="pago_metodo" required>
                <option value="Efectivo">Efectivo</option>
                <option value="Transferencia">Transferencia</option>
                <option value="Tarjeta">Tarjeta</option>
                <option value="Cheque">Cheque</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Referencia</label>
              <input type="text" class="form-control" id="pago_referencia" placeholder="No. de cheque, transferencia, etc.">
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Notas</label>
            <textarea class="form-control" id="pago_notas" rows="2"></textarea>
          </div>
        </form>
        
        <hr>
        
        <h6><i class="fas fa-history"></i> Historial de Pagos</h6>
        <div id="historial_pagos">
          <p class="text-muted">Cargando...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" onclick="guardarPago()">
          <i class="fas fa-save"></i> Registrar Pago
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let tabla;

function initOrdenes() {
  inicializarDataTable();
  inicializarFiltros();
}

function inicializarDataTable() {
  tabla = $('#tablaOrdenes').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?=base_url();?>ventas/Ordenes/lista_ajax',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d.filtro_estatus = $('#filtro_estatus').val();
        d.filtro_tipo = $('#filtro_tipo').val();
        d.filtro_fecha_desde = $('#filtro_fecha_desde').val();
        d.filtro_fecha_hasta = $('#filtro_fecha_hasta').val();
        d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
      }
    },
    columns: [
      { data: 0 },  // Folio
      { data: 1 },  // Fecha
      { data: 2 },  // Cliente
      { data: 3 },  // Total
      { data: 4 },  // Saldo Pendiente
      { data: 5 },  // Estatus Pago
      { data: 6 },  // Tipo
      { data: 7 },  // Estatus
      { data: 8, orderable: false }  // Acciones
    ],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    },
    order: [[0, 'desc']]
  });
}

function inicializarFiltros() {
  $('#filtro_estatus, #filtro_tipo, #filtro_fecha_desde, #filtro_fecha_hasta').on('change', function() {
    tabla.ajax.reload();
  });
}

function limpiarFiltros() {
  $('#filtro_estatus').val('');
  $('#filtro_tipo').val('');
  $('#filtro_fecha_desde').val('');
  $('#filtro_fecha_hasta').val('');
  tabla.ajax.reload();
}

window.verOrden = function(id) {
  $('#detalleOrdenBody').html(`
    <div class="text-center py-5">
      <i class="fas fa-spinner fa-spin fa-3x"></i>
      <p>Cargando...</p>
    </div>
  `);
  $('#modalVerOrden').modal('show');
  
  $.post('<?=base_url();?>ventas/Pos/get_orden_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      renderDetalleOrden(result.orden);
    } else {
      $('#detalleOrdenBody').html(`
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle"></i> ${result.message}
        </div>
      `);
    }
  });
};

function renderDetalleOrden(orden) {
  let html = `
    <div class="row mb-3">
      <div class="col-md-6">
        <h6><i class="fas fa-file-invoice"></i> Información de la Orden</h6>
        <table class="table table-sm">
          <tr><th width="40%">Folio:</th><td>${orden.folio}</td></tr>
          <tr><th>Fecha:</th><td>${new Date(orden.fecha_orden).toLocaleDateString('es-MX')}</td></tr>
          <tr><th>Tipo:</th><td><span class="badge bg-${orden.tipo_venta == 'Mostrador' ? 'secondary' : 'primary'}">${orden.tipo_venta}</span></td></tr>
          <tr><th>Estatus:</th><td><span class="badge bg-info">${orden.estatus}</span></td></tr>
          <tr><th>Forma de Pago:</th><td>${orden.forma_pago}</td></tr>
        </table>
      </div>
      <div class="col-md-6">
        <h6><i class="fas fa-user"></i> Cliente</h6>
        <table class="table table-sm">
          <tr><th width="40%">Razón Social:</th><td>${orden.razon_social}</td></tr>
          <tr><th>RFC:</th><td>${orden.rfc || 'N/A'}</td></tr>
        </table>
      </div>
    </div>
  `;
  
  // Mostrar información de envío si es un Pedido
  if(orden.tipo_venta === 'Pedido' || orden.direccion_envio) {
    html += `
      <div class="row mb-3">
        <div class="col-12">
          <h6><i class="fas fa-truck"></i> Información de Envío</h6>
          <table class="table table-sm">
    `;
    
    if(orden.direccion_envio) {
      html += `<tr><th width="20%">Dirección:</th><td>${orden.direccion_envio}</td></tr>`;
    }
    
    if(orden.fecha_entrega_estimada) {
      html += `<tr><th>Fecha Entrega Estimada:</th><td>${new Date(orden.fecha_entrega_estimada).toLocaleDateString('es-MX')}</td></tr>`;
    }
    
    if(orden.costo_envio && parseFloat(orden.costo_envio) > 0) {
      html += `<tr><th>Costo de Envío:</th><td class="text-success"><strong>$${parseFloat(orden.costo_envio).toFixed(2)}</strong></td></tr>`;
    }
    
    html += `
          </table>
        </div>
      </div>
    `;
  }
  
  html += `
    <h6><i class="fas fa-box"></i> Productos</h6>
    <table class="table table-sm table-bordered">
      <thead class="table-light">
        <tr>
          <th>Producto</th>
          <th width="100">Cantidad</th>
          <th width="120">Precio Unit.</th>
          <th width="120">Subtotal</th>
        </tr>
      </thead>
      <tbody>
  `;
  
  orden.detalles.forEach(d => {
    // Debug: ver qué datos de formulación tenemos
    console.log('Detalle producto:', d.nombre, 'formulacion_id:', d.formulacion_id, 'formulacion_version:', d.formulacion_version);
    
    let formulacionInfo = '';
    if(d.formulacion_id && d.formulacion_version) {
      formulacionInfo = `<br><small class="text-info"><i class="fas fa-flask"></i> Fórmula V${d.formulacion_version}</small>`;
    }
    
    html += `
      <tr>
        <td>${d.nombre}<br><small class="text-muted">${d.codigo}</small>${formulacionInfo}</td>
        <td>${parseFloat(d.cantidad).toFixed(2)}</td>
        <td>$${parseFloat(d.precio_unitario).toFixed(2)}</td>
        <td>$${parseFloat(d.subtotal).toFixed(2)}</td>
      </tr>
    `;
  });
  
  html += `
      </tbody>
      <tfoot>
        <tr>
          <th colspan="3" class="text-end">Subtotal:</th>
          <th>$${parseFloat(orden.subtotal).toFixed(2)}</th>
        </tr>
  `;
  
  // Agregar descuento si existe
  if(orden.descuento_nombre && orden.descuento_valor > 0) {
    let descuentoMonto = 0;
    if(orden.descuento_tipo === 'Porcentaje') {
      descuentoMonto = parseFloat(orden.subtotal) * (parseFloat(orden.descuento_valor) / 100);
    } else {
      descuentoMonto = parseFloat(orden.descuento_valor);
    }
    
    html += `
        <tr class="table-warning">
          <th colspan="3" class="text-end">
            Descuento (${orden.descuento_nombre}):
            ${orden.descuento_tipo === 'Porcentaje' ? orden.descuento_valor + '%' : ''}
          </th>
          <th class="text-danger">-$${descuentoMonto.toFixed(2)}</th>
        </tr>
    `;
  }
  
  html += `
        <tr>
          <th colspan="3" class="text-end">IVA (16%):</th>
          <th>$${parseFloat(orden.iva).toFixed(2)}</th>
        </tr>
  `;
  
  // Agregar costo de envío si existe
  if(orden.costo_envio && parseFloat(orden.costo_envio) > 0) {
    html += `
        <tr>
          <th colspan="3" class="text-end">Costo de Envío:</th>
          <th class="text-success">$${parseFloat(orden.costo_envio).toFixed(2)}</th>
        </tr>
    `;
  }
  
  html += `
        <tr>
          <th colspan="3" class="text-end">TOTAL:</th>
          <th class="text-primary">$${parseFloat(orden.total).toFixed(2)}</th>
        </tr>
      </tfoot>
    </table>
  `;
  
  if(orden.observaciones) {
    html += `
      <div class="alert alert-info">
        <strong>Observaciones:</strong> ${orden.observaciones}
      </div>
    `;
  }
  
  $('#detalleOrdenBody').html(html);
}

window.confirmarOrden = function(id) {
  if(!confirm('¿Confirmar esta orden?')) return;
  
  $.post('<?=base_url();?>ventas/Ordenes/confirmar_ajax', {
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

window.cancelarOrden = function(id) {
  $('#cancelar_orden_id').val(id);
  $('#cancelar_motivo').val('');
  $('#modalCancelar').modal('show');
};

function confirmarCancelacion() {
  const id = $('#cancelar_orden_id').val();
  const motivo = $('#cancelar_motivo').val();
  
  if(!motivo) {
    notifyShow('Ingresa el motivo de cancelación', 'warning');
    return;
  }
  
  $.post('<?=base_url();?>ventas/Ordenes/cancelar_ajax', {
    'id': id,
    'motivo': motivo,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if(result.success) {
      $('#modalCancelar').modal('hide');
      tabla.ajax.reload();
    }
  });
}

window.mostrarModalPago = function(orden_id, saldo_pendiente) {
  $('#pago_orden_id').val(orden_id);
  $('#pago_saldo_pendiente').text('$' + parseFloat(saldo_pendiente).toFixed(2));
  $('#pago_monto_maximo').text('$' + parseFloat(saldo_pendiente).toFixed(2));
  $('#pago_monto').attr('max', saldo_pendiente);
  $('#pago_monto').val(saldo_pendiente);
  $('#pago_fecha').val(new Date().toISOString().split('T')[0]);
  $('#pago_metodo').val('Efectivo');
  $('#pago_referencia').val('');
  $('#pago_notas').val('');
  
  // Cargar historial de pagos
  cargarHistorialPagos(orden_id);
  
  $('#modalPago').modal('show');
};

function cargarHistorialPagos(orden_id) {
  $('#historial_pagos').html('<p class="text-muted">Cargando...</p>');
  
  $.post('<?=base_url();?>ventas/Ordenes/get_pagos_orden_ajax', {
    'orden_id': orden_id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      if(result.pagos.length === 0) {
        $('#historial_pagos').html('<p class="text-muted">No hay pagos registrados</p>');
      } else {
        let html = '<table class="table table-sm"><thead><tr><th>Folio</th><th>Fecha</th><th>Monto</th><th>Método</th></tr></thead><tbody>';
        result.pagos.forEach(p => {
          html += `<tr>
            <td>${p.folio}</td>
            <td>${new Date(p.fecha_pago).toLocaleDateString('es-MX')}</td>
            <td>$${parseFloat(p.monto).toFixed(2)}</td>
            <td>${p.metodo_pago}</td>
          </tr>`;
        });
        html += '</tbody></table>';
        $('#historial_pagos').html(html);
      }
    }
  });
}

function guardarPago() {
  const orden_id = $('#pago_orden_id').val();
  const monto = $('#pago_monto').val();
  const fecha = $('#pago_fecha').val();
  const metodo = $('#pago_metodo').val();
  
  if(!monto || !fecha || !metodo) {
    notifyShow('Completa todos los campos requeridos', 'warning');
    return;
  }
  
  const data = {
    'orden_id': orden_id,
    'monto': monto,
    'fecha_pago': fecha,
    'metodo_pago': metodo,
    'referencia': $('#pago_referencia').val(),
    'notas': $('#pago_notas').val(),
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  };
  
  $.post('<?=base_url();?>ventas/Ordenes/registrar_pago_ajax', data, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if(result.success) {
      $('#modalPago').modal('hide');
      tabla.ajax.reload();
    }
  });
}

// Inicializar
if (typeof jQuery !== 'undefined') {
  $(document).ready(initOrdenes);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initOrdenes);
    }
  });
}
</script>
