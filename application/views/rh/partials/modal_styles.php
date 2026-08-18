<style>
/* =============================================================================
   ERP — Encabezados con fondo de color: títulos e iconos legibles (blanco)
   Aplica a modales, offcanvas y card-header en todos los módulos.
   ============================================================================= */

/* Modales RH (compatibilidad) */
.rh-modal .modal-header.bg-primary,
.rh-modal .modal-header.bg-info,
.rh-modal .modal-header.bg-success,
.rh-modal .modal-header.bg-dark,
.rh-modal .modal-header.text-white,
.rh-modal .modal-header[style*="gradient"],
.rh-modal .modal-header[style*="#1e3a5f"],
.rh-modal .modal-header[style*="#15803d"] {
  color: #fff;
}
.rh-modal .modal-header.bg-primary .modal-title,
.rh-modal .modal-header.bg-info .modal-title,
.rh-modal .modal-header.bg-success .modal-title,
.rh-modal .modal-header.bg-dark .modal-title,
.rh-modal .modal-header.text-white .modal-title,
.rh-modal .modal-header[style*="gradient"] .modal-title,
.rh-modal .modal-header[style*="#1e3a5f"] .modal-title,
.rh-modal .modal-header[style*="#15803d"] .modal-title {
  color: #fff !important;
}
.rh-modal .modal-header.text-white small,
.rh-modal .modal-header[style*="gradient"] small {
  color: rgba(255, 255, 255, 0.85) !important;
}
.rh-modal .modal-header.bg-warning .modal-title,
.rh-modal .modal-header.bg-warning.text-dark .modal-title {
  color: #212529 !important;
}
.rh-modal .modal-header.rh-header-brand {
  background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8e 100%);
  color: #fff;
}
.rh-modal .modal-header.rh-header-brand .modal-title {
  color: #fff !important;
}

/* Global: modal headers */
.modal-header.bg-primary.text-white,
.modal-header.bg-info.text-white,
.modal-header.bg-success.text-white,
.modal-header.bg-dark.text-white,
.modal-header.text-white,
.modal-header[style*="gradient"],
.modal-header[style*="#1e3a5f"],
.modal-header[style*="#15803d"],
.modal-header[style*="#2d5a8e"] {
  color: #fff !important;
}
.modal-header.bg-primary.text-white .modal-title,
.modal-header.bg-info.text-white .modal-title,
.modal-header.bg-success.text-white .modal-title,
.modal-header.bg-dark.text-white .modal-title,
.modal-header.text-white .modal-title,
.modal-header.text-white h5,
.modal-header[style*="gradient"] .modal-title,
.modal-header[style*="gradient"] h5,
.modal-header[style*="#1e3a5f"] .modal-title,
.modal-header[style*="#15803d"] .modal-title {
  color: #fff !important;
}
.modal-header.text-white small,
.modal-header[style*="gradient"] small,
.modal-header.bg-primary.text-white small,
.modal-header.bg-info.text-white small,
.modal-header.bg-success.text-white small,
.modal-header.bg-dark.text-white small {
  color: rgba(255, 255, 255, 0.85) !important;
}
.modal-header.text-white i,
.modal-header[style*="gradient"] i,
.modal-header.bg-primary.text-white i,
.modal-header.bg-info.text-white i,
.modal-header.bg-success.text-white i,
.modal-header.bg-dark.text-white i {
  color: #fff !important;
}
.modal-header.bg-warning.text-dark .modal-title {
  color: #212529 !important;
}

/* Global: card headers */
.card-header.bg-primary.text-white,
.card-header.bg-info.text-white,
.card-header.bg-success.text-white,
.card-header.bg-dark.text-white,
.card-header.text-white,
.card-header[style*="gradient"],
.card-header[style*="#1e3a5f"],
.card-header[style*="#2d5a8e"] {
  color: #fff !important;
}
.card-header.bg-primary.text-white .card-title,
.card-header.bg-info.text-white .card-title,
.card-header.bg-success.text-white .card-title,
.card-header.bg-dark.text-white .card-title,
.card-header.text-white .card-title,
.card-header.bg-primary.text-white h1,
.card-header.bg-primary.text-white h2,
.card-header.bg-primary.text-white h3,
.card-header.bg-primary.text-white h4,
.card-header.bg-primary.text-white h5,
.card-header.bg-primary.text-white h6,
.card-header.bg-info.text-white h1,
.card-header.bg-info.text-white h2,
.card-header.bg-info.text-white h3,
.card-header.bg-info.text-white h4,
.card-header.bg-info.text-white h5,
.card-header.bg-info.text-white h6,
.card-header.bg-success.text-white h1,
.card-header.bg-success.text-white h2,
.card-header.bg-success.text-white h3,
.card-header.bg-success.text-white h4,
.card-header.bg-success.text-white h5,
.card-header.bg-success.text-white h6,
.card-header.bg-dark.text-white h1,
.card-header.bg-dark.text-white h2,
.card-header.bg-dark.text-white h3,
.card-header.bg-dark.text-white h4,
.card-header.bg-dark.text-white h5,
.card-header.bg-dark.text-white h6,
.card-header.text-white h1,
.card-header.text-white h2,
.card-header.text-white h3,
.card-header.text-white h4,
.card-header.text-white h5,
.card-header.text-white h6,
.card-header[style*="gradient"] h1,
.card-header[style*="gradient"] h2,
.card-header[style*="gradient"] h3,
.card-header[style*="gradient"] h4,
.card-header[style*="gradient"] h5,
.card-header[style*="gradient"] h6 {
  color: #fff !important;
}
.card-header.bg-primary.text-white i,
.card-header.bg-info.text-white i,
.card-header.bg-success.text-white i,
.card-header.bg-dark.text-white i,
.card-header.text-white i,
.card-header[style*="gradient"] i {
  color: #fff !important;
}
.card-header.text-white small,
.card-header[style*="gradient"] small {
  color: rgba(255, 255, 255, 0.75) !important;
}

/* Global: offcanvas headers */
.offcanvas-header.bg-primary.text-white,
.offcanvas-header.bg-info.text-white,
.offcanvas-header.bg-success.text-white,
.offcanvas-header.bg-dark.text-white,
.offcanvas-header.text-white,
.offcanvas-header[style*="gradient"],
.offcanvas-header[style*="#1e3a5f"],
.offcanvas-header[style*="#2d5a8e"] {
  color: #fff !important;
}
.offcanvas-header.bg-primary.text-white .offcanvas-title,
.offcanvas-header.bg-primary.text-white h5,
.offcanvas-header.bg-info.text-white h5,
.offcanvas-header.bg-success.text-white h5,
.offcanvas-header.bg-dark.text-white h5,
.offcanvas-header.text-white h5,
.offcanvas-header.text-white .offcanvas-title,
.offcanvas-header[style*="gradient"] h5,
.offcanvas-header[style*="gradient"] .offcanvas-title,
.offcanvas-header.bg-primary.text-white span,
.offcanvas-header.text-white span {
  color: #fff !important;
}
.offcanvas-header.text-white i,
.offcanvas-header[style*="gradient"] i,
.offcanvas-header.bg-primary.text-white i {
  color: #fff !important;
}

/* Botones de acción en tablas CRM */
.btn-acciones-crm {
  display: inline-flex;
  flex-wrap: nowrap;
  gap: 0.35rem;
  align-items: center;
}
.badge.bg-info.text-white {
  color: #fff !important;
}
.btn-acciones-crm .btn {
  margin: 0 !important;
}

/* =============================================================================
   Modales: contraste claro / oscuro (referencia: Registrar Nueva Incidencia)
   Cuerpo, labels e inputs usan tokens de Bootstrap. bg-light / text-dark
   dentro del body se remapean para no quedar negro-sobre-negro en dark.
   Encabezados de color (gradient, primary, danger…) siguen en blanco.
   ============================================================================= */
.modal-content {
  background-color: var(--bs-modal-bg, var(--bs-secondary-bg));
  color: var(--bs-body-color);
  border-color: var(--bs-border-color);
}
.modal-body,
.modal-footer {
  color: var(--bs-body-color);
  border-color: var(--bs-border-color);
}
.modal-body .form-label,
.modal-body label:not(.form-check-label):not(.btn),
.modal-body .form-check-label {
  color: var(--bs-emphasis-color, var(--bs-body-color));
}
html[data-bs-theme="dark"] .modal-body .form-control,
html[data-bs-theme="dark"] .modal-body .form-select,
html[data-bs-theme="dark"] .modal-body .input-group-text {
  background-color: var(--bs-body-bg);
  color: var(--bs-emphasis-color, var(--bs-body-color));
  border-color: var(--bs-border-color);
}
html[data-bs-theme="dark"] .modal-body .form-control::placeholder {
  color: var(--bs-secondary-color);
  opacity: 1;
}
html[data-bs-theme="dark"] .modal-body .form-control:focus,
html[data-bs-theme="dark"] .modal-body .form-select:focus {
  background-color: var(--bs-body-bg);
  color: var(--bs-emphasis-color, var(--bs-body-color));
  border-color: var(--bs-primary);
}

/* Superficies "claras" de Bootstrap → superficie terciaria del tema */
.modal .bg-light,
.offcanvas .bg-light,
.modal .card.bg-light,
.modal .card-header.bg-light {
  background-color: var(--bs-tertiary-bg) !important;
  color: var(--bs-body-color);
}
.modal .card-header.bg-light,
.modal .card-header.bg-light h6,
.modal .card-header.bg-light .mb-0 {
  color: var(--bs-emphasis-color, var(--bs-body-color));
}

/* text-dark en el cuerpo del modal (no en headers warning/amarillos) */
.modal-body .text-dark,
.modal-footer .text-dark,
.modal .card-body .text-dark,
.modal thead .text-dark,
.modal td .text-dark,
.modal th.text-dark {
  color: var(--bs-emphasis-color, var(--bs-body-color)) !important;
}
.modal-header.bg-warning .text-dark,
.modal-header.bg-warning .modal-title,
.modal-header.bg-warning.text-dark .modal-title {
  color: #212529 !important;
}

.modal .table-light,
.modal .table-light > :not(caption) > * > * {
  --bs-table-bg: var(--bs-tertiary-bg);
  --bs-table-color: var(--bs-body-color);
  background-color: var(--bs-tertiary-bg) !important;
  color: var(--bs-body-color) !important;
}

/* Fondos hex claros hardcodeados */
.modal-body[style*="#f5f5f5"],
.modal-body[style*="#f8f9fa"] {
  background-color: var(--bs-tertiary-bg) !important;
}

html[data-bs-theme="dark"] .modal .bg-white:not(#contenidoContratoModal):not(.recibo-paper):not(.recibo-item) {
  background-color: var(--bs-body-bg) !important;
  color: var(--bs-body-color);
}

/* Cerrar (X) visible en headers oscuros/de color */
.modal-header.bg-primary .btn-close:not(.btn-close-white),
.modal-header.bg-success .btn-close:not(.btn-close-white),
.modal-header.bg-danger .btn-close:not(.btn-close-white),
.modal-header.bg-dark .btn-close:not(.btn-close-white),
.modal-header.bg-info .btn-close:not(.btn-close-white),
.modal-header.text-white .btn-close:not(.btn-close-white),
.modal-header[style*="gradient"] .btn-close:not(.btn-close-white) {
  filter: invert(1) grayscale(100%) brightness(200%);
}
</style>
