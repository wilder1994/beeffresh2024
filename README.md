# Beeffresh

Plataforma web para digitalizar la gestión de una carnicería: catálogo público, carrito, contenidos (recetas en video, promociones, cortes) y panel administrativo protegido con autenticación ([Laravel Breeze](https://laravel.com/docs/breeze)).

**Repositorio:** [github.com/wilder1994/beeffresh2024](https://github.com/wilder1994/beeffresh2024)

## Requisitos

- PHP ^8.1 con extensiones habituales de Laravel
- Composer 2
- Node.js y npm (assets con Vite)
- MySQL u otro motor compatible con la configuración en `.env`

## Instalación local

```bash
git clone https://github.com/wilder1994/beeffresh2024.git
cd beeffresh2024
composer install
copy .env.example .env   # en Windows; en Linux/macOS: cp .env.example .env
php artisan key:generate
```

Configura en `.env` la base de datos (`DB_*`), la URL de la aplicación (`APP_URL`) y, si aplica, las variables del usuario administrador (ver siguiente sección).

```bash
php artisan migrate
php artisan db:seed
npm install
npm run build
```

En desarrollo, para recargar CSS/JS con Vite:

```bash
npm run dev
```

## Usuario administrador (semillas)

Las credenciales del usuario de panel se definen en `.env` y se exponen vía `config/admin.php` (adecuado si usas `php artisan config:cache`):

| Variable | Descripción |
|----------|-------------|
| `ADMIN_NAME` | Nombre visible |
| `ADMIN_EMAIL` | Correo (único en `users`) |
| `ADMIN_PASSWORD` | Contraseña en texto plano en `.env`; Laravel la almacena hasheada |

Valores por defecto en `.env.example` si no defines nada: `admin@beeffresh.local` / `password`.

Ejecutar solo el seeder del admin:

```bash
php artisan db:seed --class=AdminUserSeeder
```

El acceso al panel es la ruta `/login` tras registrar o usar el usuario sembrado.

## API

Rutas REST bajo el prefijo estándar `api` (por ejemplo `GET /api/v1/producto`). Autenticación API según [Sanctum](https://laravel.com/docs/sanctum).

## Licencia

Este proyecto utiliza el framework [Laravel](https://laravel.com), open source bajo licencia [MIT](https://opensource.org/licenses/MIT).
