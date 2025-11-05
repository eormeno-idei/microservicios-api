#!/bin/bash

# Script Bash para eliminar una rama (branch) de Git de forma local y remota
# Uso: ./delbranch.sh -b nombre-rama [-r origin] [-f]

# Colores para la salida
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Variables por defecto
REMOTE="origin"
FORCE=false

# Función para mostrar mensajes con color
print_color() {
    local message=$1
    local color=$2
    echo -e "${color}${message}${NC}"
}

# Función para mostrar el uso
show_help() {
    echo "Uso: $0 -b nombre-rama [-r remote] [-f]"
    echo ""
    echo "Opciones:"
    echo "  -b              Nombre de la rama a eliminar (requerido)"
    echo "  -r              Nombre del remoto (por defecto: origin)"
    echo "  -f              Fuerza la eliminación sin pedir confirmación"
    echo "  -h              Muestra esta ayuda"
    echo ""
    echo "Ejemplos:"
    echo "  $0 -b feature/nueva-funcionalidad"
    echo "  $0 -b feature/nueva-funcionalidad -f"
    echo "  $0 -b feature/nueva-funcionalidad -r upstream"
}

# Parsear argumentos
while getopts "b:r:fh" opt; do
    case $opt in
        b)
            BRANCH_NAME="$OPTARG"
            ;;
        r)
            REMOTE="$OPTARG"
            ;;
        f)
            FORCE=true
            ;;
        h)
            show_help
            exit 0
            ;;
        *)
            show_help
            exit 1
            ;;
    esac
done

# Validar que se proporciono el nombre de la rama
if [ -z "$BRANCH_NAME" ]; then
    print_color "Error: Debes especificar el nombre de la rama con -b" "$RED"
    show_help
    exit 1
fi

# Verificar que estamos en un repositorio Git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    print_color "Error: No estamos en un repositorio Git valido" "$RED"
    exit 1
fi

# Obtener la rama actual
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

# Validar que no estamos intentando eliminar la rama actual
if [ "$CURRENT_BRANCH" = "$BRANCH_NAME" ]; then
    print_color "Error: No puedes eliminar la rama actual ($BRANCH_NAME)" "$RED"
    print_color "Cambia a otra rama primero con: git checkout [otra-rama]" "$CYAN"
    exit 1
fi

# Confirmar la acción si no se usa -f (force)
if [ "$FORCE" = false ]; then
    print_color "\nAdvertencia: Vas a eliminar la rama '$BRANCH_NAME'" "$YELLOW"
    print_color "   - Local: Si" "$CYAN"
    print_color "   - Remoto ($REMOTE): Si" "$CYAN"
    read -p "$(print_color '¿Deseas continuar? (s/n): ' "$YELLOW")" -n 1 -r confirmation
    echo
    
    if [[ ! $confirmation =~ ^[sS]$ ]]; then
        print_color "Operacion cancelada" "$YELLOW"
        exit 0
    fi
fi

print_color "\nProcesando eliminacion de rama '$BRANCH_NAME'..." "$CYAN"

# Paso 1: Eliminar la rama de forma local
print_color "\nEliminando rama local..." "$CYAN"
if git branch -d "$BRANCH_NAME" 2>/dev/null; then
    print_color "Rama local eliminada" "$GREEN"
else
    # Si falla con -d, intentar con -D (force)
    if git branch -D "$BRANCH_NAME" 2>/dev/null; then
        print_color "Rama local eliminada (con force)" "$GREEN"
    else
        print_color "Error al eliminar la rama local" "$RED"
        exit 1
    fi
fi

# Paso 2: Eliminar la rama del repositorio remoto
print_color "\nEliminando rama remota ($REMOTE)..." "$CYAN"
if git push "$REMOTE" --delete "$BRANCH_NAME" 2>/dev/null; then
    print_color "Rama remota eliminada de $REMOTE" "$GREEN"
else
    # Verificar si la rama existe en remoto
    if git branch -r | grep -q "$REMOTE/$BRANCH_NAME"; then
        print_color "Error al eliminar la rama remota" "$RED"
        exit 1
    else
        print_color "La rama remota ya no existe en $REMOTE" "$YELLOW"
    fi
fi

# Resumen final
print_color "\n========================================" "$GREEN"
print_color "Rama eliminada correctamente" "$GREEN"
print_color "========================================" "$GREEN"
print_color "\nResumen:" "$CYAN"
print_color "   - Rama: $BRANCH_NAME" "$CYAN"
print_color "   - Local: Eliminada" "$GREEN"
print_color "   - Remoto ($REMOTE): Eliminada" "$GREEN"
print_color "\n" "$GREEN"

exit 0