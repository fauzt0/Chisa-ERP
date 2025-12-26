<?php
/**
 * Vista principal de Gestión de Descuentos
 */
?>

<!-- Breadcrumb -->
<div class="row">
  <div class="col-12">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
        <li class="breadcrumb-item"><a href="#">CRM Ventas</a></li>
        <li class="breadcrumb-item active">Descuentos</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Título -->
<div class="row mb-3">
  <div class="col-md-6">
    <h2><i class="fas fa-percent"></i> Gestión de Descuentos</h2>
  </div>
  <div class="col-md-6 text-end">
    <button type="button" class="btn btn-primary" onclick="mostrarModalNuevo()">
      <i class="fas fa-plus"></i> Nuevo Descuento
    </button>
  </div>
</div>

<!-- Tabla -->
<div class="card">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-list"></i> Lista de Descuentos</h3>
  </div>
  <div class="card-body">
    <table id="tablaDescuentos" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Descuento</th>
          <th>Estatus</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal: Nuevo/Editar -->
<div class="modal fade" id="modalDescuento" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDescuentoTitle">Nuevo Descuento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formDescuento">
          <input type="hidden" id="descuento_id">
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Nombre <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="descuento_nombre" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Estatus <span class="text-danger">*</span></label>
              <select class="form-select" id="descuento_estatus" required>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" id="descuento_descripcion" rows="2"></textarea>
          </div>
          
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Tipo de Descuento <span class="text-danger">*</span></label>
              <select class="form-select" id="descuento_tipo" required onchange="cambiarTipoDescuento()">
                <option value="Porcentaje">Porcentaje (%)</option>
                <option value="Monto Fijo">Monto Fijo ($)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Valor <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text" id="descuento_simbolo">%</span>
                <input type="number" step="0.01" class="form-control" id="descuento_valor" required>
              </div>
              <small class="text-muted" id="descuento_ayuda">Ingrese el porcentaje de descuento</small>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="guardarDescuento()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<script>
let tabla;

function initDescuentos() {
  inicializarDataTable();
}

function inicializarDataTable() {
  tabla = $('#tablaDescuentos').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '<?=base_url();?>ventas/Descuentos/lista_ajax',
      type: 'POST',
      data: function(d) {
        d.peticion = 'ajax';
        d['<?php echo $this->security->get_csrf_token_name();?>'] = '<?php echo $this->security->get_csrf_hash();?>';
      }
    },
    columns: [
      { data: 0 },
      { data: 1 },
      { data: 2 },
      { data: 3 },
      { data: 4, orderable: false }
    ],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
    },
    order: [[0, 'asc']]
  });
}

function mostrarModalNuevo() {
  $('#modalDescuentoTitle').text('Nuevo Descuento');
  $('#formDescuento')[0].reset();
  $('#descuento_id').val('');
  $('#descuento_estatus').val('Activo');
  $('#descuento_tipo').val('Porcentaje');
  cambiarTipoDescuento();
  $('#modalDescuento').modal('show');
}

function cambiarTipoDescuento() {
  const tipo = $('#descuento_tipo').val();
  if(tipo == 'Porcentaje') {
    $('#descuento_simbolo').text('%');
    $('#descuento_ayuda').text('Ingrese el porcentaje de descuento (ej: 10 para 10%)');
  } else {
    $('#descuento_simbolo').text('$');
    $('#descuento_ayuda').text('Ingrese el monto fijo de descuento');
  }
}

window.editarDescuento = function(id) {
  $.post('<?=base_url();?>ventas/Descuentos/get_descuento_ajax', {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      const d = result.descuento;
      $('#modalDescuentoTitle').text('Editar Descuento');
      $('#descuento_id').val(d.id);
      $('#descuento_nombre').val(d.nombre);
      $('#descuento_descripcion').val(d.descripcion);
      $('#descuento_tipo').val(d.tipo_descuento);
      $('#descuento_valor').val(d.valor);
      $('#descuento_estatus').val(d.estatus);
      cambiarTipoDescuento();
      $('#modalDescuento').modal('show');
    }
  });
};

function guardarDescuento() {
  const id = $('#descuento_id').val();
  const url = id ? '<?=base_url();?>ventas/Descuentos/editar_ajax' : '<?=base_url();?>ventas/Descuentos/crear_ajax';
  
  const data = {
    'id': id,
    'nombre': $('#descuento_nombre').val(),
    'descripcion': $('#descuento_descripcion').val(),
    'tipo_descuento': $('#descuento_tipo').val(),
    'valor': $('#descuento_valor').val(),
    'estatus': $('#descuento_estatus').val(),
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  };
  
  $.post(url, data, function(result) {
    result = JSON.parse(result);
    notifyShow(result.message, result.success ? 'success' : 'danger');
    if(result.success) {
      $('#modalDescuento').modal('hide');
      tabla.ajax.reload();
    }
  });
}

window.eliminarDescuento = function(id) {
  if(!confirm('¿Está seguro de eliminar este descuento?')) return;
  
  $.post('<?=base_url();?>ventas/Descuentos/eliminar_ajax', {
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

// Inicializar
if (typeof jQuery !== 'undefined') {
  $(document).ready(initDescuentos);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initDescuentos);
    }
  });
}
</script>
