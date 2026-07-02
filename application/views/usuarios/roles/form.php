<div class="container-fluid p-0">
  <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3><?= $is_edit ? 'Editar Rol' : 'Nuevo Rol' ?></h3>
            </div>
            <div class="card-body">
                <form action="<?=base_url('usuarios/Roles/guardar')?>" method="post">
                    <input type="hidden" name="id" value="<?= $is_edit ? $role->id : '' ?>">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre del Rol</label>
                            <input type="text" class="form-control" name="nombre" value="<?= $is_edit ? $role->nombre : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Descripción</label>
                            <input type="text" class="form-control" name="descripcion" value="<?= $is_edit ? $role->descripcion : '' ?>">
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row mb-3">
                        <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                            <h4>Permisos</h4>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="toggleAll">
                                <i class="fas fa-check-square"></i> Seleccionar Todos
                            </button>
                        </div>
                        
                        <?php foreach($permissions as $module => $perms): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 fw-bold"><?=$module?></h6>
                                </div>
                                <div class="card-body p-2">
                                    <?php foreach($perms as $key => $label): ?>
                                    <div class="form-check">
                                        <input class="form-check-input perm-check" type="checkbox" name="<?=$key?>" value="1" id="<?=$key?>"
                                            <?= ($is_edit && isset($selected_permissions[$key])) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="<?=$key?>">
                                            <?=$label?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="text-end">
                        <a href="<?=base_url('usuarios/Roles')?>" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Rol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('toggleAll').addEventListener('click', function() {
    var checkboxes = document.querySelectorAll('.perm-check');
    var allChecked = Array.from(checkboxes).every(c => c.checked);
    
    checkboxes.forEach(function(cb) {
        cb.checked = !allChecked;
    });
});
</script>
