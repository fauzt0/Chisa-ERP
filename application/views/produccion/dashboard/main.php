<!-- Breadcrumb -->
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">Producción</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Título -->
<div class="row mb-3">
    <div class="col-md-6">
        <h2><i class="fas fa-industry"></i> Dashboard de Producción</h2>
    </div>
    <div class="col-md-6 d-flex align-items-center justify-content-end gap-2 flex-wrap">
        <?php
        $ordenes_all   = $response['ordenes'] ?? [];
        $total_vis     = count($ordenes_all);
        $total_ventas  = count(array_filter($ordenes_all, fn($o) => $o->tipo_registro !== 'obra'));
        $total_obras   = count(array_filter($ordenes_all, fn($o) => $o->tipo_registro === 'obra'));
        ?>
        <span class="badge bg-dark fs-6 px-3 py-2">
            <i class="fas fa-list-ul"></i> <?=$total_vis?> Órdenes
        </span>
        <?php if($total_ventas > 0): ?>
        <span class="badge bg-primary fs-6 px-3 py-2">
            <i class="fas fa-shopping-cart"></i> <?=$total_ventas?> Ventas
        </span>
        <?php endif; ?>
        <?php if($total_obras > 0): ?>
        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
            <i class="fas fa-hard-hat"></i> <?=$total_obras?> Obras
        </span>
        <?php endif; ?>
        <!-- Contador Sin Insumos (se actualiza con AJAX tras cargar badges) -->
        <span class="badge bg-secondary fs-6 px-3 py-2" id="badge_sin_insumos" style="display:none;">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="count_sin_insumos">0</span> Sin Insumos
        </span>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-filter"></i> Filtros de Búsqueda</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="<?=base_url()?>produccion/Dashboard" id="formFiltros">
                    <div class="row">
                        <!-- Campo de búsqueda -->
                        <div class="col-md-4 mb-3">
                            <label class="form-label" style="font-size: 1.2rem; font-weight: 600;">Buscar por Folio o Cliente</label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="busqueda" 
                                   placeholder="Ej: OV-00001 o Nombre del cliente"
                                   value="<?=htmlspecialchars($response['filtros_activos']['busqueda'] ?? '')?>" 
                                   style="font-size: 1.1rem; padding: 0.75rem 1rem;">
                        </div>
                        
                        <!-- Filtros de estatus -->
                        <div class="col-md-8 mb-3">
                            <label class="form-label" style="font-size: 1.2rem; font-weight: 600;">Filtrar por Estatus</label>
                            <div class="d-flex flex-wrap gap-3">
                                <?php 
                                $estatus_disponibles = [
                                    'Confirmada' => 'warning',
                                    'En Preparación' => 'info',
                                    'En Proceso' => 'primary',
                                    'Completada' => 'success',
                                    'Entregada' => 'secondary'
                                ];
                                
                                // Obtener estatus activos (default: Confirmada y En Preparación)
                                $estatus_activos = $response['filtros_activos']['estatus'] ?? ['Confirmada', 'En Preparación'];
                                
                                foreach($estatus_disponibles as $estatus => $color): 
                                    $checked = in_array($estatus, $estatus_activos) ? 'checked' : '';
                                ?>
                                <div class="form-check" style="margin-right: 1rem;">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="estatus[]" 
                                           value="<?=$estatus?>" 
                                           id="estatus_<?=str_replace(' ', '_', $estatus)?>"
                                           <?=$checked?>
                                           style="width: 1.5rem; height: 1.5rem; cursor: pointer;">
                                    <label class="form-check-label" for="estatus_<?=str_replace(' ', '_', $estatus)?>" style="cursor: pointer; margin-left: 0.5rem;">
                                        <span class="badge bg-<?=$color?>" style="font-size: 1rem; padding: 0.5rem 0.75rem;"><?=$estatus?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg" style="font-size: 1.2rem; padding: 0.75rem 2rem; margin-right: 1rem;">
                                <i class="fas fa-search"></i> Aplicar Filtros
                            </button>
                            <a href="<?=base_url()?>produccion/Dashboard" class="btn btn-secondary btn-lg" style="font-size: 1.2rem; padding: 0.75rem 2rem;">
                                <i class="fas fa-times"></i> Limpiar Filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php 
    $ordenes = $response['ordenes'] ?? [];
    
    if(empty($ordenes)): ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-2x mb-2"></i>
                <p class="mb-0">No hay órdenes de producción pendientes</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach($ordenes as $orden): 
            // Determinar si es obra u orden
            $es_obra = ($orden->tipo_registro === 'obra');
            
            // Determinar color del badge según estatus
            $badgeColor = 'secondary';
            switch($orden->estatus) {
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
            
            // Color del header según tipo
            $headerColor = $es_obra ? 'warning' : 'primary';
            $icono = $es_obra ? 'hard-hat' : 'shopping-cart';
        ?>
        <div class="col-12 col-md-6 col-lg-3">    
            <div class="card h-100" style="cursor: pointer;" onclick="window.location.href='<?=base_url()?>produccion/Dashboard/detalle/<?=$orden->tipo_registro?>/<?=$orden->id?>'">
                <div class="card-header bg-<?=$headerColor?> text-white px-4 pt-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="card-title mb-0 text-white">
                                <i class="fas fa-<?=$icono?>"></i> <?=$orden->folio?>
                            </h5>
                        </div>
                        <span class="badge bg-light text-dark">
                            <?=$es_obra ? 'OBRA' : 'VENTA'?>
                        </span>
                    </div>
                    <div class="badge bg-<?=$badgeColor?> my-2"><?=$orden->estatus?></div>
                </div>
                <div class="card-body px-4 pt-2">
                    <p class="mb-2">
                        <i class="fas fa-building"></i> 
                        <strong><?=$orden->cliente ?: 'Sin cliente'?></strong>
                    </p>
                    <?php if($es_obra && $orden->nombre_comercial): ?>
                    <p class="mb-2 text-muted small">
                        <i class="fas fa-tag"></i> <?=$orden->nombre_comercial?>
                    </p>
                    <?php endif; ?>
                    <p class="mb-1 text-muted">
                        <i class="fas fa-calendar"></i> 
                        Creada: <?=date('d/m/Y', strtotime($orden->fecha_creacion))?>
                    </p>
                    <p class="mb-1 text-muted">
                        <i class="fas fa-box"></i> 
                        <?=$orden->total_productos?> producto(s)
                    </p>
                    <!-- Badge de Stock (asíncrono) -->
                    <div class="mt-2 text-center">
                        <?php $tipo_badge = $es_obra ? 'obra' : 'venta'; ?>
                        <span class="badge bg-secondary stock-badge"
                              id="stock_badge_<?=$tipo_badge?>_<?=$orden->id?>"
                              title="Verificando stock...">
                            <i class="fas fa-spinner fa-spin fa-xs"></i>
                        </span>
                    </div>
                </div>                        
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Toast Container para Notificaciones -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <div id="toastNuevasOrdenes" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-success text-white">
            <i class="fas fa-bell me-2"></i>
            <strong class="me-auto">¡Nuevas Órdenes!</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <p id="toastMensaje" class="mb-2"></p>
            <button class="btn btn-sm btn-primary w-100" onclick="location.reload()">
                <i class="fas fa-sync"></i> Actualizar Dashboard
            </button>
        </div>
    </div>
</div>

<!-- Indicador de Estado de Sincronización -->
<div class="position-fixed bottom-0 end-0 p-4 mb-3" style="z-index: 1000;">
    <small class="text-muted" id="estadoSincronizacion">
        <i class="fas fa-circle text-success"></i> Última verificación: <span id="ultimaVerificacionTexto">Iniciando...</span>
    </small>
</div>

<script>
// Variables globales para el sistema de alertas en tiempo real
let ultimaVerificacion = Math.floor(Date.now() / 1000);
let checkInterval = null;
let verificandoOrdenes = false;

// Inicializar el monitoreo cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    iniciarMonitoreoOrdenes();
    actualizarTextoUltimaVerificacion();
    // Cargar badges de stock de todas las órdenes (en batch)
    actualizarBadgesStock();
});

/**
 * Inicia el monitoreo periódico de nuevas órdenes
 */
function iniciarMonitoreoOrdenes() {
    // Verificar inmediatamente
    verificarNuevasOrdenes();
    
    // Configurar verificación cada 30 segundos
    checkInterval = setInterval(verificarNuevasOrdenes, 30000);
    
    console.log('Sistema de alertas en tiempo real iniciado');
}

/**
 * Verifica si hay nuevas órdenes mediante AJAX
 */
function verificarNuevasOrdenes() {
    if(verificandoOrdenes) {
        return; // Evitar llamadas simultáneas
    }
    
    verificandoOrdenes = true;
    actualizarEstadoSincronizacion('checking');
    
    // Obtener filtros activos del formulario
    const filtrosActivos = obtenerFiltrosActivos();
    
    // Crear URL con parámetros
    const params = new URLSearchParams({
        ultima_verificacion: ultimaVerificacion,
        filtros: JSON.stringify(filtrosActivos)
    });
    
    fetch('<?=base_url()?>produccion/Dashboard/check_nuevas_ordenes_ajax?' + params.toString(), {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(response => {
        if(response.success) {
            // Actualizar timestamp
            ultimaVerificacion = response.timestamp;
            
            // Si hay nuevas órdenes, mostrar notificación
            if(response.hay_nuevas && response.cantidad_nuevas > 0) {
                mostrarNotificacion(response.cantidad_nuevas);
                agregarOrdenesAlGrid(response.ordenes_nuevas);
                reproducirSonidoAlerta();
            }
            
            actualizarEstadoSincronizacion('success');
            actualizarTextoUltimaVerificacion();
        } else {
            actualizarEstadoSincronizacion('error');
        }
    })
    .catch(error => {
        console.error('Error al verificar nuevas órdenes:', error);
        actualizarEstadoSincronizacion('error');
    })
    .finally(() => {
        verificandoOrdenes = false;
    });
}

/**
 * Obtiene los filtros activos del formulario
 */
function obtenerFiltrosActivos() {
    const filtros = {};
    
    // Obtener búsqueda
    const busquedaInput = document.querySelector('input[name="busqueda"]');
    if(busquedaInput && busquedaInput.value) {
        filtros.busqueda = busquedaInput.value;
    }
    
    // Obtener estatus seleccionados
    const estatusSeleccionados = [];
    const checkboxes = document.querySelectorAll('input[name="estatus[]"]:checked');
    checkboxes.forEach(checkbox => {
        estatusSeleccionados.push(checkbox.value);
    });
    
    if(estatusSeleccionados.length > 0) {
        filtros.estatus = estatusSeleccionados;
    }
    
    return filtros;
}

/**
 * Muestra una notificación toast con el número de nuevas órdenes
 */
function mostrarNotificacion(cantidadNuevas) {
    const mensaje = cantidadNuevas === 1 
        ? 'Se ha detectado <strong>1 nueva orden</strong> de producción.' 
        : `Se han detectado <strong>${cantidadNuevas} nuevas órdenes</strong> de producción.`;
    
    const toastMensaje = document.getElementById('toastMensaje');
    toastMensaje.innerHTML = mensaje;
    
    const toastElement = document.getElementById('toastNuevasOrdenes');
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 8000
    });
    
    toast.show();
}

/**
 * Agrega las nuevas órdenes al grid con animación
 */
function agregarOrdenesAlGrid(ordenes) {
    if(!ordenes || ordenes.length === 0) {
        return;
    }
    
    // Obtener el grid de órdenes (último .row)
    const rows = document.querySelectorAll('.row');
    const grid = rows[rows.length - 1];
    
    ordenes.forEach(orden => {
        const ordenCard = crearTarjetaOrden(orden);
        
        // Crear elemento temporal
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = ordenCard.trim();
        const cardElement = tempDiv.firstChild;
        
        // Agregar al inicio del grid con animación
        cardElement.style.opacity = '0';
        grid.insertBefore(cardElement, grid.firstChild);
        
        // Fade in
        setTimeout(() => {
            cardElement.style.transition = 'opacity 0.8s';
            cardElement.style.opacity = '1';
            cardElement.classList.add('orden-nueva-highlight');
        }, 10);
        
        // Remover highlight después de 3 segundos
        setTimeout(() => {
            cardElement.classList.remove('orden-nueva-highlight');
        }, 3000);
    });
}

/**
 * Crea el HTML de una tarjeta de orden
 */
function crearTarjetaOrden(orden) {
    // Determinar color del badge según estatus
    let badgeColor = 'secondary';
    switch(orden.estatus) {
        case 'Confirmada':
            badgeColor = 'warning';
            break;
        case 'En Proceso':
            badgeColor = 'primary';
            break;
        case 'Completada':
            badgeColor = 'success';
            break;
        case 'Entregada':
            badgeColor = 'info';
            break;
    }
    
    // Formatear fecha
    const fecha = new Date(orden.fecha_creacion);
    const fechaFormateada = fecha.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
    
    // Formatear total
    const totalFormateado = parseFloat(orden.total).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    
    const html = `
        <div class="col-12 col-md-6 col-lg-3">    
            <div class="card" style="cursor: pointer;" onclick="window.location.href='<?=base_url()?>produccion/Dashboard/detalle/${orden.id}'">
                <div class="card-header px-4 pt-4">                
                    <h5 class="card-title mb-0">${orden.folio}</h5>
                    <div class="badge bg-${badgeColor} my-2">${orden.estatus}</div>
                </div>
                <div class="card-body px-4 pt-2">
                    <p class="mb-2">
                        <i class="fas fa-building"></i> 
                        <strong>${orden.cliente || 'Sin cliente'}</strong>
                    </p>
                    <p class="mb-1 text-muted">
                        <i class="fas fa-calendar"></i> 
                        Creada: ${fechaFormateada}
                    </p>
                    <p class="mb-1 text-muted">
                        <i class="fas fa-box"></i> 
                        ${orden.total_productos || 0} producto(s)
                    </p>
                    <p class="mb-0 text-muted">
                        <i class="fas fa-dollar-sign"></i> 
                        $${totalFormateado}
                    </p>
                </div>                        
            </div>
        </div>
    `;
    
    return html;
}

/**
 * Reproduce un sonido de alerta
 */
function reproducirSonidoAlerta() {
    try {
        // Crear un beep simple usando Web Audio API
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    } catch(e) {
        console.log('No se pudo reproducir el sonido de alerta');
    }
}

/**
 * Actualiza el estado visual de sincronización
 */
function actualizarEstadoSincronizacion(estado) {
    const indicador = document.querySelector('#estadoSincronizacion i');
    
    if(!indicador) return;
    
    switch(estado) {
        case 'checking':
            indicador.className = 'fas fa-spinner fa-spin text-warning';
            break;
        case 'success':
            indicador.className = 'fas fa-circle text-success';
            break;
        case 'error':
            indicador.className = 'fas fa-circle text-danger';
            break;
    }
}

/**
 * Actualiza el texto de última verificación
 */
function actualizarTextoUltimaVerificacion() {
    const ahora = new Date();
    const horaFormateada = ahora.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    
    const textoElement = document.getElementById('ultimaVerificacionTexto');
    if(textoElement) {
        textoElement.textContent = horaFormateada;
    }
}
/**
 * Actualiza los badges de stock de todas las órdenes visibles en el dashboard
 * en una sola llamada AJAX para máximo rendimiento.
 */
function actualizarBadgesStock() {
    const badges = document.querySelectorAll('.stock-badge');
    if (badges.length === 0) return;

    // Construir lista de órdenes a verificar
    const ordenes = [];
    badges.forEach(badge => {
        // id: stock_badge_{tipo}_{id}
        const parts = badge.id.replace('stock_badge_', '').split('_');
        if (parts.length >= 2) {
            const tipo = parts[0];         // 'venta' o 'obra'
            const id   = parts.slice(1).join('_'); // soporta IDs con _
            ordenes.push({ id: parseInt(id), tipo: tipo });
        }
    });

    if (ordenes.length === 0) return;

    $.post('<?=base_url()?>produccion/Dashboard/get_stock_estado_ordenes_ajax', {
        ordenes: JSON.stringify(ordenes)
    }, function(res) {
        if (!res.success) return;

        Object.entries(res.estados).forEach(([key, estado]) => {
            const badge = document.getElementById('stock_badge_' + key);
            if (!badge) return;

            badge.classList.remove('bg-secondary', 'bg-success', 'bg-danger', 'bg-warning');

            if (estado === 'ok') {
                badge.classList.add('bg-success');
                badge.setAttribute('title', 'Stock completo — listo para producir');
                badge.innerHTML = '<i class="fas fa-check-circle fa-xs"></i> Stock OK';
            } else if (estado === 'faltante') {
                badge.classList.add('bg-danger');
                badge.setAttribute('title', 'Insumos insuficientes — se requiere pre-orden');
                badge.innerHTML = '<i class="fas fa-exclamation-triangle fa-xs"></i> Sin Insumos';
            } else if (estado === 'sin_formulacion') {
                badge.classList.add('bg-warning');
                badge.setAttribute('title', 'Sin formulación activa configurada');
                badge.innerHTML = '<i class="fas fa-flask fa-xs"></i> Sin Fórmula';
            } else {
                badge.classList.add('bg-secondary');
                badge.innerHTML = '—';
            }
        });
        // Actualizar contador "Sin Insumos" en el header
        let countFaltante = 0;
        Object.values(res.estados).forEach(estado => {
            if (estado === 'faltante') countFaltante++;
        });

        const badgeSinInsumos  = document.getElementById('badge_sin_insumos');
        const countSinInsumos  = document.getElementById('count_sin_insumos');
        if (badgeSinInsumos && countSinInsumos) {
            countSinInsumos.textContent = countFaltante;
            if (countFaltante > 0) {
                badgeSinInsumos.classList.remove('bg-secondary');
                badgeSinInsumos.classList.add('bg-danger');
                badgeSinInsumos.style.display = '';
            } else {
                badgeSinInsumos.style.display = 'none';
            }
        }

    }, 'json').fail(function() {
        // Silenciar errores en el dashboard — no crítico
        badges.forEach(b => {
            b.classList.remove('bg-secondary');
            b.innerHTML = '';
        });
    });
}

</script>

<style>
/* Animación para resaltar nuevas órdenes */
@keyframes highlight-pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }
    50% {
        box-shadow: 0 0 20px 10px rgba(40, 167, 69, 0.3);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
    }
}

.orden-nueva-highlight {
    animation: highlight-pulse 1.5s ease-in-out 2;
    border: 2px solid #28a745 !important;
}
</style>
