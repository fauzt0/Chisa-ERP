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
        var params = '?fecha=' + encodeURIComponent($('#filtro_fecha').val());
        var empleado = $('#filtro_empleado').val();
        var depto = $('#filtro_departamento').val();
        var estado = $('#filtro_estado').val();
        if (empleado) params += '&empleado_id=' + encodeURIComponent(empleado);
        if (depto) params += '&departamento_id=' + encodeURIComponent(depto);
        if (estado) params += '&estado=' + encodeURIComponent(estado);
        $('#btnExportarCSV').attr('href', '<?php echo base_url("rh/RelojChecador/exportar_diario_csv"); ?>' + params);
    }

    function badgeEstadoAsistencia(estado) {
        var clases = {
            'Asistencia completa': 'bg-success',
            'Con retardo': 'bg-warning text-dark',
            'Retardo mayor': 'bg-danger',
            'Salida temprana': 'bg-warning text-dark',
            'Checadas parciales': 'bg-secondary',
            'Sin checadas': 'bg-light text-muted border',
            'Sin horario asignado': 'bg-info'
        };
        return '<span class="badge ' + (clases[estado] || 'bg-secondary') + '">' + estado + '</span>';
    }

    function badgeTipoChecada(tipo, label) {
        var clases = {
            entrada: 'bg-success',
            salida: 'bg-primary',
            salida_comida: 'bg-warning text-dark',
            entrada_comida: 'bg-info text-dark',
            checada_intermedia: 'bg-secondary',
            checada_extra: 'bg-light text-dark border'
        };
        return '<span class="badge ' + (clases[tipo] || 'bg-secondary') + '">' + (label || tipo) + '</span>';
    }

    function formatearFechaMx(fecha) {
        if (!fecha) return '';
        var p = fecha.split('-');
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    function renderDetalleDia(result) {
        var calc = result.calculo || {};
        var checadas = result.checadas_etiquetadas || [];
        var html = '';

        html += '<div class="d-flex flex-wrap align-items-center gap-2 mb-3">';
        html += badgeEstadoAsistencia(calc.estado || 'Sin checadas');
        html += '<span class="badge bg-light text-dark border">' + result.dia_semana + '</span>';
        if (calc.retardo) {
            html += '<span class="badge bg-danger">Retardo: ' + calc.minutos_retardo + ' min</span>';
        }
        html += '</div>';

        if (checadas.length === 0) {
            html += '<div class="alert alert-light border mb-0">Sin checadas registradas este día</div>';
        } else {
            html += '<div class="position-relative ps-3 border-start border-success border-3 mb-3">';
            checadas.forEach(function(c, idx) {
                html += '<div class="mb-3 ps-2">';
                html += '<div class="d-flex align-items-center gap-2">';
                html += '<span class="badge rounded-pill bg-success">&nbsp;</span>';
                html += '<strong>' + c.hora + '</strong>';
                html += badgeTipoChecada(c.tipo, c.tipo_label);
                html += '<small class="text-muted">' + c.metodo_label + '</small>';
                if (c.dispositivo_sn) {
                    html += '<code class="small ms-auto">' + c.dispositivo_sn + '</code>';
                }
                html += '</div></div>';
            });
            html += '</div>';

            html += '<div class="p-3 bg-light rounded small">';
            html += '<div class="row g-2">';
            html += '<div class="col-6 col-md-3"><strong>Entrada:</strong> ' + (calc.entrada || '—') + '</div>';
            html += '<div class="col-6 col-md-3"><strong>Salida:</strong> ' + (calc.salida || '—') + '</div>';
            html += '<div class="col-6 col-md-3"><strong>Salida comida:</strong> ' + (calc.salida_comida || '—') + '</div>';
            html += '<div class="col-6 col-md-3"><strong>Entrada comida:</strong> ' + (calc.entrada_comida || '—') + '</div>';
            html += '<div class="col-6 col-md-3"><strong>Horas:</strong> ' + (calc.horas_trabajadas || '00:00') + '</div>';
            html += '</div></div>';
        }

        return html;
    }

    function abrirDetalleEmpleado(empleadoId, nombre) {
        var fecha = $('#filtro_fecha').val();
        $('#modal-detalle-subtitulo').text(nombre + ' · ' + formatearFechaMx(fecha));
        $('#modal-detalle-contenido').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
        $('#modalDetalleDiario').modal('show');

        $.post('<?php echo base_url("rh/RelojChecador/asistencia_detalle_dia"); ?>', {
            empleado_id: empleadoId,
            fecha: fecha,
            peticion: 'ajax',
            [csrfName]: csrfHash
        }, function(result) {
            try {
                if (typeof result === 'string') result = JSON.parse(result);
                if (!result.success) {
                    $('#modal-detalle-contenido').html('<div class="alert alert-warning mb-0">' + (result.message || 'Error') + '</div>');
                    return;
                }
                $('#modal-detalle-contenido').html(renderDetalleDia(result));
            } catch (e) {
                $('#modal-detalle-contenido').html('<div class="alert alert-danger mb-0">Error al procesar respuesta</div>');
            }
        });
    }

    function initReporteDiario() {
        tablaDiario = $('#tabla-asistencias-diario').DataTable({
            processing: true,
            serverSide: true,
            order: [[1, 'asc']],
            ajax: {
                url: '<?php echo base_url("rh/RelojChecador/search_asistencias_diario"); ?>',
                type: 'POST',
                data: function(d) {
                    d[csrfName] = csrfHash;
                    d.fecha = $('#filtro_fecha').val();
                    d.empleado_id = $('#filtro_empleado').val();
                    d.departamento_id = $('#filtro_departamento').val();
                    d.estado = $('#filtro_estado').val();
                },
                dataSrc: function(json) {
                    if (json && json.csrf_hash) csrfHash = json.csrf_hash;
                    return json.data;
                }
            },
            columns: [
                { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 },
                { data: 4 }, { data: 5 }, { data: 6 }, { data: 7 },
                { data: 8 }, { data: 9 }, { data: 10, visible: false }
            ],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-info btn-sm' }
            ],
            responsive: true,
            createdRow: function(row, data) {
                $(row).css('cursor', 'pointer').attr('title', 'Clic para ver detalle');
            }
        });

        $('#tabla-asistencias-diario tbody').on('click', 'tr', function() {
            var data = tablaDiario.row(this).data();
            if (!data || !data[10]) return;
            abrirDetalleEmpleado(data[10], data[1]);
        });

        $('#filtro_fecha, #filtro_empleado, #filtro_departamento, #filtro_estado').on('change', actualizarExportLink);
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
