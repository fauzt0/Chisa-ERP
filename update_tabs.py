import re

with open("application/views/produccion/productos/main.php", "r") as f:
    content = f.read()

# Find the insertion point before "<!-- Filtros -->"
tabs_html = """
<!-- Tabs Principales -->
<ul class="nav nav-tabs mb-4" id="mainTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="tab-lista-productos" data-bs-toggle="tab" data-bs-target="#pane-lista-productos" type="button" role="tab"><i class="fas fa-boxes"></i> Catálogo de Productos</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="tab-calculadora-excel" data-bs-toggle="tab" data-bs-target="#pane-calculadora-excel" type="button" role="tab"><i class="fas fa-file-excel text-success"></i> Simulador de Producción</button>
  </li>
</ul>

<div class="tab-content" id="mainTabsContent">
  <!-- Pane: Lista de Productos -->
  <div class="tab-pane fade show active" id="pane-lista-productos" role="tabpanel">

"""

calc_html = """
  </div>

  <!-- Pane: Calculadora de Producción -->
  <div class="tab-pane fade" id="pane-calculadora-excel" role="tabpanel">
    <div class="card shadow-sm border-success mb-4">
      <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-calculator"></i> Calculadora y Simulador de Lotes</h5>
        <div>
           <button class="btn btn-sm btn-light text-success" onclick="abrirModalImportacionExcel()"><i class="fas fa-upload"></i> Importar desde Excel</button>
        </div>
      </div>
      <div class="card-body bg-light">
        <!-- Buscador / Selectores -->
        <div class="row mb-3 align-items-end">
          <div class="col-md-4">
            <label class="form-label">1. Seleccionar Producto Fabricado</label>
            <select class="form-select select2-productos" id="calc_producto_id">
              <option value="">-- Buscar Producto --</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">2. Versión de Formulación</label>
            <select class="form-select" id="calc_formulacion_id" disabled>
              <option value="">-- Seleccionar --</option>
            </select>
          </div>
          <div class="col-md-5">
            <label class="form-label">3. Simular Producción</label>
            <div class="input-group">
              <span class="input-group-text bg-primary text-white">Cubetas</span>
              <input type="number" class="form-control" id="calc_cubetas" value="1" min="1">
              <span class="input-group-text bg-info text-white">o m²</span>
              <input type="number" class="form-control" id="calc_m2" placeholder="Área">
              <button class="btn btn-primary" type="button" onclick="simularProduccion()"><i class="fas fa-play"></i> Calcular</button>
            </div>
          </div>
        </div>

        <!-- Metadatos de la formulación -->
        <div class="alert alert-secondary d-none" id="alertMetadatosFormulacion">
            <div class="row">
                <div class="col-md-4"><strong>Cliente:</strong> <span id="lbl_calc_cliente">-</span></div>
                <div class="col-md-4"><strong>Rendimiento:</strong> <span id="lbl_calc_rendimiento">-</span> m²/kg</div>
                <div class="col-md-4"><strong>Fecha:</strong> <span id="lbl_calc_fecha">-</span></div>
            </div>
            <div class="row mt-1">
                <div class="col-12"><strong>Comentarios:</strong> <span id="lbl_calc_comentarios">-</span></div>
            </div>
        </div>

        <!-- Tabla Excel -->
        <div class="table-responsive bg-white border p-0 rounded">
          <table class="table table-bordered table-sm mb-0 table-hover" id="tablaExcelSimulador">
            <thead class="table-dark">
              <tr>
                <th width="15%">Grupo / Color</th>
                <th width="25%">Insumo / Componente</th>
                <th width="10%" class="text-center">% BOM</th>
                <th width="12%" class="text-end">kg (Unidad)</th>
                <th width="10%" class="text-center">% Fase Acuosa</th>
                <th width="12%" class="text-end">kg Fase Acuosa</th>
                <th width="16%" class="text-end bg-primary text-white">TOTAL REQUERIDO</th>
              </tr>
            </thead>
            <tbody id="tbodyExcelSimulador">
              <tr>
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="fas fa-file-excel fa-3x mb-3 text-light"></i><br>
                  Seleccione un producto y formulación para simular la producción.
                </td>
              </tr>
            </tbody>
            <tfoot class="table-secondary" id="tfootExcelSimulador" style="display:none;">
              <tr>
                <th colspan="2" class="text-end">TOTALES GLOBALES:</th>
                <th class="text-center" id="lbl_total_porcentaje">100%</th>
                <th class="text-end" id="lbl_total_kg_unidad">0.000</th>
                <th class="text-center">-</th>
                <th class="text-end" id="lbl_total_kg_fase_acuosa">0.000</th>
                <th class="text-end bg-primary text-white fw-bold" id="lbl_total_requerido_kg">0.000</th>
              </tr>
            </tfoot>
          </table>
        </div>
        
        <!-- Botones de Acción (Edición) -->
        <div class="row mt-3" id="accionesEdicionExcel" style="display:none;">
           <div class="col-12 text-end">
             <button type="button" class="btn btn-outline-secondary me-2" onclick="habilitarEdicionInline()" id="btnHabilitarEdicion"><i class="fas fa-pencil-alt"></i> Editar Inline</button>
             <button type="button" class="btn btn-warning me-2 btn-edicion-excel d-none" onclick="guardarFormulacionExcel(true)"><i class="fas fa-copy"></i> Guardar como Nueva Versión</button>
             <button type="button" class="btn btn-danger btn-edicion-excel d-none" onclick="guardarFormulacionExcel(false)"><i class="fas fa-save"></i> Actualizar Actual</button>
             <button type="button" class="btn btn-light btn-edicion-excel d-none" onclick="cancelarEdicionInline()"><i class="fas fa-times"></i> Cancelar</button>
           </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- Fin Tabs Principales -->
"""

content = content.replace("<!-- Filtros -->", tabs_html + "<!-- Filtros -->")
content = content.replace("<!-- Modal Producto (Crear/Editar) -->", calc_html + "\n<!-- Modal Producto (Crear/Editar) -->")

with open("application/views/produccion/productos/main.php", "w") as f:
    f.write(content)

