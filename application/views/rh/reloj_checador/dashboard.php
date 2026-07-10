<?php
/**
 * Dashboard del Módulo de Reloj Checador
 *
 * @package ChisaERP
 * @subpackage Reloj
 */
$stats = $response['stats'] ?? [];
$permisos = $response['permisos'] ?? [];
$checadas7 = $stats['checadas_7_dias'] ?? [];
?>
<div class="container-fluid p-0">

    <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h3 mb-0"><?php echo $headTitle; ?></h1>
        <div class="d-flex flex-wrap gap-2">
            <?php if (!empty($permisos['reportes'])): ?>
                <a href="<?php echo base_url('rh/RelojChecador/reporte_diario'); ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-calendar-day"></i> Reporte diario
                </a>
            <?php endif; ?>
            <?php if (!empty($permisos['gestionar'])): ?>
                <a href="<?php echo base_url('rh/RelojChecador/dispositivos'); ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-clock"></i> Dispositivos
                </a>
            <?php endif; ?>
            <?php if (!empty($permisos['sync_empleados'])): ?>
                <a href="<?php echo base_url('rh/RelojChecador/sync_empleados_rh'); ?>" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-sync"></i> Sync empleados
                </a>
            <?php endif; ?>
            <button type="button" class="btn btn-light btn-sm" id="btn-refresh-dashboard" title="Actualizar">
                <i class="fas fa-sync-alt"></i>
            </button>
            <div class="form-check form-switch ms-1 mb-0 d-flex align-items-center">
                <input class="form-check-input" type="checkbox" id="toggle-auto-refresh" title="Auto-actualizar cada 30 segundos">
                <label class="form-check-label small ms-1" for="toggle-auto-refresh">Auto 30s</label>
            </div>
        </div>
    </div>

    <?php if (!empty($validate) && is_array($validate)): ?>
        <?php foreach ($validate as $alert): ?>
            <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                <div class="alert-message"><?php echo $alert['message']; ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Cards principales -->
    <div class="row">
        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill border-success">
                <div class="card-header">
                    <h5 class="card-title mb-0 mt-2">Resumen del Día</h5>
                </div>
                <div class="card-body my-0 pt-0">
                    <div class="row text-center g-2 mb-2">
                        <div class="col-4">
                            <div class="p-2 rounded bg-success bg-opacity-10">
                                <div class="h4 mb-0 text-success fw-light" id="stat-presentes-hoy"><?php echo (int)($stats['presentes_hoy'] ?? 0); ?></div>
                                <small class="text-muted">Presentes</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded bg-secondary bg-opacity-10">
                                <div class="h4 mb-0 text-secondary fw-light" id="stat-ausentes-hoy"><?php echo (int)($stats['ausentes_hoy'] ?? 0); ?></div>
                                <small class="text-muted">Ausentes</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded bg-warning bg-opacity-10">
                                <div class="h4 mb-0 text-warning fw-light" id="stat-retardos-resumen"><?php echo (int)($stats['retardos_hoy'] ?? 0); ?></div>
                                <small class="text-muted">Retardos</small>
                            </div>
                        </div>
                    </div>
                    <div class="progress progress-sm shadow-sm mb-1">
                        <?php
                        $total_dia = max(1, (int)($stats['total_esperados_hoy'] ?? 0));
                        $pct_presentes = min(100, round(((int)($stats['presentes_hoy'] ?? 0) / $total_dia) * 100));
                        ?>
                        <div class="progress-bar bg-success" id="bar-presentes-hoy" style="width: <?php echo $pct_presentes; ?>%"></div>
                    </div>
                    <small class="text-muted">
                        Checadas hoy: <strong id="stat-asistencias-hoy"><?php echo (int)($stats['asistencias_hoy'] ?? 0); ?></strong>
                        · Empleados: <strong id="stat-empleados-hoy"><?php echo (int)($stats['empleados_checaron_hoy'] ?? 0); ?></strong>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill border-warning">
                <div class="card-header">
                    <h5 class="card-title mb-0 mt-2">Retardos Hoy</h5>
                </div>
                <div class="card-body my-0 pt-0">
                    <div class="row d-flex align-items-center mb-3">
                        <div class="col-8">
                            <h3 class="d-flex align-items-center mb-0 fw-light text-warning" id="stat-retardos-hoy">
                                <?php echo (int)($stats['retardos_hoy'] ?? 0); ?>
                            </h3>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="progress progress-sm shadow-sm mb-1">
                        <?php
                        $pct_ret = ($stats['empleados_checaron_hoy'] ?? 0) > 0
                            ? min(100, round((($stats['retardos_hoy'] ?? 0) / $stats['empleados_checaron_hoy']) * 100))
                            : 0;
                        ?>
                        <div class="progress-bar bg-warning" id="bar-retardos-hoy" style="width: <?php echo $pct_ret; ?>%"></div>
                    </div>
                    <small class="text-muted">Empleados con retardo registrado</small>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill border-danger">
                <div class="card-header">
                    <h5 class="card-title mb-0 mt-2">Sin Salida</h5>
                </div>
                <div class="card-body my-0 pt-0">
                    <div class="row d-flex align-items-center mb-3">
                        <div class="col-8">
                            <h3 class="d-flex align-items-center mb-0 fw-light text-danger" id="stat-sin-salida">
                                <?php echo (int)($stats['sin_salida_hoy'] ?? 0); ?>
                            </h3>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-sign-out-alt text-danger" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="progress progress-sm shadow-sm mb-1">
                        <div class="progress-bar bg-danger" style="width: <?php echo min(100, ($stats['sin_salida_hoy'] ?? 0) * 10); ?>%"></div>
                    </div>
                    <small class="text-muted">Checaron pero sin salida completa</small>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0 mt-2">Dispositivos</h5>
                </div>
                <div class="card-body my-0 pt-0">
                    <div class="row d-flex align-items-center mb-3">
                        <div class="col-8">
                            <h3 class="d-flex align-items-center mb-0 fw-light" id="stat-dispositivos">
                                <?php echo (int)($stats['dispositivos_activos'] ?? 0); ?>
                            </h3>
                        </div>
                        <div class="col-4 text-end">
                            <span class="badge bg-success" id="stat-dispositivos-online"><?php echo (int)($response['dispositivos_online'] ?? 0); ?> en línea</span>
                        </div>
                    </div>
                    <div class="progress progress-sm shadow-sm mb-1">
                        <div class="progress-bar bg-info" style="width: 100%"></div>
                    </div>
                    <small class="text-muted">
                        Comandos pendientes: <strong id="stat-comandos"><?php echo (int)($stats['comandos_pendientes'] ?? 0); ?></strong>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Segunda fila: gráfica + alertas -->
    <div class="row">
        <div class="col-lg-8 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">Checadas — Últimos 7 días</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartChecadas7d" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">Alertas</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-user-clock text-warning"></i> Retardos hoy</span>
                            <span class="badge bg-warning text-dark" id="badge-retardos"><?php echo (int)($stats['retardos_hoy'] ?? 0); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-door-open text-danger"></i> Sin checada salida</span>
                            <span class="badge bg-danger" id="badge-sin-salida"><?php echo (int)($stats['sin_salida_hoy'] ?? 0); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-tasks text-info"></i> Comandos pendientes</span>
                            <span class="badge bg-info" id="badge-comandos"><?php echo (int)($stats['comandos_pendientes'] ?? 0); ?></span>
                        </li>
                        <?php if ($stats['empleados_sin_pin'] !== null): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><i class="fas fa-id-badge text-secondary"></i> Sin PIN reloj</span>
                            <span class="badge bg-secondary" id="badge-sin-pin"><?php echo (int)$stats['empleados_sin_pin']; ?></span>
                        </li>
                        <?php endif; ?>
                        <li class="list-group-item px-0">
                            <small class="text-muted">
                                Última sync:
                                <strong id="stat-ultima-sync">
                                    <?php echo !empty($stats['ultima_sincronizacion'])
                                        ? date('d/m/Y H:i', strtotime($stats['ultima_sincronizacion']))
                                        : 'Nunca'; ?>
                                </strong>
                            </small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Últimas checadas -->
        <div class="col-lg-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Últimas Checadas</h5>
                    <?php if (!empty($permisos['reportes'])): ?>
                        <a href="<?php echo base_url('rh/RelojChecador/reporte_diario'); ?>" class="btn btn-sm btn-outline-success">Ver reporte</a>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" id="tabla-ultimas-checadas">
                            <thead class="table-light">
                                <tr>
                                    <th>PIN</th>
                                    <th>Empleado</th>
                                    <th>Hora</th>
                                    <th>Método</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($response['ultimas_checadas'])): ?>
                                    <?php foreach ($response['ultimas_checadas'] as $c): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($c->numero_empleado ?? $c->usuario_id); ?></code></td>
                                            <td><?php echo htmlspecialchars($c->empleado_nombre ?? ('PIN ' . $c->usuario_id)); ?></td>
                                            <td><?php echo date('H:i:s', strtotime($c->fecha_hora)); ?></td>
                                            <td><?php echo $c->metodo_html ?? ''; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-muted text-center py-3">Sin checadas recientes</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dispositivos con estado online -->
        <div class="col-lg-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">Estado de Dispositivos</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($response['dispositivos'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" id="tabla-dispositivos-status">
                                <thead class="table-light">
                                    <tr>
                                        <th>SN</th>
                                        <th>Alias</th>
                                        <th>Última conexión</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($response['dispositivos'] as $d): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($d->sn); ?></code></td>
                                            <td><?php echo htmlspecialchars($d->alias ?? '—'); ?></td>
                                            <td>
                                                <?php if ($d->ultima_conexion): ?>
                                                    <?php echo date('d/m/Y H:i', strtotime($d->ultima_conexion)); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Nunca</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($d->online)): ?>
                                                    <span class="badge bg-success"><i class="fas fa-circle" style="font-size:0.5rem;"></i> En línea</span>
                                                <?php elseif ($d->ultima_conexion): ?>
                                                    <span class="badge bg-secondary">Desconectado</span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted border">Sin datos</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted p-3 mb-0">No hay dispositivos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync log -->
    <div class="row">
        <div class="col-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Últimas Sincronizaciones</h5>
                    <a href="<?php echo base_url('rh/RelojChecador/sync_log'); ?>" class="btn btn-sm btn-light">Ver bitácora</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($response['ultimo_sync'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Dispositivo</th>
                                        <th>Tipo</th>
                                        <th>Resumen</th>
                                        <th>Registros</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($response['ultimo_sync'] as $s): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($s->dispositivo_sn ?? '—'); ?></code></td>
                                            <td>
                                                <?php
                                                $badges = [
                                                    'asistencias' => 'bg-success',
                                                    'comandos'    => 'bg-info',
                                                    'resultado'   => 'bg-primary',
                                                    'conexion'    => 'bg-secondary',
                                                    'error'       => 'bg-danger',
                                                ];
                                                $cls = $badges[$s->tipo] ?? 'bg-dark';
                                                echo '<span class="badge ' . $cls . '">' . htmlspecialchars($s->tipo) . '</span>';
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($s->payload_resumen ?? '—'); ?></td>
                                            <td><?php echo (int)($s->registros_afectados ?? 0); ?></td>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($s->fecha)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No hay sincronizaciones registradas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="dashboard-checadas-data"><?php echo json_encode($checadas7); ?></script>
