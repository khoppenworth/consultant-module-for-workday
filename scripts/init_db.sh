#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   DB_HOST=127.0.0.1 DB_PORT=3306 DB_NAME=consultant_db DB_USER=consultant DB_PASS=consultantpass ./scripts/init_db.sh

: "${DB_HOST:=127.0.0.1}"
: "${DB_PORT:=3306}"
: "${DB_NAME:=consultant_db}"
: "${DB_USER:=consultant}"
: "${DB_PASS:=}"

mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < db/schema.sql
echo "Schema applied."
