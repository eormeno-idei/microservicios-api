#!/bin/bash

# Script para ver logs de GameCore
# Uso: ./logs.sh [opciones]

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Colores para la salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

show_help() {
    echo -e "${BLUE}GameCore Log Viewer${NC}"
    echo ""
    echo "Uso: ./logs.sh [opciones]"
    echo ""
    echo "Opciones:"
    echo "  -h, --help         Mostrar esta ayuda"
    echo "  -v, --view [n]     Ver las últimas n líneas (por defecto: 50)"
    echo "  -t, --tail         Seguir el log en tiempo real"
    echo "  -c, --clear        Limpiar el archivo de log"
    echo "  -f, --file [name]  Especificar archivo de log (por defecto: laravel.log)"
    echo "  -s, --search [term] Buscar un término en los logs"
    echo "  -e, --errors       Mostrar solo errores"
    echo "  -w, --warnings     Mostrar solo advertencias"
    echo "  -p, --pail         Usar Laravel Pail para ver logs en tiempo real"
    echo ""
    echo "Ejemplos:"
    echo "  ./logs.sh -v 100              # Ver últimas 100 líneas"
    echo "  ./logs.sh -t                  # Seguir logs en tiempo real"
    echo "  ./logs.sh -s \"error\"          # Buscar 'error' en los logs"
    echo "  ./logs.sh -e                  # Ver solo errores"
    echo "  ./logs.sh -p                  # Usar Laravel Pail"
    echo ""
}

view_logs() {
    local lines=${1:-50}
    local file=${2:-laravel.log}
    local log_path="storage/logs/$file"

    if [ ! -f "$log_path" ]; then
        echo -e "${RED}Error: Archivo de log no encontrado: $log_path${NC}"
        exit 1
    fi

    echo -e "${GREEN}Mostrando últimas $lines líneas de: $file${NC}"
    echo ""
    tail -n "$lines" "$log_path" | while IFS= read -r line; do
        if echo "$line" | grep -qi "error\|exception\|fatal"; then
            echo -e "${RED}$line${NC}"
        elif echo "$line" | grep -qi "warning\|warn"; then
            echo -e "${YELLOW}$line${NC}"
        elif echo "$line" | grep -qi "info"; then
            echo -e "${BLUE}$line${NC}"
        else
            echo "$line"
        fi
    done
}

tail_logs() {
    local file=${1:-laravel.log}
    local log_path="storage/logs/$file"

    if [ ! -f "$log_path" ]; then
        echo -e "${RED}Error: Archivo de log no encontrado: $log_path${NC}"
        exit 1
    fi

    echo -e "${GREEN}Siguiendo log en tiempo real: $file${NC}"
    echo -e "${YELLOW}Presiona Ctrl+C para detener${NC}"
    echo ""
    
    tail -f "$log_path" | while IFS= read -r line; do
        if echo "$line" | grep -qi "error\|exception\|fatal"; then
            echo -e "${RED}$line${NC}"
        elif echo "$line" | grep -qi "warning\|warn"; then
            echo -e "${YELLOW}$line${NC}"
        elif echo "$line" | grep -qi "info"; then
            echo -e "${BLUE}$line${NC}"
        else
            echo "$line"
        fi
    done
}

clear_logs() {
    local file=${1:-laravel.log}
    local log_path="storage/logs/$file"

    if [ ! -f "$log_path" ]; then
        echo -e "${RED}Error: Archivo de log no encontrado: $log_path${NC}"
        exit 1
    fi

    read -p "¿Estás seguro de que deseas limpiar $file? (s/N): " confirm
    if [[ $confirm =~ ^[Ss]$ ]]; then
        > "$log_path"
        echo -e "${GREEN}Log limpiado exitosamente: $file${NC}"
    else
        echo -e "${YELLOW}Operación cancelada${NC}"
    fi
}

search_logs() {
    local term=$1
    local file=${2:-laravel.log}
    local log_path="storage/logs/$file"

    if [ ! -f "$log_path" ]; then
        echo -e "${RED}Error: Archivo de log no encontrado: $log_path${NC}"
        exit 1
    fi

    echo -e "${GREEN}Buscando '$term' en: $file${NC}"
    echo ""
    
    grep -i "$term" "$log_path" | while IFS= read -r line; do
        if echo "$line" | grep -qi "error\|exception\|fatal"; then
            echo -e "${RED}$line${NC}"
        elif echo "$line" | grep -qi "warning\|warn"; then
            echo -e "${YELLOW}$line${NC}"
        else
            echo "$line"
        fi
    done
}

filter_errors() {
    local file=${1:-laravel.log}
    local log_path="storage/logs/$file"

    if [ ! -f "$log_path" ]; then
        echo -e "${RED}Error: Archivo de log no encontrado: $log_path${NC}"
        exit 1
    fi

    echo -e "${RED}Mostrando solo errores de: $file${NC}"
    echo ""
    grep -iE "error|exception|fatal" "$log_path" | while IFS= read -r line; do
        echo -e "${RED}$line${NC}"
    done
}

filter_warnings() {
    local file=${1:-laravel.log}
    local log_path="storage/logs/$file"

    if [ ! -f "$log_path" ]; then
        echo -e "${RED}Error: Archivo de log no encontrado: $log_path${NC}"
        exit 1
    fi

    echo -e "${YELLOW}Mostrando solo advertencias de: $file${NC}"
    echo ""
    grep -iE "warning|warn" "$log_path" | while IFS= read -r line; do
        echo -e "${YELLOW}$line${NC}"
    done
}

use_pail() {
    echo -e "${GREEN}Iniciando Laravel Pail...${NC}"
    echo -e "${YELLOW}Presiona Ctrl+C para detener${NC}"
    echo ""
    php artisan pail --timeout=0
}

# Parse argumentos
FILE="laravel.log"
ACTION=""
PARAM=""

while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -v|--view)
            ACTION="view"
            PARAM=${2:-50}
            shift 2
            ;;
        -t|--tail)
            ACTION="tail"
            shift
            ;;
        -c|--clear)
            ACTION="clear"
            shift
            ;;
        -f|--file)
            FILE=$2
            shift 2
            ;;
        -s|--search)
            ACTION="search"
            PARAM=$2
            shift 2
            ;;
        -e|--errors)
            ACTION="errors"
            shift
            ;;
        -w|--warnings)
            ACTION="warnings"
            shift
            ;;
        -p|--pail)
            ACTION="pail"
            shift
            ;;
        *)
            echo -e "${RED}Opción desconocida: $1${NC}"
            show_help
            exit 1
            ;;
    esac
done

# Ejecutar acción
case $ACTION in
    view)
        view_logs "$PARAM" "$FILE"
        ;;
    tail)
        tail_logs "$FILE"
        ;;
    clear)
        clear_logs "$FILE"
        ;;
    search)
        if [ -z "$PARAM" ]; then
            echo -e "${RED}Error: Debes especificar un término de búsqueda${NC}"
            exit 1
        fi
        search_logs "$PARAM" "$FILE"
        ;;
    errors)
        filter_errors "$FILE"
        ;;
    warnings)
        filter_warnings "$FILE"
        ;;
    pail)
        use_pail
        ;;
    *)
        # Por defecto, mostrar las últimas 50 líneas
        view_logs 50 "$FILE"
        ;;
esac
