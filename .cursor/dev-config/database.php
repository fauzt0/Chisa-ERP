<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| LOCAL DEVELOPMENT DATABASE SETTINGS (Cloud Agent / local)
| -------------------------------------------------------------------
| This file is copied to application/config/development/database.php by
| .cursor/install.sh. It overrides application/config/database.php when
| ENVIRONMENT === 'development' and points the ERP at the local MariaDB
| instance provisioned by the Cloud Agent environment.
|
| Credentials can be overridden via environment variables so the same
| file works across machines without edits.
*/
$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => getenv('CHISA_DB_HOST') ?: '127.0.0.1',
	'username' => getenv('CHISA_DB_USER') ?: 'chisa',
	'password' => getenv('CHISA_DB_PASS') ?: 'chisa',
	'database' => getenv('CHISA_DB_NAME') ?: 'st32477_chisa',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);
