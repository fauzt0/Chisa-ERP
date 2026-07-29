<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="container-fluid p-0 rh-nomina-page">

  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
      <h1 class="h3 mb-1"><i data-lucide="banknote" class="me-2" style="width:28px;height:28px;"></i><?= $headTitle ?></h1>
      <p class="text-muted mb-0 small">Cree o revise la nómina semanal, calcule, edite montos y procese el pago por trabajador.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
      <a href="<?= base_url('rh/RecursosHumanos') ?>" class="btn btn-outline-secondary">
        <i data-lucide="users" style="width:16px;height:16px;"></i> Empleados
      </a>
      <button type="button" class="btn btn-outline-secondary" onclick="abrirModalConfiguracion()" title="Frecuencia y creación automática">
        <i data-lucide="settings" style="width:16px;height:16px;"></i> Automatización
      </button>
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

  <div class="alert alert-light border shadow-sm mb-3 py-2 px-3">
    <div class="nomina-flujo d-flex flex-wrap align-items-center gap-2 small">
      <span class="fw-semibold text-muted me-1">Flujo:</span>
      <div class="nomina-flujo-paso">
        <span class="badge bg-secondary">1. Nueva / automática</span>
      </div>
      <span class="nomina-flujo-flecha text-muted" aria-hidden="true">›</span>
      <div class="nomina-flujo-paso">
        <span class="badge bg-warning text-dark">2. Calcular / revisar</span>
      </div>
      <span class="nomina-flujo-flecha text-muted" aria-hidden="true">›</span>
      <div class="nomina-flujo-paso">
        <span class="badge bg-success">3. Procesar pago</span>
      </div>
      <span class="text-muted nomina-flujo-nota">· Use <strong>Ver</strong> para editar montos · La campana avisa cuando hay nóminas por pagar.</span>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
      <h5 class="card-title mb-0"><i data-lucide="list" style="width:18px;height:18px;"></i> Historial de Nóminas</h5>
    </div>
    <div class="card-body">
      <div class="row g-2 mb-3 align-items-end">
        <div class="col-md-2">
          <label class="form-label small mb-1" for="filtro_folio">Folio</label>
          <input type="text" class="form-control form-control-sm" id="filtro_folio" placeholder="NOM000022">
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1" for="filtro_tipo">Tipo</label>
          <select class="form-select form-select-sm" id="filtro_tipo">
            <option value="">Todos</option>
            <option value="Semanal">Semanal</option>
            <option value="Quincenal">Quincenal</option>
            <option value="Mensual">Mensual</option>
            <option value="Extraordinaria">Extraordinaria</option>
            <option value="Aguinaldo">Aguinaldo</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1" for="filtro_periodo_desde">Periodo desde</label>
          <input type="date" class="form-control form-control-sm" id="filtro_periodo_desde">
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1" for="filtro_periodo_hasta">Periodo hasta</label>
          <input type="date" class="form-control form-control-sm" id="filtro_periodo_hasta">
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1" for="filtro_estatus">Estatus</label>
          <select class="form-select form-select-sm" id="filtro_estatus">
            <option value="">Todos</option>
            <option value="Borrador">Borrador</option>
            <option value="Calculada">Calculada</option>
            <option value="Parcial">Parcial</option>
            <option value="Pagada">Pagada</option>
            <option value="Cancelada">Cancelada</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="limpiarFiltrosNominas()">
            <i class="fas fa-eraser"></i> Limpiar
          </button>
        </div>
      </div>
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
            <th width="170">Pago</th>
            <th width="130">Acciones</th>
            <th class="d-none" aria-hidden="true"></th>
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
.nomina-flujo { row-gap: 0.5rem !important; }
.nomina-flujo-paso { flex: 0 0 auto; }
.nomina-flujo-flecha {
  flex: 0 0 auto;
  font-size: 1.1rem;
  line-height: 1;
  padding: 0 0.15rem;
  user-select: none;
}
.nomina-flujo-nota { flex: 1 1 100%; margin-left: 0.15rem; }
@media (min-width: 992px) {
  .nomina-flujo-nota { flex: 1 1 auto; margin-left: 0.5rem; }
}
/* Evitar doble flecha en "Mostrar X entradas" (Bootstrap form-select + select nativo) */
.rh-nomina-page .dataTables_wrapper .dataTables_length select,
.rh-nomina-page .dt-container .dt-length select,
.rh-nomina-page .dataTables_wrapper .dataTables_length select.form-select,
.rh-nomina-page .dt-container .dt-length select.form-select {
  appearance: auto !important;
  -webkit-appearance: menulist !important;
  -moz-appearance: menulist !important;
  background-image: none !important;
  background-position: unset !important;
  padding: 0.2rem 1.5rem 0.2rem 0.45rem !important;
  min-width: 4rem;
  max-width: 5rem;
  width: auto;
  display: inline-block;
  vertical-align: middle;
  line-height: 1.4;
  border: 1px solid var(--bs-border-color, #ced4da);
  border-radius: 0.25rem;
}
.rh-nomina-page .dataTables_wrapper .dataTables_length label,
.rh-nomina-page .dt-container .dt-length label {
  display: inline-flex;
  align-items: center;
  flex-wrap: nowrap;
  gap: 0.4rem;
  margin-bottom: 0.75rem;
  font-weight: normal;
  white-space: nowrap;
}
.rh-nomina-page .dataTables_wrapper .dataTables_length,
.rh-nomina-page .dt-container .dt-length {
  margin-bottom: 0.5rem;
}
#tablaDetalleNomina td.editable {
  cursor: pointer;
  background-color: #fffef5;
}
#tablaDetalleNomina td.editable:hover {
  outline: 1px dashed #2d5a8e;
  outline-offset: -1px;
}
#botonesPeriodoRapido .periodo-btn.active {
  box-shadow: 0 0 0 0.15rem rgba(30, 58, 95, 0.25);
}
</style>

<?php $this->load->view('rh/partials/modal_styles'); ?>
<div class="modal fade rh-modal" id="modalNomina" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <h5 class="modal-title text-white"><i data-lucide="plus-circle" style="width:20px;height:20px;"></i> Nueva Nómina</h5>
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
          <div class="mb-3">
            <label class="form-label fw-bold">Selección rápida de periodo</label>
            <div class="d-flex flex-wrap gap-1" id="botonesPeriodoRapido">
              <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="semana">Esta semana</button>
              <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="semana_anterior">Semana anterior</button>
              <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="quincena1">1ra Quincena</button>
              <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="quincena2">2da Quincena</button>
              <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="mes_actual">Mes actual</button>
              <button type="button" class="btn btn-sm btn-outline-secondary periodo-btn" data-rango="mes_anterior">Mes anterior</button>
            </div>
            <small class="text-muted d-block mt-1">También puede elegir el tipo arriba y ajustar las fechas a mano.</small>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label" for="periodoInicio">Periodo inicio <span class="text-danger">*</span></label>
              <input type="date" id="periodoInicio" name="periodo_inicio" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="periodoFin">Periodo fin <span class="text-danger">*</span></label>
              <input type="date" id="periodoFin" name="periodo_fin" class="form-control" required>
              <small class="text-muted" id="periodoFinHint">Auto-calculado según tipo de nómina</small>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="nomina_fecha_pago">Fecha de Pago <span class="text-danger">*</span></label>
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

<!-- Modal: Detalle Completo de Nómina -->
<div class="modal fade rh-modal" id="modalDetalleNomina" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-fullscreen-xl-down modal-dialog-scrollable" style="max-width: 95vw;">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <h5 class="modal-title text-white mb-0">
          <i class="fas fa-file-invoice-dollar me-2"></i>
          Nómina <span id="detalleFolio">—</span>
        </h5>
        <div class="d-flex align-items-center gap-2">
          <button type="button" class="btn btn-sm btn-light" onclick="exportarDetalleExcel()" title="Exportar Excel (relación, transferencias y resumen)">
            <i class="fas fa-file-excel text-success"></i> Exportar
          </button>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body p-0">
        <!-- Info de cabecera: Periodo, Tipo, Fecha Pago -->
        <div class="p-3 bg-light border-bottom">
          <div class="row g-2 small">
            <div class="col-md-3"><strong>Periodo:</strong> <span id="detallePeriodo">—</span></div>
            <div class="col-md-3"><strong>Tipo:</strong> <span id="detalleTipo">—</span></div>
            <div class="col-md-3"><strong>Fecha Pago:</strong> <span id="detalleFechaPago">—</span></div>
            <div class="col-md-3"><strong>Estatus:</strong> <span id="detalleEstatus">—</span></div>
          </div>
        </div>

        <div class="px-3 pt-3">
          <div class="input-group input-group-sm" style="max-width: 300px;">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="buscadorDetalle" class="form-control" placeholder="Buscar trabajador..." oninput="filtrarTablaDetalle()">
          </div>
        </div>

        <!-- Tabla scrolling horizontal con el formato exacto de las imágenes -->
        <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
          <table class="table table-sm table-bordered table-hover mb-0" id="tablaDetalleNomina"
                 style="font-size: 0.78rem; white-space: nowrap; min-width: 2200px;">
            <thead class="table-dark text-center align-middle" style="position: sticky; top: 0; z-index: 2;">
              <tr>
                <th rowspan="2" style="min-width:100px;">Lugar u origen</th>
                <th rowspan="2" style="min-width:180px;">Nombre del trabajador</th>
                <th rowspan="2" style="min-width:80px;">Sueldo diario</th>
                <th rowspan="2" style="min-width:80px;">Sueldo neto</th>
                <th colspan="3" class="bg-success text-white">Horas extras</th>
                <th rowspan="2" style="min-width:70px;">Comidas</th>
                <th rowspan="2" style="min-width:80px;">Viáticos / Pasajes</th>
                <th rowspan="2" style="min-width:70px;">Prima</th>
                <th rowspan="2" style="min-width:70px;">Otros bonos</th>
                <th rowspan="2" style="min-width:70px;">Otros</th>
                <th rowspan="2" class="bg-success text-white" style="min-width:90px;">Total de Percepciones</th>
                <th rowspan="2" class="bg-danger text-white" style="min-width:80px;">Desglose INFONAVIT</th>
                <th rowspan="2" style="min-width:80px;">Préstamo personal</th>
                <th rowspan="2" style="min-width:80px;">Otros descuentos</th>
                <th rowspan="2" class="bg-danger text-white" style="min-width:90px;">Total Deducciones</th>
                <th rowspan="2" class="bg-primary text-white" style="min-width:90px;">Total Sueldo Neto</th>
                <th rowspan="2" style="min-width:90px;">Forma de pago</th>
                <th rowspan="2" style="min-width:50px;">Recibo</th>
                <th rowspan="2" style="min-width:160px;">Banco / Cuenta</th>
              </tr>
              <tr>
                <th class="bg-success-subtle text-dark" style="min-width:60px;">Cantidad</th>
                <th class="bg-success-subtle text-dark" style="min-width:70px;">Costo x hora</th>
                <th class="bg-success-subtle text-dark" style="min-width:70px;">Monto</th>
              </tr>
            </thead>
            <tbody id="detalleNominaBody">
              <!-- Se llena vía AJAX -->
            </tbody>
            <tfoot class="table-secondary fw-bold text-end" style="position: sticky; bottom: 0; z-index: 2;">
              <tr id="detalleNominaFooter">
                <!-- Se llena vía JS con totales -->
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <!-- Notas de ajuste (visible solo si hay notas o nómina pagada) -->
      <div id="seccionNotasNomina" class="border-top px-3 py-2 bg-light" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0"><i class="fas fa-sticky-note text-warning me-1"></i>Notas de ajuste</h6>
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="abrirModalNota()">
            <i class="fas fa-plus"></i> Agregar nota
          </button>
        </div>
        <div id="notasNominaBody" class="small"></div>
      </div>
      <div class="modal-footer">
        <small class="text-muted me-auto" id="detalleFooterHint">Celdas en amarillo claro: <strong>doble clic</strong> para editar (horas extras, comidas, descuentos, etc.).</small>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Procesar Pago (selección por trabajador) -->
<div class="modal fade rh-modal" id="modalProcesarPago" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 90vw;">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <div>
          <h5 class="modal-title mb-0 text-white"><i data-lucide="banknote" style="width:20px;height:20px;"></i> Procesar Pago de Nómina</h5>
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

        <div class="mb-2">
          <div class="input-group input-group-sm" style="max-width: 300px;">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="buscadorPago" class="form-control" placeholder="Buscar trabajador..." oninput="filtrarTablaPago()">
          </div>
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
<div class="modal fade rh-modal" id="modalRecibosNomina" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title mb-0 text-white">
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

<!-- Modal: Configuración de Automatización -->
<div class="modal fade rh-modal" id="modalConfiguracion" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <h5 class="modal-title text-white"><i class="fas fa-robot me-2"></i>Automatización de Nóminas</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label fw-bold" for="configFrecuencia">Frecuencia principal (referencia)</label>
          <select id="configFrecuencia" class="form-select">
            <option value="Semanal">Semanal (periodo lun–dom)</option>
            <option value="Quincenal">Quincenal (días 1 y 16)</option>
            <option value="Mensual">Mensual (día 1 del mes)</option>
          </select>
          <small class="text-muted">La automatización evalúa <strong>Semanal, Quincenal y Mensual</strong> cada día: solo crea nómina del tipo que corresponda al calendario y solo incluye empleados con ese <code>tipo_nomina</code>.</small>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold" for="configDiasAntes">Días de anticipación</label>
          <input type="number" id="configDiasAntes" class="form-control" min="0" max="30" value="1" placeholder="0 = mismo día">
          <small class="text-muted">Con Semanal y 1 día, el borrador se crea el domingo anterior al lunes de inicio.</small>
        </div>
        <div class="form-check form-switch mb-2">
          <input class="form-check-input" type="checkbox" id="configAutoCrear">
          <label class="form-check-label fw-bold" for="configAutoCrear">Crear nómina automáticamente</label>
        </div>
        <div class="alert alert-info mb-0 py-2 small">
          También se verifica al abrir esta pantalla. El cron del servidor ejecuta la creación diaria a las 07:00.
        </div>
        <div class="alert alert-warning mt-3 mb-0" id="configAlerta" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarConfiguracion()"><i class="fas fa-save me-1"></i>Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Cuentas Bancarias del Empleado -->
<div class="modal fade rh-modal" id="modalCuentasEmpleado" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <h5 class="modal-title text-white"><i class="fas fa-university me-2"></i>Cuentas — <span id="cuentasEmpleadoNombre">—</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm table-hover" id="tablaCuentasEmpleado">
            <thead class="table-light">
              <tr>
                <th>Banco</th>
                <th>Número de Cuenta</th>
                <th>CLABE</th>
                <th>Default</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody id="cuentasEmpleadoBody"></tbody>
          </table>
        </div>
        <hr>
        <h6 class="fw-bold">Agregar cuenta</h6>
        <div class="row g-2">
          <div class="col-md-4">
            <select id="nuevoBancoId" class="form-select form-select-sm">
              <option value="">Seleccionar banco...</option>
              <!-- Llenado vía AJAX desde cuentas_bancarias -->
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" id="nuevoNumeroCuenta" class="form-control form-control-sm" placeholder="No. Cuenta">
          </div>
          <div class="col-md-3">
            <input type="text" id="nuevoClabe" class="form-control form-control-sm" placeholder="CLABE (18 dígitos)">
          </div>
          <div class="col-md-2">
            <button class="btn btn-sm btn-success w-100" onclick="agregarCuentaEmpleado()">
              <i class="fas fa-plus"></i> Agregar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Cancelar Nómina -->
<div class="modal fade" id="modalCancelarNomina" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Cancelar Nómina</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="cancelar-nomina-id">
        <p class="text-muted">Está a punto de cancelar la nómina <strong id="cancelar-nomina-folio">—</strong>. Esta acción es reversible solo desde base de datos.</p>
        <div class="mb-3">
          <label class="form-label">Motivo de cancelación <span class="text-danger">*</span></label>
          <textarea class="form-control" id="cancelar-motivo" rows="3" placeholder="Describa el motivo (mínimo 10 caracteres)..." required minlength="10"></textarea>
          <small class="text-muted">Mínimo 10 caracteres</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="confirmarCancelarNomina()">
          <i class="fas fa-ban"></i> Confirmar Cancelación
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Agregar Nota -->
<div class="modal fade" id="modalAgregarNota" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <h5 class="modal-title"><i class="fas fa-sticky-note me-2"></i>Nota de Ajuste</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="nota-nomina-id">
        <div class="mb-3">
          <label class="form-label">Tipo</label>
          <select class="form-select" id="nota-tipo">
            <option value="Ajuste">Ajuste</option>
            <option value="Corrección">Corrección</option>
            <option value="Reclasificación">Reclasificación</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Descripción <span class="text-danger">*</span></label>
          <textarea class="form-control" id="nota-descripcion" rows="3" placeholder="Describa el ajuste..." required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Monto (opcional)</label>
          <input type="number" class="form-control" id="nota-monto" step="0.01" placeholder="0.00">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarNota()">
          <i class="fas fa-save"></i> Guardar Nota
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
var periodoFinManual = false;
var actualizandoPeriodoFin = false;

/**
 * Los scripts de esta vista se renderizan DENTRO de <main> (antes de app.js).
 * jQuery solo está disponible tras cargar assets/dist/js/app.js al final del layout.
 * Por eso diferimos toda inicialización que use $ hasta que jQuery exista.
 */
function bootNominaModule() {
  if (typeof jQuery === 'undefined' || typeof $.fn.DataTable === 'undefined') {
    console.error('jQuery/DataTables no cargó correctamente');
    return;
  }
  initTablaNominas();
  bindFiltrosNominas();
  bindPeriodoRapido();
  bindPeriodoFinAuto();
  // Lazy-check: si toca, crea nómina automática (también cubierto por cron CLI)
  $.post('<?= base_url('rh/Nomina/verificar_auto_nomina_ajax') ?>', {
    peticion: 'ajax',
    [csrfName]: csrfHash
  }, function(r) {
    if (r && r.creada) {
      var msg = r.message || ('Nómina automática creada (#' + (r.nomina_id || '') + ').');
      if (r.multiple && r.creadas && r.creadas.length) {
        msg = 'Nóminas automáticas: ' + r.creadas.map(function(n) {
          return n.tipo_nomina + ' ' + n.periodo;
        }).join(' · ');
      } else if (r.calculada) {
        msg = 'Nómina automática #' + r.nomina_id + ' creada y calculada. Revise y procese el pago.';
      }
      notifyShow(msg, 'success');
      if (tablaNominas) tablaNominas.ajax.reload(null, false);
    }
  }, 'json');
}

if (typeof jQuery !== 'undefined') {
  $(document).ready(bootNominaModule);
} else {
  window.addEventListener('load', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(bootNominaModule);
    } else {
      console.error('jQuery no disponible tras load — revisar orden de scripts en general_template');
    }
  });
}

function initTablaNominas() {
  if ($.fn.DataTable.isDataTable('#tablaNominas')) {
    $('#tablaNominas').DataTable().destroy();
  }

  tablaNominas = $('#tablaNominas').DataTable({
    processing: true,
    serverSide: false,
    searching: false,
    ajax: {
      url: '<?= base_url('rh/Nomina/lista_ajax') ?>',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d[csrfName] = csrfHash;
        d.filtro_folio = $('#filtro_folio').val();
        d.filtro_tipo = $('#filtro_tipo').val();
        d.filtro_estatus = $('#filtro_estatus').val();
        d.filtro_periodo_desde = $('#filtro_periodo_desde').val();
        d.filtro_periodo_hasta = $('#filtro_periodo_hasta').val();
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
      { data: 9, orderable: false },
      { data: 10, visible: false, searchable: false, type: 'num' }
    ],
    order: [[10, 'desc']],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json' },
    pageLength: 25,
    autoWidth: false,
    columnDefs: [
      { className: 'text-end', targets: [4, 5, 6] },
      { className: 'text-nowrap', targets: [8, 9] },
      { type: 'html-num-fmt', targets: [4, 5, 6] },
      { targets: 2, orderData: [10, 2] },
      { targets: 10, visible: false, orderable: true, searchable: false }
    ],
    drawCallback: function() {
      refreshLucideIcons();
    },
    initComplete: function() {
      var $wrap = $(this.api().table().container());
      $wrap.find('.dataTables_length select, .dt-length select')
        .removeClass('form-select form-select-sm');
    }
  });

  $('#nomina_tipo').off('change.nomina').on('change.nomina', sugerirPeriodo);
  $('#nomina_fecha_pago').val('<?= date('Y-m-d') ?>');
  refreshLucideIcons();
}

var filtroNominaTimer;
function bindFiltrosNominas() {
  $('#filtro_tipo, #filtro_estatus, #filtro_periodo_desde, #filtro_periodo_hasta')
    .off('change.nominaFiltro')
    .on('change.nominaFiltro', function() {
      if (tablaNominas) tablaNominas.ajax.reload(null, false);
    });
  $('#filtro_folio')
    .off('input.nominaFiltro')
    .on('input.nominaFiltro', function() {
      clearTimeout(filtroNominaTimer);
      filtroNominaTimer = setTimeout(function() {
        if (tablaNominas) tablaNominas.ajax.reload(null, false);
      }, 350);
    });
}

function limpiarFiltrosNominas() {
  $('#filtro_folio').val('');
  $('#filtro_tipo').val('');
  $('#filtro_estatus').val('');
  $('#filtro_periodo_desde').val('');
  $('#filtro_periodo_hasta').val('');
  if (tablaNominas) tablaNominas.ajax.reload(null, false);
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

function setPeriodoFinAuto(val) {
  actualizandoPeriodoFin = true;
  $('#periodoFin').val(val);
  actualizandoPeriodoFin = false;
}

function actualizarHintPeriodoFin() {
  var tipo = $('#nomina_tipo').val();
  var auto = tipo !== 'Extraordinaria' && tipo !== 'Aguinaldo';
  $('#periodoFinHint').text(auto
    ? 'Auto-calculado según tipo de nómina'
    : 'Defina manualmente el periodo fin');
}

function calcularPeriodoFinDesdeInicio() {
  var tipo = $('#nomina_tipo').val();
  var inicioStr = $('#periodoInicio').val();
  actualizarHintPeriodoFin();
  if (!inicioStr || tipo === 'Extraordinaria' || tipo === 'Aguinaldo' || periodoFinManual) {
    return;
  }
  var partes = inicioStr.split('-');
  var inicio = new Date(parseInt(partes[0], 10), parseInt(partes[1], 10) - 1, parseInt(partes[2], 10));
  var fin;
  if (tipo === 'Semanal') {
    fin = new Date(inicio);
    fin.setDate(inicio.getDate() + 6);
  } else if (tipo === 'Quincenal') {
    fin = new Date(inicio);
    fin.setDate(inicio.getDate() + 14);
  } else if (tipo === 'Mensual') {
    fin = new Date(inicio.getFullYear(), inicio.getMonth() + 1, 0);
  } else {
    return;
  }
  var finStr = formatDate(fin);
  setPeriodoFinAuto(finStr);
  $('#nomina_fecha_pago').val(finStr);
}

function bindPeriodoFinAuto() {
  $('#periodoInicio')
    .off('change.nominaPeriodo')
    .on('change.nominaPeriodo', calcularPeriodoFinDesdeInicio);
  $('#periodoFin')
    .off('change.nominaPeriodoManual input.nominaPeriodoManual')
    .on('change.nominaPeriodoManual input.nominaPeriodoManual', function() {
      if (!actualizandoPeriodoFin) {
        periodoFinManual = true;
      }
    });
  $('#nomina_tipo')
    .off('change.nominaPeriodoHint')
    .on('change.nominaPeriodoHint', function() {
      periodoFinManual = false;
      actualizarHintPeriodoFin();
    });
}

function sugerirPeriodo() {
  periodoFinManual = false;
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
    var diaSemana = hoy.getDay();
    inicio = new Date(hoy);
    inicio.setDate(hoy.getDate() - (diaSemana === 0 ? 6 : diaSemana - 1));
    fin = new Date(inicio);
    fin.setDate(inicio.getDate() + 6);
  }
  actualizarHintPeriodoFin();
  if (inicio && fin) {
    $('#periodoInicio').val(formatDate(inicio));
    setPeriodoFinAuto(formatDate(fin));
    $('#nomina_fecha_pago').val(formatDate(fin));
    $('.periodo-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
  }
}

function mostrarModalNuevo() {
  $('#formNomina')[0].reset();
  periodoFinManual = false;
  $('.periodo-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
  $('#nomina_fecha_pago').val('<?= date('Y-m-d') ?>');
  $('#nomina_tipo').val('Semanal');
  sugerirPeriodo();
  $('#modalNomina').modal('show');
  refreshLucideIcons();
}

function parseJsonSafe(result, fallbackMsg) {
  if (typeof result === 'object' && result !== null) return result;
  try {
    return JSON.parse(result);
  } catch (e) {
    notifyShow(fallbackMsg || 'Error al procesar la respuesta del servidor', 'danger');
    return null;
  }
}

function fmtFechaUi(val) {
  if (!val) return '—';
  var m = String(val).match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (m) return m[3] + '/' + m[2] + '/' + m[1];
  return val;
}

function guardarNomina() {
  if (!$('#nomina_tipo').val() || !$('#periodoInicio').val() || !$('#periodoFin').val() || !$('#nomina_fecha_pago').val()) {
    notifyShow('Complete tipo, periodo y fecha de pago', 'warning');
    return;
  }
  var formData = $('#formNomina').serialize();
  formData += '&peticion=ajax&' + csrfName + '=' + csrfHash;
  $.post('<?= base_url('rh/Nomina/crear_ajax') ?>', formData, function(result) {
    result = parseJsonSafe(result);
    if (!result) return;
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
    result = parseJsonSafe(result, 'Error al calcular la nómina');
    if (!result) return;
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if (result.success) {
      recargarTablaNominas();
      if (typeof callback === 'function') callback();
    }
  });
}

function verNomina(id, mostrarNotas) {
  $.post('<?= base_url('rh/Nomina/get_nomina_detalle_completo_ajax') ?>', {
    id: id, peticion: 'ajax', [csrfName]: csrfHash
  }, function(r) {
    r = parseJsonSafe(r);
    if (!r) return;
    if (!r.success) { notifyShow(r.message || 'Error al cargar el detalle', 'danger'); return; }

    $('#detalleFolio').text(r.nomina.folio);
    $('#detallePeriodo').text(fmtFechaUi(r.nomina.periodo_inicio) + ' — ' + fmtFechaUi(r.nomina.periodo_fin));
    $('#detalleTipo').text(r.nomina.tipo_nomina);
    $('#detalleFechaPago').text(fmtFechaUi(r.nomina.fecha_pago));
    $('#detalleEstatus').html(renderBadgeEstatus(r.nomina.estatus));

    $('#modalDetalleNomina').data('nomina-id', id);
    $('#modalDetalleNomina').data('nomina-estatus', r.nomina.estatus);

    renderTablaDetalle(r.detalle, r.nomina.estatus);

    cargarNotasNomina(id);
    if (r.nomina && (r.nomina.estatus === 'Pagada' || r.nomina.estatus === 'Parcial')) {
      $('#seccionNotasNomina').show();
    } else if (!mostrarNotas) {
      $('#seccionNotasNomina').hide();
    }

    $('#modalDetalleNomina').modal('show');
  });
}

function calcularNomina(id) {
  if (!confirm('¿Calcular esta nómina con los salarios y deducciones de cada empleado?')) return;
  $.post('<?= base_url('rh/Nomina/calcular_ajax') ?>', { id: id, peticion: 'ajax', [csrfName]: csrfHash }, function(result) {
    result = parseJsonSafe(result);
    if (!result) return;
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
    result = parseJsonSafe(result);
    if (!result) {
      $('#modalProcesarPago').modal('hide');
      return;
    }
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
  $('#pago-nomina-subtitulo').text(
    n.folio + ' · ' + n.tipo_nomina + ' · ' +
    fmtFechaUi(n.periodo_inicio) + ' al ' + fmtFechaUi(n.periodo_fin)
  );
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
  $('#buscadorPago').val('');
  recalcularPagoSeleccion();
  refreshLucideIcons();
}

function filtrarTablaPago() {
  var q = ($('#buscadorPago').val() || '').toLowerCase().trim();
  $('#pago-empleados-body tr[data-idx]').each(function() {
    var idx = $(this).data('idx');
    var emp = pagoEmpleadosData[idx];
    var nombre = (emp && emp.nombre ? emp.nombre : '').toLowerCase();
    var match = !q || nombre.indexOf(q) !== -1;
    $(this).toggle(match);
    var $conceptos = $('#conceptos-pago-' + idx);
    if (!match) {
      $conceptos.hide();
    } else if ($conceptos.hasClass('d-none')) {
      $conceptos.hide();
    } else {
      $conceptos.show();
    }
  });
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
    result = parseJsonSafe(result);
    if (!result) return;
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
  // Export multi-hoja (relación + transferencias + resumen) — el de uso diario
  window.location.href = '<?= base_url('rh/Nomina/exportar_detalle_excel/') ?>' + id;
}

function eliminarNomina(id) {
  if (!confirm('¿Eliminar permanentemente esta nómina?')) return;
  $.post('<?= base_url('rh/Nomina/eliminar_ajax') ?>', { id: id, peticion: 'ajax', [csrfName]: csrfHash }, function(result) {
    result = parseJsonSafe(result);
    if (!result) return;
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

function descargarReciboIndividual(detalleId) {
  $.post('<?= base_url('rh/Nomina/get_recibo_individual_ajax') ?>', {
    detalle_id: detalleId, peticion: 'ajax', [csrfName]: csrfHash
  }, function(result) {
    result = parseJsonSafe(result);
    if (!result || !result.html) {
      notifyShow('Error al generar el recibo', 'danger');
      return;
    }
    var tempDiv = document.createElement('div');
    tempDiv.innerHTML = result.html;
    tempDiv.style.position = 'absolute';
    tempDiv.style.left = '-9999px';
    document.body.appendChild(tempDiv);

    html2pdf().set({
      margin: [5, 5, 5, 5],
      filename: 'recibo_' + detalleId + '.pdf',
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2 },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).from(tempDiv).save().then(function() {
      document.body.removeChild(tempDiv);
    });
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

// --- Configuración de automatización ---

function abrirModalConfiguracion() {
  $.post('<?= base_url('rh/Nomina/get_configuracion_ajax') ?>', {
    peticion: 'ajax', [csrfName]: csrfHash
  }, function(r) {
    try { if (typeof r === 'string') r = JSON.parse(r); } catch (e) { return; }
    if (r.success && r.config) {
      $('#configFrecuencia').val(r.config.frecuencia);
      $('#configDiasAntes').val(r.config.crear_dias_antes || 1);
      $('#configAutoCrear').prop('checked', r.config.auto_crear == 1);
    }
    $('#modalConfiguracion').modal('show');
  });
}

function guardarConfiguracion() {
  $.post('<?= base_url('rh/Nomina/guardar_configuracion_ajax') ?>', {
    frecuencia: $('#configFrecuencia').val(),
    crear_dias_antes: $('#configDiasAntes').val(),
    auto_crear: $('#configAutoCrear').is(':checked') ? 1 : 0,
    peticion: 'ajax',
    [csrfName]: csrfHash
  }, function(r) {
    try { if (typeof r === 'string') r = JSON.parse(r); } catch (e) {
      notifyShow('Error al procesar la respuesta', 'danger');
      return;
    }
    if (r.success) {
      notifyShow(r.message || 'Configuración guardada', 'success');
      $('#modalConfiguracion').modal('hide');
    } else {
      notifyShow(r.message || 'Error al guardar', 'danger');
    }
  });
}

// --- Modal de detalle (formato tabla nuevo) ---

function renderTablaDetalle(detalle, estatus) {
  var soloLectura = estatus === 'Pagada' || estatus === 'Cancelada';
  var editableCls = soloLectura ? '' : 'editable';
  var editableTitle = soloLectura ? '' : ' title="Doble clic para editar"';
  var html = '';
  var totales = {
    sueldo_diario: 0, sueldo_neto: 0, horas_extras: 0, monto_horas_extras: 0,
    comidas: 0, viaticos: 0, prima: 0, bonos: 0, otros: 0,
    percepciones: 0, infonavit: 0, prestamo: 0, otros_desc: 0,
    deducciones: 0, neto: 0
  };

  (detalle || []).forEach(function(d) {
    html += '<tr data-detalle-id="' + d.detalle_id + '">';
    html += '<td class="' + editableCls + ' text-center" data-field="lugar_origen"' + editableTitle + '>' + esc(d.lugar_origen) + '</td>';
    html += '<td>' + esc(d.nombre + ' ' + d.apellido_paterno + ' ' + (d.apellido_materno || '')) + '</td>';
    html += '<td class="text-end">' + fmt(d.sueldo_diario) + '</td>';
    html += '<td class="text-end">' + fmt(d.sueldo_base) + '</td>';
    html += '<td class="' + editableCls + ' text-center" data-field="horas_extras" data-type="number"' + editableTitle + '>' + fmtNum(d.horas_extras) + '</td>';
    html += '<td class="' + editableCls + ' text-end" data-field="costo_hora_extra" data-type="money"' + editableTitle + '>' + fmt(d.costo_hora_extra) + '</td>';
    html += '<td class="text-end fw-bold">' + fmt(d.monto_horas_extras) + '</td>';
    html += '<td class="' + editableCls + ' text-end" data-field="comidas" data-type="money"' + editableTitle + '>' + fmt(d.comidas) + '</td>';
    html += '<td class="' + editableCls + ' text-end" data-field="viaticos_pasajes" data-type="money"' + editableTitle + '>' + fmt(d.viaticos_pasajes) + '</td>';
    html += '<td class="' + editableCls + ' text-end" data-field="prima" data-type="money"' + editableTitle + '>' + fmt(d.prima) + '</td>';
    html += '<td class="' + editableCls + ' text-end" data-field="otros_bonos" data-type="money"' + editableTitle + '>' + fmt(d.otros_bonos) + '</td>';
    html += '<td class="' + editableCls + ' text-end" data-field="otros_ingresos" data-type="money"' + editableTitle + '>' + fmt(d.otros_ingresos) + '</td>';
    html += '<td class="text-end bg-success text-white fw-bold">' + fmt(d.percepciones) + '</td>';
    html += '<td class="text-end bg-danger text-white fw-bold">' + fmt(d.infonavit_descuento) + '</td>';
    html += '<td class="' + editableCls + ' text-end" data-field="prestamo_personal" data-type="money"' + editableTitle + '>' + fmt(d.prestamo_personal) + '</td>';
    html += '<td class="' + editableCls + ' text-end" data-field="otros_descuentos" data-type="money"' + editableTitle + '>' + fmt(d.otros_descuentos) + '</td>';
    html += '<td class="text-end bg-danger text-white fw-bold">' + fmt(d.deducciones) + '</td>';
    html += '<td class="text-end bg-primary text-white fw-bold">' + fmt(d.neto) + '</td>';
    html += '<td class="text-center">' + badgeFormaPago(d.forma_pago) + '</td>';
    html += '<td class="text-center">';
    html += '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="descargarReciboIndividual(' + d.detalle_id + ')" title="Descargar recibo individual">';
    html += '<i class="fas fa-file-pdf"></i>';
    html += '</button></td>';
    html += '<td class="text-center" style="min-width:160px;">';
    var cuentas = d.cuentas_bancarias || [];
    if (cuentas.length === 0) {
      html += '<span class="text-muted small">Sin cuentas</span> ';
      html += '<button type="button" class="btn btn-sm btn-outline-primary py-0 px-1" onclick="verCuentasEmpleado(' + d.empleado_id + ',\'' + escJS(d.nombre) + '\')" title="Agregar cuenta bancaria">+ Agregar</button>';
    } else {
      var defaultId = d.cuenta_default ? d.cuenta_default.id : cuentas[0].id;
      html += '<select class="form-select form-select-sm" style="min-width:150px;" title="Cuenta bancaria (informativo)">';
      cuentas.forEach(function(c) {
        var num = String(c.numero_cuenta || c.clabe || '');
        var ult4 = num.length >= 4 ? num.slice(-4) : (num || '—');
        var label = esc((c.banco || 'Banco') + ' - ' + ult4);
        html += '<option value="' + c.id + '"' + (c.id == defaultId ? ' selected' : '') + '>' + label + '</option>';
      });
      html += '</select>';
    }
    html += '</td>';
    html += '</tr>';

    totales.sueldo_diario += parseFloat(d.sueldo_diario) || 0;
    totales.sueldo_neto += parseFloat(d.sueldo_base) || 0;
    totales.horas_extras += parseFloat(d.horas_extras) || 0;
    totales.monto_horas_extras += parseFloat(d.monto_horas_extras) || 0;
    totales.comidas += parseFloat(d.comidas) || 0;
    totales.viaticos += parseFloat(d.viaticos_pasajes) || 0;
    totales.prima += parseFloat(d.prima) || 0;
    totales.bonos += parseFloat(d.otros_bonos) || 0;
    totales.otros += parseFloat(d.otros_ingresos) || 0;
    totales.percepciones += parseFloat(d.percepciones) || 0;
    totales.infonavit += parseFloat(d.infonavit_descuento) || 0;
    totales.prestamo += parseFloat(d.prestamo_personal) || 0;
    totales.otros_desc += parseFloat(d.otros_descuentos) || 0;
    totales.deducciones += parseFloat(d.deducciones) || 0;
    totales.neto += parseFloat(d.neto) || 0;
  });

  $('#detalleNominaBody').html(html);

  var footer = '<td class="text-center fw-bold">TOTALES</td>';
  footer += '<td></td>';
  footer += '<td class="text-end fw-bold">' + fmt(totales.sueldo_diario) + '</td>';
  footer += '<td class="text-end fw-bold">' + fmt(totales.sueldo_neto) + '</td>';
  footer += '<td class="text-center">' + fmtNum(totales.horas_extras) + '</td>';
  footer += '<td></td>';
  footer += '<td class="text-end fw-bold">' + fmt(totales.monto_horas_extras) + '</td>';
  footer += '<td class="text-end fw-bold">' + fmt(totales.comidas) + '</td>';
  footer += '<td class="text-end fw-bold">' + fmt(totales.viaticos) + '</td>';
  footer += '<td class="text-end fw-bold">' + fmt(totales.prima) + '</td>';
  footer += '<td class="text-end fw-bold">' + fmt(totales.bonos) + '</td>';
  footer += '<td class="text-end fw-bold">' + fmt(totales.otros) + '</td>';
  footer += '<td class="text-end bg-success text-white fw-bold">' + fmt(totales.percepciones) + '</td>';
  footer += '<td class="text-end bg-danger text-white fw-bold">' + fmt(totales.infonavit) + '</td>';
  footer += '<td class="text-end fw-bold">' + fmt(totales.prestamo) + '</td>';
  footer += '<td class="text-end fw-bold">' + fmt(totales.otros_desc) + '</td>';
  footer += '<td class="text-end bg-danger text-white fw-bold">' + fmt(totales.deducciones) + '</td>';
  footer += '<td class="text-end bg-primary text-white fw-bold">' + fmt(totales.neto) + '</td>';
  footer += '<td></td>'; // forma de pago
  footer += '<td></td>'; // recibo
  footer += '<td></td>'; // banco/cuenta
  $('#detalleNominaFooter').html(footer);

  $('#buscadorDetalle').val('');
  if (soloLectura) {
    $('#detalleFooterHint').html('Nómina finalizada — solo lectura');
  } else {
    $('#detalleFooterHint').html('Celdas en amarillo claro: <strong>doble clic</strong> para editar (horas extras, comidas, descuentos, etc.).');
    activarEdicionInline();
  }
}

function filtrarTablaDetalle() {
  var q = ($('#buscadorDetalle').val() || '').toLowerCase().trim();
  var visibles = 0;
  $('#detalleNominaBody tr[data-detalle-id]').each(function() {
    var nombre = $(this).find('td').eq(1).text().toLowerCase();
    var match = !q || nombre.indexOf(q) !== -1;
    $(this).toggle(match);
    if (match) visibles++;
  });
  $('#tablaDetalleNomina tfoot').toggle(!q || visibles > 0);
}

function activarEdicionInline() {
  $('#detalleNominaBody .editable').off('dblclick').on('dblclick', function() {
    var td = $(this);
    if (td.find('input').length > 0) return;

    var valActual = td.text().replace(/[$,]/g, '').trim();
    var field = td.data('field');
    var type = td.data('type') || 'text';
    var detalleId = td.closest('tr').data('detalle-id');
    var textoOriginal = td.text();

    var input = $('<input type="' + (type === 'number' || type === 'money' ? 'number' : 'text') + '" class="form-control form-control-sm" style="width:100%;min-width:80px;">')
      .val(valActual)
      .on('blur', function() {
        var nuevoVal = $(this).val();
        td.text(type === 'money' ? fmt(parseFloat(nuevoVal) || 0) : nuevoVal);
        $(this).remove();

        var data = { detalle_id: detalleId, peticion: 'ajax', [csrfName]: csrfHash };
        data[field] = (type === 'number' || type === 'money') ? (parseFloat(nuevoVal) || 0) : nuevoVal;
        $.post('<?= base_url('rh/Nomina/actualizar_detalle_ajax') ?>', data, function(r) {
          try { if (typeof r === 'string') r = JSON.parse(r); } catch (e) {
            notifyShow('Error al procesar la respuesta', 'danger');
            return;
          }
          if (r.success) {
            var nominaId = $('#modalDetalleNomina').data('nomina-id');
            verNomina(nominaId);
          } else {
            notifyShow(r.message || 'Error al guardar', 'danger');
            td.text(textoOriginal);
          }
        });
      })
      .on('keydown', function(e) {
        if (e.key === 'Enter') $(this).blur();
        if (e.key === 'Escape') { td.text(textoOriginal); $(this).remove(); }
      });

    td.empty().append(input);
    input.focus().select();
  });
}

// --- Cuentas bancarias del empleado ---

function verCuentasEmpleado(empleadoId, nombre) {
  $('#cuentasEmpleadoNombre').text(nombre);
  $('#modalCuentasEmpleado').data('empleado-id', empleadoId);
  cargarCuentasEmpleado(empleadoId);
  cargarCatalogoBancos();
  $('#modalCuentasEmpleado').modal('show');
}

function cargarCuentasEmpleado(empleadoId) {
  $.post('<?= base_url('rh/Nomina/get_nomina_cuentas_ajax') ?>', {
    empleado_id: empleadoId, peticion: 'ajax', [csrfName]: csrfHash
  }, function(r) {
    try { if (typeof r === 'string') r = JSON.parse(r); } catch (e) { return; }
    var html = '';
    if (r.cuentas && r.cuentas.length > 0) {
      r.cuentas.forEach(function(c) {
        html += '<tr>';
        html += '<td>' + esc(c.banco || '—') + '</td>';
        html += '<td>' + esc(c.numero_cuenta) + '</td>';
        html += '<td>' + esc(c.clabe || '—') + '</td>';
        html += '<td>' + (c.es_default == 1 ? '<span class="badge bg-success">Principal</span>' :
          '<button class="btn btn-sm btn-outline-success" onclick="setCuentaDefault(' + empleadoId + ',' + c.id + ')">Establecer</button>') + '</td>';
        html += '<td class="text-end"><button class="btn btn-sm btn-outline-danger" onclick="eliminarCuentaEmpleado(' + c.id + ',' + empleadoId + ')"><i class="fas fa-trash"></i></button></td>';
        html += '</tr>';
      });
    } else {
      html = '<tr><td colspan="5" class="text-center text-muted">Sin cuentas registradas</td></tr>';
    }
    $('#cuentasEmpleadoBody').html(html);
  });
}

function cargarCatalogoBancos() {
  $.get('<?= base_url('rh/Nomina/get_catalogo_bancos_ajax') ?>', function(r) {
    try { if (typeof r === 'string') r = JSON.parse(r); } catch (e) { return; }
    var opts = '<option value="">Seleccionar banco...</option>';
    if (r.bancos) {
      r.bancos.forEach(function(b) {
        opts += '<option value="' + b.id + '">' + esc(b.banco) + '</option>';
      });
    }
    $('#nuevoBancoId').html(opts);
  });
}

function agregarCuentaEmpleado() {
  var empleadoId = $('#modalCuentasEmpleado').data('empleado-id');
  $.post('<?= base_url('rh/Nomina/guardar_cuenta_empleado_ajax') ?>', {
    empleado_id: empleadoId,
    cuenta_bancaria_id: $('#nuevoBancoId').val(),
    numero_cuenta: $('#nuevoNumeroCuenta').val(),
    clabe: $('#nuevoClabe').val(),
    es_default: 0,
    peticion: 'ajax',
    [csrfName]: csrfHash
  }, function(r) {
    try { if (typeof r === 'string') r = JSON.parse(r); } catch (e) {
      notifyShow('Error al procesar la respuesta', 'danger');
      return;
    }
    if (r.success) {
      $('#nuevoNumeroCuenta, #nuevoClabe').val('');
      cargarCuentasEmpleado(empleadoId);
      notifyShow(r.message || 'Cuenta agregada', 'success');
    } else {
      notifyShow(r.message || 'Error al agregar cuenta', 'danger');
    }
  });
}

function setCuentaDefault(empleadoId, cuentaId) {
  $.post('<?= base_url('rh/Nomina/set_cuenta_default_ajax') ?>', {
    empleado_id: empleadoId, cuenta_id: cuentaId, peticion: 'ajax', [csrfName]: csrfHash
  }, function(r) {
    try { if (typeof r === 'string') r = JSON.parse(r); } catch (e) { return; }
    if (r.success) {
      cargarCuentasEmpleado(empleadoId);
      notifyShow(r.message || 'Cuenta principal actualizada', 'success');
    }
  });
}

function eliminarCuentaEmpleado(cuentaId, empleadoId) {
  if (!confirm('¿Eliminar esta cuenta?')) return;
  $.post('<?= base_url('rh/Nomina/eliminar_cuenta_empleado_ajax') ?>', {
    id: cuentaId, peticion: 'ajax', [csrfName]: csrfHash
  }, function(r) {
    try { if (typeof r === 'string') r = JSON.parse(r); } catch (e) { return; }
    if (r.success) {
      cargarCuentasEmpleado(empleadoId);
      notifyShow(r.message || 'Cuenta eliminada', 'info');
    }
  });
}

function exportarDetalleExcel() {
  var id = $('#modalDetalleNomina').data('nomina-id');
  if (!id) {
    notifyShow('No hay nómina seleccionada para exportar', 'warning');
    return;
  }
  window.location.href = '<?= base_url('rh/Nomina/exportar_detalle_excel/') ?>' + id;
}

// --- Selector inteligente de fechas (se enlaza tras cargar jQuery) ---

function bindPeriodoRapido() {
  $(document).off('click.periodoRapido', '.periodo-btn').on('click.periodoRapido', '.periodo-btn', function() {
    var rango = $(this).data('rango');
    var inicio, fin;
    var hoy = new Date();

    switch (rango) {
      case 'semana':
        var diaSemana = hoy.getDay();
        var lunes = new Date(hoy);
        lunes.setDate(hoy.getDate() - (diaSemana === 0 ? 6 : diaSemana - 1));
        inicio = lunes;
        fin = new Date(lunes);
        fin.setDate(lunes.getDate() + 6);
        break;
      case 'semana_anterior':
        var diaSemana2 = hoy.getDay();
        var lunesAnt = new Date(hoy);
        lunesAnt.setDate(hoy.getDate() - (diaSemana2 === 0 ? 6 : diaSemana2 - 1) - 7);
        inicio = lunesAnt;
        fin = new Date(lunesAnt);
        fin.setDate(lunesAnt.getDate() + 6);
        break;
      case 'quincena1':
        inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        fin = new Date(hoy.getFullYear(), hoy.getMonth(), 15);
        break;
      case 'quincena2':
        inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 16);
        fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
        break;
      case 'mes_actual':
        inicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        fin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
        break;
      case 'mes_anterior':
        inicio = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        fin = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
        break;
      default: return;
    }

    periodoFinManual = false;
    $('#periodoInicio').val(formatDate(inicio));
    setPeriodoFinAuto(formatDate(fin));
    $('#nomina_fecha_pago').val(formatDate(fin));
    actualizarHintPeriodoFin();

    // Sincronizar tipo de nómina según el rango elegido
    var tipoSelect = $('#nomina_tipo');
    if (rango === 'semana' || rango === 'semana_anterior') {
      tipoSelect.val('Semanal');
    } else if (rango === 'quincena1' || rango === 'quincena2') {
      tipoSelect.val('Quincenal');
    } else if (rango === 'mes_actual' || rango === 'mes_anterior') {
      tipoSelect.val('Mensual');
    }

    $('.periodo-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
    $(this).removeClass('btn-outline-secondary').addClass('active btn-primary');
  });
}

// --- Helpers ---

function formatDate(d) {
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

function fmt(val) {
  return '$' + parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function fmtNum(val) {
  return parseFloat(val || 0).toFixed(2);
}

function esc(str) {
  // Evita depender de jQuery por si se llama antes del boot
  var d = document.createElement('span');
  d.textContent = str || '';
  return d.innerHTML;
}

function escJS(str) {
  return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function renderBadgeEstatus(estatus) {
  var map = { Borrador: 'secondary', Calculada: 'warning', Parcial: 'info', Pagada: 'success', Cancelada: 'danger' };
  return '<span class="badge bg-' + (map[estatus] || 'secondary') + '">' + estatus + '</span>';
}

function badgeFormaPago(forma) {
  var f = (forma || '').trim();
  if (!f) return '<span class="badge bg-secondary">Sin definir</span>';
  var map = { Transferencia: 'primary', Cheque: 'info', Efectivo: 'success', 'Depósito': 'warning' };
  return '<span class="badge bg-' + (map[f] || 'secondary') + '">' + esc(f) + '</span>';
}

// --- Cancelar nómina ---
var nominaAEliminarId = null;
function pedirCancelarNomina(id, folio) {
  nominaAEliminarId = id;
  $('#cancelar-nomina-id').val(id);
  $('#cancelar-nomina-folio').text(folio);
  $('#cancelar-motivo').val('');
  $('#modalCancelarNomina').modal('show');
}
function confirmarCancelarNomina() {
  var id = $('#cancelar-nomina-id').val();
  var motivo = $('#cancelar-motivo').val().trim();
  if (motivo.length < 10) {
    notifyShow('El motivo debe tener al menos 10 caracteres', 'warning');
    return;
  }
  $.post('<?= base_url('rh/Nomina/eliminar_ajax') ?>', {
    id: id, motivo: motivo, peticion: 'ajax', [csrfName]: csrfHash
  }, function(result) {
    result = parseJsonSafe(result);
    if (!result) return;
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if (result.success) {
      $('#modalCancelarNomina').modal('hide');
      recargarTablaNominas();
    }
  });
}

// --- Notas de ajuste ---
function abrirModalNota() {
  $('#nota-nomina-id').val($('#modalDetalleNomina').data('nomina-id'));
  $('#nota-tipo').val('Ajuste');
  $('#nota-descripcion').val('');
  $('#nota-monto').val('');
  $('#modalAgregarNota').modal('show');
}
function guardarNota() {
  var id = $('#nota-nomina-id').val();
  var desc = $('#nota-descripcion').val().trim();
  if (!desc) {
    notifyShow('La descripción es requerida', 'warning');
    return;
  }
  $.post('<?= base_url('rh/Nomina/agregar_nota_ajax') ?>', {
    nomina_id: id,
    tipo: $('#nota-tipo').val(),
    descripcion: desc,
    monto: $('#nota-monto').val(),
    peticion: 'ajax',
    [csrfName]: csrfHash
  }, function(result) {
    result = parseJsonSafe(result);
    if (!result) return;
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if (result.success) {
      $('#modalAgregarNota').modal('hide');
      cargarNotasNomina(id);
    }
  });
}
function cargarNotasNomina(nominaId) {
  var estatus = $('#modalDetalleNomina').data('nomina-estatus');
  var puedeNotas = estatus === 'Pagada' || estatus === 'Parcial';
  $.post('<?= base_url('rh/Nomina/get_notas_ajax') ?>', {
    nomina_id: nominaId, peticion: 'ajax', [csrfName]: csrfHash
  }, function(result) {
    result = parseJsonSafe(result);
    if (!result || !result.notas) return;
    if (result.notas.length > 0) {
      var html = '';
      result.notas.forEach(function(n) {
        var badgeTipo = { Ajuste: 'warning', Corrección: 'info', Reclasificación: 'secondary' };
        html += '<div class="border-bottom py-1">';
        html += '<span class="badge bg-' + (badgeTipo[n.tipo] || 'secondary') + ' me-1">' + esc(n.tipo) + '</span>';
        html += '<strong>' + esc(n.descripcion) + '</strong>';
        if (n.monto) html += ' <span class="text-danger">($' + parseFloat(n.monto).toFixed(2) + ')</span>';
        html += '<div class="text-muted" style="font-size:0.7rem;">' + (n.usuario_nombre || 'Sistema') + ' · ' + n.created_at + '</div>';
        html += '</div>';
      });
      $('#notasNominaBody').html(html);
    } else {
      $('#notasNominaBody').html('<div class="text-muted">Sin notas registradas.</div>');
    }
    if (result.notas.length > 0 || puedeNotas) {
      $('#seccionNotasNomina').show();
    } else {
      $('#seccionNotasNomina').hide();
    }
  });
}
</script>

<style>
@media (max-width: 767px) {
  .rh-nomina-page .table-responsive {
    -webkit-overflow-scrolling: touch;
    overflow-x: auto;
  }
  .rh-nomina-page .dataTables_wrapper .dataTables_filter,
  .rh-nomina-page .dataTables_wrapper .dataTables_length {
    float: none;
    text-align: left;
    margin-bottom: 8px;
  }
  #tablaNominas_wrapper .row {
    flex-direction: column;
  }
}
</style>
