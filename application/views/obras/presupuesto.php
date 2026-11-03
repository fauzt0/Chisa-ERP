<?php
$presupuesto = $presupuesto ?? null;
$cliente = $cliente ?? null;
$marca = $marca_agua ?? [];
$lineas = [];
foreach (($presupuesto->conceptos ?? []) as $c) {
    $lineas[] = [
        'grupo' => $c->seccion ?? '',
        'concepto' => $c->descripcion ?? '',
        'unidad' => $c->unidad ?? '',
        'cantidad' => $c->cantidad ?? 0,
        'precio_unitario' => $c->precio_unitario ?? 0,
        'importe' => $c->importe ?? 0,
    ];
}
$this->load->view('papeleria/carta_hapa', [
    'marca' => $marca,
    'empresa' => $empresa ?? null,
    'para_pdf' => false,
    'fecha' => $presupuesto->fecha ?? date('Y-m-d'),
    'constructora' => $cliente->razon_social ?? ($presupuesto->constructora ?? ''),
    'atencion' => $presupuesto->atencion ?? '',
    'obra_nombre' => $presupuesto->obra_nombre ?? ($obra->nombre ?? ''),
    'ubicacion' => '',
    'pres_ref' => $presupuesto->pres_ref ?? ($presupuesto->folio ?? ''),
    'folio' => $presupuesto->folio ?? '',
    'introduccion' => $presupuesto->introduccion ?? '',
    'lineas' => $lineas,
    'total' => $presupuesto->subtotal ?? 0,
    'iva' => isset($presupuesto->iva_monto) ? (float) $presupuesto->iva_monto : null,
    'iva_pct' => isset($presupuesto->iva_porcentaje) ? (float) $presupuesto->iva_porcentaje : null,
    'pie_texto' => $presupuesto->notas_legales ?? ($marca['pie_cotizacion'] ?? ''),
    'notas_legales' => $marca['notas_cotizacion'] ?? '',
    'forma_pago' => $marca['forma_pago'] ?? '',
]);
