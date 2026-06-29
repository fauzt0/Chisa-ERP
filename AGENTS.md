# AGENTS.md

## Cursor Cloud specific instructions

### What this is
Chisa-ERP — a **CodeIgniter 3.1.13** ERP web app running on **PHP 7.4** with a **MariaDB/MySQL**
database (`mysqli` driver). Server-rendered MVC with Bootstrap/AdminLTE/DataTables/jQuery.
Front controller is `index.php`; module controllers live under `application/controllers/<module>/`.
The startup update script installs PHP deps with `composer install` (provides
`phpoffice/phpspreadsheet`, used by the Producción module). System packages (PHP 7.4, MariaDB,
Composer) are provisioned in the VM image, not by the update script.

### Database (required to run the app)
- The committed `application/config/database.php` already targets `localhost` with db/user
  `st32477_chisa` and a password — so **no DB config override is needed** as long as a local
  MariaDB has a matching database + user. Set it up once per fresh VM:
  - Start MariaDB if not running: `sudo mysqld_safe --datadir=/var/lib/mysql &`
    (init the datadir first only if missing: `sudo mariadb-install-db --user=mysql --datadir=/var/lib/mysql`).
  - Create db/user and import the seed dump:
    ```
    sudo mysql -e "CREATE DATABASE IF NOT EXISTS st32477_chisa CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
      CREATE USER IF NOT EXISTS 'st32477_chisa'@'localhost' IDENTIFIED BY 'hADJXjtLjYp4ykTtRzEG';
      GRANT ALL PRIVILEGES ON st32477_chisa.* TO 'st32477_chisa'@'localhost'; FLUSH PRIVILEGES;"
    sudo mysql st32477_chisa < database/respaldo_chisa_desarrollo_2026_01_21.sql
    ```
  - `database/respaldo_chisa_desarrollo_2026_01_21.sql` is the base schema+data. Other files in
    `database/` are incremental `ALTER`/feature scripts; import them only if a feature needs them.

### base_url override (required for local runs)
`application/config/config.php` hardcodes `base_url` to the production URL, and the app defaults to
the `development` environment (`index.php` sets `CI_ENV=development` unless overridden). CodeIgniter
auto-loads `application/config/development/config.php` on top of the main config, so create that file
(it is gitignored — do NOT commit it, because the production server also runs as `development` and
would pick it up):
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$config['base_url'] = 'http://localhost:8080/';
```

### Running the dev server
PHP's built-in server needs a router: without one it 404s clean URLs and would funnel static assets
through CodeIgniter. Use a `server.php` router at the repo root (serves existing files directly,
routes everything else to `index.php`):
```php
<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/' . ltrim(urldecode($path), '/');
if ($path !== '/' && is_file($file)) { return false; }
require __DIR__ . '/index.php';
```
Then run: `php -S 0.0.0.0:8080 server.php`
- Login page: `http://localhost:8080/admin` — Dashboard: `http://localhost:8080/dashboard`.
- The repo `/` default controller is CodeIgniter's `welcome` placeholder; real entry is `/admin`.

### Auth / test login
- 2FA is **skipped when `ENVIRONMENT === 'development'`** (the default), so local login only needs
  username + password.
- Seed test account (full permissions): `soporte2@especialistasweb.com.mx` / `Prueba123@`.

### Lint / test
- No application PHPUnit suite exists (`tests/` is absent; `composer test:coverage` points at
  missing `tests/travis/sqlite.phpunit.xml`). Lint individual PHP files with `php -l <file>`.

### Misc
- The root `update_*.py` scripts are one-off code generators that patch a single Producción view;
  they are not part of environment setup or runtime.
