<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| LOCAL DEVELOPMENT CONFIG OVERRIDES (Cloud Agent / local)
| -------------------------------------------------------------------
| This file is copied to application/config/development/config.php by
| .cursor/install.sh. It is merged over application/config/config.php
| when ENVIRONMENT === 'development' (see system/core/Common.php
| get_config()). Only the keys that must differ for local development
| are set here.
*/

// Derive base_url from the actual request Host header so the app works on
// http://localhost:<port> for local dev and on any proxied/forwarded host,
// instead of redirecting to the production domain. CodeIgniter's built-in
// empty-base_url auto-detection uses SERVER_ADDR and drops the port, which
// breaks redirects and asset URLs when served on a non-standard port.
if ( ! empty($_SERVER['HTTP_HOST']))
{
	$scheme = ( ! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
	$config['base_url'] = $scheme.'://'.$_SERVER['HTTP_HOST'].'/';
}
else
{
	$config['base_url'] = '';
}
