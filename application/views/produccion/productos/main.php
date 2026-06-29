<?php
/**
 * Vista principal de Productos
 * Gestión de productos terminados con formulaciones (BOM)
 */
$stats = $response['stats'] ?? [];
$puede_ver_costos = $response['puede_ver_costos'] ?? false;
?>

<script>
// Variables globales inyectadas desde PHP — usadas por produccion_productos.js
const PUEDE_VER_COSTOS = <?= $puede_ver_costos ? 'true' : 'false' ?>;
const BASE_URL = '<?= base_url() ?>';
const CSRF_TOKEN_NAME = '<?= $this->security->get_csrf_token_name() ?>';
const CSRF_HASH = '<?= $this->security->get_csrf_hash() ?>';
</script>

<div class="produccion-productos-page">

<!-- Breadcrumb -->
<div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-2">
        <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
        <li class="breadcrumb-item">Producción</li>
        <li class="breadcrumb-item active">Productos y Formulaciones</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Encabezado -->
<div class="page-hero d-flex flex-wrap justify-content-between align-items-center gap-3">
  <div>
    <h2><i class="fas fa-industry me-2"></i>Productos y Formulaciones</h2>
    <p class="lead mb-0">Catálogo, simulador de lotes y gestión de BOM (árbol de materiales)</p>
  </div>
  <div>
    <button type="button" class="btn btn-light btn-accion-principal text-primary" onclick="mostrarModalNuevo()">
      <i class="fas fa-plus"></i> Nuevo Producto
    </button>
  </div>
</div>

<!-- Guía rápida -->
<div class="guia-banner">
  <div class="d-flex flex-wrap align-items-start gap-3">
    <div class="flex-grow-1">
      <strong><i class="fas fa-book-open me-1"></i> Después de importar Excel:</strong>
      busca semielaborados (<code>BASE ORGANICA BLANCA</code>, <code>TINTA NEGRA</code>, etc.),
      activa su formulación en el historial y enlaza insumos fabricados para el árbol BOM.
      Guía completa: <code>GUIA_PRODUCCION_POST_IMPORTACION.md</code> en la raíz del proyecto.
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <button type="button" class="btn btn-sm btn-warning fw-bold" onclick="document.getElementById('buscarProductos').focus(); document.getElementById('tab-lista-productos').click();">
        <i class="fas fa-search"></i> Ir a buscar
      </button>
      <button type="button" class="btn btn-sm btn-success fw-bold" onclick="abrirModalImportacionExcel()">
        <i class="fas fa-file-excel"></i> Importar Excel
      </button>
    </div>
  </div>
</div>

<!-- Cards de estadísticas -->
<div class="row g-3 mb-4">
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card stat-card stat-primary flex-fill">
      <div class="card-header"><i class="fas fa-boxes me-1"></i> Total productos</div>
      <div class="card-body pt-3">
        <div class="stat-value"><?= number_format($stats['total_products'] ?? 0) ?></div>
        <div class="progress progress-sm mt-2 mb-1" style="height:6px;">
          <div class="progress-bar bg-primary" style="width: <?= (float)($stats['active_percentage'] ?? 0) ?>%"></div>
        </div>
        <small class="text-muted">Activos: <?= number_format($stats['active_products'] ?? 0) ?> · Inactivos: <?= number_format($stats['inactive_products'] ?? 0) ?></small>
      </div>
    </div>
  </div>
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card stat-card stat-success flex-fill">
      <div class="card-header"><i class="fas fa-chart-line me-1"></i> Nuevos (30 días)</div>
      <div class="card-body pt-3">
        <div class="stat-value"><?= number_format($stats['new_products_30days'] ?? 0) ?></div>
        <div class="progress progress-sm mt-2 mb-1" style="height:6px;">
          <div class="progress-bar bg-success" style="width: <?= min((float)($stats['growth_percentage'] ?? 0), 100) ?>%"></div>
        </div>
        <small class="text-muted">Crecimiento reciente del catálogo</small>
      </div>
    </div>
  </div>
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card stat-card stat-info flex-fill">
      <div class="card-header"><i class="fas fa-industry me-1"></i> Fabricados</div>
      <div class="card-body pt-3">
        <div class="stat-value"><?= number_format($stats['manufactured_products'] ?? 0) ?></div>
        <?php $manufactured_percentage = ($stats['total_products'] ?? 0) > 0 ? ($stats['manufactured_products'] / $stats['total_products']) * 100 : 0; ?>
        <div class="progress progress-sm mt-2 mb-1" style="height:6px;">
          <div class="progress-bar bg-info" style="width: <?= $manufactured_percentage ?>%"></div>
        </div>
        <small class="text-muted">Con formulación / BOM en planta</small>
      </div>
    </div>
  </div>
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card stat-card stat-danger flex-fill">
      <div class="card-header"><i class="fas fa-exclamation-triangle me-1"></i> Stock bajo</div>
      <div class="card-body pt-3">
        <div class="stat-value text-danger"><?= number_format($stats['low_stock_products'] ?? 0) ?></div>
        <?php $low_stock_percentage = ($stats['total_products'] ?? 0) > 0 ? ($stats['low_stock_products'] / $stats['total_products']) * 100 : 0; ?>
        <div class="progress progress-sm mt-2 mb-1" style="height:6px;">
          <div class="progress-bar bg-danger" style="width: <?= $low_stock_percentage ?>%"></div>
        </div>
        <small class="text-muted">Requieren atención de compras</small>
      </div>
    </div>
  </div>
</div>


<!-- Tabs Principales -->
<ul class="nav nav-tabs mb-3" id="mainTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="tab-lista-productos" data-bs-toggle="tab" data-bs-target="#pane-lista-productos" type="button" role="tab">
      <i class="fas fa-boxes me-1"></i> Catálogo
    </button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="tab-calculadora-excel" data-bs-toggle="tab" data-bs-target="#pane-calculadora-excel" type="button" role="tab">
      <i class="fas fa-flask me-1"></i> Simulador de Producción
    </button>
  </li>
</ul>

<div class="tab-content" id="mainTabsContent">
  <!-- Pane: Lista de Productos -->
  <div class="tab-pane show active" id="pane-lista-productos" role="tabpanel">

<!-- Filtros -->
<div class="row mb-3 productos-filtros-panel">
  <div class="col-12">
    <div class="panel-filtros">
      <div class="panel-filtros-header"><i class="fas fa-filter me-2"></i>Buscar y filtrar productos</div>
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-4 col-md-6">
            <label for="buscarProductos"><i class="fas fa-search me-1"></i> Buscar producto</label>
            <input type="text" class="form-control" id="buscarProductos"
                   placeholder="Ej: BASE ORGANICA BLANCA, TINTA NEGRA, CHISA GLASS..." autocomplete="off">
            <div class="d-flex flex-wrap gap-1 mt-2">
              <?php foreach (['BASE ORGANICA BLANCA','TINTA NEGRA','SOLUCION FASE ACUOSA','CHISA GLASS'] as $chip): ?>
              <button type="button" class="btn btn-sm btn-outline-primary btn-chip-buscar" data-term="<?= htmlspecialchars($chip) ?>"><?= htmlspecialchars($chip) ?></button>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="col-lg-2 col-md-6">
            <label for="filtroTipo">Tipo</label>
            <select class="form-select" id="filtroTipo">
              <option value="">Todos</option>
              <option value="Fabricado">Fabricado</option>
              <option value="Reventa">Reventa</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-4">
            <label for="filtroEstatus">Estatus</label>
            <select class="form-select" id="filtroEstatus">
              <option value="">Todos</option>
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
              <option value="Descontinuado">Descontinuado</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-4">
            <label for="filtroStock">Stock</label>
            <select class="form-select" id="filtroStock">
              <option value="">Todos</option>
              <option value="bajo">Stock bajo</option>
              <option value="ok">Stock OK</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-4">
            <button type="button" class="btn btn-outline-dark w-100 btn-accion-principal" id="btnLimpiarFiltrosProductos">
              <i class="fas fa-eraser"></i> Limpiar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabla de productos -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card panel-tabla">
      <div class="card-header"><i class="fas fa-list me-2"></i>Listado de productos</div>
      <div class="card-body p-2 p-md-3">
        <div class="table-responsive">
          <table id="tablaProductos" class="table table-striped table-hover mb-0" style="width:100%">
            <thead>
              <tr>
                <th>Código</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Alias</th>
                <th>Categoría</th>
                <th>Tipo</th>
                <th>Stock</th>
                <th>Precio</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>


  </div>

  <!-- Pane: Calculadora de Producción -->
  <div class="tab-pane fade" id="pane-calculadora-excel" role="tabpanel">
    <div class="card simulador-card mb-4">
      <div class="card-header text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold"><i class="fas fa-calculator me-2"></i>Simulador de lotes (vista Excel)</h5>
        <button class="btn btn-sm btn-light text-success fw-bold" type="button" onclick="abrirModalImportacionExcel()">
          <i class="fas fa-upload"></i> Importar Excel
        </button>
      </div>
      <div class="card-body" style="background:#f8fafc;">
        <div class="row g-3 mb-4">
          <div class="col-lg-4 col-md-6">
            <div class="simulador-paso">
              <label><span class="paso-num">1</span>Producto fabricado</label>
              <select class="form-select form-select-lg" id="calc_producto_id">
                <option value="">— Buscar producto —</option>
              </select>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="simulador-paso">
              <label><span class="paso-num">2</span>Versión formulación</label>
              <select class="form-select form-select-lg" id="calc_formulacion_id" disabled>
                <option value="">— Seleccionar —</option>
              </select>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="simulador-paso">
              <label><span class="paso-num">3</span>Cantidad a producir</label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-primary text-white fw-bold">Cubetas</span>
                <input type="number" class="form-control" id="calc_cubetas" value="1" min="1">
                <span class="input-group-text bg-secondary text-white fw-bold">m²</span>
                <input type="number" class="form-control" id="calc_m2" placeholder="Área">
                <button class="btn btn-primary fw-bold px-4" type="button" onclick="simularProduccion()">
                  <i class="fas fa-play"></i> Calcular
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Metadatos de la formulación -->
        <div class="alert alert-light border d-none mb-3" id="alertMetadatosFormulacion" style="border-left:4px solid #059669 !important;">
            <div class="row g-2">
                <div class="col-md-4"><strong>Cliente:</strong> <span id="lbl_calc_cliente">—</span></div>
                <div class="col-md-4"><strong>Rendimiento:</strong> <span id="lbl_calc_rendimiento">—</span></div>
                <div class="col-md-4"><strong>Fecha:</strong> <span id="lbl_calc_fecha">—</span></div>
                <div class="col-12"><strong>Comentarios:</strong> <span id="lbl_calc_comentarios">—</span></div>
            </div>
        </div>

        <!-- Tabla Excel -->
        <div class="table-responsive bg-white border rounded shadow-sm">
          <table class="table table-bordered table-sm mb-0 table-hover" id="tablaExcelSimulador">
            <thead class="table-dark">
              <tr>
                <th width="15%">Grupo / Color</th>
                <th width="25%">Insumo / Componente</th>
                <th width="10%" class="text-center">% BOM</th>
                <th width="12%" class="text-end">kg (Unidad)</th>
                <th width="10%" class="text-center">% Fase Acuosa</th>
                <th width="12%" class="text-end">kg Fase Acuosa</th>
                <th width="16%" class="text-end bg-primary text-white">TOTAL REQUERIDO</th>
              </tr>
            </thead>
            <tbody id="tbodyExcelSimulador">
              <tr>
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="fas fa-file-excel fa-3x mb-3 text-light"></i><br>
                  Seleccione un producto y formulación para simular la producción.
                </td>
              </tr>
            </tbody>
            <tfoot class="table-secondary" id="tfootExcelSimulador" style="display:none;">
              <tr>
                <th colspan="2" class="text-end">TOTALES GLOBALES:</th>
                <th class="text-center" id="lbl_total_porcentaje">100%</th>
                <th class="text-end" id="lbl_total_kg_unidad">0.000</th>
                <th class="text-center">-</th>
                <th class="text-end" id="lbl_total_kg_fase_acuosa">0.000</th>
                <th class="text-end bg-primary text-white fw-bold" id="lbl_total_requerido_kg">0.000</th>
              </tr>
            </tfoot>
          </table>
        </div>
        
        <!-- Botones de Acción (Edición) -->
        <div class="row mt-3" id="accionesEdicionExcel" style="display:none;">
           <div class="col-12 text-end">
             <button type="button" class="btn btn-outline-secondary me-2" onclick="habilitarEdicionInline()" id="btnHabilitarEdicion"><i class="fas fa-pencil-alt"></i> Editar Inline</button>
             <button type="button" class="btn btn-warning me-2 btn-edicion-excel d-none" onclick="guardarFormulacionExcel(true)"><i class="fas fa-copy"></i> Guardar como Nueva Versión</button>
             <button type="button" class="btn btn-danger btn-edicion-excel d-none" onclick="guardarFormulacionExcel(false)"><i class="fas fa-save"></i> Actualizar Actual</button>
             <button type="button" class="btn btn-light btn-edicion-excel d-none" onclick="cancelarEdicionInline()"><i class="fas fa-times"></i> Cancelar</button>
           </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Fin Tabs Principales -->

</div><!-- /.produccion-productos-page -->

<?php $this->load->view("produccion/productos/modals/modales"); ?>
