<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Barras - <?=$codigo?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
        
        body {
            background: #f8f9fa;
            padding: 20px;
        }
        
        .etiqueta {
            background: white;
            border: 3px solid #000;
            padding: 30px;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        
        .codigo-barras {
            margin: 30px 0;
        }
        
        .codigo-texto {
            font-size: 2rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            letter-spacing: 3px;
            margin: 20px 0;
        }
        
        .info-producto {
            font-size: 1.5rem;
            margin: 15px 0;
        }
        
        .info-lote {
            font-size: 1.2rem;
            color: #666;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="etiqueta">
        <h2>CHISA Recubrimientos</h2>
        <hr>
        
        <!-- Información del Producto -->
        <div class="info-producto">
            <strong><?=$producto->nombre?></strong><br>
            <small><?=$producto->codigo?></small>
        </div>
        
        <!-- Código de Barras (usando SVG) -->
        <div class="codigo-barras">
            <svg id="barcode"></svg>
        </div>
        
        <!-- Código en texto -->
        <div class="codigo-texto">
            <?=$codigo?>
        </div>
        
        <hr>
        
        <!-- Información del Lote -->
        <div class="info-lote">
            <strong>Lote:</strong> #<?=$lote_id?><br>
            <strong>Cantidad:</strong> <?=$producto->cantidad?> <?=$producto->unidad_medida?><br>
            <strong>Fecha Producción:</strong> <?=date('d/m/Y H:i')?><br>
            <?php if($producto->formulacion_version): ?>
            <strong>Fórmula:</strong> V<?=$producto->formulacion_version?><br>
            <?php endif; ?>
            <strong>Orden:</strong> <?=$orden->folio?>
        </div>
        
        <hr>
        
        <!-- Botones -->
        <div class="no-print mt-4">
            <button onclick="window.print()" class="btn btn-primary btn-lg">
                <i class="fas fa-print"></i> Imprimir Etiqueta
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg ms-2">
                Cerrar
            </button>
        </div>
    </div>
    
    <!-- Librería para generar código de barras -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
    <script>
        // Generar código de barras
        JsBarcode("#barcode", "<?=$codigo?>", {
            format: "CODE128",
            width: 3,
            height: 100,
            displayValue: false,
            margin: 10
        });
        
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>
</html>
