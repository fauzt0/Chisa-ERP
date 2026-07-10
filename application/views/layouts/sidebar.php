<nav id="sidebar" class="sidebar">
  <?php $this->load->helper('permissions'); ?>
  <div class="sidebar-content js-simplebar">
    <a class="sidebar-brand" id="sidebar-brand" href="https://aapcoy.org.mx/dashboard" style="background-color: rgba(255, 255, 255, 1); padding: 0rem; padding-top:10px">
      <img class="align-middle img-fluid logotype" id="logo" src="<?php echo base_url();?>assets/dist/img/brands/chisa_recubrimientos_logo.jpg" alt="CHISA Logo" width="180px" />
    </a>

    <ul class="sidebar-nav">
      
      <li class="sidebar-item">
        <a class="sidebar-link" href="<?php echo base_url();?>dashboard">
          <i class="align-middle" data-lucide="home"></i> <span class="align-middle">Inicio</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a class="sidebar-link" href="<?php echo base_url();?>usuarios/Perfil">
          <i class="align-middle" data-lucide="user"></i> <span class="align-middle">Mi Perfil</span>
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
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>usuarios/Roles">
            Roles y Permisos
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>usuarios/GestionUsuarios/empresa">
            Datos de la Empresa
          </a></li>
          <?php if (tiene_permiso('admin_simular_alertas')): ?>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>usuarios/GestionUsuarios/simulador_alertas">
            Simulador de Alertas
          </a></li>
          <?php endif; ?>
          
        </ul>
      </li>

      <!-- Recursos Humanos -->
      <li class="sidebar-item">
        <a data-bs-target="#rh-menu" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="users"></i> <span class="align-middle">Recursos Humanos</span>
        </a>
        <ul id="rh-menu" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/RecursosHumanos">
            Gestión de Empleados
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/RecursosHumanos/alta">
            Alta de Empleado
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/Departamentos">
            Departamentos
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/Nomina">
            Nómina
          </a></li>
        </ul>
      </li>

      <!-- Reloj Checador -->
      <li class="sidebar-item">
        <a data-bs-target="#reloj-menu" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="clock"></i> <span class="align-middle">Reloj Checador</span>
        </a>
        <ul id="reloj-menu" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/RelojChecador">
            Dashboard
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/RelojChecador/dispositivos">
            Dispositivos
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/RelojChecador/comandos">
            Comandos
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/RelojChecador/sync_empleados_rh">
            Sync Empleados RH
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/RelojChecador/sync_log">
            Sincronización
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/RelojChecador/reporte_diario">
            Reporte Diario
          </a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?php echo base_url();?>rh/RelojChecador/reporte_mensual">
            Reporte Mensual
          </a></li>
        </ul>
      </li>
      
      <li class="sidebar-item">
        <a data-bs-target="#proveedores" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="package"></i> <span class="align-middle">Proveedores</span>
        </a>
        <ul id="proveedores" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>compras/Categorias">Categorías</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>compras/Insumos">Insumos</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>compras/Proveedores">Proveedores</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>compras/OrdenesCompra">Órdenes de Compra</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>compras/ServiciosRecurrentes">Servicios Recurrentes</a></li>
        </ul>
      </li>
      
      <li class="sidebar-item">
        <a data-bs-target="#produccion" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="factory"></i> <span class="align-middle">Producción</span>
        </a>
        <ul id="produccion" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>produccion/Dashboard">Fabricación Dashboard</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>produccion/Productos">Productos y Fórmulas</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>produccion/Lotes">Control de Lotes</a></li>
        </ul>
      </li>
      
      <li class="sidebar-item">
        <a data-bs-target="#ventas" data-bs-toggle="collapse" class="sidebar-link collapsed">
          <i class="align-middle" data-lucide="shopping-cart"></i> <span class="align-middle">CRM Ventas</span>
        </a>
        <ul id="ventas" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>ventas/Pos">Punto de Venta (POS)</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>ventas/Ordenes">Órdenes de Venta</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>ventas/ObrasVentas">Obras</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>ventas/Clientes">Clientes</a></li>
          <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>ventas/Descuentos">Descuentos</a></li>
        </ul>
      </li>

      <!-- Facturación -->
      <li class="sidebar-item">
        <a data-bs-target="#facturacion" data-bs-toggle="collapse" class="sidebar-link collapsed">
            <i class="align-middle fas fa-file-invoice-dollar"></i> <span class="align-middle">Facturación</span>
        </a>
        <ul id="facturacion" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>facturacion/Facturas">
                    Dashboard
                </a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>facturacion/Facturas/crear">
                    Nueva Factura
                </a></li>
        </ul>
      </li>
      
      <!-- Obras -->
      <li class="sidebar-item">
        <a class="sidebar-link" href="<?=base_url();?>obras/Obras">
          <i class="align-middle fas fa-hard-hat"></i> <span class="align-middle">Obras</span>
        </a>
      </li>
      

      <li class="sidebar-item">
        <a data-bs-target="#contabilidad" data-bs-toggle="collapse" class="sidebar-link collapsed">
            <i class="align-middle fas fa-calculator"></i> <span class="align-middle">Contabilidad</span>
        </a>
        <ul id="contabilidad" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>contabilidad/Dashboard">
                    Dashboard Contable
                </a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>contabilidad/CuentasContables">
                    Catálogo de Cuentas
                </a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>contabilidad/Polizas">
                    Pólizas Contables
                </a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>contabilidad/Bancos">
                    Bancos
                </a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>contabilidad/Nomina">
                    Nómina
                </a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>contabilidad/Reportes">
                    Reportes Financieros
                </a></li>
        </ul>
    </li>
      
      <!-- Almacén -->
      <li class="sidebar-item">
        <a data-bs-target="#almacen" data-bs-toggle="collapse" class="sidebar-link collapsed">
            <i class="align-middle fas fa-warehouse"></i> <span class="align-middle">Almacén</span>
        </a>
        <ul id="almacen" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>almacen/Dashboard">
                    Dashboard
                </a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>almacen/Inventario">
                    Inventario
                </a></li>
            <li class="sidebar-item"><a class="sidebar-link" href="<?=base_url();?>almacen/Entregas">
                    Entregas
                </a></li>
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