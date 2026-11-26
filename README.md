# Sistema de fidelización hotelera

Programa de puntos para hoteles y cadenas: registra huéspedes, asigna habitaciones (cada estadía suma puntos), canjea recompensas y cobra con QR. Varias empresas comparten una misma instalación sin ver los datos de las otras.

- **Empresas** (cadenas u hoteles individuales) con su marca, plan y hoteles.
- **Huéspedes únicos en toda la plataforma** (DNI, CE o pasaporte); **los puntos son de cada empresa** y se comparten entre los hoteles de una cadena.
- **Roles:** super-admin de plataforma, administrador de empresa, gerente y recepcionista.

Detalle del diseño y las reglas: [docs/PLAN-MULTILOCAL.md](docs/PLAN-MULTILOCAL.md).

## Requisitos

- PHP 8.2+ con `mbstring`, `pdo_mysql`, `gd` y `fileinfo` (y `pdo_sqlite` para los tests)
- MySQL 8
- Composer

## Instalación local (Laragon u otro)

```bash
composer install
cp .env.example .env          # ajusta DB_* si hace falta
php artisan key:generate
php artisan migrate
php artisan storage:link      # logos y QR subidos
php artisan plataforma:super-admin tu@correo.com --nombre="Tu Nombre" --dni=12345678
php artisan serve
```

Con el super-admin entras a **Plataforma** y das de alta la primera empresa (empresa + hotel + su administrador). El administrador completa lo demás desde la app: logo, tipos de habitación, recompensas, QR de pago y personal.

> En Windows con varias versiones de PHP, usa la de Laragon (`C:\laragon\bin\php\php-8.2.x\php.exe`) si el `php` del PATH no tiene `mbstring`.

## Docker

```bash
cp .env.docker.example .env.docker
# Completa en .env.docker: APP_KEY (php artisan key:generate --show), DB_PASSWORD y MYSQL_ROOT_PASSWORD (la misma)
docker compose up --build
docker compose exec laravel-backend php artisan plataforma:super-admin tu@correo.com --dni=12345678
```

La app queda en http://localhost:8000. MySQL solo escucha en `127.0.0.1:3306`.

## Correo

Con `MAIL_MAILER=log` (por defecto) no se envían correos: el enlace "¿Olvidaste tu contraseña?" se oculta y las contraseñas las cambia un administrador desde **Personal**. Configura `MAIL_*` con un SMTP real para activarlo.

## Tests

```bash
php artisan test
```

Usan SQLite en memoria. Incluyen pruebas de aislamiento entre empresas (`tests/Feature/AislamientoTest.php`) y de permisos por rol (`RolesTest.php`).
