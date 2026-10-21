<?php defined('BASEPATH') OR exit('No direct script access allowed');
$obra_id = (int) ($obra_id ?? 0);
if ($obra_id <= 0) {
    return;
}
?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-truck"></i> Seguimiento de Entregas</h5>
                <button type="button" class="btn btn-light btn-sm" onclick="cargarEntregasObraVista()">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </div>
            <div class="card-body">
                <div id="entregas_loading" class="text-center py-4 text-muted d-none">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2 mb-0">Cargando entregas…</p>
                </div>
                <div id="entregas_productos" class="table-responsive mb-4"></div>
                <div id="entregas_historial"></div>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    const OBRA_ENTREGAS_ID = <?= (int) $obra_id ?>;

    function escHtmlEntregas(s) {
        if (s === null || s === undefined) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    window.cargarEntregasObraVista = function() {
        const loading = document.getElementById('entregas_loading');
        if (!loading) return;
        loading.classList.remove('d-none');
        document.getElementById('entregas_productos').innerHTML = '';
        document.getElementById('entregas_historial').innerHTML = '';

        fetch('<?= base_url() ?>obras/Obras/get_entregas_obra_ajax?obra_id=' + OBRA_ENTREGAS_ID)
            .then(r => r.json())
            .then(res => {
                loading.classList.add('d-none');
                if (!res.success) {
                    document.getElementById('entregas_productos').innerHTML =
                        '<div class="alert alert-warning">' + escHtmlEntregas(res.message) + '</div>';
                    return;
                }

                const prods = res.entregas.productos || [];
                if (prods.length === 0) {
                    document.getElementById('entregas_productos').innerHTML =
                        '<p class="text-muted text-center py-3">Sin productos en esta obra.</p>';
                } else {
                    let html = '<h6 class="mb-2">Estado por producto</h6><table class="table table-sm table-hover table-bordered">';
                    html += '<thead class="table-dark"><tr><th>Producto</th><th>Sección</th><th>Solicitado</th><th>Entregado</th><th>Pendiente</th><th>Unidad</th><th>% Avance</th></tr></thead><tbody>';
                    prods.forEach(p => {
                        const solicitado = parseFloat(p.cantidad_ajustada || p.cantidad_calculada || 0);
                        const entregado = parseFloat(p.cantidad_entregada || 0);
                        const pendiente = Math.max(solicitado - entregado, 0);
                        const pct = solicitado > 0 ? Math.min((entregado / solicitado) * 100, 100) : 0;
                        const color = pct >= 100 ? 'success' : (pct > 0 ? 'warning' : 'danger');
                        html += `<tr>
                            <td><strong>${escHtmlEntregas(p.producto_nombre)}</strong><br><small class="text-muted">${escHtmlEntregas(p.producto_codigo)}</small></td>
                            <td>${escHtmlEntregas(p.seccion_obra || '—')}</td>
                            <td class="text-center">${solicitado.toFixed(2)}</td>
                            <td class="text-center text-success fw-bold">${entregado.toFixed(2)}</td>
                            <td class="text-center ${pendiente > 0 ? 'text-danger' : 'text-success'}">${pendiente.toFixed(2)}</td>
                            <td class="text-center">${escHtmlEntregas(p.unidad)}</td>
                            <td style="min-width:100px">
                                <div class="progress" style="height:18px;">
                                    <div class="progress-bar bg-${color}" role="progressbar" style="width:${pct.toFixed(0)}%">${pct.toFixed(0)}%</div>
                                </div>
                            </td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('entregas_productos').innerHTML = html;
                }

                const hist = res.entregas.historial || [];
                if (hist.length > 0) {
                    let hhtml = '<h6 class="mt-3 mb-2">Historial de entregas</h6><table class="table table-sm table-striped">';
                    hhtml += '<thead class="table-light"><tr><th>Folio</th><th>Fecha</th><th>Producto</th><th>Cantidad</th></tr></thead><tbody>';
                    hist.forEach(h => {
                        hhtml += `<tr>
                            <td><strong>${escHtmlEntregas(h.folio)}</strong></td>
                            <td>${new Date(h.fecha_entrega).toLocaleDateString('es-MX')}</td>
                            <td>${escHtmlEntregas(h.producto_nombre)}</td>
                            <td class="text-end">${parseFloat(h.cantidad_entregada).toFixed(2)}</td>
                        </tr>`;
                    });
                    hhtml += '</tbody></table>';
                    document.getElementById('entregas_historial').innerHTML = hhtml;
                } else {
                    document.getElementById('entregas_historial').innerHTML =
                        '<p class="text-muted small mt-2"><i class="fas fa-info-circle"></i> Aún no hay entregas registradas. Gestión en <a href="<?= base_url('almacen/Entregas') ?>">Almacén &gt; Entregas</a>.</p>';
                }
            })
            .catch(() => {
                loading.classList.add('d-none');
                document.getElementById('entregas_productos').innerHTML =
                    '<div class="alert alert-danger">Error al cargar entregas.</div>';
            });
    };

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('entregas_productos')) {
            cargarEntregasObraVista();
        }
    });
})();
</script>
