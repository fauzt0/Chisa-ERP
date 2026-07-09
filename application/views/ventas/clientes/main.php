<?php
/**
 * Vista principal de Clientes
 * Gestión de clientes del sistema CRM
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
        <li class="breadcrumb-item active">Clientes</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Título y botón nuevo -->
<div class="row mb-3">
  <div class="col-md-6">
    <h2><i class="fas fa-users"></i> Gestión de Clientes</h2>
  </div>
  <div class="col-md-6 text-end">
    <button type="button" class="btn btn-primary" onclick="mostrarModalNuevo()">
      <i class="fas fa-plus"></i> Nuevo Cliente
    </button>
  </div>
</div>

<!-- Estadísticas -->
<!-- Estadísticas -->
<div class="row mb-4">
  <!-- Total Clientes -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Total Clientes</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?=number_format($stats['total_clientes'] ?? 0)?>
            </h3>
          </div>
          <div class="col-4 text-end">
             <i class="fas fa-users text-primary" style="font-size: 1.5rem;"></i>
          </div>
        </div>
        
        <div class="progress progress-sm shadow-sm mb-1">
          <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
        </div>
        
        <small class="text-muted">Nuevos (30d): <?=$stats['nuevos_30_dias'] ?? 0?></small>
      </div>
    </div>
  </div>

  <!-- Clientes Activos -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Clientes Activos</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?=number_format($stats['clientes_activos'] ?? 0)?>
            </h3>
          </div>
          <div class="col-4 text-end">
             <span class="badge bg-success"><?=$stats['porcentaje_activos'] ?? 0?>%</span>
          </div>
        </div>
        
        <div class="progress progress-sm shadow-sm mb-1">
          <div class="progress-bar bg-success" role="progressbar" style="width: <?=$stats['porcentaje_activos'] ?? 0?>%"></div>
        </div>
        
        <small class="text-muted">Del total de clientes</small>
      </div>
    </div>
  </div>

  <!-- Clientes Regulares -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Clientes Regulares</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?=number_format($stats['clientes_regulares'] ?? 0)?>
            </h3>
          </div>
          <div class="col-4 text-end">
            <span class="badge bg-info"><?=$stats['porcentaje_regulares'] ?? 0?>%</span>
          </div>
        </div>
        
        <div class="progress progress-sm shadow-sm mb-1">
          <div class="progress-bar bg-info" role="progressbar" style="width: <?=$stats['porcentaje_regulares'] ?? 0?>%"></div>
        </div>

        <small class="text-muted">Tipo regular</small>
      </div>
    </div>
  </div>

  <!-- Con Saldo Pendiente -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Con Saldo</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?=number_format($stats['clientes_con_saldo'] ?? 0)?>
            </h3>
          </div>
          <div class="col-4 text-end">
            <span class="badge bg-warning"><?=$stats['porcentaje_con_saldo'] ?? 0?>%</span>
          </div>
        </div>
        
        <div class="progress progress-sm shadow-sm mb-1">
          <div class="progress-bar bg-warning" role="progressbar" style="width: <?=$stats['porcentaje_con_saldo'] ?? 0?>%"></div>
        </div>
        
        <small class="text-muted">Clientes con deuda</small>
      </div>
    </div>
  </div>
</div>

<!-- Tabla de Clientes -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0"><i class="fas fa-list"></i> Directorio de Clientes</h3>
    <button type="button" class="btn btn-primary btn-sm" onclick="mostrarModalNuevo()">
      <i class="fas fa-plus"></i> Nuevo Cliente
    </button>
  </div>
  <div class="card-body">
    <!-- Barra CRM -->
    <div class="crm-toolbar mb-3 p-3 bg-light rounded border">
      <div class="row g-2 align-items-end">
        <div class="col-lg-4">
          <label class="form-label small text-muted mb-1">Búsqueda rápida</label>
          <div class="input-group input-group-sm">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="busquedaClientes" placeholder="Nombre, RFC, teléfono, ciudad...">
            <button class="btn btn-outline-secondary" type="button" id="btnLimpiarBusquedaCli" title="Limpiar"><i class="fas fa-times"></i></button>
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Tipo</label>
          <select class="form-select form-select-sm" id="filtro_tipo_cliente">
            <option value="">Todos</option>
            <option value="Regular">Regular</option>
            <option value="Mostrador">Mostrador</option>
            <option value="Gobierno">Gobierno</option>
            <option value="Licitación">Licitación</option>
            <option value="Distribuidor">Distribuidor</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Estatus</label>
          <select class="form-select form-select-sm" id="filtro_estatus">
            <option value="">Todos</option>
            <option value="Activo">Activo</option>
            <option value="Inactivo">Inactivo</option>
            <option value="Suspendido">Suspendido</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small text-muted mb-1">Saldo</label>
          <select class="form-select form-select-sm" id="filtro_saldo">
            <option value="">Todos</option>
            <option value="con_saldo">Con Saldo</option>
            <option value="sin_saldo">Sin Saldo</option>
          </select>
        </div>
        <div class="col-md-2">
          <button type="button" class="btn btn-sm btn-secondary w-100" onclick="limpiarFiltros()">
            <i class="fas fa-eraser"></i> Limpiar
          </button>
        </div>
      </div>
    </div>
    
    <table id="tablaClientes" class="table table-bordered table-striped table-hover table-sm">
      <thead class="table-light">
        <tr>
          <th>Código</th>
          <th>Razón Social</th>
          <th>RFC</th>
          <th>Contacto</th>
          <th>Ubicación</th>
          <th>Saldo</th>
          <th>Tipo</th>
          <th>Estatus</th>
          <th width="110">Acciones</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal: Nuevo/Editar Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalClienteTitle">Nuevo Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formCliente">
          <input type="hidden" id="cliente_id">
          
          <!-- Datos Fiscales -->
          <h6><i class="fas fa-file-invoice"></i> Datos Fiscales</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Razón Social <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cliente_razon_social" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nombre Comercial</label>
              <input type="text" class="form-control" id="cliente_nombre_comercial">
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">RFC <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cliente_rfc" maxlength="13" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Régimen Fiscal</label>
              <input type="text" class="form-control" id="cliente_regimen_fiscal">
            </div>
            <div class="col-md-4">
              <label class="form-label">Tipo de Cliente <span class="text-danger">*</span></label>
              <select class="form-select" id="cliente_tipo_cliente" required>
                <option value="Regular">Regular</option>
                <option value="Mostrador">Mostrador</option>
                <option value="Gobierno">Gobierno</option>
                <option value="Licitación">Licitación</option>
                <option value="Distribuidor">Distribuidor</option>
              </select>
            </div>
          </div>
          
          <hr>
          
          <!-- Contacto -->
          <h6><i class="fas fa-address-book"></i> Datos de Contacto</h6>
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Nombre de Contacto</label>
              <input type="text" class="form-control" id="cliente_contacto_nombre">
            </div>
            <div class="col-md-4">
              <label class="form-label">Teléfono</label>
              <input type="text" class="form-control" id="cliente_telefono">
            </div>
            <div class="col-md-4">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" id="cliente_email">
            </div>
          </div>
          
          <hr>
          
          <!-- Dirección -->
          <h6><i class="fas fa-map-marker-alt"></i> Dirección</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Calle</label>
              <input type="text" class="form-control" id="cliente_calle">
            </div>
            <div class="col-md-3">
              <label class="form-label">Número Exterior</label>
              <input type="text" class="form-control" id="cliente_numero_exterior">
            </div>
            <div class="col-md-3">
              <label class="form-label">Número Interior</label>
              <input type="text" class="form-control" id="cliente_numero_interior">
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Colonia</label>
              <input type="text" class="form-control" id="cliente_colonia">
            </div>
            <div class="col-md-4">
              <label class="form-label">Ciudad</label>
              <input type="text" class="form-control" id="cliente_ciudad">
            </div>
            <div class="col-md-2">
              <label class="form-label">Estado</label>
              <input type="text" class="form-control" id="cliente_estado">
            </div>
            <div class="col-md-2">
              <label class="form-label">C.P.</label>
              <input type="text" class="form-control" id="cliente_codigo_postal" maxlength="5">
            </div>
          </div>
          
          <hr>
          
          <!-- Datos Financieros -->
          <h6><i class="fas fa-dollar-sign"></i> Datos Financieros</h6>
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Límite de Crédito</label>
              <input type="number" step="0.01" class="form-control" id="cliente_limite_credito" value="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Días de Crédito</label>
              <input type="number" class="form-control" id="cliente_dias_credito" value="0">
            </div>
            <div class="col-md-4">
              <label class="form-label">Estatus <span class="text-danger">*</span></label>
              <select class="form-select" id="cliente_estatus" required>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
                <option value="Suspendido">Suspendido</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarCliente()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Offcanvas: Detalle del Cliente -->
<div class="offcanvas offcanvas-end" style="width:560px;" tabindex="-1" id="offcanvasDetalleCliente">
  <div class="offcanvas-header bg-primary text-white">
    <h5 class="mb-0 text-white"><i class="fas fa-user-tie text-white"></i> <span id="cli-razon-social" class="text-white">Cliente</span></h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <div class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
      <span class="text-muted small">Código: <strong id="cli-codigo">—</strong></span>
      <span id="cli-estatus-badge"></span>
    </div>
    <div class="px-3 py-2 border-bottom d-flex gap-2">
      <button class="btn btn-sm btn-primary" id="cli-btn-editar"><i class="fas fa-edit"></i> Editar</button>
    </div>
    <ul class="nav nav-tabs px-3 pt-2" role="tablist">
      <li class="nav-item"><button class="nav-link active" id="cli-info-tab" data-bs-toggle="tab" data-bs-target="#cli-tab-info" type="button">Información</button></li>
      <li class="nav-item"><button class="nav-link" id="cli-ventas-tab" data-bs-toggle="tab" data-bs-target="#cli-tab-ventas" type="button">Ventas</button></li>
      <li class="nav-item"><button class="nav-link" id="cli-cotizaciones-tab" data-bs-toggle="tab" data-bs-target="#cli-tab-cotizaciones" type="button">Cotizaciones</button></li>
    </ul>
    <div class="tab-content px-3 py-3">
      <div class="tab-pane fade show active" id="cli-tab-info">
        <table class="table table-sm"><tbody id="cli-detalles"></tbody></table>
      </div>
      <div class="tab-pane fade" id="cli-tab-ventas">
        <div id="cli-ventas-loading" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i></div>
        <div id="cli-ventas-container" style="display:none;">
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead class="table-light"><tr><th>Folio</th><th>Fecha</th><th class="text-end">Total</th><th>Estatus</th><th width="90">Acciones</th></tr></thead>
              <tbody id="cli-ventas-tbody"></tbody>
            </table>
          </div>
          <div id="cli-ventas-paginacion" class="d-grid gap-2 mt-2"></div>
        </div>
      </div>
      <div class="tab-pane fade" id="cli-tab-cotizaciones">
        <div id="cli-cotizaciones-loading" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i></div>
        <div id="cli-cotizaciones-container" style="display:none;">
          <div class="table-responsive">
            <table class="table table-sm table-hover">
              <thead class="table-light"><tr><th>Folio</th><th>Fecha</th><th class="text-end">Total</th><th>Estatus</th><th width="130">Acciones</th></tr></thead>
              <tbody id="cli-cotizaciones-tbody"></tbody>
            </table>
          </div>
          <div id="cli-cotizaciones-paginacion" class="d-grid gap-2 mt-2"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Detalle de Orden de Venta -->
<div class="modal fade" id="modalDetalleOrdenVenta" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de Orden <span id="det-ov-folio"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="det-ov-loading" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i></div>
        <div id="det-ov-error" class="alert alert-danger" style="display:none;"></div>
        <div id="det-ov-content" style="display:none;">
          <div class="row mb-3">
            <div class="col-md-6">
              <p class="mb-1"><strong>Cliente:</strong> <span id="det-ov-cliente"></span></p>
              <p class="mb-1"><strong>Fecha:</strong> <span id="det-ov-fecha"></span></p>
              <p class="mb-1"><strong>Estatus:</strong> <span id="det-ov-estatus"></span></p>
              <p class="mb-1"><strong>Tipo:</strong> <span id="det-ov-tipo"></span></p>
            </div>
            <div class="col-md-6 text-md-end">
              <p class="mb-1"><strong>Total:</strong> <span id="det-ov-total"></span></p>
              <p class="mb-1"><strong>Pago:</strong> <span id="det-ov-pago"></span></p>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead class="table-light">
                <tr><th>Producto</th><th>Código</th><th class="text-center">Cantidad</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr>
              </thead>
              <tbody id="det-ov-detalles"></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <a href="#" id="det-ov-btn-recibo" target="_blank" class="btn btn-primary"><i class="fas fa-print"></i> Imprimir Recibo</a>
      </div>
    </div>
  </div>
</div>

<script>
let tabla;
let busquedaCliTimer = null;
let cliOrdenesActualId = 0;
let cliVentasLimit = 10;
let cliVentasOffset = 0;
let cliVentasTotal = 0;
let cliCotizacionesLimit = 10;
let cliCotizacionesOffset = 0;
let cliCotizacionesTotal = 0;

const badgeOrdenVenta = {
  'Cotización': 'warning',
  'Confirmada': 'info',
  'En Preparación': 'primary',
  'Entregada': 'success',
  'Cancelada': 'danger',
  'Pendiente': 'warning',
  'Parcial': 'info',
  'Pagado': 'success'
};

function initClientes() {
  inicializarDataTable();
  inicializarFiltros();

  $('#busquedaClientes').on('keyup', function() {
    clearTimeout(busquedaCliTimer);
    busquedaCliTimer = setTimeout(function() {
      tabla.search($('#busquedaClientes').val()).draw();
    }, 350);
  });
  $('#btnLimpiarBusquedaCli').on('click', function() {
    $('#busquedaClientes').val('');
    tabla.search('').draw();
  });
}

function inicializarDataTable() {
  tabla = $('#tablaClientes').DataTable({
    processing: true,
    serverSide: true,
    dom: 'lrtip',
    ajax: {
      url: '<?=base_url();?>ventas/Clientes/lista_ajax',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d.filtro_tipo_cliente = $('#filtro_tipo_cliente').val();
        d.filtro_estatus = $('#filtro_estatus').val();
        d.filtro_saldo = $('#filtro_saldo').val();
        d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
      }
    },
    columns: [
      { data: 0 },
      { data: 1 },
      { data: 2 },
      { data: 3 },
      { data: 4 },
      { data: 5 },
      { data: 6 },
      { data: 7 },
      { data: 8, orderable: false }
    ],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    },
    order: [[0, 'desc']]
  });
}

function inicializarFiltros() {
  $('#filtro_tipo_cliente, #filtro_estatus, #filtro_saldo').on('change', function() {
    tabla.ajax.reload();
  });
}

function limpiarFiltros() {
  $('#filtro_tipo_cliente').val('');
  $('#filtro_estatus').val('');
  $('#filtro_saldo').val('');
  $('#busquedaClientes').val('');
  tabla.search('').ajax.reload();
}

function mostrarModalNuevo() {
  $('#modalClienteTitle').text('Nuevo Cliente');
  $('#formCliente')[0].reset();
  $('#cliente_id').val('');
  $('#cliente_estatus').val('Activo');
  $('#cliente_tipo_cliente').val('Regular');
  $('#modalCliente').modal('show');
}

window.editarCliente = function(id) {
  $.post('<?=base_url();?>ventas/Clientes/get_cliente_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const c = result.cliente;
      $('#modalClienteTitle').text('Editar Cliente');
      $('#cliente_id').val(c.id);
      $('#cliente_razon_social').val(c.razon_social);
      $('#cliente_nombre_comercial').val(c.nombre_comercial);
      $('#cliente_rfc').val(c.rfc);
      $('#cliente_regimen_fiscal').val(c.regimen_fiscal);
      $('#cliente_contacto_nombre').val(c.contacto_nombre);
      $('#cliente_telefono').val(c.telefono);
      $('#cliente_email').val(c.email);
      $('#cliente_calle').val(c.calle);
      $('#cliente_numero_exterior').val(c.numero_exterior);
      $('#cliente_numero_interior').val(c.numero_interior);
      $('#cliente_colonia').val(c.colonia);
      $('#cliente_ciudad').val(c.ciudad);
      $('#cliente_estado').val(c.estado);
      $('#cliente_codigo_postal').val(c.codigo_postal);
      $('#cliente_limite_credito').val(c.limite_credito);
      $('#cliente_dias_credito').val(c.dias_credito);
      $('#cliente_tipo_cliente').val(c.tipo_cliente);
      $('#cliente_estatus').val(c.estatus);
      $('#modalCliente').modal('show');
    }
  });
};

function guardarCliente() {
  const id = $('#cliente_id').val();
  const url = id ? '<?=base_url();?>ventas/Clientes/editar_ajax' : '<?=base_url();?>ventas/Clientes/crear_ajax';
  
  const data = {
    'id': id,
    'razon_social': $('#cliente_razon_social').val(),
    'nombre_comercial': $('#cliente_nombre_comercial').val(),
    'rfc': $('#cliente_rfc').val(),
    'regimen_fiscal': $('#cliente_regimen_fiscal').val(),
    'contacto_nombre': $('#cliente_contacto_nombre').val(),
    'telefono': $('#cliente_telefono').val(),
    'email': $('#cliente_email').val(),
    'calle': $('#cliente_calle').val(),
    'numero_exterior': $('#cliente_numero_exterior').val(),
    'numero_interior': $('#cliente_numero_interior').val(),
    'colonia': $('#cliente_colonia').val(),
    'ciudad': $('#cliente_ciudad').val(),
    'estado': $('#cliente_estado').val(),
    'codigo_postal': $('#cliente_codigo_postal').val(),
    'limite_credito': $('#cliente_limite_credito').val(),
    'dias_credito': $('#cliente_dias_credito').val(),
    'tipo_cliente': $('#cliente_tipo_cliente').val(),
    'estatus': $('#cliente_estatus').val(),
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  };
  
  $.post(url, data, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if(result.success) {
      $('#modalCliente').modal('hide');
      tabla.ajax.reload();
    }
  });
}

window.verCliente = function(id) {
  cliOrdenesActualId = id;
  cliVentasOffset = 0;
  cliCotizacionesOffset = 0;

  var oc = new bootstrap.Offcanvas(document.getElementById('offcanvasDetalleCliente'));
  oc.show();

  $('#cli-detalles').html('<tr><td colspan="2" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i></td></tr>');
  $('#cli-ventas-container, #cli-cotizaciones-container').hide();
  $('#cli-ventas-loading, #cli-cotizaciones-loading').show();
  $('#cli-ventas-tbody, #cli-cotizaciones-tbody').html('');
  $('#cli-ventas-paginacion, #cli-cotizaciones-paginacion').html('');

  $('#cli-btn-editar').off('click').on('click', function() {
    bootstrap.Offcanvas.getInstance(document.getElementById('offcanvasDetalleCliente')).hide();
    editarCliente(id);
  });

  $('#cli-info-tab').tab('show');

  $('#cli-ventas-tab').off('shown.bs.tab').on('shown.bs.tab', function() {
    if($('#cli-ventas-tbody').is(':empty')) cargarHistorialVentas(id);
  });
  $('#cli-cotizaciones-tab').off('shown.bs.tab').on('shown.bs.tab', function() {
    if($('#cli-cotizaciones-tbody').is(':empty')) cargarCotizaciones(id);
  });

  $.post('<?=base_url();?>ventas/Clientes/get_cliente_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(!result.success) return;
    const c = result.cliente;

    $('#cli-razon-social').text(c.razon_social);
    $('#cli-codigo').text(c.codigo || '—');
    var badgeMap = {Activo: 'success', Inactivo: 'secondary', Suspendido: 'danger'};
    $('#cli-estatus-badge').html('<span class="badge bg-' + (badgeMap[c.estatus] || 'secondary') + '">' + c.estatus + '</span>');

    function fila(label, valor) {
      return '<tr><th class="text-muted fw-normal" style="width:40%">' + label + '</th><td>' + (valor || '<span class="text-muted">—</span>') + '</td></tr>';
    }

    let html = '';
    html += fila('Nombre Comercial', c.nombre_comercial);
    html += fila('RFC', c.rfc);
    html += fila('Régimen Fiscal', c.regimen_fiscal);
    html += fila('Tipo', '<span class="badge bg-primary">' + c.tipo_cliente + '</span>');
    html += fila('Contacto', c.contacto_nombre);
    html += fila('Teléfono', c.telefono ? '<a href="tel:' + c.telefono + '">' + c.telefono + '</a>' : null);
    html += fila('Email', c.email ? '<a href="mailto:' + c.email + '">' + c.email + '</a>' : null);
    html += fila('Dirección', [c.calle, c.numero_exterior, c.numero_interior, c.colonia, c.ciudad, c.estado, c.codigo_postal].filter(Boolean).join(', '));
    html += fila('Límite Crédito', '$' + parseFloat(c.limite_credito || 0).toLocaleString('es-MX', {minimumFractionDigits:2}));
    html += fila('Días Crédito', c.dias_credito ? c.dias_credito + ' días' : null);
    html += fila('Saldo Pendiente', '<span class="' + (parseFloat(c.saldo_pendiente) > 0 ? 'text-danger fw-semibold' : '') + '">$' + parseFloat(c.saldo_pendiente || 0).toLocaleString('es-MX', {minimumFractionDigits:2}) + '</span>');

    $('#cli-detalles').html(html);
  });
};

function formatearFechaMX(fecha) {
  if(!fecha) return '—';
  var d = new Date(fecha);
  if(isNaN(d.getTime())) return '—';
  return d.toLocaleDateString('es-MX');
}

function renderBadge(estatus) {
  return '<span class="badge bg-' + (badgeOrdenVenta[estatus] || 'secondary') + '">' + estatus + '</span>';
}

function botonCargarMas(containerId, offset, total, limit, callback) {
  if(offset + limit >= total) return '';
  return '<button class="btn btn-outline-secondary btn-sm" onclick="' + callback + '"><i class="fas fa-chevron-down"></i> Cargar más (' + (total - offset - limit) + ' restantes)</button>';
}

function renderAccionesOrdenVenta(ov, esCotizacion) {
  var html = '<div class="btn-group btn-group-sm">';
  html += '<button type="button" class="btn btn-info" onclick="verDetalleOrdenVenta(' + ov.id + ')" title="Ver detalle"><i class="fas fa-eye"></i></button>';
  html += '<a href="<?=base_url();?>ventas/Pos/imprimir_recibo_template/' + ov.id + '/1" target="_blank" class="btn btn-primary" title="Imprimir recibo"><i class="fas fa-print"></i></a>';
  if(esCotizacion) {
    html += '<button type="button" class="btn btn-success" onclick="convertirCotizacion(' + ov.id + ')" title="Convertir a venta"><i class="fas fa-check"></i></button>';
  }
  html += '</div>';
  return html;
}

function cargarHistorialVentas(clienteId, append) {
  if(!append) {
    cliVentasOffset = 0;
    $('#cli-ventas-tbody').html('');
  }
  $('#cli-ventas-loading').show();
  $('#cli-ventas-paginacion').html('');

  $.post('<?=base_url();?>ventas/Clientes/get_historial_ventas_ajax', {
    cliente_id: clienteId,
    limit: cliVentasLimit,
    offset: cliVentasOffset,
    peticion: 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(res) {
    res = JSON.parse(res);
    $('#cli-ventas-loading').hide();
    cliVentasTotal = res.total || 0;

    var tbody = '';
    if(res.success && res.ordenes && res.ordenes.length) {
      res.ordenes.forEach(function(ov) {
        tbody += '<tr>';
        tbody += '<td><strong>' + ov.folio + '</strong></td>';
        tbody += '<td>' + formatearFechaMX(ov.fecha_orden) + '</td>';
        tbody += '<td class="text-end">$' + parseFloat(ov.total || 0).toLocaleString('es-MX', {minimumFractionDigits:2}) + '</td>';
        tbody += '<td>' + renderBadge(ov.estatus) + (ov.estatus_pago ? ' ' + renderBadge(ov.estatus_pago) : '') + '</td>';
        tbody += '<td>' + renderAccionesOrdenVenta(ov, false) + '</td>';
        tbody += '</tr>';
      });
      if(append) {
        $('#cli-ventas-tbody').append(tbody);
      } else {
        $('#cli-ventas-tbody').html(tbody);
      }
      cliVentasOffset = res.offset + res.ordenes.length;
      $('#cli-ventas-paginacion').html(botonCargarMas('cli-ventas-paginacion', cliVentasOffset, cliVentasTotal, cliVentasLimit, 'cargarMasVentas()'));
      $('#cli-ventas-container').show();
    } else if(!append) {
      $('#cli-ventas-tbody').html('<tr><td colspan="5" class="text-center text-muted py-3">Sin órdenes de venta</td></tr>');
      $('#cli-ventas-container').show();
    }
  });
}

window.cargarMasVentas = function() {
  cargarHistorialVentas(cliOrdenesActualId, true);
};

function cargarCotizaciones(clienteId, append) {
  if(!append) {
    cliCotizacionesOffset = 0;
    $('#cli-cotizaciones-tbody').html('');
  }
  $('#cli-cotizaciones-loading').show();
  $('#cli-cotizaciones-paginacion').html('');

  $.post('<?=base_url();?>ventas/Clientes/get_historial_cotizaciones_ajax', {
    cliente_id: clienteId,
    limit: cliCotizacionesLimit,
    offset: cliCotizacionesOffset,
    peticion: 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(res) {
    res = JSON.parse(res);
    $('#cli-cotizaciones-loading').hide();
    cliCotizacionesTotal = res.total || 0;

    var tbody = '';
    if(res.success && res.cotizaciones && res.cotizaciones.length) {
      res.cotizaciones.forEach(function(ov) {
        tbody += '<tr>';
        tbody += '<td><strong>' + ov.folio + '</strong></td>';
        tbody += '<td>' + formatearFechaMX(ov.fecha_orden) + '</td>';
        tbody += '<td class="text-end">$' + parseFloat(ov.total || 0).toLocaleString('es-MX', {minimumFractionDigits:2}) + '</td>';
        tbody += '<td>' + renderBadge(ov.estatus) + '</td>';
        tbody += '<td>' + renderAccionesOrdenVenta(ov, true) + '</td>';
        tbody += '</tr>';
      });
      if(append) {
        $('#cli-cotizaciones-tbody').append(tbody);
      } else {
        $('#cli-cotizaciones-tbody').html(tbody);
      }
      cliCotizacionesOffset = res.offset + res.cotizaciones.length;
      $('#cli-cotizaciones-paginacion').html(botonCargarMas('cli-cotizaciones-paginacion', cliCotizacionesOffset, cliCotizacionesTotal, cliCotizacionesLimit, 'cargarMasCotizaciones()'));
      $('#cli-cotizaciones-container').show();
    } else if(!append) {
      $('#cli-cotizaciones-tbody').html('<tr><td colspan="5" class="text-center text-muted py-3">Sin cotizaciones registradas</td></tr>');
      $('#cli-cotizaciones-container').show();
    }
  });
}

window.cargarMasCotizaciones = function() {
  cargarCotizaciones(cliOrdenesActualId, true);
};

window.convertirCotizacion = function(ordenId) {
  if(!confirm('¿Convertir esta cotización en orden de venta?')) return;
  $.post('<?=base_url();?>ventas/Clientes/convertir_cotizacion_ajax', {
    orden_id: ordenId,
    peticion: 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(res) {
    res = JSON.parse(res);
    notifyShow(res.message, res.success ? 'success' : 'danger');
    if(res.success) {
      cliCotizacionesOffset = 0;
      cargarCotizaciones(cliOrdenesActualId);
      // Refrescar ventas si ya se cargó para reflejar la nueva orden
      if(!$('#cli-ventas-tbody').is(':empty')) {
        cliVentasOffset = 0;
        cargarHistorialVentas(cliOrdenesActualId);
      }
    }
  });
};

window.verDetalleOrdenVenta = function(ordenId) {
  $('#modalDetalleOrdenVenta').modal('show');
  $('#det-ov-loading').show();
  $('#det-ov-content').hide();
  $('#det-ov-error').hide();
  $('#det-ov-detalles').html('');
  $('#det-ov-btn-recibo').attr('href', '<?=base_url();?>ventas/Pos/imprimir_recibo_template/' + ordenId + '/1');

  $.post('<?=base_url();?>ventas/Pos/get_orden_ajax', {
    id: ordenId,
    peticion: 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(res) {
    res = JSON.parse(res);
    $('#det-ov-loading').hide();
    if(!res.success || !res.orden) {
      $('#det-ov-error').text('No se pudo cargar el detalle de la orden.').show();
      return;
    }
    var ov = res.orden;
    $('#det-ov-folio').text(ov.folio || '—');
    $('#det-ov-cliente').text(ov.razon_social || '—');
    $('#det-ov-fecha').text(formatearFechaMX(ov.fecha_orden));
    $('#det-ov-estatus').html(renderBadge(ov.estatus));
    $('#det-ov-tipo').text(ov.tipo_venta || '—');
    $('#det-ov-total').html('<strong>$' + parseFloat(ov.total || 0).toLocaleString('es-MX', {minimumFractionDigits:2}) + '</strong>');
    $('#det-ov-pago').html(renderBadge(ov.estatus_pago || 'Pendiente'));

    var detHtml = '';
    if(ov.detalles && ov.detalles.length) {
      ov.detalles.forEach(function(d) {
        var subtotal = (parseFloat(d.cantidad || 0) * parseFloat(d.precio_unitario || 0));
        detHtml += '<tr>';
        detHtml += '<td>' + (d.nombre || '—') + '</td>';
        detHtml += '<td><small class="text-muted">' + (d.codigo || '—') + '</small></td>';
        detHtml += '<td class="text-center">' + parseFloat(d.cantidad || 0).toLocaleString('es-MX') + '</td>';
        detHtml += '<td class="text-end">$' + parseFloat(d.precio_unitario || 0).toLocaleString('es-MX', {minimumFractionDigits:2}) + '</td>';
        detHtml += '<td class="text-end">$' + subtotal.toLocaleString('es-MX', {minimumFractionDigits:2}) + '</td>';
        detHtml += '</tr>';
      });
    } else {
      detHtml = '<tr><td colspan="5" class="text-center text-muted">Sin productos registrados</td></tr>';
    }
    $('#det-ov-detalles').html(detHtml);
    $('#det-ov-content').show();
  });
};

window.imprimirReciboOrdenVenta = function(ordenId) {
  window.open('<?=base_url();?>ventas/Pos/imprimir_recibo_template/' + ordenId + '/1', '_blank');
};

window.eliminarCliente = function(id) {
  if(!confirm('¿Está seguro de eliminar este cliente?')) return;
  
  $.post('<?=base_url();?>ventas/Clientes/eliminar_ajax', {
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

// Inicializar cuando jQuery esté disponible
if (typeof jQuery !== 'undefined') {
  $(document).ready(initClientes);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initClientes);
    }
  });
}
</script>
