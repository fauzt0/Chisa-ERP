<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid p-0">

  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
      <h1 class="h3 mb-1"><i data-lucide="banknote" class="me-2" style="width:28px;height:28px;"></i><?= $headTitle ?></h1>
      <p class="text-muted mb-0 small">Pago por periodo en lote · Selección individual de trabajadores antes de pagar</p>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= base_url('rh/RecursosHumanos') ?>" class="btn btn-outline-secondary">
        <i data-lucide="users" style="width:16px;height:16px;"></i> Empleados
      </a>
      <button type="button" class="btn btn-primary" onclick="mostrarModalNuevo()">
        <i data-lucide="plus" style="width:16px;height:16px;"></i> Nueva Nómina
      </button>
    </div>
  </div>

  <!-- Stats -->
  <div class="row mb-3">
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
              <i data-lucide="file-stack" class="text-primary" style="width:24px;height:24px;"></i>
            </div>
            <div>
              <div class="text-muted small">Total nóminas</div>
              <div class="h4 mb-0"><?= (int)$stats['total_nominas'] ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
              <i data-lucide="clock" class="text-warning" style="width:24px;height:24px;"></i>
            </div>
            <div>
              <div class="text-muted small">Pendientes de pago</div>
              <div class="h4 mb-0"><?= (int)$stats['pendientes_pago'] ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
              <i data-lucide="check-circle" class="text-success" style="width:24px;height:24px;"></i>
            </div>
            <div>
              <div class="text-muted small">Pagadas este mes</div>
              <div class="h4 mb-0"><?= (int)$stats['pagadas_mes'] ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
              <i data-lucide="wallet" class="text-info" style="width:24px;height:24px;"></i>
            </div>
            <div>
              <div class="text-muted small">Neto pendiente</div>
              <div class="h4 mb-0">$<?= number_format($stats['neto_pendiente'], 2) ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($requiere_migracion_pago)): ?>
  <div class="alert alert-warning border-0 shadow-sm mb-3">
    <i data-lucide="alert-triangle" style="width:18px;height:18px;"></i>
    <strong>Migración pendiente:</strong> ejecute <code>database/nomina_pago_parcial.sql</code> en la base de datos para habilitar pagos parciales y el botón <strong>Procesar Pago</strong>.
  </div>
  <?php endif; ?>

  <div class="alert alert-light border shadow-sm mb-3 py-2">
    <div class="d-flex flex-wrap align-items-center gap-2 small">
      <span class="fw-semibold text-muted">Flujo:</span>
      <span class="badge bg-secondary">1. Nueva Nómina</span>
      <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
      <span class="badge bg-warning text-dark">2. Calcular</span>
      <i data-lucide="chevron-right" style="width:14px;height:14px;"></i>
      <span class="badge bg-success">3. Procesar Pago</span>
      <span class="text-muted ms-2">— El botón verde aparece en la columna <strong>Pago</strong> cuando la nómina está calculada.</span>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
      <h5 class="card-title mb-0"><i data-lucide="list" style="width:18px;height:18px;"></i> Historial de Nóminas</h5>
    </div>
    <div class="card-body">
      <div class="table-responsive">
      <table id="tablaNominas" class="table table-hover w-100 align-middle">
        <thead class="table-light">
          <tr>
            <th>Folio</th>
            <th>Tipo</th>
            <th>Periodo</th>
            <th>Fecha Pago</th>
            <th class="text-end">Percepciones</th>
            <th class="text-end">Deducciones</th>
            <th class="text-end">Neto</th>
            <th>Estatus</th>
            <th width="160">Pago</th>
            <th width="140">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
      </div>
    </div>
  </div>
</div>

<style>
#tablaNominas .btn-group .btn { min-width: 2rem; }
#tablaNominas td { vertical-align: middle; }
#tablaNominas .btn-group { gap: 0; }
</style>

<!-- Modal Nueva Nómina -->
<div class="modal fade" id="modalNomina" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <h5 class="modal-title"><i data-lucide="plus-circle" style="width:20px;height:20px;"></i> Nueva Nómina</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formNomina">
          <div class="mb-3">
            <label class="form-label">Tipo de Nómina <span class="text-danger">*</span></label>
            <select class="form-select" id="nomina_tipo" name="tipo_nomina" required>
              <option value="">Seleccionar...</option>
              <option value="Semanal">Semanal</option>
              <option value="Quincenal">Quincenal</option>
              <option value="Mensual">Mensual</option>
              <option value="Extraordinaria">Extraordinaria</option>
              <option value="Aguinaldo">Aguinaldo</option>
            </select>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Periodo Inicio <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="nomina_periodo_inicio" name="periodo_inicio" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Periodo Fin <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="nomina_periodo_fin" name="periodo_fin" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="nomina_fecha_pago" name="fecha_pago" required>
          </div>
          <div class="alert alert-info mb-0 py-2 small">
            <i data-lucide="info" style="width:14px;height:14px;"></i>
            Se incluirán automáticamente los empleados activos del tipo de nómina seleccionado.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarNomina()">
          <i data-lucide="save" style="width:16px;height:16px;"></i> Crear Nómina
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Detalle -->
<div class="modal fade" id="modalDetalleNomina" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <h5 class="modal-title"><i data-lucide="receipt" style="width:20px;height:20px;"></i> Detalle de Nómina</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detalleNominaBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-outline-secondary d-none" id="btnDetalleImprimirRecibos">
          <i class="fas fa-file-pdf"></i> Ver recibos
        </button>
        <button type="button" class="btn btn-success d-none" id="btnDetalleProcesarPago">
          <i data-lucide="banknote" style="width:16px;height:16px;"></i> Procesar Pago
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Procesar Pago (selección por trabajador) -->
<div class="modal fade" id="modalProcesarPago" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <div>
          <h5 class="modal-title mb-0"><i data-lucide="banknote" style="width:20px;height:20px;"></i> Procesar Pago de Nómina</h5>
          <small class="text-white-50" id="pago-nomina-subtitulo">Seleccione los trabajadores a pagar</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="pago-nomina-id">

        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <div class="card border-0 bg-light h-100"><div class="card-body py-2 text-center">
              <div class="text-muted small">% pagado del periodo</div>
              <div class="h4 mb-0 text-primary" id="pago-pct-periodo">0%</div>
            </div></div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 bg-light h-100"><div class="card-body py-2 text-center">
              <div class="text-muted small">Neto pendiente</div>
              <div class="h5 mb-0 text-warning" id="pago-neto-pendiente">$0.00</div>
            </div></div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 bg-light h-100"><div class="card-body py-2 text-center">
              <div class="text-muted small">Seleccionados a pagar</div>
              <div class="h5 mb-0 text-success" id="pago-neto-seleccion">$0.00</div>
            </div></div>
          </div>
          <div class="col-md-3">
            <div class="card border-0 bg-light h-100"><div class="card-body py-2 text-center">
              <div class="text-muted small">Empleados</div>
              <div class="h5 mb-0"><span id="pago-count-sel">0</span> / <span id="pago-count-total">0</span></div>
            </div></div>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
          <div class="d-flex align-items-center gap-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="pago-seleccionar-todos" checked onchange="toggleTodosPago(this.checked)">
              <label class="form-check-label fw-semibold" for="pago-seleccionar-todos">Seleccionar todos los pendientes</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="pago-incluir-adeudos-todos" onchange="toggleAdeudosTodos(this.checked)">
              <label class="form-check-label" for="pago-incluir-adeudos-todos">Incluir adeudos previos en todos</label>
            </div>
          </div>
          <small class="text-muted">Edite el monto a pagar (parcial o total). Los adeudos se liquidan primero.</small>
        </div>

        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle" id="tablaPagoEmpleados">
            <thead class="table-light">
              <tr>
                <th width="40"></th>
                <th>Empleado</th>
                <th class="text-end">Pend. periodo</th>
                <th class="text-end">Adeudos prev.</th>
                <th class="text-center" width="70">Incl.</th>
                <th class="text-end" width="150">Monto a pagar</th>
                <th class="text-center">% Pagado</th>
                <th width="50"></th>
              </tr>
            </thead>
            <tbody id="pago-empleados-body">
              <tr><td colspan="8" class="text-center text-muted py-4">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="confirmarPagoSeleccion()">
          <i data-lucide="check-circle" style="width:16px;height:16px;"></i> Confirmar Pago
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Recibos de Pago (previsualización + PDF) -->
<div class="modal fade" id="modalRecibosNomina" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title mb-0">
          <i class="fas fa-receipt me-2"></i> Recibos de Pago
          <span class="badge bg-light text-dark ms-2" id="recibos-modal-folio"></span>
          <small class="text-white-50 ms-2" id="recibos-modal-count"></small>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-light p-3">
        <div id="recibos-preview-content">
          <div class="text-center text-muted py-5">
            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
            <p class="mb-0">Cargando recibos...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times"></i> Cerrar
        </button>
        <button type="button" class="btn btn-outline-primary" onclick="imprimirRecibosModal()" id="btnRecibosImprimir" disabled>
          <i class="fas fa-print"></i> Imprimir
        </button>
        <button type="button" class="btn btn-danger" onclick="descargarRecibosPDF()" id="btnRecibosPdf" disabled>
          <i class="fas fa-file-pdf"></i> Descargar PDF
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
var tablaNominas;
var csrfName = '<?= $this->security->get_csrf_token_name() ?>';
var csrfHash = '<?= $this->security->get_csrf_hash() ?>';
var pagoEmpleadosData = [];

document.addEventListener('DOMContentLoaded', function() {
  if (typeof jQuery === 'undefined' || typeof $.fn.DataTable === 'undefined') {
    console.error('jQuery/DataTables no cargó correctamente');
    return;
  }
  initTablaNominas();
});

function initTablaNominas() {
  if ($.fn.DataTable.isDataTable('#tablaNominas')) {
    $('#tablaNominas').DataTable().destroy();
  }

  tablaNominas = $('#tablaNominas').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: '<?= base_url('rh/Nomina/lista_ajax') ?>',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d[csrfName] = csrfHash;
      },
      dataSrc: 'data',
      error: function(xhr) {
        console.error('Error lista_ajax:', xhr.responseText);
        notifyShow('No se pudo cargar el historial de nóminas.', 'danger');
      }
    },
    columns: [
      { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 },
      { data: 4 }, { data: 5 }, { data: 6 }, { data: 7 },
      { data: 8, orderable: false },
      { data: 9, orderable: false }
    ],
    order: [[3, 'desc']],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json' },
    pageLength: 25,
    autoWidth: false,
    columnDefs: [
      { className: 'text-end', targets: [4, 5, 6] },
      { className: 'text-nowrap', targets: [8, 9] }
    ],
    drawCallback: function() {
      refreshLucideIcons();
    }
  });

  $('#nomina_tipo').off('change.nomina').on('change.nomina', sugerirPeriodo);
  $('#nomina_fecha_pago').val('<?= date('Y-m-d') ?>');
  refreshLucideIcons();
}

function refreshLucideIcons() {
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }
}

function recargarTablaNominas() {
  if (tablaNominas && tablaNominas.ajax) {
    tablaNominas.ajax.reload(null, false);
  } else {
    initTablaNominas();
  }
}

function sugerirPeriodo() {
  var tipo = $('#nomina_tipo').val();
  var hoy = new Date();
  var inicio, fin;
  if (tipo === 'Quincenal') {
    if (hoy.getDate() <= 15) {
      inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
      fin = new Date(hoy.getFullYear(), hoy.getMonth(), 15);
    } else {
      inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 16);
      fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
    }
  } else if (tipo === 'Mensual') {
    inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
    fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
  } else if (tipo === 'Semanal') {
    var day = hoy.getDay() || 7;
    inicio = new Date(hoy);
    inicio.setDate(hoy.getDate() - day + 1);
    fin = new Date(inicio);
    fin.setDate(inicio.getDate() + 6);
  }
  if (inicio && fin) {
    $('#nomina_periodo_inicio').val(inicio.toISOString().slice(0, 10));
    $('#nomina_periodo_fin').val(fin.toISOString().slice(0, 10));
  }
}

function mostrarModalNuevo() {
  $('#formNomina')[0].reset();
  $('#nomina_fecha_pago').val('<?= date('Y-m-d') ?>');
  $('#modalNomina').modal('show');
}

function guardarNomina() {
  var formData = $('#formNomina').serialize();
  formData += '&peticion=ajax&' + csrfName + '=' + csrfHash;
  $.post('<?= base_url('rh/Nomina/crear_ajax') ?>', formData, function(result) {
    try { result = JSON.parse(result); } catch (e) {
      notifyShow('Error al procesar la respuesta del servidor', 'danger');
      return;
    }
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if (!result.success) return;

    $('#modalNomina').modal('hide');
    recargarTablaNominas();

    var nominaId = result.nomina_id;
    var totalEmp = parseInt(result.total_empleados, 10) || 0;
    var msg = 'Nómina creada con ' + totalEmp + ' empleado(s).';
    if (totalEmp <= 0) {
      notifyShow(msg + ' No hay empleados activos con ese tipo de nómina.', 'warning');
      return;
    }
    if (confirm(msg + '\n\n¿Desea calcularla ahora y abrir Procesar Pago?')) {
      calcularNominaSilencioso(nominaId, function() {
        abrirModalPago(nominaId);
      });
    }
  });
}

function calcularNominaSilencioso(id, callback) {
  $.post('<?= base_url('rh/Nomina/calcular_ajax') ?>', { id: id, peticion: 'ajax', [csrfName]: csrfHash }, function(result) {
    try { result = JSON.parse(result); } catch (e) {
      notifyShow('Error al calcular la nómina', 'danger');
      return;
    }
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if (result.success) {
      recargarTablaNominas();
      if (typeof callback === 'function') callback();
    }
  });
}

function verNomina(id) {
  $.post('<?= base_url('rh/Nomina/get_nomina_ajax') ?>', {
    id: id, peticion: 'ajax', [csrfName]: csrfHash
  }, function(result) {
    result = JSON.parse(result);
    if (!result.success) { notifyShow(result.message, 'danger'); return; }
    var n = result.nomina;
    var html = '';
    if (n.estatus === 'Borrador') {
      html += '<div class="alert alert-warning py-2 small mb-3"><i data-lucide="info" style="width:14px;height:14px;"></i> Esta nómina está en <strong>Borrador</strong>. Calcúlela primero para habilitar <strong>Procesar Pago</strong>.</div>';
    } else if (n.estatus === 'Calculada' || n.estatus === 'Parcial') {
      html += '<div class="alert alert-success py-2 small mb-3"><i data-lucide="banknote" style="width:14px;height:14px;"></i> Lista para pago. Use el botón <strong>Procesar Pago</strong> al pie de esta ventana.</div>';
    }
    html += '<div class="row mb-3">' +
      '<div class="col-md-6"><table class="table table-sm table-borderless">' +
      '<tr><th width="120">Folio:</th><td><strong>' + n.folio + '</strong></td></tr>' +
      '<tr><th>Tipo:</th><td>' + n.tipo_nomina + '</td></tr>' +
      '<tr><th>Periodo:</th><td>' + n.periodo_inicio + ' — ' + n.periodo_fin + '</td></tr>' +
      '<tr><th>Fecha Pago:</th><td>' + n.fecha_pago + '</td></tr></table></div>' +
      '<div class="col-md-6"><table class="table table-sm table-borderless">' +
      '<tr><th width="120">Percepciones:</th><td class="text-success">$' + parseFloat(n.total_percepciones).toFixed(2) + '</td></tr>' +
      '<tr><th>Deducciones:</th><td class="text-danger">$' + parseFloat(n.total_deducciones).toFixed(2) + '</td></tr>' +
      '<tr><th>Neto:</th><td><strong class="fs-5">$' + parseFloat(n.total_neto).toFixed(2) + '</strong></td></tr>' +
      '<tr><th>Estatus:</th><td>' + n.estatus + '</td></tr></table></div></div>';
    if (n.poliza_id) {
      html += '<div class="alert alert-info py-2 small"><i data-lucide="book-open" style="width:14px;height:14px;"></i> Póliza contable vinculada: <strong>#' + n.poliza_id + '</strong> · <a href="<?= base_url('contabilidad/Polizas') ?>">Ver en Contabilidad</a></div>';
    }
    html += '<h6 class="border-bottom pb-2">Detalle por Empleado</h6>' +
      '<div class="table-responsive"><table class="table table-sm table-bordered table-hover">' +
      '<thead class="table-light"><tr><th>Empleado</th><th>Puesto</th><th>Días</th><th>Neto</th><th>% Pagado</th><th>Estatus</th><th width="80">Recibo</th></tr></thead><tbody>';

    if (n.detalle && n.detalle.length) {
      n.detalle.forEach(function(d) {
        var pagado = parseFloat(d.monto_pagado || 0);
        var neto = parseFloat(d.neto || 0);
        var pct = neto > 0 ? ((pagado / neto) * 100).toFixed(1) : '0';
        var badge = d.estatus === 'Pagado' ? 'success' : (d.estatus === 'Parcial' ? 'info' : 'warning');
        var recibo = pagado > 0
          ? '<button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="verRecibosNomina(' + n.id + ', null, null, ' + d.id + ')" title="Ver recibo de pago"><i class="fas fa-file-pdf"></i></button>'
          : '<span class="text-muted small">—</span>';
        html += '<tr><td>' + d.nombre + ' ' + d.apellido_paterno + '</td><td>' + (d.puesto || '—') + '</td>' +
          '<td>' + (d.dias_trabajados || 0) + '</td><td><strong>$' + neto.toFixed(2) + '</strong></td>' +
          '<td>' + pct + '%</td><td><span class="badge bg-' + badge + '">' + (d.estatus || 'Pendiente') + '</span></td>' +
          '<td>' + recibo + '</td></tr>';
      });
    } else {
      html += '<tr><td colspan="7" class="text-center text-muted">Sin empleados</td></tr>';
    }
    html += '</tbody></table></div>';

    $('#detalleNominaBody').html(html);
    var $btnPago = $('#btnDetalleProcesarPago');
    var $btnRecibos = $('#btnDetalleImprimirRecibos');
    if (n.estatus === 'Calculada' || n.estatus === 'Parcial') {
      $btnPago.removeClass('d-none').off('click').on('click', function() {
        $('#modalDetalleNomina').modal('hide');
        abrirModalPago(n.id);
      });
    } else {
      $btnPago.addClass('d-none').off('click');
    }
    if (n.estatus === 'Parcial' || n.estatus === 'Pagada') {
      $btnRecibos.removeClass('d-none').off('click').on('click', function() {
        verRecibosNomina(n.id);
      });
    } else {
      $btnRecibos.addClass('d-none').off('click');
    }
    $('#modalDetalleNomina').modal('show');
    refreshLucideIcons();
  });
}

function calcularNomina(id) {
  if (!confirm('¿Calcular esta nómina con los salarios y deducciones de cada empleado?')) return;
  $.post('<?= base_url('rh/Nomina/calcular_ajax') ?>', { id: id, peticion: 'ajax', [csrfName]: csrfHash }, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if (result.success) {
      recargarTablaNominas();
      if (confirm('Nómina calculada correctamente.\n\n¿Desea abrir Procesar Pago ahora?')) {
        abrirModalPago(id);
      }
    }
  });
}

function abrirModalPago(id) {
  $('#pago-nomina-id').val(id);
  $('#pago-incluir-adeudos-todos').prop('checked', false);
  $('#pago-empleados-body').html('<tr><td colspan="8" class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
  $('#modalProcesarPago').modal('show');

  $.post('<?= base_url('rh/Nomina/detalle_pago_ajax') ?>', {
    id: id, peticion: 'ajax', [csrfName]: csrfHash
  }, function(result) {
    result = JSON.parse(result);
    if (!result.success) {
      notifyShow(result.message, 'danger');
      $('#modalProcesarPago').modal('hide');
      return;
    }
    renderModalPago(result.data);
  });
}

function renderModalPago(data) {
  var n = data.nomina;
  pagoEmpleadosData = data.empleados;
  $('#pago-nomina-subtitulo').text(n.folio + ' · ' + n.tipo_nomina + ' · ' + n.periodo_inicio + ' al ' + n.periodo_fin);
  $('#pago-pct-periodo').text(data.totales.porcentaje + '%');
  $('#pago-neto-pendiente').text('$' + parseFloat(data.totales.neto_pendiente).toFixed(2));
  $('#pago-count-total').text(data.empleados.length);

  var html = '';
  data.empleados.forEach(function(emp, idx) {
    var disabled = !emp.puede_pagar ? 'disabled' : '';
    var checked = emp.seleccionado && emp.puede_pagar ? 'checked' : '';
    var rowClass = emp.puede_pagar ? '' : 'table-success opacity-75';
    var adeudoTitle = emp.adeudos.items.length
      ? emp.adeudos.items.map(function(i){ return i.folio + ' (' + i.periodo + '): $' + i.pendiente.toFixed(2); }).join(' | ')
      : '';
    var adeudo = emp.adeudos.total > 0
      ? '<span class="text-danger fw-semibold" title="' + adeudoTitle + '">$' + emp.adeudos.total.toFixed(2) + '</span>'
      : '<span class="text-muted">—</span>';
    var pctClass = emp.porcentaje_pagado >= 100 ? 'success' : (emp.porcentaje_pagado > 0 ? 'info' : 'secondary');
    var adeudoChk = emp.adeudos.total > 0 && emp.puede_pagar
      ? '<input type="checkbox" class="form-check-input chk-adeudo-emp" data-idx="' + idx + '" onchange="actualizarMaxMonto(' + idx + ')">'
      : '<span class="text-muted">—</span>';
    var montoInput = emp.puede_pagar
      ? '<div class="input-group input-group-sm">' +
        '<span class="input-group-text">$</span>' +
        '<input type="number" class="form-control text-end inp-monto-pago" data-idx="' + idx + '" ' +
          'min="0.01" step="0.01" max="' + emp.pendiente.toFixed(2) + '" value="' + emp.pendiente.toFixed(2) + '" ' +
          'onchange="validarMontoPago(' + idx + ')" oninput="recalcularPagoSeleccion()">' +
        '</div>' +
        '<div class="btn-group btn-group-sm mt-1 w-100" role="group">' +
        '<button type="button" class="btn btn-outline-secondary py-0" onclick="aplicarPorcentajePago(' + idx + ',25)">25%</button>' +
        '<button type="button" class="btn btn-outline-secondary py-0" onclick="aplicarPorcentajePago(' + idx + ',50)">50%</button>' +
        '<button type="button" class="btn btn-outline-secondary py-0" onclick="aplicarPorcentajePago(' + idx + ',100)">100%</button>' +
        '</div>'
      : '<span class="text-muted">Pagado</span>';

    html += '<tr class="' + rowClass + '" data-idx="' + idx + '">' +
      '<td><input type="checkbox" class="form-check-input chk-pago-emp" data-idx="' + idx + '" ' + checked + ' ' + disabled + ' onchange="recalcularPagoSeleccion()"></td>' +
      '<td><div class="fw-semibold">' + emp.nombre + '</div><small class="text-muted">' + emp.numero_empleado + ' · ' + (emp.puesto || '—') + '</small></td>' +
      '<td class="text-end"><strong>$' + emp.pendiente.toFixed(2) + '</strong><div class="small text-muted">de $' + emp.neto.toFixed(2) + '</div></td>' +
      '<td class="text-end">' + adeudo + '</td>' +
      '<td class="text-center">' + adeudoChk + '</td>' +
      '<td class="text-end">' + montoInput + '</td>' +
      '<td class="text-center"><span class="badge bg-' + pctClass + '">' + emp.porcentaje_pagado + '%</span></td>' +
      '<td><button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="toggleConceptosPago(' + idx + ')" title="Ver desglose"><i class="fas fa-chevron-down"></i></button></td>' +
      '</tr>' +
      '<tr class="conceptos-pago-row d-none" id="conceptos-pago-' + idx + '"><td colspan="8" class="bg-light py-2">' +
      '<div class="row"><div class="col-md-6"><strong class="small text-success">Percepciones</strong><ul class="small mb-0">';
    emp.conceptos.filter(function(c){ return c.tipo === 'Percepción'; }).forEach(function(c) {
      html += '<li>' + c.concepto + ': $' + c.monto.toFixed(2) + '</li>';
    });
    html += '</ul></div><div class="col-md-6"><strong class="small text-danger">Deducciones</strong><ul class="small mb-0">';
    emp.conceptos.filter(function(c){ return c.tipo === 'Deducción'; }).forEach(function(c) {
      html += '<li>' + c.concepto + ': $' + c.monto.toFixed(2) + '</li>';
    });
    html += '</ul></div></div></td></tr>';
  });

  if (!html) html = '<tr><td colspan="8" class="text-center text-muted">Sin empleados</td></tr>';
  $('#pago-empleados-body').html(html);
  recalcularPagoSeleccion();
  refreshLucideIcons();
}

function getMaxMontoEmpleado(idx) {
  var emp = pagoEmpleadosData[idx];
  if (!emp) return 0;
  var incluir = $('.chk-adeudo-emp[data-idx="' + idx + '"]').is(':checked');
  return emp.pendiente + (incluir ? emp.adeudos.total : 0);
}

function actualizarMaxMonto(idx) {
  var max = getMaxMontoEmpleado(idx);
  var $inp = $('.inp-monto-pago[data-idx="' + idx + '"]');
  $inp.attr('max', max.toFixed(2));
  var val = parseFloat($inp.val()) || 0;
  if (val > max) $inp.val(max.toFixed(2));
  recalcularPagoSeleccion();
}

function validarMontoPago(idx) {
  var max = getMaxMontoEmpleado(idx);
  var $inp = $('.inp-monto-pago[data-idx="' + idx + '"]');
  var val = parseFloat($inp.val()) || 0;
  if (val <= 0) $inp.val('0.01');
  if (val > max) $inp.val(max.toFixed(2));
  recalcularPagoSeleccion();
}

function aplicarPorcentajePago(idx, pct) {
  var emp = pagoEmpleadosData[idx];
  if (!emp) return;
  var base = emp.pendiente;
  var monto = Math.round(base * (pct / 100) * 100) / 100;
  if (monto <= 0 && base > 0) monto = 0.01;
  $('.inp-monto-pago[data-idx="' + idx + '"]').val(monto.toFixed(2));
  recalcularPagoSeleccion();
}

function toggleAdeudosTodos(checked) {
  $('.chk-adeudo-emp').prop('checked', checked);
  pagoEmpleadosData.forEach(function(emp, idx) {
    if (emp.puede_pagar) actualizarMaxMonto(idx);
  });
}

function toggleConceptosPago(idx) {
  $('#conceptos-pago-' + idx).toggleClass('d-none');
}

function toggleTodosPago(checked) {
  $('.chk-pago-emp:not(:disabled)').prop('checked', checked);
  recalcularPagoSeleccion();
}

function recalcularPagoSeleccion() {
  var count = 0, neto = 0;
  $('.chk-pago-emp:checked').each(function() {
    var idx = $(this).data('idx');
    if (!pagoEmpleadosData[idx]) return;
    count++;
    var monto = parseFloat($('.inp-monto-pago[data-idx="' + idx + '"]').val()) || 0;
    neto += monto;
  });
  $('#pago-count-sel').text(count);
  $('#pago-neto-seleccion').text('$' + neto.toFixed(2));
}

function confirmarPagoSeleccion() {
  var pagos = [];
  var tieneParcial = false;
  $('.chk-pago-emp:checked').each(function() {
    var idx = $(this).data('idx');
    var emp = pagoEmpleadosData[idx];
    if (!emp) return;
    var monto = parseFloat($('.inp-monto-pago[data-idx="' + idx + '"]').val()) || 0;
    var incluir = $('.chk-adeudo-emp[data-idx="' + idx + '"]').is(':checked');
    var max = getMaxMontoEmpleado(idx);
    if (monto <= 0) return;
    if (monto > max + 0.01) {
      notifyShow('Monto inválido para ' + emp.nombre + '. Máximo: $' + max.toFixed(2), 'danger');
      pagos = null;
      return false;
    }
    if (monto < emp.pendiente - 0.01 || (incluir && monto > emp.pendiente + 0.01)) {
      tieneParcial = true;
    }
    pagos.push({
      detalle_id: emp.detalle_id,
      monto: monto,
      incluir_adeudos: incluir
    });
  });

  if (pagos === null) return;
  if (!pagos.length) {
    notifyShow('Seleccione al menos un empleado con monto mayor a cero', 'warning');
    return;
  }

  var neto = $('#pago-neto-seleccion').text();
  var msg = '¿Confirmar pago de ' + pagos.length + ' empleado(s) por ' + neto + '?';
  if (tieneParcial) msg += '\n\nIncluye pagos parciales o consolidación de adeudos.';
  msg += '\nSe generará póliza contable por este lote.';
  if (!confirm(msg)) return;

  $.post('<?= base_url('rh/Nomina/pagar_ajax') ?>', {
    id: $('#pago-nomina-id').val(),
    pagos: JSON.stringify(pagos),
    peticion: 'ajax',
    [csrfName]: csrfHash
  }, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if (result.success) {
      $('#modalProcesarPago').modal('hide');
      recargarTablaNominas();
      if (result.detalle_ids && result.detalle_ids.length && confirm('¿Desea ver los recibos de pago de este lote?')) {
        verRecibosNomina(result.nomina_id || $('#pago-nomina-id').val(), result.detalle_ids, result.pagos_lote || null);
      }
    }
  });
}

function pagarNomina(id) {
  abrirModalPago(id);
}

function exportarExcel(id) {
  window.location.href = '<?= base_url('rh/Nomina/exportar_excel/') ?>' + id;
}

function eliminarNomina(id) {
  if (!confirm('¿Eliminar esta nómina en borrador?')) return;
  $.post('<?= base_url('rh/Nomina/eliminar_ajax') ?>', { id: id, peticion: 'ajax', [csrfName]: csrfHash }, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if (result.success) recargarTablaNominas();
  });
}

var recibosNominaActual = { html: '', filename: 'Recibos_Nomina.pdf' };

function verRecibosNomina(id, detalleIds, montosLote, detalleId) {
  var payload = {
    id: id,
    peticion: 'ajax',
    [csrfName]: csrfHash
  };
  if (detalleId) payload.detalle_id = detalleId;
  if (detalleIds && detalleIds.length) {
    payload.ids = JSON.stringify(Array.isArray(detalleIds) ? detalleIds : [detalleIds]);
  }
  if (montosLote && typeof montosLote === 'object') {
    payload.montos = JSON.stringify(montosLote);
  }

  recibosNominaActual = { html: '', filename: 'Recibos_Nomina.pdf' };
  $('#recibos-preview-content').html('<div class="text-center text-muted py-5"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p class="mb-0">Cargando recibos...</p></div>');
  $('#recibos-modal-folio').text('');
  $('#recibos-modal-count').text('');
  $('#btnRecibosPdf, #btnRecibosImprimir').prop('disabled', true);
  $('#modalRecibosNomina').modal('show');

  $.ajax({
    url: '<?= base_url('rh/Nomina/get_recibos_ajax') ?>',
    method: 'POST',
    data: payload,
    dataType: 'json',
    success: function(result) {
      if (!result || !result.success) {
        var msg = (result && result.message) ? result.message : 'No se pudieron cargar los recibos';
        notifyShow(msg, 'warning');
        $('#recibos-preview-content').html('<div class="alert alert-warning mb-0">' + msg + '</div>');
        return;
      }
      recibosNominaActual = { html: result.html, filename: result.filename };
      $('#recibos-modal-folio').text(result.folio);
      $('#recibos-modal-count').text('(' + result.count + ' recibo' + (result.count === 1 ? '' : 's') + ')');
      $('#recibos-preview-content').html(result.html);
      $('#btnRecibosPdf, #btnRecibosImprimir').prop('disabled', false);
    },
    error: function(xhr) {
      var msg = 'No se pudo cargar la previsualización de recibos';
      if (xhr.responseJSON && xhr.responseJSON.message) {
        msg = xhr.responseJSON.message;
      } else if (xhr.responseText) {
        try {
          var parsed = JSON.parse(xhr.responseText);
          if (parsed.message) msg = parsed.message;
        } catch (e) { /* respuesta no JSON */ }
      }
      notifyShow(msg, 'danger');
      $('#recibos-preview-content').html('<div class="alert alert-danger mb-0">' + msg + '</div>');
    }
  });
}

function descargarRecibosPDF() {
  if (!recibosNominaActual.html || typeof html2pdf === 'undefined') {
    notifyShow('No hay contenido para generar el PDF', 'warning');
    return;
  }
  var btn = $('#btnRecibosPdf');
  btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generando...');

  var element = document.createElement('div');
  element.innerHTML = recibosNominaActual.html;

  html2pdf().set({
    margin: [10, 10, 12, 10],
    filename: recibosNominaActual.filename,
    image: { type: 'jpeg', quality: 0.95 },
    html2canvas: { scale: 2, useCORS: true, scrollY: 0, logging: false },
    jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' },
    pagebreak: { mode: ['css', 'legacy'], before: '.recibo' }
  }).from(element).save().then(function() {
    btn.prop('disabled', false).html('<i class="fas fa-file-pdf"></i> Descargar PDF');
  }).catch(function(err) {
    btn.prop('disabled', false).html('<i class="fas fa-file-pdf"></i> Descargar PDF');
    notifyShow('Error al generar PDF: ' + (err.message || ''), 'danger');
  });
}

function imprimirRecibosModal() {
  if (!recibosNominaActual.html) return;
  var ventana = window.open('', '_blank');
  if (!ventana) {
    notifyShow('Permita ventanas emergentes para imprimir', 'warning');
    return;
  }
  ventana.document.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Recibos de Pago</title></head><body style="margin:0;padding:16px;background:#eee;">');
  ventana.document.write(recibosNominaActual.html);
  ventana.document.write('</body></html>');
  ventana.document.close();
  ventana.focus();
  setTimeout(function() { ventana.print(); }, 500);
}

/** Compatibilidad: abre previsualización en modal (antes abría pestaña directa). */
function imprimirRecibos(id, detalleIds, montosLote) {
  verRecibosNomina(id, detalleIds, montosLote);
}
</script>
