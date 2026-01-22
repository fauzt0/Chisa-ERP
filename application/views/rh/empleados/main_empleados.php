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

  <!-- Alerta de Datos Faltantes (Solo Visible para Administradores con Permiso) -->
  <?php if(!empty($response['datos_faltantes'])): ?>
  <div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="alert-icon">
      <i class="far fa-fw fa-bell"></i>
    </div>
    <div class="alert-message">
      <strong><i class="fas fa-exclamation-triangle"></i> Atención:</strong> Se han detectado empleados con datos fiscales incompletos (RFC, CURP o NSS).
      <ul class="mb-0 mt-1">
        <?php foreach(array_slice($response['datos_faltantes'], 0, 5) as $emp): ?>
          <li>
            <?php echo $emp->nombre . ' ' . $emp->apellido_paterno; ?>: 
            <?php 
              $faltantes = [];
              if(empty($emp->rfc)) $faltantes[] = 'RFC';
              if(empty($emp->curp)) $faltantes[] = 'CURP';
              if(empty($emp->nss)) $faltantes[] = 'NSS';
              echo implode(', ', $faltantes);
            ?>
             - <a href="<?php echo base_url('rh/RecursosHumanos/editar/'.$emp->id); ?>" class="alert-link">Corregir</a>
          </li>
        <?php endforeach; ?>
        <?php if(count($response['datos_faltantes']) > 5): ?>
          <li>... y <?php echo count($response['datos_faltantes']) - 5; ?> más.</li>
        <?php endif; ?>
      </ul>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasDetalleEmpleado" aria-labelledby="offcanvasDetalleEmpleadoLabel">
      <div class="offcanvas-header bg-light">
        <h5 id="offcanvasDetalleEmpleadoLabel"><i class="fas fa-user-circle"></i> Datos del Empleado</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
         <div id="actions" class="mb-3 text-center"></div>
         <hr>
          <div class="table-responsive">
            <table class="table table-sm my-2">
              <tbody id="detalles">
              </tbody>
            </table>
          </div>

          <!-- Balance de Vacaciones -->
          <div id="vacaciones-badge" style="display:none;" class="alert alert-info mt-3 p-3">
            <div class="border-bottom pb-2 mb-3">
              <strong class="text-primary text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">🏖️ Vacaciones</strong><br>
              <button class="btn btn-sm btn-primary shadow-sm mt-2" onclick="verVacaciones()" id="btn-ver-vacaciones">
                <i class="fas fa-calendar-alt"></i> Ver Detalle
              </button>
            </div>
            <div class="text-center">
              <h2 class="mb-1 fw-bold" id="dias-disponibles">-- días</h2>
              <small class="text-muted d-block" id="periodo-vacaciones">Cargando...</small>
            </div>
          </div>

          <!-- Balance de Incidencias -->
          <div id="incidencias-badge" style="display:none;" class="alert alert-warning mt-3 p-3">
            <div class="border-bottom pb-2 mb-3">
              <strong class="text-warning text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">⚠️ Incidencias</strong><br>
              <button class="btn btn-sm btn-warning shadow-sm mt-2" onclick="verIncidencias()" id="btn-ver-incidencias">
                <i class="fas fa-exclamation-triangle"></i> Ver Incidencias
              </button>
            </div>
            <div class="text-center">
              <h2 class="mb-1 fw-bold" id="total-incidencias">0</h2>
              <small class="text-muted d-block">Incidencias este año</small>
            </div>
          </div>

          <!-- Horario Laboral -->
          <div id="horario-badge" style="display:none;" class="alert alert-info mt-3 p-3">
            <div class="border-bottom pb-2 mb-3">
              <strong class="text-info text-uppercase" style="font-size: 0.85rem; letter-spacing: 0.5px;">🕐 Horario Laboral</strong><br>
              <button class="btn btn-sm btn-info shadow-sm mt-2" onclick="verHorario()" id="btn-ver-horario">
                <i class="fas fa-clock"></i> Ver/Editar Horario
              </button>
            </div>
            <div class="text-center">
              <h2 class="mb-1 fw-bold" id="horas-semana">0 hrs</h2>
              <small class="text-muted d-block" id="turno-empleado">Sin horario</small>
            </div>
          </div>

          <!-- Historial de Contratos -->
          <div class="mt-4">
            <h6 class="border-bottom pb-2">Historial de Contratos</h6>
            <ul class="timeline mt-2 mb-0" id="historial-contratos">
              <li class="text-muted">Selecciona un empleado para ver su historial</li>
            </ul>
          </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal para ver Contrato -->
<div class="modal fade" id="modalContrato" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-file-contract"></i> Contrato de Trabajo</h5>
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
        <button type="button" class="btn btn-info" onclick="imprimirContrato()">
          <i class="fas fa-print"></i> Imprimir
        </button>
        <button type="button" class="btn btn-primary" onclick="descargarPDF()">
          <i class="fas fa-file-pdf"></i> Descargar PDF
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Vacaciones: Detalle y Balance -->
<div class="modal fade" id="modalVacaciones" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-umbrella-beach"></i> Gestión de Vacaciones</h5>
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
<div class="modal fade" id="modalSolicitarVacaciones" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-calendar-plus"></i> Solicitar Vacaciones</h5>
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
<div class="modal fade" id="modalTodasSolicitudes" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="fas fa-tasks"></i> Solicitudes de Vacaciones Pendientes</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
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
<div class="modal fade" id="modalIncidencias" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Incidencias del Empleado</h5>
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
<div class="modal fade" id="modalRegistrarIncidencia" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-plus"></i> Registrar Nueva Incidencia</h5>
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

<!-- Modal: Ver/Editar Horario -->
<div class="modal fade" id="modalHorario" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-clock"></i> Horario Laboral del Empleado</h5>
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






<!-- Scripts necesarios para exportar tabla excel, pdf -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>


<script>
var table;
var export_filename = 'empleados-<?php echo date("Y-m-d");?>';

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
    $.post('<?=base_url();?>/rh/RecursosHumanos/detail',
    {
      'id':id,
      'peticion':'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>':'<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result){
      result = JSON.parse(result);
      if(result['response']!=null){
        $("#detalles").html(result['detail']);
        $("#actions").html(result['actions']);
        
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
      }else {
        notifyShow("Error al obtener los datos","danger");
      }

    });
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

  // Cargar historial de contratos cuando se selecciona un empleado
  function cargarHistorialContratos(empleado_id) {
    $.post('<?=base_url();?>/rh/RecursosHumanos/historial_contratos',
    {
      'empleado_id': empleado_id,
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

  function imprimirContrato() {
    if(!contratoActual) {
      notifyShow("No hay contrato cargado", "warning");
      return;
    }
    
    var contenido = document.getElementById('contrato-content').innerHTML;
    var ventana = window.open('', '_blank');
    var htmlDoc = '<html><head>' +
      '<title>Contrato - ' + contratoActual.tipo_contrato + '</title>' +
      '<style>' +
        'body { font-family: Times New Roman, serif; padding: 20px; line-height: 1.9; background: #f5f5f5; }' +
        '@media print { body { padding: 0; background: white; } }' +
        '@page { margin: 2cm; }' +
      '</style>' +
      '</head><body>' + contenido + '</body></html>';
    
    ventana.document.write(htmlDoc);
    ventana.document.close();
    ventana.focus();
    setTimeout(function() {
      ventana.print();
      ventana.close();
    }, 250);
  }

  function descargarPDF() {
    if(!contratoActual) {
      notifyShow("No hay contrato cargado", "warning");
      return;
    }
    
    notifyShow("Usa la función de imprimir y selecciona 'Guardar como PDF' en tu navegador", "info");
    imprimirContrato();
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
        $('#periodo-vacaciones').html('<button class="btn btn-sm btn-warning mt-2" onclick="generarPeriodoManual(' + empleado_id + ')"><i class="fas fa-plus"></i> Generar Período</button>');
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
  $('#fecha_inicio_vac, #fecha_fin_vac').on('change', function() {
    var inicio = $('#fecha_inicio_vac').val();
    var fin = $('#fecha_fin_vac').val();
    
    if(inicio && fin) {
      // Calcular días hábiles (aproximado - el servidor hará el cálculo exacto)
      var d1 = new Date(inicio);
      var d2 = new Date(fin);
      var dias = Math.ceil((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
      $('#dias_calculados').val(dias);
    }
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
    cargarTodasSolicitudes();
  }

  // Cargar todas las solicitudes pendientes
  function cargarTodasSolicitudes() {
    $.post('<?=base_url();?>/rh/RecursosHumanos/solicitudes_vacaciones_lista',
    {
      'estatus': 'Pendiente',
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result) {
      result = JSON.parse(result);
      if(result['success']) {
        var html = '';
        if(result['solicitudes'].length > 0) {
          result['solicitudes'].forEach(function(sol) {
            html += '<tr>' +
              '<td>' + sol.numero_empleado + ' - ' + sol.nombre + ' ' + sol.apellido_paterno + '</td>' +
              '<td>' + sol.fecha_solicitud + '</td>' +
              '<td>' + sol.fecha_inicio + ' al ' + sol.fecha_fin + '</td>' +
              '<td>' + sol.dias_solicitados + '</td>' +
              '<td>' + (sol.observaciones || '-') + '</td>' +
              '<td>' +
                '<button class="btn btn-sm btn-success me-1" onclick="procesarSolicitud(' + sol.id + ', \'aprobar\')"><i class="fas fa-check"></i></button>' +
                '<button class="btn btn-sm btn-danger" onclick="procesarSolicitud(' + sol.id + ', \'rechazar\')"><i class="fas fa-times"></i></button>' +
              '</td>' +
            '</tr>';
          });
        } else {
          html = '<tr><td colspan="6" class="text-center text-muted">No hay solicitudes pendientes</td></tr>';
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
          <td>${inc.descripcion || '-'}</td>
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
    const form = $('#formRegistrarIncidencia');
    const formData = form.serializeArray();
    const data = {};
    
    formData.forEach(item => {
      data[item.name] = item.value;
    });
    
    data.empleado_id = empleadoActualIncidencias;
    data.peticion = 'ajax';
    data['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';

    if (!data.tipo_incidencia || !data.fecha_incidencia) {
      notifyShow('Completa los campos requeridos', 'warning');
      return;
    }

    $.post('<?=base_url();?>/rh/RecursosHumanos/registrar_incidencia_ajax', data, function(result) {
      result = JSON.parse(result);
      if (result['success']) {
        notifyShow(result['message'], 'success');
        $('#modalRegistrarIncidencia').modal('hide');
        cargarIncidencias();
        cargarBadgeIncidencias(empleadoActualIncidencias);
      } else {
        notifyShow(result['message'], 'danger');
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

</script>


