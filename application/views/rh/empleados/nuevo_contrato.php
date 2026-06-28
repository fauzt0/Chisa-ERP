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
                        <i data-lucide="file-pdf" style="width:16px;height:16px;"></i> PDF
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
                    <div id="previewContent" style="width:800px; margin:0 auto; padding:40px; font-family:'Times New Roman',serif; font-size:12pt; line-height:1.6; text-align:justify; color:#000; background:#fff; box-shadow:0 0 20px rgba(0,0,0,0.15); transform-origin:top center;">
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
                                                <i data-lucide="eye" style="width:14px;height:14px;"></i> Ver
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-dark" onclick="descargarContratoHistorico(<?= $h->id ?>)">
                                                <i data-lucide="file-pdf" style="width:14px;height:14px;"></i> PDF
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
            <i data-lucide="file-pdf" style="width:16px;height:16px;"></i> Descargar PDF
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    var editorInstance;
    var modalVerInstance;
    var colorCorporativo = '<?= isset($plantilla) ? ($plantilla->color_corporativo ?? '#1a3a5c') : '#1a3a5c' ?>';
    var logoEmpresaHtml = '';

    // Inicializar TinyMCE
    tinymce.init({
        selector: '#editorContrato',
        height: 650,
        menubar: true,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount pagebreak',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table image | removeformat pagebreak | fullscreen code | help',
        content_style: 'body { font-family:"Times New Roman",serif; font-size:12pt; line-height:1.6; color:#000; } ' +
                       'h1,h2,h3 { color:#1a3a5c; font-family:"Georgia",serif; } ' +
                       'table { border-collapse:collapse; width:100%; } ' +
                       'td,th { padding:8px; vertical-align:top; }',
        setup: function (editor) {
            editorInstance = editor;
            // Una vez que TinyMCE está listo, configurar el listener del select
            configurarSelectPlantilla();
        }
    });
    
    lucide.createIcons();

    function configurarSelectPlantilla() {
        var select = document.getElementById('selectPlantilla');
        if (!select) return;
        select.addEventListener('change', function() {
            var plantillaId = this.value;
            var empleadoId = <?= $empleado->id ?>;
            if (!plantillaId) return;
            
            var editor = tinymce.get('editorContrato');
            if (!editor) { alert('Editor no disponible. Recarga la página.'); return; }
            
            editor.setContent('<p class="text-center text-muted">Cargando plantilla...</p>');
            
            $.post('<?= base_url('rh/RecursosHumanos/ajax_previsualizar_contrato') ?>', {
                empleado_id: empleadoId,
                plantilla_id: plantillaId
            }, function(response) {
                try {
                    var resp = JSON.parse(response);
                    if(resp.success) {
                        editor.setContent(resp.contenido);
                        if(typeof notifyShow === 'function') notifyShow('Plantilla cargada', 'success');
                    } else {
                        editor.setContent('<p class="text-danger">Error: ' + (resp.message || 'No se pudo cargar la plantilla') + '</p>');
                    }
                } catch(e) {
                    editor.setContent('<p class="text-danger">Error al procesar la respuesta del servidor.</p>');
                }
            }).fail(function() {
                editor.setContent('<p class="text-danger">Error de conexión al cargar la plantilla.</p>');
            });
        });
    }

    document.getElementById('chkGuardarPlantilla').addEventListener('change', function() {
        var div = document.getElementById('divNombrePlantilla');
        if(this.checked) {
            div.style.display = 'block';
            document.getElementsByName('nombre_nueva_plantilla')[0].required = true;
        } else {
            div.style.display = 'none';
            document.getElementsByName('nombre_nueva_plantilla')[0].required = false;
        }
    });

    function togglePreview() {
        var panel = document.getElementById('previewPanel');
        if (panel.style.display === 'none' || panel.style.display === '') {
            panel.style.display = 'block';
            document.getElementById('btnPreviewToggle').innerHTML = '<i data-lucide="eye-off" style="width:16px;height:16px;"></i> Ocultar';
            refreshPreview();
        } else {
            panel.style.display = 'none';
            document.getElementById('btnPreviewToggle').innerHTML = '<i data-lucide="eye" style="width:16px;height:16px;"></i> Previsualizar';
        }
        lucide.createIcons();
    }
    
    function updatePreviewScale() {
        var scale = document.getElementById('previewScale').value;
        document.getElementById('previewContent').style.transform = 'scale(' + scale + ')';
    }
    
    function refreshPreview() {
        var editor = tinymce.get('editorContrato');
        var content = editor ? editor.getContent() : '';
        content = replacePlaceholders(content);
        var previewHtml = '<style>body{font-family:"Times New Roman",serif;font-size:12pt;line-height:1.6;color:#000;}h1,h2,h3{color:'+colorCorporativo+';font-family:Georgia,serif;}table{border-collapse:collapse;width:100%;}td,th{padding:8px;}</style>'+content;
        document.getElementById('previewContent').innerHTML = previewHtml;
    }
    
    function replacePlaceholders(content) {
        var emp = {
            '{{nombre_completo}}': '<?= htmlspecialchars(addslashes($empleado->nombre . ' ' . $empleado->apellido_paterno . ' ' . $empleado->apellido_materno)) ?>',
            '{{rfc}}': '<?= addslashes($empleado->rfc ?? '') ?>',
            '{{curp}}': '<?= addslashes($empleado->curp ?? '') ?>',
            '{{nss}}': '<?= addslashes($empleado->nss ?? 'N/A') ?>',
            '{{puesto}}': '<?= htmlspecialchars(addslashes($empleado->puesto)) ?>',
            '{{departamento}}': '<?= htmlspecialchars(addslashes($empleado->departamento_nombre ?? 'Sin departamento')) ?>',
            '{{tipo_trabajador}}': '<?= addslashes($empleado->tipo_trabajador) ?>',
            '{{salario_base_mensual}}': '$<?= number_format($empleado->salario_base_mensual, 2) ?> MXN',
            '{{salario_base_diario}}': '$<?= number_format($empleado->salario_base_mensual / 30, 2) ?> MXN',
            '{{fecha_inicio}}': '<?= date('d/m/Y', strtotime($empleado->fecha_ingreso)) ?>',
            '{{fecha_generacion}}': '<?= date('d/m/Y H:i:s') ?>',
            '{{numero_empleado}}': '<?= $empleado->numero_empleado ?>',
            '{{nacionalidad}}': '<?= addslashes($empleado->nacionalidad ?? 'Mexicana') ?>',
            '{{edad}}': '<?= $empleado->fecha_nacimiento ? (new DateTime($empleado->fecha_nacimiento))->diff(new DateTime())->y : 'N/D' ?>',
            '{{genero}}': '<?= addslashes($empleado->genero ?? 'N/D') ?>',
            '{{sexo}}': '<?= addslashes($empleado->genero ?? 'N/D') ?>',
            '{{estado_civil}}': '<?= addslashes($empleado->estado_civil ?? 'Soltero(a)') ?>',
            '{{domicilio}}': '<?= htmlspecialchars(addslashes($empleado->direccion ?? 'No registrado')) ?>',
            '{{beneficiarios}}': '<?= htmlspecialchars(addslashes($empleado->beneficiarios ?? 'No designados')) ?>',
            '{{telefono}}': '<?= addslashes($empleado->telefono ?? 'N/A') ?>',
            '{{email}}': '<?= addslashes($empleado->email_personal ?? $empleado->email_corporativo ?? 'N/A') ?>',
            '{{domicilio_empresa}}': 'DOMICILIO FISCAL DE LA EMPRESA',
            '{{ciudad_contrato}}': 'CD. JUÁREZ, CHIHUAHUA',
            '{{lugar_pago}}': 'Transferencia Bancaria',
            '{{version}}': '<?= !empty($historial) ? max(array_column($historial, 'version')) + 1 : 1 ?>',
            '{{tipo_nomina}}': '<?= addslashes($empleado->tipo_nomina ?? 'Quincenal') ?>',
            '{{jornada_laboral}}': 'Tiempo Completo',
            '{{color_corporativo}}': colorCorporativo,
            '{{logo_empresa}}': logoEmpresaHtml,
        };
        var result = content;
        for (var key in emp) { 
            if (emp.hasOwnProperty(key)) {
                result = result.split(key).join(emp[key]); 
            }
        }
        return result;
    }

    function insertarFirmas() {
        var html = '<br><br><table style="width:100%;border-collapse:collapse;margin-top:60px;"><tr><td style="width:45%;text-align:center;vertical-align:top;"><div style="border-top:2px solid #333;width:220px;margin:0 auto;padding-top:8px;"><strong>{{nombre_completo}}</strong><br><small style="color:#666;">EL TRABAJADOR</small></div></td><td style="width:10%;"></td><td style="width:45%;text-align:center;vertical-align:top;"><div style="border-top:2px solid #333;width:220px;margin:0 auto;padding-top:8px;"><strong>CHISA RECUBRIMIENTOS</strong><br><small style="color:#666;">REPRESENTANTE LEGAL</small></div></td></tr></table>';
        tinymce.get('editorContrato').insertContent(html);
    }
    
    function insertarMembrete() {
        var html = '<table style="width:100%;border-bottom:3px solid {{color_corporativo}};margin-bottom:30px;padding-bottom:15px;"><tr><td style="text-align:center;"><h1 style="margin:0;font-size:18pt;letter-spacing:1px;">CHISA RECUBRIMIENTOS</h1><p style="margin:4px 0 0;font-size:9pt;color:#666;">{{domicilio_empresa}}</p></td></tr></table>';
        tinymce.get('editorContrato').insertContent(html);
    }
    
    function previsualizarConDatos() {
        var editor = tinymce.get('editorContrato');
        if (!editor) return;
        var content = editor.getContent();
        content = replacePlaceholders(content);
        editor.setContent(content);
        if(typeof notifyShow === 'function') notifyShow('Variables reemplazadas con datos reales', 'success');
        if (document.getElementById('previewPanel').style.display !== 'none') refreshPreview();
    }

    function cargarModeloRapido(tipo, plantillaId, nombre) {
        var editor = tinymce.get('editorContrato');
        if (!editor) { alert('Editor no disponible. Recarga la página.'); return; }
        
        var cur = editor.getContent().trim();
        if(cur !== '' && cur !== '<p class="text-center text-muted">Cargando plantilla...</p>') {
            if(!confirm('Esto reemplazará el contenido actual. ¿Continuar?')) return;
        }
        
        if (tipo === 'plantilla' && plantillaId) {
            editor.setContent('<p class="text-center text-muted">Cargando...</p>');
            $.post('<?= base_url('rh/RecursosHumanos/ajax_previsualizar_contrato') ?>', {
                empleado_id: <?= $empleado->id ?>, plantilla_id: plantillaId
            }, function(response) {
                try {
                    var resp = JSON.parse(response);
                    if(resp.success) { 
                        editor.setContent(resp.contenido); 
                        if(typeof notifyShow === 'function') notifyShow('Plantilla cargada', 'success'); 
                    } else { 
                        editor.setContent('<p class="text-danger">Error al cargar la plantilla.</p>');
                    }
                } catch(e) { 
                    editor.setContent('<p class="text-danger">Error al procesar la respuesta.</p>');
                }
            }).fail(function() {
                editor.setContent('<p class="text-danger">Error de conexión al cargar la plantilla.</p>');
            });
        } else {
            var html = getModeloHTML(tipo);
            if(html) editor.setContent(html);
        }
        if (document.getElementById('previewPanel').style.display !== 'none') setTimeout(refreshPreview, 500);
    }
    
    function getModeloHTML(tipo) {
        if (tipo === 'lft') return '<div style="text-align:center;margin-bottom:25px;padding-bottom:15px;border-bottom:3px solid {{color_corporativo}};"><h1 style="font-size:18pt;color:{{color_corporativo}};">CHISA RECUBRIMIENTOS</h1><p style="font-size:9pt;color:#777;">{{domicilio_empresa}}</p></div><h2 style="text-align:center;font-size:15pt;color:{{color_corporativo}};">CONTRATO INDIVIDUAL DE TRABAJO</h2><h3 style="text-align:center;font-size:11pt;font-weight:normal;color:#555;">POR TIEMPO <strong>{{tipo_contrato}}</strong></h3><p style="text-align:justify;">CONTRATO QUE CELEBRAN <strong>CHISA RECUBRIMIENTOS</strong> Y <strong>{{nombre_completo}}</strong> AL TENOR DE LAS SIGUIENTES CLÁUSULAS:</p><p style="text-align:justify;"><strong>PRIMERA.-</strong> Puesto: <strong>{{puesto}}</strong> en <strong>{{departamento}}</strong>.</p><p style="text-align:justify;"><strong>SEGUNDA.-</strong> Salario: <strong>{{salario_base_mensual}}</strong> ({{salario_base_diario}} diarios).</p><p style="text-align:justify;"><strong>TERCERA.-</strong> Jornada: <strong>{{jornada_laboral}}</strong>.</p><p style="text-align:center;margin:30px 0;">Firmado en {{ciudad_contrato}} a {{fecha_generacion}}.</p>';
        if (tipo === 'ejecutivo') return '<div style="background:linear-gradient(135deg,{{color_corporativo}},#2a5078);color:#fff;padding:25px;text-align:center;margin-bottom:25px;"><h1 style="color:#fff;margin:0;">CONTRATO LABORAL EJECUTIVO</h1></div><p><strong>Empleado:</strong> {{nombre_completo}} | <strong>Puesto:</strong> {{puesto}} | <strong>N°:</strong> {{numero_empleado}}</p><h3>1. DISPOSICIONES GENERALES</h3><p style="text-align:justify;">CHISA RECUBRIMIENTOS contrata a {{nombre_completo}} como <strong>{{tipo_trabajador}}</strong> de confianza.</p><h3>2. COMPENSACIÓN</h3><p style="text-align:justify;">Salario mensual: <strong>{{salario_base_mensual}}</strong>. Pago: <strong>{{tipo_nomina}}</strong>.</p>';
        if (tipo === 'operativo') return '<table style="width:100%;border-bottom:2px solid {{color_corporativo}};margin-bottom:20px;"><tr><td><h2 style="color:{{color_corporativo}};">CHISA RECUBRIMIENTOS</h2></td><td style="text-align:right;">Contrato N° {{numero_empleado}}-{{version}}</td></tr></table><h2 style="text-align:center;">CONTRATO INDIVIDUAL DE TRABAJO<br><span style="font-size:11pt;font-weight:normal;">Personal Operativo</span></h2><p style="text-align:justify;">En {{ciudad_contrato}}, a {{fecha_generacion}}, comparecen <strong>CHISA RECUBRIMIENTOS</strong> y <strong>{{nombre_completo}}</strong> para celebrar contrato por tiempo <strong>{{tipo_contrato}}</strong>.</p><p><strong>PRIMERA.-</strong> Puesto: <strong>{{puesto}}</strong> en <strong>{{departamento}}</strong>.</p><p><strong>SEGUNDA.-</strong> Salario: <strong>{{salario_base_mensual}}</strong>.</p><p style="text-align:center;margin:30px 0;">Leído y firmado de conformidad.</p>';
        if (tipo === 'confidencialidad') return '<div style="border:2px solid {{color_corporativo}};padding:25px;text-align:center;margin-bottom:25px;"><h1 style="color:{{color_corporativo}};">ACUERDO DE CONFIDENCIALIDAD</h1><p style="color:#555;">ANEXO AL CONTRATO INDIVIDUAL DE TRABAJO</p></div><p style="text-align:justify;">En {{ciudad_contrato}}, a {{fecha_generacion}}, <strong>CHISA RECUBRIMIENTOS</strong> y <strong>{{nombre_completo}}</strong> acuerdan:</p><p style="text-align:justify;"><strong>1.</strong> Toda información técnica, comercial y de procesos es confidencial.</p><p style="text-align:justify;"><strong>2.</strong> El empleado se obliga a no divulgar dicha información.</p><p style="text-align:justify;"><strong>3.</strong> Vigencia: 5 años posteriores a la terminación laboral.</p>';
        return '';
    }

    function generarPDF() {
        var editor = tinymce.get('editorContrato');
        if (!editor) { alert('Editor no disponible'); return; }
        var c = editor.getContent();
        if(!c || c === '<p>&nbsp;</p>') { alert('El contenido está vacío'); return; }
        // Reemplazar variables antes de generar el PDF
        c = replacePlaceholders(c);
        generarPDFDesdeHtml(c, 'Contrato_<?= $empleado->numero_empleado ?>.pdf');
    }
    
    function generarPDFDesdeHtml(html, filename) {
        // Convertir texto plano a HTML (saltos de línea)
        if (!/<[a-z][\s\S]*>/i.test(html)) {
            html = html.replace(/\n/g, '<br>');
        }
        
        // Reemplazar variable de color si está presente
        html = html.replace(/\{\{color_corporativo\}\}/g, colorCorporativo);
        
        var el = document.createElement('div'); 
        el.innerHTML = html;
        Object.assign(el.style, {
            padding:'30px 40px',
            fontFamily:'"Times New Roman",Times,serif',
            fontSize:'12pt',
            lineHeight:'1.6',
            textAlign:'justify',
            color:'#1a1a1a',
            backgroundColor:'#fff',
            boxSizing:'border-box'
        });
        el.querySelectorAll('h1,h2,h3').forEach(function(h){
            h.style.color = colorCorporativo;
            h.style.fontFamily = '"Georgia","Times New Roman",serif';
            h.style.marginBottom = '10px';
            h.style.marginTop = '20px';
        });
        el.querySelectorAll('img').forEach(function(i){
            i.style.maxWidth = '140px';
            i.style.maxHeight = '60px';
            i.style.height = 'auto';
        });
        var wr = document.createElement('div'); 
        wr.style.border = '2px solid ' + colorCorporativo; 
        wr.style.padding = '20px'; 
        wr.style.backgroundColor = '#fff'; 
        wr.appendChild(el);
        
        html2pdf().set({
            margin:[15,15,20,15],
            filename:filename,
            image:{type:'jpeg',quality:0.95},
            html2canvas:{scale:1.5,useCORS:true,scrollY:0,letterRendering:true,logging:false},
            jsPDF:{unit:'mm',format:'letter',orientation:'portrait'},
            pagebreak:{mode:['css','legacy']}
        }).from(wr).save().catch(function(e){
            console.error(e);
            alert('Error al generar PDF: ' + (e.message || 'Error desconocido'));
        });
    }
    
    function verContrato(id) {
        if(!modalVerInstance) { 
            modalVerInstance = new bootstrap.Modal(document.getElementById('modalVerContrato')); 
        }
        var modalBody = document.getElementById('contenidoContratoModal');
        modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div><br>Cargando...</div>';
        modalVerInstance.show();
        
        $.get('<?= base_url('rh/RecursosHumanos/ajax_get_contrato/') ?>'+id, function(r) {
            try { 
                var resp = JSON.parse(r); 
                if(resp.success) {
                    var contenido = resp.contenido;
                    // Detectar si es texto plano (sin tags HTML) y convertirlo
                    if (contenido && !/<[a-z][\s\S]*>/i.test(contenido)) {
                        contenido = contenido.replace(/\n/g, '<br>');
                        contenido = '<div style="white-space:pre-line;font-family:\'Times New Roman\',serif;">' + contenido + '</div>';
                    }
                    modalBody.innerHTML = contenido;
                } else {
                    modalBody.innerHTML = '<div class="alert alert-danger">'+ (resp.message || 'Error al cargar') +'</div>';
                }
                lucide.createIcons();
            }
            catch(e) { 
                modalBody.innerHTML = '<div class="alert alert-danger">Error al procesar los datos del contrato.</div>'; 
            }
        }).fail(function() {
            modalBody.innerHTML = '<div class="alert alert-danger">Error de conexión al cargar el contrato.</div>';
        });
    }
    
    function descargarContratoHistorico(id) {
        $.get('<?= base_url('rh/RecursosHumanos/ajax_get_contrato/') ?>'+id, function(r) {
            try { 
                var resp = JSON.parse(r); 
                if(resp.success) {
                    generarPDFDesdeHtml(resp.contenido, 'Contrato_v'+id+'.pdf');
                } else {
                    alert('Error: ' + (resp.message || 'No se pudo obtener el contrato'));
                }
            }
            catch(e) { alert('Error al procesar los datos del contrato.'); }
        }).fail(function() {
            alert('Error de conexión al descargar el contrato.');
        });
    }
    
    function imprimirContratoModal() {
        var contenido = document.getElementById('contenidoContratoModal').innerHTML;
        if (!contenido || contenido.indexOf('spinner-border') !== -1) {
            alert('Espere a que cargue el contenido.');
            return;
        }
        generarPDFDesdeHtml(contenido, 'Contrato_Historico.pdf');
    }
</script>