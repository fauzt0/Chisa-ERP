<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura <?=$factura->folio?> - <?=$orden->folio?></title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        .invoice-container { max-width: 800px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .logo-area { width: 60%; }
        .invoice-info { width: 40%; text-align: right; }
        
        .company-name { font-size: 24px; font-weight: bold; color: #2c3e50; margin-bottom: 5px; }
        .company-details { color: #7f8c8d; font-size: 11px; }
        
        .invoice-title { font-size: 20px; font-weight: bold; color: #2980b9; margin-bottom: 15px; border-bottom: 2px solid #2980b9; padding-bottom: 5px; }
        
        .client-info, .fiscal-info { margin-bottom: 20px; border: 1px solid #eee; padding: 15px; background: #f9f9f9; }
        .section-title { font-weight: bold; text-transform: uppercase; color: #2980b9; border-bottom: 1px solid #ddd; margin-bottom: 10px; padding-bottom: 5px; font-size: 11px; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .info-item label { display: block; font-weight: bold; font-size: 10px; color: #7f8c8d; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #2c3e50; color: white; padding: 8px; text-align: left; font-size: 11px; }
        td { border-bottom: 1px solid #eee; padding: 8px; }
        .text-right { text-align: right; }
        
        .totals-area { display: flex; justify-content: flex-end; }
        .totals-table { width: 250px; }
        .totals-table td { padding: 5px; }
        .total-row { font-weight: bold; font-size: 14px; background: #eee; }
        
        .sat-chain { margin-top: 30px; font-family: 'Courier New', monospace; font-size: 9px; border: 1px solid #ddd; padding: 10px; background: #fdfdfd; word-break: break-all; }
        .qr-placeholder { width: 120px; height: 120px; background: #eee; display: flex; align-items: center; justify-content: center; border: 1px dashed #999; }
        
        .footer { margin-top: 40px; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; color: #7f8c8d; font-size: 10px; }
        
        @media print {
            .invoice-container { border: none; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="no-print" style="margin-bottom: 20px; text-align: right;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #2980b9; color: white; border: none; border-radius: 4px;">Imprimir / Guardar PDF</button>
        </div>
        
        <div class="header">
            <div class="logo-area">
                <div class="company-name">CHISA Recubrimientos</div>
                <div class="company-details">
                    RFC: CHI200101ABC<br>
                    Régimen Fiscal: 601 - General de Ley Personas Morales<br>
                    Av. Principal 123, Col. Centro<br>
                    Guadalajara, Jalisco, CP 44100
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">FACTURA</div>
                <strong>Folio:</strong> <?=$factura->folio?><br>
                <strong>Folio Fiscal (UUID):</strong><br> <?=$factura->folio_fiscal?><br>
                <strong>Fecha Emisión:</strong> <?=$factura->fecha_emision?><br>
                <strong>Tipo Comprobante:</strong> I - Ingreso
            </div>
        </div>
        
        <div class="client-info">
            <div class="section-title">Receptor</div>
            <div class="info-grid">
                <div class="info-item">
                    <label>Razón Social</label>
                    <?=$factura->razon_social?>
                </div>
                <div class="info-item">
                    <label>RFC</label>
                    <?=$factura->rfc?>
                </div>
                <div class="info-item">
                    <label>Régimen Fiscal</label>
                    <?=$factura->regimen_fiscal?>
                </div>
                <div class="info-item">
                    <label>Uso CFDI</label>
                    <?=$factura->uso_cfdi?>
                </div>
                <div class="info-item">
                    <label>Código Postal</label>
                    <?=$factura->codigo_postal?>
                </div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th width="10%">Cant.</th>
                    <th width="10%">Unidad</th>
                    <th width="40%">Descripción</th>
                    <th width="15%" class="text-right">Precio Unit.</th>
                    <th width="10%" class="text-right">Desc.</th>
                    <th width="15%" class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orden->detalles as $item): ?>
                <tr>
                    <td><?=$item->cantidad?></td>
                    <td>PZA</td> <!-- Simplificado -->
                    <td>
                        <strong><?=$item->nombre?></strong><br>
                        <small>Clave SAT: 01010101</small>
                    </td>
                    <td class="text-right">$<?=number_format($item->precio_unitario, 2)?></td>
                    <td class="text-right"><?=$item->descuento?>%</td>
                    <td class="text-right">$<?=number_format($item->subtotal, 2)?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="totals-area">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">$<?=number_format($factura->subtotal, 2)?></td>
                </tr>
                <tr>
                    <td>IVA (16%):</td>
                    <td class="text-right">$<?=number_format($factura->iva, 2)?></td>
                </tr>
                <tr class="total-row">
                    <td>Total:</td>
                    <td class="text-right">$<?=number_format($factura->total, 2)?></td>
                </tr>
            </table>
        </div>
        
        <div style="display: flex; gap: 20px; margin-top: 30px;">
            <div class="qr-placeholder">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?=$factura->folio_fiscal?>" alt="QR">
            </div>
            <div style="flex: 1;">
                <div class="sat-chain">
                    Sello Digital del CFDI:<br>
                    <?=hash('sha256', $factura->folio_fiscal . 'SELLODIGITAL')?><br><br>
                    Sello del SAT:<br>
                    <?=hash('sha256', 'SAT' . $factura->folio_fiscal)?><br><br>
                    Cadena Original del complemento de certificación digital del SAT:<br>
                    ||1.1|<?=$factura->folio_fiscal?>|<?=$factura->fecha_emision?>|MAS0810247C0|<?=hash('sha256', $factura->folio_fiscal)?>|00001000000404486074||
                </div>
            </div>
        </div>
        
        <div class="footer">
            Este documento es una representación impresa de un CFDI (Simulado para ERP CHISA).
        </div>
    </div>
</body>
</html>
