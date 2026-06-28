<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recibos de Pago — <?= htmlspecialchars($nomina->folio) ?></title>
  <style>
    body { margin: 0; padding: 20px; background: #eee; }
    @media print { body { background: #fff; padding: 0; } .no-print { display: none; } }
  </style>
</head>
<body>
  <div class="no-print" style="text-align:center;margin-bottom:20px;">
    <button onclick="window.print()" style="padding:10px 24px;background:#1e3a5f;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12pt;">
      Imprimir Recibos
    </button>
    <button onclick="window.close()" style="padding:10px 24px;background:#6c757d;color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:12pt;margin-left:8px;">
      Cerrar
    </button>
  </div>
  <?php $this->load->view('rh/nomina/partials/recibos_contenido', get_defined_vars()); ?>
</body>
</html>
