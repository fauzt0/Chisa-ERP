<?php
/**
 * Vista de Reporte Diario — resumen por empleado
 *
 * @package ChisaERP
 * @subpackage Reloj
 */
?>
<div class="container-fluid p-0">

    <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="h3 mb-0"><?php echo $headTitle; ?></h1>
        <span class="badge bg-light text-dark border">
            <i class="fas fa-info-circle text-success"></i> Una fila por empleado · clic para ver detalle
        </span>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Filtros</h5>
        </div>
        <div class="card-body">
            <form id="formFiltros" class="row g-3">
                <div class="col-md-2">
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
                <div class="col-md-2">
                    <label for="filtro_departamento" class="form-label">Departamento</label>
                    <select class="form-select" id="filtro_departamento" name="departamento_id">
                        <option value="">Todos</option>
                        <?php if (!empty($response['departamentos'])): ?>
                            <?php foreach ($response['departamentos'] as $dep): ?>
                                <option value="<?php echo $dep->id; ?>">
                                    <?php echo htmlspecialchars($dep->nombre); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filtro_estado" class="form-label">Estado</label>
                    <select class="form-select" id="filtro_estado" name="estado">
                        <option value="">Todos</option>
                        <option value="completo">Asistencia completa</option>
                        <option value="retardo">Con retardo</option>
                        <option value="falta">Falta (sin checada)</option>
                        <option value="incompleto">Incompleto / salida temprana</option>
                        <option value="sin_horario">Sin horario asignado</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="button" class="btn btn-success" onclick="filtrarTabla()">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="<?php echo base_url('rh/RelojChecador/exportar_diario_csv'); ?>" class="btn btn-outline-success" id="btnExportarCSV">
                        <i class="fas fa-file-csv"></i> CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Resumen por Empleado</h5>
            <small class="text-muted">Interpretación entrada / comida / salida</small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabla-asistencias-diario" class="table table-striped table-hover w-100">
                    <thead>
                        <tr>
                            <th># Empleado</th>
                            <th>Nombre</th>
                            <th>Departamento</th>
                            <th>Entrada</th>
                            <th>Salida comida</th>
                            <th>Entrada comida</th>
                            <th>Salida</th>
                            <th>Estado</th>
                            <th>Retardo</th>
                            <th>Horas</th>
                            <th style="display:none;">empleado_id</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal detalle del día -->
<div class="modal fade" id="modalDetalleDiario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);">
                <div>
                    <h5 class="modal-title mb-0"><i class="fas fa-fingerprint"></i> Detalle del día</h5>
                    <small id="modal-detalle-subtitulo" class="opacity-75"></small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-detalle-contenido">
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
