<?php
$obra = $response['obra'] ?? null;
if(!$obra) {
    echo '<div class="alert alert-danger">Obra no encontrada</div>';
    return;
}
// Determinar color del badge según estatus
$badgeColor = 'secondary';
switch($obra->estatus) {
    case 'En Cotización':
        $badgeColor = 'warning';
        break;
    case 'Aprobada':
        $badgeColor = 'success';
        break;
    case 'En Ejecución':
        $badgeColor = 'primary';
        break;
    case 'Completada':
        $badgeColor = 'info';
        break;
    case 'Pausada':
        $badgeColor = 'danger';
        break;
}
?>
<!-- Breadcrumb -->
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?=base_url();?>ventas/ObrasVentas">Obras</a></li>
                <li class="breadcrumb-item active">Detalle</li>
            </ol>
        </nav>
    </div>
</div>
<!-- Encabezado -->
<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="display-4">
            <i class="fas fa-hard-hat"></i> <?=$obra->folio?>
            <span class="badge bg-warning ms-2" style="font-size: 0.5em;">OBRA</span>
        </h1>
        <h3 class="text-muted"><?=$obra->cliente?></h3>
        <p class="lead"><?=$obra->nombre?></p>
    </div>
    <div class="col-md-4 text-end">
        <h2><span class="badge bg-<?=$badgeColor?>" style="font-size: 1.5rem; padding: 15px 25px;"><?=$obra->estatus?></span></h2>
        <p class="h4 text-muted mt-3">
            <i class="fas fa-calendar"></i> <?=date('d/m/Y', strtotime($obra->fecha_creacion))?>
        </p>
    </div>
</div>
<!-- Información General -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información General</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Folio:</strong> <?=$obra->folio?></p>
                <p class="mb-2"><strong>Nombre:</strong> <?=$obra->nombre?></p>
                <?php if($obra->area_total): ?>
                <p class="mb-2"><strong>Área Total:</strong> <?=number_format($obra->area_total, 2)?> m²</p>
                <?php endif; ?>
                <p class="mb-2"><strong>Estatus:</strong> 
                    <span class="badge bg-<?=$badgeColor?>"><?=$obra->estatus?></span>
                </p>
                <p class="mb-2"><strong>Fecha de Creación:</strong> <?=date('d/m/Y H:i', strtotime($obra->fecha_creacion))?></p>
                <hr>
                <p class="mb-2"><strong>Subtotal:</strong> $<?=number_format($obra->subtotal ?? 0, 2)?></p>
                <p class="mb-2"><strong>IVA:</strong> $<?=number_format($obra->iva_monto ?? 0, 2)?></p>
                <p class="mb-0"><strong>Total:</strong> <span class="text-success h4">$<?=number_format($obra->total, 2)?></span></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-user"></i> Datos del Cliente</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Cliente:</strong> <?=$obra->cliente?></p>
                <?php if($obra->nombre_comercial): ?>
                <p class="mb-2"><strong>Nombre Comercial:</strong> <?=$obra->nombre_comercial?></p>
                <?php endif; ?>
                <?php if($obra->rfc): ?>
                <p class="mb-2"><strong>RFC:</strong> <?=$obra->rfc?></p>
                <?php endif; ?>
                <?php if($obra->telefono): ?>
                <p class="mb-2"><strong>Teléfono:</strong> <?=$obra->telefono?></p>
                <?php endif; ?>
                <?php if($obra->email): ?>
                <p class="mb-2"><strong>Email:</strong> <?=$obra->email?></p>
                <?php endif; ?>
                <?php if($obra->direccion): ?>
                <hr>
                <p class="mb-0"><strong>Dirección de la Obra:</strong><br><?=$obra->direccion?></p>
                <?php if($obra->ciudad): ?>
                <p class="mb-0 mt-2"><?=$obra->ciudad?>, <?=$obra->estado?> <?=$obra->codigo_postal?></p>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- Productos -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-boxes"></i> Productos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="80">Imagen</th>
                                <th>Producto</th>
                                <th>Formulación</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($obra->productos as $producto): 
                                $cantidad = $producto->cantidad_ajustada ?? $producto->cantidad_calculada;
                                $precio = $producto->precio_unitario ?? 0;
                                $subtotal = $cantidad * $precio;
                                $imagen = $producto->foto_producto ?? 'assets/img/no-image.png';
                            ?>
                            <tr>
                                <td>
                                    <img src="<?=base_url($imagen)?>" 
                                         alt="<?=$producto->producto_nombre?>" 
                                         class="img-thumbnail"
                                         style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                         onclick="verImagenProducto('<?=base_url($imagen)?>', '<?=addslashes($producto->producto_nombre)?>')"
                                         onerror="this.src='<?=base_url('assets/img/no-image.png')?>'">
                                </td>
                                <td>
                                    <strong><?=$producto->producto_nombre?></strong><br>
                                    <small class="text-muted">
                                        <i class="fas fa-barcode"></i> <?=$producto->producto_codigo?>
                                    </small>
                                </td>
                                <td>
                                    <?php if($producto->formulacion_version): ?>
                                        <span class="badge bg-info">
                                            <i class="fas fa-flask"></i> V<?=$producto->formulacion_version?>
                                        </span>
                                        <?php if($producto->formulacion_nombre): ?>
                                            <br><small class="text-muted"><?=$producto->formulacion_nombre?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin formulación</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <strong><?=number_format($cantidad, 2)?></strong>
                                    <br><small class="text-muted"><?=$producto->unidad ?? 'unidad'?></small>
                                </td>
                                <td class="text-end">$<?=number_format($precio, 2)?></td>
                                <td class="text-end"><strong class="text-success">$<?=number_format($subtotal, 2)?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong class="text-success h5">$<?=number_format($obra->total, 2)?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Pagos y Recibos -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Pagos y Recibos</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($obra->pagos)): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Método</th>
                                <th class="text-end">Monto</th>
                                <th>Referencia</th>
                                <th>Notas</th>
                                <th class="text-center">Recibo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_pagado = 0;
                            foreach($obra->pagos as $pago): 
                                $total_pagado += $pago->monto;
                            ?>
                            <tr>
                                <td><?=date('d/m/Y', strtotime($pago->fecha_pago))?></td>
                                <td><?=$pago->metodo_pago?></td>
                                <td class="text-end"><strong>$<?=number_format($pago->monto, 2)?></strong></td>
                                <td><?=$pago->referencia ?? '-'?></td>
                                <td><?=$pago->notas ?? '-'?></td>
                                <td class="text-center">
                                    <button onclick="verRecibo(<?=$pago->id?>)" class="btn btn-sm btn-info" title="Ver Recibo">
                                        <i class="fas fa-receipt"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="2" class="text-end"><strong>Total Pagado:</strong></td>
                                <td class="text-end"><strong class="text-success">$<?=number_format($total_pagado, 2)?></strong></td>
                                <td colspan="3"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-end"><strong>Saldo Pendiente:</strong></td>
                                <td class="text-end"><strong class="text-danger">$<?=number_format($obra->total - $total_pagado, 2)?></strong></td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No se han registrado pagos para esta obra.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Facturación -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-file-invoice"></i> Facturación</h5>
            </div>
            <div class="card-body">
                <?php
                // Verificar si tiene factura
                $this->db->where('obra_id', $obra->id);
                $factura = $this->db->get('facturas_obras')->row();
                ?>
                <?php if($factura): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Factura generada: <strong><?=$factura->folio?></strong>
                        <br>Fecha de emisión: <?=date('d/m/Y H:i', strtotime($factura->fecha_emision))?>
                    </div>
                    <a href="<?=base_url()?>ventas/ObrasVentas/imprimir_factura/<?=$obra->id?>" 
                       target="_blank" class="btn btn-primary btn-lg">
                        <i class="fas fa-file-pdf"></i> Ver Factura
                    </a>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No se ha generado factura para esta obra.
                    </div>
                    <button onclick="abrirModalFacturacion()" class="btn btn-success btn-lg" id="btnAbrirModalFactura">
                        <i class="fas fa-plus"></i> Generar Factura
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Botones de Acción -->
<div class="row mt-4">
    <div class="col-12">
        <a href="<?=base_url()?>ventas/ObrasVentas" class="btn btn-secondary btn-lg">
            <i class="fas fa-arrow-left"></i> Volver a Obras
        </a>
    </div>
</div>
<!-- Modal: Datos de Facturación -->
<div class="modal fade" id="modalDatosFacturacion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice"></i> Datos de Facturación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Revise y edite los datos fiscales que se usarán para generar la factura. 
                    <strong>Estos cambios solo afectarán esta factura</strong>, no modificarán los datos del cliente.
                </div>

                <form id="formDatosFacturacion">
                    <input type="hidden" name="obra_id" value="<?=$obra->id?>">
                    
                    <h6 class="mb-3"><i class="fas fa-building"></i> Datos del Receptor (Cliente)</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">RFC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="rfc_receptor" 
                                   value="<?=$obra->rfc ?? 'XAXX010101000'?>" 
                                   required maxlength="13" pattern="[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}">
                            <small class="text-muted">Formato: XAXX010101000</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="razon_social_receptor" 
                                   value="<?=$obra->cliente?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección Fiscal</label>
                        <textarea class="form-control" name="direccion_receptor" rows="2"><?=$obra->direccion ?? ''?></textarea>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3"><i class="fas fa-store"></i> Datos del Emisor (Su Empresa)</h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">RFC Emisor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="rfc_emisor" 
                                   value="XAXX010101000" required maxlength="13">
                            <small class="text-muted">Actualice con su RFC real</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Razón Social Emisor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="razon_social_emisor" 
                                   value="Mi Empresa S.A. de C.V." required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dirección Emisor</label>
                        <textarea class="form-control" name="direccion_emisor" rows="2">Calle Principal #123, Col. Centro</textarea>
                        <small class="text-muted">Actualice con su dirección fiscal</small>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="form-label">Notas Adicionales (Opcional)</label>
                        <textarea class="form-control" name="notas" rows="2" placeholder="Condiciones de pago, términos, etc."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" onclick="confirmarGenerarFactura()">
                    <i class="fas fa-check"></i> Generar Factura
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ver Recibo de Pago -->
<div class="modal fade" id="modalRecibo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-receipt"></i> Recibo de Pago
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoRecibo">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Cargando recibo...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ver Imagen del Producto -->
<div class="modal fade" id="modalImagenProducto" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">
                    <i class="fas fa-image"></i> <span id="lbl_imagen_producto_nombre"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="img_producto_zoom" src="" alt="" class="img-fluid" style="max-height: 70vh; width: auto;">
            </div>
        </div>
    </div>
</div>
<script>
function verImagenProducto(imagenUrl, nombreProducto) {
    $('#lbl_imagen_producto_nombre').text(nombreProducto);
    $('#img_producto_zoom').attr('src', imagenUrl);
    $('#modalImagenProducto').modal('show');
}

function abrirModalFacturacion() {
    $('#modalDatosFacturacion').modal('show');
}

function confirmarGenerarFactura() {
    const form = document.getElementById('formDatosFacturacion');
    
    // Validar formulario
    if(!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Obtener datos del formulario
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // Confirmar
    if(!confirm('¿Está seguro de generar la factura con estos datos?')) {
        return;
    }
    
    // Deshabilitar botón
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
    
    // Enviar via AJAX
    $.ajax({
        url: '<?=base_url()?>ventas/ObrasVentas/generar_factura_ajax',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                alert('Factura generada correctamente: ' + response.folio);
                location.reload();
            } else {
                alert('Error: ' + response.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        },
        error: function() {
            alert('Error al comunicarse con el servidor');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
}

function verRecibo(pago_id) {
    // Mostrar modal con loading
    $('#modalRecibo').modal('show');
    $('#contenidoRecibo').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Cargando recibo...</p></div>');
    
    // Cargar recibo via AJAX
    $.ajax({
        url: '<?=base_url()?>ventas/ObrasVentas/get_recibo_ajax',
        method: 'POST',
        data: {
            pago_id: pago_id
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#contenidoRecibo').html(response.html);
            } else {
                $('#contenidoRecibo').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            $('#contenidoRecibo').html('<div class="alert alert-danger">Error al cargar el recibo</div>');
        }
    });
}
</script>