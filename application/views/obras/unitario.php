<?php
$titulo = 'Análisis de Precio Unitario';
$presupuesto = $presupuesto ?? null;
$apu = $apu ?? [];
$this->load->view('obras/partials/print_head');
?>
<?php foreach (($presupuesto->conceptos ?? []) as $c): ?>
    <?php $a = $apu[$c->id] ?? null; if (!$a) continue; ?>
    <div style="margin-bottom:14px; border:1px solid #cbd5e1; padding:8px;">
        <div style="font-weight:700; font-size:9px;"><?=htmlspecialchars($c->codigo ?? '')?> — <?=htmlspecialchars($c->descripcion ?? '')?></div>
        <table class="tabla-datos" style="margin-top:6px;">
            <thead><tr><th>MATERIALES</th><th>UNIDAD</th><th>CANTIDAD</th><th>COSTO</th><th>IMPORTE</th></tr></thead>
            <tbody>
            <?php foreach ($a['materiales'] as $m): ?>
                <tr><td><?=htmlspecialchars($m['descripcion'])?></td><td class="text-center"><?=htmlspecialchars($m['unidad'])?></td><td class="text-right"><?=number_format($m['cantidad'], 4)?></td><td class="text-right"><?=number_format($m['costo_unitario'], 2)?></td><td class="text-right"><?=number_format($m['importe'], 2)?></td></tr>
            <?php endforeach; ?>
            <tr class="bg-total"><td colspan="4" class="text-right">SUBTOTAL MATERIAL</td><td class="text-right"><?=number_format($a['subtotal_material'], 2)?></td></tr>
            </tbody>
        </table>
        <table class="tabla-datos" style="margin-top:6px;">
            <thead><tr><th>CATEGORIA</th><th>SALARIO SEM</th><th>CANT X CUADRILLA</th><th>SALARIO X JOR</th><th>REDIMIENTO/JOR</th><th>COSTO</th></tr></thead>
            <tbody>
            <?php foreach ($a['mano_obra'] as $mo): ?>
                <tr><td><?=htmlspecialchars($mo['categoria'])?></td><td class="text-right"><?=number_format($mo['salario_semanal'], 2)?></td><td class="text-right"><?=number_format($mo['cant_cuadrilla'], 2)?></td><td class="text-right"><?=number_format($mo['salario_jor'], 2)?></td><td class="text-right"><?=number_format($mo['rendimiento_jor'], 4)?></td><td class="text-right"><?=number_format($mo['costo'], 4)?></td></tr>
            <?php endforeach; ?>
            <tr class="bg-total"><td colspan="5" class="text-right">SUBTOTAL MANO DE OBRA</td><td class="text-right"><?=number_format($a['subtotal_mo'], 4)?></td></tr>
            </tbody>
        </table>
        <table class="tabla-datos" style="margin-top:6px; width:60%;">
            <tbody>
                <tr><td>IMSS <?=$a['parametros']['imss']?>%</td><td class="text-right"><?=number_format($a['imss'], 2)?></td></tr>
                <tr><td>RCYV <?=$a['parametros']['rcyv']?>%</td><td class="text-right"><?=number_format($a['rcyv'], 2)?></td></tr>
                <tr><td>ISN <?=$a['parametros']['isn']?>%</td><td class="text-right"><?=number_format($a['isn'], 2)?></td></tr>
                <tr><td>HERRAMIENTA <?=$a['parametros']['herramienta']?>%</td><td class="text-right"><?=number_format($a['herramienta'], 2)?></td></tr>
                <tr class="bg-total"><td>COSTO DIRECTO</td><td class="text-right"><?=number_format($a['costo_directo'], 2)?></td></tr>
                <tr><td>INDIRECTO Y UTILIDAD <?=$a['parametros']['indirecto_utilidad']?>%</td><td class="text-right"><?=number_format($a['indirecto'], 2)?></td></tr>
                <tr class="bg-total"><td>P.UNITARIO</td><td class="text-right"><?=number_format($a['precio_unitario'], 2)?></td></tr>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>
<?php $this->load->view('obras/partials/print_foot'); ?>
