#!/usr/bin/env sh
set -e

# Clear any cached config/routes/views so runtime env is always respected.
php artisan optimize:clear

if [ "${RUN_MIGRATIONS}" = "true" ]; then
  php artisan migrate --force
fi

exec "$@"
