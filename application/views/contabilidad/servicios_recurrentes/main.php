<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container-fluid p-0">
  
  <!-- Header -->
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-sync-alt"></i> <?= $headTitle ?></h3>
    </div>
    
    <div class="col-auto ms-auto text-end mt-n1">
      <button type="button" class="btn btn-primary" onclick="mostrarModalNuevoServicio()">
        <i class="fas fa-plus"></i> Nuevo Servicio
      </button>
    </div>
  </div>

  <!-- Resumen del Mes -->
  <?php if($resumen): ?>
  <div class="row mb-3">
    <div class="col-md-3">
      <div class="card">
        <div class="card-body">
          <h6 class="card-subtitle text-muted">Total Servicios</h6>
          <h3 class="mb-0"><?= $resumen->total ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-warning">
        <div class="card-body">
          <h6 class="card-subtitle text-muted">Pendientes</h6>
          <h3 class="mb-0 text-warning"><?= $resumen->pendientes ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-success">
        <div class="card-body">
          <h6 class="card-subtitle text-muted">Pagados</h6>
          <h3 class="mb-0 text-success"><?= $resumen->pagados ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card border-danger">
        <div class="card-body">
          <h6 class="card-subtitle text-muted">Total a Pagar</h6>
          <h3 class="mb-0">$<?= number_format($resumen->total_monto - $resumen->monto_pagado, 2) ?></h3>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="serviciosTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="servicios-tab" data-bs-toggle="tab" data-bs-target="#servicios" type="button">
        <i class="fas fa-list"></i> Servicios
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos" type="button">
        <i class="fas fa-calendar-alt"></i> Calendario de Pagos
      </button>
    </li>
  </ul>

  <div class="tab-content" id="serviciosTabContent">
    
    <!-- Tab Servicios -->
    <div class="tab-pane fade show active" id="servicios" role="tabpanel">
      <div class="card">
        <div class="card-body">
          <table id="tablaServicios" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th>Servicio</th>
                <th>Tipo</th>
                <th>Proveedor</th>
                <th>Frecuencia</th>
                <th>Vencimiento</th>
                <th>Monto</th>
                <th>Cuenta Contable</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Servicio</th>
                <th>Tipo</th>
                <th>Proveedor</th>
                <th>Frecuencia</th>
                <th>Vencimiento</th>
                <th>Monto</th>
                <th>Cuenta Contable</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- Tab Pagos -->
    <div class="tab-pane fade" id="pagos" role="tabpanel">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-md-3">
              <label>Periodo</label>
              <input type="month" id="filtro_periodo" class="form-control" value="<?= date('Y-m') ?>" onchange="aplicarFiltrosPagos()">
            </div>
          </div>
        </div>
        <div class="card-body">
          <table id="tablaPagos" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th>Servicio</th>
                <th>Tipo</th>
                <th>Vencimiento</th>
                <th>Monto</th>
                <th>Fecha Pago</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Servicio</th>
                <th>Tipo</th>
                <th>Vencimiento</th>
                <th>Monto</th>
                <th>Fecha Pago</th>
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

<!-- Modal Nuevo Servicio -->
<div class="modal fade" id="modalServicio" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nuevo Servicio Recurrente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formServicio">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="servicio_nombre" class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="servicio_nombre" name="nombre_servicio" required>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="servicio_tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
              <select class="form-select" id="servicio_tipo" name="tipo_servicio" required>
                <option value="">Seleccionar...</option>
                <option value="Servicios Públicos">Servicios Públicos</option>
                <option value="Renta">Renta</option>
                <option value="Seguros">Seguros</option>
                <option value="Suscripciones">Suscripciones</option>
                <option value="Mantenimiento">Mantenimiento</option>
                <option value="Otros">Otros</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-4 mb-3">
              <label for="servicio_frecuencia" class="form-label">Frecuencia <span class="text-danger">*</span></label>
              <select class="form-select" id="servicio_frecuencia" name="frecuencia" required>
                <option value="Mensual" selected>Mensual</option>
                <option value="Bimestral">Bimestral</option>
                <option value="Trimestral">Trimestral</option>
                <option value="Semestral">Semestral</option>
                <option value="Anual">Anual</option>
              </select>
            </div>
            
            <div class="col-md-4 mb-3">
              <label for="servicio_dia" class="form-label">Día de Vencimiento <span class="text-danger">*</span></label>
              <input type="number" class="form-control" id="servicio_dia" name="dia_vencimiento" min="1" max="31" required>
            </div>
            
            <div class="col-md-4 mb-3">
              <label for="servicio_monto" class="form-label">Monto Estimado <span class="text-danger">*</span></label>
              <input type="number" step="0.01" class="form-control" id="servicio_monto" name="monto_estimado" required>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="servicio_cuenta_contable" class="form-label">Cuenta Contable</label>
              <select class="form-select" id="servicio_cuenta_contable" name="cuenta_contable_id">
                <option value="">Seleccionar...</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="servicio_cuenta_bancaria" class="form-label">Cuenta Bancaria</label>
              <select class="form-select" id="servicio_cuenta_bancaria" name="cuenta_bancaria_id">
                <option value="">Seleccionar...</option>
              </select>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="servicio_descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="servicio_descripcion" name="descripcion" rows="2"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarServicio()">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Registrar Pago -->
<div class="modal fade" id="modalPago" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Pago</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formPago">
          <input type="hidden" id="pago_id" name="pago_id">
          
          <div class="mb-3">
            <label for="pago_fecha" class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="pago_fecha" name="fecha_pago" required value="<?= date('Y-m-d') ?>">
          </div>
          
          <div class="mb-3">
            <label for="pago_monto" class="form-label">Monto <span class="text-danger">*</span></label>
            <input type="number" step="0.01" class="form-control" id="pago_monto" name="monto" required>
          </div>
          
          <div class="mb-3">
            <label for="pago_referencia" class="form-label">Referencia</label>
            <input type="text" class="form-control" id="pago_referencia" name="referencia">
          </div>
          
          <div class="mb-3">
            <label for="pago_notas" class="form-label">Notas</label>
            <textarea class="form-control" id="pago_notas" name="notas" rows="2"></textarea>
          </div>
          
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Se generará automáticamente la póliza contable y el movimiento bancario.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="confirmarPago()">
          <i class="fas fa-check"></i> Registrar Pago
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Esperar a que jQuery esté disponible
if (typeof jQuery !== 'undefined') {
  $(document).ready(initServicios);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initServicios);
    }
  });
}

function initServicios() {
  // Inicializar DataTable de Servicios
  window.tablaServicios = $('#tablaServicios').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: '<?=base_url()?>contabilidad/ServiciosRecurrentes/lista_servicios_ajax',
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
      { data: 7 },
      { data: 8, orderable: false }
    ],
    order: [[0, 'asc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    }
  });
  
  // Inicializar DataTable de Pagos
  window.tablaPagos = $('#tablaPagos').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: '<?=base_url()?>contabilidad/ServiciosRecurrentes/lista_pagos_ajax',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
        d.periodo = $('#filtro_periodo').val();
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
    order: [[2, 'asc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    }
  });
  
  // Cargar cuentas contables y bancarias
  cargarCuentas();
}

function cargarCuentas() {
  // Cuentas contables
  $.post('<?=base_url()?>contabilidad/CuentasContables/get_cuentas_padre_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      let html = '<option value="">Seleccionar...</option>';
      result.cuentas.forEach(c => {
        if(c.codigo.startsWith('6')) { // Solo cuentas de gastos
          html += `<option value="${c.id}">${c.codigo} - ${c.nombre}</option>`;
        }
      });
      $('#servicio_cuenta_contable').html(html);
    }
  });
  
  // Cuentas bancarias
  $.post('<?=base_url()?>contabilidad/Bancos/get_cuentas_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      let html = '<option value="">Seleccionar...</option>';
      result.cuentas.forEach(c => {
        html += `<option value="${c.id}">${c.banco} - ${c.numero_cuenta}</option>`;
      });
      $('#servicio_cuenta_bancaria').html(html);
    }
  });
}

function aplicarFiltrosPagos() {
  if (window.tablaPagos) {
    window.tablaPagos.ajax.reload();
  }
}

function mostrarModalNuevoServicio() {
  $('#formServicio')[0].reset();
  $('#modalServicio').modal('show');
}

function guardarServicio() {
  let formData = $('#formServicio').serialize();
  formData += '&fecha_inicio=<?= date("Y-m-01") ?>&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>';
  
  $.post('<?=base_url()?>contabilidad/ServiciosRecurrentes/crear_servicio_ajax', formData, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#modalServicio').modal('hide');
      if (window.tablaServicios) window.tablaServicios.ajax.reload();
      if (window.tablaPagos) window.tablaPagos.ajax.reload();
      alert(result.message);
    } else {
      alert('Error: ' + result.message);
    }
  });
}

function registrarPago(servicio_id) {
  // TODO: Implementar
  alert('Registrar pago para servicio ' + servicio_id);
}

function pagarServicio(pago_id) {
  $('#pago_id').val(pago_id);
  $('#formPago')[0].reset();
  $('#pago_fecha').val('<?= date("Y-m-d") ?>');
  $('#modalPago').modal('show');
}

function confirmarPago() {
  let formData = $('#formPago').serialize();
  formData += '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>';
  
  $.post('<?=base_url()?>contabilidad/ServiciosRecurrentes/registrar_pago_ajax', formData, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#modalPago').modal('hide');
      if (window.tablaPagos) window.tablaPagos.ajax.reload();
      location.reload(); // Recargar para actualizar resumen
    } else {
      alert('Error: ' + result.message);
    }
  });
}

function verServicio(id) {
  alert('Ver servicio ' + id);
}

function editarServicio(id) {
  alert('Editar servicio ' + id);
}

function verHistorial(id) {
  alert('Ver historial ' + id);
}

function verPago(id) {
  alert('Ver pago ' + id);
}
</script>
