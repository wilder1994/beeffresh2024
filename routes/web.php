<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\CompanySettingsController;
use App\Http\Controllers\Admin\LogoController;
use App\Http\Controllers\Admin\OrderOperationsController;
use App\Http\Controllers\Admin\RealtimeHealthController;
use App\Http\Controllers\Admin\OrderTicketController;
use App\Http\Controllers\Courier\CourierOrderController;
use App\Http\Controllers\Store\CustomerOrderController;
use App\Http\Controllers\Store\OrderTrackingController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Catalog\CatalogController;
use App\Http\Controllers\Catalog\InventoryController;
use App\Http\Controllers\Catalog\MeatCutController;
use App\Http\Controllers\Catalog\MeatTypeController;
use App\Http\Controllers\Catalog\OfferController;
use App\Http\Controllers\Catalog\PricingController;
use App\Http\Controllers\Catalog\ProductController as CatalogProductController;
use App\Http\Controllers\Catalog\PromotionOverviewController;
use App\Http\Controllers\SupplierPortalController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\PaymentAdminController;
use App\Http\Controllers\Store\PaymentCheckoutController;
use App\Http\Controllers\Webhooks\WompiWebhookController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Publico\OfferPublicController;
use App\Http\Controllers\Publico\ProductPublicController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\NosotrosController;
use App\Http\Controllers\VideoRecetaController;
use Illuminate\Support\Facades\Route;

Route::get('/productos-publicos', [ProductPublicController::class, 'index'])->name('products.public.index');
Route::get('/productos-publicos/{product:slug}', [ProductPublicController::class, 'show'])->name('products.public.show');
Route::get('/combos/{offer:slug}', [OfferPublicController::class, 'show'])->name('offers.public.show');

Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])
    ->middleware('throttle:60,1')
    ->name('carrito.agregar');
Route::post('/carrito/agregar-pack', [CarritoController::class, 'agregarOffer'])
    ->middleware('throttle:60,1')
    ->name('carrito.agregar-offer');
Route::get('/carrito', [CarritoController::class, 'ver'])->name('carrito.ver');
Route::get('/carrito/validar', [CarritoController::class, 'validar'])->name('carrito.validar');
Route::patch('/carrito/linea', [CarritoController::class, 'actualizarLinea'])
    ->middleware('throttle:60,1')
    ->name('carrito.linea.actualizar');
Route::delete('/carrito/linea', [CarritoController::class, 'eliminarLinea'])
    ->middleware('throttle:60,1')
    ->name('carrito.linea.eliminar');

Route::get('/', HomeController::class)->name('home');

Route::get('/nosotros', NosotrosController::class)->name('nosotros');

Route::middleware(['auth', 'role:supplier'])->prefix('portal-proveedor')->name('supplier.')->group(function () {
    Route::get('/', [SupplierPortalController::class, 'index'])->name('home');
    Route::get('/contacto', [SupplierPortalController::class, 'contact'])->name('contact');
});

Route::post('/webhooks/wompi', WompiWebhookController::class)->name('webhooks.wompi');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/pagar', [PaymentCheckoutController::class, 'initiate'])->name('payments.initiate');

    Route::prefix('pago')->name('payments.')->group(function () {
        Route::get('/procesar/{payment}', [PaymentCheckoutController::class, 'process'])->name('process');
        Route::get('/retorno/{payment}', [PaymentCheckoutController::class, 'return'])->name('return');
        Route::get('/estado/{payment}', [PaymentCheckoutController::class, 'status'])->name('status');
        Route::get('/exito/{payment}', [PaymentCheckoutController::class, 'success'])->name('success');
        Route::get('/pendiente/{payment}', [PaymentCheckoutController::class, 'pending'])->name('pending');
        Route::get('/fallido/{payment}', [PaymentCheckoutController::class, 'failed'])->name('failed');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('notificaciones')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Notifications\NotificationCenterController::class, 'index'])->name('index');
        Route::get('/feed', [\App\Http\Controllers\Notifications\NotificationCenterController::class, 'feed'])->name('feed');
        Route::get('/historial', [\App\Http\Controllers\Notifications\NotificationCenterController::class, 'history'])->name('history');
        Route::patch('/preferencias', [\App\Http\Controllers\Notifications\NotificationPreferenceController::class, 'update'])->name('preferences.update');
        Route::patch('/marcar-todas', [\App\Http\Controllers\Notifications\NotificationCenterController::class, 'markAllRead'])->name('mark-all-read');
        Route::patch('/{notification}/leida', [\App\Http\Controllers\Notifications\NotificationCenterController::class, 'markRead'])->name('read');
    });
});

Route::get('/seguimiento/{tracking_token}', [OrderTrackingController::class, 'showByToken'])
    ->name('orders.tracking.guest');
Route::get('/seguimiento/{tracking_token}/feed', [OrderTrackingController::class, 'feedByToken'])
    ->name('orders.tracking.guest-feed');

Route::middleware(['auth'])->group(function () {
    Route::get('/mis-pedidos', [CustomerOrderController::class, 'index'])->name('customer.orders.index');
    Route::get('/mis-pedidos/{order}/seguimiento', [OrderTrackingController::class, 'show'])
        ->name('orders.tracking.show');
    Route::get('/mis-pedidos/{order}/seguimiento/feed', [OrderTrackingController::class, 'feed'])
        ->name('orders.tracking.feed');
});

Route::middleware(['auth', 'role:admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::prefix('pagos')->name('payments.')->group(function () {
        Route::get('/', [PaymentAdminController::class, 'index'])->name('index');
        Route::get('/{payment}', [PaymentAdminController::class, 'show'])->name('show');
    });
});

Route::middleware(['auth', 'role_or_permission:admin|module.orders'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('/realtime/health', RealtimeHealthController::class)->name('realtime.health');

    Route::prefix('pedidos')->name('pedidos.')->group(function () {
        Route::get('/', [OrderOperationsController::class, 'index'])->name('index');
        Route::get('/mapa', [OrderOperationsController::class, 'map'])->name('map');
        Route::get('/feed', [OrderOperationsController::class, 'feed'])->name('feed');
        Route::get('/{order}/fragmento-tarjeta', [OrderOperationsController::class, 'cardFragment'])->name('card-fragment');
        Route::get('/mapa/datos', [OrderOperationsController::class, 'mapData'])->name('map-data');
        Route::get('/{order}/ticket', [OrderTicketController::class, 'show'])->name('ticket.show');
        Route::post('/{order}/ticket/impreso', [OrderTicketController::class, 'markPrinted'])->name('ticket.mark-printed');
        Route::post('/{order}/preparar', [OrderOperationsController::class, 'startPreparing'])->name('start-preparing');
        Route::post('/{order}/listo', [OrderOperationsController::class, 'markReady'])->name('mark-ready');
        Route::post('/{order}/asignar-domiciliario', [OrderOperationsController::class, 'assignCourier'])->name('assign-courier');
        Route::post('/{order}/cancelar', [OrderOperationsController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/reprogramar', [OrderOperationsController::class, 'redispatch'])->name('redispatch');
        Route::get('/{order}', [OrderOperationsController::class, 'show'])->name('show');
    });
});

Route::middleware(['auth', 'role:employee', 'courier'])->prefix('domiciliario')->name('courier.')->group(function () {
    Route::get('/pedidos', [CourierOrderController::class, 'index'])->name('orders.index');
    Route::post('/pedidos/{order}/aceptar', [CourierOrderController::class, 'accept'])->name('orders.accept');
    Route::post('/ubicacion', [CourierOrderController::class, 'updateLocation'])->name('location.update');
    Route::get('/pedidos/{order}', [CourierOrderController::class, 'show'])->name('orders.show');
    Route::post('/pedidos/{order}/recogido', [CourierOrderController::class, 'markPickedUp'])->name('orders.picked-up');
    Route::post('/pedidos/{order}/en-camino', [CourierOrderController::class, 'markInTransit'])->name('orders.in-transit');
    Route::post('/pedidos/{order}/entregado', [CourierOrderController::class, 'markDelivered'])->name('orders.delivered');
    Route::post('/pedidos/{order}/fallido', [CourierOrderController::class, 'markFailed'])->name('orders.failed');
});

Route::middleware(['auth', 'role:admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::post('/logo/empresa', [LogoController::class, 'update'])->name('logo.update');
    Route::redirect('/empresa', '/admin/configuracion/empresa?tab=nosotros')->name('empresa.edit');
    Route::put('/empresa', [CompanyProfileController::class, 'update'])->name('empresa.update');

    Route::get('/configuracion/empresa', [CompanySettingsController::class, 'index'])->name('configuracion.empresa');
    Route::put('/configuracion/empresa/general', [CompanySettingsController::class, 'updateGeneral'])->name('configuracion.empresa.general');
    Route::put('/configuracion/empresa/ubicacion', [CompanySettingsController::class, 'updateLocation'])->name('configuracion.empresa.ubicacion');
    Route::put('/configuracion/empresa/nosotros', [CompanySettingsController::class, 'updateAbout'])->name('configuracion.empresa.nosotros');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    })->name('home');
    Route::get('/users/clientes', [UserController::class, 'indexClients'])->name('users.clientes');
    Route::get('/users/empresa', [UserController::class, 'indexCompany'])->name('users.empresa');
    Route::get('/users/proveedores', [UserController::class, 'indexProveedores'])->name('users.proveedores');
    Route::resource('users', UserController::class)->only(['index', 'create', 'show', 'edit']);
    Route::resource('positions', PositionController::class)->except(['show']);
});

Route::middleware(['auth', 'role_or_permission:admin|module.catalog'])->group(function () {
    Route::redirect('/productos', '/catalogo/productos');
    Route::redirect('/productos/create', '/catalogo/productos/create');

    Route::prefix('catalogo')->name('catalog.')->group(function () {
        Route::get('/', [CatalogController::class, 'index'])->name('index');
        Route::get('productos/stock.json', [CatalogProductController::class, 'stockFeed'])->name('products.stock-feed');
        Route::resource('productos', CatalogProductController::class)
            ->except(['show'])
            ->parameters(['productos' => 'product'])
            ->names('products');
        Route::get('combos/nuevo', [OfferController::class, 'createBundle'])->name('offers.bundles.create');
        Route::get('combos', [OfferController::class, 'bundles'])->name('offers.bundles');
        Route::post('combos', [OfferController::class, 'store'])->name('offers.store');
        Route::get('combos/{offer}/edit', [OfferController::class, 'edit'])->name('offers.edit');
        Route::put('combos/{offer}', [OfferController::class, 'update'])->name('offers.update');
        Route::delete('combos/{offer}', [OfferController::class, 'destroy'])->name('offers.destroy');
        Route::get('escalas-volumen/nueva', [OfferController::class, 'createVolume'])->name('offers.volumes.create');
        Route::get('escalas-volumen', [OfferController::class, 'volumes'])->name('offers.volumes');
        Route::get('tipos-carne', [MeatTypeController::class, 'index'])->name('meat-types.index');
        Route::post('tipos-carne', [MeatTypeController::class, 'store'])->name('meat-types.store');
        Route::put('tipos-carne/{meatType}', [MeatTypeController::class, 'update'])->name('meat-types.update');
        Route::delete('tipos-carne/{meatType}', [MeatTypeController::class, 'destroy'])->name('meat-types.destroy');
        Route::get('cortes', [MeatCutController::class, 'index'])->name('meat-cuts.index');
        Route::post('cortes', [MeatCutController::class, 'store'])->name('meat-cuts.store');
        Route::put('cortes/{meatCut}', [MeatCutController::class, 'update'])->name('meat-cuts.update');
        Route::delete('cortes/{meatCut}', [MeatCutController::class, 'destroy'])->name('meat-cuts.destroy');
        Route::get('promociones', [PromotionOverviewController::class, 'index'])->name('promotions.index');
        Route::get('precios', [PricingController::class, 'index'])->name('pricing.index');
        Route::put('precios', [PricingController::class, 'update'])->name('pricing.update');
        Route::get('inventario', [InventoryController::class, 'index'])->name('inventory.index');
        Route::put('inventario', [InventoryController::class, 'update'])->name('inventory.update');
        Route::get('tipos-carne/{meatType}/cortes.json', [CatalogProductController::class, 'cutsByType'])->name('meat-cuts.by-type');
    });

    Route::resource('videos', VideoRecetaController::class)->except(['show']);
    Route::resource('recetas', RecetaController::class)->except(['show']);
});

require __DIR__.'/auth.php';
