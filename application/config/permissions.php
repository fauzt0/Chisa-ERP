<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permissions Configuration
 * Define all system permissions organized by module
 * Updated to match current ERP modules
 */

$config['permissions'] = array(
  'Administradores' => array(
    'user_add'     => 'Agregar usuarios',
    'user_edit'    => 'Editar usuarios',
    'user_consult' => 'Consultar usuarios',
    'user_delete'  => 'Eliminar usuarios',
    'user_bitacora' => 'Ver bitácora',
    'admin_simular_alertas' => 'Simular alertas del sistema (demo)',
  ),
  
  'Recursos Humanos' => array(
    'rh_empleados_add'     => 'Agregar empleados',
    'rh_empleados_edit'    => 'Editar empleados',
    'rh_empleados_consult' => 'Consultar empleados',
    'rh_empleados_delete'  => 'Eliminar empleados',
    'rh_departamentos'     => 'Gestionar departamentos',
    'rh_nomina'            => 'Gestionar nómina',
  ),
  
  'Clientes (CRM)' => array(
    'clientes_add'     => 'Agregar clientes',
    'clientes_edit'    => 'Editar clientes',
    'clientes_consult' => 'Consultar clientes',
    'clientes_delete'  => 'Eliminar clientes',
  ),
  
  'Proveedores' => array(
    'proveedores_add'     => 'Agregar proveedores',
    'proveedores_edit'    => 'Editar proveedores',
    'proveedores_consult' => 'Consultar proveedores',
    'proveedores_delete'  => 'Eliminar proveedores',
    'proveedores_insumos' => 'Vincular y gestionar insumos del proveedor',
  ),
  
  'Ventas' => array(
    'ventas_ordenes_add'     => 'Crear órdenes de venta',
    'ventas_ordenes_edit'    => 'Editar órdenes de venta',
    'ventas_ordenes_consult' => 'Consultar órdenes de venta',
    'ventas_ordenes_delete'  => 'Cancelar órdenes de venta',
    'ventas_cotizaciones'    => 'Gestionar cotizaciones',
  ),
  
  'Compras' => array(
    'compras_ordenes_add'          => 'Crear órdenes de compra',
    'compras_ordenes_edit'         => 'Editar órdenes de compra',
    'compras_ordenes_consult'      => 'Consultar órdenes de compra',
    'compras_ordenes_delete'       => 'Cancelar órdenes de compra',
    'compras_recepcion'            => 'Recibir mercancía',
    'compras_autorizar_preordenes' => 'Autorizar o rechazar pre-órdenes de compra',
    'compras_preordenes_edit'      => 'Editar pre-órdenes pendientes',
    'compras_categorias'           => 'Gestionar categorías de insumos',
    'compras_documentos'           => 'Adjuntar documentos y comentarios en OC',
    'compras_pagos'                => 'Registrar pagos y marcar adeudos en OC',
    'compras_servicios_recurrentes' => 'Gestionar servicios recurrentes (internet, soporte, etc.)',
  ),
  
  'Producción' => array(
    'produccion_productos_add'     => 'Agregar productos',
    'produccion_productos_edit'    => 'Editar productos',
    'produccion_productos_consult' => 'Consultar productos',
    'produccion_formulaciones'     => 'Gestionar formulaciones',
    'produccion_ordenes'           => 'Gestionar órdenes de producción',
    'produccion_ver_costos'        => 'Ver costos y precios',
    'produccion_preordenes'        => 'Generar pre-órdenes de compra desde producción',
  ),
  
  'Almacén' => array(
    'almacen_inventario_consult' => 'Consultar inventario',
    'almacen_ajustes'            => 'Realizar ajustes de inventario',
    'almacen_entregas'           => 'Gestionar entregas',
    'almacen_movimientos'        => 'Ver movimientos',
    'almacen_insumos'            => 'Gestionar insumos',
  ),
  
  'Obras' => array(
    'obras_add'     => 'Crear obras',
    'obras_edit'    => 'Editar obras',
    'obras_consult' => 'Consultar obras',
    'obras_delete'  => 'Eliminar obras',
    'obras_pagos'   => 'Gestionar pagos de obras',
  ),
  
  'Contabilidad' => array(
    'contabilidad_cuentas'     => 'Gestionar catálogo de cuentas',
    'contabilidad_polizas'     => 'Crear pólizas contables',
    'contabilidad_nomina'      => 'Gestionar nómina',
    'contabilidad_reportes'    => 'Ver reportes financieros',
    'contabilidad_gastos'      => 'Registrar gastos',
    'contabilidad_ingresos'    => 'Registrar ingresos',
  ),
  
  'Reportes' => array(
    'reportes_ventas'      => 'Reportes de ventas',
    'reportes_compras'     => 'Reportes de compras',
    'reportes_inventario'  => 'Reportes de inventario',
    'reportes_produccion'  => 'Reportes de producción',
    'reportes_financieros' => 'Reportes financieros',
    'reportes_obras'       => 'Reportes de obras',
  ),
  
  'Dashboard' => array(
    'dashboard_main'       => 'Ver dashboard principal',
    'dashboard_ventas'     => 'Ver dashboard de ventas',
    'dashboard_produccion' => 'Ver dashboard de producción',
    'dashboard_almacen'    => 'Ver dashboard de almacén',
  ),
  
  'Reloj Checador' => array(
    'reloj_ver_dashboard'   => 'Ver dashboard del reloj checador',
    'reloj_sync_asistencias' => 'Sincronizar asistencias',
    'reloj_ver_reportes'    => 'Ver reportes de asistencia',
    'reloj_gestionar'       => 'Gestionar dispositivos y comandos',
    'reloj_sync_empleados_rh' => 'Sincronización forzada de empleados RH al reloj',
  ),
);