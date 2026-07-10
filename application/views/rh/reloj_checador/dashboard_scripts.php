<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
    var $ = window.jQuery;
    if (!$) return;

    var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
    var chartInstance = null;
    var autoRefreshTimer = null;

    function initAutoRefreshToggle() {
        var toggle = document.getElementById('toggle-auto-refresh');
        if (!toggle) return;

        if (sessionStorage.getItem('reloj_auto_refresh') === '1') {
            toggle.checked = true;
            startAutoRefresh();
        }

        toggle.addEventListener('change', function() {
            if (toggle.checked) {
                sessionStorage.setItem('reloj_auto_refresh', '1');
                startAutoRefresh();
            } else {
                sessionStorage.removeItem('reloj_auto_refresh');
                stopAutoRefresh();
            }
        });
    }

    function startAutoRefresh() {
        stopAutoRefresh();
        autoRefreshTimer = setInterval(actualizarDashboard, 30000);
    }

    function stopAutoRefresh() {
        if (autoRefreshTimer) {
            clearInterval(autoRefreshTimer);
            autoRefreshTimer = null;
        }
    }

    function initChart() {
        var el = document.getElementById('chartChecadas7d');
        var dataEl = document.getElementById('dashboard-checadas-data');
        if (!el || !dataEl || typeof Chart === 'undefined') return;

        var datos = [];
        try { datos = JSON.parse(dataEl.textContent); } catch (e) { return; }

        var labels = datos.map(function(d) { return d.fecha_corta; });
        var values = datos.map(function(d) { return d.total; });

        chartInstance = new Chart(el, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Checadas',
                    data: values,
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',
                    borderColor: '#15803d',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    function actualizarDashboard() {
        $.post('<?php echo base_url("rh/RelojChecador/dashboard_stats_ajax"); ?>', {
            peticion: 'ajax',
            [csrfName]: csrfHash
        }, function(result) {
            try {
                if (typeof result === 'string') result = JSON.parse(result);
                if (!result.success) return;

                var s = result.stats || {};
                $('#stat-asistencias-hoy').text(s.asistencias_hoy || 0);
                $('#stat-empleados-hoy').text(s.empleados_checaron_hoy || 0);
                $('#stat-presentes-hoy').text(s.presentes_hoy || 0);
                $('#stat-ausentes-hoy').text(s.ausentes_hoy || 0);
                $('#stat-retardos-resumen').text(s.retardos_hoy || 0);
                var totalDia = Math.max(1, parseInt(s.total_esperados_hoy || 0, 10));
                var pctPresentes = Math.min(100, Math.round(((parseInt(s.presentes_hoy || 0, 10)) / totalDia) * 100));
                $('#bar-presentes-hoy').css('width', pctPresentes + '%');
                $('#stat-retardos-hoy, #badge-retardos').text(s.retardos_hoy || 0);
                $('#stat-sin-salida, #badge-sin-salida').text(s.sin_salida_hoy || 0);
                $('#stat-comandos, #badge-comandos').text(s.comandos_pendientes || 0);
                if (s.empleados_sin_pin !== null && s.empleados_sin_pin !== undefined) {
                    $('#badge-sin-pin').text(s.empleados_sin_pin);
                }
                if (s.ultima_sincronizacion) {
                    var d = new Date(s.ultima_sincronizacion.replace(' ', 'T'));
                    $('#stat-ultima-sync').text(
                        ('0' + d.getDate()).slice(-2) + '/' +
                        ('0' + (d.getMonth() + 1)).slice(-2) + '/' +
                        d.getFullYear() + ' ' +
                        ('0' + d.getHours()).slice(-2) + ':' +
                        ('0' + d.getMinutes()).slice(-2)
                    );
                }

                if (result.dispositivos) {
                    var online = result.dispositivos.filter(function(d) { return d.online; }).length;
                    $('#stat-dispositivos-online').text(online + ' en línea');
                    var tbody = '';
                    result.dispositivos.forEach(function(d) {
                        var estado = d.online
                            ? '<span class="badge bg-success"><i class="fas fa-circle" style="font-size:0.5rem;"></i> En línea</span>'
                            : (d.ultima ? '<span class="badge bg-secondary">Desconectado</span>' : '<span class="badge bg-light text-muted border">Sin datos</span>');
                        var ultima = d.ultima ? d.ultima : '<span class="text-muted">Nunca</span>';
                        tbody += '<tr><td><code>' + d.sn + '</code></td><td>' + (d.alias || '—') + '</td><td>' + ultima + '</td><td>' + estado + '</td></tr>';
                    });
                    if (tbody) $('#tabla-dispositivos-status tbody').html(tbody);
                }

                if (result.ultimas_checadas) {
                    var rows = '';
                    result.ultimas_checadas.forEach(function(c) {
                        rows += '<tr><td><code>' + c.numero_empleado + '</code></td><td>' + c.empleado_nombre + '</td><td>' + c.hora + '</td><td>' + c.metodo_html + '</td></tr>';
                    });
                    $('#tabla-ultimas-checadas tbody').html(rows || '<tr><td colspan="4" class="text-muted text-center py-3">Sin checadas recientes</td></tr>');
                }

                if (chartInstance && s.checadas_7_dias) {
                    chartInstance.data.datasets[0].data = s.checadas_7_dias.map(function(d) { return d.total; });
                    chartInstance.data.labels = s.checadas_7_dias.map(function(d) { return d.fecha_corta; });
                    chartInstance.update();
                }
            } catch (e) {
                console.warn('Dashboard refresh error', e);
            }
        });
    }

    $(document).ready(function() {
        initChart();
        initAutoRefreshToggle();
        $('#btn-refresh-dashboard').on('click', actualizarDashboard);
    });
})();
</script>
