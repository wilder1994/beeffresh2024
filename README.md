# Beeffresh

Plataforma web para digitalizar la gestión de una carnicería: **tienda pública** (inicio, catálogo, carrito, checkout con pedidos en base de datos), **operaciones de despacho y entrega** (despachador, domiciliario, tracking), **contenidos** (videos, recetas, promociones, cortes), **panel de administración** con métricas (KPIs, alertas, stock) y **acceso por roles y permisos** ([Laravel Breeze](https://laravel.com/docs/breeze), **Livewire**, **Spatie Permission**, Sanctum para API).

**Repositorio:** [github.com/wilder1994/beeffresh2024](https://github.com/wilder1994/beeffresh2024)

| Stack | Versión / notas |
|--------|------------------|
| PHP | ^8.2 |
| Laravel | ^11 |
| Realtime | [Laravel Reverb](https://laravel.com/docs/reverb) + Echo (Fase 0–2); [`docs/REALTIME.md`](docs/REALTIME.md) |
| Frontend | Vite, Tailwind CSS, DaisyUI |
| Auth web | [Laravel Breeze](https://laravel.com/docs/breeze), **Livewire 3**, **Spatie Laravel Permission** |
| Auth API | Laravel Sanctum |

**Última actualización de esta documentación:** 2026-05-28

**Identidad visual:** variables CSS `--bf-*` en `resources/css/app.css` (crema, marrón del logo, carmesí, sol/dorado); **Figtree** (UI) y **Libre Baskerville** (marca, clase `font-brand` / `fontFamily.brand` en Tailwind); hojas de estilo de fuentes en `resources/views/layouts/partials/fonts.blade.php`. Fondo de página y superficies con degradado crema (`--bf-surface-gradient`, clase `bf-panel-bg` / `bf-surface`); bordes café finos (`--bf-border-brand`, `--bf-border-brand-subtle`). **Proporción unificada 4:3** en catálogo, home, cinta y tarjetas de producto/oferta/corte (avatares y logo: 1:1).

**Catálogo comercial (admin):** módulo en `/catalogo` con pestañas: Productos, Tipos de carne, Cortes, Promociones, **Combos y packs** (`/catalogo/combos`), **Escalas por volumen** (`/catalogo/escalas-volumen`), Precios e Inventario. Tablas `meat_types`, `meat_cuts`, `products`, `offers`, `offer_items`; servicios en `App\Services\Catalog\` y `App\Services\Store\`. Semillas `CatalogSeeder` y `OfferSeeder`.

**Combos y packs (`offers`):** dos tipos — **bundle** (pack: **mínimo 2 productos** con cantidades, precio fijo del pack; stock = mínimo de unidades posibles) y **volume** (un producto, cantidad mínima **≥ 3 lb** — 3 lb o 1,5 kg —, precio unitario especial en la unidad elegida). Validación en `StoreOfferRequest` / `UpdateOfferRequest`: trait `ValidatesVolumeOffer` (`app/Http/Requests/Catalog/Concerns/`), reglas de mínimo en `App\Support\VolumeOfferConstraints`; el formulario envía un solo `volume_offer_unit_price` y se mapea a `volume_offer_price_kg` o `_lb` al validar. Formulario admin en `resources/views/catalog/offers/_form.blade.php`: cabecera en **dos columnas** (tipo, nombre y descripción a la izquierda; imagen a la derecha, `.bf-offer-form-head`); **pack** con 2 filas iniciales, filas dinámicas Alpine (`x-for` + `_id` único), valor real por línea y fila de precios (valor real del pack | precio del pack | ahorro, `.bf-offer-pack-pricing`); **volume** con fila producto/cantidad/unidad y fila de precios dinámica por unidad (`.bf-offer-volume-*`). Estilos en `app.css`. En carrito y checkout gana el mejor precio entre precio real, promo del producto y oferta por volumen. CRUD en `/catalogo/combos`. Vista pública de packs: `/combos/{slug}`.

**Cinta (inicio):** marquesina automática desde productos y ofertas con **`show_on_cinta`** activo (checkbox en producto u oferta). Sin subida manual de diapositivas. Bucle continuo vía `App\Support\CintaMarqueeSlides` y `App\Services\Store\CintaMerchandisingService`. Estilos compactos en `.bf-cinta-marquee*` (`clamp()` en ancho de slide, `max-height` en viewport/tarjeta, overlay reducido); proporción visual **4:3** (`config/cinta.php`).

**Inicio (tienda):** orden **Cinta → Promociones (productos en promo) → Combos y packs → Destacados → Tipos de corte → Recetas en video → Nosotros**. Datos en `App\Services\Store\HomePageService`, vista `welcome.blade.php`, componentes `x-store.home-*`. Layout compacto en desktop/portátil: contenedor `max-w-6xl`, secciones con menos padding (`.bf-home-section`), grids de productos/cortes hasta **6 columnas** en `xl` (`.bf-home-products__grid`, `.bf-home-cuts__grid`), tarjetas con tipografía y padding reducidos. El **catálogo público** (`/productos-publicos`) reutiliza el mismo grid y `x-store.home-product-card` (teaser sin compra en listado; compra en ficha). Eliminados `store_banners`, `store_highlights` y admin de cinta manual.

**Imágenes (catálogo y perfil):** perfiles en `config/images.php` (catálogo 4:3 → 1200×900 JPEG, logo/avatar 1:1 → 512 px). Subida con recorte previo: `resources/js/imageCropCore.js`, `imageCropEditor.js` (Alpine `imageCropUpload`, `logoCropUpload`), modal `x-bf.image-crop-dialog`, componente `x-bf.image-upload-zone` en productos y ofertas; logo desde sidebar admin (solo `admin`); avatares vía `avatarEditor.js` (reutiliza el núcleo de crop). Exportación client-side antes del POST; validación servidor MIME/peso sin cambios. Permisos: catálogo según `module.catalog`; logo solo administrador; avatar según perfil/Livewire usuarios.

**Tablas (panel admin):** contenedor `bf-table-panel` (variante `bf-table-panel--flush`), tabla `bf-table` (opcional `bf-table--sticky-head`): encabezado café con degradado, cuerpo crema degradado, celdas con bordes finos. Usado en listados de usuarios, pedidos, cargos y tablas del dashboard.

**Identificación y domicilio (Colombia):** tipos de documento en `App\Domain\Users\ColombianDocumentType` (`x-forms.document-type-select`); país fijo Colombia (`App\Domain\Geo\Colombia`, `x-forms.colombia-country`). Dirección unificada con **`x-forms.colombia-address`**: cascada departamento → ciudad → barrio (datos en `resources/data/colombia-locations.json`, `App\Domain\Geo\ColombianLocations`), icono de mapa (Google Maps: Places, Geocoding) y coordenadas `latitude` / `longitude` en perfiles (`customer_profiles`, `employee_profiles`, `supplier_profiles`). Alpine `colombiaAddressPicker` en `resources/js/addressPicker.js`. Variable de entorno **`GOOGLE_MAPS_API_KEY`** (`config/services.php`); sin clave el formulario funciona, el mapa muestra aviso. Restringe la clave por **referrer HTTP** en Google Cloud: localhost, `beeffresh2024.test`, IP LAN y, si usas túnel, `https://*.ngrok-free.app/*` (mapa operativo, seguimiento cliente y selector de dirección).

**Formularios (panel admin, catálogo, perfil, auth):** clases en `@layer components` de `resources/css/app.css`: contenedor `bf-form-panel` / `bf-form-panel-tight`, barra de filtros `bf-form-toolbar`, secciones internas `bf-form-section` / `bf-form-section--nested`, ítems checkbox `bf-form-check-item`, campos `bf-input`, `bf-select`, `bf-textarea`, `bf-file` (fondo semitransparente y borde café), etiquetas `bf-label` / `bf-label-muted`, acciones `bf-form-actions`, botones `bf-btn-primary` / `bf-btn-ghost`. Paneles de cuenta y login: `bf-account-shell`, `bf-auth-card`; modales de registro y confirmación usan `bf-surface`. El componente Blade `x-text-input` aplica `bf-input` por defecto (login Breeze, perfil). **Usuarios (admin):** alta y edición con **Livewire 3** (`App\Livewire\Admin\UserForm`, vista `resources/views/livewire/admin/user-form.blade.php`, cabecera en `livewire/admin/partials/user-form-header.blade.php`); persistencia en `App\Services\Admin\AdminUserPersistence`. **Cargos:** CRUD en `/admin/positions` (modelo `Position`; el domiciliario es un **cargo** con slug `domiciliario`, no un rol). **Frontend (Alpine + Livewire):** `resources/js/app.js` importa Alpine y Livewire desde `vendor/livewire/livewire/dist/livewire.esm.js` y arranca **una sola vez** (`Livewire.start()` cuando existe `@livewireScriptConfig` en el panel); en `layouts/app.blade.php` usar `@livewireScriptConfig` en lugar de `@livewireScripts` para evitar doble inicialización de Alpine (rompe `x-for` en formularios dinámicos). Tras cambiar CSS o JS, ejecuta `npm run build` (o `npm run dev`) para regenerar assets; `public/build` está en `.gitignore` — en despliegue conviene compilar en CI o en el servidor.

**Página «Nosotros»:** ruta pública `GET /nosotros` (`company_profiles`, registro id 1). Iconos: `x-store.social-icons`.

**Configuración de empresa (solo administrador):** `GET /admin/configuracion/empresa` con pestañas **General** (logo, razón social, NIT, contacto), **Ubicación** (dirección Colombia + mapa Google; coordenadas `store_latitude` / `store_longitude` para mapa operativo y asignación) y **Nosotros** (textos y redes de `/nosotros`). Tras elegir dirección en el mapa, confirmar con **Usar esta ubicación** antes de **Guardar ubicación**. Servicio `App\Services\Admin\CompanySettingsService`. Sidebar **Empresa y marca** (solo rol `admin`; `module.settings` ya no abre esta pantalla). Legacy `/admin/empresa` → redirección a pestaña Nosotros. Semilla `CompanyProfileSeeder` en `DatabaseSeeder`. Colores de marca: fijos en `app.css` (`--bf-*`).

**Tras login:** clientes → **`/`**; proveedores → `/portal-proveedor`; **domiciliarios** (`module.courier`) → `/domiciliario/pedidos`; **despachadores** y empleados con `module.orders` → `/admin/pedidos`; resto del personal → `/dashboard` (`App\Support\PostLoginRedirect`, `DashboardController`).

**Ingreso y registro (tienda):** solo los **clientes** pueden registrarse por su cuenta. En la navbar, **Ingresar** despliega Cliente / Empleado / Proveedor (`/login?tipo=cliente|empleado|proveedor`, lógica en `App\Support\AuthLoginAudience`). **Registrarse** abre modales en la tienda: confirmación («te registras como cliente») y formulario completo (`RegisterCustomerRequest`: datos personales, cuenta y domicilio de entrega → `users` + `customer_profiles`). `GET /register` redirige a `/?registro=confirm`. Empleados y proveedores reciben credenciales del administrador. JS: `window.bfOpenRegisterConfirm()` / `bfOpenRegisterClient()` en `resources/js/app.js`; vistas `resources/views/components/auth/*`.

**Perfil y cuenta:** **Mi perfil** (`/profile`) usa panel modal reutilizable (`resources/views/components/account/*`). Avatares en `users.avatar` (`App\Support\UserAvatarStorage`, disco `public/avatars/`). Al cambiar la foto se abre un **editor circular** (girar, zoom, arrastrar para centrar) antes de guardar; lógica en `resources/js/avatarEditor.js` + `imageCropCore.js`, modal unificado `x-bf.image-crop-dialog` (alias `x-avatar.crop-dialog`). Aplica en el modal de perfil y en el formulario Livewire de usuarios (`UserForm`). En admin, vista de cuenta en modal Livewire (`App\Livewire\Admin\UserAccountModal`).

**Videos / recetas:** URLs de YouTube se normalizan a embed (`App\Support\YoutubeEmbedUrl`) en formularios de contenido.

**Migraciones:** `users` mantiene datos de cuenta (nombre, documento, teléfono, email, avatar `users.avatar`, estado). Perfiles en tablas `employee_profiles`, `customer_profiles`, `supplier_profiles`; roles y permisos con **Spatie** (`roles`, `permissions`, tablas pivot). Config publicada: `config/permission.php`. En desarrollo, ante un esquema desalineado: `php artisan migrate:fresh --seed`. En producción ya desplegada conviene migraciones incrementales; este repo define el esquema base para instalaciones nuevas.

El **personal interno** (roles empresa en `layouts.app`) usa **sidebar** (colapsable en escritorio, panel lateral en móvil con overlay). **Operaciones** agrupa pedidos, mapa operativo, catálogo (según permisos) y **Mis entregas** (domiciliarios). **Usuarios** y **Ajustes** son acordeones; cada bloque se despliega abierto si la ruta actual pertenece a ese grupo. En la base del sidebar, **`x-staff.sidebar-account`** muestra avatar, menú de cuenta y **campana de notificaciones** (`<x-notifications.bell />`, también en el header móvil del panel).

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

1. Configura en `.env`: `DB_*`, `APP_URL`, `ADMIN_*` (administrador inicial vía semillas), `GOOGLE_MAPS_API_KEY` (mapas y dirección), `ORDER_COURIER_CLAIM_TIMEOUT_MINUTES` (opcional, default 45) y, para GPS en vivo del domiciliario, `BF_COURIER_GPS_ACTIVE_MS` / `BF_COURIER_GPS_IDLE_MS` / `BF_COURIER_GPS_MIN_METERS` (ver `.env.example` y `config/realtime.php`).
2. Enlaza almacenamiento público para imágenes de productos, promociones, **cinta**, avatares, etc.:

```bash
php artisan storage:link
```

3. Migraciones y semilla por defecto (incluye `AdminUserSeeder`, usuarios demo y catálogo):

```bash
php artisan migrate:fresh --seed
```

Usuarios demo (`DemoUsersSeeder`): contraseña **`password`** (ver tabla en consola al sembrar). Catálogo: `CatalogSeeder`, `OfferSeeder`.

4. Assets (Vite incluye entradas de operaciones: `operationsPolling.js`, `operationsMap.js`, `courierOps.js`, `orderTracking.js`, `notificationBell.js`):

```bash
npm install
npm run build
```

Desarrollo con recarga de assets: `npm run dev`.

5. **Tiempo real y notificaciones:** ver sección [Arranque en desarrollo: ngrok + tiempo real + notificaciones](#arranque-en-desarrollo-ngrok--tiempo-real--notificaciones) (ngrok `8080`, `reverb:start`, `queue:work` con `default,notifications,notifications-email`). Plantillas: `php artisan db:seed --class=NotificationTemplateSeeder`. En local sin SMTP: `MAIL_MAILER=log`.

**Laragon (Windows):** si en PowerShell o Cursor no se reconocen `php`, `composer` o `npm`:

- **PHP:** `C:\laragon\bin\php\php-8.2.29-Win32-vs16-x64\php.exe` (versión activa en `C:\laragon\usr\laragon.ini`).
- **Composer:** `C:\laragon\bin\composer\composer.bat` (requiere que `php` esté en el `PATH` de esa sesión).
- **Node / npm:** `C:\laragon\bin\nodejs\node-v18\` (p. ej. `& 'C:\laragon\bin\nodejs\node-v18\npm.cmd' run build`).

Alternativa: **Menu → Path → Add Laragon to Path** y usar la **terminal de Laragon**, o ejecutar Composer/Artisan desde la raíz del proyecto con esas rutas en el `PATH` de la sesión.

**Livewire:** en `resources/views/layouts/app.blade.php` están `@livewireStyles` (head) y **`@livewireScriptConfig`** (antes de `</body>`); el bundle Vite (`resources/js/app.js`) llama a `Livewire.start()` una vez. Tras `composer install`, conviene `php artisan optimize:clear` si algo de paquetes no se refleja.

**PSR-4:** el catálogo público usa `App\Http\Controllers\Publico\ProductPublicController` en **`app/Http/Controllers/Publico/`** (P mayúscula).

### Acceso en LAN por IP (Laragon / Apache)

En la configuración de Apache de Laragon en la máquina de desarrollo, **Beeffresh** por dirección IP usa el **puerto 8080** (`C:/laragon/etc/apache2/sites-enabled/beeffresh2024-ip.conf`: `VirtualHost *:8080`, `ServerName` = IP LAN). El **puerto 80** en esa misma IP puede quedar reservado para otro proyecto (p. ej. DecoWandy) sin conflicto de cabecera `Host`.

- URL típica desde la red: `http://192.168.18.19:8080` (ajusta la IP si DHCP cambia).
- En `.env`, `APP_URL` debe coincidir con esa URL, p. ej. `APP_URL=http://192.168.18.19:8080`.
- En `httpd.conf` de Apache debe existir `Listen 8080`; tras cambiar la configuración, **reinicia Apache** en Laragon.
- Sigue siendo válido `http://beeffresh2024.test` en el puerto **80** si tienes el virtual host automático (`auto.beeffresh2024.test.conf`) y la entrada en `hosts`.

### Túnel ngrok (pagos Wompi / webhooks)

Para probar checkout y webhooks desde internet (p. ej. Wompi sandbox). **Beeffresh en Laragon escucha en el puerto 8080** (no uses `ngrok http 80`, salvo que expongas otro proyecto en ese puerto).

**Levantar el túnel (Windows):**

1. En Laragon: **Start All** (Apache debe estar activo; comprueba `http://127.0.0.1:8080` o `http://beeffresh2024.test`).
2. En una terminal, desde la carpeta de ngrok (p. ej. `C:\Users\wandy\Downloads\ngrok-v3-stable-windows-amd64`):

```powershell
.\ngrok.exe http 8080
```

3. Copia la URL **HTTPS** que muestra ngrok (plan free: cambia en cada sesión), p. ej. `https://xxxx-xxxx.ngrok-free.app`.
4. Panel local de ngrok (estado y peticiones): `http://127.0.0.1:4040`.

**Configurar el proyecto tras cada nueva URL de ngrok:**

1. En `.env`: `APP_URL=https://<tu-subdominio>.ngrok-free.app` (sin barra final).
2. Limpia configuración en caché:

```bash
php artisan config:clear
```

3. En el panel Wompi → Developers, webhook:

```
https://<tu-subdominio>.ngrok-free.app/webhooks/wompi
```

4. Compila assets (`npm run build`); no basta `npm run dev` para compartir el túnel con terceros.

**Notas:**

- En local (`APP_ENV=local`), `TrustProxies` confía el proxy para que Vite/assets y URLs de pago usen el host HTTPS correcto. `AppServiceProvider` fuerza **HTTPS** y cookies de sesión seguras cuando `APP_URL` empieza por `https://` (evita 419 en POST/PATCH vía túnel).
- **Cerrar sesión:** usar el botón del menú (POST con CSRF). Si el token expiró, `GET /logout` muestra una pantalla intermedia con token fresco (`auth/logout.blade.php`) antes de destruir la sesión.
- Si ngrok responde pero la app no carga, revisa que Laragon/Apache esté encendido y que el túnel apunte a **8080** (VirtualHost en `beeffresh2024-ip.conf`).

### Arranque en desarrollo: ngrok + tiempo real + notificaciones

En local necesitas **Laragon (Apache)** y, para modo *live*, **tres procesos en segundo plano** (más la app web). Puertos habituales en este proyecto:

| Servicio | Puerto | Rol |
|----------|--------|-----|
| Apache / Beeffresh (HTTP) | **8080** | Páginas, API, checkout, webhooks Wompi |
| Laravel Reverb (WebSocket) | **8081** | Echo: pedidos, campana, pagos, stock, métricas |
| ngrok (túnel HTTPS) | → 8080 | Exponer la app a internet (Wompi, pruebas móvil) |

#### Checklist rápido

1. Laragon → **Start All** (comprueba `http://127.0.0.1:8080` o `http://beeffresh2024.test`).
2. **Terminal A — ngrok** (pagos / webhooks / acceso externo a la web):

```powershell
cd C:\ruta\a\ngrok
.\ngrok.exe http 8080
```

Panel de inspección: `http://127.0.0.1:4040`. Copia la URL HTTPS (`https://xxxx.ngrok-free.app`).

3. **Terminal B — Reverb** (WebSocket; no uses el 8080 de Apache):

```bash
php artisan reverb:start
```

Por defecto escucha en **8081** (`REVERB_SERVER_PORT` en `.env`).

4. **Terminal C — colas** (obligatorio: `ShouldBroadcast`, emails y jobs de notificaciones):

```bash
php artisan queue:work database --queue=default,notifications,notifications-email --tries=3
```

5. Tras cambiar `.env` o variables `VITE_*`:

```bash
php artisan config:clear
npm run build
```

#### Variables `.env` (tiempo real)

```env
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database

REVERB_APP_ID=beeffresh-local
REVERB_APP_KEY=local-reverb-key
REVERB_APP_SECRET=local-reverb-secret
REVERB_HOST=localhost
REVERB_PORT=8081
REVERB_SCHEME=http
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8081

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

NOTIFICATION_QUEUE=notifications
NOTIFICATION_EMAIL_QUEUE=notifications-email
```

Con **ngrok solo en 8080** (misma PC abriendo el panel por la URL del túnel), suele bastar `REVERB_HOST=localhost` y `REVERB_SCHEME=http`: el navegador conecta el WS a `localhost:8081`. Si pruebas desde **otro dispositivo**, abre un segundo túnel `ngrok http 8081` y alinea `REVERB_HOST` / `VITE_REVERB_HOST` con ese host (y `REVERB_SCHEME=https` si el túnel es HTTPS).

Tras cada URL nueva de ngrok, actualiza `APP_URL`, `php artisan config:clear`, webhook Wompi y vuelve a compilar assets (`npm run build`).

#### Qué hace cada capa

| Capa | Sin proceso | Con procesos + `BROADCAST_CONNECTION=reverb` |
|------|-------------|-----------------------------------------------|
| **Pedidos** (`/admin/pedidos`) | Polling 15 s inserta/parchea tarjetas | WS `order.updated` + polling respaldo |
| **Campana** | Polling 30 s | WS `notification.created` + polling 30 s |
| **Pago Wompi** | Polling 2,5 s | WS `payment.status.updated` + polling |
| **Métricas ops** | Feed indirecto | WS `operations.metrics.updated` (job en cola `default`) |
| **Seguimiento pedido** | Polling 12 s | WS `order.tracking.updated` + invitado en canal público `tracking.{token}` + polling 12–24 s |
| **Mapa operativo** (`/admin/pedidos/mapa`) | Polling 15–30 s | WS `operations.map.updated`, `courier.location.updated`, `courier.presence.updated` + polling |
| **GPS domiciliario** | `watchPosition` ~12 s en ruta / ~45 s en espera | `POST /domiciliario/ubicacion` → `courier.location.updated`; pin animado en mapa (`mapUi.js`) |

- **Mapa operativo (UI):** banner `cabecera`, indicador realtime, mapa a alto `calc(100dvh − offset)` (`staff_map_page`, sin footer). Centro del mapa = coords de tienda en `company_profiles`.
- **GPS:** requiere panel domiciliario abierto, permiso de ubicación y **Reverb + cola** para ver movimiento casi en vivo; con *Modo respaldo (polling)* el pin se actualiza cada 15–30 s.

Indicador en operaciones: `<x-realtime.status-indicator />` — *Operación en tiempo real* (WS + cola OK), *Sincronización diferida* (cola lenta) o *Modo respaldo (polling)*. Salud: `GET /admin/realtime/health` (staff).

#### Notificaciones (campana + email + sonido)

- **Campana (dropdown):** solo **no leídas** (`GET /notificaciones/feed?scope=unread`, scroll). Clic en un ítem → marca leída y abre destino (rutas relativas; enlaces antiguos de ngrok se normalizan con `App\Support\NotificationActionUrl`). **Centro (modal):** `notification-center` — historial paginado (`GET /notificaciones/historial`), preferencias y sonido; enlace «Abrir página completa» → `/notificaciones`. Atajo: `?open=notifications`.
- **Tiempo real:** evento `NotificationCreated` → canal privado `App.Models.User.{id}` → `notificationBell.js` (badge, lista, toast). Requiere **Reverb + cola** (el broadcast se encola en `default`).
- **Sonido en el navegador (no es un canal de preferencias):** tono tipo **campana** (`public/sounds/notification.wav`, ~0,55 s). Lógica en `resources/js/realtime/utils/notificationSound.js`. Solo suena con alertas **nuevas** por el centro interno (WS o polling 30 s). Volumen = sistema operativo / pestaña del navegador. **Silenciar:** «Sonido activo» en campana o modal (`localStorage` `bf:notifications:sound-muted`). Tras recargar hace falta **un clic** en la página (autoplay). Regenerar WAV: `php scripts/generate-notification-sound.php`.
- **Preferencias de canal** (`notification_preferences`, modal y `/notificaciones`, `PATCH /notificaciones/preferencias`): controlan **cómo se envían** las alertas automáticas (pedidos, pagos, etc.), no el historial ni el sonido del navegador.

| Preferencia en UI | Canal | ¿Implementado? | Efecto si está activo |
|-------------------|--------|----------------|------------------------|
| **Centro interno** | `internal` | Sí | Bandeja + campana + tiempo real + toast; base del sonido en pestaña |
| **Correo electrónico** | `email` | Sí | Envío por email (plantillas; en local `MAIL_MAILER=log` → `storage/logs/laravel.log`) |
| **Push (próximamente)** | `push` | No (stub) | Reservado para notificaciones del SO/navegador fuera de la pestaña; checkbox deshabilitado; el resolver ignora canales no implementados |
| **Sonido activo** | — | Solo cliente | Ver fila anterior; no se guarda en BD |

- Si desmarcas **Centro interno**, dejas de recibir avisos en la app para los tipos que usan ese canal. Si desmarcas **Correo**, no se encolan emails. **Push** no cambia el comportamiento actual hasta implementar `PushNotificationChannel` (p. ej. Web Push + permisos del navegador).
- **Portal proveedor:** campana en `layouts/supplier` y mismo modal/página de notificaciones.
- **Entrega email / reintentos:** jobs en colas `notifications` y `notifications-email` (mismo worker de la Terminal C).
- **Retrasos / pedidos demorados:** `php artisan notifications:check-delayed-orders` (programar en scheduler si aplica).

Documentación detallada: [`docs/REALTIME.md`](docs/REALTIME.md) · [`docs/NOTIFICATIONS.md`](docs/NOTIFICATIONS.md).

### Realtime (referencia técnica)

**Stack:** Laravel 11, `laravel/reverb`, `laravel-echo`, canales privados y públicos (`routes/channels.php`). Frontend MPA: `resources/js/realtime/` (Echo + DOM patch granular; **sin SPA**).

| Fase | Alcance |
|------|---------|
| **1** | Campana, grid `/admin/pedidos`, pago Wompi (WS + polling) |
| **1.5** | Stock tienda/inventario, métricas ops, fulfill post-pago, dashboard low-stock |
| **1.5-STAB** | markReady un broadcast, coalesce métricas, health degradado, grid ops siempre presente |
| **2** | Tracking cliente/staff, mapa operativo live, GPS courier, presencia básica, resync reconnect |

**Servicios broadcast (`App\Services\Realtime\`):**

| Servicio | Evento(s) | Notas |
|----------|-----------|--------|
| `OrderBroadcastService` | `OrderUpdated` + dispara tracking/mapa | `afterCommit` |
| `StockBroadcastService` | `ProductStockUpdated`, availability | — |
| `OperationsMetricsBroadcastService` | `OperationsMetricsUpdated` | job unique 2s |
| `TrackingBroadcastService` | `OrderTrackingUpdated` | coalesce 2s; timeline + courier |
| `CourierLocationBroadcastService` | `CourierLocationUpdated` | throttle por ruta/idle (`config/realtime.php`); job unique 3s |
| `OperationsMapBroadcastService` | `OperationsMapUpdated` | coalesce ~1s |
| `CourierPresenceBroadcastService` | `CourierPresenceUpdated` | disponible/ocupado |

**Eventos Fase 2 (alias WS):** `order.tracking.updated`, `courier.location.updated`, `operations.map.updated`, `courier.presence.updated`.

**Canales:** privados `operations.map`, `operations.couriers`, `orders.{id}`, `couriers.{id}`; público `tracking.{token}` (invitado, token no enumerable). Metas Blade: `bf-tracking-token`, `bf-order-id`, `bf-staff-operations-map`, `bf-courier-id`.

**Frontend Fase 2:** `channels/tracking.js`, `maps.js`, `couriers.js`; handlers `trackingHandler`, `operationsMapHandler`, `courierLocationHandler`, `courierPresenceHandler`; utils `trackingUi.js`, `mapUi.js` (`bfPatchOrderMarker`, `bfPatchCourierMarker`). Entradas Vite: `orderTracking.js`, `operationsMap.js`, `courierOps.js` (polling fallback intacto). Reconnect: evento `bf:realtime-resync`.

| Método | Ruta | Uso |
|--------|------|-----|
| `GET` | `/admin/realtime/health` | Salud cola + modo `live` / `degraded` / `fallback` |
| `GET` | `/admin/pedidos/feed` | Feed polling operaciones (15s) |
| `GET` | `/admin/pedidos/mapa/datos` | JSON mapa (store, pedidos, couriers) |
| `GET` | `/admin/pedidos/{order}/fragmento-tarjeta` | HTML tarjeta para insert realtime |
| `GET` | `/seguimiento/{tracking_token}/feed` | Feed tracking invitado (polling) |
| `POST` | `/domiciliario/ubicacion` | GPS domiciliario (throttle + broadcast) |
| `POST` | `/domiciliario/pedidos/{order}/aceptar` | Domiciliario reclama pedido listo (primer click) |
| `POST` | `/admin/pedidos/{order}/asignar-domiciliario` | Asignación manual desde operaciones |
| `GET` | `/carrito/validar` | Validación stock en carrito (sin WS) |

**Tests:** `php artisan test --filter=Broadcasting` · `php artisan test --filter=Realtime` (incluye `CourierLocationBroadcastTest`, `OrderTrackingRealtimeTest`, `OperationsMapRealtimeTest`, `TrackingGuestAuthorizationTest`, `CourierPresenceTest`) · `RealtimeHealthTest` · `MetricsCoalesceTest`.

**Arranque local (4 procesos):** Laragon/nginx **8080**, `php artisan reverb:start` **8081**, `php artisan queue:work --queue=default,notifications,notifications-email`, y `npm run build` tras cambiar `VITE_REVERB_*`. Sin Reverb/cola → modo respaldo honesto (polling).

**Nequi sandbox:** solo `3991111111` (aprobado) y `3992222222` (rechazado); cualquier otro número devuelve `ERROR`.

### Logo de la empresa y fotos de perfil

- **Logo comercial** (`logos.tipo = principal`): en **Empresa y marca → General** o con el **icono de cámara** del sidebar (`POST /admin/logo/empresa`, solo admin). Por defecto: `public/logos/logo.jpeg`.
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

Roles de aplicación (guard `web`): `admin`, `employee`, `customer`, `supplier`. Constantes en `App\Domain\Users\RoleSlug`. Los permisos de módulo para empleados viven en `App\Domain\Users\PermissionKey`; se sembraron con `RolePermissionSeeder`. El **administrador** pasa todas las comprobaciones `can()` vía `Gate::before` en `AuthServiceProvider`.

| Permiso | Uso |
|---------|-----|
| `module.catalog` | CRUD catálogo, videos, recetas |
| `module.orders` | Centro de operaciones `/admin/pedidos` |
| `module.courier` | Portal domiciliario `/domiciliario/pedidos` |
| `module.users` | Usuarios, cargos |
| `admin` (rol) | Configuración **Empresa y marca** (`/admin/configuracion/empresa`) |

| Rol | Uso |
|-----|-----|
| `customer` | **Único rol con registro público**; formulario modal con domicilio completo; tras login → inicio `/` |
| `admin` | Panel completo, usuarios, pedidos, CRUD catálogo, API mutaciones |
| `employee` | Personal interno; **cargo** en `employee_profiles` → `positions` (`despachador`, `domiciliario`, etc.) + permisos Spatie directos |
| `supplier` | Portal `/portal-proveedor`; datos comerciales en `supplier_profiles` |

**Cargos operativos** (tabla `positions`, no roles Spatie): slug `despachador` (alistamiento y despacho; permiso `module.orders`) y slug `domiciliario` (entregas; permiso `module.courier`). Políticas en `App\Policies\OrderPolicy`.

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
| Tienda (clientes) | `/` (cinta automática, promos, combos, destacados), `/nosotros`, `/productos-publicos`, `/combos/{slug}`, `/carrito`, `/checkout` (auth; cliente con perfil de entrega completo), **`/mis-pedidos`** (historial de pedidos, menú avatar) |
| Catálogo admin | `/catalogo/productos`, `/catalogo/combos`, `/catalogo/tipos-carne`, `/catalogo/cortes`, `/catalogo/promociones`, `/catalogo/precios`, `/catalogo/inventario` |
| Auth invitados | `/login?tipo=cliente|empleado|proveedor`; registro cliente vía modal en tienda o `/register` → `/?registro=confirm` |
| Configuración empresa (admin) | `GET /admin/configuracion/empresa` (?tab=general\|ubicacion\|nosotros); `PUT …/general`, `…/ubicacion`, `…/nosotros` — `company_profiles` + logo (`logos`) |
| Dashboard | `/dashboard` (admin/empleado con KPIs; **clientes** usan inicio `/` tras login) |
| Panel admin (atajo) | `GET /admin` redirige a `/dashboard` (evita 404) |
| Pedidos (operaciones) | `/admin/pedidos` (centro de despacho), `/admin/pedidos/mapa`, ticket `/admin/pedidos/{order}/ticket` — permiso `module.orders`; cargo **Despachador** (`positions.slug = despachador`) |
| Domiciliario | `/domiciliario/pedidos` — permiso `module.courier`; cargo domiciliario (`domiciliario`) |
| Seguimiento cliente | `/mis-pedidos` (historial, auth cliente), `/mis-pedidos/{order}/seguimiento` (auth) o `/seguimiento/{tracking_token}` (invitado) |
| Notificaciones | `/notificaciones` (página respaldo), modal centro (`notification-center`), `/notificaciones/feed?scope=unread` (campana), `/notificaciones/historial` (JSON paginado), `PATCH /notificaciones/marcar-todas`, preferencias por usuario |
| Pagos (admin) | `/admin/pagos` — transacciones, webhooks, intentos |
| Checkout / pago | `POST /checkout/pagar` → widget Wompi; webhook `POST /webhooks/wompi` |
| Usuarios (admin) | `/admin/users`, Livewire create/edit; `/admin/positions` (cargos) |
| Portal proveedor | `/portal-proveedor` (auth + rol supplier) |
| Perfil Breeze | `/profile` |

La **navbar marrón** del layout interno (`layouts.app`) agrupa acceso a la vista cliente (inicio tienda, catálogo, carrito) y enlaces de gestión para administradores (incluye **Usuarios**).

## Tienda y pedidos

- **Catálogo público:** `/productos-publicos` — grid compacto `bf-home-products__grid` + `x-store.home-product-card` (teaser: precio y “Ver producto →”; sin compra en listado). Compra (Kg/Lb, cantidad, agregar) solo en ficha `/productos-publicos/{slug}` (`x-store.product-purchase`, layout `.bf-store-product-detail`). Presentación de precios/badge: `App\Services\Store\StoreCatalogCardPresenter`. Filtro `?promo=1` para promociones activas. Tests: `PublicCatalogViewsTest`.
- **Carrito en sesión:** líneas `product:{id}:{kg|lb}` u `offer:{id}` (packs); servicios `App\Services\Catalog\CartSessionService`, `App\Services\Store\ProductBestPriceResolver`, `App\Services\Store\OfferPricingService`, `App\Services\Store\OfferAvailabilityService` y `App\Services\Catalog\ProductPromotionResolver`. Conversión a stock: `App\Services\Catalog\CartUnitConverter` (2 lb ≈ 1 kg). Badge del carrito: `resources/js/cartBadge.js` + `bfUpdateCartCount()`.
- Solo **cuentas cliente** pueden cerrar compra en línea; **checkout** (`/checkout`, auth) exige perfil de entrega completo (teléfono, dirección, ciudad, provincia).
- Confirmación: tablas **`orders`** (snapshot `shipping_*`, `tracking_token`, estados operacionales) y **`order_items`**; el **stock se descuenta solo cuando el pago es aprobado** (webhook Wompi), no al iniciar checkout.
- **Pagos en línea:** arquitectura multi-pasarela (`PaymentGatewayInterface`, `PaymentGatewayManager`; drivers Wompi activo, PayPal/Mercado Pago/Stripe/ePayco placeholder). Tablas `payments`, `payment_attempts`, `payment_webhooks`. Flujo: checkout → intención de pago → widget Wompi → webhook → pedido + operaciones. Post-pago: `/pago/procesar/{uuid}` y `/pago/pendiente/{uuid}` con **websocket** (`payment.status.updated` en canal privado `payments.{uuid}`) + **polling JSON de respaldo** (`GET /pago/estado/{uuid}`, `resources/js/paymentProcess.js`). El webhook sigue siendo la fuente de verdad; el WS acelera la UI. Al aprobar vacía la sesión `carrito` y actualiza el badge. Logs: `storage/logs/payments.log`. Variables `.env`: `WOMPI_*`, `PAYMENT_DEFAULT_GATEWAY`. Tests: `tests/Feature/Payments/PaymentWebhookFlowTest.php`, `PaymentPollTest.php`.
- **Mis pedidos (cliente):** listado paginado en `/mis-pedidos` (`CustomerOrderController`, vista `store/orders/index.blade.php`, tarjeta `x-store.order-card`). Enlace en menú avatar y menú móvil tienda; desde pago exitoso (“Ver todos mis pedidos”). Clic en un pedido → seguimiento en vivo.
- **Seguimiento cliente:** `/mis-pedidos/{order}/seguimiento` o `/seguimiento/{tracking_token}`. Layout dos columnas (timeline + mapa). Mapa en vivo solo entre **recogido** y **entregado** (`CustomerTrackingMapPhase`, `trackingMap.js`); fases *waiting* / *closed* con mensaje. Timeline vía `order.tracking.updated` (canal público `tracking.{token}` para invitados) + polling 12 s (`orderTracking.js`). Fechas en **`America/Bogota`**. Ver [`docs/REALTIME.md`](docs/REALTIME.md) Fase 2.
- **Notificaciones (núcleo):** `App\Services\Notifications\` — `NotificationService`, `NotificationPreferenceService`, canales (`internal`, `email` activos; `push`/WhatsApp/SMS stubs). **UI:** campana solo no leídas; modal `<x-notifications.center-dialog />` (historial + preferencias + sonido); página `/notificaciones` como respaldo. JS: `notificationBell.js`, `notificationCenter.js`, `notificationSound.js`. Tests: `NotificationSystemTest`, `NotificationFeedScopeTest`, `SupplierNotificationCenterTest`. Detalle: [`docs/NOTIFICATIONS.md`](docs/NOTIFICATIONS.md), [`docs/REALTIME.md`](docs/REALTIME.md).
- **Operaciones y despacho:** al **Marcar listo para recoger** el pedido queda sin domiciliario; se notifica a **todos los domiciliarios disponibles** (`available_couriers`). En `/domiciliario/pedidos` ven la cola **Disponibles** y **Aceptar** (`POST /domiciliario/pedidos/{order}/aceptar`, `CourierAssignmentService::claimByCourier` con bloqueo). Operaciones puede **asignar manualmente** en `/admin/pedidos/{order}` (`POST …/asignar-domiciliario`). Timeout configurable: `ORDER_COURIER_CLAIM_TIMEOUT_MINUTES` / `config/orders.php`; si nadie acepta, `php artisan orders:notify-unclaimed-ready` alerta a operaciones (scheduler cada 15 min). Grid en vivo (`order.updated`) + **mapa operativo** + GPS domiciliario como antes.
- Panel admin dashboard: KPIs, pedidos recientes, stock bajo (`App\Services\AdminDashboardService`).
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
php artisan test --filter=OrderOperationsFlowTest
```

Cobertura relevante: flujo operacional de pedidos (`OrderOperationsFlowTest`, `CourierOrderClaimTest`, `MarkReadyBroadcastTest`), configuración empresa (`CompanySettingsTest`), historial y seguimiento cliente (`CustomerOrderHistoryTest`, `OrderTrackingTimelineTest`, `CustomerTrackingMapTest`), **notificaciones** (`NotificationSystemTest`, `NotificationFeedScopeTest`, `SupplierNotificationCenterTest`), **broadcasting / Reverb** (`tests/Feature/Broadcasting/`, `tests/Feature/Realtime/`, incl. `CourierLocationBroadcastTest`), pagos Wompi (`tests/Feature/Payments/`), carrito, catálogo público compacto y escalas por volumen (`tests/Feature/Store/`, incl. `PublicCatalogViewsTest`), catálogo admin de ofertas (`tests/Feature/Catalog/`).

**Importante:** no ejecutar la suite de tests contra la base de datos de desarrollo sin ese aislamiento; `RefreshDatabase` ejecuta migraciones desde cero sobre la BD configurada para `APP_ENV=testing`.

## Base de datos y migraciones

Orden de migraciones coherente con FKs: `users` y tablas Spatie de permisos, `positions`, perfiles, catálogo (`products`, `offers`, `offer_items`), `orders` / `order_items`, operaciones (`order_status_logs`, `order_assignments`, `courier_locations`, `delivery_proofs`; columnas extra en `orders` y coordenadas de tienda en `company_profiles` — migración `2026_05_19_100000_order_operations`), **notificaciones** (`notifications`, `notification_deliveries`, `notification_templates`, `notification_preferences` — `2026_05_25_120000_create_notification_system`), cola **`jobs`** (`2026_05_25_120001_create_jobs_table`), etc.

Semilla opcional de cuentas demo (no producción): `php artisan db:seed --class=DemoUsersSeeder`. Contraseña: valor de `ADMIN_PASSWORD` en `.env` (por defecto `password`). Cuentas útiles para operaciones:

| Rol / cargo | Correo demo |
|-------------|-------------|
| Despachador | `despachador1@demo.beeffresh.test` |
| Domiciliario | `empleado2@demo.beeffresh.test` |
| Cliente (con coords) | `cliente2@demo.beeffresh.test` |

### Comando destructivo (solo si lo necesitas a sabiendas)

`migrate:fresh` **elimina todas las tablas y datos**. Úsalo solo en entornos locales/desechables:

```bash
php artisan migrate:fresh --seed
```

## Licencia

[Laravel](https://laravel.com) — [MIT](https://opensource.org/licenses/MIT).
