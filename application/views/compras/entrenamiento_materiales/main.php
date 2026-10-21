<?php
$busqueda          = $response['busqueda']            ?? '';
$filas             = $response['filas']               ?? [];
$total             = (int) ($response['total']        ?? 0);
$proveedores_act   = $response['proveedores_activos'] ?? [];
$csrf_name         = $this->security->get_csrf_token_name();
$csrf_hash         = $this->security->get_csrf_hash();
?>
<div class="container-fluid p-0 compras-page">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

  <h1 class="h3 mb-2"><?= htmlspecialchars($headTitle, ENT_QUOTES, 'UTF-8') ?></h1>
  <p class="text-muted mb-3 small">
    Consulta y edición ligera: estatus, alias, precio de proveedor y datos de producto.
    El tipo de insumo (comprado/fabricado/semielaborado) y el stock solo se modifican desde Insumos.
  </p>

  <div class="alert alert-warning py-2 small mb-3">
    <strong>Precio de venta del producto:</strong> es el precio de lista al cliente; NO es costo de compra del insumo.
  </div>

  <!-- Búsqueda -->
  <form class="card mb-3" method="get" action="<?= base_url('compras/EntrenamientoMateriales') ?>">
    <div class="card-body row g-2 align-items-end">
      <div class="col-md-8">
        <label class="form-label" for="busqueda">Buscar por nombre técnico, alias o código</label>
        <input type="search" class="form-control" id="busqueda" name="busqueda"
               value="<?= htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8') ?>"
               placeholder="BERMOCOLL, Agua desmineralizada, INS-001…">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Buscar</button>
      </div>
      <div class="col-md-2">
        <a class="btn btn-outline-secondary w-100" href="<?= base_url('compras/EntrenamientoMateriales') ?>">Ver todos</a>
      </div>
    </div>
  </form>

  <!-- Tabla -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Materiales</span>
      <span class="text-muted small"><?= $total ?> resultado(s)</span>
    </div>
    <div class="table-responsive">
      <table class="table table-striped table-sm mb-0" id="tablaEM">
        <thead class="table-light">
          <tr>
            <th>Código</th>
            <th>Nombre técnico</th>
            <th>Alias interno</th>
            <th>Aliases catálogo</th>
            <th>Estatus</th>
            <th>Categoría</th>
            <th>Tipo</th>
            <th>Clase uso</th>
            <th>Proveedores (precio compra)</th>
            <th>Último precio hist.</th>
            <th>Producto enlazado</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($filas)): ?>
          <tr><td colspan="12" class="text-muted py-3">Sin resultados.</td></tr>
        <?php else: ?>
          <?php foreach ($filas as $f):
            // JSON compacto para el modal; se escapa en el atributo HTML
            $json_fila = htmlspecialchars(json_encode([
              'id'             => $f['id'],
              'nombre_tecnico' => $f['nombre_tecnico'],
              'codigo'         => $f['codigo'],
              'estatus'        => $f['estatus'],
              'tipo'           => $f['tipo'],
              'clase_uso'      => $f['clase_uso'],
              'alias_interno'  => $f['alias_interno'],
              'producto_id'    => $f['producto_id'],
              'aliases'        => $f['aliases'],
              'proveedores_raw'=> $f['proveedores_raw'],
              'producto'       => $f['producto'],
            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
          ?>
          <tr>
            <td><?= htmlspecialchars($f['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($f['nombre_tecnico'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= $f['alias_interno'] !== '' ? htmlspecialchars($f['alias_interno'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
            <td class="small">
              <?php if (!empty($f['aliases'])):
                $txt = array_map(function($a) { return htmlspecialchars($a->alias, ENT_QUOTES, 'UTF-8') . ' <em class="text-muted">('.$a->tipo.')</em>'; }, $f['aliases']);
                echo implode('<br>', $txt);
              else: echo '—'; endif; ?>
            </td>
            <td>
              <?php $ecls = $f['estatus'] === 'Activo' ? 'success' : ($f['estatus'] === 'Descontinuado' ? 'secondary' : 'warning'); ?>
              <span class="badge bg-<?= $ecls ?>"><?= htmlspecialchars($f['estatus'], ENT_QUOTES, 'UTF-8') ?></span>
            </td>
            <td><?= $f['categoria'] !== '' ? htmlspecialchars($f['categoria'], ENT_QUOTES, 'UTF-8') : '—' ?></td>
            <td><?= htmlspecialchars($f['tipo'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <?php $cu = $f['clase_uso']; ?>
              <?= $cu !== '' ? '<span class="badge bg-info text-dark">'.htmlspecialchars($cu, ENT_QUOTES, 'UTF-8').'</span>' : '—' ?>
            </td>
            <td class="small">
              <?php if (!empty($f['proveedores'])): ?>
                <ul class="list-unstyled mb-0"><?php foreach ($f['proveedores'] as $pt): ?><li><?= $pt ?></li><?php endforeach; ?></ul>
              <?php else: ?><span class="text-muted">Sin proveedores</span><?php endif; ?>
            </td>
            <td class="small">
              <?php if (!empty($f['historial_precio'])):
                $h = $f['historial_precio']; ?>
                $<?= number_format((float) $h->precio, 2) ?>
                <?php if (!empty($h->fecha)): ?><br><small class="text-muted"><?= htmlspecialchars($h->fecha, ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
              <?php else: ?><span class="text-muted">sin historial</span><?php endif; ?>
            </td>
            <td class="small">
              <?php if (!empty($f['producto'])):
                $p = $f['producto']; ?>
                <strong>UV:</strong> <?= htmlspecialchars((string)($p->unidad_venta ?? '—'), ENT_QUOTES, 'UTF-8') ?><br>
                <strong>Pres.:</strong> <?= htmlspecialchars((string)($p->presentacion_principal ?? '—'), ENT_QUOTES, 'UTF-8') ?><br>
                <strong>Cont.:</strong> <?= $p->contenido_neto !== null ? htmlspecialchars((string)$p->contenido_neto, ENT_QUOTES, 'UTF-8') : '—' ?><br>
                <strong>Rend.:</strong> <?= $p->rendimiento !== null && $p->rendimiento !== '' ? htmlspecialchars((string)$p->rendimiento, ENT_QUOTES, 'UTF-8') : '—' ?><br>
                <strong class="text-muted">PV (lista):</strong> $<?= number_format((float)($p->precio_venta ?? 0), 2) ?>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <button type="button" class="btn btn-sm btn-outline-primary btn-editar"
                      data-fila="<?= $json_fila ?>"
                      title="Editar">
                <i class="fas fa-pencil-alt"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ===== MODAL EDICIÓN ===== -->
<div class="modal fade" id="modalEM" tabindex="-1" aria-labelledby="modalEMLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEMLabel">Editar material</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- SECCIÓN 1: Datos generales -->
        <h6 class="fw-bold border-bottom pb-1 mb-3"><i class="fas fa-sliders-h me-1"></i> Datos generales</h6>
        <div class="row g-2 mb-2">
          <div class="col-md-4">
            <label class="form-label">Código</label>
            <input type="text" class="form-control form-control-sm" id="em_codigo" readonly>
          </div>
          <div class="col-md-8">
            <label class="form-label">Nombre técnico (referencia, no editable aquí)</label>
            <input type="text" class="form-control form-control-sm" id="em_nombre" readonly>
          </div>
        </div>
        <div class="row g-2 mb-2">
          <div class="col-md-4">
            <label class="form-label">Estatus</label>
            <select class="form-select form-select-sm" id="em_estatus">
              <option value="Activo">Activo</option>
              <option value="Inactivo">Inactivo</option>
            </select>
            <div class="form-text text-warning" id="em_estatus_aviso" style="display:none">
              Descontinuado: no se puede cambiar desde aquí.
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Clase de uso</label>
            <select class="form-select form-select-sm" id="em_clase_uso">
              <option value="">— Sin clasificar —</option>
              <option value="materia_prima">Materia prima</option>
              <option value="insumo">Insumo</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipo (solo lectura)</label>
            <input type="text" class="form-control form-control-sm" id="em_tipo" readonly>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Alias interno (insumos.alias)</label>
          <input type="text" class="form-control form-control-sm" id="em_alias_interno"
                 placeholder="Nombre interno de referencia rápida">
        </div>
        <button type="button" class="btn btn-primary btn-sm mb-4" id="btn_guardar_insumo">
          <i class="fas fa-save me-1"></i> Guardar datos generales
        </button>

        <!-- SECCIÓN 2: Aliases adicionales -->
        <h6 class="fw-bold border-bottom pb-1 mb-3"><i class="fas fa-tags me-1"></i> Aliases adicionales</h6>
        <div id="em_lista_aliases" class="mb-2 small text-muted">Cargando…</div>
        <div class="row g-2 align-items-end mb-4">
          <div class="col-md-4">
            <label class="form-label">Tipo de alias</label>
            <select class="form-select form-select-sm" id="em_alias_tipo">
              <option value="Genérico">Genérico</option>
              <option value="Proveedor">Proveedor (requiere proveedor)</option>
              <option value="Comercial">Comercial</option>
              <option value="Técnico">Técnico</option>
            </select>
          </div>
          <div class="col-md-4" id="em_alias_proveedor_wrap" style="display:none">
            <label class="form-label">Proveedor</label>
            <select class="form-select form-select-sm" id="em_alias_proveedor_id">
              <option value="">— Seleccionar —</option>
              <?php foreach ($proveedores_act as $pv): ?>
              <option value="<?= (int)$pv->id ?>"><?= htmlspecialchars($pv->razon_social, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Alias</label>
            <input type="text" class="form-control form-control-sm" id="em_alias_nuevo" placeholder="Nombre del alias">
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-success btn-sm w-100" id="btn_agregar_alias">
              <i class="fas fa-plus"></i> Agregar
            </button>
          </div>
        </div>

        <!-- SECCIÓN 3: Proveedor / precio de compra -->
        <h6 class="fw-bold border-bottom pb-1 mb-3"><i class="fas fa-truck me-1"></i> Precio de compra por proveedor</h6>
        <div id="em_lista_proveedores" class="mb-3 small">Cargando…</div>
        <div class="row g-2 align-items-end mb-4">
          <div class="col-md-5">
            <label class="form-label">Proveedor</label>
            <select class="form-select form-select-sm" id="em_prov_id">
              <option value="">— Seleccionar proveedor —</option>
              <?php foreach ($proveedores_act as $pv): ?>
              <option value="<?= (int)$pv->id ?>"><?= htmlspecialchars($pv->razon_social, ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Precio compra ($)</label>
            <input type="number" min="0" step="0.01" class="form-control form-control-sm" id="em_prov_precio" placeholder="0.00">
          </div>
          <div class="col-md-2">
            <button type="button" class="btn btn-success btn-sm w-100" id="btn_guardar_proveedor">
              <i class="fas fa-save"></i> Guardar
            </button>
          </div>
        </div>

        <!-- SECCIÓN 4: Producto enlazado (solo si producto_id no es null) -->
        <div id="em_seccion_producto" style="display:none">
          <h6 class="fw-bold border-bottom pb-1 mb-3">
            <i class="fas fa-box me-1"></i> Producto terminado enlazado
            <small class="text-muted fw-normal ms-2">(Precio de venta = lista al cliente, no es costo de compra)</small>
          </h6>
          <div class="row g-2 mb-2">
            <div class="col-md-4">
              <label class="form-label">Unidad de venta</label>
              <select class="form-select form-select-sm" id="em_prod_uv">
                <option value="Cubeta">Cubeta</option>
                <option value="Litro">Litro</option>
                <option value="Galon">Galón</option>
                <option value="Kg">Kg</option>
                <option value="Pieza">Pieza</option>
                <option value="Caja">Caja</option>
                <option value="Metro">Metro</option>
                <option value="M2">M2</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Presentación principal</label>
              <input type="text" class="form-control form-control-sm" id="em_prod_pres" placeholder="Ej: 19L">
            </div>
            <div class="col-md-4">
              <label class="form-label">Contenido neto (kg)</label>
              <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="em_prod_cn">
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label class="form-label">Rendimiento</label>
              <input type="text" class="form-control form-control-sm" id="em_prod_rend" placeholder="Ej: 8-12 m2/L">
            </div>
            <div class="col-md-6">
              <label class="form-label">Precio de venta — lista (no es costo compra)</label>
              <div class="input-group input-group-sm">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" min="0" class="form-control" id="em_prod_pv">
              </div>
            </div>
          </div>
          <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_producto">
            <i class="fas fa-save me-1"></i> Guardar producto
          </button>
        </div>

      </div><!-- /.modal-body -->
    </div>
  </div>
</div>

<script>
(function () {
  'use strict';

  var CSRF_NAME = '<?= $csrf_name ?>';
  var CSRF_HASH = '<?= $csrf_hash ?>';
  var BASE      = '<?= base_url() ?>';
  var filaActual = null; // objeto JSON de la fila en edición

  /* ---- util ---- */
  function postJSON(url, data, cb) {
    data[CSRF_NAME] = CSRF_HASH;
    $.post(url, data, function (res) {
      // Renovar CSRF tras cada POST
      if (res && res.csrf_hash) CSRF_HASH = res.csrf_hash;
      cb(res);
    }, 'json').fail(function () {
      cb({success: false, message: 'Error de comunicación'});
    });
  }

  function alerta(msg, tipo) {
    tipo = tipo || 'danger';
    $('#em_alerta').remove();
    var el = $('<div class="alert alert-' + tipo + ' alert-dismissible fade show py-2 small" id="em_alerta" role="alert">' +
      msg + '<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button></div>');
    $('#modalEM .modal-body').prepend(el);
  }

  /* ---- renderiza lista de aliases ---- */
  function renderAliases(aliases) {
    var $c = $('#em_lista_aliases');
    if (!aliases || aliases.length === 0) {
      $c.html('<span class="text-muted">Sin aliases adicionales.</span>');
      return;
    }
    var html = '<ul class="list-group list-group-flush">';
    aliases.forEach(function(a) {
      html += '<li class="list-group-item py-1 px-0">';
      html += '<span class="badge bg-secondary me-1">' + escH(a.tipo) + '</span>';
      html += escH(a.alias);
      if (a.proveedor_id) html += ' <small class="text-muted">(proveedor #' + a.proveedor_id + ')</small>';
      html += '</li>';
    });
    html += '</ul>';
    $c.html(html);
  }

  /* ---- renderiza lista de proveedores con inputs de precio ---- */
  function renderProveedores(provs) {
    var $c = $('#em_lista_proveedores');
    if (!provs || provs.length === 0) {
      $c.html('<span class="text-muted">Sin proveedores activos. Use el formulario de abajo para agregar.</span>');
      return;
    }
    var html = '<table class="table table-sm table-bordered">';
    html += '<thead><tr><th>Proveedor</th><th style="width:150px">Precio compra</th><th></th></tr></thead><tbody>';
    provs.forEach(function(p) {
      var nom = p.razon_social || p.nombre_comercial || p.prov_codigo || 'Proveedor #' + p.proveedor_id;
      html += '<tr>';
      html += '<td>' + escH(nom) + '</td>';
      html += '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm em-prov-precio" '
              + 'value="' + parseFloat(p.precio_compra || 0).toFixed(2) + '" '
              + 'data-prov-id="' + parseInt(p.proveedor_id) + '"></td>';
      html += '<td><button type="button" class="btn btn-sm btn-outline-success em-btn-prov-guardar" '
              + 'data-prov-id="' + parseInt(p.proveedor_id) + '">'
              + '<i class="fas fa-save"></i></button></td>';
      html += '</tr>';
    });
    html += '</tbody></table>';
    $c.html(html);
  }

  function escH(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  /* ---- abrir modal ---- */
  $(document).on('click', '.btn-editar', function () {
    filaActual = JSON.parse($(this).attr('data-fila'));
    var f = filaActual;

    $('#em_codigo').val(f.codigo);
    $('#em_nombre').val(f.nombre_tecnico);
    $('#em_tipo').val(f.tipo);
    $('#em_alias_interno').val(f.alias_interno);
    $('#em_clase_uso').val(f.clase_uso || '');

    // Estatus
    if (f.estatus === 'Descontinuado') {
      $('#em_estatus').val('Activo').prop('disabled', true);
      $('#em_estatus_aviso').show();
    } else {
      $('#em_estatus').val(f.estatus).prop('disabled', false);
      $('#em_estatus_aviso').hide();
    }

    renderAliases(f.aliases);
    renderProveedores(f.proveedores_raw);

    // Sección producto
    if (f.producto_id && f.producto) {
      var pr = f.producto;
      $('#em_prod_uv').val(pr.unidad_venta || 'Cubeta');
      $('#em_prod_pres').val(pr.presentacion_principal || '');
      $('#em_prod_cn').val(pr.contenido_neto !== null ? pr.contenido_neto : '');
      $('#em_prod_rend').val(pr.rendimiento || '');
      $('#em_prod_pv').val(pr.precio_venta !== null ? parseFloat(pr.precio_venta).toFixed(2) : '');
      $('#em_seccion_producto').show();
    } else {
      $('#em_seccion_producto').hide();
    }

    // Limpiar campos de formularios
    $('#em_alias_nuevo').val('');
    $('#em_alias_tipo').val('Genérico');
    $('#em_alias_proveedor_wrap').hide();
    $('#em_prov_id').val('');
    $('#em_prov_precio').val('');
    $('#em_alerta').remove();

    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEM')).show();
  });

  /* ---- toggle proveedor en alias ---- */
  $('#em_alias_tipo').on('change', function () {
    $('#em_alias_proveedor_wrap').toggle($(this).val() === 'Proveedor');
  });

  /* ---- guardar datos generales ---- */
  $('#btn_guardar_insumo').on('click', function () {
    if (!filaActual) return;
    var btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando…');
    postJSON(BASE + 'compras/EntrenamientoMateriales/guardar_insumo_ajax', {
      id:            filaActual.id,
      estatus:       $('#em_estatus').val(),
      clase_uso:     $('#em_clase_uso').val(),
      alias_interno: $('#em_alias_interno').val()
    }, function (res) {
      btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar datos generales');
      alerta(res.message, res.success ? 'success' : 'danger');
      if (res.success) setTimeout(function () { window.location.reload(); }, 700);
    });
  });

  /* ---- agregar alias ---- */
  $('#btn_agregar_alias').on('click', function () {
    if (!filaActual) return;
    var tipo   = $('#em_alias_tipo').val();
    var alias  = $.trim($('#em_alias_nuevo').val());
    var provId = $('#em_alias_proveedor_id').val();
    if (!alias) { alerta('Escribe el alias'); return; }
    if (tipo === 'Proveedor' && !provId) { alerta('Selecciona proveedor'); return; }

    var data = { insumo_id: filaActual.id, alias: alias, tipo: tipo };
    if (tipo === 'Proveedor') data.proveedor_id = provId;

    var btn = $(this).prop('disabled', true);
    postJSON(BASE + 'compras/EntrenamientoMateriales/guardar_alias_ajax', data, function (res) {
      btn.prop('disabled', false);
      alerta(res.message, res.success ? 'success' : 'danger');
      if (res.success) {
        // actualizar lista local
        filaActual.aliases = filaActual.aliases || [];
        filaActual.aliases.push({id: res.id || null, alias: alias, tipo: tipo, proveedor_id: provId || null});
        renderAliases(filaActual.aliases);
        $('#em_alias_nuevo').val('');
      }
    });
  });

  /* ---- guardar precio proveedor (fila existente) ---- */
  $(document).on('click', '.em-btn-prov-guardar', function () {
    if (!filaActual) return;
    var provId = $(this).data('prov-id');
    var precio = $(this).closest('tr').find('.em-prov-precio').val();
    var btn    = $(this).prop('disabled', true);
    postJSON(BASE + 'compras/EntrenamientoMateriales/guardar_proveedor_precio_ajax', {
      insumo_id:    filaActual.id,
      proveedor_id: provId,
      precio_compra: precio
    }, function (res) {
      btn.prop('disabled', false);
      alerta(res.message, res.success ? 'success' : 'danger');
      if (res.success) setTimeout(function () { window.location.reload(); }, 700);
    });
  });

  /* ---- agregar nuevo proveedor ---- */
  $('#btn_guardar_proveedor').on('click', function () {
    if (!filaActual) return;
    var provId = $('#em_prov_id').val();
    var precio = $('#em_prov_precio').val();
    if (!provId) { alerta('Selecciona un proveedor'); return; }
    var btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
    postJSON(BASE + 'compras/EntrenamientoMateriales/guardar_proveedor_precio_ajax', {
      insumo_id:    filaActual.id,
      proveedor_id: provId,
      precio_compra: precio || '0'
    }, function (res) {
      btn.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
      alerta(res.message, res.success ? 'success' : 'danger');
      if (res.success) setTimeout(function () { window.location.reload(); }, 700);
    });
  });

  /* ---- guardar producto ---- */
  $('#btn_guardar_producto').on('click', function () {
    if (!filaActual || !filaActual.producto_id) return;
    var btn = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Guardando…');
    postJSON(BASE + 'compras/EntrenamientoMateriales/guardar_producto_ajax', {
      insumo_id:             filaActual.id,
      unidad_venta:          $('#em_prod_uv').val(),
      presentacion_principal:$('#em_prod_pres').val(),
      contenido_neto:        $('#em_prod_cn').val(),
      rendimiento:           $('#em_prod_rend').val(),
      precio_venta:          $('#em_prod_pv').val()
    }, function (res) {
      btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Guardar producto');
      alerta(res.message, res.success ? 'success' : 'danger');
      if (res.success) setTimeout(function () { window.location.reload(); }, 700);
    });
  });

  /* ---- DataTable ---- */
  $(document).ready(function () {
    if ($.fn.DataTable && $('#tablaEM tbody tr td').length > 1) {
      $('#tablaEM').DataTable({
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: -1 }],
        language: { url: 'https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' }
      });
    }
  });

}());
</script>
