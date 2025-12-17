<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Permissions Configuration
 * Define all system permissions organized by module
 */

$config['permissions'] = array(
  'Administradores' => array(
    'user_add'     => 'Agregar usuarios',
    'user_edit'    => 'Editar usuarios',
    'user_consult' => 'Consultar usuarios',
  ),
  'Clientes' => array(
    'customer_add'     => 'Agregar clientes',
    'customer_edit'    => 'Editar clientes',
    'customer_consult' => 'Consultar clientes',
  ),
  'Proveedores' => array(
    'proveedores_add'     => 'Agregar proveedores',
    'proveedores_edit'    => 'Editar proveedores',
    'proveedores_consult' => 'Consultar proveedores',
  ),
  'MateriaPrima' => array(
    'materia_prima_add'     => 'Agregar materia prima',
    'materia_prima_edit'    => 'Editar materia prima',
    'materia_prima_consult' => 'Consultar materia prima',
  ),
  'Cobranza' => array(
    'cobranza_add'     => 'Agregar cobranza',
    'cobranza_edit'    => 'Editar cobranza',
    'cobranza_consult' => 'Consultar cobranza',
  ),
  'Compras' => array(
    'compras_add'     => 'Agregar compras',
    'compras_edit'    => 'Editar compras',
    'compras_consult' => 'Consultar compras',
  ),
  'Ventas' => array(
    'ventas_add'     => 'Agregar ventas',
    'ventas_edit'    => 'Editar ventas',
    'ventas_consult' => 'Consultar ventas',
  ),
  'Reportes' => array(
    'reportes_add'     => 'Agregar reportes',
    'reportes_edit'    => 'Editar reportes',
    'reportes_consult' => 'Consultar reportes',
  ),
  'Mensajes' => array(
    'mensajes_add'     => 'Agregar mensajes',
    'mensajes_edit'    => 'Editar mensajes',
    'mensajes_consult' => 'Consultar mensajes',
  ),  
);