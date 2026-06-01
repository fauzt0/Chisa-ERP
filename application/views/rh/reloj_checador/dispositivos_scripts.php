<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
(function() {
    var $ = window.jQuery;
    if (!$) {
        console.error('Reloj dispositivos: jQuery no está disponible');
        return;
    }

    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var tablaDispositivos = null;

    function notify(msg, type) {
        if (typeof notifyShow === 'function') {
            notifyShow(msg, type);
        } else {
            alert(msg);
        }
    }

    function hideModal(elId) {
        var el = document.getElementById(elId);
        if (!el) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var instance = bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
            instance.hide();
        } else {
            $(el).modal('hide');
        }
    }

    function showModal(elId) {
        var el = document.getElementById(elId);
        if (!el) return;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        } else {
            $(el).modal('show');
        }
    }

    function initDispositivos() {
        tablaDispositivos = $('#tabla-dispositivos').DataTable({
            processing: true,
            serverSide: true,
            order: [],
            ajax: {
                url: '<?php echo base_url("rh/RelojChecador/search_dispositivos"); ?>',
                type: 'POST',
                data: function(d) {
                    d[csrfName] = csrfHash;
                },
                dataSrc: function(json) {
                    csrfHash = json.csrf_hash || csrfHash;
                    return json.data;
                }
            },
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2 },
                { data: 3 },
                { data: 4 },
                { data: 5, orderable: false }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-info btn-sm' }
            ],
            responsive: true
        });

        $('#tabla-dispositivos').on('xhr.dt', function(e, settings, json) {
            if (json && json.csrf_hash) {
                csrfHash = json.csrf_hash;
            }
        });

        $('#formDispositivo').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();
            formData += '&' + csrfName + '=' + csrfHash;
            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);

            $.ajax({
                url: '<?php echo base_url("rh/RelojChecador/guardar_dispositivo"); ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(resp) {
                    if (typeof resp === 'string') {
                        try { resp = JSON.parse(resp); } catch (err) { resp = { success: false, message: 'Respuesta inválida del servidor' }; }
                    }
                    if (resp.success) {
                        if (resp.token) {
                            $('#tokenDisplay').val(resp.token);
                            $('#tokenContainer').show();
                            notify('Dispositivo creado. Copia el token API antes de cerrar el modal.', 'success');
                        } else {
                            notify(resp.message, 'success');
                            hideModal('modalDispositivo');
                        }
                        tablaDispositivos.ajax.reload();
                    } else {
                        notify(resp.message || 'No se pudo guardar el dispositivo', 'danger');
                    }
                    csrfHash = resp.csrf_hash || csrfHash;
                },
                error: function(xhr) {
                    var msg = 'Error al comunicarse con el servidor';
                    if (xhr.responseText) {
                        try {
                            var err = JSON.parse(xhr.responseText);
                            if (err.message) msg = err.message;
                        } catch (e) {}
                    }
                    notify(msg, 'danger');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        $('#modalDispositivo').on('show.bs.modal', function() {
            if (!$('#dispositivo_id').val()) {
                $('#modalDispositivoTitle').text('Nuevo Dispositivo');
                $('#formDispositivo')[0].reset();
                $('#tokenContainer').hide();
                $('#activo').prop('checked', true);
            }
        });

        $('#modalDispositivo').on('hidden.bs.modal', function() {
            $('#formDispositivo')[0].reset();
            $('#dispositivo_id').val('');
            $('#tokenContainer').hide();
        });
    }

    window.ver_dispositivo = function(id) {
        $('#detalleContent').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>');
        showModal('modalDetalle');
        $('#btnRegenerarToken').data('dispositivo-id', id);

        $.ajax({
            url: '<?php echo base_url("rh/RelojChecador/dispositivo_detail"); ?>',
            type: 'POST',
            data: { id: id, [csrfName]: csrfHash },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    var d = resp.detalle;
                    var html = '<div class="row g-3">';
                    html += '<div class="col-md-6"><strong>SN:</strong> <code>' + d.sn + '</code></div>';
                    html += '<div class="col-md-6"><strong>Alias:</strong> ' + (d.alias || '—') + '</div>';
                    html += '<div class="col-md-6"><strong>Ubicación:</strong> ' + (d.ubicacion || '—') + '</div>';
                    html += '<div class="col-md-6"><strong>Estado:</strong> ' + (d.activo == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>') + '</div>';
                    html += '<div class="col-md-6"><strong>Última Conexión:</strong> ' + (d.ultima_conexion ? d.ultima_conexion : '<span class="text-muted">Nunca</span>') + '</div>';
                    html += '<div class="col-md-6"><strong>Fecha de Alta:</strong> ' + (d.fecha_alta ? d.fecha_alta : '—') + '</div>';
                    html += '<div class="col-12"><hr></div>';
                    html += '<div class="col-md-4"><strong>Checadas Hoy:</strong> <span class="badge bg-info fs-6">' + (resp.checadas_hoy || 0) + '</span></div>';

                    if (resp.comandos) {
                        html += '<div class="col-md-8"><strong>Comandos:</strong> ';
                        html += '<span class="badge bg-warning text-dark me-1">Pendientes: ' + (resp.comandos.pendiente || 0) + '</span>';
                        html += '<span class="badge bg-info me-1">Enviados: ' + (resp.comandos.enviado || 0) + '</span>';
                        html += '<span class="badge bg-success me-1">Ejecutados: ' + (resp.comandos.ejecutado || 0) + '</span>';
                        html += '<span class="badge bg-danger">Fallidos: ' + (resp.comandos.fallido || 0) + '</span>';
                        html += '</div>';
                    }

                    html += '</div>';
                    $('#detalleContent').html(html);
                } else {
                    $('#detalleContent').html('<div class="alert alert-danger">' + resp.message + '</div>');
                }
                csrfHash = resp.csrf_hash || csrfHash;
            },
            error: function() {
                $('#detalleContent').html('<div class="alert alert-danger">Error al cargar detalle</div>');
            }
        });
    };

    window.editar_dispositivo = function(id) {
        $.ajax({
            url: '<?php echo base_url("rh/RelojChecador/dispositivo_detail"); ?>',
            type: 'POST',
            data: { id: id, [csrfName]: csrfHash },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    $('#modalDispositivoTitle').text('Editar Dispositivo');
                    $('#dispositivo_id').val(resp.detalle.id);
                    $('#sn').val(resp.detalle.sn);
                    $('#alias').val(resp.detalle.alias || '');
                    $('#ubicacion').val(resp.detalle.ubicacion || '');
                    $('#activo').prop('checked', resp.detalle.activo == 1);
                    $('#tokenContainer').hide();
                    showModal('modalDispositivo');
                } else {
                    notify(resp.message, 'danger');
                }
            }
        });
    };

    window.eliminar_dispositivo = function(id) {
        if (!confirm('¿Desactivar este dispositivo? Dejará de aceptar sincronizaciones.')) {
            return;
        }

        $.ajax({
            url: '<?php echo base_url("rh/RelojChecador/eliminar_dispositivo"); ?>',
            type: 'POST',
            data: { id: id, [csrfName]: csrfHash },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    notify(resp.message, 'success');
                    if (tablaDispositivos) {
                        tablaDispositivos.ajax.reload();
                    }
                } else {
                    notify(resp.message, 'danger');
                }
            },
            error: function() {
                notify('Error al desactivar el dispositivo', 'danger');
            }
        });
    };

    window.regenerarToken = function() {
        var id = $('#btnRegenerarToken').data('dispositivo-id');
        if (!id) return;

        if (!confirm('¿Regenerar token? El token anterior dejará de funcionar de inmediato.')) {
            return;
        }

        $.ajax({
            url: '<?php echo base_url("rh/RelojChecador/regenerar_token"); ?>',
            type: 'POST',
            data: { id: id, [csrfName]: csrfHash },
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    prompt('Token regenerado. Cópialo ahora (no se volverá a mostrar):', resp.token);
                    notify('Token regenerado correctamente', 'success');
                } else {
                    notify(resp.message, 'danger');
                }
            },
            error: function() {
                notify('Error al regenerar el token', 'danger');
            }
        });
    };

    window.copiarToken = function() {
        var tokenInput = document.getElementById('tokenDisplay');
        if (!tokenInput || !tokenInput.value) {
            notify('No hay token para copiar', 'warning');
            return;
        }
        tokenInput.select();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(tokenInput.value).then(function() {
                notify('Token copiado al portapapeles', 'success');
            });
        } else {
            document.execCommand('copy');
            notify('Token copiado al portapapeles', 'success');
        }
    };

    $(document).ready(initDispositivos);
})();
</script>
