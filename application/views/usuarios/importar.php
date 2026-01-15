<div class="row">
    <div class="col-12 col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Importación Masiva de Usuarios</h5>
                <h6 class="card-subtitle text-muted">Utilice esta herramienta para cargar múltiples usuarios al sistema mediante un archivo de Excel (.xlsx).</h6>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h4>Instrucciones:</h4>
                        <ol>
                            <li>Descargue la plantilla de Excel haciendo clic en el botón de la derecha.</li>
                            <li>Llene la plantilla con la información de los usuarios (Nombre, Apellidos, Email, Password, Departamento).</li>
                            <li><strong>Nota:</strong> El Email (Username) debe ser único. Si el email ya existe, el usuario será omitido.</li>
                            <li>Asegúrese de que todos los campos obligatorios estén llenos por cada fila.</li>
                            <li>Guarde el archivo y súbalo en el formulario a continuación.</li>
                        </ol>
                    </div>
                    <div class="col-md-6 text-center d-flex align-items-center justify-content-center">
                        <a href="<?= base_url('usuarios/GestionUsuarios/descargar_plantilla') ?>" class="btn btn-lg btn-success">
                            <i class="fas fa-file-excel me-2"></i> Descargar Plantilla (.xlsx)
                        </a>
                    </div>
                </div>

                <hr>

                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('usuarios/GestionUsuarios/procesar_importacion') ?>" method="post" enctype="multipart/form-data" class="mt-4">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="archivo_excel" class="form-label">Seleccione el archivo de Excel</label>
                                <input class="form-control form-control-lg" type="file" id="archivo_excel" name="archivo_excel" accept=".xlsx, .xls" required>
                                <div class="form-text">Tamaño máximo: 2MB. Formatos permitidos: .xlsx, .xls</div>
                            </div>
                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-upload me-2"></i> Iniciar Importación
                                </button>
                                <a href="<?= base_url('usuarios/GestionUsuarios') ?>" class="btn btn-link">Volver al Listado</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
