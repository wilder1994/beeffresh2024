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

**Identidad visual:** variables CSS `--bf-*` en `resources/css/app.css` (crema, marrón del logo, carmesí, sol/dorado); **Figtree** (UI) y **Libre Baskerville** (marca, clase `font-brand` / `fontFamily.brand` en Tailwind); hojas de estilo de fuentes en `resources/views/layouts/partials/fonts.blade.php`. Fondo de página y superficies con degradado crema (`--bf-surface-gradient`, clase `bf-panel-bg` / `bf-surface`); bordes café finos (`--bf-border-brand`, `--bf-border-brand-subtle`).

**Catálogo comercial (admin):** módulo en `/catalogo` con pestañas: Productos, Tipos de carne, Cortes, Promociones, Precios e Inventario. Tablas `meat_types`, `meat_cuts`, `products`; servicios en `App\Services\Catalog\`. Semillas `CatalogSeeder` y `StoreContentSeeder`. Contenido visual del inicio: `store_banners` y `store_highlights` en **Ajustes → Banners inicio / Destacados inicio**.

**Tablas (panel admin):** contenedor `bf-table-panel` (variante `bf-table-panel--flush`), tabla `bf-table` (opcional `bf-table--sticky-head`): encabezado café con degradado, cuerpo crema degradado, celdas con bordes finos. Usado en listados de usuarios, pedidos, cargos y tablas del dashboard.

**Identificación y domicilio (Colombia):** tipos de documento en `App\Domain\Users\ColombianDocumentType` (`x-forms.document-type-select`); país fijo Colombia (`App\Domain\Geo\Colombia`, `x-forms.colombia-country`). Dirección unificada con **`x-forms.colombia-address`**: cascada departamento → ciudad → barrio (datos en `resources/data/colombia-locations.json`, `App\Domain\Geo\ColombianLocations`), icono de mapa (Google Maps: Places, Geocoding) y coordenadas `latitude` / `longitude` en perfiles (`customer_profiles`, `employee_profiles`, `supplier_profiles`). Alpine `colombiaAddressPicker` en `resources/js/addressPicker.js`. Variable de entorno **`GOOGLE_MAPS_API_KEY`** (`config/services.php`); sin clave el formulario funciona, el mapa muestra aviso. Restringe la clave por dominio (localhost, `beeffresh2024.test`, IP LAN si aplica).

**Formularios (panel admin, catálogo, perfil, auth):** clases en `@layer components` de `resources/css/app.css`: contenedor `bf-form-panel` / `bf-form-panel-tight`, barra de filtros `bf-form-toolbar`, secciones internas `bf-form-section` / `bf-form-section--nested`, ítems checkbox `bf-form-check-item`, campos `bf-input`, `bf-select`, `bf-textarea`, `bf-file` (fondo semitransparente y borde café), etiquetas `bf-label` / `bf-label-muted`, acciones `bf-form-actions`, botones `bf-btn-primary` / `bf-btn-ghost`. Paneles de cuenta y login: `bf-account-shell`, `bf-auth-card`; modales de registro y confirmación usan `bf-surface`. El componente Blade `x-text-input` aplica `bf-input` por defecto (login Breeze, perfil). **Usuarios (admin):** alta y edición con **Livewire 3** (`App\Livewire\Admin\UserForm`, vista `resources/views/livewire/admin/user-form.blade.php`, cabecera en `livewire/admin/partials/user-form-header.blade.php`); persistencia en `App\Services\Admin\AdminUserPersistence`. **Cargos:** CRUD en `/admin/positions` (modelo `Position`; el domiciliario es un **cargo** con slug `domiciliario`, no un rol). Tras cambiar CSS o JS, ejecuta `npm run build` (o `npm run dev`) para regenerar assets; `public/build` está en `.gitignore` — en despliegue conviene compilar en CI o en el servidor.

**Página «Nosotros»:** ruta pública `GET /nosotros` (`company_profiles`, registro id 1). El administrador edita el texto y enlaces de redes (WhatsApp, TikTok, Instagram, Facebook, X) en **`/admin/empresa`**. Iconos reutilizables: `x-store.social-icons`.

**Cinta (inicio):** carrusel horizontal a ancho completo en la tienda (`/`), hasta **15** imágenes en proporción **16:9** (recomendado 1920×1080, mínimo 960×540). La validación admite un margen de **±3 %** sobre 16:9 (`config/cinta.php` → `aspect_ratio_tolerance`; regla `App\Rules\CintaImageAspectRatio`). Archivos en disco `public/cinta/…` vía `App\Support\CintaSlideStorage`. El administrador gestiona las diapositivas en **`/admin/cinta`** (Ajustes → Cinta en el sidebar). La marquesina duplica diapositivas hasta 15 tiles para un bucle continuo (`App\Support\CintaMarqueeSlides`).

**Tras login:** clientes van a **`/`** (inicio), no a `/dashboard` (`App\Support\PostLoginRedirect`). Proveedores a `/portal-proveedor`; personal interno a `/dashboard`.

**Ingreso y registro (tienda):** solo los **clientes** pueden registrarse por su cuenta. En la navbar, **Ingresar** despliega Cliente / Empleado / Proveedor (`/login?tipo=cliente|empleado|proveedor`, lógica en `App\Support\AuthLoginAudience`). **Registrarse** abre modales en la tienda: confirmación («te registras como cliente») y formulario completo (`RegisterCustomerRequest`: datos personales, cuenta y domicilio de entrega → `users` + `customer_profiles`). `GET /register` redirige a `/?registro=confirm`. Empleados y proveedores reciben credenciales del administrador. JS: `window.bfOpenRegisterConfirm()` / `bfOpenRegisterClient()` en `resources/js/app.js`; vistas `resources/views/components/auth/*`.

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

1. Configura en `.env`: `DB_*`, `APP_URL`, `ADMIN_*` (administrador inicial vía semillas) y, para el selector de mapa, `GOOGLE_MAPS_API_KEY` (opcional en local si no usas el mapa).
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

**PSR-4:** el catálogo público usa `App\Http\Controllers\Publico\ProductPublicController` en **`app/Http/Controllers/Publico/`** (P mayúscula).

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
| `customer` | **Único rol con registro público**; formulario modal con domicilio completo; tras login → inicio `/` |
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

- **Clientes:** al registrarse se crea `customer_profiles` con domicilio; el checkout exige perfil de entrega completo (`User::hasCompleteDeliveryProfile()`).
- **Proveedores:** razón social, NIT y contacto en `supplier_profiles` (editable también en perfil).
- **Administración:** alta/edición con Livewire; listados `/admin/users`, `/admin/users/clientes`, `/admin/users/empresa`, `/admin/users/proveedores`. **Cargos:** `/admin/positions`. No se expone borrado masivo de usuarios; al cambiar rol se evita dejar sin ningún `admin`.

Al confirmar un pedido, se guarda una **copia de domicilio** en `orders` (`shipping_*`) vía `User::snapshotShippingFromProfile()`.

Listado de usuarios: `App\Repositories\UserRepository` + `App\Contracts\UserRepositoryContract`.

## Rutas útiles

| Área | Ruta / nota |
|------|-------------|
| Tienda (clientes) | `/` (cinta, banners, destacados), `/nosotros`, `/productos-publicos`, `/carrito`, `/checkout` (auth; cliente con perfil de entrega completo) |
| Catálogo admin | `/catalogo/productos` (default), `/catalogo/tipos-carne`, `/catalogo/cortes`, `/catalogo/promociones`, `/catalogo/precios`, `/catalogo/inventario` |
| Contenido inicio | `/admin/contenido-tienda/banners`, `/admin/contenido-tienda/destacados` |
| Auth invitados | `/login?tipo=cliente|empleado|proveedor`; registro cliente vía modal en tienda o `/register` → `/?registro=confirm` |
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

- **Catálogo público:** `/productos-publicos` (rutas `products.public.*`). Cada tarjeta incluye selector **Kg / Lb** (default Kg), cantidad entera y precio según unidad (`x-store.product-purchase`, Alpine + `resources/js/storeCart.js`). Estilos compactos de la fila unidad/cantidad: `.bf-store-unit-toggle` y `.bf-store-qty-input` en `resources/css/app.css` (scoped a `[data-product-purchase]`).
- **Carrito en sesión:** líneas con clave `{product_id}:{kg|lb}`; servicios `App\Services\Catalog\CartSessionService` y `App\Services\Catalog\ProductPromotionResolver`. Conversión a stock: `App\Services\Catalog\CartUnitConverter` (2 lb ≈ 1 kg, coherente con `price_per_lb = price_per_kg ÷ 2`). Badge del carrito: `resources/js/cartBadge.js` + `bfUpdateCartCount()`.
- Solo **cuentas cliente** pueden cerrar compra en línea; **checkout** (`/checkout`, auth) exige perfil de entrega completo (teléfono, dirección, ciudad, provincia).
- Confirmación: tablas **`orders`** (snapshot `shipping_*`) y **`order_items`** (`sale_unit`, `quantity` decimal, `unit_price`, `subtotal`); descuento de stock vía `App\Services\CheckoutService`.
- Panel admin: listado en `/admin/pedidos` (columna resumen de entrega); dashboard admin muestra KPIs, pedidos recientes, stock bajo y volumen de pedidos (`App\Services\AdminDashboardService`).
- **Eliminar producto** (web o API): si el producto tiene líneas en pedidos (`order_items`), el borrado se rechaza con mensaje / HTTP 409 en API (integridad referencial).
- Tras cambios en el catálogo comercial o en `order_items`, en local: `php artisan migrate:fresh --seed` y `npm run build`.

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
