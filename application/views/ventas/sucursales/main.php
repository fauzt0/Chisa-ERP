<?php
$sucursales = $response['sucursales'] ?? [];
$puede = !empty($response['puede_editar']);
function img_suc($ruta) {
    if (!$ruta) return '';
    return base_url($ruta);
}
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <h1 class="h3 mb-2"><?=htmlspecialchars($headTitle)?></h1>
  <p class="text-muted">Logo, icono, marca de agua y datos de contacto que salen en la cotización comercial de obra.</p>
  <?php foreach ($sucursales as $s): ?>
  <div class="card mb-3">
    <div class="card-header"><strong><?=htmlspecialchars($s->codigo)?> · <?=htmlspecialchars($s->nombre)?></strong></div>
    <div class="card-body">
      <form class="form-sucursal" data-id="<?=$s->id?>">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label">Nombre</label><input class="form-control" name="nombre" value="<?=htmlspecialchars($s->nombre)?>" <?= $puede?'':'disabled'?>></div>
          <div class="col-md-4"><label class="form-label">Contacto</label><input class="form-control" name="contacto" value="<?=htmlspecialchars($s->contacto ?? '')?>" <?= $puede?'':'disabled'?>></div>
          <div class="col-md-4"><label class="form-label">Estatus</label>
            <select class="form-select" name="estatus" <?= $puede?'':'disabled'?>><option <?=$s->estatus==='Activa'?'selected':''?>>Activa</option><option <?=$s->estatus==='Inactiva'?'selected':''?>>Inactiva</option></select>
          </div>
          <div class="col-md-6"><label class="form-label">Dirección</label><input class="form-control" name="direccion" value="<?=htmlspecialchars($s->direccion ?? '')?>" <?= $puede?'':'disabled'?>></div>
          <div class="col-md-3"><label class="form-label">Ciudad</label><input class="form-control" name="ciudad" value="<?=htmlspecialchars($s->ciudad ?? '')?>" <?= $puede?'':'disabled'?>></div>
          <div class="col-md-3"><label class="form-label">Teléfono</label><input class="form-control" name="telefono" value="<?=htmlspecialchars($s->telefono ?? '')?>" <?= $puede?'':'disabled'?>></div>
          <div class="col-md-4"><label class="form-label">Correo</label><input class="form-control" name="email" value="<?=htmlspecialchars($s->email ?? '')?>" <?= $puede?'':'disabled'?>></div>
          <div class="col-md-4"><label class="form-label">Sitio web</label><input class="form-control" name="sitio_web" value="<?=htmlspecialchars($s->sitio_web ?? '')?>" <?= $puede?'':'disabled'?>></div>
          <div class="col-md-4"><label class="form-label">Texto de marca de agua</label><input class="form-control" name="texto_marca_agua" value="<?=htmlspecialchars($s->texto_marca_agua ?? '')?>" <?= $puede?'':'disabled'?>></div>
        </div>
        <?php if ($puede): ?><button class="btn btn-primary mt-3" type="submit">Guardar datos</button><?php endif; ?>
      </form>
      <div class="row g-3 mt-2">
        <?php foreach (['logo' => 'Logotipo', 'icono' => 'Icono', 'logo_marca_agua' => 'Marca de agua'] as $slot => $etiq): ?>
        <div class="col-md-4">
          <div class="border rounded p-2 h-100">
            <div class="small text-muted mb-1"><?=$etiq?></div>
            <?php if (!empty($s->$slot)): ?><img src="<?=img_suc($s->$slot)?>" alt="" style="max-height:64px;max-width:100%"><?php endif; ?>
            <?php if ($puede): ?>
            <form class="form-archivo mt-2" data-id="<?=$s->id?>" data-slot="<?=$slot?>">
              <input type="file" name="archivo" accept="image/*" class="form-control form-control-sm" required>
              <button class="btn btn-sm btn-outline-primary mt-1" type="submit">Subir</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<script>
document.querySelectorAll('.form-sucursal').forEach(function (form) {
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    var data = $(form).serialize() + '&id=' + form.dataset.id;
    $.post('<?=base_url('ventas/Sucursales/guardar_ajax')?>', data, function (res) {
      res = typeof res === 'string' ? JSON.parse(res) : res;
      alert(res.message || 'Listo');
    });
  });
});
document.querySelectorAll('.form-archivo').forEach(function (form) {
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    var fd = new FormData(form);
    fd.append('id', form.dataset.id);
    fd.append('slot', form.dataset.slot);
    $.ajax({
      url: '<?=base_url('ventas/Sucursales/subir_ajax')?>',
      method: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      success: function (res) {
        res = typeof res === 'string' ? JSON.parse(res) : res;
        alert(res.message || 'Listo');
        if (res.success) location.reload();
      }
    });
  });
});
</script>
