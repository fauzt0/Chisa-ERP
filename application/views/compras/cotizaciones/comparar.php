<?php
$this->load->helper('permissions');
$comp = $response['comparacion'];
$proveedores = $comp['proveedores'];
$insumos = $comp['insumos'];
$puede_aprobar = tiene_permiso('compras_cotizaciones_add') || tiene_permiso('compras_ordenes_add');
$puede_editar = tiene_permiso('compras_cotizaciones_edit') || tiene_permiso('compras_ordenes_edit');
?>
<div class="container-fluid p-0 compras-page">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><?php echo $headTitle; ?></h1>
    <a href="<?= base_url('compras/Cotizaciones'); ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Volver al listado
    </a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Grupo: <code><?php echo htmlspecialchars($comp['grupo_folio']); ?></code></h5>
      <p class="text-muted mb-0">Comparación de precios por proveedor. El mejor precio por insumo se resalta en verde.</p>
    </div>
  </div>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0 comparacion-cotizaciones">
          <thead class="table-light">
            <tr>
              <th>Insumo</th>
              <th class="text-center">Cantidad</th>
              <?php foreach ($proveedores as $prov): ?>
              <th class="text-center comparacion-prov-header">
                <div class="fw-bold"><?php echo htmlspecialchars($prov['nombre']); ?></div>
                <small class="text-muted"><?php echo htmlspecialchars($prov['folio']); ?></small>
                <div><span class="badge bg-<?php
                  echo $prov['estatus'] === 'Aprobada' ? 'success' : ($prov['estatus'] === 'Recibida' ? 'info' : ($prov['estatus'] === 'Rechazada' ? 'danger' : 'warning'));
                ?>"><?php echo $prov['estatus']; ?></span></div>
                <div class="small">Total: $<?php echo number_format($prov['total'], 2); ?></div>
                <?php if ($puede_aprobar && in_array($prov['estatus'], ['Pendiente', 'Recibida'], true)): ?>
                <button type="button" class="btn btn-success btn-sm mt-1" onclick="aprobarDesdeComparacion(<?php echo (int)$prov['cotizacion_id']; ?>)">
                  <i class="fas fa-check"></i> Aprobar → OC
                </button>
                <?php endif; ?>
                <?php if ($puede_editar && in_array($prov['estatus'], ['Pendiente', 'Recibida'], true)): ?>
                <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="marcarRecibidaComparacion(<?php echo (int)$prov['cotizacion_id']; ?>)">
                  <i class="fas fa-inbox"></i> Marcar recibida
                </button>
                <?php endif; ?>
              </th>
              <?php endforeach; ?>
              <th class="text-center bg-success text-white">Mejor precio</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($insumos as $ins): ?>
            <tr>
              <td>
                <strong><?php echo htmlspecialchars($ins['codigo']); ?></strong><br>
                <span class="text-muted small"><?php echo htmlspecialchars($ins['nombre']); ?></span>
              </td>
              <td class="text-center"><?php echo number_format($ins['cantidad'], 2); ?> <?php echo htmlspecialchars($ins['unidad_medida'] ?? ''); ?></td>
              <?php foreach ($proveedores as $prov):
                $pdata = $ins['precios'][$prov['proveedor_id']] ?? null;
                $precio = $pdata ? $pdata['precio_unitario'] : null;
                $es_mejor = $ins['mejor_proveedor_id'] && (int)$prov['proveedor_id'] === (int)$ins['mejor_proveedor_id'] && $precio > 0;
                $cellClass = $es_mejor ? 'table-success fw-bold' : '';
              ?>
              <td class="text-center <?php echo $cellClass; ?>">
                <?php if ($pdata && $precio > 0): ?>
                  $<?php echo number_format($precio, 2); ?>
                  <br><small class="text-muted">Sub: $<?php echo number_format($pdata['subtotal'], 2); ?></small>
                  <?php if ($es_mejor): ?><br><i class="fas fa-trophy text-success"></i><?php endif; ?>
                <?php elseif ($pdata): ?>
                  <span class="text-muted">Sin precio</span>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
              <?php endforeach; ?>
              <td class="text-center bg-light">
                <?php if ($ins['mejor_precio'] !== null): ?>
                  <strong class="text-success">$<?php echo number_format($ins['mejor_precio'], 2); ?></strong>
                  <?php
                  $mejor_nombre = '';
                  foreach ($proveedores as $p) {
                    if ((int)$p['proveedor_id'] === (int)$ins['mejor_proveedor_id']) {
                      $mejor_nombre = $p['nombre'];
                      break;
                    }
                  }
                  if ($mejor_nombre): ?>
                  <br><small><?php echo htmlspecialchars($mejor_nombre); ?></small>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot class="table-light">
            <tr>
              <th colspan="2">Total por proveedor</th>
              <?php foreach ($proveedores as $prov): ?>
              <th class="text-center">$<?php echo number_format($prov['total'], 2); ?></th>
              <?php endforeach; ?>
              <th></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <?php if (!empty($comp['cotizaciones'])): ?>
  <div class="card mt-3">
    <div class="card-header"><h5 class="card-title mb-0">Archivos adjuntos</h5></div>
    <div class="card-body">
      <div class="row">
        <?php foreach ($comp['cotizaciones'] as $cot):
          if (empty($cot->archivo_ruta)) continue;
        ?>
        <div class="col-md-4 mb-2">
          <div class="border rounded p-2">
            <strong><?php echo htmlspecialchars($cot->razon_social); ?></strong><br>
            <a href="<?= base_url($cot->archivo_ruta); ?>" target="_blank">
              <i class="fas fa-paperclip"></i> <?php echo htmlspecialchars($cot->archivo_nombre ?: 'Ver archivo'); ?>
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if ($puede_editar): ?>
      <hr>
      <p class="small text-muted">Subir cotización del proveedor:</p>
      <div class="row g-2">
        <?php foreach ($proveedores as $prov): ?>
        <div class="col-md-4">
          <label class="form-label small"><?php echo htmlspecialchars($prov['nombre']); ?></label>
          <input type="file" class="form-control form-control-sm archivo-comparacion" data-cot-id="<?php echo (int)$prov['cotizacion_id']; ?>" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
function aprobarDesdeComparacion(id) {
  Swal.fire({
    title: 'Aprobar cotización',
    text: 'Se generará OC en Borrador. Las otras cotizaciones del grupo se marcarán como rechazadas.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Aprobar',
    cancelButtonText: 'Cancelar'
  }).then(function(r) {
    if (!r.isConfirmed) return;
    $.post('<?= base_url('compras/Cotizaciones/aprobar_ajax'); ?>', { id: id }, function(res) {
      if (res.success) {
        Swal.fire('Éxito', res.message, 'success').then(function() { location.reload(); });
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    }, 'json');
  });
}

function marcarRecibidaComparacion(id) {
  Swal.fire({
    title: 'Marcar como recibida',
    text: 'Indica que el proveedor respondió. Edita precios desde el listado si es necesario.',
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'Marcar recibida',
    cancelButtonText: 'Cancelar'
  }).then(function(r) {
    if (!r.isConfirmed) return;
    $.post('<?= base_url('compras/Cotizaciones/marcar_recibida_ajax'); ?>', { id: id }, function(res) {
      if (res.success) {
        Swal.fire('OK', res.message, 'success').then(function() { location.reload(); });
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    }, 'json');
  });
}

$('.archivo-comparacion').on('change', function() {
  const cotId = $(this).data('cot-id');
  const file = this.files[0];
  if (!file) return;
  const fd = new FormData();
  fd.append('cotizacion_id', cotId);
  fd.append('archivo', file);
  $.ajax({
    url: '<?= base_url('compras/Cotizaciones/subir_archivo_ajax'); ?>',
    type: 'POST',
    data: fd,
    processData: false,
    contentType: false,
    success: function(res) {
      if (res.success) {
        Swal.fire('Archivo subido', res.message, 'success').then(function() { location.reload(); });
      } else {
        Swal.fire('Error', res.message, 'error');
      }
    }
  });
});
</script>

<style>
.comparacion-cotizaciones .comparacion-prov-header { min-width: 140px; vertical-align: top; }
.comparacion-cotizaciones .table-success { background-color: #d1e7dd !important; }
</style>
