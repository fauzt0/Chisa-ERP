<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
<div class="row">
    <div class="col-lg-9">
        <div class="card card-outline card-success shadow">
            <div class="card-header bg-dark text-white d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0 text-white font-weight-bold" style="font-size:1.05rem;">
                        <i data-lucide="file-plus" class="me-2" style="width:20px;height:20px;"></i>
                        Generar Nuevo Contrato
                    </h5>
                    <small style="color: rgba(255,255,255,0.7);">
                        Empleado: <strong><?= htmlspecialchars($empleado->nombre . ' ' . $empleado->apellido_paterno . ' ' . $empleado->apellido_materno) ?></strong> 
                        | N° <?= $empleado->numero_empleado ?> 
                        | <?= $empleado->puesto ?>
                    </small>
                </div>
                <div class="btn-group mt-1 mt-md-0">
                    <button type="button" class="btn btn-light btn-sm" onclick="togglePreview()" id="btnPreviewToggle" style="color:#333;">
                        <i data-lucide="eye" style="width:16px;height:16px;"></i> Previsualizar
                    </button>
                    <button type="button" class="btn btn-light btn-sm" onclick="generarPDF()" style="color:#dc3545;">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-light btn-sm dropdown-toggle font-weight-bold" data-bs-toggle="dropdown" aria-expanded="false" style="color:#333;">
                            <i data-lucide="sparkles" style="width:16px;height:16px;"></i> Modelo Rápido
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header text-dark font-weight-bold" style="font-size:0.75rem;">— PLANTILLAS DEL SISTEMA —</h6></li>
                            <?php if(!empty($plantillas)): ?>
                                <?php foreach($plantillas as $p): ?>
                                    <li><a class="dropdown-item" href="#" onclick="cargarModeloRapido('plantilla', <?= $p->id ?>, '<?= htmlspecialchars($p->nombre, ENT_QUOTES) ?>')" style="cursor:pointer;">📄 <?= htmlspecialchars($p->nombre) ?></a></li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><span class="dropdown-item text-muted">Sin plantillas guardadas</span></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header text-dark font-weight-bold" style="font-size:0.75rem;">— MODELOS PREDEFINIDOS —</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="cargarModeloRapido('lft')" style="cursor:pointer;font-weight:600;">📜 Legal LFT Completo</a></li>
                            <li><a class="dropdown-item" href="#" onclick="cargarModeloRapido('ejecutivo')" style="cursor:pointer;font-weight:600;">💼 Contrato Ejecutivo</a></li>
                            <li><a class="dropdown-item" href="#" onclick="cargarModeloRapido('operativo')" style="cursor:pointer;font-weight:600;">🔧 Contrato Operativo</a></li>
                            <li><a class="dropdown-item" href="#" onclick="cargarModeloRapido('confidencialidad')" style="cursor:pointer;font-weight:600;">🔒 Acuerdo de Confidencialidad</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Preview Panel -->
                <div id="previewPanel" class="border-bottom p-4 bg-white" style="display:none; max-height: 500px; overflow-y: auto; box-shadow: inset 0 2px 8px rgba(0,0,0,0.1);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-muted">
                            <i data-lucide="maximize" style="width:16px;height:16px;"></i> 
                            Vista Previa — <span class="text-success">Variables ya reemplazadas con datos de <?= htmlspecialchars($empleado->nombre) ?></span>
                        </h6>
                        <div>
                            <select class="form-select form-select-sm d-inline-block w-auto me-2" id="previewScale" onchange="updatePreviewScale()">
                                <option value="0.5">50%</option>
                                <option value="0.75">75%</option>
                                <option value="1" selected>100%</option>
                            </select>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshPreview()">
                                <i data-lucide="refresh-cw" style="width:14px;height:14px;"></i>
                            </button>
                        </div>
                    </div>
                    <div id="previewContent" style="width:6.5in; max-width:100%; margin:0 auto; padding:0; font-family:'Times New Roman',serif; font-size:12pt; line-height:1.6; text-align:justify; color:#000; background:#fff; box-shadow:0 0 20px rgba(0,0,0,0.15); transform-origin:top center;">
                    </div>
                </div>

                <form action="<?= base_url('rh/RecursosHumanos/guardar_nuevo_contrato') ?>" method="POST" id="formContrato">
                    <input type="hidden" name="empleado_id" value="<?= $empleado->id ?>">
                    
                    <div class="p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-dark">TIPO DE CONTRATO <span class="text-danger">*</span></label>
                                <select class="form-select" name="tipo_contrato" required>
                                    <option value="Tiempo Indeterminado">📋 Tiempo Indeterminado</option>
                                    <option value="Tiempo Determinado">📅 Tiempo Determinado</option>
                                    <option value="Prueba (3 Meses)">⏳ Prueba (3 Meses)</option>
                                    <option value="Capacitación Inicial">🎓 Capacitación Inicial</option>
                                    <option value="Por Obra Determinada">🏗️ Por Obra Determinada</option>
                                    <option value="Sustitución">🔄 Sustitución de Personal</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-dark">PLANTILLA BASE</label>
                                <select class="form-select" name="plantilla_id" id="selectPlantilla">
                                    <option value="">-- Manual / Libre --</option>
                                    <?php if(!empty($plantillas)): ?>
                                        <?php foreach($plantillas as $p): ?>
                                            <option value="<?= $p->id ?>"><?= htmlspecialchars($p->nombre) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-dark">MOTIVO / REFERENCIA</label>
                                <input type="text" class="form-control" name="motivo" placeholder="Ej. Renovación anual, Cambio de puesto...">
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-2 d-flex justify-content-between align-items-center bg-light p-2 rounded">
                            <label class="form-label mb-0 fw-bold small text-dark">CONTENIDO DEL CONTRATO</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-dark btn-sm" onclick="insertarFirmas()" title="Insertar bloque de firmas">
                                    <i data-lucide="pen-line" style="width:14px;height:14px;"></i> Firmas
                                </button>
                                <button type="button" class="btn btn-outline-dark btn-sm" onclick="insertarMembrete()" title="Insertar membrete corporativo">
                                    <i data-lucide="image" style="width:14px;height:14px;"></i> Membrete
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="previsualizarConDatos()" title="Reemplazar variables con datos reales">
                                    <i data-lucide="replace" style="width:14px;height:14px;"></i> Reemplazar Variables
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <textarea id="editorContrato" name="contenido" rows="22"></textarea>
                        </div>
                        
                        <div class="row mb-4 p-3 bg-light border rounded">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="chkGuardarPlantilla" name="guardar_como_plantilla" value="1">
                                    <label class="form-check-label fw-bold text-dark" for="chkGuardarPlantilla">
                                        <i data-lucide="save" style="width:14px;height:14px;"></i> 
                                        Guardar estos cambios como una nueva plantilla global
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mt-2" id="divNombrePlantilla" style="display:none;">
                                <input type="text" class="form-control" name="nombre_nueva_plantilla" placeholder="Nombre para la nueva plantilla">
                                <small class="text-muted">Se guardará en la biblioteca de plantillas para uso futuro.</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <a href="<?= base_url('rh/RecursosHumanos') ?>" class="btn btn-outline-secondary">
                                <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success btn-lg px-4 font-weight-bold">
                                <i data-lucide="file-check" style="width:18px;height:18px;"></i> Generar y Guardar Contrato
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar: Datos del Empleado -->
    <div class="col-lg-3">
        <div class="card shadow-sm mb-4 border-dark">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0 small font-weight-bold text-white"><i data-lucide="user" style="width:16px;height:16px;" class="me-1"></i> DATOS DEL EMPLEADO</h6>
            </div>
            <div class="card-body p-2">
                <div class="list-group list-group-flush small">
                    <div class="list-group-item py-2 border-bottom"><strong>Nombre:</strong> <?= htmlspecialchars($empleado->nombre . ' ' . $empleado->apellido_paterno . ' ' . $empleado->apellido_materno) ?></div>
                    <div class="list-group-item py-2 border-bottom"><strong>RFC:</strong> <?= $empleado->rfc ?: '<span class="text-danger font-weight-bold">No registrado</span>' ?></div>
                    <div class="list-group-item py-2 border-bottom"><strong>CURP:</strong> <?= $empleado->curp ?: '<span class="text-danger font-weight-bold">No registrada</span>' ?></div>
                    <div class="list-group-item py-2 border-bottom"><strong>NSS:</strong> <?= $empleado->nss ?: '<span class="text-muted">N/A</span>' ?></div>
                    <div class="list-group-item py-2 border-bottom"><strong>Puesto:</strong> <?= htmlspecialchars($empleado->puesto) ?></div>
                    <div class="list-group-item py-2 border-bottom"><strong>Departamento:</strong> <?= htmlspecialchars($empleado->departamento_nombre ?? 'N/A') ?></div>
                    <div class="list-group-item py-2 border-bottom"><strong>Tipo:</strong> <?= htmlspecialchars($empleado->tipo_trabajador) ?></div>
                    <div class="list-group-item py-2 border-bottom"><strong>Salario:</strong> $<?= number_format($empleado->salario_base_mensual, 2) ?></div>
                    <div class="list-group-item py-2 border-bottom"><strong>Ingreso:</strong> <?= date('d/m/Y', strtotime($empleado->fecha_ingreso)) ?></div>
                    <div class="list-group-item py-2"><strong>N° Empleado:</strong> <?= $empleado->numero_empleado ?></div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm bg-light border-0">
            <div class="card-body p-3">
                <h6 class="small font-weight-bold text-dark mb-2"><i data-lucide="info" style="width:14px;height:14px;"></i> CONSEJOS</h6>
                <ul class="small text-dark mb-0 ps-3" style="line-height:1.6;">
                    <li class="mb-1">Elige una <strong>plantilla guardada</strong> o un <strong>modelo predefinido</strong> como base.</li>
                    <li class="mb-1">Las variables <code>{{...}}</code> se reemplazarán automáticamente con los datos del empleado.</li>
                    <li class="mb-1">Usa <strong>"Reemplazar Variables"</strong> para ver una previsualización con datos reales.</li>
                    <li class="mb-1">Puedes guardar tu contrato como <strong>nueva plantilla</strong> para reutilizarlo.</li>
                    <li class="mb-1">💡 El <strong>logo de la empresa</strong> se coloca automáticamente al inicio del contrato. La variable <code>{{logo_empresa}}</code> solo es necesaria si deseas insertar el logo dentro del cuerpo del documento.</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Historial -->
    <div class="col-12 mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0 small font-weight-bold text-white"><i data-lucide="history" style="width:16px;height:16px;" class="me-1"></i> HISTORIAL DE CONTRATOS</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="small font-weight-bold text-dark">Versión</th>
                                <th class="small font-weight-bold text-dark">Tipo</th>
                                <th class="small font-weight-bold text-dark">Fecha Inicio</th>
                                <th class="small font-weight-bold text-dark">Estatus</th>
                                <th class="small font-weight-bold text-dark">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($historial)): ?>
                                <?php foreach($historial as $h): ?>
                                    <tr>
                                        <td class="small">v<?= $h->version ?></td>
                                        <td class="small"><?= htmlspecialchars($h->tipo_contrato) ?></td>
                                        <td class="small"><?= date('d/m/Y', strtotime($h->fecha_inicio)) ?></td>
                                        <td><?= $h->vigente ? '<span class="badge bg-success">Vigente</span>' : '<span class="badge bg-secondary">Histórico</span>' ?></td>
                                        <td>
                                            <button type="button" class="btn btn-xs btn-outline-primary" onclick="verContrato(<?= $h->id ?>)">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-dark" onclick="descargarContratoHistorico(<?= $h->id ?>)">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted small py-3">Sin historial de contratos</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Contrato -->
<div class="modal fade" id="modalVerContrato" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content shadow-lg">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title text-white font-weight-bold"><i data-lucide="file-text" style="width:20px;height:20px;" class="me-2"></i>Detalle del Contrato</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-light p-4">
        <div id="contenidoContratoModal" class="p-4 bg-white shadow-sm" style="font-family:'Times New Roman',serif; font-size:12pt; line-height:1.6; text-align:justify; color:#000;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i data-lucide="x" style="width:16px;height:16px;"></i> Cerrar
        </button>
        <button type="button" class="btn btn-dark" onclick="imprimirContratoModal()">
            <i class="fas fa-file-pdf"></i> Descargar PDF
        </button>
      </div>
    </div>
  </div>
</div>