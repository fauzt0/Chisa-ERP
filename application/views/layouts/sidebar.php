<nav id="sidebar" class="sidebar">
  <div class="sidebar-content js-simplebar">
    <a class="sidebar-brand" id="sidebar-brand" href="https://aapcoy.org.mx/dashboard" style="background-color: rgba(255, 255, 255, 1); padding: 0rem; padding-top:10px">
      <img class="align-middle img-fluid logotype" id="logo" src="<?php echo base_url();?>assets/dist/img/brands/chisa_recubrimientos_logo.jpg" alt="CHISA Logo" width="180px" />
    </a>

    <ul class="sidebar-nav">
      
      <li class="sidebar-item active">
        <a class="sidebar-link active" href="<?php echo base_url();?>dashboard">
          <i class="align-middle" data-lucide="home"></i> <span class="align-middle">Inicio</span>
        </a>
      </li>
      
      <li class="sidebar-item">
        <a data-bs-target="#ecommerce" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle me-2 fas fa-fw fa-users"></i> <span class="align-middle">Administradores</span>
        </a>
        <ul id="ecommerce" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>usuarios/GestionUsuarios/alta">
            Alta Administrador
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>usuarios/GestionUsuarios">
            Buscar Administrador
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>usuarios/GestionUsuarios/bitacora">
            Bitácora
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="ecommerce-customers.html">Configuración</a></li>
          
        </ul>
      </li>

      <!-- Recursos Humanos -->
      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">Recursos Humanos</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>

      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">Ordenes Compra</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>

      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">Proveedores</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>

      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">CRM</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>

      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">Ventas</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>

      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">Cálculo Materiales</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>

      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">Producción</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>

      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">Almacén</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>

      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">Facturación</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>

      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">Contabilidad</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>

      <li class="sidebar-item">
        <a data-bs-target="#projects" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="layout"></i> <span class="align-middle">Reportes</span>
        </a>
        <ul id="projects" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="projects-overview.html">Overview</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="projects-details.html">Details</a></li>
        </ul>
      </li>
      
      



    </ul>

    <!--<div class="sidebar-cta">
      <div class="sidebar-cta-content">
        <strong class="d-inline-block mb-2">Monthly Sales Report</strong>
        <div class="mb-3 text-sm">
          Your monthly sales report is ready for download!
        </div>

        <div class="d-grid">
          <a href="https://themes.getbootstrap.com/product/appstack-responsive-admin-template/" class="btn btn-primary" target="_blank">Download</a>
        </div>
      </div>
    </div> -->
  </div>
</nav>