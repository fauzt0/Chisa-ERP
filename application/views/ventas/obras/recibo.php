<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body m-sm-3 m-md-5">
                <div class="mb-4">
                    Recibí de <strong><?=$obra->cliente?></strong>,
                    <br> la cantidad de <strong>$<?=number_format($pago->monto, 2)?></strong> (MXN) por concepto de pago de la obra <strong><?=$obra->folio?></strong>.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="text-muted">No. de Pago</div>
                        <strong><?=$pago->id?></strong>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-muted">Fecha de Pago</div>
                        <strong><?=date('F j, Y - h:i a', strtotime($pago->fecha_pago))?></strong>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="text-muted">Cliente</div>
                        <strong><?=$obra->cliente?></strong>
                        <p>
                            <?php if($obra->nombre_comercial): ?>
                                <?=$obra->nombre_comercial?><br>
                            <?php endif; ?>
                            <?php if($obra->rfc): ?>
                                RFC: <?=$obra->rfc?><br>
                            <?php endif; ?>
                            <?php if($obra->direccion): ?>
                                <?=$obra->direccion?><br>
                            <?php endif; ?>
                            <?php if(isset($obra->email) && $obra->email): ?>
                                <a href="mailto:<?=$obra->email?>"><?=$obra->email?></a>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="text-muted">Obra</div>
                        <strong><?=$obra->folio?></strong>
                        <p>
                            <?=$obra->nombre?><br>
                            <?php if($obra->area_total): ?>
                                Área: <?=number_format($obra->area_total, 2)?> m²<br>
                            <?php endif; ?>
                            Estatus: <?=$obra->estatus?>
                        </p>
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
                            <td>Pago <?=$pago->referencia ? '- Ref: '.$pago->referencia : ''?></td>
                            <td><?=$pago->metodo_pago?></td>
                            <td class="text-end">$<?=number_format($pago->monto, 2)?></td>
                        </tr>
                        <?php if($pago->notas): ?>
                        <tr>
                            <td colspan="3">
                                <small class="text-muted"><strong>Notas:</strong> <?=$pago->notas?></small>
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
                    <?php if($pago->notas): ?>
                    <p class="text-sm">
                        <strong>Nota:</strong> <?=$pago->notas?>
                    </p>
                    <?php endif; ?>

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