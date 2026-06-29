<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$e = $response['empresa'] ?? (object)[];
?>
<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0"><?=$headTitle?></h1>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="card-title text-white mb-0"><i class="fas fa-building"></i> Información fiscal y de contacto</h5>
        </div>
        <div class="card-body">
          <p class="text-muted small">Estos datos se utilizan en documentos del sistema (órdenes de compra, contratos, etc.).</p>

          <form id="formEmpresa">
            <h6 class="text-primary border-bottom pb-2 mb-3">Identificación</h6>
            <div class="row g-3 mb-3">
              <div class="col-md-8">
                <label class="form-label">Razón social <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="razon_social" value="<?=htmlspecialchars($e->razon_social ?? '')?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Nombre comercial</label>
                <input type="text" class="form-control" name="nombre_comercial" value="<?=htmlspecialchars($e->nombre_comercial ?? '')?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">RFC</label>
                <input type="text" class="form-control text-uppercase" name="rfc" maxlength="13" value="<?=htmlspecialchars($e->rfc ?? '')?>">
              </div>
              <div class="col-md-8">
                <label class="form-label">Régimen fiscal</label>
                <input type="text" class="form-control" name="regimen_fiscal" value="<?=htmlspecialchars($e->regimen_fiscal ?? '')?>" placeholder="Ej: 601 - General de Ley Personas Morales">
              </div>
            </div>

            <h6 class="text-primary border-bottom pb-2 mb-3">Domicilio fiscal</h6>
            <div class="row g-3 mb-3">
              <div class="col-md-8">
                <label class="form-label">Calle</label>
                <input type="text" class="form-control" name="calle" value="<?=htmlspecialchars($e->calle ?? '')?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">No. ext.</label>
                <input type="text" class="form-control" name="numero_exterior" value="<?=htmlspecialchars($e->numero_exterior ?? '')?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">No. int.</label>
                <input type="text" class="form-control" name="numero_interior" value="<?=htmlspecialchars($e->numero_interior ?? '')?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Colonia</label>
                <input type="text" class="form-control" name="colonia" value="<?=htmlspecialchars($e->colonia ?? '')?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="ciudad" value="<?=htmlspecialchars($e->ciudad ?? '')?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">Estado</label>
                <input type="text" class="form-control" name="estado" value="<?=htmlspecialchars($e->estado ?? '')?>">
              </div>
              <div class="col-md-2">
                <label class="form-label">C.P.</label>
                <input type="text" class="form-control" name="codigo_postal" maxlength="10" value="<?=htmlspecialchars($e->codigo_postal ?? '')?>">
              </div>
            </div>

            <h6 class="text-primary border-bottom pb-2 mb-3">Contacto</h6>
            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <label class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="telefono" value="<?=htmlspecialchars($e->telefono ?? '')?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Correo</label>
                <input type="email" class="form-control" name="email" value="<?=htmlspecialchars($e->email ?? '')?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Sitio web</label>
                <input type="url" class="form-control" name="sitio_web" value="<?=htmlspecialchars($e->sitio_web ?? '')?>" placeholder="https://">
              </div>
            </div>

            <button type="button" class="btn btn-primary" onclick="guardarEmpresa()">
              <i class="fas fa-save"></i> Guardar datos de la empresa
            </button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-0">Vista previa</h5>
        </div>
        <div class="card-body text-center">
          <?php $logo = !empty($e->logo) ? base_url($e->logo) : base_url('assets/dist/img/brands/chisa_recubrimientos_logo.jpg'); ?>
          <img src="<?=$logo?>" alt="Logo" class="img-fluid mb-3" style="max-height: 100px;">
          <h5 class="mb-1"><?=htmlspecialchars($e->razon_social ?? 'Chisa Recubrimientos', ENT_QUOTES, 'UTF-8')?></h5>
          <?php
            $lineas_previa = [];
            if (!empty($e->nombre_comercial)) {
              $lineas_previa[] = $e->nombre_comercial;
            }
            if (!empty($e->rfc)) {
              $lineas_previa[] = 'RFC: ' . $e->rfc;
            }
            if (!empty($e->regimen_fiscal)) {
              $lineas_previa[] = 'Régimen: ' . $e->regimen_fiscal;
            }
            $calle_prev = trim(($e->calle ?? '') . ' ' . ($e->numero_exterior ?? '') . (!empty($e->numero_interior) ? ' Int. ' . $e->numero_interior : ''));
            if ($calle_prev !== '') {
              $lineas_previa[] = $calle_prev;
            }
            if (!empty($e->colonia)) {
              $lineas_previa[] = 'Col. ' . $e->colonia;
            }
            $ciudad_linea = trim(($e->ciudad ?? '') . (!empty($e->estado) ? ', ' . $e->estado : ''));
            if (!empty($e->codigo_postal)) {
              $ciudad_linea .= ($ciudad_linea !== '' ? ' ' : '') . 'C.P. ' . $e->codigo_postal;
            }
            if ($ciudad_linea !== '') {
              $lineas_previa[] = $ciudad_linea;
            }
            if (empty($calle_prev) && empty($ciudad_linea)) {
              $lineas_previa[] = 'Industria de la Pintura y Recubrimientos';
              $lineas_previa[] = 'México';
            }
            if (!empty($e->telefono)) {
              $lineas_previa[] = 'Tel. ' . $e->telefono;
            }
            if (!empty($e->email)) {
              $lineas_previa[] = $e->email;
            }
          ?>
          <?php foreach ($lineas_previa as $linea): ?>
          <p class="small text-muted mb-1"><?=htmlspecialchars($linea, ENT_QUOTES, 'UTF-8')?></p>
          <?php endforeach; ?>
        </div>
      </div>
      <?php if(!empty($e->fecha_actualizacion)): ?>
      <p class="text-muted small mt-2">Última actualización: <?=date('d/m/Y H:i', strtotime($e->fecha_actualizacion))?></p>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function guardarEmpresa() {
  var data = $('#formEmpresa').serialize();
  data += '&<?=$this->security->get_csrf_token_name()?>=<?=$this->security->get_csrf_hash()?>';

  $.post('<?=base_url('usuarios/GestionUsuarios/guardar_empresa_ajax')?>', data, function(res) {
    res = typeof res === 'string' ? JSON.parse(res) : res;
    if (typeof notifyShow === 'function') {
      notifyShow(res.message, res.success ? 'success' : 'danger');
    } else {
      alert(res.message);
    }
    if (res.success) location.reload();
  });
}
</script>
