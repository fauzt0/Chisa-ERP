<?php
$obra_id = (int) ($obra->id ?? 0);
?>
<div class="card mt-4">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabPartidas"><i class="fas fa-list"></i> Partidas y conceptos</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabApu"><i class="fas fa-calculator"></i> Unitarios (APU)</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabGeneradores"><i class="fas fa-ruler-combined"></i> Generadores</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabRevision"><i class="fas fa-clipboard-check"></i> Revisión de cuantificación</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">

            <!-- ── Selector de presupuesto ── -->
            <div class="row mb-3">
                <div class="col-md-5">
                    <label class="form-label">Presupuesto</label>
                    <select id="selectPresupuesto" class="form-select" onchange="cargarPresupuesto(this.value)">
                        <option value="">— Sin presupuesto —</option>
                    </select>
                </div>
                <div class="col-md-7 text-end align-self-end">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalNuevoPresupuesto"><i class="fas fa-plus"></i> Nuevo presupuesto / cotización</button>
                </div>
            </div>

            <!-- ── Tab Partidas ── -->
            <div class="tab-pane fade show active" id="tabPartidas">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Partidas</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalPartida" onclick="prepararModalPartida()"><i class="fas fa-plus"></i> Agregar partida</button>
                </div>
                <table class="table table-sm table-striped" id="tablaPartidas">
                    <thead><tr><th>Código</th><th>Concepto</th><th>Unidad</th><th>Cant.</th><th>P.U.</th><th>Importe</th><th></th></tr></thead>
                    <tbody></tbody>
                </table>
                <div class="text-end" id="totalesPresupuesto"></div>
            </div>

            <!-- ── Tab APU ── -->
            <div class="tab-pane fade" id="tabApu">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Concepto</label>
                        <select id="selectConceptoApu" class="form-select" onchange="cargarApu(this.value)"><option value="">Seleccione…</option></select>
                    </div>
                </div>
                <div id="contenidoApu" class="mt-3"></div>
            </div>

            <!-- ── Tab Generadores ── -->
            <div class="tab-pane fade" id="tabGeneradores">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="mb-0">Generadores de cuantificación</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalGenerador"><i class="fas fa-plus"></i> Nuevo generador</button>
                </div>
                <div id="listaGeneradores"></div>
            </div>

            <!-- ── Tab Revisión ── -->
            <div class="tab-pane fade" id="tabRevision">
                <button class="btn btn-sm btn-secondary mb-2" onclick="compararRevision()"><i class="fas fa-sync"></i> Comparar cuantificación</button>
                <table class="table table-sm table-striped">
                    <thead><tr><th>DESC</th><th>TOTAL CUANTI</th><th>COTIZADO</th><th>DIFERENCIA</th><th>%</th><th>Comentario</th></tr></thead>
                    <tbody id="tablaRevision"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Modal nuevo presupuesto -->
<div class="modal fade" id="modalNuevoPresupuesto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"><h5 class="modal-title">Nuevo presupuesto</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="np_obra_id" value="<?=$obra_id?>">
                <div class="mb-2"><label class="form-label">Tipo</label>
                    <select id="np_tipo" class="form-select"><option>Presupuesto</option><option>Cotizacion</option></select></div>
                <div class="mb-2"><label class="form-label">PRES. REF.</label><input id="np_pres_ref" class="form-control"></div>
                <div class="mb-2"><label class="form-label">Atención</label><input id="np_atencion" class="form-control"></div>
                <div class="mb-2"><label class="form-label">Fecha</label><input type="date" id="np_fecha" class="form-control" value="<?=date('Y-m-d')?>"></div>
                <div class="mb-2"><label class="form-label">Vigencia (días)</label><input type="number" id="np_validez" class="form-control" value="15"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" onclick="crearPresupuesto()">Crear</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal partida -->
<div class="modal fade" id="modalPartida" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Partida</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="partida_id">
                <div class="mb-2"><label class="form-label">Concepto (catálogo)</label>
                    <select id="partida_concepto_id" class="form-select" onchange="autollenarPartida(this)"><option value="">— Libre —</option></select></div>
                <div class="mb-2"><label class="form-label">Código</label><input id="partida_codigo" class="form-control"></div>
                <div class="mb-2"><label class="form-label">Descripción</label><textarea id="partida_descripcion" class="form-control" rows="2"></textarea></div>
                <div class="row">
                    <div class="col-4"><label class="form-label">Unidad</label><input id="partida_unidad" class="form-control"></div>
                    <div class="col-4"><label class="form-label">Cantidad</label><input type="number" step="0.01" id="partida_cantidad" class="form-control"></div>
                    <div class="col-4"><label class="form-label">P.U.</label><input type="number" step="0.01" id="partida_pu" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="guardarPartida()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal generador -->
<div class="modal fade" id="modalGenerador" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">Generador de cuantificación</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="gen_id">
                <input type="hidden" id="gen_obra_id" value="<?=$obra_id?>">
                <div class="row">
                    <div class="col-4"><label class="form-label">Hoja No.</label><input type="number" id="gen_hoja_no" class="form-control" value="1"></div>
                    <div class="col-4"><label class="form-label">Hoja de</label><input type="number" id="gen_hoja_de" class="form-control" value="1"></div>
                    <div class="col-4"><label class="form-label">Acumulado anterior</label><input type="number" step="0.01" id="gen_acumulado" class="form-control" value="0"></div>
                </div>
                <div class="mb-2"><label class="form-label">Concepto</label><input id="gen_concepto_texto" class="form-control"></div>
                <div class="mb-2"><label class="form-label">Ubicación</label><input id="gen_ubicacion" class="form-control"></div>
                <hr>
                <table class="table table-sm">
                    <thead><tr><th>Bloque</th><th>Pzas</th><th>N°</th><th>Largo</th><th>Altura</th><th>Desc.</th><th>Símb.</th><th></th></tr></thead>
                    <tbody id="tablaLineasGenerador"></tbody>
                </table>
                <div class="row">
                    <div class="col-2"><input type="number" id="ln_bloque" class="form-control" value="1"></div>
                    <div class="col-2"><input type="number" step="0.01" id="ln_pzas" class="form-control" value="1"></div>
                    <div class="col-2"><input type="number" id="ln_n" class="form-control" value="0"></div>
                    <div class="col-2"><input type="number" step="0.001" id="ln_largo" class="form-control" placeholder="Largo"></div>
                    <div class="col-2"><input type="number" step="0.001" id="ln_alto" class="form-control" placeholder="Altura"></div>
                    <div class="col-2"><input type="number" step="0.001" id="ln_desc" class="form-control" value="0"></div>
                </div>
                <div class="row mt-2">
                    <div class="col-4"><select id="ln_simbolo" class="form-select"><option value="">—</option><option>P</option><option>CV</option><option>HM</option><option>BOQH</option><option>BOQV</option><option>C</option><option>V</option><option>GE</option><option>VA</option><option>O</option></select></div>
                    <div class="col-8 text-end"><button class="btn btn-sm btn-success" onclick="agregarLineaGenerador()">Agregar línea</button></div>
                </div>
                <div class="text-end mt-2"><strong id="totalGenerador">TOTAL: 0.00</strong></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" onclick="guardarGenerador()">Guardar generador</button>
            </div>
        </div>
    </div>
</div>

<script>
var BASE = '<?=base_url()?>ventas/ObrasVentas/';
var presupuestoActual = 0;

function initTabsPresupuesto() { listarPresupuestos(); listarConceptos(); listarGeneradores(); }

function listarPresupuestos() {
    $.get(BASE + 'listar_presupuestos_ajax', {obra_id: <?=$obra_id?>}, function(res) {
        if (!res.success) return;
        var sel = $('#selectPresupuesto');
        sel.find('option:not(:first)').remove();
        res.presupuestos.forEach(function(p) {
            sel.append('<option value="' + p.id + '">' + p.folio + ' (' + p.tipo + ')</option>');
        });
        if (res.presupuestos.length > 0) { cargarPresupuesto(res.presupuestos[0].id); }
    }, 'json');
}

function cargarPresupuesto(id) {
    presupuestoActual = parseInt(id) || 0;
    if (!presupuestoActual) { $('#tablaPartidas tbody').empty(); $('#totalesPresupuesto').empty(); return; }
    $.get(BASE + 'get_presupuesto_ajax', {presupuesto_id: presupuestoActual}, function(res) {
        if (res.success) renderPartidas(res.presupuesto, res.apu);
    }, 'json');
}

function renderPartidas(p, apu) {
    var tbody = $('#tablaPartidas tbody'); tbody.empty();
    p.conceptos.forEach(function(c) {
        var usarApu = (c.concepto_id && apu[c.id]) ? '<button class="btn btn-sm btn-outline-primary" title="Usar P.U. del APU" onclick="usarPuPartida(' + c.id + ')">APU</button>' : '';
        tbody.append('<tr><td>' + (c.codigo||'-') + '</td><td>' + (c.descripcion||'-') + '</td><td>' + (c.unidad||'-') + '</td><td>' + c.cantidad + '</td><td>' + c.precio_unitario + '</td><td>' + c.importe + '</td><td>' + usarApu + ' <button class="btn btn-sm btn-outline-secondary" onclick="editarPartida(' + c.id + ')"><i class="fas fa-edit"></i></button> <button class="btn btn-sm btn-outline-danger" onclick="eliminarPartida(' + c.id + ')"><i class="fas fa-trash"></i></button></td></tr>');
    });
    $('#totalesPresupuesto').html('<strong>SUBTOTAL: $' + p.subtotal + '</strong> · IVA: $' + p.iva_monto + ' · <strong>TOTAL: $' + p.total + '</strong>');
}

function listarConceptos() {
    $.get(BASE + 'listar_conceptos_ajax', {}, function(res) {
        var sel = $('#partida_concepto_id');
        (res.conceptos || []).forEach(function(c) {
            sel.append('<option value="' + c.id + '" data-codigo="' + c.codigo + '" data-desc="' + (c.descripcion||'') + '" data-unidad="' + c.unidad + '">' + c.codigo + '</option>');
        });
    }, 'json');
}

function autollenarPartida(sel) {
    var opt = sel.options[sel.selectedIndex];
    if (sel.value) {
        $('#partida_codigo').val(opt.dataset.codigo || '');
        $('#partida_descripcion').val(opt.dataset.desc || '');
        $('#partida_unidad').val(opt.dataset.unidad || '');
    }
}

function prepararModalPartida() {
    $('#partida_id').val('');
    $('#partida_codigo,#partida_descripcion,#partida_unidad').val('');
    $('#partida_cantidad').val(''); $('#partida_pu').val('');
    $('#partida_concepto_id').val('');
}

function editarPartida(id) {
    $.get(BASE + 'get_presupuesto_ajax', {presupuesto_id: presupuestoActual}, function(res) {
        var p = null;
        res.presupuesto.conceptos.forEach(function(c) { if (c.id == id) p = c; });
        if (!p) return;
        $('#partida_id').val(id);
        $('#partida_concepto_id').val(p.concepto_id || '');
        $('#partida_codigo').val(p.codigo || '');
        $('#partida_descripcion').val(p.descripcion || '');
        $('#partida_unidad').val(p.unidad || '');
        $('#partida_cantidad').val(p.cantidad);
        $('#partida_pu').val(p.precio_unitario);
        $('#modalPartida').modal('show');
    }, 'json');
}

function guardarPartida() {
    var data = {
        id: $('#partida_id').val(),
        presupuesto_id: presupuestoActual,
        concepto_id: $('#partida_concepto_id').val(),
        codigo: $('#partida_codigo').val(),
        descripcion: $('#partida_descripcion').val(),
        unidad: $('#partida_unidad').val(),
        cantidad: $('#partida_cantidad').val(),
        precio_unitario: $('#partida_pu').val()
    };
    var url = data.id ? 'actualizar_partida_ajax' : 'agregar_partida_ajax';
    $.post(BASE + url, data, function(res) {
        if (res.success) { $('#modalPartida').modal('hide'); cargarPresupuesto(presupuestoActual); }
        else alert(res.message || 'Error');
    }, 'json');
}

function eliminarPartida(id) {
    if (!confirm('¿Eliminar esta partida?')) return;
    $.post(BASE + 'eliminar_partida_ajax', {id: id}, function(res) {
        if (res.success) cargarPresupuesto(presupuestoActual); else alert(res.message);
    }, 'json');
}

function usarPuPartida(partidaId) {
    $.post(BASE + 'usar_pu_partida_ajax', {partida_id: partidaId}, function(res) {
        if (res.success) cargarPresupuesto(presupuestoActual); else alert(res.message);
    }, 'json');
}

function cargarApu(conceptoId) {
    if (!conceptoId) { $('#contenidoApu').empty(); return; }
    $.get(BASE + 'get_apu_ajax', {concepto_id: conceptoId}, function(res) {
        var h = '<div class="row"><div class="col-md-6"><h6>Materiales</h6><table class="table table-sm"><thead><tr><th>Desc.</th><th>Cant.</th><th>Costo</th><th>Importe</th></tr></thead><tbody>';
        res.materiales.forEach(function(m) { h += '<tr><td>' + m.descripcion + '</td><td>' + m.cantidad + '</td><td>' + m.costo_unitario + '</td><td>' + m.importe.toFixed(2) + '</td></tr>'; });
        h += '</tbody></table><strong>SUBTOTAL MATERIAL: ' + res.subtotal_material.toFixed(2) + '</strong></div>';
        h += '<div class="col-md-6"><h6>Mano de obra</h6><table class="table table-sm"><thead><tr><th>Categoría</th><th>Sal. sem</th><th>Rend.</th><th>Costo</th></tr></thead><tbody>';
        res.mano_obra.forEach(function(m) { h += '<tr><td>' + m.categoria + '</td><td>' + m.salario_semanal + '</td><td>' + m.rendimiento_jor + '</td><td>' + m.costo.toFixed(4) + '</td></tr>'; });
        h += '</tbody></table><strong>SUBTOTAL MO: ' + res.subtotal_mo.toFixed(2) + '</strong></div></div>';
        h += '<table class="table table-sm mt-2"><tbody>';
        h += '<tr><td>IMSS ' + res.parametros.imss + '%</td><td>' + res.imss.toFixed(2) + '</td></tr>';
        h += '<tr><td>RCYV ' + res.parametros.rcyv + '%</td><td>' + res.rcyv.toFixed(2) + '</td></tr>';
        h += '<tr><td>ISN ' + res.parametros.isn + '%</td><td>' + res.isn.toFixed(2) + '</td></tr>';
        h += '<tr><td>HERRAMIENTA ' + res.parametros.herramienta + '%</td><td>' + res.herramienta.toFixed(2) + '</td></tr>';
        h += '<tr class="table-primary"><td>COSTO DIRECTO</td><td>' + res.costo_directo.toFixed(2) + '</td></tr>';
        h += '<tr><td>INDIRECTO ' + res.parametros.indirecto_utilidad + '%</td><td>' + res.indirecto.toFixed(2) + '</td></tr>';
        h += '<tr class="table-success"><td><strong>P.UNITARIO</strong></td><td><strong>' + res.precio_unitario_redondeado + '</strong></td></tr>';
        h += '</tbody></table>';
        $('#contenidoApu').html(h);
    }, 'json');
}

function crearPresupuesto() {
    $.post(BASE + 'crear_presupuesto_ajax', {
        obra_id: <?=$obra_id?>,
        tipo: $('#np_tipo').val(),
        pres_ref: $('#np_pres_ref').val(),
        atencion: $('#np_atencion').val(),
        fecha: $('#np_fecha').val(),
        validez_dias: $('#np_validez').val()
    }, function(res) {
        if (res.success) { $('#modalNuevoPresupuesto').modal('hide'); listarPresupuestos(); }
        else alert(res.message || 'Error');
    }, 'json');
}

function listarGeneradores() {
    $.get(BASE + 'listar_generadores_ajax', {obra_id: <?=$obra_id?>}, function(res) {
        if (!res.success) return;
        var h = '';
        res.generadores.forEach(function(g) {
            h += '<div class="border p-2 mb-2"><strong>Hoja ' + g.hoja_no + ' de ' + g.hoja_de + '</strong> — ' + (g.concepto_texto||'') + ' — SUMA: ' + g.suma_hoja + ' TOTAL: ' + g.total;
            h += ' <button class="btn btn-sm btn-outline-primary" onclick="aplicarGenerador(' + g.id + ')">Aplicar a partida</button></div>';
        });
        $('#listaGeneradores').html(h || '<p class="text-muted">Sin generadores</p>');
    }, 'json');
}

function guardarGenerador() {
    $.post(BASE + 'crear_generador_ajax', {
        obra_id: <?=$obra_id?>,
        hoja_no: $('#gen_hoja_no').val(),
        hoja_de: $('#gen_hoja_de').val(),
        acumulado_anterior: $('#gen_acumulado').val(),
        concepto_texto: $('#gen_concepto_texto').val(),
        ubicacion: $('#gen_ubicacion').val()
    }, function(res) {
        if (res.success) { $('#gen_id').val(res.id); $('#modalGenerador').modal('hide'); listarGeneradores(); }
        else alert(res.message || 'Error');
    }, 'json');
}

function agregarLineaGenerador() {
    var gid = $('#gen_id').val();
    if (!gid) { alert('Primero guarde el generador'); return; }
    $.post(BASE + 'agregar_linea_generador_ajax', {
        generador_id: gid,
        bloque: $('#ln_bloque').val(),
        pzas: $('#ln_pzas').val(),
        n: $('#ln_n').val(),
        largo: $('#ln_largo').val(),
        alto: $('#ln_alto').val(),
        descuento: $('#ln_desc').val(),
        simbolo: $('#ln_simbolo').val()
    }, function(res) {
        if (res.success) { refrescarLineasGenerador(gid); } else alert(res.message || 'Error');
    }, 'json');
}

function refrescarLineasGenerador(gid) {
    $.get(BASE + 'listar_generadores_ajax', {obra_id: <?=$obra_id?>}, function(res) {
        var g = null;
        res.generadores.forEach(function(x) { if (x.id == gid) g = x; });
        if (!g) return;
        var h = '';
        g.lineas.forEach(function(l) {
            h += '<tr><td>' + l.bloque + '</td><td>' + l.pzas + '</td><td>' + l.n + '</td><td>' + l.largo + '</td><td>' + l.alto + '</td><td>' + l.descuento + '</td><td>' + l.simbolo + '</td><td><button class="btn btn-sm btn-outline-danger" onclick="eliminarLinea(' + l.id + ')">×</button></td></tr>';
        });
        $('#tablaLineasGenerador').html(h);
        $('#totalGenerador').text('TOTAL: ' + g.total);
    }, 'json');
}

function eliminarLinea(id) {
    $.post(BASE + 'eliminar_linea_generador_ajax', {id: id}, function(res) {
        if (res.success) refrescarLineasGenerador($('#gen_id').val());
    }, 'json');
}

function aplicarGenerador(gid) {
    $.post(BASE + 'aplicar_generador_ajax', {generador_id: gid}, function(res) {
        alert(res.message || (res.success ? 'Aplicado' : 'Error'));
        if (res.success) { cargarPresupuesto(presupuestoActual); listarGeneradores(); }
    }, 'json');
}

function compararRevision() {
    $.post(BASE + 'comparar_revision_ajax', {obra_id: <?=$obra_id?>}, function(res) {
        if (!res.success) return;
        var h = '';
        (res.revisiones || []).forEach(function(r) {
            h += '<tr><td>' + (r.descripcion||'') + '</td><td>' + r.total_cuantificado + '</td><td>' + r.total_cotizado + '</td><td>' + r.diferencia + '</td><td>' + r.diferencia_pct + '%</td><td>' + (r.comentario||'') + '</td></tr>';
        });
        $('#tablaRevision').html(h);
    }, 'json');
}

$(document).ready(function() { initTabsPresupuesto(); });
</script>




