<?php
/**
 * Vista de Gestión de Dispositivos ZKTeco
 * 
 * CRUD de dispositivos biométricos: listado DataTables SSR,
 * creación, edición, eliminación y regeneración de tokens.
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
            <h5 class="card-title mb-0">Listado de Dispositivos</h5>
            <div class="mt-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDispositivo">
                    <i class="fas fa-plus"></i> Nuevo Dispositivo
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-dispositivos" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th>Alias</th>
                            <th>Ubicación</th>
                            <th>Última Conexión</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Nuevo/Editar Dispositivo -->
<div class="modal fade" id="modalDispositivo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formDispositivo" method="post">
                <input type="hidden" name="id" id="dispositivo_id" value="">
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalDispositivoTitle">Nuevo Dispositivo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="sn" class="form-label">Número de Serie (SN) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sn" name="sn" required maxlength="50"
                                placeholder="Ej: ZK1234567890">
                        </div>
                        <div class="col-md-6">
                            <label for="alias" class="form-label">Alias / Nombre</label>
                            <input type="text" class="form-control" id="alias" name="alias" maxlength="100"
                                placeholder="Ej: Reloj Oficinas Centrales">
                        </div>
                        <div class="col-md-6">
                            <label for="ubicacion" class="form-label">Ubicación</label>
                            <input type="text" class="form-control" id="ubicacion" name="ubicacion" maxlength="200"
                                placeholder="Ej: Planta Baja, Acceso Principal">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" checked>
                                <label class="form-check-label" for="activo">Dispositivo Activo</label>
                            </div>
                        </div>
                    </div>

                    <!-- Token (solo visible al crear o regenerar) -->
                    <div id="tokenContainer" class="mt-3" style="display:none;">
                        <label class="form-label">Token API</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="tokenDisplay" readonly>
                            <button type="button" class="btn btn-outline-secondary" onclick="copiarToken()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-muted">Guarda este token, no se mostrará de nuevo.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Detalle del Dispositivo -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Dispositivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detalleContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-warning" id="btnRegenerarToken" onclick="regenerarToken()">
                    <i class="fas fa-key"></i> Regenerar Token
                </button>
            </div>
        </div>
    </div>
</div>
