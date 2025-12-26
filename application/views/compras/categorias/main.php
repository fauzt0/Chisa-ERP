<?php
/**
 * Vista principal - Categorías de Insumos
 */
?>
<div class="container-fluid p-0">
  <!-- Breadcrumb -->
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
   
  <!-- Titulo de la pagina -->
  <h1 class="h3 mb-3"><?php echo $headTitle;?></h1>
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h4 class="mb-0"><i class="fas fa-sitemap"></i> Categorías de Insumos</h4>
          <button class="btn btn-light btn-sm" onclick="mostrarModalNueva()">
            <i class="fas fa-plus"></i> Nueva Categoría
          </button>
        </div>
        <div class="card-body">
          <!-- Buscador -->
          <div class="row mb-3">
            <div class="col-md-6">
              <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="buscarCategoria" placeholder="Buscar categoría...">
              </div>
              <small class="text-muted">Busca por nombre de categoría</small>
            </div>
          </div>
          
          <div id="arbol-categorias">
            <div class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
              </div>
              <p class="mt-2">Cargando categorías...</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Modal: Nueva/Editar Categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalCategoriaTitle">Nueva Categoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formCategoria">
          <input type="hidden" name="id" id="categoria_id">
          
          <div class="mb-3">
            <label class="form-label">Nombre <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" id="categoria_nombre" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Categoría Padre</label>
            <select class="form-select" name="categoria_padre_id" id="categoria_padre_id">
              <option value="">-- Categoría Raíz --</option>
            </select>
            <small class="text-muted">Dejar vacío para crear una categoría raíz</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Tipo <span class="text-danger">*</span></label>
            <select class="form-select" name="tipo" id="categoria_tipo" required>
              <option value="">-- Seleccionar --</option>
              <option value="Materia Prima">Materia Prima</option>
              <option value="Material">Material</option>
              <option value="Servicio">Servicio</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Icono (FontAwesome)</label>
            <div class="input-group">
              <span class="input-group-text"><i id="preview-icono" class="fas fa-box"></i></span>
              <input type="text" class="form-control" name="icono" id="categoria_icono" 
                     placeholder="fa-box" value="fa-box">
            </div>
            <small class="text-muted">Ejemplos: fa-flask, fa-tools, fa-handshake</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" id="categoria_descripcion" rows="2"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Orden</label>
            <input type="number" class="form-control" name="orden" id="categoria_orden" value="0" min="0">
            <small class="text-muted">Orden de visualización (menor = primero)</small>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarCategoria()">
          <i class="fas fa-save"></i> Guardar
        </button>
      </div>
    </div>
  </div>
</div>
<style>
  .categoria-item {
    padding: 10px;
    margin: 5px 0;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    background: #fff;
  }
  .categoria-item:hover {
    background: #f8f9fa;
  }
  .categoria-raiz {
    font-weight: bold;
    background: #e7f3ff;
  }
  .subcategoria {
    margin-left: 30px;
  }
  .categoria-icono {
    width: 30px;
    text-align: center;
  }
</style>
<script>
// Esperar a que jQuery esté disponible
(function() {
  'use strict';
  
  let categoriaEditando = null;

  // Función de inicialización
  function initCategorias() {
    cargarArbolCategorias();
    
    // Preview de icono
    $('#categoria_icono').on('input', function() {
      const icono = $(this).val() || 'fa-box';
      $('#preview-icono').attr('class', 'fas ' + icono);
    });
    
    // Buscador en tiempo real
    $('#buscarCategoria').on('input', function() {
      const termino = $(this).val().toLowerCase().trim();
      filtrarCategorias(termino);
    });
  }
  
  // Filtrar categorías por término de búsqueda
  function filtrarCategorias(termino) {
    if(termino === '') {
      // Mostrar todas
      $('.categoria-item').show();
    } else {
      // Filtrar por nombre
      $('.categoria-item').each(function() {
        const nombre = $(this).find('strong').first().text().toLowerCase();
        if(nombre.includes(termino)) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    }
  }

  function cargarArbolCategorias() {
    $.post('<?=base_url();?>compras/Categorias/lista_ajax', {
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    })
    .done(function(result) {
      try {
        result = JSON.parse(result);
        if(result.success) {
          renderizarArbol(result.categorias);
        } else {
          $('#arbol-categorias').html('<div class="alert alert-warning">No hay categorías</div>');
        }
      } catch(e) {
        console.error('Error parsing JSON:', e);
        $('#arbol-categorias').html('<div class="alert alert-danger">Error al cargar categorías: ' + e.message + '</div>');
      }
    })
    .fail(function(xhr, status, error) {
      console.error('AJAX Error:', status, error);
      console.error('Response:', xhr.responseText);
      $('#arbol-categorias').html('<div class="alert alert-danger">Error de conexión: ' + error + '</div>');
    });
  }

  function renderizarArbol(categorias) {
    if(categorias.length === 0) {
      $('#arbol-categorias').html('<div class="alert alert-info">No hay categorías. Crea la primera.</div>');
      return;
    }

    let html = '';
    categorias.forEach(cat => {
      html += renderizarCategoria(cat, true);
    });
    $('#arbol-categorias').html(html);
  }

  function renderizarCategoria(categoria, esRaiz = false) {
    const claseExtra = esRaiz ? 'categoria-raiz' : 'subcategoria';
    let html = `
      <div class="categoria-item ${claseExtra}">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <span class="categoria-icono"><i class="fas ${categoria.icono}"></i></span>
            <strong>${categoria.nombre}</strong>
            <span class="badge bg-secondary ms-2">${categoria.tipo}</span>
            ${categoria.subcategorias.length > 0 ? `<span class="badge bg-info ms-1">${categoria.subcategorias.length} sub</span>` : ''}
          </div>
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick="mostrarModalEditar(${categoria.id})" title="Editar">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-outline-success" onclick="mostrarModalNuevaSubcategoria(${categoria.id}, '${categoria.tipo}')" title="Agregar subcategoría">
              <i class="fas fa-plus"></i>
            </button>
            <button class="btn btn-outline-danger" onclick="eliminarCategoria(${categoria.id})" title="Eliminar">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
        ${categoria.descripcion ? `<small class="text-muted d-block mt-1">${categoria.descripcion}</small>` : ''}
      </div>
    `;

    // Renderizar subcategorías recursivamente
    if(categoria.subcategorias.length > 0) {
      categoria.subcategorias.forEach(sub => {
        html += renderizarCategoria(sub, false);
      });
    }

    return html;
  }

  // Funciones globales
  window.mostrarModalNueva = function() {
    categoriaEditando = null;
    $('#modalCategoriaTitle').text('Nueva Categoría');
    $('#formCategoria')[0].reset();
    $('#categoria_id').val('');
    cargarSelectPadres();
    $('#modalCategoria').modal('show');
  };

  window.mostrarModalNuevaSubcategoria = function(padreId, tipo) {
    categoriaEditando = null;
    $('#modalCategoriaTitle').text('Nueva Subcategoría');
    $('#formCategoria')[0].reset();
    $('#categoria_id').val('');
    $('#categoria_padre_id').val(padreId);
    $('#categoria_tipo').val(tipo);
    cargarSelectPadres();
    $('#modalCategoria').modal('show');
  };

  window.mostrarModalEditar = function(id) {
    categoriaEditando = id;
    $('#modalCategoriaTitle').text('Editar Categoría');
    
    $.post('<?=base_url();?>compras/Categorias/get_categoria_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        const cat = result.categoria;
        $('#categoria_id').val(cat.id);
        $('#categoria_nombre').val(cat.nombre);
        $('#categoria_tipo').val(cat.tipo);
        $('#categoria_descripcion').val(cat.descripcion);
        $('#categoria_icono').val(cat.icono);
        $('#categoria_orden').val(cat.orden);
        $('#preview-icono').attr('class', 'fas ' + cat.icono);
        
        cargarSelectPadres(id, cat.categoria_padre_id);
        $('#modalCategoria').modal('show');
      }
    });
  };

  function cargarSelectPadres(excluirId = null, seleccionado = null) {
    $.post('<?=base_url();?>compras/Categorias/get_select_padres_ajax', {
      'excluir_id': excluirId,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      if(result.success) {
        let html = '<option value="">-- Categoría Raíz --</option>';
        for(let id in result.categorias) {
          const selected = (seleccionado == id) ? 'selected' : '';
          html += `<option value="${id}" ${selected}>${result.categorias[id]}</option>`;
        }
        $('#categoria_padre_id').html(html);
      }
    });
  }

  window.guardarCategoria = function() {
    const formData = $('#formCategoria').serialize();
    const url = categoriaEditando ? 
      '<?=base_url();?>compras/Categorias/editar_ajax' : 
      '<?=base_url();?>compras/Categorias/crear_ajax';

    $.post(url, 
      formData + '&peticion=ajax&<?php echo $this->security->get_csrf_token_name();?>=<?php echo $this->security->get_csrf_hash();?>',
      function(result) {
        result = JSON.parse(result);
        if(result.success) {
          notifyShow(result.message, 'success');
          $('#modalCategoria').modal('hide');
          cargarArbolCategorias();
        } else {
          notifyShow('Error: ' + result.message, 'danger');
        }
      }
    );
  };

  window.eliminarCategoria = function(id) {
    if(!confirm('¿Estás seguro de eliminar esta categoría?')) return;

    $.post('<?=base_url();?>compras/Categorias/eliminar_ajax', {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(result) {
      result = JSON.parse(result);
      notifyShow(result.message, result.success ? 'success' : 'danger');
      if(result.success) {
        cargarArbolCategorias();
      }
    });
  };

  // Inicializar cuando jQuery esté disponible
  if (typeof jQuery !== 'undefined') {
    $(document).ready(initCategorias);
  } else {
    // Esperar a que jQuery esté disponible
    window.addEventListener('load', function() {
      if (typeof jQuery !== 'undefined') {
        $(document).ready(initCategorias);
      }
    });
  }
})();
</script>