<?php

use App\Http\Controllers\Admin\CintaController;
use App\Http\Controllers\Admin\CompanyProfileController;
use App\Http\Controllers\Admin\LogoController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\StoreBannerController;
use App\Http\Controllers\Admin\StoreHighlightController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Catalog\CatalogController;
use App\Http\Controllers\Catalog\InventoryController;
use App\Http\Controllers\Catalog\MeatCutController;
use App\Http\Controllers\Catalog\MeatTypeController;
use App\Http\Controllers\Catalog\PricingController;
use App\Http\Controllers\Catalog\ProductController as CatalogProductController;
use App\Http\Controllers\Catalog\PromotionOverviewController;
use App\Http\Controllers\SupplierPortalController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Publico\ProductPublicController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\NosotrosController;
use App\Http\Controllers\VideoRecetaController;
use App\Models\CintaSlide;
use App\Models\StoreBanner;
use App\Models\StoreHighlight;
use App\Models\VideoReceta;
use Illuminate\Support\Facades\Route;

Route::get('/productos-publicos', [ProductPublicController::class, 'index'])->name('products.public.index');
Route::get('/productos-publicos/{product}', [ProductPublicController::class, 'show'])->name('products.public.show');

Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])
    ->middleware('throttle:60,1')
    ->name('carrito.agregar');
Route::get('/carrito', [CarritoController::class, 'ver'])->name('carrito.ver');

Route::get('/', function () {
    $cintaSlides = CintaSlide::query()->orderBy('sort_order')->orderBy('id')->get();
    $videos = VideoReceta::latest()->take(6)->get();
    $banners = StoreBanner::query()->where('is_active', true)->orderBy('sort_order')->take(6)->get();
    $highlights = StoreHighlight::query()->where('is_active', true)->orderBy('sort_order')->take(8)->get();

    return view('welcome', compact('cintaSlides', 'videos', 'banners', 'highlights'));
})->name('home');

Route::get('/nosotros', NosotrosController::class)->name('nosotros');

Route::middleware(['auth', 'role:supplier'])->prefix('portal-proveedor')->name('supplier.')->group(function () {
    Route::get('/', [SupplierPortalController::class, 'index'])->name('home');
    Route::get('/contacto', [SupplierPortalController::class, 'contact'])->name('contact');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/carrito/finalizar', [CarritoController::class, 'finalizarCompra'])->name('carrito.finalizar');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role_or_permission:admin|module.orders'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('/pedidos', [OrderController::class, 'index'])->name('pedidos.index');
});

Route::middleware(['auth', 'role_or_permission:admin|module.settings'])->name('admin.')->prefix('admin')->group(function () {
    Route::post('/logo/empresa', [LogoController::class, 'update'])->name('logo.update');
    Route::get('/empresa', [CompanyProfileController::class, 'edit'])->name('empresa.edit');
    Route::put('/empresa', [CompanyProfileController::class, 'update'])->name('empresa.update');
    Route::get('/cinta', [CintaController::class, 'edit'])->name('cinta.edit');
    Route::post('/cinta', [CintaController::class, 'store'])->name('cinta.store');
    Route::put('/cinta/{cintaSlide}', [CintaController::class, 'update'])->name('cinta.update');
    Route::delete('/cinta/{cintaSlide}', [CintaController::class, 'destroy'])->name('cinta.destroy');
});

Route::middleware(['auth', 'role:admin'])->name('admin.')->prefix('admin')->group(function () {
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
        Route::resource('productos', CatalogProductController::class)
            ->except(['show'])
            ->parameters(['productos' => 'product'])
            ->names('products');
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

    Route::prefix('admin/contenido-tienda')->name('admin.store.')->group(function () {
        Route::resource('banners', StoreBannerController::class)->except(['show']);
        Route::resource('destacados', StoreHighlightController::class)
            ->except(['show'])
            ->parameters(['destacados' => 'highlight'])
            ->names('highlights');
    });

    Route::redirect('/promociones', '/admin/contenido-tienda/banners');
    Route::redirect('/cortes', '/admin/contenido-tienda/destacados');

    Route::resource('videos', VideoRecetaController::class)->except(['show']);
    Route::resource('recetas', RecetaController::class)->except(['show']);
});

require __DIR__.'/auth.php';
