# Beeffresh

Plataforma web para digitalizar la gestión de una carnicería: **tienda pública** (inicio, catálogo, carrito, checkout con pedidos en base de datos), **contenidos** (videos, recetas, promociones, cortes), **panel de administración** con métricas (KPIs, alertas, stock) y **acceso por roles** ([Laravel Breeze](https://laravel.com/docs/breeze) + Sanctum).

**Repositorio:** [github.com/wilder1994/beeffresh2024](https://github.com/wilder1994/beeffresh2024)

| Stack | Versión / notas |
|--------|------------------|
| PHP | ^8.1 |
| Laravel | ^10 |
| Frontend | Vite, Tailwind CSS, DaisyUI |
| Auth API | Laravel Sanctum |

**Última actualización de esta documentación:** 2026-05-02

## Requisitos

- PHP ^8.1, Composer 2, Node.js y npm
- MySQL (o compatible) según `DB_*` en `.env`
- Extensión `pdo_mysql` habilitada (Laragon / entorno local)

## Instalación local

```bash
git clone https://github.com/wilder1994/beeffresh2024.git
cd beeffresh2024
composer install
copy .env.example .env
php artisan key:generate
```

1. Configura en `.env`: `DB_*`, `APP_URL`, y `ADMIN_*` (administrador inicial vía semillas).
2. Enlaza almacenamiento público para imágenes de productos, promociones, etc.:

```bash
php artisan storage:link
```

3. Migraciones y semilla por defecto (incluye `AdminUserSeeder`):

```bash
php artisan migrate
php artisan db:seed
```

4. Assets:

```bash
npm install
npm run build
```

Desarrollo con recarga de assets: `npm run dev`.

### Logo por defecto

Si no hay logo en base de datos, la tienda usa `public/logos/logo.jpeg` (también referenciado en vistas).

## Usuario administrador (semillas)

Credenciales definidas en `.env` y `config/admin.php`:

| Variable | Descripción |
|----------|-------------|
| `ADMIN_NAME` | Nombre visible |
| `ADMIN_EMAIL` | Correo (único) |
| `ADMIN_PASSWORD` | Texto plano en `.env`; se almacena hasheado |

Valores de ejemplo en `.env.example`. Volver a crear o actualizar solo el admin:

```bash
php artisan db:seed --class=AdminUserSeeder
```

Acceso: `/login`.

## Roles (`App\Enums\UserRole`)

| Rol | Uso |
|-----|-----|
| `customer` | Registro público Breeze |
| `admin` | Panel completo, pedidos, CRUD catálogo, API mutaciones |
| `cashier`, `order_clerk`, `delivery` | Personal interno (dashboard propio; rutas admin según middleware) |
| `supplier` | Portal `/portal-proveedor` (vistas dedicadas) |

Middleware `role:*` en rutas web y API. Crear cuentas de personal:

```bash
php artisan beeffresh:user --email=caja@demo.local --name="Caja" --role=cashier --password=secreto
```

Roles válidos en el comando: `admin`, `cashier`, `order_clerk`, `delivery`, `supplier`, `customer`.

## Rutas útiles

| Área | Ruta / nota |
|------|-------------|
| Tienda (clientes) | `/`, `/productos-publicos`, `/carrito`, `/checkout` (auth) |
| Dashboard | `/dashboard` (según rol: admin con KPIs, cliente tienda `layouts.store`, proveedor redirige a portal) |
| Pedidos (admin) | `/admin/pedidos` |
| Portal proveedor | `/portal-proveedor` (auth + rol supplier) |
| Perfil Breeze | `/profile` |

La **navbar marrón** del layout interno (`layouts.app`) agrupa acceso a la vista cliente (inicio tienda, catálogo, carrito) y enlaces de gestión para administradores.

## Tienda y pedidos

- Carrito en sesión; **checkout** requiere usuario autenticado.
- Confirmación: tablas **`orders`** y **`order_items`**, descuento de stock vía `App\Services\CheckoutService`.
- Panel admin: listado en `/admin/pedidos`; dashboard admin muestra KPIs, pedidos recientes, stock bajo y volumen de pedidos (servicio `App\Services\AdminDashboardService`).

## API (Sanctum)

Prefijo `/api` (ver `routes/api.php`). Lecturas públicas según rutas definidas; mutaciones con `auth:sanctum` y rol **admin** donde aplique. Token de ejemplo (tinker):

```php
User::where('email', config('admin.email'))->first()->createToken('api')->plainTextToken;
```

Cabecera: `Authorization: Bearer {token}`.

## Pruebas automatizadas

Los tests de características usan `RefreshDatabase`. En **`phpunit.xml`** la base de datos de pruebas es **`beeffresh2024_test`** (no la misma que desarrollo). Al ejecutar tests, `tests/CreatesApplication.php` intenta crear esa base en MySQL si el nombre termina en `_test`.

```bash
php artisan test
```

**Importante:** no ejecutar la suite de tests contra la base de datos de desarrollo sin ese aislamiento; `RefreshDatabase` ejecuta migraciones desde cero sobre la BD configurada para `APP_ENV=testing`.

## Base de datos y migraciones

Orden de migraciones coherente con FKs (p. ej. `categorias` antes de `productos`; `users` con `role` en migración inicial).

### Comando destructivo (solo si lo necesitas a sabiendas)

`migrate:fresh` **elimina todas las tablas y datos**. Úsalo solo en entornos locales/desechables:

```bash
php artisan migrate:fresh --seed
```

## Licencia

[Laravel](https://laravel.com) — [MIT](https://opensource.org/licenses/MIT).
