<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$empleado = $response['empleado'] ?? null;
$resumen = $response['resumen'] ?? [];
$contactos = $response['contactos'] ?? [];
$tablas_listas = !empty($response['tablas_listas']);
$nombreEmp = $empleado ? trim($empleado->nombre . ' ' . $empleado->apellido_paterno) : '';
?>

<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

  <div class="row mb-3">
    <div class="col">
      <h3 class="mb-0"><strong>Comunicación Interna</strong></h3>
      <p class="text-muted mb-0">Mensajes y tareas con tu equipo · <?= htmlspecialchars($nombreEmp) ?></p>
    </div>
  </div>

  <?php if(!$tablas_listas): ?>
  <div class="alert alert-warning">
    <i class="fas fa-database"></i>
    Las tablas de comunicación aún no están instaladas. Ejecuta <code>database/rh_comunicacion_interna.sql</code> en la base de datos.
  </div>
  <?php endif; ?>

  <?php if(empty($contactos)): ?>
  <div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    No hay otros empleados activos disponibles para mensajes o tareas. Verifica que existan compañeros con estatus activo en RH.
  </div>
  <?php endif; ?>

  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <div class="text-muted small text-uppercase">Mensajes sin leer</div>
          <div class="fs-2 fw-bold text-primary" id="statMensajesNoLeidos"><?=(int)($resumen['mensajes_no_leidos'] ?? 0)?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <div class="text-muted small text-uppercase">Tareas pendientes</div>
          <div class="fs-2 fw-bold text-warning" id="statTareasPendientes"><?=(int)($resumen['tareas_pendientes'] ?? 0)?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center">
          <div class="text-muted small text-uppercase">Tareas en proceso</div>
          <div class="fs-2 fw-bold text-info" id="statTareasProceso"><?=(int)($resumen['tareas_en_proceso'] ?? 0)?></div>
        </div>
      </div>
    </div>
  </div>

  <ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item">
      <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabMensajes" type="button">
        <i class="fas fa-envelope"></i> Mensajes
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabTareas" type="button">
        <i class="fas fa-tasks"></i> Tareas
      </button>
    </li>
  </ul>

  <div class="tab-content">
    <!-- MENSAJES -->
    <div class="tab-pane fade show active" id="tabMensajes">
      <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary active" id="btnBandejaRecibidos" onclick="cambiarBandejaMensajes('recibidos')">Recibidos</button>
            <button type="button" class="btn btn-outline-primary" id="btnBandejaEnviados" onclick="cambiarBandejaMensajes('enviados')">Enviados</button>
          </div>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoMensaje" <?= $tablas_listas ? '' : 'disabled' ?>>
            <i class="fas fa-paper-plane"></i> Nuevo mensaje
          </button>
        </div>
        <div class="card-body p-0">
          <div id="listaMensajes" class="list-group list-group-flush">
            <div class="list-group-item text-center text-muted py-4">Cargando mensajes...</div>
          </div>
        </div>
      </div>
    </div>

    <!-- TAREAS -->
    <div class="tab-pane fade" id="tabTareas">
      <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-success active" id="btnTareasAsignadas" onclick="cambiarVistaTareas('asignadas')">Asignadas a mí</button>
            <button type="button" class="btn btn-outline-success" id="btnTareasEnviadas" onclick="cambiarVistaTareas('enviadas')">Que asigné</button>
          </div>
          <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaTarea" <?= $tablas_listas ? '' : 'disabled' ?>>
            <i class="fas fa-plus"></i> Nueva tarea
          </button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Tarea</th>
                  <th>Con</th>
                  <th>Estatus</th>
                  <th>Límite</th>
                  <th style="width:140px;">Acción</th>
                </tr>
              </thead>
              <tbody id="listaTareas">
                <tr><td colspan="5" class="text-center text-muted py-4">Cargando tareas...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Mensaje -->
<div class="modal fade" id="modalNuevoMensaje" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-white"><i class="fas fa-envelope"></i> Nuevo mensaje</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formNuevoMensaje">
          <div class="mb-3">
            <label class="form-label">Para</label>
            <select class="form-select" name="para_empleado_id" required>
              <option value="">— Selecciona un compañero —</option>
              <?php foreach($contactos as $c): ?>
              <option value="<?=(int)$c->id?>">
                <?= htmlspecialchars($c->nombre_completo) ?> — <?= htmlspecialchars($c->puesto ?? '') ?>
                <?php if(!empty($c->es_mi_jefe)): ?> (Jefe directo)<?php endif; ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Mensaje</label>
            <textarea class="form-control" name="mensaje" rows="4" maxlength="2000" required placeholder="Escribe tu mensaje..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="enviarMensaje()">Enviar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tarea -->
<div class="modal fade" id="modalNuevaTarea" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title text-white"><i class="fas fa-tasks"></i> Nueva tarea</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formNuevaTarea">
          <div class="mb-3">
            <label class="form-label">Asignar a</label>
            <select class="form-select" name="para_empleado_id" required>
              <option value="">— Selecciona un compañero —</option>
              <?php foreach($contactos as $c): ?>
              <option value="<?=(int)$c->id?>"><?= htmlspecialchars($c->nombre_completo) ?> — <?= htmlspecialchars($c->puesto ?? '') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" class="form-control" name="titulo" maxlength="200" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="3" placeholder="Opcional"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Fecha límite</label>
            <input type="date" class="form-control" name="fecha_limite">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="crearTarea()">Asignar tarea</button>
      </div>
    </div>
  </div>
</div>
