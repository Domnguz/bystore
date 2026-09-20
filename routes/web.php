<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Admin\ProductoController as AdminProductoController;
use App\Http\Controllers\CategoriaController;
use App\Models\Categoria;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\BannerController;




Route::get('/carrito', [CarritoController::class, 'index']);

Route::post('/carrito/agregar/{id}', [CarritoController::class, 'agregar']);

Route::post('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar']);

Route::get('/checkout', [CheckoutController::class, 'index'])
    ->middleware('auth');

Route::post('/checkout', [CheckoutController::class, 'procesar'])
    ->middleware('auth');

Route::get('/carrito/contenido', function () {
    return view('partials.carrito-contenido');
});

Route::get('/carrito/vista', function () {
    return view('partials.carrito-contenido');
});

Route::middleware('auth')->prefix('admin')->group(function () {
 
// Administración de banners.
    Route::resource('banners', BannerController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->names('admin.banners');

    Route::resource('productos', AdminProductoController::class);

    Route::resource('categorias', CategoriaController::class);

    Route::resource('usuarios', UserController::class);
   
    Route::post(

        'pedidos/{id}/rechazar',
        [PedidoController::class, 'rechazar']
    )->name('pedidos.rechazar');
    Route::post(
        'pedidos/{id}/estado',
        [PedidoController::class, 'cambiarEstado']
    )->name('pedidos.estado');

    Route::get(
        'pedidos',
        [PedidoController::class, 'index']
    )->name('pedidos.index');

    Route::get(
        'pedidos/{id}',
        [PedidoController::class, 'show']
    )->name('pedidos.show');

    Route::post(
        'pedidos/{id}/aprobar',
        [PedidoController::class, 'aprobar']
    )->name('pedidos.aprobar');

    Route::post(
        'pedidos/{id}/estado',
        [PedidoController::class, 'cambiarEstado']
    )->name('pedidos.estado');
});


use App\Models\Producto;

Route::get('/', function () {

    $categorias = Categoria::all();

    $productos = Producto::with('categoria')->latest('created_at')
                    ->take(4)
                    ->get();

    return view(
        'inicio',
        compact('categorias', 'productos')
    );
});




Route::get('/productos', [ProductoController::class, 'index']);
// Detalle público de un producto.
// Ejemplo: /productos/19
// "tienda.productos.show" permite generar este enlace desde Blade.
Route::get('/productos/{id}', [ProductoController::class, 'show'])
    ->whereNumber('id')
    ->name('tienda.productos.show');



    

Route::get('/productos/{id}', [ProductoController::class, 'show'])
    ->whereNumber('id')
    ->name('tienda.productos.show');


Route::get('/categoria/{id}', [ProductoController::class, 'categoria']);



Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('profile');

});

Route::get('/login', function () {
    return view('login');
})->name('login');




    Route::get('/admin', [DashboardController::class, 'index'])
        ->middleware('auth');

    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout']);
