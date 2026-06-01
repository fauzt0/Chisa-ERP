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
                                $imagen = $producto->foto_producto ?? 'assets/dist/img/logo.svg';
                            ?>
                            <tr>
                                <td>
                                    <img src="<?=base_url($imagen)?>" 
                                         alt="<?=$producto->producto_nombre?>" 
                                         class="img-thumbnail"
                                         style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                         onclick="verImagenProducto('<?=base_url($imagen)?>', '<?=addslashes($producto->producto_nombre)?>')"
                                         onerror="this.onerror=null; this.src='<?=base_url('assets/dist/img/logo.svg')?>'">
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
                                    <button class="btn btn-sm btn-primary" onclick="abrirHistorialFormulaciones(<?=$producto->producto_id?>, '<?=addslashes($producto->producto_nombre)?>')">
                                        <i class="fas fa-history"></i> Historial/Fórmula
                                    </button>
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

<!-- ============================================================
     SECCIÓN: INSUMOS REQUERIDOS PARA PRODUCCIÓN
     (Cargados vía AJAX al abrir la página)
     ============================================================ -->
<div class="row mb-4" id="seccion_insumos">
    <div class="col-12">
        <!-- Alerta de estado de stock (se actualiza dinámicamente) -->
        <div id="alerta_stock" class="alert d-none mb-3 py-3 px-4" role="alert">
            <div class="d-flex align-items-center justify-content-between">
                <div id="alerta_stock_msg">
                    <i class="fas fa-spinner fa-spin"></i> Verificando disponibilidad de insumos...
                </div>
                <button id="btn_preorden" class="btn btn-danger d-none"
                        onclick="abrirModalPreOrden()" title="Generar pre-orden de compra">
                    <i class="fas fa-shopping-cart"></i> Generar Pre-Orden de Compra
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="fas fa-flask"></i> Insumos Requeridos para Producción
                </h5>
                <button class="btn btn-sm btn-light" onclick="cargarInsumosRequeridos()" title="Actualizar">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div id="tabla_insumos_container">
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando insumos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     SECCIÓN: LOTES DE PRODUCCIÓN GENERADOS
     ============================================================ -->
<div class="row mb-4" id="seccion_lotes">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="fas fa-barcode"></i> Lotes de Producción Generados
                </h5>
                <button class="btn btn-sm btn-light" onclick="cargarLotesOrden()" title="Actualizar">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="card-body p-0">
                <div id="lotes_container">
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando lotes...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botones de Acción -->
<div class="row mt-2">
    <div class="col-12">
        <a href="<?=base_url()?>produccion/Dashboard" class="btn btn-secondary btn-lg">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
</div>

<!-- Modal: Pre-Orden de Compra -->
<div class="modal fade" id="modalPreOrden" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-shopping-cart"></i> Generar Pre-Orden de Compra
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Se crearán órdenes de compra en estatus <strong>Borrador</strong>, agrupadas por proveedor.
                    Podrás revisarlas en el módulo de <strong>Compras &gt; Órdenes de Compra</strong>.
                </div>
                <div id="preorden_detalle">
                    <p class="text-center"><i class="fas fa-spinner fa-spin"></i> Calculando...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn_confirmar_preorden" onclick="confirmarPreOrden()">
                    <i class="fas fa-check"></i> Confirmar y Crear Pre-Orden(es)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Etiqueta de Lote (Código de Barras) -->
<div class="modal fade" id="modalEtiqueta" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-barcode"></i> Etiqueta de Lote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="etiqueta_body">
                <!-- Contenido de la etiqueta generado dinámicamente -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" onclick="imprimirEtiqueta()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
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

<!-- Modal: Historial de Formulaciones para Dashboard -->
<div class="modal fade" id="modalHistorialFormulacionesDashboard" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-history"></i> Variaciones y Formulaciones - <span id="historial_producto_nombre_dashboard"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Seleccione una formulación para aplicarla a esta orden de venta.
        </div>
        <!-- Filtros -->
        <div class="card bg-light mb-3">
          <div class="card-body py-2">
            <div class="row align-items-end">
              <div class="col-md-4">
                <label class="form-label mb-1">Buscar (Versión, Comentarios, Cliente)</label>
                <input type="text" class="form-control form-control-sm" id="busquedaHistorialDashboard" placeholder="Buscar...">
              </div>
              <div class="col-md-2">
                <button class="btn btn-sm btn-secondary w-100" onclick="cargarHistorialDashboard()">Buscar</button>
              </div>
            </div>
          </div>
        </div>
        
        <div id="listaHistorialFormulacionesDashboard">
          <div class="text-center text-muted py-5">
            <i class="fas fa-spinner fa-spin fa-3x"></i>
            <p class="mt-3">Cargando historial...</p>
          </div>
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
// Esperar a que jQuery esté cargado
(function waitForjQuery() {
    if (typeof jQuery === 'undefined') {
        setTimeout(waitForjQuery, 50);
        return;
    }
    initDashboardDetalle();
})();

function initDashboardDetalle() {
    // Definir constantes necesarias para las funciones
    if (typeof ORDEN_ID === 'undefined') {
        window.ORDEN_ID = <?=$registro->id?>;
        window.ORDEN_TIPO = '<?=$es_obra ? 'obra' : 'venta'?>';
        window.FOLIO = '<?=addslashes($registro->folio)?>';
    }

    // Inicializar el monitoreo cuando la página esté lista
    $(document).ready(function() {
        cargarInsumosRequeridos();
        cargarLotesOrden();
        
        $('#busquedaHistorialDashboard').on('keyup', function(e) {
            if(e.key === 'Enter') cargarHistorialDashboard();
        });
    });
}

function abrirHistorialFormulaciones(productoId, productoNombre) {
    window.productoActualDashboard = productoId;
    $('#historial_producto_nombre_dashboard').text(productoNombre);
    $('#modalHistorialFormulacionesDashboard').modal('show');
    cargarHistorialDashboard();
}

function cargarHistorialDashboard() {
    let busqueda = $('#busquedaHistorialDashboard').val() || '';
    let productoId = window.productoActualDashboard;
    
    $('#listaHistorialFormulacionesDashboard').html(`
        <div class="text-center text-muted py-5">
        <i class="fas fa-spinner fa-spin fa-3x"></i>
        <p class="mt-3">Buscando formulaciones...</p>
        </div>
    `);
    
    $.post('<?=base_url()?>produccion/Productos/get_historial_formulaciones_ajax', {
        'producto_id': productoId,
        'busqueda': busqueda,
        'peticion': 'ajax'
    }, function(result) {
        result = JSON.parse(result);
        if(result.success) {
            if(result.formulaciones.length === 0) {
                $('#listaHistorialFormulacionesDashboard').html(`
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No se encontraron formulaciones.
                </div>
                `);
                return;
            }
            
            let html = '<div class="accordion" id="accordionHistorialDashboard">';
            result.formulaciones.forEach((f, index) => {
                const badgeActiva = f.es_activa == '1' ? '<span class="badge bg-success ms-2">Formulación por defecto</span>' : '';
                const fecha = new Date(f.fecha_creacion).toLocaleDateString('es-MX');
                
                html += `
                <div class="accordion-item border-start border-4 ${f.es_activa == '1' ? 'border-success' : 'border-secondary'} mb-2">
                    <h2 class="accordion-header">
                    <button class="accordion-button ${index > 0 ? 'collapsed' : ''} bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDash${f.id}">
                        <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                            <strong class="fs-5"><i class="fas fa-flask text-primary"></i> Versión ${f.version}: ${f.nombre_version || 'Sin nombre'}</strong>
                            ${badgeActiva}
                            </div>
                            <small class="text-muted me-3">Creada: ${fecha}</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted" style="font-size: 0.9rem;">
                            ${f.cliente_nombre ? `<span class="me-3"><i class="fas fa-user"></i> <strong>Cliente:</strong> ${f.cliente_nombre}</span>` : ''}
                            ${f.comentarios ? `<span><i class="fas fa-comment"></i> <strong>Nota:</strong> ${f.comentarios}</span>` : ''}
                            </div>
                        </div>
                        </div>
                    </button>
                    </h2>
                    <div id="collapseDash${f.id}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#accordionHistorialDashboard">
                    <div class="accordion-body">
                        <p>${f.descripcion || 'Sin descripción'}</p>
                        <div class="d-flex justify-content-end align-items-center border-top pt-2 mt-2">
                            <button class="btn btn-outline-info me-2" onclick="verFormulacion(${f.id}, $('#historial_producto_nombre_dashboard').text())">
                                <i class="fas fa-eye"></i> Ver Componentes
                            </button>
                            <button class="btn btn-success" onclick="aplicarFormulacionAOrden(${productoId}, ${f.id})">
                                <i class="fas fa-check-circle"></i> Aplicar a esta Orden
                            </button>
                        </div>
                    </div>
                    </div>
                </div>
                `;
            });
            html += '</div>';
            $('#listaHistorialFormulacionesDashboard').html(html);
        }
    });
}

function aplicarFormulacionAOrden(productoId, formulacionId) {
    if(!confirm("¿Está seguro de aplicar esta variación de formulación a la orden actual?")) return;
    
    // Check if user also wants to set as default
    const setAsDefault = confirm("¿Desea además establecer esta formulación como la nueva por defecto para futuros pedidos de este producto?");
    
    $.post('<?=base_url()?>produccion/Dashboard/aplicar_formulacion_orden_ajax', {
        'orden_id': ORDEN_ID,
        'tipo_orden': ORDEN_TIPO,
        'producto_id': productoId,
        'formulacion_id': formulacionId,
        'set_as_default': setAsDefault ? 1 : 0
    }, function(result) {
        if(typeof result === 'string') result = JSON.parse(result);
        if(result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || 'Error al aplicar la formulación');
        }
    });
}

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
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    $.ajax({
        url: '<?=base_url()?>produccion/Dashboard/actualizar_estatus_ajax',
        method: 'POST',
        data: {
            orden_id: ORDEN_ID,
            tipo:     ORDEN_TIPO,
            estatus:  nuevoEstatus
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                // Si se generaron lotes, recargar solo esa sección (sin reload completo)
                if (response.lotes_generados && response.lotes_generados.length > 0) {
                    let msg = response.message + `<br><small><strong>${response.lotes_generados.length}</strong> lote(s) generado(s) con código de barras.</small>`;
                    notifyShow(msg, 'success');
                    // Recargar secciones dinámicas
                    cargarLotesOrden();
                    cargarInsumosRequeridos();
                    // Actualizar badge de estatus en el header
                    setTimeout(() => location.reload(), 2000);
                } else {
                    notifyShow(response.message || 'Estatus actualizado', 'success');
                    setTimeout(() => location.reload(), 1200);
                }
            } else {
                notifyShow('Error: ' + response.message, 'danger');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        },
        error: function() {
            notifyShow('Error al comunicarse con el servidor', 'danger');
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
    $('#modalFormulacion').modal('show');
    $('#lbl_producto_nombre').text(producto_nombre);
    
    $.ajax({
        url: '<?=base_url()?>produccion/Dashboard/get_formulacion_detalle_ajax',
        method: 'POST',
        data: { formulacion_id: formulacion_id },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.formulacion) {
                const f = response.formulacion;
                $('#lbl_version').text('V' + f.version + (f.nombre_version ? ' - ' + f.nombre_version : ''));
                $('#lbl_descripcion').text(f.descripcion || 'Sin descripción');
                $('#lbl_costo_total').text('$' + parseFloat(f.costo_total).toLocaleString('es-MX', {minimumFractionDigits: 2}));
                
                let html = '';
                if(f.componentes && f.componentes.length > 0) {
                    f.componentes.forEach(c => {
                        const nombre = c.tipo_componente === 'Insumo' ? c.insumo_nombre : c.producto_nombre;
                        const codigo = c.tipo_componente === 'Insumo' ? c.insumo_codigo : c.producto_codigo;
                        const badgeClass = c.tipo_componente === 'Insumo' ? 'bg-secondary' : 'bg-primary';
                        const pct = c.porcentaje ? `<span class="badge bg-info ms-1">${parseFloat(c.porcentaje).toFixed(1)}%</span>` : '';
                        html += `
                            <tr>
                                <td><strong>${nombre}</strong>${pct}<br><small class="text-muted">${codigo}</small></td>
                                <td><span class="badge ${badgeClass}">${c.tipo_componente}</span></td>
                                <td class="text-center">${parseFloat(c.cantidad).toFixed(3)}</td>
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

// =====================================================
// VERIFICACIÓN DE STOCK E INSUMOS REQUERIDOS
// =====================================================


let insumosData = null;  // Cache de datos de insumos

function cargarInsumosRequeridos() {
    $('#tabla_insumos_container').html('<div class="text-center py-4 text-muted"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Verificando stock...</p></div>');
    $('#alerta_stock').removeClass('d-none alert-success alert-danger alert-warning').addClass('alert-secondary');
    $('#alerta_stock_msg').html('<i class="fas fa-spinner fa-spin"></i> Verificando disponibilidad de insumos...');
    $('#btn_preorden').addClass('d-none');

    $.post('<?=base_url()?>produccion/Dashboard/verificar_stock_ajax', {
        orden_id: ORDEN_ID,
        tipo: ORDEN_TIPO
    }, function(res) {
        insumosData = res;
        renderizarTablaInsumos(res);
    }, 'json').fail(function() {
        $('#tabla_insumos_container').html('<div class="alert alert-danger m-3">Error al cargar los insumos requeridos.</div>');
    });
}

function renderizarTablaInsumos(res) {
    const alerta = $('#alerta_stock');
    const msg    = $('#alerta_stock_msg');

    if (res.sin_formulacion) {
        alerta.removeClass('d-none alert-success alert-danger alert-secondary').addClass('alert-warning');
        msg.html('<i class="fas fa-exclamation-triangle"></i> <strong>Advertencia:</strong> Uno o más productos de esta orden no tienen formulación activa asignada. Configure la formulación en <a href="<?=base_url()?>produccion/Productos" class="alert-link">Gestión de Productos</a>.');
        $('#tabla_insumos_container').html('<div class="alert alert-warning m-3"><i class="fas fa-flask"></i> Sin formulación configurada para calcular insumos.</div>');
        return;
    }

    if (!res.insumos || res.insumos.length === 0) {
        alerta.removeClass('d-none alert-danger alert-warning alert-secondary').addClass('alert-success');
        msg.html('<i class="fas fa-check-circle"></i> <strong>No se requieren insumos</strong> (productos sin formulación de insumos definida).');
        $('#tabla_insumos_container').html('<div class="alert alert-info m-3">No hay insumos calculables para esta orden.</div>');
        return;
    }

    if (res.stock_suficiente) {
        alerta.removeClass('d-none alert-danger alert-warning alert-secondary').addClass('alert-success');
        msg.html('<i class="fas fa-check-circle fa-lg"></i> <strong>¡Stock suficiente!</strong> Todos los insumos están disponibles para iniciar producción.');
    } else {
        alerta.removeClass('d-none alert-success alert-warning alert-secondary').addClass('alert-danger');
        const nFaltantes = res.insumos.filter(i => !i.disponible).length;
        msg.html(`<i class="fas fa-times-circle fa-lg"></i> <strong>Stock insuficiente.</strong> Faltan ${nFaltantes} insumo(s) para producir esta orden.`);
        $('#btn_preorden').removeClass('d-none');
    }

    // Construir tabla
    let html = `
    <div class="table-responsive">
      <table class="table table-sm table-hover mb-0">
        <thead class="table-dark">
          <tr>
            <th>Insumo</th>
            <th class="text-center">%</th>
            <th class="text-center">Cant./Unidad</th>
            <th class="text-center">Total Requerido</th>
            <th class="text-center">Disponible</th>
            <th class="text-center">Faltante</th>
            <th class="text-center">Estado</th>
          </tr>
        </thead>
        <tbody>
    `;

    res.insumos.forEach(insumo => {
        const estadoBadge = insumo.disponible
            ? '<span class="badge bg-success"><i class="fas fa-check"></i> OK</span>'
            : `<span class="badge bg-danger"><i class="fas fa-times"></i> Faltante</span>`;
        const rowClass = insumo.disponible ? '' : 'table-danger';
        const pct = insumo.porcentaje ? parseFloat(insumo.porcentaje).toFixed(1) + '%' : '-';
        const faltanteStr = insumo.faltante > 0 
            ? `<strong class="text-danger">${parseFloat(insumo.faltante).toFixed(3)}</strong><br><small class="text-muted">≈ $${parseFloat(insumo.costo_estimado_faltante).toLocaleString('es-MX', {minimumFractionDigits: 2})}</small>`
            : '<span class="text-success">0</span>';

        html += `
          <tr class="${rowClass}">
            <td>
              <strong>${insumo.insumo_nombre}</strong><br>
              <small class="text-muted">${insumo.insumo_codigo}</small>
            </td>
            <td class="text-center"><span class="badge bg-secondary">${pct}</span></td>
            <td class="text-center">${parseFloat(insumo.cantidad_por_unidad).toFixed(3)} ${insumo.unidad}</td>
            <td class="text-center"><strong>${parseFloat(insumo.cantidad_requerida).toFixed(3)} ${insumo.unidad}</strong></td>
            <td class="text-center">${parseFloat(insumo.stock_actual).toFixed(3)} ${insumo.unidad}</td>
            <td class="text-center">${faltanteStr}</td>
            <td class="text-center">${estadoBadge}</td>
          </tr>
        `;
    });

    html += `</tbody></table></div>`;
    $('#tabla_insumos_container').html(html);
}

// =====================================================
// PRE-ORDEN DE COMPRA
// =====================================================

function abrirModalPreOrden() {
    if (!insumosData || !insumosData.insumos) return;

    const faltantes = insumosData.insumos.filter(i => !i.disponible);
    let html = '<h6 class="mb-3">Insumos que se incluirán en la(s) pre-orden(es):</h6>';
    html += '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Insumo</th><th class="text-center">Cantidad Faltante</th><th class="text-center">Precio Est.</th><th class="text-center">Subtotal Est.</th></tr></thead><tbody>';
    let total = 0;
    faltantes.forEach(i => {
        html += `<tr>
            <td><strong>${i.insumo_nombre}</strong><br><small>${i.insumo_codigo}</small></td>
            <td class="text-center">${parseFloat(i.faltante).toFixed(3)} ${i.unidad}</td>
            <td class="text-center">$${parseFloat(i.precio_promedio).toFixed(2)}</td>
            <td class="text-center text-danger"><strong>$${parseFloat(i.costo_estimado_faltante).toFixed(2)}</strong></td>
        </tr>`;
        total += parseFloat(i.costo_estimado_faltante);
    });
    html += `</tbody><tfoot class="table-light"><tr><td colspan="3" class="text-end"><strong>Total Estimado:</strong></td><td class="text-center"><strong class="text-danger">$${total.toFixed(2)}</strong></td></tr></tfoot></table>`;

    $('#preorden_detalle').html(html);
    $('#modalPreOrden').modal('show');
}

function confirmarPreOrden() {
    const btn = document.getElementById('btn_confirmar_preorden');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creando...';

    $.post('<?=base_url()?>produccion/Dashboard/generar_preorden_compra_ajax', {
        orden_id: ORDEN_ID,
        tipo:     ORDEN_TIPO,
        folio_origen: FOLIO
    }, function(res) {
        if (res.success) {
            let mensaje = res.message;
            if (res.ordenes_creadas && res.ordenes_creadas.length > 0) {
                mensaje += '<ul class="mt-2">';
                res.ordenes_creadas.forEach(oc => {
                    mensaje += `<li><strong>${oc.folio}</strong> — ${oc.proveedor} (${oc.num_insumos} insumos, $${parseFloat(oc.total).toFixed(2)})</li>`;
                });
                mensaje += '</ul>';
            }
            if (res.sin_proveedor && res.sin_proveedor.length > 0) {
                mensaje += `<br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Sin proveedor: ${res.sin_proveedor.join(', ')}</small>`;
            }
            $('#modalPreOrden').modal('hide');
            Swal && Swal.fire ? Swal.fire({title: '¡Pre-orden creada!', html: mensaje, icon: 'success'}) : alert(mensaje);
        } else {
            notifyShow && notifyShow(res.message, 'danger');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Confirmar y Crear Pre-Orden(es)';
    }, 'json').fail(function() {
        notifyShow && notifyShow('Error de servidor', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Confirmar y Crear Pre-Orden(es)';
    });
}

// =====================================================
// ETIQUETA / CÓDIGO DE BARRAS
// =====================================================

function verEtiquetaLote(loteId, codigoBarras, productoNombre, cantidad, unidad, fecha) {
    const html = `
        <div style="font-family: monospace; border: 2px solid #333; border-radius: 8px; padding: 20px; max-width: 400px; margin: 0 auto;">
            <div style="font-size:0.85rem; font-weight:bold; text-align:center; margin-bottom:8px;">CHISA RECUBRIMIENTOS</div>
            <svg id="barcode_svg"></svg>
            <div style="margin-top:10px; text-align:left; font-size:0.8rem;">
                <strong>Producto:</strong> ${productoNombre}<br>
                <strong>Cantidad:</strong> ${cantidad} ${unidad}<br>
                <strong>Fecha:</strong> ${fecha}<br>
                <strong>Código:</strong> ${codigoBarras}
            </div>
        </div>
    `;
    $('#etiqueta_body').html(html);

    // Cargar JsBarcode si no está cargado
    if (typeof JsBarcode === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
        script.onload = function() { JsBarcode('#barcode_svg', codigoBarras, {format: 'CODE128', width: 2, height: 80, displayValue: true}); };
        document.head.appendChild(script);
    } else {
        JsBarcode('#barcode_svg', codigoBarras, {format: 'CODE128', width: 2, height: 80, displayValue: true});
    }

    $('#modalEtiqueta').modal('show');
}

function imprimirEtiqueta() {
    const contenido = document.getElementById('etiqueta_body').innerHTML;
    const ventana = window.open('', '_blank', 'width=600,height=400');
    ventana.document.write(`<html><head><title>Etiqueta</title><style>body{margin:20px;font-family:monospace;}</style></head><body>${contenido}<script>window.print();window.close();<\/script></body></html>`);
    ventana.document.close();
}

// =====================================================
// LOTES DE PRODUCCIÓN
// =====================================================

/**
 * Carga los lotes de producción generados para esta orden (AJAX)
 */
function cargarLotesOrden() {
    $.post('<?=base_url()?>produccion/Dashboard/get_lotes_orden_ajax', {
        orden_id: ORDEN_ID,
        tipo:     ORDEN_TIPO
    }, function(res) {
        const container = document.getElementById('lotes_container');
        if (!res.success) {
            container.innerHTML = '<div class="text-center py-3 text-muted"><i class="fas fa-exclamation-circle"></i> Error al cargar lotes</div>';
            return;
        }

        if (!res.lotes || res.lotes.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-box-open fa-2x mb-2"></i>
                    <p class="mb-0">No hay lotes generados aún</p>
                    <small>Se generarán automáticamente al marcar la orden como <strong>Completada</strong></small>
                </div>`;
            return;
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código de Barras</th>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Fecha Producción</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>`;

        res.lotes.forEach(lote => {
            const estadoClass  = lote.estatus === 'Disponible' ? 'success' : 'secondary';
            const fechaDisplay = lote.fecha_produccion
                ? new Date(lote.fecha_produccion).toLocaleDateString('es-MX', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'})
                : '—';

            html += `
                <tr>
                    <td>
                        <code class="text-dark">${lote.codigo_barras}</code>
                        <div><svg id="bc_mini_${lote.id}" style="max-width:160px;"></svg></div>
                    </td>
                    <td>
                        <strong>${lote.producto_nombre || '—'}</strong>
                        <br><small class="text-muted">${lote.producto_codigo || ''}</small>
                    </td>
                    <td class="text-center">${parseFloat(lote.cantidad_producida).toFixed(2)} ${lote.unidad || ''}</td>
                    <td class="text-center"><small>${fechaDisplay}</small></td>
                    <td class="text-center">
                        <span class="badge bg-${estadoClass}">${lote.estatus}</span>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary"
                            onclick="verEtiquetaLote(${lote.id}, '${lote.codigo_barras}', '${(lote.producto_nombre||'').replace(/'/g,"\\'")}', '${lote.cantidad_producida}', '${lote.unidad||''}', '${fechaDisplay}')"
                            title="Ver etiqueta">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="<?=base_url()?>produccion/Dashboard/etiqueta_lote/${lote.id}" target="_blank"
                           class="btn btn-sm btn-outline-success" title="Imprimir etiqueta (página completa)">
                            <i class="fas fa-print"></i>
                        </a>
                    </td>
                </tr>`;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;

        // Renderizar código de barras en miniatura si JsBarcode está disponible
        setTimeout(function(){
            if (typeof JsBarcode !== 'undefined') {
                res.lotes.forEach(lote => {
                    try {
                        JsBarcode('#bc_mini_' + lote.id, lote.codigo_barras, {
                            format: 'CODE128', width: 1.5, height: 40,
                            displayValue: false, margin: 4
                        });
                    } catch(e) {}
                });
            }
        }, 300);

    }, 'json').fail(function() {
        document.getElementById('lotes_container').innerHTML =
            '<div class="text-center py-3 text-muted">Error al cargar lotes</div>';
    });
}

</script>
