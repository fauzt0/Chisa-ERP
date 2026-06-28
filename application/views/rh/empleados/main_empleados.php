<?php
/**
 * Vista principal de Recursos Humanos
 * Listado de empleados con DataTables y estadísticas
 */
?>
<div class="container-fluid p-0">

  <!-- Breadcrumb (Migas de pan) -->
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
   
  <!-- Titulo de la pagina -->
  <h1 class="h3 mb-3"><?php echo $headTitle;?></h1>

  <?php if (empty($response['vinculo_usuarios_habilitado'])): ?>
  <div class="alert alert-secondary border mb-3 py-2 small">
    <i class="fas fa-info-circle"></i> Para vincular usuarios ERP con empleados, ejecute la migración
    <code>database/empleado_usuario_vinculo.sql</code>.
  </div>
  <?php endif; ?>

  <!-- Alerta de Datos Faltantes (Solo Visible para Administradores con Permiso) -->
  <?php if(!empty($response['datos_faltantes'])): 
    $total_faltantes = count($response['datos_faltantes']);
  ?>
  <div class="alert alert-warning border-start border-warning border-4 px-3 py-2 mb-3" role="alert">
    <div class="d-flex align-items-center gap-3">
      <div class="flex-shrink-0">
        <i class="fas fa-exclamation-triangle text-warning" style="font-size:1.3rem;"></i>
      </div>
      <div class="flex-grow-1">
        <strong><i class="fas fa-bell me-1"></i> Datos Incompletos:</strong> 
        <strong class="text-danger"><?php echo $total_faltantes; ?></strong> empleado<?php echo $total_faltantes > 1 ? 's' : ''; ?> con información fiscal faltante.
        <div class="mt-1 d-flex flex-wrap gap-1">
          <?php foreach(array_slice($response['datos_faltantes'], 0, 5) as $emp): 
            $faltantes_str = implode(', ', $emp['faltantes']);
          ?>
            <a href="<?php echo base_url('rh/RecursosHumanos/editar/'.$emp['id']); ?>" 
               class="btn btn-sm btn-outline-danger px-2 py-0" 
               style="font-size:0.72rem;"
               title="Faltan: <?php echo $faltantes_str; ?>">
              <?php echo $emp['nombre']; ?> 
              <span class="badge bg-warning text-dark ms-1"><?php echo $emp['total_faltantes']; ?></span>
            </a>
          <?php endforeach; ?>
          <?php if($total_faltantes > 5): ?>
            <span class="small text-muted align-self-center ms-1">+<?php echo $total_faltantes - 5; ?> más</span>
          <?php endif; ?>
        </div>
      </div>
      <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
  <?php endif; ?>

  <!-- Alerta de Expedientes Incompletos -->
  <?php if(!empty($response['total_expedientes_incompletos']) && $response['total_expedientes_incompletos'] > 0): ?>
  <div class="alert alert-danger border-start border-danger border-4 px-3 py-2 mb-3" role="alert">
    <div class="d-flex align-items-center gap-3">
      <div class="flex-shrink-0">
        <i class="fas fa-folder-open text-danger" style="font-size:1.3rem;"></i>
      </div>
      <div class="flex-grow-1">
        <strong><i class="fas fa-file-alt me-1"></i> Expedientes Incompletos:</strong>
        <strong><?php echo (int)$response['total_expedientes_incompletos']; ?></strong> empleado(s) sin documentación requerida (acta, CURP, RFC, NSS, INE, comprobante domicilio).
        <div class="mt-1 d-flex flex-wrap gap-1">
          <?php foreach(($response['expedientes_incompletos'] ?? []) as $exp): ?>
            <a href="<?php echo base_url('rh/RecursosHumanos/editar/'.$exp['id'].'#documentos'); ?>"
               class="btn btn-sm btn-outline-danger px-2 py-0" style="font-size:0.72rem;"
               title="Faltan: <?php echo htmlspecialchars(implode(', ', $exp['faltantes'])); ?>">
              <?php echo htmlspecialchars($exp['nombre']); ?>
              <span class="badge bg-light text-danger ms-1"><?php echo (int)$exp['total_faltantes']; ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="alert"></button>
    </div>
  </div>
  <?php endif; ?>

  <!-- Alerta de Vacaciones Pendientes -->
  <?php if(!empty($response['vacaciones_pendientes']) && $response['vacaciones_pendientes'] > 0): ?>
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    <div class="alert-icon">
      <i class="fas fa-umbrella-beach"></i>
    </div>
    <div class="alert-message">
      <strong><i class="fas fa-clock"></i> Solicitudes Pendientes:</strong> Hay <strong><?php echo $response['vacaciones_pendientes']; ?></strong> solicitudes de vacaciones esperando aprobación.
      <div class="mt-1">
          <button class="btn btn-sm btn-light" onclick="abrirTodasSolicitudes()">Revisar Solicitudes</button>
      </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  <?php endif; ?>

  <?php if (!empty($response['vinculo_usuarios_habilitado']) && !empty($response['usuarios_sin_empleado'])): ?>
  <div class="alert alert-primary border-start border-primary border-4 px-3 py-2 mb-3" role="alert">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <strong><i class="fas fa-user-lock me-1"></i> Usuarios ERP sin expediente:</strong>
        Hay <strong><?= (int)$response['usuarios_sin_empleado'] ?></strong> usuario(s) del sistema sin empleado vinculado.
      </div>
      <button type="button" class="btn btn-sm btn-primary" onclick="abrirModalUsuariosSinEmpleado()">
        <i class="fas fa-link"></i> Vincular / Crear empleados
      </button>
    </div>
  </div>
  <?php endif; ?>

  <!-- Cards de estadísticas RH -->
  <div class="row">
    <!-- Total Empleados -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Total Empleados</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['total_empleados']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <span class="badge bg-primary"><?php echo $response['stats']['porcentaje_activos']; ?>%</span>
            </div>
          </div>

          <div class="progress progress-sm shadow-sm mb-1">
            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $response['stats']['porcentaje_activos']; ?>%"></div>
          </div>
          <small class="text-muted">Activos: <?php echo $response['stats']['empleados_activos']; ?> | Inactivos: <?php echo $response['stats']['empleados_inactivos']; ?></small>
        </div>
      </div>
    </div>

    <!-- Nuevos Ingresos (30 días) -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Nuevos (30d)</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['nuevos_ingresos']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-user-plus text-success" style="font-size: 1.5rem;"></i>
            </div>
          </div>

          <div class="progress progress-sm shadow-sm mb-1">
            <div class="progress-bar bg-success" role="progressbar" style="width: 60%"></div>
          </div>
          <small class="text-muted">Nuevos ingresos últimos 30 días</small>
        </div>
      </div>
    </div>

    <!-- Nómina Total Mensual -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Nómina Mensual</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-12">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                $<?php echo number_format($response['stats']['nomina_total'], 2); ?>
              </h3>
            </div>
          </div>

          <div class="progress progress-sm shadow-sm mb-1">
            <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
          </div>
          <small class="text-muted">Salarios base mensuales</small>
        </div>
      </div>
    </div>

    <!-- Por Tipo de Trabajador -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Por Tipo</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row">
            <?php if(!empty($response['stats']['por_tipo'])): ?>
              <?php foreach($response['stats']['por_tipo'] as $tipo): ?>
                <div class="col-12">
                  <small><strong><?php echo $tipo->tipo_trabajador; ?>:</strong> <?php echo $tipo->total; ?></small>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <small class="text-muted">Sin datos</small>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Columna principal de datos -->
    <div class="col-xl-12">
      <div class="card">   
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-6 mb-2 mb-md-0">
              <div class="input-group input-group-search">
                <input type="text" class="form-control" id="datatables-empleados-search" placeholder="Buscar empleados…">
                <button class="btn" type="button" id="btn-filter">
                  <i class="align-middle" data-lucide="search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6">
              <div class="d-flex flex-wrap gap-2 justify-content-md-end">              
                <div class="dropdown position-relative d-inline-block">
                  <a href="#" data-bs-toggle="dropdown" data-bs-display="static" class="btn btn-light btn-lg">
                    <i data-lucide="download"></i> Exportar
                  </a>
                  <div class="dropdown-menu dropdown-menu-end" id="table_menu_actions">                    
                  </div>
                </div>                
                <a href="<?php echo base_url('rh/RecursosHumanos/alta'); ?>" class="btn btn-primary btn-lg"><i data-lucide="plus"></i> Alta Empleado</a>
                <a href="<?php echo base_url('rh/Nomina'); ?>" class="btn btn-success btn-lg"><i data-lucide="banknote"></i> Nómina</a>
                <a href="<?php echo base_url('rh/RecursosHumanos/plantillas'); ?>" class="btn btn-info btn-lg"><i class="fas fa-file-contract"></i> Plantillas Contratos</a>                
                <button onclick="abrirTodasSolicitudes()" class="btn btn-warning btn-lg">
                  <i class="fas fa-umbrella-beach"></i> Vacaciones
                </button>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3">
              <label for="filter-estatus" class="form-label">Estatus:</label>
              <select class="form-select" id="filter-estatus">
                <option value="all">Todos</option>
                <option value="1" selected>Activos</option>
                <option value="0">Inactivos</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="filter-departamento" class="form-label">Departamento:</label>
              <select class="form-select" id="filter-departamento">
                <option value="all">Todos</option>
                <?php foreach($response['departamentos'] as $dept): ?>
                  <option value="<?php echo $dept->id; ?>"><?php echo $dept->nombre; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Mostrar filas:</label>
              <div id="datatables-length-container"></div>
            </div>
          </div>
          <table id="datatables-empleados" class="table w-100 table-hover table-striped">
            <thead>
              <tr>
                <th class="text-start">#</th>
                <th>Nombre</th>
                <th>Puesto</th>
                <th>Departamento</th>
                <th>Estatus</th>                
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>              
            </tbody>
            <tfoot>
              <tr>
                <th class="text-start">#</th>
                <th>Nombre</th>
                <th>Puesto</th>
                <th>Departamento</th>
                <th>Estatus</th>                
                <th>Acciones</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- Offcanvas Detalle Empleado -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasDetalleEmpleado" aria-labelledby="offcanvasDetalleEmpleadoLabel" style="width: 520px !important;">
      <div class="offcanvas-header border-bottom" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);">
        <div>
          <h5 id="offcanvasDetalleEmpleadoLabel" class="mb-1 fw-bold text-white" style="font-size:1rem;"><i class="fas fa-id-card me-2 text-white"></i><span id="offcanvas-empleado-nombre" class="text-white">Datos del Empleado</span></h5>
          <small class="text-white-50" id="offcanvas-empleado-numero"></small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <!-- Tabs de navegación -->
      <ul class="nav nav-tabs nav-fill px-2 pt-2" id="offcanvasTabs" role="tablist" style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-personal-btn" data-bs-toggle="tab" data-bs-target="#tab-personal" type="button" role="tab">
            <i data-lucide="user" class="d-block mx-auto mb-1" style="width:18px;height:18px;"></i>
            <small>Personal</small>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-fiscal-btn" data-bs-toggle="tab" data-bs-target="#tab-fiscal" type="button" role="tab">
            <i data-lucide="file-text" class="d-block mx-auto mb-1" style="width:18px;height:18px;"></i>
            <small>Fiscal</small>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-laboral-btn" data-bs-toggle="tab" data-bs-target="#tab-laboral" type="button" role="tab">
            <i data-lucide="briefcase" class="d-block mx-auto mb-1" style="width:18px;height:18px;"></i>
            <small>Laboral</small>
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-documentos-btn" data-bs-toggle="tab" data-bs-target="#tab-documentos" type="button" role="tab">
            <i data-lucide="folder" class="d-block mx-auto mb-1" style="width:18px;height:18px;"></i>
            <small>Docs</small>
          </button>
        </li>
      </ul>
      <div class="offcanvas-body pt-3">
        <!-- Acciones rápidas -->
        <div id="offcanvas-actions" class="d-flex gap-2 flex-wrap mb-3">
        </div>

        <!-- Contenido de tabs -->
        <div class="tab-content" id="offcanvasTabsContent">
          <div class="tab-pane fade show active" id="tab-personal" role="tabpanel">
            <div class="list-group list-group-flush" id="tab-personal-content"></div>
          </div>
          <div class="tab-pane fade" id="tab-fiscal" role="tabpanel">
            <div class="list-group list-group-flush" id="tab-fiscal-content"></div>
          </div>
          <div class="tab-pane fade" id="tab-laboral" role="tabpanel">
            <div class="list-group list-group-flush" id="tab-laboral-content"></div>
          </div>
          <div class="tab-pane fade" id="tab-documentos" role="tabpanel">
            <div id="checklist-documentos-offcanvas" class="mb-2"></div>
            <div class="list-group list-group-flush mb-2" id="tab-documentos-content"></div>
            <div id="lista-documentos-empleado" class="mb-2"></div>
            <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="abrirModalSubirDocumento()">
              <i data-lucide="upload" style="width:14px;height:14px;"></i> Adjuntar Documento
            </button>
          </div>
        </div>

          <!-- Balance de Vacaciones -->
          <div id="vacaciones-badge" style="display:none;" class="alert alert-info mt-3 px-4 py-3">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2 me-3">
              <strong class="text-primary text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">🏖️ Vacaciones</strong>
              <button class="btn btn-sm btn-primary py-1" onclick="verVacaciones()" id="btn-ver-vacaciones">
                <i class="fas fa-calendar-alt"></i> Ver Detalle
              </button>
            </div>
            <div class="text-center py-1">
              <h2 class="mb-0 fw-bold" id="dias-disponibles">-- días</h2>
              <small class="text-muted d-block" id="periodo-vacaciones">Cargando...</small>
            </div>
          </div>

          <!-- Balance de Incidencias -->
          <div id="incidencias-badge" style="display:none;" class="alert alert-warning mt-3 px-4 py-3">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
              <strong class="text-warning text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">⚠️ Incidencias</strong>
              <button class="btn btn-sm btn-warning py-1" onclick="verIncidencias()" id="btn-ver-incidencias">
                <i class="fas fa-exclamation-triangle"></i> Ver Incidencias
              </button>
            </div>
            <div class="text-center py-1">
              <h2 class="mb-0 fw-bold" id="total-incidencias">0</h2>
              <small class="text-muted d-block">Incidencias este año</small>
            </div>
          </div>

          <!-- Horario Laboral -->
          <div id="horario-badge" style="display:none;" class="alert alert-info mt-3 px-4 py-3">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-2">
              <strong class="text-info text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.5px;">🕐 Horario Laboral</strong>
              <button class="btn btn-sm btn-info py-1" onclick="verHorario()" id="btn-ver-horario">
                <i class="fas fa-clock"></i> Ver/Editar Horario
              </button>
            </div>
            <div class="text-center py-1">
              <h2 class="mb-0 fw-bold" id="horas-semana">0 hrs</h2>
              <small class="text-muted d-block" id="turno-empleado">Sin horario</small>
            </div>
          </div>

          <?php if (!empty($response['puede_ver_reloj'])): ?>
          <!-- Asistencias Reloj Checador -->
          <div id="reloj-badge" class="mt-3 rounded-3 p-0 overflow-hidden" style="border: 1px solid #86efac;">
            <div class="px-3 py-2 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);">
              <span class="text-white fw-semibold" style="font-size: 0.82rem; letter-spacing: 0.4px;">
                <i class="fas fa-fingerprint me-1"></i> RELOJ CHECADOR
              </span>
              <button class="btn btn-light btn-sm py-0 px-2" onclick="verAsistenciasReloj()" id="btn-ver-reloj" style="font-size:0.78rem;">
                <i class="fas fa-table me-1"></i>Ver registros
              </button>
            </div>
            <div class="px-3 py-3 text-center" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
              <div class="fw-bold text-success fs-5 mb-0" id="reloj-ultima-checada">—</div>
              <small class="text-muted" id="reloj-resumen-mes">Selecciona un empleado</small>
            </div>
          </div>
          <?php endif; ?>

          <!-- Historial de Contratos -->
          <div class="mt-4">
            <h6 class="border-bottom pb-2">Historial de Contratos</h6>
            
            <div class="row g-2 mb-2">
              <div class="col-5">
                <input type="date" class="form-control form-control-sm" id="historial-desde" placeholder="Desde">
              </div>
              <div class="col-5">
                <input type="date" class="form-control form-control-sm" id="historial-hasta" placeholder="Hasta">
              </div>
              <div class="col-2">
                <button class="btn btn-sm btn-outline-primary w-100" onclick="filtrarHistorial()"><i class="fas fa-search"></i></button>
              </div>
            </div>

            <ul class="timeline mt-2 mb-0" id="historial-contratos">
              <li class="text-muted">Selecciona un empleado para ver su historial</li>
            </ul>
          </div>
      </div>
    </div>
  </div>
</div>

<?php $this->load->view('rh/partials/modal_styles'); ?>

<!-- Modal para ver Contrato -->
<div class="modal fade rh-modal" id="modalContrato" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-white"><i class="fas fa-file-contract"></i> Contrato de Trabajo</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" style="background: #f5f5f5;">
        <div id="contrato-content" style="min-height: 400px;">
          <div class="text-center text-muted p-5">
            <p>Cargando contrato...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times"></i> Cerrar
        </button>
        <button type="button" class="btn btn-primary" onclick="descargarPDF()">
          <i class="fas fa-file-pdf"></i> Descargar PDF
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Vacaciones: Detalle y Balance -->
<div class="modal fade rh-modal" id="modalVacaciones" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title text-white"><i class="fas fa-umbrella-beach"></i> Gestión de Vacaciones</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Balance Actual -->
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-calendar-check"></i> Período Actual</h6>
          </div>
          <div class="card-body">
            <div class="row" id="balance-actual">
              <div class="col-md-12 text-center text-muted">
                <p>Cargando información...</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Solicitudes -->
        <div class="card">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-list"></i> Historial de Solicitudes</h6>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm" id="tabla-solicitudes">
                <thead>
                  <tr>
                    <th>Fecha Solicitud</th>
                    <th>Período</th>
                    <th>Días</th>
                    <th>Estatus</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody id="solicitudes-body">
                  <tr><td colspan="5" class="text-center text-muted">Sin solicitudes</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" onclick="abrirSolicitudVacaciones()">
          <i class="fas fa-plus"></i> Nueva Solicitud
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Solicitar Vacaciones -->
<div class="modal fade rh-modal" id="modalSolicitarVacaciones" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title text-white"><i class="fas fa-calendar-plus"></i> Solicitar Vacaciones</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formSolicitarVacaciones">
          <input type="hidden" id="empleado_id_vacaciones" name="empleado_id">
          
          <div class="mb-3">
            <label class="form-label">Fecha Inicio *</label>
            <input type="date" class="form-control" id="fecha_inicio_vac" name="fecha_inicio" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Fecha Fin *</label>
            <input type="date" class="form-control" id="fecha_fin_vac" name="fecha_fin" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Días Solicitados</label>
            <input type="number" class="form-control" id="dias_calculados" readonly>
            <small class="text-muted">Se calculan automáticamente (días hábiles)</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" rows="3"></textarea>
          </div>

          <div class="alert alert-warning" id="dias-disponibles-alert">
            <small><strong>Días disponibles:</strong> <span id="dias-disp-solicitud">--</span></small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="enviarSolicitudVacaciones()">
          <i class="fas fa-paper-plane"></i> Enviar Solicitud
        </button>
      </div>
    </div>
  </div>
</div><!-- Modal: Todas las Solicitudes (Admin) -->
<div class="modal fade rh-modal" id="modalTodasSolicitudes" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title text-dark"><i class="fas fa-tasks"></i> Solicitudes de Vacaciones Pendientes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Tabs de navegación -->
        <ul class="nav nav-tabs mb-3">
          <li class="nav-item">
            <button class="nav-link nav-link-solicitudes active" id="tab-sol-Pendiente" onclick="cargarTodasSolicitudes('Pendiente')">
              <i class="fas fa-clock"></i> Pendientes
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link nav-link-solicitudes" id="tab-sol-Aprobada" onclick="cargarTodasSolicitudes('Aprobada')">
              <i class="fas fa-check"></i> Aprobadas
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link nav-link-solicitudes" id="tab-sol-Rechazada" onclick="cargarTodasSolicitudes('Rechazada')">
              <i class="fas fa-times"></i> Rechazadas
            </button>
          </li>
          <li class="nav-item">
            <button class="nav-link nav-link-solicitudes" id="tab-sol-Todas" onclick="cargarTodasSolicitudes('Todas')">
              <i class="fas fa-list"></i> Todas
            </button>
          </li>
        </ul>

        <div class="table-responsive">
          <table class="table table-hover" id="tabla-todas-solicitudes">
            <thead>
              <tr>
                <th>Empleado</th>
                <th>Fecha Solicitud</th>
                <th>Período Solicitado</th>
                <th>Días</th>
                <th>Observaciones</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="todas-solicitudes-body">
              <tr><td colspan="6" class="text-center text-muted">Cargando solicitudes...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Ver Incidencias -->
<div class="modal fade rh-modal" id="modalIncidencias" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title text-dark"><i class="fas fa-exclamation-triangle"></i> Incidencias del Empleado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="card bg-light">
              <div class="card-body text-center">
                <h3 id="stat-total-incidencias">0</h3>
                <small class="text-muted">Total Incidencias</small>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card bg-light">
              <div class="card-body text-center">
                <h3 id="stat-descuentos">$0.00</h3>
                <small class="text-muted">Total Descuentos</small>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <button class="btn btn-success w-100 h-100" onclick="abrirRegistroIncidencia()">
              <i class="fas fa-plus"></i> Nueva Incidencia
            </button>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-md-4">
            <select class="form-select" id="filtro-tipo-incidencia">
              <option value="">Todos los tipos</option>
              <option value="Retardo">Retardo</option>
              <option value="Falta">Falta</option>
              <option value="Falta Justificada">Falta Justificada</option>
              <option value="Permiso">Permiso</option>
              <option value="Incapacidad">Incapacidad</option>
              <option value="Suspensión">Suspensión</option>
              <option value="Amonestación">Amonestación</option>
              <option value="Renuncia">Renuncia</option>
              <option value="Otro">Otro</option>
            </select>
          </div>
          <div class="col-md-3">
            <input type="date" class="form-control" id="filtro-fecha-desde">
          </div>
          <div class="col-md-3">
            <input type="date" class="form-control" id="filtro-fecha-hasta">
          </div>
          <div class="col-md-2">
            <button class="btn btn-primary w-100" onclick="aplicarFiltrosIncidencias()">
              <i class="fas fa-filter"></i> Filtrar
            </button>
          </div>
        </div>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Descuento</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="incidencias-body">
              <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Registrar Incidencia -->
<div class="modal fade rh-modal" id="modalRegistrarIncidencia" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title text-white"><i class="fas fa-plus"></i> Registrar Nueva Incidencia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formRegistrarIncidencia">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Tipo de Incidencia *</label>
              <select class="form-select" name="tipo_incidencia" id="tipo_incidencia" required>
                <option value="">Seleccionar...</option>
                <option value="Retardo">Retardo</option>
                <option value="Falta">Falta</option>
                <option value="Falta Justificada">Falta Justificada</option>
                <option value="Permiso">Permiso</option>
                <option value="Incapacidad">Incapacidad</option>
                <option value="Horas Extras">Horas Extras</option>
                <option value="Suspensión">Suspensión</option>
                <option value="Amonestación">Amonestación</option>
                <option value="Renuncia">Renuncia</option>
                <option value="Otro">Otro</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Fecha de Incidencia *</label>
              <input type="date" class="form-control" name="fecha_incidencia" required>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3" id="hora-container" style="display:none;">
              <label class="form-label">Hora (para retardos)</label>
              <input type="time" class="form-control" name="hora_incidencia">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">¿Aplica descuento?</label>
              <select class="form-select" name="tiene_descuento" id="tiene_descuento_inc">
                <option value="0">No</option>
                <option value="1">Sí</option>
              </select>
            </div>
            <div class="col-md-6 mb-3" id="monto-descuento-container" style="display:none;">
              <label class="form-label">Monto de Descuento</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" class="form-control" name="monto_descuento" step="0.01">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Evidencia (PDF, Imagen) - Opcional</label>
            <input type="file" class="form-control" name="archivo_evidencia" accept="image/*,.pdf">
            <small class="text-muted">Máx. 5MB</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="guardarIncidencia()">
          <i class="fas fa-save"></i> Guardar Incidencia
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Subir Documento del Empleado -->
<div class="modal fade rh-modal" id="modalSubirDocumento" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3a5f, #2d5a8e);">
        <h5 class="modal-title text-white"><i data-lucide="upload" style="width:20px;height:20px;"></i> Adjuntar Documento</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formSubirDocumento" enctype="multipart/form-data">
          <input type="hidden" name="empleado_id" id="doc_empleado_id">
          <div class="mb-3">
            <label class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
            <select class="form-select" name="tipo_documento" id="doc_tipo" required>
              <option value="">Seleccionar...</option>
              <option value="acta_nacimiento">Acta de Nacimiento</option>
              <option value="curp">CURP</option>
              <option value="rfc">Constancia RFC</option>
              <option value="nss">IMSS / Seguro Social</option>
              <option value="ine">INE / Identificación</option>
              <option value="comprobante_domicilio">Comprobante de Domicilio</option>
              <option value="comprobante_estudios">Comprobante de Estudios</option>
              <option value="carta_recomendacion">Carta de Recomendación</option>
              <option value="constancia_fiscal">Constancia de Situación Fiscal</option>
              <option value="cuenta_bancaria">Estado de Cuenta / CLABE</option>
              <option value="contrato_firmado">Contrato Firmado</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Archivo (PDF o imagen) <span class="text-danger">*</span></label>
            <input type="file" class="form-control" name="archivo" accept=".pdf,.jpg,.jpeg,.png,.gif,.webp" required>
            <small class="text-muted">Máximo 10 MB</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea class="form-control" name="observaciones" rows="2" placeholder="Notas opcionales..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="subirDocumentoEmpleado()">
          <i data-lucide="upload" style="width:16px;height:16px;"></i> Subir
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Calculadora de Finiquito/Liquidación -->
<div class="modal fade rh-modal" id="modalCalculadoraBaja" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header text-white pb-2" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);">
        <div>
          <h5 class="modal-title mb-1 fw-bold text-white"><i class="fas fa-calculator me-2"></i>Simulador de referencia — Finiquito / Liquidación</h5>
          <p class="mb-0 small opacity-75">Solo estimación orientativa. El ERP no genera finiquitos oficiales; cargue el documento firmado en el expediente.</p>
          <small class="text-white-50">Estimación basada en la Ley Federal del Trabajo</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body px-4 py-4">
        <div class="alert alert-warning py-2 px-3 small mb-4">
          <i class="fas fa-exclamation-triangle me-1"></i> <strong>Nota Legal:</strong> Este cálculo es una <strong>estimación</strong>. Se recomienda validación por el departamento legal o contable antes de proceder con el pago.
        </div>
        
        <div class="row g-3">
          <!-- Datos del Empleado (Readonly) -->
          <div class="col-md-6">
            <div class="card border-0 bg-light h-100">
              <div class="card-body py-3">
                <h6 class="text-uppercase text-muted mb-3" style="font-size:0.75rem;letter-spacing:0.5px;"><i class="fas fa-user me-2"></i>Datos del Empleado</h6>
                <div class="row">
                  <div class="col-5 text-muted small">Empleado:</div>
                  <div class="col-7 fw-semibold text-dark" id="calc-nombre">—</div>
                </div>
                <div class="row mt-2">
                  <div class="col-5 text-muted small">Fecha Ingreso:</div>
                  <div class="col-7 fw-semibold" id="calc-fecha-ingreso">—</div>
                </div>
                <div class="row mt-2">
                  <div class="col-5 text-muted small">Antigüedad:</div>
                  <div class="col-7 fw-semibold" id="calc-antiguedad">— años</div>
                </div>
                <div class="row mt-2 pt-2 border-top">
                  <div class="col-5 text-muted small">Salario Diario:</div>
                  <div class="col-7 fw-bold text-primary fs-5" id="calc-salario-diario">$0.00</div>
                </div>
              </div>
            </div>
            <input type="hidden" id="calc-salario-diario-val">
            <input type="hidden" id="calc-fecha-ingreso-val">
          </div>
          
          <!-- Parámetros del Cálculo -->
          <div class="col-md-6">
            <div class="card border-0 bg-light h-100">
              <div class="card-body py-3">
                <h6 class="text-uppercase text-muted mb-3" style="font-size:0.75rem;letter-spacing:0.5px;"><i class="fas fa-sliders-h me-2"></i>Parámetros de Baja</h6>
                <div class="mb-2">
                  <label class="form-label small fw-semibold mb-1">Fecha de Baja</label>
                  <input type="date" class="form-control form-control-sm" id="calc-fecha-baja" value="<?php echo date('Y-m-d'); ?>" onchange="calcularBaja()">
                </div>
                <div class="mb-2">
                  <label class="form-label small fw-semibold mb-1">Motivo de Baja</label>
                  <select class="form-select form-select-sm" id="calc-motivo" onchange="calcularBaja()">
                    <option value="renuncia">Renuncia Voluntaria (Finiquito)</option>
                    <option value="despido_justificado">Despido Justificado (Finiquito)</option>
                    <option value="despido_injustificado">Despido Injustificado (Liquidación)</option>
                  </select>
                </div>
                <div class="mb-2">
                  <label class="form-label small fw-semibold mb-1">Días de Aguinaldo: <span class="text-primary" id="calc-dias-aguinaldo-label">15</span></label>
                  <input type="range" class="form-range" id="calc-dias-aguinaldo" min="0" max="60" value="15" oninput="document.getElementById('calc-dias-aguinaldo-label').textContent=this.value;calcularBaja();">
                </div>
                <div class="mb-0">
                  <label class="form-label small fw-semibold mb-1">Vacaciones Pendientes: <span class="text-primary" id="calc-dias-vacaciones-label">0</span> días</label>
                  <input type="range" class="form-range" id="calc-dias-vacaciones" min="0" max="60" value="0" oninput="document.getElementById('calc-dias-vacaciones-label').textContent=this.value;calcularBaja();">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Resultados -->
        <div class="mt-4">
          <h6 class="text-uppercase text-muted mb-2" style="font-size:0.75rem;letter-spacing:0.5px;"><i class="fas fa-receipt me-2"></i>Desglose del Cálculo</h6>
          <div class="table-responsive rounded border">
            <table class="table table-sm mb-0">
              <thead class="table-dark text-uppercase small">
                <tr>
                  <th class="ps-3">Concepto</th>
                  <th class="text-center" style="width:40px;">Op.</th>
                  <th class="text-end pe-3" style="width:140px;">Monto</th>
                </tr>
              </thead>
              <tbody id="tabla-calculo-body">
                <tr><td colspan="3" class="text-center text-muted small py-3"><i class="fas fa-hand-pointer me-1"></i>Los parámetros se ajustan automáticamente — revise los valores arriba</td></tr>
              </tbody>
              <tfoot style="background:#f0fdf4;">
                <tr>
                  <td colspan="2" class="text-end fw-bold pe-3" style="font-size:0.9rem;">TOTAL ESTIMADO A PAGAR:</td>
                  <td class="text-end pe-3 fw-bold text-success" style="font-size:1.1rem;" id="calc-total">$0.00</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer border-top py-2">
        <small class="text-muted me-auto" style="font-size:0.7rem;">Salario mínimo 2026 zona general: $278.80</small>
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="imprimirCalculoFiniquito()">
          <i class="fas fa-print me-1"></i> Imprimir
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Ver/Editar Horario -->
<div class="modal fade rh-modal" id="modalHorario" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title text-white"><i class="fas fa-clock"></i> Horario Laboral del Empleado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="card bg-light">
              <div class="card-body text-center">
                <h3 id="resumen-horas">0 hrs</h3>
                <small class="text-muted">Horas por semana</small>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card bg-light">
              <div class="card-body text-center">
                <h3 id="resumen-dias">0</h3>
                <small class="text-muted">Días laborales</small>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <button class="btn btn-success w-100 h-100" onclick="crearHorarioEstandar()">
              <i class="fas fa-plus"></i> Horario Estándar
            </button>
          </div>
        </div>
        <form id="formHorario">
          <input type="hidden" name="empleado_id" id="horario_empleado_id">
          <div class="mb-3">
            <label class="form-label">Fecha de inicio de vigencia</label>
            <input type="date" class="form-control" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>Día</th>
                  <th width="80">Laboral</th>
                  <th>Entrada</th>
                  <th>Salida</th>
                  <th>Comida Inicio</th>
                  <th>Comida Fin</th>
                  <th>Turno</th>
                </tr>
              </thead>
              <tbody id="horarios-tabla">
              </tbody>
            </table>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarHorario()">
          <i class="fas fa-save"></i> Guardar Horario
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Vincular usuario ERP a empleado -->
<div class="modal fade rh-modal" id="modalVincularUsuario" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header rh-header-brand text-white">
        <h5 class="modal-title text-white"><i class="fas fa-user-lock me-2"></i>Vincular Usuario ERP</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="vinculo-empleado-id">
        <p class="text-muted small mb-3">Empleado: <strong id="vinculo-empleado-nombre">—</strong></p>
        <div class="input-group mb-3">
          <span class="input-group-text"><i class="fas fa-search"></i></span>
          <input type="text" class="form-control" id="vinculo-buscar-usuario" placeholder="Buscar por nombre, email o ID de usuario...">
          <button type="button" class="btn btn-outline-primary" onclick="buscarUsuariosVinculo()">Buscar</button>
        </div>
        <div id="vinculo-usuarios-lista" class="list-group list-group-flush border rounded" style="max-height:320px;overflow-y:auto;">
          <div class="list-group-item text-muted text-center py-4">Escriba para buscar usuarios disponibles</div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-danger me-auto" id="btn-desvincular-usuario" onclick="desvincularUsuarioEmpleado()" style="display:none;">
          <i class="fas fa-unlink"></i> Desvincular
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Usuarios ERP sin empleado -->
<div class="modal fade rh-modal" id="modalUsuariosSinEmpleado" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header rh-header-brand text-white">
        <h5 class="modal-title text-white"><i class="fas fa-users-cog me-2"></i>Usuarios sin expediente de empleado</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div id="lista-usuarios-sin-empleado" class="list-group list-group-flush">
          <div class="list-group-item text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
        </div>
      </div>
      <div class="modal-footer">
        <small class="text-muted me-auto">Se creará un expediente básico con RFC/CURP pendientes de completar.</small>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($response['puede_ver_reloj'])): ?>
<!-- Modal: Asistencias Reloj Checador -->
<div class="modal fade rh-modal" id="modalAsistenciasReloj" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">

      <!-- Header verde degradado -->
      <div class="modal-header border-0 text-white pb-2" style="background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);">
        <div>
          <h5 class="modal-title mb-0 text-white">
            <i class="fas fa-fingerprint me-2"></i>Registros del Reloj Checador
          </h5>
          <small class="opacity-75" id="reloj-modal-periodo">—</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <!-- Sub-barra: empleado + periodo + stats -->
      <div class="px-3 py-2 border-bottom" style="background:#f8fdf9;">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <span class="fw-semibold text-success" id="reloj-modal-empleado">—</span>
          </div>
          <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge rounded-pill" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;" id="reloj-stat-checadas">0 checadas</span>
            <span class="badge rounded-pill" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;" id="reloj-stat-dias">0 días con registro</span>
          </div>
        </div>
      </div>

      <div class="modal-body pt-3">

        <!-- Controles: periodo + fecha -->
        <div class="row g-2 mb-3 align-items-end">
          <div class="col-auto">
            <label class="form-label small text-muted mb-1">Ver por</label>
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-outline-success active" id="tab-reloj-dia" onclick="cambiarModoReloj('dia')">
                <i class="fas fa-calendar-day me-1"></i>Día
              </button>
              <button type="button" class="btn btn-outline-success" id="tab-reloj-semana" onclick="cambiarModoReloj('semana')">
                <i class="fas fa-calendar-week me-1"></i>Semana
              </button>
              <button type="button" class="btn btn-outline-success" id="tab-reloj-mes" onclick="cambiarModoReloj('mes')">
                <i class="fas fa-calendar-alt me-1"></i>Mes
              </button>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label small text-muted mb-1">Fecha de referencia</label>
            <input type="date" class="form-control form-control-sm" id="reloj-fecha-ref" value="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="col-auto">
            <button class="btn btn-success btn-sm" onclick="cargarAsistenciasReloj()">
              <i class="fas fa-search me-1"></i>Consultar
            </button>
          </div>
        </div>

        <!-- Pestañas de vista -->
        <ul class="nav nav-tabs mb-0" id="reloj-vista-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active px-3 py-2 small" id="tab-reloj-resumen-vista"
              data-bs-toggle="tab" data-bs-target="#reloj-panel-resumen" type="button" role="tab">
              <i class="fas fa-table me-1"></i>Resumen por día
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link px-3 py-2 small" id="tab-reloj-detalle-vista"
              data-bs-toggle="tab" data-bs-target="#reloj-panel-detalle" type="button" role="tab">
              <i class="fas fa-list me-1"></i>Checadas individuales
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link px-3 py-2 small" id="tab-reloj-calendario-vista"
              data-bs-toggle="tab" data-bs-target="#reloj-panel-calendario" type="button" role="tab">
              <i class="fas fa-calendar-alt me-1"></i>Calendario
            </button>
          </li>
        </ul>

        <div class="tab-content border border-top-0 rounded-bottom" id="reloj-vista-panels">
          <div class="tab-pane fade show active" id="reloj-panel-resumen" role="tabpanel">
            <div id="reloj-tabla-resumen">
              <div class="text-center text-muted py-5">
                <i class="fas fa-fingerprint fa-2x mb-2 opacity-25"></i>
                <p class="mb-0 small">Elige un periodo y presiona <strong>Consultar</strong></p>
              </div>
            </div>
          </div>
          <div class="tab-pane fade" id="reloj-panel-detalle" role="tabpanel">
            <div id="reloj-tabla-detalle">
              <div class="text-center text-muted py-5">
                <i class="fas fa-list fa-2x mb-2 opacity-25"></i>
                <p class="mb-0 small">Elige un periodo y presiona <strong>Consultar</strong></p>
              </div>
            </div>
          </div>
          <div class="tab-pane fade" id="reloj-panel-calendario" role="tabpanel">
            <div id="reloj-tabla-calendario">
              <div class="text-center text-muted py-5">
                <i class="fas fa-calendar-alt fa-2x mb-2 opacity-25"></i>
                <p class="mb-0 small">Cambia a modo <strong>Mes</strong> para ver el calendario</p>
              </div>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer border-top py-2" style="background:#f8fdf9;">
        <a href="<?php echo base_url('rh/RelojChecador/reporte_diario'); ?>" class="btn btn-sm btn-outline-success" target="_blank">
          <i class="fas fa-external-link-alt me-1"></i>Reporte diario
        </a>
        <a href="<?php echo base_url('rh/RelojChecador/reporte_mensual'); ?>" class="btn btn-sm btn-outline-success" target="_blank">
          <i class="fas fa-calendar-alt me-1"></i>Reporte mensual
        </a>
        <button type="button" class="btn btn-sm btn-secondary ms-auto" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>
<?php endif; ?>





<!-- Scripts necesarios para exportar tabla excel, pdf -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>


<script>
var table;
var export_filename = 'empleados-<?php echo date("Y-m-d");?>';
var currentEmpleadoId = null;

document.addEventListener("DOMContentLoaded", function() {
      //datatables      
      table = $('#datatables-empleados').DataTable({
          responsive: true,
          dom: 'Bfrtip',
          "searching": false,
          "order": [],
          "processing": true,
          lengthMenu: [
              [10, 50, 150, 500,-1],
              ['10 filas', '50 filas', '150 filas', '500 Filas', 'Mostrar todo']
          ],
  				buttons: [
                { extend: 'excelHtml5', text: 'Excel <i class="align-middle me-2 far fa-fw fa-file-excel"></i>', className: 'dropdown-item text-dark' },
                { extend: 'pdf', text: 'Pdf <i class="align-middle me-2 far fa-fw fa-file-pdf"></i>', className: 'dropdown-item text-dark' },
                { extend: 'csv', text: 'Csv <i class="align-middle me-2 far fa-fw fa-file-csv"></i>', className: 'dropdown-item text-dark' },
                { extend: 'print', text: 'Imprimir <i class="align-middle me-2 fas fa-fw fa-print"></i>', className: 'dropdown-item text-dark' },
          ],
          language: {
            "sProcessing": "Procesando...",
            "lengthMenu": 'Mostrar _MENU_',
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Registros del _START_ al _END_ de _TOTAL_",
            "sInfoFiltered": "(filtrado de _MAX_ registros)",            
          }, 

          
          "serverSide": true,

          "ajax": {
              "url": "<?php echo base_url('/rh/RecursosHumanos/search_empleados') ?>",
              "type": "POST",
              "data": function ( data ) {
                  data.search = $('#datatables-empleados-search').val();
                  data.estatus = $('#filter-estatus').val();
                  data.departamento_id = $('#filter-departamento').val();
                  data.<?php echo $this->security->get_csrf_token_name();?> = '<?php echo $this->security->get_csrf_hash();?>';
              }
          },
          "columnDefs": [
          {
              "targets": [ 0 ],
              "orderable": true,
          },
          ],

      });
      
      table.buttons().container().appendTo("#table_menu_actions");

      // Move length menu to custom container
      var lengthMenu = $('<select name="datatables-empleados_length" class="form-select" id="datatables-length-select">' +
        '<option value="10">10 filas</option>' +
        '<option value="50">50 filas</option>' +
        '<option value="150">150 filas</option>' +
        '<option value="500">500 Filas</option>' +
        '<option value="-1">Mostrar todo</option>' +
        '</select>');
      
      $('#datatables-length-container').html(lengthMenu);
      
      // Handle length menu change
      $('#datatables-length-select').on('change', function() {
        var val = $(this).val();
        table.page.len(val).draw();
      });

      ///boton busqueda con filtro
      $('#btn-filter').click(function(){
          table.ajax.reload();
      });

      ///detectamos si el cursor esta en el input de busqueda, al dar enter se ejecuta la busqueda 
      $('#datatables-empleados-search').on('keyup', function(e){
          if(e.which == 13){
              table.ajax.reload();
          }
      });

      ///evento para el filtro de estatus
      $('#filter-estatus').on('change', function(){
          table.ajax.reload();
      });

      ///evento para el filtro de departamento
      $('#filter-departamento').on('change', function(){
          table.ajax.reload();
      });

  });
</script>  

<script>
  function empleado_detail(id){
    currentEmpleadoId = id;
    empleadoActualId = id;
    $('#historial-desde').val('');
    $('#historial-hasta').val('');

    $.post('<?=base_url();?>/rh/RecursosHumanos/detail',
    {
      'id':id,
      'peticion':'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>':'<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result){
      result = JSON.parse(result);
      if(result['success'] && result['response']!=null){

        // Actualizar encabezado del offcanvas
        $('#offcanvas-empleado-nombre').text(result.nombre_completo);
        $('#offcanvas-empleado-numero').text('N° ' + (result.numero_empleado || '—'));

        // Renderizar tabs desde la estructura de datos
        if (result.tabs) {
          Object.keys(result.tabs).forEach(function(tabKey) {
            var tab = result.tabs[tabKey];
            var tabContentId = '#tab-' + tabKey + '-content';
            var html = '';
            tab.fields.forEach(function(field) {
              var iconHtml = field.icon ? '<i data-lucide="' + field.icon + '" class="me-2 text-muted" style="width:16px;height:16px;"></i>' : '';
              var cssClass = field.css_class || '';
              html += '<div class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">' +
                '<span class="text-muted small">' + iconHtml + field.label + '</span>' +
                '<span class="fw-semibold text-end ' + cssClass + '" style="max-width:60%;">' + field.value + '</span>' +
              '</div>';
            });
            $(tabContentId).html(html);
          });
          // Reinicializar iconos Lucide
          if (typeof lucide !== 'undefined') { lucide.createIcons(); }
        }

        // Renderizar acciones
        var actionsHtml = '';
        actionsHtml += '<a href="' + result.actions.editar + '" class="btn btn-warning btn-sm"><i data-lucide="edit" class="me-1" style="width:14px;height:14px;"></i>Editar</a>';
        actionsHtml += '<a href="' + result.actions.nuevo_contrato + '" class="btn btn-success btn-sm"><i data-lucide="file-text" class="me-1" style="width:14px;height:14px;"></i>Nuevo Contrato</a>';
        if (result.actions.mostrar_finiquito) {
          actionsHtml += '<button class="btn btn-info btn-sm" onclick="abrirCalculadoraBaja(' + result.actions.empleado_id + ')"><i class="fas fa-calculator me-1"></i>Finiquito</button>';
        }
        if (result.actions.mostrar_baja) {
          actionsHtml += '<button class="btn btn-danger btn-sm" onclick="delete_empleado(' + result.actions.empleado_id + ')"><i data-lucide="trash-2" class="me-1" style="width:14px;height:14px;"></i>Dar de Baja</button>';
        }
        if (result.actions.vinculo_habilitado) {
          var lblVinculo = result.actions.usuario_vinculado ? 'Usuario ERP' : 'Vincular usuario';
          var clsVinculo = result.actions.usuario_vinculado ? 'btn-outline-primary' : 'btn-primary';
          actionsHtml += '<button class="btn btn-sm ' + clsVinculo + '" onclick="abrirModalVincularUsuario(' + result.actions.empleado_id + ')"><i class="fas fa-user-lock me-1"></i>' + lblVinculo + '</button>';
        }
        $('#offcanvas-actions').html(actionsHtml);
        if (typeof lucide !== 'undefined') { lucide.createIcons(); }

        // Mostrar el Offcanvas
        var bsOffcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasDetalleEmpleado'));
        bsOffcanvas.show();
        
        // Cargar historial de contratos después de cargar detalles
        cargarHistorialContratos(id);
        
        // Cargar balance de vacaciones
        cargarVacaciones(id);
        
        // Cargar incidencias
        cargarBadgeIncidencias(id);
        
        // Cargar horario
        cargarBadgeHorario(id);

        <?php if (!empty($response['puede_ver_reloj'])): ?>
        cargarBadgeReloj(id);
        <?php endif; ?>

        cargarDocumentosEmpleado(id);
      }else {
        notifyShow("Error al obtener los datos","danger");
      }

    });
  }

  // Función para notificar datos faltantes al hacer clic en el badge ⚠️
  function notificarFaltantes(id) {
    // Simplemente abrimos el offcanvas de detalle (ya muestra los campos en rojo en la tab Fiscal)
    empleado_detail(id);
    setTimeout(function() {
      // Mostrar mensaje auxiliar indicando que revise la pestaña Fiscal
      notifyShow('⚠️ Revisa la pestaña "Fiscal" del empleado para ver los datos faltantes (marcados en rojo)', 'warning');
    }, 800);
  }

  function delete_empleado(id){
    if (confirm("¿Estás seguro de que quieres dar de baja a este empleado?")) {
      $.post('<?=base_url();?>/rh/RecursosHumanos/eliminar',
        {
          'id': id,
          'peticion': 'ajax',
          '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
        },
        function(result) {
          result = JSON.parse(result);
          if (result['success'] === true ) {
            notifyShow(result['message'], "success");
            table.ajax.reload(null, false);
            $("#detalles").html('');
            $("#actions").html('');
            $("#historial-contratos").html('<li class="timeline-item"><p class="text-muted">Selecciona un empleado para ver su historial</p></li>');
            table.ajax.reload();
          } else {
            notifyShow(result['message'], "warning");
          }
        }
      );
    }    
  } 

  // Filtrar historial
  function filtrarHistorial() {
    if(currentEmpleadoId) {
      cargarHistorialContratos(currentEmpleadoId);
    }
  }

  // Cargar historial de contratos cuando se selecciona un empleado
  function cargarHistorialContratos(empleado_id) {
    var fecha_inicio = $('#historial-desde').val();
    var fecha_fin = $('#historial-hasta').val();

    $("#historial-contratos").html('<li class="text-center text-muted mt-2"><i class="fas fa-spinner fa-spin"></i> Cargando...</li>');

    $.post('<?=base_url();?>/rh/RecursosHumanos/historial_contratos',
    {
      'empleado_id': empleado_id,
      'fecha_inicio': fecha_inicio,
      'fecha_fin': fecha_fin,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      if(result['success']) {
        $("#historial-contratos").html(result['timeline']);
      } else {
        $("#historial-contratos").html('<li class="timeline-item"><p class="text-muted">Error al cargar historial</p></li>');
      }
    });
  }

  // Ver contrato en modal
  var contratoActual = null;
  
  function verContrato(contrato_id) {
    $.post('<?=base_url();?>/rh/RecursosHumanos/ver_contrato',
    {
      'contrato_id': contrato_id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      if(result['success']) {
        contratoActual = result['contrato'];
        
        var badgeVigente = contratoActual.vigente == 1 ? 
          '<span class="badge bg-success">● VIGENTE</span>' : 
          '<span class="badge bg-secondary">○ NO VIGENTE</span>';
        
        // Formato profesional basado en ejemplo de recibo
        var html = '<div class="card">' +
          '<div class="card-body m-sm-3 m-md-5">' +
            // Encabezado
            '<div class="mb-4 text-center">' +
              '<img src="<?php echo base_url(); ?>assets/dist/img/brands/chisa_recubrimientos_logo.jpg" alt="CHISA" style="max-width: 200px; margin-bottom: 20px;">' +
              '<h2 style="margin: 0; font-weight: bold; text-transform: uppercase;">Contrato Individual de Trabajo</h2>' +
              '<p class="text-muted mb-0">CHISA RECUBRIMIENTOS</p>' +
            '</div>' +
            
            // Información del contrato
            '<div class="row mb-4">' +
              '<div class="col-md-6">' +
                '<div class="text-muted">Contrato No.</div>' +
                '<strong>' + contratoActual.version + '</strong>' +
              '</div>' +
              '<div class="col-md-6 text-md-end">' +
                '<div class="text-muted">Fecha de Inicio</div>' +
                '<strong>' + contratoActual.fecha_inicio + '</strong>' +
              '</div>' +
            '</div>' +
            
            '<hr class="my-4">' +
            
            // Información de las partes
            '<div class="row mb-4">' +
              '<div class="col-md-6">' +
                '<div class="text-muted">Empleado</div>' +
                '<strong>' + (contratoActual.nombre_completo || 'N/A') + '</strong>' +
                '<p class="mb-0">' +
                  '<small>Número de Empleado: ' + (contratoActual.numero_empleado || 'N/A') + '</small><br>' +
                  '<small>Puesto: ' + (contratoActual.puesto || 'N/A') + '</small>' +
                '</p>' +
              '</div>' +
              '<div class="col-md-6 text-md-end">' +
                '<div class="text-muted">Tipo de Contrato</div>' +
                '<strong>' + contratoActual.tipo_contrato + '</strong>' +
                '<p class="mb-0">' +
                  '<small>Estatus: ' + badgeVigente + '</small><br>' +
                  '<small>Fecha de Creación: ' + new Date(contratoActual.fecha_creacion).toLocaleDateString('es-MX') + '</small>' +
                '</p>' +
              '</div>' +
            '</div>' +
            
            // Contenido del contrato
            '<div class="mb-4" style="background: #f8f9fa; padding: 25px; border-radius: 8px; border: 1px solid #dee2e6;">' +
              '<div style="white-space: pre-wrap; text-align: justify; line-height: 1.8; font-size: 14px;">' +
                contratoActual.contrato_texto +
              '</div>' +
            '</div>' +
            
            // Pie de página
            '<div class="text-center mt-4 pt-4" style="border-top: 1px solid #dee2e6;">' +
              '<p class="text-muted mb-2" style="font-size: 12px; font-style: italic;">Este documento es una representación digital del contrato de trabajo</p>' +
              '<p class="text-muted mb-0" style="font-size: 11px;">Generado el ' + new Date().toLocaleString('es-MX') + '</p>' +
            '</div>' +
          '</div>' +
        '</div>';
        
        $('#contrato-content').html(html);
        $('#modalContrato').modal('show');
      } else {
        notifyShow(result['message'], "danger");
      }
    });
  }


  function descargarPDF() {
    if(!contratoActual) {
      notifyShow("No hay contrato cargado", "warning");
      return;
    }
    
    var html = contratoActual.contrato_texto;
    
    // Crear contenedor virtual simulando hoja carta
    var element = document.createElement('div');
    element.innerHTML = html;
    
    // Estilos para simular un documento profesional
    Object.assign(element.style, {
        padding: '20px',
        fontFamily: '"Times New Roman", serif',
        fontSize: '12pt',
        lineHeight: '1.5',
        textAlign: 'justify',
        color: '#000000',
        backgroundColor: '#ffffff',
        width: '800px', // Ancho fijo para consistencia
        margin: '0 auto'
    });
    
    // Ajustar imágenes
    var imgs = element.querySelectorAll('img');
    imgs.forEach(function(img) {
        img.style.maxWidth = '100%';
        img.style.height = 'auto';
        img.style.display = 'block';
        img.style.margin = '0 auto';
    });
    
    var opt = {
      margin:       [15, 15, 15, 15], // Márgenes del PDF (mm)
      filename:     'Contrato_' + (contratoActual.tipo_contrato || 'Historico') + '.pdf',
      image:        { type: 'jpeg', quality: 1 },
      html2canvas:  { 
          scale: 2, // Mayor calidad
          useCORS: true, 
          scrollY: 0
      },
      jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' }
    };
    
    // Usar worker para mejor control
    html2pdf().set(opt).from(element).save().catch(function(err) {
        console.error(err);
        alert('Error al generar PDF. Verifique que no haya imágenes bloqueadas.');
    });
  }

  function descargarPDFDirecto(id) {
    var btn = $(event.target).closest('button');
    var originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    
    $.post('<?=base_url();?>/rh/RecursosHumanos/ver_contrato',
    {
      'contrato_id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      btn.prop('disabled', false).html(originalText);
      try {
          result = JSON.parse(result);
          if(result['success']) {
            var contrato = result['contrato'];
            
            // Usar solo texto del contrato
            var html = contrato.contrato_texto;
            
            var element = document.createElement('div');
            element.innerHTML = html;
            
            Object.assign(element.style, {
                padding: '20px',
                fontFamily: '"Times New Roman", serif',
                fontSize: '12pt',
                lineHeight: '1.5',
                textAlign: 'justify',
                color: '#000000',
                backgroundColor: '#ffffff',
                width: '800px',
                margin: '0 auto'
            });
            
            var imgs = element.querySelectorAll('img');
            imgs.forEach(function(img) {
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                img.style.display = 'block';
                img.style.margin = '0 auto';
            });
            
            var opt = {
              margin:       [15, 15, 15, 15],
              filename:     'Contrato_' + (contrato.tipo_contrato || 'Historico') + '.pdf',
              image:        { type: 'jpeg', quality: 1 },
              html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
              jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' }
            };
            
            html2pdf().set(opt).from(element).save();
            
          } else {
            notifyShow(result['message'], "danger");
          }
      } catch(e) {
          notifyShow('Error al procesar respuesta', 'danger');
      }
    });
  }

  // ========================================================================
  // GESTIÓN DE VACACIONES
  // ========================================================================

  var empleadoActualId = null;
  var periodoActual = null;
  var empleadoActualIncidencias = null;

  // Cargar balance de vacaciones
  function cargarVacaciones(empleado_id) {
    empleadoActualId = empleado_id;
    
    $.post('<?=base_url();?>/rh/RecursosHumanos/vacaciones_balance',
    {
      'empleado_id': empleado_id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      
      // Siempre mostrar el badge
      $('#vacaciones-badge').show();
      
      if(result['success'] && result['periodo']) {
        periodoActual = result['periodo'];
        
        // Mostrar días disponibles
        $('#dias-disponibles').text(result['periodo'].dias_disponibles + ' días');
        $('#periodo-vacaciones').text('Período: ' + result['periodo'].periodo_inicio + ' - ' + result['periodo'].periodo_fin);
        $('#btn-ver-vacaciones').show();
      } else {
        // No hay período, mostrar mensaje y opción de generar
        $('#dias-disponibles').html('<small class="text-muted">Sin período activo</small>');
        $('#periodo-vacaciones').html('<button class="btn btn-sm btn-success mt-2" onclick="generarPeriodoManual(' + empleado_id + ')"><i class="fas fa-plus"></i> Generar Período</button>');
        $('#btn-ver-vacaciones').hide();
      }
    });
  }

  // Generar período manualmente
  function generarPeriodoManual(empleado_id) {
    if(!confirm('¿Generar período de vacaciones para este empleado?')) {
      return;
    }

    $.post('<?=base_url();?>/rh/RecursosHumanos/generar_periodo_vacaciones',
    {
      'empleado_id': empleado_id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      if(result['success']) {
        notifyShow(result['message'], "success");
        cargarVacaciones(empleado_id);
      } else {
        notifyShow(result['message'], "danger");
      }
    });
  }

  // Ver detalle de vacaciones
  function verVacaciones() {
    if(!empleadoActualId) {
      notifyShow("Selecciona un empleado primero", "warning");
      return;
    }

    $.post('<?=base_url();?>/rh/RecursosHumanos/vacaciones_balance',
    {
      'empleado_id': empleadoActualId,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      if(result['success']) {
        periodoActual = result['periodo'];
        
        // Mostrar balance actual
        if(result['periodo']) {
          var balanceHtml = '<div class="col-md-3 text-center">' +
            '<h3 class="text-primary">' + result['periodo'].dias_totales + '</h3>' +
            '<small>Días Totales</small>' +
          '</div>' +
          '<div class="col-md-3 text-center">' +
            '<h3 class="text-danger">' + result['periodo'].dias_tomados + '</h3>' +
            '<small>Días Tomados</small>' +
          '</div>' +
          '<div class="col-md-3 text-center">' +
            '<h3 class="text-success">' + result['periodo'].dias_disponibles + '</h3>' +
            '<small>Días Disponibles</small>' +
          '</div>' +
          '<div class="col-md-3 text-center">' +
            '<h3 class="text-info">' + result['periodo'].anios_antiguedad + '</h3>' +
            '<small>Años Antigüedad</small>' +
          '</div>';
          
          $('#balance-actual').html(balanceHtml);
        }
        
        // Mostrar solicitudes
        if(result['solicitudes'] && result['solicitudes'].length > 0) {
          var solicitudesHtml = '';
          result['solicitudes'].forEach(function(sol) {
            var badgeClass = sol.estatus == 'Aprobada' ? 'bg-success' : 
                           sol.estatus == 'Rechazada' ? 'bg-danger' : 'bg-warning';
            
            var acciones = '';
            if(sol.estatus == 'Pendiente') {
              acciones = '<button class="btn btn-xs btn-success me-1" onclick="procesarSolicitud(' + sol.id + ', \'aprobar\')"><i class="fas fa-check"></i></button>' +
                         '<button class="btn btn-xs btn-danger" onclick="procesarSolicitud(' + sol.id + ', \'rechazar\')"><i class="fas fa-times"></i></button>';
            }

            solicitudesHtml += '<tr>' +
              '<td>' + sol.fecha_solicitud + '</td>' +
              '<td>' + sol.fecha_inicio + ' - ' + sol.fecha_fin + '</td>' +
              '<td>' + sol.dias_solicitados + '</td>' +
              '<td><span class="badge ' + badgeClass + '">' + sol.estatus + '</span></td>' +
              '<td>' + acciones + '</td>' +
            '</tr>';
          });
          $('#solicitudes-body').html(solicitudesHtml);
        }
        
        $('#modalVacaciones').modal('show');
      } else {
        notifyShow("No hay información de vacaciones disponible", "warning");
      }
    });
  }

  // Abrir modal de solicitud
  function abrirSolicitudVacaciones() {
    if(!periodoActual) {
      notifyShow("No hay período de vacaciones activo", "warning");
      return;
    }

    $('#empleado_id_vacaciones').val(empleadoActualId);
    $('#dias-disp-solicitud').text(periodoActual.dias_disponibles);
    $('#modalVacaciones').modal('hide');
    $('#modalSolicitarVacaciones').modal('show');
  }

  // Calcular días al cambiar fechas
  document.addEventListener("DOMContentLoaded", function() {
    $('#fecha_inicio_vac, #fecha_fin_vac').on('change', function() {
      var inicio = $('#fecha_inicio_vac').val();
      var fin = $('#fecha_fin_vac').val();
      if(inicio && fin) {
        var d1 = new Date(inicio);
        var d2 = new Date(fin);
        var dias = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
        $('#dias_calculados').val(dias);
      }
    });
  });

  // Enviar solicitud
  function enviarSolicitudVacaciones() {
    var formData = $('#formSolicitarVacaciones').serialize();
    formData += '&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>';
    
    $.post('<?=base_url();?>/rh/RecursosHumanos/solicitar_vacaciones_ajax',
      formData,
      function(result) {
        result = JSON.parse(result);
        if(result['success']) {
          notifyShow(result['message'], "success");
          $('#modalSolicitarVacaciones').modal('hide');
          $('#formSolicitarVacaciones')[0].reset();
          cargarVacaciones(empleadoActualId);
        } else {
          notifyShow(result['message'], "danger");
        }
      }
    );
  }

  // Abrir modal con todas las solicitudes (Admin)
  function abrirTodasSolicitudes() {
    $('#modalTodasSolicitudes').modal('show');
    cargarTodasSolicitudes('Pendiente');
  }

  // Cargar todas las solicitudes
  var estatusActualSolicitudes = 'Pendiente';

  function cargarTodasSolicitudes(estatus) {
    if(!estatus) estatus = 'Pendiente';
    estatusActualSolicitudes = estatus;

    // Actualizar UI de tabs
    $('.nav-link-solicitudes').removeClass('active');
    $('#tab-sol-' + estatus).addClass('active');

    // Mapear 'Todas' a vacío para el backend
    var estatusBackend = estatus === 'Todas' ? '' : estatus;

    $.post('<?=base_url();?>/rh/RecursosHumanos/solicitudes_vacaciones_lista',
    {
      'estatus': estatusBackend,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      if(result['success']) {
        var html = '';
        if(result['solicitudes'].length > 0) {
          result['solicitudes'].forEach(function(sol) {
            
            var acciones = '';
            if(sol.estatus === 'Pendiente') {
                acciones = '<button class="btn btn-sm btn-success me-1" onclick="procesarSolicitud(' + sol.id + ', \'aprobar\')"><i class="fas fa-check"></i></button>' +
                           '<button class="btn btn-sm btn-danger" onclick="procesarSolicitud(' + sol.id + ', \'rechazar\')"><i class="fas fa-times"></i></button>';
            } else {
                acciones = '<span class="text-muted">-</span>';
            }

            // Badge de estatus
            var badgeClass = 'bg-secondary';
            if(sol.estatus === 'Aprobada') badgeClass = 'bg-success';
            if(sol.estatus === 'Rechazada') badgeClass = 'bg-danger';
            if(sol.estatus === 'Pendiente') badgeClass = 'bg-warning text-dark';
            
            var estatusHtml = '<span class="badge ' + badgeClass + '">' + sol.estatus + '</span>';

            html += '<tr>' +
              '<td>' + sol.numero_empleado + ' - ' + sol.nombre + ' ' + sol.apellido_paterno + '</td>' +
              '<td>' + sol.fecha_solicitud + '</td>' +
              '<td>' + sol.fecha_inicio + ' al ' + sol.fecha_fin + '</td>' +
              '<td>' + sol.dias_solicitados + '</td>' +
              '<td>' + (sol.observaciones || '-') + '</td>' +
              '<td>' + estatusHtml + '</td>' + // Mostrar estatus si se ven todas
              '<td>' + acciones + '</td>' +
            '</tr>';
          });
        } else {
          html = '<tr><td colspan="7" class="text-center text-muted">No hay solicitudes ' + estatus.toLowerCase() + 's</td></tr>';
        }
        $('#todas-solicitudes-body').html(html);
      }
    });
  }

  // Aprobar o rechazar solicitud
  function procesarSolicitud(id, accion) {
    var url = accion == 'aprobar' ? 'aprobar_vacaciones_ajax' : 'rechazar_vacaciones_ajax';
    var motivo = '';
    
    if(accion == 'rechazar') {
      motivo = prompt('Motivo del rechazo:');
      if(motivo === null) return; // Canceló el prompt
    } else {
      if(!confirm('¿Estás seguro de aprobar esta solicitud?')) return;
    }

    $.post('<?=base_url();?>/rh/RecursosHumanos/' + url,
    {
      'id': id,
      'motivo': motivo,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      if(result['success']) {
        notifyShow(result['message'], "success");
        cargarTodasSolicitudes();
        // Recargar balance si el empleado está seleccionado
        if(empleadoActualId) cargarVacaciones(empleadoActualId);
      } else {
        notifyShow(result['message'], "danger");
      }
    });
  }

  // ========================================================================
  // GESTIÓN DE INCIDENCIAS
  // ========================================================================

  function cargarBadgeIncidencias(empleado_id) {
    $.post('<?=base_url();?>/rh/RecursosHumanos/incidencias_lista',
    {
      'empleado_id': empleado_id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      try {
        result = JSON.parse(result);
        console.log('Incidencias response:', result);
        if(result['success']) {
          $('#incidencias-badge').show();
          $('#total-incidencias').text(result['estadisticas'].total || 0);
        } else {
          console.log('Error en incidencias:', result);
          $('#incidencias-badge').hide();
        }
      } catch(e) {
        console.error('Error parsing incidencias:', e, result);
      }
    }).fail(function(xhr, status, error) {
      console.error('AJAX error incidencias:', status, error);
    });
  }

  function verIncidencias() {
    if (!empleadoActualId) {
      notifyShow('Selecciona un empleado primero', 'warning');
      return;
    }
    empleadoActualIncidencias = empleadoActualId;
    $('#modalIncidencias').modal('show');
    cargarIncidencias();
  }

  function cargarIncidencias() {
    const filtros = {
      empleado_id: empleadoActualIncidencias,
      tipo_incidencia: $('#filtro-tipo-incidencia').val(),
      fecha_desde: $('#filtro-fecha-desde').val(),
      fecha_hasta: $('#filtro-fecha-hasta').val(),
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    };

    $.post('<?=base_url();?>/rh/RecursosHumanos/incidencias_lista', filtros, function(result) {
      result = JSON.parse(result);
      if (result['success']) {
        mostrarIncidencias(result['incidencias']);
        mostrarEstadisticasIncidencias(result['estadisticas']);
      }
    });
  }

  function mostrarIncidencias(incidencias) {
    const tbody = $('#incidencias-body');
    tbody.empty();

    if (incidencias.length === 0) {
      tbody.append('<tr><td colspan="6" class="text-center text-muted">No hay incidencias registradas</td></tr>');
      return;
    }

    incidencias.forEach(inc => {
      const badgeClass = inc.estatus === 'Activa' ? 'bg-success' : (inc.estatus === 'Cancelada' ? 'bg-danger' : 'bg-secondary');
      const descuento = inc.tiene_descuento == 1 ? `$${parseFloat(inc.monto_descuento).toFixed(2)}` : '-';
      const acciones = inc.estatus === 'Activa' 
        ? `<button class="btn btn-sm btn-danger" onclick="cancelarIncidencia(${inc.id})"><i class="fas fa-times"></i></button>`
        : '-';

      const row = `
        <tr>
          <td>${inc.fecha_incidencia}${inc.hora_incidencia ? ' ' + inc.hora_incidencia : ''}</td>
          <td><span class="badge bg-warning">${inc.tipo_incidencia}</span></td>
          <td>
            ${inc.descripcion || '-'}
            ${inc.archivo_evidencia ? `<br><a href="<?=base_url()?>${inc.archivo_evidencia}" target="_blank" class="text-info"><i class="fas fa-paperclip"></i> Ver Evidencia</a>` : ''}
          </td>
          <td>${descuento}</td>
          <td><span class="badge ${badgeClass}">${inc.estatus}</span></td>
          <td>${acciones}</td>
        </tr>
      `;
      tbody.append(row);
    });
  }

  function mostrarEstadisticasIncidencias(stats) {
    $('#stat-total-incidencias').text(stats.total || 0);
    $('#stat-descuentos').text('$' + parseFloat(stats.total_descuentos || 0).toFixed(2));
  }

  function aplicarFiltrosIncidencias() {
    cargarIncidencias();
  }

  function abrirRegistroIncidencia() {
    $('#formRegistrarIncidencia')[0].reset();
    $('#modalRegistrarIncidencia').modal('show');
  }

  function guardarIncidencia() {
    const form = $('#formRegistrarIncidencia')[0];
    const formData = new FormData(form);
    
    formData.append('empleado_id', empleadoActualIncidencias);
    formData.append('peticion', 'ajax');
    formData.append('<?php echo $this->security->get_csrf_token_name();?>', '<?php echo $this->security->get_csrf_hash();?>');

    const tipo = formData.get('tipo_incidencia');
    const fecha = formData.get('fecha_incidencia');

    if (!tipo || !fecha) {
      notifyShow('Completa los campos requeridos', 'warning');
      return;
    }

    $.ajax({
      url: '<?=base_url();?>/rh/RecursosHumanos/registrar_incidencia_ajax',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      success: function(result) {
        result = JSON.parse(result);
        if (result['success']) {
          notifyShow(result['message'], 'success');
          $('#modalRegistrarIncidencia').modal('hide');
          cargarIncidencias();
          cargarBadgeIncidencias(empleadoActualIncidencias);
        } else {
          notifyShow(result['message'], 'danger');
        }
      },
      error: function(xhr, status, error) {
        notifyShow('Error al procesar la solicitud', 'danger');
        console.error(error);
      }
    });
  }

  function cancelarIncidencia(id) {
    if (!confirm('¿Cancelar esta incidencia?')) return;

    $.post('<?=base_url();?>/rh/RecursosHumanos/cancelar_incidencia_ajax',
    {
      id: id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      if (result['success']) {
        notifyShow('Incidencia cancelada', 'success');
        cargarIncidencias();
      }
    });
  }

  // Toggle campos según tipo
  document.addEventListener("DOMContentLoaded", function() {
    $('#tipo_incidencia').on('change', function() {
      if ($(this).val() === 'Retardo') {
        $('#hora-container').show();
      } else {
        $('#hora-container').hide();
      }
    });

    $('#tiene_descuento_inc').on('change', function() {
      if ($(this).val() == '1') {
        $('#monto-descuento-container').show();
      } else {
        $('#monto-descuento-container').hide();
      }
    });
  });

  // ========================================================================
  // GESTIÓN DE HORARIOS
  // ========================================================================

  var empleadoActualHorario = null;

  function cargarBadgeHorario(empleado_id) {
    $.post('<?=base_url();?>/rh/RecursosHumanos/horario_empleado',
    {
      'empleado_id': empleado_id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      try {
        result = JSON.parse(result);
        if(result['success'] && result['tiene_horario']) {
          $('#horario-badge').show();
          $('#horas-semana').text(result['resumen'].total_horas_semana.toFixed(1) + ' hrs');
          $('#turno-empleado').text(result['resumen'].turno || 'Sin turno');
        } else {
          $('#horario-badge').show();
          $('#horas-semana').text('0 hrs');
          $('#turno-empleado').text('Sin horario');
        }
      } catch(e) {
        console.error('Error parsing horario:', e);
      }
    });
  }

  function verHorario() {
    if (!empleadoActualId) {
      notifyShow('Selecciona un empleado primero', 'warning');
      return;
    }
    empleadoActualHorario = empleadoActualId;
    $('#horario_empleado_id').val(empleadoActualId);
    $('#modalHorario').modal('show');
    cargarHorarioCompleto();
  }

  function cargarHorarioCompleto() {
    $.post('<?=base_url();?>/rh/RecursosHumanos/horario_empleado',
    {
      'empleado_id': empleadoActualHorario,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      if(result['success']) {
        mostrarHorarios(result['horarios'], result['resumen']);
      }
    });
  }

  function mostrarHorarios(horarios, resumen) {
    const tbody = $('#horarios-tabla');
    tbody.empty();
    const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    
    dias.forEach(dia => {
      const horario = horarios.find(h => h.dia_semana === dia) || {};
      const esLaboral = horario.es_dia_laboral == 1;
      
      const row = `
        <tr>
          <td><strong>${dia}</strong></td>
          <td class="text-center">
            <input type="checkbox" name="${dia}_laboral" ${esLaboral ? 'checked' : ''} 
                   onchange="toggleDiaLaboral('${dia}', this.checked)">
          </td>
          <td><input type="time" class="form-control form-control-sm" name="${dia}_entrada" 
                     value="${horario.hora_entrada || '09:00'}" ${!esLaboral ? 'disabled' : ''}></td>
          <td><input type="time" class="form-control form-control-sm" name="${dia}_salida" 
                     value="${horario.hora_salida || '18:00'}" ${!esLaboral ? 'disabled' : ''}></td>
          <td><input type="time" class="form-control form-control-sm" name="${dia}_comida_entrada" 
                     value="${horario.hora_entrada_comida || ''}" ${!esLaboral ? 'disabled' : ''}></td>
          <td><input type="time" class="form-control form-control-sm" name="${dia}_comida_salida" 
                     value="${horario.hora_salida_comida || ''}" ${!esLaboral ? 'disabled' : ''}></td>
          <td>
            <select class="form-select form-select-sm" name="${dia}_turno" ${!esLaboral ? 'disabled' : ''}>
              <option value="">Sin turno</option>
              <option value="Matutino" ${horario.turno === 'Matutino' ? 'selected' : ''}>Matutino</option>
              <option value="Vespertino" ${horario.turno === 'Vespertino' ? 'selected' : ''}>Vespertino</option>
              <option value="Nocturno" ${horario.turno === 'Nocturno' ? 'selected' : ''}>Nocturno</option>
              <option value="Mixto" ${horario.turno === 'Mixto' ? 'selected' : ''}>Mixto</option>
            </select>
          </td>
        </tr>
      `;
      tbody.append(row);
    });
    
    $('#resumen-horas').text(resumen.total_horas_semana.toFixed(1) + ' hrs');
    $('#resumen-dias').text(resumen.dias_laborales);
  }

  function toggleDiaLaboral(dia, esLaboral) {
    const inputs = $(`input[name^="${dia}_"], select[name^="${dia}_"]`).not(`input[name="${dia}_laboral"]`);
    inputs.prop('disabled', !esLaboral);
  }

  function guardarHorario() {
    const formData = $('#formHorario').serialize();
    
    $.post('<?=base_url();?>/rh/RecursosHumanos/guardar_horario_ajax',
      formData + '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>',
      function(result) {
        result = JSON.parse(result);
        if(result['success']) {
          notifyShow(result['message'], 'success');
          $('#modalHorario').modal('hide');
          cargarBadgeHorario(empleadoActualHorario);
        } else {
          notifyShow('Error al guardar horario', 'danger');
        }
      }
    );
  }

  function crearHorarioEstandar() {
    if(!confirm('¿Crear horario estándar (Lun-Vie 9-18hrs)?')) return;
    
    $.post('<?=base_url();?>/rh/RecursosHumanos/crear_horario_estandar_ajax',
    {
      'empleado_id': empleadoActualHorario,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      if(result['success']) {
        notifyShow(result['message'], 'success');
        cargarHorarioCompleto();
      }
    });
  }

  <?php if (!empty($response['puede_ver_reloj'])): ?>
  // ========================================================================
  // ASISTENCIAS RELOJ CHECADOR (offcanvas + modal)
  // ========================================================================
  var modoRelojActual = 'semana';

  function _rjParse(r) {
    if (typeof r === 'string') { try { return JSON.parse(r); } catch(e) { return null; } }
    return r || null;
  }

  function cargarBadgeReloj(empleado_id) {
    $('#reloj-badge').show();
    $('#reloj-ultima-checada').html('<i class="fas fa-spinner fa-spin fa-sm text-success"></i>');
    $('#reloj-resumen-mes').text('Cargando...');

    $.post('<?=base_url();?>/rh/RecursosHumanos/asistencias_reloj_resumen', {
      empleado_id: empleado_id,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(raw) {
      var result = _rjParse(raw);
      if (result && result.success) {
        $('#reloj-ultima-checada').text(result.ultima_checada_fmt || 'Sin registros');
        $('#reloj-resumen-mes').text(
          (result.dias_trabajados_mes || 0) + ' días este mes · ' +
          (result.total_checadas_30 || 0) + ' checadas (30d)'
        );
      } else {
        $('#reloj-ultima-checada').text('—');
        $('#reloj-resumen-mes').text((result && result.message) ? result.message : 'Sin datos');
      }
    });
  }

  function verAsistenciasReloj() {
    if (!empleadoActualId) {
      notifyShow('Selecciona un empleado primero', 'warning');
      return;
    }
    $('#reloj-fecha-ref').val(new Date().toISOString().slice(0, 10));
    modoRelojActual = 'semana';
    actualizarTabsReloj();
    $('#modalAsistenciasReloj').modal('show');
    cargarAsistenciasReloj();
  }

  function cambiarModoReloj(modo) {
    modoRelojActual = modo;
    actualizarTabsReloj();
    cargarAsistenciasReloj();
  }

  function actualizarTabsReloj() {
    $('#tab-reloj-dia, #tab-reloj-semana, #tab-reloj-mes').removeClass('active');
    $('#tab-reloj-' + modoRelojActual).addClass('active');
  }

  function cargarAsistenciasReloj() {
    if (!empleadoActualId) return;

    var loading = '<div class="text-center py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2 text-success"></i><p class="mb-0 small mt-2">Cargando registros...</p></div>';
    $('#reloj-tabla-resumen').html(loading);
    $('#reloj-tabla-detalle').html(loading);

    $.post('<?=base_url();?>/rh/RecursosHumanos/asistencias_reloj_periodo', {
      empleado_id: empleadoActualId,
      modo: modoRelojActual,
      fecha_ref: $('#reloj-fecha-ref').val(),
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(raw) {
      var result = _rjParse(raw);
      if (!result) {
        var errHtml = '<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle me-2"></i>No se pudo procesar la respuesta del servidor. Intenta de nuevo.</div>';
        $('#reloj-tabla-resumen, #reloj-tabla-detalle').html(errHtml);
        return;
      }
      if (!result.success) {
        var warnHtml = '<div class="alert alert-warning m-3"><i class="fas fa-info-circle me-2"></i>' + (result.message || 'No se pudo cargar el periodo.') + '</div>';
        $('#reloj-tabla-resumen, #reloj-tabla-detalle').html(warnHtml);
        return;
      }

      $('#reloj-modal-empleado').text(
        result.empleado.numero_empleado + ' — ' + result.empleado.nombre
      );
      $('#reloj-modal-periodo').text(
        'Del ' + formatearFechaMx(result.fecha_inicio) + ' al ' + formatearFechaMx(result.fecha_fin)
      );
      $('#reloj-stat-checadas').text(result.resumen.total_checadas + ' checadas');
      $('#reloj-stat-dias').text(result.resumen.dias_con_registro + ' días con registro');

      renderAsistenciasReloj(result.dias);
    }).fail(function(xhr) {
      var errHtml = '<div class="alert alert-danger m-3"><i class="fas fa-exclamation-circle me-2"></i>Error de red (HTTP ' + (xhr.status || '?') + '). Verifica tu conexión.</div>';
      $('#reloj-tabla-resumen, #reloj-tabla-detalle').html(errHtml);
    });
  }

  function formatearFechaMx(fecha) {
    if (!fecha) return '';
    var p = fecha.split('-');
    return p[2] + '/' + p[1] + '/' + p[0];
  }

  var _relojEstadoClases = {
    'Asistencia completa': 'bg-success',
    'Con retardo':         'bg-warning text-dark',
    'Retardo mayor':       'bg-danger',
    'Salida temprana':     'bg-warning text-dark',
    'Checadas parciales':  'bg-secondary',
    'Sin checadas':        'bg-light text-muted border',
    'Sin horario asignado':'bg-info text-dark'
  };

  var _relojTipoClases = {
    entrada:            'bg-success',
    salida:             'bg-primary',
    salida_comida:      'bg-warning text-dark',
    entrada_comida:     'bg-info text-dark',
    checada_intermedia: 'bg-secondary',
    checada_extra:      'bg-light text-dark border'
  };

  function badgeEstadoAsistencia(estado) {
    var mapa = (typeof _relojEstadoClases !== 'undefined') ? _relojEstadoClases : {};
    var etiqueta = estado || 'Sin checadas';
    var cls = mapa[etiqueta] || 'bg-secondary';
    return '<span class="badge rounded-pill ' + cls + '">' + etiqueta + '</span>';
  }

  function badgeTipoChecada(tipo, label) {
    var mapa = (typeof _relojTipoClases !== 'undefined') ? _relojTipoClases : {};
    var cls = mapa[tipo] || 'bg-secondary';
    return '<span class="badge ' + cls + '">' + (label || tipo || '—') + '</span>';
  }

  function celdaHora(val) {
    return val
      ? '<span class="fw-semibold text-success">' + val + '</span>'
      : '<span class="text-muted small">—</span>';
  }

  function renderAsistenciasReloj(dias) {
    if (!dias || dias.length === 0) {
      var vacio = '<div class="text-center text-muted py-5"><i class="fas fa-calendar-times fa-2x mb-2 opacity-25"></i><p class="mb-0 small">Sin registros en este periodo</p></div>';
      $('#reloj-tabla-resumen, #reloj-tabla-detalle, #reloj-tabla-calendario').html(vacio);
      return;
    }

    // --- Tabla resumen ---
    var r = '<div class="table-responsive">';
    r += '<table class="table table-sm table-hover align-middle mb-0" style="font-size:0.82rem">';
    r += '<thead><tr style="background:#f0fdf4;color:#15803d;">';
    r += '<th class="fw-semibold">Fecha</th>';
    r += '<th class="fw-semibold">Día</th>';
    r += '<th class="fw-semibold text-center"><i class="fas fa-sign-in-alt me-1"></i>Entrada</th>';
    r += '<th class="fw-semibold text-center">Sal. comida</th>';
    r += '<th class="fw-semibold text-center">Ent. comida</th>';
    r += '<th class="fw-semibold text-center"><i class="fas fa-sign-out-alt me-1"></i>Salida</th>';
    r += '<th class="fw-semibold">Estado</th>';
    r += '<th class="fw-semibold text-end">Retardo</th>';
    r += '<th class="fw-semibold text-end">Horas</th>';
    r += '</tr></thead><tbody>';

    dias.forEach(function(dia) {
      var calc = dia.calculo || {};
      var tieneDatos = dia.checadas && dia.checadas.length > 0;
      var trClass = tieneDatos ? '' : 'opacity-60';
      var retardo = calc.retardo
        ? '<span class="text-danger fw-semibold">' + calc.minutos_retardo + 'min</span>'
        : '<span class="text-muted">—</span>';

      r += '<tr class="' + trClass + '">';
      r += '<td class="fw-semibold">' + formatearFechaMx(dia.fecha) + '</td>';
      r += '<td><small class="text-muted">' + dia.dia_semana + '</small></td>';
      r += '<td class="text-center">' + celdaHora(calc.entrada) + '</td>';
      r += '<td class="text-center">' + celdaHora(calc.salida_comida) + '</td>';
      r += '<td class="text-center">' + celdaHora(calc.entrada_comida) + '</td>';
      r += '<td class="text-center">' + celdaHora(calc.salida) + '</td>';
      r += '<td>' + badgeEstadoAsistencia(calc.estado || 'Sin checadas') + '</td>';
      r += '<td class="text-end">' + retardo + '</td>';
      r += '<td class="text-end fw-semibold">' + (calc.horas_trabajadas || '00:00') + '</td>';
      r += '</tr>';
    });

    r += '</tbody></table></div>';
    $('#reloj-tabla-resumen').html(r);

    // --- Tabla detalle ---
    var d = '<div class="table-responsive">';
    d += '<table class="table table-sm table-hover align-middle mb-0" style="font-size:0.82rem">';
    d += '<thead><tr style="background:#f0fdf4;color:#15803d;">';
    d += '<th class="fw-semibold">Fecha</th>';
    d += '<th class="fw-semibold">Hora</th>';
    d += '<th class="fw-semibold">Tipo</th>';
    d += '<th class="fw-semibold">Método</th>';
    d += '<th class="fw-semibold">Dispositivo</th>';
    d += '</tr></thead><tbody>';

    var hayChecadas = false;
    dias.forEach(function(dia) {
      if (!dia.checadas || !dia.checadas.length) return;
      hayChecadas = true;
      dia.checadas.forEach(function(c, idx) {
        d += idx === 0
          ? '<tr class="table-success table-success-subtle">'
          : '<tr>';
        d += '<td class="text-muted small">' + (idx === 0 ? '<strong class="text-dark">' + formatearFechaMx(dia.fecha) + '</strong>' : '') + '</td>';
        d += '<td class="fw-semibold">' + c.hora + '</td>';
        d += '<td>' + badgeTipoChecada(c.tipo, c.tipo_label) + '</td>';
        d += '<td><small class="text-muted">' + c.metodo_label + '</small></td>';
        d += '<td><code class="small text-muted">' + (c.dispositivo_sn || '—') + '</code></td>';
        d += '</tr>';
      });
    });

    if (!hayChecadas) {
      d += '<tr><td colspan="5" class="text-muted text-center py-4"><i class="fas fa-inbox me-2"></i>Sin checadas en este periodo</td></tr>';
    }
    d += '</tbody></table></div>';
    $('#reloj-tabla-detalle').html(d);

    // --- Calendario mensual (solo si modo es 'mes') ---
    if (modoRelojActual === 'mes') {
      // Extraer año y mes de la primera fecha
      var year = 0, month = 0;
      dias.forEach(function(dia) {
        if (dia.fecha) {
          var p = dia.fecha.split('-');
          year = parseInt(p[0], 10);
          month = parseInt(p[1], 10);
          return false; // break
        }
      });
      if (year > 0 && month > 0 && typeof window.renderCalendarioMensual === 'function') {
        $('#reloj-tabla-calendario').html('<div class="mt-4">' +
          '<div class="d-flex align-items-center justify-content-between mb-3">' +
          '<h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2 text-success"></i>Vista Calendario Mensual</h6>' +
          '<div class="d-flex gap-2 align-items-center">' +
          '<span class="badge bg-success rounded-pill px-3 py-1" style="font-size:0.7rem;">✓ Asistió</span>' +
          '<span class="badge bg-warning text-dark rounded-pill px-3 py-1" style="font-size:0.7rem;">⚠ Retardo</span>' +
          '<span class="badge bg-danger rounded-pill px-3 py-1" style="font-size:0.7rem;">✗ Falta</span>' +
          '<span class="badge bg-secondary rounded-pill px-3 py-1" style="font-size:0.7rem;">— Descanso</span>' +
          '<span class="badge bg-light text-muted rounded-pill px-3 py-1 border" style="font-size:0.7rem;"> Sin datos</span>' +
          '</div></div>' +
          '<div class="table-responsive rounded border">' +
          '<table class="table table-sm table-bordered mb-0 text-center cal-month-table" style="font-size:0.8rem;">' +
          '<thead class="table-success text-uppercase small">' +
          '<tr><th class="fw-semibold" style="width:14.28%;">Lun</th><th class="fw-semibold" style="width:14.28%;">Mar</th><th class="fw-semibold" style="width:14.28%;">Mié</th><th class="fw-semibold" style="width:14.28%;">Jue</th><th class="fw-semibold" style="width:14.28%;">Vie</th><th class="fw-semibold text-muted" style="width:14.28%;">Sáb</th><th class="fw-semibold text-danger" style="width:14.28%;">Dom</th></tr>' +
          '</thead><tbody id="calendario-cuerpo"></tbody></table></div>' +
          '<div class="row mt-3 g-2" id="calendario-resumen">' +
          '<div class="col-3"><div class="card bg-success bg-opacity-10 border-success text-center py-2"><div class="fw-bold text-success fs-5" id="cal-dias-asistio">0</div><small class="text-muted">Asistió</small></div></div>' +
          '<div class="col-3"><div class="card bg-warning bg-opacity-10 border-warning text-center py-2"><div class="fw-bold text-warning fs-5" id="cal-dias-retardo">0</div><small class="text-muted">Con retardo</small></div></div>' +
          '<div class="col-3"><div class="card bg-danger bg-opacity-10 border-danger text-center py-2"><div class="fw-bold text-danger fs-5" id="cal-dias-falta">0</div><small class="text-muted">Falta</small></div></div>' +
          '<div class="col-3"><div class="card bg-light border text-center py-2"><div class="fw-bold fs-5" id="cal-total-horas">0 hrs</div><small class="text-muted">Horas trabajadas</small></div></div>' +
          '</div></div>');
        renderCalendarioMensual(dias, year, month);
      } else {
        $('#reloj-tabla-calendario').html('<div class="text-center text-muted py-5"><i class="fas fa-calendar-alt fa-2x mb-2 opacity-25"></i><p class="mb-0 small">Selecciona un mes para ver el calendario</p></div>');
      }
    }
  }
  <?php endif; ?>

  // ===========================================
  // CALCULADORA DE FINIQUITOS
  // ===========================================
  var modalCalculadora = null;
  var calculoResultados = null;

  function abrirCalculadoraBaja(id) {
    if(!modalCalculadora) {
        modalCalculadora = new bootstrap.Modal(document.getElementById('modalCalculadoraBaja'));
    }
    
    // Resetear
    calculoResultados = null;
    $('#tabla-calculo-body').html('<tr><td colspan="3" class="text-center text-muted small py-3"><i class="fas fa-hand-pointer me-1"></i>Los parámetros se ajustan automáticamente — revise los valores arriba</td></tr>');
    $('#calc-total').text('$0.00');
    $('#calc-nombre').text('Cargando...');
    $('#calc-salario-diario').text('$0.00');
    $('#calc-dias-aguinaldo').val(15);
    $('#calc-dias-vacaciones').val(0);
    $('#calc-dias-aguinaldo-label').text('15');
    $('#calc-dias-vacaciones-label').text('0');
    
    modalCalculadora.show();
    
    $.post('<?=base_url();?>/rh/RecursosHumanos/get_datos_calculadora',
    {
      'id': id,
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
       var data = JSON.parse(result);
       if(data.success) {
           $('#calc-nombre').text(data.nombre);
           $('#calc-fecha-ingreso').text(data.fecha_ingreso);
           $('#calc-fecha-ingreso-val').val(data.fecha_ingreso);
           $('#calc-antiguedad').text(data.antiguedad_anios);
           $('#calc-salario-diario').text('$' + parseFloat(data.salario_diario).toFixed(2));
           $('#calc-salario-diario-val').val(data.salario_diario);
           // Auto-calcular al cargar los datos
           calcularBaja();
       } else {
           alert('Error al cargar datos del empleado');
           modalCalculadora.hide();
       }
    });
  }

  function calcularBaja() {
      // 1. Obtener valores
      var salarioDiario = parseFloat($('#calc-salario-diario-val').val());
      var fechaIngresoStr = $('#calc-fecha-ingreso-val').val();
      if (!fechaIngresoStr || !salarioDiario) return;
      
      var fechaIngreso = new Date(fechaIngresoStr + 'T00:00:00');
      var fechaBaja = new Date($('#calc-fecha-baja').val() + 'T00:00:00');
      var motivo = $('#calc-motivo').val();
      var diasAguinaldoConf = parseInt($('#calc-dias-aguinaldo').val()) || 15;
      var diasVacacionesPendientes = parseInt($('#calc-dias-vacaciones').val()) || 0;
      
      // Update slider labels
      $('#calc-dias-aguinaldo-label').text(diasAguinaldoConf);
      $('#calc-dias-vacaciones-label').text(diasVacacionesPendientes);
      
      if(fechaBaja < fechaIngreso) {
        $('#tabla-calculo-body').html('<tr><td colspan="3" class="text-center text-danger small py-2">La fecha de baja no puede ser anterior a la de ingreso</td></tr>');
        $('#calc-total').text('$0.00');
        return;
      }
      
      // Cálculo de días trabajados totales y en el último año
      var diffTime = Math.abs(fechaBaja - fechaIngreso);
      var diasTrabajadosTotal = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
      
      var inicioAnio = new Date(fechaBaja.getFullYear(), 0, 1);
      if(fechaIngreso > inicioAnio) inicioAnio = fechaIngreso;
      var diffAnio = Math.abs(fechaBaja - inicioAnio);
      var diasTrabajadosAnio = Math.ceil(diffAnio / (1000 * 60 * 60 * 24)) + 1;
      
      var antiguedadAnios = diasTrabajadosTotal / 365;
      
      // === CÁLCULOS ===
      var conceptos = [];
      var total = 0;
      
      // 1. Aguinaldo Proporcional
      var aguinaldoProporcional = (diasTrabajadosAnio / 365) * diasAguinaldoConf * salarioDiario;
      conceptos.push({nombre: 'Aguinaldo Proporcional (' + diasTrabajadosAnio + ' días trab. año)', operacion: (diasTrabajadosAnio + '/365 × ' + diasAguinaldoConf + ' × $' + salarioDiario.toFixed(2)), monto: aguinaldoProporcional});
      
      // 2. Vacaciones
      var vacacionesMonto = diasVacacionesPendientes * salarioDiario;
      conceptos.push({nombre: 'Vacaciones Pendientes (' + diasVacacionesPendientes + ' días)', operacion: (diasVacacionesPendientes + ' × $' + salarioDiario.toFixed(2)), monto: vacacionesMonto});
      
      // 3. Prima Vacacional (25%)
      var primaVacacional = vacacionesMonto * 0.25;
      conceptos.push({nombre: 'Prima Vacacional (25%)', operacion: ('$' + vacacionesMonto.toFixed(2) + ' × 25%'), monto: primaVacacional});
      
      // 4. Prima de Antigüedad (12 días por año, tope 2× salario mínimo)
      var salarioMinimo = 278.80;
      var topePrima = salarioMinimo * 2;
      var salarioBasePrima = (salarioDiario > topePrima) ? topePrima : salarioDiario;
      var diasPrima = antiguedadAnios * 12;
      var montoPrima = diasPrima * salarioBasePrima;
      
      if(motivo == 'despido_injustificado' || motivo == 'despido_justificado' || (motivo == 'renuncia' && antiguedadAnios >= 15)) {
           var notaTope = (salarioDiario > topePrima) ? ' (tope 2×SM)' : '';
           conceptos.push({nombre: 'Prima de Antigüedad (' + diasPrima.toFixed(2) + ' días)' + notaTope, operacion: (diasPrima.toFixed(1) + ' × $' + salarioBasePrima.toFixed(2)), monto: montoPrima});
      }
      
      // 5. Indemnización Constitucional (90 días) - Solo Despido Injustificado
      if(motivo == 'despido_injustificado') {
          var indemnizacion = 90 * salarioDiario;
          conceptos.push({nombre: 'Indemnización Constitucional (90 días)', operacion: ('90 × $' + salarioDiario.toFixed(2)), monto: indemnizacion});
          
          var indemnizacion20dias = antiguedadAnios * 20 * salarioDiario;
          conceptos.push({nombre: 'Indemnización 20 días/año (Negativa Reinstalación)', operacion: (antiguedadAnios.toFixed(2) + ' años × 20 × $' + salarioDiario.toFixed(2)), monto: indemnizacion20dias});
      }
      
      // Guardar resultados para impresión
      calculoResultados = { conceptos: conceptos, total: 0, empleado: $('#calc-nombre').text(), fechaIngreso: $('#calc-fecha-ingreso').text(), antiguedad: $('#calc-antiguedad').text(), salarioDiario: salarioDiario, motivo: motivo, fechaBaja: $('#calc-fecha-baja').val(), diasAguinaldo: diasAguinaldoConf, diasVacaciones: diasVacacionesPendientes };
      
      // Render Table
      var html = '';
      total = 0;
      conceptos.forEach(function(c) {
          html += '<tr><td class="text-start small">' + c.nombre + '</td><td class="text-center text-muted small">' + c.operacion + '</td><td class="text-end fw-semibold">$' + c.monto.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td></tr>';
          total += c.monto;
      });
      calculoResultados.total = total;
      
      $('#tabla-calculo-body').html(html);
      $('#calc-total').text('$' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
  }

  function imprimirCalculoFiniquito() {
      if (!calculoResultados || !calculoResultados.conceptos || calculoResultados.conceptos.length === 0) {
          calcularBaja();
          if (!calculoResultados || !calculoResultados.conceptos || calculoResultados.conceptos.length === 0) {
              alert('Primero debe realizarse el cálculo');
              return;
          }
      }
      var r = calculoResultados;
      
      var html = '<div style="padding:30px;font-family:Arial,sans-serif;max-width:700px;margin:0 auto;">';
      html += '<div style="text-align:center;border-bottom:2px solid #1e3a5f;padding-bottom:15px;margin-bottom:20px;">';
      html += '<h3 style="color:#1e3a5f;margin:0 0 5px 0;">CHISA RECUBRIMIENTOS</h3>';
      html += '<h4 style="color:#555;margin:0;">Cálculo Estimado de Finiquito / Liquidación</h4>';
      html += '<small style="color:#888;">Generado el ' + new Date().toLocaleDateString('es-MX') + '</small>';
      html += '</div>';
      
      html += '<table style="width:100%;font-size:13px;margin-bottom:15px;">';
      html += '<tr><td style="color:#666;padding:3px 0;">Empleado:</td><td><strong>' + r.empleado + '</strong></td></tr>';
      html += '<tr><td style="color:#666;padding:3px 0;">Fecha Ingreso:</td><td>' + r.fechaIngreso + '</td></tr>';
      html += '<tr><td style="color:#666;padding:3px 0;">Antigüedad:</td><td>' + r.antiguedad + ' años</td></tr>';
      html += '<tr><td style="color:#666;padding:3px 0;">Salario Diario:</td><td>$' + r.salarioDiario.toFixed(2) + '</td></tr>';
      html += '<tr><td style="color:#666;padding:3px 0;">Fecha de Baja:</td><td>' + r.fechaBaja + '</td></tr>';
      html += '<tr><td style="color:#666;padding:3px 0;">Motivo:</td><td>' + (r.motivo === 'renuncia' ? 'Renuncia Voluntaria' : r.motivo === 'despido_justificado' ? 'Despido Justificado' : 'Despido Injustificado') + '</td></tr>';
      html += '</table>';
      
      html += '<table style="width:100%;border-collapse:collapse;font-size:12px;margin-bottom:15px;">';
      html += '<thead><tr style="background:#1e3a5f;color:#fff;"><th style="padding:8px;text-align:left;">Concepto</th><th style="padding:8px;text-align:right;">Monto</th></tr></thead><tbody>';
      r.conceptos.forEach(function(c) {
          html += '<tr style="border-bottom:1px solid #eee;"><td style="padding:6px 8px;">' + c.nombre + '</td><td style="padding:6px 8px;text-align:right;font-weight:bold;">$' + c.monto.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td></tr>';
      });
      html += '</tbody><tfoot><tr style="background:#f0fdf4;"><td style="padding:10px 8px;text-align:right;font-weight:bold;font-size:14px;">TOTAL ESTIMADO A PAGAR:</td><td style="padding:10px 8px;text-align:right;font-weight:bold;font-size:14px;color:#15803d;">$' + r.total.toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td></tr></tfoot>';
      html += '</table>';
      
      html += '<div style="background:#fff3cd;padding:10px;border:1px solid #ffc107;border-radius:4px;font-size:11px;color:#856404;margin-top:15px;">';
      html += '<strong>⚠️ Nota Legal:</strong> Este cálculo es una estimación basada en la Ley Federal del Trabajo (aguinaldo ' + r.diasAguinaldo + ' días, prima vacacional 25%, salario mínimo $278.80 zona general 2026). Se recomienda validación por el departamento legal o contable antes de proceder con el pago.';
      html += '</div>';
      html += '</div>';
      
      var element = document.createElement('div');
      element.innerHTML = html;
      
      var opt = {
        margin: [10, 10, 10, 10],
        filename: 'Finiquito_' + r.empleado.replace(/\s+/g, '_') + '_' + new Date().toISOString().slice(0, 10) + '.pdf',
        image: { type: 'jpeg', quality: 1 },
        html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
        jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' }
      };
      
      html2pdf().set(opt).from(element).save().catch(function(err) {
        alert('Error al generar PDF: ' + (err.message || ''));
      });
  }

  // ========================================================================
  // DOCUMENTOS DEL EMPLEADO
  // ========================================================================

  function cargarDocumentosEmpleado(empleado_id) {
    $('#lista-documentos-empleado').html('<div class="text-center text-muted py-2 small"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
    $.post('<?= base_url('rh/RecursosHumanos/documentos_listar') ?>', {
      empleado_id: empleado_id,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if (!result.success) {
        $('#lista-documentos-empleado').html('<div class="text-danger small">Error al cargar documentos</div>');
        return;
      }

      if (result.checklist) {
        var chk = result.checklist;
        var chkHtml = '<div class="card border-0 bg-light mb-2"><div class="card-body py-2 px-3">' +
          '<div class="d-flex justify-content-between align-items-center mb-1">' +
          '<small class="fw-bold text-uppercase text-muted">Checklist expediente</small>' +
          '<span class="badge bg-' + (chk.completo ? 'success' : 'warning') + '">' + chk.porcentaje + '%</span></div>' +
          '<div class="progress mb-2" style="height:6px;"><div class="progress-bar" style="width:' + chk.porcentaje + '%"></div></div>' +
          '<div class="d-flex flex-wrap gap-1">';
        chk.items.forEach(function(item) {
          chkHtml += '<span class="badge ' + (item.tiene ? 'bg-success' : 'bg-danger') + '" style="font-size:0.65rem;">' +
            (item.tiene ? '✓' : '✗') + ' ' + item.label + '</span>';
        });
        chkHtml += '</div></div></div>';
        $('#checklist-documentos-offcanvas').html(chkHtml);
      }

      if (!result.documentos.length) {
        $('#lista-documentos-empleado').html('<div class="alert alert-light border text-center py-3 mb-0"><i data-lucide="folder-open" style="width:24px;height:24px;" class="text-muted mb-1"></i><br><small class="text-muted">Sin documentos adjuntos</small></div>');
        if (typeof lucide !== 'undefined') lucide.createIcons();
        return;
      }
      var html = '<div class="list-group list-group-flush">';
      result.documentos.forEach(function(doc) {
        var icon = doc.ruta_archivo.match(/\.pdf$/i) ? 'file-text' : 'image';
        html += '<div class="list-group-item px-2 py-2">' +
          '<div class="d-flex align-items-start gap-2">' +
            '<i data-lucide="' + icon + '" class="text-primary flex-shrink-0 mt-1" style="width:16px;height:16px;"></i>' +
            '<div class="flex-grow-1 min-w-0">' +
              '<div class="fw-semibold small">' + doc.tipo_label + '</div>' +
              '<div class="text-muted text-truncate" style="font-size:0.72rem;">' + doc.nombre_archivo + '</div>' +
              '<div class="text-muted" style="font-size:0.68rem;">' + doc.fecha_subida + ' · ' + doc.tamano + '</div>' +
            '</div>' +
            '<div class="d-flex gap-1 flex-shrink-0">' +
              '<a href="' + doc.url + '" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-1" title="Ver"><i data-lucide="eye" style="width:14px;height:14px;"></i></a>' +
              '<button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="eliminarDocumentoEmpleado(' + doc.id + ')" title="Eliminar"><i data-lucide="trash-2" style="width:14px;height:14px;"></i></button>' +
            '</div>' +
          '</div></div>';
      });
      html += '</div>';
      $('#lista-documentos-empleado').html(html);
      if (typeof lucide !== 'undefined') lucide.createIcons();
    });
  }

  function abrirModalSubirDocumento() {
    if (!currentEmpleadoId) return;
    $('#doc_empleado_id').val(currentEmpleadoId);
    $('#formSubirDocumento')[0].reset();
    $('#doc_empleado_id').val(currentEmpleadoId);
    $('#modalSubirDocumento').modal('show');
    if (typeof lucide !== 'undefined') lucide.createIcons();
  }

  function subirDocumentoEmpleado() {
    if (!currentEmpleadoId) return;
    var formData = new FormData(document.getElementById('formSubirDocumento'));
    formData.append('peticion', 'ajax');
    formData.append('<?php echo $this->security->get_csrf_token_name();?>', '<?php echo $this->security->get_csrf_hash();?>');

    $.ajax({
      url: '<?= base_url('rh/RecursosHumanos/documento_subir') ?>',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(result) {
        result = JSON.parse(result);
        notifyShow(result.message, result.success ? 'success' : 'danger');
        if (result.success) {
          $('#modalSubirDocumento').modal('hide');
          cargarDocumentosEmpleado(currentEmpleadoId);
        }
      },
      error: function() {
        notifyShow('Error al subir el archivo', 'danger');
      }
    });
  }

  function eliminarDocumentoEmpleado(docId) {
    if (!confirm('¿Eliminar este documento del expediente?')) return;
    $.post('<?= base_url('rh/RecursosHumanos/documento_eliminar') ?>', {
      id: docId,
      empleado_id: currentEmpleadoId,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if (result.success) cargarDocumentosEmpleado(currentEmpleadoId);
    });
  }

  var vinculoEmpleadoModalId = null;

  function abrirModalVincularUsuario(empleadoId) {
    vinculoEmpleadoModalId = empleadoId;
    var nombre = $('#offcanvas-empleado-nombre').text() || ('Empleado #' + empleadoId);
    $('#vinculo-empleado-id').val(empleadoId);
    $('#vinculo-empleado-nombre').text(nombre);
    $('#vinculo-buscar-usuario').val('');
    $('#vinculo-usuarios-lista').html('<div class="list-group-item text-muted text-center py-4">Escriba para buscar usuarios disponibles</div>');
    $('#btn-desvincular-usuario').hide();
    buscarUsuariosVinculo();
    $('#modalVincularUsuario').modal('show');
  }

  function buscarUsuariosVinculo() {
    if (!vinculoEmpleadoModalId) return;
    $.post('<?= base_url('rh/RecursosHumanos/usuarios_buscar_ajax') ?>', {
      empleado_id: vinculoEmpleadoModalId,
      q: $('#vinculo-buscar-usuario').val(),
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      if (!result.success || !result.usuarios.length) {
        $('#vinculo-usuarios-lista').html('<div class="list-group-item text-muted text-center py-4">No hay usuarios disponibles</div>');
        return;
      }
      var html = '';
      var tieneVinculoActual = false;
      result.usuarios.forEach(function(u) {
        if (u.vinculado_a_este) tieneVinculoActual = true;
        var btn = u.ocupado
          ? '<span class="badge bg-secondary">Ocupado</span>'
          : '<button type="button" class="btn btn-sm btn-primary" onclick="confirmarVinculoUsuario(' + u.id + ')"><i class="fas fa-link"></i> Vincular</button>';
        html += '<div class="list-group-item d-flex justify-content-between align-items-center">' +
          '<div><strong>#' + u.id + '</strong> ' + u.nombre + '<br><small class="text-muted">' + u.username + '</small></div>' +
          btn + '</div>';
      });
      $('#vinculo-usuarios-lista').html(html);
      if (tieneVinculoActual) $('#btn-desvincular-usuario').show();
    });
  }

  function confirmarVinculoUsuario(usuarioId) {
    if (!vinculoEmpleadoModalId) return;
    $.post('<?= base_url('rh/RecursosHumanos/vincular_usuario_ajax') ?>', {
      empleado_id: vinculoEmpleadoModalId,
      usuario_id: usuarioId,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { notifyShow('Error al procesar respuesta', 'danger'); return; }
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if (result.success) {
        $('#modalVincularUsuario').modal('hide');
        if (typeof table !== 'undefined' && table.ajax) table.ajax.reload(null, false);
        empleado_detail(vinculoEmpleadoModalId);
      }
    });
  }

  function desvincularUsuarioEmpleado() {
    if (!vinculoEmpleadoModalId || !confirm('¿Desvincular el usuario ERP de este empleado?')) return;
    $.post('<?= base_url('rh/RecursosHumanos/desvincular_usuario_ajax') ?>', {
      empleado_id: vinculoEmpleadoModalId,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      notifyShow(result.message, result.success ? 'success' : 'warning');
      if (result.success) {
        $('#modalVincularUsuario').modal('hide');
        if (typeof table !== 'undefined' && table.ajax) table.ajax.reload(null, false);
        empleado_detail(vinculoEmpleadoModalId);
      }
    });
  }

  function abrirModalUsuariosSinEmpleado() {
    $('#lista-usuarios-sin-empleado').html('<div class="list-group-item text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
    $('#modalUsuariosSinEmpleado').modal('show');
    $.post('<?= base_url('rh/RecursosHumanos/usuarios_sin_empleado_ajax') ?>', {
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      if (!result.success || !result.usuarios.length) {
        $('#lista-usuarios-sin-empleado').html('<div class="list-group-item text-center text-muted py-4">Todos los usuarios activos ya tienen empleado vinculado</div>');
        return;
      }
      var html = '';
      result.usuarios.forEach(function(u) {
        html += '<div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">' +
          '<div><strong>#' + u.id + '</strong> ' + u.nombre + '<br><small class="text-muted">' + u.username + '</small></div>' +
          '<button type="button" class="btn btn-sm btn-success" onclick="crearEmpleadoDesdeUsuario(' + u.id + ')"><i class="fas fa-user-plus"></i> Crear empleado</button>' +
          '</div>';
      });
      $('#lista-usuarios-sin-empleado').html(html);
    });
  }

  function crearEmpleadoDesdeUsuario(usuarioId) {
    if (!confirm('¿Crear expediente de empleado para este usuario y vincularlo automáticamente?')) return;
    $.post('<?= base_url('rh/RecursosHumanos/crear_empleado_desde_usuario_ajax') ?>', {
      usuario_id: usuarioId,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { notifyShow('Error al procesar respuesta', 'danger'); return; }
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if (result.success) {
        $('#modalUsuariosSinEmpleado').modal('hide');
        if (typeof table !== 'undefined' && table.ajax) table.ajax.reload(null, false);
        if (result.empleado_id) empleado_detail(result.empleado_id);
      }
    });
  }

  $('#vinculo-buscar-usuario').on('keyup', function(e) {
    if (e.key === 'Enter') buscarUsuariosVinculo();
  });

  document.addEventListener('DOMContentLoaded', function() {
    var abrirVinculo = sessionStorage.getItem('abrirVinculoEmpleado');
    if (abrirVinculo && typeof empleado_detail === 'function') {
      sessionStorage.removeItem('abrirVinculoEmpleado');
      var empId = parseInt(abrirVinculo, 10);
      setTimeout(function() {
        empleado_detail(empId);
        setTimeout(function() { abrirModalVincularUsuario(empId); }, 600);
      }, 400);
    }
  });

</script>


