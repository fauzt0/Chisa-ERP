<?php
$e = $etiqueta ?? (array) ($lote ?? []);
if (is_object($e)) {
    $e = (array) $e;
}
$logoUrl = $logo_url ?? base_url('assets/dist/img/brands/chisa_recubrimientos_logo.jpg');
$sizeInit = in_array($size ?? '', ['50x25', '100x50'], true) ? $size : '100x50';
$zoomInit = (int) ($zoom ?? 100);
if ($zoomInit < 50 || $zoomInit > 200) {
    $zoomInit = 100;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Etiqueta — <?=htmlspecialchars($e['codigo_barras'] ?? '')?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --label-w: 100mm;
            --label-h: 50mm;
            --zoom: 1;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #e9ecef;
            min-height: 100vh;
            padding: 16px;
        }
        .toolbar {
            max-width: 520px;
            margin: 0 auto 16px;
            background: #fff;
            border-radius: 8px;
            padding: 12px 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,.12);
        }
        .toolbar label { font-size: 12px; font-weight: 600; color: #555; }
        .toolbar-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 8px; }
        .btn-size {
            border: 1px solid #ccc;
            background: #f8f9fa;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-size.active { background: #0d6efd; color: #fff; border-color: #0d6efd; }
        .preview-wrap {
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .etiqueta-scale {
            transform: scale(var(--zoom));
            transform-origin: top center;
        }
        .etiqueta {
            width: var(--label-w);
            height: var(--label-h);
            background: #fff;
            border: 1px solid #000;
            padding: 2mm 3mm;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .etiqueta-header {
            display: flex;
            align-items: center;
            gap: 2mm;
            border-bottom: 0.3mm solid #000;
            padding-bottom: 1mm;
            margin-bottom: 1mm;
            flex-shrink: 0;
        }
        .logo {
            height: 8mm;
            max-width: 28mm;
            object-fit: contain;
        }
        .empresa-nombre {
            font-size: 2.2mm;
            font-weight: 700;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 0.2mm;
        }
        .barcode-wrap {
            text-align: center;
            flex-shrink: 0;
        }
        .barcode-wrap svg { max-width: 100%; height: auto; }
        .info {
            flex: 1;
            font-size: 2mm;
            line-height: 1.35;
            overflow: hidden;
        }
        .info-row { display: flex; gap: 1mm; }
        .info-row .lbl { font-weight: 700; min-width: 14mm; flex-shrink: 0; }
        .info-row .val { flex: 1; word-break: break-word; }
        .info-row.destacado .val { font-weight: 700; font-size: 2.3mm; }

        /* Tamaño compacto 50×25 */
        body.size-50x25 .logo { height: 5mm; max-width: 18mm; }
        body.size-50x25 .empresa-nombre { font-size: 1.6mm; }
        body.size-50x25 .info { font-size: 1.5mm; line-height: 1.25; }
        body.size-50x25 .info-row .lbl { min-width: 10mm; }
        body.size-50x25 .info-row.destacado .val { font-size: 1.7mm; }

        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .preview-wrap { display: block; }
            .etiqueta-scale { transform: none !important; }
            .etiqueta { border: 0.2mm solid #000; page-break-inside: avoid; }
        }
        @page {
            size: var(--label-w) var(--label-h);
            margin: 0;
        }
    </style>
</head>
<body class="size-<?=htmlspecialchars($sizeInit)?>">

<div class="toolbar no-print">
    <label>Tamaño de etiqueta</label>
    <div class="toolbar-row">
        <button type="button" class="btn-size" data-size="50x25">50 × 25 mm</button>
        <button type="button" class="btn-size" data-size="100x50">100 × 50 mm</button>
    </div>
    <label style="margin-top:10px;display:block;">Zoom vista previa: <span id="zoom_val"><?=$zoomInit?>%</span></label>
    <div class="toolbar-row">
        <input type="range" id="zoom_range" min="50" max="200" step="5" value="<?=$zoomInit?>" style="flex:1;">
        <button type="button" onclick="window.print()" style="padding:8px 16px;background:#0d6efd;color:#fff;border:none;border-radius:6px;cursor:pointer;">🖨️ Imprimir</button>
        <button type="button" onclick="window.close()" style="padding:8px 16px;background:#6c757d;color:#fff;border:none;border-radius:6px;cursor:pointer;">Cerrar</button>
    </div>
    <p style="font-size:11px;color:#666;margin-top:8px;">Logo: assets/dist/img/brands/chisa_recubrimientos_logo.jpg</p>
</div>

<div class="preview-wrap">
    <div class="etiqueta-scale" id="etiqueta_scale">
        <div class="etiqueta" id="etiqueta_print">
            <div class="etiqueta-header">
                <img src="<?=htmlspecialchars($logoUrl)?>" alt="CHISA Recubrimientos" class="logo">
                <div class="empresa-nombre">CHISA Recubrimientos</div>
            </div>
            <div class="barcode-wrap">
                <svg id="barcode"></svg>
            </div>
            <div class="info">
                <div class="info-row destacado">
                    <span class="lbl">Producto:</span>
                    <span class="val"><?=htmlspecialchars($e['producto_nombre'] ?? '')?></span>
                </div>
                <div class="info-row">
                    <span class="lbl">Código:</span>
                    <span class="val"><?=htmlspecialchars($e['producto_codigo'] ?? '')?></span>
                </div>
                <div class="info-row">
                    <span class="lbl">Lote:</span>
                    <span class="val"><?=htmlspecialchars($e['codigo_barras'] ?? '')?></span>
                </div>
                <div class="info-row destacado">
                    <span class="lbl">Cubeta:</span>
                    <span class="val"><?=htmlspecialchars($e['cubeta'] ?? $e['cantidad_display'] ?? '')?></span>
                </div>
                <?php if (!empty($e['formulacion_nombre'])): ?>
                <div class="info-row">
                    <span class="lbl">Formulación:</span>
                    <span class="val"><?=htmlspecialchars($e['formulacion_nombre'])?><?=!empty($e['formulacion_version']) ? ' v'.htmlspecialchars($e['formulacion_version']) : ''?></span>
                </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="lbl">Origen:</span>
                    <span class="val"><?=htmlspecialchars($e['origen_etiqueta'] ?? '—')?></span>
                </div>
                <div class="info-row">
                    <span class="lbl">Fecha:</span>
                    <span class="val"><?=htmlspecialchars($e['fecha_display'] ?? '')?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
(function() {
    const PRESETS = {
        '50x25':  { w: '50mm',  h: '25mm',  bcH: 28, bcW: 1.2, bcFont: 8,  showText: false },
        '100x50': { w: '100mm', h: '50mm',  bcH: 42, bcW: 1.8, bcFont: 10, showText: true  }
    };
    const STORAGE_KEY = 'chisa_etiqueta_prefs';
    const params = new URLSearchParams(window.location.search);
    let size = params.get('size') || localStorage.getItem(STORAGE_KEY + '_size') || '<?=addslashes($sizeInit)?>';
    let zoom = parseInt(params.get('zoom') || localStorage.getItem(STORAGE_KEY + '_zoom') || '<?=$zoomInit?>', 10);
    if (!PRESETS[size]) size = '100x50';
    if (isNaN(zoom) || zoom < 50 || zoom > 200) zoom = 100;

    const codigo = <?=json_encode($e['codigo_barras'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT)?>;

    function applySize(s) {
        size = s;
        const p = PRESETS[s];
        document.documentElement.style.setProperty('--label-w', p.w);
        document.documentElement.style.setProperty('--label-h', p.h);
        document.body.className = 'size-' + s;
        document.querySelectorAll('.btn-size').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.size === s);
        });
        JsBarcode('#barcode', codigo, {
            format: 'CODE128',
            width: p.bcW,
            height: p.bcH,
            displayValue: p.showText,
            fontSize: p.bcFont,
            margin: 2,
            textMargin: 1
        });
        persist();
        syncUrl();
    }

    function applyZoom(z) {
        zoom = z;
        document.documentElement.style.setProperty('--zoom', (z / 100).toFixed(2));
        document.getElementById('zoom_val').textContent = z + '%';
        document.getElementById('zoom_range').value = z;
        persist();
        syncUrl();
    }

    function persist() {
        try {
            localStorage.setItem(STORAGE_KEY + '_size', size);
            localStorage.setItem(STORAGE_KEY + '_zoom', String(zoom));
        } catch (e) {}
    }

    function syncUrl() {
        const u = new URL(window.location.href);
        u.searchParams.set('size', size);
        u.searchParams.set('zoom', zoom);
        history.replaceState(null, '', u.toString());
    }

    document.querySelectorAll('.btn-size').forEach(btn => {
        btn.addEventListener('click', () => applySize(btn.dataset.size));
    });
    document.getElementById('zoom_range').addEventListener('input', e => applyZoom(parseInt(e.target.value, 10)));

    applySize(size);
    applyZoom(zoom);
})();
</script>
</body>
</html>
