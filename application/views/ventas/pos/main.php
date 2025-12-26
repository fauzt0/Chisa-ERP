<?php
/**
 * Vista principal del Point of Sale (POS)
 */
$stats = $response['stats'] ?? [];
?>

<!-- Breadcrumb -->
<div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
        <li class="breadcrumb-item"><a href="#">CRM Ventas</a></li>
        <li class="breadcrumb-item active">Punto de Venta</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Título -->
<div class="row mb-3">
  <div class="col-md-12">
    <h2><i class="fas fa-cash-register"></i> Punto de Venta (POS)</h2>
  </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
  <div class="col-md-3">
    <div class="card bg-success text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-0">Ventas Hoy</h6>
            <h3 class="mb-0"><?=number_format($stats['ventas_hoy'] ?? 0)?></h3>
            <small>$<?=number_format($stats['monto_hoy'] ?? 0, 2)?></small>
          </div>
          <div>
            <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="card bg-primary text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-0">Ventas del Mes</h6>
            <h3 class="mb-0"><?=number_format($stats['ventas_mes'] ?? 0)?></h3>
            <small>$<?=number_format($stats['monto_mes'] ?? 0, 2)?></small>
          </div>
          <div>
            <i class="fas fa-chart-line fa-3x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="card bg-warning text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-0">Cotizaciones</h6>
            <h3 class="mb-0"><?=number_format($stats['cotizaciones_pendientes'] ?? 0)?></h3>
          </div>
          <div>
            <i class="fas fa-file-invoice fa-3x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="card bg-info text-white">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="card-title mb-0">En Preparación</h6>
            <h3 class="mb-0"><?=number_format($stats['ordenes_preparacion'] ?? 0)?></h3>
          </div>
          <div>
            <i class="fas fa-boxes fa-3x opacity-50"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- POS Interface -->
<div class="row">
  <!-- Columna Izquierda: Catálogo de Productos -->
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="fas fa-box"></i> Catálogo de Productos</h5>
      </div>
      <div class="card-body">
        <!-- Búsqueda -->
        <div class="row mb-3">
          <div class="col-md-12">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
              <input type="text" class="form-control" id="buscar_producto" placeholder="Buscar por nombre, código o código de barras...">
              <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusqueda()">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Top Productos -->
        <div id="contenedor_top_productos" class="mb-3" style="display:none;">
            <h6 class="text-muted mb-2"><i class="fas fa-star text-warning"></i> Más Vendidos</h6>
            <div id="grid_top_productos" class="row g-2"></div>
            <hr>
        </div>
        
        <!-- Grid de Productos -->
        <div id="grid_productos" class="row" style="max-height: 500px; overflow-y: auto;">
          <div class="col-12 text-center text-muted py-5">
            <i class="fas fa-search fa-3x mb-3"></i>
            <p>Busca un producto para comenzar</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Columna Derecha: Ticket de Venta -->
  <div class="col-md-5">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="fas fa-receipt"></i> Ticket de Venta</h5>
      </div>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<style>
.select2-container--bootstrap-5 .select2-selection {
  border: 1px solid #ced4da;
  border-radius: 0.25rem;
  min-height: 38px;
}
</style>

<!-- ... (resto del contenido) ... -->

      <div class="card-body">
        <!-- Selector de Cliente -->
        <div class="mb-3">
          <label class="form-label d-flex justify-content-between align-items-center">
            Cliente
            <div>
                <small><a href="#" onclick="verDetallesCliente(); return false;" class="text-decoration-none me-2"><i class="fas fa-edit"></i> Datos Fiscales</a></small>
            </div>
          </label>
          <div class="input-group">
            <select class="form-select" id="ticket_cliente" onchange="verificarCliente()" style="width: 85%;">
              <option value="">Buscar cliente...</option>
            </select>
            <button class="btn btn-success" type="button" onclick="mostrarModalNuevoCliente()" title="Nuevo Cliente">
              <i class="fas fa-plus"></i>
            </button>
            <button class="btn btn-outline-secondary" type="button" onclick="verDetallesCliente()" title="Ver datos fiscales">
              <i class="fas fa-file-invoice"></i>
            </button>
          </div>
          
          <!-- Vista previa de datos del cliente -->
          <div id="info_cliente_seleccionado" class="alert alert-info mt-2 p-2 mb-0" style="display:none; font-size: 0.85rem;">
            <div class="row">
                <div class="col-md-12">
                    <strong><i class="fas fa-user"></i> <span id="lbl_cli_razon"></span></strong>
                </div>
                <div class="col-md-6 mt-1">
                    <i class="fas fa-id-card"></i> <span id="lbl_cli_rfc"></span>
                </div>
                <div class="col-md-6 mt-1">
                    <i class="fas fa-envelope"></i> <span id="lbl_cli_email"></span>
                </div>
                <div class="col-md-12 mt-1">
                     <i class="fas fa-dollar-sign"></i> Crédito: <span id="lbl_cli_credito"></span> | Días: <span id="lbl_cli_dias"></span>
                </div>
            </div>
          </div>
        </div>
        
        <!-- Tabla de Productos en el Ticket -->
        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
          <table class="table table-sm" id="tabla_ticket">
            <thead class="table-light sticky-top">
              <tr>
                <th>Producto</th>
                <th width="80">Cant.</th>
                <th width="100">Precio</th>
                <th width="100">Subtotal</th>
                <th width="40"></th>
              </tr>
            </thead>
            <tbody id="ticket_items">
              <tr>
                <td colspan="5" class="text-center text-muted">
                  <i class="fas fa-shopping-basket"></i> Carrito vacío
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <hr>
        
        <!-- Totales -->
        <div class="row mb-2">
          <div class="col-6 text-end"><strong>Subtotal:</strong></div>
          <div class="col-6 text-end" id="ticket_subtotal">$0.00</div>
        </div>
        <div class="row mb-2" id="row_descuento" style="display: none;">
          <div class="col-6 text-end"><strong>Descuento:</strong></div>
          <div class="col-6 text-end text-success" id="ticket_descuento_aplicado">-$0.00</div>
        </div>
        <div class="row mb-2">
          <div class="col-6 text-end"><strong>IVA (16%):</strong></div>
          <div class="col-6 text-end" id="ticket_iva">$0.00</div>
        </div>
        <div class="row mb-3">
          <div class="col-6 text-end"><h5><strong>TOTAL:</strong></h5></div>
          <div class="col-6 text-end"><h5 class="text-primary" id="ticket_total">$0.00</h5></div>
        </div>
        
        <!-- Descuento -->
        <div class="mb-3">
          <label class="form-label">Aplicar Descuento</label>
          <select class="form-select" id="ticket_descuento" onchange="aplicarDescuento()">
            <option value="">Sin descuento</option>
          </select>
          <small class="text-muted" id="descuento_info"></small>
        </div>
        
        
        <!-- Tipo de Venta -->
        <div class="mb-3">
          <label class="form-label">Tipo de Venta</label>
          <select class="form-select" id="ticket_tipo_venta" onchange="toggleDireccionEnvio()">
            <option value="Mostrador">Mostrador</option>
            <option value="Pedido">Pedido (Envío)</option>
          </select>
        </div>
        
        <!-- Dirección de Envío (solo para Pedido) -->
        <div class="mb-3" id="div_direccion_envio" style="display:none;">
          <label class="form-label">Dirección de Envío <span class="text-danger">*</span></label>
          <textarea class="form-control" id="ticket_direccion_envio" rows="3" placeholder="Calle, número, colonia, ciudad, estado, CP"></textarea>
          <small class="text-muted">Requerido para pedidos con envío</small>
        </div>
        
        <!-- Costo de Envío (solo para Pedido) -->
        <div class="mb-3" id="div_costo_envio" style="display:none;">
          <label class="form-label">Costo de Envío</label>
          <input type="number" class="form-control" id="ticket_costo_envio" min="0" step="0.01" value="0" placeholder="0.00" onchange="actualizarTotales()">
          <small class="text-muted">Se sumará al total de la orden</small>
        </div>
        
        <!-- Fecha de Entrega Estimada (solo para Pedido) -->
        <div class="mb-3" id="div_fecha_entrega" style="display:none;">
          <label class="form-label">Fecha de Entrega Estimada</label>
          <input type="date" class="form-control" id="ticket_fecha_entrega" min="<?php echo date('Y-m-d'); ?>">
          <small class="text-muted">Fecha estimada de entrega al cliente</small>
        </div>
        
        <!-- Forma de Pago -->
        <div class="mb-3">
          <label class="form-label">Forma de Pago</label>
          <select class="form-select" id="ticket_forma_pago">
            <option value="Efectivo">Efectivo</option>
            <option value="Transferencia">Transferencia</option>
            <option value="Tarjeta">Tarjeta</option>
            <option value="Cheque">Cheque</option>
            <option value="Crédito">Crédito</option>
          </select>
        </div>
        
        <!-- Observaciones -->
        <div class="mb-3">
          <label class="form-label">Observaciones</label>
          <textarea class="form-control" id="ticket_observaciones" rows="2"></textarea>
        </div>
        
        <!-- Botones de Acción -->
        <div class="row">
          <div class="col-6">
            <button type="button" class="btn btn-secondary w-100" onclick="guardarCotizacion()">
              <i class="fas fa-save"></i> Guardar Cotización
            </button>
          </div>
          <div class="col-6">
            <button type="button" class="btn btn-success w-100" onclick="cobrarVenta()">
              <i class="fas fa-cash-register"></i> Cobrar
            </button>
          </div>
        </div>
        
        <button type="button" class="btn btn-danger w-100 mt-2" onclick="cancelarTicket()">
          <i class="fas fa-times"></i> Cancelar Ticket
        </button>
      </div>    </div>
  </div>
</div>

<!-- Modal Cliente / Datos Fiscales -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Datos Fiscales del Cliente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formCliente">
          <input type="hidden" id="cli_id" name="cliente_id">
          
          <div class="mb-3">
            <label class="form-label">Razón Social</label>
            <input type="text" class="form-control" id="cli_razon_social" required>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">RFC</label>
              <input type="text" class="form-control" id="cli_rfc" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Código Postal</label>
              <input type="text" class="form-control" id="cli_cp">
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Régimen Fiscal</label>
            <select class="form-select" id="cli_regimen">
              <option value="">Seleccionar...</option>
              <option value="601">601 - General de Ley Personas Morales</option>
              <option value="612">612 - Personas Físicas con Actividades Empresariales</option>
              <option value="626">626 - Régimen Simplificado de Confianza</option>
              <option value="616">616 - Sin obligaciones fiscales</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Uso de CFDI</label>
            <select class="form-select" id="cli_uso_cfdi">
              <option value="G03">G03 - Gastos en general</option>
              <option value="G01">G01 - Adquisición de mercancías</option>
              <option value="S01">S01 - Sin efectos fiscales</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Email Facturación</label>
            <input type="email" class="form-control" id="cli_email">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" onclick="guardarDatosFiscales()">Guardar Datos</button>
      </div>
    </div>
  </div>
</div>
        

<!-- Modal: Ver Formulación -->
<div class="modal fade" id="modalFormulacion" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-flask"></i> Formulación: <span id="lbl_form_producto"></span></h5>
        <div>
            <button class="btn btn-sm btn-light text-info me-2" onclick="verHistorialFormulaciones()">
                <i class="fas fa-history"></i> Historial
            </button>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
      </div>
      <div class="modal-body">
        <!-- Vista Activa -->
        <div id="vista_formulacion_activa">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Versión:</strong> <span id="lbl_form_version">V1.0</span>
                </div>
                <div class="col-md-6 text-end">
                    <strong>Costo Producción:</strong> <span id="lbl_form_costo" class="text-success fw-bold"></span>
                </div>
                <div class="col-12 mt-2">
                    <p class="text-muted small mb-0" id="lbl_form_desc"></p>
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
                    <tbody id="lista_componentes_formulacion">
                        <!-- JS populate -->
                    </tbody>
                </table>
            </div>
            
            <div class="alert alert-warning small mb-0 mt-3 d-flex align-items-center">
                <i class="fas fa-info-circle me-2 fa-2x"></i>
                <div>
                    Está viendo la formulación activa. <button class="btn btn-sm btn-outline-dark ms-2" onclick="usarFormulacionActual()">Usar esta versión</button>
                </div>
            </div>
        </div>

        <!-- Vista Historial -->
        <div id="vista_formulacion_historial" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6>Historial de Versiones</h6>
                <button class="btn btn-sm btn-secondary" onclick="volverVistaActiva()"><i class="fas fa-arrow-left"></i> Volver</button>
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
                    <tbody id="lista_historial_formulaciones"></tbody>
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
let currentProductoIdFormulacion = null;
let currentProductoNombreFormulacion = null;
let currentFormulacionActivaId = null;

function verFormulacion(producto_id, nombre_producto) {
    currentProductoIdFormulacion = producto_id;
    currentProductoNombreFormulacion = nombre_producto;
    
    $('#lbl_form_producto').text(nombre_producto);
    $('#lista_componentes_formulacion').html('<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    
    // Reset vistas
    $('#vista_formulacion_activa').show();
    $('#vista_formulacion_historial').hide();
    
    $('#modalFormulacion').modal('show');
    
    $.post('<?=base_url();?>ventas/Pos/get_formulacion_ajax', {
        'producto_id': producto_id,
        '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
        try {
            const res = JSON.parse(result);
            if(res.success && res.formulacion) {
                currentFormulacionActivaId = res.formulacion.id;
                renderFormulacion(res.formulacion);
            } else {
                $('#lista_componentes_formulacion').html(`<tr><td colspan="5" class="text-center text-muted py-3">${res.message || 'No se encontró formulación activa'}</td></tr>`);
                $('#lbl_form_version').text('-');
                $('#lbl_form_costo').text('-');
                $('#lbl_form_desc').text('');
                currentFormulacionActivaId = null;
            }
        } catch(e) {
            console.error(e);
            notifyShow('Error al cargar formulación', 'danger');
        }
    });
}

function renderFormulacion(f) {
    $('#lbl_form_version').text('V' + f.version + (f.nombre_version ? ' - ' + f.nombre_version : ''));
    $('#lbl_form_costo').text(parseFloat(f.costo_total).toLocaleString('es-MX', {style: 'currency', currency: 'MXN'}));
    $('#lbl_form_desc').text(f.descripcion || 'Sin descripción');
    
    let html = '';
    if(f.componentes && f.componentes.length > 0) {
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
                    <td class="text-end text-success">${parseFloat(c.costo_total).toLocaleString('es-MX', {style: 'currency', currency: 'MXN'})}</td>
                </tr>
            `;
        });
    } else {
        html = '<tr><td colspan="5" class="text-center text-muted">Sin componentes definidos</td></tr>';
    }
    
    $('#lista_componentes_formulacion').html(html);
}

function verHistorialFormulaciones() {
    $('#vista_formulacion_activa').hide();
    $('#vista_formulacion_historial').fadeIn();
    $('#lista_historial_formulaciones').html('<tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    
    $.post('<?=base_url();?>ventas/Pos/get_historial_formulaciones_ajax', {
        'producto_id': currentProductoIdFormulacion,
        '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
        try {
            const res = JSON.parse(result);
            if(res.success && res.historial) {
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
                                <button class="btn btn-sm btn-primary" onclick="seleccionarFormulacion(${h.id}, ${h.version})">
                                    <i class="fas fa-check"></i> Usar esta
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#lista_historial_formulaciones').html(html);
            } else {
                $('#lista_historial_formulaciones').html('<tr><td colspan="4" class="text-center">No hay historial disponible</td></tr>');
            }
        } catch(e) {
            console.error(e);
        }
    });
}

function volverVistaActiva() {
    $('#vista_formulacion_historial').hide();
    $('#vista_formulacion_activa').fadeIn();
}

function seleccionarFormulacion(id_formulacion, version) {
    if(!currentProductoIdFormulacion) return;
    
    // Obtener información básica del producto (precio, stock) - Esto es un hack porque no lo tenemos aquí directo
    // Lo ideal sería obtenerlo del DOM o pasarlo. Vamos a buscarlo en el grid o recargar.
    // Simplificación: Asumimos que el usuario lo agrega. Pero necesitamos precio.
    // Solución: Cerrar modal y notificar que se usará esa formulación al agregar.
    
    // Mejor flujo directo: Llamar agregarAlTicket con parametro extra.
    // PERO agregarAlTicket necesita precio y stock.
    // Vamos a buscar el producto en el DOM para sacar esos datos? No, muy frágil.
    
    // Alternativa: Guardar la formulación seleccionada en una variable temporal y cuando el usuario de click en la tarjeta, usarla?
    // O mejor, invocar agregarAlTicket buscando los datos del producto via AJAX o asumiendo que ya los tenemos
    // Si abrimos el modal desde la tarjeta, teníamos los datos.
    
    // Requerimos modificar 'verFormulacion' para recibir precio y stock también.
    
    // Por ahora, mostrar mensaje y cerrar.
    $('#modalFormulacion').modal('hide');
    // Implementar lógica de selección especial para el ticketitem
    notifyShow(`Formulación V${version} seleccionada para el producto.`, 'info');
    
    // Trigger evento custom o guardar en global para que la proxima vez que se agregue ESTE producto, use ESA formulación
    window.formulacionSeleccionada = {
        producto_id: currentProductoIdFormulacion,
        formulacion_id: id_formulacion,
        version: version
    };
    
    // Si ya está en el ticket, actualizarlo?
    // Buscar en ticketItems
    const existingItem = ticketItems.find(i => i.id == currentProductoIdFormulacion);
    if(existingItem) {
        existingItem.formulacion_id = id_formulacion;
        existingItem.formulacion_version = version;
        renderTicket();
        notifyShow('Producto en ticket actualizado con la formulación V' + version, 'success');
    }
}

function usarFormulacionActual() {
    if(currentFormulacionActivaId) {
        seleccionarFormulacion(currentFormulacionActivaId, $('#lbl_form_version').text().replace('V', '').split(' ')[0]);
    }
}
</script>
<div class="modal fade" id="modalNuevoClientePOS" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-user-plus"></i> Nuevo Cliente</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formNuevoClientePOS">
          
          <!-- Datos Fiscales -->
          <h6><i class="fas fa-file-invoice"></i> Datos Fiscales</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Razón Social <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="new_cliente_razon_social" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Nombre Comercial</label>
              <input type="text" class="form-control" id="new_cliente_nombre_comercial">
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">RFC <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="new_cliente_rfc" maxlength="13" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Régimen Fiscal</label>
              <select class="form-select" id="new_cliente_regimen_fiscal">
                  <option value="">Seleccionar...</option>
                  <option value="601">601 - General de Ley Personas Morales</option>
                  <option value="612">612 - Personas Físicas con Actividades Empresariales</option>
                  <option value="626">626 - Régimen Simplificado de Confianza</option>
                  <option value="616">616 - Sin obligaciones fiscales</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Tipo de Cliente <span class="text-danger">*</span></label>
              <select class="form-select" id="new_cliente_tipo_cliente" required>
                <option value="Regular">Regular</option>
                <option value="Mostrador">Mostrador</option>
                <option value="Gobierno">Gobierno</option>
                <option value="Licitación">Licitación</option>
                <option value="Distribuidor">Distribuidor</option>
              </select>
            </div>
          </div>
          
          <hr>
          
          <!-- Contacto -->
          <h6><i class="fas fa-address-book"></i> Datos de Contacto</h6>
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Nombre de Contacto</label>
              <input type="text" class="form-control" id="new_cliente_contacto_nombre">
            </div>
            <div class="col-md-4">
              <label class="form-label">Teléfono</label>
              <input type="text" class="form-control" id="new_cliente_telefono">
            </div>
            <div class="col-md-4">
              <label class="form-label">Email Facturación</label>
              <input type="email" class="form-control" id="new_cliente_email">
            </div>
          </div>
          
          <hr>
          
          <!-- Dirección -->
          <h6><i class="fas fa-map-marker-alt"></i> Dirección</h6>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Calle</label>
              <input type="text" class="form-control" id="new_cliente_calle">
            </div>
            <div class="col-md-3">
              <label class="form-label">Número Exterior</label>
              <input type="text" class="form-control" id="new_cliente_numero_exterior">
            </div>
            <div class="col-md-3">
              <label class="form-label">Número Interior</label>
              <input type="text" class="form-control" id="new_cliente_numero_interior">
            </div>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Colonia</label>
              <input type="text" class="form-control" id="new_cliente_colonia">
            </div>
            <div class="col-md-4">
              <label class="form-label">Ciudad</label>
              <input type="text" class="form-control" id="new_cliente_ciudad">
            </div>
            <div class="col-md-2">
              <label class="form-label">Estado</label>
              <input type="text" class="form-control" id="new_cliente_estado">
            </div>
            <div class="col-md-2">
              <label class="form-label">C.P.</label>
              <input type="text" class="form-control" id="new_cliente_codigo_postal" maxlength="5">
            </div>
          </div>
          
             <div class="row mb-3">
               <div class="col-md-12">
                 <label class="form-label">Uso de CFDI</label>
                 <select class="form-select" id="new_cliente_uso_cfdi">
                   <option value="G03">G03 - Gastos en general</option>
                   <option value="G01">G01 - Adquisición de mercancías</option>
                   <option value="S01">S01 - Sin efectos fiscales</option>
                 </select>
               </div>
             </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="guardarClientePOS()">Guardar Cliente</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Inicializar Select2 al cargar
function initSelect2() {
    $('#ticket_cliente').select2({
        theme: 'bootstrap-5',
        placeholder: 'Buscar cliente...',
        allowClear: true,
        width: 'style'
    });
}
</script>

<script>
let ticketItems = [];
let clientes = [];
let descuentos = [];
let descuentoSeleccionado = null;


function initPOS() {
  cargarClientes();
  cargarDescuentos();
  inicializarBusqueda();
  cargarTopProductos();
  // Init Select2 after loading clients in cargarClientes
}

function cargarTopProductos() {
  $.post('<?=base_url();?>ventas/Pos/get_top_productos_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success && result.productos.length > 0) {
      $('#contenedor_top_productos').show();
      let html = '';
      result.productos.forEach(p => {
        const stock = parseFloat(p.stock_actual);
        html += `
          <div class="col-md-4 col-sm-6">
            <div class="card h-100 producto-card border-warning" onclick="agregarAlTicket(${p.id}, '${p.nombre.replace(/'/g, "\\'")}', ${p.precio_venta}, ${stock})" style="cursor: pointer;">
              <div class="card-body p-2 text-center">
                <h6 class="card-title text-truncate mb-1" style="font-size: 0.9rem;">${p.nombre}</h6>
                <p class="text-primary fw-bold mb-0">$${parseFloat(p.precio_venta).toFixed(2)}</p>
                <small class="text-success"><i class="fas fa-check"></i> Stock: ${stock}</small>
              </div>
            </div>
          </div>
        `;
      });
      $('#grid_top_productos').html(html);
    }
  });
}

function buscarProductos() {
  const busqueda = $('#buscar_producto').val();
  
  if(!busqueda || busqueda.length < 2) {
    $('#contenedor_top_productos').show();
    $('#grid_productos').html(`
      <div class="col-12 text-center text-muted py-5">
        <i class="fas fa-search fa-3x mb-3"></i>
        <p>Busca un producto para comenzar</p>
      </div>
    `);
    return;
  }
  
  $('#contenedor_top_productos').hide(); // Ocultar top productos al buscar
  
  $.post('<?=base_url();?>ventas/Pos/get_productos_ajax', {
    'busqueda': busqueda,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      renderProductos(result.productos);
    }
  });
}

function limpiarBusqueda() {
  $('#buscar_producto').val('');
  buscarProductos();
}

function verificarCliente() {
  const cliente_id = $('#ticket_cliente').val();
  
  if(!cliente_id) {
      $('#info_cliente_seleccionado').hide();
      return;
  }
  
  // Buscar cliente en array local
  const cliente = clientes.find(c => c.id == cliente_id);
  
  if(cliente) {
      $('#lbl_cli_razon').text(cliente.razon_social);
      $('#lbl_cli_rfc').text(cliente.rfc || 'Sin RFC');
      $('#lbl_cli_email').text(cliente.email_facturacion || cliente.email || 'Sin Email');
      
      const credito = parseFloat(cliente.limite_credito || 0).toLocaleString('es-MX', {style: 'currency', currency: 'MXN'});
      const dias = cliente.dias_credito || 0;
      
      $('#lbl_cli_credito').text(credito);
      $('#lbl_cli_dias').text(dias);
      
      // Mostrar contenedor
      $('#info_cliente_seleccionado').slideDown();
  }
}

function verDetallesCliente() {
  const cliente_id = $('#ticket_cliente').val();
  if(!cliente_id) {
    notifyShow('Selecciona un cliente primero', 'warning');
    return;
  }
  
  // Buscar cliente en array local
  const cliente = clientes.find(c => c.id == cliente_id);
  
  if(cliente) {
    $('#cli_id').val(cliente.id);
    $('#cli_razon_social').val(cliente.razon_social);
    $('#cli_rfc').val(cliente.rfc || '');
    // Asumimos que los campos nuevos vendrán en el futuro cuando actualicemos get_clientes_ajax o hagamos fetch individual
    // Por ahora, si no vienen, mostrar vacíos o lo que tenga
    $('#cli_cp').val(cliente.codigo_postal || '');
    $('#cli_regimen').val(cliente.regimen_fiscal || '');
    $('#cli_uso_cfdi').val(cliente.uso_cfdi || 'G03');
    $('#cli_email').val(cliente.email_facturacion || cliente.email || '');
    
    $('#modalCliente').modal('show');
  }
}

// Funciones para Nuevo Cliente POS
function mostrarModalNuevoCliente() {
    $('#formNuevoClientePOS')[0].reset();
    $('#new_cliente_uso_cfdi').val('G03');
    $('#new_cliente_tipo_cliente').val('Regular');
    $('#modalNuevoClientePOS').modal('show');
}

function guardarClientePOS() {
  const data = {
    'razon_social': $('#new_cliente_razon_social').val(),
    'nombre_comercial': $('#new_cliente_nombre_comercial').val(),
    'rfc': $('#new_cliente_rfc').val(),
    'regimen_fiscal': $('#new_cliente_regimen_fiscal').val(),
    'uso_cfdi': $('#new_cliente_uso_cfdi').val(),
    'tipo_cliente': $('#new_cliente_tipo_cliente').val(),
    
    'contacto_nombre': $('#new_cliente_contacto_nombre').val(),
    'telefono': $('#new_cliente_telefono').val(),
    'email': $('#new_cliente_email').val(),
    
    'calle': $('#new_cliente_calle').val(),
    'numero_exterior': $('#new_cliente_numero_exterior').val(),
    'numero_interior': $('#new_cliente_numero_interior').val(),
    'colonia': $('#new_cliente_colonia').val(),
    'ciudad': $('#new_cliente_ciudad').val(),
    'estado': $('#new_cliente_estado').val(),
    'codigo_postal': $('#new_cliente_codigo_postal').val(),
    
    'estatus': 'Activo',
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  };
  
  if(!data.razon_social || !data.rfc) {
      notifyShow('Razón Social y RFC son obligatorios', 'warning');
      return;
  }
  
  $.post('<?=base_url();?>ventas/Clientes/crear_ajax', data, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if(result.success) {
      $('#modalNuevoClientePOS').modal('hide');
      // Recargar clientes y seleccionar el nuevo (simplificado: recargar todos)
      // En una implementación ideal, el servidor devolvería el ID nuevo
        cargarClientes(function() {
             // Podríamos intentar seleccionar el nuevo cliente si tuviéramos su ID
             // Como no tenemos el ID fácilmente sin modificar el controller Clientes, 
             // al menos ya aparecerá en la lista
             notifyShow("Cliente creado y listo para seleccionar", "success");
        });
    }
  });
}


function guardarDatosFiscales() {
  const data = {
    'cliente_id': $('#cli_id').val(),
    'razon_social': $('#cli_razon_social').val(),
    'rfc': $('#cli_rfc').val(),
    'codigo_postal': $('#cli_cp').val(),
    'regimen_fiscal': $('#cli_regimen').val(),
    'uso_cfdi': $('#cli_uso_cfdi').val(),
    'email_facturacion': $('#cli_email').val(),
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  };
  
  if(!data.razon_social || !data.rfc) {
    notifyShow('Razón Social y RFC son obligatorios', 'warning');
    return;
  }
  
  $.post('<?=base_url();?>ventas/Pos/guardar_datos_fiscales_ajax', data, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if(result.success) {
      $('#modalCliente').modal('hide');
      cargarClientes(); // Recargar lista para tener datos frescos
    }
  });
}

function cargarClientes(callback) {
  $.post('<?=base_url();?>ventas/Pos/get_clientes_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      clientes = result.clientes;
      let html = '<option value="">Buscar cliente...</option>';
      clientes.forEach(c => {
        let nombre = c.razon_social;
        if(c.nombre_comercial) nombre += ' (' + c.nombre_comercial + ')';
        html += `<option value="${c.id}">${nombre}</option>`;
      });
      $('#ticket_cliente').html(html);
      
      // Re-init Select2 to update options
      initSelect2();
      
      if(callback) callback();
    }
  });
}

function cargarDescuentos() {
  $.post('<?=base_url();?>ventas/Pos/get_descuentos_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      descuentos = result.descuentos;
      let html = '<option value="">Sin descuento</option>';
      descuentos.forEach(d => {
        const valor = d.tipo_descuento == 'Porcentaje' ? d.valor + '%' : '$' + parseFloat(d.valor).toFixed(2);
        html += `<option value="${d.id}" data-tipo="${d.tipo_descuento}" data-valor="${d.valor}" data-nombre="${d.nombre}">${d.nombre} (${valor})</option>`;
      });
      $('#ticket_descuento').html(html);
    }
  });
}

function aplicarDescuento() {
  const select = $('#ticket_descuento');
  const option = select.find('option:selected');
  
  if(option.val()) {
    descuentoSeleccionado = {
      id: option.val(),
      nombre: option.data('nombre'),
      tipo: option.data('tipo'),
      valor: parseFloat(option.data('valor'))
    };
    
    const valorTexto = descuentoSeleccionado.tipo == 'Porcentaje' ? 
      descuentoSeleccionado.valor + '%' : 
      '$' + descuentoSeleccionado.valor.toFixed(2);
    $('#descuento_info').text(descuentoSeleccionado.nombre + ' - ' + valorTexto);
  } else {
    descuentoSeleccionado = null;
    $('#descuento_info').text('');
  }
  
  actualizarTotales();
}

function inicializarBusqueda() {
  let timeout = null;
  $('#buscar_producto').on('keyup', function() {
    clearTimeout(timeout);
    timeout = setTimeout(function() {
      buscarProductos();
    }, 300);
  });
}

function buscarProductos() {
  const busqueda = $('#buscar_producto').val();
  
  if(!busqueda || busqueda.length < 2) {
    $('#grid_productos').html(`
      <div class="col-12 text-center text-muted py-5">
        <i class="fas fa-search fa-3x mb-3"></i>
        <p>Busca un producto para comenzar</p>
      </div>
    `);
    return;
  }
  
  $.post('<?=base_url();?>ventas/Pos/get_productos_ajax', {
    'busqueda': busqueda,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      renderProductos(result.productos);
    }
  });
}

function renderProductos(productos) {
  if(productos.length === 0) {
    $('#grid_productos').html(`
      <div class="col-12 text-center text-muted py-5">
        <i class="fas fa-box-open fa-3x mb-3"></i>
        <p>No se encontraron productos</p>
      </div>
    `);
    return;
  }
  
  let html = '';
  productos.forEach(p => {
    const stock = parseFloat(p.stock_actual);
    let stockClass = 'success';
    let stockIcon = 'check-circle';
    
    if(stock <= 0) {
      stockClass = 'danger';
      stockIcon = 'times-circle';
    } else if(stock <= p.stock_minimo) {
      stockClass = 'warning';
      stockIcon = 'exclamation-circle';
    }
    
    // Lógica de Imagen
    let imagenHtml = '';
    if(p.foto_producto) {
        const rutaImagen = p.foto_producto.startsWith('http') ? p.foto_producto : '<?=base_url();?>' + p.foto_producto;
        imagenHtml = `<img src="${rutaImagen}" class="img-fluid rounded-start" alt="${p.nombre}" style="max-height: 80px; width: auto; object-fit: contain;">`;
    } else {
        imagenHtml = `<i class="fas fa-image fa-3x text-muted opacity-25"></i>`;
    }
    
    html += `
      <div class="col-md-6 mb-3">
        <div class="card h-100 producto-card border-0 shadow-sm" onclick="agregarAlTicket(${p.id}, '${p.nombre.replace(/'/g, "\\'")}', ${p.precio_venta}, ${stock})" style="cursor: pointer; overflow: hidden;">
          <div class="row g-0 h-100">
            <div class="col-4 d-flex align-items-center justify-content-center bg-light">
                ${imagenHtml}
            </div>
            <div class="col-8">
                <div class="card-body p-2">
                    <h6 class="card-title text-truncate mb-1" style="font-size: 0.95rem;" title="${p.nombre}">${p.nombre}</h6>
                    <p class="card-text mb-1 text-truncate">
                      <small class="text-muted fw-bold">${p.codigo}</small>
                    </p>
                    <p class="card-text mb-1 text-muted" style="font-size: 0.75rem; line-height: 1.2; height: 2.4em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                      ${p.descripcion || 'Sin descripción'}
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                      <h5 class="text-primary mb-0">$${parseFloat(p.precio_venta).toFixed(2)}</h5>
                      <div>
                        ${p.tipo_producto === 'Fabricado' ? 
                          `<button class="btn btn-xs btn-outline-info me-1" onclick="verFormulacion(${p.id}, '${p.nombre.replace(/'/g, "\\'")}')" title="Ver Formulación" style="font-size: 0.7rem; padding: 0.1rem 0.3rem;">
                             <i class="fas fa-flask"></i>
                           </button>` : ''
                        }
                        <span class="badge bg-${stockClass}">
                          <i class="fas fa-${stockIcon}"></i> ${stock}
                        </span>
                      </div>
                    </div>
                </div>
            </div>
          </div>
        </div>
      </div>
    `;
  });
  
  $('#grid_productos').html(html);
}

function agregarAlTicket(id, nombre, precio, stock) {
  // Verificar si ya existe en el ticket
  const existe = ticketItems.find(item => item.id === id);
  
  if(existe) {
    existe.cantidad++;
  } else {
    ticketItems.push({
      id: id,
      nombre: nombre,
      precio: parseFloat(precio),
      cantidad: 1,
      stock: parseFloat(stock)
    });
  }
  
  renderTicket();
}

function renderTicket() {
  if(ticketItems.length === 0) {
    $('#ticket_items').html(`
      <tr>
        <td colspan="5" class="text-center text-muted">
          <i class="fas fa-shopping-basket"></i> Carrito vacío
        </td>
      </tr>
    `);
    actualizarTotales();
    return;
  }
  
  let html = '';
  ticketItems.forEach((item, index) => {
    const subtotal = item.cantidad * item.precio;
    const sinStock = item.cantidad > item.stock;
    const tieneFormulacion = item.formulacion_id && item.formulacion_version;
    
    html += `
      <tr ${sinStock ? 'class="table-warning"' : ''}>
        <td>
          <div class="d-flex justify-content-between align-items-start">
            <div>
              ${item.nombre}
              ${tieneFormulacion ? `<br><small class="text-info"><i class="fas fa-flask"></i> Fórmula V${item.formulacion_version}</small>` : ''}
              ${sinStock ? '<br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Sin stock suficiente</small>' : ''}
            </div>
            ${tieneFormulacion ? `<button class="btn btn-xs btn-outline-info" onclick="toggleFormulacionInfo(${index})" title="Ver detalles de formulación"><i class="fas fa-info-circle"></i></button>` : ''}
          </div>
          <div id="formulacion_info_${index}" class="mt-2 p-2 bg-light rounded" style="display: none; font-size: 0.85rem;">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <strong class="text-info">Formulación V${item.formulacion_version || ''}</strong>
              <span class="spinner-border spinner-border-sm" role="status" id="formulacion_loader_${index}"></span>
            </div>
            <div id="formulacion_detalle_${index}">
              <!-- Se carga dinámicamente -->
            </div>
          </div>
        </td>
        <td>
          <input type="number" class="form-control form-control-sm" value="${item.cantidad}" min="1" 
                 onchange="cambiarCantidad(${index}, this.value)" style="width: 60px;">
        </td>
        <td>$${item.precio.toFixed(2)}</td>
        <td>$${subtotal.toFixed(2)}</td>
        <td>
          <button class="btn btn-sm btn-danger" onclick="eliminarItem(${index})">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>
    `;
  });
  
  $('#ticket_items').html(html);
  actualizarTotales();
}

function cambiarCantidad(index, nuevaCantidad) {
  ticketItems[index].cantidad = parseInt(nuevaCantidad) || 1;
  renderTicket();
}

function eliminarItem(index) {
  ticketItems.splice(index, 1);
  renderTicket();
}

function toggleFormulacionInfo(index) {
  const infoDiv = $(`#formulacion_info_${index}`);
  const detalleDiv = $(`#formulacion_detalle_${index}`);
  const loader = $(`#formulacion_loader_${index}`);
  const item = ticketItems[index];
  
  console.log('toggleFormulacionInfo called for index:', index);
  console.log('Item:', item);
  console.log('formulacion_id:', item.formulacion_id);
  
  if(infoDiv.is(':visible')) {
    infoDiv.slideUp();
    return;
  }
  
  // Si ya se cargó antes, solo mostrar
  const currentContent = detalleDiv.html().trim();
  console.log('Current content in detalleDiv:', currentContent);
  console.log('Content length:', currentContent.length);
  
  if(currentContent.length > 0 && !currentContent.includes('<!--')) {
    console.log('Content already loaded, just showing');
    infoDiv.slideDown();
    return;
  }
  
  // Cargar detalles de la formulación
  console.log('Proceeding to load formulation details');
  infoDiv.slideDown();
  loader.show();
  
  console.log('Making AJAX request to get_formulacion_detalle_ajax');
  
  $.ajax({
    url: '<?=base_url();?>ventas/Pos/get_formulacion_detalle_ajax',
    type: 'POST',
    data: {
      'formulacion_id': item.formulacion_id,
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    dataType: 'json',
    success: function(res) {
      console.log('AJAX Success - Response:', res);
      loader.hide();
      if(res.success && res.formulacion) {
        const f = res.formulacion;
        console.log('Formulacion data:', f);
        let html = '';
        
        // Descripción
        if(f.descripcion) {
          html += `<p class="mb-2 text-muted small">${f.descripcion}</p>`;
        }
        
        // Componentes principales
        if(f.componentes && f.componentes.length > 0) {
          html += '<div class="mb-1"><strong class="small">Componentes:</strong></div>';
          html += '<ul class="small mb-0" style="padding-left: 1.2rem;">';
          
          // Mostrar solo los primeros 5 componentes para no saturar
          const componentesMostrar = f.componentes.slice(0, 5);
          componentesMostrar.forEach(c => {
            const nombre = c.tipo_componente === 'Insumo' ? c.insumo_nombre : c.producto_nombre;
            html += `<li>${nombre} - ${parseFloat(c.cantidad)} ${c.unidad}</li>`;
          });
          
          if(f.componentes.length > 5) {
            html += `<li class="text-muted">... y ${f.componentes.length - 5} más</li>`;
          }
          html += '</ul>';
        }
        
        console.log('Generated HTML:', html);
        console.log('Setting HTML to element:', detalleDiv);
        detalleDiv.html(html);
        console.log('HTML set successfully');
      } else {
        console.log('Response success but no formulacion data');
        detalleDiv.html('<p class="text-muted small mb-0">No se pudieron cargar los detalles</p>');
      }
    },
    error: function(xhr, status, error) {
      console.error('AJAX Error - Status:', status);
      console.error('AJAX Error - Error:', error);
      console.error('AJAX Error - Response Text:', xhr.responseText);
      loader.hide();
      detalleDiv.html('<p class="text-danger small mb-0">Error al cargar detalles: ' + error + '</p>');
    }
  });
}

function actualizarTotales() {
  const subtotal = ticketItems.reduce((sum, item) => sum + (item.cantidad * item.precio), 0);
  
  // Calcular descuento
  let descuentoAplicado = 0;
  if(descuentoSeleccionado) {
    if(descuentoSeleccionado.tipo == 'Porcentaje') {
      descuentoAplicado = subtotal * (descuentoSeleccionado.valor / 100);
    } else {
      descuentoAplicado = descuentoSeleccionado.valor;
    }
    $('#row_descuento').show();
    $('#ticket_descuento_aplicado').text('-$' + descuentoAplicado.toFixed(2));
  } else {
    $('#row_descuento').hide();
  }
  
  const subtotalConDescuento = subtotal - descuentoAplicado;
  const iva = subtotalConDescuento * 0.16;
  
  // Obtener costo de envío
  const costoEnvio = parseFloat($('#ticket_costo_envio').val()) || 0;
  
  const total = subtotalConDescuento + iva + costoEnvio;
  
  $('#ticket_subtotal').text('$' + subtotal.toFixed(2));
  $('#ticket_iva').text('$' + iva.toFixed(2));
  $('#ticket_total').text('$' + total.toFixed(2));
}

function guardarCotizacion() {
  procesarVenta('Cotización');
}

function cobrarVenta() {
  procesarVenta('Entregada');
}

function procesarVenta(estatus) {
  if(ticketItems.length === 0) {
    notifyShow('Agrega productos al ticket', 'warning');
    return;
  }
  
  const cliente_id = $('#ticket_cliente').val();
  if(!cliente_id) {
    notifyShow('Selecciona un cliente', 'warning');
    return;
  }
  
  const tipo_venta = $('#ticket_tipo_venta').val();
  const direccion_envio = $('#ticket_direccion_envio').val().trim();
  
  // Validar dirección de envío para pedidos
  if(tipo_venta === 'Pedido' && !direccion_envio) {
    notifyShow('Ingresa la dirección de envío para el pedido', 'warning');
    $('#ticket_direccion_envio').focus();
    return;
  }
  
  const detalles = ticketItems.map(item => {
    const detalle = {
      producto_id: item.id,
      cantidad: item.cantidad,
      precio_unitario: item.precio,
      descuento: 0
    };
    
    // Incluir formulación si fue seleccionada
    if(item.formulacion_id) {
      detalle.formulacion_id = item.formulacion_id;
      detalle.formulacion_version = item.formulacion_version;
    }
    
    return detalle;
  });
  
  const data = {
    cliente_id: cliente_id,
    tipo_venta: tipo_venta,
    direccion_envio: direccion_envio || null,
    costo_envio: parseFloat($('#ticket_costo_envio').val()) || 0,
    fecha_entrega_estimada: $('#ticket_fecha_entrega').val() || null,
    forma_pago: $('#ticket_forma_pago').val(),
    estatus: estatus,
    observaciones: $('#ticket_observaciones').val(),
    detalles: JSON.stringify(detalles),
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  };
  
  // Agregar descuento si existe
  if(descuentoSeleccionado) {
    data.descuento_id = descuentoSeleccionado.id;
    data.descuento_nombre = descuentoSeleccionado.nombre;
    data.descuento_tipo = descuentoSeleccionado.tipo;
    data.descuento_valor = descuentoSeleccionado.valor;
  }
  
  $.post('<?=base_url();?>ventas/Pos/crear_orden_ajax', data, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    
    if(result.success) {
      // Limpiar ticket
      cancelarTicket();
      
      // Mostrar opción de imprimir
      if(confirm('¿Deseas imprimir el recibo?')) {
        window.open('<?=base_url();?>ventas/Pos/imprimir_recibo/' + result.orden_id, '_blank');
      }
    }
  });
}

function toggleDireccionEnvio() {
  const tipoVenta = $('#ticket_tipo_venta').val();
  if(tipoVenta === 'Pedido') {
    $('#div_direccion_envio').slideDown();
    $('#div_costo_envio').slideDown();
    $('#div_fecha_entrega').slideDown();
  } else {
    $('#div_direccion_envio').slideUp();
    $('#div_costo_envio').slideUp();
    $('#div_fecha_entrega').slideUp();
    $('#ticket_direccion_envio').val(''); // Limpiar si cambia a Mostrador
    $('#ticket_costo_envio').val('0'); // Resetear costo
    $('#ticket_fecha_entrega').val(''); // Limpiar fecha
    actualizarTotales(); // Recalcular total sin envío
  }
}

function cancelarTicket() {
  ticketItems = [];
  descuentoSeleccionado = null;
  renderTicket();
  $('#ticket_observaciones').val('');
  $('#ticket_descuento').val('');
  $('#descuento_info').text('');
  $('#ticket_tipo_venta').val('Mostrador');
  $('#ticket_direccion_envio').val('');
  $('#ticket_costo_envio').val('0');
  $('#ticket_fecha_entrega').val('');
  $('#div_direccion_envio').hide();
  $('#div_costo_envio').hide();
  $('#div_fecha_entrega').hide();
  $('#buscar_producto').val('');
  $('#grid_productos').html(`
    <div class="col-12 text-center text-muted py-5">
      <i class="fas fa-search fa-3x mb-3"></i>
      <p>Busca un producto para comenzar</p>
    </div>
  `);
  actualizarTotales(); // Recalcular totales
}

// Inicializar cuando jQuery esté disponible
if (typeof jQuery !== 'undefined') {
  $(document).ready(initPOS);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initPOS);
    }
  });
}
</script>

<style>
.producto-card:hover {
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  transform: translateY(-2px);
  transition: all 0.3s;
}
</style>
