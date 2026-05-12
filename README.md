# Beeffresh

Plataforma web para digitalizar la gestión de una carnicería: **tienda pública** (inicio, catálogo, carrito, checkout con pedidos en base de datos), **contenidos** (videos, recetas, promociones, cortes), **panel de administración** con métricas (KPIs, alertas, stock) y **acceso por roles** ([Laravel Breeze](https://laravel.com/docs/breeze) + Sanctum).

**Repositorio:** [github.com/wilder1994/beeffresh2024](https://github.com/wilder1994/beeffresh2024)

| Stack | Versión / notas |
|--------|------------------|
| PHP | ^8.1 |
| Laravel | ^10 |
| Frontend | Vite, Tailwind CSS, DaisyUI |
| Auth API | Laravel Sanctum |

**Última actualización de esta documentación:** 2026-05-11

**Identidad visual:** variables CSS `--bf-*` en `resources/css/app.css` (crema, marrón del logo, carmesí, sol/dorado); **Figtree** (UI) y **Libre Baskerville** (marca, clase `font-brand` / `fontFamily.brand` en Tailwind); hojas de estilo de fuentes en `resources/views/layouts/partials/fonts.blade.php`.

**Formularios (panel admin, catálogo, perfil):** clases utilitarias en la capa `@layer components` de `resources/css/app.css`: contenedor `bf-form-panel` / `bf-form-panel-tight`, campos `bf-input`, `bf-select`, `bf-textarea`, `bf-file`, etiquetas `bf-label` / `bf-label-muted`, acciones `bf-form-actions`, botones `bf-btn-primary` / `bf-btn-ghost`. El componente Blade `x-text-input` aplica `bf-input` por defecto (login Breeze, perfil). Tras cambiar CSS, ejecuta `npm run build` (o `npm run dev`) para regenerar assets; `public/build` está en `.gitignore` — en despliegue conviene compilar en CI o en el servidor.

**Página «Nosotros»:** ruta pública `GET /nosotros` (`company_profiles`, registro id 1). El administrador edita el texto y enlaces de redes en **`/admin/empresa`**.

**Migraciones:** perfil de cliente, avatar y datos de envío en pedidos están definidos dentro de `create_users_table` y `create_orders_table` (sin migraciones `*_add_*` sueltas). Si tu BD local ya tenía el historial antiguo y tras actualizar el código ves inconsistencias, en desarrollo puedes recrear el esquema con `php artisan migrate:fresh` (y `db:seed` si usas semillas). En producción ya desplegada conviene migraciones incrementales; este repo se mantiene como esquema base único para instalaciones nuevas.

El **personal interno** (roles empresa en `layouts.app`) usa **sidebar** (colapsable en escritorio, panel lateral en móvil con overlay); invitados y clientes en ese layout conservan la **barra superior** clásica. Los administradores tienen **Operaciones** (pedidos, catálogo), **Usuarios** (Todos, Clientes, Empresa, Proveedores) y **Ajustes** como acordeones (clic en el título abre o cierra). Cada bloque se despliega abierto si la ruta actual pertenece a ese grupo.

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

**Laragon (Windows):** si en PowerShell o Cursor `npm` no se reconoce, Node suele estar en `C:\laragon\bin\nodejs\node-v18\`. Desde la raíz del proyecto puedes usar la ruta completa, p. ej. `& 'C:\laragon\bin\nodejs\node-v18\npm.cmd' run build`, añadir esa carpeta al `PATH` del sistema, o usar la terminal de Laragon tras **Menu → Path → Add Laragon to Path**.

### Acceso en LAN por IP (Laragon / Apache)

En la configuración de Apache de Laragon en la máquina de desarrollo, **Beeffresh** por dirección IP usa el **puerto 8080** (`C:/laragon/etc/apache2/sites-enabled/beeffresh2024-ip.conf`: `VirtualHost *:8080`, `ServerName` = IP LAN). El **puerto 80** en esa misma IP puede quedar reservado para otro proyecto (p. ej. DecoWandy) sin conflicto de cabecera `Host`.

- URL típica desde la red: `http://192.168.18.19:8080` (ajusta la IP si DHCP cambia).
- En `.env`, `APP_URL` debe coincidir con esa URL, p. ej. `APP_URL=http://192.168.18.19:8080`.
- En `httpd.conf` de Apache debe existir `Listen 8080`; tras cambiar la configuración, **reinicia Apache** en Laragon.
- Sigue siendo válido `http://beeffresh2024.test` en el puerto **80** si tienes el virtual host automático (`auto.beeffresh2024.test.conf`) y la entrada en `hosts`.

### Logo de la empresa y fotos de perfil

- **Logo comercial** (`logos.tipo = principal`): se sube **solo desde el panel** con el **icono de cámara** junto al logo en el **sidebar** (administradores). No hay página dedicada `/admin/logo/edit`. Alternativa por defecto: `public/logos/logo.jpeg`.
- **Foto de usuario**: columna `users.avatar_path` (disco `public/avatars/…`). Cada usuario la cambia en **Mi perfil** (icono de cámara). El administrador puede asignar una foto al **crear o editar** un usuario en `/admin/users`.

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
| `admin` | Panel completo, usuarios, pedidos, CRUD catálogo, API mutaciones |
| `cashier`, `order_clerk`, `delivery` | Personal interno (dashboard propio; rutas admin según middleware) |
| `supplier` | Portal `/portal-proveedor` (vistas dedicadas) |

Middleware `role:*` en rutas web y API. Crear cuentas de personal:

```bash
php artisan beeffresh:user --email=caja@demo.local --name="Caja" --role=cashier --password=secreto
```

Roles válidos en el comando: `admin`, `cashier`, `order_clerk`, `delivery`, `supplier`, `customer`.

## Usuarios y domicilios

Los perfiles se agrupan en **tres ámbitos** (filtros en `UserRole::audienceId()` / etiquetas en español): **clientes** (`customer`), **empresa** (personal interno: `admin`, `cashier`, `order_clerk`, `delivery`) y **proveedores** (`supplier`).

- **Clientes:** en **Mi perfil** deben completar teléfono, dirección, ciudad y provincia para poder **finalizar un pedido** o entrar a **checkout**; datos opcionales: cédula/RNC, indicaciones al domiciliario, código postal, país (por defecto `DO`).
- **Proveedores:** pueden indicar **razón social** en el perfil.
- **Administración:** CRUD de usuarios en `/admin/users` (vista “todos” con filtro por tipo), listados dedicados `/admin/users/clientes`, `/admin/users/empresa` y `/admin/users/proveedores` (sidebar **Usuarios**: Todos, Clientes, Empresa, Proveedores). No se expone borrado masivo; al editar roles se evita quitar el último `admin`.

Al confirmar un pedido, se guarda una **copia de domicilio** en la tabla `orders` (`shipping_*`) para conservar la dirección vigente aunque el cliente cambie el perfil después.

Listado de usuarios vía `App\Repositories\UserRepository` + contrato `App\Contracts\UserRepositoryContract`.

## Rutas útiles

| Área | Ruta / nota |
|------|-------------|
| Tienda (clientes) | `/`, `/nosotros`, `/productos-publicos`, `/carrito`, `/checkout` (auth; cliente con perfil de entrega completo) |
| Contenido empresa (admin) | `GET/PUT /admin/empresa` — texto de la página Nosotros y enlaces de redes (`company_profiles`) |
| Dashboard | `/dashboard` (según rol: admin con KPIs, cliente tienda `layouts.store`, proveedor redirige a portal) |
| Panel admin (atajo) | `GET /admin` redirige a `/dashboard` (evita 404) |
| Pedidos (admin) | `/admin/pedidos` |
| Usuarios (admin) | `/admin/users`, `/admin/users/clientes`, `/admin/users/empresa`, `/admin/users/proveedores` |
| Portal proveedor | `/portal-proveedor` (auth + rol supplier) |
| Perfil Breeze | `/profile` |

La **navbar marrón** del layout interno (`layouts.app`) agrupa acceso a la vista cliente (inicio tienda, catálogo, carrito) y enlaces de gestión para administradores (incluye **Usuarios**).

## Tienda y pedidos

- Carrito en sesión; solo **cuentas cliente** pueden cerrar compra en línea; **checkout** exige perfil de entrega completo (teléfono, dirección, ciudad, provincia).
- Confirmación: tablas **`orders`** (con snapshot `shipping_*`) y **`order_items`**, descuento de stock vía `App\Services\CheckoutService`.
- Panel admin: listado en `/admin/pedidos` (columna resumen de entrega); dashboard admin muestra KPIs, pedidos recientes, stock bajo y volumen de pedidos (`App\Services\AdminDashboardService`).
- **Eliminar producto** (web o API): si el producto tiene líneas en pedidos (`order_items`), el borrado se rechaza con mensaje / HTTP 409 en API (integridad referencial).

## API (Sanctum)

Prefijo `/api` (ver `routes/api.php`). Lecturas públicas con **throttle**; mutaciones con `auth:sanctum`, rol **admin** y throttle donde aplique. Token de ejemplo (tinker):

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
