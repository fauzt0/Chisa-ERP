<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Sincronización forzada: empleados activos RH → cola de comandos ZKTeco
 */
$preview = $response['preview'] ?? [];
$dispositivos = $response['dispositivos'] ?? [];
$campos_ok = !empty($response['campos_reloj_ok']);
$pin_admin = (int)($response['pin_admin'] ?? 1);
?>
<div class="container-fluid p-0">

    <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

    <h1 class="h3 mb-3"><?php echo htmlspecialchars($headTitle); ?></h1>

    <?php if (!$campos_ok): ?>
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2" id="alertMigracionPendiente">
        <div>
            <strong>Migración pendiente.</strong>
            Faltan las columnas <code>reloj_pin</code>, <code>reloj_nombre_meta</code> y <code>reloj_sync_at</code> en <code>empleados</code>.
            Puede aplicarlas desde aquí o ejecutar <code>database/reloj_sync_empleados_rh.sql</code> en phpMyAdmin.
        </div>
        <button type="button" class="btn btn-warning" id="btnAplicarMigracion">
            <i class="fas fa-database"></i> Aplicar migración ahora
        </button>
    </div>
    <?php else: ?>
    <div class="alert alert-success d-none" id="alertMigracionOk">
        Estructura de base de datos lista para sincronizar empleados al reloj.
    </div>
    <?php endif; ?>

    <div class="alert alert-warning">
        <strong>Importante:</strong> entrar a esta pantalla solo muestra la vista previa.
        Debe elegir dispositivo y pulsar <strong>「Encolar sincronización forzada」</strong>.
        El <strong>proxy</strong> debe consultar <code>comandos_pendientes</code> repetidamente (1 comando por consulta) y reportar cada <code>comando_resultado</code> hasta vaciar la cola
        (revise en <a href="<?php echo base_url('rh/RelojChecador/comandos'); ?>">Comandos</a>).
    </div>

    <div class="alert alert-info">
        <ul class="mb-0">
            <li>Un comando <code>DATA USER</code> por empleado activo, con <strong>TAB real</strong> (⇥) entre campos — igual que el proxy de planta.</li>
            <li><strong>PIN</strong> corto en el reloj: <strong>2, 3, 4 …</strong> (hasta 200 activos por sync; el 1 queda para el admin del dispositivo). Para checar usan ese PIN, no el folio largo del ERP.</li>
            <li><strong>Name</strong> = nombre completo en el reloj (ej. Juan Perez). <strong>Passwd</strong> vacío por defecto.</li>
            <li>PIN <strong><?php echo $pin_admin; ?></strong> (admin del reloj) no se modifica desde aquí.</li>
        </ul>
        <?php if (!empty($preview['ejemplo_comando'])): ?>
        <p class="mb-0 mt-2"><small>Ejemplo: <code><?php echo htmlspecialchars($preview['ejemplo_comando']); ?></code></small></p>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Parámetros</h5>
                </div>
                <div class="card-body">
                    <form id="formSyncRh">
                        <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

                        <div class="mb-3">
                            <label for="dispositivo_sn" class="form-label">Dispositivo <span class="text-danger">*</span></label>
                            <select class="form-select" id="dispositivo_sn" name="dispositivo_sn" required <?php echo $campos_ok ? '' : 'disabled'; ?>>
                                <option value="">Seleccione...</option>
                                <?php foreach ($dispositivos as $d): ?>
                                <option value="<?php echo htmlspecialchars($d->sn); ?>">
                                    <?php echo htmlspecialchars($d->alias ?: $d->sn); ?>
                                    <?php echo $d->ubicacion ? ' — ' . htmlspecialchars($d->ubicacion) : ''; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="button" class="btn btn-outline-danger w-100 mb-2" id="btnVaciarCola" <?php echo $campos_ok ? '' : 'disabled'; ?>>
                            <i class="fas fa-trash-alt"></i> Vaciar cola del dispositivo
                        </button>
                        <button type="button" class="btn btn-outline-warning w-100 mb-2" id="btnReencolarCola" <?php echo $campos_ok ? '' : 'disabled'; ?>>
                            <i class="fas fa-redo"></i> Reencolar «Enviado» a pendiente
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100 mb-2" id="btnRefrescarPreview" <?php echo $campos_ok ? '' : 'disabled'; ?>>
                            <i class="fas fa-sync"></i> Actualizar vista previa
                        </button>
                        <button type="submit" class="btn btn-danger w-100" id="btnEjecutarSync" <?php echo $campos_ok ? '' : 'disabled'; ?>>
                            <i class="fas fa-bolt"></i> Encolar sincronización forzada
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="text-muted">Resumen</h6>
                    <p class="mb-1">Empleados activos: <strong id="statEmpleados"><?php echo (int)($preview['total_empleados'] ?? 0); ?></strong></p>
                    <p class="mb-1">Encolables: <strong id="statEncolables"><?php echo (int)($preview['total_encolables'] ?? 0); ?></strong></p>
                    <p class="mb-0">Omitidos: <strong id="statOmitidos"><?php echo (int)($preview['total_omitidos'] ?? 0); ?></strong></p>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Vista previa de asignación</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-preview-sync" class="table table-sm table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID RH</th>
                                    <th>No. empleado</th>
                                    <th>Nombre</th>
                                    <th>PIN reloj</th>
                                    <th>Name (reloj)</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($preview['empleados'])): ?>
                                    <?php foreach ($preview['empleados'] as $f): ?>
                                    <tr class="<?php echo !empty($f['omitido']) ? 'table-warning' : ''; ?>">
                                        <td><?php echo (int)$f['empleado_id']; ?></td>
                                        <td><?php echo htmlspecialchars($f['numero_empleado'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($f['nombre_completo']); ?></td>
                                        <td><?php echo $f['pin_asignado'] !== null ? '<code>' . (int)$f['pin_asignado'] . '</code>' : '—'; ?></td>
                                        <td><code><?php echo htmlspecialchars($f['reloj_nombre_meta'] ?? ''); ?></code></td>
                                        <td><?php echo !empty($f['omitido']) ? htmlspecialchars($f['motivo_omitido'] ?? 'Omitido') : 'OK'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
