<?php
$titulo = 'Generador de Cuantificación';
$presupuesto = $presupuesto ?? null;
$generadores = $generadores ?? [];
$this->load->view('obras/partials/print_head', ['titulo' => $titulo]);
?>
<?php foreach ($generadores as $g): ?>
    <div style="margin-bottom:14px; border:1px solid #cbd5e1; padding:8px;">
        <div style="font-weight:700; font-size:9px;">HOJA N° <?=$g->hoja_no?> DE <?=$g->hoja_de?> · <?=htmlspecialchars($g->concepto_texto ?? '')?> · <?=htmlspecialchars($g->ubicacion ?? '')?></div>
        <table class="tabla-datos" style="margin-top:6px;">
            <thead><tr><th>PZAS</th><th>N°</th><th>LARGO</th><th>ALTURA</th><th>AREA</th><th>DESCUENTO</th></tr></thead>
            <tbody>
            <?php foreach (($g->lineas ?? []) as $l): ?>
                <tr><td class="text-right"><?=number_format((float)$l->pzas, 2)?></td><td class="text-right"><?=$l->n?></td><td class="text-right"><?=number_format((float)$l->largo, 3)?></td><td class="text-right"><?=number_format((float)$l->alto, 3)?></td><td class="text-right"><?=number_format((float)$l->area, 4)?></td><td class="text-right"><?=number_format((float)$l->descuento, 4)?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="simbologia">
            <strong>SIMBOLOGÍA:</strong>
            <span><b>P</b> = PUERTA</span><span><b>C.V.</b> = CUADRO DE VÁLVULAS</span><span><b>H.M.</b> = HUECO EN MURO</span>
            <span><b>BOQ.H/V</b> = BOCA DE HUEVO</span><span><b>C.</b> = CANCEL</span><span><b>V</b> = VENTANA</span>
            <span><b>G.E.</b> = GABINETE ELÉCTRICO</span><span><b>V.A.</b> = VANO DE ACCESO</span><span><b>O</b> = OTRO</span>
        </div>
        <table class="tabla-datos" style="margin-top:6px; width:50%;">
            <tr><td>SUMA ESTA HOJA</td><td class="text-right"><?=number_format((float)$g->suma_hoja, 2)?></td></tr>
            <tr><td>CANTIDAD ACUMULADA ANTERIOR</td><td class="text-right"><?=number_format((float)$g->acumulado_anterior, 2)?></td></tr>
            <tr class="bg-total"><td>TOTAL</td><td class="text-right"><?=number_format((float)$g->total, 2)?></td></tr>
        </table>
    </div>
<?php endforeach; ?>
<div class="firmas">
    <div class="firma-block"><div class="titulo">ELABORÓ</div><div class="firma-line">FIRMA / NOMBRE</div></div>
    <div class="firma-block"><div class="titulo">REVISÓ</div><div class="firma-line">FIRMA / NOMBRE</div></div>
</div>
<?php $this->load->view('obras/partials/print_foot'); ?>
