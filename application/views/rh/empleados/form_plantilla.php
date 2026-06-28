<div class="row">
    <div class="col-md-9">
        <div class="card card-outline card-primary shadow">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0 text-white font-weight-bold" style="font-size:1.05rem;">
                        <i data-lucide="file-text" class="me-2" style="width:20px;height:20px;"></i>
                        <?= isset($plantilla) ? '📝 Editar Plantilla' : '📄 Nueva Plantilla' ?>
                    </h5>
                    <small class="text-white-50">Diseñe contratos profesionales con variables inteligentes</small>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-light btn-sm" onclick="togglePreview()" id="btnPreviewToggle">
                        <i data-lucide="eye" style="width:16px;height:16px;"></i> Previsualizar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="generarPDF()">
                        <i data-lucide="file-pdf" style="width:16px;height:16px;"></i> PDF
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i data-lucide="sparkles" style="width:16px;height:16px;"></i> Cargar Modelo
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><h6 class="dropdown-header text-muted small">— MODELOS PROFESIONALES —</h6></li>
                            <li><a class="dropdown-item" href="#" onclick="cargarModelo('lft')"><strong>📜 Legal LFT Completo</strong><br><small class="text-muted">Contrato individual conforme a Ley Federal del Trabajo</small></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="cargarModelo('ejecutivo')"><strong>💼 Contrato Ejecutivo</strong><br><small class="text-muted">Para puestos administrativos y de confianza</small></a></li>
                            <li><a class="dropdown-item" href="#" onclick="cargarModelo('operativo')"><strong>🔧 Contrato Operativo</strong><br><small class="text-muted">Para personal de producción y operaciones</small></a></li>
                            <li><a class="dropdown-item" href="#" onclick="cargarModelo('confidencialidad')"><strong>🔒 Acuerdo de Confidencialidad</strong><br><small class="text-muted">Anexo de protección de información</small></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Preview Panel (toggled) -->
                <div id="previewPanel" class="border-bottom p-4 bg-white" style="display:none; max-height: 500px; overflow-y: auto; box-shadow: inset 0 2px 8px rgba(0,0,0,0.1);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-muted"><i data-lucide="maximize" style="width:16px;height:16px;"></i> Vista Previa</h6>
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

                <form action="<?= base_url('rh/RecursosHumanos/guardar_plantilla') ?>" method="POST" id="formPlantilla" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= isset($plantilla) ? $plantilla->id : '' ?>">
                    
                    <div class="p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">NOMBRE DE LA PLANTILLA <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nombre" 
                                       value="<?= isset($plantilla) ? htmlspecialchars($plantilla->nombre, ENT_QUOTES) : '' ?>" 
                                       placeholder="Ej. Contrato Individual LFT 2024" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-muted">LOGO (OPCIONAL)</label>
                                <input type="file" class="form-control" name="logo" accept="image/*">
                                <?php if(isset($plantilla) && !empty($plantilla->logo)): ?>
                                    <small class="text-success d-block mt-1"><i class="fas fa-check-circle"></i> Logo cargado</small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-muted">COLOR CORPORATIVO</label>
                                <input type="color" class="form-control form-control-color w-100" 
                                       name="color_corporativo" 
                                       value="<?= isset($plantilla) ? ($plantilla->color_corporativo ?? '#1a3a5c') : '#1a3a5c' ?>" 
                                       title="Color para encabezados y bordes del contrato">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">DOMICILIO FISCAL DE LA EMPRESA</label>
                            <input type="text" class="form-control" name="domicilio_empresa" 
                                   value="<?= isset($plantilla) ? htmlspecialchars($plantilla->domicilio_empresa ?? '', ENT_QUOTES) : '' ?>" 
                                   placeholder="Calle, Número, Colonia, C.P., Ciudad, Estado">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">DESCRIPCIÓN INTERNA</label>
                            <input type="text" class="form-control" name="descripcion" 
                                   value="<?= isset($plantilla) ? htmlspecialchars($plantilla->descripcion ?? '', ENT_QUOTES) : '' ?>" 
                                   placeholder="Ej. Contrato base para todo el personal operativo">
                        </div>
                        
                        <div class="mb-2 d-flex justify-content-between align-items-center bg-light p-2 rounded">
                            <label class="form-label mb-0 fw-bold small text-muted">CONTENIDO DEL CONTRATO</label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-dark" onclick="insertarFirmas()" title="Insertar bloque de firmas">
                                    <i data-lucide="pen-line" style="width:14px;height:14px;"></i> Firmas
                                </button>
                                <button type="button" class="btn btn-outline-dark" onclick="insertarMembrete()" title="Insertar membrete corporativo">
                                    <i data-lucide="image" style="width:14px;height:14px;"></i> Membrete
                                </button>
                                <button type="button" class="btn btn-outline-dark" onclick="insertarSello()" title="Insertar bloque de testigos y sello">
                                    <i data-lucide="stamp" style="width:14px;height:14px;"></i> Testigos
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <textarea id="editorContenido" name="contenido" rows="22"><?= isset($plantilla) ? $plantilla->contenido : '' ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <a href="<?= base_url('rh/RecursosHumanos/plantillas') ?>" class="btn btn-outline-secondary">
                                <i data-lucide="arrow-left" style="width:16px;height:16px;"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i data-lucide="save" style="width:18px;height:18px;"></i> Guardar Plantilla
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar: Variables y Tips -->
    <div class="col-md-3">
        <!-- Panel de Variables -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0 small fw-bold text-white"><i data-lucide="braces" style="width:16px;height:16px;" class="me-1"></i> VARIABLES DINÁMICAS</h6>
            </div>
            <div class="card-body p-0">
                <p class="small text-muted px-3 pt-2 mb-1">Haz clic para copiar al portapapeles:</p>
                <div class="list-group list-group-flush small" style="max-height: 420px; overflow-y: auto;">
                    <div class="list-group-item bg-light fw-bold small py-1">👤 Datos Personales</div>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{nombre_completo}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{rfc}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{curp}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{nss}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{nacionalidad}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{edad}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{genero}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{sexo}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{estado_civil}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{domicilio}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{beneficiarios}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{telefono}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{email}}</a>
                    
                    <div class="list-group-item bg-light fw-bold small py-1">💼 Datos Laborales</div>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{puesto}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{departamento}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{tipo_trabajador}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{tipo_contrato}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{tipo_nomina}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{jornada_laboral}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{salario_base_mensual}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{salario_base_diario}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{lugar_pago}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{fecha_inicio}}</a>
                    
                    <div class="list-group-item bg-light fw-bold small py-1">📋 Contrato</div>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{version}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{motivo_cambio}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{fecha_generacion}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{domicilio_empresa}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{ciudad_contrato}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{numero_empleado}}</a>
                    
                    <div class="list-group-item bg-light fw-bold small py-1">✍️ Firmas</div>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{firma_empleado_espacio}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{firma_empresa_espacio}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{firma_testigo1}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var py-1">{{firma_testigo2}}</a>
                </div>
            </div>
        </div>
        
        <!-- Tips -->
        <div class="card shadow-sm bg-white border">
            <div class="card-body p-3">
                <h6 class="small fw-bold text-dark mb-2"><i data-lucide="info" style="width:14px;height:14px;"></i> CONSEJOS</h6>
                <ul class="small text-dark mb-0 ps-3" style="line-height:1.6;">
                    <li class="mb-1">Usa <code>{{nombre_completo}}</code> para el nombre automático del empleado.</li>
                    <li class="mb-1">El bloque de firmas se inserta al final con <strong>"Firmas"</strong>.</li>
                    <li class="mb-1">Elige un color corporativo para encabezados profesionales.</li>
                    <li class="mb-1">Previsualiza antes de guardar con el botón <strong>"Previsualizar"</strong>.</li>
                    <li class="mb-1">El PDF se genera en formato Carta con márgenes profesionales.</li>
                    <li class="mb-1">💡 El <strong>logo de la empresa</strong> que subas aquí se colocará automáticamente al inicio del contrato. La variable <code>{{logo_empresa}}</code> solo es necesaria si deseas insertar el logo manualmente dentro del cuerpo.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Hidden iframe for PDF preview -->
<iframe id="pdfPreviewFrame" style="display:none; width:100%; height:600px;"></iframe>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    var editorInstance;
    
    tinymce.init({
        selector: '#editorContenido',
        height: 650,
        menubar: true,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount pagebreak',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table image | removeformat pagebreak | fullscreen code | help',
        content_style: 'body { font-family:"Times New Roman",serif; font-size:12pt; line-height:1.6; color:#000; } ' +
                       'h1,h2,h3 { color:#1a3a5c; font-family:"Georgia",serif; } ' +
                       'table { border-collapse:collapse; width:100%; } ' +
                       'td,th { padding:8px; vertical-align:top; }',
        setup: function(editor) {
            editorInstance = editor;
        }
    });
    
    // Inicializar iconos Lucide
    lucide.createIcons();
    
    // Copy variable to clipboard
    document.querySelectorAll('.copy-var').forEach(item => {
        item.addEventListener('click', event => {
            event.preventDefault();
            const text = item.textContent;
            navigator.clipboard.writeText(text).then(function() {
                if(typeof notifyShow === 'function') {
                    notifyShow('Variable copiada: ' + text, 'success');
                }
                // Feedback visual temporal
                item.classList.add('bg-success', 'text-white');
                setTimeout(() => item.classList.remove('bg-success', 'text-white'), 800);
            });
        });
    });

    // === TOGGLE PREVIEW ===
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
        var content = tinymce.get('editorContenido').getContent();
        var colorCorp = document.querySelector('[name="color_corporativo"]').value || '#1a3a5c';
        
        var previewHtml = '<style>' +
            'body { font-family:"Times New Roman",serif; font-size:12pt; line-height:1.6; color:#000; }' +
            'h1,h2,h3 { color:' + colorCorp + '; font-family:"Georgia",serif; }' +
            'table { border-collapse:collapse; width:100%; }' +
            'td, th { padding:8px; }' +
            '@media print { body { margin:0; } }' +
            '</style>' + content;
        
        document.getElementById('previewContent').innerHTML = previewHtml;
    }

    // === INSERT BLOCKS ===
    function insertarFirmas() {
        var html = '<br><br>' +
            '<table style="width:100%; border-collapse:collapse; margin-top:60px;">' +
            '<tr>' +
            '<td style="width:45%; text-align:center; vertical-align:top;">' +
            '<div style="border-top:2px solid #333; width:220px; margin:0 auto; padding-top:8px;">' +
            '<strong>{{nombre_completo}}</strong><br><small style="color:#666;">EL TRABAJADOR</small>' +
            '</div></td>' +
            '<td style="width:10%;"></td>' +
            '<td style="width:45%; text-align:center; vertical-align:top;">' +
            '<div style="border-top:2px solid #333; width:220px; margin:0 auto; padding-top:8px;">' +
            '<strong>CHISA RECUBRIMIENTOS</strong><br><small style="color:#666;">REPRESENTANTE LEGAL</small>' +
            '</div></td>' +
            '</tr>' +
            '</table>';
        tinymce.get('editorContenido').insertContent(html);
    }
    
    function insertarMembrete() {
        var html = '<table style="width:100%; border-bottom:3px solid {{color_corporativo}}; margin-bottom:30px; padding-bottom:15px;">' +
            '<tr>' +
            '<td style="text-align:center;">' +
            '<h1 style="margin:0; font-size:18pt; letter-spacing:1px;">CHISA RECUBRIMIENTOS</h1>' +
            '<p style="margin:4px 0 0; font-size:9pt; color:#666;">{{domicilio_empresa}}</p>' +
            '<p style="margin:2px 0 0; font-size:9pt; color:#666;">RFC: CHI-XXXXXX-XXX | Registro Patronal IMSS: XXXXXXXXX</p>' +
            '</td>' +
            '</tr>' +
            '</table>';
        tinymce.get('editorContenido').insertContent(html);
    }
    
    function insertarSello() {
        var html = '<br><table style="width:100%; border-collapse:collapse; margin-top:40px;">' +
            '<tr>' +
            '<td style="width:33%; text-align:center; vertical-align:top;">' +
            '<div style="border-top:1px solid #999; width:180px; margin:0 auto; padding-top:5px;">' +
            '<small style="color:#888;">TESTIGO 1<br>Nombre y Firma</small></div></td>' +
            '<td style="width:33%; text-align:center; vertical-align:top;">' +
            '<div style="border:2px dashed #ccc; width:100px; height:80px; margin:0 auto; border-radius:8px; display:flex; align-items:center; justify-content:center;">' +
            '<small style="color:#ccc;">SELLO<br>EMPRESA</small></div></td>' +
            '<td style="width:33%; text-align:center; vertical-align:top;">' +
            '<div style="border-top:1px solid #999; width:180px; margin:0 auto; padding-top:5px;">' +
            '<small style="color:#888;">TESTIGO 2<br>Nombre y Firma</small></div></td>' +
            '</tr></table>';
        tinymce.get('editorContenido').insertContent(html);
    }

    // === GENERATE PDF ===
    function generarPDF() {
        const contenido = tinymce.get('editorContenido').getContent();
        if(!contenido) {
            alert('El contrato está vacío');
            return;
        }
        // Reemplazar variable de color antes de generar PDF
        var colorCorp = document.querySelector('[name="color_corporativo"]')?.value || '#1a3a5c';
        var html = contenido.replace(/\{\{color_corporativo\}\}/g, colorCorp);
        generarPDFDesdeHtml(html, 'Plantilla_Contrato.pdf');
    }
    
    function generarPDFDesdeHtml(html, filename) {
        var colorCorp = document.querySelector('[name="color_corporativo"]')?.value || '#1a3a5c';
        
        var element = document.createElement('div');
        element.innerHTML = html;
        
        // Professional document styling - SIN width fijo, padding reducido
        Object.assign(element.style, {
            padding: '30px 40px',
            fontFamily: '"Times New Roman", Times, serif',
            fontSize: '12pt',
            lineHeight: '1.6',
            textAlign: 'justify',
            color: '#1a1a1a',
            backgroundColor: '#ffffff',
            boxSizing: 'border-box'
        });
        
        // Style all headings professionally
        var headings = element.querySelectorAll('h1, h2, h3');
        headings.forEach(function(h) {
            h.style.color = colorCorp;
            h.style.fontFamily = '"Georgia", "Times New Roman", serif';
            h.style.marginBottom = '10px';
            h.style.marginTop = '20px';
        });
        
        // Style tables
        var tables = element.querySelectorAll('table');
        tables.forEach(function(t) {
            t.style.borderCollapse = 'collapse';
            t.style.width = '100%';
        });
        
        // Style images (logo) - más pequeño
        var imgs = element.querySelectorAll('img');
        imgs.forEach(function(img) {
            img.style.maxWidth = '140px';
            img.style.maxHeight = '60px';
            img.style.height = 'auto';
            img.style.display = 'block';
            img.style.margin = '0 auto 15px';
        });

        // Add watermark-style page border
        var wrapper = document.createElement('div');
        wrapper.style.border = '2px solid ' + colorCorp;
        wrapper.style.padding = '20px';
        wrapper.style.backgroundColor = '#fff';
        wrapper.appendChild(element);
        
        var opt = {
            margin:       [15, 15, 20, 15],
            filename:     filename,
            image:        { type: 'jpeg', quality: 0.95 },
            html2canvas:  { 
                scale: 1.5,
                useCORS: true,
                scrollY: 0,
                letterRendering: true,
                logging: false
            },
            jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' },
            pagebreak:    { mode: ['css', 'legacy'] }
        };
        
        html2pdf().set(opt).from(wrapper).save().catch(function(err) {
            console.error('Error generando PDF:', err);
            alert('Error al generar PDF. Intente de nuevo.');
        });
    }

    // === MODEL LOADERS ===
    function cargarModelo(tipo) {
        if(tinymce.get('editorContenido').getContent().trim() !== '' && 
           !confirm('⚠️ Esto reemplazará el contenido actual. ¿Deseas continuar?')) {
            return;
        }

        var html = '';
        
        if (tipo === 'lft') {
            html = `
<!-- MEMBRETE CORPORATIVO -->
<div style="text-align:center; margin-bottom:30px; padding-bottom:20px; border-bottom:3px solid {{color_corporativo}};">
  {{logo_empresa}}
  <h1 style="margin:10px 0 5px; font-size:20pt; letter-spacing:2px; color:{{color_corporativo}}; font-family:Georgia,serif;">CHISA RECUBRIMIENTOS</h1>
  <p style="font-size:9pt; color:#777; margin:2px 0;">{{domicilio_empresa}}</p>
  <p style="font-size:9pt; color:#999; margin:0;">RFC: CHI-XXXXXX-XXX &nbsp;|&nbsp; Reg. Patronal IMSS: XXXXXXXXX</p>
</div>

<h2 style="text-align:center; font-size:16pt; margin-bottom:25px; color:{{color_corporativo}}; font-family:Georgia,serif;">CONTRATO INDIVIDUAL DE TRABAJO</h2>
<h3 style="text-align:center; font-size:12pt; font-weight:normal; color:#555; margin-bottom:30px;">POR TIEMPO <strong>{{tipo_contrato}}</strong></h3>

<p style="text-align:justify; margin-bottom:20px;">
CONTRATO INDIVIDUAL DE TRABAJO POR TIEMPO <strong>{{tipo_contrato}}</strong> QUE CELEBRAN POR UNA PARTE <strong>CHISA RECUBRIMIENTOS</strong>, SOCIEDAD ANÓNIMA DE CAPITAL VARIABLE, (EN LO SUCESIVO "LA EMPRESA" O "EL PATRÓN"), CON DOMICILIO FISCAL UBICADO EN <strong>{{domicilio_empresa}}</strong>, REPRESENTADA EN ESTE ACTO POR SU REPRESENTANTE LEGAL DEBIDAMENTE ACREDITADO; Y POR LA OTRA PARTE, EL(LA) C. <strong>{{nombre_completo}}</strong> (EN LO SUCESIVO "EL TRABAJADOR" O "LA TRABAJADORA"), QUIENES MANIFIESTAN TENER CAPACIDAD LEGAL PARA OBLIGARSE, AL TENOR DE LAS SIGUIENTES:
</p>

<h3 style="color:{{color_corporativo}}; border-bottom:1px solid #ddd; padding-bottom:4px; margin-top:30px;">DECLARACIONES</h3>

<ol type="I" style="padding-left:25px; text-align:justify;">
  <li style="margin-bottom:12px;"><strong>DECLARA "LA EMPRESA":</strong><br>
  a) Ser una Sociedad Mercantil mexicana, legalmente constituida conforme a las leyes de los Estados Unidos Mexicanos.<br>
  b) Tener su domicilio fiscal en <strong>{{domicilio_empresa}}</strong>.<br>
  c) Estar inscrita ante el Registro Federal de Contribuyentes y el Instituto Mexicano del Seguro Social.<br>
  d) Requerir los servicios personales y subordinados de "EL TRABAJADOR" para el desempeño de sus actividades.</li>

  <li style="margin-bottom:12px;"><strong>DECLARA "EL TRABAJADOR":</strong><br>
  a) Llamarse como ha quedado escrito, ser de nacionalidad <strong>{{nacionalidad}}</strong>.<br>
  b) Tener <strong>{{edad}}</strong> años de edad, sexo <strong>{{genero}}</strong>, estado civil <strong>{{estado_civil}}</strong>.<br>
  c) Tener su domicilio particular en <strong>{{domicilio}}</strong>.<br>
  d) Estar inscrito en el RFC con clave <strong>{{rfc}}</strong>.<br>
  e) Tener Clave Única de Registro de Población (CURP): <strong>{{curp}}</strong>.<br>
  f) Estar registrado ante el IMSS con Número de Seguridad Social (NSS): <strong>{{nss}}</strong>.<br>
  g) Contar con la capacidad, conocimientos y experiencia necesarios para desempeñar el puesto de <strong>{{puesto}}</strong>.<br>
  h) Manifestar bajo protesta de decir verdad que no tiene impedimento legal para prestar sus servicios.</li>
</ol>

<h3 style="color:{{color_corporativo}}; border-bottom:1px solid #ddd; padding-bottom:4px; margin-top:30px;">CLÁUSULAS</h3>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">PRIMERA.- OBJETO Y RELACIÓN LABORAL.</strong> "LA EMPRESA" contrata los servicios personales y subordinados de "EL TRABAJADOR", quien se obliga a prestarlos con el puesto de <strong>{{puesto}}</strong>, adscrito al departamento de <strong>{{departamento}}</strong>, bajo la categoría de <strong>{{tipo_trabajador}}</strong>, por tiempo <strong>{{tipo_contrato}}</strong>, en el entendido de que, de subsistir la materia de trabajo, la relación laboral continuará por tiempo indeterminado conforme al Artículo 35 de la Ley Federal del Trabajo.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">SEGUNDA.- JORNADA DE TRABAJO.</strong> "EL TRABAJADOR" prestará sus servicios en una jornada de <strong>{{jornada_laboral}}</strong>, con un periodo de descanso para consumir alimentos de al menos treinta minutos diarios. "LA EMPRESA" podrá distribuir las horas de trabajo en los términos del Artículo 59 de la LFT para permitir el reposo del sábado en la tarde o cualquier modalidad equivalente, previo acuerdo por escrito.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">TERCERA.- LUGAR DE TRABAJO.</strong> "EL TRABAJADOR" prestará sus servicios en el domicilio de "LA EMPRESA" ubicado en {{domicilio_empresa}}, o en aquellos lugares que "LA EMPRESA" designe de acuerdo a las necesidades operativas, siempre que se encuentren dentro de la misma área geográfica, salvo pacto en contrario conforme al Artículo 42 de la LFT.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">CUARTA.- SALARIO.</strong> "LA EMPRESA" pagará a "EL TRABAJADOR" un salario mensual bruto de <strong>{{salario_base_mensual}}</strong>, equivalente a un salario diario de <strong>{{salario_base_diario}}</strong>, pagadero en moneda nacional de forma <strong>{{tipo_nomina}}</strong> a través de <strong>{{lugar_pago}}</strong>. "EL TRABAJADOR" acepta y reconoce expresamente que la forma y lugar de pago pactados le son convenientes. Este salario podrá incrementarse a discreción de "LA EMPRESA" o por revisión contractual conforme a derecho.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">QUINTA.- CAPACITACIÓN Y ADIESTRAMIENTO.</strong> "EL TRABAJADOR" se obliga a asistir y participar en los programas de capacitación y adiestramiento que "LA EMPRESA" establezca o llegue a establecer, conforme a los planes y programas autorizados por la Secretaría del Trabajo y Previsión Social, en cumplimiento del Artículo 153-A y demás relativos de la LFT.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">SEXTA.- VACACIONES, PRIMA VACACIONAL Y AGUINALDO.</strong> "EL TRABAJADOR" tendrá derecho a disfrutar de un período anual de vacaciones pagadas conforme a la tabla progresiva del Artículo 76 de la LFT, así como al pago de una prima vacacional no menor al 25% sobre los salarios correspondientes. Asimismo, tendrá derecho al pago de un aguinaldo anual equivalente a por lo menos 15 días de salario, pagadero antes del 20 de diciembre de cada año, conforme al Artículo 87 de la LFT.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">SÉPTIMA.- DESIGNACIÓN DE BENEFICIARIOS.</strong> De conformidad con los Artículos 25 fracción X y 501 de la LFT, "EL TRABAJADOR" designa como beneficiarios para el pago de los salarios y prestaciones devengadas y no cobradas a su fallecimiento o desaparición derivada de un acto delincuencial, a las siguientes personas:<br><br><strong>{{beneficiarios}}</strong><br><br>En caso de no designarse beneficiarios, "LA EMPRESA" quedará liberada de responsabilidad al realizar el pago a quien acredite tener mejor derecho, conforme a la legislación aplicable.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">OCTAVA.- SEGURIDAD SOCIAL.</strong> "LA EMPRESA" se obliga a inscribir a "EL TRABAJADOR" ante el Instituto Mexicano del Seguro Social (IMSS) dentro del plazo legal, así como a realizar las aportaciones correspondientes al INFONAVIT, AFORE y demás obligaciones de seguridad social, reteniendo de su salario las cuotas obreras correspondientes.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">NOVENA.- CONFIDENCIALIDAD Y PROPIEDAD INTELECTUAL.</strong> "EL TRABAJADOR" se obliga a guardar estricta confidencialidad respecto de toda la información técnica, comercial, financiera, de clientes, proveedores, procesos y cualquier otro dato o documento que conozca o tenga acceso con motivo de su trabajo en "LA EMPRESA". Esta obligación subsistirá aún después de terminada la relación laboral por cualquier causa. Toda invención, mejora, desarrollo o creación realizada por "EL TRABAJADOR" durante la vigencia de este contrato y relacionada con las actividades de "LA EMPRESA" será propiedad exclusiva de ésta.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">DÉCIMA.- REGLAMENTO INTERIOR.</strong> "EL TRABAJADOR" se obliga a cumplir con el Reglamento Interior de Trabajo, las políticas internas, los procedimientos operativos, las normas de seguridad e higiene, y demás disposiciones que "LA EMPRESA" tenga establecidas o llegue a establecer para el buen funcionamiento de las operaciones, siempre que no contravengan lo dispuesto por la LFT y demás leyes aplicables.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">DÉCIMA PRIMERA.- RESCISIÓN.</strong> Las partes acuerdan que serán causas de rescisión de la relación laboral sin responsabilidad para "LA EMPRESA", además de las señaladas en el Artículo 47 de la LFT, cualquier falta grave a las obligaciones contraídas en el presente contrato, así como la violación a las políticas de confidencialidad establecidas en la cláusula novena.</p>

<p style="text-align:justify; margin-bottom:15px;"><strong style="color:{{color_corporativo}};">DÉCIMA SEGUNDA.- COMPETENCIA.</strong> Para la interpretación y cumplimiento del presente contrato, las partes se someten a la jurisdicción de la Junta Federal de Conciliación y Arbitraje o, en su caso, a los Tribunales Laborales competentes en la Ciudad de {{ciudad_contrato}}, renunciando expresamente a cualquier fuero que por razón de su domicilio presente o futuro pudiera corresponderles.</p>

<p style="text-align:center; margin:40px 0 30px; font-style:italic;">LEÍDO QUE FUE EL PRESENTE CONTRATO Y ENTERADAS LAS PARTES DE SU CONTENIDO Y ALCANCE LEGAL, LO FIRMAN DE CONFORMIDAD EN LA CIUDAD DE {{ciudad_contrato}}, A LOS {{fecha_generacion}}.</p>

<!-- BLOQUE DE FIRMAS -->
<br>
<table style="width:100%; border-collapse:collapse; margin-top:50px;">
  <tr>
    <td style="width:48%; text-align:center; vertical-align:top;">
      <div style="border-top:2px solid #333; width:240px; margin:0 auto; padding-top:10px;">
        <strong style="font-size:11pt;">{{nombre_completo}}</strong><br>
        <small style="color:#555;">EL TRABAJADOR</small><br>
        <small style="color:#999; font-size:8pt;">RFC: {{rfc}}</small>
      </div>
    </td>
    <td style="width:4%;"></td>
    <td style="width:48%; text-align:center; vertical-align:top;">
      <div style="border-top:2px solid #333; width:240px; margin:0 auto; padding-top:10px;">
        <strong style="font-size:11pt;">CHISA RECUBRIMIENTOS</strong><br>
        <small style="color:#555;">REPRESENTANTE LEGAL</small><br>
        <small style="color:#999; font-size:8pt;">POR LA EMPRESA</small>
      </div>
    </td>
  </tr>
</table>

<!-- TESTIGOS -->
<table style="width:100%; border-collapse:collapse; margin-top:60px;">
  <tr>
    <td style="width:45%; text-align:center; vertical-align:top;">
      <div style="border-top:1px solid #999; width:200px; margin:0 auto; padding-top:8px;">
        <small style="color:#777;">TESTIGO</small><br>
        <small style="color:#aaa; font-size:8pt;">Nombre y Firma</small>
      </div>
    </td>
    <td style="width:10%;"></td>
    <td style="width:45%; text-align:center; vertical-align:top;">
      <div style="border-top:1px solid #999; width:200px; margin:0 auto; padding-top:8px;">
        <small style="color:#777;">TESTIGO</small><br>
        <small style="color:#aaa; font-size:8pt;">Nombre y Firma</small>
      </div>
    </td>
  </tr>
</table>`;
            
        } else if(tipo === 'ejecutivo') {
            html = `
<div style="background:linear-gradient(135deg, {{color_corporativo}} 0%, #2a5078 100%); color:#fff; padding:30px 40px; text-align:center; margin-bottom:30px;">
  <h1 style="color:#fff; font-family:Georgia,serif; font-size:20pt; margin:0 0 5px; letter-spacing:1px;">CONTRATO LABORAL EJECUTIVO</h1>
  <p style="margin:5px 0 0; font-size:10pt; opacity:0.9;">Documento Confidencial · RRHH · Versión {{version}}</p>
</div>

<table style="width:100%; border-collapse:collapse; margin-bottom:25px;">
  <tr>
    <td style="width:50%; padding:15px; background:#f8f9fa; border-radius:4px;">
      <strong style="color:{{color_corporativo}}; display:block; margin-bottom:8px; font-size:10pt; text-transform:uppercase;">Datos del Empleado</strong>
      <p style="margin:2px 0;"><strong>Nombre:</strong> {{nombre_completo}}</p>
      <p style="margin:2px 0;"><strong>Puesto:</strong> {{puesto}}</p>
      <p style="margin:2px 0;"><strong>Departamento:</strong> {{departamento}}</p>
      <p style="margin:2px 0;"><strong>Tipo:</strong> {{tipo_trabajador}}</p>
      <p style="margin:2px 0;"><strong>N° Empleado:</strong> {{numero_empleado}}</p>
    </td>
    <td style="width:50%; padding:15px; background:#f8f9fa; border-radius:4px;">
      <strong style="color:{{color_corporativo}}; display:block; margin-bottom:8px; font-size:10pt; text-transform:uppercase;">Condiciones Laborales</strong>
      <p style="margin:2px 0;"><strong>Contrato:</strong> {{tipo_contrato}}</p>
      <p style="margin:2px 0;"><strong>Salario Mensual:</strong> {{salario_base_mensual}}</p>
      <p style="margin:2px 0;"><strong>Salario Diario:</strong> {{salario_base_diario}}</p>
      <p style="margin:2px 0;"><strong>Nómina:</strong> {{tipo_nomina}}</p>
      <p style="margin:2px 0;"><strong>Inicio:</strong> {{fecha_inicio}}</p>
    </td>
  </tr>
</table>

<h3 style="color:{{color_corporativo}}; border-left:4px solid {{color_corporativo}}; padding-left:12px; margin-top:25px;">1. DISPOSICIONES GENERALES</h3>
<p style="text-align:justify;">El presente contrato establece los términos y condiciones bajo los cuales <strong>CHISA RECUBRIMIENTOS</strong>, con domicilio en {{domicilio_empresa}}, contrata los servicios profesionales de <strong>{{nombre_completo}}</strong> en calidad de <strong>{{tipo_trabajador}}</strong> de confianza, desempeñando el puesto de <strong>{{puesto}}</strong>. Las partes reconocen que este contrato se rige por lo dispuesto en la Ley Federal del Trabajo en lo aplicable a trabajadores de confianza.</p>

<h3 style="color:{{color_corporativo}}; border-left:4px solid {{color_corporativo}}; padding-left:12px; margin-top:25px;">2. FUNCIONES Y RESPONSABILIDADES</h3>
<p style="text-align:justify;">El puesto de <strong>{{puesto}}</strong> conlleva funciones de dirección, supervisión, inspección, vigilancia, fiscalización y manejo de información confidencial. "EL TRABAJADOR" desempeñará sus funciones con estricto apego a las políticas, procedimientos y código de ética de "LA EMPRESA".</p>

<h3 style="color:{{color_corporativo}}; border-left:4px solid {{color_corporativo}}; padding-left:12px; margin-top:25px;">3. COMPENSACIÓN Y BENEFICIOS</h3>
<p style="text-align:justify;">Además del salario base mensual de {{salario_base_mensual}}, "EL TRABAJADOR" podrá ser sujeto de un esquema de compensación variable basado en desempeño, mismo que será comunicado por separado y podrá ajustarse anualmente a criterio de la Dirección General.</p>

<h3 style="color:{{color_corporativo}}; border-left:4px solid {{color_corporativo}}; padding-left:12px; margin-top:25px;">4. CONFIDENCIALIDAD Y NO COMPETENCIA</h3>
<p style="text-align:justify;">Por la naturaleza de confianza del puesto, "EL TRABAJADOR" se obliga a no divulgar, utilizar o explotar en beneficio propio o de terceros, cualquier información confidencial o secreto industrial de "LA EMPRESA". Esta obligación subsistirá por un período de 2 años posteriores a la terminación de la relación laboral.</p>

<br>
<div style="background:#f8f9fa; padding:20px; text-align:center; margin:30px 0;">
  <p style="margin:0;">Firmado en {{ciudad_contrato}} a los {{fecha_generacion}}</p>
</div>

<table style="width:100%; border-collapse:collapse; margin-top:50px;">
  <tr>
    <td style="width:48%; text-align:center; vertical-align:top;">
      <div style="border-top:2px solid {{color_corporativo}}; width:240px; margin:0 auto; padding-top:10px;">
        <strong>{{nombre_completo}}</strong><br><small style="color:#555;">EL TRABAJADOR</small>
      </div>
    </td>
    <td style="width:4%;"></td>
    <td style="width:48%; text-align:center; vertical-align:top;">
      <div style="border-top:2px solid {{color_corporativo}}; width:240px; margin:0 auto; padding-top:10px;">
        <strong>CHISA RECUBRIMIENTOS</strong><br><small style="color:#555;">DIRECCIÓN GENERAL</small>
      </div>
    </td>
  </tr>
</table>`;

        } else if (tipo === 'operativo') {
            html = `
<!-- MEMBRETE SIMPLE -->
<table style="width:100%; border-bottom:2px solid {{color_corporativo}}; margin-bottom:25px; padding-bottom:12px;">
  <tr>
    <td><h2 style="margin:0; color:{{color_corporativo}}; font-family:Georgia,serif;">CHISA RECUBRIMIENTOS</h2></td>
    <td style="text-align:right;"><span style="color:#999; font-size:9pt;">Contrato N° {{numero_empleado}}-{{version}}</span></td>
  </tr>
</table>

<h2 style="text-align:center; font-size:15pt; margin:20px 0; color:{{color_corporativo}}; font-family:Georgia,serif;">CONTRATO INDIVIDUAL DE TRABAJO<br><span style="font-size:11pt; font-weight:normal; color:#555;">Personal Operativo</span></h2>

<p style="text-align:justify; margin-bottom:20px;">
En <strong>{{ciudad_contrato}}</strong>, a <strong>{{fecha_generacion}}</strong>, comparecen por una parte <strong>CHISA RECUBRIMIENTOS</strong>, representada legalmente en este acto, a quien en lo sucesivo se le denominará <strong>"LA EMPRESA"</strong>; y por la otra parte el(la) C. <strong>{{nombre_completo}}</strong>, a quien en lo sucesivo se le denominará <strong>"EL TRABAJADOR"</strong>, quienes celebran el presente <strong>CONTRATO INDIVIDUAL DE TRABAJO</strong> por tiempo <strong>{{tipo_contrato}}</strong>, al tenor de las siguientes:</p>

<h3 style="color:{{color_corporativo}}; margin-top:25px;">CLÁUSULAS</h3>

<p style="text-align:justify; margin-bottom:12px;"><strong>PRIMERA.-</strong> "EL TRABAJADOR" prestará sus servicios personales y subordinados a "LA EMPRESA" con el puesto de <strong>{{puesto}}</strong>, en el departamento de <strong>{{departamento}}</strong>, con categoría de <strong>{{tipo_trabajador}}</strong>.</p>

<p style="text-align:justify; margin-bottom:12px;"><strong>SEGUNDA.-</strong> La jornada de trabajo será de <strong>{{jornada_laboral}}</strong>, con un descanso de media hora para tomar alimentos, en el horario y turno que "LA EMPRESA" asigne conforme a sus necesidades operativas.</p>

<p style="text-align:justify; margin-bottom:12px;"><strong>TERCERA.-</strong> El lugar de trabajo será en las instalaciones de "LA EMPRESA" ubicadas en {{domicilio_empresa}}, o en cualquier obra, sucursal o proyecto donde "LA EMPRESA" requiera los servicios de "EL TRABAJADOR".</p>

<p style="text-align:justify; margin-bottom:12px;"><strong>CUARTA.-</strong> "EL TRABAJADOR" percibirá un salario mensual de <strong>{{salario_base_mensual}}</strong> (equivalente a <strong>{{salario_base_diario}}</strong> diarios), pagadero de forma <strong>{{tipo_nomina}}</strong> mediante {{lugar_pago}}.</p>

<p style="text-align:justify; margin-bottom:12px;"><strong>QUINTA.-</strong> "EL TRABAJADOR" tendrá derecho a vacaciones, prima vacacional, aguinaldo, seguridad social (IMSS, INFONAVIT, AFORE) y demás prestaciones establecidas en la Ley Federal del Trabajo.</p>

<p style="text-align:justify; margin-bottom:12px;"><strong>SEXTA.-</strong> "EL TRABAJADOR" se obliga a cumplir el Reglamento Interior de Trabajo, normas de seguridad e higiene, y utilizar el equipo de protección personal que "LA EMPRESA" le proporcione.</p>

<p style="text-align:justify; margin-bottom:12px;"><strong>SÉPTIMA.-</strong> Para el pago de salarios y prestaciones devengadas y no cobradas en caso de fallecimiento, "EL TRABAJADOR" designa como beneficiarios a: <strong>{{beneficiarios}}</strong>.</p>

<p style="text-align:center; margin:40px 0 30px;">Leído y entendido el contenido y alcance legal del presente contrato, las partes lo firman de conformidad.</p>

<table style="width:100%; border-collapse:collapse; margin-top:40px;">
  <tr>
    <td style="width:48%; text-align:center; vertical-align:top; padding:10px;">
      <div style="border-top:2px solid #333; width:220px; margin:0 auto; padding-top:10px;">
        <strong>{{nombre_completo}}</strong><br><small style="color:#555;">EL TRABAJADOR</small>
      </div>
    </td>
    <td style="width:48%; text-align:center; vertical-align:top; padding:10px;">
      <div style="border-top:2px solid #333; width:220px; margin:0 auto; padding-top:10px;">
        <strong>CHISA RECUBRIMIENTOS</strong><br><small style="color:#555;">POR LA EMPRESA</small>
      </div>
    </td>
  </tr>
</table>`;

        } else if (tipo === 'confidencialidad') {
            html = `
<div style="border:2px solid {{color_corporativo}}; padding:30px; margin-bottom:30px; text-align:center;">
  <h1 style="color:{{color_corporativo}}; font-family:Georgia,serif; font-size:18pt; margin:0 0 5px;">ACUERDO DE CONFIDENCIALIDAD</h1>
  <p style="color:#555; font-size:10pt; margin:5px 0 0;">ANEXO AL CONTRATO INDIVIDUAL DE TRABAJO</p>
  <p style="color:#999; font-size:9pt; margin:10px 0 0;">Ref: {{numero_empleado}} / Versión {{version}}</p>
</div>

<p style="text-align:justify; margin-bottom:20px;">
En <strong>{{ciudad_contrato}}</strong>, a <strong>{{fecha_generacion}}</strong>, comparecen <strong>CHISA RECUBRIMIENTOS</strong> ("LA EMPRESA") con domicilio en {{domicilio_empresa}}, y el(la) C. <strong>{{nombre_completo}}</strong> ("EL EMPLEADO"), con puesto de <strong>{{puesto}}</strong>, RFC <strong>{{rfc}}</strong>, quienes acuerdan lo siguiente:
</p>

<h3 style="color:{{color_corporativo}}; margin-top:25px;">1. DEFINICIÓN DE INFORMACIÓN CONFIDENCIAL</h3>
<p style="text-align:justify;">Se considera información confidencial toda aquella relacionada con las actividades de "LA EMPRESA", incluyendo pero no limitado a: fórmulas, procesos de fabricación de recubrimientos, especificaciones técnicas, bases de datos de clientes y proveedores, listas de precios, estrategias comerciales, información financiera, planes de negocio, software, know-how técnico, y cualquier otra información que "LA EMPRESA" haya designado como confidencial o que por su naturaleza deba considerarse como tal.</p>

<h3 style="color:{{color_corporativo}}; margin-top:25px;">2. OBLIGACIONES DEL EMPLEADO</h3>
<p style="text-align:justify;">"EL EMPLEADO" se obliga a: a) Mantener absoluta confidencialidad; b) No divulgar, copiar, transmitir o utilizar la información para fines distintos a sus funciones; c) Devolver todo material confidencial al término de la relación laboral; d) Notificar inmediatamente cualquier filtración o acceso no autorizado.</p>

<h3 style="color:{{color_corporativo}}; margin-top:25px;">3. PROPIEDAD INTELECTUAL</h3>
<p style="text-align:justify;">Toda invención, mejora, desarrollo, diseño, software, documento o creación realizada por "EL EMPLEADO" durante su relación laboral y relacionada con las actividades de "LA EMPRESA" será propiedad exclusiva de ésta. "EL EMPLEADO" cede desde ahora todos los derechos patrimoniales sobre dichas creaciones.</p>

<h3 style="color:{{color_corporativo}}; margin-top:25px;">4. VIGENCIA</h3>
<p style="text-align:justify;">Las obligaciones de confidencialidad establecidas en este acuerdo subsistirán durante toda la relación laboral y continuarán vigentes por un período de <strong>5 (CINCO) AÑOS</strong> posteriores a la terminación de la misma, por cualquier causa que ésta ocurra.</p>

<h3 style="color:{{color_corporativo}}; margin-top:25px;">5. SANCIONES</h3>
<p style="text-align:justify;">El incumplimiento de las obligaciones contenidas en este acuerdo será causa de rescisión laboral sin responsabilidad para "LA EMPRESA", además de las acciones legales civiles y penales que correspondan por la violación de secretos industriales conforme al Código Penal Federal y la Ley de Propiedad Industrial.</p>

<br>
<table style="width:100%; border-collapse:collapse; margin-top:50px;">
  <tr>
    <td style="width:48%; text-align:center; vertical-align:top;">
      <div style="border-top:2px solid #333; width:240px; margin:0 auto; padding-top:10px;">
        <strong>{{nombre_completo}}</strong><br><small style="color:#555;">EL EMPLEADO</small><br><small style="color:#999; font-size:8pt;">RFC: {{rfc}}</small>
      </div>
    </td>
    <td style="width:48%; text-align:center; vertical-align:top;">
      <div style="border-top:2px solid #333; width:240px; margin:0 auto; padding-top:10px;">
        <strong>CHISA RECUBRIMIENTOS</strong><br><small style="color:#555;">REPRESENTANTE LEGAL</small>
      </div>
    </td>
  </tr>
</table>

<!-- SELLO -->
<div style="text-align:center; margin-top:40px; padding:15px; border:1px dashed #ccc; display:inline-block;">
  <small style="color:#aaa;">ESPACIO PARA SELLO DE LA EMPRESA</small>
</div>`;
        }
        
        tinymce.get('editorContenido').setContent(html);
        
        // Refresh preview if visible
        if (document.getElementById('previewPanel').style.display !== 'none') {
            refreshPreview();
        }
    }
</script>