<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
(function() {
    var $ = window.jQuery;
    if (!$) {
        console.error('Reloj reporte mensual: jQuery no está disponible');
        return;
    }

    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var tablaMensual = null;

    function initReporteMensual() {
        tablaMensual = $('#tabla-asistencias-mensual').DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'asc']],
            ajax: {
                url: '<?php echo base_url("rh/RelojChecador/search_asistencias_mensual"); ?>',
                type: 'POST',
                data: function(d) {
                    d[csrfName] = csrfHash;
                    d.mes = $('#filtro_mes').val();
                    d.anio = $('#filtro_anio').val();
                    d.empleado_id = $('#filtro_empleado').val();
                    d.departamento_id = $('#filtro_departamento').val();
                },
                dataSrc: function(json) {
                    csrfHash = json.csrf_hash || csrfHash;
                    if (json.error) {
                        console.error('Reporte mensual:', json.error);
                        if (typeof notifyShow === 'function') {
                            notifyShow(json.error, 'danger');
                        }
                        return [];
                    }
                    return json.data || [];
                },
                error: function(xhr) {
                    var msg = 'Error al cargar el reporte mensual';
                    if (xhr.responseText) {
                        try {
                            var err = JSON.parse(xhr.responseText);
                            if (err.error) msg = err.error;
                        } catch (e) {}
                    }
                    if (typeof notifyShow === 'function') {
                        notifyShow(msg, 'danger');
                    }
                }
            },
            columns: [
                { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 },
                { data: 4 }, { data: 5 }, { data: 6 }, { data: 7 }, { data: 8 }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-info btn-sm' }
            ],
            responsive: true,
            columnDefs: [
                { orderable: false, targets: 6 },
                { type: 'num', targets: [4, 5] }
            ]
        });

        $('#tabla-asistencias-mensual').on('xhr.dt', function(e, settings, json) {
            if (json && json.csrf_hash) csrfHash = json.csrf_hash;
        });
    }

    window.filtrarTabla = function() {
        if (tablaMensual) {
            tablaMensual.ajax.reload();
        }
    };

    $(document).ready(initReporteMensual);
})();
</script>
