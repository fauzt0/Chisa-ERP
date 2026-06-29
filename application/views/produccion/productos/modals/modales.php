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
                  <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <div class="input-group">
                      <input type="number" step="0.001" class="form-control" id="componente_insumo_cantidad" placeholder="0" oninput="autoCalcularPorcentajeInsumo()">
                      <select class="form-select" id="componente_insumo_unidad" style="max-width:70px;">
                        <option value="L">L</option>
                        <option value="ml">ml</option>
                        <option value="Kg">Kg</option>
                        <option value="g">g</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">% en BOM</label>
                    <div class="input-group">
                      <input type="number" step="0.01" class="form-control" id="componente_insumo_porcentaje" placeholder="Auto">
                      <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Auto-calculado</small>
                  </div>
                  <div class="col-md-2">
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
                  <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <div class="input-group">
                      <input type="number" step="0.001" class="form-control" id="componente_producto_cantidad" placeholder="0" oninput="autoCalcularPorcentajeProducto()">
                      <select class="form-select" id="componente_producto_unidad" style="max-width:70px;">
                        <option value="L">L</option>
                        <option value="ml">ml</option>
                        <option value="Kg">Kg</option>
                        <option value="g">g</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">% en BOM</label>
                    <div class="input-group">
                      <input type="number" step="0.01" class="form-control" id="componente_producto_porcentaje" placeholder="Auto">
                      <span class="input-group-text">%</span>
                    </div>
                    <small class="text-muted">Auto-calculado</small>
                  </div>
                  <div class="col-md-2">
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
                    <th width="8%">Tipo</th>
                    <th width="28%">Componente</th>
                    <th width="13%">Cantidad</th>
                    <th width="8%" class="text-center">%</th>
                    <th width="13%">Costo Unit.</th>
                    <th width="13%">Subtotal</th>
                    <th width="8%">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tablaComponentes">
                  <tr id="noComponentes">
                    <td colspan="7" class="text-center text-muted">No hay componentes agregados</td>
                  </tr>
                </tbody>
                <tfoot class="table-light">
                  <tr>
                    <td colspan="5" class="text-end"><strong>Total Insumos:</strong></td>
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
        <!-- Filtros de Historial -->
        <div class="card bg-light mb-3">
          <div class="card-body py-2">
            <div class="row align-items-end">
              <div class="col-md-3">
                <label class="form-label mb-1">Cliente</label>
                <select class="form-select form-select-sm" id="historial_cliente_id">
                  <option value="">Todos los clientes</option>
                  <!-- Se llena via JS si es necesario -->
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label mb-1">Fecha Desde</label>
                <input type="date" class="form-control form-control-sm" id="historial_fecha_inicio">
              </div>
              <div class="col-md-3">
                <label class="form-label mb-1">Fecha Hasta</label>
                <input type="date" class="form-control form-control-sm" id="historial_fecha_fin">
              </div>
              <div class="col-md-3">
                <label class="form-label mb-1">Buscar (Versión, Comentarios)</label>
                <input type="text" class="form-control form-control-sm" id="busquedaHistorial" placeholder="Buscar...">
              </div>
            </div>
          </div>
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
  <div class="modal-dialog modal-xl">
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

<!-- Modal: Importación Excel de Formulaciones -->
<div class="modal fade" id="modalImportacionExcel" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="fas fa-file-excel"></i> Importar Formulaciones desde Excel
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- Instrucciones del formato esperado -->
        <div class="alert alert-info">
          <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Formato esperado del Excel</h6>
          <div class="row mt-2" style="font-size:0.85rem;">
            <div class="col-md-6">
              <strong>Fila de producto:</strong>
              <code class="d-block mt-1 bg-dark text-success p-1 rounded">CHISA GLASS REF PCH11 &nbsp;|&nbsp; KILOS &nbsp;|&nbsp; 19.000</code>
              <small class="text-muted">Opcionalmente antes: <code>1 CUBETA VENTA [CLIENTE]</code></small>
            </div>
            <div class="col-md-6">
              <strong>Filas de insumo:</strong>
              <code class="d-block mt-1 bg-dark text-success p-1 rounded">BLANCO &nbsp;|&nbsp; 38.42% &nbsp;|&nbsp; &nbsp;|&nbsp; BLANCO &nbsp;|&nbsp; 0.088</code>
              <small class="text-muted">Col A=nombre | B=% | C=%FaseAcuosa | D=nombre | E=kg</small>
            </div>
          </div>
          <hr class="my-2">
          <small>
            <i class="fas fa-magic text-success"></i>
            Los <strong>productos</strong> e <strong>insumos</strong> no encontrados se crean automáticamente con datos básicos.<br>
            Podrás completar sus datos (precios, proveedores, fotos) en el catálogo después de la importación.
          </small>
        </div>

        <!-- Formulario de subida (method=post + onsubmit evita recarga si JS falla) -->
        <form id="formImportacionExcel" method="post" action="#" enctype="multipart/form-data"
              onsubmit="return false;">
          <div class="mb-3">
            <label class="form-label fw-bold">Seleccionar archivo Excel</label>
            <input type="file" class="form-control" id="excel_file_input" name="excel_file"
                   accept=".xlsx,.xls" required>
            <div class="form-text">Formatos aceptados: .xlsx, .xls — Se procesarán todas las hojas del archivo.</div>
          </div>
          <div class="d-grid">
            <button type="button" class="btn btn-success btn-lg" id="btnImportar"
                    onclick="if(typeof ejecutarImportacionExcel==='function') ejecutarImportacionExcel();">
              <i class="fas fa-upload"></i> Iniciar Importación
            </button>
          </div>
        </form>

        <!-- Barra de progreso (oculta inicialmente) -->
        <div id="importProgress" class="mt-3" style="display:none;">
          <div class="text-center text-muted mb-2">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2 mb-0">Procesando archivo... esto puede tardar unos segundos.</p>
          </div>
          <div class="progress">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width:100%"></div>
          </div>
        </div>

        <!-- Resultados (ocultos inicialmente) -->
        <div id="importResultados" class="mt-3" style="display:none;"></div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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


