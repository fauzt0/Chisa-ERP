/**
 * Utilidades compartidas para previsualización y exportación PDF de contratos RH.
 * Formato carta (letter). La generación PDF sigue el mismo enfoque probado en plantillas RH.
 */
(function (window) {
  'use strict';

  var COLOR_DEFAULT = '#1a3a5c';

  function esHtml(contenido) {
    return !!(contenido && /<[a-z][\s\S]*>/i.test(contenido));
  }

  function normalizarHtmlContrato(contenido, colorCorporativo) {
    if (!contenido) return '';
    var color = colorCorporativo || COLOR_DEFAULT;
    var html = String(contenido);
    if (!esHtml(html)) {
      html = html.replace(/\n/g, '<br>');
    }
    return html.replace(/\{\{color_corporativo\}\}/g, color);
  }

  function estilosContratoCss(colorCorporativo) {
    var color = colorCorporativo || COLOR_DEFAULT;
    return (
      '.rh-contrato-doc{font-family:"Times New Roman",Times,serif;font-size:12pt;line-height:1.6;color:#1a1a1a;text-align:justify;}' +
      '.rh-contrato-doc h1,.rh-contrato-doc h2,.rh-contrato-doc h3{color:' + color + ';font-family:Georgia,"Times New Roman",serif;margin-top:1em;margin-bottom:0.5em;}' +
      '.rh-contrato-doc table{border-collapse:collapse;width:100%;}' +
      '.rh-contrato-doc td,.rh-contrato-doc th{padding:8px;vertical-align:top;}' +
      '.rh-contrato-doc img{max-width:200px;height:auto;}'
    );
  }

  function aplicarEstilosContenedor(container, colorCorporativo) {
    if (!container) return;
    Object.assign(container.style, {
      fontFamily: '"Times New Roman", Times, serif',
      fontSize: '12pt',
      lineHeight: '1.6',
      textAlign: 'justify',
      color: '#1a1a1a',
      background: '#fff',
      boxSizing: 'border-box'
    });
    container.querySelectorAll('h1,h2,h3').forEach(function (h) {
      h.style.color = colorCorporativo || COLOR_DEFAULT;
      h.style.fontFamily = 'Georgia, "Times New Roman", serif';
    });
    container.querySelectorAll('img').forEach(function (img) {
      img.style.maxWidth = '200px';
      img.style.maxHeight = '80px';
      img.style.height = 'auto';
      if (!img.getAttribute('crossorigin')) {
        img.setAttribute('crossorigin', 'anonymous');
      }
    });
  }

  function renderPreview(container, html, colorCorporativo) {
    if (!container) return;
    var color = colorCorporativo || COLOR_DEFAULT;
    var normalizado = normalizarHtmlContrato(html, color);
    container.innerHTML =
      '<style>' + estilosContratoCss(color) + '</style>' +
      '<div class="rh-contrato-doc">' + normalizado + '</div>';
    Object.assign(container.style, {
      padding: '32px 40px',
      minHeight: '200px',
      background: '#fff'
    });
    aplicarEstilosContenedor(container.querySelector('.rh-contrato-doc') || container, color);
  }

  /**
   * Construye el nodo que html2pdf captura (mismo patrón que form_plantilla.php).
   * No posicionar fuera de pantalla: html2canvas deja la hoja en blanco.
   */
  function prepararElementoPdf(html, colorCorporativo) {
    var color = colorCorporativo || COLOR_DEFAULT;
    var normalizado = normalizarHtmlContrato(html, color);

    var element = document.createElement('div');
    element.className = 'rh-contrato-pdf-content';
    element.innerHTML = normalizado;

    Object.assign(element.style, {
      padding: '30px 40px',
      fontFamily: '"Times New Roman", Times, serif',
      fontSize: '12pt',
      lineHeight: '1.6',
      textAlign: 'justify',
      color: '#1a1a1a',
      backgroundColor: '#ffffff',
      boxSizing: 'border-box'
    });

    element.querySelectorAll('h1,h2,h3').forEach(function (h) {
      h.style.color = color;
      h.style.fontFamily = 'Georgia, "Times New Roman", serif';
      h.style.marginBottom = '10px';
      h.style.marginTop = '20px';
    });

    element.querySelectorAll('table').forEach(function (t) {
      t.style.borderCollapse = 'collapse';
      t.style.width = '100%';
    });

    element.querySelectorAll('img').forEach(function (img) {
      img.style.maxWidth = '140px';
      img.style.maxHeight = '60px';
      img.style.height = 'auto';
      img.style.display = 'block';
      img.style.margin = '0 auto 15px';
      if (!img.getAttribute('crossorigin')) {
        img.setAttribute('crossorigin', 'anonymous');
      }
    });

    var wrapper = document.createElement('div');
    wrapper.className = 'rh-contrato-pdf-root';
    wrapper.style.border = '2px solid ' + color;
    wrapper.style.padding = '20px';
    wrapper.style.backgroundColor = '#ffffff';
    wrapper.style.boxSizing = 'border-box';
    wrapper.appendChild(element);

    return wrapper;
  }

  function opcionesPdf(filename) {
    return {
      margin: [15, 15, 20, 15],
      filename: filename || 'Contrato.pdf',
      image: { type: 'jpeg', quality: 0.95 },
      html2canvas: {
        scale: 1.5,
        useCORS: true,
        scrollY: 0,
        letterRendering: true,
        logging: false
      },
      jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' },
      pagebreak: { mode: ['css', 'legacy'] }
    };
  }

  function parseJsonResponse(response) {
    if (response && typeof response === 'object') return response;
    return JSON.parse(response);
  }

  function generarPDF(html, filename, colorCorporativo) {
    if (!window.html2pdf) {
      alert('La librería de PDF no está cargada. Recarga la página.');
      return;
    }
    if (!html || html === '<p>&nbsp;</p>' || html === '<p></p>') {
      alert('El contenido del contrato está vacío.');
      return;
    }

    var wrapper = prepararElementoPdf(html, colorCorporativo);

    // Igual que form_plantilla.php: html2pdf clona el nodo internamente.
    // No usar left:-10000px ni display:none — html2canvas captura hoja en blanco.
    html2pdf()
      .set(opcionesPdf(filename))
      .from(wrapper)
      .save()
      .catch(function (e) {
        console.error(e);
        alert('Error al generar PDF: ' + (e.message || 'Error desconocido'));
      });
  }

  function abrirModalContrato(modalId) {
    var el = document.getElementById(modalId);
    if (!el) return null;
    if (window.bootstrap && bootstrap.Modal) {
      return bootstrap.Modal.getOrCreateInstance(el);
    }
    if (window.jQuery) {
      return {
        show: function () { jQuery(el).modal('show'); },
        hide: function () { jQuery(el).modal('hide'); }
      };
    }
    return null;
  }

  window.RhContratos = {
    COLOR_DEFAULT: COLOR_DEFAULT,
    esHtml: esHtml,
    normalizarHtmlContrato: normalizarHtmlContrato,
    renderPreview: renderPreview,
    prepararElementoPdf: prepararElementoPdf,
    generarPDF: generarPDF,
    parseJsonResponse: parseJsonResponse,
    abrirModalContrato: abrirModalContrato
  };
})(window);
