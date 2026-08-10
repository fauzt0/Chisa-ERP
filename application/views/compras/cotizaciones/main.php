<?php $this->load->helper('permissions'); ?>
<div class="container-fluid p-0 compras-page">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <h1 class="h3 mb-3"><?php echo $headTitle; ?></h1>

  <div class="row">
    <div class="col-lg-4 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header"><h5 class="card-title mb-0 mt-2">Total</h5></div>
        <div class="card-body pt-0">
          <h3 class="fw-light mb-0"><?php echo (int)($response['stats']['total'] ?? 0); ?></h3>
          <small class="text-muted">Cotizaciones activas</small>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header"><h5 class="card-title mb-0 mt-2">Pendientes</h5></div>
        <div class="card-body pt-0">
          <h3 class="fw-light mb-0 text-warning"><?php echo (int)($response['stats']['pendientes'] ?? 0); ?></h3>
          <small class="text-muted">Por respuesta</small>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header"><h5 class="card-title mb-0 mt-2">Recibidas</h5></div>
        <div class="card-body pt-0">
          <h3 class="fw-light mb-0 text-info"><?php echo (int)($response['stats']['recibidas'] ?? 0); ?></h3>
          <small class="text-muted">Listas para comparar</small>
        </div>
      </div>
    </div>
    <div class="col-lg-4 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header"><h5 class="card-title mb-0 mt-2">Aprobadas</h5></div>
        <div class="card-body pt-0">
          <h3 class="fw-light mb-0 text-success"><?php echo (int)($response['stats']['aprobadas'] ?? 0); ?></h3>
          <small class="text-muted">OC generadas</small>
        </div>
      </div>
    </div>
  </div>

  <div class="row mt-3">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">Cotizaciones de Compra</h5>
          <?php if (tiene_permiso('compras_cotizaciones_add') || tiene_permiso('compras_ordenes_add')): ?>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="abrirModalSolicitud()">
              <i class="fas fa-plus"></i> Nueva solicitud (comparar)
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="abrirModalCotizacion()">
              <i class="fas fa-file-alt"></i> Cotización individual
            </button>
          </div>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <div class="crm-toolbar mb-3 p-3 bg-light rounded border">
            <div class="row g-2 align-items-end">
              <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Estatus</label>
                <select class="form-select form-select-sm" id="filtroEstatusCot">
                  <option value="">Todos</option>
                  <option value="Pendiente">Pendiente</option>
                  <option value="Recibida">Recibida</option>
                  <option value="Rechazada">Rechazada</option>
                  <option value="Aprobada">Aprobada</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Grupo comparación</label>
                <select class="form-select form-select-sm" id="filtroGrupoCot">
                  <option value="">Todos</option>
                  <?php foreach ($response['grupos'] ?? [] as $g): ?>
                  <option value="<?php echo htmlspecialchars($g->grupo_folio); ?>">
                    <?php echo htmlspecialchars($g->grupo_folio); ?> (<?php echo (int)$g->total_cotizaciones; ?>)
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-secondary w-100" onclick="limpiarFiltrosCot()">
                  <i class="fas fa-eraser"></i> Limpiar
                </button>
              </div>
            </div>
          </div>

          <table id="tablaCotizaciones" class="table table-striped table-hover table-sm" style="width:100%">
            <thead class="table-light">
              <tr>
                <th>Folio</th>
                <th>Grupo</th>
                <th>Proveedor</th>
                <th>Solicitud</th>
                <th>Respuesta</th>
                <th>Total</th>
                <th>Estatus</th>
                <th width="140">Acciones</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Nueva solicitud (múltiples proveedores) -->
<div class="modal fade" id="modalSolicitud" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-white">Nueva solicitud de cotización</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info small mb-3">
          <strong><i class="fas fa-info-circle"></i> ¿Para qué sirve?</strong><br>
          Envía la misma solicitud de insumos a <strong>varios proveedores a la vez</strong>. El sistema crea una cotización por cada proveedor y las agrupa para que puedas <strong>comparar precios</strong> y elegir la mejor opción.
        </div>
        <div class="mb-3">
          <label class="form-label">Proveedores <span class="text-danger">*</span></label>
          <select class="form-select" id="solicitud_proveedores" multiple size="6"></select>
          <small class="text-muted">Ctrl+clic para seleccionar varios</small>
        </div>
        <div class="mb-3">
          <label class="form-label">Observaciones</label>
          <textarea class="form-control" id="solicitud_observaciones" rows="2"></textarea>
        </div>
        <h6 class="text-primary">Insumos a cotizar</h6>
        <div class="table-responsive">
          <table class="table table-sm" id="tablaInsumosSolicitud">
            <thead>
              <tr>
                <th>Insumo</th>
                <th width="120">Cantidad</th>
                <th width="60"></th>
              </tr>
            </thead>
            <tbody>
              <tr class="row-insumo-solicitud">
                <td>
                  <select class="form-select form-select-sm insumo-select"></select>
                </td>
                <td><input type="number" class="form-control form-control-sm cantidad-input" min="0.01" step="0.01" value="1"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarRowInsumo(this)"><i class="fas fa-times"></i></button></td>
              </tr>
            </tbody>
          </table>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarRowInsumoSolicitud()"><i class="fas fa-plus"></i> Agregar insumo</button>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarSolicitud()">Crear solicitud</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Cotización individual -->
<div class="modal fade" id="modalCotizacion" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-white" id="modalCotizacionTitle">Nueva cotización</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="cotizacion_id">
        <div class="alert alert-light border small mb-3">
          <strong><i class="fas fa-info-circle"></i> ¿Para qué sirve?</strong><br>
          Registra una <strong>cotización de un solo proveedor</strong>. Úsala cuando ya tienes la propuesta de un proveedor específico y quieres dejarla registrada, adjuntar el archivo PDF, y eventualmente <strong>aprobarla para generar una Orden de Compra</strong>.
        </div>
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label">Proveedor <span class="text-danger">*</span></label>
            <select class="form-select" id="cot_proveedor_id"></select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Fecha solicitud</label>
            <input type="date" class="form-control" id="cot_fecha_solicitud" value="<?php echo date('Y-m-d'); ?>">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Observaciones</label>
          <textarea class="form-control" id="cot_observaciones" rows="2"></textarea>
        </div>
        <div id="cot_detalle_container">
          <h6 class="text-primary">Insumos</h6>
          <table class="table table-sm" id="tablaInsumosCot">
            <thead><tr><th>Insumo</th><th>Cant.</th><th>Precio</th><th></th></tr></thead>
            <tbody>
              <tr class="row-insumo-cot">
                <td><select class="form-select form-select-sm insumo-select"></select></td>
                <td><input type="number" class="form-control form-control-sm cantidad-input" min="0.01" step="0.01" value="1"></td>
                <td><input type="number" class="form-control form-control-sm precio-input" min="0" step="0.01" value="0"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarRowInsumo(this)"><i class="fas fa-times"></i></button></td>
              </tr>
            </tbody>
          </table>
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarRowInsumoCot()"><i class="fas fa-plus"></i> Agregar</button>
        </div>
        <div id="cot_archivo_section" class="mt-3" style="display:none;">
          <label class="form-label">Adjuntar cotización (PDF/imagen)</label>
          <input type="file" class="form-control form-control-sm" id="cot_archivo" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
          <div id="cot_archivo_actual" class="small text-muted mt-1"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarCotizacion()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Ver detalle -->
<div class="modal fade" id="modalVerCotizacion" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de cotización</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="verCotizacionBody"></div>
      <div class="modal-footer" id="verCotizacionFooter"></div>
    </div>
  </div>
</div>

<script>
let tablaCotizaciones = null;
let cacheInsumos = [];
let cacheProveedores = [];

$(function() {
  cargarCatalogosCot();
  initTablaCotizaciones();
  $('#filtroEstatusCot, #filtroGrupoCot').on('change', function() { tablaCotizaciones.ajax.reload(); });
});

function initTablaCotizaciones() {
  tablaCotizaciones = $('#tablaCotizaciones').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?= base_url('compras/Cotizaciones/lista_ajax'); ?>',
      type: 'POST',
      data: function(d) {
        d.filtro_estatus = $('#filtroEstatusCot').val();
        d.filtro_grupo = $('#filtroGrupoCot').val();
      }
    },
    order: [[3, 'desc']],
    pageLength: 25,
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
    columns: [
      { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 }, { data: 4 },
      { data: 5 }, { data: 6 }, { data: 7, orderable: false }
    ]
  });
}

function limpiarFiltrosCot() {
  $('#filtroEstatusCot, #filtroGrupoCot').val('');
  tablaCotizaciones.ajax.reload();
}

function cargarCatalogosCot() {
  $.post('<?= base_url('compras/Cotizaciones/insumos_ajax'); ?>', function(res) {
    if (res.success) cacheInsumos = res.insumos || [];
    poblarSelectsInsumo();
  });
  $.post('<?= base_url('compras/Cotizaciones/proveedores_ajax'); ?>', function(res) {
    if (res.success) {
      cacheProveedores = res.proveedores || [];
      poblarSelectProveedores();
    }
  });
}

function opcionesInsumosHtml() {
  let html = '<option value="">Seleccionar...</option>';
  cacheInsumos.forEach(function(i) {
    html += '<option value="' + i.id + '">' + (i.codigo || '') + ' - ' + i.nombre_tecnico + '</option>';
  });
  return html;
}

function poblarSelectsInsumo() {
  $('.insumo-select').html(opcionesInsumosHtml());
}

function poblarSelectProveedores() {
  let html = '<option value="">Seleccionar...</option>';
  let htmlMulti = '';
  cacheProveedores.forEach(function(p) {
    const label = p.razon_social + (p.nombre_comercial ? ' (' + p.nombre_comercial + ')' : '');
    html += '<option value="' + p.id + '">' + label + '</option>';
    htmlMulti += '<option value="' + p.id + '">' + label + '</option>';
  });
  $('#cot_proveedor_id').html(html);
  $('#solicitud_proveedores').html(htmlMulti);
}

function agregarRowInsumoSolicitud() {
  const row = '<tr class="row-insumo-solicitud"><td><select class="form-select form-select-sm insumo-select">' + opcionesInsumosHtml() + '</select></td><td><input type="number" class="form-control form-control-sm cantidad-input" min="0.01" step="0.01" value="1"></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarRowInsumo(this)"><i class="fas fa-times"></i></button></td></tr>';
  $('#tablaInsumosSolicitud tbody').append(row);
}

function agregarRowInsumoCot() {
  const row = '<tr class="row-insumo-cot"><td><select class="form-select form-select-sm insumo-select">' + opcionesInsumosHtml() + '</select></td><td><input type="number" class="form-control form-control-sm cantidad-input" min="0.01" step="0.01" value="1"></td><td><input type="number" class="form-control form-control-sm precio-input" min="0" step="0.01" value="0"></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarRowInsumo(this)"><i class="fas fa-times"></i></button></td></tr>';
  $('#tablaInsumosCot tbody').append(row);
}

function eliminarRowInsumo(btn) {
  const tbody = $(btn).closest('tbody');
  if (tbody.find('tr').length > 1) $(btn).closest('tr').remove();
}

function abrirModalSolicitud() {
  $('#solicitud_observaciones').val('');
  $('#tablaInsumosSolicitud tbody').html('<tr class="row-insumo-solicitud"><td><select class="form-select form-select-sm insumo-select">' + opcionesInsumosHtml() + '</select></td><td><input type="number" class="form-control form-control-sm cantidad-input" min="0.01" step="0.01" value="1"></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarRowInsumo(this)"><i class="fas fa-times"></i></button></td></tr>');
  new bootstrap.Modal('#modalSolicitud').show();
}

function abrirModalCotizacion() {
  $('#cotizacion_id').val('');
  $('#modalCotizacionTitle').text('Nueva cotización');
  $('#cot_proveedor_id').val('').prop('disabled', false);
  $('#cot_fecha_solicitud').val('<?php echo date('Y-m-d'); ?>');
  $('#cot_observaciones').val('');
  $('#cot_archivo_section').hide();
  $('#tablaInsumosCot tbody').html('<tr class="row-insumo-cot"><td><select class="form-select form-select-sm insumo-select">' + opcionesInsumosHtml() + '</select></td><td><input type="number" class="form-control form-control-sm cantidad-input" min="0.01" step="0.01" value="1"></td><td><input type="number" class="form-control form-control-sm precio-input" min="0" step="0.01" value="0"></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarRowInsumo(this)"><i class="fas fa-times"></i></button></td></tr>');
  new bootstrap.Modal('#modalCotizacion').show();
}

function recolectarInsumos(selector) {
  const items = [];
  $(selector).each(function() {
    const insumoId = $(this).find('.insumo-select').val();
    const cantidad = parseFloat($(this).find('.cantidad-input').val()) || 0;
    const precio = parseFloat($(this).find('.precio-input').val()) || 0;
    if (insumoId && cantidad > 0) {
      items.push({ insumo_id: insumoId, cantidad: cantidad, precio_unitario: precio });
    }
  });
  return items;
}

function guardarSolicitud() {
  const proveedores = $('#solicitud_proveedores').val();
  const insumos = recolectarInsumos('#tablaInsumosSolicitud tr.row-insumo-solicitud');
  if (!proveedores || proveedores.length === 0) {
    Swal.fire('Atención', 'Seleccione al menos un proveedor', 'warning');
    return;
  }
  if (insumos.length === 0) {
    Swal.fire('Atención', 'Agregue al menos un insumo', 'warning');
    return;
  }
  $.post('<?= base_url('compras/Cotizaciones/crear_solicitud_ajax'); ?>', {
    proveedores: proveedores,
    insumos: insumos,
    observaciones: $('#solicitud_observaciones').val()
  }, function(res) {
    if (res.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalSolicitud')).hide();
      Swal.fire('Éxito', res.message, 'success');
      tablaCotizaciones.ajax.reload();
      if (res.grupo_folio) {
        setTimeout(function() {
          window.location.href = '<?= base_url('compras/Cotizaciones/comparar/'); ?>' + encodeURIComponent(res.grupo_folio);
        }, 1200);
      }
    } else {
      Swal.fire('Error', res.message || 'No se pudo crear', 'error');
    }
  }, 'json');
}

function guardarCotizacion() {
  const id = $('#cotizacion_id').val();
  const insumos = recolectarInsumos('#tablaInsumosCot tr.row-insumo-cot');
  if (!id && !$('#cot_proveedor_id').val()) {
    Swal.fire('Atención', 'Seleccione proveedor', 'warning');
    return;
  }
  if (!id && insumos.length === 0) {
    Swal.fire('Atención', 'Agregue insumos', 'warning');
    return;
  }

  if (id) {
    const detalles_precios = {};
    $('#tablaInsumosCot tr').each(function() {
      const detId = $(this).data('detalle-id');
      const precio = $(this).find('.precio-input').val();
      if (detId) detalles_precios[detId] = precio;
    });
    $.post('<?= base_url('compras/Cotizaciones/editar_ajax'); ?>', {
      id: id,
      observaciones: $('#cot_observaciones').val(),
      fecha_solicitud: $('#cot_fecha_solicitud').val(),
      detalles_precios: detalles_precios
    }, function(res) {
      if (res.success) {
        subirArchivoCotSiHay(id);
        bootstrap.Modal.getInstance(document.getElementById('modalCotizacion')).hide();
        Swal.fire('Éxito', res.message, 'success');
        tablaCotizaciones.ajax.reload();
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    }, 'json');
  } else {
    $.post('<?= base_url('compras/Cotizaciones/crear_ajax'); ?>', {
      proveedor_id: $('#cot_proveedor_id').val(),
      fecha_solicitud: $('#cot_fecha_solicitud').val(),
      observaciones: $('#cot_observaciones').val(),
      insumos: insumos
    }, function(res) {
      if (res.success) {
        subirArchivoCotSiHay(res.cotizacion_id);
        bootstrap.Modal.getInstance(document.getElementById('modalCotizacion')).hide();
        Swal.fire('Éxito', res.message, 'success');
        tablaCotizaciones.ajax.reload();
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    }, 'json');
  }
}

function subirArchivoCotSiHay(cotId) {
  const fileInput = document.getElementById('cot_archivo');
  if (!fileInput.files.length) return;
  const fd = new FormData();
  fd.append('cotizacion_id', cotId);
  fd.append('archivo', fileInput.files[0]);
  $.ajax({
    url: '<?= base_url('compras/Cotizaciones/subir_archivo_ajax'); ?>',
    type: 'POST',
    data: fd,
    processData: false,
    contentType: false
  });
}

function verCotizacion(id) {
  $.post('<?= base_url('compras/Cotizaciones/get_ajax/'); ?>' + id, function(res) {
    if (!res.success) {
      Swal.fire('Error', res.message, 'error');
      return;
    }
    const c = res.cotizacion;
    let html = '<p><strong>Folio:</strong> ' + c.folio + '</p>';
    html += '<p><strong>Proveedor:</strong> ' + c.razon_social + '</p>';
    html += '<p><strong>Estatus:</strong> ' + c.estatus + ' &nbsp; <strong>Total:</strong> $' + parseFloat(c.total).toFixed(2) + '</p>';
    if (c.observaciones) html += '<p><strong>Observaciones:</strong> ' + c.observaciones + '</p>';
    if (c.archivo_ruta) html += '<p><a href="<?= base_url(); ?>' + c.archivo_ruta + '" target="_blank"><i class="fas fa-paperclip"></i> ' + (c.archivo_nombre || 'Ver archivo') + '</a></p>';
    html += '<table class="table table-sm"><thead><tr><th>Insumo</th><th>Cant.</th><th>P.U.</th><th>Subtotal</th></tr></thead><tbody>';
    (c.detalles || []).forEach(function(d) {
      html += '<tr><td>' + d.codigo + ' - ' + d.nombre_tecnico + '</td><td>' + d.cantidad + '</td><td>$' + parseFloat(d.precio_unitario).toFixed(2) + '</td><td>$' + parseFloat(d.subtotal).toFixed(2) + '</td></tr>';
    });
    html += '</tbody></table>';
    $('#verCotizacionBody').html(html);
    let footer = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
    if (c.grupo_folio) {
      footer += ' <a href="<?= base_url('compras/Cotizaciones/comparar/'); ?>' + encodeURIComponent(c.grupo_folio) + '" class="btn btn-info">Comparar grupo</a>';
    }
    $('#verCotizacionFooter').html(footer);
    new bootstrap.Modal('#modalVerCotizacion').show();
  }, 'json');
}

function editarCotizacion(id) {
  $.post('<?= base_url('compras/Cotizaciones/get_ajax/'); ?>' + id, function(res) {
    if (!res.success) return;
    const c = res.cotizacion;
    $('#cotizacion_id').val(c.id);
    $('#modalCotizacionTitle').text('Editar ' + c.folio);
    $('#cot_proveedor_id').val(c.proveedor_id).prop('disabled', true);
    $('#cot_fecha_solicitud').val(c.fecha_solicitud);
    $('#cot_observaciones').val(c.observaciones || '');
    $('#cot_archivo_section').show();
    $('#cot_archivo_actual').html(c.archivo_nombre ? 'Actual: ' + c.archivo_nombre : 'Sin archivo');
    let rows = '';
    (c.detalles || []).forEach(function(d) {
      rows += '<tr class="row-insumo-cot" data-detalle-id="' + d.id + '"><td>' + d.codigo + ' - ' + d.nombre_tecnico + '</td><td>' + d.cantidad + '</td><td><input type="number" class="form-control form-control-sm precio-input" min="0" step="0.01" value="' + d.precio_unitario + '"></td><td></td></tr>';
    });
    $('#tablaInsumosCot tbody').html(rows);
    new bootstrap.Modal('#modalCotizacion').show();
  }, 'json');
}

function aprobarCotizacion(id) {
  Swal.fire({
    title: 'Aprobar cotización',
    text: 'Se generará una Orden de Compra en Borrador con los precios de esta cotización.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Aprobar y generar OC',
    cancelButtonText: 'Cancelar'
  }).then(function(r) {
    if (!r.isConfirmed) return;
    $.post('<?= base_url('compras/Cotizaciones/aprobar_ajax'); ?>', { id: id }, function(res) {
      if (res.success) {
        Swal.fire('Éxito', res.message, 'success');
        tablaCotizaciones.ajax.reload();
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    }, 'json');
  });
}

function eliminarCotizacion(id) {
  Swal.fire({
    title: 'Eliminar cotización',
    text: '¿Confirma eliminar esta cotización?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Eliminar',
    cancelButtonText: 'Cancelar'
  }).then(function(r) {
    if (!r.isConfirmed) return;
    $.post('<?= base_url('compras/Cotizaciones/eliminar_ajax'); ?>', { id: id }, function(res) {
      if (res.success) {
        Swal.fire('Eliminada', res.message, 'success');
        tablaCotizaciones.ajax.reload();
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    }, 'json');
  });
}
</script>
