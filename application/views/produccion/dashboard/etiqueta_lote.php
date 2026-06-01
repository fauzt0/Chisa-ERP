<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Etiqueta Lote — <?=$lote->codigo_barras?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            background: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .etiqueta {
            border: 2px solid #000;
            border-radius: 8px;
            padding: 20px 24px;
            max-width: 380px;
            width: 100%;
        }
        .empresa {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .barcode-container {
            text-align: center;
            margin: 10px 0;
        }
        svg { max-width: 100%; }
        .info {
            font-size: 11px;
            margin-top: 12px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            line-height: 1.8;
        }
        .info strong { display: inline-block; width: 90px; }
        @media print {
            body { padding: 0; align-items: flex-start; }
            .etiqueta { border: 1px solid #000; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div>
        <div class="etiqueta" id="etiqueta_print">
            <div class="empresa">CHISA Recubrimientos</div>
            <div class="barcode-container">
                <svg id="barcode"></svg>
            </div>
            <div class="info">
                <div><strong>Producto:</strong> <?=htmlspecialchars($lote->producto_nombre)?></div>
                <div><strong>Código:</strong> <?=htmlspecialchars($lote->producto_codigo)?></div>
                <?php if($lote->formulacion_nombre): ?>
                <div><strong>Formulación:</strong> <?=htmlspecialchars($lote->formulacion_nombre)?></div>
                <?php endif; ?>
                <div><strong>Lote:</strong> <?=htmlspecialchars($lote->codigo_barras)?></div>
                <div><strong>Cantidad:</strong> <?=number_format($lote->cantidad_producida, 2)?> <?=$lote->unidad?></div>
                <div><strong>Fecha:</strong> <?=date('d/m/Y H:i', strtotime($lote->fecha_produccion))?></div>
                <?php if($lote->observaciones): ?>
                <div><strong>Obs:</strong> <?=htmlspecialchars($lote->observaciones)?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Botones solo en pantalla -->
        <div class="no-print" style="margin-top:16px; text-align:center;">
            <button onclick="window.print()"
                    style="padding:10px 24px; background:#007bff; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:14px;">
                🖨️ Imprimir
            </button>
            <button onclick="window.close()"
                    style="padding:10px 24px; background:#6c757d; color:#fff; border:none; border-radius:6px; cursor:pointer; font-size:14px; margin-left:8px;">
                Cerrar
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        JsBarcode("#barcode", "<?=addslashes($lote->codigo_barras)?>", {
            format: "CODE128",
            width: 2,
            height: 80,
            displayValue: true,
            fontSize: 12,
            margin: 8
        });
    </script>
</body>
</html>
