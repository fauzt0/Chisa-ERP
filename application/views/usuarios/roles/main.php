<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
<div class="row mb-3">
    <div class="col-md-6">
        <h2><i class="fas fa-user-tag"></i> Gestión de Roles</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?=base_url('usuarios/Roles/crear')?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Rol
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Permisos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($roles)): ?>
                    <?php foreach($roles as $role): ?>
                    <tr>
                        <td><strong><?=$role->nombre?></strong></td>
                        <td><?=$role->descripcion?></td>
                        <td>
                            <?php 
                                $perms = json_decode($role->permisos, true);
                                echo count($perms) . ' permisos asignados';
                            ?>
                        </td>
                        <td>
                            <div class="btn-acciones-crm">
                            <a href="<?=base_url('usuarios/Roles/editar/'.$role->id)?>" class="btn btn-sm btn-primary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="eliminarRol(<?=$role->id?>)" class="btn btn-sm btn-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">No hay roles definidos</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function eliminarRol(id) {
    if(confirm('¿Está seguro de eliminar este rol?')) {
        $.post('<?=base_url('usuarios/Roles/eliminar')?>/' + id, {
            '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
        }, function(resp) {
            resp = JSON.parse(resp);
            if(resp.success) {
                location.reload();
            } else {
                alert(resp.message || 'Error al eliminar el rol');
            }
        });
    }
}
</script>
