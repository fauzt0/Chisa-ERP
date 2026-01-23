<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title">Generar Nuevo Contrato</h5>
                    <h6 class="card-subtitle text-muted">Empleado: <?= $empleado->nombre . ' ' . $empleado->apellido_paterno ?></h6>
                </div>
                <!-- Botón de Previsualización PDF -->
                <!-- Botones de Acción -->
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-dark" onclick="cargarContratoMX()">
                        <i class="fas fa-gavel"></i> Cargar Contrato LFT
                    </button>
                    <button type="button" class="btn btn-danger" onclick="generarPDF()">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('rh/RecursosHumanos/guardar_nuevo_contrato') ?>" method="POST" id="formContrato">
                    <input type="hidden" name="empleado_id" value="<?= $empleado->id ?>">
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Contrato</label>
                            <select class="form-select" name="tipo_contrato" required>
                                <option value="Tiempo Indeterminado">Tiempo Indeterminado</option>
                                <option value="Tiempo Determinado">Tiempo Determinado</option>
                                <option value="Prueba (3 Meses)">Prueba (3 Meses)</option>
                                <option value="Capacitación Inicial">Capacitación Inicial</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Seleccionar Plantilla</label>
                            <select class="form-select" name="plantilla_id" id="selectPlantilla">
                                <option value="">-- Manual / Estándar MX --</option>
                                <?php foreach($plantillas as $p): ?>
                                    <option value="<?= $p->id ?>"><?= $p->nombre ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Motivo (Opcional)</label>
                            <input type="text" class="form-control" name="motivo" placeholder="Ej. Renovación anual">
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label">Contenido del Contrato (Editable)</label>
                        <textarea id="editorContrato" name="contenido" rows="25"></textarea>
                    </div>
                    
                    <div class="row mb-4 p-3 bg-light border rounded">
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="chkGuardarPlantilla" name="guardar_como_plantilla" value="1">
                                <label class="form-check-label fw-bold" for="chkGuardarPlantilla">Guardar estos cambios como una nueva plantilla global</label>
                            </div>
                        </div>
                        <div class="col-md-6 mt-2" id="divNombrePlantilla" style="display:none;">
                            <input type="text" class="form-control" name="nombre_nueva_plantilla" placeholder="Nombre para la nueva plantilla">
                            <small class="text-muted">Se guardará en la biblioteca de plantillas para uso futuro.</small>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <a href="<?= base_url('rh/RecursosHumanos') ?>" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-file-contract"></i> Generar y Guardar Contrato
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Historial rápido -->
    <div class="col-12 mt-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Historial de Contratos de este Empleado</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Versión</th>
                                <th>Tipo</th>
                                <th>Fecha Inicio</th>
                                <th>Estatus</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($historial)): ?>
                                <?php foreach($historial as $h): ?>
                                    <tr>
                                        <td><?= $h->version ?></td>
                                        <td><?= $h->tipo_contrato ?></td>
                                        <td><?= date('d/m/Y', strtotime($h->fecha_inicio)) ?></td>
                                        <td><?= $h->vigente ? '<span class="badge bg-success">Vigente</span>' : '<span class="badge bg-secondary">Histórico</span>' ?></td>
                                        <td>
                                            <!-- Ver contrato podría ser un modal o link, aquí usamos un placeholder -->
                                            <button type="button" class="btn btn-xs btn-info" onclick="verContrato(<?= $h->id ?>)">Ver</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted">Sin historial</td></tr>
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
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle del Contrato</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="contenidoContratoModal" class="p-4 bg-white border shadow-sm"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="imprimirContratoModal()">
            <i class="fas fa-file-pdf"></i> Descargar PDF
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

    tinymce.init({
        selector: '#editorContrato',
        height: 600,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount pagebreak',
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | pagebreak | help',
        content_style: 'body { font-family:Times New Roman,serif; font-size:12pt; line-height: 1.5; }',
        setup: function (editor) {
            editorInstance = editor;
        }
    });

    // Cargar plantilla al seleccionar
    document.getElementById('selectPlantilla').addEventListener('change', function() {
        var plantillaId = this.value;
        var empleadoId = <?= $empleado->id ?>;
        
        if(plantillaId) {
            tinymce.get('editorContrato').setContent('<p class="text-center">Cargando plantilla...</p>');
            
            $.post('<?= base_url('rh/RecursosHumanos/ajax_previsualizar_contrato') ?>', {
                empleado_id: empleadoId,
                plantilla_id: plantillaId
            }, function(response) {
                var resp = JSON.parse(response);
                if(resp.success) {
                    tinymce.get('editorContrato').setContent(resp.contenido);
                } else {
                    alert(resp.message);
                }
            });
        }
    });

    // Toggle input nueva plantilla
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

    function generarPDF() {
        const contenido = tinymce.get('editorContrato').getContent();
        if(!contenido) {
            alert('El contrato está vacío');
            return;
        }
        generarPDFDesdeHtml(contenido, 'Contrato_<?= $empleado->numero_empleado ?>.pdf');
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

    function cargarContratoMX() {
        if(tinymce.get('editorContrato').getContent() !== '' && !confirm('Esto reemplazará el contenido actual. ¿Deseas continuar?')) {
            return;
        }

        var html = '<h2 style="text-align: center;">CONTRATO INDIVIDUAL DE TRABAJO</h2>' +
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
'<p style="text-align: center;">Leído que fue el presente contrato y enteradas las partes de su contenido y alcance legal, lo firman de conformidad en CD. JUÁREZ, CHIHUAHUA a <strong>{{fecha_generacion}}</strong>.</p>' +
'<br><br><table style="width: 100%; border-collapse: collapse; margin-top: 50px;">' + 
'<tr>' +
'<td style="width: 45%; text-align: center;">{{firma_empresa_espacio}}</td>' +
'<td style="width: 10%;"></td>' +
'<td style="width: 45%; text-align: center;">{{firma_empleado_espacio}}</td>' +
'</tr></table>';

        tinymce.get('editorContrato').setContent(html);
    }
    
    function verContrato(id) {
        // Inicializar modal si no existe
        if(!modalVerInstance) {
            var modalEl = document.getElementById('modalVerContrato');
            modalVerInstance = new bootstrap.Modal(modalEl);
        }
        
        $('#contenidoContratoModal').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Cargando contrato...</div>');
        modalVerInstance.show();
        
        $.get('<?= base_url('rh/RecursosHumanos/ajax_get_contrato/') ?>' + id, function(response) {
            try {
                var resp = JSON.parse(response);
                if(resp.success) {
                    $('#contenidoContratoModal').html(resp.contenido);
                } else {
                    $('#contenidoContratoModal').html('<div class="alert alert-danger text-center"><i class="fas fa-exclamation-circle"></i> ' + resp.message + '</div>');
                }
            } catch(e) {
                $('#contenidoContratoModal').html('<div class="alert alert-danger">Error de respuesta del servidor</div>');
            }
        });
    }
    
    function imprimirContratoModal() {
        var content = document.getElementById('contenidoContratoModal').innerHTML;
        generarPDFDesdeHtml(content, 'Contrato_Historico.pdf');
    }
</script>
