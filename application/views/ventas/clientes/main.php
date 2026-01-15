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
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-list"></i> Lista de Clientes</h3>
    <div class="card-tools">
      <button type="button" class="btn btn-primary" onclick="mostrarModalNuevo()">
        <i class="fas fa-plus"></i> Nuevo Cliente
      </button>
    </div>
  </div>
  <div class="card-body">
    <!-- Filtros -->
    <div class="row mb-3">
      <div class="col-md-3">
        <label class="form-label">Tipo de Cliente</label>
        <select class="form-select" id="filtro_tipo_cliente">
          <option value="">Todos</option>
          <option value="Regular">Regular</option>
          <option value="Mostrador">Mostrador</option>
          <option value="Gobierno">Gobierno</option>
          <option value="Licitación">Licitación</option>
          <option value="Distribuidor">Distribuidor</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Estatus</label>
        <select class="form-select" id="filtro_estatus">
          <option value="">Todos</option>
          <option value="Activo">Activo</option>
          <option value="Inactivo">Inactivo</option>
          <option value="Suspendido">Suspendido</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Con Saldo Pendiente</label>
        <select class="form-select" id="filtro_saldo">
          <option value="">Todos</option>
          <option value="con_saldo">Con Saldo</option>
          <option value="sin_saldo">Sin Saldo</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">&nbsp;</label>
        <button type="button" class="btn btn-secondary w-100" onclick="limpiarFiltros()">
          <i class="fas fa-eraser"></i> Limpiar Filtros
        </button>
      </div>
    </div>
    
    <table id="tablaClientes" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Código</th>
                <th>Razón Social</th>
                <th>RFC</th>
                <th>Contacto</th>
                <th>Tipo</th>
                <th>Estatus</th>
                <th>Acciones</th>
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

<script>
let tabla;

function initClientes() {
  inicializarDataTable();
  inicializarFiltros();
}

function inicializarDataTable() {
  tabla = $('#tablaClientes').DataTable({
    processing: true,
    serverSide: true,
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
      { data: 0 },  // Código
      { data: 1 },  // Razón Social
      { data: 2 },  // RFC
      { data: 3 },  // Contacto
      { data: 4 },  // Tipo
      { data: 5 },  // Estatus
      { data: 6, orderable: false }  // Acciones
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
  tabla.ajax.reload();
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
  $.post('<?=base_url();?>ventas/Clientes/get_cliente_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const c = result.cliente;
      let html = `
        <div class="row">
          <div class="col-md-6">
            <h6><i class="fas fa-file-invoice"></i> Datos Fiscales</h6>
            <table class="table table-sm">
              <tr><th width="40%">Código:</th><td>${c.codigo}</td></tr>
              <tr><th>Razón Social:</th><td>${c.razon_social}</td></tr>
              ${c.nombre_comercial ? `<tr><th>Nombre Comercial:</th><td>${c.nombre_comercial}</td></tr>` : ''}
              <tr><th>RFC:</th><td>${c.rfc}</td></tr>
              ${c.regimen_fiscal ? `<tr><th>Régimen Fiscal:</th><td>${c.regimen_fiscal}</td></tr>` : ''}
              <tr><th>Tipo:</th><td><span class="badge bg-primary">${c.tipo_cliente}</span></td></tr>
            </table>
            
            <h6><i class="fas fa-address-book"></i> Contacto</h6>
            <table class="table table-sm">
              ${c.contacto_nombre ? `<tr><th width="40%">Contacto:</th><td>${c.contacto_nombre}</td></tr>` : ''}
              ${c.telefono ? `<tr><th>Teléfono:</th><td>${c.telefono}</td></tr>` : ''}
              ${c.email ? `<tr><th>Email:</th><td>${c.email}</td></tr>` : ''}
            </table>
          </div>
          
          <div class="col-md-6">
            <h6><i class="fas fa-map-marker-alt"></i> Dirección</h6>
            <p>${c.calle || ''} ${c.numero_exterior || ''} ${c.numero_interior || ''}<br>
            ${c.colonia || ''}<br>
            ${c.ciudad || ''}, ${c.estado || ''} ${c.codigo_postal || ''}</p>
            
            <h6><i class="fas fa-dollar-sign"></i> Datos Financieros</h6>
            <table class="table table-sm">
              <tr><th width="40%">Límite de Crédito:</th><td>$${parseFloat(c.limite_credito).toFixed(2)}</td></tr>
              <tr><th>Días de Crédito:</th><td>${c.dias_credito} días</td></tr>
              <tr><th>Saldo Pendiente:</th><td class="${c.saldo_pendiente > 0 ? 'text-danger' : ''}">$${parseFloat(c.saldo_pendiente).toFixed(2)}</td></tr>
              <tr><th>Estatus:</th><td><span class="badge bg-${c.estatus == 'Activo' ? 'success' : 'secondary'}">${c.estatus}</span></td></tr>
            </table>
          </div>
        </div>
      `;
      
      if($('#modalVerCliente').length === 0) {
        $('body').append(`
          <div class="modal fade" id="modalVerCliente" tabindex="-1">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Detalles del Cliente</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalleClienteBody"></div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" onclick="$('#modalVerCliente').modal('hide'); editarCliente(${c.id});">Editar</button>
                </div>
              </div>
            </div>
          </div>
        `);
      }
      
      $('#detalleClienteBody').html(html);
      $('#modalVerCliente').modal('show');
    }
  });
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
