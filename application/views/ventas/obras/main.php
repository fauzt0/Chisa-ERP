<?php
$stats = $response['stats'] ?? [];
?>
<!-- Breadcrumb -->
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="#">CRM Ventas</a></li>
                <li class="breadcrumb-item active">Obras</li>
            </ol>
        </nav>
    </div>
</div>
<!-- Título -->
<div class="row mb-3">
    <div class="col-md-6">
        <h2><i class="fas fa-hard-hat"></i> Gestión de Obras</h2>
    </div>
</div>
<!-- Estadísticas -->
<!-- Estadísticas -->
<div class="row mb-4">
    <!-- Total Obras -->
    <div class="col-lg-6 col-xl-3 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5 class="card-title mb-0 mt-2">Total Obras</h5>
            </div>
            <div class="card-body my-0 pt-0">
                <div class="row d-flex align-items-center mb-3">
                    <div class="col-8">
                        <h3 class="d-flex align-items-center mb-0 fw-light">
                            <?=$stats['total'] ?? 0?>
                        </h3>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-hard-hat text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div class="progress progress-sm shadow-sm mb-1">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 100%"></div>
                </div>
                <small class="text-muted">Total registradas</small>
            </div>
        </div>
    </div>
    
    <!-- En Cotización -->
    <div class="col-lg-6 col-xl-3 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5 class="card-title mb-0 mt-2">En Cotización</h5>
            </div>
            <div class="card-body my-0 pt-0">
                <div class="row d-flex align-items-center mb-3">
                    <div class="col-8">
                        <h3 class="d-flex align-items-center mb-0 fw-light">
                            <?=$stats['en_cotizacion'] ?? 0?>
                        </h3>
                    </div>
                    <div class="col-4 text-end">
                        <span class="badge bg-warning"><?=$stats['porcentaje_cotizacion'] ?? 0?>%</span>
                    </div>
                </div>
                <div class="progress progress-sm shadow-sm mb-1">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?=$stats['porcentaje_cotizacion'] ?? 0?>%"></div>
                </div>
                <small class="text-muted">Pendientes de aprobación</small>
            </div>
        </div>
    </div>
    
    <!-- Aprobadas -->
    <div class="col-lg-6 col-xl-3 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5 class="card-title mb-0 mt-2">Aprobadas</h5>
            </div>
            <div class="card-body my-0 pt-0">
                <div class="row d-flex align-items-center mb-3">
                    <div class="col-8">
                        <h3 class="d-flex align-items-center mb-0 fw-light">
                            <?=$stats['aprobadas'] ?? 0?>
                        </h3>
                    </div>
                    <div class="col-4 text-end">
                        <span class="badge bg-success"><?=$stats['porcentaje_aprobadas'] ?? 0?>%</span>
                    </div>
                </div>
                <div class="progress progress-sm shadow-sm mb-1">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?=$stats['porcentaje_aprobadas'] ?? 0?>%"></div>
                </div>
                <small class="text-muted">Listas para iniciar</small>
            </div>
        </div>
    </div>
    
    <!-- En Ejecución -->
    <div class="col-lg-6 col-xl-3 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5 class="card-title mb-0 mt-2">En Ejecución</h5>
            </div>
            <div class="card-body my-0 pt-0">
                <div class="row d-flex align-items-center mb-3">
                    <div class="col-8">
                        <h3 class="d-flex align-items-center mb-0 fw-light">
                            <?=$stats['en_ejecucion'] ?? 0?>
                        </h3>
                    </div>
                    <div class="col-4 text-end">
                        <span class="badge bg-info"><?=$stats['porcentaje_ejecucion'] ?? 0?>%</span>
                    </div>
                </div>
                <div class="progress progress-sm shadow-sm mb-1">
                    <div class="progress-bar bg-info" role="progressbar" style="width: <?=$stats['porcentaje_ejecucion'] ?? 0?>%"></div>
                </div>
                <small class="text-muted">Obras activas</small>
            </div>
        </div>
    </div>
</div>
<!-- Tabla de Obras -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Lista de Obras</h5>
            </div>
            <div class="card-body">
                <table id="tabla_obras" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Nombre</th>
                            <th>Cliente</th>
                            <th>Estatus</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Cargado por DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined') {
        $('#tabla_obras').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?=base_url()?>ventas/ObrasVentas/lista_ajax',
                type: 'POST'
            },
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2 },
                { data: 3 },
                { data: 4 },
                { data: 5 },
                { data: 6, orderable: false }
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-MX.json'
            },
            order: [[4, 'desc']]
        });
    } else {
        console.error('jQuery no está cargado');
    }
});
</script>