#!/usr/bin/env bash
#
# Cloud Agent start script for the Chisa ERP.
# Runs on every boot: brings up MariaDB and waits until it accepts connections.
# The web server itself runs as a visible terminal (see environment.json).
#
set -euo pipefail

echo "==> Starting MariaDB"
sudo service mariadb start

echo "==> Waiting for MariaDB to accept connections"
for _ in $(seq 1 60); do
  if sudo mysqladmin ping >/dev/null 2>&1; then
    echo "    MariaDB is ready."
    exit 0
  fi
  sleep 1
done

echo "MariaDB did not become ready in time." >&2
exit 1
