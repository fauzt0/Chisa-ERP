<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
(function() {
    var $ = window.jQuery;
    if (!$) {
        console.error('Reloj sync_log: jQuery no está disponible');
        return;
    }

    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

    function initSyncLog() {
        var tabla = $('#tabla-sync-log').DataTable({
            processing: true,
            serverSide: true,
            order: [[5, 'desc']],
            ajax: {
                url: '<?php echo base_url("rh/RelojChecador/search_sync_log"); ?>',
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
                { data: 0 }, { data: 1 }, { data: 2 },
                { data: 3 }, { data: 4 }, { data: 5 }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-info btn-sm' }
            ],
            responsive: true
        });

        $('#tabla-sync-log').on('xhr.dt', function(e, settings, json) {
            if (json && json.csrf_hash) csrfHash = json.csrf_hash;
        });
    }

    $(document).ready(initSyncLog);
})();
</script>
