<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script>
(function() {
    var $ = window.jQuery;
    if (!$) {
        console.error('Reloj reporte diario: jQuery no está disponible');
        return;
    }

    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var tablaDiario = null;

    function actualizarExportLink() {
        var fecha = $('#filtro_fecha').val();
        var empleado = $('#filtro_empleado').val();
        var baseUrl = '<?php echo base_url("rh/RelojChecador/exportar_diario_csv"); ?>';
        var params = '?fecha=' + encodeURIComponent(fecha);
        if (empleado) {
            params += '&empleado_id=' + encodeURIComponent(empleado);
        }
        $('#btnExportarCSV').attr('href', baseUrl + params);
    }

    function initReporteDiario() {
        tablaDiario = $('#tabla-asistencias-diario').DataTable({
            processing: true,
            serverSide: true,
            order: [[4, 'desc']],
            ajax: {
                url: '<?php echo base_url("rh/RelojChecador/search_asistencias_diario"); ?>',
                type: 'POST',
                data: function(d) {
                    d[csrfName] = csrfHash;
                    d.fecha = $('#filtro_fecha').val();
                    d.empleado_id = $('#filtro_empleado').val();
                    d.departamento_id = $('#filtro_departamento').val();
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

        $('#tabla-asistencias-diario').on('xhr.dt', function(e, settings, json) {
            if (json && json.csrf_hash) csrfHash = json.csrf_hash;
        });

        $('#filtro_fecha, #filtro_empleado, #filtro_departamento').on('change', actualizarExportLink);
        actualizarExportLink();
    }

    window.filtrarTabla = function() {
        if (tablaDiario) {
            tablaDiario.ajax.reload();
            actualizarExportLink();
        }
    };

    $(document).ready(initReporteDiario);
})();
</script>
