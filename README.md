# CMS Builder PHP

CMS dinámico con módulos configurables en tiempo de ejecución. Permite crear
páginas, tablas y columnas desde la UI sin redeploys.

## Requisitos

- PHP 8.1 o superior con extensiones `pdo` y `pdo_pgsql`.
- PostgreSQL 12+ (o el motor soportado por la API remota).
- Composer 2.
- Una API remota que cumpla el contrato descrito en [`docs/api.md`](docs/api.md).

## Instalación

```bash
git clone <repo>
cd cms
composer install
cp .env.example .env
php -r "echo bin2hex(random_bytes(32));"
# Pegar el resultado en APP_KEY del .env
```

Editar `.env`:

```env
APP_ENV=local              # local | production
APP_KEY=<32 bytes hex>     # usado para firmar IDs
APP_URL=http://localhost:6060

API_BASE_URL=http://localhost:9090/
API_TOKEN=<token Bearer>
```

## Servidor de desarrollo

```bash
composer serve
# http://localhost:6060
```

## Tests

```bash
composer test    # PHPUnit
composer stan    # PHPStan (nivel 5)
composer cs      # PHPCS PSR-12
composer cs:fix
```

CI ejecuta `lint` + `test` en PHP 8.1, 8.2 y 8.3 vía GitHub Actions.

## Arquitectura

```
public/index.php            front controller
  └─ TemplateController     render de vistas
       └─ views/template.php
            ├─ vistas (login, install, dashboard, modales)
            └─ controllers (Admins, Pages, Modules, Dynamic)

ajax/*.ajax.php             endpoints AJAX
  └─ llaman a CurlController
       └─ habla con la API remota (API_BASE_URL)

app/Http/Security.php       CSRF, tokens firmados, hashing
app/Support/UrlBuilder.php  helper de construcción de URLs a la API
```

La API remota y este front son **servicios separados**. Toda la comunicación
entre el front y la API pasa por `CurlController` con autenticación
`Authorization: Bearer ${API_TOKEN}`.

## Variables de entorno

| Variable | Descripción | Default |
|---|---|---|
| `APP_ENV` | `local` activa errores en pantalla. | `production` |
| `APP_KEY` | Secreto para firmar IDs (mínimo 32 bytes hex). | — |
| `APP_URL` | URL pública del front. | `http://localhost:6060` |
| `API_BASE_URL` | URL base de la API remota. | `http://localhost:9090/` |
| `API_TOKEN` | Bearer token para la API. | — |
| `DB_HOST/PORT/NAME/USER/PASS` | Conexión a la BD de datos. | — |
| `MAIL_*` | SMTP saliente. | — |

## Pendiente (no incluido en Fase 0)

- [ ] Matar self-API: reemplazar `CurlController` por acceso directo a la
      capa de datos cuando convivan en el mismo deploy.
- [ ] Router central con whitelist de páginas.
- [ ] Namespacing completo de los AJAX controllers.
- [ ] DTOs / validación de entrada estructurada.
- [ ] Render JSON para tablas dinámicas.
- [ ] Versionado de esquema (migrations) para tablas dinámicas.
