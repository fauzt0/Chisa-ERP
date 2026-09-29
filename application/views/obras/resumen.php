<?php
$titulo = 'Resumen de Obra';
$presupuesto = $presupuesto ?? null;
$this->load->view('obras/partials/print_head');
?>
    <div style="font-size:9px; margin-bottom:10px;"><strong>OBRA:</strong> <?=htmlspecialchars($presupuesto->obra_nombre ?? '-')?> · <strong>PRES. REF.:</strong> <?=htmlspecialchars($presupuesto->pres_ref ?? '-')?></div>
    <table class="tabla-datos">
        <thead><tr><th>SECCIÓN</th><th>FASE</th><th>ALCANCE / CONCEPTO</th><th>UNIDAD</th><th>IMPORTE</th></tr></thead>
        <tbody>
        <?php foreach (($presupuesto->conceptos ?? []) as $c): ?>
            <tr>
                <td><?=htmlspecialchars($c->seccion ?? '-')?></td>
                <td><?=htmlspecialchars($c->fase ?? '-')?></td>
                <td><?=htmlspecialchars($c->descripcion ?? '')?></td>
                <td class="text-center"><?=htmlspecialchars($c->unidad ?? '')?></td>
                <td class="text-right"><?=number_format((float)$c->importe, 2)?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot class="bg-total">
            <tr><td colspan="4" class="text-right">TOTAL</td><td class="text-right"><?=number_format((float)$presupuesto->total, 2)?></td></tr>
        </tfoot>
    </table>
<?php $this->load->view('obras/partials/print_foot'); ?>
