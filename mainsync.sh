#!/bin/bash

# Script para sincronizar archivos desde main hacia el branch actual
# Reglas:
# - Archivos modificados en main: se sobreescriben sin preguntar
# - Archivos nuevos en main: se agregan al branch
# - Archivos únicos del branch: se mantienen intactos

# Guardar la ruta completa del script
SCRIPT_PATH="$(realpath "$0")"
SCRIPT_DIR="$(dirname "$SCRIPT_PATH")"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Archivos que se deben ignorar durante la sincronización
IGNORE_FILES=(
    "app/Models/User.php",
    "database/factories/UserFactory.php",
    "database/seeders/DatabaseSeeder.php"
    "routes/api.php"
    "routes/web.php"
    "config/app.php"
    "config/database.php"
    ".env"
)

# Función para verificar si un archivo debe ser ignorado
is_ignored_file() {
    local file="$1"
    for ignored in "${IGNORE_FILES[@]}"; do
        if [[ "$file" == "$ignored" ]]; then
            return 0  # Es un archivo ignorado
        fi
    done
    return 1  # No es un archivo ignorado
}

# Función para mostrar ayuda
show_help() {
    echo -e "${BLUE}Uso: $(basename "$0") [branch-destino]${NC}"
    echo ""
    echo "Descripción:"
    echo "  Sincroniza archivos desde 'main' hacia el branch especificado"
    echo ""
    echo "Reglas de sincronización:"
    echo "  • Archivos modificados en main → Se sobreescriben automáticamente"
    echo "  • Archivos nuevos en main → Se agregan al branch"
    echo "  • Archivos únicos del branch → Se mantienen intactos"
    echo "  • Archivos en la lista de ignorados → Se omiten completamente"
    echo ""
    echo "Parámetros:"
    echo "  branch-destino  Branch al cual sincronizar (default: branch actual)"
    echo ""
    echo "Ejemplos:"
    echo "  $(basename "$0")              # Sincroniza main → branch actual"
    echo "  $(basename "$0") develop     # Sincroniza main → develop"
    echo "  $(basename "$0") --help      # Muestra esta ayuda"
}

# Verificar si se solicita ayuda
if [[ "$1" == "--help" || "$1" == "-h" ]]; then
    show_help
    exit 0
fi

# Configurar branches
SOURCE_BRANCH="main"
TARGET_BRANCH=${1:-$(git branch --show-current)}

echo -e "${CYAN}=== Sincronización desde Main ===${NC}"
echo -e "${YELLOW}Branch origen: $SOURCE_BRANCH${NC}"
echo -e "${YELLOW}Branch destino: $TARGET_BRANCH${NC}"
echo ""

# Verificar que estamos en un repositorio git
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo -e "${RED}Error: No estás en un repositorio Git${NC}"
    exit 1
fi

# Verificar que el branch main existe
if ! git show-ref --verify --quiet refs/heads/$SOURCE_BRANCH && ! git show-ref --verify --quiet refs/remotes/origin/$SOURCE_BRANCH; then
    echo -e "${RED}Error: El branch '$SOURCE_BRANCH' no existe${NC}"
    exit 1
fi

# Cambiar al branch destino si no estamos en él
CURRENT_BRANCH=$(git branch --show-current)
if [[ "$CURRENT_BRANCH" != "$TARGET_BRANCH" ]]; then
    echo -e "${YELLOW}Cambiando al branch: $TARGET_BRANCH${NC}"

    # Verificar si el branch destino existe
    if ! git show-ref --verify --quiet refs/heads/$TARGET_BRANCH && ! git show-ref --verify --quiet refs/remotes/origin/$TARGET_BRANCH; then
        echo -e "${RED}Error: El branch '$TARGET_BRANCH' no existe${NC}"
        exit 1
    fi

    git checkout $TARGET_BRANCH
    if [[ $? -ne 0 ]]; then
        echo -e "${RED}Error: No se pudo cambiar al branch '$TARGET_BRANCH'${NC}"
        exit 1
    fi
fi

# Fetch para asegurar que tenemos los últimos cambios
echo -e "${YELLOW}Obteniendo últimos cambios de main...${NC}"
git fetch origin $SOURCE_BRANCH

# Determinar la referencia del branch origen
if git show-ref --verify --quiet refs/heads/$SOURCE_BRANCH; then
    SOURCE_REF=$SOURCE_BRANCH
else
    SOURCE_REF=origin/$SOURCE_BRANCH
fi

# Obtener todos los archivos que están en main
echo -e "${YELLOW}Analizando archivos en main...${NC}"
MAIN_FILES=$(git ls-tree -r --name-only $SOURCE_REF)

# Categorizar archivos
MODIFIED_FILES=""
NEW_FILES=""
UNCHANGED_FILES=""
IGNORED_FILES=""
ERROR_COUNT=0

echo -e "${BLUE}Categorizando archivos...${NC}"

while IFS= read -r file; do
    if [[ -n "$file" ]]; then
        # Verificar si el archivo debe ser ignorado
        if is_ignored_file "$file"; then
            IGNORED_FILES="$IGNORED_FILES$file\n"
            echo -e "  ${CYAN}Ignorado: $file${NC}"
        elif [[ -f "$file" ]]; then
            # El archivo existe en ambos branches, verificar si es diferente
            if ! git diff --quiet HEAD $SOURCE_REF -- "$file" 2>/dev/null; then
                MODIFIED_FILES="$MODIFIED_FILES$file\n"
                echo -e "  ${YELLOW}Modificado: $file${NC}"
            else
                UNCHANGED_FILES="$UNCHANGED_FILES$file\n"
            fi
        else
            # El archivo no existe en el branch actual, es nuevo desde main
            NEW_FILES="$NEW_FILES$file\n"
            echo -e "  ${GREEN}Nuevo: $file${NC}"
        fi
    fi
done <<< "$MAIN_FILES"

# Remover últimos \n
MODIFIED_FILES=$(echo -e "$MODIFIED_FILES" | sed '/^$/d')
NEW_FILES=$(echo -e "$NEW_FILES" | sed '/^$/d')
UNCHANGED_FILES=$(echo -e "$UNCHANGED_FILES" | sed '/^$/d')
IGNORED_FILES=$(echo -e "$IGNORED_FILES" | sed '/^$/d')

# Mostrar resumen
echo ""
echo -e "${CYAN}=== RESUMEN DE SINCRONIZACIÓN ===${NC}"

if [[ -n "$MODIFIED_FILES" ]]; then
    echo -e "${YELLOW}Archivos que se sobreescribán ($(echo -e "$MODIFIED_FILES" | wc -l)):${NC}"
    echo -e "$MODIFIED_FILES" | sed 's/^/  ▶ /'
    echo ""
fi

if [[ -n "$NEW_FILES" ]]; then
    echo -e "${GREEN}Archivos nuevos que se agregarán ($(echo -e "$NEW_FILES" | wc -l)):${NC}"
    echo -e "$NEW_FILES" | sed 's/^/  ➕ /'
    echo ""
fi

if [[ -n "$UNCHANGED_FILES" ]]; then
    echo -e "${BLUE}Archivos sin cambios ($(echo -e "$UNCHANGED_FILES" | wc -l)):${NC}"
    echo -e "  (No se modificarán)"
    echo ""
fi

if [[ -n "$IGNORED_FILES" ]]; then
    echo -e "${CYAN}Archivos ignorados ($(echo -e "$IGNORED_FILES" | wc -l)):${NC}"
    echo -e "$IGNORED_FILES" | sed 's/^/  🚫 /'
    echo ""
fi

# Verificar si hay algo que hacer
if [[ -z "$MODIFIED_FILES" && -z "$NEW_FILES" ]]; then
    echo -e "${GREEN}✓ El branch ya está sincronizado con main${NC}"
    exit 0
fi

echo ""
echo -e "${CYAN}Iniciando sincronización automática...${NC}"

# Procesar archivos modificados
if [[ -n "$MODIFIED_FILES" ]]; then
    echo -e "${YELLOW}Sobreescribiendo archivos modificados...${NC}"
    while IFS= read -r file; do
        if [[ -n "$file" ]]; then
            echo -e "  ${YELLOW}Sobreescribiendo: $file${NC}"
            git checkout $SOURCE_REF -- "$file"
            if [[ $? -ne 0 ]]; then
                echo -e "  ${RED}Error sobreescribiendo: $file${NC}"
                ((ERROR_COUNT++))
            else
                git add "$file"
            fi
        fi
    done <<< "$MODIFIED_FILES"
fi

# Procesar archivos nuevos
if [[ -n "$NEW_FILES" ]]; then
    echo -e "${GREEN}Agregando archivos nuevos...${NC}"
    while IFS= read -r file; do
        if [[ -n "$file" ]]; then
            echo -e "  ${GREEN}Agregando: $file${NC}"

            # Crear directorio padre si no existe
            file_dir=$(dirname "$file")
            if [[ "$file_dir" != "." ]]; then
                mkdir -p "$file_dir"
            fi

            git checkout $SOURCE_REF -- "$file"
            if [[ $? -ne 0 ]]; then
                echo -e "  ${RED}Error agregando: $file${NC}"
                ((ERROR_COUNT++))
            else
                git add "$file"
            fi
        fi
    done <<< "$NEW_FILES"
fi

# Verificar si hubo errores
if [[ $ERROR_COUNT -gt 0 ]]; then
    echo ""
    echo -e "${RED}Se encontraron $ERROR_COUNT errores durante la sincronización${NC}"
    exit 1
fi

# Mostrar estado final
echo ""
echo -e "${GREEN}✓ Sincronización completada exitosamente${NC}"
echo -e "${BLUE}Estado del repositorio:${NC}"
git status --short

# Mostrar instrucciones para commit manual
echo ""
TOTAL_CHANGES=$(($(echo -e "$MODIFIED_FILES" | wc -l) + $(echo -e "$NEW_FILES" | wc -l)))
if [[ $TOTAL_CHANGES -gt 0 ]]; then
    echo -e "${YELLOW}Los cambios están listos para commit manual.${NC}"
    echo -e "${BLUE}Para crear el commit, ejecuta:${NC}"
    echo -e "  ${CYAN}git commit -m \"Sync from main: $TOTAL_CHANGES files updated\"${NC}"
    echo ""
    echo -e "${BLUE}Resumen de cambios:${NC}"
    if [[ -n "$MODIFIED_FILES" ]]; then
        echo -e "${YELLOW}Archivos modificados ($(echo -e "$MODIFIED_FILES" | wc -l)):${NC}"
        echo -e "$MODIFIED_FILES" | sed 's/^/  - /'
    fi
    if [[ -n "$NEW_FILES" ]]; then
        echo -e "${GREEN}Archivos nuevos ($(echo -e "$NEW_FILES" | wc -l)):${NC}"
        echo -e "$NEW_FILES" | sed 's/^/  - /'
    fi
fi

echo ""
echo -e "${CYAN}🎉 ¡Sincronización desde main completada!${NC}"
