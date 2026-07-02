<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$user = $response['user'] ?? null;
$empleado = $response['empleado'] ?? null;
$vacaciones = $response['vacaciones'] ?? null;
$solicitudes = $response['solicitudes'] ?? [];
$mensaje_vinculo = $response['mensaje_vinculo'] ?? '';
$iniciales = $user ? strtoupper(substr($user->nombre, 0, 1) . substr($user->apellidos, 0, 1)) : '?';
?>

<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

<div class="row mb-2 mb-xl-3">
  <div class="col">
    <h3 class="mb-0"><strong>Mi Perfil</strong></h3>
    <p class="text-muted mb-0">Cuenta de acceso y expediente laboral</p>
  </div>
</div>

<div class="row">
  <!-- Cuenta de usuario (siempre visible) -->
  <div class="col-lg-4 mb-3">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-user-cog"></i> Mi Cuenta</h5>
      </div>
      <div class="card-body">
        <div class="text-center mb-3">
          <div class="avatar rounded-circle bg-primary text-white d-inline-flex justify-content-center align-items-center mx-auto"
               style="width: 80px; height: 80px; font-size: 1.75rem;">
            <?=htmlspecialchars($iniciales)?>
          </div>
          <h5 class="mt-2 mb-0"><?=htmlspecialchars(trim(($user->nombre ?? '') . ' ' . ($user->apellidos ?? '')))?></h5>
          <div class="text-muted small"><?=htmlspecialchars($user->username ?? '')?></div>
          <?php if(!empty($user->departamento)): ?>
          <span class="badge bg-secondary mt-1"><?=htmlspecialchars($user->departamento)?></span>
          <?php endif; ?>
        </div>

        <form id="formMiCuenta">
          <div class="mb-3">
            <label class="form-label">Nombre(s)</label>
            <input type="text" class="form-control" name="nombre" id="perfil_nombre" value="<?=htmlspecialchars($user->nombre ?? '')?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Apellidos</label>
            <input type="text" class="form-control" name="apellidos" id="perfil_apellidos" value="<?=htmlspecialchars($user->apellidos ?? '')?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo / Usuario</label>
            <input type="email" class="form-control" name="username" id="perfil_username" value="<?=htmlspecialchars($user->username ?? '')?>" required>
          </div>
          <hr>
          <p class="small text-muted mb-2">Dejar en blanco si no deseas cambiar la contraseña</p>
          <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" class="form-control" name="password" id="perfil_password" autocomplete="new-password">
          </div>
          <div class="mb-3">
            <label class="form-label">Confirmar contraseña</label>
            <input type="password" class="form-control" name="password_confirm" id="perfil_password_confirm" autocomplete="new-password">
          </div>
          <button type="button" class="btn btn-primary w-100" onclick="guardarMiCuenta()">
            <i class="fas fa-save"></i> Guardar cambios
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Expediente RH -->
  <div class="col-lg-8 mb-3">
    <?php if(!empty($mensaje_vinculo) && !$empleado): ?>
    <div class="alert alert-info">
      <i class="fas fa-info-circle"></i> <?=htmlspecialchars($mensaje_vinculo)?>
    </div>
    <?php endif; ?>

    <?php if($empleado): ?>
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="fas fa-id-card"></i> Expediente de Empleado</h5>
        <a href="<?=base_url('rh/RecursosHumanos')?>" class="btn btn-sm btn-outline-primary">Ver en RH</a>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><strong>No. Empleado:</strong> <?=htmlspecialchars($empleado->numero_empleado)?></li>
              <li class="mb-2"><strong>Puesto:</strong> <?=htmlspecialchars($empleado->puesto ?? '—')?></li>
              <li class="mb-2"><strong>Departamento:</strong> <?=htmlspecialchars($empleado->departamento_nombre ?: 'Sin departamento')?></li>
              <li class="mb-2"><strong>Ingreso:</strong> <?=$this->init_controller->date_to_string($empleado->fecha_ingreso)?></li>
            </ul>
          </div>
          <div class="col-md-6">
            <ul class="list-unstyled mb-0">
              <li class="mb-2"><strong>RFC:</strong> <?=htmlspecialchars($empleado->rfc ?? '—')?></li>
              <li class="mb-2"><strong>Ubicación:</strong> <?=htmlspecialchars(trim(($empleado->ciudad ?? '') . ', ' . ($empleado->estado ?? ''), ', '))?></li>
              <li class="mb-2"><strong>Correo de contacto:</strong> <?=htmlspecialchars($empleado->email_personal ?: '—')?></li>
              <li class="mb-2"><strong>Correo institucional:</strong> <?=htmlspecialchars($empleado->email_corporativo ?: '—')?></li>
              <li class="mb-2"><strong>Teléfono de contacto:</strong> <?=htmlspecialchars($empleado->telefono ?? '—')?></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Vacaciones -->
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0"><i class="fas fa-umbrella-beach"></i> Vacaciones</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalSolicitarVacaciones">
          <i class="fas fa-plus"></i> Solicitar vacaciones
        </button>
      </div>
      <div class="card-body">
        <?php if($vacaciones): ?>
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="card bg-success text-white mb-2 mb-md-0">
              <div class="card-body text-center py-3">
                <h3 class="mb-0"><?=(int)$vacaciones->dias_disponibles?></h3>
                <small>Días disponibles</small>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card bg-light mb-2 mb-md-0">
              <div class="card-body text-center py-3">
                <h3 class="mb-0"><?=(int)$vacaciones->dias_tomados?></h3>
                <small>Días tomados</small>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card bg-info text-white">
              <div class="card-body text-center py-3">
                <h3 class="mb-0"><?=(int)$vacaciones->dias_totales?></h3>
                <small>Total período <?=date('Y', strtotime($vacaciones->periodo_inicio))?></small>
              </div>
            </div>
          </div>
        </div>
        <?php else: ?>
        <p class="text-muted mb-3">No hay período vacacional activo. Al enviar una solicitud se intentará generar el período según tu antigüedad.</p>
        <?php endif; ?>

        <h6 class="text-muted text-uppercase small">Mis solicitudes</h6>
        <?php if(!empty($solicitudes)): ?>
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>Fechas</th>
                <th>Días</th>
                <th>Estatus</th>
                <th>Solicitud</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($solicitudes as $s):
                $badge = 'secondary';
                if ($s->estatus === 'Aprobada') $badge = 'success';
                elseif ($s->estatus === 'Rechazada') $badge = 'danger';
                elseif ($s->estatus === 'Pendiente') $badge = 'warning';
              ?>
              <tr>
                <td><?=date('d/m/Y', strtotime($s->fecha_inicio))?> — <?=date('d/m/Y', strtotime($s->fecha_fin))?></td>
                <td><?=(int)$s->dias_solicitados?></td>
                <td><span class="badge bg-<?=$badge?>"><?=htmlspecialchars($s->estatus)?></span></td>
                <td class="text-muted small"><?=date('d/m/Y H:i', strtotime($s->fecha_solicitud))?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
        <p class="text-muted mb-0">Aún no has registrado solicitudes de vacaciones.</p>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal Solicitar Vacaciones -->
<div class="modal fade" id="modalSolicitarVacaciones" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-white"><i class="fas fa-umbrella-beach"></i> Solicitar vacaciones</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formSolicitarVacaciones">
          <div class="mb-3">
            <label class="form-label">Fecha inicio</label>
            <input type="date" class="form-control" name="fecha_inicio" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha fin</label>
            <input type="date" class="form-control" name="fecha_fin" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" rows="3" placeholder="Opcional"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="enviarSolicitudVacaciones()">Enviar solicitud</button>
      </div>
    </div>
  </div>
</div>

<script>
function guardarMiCuenta() {
  var data = $('#formMiCuenta').serialize();
  data += '&<?=$this->security->get_csrf_token_name()?>=<?=$this->security->get_csrf_hash()?>';

  $.post('<?=base_url('usuarios/Perfil/actualizar_cuenta')?>', data, function(res) {
    res = typeof res === 'string' ? JSON.parse(res) : res;
    var ok = res.success === true || res.success === 1;
    var msg = res.message || res.msg || (ok ? 'Guardado' : 'Error');
    if (typeof notifyShow === 'function') {
      notifyShow(msg, ok ? 'success' : 'danger');
    } else {
      alert(msg);
    }
    if (ok) {
      $('#perfil_password, #perfil_password_confirm').val('');
    }
  });
}

function enviarSolicitudVacaciones() {
  var data = $('#formSolicitarVacaciones').serialize();
  data += '&<?=$this->security->get_csrf_token_name()?>=<?=$this->security->get_csrf_hash()?>';

  $.post('<?=base_url('usuarios/Perfil/solicitar_vacaciones')?>', data, function(res) {
    res = typeof res === 'string' ? JSON.parse(res) : res;
    if (res.success) {
      if (typeof notifyShow === 'function') notifyShow(res.message || 'Solicitud enviada', 'success');
      else alert('Solicitud enviada');
      location.reload();
    } else {
      var msg = res.message || 'No se pudo enviar la solicitud';
      if (typeof notifyShow === 'function') notifyShow(msg, 'danger');
      else alert(msg);
    }
  });
}
</script>
