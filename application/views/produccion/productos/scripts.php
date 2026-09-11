<script>
if (typeof window.jQuery !== 'undefined' && typeof window.$ === 'undefined') {
  window.$ = window.jQuery;
}
</script>
<style>
/* ═══════════════════════════════════════════════════════════════
   PRODUCCIÓN → PRODUCTOS — UI legible, alto contraste, touch-friendly
   ═══════════════════════════════════════════════════════════════ */
.produccion-productos-page {
  --prod-primary: #1e40af;
  --prod-primary-dark: #1e3a8a;
  --prod-accent: #059669;
  --prod-accent-light: #d1fae5;
  --prod-warn: #d97706;
  --prod-surface: #ffffff;
  --prod-bg: #f1f5f9;
  --prod-border: #cbd5e1;
  --prod-text: #0f172a;
  --prod-text-muted: #475569;
  --prod-radius: 10px;
  --prod-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
}

.produccion-productos-page .page-hero {
  background: linear-gradient(135deg, var(--prod-primary) 0%, var(--prod-primary-dark) 100%);
  color: #fff;
  border-radius: var(--prod-radius);
  padding: 1.25rem 1.5rem;
  margin-bottom: 1.25rem;
  box-shadow: var(--prod-shadow);
}
.produccion-productos-page .page-hero h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  color: #fff;
}
.produccion-productos-page .page-hero .lead {
  opacity: 0.92;
  font-size: 0.95rem;
  margin: 0.35rem 0 0;
}

.produccion-productos-page .guia-banner {
  background: #fffbeb;
  border: 2px solid #fbbf24;
  border-left: 6px solid var(--prod-warn);
  border-radius: var(--prod-radius);
  padding: 1rem 1.25rem;
  margin-bottom: 1.25rem;
  color: var(--prod-text);
  font-size: 0.9rem;
  line-height: 1.45;
}
.produccion-productos-page .guia-banner strong { color: #92400e; }

.produccion-productos-page .stat-card {
  border: none;
  border-radius: var(--prod-radius);
  box-shadow: var(--prod-shadow);
  overflow: hidden;
  transition: transform 0.15s ease;
}
.produccion-productos-page .stat-card:hover { transform: translateY(-2px); }
.produccion-productos-page .stat-card .card-header {
  border-bottom: 2px solid var(--prod-bg);
  background: var(--prod-surface);
  font-weight: 600;
  font-size: 0.88rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--prod-text-muted);
}
.produccion-productos-page .stat-card .stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--prod-text);
  line-height: 1.1;
}
.produccion-productos-page .stat-card.stat-primary .card-header { border-bottom-color: var(--prod-primary); }
.produccion-productos-page .stat-card.stat-success .card-header { border-bottom-color: var(--prod-accent); }
.produccion-productos-page .stat-card.stat-info .card-header { border-bottom-color: #0ea5e9; }
.produccion-productos-page .stat-card.stat-danger .card-header { border-bottom-color: #dc2626; }

.produccion-productos-page #mainTabs {
  border-bottom: 3px solid var(--prod-border);
  gap: 0.25rem;
}
.produccion-productos-page #mainTabs .nav-link {
  font-weight: 600;
  font-size: 1rem;
  padding: 0.75rem 1.25rem;
  color: var(--prod-text-muted);
  border: none;
  border-radius: var(--prod-radius) var(--prod-radius) 0 0;
  min-height: 48px;
}
.produccion-productos-page #mainTabs .nav-link:hover {
  color: var(--prod-primary);
  background: var(--prod-bg);
}
.produccion-productos-page #mainTabs .nav-link.active {
  color: var(--prod-primary);
  background: var(--prod-surface);
  border-bottom: 3px solid var(--prod-primary);
  margin-bottom: -3px;
}

.produccion-productos-page .panel-filtros {
  background: var(--prod-surface);
  border: 2px solid var(--prod-border);
  border-radius: var(--prod-radius);
  box-shadow: var(--prod-shadow);
  position: relative;
  z-index: 10;
  pointer-events: auto;
  overflow: hidden;
}
.produccion-productos-page .panel-filtros .panel-filtros-header {
  background: var(--prod-bg);
  border-bottom: 2px solid var(--prod-border);
  padding: 0.7rem 1.25rem;
  color: var(--prod-text);
}
.produccion-productos-page .panel-filtros-title {
  font-weight: 700;
  font-size: 0.9rem;
  color: var(--prod-text);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.produccion-productos-page .panel-filtros-body {
  padding: 1.25rem 1.5rem;
}
.produccion-productos-page .panel-filtros label {
  font-weight: 600;
  font-size: 0.85rem;
  color: var(--prod-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.02em;
  margin-bottom: 0.4rem;
}
.produccion-productos-page .panel-filtros .form-control,
.produccion-productos-page .panel-filtros .form-select {
  min-height: 44px;
  font-size: 1rem;
  border: 2px solid var(--prod-border);
  border-radius: 8px;
}
.produccion-productos-page .panel-filtros .form-control:focus,
.produccion-productos-page .panel-filtros .form-select:focus {
  border-color: var(--prod-primary);
  box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.2);
}
.produccion-productos-page #buscarProductos {
  font-size: 1.05rem;
  font-weight: 500;
}

/* Badge de filtros activos (header) */
.produccion-productos-page .badge-filtros-activos {
  display: inline-flex;
  align-items: center;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  padding: 0.28rem 0.6rem;
  border-radius: 999px;
  background: var(--prod-accent-light);
  color: var(--prod-primary-dark);
  border: 1px solid rgba(5, 150, 105, 0.35);
}
.produccion-productos-page .btn-limpiar-filtros {
  font-weight: 600;
  white-space: nowrap;
}
.produccion-productos-page .btn-limpiar-filtros:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

/* Bloque de búsqueda: input-group con icono */
.produccion-productos-page .input-group-buscar .input-group-text {
  border: 2px solid var(--prod-border);
  border-right: 0;
  background: var(--prod-bg);
  color: var(--prod-text-muted);
  border-radius: 8px 0 0 8px;
}
.produccion-productos-page .input-group-buscar .form-control {
  border-left: 0;
  border-radius: 0 8px 8px 0;
}
.produccion-productos-page .input-group-buscar .form-control:focus {
  box-shadow: none;
}
.produccion-productos-page .input-group-buscar:focus-within {
  box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.2);
  border-radius: 8px;
}
.produccion-productos-page .input-group-buscar:focus-within .input-group-text,
.produccion-productos-page .input-group-buscar:focus-within .form-control {
  border-color: var(--prod-primary);
}
.produccion-productos-page .chips-label {
  font-weight: 600;
}
.produccion-productos-page .btn-chip-buscar {
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 20px;
  padding: 0.2rem 0.65rem;
}

/* Separador de grupo "Filtros" */
.produccion-productos-page .filtros-separador {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 1.15rem 0 0.85rem;
}
.produccion-productos-page .filtros-separador span {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--prod-text-muted);
  flex: 0 0 auto;
}
.produccion-productos-page .filtros-separador::after {
  content: "";
  flex: 1 1 auto;
  border-top: 1px dashed var(--prod-border);
}

/* Estado "filtro activo" en selects */
.produccion-productos-page .panel-filtros .form-select.is-filtro-activo {
  border-color: var(--prod-primary);
  background-color: rgba(30, 64, 175, 0.05);
  font-weight: 600;
}

/* ── Modo oscuro (hereda tokens Bootstrap 5.3) ───────────────────────── */
html[data-bs-theme="dark"] .produccion-productos-page .panel-filtros {
  background: var(--bs-secondary-bg);
  border-color: var(--bs-border-color);
}
html[data-bs-theme="dark"] .produccion-productos-page .panel-filtros .panel-filtros-header {
  background: var(--bs-tertiary-bg);
  color: var(--bs-body-color);
  border-bottom-color: var(--bs-border-color);
}
html[data-bs-theme="dark"] .produccion-productos-page .panel-filtros-title {
  color: var(--bs-body-color);
}
html[data-bs-theme="dark"] .produccion-productos-page .panel-filtros label {
  color: var(--bs-secondary-color);
}
html[data-bs-theme="dark"] .produccion-productos-page .panel-filtros .form-control,
html[data-bs-theme="dark"] .produccion-productos-page .panel-filtros .form-select {
  background-color: var(--bs-body-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-body-color);
}
html[data-bs-theme="dark"] .produccion-productos-page .input-group-buscar .input-group-text {
  background: var(--bs-tertiary-bg);
  border-color: var(--bs-border-color);
  color: var(--bs-secondary-color);
}
html[data-bs-theme="dark"] .produccion-productos-page .filtros-separador span {
  color: var(--bs-secondary-color);
}
html[data-bs-theme="dark"] .produccion-productos-page .filtros-separador::after {
  border-top-color: var(--bs-border-color);
}
html[data-bs-theme="dark"] .produccion-productos-page .badge-filtros-activos {
  background: rgba(16, 185, 129, 0.18);
  color: #6ee7b7;
  border-color: rgba(16, 185, 129, 0.4);
}
html[data-bs-theme="dark"] .produccion-productos-page .panel-filtros .form-select.is-filtro-activo {
  border-color: #60a5fa;
  background-color: rgba(96, 165, 250, 0.12);
}

/* Responsive del panel de filtros */
@media (max-width: 768px) {
  .produccion-productos-page .panel-filtros-body { padding: 1rem; }
  .produccion-productos-page .panel-filtros .panel-filtros-header { padding: 0.65rem 1rem; }
}

.produccion-productos-page .panel-tabla {
  border: 2px solid var(--prod-border);
  border-radius: var(--prod-radius);
  box-shadow: var(--prod-shadow);
  overflow: hidden;
}
.produccion-productos-page .panel-tabla .card-header {
  background: var(--prod-primary);
  color: #fff;
  font-weight: 700;
  padding: 0.75rem 1.25rem;
  border: none;
}
.produccion-productos-page #tablaProductos thead th {
  background: #1e293b !important;
  color: #fff !important;
  font-weight: 600;
  font-size: 0.82rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  padding: 0.65rem 0.5rem;
  white-space: nowrap;
}
.produccion-productos-page #tablaProductos tbody td {
  vertical-align: middle;
  font-size: 0.9rem;
  color: var(--prod-text);
  padding: 0.6rem 0.5rem;
}
.produccion-productos-page #tablaProductos tbody tr:hover {
  background-color: #eff6ff !important;
}
.produccion-productos-page .dataTables_wrapper .dataTables_length select,
.produccion-productos-page .dataTables_wrapper .dataTables_info {
  font-size: 0.9rem;
  color: var(--prod-text-muted);
}
.produccion-productos-page .dataTables_processing {
  z-index: 2;
  pointer-events: none;
  background: rgba(255,255,255,0.92) !important;
  font-weight: 600;
  color: var(--prod-primary);
}

/* Simulador */
.produccion-productos-page .simulador-card {
  border: 2px solid var(--prod-accent);
  border-radius: var(--prod-radius);
  overflow: hidden;
  box-shadow: var(--prod-shadow);
}
.produccion-productos-page .simulador-card > .card-header {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  padding: 1rem 1.25rem;
}
.produccion-productos-page .simulador-paso {
  background: var(--prod-surface);
  border: 2px solid var(--prod-border);
  border-radius: 8px;
  padding: 1rem;
  height: 100%;
}
.produccion-productos-page .simulador-paso .paso-num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  background: var(--prod-accent);
  color: #fff;
  font-weight: 700;
  border-radius: 50%;
  font-size: 0.85rem;
  margin-right: 0.5rem;
}
.produccion-productos-page .simulador-paso label {
  font-weight: 700;
  color: var(--prod-text);
  margin-bottom: 0.5rem;
}
.produccion-productos-page #tablaExcelSimulador thead th {
  background: #0f172a !important;
  color: #fff !important;
  font-size: 0.82rem;
  text-transform: uppercase;
}
.produccion-productos-page #tablaExcelSimulador tbody td {
  font-size: 0.9rem;
}
.produccion-productos-page #tablaExcelSimulador .celda-edit input {
  min-height: 38px;
  font-size: 1rem;
}

.produccion-productos-page .btn-accion-principal {
  min-height: 44px;
  font-weight: 600;
  padding: 0.5rem 1.25rem;
}

@media (max-width: 768px) {
  .produccion-productos-page .page-hero h2 { font-size: 1.25rem; }
  .produccion-productos-page #mainTabs .nav-link { font-size: 0.9rem; padding: 0.6rem 0.75rem; }
}
</style>
<script src="<?= base_url(); ?>assets/dist/js/produccion_productos.js?v=<?= time(); ?>"></script>
<script>
/* UI del panel de filtros: contador de filtros activos, botón "Limpiar"
   contextual y resalte de selects con valor. Solo añade comportamiento de UI;
   no altera la lógica de filtrado de produccion_productos.js. */
(function () {
  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }
  ready(function () {
    var busca = document.getElementById('buscarProductos');
    var selects = ['filtroTipo', 'filtroEstatus', 'filtroStock']
      .map(function (id) { return document.getElementById(id); });
    var badge = document.getElementById('contadorFiltrosProductos');
    var btnLimpiar = document.getElementById('btnLimpiarFiltrosProductos');
    if (!busca || !badge || !btnLimpiar) { return; }

    function actualizarEstadoFiltros() {
      var n = 0;
      if (busca.value.trim() !== '') { n++; }
      selects.forEach(function (sel) {
        if (!sel) { return; }
        var activo = sel.value !== '';
        sel.classList.toggle('is-filtro-activo', activo);
        if (activo) { n++; }
      });
      if (n > 0) {
        badge.textContent = n + (n === 1 ? ' filtro activo' : ' filtros activos');
        badge.classList.remove('d-none');
        btnLimpiar.disabled = false;
      } else {
        badge.textContent = '';
        badge.classList.add('d-none');
        btnLimpiar.disabled = true;
      }
    }

    busca.addEventListener('input', actualizarEstadoFiltros);
    selects.forEach(function (sel) {
      if (sel) { sel.addEventListener('change', actualizarEstadoFiltros); }
    });
    // El handler original de produccion_productos.js corre primero (limpia
    // valores / aplica chip); recalculamos justo después en un microtask.
    btnLimpiar.addEventListener('click', function () {
      setTimeout(actualizarEstadoFiltros, 0);
    });
    document.addEventListener('click', function (e) {
      if (e.target.closest && e.target.closest('.btn-chip-buscar')) {
        setTimeout(actualizarEstadoFiltros, 0);
      }
    });

    actualizarEstadoFiltros();
  });
})();
</script>
