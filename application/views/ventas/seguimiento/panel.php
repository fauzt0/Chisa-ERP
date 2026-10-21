<?php
$s = $seguimiento ?? [];
$tipo = $s['origen_tipo'] ?? '';
$oid = (int) ($s['origen_id'] ?? 0);
$fases = $s['fases'] ?? [];
$eventos = $s['eventos'] ?? [];
$carpetas = $s['carpetas'] ?? [];
$archivos = $s['archivos'] ?? [];
$adiciones = $s['adiciones'] ?? [];
$asignaciones = $s['asignaciones'] ?? [];
$empleados = $s['empleados'] ?? [];
$avance = (float) ($s['avance'] ?? 0);
$estatus_obra = $s['estatus_obra'] ?? '';
$base = base_url('ventas/Seguimiento/');
$por_carpeta = [];
foreach ($archivos as $a) {
    $por_carpeta[(int) $a->carpeta_id][] = $a;
}
$hijos = [];
foreach ($carpetas as $c) {
    $hijos[(int) $c->parent_id][] = $c;
}
$pintar_arbol = function ($parent, $nivel) use (&$pintar_arbol, $hijos, $por_carpeta, $base) {
    foreach ($hijos[$parent] ?? [] as $carp) {
        echo '<div class="ms-' . min($nivel, 3) . ' mb-2">';
        echo '<div class="fw-semibold"><i class="fas fa-folder text-warning"></i> ' . htmlspecialchars($carp->nombre) . '</div>';
        foreach ($por_carpeta[(int) $carp->id] ?? [] as $ar) {
            echo '<div class="d-flex justify-content-between align-items-center small ps-3">';
            echo '<a href="' . $base . 'descargar/' . (int) $ar->id . '">' . htmlspecialchars($ar->nombre) . '</a>';
            echo '<button type="button" class="btn btn-link btn-sm text-danger p-0 seg-quitar-doc" data-id="' . (int) $ar->id . '">Quitar</button>';
            echo '</div>';
        }
        $pintar_arbol((int) $carp->id, $nivel + 1);
        echo '</div>';
    }
};
?>
<div class="card mb-3" id="panelSeguimiento" data-tipo="<?=htmlspecialchars($tipo)?>" data-id="<?=$oid?>">
  <div class="card-header d-flex justify-content-between align-items-center">
    <strong>Seguimiento, fases y expediente</strong>
    <span class="badge bg-primary">Avance <?=number_format($avance, 1)?>%</span>
  </div>
  <div class="card-body">
    <div class="progress mb-3" style="height:8px">
      <div class="progress-bar" style="width: <?=min(100, $avance)?>%"></div>
    </div>

    <?php if ($tipo === 'obra'): ?>
    <div class="row g-2 align-items-end mb-3">
      <div class="col-md-4">
        <label class="form-label">Estatus de la obra</label>
        <select class="form-select" id="segEstatusObra">
          <?php foreach (['Planificación','En Cotización','Aprobada','En Ejecución','Pausada','Completada','Cancelada'] as $est): ?>
          <option <?= $estatus_obra === $est ? 'selected' : '' ?>><?=htmlspecialchars($est)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-outline-primary" id="segGuardarEstatus">Cambiar estatus</button>
      </div>
    </div>
    <div class="row g-2 align-items-end mb-3">
      <div class="col-md-5">
        <label class="form-label">Asignar personal</label>
        <select class="form-select" id="segEmpleado">
          <option value="">—</option>
          <?php foreach ($empleados as $e): ?>
          <option value="<?=$e->id?>"><?=htmlspecialchars(trim($e->nombre . ' ' . $e->apellido_paterno) . ' · ' . $e->numero_empleado)?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <input class="form-control" id="segRol" placeholder="Rol (aplicador, residente)">
      </div>
      <div class="col-md-2">
        <button type="button" class="btn btn-outline-primary" id="segAsignar">Asignar</button>
      </div>
    </div>
    <ul class="list-unstyled small mb-3">
      <?php foreach ($asignaciones as $a): ?>
      <li class="d-flex justify-content-between">
        <span><?=htmlspecialchars(trim($a->nombre . ' ' . $a->apellido_paterno))?> · <?=htmlspecialchars($a->rol ?: '')?></span>
        <button type="button" class="btn btn-link btn-sm text-danger p-0 seg-quitar-asig" data-id="<?=$a->id?>">Quitar</button>
      </li>
      <?php endforeach; ?>
      <?php if (!$asignaciones): ?><li class="text-muted">Sin personal asignado. Si la obra pasa a ejecución, el sistema avisa.</li><?php endif; ?>
    </ul>
    <?php endif; ?>

    <h6 class="mb-2">Fases</h6>
    <div class="table-responsive mb-2">
      <table class="table table-sm align-middle mb-0">
        <thead><tr><th>Fase</th><th>Avance</th><th>%</th><th>Compromiso</th><th>Estatus</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($fases as $f): ?>
          <tr>
            <td><?=htmlspecialchars($f->nombre)?></td>
            <td style="min-width:90px"><div class="progress" style="height:6px"><div class="progress-bar" style="width:<?=(float)$f->porcentaje?>%"></div></div></td>
            <td><?=number_format((float)$f->porcentaje, 0)?>%</td>
            <td><?=htmlspecialchars($f->fecha_compromiso ?: '—')?></td>
            <td><?=htmlspecialchars($f->estatus)?></td>
            <td><button type="button" class="btn btn-link btn-sm text-danger p-0 seg-quitar-fase" data-id="<?=$f->id?>">Quitar</button></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$fases): ?><tr><td colspan="6" class="text-muted">Sin fases. En el Excel corresponden a planta, zona o frente.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="row g-2 align-items-end mb-3">
      <div class="col-md-4"><input class="form-control form-control-sm" id="segFaseNombre" placeholder="Nombre de la fase"></div>
      <div class="col-md-2"><input type="number" min="0" max="100" class="form-control form-control-sm" id="segFasePct" placeholder="%"></div>
      <div class="col-md-2"><input type="date" class="form-control form-control-sm" id="segFaseFecha"></div>
      <div class="col-md-2">
        <select class="form-select form-select-sm" id="segFaseEst"><option>Pendiente</option><option>En proceso</option><option>Terminada</option></select>
      </div>
      <div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-primary" id="segGuardarFase">Agregar fase</button></div>
    </div>

    <?php if ($tipo === 'cotizacion'): ?>
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h6 class="mb-0">Adiciones</h6>
      <button type="button" class="btn btn-sm btn-outline-primary" id="segAdicion">Nueva subcotización</button>
    </div>
    <ul class="small mb-3">
      <?php foreach ($adiciones as $ad): ?>
      <li><a href="<?=base_url('ventas/CotizacionesObra/editar/'.$ad->id)?>"><?=htmlspecialchars($ad->folio)?></a> · <?=htmlspecialchars($ad->estatus)?></li>
      <?php endforeach; ?>
      <?php if (!$adiciones): ?><li class="text-muted">Sin adiciones. Úsala cuando el cliente pida un frente nuevo.</li><?php endif; ?>
    </ul>
    <?php endif; ?>

    <h6>Expediente</h6>
    <p class="text-muted small">Facturas, PDF, Word o comprobantes. Suelta los archivos en la zona o elige una carpeta. Más adelante se pueden sincronizar con la nube.</p>
    <div class="row g-2 align-items-end mb-2">
      <div class="col-md-4"><input class="form-control form-control-sm" id="segCarpetaNombre" placeholder="Nueva carpeta (Facturas, Planos)"></div>
      <div class="col-md-3">
        <select class="form-select form-select-sm" id="segCarpetaPadre">
          <option value="">En la raíz</option>
          <?php foreach ($carpetas as $c): ?><option value="<?=$c->id?>"><?=htmlspecialchars($c->nombre)?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2"><button type="button" class="btn btn-sm btn-outline-secondary" id="segCrearCarpeta">Crear carpeta</button></div>
      <div class="col-md-3">
        <select class="form-select form-select-sm" id="segCarpetaDestino">
          <option value="">Subir a la raíz</option>
          <?php foreach ($carpetas as $c): ?><option value="<?=$c->id?>"><?=htmlspecialchars($c->nombre)?></option><?php endforeach; ?>
        </select>
      </div>
    </div>
    <div id="segDrop" class="border rounded p-4 text-center mb-2" style="border-style:dashed !important;background:#f8fafc;cursor:pointer">
      Arrastra aquí los documentos o haz clic para elegirlos
      <input type="file" id="segArchivos" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.xml,.zip" hidden>
    </div>
    <div class="mb-3">
      <?php foreach ($por_carpeta[0] ?? [] as $ar): ?>
      <div class="d-flex justify-content-between small">
        <a href="<?=$base?>descargar/<?=$ar->id?>"><?=htmlspecialchars($ar->nombre)?></a>
        <button type="button" class="btn btn-link btn-sm text-danger p-0 seg-quitar-doc" data-id="<?=$ar->id?>">Quitar</button>
      </div>
      <?php endforeach; ?>
      <?php $pintar_arbol(0, 0); ?>
      <?php if (!$archivos && !$carpetas): ?><div class="text-muted small">El expediente está vacío.</div><?php endif; ?>
    </div>

    <h6>Notas de seguimiento</h6>
    <div class="input-group mb-2">
      <input class="form-control" id="segNota" placeholder="Acuerdo, cambio de alcance, llamada con el cliente">
      <button class="btn btn-outline-primary" type="button" id="segGuardarNota">Agregar</button>
    </div>
    <ul class="list-unstyled small mb-0">
      <?php foreach ($eventos as $ev): ?>
      <li class="mb-1"><span class="text-muted"><?=htmlspecialchars(date('d/m/Y H:i', strtotime($ev->fecha)))?></span> · <?=htmlspecialchars($ev->mensaje)?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<script>
(function () {
  var panel = document.getElementById('panelSeguimiento');
  if (!panel || panel.dataset.listo) return;
  panel.dataset.listo = '1';
  var base = '<?=$base?>';
  var tipo = panel.dataset.tipo;
  var id = panel.dataset.id;
  function post(url, data, cb) {
    $.post(url, data, function (res) {
      res = typeof res === 'string' ? JSON.parse(res) : res;
      alert(res.message || 'Listo');
      if (cb) { cb(res); return; }
      if (res.success) location.reload();
    });
  }
  document.getElementById('segGuardarFase')?.addEventListener('click', function () {
    post(base + 'fase_ajax', {
      origen_tipo: tipo, origen_id: id,
      nombre: document.getElementById('segFaseNombre').value,
      porcentaje: document.getElementById('segFasePct').value,
      fecha_compromiso: document.getElementById('segFaseFecha').value,
      estatus: document.getElementById('segFaseEst').value
    });
  });
  document.getElementById('segGuardarNota')?.addEventListener('click', function () {
    post(base + 'nota_ajax', { origen_tipo: tipo, origen_id: id, mensaje: document.getElementById('segNota').value });
  });
  document.getElementById('segCrearCarpeta')?.addEventListener('click', function () {
    post(base + 'carpeta_ajax', {
      origen_tipo: tipo, origen_id: id,
      nombre: document.getElementById('segCarpetaNombre').value,
      parent_id: document.getElementById('segCarpetaPadre').value
    });
  });
  document.getElementById('segAdicion')?.addEventListener('click', function () {
    post(base + 'adicion_ajax', { cotizacion_id: id }, function (res) {
      if (res.success && res.id) location.href = '<?=base_url('ventas/CotizacionesObra/editar/')?>' + res.id;
    });
  });
  document.getElementById('segGuardarEstatus')?.addEventListener('click', function () {
    post(base + 'estatus_obra_ajax', { obra_id: id, estatus: document.getElementById('segEstatusObra').value });
  });
  document.getElementById('segAsignar')?.addEventListener('click', function () {
    post(base + 'asignar_ajax', { obra_id: id, empleado_id: document.getElementById('segEmpleado').value, rol: document.getElementById('segRol').value });
  });
  panel.addEventListener('click', function (ev) {
    if (ev.target.classList.contains('seg-quitar-fase')) {
      post(base + 'eliminar_fase_ajax', { id: ev.target.dataset.id, origen_id: id });
    }
    if (ev.target.classList.contains('seg-quitar-doc')) {
      post(base + 'eliminar_archivo_ajax', { id: ev.target.dataset.id });
    }
    if (ev.target.classList.contains('seg-quitar-asig')) {
      post(base + 'quitar_asignacion_ajax', { id: ev.target.dataset.id, obra_id: id });
    }
  });
  var drop = document.getElementById('segDrop');
  var input = document.getElementById('segArchivos');
  function subir(files) {
    if (!files || !files.length) return;
    var fd = new FormData();
    fd.append('origen_tipo', tipo);
    fd.append('origen_id', id);
    fd.append('carpeta_id', document.getElementById('segCarpetaDestino').value);
    Array.prototype.forEach.call(files, function (f) { fd.append('archivos[]', f); });
    $.ajax({
      url: base + 'subir_ajax',
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      success: function (res) {
        res = typeof res === 'string' ? JSON.parse(res) : res;
        alert(res.message || 'Listo');
        if (res.success) location.reload();
      }
    });
  }
  drop.addEventListener('click', function () { input.click(); });
  input.addEventListener('change', function () { subir(input.files); });
  drop.addEventListener('dragover', function (ev) { ev.preventDefault(); drop.classList.add('border-primary'); });
  drop.addEventListener('dragleave', function () { drop.classList.remove('border-primary'); });
  drop.addEventListener('drop', function (ev) {
    ev.preventDefault();
    drop.classList.remove('border-primary');
    subir(ev.dataTransfer.files);
  });
})();
</script>
