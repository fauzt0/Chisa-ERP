<div class="container-fluid p-0">
    <div class="row mb-3">
        <div class="col-md-8">
            <h3><strong><i class="fas fa-barcode"></i> Consultar Lote</strong></h3>
            <p class="text-muted mb-0">Escanee o capture el código de barras del lote para ver producto, cubeta, venta u obra.</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="<?=base_url()?>produccion/Lotes" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> Historial de Lotes
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <label for="input_codigo_barras" class="form-label fw-bold">Código de barras del lote</label>
                    <div class="input-group input-group-lg mb-3">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        <input type="text" class="form-control" id="input_codigo_barras"
                               placeholder="Escanee o escriba el código…" autocomplete="off" autofocus>
                        <button type="button" class="btn btn-primary" id="btn_consultar">
                            <i class="fas fa-search"></i> Consultar
                        </button>
                    </div>
                    <div id="consulta_alerta" class="alert d-none" role="alert"></div>
                </div>
            </div>

            <div class="card shadow-sm mt-3 d-none" id="card_resultado">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-check-circle"></i> Lote encontrado</span>
                    <a href="#" id="btn_imprimir_etiqueta" target="_blank" class="btn btn-sm btn-light">
                        <i class="fas fa-print"></i> Imprimir etiqueta
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3" id="resultado_grid"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const CSRF_NAME = '<?=$this->security->get_csrf_token_name()?>';
    const CSRF_HASH = '<?=$this->security->get_csrf_hash()?>';
    const BASE_ETIQUETA = '<?=base_url()?>produccion/Dashboard/etiqueta_lote/';
    const ENDPOINT = '<?=base_url()?>produccion/Lotes/consultar_lote_ajax';

    function escHtml(str) {
        if (str === null || str === undefined) return '—';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function etiquetaPrefsQuery() {
        try {
            const size = localStorage.getItem('chisa_etiqueta_prefs_size') || '100x50';
            const zoom = localStorage.getItem('chisa_etiqueta_prefs_zoom') || '100';
            return 'size=' + encodeURIComponent(size) + '&zoom=' + encodeURIComponent(zoom);
        } catch (e) {
            return 'size=100x50&zoom=100';
        }
    }

    function renderResultado(lote) {
        const campos = [
            ['Producto', lote.producto_nombre],
            ['Código producto', lote.producto_codigo],
            ['Lote / Código barras', lote.codigo_barras],
            ['Cubeta / Cantidad', lote.cubeta || lote.cantidad_display],
            ['Formulación', lote.formulacion_nombre ? (lote.formulacion_nombre + (lote.formulacion_version ? ' v' + lote.formulacion_version : '')) : '—'],
            ['Orden de venta', lote.orden_venta_folio ? ('OV ' + lote.orden_venta_folio) : '—'],
            ['Obra', lote.obra_folio ? ('Obra ' + lote.obra_folio) : '—'],
            ['Fecha producción', lote.fecha_display],
            ['Estatus', lote.estatus || '—'],
        ];

        let html = '';
        campos.forEach(function(pair) {
            html += '<div class="col-md-6">';
            html += '<div class="border rounded p-3 h-100">';
            html += '<small class="text-muted d-block">' + escHtml(pair[0]) + '</small>';
            html += '<strong>' + escHtml(pair[1]) + '</strong>';
            html += '</div></div>';
        });

        $('#resultado_grid').html(html);
        $('#btn_imprimir_etiqueta').attr('href', BASE_ETIQUETA + lote.id + '?' + etiquetaPrefsQuery());
        $('#card_resultado').removeClass('d-none');
    }

    function consultar() {
        const codigo = $('#input_codigo_barras').val().trim();
        const $alerta = $('#consulta_alerta');

        if (!codigo) {
            $alerta.removeClass('d-none alert-success').addClass('alert-warning')
                .html('<i class="fas fa-exclamation-triangle"></i> Ingrese un código de barras.');
            $('#card_resultado').addClass('d-none');
            return;
        }

        $('#btn_consultar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        const postData = { codigo_barras: codigo };
        postData[CSRF_NAME] = CSRF_HASH;

        $.post(ENDPOINT, postData, function(res) {
            $('#btn_consultar').prop('disabled', false).html('<i class="fas fa-search"></i> Consultar');

            if (res.success && res.lote) {
                $alerta.addClass('d-none');
                renderResultado(res.lote);
            } else {
                $('#card_resultado').addClass('d-none');
                $alerta.removeClass('d-none alert-success').addClass('alert-danger')
                    .html('<i class="fas fa-times-circle"></i> ' + escHtml(res.message || 'Lote no encontrado'));
            }
        }, 'json').fail(function() {
            $('#btn_consultar').prop('disabled', false).html('<i class="fas fa-search"></i> Consultar');
            $('#card_resultado').addClass('d-none');
            $alerta.removeClass('d-none alert-success').addClass('alert-danger')
                .html('<i class="fas fa-times-circle"></i> Error de comunicación con el servidor.');
        });
    }

    $('#btn_consultar').on('click', consultar);
    $('#input_codigo_barras').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            consultar();
        }
    });

    // Auto-consulta si viene ?codigo= en URL (deep link desde escáner externo)
    const urlCodigo = new URLSearchParams(window.location.search).get('codigo');
    if (urlCodigo) {
        $('#input_codigo_barras').val(urlCodigo);
        consultar();
    }
})();
</script>
