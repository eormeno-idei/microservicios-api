#!/bin/bash
# Script para eliminar, migrar y seedear la base de datos en Laravel

# Función para mostrar ayuda
show_help() {
    cat << EOF
migrate.sh - Script para gestionar migraciones en Laravel

DESCRIPCIÓN:
    Este script elimina la base de datos SQLite actual, ejecuta las migraciones
    y carga los seeders para resetear completamente la base de datos.

USO:
    ./migrate.sh [OPCIÓN]

OPCIONES:
    -h, --help      Muestra esta ayuda y sale

EJEMPLOS:
    ./migrate.sh            # Ejecuta el proceso completo de migración
    ./migrate.sh --help     # Muestra esta ayuda
    ./migrate.sh -h         # Muestra esta ayuda

ARCHIVOS:
    database/database.sqlite    Base de datos SQLite que será recreada

AUTOR:
    Script de migración para proyecto Laravel
EOF
}

# Procesar argumentos de línea de comandos
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        *)
            echo "Opción desconocida: $1"
            echo "Usa -h o --help para ver las opciones disponibles."
            exit 1
            ;;
    esac
    shift
done

DB_PATH="database/database.sqlite"

echo "🔄 Iniciando proceso de migración..."
echo

# Eliminar base de datos existente
if [ -f "$DB_PATH" ]; then
  echo "🗑️  Eliminando base de datos existente: $DB_PATH"
  rm "$DB_PATH"
else
  echo "ℹ️  No se encontró base de datos existente: $DB_PATH"
fi

echo "📊 Ejecutando migraciones y seeders..."
php artisan migrate --force --seed

status=$?
if [ $status -ne 0 ]; then
  echo "❌ Error durante la migración o el seed. Código de salida: $status"
  exit $status
fi

echo
echo "✅ Migración y seed completados con éxito."
echo "🎉 La base de datos ha sido recreada correctamente."
