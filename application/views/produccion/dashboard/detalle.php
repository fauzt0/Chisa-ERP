<?php
$registro = $response['registro'] ?? null;
$tipo = $response['tipo'] ?? 'orden_venta';
$es_obra = ($tipo === 'obra');

if(!$registro) {
    echo '<div class="alert alert-danger">Registro no encontrado</div>';
    return;
}

// Determinar color del badge según estatus
$badgeColor = 'secondary';
switch($registro->estatus) {
    case 'Confirmada':
    case 'Aprobada':
        $badgeColor = 'warning';
        break;
    case 'En Proceso':
    case 'En Ejecución':
        $badgeColor = 'primary';
        break;
    case 'Completada':
        $badgeColor = 'success';
        break;
    case 'Entregada':
        $badgeColor = 'info';
        break;
}

$titulo = $es_obra ? 'Obra' : 'Orden de Venta';
$icono = $es_obra ? 'hard-hat' : 'file-invoice';
?>

<!-- Breadcrumb -->
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?=base_url();?>produccion/Dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Detalle de <?=$titulo?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Encabezado -->
<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="display-4">
            <i class="fas fa-<?=$icono?>"></i> <?=$registro->folio?>
            <span class="badge bg-<?=$es_obra ? 'warning' : 'primary'?> ms-2" style="font-size: 0.5em;">
                <?=$es_obra ? 'OBRA' : 'VENTA'?>
            </span>
        </h1>
        <h3 class="text-muted"><?=$registro->cliente ?: 'Sin cliente'?></h3>
    </div>
    <div class="col-md-4 text-end">
        <h2><span class="badge bg-<?=$badgeColor?>" style="font-size: 1.5rem; padding: 15px 25px;"><?=$registro->estatus?></span></h2>
        <p class="h4 text-muted mt-3">
            <i class="fas fa-calendar"></i> <?=date('d/m/Y', strtotime($registro->fecha_creacion))?>
        </p>
    </div>
</div>

<!-- Información General -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><i class="fas fa-info-circle"></i> Información General</h3>
            </div>
            <div class="card-body" style="font-size: 1.2rem;">
                <p class="mb-2"><strong>Folio:</strong> <?=$registro->folio?></p>
                <?php if(!$es_obra): ?>
                <p class="mb-2"><strong>Tipo de Venta:</strong> <?=$registro->tipo_venta?></p>
                <?php else: ?>
                <p class="mb-2"><strong>Nombre:</strong> <?=$registro->nombre?></p>
                <?php if($registro->area_total): ?>
                <p class="mb-2"><strong>Área Total:</strong> <?=number_format($registro->area_total, 2)?> m²</p>
                <?php endif; ?>
                <?php endif; ?>
                <p class="mb-2"><strong>Estatus:</strong> 
                    <span class="badge bg-<?=$badgeColor?>"><?=$registro->estatus?></span>
                </p>
                <p class="mb-2"><strong>Fecha de Creación:</strong> <?=date('d/m/Y H:i', strtotime($registro->fecha_creacion))?></p>
                <?php if(isset($registro->fecha_entrega_estimada) && $registro->fecha_entrega_estimada): ?>
                <p class="mb-0"><strong>Fecha Estimada de Entrega:</strong> <?=date('d/m/Y', strtotime($registro->fecha_entrega_estimada))?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="mb-0"><i class="fas fa-<?=$es_obra ? 'map-marker-alt' : 'user'?>"></i> <?=$es_obra ? 'Ubicación' : 'Datos del Cliente'?></h3>
            </div>
            <div class="card-body" style="font-size: 1.2rem;">
                <p class="mb-2"><strong>Cliente:</strong> <?=$registro->cliente?></p>
                <?php if(isset($registro->nombre_comercial) && $registro->nombre_comercial): ?>
                <p class="mb-2"><strong>Nombre Comercial:</strong> <?=$registro->nombre_comercial?></p>
                <?php endif; ?>
                <?php if($es_obra): ?>
                    <?php if($registro->direccion): ?>
                    <p class="mb-2"><strong>Dirección:</strong> <?=$registro->direccion?></p>
                    <?php endif; ?>
                    <?php if($registro->ciudad): ?>
                    <p class="mb-2"><strong>Ciudad:</strong> <?=$registro->ciudad?>, <?=$registro->estado?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <?php if(isset($registro->telefono) && $registro->telefono): ?>
                    <p class="mb-2"><strong>Teléfono:</strong> <?=$registro->telefono?></p>
                    <?php endif; ?>
                    <?php if(isset($registro->email) && $registro->email): ?>
                    <p class="mb-2"><strong>Email:</strong> <?=$registro->email?></p>
                    <?php endif; ?>
                    <?php if(isset($registro->direccion_envio) && $registro->direccion_envio): ?>
                    <hr>
                    <p class="mb-0"><strong>Dirección de Envío:</strong><br><?=$registro->direccion_envio?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Actualizar Estatus -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-white">
                <h3 class="mb-0"><i class="fas fa-edit"></i> Actualizar Estatus</h3>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label class="form-label h5">Seleccionar Nuevo Estatus:</label>
                        <select id="nuevo_estatus" class="form-select form-select-lg">
                            <option value="">-- Seleccionar --</option>
                            <option value="Confirmada" <?=$registro->estatus == 'Confirmada' ? 'selected' : ''?>>Confirmada</option>
                            <option value="En Proceso" <?=$registro->estatus == 'En Proceso' ? 'selected' : ''?>>En Proceso</option>
                            <option value="Completada" <?=$registro->estatus == 'Completada' ? 'selected' : ''?>>Completada</option>
                            <option value="Entregada" <?=$registro->estatus == 'Entregada' ? 'selected' : ''?>>Entregada</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-success btn-lg" onclick="actualizarEstatus()" style="font-size: 1.3rem; padding: 15px 40px;">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
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
                                <th class="text-center" width="120">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $contador = 1;
                            foreach($registro->productos as $producto): 
                                $cantidad = $producto->cantidad ?? ($producto->cantidad_ajustada ?? $producto->cantidad_calculada ?? 0);
                                
                                // Imagen por defecto si no existe
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
                                <td style="font-size: 0.95rem;">
                                    <strong><?=$producto->producto_nombre?></strong><br>
                                    <small class="text-muted">
                                        <i class="fas fa-barcode"></i> <?=$producto->producto_codigo?>
                                    </small>
                                </td>
                                <td style="font-size: 0.9rem;">
                                    <?php if($producto->formulacion_id): ?>
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
                                <td class="text-center" style="font-size: 0.95rem;">
                                    <strong><?=number_format($cantidad, 2)?></strong>
                                    <br><small class="text-muted"><?=$producto->unidad_venta ?? $producto->unidad ?? '-'?></small>
                                </td>
                                <td class="text-center">
                                    <?php if($producto->formulacion_id): ?>
                                        <button class="btn btn-sm btn-primary" onclick="verFormulacion(<?=$producto->formulacion_id?>, '<?=addslashes($producto->producto_nombre)?>')">
                                            <i class="fas fa-flask"></i> Fórmula
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botones de Acción -->
<div class="row mt-4">
    <div class="col-12">
        <a href="<?=base_url()?>produccion/Dashboard" class="btn btn-secondary btn-lg">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
</div>

<!-- Modal: Ver Formulación -->
<div class="modal fade" id="modalFormulacion" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-flask"></i> Formulación: <span id="lbl_producto_nombre"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Versión:</strong> <span id="lbl_version"></span></p>
                        <p><strong>Descripción:</strong> <span id="lbl_descripcion"></span></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p><strong>Costo Total:</strong> <span id="lbl_costo_total" class="text-success h4"></span></p>
                    </div>
                </div>
                
                <h5>Componentes de la Formulación</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Componente</th>
                                <th>Tipo</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-end">Costo Unitario</th>
                                <th class="text-end">Costo Total</th>
                            </tr>
                        </thead>
                        <tbody id="tabla_componentes">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
function actualizarEstatus() {
    const nuevoEstatus = document.getElementById('nuevo_estatus').value;
    
    if(!nuevoEstatus) {
        alert('Por favor seleccione un estatus');
        return;
    }
    
    if(nuevoEstatus === '<?=$registro->estatus?>') {
        alert('El estatus seleccionado es el mismo que el actual');
        return;
    }
    
    if(!confirm('¿Está seguro de cambiar el estatus a "' + nuevoEstatus + '"?')) {
        return;
    }
    
    // Mostrar loading
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    $.ajax({
        url: '<?=base_url()?>produccion/Dashboard/actualizar_estatus_ajax',
        method: 'POST',
        data: {
            orden_id: <?=$registro->id?>,
            estatus: nuevoEstatus
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                alert(response.message);
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

function verImagenProducto(imagenUrl, nombreProducto) {
    $('#lbl_imagen_producto_nombre').text(nombreProducto);
    $('#img_producto_zoom').attr('src', imagenUrl);
    $('#modalImagenProducto').modal('show');
}

function verFormulacion(formulacion_id, producto_nombre) {
    // Mostrar modal
    $('#modalFormulacion').modal('show');
    $('#lbl_producto_nombre').text(producto_nombre);
    
    // Cargar datos de la formulación
    $.ajax({
        url: '<?=base_url()?>produccion/Dashboard/get_formulacion_detalle_ajax',
        method: 'POST',
        data: {
            formulacion_id: formulacion_id
        },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.formulacion) {
                const f = response.formulacion;
                
                // Llenar información general
                $('#lbl_version').text('V' + f.version + (f.nombre_version ? ' - ' + f.nombre_version : ''));
                $('#lbl_descripcion').text(f.descripcion || 'Sin descripción');
                $('#lbl_costo_total').text('$' + parseFloat(f.costo_total).toLocaleString('es-MX', {minimumFractionDigits: 2}));
                
                // Llenar tabla de componentes
                let html = '';
                if(f.componentes && f.componentes.length > 0) {
                    f.componentes.forEach(c => {
                        const nombre = c.tipo_componente === 'Insumo' ? c.insumo_nombre : c.producto_nombre;
                        const codigo = c.tipo_componente === 'Insumo' ? c.insumo_codigo : c.producto_codigo;
                        const badgeClass = c.tipo_componente === 'Insumo' ? 'bg-secondary' : 'bg-primary';
                        
                        html += `
                            <tr>
                                <td>
                                    <strong>${nombre}</strong><br>
                                    <small class="text-muted">${codigo}</small>
                                </td>
                                <td><span class="badge ${badgeClass}">${c.tipo_componente}</span></td>
                                <td class="text-center">${parseFloat(c.cantidad).toFixed(2)}</td>
                                <td class="text-center">${c.unidad}</td>
                                <td class="text-end">$${parseFloat(c.costo_unitario || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                                <td class="text-end text-success"><strong>$${parseFloat(c.costo_total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</strong></td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="6" class="text-center text-muted">Sin componentes definidos</td></tr>';
                }
                
                $('#tabla_componentes').html(html);
            } else {
                alert('Error: ' + (response.message || 'No se pudo cargar la formulación'));
                $('#modalFormulacion').modal('hide');
            }
        },
        error: function() {
            alert('Error al comunicarse con el servidor');
            $('#modalFormulacion').modal('hide');
        }
    });
}
</script>
