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

<!-- Breadcrumb -->
<div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
        <li class="breadcrumb-item"><a href="#">Producción</a></li>
        <li class="breadcrumb-item active">Productos</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Título y botón nuevo -->
<div class="row mb-3">
  <div class="col-md-6">
    <h2><i class="fas fa-box"></i> Gestión de Productos</h2>
  </div>
  <div class="col-md-6 text-end">
    <button type="button" class="btn btn-primary" onclick="mostrarModalNuevo()">
      <i class="fas fa-plus"></i> Nuevo Producto
    </button>
  </div>
</div>

<!-- Cards de estadísticas -->
<div class="row">
  <!-- Total de Productos -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Total Productos</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?php echo number_format($stats['total_products'] ?? 0); ?>
            </h3>
          </div>
          <div class="col-4 text-end">
            <span class="badge bg-primary"><?php echo $stats['active_percentage'] ?? 0; ?>%</span>
          </div>
        </div>

        <div class="progress progress-sm shadow-sm mb-1">
          <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $stats['active_percentage'] ?? 0; ?>%"></div>
        </div>
        <small class="text-muted">Activos: <?php echo number_format($stats['active_products'] ?? 0); ?> | Inactivos: <?php echo number_format($stats['inactive_products'] ?? 0); ?></small>
      </div>
    </div>
  </div>

  <!-- Nuevos Productos (30 días) -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Nuevos (30d)</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?php echo number_format($stats['new_products_30days'] ?? 0); ?>
            </h3>
          </div>
          <div class="col-4 text-end">
            <span class="badge bg-success">+<?php echo $stats['growth_percentage'] ?? 0; ?>%</span>
          </div>
        </div>

        <div class="progress progress-sm shadow-sm mb-1">
          <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo min($stats['growth_percentage'] ?? 0, 100); ?>%"></div>
        </div>
        <small class="text-muted">Crecimiento últimos 30 días</small>
      </div>
    </div>
  </div>

  <!-- Fabricados vs Reventa -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Fabricados</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?php echo number_format($stats['manufactured_products'] ?? 0); ?>
            </h3>
          </div>
          <div class="col-4 text-end">
            <i class="fas fa-industry text-info" style="font-size: 1.5rem;"></i>
          </div>
        </div>

        <div class="progress progress-sm shadow-sm mb-1">
            <?php 
                $manufactured_percentage = ($stats['total_products'] > 0) 
                    ? ($stats['manufactured_products'] / $stats['total_products']) * 100 
                    : 0; 
            ?>
          <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $manufactured_percentage; ?>%"></div>
        </div>
        <small class="text-muted">Prod. Fabricados del total</small>
      </div>
    </div>
  </div>

  <!-- Stock Bajo -->
  <div class="col-lg-6 col-xl-3 d-flex">
    <div class="card flex-fill">
      <div class="card-header">
        <h5 class="card-title mb-0 mt-2">Stock Bajo</h5>
      </div>
      <div class="card-body my-0 pt-0">
        <div class="row d-flex align-items-center mb-3">
          <div class="col-8">
            <h3 class="d-flex align-items-center mb-0 fw-light">
              <?php echo number_format($stats['low_stock_products'] ?? 0); ?>
            </h3>
          </div>
          <div class="col-4 text-end">
            <?php if(($stats['low_stock_products'] ?? 0) > 0): ?>
                <span class="badge bg-danger"><?php echo $stats['low_stock_products'] ?? 0; ?></span>
            <?php else: ?>
                <span class="badge bg-success"><i class="fas fa-check"></i></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="progress progress-sm shadow-sm mb-1">
            <?php 
                $low_stock_percentage = ($stats['total_products'] > 0) 
                    ? ($stats['low_stock_products'] / $stats['total_products']) * 100 
                    : 0; 
            ?>
          <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $low_stock_percentage; ?>%"></div>
        </div>
        <small class="text-muted">Productos con stock crítico</small>
      </div>
    </div>
  </div>
</div>


<!-- Tabs Principales -->
<ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="tab-lista-productos" data-bs-toggle="tab" data-bs-target="#pane-lista-productos" type="button" role="tab"><i class="fas fa-boxes"></i> Catálogo de Productos</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="tab-calculadora-excel" data-bs-toggle="tab" data-bs-target="#pane-calculadora-excel" type="button" role="tab"><i class="fas fa-file-excel text-success"></i> Simulador de Producción</button>
  </li>
</ul>

<div class="tab-content" id="mainTabsContent">
  <!-- Pane: Lista de Productos -->
  <div class="tab-pane show active" id="pane-lista-productos" role="tabpanel">

<!-- Filtros -->
<div class="row mb-3">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-md-3">
            <label>Tipo de Producto</label>
            <select class="form-select" id="filtroTipo">
              <option value="">Todos</option>
              <option value="Fabricado">Fabricado</option>
              <option value="Reventa">Reventa</option>
            </select>
          </div>
          <div class="col-md-3">
            <label>Estatus</label>
            <select class="form-select" id="filtroEstatus">
              <option value="">Todos</option>
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
              <option value="Descontinuado">Descontinuado</option>
            </select>
          </div>
          <div class="col-md-3">
            <label>Stock</label>
            <select class="form-select" id="filtroStock">
              <option value="">Todos</option>
              <option value="bajo">Stock Bajo</option>
              <option value="ok">Stock OK</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabla de productos -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table id="tablaProductos" class="table table-striped table-hover" style="width:100%">
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
            <tfoot>
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
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>


  </div>

  <!-- Pane: Calculadora de Producción -->
  <div class="tab-pane fade" id="pane-calculadora-excel" role="tabpanel">
    <div class="card shadow-sm border-success mb-4">
      <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-calculator"></i> Calculadora y Simulador de Lotes</h5>
        <div>
           <button class="btn btn-sm btn-light text-success" onclick="abrirModalImportacionExcel()"><i class="fas fa-upload"></i> Importar desde Excel</button>
        </div>
      </div>
      <div class="card-body bg-light">
        <!-- Buscador / Selectores -->
        <div class="row mb-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">1. Seleccionar Producto Fabricado</label>
            <select class="form-select select2-productos" id="calc_producto_id">
              <option value="">-- Buscar Producto --</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">2. Versión de Formulación</label>
            <select class="form-select" id="calc_formulacion_id" disabled>
              <option value="">-- Seleccionar --</option>
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label">3. Simular Producción</label>
            <div class="input-group">
              <span class="input-group-text bg-primary text-white">Cubetas</span>
              <input type="number" class="form-control" id="calc_cubetas" value="1" min="1">
              <span class="input-group-text bg-info text-white">o m²</span>
              <input type="number" class="form-control" id="calc_m2" placeholder="Área">
              <button class="btn btn-primary" type="button" onclick="simularProduccion()"><i class="fas fa-play"></i> Calcular</button>
            </div>
          </div>
        </div>

        <!-- Metadatos de la formulación -->
        <div class="alert alert-secondary d-none" id="alertMetadatosFormulacion">
            <div class="row">
                <div class="col-md-4"><strong>Cliente:</strong> <span id="lbl_calc_cliente">-</span></div>
                <div class="col-md-4"><strong>Rendimiento:</strong> <span id="lbl_calc_rendimiento">-</span> m²/kg</div>
                <div class="col-md-4"><strong>Fecha:</strong> <span id="lbl_calc_fecha">-</span></div>
            </div>
            <div class="row mt-1">
                <div class="col-12"><strong>Comentarios:</strong> <span id="lbl_calc_comentarios">-</span></div>
            </div>
        </div>

        <!-- Tabla Excel -->
        <div class="table-responsive bg-white border p-0 rounded">
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

<?php $this->load->view("produccion/productos/modals/modales"); ?>

<script src="<?=base_url();?>assets/dist/js/produccion_productos.js?v=<?=date('Ymd');?>"></script>


<!-- Cache buster: v2.0 -->
