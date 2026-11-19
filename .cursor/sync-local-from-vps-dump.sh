#!/usr/bin/env bash
# Sincroniza SOLO la MariaDB local usando un volcado de datos ya descargado (referencia VPS).
# No conecta ni modifica el VPS ni phpMyAdmin.
set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DUMP="${VPS_DATA_DUMP:-/tmp/chisa-dump/vps_from_pma.sql.gz}"
DB_NAME="${CHISA_DB_NAME:-st32477_chisa}"
DB_USER="${CHISA_DB_USER:-chisa}"
DB_PASS="${CHISA_DB_PASS:-chisa}"

if [[ ! -f "$DUMP" ]]; then
  echo "No existe el volcado local: $DUMP"
  echo "Coloca ahí un export de datos (.sql.gz) que tú generes desde phpMyAdmin (solo lectura en el servidor)."
  exit 1
fi

mysql_cmd() { mysql -u"$DB_USER" -p"$DB_PASS" "$@"; }

echo "==> Aplicar alineación de esquema local (database/local_align_vps_reference.sql)"
mysql_cmd "$DB_NAME" < "$REPO_DIR/database/local_align_vps_reference.sql" || true
mysql_cmd "$DB_NAME" < "$REPO_DIR/database/nomina_detalle_iteracion2.sql" 2>/dev/null || true
mysql_cmd "$DB_NAME" < "$REPO_DIR/database/nomina_pago_parcial.sql" 2>/dev/null || true

echo "==> Vaciar tablas locales"
mysql_cmd -N "$DB_NAME" -e "
SELECT CONCAT('TRUNCATE TABLE \`', table_name, '\`;')
FROM information_schema.tables
WHERE table_schema='$DB_NAME' AND table_type='BASE TABLE'
" > /tmp/truncate_all.sql
{
  echo 'SET FOREIGN_KEY_CHECKS=0; SET UNIQUE_CHECKS=0;'
  cat /tmp/truncate_all.sql
} | mysql_cmd "$DB_NAME"

echo "==> Importar datos de referencia"
{
  echo 'SET FOREIGN_KEY_CHECKS=0; SET UNIQUE_CHECKS=0; SET SQL_MODE=NO_AUTO_VALUE_ON_ZERO;'
  zcat "$DUMP"
  echo 'SET FOREIGN_KEY_CHECKS=1;'
} | mysql_cmd --force "$DB_NAME"

echo "==> Verificación rápida"
mysql_cmd "$DB_NAME" -e "
SELECT 'administradores' AS t, COUNT(*) AS n FROM administradores
UNION ALL SELECT 'clientes', COUNT(*) FROM clientes
UNION ALL SELECT 'productos', COUNT(*) FROM productos
UNION ALL SELECT 'ordenes_venta', COUNT(*) FROM ordenes_venta;
"
echo "Listo. Revisa tablas que aún no existan en el repo (cotizaciones_comerciales, sucursales, etc.)."