<div class="row">
    <div class="col-12 col-xl-12">
        <div class="card h-100">
            <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                <div class="text-center">
                    <div class="display-1 text-danger mb-4">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h1 class="h2 mb-3">Acceso Denegado</h1>
                    <p class="lead mb-4">
                        Lo sentimos, pero no tienes los privilegios necesarios para acceder a este módulo.
                    </p>
                    <div class="border-top pt-4 w-100">
                        <p class="text-muted mb-4">
                            Si cree que esto es un error, por favor contacte al administrador del sistema para solicitar acceso al módulo: 
                            <?php if(isset($modulo)): ?>
                                <strong><?php echo $modulo; ?></strong>
                            <?php else: ?>
                                <strong>No especificado</strong>
                            <?php endif; ?>
                        </p>
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <a href="<?= base_url('dashboard') ?>" class="btn btn-primary btn-lg px-4 gap-3">
                                <i class="fas fa-home me-2"></i> Ir al Dashboard
                            </a>
                            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="fas fa-arrow-left me-2"></i> Regresar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
