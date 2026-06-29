<?php
/**
 * Vista de calendario mensual para asistencias del reloj checador
 * Se carga como widget dentro del modal de asistencias
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="mt-4">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="fw-bold mb-0"><i class="fas fa-calendar-alt me-2 text-success"></i>Vista Calendario Mensual</h6>
    <div class="d-flex gap-2 align-items-center">
      <span class="badge bg-success rounded-pill px-3 py-1" style="font-size:0.7rem;">✓ Asistió</span>
      <span class="badge bg-warning text-dark rounded-pill px-3 py-1" style="font-size:0.7rem;">⚠ Retardo</span>
      <span class="badge bg-danger rounded-pill px-3 py-1" style="font-size:0.7rem;">✗ Falta</span>
      <span class="badge bg-secondary rounded-pill px-3 py-1" style="font-size:0.7rem;">— Descanso</span>
      <span class="badge bg-light text-muted rounded-pill px-3 py-1 border" style="font-size:0.7rem;"> Sin datos</span>
    </div>
  </div>
  <div class="table-responsive rounded border">
    <table class="table table-sm table-bordered mb-0 text-center cal-month-table" style="font-size:0.8rem;">
      <thead class="table-success text-uppercase small">
        <tr>
          <th class="fw-semibold" style="width:14.28%;">Lun</th>
          <th class="fw-semibold" style="width:14.28%;">Mar</th>
          <th class="fw-semibold" style="width:14.28%;">Mié</th>
          <th class="fw-semibold" style="width:14.28%;">Jue</th>
          <th class="fw-semibold" style="width:14.28%;">Vie</th>
          <th class="fw-semibold text-muted" style="width:14.28%;">Sáb</th>
          <th class="fw-semibold text-danger" style="width:14.28%;">Dom</th>
        </tr>
      </thead>
      <tbody id="calendario-cuerpo">
        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-calendar-times me-2"></i>Selecciona un mes y presiona Consultar</td></tr>
      </tbody>
    </table>
  </div>

  <!-- Resumen del mes -->
  <div class="row mt-3 g-2" id="calendario-resumen">
    <div class="col-3">
      <div class="card bg-success bg-opacity-10 border-success text-center py-2">
        <div class="fw-bold text-success fs-5" id="cal-dias-asistio">0</div>
        <small class="text-muted">Asistió</small>
      </div>
    </div>
    <div class="col-3">
      <div class="card bg-warning bg-opacity-10 border-warning text-center py-2">
        <div class="fw-bold text-warning fs-5" id="cal-dias-retardo">0</div>
        <small class="text-muted">Con retardo</small>
      </div>
    </div>
    <div class="col-3">
      <div class="card bg-danger bg-opacity-10 border-danger text-center py-2">
        <div class="fw-bold text-danger fs-5" id="cal-dias-falta">0</div>
        <small class="text-muted">Falta</small>
      </div>
    </div>
    <div class="col-3">
      <div class="card bg-light border text-center py-2">
        <div class="fw-bold fs-5" id="cal-total-horas">0 hrs</div>
        <small class="text-muted">Horas trabajadas</small>
      </div>
    </div>
  </div>
</div>

<style>
.cal-month-table td {
  height: 38px;
  vertical-align: middle;
  cursor: default;
  font-weight: 500;
}
.cal-month-table td.cal-dia-otro-mes { opacity: 0.3; }
.cal-month-table td.cal-asistio { background: #d4edda; color: #155724; }
.cal-month-table td.cal-retardo { background: #fff3cd; color: #856404; }
.cal-month-table td.cal-falta { background: #f8d7da; color: #721c24; }
.cal-month-table td.cal-descanso { background: #e9ecef; color: #6c757d; }
.cal-month-table td.cal-hoy { outline: 3px solid #0d6efd; outline-offset: -2px; }
</style>

<script>
(function() {
  var $ = window.jQuery;
  if (!$) return;

  window.renderCalendarioMensual = function(dias, year, month) {
    var tbody = $('#calendario-cuerpo');
    if (!dias || dias.length === 0) {
      tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">Sin registros en este mes</td></tr>');
      return;
    }

    // Build lookup by day
    var lookup = {};
    var totalRetardo = 0, totalFalta = 0, totalAsistio = 0, totalHoras = 0;
    dias.forEach(function(d) {
      var dayNum = parseInt(d.fecha ? d.fecha.split('-')[2] : 0, 10);
      var calc = d.calculo || {};
      var estado = calc.estado || 'Sin checadas';
      lookup[dayNum] = {
        estado: estado,
        retardo: calc.minutos_retardo || 0,
        horas: calc.horas_trabajadas || '00:00',
        tieneRetardo: !!calc.retardo
      };
      if (estado === 'Asistencia completa' || estado === 'Con retardo') totalAsistio++;
      if (calc.retardo) totalRetardo++;
      if (estado === 'Sin checadas' && d.dia_semana !== 'Sábado' && d.dia_semana !== 'Domingo') totalFalta++;
      // Parse horas
      var hp = (calc.horas_trabajadas || '00:00').split(':');
      totalHoras += parseInt(hp[0] || 0, 10) + (parseInt(hp[1] || 0, 10) / 60);
    });

    // Build calendar grid
    var firstDay = new Date(year, month - 1, 1).getDay(); // 0=Sun
    var daysInMonth = new Date(year, month, 0).getDate();
    var firstDayMon = (firstDay === 0) ? 6 : firstDay - 1; // Convert to Mon=0
    var daysInPrev = new Date(year, month - 1, 0).getDate();
    var today = new Date();
    var html = '';
    var dayCount = 1;
    var done = false;
    var mapDia = { 'Lunes': 0, 'Martes': 1, 'Miércoles': 2, 'Jueves': 3, 'Viernes': 4, 'Sábado': 5, 'Domingo': 6 };
    var diaNombres = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];

    for (var w = 0; w < 6 && !done; w++) {
      html += '<tr>';
      for (var d = 0; d < 7; d++) {
        if ((w === 0 && d < firstDayMon) || dayCount > daysInMonth) {
          if (w === 0 && d < firstDayMon) {
            var prevDay = daysInPrev - firstDayMon + d + 1;
            html += '<td class="cal-dia-otro-mes">' + prevDay + '</td>';
          } else {
            html += '<td class="cal-dia-otro-mes">&nbsp;</td>';
          }
        } else {
          var info = lookup[dayCount] || null;
          var cls = 'cal-dia-otro-mes';
          var label = '';
          if (info) {
            if (info.estado === 'Asistencia completa') cls = 'cal-asistio';
            else if (info.estado === 'Con retardo' || info.tieneRetardo) cls = 'cal-retardo';
            else if (info.estado === 'Sin checadas') cls = 'cal-falta';
          } else {
            // Check if weekend
            var dow = (w * 7 + d) % 7;
            cls = (dow >= 5) ? 'cal-descanso' : '';
          }
          if (dayCount === today.getDate() && month === today.getMonth() + 1 && year === today.getFullYear()) {
            cls += ' cal-hoy';
          }
          html += '<td class="' + cls + '">' + dayCount + '</td>';
          dayCount++;
          if (dayCount > daysInMonth) done = true;
        }
      }
      html += '</tr>';
    }

    tbody.html(html);

    // Update summary
    $('#cal-dias-asistio').text(totalAsistio);
    $('#cal-dias-retardo').text(totalRetardo);
    $('#cal-dias-falta').text(totalFalta);
    $('#cal-total-horas').text(totalHoras.toFixed(1) + ' hrs');
  };
})();
</script>