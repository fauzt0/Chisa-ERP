<?php
$titulo = 'Datos de Obra Contratada';
$obra = $obra ?? null;
$cliente = $cliente ?? null;
$this->load->view('obras/partials/print_head', ['titulo' => $titulo]);
?>
    <table class="tabla-datos" style="width:80%;">
        <tbody>
            <tr><td class="bg-total" style="width:30%;">NOMBRE DE LA EMPRESA</td><td><?=htmlspecialchars($cliente->razon_social ?? '-')?></td></tr>
            <tr><td class="bg-total">DIRECCION</td><td><?=htmlspecialchars($obra->direccion ?? '-')?></td></tr>
            <tr><td class="bg-total">CONTACTO DE OFICINA</td><td><?=htmlspecialchars($cliente->contacto_nombre ?? '-')?></td></tr>
            <tr><td class="bg-total">TELEFONOS</td><td><?=htmlspecialchars($cliente->telefono ?? '-')?></td></tr>
            <tr><td class="bg-total">CIUDAD</td><td><?=htmlspecialchars($obra->ciudad ?? '-')?></td></tr>
            <tr><td class="bg-total">ESTADO</td><td><?=htmlspecialchars($obra->estado ?? '-')?></td></tr>
            <tr><td class="bg-total">AREA TOTAL</td><td><?=number_format((float)($obra->area_total ?? 0), 2)?> m²</td></tr>
            <tr><td class="bg-total">TIPO DE SUPERFICIE</td><td><?=htmlspecialchars($obra->tipo_superficie ?? '-')?></td></tr>
        </tbody>
    </table>
<?php $this->load->view('obras/partials/print_foot'); ?>
