<?php
$titulo = 'Presupuesto de Obra';
$presupuesto = $presupuesto ?? null;
$cliente = $cliente ?? null;
$this->load->view('obras/partials/print_head', ['titulo' => $titulo]);
?>
    <div style="font-size:9px; margin-bottom:10px;">
        <div>CIUDAD DE MEXICO A <?=strtoupper(date('d'))?> DE <?=strtoupper(date('F'))?> DEL <?=date('Y')?></div>
        <div>CONSTRUCTORA: <?=htmlspecialchars($cliente->razon_social ?? '-')?></div>
        <div>AT´N: <?=htmlspecialchars($presupuesto->atencion ?? '-')?></div>
        <div>OBRA: <?=htmlspecialchars($presupuesto->obra_nombre ?? $presupuesto->pres_ref ?? '-')?></div>
        <div>PRES. REF. <?=htmlspecialchars($presupuesto->pres_ref ?? '-')?></div>
    </div>
    <table class="tabla-datos">
        <thead><tr><th>COLOR</th><th>CONCEPTO</th><th>UNIDAD</th><th>CANT.</th><th>PRECIO U.</th><th>IMPORTE</th></tr></thead>
        <tbody>
        <?php foreach (($presupuesto->conceptos ?? []) as $c): ?>
            <tr>
                <td class="text-center"><?=htmlspecialchars($c->codigo ?? '')?></td>
                <td><?=htmlspecialchars($c->descripcion ?? '')?></td>
                <td class="text-center"><?=htmlspecialchars($c->unidad ?? '')?></td>
                <td class="text-right"><?=number_format((float)$c->cantidad, 2)?></td>
                <td class="text-right"><?=number_format((float)$c->precio_unitario, 2)?></td>
                <td class="text-right"><?=number_format((float)$c->importe, 2)?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot class="bg-total">
            <tr><td colspan="5" class="text-right">IMPORTE</td><td class="text-right"><?=number_format((float)$presupuesto->subtotal, 2)?></td></tr>
            <tr><td colspan="5" class="text-right">(<?=number_format((float)$presupuesto->iva_porcentaje, 2)?>%) I.V.A.</td><td class="text-right"><?=number_format((float)$presupuesto->iva_monto, 2)?></td></tr>
            <tr><td colspan="5" class="text-right">TOTAL</td><td class="text-right"><?=number_format((float)$presupuesto->total, 2)?></td></tr>
        </tfoot>
    </table>
    <?php if (!empty($presupuesto->notas_legales)): ?>
    <div style="margin-top:10px; font-size:8px;"><strong>NOTAS LEGALES:</strong> <?=nl2br(htmlspecialchars($presupuesto->notas_legales))?></div>
    <?php endif; ?>
    <div class="firmas">
        <div class="firma-block"><div class="titulo">CLIENTE — <?=htmlspecialchars(strtoupper($cliente->razon_social ?? 'CONSTRUCTORA'))?></div><div class="firma-line">FIRMA / NOMBRE</div></div>
        <div class="firma-block"><div class="titulo"><?=htmlspecialchars(strtoupper($empresa->razon_social ?? 'CHISA'))?></div><div class="firma-line">FIRMA / NOMBRE</div></div>
    </div>
<?php $this->load->view('obras/partials/print_foot'); ?>

