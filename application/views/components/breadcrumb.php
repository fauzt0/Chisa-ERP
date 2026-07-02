<?php
/**
 * Componente de breadcrumb (migas de pan) con enlaces navegables.
 *
 * @param string $breadcrumb Ej. "Inicio > Recursos Humanos > Nómina"
 */
defined('BASEPATH') OR exit('No direct script access allowed');

if (!isset($breadcrumb) || $breadcrumb === '') {
    return;
}

$items = array_map('trim', explode('>', $breadcrumb));

$breadcrumb_urls = [
    // General
    'Inicio' => base_url('dashboard'),
    'Dashboard' => base_url('dashboard'),
    'ERP' => base_url('dashboard'),

    // Administradores / usuarios
    'Gestion de usuarios' => base_url('usuarios/GestionUsuarios'),
    'Gestión de usuarios' => base_url('usuarios/GestionUsuarios'),
    'Administradores' => base_url('usuarios/GestionUsuarios'),
    'Usuarios' => base_url('usuarios/GestionUsuarios'),
    'Alta de usuarios' => base_url('usuarios/GestionUsuarios/alta'),
    'Bitácora' => base_url('usuarios/GestionUsuarios/bitacora'),
    'Importar' => base_url('usuarios/GestionUsuarios/importar'),
    'Datos de la Empresa' => base_url('usuarios/GestionUsuarios/empresa'),
    'Roles' => base_url('usuarios/Roles'),
    'Mi Perfil' => base_url('usuarios/Perfil'),

    // Recursos Humanos
    'Recursos Humanos' => base_url('rh/RecursosHumanos'),
    'RH' => base_url('rh/RecursosHumanos'),
    'Empleados' => base_url('rh/RecursosHumanos'),
    'Alta de empleado' => base_url('rh/RecursosHumanos/alta'),
    'Departamentos' => base_url('rh/Departamentos'),
    'Nómina' => base_url('rh/Nomina'),
    'Plantillas' => base_url('rh/RecursosHumanos/plantillas'),
    'Nueva' => base_url('rh/RecursosHumanos/crear_plantilla'),

    // Reloj checador
    'Reloj Checador' => base_url('rh/RelojChecador'),
    'Dispositivos' => base_url('rh/RelojChecador/dispositivos'),
    'Comandos' => base_url('rh/RelojChecador/comandos'),
    'Sincronización' => base_url('rh/RelojChecador/sync_log'),
    'Sync Empleados RH' => base_url('rh/RelojChecador/sync_empleados_rh'),
    'Reporte Diario' => base_url('rh/RelojChecador/reporte_diario'),
    'Reporte Mensual' => base_url('rh/RelojChecador/reporte_mensual'),

    // Compras
    'Compras' => base_url('compras/Categorias'),
    'Categorías' => base_url('compras/Categorias'),
    'Insumos' => base_url('compras/Insumos'),
    'Proveedores' => base_url('compras/Proveedores'),
    'Órdenes de Compra' => base_url('compras/OrdenesCompra'),

    // Producción
    'Producción' => base_url('produccion/Dashboard'),
    'Control de Lotes' => base_url('produccion/Lotes'),
    'Historial de Ventas' => base_url('produccion/HistorialVentas'),
    'Alta de productos' => base_url('produccion/Productos/alta'),

    // CRM / Ventas
    'CRM Ventas' => base_url('ventas/Clientes'),
    'Clientes' => base_url('ventas/Clientes'),
    'Órdenes' => base_url('ventas/Ordenes'),
    'Órdenes de Venta' => base_url('ventas/Ordenes'),
    'POS' => base_url('ventas/Pos'),
    'Descuentos' => base_url('ventas/Descuentos'),
    'Obras' => base_url('ventas/ObrasVentas'),

    // Facturación
    'Facturación' => base_url('facturacion/Facturas'),

    // Contabilidad
    'Contabilidad' => base_url('contabilidad/Dashboard'),
    'Catálogo de Cuentas' => base_url('contabilidad/CuentasContables'),
    'Pólizas' => base_url('contabilidad/Polizas'),
    'Bancos' => base_url('contabilidad/Bancos'),
    'Reportes' => base_url('contabilidad/Reportes'),
    'Servicios Recurrentes' => base_url('contabilidad/ServiciosRecurrentes'),

    // Almacén
    'Almacén' => base_url('almacen/Dashboard'),
    'Inventario' => base_url('almacen/Inventario'),
    'Entregas' => base_url('almacen/Entregas'),
];
?>

<nav aria-label="breadcrumb" class="mb-2">
    <ol class="breadcrumb mb-0">
        <?php foreach ($items as $index => $item): ?>
            <?php
                $is_last = ($index === count($items) - 1);
                $url = $breadcrumb_urls[$item] ?? '#';
            ?>
            <?php if ($is_last): ?>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>
                </li>
            <?php else: ?>
                <li class="breadcrumb-item">
                    <a href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
