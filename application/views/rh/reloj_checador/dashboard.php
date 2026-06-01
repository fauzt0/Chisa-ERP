<?php
/**
 * Vista Dashboard del Módulo de Reloj Checador
 * 
 * Muestra estadísticas generales del módulo: dispositivos activos,
 * checadas del día/mes, comandos pendientes, y últimos syncs.
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

    <!-- Alertas -->
    <?php if (!empty($validate) && is_array($validate)): ?>
        <?php foreach ($validate as $alert): ?>
            <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                <div class="alert-message">
                    <?php echo $alert['message']; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Cards de estadísticas -->
    <div class="row">
        <!-- Asistencias Hoy -->
        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0 mt-2">Asistencias Hoy</h5>
                </div>
                <div class="card-body my-0 pt-0">
                    <div class="row d-flex align-items-center mb-3">
                        <div class="col-8">
                            <h3 class="d-flex align-items-center mb-0 fw-light">
                                <?php echo $response['stats']['asistencias_hoy'] ?? 0; ?>
                            </h3>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-fingerprint fa-2x text-primary"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        Empleados que checaron: <strong><?php echo $response['stats']['empleados_checaron_hoy'] ?? 0; ?></strong>
                    </small>
                </div>
            </div>
        </div>

        <!-- Asistencias del Mes -->
        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0 mt-2">Asistencias del Mes</h5>
                </div>
                <div class="card-body my-0 pt-0">
                    <div class="row d-flex align-items-center mb-3">
                        <div class="col-8">
                            <h3 class="d-flex align-items-center mb-0 fw-light">
                                <?php echo $response['stats']['asistencias_mes'] ?? 0; ?>
                            </h3>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-calendar-alt fa-2x text-success"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        Total de checadas registradas
                    </small>
                </div>
            </div>
        </div>

        <!-- Dispositivos Activos -->
        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0 mt-2">Dispositivos</h5>
                </div>
                <div class="card-body my-0 pt-0">
                    <div class="row d-flex align-items-center mb-3">
                        <div class="col-8">
                            <h3 class="d-flex align-items-center mb-0 fw-light">
                                <?php echo $response['stats']['dispositivos_activos'] ?? 0; ?>
                            </h3>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-clock fa-2x text-info"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        Relojes biométricos activos
                    </small>
                </div>
            </div>
        </div>

        <!-- Comandos Pendientes -->
        <div class="col-lg-6 col-xl-3 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0 mt-2">Comandos Pendientes</h5>
                </div>
                <div class="card-body my-0 pt-0">
                    <div class="row d-flex align-items-center mb-3">
                        <div class="col-8">
                            <h3 class="d-flex align-items-center mb-0 fw-light">
                                <?php echo $response['stats']['comandos_pendientes'] ?? 0; ?>
                            </h3>
                        </div>
                        <div class="col-4 text-end">
                            <i class="fas fa-tasks fa-2x text-warning"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        Última sincronización: <strong><?php echo $response['stats']['ultima_sincronizacion'] ? date('d/m/Y H:i', strtotime($response['stats']['ultima_sincronizacion'])) : 'Nunca'; ?></strong>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Dispositivos y Último Sync -->
    <div class="row">
        <!-- Lista de Dispositivos -->
        <div class="col-12 col-lg-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dispositivos Registrados</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($response['dispositivos'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>SN</th>
                                        <th>Alias</th>
                                        <th>Ubicación</th>
                                        <th>Última Conexión</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($response['dispositivos'] as $d): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($d->sn); ?></code></td>
                                            <td><?php echo htmlspecialchars($d->alias ?? '—'); ?></td>
                                            <td><?php echo htmlspecialchars($d->ubicacion ?? '—'); ?></td>
                                            <td>
                                                <?php if ($d->ultima_conexion): ?>
                                                    <span title="<?php echo $d->ultima_conexion; ?>">
                                                        <?php echo date('d/m/Y H:i', strtotime($d->ultima_conexion)); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Nunca</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($d->activo): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No hay dispositivos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Últimas Sincronizaciones -->
        <div class="col-12 col-lg-6 d-flex">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">Últimas Sincronizaciones</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($response['ultimo_sync'])): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Resumen</th>
                                        <th>Registros</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($response['ultimo_sync'] as $s): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                    switch ($s->tipo) {
                                                        case 'asistencias':
                                                            $badge_tipo = '<span class="badge bg-success">Asistencias</span>';
                                                            break;
                                                        case 'comandos':
                                                            $badge_tipo = '<span class="badge bg-info">Comandos</span>';
                                                            break;
                                                        case 'resultado':
                                                            $badge_tipo = '<span class="badge bg-primary">Resultado</span>';
                                                            break;
                                                        case 'conexion':
                                                            $badge_tipo = '<span class="badge bg-secondary">Conexión</span>';
                                                            break;
                                                        case 'error':
                                                            $badge_tipo = '<span class="badge bg-danger">Error</span>';
                                                            break;
                                                        default:
                                                            $badge_tipo = '<span class="badge bg-dark">' . $s->tipo . '</span>';
                                                            break;
                                                    }
                                                    echo $badge_tipo;
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
