<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container-fluid p-0">
  
  <!-- Header -->
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-calculator"></i> <?= $headTitle ?></h3>
    </div>
    
    <div class="col-auto ms-auto text-end mt-n1">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent p-0 mt-1 mb-0">
          <li class="breadcrumb-item"><a href="<?=base_url()?>dashboard">Inicio</a></li>
          <li class="breadcrumb-item"><a href="<?=base_url()?>contabilidad/Dashboard">Contabilidad</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div>
  </div>

  <!-- Periodo Actual -->
  <?php if($periodo_actual): ?>
  <div class="row">
    <div class="col-12">
      <div class="alert alert-info">
        <i class="fas fa-calendar-alt"></i> 
        <strong>Periodo Actual:</strong> <?= $periodo_actual->nombre ?> <?= $periodo_actual->año ?>
        (<?= date('d/m/Y', strtotime($periodo_actual->fecha_inicio)) ?> - <?= date('d/m/Y', strtotime($periodo_actual->fecha_fin)) ?>)
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Resumen Financiero -->
  <div class="row">
    <div class="col-xl-4 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col mt-0">
              <h5 class="card-title">Ingresos</h5>
            </div>
            <div class="col-auto">
              <div class="stat text-primary">
                <i class="fas fa-arrow-up"></i>
              </div>
            </div>
          </div>
          <h1 class="mt-1 mb-3">$<?= number_format($resumen_financiero['ingresos'], 2) ?></h1>
          <div class="mb-0">
            <span class="badge badge-success-light">
              <i class="mdi mdi-arrow-bottom-right"></i> Periodo actual
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col mt-0">
              <h5 class="card-title">Egresos</h5>
            </div>
            <div class="col-auto">
              <div class="stat text-danger">
                <i class="fas fa-arrow-down"></i>
              </div>
            </div>
          </div>
          <h1 class="mt-1 mb-3">$<?= number_format($resumen_financiero['egresos'], 2) ?></h1>
          <div class="mb-0">
            <span class="badge badge-danger-light">
              <i class="mdi mdi-arrow-bottom-right"></i> Periodo actual
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-4 col-md-6">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col mt-0">
              <h5 class="card-title">Utilidad</h5>
            </div>
            <div class="col-auto">
              <div class="stat <?= $resumen_financiero['utilidad'] >= 0 ? 'text-success' : 'text-danger' ?>">
                <i class="fas fa-chart-line"></i>
              </div>
            </div>
          </div>
          <h1 class="mt-1 mb-3">$<?= number_format($resumen_financiero['utilidad'], 2) ?></h1>
          <div class="mb-0">
            <span class="badge <?= $resumen_financiero['utilidad'] >= 0 ? 'badge-success-light' : 'badge-danger-light' ?>">
              <i class="mdi mdi-arrow-bottom-right"></i> Ingresos - Egresos
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Gráfica de Ingresos vs Egresos -->
  <div class="row">
    <div class="col-12 col-lg-8">
      <div class="card flex-fill w-100">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="fas fa-chart-bar"></i> Ingresos vs Egresos - Últimos 6 Meses</h5>
        </div>
        <div class="card-body">
          <div class="chart">
            <canvas id="chartIngresosEgresos"></canvas>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle"></i> Alertas</h5>
        </div>
        <div class="card-body">
          <div class="list-group list-group-flush">
            <?php if(count($polizas_pendientes) > 0): ?>
            <div class="list-group-item">
              <div class="row align-items-center">
                <div class="col-auto">
                  <span class="badge bg-warning rounded-pill"><?= count($polizas_pendientes) ?></span>
                </div>
                <div class="col">
                  <strong>Pólizas pendientes</strong><br>
                  <small class="text-muted">Requieren autorización</small>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <?php if($periodo_actual && $periodo_actual->estatus == 'Abierto'): ?>
            <div class="list-group-item">
              <div class="row align-items-center">
                <div class="col-auto">
                  <span class="badge bg-info rounded-pill"><i class="fas fa-calendar"></i></span>
                </div>
                <div class="col">
                  <strong>Periodo abierto</strong><br>
                  <small class="text-muted"><?= $periodo_actual->nombre ?> <?= $periodo_actual->año ?></small>
                </div>
              </div>
            </div>
            <?php endif; ?>

            <div class="list-group-item">
              <div class="row align-items-center">
                <div class="col-auto">
                  <span class="badge bg-success rounded-pill"><i class="fas fa-check"></i></span>
                </div>
                <div class="col">
                  <strong>Sistema operativo</strong><br>
                  <small class="text-muted">Todos los módulos funcionando</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Pólizas Recientes -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="fas fa-file-invoice"></i> Pólizas Pendientes de Autorizar</h5>
        </div>
        <div class="card-body">
          <?php if(count($polizas_pendientes) > 0): ?>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Folio</th>
                  <th>Tipo</th>
                  <th>Fecha</th>
                  <th>Concepto</th>
                  <th>Debe</th>
                  <th>Haber</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($polizas_pendientes as $poliza): ?>
                <tr>
                  <td><strong><?= $poliza->folio ?></strong></td>
                  <td>
                    <?php
                    $badge_tipo = '';
                    switch($poliza->tipo_poliza) {
                      case 'Ingresos': $badge_tipo = 'success'; break;
                      case 'Egresos': $badge_tipo = 'danger'; break;
                      case 'Diario': $badge_tipo = 'primary'; break;
                      default: $badge_tipo = 'secondary';
                    }
                    ?>
                    <span class="badge bg-<?= $badge_tipo ?>"><?= $poliza->tipo_poliza ?></span>
                  </td>
                  <td><?= date('d/m/Y', strtotime($poliza->fecha)) ?></td>
                  <td><?= substr($poliza->concepto, 0, 50) ?>...</td>
                  <td>$<?= number_format($poliza->total_debe, 2) ?></td>
                  <td>$<?= number_format($poliza->total_haber, 2) ?></td>
                  <td>
                    <button class="btn btn-sm btn-primary" onclick="verPoliza(<?= $poliza->id ?>)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success" onclick="autorizarPoliza(<?= $poliza->id ?>)">
                      <i class="fas fa-check"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
          <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle"></i> No hay pólizas pendientes de autorizar
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</div>

<script>
// Esperar a que jQuery esté disponible
if (typeof jQuery !== 'undefined') {
  $(document).ready(initDashboard);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initDashboard);
    }
  });
}

function initDashboard() {
  cargarGraficaIngresosEgresos();
}

function cargarGraficaIngresosEgresos() {
  $.post('<?=base_url()?>contabilidad/Dashboard/get_datos_graficas_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    
    if(result.success) {
      var ctx = document.getElementById('chartIngresosEgresos').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: result.labels,
          datasets: [{
            label: 'Ingresos',
            data: result.ingresos,
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
          }, {
            label: 'Egresos',
            data: result.egresos,
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return '$' + value.toLocaleString();
                }
              }
            }
          },
          plugins: {
            legend: {
              position: 'top',
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                }
              }
            }
          }
        }
      });
    }
  });
}

function verPoliza(id) {
  // TODO: Implementar vista de póliza
  alert('Ver póliza ' + id);
}

function autorizarPoliza(id) {
  // TODO: Implementar autorización
  if(confirm('¿Autorizar esta póliza?')) {
    alert('Autorizar póliza ' + id);
  }
}
</script>
