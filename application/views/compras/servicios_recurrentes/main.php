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
        <div class="card-body">
          <table id="tablaServicios" class="table table-striped w-100">
            <thead><tr>
              <th>Servicio</th><th>Tipo</th><th>Proveedor</th><th>Frecuencia</th><th>Vence</th><th>Monto</th><th>Estatus</th><th></th>
            </tr></thead>
          </table>
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
          <table id="tablaPagos" class="table table-striped w-100">
            <thead><tr>
              <th>Servicio</th><th>Proveedor</th><th>Vencimiento</th><th>Monto</th><th>Fecha pago</th><th>Estatus</th><th></th>
            </tr></thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal nuevo servicio -->
<div class="modal fade" id="modalNuevoServicio" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Nuevo servicio recurrente</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="formNuevoServicio">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label">Nombre del servicio *</label>
              <input type="text" class="form-control" name="nombre_servicio" required placeholder="Ej. Internet empresarial 100 Mbps">
            </div>
            <div class="col-md-4">
              <label class="form-label">Tipo *</label>
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
              <label class="form-label">Proveedor</label>
              <select class="form-select" name="proveedor_id" id="srv_proveedor_id">
                <option value="">Sin proveedor vinculado</option>
                <?php foreach (($proveedores_servicios ?? []) as $p): ?>
                <option value="<?= $p->id ?>"><?= htmlspecialchars($p->razon_social) ?></option>
                <?php endforeach; ?>
              </select>
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
        <button type="button" class="btn btn-primary" onclick="guardarServicio()"><i class="fas fa-save me-1"></i> Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal registrar pago servicio -->
<div class="modal fade" id="modalPagoServicio" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Registrar pago de servicio</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" id="pago_srv_id">
        <input type="hidden" id="pago_srv_servicio_id">
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
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="confirmarPagoServicio()"><i class="fas fa-check me-1"></i> Marcar pagado</button>
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
  let tablaServicios, tablaPagos;
  let seguimientoCargado = false;

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
        body += '<tr><td><strong>' + s.nombre_servicio + '</strong><br><small class="text-muted">Día ' + s.dia_vencimiento + '</small></td>';
        body += '<td class="small">' + (s.proveedor_nombre || '—') + '</td>';
        body += '<td><span class="badge bg-secondary">' + s.tipo_servicio + '</span></td>';
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
    if (typeof showErpToast === 'function') showErpToast(type, title, msg);
    else alert(title + ': ' + msg);
  }

  window.abrirModalNuevoServicio = function(proveedorId) {
    document.getElementById('formNuevoServicio').reset();
    if (proveedorId) document.getElementById('srv_proveedor_id').value = proveedorId;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNuevoServicio')).show();
  };

  window.abrirModalPagoServicio = function(pagoId, servicioId, monto) {
    document.getElementById('pago_srv_id').value = pagoId || '';
    document.getElementById('pago_srv_servicio_id').value = servicioId || '';
    if (monto) document.getElementById('pago_srv_monto').value = monto;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPagoServicio')).show();
  };

  window.guardarServicio = function() {
    const form = document.getElementById('formNuevoServicio');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    const data = $(form).serializeArray();
    data.push({name: CSRF.name, value: CSRF.hash});
    $.post('<?= base_url('compras/ServiciosRecurrentes/crear_servicio_ajax') ?>', data, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) {}
      if (r.success) {
        toastSrv('success', 'Listo', r.message);
        bootstrap.Modal.getInstance(document.getElementById('modalNuevoServicio')).hide();
        if (tablaServicios) tablaServicios.ajax.reload();
        recargarPagos();
      } else toastSrv('danger', 'Error', r.message || 'No se pudo guardar');
    });
  };

  window.confirmarPagoServicio = function() {
    if (!PUEDE_PAGOS) { toastSrv('warning', 'Sin permiso', 'No puedes registrar pagos'); return; }
    $.post('<?= base_url('compras/ServiciosRecurrentes/registrar_pago_ajax') ?>', {
      pago_id: $('#pago_srv_id').val(),
      servicio_id: $('#pago_srv_servicio_id').val(),
      periodo: $('#filtro_periodo').val() || '<?= date('Y-m') ?>',
      fecha_pago: $('#pago_srv_fecha').val(),
      monto: $('#pago_srv_monto').val(),
      referencia: $('#pago_srv_referencia').val(),
      [CSRF.name]: CSRF.hash
    }, function(r) {
      try { r = typeof r === 'string' ? JSON.parse(r) : r; } catch(e) {}
      if (r.success) {
        toastSrv('success', 'Pagado', r.message);
        bootstrap.Modal.getInstance(document.getElementById('modalPagoServicio')).hide();
        recargarPagos();
        if (tablaServicios) tablaServicios.ajax.reload();
        if (seguimientoCargado) cargarSeguimientoMensual();
      } else toastSrv('danger', 'Error', r.message || 'No se pudo registrar');
    });
  };

  window.recargarPagos = function() {
    if (tablaPagos) tablaPagos.ajax.reload();
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

  $(function() {
    tablaServicios = $('#tablaServicios').DataTable({
      processing: true, serverSide: false,
      ajax: { url: '<?= base_url('compras/ServiciosRecurrentes/lista_servicios_ajax') ?>', type: 'POST', data: d => { d[CSRF.name] = CSRF.hash; } },
      order: [[0, 'asc']], pageLength: 25
    });
    tablaPagos = $('#tablaPagos').DataTable({
      processing: true, serverSide: false,
      ajax: { url: '<?= base_url('compras/ServiciosRecurrentes/lista_pagos_ajax') ?>', type: 'POST',
        data: d => { d.periodo = $('#filtro_periodo').val(); d.proveedor_id = $('#filtro_proveedor_pago').val(); d[CSRF.name] = CSRF.hash; }
      },
      order: [[2, 'asc']], pageLength: 25
    });

    const params = new URLSearchParams(window.location.search);
    if (params.get('proveedor_id')) {
      $('#filtro_proveedor_pago').val(params.get('proveedor_id'));
      $('#srv_proveedor_id').val(params.get('proveedor_id'));
    }
    if (params.get('nuevo') === '1') {
      abrirModalNuevoServicio(params.get('proveedor_id'));
    }
  });
})();
</script>
