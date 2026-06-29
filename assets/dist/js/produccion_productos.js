let tabla;
let formulacionActual = null;
let componentesTemporales = [];
let insumosDisponibles = [];
let productosDisponibles = [];

function initProductos() {
  limpiarBackdropsModal();
  inicializarDataTable();

  // Buscador propio (siempre editable, independiente del buscador interno de DataTables)
  let buscarTimer = null;
  $('#buscarProductos').on('input', function() {
    clearTimeout(buscarTimer);
    const val = this.value;
    buscarTimer = setTimeout(function() {
      if (tabla) {
        tabla.search(val).draw();
      }
    }, 350);
  });

  // Filtros
  $('#filtroTipo, #filtroEstatus, #filtroStock').on('change', function() {
    if (tabla) tabla.ajax.reload();
  });

  $('#btnLimpiarFiltrosProductos').on('click', function() {
    $('#buscarProductos').val('');
    $('#filtroTipo, #filtroEstatus, #filtroStock').val('');
    if (tabla) {
      tabla.search('').draw();
      tabla.ajax.reload();
    }
  });

  // Chips de búsqueda rápida (semielaborados frecuentes)
  $(document).on('click', '.btn-chip-buscar', function() {
    const term = $(this).data('term') || '';
    $('#buscarProductos').val(term).trigger('input');
  });

  // Event listeners para actualizar costo total de formulación
  $('#formulacion_costo_mano_obra, #formulacion_costo_indirecto').on('input', actualizarCostoTotal);
}

/** Elimina backdrops de Bootstrap que bloquean clics en la página */
function limpiarBackdropsModal() {
  document.querySelectorAll('.modal-backdrop').forEach(function(el) { el.remove(); });
  document.body.classList.remove('modal-open');
  document.body.style.removeProperty('overflow');
  document.body.style.removeProperty('padding-right');
}

function inicializarDataTable() {
  if ($.fn.DataTable.isDataTable('#tablaProductos')) {
    $('#tablaProductos').DataTable().destroy();
    $('#tablaProductos tbody').empty();
  }

  tabla = $('#tablaProductos').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    autoWidth: false,
    dom: 'lrtip', // sin buscador interno de DT — usamos #buscarProductos
    ajax: {
      url: BASE_URL+'produccion/Productos/lista_ajax',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d[CSRF_TOKEN_NAME] = CSRF_HASH;
        d.filtro_tipo = $('#filtroTipo').val();
        d.filtro_estatus = $('#filtroEstatus').val();
        d.filtro_stock = $('#filtroStock').val();
        // Sincronizar búsqueda externa con server-side
        if ($('#buscarProductos').val()) {
          d.search = d.search || {};
          d.search.value = $('#buscarProductos').val();
        }
      },
      error: function(xhr) {
        console.error('Error lista_ajax productos:', xhr.responseText);
        limpiarBackdropsModal();
        if (typeof toastr !== 'undefined') {
          toastr.error('Error al cargar el catálogo de productos');
        }
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
    order: [[0, 'desc']],
    drawCallback: function() {
      // Recalcular anchos después de cada draw para evitar brincos
      this.api().columns.adjust();
    }
  });
}

function cargarCategorias() {
  $.post(BASE_URL+'produccion/Productos/get_categorias_select_ajax', {
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(result) {
    try {
      result = JSON.parse(result);
      if(result.success) {
        let html = '<option value="">-- Seleccionar --</option>';
        result.categorias.forEach(function(cat) {
          html += `<option value="${cat.id}">${cat.nombre}</option>`;
        });
        $('#producto_categoria_id').html(html);
      } else {
        console.error('Error en categorías:', result.message);
      }
    } catch(e) {
      console.error('Error al parsear categorías:', e);
    }
  }).fail(function(xhr, status, error) {
    console.error('Error AJAX categorías:', status, error);
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
  
  $.post(BASE_URL+'produccion/Productos/get_producto_ajax', {
    'id': id,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
        $('#linkCatalogoActual').attr('href', BASE_URL+'' + p.catalogo_pdf);
      } else {
        $('#catalogoActual').hide();
      }
      
      toggleProveedorField();
      $('#modalProducto').modal('show');
    }
  });
};

window.verProducto = function(id) {
  $.post(BASE_URL+'produccion/Productos/get_producto_ajax', {
    'id': id,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const p = result.producto;
      
      // Construir HTML con los detalles del producto
      let html = `
        <div class="row">
          ${p.foto_producto ? `
          <div class="col-md-12 mb-3 text-center">
            <img src="${BASE_URL}${p.foto_producto}" alt="${p.nombre}" class="img-fluid rounded" style="max-height: 300px; object-fit: contain;">
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
  $.post(BASE_URL+'produccion/Productos/get_producto_ajax', {
    'id': id,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
  
  $.post(BASE_URL+'produccion/Productos/ajustar_stock_ajax', {
    'producto_id': productoId,
    'tipo_movimiento': tipoMovimiento,
    'cantidad': cantidad,
    'motivo': motivo,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
  const url = id ? BASE_URL+'produccion/Productos/editar_ajax' : BASE_URL+'produccion/Productos/crear_ajax';
  
  // Usar FormData para soportar archivos
  const formData = new FormData($('#formProducto')[0]);
  formData.append('peticion', 'ajax');
  formData.append(CSRF_TOKEN_NAME, CSRF_HASH);
  
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
  
  $.post(BASE_URL+'produccion/Productos/eliminar_ajax', {
    'id': id,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
  $.post(BASE_URL+'produccion/Productos/get_producto_ajax', {
    'id': productoId,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
  $.post(BASE_URL+'produccion/Productos/get_formulacion_ajax', {
    'producto_id': productoId,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
          porcentaje: c.porcentaje != null ? parseFloat(c.porcentaje) : null,
          costo_unitario: parseFloat(c.costo_unitario),
          subtotal: parseFloat(c.costo_total)
        }));
        recalcularPorcentajesTodos();
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
  $.post(BASE_URL+'produccion/Productos/get_insumos_select_ajax', {
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
  $.post(BASE_URL+'produccion/Productos/get_productos_select_ajax', {
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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

window.autoCalcularPorcentajeInsumo = function() {
  const cantidad = parseFloat($('#componente_insumo_cantidad').val()) || 0;
  const totalActual = componentesTemporales.reduce((s, c) => s + c.cantidad, 0);
  const totalConNuevo = totalActual + cantidad;
  if(totalConNuevo > 0) {
    const pct = (cantidad / totalConNuevo * 100).toFixed(2);
    $('#componente_insumo_porcentaje').val(pct);
  }
};

window.autoCalcularPorcentajeProducto = function() {
  const cantidad = parseFloat($('#componente_producto_cantidad').val()) || 0;
  const totalActual = componentesTemporales.reduce((s, c) => s + c.cantidad, 0);
  const totalConNuevo = totalActual + cantidad;
  if(totalConNuevo > 0) {
    const pct = (cantidad / totalConNuevo * 100).toFixed(2);
    $('#componente_producto_porcentaje').val(pct);
  }
};

window.agregarInsumo = function() {
  const insumoId = $('#componente_insumo_id').val();
  const cantidad = parseFloat($('#componente_insumo_cantidad').val());
  const unidad = $('#componente_insumo_unidad').val();
  const porcentaje = parseFloat($('#componente_insumo_porcentaje').val()) || null;
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
    porcentaje: porcentaje,
    costo_unitario: precio,
    subtotal: cantidad * precio,
    observaciones: observaciones
  });
  
  renderizarComponentes();
  recalcularPorcentajesTodos();
  
  // Limpiar campos
  $('#componente_insumo_id').val('');
  $('#componente_insumo_cantidad').val('');
  $('#componente_insumo_porcentaje').val('');
  $('#componente_insumo_observaciones').val('');
};

window.agregarProducto = function() {
  const productoId = $('#componente_producto_id').val();
  const cantidad = parseFloat($('#componente_producto_cantidad').val());
  const unidad = $('#componente_producto_unidad').val();
  const porcentaje = parseFloat($('#componente_producto_porcentaje').val()) || null;
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
    porcentaje: porcentaje,
    costo_unitario: costo,
    subtotal: cantidad * costo,
    observaciones: observaciones
  });
  
  renderizarComponentes();
  recalcularPorcentajesTodos();
  
  // Limpiar campos
  $('#componente_producto_id').val('');
  $('#componente_producto_cantidad').val('');
  $('#componente_producto_porcentaje').val('');
  $('#componente_producto_observaciones').val('');
};

function recalcularPorcentajesTodos() {
  const totalCantidad = componentesTemporales.reduce((s, c) => s + parseFloat(c.cantidad), 0);
  if(totalCantidad > 0) {
    componentesTemporales.forEach(c => {
      // Solo auto-calcular si no tiene porcentaje manual definido previamente
      if(c.porcentaje === null || c.porcentaje === undefined) {
        c.porcentaje = parseFloat((c.cantidad / totalCantidad * 100).toFixed(2));
      }
    });
  }
}

function renderizarComponentes() {
  let html = '';
  let total = 0;
  const totalCantidad = componentesTemporales.reduce((s, c) => s + parseFloat(c.cantidad || 0), 0);
  
  if(componentesTemporales.length === 0) {
    html = '<tr id="noComponentes"><td colspan="7" class="text-center text-muted">No hay componentes agregados</td></tr>';
  } else {
    componentesTemporales.forEach(function(comp, index) {
      const badgeTipo = comp.tipo === 'Insumo' ? 'primary' : 'success';
      const pct = comp.porcentaje != null ? parseFloat(comp.porcentaje).toFixed(1) : (totalCantidad > 0 ? (comp.cantidad / totalCantidad * 100).toFixed(1) : '-');
      const pctBadge = parseFloat(pct) >= 40 ? 'bg-danger' : (parseFloat(pct) >= 20 ? 'bg-warning text-dark' : 'bg-info');
      html += `
        <tr>
          <td><span class="badge bg-${badgeTipo}">${comp.tipo}</span></td>
          <td><strong>${comp.codigo}</strong><br><small class="text-muted">${comp.nombre}</small></td>
          <td>${comp.cantidad} ${comp.unidad}</td>
          <td class="text-center">
            <span class="badge ${pctBadge}">${pct}%</span>
          </td>
          <td>$${parseFloat(comp.costo_unitario || 0).toFixed(2)}</td>
          <td class="text-success"><strong>$${parseFloat(comp.subtotal || 0).toFixed(2)}</strong></td>
          <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="eliminarComponente(${index})" title="Eliminar">
              <i class="fas fa-trash"></i>
            </button>
          </td>
        </tr>
      `;
      total += parseFloat(comp.subtotal || 0);
    });
  }
  
  $('#tablaComponentes').html(html);
  $('#totalInsumos').text('$' + total.toFixed(2));
  
  // Actualizar costo total
  actualizarCostoTotal();
}

window.eliminarComponente = function(index) {
  componentesTemporales.splice(index, 1);
  // Re-calcular porcentajes del resto
  componentesTemporales.forEach(c => { c.porcentaje = null; });
  recalcularPorcentajesTodos();
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
  $.post(BASE_URL+'produccion/Productos/crear_formulacion_ajax', {
    'producto_id': productoId,
    'nombre_version': $('#formulacion_nombre_version').val(),
    'descripcion': $('#formulacion_descripcion').val(),
    'cantidad_producida': cantidadProducida,
    'unidad_produccion': $('#formulacion_unidad_produccion').val(),
    'costo_mano_obra': $('#formulacion_costo_mano_obra').val(),
    'costo_indirecto': $('#formulacion_costo_indirecto').val(),
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const formulacionId = result.formulacion_id;
      
      // Agregar componentes
      let componentesGuardados = 0;
      componentesTemporales.forEach(function(comp) {
        $.post(BASE_URL+'produccion/Productos/agregar_componente_ajax', {
          'formulacion_id': formulacionId,
          'tipo_componente': comp.tipo,
          'insumo_id': comp.tipo === 'Insumo' ? comp.item_id : null,
          'producto_id': comp.tipo === 'Producto' ? comp.item_id : null,
          'cantidad': comp.cantidad,
          'unidad': comp.unidad,
          'porcentaje': comp.porcentaje || '',
          'observaciones': comp.observaciones,
          'peticion': 'ajax',
          [CSRF_TOKEN_NAME]: CSRF_HASH
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
  $.post(BASE_URL+'produccion/Productos/get_producto_ajax', {
    'id': productoId,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#historial_producto_nombre').text(result.producto.nombre);
    }
  });
  
  // Cargar historial
  cargarHistorialFormulaciones(productoId, '');
  
  // Configurar búsqueda reactiva
  $('#busquedaHistorial').off('input').on('input', reloadHistorial);
  $('#historial_cliente_id, #historial_fecha_inicio, #historial_fecha_fin').off('change').on('change', reloadHistorial);
  
  function reloadHistorial() {
    cargarHistorialFormulaciones(historialProductoId);
  }
  
  // Opcional: cargar lista de clientes
  $.post(BASE_URL+'ventas/Clientes/get_clientes_ajax', {
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(res) {
    try {
      let result = JSON.parse(res);
      if (result.success && result.data) {
        let options = '<option value="">Todos los clientes</option>';
        result.data.forEach(c => {
           options += `<option value="${c.id}">${c.razon_social || c.nombre}</option>`;
        });
        $('#historial_cliente_id').html(options);
      }
    } catch(e) {}
  });
  $('#historial_producto_nombre').data('producto_id', productoId);
  
  $('#modalHistorialFormulaciones').modal('show');
};

function cargarHistorialFormulaciones(productoId) {
  let busqueda = $('#busquedaHistorial').val() || '';
  let cliente_id = $('#historial_cliente_id').val() || '';
  let fecha_inicio = $('#historial_fecha_inicio').val() || '';
  let fecha_fin = $('#historial_fecha_fin').val() || '';

  $('#listaHistorialFormulaciones').html(`
    <div class="text-center text-muted py-5">
      <i class="fas fa-spinner fa-spin fa-3x"></i>
      <p class="mt-3">Buscando formulaciones...</p>
    </div>
  `);
  
  $.post(BASE_URL+'produccion/Productos/get_historial_formulaciones_ajax', {
    'producto_id': productoId,
    'busqueda': busqueda,
    'cliente_id': cliente_id,
    'fecha_inicio': fecha_inicio,
    'fecha_fin': fecha_fin,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
                    <div class="me-3">
                      ${statsVentas}
                    </div>
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
                        ${f.cliente_nombre ? `<p class="mb-2"><strong><i class="fas fa-user"></i> Cliente:</strong> ${f.cliente_nombre}</p>` : ''}
                        ${f.comentarios ? `<p class="mb-2"><strong><i class="fas fa-comment"></i> Comentarios:</strong> ${f.comentarios}</p>` : ''}
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
                <div class="row mt-3 border-top pt-3">
                  <div class="col-12 d-flex justify-content-end align-items-center">
                    ${f.es_activa == '1' ? '' : `
                    <button class="btn btn-outline-success me-2" onclick="activarFormulacion(${f.id})">
                      <i class="fas fa-star"></i> Establecer como Default
                    </button>
                    `}
                    <button class="btn btn-outline-primary me-2" onclick="editarFormulacion(${productoId}, ${f.id})">
                      <i class="fas fa-edit"></i> Editar Variación
                    </button>
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
  $.post(BASE_URL+'produccion/Productos/get_productos_base_ajax', {
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
  $.post(BASE_URL+'produccion/Productos/get_producto_ajax', {
    'id': productoId,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
  $.post(BASE_URL+'produccion/Productos/get_producto_ajax', {
    'id': productoId,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      $('#familiaProductoBase').text(result.producto.codigo + ' - ' + result.producto.nombre);
    }
  });
  
  // Obtener variantes
  $.post(BASE_URL+'produccion/Productos/get_variantes_ajax', {
    'producto_id': productoId,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
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
// =====================================================
// EDICIÓN Y ACTIVACIÓN DE FORMULACIONES
// =====================================================

window.activarFormulacion = function(formulacionId) {
  if(!confirm('¿Estás seguro de establecer esta formulación como la versión por defecto para este producto?')) return;
  
  $.post(BASE_URL+'produccion/Productos/activar_formulacion_ajax', {
    'formulacion_id': formulacionId,
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      notifyShow(result.message, 'success');
      // Recargar el historial
      const productoId = $('#historial_producto_nombre').data('producto_id');
      if(productoId) cargarHistorialFormulaciones(productoId);
    } else {
      notifyShow(result.message || 'Error al activar', 'danger');
    }
  });
};

window.editarFormulacion = function(productoId, formulacionId) {
  $('#modalHistorialFormulaciones').modal('hide');
  
  $('#formulacion_producto_id').val(productoId);
  componentesTemporales = [];
  
  // Obtener producto
  $.post(BASE_URL+'produccion/Productos/get_producto_ajax', {
    'id': productoId,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(resProd) {
    resProd = JSON.parse(resProd);
    if(resProd.success) {
      $('#formulacionProductoNombre').text(resProd.producto.nombre);
      
      // Obtener detalle de formulación específica
      $.post(BASE_URL+'produccion/Productos/get_detalle_formulacion_ajax', {
        'formulacion_id': formulacionId,
        'peticion': 'ajax',
        [CSRF_TOKEN_NAME]: CSRF_HASH
      }, function(result) {
        result = JSON.parse(result);
        if(result.success && result.formulacion) {
          const f = result.formulacion;
          $('#formulacion_id').val(f.id);
          $('#formulacion_nombre_version').val(f.nombre_version);
          $('#formulacion_cantidad_producida').val(f.cantidad_producida);
          $('#formulacion_unidad_produccion').val(f.unidad_produccion);
          $('#formulacion_descripcion').val(f.descripcion);
          $('#formulacion_costo_mano_obra').val(f.costo_mano_obra);
          $('#formulacion_costo_indirecto').val(f.costo_indirecto);
          $('#formulacion_costo_total').val(f.costo_total);
          
          if(f.componentes) {
            componentesTemporales = f.componentes.map(c => ({
              id: c.id,
              tipo: c.tipo_componente,
              item_id: c.tipo_componente === 'Insumo' ? c.insumo_id : c.producto_id,
              nombre: c.tipo_componente === 'Insumo' ? c.insumo_nombre : c.producto_nombre,
              codigo: c.tipo_componente === 'Insumo' ? c.insumo_codigo : c.producto_codigo,
              cantidad: parseFloat(c.cantidad),
              unidad: c.unidad,
              porcentaje: c.porcentaje != null ? parseFloat(c.porcentaje) : null,
              costo_unitario: parseFloat(c.costo_unitario),
              subtotal: parseFloat(c.costo_total)
            }));
            recalcularPorcentajesTodos();
            renderizarComponentes();
          }
          
          cargarInsumosSelect();
          cargarProductosSelect();
          
          $('#modalFormulacion').modal('show');
        }
      });
    }
  });
};
window.verDetalleFormulacion = function(formulacionId) {
  $('#modalHistorialFormulaciones').modal('hide');

  $('#detalleFormulacionBody').html(`
    <div class="text-center py-5 text-muted">
      <i class="fas fa-spinner fa-spin fa-2x mb-3"></i><br>Cargando formulación...
    </div>`);

  setTimeout(function() { $('#modalDetalleFormulacion').modal('show'); }, 150);

  $.post(BASE_URL + 'produccion/Productos/get_detalle_formulacion_ajax', {
    'formulacion_id': formulacionId,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(result) {
    result = JSON.parse(result);
    if (!result.success) {
      $('#detalleFormulacionBody').html(`<div class="alert alert-danger">Error al cargar la formulación</div>`);
      return;
    }

    const f = result.formulacion;

    // Título del modal
    $('#detalleFormulacionTitle').html(`
      <i class="fas fa-flask text-success me-1"></i>${f.nombre_version || ('Versión ' + f.version)}
      ${f.es_activa == '1'
        ? '<span class="badge bg-success ms-2"><i class="fas fa-star"></i> Activa</span>'
        : '<span class="badge bg-secondary ms-2">Histórica</span>'}
      ${f.cliente_nombre ? `<br><small class="opacity-75 fw-normal"><i class="fas fa-user"></i> ${f.cliente_nombre}</small>` : ''}
    `);

    // ── Cards de metadatos ────────────────────────────────────────────────
    let html = `
      <div class="row mb-3 g-2">
        <div class="col-6 col-md-3">
          <div class="rounded border p-2 text-center bg-light">
            <div class="text-muted small">Por cubeta</div>
            <div class="fw-bold">${f.cantidad_producida} ${f.unidad_produccion}</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="rounded border p-2 text-center bg-light">
            <div class="text-muted small">Rendimiento</div>
            <div class="fw-bold">${f.rendimiento_m2_por_kg ? f.rendimiento_m2_por_kg + ' m²/kg' : '–'}</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="rounded border p-2 text-center bg-light">
            <div class="text-muted small">Cliente</div>
            <div class="fw-bold">${f.cliente_nombre || 'General'}</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="rounded border p-2 text-center bg-light">
            <div class="text-muted small">Versión</div>
            <div class="fw-bold">V${f.version}</div>
          </div>
        </div>
      </div>
      ${f.comentarios ? `<div class="alert alert-light border mb-3 py-2"><i class="fas fa-comment-dots text-muted me-1"></i>${f.comentarios}</div>` : ''}
      ${f.descripcion ? `<div class="alert alert-light border mb-3 py-2"><i class="fas fa-info-circle text-muted me-1"></i>${f.descripcion}</div>` : ''}
    `;

    // ── Agrupar componentes ───────────────────────────────────────────────
    let gruposOrden = [];
    let grupos = {};
    let hayGrupos = false;

    if (f.componentes && f.componentes.length > 0) {
      f.componentes.forEach(c => {
        let g = (c.grupo_color && c.grupo_color.trim() !== '') ? c.grupo_color.trim() : '__sin_grupo__';
        if (g !== '__sin_grupo__') hayGrupos = true;
        if (!grupos[g]) { grupos[g] = []; gruposOrden.push(g); }
        grupos[g].push(c);
      });
    }

    // ── Tabla en formato Excel ────────────────────────────────────────────
    const colCosto = PUEDE_VER_COSTOS;
    html += `
      <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0" style="font-size:.875rem;">
          <thead class="table-dark">
            <tr>
              <th width="28%">Insumo / Componente</th>
              <th width="9%" class="text-center">% BOM</th>
              <th width="12%" class="text-end">kg / cubeta</th>
              <th width="9%" class="text-center">% F.Acuosa</th>
              <th width="12%" class="text-end">kg F.Acuosa</th>
              ${colCosto ? '<th width="12%" class="text-end">Costo Unit.</th>' : ''}
              ${colCosto ? '<th width="12%" class="text-end">Subtotal</th>' : ''}
            </tr>
          </thead>
          <tbody>
    `;

    let totPct = 0, totKg = 0, totFase = 0, totCosto = 0;

    if (gruposOrden.length === 0) {
      const cols = colCosto ? 7 : 5;
      html += `<tr><td colspan="${cols}" class="text-center text-muted py-4">Sin componentes registrados</td></tr>`;
    } else {
      gruposOrden.forEach(key => {
        const grupo = key === '__sin_grupo__' ? '' : key;
        const items = grupos[key];
        const cols  = colCosto ? 7 : 5;

        if (hayGrupos) {
          html += `
            <tr style="background:#dbeafe; border-left:4px solid #2563eb;">
              <td colspan="${cols}" class="py-1 ps-3 fw-bold" style="font-size:.8rem;letter-spacing:.04em;">
                <i class="fas fa-palette text-primary me-1"></i>${grupo || 'Sin grupo'}
              </td>
            </tr>`;
        }

        let subFa = 0, subCosto = 0;

        items.forEach(c => {
          const nombre = c.tipo_componente === 'Insumo' ? (c.insumo_nombre || '–') : (c.producto_nombre || '–');
          const pct    = parseFloat(c.porcentaje || 0);
          const kg     = parseFloat(c.cantidad   || 0);
          const pct_fa = parseFloat(c.porcentaje_fase_acuosa || 0);
          const kg_fa  = parseFloat(c.kg_fase_acuosa || 0);
          const cu     = parseFloat(c.costo_unitario || 0);
          const sub    = parseFloat(c.costo_total    || 0);

          totPct   += pct;
          totKg    += kg;
          totFase  += kg_fa;
          totCosto += sub;
          subFa    += kg_fa;
          subCosto += sub;

          html += `
            <tr>
              <td>
                <strong>${nombre}</strong>
                ${c.observaciones ? `<br><small class="text-muted fst-italic">${c.observaciones}</small>` : ''}
              </td>
              <td class="text-center">
                ${pct > 0 ? `<span class="badge bg-light text-dark border">${pct.toFixed(2)}%</span>` : '<span class="text-muted">–</span>'}
              </td>
              <td class="text-end">${kg > 0 ? kg.toFixed(4) : '0.0000'}</td>
              <td class="text-center">
                ${pct_fa > 0 ? `<span class="badge bg-info bg-opacity-25 text-dark border border-info">${pct_fa.toFixed(2)}%</span>` : '<span class="text-muted">–</span>'}
              </td>
              <td class="text-end text-info">${kg_fa > 0 ? kg_fa.toFixed(4) : '<span class="text-muted">–</span>'}</td>
              ${colCosto ? `<td class="text-end">$${cu.toFixed(2)}</td>` : ''}
              ${colCosto ? `<td class="text-end">$${sub.toFixed(2)}</td>` : ''}
            </tr>`;
        });

        // Subtotal de grupo si hay varios items
        if (hayGrupos && items.length > 1) {
          const cols2 = colCosto ? 7 : 5;
          html += `
            <tr style="background:#eff6ff; font-size:.8rem;">
              <td colspan="${colCosto ? 5 : 3}" class="text-end text-muted fst-italic">↳ Subtotal <em>${grupo || 'Sin grupo'}</em>:</td>
              <td class="text-end fw-bold text-info">${subFa > 0 ? subFa.toFixed(4) : '–'}</td>
              ${colCosto ? `<td></td><td class="text-end fw-bold text-primary">$${subCosto.toFixed(2)}</td>` : ''}
            </tr>`;
        }
      });
    }

    // Fila de totales
    html += `
          </tbody>
          <tfoot class="table-secondary fw-bold">
            <tr>
              <td>TOTALES</td>
              <td class="text-center">${totPct.toFixed(2)}%</td>
              <td class="text-end">${totKg.toFixed(4)}</td>
              <td></td>
              <td class="text-end text-info">${totFase > 0 ? totFase.toFixed(4) : '–'}</td>
              ${colCosto ? `<td></td><td class="text-end text-primary">$${totCosto.toFixed(2)}</td>` : ''}
            </tr>
          </tfoot>
        </table>
      </div>
    `;

    // ── Resumen de costos (sólo con permiso) ─────────────────────────────
    if (colCosto) {
      const mo  = parseFloat(f.costo_mano_obra  || 0);
      const ind = parseFloat(f.costo_indirecto  || 0);
      const tot = parseFloat(f.costo_total      || 0);
      html += `
        <div class="row mt-3 g-2">
          <div class="col-md-4">
            <div class="rounded border p-2 text-center bg-light">
              <div class="text-muted small">Insumos</div>
              <div class="fw-bold">$${totCosto.toFixed(2)}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="rounded border p-2 text-center bg-light">
              <div class="text-muted small">M.O. + Indirectos</div>
              <div class="fw-bold">$${(mo + ind).toFixed(2)}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="rounded border p-2 text-center bg-success text-white">
              <div class="small opacity-75">COSTO TOTAL</div>
              <div class="fw-bold fs-5">$${tot.toFixed(2)}</div>
            </div>
          </div>
        </div>`;
    }

    $('#detalleFormulacionBody').html(html);
  });
};

// Inicializar cuando jQuery esté disponible (scripts.php carga después de app.js)
function _initPaginaProductos() {
  limpiarBackdropsModal();
  // Limpiar backdrop al cerrar cualquier modal de esta página
  document.querySelectorAll('.modal').forEach(function(modalEl) {
    modalEl.addEventListener('hidden.bs.modal', limpiarBackdropsModal);
  });
  if (typeof initProductos === 'function') initProductos();
  if (typeof initCalculadoraExcel === 'function') initCalculadoraExcel();
}
if (typeof jQuery !== 'undefined') {
  jQuery(document).ready(_initPaginaProductos);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      jQuery(document).ready(_initPaginaProductos);
    }
  });
}


// --- FUNCIONALIDADES DE CALCULADORA EXCEL ---
function initCalculadoraExcel() {
  // Cargar productos en el select
  $.post(BASE_URL+'produccion/Productos/get_productos_base_ajax', {
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(res) {
    let result = JSON.parse(res);
    if(result.success) {
      let html = '<option value="">-- Buscar Producto --</option>';
      result.productos.forEach(p => {
        html += `<option value="${p.id}">${p.codigo} - ${p.nombre}</option>`;
      });
      $('#calc_producto_id').html(html);
    }
  });

  // OnChange Producto (evitar handlers duplicados si se recarga)
  $('#calc_producto_id').off('change.calc').on('change.calc', function() {
    let prod_id = $(this).val();
    if(!prod_id) {
      $('#calc_formulacion_id').html('<option value="">-- Seleccionar --</option>').prop('disabled', true);
      return;
    }
    
    $.post(BASE_URL+'produccion/Productos/get_historial_formulaciones_ajax', {
      'producto_id': prod_id,
      'peticion': 'ajax',
      [CSRF_TOKEN_NAME]: CSRF_HASH
    }, function(res) {
      let result = JSON.parse(res);
      if(result.success && result.formulaciones.length > 0) {
        let html = '<option value="">-- Seleccionar Versión --</option>';
        result.formulaciones.forEach(f => {
          html += `<option value="${f.id}">V${f.version} - ${f.nombre_version} ${f.es_activa == 1 ? '(ACTIVA)' : ''}</option>`;
        });
        $('#calc_formulacion_id').html(html).prop('disabled', false);
      } else {
        $('#calc_formulacion_id').html('<option value="">Sin formulaciones</option>').prop('disabled', true);
      }
    });
  });

  // OnChange Formulacion
  $('#calc_formulacion_id').off('change.calc').on('change.calc', function() {
    if($(this).val()) {
      simularProduccion();
    }
  });
}

// Agregar inicialización al cargar la página (después de jQuery)
// initCalculadoraExcel se llama desde _initPaginaProductos()

function simularProduccion() {
  let form_id = $('#calc_formulacion_id').val();
  let cubetas = $('#calc_cubetas').val();
  let m2 = $('#calc_m2').val();

  if(!form_id) {
    toastr.warning('Seleccione una formulación');
    return;
  }

  $('#tbodyExcelSimulador').html('<tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Calculando...</td></tr>');

  $.post(BASE_URL+'produccion/Productos/calcular_insumos_ajax', {
    'formulacion_id': form_id,
    'cubetas': cubetas,
    'm2': m2,
    'peticion': 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(res) {
    let result = JSON.parse(res);
    if(result.success) {
      renderizarTablaExcel(result.datos);
    } else {
      toastr.error('Error al calcular insumos');
    }
  });
}

function renderizarTablaExcel(datos) {
  let f = datos.formulacion;
  let componentes = datos.componentes;
  
  // Actualizar metadatos
  $('#lbl_calc_cliente').text(f.cliente_nombre || 'General');
  $('#lbl_calc_rendimiento').text(f.rendimiento_m2_por_kg ? f.rendimiento_m2_por_kg + ' m²/kg' : 'No definido');
  $('#lbl_calc_fecha').text(f.fecha_creacion ? new Date(f.fecha_creacion).toLocaleDateString('es-MX') : '-');
  $('#lbl_calc_comentarios').text(f.comentarios || '-');
  $('#alertMetadatosFormulacion').removeClass('d-none');

  // Detectar si hay grupos de color definidos
  let hayGrupos = componentes.some(c => c.grupo_color && c.grupo_color.trim() !== '');

  // Agrupar respetando el orden de aparición
  let gruposOrden = [];
  let grupos = {};
  componentes.forEach(c => {
    let key = (c.grupo_color && c.grupo_color.trim() !== '') ? c.grupo_color.trim() : '__sin_grupo__';
    if(!grupos[key]) { grupos[key] = []; gruposOrden.push(key); }
    grupos[key].push(c);
  });

  let html = '';
  let totalBOM = 0, totalKgBase = 0, totalFaseAc = 0, totalReqKg = 0;

  gruposOrden.forEach(key => {
    let grupo = key === '__sin_grupo__' ? '' : key;
    let items = grupos[key];

    // Separador de grupo (sólo si hay grupos definidos)
    if (hayGrupos) {
      html += `
        <tr style="background:#dbeafe; border-left:4px solid #2563eb;">
          <td colspan="7" class="py-1 ps-3 fw-bold" style="font-size:0.85rem; letter-spacing:.04em;">
            <i class="fas fa-palette text-primary me-1"></i>${grupo || 'Sin grupo'}
          </td>
        </tr>`;
    }

    let subFaseAc = 0, subReqKg = 0;

    items.forEach(c => {
      let nombre = c.tipo_componente === 'Insumo' ? (c.insumo_nombre || c.producto_nombre) : (c.producto_nombre || c.insumo_nombre);
      let pct     = parseFloat(c.porcentaje || 0);
      let kg_base = parseFloat(c.cantidad || 0);
      let pct_fa  = parseFloat(c.porcentaje_fase_acuosa || 0);
      let kg_fa   = parseFloat(c.kg_fase_acuosa_escalado || 0);
      let total   = parseFloat(c.cantidad_escalada || 0);

      totalBOM   += pct;
      totalKgBase+= kg_base;
      subFaseAc  += kg_fa;
      totalFaseAc+= kg_fa;
      totalReqKg += total;
      subReqKg   += total;

      html += `
        <tr data-id="${c.id}" class="fila-componente">
          <td class="text-muted small ps-3">${hayGrupos ? '' : '&mdash;'}</td>
          <td>
            <strong>${nombre}</strong>
            ${c.observaciones ? `<br><small class="text-muted fst-italic">${c.observaciones}</small>` : ''}
          </td>
          <td class="text-center celda-edit celda-pct" data-val="${pct}">
            ${pct > 0 ? `<span class="badge bg-light text-dark border">${pct.toFixed(2)}%</span>` : '<span class="text-muted">-</span>'}
          </td>
          <td class="text-end celda-edit celda-kg" data-val="${kg_base}">
            ${kg_base > 0 ? kg_base.toFixed(4) : '0.0000'}
          </td>
          <td class="text-center celda-edit celda-pct-fase" data-val="${pct_fa}">
            ${pct_fa > 0 ? `<span class="badge bg-info bg-opacity-25 text-dark border border-info">${pct_fa.toFixed(2)}%</span>` : '<span class="text-muted">-</span>'}
          </td>
          <td class="text-end text-info">
            ${kg_fa > 0 ? kg_fa.toFixed(4) : '<span class="text-muted">-</span>'}
          </td>
          <td class="text-end fw-bold" style="background:rgba(37,99,235,.07); color:#1d4ed8;">
            ${total.toFixed(4)}
          </td>
        </tr>`;
    });

    // Subtotales del grupo (sólo si hay grupos Y más de 1 item)
    if (hayGrupos && items.length > 1) {
      html += `
        <tr style="background:#eff6ff; font-size:0.8rem;">
          <td colspan="2" class="text-end text-muted fst-italic">↳ Subtotal <em>${grupo || 'Sin grupo'}</em>:</td>
          <td></td>
          <td></td>
          <td></td>
          <td class="text-end fw-bold text-info">${subFaseAc > 0 ? subFaseAc.toFixed(4) : '-'}</td>
          <td class="text-end fw-bold text-primary">${subReqKg.toFixed(4)}</td>
        </tr>`;
    }
  });

  $('#tbodyExcelSimulador').html(html);

  // Totales en el tfoot
  $('#lbl_total_porcentaje').text(totalBOM.toFixed(2) + '%');
  $('#lbl_total_kg_unidad').text(totalKgBase.toFixed(4));
  $('#lbl_total_kg_fase_acuosa').text(totalFaseAc.toFixed(4));
  $('#lbl_total_requerido_kg').text(totalReqKg.toFixed(4));
  $('#tfootExcelSimulador').show();

  if (PUEDE_VER_COSTOS) {
    $('#accionesEdicionExcel').show();
  }
}

// Edicion Inline (Concepto)
let modoEdicionActivo = false;
function habilitarEdicionInline() {
  modoEdicionActivo = !modoEdicionActivo;
  
  if(modoEdicionActivo) {
    $('#btnHabilitarEdicion').html('<i class="fas fa-times"></i> Cancelar Edición').removeClass('btn-outline-secondary').addClass('btn-secondary');
    $('.btn-edicion-excel').removeClass('d-none');
    
    $('.fila-componente').each(function() {
      let celdaPct = $(this).find('.celda-pct');
      let celdaKg = $(this).find('.celda-kg');
      let celdaPctFase = $(this).find('.celda-pct-fase');
      
      celdaPct.html(`<input type="number" step="0.01" class="form-control form-control-sm in-pct" value="${celdaPct.data('val')}">`);
      celdaKg.html(`<input type="number" step="0.0001" class="form-control form-control-sm in-kg" value="${celdaKg.data('val')}">`);
      celdaPctFase.html(`<input type="number" step="0.01" class="form-control form-control-sm in-pct-fase" value="${celdaPctFase.data('val')}">`);
    });
    
    // Auto calculos (porcentajes)
    $('.in-pct').on('input', function() {
        let pct = parseFloat($(this).val()) || 0;
        let total_vol = parseFloat($('#lbl_total_kg_unidad').text());
        if(total_vol > 0) {
            let kg = (pct / 100) * total_vol;
            $(this).closest('tr').find('.in-kg').val(kg.toFixed(4));
        }
    });
  } else {
    cancelarEdicionInline();
  }
}

function cancelarEdicionInline() {
  modoEdicionActivo = false;
  $('#btnHabilitarEdicion').html('<i class="fas fa-pencil-alt"></i> Editar Inline').addClass('btn-outline-secondary').removeClass('btn-secondary');
  $('.btn-edicion-excel').addClass('d-none');
  simularProduccion(); // Recargar datos limpios
}

function abrirModalImportacionExcel() {
  limpiarBackdropsModal();
  const form = document.getElementById('formImportacionExcel');
  const progreso = document.getElementById('importProgress');
  const resultados = document.getElementById('importResultados');
  const btn = document.getElementById('btnImportar');
  if (form) form.reset();
  if (progreso) progreso.style.display = 'none';
  if (resultados) { resultados.style.display = 'none'; resultados.innerHTML = ''; }
  if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-upload"></i> Iniciar Importación'; }
  const modalEl = document.getElementById('modalImportacionExcel');
  if (modalEl && typeof bootstrap !== 'undefined') {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  } else if (typeof jQuery !== 'undefined') {
    jQuery('#modalImportacionExcel').modal('show');
  }
}

/** Ejecuta la importación Excel vía fetch (sin depender de jQuery) */
function ejecutarImportacionExcel() {
  const form = document.getElementById('formImportacionExcel');
  const fileInput = document.getElementById('excel_file_input');
  if (!fileInput || !fileInput.files.length) {
    if (typeof toastr !== 'undefined') toastr.warning('Selecciona un archivo Excel primero');
    return;
  }

  const formData = new FormData(form || undefined);
  if (!form) formData.append('excel_file', fileInput.files[0]);
  formData.append('peticion', 'ajax');
  if (typeof CSRF_TOKEN_NAME !== 'undefined' && typeof CSRF_HASH !== 'undefined') {
    formData.append(CSRF_TOKEN_NAME, CSRF_HASH);
  }

  const btnImportar = document.getElementById('btnImportar');
  const progreso = document.getElementById('importProgress');
  const resultados = document.getElementById('importResultados');

  if (btnImportar) { btnImportar.disabled = true; btnImportar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...'; }
  if (progreso) progreso.style.display = 'block';
  if (resultados) { resultados.style.display = 'none'; resultados.innerHTML = ''; }

  fetch((typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'produccion/Productos/importar_formulacion_excel_ajax', {
    method: 'POST',
    body: formData
  })
  .then(function(r) { return r.text(); })
  .then(function(raw) {
    let res;
    try { res = JSON.parse(raw); } catch(ex) {
      res = { success: false, message: 'Respuesta inesperada del servidor. Revisa los logs.' };
    }
    _mostrarResultadoImportacion(res);
  })
  .catch(function(err) {
    console.error('Error importación Excel:', err);
    if (progreso) progreso.style.display = 'none';
    if (btnImportar) { btnImportar.disabled = false; btnImportar.innerHTML = '<i class="fas fa-upload"></i> Iniciar Importación'; }
    if (typeof toastr !== 'undefined') toastr.error('Error de conexión. Intenta de nuevo.');
  });
}

function _mostrarResultadoImportacion(res) {
  const btnImportar = document.getElementById('btnImportar');
  const progreso = document.getElementById('importProgress');
  const resultados = document.getElementById('importResultados');

  if (progreso) progreso.style.display = 'none';
  if (btnImportar) { btnImportar.disabled = false; btnImportar.innerHTML = '<i class="fas fa-upload"></i> Importar otro archivo'; }

  let html = '';
  if (!res.success) {
    html = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> <strong>Error:</strong> ' + (res.message || 'Error desconocido') + '</div>';
    if (resultados) { resultados.innerHTML = html; resultados.style.display = 'block'; }
    return;
  }

  const nOk  = (res.importados   || []).length;
  const nErr = (res.errores      || []).length;
  const nAdv = (res.advertencias || []).length;

  html += '<div class="row g-2 mb-3">' +
    '<div class="col-4"><div class="rounded border p-2 text-center bg-success bg-opacity-10">' +
    '<div class="fw-bold fs-4 text-success">' + nOk + '</div><div class="small text-muted">Importadas</div></div></div>' +
    '<div class="col-4"><div class="rounded border p-2 text-center bg-warning bg-opacity-10">' +
    '<div class="fw-bold fs-4 text-warning">' + nAdv + '</div><div class="small text-muted">Advertencias</div></div></div>' +
    '<div class="col-4"><div class="rounded border p-2 text-center bg-danger bg-opacity-10">' +
    '<div class="fw-bold fs-4 text-danger">' + nErr + '</div><div class="small text-muted">Errores</div></div></div></div>';

  if (nOk > 0) {
    html += '<h6 class="text-success"><i class="fas fa-check-circle"></i> Formulaciones importadas</h6>' +
      '<div class="table-responsive mb-3"><table class="table table-sm table-bordered">' +
      '<thead class="table-success"><tr><th>Referencia</th><th>Producto</th><th>Versión</th><th>Cliente</th><th>Comp.</th><th>Grupos</th></tr></thead><tbody>';
    res.importados.forEach(function(d) {
      const prodBadge = d.producto_creado
        ? d.producto + ' <span class="badge bg-warning text-dark ms-1">Nuevo</span>'
        : d.producto;
      html += '<tr><td><strong>' + d.ref + '</strong></td><td>' + prodBadge + '</td>' +
        '<td><span class="badge bg-primary">V' + d.version + '</span></td>' +
        '<td>' + (d.cliente || '<span class="text-muted">—</span>') + '</td>' +
        '<td><span class="badge bg-secondary">' + d.num_componentes + '</span></td>' +
        '<td><span class="badge bg-info">' + d.num_grupos + '</span></td></tr>';
    });
    html += '</tbody></table></div>';
  }
  if (nAdv > 0) {
    html += '<div class="alert alert-warning"><h6><i class="fas fa-exclamation-triangle"></i> Advertencias</h6><ul class="mb-0 ps-3 small">';
    res.advertencias.forEach(function(a) { html += '<li>' + a + '</li>'; });
    html += '</ul></div>';
  }
  if (nErr > 0) {
    html += '<div class="alert alert-danger"><h6><i class="fas fa-times-circle"></i> Errores</h6><ul class="mb-0 ps-3 small">';
    res.errores.forEach(function(er) { html += '<li>' + er + '</li>'; });
    html += '</ul></div>';
  }
  if (nOk > 0) {
    html += '<div class="alert alert-secondary small"><i class="fas fa-info-circle"></i> ' +
      'Las formulaciones se crean como <strong>inactivas</strong>. Actívalas desde el historial de cada producto.</div>';
  }
  if (resultados) { resultados.innerHTML = html; resultados.style.display = 'block'; }

  limpiarBackdropsModal();

  if (nOk > 0 && typeof initCalculadoraExcel === 'function') initCalculadoraExcel();
  if (nOk > 0 && typeof tabla !== 'undefined' && tabla) {
    try { tabla.ajax.reload(null, false); } catch(ex) {}
  }
}

function guardarFormulacionExcel(esNueva) {
  const formulacion_id = $('#calc_formulacion_id').val();
  const producto_id    = $('#calc_producto_id').val();

  if (!formulacion_id || !producto_id) {
    toastr.warning('Seleccione producto y formulación primero');
    return;
  }

  // Recopilar componentes editados de la tabla
  let componentes = [];
  $('#tablaExcelSimulador tbody .fila-componente').each(function() {
    const id_comp   = $(this).data('id');
    const pct       = parseFloat($(this).find('.in-pct, .celda-pct').first().val() || $(this).find('.celda-pct').data('val') || 0);
    const kg        = parseFloat($(this).find('.in-kg, .celda-kg').first().val() || $(this).find('.celda-kg').data('val') || 0);
    const pct_fa    = parseFloat($(this).find('.in-pct-fase, .celda-pct-fase').first().val() || $(this).find('.celda-pct-fase').data('val') || 0);
    if (id_comp) {
      componentes.push({ id: id_comp, porcentaje: pct, cantidad: kg, porcentaje_fase_acuosa: pct_fa });
    }
  });

  if (componentes.length === 0) {
    toastr.warning('No hay componentes para guardar');
    return;
  }

  const btnNueva   = $('.btn-edicion-excel').eq(0);
  const btnActual  = $('.btn-edicion-excel').eq(1);
  btnNueva.prop('disabled', true);
  btnActual.prop('disabled', true);

  $.post(BASE_URL + 'produccion/Productos/guardar_excel_ajax', {
    'formulacion_id' : formulacion_id,
    'producto_id'    : producto_id,
    'es_nueva'       : esNueva ? 1 : 0,
    'componentes'    : JSON.stringify(componentes),
    'peticion'       : 'ajax',
    [CSRF_TOKEN_NAME]: CSRF_HASH
  }, function(res) {
    let result = JSON.parse(res);
    btnNueva.prop('disabled', false);
    btnActual.prop('disabled', false);

    if (result.success) {
      toastr.success(result.message || (esNueva ? 'Nueva versión guardada' : 'Formulación actualizada'));

      // Si es nueva versión, seleccionarla en el dropdown
      if (esNueva && result.nueva_formulacion_id) {
        // Recargar el selector de formulaciones
        $('#calc_producto_id').trigger('change');
        setTimeout(function() {
          $('#calc_formulacion_id').val(result.nueva_formulacion_id).trigger('change');
        }, 800);
      } else {
        cancelarEdicionInline();
      }
    } else {
      toastr.error(result.message || 'Error al guardar');
    }
  }).fail(function() {
    btnNueva.prop('disabled', false);
    btnActual.prop('disabled', false);
    toastr.error('Error de conexión al guardar');
  });
}
