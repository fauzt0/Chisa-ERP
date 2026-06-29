<?php
/**
 * Vista principal de Proveedores
 * Listado de proveedores con DataTables
 */
?>
<div class="container-fluid p-0">

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
            <button class="btn btn-primary btn-sm" onclick="mostrarModalNuevo()">
              <i class="fas fa-plus"></i> Nuevo Proveedor
            </button>
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
                  <option value="Insumos">Insumos</option>
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
    <div class="px-3 py-2 border-bottom d-flex gap-2">
      <button class="btn btn-sm btn-primary" id="oc-btn-editar">
        <i class="fas fa-edit"></i> Editar
      </button>
      <button class="btn btn-sm btn-info" id="oc-btn-insumos">
        <i class="fas fa-boxes"></i> Ver Insumos
      </button>
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
                  <th></th>
                </tr>
              </thead>
              <tbody id="oc-ordenes-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<!-- /Offcanvas Proveedor -->

<script>
(function() {
  'use strict';
  
  let tabla;
  let proveedorEditando = null;
  let proveedorInsumosActual = null;
  let busquedaProvTimer = null;

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

  window.mostrarModalNuevo = function() {
    proveedorEditando = null;
    $('#modalProveedorTitle').text('Nuevo Proveedor');
    $('#formProveedor')[0].reset();
    $('#proveedor_id').val('');
    $('#proveedor_estatus').val('Activo');
    $('#proveedor_pais').val('México');
    $('#proveedor_tipo_proveedor').val('Mixto');
    $('#modalProveedor').modal('show');
  };

  window.mostrarModalEditar = function(id) {
    proveedorEditando = id;
    $('#modalProveedorTitle').text('Editar Proveedor');
    
    $.post('<?=base_url();?>compras/Proveedores/get_proveedor_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
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
        
        $('#modalProveedor').modal('show');
      }
    });
  };

  window.guardarProveedor = function() {
    const formData = $('#formProveedor').serialize();
    const url = proveedorEditando ? 
      '<?=base_url();?>compras/Proveedores/editar_ajax' : 
      '<?=base_url();?>compras/Proveedores/crear_ajax';

    $.post(url, 
      formData + '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>',
      function(result) {
        result = JSON.parse(result);
        if(result.success) {
          notifyShow(result.message, 'success');
          $('#modalProveedor').modal('hide');
          tabla.ajax.reload();
        } else {
          notifyShow('Error: ' + result.message, 'danger');
        }
      }
    );
  };

  window.eliminarProveedor = function(id) {
    if(!confirm('¿Estás seguro de eliminar este proveedor?')) return;

    $.post('<?=base_url();?>compras/Proveedores/eliminar_ajax', {
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
    $('#modalInsumos').modal('show');
  };

  function cargarInsumosProveedor(proveedorId) {
    $.post('<?=base_url();?>compras/Proveedores/get_insumos_proveedor_ajax', {
      'proveedor_id': proveedorId,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        let html = '';
        if(result.insumos.length === 0) {
          html = '<tr><td colspan="6" class="text-center text-muted">No hay insumos relacionados</td></tr>';
        } else {
          result.insumos.forEach(function(ins) {
            const nombreProv = ins.nombre_proveedor ? `<span class="badge bg-info text-white">${ins.nombre_proveedor}</span>` : '<span class="text-muted">—</span>';
            html += `
              <tr>
                <td><small>${ins.codigo}</small></td>
                <td><strong>${ins.nombre_tecnico}</strong></td>
                <td>${nombreProv}</td>
                <td>$${parseFloat(ins.precio_compra).toFixed(2)}</td>
                <td><span class="badge bg-light text-dark">${ins.unidad_medida}</span></td>
                <td>${ins.tiempo_entrega_dias} días</td>
                <td>
                  <button class="btn btn-sm btn-primary" onclick="editarInsumoProveedor(${ins.insumo_id}, ${ins.precio_compra}, ${ins.tiempo_entrega_dias}, ${ins.cantidad_minima}, '${ins.codigo_proveedor || ''}', '${(ins.nombre_proveedor || '').replace(/'/g, '\\&apos;')}', '${ins.observaciones || ''}')" title="Editar">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn btn-sm btn-danger" onclick="eliminarInsumoProveedor(${ins.insumo_id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                  </button>
                </td>
              </tr>
            `;
          });
        }
        $('#tablaInsumosProveedor tbody').html(html);
      }
    });
  }

  window.mostrarFormAgregarInsumo = function() {
    $('#tituloFormInsumo').text('Agregar Insumo');
    $('#formInsumoProveedor')[0].reset();
    $('#insumo_editando_id').val('');
    $('#insumo_select').prop('disabled', false);
    $('#formAgregarInsumo').slideDown();
  };

  window.cancelarFormInsumo = function() {
    $('#formAgregarInsumo').slideUp();
    $('#formInsumoProveedor')[0].reset();
  };

  window.editarInsumoProveedor = function(insumoId, precio, tiempoEntrega, cantidadMin, codigoProv, nombreProv, obs) {
    $('#tituloFormInsumo').text('Editar Insumo del Proveedor');
    $('#insumo_editando_id').val(insumoId);
    $('#insumo_select').val(insumoId).prop('disabled', true);
    $('#insumo_precio_compra').val(precio);
    $('#insumo_tiempo_entrega').val(tiempoEntrega);
    $('#insumo_cantidad_minima').val(cantidadMin);
    $('#insumo_codigo_proveedor').val(codigoProv);
    $('#insumo_nombre_proveedor').val(nombreProv);
    $('#insumo_observaciones').val(obs);
    $('#formAgregarInsumo').slideDown();
  };

  window.guardarInsumoProveedor = function() {
    const insumoEditandoId = $('#insumo_editando_id').val();
    const url = insumoEditandoId ? 
      '<?=base_url();?>compras/Proveedores/actualizar_precio_insumo_ajax' : 
      '<?=base_url();?>compras/Proveedores/agregar_insumo_ajax';

    const data = {
      'proveedor_id': proveedorInsumosActual,
      'insumo_id': insumoEditandoId || $('#insumo_select').val(),
      'precio_compra': $('#insumo_precio_compra').val(),
      'tiempo_entrega_dias': $('#insumo_tiempo_entrega').val(),
      'cantidad_minima': $('#insumo_cantidad_minima').val(),
      'codigo_proveedor': $('#insumo_codigo_proveedor').val(),
      'nombre_proveedor': $('#insumo_nombre_proveedor').val(),
      'observaciones': $('#insumo_observaciones').val(),
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    };

    $.post(url, data, function(result) {
      result = JSON.parse(result);
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if(result.success) {
        cancelarFormInsumo();
        cargarInsumosProveedor(proveedorInsumosActual);
      }
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
      result = JSON.parse(result);
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if(result.success) {
        cargarInsumosProveedor(proveedorInsumosActual);
      }
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
    $('#oc-detalles').html('<tr><td colspan="2" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
    $('#oc-razon-social').text('Proveedor');
    $('#oc-codigo').text('...');
    $('#oc-estatus-badge').html('');
    $('#oc-insumos-loading').show();
    $('#oc-insumos-container').hide();

    // Activar tab Información por defecto
    $('#oc-info-tab').tab('show');

    // Botones de acción rápida
    $('#oc-btn-editar').off('click').on('click', function() {
      mostrarModalEditar(id);
    });
    $('#oc-btn-insumos').off('click').on('click', function() {
      mostrarModalInsumos(id);
    });

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
    });

    // Cargar insumos cuando el tab se activa
    $('#oc-ins-tab').off('shown.bs.tab').on('shown.bs.tab', function() {
      cargarInsumosOC(id);
    });

    $('#oc-ordenes-tab').off('shown.bs.tab').on('shown.bs.tab', function() {
      cargarOrdenesOC(id);
    });
  };

  function cargarOrdenesOC(proveedorId) {
    $('#oc-ordenes-loading').show();
    $('#oc-ordenes-container').hide();

    $.post('<?=base_url();?>compras/Proveedores/get_ordenes_proveedor_ajax', {
      'proveedor_id': proveedorId, 'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(res) {
      res = JSON.parse(res);
      $('#oc-ordenes-loading').hide();
      var tbody = '';
      if(res.success && res.ordenes && res.ordenes.length > 0) {
        res.ordenes.forEach(function(oc) {
          var pdfUrl = '<?=base_url();?>compras/OrdenesCompra/generar_pdf/' + oc.id;
          tbody += '<tr>';
          tbody += '<td><strong>' + oc.folio + '</strong></td>';
          tbody += '<td>' + (oc.fecha_orden ? new Date(oc.fecha_orden).toLocaleDateString('es-MX') : '-') + '</td>';
          tbody += '<td class="text-end">$' + parseFloat(oc.total || 0).toLocaleString('es-MX', {minimumFractionDigits:2}) + '</td>';
          tbody += '<td><span class="badge bg-secondary">' + oc.estatus + '</span></td>';
          tbody += '<td class="text-end"><a href="' + pdfUrl + '" target="_blank" class="btn btn-xs btn-outline-primary btn-sm" title="Ver PDF"><i class="fas fa-file-pdf"></i></a></td>';
          tbody += '</tr>';
        });
      } else {
        tbody = '<tr><td colspan="5" class="text-center text-muted py-3">Sin órdenes de compra registradas</td></tr>';
      }
      $('#oc-ordenes-tbody').html(tbody);
      $('#oc-ordenes-container').show();
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
