<?php
/**
 * Vista principal de Órdenes de Compra
 * Listado de órdenes con DataTables
 */
?>
<?php $this->load->helper('permissions'); ?>
<div class="container-fluid p-0 compras-page">

  <!-- Breadcrumb -->
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
   
  <!-- Titulo de la pagina -->
  <h1 class="h3 mb-3"><?php echo $headTitle;?></h1>

  <!-- Cards de estadísticas -->
  <div class="row">
    <!-- Total Órdenes -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Total Órdenes</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['total_ordenes']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-file-invoice text-primary" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Órdenes en sistema</small>
        </div>
      </div>
    </div>

    <!-- Órdenes Pendientes -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Pendientes</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['ordenes_pendientes']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-clock text-warning" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Por recibir</small>
        </div>
      </div>
    </div>

    <!-- Recibidas Este Mes -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Recibidas (Mes)</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-12">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['recibidas_mes']; ?>
              </h3>
            </div>
          </div>
          <small class="text-muted">Órdenes completadas</small>
        </div>
      </div>
    </div>

    <!-- Total Gastado Este Mes -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Gasto (Mes)</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-12">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                $<?php echo number_format($response['stats']['total_mes'], 2); ?>
              </h3>
            </div>
          </div>
          <small class="text-muted">Total en compras</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Pre-órdenes pendientes de autorización -->
  <div class="row mb-3">
    <div class="col-12">
      <div class="card border-warning">
        <div class="card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">
            <i class="fas fa-clipboard-list text-warning me-1"></i> Pre-órdenes Pendientes de Autorización
            <span class="badge bg-warning text-dark ms-2" id="badgeConteoPreordenes">0</span>
          </h5>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cargarPreordenesPendientes()" title="Actualizar lista">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="tablaPreordenes">
              <thead class="table-light">
                <tr>
                  <th>Folio</th>
                  <th>Insumo</th>
                  <th>Cantidad</th>
                  <th>Proveedor sugerido</th>
                  <th>Fecha solicitud</th>
                  <th>Notas</th>
                  <th class="text-end" style="min-width:180px;">Acciones</th>
                </tr>
              </thead>
              <tbody id="tbodyPreordenes">
                <tr id="filaPreordenesCargando">
                  <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin me-1"></i> Cargando pre-órdenes...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Historial de pre-órdenes -->
  <div class="row mb-3">
    <div class="col-12">
      <div class="card border-secondary">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="card-title mb-0">
            <i class="fas fa-history text-secondary me-1"></i> Historial de Pre-órdenes
          </h5>
          <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cargarHistorialPreordenes()" title="Actualizar historial">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0" id="tablaHistorialPreordenes">
              <thead class="table-light">
                <tr>
                  <th>Folio</th>
                  <th>Insumo</th>
                  <th>Cantidad</th>
                  <th>Estatus</th>
                  <th>OC generada</th>
                  <th>Fecha</th>
                  <th class="text-end">Acciones</th>
                </tr>
              </thead>
              <tbody id="tbodyHistorialPreordenes">
                <tr>
                  <td colspan="7" class="text-center text-muted py-3">
                    <i class="fas fa-spinner fa-spin me-1"></i> Cargando historial...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabs: Órdenes / Reporte -->
  <ul class="nav nav-tabs mb-3" id="tabsOrdenesCompra" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="tab-ordenes-btn" data-bs-toggle="tab" data-bs-target="#panelOrdenes" type="button" role="tab">
        <i class="fas fa-list me-1"></i> Órdenes de Compra
      </button>
    </li>
    <?php if (tiene_permiso('reportes_compras') || tiene_permiso('compras_ordenes_consult')): ?>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="tab-reporte-btn" data-bs-toggle="tab" data-bs-target="#panelReporte" type="button" role="tab" onclick="cargarReporteCompras()">
        <i class="fas fa-chart-bar me-1"></i> Reporte de Compras
      </button>
    </li>
    <?php endif; ?>
  </ul>

  <div class="tab-content" id="tabsOrdenesCompraContent">
  <div class="tab-pane fade show active" id="panelOrdenes" role="tabpanel">
  <!-- Tabla de órdenes -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Órdenes de Compra</h5>
            <?php if (tiene_permiso('compras_ordenes_add')): ?>
            <button class="btn btn-primary btn-sm" onclick="mostrarModalNuevo()">
              <i class="fas fa-plus"></i> Nueva Orden
            </button>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-body">
          <!-- Filtros -->
          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">Estatus entrega</label>
              <select class="form-select form-select-sm" id="filtroEstatus">
                <option value="">Todos</option>
                <option value="Borrador">Borrador</option>
                <option value="Enviada">Enviada</option>
                <option value="Confirmada">Confirmada</option>
                <option value="En Tránsito">En Tránsito</option>
                <option value="Recibida Parcial">Recibida Parcial</option>
                <option value="Recibida">Recibida</option>
                <option value="Cancelada">Cancelada</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Estatus pago</label>
              <select class="form-select form-select-sm" id="filtroEstatusPago">
                <option value="">Todos</option>
                <option value="Pendiente">Con adeudo</option>
                <option value="Parcial">Pago parcial</option>
                <option value="Pagado">Pagado</option>
                <option value="Sin adeudo">Sin adeudo</option>
              </select>
            </div>
          </div>

          <!-- DataTable -->
          <table id="tablaOrdenes" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Total</th>
                <th>Pago</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>              
            </tbody>
            <tfoot>
              <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Total</th>
                <th>Pago</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
  </div><!-- /panelOrdenes -->

  <!-- Panel Reporte de Compras -->
  <div class="tab-pane fade" id="panelReporte" role="tabpanel">
    <div class="row mb-3">
      <div class="col-md-3">
        <label class="form-label" for="reporte_periodo">Periodo</label>
        <select class="form-select form-select-sm" id="reporte_periodo" onchange="toggleRangoPersonalizado()">
          <option value="mes">Mes actual</option>
          <option value="trimestre">Trimestre actual</option>
          <option value="personalizado">Personalizado</option>
        </select>
      </div>
      <div class="col-md-2 rango-personalizado" style="display:none;">
        <label class="form-label" for="reporte_fecha_inicio">Desde</label>
        <input type="date" class="form-control form-control-sm" id="reporte_fecha_inicio">
      </div>
      <div class="col-md-2 rango-personalizado" style="display:none;">
        <label class="form-label" for="reporte_fecha_fin">Hasta</label>
        <input type="date" class="form-control form-control-sm" id="reporte_fecha_fin">
      </div>
      <div class="col-md-5 d-flex align-items-end gap-2">
        <button type="button" class="btn btn-primary btn-sm" onclick="cargarReporteCompras()">
          <i class="fas fa-sync-alt me-1"></i> Actualizar
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" onclick="exportarReporteCsv()">
          <i class="fas fa-file-csv me-1"></i> Exportar CSV
        </button>
      </div>
    </div>

    <div class="row mb-3" id="reporteCards">
      <div class="col-md-4">
        <div class="card border-success">
          <div class="card-body">
            <h6 class="text-muted mb-1">Monto gastado</h6>
            <h3 class="mb-0 text-success" id="reporte_monto_gastado">$0.00</h3>
            <small class="text-muted">OC Recibida / Recibida Parcial</small>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-primary">
          <div class="card-body">
            <h6 class="text-muted mb-1">OC recibidas</h6>
            <h3 class="mb-0 text-primary" id="reporte_oc_recibidas">0</h3>
            <small class="text-muted">Completadas en el periodo</small>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-warning">
          <div class="card-body">
            <h6 class="text-muted mb-1">OC pendientes</h6>
            <h3 class="mb-0 text-warning" id="reporte_oc_pendientes">0</h3>
            <small class="text-muted">Por recibir</small>
          </div>
        </div>
      </div>
    </div>

    <p class="text-muted small mb-2" id="reporte_rango_label">—</p>

    <div class="row">
      <div class="col-lg-5 mb-3">
        <div class="card h-100">
          <div class="card-header"><h6 class="mb-0">Desglose por proveedor</h6></div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0" id="tablaReporteProveedores">
                <thead class="table-light">
                  <tr><th>Proveedor</th><th class="text-center">OC</th><th class="text-end">Monto</th></tr>
                </thead>
                <tbody id="tbodyReporteProveedores">
                  <tr><td colspan="3" class="text-center text-muted py-3">Seleccione un periodo y pulse Actualizar</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-7 mb-3">
        <div class="card h-100">
          <div class="card-header"><h6 class="mb-0">Órdenes del periodo</h6></div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-sm table-hover mb-0" id="tablaReporteOrdenes">
                <thead class="table-light">
                  <tr><th>Folio</th><th>Fecha</th><th>Proveedor</th><th class="text-end">Total</th><th>Estatus</th></tr>
                </thead>
                <tbody id="tbodyReporteOrdenes">
                  <tr><td colspan="5" class="text-center text-muted py-3">—</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div><!-- /panelReporte -->
  </div><!-- /tab-content -->
</div>

<!-- Modal: Simulación de correo al proveedor -->
<div class="modal fade" id="modalSimularCorreo" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-envelope me-1"></i> Enviar solicitud (simulación)</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label text-muted small mb-0">Destinatario</label>
          <div class="fw-semibold" id="correo_destinatario">—</div>
        </div>
        <div class="mb-3">
          <label class="form-label text-muted small mb-0">Asunto</label>
          <div class="fw-semibold" id="correo_asunto">—</div>
        </div>
        <div class="mb-3">
          <label class="form-label text-muted small mb-0">Cuerpo del mensaje</label>
          <div class="border rounded p-3 bg-light" id="correo_cuerpo_html" style="max-height:360px;overflow:auto;"></div>
        </div>
        <textarea class="form-control d-none" id="correo_cuerpo_texto" rows="8" readonly></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" onclick="copiarCorreoSimulado()">
          <i class="fas fa-copy me-1"></i> Copiar
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Nueva/Editar Orden -->
<div class="modal fade" id="modalOrden" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalOrdenTitle">Nueva Orden de Compra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formOrden">
          <input type="hidden" name="id" id="orden_id">

          <div class="alert alert-light border mb-3" id="alertPasosNuevaOC">
            <strong><i class="fas fa-list-ol me-1"></i> Pasos para crear una OC:</strong>
            <ol class="mb-0 small ps-3 mt-1">
              <li>Seleccione un <strong>proveedor</strong> (debe tener insumos vinculados)</li>
              <li>Clic en <strong>Agregar Insumo</strong>, elija producto, cantidad y precio</li>
              <li><strong>Guardar Borrador</strong> y luego use el botón ✓ verde para aprobar/enviar</li>
            </ol>
          </div>
          
          <div class="row">
            <!-- Columna Izquierda -->
            <div class="col-md-6">
              <h6 class="mb-3 text-primary">Información General</h6>
              
              <div class="mb-3">
                <label class="form-label">Folio</label>
                <input type="text" class="form-control" id="orden_folio" readonly placeholder="Auto-generado">
              </div>

              <div class="mb-3">
                <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                <select class="form-select" name="proveedor_id" id="orden_proveedor_id" required onchange="cargarInsumosProveedor()">
                  <option value="">-- Seleccionar --</option>
                </select>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Fecha de Orden <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" name="fecha_orden" id="orden_fecha_orden" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Fecha Entrega Estimada</label>
                  <input type="date" class="form-control" name="fecha_entrega_estimada" id="orden_fecha_entrega_estimada">
                </div>
              </div>
            </div>

            <!-- Columna Derecha -->
            <div class="col-md-6">
              <h6 class="mb-3 text-primary">Condiciones de Pago</h6>

              <div class="mb-3">
                <label class="form-label">Forma de Pago</label>
                <select class="form-select" name="forma_pago" id="orden_forma_pago">
                  <option value="Transferencia">Transferencia</option>
                  <option value="Efectivo">Efectivo</option>
                  <option value="Cheque">Cheque</option>
                  <option value="Crédito">Crédito</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Condiciones de Pago</label>
                <textarea class="form-control" name="condiciones_pago" id="orden_condiciones_pago" rows="2" placeholder="Ej: 50% anticipo, 50% contra entrega"></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea class="form-control" name="observaciones" id="orden_observaciones" rows="2"></textarea>
              </div>
            </div>
          </div>

          <hr>

          <!-- Sección de Detalles -->
          <h6 class="mb-3 text-primary">Detalle de Insumos</h6>
          
          <div class="mb-3">
            <button type="button" class="btn btn-success btn-sm" onclick="mostrarFormAgregarDetalle()" id="btnAgregarDetalle" disabled>
              <i class="fas fa-plus"></i> Agregar Insumo
            </button>
          </div>

          <!-- Formulario agregar detalle (oculto) -->
          <div id="formAgregarDetalle" style="display:none;">
            <div class="card mb-3">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4">
                    <label class="form-label">Insumo <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" id="detalle_insumo_id">
                      <option value="">-- Seleccionar --</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm" id="detalle_cantidad" step="0.01" min="0.01" onchange="calcularSubtotalDetalle()">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Precio Unit. <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm" id="detalle_precio" step="0.01" min="0.01" onchange="calcularSubtotalDetalle()">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Subtotal</label>
                    <input type="text" class="form-control form-control-sm" id="detalle_subtotal" readonly>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                      <button type="button" class="btn btn-success btn-sm" onclick="agregarDetalleATabla()">
                        <i class="fas fa-check"></i>
                      </button>
                      <button type="button" class="btn btn-secondary btn-sm" onclick="cancelarAgregarDetalle()">
                        <i class="fas fa-times"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabla de detalles -->
          <div class="table-responsive">
            <table class="table table-sm table-hover" id="tablaDetalles">
              <thead class="table-light">
                <tr>
                  <th>Código</th>
                  <th>Insumo</th>
                  <th>Cantidad</th>
                  <th>Precio Unit.</th>
                  <th>Subtotal</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr id="noDetalles">
                  <td colspan="6" class="text-center text-muted">No hay insumos agregados</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Totales -->
          <div class="row">
            <div class="col-md-8"></div>
            <div class="col-md-4">
              <table class="table table-sm">
                <tr>
                  <td class="text-end"><strong>Subtotal:</strong></td>
                  <td class="text-end" id="orden_subtotal_display">$0.00</td>
                </tr>
                <tr>
                  <td class="text-end"><strong>IVA (16%):</strong></td>
                  <td class="text-end" id="orden_iva_display">$0.00</td>
                </tr>
                <tr class="table-primary">
                  <td class="text-end"><strong>TOTAL:</strong></td>
                  <td class="text-end"><strong id="orden_total_display">$0.00</strong></td>
                </tr>
              </table>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarOrden()">
          <i class="fas fa-save"></i> Guardar Borrador
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Recibir Mercancía -->
<div class="modal fade" id="modalRecibir" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Recibir Mercancía: <span id="folioRecibir"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="recibir_orden_id">
        
        <div class="alert alert-info">
          <i class="fas fa-info-circle"></i> Ingrese las cantidades recibidas. Si recibe menos de lo solicitado, la orden quedará como "Recibida Parcial".
        </div>

        <!-- Tabla de detalles para recibir -->
        <div class="table-responsive">
          <table class="table table-sm" id="tablaRecibirDetalles">
            <thead class="table-light">
              <tr>
                <th>Insumo</th>
                <th>Solicitada</th>
                <th>Ya Recibida</th>
                <th>Recibir Ahora</th>
                <th>Pendiente</th>
              </tr>
            </thead>
            <tbody>
              <!-- Se llena dinámicamente -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" onclick="guardarRecepcion()">
          <i class="fas fa-truck-loading"></i> Confirmar Recepción
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Consultar pre-orden -->
<div class="modal fade" id="modalDetallePreorden" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning bg-opacity-25">
        <h5 class="modal-title"><i class="fas fa-clipboard-list me-1"></i> Pre-orden: <span id="detalle_preorden_folio">—</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="detalle_preorden_id">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label text-muted small mb-0">Insumo</label>
            <div class="fw-semibold" id="detalle_preorden_insumo">—</div>
          </div>
          <div class="col-md-3">
            <label class="form-label text-muted small mb-0">Cantidad solicitada</label>
            <div id="detalle_preorden_cantidad">—</div>
          </div>
          <div class="col-md-3">
            <label class="form-label text-muted small mb-0">Estatus</label>
            <div id="detalle_preorden_estatus">—</div>
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted small mb-0">Proveedor sugerido</label>
            <div id="detalle_preorden_proveedor">—</div>
          </div>
          <div class="col-md-3">
            <label class="form-label text-muted small mb-0">Stock actual</label>
            <div id="detalle_preorden_stock">—</div>
          </div>
          <div class="col-md-3">
            <label class="form-label text-muted small mb-0">Precio promedio</label>
            <div id="detalle_preorden_precio">—</div>
          </div>
          <div class="col-md-4">
            <label class="form-label text-muted small mb-0">Origen</label>
            <div id="detalle_preorden_origen">—</div>
          </div>
          <div class="col-md-4">
            <label class="form-label text-muted small mb-0">Solicitó</label>
            <div id="detalle_preorden_solicita">—</div>
          </div>
          <div class="col-md-4">
            <label class="form-label text-muted small mb-0">Fecha solicitud</label>
            <div id="detalle_preorden_fecha">—</div>
          </div>
          <div class="col-md-6" id="wrap_detalle_oc_generada" style="display:none;">
            <label class="form-label text-muted small mb-0">OC generada</label>
            <div id="detalle_preorden_oc">—</div>
          </div>
          <div class="col-md-6" id="wrap_detalle_motivo_rechazo" style="display:none;">
            <label class="form-label text-muted small mb-0">Motivo rechazo</label>
            <div id="detalle_preorden_motivo" class="text-danger">—</div>
          </div>
          <div class="col-12">
            <label class="form-label text-muted small mb-0">Notas</label>
            <div id="detalle_preorden_notas" class="border rounded p-2 bg-light small">—</div>
          </div>
        </div>
      </div>
      <div class="modal-footer" id="footerDetallePreorden">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Editar pre-orden -->
<div class="modal fade" id="modalEditarPreorden" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-edit me-1"></i> Editar pre-orden: <span id="editar_preorden_folio">—</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editar_preorden_id">
        <p class="mb-2 text-muted small" id="editar_preorden_insumo_label">—</p>
        <div class="mb-3">
          <label class="form-label" for="editar_preorden_cantidad">Cantidad solicitada</label>
          <input type="number" class="form-control" id="editar_preorden_cantidad" step="0.0001" min="0.0001">
          <small class="text-muted">Unidad: <span id="editar_preorden_unidad">—</span></small>
        </div>
        <div class="mb-3">
          <label class="form-label" for="editar_preorden_proveedor_id">Proveedor sugerido</label>
          <select class="form-select" id="editar_preorden_proveedor_id">
            <option value="">— Sin proveedor —</option>
          </select>
          <small class="text-muted">Solo proveedores vinculados a este insumo</small>
        </div>
        <div class="mb-3">
          <label class="form-label" for="editar_preorden_notas">Notas</label>
          <textarea class="form-control" id="editar_preorden_notas" rows="3" placeholder="Observaciones para el área de compras"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnGuardarPreorden" onclick="guardarEdicionPreorden()">
          <i class="fas fa-save me-1"></i> Guardar cambios
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Autorizar pre-orden -->
<div class="modal fade" id="modalAutorizarPreorden" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-check-circle me-1"></i> Autorizar pre-orden</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="autorizar_preorden_id">
        <input type="hidden" id="autorizar_insumo_id">
        <p class="mb-2"><strong id="autorizar_insumo_label">—</strong></p>
        <div class="mb-3">
          <label class="form-label" for="autorizar_cantidad">Cantidad aprobada</label>
          <input type="number" class="form-control" id="autorizar_cantidad" step="0.0001" min="0.0001">
          <small class="text-muted">Unidad: <span id="autorizar_unidad">—</span></small>
        </div>
        <div class="mb-3">
          <label class="form-label" for="autorizar_proveedor_id">Proveedor</label>
          <select class="form-select" id="autorizar_proveedor_id">
            <option value="">— Seleccionar —</option>
          </select>
          <small class="text-muted">Proveedores vinculados al insumo</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnConfirmarAutorizarPreorden" onclick="confirmarAutorizarPreorden()">
          <i class="fas fa-check me-1"></i> Autorizar y crear OC
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Rechazar pre-orden -->
<div class="modal fade" id="modalRechazarPreorden" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fas fa-times-circle me-1"></i> Rechazar pre-orden</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="rechazar_preorden_id">
        <p class="text-muted small mb-2">Pre-orden: <strong id="rechazar_folio_label">—</strong></p>
        <div class="mb-3">
          <label class="form-label" for="rechazar_motivo">Motivo del rechazo <span class="text-danger">*</span></label>
          <textarea class="form-control" id="rechazar_motivo" rows="3" required placeholder="Indique el motivo del rechazo"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmarRechazarPreorden" onclick="confirmarRechazarPreorden()">
          <i class="fas fa-ban me-1"></i> Rechazar pre-orden
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Documentos y comentarios -->
<div class="modal fade" id="modalGestionarOrden" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-secondary text-white">
        <h5 class="modal-title"><i class="fas fa-paperclip me-1"></i> OC: <span id="gestionar_folio"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="gestionar_orden_id">

        <ul class="nav nav-tabs mb-3" role="tablist">
          <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabGestionComentarios" type="button">Comentarios</button></li>
          <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabGestionDocumentos" type="button">Documentos</button></li>
        </ul>

        <div class="tab-content">
          <div class="tab-pane fade show active" id="tabGestionComentarios">
            <div class="mb-3">
              <label class="form-label">Nuevo comentario</label>
              <textarea class="form-control" id="gestionar_comentario_nuevo" rows="2" placeholder="Seguimiento, acuerdos con proveedor, incidencias..."></textarea>
              <button type="button" class="btn btn-primary btn-sm mt-2" onclick="agregarComentarioOC()">
                <i class="fas fa-comment-dots"></i> Agregar comentario
              </button>
            </div>
            <div id="listaComentariosOC" class="border rounded p-2 bg-light" style="max-height:280px;overflow:auto;">
              <div class="text-muted small text-center py-3">Cargando...</div>
            </div>
          </div>

          <div class="tab-pane fade" id="tabGestionDocumentos">
            <form id="formDocumentoOC" class="border rounded p-3 mb-3 bg-light" onsubmit="return false;">
              <div class="row g-2">
                <div class="col-md-4">
                  <label class="form-label">Tipo</label>
                  <select class="form-select form-select-sm" id="doc_tipo">
                    <option value="Factura">Factura</option>
                    <option value="Nota de remisión">Nota de remisión</option>
                    <option value="Cotización">Cotización</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
                <div class="col-md-8">
                  <label class="form-label">Archivo (PDF, imagen, Office)</label>
                  <input type="file" class="form-control form-control-sm" id="doc_archivo" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx">
                </div>
                <div class="col-12">
                  <label class="form-label">Notas (opcional)</label>
                  <input type="text" class="form-control form-control-sm" id="doc_notas" placeholder="Ej. Factura CFDI julio, remisión parcial">
                </div>
                <div class="col-12">
                  <button type="button" class="btn btn-success btn-sm" onclick="subirDocumentoOC()">
                    <i class="fas fa-upload"></i> Subir documento
                  </button>
                </div>
              </div>
            </form>
            <div id="listaDocumentosOC">
              <div class="text-muted small text-center py-3">Cargando...</div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Registrar pago OC -->
<div class="modal fade" id="modalPagoOc" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title text-white"><i class="fas fa-dollar-sign me-2"></i>Pago — <span id="pago_oc_folio"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="pago_oc_orden_id">
        <div class="alert alert-light border mb-3">
          <strong>Saldo pendiente:</strong> <span id="pago_oc_saldo" class="text-danger fs-5">$0.00</span>
        </div>
        <div class="mb-3">
          <label class="form-label">Monto a pagar *</label>
          <input type="number" step="0.01" class="form-control" id="pago_oc_monto">
        </div>
        <div class="mb-3">
          <label class="form-label">Fecha de pago *</label>
          <input type="date" class="form-control" id="pago_oc_fecha" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Método</label>
          <select class="form-select" id="pago_oc_metodo">
            <option value="Transferencia">Transferencia</option>
            <option value="Efectivo">Efectivo</option>
            <option value="Cheque">Cheque</option>
            <option value="Crédito">Crédito</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Referencia</label>
          <input type="text" class="form-control" id="pago_oc_referencia" placeholder="Folio transferencia, cheque, etc.">
        </div>
        <div class="mb-0">
          <label class="form-label">Notas</label>
          <textarea class="form-control" id="pago_oc_notas" rows="2"></textarea>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-success" onclick="marcarPagadoCompletoOc()">
          <i class="fas fa-check-double me-1"></i> Marcar pagado total
        </button>
        <div>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-success" onclick="registrarPagoOc()">
            <i class="fas fa-save me-1"></i> Registrar pago
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';
  
  let tabla;
  let ordenEditando = null;
  let detallesTemporales = []; // Para órdenes nuevas
  let insumosProveedor = [];
  const PUEDE_AUTORIZAR_PREORDENES = <?php echo tiene_permiso('compras_autorizar_preordenes') ? 'true' : 'false'; ?>;
  const PUEDE_EDITAR_PREORDENES = <?php echo (tiene_permiso('compras_autorizar_preordenes') || tiene_permiso('compras_preordenes_edit') || tiene_permiso('compras_ordenes_edit')) ? 'true' : 'false'; ?>;
  const PUEDE_CREAR_OC = <?php echo tiene_permiso('compras_ordenes_add') ? 'true' : 'false'; ?>;
  const PUEDE_REPORTES = <?php echo (tiene_permiso('reportes_compras') || tiene_permiso('compras_ordenes_consult')) ? 'true' : 'false'; ?>;
  const PUEDE_PAGOS = <?php echo tiene_permiso('compras_pagos') ? 'true' : 'false'; ?>;
  const NUEVA_PROVEEDOR_ID = <?php echo (int)($response['nueva_proveedor'] ?? 0); ?>;
  const COLSPAN_PREORDENES = 7;
  let preordenesPendientes = [];
  let preordenActual = null;
  let procesandoPreorden = false;
  let reporteCargado = false;
  let ultimoCorreoTexto = '';

  function abrirModal(id) {
    const el = document.getElementById(id);
    if (el && typeof bootstrap !== 'undefined') {
      bootstrap.Modal.getOrCreateInstance(el).show();
    }
  }

  function cerrarModal(id) {
    const el = document.getElementById(id);
    if (el && typeof bootstrap !== 'undefined') {
      bootstrap.Modal.getOrCreateInstance(el).hide();
    }
  }

  function toastCompras(type, title, message) {
    if (typeof showErpToast === 'function') {
      showErpToast({ type: type, module: 'Compras', title: title, message: message });
    } else if (typeof notifyShow === 'function') {
      notifyShow(message, type);
    }
  }

  function restaurarFooterOrden() {
    $('#modalOrden .modal-footer').html(`
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      <button type="button" class="btn btn-primary" onclick="guardarOrden()">
        <i class="fas fa-save"></i> Guardar Borrador
      </button>
    `);
  }

  function initOrdenesCompra() {
    inicializarDataTable();
    cargarProveedoresSelect(function() {
      if (NUEVA_PROVEEDOR_ID > 0) {
        setTimeout(function() {
          mostrarModalNuevo(NUEVA_PROVEEDOR_ID);
        }, 300);
      }
    });
    cargarPreordenesPendientes();
    cargarHistorialPreordenes();
    
    // Filtros
    $('#filtroEstatus').on('change', function() {
      tabla.ajax.reload();
    });
    $('#filtroEstatusPago').on('change', function() {
      tabla.ajax.reload();
    });

    // Fecha de orden por defecto: hoy
    $('#orden_fecha_orden').val(new Date().toISOString().split('T')[0]);
    
    // Event listener para cambio de insumo
    $('#detalle_insumo_id').on('change', function() {
      const selected = $(this).find(':selected');
      const precio = selected.data('precio');
      if(precio) {
        $('#detalle_precio').val(precio);
        calcularSubtotalDetalle();
      }
    });
  }

  function inicializarDataTable() {
    tabla = $('#tablaOrdenes').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '<?=base_url();?>compras/OrdenesCompra/lista_ajax',
        type: 'POST',
        data: function(d) {
          d.peticion = 'ajax';
          d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
          d.filtro_estatus = $('#filtroEstatus').val();
          d.filtro_estatus_pago = $('#filtroEstatusPago').val();
        }
      },
      columns: [
        { data: 0 },
        { data: 1 },
        { data: 2 },
        { data: 3 },
        { data: 4 },
        { data: 5 },
        { data: 6, orderable: false }
      ],
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
      },
      pageLength: 25,
      order: [[1, 'desc']]
    });
  }

  function cargarProveedoresSelect(callback) {
    $.post('<?=base_url();?>compras/OrdenesCompra/get_proveedores_select_ajax', {
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        let html = '<option value="">-- Seleccionar --</option>';
        result.proveedores.forEach(function(prov) {
          html += `<option value="${prov.id}">${prov.text}</option>`;
        });
        $('#orden_proveedor_id').html(html);
        $('#autorizar_proveedor_id').html(html);
      }
      if (typeof callback === 'function') callback();
    });
  }

  function formatearFechaPreorden(fecha) {
    if (!fecha) return '—';
    try {
      return new Date(fecha).toLocaleDateString('es-MX', { year: 'numeric', month: 'short', day: 'numeric' });
    } catch (e) {
      return fecha;
    }
  }

  function badgeEstatusPreorden(estatus) {
    const map = {
      'Pendiente': 'warning',
      'Convertida': 'success',
      'Rechazada': 'danger',
      'Aprobada': 'info',
      'Cancelada': 'secondary'
    };
    const cls = map[estatus] || 'secondary';
    return '<span class="badge bg-' + cls + '">' + (estatus || '—') + '</span>';
  }

  function cargarProveedoresInsumoSelect(insumoId, selectId, selectedId, callback) {
    if (!insumoId) {
      $(selectId).html('<option value="">— Sin proveedores —</option>');
      if (typeof callback === 'function') callback();
      return;
    }
    $.post('<?=base_url();?>compras/OrdenesCompra/get_proveedores_insumo_ajax', {
      insumo_id: insumoId,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { result = { success: false }; }
      let html = '<option value="">— Seleccionar —</option>';
      if (result.success && result.proveedores && result.proveedores.length) {
        result.proveedores.forEach(function(prov) {
          const precio = parseFloat(prov.precio_compra || 0).toFixed(2);
          const principal = prov.es_proveedor_principal == 1 ? ' ★' : '';
          html += '<option value="' + prov.id + '">' + prov.razon_social + ' ($' + precio + ')' + principal + '</option>';
        });
      }
      $(selectId).html(html);
      if (selectedId) $(selectId).val(String(selectedId));
      if (typeof callback === 'function') callback();
    }).fail(function() {
      if (typeof callback === 'function') callback();
    });
  }

  function botonesAccionPreorden(p, esPendiente) {
    let html = '<td class="text-end text-nowrap" onclick="event.stopPropagation();">';
    html += '<button type="button" class="btn btn-sm btn-info me-1" onclick="verPreorden(' + p.id + ')" title="Consultar">';
    html += '<i class="fas fa-eye"></i></button>';
    if (esPendiente && PUEDE_EDITAR_PREORDENES) {
      html += '<button type="button" class="btn btn-sm btn-primary me-1" onclick="editarPreorden(' + p.id + ')" title="Editar">';
      html += '<i class="fas fa-edit"></i></button>';
    }
    if (esPendiente && PUEDE_AUTORIZAR_PREORDENES) {
      html += '<button type="button" class="btn btn-sm btn-success me-1" onclick="abrirModalAutorizarPreorden(' + p.id + ')" title="Autorizar">';
      html += '<i class="fas fa-check"></i></button>';
      html += '<button type="button" class="btn btn-sm btn-outline-danger" onclick="abrirModalRechazarPreorden(' + p.id + ')" title="Rechazar">';
      html += '<i class="fas fa-times"></i></button>';
    }
    html += '</td>';
    return html;
  }

  window.verPreorden = function(id) {
    $.post('<?=base_url();?>compras/OrdenesCompra/get_preorden_ajax', {
      id: id,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) {
        toastCompras('danger', 'Error', 'Respuesta inválida del servidor.');
        return;
      }
      if (!result.success || !result.preorden) {
        toastCompras('danger', 'Error', result.message || 'No se pudo cargar la pre-orden.');
        return;
      }
      const p = result.preorden;
      preordenActual = p;

      $('#detalle_preorden_id').val(p.id);
      $('#detalle_preorden_folio').text(p.folio || ('#' + p.id));
      $('#detalle_preorden_insumo').text((p.insumo_codigo || '') + ' — ' + (p.insumo_nombre || ''));
      $('#detalle_preorden_cantidad').text(parseFloat(p.cantidad_solicitada || 0).toFixed(4) + ' ' + (p.unidad || ''));
      $('#detalle_preorden_estatus').html(badgeEstatusPreorden(p.estatus));
      $('#detalle_preorden_proveedor').text(p.proveedor_sugerido_nombre || 'Sin proveedor asignado');
      $('#detalle_preorden_stock').text((p.insumo_stock_actual != null ? parseFloat(p.insumo_stock_actual).toFixed(2) : '—') + ' / mín ' + (p.insumo_stock_minimo != null ? parseFloat(p.insumo_stock_minimo).toFixed(2) : '—'));
      $('#detalle_preorden_precio').text(p.insumo_precio_promedio ? ('$' + parseFloat(p.insumo_precio_promedio).toFixed(2)) : '—');
      $('#detalle_preorden_origen').text((p.origen_tipo || '—') + (p.origen_id ? (' #' + p.origen_id) : ''));
      $('#detalle_preorden_solicita').text(p.usuario_solicita_nombre || '—');
      $('#detalle_preorden_fecha').text(formatearFechaPreorden(p.fecha_solicitud));
      $('#detalle_preorden_notas').text(p.notas || 'Sin notas');

      if (p.orden_compra_folio) {
        $('#wrap_detalle_oc_generada').show();
        $('#detalle_preorden_oc').html('<span class="badge bg-success">' + p.orden_compra_folio + '</span>');
      } else {
        $('#wrap_detalle_oc_generada').hide();
      }
      if (p.motivo_rechazo) {
        $('#wrap_detalle_motivo_rechazo').show();
        $('#detalle_preorden_motivo').text(p.motivo_rechazo);
      } else {
        $('#wrap_detalle_motivo_rechazo').hide();
      }

      let footer = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
      if (p.estatus === 'Pendiente' && PUEDE_EDITAR_PREORDENES) {
        footer = '<button type="button" class="btn btn-primary me-auto" onclick="cerrarModal(\'modalDetallePreorden\'); editarPreorden(' + p.id + ');"><i class="fas fa-edit me-1"></i> Editar</button>' + footer;
      }
      if (p.estatus === 'Pendiente' && PUEDE_AUTORIZAR_PREORDENES) {
        footer += '<button type="button" class="btn btn-success ms-2" onclick="cerrarModal(\'modalDetallePreorden\'); abrirModalAutorizarPreorden(' + p.id + ');"><i class="fas fa-check me-1"></i> Autorizar</button>';
      }
      $('#footerDetallePreorden').html(footer);
      abrirModal('modalDetallePreorden');
    }).fail(function() {
      toastCompras('danger', 'Error de conexión', 'No se pudo cargar la pre-orden.');
    });
  };

  window.editarPreorden = function(id) {
    const cargar = function(p) {
      if (!p) return;
      preordenActual = p;
      $('#editar_preorden_id').val(p.id);
      $('#editar_preorden_folio').text(p.folio || ('#' + p.id));
      $('#editar_preorden_insumo_label').text((p.insumo_codigo || '') + ' — ' + (p.insumo_nombre || ''));
      $('#editar_preorden_cantidad').val(parseFloat(p.cantidad_solicitada || 0));
      $('#editar_preorden_unidad').text(p.unidad || '—');
      $('#editar_preorden_notas').val(p.notas || '');
      cargarProveedoresInsumoSelect(p.insumo_id, '#editar_preorden_proveedor_id', p.proveedor_sugerido_id, function() {
        abrirModal('modalEditarPreorden');
      });
    };

    const local = preordenesPendientes.find(function(x) { return String(x.id) === String(id); });
    if (local && local.insumo_id) {
      cargar(local);
      return;
    }

    $.post('<?=base_url();?>compras/OrdenesCompra/get_preorden_ajax', {
      id: id,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      if (result.success && result.preorden) cargar(result.preorden);
      else toastCompras('danger', 'Error', result.message || 'No se pudo cargar la pre-orden.');
    });
  };

  window.guardarEdicionPreorden = function() {
    if (procesandoPreorden) return;
    const id = $('#editar_preorden_id').val();
    const cantidad = parseFloat($('#editar_preorden_cantidad').val());
    if (!id || !cantidad || cantidad <= 0) {
      toastCompras('warning', 'Datos incompletos', 'Indique una cantidad válida.');
      return;
    }
    procesandoPreorden = true;
    $('#btnGuardarPreorden').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando...');

    $.post('<?=base_url();?>compras/OrdenesCompra/editar_preorden_ajax', {
      id: id,
      cantidad_solicitada: cantidad,
      proveedor_sugerido_id: $('#editar_preorden_proveedor_id').val(),
      notas: $('#editar_preorden_notas').val(),
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      procesandoPreorden = false;
      $('#btnGuardarPreorden').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar cambios');
      try { result = JSON.parse(result); } catch (e) { return; }
      toastCompras(result.success ? 'success' : 'danger', result.success ? 'Pre-orden actualizada' : 'Error', result.message || '');
      if (result.success) {
        cerrarModal('modalEditarPreorden');
        if (result.preorden) {
          const idx = preordenesPendientes.findIndex(function(x) { return String(x.id) === String(id); });
          if (idx >= 0) preordenesPendientes[idx] = result.preorden;
        }
        cargarPreordenesPendientes();
      }
    }).fail(function() {
      procesandoPreorden = false;
      $('#btnGuardarPreorden').prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar cambios');
      toastCompras('danger', 'Error de conexión', 'No se pudo guardar la pre-orden.');
    });
  };

  window.cargarHistorialPreordenes = function() {
    $('#tbodyHistorialPreordenes').html('<tr><td colspan="7" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin me-1"></i> Cargando...</td></tr>');
    $.post('<?=base_url();?>compras/OrdenesCompra/lista_preordenes_historial_ajax', {
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) {
        $('#tbodyHistorialPreordenes').html('<tr><td colspan="7" class="text-center text-danger py-3">Error al leer respuesta.</td></tr>');
        return;
      }
      const items = result.preordenes || [];
      if (!items.length) {
        $('#tbodyHistorialPreordenes').html('<tr><td colspan="7" class="text-center text-muted py-4">Sin pre-órdenes procesadas aún.</td></tr>');
        return;
      }
      let html = '';
      items.forEach(function(p) {
        const cantidad = parseFloat(p.cantidad_solicitada || 0).toFixed(4);
        const insumo = '<strong>' + (p.insumo_codigo || '') + '</strong> — ' + (p.insumo_nombre || '');
        const oc = p.orden_compra_folio ? '<span class="badge bg-success">' + p.orden_compra_folio + '</span>' : '<span class="text-muted">—</span>';
        html += '<tr>';
        html += '<td><button type="button" class="btn btn-link btn-sm p-0" onclick="verPreorden(' + p.id + ')">' + (p.folio || p.id) + '</button></td>';
        html += '<td>' + insumo + '</td>';
        html += '<td>' + cantidad + ' <small class="text-muted">' + (p.unidad || '') + '</small></td>';
        html += '<td>' + badgeEstatusPreorden(p.estatus) + '</td>';
        html += '<td>' + oc + '</td>';
        html += '<td class="small">' + formatearFechaPreorden(p.fecha_solicitud) + '</td>';
        html += '<td class="text-end"><button type="button" class="btn btn-sm btn-info" onclick="verPreorden(' + p.id + ')" title="Consultar"><i class="fas fa-eye"></i></button></td>';
        html += '</tr>';
      });
      $('#tbodyHistorialPreordenes').html(html);
    }).fail(function() {
      $('#tbodyHistorialPreordenes').html('<tr><td colspan="7" class="text-center text-danger py-3">Error de conexión.</td></tr>');
    });
  };

  window.cargarPreordenesPendientes = function() {
    $('#tbodyPreordenes').html(
      '<tr><td colspan="' + COLSPAN_PREORDENES + '" class="text-center text-muted py-4">' +
      '<i class="fas fa-spinner fa-spin me-1"></i> Cargando pre-órdenes...</td></tr>'
    );

    $.post('<?=base_url();?>compras/OrdenesCompra/lista_preordenes_ajax', {
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) {
        $('#tbodyPreordenes').html('<tr><td colspan="' + COLSPAN_PREORDENES + '" class="text-center text-danger py-3">Error al leer la respuesta.</td></tr>');
        return;
      }
      if (!result.success) {
        $('#tbodyPreordenes').html('<tr><td colspan="' + COLSPAN_PREORDENES + '" class="text-center text-danger py-3">' + (result.message || 'Error al cargar') + '</td></tr>');
        return;
      }
      preordenesPendientes = result.preordenes || [];
      renderizarPreordenesPendientes();
    }).fail(function() {
      $('#tbodyPreordenes').html('<tr><td colspan="' + COLSPAN_PREORDENES + '" class="text-center text-danger py-3">Error de conexión.</td></tr>');
    });
  };

  function renderizarPreordenesPendientes() {
    $('#badgeConteoPreordenes').text(preordenesPendientes.length);

    if (preordenesPendientes.length === 0) {
      $('#tbodyPreordenes').html(
        '<tr><td colspan="' + COLSPAN_PREORDENES + '" class="text-center text-muted py-4">' +
        '<i class="fas fa-check-circle text-success me-1"></i> No hay pre-órdenes pendientes de autorización.</td></tr>'
      );
      return;
    }

    let html = '';
    preordenesPendientes.forEach(function(p) {
      const cantidad = parseFloat(p.cantidad_solicitada || 0).toFixed(4);
      const insumo = '<strong>' + (p.insumo_codigo || '') + '</strong> — ' + (p.insumo_nombre || '');
      const proveedor = p.proveedor_sugerido_nombre || '<span class="text-muted">Sin sugerencia</span>';
      const notas = p.notas ? $('<div>').text(p.notas).html() : '<span class="text-muted">—</span>';

      html += '<tr data-preorden-id="' + p.id + '" style="cursor:pointer;" onclick="verPreorden(' + p.id + ')">';
      html += '<td><span class="badge bg-secondary">' + (p.folio || p.id) + '</span></td>';
      html += '<td>' + insumo + '</td>';
      html += '<td>' + cantidad + ' <small class="text-muted">' + (p.unidad || '') + '</small></td>';
      html += '<td>' + proveedor + '</td>';
      html += '<td class="small">' + formatearFechaPreorden(p.fecha_solicitud) + '</td>';
      html += '<td class="small">' + notas + '</td>';
      html += botonesAccionPreorden(p, true);
      html += '</tr>';
    });
    $('#tbodyPreordenes').html(html);
  }

  window.abrirModalAutorizarPreorden = function(id) {
    const abrir = function(p) {
      if (!p) return;
      $('#autorizar_preorden_id').val(p.id);
      $('#autorizar_insumo_id').val(p.insumo_id || '');
      $('#autorizar_insumo_label').text((p.insumo_codigo || '') + ' — ' + (p.insumo_nombre || ''));
      $('#autorizar_cantidad').val(parseFloat(p.cantidad_solicitada || 0));
      $('#autorizar_unidad').text(p.unidad || '—');
      cargarProveedoresInsumoSelect(p.insumo_id, '#autorizar_proveedor_id', p.proveedor_sugerido_id, function() {
        abrirModal('modalAutorizarPreorden');
      });
    };

    const p = preordenesPendientes.find(function(x) { return String(x.id) === String(id); });
    if (p) {
      abrir(p);
      return;
    }

    $.post('<?=base_url();?>compras/OrdenesCompra/get_preorden_ajax', {
      id: id,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      if (result.success && result.preorden) abrir(result.preorden);
      else toastCompras('danger', 'Error', result.message || 'No se pudo cargar la pre-orden.');
    });
  };

  window.confirmarAutorizarPreorden = function() {
    if (procesandoPreorden) return;

    const id = $('#autorizar_preorden_id').val();
    const cantidad = parseFloat($('#autorizar_cantidad').val());
    if (!id || !cantidad || cantidad <= 0) {
      showErpToast({ type: 'warning', module: 'Compras', title: 'Datos incompletos', message: 'Indique una cantidad aprobada válida.' });
      return;
    }

    procesandoPreorden = true;
    $('#btnConfirmarAutorizarPreorden').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');

    const payload = {
      id: id,
      cantidad_aprobada: cantidad,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    };
    const proveedorId = $('#autorizar_proveedor_id').val();
    if (proveedorId) payload.proveedor_id = proveedorId;

    $.post('<?=base_url();?>compras/OrdenesCompra/autorizar_preorden_ajax', payload, function(result) {
      procesandoPreorden = false;
      $('#btnConfirmarAutorizarPreorden').prop('disabled', false).html('<i class="fas fa-check me-1"></i> Autorizar y crear OC');

      try { result = JSON.parse(result); } catch (e) {
        showErpToast({ type: 'danger', module: 'Compras', title: 'Error', message: 'Respuesta inválida del servidor.' });
        return;
      }

      showErpToast({
        type: result.success ? 'success' : 'danger',
        module: 'Compras',
        title: result.success ? 'Pre-orden autorizada' : 'Error al autorizar',
        message: result.message || (result.success ? 'Se generó la orden de compra.' : 'No se pudo autorizar.')
      });

      if (result.success) {
        const modalEl = document.getElementById('modalAutorizarPreorden');
        if (modalEl && typeof bootstrap !== 'undefined') {
          bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        }
        preordenesPendientes = preordenesPendientes.filter(function(x) { return String(x.id) !== String(id); });
        renderizarPreordenesPendientes();
        cargarHistorialPreordenes();
        if (typeof tabla !== 'undefined' && tabla) {
          tabla.ajax.reload(null, false);
        }
        if (result.orden_compra_id && typeof verOrden === 'function') {
          setTimeout(function() { verOrden(result.orden_compra_id); }, 600);
        }
      }
    }).fail(function() {
      procesandoPreorden = false;
      $('#btnConfirmarAutorizarPreorden').prop('disabled', false).html('<i class="fas fa-check me-1"></i> Autorizar y crear OC');
      showErpToast({ type: 'danger', module: 'Compras', title: 'Error de conexión', message: 'No se pudo contactar al servidor.' });
    });
  };

  window.abrirModalRechazarPreorden = function(id) {
    const p = preordenesPendientes.find(function(x) { return String(x.id) === String(id); });
    if (!p) return;

    $('#rechazar_preorden_id').val(p.id);
    $('#rechazar_folio_label').text(p.folio || ('#' + p.id));
    $('#rechazar_motivo').val('');

    const modalEl = document.getElementById('modalRechazarPreorden');
    if (modalEl && typeof bootstrap !== 'undefined') {
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
  };

  window.confirmarRechazarPreorden = function() {
    if (procesandoPreorden) return;

    const id = $('#rechazar_preorden_id').val();
    const motivo = ($('#rechazar_motivo').val() || '').trim();
    if (!id || !motivo) {
      showErpToast({ type: 'warning', module: 'Compras', title: 'Motivo requerido', message: 'Debe indicar el motivo del rechazo.' });
      $('#rechazar_motivo').focus();
      return;
    }

    procesandoPreorden = true;
    $('#btnConfirmarRechazarPreorden').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Procesando...');

    $.post('<?=base_url();?>compras/OrdenesCompra/rechazar_preorden_ajax', {
      id: id,
      motivo: motivo,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      procesandoPreorden = false;
      $('#btnConfirmarRechazarPreorden').prop('disabled', false).html('<i class="fas fa-ban me-1"></i> Rechazar pre-orden');

      try { result = JSON.parse(result); } catch (e) {
        showErpToast({ type: 'danger', module: 'Compras', title: 'Error', message: 'Respuesta inválida del servidor.' });
        return;
      }

      showErpToast({
        type: result.success ? 'success' : 'danger',
        module: 'Compras',
        title: result.success ? 'Pre-orden rechazada' : 'Error al rechazar',
        message: result.message || ''
      });

      if (result.success) {
        const modalEl = document.getElementById('modalRechazarPreorden');
        if (modalEl && typeof bootstrap !== 'undefined') {
          bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        }
        preordenesPendientes = preordenesPendientes.filter(function(x) { return String(x.id) !== String(id); });
        renderizarPreordenesPendientes();
        cargarHistorialPreordenes();
      }
    }).fail(function() {
      procesandoPreorden = false;
      $('#btnConfirmarRechazarPreorden').prop('disabled', false).html('<i class="fas fa-ban me-1"></i> Rechazar pre-orden');
      showErpToast({ type: 'danger', module: 'Compras', title: 'Error de conexión', message: 'No se pudo contactar al servidor.' });
    });
  };

  window.cargarInsumosProveedor = function() {
    const proveedorId = $('#orden_proveedor_id').val();
    if(!proveedorId) {
      $('#btnAgregarDetalle').prop('disabled', true);
      return;
    }

    $.post('<?=base_url();?>compras/OrdenesCompra/get_insumos_proveedor_ajax', {
      'proveedor_id': proveedorId,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        insumosProveedor = result.insumos || [];
        if (insumosProveedor.length === 0) {
          $('#btnAgregarDetalle').prop('disabled', true);
          toastCompras('warning', 'Sin insumos', 'Este proveedor no tiene insumos vinculados. Vincúlelos en Proveedores → pestaña Insumos.');
          $('#detalle_insumo_id').html('<option value="">— Sin insumos —</option>');
          return;
        }
        $('#btnAgregarDetalle').prop('disabled', false);
        
        let html = '<option value="">-- Seleccionar --</option>';
        result.insumos.forEach(function(ins) {
          const nomProv = ins.nombre_proveedor || '';
          const codProv = ins.codigo_proveedor || '';
          html += `<option value="${ins.insumo_id}" 
                    data-precio="${ins.precio_compra}" 
                    data-codigo="${ins.codigo}" 
                    data-nombre="${ins.nombre_tecnico}" 
                    data-um="${ins.unidad_medida}"
                    data-nomprov="${nomProv}"
                    data-codprov="${codProv}">
                    ${ins.codigo} - ${ins.nombre_tecnico} ($${parseFloat(ins.precio_compra).toFixed(2)})
                   </option>`;
        });
        $('#detalle_insumo_id').html(html);
      }
    });
  };

  window.mostrarModalNuevo = function(proveedorPreseleccionado) {
    ordenEditando = null;
    detallesTemporales = [];
    restaurarFooterOrden();
    $('#alertPasosNuevaOC').show();
    $('#modalOrdenTitle').text('Nueva Orden de Compra');
    $('#formOrden')[0].reset();
    $('#orden_id').val('');
    $('#orden_folio').val('Auto-generado');
    $('#orden_fecha_orden').val(new Date().toISOString().split('T')[0]);
    $('#orden_forma_pago').val('Transferencia');
    $('#orden_proveedor_id').prop('disabled', false);
    $('#btnAgregarDetalle').show().prop('disabled', true);
    $('#formAgregarDetalle').hide();
    $('#tablaDetalles tbody').html('<tr id="noDetalles"><td colspan="6" class="text-center text-muted">No hay insumos agregados</td></tr>');
    actualizarTotales();
    abrirModal('modalOrden');
    if (proveedorPreseleccionado) {
      $('#orden_proveedor_id').val(String(proveedorPreseleccionado));
      cargarInsumosProveedor();
    }
  };

  window.mostrarFormAgregarDetalle = function() {
    $('#formAgregarDetalle').slideDown();
    $('#detalle_insumo_id').val('');
    $('#detalle_cantidad').val('');
    $('#detalle_precio').val('');
    $('#detalle_subtotal').val('');
  };

  window.cancelarAgregarDetalle = function() {
    $('#formAgregarDetalle').slideUp();
  };

  window.calcularSubtotalDetalle = function() {
    const cantidad = parseFloat($('#detalle_cantidad').val()) || 0;
    const precio = parseFloat($('#detalle_precio').val()) || 0;
    const subtotal = cantidad * precio;
    $('#detalle_subtotal').val('$' + subtotal.toFixed(2));
  };

  window.agregarDetalleATabla = function() {
    const insumoId = $('#detalle_insumo_id').val();
    const cantidad = parseFloat($('#detalle_cantidad').val());
    const precio = parseFloat($('#detalle_precio').val());

    if(!insumoId || !cantidad || !precio) {
      notifyShow('Complete todos los campos', 'warning');
      return;
    }

    const selected = $('#detalle_insumo_id').find(':selected');
    const codigo = selected.data('codigo');
    const nombre = selected.data('nombre');
    const um = selected.data('um');
    const nomProv = selected.data('nomprov');
    const codProv = selected.data('codprov');
    const subtotal = cantidad * precio;

    const detalle = {
      insumo_id: insumoId,
      codigo: codigo,
      nombre: nombre,
      unidad_medida: um,
      nombre_proveedor: nomProv,
      codigo_proveedor: codProv,
      cantidad: cantidad,
      precio: precio,
      subtotal: subtotal
    };

    detallesTemporales.push(detalle);
    renderizarDetalles();
    cancelarAgregarDetalle();
  };

  function renderizarDetalles() {
    if(detallesTemporales.length === 0) {
      $('#tablaDetalles tbody').html('<tr id="noDetalles"><td colspan="6" class="text-center text-muted">No hay insumos agregados</td></tr>');
    } else {
      let html = '';
      detallesTemporales.forEach(function(det, index) {
        const infoProv = det.nombre_proveedor ? `<br><small class="text-primary"><i class="fas fa-tag"></i> ${det.nombre_proveedor}</small>` : '';
        const codigoProv = det.codigo_proveedor ? `<br><small class="text-muted">${det.codigo_proveedor}</small>` : '';
        html += `
          <tr>
            <td>${det.codigo}${codigoProv}</td>
            <td>${det.nombre} ${infoProv} <small class="text-muted">(${det.unidad_medida})</small></td>
            <td>${det.cantidad}</td>
            <td>$${det.precio.toFixed(2)}</td>
            <td>$${det.subtotal.toFixed(2)}</td>
            <td>
              <button class="btn btn-sm btn-danger" onclick="eliminarDetalleTemporal(${index})">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        `;
      });
      $('#tablaDetalles tbody').html(html);
    }
    actualizarTotales();
  }

  window.eliminarDetalleTemporal = function(index) {
    detallesTemporales.splice(index, 1);
    renderizarDetalles();
  };

  function actualizarTotales() {
    let subtotal = 0;
    detallesTemporales.forEach(function(det) {
      subtotal += det.subtotal;
    });
    
    const iva = subtotal * 0.16;
    const total = subtotal + iva;

    $('#orden_subtotal_display').text('$' + subtotal.toFixed(2));
    $('#orden_iva_display').text('$' + iva.toFixed(2));
    $('#orden_total_display').text('$' + total.toFixed(2));
  }

  window.guardarOrden = function() {
    if (!$('#orden_proveedor_id').val()) {
      toastCompras('warning', 'Proveedor requerido', 'Seleccione un proveedor antes de guardar.');
      return;
    }
    if(detallesTemporales.length === 0) {
      toastCompras('warning', 'Sin insumos', 'Agregue al menos un insumo a la orden (botón Agregar Insumo).');
      return;
    }

    const formData = $('#formOrden').serialize();
    
    $.post('<?=base_url();?>compras/OrdenesCompra/crear_ajax',
      formData + '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>',
      function(result) {
        result = JSON.parse(result);
        if(result.success) {
          const ordenId = result.orden_id;
          
          // Guardar detalles
          let detallesGuardados = 0;
          detallesTemporales.forEach(function(det) {
            $.post('<?=base_url();?>compras/OrdenesCompra/agregar_detalle_ajax', {
              'orden_id': ordenId,
              'insumo_id': det.insumo_id,
              'cantidad_solicitada': det.cantidad,
              'precio_unitario': det.precio,
              'nombre_proveedor': det.nombre_proveedor,
              'codigo_proveedor': det.codigo_proveedor,
              'peticion': 'ajax',
              '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
            }, function() {
              detallesGuardados++;
              if(detallesGuardados === detallesTemporales.length) {
                toastCompras('success', 'Orden creada', 'Borrador guardado. Use el botón ✓ verde para aprobar y enviar.');
                cerrarModal('modalOrden');
                tabla.ajax.reload();
              }
            }).fail(function() {
              toastCompras('danger', 'Error', 'No se pudo agregar un insumo a la orden.');
            });
          });
        } else {
          toastCompras('danger', 'Error', result.message || 'No se pudo crear la orden');
        }
      }
    ).fail(function() {
      toastCompras('danger', 'Error de conexión', 'No se pudo contactar al servidor.');
    });
  };

  window.verOrden = function(id) {
    $.post('<?=base_url();?>compras/OrdenesCompra/get_orden_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        const orden = result.orden;
        
        // Llenar formulario en modo solo lectura
        $('#modalOrdenTitle').text('Ver Orden: ' + orden.folio);
        $('#orden_id').val(orden.id);
        $('#orden_folio').val(orden.folio);
        $('#orden_proveedor_id').val(orden.proveedor_id).prop('disabled', true);
        $('#orden_fecha_orden').val(orden.fecha_orden).prop('disabled', true);
        $('#orden_fecha_entrega_estimada').val(orden.fecha_entrega_estimada).prop('disabled', true);
        $('#orden_forma_pago').val(orden.forma_pago).prop('disabled', true);
        $('#orden_condiciones_pago').val(orden.condiciones_pago).prop('disabled', true);
        $('#orden_observaciones').val(orden.observaciones).prop('disabled', true);
        
        // Ocultar botones de edición
        $('#btnAgregarDetalle').hide();
        $('#formAgregarDetalle').hide();
        
        // Mostrar detalles
        let html = '';
        orden.detalles.forEach(function(det) {
          html += `
            <tr>
              <td>${det.codigo}</td>
              <td>${det.nombre_tecnico} <small class="text-muted">(${det.unidad_medida})</small></td>
              <td>${det.cantidad_solicitada}</td>
              <td>$${parseFloat(det.precio_unitario).toFixed(2)}</td>
              <td>$${parseFloat(det.subtotal).toFixed(2)}</td>
              <td>-</td>
            </tr>
          `;
        });
        $('#tablaDetalles tbody').html(html);
        
        // Mostrar totales
        $('#orden_subtotal_display').text('$' + parseFloat(orden.subtotal).toFixed(2));
        $('#orden_iva_display').text('$' + parseFloat(orden.iva).toFixed(2));
        $('#orden_total_display').text('$' + parseFloat(orden.total).toFixed(2));

        let footerHtml = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
        const saldo = parseFloat(orden.saldo_pendiente || 0);
        const estatusPago = orden.estatus_pago || 'Sin adeudo';
        if (saldo > 0 && PUEDE_PAGOS && !['Borrador', 'Cancelada'].includes(orden.estatus)) {
          footerHtml = '<span class="me-auto text-danger small align-self-center"><i class="fas fa-exclamation-circle"></i> Adeudo: $' + saldo.toFixed(2) + ' (' + estatusPago + ')</span>' +
            '<button type="button" class="btn btn-success me-2" onclick="cerrarModal(\'modalOrden\'); mostrarModalPagoOc(' + orden.id + ', ' + saldo + ', \'' + orden.folio.replace(/'/g, "\\'") + '\')"><i class="fas fa-dollar-sign"></i> Registrar pago</button>' + footerHtml;
        } else if (estatusPago === 'Pagado') {
          footerHtml = '<span class="me-auto text-success small align-self-center"><i class="fas fa-check-circle"></i> Pagado — $' + parseFloat(orden.monto_pagado || 0).toFixed(2) + '</span>' + footerHtml;
        }
        
        $('#alertPasosNuevaOC').hide();
        $('.modal-footer', '#modalOrden').html(footerHtml);
        
        abrirModal('modalOrden');
      }
    });
  };

  window.editarOrden = function(id) {
    ordenEditando = id;
    
    $.post('<?=base_url();?>compras/OrdenesCompra/get_orden_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        const orden = result.orden;
        
        // Llenar formulario
        $('#modalOrdenTitle').text('Editar Orden: ' + orden.folio);
        $('#orden_id').val(orden.id);
        $('#orden_folio').val(orden.folio);
        $('#orden_proveedor_id').val(orden.proveedor_id).prop('disabled', false);
        $('#orden_fecha_orden').val(orden.fecha_orden).prop('disabled', false);
        $('#orden_fecha_entrega_estimada').val(orden.fecha_entrega_estimada).prop('disabled', false);
        $('#orden_forma_pago').val(orden.forma_pago).prop('disabled', false);
        $('#orden_condiciones_pago').val(orden.condiciones_pago).prop('disabled', false);
        $('#orden_observaciones').val(orden.observaciones).prop('disabled', false);
        
        // Cargar insumos del proveedor
        cargarInsumosProveedor();
        
        // Mostrar botón agregar
        $('#btnAgregarDetalle').show().prop('disabled', false);
        
        // Cargar detalles existentes en array temporal
        detallesTemporales = [];
        orden.detalles.forEach(function(det) {
          detallesTemporales.push({
            id: det.id, // ID del detalle para actualizar
            insumo_id: det.insumo_id,
            codigo: det.codigo,
            nombre: det.nombre_tecnico,
            unidad_medida: det.unidad_medida,
            cantidad: parseFloat(det.cantidad_solicitada),
            precio: parseFloat(det.precio_unitario),
            subtotal: parseFloat(det.subtotal)
          });
        });
        
        renderizarDetalles();
        
        $('#alertPasosNuevaOC').show();
        // Restaurar botones normales
        $('.modal-footer', '#modalOrden').html(`
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="actualizarOrden()">
            <i class="fas fa-save"></i> Actualizar Orden
          </button>
        `);
        
        abrirModal('modalOrden');
      }
    });
  };

  window.actualizarOrden = function() {
    if(detallesTemporales.length === 0) {
      notifyShow('Agregue al menos un insumo a la orden', 'warning');
      return;
    }

    const ordenId = $('#orden_id').val();
    const formData = $('#formOrden').serialize();
    
    // Actualizar datos de la orden
    $.post('<?=base_url();?>compras/OrdenesCompra/editar_ajax',
      formData + '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>',
      function(result) {
        result = JSON.parse(result);
        if(result.success) {
          // Eliminar detalles antiguos y agregar nuevos
          // Por simplicidad, eliminamos todos y agregamos los nuevos
          $.post('<?=base_url();?>compras/OrdenesCompra/eliminar_todos_detalles_ajax', {
            'orden_id': ordenId,
            'peticion': 'ajax',
            '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
          }, function() {
            // Agregar nuevos detalles
            let detallesGuardados = 0;
            detallesTemporales.forEach(function(det) {
              $.post('<?=base_url();?>compras/OrdenesCompra/agregar_detalle_ajax', {
                'orden_id': ordenId,
                'insumo_id': det.insumo_id,
                'cantidad_solicitada': det.cantidad,
                'precio_unitario': det.precio,
                'nombre_proveedor': det.nombre_proveedor,
                'codigo_proveedor': det.codigo_proveedor,
                'peticion': 'ajax',
                '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
              }, function() {
                detallesGuardados++;
                if(detallesGuardados === detallesTemporales.length) {
                  toastCompras('success', 'Orden actualizada', 'Los cambios se guardaron correctamente.');
                  cerrarModal('modalOrden');
                  tabla.ajax.reload();
                }
              });
            });
          });
        } else {
          notifyShow('Error: ' + result.message, 'danger');
        }
      }
    );
  };

  window.aprobarOrden = function(id) {
    if(!confirm('¿Aprobar y enviar esta orden al proveedor?')) return;

    $.post('<?=base_url();?>compras/OrdenesCompra/cambiar_estatus_ajax', {
      'id': id,
      'estatus': 'Enviada',
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if(result.success) {
        tabla.ajax.reload();
      }
    });
  };

  window.eliminarOrden = function(id) {
    if(!confirm('¿Eliminar esta orden? Solo se pueden eliminar órdenes en Borrador.')) return;

    $.post('<?=base_url();?>compras/OrdenesCompra/eliminar_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if(result.success) {
        tabla.ajax.reload();
      }
    });
  };

  window.recibirMercancia = function(id) {
    $('#recibir_orden_id').val(id);
    
    $.post('<?=base_url();?>compras/OrdenesCompra/get_orden_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        const orden = result.orden;
        $('#folioRecibir').text(orden.folio);
        
        let html = '';
        orden.detalles.forEach(function(det) {
          const pendiente = det.cantidad_solicitada - det.cantidad_recibida;
          html += `
            <tr>
              <td>${det.nombre_tecnico} <small class="text-muted">(${det.unidad_medida})</small></td>
              <td>${det.cantidad_solicitada}</td>
              <td>${det.cantidad_recibida}</td>
              <td>
                <input type="number" class="form-control form-control-sm recibir-cantidad" 
                       data-detalle-id="${det.id}" 
                       data-max="${pendiente}"
                       value="${pendiente}" 
                       min="0" 
                       max="${pendiente}" 
                       step="0.01">
              </td>
              <td class="pendiente-${det.id}">${pendiente}</td>
            </tr>
          `;
        });
        
        $('#tablaRecibirDetalles tbody').html(html);
        
        // Calcular pendiente en tiempo real
        $('.recibir-cantidad').on('input', function() {
          const detalleId = $(this).data('detalle-id');
          const max = parseFloat($(this).data('max'));
          const recibir = parseFloat($(this).val()) || 0;
          const pendiente = max - recibir;
          $('.pendiente-' + detalleId).text(pendiente.toFixed(2));
        });
        
        abrirModal('modalRecibir');
      }
    });
  };

  window.simularCorreoProveedor = function(id) {
    $.post('<?=base_url();?>compras/OrdenesCompra/simular_correo_ajax', {
      id: id,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) {
        showErpToast({ type: 'danger', module: 'Compras', title: 'Error', message: 'Respuesta inválida del servidor.' });
        return;
      }
      if (!result.success) {
        showErpToast({ type: 'danger', module: 'Compras', title: 'Error', message: result.message || 'No se pudo generar la simulación.' });
        return;
      }
      $('#correo_destinatario').text(result.destinatario || '—');
      $('#correo_asunto').text(result.asunto || '—');
      $('#correo_cuerpo_html').html(result.cuerpo_html || '');
      ultimoCorreoTexto = 'Para: ' + (result.destinatario || '') + '\nAsunto: ' + (result.asunto || '') + '\n\n' + (result.cuerpo_texto || '');
      $('#correo_cuerpo_texto').val(ultimoCorreoTexto);
      const modalEl = document.getElementById('modalSimularCorreo');
      if (modalEl && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
      }
    }).fail(function() {
      showErpToast({ type: 'danger', module: 'Compras', title: 'Error de conexión', message: 'No se pudo contactar al servidor.' });
    });
  };

  window.copiarCorreoSimulado = function() {
    const texto = ultimoCorreoTexto || $('#correo_cuerpo_texto').val();
    if (!texto) return;
    const onCopied = function() {
      showErpToast({ type: 'info', module: 'Compras', title: 'Correo simulado', message: 'Contenido copiado — envío real pendiente de configuración SMTP.' });
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(texto).then(onCopied).catch(function() {
        showErpToast({ type: 'warning', module: 'Compras', title: 'Copiar', message: 'No se pudo copiar al portapapeles.' });
      });
    } else {
      const ta = document.createElement('textarea');
      ta.value = texto;
      document.body.appendChild(ta);
      ta.select();
      try {
        document.execCommand('copy');
        onCopied();
      } catch (e) {
        showErpToast({ type: 'warning', module: 'Compras', title: 'Copiar', message: 'No se pudo copiar al portapapeles.' });
      }
      document.body.removeChild(ta);
    }
  };

  window.toggleRangoPersonalizado = function() {
    const esPersonalizado = $('#reporte_periodo').val() === 'personalizado';
    $('.rango-personalizado').toggle(esPersonalizado);
  };

  window.cargarReporteCompras = function() {
    if (!PUEDE_REPORTES) {
      toastCompras('warning', 'Sin permiso', 'No tienes permiso para ver reportes de compras.');
      return;
    }
    const payload = {
      periodo: $('#reporte_periodo').val() || 'mes',
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    };
    if (payload.periodo === 'personalizado') {
      payload.fecha_inicio = $('#reporte_fecha_inicio').val();
      payload.fecha_fin = $('#reporte_fecha_fin').val();
    }

    $('#tbodyReporteProveedores').html('<tr><td colspan="3" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#tbodyReporteOrdenes').html('<tr><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');

    $.post('<?=base_url();?>compras/OrdenesCompra/reporte_ajax', payload, function(result) {
      try { result = JSON.parse(result); } catch (e) {
        showErpToast({ type: 'danger', module: 'Compras', title: 'Error', message: 'Respuesta inválida del servidor.' });
        return;
      }
      if (!result.success || !result.reporte) {
        showErpToast({ type: 'danger', module: 'Compras', title: 'Error', message: result.message || 'No se pudo cargar el reporte.' });
        return;
      }
      renderizarReporteCompras(result.reporte);
      reporteCargado = true;
    }).fail(function() {
      showErpToast({ type: 'danger', module: 'Compras', title: 'Error de conexión', message: 'No se pudo cargar el reporte.' });
    });
  };

  function renderizarReporteCompras(r) {
    $('#reporte_monto_gastado').text('$' + parseFloat(r.monto_gastado || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $('#reporte_oc_recibidas').text(r.oc_recibidas || 0);
    $('#reporte_oc_pendientes').text(r.oc_pendientes || 0);
    $('#reporte_rango_label').text('Periodo: ' + (r.fecha_inicio || '') + ' — ' + (r.fecha_fin || ''));

    const provs = r.por_proveedor || [];
    if (provs.length === 0) {
      $('#tbodyReporteProveedores').html('<tr><td colspan="3" class="text-center text-muted py-3">Sin compras recibidas en este periodo</td></tr>');
    } else {
      let htmlProv = '';
      provs.forEach(function(p) {
        htmlProv += '<tr><td>' + (p.razon_social || '—') + '</td>';
        htmlProv += '<td class="text-center">' + (p.num_ordenes || 0) + '</td>';
        htmlProv += '<td class="text-end">$' + parseFloat(p.monto_total || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '</td></tr>';
      });
      $('#tbodyReporteProveedores').html(htmlProv);
    }

    const ordenes = r.ordenes || [];
    if (ordenes.length === 0) {
      $('#tbodyReporteOrdenes').html('<tr><td colspan="5" class="text-center text-muted py-3">Sin órdenes en este periodo</td></tr>');
    } else {
      let htmlOc = '';
      ordenes.forEach(function(oc) {
        const fecha = oc.fecha_orden ? new Date(oc.fecha_orden + 'T12:00:00').toLocaleDateString('es-MX') : '—';
        htmlOc += '<tr><td><strong>' + (oc.folio || '') + '</strong></td>';
        htmlOc += '<td>' + fecha + '</td>';
        htmlOc += '<td>' + (oc.razon_social || '—') + '</td>';
        htmlOc += '<td class="text-end">$' + parseFloat(oc.total || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 }) + '</td>';
        htmlOc += '<td><span class="badge bg-secondary">' + (oc.estatus || '') + '</span></td></tr>';
      });
      $('#tbodyReporteOrdenes').html(htmlOc);
    }
  }

  window.exportarReporteCsv = function() {
    const periodo = $('#reporte_periodo').val() || 'mes';
    let url = '<?=base_url();?>compras/OrdenesCompra/exportar_reporte_csv?periodo=' + encodeURIComponent(periodo);
    if (periodo === 'personalizado') {
      const fi = $('#reporte_fecha_inicio').val();
      const ff = $('#reporte_fecha_fin').val();
      if (fi) url += '&fecha_inicio=' + encodeURIComponent(fi);
      if (ff) url += '&fecha_fin=' + encodeURIComponent(ff);
    }
    window.location.href = url;
  };

  window.guardarRecepcion = function() {
    const ordenId = $('#recibir_orden_id').val();
    const detalles = [];
    
    $('.recibir-cantidad').each(function() {
      const cantidad = parseFloat($(this).val()) || 0;
      if(cantidad > 0) {
        detalles.push({
          detalle_id: $(this).data('detalle-id'),
          cantidad_recibida: cantidad
        });
      }
    });

    if(detalles.length === 0) {
      notifyShow('Ingrese al menos una cantidad a recibir', 'warning');
      return;
    }

    // Enviar como JSON string para que PHP lo reciba correctamente
    $.ajax({
      url: '<?=base_url();?>compras/OrdenesCompra/recibir_mercancia_ajax',
      type: 'POST',
      data: {
        'orden_id': ordenId,
        'detalles': JSON.stringify(detalles),
        'peticion': 'ajax',
        '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
      },
      success: function(result) {
        result = JSON.parse(result);
        const msg = result.success && result.estatus
          ? result.message + ' — Estatus: ' + result.estatus
          : (result.message || '');
        toastCompras(result.success ? 'success' : 'danger', result.success ? 'Recepción registrada' : 'Error', msg);
        if(result.success) {
          cerrarModal('modalRecibir');
          tabla.ajax.reload();
        }
      },
      error: function() {
        toastCompras('danger', 'Error', 'No se pudo procesar la recepción.');
      }
    });
  };

  window.gestionarOrden = function(id, folio) {
    $('#gestionar_orden_id').val(id);
    $('#gestionar_folio').text(folio || ('#' + id));
    $('#gestionar_comentario_nuevo').val('');
    $('#doc_archivo').val('');
    $('#doc_notas').val('');
    cargarComentariosOC();
    cargarDocumentosOC();
    abrirModal('modalGestionarOrden');
  };

  function cargarComentariosOC() {
    const ordenId = $('#gestionar_orden_id').val();
    $.post('<?=base_url();?>compras/OrdenesCompra/listar_comentarios_ajax', {
      orden_id: ordenId,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      if (!result.success) {
        $('#listaComentariosOC').html('<div class="text-danger small">Error al cargar comentarios</div>');
        return;
      }
      if (!result.comentarios || result.comentarios.length === 0) {
        $('#listaComentariosOC').html('<div class="text-muted small text-center py-2">Sin comentarios aún</div>');
        return;
      }
      let html = '';
      result.comentarios.forEach(function(c) {
        const fecha = c.creado_en ? new Date(c.creado_en.replace(' ', 'T')).toLocaleString('es-MX') : '';
        html += '<div class="border-bottom pb-2 mb-2"><div class="small text-muted">' +
          (c.autor_nombre || 'Usuario') + ' · ' + fecha + '</div><div>' +
          $('<div>').text(c.comentario).html() + '</div></div>';
      });
      $('#listaComentariosOC').html(html);
    });
  }

  window.agregarComentarioOC = function() {
    const ordenId = $('#gestionar_orden_id').val();
    const comentario = ($('#gestionar_comentario_nuevo').val() || '').trim();
    if (!comentario) {
      toastCompras('warning', 'Comentario vacío', 'Escriba un comentario antes de guardar.');
      return;
    }
    $.post('<?=base_url();?>compras/OrdenesCompra/agregar_comentario_ajax', {
      orden_id: ordenId,
      comentario: comentario,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      toastCompras(result.success ? 'success' : 'danger', result.success ? 'Comentario agregado' : 'Error', result.message || '');
      if (result.success) {
        $('#gestionar_comentario_nuevo').val('');
        cargarComentariosOC();
      }
    });
  };

  function cargarDocumentosOC() {
    const ordenId = $('#gestionar_orden_id').val();
    $.post('<?=base_url();?>compras/OrdenesCompra/listar_documentos_ajax', {
      orden_id: ordenId,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      if (!result.success) {
        $('#listaDocumentosOC').html('<div class="text-danger small">Error al cargar documentos</div>');
        return;
      }
      if (!result.documentos || result.documentos.length === 0) {
        $('#listaDocumentosOC').html('<div class="text-muted small text-center py-2">Sin documentos adjuntos</div>');
        return;
      }
      let html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead><tr><th>Tipo</th><th>Archivo</th><th>Fecha</th><th></th></tr></thead><tbody>';
      result.documentos.forEach(function(d) {
        const url = '<?=base_url();?>' + d.ruta;
        const fecha = d.fecha_subida ? new Date(d.fecha_subida.replace(' ', 'T')).toLocaleDateString('es-MX') : '';
        html += '<tr><td><span class="badge bg-info text-dark">' + d.tipo + '</span></td>';
        html += '<td><a href="' + url + '" target="_blank" rel="noopener">' + d.nombre_archivo + '</a>';
        if (d.notas) html += '<br><small class="text-muted">' + $('<div>').text(d.notas).html() + '</small>';
        html += '</td><td class="small">' + fecha + '</td>';
        html += '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="eliminarDocumentoOC(' + d.id + ')"><i class="fas fa-trash"></i></button></td></tr>';
      });
      html += '</tbody></table></div>';
      $('#listaDocumentosOC').html(html);
    });
  }

  window.subirDocumentoOC = function() {
    const ordenId = $('#gestionar_orden_id').val();
    const archivo = document.getElementById('doc_archivo');
    if (!archivo || !archivo.files || !archivo.files.length) {
      toastCompras('warning', 'Archivo requerido', 'Seleccione un archivo para subir.');
      return;
    }
    const fd = new FormData();
    fd.append('orden_id', ordenId);
    fd.append('tipo', $('#doc_tipo').val());
    fd.append('notas', $('#doc_notas').val());
    fd.append('archivo', archivo.files[0]);
    fd.append('peticion', 'ajax');
    fd.append('<?php echo $this->security->get_csrf_token_name();?>', '<?php echo $this->security->get_csrf_hash();?>');

    $.ajax({
      url: '<?=base_url();?>compras/OrdenesCompra/subir_documento_ajax',
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      success: function(result) {
        try { result = JSON.parse(result); } catch (e) { return; }
        toastCompras(result.success ? 'success' : 'danger', result.success ? 'Documento cargado' : 'Error', result.message || '');
        if (result.success) {
          $('#doc_archivo').val('');
          $('#doc_notas').val('');
          cargarDocumentosOC();
        }
      },
      error: function() {
        toastCompras('danger', 'Error', 'No se pudo subir el archivo.');
      }
    });
  };

  window.eliminarDocumentoOC = function(id) {
    if (!confirm('¿Eliminar este documento?')) return;
    $.post('<?=base_url();?>compras/OrdenesCompra/eliminar_documento_ajax', {
      id: id,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      toastCompras(result.success ? 'success' : 'danger', result.success ? 'Eliminado' : 'Error', result.message || '');
      if (result.success) cargarDocumentosOC();
    });
  };

  window.mostrarModalPagoOc = function(ordenId, saldo, folio) {
    if (!PUEDE_PAGOS) {
      toastCompras('warning', 'Sin permiso', 'No tienes permiso para registrar pagos.');
      return;
    }
    $('#pago_oc_orden_id').val(ordenId);
    $('#pago_oc_folio').text(folio || ('OC #' + ordenId));
    $('#pago_oc_saldo').text('$' + parseFloat(saldo).toFixed(2));
    $('#pago_oc_monto').val(parseFloat(saldo).toFixed(2)).attr('max', saldo);
    abrirModal('modalPagoOc');
  };

  window.registrarPagoOc = function() {
    const ordenId = $('#pago_oc_orden_id').val();
    const monto = parseFloat($('#pago_oc_monto').val());
    if (!ordenId || !monto || monto <= 0) {
      toastCompras('warning', 'Monto inválido', 'Indique un monto mayor a cero.');
      return;
    }
    $.post('<?=base_url();?>compras/OrdenesCompra/registrar_pago_ajax', {
      orden_id: ordenId,
      monto: monto,
      fecha_pago: $('#pago_oc_fecha').val(),
      metodo_pago: $('#pago_oc_metodo').val(),
      referencia: $('#pago_oc_referencia').val(),
      notas: $('#pago_oc_notas').val(),
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      toastCompras(result.success ? 'success' : 'danger', result.success ? 'Pago registrado' : 'Error', result.message || '');
      if (result.success) {
        cerrarModal('modalPagoOc');
        if (tabla) tabla.ajax.reload();
      }
    });
  };

  window.marcarPagadoCompletoOc = function() {
    const ordenId = $('#pago_oc_orden_id').val();
    $.post('<?=base_url();?>compras/OrdenesCompra/marcar_pagado_ajax', {
      orden_id: ordenId,
      fecha_pago: $('#pago_oc_fecha').val(),
      metodo_pago: $('#pago_oc_metodo').val(),
      referencia: $('#pago_oc_referencia').val() || 'Pago total',
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      toastCompras(result.success ? 'success' : 'danger', result.success ? 'Pagado' : 'Error', result.message || '');
      if (result.success) {
        cerrarModal('modalPagoOc');
        if (tabla) tabla.ajax.reload();
      }
    });
  };

  // Inicializar
  if (typeof jQuery !== 'undefined') {
    $(document).ready(initOrdenesCompra);
  } else {
    window.addEventListener('load', function() {
      if (typeof jQuery !== 'undefined') {
        $(document).ready(initOrdenesCompra);
      }
    });
  }
})();
</script>
