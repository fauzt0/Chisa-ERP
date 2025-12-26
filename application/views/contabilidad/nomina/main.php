<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container-fluid p-0">
  
  <!-- Header -->
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-money-check-alt"></i> <?= $headTitle ?></h3>
    </div>
    
    <div class="col-auto ms-auto text-end mt-n1">
      <button type="button" class="btn btn-primary" onclick="mostrarModalNuevo()">
        <i class="fas fa-plus"></i> Nueva Nómina
      </button>
    </div>
  </div>

  <!-- DataTable -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Nóminas</h5>
        </div>
        <div class="card-body">
          <table id="tablaNominas" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th>Folio</th>
                <th>Tipo</th>
                <th>Periodo</th>
                <th>Fecha Pago</th>
                <th>Percepciones</th>
                <th>Deducciones</th>
                <th>Neto</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <th>Folio</th>
                <th>Tipo</th>
                <th>Periodo</th>
                <th>Fecha Pago</th>
                <th>Percepciones</th>
                <th>Deducciones</th>
                <th>Neto</th>
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

<!-- Modal Nueva Nómina -->
<div class="modal fade" id="modalNomina" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva Nómina</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formNomina">
          <div class="mb-3">
            <label for="nomina_tipo" class="form-label">Tipo de Nómina <span class="text-danger">*</span></label>
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
              <label for="nomina_periodo_inicio" class="form-label">Periodo Inicio <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="nomina_periodo_inicio" name="periodo_inicio" required>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="nomina_periodo_fin" class="form-label">Periodo Fin <span class="text-danger">*</span></label>
              <input type="date" class="form-control" id="nomina_periodo_fin" name="periodo_fin" required>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="nomina_fecha_pago" class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="nomina_fecha_pago" name="fecha_pago" required>
          </div>
          
          <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Se agregarán automáticamente todos los empleados activos del tipo de nómina seleccionado.
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarNomina()">
          <i class="fas fa-save"></i> Crear Nómina
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ver Detalle -->
<div class="modal fade" id="modalDetalleNomina" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de Nómina</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detalleNominaBody">
        <!-- Contenido dinámico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
var tablaNominas;

// Esperar a que jQuery esté disponible
if (typeof jQuery !== 'undefined') {
  $(document).ready(initNomina);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initNomina);
    }
  });
}

function initNomina() {
  // Inicializar DataTable
  tablaNominas = $('#tablaNominas').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: '<?=base_url()?>contabilidad/Nomina/lista_ajax',
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
    order: [[3, 'desc']],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    },
    pageLength: 25
  });
}

function mostrarModalNuevo() {
  $('#formNomina')[0].reset();
  $('#nomina_fecha_pago').val('<?= date("Y-m-d") ?>');
  $('#modalNomina').modal('show');
}

function guardarNomina() {
  let formData = $('#formNomina').serialize();
  formData += '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>';
  
  $.post('<?=base_url()?>contabilidad/Nomina/crear_ajax', formData, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#modalNomina').modal('hide');
      tablaNominas.ajax.reload();
      alert(result.message);
    } else {
      alert('Error: ' + result.message);
    }
  });
}

function verNomina(id) {
  $.post('<?=base_url()?>contabilidad/Nomina/get_nomina_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      let n = result.nomina;
      let html = `
        <div class="row mb-3">
          <div class="col-md-6">
            <table class="table table-sm">
              <tr><th>Folio:</th><td>${n.folio}</td></tr>
              <tr><th>Tipo:</th><td>${n.tipo_nomina}</td></tr>
              <tr><th>Periodo:</th><td>${n.periodo_inicio} - ${n.periodo_fin}</td></tr>
              <tr><th>Fecha Pago:</th><td>${n.fecha_pago}</td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <table class="table table-sm">
              <tr><th>Percepciones:</th><td>$${parseFloat(n.total_percepciones).toFixed(2)}</td></tr>
              <tr><th>Deducciones:</th><td>$${parseFloat(n.total_deducciones).toFixed(2)}</td></tr>
              <tr><th>Neto:</th><td><strong>$${parseFloat(n.total_neto).toFixed(2)}</strong></td></tr>
              <tr><th>Estatus:</th><td>${n.estatus}</td></tr>
            </table>
          </div>
        </div>
        
        <h6>Detalle por Empleado:</h6>
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead>
              <tr>
                <th>Empleado</th>
                <th>Puesto</th>
                <th>Días</th>
                <th>Sueldo</th>
                <th>Percepciones</th>
                <th>Deducciones</th>
                <th>Neto</th>
              </tr>
            </thead>
            <tbody>
      `;
      
      if(n.detalle && n.detalle.length > 0) {
        n.detalle.forEach(d => {
          html += `
            <tr>
              <td>${d.nombre} ${d.apellido_paterno}</td>
              <td>${d.puesto || 'N/A'}</td>
              <td>${d.dias_trabajados || 0}</td>
              <td>$${parseFloat(d.sueldo_base || 0).toFixed(2)}</td>
              <td>$${parseFloat(d.percepciones || 0).toFixed(2)}</td>
              <td>$${parseFloat(d.deducciones || 0).toFixed(2)}</td>
              <td><strong>$${parseFloat(d.neto || 0).toFixed(2)}</strong></td>
            </tr>
          `;
        });
      } else {
        html += '<tr><td colspan="7" class="text-center">No hay empleados en esta nómina</td></tr>';
      }
      
      html += `
            </tbody>
            <tfoot>
              <tr>
                <th colspan="4">TOTALES:</th>
                <th>$${parseFloat(n.total_percepciones).toFixed(2)}</th>
                <th>$${parseFloat(n.total_deducciones).toFixed(2)}</th>
                <th>$${parseFloat(n.total_neto).toFixed(2)}</th>
              </tr>
            </tfoot>
          </table>
        </div>
      `;
      
      $('#detalleNominaBody').html(html);
      $('#modalDetalleNomina').modal('show');
    }
  });
}

function calcularNomina(id) {
  if(!confirm('¿Calcular esta nómina? Se procesarán todos los empleados.')) {
    return;
  }
  
  $.post('<?=base_url()?>contabilidad/Nomina/calcular_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    alert(result.message);
    if(result.success) {
      tablaNominas.ajax.reload();
    }
  });
}

function pagarNomina(id) {
  if(!confirm('¿Marcar esta nómina como pagada? Se generará la póliza contable automáticamente.')) {
    return;
  }
  
  $.post('<?=base_url()?>contabilidad/Nomina/pagar_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    alert(result.message);
    if(result.success) {
      tablaNominas.ajax.reload();
    }
  });
}

function eliminarNomina(id) {
  if(!confirm('¿Eliminar esta nómina?')) {
    return;
  }
  
  $.post('<?=base_url()?>contabilidad/Nomina/eliminar_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    alert(result.message);
    if(result.success) {
      tablaNominas.ajax.reload();
    }
  });
}

function imprimirRecibos(id) {
  // TODO: Implementar impresión de recibos
  window.open('<?=base_url()?>contabilidad/Nomina/imprimir_recibos/' + id, '_blank');
}
</script>
