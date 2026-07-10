<?php
/**
 * Vista principal de Proveedores
 * Listado de proveedores con DataTables
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
    <!-- Total Proveedores Activos -->
    <div class="col-lg-6 col-xl-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Proveedores Activos</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['total_activos']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-truck text-primary" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Proveedores activos en sistema</small>
        </div>
      </div>
    </div>

    <!-- Proveedores Inactivos -->
    <div class="col-lg-6 col-xl-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Proveedores Inactivos</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['total_inactivos']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-ban text-secondary" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Proveedores dados de baja</small>
        </div>
      </div>
    </div>

    <!-- Relaciones Proveedor-Insumo -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Relaciones</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-12">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['total_relaciones']; ?>
              </h3>
            </div>
          </div>
          <small class="text-muted">Insumos relacionados con proveedores</small>
        </div>
      </div>
    </div>

    <!-- Órdenes de Compra -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Órdenes de Compra</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['total_ordenes'] ?? 0; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-file-invoice text-warning" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted"><?php echo $response['stats']['proveedores_con_ordenes'] ?? 0; ?> proveedores con OC</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla de proveedores -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Catálogo de Proveedores</h5>
            <?php if (tiene_permiso('proveedores_add')): ?>
            <div class="d-flex gap-2">
              <a class="btn btn-outline-success btn-sm" href="<?= base_url('compras/Proveedores/descargar_plantilla_excel') ?>">
                <i class="fas fa-file-excel"></i> Plantilla Excel
              </a>
              <button class="btn btn-success btn-sm" type="button" onclick="abrirModalImportarProveedores()">
                <i class="fas fa-file-upload"></i> Carga masiva
              </button>
              <button class="btn btn-primary btn-sm" type="button" onclick="mostrarModalNuevo()">
                <i class="fas fa-plus"></i> Nuevo Proveedor
              </button>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="card-body">
          <!-- Barra CRM -->
          <div class="crm-toolbar mb-3 p-3 bg-light rounded border">
            <div class="row g-2 align-items-end">
              <div class="col-lg-4">
                <label class="form-label small text-muted mb-1">Búsqueda rápida</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                  <input type="text" class="form-control" id="busquedaProveedores" placeholder="Nombre, RFC, teléfono, email...">
                  <button class="btn btn-outline-secondary" type="button" id="btnLimpiarBusquedaProv" title="Limpiar"><i class="fas fa-times"></i></button>
                </div>
              </div>
              <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Estatus</label>
                <select class="form-select form-select-sm" id="filtroEstatus">
                  <option value="">Todos</option>
                  <option value="Activo">Activo</option>
                  <option value="Inactivo">Inactivo</option>
                  <option value="Suspendido">Suspendido</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Tipo</label>
                <select class="form-select form-select-sm" id="filtroTipoProveedor">
                  <option value="">Todos</option>
                  <option value="Materia Prima">Materia Prima</option>
                  <option value="Materiales">Materiales</option>
                  <option value="Servicios">Servicios</option>
                  <option value="Mixto">Mixto</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Con órdenes</label>
                <select class="form-select form-select-sm" id="filtroConOrdenes">
                  <option value="">Todos</option>
                  <option value="si">Con OC</option>
                  <option value="no">Sin OC</option>
                </select>
              </div>
              <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-secondary w-100" onclick="limpiarFiltrosProveedores()">
                  <i class="fas fa-eraser"></i> Limpiar
                </button>
              </div>
            </div>
          </div>

          <!-- DataTable -->
          <table id="tablaProveedores" class="table table-striped table-hover table-sm" style="width:100%">
            <thead class="table-light">
              <tr>
                <th>Código</th>
                <th>Razón Social</th>
                <th>RFC</th>
                <th>Contacto</th>
                <th>Ubicación</th>
                <th>Tipo</th>
                <th>Actividad</th>
                <th>Estatus</th>
                <th width="120">Acciones</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Nuevo/Editar Proveedor -->
<div class="modal fade" id="modalProveedor" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-white" id="modalProveedorTitle">Nuevo Proveedor</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formProveedor">
          <input type="hidden" name="id" id="proveedor_id">
          
          <div class="row">
            <!-- Columna Izquierda -->
            <div class="col-md-6">
              <h6 class="mb-3 text-primary">Información Básica</h6>
              
              <div class="mb-3">
                <label class="form-label">Código</label>
                <input type="text" class="form-control" name="codigo" id="proveedor_codigo" placeholder="Auto-generado si vacío">
                <small class="text-muted">Dejar vacío para generar automáticamente</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="razon_social" id="proveedor_razon_social" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Nombre Comercial</label>
                <input type="text" class="form-control" name="nombre_comercial" id="proveedor_nombre_comercial">
              </div>

              <div class="mb-3">
                <label class="form-label">RFC <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="rfc" id="proveedor_rfc" maxlength="13" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Tipo de Proveedor</label>
                <select class="form-select" name="tipo_proveedor" id="proveedor_tipo_proveedor">
                  <option value="Mixto">Mixto</option>
                  <option value="Materia Prima">Materia Prima</option>
                  <option value="Servicios">Servicios</option>
                  <option value="Materiales">Materiales</option>
                </select>
              </div>

              <h6 class="mb-3 mt-4 text-primary">Contacto</h6>

              <div class="mb-3">
                <label class="form-label">Contacto Principal</label>
                <input type="text" class="form-control" name="contacto_principal" id="proveedor_contacto_principal" placeholder="Nombre del contacto">
              </div>

              <div class="mb-3">
                <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" name="telefono" id="proveedor_telefono" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Teléfono Alternativo</label>
                <input type="tel" class="form-control" name="telefono_alternativo" id="proveedor_telefono_alternativo">
              </div>

              <div class="mb-3">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" id="proveedor_email" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Sitio Web</label>
                <input type="url" class="form-control" name="sitio_web" id="proveedor_sitio_web" placeholder="https://">
              </div>
            </div>

            <!-- Columna Derecha -->
            <div class="col-md-6">
              <h6 class="mb-3 text-primary">Dirección</h6>
              
              <div class="mb-3">
                <label class="form-label">Dirección Completa <span class="text-danger">*</span></label>
                <textarea class="form-control" name="direccion" id="proveedor_direccion" rows="3" required placeholder="Calle, número, colonia"></textarea>
                <small class="text-muted">Incluir calle, número, colonia</small>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Ciudad <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="ciudad" id="proveedor_ciudad" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Estado <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="estado" id="proveedor_estado" required>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Código Postal <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="codigo_postal" id="proveedor_codigo_postal" maxlength="10" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">País</label>
                  <input type="text" class="form-control" name="pais" id="proveedor_pais" value="México">
                </div>
              </div>

              <h6 class="mb-3 mt-4 text-primary">Información Financiera</h6>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Días de Crédito</label>
                  <input type="number" class="form-control" name="dias_credito" id="proveedor_dias_credito" value="0" min="0">
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Límite de Crédito</label>
                  <input type="number" class="form-control" name="limite_credito" id="proveedor_limite_credito" value="0" min="0" step="0.01">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Banco</label>
                <input type="text" class="form-control" name="banco" id="proveedor_banco">
              </div>

              <div class="mb-3">
                <label class="form-label">Cuenta Bancaria</label>
                <input type="text" class="form-control" name="cuenta_bancaria" id="proveedor_cuenta_bancaria">
              </div>

              <div class="mb-3">
                <label class="form-label">Observaciones</label>
                <textarea class="form-control" name="observaciones" id="proveedor_observaciones" rows="2"></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Estatus</label>
                <select class="form-select" name="estatus" id="proveedor_estatus">
                  <option value="Activo">Activo</option>
                  <option value="Inactivo">Inactivo</option>
                  <option value="Suspendido">Suspendido</option>
                </select>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarProveedor()">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Gestión de Insumos del Proveedor -->
<div class="modal fade" id="modalInsumos" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title text-white">Insumos del Proveedor: <span id="nombreProveedorInsumos" class="text-white"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="proveedor_insumos_id">
        
        <!-- Botón agregar insumo -->
        <div class="mb-3">
          <button class="btn btn-success btn-sm" onclick="mostrarFormAgregarInsumo()">
            <i class="fas fa-plus"></i> Agregar Insumo
          </button>
        </div>

        <!-- Formulario agregar/editar insumo (oculto por default) -->
        <div id="formAgregarInsumo" style="display:none;">
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0" id="tituloFormInsumo">Agregar Insumo</h6>
            </div>
            <div class="card-body">
              <form id="formInsumoProveedor">
                <input type="hidden" id="insumo_editando_id">
                
                <div class="mb-3">
                  <label class="form-label">Insumo <span class="text-danger">*</span></label>
                  <select class="form-select" id="insumo_select" required>
                    <option value="">-- Seleccionar --</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Nombre que usa este proveedor para el insumo</label>
                  <input type="text" class="form-control" id="insumo_nombre_proveedor"
                    placeholder="Ej: Resina Acrílica XT-300, Acrilato Premium, etc.">
                  <small class="text-muted">Nombre comercial o técnico que utiliza el proveedor. Será buscable al crear órdenes de compra.</small>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Precio de Compra <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="insumo_precio_compra" step="0.01" min="0" required>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Tiempo de Entrega (días)</label>
                    <input type="number" class="form-control" id="insumo_tiempo_entrega" value="0" min="0">
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Cantidad Mínima</label>
                    <input type="number" class="form-control" id="insumo_cantidad_minima" value="1" min="1" step="0.01">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label">Código / SKU del Proveedor</label>
                    <input type="text" class="form-control" id="insumo_codigo_proveedor" placeholder="SKU del proveedor">
                  </div>
                </div>

                <div class="mb-3">
                  <label class="form-label">Observaciones</label>
                  <textarea class="form-control" id="insumo_observaciones" rows="2"></textarea>
                </div>

                <div class="form-check mb-3">
                  <input class="form-check-input" type="checkbox" id="insumo_es_principal" value="1">
                  <label class="form-check-label" for="insumo_es_principal">Proveedor principal para este insumo</label>
                </div>

                <div class="text-end">
                  <button type="button" class="btn btn-secondary btn-sm" onclick="cancelarFormInsumo()">Cancelar</button>
                  <button type="button" class="btn btn-success btn-sm" onclick="guardarInsumoProveedor()">
                    <i class="fas fa-save"></i> Guardar
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Tabla de insumos del proveedor -->
        <div class="table-responsive">
          <table class="table table-sm table-hover" id="tablaInsumosProveedor">
            <thead class="table-light">
              <tr>
                <th>Código Interno</th>
                <th>Insumo</th>
                <th>Nombre del Proveedor</th>
                <th>Precio</th>
                <th>UM</th>
                <th>Tiempo Entrega</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- Se llena dinámicamente -->
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


<!-- Modal: Carga masiva de proveedores -->
<div class="modal fade" id="modalImportarProveedores" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-file-upload me-1"></i> Carga masiva de proveedores</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-info small mb-3">
          <strong>Instrucciones:</strong>
          <ol class="mb-0 ps-3">
            <li>Descargue la <a href="<?= base_url('compras/Proveedores/descargar_plantilla_excel') ?>" class="alert-link">plantilla Excel</a> con las columnas requeridas.</li>
            <li>La <strong>fila 2</strong> del Excel es solo ejemplo — reemplácela o elimínela antes de importar.</li>
            <li>Capture sus proveedores desde la <strong>fila 2</strong> en la hoja «Proveedores» (no modifique el orden de columnas).</li>
            <li>Campos obligatorios: <strong>Razón social</strong> y <strong>RFC</strong>.</li>
            <li>Los RFC duplicados (en el archivo o en el sistema) se omiten automáticamente.</li>
          </ol>
        </div>

        <div class="mb-3">
          <label class="form-label" for="archivo_importar_proveedores">Archivo Excel (.xlsx / .xls)</label>
          <input type="file" class="form-control" id="archivo_importar_proveedores" accept=".xlsx,.xls">
        </div>

        <div id="importar_proveedores_resultado" class="d-none">
          <div class="border rounded p-3 bg-light small" id="importar_proveedores_resumen"></div>
          <ul class="small mt-2 mb-0" id="importar_proveedores_detalle" style="max-height: 200px; overflow-y: auto;"></ul>
        </div>
      </div>
      <div class="modal-footer">
        <a class="btn btn-outline-success btn-sm" href="<?= base_url('compras/Proveedores/descargar_plantilla_excel') ?>">
          <i class="fas fa-download"></i> Descargar plantilla
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btnProcesarImportacionProveedores" onclick="procesarImportacionProveedores()">
          <i class="fas fa-upload"></i> Importar proveedores
        </button>
      </div>
    </div>
  </div>
</div>

<!-- =====================================================
     Offcanvas: Detalle del Proveedor
===================================================== -->
<div class="offcanvas offcanvas-end" style="width:500px;" tabindex="-1"
     id="offcanvasDetalleProveedor" aria-labelledby="offcanvasProveedorLabel">
  <div class="offcanvas-header bg-primary text-white">
    <h5 id="offcanvasProveedorLabel" class="mb-0 text-white">
      <i class="fas fa-truck text-white"></i> <span id="oc-razon-social" class="text-white">Proveedor</span>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">

    <!-- Header info rápida -->
    <div class="px-3 py-2 bg-light border-bottom d-flex justify-content-between align-items-center">
      <span class="text-muted small">Código: <strong id="oc-codigo">&mdash;</strong></span>
      <span id="oc-estatus-badge"></span>
    </div>

    <!-- Botones de acciones rápidas -->
    <div class="px-3 py-2 border-bottom d-flex gap-2 flex-wrap">
      <?php if (tiene_permiso('proveedores_edit')): ?>
      <button class="btn btn-sm btn-primary" id="oc-btn-editar">
        <i class="fas fa-edit"></i> Editar
      </button>
      <?php endif; ?>
      <?php if (tiene_permiso('proveedores_insumos') || tiene_permiso('proveedores_edit')): ?>
      <button class="btn btn-sm btn-info" id="oc-btn-insumos">
        <i class="fas fa-boxes"></i> Ver Insumos
      </button>
      <?php endif; ?>
      <?php if (tiene_permiso('compras_servicios_recurrentes')): ?>
      <a class="btn btn-sm btn-warning" id="oc-btn-servicios" href="<?= base_url('compras/ServiciosRecurrentes') ?>">
        <i class="fas fa-sync-alt"></i> Servicios
      </a>
      <?php endif; ?>
      <?php if (tiene_permiso('compras_ordenes_add')): ?>
      <a class="btn btn-sm btn-success" id="oc-btn-nueva-orden" href="#">
        <i class="fas fa-file-invoice"></i> Nueva OC
      </a>
      <?php endif; ?>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs px-3 pt-2" id="ocTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="oc-info-tab" data-bs-toggle="tab"
                data-bs-target="#oc-tab-info" type="button">Información</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="oc-ins-tab" data-bs-toggle="tab"
                data-bs-target="#oc-tab-insumos" type="button">Insumos</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="oc-ordenes-tab" data-bs-toggle="tab"
                data-bs-target="#oc-tab-ordenes" type="button">Órdenes</button>
      </li>
      <li class="nav-item" role="presentation" id="oc-servicios-tab-li" style="display:none;">
        <button class="nav-link" id="oc-servicios-tab" data-bs-toggle="tab"
                data-bs-target="#oc-tab-servicios" type="button">Servicios</button>
      </li>
    </ul>

    <div class="tab-content px-3 py-3">

      <!-- TAB INFO -->
      <div class="tab-pane fade show active" id="oc-tab-info" role="tabpanel">
        <table class="table table-sm">
          <tbody id="oc-detalles">
            <tr><td colspan="2" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>
          </tbody>
        </table>
      </div>

      <!-- TAB INSUMOS -->
      <div class="tab-pane fade" id="oc-tab-insumos" role="tabpanel">
        <div class="text-center text-muted py-4" id="oc-insumos-loading">
          <i class="fas fa-spinner fa-spin"></i> Cargando...
        </div>
        <div id="oc-insumos-container" style="display:none;">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Insumo</th>
                  <th>Nom. Proveedor</th>
                  <th class="text-end">Precio</th>
                  <th class="text-center">Entrega</th>
                </tr>
              </thead>
              <tbody id="oc-insumos-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB ÓRDENES -->
      <div class="tab-pane fade" id="oc-tab-ordenes" role="tabpanel">
        <div class="text-center text-muted py-4" id="oc-ordenes-loading">
          <i class="fas fa-spinner fa-spin"></i> Cargando...
        </div>
        <div id="oc-ordenes-container" style="display:none;">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Folio</th>
                  <th>Fecha</th>
                  <th class="text-end">Total</th>
                  <th>Estatus</th>
                  <th>Pago</th>
                  <th width="100">Acciones</th>
                </tr>
              </thead>
              <tbody id="oc-ordenes-tbody"></tbody>
            </table>
          </div>
          <div id="oc-ordenes-paginacion" class="d-grid gap-2 mt-2"></div>
        </div>
      </div>

      <!-- TAB SERVICIOS RECURRENTES -->
      <div class="tab-pane fade" id="oc-tab-servicios" role="tabpanel">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="small text-muted">Pagos mensuales vinculados al proveedor</span>
          <?php if (tiene_permiso('compras_servicios_recurrentes')): ?>
          <button type="button" class="btn btn-sm btn-primary" id="oc-btn-nuevo-servicio" onclick="nuevoServicioDesdeProveedor()">
            <i class="fas fa-plus"></i> Agregar
          </button>
          <?php endif; ?>
        </div>
        <div id="oc-servicios-loading" class="text-center text-muted py-4">
          <i class="fas fa-spinner fa-spin"></i> Cargando...
        </div>
        <div id="oc-servicios-container" style="display:none;">
          <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Servicio</th>
                  <th>Monto</th>
                  <th>Vence</th>
                  <th>Estatus mes</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="oc-servicios-tbody"></tbody>
            </table>
          </div>
        </div>
        <div class="mt-2">
          <a href="<?= base_url('compras/ServiciosRecurrentes') ?>" class="btn btn-sm btn-outline-secondary w-100">
            <i class="fas fa-external-link-alt me-1"></i> Ver calendario completo
          </a>
        </div>
      </div>

    </div>
  </div>
</div>
<!-- /Offcanvas Proveedor -->

<!-- Modal: Detalle de Orden de Compra -->
<div class="modal fade" id="modalDetalleOrdenCompra" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de Orden de Compra <span id="det-oc-folio"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="det-oc-loading" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i></div>
        <div id="det-oc-error" class="alert alert-danger" style="display:none;"></div>
        <div id="det-oc-content" style="display:none;">
          <div class="row mb-3">
            <div class="col-md-6">
              <p class="mb-1"><strong>Proveedor:</strong> <span id="det-oc-proveedor"></span></p>
              <p class="mb-1"><strong>Fecha:</strong> <span id="det-oc-fecha"></span></p>
              <p class="mb-1"><strong>Estatus:</strong> <span id="det-oc-estatus"></span></p>
            </div>
            <div class="col-md-6 text-md-end">
              <p class="mb-1"><strong>Total:</strong> <span id="det-oc-total"></span></p>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead class="table-light">
                <tr><th>Insumo</th><th>Código</th><th class="text-center">Solicitada</th><th class="text-center">Recibida</th><th class="text-end">Precio</th><th class="text-end">Subtotal</th></tr>
              </thead>
              <tbody id="det-oc-detalles"></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <div>
          <button type="button" class="btn btn-warning me-2" id="det-oc-btn-recibir" style="display:none;" onclick="recibirMercanciaProveedor()">
            <i class="fas fa-truck-loading"></i> Recibir mercancía
          </button>
          <a href="#" id="det-oc-btn-pdf" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Ver PDF</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Recibir mercancía (desde proveedor) -->
<div class="modal fade" id="modalRecibirProveedor" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">Recibir mercancía: <span id="prov_folio_recibir"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="prov_recibir_orden_id">
        <div class="alert alert-info small mb-3">
          <i class="fas fa-info-circle"></i> Al confirmar, el <strong>stock de insumos</strong> se actualiza automáticamente para Producción.
        </div>
        <div class="table-responsive">
          <table class="table table-sm" id="tablaRecibirProveedor">
            <thead class="table-light">
              <tr>
                <th>Insumo</th>
                <th>Solicitada</th>
                <th>Ya recibida</th>
                <th>Recibir ahora</th>
                <th>Pendiente</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" onclick="guardarRecepcionProveedor()">
          <i class="fas fa-truck-loading"></i> Confirmar recepción
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';
  
  let tabla;
  let proveedorEditando = null;
  let proveedorInsumosActual = null;
  let busquedaProvTimer = null;
  let provOrdenesActualId = 0;
  let provServiciosActualId = 0;
  const PUEDE_PAGOS = <?php echo tiene_permiso('compras_pagos') ? 'true' : 'false'; ?>;
  const PUEDE_SERVICIOS = <?php echo tiene_permiso('compras_servicios_recurrentes') ? 'true' : 'false'; ?>;
  const PUEDE_RECEPCION = <?php echo tiene_permiso('compras_recepcion') ? 'true' : 'false'; ?>;
  let detOcActualId = 0;
  let detOcActual = null;
  let provOrdenesLimit = 10;
  let provOrdenesOffset = 0;
  let provOrdenesTotal = 0;
  let insumosProveedorCache = {};

  const badgeOrdenCompra = {
    'Borrador': 'secondary',
    'Enviada': 'primary',
    'Confirmada': 'info',
    'En Tránsito': 'warning',
    'Recibida Parcial': 'warning',
    'Recibida': 'success',
    'Cancelada': 'danger'
  };

  function abrirModal(id) {
    const el = document.getElementById(id);
    if (el && typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(el).show();
  }
  function cerrarModal(id) {
    const el = document.getElementById(id);
    if (el && typeof bootstrap !== 'undefined') bootstrap.Modal.getOrCreateInstance(el).hide();
  }

  function toastProveedores(type, title, message) {
    if (typeof showErpToast === 'function') {
      showErpToast({ type: type, module: 'Proveedores', title: title, message: message });
    } else if (typeof notifyShow === 'function') {
      notifyShow(message, type);
    }
  }

  function cerrarOffcanvasProveedor() {
    const el = document.getElementById('offcanvasDetalleProveedor');
    if (el && typeof bootstrap !== 'undefined') {
      bootstrap.Offcanvas.getOrCreateInstance(el).hide();
    }
  }

  function initProveedores() {
    inicializarDataTable();
    cargarInsumosSelect();
    
    $('#filtroEstatus, #filtroTipoProveedor, #filtroConOrdenes').on('change', function() {
      tabla.ajax.reload();
    });

    $('#busquedaProveedores').on('keyup', function() {
      clearTimeout(busquedaProvTimer);
      busquedaProvTimer = setTimeout(function() {
        tabla.search($('#busquedaProveedores').val()).draw();
      }, 350);
    });

    $('#btnLimpiarBusquedaProv').on('click', function() {
      $('#busquedaProveedores').val('');
      tabla.search('').draw();
    });
  }

  window.limpiarFiltrosProveedores = function() {
    $('#filtroEstatus, #filtroTipoProveedor, #filtroConOrdenes').val('');
    $('#busquedaProveedores').val('');
    tabla.search('').ajax.reload();
  };

  function inicializarDataTable() {
    tabla = $('#tablaProveedores').DataTable({
      processing: true,
      serverSide: true,
      dom: 'lrtip',
      ajax: {
        url: '<?=base_url();?>compras/Proveedores/lista_ajax',
        type: 'POST',
        data: function(d) {
          d.peticion = 'ajax';
          d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
          d.filtro_estatus = $('#filtroEstatus').val();
          d.filtro_tipo_proveedor = $('#filtroTipoProveedor').val();
          d.filtro_con_ordenes = $('#filtroConOrdenes').val();
        }
      },
      columns: [
        { data: 0 },
        { data: 1 },
        { data: 2 },
        { data: 3 },
        { data: 4 },
        { data: 5 },
        { data: 6 },
        { data: 7 },
        { data: 8, orderable: false }
      ],
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
      },
      pageLength: 25,
      order: [[1, 'asc']]
    });
  }

  function cargarInsumosSelect() {
    $.post('<?=base_url();?>compras/Proveedores/get_insumos_select_ajax', {
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        let html = '<option value="">-- Seleccionar --</option>';
        result.insumos.forEach(function(ins) {
          html += `<option value="${ins.id}">${ins.text}</option>`;
        });
        $('#insumo_select').html(html);
      }
    });
  }

  window.abrirModalImportarProveedores = function() {
    $('#archivo_importar_proveedores').val('');
    $('#importar_proveedores_resultado').addClass('d-none');
    $('#importar_proveedores_resumen').empty();
    $('#importar_proveedores_detalle').empty();
    $('#btnProcesarImportacionProveedores').prop('disabled', false).html('<i class="fas fa-upload"></i> Importar proveedores');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalImportarProveedores')).show();
  };

  window.procesarImportacionProveedores = function() {
    const input = document.getElementById('archivo_importar_proveedores');
    if (!input || !input.files || !input.files.length) {
      toastProveedores('warning', 'Archivo requerido', 'Seleccione un archivo Excel (.xlsx o .xls).');
      return;
    }

    const formData = new FormData();
    formData.append('archivo_excel', input.files[0]);
    formData.append('peticion', 'ajax');
    formData.append('<?php echo $this->security->get_csrf_token_name(); ?>', '<?php echo $this->security->get_csrf_hash(); ?>');

    const btn = $('#btnProcesarImportacionProveedores');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');

    $.ajax({
      url: '<?= base_url('compras/Proveedores/importar_excel_ajax') ?>',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(result) {
        btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Importar proveedores');

        if (!result || typeof result !== 'object') {
          toastProveedores('danger', 'Error', 'Respuesta inválida del servidor.');
          return;
        }

        const res = result.resultado || {};
        $('#importar_proveedores_resultado').removeClass('d-none');
        $('#importar_proveedores_resumen').html(
          '<strong>Resultado:</strong> ' + (result.message || '') +
          '<br><span class="text-success">Insertados: ' + (res.inserted || 0) + '</span> · ' +
          '<span class="text-warning">Omitidos: ' + (res.skipped || 0) + '</span> · ' +
          '<span class="text-danger">Errores: ' + (res.errors || 0) + '</span>'
        );

        const detalle = (res.messages || []).map(function(msg) {
          return '<li>' + $('<div>').text(msg).html() + '</li>';
        }).join('');
        $('#importar_proveedores_detalle').html(detalle || '<li class="text-muted">Sin detalles adicionales.</li>');

        toastProveedores(
          result.partial ? 'warning' : (result.success ? 'success' : 'warning'),
          result.success ? 'Importación completada' : (result.partial ? 'Importación parcial' : 'Importación con observaciones'),
          result.message || 'Proceso finalizado.'
        );

        if ((res.inserted || 0) > 0 && typeof tabla !== 'undefined' && tabla) {
          tabla.ajax.reload(null, false);
        }
      },
      error: function() {
        btn.prop('disabled', false).html('<i class="fas fa-upload"></i> Importar proveedores');
        toastProveedores('danger', 'Error de conexión', 'No se pudo procesar el archivo.');
      }
    });
  };

  window.mostrarModalNuevo = function() {
    proveedorEditando = null;
    $('#modalProveedorTitle').text('Nuevo Proveedor');
    $('#formProveedor')[0].reset();
    $('#proveedor_id').val('');
    $('#proveedor_estatus').val('Activo');
    $('#proveedor_pais').val('México');
    $('#proveedor_tipo_proveedor').val('Mixto');
    abrirModal('modalProveedor');
  };

  window.mostrarModalEditar = function(id) {
    proveedorEditando = id;
    $('#modalProveedorTitle').text('Editar Proveedor');
    
    $.post('<?=base_url();?>compras/Proveedores/get_proveedor_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      if(result.success) {
        const p = result.proveedor;
        $('#proveedor_id').val(p.id);
        $('#proveedor_codigo').val(p.codigo);
        $('#proveedor_razon_social').val(p.razon_social);
        $('#proveedor_nombre_comercial').val(p.nombre_comercial);
        $('#proveedor_rfc').val(p.rfc);
        $('#proveedor_tipo_proveedor').val(p.tipo_proveedor);
        $('#proveedor_contacto_principal').val(p.contacto_principal);
        $('#proveedor_telefono').val(p.telefono);
        $('#proveedor_telefono_alternativo').val(p.telefono_alternativo);
        $('#proveedor_email').val(p.email);
        $('#proveedor_sitio_web').val(p.sitio_web);
        $('#proveedor_direccion').val(p.direccion);
        $('#proveedor_ciudad').val(p.ciudad);
        $('#proveedor_estado').val(p.estado);
        $('#proveedor_codigo_postal').val(p.codigo_postal);
        $('#proveedor_pais').val(p.pais);
        $('#proveedor_dias_credito').val(p.dias_credito);
        $('#proveedor_limite_credito').val(p.limite_credito);
        $('#proveedor_banco').val(p.banco);
        $('#proveedor_cuenta_bancaria').val(p.cuenta_bancaria);
        $('#proveedor_observaciones').val(p.observaciones);
        $('#proveedor_estatus').val(p.estatus);
        
        abrirModal('modalProveedor');
      } else {
        toastProveedores('danger', 'Error', result.message || 'No se pudo cargar el proveedor.');
      }
    }).fail(function() {
      toastProveedores('danger', 'Error de conexión', 'No se pudo cargar el proveedor.');
    });
  };

  window.guardarProveedor = function() {
    const form = document.getElementById('formProveedor');
    if (form && !form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const formData = $('#formProveedor').serialize();
    const url = proveedorEditando ?
      '<?=base_url();?>compras/Proveedores/editar_ajax' :
      '<?=base_url();?>compras/Proveedores/crear_ajax';

    $.post(url,
      formData + '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>',
      function(result) {
        try { result = JSON.parse(result); } catch (e) {
          toastProveedores('danger', 'Error', 'Respuesta inválida del servidor.');
          return;
        }
        if(result.success) {
          toastProveedores('success', proveedorEditando ? 'Proveedor actualizado' : 'Proveedor creado', result.message);
          cerrarModal('modalProveedor');
          tabla.ajax.reload();
        } else {
          toastProveedores('danger', 'Error', result.message || 'No se pudo guardar el proveedor.');
        }
      }
    ).fail(function() {
      toastProveedores('danger', 'Error de conexión', 'No se pudo contactar al servidor.');
    });
  };

  window.eliminarProveedor = function(id) {
    if(!confirm('¿Estás seguro de eliminar este proveedor?')) return;

    $.post('<?=base_url();?>compras/Proveedores/eliminar_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      toastProveedores(result.success ? 'success' : 'danger', result.success ? 'Eliminado' : 'Error', result.message || '');
      if(result.success) {
        tabla.ajax.reload();
      }
    }).fail(function() {
      toastProveedores('danger', 'Error de conexión', 'No se pudo eliminar el proveedor.');
    });
  };

  // ============ GESTIÓN DE INSUMOS ============

  window.mostrarModalInsumos = function(proveedorId) {
    proveedorInsumosActual = proveedorId;
    $('#proveedor_insumos_id').val(proveedorId);
    $('#formAgregarInsumo').hide();
    
    // Obtener nombre del proveedor
    $.post('<?=base_url();?>compras/Proveedores/get_proveedor_ajax', {
      'id': proveedorId,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        $('#nombreProveedorInsumos').text(result.proveedor.razon_social);
      }
    });
    
    cargarInsumosProveedor(proveedorId);
    abrirModal('modalInsumos');
  };

  function cargarInsumosProveedor(proveedorId) {
    $.post('<?=base_url();?>compras/Proveedores/get_insumos_proveedor_ajax', {
      'proveedor_id': proveedorId,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) {
        toastProveedores('danger', 'Error', 'No se pudieron cargar los insumos.');
        return;
      }
      if(result.success) {
        insumosProveedorCache = {};
        let html = '';
        if(result.insumos.length === 0) {
          html = '<tr><td colspan="7" class="text-center text-muted">No hay insumos relacionados</td></tr>';
        } else {
          result.insumos.forEach(function(ins) {
            insumosProveedorCache[ins.insumo_id] = ins;
            const nombreProv = ins.nombre_proveedor ? `<span class="badge bg-info text-white">${$('<div>').text(ins.nombre_proveedor).html()}</span>` : '<span class="text-muted">—</span>';
            const principal = ins.es_proveedor_principal == 1 ? ' <span class="badge bg-warning text-dark" title="Principal">★</span>' : '';
            html += `
              <tr>
                <td><small>${ins.codigo || ''}</small></td>
                <td><strong>${ins.nombre_tecnico || ''}</strong>${principal}</td>
                <td>${nombreProv}</td>
                <td>$${parseFloat(ins.precio_compra || 0).toFixed(2)}</td>
                <td><span class="badge bg-light text-dark">${ins.unidad_medida || ''}</span></td>
                <td>${ins.tiempo_entrega_dias || 0} días</td>
                <td class="text-nowrap">
                  <button type="button" class="btn btn-sm btn-primary" onclick="editarInsumoProveedor(${ins.insumo_id})" title="Editar">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button type="button" class="btn btn-sm btn-danger" onclick="eliminarInsumoProveedor(${ins.insumo_id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            `;
          });
        }
        $('#tablaInsumosProveedor tbody').html(html);
      }
    }).fail(function() {
      toastProveedores('danger', 'Error de conexión', 'No se pudieron cargar los insumos.');
    });
  }

  window.mostrarFormAgregarInsumo = function() {
    $('#tituloFormInsumo').text('Agregar Insumo');
    $('#formInsumoProveedor')[0].reset();
    $('#insumo_editando_id').val('');
    $('#insumo_es_principal').prop('checked', false);
    $('#insumo_select').prop('disabled', false);
    $('#formAgregarInsumo').slideDown();
  };

  window.cancelarFormInsumo = function() {
    $('#formAgregarInsumo').slideUp();
    $('#formInsumoProveedor')[0].reset();
    $('#insumo_es_principal').prop('checked', false);
  };

  window.editarInsumoProveedor = function(insumoId) {
    const ins = insumosProveedorCache[insumoId];
    if (!ins) {
      toastProveedores('warning', 'Insumo no encontrado', 'Recargue la lista e intente de nuevo.');
      return;
    }
    $('#tituloFormInsumo').text('Editar Insumo del Proveedor');
    $('#insumo_editando_id').val(insumoId);
    $('#insumo_select').val(insumoId).prop('disabled', true);
    $('#insumo_precio_compra').val(ins.precio_compra);
    $('#insumo_tiempo_entrega').val(ins.tiempo_entrega_dias);
    $('#insumo_cantidad_minima').val(ins.cantidad_minima);
    $('#insumo_codigo_proveedor').val(ins.codigo_proveedor || '');
    $('#insumo_nombre_proveedor').val(ins.nombre_proveedor || '');
    $('#insumo_observaciones').val(ins.observaciones || '');
    $('#insumo_es_principal').prop('checked', ins.es_proveedor_principal == 1);
    $('#formAgregarInsumo').slideDown();
  };

  window.guardarInsumoProveedor = function() {
    const insumoEditandoId = $('#insumo_editando_id').val();
    const insumoId = insumoEditandoId || $('#insumo_select').val();
    const precio = parseFloat($('#insumo_precio_compra').val());

    if (!insumoId) {
      toastProveedores('warning', 'Insumo requerido', 'Seleccione un insumo de la lista.');
      return;
    }
    if (!precio || precio <= 0) {
      toastProveedores('warning', 'Precio requerido', 'Indique un precio de compra válido.');
      return;
    }

    const url = insumoEditandoId ?
      '<?=base_url();?>compras/Proveedores/actualizar_precio_insumo_ajax' :
      '<?=base_url();?>compras/Proveedores/agregar_insumo_ajax';

    const data = {
      'proveedor_id': proveedorInsumosActual,
      'insumo_id': insumoId,
      'precio_compra': precio,
      'tiempo_entrega_dias': $('#insumo_tiempo_entrega').val() || 0,
      'cantidad_minima': $('#insumo_cantidad_minima').val() || 1,
      'codigo_proveedor': $('#insumo_codigo_proveedor').val(),
      'nombre_proveedor': $('#insumo_nombre_proveedor').val(),
      'observaciones': $('#insumo_observaciones').val(),
      'es_proveedor_principal': $('#insumo_es_principal').is(':checked') ? 1 : 0,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    };

    $.post(url, data, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      toastProveedores(result.success ? 'success' : 'danger', result.success ? 'Insumo guardado' : 'Error', result.message || '');
      if(result.success) {
        cancelarFormInsumo();
        cargarInsumosProveedor(proveedorInsumosActual);
        if (typeof tabla !== 'undefined' && tabla) tabla.ajax.reload(null, false);
      }
    }).fail(function() {
      toastProveedores('danger', 'Error de conexión', 'No se pudo guardar el insumo.');
    });
  };

  window.eliminarInsumoProveedor = function(insumoId) {
    if(!confirm('¿Eliminar este insumo del proveedor?')) return;

    $.post('<?=base_url();?>compras/Proveedores/eliminar_insumo_ajax', {
      'proveedor_id': proveedorInsumosActual,
      'insumo_id': insumoId,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      try { result = JSON.parse(result); } catch (e) { return; }
      toastProveedores(result.success ? 'success' : 'danger', result.success ? 'Insumo eliminado' : 'Error', result.message || '');
      if(result.success) {
        cargarInsumosProveedor(proveedorInsumosActual);
        if (typeof tabla !== 'undefined' && tabla) tabla.ajax.reload(null, false);
      }
    }).fail(function() {
      toastProveedores('danger', 'Error de conexión', 'No se pudo eliminar el insumo.');
    });
  };

  // --------------------------------------------------
  // VER DETALLE EN OFFCANVAS
  // --------------------------------------------------
  window.verDetalleProveedor = function(id) {
    // Abrir offcanvas
    var oc = new bootstrap.Offcanvas(document.getElementById('offcanvasDetalleProveedor'));
    oc.show();

    // Reset UI
    provOrdenesActualId = id;
    provOrdenesOffset = 0;
    $('#oc-detalles').html('<tr><td colspan="2" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#oc-razon-social').text('Proveedor');
    $('#oc-codigo').text('...');
    $('#oc-estatus-badge').html('');
    $('#oc-insumos-loading').show();
    $('#oc-insumos-container').hide();
    $('#oc-ordenes-tbody').html('');
    $('#oc-ordenes-paginacion').html('');

    // Activar tab Información por defecto
    const tabInfo = document.getElementById('oc-info-tab');
    if (tabInfo && typeof bootstrap !== 'undefined') bootstrap.Tab.getOrCreateInstance(tabInfo).show();

    // Botones de acción rápida
    if ($('#oc-btn-editar').length) {
      $('#oc-btn-editar').off('click').on('click', function() {
        cerrarOffcanvasProveedor();
        setTimeout(function() { mostrarModalEditar(id); }, 250);
      });
    }
    if ($('#oc-btn-insumos').length) {
      $('#oc-btn-insumos').off('click').on('click', function() {
        cerrarOffcanvasProveedor();
        setTimeout(function() { mostrarModalInsumos(id); }, 250);
      });
    }
    if ($('#oc-btn-nueva-orden').length) {
      $('#oc-btn-nueva-orden').attr('href', '<?=base_url('compras/OrdenesCompra');?>?nueva_proveedor=' + id);
    }

    // Cargar datos del proveedor
    $.post('<?=base_url();?>compras/Proveedores/get_proveedor_ajax', {
      'id': id, 'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(res) {
      res = JSON.parse(res);
      if(!res.success) return;
      var p = res.proveedor;

      // Header
      $('#oc-razon-social').text(p.razon_social);
      $('#oc-codigo').text(p.codigo || '-');
      var badgeClass = p.estatus === 'Activo' ? 'bg-success' : 'bg-secondary';
      $('#oc-estatus-badge').html('<span class="badge ' + badgeClass + '">' + p.estatus + '</span>');

      // Filas de información
      function fila(label, valor) {
        return '<tr><th class="text-muted fw-normal" style="width:40%;">' + label + '</th><td>' + (valor || '<span class="text-muted">-</span>') + '</td></tr>';
      }

      var html = '';
      html += '<tr><td colspan="2" class="bg-light fw-bold text-uppercase small py-1 ps-2" style="font-size:0.7rem;letter-spacing:1px;">General</td></tr>';
      html += fila('Nombre Comercial', p.nombre_comercial);
      html += fila('RFC', p.rfc);
      html += fila('Tipo', p.tipo_proveedor);
      html += fila('País', p.pais);
      html += '<tr><td colspan="2" class="bg-light fw-bold text-uppercase small py-1 ps-2" style="font-size:0.7rem;letter-spacing:1px;">Contacto</td></tr>';
      html += fila('Contacto', p.contacto_principal);
      html += fila('Teléfono', p.telefono ? '<a href="tel:' + p.telefono + '">' + p.telefono + '</a>' : null);
      html += fila('Email', p.email ? '<a href="mailto:' + p.email + '">' + p.email + '</a>' : null);
      html += fila('Sitio Web', p.sitio_web ? '<a href="' + p.sitio_web + '" target="_blank">' + p.sitio_web + '</a>' : null);
      html += '<tr><td colspan="2" class="bg-light fw-bold text-uppercase small py-1 ps-2" style="font-size:0.7rem;letter-spacing:1px;">Dirección</td></tr>';
      html += fila('Dirección', p.direccion);
      html += fila('Ciudad', p.ciudad);
      html += fila('Estado', p.estado);
      html += fila('C.P.', p.codigo_postal);
      html += '<tr><td colspan="2" class="bg-light fw-bold text-uppercase small py-1 ps-2" style="font-size:0.7rem;letter-spacing:1px;">Condiciones Comerciales</td></tr>';
      html += fila('Días de Crédito', p.dias_credito ? p.dias_credito + ' días' : null);
      html += fila('Límite de Crédito', p.limite_credito ? '$' + parseFloat(p.limite_credito).toLocaleString('es-MX', {minimumFractionDigits:2}) : null);
      html += fila('Banco', p.banco);
      html += fila('Calificación', p.calificacion ? '⭐'.repeat(parseInt(p.calificacion)) : null);
      if(p.observaciones) {
        html += '<tr><td colspan="2" class="bg-light fw-bold text-uppercase small py-1 ps-2" style="font-size:0.7rem;letter-spacing:1px;">Observaciones</td></tr>';
        html += '<tr><td colspan="2" class="text-muted small">' + p.observaciones + '</td></tr>';
      }

      $('#oc-detalles').html(html);

      // Tab servicios para proveedores tipo Servicios o Mixto
      if (p.tipo_proveedor === 'Servicios' || p.tipo_proveedor === 'Mixto') {
        $('#oc-servicios-tab-li').show();
        provServiciosActualId = id;
        if ($('#oc-btn-servicios').length) {
          $('#oc-btn-servicios').attr('href', '<?= base_url('compras/ServiciosRecurrentes') ?>?proveedor_id=' + id);
        }
      } else {
        $('#oc-servicios-tab-li').hide();
      }
    });

    // Cargar insumos cuando el tab se activa
    $('#oc-ins-tab').off('shown.bs.tab').on('shown.bs.tab', function() {
      cargarInsumosOC(id);
    });

    $('#oc-ordenes-tab').off('shown.bs.tab').on('shown.bs.tab', function() {
      if($('#oc-ordenes-tbody').is(':empty')) cargarOrdenesOC(id);
    });

    $('#oc-servicios-tab').off('shown.bs.tab').on('shown.bs.tab', function() {
      cargarServiciosOC(id);
    });
  };

  window.nuevoServicioDesdeProveedor = function() {
    if (!provServiciosActualId) return;
    window.location.href = '<?= base_url('compras/ServiciosRecurrentes') ?>?proveedor_id=' + provServiciosActualId + '&nuevo=1';
  };

  function badgePagoOC(estatus, saldo) {
    const map = { Pendiente: 'danger', Parcial: 'warning', Pagado: 'success', 'Sin adeudo': 'secondary' };
    let html = '<span class="badge bg-' + (map[estatus] || 'secondary') + '">' + (estatus || '—') + '</span>';
    if (parseFloat(saldo || 0) > 0) {
      html += '<br><small class="text-danger">$' + parseFloat(saldo).toFixed(2) + '</small>';
    }
    return html;
  }

  function cargarServiciosOC(proveedorId) {
    $('#oc-servicios-loading').show();
    $('#oc-servicios-container').hide();
    $.post('<?= base_url('compras/ServiciosRecurrentes/pagos_proveedor_ajax') ?>', {
      proveedor_id: proveedorId,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(res) {
      try { res = JSON.parse(res); } catch(e) { return; }
      $('#oc-servicios-loading').hide();
      if (!res.success) return;
      let tbody = '';
      const pagosMap = {};
      (res.pagos_mes || []).forEach(function(p) { pagosMap[p.servicio_recurrente_id] = p; });
      if (!res.servicios || res.servicios.length === 0) {
        tbody = '<tr><td colspan="5" class="text-center text-muted py-3">Sin servicios recurrentes. <a href="<?= base_url('compras/ServiciosRecurrentes') ?>">Agregar uno</a></td></tr>';
      } else {
        res.servicios.forEach(function(s) {
          const p = pagosMap[s.id];
          const est = p ? p.estatus : '—';
          const badge = { Pendiente: 'warning', Pagado: 'success', Vencido: 'danger' };
          tbody += '<tr><td><strong>' + s.nombre_servicio + '</strong><br><small class="text-muted">' + s.tipo_servicio + '</small></td>';
          tbody += '<td>$' + parseFloat(s.monto_estimado).toFixed(2) + '</td>';
          tbody += '<td>Día ' + s.dia_vencimiento + '</td>';
          tbody += '<td><span class="badge bg-' + (badge[est] || 'secondary') + '">' + est + '</span></td><td>';
          if (p && PUEDE_PAGOS && (est === 'Pendiente' || est === 'Vencido')) {
            tbody += '<button class="btn btn-sm btn-success" onclick="pagarServicioProveedor(' + p.id + ', ' + p.monto + ')"><i class="fas fa-check"></i></button>';
          }
          tbody += '</td></tr>';
        });
      }
      $('#oc-servicios-tbody').html(tbody);
      $('#oc-servicios-container').show();
    });
  }

  window.pagarServicioProveedor = function(pagoId, monto) {
    if (!PUEDE_PAGOS || !confirm('¿Registrar pago de $' + parseFloat(monto).toFixed(2) + '?')) return;
    $.post('<?= base_url('compras/ServiciosRecurrentes/registrar_pago_ajax') ?>', {
      pago_id: pagoId,
      monto: monto,
      fecha_pago: new Date().toISOString().slice(0, 10),
      referencia: 'Pago desde proveedor',
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(res) {
      try { res = JSON.parse(res); } catch(e) {}
      toastProveedores(res.success ? 'success' : 'danger', res.success ? 'Pagado' : 'Error', res.message || '');
      if (res.success && provServiciosActualId) cargarServiciosOC(provServiciosActualId);
    });
  };

  function formatearFechaMX(fecha) {
    if(!fecha) return '-';
    var d = new Date(fecha);
    if(isNaN(d.getTime())) return '-';
    return d.toLocaleDateString('es-MX');
  }

  function renderBadgeOC(estatus) {
    return '<span class="badge bg-' + (badgeOrdenCompra[estatus] || 'secondary') + '">' + estatus + '</span>';
  }

  function botonCargarMasOC(offset, total, limit) {
    if(offset + limit >= total) return '';
    return '<button class="btn btn-outline-secondary btn-sm" onclick="cargarMasOrdenesCompra()"><i class="fas fa-chevron-down"></i> Cargar más (' + (total - offset - limit) + ' restantes)</button>';
  }

  function cargarOrdenesOC(proveedorId, append) {
    if(!append) {
      provOrdenesOffset = 0;
      $('#oc-ordenes-tbody').html('');
    }
    $('#oc-ordenes-loading').show();
    $('#oc-ordenes-paginacion').html('');

    $.post('<?=base_url();?>compras/Proveedores/get_historial_ordenes_compra_ajax', {
      proveedor_id: proveedorId,
      limit: provOrdenesLimit,
      offset: provOrdenesOffset,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(res) {
      res = JSON.parse(res);
      $('#oc-ordenes-loading').hide();
      provOrdenesTotal = res.total || 0;

      var tbody = '';
      if(res.success && res.ordenes && res.ordenes.length > 0) {
        res.ordenes.forEach(function(oc) {
          var pdfUrl = '<?=base_url();?>compras/OrdenesCompra/generar_pdf/' + oc.id;
          tbody += '<tr>';
          tbody += '<td><strong>' + oc.folio + '</strong></td>';
          tbody += '<td>' + formatearFechaMX(oc.fecha_orden) + '</td>';
          tbody += '<td class="text-end">$' + parseFloat(oc.total || 0).toLocaleString('es-MX', {minimumFractionDigits:2}) + '</td>';
          tbody += '<td>' + renderBadgeOC(oc.estatus) + '</td>';
          tbody += '<td>' + badgePagoOC(oc.estatus_pago, oc.saldo_pendiente) + '</td>';
          tbody += '<td>';
          tbody += '<div class="btn-group btn-group-sm">';
          tbody += '<button type="button" class="btn btn-info" onclick="verDetalleOrdenCompra(' + oc.id + ')" title="Ver detalle"><i class="fas fa-eye"></i></button>';
          if (puedeRecibirOC(oc.estatus)) {
            tbody += '<button type="button" class="btn btn-warning" onclick="recibirMercanciaProveedor(' + oc.id + ')" title="Recibir mercancía"><i class="fas fa-truck-loading"></i></button>';
          }
          tbody += '<a href="' + pdfUrl + '" target="_blank" class="btn btn-danger" title="Ver PDF"><i class="fas fa-file-pdf"></i></a>';
          tbody += '</div>';
          tbody += '</td>';
          tbody += '</tr>';
        });
        if(append) {
          $('#oc-ordenes-tbody').append(tbody);
        } else {
          $('#oc-ordenes-tbody').html(tbody);
        }
        provOrdenesOffset = res.offset + res.ordenes.length;
        $('#oc-ordenes-paginacion').html(botonCargarMasOC(provOrdenesOffset, provOrdenesTotal, provOrdenesLimit));
        $('#oc-ordenes-container').show();
      } else if(!append) {
        tbody = '<tr><td colspan="5" class="text-center text-muted py-3">Sin órdenes de compra registradas</td></tr>';
        $('#oc-ordenes-tbody').html(tbody);
        $('#oc-ordenes-container').show();
      }
    }).fail(function() {
      $('#oc-ordenes-loading').hide();
      $('#oc-ordenes-tbody').html('<tr><td colspan="5" class="text-center text-danger py-3">Error al cargar órdenes</td></tr>');
      $('#oc-ordenes-container').show();
    });
  }

  window.cargarMasOrdenesCompra = function() {
    cargarOrdenesOC(provOrdenesActualId, true);
  };

  function puedeRecibirOC(estatus) {
    return PUEDE_RECEPCION && ['Enviada', 'Confirmada', 'En Tránsito', 'Recibida Parcial'].indexOf(estatus) >= 0;
  }

  function llenarTablaRecibirProveedor(orden) {
    $('#prov_recibir_orden_id').val(orden.id);
    $('#prov_folio_recibir').text(orden.folio);
    let html = '';
    (orden.detalles || []).forEach(function(det) {
      const solicitada = parseFloat(det.cantidad_solicitada || 0);
      const ya = parseFloat(det.cantidad_recibida || 0);
      const pendiente = Math.max(0, solicitada - ya);
      if (pendiente <= 0) return;
      html += '<tr>';
      html += '<td>' + (det.nombre_tecnico || '-') + ' <small class="text-muted">(' + (det.unidad_medida || '') + ')</small></td>';
      html += '<td>' + solicitada + '</td><td>' + ya + '</td>';
      html += '<td><input type="number" class="form-control form-control-sm prov-recibir-cantidad" data-detalle-id="' + det.id + '" data-max="' + pendiente + '" value="' + pendiente + '" min="0" max="' + pendiente + '" step="0.01"></td>';
      html += '<td class="prov-pendiente-' + det.id + '">' + pendiente.toFixed(2) + '</td>';
      html += '</tr>';
    });
    $('#tablaRecibirProveedor tbody').html(html || '<tr><td colspan="5" class="text-center text-muted">Todo recibido</td></tr>');
    $('.prov-recibir-cantidad').off('input').on('input', function() {
      const id = $(this).data('detalle-id');
      const max = parseFloat($(this).data('max'));
      const val = parseFloat($(this).val()) || 0;
      $('.prov-pendiente-' + id).text(Math.max(0, max - val).toFixed(2));
    });
  }

  window.recibirMercanciaProveedor = function(ordenId) {
    const id = ordenId || detOcActualId;
    if (!id || !PUEDE_RECEPCION) return;
    $.post('<?=base_url();?>compras/OrdenesCompra/get_orden_ajax', {
      id: id, peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(res) {
      try { res = JSON.parse(res); } catch(e) { return; }
      if (!res.success || !res.orden) {
        toastProveedores('danger', 'Error', 'No se pudo cargar la orden.');
        return;
      }
      if (!puedeRecibirOC(res.orden.estatus)) {
        toastProveedores('warning', 'No disponible', 'Esta orden no admite recepción en su estatus actual.');
        return;
      }
      cerrarModal('modalDetalleOrdenCompra');
      llenarTablaRecibirProveedor(res.orden);
      abrirModal('modalRecibirProveedor');
    });
  };

  window.guardarRecepcionProveedor = function() {
    const ordenId = $('#prov_recibir_orden_id').val();
    const detalles = [];
    $('.prov-recibir-cantidad').each(function() {
      const cantidad = parseFloat($(this).val()) || 0;
      if (cantidad > 0) {
        detalles.push({ detalle_id: $(this).data('detalle-id'), cantidad_recibida: cantidad });
      }
    });
    if (!detalles.length) {
      toastProveedores('warning', 'Cantidades', 'Indique al menos una cantidad a recibir.');
      return;
    }
    $.ajax({
      url: '<?=base_url();?>compras/OrdenesCompra/recibir_mercancia_ajax',
      type: 'POST',
      data: {
        orden_id: ordenId,
        detalles: JSON.stringify(detalles),
        peticion: 'ajax',
        '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
      },
      success: function(result) {
        try { result = JSON.parse(result); } catch(e) { return; }
        toastProveedores(result.success ? 'success' : 'danger', result.success ? 'Recepción OK' : 'Error', result.message || '');
        if (result.success) {
          cerrarModal('modalRecibirProveedor');
          if (provOrdenesActualId) {
            cargarOrdenesOC(provOrdenesActualId);
          }
          if (typeof tabla !== 'undefined' && tabla) tabla.ajax.reload(null, false);
        }
      },
      error: function() {
        toastProveedores('danger', 'Error', 'No se pudo procesar la recepción.');
      }
    });
  };

  window.verDetalleOrdenCompra = function(ordenId) {
    abrirModal('modalDetalleOrdenCompra');
    $('#det-oc-loading').show();
    $('#det-oc-content').hide();
    $('#det-oc-error').hide();
    $('#det-oc-detalles').html('');
    $('#det-oc-btn-pdf').attr('href', '<?=base_url();?>compras/OrdenesCompra/generar_pdf/' + ordenId);

    $.post('<?=base_url();?>compras/OrdenesCompra/get_orden_ajax', {
      id: ordenId,
      peticion: 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(res) {
      res = JSON.parse(res);
      $('#det-oc-loading').hide();
      if(!res.success || !res.orden) {
        $('#det-oc-error').text('No se pudo cargar el detalle de la orden.').show();
        return;
      }
      var oc = res.orden;
      detOcActualId = ordenId;
      detOcActual = oc;
      $('#det-oc-folio').text(oc.folio || '-');
      $('#det-oc-proveedor').text(oc.razon_social || '-');
      $('#det-oc-fecha').text(formatearFechaMX(oc.fecha_orden));
      $('#det-oc-estatus').html(renderBadgeOC(oc.estatus));
      $('#det-oc-total').html('<strong>$' + parseFloat(oc.total || 0).toLocaleString('es-MX', {minimumFractionDigits:2}) + '</strong>');

      if (puedeRecibirOC(oc.estatus)) {
        $('#det-oc-btn-recibir').show();
      } else {
        $('#det-oc-btn-recibir').hide();
      }

      var detHtml = '';
      if(oc.detalles && oc.detalles.length) {
        oc.detalles.forEach(function(d) {
          var subtotal = (parseFloat(d.cantidad_solicitada || d.cantidad || 0) * parseFloat(d.precio_unitario || 0));
          var recibida = parseFloat(d.cantidad_recibida || 0);
          var solicitada = parseFloat(d.cantidad_solicitada || d.cantidad || 0);
          detHtml += '<tr>';
          detHtml += '<td>' + (d.nombre_tecnico || d.nombre || '-') + '</td>';
          detHtml += '<td><small class="text-muted">' + (d.codigo || '-') + '</small></td>';
          detHtml += '<td class="text-center">' + solicitada.toLocaleString('es-MX') + '</td>';
          detHtml += '<td class="text-center">' + recibida.toLocaleString('es-MX');
          if (recibida >= solicitada && solicitada > 0) {
            detHtml += ' <span class="badge bg-success ms-1">✓</span>';
          } else if (recibida > 0) {
            detHtml += ' <span class="badge bg-warning ms-1">Parcial</span>';
          }
          detHtml += '</td>';
          detHtml += '<td class="text-end">$' + parseFloat(d.precio_unitario || 0).toLocaleString('es-MX', {minimumFractionDigits:2}) + '</td>';
          detHtml += '<td class="text-end">$' + subtotal.toLocaleString('es-MX', {minimumFractionDigits:2}) + '</td>';
          detHtml += '</tr>';
        });
      } else {
        detHtml = '<tr><td colspan="6" class="text-center text-muted">Sin insumos registrados</td></tr>';
      }
      $('#det-oc-detalles').html(detHtml);
      $('#det-oc-content').show();
    }).fail(function() {
      $('#det-oc-loading').hide();
      $('#det-oc-error').text('Error de conexión al cargar la orden.').show();
    });
  };

  function cargarInsumosOC(proveedorId) {
    $('#oc-insumos-loading').show();
    $('#oc-insumos-container').hide();

    $.post('<?=base_url();?>compras/Proveedores/get_insumos_proveedor_ajax', {
      'proveedor_id': proveedorId, 'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(res) {
      res = JSON.parse(res);
      $('#oc-insumos-loading').hide();
      var tbody = '';
      if(res.success && res.insumos && res.insumos.length > 0) {
        res.insumos.forEach(function(ins) {
          var nomProv = ins.nombre_proveedor
            ? '<span class="badge bg-info text-white">' + ins.nombre_proveedor + '</span>'
            : '<span class="text-muted">-</span>';
          tbody += '<tr>';
          tbody += '<td><strong class="small">' + ins.nombre_tecnico + '</strong><br><span class="text-muted" style="font-size:0.75rem;">' + (ins.codigo || '') + '</span></td>';
          tbody += '<td>' + nomProv + '</td>';
          tbody += '<td class="text-end">' + (ins.precio_compra ? '$' + parseFloat(ins.precio_compra).toLocaleString('es-MX', {minimumFractionDigits:2}) : '-') + '</td>';
          tbody += '<td class="text-center">' + (ins.tiempo_entrega_dias ? ins.tiempo_entrega_dias + ' días' : '-') + '</td>';
          tbody += '</tr>';
        });
        $('#oc-insumos-tbody').html(tbody);
        $('#oc-insumos-container').show();
      } else {
        $('#oc-insumos-tbody').html('<tr><td colspan="4" class="text-center text-muted py-3">Sin insumos asignados</td></tr>');
        $('#oc-insumos-container').show();
      }
    });
  }

  // Inicializar cuando jQuery esté disponible
  if (typeof jQuery !== 'undefined') {
    $(document).ready(initProveedores);
  } else {
    window.addEventListener('load', function() {
      if (typeof jQuery !== 'undefined') {
        $(document).ready(initProveedores);
      }
    });
  }
})();
</script>
