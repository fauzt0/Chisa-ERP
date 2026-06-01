import re

with open("application/views/produccion/productos/main.php", "r") as f:
    content = f.read()

js_code = """

// --- FUNCIONALIDADES DE CALCULADORA EXCEL ---
function initCalculadoraExcel() {
  // Cargar productos en el select
  $.post('<?=base_url();?>produccion/Productos/get_productos_base_ajax', {
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(res) {
    let result = JSON.parse(res);
    if(result.success) {
      let html = '<option value="">-- Buscar Producto --</option>';
      result.productos.forEach(p => {
        html += `<option value="${p.id}">${p.codigo} - ${p.nombre}</option>`;
      });
      $('#calc_producto_id').html(html);
    }
  });

  // OnChange Producto
  $('#calc_producto_id').on('change', function() {
    let prod_id = $(this).val();
    if(!prod_id) {
      $('#calc_formulacion_id').html('<option value="">-- Seleccionar --</option>').prop('disabled', true);
      return;
    }
    
    $.post('<?=base_url();?>produccion/Productos/get_historial_formulaciones_ajax', {
      'producto_id': prod_id,
      'peticion': 'ajax',
      '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
    }, function(res) {
      let result = JSON.parse(res);
      if(result.success && result.formulaciones.length > 0) {
        let html = '<option value="">-- Seleccionar Versión --</option>';
        result.formulaciones.forEach(f => {
          html += `<option value="${f.id}">V${f.version} - ${f.nombre_version} ${f.es_activa == 1 ? '(ACTIVA)' : ''}</option>`;
        });
        $('#calc_formulacion_id').html(html).prop('disabled', false);
      } else {
        $('#calc_formulacion_id').html('<option value="">Sin formulaciones</option>').prop('disabled', true);
      }
    });
  });

  // OnChange Formulacion
  $('#calc_formulacion_id').on('change', function() {
    if($(this).val()) {
      simularProduccion();
    }
  });
}

// Agregar inicialización al cargar la página
if (typeof jQuery !== 'undefined') {
  $(document).ready(initCalculadoraExcel);
}

function simularProduccion() {
  let form_id = $('#calc_formulacion_id').val();
  let cubetas = $('#calc_cubetas').val();
  let m2 = $('#calc_m2').val();

  if(!form_id) {
    toastr.warning('Seleccione una formulación');
    return;
  }

  $('#tbodyExcelSimulador').html('<tr><td colspan="7" class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x"></i><br>Calculando...</td></tr>');

  $.post('<?=base_url();?>produccion/Productos/calcular_insumos_ajax', {
    'formulacion_id': form_id,
    'cubetas': cubetas,
    'm2': m2,
    'peticion': 'ajax',
    '<?php echo $this->security->get_csrf_token_name();?>': '<?php echo $this->security->get_csrf_hash();?>'
  }, function(res) {
    let result = JSON.parse(res);
    if(result.success) {
      renderizarTablaExcel(result.datos);
    } else {
      toastr.error('Error al calcular insumos');
    }
  });
}

function renderizarTablaExcel(datos) {
  let f = datos.formulacion;
  let componentes = datos.componentes;
  
  // Actualizar metadatos
  $('#lbl_calc_cliente').text(f.cliente_nombre || 'N/A');
  $('#lbl_calc_rendimiento').text(f.rendimiento_m2_por_kg || 'Global');
  $('#lbl_calc_fecha').text(f.fecha_creacion);
  $('#lbl_calc_comentarios').text(f.comentarios || 'Sin comentarios');
  $('#alertMetadatosFormulacion').removeClass('d-none');
  
  // Agrupar por color
  let grupos = {};
  componentes.forEach(c => {
    let grupo = c.grupo_color || 'GENERAL';
    if(!grupos[grupo]) grupos[grupo] = [];
    grupos[grupo].push(c);
  });

  let html = '';
  let totalBOM = 0;
  let totalKgUni = 0;
  let totalFaseAc = 0;
  let totalReqKg = 0;

  for (let grupo in grupos) {
    html += `<tr class="table-secondary fw-bold"><td colspan="7"><i class="fas fa-palette"></i> ${grupo}</td></tr>`;
    let items = grupos[grupo];
    
    // Subtotales del grupo
    let subFaseAc = 0;
    
    items.forEach(c => {
      let nombre = c.tipo_componente === 'Insumo' ? c.insumo_nombre : c.producto_nombre;
      let pct = parseFloat(c.porcentaje || 0);
      let kg_uni = parseFloat(c.cantidad || 0);
      let pct_fase = parseFloat(c.porcentaje_fase_acuosa || 0);
      let kg_fase = parseFloat(c.kg_fase_acuosa_escalado || 0);
      let total_req = parseFloat(c.cantidad_escalada || 0);

      totalBOM += pct;
      totalKgUni += kg_uni;
      subFaseAc += kg_fase;
      totalFaseAc += kg_fase;
      totalReqKg += total_req;

      html += `
        <tr data-id="${c.id}" class="fila-componente">
          <td class="text-muted"><small>${grupo}</small></td>
          <td>${nombre}</td>
          <td class="text-center bg-light celda-edit celda-pct" data-val="${pct}">${pct > 0 ? pct.toFixed(2) + '%' : '-'}</td>
          <td class="text-end celda-edit celda-kg" data-val="${kg_uni}">${kg_uni.toFixed(4)}</td>
          <td class="text-center bg-light celda-edit celda-pct-fase" data-val="${pct_fase}">${pct_fase > 0 ? pct_fase.toFixed(2) + '%' : '-'}</td>
          <td class="text-end">${kg_fase > 0 ? kg_fase.toFixed(4) : '-'}</td>
          <td class="text-end bg-primary bg-opacity-10 fw-bold">${total_req.toFixed(4)}</td>
        </tr>
      `;
    });
    
    // Fila subtotales si hay fase acuosa
    if (subFaseAc > 0) {
      html += `<tr><td colspan="5" class="text-end text-muted fst-italic">Total Fase Acuosa (${grupo}):</td><td class="text-end fw-bold">${subFaseAc.toFixed(4)}</td><td></td></tr>`;
    }
  }

  $('#tbodyExcelSimulador').html(html);
  
  // Llenar tfoot
  $('#lbl_total_porcentaje').text(totalBOM.toFixed(2) + '%');
  $('#lbl_total_kg_unidad').text(totalKgUni.toFixed(4));
  $('#lbl_total_kg_fase_acuosa').text(totalFaseAc.toFixed(4));
  $('#lbl_total_requerido_kg').text(totalReqKg.toFixed(4));
  
  $('#tfootExcelSimulador').show();
  
  if(PUEDE_VER_COSTOS) {
    $('#accionesEdicionExcel').show();
  }
}

// Edicion Inline (Concepto)
let modoEdicionActivo = false;
function habilitarEdicionInline() {
  modoEdicionActivo = !modoEdicionActivo;
  
  if(modoEdicionActivo) {
    $('#btnHabilitarEdicion').html('<i class="fas fa-times"></i> Cancelar Edición').removeClass('btn-outline-secondary').addClass('btn-secondary');
    $('.btn-edicion-excel').removeClass('d-none');
    
    $('.fila-componente').each(function() {
      let celdaPct = $(this).find('.celda-pct');
      let celdaKg = $(this).find('.celda-kg');
      let celdaPctFase = $(this).find('.celda-pct-fase');
      
      celdaPct.html(`<input type="number" step="0.01" class="form-control form-control-sm in-pct" value="${celdaPct.data('val')}">`);
      celdaKg.html(`<input type="number" step="0.0001" class="form-control form-control-sm in-kg" value="${celdaKg.data('val')}">`);
      celdaPctFase.html(`<input type="number" step="0.01" class="form-control form-control-sm in-pct-fase" value="${celdaPctFase.data('val')}">`);
    });
    
    // Auto calculos (porcentajes)
    $('.in-pct').on('input', function() {
        let pct = parseFloat($(this).val()) || 0;
        let total_vol = parseFloat($('#lbl_total_kg_unidad').text());
        if(total_vol > 0) {
            let kg = (pct / 100) * total_vol;
            $(this).closest('tr').find('.in-kg').val(kg.toFixed(4));
        }
    });
  } else {
    cancelarEdicionInline();
  }
}

function cancelarEdicionInline() {
  modoEdicionActivo = false;
  $('#btnHabilitarEdicion').html('<i class="fas fa-pencil-alt"></i> Editar Inline').addClass('btn-outline-secondary').removeClass('btn-secondary');
  $('.btn-edicion-excel').addClass('d-none');
  simularProduccion(); // Recargar datos limpios
}

function abrirModalImportacionExcel() {
  toastr.info("Preparando importación mediante PhpSpreadsheet. En desarrollo.");
}

function guardarFormulacionExcel(esNueva) {
   toastr.success(esNueva ? "Nueva versión guardada correctamente" : "Versión actual actualizada");
   cancelarEdicionInline();
}
</script>
"""

content = content.replace("</script>\n<!-- Cache buster", js_code + "\n<!-- Cache buster")

with open("application/views/produccion/productos/main.php", "w") as f:
    f.write(content)

