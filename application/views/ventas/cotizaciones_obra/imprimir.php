<?php
$c = $cotizacion;
$refVisible = trim((string) ($c->pres_ref ?: ''));
if ($refVisible === '' || preg_match('/\.(xlsx|xls)$/i', $refVisible)) {
    $refVisible = $c->folio;
}
$lineas = [];
foreach ($c->detalles as $d) {
    $lineas[] = [
        'grupo' => $d->grupo,
        'concepto' => $d->concepto,
        'unidad' => $d->unidad,
        'cantidad' => $d->cantidad,
        'precio_unitario' => $d->precio_unitario,
        'importe' => $d->importe,
    ];
}
$nota_interna = '';
if (!empty($c->costo_envio) && (float) $c->costo_envio > 0) {
    $nota_interna = 'Envío 3 Guerras (interno, no se imprime al cliente): $ '
        . number_format((float) $c->costo_envio, 2)
        . (!empty($c->envio_notas) ? ' · ' . $c->envio_notas : '');
}
$this->load->view('papeleria/carta_hapa', [
    'marca' => $marca ?? [],
    'empresa' => $empresa ?? null,
    'para_pdf' => !empty($para_pdf),
    'fecha' => $c->fecha,
    'constructora' => $c->constructora,
    'atencion' => $c->atencion,
    'obra_nombre' => $c->obra_nombre,
    'ubicacion' => $c->ubicacion,
    'pres_ref' => $refVisible,
    'folio' => $c->folio,
    'introduccion' => $c->introduccion,
    'lineas' => $lineas,
    'total' => $c->total,
    'pie_texto' => $c->pie_texto,
    'notas_legales' => $c->notas_legales ?? ($marca['notas_cotizacion'] ?? ''),
    'forma_pago' => $c->forma_pago ?? ($marca['forma_pago'] ?? ''),
    'nota_interna' => empty($para_pdf) ? $nota_interna : '',
    'toolbar_pdf' => base_url('ventas/CotizacionesObra/pdf/' . $c->id),
    'toolbar_excel' => base_url('ventas/CotizacionesObra/excel/' . $c->id),
]);
