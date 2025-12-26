#!/usr/bin/env bash
set -e

# =========================
# Configuración inicial
# =========================

SSH_DIR="$HOME/.ssh"
KEY_PROD="$SSH_DIR/id_ed25519_prod"
KEY_TEST="$SSH_DIR/id_ed25519_test"

# Colores
COLOR_RED="\033[1;31m"
COLOR_GREEN="\033[1;32m"
COLOR_YELLOW="\033[1;33m"
COLOR_CYAN="\033[1;36m"
COLOR_RESET="\033[0m"

echo "=== Setup de claves SSH (testing / producción) ==="
echo

# =========================
# Pedir datos al usuario
# =========================

read -rp "Nombre de usuario (ej: jperez): " USER_NAME
read -rp "Nombre del equipo (ej: notebook-trabajo): " HOST_NAME

if [[ -z "$USER_NAME" || -z "$HOST_NAME" ]]; then
    echo -e "${COLOR_RED}ERROR: Usuario y equipo no pueden estar vacíos.${COLOR_RESET}"
    exit 1
fi

# =========================
# Verificar / crear ~/.ssh
# =========================

mkdir -p "$SSH_DIR"
chmod 700 "$SSH_DIR"

# =========================
# Manejo de .gitignore
# =========================

if [[ -f .gitignore ]]; then
    if ! grep -qx ".ssh" .gitignore; then
        echo ".ssh" >> .gitignore
        echo "Agregada entrada .ssh a .gitignore"
    fi
else
    echo ".ssh" > .gitignore
    echo "Creado .gitignore con entrada .ssh"
fi

# =========================
# Manejo de .dockerignore
# =========================

if [[ -f .dockerignore ]]; then
    if ! grep -qx ".ssh" .dockerignore; then
        echo ".ssh" >> .dockerignore
        echo "Agregada entrada .ssh a .dockerignore"
    fi
else
    echo ".ssh" > .dockerignore
    echo "Creado .dockerignore con entrada .ssh"
fi

# =========================
# Si ya existen claves, NO sobrescribir
# =========================

if [[ -f "$KEY_PROD" || -f "$KEY_TEST" ]]; then
    echo
    echo -e "${COLOR_YELLOW}==============================================${COLOR_RESET}"
    echo -e "${COLOR_YELLOW} CLAVES SSH YA EXISTENTES${COLOR_RESET}"
    echo -e "${COLOR_YELLOW}==============================================${COLOR_RESET}"
    echo
    echo "Se detectaron claves SSH previamente generadas en:"
    echo "  $SSH_DIR"
    echo
    echo "El script NO sobrescribe claves existentes por seguridad."
    echo

    if [[ -f "${KEY_PROD}.pub" ]]; then
        echo -e "${COLOR_CYAN}--- CLAVE PÚBLICA PRODUCCIÓN ---${COLOR_RESET}"
        cat "${KEY_PROD}.pub"
        echo
    fi

    if [[ -f "${KEY_TEST}.pub" ]]; then
        echo -e "${COLOR_CYAN}--- CLAVE PÚBLICA TESTING ---${COLOR_RESET}"
        cat "${KEY_TEST}.pub"
        echo
    fi

    echo -e "${COLOR_YELLOW}NOTA:${COLOR_RESET}"
    echo "En caso de querer reemplazarlas:"
    echo "  1. Elimine manualmente las claves del directorio:"
    echo "     $SSH_DIR"
    echo "  2. Vuelva a ejecutar este script."
    echo
    echo "No se realizó ninguna modificación."
    exit 0
fi

# =========================
# Generar claves
# =========================

echo
echo "Generando clave SSH para PRODUCCIÓN..."
ssh-keygen -t ed25519 -f "$KEY_PROD" -C "${USER_NAME}@${HOST_NAME}-prod"

echo
echo "Generando clave SSH para TESTING..."
ssh-keygen -t ed25519 -f "$KEY_TEST" -C "${USER_NAME}@${HOST_NAME}-test"

# =========================
# Mensaje final claro
# =========================

echo
echo -e "${COLOR_GREEN}=== Setup completado correctamente ===${COLOR_RESET}"
echo

echo -e "${COLOR_YELLOW}==============================================${COLOR_RESET}"
echo -e "${COLOR_YELLOW} ATENCIÓN – INFORMACIÓN A ENVIAR AL ADMIN VPS ${COLOR_RESET}"
echo -e "${COLOR_YELLOW}==============================================${COLOR_RESET}"
echo
echo "Copie y envíe EXACTAMENTE las siguientes claves públicas."
echo "No envíe fingerprints ni claves privadas."
echo

echo -e "${COLOR_CYAN}--- CLAVE PÚBLICA PRODUCCIÓN ---${COLOR_RESET}"
cat "${KEY_PROD}.pub"
echo

echo -e "${COLOR_CYAN}--- CLAVE PÚBLICA TESTING ---${COLOR_RESET}"
cat "${KEY_TEST}.pub"
echo

echo -e "${COLOR_YELLOW}==============================================${COLOR_RESET}"
echo
