<?php
/**
 * Datos de demo: fiscales, saldos de obras, parcialidades y seguimientos CRM.
 * Idempotente. No DROP/TRUNCATE. Uso: php cargar_demo_cartera.php
 */
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
$db = [];
require dirname(__DIR__, 3) . '/application/config/database.php';
$c = $db['default'];
$m = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
if ($m->connect_error) { fwrite(STDERR, "Conexion fallida\n"); exit(1); }
$m->set_charset('utf8');

$m->query("CREATE TABLE IF NOT EXISTS `obras_parcialidades` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `obra_id` INT NOT NULL,
  `numero` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `fecha_programada` DATE NOT NULL,
  `monto` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `estatus` VARCHAR(20) NOT NULL DEFAULT 'Pendiente',
  `pago_id` INT NULL,
  `notas` VARCHAR(255) NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_obra` (`obra_id`,`activo`),
  KEY `idx_fecha` (`fecha_programada`,`estatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8");

$m->query("UPDATE clientes SET regimen_fiscal='601', uso_cfdi='G03', email_facturacion=COALESCE(NULLIF(email_facturacion,''), email),
  codigo_postal=IFNULL(NULLIF(codigo_postal,''),'44130') WHERE id=2");
$m->query("UPDATE clientes SET razon_social='Empresa de Prueba S.A.', rfc='EPR010101XX1', regimen_fiscal='601',
  uso_cfdi='G03', email='compras@empresaprueba.mx', email_facturacion='facturacion@empresaprueba.mx',
  codigo_postal='44100', telefono='3344556677', limite_credito=150000, dias_credito=30 WHERE id=9");

$m->query("UPDATE obras SET estatus='Aprobada', saldo_pendiente=GREATEST(total-IFNULL(total_pagado,0),0)
  WHERE id=1 AND activo=1");
$m->query("UPDATE obras SET saldo_pendiente=GREATEST(total-IFNULL(total_pagado,0),0) WHERE id=2 AND activo=1");

$ex = $m->query("SELECT COUNT(*) n FROM obras_parcialidades WHERE obra_id=2 AND activo=1")->fetch_assoc();
if ((int)$ex['n'] === 0) {
    $m->query("INSERT INTO obras_parcialidades (obra_id, numero, fecha_programada, monto, estatus, notas, activo) VALUES
      (2, 1, '2026-08-15', 10000.00, 'Vencida', '2ª exhibición — vencida (demo)', 1),
      (2, 2, CURDATE(), 15000.00, 'Pendiente', '3ª exhibición — vence hoy (demo)', 1),
      (2, 3, DATE_ADD(CURDATE(), INTERVAL 20 DAY), 14760.00, 'Pendiente', 'Liquidación programada (demo)', 1)");
}
$ex1 = $m->query("SELECT COUNT(*) n FROM obras_parcialidades WHERE obra_id=1 AND activo=1")->fetch_assoc();
if ((int)$ex1['n'] === 0) {
    $m->query("INSERT INTO obras_parcialidades (obra_id, numero, fecha_programada, monto, estatus, notas, activo) VALUES
      (1, 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), 2900.00, 'Pendiente', 'Anticipo 50% (demo)', 1),
      (1, 2, DATE_ADD(CURDATE(), INTERVAL 25 DAY), 2900.00, 'Pendiente', 'Saldo contra avance (demo)', 1)");
}

$seg = $m->query("SELECT COUNT(*) n FROM seguimientos_cliente WHERE cliente_id IN (2,9)")->fetch_assoc();
if ((int)$seg['n'] === 0) {
    $m->query("INSERT INTO seguimientos_cliente (cliente_id, tipo, fecha, asunto, notas, usuario_id, fecha_registro) VALUES
      (2, 'Llamada', NOW() - INTERVAL 2 DAY, 'Cobranza Hospital Luz', 'Recordatorio de parcialidad vencida $10,000. Quedaron de transferir esta semana.', 1, NOW()),
      (2, 'WhatsApp', NOW() - INTERVAL 1 DAY, 'Envío de estado de cuenta', 'Se envió desglose OV-2025-0015 + OB-00002. RFC STB150820GH1.', 1, NOW()),
      (9, 'Correo', NOW() - INTERVAL 5 DAY, 'OV-2026-0004 pendiente', 'Saldo $6,960 Confirmada. Pedir fecha de pago.', 1, NOW())");
}

foreach ([2, 9] as $cid) {
    $ov = $m->query("SELECT COALESCE(SUM(saldo_pendiente),0) s FROM ordenes_venta WHERE cliente_id={$cid} AND estatus NOT IN ('Cotización','Cancelada')")->fetch_assoc();
    $ob = $m->query("SELECT COALESCE(SUM(saldo_pendiente),0) s FROM obras WHERE cliente_id={$cid} AND activo=1 AND estatus<>'Cancelada'")->fetch_assoc();
    $saldo = round((float)$ov['s'] + (float)$ob['s'], 2);
    $m->query("UPDATE clientes SET saldo_pendiente={$saldo} WHERE id={$cid}");
    echo "cliente {$cid} saldo={$saldo}\n";
}

echo "OK demo cartera\n";
