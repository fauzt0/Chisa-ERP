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
</style>
