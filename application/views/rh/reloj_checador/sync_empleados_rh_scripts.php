<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
(function() {
    var $ = window.jQuery;
    if (!$) {
        console.error('Reloj sync RH: jQuery no disponible');
        return;
    }

    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var camposOk = <?php echo !empty($response['campos_reloj_ok']) ? 'true' : 'false'; ?>;

    function habilitarFormularioSync(habilitar) {
        $('#dispositivo_sn, #btnRefrescarPreview, #btnEjecutarSync').prop('disabled', !habilitar);
    }

    function marcarMigracionLista() {
        camposOk = true;
        $('#alertMigracionPendiente').remove();
        if (!$('#alertMigracionOk').length) {
            $('<div class="alert alert-success" id="alertMigracionOk">Estructura de base de datos lista.</div>')
                .insertBefore('.alert-info');
        } else {
            $('#alertMigracionOk').removeClass('d-none');
        }
        habilitarFormularioSync(true);
    }

    function notify(msg, type) {
        if (typeof notifyShow === 'function') {
            notifyShow(msg, type);
        } else {
            alert(msg);
        }
    }

    function postData(extra) {
        var d = {};
        d[csrfName] = csrfHash;
        if (extra) {
            for (var k in extra) {
                if (Object.prototype.hasOwnProperty.call(extra, k)) {
                    d[k] = extra[k];
                }
            }
        }
        return d;
    }

    var tablaPreview = null;

    function initTablaPreview() {
        if ($.fn.DataTable && $('#tabla-preview-sync').length) {
            if (tablaPreview) {
                tablaPreview.destroy();
            }
            tablaPreview = $('#tabla-preview-sync').DataTable({
                pageLength: 25,
                order: [[0, 'asc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
                }
            });
        }
    }

    function renderPreview(data) {
        $('#statEmpleados').text(data.total_empleados || 0);
        $('#statEncolables').text(data.total_encolables != null ? data.total_encolables : 0);
        $('#statOmitidos').text(data.total_omitidos || 0);

        if (!tablaPreview) {
            initTablaPreview();
        }
        if (!tablaPreview) {
            return;
        }

        tablaPreview.clear();
        (data.empleados || []).forEach(function(f) {
            var estado = f.omitido ? (f.motivo_omitido || 'Omitido') : 'OK';
            tablaPreview.row.add([
                f.empleado_id,
                f.numero_empleado || '—',
                f.nombre_completo,
                f.pin_asignado != null ? String(f.pin_asignado) : '—',
                f.reloj_nombre_meta || '',
                estado
            ]);
        });
        tablaPreview.draw();
    }

    function cargarPreview() {
        if (!camposOk) {
            notify('Ejecute la migración SQL antes de continuar.', 'warning');
            return;
        }

        $.ajax({
            url: '<?php echo base_url("rh/RelojChecador/preview_sync_empleados_rh"); ?>',
            type: 'POST',
            dataType: 'json',
            data: postData({}),
            success: function(res) {
                if (!res.success) {
                    notify(res.message || 'Error al cargar vista previa', 'error');
                    return;
                }
                renderPreview(res);
            },
            error: function() {
                notify('Error de comunicación al cargar vista previa', 'error');
            }
        });
    }

    function aplicarMigracion() {
        var $btn = $('#btnAplicarMigracion').prop('disabled', true);
        $.ajax({
            url: '<?php echo base_url("rh/RelojChecador/aplicar_migracion_sync_empleados_rh"); ?>',
            type: 'POST',
            dataType: 'json',
            data: postData({}),
            success: function(res) {
                if (res.success && res.campos_reloj_ok) {
                    notify(res.message || 'Migración aplicada correctamente', 'success');
                    marcarMigracionLista();
                    cargarPreview();
                } else {
                    notify(res.message || 'No se pudo aplicar la migración', 'error');
                }
            },
            error: function() {
                notify('Error de comunicación al aplicar migración', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    }

    function initSyncRh() {
        habilitarFormularioSync(camposOk);
        initTablaPreview();

        $('#btnAplicarMigracion').on('click', aplicarMigracion);

        $('#btnReencolarCola').on('click', function() {
            var sn = $('#dispositivo_sn').val();
            if (!sn) {
                notify('Seleccione un dispositivo.', 'warning');
                return;
            }
            $.ajax({
                url: '<?php echo base_url("rh/RelojChecador/reencolar_cola_sync_empleados_rh"); ?>',
                type: 'POST',
                dataType: 'json',
                data: postData({ dispositivo_sn: sn }),
                success: function(res) {
                    notify(res.message || 'Listo', res.success ? 'success' : 'error');
                },
                error: function() {
                    notify('Error al reencolar', 'error');
                }
            });
        });

        $('#btnVaciarCola').on('click', function() {
            var sn = $('#dispositivo_sn').val();
            if (!sn) {
                notify('Seleccione un dispositivo.', 'warning');
                return;
            }
            if (!window.confirm('¿Eliminar todos los comandos pendientes y enviados de ' + sn + '?')) {
                return;
            }
            $.ajax({
                url: '<?php echo base_url("rh/RelojChecador/vaciar_cola_sync_empleados_rh"); ?>',
                type: 'POST',
                dataType: 'json',
                data: postData({ dispositivo_sn: sn }),
                success: function(res) {
                    notify(res.message || (res.success ? 'Cola vaciada' : 'Error'), res.success ? 'success' : 'error');
                },
                error: function() {
                    notify('Error al vaciar la cola', 'error');
                }
            });
        });

        $('#btnRefrescarPreview').on('click', cargarPreview);

        $('#formSyncRh').on('submit', function(e) {
            e.preventDefault();
            if (!camposOk) {
                notify('Migración SQL pendiente en empleados.', 'warning');
                return;
            }

            var sn = $('#dispositivo_sn').val();
            if (!sn) {
                notify('Seleccione un dispositivo.', 'warning');
                return;
            }

            var encolables = parseInt($('#statEncolables').text(), 10) || 0;
            if (encolables === 0) {
                notify('No hay empleados encolables (revise número de empleado y nombres).', 'warning');
                return;
            }

            var msg = 'Se vaciará la cola y se encolarán ' + encolables + ' comandos DATA USER (PIN 2, 3, 4…, TAB real) en ' + sn + '. ¿Continuar?';

            if (!window.confirm(msg)) {
                return;
            }

            var $btn = $('#btnEjecutarSync').prop('disabled', true);

            $.ajax({
                url: '<?php echo base_url("rh/RelojChecador/ejecutar_sync_empleados_rh"); ?>',
                type: 'POST',
                dataType: 'json',
                data: postData({ dispositivo_sn: sn }),
                success: function(res) {
                    if (res.success) {
                        var extra = ' Cola previa eliminada: ' + (res.cola_vaciada || 0) + '.';
                        extra += res.pendientes_sn != null ? ' Pendientes: ' + res.pendientes_sn + '.' : '';
                        if (res.ejemplo_user) {
                            extra += ' Ejemplo: ' + res.ejemplo_user;
                        }
                        if (res.empleados_omitidos > 0) {
                            extra += ' Omitidos: ' + res.empleados_omitidos + '.';
                        }
                        notify(
                            res.comandos_alta + ' DATA USER encolado(s) (' + res.empleados_sync + ' empleados).' + extra,
                            'success'
                        );
                        cargarPreview();
                    } else {
                        notify(res.message || 'No se pudo encolar la sincronización', 'error');
                    }
                },
                error: function() {
                    notify('Error de comunicación al encolar comandos', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    }

    $(document).ready(initSyncRh);
})();
</script>
