<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets/dist/js/rh_contratos_pdf.js') ?>?v=<?= time() ?>"></script>

<script>
    var editorInstance;
    var modalVerInstance;
    var contratoModalActual = null;
    var colorCorporativo = '<?= isset($plantilla) ? ($plantilla->color_corporativo ?? '#1a3a5c') : '#1a3a5c' ?>';
    var logoEmpresaHtml = '';

    function refreshLucideIcons() {
        if (typeof lucide !== 'undefined' && typeof lucide.createIcons === 'function') {
            lucide.createIcons();
        }
    }

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
            configurarSelectPlantilla();
        }
    });

    refreshLucideIcons();

    function configurarSelectPlantilla() {
        var select = document.getElementById('selectPlantilla');
        if (!select) return;
        select.addEventListener('change', function() {
            var plantillaId = this.value;
            var empleadoId = <?= (int)$empleado->id ?>;
            if (!plantillaId) return;

            var editor = tinymce.get('editorContrato');
            if (!editor) { alert('Editor no disponible. Recarga la página.'); return; }

            editor.setContent('<p class="text-center text-muted">Cargando plantilla...</p>');

            $.post('<?= base_url('rh/RecursosHumanos/ajax_previsualizar_contrato') ?>', {
                empleado_id: empleadoId,
                plantilla_id: plantillaId
            }, function(response) {
                try {
                    var resp = typeof response === 'string' ? JSON.parse(response) : response;
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

    var chkPlantilla = document.getElementById('chkGuardarPlantilla');
    if (chkPlantilla) {
        chkPlantilla.addEventListener('change', function() {
            var div = document.getElementById('divNombrePlantilla');
            if(this.checked) {
                div.style.display = 'block';
                document.getElementsByName('nombre_nueva_plantilla')[0].required = true;
            } else {
                div.style.display = 'none';
                document.getElementsByName('nombre_nueva_plantilla')[0].required = false;
            }
        });
    }

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
        refreshLucideIcons();
    }

    function updatePreviewScale() {
        var scale = document.getElementById('previewScale').value;
        document.getElementById('previewContent').style.transform = 'scale(' + scale + ')';
    }

    function refreshPreview() {
        var editor = tinymce.get('editorContrato');
        var content = editor ? editor.getContent() : '';
        content = replacePlaceholders(content);
        RhContratos.renderPreview(document.getElementById('previewContent'), content, colorCorporativo);
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
                empleado_id: <?= (int)$empleado->id ?>, plantilla_id: plantillaId
            }, function(response) {
                try {
                    var resp = typeof response === 'string' ? JSON.parse(response) : response;
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
        c = replacePlaceholders(c);
        RhContratos.generarPDF(c, 'Contrato_<?= $empleado->numero_empleado ?>.pdf', colorCorporativo);
    }

    function cargarContratoPorId(id, onSuccess, onError) {
        $.post('<?= base_url('rh/RecursosHumanos/ver_contrato') ?>', {
            contrato_id: id,
            peticion: 'ajax',
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        }, function(r) {
            try {
                var resp = RhContratos.parseJsonResponse(r);
                if (resp.success && resp.contrato) {
                    onSuccess(resp.contrato);
                } else {
                    onError(resp.message || 'No se pudo cargar el contrato');
                }
            } catch (e) {
                onError('Error al procesar los datos del contrato.');
            }
        }).fail(function() {
            onError('Error de conexión al cargar el contrato.');
        });
    }

    function verContrato(id) {
        if (!modalVerInstance) {
            modalVerInstance = RhContratos.abrirModalContrato('modalVerContrato');
            if (!modalVerInstance && window.bootstrap) {
                modalVerInstance = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerContrato'));
            }
        }
        var modalBody = document.getElementById('contenidoContratoModal');
        modalBody.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div><br>Cargando...</div>';
        if (modalVerInstance && modalVerInstance.show) modalVerInstance.show();

        cargarContratoPorId(id, function(contrato) {
            contratoModalActual = contrato;
            RhContratos.renderPreview(modalBody, contrato.contrato_texto, colorCorporativo);
            refreshLucideIcons();
        }, function(msg) {
            contratoModalActual = null;
            modalBody.innerHTML = '<div class="alert alert-danger">' + msg + '</div>';
        });
    }

    function descargarContratoHistorico(id) {
        cargarContratoPorId(id, function(contrato) {
            RhContratos.generarPDF(contrato.contrato_texto, 'Contrato_v' + contrato.version + '.pdf', colorCorporativo);
        }, function(msg) {
            alert(msg);
        });
    }

    function imprimirContratoModal() {
        if (!contratoModalActual || !contratoModalActual.contrato_texto) {
            alert('Espere a que cargue el contenido del contrato.');
            return;
        }
        RhContratos.generarPDF(
            contratoModalActual.contrato_texto,
            'Contrato_v' + (contratoModalActual.version || 'Historico') + '.pdf',
            colorCorporativo
        );
    }
</script>
