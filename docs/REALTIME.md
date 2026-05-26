# BF Realtime — Fase 0 (Laravel Reverb)

**Última actualización:** 2026-05-24

## Fase 1.5 — Estabilización operacional (activa)

| Módulo | WebSocket | Polling fallback |
|--------|-----------|------------------|
| Grid operaciones + pedido nuevo | `order.updated` (post-fulfill + courier) → insert/parche | 15s |
| Métricas ops + dashboard KPI pendientes | `operations.metrics.updated` | 15s (indirecto vía feed) |
| Inventario admin | `product.stock.updated` → filas `[data-inventory-product-id]` | — |
| Tienda (guest) | `product.availability.updated` en canal público `store.catalog` (sin stock numérico) | — |
| Campana / pago | Sin cambios Fase 1 | 30s / 2.5s |

### Servicios broadcast (único punto de emisión)

- `App\Services\Realtime\StockBroadcastService`
- `App\Services\Realtime\OrderBroadcastService`
- `App\Services\Realtime\OperationsMetricsBroadcastService`

Usan `DB::afterCommit` cuando hay transacción abierta (equivalente a *after commit* en esta versión de Laravel).

### Eventos nuevos

| Evento | Alias | Canales |
|--------|-------|---------|
| `ProductStockUpdated` | `product.stock.updated` | `operations.inventory`, `operations.dashboard` |
| `ProductAvailabilityUpdated` | `product.availability.updated` | `store.catalog` (público) |
| `OperationsMetricsUpdated` | `operations.metrics.updated` | `operations.dashboard`, `operations.orders` |

`OrderUpdated` añade canal `operations.dashboard`.

### Eventos DOM nuevos

- `bf:ops-metrics-updated`
- `bf:product-stock-updated`
- `bf:product-availability-updated`

### Fase 1.5-STABILIZATION (2026-05-24)

| STAB | Implementación |
|------|----------------|
| markReady | `OrderWorkflowService::transitionSilent()` + `CourierAssignmentService` sin broadcast + un `OrderBroadcastService::dispatch()` |
| Métricas | `BroadcastOperationsMetricsJob` (`ShouldBeUnique`, ventana 2s) + `RealtimeMetricsContext` evita duplicar stock+order en la misma request |
| DOM ops | `opsInsertGuards.js` (locks + TTL 5s), polling no inserta tarjetas nuevas, inserts solo `data-ops-page="1"` |
| Health | `GET /admin/realtime/health`, `healthMonitor.js` (60s), `realtimeStore` modos live/degraded/fallback |
| Reconnect | `bfResyncOperationsAfterReconnect()` — feed + parche sin reload |
| Dashboard | `[data-dashboard-low-stock-*]`, template `#bf-low-stock-row-tpl`, `stockUi.js` |
| Carrito | `GET /carrito/validar`, `cartValidate.js` (deshabilita checkout si agotado) |
| Campana | `localStorage` key `bf:notifications:unread` + evento `storage` entre pestañas |
| Ruido | Canales map/tracking huérfanos en noop; sin `bf:dashboard-order-updated` duplicado |

**Respuesta health (staff):** `websocket_connected` (false en servidor; el cliente usa estado Echo), `queue_healthy`, `pending_jobs`, `oldest_pending_seconds`, `mode`, `fallback_mode`.

## Fase 1 — Realtime operacional

| Módulo | WebSocket | Polling fallback |
|--------|-----------|------------------|
| Campana + badge + dropdown | `notification.created` → DOM patch + toast | 30s `notificationBell.js` |
| Grid operaciones | `order.updated` → parche tarjeta | 15s sin reload |
| Pago Wompi | `payment.status.updated` → UI + redirect | 2.5s `paymentProcess.js` |

### Store central

`resources/js/realtime/stores/realtimeStore.js` — conexión, reconnecting, fallback, métricas, timestamps.

### Eventos DOM

- `bf:realtime-connected` / `bf:realtime-disconnected` / `bf:realtime-reconnecting`
- `bf:notification-created`
- `bf:order-updated`
- `bf:payment-status-updated`

### Handlers

- `handlers/notificationsHandler.js`
- `handlers/operationsHandler.js`
- `handlers/paymentHandler.js`
- `handlers/statusIndicator.js`

### API nueva

`GET /admin/pedidos/{order}/fragmento-tarjeta` — HTML de tarjeta para pedidos nuevos en grid.

---

# BF Realtime — Fase 0 (Laravel Reverb)

---

## Arquitectura

```text
Dominio (PHP)                Cola (database)           Reverb (WS)
─────────────                ───────────────           ────────────
OrderWorkflowService  ──►   OrderUpdated      ──►    private-operations.orders
NotificationRepository ──►  NotificationCreated ──►   private-App.Models.User.{id}
PaymentWebhookProcessor ►  PaymentStatusUpdated ──►  private-payments.{uuid}

Frontend (Vite)
───────────────
app.js → bootstrapBfRealtime() → Echo (Reverb)
       → channels/* (listeners desacoplados, eventos DOM bf:*)
       → polling legacy sigue activo en módulos BF-* existentes
```

### Coexistencia websocket + polling

| Módulo | Polling (activo) | WebSocket (Fase 0) |
|--------|-------------------|---------------------|
| Operaciones pedidos | `operationsPolling.js` (15s parche DOM) | Parche tarjeta vía `bf:order-updated` |
| Mapa operativo | `operationsMap.js` (15s) | Polling only (WS map desactivado en STAB) |
| Tracking pedido | `orderTracking.js` (12s) | Polling only (WS tracking desactivado en STAB) |
| Campana notificaciones | `notificationBell.js` (30s) | Badge + dropdown + toast instantáneo |
| Pago Wompi | `paymentProcess.js` (2.5s) | Redirect/UI instantáneo en `payment.status.updated` |
| Courier GPS | `courierOps.js` (45s POST) | Canal `couriers.{id}` autorizado, sin UI aún |

---

## Stack

- **Laravel 11** (upgrade desde 10)
- **laravel/reverb** — servidor WebSocket oficial
- **laravel-echo** + **pusher-js** — cliente (protocolo Pusher compatible)
- **BroadcastServiceProvider** habilitado
- **Canales privados** con policies Spatie / ownership

---

## Variables de entorno

Copiar de `.env.example`:

```env
# Estable sin WS (default Fase 0)
BROADCAST_CONNECTION=log

# Activar broadcast real
BROADCAST_CONNECTION=reverb

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
```

> **Puerto:** usar `8081` para Reverb si Laragon/ngrok ya ocupan `8080`.

---

## Comandos locales

Terminal 1 — aplicación (Laragon):

```bash
php artisan serve
# o Laragon Start All
```

Terminal 2 — colas (notificaciones + broadcast en cola):

```bash
php artisan queue:work database --queue=default,notifications,notifications-email
```

Terminal 3 — Reverb:

```bash
php artisan reverb:start
```

Frontend:

```bash
npm run dev
# o npm run build
```

Limpiar caché tras cambiar `.env`:

```bash
php artisan optimize:clear
```

---

## Eventos broadcast (Fase 0)

| Evento | Alias | Canales privados | Disparador |
|--------|-------|------------------|------------|
| `App\Events\OrderUpdated` | `order.updated` | `operations.orders`, `orders.{id}` | `OrderWorkflowService` |
| `App\Events\NotificationCreated` | `notification.created` | `App.Models.User.{id}` | `NotificationRepository::createInboxNotification` |
| `App\Events\Payments\PaymentStatusUpdated` | `payment.status.updated` | `payments.{uuid}` | `PaymentWebhookProcessor::applyPaymentStatus` |

Payloads: solo datos operacionales mínimos (sin PII extra).

---

## Canales (`routes/channels.php`)

| Canal | Autorización |
|-------|--------------|
| `App.Models.User.{id}` | Mismo usuario |
| `orders.{orderId}` | `OrderPolicy@view` |
| `operations.orders` | `canAccessOrderOperations()` o `isDispatcher()` |
| `operations.dashboard` | Operaciones, despacho o admin |
| `couriers.{courierId}` | Propio courier con módulo o staff operaciones |
| `payments.{paymentUuid}` | `PaymentPolicy@view` |

**No hay canales públicos** para pedidos ni pagos.

---

## Frontend (`resources/js/realtime/`)

```text
realtime/
  index.js          # bootstrapBfRealtime()
  echo.js           # window.Echo + reconexión + logs
  channels/
    notifications.js
    operations.js
    tracking.js
    payments.js
    maps.js
  stores/
    connectionStore.js
  utils/
    logger.js
    dom.js
```

Metadatos Blade (`layouts/partials/realtime-meta.blade.php`):

- `bf-user-id` — usuario autenticado
- `bf-staff-operations` — staff operaciones
- `bf-order-id` — tracking autenticado
- `bf-payment-uuid` — pantallas de pago
- `bf-staff-operations-map` — mapa operativo

Eventos DOM para Fase 1: `bf:notification-created`, `bf:order-updated`, `bf:payment-status-updated`, etc.

Debug: `VITE_BF_REALTIME_DEBUG=true` o entorno `dev`.

---

## Debug WebSocket

1. Consola navegador: buscar `[BF-Realtime]`.
2. Verificar `VITE_REVERB_*` tras `npm run build`.
3. `php artisan reverb:start` en ejecución.
4. `BROADCAST_CONNECTION=reverb` (no `log`).
5. Worker de colas activo (eventos `ShouldBroadcast` van a cola).
6. Auth canal: `POST /broadcasting/auth` (419 = CSRF; requiere sesión).
7. Invitado tracking: **sin Echo** (canal privado); sigue polling.

---

## Tests

```bash
php artisan test --filter=Broadcasting
```

- `BroadcastingAuthorizationTest` — auth de canales
- `RealtimeEventsTest` — eventos y canales broadcast

---

## Producción futura

- Reverb detrás de proxy TLS (WSS); alinear `REVERB_HOST` / `REVERB_SCHEME` con dominio público.
- `allowed_origins` en `config/reverb.php` (restringir, no `*`).
- Scaling Reverb con Redis (`REVERB_SCALING_ENABLED`).
- Supervisor/systemd para `reverb:start` y `queue:work`.
- Fase 1: sustituir polling por handlers que escuchen eventos `bf:*`.

---

## Fase 2+ (pendiente)

1. Mapa operativo live (`operationsMap.js`)
2. Tracking guest websocket
3. Dashboard métricas `operations.dashboard`
4. Courier GPS live
5. Redis scaling / Horizon
