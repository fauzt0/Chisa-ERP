<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container-fluid p-0">
  
  <!-- Header -->
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-chart-bar"></i> <?= $headTitle ?></h3>
    </div>
  </div>

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="reportesTabs" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="balance-tab" data-bs-toggle="tab" data-bs-target="#balance" type="button">
        <i class="fas fa-balance-scale"></i> Balance General
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="resultados-tab" data-bs-toggle="tab" data-bs-target="#resultados" type="button">
        <i class="fas fa-chart-line"></i> Estado de Resultados
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="balanza-tab" data-bs-toggle="tab" data-bs-target="#balanza" type="button">
        <i class="fas fa-table"></i> Balanza de Comprobación
      </button>
    </li>
  </ul>

  <div class="tab-content" id="reportesTabContent">
    
    <!-- Tab Balance General -->
    <div class="tab-pane fade show active" id="balance" role="tabpanel">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-md-3">
              <label>Fecha de Corte</label>
              <input type="date" id="balance_fecha" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
              <label>&nbsp;</label><br>
              <button type="button" class="btn btn-primary" onclick="generarBalanceGeneral()">
                <i class="fas fa-play"></i> Generar
              </button>
            </div>
          </div>
        </div>
        <div class="card-body" id="balanceContent">
          <div class="text-center text-muted py-5">
            <i class="fas fa-chart-bar fa-3x mb-3"></i>
            <p>Seleccione una fecha y haga clic en "Generar" para ver el Balance General</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab Estado de Resultados -->
    <div class="tab-pane fade" id="resultados" role="tabpanel">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-md-3">
              <label>Fecha Inicio</label>
              <input type="date" id="resultados_inicio" class="form-control">
            </div>
            <div class="col-md-3">
              <label>Fecha Fin</label>
              <input type="date" id="resultados_fin" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
              <label>&nbsp;</label><br>
              <button type="button" class="btn btn-primary" onclick="generarEstadoResultados()">
                <i class="fas fa-play"></i> Generar
              </button>
            </div>
          </div>
        </div>
        <div class="card-body" id="resultadosContent">
          <div class="text-center text-muted py-5">
            <i class="fas fa-chart-line fa-3x mb-3"></i>
            <p>Seleccione un periodo y haga clic en "Generar" para ver el Estado de Resultados</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab Balanza de Comprobación -->
    <div class="tab-pane fade" id="balanza" role="tabpanel">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-md-3">
              <label>Fecha Inicio</label>
              <input type="date" id="balanza_inicio" class="form-control">
            </div>
            <div class="col-md-3">
              <label>Fecha Fin</label>
              <input type="date" id="balanza_fin" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
              <label>&nbsp;</label><br>
              <button type="button" class="btn btn-primary" onclick="generarBalanzaComprobacion()">
                <i class="fas fa-play"></i> Generar
              </button>
            </div>
          </div>
        </div>
        <div class="card-body" id="balanzaContent">
          <div class="text-center text-muted py-5">
            <i class="fas fa-table fa-3x mb-3"></i>
            <p>Seleccione un periodo y haga clic en "Generar" para ver la Balanza de Comprobación</p>
          </div>
        </div>
      </div>
    </div>

  </div>

</div>

<script>
// Esperar a que jQuery esté disponible
if (typeof jQuery !== 'undefined') {
  $(document).ready(initReportes);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initReportes);
    }
  });
}

function initReportes() {
  // Establecer fecha de inicio del ejercicio actual
  <?php if($ejercicio_actual): ?>
  $('#resultados_inicio').val('<?= $ejercicio_actual->fecha_inicio ?>');
  $('#balanza_inicio').val('<?= $ejercicio_actual->fecha_inicio ?>');
  <?php endif; ?>
}

function generarBalanceGeneral() {
  let fecha = $('#balance_fecha').val();
  
  if(!fecha) {
    alert('Debe seleccionar una fecha');
    return;
  }
  
  $.post('<?=base_url()?>contabilidad/Reportes/balance_general_ajax', {
    'fecha_corte': fecha,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      mostrarBalanceGeneral(result.data);
    }
  });
}

function mostrarBalanceGeneral(data) {
  let html = `
    <div class="text-center mb-4">
      <h4>BALANCE GENERAL</h4>
      <p>Al ${formatDate(data.fecha_corte)}</p>
    </div>
    
    <div class="row">
      <div class="col-md-6">
        <h5 class="bg-primary text-white p-2">ACTIVO</h5>
        <table class="table table-sm">
          <tbody>
  `;
  
  data.activo.forEach(c => {
    html += `<tr><td>${c.codigo} - ${c.nombre}</td><td class="text-end">$${formatNumber(c.saldo)}</td></tr>`;
  });
  
  html += `
          </tbody>
          <tfoot>
            <tr class="fw-bold">
              <td>TOTAL ACTIVO</td>
              <td class="text-end">$${formatNumber(data.total_activo)}</td>
            </tr>
          </tfoot>
        </table>
      </div>
      
      <div class="col-md-6">
        <h5 class="bg-danger text-white p-2">PASIVO</h5>
        <table class="table table-sm">
          <tbody>
  `;
  
  data.pasivo.forEach(c => {
    html += `<tr><td>${c.codigo} - ${c.nombre}</td><td class="text-end">$${formatNumber(c.saldo)}</td></tr>`;
  });
  
  html += `
          </tbody>
          <tfoot>
            <tr class="fw-bold">
              <td>TOTAL PASIVO</td>
              <td class="text-end">$${formatNumber(data.total_pasivo)}</td>
            </tr>
          </tfoot>
        </table>
        
        <h5 class="bg-success text-white p-2 mt-3">CAPITAL</h5>
        <table class="table table-sm">
          <tbody>
  `;
  
  data.capital.forEach(c => {
    html += `<tr><td>${c.codigo} - ${c.nombre}</td><td class="text-end">$${formatNumber(c.saldo)}</td></tr>`;
  });
  
  html += `
            <tr><td>Utilidad del Ejercicio</td><td class="text-end">$${formatNumber(data.utilidad_ejercicio)}</td></tr>
          </tbody>
          <tfoot>
            <tr class="fw-bold">
              <td>TOTAL CAPITAL</td>
              <td class="text-end">$${formatNumber(data.total_capital + data.utilidad_ejercicio)}</td>
            </tr>
            <tr class="fw-bold bg-light">
              <td>TOTAL PASIVO + CAPITAL</td>
              <td class="text-end">$${formatNumber(data.total_pasivo_capital)}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  `;
  
  $('#balanceContent').html(html);
}

function generarEstadoResultados() {
  let inicio = $('#resultados_inicio').val();
  let fin = $('#resultados_fin').val();
  
  if(!fin) {
    alert('Debe seleccionar al menos la fecha fin');
    return;
  }
  
  $.post('<?=base_url()?>contabilidad/Reportes/estado_resultados_ajax', {
    'fecha_inicio': inicio,
    'fecha_fin': fin,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      mostrarEstadoResultados(result.data);
    }
  });
}

function mostrarEstadoResultados(data) {
  let html = `
    <div class="text-center mb-4">
      <h4>ESTADO DE RESULTADOS</h4>
      <p>Del ${formatDate(data.fecha_inicio)} al ${formatDate(data.fecha_fin)}</p>
    </div>
    
    <table class="table">
      <tbody>
        <tr class="table-info">
          <td colspan="2"><strong>INGRESOS</strong></td>
        </tr>
  `;
  
  data.ingresos.forEach(c => {
    let monto = c.haber - c.debe;
    html += `<tr><td class="ps-4">${c.codigo} - ${c.nombre}</td><td class="text-end">$${formatNumber(monto)}</td></tr>`;
  });
  
  html += `
        <tr class="fw-bold">
          <td>TOTAL INGRESOS</td>
          <td class="text-end">$${formatNumber(data.total_ingresos)}</td>
        </tr>
        
        <tr class="table-warning">
          <td colspan="2"><strong>COSTOS</strong></td>
        </tr>
  `;
  
  data.costos.forEach(c => {
    let monto = c.debe - c.haber;
    html += `<tr><td class="ps-4">${c.codigo} - ${c.nombre}</td><td class="text-end">$${formatNumber(monto)}</td></tr>`;
  });
  
  html += `
        <tr class="fw-bold">
          <td>TOTAL COSTOS</td>
          <td class="text-end">$${formatNumber(data.total_costos)}</td>
        </tr>
        
        <tr class="table-success">
          <td><strong>UTILIDAD BRUTA</strong></td>
          <td class="text-end"><strong>$${formatNumber(data.utilidad_bruta)}</strong></td>
        </tr>
        
        <tr class="table-danger">
          <td colspan="2"><strong>GASTOS DE OPERACIÓN</strong></td>
        </tr>
  `;
  
  data.egresos.forEach(c => {
    let monto = c.debe - c.haber;
    html += `<tr><td class="ps-4">${c.codigo} - ${c.nombre}</td><td class="text-end">$${formatNumber(monto)}</td></tr>`;
  });
  
  html += `
        <tr class="fw-bold">
          <td>TOTAL GASTOS</td>
          <td class="text-end">$${formatNumber(data.total_egresos)}</td>
        </tr>
        
        <tr class="table-${data.utilidad_neta >= 0 ? 'success' : 'danger'}">
          <td><strong>UTILIDAD NETA</strong></td>
          <td class="text-end"><strong>$${formatNumber(data.utilidad_neta)}</strong></td>
        </tr>
      </tbody>
    </table>
  `;
  
  $('#resultadosContent').html(html);
}

function generarBalanzaComprobacion() {
  let inicio = $('#balanza_inicio').val();
  let fin = $('#balanza_fin').val();
  
  if(!fin) {
    alert('Debe seleccionar al menos la fecha fin');
    return;
  }
  
  $.post('<?=base_url()?>contabilidad/Reportes/balanza_comprobacion_ajax', {
    'fecha_inicio': inicio,
    'fecha_fin': fin,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      mostrarBalanzaComprobacion(result.data);
    }
  });
}

function mostrarBalanzaComprobacion(data) {
  let html = `
    <div class="text-center mb-4">
      <h4>BALANZA DE COMPROBACIÓN</h4>
      <p>Del ${formatDate(data.fecha_inicio)} al ${formatDate(data.fecha_fin)}</p>
    </div>
    
    <div class="table-responsive">
      <table class="table table-sm table-bordered">
        <thead class="table-light">
          <tr>
            <th>Código</th>
            <th>Cuenta</th>
            <th class="text-end">Debe</th>
            <th class="text-end">Haber</th>
            <th class="text-end">Saldo Deudor</th>
            <th class="text-end">Saldo Acreedor</th>
          </tr>
        </thead>
        <tbody>
  `;
  
  data.cuentas.forEach(c => {
    html += `
      <tr>
        <td>${c.codigo}</td>
        <td>${c.nombre}</td>
        <td class="text-end">$${formatNumber(c.total_debe)}</td>
        <td class="text-end">$${formatNumber(c.total_haber)}</td>
        <td class="text-end">${c.saldo_deudor > 0 ? '$' + formatNumber(c.saldo_deudor) : ''}</td>
        <td class="text-end">${c.saldo_acreedor > 0 ? '$' + formatNumber(c.saldo_acreedor) : ''}</td>
      </tr>
    `;
  });
  
  html += `
        </tbody>
        <tfoot class="table-light fw-bold">
          <tr>
            <td colspan="2">TOTALES</td>
            <td class="text-end">$${formatNumber(data.total_debe)}</td>
            <td class="text-end">$${formatNumber(data.total_haber)}</td>
            <td class="text-end">$${formatNumber(data.saldo_deudor_total)}</td>
            <td class="text-end">$${formatNumber(data.saldo_acreedor_total)}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  `;
  
  $('#balanzaContent').html(html);
}

function formatNumber(num) {
  return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function formatDate(dateStr) {
  if(!dateStr) return 'N/A';
  let parts = dateStr.split('-');
  return parts[2] + '/' + parts[1] + '/' + parts[0];
}
</script>
