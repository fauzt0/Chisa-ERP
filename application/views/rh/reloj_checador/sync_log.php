<?php
/**
 * Vista de Historial de Sincronización del Reloj Checador
 * 
 * Bitácora DataTables SSR de todas las sincronizaciones realizadas
 * por los dispositivos biométricos.
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
            <h5 class="card-title mb-0">Bitácora de Sincronización</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-sync-log" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Dispositivo</th>
                            <th>Tipo</th>
                            <th>Resumen</th>
                            <th>Registros</th>
                            <th>IP Origen</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
