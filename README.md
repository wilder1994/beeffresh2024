# Beeffresh

Plataforma web para digitalizar la gestión de una carnicería: catálogo público, carrito y checkout (pedidos en base de datos), contenidos (videos, promociones, cortes) y panel por roles ([Laravel Breeze](https://laravel.com/docs/breeze)).

**Repositorio:** [github.com/wilder1994/beeffresh2024](https://github.com/wilder1994/beeffresh2024)

## Requisitos

- PHP ^8.1
- Composer 2
- Node.js y npm (Vite + Tailwind + DaisyUI)
- MySQL u otro motor compatible con `.env`

## Instalación local

```bash
git clone https://github.com/wilder1994/beeffresh2024.git
cd beeffresh2024
composer install
copy .env.example .env   # Windows — en Linux/macOS: cp .env.example .env
php artisan key:generate
```

Configura `DB_*`, `APP_URL` y las variables `ADMIN_*` (usuario administrador inicial).

```bash
php artisan migrate
php artisan db:seed
npm install
npm run build
```

Desarrollo con recarga de assets:

```bash
npm run dev
```

Para **reconstruir la base desde cero** en local (borra todos los datos):

```bash
php artisan migrate:fresh --seed
```

## Usuario administrador (semillas)

Credenciales definidas en `.env` y leídas vía `config/admin.php`:

| Variable | Descripción |
|----------|-------------|
| `ADMIN_NAME` | Nombre visible |
| `ADMIN_EMAIL` | Correo (único) |
| `ADMIN_PASSWORD` | Texto plano en `.env`; se guarda hasheado |

Por defecto en `.env.example`: `admin@beeffresh.local` / `password`.

```bash
php artisan db:seed --class=AdminUserSeeder
```

Acceso al sistema: `/login`.

## Roles

Los usuarios tienen un campo `role` (`App\Enums\UserRole`): **customer** (registro público), **admin**, **cashier**, **order_clerk**, **delivery**. El middleware `role:*` protege rutas; el dashboard muestra contenido según el rol.

Crear cuentas de personal desde consola:

```bash
php artisan beeffresh:user --email=caja@demo.local --name="Caja" --role=cashier --password=secreto
```

Roles válidos: `admin`, `cashier`, `order_clerk`, `delivery`, `customer`.

## Tienda y pedidos

- Catálogo público y carrito en sesión.
- Para **pagar / confirmar compra** hace falta estar autenticado; invitados pueden registrarse.
- La confirmación crea registros en **`orders`** y **`order_items`** y descuenta stock (servicio `App\Services\CheckoutService`).
- Los administradores pueden ver el listado en **`/admin/pedidos`**.

## API (Sanctum)

Prefijo estándar `/api`:

| Método | Ruta | Acceso |
|--------|------|--------|
| GET | `/api/v1/producto` | Público |
| GET | `/api/v1/producto/{id}` | Público |
| POST, PUT, PATCH, DELETE | `/api/v1/producto...` | Token Sanctum + rol **admin** |

Ejemplo de token (tinker):  
`User::where('email', 'admin@beeffresh.local')->first()->createToken('api')->plainTextToken`  
Cabecera: `Authorization: Bearer {token}`.

## Esquema de base de datos

Las migraciones están armadas para instalaciones nuevas: **`categorias`** se crea antes que **`productos`** (clave foránea `categoria_id`); **`users`** incluye `role` desde la migración inicial.

## Licencia

[Laravel](https://laravel.com) — licencia [MIT](https://opensource.org/licenses/MIT).
