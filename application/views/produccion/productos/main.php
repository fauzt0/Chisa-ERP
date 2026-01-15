<?php
/**
 * Vista principal de Productos
 * Gestión de productos terminados con formulaciones (BOM)
 */
$stats = $response['stats'] ?? [];
$puede_ver_costos = $response['puede_ver_costos'] ?? false;
?>

<script>
// Variable global para permisos
const PUEDE_VER_COSTOS = <?= $puede_ver_costos ? 'true' : 'false' ?>;
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

<!-- Modal Producto (Crear/Editar) -->
<div class="modal fade" id="modalProducto" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalProductoTitle">Nuevo Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formProducto">
          <input type="hidden" id="producto_id" name="id">
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Nombre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="producto_nombre" name="nombre" required>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Alias (Nombre Alternativo)</label>
              <input type="text" class="form-control" id="producto_alias" name="alias" placeholder="Nombre con el que conocen el producto">
              <small class="text-muted">Ej: "Pintura blanca" para "Recubrimiento Vinílico Blanco"</small>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Categoría <span class="text-danger">*</span></label>
              <select class="form-select" id="producto_categoria_id" name="categoria_id" required>
                <option value="">-- Seleccionar --</option>
              </select>
            </div>
          
            <div class="col-md-6 mb-3">
              <label class="form-label">Tipo de Producto <span class="text-danger">*</span></label>
              <select class="form-select" id="producto_tipo_producto" name="tipo_producto" required onchange="toggleProveedorField()">
                <option value="Fabricado">Fabricado</option>
                <option value="Reventa">Reventa</option>
              </select>
            </div>
          </div>
          
          <div class="row" id="divProveedor" style="display:none;">
            <div class="col-md-12 mb-3">
              <label class="form-label">Proveedor</label>
              <select class="form-select" id="producto_proveedor_id" name="proveedor_id">
                <option value="">-- Seleccionar --</option>
              </select>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" id="producto_descripcion" name="descripcion" rows="2"></textarea>
            </div>
          </div>
          
          <hr>
          <h6><i class="fas fa-ruler"></i> Presentación</h6>
          
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Unidad de Venta</label>
              <select class="form-select" id="producto_unidad_venta" name="unidad_venta">
                <option value="Cubeta">Cubeta</option>
                <option value="Litro">Litro</option>
                <option value="Galon">Galón</option>
                <option value="Kg">Kilogramo</option>
                <option value="Pieza">Pieza</option>
                <option value="Caja">Caja</option>
              </select>
            </div>
            
            <div class="col-md-4 mb-3">
              <label class="form-label">Presentación Principal</label>
              <input type="text" class="form-control" id="producto_presentacion_principal" name="presentacion_principal" placeholder="Ej: 19L">
            </div>
            
            <div class="col-md-4 mb-3">
              <label class="form-label">Contenido Neto</label>
              <div class="input-group">
                <input type="number" step="0.01" class="form-control" id="producto_contenido_neto" name="contenido_neto">
                <select class="form-select" id="producto_unidad_contenido" name="unidad_contenido" style="max-width:80px;">
                  <option value="L">L</option>
                  <option value="ml">ml</option>
                  <option value="Kg">Kg</option>
                  <option value="g">g</option>
                </select>
              </div>
            </div>
          </div>
          
          <hr>
          <h6><i class="fas fa-barcode"></i> Códigos de Identificación</h6>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Código de Barras EAN-13 <span class="badge bg-success">Auto</span></label>
              <input type="text" class="form-control bg-light" id="producto_codigo_barras" name="codigo_barras" readonly placeholder="Se genera automáticamente">
              <small class="text-muted">Generado automáticamente al crear el producto</small>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">SKU</label>
              <input type="text" class="form-control" id="producto_sku" name="sku">
            </div>
          </div>
          
          <hr>
          <h6><i class="fas fa-warehouse"></i> Inventario</h6>
          
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Stock Mínimo</label>
              <input type="number" step="0.01" class="form-control" id="producto_stock_minimo" name="stock_minimo" value="0">
            </div>
            
            <div class="col-md-4 mb-3">
              <label class="form-label">Stock Máximo</label>
              <input type="number" step="0.01" class="form-control" id="producto_stock_maximo" name="stock_maximo" value="0">
            </div>
            
            <div class="col-md-4 mb-3">
              <label class="form-label">Estatus</label>
              <select class="form-select" id="producto_estatus" name="estatus">
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
                <option value="Descontinuado">Descontinuado</option>
              </select>
            </div>
          </div>
          
          <hr>
          <h6><i class="fas fa-dollar-sign"></i> Precios</h6>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Precio de Venta</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" class="form-control" id="producto_precio_venta" name="precio_venta" value="0">
              </div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Margen de Utilidad (%)</label>
              <div class="input-group">
                <input type="number" step="0.01" class="form-control" id="producto_margen_utilidad" name="margen_utilidad" value="0">
                <span class="input-group-text">%</span>
              </div>
            </div>
          </div>
          
          <hr>
          <h6><i class="fas fa-info-circle"></i> Datos Técnicos</h6>
          
          <div class="row">
            <div class="col-md-3 mb-3">
              <label class="form-label">Rendimiento</label>
              <input type="text" class="form-control" id="producto_rendimiento" name="rendimiento" placeholder="Ej: 10-12 m²/L">
              <small class="text-muted">Cobertura aproximada</small>
            </div>
            
            <div class="col-md-3 mb-3">
              <label class="form-label">Peso Bruto (Kg)</label>
              <input type="number" step="0.01" class="form-control" id="producto_peso_bruto" name="peso_bruto" placeholder="Con envase">
            </div>
            
            <div class="col-md-3 mb-3">
              <label class="form-label">Tiempo de Secado</label>
              <input type="text" class="form-control" id="producto_tiempo_secado" name="tiempo_secado" placeholder="Ej: 2-4 horas">
            </div>
            
            <div class="col-md-3 mb-3">
              <label class="form-label">Colores Disponibles</label>
              <input type="text" class="form-control" id="producto_colores_disponibles" name="colores_disponibles" placeholder="Blanco, Beige, etc.">
              <small class="text-muted">Separados por comas</small>
            </div>
          </div>
          
          <hr>
          <h6><i class="fas fa-clipboard-list"></i> Información Adicional</h6>
          
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Características</label>
              <textarea class="form-control" id="producto_caracteristicas" name="caracteristicas" rows="2" placeholder="Características técnicas del producto"></textarea>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Texturas</label>
              <input type="text" class="form-control" id="producto_texturas" name="texturas" placeholder="Ej: Lisa, Rugosa, Mate">
            </div>
            
            <div class="col-md-4 mb-3">
              <label class="form-label">Forma</label>
              <input type="text" class="form-control" id="producto_forma" name="forma" placeholder="Ej: Rectangular, Cuadrada">
            </div>
            
            <div class="col-md-4 mb-3">
              <label class="form-label">Dimensiones</label>
              <input type="text" class="form-control" id="producto_dimensiones" name="dimensiones" placeholder="Ej: 30x30 cm, 1x1 m">
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Resistencia</label>
              <input type="text" class="form-control" id="producto_resistencia" name="resistencia" placeholder="Especificaciones de resistencia">
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Colocación</label>
              <textarea class="form-control" id="producto_colocacion" name="colocacion" rows="2" placeholder="Instrucciones de colocación"></textarea>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Mantenimiento Preventivo</label>
              <textarea class="form-control" id="producto_mantenimiento_preventivo" name="mantenimiento_preventivo" rows="2" placeholder="Instrucciones de mantenimiento preventivo"></textarea>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Mantenimiento Correctivo</label>
              <textarea class="form-control" id="producto_mantenimiento_correctivo" name="mantenimiento_correctivo" rows="2" placeholder="Instrucciones de mantenimiento correctivo"></textarea>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Observaciones</label>
              <textarea class="form-control" id="producto_observaciones" name="observaciones" rows="2" placeholder="Observaciones generales"></textarea>
            </div>
          </div>
          
          <hr>
          <h6><i class="fas fa-palette"></i> Variante de Producto</h6>
          
          <div class="row">
            <div class="col-md-12 mb-3">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="es_variante_check" onchange="toggleVarianteFields()">
                <label class="form-check-label" for="es_variante_check">
                  Este producto es una variante de otro producto existente
                </label>
              </div>
            </div>
          </div>
          
          <div id="varianteFields" style="display: none;">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Producto Base <span class="text-danger">*</span></label>
                <select class="form-select" id="producto_padre_id" name="producto_padre_id">
                  <option value="">-- Seleccionar Producto Base --</option>
                </select>
                <small class="text-muted">Producto del cual esta será una variante</small>
              </div>
              
              <div class="col-md-3 mb-3">
                <label class="form-label">Tipo de Variante</label>
                <select class="form-select" id="variante_tipo" name="variante_tipo">
                  <option value="color">Color</option>
                  <option value="tamaño">Tamaño</option>
                  <option value="acabado">Acabado</option>
                  <option value="textura">Textura</option>
                  <option value="otro">Otro</option>
                </select>
              </div>
              
              <div class="col-md-3 mb-3">
                <label class="form-label">Valor <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="variante_valor" name="variante_valor" placeholder="Ej: Rojo, Grande, Mate">
                <small class="text-muted">Valor específico de la variante</small>
              </div>
            </div>
            
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i> 
              <strong>Nota:</strong> Al crear una variante, se copiarán los datos básicos del producto base.
              Podrás modificar el código, nombre, formulación e inventario de forma independiente.
            </div>
          </div>
          
          <hr>
          <h6><i class="fas fa-file-pdf"></i> Catálogo del Producto</h6>
          
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label">Archivo PDF del Catálogo</label>
              <input type="file" class="form-control" id="producto_catalogo_pdf" name="catalogo_pdf" accept=".pdf">
              <small class="text-muted">Formato: PDF. Máximo 5MB</small>
              <div id="catalogoActual" class="mt-2" style="display: none;">
                <small class="text-success">
                  <i class="fas fa-file-pdf"></i> Catálogo actual: <a href="#" id="linkCatalogoActual" target="_blank">Ver PDF</a>
                </small>
              </div>
            </div>
          </div>
          
          <hr>
          <h6><i class="fas fa-image"></i> Imagen del Producto</h6>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Foto del Producto</label>
              <input type="file" class="form-control" id="producto_foto" name="foto_producto" accept="image/*" onchange="previewImage(this)">
              <small class="text-muted">Formatos: JPG, PNG, GIF, WEBP. Máximo 2MB</small>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Vista Previa</label>
              <div id="imagePreview" class="border rounded p-2 text-center" style="min-height: 150px; background-color: #f8f9fa;">
                <img id="previewImg" src="" alt="Vista previa" style="max-width: 100%; max-height: 200px; display: none;">
                <p id="noImageText" class="text-muted mt-5">No hay imagen seleccionada</p>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarProducto()">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Formulación (SUPER INTUITIVO) -->
<div class="modal fade" id="modalFormulacion" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="fas fa-flask"></i> Gestión de Formulación (BOM)
          <br><small id="formulacionProductoNombre" class="opacity-75"></small>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="formulacion_producto_id">
        <input type="hidden" id="formulacion_id">
        
        <!-- Información de la formulación -->
        <div class="card mb-3">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Datos de la Formulación</h6>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-4">
                <label class="form-label">Nombre de Versión</label>
                <input type="text" class="form-control" id="formulacion_nombre_version" placeholder="Ej: V1.0, Mejorada">
              </div>
              <div class="col-md-4">
                <label class="form-label">Cantidad que Produce</label>
                <div class="input-group">
                  <input type="number" step="0.01" class="form-control" id="formulacion_cantidad_producida" placeholder="19">
                  <select class="form-select" id="formulacion_unidad_produccion" style="max-width:80px;">
                    <option value="L">L</option>
                    <option value="ml">ml</option>
                    <option value="Kg">Kg</option>
                    <option value="g">g</option>
                    <option value="Pza">Pza</option>
                  </select>
                </div>
                <small class="text-muted">Ej: 19 L (una cubeta)</small>
              </div>
              <div class="col-md-4">
                <label class="form-label">Descripción</label>
                <input type="text" class="form-control" id="formulacion_descripcion" placeholder="Opcional">
              </div>
            </div>
            
            <div class="row mt-3">
              <div class="col-md-4">
                <label class="form-label">Costo Mano de Obra</label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" class="form-control" id="formulacion_costo_mano_obra" value="0">
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Costos Indirectos</label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input type="number" step="0.01" class="form-control" id="formulacion_costo_indirecto" value="0">
                </div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Costo Total</label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input type="text" class="form-control bg-light" id="formulacion_costo_total" readonly value="0.00">
                </div>
                <small class="text-muted">Calculado automáticamente</small>
              </div>
            </div>
            
            <div class="row mt-3">
              <div class="col-12">
                <button type="button" class="btn btn-success" onclick="guardarFormulacion()">
                  <i class="fas fa-save"></i> Guardar Formulación
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Componentes de la formulación -->
        <div class="card">
          <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="fas fa-list"></i> Componentes de la Formulación</h6>
          </div>
          <div class="card-body">
            
            <!-- Tabs para Insumos y Productos -->
            <ul class="nav nav-tabs mb-3" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-insumos" data-bs-toggle="tab" data-bs-target="#tabInsumos" type="button">
                  <i class="fas fa-boxes"></i> Agregar Insumos
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-productos" data-bs-toggle="tab" data-bs-target="#tabProductos" type="button">
                  <i class="fas fa-box"></i> Agregar Productos Base
                </button>
              </li>
            </ul>
            
            <div class="tab-content">
              <!-- Tab Insumos -->
              <div class="tab-pane fade show active" id="tabInsumos">
                <div class="alert alert-info">
                  <i class="fas fa-info-circle"></i> <strong>Agregar Insumos:</strong> Selecciona los insumos que necesitas para fabricar este producto.
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-5">
                    <label class="form-label">Insumo</label>
                    <select class="form-select" id="componente_insumo_id">
                      <option value="">-- Seleccionar Insumo --</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Cantidad</label>
                    <div class="input-group">
                      <input type="number" step="0.001" class="form-control" id="componente_insumo_cantidad" placeholder="0">
                      <select class="form-select" id="componente_insumo_unidad" style="max-width:80px;">
                        <option value="L">L</option>
                        <option value="ml">ml</option>
                        <option value="Kg">Kg</option>
                        <option value="g">g</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Observaciones</label>
                    <input type="text" class="form-control" id="componente_insumo_observaciones" placeholder="Opcional">
                  </div>
                  <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-success w-100" onclick="agregarInsumo()">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </div>
              </div>
              
              <!-- Tab Productos -->
              <div class="tab-pane fade" id="tabProductos">
                <div class="alert alert-warning">
                  <i class="fas fa-info-circle"></i> <strong>Productos Base:</strong> Si este producto usa otro producto como base (ej: primer), agrégalo aquí.
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-5">
                    <label class="form-label">Producto Base</label>
                    <select class="form-select" id="componente_producto_id">
                      <option value="">-- Seleccionar Producto --</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Cantidad</label>
                    <div class="input-group">
                      <input type="number" step="0.001" class="form-control" id="componente_producto_cantidad" placeholder="0">
                      <select class="form-select" id="componente_producto_unidad" style="max-width:80px;">
                        <option value="L">L</option>
                        <option value="ml">ml</option>
                        <option value="Kg">Kg</option>
                        <option value="g">g</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Observaciones</label>
                    <input type="text" class="form-control" id="componente_producto_observaciones" placeholder="Opcional">
                  </div>
                  <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-success w-100" onclick="agregarProducto()">
                      <i class="fas fa-plus"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <hr>
            
            <!-- Tabla de componentes agregados -->
            <h6><i class="fas fa-list-ul"></i> Componentes Agregados</h6>
            <div class="table-responsive">
              <table class="table table-sm table-bordered">
                <thead class="table-light">
                  <tr>
                    <th width="10%">Tipo</th>
                    <th width="30%">Componente</th>
                    <th width="15%">Cantidad</th>
                    <th width="15%">Costo Unit.</th>
                    <th width="15%">Subtotal</th>
                    <th width="10%">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tablaComponentes">
                  <tr id="noComponentes">
                    <td colspan="6" class="text-center text-muted">No hay componentes agregados</td>
                  </tr>
                </tbody>
                <tfoot class="table-light">
                  <tr>
                    <td colspan="4" class="text-end"><strong>Total Insumos:</strong></td>
                    <td colspan="2"><strong id="totalInsumos">$0.00</strong></td>
                  </tr>
                </tfoot>
              </table>
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

<!-- Modal: Historial de Formulaciones -->
<div class="modal fade" id="modalHistorialFormulaciones" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-history"></i> Historial de Formulaciones - <span id="historial_producto_nombre"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Buscador -->
        <div class="mb-3">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="busquedaHistorial" placeholder="Buscar por versión o descripción (ej: Plaza Comercial, V1.2, etc.)">
          </div>
          <small class="text-muted">La búsqueda se realiza en tiempo real</small>
        </div>
        
        <!-- Lista de formulaciones -->
        <div id="listaHistorialFormulaciones">
          <div class="text-center text-muted py-5">
            <i class="fas fa-spinner fa-spin fa-3x"></i>
            <p class="mt-3">Cargando historial...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Detalle de Formulación (Solo Lectura) -->
<div class="modal fade" id="modalDetalleFormulacion" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detalleFormulacionTitle">
          <i class="fas fa-flask"></i> Detalle de Formulación
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detalleFormulacionBody">
        <!-- Se llena dinámicamente -->
      </div>
    </div>
  </div>
</div>

<!-- Modal: Familia de Productos -->
<div class="modal fade" id="modalFamiliaProductos" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="fas fa-sitemap"></i> Familia de Productos
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <h6 class="mb-3">
          <i class="fas fa-box"></i> Producto Base: <span id="familiaProductoBase"></span>
        </h6>
        
        <div class="table-responsive">
          <table class="table table-sm table-hover">
            <thead class="table-light">
              <tr>
                <th>Código</th>
                <th>Variante</th>
                <th>Stock</th>
                <th>Formulación</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="familiaProductosBody">
              <tr>
                <td colspan="5" class="text-center">
                  <i class="fas fa-spinner fa-spin"></i> Cargando variantes...
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Zoom de Imagen de Producto -->
<div class="modal fade" id="modalImagenProductoZoom" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">
          <i class="fas fa-image"></i> <span id="lblImagenProductoNombre"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-0">
        <img id="imgProductoZoom" src="" alt="" class="img-fluid" style="max-height: 70vh; width: auto;">
      </div>
    </div>
  </div>
</div>

<script>
let tabla;
let formulacionActual = null;
let componentesTemporales = [];
let insumosDisponibles = [];
let productosDisponibles = [];

function initProductos() {
  inicializarDataTable();
  
  // Event listeners para actualizar costo total de formulación
  $('#formulacion_costo_mano_obra, #formulacion_costo_indirecto').on('input', actualizarCostoTotal);
  
  // Filtros
  $('#filtroTipo, #filtroEstatus, #filtroStock').on('change', function() {
    tabla.ajax.reload();
  });
}

function inicializarDataTable() {
  tabla = $('#tablaProductos').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?=base_url();?>produccion/Productos/lista_ajax',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
        d.filtro_tipo = $('#filtroTipo').val();
        d.filtro_estatus = $('#filtroEstatus').val();
        d.filtro_stock = $('#filtroStock').val();
      }
    },
    columns: [
      { data: 0 },  // Código
      { data: 1, orderable: false },  // Imagen
      { data: 2 },  // Nombre
      { data: 3 },  // Alias
      { data: 4 },  // Categoría
      { data: 5 },  // Tipo
      { data: 6 },  // Stock
      { data: 7 },  // Precio
      { data: 8 },  // Estatus
      { data: 9, orderable: false }  // Acciones
    ],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    },
    pageLength: 25,
    order: [[0, 'desc']]
  });
}

function cargarCategorias() {
  console.log('Cargando categorías...');
  $.post('<?=base_url();?>produccion/Productos/get_categorias_select_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    console.log('Respuesta raw:', result);
    try {
      result = JSON.parse(result);
      console.log('Respuesta parseada:', result);
      if(result.success) {
        let html = '<option value="">-- Seleccionar --</option>';
        result.categorias.forEach(function(cat) {
          html += `<option value="${cat.id}">${cat.nombre}</option>`;
        });
        $('#producto_categoria_id').html(html);
        console.log('Categorías cargadas:', result.categorias.length);
      } else {
        console.error('Error en respuesta:', result.message);
      }
    } catch(e) {
      console.error('Error al parsear JSON:', e, result);
    }
  }).fail(function(xhr, status, error) {
    console.error('Error AJAX:', status, error);
    console.error('Respuesta:', xhr.responseText);
  });
}

window.mostrarModalNuevo = function() {
  $('#modalProductoTitle').text('Nuevo Producto');
  $('#formProducto')[0].reset();
  $('#producto_id').val('');
  $('#producto_tipo_producto').val('Fabricado');
  $('#divProveedor').hide();
  
  // Limpiar vista previa de imagen
  $('#previewImg').attr('src', '').hide();
  $('#noImageText').show();
  
  // Limpiar campos de variante
  $('#es_variante_check').prop('checked', false);
  $('#varianteFields').hide();
  $('#producto_padre_id').val('');
  $('#variante_tipo').val('color');
  $('#variante_valor').val('');
  $('#catalogoActual').hide();
  
  // Cargar categorías cuando se abre el modal
  cargarCategorias();
  
  $('#modalProducto').modal('show');
};

window.editarProducto = function(id) {
  // Primero cargar categorías
  cargarCategorias();
  
  $.post('<?=base_url();?>produccion/Productos/get_producto_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const p = result.producto;
      $('#modalProductoTitle').text('Editar Producto');
      $('#producto_id').val(p.id);
      $('#producto_nombre').val(p.nombre);
      $('#producto_alias').val(p.alias);
      $('#producto_descripcion').val(p.descripcion);
      
      // Esperar un momento para que las categorías se carguen
      setTimeout(function() {
        $('#producto_categoria_id').val(p.categoria_id);
      }, 300);
      
      $('#producto_tipo_producto').val(p.tipo_producto);
      $('#producto_unidad_venta').val(p.unidad_venta);
      $('#producto_presentacion_principal').val(p.presentacion_principal);
      $('#producto_contenido_neto').val(p.contenido_neto);
      $('#producto_unidad_contenido').val(p.unidad_contenido);
      $('#producto_codigo_barras').val(p.codigo_barras);
      $('#producto_sku').val(p.sku);
      $('#producto_stock_minimo').val(p.stock_minimo);
      $('#producto_stock_maximo').val(p.stock_maximo);
      $('#producto_precio_venta').val(p.precio_venta);
      $('#producto_margen_utilidad').val(p.margen_utilidad);
      $('#producto_rendimiento').val(p.rendimiento);
      $('#producto_peso_bruto').val(p.peso_bruto);
      $('#producto_tiempo_secado').val(p.tiempo_secado);
      $('#producto_colores_disponibles').val(p.colores_disponibles);
      $('#producto_caracteristicas').val(p.caracteristicas);
      $('#producto_texturas').val(p.texturas);
      $('#producto_forma').val(p.forma);
      $('#producto_dimensiones').val(p.dimensiones);
      $('#producto_resistencia').val(p.resistencia);
      $('#producto_colocacion').val(p.colocacion);
      $('#producto_mantenimiento_preventivo').val(p.mantenimiento_preventivo);
      $('#producto_mantenimiento_correctivo').val(p.mantenimiento_correctivo);
      $('#producto_observaciones').val(p.observaciones);
      $('#producto_estatus').val(p.estatus);
      
      // Cargar datos de variante si aplica
      if(p.es_variante == 1 && p.producto_padre_id) {
        $('#es_variante_check').prop('checked', true);
        toggleVarianteFields();
        
        setTimeout(function() {
          $('#producto_padre_id').val(p.producto_padre_id);
          $('#variante_tipo').val(p.variante_tipo);
          $('#variante_valor').val(p.variante_valor);
        }, 500);
      } else {
        $('#es_variante_check').prop('checked', false);
        $('#varianteFields').hide();
      }
      
      // Mostrar catálogo PDF si existe
      if(p.catalogo_pdf) {
        $('#catalogoActual').show();
        $('#linkCatalogoActual').attr('href', '<?=base_url()?>' + p.catalogo_pdf);
      } else {
        $('#catalogoActual').hide();
      }
      
      toggleProveedorField();
      $('#modalProducto').modal('show');
    }
  });
};

window.verProducto = function(id) {
  $.post('<?=base_url();?>produccion/Productos/get_producto_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const p = result.producto;
      
      // Construir HTML con los detalles del producto
      let html = `
        <div class="row">
          ${p.foto_producto ? `
          <div class="col-md-12 mb-3 text-center">
            <img src="<?=base_url();?>${p.foto_producto}" alt="${p.nombre}" class="img-fluid rounded" style="max-height: 300px; object-fit: contain;">
          </div>
          ` : ''}
          
          <div class="col-md-6">
            <h6><i class="fas fa-info-circle"></i> Información General</h6>
            <table class="table table-sm">
              <tr><th width="40%">Código:</th><td>${p.codigo}</td></tr>
              <tr><th>Nombre:</th><td>${p.nombre}</td></tr>
              ${p.alias ? `<tr><th>Alias:</th><td>${p.alias}</td></tr>` : ''}
              <tr><th>Categoría:</th><td>${p.categoria_nombre}</td></tr>
              <tr><th>Tipo:</th><td><span class="badge bg-${p.tipo_producto == 'Fabricado' ? 'primary' : 'info'}">${p.tipo_producto}</span></td></tr>
              <tr><th>Estatus:</th><td><span class="badge bg-${p.estatus == 'Activo' ? 'success' : 'secondary'}">${p.estatus}</span></td></tr>
            </table>
            
            <h6><i class="fas fa-barcode"></i> Códigos</h6>
            <table class="table table-sm">
              <tr><th width="40%">Código de Barras:</th><td>${p.codigo_barras || 'N/A'}</td></tr>
              <tr><th>SKU:</th><td>${p.sku || 'N/A'}</td></tr>
            </table>
            
            ${p.rendimiento || p.peso_bruto || p.tiempo_secado || p.colores_disponibles ? `
            <h6><i class="fas fa-info-circle"></i> Datos Técnicos</h6>
            <table class="table table-sm">
              ${p.rendimiento ? `<tr><th width="40%">Rendimiento:</th><td>${p.rendimiento}</td></tr>` : ''}
              ${p.peso_bruto ? `<tr><th>Peso Bruto:</th><td>${p.peso_bruto} Kg</td></tr>` : ''}
              ${p.tiempo_secado ? `<tr><th>Tiempo Secado:</th><td>${p.tiempo_secado}</td></tr>` : ''}
              ${p.colores_disponibles ? `<tr><th>Colores:</th><td>${p.colores_disponibles}</td></tr>` : ''}
            </table>
            ` : ''}
          </div>
          
          <div class="col-md-6">
            <h6><i class="fas fa-boxes"></i> Inventario</h6>
            <table class="table table-sm">
              <tr><th width="40%">Stock Actual:</th><td><strong class="text-${p.stock_actual <= p.stock_minimo ? 'danger' : 'success'}">${p.stock_actual} ${p.unidad_venta}</strong></td></tr>
              <tr><th>Stock Mínimo:</th><td>${p.stock_minimo} ${p.unidad_venta}</td></tr>
              <tr><th>Stock Máximo:</th><td>${p.stock_maximo} ${p.unidad_venta}</td></tr>
            </table>
            
            <h6><i class="fas fa-dollar-sign"></i> Precios</h6>
            <table class="table table-sm">
              <tr><th width="40%">Precio Venta:</th><td>$${parseFloat(p.precio_venta).toFixed(2)}</td></tr>
              <tr><th>Margen Utilidad:</th><td>${p.margen_utilidad}%</td></tr>
              ${p.costo_produccion ? `<tr><th>Costo Producción:</th><td>$${parseFloat(p.costo_produccion).toFixed(2)}</td></tr>` : ''}
            </table>
          </div>
        </div>
        
        ${p.descripcion ? `<div class="mt-3"><h6>Descripción:</h6><p>${p.descripcion}</p></div>` : ''}
        
        <div class="mt-3 text-end">
          <button class="btn btn-warning" onclick="$('#modalVerProducto').modal('hide'); ajustarStock(${p.id});">
            <i class="fas fa-edit"></i> Ajustar Stock
          </button>
          <button class="btn btn-primary" onclick="$('#modalVerProducto').modal('hide'); editarProducto(${p.id});">
            <i class="fas fa-edit"></i> Editar Producto
          </button>
        </div>
      `;
      
      // Mostrar en un modal simple
      if($('#modalVerProducto').length === 0) {
        $('body').append(`
          <div class="modal fade" id="modalVerProducto" tabindex="-1">
            <div class="modal-dialog modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Detalles del Producto</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalleProductoBody"></div>
              </div>
            </div>
          </div>
        `);
      }
      
      $('#detalleProductoBody').html(html);
      $('#modalVerProducto').modal('show');
    }
  });
};

window.ajustarStock = function(id) {
  $.post('<?=base_url();?>produccion/Productos/get_producto_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const p = result.producto;
      
      let html = `
        <div class="alert alert-info">
          <strong>${p.nombre}</strong><br>
          Stock actual: <strong>${p.stock_actual} ${p.unidad_venta}</strong>
        </div>
        
        <form id="formAjustarStock">
          <input type="hidden" id="ajuste_producto_id" value="${p.id}">
          
          <div class="mb-3">
            <label class="form-label">Tipo de Movimiento</label>
            <select class="form-select" id="ajuste_tipo_movimiento" required>
              <option value="Entrada">Entrada (Aumentar stock)</option>
              <option value="Salida">Salida (Disminuir stock)</option>
              <option value="Ajuste">Ajuste de Inventario</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Cantidad</label>
            <input type="number" step="0.01" class="form-control" id="ajuste_cantidad" required>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Motivo</label>
            <textarea class="form-control" id="ajuste_motivo" rows="2" required></textarea>
          </div>
        </form>
      `;
      
      if($('#modalAjustarStock').length === 0) {
        $('body').append(`
          <div class="modal fade" id="modalAjustarStock" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Ajustar Stock</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="ajusteStockBody"></div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="button" class="btn btn-primary" onclick="guardarAjusteStock()">Guardar Ajuste</button>
                </div>
              </div>
            </div>
          </div>
        `);
      }
      
      $('#ajusteStockBody').html(html);
      $('#modalAjustarStock').modal('show');
    }
  });
};

window.guardarAjusteStock = function() {
  const productoId = $('#ajuste_producto_id').val();
  const tipoMovimiento = $('#ajuste_tipo_movimiento').val();
  const cantidad = $('#ajuste_cantidad').val();
  const motivo = $('#ajuste_motivo').val();
  
  if(!cantidad || !motivo) {
    notifyShow('Complete todos los campos', 'warning');
    return;
  }
  
  $.post('<?=base_url();?>produccion/Productos/ajustar_stock_ajax', {
    'producto_id': productoId,
    'tipo_movimiento': tipoMovimiento,
    'cantidad': cantidad,
    'motivo': motivo,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if(result.success) {
      $('#modalAjustarStock').modal('hide');
      tabla.ajax.reload();
    }
  });
};

window.guardarProducto = function() {
  const id = $('#producto_id').val();
  const url = id ? '<?=base_url();?>produccion/Productos/editar_ajax' : '<?=base_url();?>produccion/Productos/crear_ajax';
  
  // Usar FormData para soportar archivos
  const formData = new FormData($('#formProducto')[0]);
  formData.append('peticion', 'ajax');
  formData.append('<?php echo $this->security->get_csrf_token_name();?>', '<?php echo $this->security->get_csrf_hash();?>');
  
  $.ajax({
    url: url,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(result) {
      result = JSON.parse(result);
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if(result.success) {
        $('#modalProducto').modal('hide');
        tabla.ajax.reload();
      }
    },
    error: function(xhr, status, error) {
      notifyShow('Error al guardar: ' + error, 'danger');
    }
  });
};

// Función para vista previa de imagen
window.previewImage = function(input) {
  const preview = document.getElementById('previewImg');
  const noImageText = document.getElementById('noImageText');
  
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
      noImageText.style.display = 'none';
    };
    
    reader.readAsDataURL(input.files[0]);
  } else {
    preview.src = '';
    preview.style.display = 'none';
    noImageText.style.display = 'block';
  }
};

window.eliminarProducto = function(id) {
  if(!confirm('¿Está seguro de eliminar este producto?')) return;
  
  $.post('<?=base_url();?>produccion/Productos/eliminar_ajax', {
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

window.toggleProveedorField = function() {
  const tipo = $('#producto_tipo_producto').val();
  if(tipo === 'Reventa') {
    $('#divProveedor').show();
  } else {
    $('#divProveedor').hide();
  }
};

// =====================================================
// GESTIÓN DE FORMULACIONES (SUPER INTUITIVO)
// =====================================================

window.gestionarFormulacion = function(productoId) {
  $('#formulacion_producto_id').val(productoId);
  componentesTemporales = [];
  
  // Obtener datos del producto
  $.post('<?=base_url();?>produccion/Productos/get_producto_ajax', {
    'id': productoId,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#formulacionProductoNombre').text(result.producto.nombre);
      
      // Cargar formulación existente
      cargarFormulacion(productoId);
      
      // Cargar listas de insumos y productos
      cargarInsumosSelect();
      cargarProductosSelect();
      
      $('#modalFormulacion').modal('show');
    }
  });
};

function cargarFormulacion(productoId) {
  $.post('<?=base_url();?>produccion/Productos/get_formulacion_ajax', {
    'producto_id': productoId,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success && result.formulacion) {
      formulacionActual = result.formulacion;
      $('#formulacion_id').val(result.formulacion.id);
      $('#formulacion_nombre_version').val(result.formulacion.nombre_version);
      $('#formulacion_cantidad_producida').val(result.formulacion.cantidad_producida);
      $('#formulacion_unidad_produccion').val(result.formulacion.unidad_produccion);
      $('#formulacion_descripcion').val(result.formulacion.descripcion);
      $('#formulacion_costo_mano_obra').val(result.formulacion.costo_mano_obra);
      $('#formulacion_costo_indirecto').val(result.formulacion.costo_indirecto);
      $('#formulacion_costo_total').val(result.formulacion.costo_total);
      
      // Cargar componentes
      if(result.formulacion.componentes) {
        componentesTemporales = result.formulacion.componentes.map(c => ({
          id: c.id,
          tipo: c.tipo_componente,
          item_id: c.tipo_componente === 'Insumo' ? c.insumo_id : c.producto_id,
          nombre: c.tipo_componente === 'Insumo' ? c.insumo_nombre : c.producto_nombre,
          codigo: c.tipo_componente === 'Insumo' ? c.insumo_codigo : c.producto_codigo,
          cantidad: parseFloat(c.cantidad),
          unidad: c.unidad,
          costo_unitario: parseFloat(c.costo_unitario),
          subtotal: parseFloat(c.costo_total)
        }));
        renderizarComponentes();
      }
    } else {
      // Nueva formulación
      $('#formulacion_id').val('');
      $('#formulacion_nombre_version').val('V1.0');
      $('#formulacion_cantidad_producida').val('');
      $('#formulacion_unidad_produccion').val('L');
    }
  });
}

function cargarInsumosSelect() {
  $.post('<?=base_url();?>produccion/Productos/get_insumos_select_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      insumosDisponibles = result.insumos;
      let html = '<option value="">-- Seleccionar Insumo --</option>';
      result.insumos.forEach(function(ins) {
        html += `<option value="${ins.id}" data-precio="${ins.precio_promedio}" data-codigo="${ins.codigo}" data-nombre="${ins.nombre_tecnico}">${ins.codigo} - ${ins.nombre_tecnico} ($${parseFloat(ins.precio_promedio).toFixed(2)})</option>`;
      });
      $('#componente_insumo_id').html(html);
    }
  });
}

function cargarProductosSelect() {
  $.post('<?=base_url();?>produccion/Productos/get_productos_select_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      productosDisponibles = result.productos;
      let html = '<option value="">-- Seleccionar Producto --</option>';
      result.productos.forEach(function(prod) {
        html += `<option value="${prod.id}" data-costo="${prod.costo_produccion}" data-codigo="${prod.codigo}" data-nombre="${prod.nombre}">${prod.codigo} - ${prod.nombre} ($${parseFloat(prod.costo_produccion).toFixed(2)})</option>`;
      });
      $('#componente_producto_id').html(html);
    }
  });
}

window.agregarInsumo = function() {
  const insumoId = $('#componente_insumo_id').val();
  const cantidad = parseFloat($('#componente_insumo_cantidad').val());
  const unidad = $('#componente_insumo_unidad').val();
  const observaciones = $('#componente_insumo_observaciones').val();
  
  if(!insumoId || !cantidad) {
    notifyShow('Seleccione un insumo y cantidad', 'warning');
    return;
  }
  
  const selected = $('#componente_insumo_id').find(':selected');
  const precio = parseFloat(selected.data('precio'));
  const codigo = selected.data('codigo');
  const nombre = selected.data('nombre');
  
  componentesTemporales.push({
    tipo: 'Insumo',
    item_id: insumoId,
    codigo: codigo,
    nombre: nombre,
    cantidad: cantidad,
    unidad: unidad,
    costo_unitario: precio,
    subtotal: cantidad * precio,
    observaciones: observaciones
  });
  
  renderizarComponentes();
  
  // Limpiar campos
  $('#componente_insumo_id').val('');
  $('#componente_insumo_cantidad').val('');
  $('#componente_insumo_observaciones').val('');
};

window.agregarProducto = function() {
  const productoId = $('#componente_producto_id').val();
  const cantidad = parseFloat($('#componente_producto_cantidad').val());
  const unidad = $('#componente_producto_unidad').val();
  const observaciones = $('#componente_producto_observaciones').val();
  
  if(!productoId || !cantidad) {
    notifyShow('Seleccione un producto y cantidad', 'warning');
    return;
  }
  
  const selected = $('#componente_producto_id').find(':selected');
  const costo = parseFloat(selected.data('costo'));
  const codigo = selected.data('codigo');
  const nombre = selected.data('nombre');
  
  componentesTemporales.push({
    tipo: 'Producto',
    item_id: productoId,
    codigo: codigo,
    nombre: nombre,
    cantidad: cantidad,
    unidad: unidad,
    costo_unitario: costo,
    subtotal: cantidad * costo,
    observaciones: observaciones
  });
  
  renderizarComponentes();
  
  // Limpiar campos
  $('#componente_producto_id').val('');
  $('#componente_producto_cantidad').val('');
  $('#componente_producto_observaciones').val('');
};

function renderizarComponentes() {
  let html = '';
  let total = 0;
  
  if(componentesTemporales.length === 0) {
    html = '<tr id="noComponentes"><td colspan="6" class="text-center text-muted">No hay componentes agregados</td></tr>';
  } else {
    componentesTemporales.forEach(function(comp, index) {
      const badgeTipo = comp.tipo === 'Insumo' ? 'primary' : 'success';
      html += `
        <tr>
          <td><span class="badge bg-${badgeTipo}">${comp.tipo}</span></td>
          <td>${comp.codigo} - ${comp.nombre}</td>
          <td>${comp.cantidad} ${comp.unidad}</td>
          <td>$${comp.costo_unitario.toFixed(2)}</td>
          <td>$${comp.subtotal.toFixed(2)}</td>
          <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarComponente(${index})">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>
      `;
      total += comp.subtotal;
    });
  }
  
  $('#tablaComponentes').html(html);
  $('#totalInsumos').text('$' + total.toFixed(2));
  
  // Actualizar costo total
  actualizarCostoTotal();
}

window.eliminarComponente = function(index) {
  componentesTemporales.splice(index, 1);
  renderizarComponentes();
};

function actualizarCostoTotal() {
  const totalInsumos = componentesTemporales.reduce((sum, c) => sum + c.subtotal, 0);
  const manoObra = parseFloat($('#formulacion_costo_mano_obra').val()) || 0;
  const indirectos = parseFloat($('#formulacion_costo_indirecto').val()) || 0;
  const total = totalInsumos + manoObra + indirectos;
  
  $('#formulacion_costo_total').val(total.toFixed(2));
}

window.guardarFormulacion = function() {
  const productoId = $('#formulacion_producto_id').val();
  const cantidadProducida = $('#formulacion_cantidad_producida').val();
  
  if(!cantidadProducida) {
    notifyShow('Ingrese la cantidad que produce esta formulación', 'warning');
    return;
  }
  
  if(componentesTemporales.length === 0) {
    notifyShow('Agregue al menos un componente a la formulación', 'warning');
    return;
  }
  
  // Crear formulación
  $.post('<?=base_url();?>produccion/Productos/crear_formulacion_ajax', {
    'producto_id': productoId,
    'nombre_version': $('#formulacion_nombre_version').val(),
    'descripcion': $('#formulacion_descripcion').val(),
    'cantidad_producida': cantidadProducida,
    'unidad_produccion': $('#formulacion_unidad_produccion').val(),
    'costo_mano_obra': $('#formulacion_costo_mano_obra').val(),
    'costo_indirecto': $('#formulacion_costo_indirecto').val(),
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const formulacionId = result.formulacion_id;
      
      // Agregar componentes
      let componentesGuardados = 0;
      componentesTemporales.forEach(function(comp) {
        $.post('<?=base_url();?>produccion/Productos/agregar_componente_ajax', {
          'formulacion_id': formulacionId,
          'tipo_componente': comp.tipo,
          'insumo_id': comp.tipo === 'Insumo' ? comp.item_id : null,
          'producto_id': comp.tipo === 'Producto' ? comp.item_id : null,
          'cantidad': comp.cantidad,
          'unidad': comp.unidad,
          'observaciones': comp.observaciones,
          'peticion': 'ajax',
          '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
        }, function() {
          componentesGuardados++;
          if(componentesGuardados === componentesTemporales.length) {
            notifyShow('Formulación guardada correctamente', 'success');
            cargarFormulacion(productoId);
          }
        });
      });
    } else {
      notifyShow('Error: ' + result.message, 'danger');
    }
  });
};

// =====================================================
// HISTORIAL DE FORMULACIONES
// =====================================================

let historialProductoId = null;

window.verHistorialFormulaciones = function(productoId) {
  historialProductoId = productoId;
  
  // Obtener nombre del producto
  $.post('<?=base_url();?>produccion/Productos/get_producto_ajax', {
    'id': productoId,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#historial_producto_nombre').text(result.producto.nombre);
    }
  });
  
  // Cargar historial
  cargarHistorialFormulaciones(productoId, '');
  
  // Configurar búsqueda en tiempo real
  $('#busquedaHistorial').off('input').on('input', function() {
    const busqueda = $(this).val();
    cargarHistorialFormulaciones(historialProductoId, busqueda);
  });
  
  $('#modalHistorialFormulaciones').modal('show');
};

function cargarHistorialFormulaciones(productoId, busqueda) {
  $('#listaHistorialFormulaciones').html(`
    <div class="text-center text-muted py-5">
      <i class="fas fa-spinner fa-spin fa-3x"></i>
      <p class="mt-3">Buscando formulaciones...</p>
    </div>
  `);
  
  $.post('<?=base_url();?>produccion/Productos/get_historial_formulaciones_ajax', {
    'producto_id': productoId,
    'busqueda': busqueda,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      if(result.formulaciones.length === 0) {
        $('#listaHistorialFormulaciones').html(`
          <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> 
            ${busqueda ? 'No se encontraron formulaciones con ese criterio de búsqueda' : 'Este producto aún no tiene formulaciones registradas'}
          </div>
        `);
        return;
      }
      
      let html = '<div class="accordion" id="accordionHistorial">';
      
      result.formulaciones.forEach((f, index) => {
        const badgeActiva = f.es_activa == '1' ? '<span class="badge bg-success ms-2"><i class="fas fa-star"></i> Activa</span>' : '<span class="badge bg-secondary ms-2">Histórica</span>';
        const fecha = new Date(f.fecha_creacion).toLocaleDateString('es-MX', {year: 'numeric', month: 'long', day: 'numeric'});
        
        // Estadísticas de ventas
        const statsVentas = f.num_ventas > 0 
          ? `<span class="badge bg-info ms-2"><i class="fas fa-shopping-cart"></i> ${f.num_ventas} ${f.num_ventas === 1 ? 'venta' : 'ventas'}</span>`
          : '<span class="badge bg-warning ms-2"><i class="fas fa-exclamation-triangle"></i> Sin ventas</span>';
        
        html += `
          <div class="accordion-item border-start border-4 ${f.es_activa == '1' ? 'border-success' : 'border-secondary'} mb-2">
            <h2 class="accordion-header">
              <button class="accordion-button ${index > 0 ? 'collapsed' : ''} bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${f.id}">
                <div class="w-100">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong class="fs-5"><i class="fas fa-flask text-primary"></i> Versión ${f.version}: ${f.nombre_version || 'Sin nombre'}</strong>
                      ${badgeActiva}
                      ${statsVentas}
                    </div>
                    <small class="text-muted me-3">Creada: ${fecha}</small>
                  </div>
                </div>
              </button>
            </h2>
            <div id="collapse${f.id}" class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" data-bs-parent="#accordionHistorial">
              <div class="accordion-body">
                <div class="row">
                  <!-- Información de la Formulación -->
                  <div class="col-md-6">
                    <div class="card h-100">
                      <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Información de la Formulación</h6>
                      </div>
                      <div class="card-body">
                        ${f.descripcion ? `<p class="mb-2"><strong>Descripción:</strong><br>${f.descripcion}</p>` : '<p class="text-muted mb-2">Sin descripción</p>'}
                        <p class="mb-2"><strong><i class="fas fa-box"></i> Cantidad producida:</strong> ${f.cantidad_producida} ${f.unidad_produccion}</p>
                        <p class="mb-2"><strong><i class="fas fa-calendar"></i> Fecha de creación:</strong> ${fecha}</p>
                        ${f.ultima_venta ? `<p class="mb-2"><strong><i class="fas fa-clock"></i> Última venta:</strong> ${new Date(f.ultima_venta).toLocaleDateString('es-MX')}</p>` : ''}
                        ${f.total_vendido > 0 ? `<p class="mb-0"><strong><i class="fas fa-chart-line"></i> Total vendido:</strong> <span class="badge bg-success">${f.total_vendido.toFixed(2)} unidades</span></p>` : ''}
                      </div>
                    </div>
                  </div>
                  
                  <!-- Historial de Ventas -->
                  <div class="col-md-6">
                    <div class="card h-100">
                      <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-history"></i> Historial de Ventas (Últimas 10)</h6>
                      </div>
                      <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                        ${f.ventas && f.ventas.length > 0 ? `
                          <div class="list-group list-group-flush">
                            ${f.ventas.map(v => `
                              <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                  <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                      ${v.tipo === 'venta' 
                                        ? '<span class="badge bg-primary me-2"><i class="fas fa-shopping-cart"></i> Venta</span>' 
                                        : '<span class="badge bg-warning me-2"><i class="fas fa-hard-hat"></i> Obra</span>'}
                                      <strong>${v.folio}</strong>
                                    </div>
                                    <div class="text-muted small">
                                      <i class="fas fa-user"></i> ${v.cliente || 'Cliente no especificado'}
                                    </div>
                                    <div class="text-muted small">
                                      <i class="fas fa-calendar"></i> ${new Date(v.fecha_creacion).toLocaleDateString('es-MX')}
                                    </div>
                                  </div>
                                  <div class="text-end">
                                    <span class="badge bg-success">${parseFloat(v.cantidad).toFixed(2)}</span>
                                  </div>
                                </div>
                              </div>
                            `).join('')}
                          </div>
                        ` : `
                          <div class="alert alert-warning mb-0">
                            <i class="fas fa-info-circle"></i> Esta formulación aún no ha sido vendida
                          </div>
                        `}
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Botón para ver detalle completo -->
                <div class="row mt-3">
                  <div class="col-12 text-end">
                    <button class="btn btn-primary" onclick="verDetalleFormulacion(${f.id})">
                      <i class="fas fa-eye"></i> Ver Composición Completa
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
      });
      
      html += '</div>';
      $('#listaHistorialFormulaciones').html(html);
    }
  });
}

// =====================================================
// FUNCIONES PARA SISTEMA DE VARIANTES DE PRODUCTOS
// =====================================================

// Toggle de campos de variante
window.toggleVarianteFields = function() {
  const checked = $('#es_variante_check').is(':checked');
  if(checked) {
    $('#varianteFields').slideDown();
    cargarProductosBase();
  } else {
    $('#varianteFields').slideUp();
    $('#producto_padre_id').val('');
    $('#variante_tipo').val('color');
    $('#variante_valor').val('');
  }
};

// Cargar productos base para selector
function cargarProductosBase() {
  $.post('<?=base_url()?>produccion/Productos/get_productos_base_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      let html = '<option value="">-- Seleccionar Producto Base --</option>';
      result.productos.forEach(p => {
        html += `<option value="${p.id}">${p.codigo} - ${p.nombre}</option>`;
      });
      $('#producto_padre_id').html(html);
    }
  });
}

// Crear variante desde producto existente
window.crearVariante = function(productoId) {
  $.post('<?=base_url()?>produccion/Productos/get_producto_ajax', {
    'id': productoId,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const p = result.producto;
      
      // Abrir modal de nuevo producto
      $('#modalProductoTitle').text('Crear Variante de: ' + p.nombre);
      $('#formProducto')[0].reset();
      $('#producto_id').val('');
      
      // Pre-llenar campos del producto base
      $('#producto_nombre').val(p.nombre);
      $('#producto_alias').val(p.alias);
      $('#producto_descripcion').val(p.descripcion);
      $('#producto_tipo_producto').val(p.tipo_producto);
      $('#producto_unidad_venta').val(p.unidad_venta);
      $('#producto_presentacion_principal').val(p.presentacion_principal);
      $('#producto_contenido_neto').val(p.contenido_neto);
      $('#producto_unidad_contenido').val(p.unidad_contenido);
      $('#producto_rendimiento').val(p.rendimiento);
      $('#producto_peso_bruto').val(p.peso_bruto);
      $('#producto_tiempo_secado').val(p.tiempo_secado);
      
      // Cargar categoría
      setTimeout(function() {
        $('#producto_categoria_id').val(p.categoria_id);
      }, 300);
      
      // Marcar como variante
      $('#es_variante_check').prop('checked', true);
      toggleVarianteFields();
      
      // Esperar a que se carguen los productos base
      setTimeout(function() {
        $('#producto_padre_id').val(productoId);
        $('#variante_tipo').val('color');
        $('#variante_valor').focus();
      }, 500);
      
      $('#modalProducto').modal('show');
    }
  });
};

// Ver familia de productos
window.verFamiliaProductos = function(productoId) {
  // Obtener producto base
  $.post('<?=base_url()?>produccion/Productos/get_producto_ajax', {
    'id': productoId,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#familiaProductoBase').text(result.producto.codigo + ' - ' + result.producto.nombre);
    }
  });
  
  // Obtener variantes
  $.post('<?=base_url()?>produccion/Productos/get_variantes_ajax', {
    'producto_id': productoId,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      let html = '';
      
      if(result.variantes.length === 0) {
        html = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-info-circle"></i> No hay variantes creadas</td></tr>';
      } else {
        result.variantes.forEach(v => {
          const formulacion = v.tiene_formulacion 
            ? `<span class="badge bg-success"><i class="fas fa-flask"></i> V${v.formulacion_version}</span>`
            : '<span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> Sin formulación</span>';
          
          let badgeColor = 'info', icon = 'fa-palette';
          switch(v.variante_tipo) {
            case 'color': badgeColor = 'info'; icon = 'fa-palette'; break;
            case 'tamaño': badgeColor = 'warning'; icon = 'fa-ruler'; break;
            case 'acabado': badgeColor = 'secondary'; icon = 'fa-paint-brush'; break;
          }
          
          html += `
            <tr>
              <td><strong>${v.codigo}</strong></td>
              <td><span class="badge bg-${badgeColor}"><i class="fas ${icon}"></i> ${v.variante_valor}</span></td>
              <td><strong>${v.stock_actual}</strong> ${v.unidad_venta}</td>
              <td>${formulacion}</td>
              <td>
                <button class="btn btn-sm btn-primary" onclick="$('#modalFamiliaProductos').modal('hide'); editarProducto(${v.id});">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-success" onclick="$('#modalFamiliaProductos').modal('hide'); gestionarFormulacion(${v.id});">
                  <i class="fas fa-flask"></i>
                </button>
              </td>
            </tr>
          `;
        });
      }
      
      $('#familiaProductosBody').html(html);
    }
  });
  
  $('#modalFamiliaProductos').modal('show');
};

// Función para ver imagen de producto en zoom
window.verImagenProductoZoom = function(imagenUrl, nombreProducto) {
  $('#lblImagenProductoNombre').text(nombreProducto);
  $('#imgProductoZoom').attr('src', imagenUrl);
  $('#modalImagenProductoZoom').modal('show');
};

window.verDetalleFormulacion = function(formulacionId) {
  // Cerrar el modal de historial antes de abrir el detalle
  $('#modalHistorialFormulaciones').modal('hide');
  
  $.post('<?=base_url();?>produccion/Productos/get_detalle_formulacion_ajax', {
    'formulacion_id': formulacionId,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const f = result.formulacion;
      
      $('#detalleFormulacionTitle').html(`
        <i class="fas fa-flask"></i> ${f.nombre_version || 'Versión ' + f.version}
        ${f.es_activa == '1' ? '<span class="badge bg-success ms-2">Activa</span>' : '<span class="badge bg-secondary ms-2">Histórica</span>'}
      `);
      
      // Construir tabla de componentes
      let html = `
        <div class="alert alert-info">
          <strong>Información de la Formulación</strong><br>
          ${f.descripcion ? `<p class="mb-1">${f.descripcion}</p>` : ''}
          <p class="mb-0"><strong>Cantidad producida:</strong> ${f.cantidad_producida} ${f.unidad_produccion}</p>
        </div>
        
        <h6><i class="fas fa-list"></i> Componentes</h6>
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead class="table-light">
              <tr>
                <th width="${PUEDE_VER_COSTOS ? '10%' : '20%'}">Tipo</th>
                <th width="${PUEDE_VER_COSTOS ? '40%' : '60%'}">Componente</th>
                <th width="${PUEDE_VER_COSTOS ? '15%' : '20%'}">Cantidad</th>
                ${PUEDE_VER_COSTOS ? '<th width="15%">Costo Unit.</th>' : ''}
                ${PUEDE_VER_COSTOS ? '<th width="20%">Subtotal</th>' : ''}
              </tr>
            </thead>
            <tbody>
      `;
      
      if(f.componentes && f.componentes.length > 0) {
        f.componentes.forEach(c => {
          const nombre = c.tipo_componente === 'Insumo' ? c.insumo_nombre : c.producto_nombre;
          html += `
            <tr>
              <td><span class="badge bg-${c.tipo_componente === 'Insumo' ? 'primary' : 'info'}">${c.tipo_componente}</span></td>
              <td>${nombre}</td>
              <td>${c.cantidad} ${c.unidad}</td>
              ${PUEDE_VER_COSTOS ? `<td>$${parseFloat(c.costo_unitario).toFixed(2)}</td>` : ''}
              ${PUEDE_VER_COSTOS ? `<td>$${parseFloat(c.costo_total).toFixed(2)}</td>` : ''}
            </tr>
          `;
        });
      } else {
        const colspan = PUEDE_VER_COSTOS ? '5' : '3';
        html += `<tr><td colspan="${colspan}" class="text-center text-muted">No hay componentes registrados</td></tr>`;
      }
      
      html += `
            </tbody>
            ${PUEDE_VER_COSTOS ? `
            <tfoot class="table-light">
              <tr>
                <td colspan="4" class="text-end"><strong>Total Insumos:</strong></td>
                <td><strong>$${parseFloat(f.costo_total_insumos).toFixed(2)}</strong></td>
              </tr>
            </tfoot>
            ` : ''}
          </table>
        </div>
      `;
      
      // Sección de costos - solo si tiene permiso
      if(PUEDE_VER_COSTOS) {
        html += `
        <h6><i class="fas fa-calculator"></i> Costos</h6>
        <table class="table table-sm">
          <tr>
            <th width="60%">Costo de Insumos:</th>
            <td>$${parseFloat(f.costo_total_insumos).toFixed(2)}</td>
          </tr>
          <tr>
            <th>Costo de Mano de Obra:</th>
            <td>$${parseFloat(f.costo_mano_obra).toFixed(2)}</td>
          </tr>
          <tr>
            <th>Costos Indirectos:</th>
            <td>$${parseFloat(f.costo_indirecto).toFixed(2)}</td>
          </tr>
          <tr class="table-light">
            <th><strong>COSTO TOTAL:</strong></th>
            <td><strong>$${parseFloat(f.costo_total).toFixed(2)}</strong></td>
          </tr>
        </table>
        `;
      }
      
      html += `
        <div class="alert alert-secondary">
          <small>
            <i class="fas fa-info-circle"></i> 
            Esta es una vista de solo lectura. Para modificar la formulación activa, use el botón "Gestionar Formulación" desde la lista de productos.
          </small>
        </div>
      `;
      
      $('#detalleFormulacionBody').html(html);
      
      // Esperar a que se cierre el modal anterior antes de abrir el nuevo
      setTimeout(function() {
        $('#modalDetalleFormulacion').modal('show');
      }, 300);
    }
  });
};

// Inicializar cuando jQuery esté disponible
if (typeof jQuery !== 'undefined') {
  $(document).ready(initProductos);
} else {
  // Fallback si jQuery no está cargado aún
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initProductos);
    } else {
      console.error('jQuery no está disponible. Asegúrate de que jQuery esté cargado antes de este script.');
    }
  });
}
</script>
<!-- Cache buster: v2.0 -->
