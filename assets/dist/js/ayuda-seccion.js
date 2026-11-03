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
        'Elige sucursal (CHISA o HAPA) y cliente. Se muestran RFC, régimen, uso de CFDI y saldo.',
        'Busca por palabras. Variantes solo en CHISA GLASS MICRO y MICRO COLOR 2. El matraz abre la fórmula y no agrega el ticket.',
        'Canal mostrador es venta de caja. Si eliges una obra, es venta de obra y puedes pasar el ticket a su cotización CCO.',
        'Cobrar genera la orden. En la presentación no cobres ni timbres si no está acordado. El ticket de prueba debe quedar en $0.00.'
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
        'Viáticos y gastos de obra lista los archivos de lista de raya (gasolina, comidas, pasajes).',
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
        'Pedidos y solicitudes de producción, incluidas las que vienen de obras.',
        'Ver abre el detalle del pedido: fórmula, faltantes y preorden de compra si aplica.',
        'La campana avisa solicitudes pendientes y preórdenes por autorizar.',
        'No pases un lote a Completada sin pesaje: el pesaje descuenta insumos y completar solo entra el producto terminado.'
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
        'Inventario, movimientos (kardex) y entregas. La campana de stock bajo abre el inventario filtrado.',
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
    },
    entrenamiento_materiales: {
      titulo: 'Entrenamiento de materiales',
      bullets: [
        'El semáforo verde es revisado y el rojo es pendiente. Filtra “No revisado” para seguir el entrenamiento.',
        'El lápiz abre la ficha: estatus Activo/Inactivo, clase de uso, alias y proveedor con precio de compra.',
        'El tipo comprado/fabricado no se cambia aquí. Si el insumo es producto vendible, unidad y precio de venta se editan abajo y no son el costo de compra.',
        'No borra el insumo. Si ya está en una fórmula, sigue en el listado.'
      ]
    },
    entrenamiento_productos: {
      titulo: 'Entrenamiento de productos',
      bullets: [
        'Pantalla temporal del superadmin. Abre el producto, corrige unidad y precio, y marca la formulación principal como verificada.',
        'El historial de versiones viejas se habilita solo después de verificar la principal.',
        'Consultar el historial no activa otra fórmula ni recalcula costos.',
        'Completar un lote sigue pidiendo pesaje en Control de Lotes.'
      ]
    },
    cotizaciones_obra: {
      titulo: 'Cotizaciones de obra',
      bullets: [
        'Cada folio CCO se puede ver, editar y exportar a PDF y Excel con la papelería de la sucursal (CHISA o HAPA).',
        'El archivo de entrenamiento está en “Archivo”. Las de trabajo actual son las que se pueden editar.',
        'El costo de envío 3 Guerras es interno: no sale en la hoja del cliente.',
        'No pases una cotización a obra activa durante la demo si no está acordado.'
      ]
    },
    sucursales: {
      titulo: 'Sucursales',
      bullets: [
        'SUC-MATRIZ usa papelería CHISA. SUC-HAPA usa la suya, sin logo CHISA.',
        'Aquí se cargan logo, icono y marca de agua que salen en el PDF de la cotización.',
        'El pie y las notas legales se pueden ajustar; ventas y obras también pueden editarlos al armar la carta.'
      ]
    },
    historial_formulaciones: {
      titulo: 'Historial de formulaciones',
      bullets: [
        'Consulta de versiones: producto, versión, activa, rendimiento y archivo de origen.',
        'Abrir una versión muestra los componentes en solo lectura.',
        'Asociar un cliente no copia la fórmula ni cambia cuál está activa.',
        'Si la fórmula activa no cuadra con el costo del producto, el sistema no guarda la asociación.'
      ]
    },
    contabilidad: {
      titulo: 'Contabilidad',
      bullets: [
        'Cuentas, pólizas, bancos, nómina y reportes financieros.',
        'El expediente de lista de raya muestra comidas, bancos, asistencia y viáticos del entrenamiento.',
        'Las pólizas del mes en curso pueden verse vacías si el periodo por defecto no tiene movimientos.',
        'En la presentación no registres pólizas ni pagos reales.'
      ]
    },
    usuarios: {
      titulo: 'Administradores',
      bullets: [
        'Alta y búsqueda de usuarios. La búsqueda filtra con Enter.',
        'La carga masiva acepta Excel. La bitácora guarda quién entró y qué cambió.',
        'Roles define qué menús ve cada persona. Nuevo Rol abre el formulario de permisos.',
        'Datos de la empresa alimentan recibos y papelería cuando la sucursal no trae los suyos.'
      ]
    },
    roles: {
      titulo: 'Roles y permisos',
      bullets: [
        'Cada rol es una lista de permisos. Editar abre los módulos marcados.',
        'Nuevo Rol crea uno vacío. Guardar no borra usuarios que ya lo usan.',
        'Entrenamiento de materiales, entrenamiento de productos, cotizaciones de obra y expediente de raya tienen permiso propio.',
        'Si desmarcas un permiso, el menú deja de mostrar esa pantalla.'
      ]
    },
    empresa: {
      titulo: 'Datos de la empresa',
      bullets: [
        'Razón social, RFC y régimen se usan en recibos y facturas.',
        'Si el RFC se ve vacío aquí pero el recibo del POS ya trae uno, conviene capturarlo antes de timbrar.',
        'El logo de la empresa es el respaldo cuando la sucursal no tiene el suyo.'
      ]
    },
    bitacora: {
      titulo: 'Bitácora',
      bullets: [
        'Registro de accesos y de acciones (guardar, entrenar, asociar cliente).',
        'Sirve para saber quién tocó un insumo o una cotización.',
        'No borra movimientos. Es solo consulta.'
      ]
    },
    departamentos: {
      titulo: 'Departamentos',
      bullets: [
        'Agrupan a los empleados (producción, obras, administración).',
        'Al dar de alta un trabajador se elige su departamento.',
        'No elimines un departamento que ya tenga gente asignada.'
      ]
    },
    comunicacion: {
      titulo: 'Comunicación interna',
      bullets: [
        'Mensajes y tareas entre el personal. El icono de sobre, junto a la campana, muestra lo pendiente.',
        'No sustituye las alertas de cobro ni de stock: esas viven en la campana.',
        'En la presentación se puede mostrar vacía: “estás al día” es un estado válido.'
      ]
    },
    reloj: {
      titulo: 'Reloj checador',
      bullets: [
        'Consulta dispositivos, cola de comandos y reportes de asistencia.',
        'Sincronizar o mandar comandos habla con el reloj físico. En la presentación no lo ejecutes.',
        'La última sincronización puede ser de días atrás; eso no bloquea el resto del ERP.'
      ]
    },
    insumos: {
      titulo: 'Insumos',
      bullets: [
        'Catálogo maestro de materia prima: código, nombre técnico, alias, unidad y stock.',
        'El stock no se edita a mano en el día a día: lo mueven el pesaje y las entradas de compra.',
        'El entrenamiento de materiales clasifica y revisa; este catálogo es el alta completa.'
      ]
    },
    categorias: {
      titulo: 'Categorías de insumos',
      bullets: [
        'Agrupan el catálogo (resinas, pigmentos, envases, servicios).',
        'No distinguen materia prima de insumo de uso: eso va en clase de uso del entrenamiento.',
        'Una categoría se puede dejar inactiva sin borrar los insumos.'
      ]
    },
    ordenes_compra: {
      titulo: 'Órdenes de compra',
      bullets: [
        'Documento de compra al proveedor: partidas, precio y estatus.',
        'Las preórdenes nacen de faltantes de producción y aquí se autorizan.',
        'Recibir mercancía entra el stock. En la presentación no recibas ni canceles una OC real.'
      ]
    },
    cotizaciones_compra: {
      titulo: 'Cotizaciones de compra',
      bullets: [
        'Comparan precios de proveedores antes de emitir la orden de compra.',
        'Si el listado está vacío, el flujo sigue existiendo: se crea la solicitud y se capturan precios.',
        'No es la cotización comercial de obra (esa es CCO, en CRM).'
      ]
    },
    servicios: {
      titulo: 'Servicios recurrentes',
      bullets: [
        'Internet, renta, soporte y otros pagos que se repiten.',
        'El calendario muestra el próximo vencimiento.',
        'No genera solo la póliza: contabilidad la registra cuando se paga.'
      ]
    },
    lotes: {
      titulo: 'Control de lotes',
      bullets: [
        'Cada lote tiene código de barras, producto, cantidad, origen (orden de venta u obra) y fecha.',
        'Consultar lote lee el código y muestra obra, lote y producto.',
        'Completar exige pesaje previo. El pesaje descuenta insumos; completar entra el producto terminado.'
      ]
    },
    consulta: {
      titulo: 'Consulta de ventas',
      bullets: [
        'Busca por cliente, folio o producto sin abrir cada documento.',
        'Escribe al menos un dato. Si solo dejas el campo vacío, pide un criterio.',
        'Las cotizaciones de obra CCO aparecen con su importe. Desde aquí no se cobra.'
      ]
    },
    descuentos: {
      titulo: 'Descuentos',
      bullets: [
        'Catálogo de descuentos por porcentaje o monto fijo.',
        'El POS los ofrece al armar el ticket, después de elegir cliente.',
        'Cambiar uno aquí afecta las ventas nuevas, no reescribe órdenes ya cobradas.'
      ]
    },
    cuentas: {
      titulo: 'Catálogo de cuentas',
      bullets: [
        'Plan contable. Cada cuenta se puede consultar con sus movimientos.',
        'No borres cuentas que ya tengan pólizas.',
        'El saldo en cero puede ser real si el periodo no tiene movimientos.'
      ]
    },
    polizas: {
      titulo: 'Pólizas',
      bullets: [
        'El filtro abre en el mes actual. Si se ve vacío, cambia el periodo.',
        'Una póliza cuadra cargos y abonos antes de guardarse.',
        'En la presentación no crees ni canceles pólizas.'
      ]
    },
    bancos: {
      titulo: 'Bancos',
      bullets: [
        'Cuentas bancarias y sus movimientos.',
        'Conciliar marca el movimiento contra el estado de cuenta.',
        'No registres transferencias reales durante la presentación.'
      ]
    },
    reportes: {
      titulo: 'Reportes financieros',
      bullets: [
        'Balance, resultados y auxiliares del periodo elegido.',
        'Un balance en $0 significa que el periodo no tiene saldos cargados, no que el reporte falle.',
        'Es el módulo de reportes del ERP. No hay otro menú aparte.'
      ]
    },
    expediente: {
      titulo: 'Expediente de lista de raya',
      bullets: [
        'Clasifica los archivos de nómina: comidas, bancos, asistencia, IMSS y viáticos.',
        'No calcula la nómina ni inventa CURP o NSS. La nómina sigue en su módulo.',
        'Las obras enlazan estos viáticos en su detalle para consultarlos.'
      ]
    },
    origenes: {
      titulo: 'Orígenes operativos',
      bullets: [
        'Liga pólizas con el documento que las originó: venta, compra, obra o nómina.',
        'Sirve para rastrear de dónde salió un cargo o un abono.',
        'Es consulta. No modifica la orden ni la obra.'
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
    if (p.indexOf('/ventas/consulta') !== -1) return 'consulta';
    if (p.indexOf('/ventas/descuentos') !== -1) return 'descuentos';
    if (p.indexOf('/ventas/cotizacionesobra') !== -1) return 'cotizaciones_obra';
    if (p.indexOf('/ventas/sucursales') !== -1) return 'sucursales';
    if (p.indexOf('/ventas/obrasventas') !== -1) return 'obras_lista';
    if (p.indexOf('/obras/obras/detalle') !== -1) return 'obras_detalle';
    if (p.indexOf('/obras/obras') !== -1) return 'obras_lista';
    if (p.indexOf('/produccion/entrenamientoproductos') !== -1) return 'entrenamiento_productos';
    if (p.indexOf('/produccion/historialformulaciones') !== -1) return 'historial_formulaciones';
    if (p.indexOf('/produccion/lotes') !== -1) return 'lotes';
    if (p.indexOf('/produccion/productos') !== -1) return 'productos';
    if (p.indexOf('/produccion/dashboard') !== -1) return 'fabricacion';
    if (p.indexOf('/compras/entrenamientomateriales') !== -1) return 'entrenamiento_materiales';
    if (p.indexOf('/compras/proveedores') !== -1) return 'proveedores';
    if (p.indexOf('/compras/insumos') !== -1) return 'insumos';
    if (p.indexOf('/compras/categorias') !== -1) return 'categorias';
    if (p.indexOf('/compras/ordenescompra') !== -1) return 'ordenes_compra';
    if (p.indexOf('/compras/cotizaciones') !== -1) return 'cotizaciones_compra';
    if (p.indexOf('/compras/serviciosrecurrentes') !== -1) return 'servicios';
    if (p.indexOf('/compras/') !== -1) return 'compras';
    if (p.indexOf('/contabilidad/expedienteraya') !== -1) return 'expediente';
    if (p.indexOf('/contabilidad/cuentas') !== -1) return 'cuentas';
    if (p.indexOf('/contabilidad/polizas') !== -1) return 'polizas';
    if (p.indexOf('/contabilidad/bancos') !== -1) return 'bancos';
    if (p.indexOf('/contabilidad/reportes') !== -1) return 'reportes';
    if (p.indexOf('/contabilidad/origenes') !== -1) return 'origenes';
    if (p.indexOf('/contabilidad/nomina') !== -1) return 'nomina';
    if (p.indexOf('/contabilidad/') !== -1) return 'contabilidad';
    if (p.indexOf('/almacen/') !== -1) return 'almacen';
    if (p.indexOf('/rh/relojchecador') !== -1) return 'reloj';
    if (p.indexOf('/rh/nomina') !== -1) return 'nomina';
    if (p.indexOf('/rh/departamentos') !== -1) return 'departamentos';
    if (p.indexOf('/rh/comunicacion') !== -1) return 'comunicacion';
    if (p.indexOf('/rh/') !== -1) return 'rh';
    if (p.indexOf('/usuarios/roles') !== -1) return 'roles';
    if (p.indexOf('/usuarios/gestionusuarios/empresa') !== -1) return 'empresa';
    if (p.indexOf('/usuarios/gestionusuarios/bitacora') !== -1) return 'bitacora';
    if (p.indexOf('/usuarios/gestionusuarios') !== -1) return 'usuarios';
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
