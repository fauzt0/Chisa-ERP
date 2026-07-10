<?php
/**
 * Partial: Vinculación Obra ↔ Orden de Venta + PDF
 * Variables requeridas: $obra, $baseUrl (controlador base sin trailing slash)
 */
$baseUrl = $baseUrl ?? base_url('obras/Obras');
$tieneOrden = !empty($obra->orden_venta_id);
?>
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-primary h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-file-pdf"></i> Documentación Profesional</h5>
            </div>
            <div class="card-body d-flex flex-column justify-content-between">
                <p class="text-muted mb-3">
                    Documento profesional de 5 hojas: resumen ejecutivo, avance de obra, hoja de resumen, estimación financiera y generador de cuantificación.
                </p>
                <div>
                    <a href="<?=$baseUrl?>/exportar_pdf/<?=$obra->id?>" target="_blank" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-eye"></i> Previsualizar
                    </a>
                    <a href="<?=$baseUrl?>/exportar_pdf/<?=$obra->id?>?auto=1" target="_blank" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-download"></i> Descargar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-success h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-link"></i> Vinculación con Orden de Venta</h5>
            </div>
            <div class="card-body" id="bloqueVinculoVenta">
                <?php if($tieneOrden): ?>
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-check-circle"></i>
                        Vinculada a la orden:
                        <a href="<?=base_url('ventas/Ordenes')?>" class="alert-link fw-bold" id="linkOrdenVenta">
                            <?=$obra->orden_venta_folio?>
                        </a>
                        <span class="badge bg-info ms-1"><?=$obra->orden_venta_estatus?></span>
                    </div>
                    <?php if(($obra->orden_venta_estatus ?? '') === 'Cotización'): ?>
                    <button type="button" class="btn btn-warning" id="btnConfirmarOrdenVenta" data-obra-id="<?=$obra->id?>">
                        <i class="fas fa-paper-plane"></i> Confirmar y enviar a Producción
                    </button>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted mb-3">Esta obra aún no está vinculada a una orden de venta del CRM.</p>
                    <button type="button" class="btn btn-success me-2" id="btnGenerarOrdenVenta" data-obra-id="<?=$obra->id?>">
                        <i class="fas fa-plus-circle"></i> Generar Orden de Venta
                    </button>
                    <button type="button" class="btn btn-outline-success" id="btnAbrirModalVincular" data-obra-id="<?=$obra->id?>">
                        <i class="fas fa-link"></i> Vincular Existente
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Vincular Orden de Venta -->
<div class="modal fade" id="modalVincularOrdenVenta" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-link"></i> Vincular Orden de Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Seleccione una orden de venta pendiente del mismo cliente:</p>
                <select class="form-select" id="selectOrdenVentaVincular">
                    <option value="">Cargando órdenes...</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarVincular">
                    <i class="fas fa-check"></i> Vincular
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var baseUrl = '<?=$baseUrl?>';
    var obraId = <?=(int)$obra->id?>;

    function postAjax(url, data, onSuccess) {
        data['<?=$this->security->get_csrf_token_name()?>'] = '<?=$this->security->get_csrf_hash()?>';
        $.post(url, data, function(resp) {
            var result = typeof resp === 'string' ? JSON.parse(resp) : resp;
            if (result.success) {
                if (typeof showErpToast === 'function') {
                    showErpToast({ type: 'success', module: 'Obras', title: 'Éxito', message: result.message });
                }
                if (onSuccess) onSuccess(result);
                else setTimeout(function() { location.reload(); }, 800);
            } else {
                if (typeof showErpToast === 'function') {
                    showErpToast({ type: 'danger', module: 'Obras', title: 'Error', message: result.message || 'Operación fallida' });
                }
            }
        }).fail(function() {
            if (typeof showErpToast === 'function') {
                showErpToast({ type: 'danger', module: 'Obras', title: 'Error', message: 'Error de comunicación con el servidor' });
            }
        });
    }

    $('#btnGenerarOrdenVenta').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true);
        postAjax(baseUrl + '/generar_orden_venta_ajax', { obra_id: obraId }, function() {
            setTimeout(function() { location.reload(); }, 800);
        });
        setTimeout(function() { btn.prop('disabled', false); }, 3000);
    });

    $('#btnAbrirModalVincular').on('click', function() {
        var modalEl = document.getElementById('modalVincularOrdenVenta');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        $('#selectOrdenVentaVincular').html('<option value="">Cargando...</option>');
        modal.show();

        $.get(baseUrl + '/get_ordenes_venta_disponibles_ajax', { obra_id: obraId }, function(resp) {
            var result = typeof resp === 'string' ? JSON.parse(resp) : resp;
            var html = '<option value="">— Seleccione una orden —</option>';
            if (result.success && result.ordenes && result.ordenes.length) {
                result.ordenes.forEach(function(o) {
                    html += '<option value="' + o.id + '">' + o.folio + ' — ' + o.estatus + ' — $' + parseFloat(o.total).toFixed(2) + '</option>';
                });
            } else {
                html = '<option value="">No hay órdenes disponibles para este cliente</option>';
            }
            $('#selectOrdenVentaVincular').html(html);
        });
    });

    $('#btnConfirmarVincular').on('click', function() {
        var ordenId = $('#selectOrdenVentaVincular').val();
        if (!ordenId) {
            showErpToast({ type: 'warning', module: 'Obras', title: 'Atención', message: 'Seleccione una orden de venta' });
            return;
        }
        postAjax(baseUrl + '/vincular_orden_venta_ajax', { obra_id: obraId, orden_venta_id: ordenId }, function() {
            bootstrap.Modal.getInstance(document.getElementById('modalVincularOrdenVenta')).hide();
            setTimeout(function() { location.reload(); }, 800);
        });
    });

    $('#btnConfirmarOrdenVenta').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true);
        postAjax(baseUrl + '/confirmar_orden_venta_ajax', { obra_id: obraId }, function() {
            setTimeout(function() { location.reload(); }, 800);
        });
        setTimeout(function() { btn.prop('disabled', false); }, 3000);
    });
})();
</script>
