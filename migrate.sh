#!/bin/bash

# Script para eliminar, migrar y seedear la base de datos en Laravel

clear

# Función para obtener un valor del archivo .env (líneas no comentadas)
get_env_value() {
    local key="$1"
    grep "^$key=" .env 2>/dev/null | head -1 | cut -d'=' -f2- | sed 's/^"\(.*\)"$/\1/'
}

env_db=$(get_env_value "DB_CONNECTION")

if [[ "$*" == *"-r"* ]]; then

    if [[ "$env_db" == "mysql" ]]; then
        # Obtener los valores de la base de datos en .env que no comienzan con '#'
        db=$(get_env_value "DB_DATABASE")
        user=$(get_env_value "DB_USERNAME")
        pass=$(get_env_value "DB_PASSWORD")

        echo "Removing Database: $db with $user privileges"
        mysql -u "$user" -p"$pass" -e "DROP DATABASE IF EXISTS $db; CREATE DATABASE $db;"
    fi

    if [[ "$env_db" == "sqlite" ]]; then
        # Eliminar la base de datos SQLite si existe
        rm -f database/database.sqlite
    fi

    php artisan migrate --force --seed
fi

# DB_PATH="database/database.sqlite"

# if [ -f "$DB_PATH" ]; then
#   rm "$DB_PATH"
# fi

# php artisan migrate --force --seed

# status=$?
# if [ $status -ne 0 ]; then
#   echo "Error durante la migración o el seed. Código de salida: $status"
#   exit $status
# fi

echo "Migración y seed completados con éxito."
