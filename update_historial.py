import re

with open("application/views/produccion/productos/main.php", "r") as f:
    content = f.read()

# Update the HTML
old_buscador = """        <!-- Buscador -->
        <div class="mb-3">
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="busquedaHistorial" placeholder="Buscar por versión o descripción (ej: Plaza Comercial, V1.2, etc.)">
          </div>
          <small class="text-muted">La búsqueda se realiza en tiempo real</small>
        </div>"""

new_buscador = """        <!-- Filtros de Historial -->
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
        </div>"""

content = content.replace(old_buscador, new_buscador)

# Update the JS listeners
old_listeners = """  // Configurar búsqueda en tiempo real
  $('#busquedaHistorial').off('input').on('input', function() {
    const busqueda = $(this).val();
    cargarHistorialFormulaciones(historialProductoId, busqueda);
  });"""

new_listeners = """  // Configurar búsqueda reactiva
  $('#busquedaHistorial').off('input').on('input', reloadHistorial);
  $('#historial_cliente_id, #historial_fecha_inicio, #historial_fecha_fin').off('change').on('change', reloadHistorial);
  
  function reloadHistorial() {
    cargarHistorialFormulaciones(historialProductoId);
  }
  
  // Opcional: cargar lista de clientes
  $.post('<?=base_url();?>ventas/Clientes/get_clientes_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
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
  });"""

content = content.replace(old_listeners, new_listeners)

# Update the JS fetch function
old_fetch = """function cargarHistorialFormulaciones(productoId, busqueda) {
  $('#listaHistorialFormulaciones').html(`
    <div class="text-center text-muted py-5">
      <i class="fas fa-spinner fa-spin fa-3x"></i>
      <p class="mt-3">Buscando formulaciones...</p>
    </div>
  `);
  
  $.post('<?=base_url();?>produccion/Productos/get_historial_formulaciones_ajax', {
    'producto_id': productoId,
    'busqueda': busqueda,"""

new_fetch = """function cargarHistorialFormulaciones(productoId) {
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
  
  $.post('<?=base_url();?>produccion/Productos/get_historial_formulaciones_ajax', {
    'producto_id': productoId,
    'busqueda': busqueda,
    'cliente_id': cliente_id,
    'fecha_inicio': fecha_inicio,
    'fecha_fin': fecha_fin,"""

content = content.replace(old_fetch, new_fetch)


# Render info de clientes en el HTML del historial
old_historial_info = """${f.descripcion ? `<p class="mb-2"><strong>Descripción:</strong><br>${f.descripcion}</p>` : '<p class="text-muted mb-2">Sin descripción</p>'}
                        <p class="mb-2"><strong><i class="fas fa-box"></i> Cantidad producida:</strong> ${f.cantidad_producida} ${f.unidad_produccion}</p>"""

new_historial_info = """${f.cliente_nombre ? `<p class="mb-2"><strong><i class="fas fa-user"></i> Cliente:</strong> ${f.cliente_nombre}</p>` : ''}
                        ${f.comentarios ? `<p class="mb-2"><strong><i class="fas fa-comment"></i> Comentarios:</strong> ${f.comentarios}</p>` : ''}
                        ${f.descripcion ? `<p class="mb-2"><strong>Descripción:</strong><br>${f.descripcion}</p>` : '<p class="text-muted mb-2">Sin descripción</p>'}
                        <p class="mb-2"><strong><i class="fas fa-box"></i> Cantidad producida:</strong> ${f.cantidad_producida} ${f.unidad_produccion}</p>"""

content = content.replace(old_historial_info, new_historial_info)

with open("application/views/produccion/productos/main.php", "w") as f:
    f.write(content)

