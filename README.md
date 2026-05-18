# Beeffresh

Plataforma web para digitalizar la gestión de una carnicería: **tienda pública** (inicio, catálogo, carrito, checkout con pedidos en base de datos), **contenidos** (videos, recetas, promociones, cortes), **panel de administración** con métricas (KPIs, alertas, stock) y **acceso por roles y permisos** ([Laravel Breeze](https://laravel.com/docs/breeze), **Livewire**, **Spatie Permission**, Sanctum para API).

**Repositorio:** [github.com/wilder1994/beeffresh2024](https://github.com/wilder1994/beeffresh2024)

| Stack | Versión / notas |
|--------|------------------|
| PHP | ^8.1 |
| Laravel | ^10 |
| Frontend | Vite, Tailwind CSS, DaisyUI |
| Auth web | [Laravel Breeze](https://laravel.com/docs/breeze), **Livewire 3**, **Spatie Laravel Permission** |
| Auth API | Laravel Sanctum |

**Última actualización de esta documentación:** 2026-05-18

**Identidad visual:** variables CSS `--bf-*` en `resources/css/app.css` (crema, marrón del logo, carmesí, sol/dorado); **Figtree** (UI) y **Libre Baskerville** (marca, clase `font-brand` / `fontFamily.brand` en Tailwind); hojas de estilo de fuentes en `resources/views/layouts/partials/fonts.blade.php`.

**Formularios (panel admin, catálogo, perfil):** clases utilitarias en la capa `@layer components` de `resources/css/app.css`: contenedor `bf-form-panel` / `bf-form-panel-tight`, campos `bf-input`, `bf-select`, `bf-textarea`, `bf-file`, etiquetas `bf-label` / `bf-label-muted`, acciones `bf-form-actions`, botones `bf-btn-primary` / `bf-btn-ghost`. El componente Blade `x-text-input` aplica `bf-input` por defecto (login Breeze, perfil). **Usuarios (admin):** alta y edición con **Livewire 3** (`App\Livewire\Admin\UserForm`, vista `resources/views/livewire/admin/user-form.blade.php`); persistencia en `App\Services\Admin\AdminUserPersistence`. **Cargos:** CRUD en `/admin/positions` (modelo `Position`; el domiciliario es un **cargo** con slug `domiciliario`, no un rol). Tras cambiar CSS o JS, ejecuta `npm run build` (o `npm run dev`) para regenerar assets; `public/build` está en `.gitignore` — en despliegue conviene compilar en CI o en el servidor.

**Página «Nosotros»:** ruta pública `GET /nosotros` (`company_profiles`, registro id 1). El administrador edita el texto y enlaces de redes en **`/admin/empresa`**.

**Cinta (inicio):** carrusel horizontal a ancho completo en la tienda (`/`), hasta **15** imágenes en proporción **16:9** (recomendado 1920×1080, mínimo 960×540). La validación admite un margen de **±3 %** sobre 16:9 (`config/cinta.php` → `aspect_ratio_tolerance`; regla `App\Rules\CintaImageAspectRatio`). Archivos en disco `public/cinta/…` vía `App\Support\CintaSlideStorage`. El administrador gestiona las diapositivas en **`/admin/cinta`** (Ajustes → Cinta en el sidebar). La marquesina duplica diapositivas hasta 15 tiles para un bucle continuo (`App\Support\CintaMarqueeSlides`).

**Tras login:** clientes van a **`/`** (inicio), no a `/dashboard` (`App\Support\PostLoginRedirect`). Proveedores a `/portal-proveedor`; personal interno a `/dashboard`.

**Perfil y cuenta:** **Mi perfil** (`/profile`) usa panel modal reutilizable (`resources/views/components/account/*`). Avatares en `users.avatar` (`App\Support\UserAvatarStorage`, disco `public/avatars/`). Al cambiar la foto se abre un **editor circular** (girar, zoom, arrastrar para centrar) antes de guardar; lógica en `resources/js/avatarEditor.js` (Alpine `avatarEditor`) y vista `resources/views/components/avatar/crop-dialog.blade.php`. Aplica en el modal de perfil y en el formulario Livewire de usuarios (`UserForm`). En admin, vista de cuenta en modal Livewire (`App\Livewire\Admin\UserAccountModal`).

**Videos / recetas:** URLs de YouTube se normalizan a embed (`App\Support\YoutubeEmbedUrl`) en formularios de contenido.

**Migraciones:** `users` mantiene datos de cuenta (nombre, documento, teléfono, email, avatar `users.avatar`, estado). Perfiles en tablas `employee_profiles`, `customer_profiles`, `supplier_profiles`; roles y permisos con **Spatie** (`roles`, `permissions`, tablas pivot). Config publicada: `config/permission.php`. En desarrollo, ante un esquema desalineado: `php artisan migrate:fresh --seed`. En producción ya desplegada conviene migraciones incrementales; este repo define el esquema base para instalaciones nuevas.

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
2. Enlaza almacenamiento público para imágenes de productos, promociones, **cinta**, avatares, etc.:

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

**Laragon (Windows):** si en PowerShell o Cursor no se reconocen `php`, `composer` o `npm`:

- **PHP:** suele estar en `C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\` (ajusta la carpeta si Laragon instaló otra versión).
- **Composer:** `C:\laragon\bin\composer\composer.bat` (requiere que `php` esté en el `PATH` de esa sesión).
- **Node / npm:** `C:\laragon\bin\nodejs\node-v18\` (p. ej. `& 'C:\laragon\bin\nodejs\node-v18\npm.cmd' run build`).

Alternativa: **Menu → Path → Add Laragon to Path** y usar la **terminal de Laragon**, o ejecutar Composer/Artisan desde la raíz del proyecto con esas rutas en el `PATH` de la sesión.

**Livewire:** en `resources/views/layouts/app.blade.php` están `@livewireStyles` (head) y `@livewireScripts` (antes de `</body>`). Tras `composer install`, conviene `php artisan optimize:clear` si algo de paquetes no se refleja.

**PSR-4:** el catálogo público usa `App\Http\Controllers\Publico\ProductoPublicoController` en la carpeta **`app/Http/Controllers/Publico/`** (P mayúscula), coherente con el namespace.

### Acceso en LAN por IP (Laragon / Apache)

En la configuración de Apache de Laragon en la máquina de desarrollo, **Beeffresh** por dirección IP usa el **puerto 8080** (`C:/laragon/etc/apache2/sites-enabled/beeffresh2024-ip.conf`: `VirtualHost *:8080`, `ServerName` = IP LAN). El **puerto 80** en esa misma IP puede quedar reservado para otro proyecto (p. ej. DecoWandy) sin conflicto de cabecera `Host`.

- URL típica desde la red: `http://192.168.18.19:8080` (ajusta la IP si DHCP cambia).
- En `.env`, `APP_URL` debe coincidir con esa URL, p. ej. `APP_URL=http://192.168.18.19:8080`.
- En `httpd.conf` de Apache debe existir `Listen 8080`; tras cambiar la configuración, **reinicia Apache** en Laragon.
- Sigue siendo válido `http://beeffresh2024.test` en el puerto **80** si tienes el virtual host automático (`auto.beeffresh2024.test.conf`) y la entrada en `hosts`.

### Logo de la empresa y fotos de perfil

- **Logo comercial** (`logos.tipo = principal`): se sube **solo desde el panel** con el **icono de cámara** junto al logo circular en el **sidebar** (administradores). No hay página dedicada `/admin/logo/edit`. Alternativa por defecto: `public/logos/logo.jpeg`.
- **Foto de usuario**: columna `users.avatar` (disco `public/avatars/…`). En **Mi perfil** y en **crear/editar usuario** (`/admin/users`) el botón de cámara abre el editor de recorte circular; la imagen exportada es JPEG cuadrado (512×512) lista para `object-cover` en círculo.

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

## Roles y permisos (Spatie)

Roles de aplicación (guard `web`): `admin`, `employee`, `customer`, `supplier`. Constantes en `App\Domain\Users\RoleSlug`. Los permisos de módulo para empleados viven en `App\Domain\Users\PermissionKey` (p. ej. `module.catalog`, `module.orders`); se sembraron con `RolePermissionSeeder`. El **administrador** pasa todas las comprobaciones `can()` vía `Gate::before` en `AuthServiceProvider`.

| Rol | Uso |
|-----|-----|
| `customer` | Registro público Breeze; perfil de entrega en `customer_profiles`; tras login → inicio `/` |
| `admin` | Panel completo, usuarios, pedidos, CRUD catálogo, API mutaciones |
| `employee` | Personal interno; **cargo** en `employee_profiles` → `positions` (p. ej. domiciliario) |
| `supplier` | Portal `/portal-proveedor`; datos comerciales en `supplier_profiles` |

Middleware `role:*`, `permission:*` y `role_or_permission:*` (alias en `app/Http/Kernel.php`). Crear cuentas desde consola:

```bash
php artisan beeffresh:user --email=caja@demo.local --name="Caja Demo" --role=employee --password=secreto
```

Roles válidos en el comando: `admin`, `employee`, `customer`, `supplier`.

## Usuarios y domicilios

Los listados se agrupan en **tres ámbitos** (filtros y etiquetas vía `App\Domain\Users\RoleSlug::audienceId()`): **clientes** (`customer`), **empresa** (`admin`, `employee`) y **proveedores** (`supplier`).

- **Clientes:** dirección y ciudad en `customer_profiles`; en **Mi perfil** deben completar teléfono, dirección, ciudad y provincia para **checkout**; el modelo `User` expone `filledDeliveryBasics()` / `hasCompleteDeliveryProfile()`.
- **Proveedores:** razón social, NIT y contacto en `supplier_profiles` (editable también en perfil).
- **Administración:** alta/edición con Livewire; listados `/admin/users`, `/admin/users/clientes`, `/admin/users/empresa`, `/admin/users/proveedores`. **Cargos:** `/admin/positions`. No se expone borrado masivo de usuarios; al cambiar rol se evita dejar sin ningún `admin`.

Al confirmar un pedido, se guarda una **copia de domicilio** en `orders` (`shipping_*`) vía `User::snapshotShippingFromProfile()`.

Listado de usuarios: `App\Repositories\UserRepository` + `App\Contracts\UserRepositoryContract`.

## Rutas útiles

| Área | Ruta / nota |
|------|-------------|
| Tienda (clientes) | `/` (carrusel cinta si hay diapositivas), `/nosotros`, `/productos-publicos`, `/carrito`, `/checkout` (auth; cliente con perfil de entrega completo) |
| Contenido empresa (admin) | `GET/PUT /admin/empresa` — texto de la página Nosotros y enlaces de redes (`company_profiles`) |
| Cinta (admin) | `GET /admin/cinta`, `POST/PUT/DELETE` diapositivas (`cinta_slides`, `config/cinta.php`) |
| Dashboard | `/dashboard` (admin/empleado con KPIs; **clientes** usan inicio `/` tras login) |
| Panel admin (atajo) | `GET /admin` redirige a `/dashboard` (evita 404) |
| Pedidos (admin) | `/admin/pedidos` |
| Usuarios (admin) | `/admin/users`, Livewire create/edit; `/admin/positions` (cargos) |
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

Los tests de características usan `RefreshDatabase`. `tests/TestCase.php` ejecuta `RolePermissionSeeder` y `PositionSeeder` en cada caso para que existan roles Spatie y cargos base antes de usar `User::factory()`. En **`phpunit.xml`** la base de datos de pruebas es **`beeffresh2024_test`** (no la misma que desarrollo). Al ejecutar tests, `tests/CreatesApplication.php` intenta crear esa base en MySQL si el nombre termina en `_test`.

```bash
php artisan test
```

**Importante:** no ejecutar la suite de tests contra la base de datos de desarrollo sin ese aislamiento; `RefreshDatabase` ejecuta migraciones desde cero sobre la BD configurada para `APP_ENV=testing`.

## Base de datos y migraciones

Orden de migraciones coherente con FKs: `users` y tablas Spatie de permisos, `positions`, perfiles (`employee_profiles`, `customer_profiles`, `supplier_profiles`), resto del dominio (`productos`, `orders`, `cinta_slides`, etc.). La columna de roles en `users` **no** se usa: roles en tablas Spatie.

Semilla opcional de cuentas demo (no producción): `php artisan db:seed --class=DemoUsersSeeder`.

### Comando destructivo (solo si lo necesitas a sabiendas)

`migrate:fresh` **elimina todas las tablas y datos**. Úsalo solo en entornos locales/desechables:

```bash
php artisan migrate:fresh --seed
```

## Licencia

[Laravel](https://laravel.com) — [MIT](https://opensource.org/licenses/MIT).
