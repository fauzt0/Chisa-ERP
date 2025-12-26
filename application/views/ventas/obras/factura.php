<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura - <?=$factura->folio?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; }
        .factura { max-width: 800px; margin: 0 auto; border: 2px solid #333; padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #333; padding-bottom: 20px; }
        .header h1 { color: #333; margin-bottom: 10px; }
        .header .folio { font-size: 24px; color: #0066cc; font-weight: bold; }
        .info-section { margin-bottom: 20px; }
        .info-section h3 { background: #f0f0f0; padding: 8px; margin-bottom: 10px; border-left: 4px solid #0066cc; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box { padding: 15px; border: 1px solid #ddd; }
        .info-box p { margin: 5px 0; }
        .info-box strong { color: #333; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table th { background: #333; color: white; padding: 12px; text-align: left; }
        table td { padding: 10px; border-bottom: 1px solid #ddd; }
        table tr:hover { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totales { margin-top: 20px; float: right; width: 300px; }
        .totales table { margin: 0; }
        .totales td { padding: 8px; }
        .totales .total-final { font-size: 18px; font-weight: bold; background: #f0f0f0; }
        .footer { clear: both; margin-top: 40px; padding-top: 20px; border-top: 2px solid #333; text-align: center; font-size: 12px; color: #666; }
        .btn-print { background: #0066cc; color: white; padding: 10px 20px; border: none; cursor: pointer; margin: 20px 0; }
        .btn-print:hover { background: #0052a3; }
        @media print {
            .btn-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir Factura</button>
    
    <div class="factura">
        <!-- Header -->
        <div class="header">
            <h1>FACTURA</h1>
            <div class="folio"><?=$factura->folio?></div>
            <p>Fecha de Emisión: <?=date('d/m/Y H:i', strtotime($factura->fecha_emision))?></p>
        </div>
        <!-- Datos Fiscales -->
        <div class="info-grid">
            <!-- Emisor -->
            <div class="info-box">
                <h3>EMISOR</h3>
                <p><strong><?=$factura->razon_social_emisor?></strong></p>
                <p>RFC: <?=$factura->rfc_emisor?></p>
                <?php if($factura->direccion_emisor): ?>
                <p><?=$factura->direccion_emisor?></p>
                <?php endif; ?>
            </div>
            <!-- Receptor -->
            <div class="info-box">
                <h3>RECEPTOR</h3>
                <p><strong><?=$factura->razon_social_receptor?></strong></p>
                <p>RFC: <?=$factura->rfc_receptor?></p>
                <?php if($factura->direccion_receptor): ?>
                <p><?=$factura->direccion_receptor?></p>
                <?php endif; ?>
            </div>
        </div>
        <!-- Información de la Obra -->
        <div class="info-section">
            <h3>DATOS DE LA OBRA</h3>
            <p><strong>Folio:</strong> <?=$obra->folio?></p>
            <p><strong>Nombre:</strong> <?=$obra->nombre?></p>
            <?php if($obra->area_total): ?>
            <p><strong>Área Total:</strong> <?=number_format($obra->area_total, 2)?> m²</p>
            <?php endif; ?>
            <?php if($obra->direccion): ?>
            <p><strong>Ubicación:</strong> <?=$obra->direccion?></p>
            <?php endif; ?>
        </div>
        <!-- Productos/Servicios -->
        <div class="info-section">
            <h3>CONCEPTOS</h3>
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-right">Precio Unit.</th>
                        <th class="text-right">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($obra->productos as $producto): 
                        $cantidad = $producto->cantidad_ajustada ?? $producto->cantidad_calculada;
                        $precio = $producto->precio_unitario ?? 0;
                        $importe = $cantidad * $precio;
                    ?>
                    <tr>
                        <td><?=$producto->producto_codigo?></td>
                        <td><?=$producto->producto_nombre?></td>
                        <td class="text-center"><?=number_format($cantidad, 2)?></td>
                        <td class="text-right">$<?=number_format($precio, 2)?></td>
                        <td class="text-right">$<?=number_format($importe, 2)?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Totales -->
        <div class="totales">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">$<?=number_format($factura->subtotal, 2)?></td>
                </tr>
                <tr>
                    <td>IVA (16%):</td>
                    <td class="text-right">$<?=number_format($factura->iva, 2)?></td>
                </tr>
                <tr class="total-final">
                    <td>TOTAL:</td>
                    <td class="text-right">$<?=number_format($factura->total, 2)?></td>
                </tr>
            </table>
        </div>
        <!-- Footer -->
        <div class="footer">
            <p>Este documento es una representación impresa de una factura electrónica</p>
            <p>Generado el <?=date('d/m/Y H:i')?></p>
        </div>
    </div>
</body>
</html>