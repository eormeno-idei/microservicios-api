#!/bin/bash
# Script para eliminar, migrar y seedear la base de datos en Laravel

DB_PATH="database/database.sqlite"

if [ -f "$DB_PATH" ]; then
  rm "$DB_PATH"
fi

# el --forced lo que hace es que no pregunte nada y continue con la migracion
php artisan migrate --force --seed

status=$?
if [ $status -ne 0 ]; then
  echo "Error durante la migración o el seed. Código de salida: $status"
  exit $status
fi

echo "Migración y seed completados con éxito."
