<div class="row">
    <div class="col-12 text-end mb-3">
        <a href="<?= base_url('rh/RecursosHumanos/crear_plantilla') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Plantilla
        </a>
    </div>
    
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Plantillas de Contrato</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="tabla-plantillas">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Última Edición</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($plantillas)): ?>
                                <?php foreach($plantillas as $p): ?>
                                    <tr>
                                        <td><?= $p->nombre ?></td>
                                        <td><?= $p->descripcion ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($p->fecha_edicion)) ?></td>
                                        <td>
                                            <a href="<?= base_url('rh/RecursosHumanos/editar_plantilla/'.$p->id) ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-sm btn-danger" onclick="eliminarPlantilla(<?= $p->id ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay plantillas registradas</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function eliminarPlantilla(id){
    if(confirm('¿Seguro que deseas eliminar esta plantilla?')){
        $.post('<?= base_url('rh/RecursosHumanos/eliminar_plantilla/') ?>' + id, function(response){
            var resp = JSON.parse(response);
            if(resp.success){
                notifyShow(resp.message, 'success');
                setTimeout(function(){ location.reload(); }, 1000);
            } else {
                notifyShow(resp.message, 'danger');
            }
        });
    }
}

$(document).ready(function() {
    $('#tabla-plantillas').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });
});
</script>
