<?php
if (!isset($pago) || !$pago) {
    echo '<div class="alert alert-danger">Recibo no encontrado</div>';
    return;
}
$folio_recibo = $pago->folio_recibo ?: $pago->id;
?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body m-sm-3 m-md-5">
                <div class="mb-4">
                    Recibí de <strong><?=htmlspecialchars($pago->cliente)?></strong>,
                    <br> la cantidad de <strong>$<?=number_format($pago->monto, 2)?></strong> (MXN) por concepto de pago de la obra <strong><?=htmlspecialchars($pago->obra_folio)?></strong>.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="text-muted">No. de Recibo</div>
                        <strong><?=htmlspecialchars($folio_recibo)?></strong>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-muted">Fecha de Pago</div>
                        <strong><?=date('d/m/Y', strtotime($pago->fecha_pago))?></strong>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="text-muted">Cliente</div>
                        <strong><?=htmlspecialchars($pago->cliente)?></strong>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-muted">Obra</div>
                        <strong><?=htmlspecialchars($pago->obra_folio)?></strong>
                        <p><?=htmlspecialchars($pago->obra_nombre)?></p>
                        <?php if (isset($pago->total) && $pago->total): ?>
                        <p class="text-muted">Total obra: $<?=number_format($pago->total, 2)?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th>Método de Pago</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Pago<?=$pago->referencia ? ' - Ref: ' . htmlspecialchars($pago->referencia) : ''?></td>
                            <td><?=htmlspecialchars($pago->metodo_pago)?></td>
                            <td class="text-end">$<?=number_format($pago->monto, 2)?></td>
                        </tr>
                        <?php if ($pago->notas): ?>
                        <tr>
                            <td colspan="3">
                                <small class="text-muted"><strong>Notas:</strong> <?=htmlspecialchars($pago->notas)?></small>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>&nbsp;</th>
                            <th>Total Pagado</th>
                            <th class="text-end">$<?=number_format($pago->monto, 2)?></th>
                        </tr>
                    </tbody>
                </table>

                <div class="text-center">
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> Imprimir Recibo
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn { display: none; }
    .sidebar, .navbar { display: none; }
}
</style>
