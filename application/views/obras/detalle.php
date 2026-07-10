<?php
$obra = $response['obra'] ?? null;
if(!$obra) {
    echo '<div class="alert alert-danger">Obra no encontrada</div>';
    return;
}

// Determinar color del badge según estatus
$badgeColors = [
    'Planificación' => 'warning',
    'En Cotización' => 'info',
    'Aprobada' => 'success',
    'En Ejecución' => 'primary',
    'Pausada' => 'secondary',
    'Completada' => 'success',
    'Cancelada' => 'danger'
];
$badgeColor = $badgeColors[$obra->estatus] ?? 'secondary';
?>

<!-- Breadcrumb -->
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?=base_url();?>obras/Obras">Obras</a></li>
                <li class="breadcrumb-item active">Detalle</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Encabezado de la Obra -->
<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="fas fa-hard-hat"></i> <?=$obra->folio?></h1>
        <h3 class="text-muted"><?=$obra->nombre?></h3>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-warning btn-lg mb-2" data-bs-toggle="modal" data-bs-target="#modalEditarObra">
            <i class="fas fa-edit"></i> Editar Obra
        </button>
        <h2><span class="badge bg-<?=$badgeColor?>" style="font-size: 1.5rem; padding: 15px 25px;"><?=$obra->estatus?></span></h2>
        <div class="progress mt-3" style="height: 30px;">
            <div class="progress-bar bg-<?=$badgeColor?>" role="progressbar" style="width: <?=$obra->porcentaje_avance?>%">
                <?=$obra->porcentaje_avance?>% Avance
            </div>
        </div>
    </div>
</div>

<?php
$this->load->view('obras/partials/vinculo_venta', [
    'obra' => $obra,
    'baseUrl' => base_url('obras/Obras')
]);
?>

<!-- Información General -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información General</h5>
            </div>
            <div class="card-body">
                <p><strong>Cliente:</strong> <?=$obra->cliente ?: 'Sin cliente'?></p>
                <p><strong>Descripción:</strong> <?=$obra->descripcion ?: '-'?></p>
                <p><strong>Área Total:</strong> <?=$obra->area_total ? number_format($obra->area_total, 2) . ' m²' : '-'?></p>
                <p><strong>Tipo de Superficie:</strong> <?=$obra->tipo_superficie ?: '-'?></p>
                <p><strong>Fecha Creación:</strong> <?=date('d/m/Y H:i', strtotime($obra->fecha_creacion))?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Ubicación</h5>
            </div>
            <div class="card-body">
                <p><strong>Dirección:</strong> <?=$obra->direccion?></p>
                <p><strong>Ciudad:</strong> <?=$obra->ciudad ?: '-'?></p>
                <p><strong>Estado:</strong> <?=$obra->estado ?: '-'?></p>
                <p><strong>Código Postal:</strong> <?=$obra->codigo_postal ?: '-'?></p>
                <?php if($obra->coordenadas_gps): ?>
                <p><strong>Coordenadas GPS:</strong> <?=$obra->coordenadas_gps?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Información Financiera -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Información Financiera</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <p><strong>Subtotal:</strong></p>
                        <h4 class="text-success">$<?=number_format($obra->subtotal ?? 0, 2)?></h4>
                    </div>
                    <div class="col-6">
                        <p><strong>Descuento (<?=$obra->descuento_porcentaje ?? 0?>%):</strong></p>
                        <h5 class="text-danger">-$<?=number_format($obra->descuento_monto ?? 0, 2)?></h5>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-6">
                        <p><strong>IVA (<?=$obra->iva_porcentaje ?? 16?>%):</strong></p>
                        <h5>$<?=number_format($obra->iva_monto ?? 0, 2)?></h5>
                    </div>
                    <div class="col-6">
                        <p><strong>Total:</strong></p>
                        <h3 class="text-success">$<?=number_format($obra->total ?? 0, 2)?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Costos y Rentabilidad</h5>
            </div>
            <div class="card-body">
                <p><strong>Costo Estimado:</strong> $<?=number_format($obra->costo_estimado ?? 0, 2)?></p>
                <p><strong>Costo Real:</strong> $<?=number_format($obra->costo_real ?? 0, 2)?></p>
                <hr>
                <p><strong>Utilidad Neta:</strong> <span class="text-success">$<?=number_format($obra->utilidad_neta ?? 0, 2)?></span></p>
                <p><strong>Margen de Utilidad:</strong> <span class="badge bg-<?=($obra->margen_utilidad ?? 0) > 20 ? 'success' : 'warning'?>" style="font-size: 1.2rem;"><?=number_format($obra->margen_utilidad ?? 0, 2)?>%</span></p>
                <hr>
                <p><strong>Anticipo (<?=$obra->anticipo_porcentaje ?? 0?>%):</strong> $<?=number_format($obra->anticipo_monto ?? 0, 2)?></p>
                <p><strong>Condiciones de Pago:</strong> <?=$obra->condiciones_pago ?: 'No especificadas'?></p>
                <p><strong>Tiempo de Entrega:</strong> <?=$obra->tiempo_entrega ?: 'No especificado'?></p>
            </div>
        </div>
    </div>
</div>

<!-- Pestañas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tabProductos">
                            <i class="fas fa-boxes"></i> Productos/Materiales
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tabArchivos">
                            <i class="fas fa-folder-open"></i> Archivos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tabComentarios">
                            <i class="fas fa-comments"></i> Comentarios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tabDatos">
                            <i class="fas fa-cog"></i> Datos Técnicos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tabPagos">
                            <i class="fas fa-money-bill-wave"></i> Pagos
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Tab Productos -->
                    <div class="tab-pane fade show active" id="tabProductos">
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Productos y Materiales</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarProducto">
                                <i class="fas fa-plus"></i> Agregar Producto
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Sección</th>
                                        <th>Área (m²)</th>
                                        <th>Cantidad Calculada</th>
                                        <th>Cantidad Ajustada</th>
                                        <th>Unidad</th>
                                        <th>Precio Unit.</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($obra->productos)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No hay productos agregados</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach($obra->productos as $producto): ?>
                                        <tr>
                                            <td>
                                                <strong><?=$producto->producto_nombre?></strong><br>
                                                <small class="text-muted"><?=$producto->producto_codigo?></small>
                                                <?php if($producto->formulacion_id): ?>
                                                    <br><span class="badge bg-info">
                                                        <i class="fas fa-flask"></i> V<?=$producto->formulacion_version?>
                                                        <?php if($producto->formulacion_nombre): ?>
                                                            - <?=$producto->formulacion_nombre?>
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?=$producto->seccion_obra ?: '-'?></td>
                                            <td><?=$producto->area_aplicacion ? number_format($producto->area_aplicacion, 2) : '-'?></td>
                                            <td><?=number_format($producto->cantidad_calculada, 2)?></td>
                                            <td><?=$producto->cantidad_ajustada ? number_format($producto->cantidad_ajustada, 2) : '-'?></td>
                                            <td><?=$producto->unidad?></td>
                                            <td>$<?=number_format($producto->precio_unitario ?? 0, 2)?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="verFormulacionObra(<?=$producto->producto_id?>, '<?=addslashes($producto->producto_nombre)?>', <?=$producto->id?>)" title="Ver Formulación">
                                                    <i class="fas fa-flask"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="eliminarProducto(<?=$producto->id?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Tab Archivos -->
                    <div class="tab-pane fade" id="tabArchivos">
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Archivos Adjuntos</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalSubirArchivo">
                                <i class="fas fa-upload"></i> Subir Archivo
                            </button>
                        </div>
                        
                        <div class="row">
                            <?php if(empty($obra->archivos)): ?>
                            <div class="col-12 text-center text-muted">
                                <p>No hay archivos adjuntos</p>
                            </div>
                            <?php else: ?>
                                <?php foreach($obra->archivos as $archivo): ?>
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <?php if(in_array($archivo->extension, ['.jpg', '.jpeg', '.png', '.gif'])): ?>
                                                <img src="<?=base_url().$archivo->ruta_archivo?>" class="img-fluid mb-2" alt="<?=$archivo->nombre_original?>">
                                            <?php else: ?>
                                                <i class="fas fa-file fa-4x mb-2 text-secondary"></i>
                                            <?php endif; ?>
                                            <h6><?=$archivo->nombre_original?></h6>
                                            <span class="badge bg-<?=$archivo->categoria == 'Foto' ? 'primary' : 'secondary'?>"><?=$archivo->categoria?></span>
                                            <p class="small text-muted"><?=number_format($archivo->tamano / 1024, 2)?> KB</p>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarArchivo(<?=$archivo->id?>)">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Tab Comentarios -->
                    <div class="tab-pane fade" id="tabComentarios">
                        <div class="mb-3">
                            <h5>Agregar Comentario</h5>
                            <form id="formComentario">
                                <input type="hidden" name="obra_id" value="<?=$obra->id?>">
                                <div class="row mb-2">
                                    <div class="col-md-3">
                                        <select class="form-select" name="tipo">
                                            <option value="General">General</option>
                                            <option value="Técnico">Técnico</option>
                                            <option value="Avance">Avance</option>
                                            <option value="Problema">Problema</option>
                                            <option value="Solución">Solución</option>
                                        </select>
                                    </div>
                                    <div class="col-md-9">
                                        <textarea class="form-control" name="comentario" rows="2" placeholder="Escribe tu comentario..." required></textarea>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="agregarComentario()">
                                    <i class="fas fa-comment"></i> Agregar Comentario
                                </button>
                            </form>
                        </div>
                        
                        <hr>
                        
                        <div id="listaComentarios">
                            <?php if(empty($obra->comentarios)): ?>
                            <p class="text-center text-muted">No hay comentarios</p>
                            <?php else: ?>
                                <?php foreach($obra->comentarios as $comentario): 
                                    $tipoBadges = [
                                        'General' => 'secondary',
                                        'Técnico' => 'primary',
                                        'Avance' => 'success',
                                        'Problema' => 'danger',
                                        'Solución' => 'info'
                                    ];
                                    $tipoBadge = $tipoBadges[$comentario->tipo] ?? 'secondary';
                                ?>
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong>Usuario</strong>
                                                <span class="badge bg-<?=$tipoBadge?> ms-2"><?=$comentario->tipo?></span>
                                            </div>
                                            <small class="text-muted"><?=date('d/m/Y H:i', strtotime($comentario->fecha_comentario))?></small>
                                        </div>
                                        <p class="mt-2 mb-0"><?=nl2br(htmlspecialchars($comentario->comentario))?></p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Tab Datos Técnicos -->
                    <div class="tab-pane fade" id="tabDatos">
                        <h5>Datos Técnicos y Especificaciones</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Condiciones Ambientales:</strong></p>
                                <p><?=$obra->condiciones_ambientales ?: 'No especificadas'?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Especificaciones Técnicas:</strong></p>
                                <p><?=$obra->especificaciones_tecnicas ?: 'No especificadas'?></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Fecha Inicio Estimada:</strong> <?=$obra->fecha_inicio_estimada ? date('d/m/Y', strtotime($obra->fecha_inicio_estimada)) : '-'?></p>
                                <p><strong>Fecha Fin Estimada:</strong> <?=$obra->fecha_fin_estimada ? date('d/m/Y', strtotime($obra->fecha_fin_estimada)) : '-'?></p>
                            </div>
                        </div>
                        <?php if($obra->notas_internas): ?>
                        <hr>
                        <p><strong>Notas Internas:</strong></p>
                        <p><?=nl2br(htmlspecialchars($obra->notas_internas))?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Tab Pagos -->
                    <div class="tab-pane fade" id="tabPagos">
                        <!-- Resumen de Pagos -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-receipt"></i> Resumen de Pagos</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <p><strong>Total de la Obra:</strong></p>
                                                <h4 class="text-primary">$<?=number_format($obra->total ?? 0, 2)?></h4>
                                            </div>
                                            <div class="col-6">
                                                <p><strong>Total Pagado:</strong></p>
                                                <h4 class="text-success">$<?=number_format($obra->total_pagado ?? 0, 2)?></h4>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-6">
                                                <p><strong>Saldo Pendiente:</strong></p>
                                                <h4 class="text-danger">$<?=number_format($obra->saldo_pendiente ?? 0, 2)?></h4>
                                            </div>
                                            <div class="col-6">
                                                <p><strong>Estatus de Pago:</strong></p>
                                                <?php
                                                $estatusBadges = [
                                                    'Pendiente' => 'danger',
                                                    'Anticipo Recibido' => 'warning',
                                                    'Parcialmente Pagado' => 'info',
                                                    'Pagado' => 'success'
                                                ];
                                                $estatusBadge = $estatusBadges[$obra->estatus_pago ?? 'Pendiente'] ?? 'secondary';
                                                ?>
                                                <h4><span class="badge bg-<?=$estatusBadge?>"><?=$obra->estatus_pago ?? 'Pendiente'?></span></h4>
                                            </div>
                                        </div>
                                        <div class="progress mt-3" style="height: 25px;">
                                            <?php
                                            $porcentajePagado = $obra->total > 0 ? ($obra->total_pagado / $obra->total) * 100 : 0;
                                            ?>
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?=$porcentajePagado?>%">
                                                <?=number_format($porcentajePagado, 1)?>% Pagado
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información Adicional</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Anticipo Esperado (<?=$obra->anticipo_porcentaje ?? 0?>%):</strong> $<?=number_format($obra->anticipo_monto ?? 0, 2)?></p>
                                        <p><strong>Condiciones de Pago:</strong> <?=$obra->condiciones_pago ?: 'No especificadas'?></p>
                                        <p><strong>Tiempo de Entrega:</strong> <?=$obra->tiempo_entrega ?: 'No especificado'?></p>
                                        <hr>
                                        <button class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#modalRegistrarPago">
                                            <i class="fas fa-plus-circle"></i> Registrar Pago
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Historial de Pagos -->
                        <h5 class="mb-3"><i class="fas fa-history"></i> Historial de Pagos</h5>
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaPagos">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Folio Recibo</th>
                                        <th>Fecha Pago</th>
                                        <th>Monto</th>
                                        <th>Método</th>
                                        <th>Concepto</th>
                                        <th>Referencia</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se llenará dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botón Volver -->
<div class="row mt-4">
    <div class="col-12">
        <a href="<?=base_url()?>obras/Obras" class="btn btn-secondary btn-lg">
            <i class="fas fa-arrow-left"></i> Volver a Obras
        </a>
    </div>
</div>

<!-- Modal Agregar Producto -->
<div class="modal fade" id="modalAgregarProducto" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Agregar Producto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Explicación -->
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle"></i> ¿Cómo calcular las cantidades?</h6>
                    <p class="mb-1"><strong>Cantidad Calculada:</strong> Resultado del cálculo teórico = (Área ÷ Rendimiento) × Factor Desperdicio</p>
                    <p class="mb-0"><strong>Cantidad Ajustada:</strong> (Opcional) Cantidad real a usar considerando presentaciones comerciales o condiciones especiales</p>
                </div>
                
                <form id="formAgregarProducto">
                    <input type="hidden" name="obra_id" value="<?=$obra->id?>">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Producto <span class="text-danger">*</span></label>
                            <select class="form-select" name="producto_id" required>
                                <option value="">-- Seleccionar Producto --</option>
                                <!-- Se llenará dinámicamente -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sección de la Obra</label>
                            <input type="text" class="form-control" name="seccion_obra" placeholder="ej: Sala, Recámara 1, etc.">
                        </div>
                    </div>
                    
                    <h6 class="mb-3"><i class="fas fa-calculator"></i> Cálculo Automático</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Área de Aplicación (m²)</label>
                            <input type="number" step="0.01" class="form-control" name="area_aplicacion" id="areaAplicacion">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Rendimiento Teórico (m²/unidad)</label>
                            <input type="number" step="0.01" class="form-control" name="rendimiento_teorico" id="rendimientoTeorico" placeholder="ej: 10">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Factor Desperdicio</label>
                            <input type="number" step="0.01" class="form-control" name="factor_desperdicio" id="factorDesperdicio" value="1.10">
                            <small class="text-muted">Default: 1.10 (10% extra)</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Cantidad Calculada <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control bg-light" name="cantidad_calculada" id="cantidadCalculada" readonly required>
                            <small class="text-muted">Se calcula automáticamente</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cantidad Ajustada</label>
                            <input type="number" step="0.01" class="form-control" name="cantidad_ajustada" placeholder="Opcional">
                            <small class="text-muted">Cantidad real a usar</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Unidad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="unidad" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Precio Unitario</label>
                            <input type="number" step="0.01" class="form-control" name="precio_unitario">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" name="notas" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarProducto()">
                    <i class="fas fa-save"></i> Agregar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Subir Archivo -->
<div class="modal fade" id="modalSubirArchivo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-upload"></i> Subir Archivo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formSubirArchivo" enctype="multipart/form-data">
                    <input type="hidden" name="obra_id" value="<?=$obra->id?>">
                    <div class="mb-3">
                        <label class="form-label">Archivo <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="archivo" required>
                        <small class="text-muted">Máximo 10MB. Formatos: JPG, PNG, PDF, DOC, XLS, DWG, DXF</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" name="categoria">
                            <option value="Foto">Foto</option>
                            <option value="Plano">Plano</option>
                            <option value="CAD">CAD</option>
                            <option value="Documento">Documento</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="subirArchivo()">
                    <i class="fas fa-upload"></i> Subir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Obra -->
<div class="modal fade" id="modalEditarObra" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Obra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarObra">
                    <input type="hidden" name="obra_id" value="<?=$obra->id?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre de la Obra</label>
                            <input type="text" class="form-control" name="nombre" value="<?=$obra->nombre?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estatus</label>
                            <select class="form-select" name="estatus">
                                <option value="Planificación" <?=$obra->estatus == 'Planificación' ? 'selected' : ''?>>Planificación</option>
                                <option value="En Cotización" <?=$obra->estatus == 'En Cotización' ? 'selected' : ''?>>En Cotización</option>
                                <option value="Aprobada" <?=$obra->estatus == 'Aprobada' ? 'selected' : ''?>>Aprobada</option>
                                <option value="En Ejecución" <?=$obra->estatus == 'En Ejecución' ? 'selected' : ''?>>En Ejecución</option>
                                <option value="Pausada" <?=$obra->estatus == 'Pausada' ? 'selected' : ''?>>Pausada</option>
                                <option value="Completada" <?=$obra->estatus == 'Completada' ? 'selected' : ''?>>Completada</option>
                                <option value="Cancelada" <?=$obra->estatus == 'Cancelada' ? 'selected' : ''?>>Cancelada</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Porcentaje de Avance (%)</label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" name="porcentaje_avance" value="<?=$obra->porcentaje_avance?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Costo Real</label>
                            <input type="number" step="0.01" class="form-control" name="costo_real" value="<?=$obra->costo_real ?? 0?>">
                        </div>
                    </div>
                    
                    <h6 class="mb-3"><i class="fas fa-tools"></i> Especificaciones</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Condiciones Ambientales</label>
                            <textarea class="form-control" name="condiciones_ambientales" rows="2"><?=$obra->condiciones_ambientales ?? ''?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Especificaciones Técnicas</label>
                            <textarea class="form-control" name="especificaciones_tecnicas" rows="2"><?=$obra->especificaciones_tecnicas ?? ''?></textarea>
                        </div>
                    </div>
                    
                    <h6 class="mb-3"><i class="fas fa-percent"></i> Ajustes Financieros</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Descuento (%)</label>
                            <input type="number" step="0.01" class="form-control" name="descuento_porcentaje" value="<?=$obra->descuento_porcentaje ?? 0?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">IVA (%)</label>
                            <input type="number" step="0.01" class="form-control" name="iva_porcentaje" value="<?=$obra->iva_porcentaje ?? 16?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Anticipo (%)</label>
                            <input type="number" step="0.01" class="form-control" name="anticipo_porcentaje" value="<?=$obra->anticipo_porcentaje ?? 0?>">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="actualizarObra()">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Pago -->
<div class="modal fade" id="modalRegistrarPago" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave"></i> Registrar Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRegistrarPago">
                    <input type="hidden" name="obra_id" value="<?=$obra->id?>">
                    
                    <div class="alert alert-info">
                        <strong>Saldo Pendiente:</strong> $<?=number_format($obra->saldo_pendiente ?? 0, 2)?>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha_pago" value="<?=date('Y-m-d')?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Monto <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="monto" id="montoPago" required>
                            <small class="text-muted">Máximo: $<?=number_format($obra->saldo_pendiente ?? 0, 2)?></small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
                            <select class="form-select" name="metodo_pago" required>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Referencia</label>
                            <input type="text" class="form-control" name="referencia" placeholder="Número de referencia, cheque, etc.">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Concepto <span class="text-danger">*</span></label>
                            <select class="form-select" name="concepto" required>
                                <option value="Anticipo">Anticipo</option>
                                <option value="Pago Parcial">Pago Parcial</option>
                                <option value="Avance de Obra">Avance de Obra</option>
                                <option value="Liquidación">Liquidación</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control" name="notas" rows="3" placeholder="Notas adicionales sobre el pago"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="registrarPago()">
                    <i class="fas fa-save"></i> Registrar Pago
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Ver Recibo -->
<div class="modal fade" id="modalVerRecibo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-receipt"></i> Recibo de Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contenidoRecibo">
                <!-- Se llenará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="imprimirRecibo()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Modal: Ver Formulación Obra -->
<div class="modal fade" id="modalFormulacionObra" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-flask"></i> Formulación:
                    <span id="lbl_form_producto_obra"></span>
                </h5>
                <div>
                    <button class="btn btn-sm btn-light text-info me-2" onclick="verHistorialFormulacionesObra()">
                        <i class="fas fa-history"></i> Historial
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body">
                <!-- Vista Activa -->
                <div id="vista_formulacion_activa_obra">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Versión:</strong> <span id="lbl_form_version_obra">V1.0</span>
                        </div>
                        <div class="col-md-6 text-end">
                            <strong>Costo Producción:</strong> <span id="lbl_form_costo_obra"
                                class="text-success fw-bold"></span>
                        </div>
                        <div class="col-12 mt-2">
                            <p class="text-muted small mb-0" id="lbl_form_desc_obra"></p>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Componente</th>
                                    <th>Tipo</th>
                                    <th class="text-center">Cant.</th>
                                    <th class="text-center">Unidad</th>
                                    <th class="text-end">Costo</th>
                                </tr>
                            </thead>
                            <tbody id="lista_componentes_formulacion_obra">
                                <!-- JS populate -->
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning small mb-0 mt-3 d-flex align-items-center">
                        <i class="fas fa-info-circle me-2 fa-2x"></i>
                        <div>
                            Está viendo la formulación activa. <button class="btn btn-sm btn-outline-dark ms-2"
                                onclick="usarFormulacionActualObra()">Usar esta versión</button>
                        </div>
                    </div>
                </div>
                <!-- Vista Historial -->
                <div id="vista_formulacion_historial_obra" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>Historial de Versiones</h6>
                        <button class="btn btn-sm btn-secondary" onclick="volverVistaActivaObra()"><i
                                class="fas fa-arrow-left"></i> Volver</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Versión</th>
                                    <th>Creación</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="lista_historial_formulaciones_obra"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Cargar productos cuando se abre el modal
    document.getElementById('modalAgregarProducto').addEventListener('show.bs.modal', function() {
        cargarProductos();
    });

    function cargarProductos() {
        fetch('<?=base_url()?>obras/Obras/get_productos_ajax')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const selectProducto = document.querySelector('#modalAgregarProducto select[name="producto_id"]');
                selectProducto.innerHTML = '<option value="">-- Seleccionar Producto --</option>';
                
                data.productos.forEach(producto => {
                    const option = document.createElement('option');
                    option.value = producto.id;
                    option.textContent = `${producto.nombre} (${producto.codigo})`;
                    option.dataset.unidad = producto.unidad_venta;
                    option.dataset.precio = producto.precio_venta;
                    selectProducto.appendChild(option);
                });
                
                // Auto-llenar unidad y precio al seleccionar producto
                selectProducto.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if(selectedOption.dataset.unidad) {
                        document.querySelector('#modalAgregarProducto input[name="unidad"]').value = selectedOption.dataset.unidad;
                    }
                    if(selectedOption.dataset.precio) {
                        document.querySelector('#modalAgregarProducto input[name="precio_unitario"]').value = selectedOption.dataset.precio;
                    }
                });
            }
        })
        .catch(error => console.error('Error al cargar productos:', error));
    }

    // Calculador automático de cantidades
    function calcularCantidad() {
        const area = parseFloat(document.getElementById('areaAplicacion').value) || 0;
        const rendimiento = parseFloat(document.getElementById('rendimientoTeorico').value) || 0;
        const factorDesperdicio = parseFloat(document.getElementById('factorDesperdicio').value) || 1.10;
        
        if (area > 0 && rendimiento > 0) {
            const cantidadCalculada = (area / rendimiento) * factorDesperdicio;
            document.getElementById('cantidadCalculada').value = cantidadCalculada.toFixed(2);
        } else {
            document.getElementById('cantidadCalculada').value = '';
        }
    }

    // Agregar listeners para cálculo automático
    document.getElementById('modalAgregarProducto').addEventListener('shown.bs.modal', function() {
        // Limpiar campos al abrir modal
        document.getElementById('formAgregarProducto').reset();
        document.getElementById('factorDesperdicio').value = '1.10';
        
        // Agregar listeners para cálculo en tiempo real
        document.getElementById('areaAplicacion').addEventListener('input', calcularCantidad);
        document.getElementById('rendimientoTeorico').addEventListener('input', calcularCantidad);
        document.getElementById('factorDesperdicio').addEventListener('input', calcularCantidad);
    });

    function actualizarObra() {
        const formData = new FormData(document.getElementById('formEditarObra'));
        
        fetch('<?=base_url()?>obras/Obras/actualizar_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                $('#modalEditarObra').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar la obra');
        });
    }

    function guardarProducto() {
        const formData = new FormData(document.getElementById('formAgregarProducto'));
        
        fetch('<?=base_url()?>obras/Obras/agregar_producto_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                $('#modalAgregarProducto').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar el producto');
        });
    }

    function eliminarProducto(productoObraId) {
        if(!confirm('¿Está seguro de eliminar este producto?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('producto_obra_id', productoObraId);
        
        fetch('<?=base_url()?>obras/Obras/eliminar_producto_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el producto');
        });
    }

    function subirArchivo() {
        const formData = new FormData(document.getElementById('formSubirArchivo'));
        
        fetch('<?=base_url()?>obras/Obras/subir_archivo_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                $('#modalSubirArchivo').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al subir el archivo');
        });
    }

    function eliminarArchivo(archivoId) {
        if(!confirm('¿Está seguro de eliminar este archivo?')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('archivo_id', archivoId);
        
        fetch('<?=base_url()?>obras/Obras/eliminar_archivo_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el archivo');
        });
    }

    function agregarComentario() {
        const formData = new FormData(document.getElementById('formComentario'));
        
        fetch('<?=base_url()?>obras/Obras/agregar_comentario_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                document.getElementById('formComentario').reset();
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar el comentario');
        });
    }


    // Cargar pagos al abrir la pestaña
    document.querySelector('a[href="#tabPagos"]').addEventListener('shown.bs.tab', function() {
        cargarPagos();
    });
    function cargarPagos() {
        fetch('<?=base_url()?>obras/Obras/get_pagos_ajax?obra_id=<?=$obra->id?>')
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const tbody = document.querySelector('#tablaPagos tbody');
                tbody.innerHTML = '';
                
                if(data.pagos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay pagos registrados</td></tr>';
                    return;
                }
                
                data.pagos.forEach(pago => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>${pago.folio_recibo}</strong></td>
                        <td>${new Date(pago.fecha_pago).toLocaleDateString('es-MX')}</td>
                        <td><strong class="text-success">$${parseFloat(pago.monto).toLocaleString('es-MX', {minimumFractionDigits: 2})}</strong></td>
                        <td><span class="badge bg-secondary">${pago.metodo_pago}</span></td>
                        <td>${pago.concepto}</td>
                        <td>${pago.referencia || '-'}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="verRecibo(${pago.id})" title="Ver Recibo">
                                <i class="fas fa-file-invoice"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="cancelarPago(${pago.id})" title="Cancelar Pago">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(error => console.error('Error al cargar pagos:', error));
    }
    function registrarPago() {
        const formData = new FormData(document.getElementById('formRegistrarPago'));
        const monto = parseFloat(formData.get('monto'));
        const saldoPendiente = <?=$obra->saldo_pendiente ?? 0?>;
        
        if(monto > saldoPendiente) {
            alert('El monto no puede ser mayor al saldo pendiente');
            return;
        }
        
        if(monto <= 0) {
            alert('El monto debe ser mayor a 0');
            return;
        }
        
        fetch('<?=base_url()?>obras/Obras/registrar_pago_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                $('#modalRegistrarPago').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al registrar el pago');
        });
    }
    function verRecibo(pagoId) {
        // Cargar datos del recibo
        fetch(`<?=base_url()?>obras/Obras/get_pagos_ajax?obra_id=<?=$obra->id?>`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const pago = data.pagos.find(p => p.id == pagoId);
                if(pago) {
                    const contenido = `
                        <div class="text-center mb-4">
                            <h3>RECIBO DE PAGO</h3>
                            <h4>${pago.folio_recibo}</h4>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <p><strong>Obra:</strong> <?=$obra->folio?> - <?=$obra->nombre?></p>
                                <p><strong>Cliente:</strong> <?=$obra->cliente?></p>
                            </div>
                            <div class="col-6 text-end">
                                <p><strong>Fecha de Pago:</strong> ${new Date(pago.fecha_pago).toLocaleDateString('es-MX')}</p>
                                <p><strong>Método:</strong> ${pago.metodo_pago}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-12">
                                <h5>Concepto: ${pago.concepto}</h5>
                                <p><strong>Referencia:</strong> ${pago.referencia || 'N/A'}</p>
                                ${pago.notas ? `<p><strong>Notas:</strong> ${pago.notas}</p>` : ''}
                            </div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-6">
                                <p><strong>Total de la Obra:</strong></p>
                                <h4>$${parseFloat(<?=$obra->total ?? 0?>).toLocaleString('es-MX', {minimumFractionDigits: 2})}</h4>
                            </div>
                            <div class="col-6 text-end">
                                <p><strong>Monto Pagado:</strong></p>
                                <h3 class="text-success">$${parseFloat(pago.monto).toLocaleString('es-MX', {minimumFractionDigits: 2})}</h3>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <p><strong>Total Pagado a la Fecha:</strong> $${parseFloat(<?=$obra->total_pagado ?? 0?>).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                            </div>
                            <div class="col-6 text-end">
                                <p><strong>Saldo Pendiente:</strong> <span class="text-danger">$${parseFloat(<?=$obra->saldo_pendiente ?? 0?>).toLocaleString('es-MX', {minimumFractionDigits: 2})}</span></p>
                            </div>
                        </div>
                    `;
                    document.getElementById('contenidoRecibo').innerHTML = contenido;
                    $('#modalVerRecibo').modal('show');
                }
            }
        });
    }
    function imprimirRecibo() {
        window.print();
    }
    function cancelarPago(pagoId) {
        if(!confirm('¿Está seguro de cancelar este pago? Esta acción actualizará los totales de la obra.')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('pago_id', pagoId);
        
        fetch('<?=base_url()?>obras/Obras/cancelar_pago_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cancelar el pago');
        });
    }


</script>


<script>
    // Variables globales para formulación de obras
    let currentProductoIdFormulacionObra = null;
    let currentProductoNombreFormulacionObra = null;
    let currentFormulacionActivaIdObra = null;
    let currentProductoObraId = null;
    function verFormulacionObra(producto_id, nombre_producto, producto_obra_id = null) {
        currentProductoIdFormulacionObra = producto_id;
        currentProductoNombreFormulacionObra = nombre_producto;
        currentProductoObraId = producto_obra_id;
        $('#lbl_form_producto_obra').text(nombre_producto);
        $('#lista_componentes_formulacion_obra').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
        // Reset vistas
        $('#vista_formulacion_activa_obra').show();
        $('#vista_formulacion_historial_obra').hide();
        $('#modalFormulacionObra').modal('show');
        fetch('<?=base_url()?>obras/Obras/get_formulacion_ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'producto_id=' + producto_id
        })
            .then(response => response.json())
            .then(res => {
                if (res.success && res.formulacion) {
                    currentFormulacionActivaIdObra = res.formulacion.id;
                    renderFormulacionObra(res.formulacion);
                } else {
                    $('#lista_componentes_formulacion_obra').html(`<tr><td colspan="5" class="text-center text-muted py-3">${res.message || 'No se encontró formulación activa'}</td></tr>`);
                    $('#lbl_form_version_obra').text('-');
                    $('#lbl_form_costo_obra').text('-');
                    $('#lbl_form_desc_obra').text('');
                    currentFormulacionActivaIdObra = null;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar formulación');
            });
    }
    function renderFormulacionObra(f) {
        $('#lbl_form_version_obra').text('V' + f.version + (f.nombre_version ? ' - ' + f.nombre_version : ''));
        $('#lbl_form_costo_obra').text(parseFloat(f.costo_total).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' }));
        $('#lbl_form_desc_obra').text(f.descripcion || 'Sin descripción');
        let html = '';
        if (f.componentes && f.componentes.length > 0) {
            f.componentes.forEach(c => {
                const nombre = c.tipo_componente === 'Insumo' ? c.insumo_nombre : c.producto_nombre;
                const codigo = c.tipo_componente === 'Insumo' ? c.insumo_codigo : c.producto_codigo;
                html += `
                <tr>
                    <td>
                        <div class="fw-bold">${nombre}</div>
                        <small class="text-muted">${codigo}</small>
                    </td>
                    <td><span class="badge bg-${c.tipo_componente === 'Insumo' ? 'secondary' : 'primary'}">${c.tipo_componente}</span></td>
                    <td class="text-center">${parseFloat(c.cantidad)}</td>
                    <td class="text-center">${c.unidad}</td>
                    <td class="text-end text-success">${parseFloat(c.costo_total).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' })}</td>
                </tr>
            `;
            });
        } else {
            html = '<tr><td colspan="5" class="text-center text-muted">Sin componentes definidos</td></tr>';
        }
        $('#lista_componentes_formulacion_obra').html(html);
    }
    function verHistorialFormulacionesObra() {
        $('#vista_formulacion_activa_obra').hide();
        $('#vista_formulacion_historial_obra').fadeIn();
        $('#lista_historial_formulaciones_obra').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
        fetch('<?=base_url()?>obras/Obras/get_historial_formulaciones_ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'producto_id=' + currentProductoIdFormulacionObra
        })
            .then(response => response.json())
            .then(res => {
                if (res.success && res.historial) {
                    let html = '';
                    res.historial.forEach(h => {
                        html += `
                    <tr>
                        <td>V${h.version} ${h.nombre_version ? '- ' + h.nombre_version : ''}</td>
                        <td>${h.fecha_creacion.split(' ')[0]}</td>
                        <td>
                            ${h.es_activa == 1 ? '<span class="badge bg-success">Activa</span>' : '<span class="badge bg-secondary">Inactiva</span>'}
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="seleccionarFormulacionObra(${h.id}, ${h.version})">
                                <i class="fas fa-check"></i> Usar esta
                            </button>
                        </td>
                    </tr>
                `;
                    });
                    $('#lista_historial_formulaciones_obra').html(html);
                } else {
                    $('#lista_historial_formulaciones_obra').html('<tr><td colspan="4" class="text-center">No hay historial disponible</td></tr>');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    function volverVistaActivaObra() {
        $('#vista_formulacion_historial_obra').hide();
        $('#vista_formulacion_activa_obra').fadeIn();
    }
    function seleccionarFormulacionObra(id_formulacion, version) {
        if (!currentProductoObraId) {
            alert('Primero agrega el producto a la obra');
            $('#modalFormulacionObra').modal('hide');
            return;
        }
        const formData = new FormData();
        formData.append('producto_obra_id', currentProductoObraId);
        formData.append('formulacion_id', id_formulacion);
        formData.append('formulacion_version', version);
        fetch('<?=base_url()?>obras/Obras/actualizar_formulacion_producto_ajax', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Formulación V' + version + ' asignada correctamente');
                    $('#modalFormulacionObra').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar formulación');
            });
    }
    function usarFormulacionActualObra() {
        if (currentFormulacionActivaIdObra) {
            const version = $('#lbl_form_version_obra').text().replace('V', '').split(' ')[0];
            seleccionarFormulacionObra(currentFormulacionActivaIdObra, version);
        }
    }
</script>
