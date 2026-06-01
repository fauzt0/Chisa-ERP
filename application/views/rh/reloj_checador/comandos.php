<?php
/**
 * Vista de Cola de Comandos del Reloj Checador
 * 
 * Listado DataTables SSR de comandos encolados para los dispositivos,
 * permite encolar nuevos comandos manualmente.
 * 
 * @package ChisaERP
 * @subpackage Reloj
 */
?>
<div class="container-fluid p-0">

    <!-- Breadcrumb -->
    <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
    
    <!-- Título de la página -->
    <h1 class="h3 mb-3"><?php echo $headTitle; ?></h1>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Cola de Comandos</h5>
            <div class="mt-2 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalComando">
                    <i class="fas fa-plus"></i> Nuevo Comando
                </button>
                <button type="button" class="btn btn-outline-danger" id="btnVaciarTodosComandos">
                    <i class="fas fa-trash-alt"></i> Vaciar toda la cola
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-comandos" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Dispositivo</th>
                            <th>Comando</th>
                            <th>Estado</th>
                            <th>Intentos</th>
                            <th>Respuesta</th>
                            <th>Creado</th>
                            <th>Ejecutado</th>
                            <th>Creado por</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Nuevo Comando -->
<div class="modal fade" id="modalComando" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formComando" method="post">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                <div class="modal-header">
                    <h5 class="modal-title">Encolar Nuevo Comando</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dispositivo_sn" class="form-label">Dispositivo <span class="text-danger">*</span></label>
                        <select class="form-select" id="dispositivo_sn" name="dispositivo_sn" required>
                            <option value="">Seleccione un dispositivo...</option>
                            <?php if (!empty($response['dispositivos'])): ?>
                                <?php foreach ($response['dispositivos'] as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d->sn); ?>">
                                        <?php echo htmlspecialchars($d->alias ?: $d->sn); ?>
                                        <?php echo $d->ubicacion ? '— ' . htmlspecialchars($d->ubicacion) : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="comando" class="form-label">Comando <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="comando" name="comando" rows="4" required
                            placeholder="DATA USER PIN=1001	Name=Juan Perez	Pri=0	..."></textarea>
                        <small class="text-muted">
                            Use TAB real entre campos (no espacios). Recomendado: <strong>Sync Empleados RH</strong>. Editar = mismo PIN con datos nuevos.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Encolar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
