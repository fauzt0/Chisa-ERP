<!DOCTYPE html>
<html lang="es" data-bs-theme="default" data-layout="fluid" data-sidebar-theme="dark" data-sidebar-position="left" data-sidebar-behavior="sticky">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Sistema ERP - Dashboard">
	<meta name="author" content="Especialistas Web">

	<title>ERP/CHISA - <?php if(isset($pageTitle)){ echo $pageTitle;  } ?></title>  

	<link rel="canonical" href="https://appstack.bootlab.io/dashboard-default.html" />
	<link rel="shortcut icon" href="img/favicon.ico">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">

	<link href="<?php echo base_url();?>assets/dist/css/app.css" rel="stylesheet">	
	<link href="<?php echo base_url();?>assets/dist/css/estilos.css?v=<?php echo time(); ?>" rel="stylesheet">
	<?php $this->load->view('rh/partials/modal_styles'); ?>
	<script>
	(function () {
	  var steps = { '-2': 0.85, '-1': 0.925, '0': 1, '1': 1.1, '2': 1.2 };
	  var level = parseInt(localStorage.getItem('erp_font_scale_level') || '0', 10);
	  if (isNaN(level)) level = 0;
	  if (level < -2) level = -2;
	  if (level > 2) level = 2;
	  var zoom = steps[String(level)] || 1;
	  document.documentElement.style.setProperty('--erp-font-zoom', String(zoom));
	  document.documentElement.setAttribute('data-erp-font-level', String(level));
	})();
	</script>
  
</head>

<body>
  <div class="wrapper">
    
    <?php $this->load->view('layouts/sidebar');?>
    
    <div class="main">
      <?php $this->load->view('layouts/topNavbar');?>

      <main class="content erp-font-scalable" id="erp-main-content">        
        <?php $this->load->view($pageView);?>
      </main>
      
      <?php $this->load->view('layouts/footer') ?>
    </div>

  </div>  
  <script src="<?php echo base_url();?>assets/dist/js/app.js"></script>  
  <?php if(isset($pageScript) && $pageScript != ''){ $this->load->view($pageScript); } ?>


  <script src="<?php echo base_url();?>assets/dist/js/tools.js?v=<?php echo time(); ?>"></script>

  <!-- Contenedor de Toast Notifications (esquina superior derecha) -->
  <div id="erp-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:11000;"></div>
  
  <!-- Script para cargar notificaciones en tiempo real + Toast alerts -->
  <script>
  var notificationsInterval;
  var _erpSeenNotifs = null;   // null = primera carga
  var _erpFirstLoad  = true;

  // Genera una clave única para cada notificación
  function _erpNotifKey(n) {
    return (n.module || '') + '|' + (n.title || '') + '|' + (n.message || '');
  }

  // Muestra un toast Bootstrap 5 para una notificación
  function showErpToast(notif) {
    var palette = {
      danger:  { color: '#dc3545', icon: 'fas fa-exclamation-circle' },
      warning: { color: '#e6a817', icon: 'fas fa-exclamation-triangle' },
      info:    { color: '#0d9aaf', icon: 'fas fa-info-circle' },
      success: { color: '#198754', icon: 'fas fa-check-circle' }
    };
    var style = palette[notif.type] || palette.info;
    var id = 'erptoast-' + Date.now() + '-' + Math.floor(Math.random() * 9999);

    var html =
      '<div id="' + id + '" class="toast align-items-center border-0 shadow-sm" ' +
      'role="alert" aria-live="assertive" aria-atomic="true" ' +
      'data-bs-autohide="true" data-bs-delay="6000" data-erp-type="' + (notif.type||'info') + '" style="min-width:300px;max-width:380px;">' +
      '<div class="d-flex">' +
      '<div class="toast-body d-flex align-items-start gap-2 py-3">' +
      '<i class="' + style.icon + ' mt-1 flex-shrink-0" style="color:' + style.color + ';font-size:1.15rem;"></i>' +
      '<div>' +
      '<strong class="d-block lh-sm">' + (notif.module || '') + ': ' + (notif.title || '') + '</strong>' +
      '<span class="text-muted small">' + (notif.message || '') + '</span>' +
      '</div>' +
      '</div>' +
      '<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>' +
      '</div>' +
      '</div>';

    var $c = $('#erp-toast-container');
    $c.append(html);
    var el = document.getElementById(id);
    var t  = new bootstrap.Toast(el);
    t.show();
    el.addEventListener('hidden.bs.toast', function() { el.remove(); });
  }

  // Detecta notificaciones nuevas y dispara toasts
  function _erpPlayProductionAlert() {
    try {
      var AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      var ctx = new AudioCtx();
      var playTone = function(freq, start, duration) {
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, ctx.currentTime + start);
        gain.gain.exponentialRampToValueAtTime(0.12, ctx.currentTime + start + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + start + duration);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(ctx.currentTime + start);
        osc.stop(ctx.currentTime + start + duration + 0.05);
      };
      playTone(523.25, 0, 0.12);
      playTone(659.25, 0.14, 0.18);
    } catch (e) { /* sin audio disponible */ }
  }

  function _erpCheckNewToasts(notifications) {
    var currentMap = {};
    notifications.forEach(function(n) { currentMap[_erpNotifKey(n)] = n; });

    if (_erpFirstLoad) {
      // En la primera carga sólo guardamos el estado; no mostramos toasts
      _erpSeenNotifs = currentMap;
      _erpFirstLoad  = false;
      return;
    }

    var isProduccionPage = window.location.pathname.indexOf('/produccion/') !== -1;
    var playSound = false;

    // Mostrar toast sólo para las que no estaban en el ciclo anterior
    notifications.forEach(function(n) {
      if (!_erpSeenNotifs || !_erpSeenNotifs[_erpNotifKey(n)]) {
        showErpToast(n);
        if (isProduccionPage && n.module === 'Producción') {
          playSound = true;
        }
      }
    });

    if (playSound) {
      _erpPlayProductionAlert();
    }

    _erpSeenNotifs = currentMap;
  }

  // Cargar notificaciones desde el servidor
  function loadNotifications() {
    $.get('<?= base_url('Notifications/get_notifications') ?>', function(response) {
      try {
        var data = typeof response === 'string' ? JSON.parse(response) : response;
        if (data.success) {
          updateNotificationsUI(data);
          _erpCheckNewToasts(data.notifications || []);
        }
      } catch(e) {
        console.error('Error parsing notifications:', e);
      }
    });
  }

  // Actualizar UI del dropdown de notificaciones
  function updateNotificationsUI(data) {
    var total = data.total_count || 0;
    var notifications = data.notifications || [];
    
    if (total > 0) {
      $('#notifications-header').html(total + ' Notificación' + (total > 1 ? 'es' : ''));
      $('#notifications-badge').text(total > 9 ? '9+' : total).show();
    } else {
      $('#notifications-header').html('Sin notificaciones');
      $('#notifications-badge').hide();
    }
    
    var html = '';
    if (notifications.length === 0) {
      html = '<div class="list-group-item text-center text-muted py-4">' +
             '<i class="fas fa-check-circle fa-2x mb-2"></i><br>' +
             'No hay notificaciones pendientes' +
             '</div>';
    } else {
      notifications.forEach(function(notif) {
        var iconClass = getIconClass(notif.type);
        var iconName  = notif.icon || 'bell';
        html += '<a href="' + notif.link + '" class="list-group-item">' +
                '<div class="row g-0 align-items-center">' +
                '<div class="col-2"><i class="' + iconClass + ' fas fa-' + iconName + '"></i></div>' +
                '<div class="col-10">' +
                '<div><strong>' + notif.module + ':</strong> ' + notif.title + '</div>' +
                '<div class="text-muted small mt-1">' + notif.message + '</div>' +
                '<div class="text-muted small mt-1">' + notif.time + '</div>' +
                '</div></div></a>';
      });
    }
    $('#notifications-list').html(html);
  }
  
  // Clase de color por tipo
  function getIconClass(type) {
    switch(type) {
      case 'danger':  return 'text-danger';
      case 'warning': return 'text-warning';
      case 'info':    return 'text-info';
      case 'success': return 'text-success';
      default:        return 'text-primary';
    }
  }
  
  // Refrescar manualmente desde el botón del dropdown
  function refreshNotifications() {
    $('#notifications-header').html('<span class="spinner-border spinner-border-sm me-2"></span> Cargando...');
    loadNotifications();
  }

  // Arrancar al cargar la página
  $(document).ready(function() {
    loadNotifications();
    var pollMs = window.location.pathname.indexOf('/produccion/') !== -1 ? 60000 : 120000;
    notificationsInterval = setInterval(loadNotifications, pollMs);
  });
  </script>
  
  <!-- Script para activar dinámicamente el sidebar según la ruta actual -->
  <script>
  (function() {
    // Obtener la URL actual
    var currentUrl = window.location.href;
    var currentPath = window.location.pathname;
    
    // Remover todas las clases active existentes
    document.querySelectorAll('.sidebar-item').forEach(function(item) {
      item.classList.remove('active');
    });
    document.querySelectorAll('.sidebar-link').forEach(function(link) {
      link.classList.remove('active');
    });
    
    // Recopilar todos los links con sus hrefs y calcular especificidad
    var links = [];
    document.querySelectorAll('.sidebar-link').forEach(function(link) {
      var linkHref = link.getAttribute('href');
      if(linkHref) {
        // Normalizar el href para comparación
        var normalizedHref = linkHref.replace(window.location.origin, '');
        
        // Verificar si el href coincide con la URL actual
        if(currentUrl.indexOf(linkHref) !== -1 || currentPath.indexOf(normalizedHref) !== -1) {
          links.push({
            element: link,
            href: linkHref,
            specificity: linkHref.length // Más largo = más específico
          });
        }
      }
    });
    
    // Ordenar por especificidad (más específico primero)
    links.sort(function(a, b) {
      return b.specificity - a.specificity;
    });
    
    // Activar solo el más específico
    if(links.length > 0) {
      var mostSpecificLink = links[0].element;
      
      // Agregar clase active al link
      mostSpecificLink.classList.add('active');
      
      // Agregar clase active al li padre
      var parentLi = mostSpecificLink.closest('.sidebar-item');
      if(parentLi) {
        parentLi.classList.add('active');
      }
      
      // Si es un submenu, expandir el padre y activarlo
      var parentDropdown = mostSpecificLink.closest('.sidebar-dropdown');
      if(parentDropdown) {
        // Mostrar el dropdown
        parentDropdown.classList.add('show');
        
        // Activar el link padre del dropdown
        var parentLink = parentDropdown.previousElementSibling;
        if(parentLink && parentLink.classList.contains('sidebar-link')) {
          parentLink.classList.remove('collapsed');
          parentLink.setAttribute('aria-expanded', 'true');
          
          // Activar el li del padre
          var parentParentLi = parentLink.closest('.sidebar-item');
          if(parentParentLi) {
            parentParentLi.classList.add('active');
          }
        }
      }
    } else {
      // Si no se encontró coincidencia, activar "Inicio" por defecto
      var inicioLink = document.querySelector('.sidebar-link[href*="dashboard"]');
      if(inicioLink) {
        inicioLink.classList.add('active');
        var parentLi = inicioLink.closest('.sidebar-item');
        if(parentLi) {
          parentLi.classList.add('active');
        }
      }
    }
  })();
  </script>
  
</body>
</html>




