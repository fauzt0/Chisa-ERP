<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$empleado = isset($response['empleado']) ? $response['empleado'] : null;
$vacaciones = isset($response['vacaciones']) ? $response['vacaciones'] : null;
$mensaje_error = isset($response['mensaje_error']) ? $response['mensaje_error'] : null;
?>

<?php if(!empty($mensaje_error)): ?>
<div class="alert alert-warning" role="alert">
    <div class="alert-message">
        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Atención</h4>
        <p><?php echo $mensaje_error; ?></p>
        <hr>
        <p class="mb-0">Si crees que esto es un error, por favor contacta al departamento de Recursos Humanos.</p>
    </div>
</div>
<?php endif; ?>

<?php if($empleado): ?>
<div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
        <h3><strong>Hola,</strong> <?php echo $empleado->nombre; ?></h3>
    </div>
</div>

<div class="row">
    <!-- Información Personal -->
    <div class="col-md-4 col-xl-3">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Mi Información</h5>
            </div>
            <div class="card-body text-center">
                <div class="avatar avatar-xl rounded-circle me-2 mb-2 bg-primary text-white d-flex justify-content-center align-items-center mx-auto" style="width: 100px; height: 100px; font-size: 2rem;">
                    <?php echo substr($empleado->nombre, 0, 1) . substr($empleado->apellido_paterno, 0, 1); ?>
                </div>
                <h5 class="card-title mb-0"><?php echo $empleado->nombre . ' ' . $empleado->apellido_paterno; ?></h5>
                <div class="text-muted mb-2"><?php echo $empleado->puesto; ?></div>

                <div>
                    <span class="badge bg-primary"><?php echo $empleado->departamento_nombre ?: 'Sin Departamento'; ?></span>
                </div>
            </div>
            <hr class="my-0" />
            <div class="card-body">
                <h5 class="h6 card-title">Detalles</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-1"><i class="fas fa-id-badge fa-fw me-1"></i> No. Emp: <?php echo $empleado->numero_empleado; ?></li>
                    <li class="mb-1"><i class="fas fa-calendar-alt fa-fw me-1"></i> Ingreso: <?php echo $this->init_controller->date_to_string($empleado->fecha_ingreso); ?></li>
                    <li class="mb-1"><i class="fas fa-map-marker-alt fa-fw me-1"></i> <?php echo $empleado->ciudad . ', ' . $empleado->estado; ?></li>
                    <li class="mb-1"><i class="fas fa-envelope fa-fw me-1"></i> <?php echo $empleado->email_corporativo ?: 'Sin email corporativo'; ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Panel Principal -->
    <div class="col-md-8 col-xl-9">
        
        <!-- Balance de Vacaciones -->
        <?php if($vacaciones): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Mi Balance de Vacaciones</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSolicitarVacaciones">
                    <i class="fas fa-plus"></i> Solicitar
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-success text-white mb-3">
                            <div class="card-body text-center">
                                <h3><?php echo $vacaciones->dias_disponibles; ?></h3>
                                <span>Días Disponibles</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light text-dark mb-3">
                            <div class="card-body text-center">
                                <h3><?php echo $vacaciones->dias_tomados; ?></h3>
                                <span>Días Tomados</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white mb-3">
                            <div class="card-body text-center">
                                <h3><?php echo $vacaciones->dias_totales; ?></h3>
                                <span>Total Periodo <?php echo date('Y', strtotime($vacaciones->periodo_inicio)); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="fas fa-umbrella-beach fa-3x text-muted mb-3"></i>
                    <h5>Sin información de vacaciones activa</h5>
                    <p class="text-muted">No tienes un periodo vacacional activo en este momento o no cumples con la antigüedad mínima.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Historial de Actividad Reciente (Placeholder) -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Mi Actividad Reciente</h5>
            </div>
            <div class="card-body">
                <div class="text-center text-muted py-3">
                    Próximamente verás aquí tu historial de solicitudes y recibos de nómina.
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Solicitar Vacaciones -->
<div class="modal fade" id="modalSolicitarVacaciones" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Solicitar Vacaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formSolicitarVacaciones">
          <div class="mb-3">
            <label class="form-label">Fecha Inicio</label>
            <input type="date" class="form-control" name="fecha_inicio" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha Fin</label>
            <input type="date" class="form-control" name="fecha_fin" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="enviarSolicitud()">Enviar Solicitud</button>
      </div>
    </div>
  </div>
</div>

<script>
function enviarSolicitud() {
    var form = $('#formSolicitarVacaciones').serialize();
    
    $.post('<?=base_url()?>usuarios/Perfil/solicitar_vacaciones', form, function(data){
        var result = JSON.parse(data);
        if(result.success) {
            alert('Solicitud enviada correctamente');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    });
}
</script>
<?php endif; ?>
