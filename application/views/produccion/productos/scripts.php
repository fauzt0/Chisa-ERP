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
}
.produccion-productos-page .panel-filtros .panel-filtros-header {
  background: var(--prod-bg);
  border-bottom: 2px solid var(--prod-border);
  padding: 0.65rem 1.25rem;
  font-weight: 700;
  font-size: 0.9rem;
  color: var(--prod-text);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.produccion-productos-page .panel-filtros label {
  font-weight: 600;
  font-size: 0.85rem;
  color: var(--prod-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.02em;
  margin-bottom: 0.35rem;
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
.produccion-productos-page .btn-chip-buscar {
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 20px;
  padding: 0.2rem 0.65rem;
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
