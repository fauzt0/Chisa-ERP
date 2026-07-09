<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo <?=$orden->folio?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .card { box-shadow: none !important; }
        }
        .no-print { margin-bottom: 1rem; }
        .template-selector { background: #f8f9fa; padding: 10px 0; border-bottom: 1px solid #dee2e6; }
        .recibo-logo { max-height: 90px; max-width: 220px; object-fit: contain; }
        .recibo-logo-sm { max-height: 75px; max-width: 200px; object-fit: contain; }
        .recibo-logo-lg { max-height: 110px; max-width: 260px; object-fit: contain; }
    </style>
</head>
<body>
<?php
// Logotipo y datos fiscales desde usuarios/GestionUsuarios/empresa (configuracion_empresa)
$emp = $empresa ?? null;
$logoUrl = !empty($emp->logo) ? base_url($emp->logo) : base_url('assets/dist/img/brands/chisa_recubrimientos_logo.jpg');
$nombreEmpresa = $emp->razon_social ?? 'CHISA Recubrimientos S.A. de C.V.';
$rfcEmpresa = !empty($emp->rfc) ? $emp->rfc : 'CRE940302AB1';
$emailEmpresa = !empty($emp->email) ? $emp->email : 'info@chisarecubrimientos.com.mx';
$telefonoEmpresa = !empty($emp->telefono) ? $emp->telefono : '(55) 1234-5678';
$direccionEmpresa = trim(implode(', ', array_filter([
    $emp->calle ?? '',
    $emp->numero_exterior ?? '',
    $emp->colonia ?? '',
    $emp->ciudad ?? '',
    $emp->estado ?? '',
    $emp->codigo_postal ?? '',
])));
if (!$direccionEmpresa) {
    $direccionEmpresa = 'Dirección de la empresa';
}
?>

<!-- Barra de selección de template + botón imprimir -->
<div class="no-print template-selector">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <small class="text-muted me-1"><strong>Diseño:</strong></small>
            <a href="<?=base_url()?>ventas/Pos/imprimir_recibo_template/<?=$orden->id?>/1"
               class="btn btn-sm <?=$template==1?'btn-dark':'btn-outline-dark'?>">
                <i class="fas fa-file-invoice me-1"></i>Factura
            </a>
            <a href="<?=base_url()?>ventas/Pos/imprimir_recibo_template/<?=$orden->id?>/2"
               class="btn btn-sm <?=$template==2?'btn-primary':'btn-outline-primary'?>">
                <i class="fas fa-file-alt me-1"></i>Remisión
            </a>
            <a href="<?=base_url()?>ventas/Pos/imprimir_recibo_template/<?=$orden->id?>/3"
               class="btn btn-sm <?=$template==3?'btn-success':'btn-outline-success'?>">
                <i class="fas fa-star me-1"></i>Moderno
            </a>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print me-1"></i>Imprimir
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-times me-1"></i>Cerrar
            </button>
        </div>
    </div>
</div>

<?php if($template == 1): ?>
<!-- ====================================================
     TEMPLATE 1 — FORMATO FACTURA CLÁSICO
     ==================================================== -->
<div class="container my-4">

    <!-- Encabezado empresa / folio -->
    <div class="row mb-3 align-items-start">
        <div class="col-6">
            <img src="<?=$logoUrl?>" alt="<?=htmlspecialchars($nombreEmpresa)?>" class="recibo-logo">
        </div>
        <div class="col-6 text-end">
            <h5 class="mb-1 fw-bold" style="color:#333;"><?=htmlspecialchars($nombreEmpresa)?></h5>
            <p class="mb-0 small" style="color:#666;">RFC: <?=htmlspecialchars($rfcEmpresa)?></p>
            <p class="mb-0 small" style="color:#666;"><?=htmlspecialchars($emailEmpresa)?></p>
            <p class="mb-0 small" style="color:#666;">Tel: <?=htmlspecialchars($telefonoEmpresa)?></p>
            <p class="mb-0 small" style="color:#666;"><?=htmlspecialchars($direccionEmpresa)?></p>
        </div>
    </div>
    <hr style="border-top:2px solid #333;">

    <!-- Datos cliente / venta -->
    <div class="row mb-4">
        <div class="col-md-6">
            <p class="mb-0 text-muted small">Cliente</p>
            <h6 class="fw-bold mb-0"><?=$orden->razon_social?></h6>
            <?php if(!empty($orden->rfc)): ?>
                <p class="mb-0 small text-muted">RFC: <?=$orden->rfc?></p>
            <?php endif; ?>
        </div>
        <div class="col-md-6 text-md-end">
            <p class="mb-0 text-muted small">Folio</p>
            <h6 class="fw-bold mb-0"><?=$orden->folio?></h6>
            <p class="mb-0 text-muted small">Fecha: <?=date('d/m/Y H:i', strtotime($orden->fecha_creacion))?></p>
            <p class="mb-0 text-muted small">Pago: <?=$orden->forma_pago?></p>
        </div>
    </div>

    <!-- Tabla de productos -->
    <table class="table table-sm">
        <thead style="background-color:#f0f0f0;">
            <tr>
                <th style="color:#333;">Producto</th>
                <th style="color:#333;">Código</th>
                <th class="text-center" style="color:#333;">Cant.</th>
                <th class="text-end" style="color:#333;">P. Unit.</th>
                <th class="text-end" style="color:#333;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orden->detalles as $det): ?>
            <tr>
                <td><?=$det->nombre?></td>
                <td class="small text-muted"><?=$det->codigo?></td>
                <td class="text-center"><?=number_format($det->cantidad, 2)?></td>
                <td class="text-end">$<?=number_format($det->precio_unitario, 2)?></td>
                <td class="text-end">$<?=number_format($det->subtotal, 2)?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totales -->
    <div class="row justify-content-end">
        <div class="col-md-4">
            <div class="d-flex justify-content-between">
                <span style="color:#666;">Subtotal</span>
                <span>$<?=number_format($orden->subtotal, 2)?></span>
            </div>
            <?php if($orden->descuento_aplicado > 0): ?>
            <div class="d-flex justify-content-between text-success">
                <span>Descuento <?=$orden->descuento_nombre ? '('.$orden->descuento_nombre.')' : ''?></span>
                <span>-$<?=number_format($orden->descuento_aplicado, 2)?></span>
            </div>
            <?php endif; ?>
            <div class="d-flex justify-content-between">
                <span style="color:#666;">IVA</span>
                <span>$<?=number_format($orden->iva, 2)?></span>
            </div>
            <hr style="border-top:1px solid #333;">
            <div class="d-flex justify-content-between fw-bold">
                <span>Total</span>
                <span>$<?=number_format($orden->total, 2)?></span>
            </div>
        </div>
    </div>

    <?php if($orden->observaciones): ?>
    <div class="mt-3 p-3" style="background-color:#f8f8f8;">
        <p class="mb-0 small"><strong>Observaciones:</strong> <?=$orden->observaciones?></p>
    </div>
    <?php endif; ?>

    <p class="text-center mt-4 text-muted small">Gracias por su preferencia</p>
</div>

<?php elseif($template == 2): ?>
<!-- ====================================================
     TEMPLATE 2 — NOTA DE REMISIÓN CON DESGLOSE
     ==================================================== -->
<div class="container-fluid">

    <!-- Encabezado azul oscuro -->
    <div class="row py-4" style="background-color:#1a237e; color:#fff;">
        <div class="col-12 text-center">
            <div class="d-inline-block bg-white rounded px-4 py-2 mb-2">
                <img src="<?=$logoUrl?>" alt="<?=htmlspecialchars($nombreEmpresa)?>" class="recibo-logo-sm">
            </div>
            <p class="mb-0 small opacity-75"><?=htmlspecialchars($nombreEmpresa)?></p>
        </div>
    </div>

    <div class="container my-4">

        <!-- Empresa / Datos venta -->
        <div class="row mb-4">
            <div class="col-md-6 mb-2">
                <div class="border p-3 h-100">
                    <h6 class="fw-bold mb-2" style="color:#1a237e;">Empresa</h6>
                    <p class="mb-0 small"><?=htmlspecialchars($nombreEmpresa)?></p>
                    <p class="mb-0 small">RFC: <?=htmlspecialchars($rfcEmpresa)?></p>
                    <p class="mb-0 small"><?=htmlspecialchars($emailEmpresa)?></p>
                    <p class="mb-0 small">Tel: <?=htmlspecialchars($telefonoEmpresa)?></p>
                    <p class="mb-0 small"><?=htmlspecialchars($direccionEmpresa)?></p>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <div class="border p-3 h-100" style="border-color:#1a237e !important;">
                    <h6 class="fw-bold mb-2" style="color:#1a237e;">Datos de la Venta</h6>
                    <p class="mb-0 small"><strong>Folio:</strong> <?=$orden->folio?></p>
                    <p class="mb-0 small"><strong>Fecha:</strong> <?=date('d/m/Y H:i', strtotime($orden->fecha_creacion))?></p>
                    <p class="mb-0 small"><strong>Forma de Pago:</strong> <?=$orden->forma_pago?></p>
                    <p class="mb-0 small"><strong>Cliente:</strong> <?=$orden->razon_social?></p>
                    <?php if(!empty($orden->rfc)): ?>
                        <p class="mb-0 small"><strong>RFC:</strong> <?=$orden->rfc?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tabla productos -->
        <table class="table table-striped table-sm table-bordered">
            <thead>
                <tr style="background-color:#1a237e; color:#fff;">
                    <th>Producto</th>
                    <th>Código</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-end">P. Unit.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orden->detalles as $det): ?>
                <tr>
                    <td>
                        <?=$det->nombre?>
                        <?php if(!empty($det->descripcion)): ?>
                            <br><small class="text-muted"><?=$det->descripcion?></small>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?=$det->codigo?></td>
                    <td class="text-center"><?=number_format($det->cantidad, 2)?></td>
                    <td class="text-end">$<?=number_format($det->precio_unitario, 2)?></td>
                    <td class="text-end">$<?=number_format($det->subtotal, 2)?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totales -->
        <div class="row justify-content-end mb-4">
            <div class="col-md-5">
                <div class="border p-3" style="border:2px solid #1a237e !important;">
                    <div class="d-flex justify-content-between">
                        <span>Subtotal</span>
                        <span>$<?=number_format($orden->subtotal, 2)?></span>
                    </div>
                    <?php if($orden->descuento_aplicado > 0): ?>
                    <div class="d-flex justify-content-between text-success">
                        <span>Descuento <?=$orden->descuento_nombre ? '('.$orden->descuento_nombre.')' : ''?></span>
                        <span>-$<?=number_format($orden->descuento_aplicado, 2)?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between">
                        <span>IVA</span>
                        <span>$<?=number_format($orden->iva, 2)?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold" style="color:#1a237e;">
                        <span>Total</span>
                        <span>$<?=number_format($orden->total, 2)?></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if($orden->observaciones): ?>
        <div class="mb-4 p-2 ps-3" style="border-left:4px solid #1a237e; background-color:#f8f9fa;">
            <p class="mb-0 small"><strong>Observaciones:</strong> <?=$orden->observaciones?></p>
        </div>
        <?php endif; ?>

        <!-- Firmas -->
        <div class="row mt-5">
            <div class="col-md-6">
                <p class="small text-muted mb-1">Entregado por:</p>
                <div style="border-bottom:1px solid #333; height:40px;"></div>
            </div>
            <div class="col-md-6">
                <p class="small text-muted mb-1">Recibido por:</p>
                <div style="border-bottom:1px solid #333; height:40px;"></div>
                <p class="small text-muted mt-1">Nombre y firma</p>
            </div>
        </div>

    </div>
</div>

<?php else: ?>
<!-- ====================================================
     TEMPLATE 3 — FORMATO MODERNO (VERDE)
     ==================================================== -->
<div class="container my-4">

    <!-- Logo centrado -->
    <div class="text-center mb-4">
        <img src="<?=$logoUrl?>" alt="<?=htmlspecialchars($nombreEmpresa)?>" class="recibo-logo-lg mb-2">
        <h5 class="fw-bold mt-2 mb-0" style="color:#1b5e20;"><?=htmlspecialchars($nombreEmpresa)?></h5>
        <p class="small text-muted mb-0">RFC: <?=htmlspecialchars($rfcEmpresa)?></p>
        <p class="small text-muted mb-0"><?=htmlspecialchars($emailEmpresa)?> &nbsp;|&nbsp; <?=htmlspecialchars($telefonoEmpresa)?></p>
    </div>

    <!-- Cliente / Venta en cards -->
    <div class="row mb-4">
        <div class="col-md-6 mb-2">
            <div class="card h-100" style="border-radius:12px; border-color:#1b5e20;">
                <div class="card-body">
                    <h6 class="fw-bold mb-2" style="color:#1b5e20;">Cliente</h6>
                    <p class="mb-0"><?=$orden->razon_social?></p>
                    <?php if(!empty($orden->rfc)): ?>
                        <p class="mb-0 small text-muted">RFC: <?=$orden->rfc?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-2">
            <div class="card h-100" style="border-radius:12px; border-color:#1b5e20;">
                <div class="card-body">
                    <h6 class="fw-bold mb-2" style="color:#1b5e20;">Venta</h6>
                    <p class="mb-0 small"><strong>Folio:</strong> <?=$orden->folio?></p>
                    <p class="mb-0 small"><strong>Fecha:</strong> <?=date('d/m/Y H:i', strtotime($orden->fecha_creacion))?></p>
                    <p class="mb-0 small"><strong>Pago:</strong> <?=$orden->forma_pago?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla productos con card y bordes redondeados -->
    <div class="card mb-4" style="border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead style="background-color:#f5f5f5;">
                    <tr>
                        <th class="ps-3" style="color:#1b5e20;">Producto</th>
                        <th style="color:#1b5e20;">Código</th>
                        <th class="text-center" style="color:#1b5e20;">Cant.</th>
                        <th class="text-end" style="color:#1b5e20;">P. Unit.</th>
                        <th class="text-end pe-3" style="color:#1b5e20;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orden->detalles as $det): ?>
                    <tr>
                        <td class="ps-3"><?=$det->nombre?></td>
                        <td class="small text-muted"><?=$det->codigo?></td>
                        <td class="text-center"><?=number_format($det->cantidad, 2)?></td>
                        <td class="text-end">$<?=number_format($det->precio_unitario, 2)?></td>
                        <td class="text-end pe-3">$<?=number_format($det->subtotal, 2)?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Totales en card verde -->
    <div class="row justify-content-end mb-4">
        <div class="col-md-5">
            <div class="card text-white" style="background-color:#1b5e20; border-radius:12px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>Subtotal</span>
                        <span>$<?=number_format($orden->subtotal, 2)?></span>
                    </div>
                    <?php if($orden->descuento_aplicado > 0): ?>
                    <div class="d-flex justify-content-between">
                        <span>Descuento <?=$orden->descuento_nombre ? '('.$orden->descuento_nombre.')' : ''?></span>
                        <span>-$<?=number_format($orden->descuento_aplicado, 2)?></span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between">
                        <span>IVA</span>
                        <span>$<?=number_format($orden->iva, 2)?></span>
                    </div>
                    <hr class="border-white opacity-50">
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span>$<?=number_format($orden->total, 2)?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($orden->observaciones): ?>
    <div class="alert alert-light mb-4" style="border-left:4px solid #1b5e20;">
        <p class="mb-0 small"><strong>Observaciones:</strong> <?=$orden->observaciones?></p>
    </div>
    <?php endif; ?>

    <!-- QR placeholder + pie de página -->
    <div class="d-flex align-items-center gap-3 mt-4">
        <div class="d-flex align-items-center justify-content-center flex-shrink-0"
             style="width:80px; height:80px; background-color:#e0e0e0; border-radius:8px;">
            <span class="small text-muted text-center lh-sm">Escanear<br>para<br>verificar</span>
        </div>
        <div class="small text-muted">
            <p class="mb-0">Folio: <strong><?=$orden->folio?></strong></p>
            <p class="mb-0">Fecha: <?=date('d/m/Y H:i', strtotime($orden->fecha_creacion))?></p>
            <p class="mb-0 mt-1 text-muted" style="font-size:0.75rem;"><?=htmlspecialchars($nombreEmpresa)?> — Todos los derechos reservados</p>
        </div>
    </div>

</div>
<?php endif; ?>

</body>
</html>
