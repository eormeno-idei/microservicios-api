#!/bin/bash

# Script para ejecutar php artisan test con output compacto y con colores
# Filtra stacktraces largos y mantiene solo la información esencial

# Definir colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
BOLD='\033[1m'
NC='\033[0m' # No Color

echo -e "${CYAN}🧪 Ejecutando tests con output compacto...${NC}"

# Ejecutar php artisan test y filtrar la salida manteniendo información importante
php artisan test 2>&1 | python3 -c "
import sys
import re

# Definir códigos de color
RED = '\033[0;31m'
GREEN = '\033[0;32m'
YELLOW = '\033[1;33m'
BLUE = '\033[0;34m'
MAGENTA = '\033[0;35m'
CYAN = '\033[0;36m'
WHITE = '\033[1;37m'
BOLD = '\033[1m'
NC = '\033[0m'  # No Color

skip_stack = False

def colorize_line(line):
    # Colorear tests PASS
    if 'PASS' in line:
        line = re.sub(r'PASS', f'{GREEN}PASS{NC}', line)

    # Colorear tests FAIL
    if 'FAIL' in line:
        line = re.sub(r'FAIL', f'{RED}FAIL{NC}', line)

    # Colorear checkmarks y X
    line = re.sub(r'✓', f'{GREEN}✓{NC}', line)
    line = re.sub(r'⨯', f'{RED}⨯{NC}', line)

    # Colorear FAILED en líneas de error
    if 'FAILED' in line:
        line = re.sub(r'FAILED', f'{RED}FAILED{NC}', line)
        line = f'{BOLD}{line}{NC}'

    # Colorear mensajes de error principales
    if 'Expected response status code' in line:
        line = f'{YELLOW}{line}{NC}'

    # Colorear errores de base de datos
    if 'SQLSTATE' in line or 'QueryException' in line or 'PDOException' in line:
        line = f'{RED}{line}{NC}'

    # Colorear constraint violations
    if 'constraint violation' in line.lower() or 'not null' in line.lower():
        line = f'{MAGENTA}{line}{NC}'

    # Colorear errores de validación
    if 'Failed asserting that' in line:
        line = f'{RED}{line}{NC}'

    # Colorear errores de validación específicos
    if 'ValidationException' in line or 'validation failed' in line.lower():
        line = f'{YELLOW}{line}{NC}'

    # Colorear errores HTTP específicos
    if 'Unexpected status code' in line or 'Response status' in line:
        line = f'{YELLOW}{line}{NC}'

    # Colorear errores de assertion
    if any(word in line for word in ['assertSame', 'assertEquals', 'assertTrue', 'assertFalse', 'assertNull', 'assertNotNull']):
        line = f'{RED}{line}{NC}'

    # Colorear mensajes de validación JSON
    if '\"success\": false' in line or '\"message\": \"Validation errors\"' in line:
        line = f'{RED}{line}{NC}'

    # Colorear campos con errores de validación
    if '\"errors\":' in line or 'validation.required' in line:
        line = f'{YELLOW}{line}{NC}'    # Colorear errores durante el request
    if 'The following errors occurred during the last request:' in line:
        line = f'{MAGENTA}{line}{NC}'

    # Colorear respuestas HTTP no exitosas
    if re.search(r'but received [4-5]\d{2}', line):
        line = f'{RED}{line}{NC}'

    # Colorear nombres de archivos de test
    line = re.sub(r'(at tests/[^:]+:\d+)', f'{CYAN}\\1{NC}', line)

    # Colorear líneas de código con números
    line = re.sub(r'(\d+▕)', f'{BLUE}\\1{NC}', line)
    line = re.sub(r'(➜\s*\d+▕)', f'{YELLOW}\\1{NC}', line)

    # Colorear separadores
    line = re.sub(r'(─{50,})', f'{CYAN}\\1{NC}', line)

    return line

for line in sys.stdin:
    line = line.rstrip()

    # Detectar inicio de stacktrace
    if 'Stack trace:' in line:
        skip_stack = True
        continue

    # Detectar final de stacktrace y extraer error principal
    if line.startswith('Next ') and skip_stack:
        # Limpiar la línea de rutas de vendor
        cleaned = re.sub(r' in /.*vendor/.*', '', line)
        colored = colorize_line(cleaned)
        print(colored)
        skip_stack = False
        continue

    # Saltar líneas del stacktrace
    if skip_stack:
        continue

    # Saltar líneas numeradas del stacktrace
    if re.match(r'^#\d', line):
        continue

    # Saltar líneas que contienen vendor
    if 'vendor/' in line:
        continue

    # Saltar mensaje de excepción durante request
    if 'The following exception occurred during the last request:' in line:
        continue

    # Aplicar colores y mostrar línea
    colored_line = colorize_line(line)
    print(colored_line)
"

echo -e "${GREEN}✅ Tests completados.${NC}"
