<?php
/**
 * Router for PHP's built-in web server so the CodeIgniter 3 ERP works with
 * clean URLs (mirrors the production .htaccess rewrite).
 *
 * Usage: php -S 0.0.0.0:8080 -t /workspace /workspace/.cursor/php-router.php
 */
$docroot = $_SERVER['DOCUMENT_ROOT'];
$path    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve existing static files (assets, uploads, images, etc.) directly,
// except PHP files under application/ or system/ which must never be exposed.
if ($path !== '/' && is_file($docroot . $path)) {
    if (preg_match('#^/(application|system)/.*\.php$#i', $path)) {
        http_response_code(403);
        echo 'Denied';
        return true;
    }
    return false; // let the built-in server serve the static asset
}

// Route everything else through the CodeIgniter front controller.
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $docroot . '/index.php';
require $docroot . '/index.php';
