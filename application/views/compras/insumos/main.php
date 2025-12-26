<?php
/**
 * Vista principal de Insumos
 * Listado de insumos con DataTables y estadísticas
 */
?>
<div class="container-fluid p-0">

  <!-- Breadcrumb -->
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
   
  <!-- Titulo de la pagina -->
  <h1 class="h3 mb-3"><?php echo $headTitle;?></h1>

  <!-- Cards de estadísticas -->
  <div class="row">
    <!-- Total Insumos Activos -->
    <div class="col-lg-6 col-xl-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Total Insumos</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['total_activos']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-boxes text-primary" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Insumos activos en catálogo</small>
        </div>
      </div>
    </div>

    <!-- Stock Bajo -->
    <div class="col-lg-6 col-xl-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Stock Bajo</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['stock_bajo']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-exclamation-triangle text-warning" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Insumos que necesitan reorden</small>
        </div>
      </div>
    </div>

    <!-- Valor de Inventario -->
    <div class="col-lg-6 col-xl-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Valor Inventario</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-12">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                $<?php echo number_format($response['stats']['valor_inventario'], 2); ?>
              </h3>
            </div>
          </div>
          <small class="text-muted">Valor aproximado del inventario</small>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla de insumos -->
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Catálogo de Insumos</h5>
            <button class="btn btn-primary btn-sm" onclick="mostrarModalNuevo()">
              <i class="fas fa-plus"></i> Nuevo Insumo
            </button>
          </div>
        </div>
        <div class="card-body">
          <!-- Filtros -->
          <div class="row mb-3">
            <div class="col-md-3">
              <label class="form-label">Categoría</label>
              <select class="form-select form-select-sm" id="filtroCategoria">
                <option value="">Todas las categorías</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Estatus</label>
              <select class="form-select form-select-sm" id="filtroEstatus">
                <option value="">Todos</option>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">&nbsp;</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="filtroStockBajo">
                <label class="form-check-label" for="filtroStockBajo">
                  Solo stock bajo
                </label>
              </div>
            </div>
          </div>

          <!-- DataTable -->
          <table id="tablaInsumos" class="table table-striped table-hover" style="width:100%">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>UM</th>
                <th>Stock</th>
                <th>Precio Prom.</th>
                <th>Estatus</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>              
            </tbody>
            <tfoot>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>UM</th>
                <th>Stock</th>
                <th>Precio Prom.</th>
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

<!-- Modal: Nuevo/Editar Insumo -->
<div class="modal fade" id="modalInsumo" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalInsumoTitle">Nuevo Insumo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formInsumo">
          <input type="hidden" name="id" id="insumo_id">
          
          <div class="row">
            <!-- Columna Izquierda -->
            <div class="col-md-6">
              <h6 class="mb-3">Información Básica</h6>
              
              <div class="mb-3">
                <label class="form-label">Código/SKU</label>
                <input type="text" class="form-control" name="codigo" id="insumo_codigo" placeholder="Auto-generado si vacío">
                <small class="text-muted">Dejar vacío para generar automáticamente</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Nombre Técnico <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nombre_tecnico" id="insumo_nombre_tecnico" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Alias/Nombre Comercial</label>
                <input type="text" class="form-control" name="alias" id="insumo_alias">
              </div>

              <div class="mb-3">
                <label class="form-label">Marca</label>
                <input type="text" class="form-control" name="marca" id="insumo_marca">
              </div>

              <div class="mb-3">
                <label class="form-label">Categoría <span class="text-danger">*</span></label>
                <select class="form-select" name="categoria_id" id="insumo_categoria_id" required>
                  <option value="">-- Seleccionar --</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" name="descripcion" id="insumo_descripcion" rows="2"></textarea>
              </div>
            </div>

            <!-- Columna Derecha -->
            <div class="col-md-6">
              <h6 class="mb-3">Control de Stock</h6>
              
              <div class="mb-3">
                <label class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                <select class="form-select" name="unidad_medida" id="insumo_unidad_medida" required>
                  <option value="">-- Seleccionar --</option>
                  <option value="kg">Kilogramo (kg)</option>
                  <option value="g">Gramo (g)</option>
                  <option value="mg">Miligramo (mg)</option>
                  <option value="L">Litro (L)</option>
                  <option value="mL">Mililitro (mL)</option>
                  <option value="Galón">Galón</option>
                  <option value="pza">Pieza (pza)</option>
                  <option value="m">Metro (m)</option>
                  <option value="Cubeta">Cubeta</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Stock Actual <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="stock_actual" id="insumo_stock_actual" value="0" min="0" step="0.01" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Stock Mínimo <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="stock_minimo" id="insumo_stock_minimo" value="0" min="0" step="0.01" required>
                <small class="text-muted">Nivel de reorden</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Stock Máximo</label>
                <input type="number" class="form-control" name="stock_maximo" id="insumo_stock_maximo" min="0" step="0.01">
                <small class="text-muted">Opcional</small>
              </div>

              <div class="mb-3">
                <label class="form-label">Estatus</label>
                <select class="form-select" name="estatus" id="insumo_estatus">
                  <option value="Activo">Activo</option>
                  <option value="Inactivo">Inactivo</option>
                </select>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarInsumo()">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';
  
  let tabla;
  let insumoEditando = null;

  function initInsumos() {
    cargarCategoriasFiltro();
    cargarCategoriasSelect();
    inicializarDataTable();
    
    // Filtros
    $('#filtroCategoria, #filtroEstatus, #filtroStockBajo').on('change', function() {
      tabla.ajax.reload();
    });
  }

  function inicializarDataTable() {
    tabla = $('#tablaInsumos').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '<?=base_url();?>compras/Insumos/lista_ajax',
        type: 'POST',
        data: function(d) {
          d.peticion = 'ajax';
          d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
          d.filtro_categoria = $('#filtroCategoria').val();
          d.filtro_estatus = $('#filtroEstatus').val();
          d.filtro_stock_bajo = $('#filtroStockBajo').is(':checked') ? '1' : '0';
        }
      },
      columns: [
        { data: 0 },  // Código
        { data: 1 },  // Nombre (ya formateado en controlador)
        { data: 2 },  // Categoría
        { data: 3 },  // Marca
        { data: 4 },  // Unidad de medida
        { data: 5 },  // Stock (ya formateado en controlador)
        { data: 6 },  // Precio (ya formateado en controlador)
        { data: 7 },  // Estatus (ya formateado en controlador)
        { data: 8, orderable: false }  // Acciones
      ],
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
      },
      pageLength: 25,
      order: [[1, 'asc']]
    });
  }

  function cargarCategoriasFiltro() {
    $.post('<?=base_url();?>compras/Categorias/lista_ajax', {
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        let html = '<option value="">Todas las categorías</option>';
        result.categorias.forEach(function(cat) {
          html += `<option value="${cat.id}">${cat.nombre}</option>`;
          if(cat.subcategorias) {
            cat.subcategorias.forEach(function(sub) {
              html += `<option value="${sub.id}">&nbsp;&nbsp;${sub.nombre}</option>`;
            });
          }
        });
        $('#filtroCategoria').html(html);
      }
    });
  }

  function cargarCategoriasSelect() {
    $.post('<?=base_url();?>compras/Categorias/lista_ajax', {
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        let html = '<option value="">-- Seleccionar --</option>';
        result.categorias.forEach(function(cat) {
          html += `<option value="${cat.id}">${cat.nombre} (${cat.tipo})</option>`;
          if(cat.subcategorias) {
            cat.subcategorias.forEach(function(sub) {
              html += `<option value="${sub.id}">&nbsp;&nbsp;${sub.nombre} (${sub.tipo})</option>`;
            });
          }
        });
        $('#insumo_categoria_id').html(html);
      }
    });
  }

  window.mostrarModalNuevo = function() {
    insumoEditando = null;
    $('#modalInsumoTitle').text('Nuevo Insumo');
    $('#formInsumo')[0].reset();
    $('#insumo_id').val('');
    $('#insumo_estatus').val('Activo');
    $('#modalInsumo').modal('show');
  };

  window.mostrarModalEditar = function(id) {
    insumoEditando = id;
    $('#modalInsumoTitle').text('Editar Insumo');
    
    $.post('<?=base_url();?>compras/Insumos/get_insumo_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        const ins = result.insumo;
        $('#insumo_id').val(ins.id);
        $('#insumo_codigo').val(ins.codigo);
        $('#insumo_nombre_tecnico').val(ins.nombre_tecnico);
        $('#insumo_alias').val(ins.alias);
        $('#insumo_marca').val(ins.marca);
        $('#insumo_categoria_id').val(ins.categoria_id);
        $('#insumo_descripcion').val(ins.descripcion);
        $('#insumo_unidad_medida').val(ins.unidad_medida);
        $('#insumo_stock_actual').val(ins.stock_actual);
        $('#insumo_stock_minimo').val(ins.stock_minimo);
        $('#insumo_stock_maximo').val(ins.stock_maximo);
        $('#insumo_estatus').val(ins.estatus);
        
        $('#modalInsumo').modal('show');
      }
    });
  };

  window.guardarInsumo = function() {
    const formData = $('#formInsumo').serialize();
    const url = insumoEditando ? 
      '<?=base_url();?>compras/Insumos/editar_ajax' : 
      '<?=base_url();?>compras/Insumos/crear_ajax';

    $.post(url, 
      formData + '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>',
      function(result) {
        result = JSON.parse(result);
        if(result.success) {
          notifyShow(result.message, 'success');
          $('#modalInsumo').modal('hide');
          tabla.ajax.reload();
        } else {
          notifyShow('Error: ' + result.message, 'danger');
        }
      }
    );
  };

  window.eliminarInsumo = function(id) {
    if(!confirm('¿Estás seguro de eliminar este insumo?')) return;

    $.post('<?=base_url();?>compras/Insumos/eliminar_ajax', {
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

  // Inicializar cuando jQuery esté disponible
  if (typeof jQuery !== 'undefined') {
    $(document).ready(initInsumos);
  } else {
    window.addEventListener('load', function() {
      if (typeof jQuery !== 'undefined') {
        $(document).ready(initInsumos);
      }
    });
  }
})();
</script>
