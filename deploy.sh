#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

usage() {
    echo "Uso: ./deploy.sh {init|update|status|logs}"
    exit 1
}

value_of() {
    sed -n "s/^$1=//p" .env | tail -n 1
}

check_env() {
    if [ ! -f .env ]; then
        cp .env.docker.example .env
        chmod 600 .env
        echo "Se creó .env. Configura DB_PASSWORD, VIRTUAL_HOST y CERT_NAME y vuelve a ejecutar el comando."
        exit 1
    fi

    if [ "$(value_of DB_CONNECTION)" != "pgsql" ] || [ "$(value_of DB_HOST)" != "db" ]; then
        echo "Este Compose requiere DB_CONNECTION=pgsql y DB_HOST=db en .env."
        exit 1
    fi

    if [ -z "$(value_of DB_PASSWORD)" ] || [ "$(value_of DB_PASSWORD)" = "CAMBIAR_POR_UNA_CLAVE_LARGA" ]; then
        echo "Configura una contraseña real en DB_PASSWORD antes de desplegar."
        exit 1
    fi

    if ! docker network inspect proxynet >/dev/null 2>&1; then
        echo "No existe la red Docker proxynet en este servidor."
        exit 1
    fi

    cert_name="$(value_of CERT_NAME)"
    if [ ! -f "/home/certs/${cert_name}.crt" ] || [ ! -f "/home/certs/${cert_name}.key" ]; then
        echo "No se encontraron /home/certs/${cert_name}.crt y .key. Revisa CERT_NAME y el certificado wildcard antes de desplegar."
        exit 1
    fi

    docker compose config --quiet
}

generate_key() {
    if [ -n "$(value_of APP_KEY)" ]; then
        return
    fi

    app_key="$(docker compose run --rm --no-deps app php artisan key:generate --show --no-ansi | tr -d '\r' | tail -n 1)"
    case "$app_key" in
        base64:*) ;;
        *) echo "No se pudo generar APP_KEY."; exit 1 ;;
    esac

    env_tmp="$(mktemp ./.env.XXXXXX)"
    chmod 600 "$env_tmp"
    while IFS= read -r line || [ -n "$line" ]; do
        case "$line" in
            APP_KEY=*) printf 'APP_KEY=%s\n' "$app_key" >> "$env_tmp" ;;
            *) printf '%s\n' "$line" >> "$env_tmp" ;;
        esac
    done < .env
    mv "$env_tmp" .env
}

case "${1:-}" in
    init)
        check_env
        docker compose build app web
        generate_key
        docker compose up -d db
        docker compose run --rm --user www-data app php artisan migrate --force
        docker compose run --rm --user www-data app php artisan db:seed --force
        docker compose run --rm --user www-data app php artisan andrak:user-create
        docker compose up -d app web
        docker compose ps
        ;;
    update)
        check_env
        if [ -z "$(value_of APP_KEY)" ]; then
            echo "Falta APP_KEY. Ejecuta ./deploy.sh init primero."
            exit 1
        fi
        docker compose build app web
        docker compose up -d db
        docker compose run --rm --user www-data app php artisan migrate --force
        docker compose up -d app web
        docker compose ps
        ;;
    status)
        docker compose ps
        ;;
    logs)
        docker compose logs -f --tail=100 web app db
        ;;
    *) usage ;;
esac
