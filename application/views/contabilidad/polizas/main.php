<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container-fluid p-0">
  
  <!-- Header -->
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-file-invoice"></i> <?= $headTitle ?></h3>
    </div>
    
    <div class="col-auto ms-auto text-end mt-n1">
      <button type="button" class="btn btn-primary" onclick="mostrarModalNuevo()">
        <i class="fas fa-plus"></i> Nueva Póliza
      </button>
    </div>
  </div>

  <!-- Periodo Actual -->
  <?php if($periodo_actual): ?>
  <div class="row mb-3">
    <div class="col-12">
      <div class="alert alert-info">
        <i class="fas fa-calendar-alt"></i> 
        <strong>Periodo Actual:</strong> <?= $periodo_actual->nombre ?> <?= $periodo_actual->año ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Filtros -->
  <div class="row mb-3">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-2">
              <label>Tipo</label>
              <select id="filtro_tipo" class="form-select">
                <option value="">Todos</option>
                <option value="Ingresos">Ingresos</option>
                <option value="Egresos">Egresos</option>
                <option value="Diario">Diario</option>
                <option value="Cheque">Cheque</option>
              </select>
            </div>
            <div class="col-md-2">
              <label>Estatus</label>
              <select id="filtro_estatus" class="form-select">
                <option value="">Todos</option>
                <option value="Borrador">Borrador</option>
                <option value="Autorizada" selected>Autorizada</option>
                <option value="Cancelada">Cancelada</option>
              </select>
            </div>
            <div class="col-md-2">
              <label>Fecha Inicio</label>
              <input type="date" id="filtro_fecha_inicio" class="form-control">
            </div>
            <div class="col-md-2">
              <label>Fecha Fin</label>
              <input type="date" id="filtro_fecha_fin" class="form-control">
            </div>
            <div class="col-md-2">
              <label>&nbsp;</label><br>
              <button type="button" class="btn btn-info" onclick="aplicarFiltros()">
                <i class="fas fa-filter"></i> Filtrar
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
          <h5 class="card-title mb-0">Pólizas Contables</h5>
        </div>
        <div class="card-body">
          <table id="tablaPolizas" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th>Folio</th>
                <th>Tipo</th>
                <th>Fecha</th>
                <th>Periodo</th>
                <th>Concepto</th>
                <th>Debe</th>
                <th>Haber</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Folio</th>
                <th>Tipo</th>
                <th>Fecha</th>
                <th>Periodo</th>
                <th>Concepto</th>
                <th>Debe</th>
                <th>Haber</th>
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

<!-- Modal Nueva Póliza -->
<div class="modal fade" id="modalPoliza" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPolizaTitle">Nueva Póliza Contable</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formPoliza">
          <input type="hidden" id="poliza_id" name="id">
          
          <div class="row mb-3">
            <div class="col-md-3">
              <label for="poliza_tipo" class="form-label">Tipo de Póliza <span class="text-danger">*</span></label>
              <select class="form-select" id="poliza_tipo" name="tipo_poliza" required>
                <option value="">Seleccionar...</option>
                <option value="Ingresos">Ingresos</option>
                <option value="Egresos">Egresos</option>
                <option value="Diario">Diario</option>
                <option value="Cheque">Cheque</option>
              </select>
            </div>
            
            <div class="col-md-3">
              <label for="poliza_fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="poliza_fecha" name="fecha" required value="<?= date('Y-m-d') ?>">
            </div>
            
            <div class="col-md-3">
              <label for="poliza_periodo" class="form-label">Periodo <span class="text-danger">*</span></label>
              <select class="form-select" id="poliza_periodo" name="periodo_id" required>
                <?php if($periodo_actual): ?>
                <option value="<?= $periodo_actual->id ?>" selected><?= $periodo_actual->nombre ?> <?= $periodo_actual->año ?></option>
                <?php endif; ?>
              </select>
            </div>
            
            <div class="col-md-3">
              <label for="poliza_referencia" class="form-label">Referencia</label>
              <input type="text" class="form-control" id="poliza_referencia" name="referencia">
            </div>
          </div>
          
          <div class="mb-3">
            <label for="poliza_concepto" class="form-label">Concepto <span class="text-danger">*</span></label>
            <textarea class="form-control" id="poliza_concepto" name="concepto" rows="2" required></textarea>
          </div>
          
          <hr>
          
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6><i class="fas fa-list"></i> Detalle de la Póliza</h6>
            <button type="button" class="btn btn-sm btn-success" onclick="agregarFila()">
              <i class="fas fa-plus"></i> Agregar Cuenta
            </button>
          </div>
          
          <div class="table-responsive">
            <table class="table table-sm table-bordered" id="tablaDetalle">
              <thead class="table-light">
                <tr>
                  <th width="40%">Cuenta</th>
                  <th width="25%">Concepto</th>
                  <th width="15%">Debe</th>
                  <th width="15%">Haber</th>
                  <th width="5%"></th>
                </tr>
              </thead>
              <tbody id="detalleBody">
                <!-- Filas dinámicas -->
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <td colspan="2" class="text-end"><strong>TOTALES:</strong></td>
                  <td><strong id="totalDebe">$0.00</strong></td>
                  <td><strong id="totalHaber">$0.00</strong></td>
                  <td></td>
                </tr>
                <tr>
                  <td colspan="2" class="text-end"><strong>DIFERENCIA:</strong></td>
                  <td colspan="2">
                    <strong id="diferencia" class="text-success">$0.00</strong>
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
          
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Importante:</strong> El total de cargos (Debe) debe ser igual al total de abonos (Haber).
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarPoliza()">
          <i class="fas fa-save"></i> Guardar Póliza
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ver Detalle -->
<div class="modal fade" id="modalDetallePoliza" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de Póliza</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detallePolizaBody">
        <!-- Contenido dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Cancelar Póliza -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cancelar Póliza</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="cancelar_poliza_id">
        <div class="mb-3">
          <label for="motivo_cancelacion" class="form-label">Motivo de Cancelación <span class="text-danger">*</span></label>
          <textarea class="form-control" id="motivo_cancelacion" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="confirmarCancelacion()">
          <i class="fas fa-ban"></i> Cancelar Póliza
        </button>
      </div>
    </div>
  </div>
</div>

<script>
var tablaPolizas;
var cuentasContables = [];
var filaCounter = 0;

// Esperar a que jQuery esté disponible
if (typeof jQuery !== 'undefined') {
  $(document).ready(initPolizas);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initPolizas);
    }
  });
}

function initPolizas() {
  // Inicializar DataTable
  tablaPolizas = $('#tablaPolizas').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: '<?=base_url()?>contabilidad/Polizas/lista_ajax',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
        d.filtro_tipo = $('#filtro_tipo').val();
        d.filtro_estatus = $('#filtro_estatus').val();
        d.filtro_fecha_inicio = $('#filtro_fecha_inicio').val();
        d.filtro_fecha_fin = $('#filtro_fecha_fin').val();
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
    order: [[2, 'desc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    },
    pageLength: 25
  });
  
  // Cargar cuentas contables
  cargarCuentasContables();
}

function aplicarFiltros() {
  tablaPolizas.ajax.reload();
}

function cargarCuentasContables() {
  $.post('<?=base_url()?>contabilidad/CuentasContables/get_cuentas_padre_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      cuentasContables = result.cuentas;
    }
  });
}

function mostrarModalNuevo() {
  $('#modalPolizaTitle').text('Nueva Póliza Contable');
  $('#formPoliza')[0].reset();
  $('#poliza_id').val('');
  $('#poliza_fecha').val('<?= date("Y-m-d") ?>');
  $('#detalleBody').html('');
  filaCounter = 0;
  
  // Agregar 2 filas iniciales
  agregarFila();
  agregarFila();
  
  $('#modalPoliza').modal('show');
}

function agregarFila() {
  filaCounter++;
  let html = `
    <tr id="fila_${filaCounter}">
      <td>
        <select class="form-select form-select-sm cuenta-select" name="cuenta_id[]" required onchange="calcularTotales()">
          <option value="">Seleccionar cuenta...</option>
          ${cuentasContables.map(c => {
            let indent = '&nbsp;'.repeat((c.nivel - 1) * 4);
            return `<option value="${c.id}">${indent}${c.codigo} - ${c.nombre}</option>`;
          }).join('')}
        </select>
      </td>
      <td>
        <input type="text" class="form-control form-control-sm" name="concepto[]" placeholder="Concepto">
      </td>
      <td>
        <input type="number" step="0.01" class="form-control form-control-sm debe-input" name="debe[]" value="0" onchange="validarDebeHaber(this); calcularTotales()">
      </td>
      <td>
        <input type="number" step="0.01" class="form-control form-control-sm haber-input" name="haber[]" value="0" onchange="validarDebeHaber(this); calcularTotales()">
      </td>
      <td>
        <button type="button" class="btn btn-sm btn-danger" onclick="eliminarFila(${filaCounter})">
          <i class="fas fa-trash"></i>
        </button>
      </td>
    </tr>
  `;
  $('#detalleBody').append(html);
}

function eliminarFila(id) {
  $('#fila_' + id).remove();
  calcularTotales();
}

function validarDebeHaber(input) {
  let fila = $(input).closest('tr');
  let debe = parseFloat(fila.find('.debe-input').val()) || 0;
  let haber = parseFloat(fila.find('.haber-input').val()) || 0;
  
  // Solo uno puede tener valor
  if(debe > 0 && haber > 0) {
    if($(input).hasClass('debe-input')) {
      fila.find('.haber-input').val(0);
    } else {
      fila.find('.debe-input').val(0);
    }
  }
}

function calcularTotales() {
  let totalDebe = 0;
  let totalHaber = 0;
  
  $('.debe-input').each(function() {
    totalDebe += parseFloat($(this).val()) || 0;
  });
  
  $('.haber-input').each(function() {
    totalHaber += parseFloat($(this).val()) || 0;
  });
  
  $('#totalDebe').text('$' + totalDebe.toFixed(2));
  $('#totalHaber').text('$' + totalHaber.toFixed(2));
  
  let diferencia = Math.abs(totalDebe - totalHaber);
  $('#diferencia').text('$' + diferencia.toFixed(2));
  
  if(diferencia < 0.01) {
    $('#diferencia').removeClass('text-danger').addClass('text-success');
  } else {
    $('#diferencia').removeClass('text-success').addClass('text-danger');
  }
}

function guardarPoliza() {
  // Validar totales
  let totalDebe = 0;
  let totalHaber = 0;
  
  $('.debe-input').each(function() {
    totalDebe += parseFloat($(this).val()) || 0;
  });
  
  $('.haber-input').each(function() {
    totalHaber += parseFloat($(this).val()) || 0;
  });
  
  if(Math.abs(totalDebe - totalHaber) > 0.01) {
    alert('Error: El total de cargos debe ser igual al total de abonos');
    return;
  }
  
  // Construir detalle
  let detalle = [];
  $('#detalleBody tr').each(function(index) {
    let cuenta_id = $(this).find('.cuenta-select').val();
    let concepto = $(this).find('input[name="concepto[]"]').val();
    let debe = parseFloat($(this).find('.debe-input').val()) || 0;
    let haber = parseFloat($(this).find('.haber-input').val()) || 0;
    
    if(cuenta_id && (debe > 0 || haber > 0)) {
      detalle.push({
        cuenta_id: cuenta_id,
        concepto: concepto,
        debe: debe,
        haber: haber,
        orden: index + 1
      });
    }
  });
  
  if(detalle.length === 0) {
    alert('Debe agregar al menos un movimiento');
    return;
  }
  
  let formData = $('#formPoliza').serialize();
  formData += '&detalle=' + encodeURIComponent(JSON.stringify(detalle));
  formData += '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>';
  
  $.post('<?=base_url()?>contabilidad/Polizas/crear_ajax', formData, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#modalPoliza').modal('hide');
      tablaPolizas.ajax.reload();
      alert(result.message);
    } else {
      alert('Error: ' + result.message);
    }
  });
}

function verPoliza(id) {
  $.post('<?=base_url()?>contabilidad/Polizas/get_poliza_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      let p = result.poliza;
      let html = `
        <table class="table table-sm">
          <tr><th>Folio:</th><td>${p.folio}</td></tr>
          <tr><th>Tipo:</th><td>${p.tipo_poliza}</td></tr>
          <tr><th>Fecha:</th><td>${p.fecha}</td></tr>
          <tr><th>Concepto:</th><td>${p.concepto}</td></tr>
          <tr><th>Estatus:</th><td>${p.estatus}</td></tr>
        </table>
        <h6>Detalle:</h6>
        <table class="table table-sm table-bordered">
          <thead>
            <tr>
              <th>Cuenta</th>
              <th>Concepto</th>
              <th>Debe</th>
              <th>Haber</th>
            </tr>
          </thead>
          <tbody>
      `;
      
      p.detalle.forEach(d => {
        html += `
          <tr>
            <td>${d.cuenta_codigo} - ${d.cuenta_nombre}</td>
            <td>${d.concepto || ''}</td>
            <td>$${parseFloat(d.debe).toFixed(2)}</td>
            <td>$${parseFloat(d.haber).toFixed(2)}</td>
          </tr>
        `;
      });
      
      html += `
          </tbody>
          <tfoot>
            <tr>
              <th colspan="2">TOTALES:</th>
              <th>$${parseFloat(p.total_debe).toFixed(2)}</th>
              <th>$${parseFloat(p.total_haber).toFixed(2)}</th>
            </tr>
          </tfoot>
        </table>
      `;
      
      $('#detallePolizaBody').html(html);
      $('#modalDetallePoliza').modal('show');
    }
  });
}

function autorizarPoliza(id) {
  if(!confirm('¿Autorizar esta póliza? Una vez autorizada no podrá modificarse.')) {
    return;
  }
  
  $.post('<?=base_url()?>contabilidad/Polizas/autorizar_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    alert(result.message);
    if(result.success) {
      tablaPolizas.ajax.reload();
    }
  });
}

function cancelarPoliza(id) {
  $('#cancelar_poliza_id').val(id);
  $('#motivo_cancelacion').val('');
  $('#modalCancelar').modal('show');
}

function confirmarCancelacion() {
  let id = $('#cancelar_poliza_id').val();
  let motivo = $('#motivo_cancelacion').val();
  
  if(!motivo) {
    alert('Debe especificar el motivo de cancelación');
    return;
  }
  
  $.post('<?=base_url()?>contabilidad/Polizas/cancelar_ajax', {
    'id': id,
    'motivo': motivo,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    alert(result.message);
    if(result.success) {
      $('#modalCancelar').modal('hide');
      tablaPolizas.ajax.reload();
    }
  });
}

function eliminarPoliza(id) {
  if(!confirm('¿Eliminar esta póliza?')) {
    return;
  }
  
  $.post('<?=base_url()?>contabilidad/Polizas/eliminar_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    alert(result.message);
    if(result.success) {
      tablaPolizas.ajax.reload();
    }
  });
}

function editarPoliza(id) {
  // TODO: Implementar edición
  alert('Edición de póliza en desarrollo');
}
</script>
