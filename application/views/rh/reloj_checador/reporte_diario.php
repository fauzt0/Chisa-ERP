<?php
/**
 * Vista de Reporte Diario de Asistencias
 * 
 * Listado DataTables SSR de checadas del día con filtros
 * por empleado, departamento y fecha.
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
            <h5 class="card-title mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form id="formFiltros" class="row g-3">
                <div class="col-md-3">
                    <label for="filtro_fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="filtro_fecha" name="fecha" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="col-md-3">
                    <label for="filtro_empleado" class="form-label">Empleado</label>
                    <select class="form-select" id="filtro_empleado" name="empleado_id">
                        <option value="">Todos los empleados</option>
                        <?php if (!empty($response['empleados'])): ?>
                            <?php foreach ($response['empleados'] as $emp): ?>
                                <option value="<?php echo $emp->id; ?>">
                                    <?php echo htmlspecialchars($emp->numero_empleado . ' — ' . $emp->nombre . ' ' . $emp->apellido_paterno); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtro_departamento" class="form-label">Departamento</label>
                    <select class="form-select" id="filtro_departamento" name="departamento_id">
                        <option value="">Todos los departamentos</option>
                        <?php if (!empty($response['departamentos'])): ?>
                            <?php foreach ($response['departamentos'] as $dep): ?>
                                <option value="<?php echo $dep->id; ?>">
                                    <?php echo htmlspecialchars($dep->nombre); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary me-2" onclick="filtrarTabla()">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="<?php echo base_url('rh/RelojChecador/exportar_diario_csv'); ?>" class="btn btn-success" id="btnExportarCSV">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Checadas del Día</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-asistencias-diario" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th># Empleado</th>
                            <th>Nombre</th>
                            <th>Puesto</th>
                            <th>Departamento</th>
                            <th>Hora</th>
                            <th>Método</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
