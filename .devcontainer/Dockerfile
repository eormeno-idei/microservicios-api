FROM ubuntu:22.04
ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=America/Argentina/San_Juan

# Configurar locales
RUN apt-get update && \
    apt-get install -y locales && \
    locale-gen es_ES.UTF-8 && \
    update-locale LANG=es_ES.UTF-8

# Establecer variables de entorno de locale
ENV LANG=es_ES.UTF-8
ENV LC_ALL=es_ES.UTF-8

# Instala PHP 8.3 y extensiones comunes para Laravel, incluyendo SQLite
RUN apt-get update && \
    apt-get install -y software-properties-common tzdata ca-certificates gnupg && \
    add-apt-repository ppa:ondrej/php -y && \
    apt-get update && \
    apt-get install -y \
        php8.3 php8.3-cli php8.3-mbstring php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip \
        php8.3-sqlite3 php8.3-pdo php8.3-pdo-mysql php8.3-gd php8.3-intl php8.3-readline \
        openssl curl git unzip composer \
        # Herramientas de red y diagnóstico
        iputils-ping \
        net-tools \
        dnsutils \
        traceroute \
        telnet \
        netcat-openbsd \
        wget \
        iproute2 \
        tcpdump \
        nmap \
        htop \
        vim \
        nano

# Instalar Node.js 20 LTS
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# Verificar instalación de Node.js y npm
RUN node --version && npm --version

# Limpieza
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer (por si acaso)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
