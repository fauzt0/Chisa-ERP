/**
 * Ayuda contextual por pantalla (botón ? en la barra y junto al título).
 */
(function () {
  'use strict';

  var TOPICS = {
    general: {
      titulo: 'Esta pantalla',
      bullets: [
        'La campana (icono de campana, arriba a la derecha) muestra alertas de cobro, stock, producción y compras.',
        'El círculo “?” de la barra abre esta guía para la pantalla en la que estás.',
        'Puedes cambiar a modo oscuro y el tamaño de letra desde la barra y el pie de página.'
      ]
    },
    dashboard: {
      titulo: 'Inicio ERP',
      bullets: [
        'Personalizar: oculta o reordena tarjetas. La configuración se guarda en este equipo.',
        'Clientes con falta de pago: documentos de venta y obras con saldo, con el nombre del cliente.',
        'Parcialidades: fechas de cobro de obra vencidas o en los próximos 7 días.',
        'Stock bajo: insumos por debajo del mínimo. Ábrelo solo si vas a hablar de almacén.',
        'Las tarjetas “Ventas del mes / hoy” cuentan órdenes de venta del periodo actual (pueden ir en $0 si no hay POS este mes).'
      ]
    },
    cartera: {
      titulo: 'Cartera por cobrar',
      bullets: [
        'Lista documentos con saldo: órdenes de venta y obras.',
        'El cliente y el RFC aparecen para que se identifique de inmediato quién debe.',
        'Cobrar abre el documento. En demo conviene no registrar un pago real si no lo acordaron.'
      ]
    },
    parcialidades: {
      titulo: 'Parcialidades de obra',
      bullets: [
        'Cada fila es una fecha de cobro programada (anticipo, exhibición, etc.).',
        'Rojo = vencida; amarillo = vence en los próximos 7 días.',
        'En el detalle de la obra, pestaña Pagos, se agregan o quitan fechas y se registran abonos.'
      ]
    },
    clientes: {
      titulo: 'Clientes (CRM)',
      bullets: [
        'Abre un cliente para ver datos fiscales (RFC, régimen, CFDI, CP) y el saldo de cartera.',
        'Pestaña Cobros: documentos pendientes de ese cliente.',
        'Pestaña Seguimiento: bitácora comercial (llamadas, visitas, acuerdos).',
        'Nuevo cliente / carga masiva: alta individual o por Excel.'
      ]
    },
    pos: {
      titulo: 'Punto de venta',
      bullets: [
        'Elige el cliente primero: se muestran RFC, régimen, uso de CFDI, CP y saldo pendiente.',
        'Busca productos por palabras (tokens), no hace falta el nombre exacto.',
        'Al confirmar se genera una orden de venta. En demo evita timbrar CFDI o enviar correo si no está pactado.'
      ]
    },
    ordenes: {
      titulo: 'Órdenes de venta',
      bullets: [
        'Aquí vive el documento comercial: estatus, total, saldo y entrega.',
        'Desde una orden se puede seguir el cobro y la fabricación vinculada.',
        'No canceles ni cobres en demo salvo que quieras mostrar ese flujo.'
      ]
    },
    obras_lista: {
      titulo: 'Obras (lista)',
      bullets: [
        'Cada obra tiene folio, cliente, estatus y montos.',
        'Entra al detalle para materiales, producción, pagos y parcialidades.',
        'Las preórdenes de compra se generan en documentos de compromiso, no en cotización.'
      ]
    },
    obras_detalle: {
      titulo: 'Detalle de obra',
      bullets: [
        'Pestaña Pagos: recibos y calendario de parcialidades con fecha y monto.',
        'Un pago se aplica a la parcialidad más antigua pendiente.',
        'Materiales y producción se calculan con el BOM real (sin rendimiento 1.0 inventado).',
        'Completar un lote de producción exige pesaje previo; no lo hagas en demo.'
      ]
    },
    productos: {
      titulo: 'Productos y formulaciones',
      bullets: [
        'Usa los chips (MICRO, Hospital, año) o el buscador para filtrar el catálogo.',
        'Recetas por cliente/año: panel de búsqueda de formulaciones ligadas a proyectos.',
        'El simulador de lote escala el BOM: en Kg la receta es por kilo de lote, no cantidad × 19.',
        'Importar Excel alimenta el catálogo; después hay que activar formulación y enlazar semielaborados.'
      ]
    },
    fabricacion: {
      titulo: 'Fabricación',
      bullets: [
        'Pedidos y solicitudes de producción (incluye las que vienen de obras).',
        'La campana avisa solicitudes pendientes y preórdenes de compra por autorizar.',
        'No muestres Control de Lotes si el inventario de lotes está vacío.',
        'No pases un lote a Completada sin pesaje: descuenta insumos en el pesaje, no al completar.'
      ]
    },
    proveedores: {
      titulo: 'Proveedores',
      bullets: [
        'Catálogo con tipo, contacto y montos de compra.',
        'Desde aquí se alimentan órdenes de compra y cotizaciones a proveedor.',
        'El resumen de tarjetas se puede ocultar si quieres más espacio para la tabla.'
      ]
    },
    compras: {
      titulo: 'Compras',
      bullets: [
        'Órdenes de compra y preórdenes generadas desde faltantes de producción.',
        'Las preórdenes deben autorizarse aquí; no se confirman solas.',
        'Insumos y categorías son el maestro que usa el BOM.'
      ]
    },
    almacen: {
      titulo: 'Almacén',
      bullets: [
        'Inventario de insumos y entregas.',
        'El stock mínimo dispara la alerta de la campana y la tarjeta del Inicio.',
        'El pesaje de producción descuenta insumos con movimiento PESAJE-*.'
      ]
    },
    rh: {
      titulo: 'Recursos humanos',
      bullets: [
        'Empleados, departamentos, nómina y comunicación interna.',
        'En nómina el “?” explica el flujo: borrador → calcular → pagar.',
        'Reloj checador es otro módulo; en esta demo se puede omitir.'
      ]
    },
    nomina: {
      titulo: 'Nómina',
      bullets: [
        'Ver = revisar y ajustar montos. $ = procesar pago (total o parcial).',
        'Pagada y Cancelada son solo lectura.',
        'La campana avisa nóminas vencidas (se muestran pocas para no tapar cobros).'
      ]
    },
    perfil: {
      titulo: 'Mi perfil',
      bullets: [
        'Datos del usuario que está en sesión.',
        'Cambio de contraseña y preferencias personales.',
        'El tema claro/oscuro también se cambia desde la luna de la barra.'
      ]
    },
    facturacion: {
      titulo: 'Facturación',
      bullets: [
        'CFDI sobre documentos de venta.',
        'En demo no timbres ni envíes correo si el PAC no está validado en vivo.'
      ]
    }
  };

  function pathOf() {
    return (window.location.pathname || '').toLowerCase();
  }

  function topicKey() {
    var p = pathOf();
    if (p.indexOf('/ventas/clientes') !== -1) return 'clientes';
    if (p.indexOf('/ventas/pos') !== -1) return 'pos';
    if (p.indexOf('/ventas/ordenes') !== -1) return 'ordenes';
    if (p.indexOf('/ventas/obrasventas') !== -1) return 'obras_lista';
    if (p.indexOf('/obras/obras/detalle') !== -1) return 'obras_detalle';
    if (p.indexOf('/obras/obras') !== -1) return 'obras_lista';
    if (p.indexOf('/produccion/productos') !== -1) return 'productos';
    if (p.indexOf('/produccion/dashboard') !== -1 || p.indexOf('/produccion/lotes') !== -1) return 'fabricacion';
    if (p.indexOf('/compras/proveedores') !== -1) return 'proveedores';
    if (p.indexOf('/compras/') !== -1) return 'compras';
    if (p.indexOf('/almacen/') !== -1) return 'almacen';
    if (p.indexOf('/rh/nomina') !== -1) return 'nomina';
    if (p.indexOf('/rh/') !== -1) return 'rh';
    if (p.indexOf('/usuarios/perfil') !== -1) return 'perfil';
    if (p.indexOf('/facturacion/') !== -1) return 'facturacion';
    if (p.indexOf('/dashboard') !== -1 || p.indexOf('/dashboards/') !== -1) return 'dashboard';
    return 'general';
  }

  function topic(key) {
    return TOPICS[key] || TOPICS.general;
  }

  function ensureModal() {
    if (document.getElementById('erpAyudaModal')) return;
    var wrap = document.createElement('div');
    wrap.innerHTML =
      '<div class="modal fade" id="erpAyudaModal" tabindex="-1" aria-labelledby="erpAyudaModalTitle" aria-hidden="true">' +
        '<div class="modal-dialog modal-dialog-centered">' +
          '<div class="modal-content border-0 shadow">' +
            '<div class="modal-header bg-primary text-white">' +
              '<h5 class="modal-title text-white" id="erpAyudaModalTitle"><i class="fas fa-info-circle me-2"></i>Ayuda</h5>' +
              '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>' +
            '</div>' +
            '<div class="modal-body" id="erpAyudaModalBody"></div>' +
          '</div>' +
        '</div>' +
      '</div>';
    document.body.appendChild(wrap.firstChild);
  }

  function renderBody(info) {
    var ul = '<ul class="mb-0 ps-3" style="line-height:1.55">';
    (info.bullets || []).forEach(function (b) {
      ul += '<li class="mb-1">' + b + '</li>';
    });
    ul += '</ul>';
    return ul;
  }

  function openAyuda(key) {
    ensureModal();
    var info = topic(key || topicKey());
    var title = document.getElementById('erpAyudaModalTitle');
    var body = document.getElementById('erpAyudaModalBody');
    if (title) title.innerHTML = '<i class="fas fa-info-circle me-2"></i>' + info.titulo;
    if (body) body.innerHTML = renderBody(info);
    var el = document.getElementById('erpAyudaModal');
    if (window.bootstrap && bootstrap.Modal) {
      bootstrap.Modal.getOrCreateInstance(el).show();
    }
  }

  function makeBtn(extraClass) {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'erp-btn-ayuda' + (extraClass ? ' ' + extraClass : '');
    btn.title = 'Funciones importantes de esta sección';
    btn.setAttribute('aria-label', 'Ayuda de esta sección');
    btn.setAttribute('data-erp-ayuda', topicKey());
    btn.textContent = '?';
    return btn;
  }

  function injectTitleButton() {
    var root = document.getElementById('erp-main-content');
    if (!root) return;
    var heading = root.querySelector('h1, h2, .page-hero h2, .container-fluid h1.h3, .container-fluid > .row h3.mb-0');
    if (!heading) heading = root.querySelector('h3.mb-0');
    if (!heading || heading.querySelector('.erp-btn-ayuda')) return;
    heading.appendChild(document.createTextNode(' '));
    heading.appendChild(makeBtn('erp-btn-ayuda-titulo'));
  }

  function bindDelegates() {
    document.addEventListener('click', function (ev) {
      var t = ev.target.closest ? ev.target.closest('.erp-btn-ayuda, #erpAyudaNavBtn') : null;
      if (!t) return;
      ev.preventDefault();
      openAyuda(t.getAttribute('data-erp-ayuda') || topicKey());
    });
  }

  function ready(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  ready(function () {
    ensureModal();
    bindDelegates();
    injectTitleButton();
  });

  window.erpAbrirAyuda = openAyuda;
})();
