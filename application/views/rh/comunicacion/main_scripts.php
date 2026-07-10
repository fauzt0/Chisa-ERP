<?php defined('BASEPATH') OR exit('No direct script access allowed');
$empleado = $response['empleado'] ?? null;
$tablas_listas = !empty($response['tablas_listas']);
?>
<script>
var bandejaMensajes = 'recibidos';
var vistaTareas = 'asignadas';
var csrfN = '<?= $this->security->get_csrf_token_name() ?>';
var csrfV = '<?= $this->security->get_csrf_hash() ?>';
var miEmpleadoId = <?= (int)($empleado->id ?? 0) ?>;
var tablasListas = <?= $tablas_listas ? 'true' : 'false' ?>;

function escHtml(s) {
  if (s == null || s === '') return '';
  var d = document.createElement('div');
  d.textContent = String(s);
  return d.innerHTML;
}

function parseJsonResponse(r) {
  if (typeof r === 'string') {
    try { return JSON.parse(r); } catch (e) { return { success: false, message: 'Respuesta inválida del servidor.' }; }
  }
  return r;
}

function ajaxPost(url, data, onSuccess) {
  var payload = $.extend({ peticion: 'ajax' }, data || {});
  if (csrfN && csrfV) payload[csrfN] = csrfV;
  return $.post(url, payload)
    .done(function(r) { onSuccess(parseJsonResponse(r)); })
    .fail(function() {
      if (typeof notifyShow === 'function') notifyShow('Error de conexión. Intenta de nuevo.', 'danger');
    });
}

function cerrarModal(id) {
  var el = document.getElementById(id);
  if (!el || typeof bootstrap === 'undefined') return;
  var inst = bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
  inst.hide();
}

function actualizarResumen() {
  ajaxPost('<?= base_url('rh/Comunicacion/resumen_ajax') ?>', {}, function(r) {
    if (r.success && r.resumen) {
      $('#statMensajesNoLeidos').text(r.resumen.mensajes_no_leidos || 0);
      $('#statTareasPendientes').text(r.resumen.tareas_pendientes || 0);
      $('#statTareasProceso').text(r.resumen.tareas_en_proceso || 0);
    }
    if (typeof loadComunicacionNavbar === 'function') loadComunicacionNavbar();
  });
}

function cambiarBandejaMensajes(b) {
  bandejaMensajes = b;
  $('#btnBandejaRecibidos').toggleClass('active', b === 'recibidos');
  $('#btnBandejaEnviados').toggleClass('active', b === 'enviados');
  cargarMensajes();
}

function cargarMensajes() {
  if (!tablasListas) {
    $('#listaMensajes').html('<div class="list-group-item text-center text-muted py-4">Módulo no instalado en base de datos.</div>');
    return;
  }
  $('#listaMensajes').html('<div class="list-group-item text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
  ajaxPost('<?= base_url('rh/Comunicacion/listar_mensajes') ?>', { bandeja: bandejaMensajes }, function(r) {
    if (!r.success) {
      $('#listaMensajes').html('<div class="list-group-item text-danger">' + escHtml(r.message || 'Error al cargar mensajes') + '</div>');
      return;
    }
    if (!r.mensajes || !r.mensajes.length) {
      $('#listaMensajes').html('<div class="list-group-item text-center text-muted py-4">Sin mensajes en esta bandeja.</div>');
      return;
    }
    var html = '';
    r.mensajes.forEach(function(m) {
      var otro = bandejaMensajes === 'recibidos' ? m.de_nombre : m.para_nombre;
      var fecha = m.fecha_envio ? new Date(m.fecha_envio.replace(' ', 'T')).toLocaleString('es-MX') : '';
      var noLeido = bandejaMensajes === 'recibidos' && parseInt(m.leido, 10) === 0;
      html += '<div class="list-group-item' + (noLeido ? ' bg-light' : '') + '">' +
        '<div class="d-flex justify-content-between align-items-start gap-2">' +
          '<div class="flex-grow-1">' +
            '<div class="fw-semibold">' + escHtml(otro) + (noLeido ? ' <span class="badge bg-primary">Nuevo</span>' : '') + '</div>' +
            '<div class="small text-muted mb-1">' + fecha + '</div>' +
            '<div style="white-space:pre-wrap;">' + escHtml(m.mensaje) + '</div>' +
          '</div>';
      if (noLeido) {
        html += '<button type="button" class="btn btn-sm btn-outline-primary" onclick="marcarLeido(' + parseInt(m.id, 10) + ')"><i class="fas fa-check"></i></button>';
      }
      html += '</div></div>';
    });
    $('#listaMensajes').html(html);
  });
}

function marcarLeido(id) {
  ajaxPost('<?= base_url('rh/Comunicacion/marcar_leido') ?>', { mensaje_id: id }, function() {
    cargarMensajes();
    actualizarResumen();
  });
}

function enviarMensaje() {
  var $form = $('#formNuevoMensaje');
  if (!$form[0].checkValidity()) {
    $form[0].reportValidity();
    return;
  }
  ajaxPost('<?= base_url('rh/Comunicacion/enviar_mensaje') ?>', $form.serialize(), function(r) {
    if (typeof notifyShow === 'function') notifyShow(r.message || '', r.success ? 'success' : 'danger');
    if (r.success) {
      cerrarModal('modalNuevoMensaje');
      $form[0].reset();
      cambiarBandejaMensajes('enviados');
      actualizarResumen();
    }
  });
}

function cambiarVistaTareas(v) {
  vistaTareas = v;
  $('#btnTareasAsignadas').toggleClass('active', v === 'asignadas');
  $('#btnTareasEnviadas').toggleClass('active', v === 'enviadas');
  cargarTareas();
}

function badgeEstatusTarea(e) {
  var map = { 'Pendiente': 'warning', 'En proceso': 'info', 'Hecha': 'success', 'Cancelada': 'secondary' };
  return '<span class="badge bg-' + (map[e] || 'secondary') + '">' + escHtml(e) + '</span>';
}

function cargarTareas() {
  if (!tablasListas) {
    $('#listaTareas').html('<tr><td colspan="5" class="text-center text-muted py-4">Módulo no instalado en base de datos.</td></tr>');
    return;
  }
  $('#listaTareas').html('<tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
  ajaxPost('<?= base_url('rh/Comunicacion/listar_tareas') ?>', { vista: vistaTareas }, function(r) {
    if (!r.success) {
      $('#listaTareas').html('<tr><td colspan="5" class="text-danger">' + escHtml(r.message || 'Error al cargar tareas') + '</td></tr>');
      return;
    }
    if (!r.tareas || !r.tareas.length) {
      $('#listaTareas').html('<tr><td colspan="5" class="text-center text-muted py-4">Sin tareas en esta vista.</td></tr>');
      return;
    }
    var html = '';
    r.tareas.forEach(function(t) {
      var otro = vistaTareas === 'asignadas' ? t.de_nombre : t.para_nombre;
      var limite = t.fecha_limite ? t.fecha_limite.split('-').reverse().join('/') : '—';
      var puedeCambiar = (vistaTareas === 'asignadas' && parseInt(t.para_empleado_id, 10) === miEmpleadoId)
        || (vistaTareas === 'enviadas' && parseInt(t.de_empleado_id, 10) === miEmpleadoId);
      html += '<tr>' +
        '<td><strong>' + escHtml(t.titulo) + '</strong>' +
          (t.descripcion ? '<div class="small text-muted" style="white-space:pre-wrap;">' + escHtml(t.descripcion) + '</div>' : '') + '</td>' +
        '<td class="small">' + escHtml(otro) + '</td>' +
        '<td>' + badgeEstatusTarea(t.estatus) + '</td>' +
        '<td class="small">' + limite + '</td>' +
        '<td>';
      if (puedeCambiar && t.estatus !== 'Hecha' && t.estatus !== 'Cancelada') {
        html += '<select class="form-select form-select-sm" onchange="actualizarTarea(' + parseInt(t.id, 10) + ', this.value)">' +
          '<option value="">Cambiar...</option>' +
          '<option value="Pendiente"' + (t.estatus === 'Pendiente' ? ' selected' : '') + '>Pendiente</option>' +
          '<option value="En proceso"' + (t.estatus === 'En proceso' ? ' selected' : '') + '>En proceso</option>' +
          '<option value="Hecha">Hecha</option>' +
          '<option value="Cancelada">Cancelada</option>' +
          '</select>';
      } else {
        html += '<span class="text-muted small">—</span>';
      }
      html += '</td></tr>';
    });
    $('#listaTareas').html(html);
  });
}

function actualizarTarea(id, estatus) {
  if (!estatus) return;
  ajaxPost('<?= base_url('rh/Comunicacion/actualizar_tarea') ?>', { tarea_id: id, estatus: estatus }, function(r) {
    if (typeof notifyShow === 'function') notifyShow(r.message || '', r.success ? 'success' : 'danger');
    if (r.success) {
      cargarTareas();
      actualizarResumen();
    }
  });
}

function crearTarea() {
  var $form = $('#formNuevaTarea');
  if (!$form[0].checkValidity()) {
    $form[0].reportValidity();
    return;
  }
  ajaxPost('<?= base_url('rh/Comunicacion/crear_tarea') ?>', $form.serialize(), function(r) {
    if (typeof notifyShow === 'function') notifyShow(r.message || '', r.success ? 'success' : 'danger');
    if (r.success) {
      cerrarModal('modalNuevaTarea');
      $form[0].reset();
      cambiarVistaTareas('enviadas');
      actualizarResumen();
    }
  });
}

$(function() {
  cargarMensajes();
  cargarTareas();
});
</script>
