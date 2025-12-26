<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container-fluid p-0">
  
  <!-- Header -->
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-book"></i> <?= $headTitle ?></h3>
    </div>
    
    <div class="col-auto ms-auto text-end mt-n1">
      <button type="button" class="btn btn-primary" onclick="mostrarModalNuevo()">
        <i class="fas fa-plus"></i> Nueva Cuenta
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <label>Tipo de Cuenta</label>
              <select id="filtro_tipo" class="form-select">
                <option value="">Todos</option>
                <option value="Activo">Activo</option>
                <option value="Pasivo">Pasivo</option>
                <option value="Capital">Capital</option>
                <option value="Ingresos">Ingresos</option>
                <option value="Egresos">Egresos</option>
                <option value="Costos">Costos</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>Estatus</label>
              <select id="filtro_estatus" class="form-select">
                <option value="">Todos</option>
                <option value="Activa" selected>Activa</option>
                <option value="Inactiva">Inactiva</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>&nbsp;</label><br>
              <button type="button" class="btn btn-info" onclick="aplicarFiltros()">
                <i class="fas fa-filter"></i> Filtrar
              </button>
              <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">
                <i class="fas fa-times"></i> Limpiar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- DataTable -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Catálogo de Cuentas Contables</h5>
        </div>
        <div class="card-body">
          <table id="tablaCuentas" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Naturaleza</th>
                <th>Afectable</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Naturaleza</th>
                <th>Afectable</th>
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

<!-- Modal Nueva/Editar Cuenta -->
<div class="modal fade" id="modalCuenta" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalCuentaTitle">Nueva Cuenta Contable</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formCuenta">
          <input type="hidden" id="cuenta_id" name="id">
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="cuenta_codigo" class="form-label">Código <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cuenta_codigo" name="codigo" required>
              <small class="text-muted">Ej: 1.1.01.001</small>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="cuenta_nivel" class="form-label">Nivel <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="cuenta_nivel" name="nivel" min="1" max="5" required>
              <small class="text-muted">1=Mayor, 2=Submayor, etc.</small>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="cuenta_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="cuenta_nombre" name="nombre" required>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="cuenta_tipo" class="form-label">Tipo de Cuenta <span class="text-danger">*</span></label>
              <select class="form-select" id="cuenta_tipo" name="tipo_cuenta" required>
                <option value="">Seleccionar...</option>
                <option value="Activo">Activo</option>
                <option value="Pasivo">Pasivo</option>
                <option value="Capital">Capital</option>
                <option value="Ingresos">Ingresos</option>
                <option value="Egresos">Egresos</option>
                <option value="Costos">Costos</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="cuenta_naturaleza" class="form-label">Naturaleza <span class="text-danger">*</span></label>
              <select class="form-select" id="cuenta_naturaleza" name="naturaleza" required>
                <option value="">Seleccionar...</option>
                <option value="Deudora">Deudora</option>
                <option value="Acreedora">Acreedora</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="cuenta_subtipo" class="form-label">Subtipo</label>
              <input type="text" class="form-control" id="cuenta_subtipo" name="subtipo">
              <small class="text-muted">Circulante, Fijo, etc.</small>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="cuenta_padre" class="form-label">Cuenta Padre</label>
              <select class="form-select" id="cuenta_padre" name="cuenta_padre_id">
                <option value="">Sin cuenta padre</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="cuenta_afectable" name="es_afectable" checked>
                <label class="form-check-label" for="cuenta_afectable">
                  Es Afectable (puede recibir movimientos)
                </label>
              </div>
            </div>
            
            <div class="col-md-6 mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="cuenta_requiere_auxiliar" name="requiere_auxiliar">
                <label class="form-check-label" for="cuenta_requiere_auxiliar">
                  Requiere Auxiliar
                </label>
              </div>
            </div>
          </div>
          
          <div class="row" id="divTipoAuxiliar" style="display:none;">
            <div class="col-md-6 mb-3">
              <label for="cuenta_tipo_auxiliar" class="form-label">Tipo de Auxiliar</label>
              <select class="form-select" id="cuenta_tipo_auxiliar" name="tipo_auxiliar">
                <option value="">Seleccionar...</option>
                <option value="cliente">Cliente</option>
                <option value="proveedor">Proveedor</option>
                <option value="empleado">Empleado</option>
                <option value="otro">Otro</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="cuenta_saldo_inicial" class="form-label">Saldo Inicial</label>
              <input type="number" step="0.01" class="form-control" id="cuenta_saldo_inicial" name="saldo_inicial" value="0">
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="cuenta_estatus" class="form-label">Estatus</label>
              <select class="form-select" id="cuenta_estatus" name="estatus">
                <option value="Activa">Activa</option>
                <option value="Inactiva">Inactiva</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarCuenta()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ver Detalle -->
<div class="modal fade" id="modalDetalleCuenta" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de Cuenta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detalleCuentaBody">
        <!-- Contenido dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
var tablaCuentas;

// Esperar a que jQuery esté disponible
if (typeof jQuery !== 'undefined') {
  $(document).ready(initCuentas);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initCuentas);
    }
  });
}

function initCuentas() {
  // Inicializar DataTable
  tablaCuentas = $('#tablaCuentas').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: '<?=base_url()?>contabilidad/CuentasContables/lista_ajax',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
        d.filtro_tipo = $('#filtro_tipo').val();
        d.filtro_estatus = $('#filtro_estatus').val();
      }
    },
    columns: [
      { data: 0 },
      { data: 1 },
      { data: 2 },
      { data: 3 },
      { data: 4 },
      { data: 5 },
      { data: 6, orderable: false }
    ],
    order: [[0, 'asc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    },
    pageLength: 25
  });
  
  // Cargar cuentas padre
  cargarCuentasPadre();
  
  // Toggle tipo auxiliar
  $('#cuenta_requiere_auxiliar').change(function() {
    if($(this).is(':checked')) {
      $('#divTipoAuxiliar').show();
    } else {
      $('#divTipoAuxiliar').hide();
      $('#cuenta_tipo_auxiliar').val('');
    }
  });
}

function aplicarFiltros() {
  tablaCuentas.ajax.reload();
}

function limpiarFiltros() {
  $('#filtro_tipo').val('');
  $('#filtro_estatus').val('');
  tablaCuentas.ajax.reload();
}

function mostrarModalNuevo() {
  $('#modalCuentaTitle').text('Nueva Cuenta Contable');
  $('#formCuenta')[0].reset();
  $('#cuenta_id').val('');
  $('#cuenta_estatus').val('Activa');
  $('#divTipoAuxiliar').hide();
  $('#modalCuenta').modal('show');
}

function cargarCuentasPadre() {
  $.post('<?=base_url()?>contabilidad/CuentasContables/get_cuentas_padre_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      let html = '<option value="">Sin cuenta padre</option>';
      result.cuentas.forEach(c => {
        let indent = '&nbsp;'.repeat((c.nivel - 1) * 4);
        html += `<option value="${c.id}">${indent}${c.codigo} - ${c.nombre}</option>`;
      });
      $('#cuenta_padre').html(html);
    }
  });
}

function guardarCuenta() {
  let formData = $('#formCuenta').serialize();
  let url = $('#cuenta_id').val() ? 
    '<?=base_url()?>contabilidad/CuentasContables/editar_ajax' : 
    '<?=base_url()?>contabilidad/CuentasContables/crear_ajax';
  
  formData += '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>';
  
  $.post(url, formData, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#modalCuenta').modal('hide');
      tablaCuentas.ajax.reload();
      alert(result.message);
    } else {
      alert('Error: ' + result.message);
    }
  });
}

function verCuenta(id) {
  $.post('<?=base_url()?>contabilidad/CuentasContables/get_cuenta_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      let c = result.cuenta;
      let html = `
        <table class="table table-sm">
          <tr><th>Código:</th><td>${c.codigo}</td></tr>
          <tr><th>Nombre:</th><td>${c.nombre}</td></tr>
          <tr><th>Tipo:</th><td>${c.tipo_cuenta}</td></tr>
          <tr><th>Subtipo:</th><td>${c.subtipo || 'N/A'}</td></tr>
          <tr><th>Naturaleza:</th><td>${c.naturaleza}</td></tr>
          <tr><th>Nivel:</th><td>${c.nivel}</td></tr>
          <tr><th>Cuenta Padre:</th><td>${c.cuenta_padre_nombre || 'N/A'}</td></tr>
          <tr><th>Afectable:</th><td>${c.es_afectable ? 'Sí' : 'No'}</td></tr>
          <tr><th>Saldo Inicial:</th><td>$${parseFloat(c.saldo_inicial).toFixed(2)}</td></tr>
          <tr><th>Estatus:</th><td>${c.estatus}</td></tr>
        </table>
      `;
      $('#detalleCuentaBody').html(html);
      $('#modalDetalleCuenta').modal('show');
    }
  });
}

function editarCuenta(id) {
  $.post('<?=base_url()?>contabilidad/CuentasContables/get_cuenta_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      let c = result.cuenta;
      $('#modalCuentaTitle').text('Editar Cuenta: ' + c.codigo);
      $('#cuenta_id').val(c.id);
      $('#cuenta_codigo').val(c.codigo).prop('readonly', true);
      $('#cuenta_nombre').val(c.nombre);
      $('#cuenta_tipo').val(c.tipo_cuenta).prop('disabled', true);
      $('#cuenta_naturaleza').val(c.naturaleza).prop('disabled', true);
      $('#cuenta_nivel').val(c.nivel).prop('readonly', true);
      $('#cuenta_subtipo').val(c.subtipo);
      $('#cuenta_padre').val(c.cuenta_padre_id);
      $('#cuenta_afectable').prop('checked', c.es_afectable == 1);
      $('#cuenta_requiere_auxiliar').prop('checked', c.requiere_auxiliar == 1);
      $('#cuenta_tipo_auxiliar').val(c.tipo_auxiliar);
      $('#cuenta_saldo_inicial').val(c.saldo_inicial).prop('readonly', true);
      $('#cuenta_estatus').val(c.estatus);
      
      if(c.requiere_auxiliar == 1) {
        $('#divTipoAuxiliar').show();
      }
      
      $('#modalCuenta').modal('show');
    }
  });
}

function verMovimientos(id) {
  // TODO: Implementar vista de movimientos
  alert('Ver movimientos de cuenta ' + id);
}
</script>
