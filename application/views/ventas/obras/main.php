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
        <h2><i class="fas fa-hard-hat"></i> Gestión de Obras <button type="button" class="erp-btn-ayuda" data-erp-ayuda="obras_lista" title="Funciones de obras">?</button></h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?=base_url('ventas/ObrasVentas/crear')?>" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Obra</a>
    </div>
</div>
<?php $this->load->view('ventas/cartera/_panel', ['response' => $response]); ?>
<div class="card mb-2">
    <div class="card-body py-2">
        <div class="row g-2 text-center align-items-center">
            <div class="col-6 col-md-3 border-end">
                <div class="small text-muted">Total obras</div>
                <strong><?=$stats['total'] ?? 0?></strong>
            </div>
            <div class="col-6 col-md-3 border-end">
                <div class="small text-muted">En cotización</div>
                <strong><?=$stats['en_cotizacion'] ?? 0?></strong>
                <span class="badge bg-warning"><?=$stats['porcentaje_cotizacion'] ?? 0?>%</span>
            </div>
            <div class="col-6 col-md-3 border-end">
                <div class="small text-muted">Aprobadas</div>
                <strong><?=$stats['aprobadas'] ?? 0?></strong>
                <span class="badge bg-success"><?=$stats['porcentaje_aprobadas'] ?? 0?>%</span>
            </div>
            <div class="col-6 col-md-3">
                <div class="small text-muted">En ejecución</div>
                <strong><?=$stats['en_ejecucion'] ?? 0?></strong>
                <span class="badge bg-info"><?=$stats['porcentaje_ejecucion'] ?? 0?>%</span>
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
                            <th>Saldo</th>
                            <th>Pago</th>
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
                { data: 6 },
                { data: 7 },
                { data: 8, orderable: false }
            ],
            language: {
                url: '/assets/dist/js/i18n/datatables-es.json'
            },
            order: [[4, 'desc']]
        });
    } else {
        console.error('jQuery no está cargado');
    }
});
</script>