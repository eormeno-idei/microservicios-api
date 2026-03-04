#!/bin/bash

clear

# Function to get a value from .env file (non-commented lines)
get_env_value() {
    local key="$1"
    grep "^$key=" .env 2>/dev/null | head -1 | cut -d'=' -f2- | sed 's/^"\(.*\)"$/\1/'
}

env_db=$(get_env_value "DB_CONNECTION")

if [[ "$*" == *"-r"* ]]; then
    if [[ "$env_db" == "mysql" ]]; then
        # Get the .env DB values that do not start with '#'
        db=$(get_env_value "DB_DATABASE")
        user=$(get_env_value "DB_USERNAME")
        pass=$(get_env_value "DB_PASSWORD")

        echo "Removing Database: $db with $user privileges"
        mysql -u "$user" -p"$pass" -e "DROP DATABASE IF EXISTS $db; CREATE DATABASE $db;"
    fi

    if [[ "$env_db" == "sqlite" ]]; then
        # Remove the database
        rm -f database/database.sqlite
    fi

    php artisan migrate --force --seed
fi

# Check if port 8000 is already in use
if netstat -tuln 2>/dev/null | grep -q ":8000 " || ss -tuln 2>/dev/null | grep -q ":8000 "; then
    echo "Server is already running on port 8000"
    echo "Opening browser to http://127.0.0.1:8000"
    if grep -q Microsoft /proc/version 2>/dev/null || [ -n "$WSL_DISTRO_NAME" ]; then
        # WSL - use Windows command
        cmd.exe /c start "http://127.0.0.1:8000"
    elif command -v xdg-open > /dev/null; then
        firefox "http://127.0.0.1:8000" &
    elif command -v start > /dev/null; then
        start "http://127.0.0.1:8000"
    else
        echo "Cannot detect the web browser to launch automatically"
    fi
    exit 0
fi

# Clear cache before starting the server
echo "Clearing cache..."
php artisan cache:clear

# Open browser to the demo page
echo "Opening browser to http://127.0.0.1:8000"
if grep -q Microsoft /proc/version 2>/dev/null || [ -n "$WSL_DISTRO_NAME" ]; then
    # WSL - use Windows command
    cmd.exe /c start "http://127.0.0.1:8000"
elif command -v xdg-open > /dev/null; then
    firefox "http://127.0.0.1:8000" &
elif command -v start > /dev/null; then
    start "http://127.0.0.1:8000"
else
    echo "Cannot detect the web browser to launch automatically"
fi

# Start the Laravel server (this will block the terminal)
echo "Starting Laravel server..."
php artisan serve
