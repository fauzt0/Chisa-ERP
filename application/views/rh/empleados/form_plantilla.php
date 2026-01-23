<div class="row">
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><?= isset($plantilla) ? 'Editar' : 'Nueva' ?> Plantilla</h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-danger" onclick="generarPDF()">
                        <i class="fas fa-file-pdf"></i> Previsualizar PDF
                    </button>
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-magic"></i> Cargar Modelo Base
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="cargarModelo('lft')"><strong>Legal LFT (México)</strong></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="cargarModelo('clasico')">Clásico</a></li>
                        <li><a class="dropdown-item" href="#" onclick="cargarModelo('moderno')">Moderno</a></li>
                        <li><a class="dropdown-item" href="#" onclick="cargarModelo('corporativo')">Corporativo</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('rh/RecursosHumanos/guardar_plantilla') ?>" method="POST" id="formPlantilla" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= isset($plantilla) ? $plantilla->id : '' ?>">
                    
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Nombre de la Plantilla</label>
                            <input type="text" class="form-control" name="nombre" value="<?= isset($plantilla) ? $plantilla->nombre : '' ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Logo de Encabezado (Opcional)</label>
                            <input type="file" class="form-control" name="logo" accept="image/*">
                            <?php if(isset($plantilla) && !empty($plantilla->logo)): ?>
                                <small class="text-success"><i class="fas fa-check"></i> Logo actual cargado</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Domicilio Fiscal de la Empresa (Art. 25 LFT)</label>
                        <input type="text" class="form-control" name="domicilio_empresa" value="<?= isset($plantilla) ? $plantilla->domicilio_empresa : '' ?>" placeholder="Calle, Número, Colonia, CP, Ciudad, Estado">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" class="form-control" name="descripcion" value="<?= isset($plantilla) ? $plantilla->descripcion : '' ?>">
                    </div>
                    
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0">Contenido del Contrato</label>
                        <button type="button" class="btn btn-sm btn-info" onclick="insertarFirmas()">
                            <i class="fas fa-file-signature"></i> Insertar Bloque de Firmas
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <textarea id="editorContenido" name="contenido" rows="20"><?= isset($plantilla) ? $plantilla->contenido : '' ?></textarea>
                    </div>
                    
                    <div class="text-end">
                        <a href="<?= base_url('rh/RecursosHumanos/plantillas') ?>" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Plantilla</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">Variables Disponibles</h6>
            </div>
            <div class="card-body p-2">
                <p class="small text-muted mb-2">Haz clic para copiar:</p>
                <div class="list-group list-group-flush small" style="max-height: 500px; overflow-y: auto;">
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{nombre_completo}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{rfc}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{curp}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{nss}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{puesto}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{departamento}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{tipo_trabajador}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{salario_base_mensual}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{salario_base_diario}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{tipo_nomina}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{jornada_laboral}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{fecha_inicio}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{motivo_cambio}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{version}}</a>
                    <a href="#" class="list-group-item list-group-item-action copy-var">{{fecha_generacion}}</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    tinymce.init({
        selector: '#editorContenido',
        height: 600,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
        content_style: 'body { font-family:Times New Roman,serif; font-size:12pt; line-height: 1.5; }'
    });
    
    // Copy variable helper
    document.querySelectorAll('.copy-var').forEach(item => {
        item.addEventListener('click', event => {
            event.preventDefault();
            const text = item.textContent;
            navigator.clipboard.writeText(text).then(function() {
                // Usar toastr o alert simple si notifyShow no está disponible globalmente
                if(typeof notifyShow === 'function') {
                    notifyShow('Copiado: ' + text, 'info');
                } else {
                    alert('Copiado: ' + text);
                }
            });
        });
    });

    function insertarFirmas() {
        var html = '<br><br><table style="width: 100%; border-collapse: collapse; margin-top: 50px;">' + 
                   '<tr>' +
                   '<td style="width: 45%; text-align: center;">{{firma_empresa_espacio}}</td>' +
                   '<td style="width: 10%;"></td>' +
                   '<td style="width: 45%; text-align: center;">{{firma_empleado_espacio}}</td>' +
                   '</tr></table>';
        tinymce.get('editorContenido').insertContent(html);
    }

    function generarPDF() {
        const contenido = tinymce.get('editorContenido').getContent();
        if(!contenido) {
            alert('El contrato está vacío');
            return;
        }
        generarPDFDesdeHtml(contenido, 'Plantilla_Preview.pdf');
    }
    
    function generarPDFDesdeHtml(html, filename) {
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
          filename:     filename,
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

    function cargarModelo(tipo) {
        if(!confirm('¿Estás seguro? Esto reemplazará el contenido actual.')) return;

        var html = '';
        if (tipo === 'lft') {
            html = '<h2 style="text-align: center;">CONTRATO INDIVIDUAL DE TRABAJO</h2>' +
'<p>&nbsp;</p>' +
'<p style="text-align: justify;">CONTRATO INDIVIDUAL DE TRABAJO POR TIEMPO <strong>{{tipo_contrato}}</strong> QUE CELEBRAN POR UNA PARTE <strong>CHISA RECUBRIMIENTOS</strong> (EN LO SUCESIVO "LA EMPRESA"), CON DOMICILIO EN <strong>{{domicilio_empresa}}</strong>, REPRESENTADA EN ESTE ACTO POR SU REPRESENTANTE LEGAL, Y POR LA OTRA PARTE EL C. <strong>{{nombre_completo}}</strong> (EN LO SUCESIVO "EL TRABAJADOR") AL TENOR DE LAS SIGUIENTES DECLARACIONES Y CLÁUSULAS:</p>' +
'<h3>DECLARACIONES</h3>' +
'<ol type="I">' +
'<li><strong>DECLARA "LA EMPRESA":</strong><br>Ser una Sociedad Mercantil mexicana, legalmente constituida conforme a las leyes de los Estados Unidos Mexicanos, con domicilio fiscal ubicado en <strong>{{domicilio_empresa}}</strong>.</li>' +
'<li><strong>DECLARA "EL TRABAJADOR":</strong><br>Llamarse como ha quedado escrito, de nacionalidad <strong>{{nacionalidad}}</strong>, con <strong>{{edad}}</strong> años de edad, sexo <strong>{{genero}}</strong>, estado civil <strong>{{estado_civil}}</strong>, con domicilio particular en <strong>{{domicilio}}</strong>, RFC <strong>{{rfc}}</strong>, CURP <strong>{{curp}}</strong> y NSS <strong>{{nss}}</strong>.</li>' +
'</ol>' +
'<h3>CLÁUSULAS</h3>' +
'<p style="text-align: justify;"><strong>PRIMERA.- RELACIÓN LABORAL.</strong> "LA EMPRESA" contrata a "EL TRABAJADOR" por tiempo <strong>{{tipo_contrato}}</strong> para prestar sus servicios personales y subordinados con el puesto de <strong>{{puesto}}</strong> para el departamento de <strong>{{departamento}}</strong>.</p>' +
'<p style="text-align: justify;"><strong>SEGUNDA.- JORNADA DE TRABAJO.</strong> "EL TRABAJADOR" prestará sus servicios en una jornada de <strong>{{jornada_laboral}}</strong>, disfrutando de un periodo de descanso para consumir alimentos de al menos media hora. "LA EMPRESA" podrá distribuir las horas de trabajo para permitir el reposo del sábado en la tarde o cualquier otra modalidad equivalente.</p>' +
'<p style="text-align: justify;"><strong>TERCERA.- LUGAR DE TRABAJO.</strong> "EL TRABAJADOR" prestará sus servicios en el domicilio de "LA EMPRESA" o en los lugares que ésta designe de acuerdo a las necesidades operativas.</p>' +
'<p style="text-align: justify;"><strong>CUARTA.- SALARIO.</strong> "EL TRABAJADOR" percibir&aacute; un salario mensual bruto de <strong>{{salario_base_mensual}}</strong> ({{salario_base_diario}} diarios), pagadero en moneda nacional de forma <strong>{{tipo_nomina}}</strong> a trav&eacute;s de <strong>{{lugar_pago}}</strong>, previo consentimiento expreso en este acto.</p>' +
'<p style="text-align: justify;"><strong>QUINTA.- CAPACITACIÓN Y ADIESTRAMIENTO.</strong> "EL TRABAJADOR" será capacitado o adiestrado en los términos de los planes y programas establecidos o que se establezcan en "LA EMPRESA", conforme a lo dispuesto por la Ley Federal del Trabajo.</p>' +
'<p style="text-align: justify;"><strong>SEXTA.- VACACIONES Y AGUINALDO.</strong> "EL TRABAJADOR" tendrá derecho a disfrutar de un periodo anual de vacaciones pagadas y una prima vacacional, así como a un aguinaldo anual, conforme a lo establecido en la Ley Federal del Trabajo.</p>' +
'<p style="text-align: justify;"><strong>SÉPTIMA.- DESIGNACIÓN DE BENEFICIARIOS.</strong> De conformidad con el artículo 25 fracción X y 501 de la Ley Federal del Trabajo, "EL TRABAJADOR" designa como beneficiarios para el pago de los salarios y prestaciones devengadas y no cobradas a su muerte o desaparición derivada de un acto delincuencial a:<br><strong>{{beneficiarios}}</strong></p>' +
'<p style="text-align: justify;"><strong>OCTAVA.- SEGURIDAD SOCIAL.</strong> "EL TRABAJADOR" será inscrito ante el Instituto Mexicano del Seguro Social (IMSS) conforme a la legislación aplicable.</p>' +
'<p style="text-align: justify;"><strong>NOVENA.- CONFIDENCIALIDAD.</strong> "EL TRABAJADOR" se obliga a guardar estricta confidencialidad sobre la información, documentos y asuntos de "LA EMPRESA" que conozca con motivo de su trabajo.</p>' +
'<p>&nbsp;</p>' +
'<p style="text-align: center;">Leído que fue el presente contrato y enteradas las partes de su contenido y alcance legal, lo firman de conformidad en CD. JUÁREZ, CHIHUAHUA a <strong>{{fecha_generacion}}</strong>.</p>'; 
        } else if(tipo === 'clasico') {
            html = '<h2 style="text-align: center;">CONTRATO INDIVIDUAL DE TRABAJO</h2><p>&nbsp;</p>' +
                   '<p>En la ciudad de [CIUDAD], a {{fecha_generacion}}, comparecen por una parte <strong>CHISA RECUBRIMIENTOS</strong> (el "Patrón") y por la otra <strong>{{nombre_completo}}</strong> (el "Trabajador")...</p>' +
                   '<h3>CLÁUSULAS</h3><p><strong>PRIMERA.</strong> El Trabajador prestará sus servicios como <strong>{{puesto}}</strong>...</p>';
        } else if (tipo === 'moderno') {
            html = '<div style="background-color: #f8f9fa; padding: 20px; border-left: 5px solid #0d6efd;">' +
                   '<h1 style="color: #0d6efd; margin-top: 0;">Contrato Laboral</h1>' +
                   '<p><strong>Empleado:</strong> {{nombre_completo}} | <strong>Puesto:</strong> {{puesto}}</p></div>' +
                   '<div style="margin-top: 20px;"><p>Este documento certifica la relación laboral...</p></div>';
        } else if (tipo === 'corporativo') {
            html = '<table style="width: 100%; border-bottom: 2px solid #333; margin-bottom: 20px;"><tr><td><h1 style="font-family: serif;">ACUERDO LEGAL</h1></td><td style="text-align: right;">{{fecha_generacion}}</td></tr></table>' +
                   '<p style="text-align: justify; font-family: serif;">CONTRATO CELEBRADO ENTRE LAS PARTES...</p>';
        }
        
        tinymce.get('editorContenido').setContent(html);
        insertarFirmas(); // Auto-insertar firmas
    }
</script>
