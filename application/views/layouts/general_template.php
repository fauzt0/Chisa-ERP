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
  
</head>

<body>
  <div class="wrapper">
    
    <?php $this->load->view('layouts/sidebar');?>
    
    <div class="main">
      <?php $this->load->view('layouts/topNavbar');?>

      <main class="content">        
        <?php $this->load->view($pageView);?>
      </main>
      
      <?php $this->load->view('layouts/footer') ?>
    </div>

  </div>  
  <script src="<?php echo base_url();?>assets/dist/js/app.js"></script>  
  <?php if(isset($pageScript) && $pageScript != ''){ $this->load->view($pageScript); } ?>


  <script src="<?php echo base_url();?>assets/dist/js/tools.js"></script>
  
  <!-- Script para cargar notificaciones en tiempo real -->
  <script>
  var notificationsInterval;
  
  // Cargar notificaciones
  function loadNotifications() {
    $.get('<?= base_url('Notifications/get_notifications') ?>', function(response) {
      try {
        var data = typeof response === 'string' ? JSON.parse(response) : response;
        
        if(data.success) {
          updateNotificationsUI(data);
        }
      } catch(e) {
        console.error('Error parsing notifications:', e);
      }
    });
  }
  
  // Actualizar UI de notificaciones
  function updateNotificationsUI(data) {
    var total = data.total_count || 0;
    var notifications = data.notifications || [];
    
    // Actualizar header
    if(total > 0) {
      $('#notifications-header').html(total + ' Notificación' + (total > 1 ? 'es' : ''));
      $('#notifications-badge').text(total > 9 ? '9+' : total).show();
    } else {
      $('#notifications-header').html('Sin notificaciones');
      $('#notifications-badge').hide();
    }
    
    // Actualizar lista
    var html = '';
    if(notifications.length === 0) {
      html = '<div class="list-group-item text-center text-muted py-4">' +
             '<i class="fas fa-check-circle fa-2x mb-2"></i><br>' +
             'No hay notificaciones pendientes' +
             '</div>';
    } else {
      notifications.forEach(function(notif) {
        var iconClass = getIconClass(notif.type);
        var iconName = notif.icon || 'bell';
        
        html += '<a href="' + notif.link + '" class="list-group-item">' +
                '<div class="row g-0 align-items-center">' +
                '<div class="col-2">' +
                '<i class="' + iconClass + ' fas fa-' + iconName + '"></i>' +
                '</div>' +
                '<div class="col-10">' +
                '<div><strong>' + notif.module + ':</strong> ' + notif.title + '</div>' +
                '<div class="text-muted small mt-1">' + notif.message + '</div>' +
                '<div class="text-muted small mt-1">' + notif.time + '</div>' +
                '</div>' +
                '</div>' +
                '</a>';
      });
    }
    
    $('#notifications-list').html(html);
  }
  
  // Obtener clase de icono según tipo
  function getIconClass(type) {
    switch(type) {
      case 'danger': return 'text-danger';
      case 'warning': return 'text-warning';
      case 'info': return 'text-info';
      case 'success': return 'text-success';
      default: return 'text-primary';
    }
  }
  
  // Refrescar notificaciones manualmente
  function refreshNotifications() {
    $('#notifications-header').html('<span class="spinner-border spinner-border-sm me-2"></span> Cargando...');
    loadNotifications();
  }
  
  // Cargar notificaciones al inicio
  $(document).ready(function() {
    loadNotifications();
    
    // Actualizar cada 2 minutos
    notificationsInterval = setInterval(loadNotifications, 120000);
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




