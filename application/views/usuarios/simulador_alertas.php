<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$tipos = $response['tipos'];
$activas = $response['activas'];

$modulos = [];
foreach ($tipos as $key => $tipo) {
    $mod = $tipo['modulo'];
    if (!isset($modulos[$mod])) {
        $modulos[$mod] = [];
    }
    $modulos[$mod][$key] = $tipo;
}

$badgeClass = [
    'danger'  => 'bg-danger',
    'warning' => 'bg-warning text-dark',
    'info'    => 'bg-info text-dark',
];

$iconClass = [
    'danger'  => 'text-danger',
    'warning' => 'text-warning',
    'info'    => 'text-info',
];
?>

<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

  <div class="row mb-2 mb-xl-3 align-items-center">
    <div class="col">
      <h3 class="mb-1"><i class="fas fa-bell"></i> <?= htmlspecialchars($headTitle) ?></h3>
      <p class="text-muted mb-0">Dispara alertas de prueba en la campana del sistema sin modificar datos operativos.</p>
    </div>
    <div class="col-auto d-flex gap-2">
      <button type="button" class="btn btn-outline-primary" onclick="refrescarCampana()">
        <i class="fas fa-sync-alt"></i> Actualizar campana
      </button>
      <button type="button" class="btn btn-outline-danger" onclick="limpiarSimulaciones()">
        <i class="fas fa-trash-alt"></i> Limpiar simulaciones
      </button>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-md-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex align-items-center">
            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
              <i class="fas fa-bell text-primary fa-lg"></i>
            </div>
            <div>
              <h6 class="text-muted mb-0">Simulaciones activas</h6>
              <h2 class="mb-0" id="contador-activas"><?= count($activas) ?></h2>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <div class="alert alert-info mb-0 h-100 d-flex align-items-center">
        <i class="fas fa-info-circle me-2"></i>
        <span>Al simular, verás un <strong>toast inmediato</strong> y la alerta aparecerá en la campana. Solo usuarios con permiso <code>admin_simular_alertas</code> las ven.</span>
      </div>
    </div>
  </div>

  <?php foreach ($modulos as $modulo => $items): ?>
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-white">
      <h5 class="card-title mb-0"><i class="fas fa-layer-group me-2 text-muted"></i><?= htmlspecialchars($modulo) ?></h5>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <?php foreach ($items as $tipoKey => $tipo): ?>
        <div class="col-md-6 col-xl-4">
          <div class="card h-100 border sim-alert-card" data-tipo="<?= htmlspecialchars($tipoKey) ?>">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge <?= $badgeClass[$tipo['severidad']] ?? 'bg-secondary' ?>">
                  <?= htmlspecialchars(ucfirst($tipo['severidad'])) ?>
                </span>
                <i class="fas fa-<?= htmlspecialchars($tipo['icono']) ?> <?= $iconClass[$tipo['severidad']] ?? 'text-primary' ?> fa-lg"></i>
              </div>
              <h6 class="fw-bold mb-1"><?= htmlspecialchars($tipo['titulo']) ?></h6>
              <p class="text-muted small mb-3 flex-grow-1"><?= htmlspecialchars($tipo['mensaje']) ?></p>

              <div class="border rounded p-2 bg-light small mb-3 sim-preview">
                <div><strong><?= htmlspecialchars($modulo) ?>:</strong> <?= htmlspecialchars($tipo['titulo']) ?></div>
                <div class="text-muted"><?= htmlspecialchars($tipo['mensaje']) ?></div>
                <div class="text-muted"><?= htmlspecialchars($tipo['tiempo']) ?></div>
              </div>

              <button type="button" class="btn btn-primary w-100 btn-simular" data-tipo="<?= htmlspecialchars($tipoKey) ?>">
                <i class="fas fa-play-circle"></i> Simular
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

  <div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <h5 class="card-title mb-0"><i class="fas fa-list me-2"></i>Simulaciones activas</h5>
      <span class="badge bg-secondary" id="badge-lista-activas"><?= count($activas) ?></span>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0" id="tabla-simulaciones">
          <thead class="table-light">
            <tr>
              <th>Módulo</th>
              <th>Título</th>
              <th>Mensaje</th>
              <th>Severidad</th>
              <th>Creada</th>
            </tr>
          </thead>
          <tbody id="tbody-simulaciones">
            <?php if (empty($activas)): ?>
            <tr id="fila-vacia-sim">
              <td colspan="5" class="text-center text-muted py-4">No hay simulaciones activas</td>
            </tr>
            <?php else: ?>
            <?php foreach ($activas as $a): ?>
            <tr>
              <td><?= htmlspecialchars($a->modulo) ?></td>
              <td><?= htmlspecialchars($a->titulo) ?></td>
              <td class="small text-muted"><?= htmlspecialchars($a->mensaje) ?></td>
              <td><span class="badge <?= $badgeClass[$a->severidad] ?? 'bg-secondary' ?>"><?= htmlspecialchars($a->severidad) ?></span></td>
              <td class="small"><?= date('d/m/Y H:i', strtotime($a->creado_en)) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  const URL_SIMULAR = '<?= base_url('usuarios/GestionUsuarios/simular_alerta_ajax') ?>';
  const URL_LISTAR  = '<?= base_url('usuarios/GestionUsuarios/listar_simulaciones_ajax') ?>';
  const URL_LIMPIAR = '<?= base_url('usuarios/GestionUsuarios/limpiar_simulaciones_ajax') ?>';

  const badgeClass = { danger: 'bg-danger', warning: 'bg-warning text-dark', info: 'bg-info text-dark' };

  function parseJson(resp) {
    return typeof resp === 'string' ? JSON.parse(resp) : resp;
  }

  window.refrescarCampana = function() {
    if (typeof refreshNotifications === 'function') {
      refreshNotifications();
      showErpToast({ type: 'info', module: 'Administradores', title: 'Campana', message: 'Notificaciones actualizadas.' });
    } else if (typeof loadNotifications === 'function') {
      loadNotifications();
    }
  };

  function actualizarContadores(total) {
    $('#contador-activas').text(total);
    $('#badge-lista-activas').text(total);
  }

  function renderTabla(items) {
    const $tbody = $('#tbody-simulaciones');
    $tbody.empty();

    if (!items.length) {
      $tbody.append('<tr id="fila-vacia-sim"><td colspan="5" class="text-center text-muted py-4">No hay simulaciones activas</td></tr>');
      return;
    }

    items.forEach(function(item) {
      const fecha = item.creado_en ? new Date(item.creado_en.replace(' ', 'T')).toLocaleString('es-MX') : '';
      const cls = badgeClass[item.severidad] || 'bg-secondary';
      $tbody.append(
        '<tr>' +
          '<td>' + escapeHtml(item.modulo) + '</td>' +
          '<td>' + escapeHtml(item.titulo) + '</td>' +
          '<td class="small text-muted">' + escapeHtml(item.mensaje) + '</td>' +
          '<td><span class="badge ' + cls + '">' + escapeHtml(item.severidad) + '</span></td>' +
          '<td class="small">' + escapeHtml(fecha) + '</td>' +
        '</tr>'
      );
    });
  }

  function escapeHtml(str) {
    return String(str || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  window.limpiarSimulaciones = function() {
    $.post(URL_LIMPIAR, function(resp) {
      const data = parseJson(resp);
      if (!data.success) {
        showErpToast({ type: 'danger', module: 'Administradores', title: 'Error', message: data.message || 'No se pudo limpiar.' });
        return;
      }
      actualizarContadores(0);
      renderTabla([]);
      if (typeof refreshNotifications === 'function') refreshNotifications();
      showErpToast({ type: 'success', module: 'Administradores', title: 'Simulaciones limpiadas', message: data.message });
    }).fail(function() {
      showErpToast({ type: 'danger', module: 'Administradores', title: 'Error de conexión', message: 'No se pudo contactar al servidor.' });
    });
  };

  function simularAlerta(tipo, $btn) {
    const original = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Simulando...');

    $.post(URL_SIMULAR, { tipo: tipo }, function(resp) {
      const data = parseJson(resp);
      if (!data.success) {
        showErpToast({ type: 'danger', module: 'Administradores', title: 'Error', message: data.message || 'No se pudo simular.' });
        return;
      }

      if (data.notification && typeof showErpToast === 'function') {
        showErpToast(data.notification);
      }

      if (typeof refreshNotifications === 'function') {
        refreshNotifications();
      }

      $.get(URL_LISTAR, function(listResp) {
        const listData = parseJson(listResp);
        if (listData.success) {
          actualizarContadores(listData.total);
          renderTabla(listData.items || []);
        }
      });
    }).fail(function() {
      showErpToast({ type: 'danger', module: 'Administradores', title: 'Error de conexión', message: 'No se pudo contactar al servidor.' });
    }).always(function() {
      $btn.prop('disabled', false).html(original);
    });
  }

  $(document).on('click', '.btn-simular', function() {
    simularAlerta($(this).data('tipo'), $(this));
  });
})();
</script>
