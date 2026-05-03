<?php

use App\Http\Controllers\Admin\LogoController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\SupplierPortalController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CorteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\Publico\ProductoPublicoController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\VideoRecetaController;
use App\Models\Corte;
use App\Models\Promocion;
use App\Models\VideoReceta;
use Illuminate\Support\Facades\Route;




Route::get('/productos-publicos', [ProductoPublicoController::class, 'index'])->name('productos.publico.index');
Route::get('/productos-publicos/{producto}', [ProductoPublicoController::class, 'show'])->name('productos.publico.show');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::get('/carrito', [CarritoController::class, 'ver'])->name('carrito.ver');

Route::get('/', function () {
    $videos = VideoReceta::latest()->take(6)->get();
    $promociones = Promocion::latest()->take(6)->get();
    $cortes = Corte::latest()->take(8)->get(); // puedes ajustar el número

    return view('welcome', compact('videos', 'promociones', 'cortes'));
})->name('home');


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

Route::middleware(['auth', 'role:admin'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('/logo/edit', [LogoController::class, 'edit'])->name('logo.edit');
    Route::post('/logo/{tipo}/update', [LogoController::class, 'update'])->name('logo.update');
    Route::get('/pedidos', [OrderController::class, 'index'])->name('pedidos.index');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('productos', ProductoController::class);
    Route::resource('videos', VideoRecetaController::class);
    Route::resource('recetas', RecetaController::class);
    Route::resource('promociones', PromocionController::class);
    Route::resource('cortes', CorteController::class);
});

require __DIR__.'/auth.php';
