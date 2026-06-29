<?php
/**
 * Vista principal de gestion de usuarios, incluye la tabla de usuarios, 
 * los botones de acciones, modal para alta y edicion de usuarios, 
 * y modal para confirmacion de eliminacion de usuarios
 */
?>
<div class="container-fluid p-0">


  <!-- Breadcrumb (Migas de pan) -->
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
   
  <!-- Titulo de la pagina -->
  <h1 class="h3 mb-3"><?php echo $headTitle;?></h1>

  <!-- Cards de estadísticas críticas -->
  <div class="row">
    <!-- Total de Usuarios -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Total Usuarios</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['total_users']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <span class="badge bg-primary"><?php echo $response['stats']['active_percentage']; ?>%</span>
            </div>
          </div>

          <div class="progress progress-sm shadow-sm mb-1">
            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $response['stats']['active_percentage']; ?>%"></div>
          </div>
          <small class="text-muted">Activos: <?php echo $response['stats']['active_users']; ?> | Inactivos: <?php echo $response['stats']['inactive_users']; ?></small>
        </div>
      </div>
    </div>

    <!-- Nuevos Registros (30 días) -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Nuevos (30d)</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['new_users_30days']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <span class="badge bg-success">+<?php echo $response['stats']['growth_percentage']; ?>%</span>
            </div>
          </div>

          <div class="progress progress-sm shadow-sm mb-1">
            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo min($response['stats']['growth_percentage'], 100); ?>%"></div>
          </div>
          <small class="text-muted">Crecimiento últimos 30 días</small>
        </div>
      </div>
    </div>

    <!-- Actividad del Sistema (7 días) -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Actividad (7d)</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo number_format($response['stats']['recent_activity']); ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <i class="fas fa-chart-line text-info" style="font-size: 1.5rem;"></i>
            </div>
          </div>

          <div class="progress progress-sm shadow-sm mb-1">
            <div class="progress-bar bg-info" role="progressbar" style="width: 75%"></div>
          </div>
          <small class="text-muted">Registros en bitácora</small>
        </div>
      </div>
    </div>

    <!-- Usuarios Dados de Baja (30 días) -->
    <div class="col-lg-6 col-xl-3 d-flex">
      <div class="card flex-fill">
        <div class="card-header">
          <h5 class="card-title mb-0 mt-2">Bajas (30d)</h5>
        </div>
        <div class="card-body my-0 pt-0">
          <div class="row d-flex align-items-center mb-3">
            <div class="col-8">
              <h3 class="d-flex align-items-center mb-0 fw-light">
                <?php echo $response['stats']['deleted_users_30days']; ?>
              </h3>
            </div>
            <div class="col-4 text-end">
              <?php if($response['stats']['deleted_users_30days'] > 0): ?>
                <span class="badge bg-danger">-<?php echo $response['stats']['deleted_users_30days']; ?></span>
              <?php else: ?>
                <span class="badge bg-success">0</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="progress progress-sm shadow-sm mb-1">
            <?php 
              $baja_percentage = $response['stats']['total_users'] > 0 
                ? min(($response['stats']['deleted_users_30days'] / $response['stats']['total_users']) * 100, 100) 
                : 0;
            ?>
            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $baja_percentage; ?>%"></div>
          </div>
          <small class="text-muted">Usuarios suspendidos</small>
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
                <input type="text" class="form-control" id="datatables-administradores-search" placeholder="Buscar administradores…">
                <button class="btn" type="button" id="btn-filter">
                  <i class="align-middle" data-lucide="search"></i>
                </button>
              </div>
            </div>
            <div class="col-md-6">
              <div class="text-sm-end">              
                <div class="dropdown position-relative d-inline-block">
                  <a href="#" data-bs-toggle="dropdown" data-bs-display="static"class="btn btn-light btn-lg me-2">
                    <i data-lucide="download"></i> Exportar
                  </a>
                  <div class="dropdown-menu dropdown-menu-end" id="table_menu_actions">                    
                  </div>
                </div>                
                <a href="<?php echo base_url('usuarios/Roles'); ?>" class="btn btn-outline-info btn-lg me-1"><i data-lucide="shield"></i> Gestionar Roles</a>
                <a href="<?php echo base_url('usuarios/GestionUsuarios/importar'); ?>" class="btn btn-outline-primary btn-lg me-1"><i data-lucide="upload"></i> Carga Masiva</a>
                <a href="<?php echo base_url('usuarios/GestionUsuarios/alta'); ?>" class="btn btn-primary btn-lg"><i data-lucide="plus"></i> Alta Administrador</a>                
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-md-3">
              <label for="filter-estatus" class="form-label">Estatus:</label>
              <select class="form-select" id="filter-estatus">
                <option value="all">Todos (activos y suspendidos)</option>
                <option value="1" selected>Activos</option>
                <option value="0">Suspendidos</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Mostrar filas:</label>
              <div id="datatables-length-container"></div>
            </div>
          </div>
          <table id="datatables-administradores" class="table w-100 table-hover table-striped">
            <thead>
              <tr>
                <th class="text-start">#</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Email</th>
                <th>Estatus</th>                
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>              
            </tbody>
            <tfoot>
              <tr>
                <th class="text-start">#</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Email</th>
                <th>Estatus</th>                
                <th>Acciones</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>


    <!-- columna lateral derecha -->
    <div class="col-xl-4">
        <!-- Administrator detail -->
        <div class="card">
					<div class="card-header">

						<h5 class="card-title mb-0">Datos del Administrador:</h5>
					</div>
					<div class="card-body">
            <div id="actions"></div>
            <br>          
						<table class="table table-sm my-2">
							<tbody id="detalles">
							</tbody>
						</table>
						<hr>
						<strong>Última actividad</strong>
						<ul class="timeline mt-2 mb-0" id="last_logs">
						</ul>
					</div>
				</div>        
    </div>  
    
  </div>
</div>




<!-- Scripts necesarios para exportar tabla excel, pdf -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>


<script>
var table;
var export_filename = 'administradores-<?php echo date("Y-m-d");?>';

document.addEventListener("DOMContentLoaded", function() {
      //datatables      
      table = $('#datatables-administradores').DataTable({
          responsive: true,
          dom: 'Bfrtip', // Removed 'l' to hide default length menu position
          "searching": false,
          "order": [], //Initial no order.
          "processing": true, //Feature control the processing indicator.
          lengthMenu: [
              [10, 50, 150, 500,-1],
              ['10 filas', '50 filas', '150 filas', '500 Filas', 'Mostrar todo']
          ],
  				buttons: [
                { extend: 'excelHtml5', text: 'Excel <i class="align-middle me-2 far fa-fw fa-file-excel"></i>', className: 'dropdown-item text-dark' },
                { extend: 'pdf', text: 'Pdf <i class="align-middle me-2 far fa-fw fa-file-pdf"></i>', className: 'dropdown-item text-dark', exportOptions: {
                  modifier: {
                    page: 'all'
                  }
                },
               },
                { extend: 'csv', text: 'Csv <i class="align-middle me-2 far fa-fw fa-file-csv"></i>', className: 'dropdown-item text-dark' },
                {extend: 'pdfHtml5', text: 'Pdf5 <i class="align-middle me-2 far fa-fw fa-file-pdf"></i>', className: 'dropdown-item text-dark' },
                { extend: 'print', text: 'Imprimir <i class="align-middle me-2 fas fa-fw fa-print"></i>', className: 'dropdown-item text-dark' },
                { extend: 'copy', text:' Copiar <i class="align-middle me-2 fas fa-fw fa-paperclip"></i>',  className: 'dropdown-item text-dark' }
          ],
          language: {
            "sProcessing": "Procesando...",
            "lengthMenu": 'Mostrar _MENU_',
            "sZeroRecords": "No se encontraron resultados",
            "sEmptyTable": "Ningún dato disponible en esta tabla",
            "sInfo": "Registros del _START_ al _END_ de _TOTAL_",
            "sInfoFiltered": "(filtrado de _MAX_ registros)",            
          }, 

          
          "serverSide": true, //Feature control DataTables' server-side processing mode.

          // Load data for the table's content from an Ajax source
          "ajax": {
              "url": "<?php echo base_url('/usuarios/GestionUsuarios/search_users') ?>",
              "type": "POST",
              "data": function ( data ) {
                  data.search = $('#datatables-administradores-search').val();
                  data.estatus = $('#filter-estatus').val();
                  data.<?php echo $this->security->get_csrf_token_name();?> = '<?php echo $this->security->get_csrf_hash();?>';
              }
          },
          //Set column definition initialisation properties.
          "columnDefs": [
          {
              "targets": [ 0 ], //first column / numbering column
              "orderable": true, //set not orderable
          },
          ],

      });
      
      table.buttons().container().appendTo("#table_menu_actions");

      // Move length menu to custom container
      var lengthMenu = $('<select name="datatables-administradores_length" class="form-select" id="datatables-length-select">' +
        '<option value="10">10 filas</option>' +
        '<option value="50">50 filas</option>' +
        '<option value="150">150 filas</option>' +
        '<option value="500">500 Filas</option>' +
        '<option value="-1">Mostrar todo</option>' +
        '</select>');
      
      $('#datatables-length-container').html(lengthMenu);
      
      // Handle length menu change
      $('#datatables-length-select').on('change', function() {
        var val = $(this).val();
        table.page.len(val).draw();
      });

      ///boton busqueda con filtro
      $('#btn-filter').click(function(){ //button filter event click
          table.ajax.reload();  //just reload table
      });

      ///detectamos si el cursor esta en el input de busqueda, al dar enter se ejecuta la busqueda 
      $('#datatables-administradores-search').on('keyup', function(e){
          if(e.which == 13){
              table.ajax.reload();  //just reload table
          }
      });

      ///evento para el filtro de estatus
      $('#filter-estatus').on('change', function(){
          table.ajax.reload();  //reload table when status filter changes
      });
      

      ///reiniciar busqueda
      /*
      $('#btn-reset').click(function(){ //button reset event click
       $('#form-filter')[0].reset();
       table.ajax.reload();  //just reload table  
      }); */

  });
</script>  

<script>
  function user_detail(id){
    $.post('<?=base_url();?>/usuarios/GestionUsuarios/detail',
    {
      'id':id,
      'peticion':'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>':'<?php echo $this->security->get_csrf_hash();?>'
    },
    function(result){
      result = JSON.parse(result);
      if(result['response']!=null){
        $("#detalles").html(result['detail']);
        $("#actions").html(result['actions']);
        $("#last_logs").html(result['last_logs']);
      }else {
        notifyShow("Error al obtener los datos","danger");
      }

    });
  }

  function delete_user(id){
    if (confirm("¿Estás seguro de que quieres eliminar a este usuario?")) {
      $.post('<?=base_url();?>/usuarios/GestionUsuarios/eliminar',
        {
          'id': id,
          'peticion': 'ajax',
          '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
        },
        function(result) {
          result = JSON.parse(result);
          if (result['success'] === true ) {
            notifyShow(result['message'], "success");
            table.ajax.reload(null, false); // Reload datatable and keep current page
            $("#detalles").html(''); // Clear user details
            $("#actions").html(''); // Clear actions
            $("#last_logs").html(''); // Clear logs
            //recargar la tabla
            table.ajax.reload();
          } else {
            notifyShow(result['message'], "warning");
          }
        }
      );
    }    
  } 

</script>


