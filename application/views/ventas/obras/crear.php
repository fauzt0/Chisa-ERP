<?php
$clientes = $clientes ?? [];
$sucursales = $sucursales ?? [];
$estatus_validos = $estatus_validos ?? [];
?>
<div class="row">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?=base_url();?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?=base_url();?>ventas/ObrasVentas">Obras</a></li>
                <li class="breadcrumb-item active">Nueva</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12"><h2><i class="fas fa-hard-hat"></i> Nueva Obra</h2></div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-plus-circle"></i> Datos de la obra (carátula)</h5></div>
    <div class="card-body">
        <form id="formNuevaObra">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cliente / Empresa *</label>
                    <select name="cliente_id" id="cliente_id" class="form-select" required>
                        <option value="">Seleccione…</option>
                        <?php foreach($clientes as $c): ?>
                        <option value="<?=$c->id?>" data-contacto="<?=htmlspecialchars($c->contacto_nombre ?? '')?>" data-telefono="<?=htmlspecialchars($c->telefono ?? '')?>"><?=htmlspecialchars($c->razon_social)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre de la obra *</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Contacto de oficina</label>
                    <input type="text" name="contacto_oficina" id="contacto_oficina" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Teléfonos</label>
                    <input type="text" name="telefonos" id="telefonos" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sucursal</label>
                    <select name="sucursal_id" class="form-select">
                        <option value="">— Sin sucursal —</option>
                        <?php foreach($sucursales as $s): ?>
                        <option value="<?=$s->id?>"><?=htmlspecialchars($s->nombre)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Dirección *</label>
                    <input type="text" name="direccion" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Código postal</label>
                    <input type="text" name="codigo_postal" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estado</label>
                    <input type="text" name="estado" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Coordenadas GPS</label>
                    <input type="text" name="coordenadas_gps" class="form-control" placeholder="lat, lng">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Área total (m²)</label>
                    <input type="number" step="0.01" name="area_total" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tipo de superficie</label>
                    <input type="text" name="tipo_superficie" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Estatus</label>
                    <select name="estatus" class="form-select">
                        <?php foreach($estatus_validos as $e): ?>
                        <option value="<?=htmlspecialchars($e)?>"><?=htmlspecialchars($e)?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Fecha inicio estimada</label>
                    <input type="date" name="fecha_inicio_estimada" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Fecha fin estimada</label>
                    <input type="date" name="fecha_fin_estimada" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Anticipo (%)</label>
                    <input type="number" step="0.01" name="anticipo_porcentaje" value="0" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">IVA (%)</label>
                    <input type="number" step="0.01" name="iva_porcentaje" value="16" class="form-control">
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Condiciones ambientales</label>
                    <textarea name="condiciones_ambientales" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Especificaciones técnicas</label>
                    <textarea name="especificaciones_tecnicas" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Condiciones de pago</label>
                    <textarea name="condiciones_pago" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label class="form-label">Notas internas</label>
                    <textarea name="notas_internas" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <a href="<?=base_url('ventas/ObrasVentas')?>" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary" id="btnGuardarObra"><i class="fas fa-save"></i> Guardar obra</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('cliente_id').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    document.getElementById('contacto_oficina').value = opt.dataset.contacto || '';
    document.getElementById('telefonos').value = opt.dataset.telefono || '';
});
document.getElementById('formNuevaObra').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btnGuardarObra');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando…';
    $.post('<?=base_url('ventas/ObrasVentas/guardar_obra_ajax')?>', $(this).serialize(), function(res) {
        if (res.success) {
            window.location.href = '<?=base_url('ventas/ObrasVentas/detalle/')?>' + res.obra_id;
        } else {
            alert('Error: ' + (res.message || 'No se pudo guardar'));
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Guardar obra';
        }
    }, 'json').fail(function() {
        alert('Error de comunicación con el servidor');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Guardar obra';
    });
});
</script>

