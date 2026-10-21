<?php
$titulo = 'Catálogo de Conceptos';
$presupuesto = $presupuesto ?? null;
$this->load->view('obras/partials/print_head', ['titulo' => $titulo]);
?>
    <table class="tabla-datos">
        <thead><tr><th>CODIGO</th><th>DESCRIPCION</th><th>UNIDAD</th><th>CANTIDAD</th><th>P.U.</th><th>IMPORTE</th></tr></thead>
        <tbody>
        <?php foreach (($presupuesto->conceptos ?? []) as $c): ?>
            <tr>
                <td><?=htmlspecialchars($c->codigo ?? '')?></td>
                <td><?=htmlspecialchars($c->descripcion ?? '')?></td>
                <td class="text-center"><?=htmlspecialchars($c->unidad ?? '')?></td>
                <td class="text-right"><?=number_format((float)$c->cantidad, 2)?></td>
                <td class="text-right"><?=number_format((float)$c->precio_unitario, 2)?></td>
                <td class="text-right"><?=number_format((float)$c->importe, 2)?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php $this->load->view('obras/partials/print_foot'); ?>
