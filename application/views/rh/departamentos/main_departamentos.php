<?php
/**
 * Vista principal de Departamentos
 * Listado con DataTables y modales para CRUD
 */
?>
<div class="container-fluid p-0">

  <!-- Breadcrumb (Migas de pan) -->
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
   
  <!-- Titulo de la pagina -->
  <h1 class="h3 mb-3"><?php echo $headTitle;?></h1>

  <!-- Cards de estadísticas -->
  <div class="row">
    <!-- Total Departamentos -->
    <div class="col-lg-4 col-xl-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Total Departamentos</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['total_departamentos']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-building text-primary" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Departamentos registrados</small>
        </div>
      </div>
    </div>

    <!-- Departamentos Activos -->
    <div class="col-lg-4 col-xl-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Activos</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['departamentos_activos']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-check-circle text-success" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Departamentos activos</small>
        </div>
      </div>
    </div>

    <!-- Departamentos Inactivos -->
    <div class="col-lg-4 col-xl-4 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Inactivos</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['departamentos_inactivos']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-times-circle text-danger" style="font-size: 1.5rem;"></i>
            </div>
          </div>
          <small class="text-muted">Departamentos inactivos</small>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Columna principal de datos -->
    <div class="col-xl-8">
      <div class="card">   
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-6 mb-2 mb-md-0">
              <div class="input-group input-group-search">
                <input type="text" class="form-control" id="datatables-search" placeholder="Buscar departamentos…">
                <button class="btn" type="button" id="btn-filter">
                  <i class="align-middle" data-lucide="search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6">
              <div class="text-sm-end">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalAgregar">
                  <i data-lucide="plus"></i> Nuevo Departamento
                </button>
              </div>
            </div>
          </div>

          <table id="datatables-departamentos" class="table w-100 table-hover table-striped">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Empleados</th>
                <th>Estatus</th>                
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>              
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Columna lateral derecha -->
    <div class="col-xl-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Detalle del Departamento</h5>
        </div>
        <div class="card-body">
          <div id="actions"></div>
          <br>          
          <table class="table table-sm my-2">
            <tbody id="detalles">
              <tr><td colspan="2" class="text-muted">Selecciona un departamento</td></tr>
            </tbody>
          </table>
        </div>
      </div>        
    </div>  
  </div>
</div>

<!-- Modal Agregar Departamento -->
<div class="modal fade" id="modalAgregar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i data-lucide="plus-circle"></i> Nuevo Departamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formAgregar">
          <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" class="form-control" name="nombre" id="add_nombre" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" id="add_descripcion" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="agregar_departamento()">Guardar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Editar Departamento -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i data-lucide="edit"></i> Editar Departamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formEditar">
          <input type="hidden" id="edit_id" name="id">
          <div class="mb-3">
            <label class="form-label">Nombre *</label>
            <input type="text" class="form-control" name="nombre" id="edit_nombre" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="descripcion" id="edit_descripcion" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Estatus</label>
            <select class="form-select" name="estatus" id="edit_estatus">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="actualizar_departamento()">Actualizar</button>
      </div>
    </div>
  </div>
</div>

<script>
var table;

document.addEventListener("DOMContentLoaded", function() {
  // DataTables      
  table = $('#datatables-departamentos').DataTable({
    responsive: true,
    "searching": false,
    "order": [],
    "processing": true,
    lengthMenu: [
      [10, 25, 50, -1],
      ['10 filas', '25 filas', '50 filas', 'Mostrar todo']
    ],
    language: {
      "sProcessing": "Procesando...",
      "lengthMenu": 'Mostrar _MENU_',
      "sZeroRecords": "No se encontraron resultados",
      "sEmptyTable": "Ningún dato disponible",
      "sInfo": "Registros del _START_ al _END_ de _TOTAL_",
      "sInfoFiltered": "(filtrado de _MAX_ registros)",            
    }, 
    "serverSide": true,
    "ajax": {
      "url": "<?php echo base_url('/rh/Departamentos/search_departamentos') ?>",
      "type": "POST",
      "data": function ( data ) {
        data.search = $('#datatables-search').val();
        data.<?php echo $this->security->get_csrf_token_name();?> = '<?php echo $this->security->get_csrf_hash();?>';
      }
    },
    "columnDefs": [{
      "targets": [ 0 ],
      "orderable": true,
    }],
  });

  // Búsqueda
  $('#btn-filter').click(function(){
    table.ajax.reload();
  });

  $('#datatables-search').on('keyup', function(e){
    if(e.which == 13){
      table.ajax.reload();
    }
  });
});

// Ver detalle
function departamento_detail(id){
  $.post('<?=base_url();?>/rh/Departamentos/detail',
  {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  },
  function(result){
    result = JSON.parse(result);
    if(result['response'] != null){
      $("#detalles").html(result['detail']);
      $("#actions").html(result['actions']);
      lucide.createIcons();
    } else {
      notifyShow("Error al obtener los datos", "danger");
    }
  });
}

// Agregar departamento
function agregar_departamento(){
  var nombre = $('#add_nombre').val();
  var descripcion = $('#add_descripcion').val();

  if(!nombre){
    notifyShow("El nombre es obligatorio", "warning");
    return;
  }

  $.post('<?=base_url();?>/rh/Departamentos/agregar',
  {
    'nombre': nombre,
    'descripcion': descripcion,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  },
  function(result){
    result = JSON.parse(result);
    if(result['success'] === true){
      notifyShow(result['message'], "success");
      $('#modalAgregar').modal('hide');
      $('#formAgregar')[0].reset();
      table.ajax.reload();
    } else {
      notifyShow(result['message'], "warning");
    }
  });
}

// Editar departamento
function editar_departamento(id){
  $.post('<?=base_url();?>/rh/Departamentos/detail',
  {
    'id': id,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  },
  function(result){
    result = JSON.parse(result);
    if(result['response'] != null){
      var dept = result['response'];
      $('#edit_id').val(dept.id);
      $('#edit_nombre').val(dept.nombre);
      $('#edit_descripcion').val(dept.descripcion);
      $('#edit_estatus').val(dept.estatus);
      $('#modalEditar').modal('show');
    } else {
      notifyShow("Error al obtener los datos", "danger");
    }
  });
}

// Actualizar departamento
function actualizar_departamento(){
  var id = $('#edit_id').val();
  var nombre = $('#edit_nombre').val();
  var descripcion = $('#edit_descripcion').val();
  var estatus = $('#edit_estatus').val();

  if(!nombre){
    notifyShow("El nombre es obligatorio", "warning");
    return;
  }

  $.post('<?=base_url();?>/rh/Departamentos/editar',
  {
    'id': id,
    'nombre': nombre,
    'descripcion': descripcion,
    'estatus': estatus,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  },
  function(result){
    result = JSON.parse(result);
    if(result['success'] === true){
      notifyShow(result['message'], "success");
      $('#modalEditar').modal('hide');
      table.ajax.reload();
      $("#detalles").html('<tr><td colspan="2" class="text-muted">Selecciona un departamento</td></tr>');
      $("#actions").html('');
    } else {
      notifyShow(result['message'], "warning");
    }
  });
}

// Eliminar departamento
function delete_departamento(id){
  if (confirm("¿Estás seguro de que quieres eliminar este departamento?")) {
    $.post('<?=base_url();?>/rh/Departamentos/eliminar',
    {
      'id': id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result){
      result = JSON.parse(result);
      if(result['success'] === true){
        notifyShow(result['message'], "success");
        table.ajax.reload();
        $("#detalles").html('<tr><td colspan="2" class="text-muted">Selecciona un departamento</td></tr>');
        $("#actions").html('');
      } else {
        notifyShow(result['message'], "warning");
      }
    });
  }    
} 
</script>
