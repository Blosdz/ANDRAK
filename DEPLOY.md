# Despliegue de ANDRAK en el servidor de Plusfact

Esta configuración crea **tres contenedores independientes**: `web` (Nginx), `app` (PHP 8.4 FPM) y `db` (PostgreSQL 16). Solo `web` entra en la red externa `proxynet`. PostgreSQL usa la red privada de ANDRAK, no publica el puerto 5432 y no se conecta a MariaDB de Plusfact. No se modifica `/home/proxy` ni `/home/www`.

Dominio previsto: `andrea.plusfact.com`. El proxy existente debe resolverlo hacia el nuevo `web` mediante `VIRTUAL_HOST`. El certificado se elige con `CERT_NAME` y debe cubrir `andrea.plusfact.com`.

## 1. Verificaciones en el servidor

```bash
docker network inspect proxynet >/dev/null
ls -la /home/certs
getent hosts andrea.plusfact.com
docker inspect nginx_plusfact_net --format '{{range .Config.Env}}{{println .}}{{end}}' | grep -E 'VIRTUAL_|CERT_NAME|HTTPS_METHOD'
```

Identifica en `/home/certs` un par `<nombre>.crt` y `<nombre>.key` cuyo certificado incluya `andrea.plusfact.com` o `*.plusfact.com`:

```bash
openssl x509 -in /home/certs/<nombre>.crt -noout -ext subjectAltName
```

El ejemplo usa `CERT_NAME=plusfact.net`. Cámbialo si el nombre real del par de archivos es otro. Confirma también que el DNS apunta a este servidor. Como `rash07/nginx-proxy:4.0` es una imagen personalizada y Plusfact declara `*.plusfact.net`, la precedencia del nuevo host exacto y la selección del certificado se comprueban después de levantarlo.

## 2. Clonar y configurar

Sube primero los cambios de este repositorio a GitHub. La URL HTTPS funciona en el servidor mostrado; la SSH falló allí por falta de clave autorizada.

```bash
cd /home
git clone https://github.com/Blosdz/ANDRAK.git ANDRAK
cd /home/ANDRAK
cp .env.docker.example .env
chmod 600 .env
nano .env
```

En `.env`, cambia `DB_PASSWORD` por una clave larga y comprueba `APP_URL`, `VIRTUAL_HOST` y `CERT_NAME`. Mantén `DB_CONNECTION=pgsql` y `DB_HOST=db`. `APP_KEY` se genera automáticamente durante la instalación inicial. **No copies el `.env` local de desarrollo**, porque usa SQLite.

## 3. Instalar y abrir la URL

```bash
./deploy.sh init
```

El script construye las imágenes, arranca PostgreSQL, crea `APP_KEY`, ejecuta migraciones y el seeder, pide interactivamente el nombre, correo y contraseña del primer usuario, y por último abre el contenedor web al proxy. La contraseña se introduce de forma oculta; no se guarda en `.env` ni en el historial de comandos. El registro público está deshabilitado en producción.

Comprueba:

```bash
./deploy.sh status
docker exec proxy-proxy-1 nginx -T 2>&1 | grep -A 20 'andrea.plusfact.com'
curl -I https://andrea.plusfact.com/up
curl -I https://andrea.plusfact.com/login
```

Si el proxy todavía sirve Plusfact para ese dominio, revisa la configuración generada y los registros del proxy antes de cambiar el proxy de producción:

```bash
docker logs --tail=100 proxy-proxy-1
./deploy.sh logs
```

La aplicación se abre en `https://andrea.plusfact.com/login`. El endpoint `/up` permite verificar la respuesta HTTP sin iniciar sesión.

## 4. Actualizar sin volver a sembrar datos

```bash
cd /home/ANDRAK
git pull --ff-only
./deploy.sh update
```

`update` reconstruye imágenes, ejecuta únicamente migraciones pendientes y recrea los contenedores de ANDRAK. Conserva los volúmenes `andrak_postgres_data` y `andrak_app_storage`. No ejecuta el seeder de nuevo.

## Alcance y pendientes del servidor

- Los certificados, el DNS y la configuración generada por `rash07/nginx-proxy:4.0` solo pueden verificarse en el servidor; no están incluidos en este repositorio.
- No hay puertos 80, 443 ni 5432 publicados por ANDRAK. El proxy existente conserva 80/443.
- La emisión SUNAT requiere configurar por separado certificados y credenciales de cada RUC en la aplicación.
- Antes de una actualización, respalda el volumen de PostgreSQL y el de `storage`, que contiene los certificados y documentos de ANDRAK.
