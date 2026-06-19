<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//Login User/Admin
$route['admin'] = 'auth';
$route['authenticate'] = 'auth/authenticate';
$route['logout'] = 'auth/logout';

//Dashboard
$route['dashboard'] = 'dashboards/MainDashboard';

//Reloj Checador API (Proxy Local)
$route['api/reloj/sync_asistencias'] = 'api/ApiReloj/sync_asistencias';
$route['api/reloj/sync_asistencias_debug'] = 'api/ApiReloj/sync_asistencias_debug';
$route['api/reloj/comandos_pendientes/(:any)'] = 'api/ApiReloj/comandos_pendientes/$1';
$route['api/reloj/comando_resultado'] = 'api/ApiReloj/comando_resultado';
$route['api/reloj/status'] = 'api/ApiReloj/status';
// Monitor temporal de diagnóstico (sin BD)
$route['api/reloj/monitor'] = 'api/ApiReloj/ver_log_reloj';

//Reloj Checador Web (FASE 3)
$route['rh/RelojChecador'] = 'rh/RelojChecador/index';
$route['rh/RelojChecador/index'] = 'rh/RelojChecador/index';
$route['rh/RelojChecador/dashboard_stats_ajax'] = 'rh/RelojChecador/dashboard_stats_ajax';
$route['rh/RelojChecador/dispositivos'] = 'rh/RelojChecador/dispositivos';
$route['rh/RelojChecador/search_dispositivos'] = 'rh/RelojChecador/search_dispositivos';
$route['rh/RelojChecador/dispositivo_detail'] = 'rh/RelojChecador/dispositivo_detail';
$route['rh/RelojChecador/guardar_dispositivo'] = 'rh/RelojChecador/guardar_dispositivo';
$route['rh/RelojChecador/eliminar_dispositivo'] = 'rh/RelojChecador/eliminar_dispositivo';
$route['rh/RelojChecador/regenerar_token'] = 'rh/RelojChecador/regenerar_token';
$route['rh/RelojChecador/comandos'] = 'rh/RelojChecador/comandos';
$route['rh/RelojChecador/search_comandos'] = 'rh/RelojChecador/search_comandos';
$route['rh/RelojChecador/encolar_comando'] = 'rh/RelojChecador/encolar_comando';
$route['rh/RelojChecador/vaciar_todos_comandos'] = 'rh/RelojChecador/vaciar_todos_comandos';
$route['rh/RelojChecador/sync_log'] = 'rh/RelojChecador/sync_log';
$route['rh/RelojChecador/search_sync_log'] = 'rh/RelojChecador/search_sync_log';
$route['rh/RelojChecador/reporte_diario'] = 'rh/RelojChecador/reporte_diario';
$route['rh/RelojChecador/search_asistencias_diario'] = 'rh/RelojChecador/search_asistencias_diario';
$route['rh/RelojChecador/reporte_mensual'] = 'rh/RelojChecador/reporte_mensual';
$route['rh/RelojChecador/search_asistencias_mensual'] = 'rh/RelojChecador/search_asistencias_mensual';
$route['rh/RelojChecador/asistencia_detalle_dia'] = 'rh/RelojChecador/asistencia_detalle_dia';
$route['rh/RelojChecador/exportar_diario_csv'] = 'rh/RelojChecador/exportar_diario_csv';
$route['rh/RelojChecador/sync_empleados_rh'] = 'rh/RelojChecador/sync_empleados_rh';
$route['rh/RelojChecador/aplicar_migracion_sync_empleados_rh'] = 'rh/RelojChecador/aplicar_migracion_sync_empleados_rh';
$route['rh/RelojChecador/preview_sync_empleados_rh'] = 'rh/RelojChecador/preview_sync_empleados_rh';
$route['rh/RelojChecador/ejecutar_sync_empleados_rh'] = 'rh/RelojChecador/ejecutar_sync_empleados_rh';
$route['rh/RelojChecador/vaciar_cola_sync_empleados_rh'] = 'rh/RelojChecador/vaciar_cola_sync_empleados_rh';
$route['rh/RelojChecador/reencolar_cola_sync_empleados_rh'] = 'rh/RelojChecador/reencolar_cola_sync_empleados_rh';

//Errors
$route['deny'] = 'Errors/deny';
