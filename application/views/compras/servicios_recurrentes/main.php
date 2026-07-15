<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb ?? '']); ?>

  <div class="row mb-3">
    <div class="col-auto">
      <h3><i class="fas fa-sync-alt me-2"></i><?= htmlspecialchars($headTitle) ?></h3>
      <p class="text-muted mb-0">Internet, basura, soporte técnico y costos fijos — seguimiento mensual desde Compras (vinculable a Contabilidad)</p>
    </div>
    <div class="col-auto ms-auto">
      <?php if (tiene_permiso('compras_servicios_recurrentes')): ?>
      <button type="button" class="btn btn-primary" onclick="abrirModalNuevoServicio()">
        <i class="fas fa-plus me-1"></i> Nuevo servicio
      </button>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($resumen)): ?>
  <div class="row mb-3" id="resumenCards">
    <div class="col-md-3">
      <div class="card"><div class="card-body">
        <h6 class="text-muted">Pagos del mes</h6>
        <h3 class="mb-0" id="res_total"><?= (int)$resumen->total ?></h3>
      </div></div>
    </div>
    <div class="col-md-3">
      <div class="card border-warning"><div class="card-body">
        <h6 class="text-muted">Pendientes / Vencidos</h6>
        <h3 class="mb-0 text-warning" id="res_pendientes"><?= (int)$resumen->pendientes + (int)($resumen->vencidos ?? 0) ?></h3>
      </div></div>
    </div>
    <div class="col-md-3">
      <div class="card border-success"><div class="card-body">
        <h6 class="text-muted">Pagados</h6>
        <h3 class="mb-0 text-success" id="res_pagados"><?= (int)$resumen->pagados ?></h3>
      </div></div>
    </div>
    <div class="col-md-3">
      <div class="card border-danger"><div class="card-body">
        <h6 class="text-muted">Por pagar</h6>
        <h3 class="mb-0 text-danger" id="res_por_pagar">$<?= number_format((float)$resumen->total_monto - (float)$resumen->monto_pagado, 2) ?></h3>
      </div></div>
    </div>
  </div>
  <?php endif; ?>

  <ul class="nav nav-tabs mb-0">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabServicios">Catálogo</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabSeguimiento" onclick="cargarSeguimientoMensual()">Seguimiento mensual</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPagos" onclick="recargarPagos()">Calendario del mes</button></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tabServicios">
      <div class="card border-top-0 rounded-0 rounded-bottom">
        <div class="card-header bg-white">
          <div class="row g-2 align-items-end">
            <div class="col-md-4">
              <label class="form-label small mb-0">Categoría</label>
              <select class="form-select form-select-sm" id="cat_filtro_categoria" onchange="cargarCatalogoServicios()">
                <option value="">Todas las categorías</option>
                <option value="Telecomunicaciones">Telecomunicaciones</option>
                <option value="Suscripciones">Suscripciones</option>
                <option value="Soporte Técnico">Soporte Técnico</option>
                <option value="Recolección de Basura">Recolección de Basura</option>
                <option value="Servicios Públicos">Servicios Públicos</option>
                <option value="Renta">Renta</option>
                <option value="Seguros">Seguros</option>
                <option value="Mantenimiento">Mantenimiento</option>
                <option value="Otros">Otros</option>
              </select>
            </div>
            <div class="col-md-5">
              <label class="form-label small mb-0">Proveedor</label>
              <select class="form-select form-select-sm" id="cat_filtro_proveedor" onchange="cargarCatalogoServicios()">
                <option value="">Todos los proveedores</option>
                <?php foreach (($proveedores_activos ?? []) as $p): ?>
                <option value="<?= $p->id ?>"><?= htmlspecialchars($p->razon_social) ?> (<?= htmlspecialchars($p->tipo_proveedor) ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3 text-muted small">
              <i class="fas fa-info-circle"></i> Clic en el nombre o en <strong>Editar</strong> para cambiar monto, día de pago y proveedor.
            </div>
          </div>
        </div>
        <div class="card-body">
          <div id="cat_loading" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Cargando catálogo...</div>
          <div class="table-responsive" id="cat_container" style="display:none;">
          <table id="tablaServicios" class="table table-striped w-100 mb-0">
            <thead><tr>
              <th>Servicio</th><th style="min-width:180px">Acciones</th><th>Categoría</th><th>Proveedor</th><th>Frecuencia</th><th>Vence</th><th>Monto</th><th>Estatus</th>
            </tr></thead>
            <tbody id="tbodyServicios"></tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="tabSeguimiento">
      <div class="card border-top-0 rounded-0 rounded-bottom">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <strong>Seguimiento mensual por servicio</strong>
            <span class="text-muted small ms-2">Últimos 12 meses — clic en celda para pagar</span>
          </div>
          <div class="d-flex gap-2">
            <select class="form-select form-select-sm" id="seg_filtro_proveedor" style="width:200px" onchange="cargarSeguimientoMensual()">
              <option value="">Todos los proveedores</option>
              <?php foreach (($proveedores_servicios ?? []) as $p): ?>
              <option value="<?= $p->id ?>"><?= htmlspecialchars($p->razon_social) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if (tiene_permiso('compras_servicios_recurrentes')): ?>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="generarPeriodosFuturos()">
              <i class="fas fa-calendar-plus"></i> Generar periodos
            </button>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-body p-0">
          <div id="seg_loading" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Cargando seguimiento...</div>
          <div class="table-responsive" id="seg_container" style="display:none;">
            <table class="table table-sm table-bordered mb-0" id="tablaSeguimiento">
              <thead class="table-light" id="seg_thead"></thead>
              <tbody id="seg_tbody"></tbody>
            </table>
          </div>
        </div>
        <div class="card-footer small text-muted">
          <i class="fas fa-info-circle"></i> Los pagos marcados aquí quedan listos para reflejarse en <strong>Contabilidad</strong> (póliza vía módulo contable — campo <code>poliza_id</code>).
        </div>
      </div>
    </div>
    <div class="tab-pane fade" id="tabPagos">
      <div class="card border-top-0 rounded-0 rounded-bottom">
        <div class="card-header bg-white">
          <div class="row g-2 align-items-end">
            <div class="col-md-3">
              <label class="form-label small mb-0">Periodo</label>
              <input type="month" class="form-control form-control-sm" id="filtro_periodo" value="<?= date('Y-m') ?>" onchange="recargarPagos()">
            </div>
            <div class="col-md-4">
              <label class="form-label small mb-0">Proveedor</label>
              <select class="form-select form-select-sm" id="filtro_proveedor_pago" onchange="recargarPagos()">
                <option value="">Todos</option>
                <?php foreach (($proveedores_servicios ?? []) as $p): ?>
                <option value="<?= $p->id ?>"><?= htmlspecialchars($p->razon_social) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>
        <div class="card-body">
          <div id="pag_loading" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Cargando pagos del mes...</div>
          <div class="table-responsive" id="pag_container" style="display:none;">
          <table id="tablaPagos" class="table table-striped w-100 mb-0">
            <thead><tr>
              <th>Servicio</th><th>Proveedor</th><th>Vencimiento</th><th>Monto</th><th>Fecha pago</th><th>Estatus</th><th>Acciones</th>
            </tr></thead>
            <tbody id="tbodyPagos"></tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal nuevo / editar servicio -->
<div class="modal fade" id="modalNuevoServicio" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="srv_modal_titulo">Nuevo servicio recurrente</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="formNuevoServicio">
          <input type="hidden" name="servicio_id" id="srv_id" value="">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Nombre del servicio *</label>
              <input type="text" class="form-control" name="nombre_servicio" required placeholder="Ej. Internet empresarial 100 Mbps">
            </div>
            <div class="col-md-4">
              <label class="form-label">Categoría *</label>
              <select class="form-select" name="tipo_servicio" required>
                <option value="Telecomunicaciones">Telecomunicaciones</option>
                <option value="Suscripciones">Suscripciones</option>
                <option value="Soporte Técnico">Soporte Técnico</option>
                <option value="Recolección de Basura">Recolección de Basura</option>
                <option value="Servicios Públicos">Servicios Públicos</option>
                <option value="Renta">Renta</option>
                <option value="Seguros">Seguros</option>
                <option value="Mantenimiento">Mantenimiento</option>
                <option value="Otros">Otros</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Proveedor *</label>
              <select class="form-select" name="proveedor_id" id="srv_proveedor_id" required>
                <option value="">Seleccione proveedor</option>
                <?php foreach (($proveedores_activos ?? []) as $p): ?>
                <option value="<?= $p->id ?>"><?= htmlspecialchars($p->razon_social) ?> — <?= htmlspecialchars($p->tipo_proveedor) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Vincula el servicio al proveedor que factura (Internet, basura, soporte, etc.)</div>
            </div>
            <div class="col-md-3">
              <label class="form-label">Día de pago *</label>
              <input type="number" class="form-control" name="dia_vencimiento" min="1" max="31" value="5" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Monto mensual *</label>
              <input type="number" step="0.01" class="form-control" name="monto_estimado" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Frecuencia</label>
              <select class="form-select" name="frecuencia">
                <option value="Mensual" selected>Mensual</option>
                <option value="Bimestral">Bimestral</option>
                <option value="Trimestral">Trimestral</option>
                <option value="Anual">Anual</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">Descripción</label>
              <input type="text" class="form-control" name="descripcion" placeholder="Detalle del servicio">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="srv_btn_guardar" onclick="guardarServicio()"><i class="fas fa-save me-1"></i> Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal registrar pago servicio -->
<div class="modal fade" id="modalPagoServicio" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pago_srv_modal_titulo">Registrar pago de servicio</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="pago_srv_id">
        <input type="hidden" id="pago_srv_servicio_id">
        <input type="hidden" id="pago_srv_solo_comprobante" value="0">

        <div id="pago_srv_info" class="alert alert-light border small mb-3 d-none"></div>

        <div id="pago_srv_campos_pago">
          <div class="mb-3">
            <label class="form-label">Fecha de pago *</label>
            <input type="date" class="form-control" id="pago_srv_fecha" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Monto *</label>
            <input type="number" step="0.01" class="form-control" id="pago_srv_monto">
          </div>
          <div class="mb-3">
            <label class="form-label">Referencia / folio transferencia</label>
            <input type="text" class="form-control" id="pago_srv_referencia" placeholder="TRF-123456">
          </div>
          <div class="mb-3">
            <label class="form-label">Notas</label>
            <textarea class="form-control" id="pago_srv_notas" rows="2" placeholder="Observaciones del pago"></textarea>
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">Comprobante de pago</label>
          <input type="file" class="form-control" id="pago_srv_comprobante" accept=".pdf,.jpg,.jpeg,.png,.webp">
          <div class="form-text">PDF o imagen (máx. 10 MB) — transferencia, factura, recibo, etc.</div>
        </div>
        <div id="pago_srv_comprobante_actual" class="small"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-outline-danger d-none" id="btnEliminarComprobanteSrv" onclick="eliminarComprobanteServicio()">
          <i class="fas fa-trash"></i> Quitar comprobante
        </button>
        <button type="button" class="btn btn-success" id="btnConfirmarPagoSrv" onclick="confirmarPagoServicio()">
          <i class="fas fa-check me-1"></i> <span id="pago_srv_btn_label">Registrar pago</span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: detalle seguimiento servicio -->
<div class="modal fade" id="modalSeguimientoServicio" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="seg_modal_titulo">Seguimiento mensual</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="seg_modal_body"></div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
    </div>
  </div>
</div>

<script>
(function() {
  const CSRF = {
    name: '<?= $this->security->get_csrf_token_name() ?>',
    hash: '<?= $this->security->get_csrf_hash() ?>'
  };
  const PUEDE_PAGOS = <?= tiene_permiso('compras_pagos') ? 'true' : 'false' ?>;
  const PUEDE_GESTION = <?= tiene_permiso('compras_servicios_recurrentes') ? 'true' : 'false' ?>;
  let seguimientoCargado = false;

  function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function postJson(url, data, callback) {
    data[CSRF.name] = CSRF.hash;
    data.peticion = 'ajax';
    $.post(url, data, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch (e) { r = null; }
      callback(r);
    }).fail(function() { callback(null); });
  }

  function badgeCelda(estatus) {
    const map = { Pagado: 'success', Pendiente: 'warning', Vencido: 'danger', Cancelado: 'secondary', 'Sin registro': 'light' };
    return map[estatus] || 'secondary';
  }

  window.cargarSeguimientoMensual = function() {
    $('#seg_loading').show();
    $('#seg_container').hide();
    $.post('<?= base_url('compras/ServiciosRecurrentes/seguimiento_mensual_ajax') ?>', {
      meses: 12,
      proveedor_id: $('#seg_filtro_proveedor').val(),
      [CSRF.name]: CSRF.hash
    }, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) { return; }
      $('#seg_loading').hide();
      if (!r.success || !r.servicios) {
        toastSrv('danger', 'Error', 'No se pudo cargar el seguimiento');
        return;
      }
      let head = '<tr><th>Servicio</th><th>Proveedor</th><th>Tipo</th>';
      (r.periodos || []).forEach(function(p) {
        const parts = p.split('-');
        const labels = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        head += '<th class="text-center small">' + labels[parseInt(parts[1],10)] + '<br><span class="text-muted">' + parts[0].substr(2) + '</span></th>';
      });
      head += '</tr>';
      $('#seg_thead').html(head);

      let body = '';
      if (!r.servicios.length) {
        body = '<tr><td colspan="20" class="text-center text-muted py-4">Sin servicios. Cree uno con «Nuevo servicio».</td></tr>';
      }
      r.servicios.forEach(function(s) {
        body += '<tr><td><strong>' + escapeHtml(s.nombre_servicio) + '</strong><br><small class="text-muted">Día ' + escapeHtml(s.dia_vencimiento) + '</small></td>';
        body += '<td class="small">' + escapeHtml(s.proveedor_nombre || '—') + '</td>';
        body += '<td><span class="badge bg-secondary">' + escapeHtml(s.tipo_servicio) + '</span></td>';
        s.meses.forEach(function(m) {
          const cls = badgeCelda(m.estatus);
          let title = m.estatus + (m.fecha_pago ? ' — ' + m.fecha_pago : '');
          let click = '';
          if (PUEDE_PAGOS && m.pago_id && (m.estatus === 'Pendiente' || m.estatus === 'Vencido')) {
            click = ' style="cursor:pointer" onclick="abrirModalPagoServicio(' + m.pago_id + ', null, ' + m.monto + ')" title="Clic para marcar pagado"';
          }
          let extra = m.poliza_id ? '<br><i class="fas fa-book text-info" title="Póliza contable"></i>' : '';
          body += '<td class="text-center p-1"' + click + '><span class="badge bg-' + cls + '">' + m.estatus.substr(0,4) + '</span>' + extra + '</td>';
        });
        body += '</tr>';
      });
      $('#seg_tbody').html(body);
      $('#seg_container').show();
      seguimientoCargado = true;
    });
  };

  window.verSeguimientoServicio = function(servicioId) {
    $.post('<?= base_url('compras/ServiciosRecurrentes/historial_servicio_ajax') ?>', {
      servicio_id: servicioId, [CSRF.name]: CSRF.hash
    }, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) { return; }
      if (!r.success) { toastSrv('danger', 'Error', r.message); return; }
      const h = r.historial;
      $('#seg_modal_titulo').text(h.servicio.nombre_servicio);
      let html = '<table class="table table-sm"><thead><tr><th>Mes</th><th>Vence</th><th>Monto</th><th>Estatus</th><th>Pago</th><th></th></tr></thead><tbody>';
      (h.meses || []).forEach(function(m) {
        html += '<tr><td>' + m.periodo_label + '</td><td>' + (m.fecha_vencimiento || '—') + '</td>';
        html += '<td>$' + parseFloat(m.monto).toFixed(2) + '</td>';
        html += '<td><span class="badge bg-' + badgeCelda(m.estatus) + '">' + m.estatus + '</span></td>';
        html += '<td>' + (m.fecha_pago || '—') + '</td><td>';
        if (PUEDE_PAGOS && m.pago_id && m.estatus !== 'Pagado') {
          html += '<button class="btn btn-sm btn-success" onclick="abrirModalPagoServicio(' + m.pago_id + ')"><i class="fas fa-check"></i></button>';
        }
        html += '</td></tr>';
      });
      html += '</tbody></table>';
      $('#seg_modal_body').html(html);
      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalSeguimientoServicio')).show();
    });
  };

  window.generarPeriodosFuturos = function() {
    if (!PUEDE_GESTION) return;
    $.post('<?= base_url('compras/ServiciosRecurrentes/generar_periodos_ajax') ?>', {
      meses: 12, [CSRF.name]: CSRF.hash
    }, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) {}
      toastSrv(r.success ? 'success' : 'danger', r.success ? 'Listo' : 'Error', r.message || '');
      if (r.success) cargarSeguimientoMensual();
    });
  };

  function toastSrv(type, title, msg) {
    if (typeof showErpToast === 'function') {
      showErpToast({ type: type, module: 'Servicios', title: title, message: msg });
    } else {
      alert(title + ': ' + msg);
    }
  }

  window.cargarCatalogoServicios = function() {
    $('#cat_loading').show();
    $('#cat_container').hide();
    postJson('<?= base_url('compras/ServiciosRecurrentes/lista_servicios_ajax') ?>', {
      draw: 1,
      proveedor_id: $('#cat_filtro_proveedor').val(),
      tipo_servicio: $('#cat_filtro_categoria').val()
    }, function(r) {
      $('#cat_loading').hide();
      if (!r || !r.data) {
        $('#tbodyServicios').html('<tr><td colspan="8" class="text-center text-danger py-4">No se pudo cargar el catálogo.</td></tr>');
        $('#cat_container').show();
        return;
      }
      if (!r.data.length) {
        $('#tbodyServicios').html('<tr><td colspan="8" class="text-center text-muted py-4">Sin servicios. Use «Nuevo servicio» para agregar uno.</td></tr>');
      } else {
        let html = '';
        r.data.forEach(function(row) {
          html += '<tr>';
          row.forEach(function(cell) { html += '<td>' + cell + '</td>'; });
          html += '</tr>';
        });
        $('#tbodyServicios').html(html);
      }
      $('#cat_container').show();
    });
  };

  window.cargarCalendarioPagos = function() {
    $('#pag_loading').show();
    $('#pag_container').hide();
    postJson('<?= base_url('compras/ServiciosRecurrentes/lista_pagos_ajax') ?>', {
      draw: 1,
      periodo: $('#filtro_periodo').val(),
      proveedor_id: $('#filtro_proveedor_pago').val()
    }, function(r) {
      $('#pag_loading').hide();
      if (!r || !r.data) {
        $('#tbodyPagos').html('<tr><td colspan="7" class="text-center text-danger py-4">No se pudo cargar el calendario.</td></tr>');
        $('#pag_container').show();
        return;
      }
      if (!r.data.length) {
        $('#tbodyPagos').html('<tr><td colspan="7" class="text-center text-muted py-4">Sin pagos para el periodo seleccionado.</td></tr>');
      } else {
        let html = '';
        r.data.forEach(function(row) {
          html += '<tr>';
          row.forEach(function(cell) { html += '<td>' + cell + '</td>'; });
          html += '</tr>';
        });
        $('#tbodyPagos').html(html);
      }
      $('#pag_container').show();
    });
  };

  function renderComprobanteActual(pago) {
    const box = $('#pago_srv_comprobante_actual');
    const btnDel = $('#btnEliminarComprobanteSrv');
    $('#pago_srv_comprobante').val('');
    if (pago && pago.comprobante_url) {
      box.html('<a href="' + pago.comprobante_url + '" target="_blank" class="text-primary"><i class="fas fa-paperclip"></i> ' + escapeHtml(pago.comprobante_nombre || 'Ver comprobante') + '</a>');
      btnDel.removeClass('d-none');
    } else {
      box.empty();
      btnDel.addClass('d-none');
    }
  }

  function cargarDatosPagoModal(pagoId, servicioId, monto, soloComprobante) {
    document.getElementById('pago_srv_id').value = pagoId || '';
    document.getElementById('pago_srv_servicio_id').value = servicioId || '';
    document.getElementById('pago_srv_solo_comprobante').value = soloComprobante ? '1' : '0';
    $('#pago_srv_info').addClass('d-none').empty();
    renderComprobanteActual(null);
    $('#pago_srv_campos_pago input, #pago_srv_campos_pago textarea').prop('disabled', false);

    if (!pagoId) {
      if (monto) $('#pago_srv_monto').val(monto);
      $('#pago_srv_campos_pago').show();
      $('#pago_srv_modal_titulo').text('Registrar pago de servicio');
      $('#pago_srv_btn_label').text('Registrar pago');
      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPagoServicio')).show();
      return;
    }

    $.post('<?= base_url('compras/ServiciosRecurrentes/get_pago_ajax') ?>', {
      pago_id: pagoId,
      [CSRF.name]: CSRF.hash
    }, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) { return; }
      if (!r.success || !r.pago) {
        toastSrv('danger', 'Error', r.message || 'No se pudo cargar el pago');
        return;
      }
      const p = r.pago;
      $('#pago_srv_info').removeClass('d-none').html(
        '<strong>' + escapeHtml(p.nombre_servicio) + '</strong><br>' +
        escapeHtml(p.proveedor_nombre || '—') + ' · Periodo ' + escapeHtml(p.periodo) +
        ' · Vence ' + escapeHtml(p.fecha_vencimiento || '—')
      );
      $('#pago_srv_fecha').val(p.fecha_pago || '<?= date('Y-m-d') ?>');
      $('#pago_srv_monto').val(p.monto);
      $('#pago_srv_referencia').val(p.referencia || '');
      $('#pago_srv_notas').val(p.notas || '');
      renderComprobanteActual(p);

      const yaPagado = p.estatus === 'Pagado';
      if (yaPagado && soloComprobante) {
        $('#pago_srv_campos_pago').hide();
        $('#pago_srv_modal_titulo').text('Comprobante de pago');
        $('#pago_srv_btn_label').text(p.comprobante_url ? 'Reemplazar comprobante' : 'Subir comprobante');
        document.getElementById('pago_srv_solo_comprobante').value = '1';
      } else if (yaPagado) {
        $('#pago_srv_campos_pago').show();
        $('#pago_srv_campos_pago input, #pago_srv_campos_pago textarea').prop('disabled', true);
        $('#pago_srv_modal_titulo').text('Detalle del pago');
        $('#pago_srv_btn_label').text(p.comprobante_url ? 'Reemplazar comprobante' : 'Subir comprobante');
        document.getElementById('pago_srv_solo_comprobante').value = p.comprobante_url ? '1' : '0';
      } else {
        $('#pago_srv_campos_pago').show();
        $('#pago_srv_modal_titulo').text('Registrar pago de servicio');
        $('#pago_srv_btn_label').text('Registrar pago');
      }

      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPagoServicio')).show();
    });
  }

  window.abrirModalNuevoServicio = function(proveedorId) {
    const form = document.getElementById('formNuevoServicio');
    form.reset();
    document.getElementById('srv_id').value = '';
    $('#srv_modal_titulo').text('Nuevo servicio recurrente');
    $('#srv_btn_guardar').html('<i class="fas fa-save me-1"></i> Guardar');
    if (proveedorId) document.getElementById('srv_proveedor_id').value = proveedorId;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevoServicio')).show();
  };

  window.abrirModalEditarServicio = function(servicioId) {
    if (!PUEDE_GESTION) {
      toastSrv('warning', 'Sin permiso', 'No puedes editar servicios');
      return;
    }

    $.post('<?= base_url('compras/ServiciosRecurrentes/get_servicio_ajax') ?>', {
      servicio_id: servicioId,
      [CSRF.name]: CSRF.hash
    }, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) { return; }
      if (!r.success || !r.servicio) {
        toastSrv('danger', 'Error', r.message || 'No se pudo cargar el servicio');
        return;
      }

      const s = r.servicio;
      const form = document.getElementById('formNuevoServicio');
      form.reset();
      document.getElementById('srv_id').value = s.id;
      form.nombre_servicio.value = s.nombre_servicio || '';
      form.tipo_servicio.value = s.tipo_servicio || 'Otros';
      form.frecuencia.value = s.frecuencia || 'Mensual';
      form.dia_vencimiento.value = s.dia_vencimiento || 5;
      form.monto_estimado.value = s.monto_estimado || '';
      form.descripcion.value = s.descripcion || '';

      const selProv = document.getElementById('srv_proveedor_id');
      if (s.proveedor_id) {
        const existe = Array.from(selProv.options).some(function(opt) { return opt.value === String(s.proveedor_id); });
        if (!existe && s.proveedor_nombre) {
          const opt = document.createElement('option');
          opt.value = s.proveedor_id;
          opt.textContent = s.proveedor_nombre;
          selProv.appendChild(opt);
        }
        selProv.value = String(s.proveedor_id);
      } else {
        selProv.value = '';
      }

      $('#srv_modal_titulo').text('Editar servicio recurrente');
      $('#srv_btn_guardar').html('<i class="fas fa-save me-1"></i> Guardar cambios');
      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevoServicio')).show();
    });
  };

  window.abrirModalPagoServicio = function(pagoId, servicioId, monto, soloComprobante) {
    cargarDatosPagoModal(pagoId, servicioId, monto, !!soloComprobante);
  };

  window.guardarServicio = function() {
    const form = document.getElementById('formNuevoServicio');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const servicioId = document.getElementById('srv_id').value;
    const url = servicioId
      ? '<?= base_url('compras/ServiciosRecurrentes/actualizar_servicio_ajax') ?>'
      : '<?= base_url('compras/ServiciosRecurrentes/crear_servicio_ajax') ?>';
    const data = $(form).serializeArray();
    data.push({name: CSRF.name, value: CSRF.hash});
    $.post(url, data, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) {}
      if (r.success) {
        toastSrv('success', 'Listo', r.message);
        bootstrap.Modal.getInstance(document.getElementById('modalNuevoServicio')).hide();
        cargarCatalogoServicios();
        recargarPagos();
        if (seguimientoCargado) cargarSeguimientoMensual();
      } else toastSrv('danger', 'Error', r.message || 'No se pudo guardar');
    });
  };

  window.confirmarPagoServicio = function() {
    if (!PUEDE_PAGOS) { toastSrv('warning', 'Sin permiso', 'No puedes registrar pagos'); return; }

    const soloComprobanteFlag = $('#pago_srv_solo_comprobante').val() === '1';
    const pagoId = $('#pago_srv_id').val();
    const fileInput = document.getElementById('pago_srv_comprobante');
    const tieneArchivo = fileInput && fileInput.files && fileInput.files.length > 0;
    const soloComprobante = soloComprobanteFlag || ($('#pago_srv_fecha').prop('disabled') && tieneArchivo);

    if (soloComprobante && !tieneArchivo) {
      toastSrv('warning', 'Archivo requerido', 'Seleccione un comprobante para subir');
      return;
    }

    const formData = new FormData();
    formData.append('pago_id', pagoId);
    formData.append('servicio_id', $('#pago_srv_servicio_id').val());
    formData.append('periodo', $('#filtro_periodo').val() || '<?= date('Y-m') ?>');
    formData.append('solo_comprobante', soloComprobante ? '1' : '0');
    formData.append('fecha_pago', $('#pago_srv_fecha').val());
    formData.append('monto', $('#pago_srv_monto').val());
    formData.append('referencia', $('#pago_srv_referencia').val());
    formData.append('notas', $('#pago_srv_notas').val());
    formData.append(CSRF.name, CSRF.hash);
    if (tieneArchivo) formData.append('comprobante', fileInput.files[0]);

    $('#btnConfirmarPagoSrv').prop('disabled', true);
    $.ajax({
      url: '<?= base_url('compras/ServiciosRecurrentes/registrar_pago_ajax') ?>',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(r) {
        try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) {}
        $('#btnConfirmarPagoSrv').prop('disabled', false);
        if (r.success) {
          toastSrv('success', 'Listo', r.message);
          bootstrap.Modal.getInstance(document.getElementById('modalPagoServicio')).hide();
          recargarPagos();
          cargarCatalogoServicios();
          if (seguimientoCargado) cargarSeguimientoMensual();
        } else {
          toastSrv('danger', 'Error', r.message || 'No se pudo registrar');
        }
      },
      error: function() {
        $('#btnConfirmarPagoSrv').prop('disabled', false);
        toastSrv('danger', 'Error de conexión', 'No se pudo procesar el pago');
      }
    });
  };

  window.eliminarComprobanteServicio = function() {
    const pagoId = $('#pago_srv_id').val();
    if (!pagoId) return;
    $.post('<?= base_url('compras/ServiciosRecurrentes/eliminar_comprobante_ajax') ?>', {
      pago_id: pagoId,
      [CSRF.name]: CSRF.hash
    }, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) {}
      if (r.success) {
        toastSrv('success', 'Listo', r.message);
        renderComprobanteActual(null);
        recargarPagos();
      } else toastSrv('danger', 'Error', r.message || 'No se pudo eliminar');
    });
  };

  window.recargarPagos = function() {
    cargarCalendarioPagos();
    const periodo = $('#filtro_periodo').val();
    $.post('<?= base_url('compras/ServiciosRecurrentes/resumen_ajax') ?>', { periodo, [CSRF.name]: CSRF.hash }, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) {}
      if (r.success && r.resumen) {
        $('#res_total').text(r.resumen.total);
        $('#res_pendientes').text((+r.resumen.pendientes) + (+r.resumen.vencidos || 0));
        $('#res_pagados').text(r.resumen.pagados);
        $('#res_por_pagar').text('$' + (parseFloat(r.resumen.total_monto) - parseFloat(r.resumen.monto_pagado)).toFixed(2));
      }
    });
  };

  function initServiciosRecurrentes() {
    cargarCatalogoServicios();

    $('button[data-bs-target="#tabPagos"]').on('shown.bs.tab', function() {
      cargarCalendarioPagos();
    });

    $('button[data-bs-target="#tabServicios"]').on('shown.bs.tab', function() {
      cargarCatalogoServicios();
    });

    const params = new URLSearchParams(window.location.search);
    if (params.get('proveedor_id')) {
      $('#filtro_proveedor_pago').val(params.get('proveedor_id'));
      $('#srv_proveedor_id').val(params.get('proveedor_id'));
      $('#seg_filtro_proveedor').val(params.get('proveedor_id'));
    }
    if (params.get('nuevo') === '1') {
      abrirModalNuevoServicio(params.get('proveedor_id'));
    }
  }

  if (typeof jQuery !== 'undefined') {
    $(document).ready(initServiciosRecurrentes);
  } else {
    window.addEventListener('load', function() {
      if (typeof jQuery !== 'undefined') {
        $(document).ready(initServiciosRecurrentes);
      }
    });
  }
})();
</script>
