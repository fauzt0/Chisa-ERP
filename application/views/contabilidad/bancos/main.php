<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container-fluid p-0">
  
  <!-- Header -->
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-university"></i> <?= $headTitle ?></h3>
    </div>
    
    <div class="col-auto ms-auto text-end mt-n1">
      <button type="button" class="btn btn-primary" onclick="mostrarModalNuevaCuenta()">
        <i class="fas fa-plus"></i> Nueva Cuenta Bancaria
      </button>
      <button type="button" class="btn btn-success" onclick="mostrarModalNuevoMovimiento()">
        <i class="fas fa-exchange-alt"></i> Nuevo Movimiento
      </button>
    </div>
  </div>

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="bancosTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="cuentas-tab" data-bs-toggle="tab" data-bs-target="#cuentas" type="button">
        <i class="fas fa-credit-card"></i> Cuentas Bancarias
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="movimientos-tab" data-bs-toggle="tab" data-bs-target="#movimientos" type="button">
        <i class="fas fa-list"></i> Movimientos
      </button>
    </li>
  </ul>

  <div class="tab-content" id="bancosTabContent">
    
    <!-- Tab Cuentas Bancarias -->
    <div class="tab-pane fade show active" id="cuentas" role="tabpanel">
      <div class="card">
        <div class="card-body">
          <table id="tablaCuentas" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th>Banco</th>
                <th>Número de Cuenta</th>
                <th>CLABE</th>
                <th>Tipo</th>
                <th>Saldo Actual</th>
                <th>Cuenta Contable</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Banco</th>
                <th>Número de Cuenta</th>
                <th>CLABE</th>
                <th>Tipo</th>
                <th>Saldo Actual</th>
                <th>Cuenta Contable</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- Tab Movimientos -->
    <div class="tab-pane fade" id="movimientos" role="tabpanel">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-md-4">
              <label>Filtrar por Cuenta</label>
              <select id="filtro_cuenta" class="form-select" onchange="aplicarFiltrosMovimientos()">
                <option value="">Todas las cuentas</option>
              </select>
            </div>
            <div class="col-md-3">
              <label>Mostrar</label>
              <select id="filtro_conciliado" class="form-select" onchange="aplicarFiltrosMovimientos()">
                <option value="">Todos</option>
                <option value="0">Sin conciliar</option>
                <option value="1">Conciliados</option>
              </select>
            </div>
          </div>
        </div>
        <div class="card-body">
          <table id="tablaMovimientos" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Banco/Cuenta</th>
                <th>Tipo</th>
                <th>Concepto</th>
                <th>Referencia</th>
                <th>Monto</th>
                <th>Saldo</th>
                <th>Conciliado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Fecha</th>
                <th>Banco/Cuenta</th>
                <th>Tipo</th>
                <th>Concepto</th>
                <th>Referencia</th>
                <th>Monto</th>
                <th>Saldo</th>
                <th>Conciliado</th>
                <th>Acciones</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

  </div>

</div>

<!-- Modal Nueva Cuenta Bancaria -->
<div class="modal fade" id="modalCuentaBancaria" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva Cuenta Bancaria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formCuentaBancaria">
          <input type="hidden" id="cuenta_id" name="id">
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="cuenta_banco" class="form-label">Banco <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cuenta_banco" name="banco" required>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="cuenta_tipo" class="form-label">Tipo de Cuenta <span class="text-danger">*</span></label>
              <select class="form-select" id="cuenta_tipo" name="tipo_cuenta" required>
                <option value="">Seleccionar...</option>
                <option value="Cheques">Cheques</option>
                <option value="Inversión">Inversión</option>
                <option value="Nómina">Nómina</option>
                <option value="Ahorro">Ahorro</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="cuenta_numero" class="form-label">Número de Cuenta <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="cuenta_numero" name="numero_cuenta" required>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="cuenta_clabe" class="form-label">CLABE Interbancaria</label>
              <input type="text" class="form-control" id="cuenta_clabe" name="clabe" maxlength="18">
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="cuenta_moneda" class="form-label">Moneda</label>
              <select class="form-select" id="cuenta_moneda" name="moneda">
                <option value="MXN" selected>MXN - Peso Mexicano</option>
                <option value="USD">USD - Dólar</option>
                <option value="EUR">EUR - Euro</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="cuenta_saldo_inicial" class="form-label">Saldo Inicial</label>
              <input type="number" step="0.01" class="form-control" id="cuenta_saldo_inicial" name="saldo_inicial" value="0">
            </div>
          </div>
          
          <div class="mb-3">
            <label for="cuenta_contable" class="form-label">Cuenta Contable</label>
            <select class="form-select" id="cuenta_contable" name="cuenta_contable_id">
              <option value="">Seleccionar...</option>
            </select>
            <small class="text-muted">Asociar con una cuenta del catálogo contable</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarCuentaBancaria()">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Nuevo Movimiento -->
<div class="modal fade" id="modalMovimiento" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo Movimiento Bancario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formMovimiento">
          <div class="mb-3">
            <label for="mov_cuenta" class="form-label">Cuenta Bancaria <span class="text-danger">*</span></label>
            <select class="form-select" id="mov_cuenta" name="cuenta_bancaria_id" required>
              <option value="">Seleccionar...</option>
            </select>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mov_fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="mov_fecha" name="fecha" required value="<?= date('Y-m-d') ?>">
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="mov_tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
              <select class="form-select" id="mov_tipo" name="tipo_movimiento" required>
                <option value="">Seleccionar...</option>
                <option value="Depósito">Depósito</option>
                <option value="Retiro">Retiro</option>
                <option value="Transferencia">Transferencia</option>
                <option value="Comisión">Comisión</option>
                <option value="Interés">Interés</option>
              </select>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="mov_concepto" class="form-label">Concepto <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="mov_concepto" name="concepto" required>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="mov_referencia" class="form-label">Referencia</label>
              <input type="text" class="form-control" id="mov_referencia" name="referencia">
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="mov_monto" class="form-label">Monto <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control" id="mov_monto" name="monto" required>
            </div>
          </div>
          
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="mov_generar_poliza" name="generar_poliza" checked>
            <label class="form-check-label" for="mov_generar_poliza">
              Generar póliza contable automáticamente
            </label>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarMovimiento()">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
var tablaCuentas, tablaMovimientos;
var cuentasBancarias = [];

// Esperar a que jQuery esté disponible
if (typeof jQuery !== 'undefined') {
  $(document).ready(initBancos);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initBancos);
    }
  });
}

function initBancos() {
  // Inicializar DataTable de Cuentas
  tablaCuentas = $('#tablaCuentas').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: '<?=base_url()?>contabilidad/Bancos/lista_cuentas_ajax',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
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
      { data: 7, orderable: false }
    ],
    order: [[0, 'asc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    }
  });
  
  // Inicializar DataTable de Movimientos
  tablaMovimientos = $('#tablaMovimientos').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: '<?=base_url()?>contabilidad/Bancos/lista_movimientos_ajax',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
        d.cuenta_id = $('#filtro_cuenta').val();
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
    order: [[0, 'desc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    }
  });
  
  // Cargar cuentas contables
  cargarCuentasContables();
  
  // Cargar cuentas bancarias para filtros
  cargarCuentasBancarias();
}

function cargarCuentasContables() {
  $.post('<?=base_url()?>contabilidad/CuentasContables/get_cuentas_padre_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      let html = '<option value="">Seleccionar...</option>';
      result.cuentas.forEach(c => {
        if(c.codigo.startsWith('1.1.01')) { // Solo cuentas de bancos
          html += `<option value="${c.id}">${c.codigo} - ${c.nombre}</option>`;
        }
      });
      $('#cuenta_contable').html(html);
    }
  });
}

function cargarCuentasBancarias() {
  $.post('<?=base_url()?>contabilidad/Bancos/get_cuentas_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      cuentasBancarias = result.cuentas;
      
      // Llenar selector de movimientos
      let html = '<option value="">Seleccionar...</option>';
      result.cuentas.forEach(c => {
        html += `<option value="${c.id}">${c.banco} - ${c.numero_cuenta}</option>`;
      });
      $('#mov_cuenta').html(html);
      
      // Llenar filtro
      html = '<option value="">Todas las cuentas</option>';
      result.cuentas.forEach(c => {
        html += `<option value="${c.id}">${c.banco} - ${c.numero_cuenta}</option>`;
      });
      $('#filtro_cuenta').html(html);
    }
  });
}

function aplicarFiltrosMovimientos() {
  tablaMovimientos.ajax.reload();
}

function mostrarModalNuevaCuenta() {
  $('#formCuentaBancaria')[0].reset();
  $('#cuenta_id').val('');
  $('#modalCuentaBancaria').modal('show');
}

function mostrarModalNuevoMovimiento() {
  $('#formMovimiento')[0].reset();
  $('#mov_fecha').val('<?= date("Y-m-d") ?>');
  $('#modalMovimiento').modal('show');
}

function guardarCuentaBancaria() {
  let formData = $('#formCuentaBancaria').serialize();
  formData += '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>';
  
  $.post('<?=base_url()?>contabilidad/Bancos/crear_cuenta_ajax', formData, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#modalCuentaBancaria').modal('hide');
      tablaCuentas.ajax.reload();
      cargarCuentasBancarias();
      alert(result.message);
    } else {
      alert('Error: ' + result.message);
    }
  });
}

function guardarMovimiento() {
  let formData = $('#formMovimiento').serialize();
  formData += '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>';
  
  $.post('<?=base_url()?>contabilidad/Bancos/crear_movimiento_ajax', formData, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#modalMovimiento').modal('hide');
      tablaMovimientos.ajax.reload();
      tablaCuentas.ajax.reload();
      alert(result.message);
    } else {
      alert('Error: ' + result.message);
    }
  });
}

function verCuentaBancaria(id) {
  // TODO: Implementar vista de detalle
  alert('Ver cuenta ' + id);
}

function editarCuentaBancaria(id) {
  // TODO: Implementar edición
  alert('Editar cuenta ' + id);
}

function verMovimientos(id) {
  // Cambiar a tab de movimientos y filtrar
  $('#movimientos-tab').click();
  $('#filtro_cuenta').val(id);
  aplicarFiltrosMovimientos();
}

function conciliar(id) {
  // TODO: Implementar conciliación
  alert('Conciliar cuenta ' + id);
}

function verMovimiento(id) {
  // TODO: Implementar vista de detalle
  alert('Ver movimiento ' + id);
}

function marcarConciliado(id) {
  if(!confirm('¿Marcar este movimiento como conciliado?')) {
    return;
  }
  
  $.post('<?=base_url()?>contabilidad/Bancos/conciliar_movimiento_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    alert(result.message);
    if(result.success) {
      tablaMovimientos.ajax.reload();
    }
  });
}
</script>
