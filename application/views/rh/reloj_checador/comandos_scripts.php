<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
(function() {
    var $ = window.jQuery;
    if (!$) {
        console.error('Reloj comandos: jQuery no está disponible');
        return;
    }

    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

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
            (bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el)).hide();
        } else {
            $(el).modal('hide');
        }
    }

    function initComandos() {
        var tablaComandos = $('#tabla-comandos').DataTable({
            processing: true,
            serverSide: true,
            order: [[5, 'desc']],
            ajax: {
                url: '<?php echo base_url("rh/RelojChecador/search_comandos"); ?>',
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
                { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 },
                { data: 4 }, { data: 5 }, { data: 6 }, { data: 7 }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm' }
            ],
            responsive: true
        });

        $('#tabla-comandos').on('xhr.dt', function(e, settings, json) {
            if (json && json.csrf_hash) csrfHash = json.csrf_hash;
        });

        $('#formComando').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize() + '&' + csrfName + '=' + csrfHash;
            var $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true);

            $.ajax({
                url: '<?php echo base_url("rh/RelojChecador/encolar_comando"); ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(resp) {
                    if (typeof resp === 'string') {
                        try { resp = JSON.parse(resp); } catch (err) { resp = { success: false, message: 'Respuesta inválida' }; }
                    }
                    if (resp.success) {
                        notify(resp.message, 'success');
                        hideModal('modalComando');
                        tablaComandos.ajax.reload();
                    } else {
                        notify(resp.message || 'Error al encolar', 'danger');
                    }
                    csrfHash = resp.csrf_hash || csrfHash;
                },
                error: function() {
                    notify('Error al comunicarse con el servidor', 'danger');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        $('#modalComando').on('hidden.bs.modal', function() {
            $('#formComando')[0].reset();
        });

        $('#btnVaciarTodosComandos').on('click', function() {
            if (!window.confirm('¿Eliminar TODOS los comandos de todos los dispositivos? Esta acción no se puede deshacer.')) {
                return;
            }
            var $btn = $(this).prop('disabled', true);
            $.ajax({
                url: '<?php echo base_url("rh/RelojChecador/vaciar_todos_comandos"); ?>',
                type: 'POST',
                dataType: 'json',
                data: (function() {
                    var d = {};
                    d[csrfName] = csrfHash;
                    return d;
                })(),
                success: function(resp) {
                    if (typeof resp === 'string') {
                        try { resp = JSON.parse(resp); } catch (e) { resp = { success: false }; }
                    }
                    notify(resp.message || 'Cola vaciada', resp.success ? 'success' : 'error');
                    if (resp.success) {
                        tablaComandos.ajax.reload();
                    }
                },
                error: function() {
                    notify('Error al vaciar la cola', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    }

    $(document).ready(initComandos);
})();
</script>
