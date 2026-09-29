<?php
$titulo = 'Revisión de Cuantificación';
$revisiones = $revisiones ?? [];
$this->load->view('obras/partials/print_head', ['titulo' => $titulo]);
?>
    <table class="tabla-datos">
        <thead><tr><th>DESC</th><th>TOTAL CUANTI</th><th>COTIZADO</th><th>DIFERENCIA</th><th>%</th><th>COMENTARIOS</th></tr></thead>
        <tbody>
        <?php foreach ($revisiones as $r): ?>
            <tr>
                <td><?=htmlspecialchars($r->descripcion ?? '')?></td>
                <td class="text-right"><?=number_format((float)$r->total_cuantificado, 2)?></td>
                <td class="text-right"><?=number_format((float)$r->total_cotizado, 2)?></td>
                <td class="text-right"><?=number_format((float)$r->diferencia, 2)?></td>
                <td class="text-right"><?=number_format((float)$r->diferencia_pct, 2)?></td>
                <td><?=htmlspecialchars($r->comentario ?? '')?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php $this->load->view('obras/partials/print_foot'); ?>
