<?php
$titulo = 'Precios Actuales (PRECIO_ACT)';
$precios_actuales = $precios_actuales ?? [];
$this->load->view('obras/partials/print_head', ['titulo' => $titulo]);
?>
    <table class="tabla-datos">
        <thead><tr><th>CODIGO</th><th>DESCRIPCION</th><th>TIPO</th><th>UNIDAD</th><th>PRECIO</th><th>FECHA</th></tr></thead>
        <tbody>
        <?php foreach ($precios_actuales as $p): ?>
            <tr>
                <td><?=htmlspecialchars($p['codigo'])?></td>
                <td><?=htmlspecialchars($p['descripcion'])?></td>
                <td><?=htmlspecialchars($p['tipo'])?></td>
                <td class="text-center"><?=htmlspecialchars($p['unidad'])?></td>
                <td class="text-right"><?=number_format((float)$p['precio'], 2)?></td>
                <td><?=htmlspecialchars($p['fecha'])?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php $this->load->view('obras/partials/print_foot'); ?>
