<?php
$c = $response['cotizacion'] ?? null;
$dets = $c->detalles ?? [];
$puede = !empty($response['puede_editar']);
$sucursales = $response['sucursales'] ?? [];
$clientes = $response['clientes'] ?? [];
$conceptos = $response['conceptos'] ?? [];
$obras = $response['obras'] ?? [];
$bloqueada = $c && in_array($c->estatus, ['Cancelada', 'Aceptada'], true);
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0"><?=htmlspecialchars($headTitle)?></h1>
    <div class="d-flex gap-2">
      <?php if ($c): ?>
      <a class="btn btn-outline-secondary" target="_blank" href="<?=base_url('ventas/CotizacionesObra/imprimir/'.$c->id)?>">Vista / PDF</a>
      <a class="btn btn-outline-secondary" href="<?=base_url('ventas/CotizacionesObra/excel/'.$c->id)?>">Excel</a>
      <a class="btn btn-outline-secondary" href="<?=base_url('ventas/CotizacionesObra/pdf/'.$c->id)?>">Descargar PDF</a>
      <?php endif; ?>
      <a class="btn btn-link" href="<?=base_url('ventas/CotizacionesObra')?>">Volver</a>
    </div>
  </div>
  <?php if (!empty($response['padre'])): ?>
  <div class="alert alert-info">Adición de <a href="<?=base_url('ventas/CotizacionesObra/editar/'.$response['padre']->id)?>"><?=htmlspecialchars($response['padre']->folio)?></a>. El folio de esta subcotización es independiente.</div>
  <?php endif; ?>
  <?php if ($c && !empty($c->es_consulta)): ?>
  <div class="alert alert-secondary">Consulta de archivo. Las partidas no se desglosaron; el Excel original está en el expediente de abajo.</div>
  <?php endif; ?>

  <div class="card mb-3">
    <div class="card-body">
      <input type="hidden" id="cot_id" value="<?= (int) ($c->id ?? 0) ?>">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Sucursal (logo y marca de agua)</label>
          <select class="form-select" id="sucursal_id" <?= $bloqueada ? 'disabled' : '' ?>>
            <option value="">—</option>
            <?php foreach ($sucursales as $s): ?>
            <option value="<?=$s->id?>" <?= $c && (int)$c->sucursal_id === (int)$s->id ? 'selected' : '' ?>><?=htmlspecialchars($s->nombre)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Cliente</label>
          <select class="form-select" id="cliente_id" <?= $bloqueada ? 'disabled' : '' ?>>
            <option value="">—</option>
            <?php foreach ($clientes as $cl): ?>
            <option value="<?=$cl->id?>" <?= $c && (int)$c->cliente_id === (int)$cl->id ? 'selected' : '' ?>><?=htmlspecialchars($cl->razon_social)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Obra del CRM (opcional)</label>
          <select class="form-select" id="obra_id" <?= $bloqueada ? 'disabled' : '' ?>>
            <option value="">— Sin ligar —</option>
            <?php foreach ($obras as $o): ?>
            <option value="<?=$o->id?>" data-nombre="<?=htmlspecialchars($o->nombre, ENT_QUOTES)?>" data-cliente="<?= (int) $o->cliente_id ?>" <?= $c && (int)$c->obra_id === (int)$o->id ? 'selected' : '' ?>><?=htmlspecialchars($o->folio . ' · ' . $o->nombre)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Constructora</label>
          <input class="form-control" id="constructora" value="<?=htmlspecialchars($c->constructora ?? '')?>" <?= $bloqueada ? 'disabled' : '' ?>>
        </div>
        <div class="col-md-4">
          <label class="form-label">Atención</label>
          <input class="form-control" id="atencion" value="<?=htmlspecialchars($c->atencion ?? '')?>" <?= $bloqueada ? 'disabled' : '' ?>>
        </div>
        <div class="col-md-4">
          <label class="form-label">Fecha</label>
          <input type="date" class="form-control" id="fecha" value="<?=htmlspecialchars($c->fecha ?? date('Y-m-d'))?>" <?= $bloqueada ? 'disabled' : '' ?>>
        </div>
        <div class="col-md-6">
          <label class="form-label">Obra</label>
          <input class="form-control" id="obra_nombre" value="<?=htmlspecialchars($c->obra_nombre ?? '')?>" <?= $bloqueada ? 'disabled' : '' ?>>
        </div>
        <div class="col-md-3">
          <label class="form-label">Ubicación</label>
          <input class="form-control" id="ubicacion" value="<?=htmlspecialchars($c->ubicacion ?? '')?>" <?= $bloqueada ? 'disabled' : '' ?>>
        </div>
        <div class="col-md-3">
          <label class="form-label">Pres. ref.</label>
          <input class="form-control" id="pres_ref" value="<?=htmlspecialchars($c->pres_ref ?? '')?>" <?= $bloqueada ? 'disabled' : '' ?>>
        </div>
        <div class="col-12">
          <label class="form-label">Texto de presentación</label>
          <input class="form-control" id="introduccion" value="<?=htmlspecialchars($c->introduccion ?? 'PRESENTAMOS A SU AMABLE CONSIDERACIÓN EL PRESUPUESTO DE SUMINISTRO Y APLICACIÓN DE LOS SIGUIENTES CONCEPTOS.')?>" <?= $bloqueada ? 'disabled' : '' ?>>
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Conceptos</strong>
      <?php if ($puede && !$bloqueada): ?>
      <button type="button" class="btn btn-sm btn-outline-primary" id="btnFila">Agregar fila</button>
      <?php endif; ?>
    </div>
    <div class="table-responsive">
      <table class="table table-sm mb-0" id="tablaConceptos">
        <thead>
          <tr>
            <th style="min-width:120px">Tipo</th>
            <th style="min-width:140px">Fase / zona</th>
            <th style="min-width:280px">Concepto precargado o nuevo</th>
            <th>Unidad</th>
            <th>Cantidad</th>
            <th>Precio U.</th>
            <th>Importe</th>
            <th></th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <th colspan="6" class="text-end">Total</th>
            <th id="totalCot">$0.00</th>
            <th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <datalist id="listaConceptos">
    <?php foreach ($conceptos as $con): ?>
    <option value="<?=htmlspecialchars($con->concepto, ENT_QUOTES)?>" data-unidad="<?=htmlspecialchars($con->unidad ?? '', ENT_QUOTES)?>" data-precio="<?=htmlspecialchars($con->precio_referencia)?>" data-tipo="<?=htmlspecialchars($con->tipo ?? 'Servicio', ENT_QUOTES)?>" data-producto="<?= (int) ($con->producto_id ?? 0) ?>"></option>
    <?php endforeach; ?>
  </datalist>

  <?php if (!empty($response['seguimiento'])): ?>
  <?php $this->load->view('ventas/seguimiento/panel', ['seguimiento' => $response['seguimiento']]); ?>
  <?php endif; ?>

  <?php if ($puede && !$bloqueada): ?>
  <button type="button" class="btn btn-primary" id="btnGuardar">Guardar cotización</button>
  <?php endif; ?>
  <?php if ($c && $puede): ?>
  <div class="btn-group ms-2">
    <?php foreach (['Enviada' => 'Marcar enviada', 'Aceptada' => 'Aceptada', 'Rechazada' => 'Rechazada', 'Cancelada' => 'Cancelar'] as $est => $etiq): ?>
    <button type="button" class="btn btn-outline-secondary btn-estatus" data-estatus="<?=$est?>"><?=$etiq?></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<script>
var FILAS = <?= json_encode(array_map(function ($d) {
    return [
        'tipo' => $d->tipo ?? 'Servicio',
        'producto_id' => (int) ($d->producto_id ?? 0),
        'grupo' => $d->grupo,
        'concepto' => $d->concepto,
        'unidad' => $d->unidad,
        'cantidad' => (float) $d->cantidad,
        'precio_unitario' => (float) $d->precio_unitario,
    ];
}, $dets), JSON_UNESCAPED_UNICODE) ?>;
var BLOQUEADA = <?= $bloqueada ? 'true' : 'false' ?>;

function money(n) {
  return '$' + (Number(n) || 0).toLocaleString('es-MX', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
function filaHtml(f) {
  f = f || {};
  var dis = BLOQUEADA ? 'disabled' : '';
  var tipos = ['Producto','Aplicacion','Servicio'];
  var opts = tipos.map(function (t) {
    return '<option'+(f.tipo===t?' selected':'')+'>'+t+'</option>';
  }).join('');
  return '<tr data-producto="'+(f.producto_id||'')+'">'
    + '<td><select class="form-select form-select-sm t" '+dis+'>'+opts+'</select></td>'
    + '<td><input class="form-control form-control-sm g" '+dis+' value="'+esc(f.grupo||'')+'"></td>'
    + '<td><input class="form-control form-control-sm c" list="listaConceptos" '+dis+' value="'+esc(f.concepto||'')+'"></td>'
    + '<td><input class="form-control form-control-sm u" '+dis+' value="'+esc(f.unidad||'')+'"></td>'
    + '<td><input type="number" step="0.01" class="form-control form-control-sm q" '+dis+' value="'+(f.cantidad||0)+'"></td>'
    + '<td><input type="number" step="0.01" class="form-control form-control-sm p" '+dis+' value="'+(f.precio_unitario||0)+'"></td>'
    + '<td class="imp text-end align-middle"></td>'
    + '<td>'+(BLOQUEADA?'':'<button type="button" class="btn btn-sm btn-outline-danger quitar">×</button>')+'</td>'
    + '</tr>';
}
function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
}
function pintar() {
  var tb = document.querySelector('#tablaConceptos tbody');
  tb.innerHTML = '';
  (FILAS.length ? FILAS : [{}]).forEach(function (f) { tb.insertAdjacentHTML('beforeend', filaHtml(f)); });
  recalcular();
}
function recalcular() {
  var total = 0;
  document.querySelectorAll('#tablaConceptos tbody tr').forEach(function (tr) {
    var q = parseFloat(tr.querySelector('.q').value) || 0;
    var p = parseFloat(tr.querySelector('.p').value) || 0;
    var imp = Math.round(q * p * 100) / 100;
    tr.querySelector('.imp').textContent = money(imp);
    total += imp;
  });
  document.getElementById('totalCot').textContent = money(total);
}
function lineas() {
  var out = [];
  document.querySelectorAll('#tablaConceptos tbody tr').forEach(function (tr) {
    out.push({
      tipo: tr.querySelector('.t').value,
      producto_id: tr.dataset.producto || '',
      grupo: tr.querySelector('.g').value,
      concepto: tr.querySelector('.c').value,
      unidad: tr.querySelector('.u').value,
      cantidad: tr.querySelector('.q').value,
      precio_unitario: tr.querySelector('.p').value
    });
  });
  return out;
}
document.querySelector('#tablaConceptos').addEventListener('input', function (ev) {
  if (ev.target.classList.contains('c')) {
    var opt = document.querySelector('#listaConceptos option[value="'+CSS.escape(ev.target.value)+'"]');
    if (opt) {
      var tr = ev.target.closest('tr');
      if (!tr.querySelector('.u').value) tr.querySelector('.u').value = opt.dataset.unidad || '';
      if (!(parseFloat(tr.querySelector('.p').value) > 0)) tr.querySelector('.p').value = opt.dataset.precio || 0;
      if (opt.dataset.tipo) tr.querySelector('.t').value = opt.dataset.tipo;
      tr.dataset.producto = opt.dataset.producto || '';
    }
  }
  recalcular();
});
document.querySelector('#tablaConceptos').addEventListener('click', function (ev) {
  if (ev.target.classList.contains('quitar')) {
    ev.target.closest('tr').remove();
    recalcular();
  }
});
document.getElementById('btnFila')?.addEventListener('click', function () {
  document.querySelector('#tablaConceptos tbody').insertAdjacentHTML('beforeend', filaHtml({}));
});
document.getElementById('obra_id')?.addEventListener('change', function () {
  var opt = this.selectedOptions[0];
  if (!opt || !opt.value) return;
  if (!document.getElementById('obra_nombre').value) document.getElementById('obra_nombre').value = opt.dataset.nombre || '';
  if (opt.dataset.cliente && !document.getElementById('cliente_id').value) document.getElementById('cliente_id').value = opt.dataset.cliente;
});
document.getElementById('btnGuardar')?.addEventListener('click', function () {
  var btn = this;
  btn.disabled = true;
  $.post('<?=base_url('ventas/CotizacionesObra/guardar_ajax')?>', {
    id: document.getElementById('cot_id').value,
    sucursal_id: document.getElementById('sucursal_id').value,
    cliente_id: document.getElementById('cliente_id').value,
    obra_id: document.getElementById('obra_id').value,
    constructora: document.getElementById('constructora').value,
    atencion: document.getElementById('atencion').value,
    obra_nombre: document.getElementById('obra_nombre').value,
    ubicacion: document.getElementById('ubicacion').value,
    pres_ref: document.getElementById('pres_ref').value,
    fecha: document.getElementById('fecha').value,
    introduccion: document.getElementById('introduccion').value,
    lineas: JSON.stringify(lineas())
  }, function (res) {
    res = typeof res === 'string' ? JSON.parse(res) : res;
    alert(res.message || 'Listo');
    if (res.success && res.id) location.href = '<?=base_url('ventas/CotizacionesObra/editar/')?>' + res.id;
    else btn.disabled = false;
  });
});
document.querySelectorAll('.btn-estatus').forEach(function (b) {
  b.addEventListener('click', function () {
    if (!confirm('Cambiar estatus a ' + b.dataset.estatus + '?')) return;
    $.post('<?=base_url('ventas/CotizacionesObra/estatus_ajax')?>', {
      id: document.getElementById('cot_id').value,
      estatus: b.dataset.estatus
    }, function (res) {
      res = typeof res === 'string' ? JSON.parse(res) : res;
      alert(res.message || 'Listo');
      if (res.success) location.reload();
    });
  });
});
pintar();
</script>
