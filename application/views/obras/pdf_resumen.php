<?php
$logoUrl = base_url($empresa->logo ?? 'assets/dist/img/brands/chisa_recubrimientos_logo.jpg');
$direccionEmpresa = trim(implode(', ', array_filter([
    trim(($empresa->calle ?? '') . ' ' . ($empresa->numero_exterior ?? '')),
    $empresa->colonia ?? '',
    $empresa->ciudad ?? '',
    $empresa->estado ?? '',
    $empresa->codigo_postal ?? ''
])));
$telefonoEmpresa = $empresa->telefono ?? '';
$webEmpresa = $empresa->sitio_web ?? '';

$ubicacionObra = trim(implode(', ', array_filter([
    $obra->direccion ?? '',
    $obra->ciudad ?? '',
    $obra->estado ?? '',
    $obra->codigo_postal ?? ''
])));

$fechaDoc = date('d/m/Y');
$meses = ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
$fechaDocLarga = date('d') . ' DE ' . $meses[(int)date('n') - 1] . ' ' . date('Y');

$periodoEjecucion = '—';
if ($obra->fecha_inicio_estimada && $obra->fecha_fin_estimada) {
    $periodoEjecucion = date('d/m/Y', strtotime($obra->fecha_inicio_estimada))
        . ' al ' . date('d/m/Y', strtotime($obra->fecha_fin_estimada));
} elseif ($obra->fecha_inicio_estimada) {
    $periodoEjecucion = 'Desde ' . date('d/m/Y', strtotime($obra->fecha_inicio_estimada));
}

// --- Cálculos de áreas y avance ---
$m2_contratados = (float) ($obra->area_total ?: 0);
$m2_ejecutados = 0;
$productos_por_seccion = [];
$lineas_avance = [];
$num = 0;

foreach ($obra->productos ?? [] as $producto) {
    $num++;
    $cantidad = (float) ($producto->cantidad_ajustada ?: $producto->cantidad_calculada);
    $area = (float) ($producto->area_aplicacion ?: 0);
    $unidad = strtolower(trim($producto->unidad ?? ''));

    if ($area > 0) {
        $m2_ejecutados += $area;
    } elseif (in_array($unidad, ['m²', 'm2', 'metro', 'metros', 'metros cuadrados'])) {
        $m2_ejecutados += $cantidad;
        $area = $cantidad;
    }

    $sec = trim($producto->seccion_obra ?: 'ÁREA GENERAL');
    if (!isset($productos_por_seccion[$sec])) {
        $productos_por_seccion[$sec] = [];
    }
    $productos_por_seccion[$sec][] = $producto;

    $m2_partida = $area > 0 ? $area : ($m2_contratados > 0 ? $m2_contratados : $cantidad);
    $pct_partida = $m2_partida > 0 && $m2_contratados > 0
        ? min(($m2_partida / $m2_contratados) * 100, 999)
        : (float) ($obra->porcentaje_avance ?? 0);

    $lineas_avance[] = [
        'num' => $num,
        'concepto' => $producto->producto_nombre,
        'm2_contratados' => $m2_contratados > 0 ? $m2_contratados : $m2_partida,
        'm2_ejecutados' => $m2_partida,
        'pct_avance' => $pct_partida,
        'notas' => $producto->notas,
        'seccion' => $sec,
        'producto' => $producto
    ];
}

if ($m2_ejecutados <= 0 && $m2_contratados > 0) {
    $m2_ejecutados = $m2_contratados * ((float) ($obra->porcentaje_avance ?? 0) / 100);
}
if ($m2_contratados <= 0) {
    $m2_contratados = $m2_ejecutados;
}
$pct_avance_global = $m2_contratados > 0
    ? ($m2_ejecutados / $m2_contratados) * 100
    : (float) ($obra->porcentaje_avance ?? 0);

// --- Financieros ---
$subtotal = (float) ($obra->subtotal ?? 0);
$descuento = (float) ($obra->descuento_monto ?? 0);
$iva_pct = (float) ($obra->iva_porcentaje ?? 16);
$iva_monto = (float) ($obra->iva_monto ?? 0);
$total = (float) ($obra->total ?? 0);
$anticipo = (float) ($obra->anticipo_monto ?? 0);
$anticipo_pct = (float) ($obra->anticipo_porcentaje ?? 0);
$por_ejecutar_vol = $m2_contratados - $m2_ejecutados;
$por_ejecutar_imp = $total - ($subtotal - $descuento);
$amortizacion = $anticipo;
$subtotal_est = max($subtotal - $descuento - $amortizacion, 0);
$total_est = $subtotal_est + $iva_monto;

$concepto_principal = !empty($obra->productos[0])
    ? $obra->productos[0]->producto_nombre
    : $obra->nombre;

$total_pages = 5;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentación Obra - <?=$obra->folio?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', Arial, sans-serif; background: #e8ecf1; color: #1a1a2e; padding: 20px; font-size: 10px; }
        .toolbar { max-width: 1100px; margin: 0 auto 14px; display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: none; border-radius: 6px; padding: 9px 16px; font-size: 13px; font-weight: 600; cursor: pointer; color: #fff; }
        .btn-primary { background: #1565C0; }
        .btn-secondary { background: #546E7A; }

        #pdfDocument { max-width: 1100px; margin: 0 auto; }

        /* ── Página ── */
        .pdf-page {
            background: #fff;
            padding: 28px 32px 22px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            page-break-after: always;
            min-height: 680px;
            position: relative;
        }
        .pdf-page:last-child { page-break-after: auto; margin-bottom: 0; }

        /* ── Encabezado común ── */
        .pg-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #1565C0;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .pg-header .logo img { max-height: 56px; max-width: 180px; object-fit: contain; }
        .pg-meta { text-align: right; font-size: 9px; line-height: 1.65; color: #374151; }
        .pg-meta .lbl { font-weight: 700; color: #111; }
        .pg-title {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #111;
            margin-bottom: 14px;
            padding: 6px 0;
            border-top: 1px solid #d1d5db;
            border-bottom: 1px solid #d1d5db;
        }
        .pg-footer {
            position: absolute;
            bottom: 16px;
            left: 32px;
            right: 32px;
            border-top: 1px solid #d1d5db;
            padding-top: 8px;
            font-size: 8px;
            color: #6b7280;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pg-num { font-weight: 600; color: #1565C0; }

        /* ── Tablas ── */
        table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
        th, td { border: 1px solid #9ca3af; padding: 4px 5px; vertical-align: middle; }
        th { background: #e8eef5; font-weight: 700; text-align: center; color: #1e3a5f; }
        td { background: #fff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .bg-total td, .bg-total th { background: #dbeafe; font-weight: 700; }
        .bg-header th { background: #1565C0; color: #fff; border-color: #1565C0; }

        /* ── Info blocks ── */
        .info-row { display: flex; gap: 12px; margin-bottom: 12px; font-size: 9px; }
        .info-box { flex: 1; border: 1px solid #d1d5db; padding: 8px 10px; border-radius: 4px; }
        .info-box strong { display: inline-block; min-width: 110px; }

        /* ── Gráfica de barras CSS ── */
        .chart-wrap { margin: 16px 0; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; }
        .chart-subtitle { font-size: 10px; font-weight: 600; text-align: center; margin-bottom: 12px; text-transform: uppercase; }
        .bar-chart { display: flex; align-items: flex-end; justify-content: center; gap: 40px; height: 180px; padding: 0 20px; border-bottom: 2px solid #374151; position: relative; }
        .bar-group { display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .bar {
            width: 60px;
            background: linear-gradient(180deg, #42a5f5 0%, #1565C0 100%);
            border-radius: 4px 4px 0 0;
            min-height: 4px;
        }
        .bar.ejecutado { background: linear-gradient(180deg, #66bb6a 0%, #2E7D32 100%); }
        .bar-label { font-size: 8px; font-weight: 600; text-align: center; max-width: 80px; }
        .bar-value { font-size: 9px; font-weight: 700; color: #1565C0; }
        .chart-y { position: absolute; left: 0; top: 0; bottom: 0; width: 36px; font-size: 7px; color: #9ca3af; display: flex; flex-direction: column; justify-content: space-between; padding: 4px 0; }

        /* ── Caja financiera ── */
        .fin-box { float: right; width: 220px; margin: 0 0 12px 12px; font-size: 8.5px; }
        .fin-box td:first-child { font-weight: 600; background: #f3f4f6; width: 55%; }

        /* ── Firmas ── */
        .firmas { display: flex; gap: 16px; margin-top: 20px; }
        .firma-block { flex: 1; border: 1px solid #9ca3af; padding: 10px 8px; min-height: 80px; text-align: center; }
        .firma-block .titulo { font-size: 7.5px; font-weight: 700; text-transform: uppercase; margin-bottom: 30px; color: #374151; }
        .firma-line { border-top: 1px solid #374151; margin: 0 10px; padding-top: 4px; font-size: 8px; }

        /* ── Simbología ── */
        .simbologia { font-size: 7.5px; border: 1px solid #d1d5db; padding: 8px; margin-top: 10px; }
        .simbologia span { display: inline-block; margin-right: 12px; margin-bottom: 3px; }

        /* ── Secciones cuantificación ── */
        .sec-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 10px; }
        .sec-table-wrap h4 { font-size: 8px; font-weight: 700; text-align: center; background: #e8eef5; padding: 4px; border: 1px solid #9ca3af; border-bottom: none; text-transform: uppercase; }

        .desc-concepto { font-size: 8px; line-height: 1.5; text-align: justify; }

        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .pdf-page { box-shadow: none; margin: 0; page-break-after: always; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <button class="btn btn-primary" id="btnDescargarPdf">Descargar PDF Completo</button>
    <button class="btn btn-secondary" onclick="window.print()">Imprimir</button>
    <button class="btn btn-secondary" onclick="window.close()">Cerrar</button>
</div>

<div id="pdfDocument">

<!-- ══════════════════════════════════════════════════════
     PÁGINA 1 — PORTADA / RESUMEN EJECUTIVO
══════════════════════════════════════════════════════ -->
<div class="pdf-page">
    <div class="pg-header">
        <div class="logo"><img src="<?=$logoUrl?>" alt="Logo" crossorigin="anonymous" onerror="this.style.display='none'"></div>
        <div class="pg-meta">
            <div><span class="lbl">RAZÓN SOCIAL:</span> <?=htmlspecialchars($empresa->razon_social ?? 'Chisa Recubrimientos')?></div>
            <?php if(!empty($empresa->rfc)): ?><div><span class="lbl">RFC:</span> <?=htmlspecialchars($empresa->rfc)?></div><?php endif; ?>
            <?php if($direccionEmpresa): ?><div><?=htmlspecialchars($direccionEmpresa)?></div><?php endif; ?>
            <?php if($telefonoEmpresa): ?><div><span class="lbl">TEL.</span> <?=htmlspecialchars($telefonoEmpresa)?></div><?php endif; ?>
        </div>
    </div>

    <div class="pg-title">Resumen Ejecutivo de Obra</div>

    <div class="info-row">
        <div class="info-box">
            <div><strong>OBRA:</strong> <?=htmlspecialchars($obra->nombre)?></div>
            <div><strong>FOLIO:</strong> <?=$obra->folio?></div>
            <div><strong>CLIENTE:</strong> <?=htmlspecialchars($obra->cliente ?: '—')?></div>
            <div><strong>UBICACIÓN:</strong> <?=htmlspecialchars($ubicacionObra ?: '—')?></div>
            <div><strong>SUPERFICIE:</strong> <?=htmlspecialchars($obra->tipo_superficie ?: '—')?></div>
        </div>
        <div class="info-box">
            <div><strong>FECHA:</strong> <?=$fechaDoc?></div>
            <div><strong>PERIODO:</strong> <?=$periodoEjecucion?></div>
            <div><strong>ESTATUS:</strong> <?=htmlspecialchars($obra->estatus)?></div>
            <div><strong>% AVANCE:</strong> <?=number_format($pct_avance_global, 2)?>%</div>
            <?php if(!empty($obra->orden_venta_folio)): ?>
            <div><strong>ORDEN VENTA:</strong> <?=$obra->orden_venta_folio?></div>
            <?php endif; ?>
        </div>
    </div>

    <table>
        <thead class="bg-header">
            <tr>
                <th>Código</th><th>Producto / Material</th><th>Formulación</th>
                <th>Cantidad</th><th>P. Unit.</th><th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($obra->productos)): foreach($obra->productos as $p):
            $cant = $p->cantidad_ajustada ?: $p->cantidad_calculada;
            $precio = $p->precio_unitario ?: 0;
        ?>
            <tr>
                <td class="text-center"><?=htmlspecialchars($p->producto_codigo)?></td>
                <td><?=htmlspecialchars($p->producto_nombre)?><?php if($p->seccion_obra): ?><br><small>Sección: <?=htmlspecialchars($p->seccion_obra)?></small><?php endif; ?></td>
                <td class="text-center"><?=$p->formulacion_version ? 'V'.$p->formulacion_version : '—'?></td>
                <td class="text-center"><?=number_format($cant,2)?> <?=$p->unidad?></td>
                <td class="text-right">$<?=number_format($precio,2)?></td>
                <td class="text-right">$<?=number_format($cant*$precio,2)?></td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="6" class="text-center">Sin productos registrados</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot class="bg-total">
            <tr><td colspan="5" class="text-right">SUBTOTAL</td><td class="text-right">$<?=number_format($subtotal,2)?></td></tr>
            <?php if($descuento > 0): ?><tr><td colspan="5" class="text-right">DESCUENTO</td><td class="text-right">-$<?=number_format($descuento,2)?></td></tr><?php endif; ?>
            <tr><td colspan="5" class="text-right">IVA (<?=number_format($iva_pct,0)?>%)</td><td class="text-right">$<?=number_format($iva_monto,2)?></td></tr>
            <tr><td colspan="5" class="text-right">TOTAL</td><td class="text-right">$<?=number_format($total,2)?></td></tr>
        </tfoot>
    </table>

    <?php if($obra->descripcion || $obra->condiciones_pago || $obra->especificaciones_tecnicas): ?>
    <div style="margin-top:12px;font-size:8.5px;line-height:1.6;">
        <?php if($obra->descripcion): ?><p><strong>Descripción:</strong> <?=htmlspecialchars($obra->descripcion)?></p><?php endif; ?>
        <?php if($obra->especificaciones_tecnicas): ?><p><strong>Especificaciones:</strong> <?=htmlspecialchars($obra->especificaciones_tecnicas)?></p><?php endif; ?>
        <?php if($obra->condiciones_pago): ?><p><strong>Condiciones de pago:</strong> <?=htmlspecialchars($obra->condiciones_pago)?></p><?php endif; ?>
        <?php if($obra->tiempo_entrega): ?><p><strong>Tiempo de entrega:</strong> <?=htmlspecialchars($obra->tiempo_entrega)?></p><?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="pg-footer">
        <span><?=htmlspecialchars($direccionEmpresa)?><?php if($telefonoEmpresa): ?> &nbsp;|&nbsp; TEL. <?=htmlspecialchars($telefonoEmpresa)?><?php endif; ?><?php if($webEmpresa): ?> &nbsp;|&nbsp; <?=htmlspecialchars($webEmpresa)?><?php endif; ?></span>
        <span class="pg-num">Hoja 1 de <?=$total_pages?></span>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     PÁGINA 2 — AVANCE DE OBRA
══════════════════════════════════════════════════════ -->
<div class="pdf-page">
    <div class="pg-header">
        <div class="logo"><img src="<?=$logoUrl?>" alt="Logo" crossorigin="anonymous" onerror="this.style.display='none'"></div>
        <div class="pg-meta">
            <div><span class="lbl">PROYECTO:</span> <?=htmlspecialchars($obra->nombre)?></div>
            <div><span class="lbl">CLIENTE:</span> <?=htmlspecialchars($obra->cliente ?: '—')?></div>
            <div><span class="lbl">CONTRATISTA:</span> <?=htmlspecialchars($empresa->razon_social ?? 'Chisa Recubrimientos')?></div>
            <div><span class="lbl">ESTIMACIÓN:</span> ÚNICA</div>
            <div><span class="lbl">FECHA:</span> <?=$fechaDoc?></div>
        </div>
    </div>

    <div class="pg-title">Avance de Obra</div>

    <table>
        <thead class="bg-header">
            <tr><th>CONCEPTO</th><th>M² CONTRATADOS</th><th>M² EJECUTADOS</th><th>% AVANCE</th></tr>
        </thead>
        <tbody>
        <?php if(!empty($lineas_avance)): foreach($lineas_avance as $la): ?>
            <tr>
                <td><?=htmlspecialchars($la['concepto'])?></td>
                <td class="text-center"><?=number_format($la['m2_contratados'],2)?></td>
                <td class="text-center"><?=number_format($la['m2_ejecutados'],2)?></td>
                <td class="text-center"><?=number_format($la['pct_avance'],2)?>%</td>
            </tr>
        <?php endforeach; else: ?>
            <tr>
                <td><?=htmlspecialchars($concepto_principal)?></td>
                <td class="text-center"><?=number_format($m2_contratados,2)?></td>
                <td class="text-center"><?=number_format($m2_ejecutados,2)?></td>
                <td class="text-center"><?=number_format($pct_avance_global,2)?>%</td>
            </tr>
        <?php endif; ?>
        <tr class="bg-total">
            <td><strong>TOTAL GENERAL</strong></td>
            <td class="text-center"><?=number_format($m2_contratados,2)?></td>
            <td class="text-center"><?=number_format($m2_ejecutados,2)?></td>
            <td class="text-center"><?=number_format($pct_avance_global,2)?>%</td>
        </tr>
        </tbody>
    </table>

    <div class="chart-wrap">
        <div class="chart-subtitle"><?=htmlspecialchars($concepto_principal)?> — <?=htmlspecialchars($obra->tipo_superficie ?: 'Superficie')?></div>
        <?php $maxBar = max($m2_contratados, $m2_ejecutados, 1); ?>
        <div class="bar-chart">
            <div class="chart-y">
                <span><?=number_format($maxBar,0)?></span>
                <span><?=number_format($maxBar*0.75,0)?></span>
                <span><?=number_format($maxBar*0.5,0)?></span>
                <span><?=number_format($maxBar*0.25,0)?></span>
                <span>0</span>
            </div>
            <div class="bar-group">
                <div class="bar-value"><?=number_format($m2_contratados,2)?></div>
                <div class="bar" style="height:<?=round(($m2_contratados/$maxBar)*150)?>px"></div>
                <div class="bar-label">M² CONTRATADOS</div>
            </div>
            <div class="bar-group">
                <div class="bar-value"><?=number_format($m2_ejecutados,2)?></div>
                <div class="bar ejecutado" style="height:<?=round(($m2_ejecutados/$maxBar)*150)?>px"></div>
                <div class="bar-label">M² EJECUTADOS</div>
            </div>
            <div class="bar-group">
                <div class="bar-value"><?=number_format($pct_avance_global,2)?>%</div>
                <div class="bar ejecutado" style="height:<?=min(round($pct_avance_global*1.2),150)?>px; width:40px; background:linear-gradient(180deg,#ffa726,#E65100)"></div>
                <div class="bar-label">% AVANCE</div>
            </div>
        </div>
    </div>

    <div class="pg-footer">
        <span><?=htmlspecialchars($direccionEmpresa)?><?php if($telefonoEmpresa): ?> &nbsp;|&nbsp; TEL. <?=htmlspecialchars($telefonoEmpresa)?><?php endif; ?></span>
        <span class="pg-num">Hoja 2 de <?=$total_pages?></span>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     PÁGINA 3 — HOJA DE RESUMEN
══════════════════════════════════════════════════════ -->
<div class="pdf-page">
    <div class="pg-header">
        <div class="logo"><img src="<?=$logoUrl?>" alt="Logo" crossorigin="anonymous" onerror="this.style.display='none'"></div>
        <div class="pg-meta">
            <div><span class="lbl">CLIENTE:</span> <?=htmlspecialchars($obra->cliente ?: '—')?></div>
            <div><span class="lbl">OBRA:</span> <?=htmlspecialchars($obra->nombre)?></div>
            <div><span class="lbl">UBICACIÓN:</span> <?=htmlspecialchars($ubicacionObra ?: '—')?></div>
            <div><span class="lbl">PRES. REF.:</span> <?=$obra->folio?></div>
            <div><span class="lbl">ESTIMACIÓN No.:</span> ÚNICA</div>
            <div><span class="lbl">FECHA:</span> <?=$fechaDocLarga?></div>
            <div><span class="lbl">PERIODO DE EJECUCIÓN:</span> <?=$periodoEjecucion?></div>
        </div>
    </div>

    <div class="pg-title">Hoja de Resumen</div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">Nº</th>
                <th rowspan="2">CONCEPTO</th>
                <th colspan="6">ÁREAS GENERADAS</th>
                <th colspan="3">CANTIDADES EXTRAS</th>
            </tr>
            <tr>
                <th>HOJA GENERADORA</th>
                <th>ESTA ESTIMACIÓN</th>
                <th>ACUM. ANTERIOR</th>
                <th>TOTAL ACUMULADO</th>
                <th>PRESUPUESTO</th>
                <th>POR EJECUTAR</th>
                <th>EXTRAS</th>
                <th>ACUM. ANTERIOR</th>
                <th>TOTAL EXTRAS</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($lineas_avance)): $n=0; foreach($lineas_avance as $la): $n++;
            $esta_est = $la['m2_ejecutados'];
            $presup = $la['m2_contratados'];
            $por_ejec = $presup - $esta_est;
        ?>
            <tr>
                <td class="text-center"><?=$n?></td>
                <td class="desc-concepto"><?=$n?>.- SUMINISTRO Y APLICACIÓN DE <?=strtoupper(htmlspecialchars($la['concepto']))?><?php if($obra->tipo_superficie): ?> EN SUPERFICIE <?=strtoupper(htmlspecialchars($obra->tipo_superficie))?><?php endif; ?>.</td>
                <td class="text-center"><?=$n?>-1</td>
                <td class="text-center"><?=number_format($esta_est,2)?></td>
                <td class="text-center">0.00</td>
                <td class="text-center"><?=number_format($esta_est,2)?></td>
                <td class="text-center"><?=number_format($presup,2)?></td>
                <td class="text-center"><?=number_format($por_ejec,2)?></td>
                <td class="text-center">0.00</td>
                <td class="text-center">0.00</td>
                <td class="text-center">0.00</td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="11" class="text-center">Sin conceptos registrados</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="firmas">
        <div class="firma-block">
            <div class="titulo"><?=htmlspecialchars(strtoupper($empresa->nombre_comercial ?? $empresa->razon_social ?? 'CHISA'))?></div>
            <div class="firma-line">FIRMA / NOMBRE</div>
        </div>
        <div class="firma-block">
            <div class="titulo">CLIENTE — RECIBE A REVISIÓN</div>
            <div class="firma-line">FIRMA / NOMBRE</div>
        </div>
        <div class="firma-block">
            <div class="titulo">CLIENTE — VISTO BUENO</div>
            <div class="firma-line">FIRMA / NOMBRE</div>
        </div>
    </div>

    <div class="pg-footer">
        <span><?=htmlspecialchars($direccionEmpresa)?></span>
        <span class="pg-num">Hoja 3 de <?=$total_pages?></span>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     PÁGINA 4 — ESTIMACIÓN DE OBRA
══════════════════════════════════════════════════════ -->
<div class="pdf-page">
    <div class="pg-header">
        <div class="logo"><img src="<?=$logoUrl?>" alt="Logo" crossorigin="anonymous" onerror="this.style.display='none'"></div>
        <div class="pg-meta">
            <div><span class="lbl">ESTIMACIÓN DE OBRA</span></div>
            <div><span class="lbl">OBRA:</span> <?=htmlspecialchars($obra->nombre)?></div>
            <div><span class="lbl">PROYECTO:</span> <?=htmlspecialchars($obra->nombre)?></div>
            <div><span class="lbl">CLIENTE:</span> <?=htmlspecialchars($obra->cliente ?: '—')?></div>
            <div><span class="lbl">CONTRATISTA:</span> <?=htmlspecialchars($empresa->razon_social ?? 'Chisa')?></div>
            <div><span class="lbl">No. ESTIMACIÓN:</span> ÚNICA</div>
            <div><span class="lbl">PARTIDA:</span> <?=htmlspecialchars($concepto_principal)?></div>
            <div><span class="lbl">FECHA:</span> <?=$fechaDoc?> &nbsp; <span class="lbl">PERIODO:</span> <?=$periodoEjecucion?></div>
            <div><span class="lbl">PRES. REF.:</span> <?=$obra->folio?></div>
        </div>
    </div>

    <table class="fin-box">
        <tr><td>Monto de Contrato</td><td class="text-right">$<?=number_format($total,2)?></td></tr>
        <tr><td>Adendum</td><td class="text-right">$ —</td></tr>
        <tr><td>Gran Total</td><td class="text-right">$<?=number_format($total,2)?></td></tr>
        <tr><td>Monto por Ejercer</td><td class="text-right">$<?=number_format(max($por_ejecutar_imp, 0),2)?></td></tr>
        <tr><td>Monto Anticipo (<?=number_format($anticipo_pct,0)?>%)</td><td class="text-right">$<?=number_format($anticipo,2)?></td></tr>
        <tr><td>Amortización Anticipo</td><td class="text-right">$<?=number_format($amortizacion,2)?></td></tr>
        <tr><td>Monto por Amortizar</td><td class="text-right">$ —</td></tr>
    </table>

    <div class="pg-title" style="clear:both;">Estimación de Obra — Detalle Financiero</div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">CLAVE</th>
                <th rowspan="2">CONCEPTO</th>
                <th rowspan="2">VOL. TOPE</th>
                <th rowspan="2">U.</th>
                <th rowspan="2">P.U.</th>
                <th rowspan="2">PRESUPUESTO</th>
                <th colspan="2">ESTIM. ANTERIOR</th>
                <th colspan="2">ESTA ESTIMACIÓN</th>
                <th colspan="2">ESTIM. ACUMULADA</th>
                <th colspan="3">POR ESTIMAR</th>
            </tr>
            <tr>
                <th>VOL.</th><th>IMPORTE</th>
                <th>VOL.</th><th>IMPORTE</th>
                <th>VOL.</th><th>IMPORTE</th>
                <th>VOL.</th><th>IMPORTE</th><th>EST.%</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($obra->productos)): foreach($obra->productos as $p):
            $cant = (float)($p->cantidad_ajustada ?: $p->cantidad_calculada);
            $area = (float)($p->area_aplicacion ?: $cant);
            $pu = (float)($p->precio_unitario ?: 0);
            $presup = $area * $pu;
            $importe = $area * $pu;
            $vol_tope = $m2_contratados > 0 ? $m2_contratados : $area;
            $por_est_vol = $vol_tope - $area;
            $por_est_imp = ($vol_tope * $pu) - $importe;
            $pct_est = $vol_tope > 0 ? ($area / $vol_tope) * 100 : 0;
            $desc = 'Suministro y Aplicación de ' . $p->producto_nombre;
            if ($obra->tipo_superficie) $desc .= ' en superficie ' . $obra->tipo_superficie;
            if ($p->notas) $desc .= '. ' . $p->notas;
            if ($obra->especificaciones_tecnicas) $desc .= ' ' . $obra->especificaciones_tecnicas;
        ?>
            <tr>
                <td class="text-center"><?=htmlspecialchars($p->producto_codigo)?></td>
                <td class="desc-concepto"><?=htmlspecialchars($desc)?></td>
                <td class="text-center"><?=number_format($vol_tope,2)?></td>
                <td class="text-center">M²</td>
                <td class="text-right">$<?=number_format($pu,2)?></td>
                <td class="text-right">$<?=number_format($presup,2)?></td>
                <td class="text-center">—</td><td class="text-right">—</td>
                <td class="text-center"><?=number_format($area,2)?></td>
                <td class="text-right">$<?=number_format($importe,2)?></td>
                <td class="text-center"><?=number_format($area,2)?></td>
                <td class="text-right">$<?=number_format($importe,2)?></td>
                <td class="text-center"><?=number_format($por_est_vol,2)?></td>
                <td class="text-right">$<?=number_format($por_est_imp,2)?></td>
                <td class="text-center"><?=number_format($pct_est,0)?>%</td>
            </tr>
        <?php endforeach; else: ?>
            <tr><td colspan="15" class="text-center">Sin partidas</td></tr>
        <?php endif; ?>
        </tbody>
        <tfoot class="bg-total">
            <tr>
                <td colspan="9" class="text-right">IMPORTE ESTA ESTIMACIÓN</td>
                <td class="text-right">$<?=number_format($subtotal - $descuento,2)?></td>
                <td colspan="2"></td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="9" class="text-right">AMORTIZACIÓN ANTICIPO</td>
                <td class="text-right">$<?=number_format($amortizacion,2)?></td>
                <td colspan="5"></td>
            </tr>
            <tr>
                <td colspan="9" class="text-right">SUBTOTAL</td>
                <td class="text-right">$<?=number_format($subtotal_est,2)?></td>
                <td colspan="5"></td>
            </tr>
            <tr>
                <td colspan="9" class="text-right">I.V.A. (<?=number_format($iva_pct,0)?>%)</td>
                <td class="text-right">$<?=number_format($iva_monto,2)?></td>
                <td colspan="5"></td>
            </tr>
            <tr>
                <td colspan="9" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>$<?=number_format($total_est,2)?></strong></td>
                <td colspan="5"></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top:10px;font-size:8px;">
        <strong>NOTAS:</strong> <?=htmlspecialchars($obra->notas_internas ?: $obra->descripcion ?: '—')?>
    </div>

    <div class="firmas" style="margin-top:14px;">
        <div class="firma-block">
            <div class="titulo">AUTORIZA — <?=htmlspecialchars(strtoupper($empresa->nombre_comercial ?? 'CHISA'))?></div>
            <div class="firma-line">FIRMA / NOMBRE</div>
        </div>
        <div class="firma-block">
            <div class="titulo">RECIBE — <?=htmlspecialchars(strtoupper($obra->cliente ?: 'CLIENTE'))?></div>
            <div class="firma-line">FIRMA / NOMBRE</div>
        </div>
    </div>

    <div class="pg-footer">
        <span><?=htmlspecialchars($direccionEmpresa)?></span>
        <span class="pg-num">Hoja 4 de <?=$total_pages?></span>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     PÁGINA 5 — GENERADOR PARA CUANTIFICAR
══════════════════════════════════════════════════════ -->
<div class="pdf-page">
    <div class="pg-header">
        <div class="logo"><img src="<?=$logoUrl?>" alt="Logo" crossorigin="anonymous" onerror="this.style.display='none'"></div>
        <div class="pg-meta">
            <div><span class="lbl">GENERADOR PARA CUANTIFICAR</span></div>
            <div><span class="lbl">OBRA:</span> <?=htmlspecialchars($obra->nombre)?></div>
            <div><span class="lbl">UBICACIÓN:</span> <?=htmlspecialchars($ubicacionObra ?: '—')?></div>
            <div><span class="lbl">ESTIMACIÓN:</span> ÚNICA</div>
            <div><span class="lbl">CONCEPTO:</span> SUMINISTRO Y APLICACIÓN DE <?=strtoupper(htmlspecialchars($concepto_principal))?></div>
            <div><span class="lbl">FECHA:</span> <?=$fechaDocLarga?></div>
            <div><span class="lbl">PERIODO:</span> <?=$periodoEjecucion?></div>
        </div>
    </div>

    <div class="pg-title">Generador para Cuantificar</div>

    <div class="sec-grid">
    <?php
    $secciones = !empty($productos_por_seccion) ? $productos_por_seccion : ['ÁREA GENERAL' => ($obra->productos ?? [])];
    foreach ($secciones as $nombre_sec => $items):
        $suma_sec = 0;
    ?>
        <div class="sec-table-wrap">
            <h4><?=htmlspecialchars(strtoupper($nombre_sec))?></h4>
            <table>
                <thead>
                    <tr><th>Nº</th><th>ÁREA m²</th><th>UNIDAD</th><th>CANT.</th><th>DESC.</th></tr>
                </thead>
                <tbody>
                <?php $i=0; foreach($items as $item): $i++;
                    $area = (float)($item->area_aplicacion ?: 0);
                    $cant = (float)($item->cantidad_ajustada ?: $item->cantidad_calculada);
                    $val = $area > 0 ? $area : $cant;
                    $suma_sec += $val;
                    $desc = $item->factor_desperdicio && $item->factor_desperdicio > 1
                        ? '-' . number_format($val * ($item->factor_desperdicio - 1), 2)
                        : '—';
                ?>
                    <tr>
                        <td class="text-center">M<?=$i?></td>
                        <td class="text-center"><?=number_format($val,2)?></td>
                        <td class="text-center"><?=htmlspecialchars($item->unidad)?></td>
                        <td class="text-center"><?=number_format($cant,2)?></td>
                        <td class="text-center"><?=$desc?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-total">
                    <tr><td colspan="2" class="text-right">SUMA</td><td colspan="3" class="text-center"><?=number_format($suma_sec,2)?></td></tr>
                    <tr><td colspan="2" class="text-right">TOTAL SECCIÓN</td><td colspan="3" class="text-center"><?=number_format($suma_sec,2)?></td></tr>
                </tfoot>
            </table>
        </div>
    <?php endforeach; ?>
    </div>

    <div class="simbologia">
        <strong>SIMBOLOGÍA:</strong>
        <span><b>P</b> = PUERTA</span>
        <span><b>V</b> = VENTANA</span>
        <span><b>C.V.</b> = CUADRO DE VÁLVULAS</span>
        <span><b>G.E.</b> = GABINETE ELÉCTRICO</span>
        <span><b>H.M.</b> = HUECO EN MURO</span>
        <span><b>V.A.</b> = VANO DE ACCESO</span>
        <span><b>BOQ.H/V</b> = BOQUILLA HORIZONTAL / VERTICAL</span>
        <span><b>DESC.</b> = DESCUENTO POR DESPERDICIO</span>
    </div>

    <table style="margin-top:12px; width:50%;">
        <tr class="bg-total"><td>SUMA ESTA HOJA</td><td class="text-right"><?=number_format($m2_ejecutados,2)?></td></tr>
        <tr><td>CANTIDAD ACUMULADA ANTERIOR</td><td class="text-right">0.00</td></tr>
        <tr class="bg-total"><td><strong>TOTAL</strong></td><td class="text-right"><strong><?=number_format($m2_ejecutados,2)?></strong></td></tr>
    </table>

    <div class="firmas">
        <div class="firma-block">
            <div class="titulo">CLIENTE — <?=htmlspecialchars(strtoupper($obra->cliente ?: 'CONSTRUCTORA'))?></div>
            <div class="firma-line">FIRMA / NOMBRE</div>
        </div>
        <div class="firma-block">
            <div class="titulo"><?=htmlspecialchars(strtoupper($empresa->razon_social ?? 'CHISA'))?></div>
            <div class="firma-line">FIRMA / NOMBRE</div>
        </div>
    </div>

    <div class="pg-footer">
        <span><?=htmlspecialchars($direccionEmpresa)?><?php if($telefonoEmpresa): ?> &nbsp;|&nbsp; TEL. <?=htmlspecialchars($telefonoEmpresa)?><?php endif; ?></span>
        <span class="pg-num">Hoja 5 de <?=$total_pages?></span>
    </div>
</div>

</div><!-- #pdfDocument -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" crossorigin="anonymous"></script>
<script>
(function() {
    var btn = document.getElementById('btnDescargarPdf');
    var filename = 'Obra_<?=$obra->folio?>_Documentacion_Completa.pdf';

    function descargarPdf() {
        if (typeof html2pdf === 'undefined') { window.print(); return; }
        btn.disabled = true;
        btn.textContent = 'Generando PDF (5 hojas)...';
        html2pdf().set({
            margin: [8, 8, 8, 8],
            filename: filename,
            image: { type: 'jpeg', quality: 0.95 },
            html2canvas: { scale: 2, useCORS: true, scrollY: 0, logging: false },
            jsPDF: { unit: 'mm', format: 'letter', orientation: 'landscape' },
            pagebreak: { mode: ['css', 'legacy'] }
        }).from(document.getElementById('pdfDocument')).save()
          .catch(function() { window.print(); })
          .finally(function() {
              btn.disabled = false;
              btn.textContent = 'Descargar PDF Completo';
          });
    }

    btn.addEventListener('click', descargarPdf);
    if (new URLSearchParams(window.location.search).get('auto') === '1') {
        window.addEventListener('load', function() { setTimeout(descargarPdf, 800); });
    }
})();
</script>
</body>
</html>
