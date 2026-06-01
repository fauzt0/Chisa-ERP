<?php
/**
 * Vista de Reporte Mensual de Asistencias
 * 
 * Resumen DataTables SSR por empleado del mes seleccionado,
 * mostrando días trabajados vs días laborales y porcentaje.
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
                <div class="col-md-2">
                    <label for="filtro_mes" class="form-label">Mes</label>
                    <select class="form-select" id="filtro_mes" name="mes">
                        <?php foreach ($response['meses'] as $num => $nombre): ?>
                            <option value="<?php echo $num; ?>" <?php echo $num === date('m') ? 'selected' : ''; ?>>
                                <?php echo $nombre; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtro_anio" class="form-label">Año</label>
                    <select class="form-select" id="filtro_anio" name="anio">
                        <?php for ($a = (int)$response['anio_actual'] - 2; $a <= (int)$response['anio_actual'] + 1; $a++): ?>
                            <option value="<?php echo $a; ?>" <?php echo $a === (int)$response['anio_actual'] ? 'selected' : ''; ?>>
                                <?php echo $a; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
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
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary" onclick="filtrarTabla()">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Resumen Mensual de Asistencias</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-asistencias-mensual" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th># Empleado</th>
                            <th>Nombre</th>
                            <th>Puesto</th>
                            <th>Departamento</th>
                            <th>Días Trabajados</th>
                            <th>Días Laborales</th>
                            <th>% Asistencia</th>
                            <th>Primera Checada</th>
                            <th>Última Checada</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
