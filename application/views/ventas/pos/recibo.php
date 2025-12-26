<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Venta - <?=$orden->folio?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
        }
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body m-sm-3 m-md-5">
                        <div class="mb-4">
                            Estimado(a) <strong><?=$orden->razon_social?></strong>,
                            <br> Este es el recibo de su compra por un total de <strong>$<?=number_format($orden->total, 2)?></strong> (MXN) realizada en nuestro establecimiento.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-muted">Folio de Venta</div>
                                <strong><?=$orden->folio?></strong>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="text-muted">Fecha de Venta</div>
                                <strong><?=date('d/m/Y - h:i a', strtotime($orden->fecha_creacion))?></strong>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="text-muted">Cliente</div>
                                <strong><?=$orden->razon_social?></strong>
                                <?php if($orden->rfc): ?>
                                <p class="mb-0">
                                    <small>RFC: <?=$orden->rfc?></small>
                                </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <div class="text-muted">Vendido por</div>
                                <strong>CHISA Recubrimientos</strong>
                                <p class="mb-0">
                                    <small>RFC: XAXX010101000</small><br>
                                    <small>info@chisarecubrimientos.com.mx</small>
                                </p>
                            </div>
                        </div>

                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orden->detalles as $detalle): ?>
                                <tr>
                                    <td>
                                        <?=$detalle->nombre?>
                                        <br><small class="text-muted"><?=$detalle->codigo?></small>
                                    </td>
                                    <td class="text-center"><?=number_format($detalle->cantidad, 2)?></td>
                                    <td class="text-end">$<?=number_format($detalle->precio_unitario, 2)?></td>
                                    <td class="text-end">$<?=number_format($detalle->subtotal, 2)?></td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <tr>
                                    <th>&nbsp;</th>
                                    <th colspan="2">Subtotal</th>
                                    <th class="text-end">$<?=number_format($orden->subtotal, 2)?></th>
                                </tr>
                                <?php if($orden->descuento_aplicado > 0): ?>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th colspan="2">Descuento (<?=$orden->descuento_nombre?>)</th>
                                    <th class="text-end text-success">-$<?=number_format($orden->descuento_aplicado, 2)?></th>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th colspan="2">IVA (16%)</th>
                                    <th class="text-end">$<?=number_format($orden->iva, 2)?></th>
                                </tr>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th colspan="2"><strong>Total</strong></th>
                                    <th class="text-end"><strong>$<?=number_format($orden->total, 2)?></strong></th>
                                </tr>
                            </tbody>
                        </table>

                        <?php if($orden->observaciones): ?>
                        <div class="text-center mt-4">
                            <p class="text-sm">
                                <strong>Observaciones:</strong> <?=$orden->observaciones?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <p class="text-muted mb-3">
                                <strong>Forma de Pago:</strong> <?=$orden->forma_pago?>
                            </p>
                            
                            <button onclick="window.print()" class="btn btn-primary no-print">
                                <i class="bi bi-printer"></i> Imprimir Recibo
                            </button>
                            <a href="<?=base_url();?>ventas/Pos" class="btn btn-secondary no-print ms-2">
                                Volver al POS
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
