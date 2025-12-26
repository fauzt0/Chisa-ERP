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
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Obras</h5>
                <h2><?=$stats['total'] ?? 0?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">En Cotización</h5>
                <h2><?=$stats['en_cotizacion'] ?? 0?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Aprobadas</h5>
                <h2><?=$stats['aprobadas'] ?? 0?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">En Ejecución</h5>
                <h2><?=$stats['en_ejecucion'] ?? 0?></h2>
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