#!/bin/bash
set -e

GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

# Detectar PHP
PHP_BIN="/RunCloud/Packages/php84rc/bin/php"
if [ ! -f "$PHP_BIN" ]; then
    if command -v php84 &> /dev/null; then
        PHP_BIN="php84"
    else
        PHP_BIN="php"
    fi
fi

echo -e "${BLUE}==> Build WAB Curriculos${NC}"
echo ""

# 1. Composer
echo -e "${GREEN}--> Instalando dependencias...${NC}"
COMPOSER_NO_INTERACTION=1 composer install --no-scripts --quiet
composer run-script post-install-cmd --quiet 2>/dev/null || true

# 2. Cache
echo -e "${GREEN}--> Limpando cache...${NC}"
rm -rf var/cache/*
$PHP_BIN bin/console cache:clear --no-warmup 2>/dev/null || true
$PHP_BIN bin/console cache:warmup 2>/dev/null || true

# 3. Migrations
echo -e "${GREEN}--> Executando migrations...${NC}"
$PHP_BIN bin/console doctrine:migrations:migrate --no-interaction 2>/dev/null || true

# 4. Assets
echo -e "${GREEN}--> Compilando assets...${NC}"
$PHP_BIN bin/console importmap:install 2>/dev/null || true
$PHP_BIN bin/console asset-map:compile 2>/dev/null || true

# 5. Miniaturas LiipImagine
if [ -d "public/media/cache" ]; then
    echo -e "${GREEN}--> Removendo miniaturas...${NC}"
    rm -rf public/media/cache/*
fi

# 6. Logs e temporarios
echo -e "${GREEN}--> Limpando logs e temporarios...${NC}"
rm -rf var/log/*.log 2>/dev/null || true
rm -rf var/tmp/* 2>/dev/null || true

# 7. Permissoes e diretorios
echo -e "${GREEN}--> Ajustando permissoes...${NC}"
mkdir -p var/cache var/log var/uploads/resumes
mkdir -p public/uploads/tenants/logo public/uploads/tenants/dark-logo public/uploads/tenants/favicon
chmod -R 775 var/ public/uploads/ 2>/dev/null || true

echo ""
echo -e "${BLUE}==> Build concluido!${NC}"
