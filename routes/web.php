<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VideoRecetaController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\CorteController;
use App\Models\VideoReceta;
use App\Models\Promocion;
use App\Models\Corte;
use App\Http\Controllers\Admin\LogoController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\Publico\ProductoPublicoController;




Route::get('/productos-publicos', [ProductoPublicoController::class, 'index'])->name('productos.publico.index');
Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
Route::get('/carrito', [CarritoController::class, 'ver'])->name('carrito.ver');

Route::get('/', function () {
    $videos = VideoReceta::latest()->take(6)->get();
    $promociones = Promocion::latest()->take(6)->get();
    $cortes = Corte::latest()->take(8)->get(); // puedes ajustar el número

    return view('welcome', compact('videos', 'promociones', 'cortes'));
})->name('home');


Route::middleware(['auth'])->name('admin.')->prefix('admin')->group(function () {
    Route::get('/logo/edit', [LogoController::class, 'edit'])->name('logo.edit');
    Route::post('/logo/{tipo}/update', [LogoController::class, 'update'])->name('logo.update');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('productos', ProductoController::class);
    Route::resource('videos', VideoRecetaController::class);
    Route::resource('recetas', RecetaController::class);
    Route::resource('promociones', PromocionController::class);
    Route::resource('cortes', CorteController::class);

});

require __DIR__.'/auth.php';
