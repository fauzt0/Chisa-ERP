<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <div class="row mb-2 mb-xl-3">
    <div class="col-auto d-none d-sm-block">
      <h3><i class="fas fa-history"></i> <?= $headTitle ?></h3>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0"><i class="fas fa-users"></i> Administradores del Sistema</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover" id="tabla-bitacora">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Nombre</th>
                  <th>Email</th>
                  <th>Estatus</th>
                  <th>Fecha Registro</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($response['usuarios'] as $usuario): ?>
                <tr>
                  <td><?= $usuario->id ?></td>
                  <td><strong><?= $usuario->nombre ?> <?= $usuario->apellidos ?></strong></td>
                  <td><?= $usuario->username ?></td>
                  <td>
                    <?php if($usuario->estatus == 1): ?>
                      <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Suspendido</span>
                    <?php endif; ?>
                  </td>
                  <td><?= date('d/m/Y', strtotime($usuario->fecha_alta)) ?></td>
                  <td>
                    <button class="btn btn-sm btn-primary" onclick="verBitacora(<?= $usuario->id ?>, '<?= addslashes($usuario->nombre . ' ' . $usuario->apellidos) ?>')">
                      <i class="fas fa-eye"></i> Ver Bitácora
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Bitácora de Usuario -->
<div class="modal fade" id="modalBitacora" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-white"><i class="fas fa-history"></i> Bitácora de <span id="usuario-nombre" class="text-white"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="bitacora-content">
          <div class="text-center">
            <div class="spinner-border" role="status"></div>
            <p>Cargando...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
// Función de inicialización con corrección de jQuery
function initBitacora() {
  // Inicializar DataTable
  if($('#tabla-bitacora').length) {
    $('#tabla-bitacora').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
      },
      order: [[1, 'asc']], // Ordenar por nombre
      pageLength: 25
    });
  }
}

// Esperar a que jQuery esté disponible (corrección de error)
if (typeof jQuery !== 'undefined') {
  $(document).ready(initBitacora);
} else {
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
      $(document).ready(initBitacora);
    }
  });
}

// Ver bitácora de usuario
function verBitacora(userId, userName) {
  $('#usuario-nombre').text(userName);
  $('#modalBitacora').modal('show');
  
  // Cargar logs del usuario
  $.post('<?= base_url('usuarios/GestionUsuarios/get_user_logs_ajax') ?>', {
    user_id: userId,
    limit: 100,
    '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
  }, function(result) {
    result = JSON.parse(result);
    if(result.success) {
      mostrarLogs(result.logs);
    } else {
      $('#bitacora-content').html('<div class="alert alert-danger">' + result.message + '</div>');
    }
  });
}

function mostrarLogs(logs) {
  if(logs.length == 0) {
    $('#bitacora-content').html('<div class="alert alert-info">No hay registros de actividad para este usuario.</div>');
    return;
  }
  
  var html = '<div class="table-responsive">' +
    '<table class="table table-sm table-striped">' +
    '<thead>' +
    '<tr>' +
    '<th>Fecha</th>' +
    '<th>Tipo</th>' +
    '<th>Mensaje</th>' +
    '</tr>' +
    '</thead>' +
    '<tbody>';
  
  logs.forEach(function(log) {
    var badgeClass = 'bg-secondary';
    if(log.tipo && log.tipo.toLowerCase().includes('error')) {
      badgeClass = 'bg-danger';
    } else if(log.tipo && log.tipo.toLowerCase().includes('success')) {
      badgeClass = 'bg-success';
    } else if(log.tipo && log.tipo.toLowerCase().includes('warning')) {
      badgeClass = 'bg-warning';
    } else if(log.tipo && log.tipo.toLowerCase().includes('info')) {
      badgeClass = 'bg-info';
    }
    
    html += '<tr>' +
      '<td><small>' + log.fecha + '</small></td>' +
      '<td><span class="badge ' + badgeClass + '">' + (log.tipo || 'Actividad') + '</span></td>' +
      '<td>' + log.mensaje + '</td>' +
      '</tr>';
  });
  
  html += '</tbody></table></div>';
  
  $('#bitacora-content').html(html);
}
</script>
