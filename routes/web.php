<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\ComprobanteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PedidoController;
use App\Http\Controllers\Admin\ProductoController as AdminProductoController;
use App\Http\Controllers\Admin\TiendaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PedidoClienteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReclamacionController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\EnsureAccountDni;
use App\Http\Middleware\EnsureAdmin;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $categorias = Categoria::all();
    $productos = Producto::with('categoria')->latest('created_at')->take(4)->get();

    return view('inicio', compact('categorias', 'productos'));
})->name('inicio');
Route::get('/productos', [ProductoController::class, 'index'])->name('catalogo');
Route::get('/productos/sugerencias', [ProductoController::class, 'sugerencias'])->middleware('throttle:60,1')->name('catalogo.sugerencias');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::view('/contacto', 'contacto')->name('contacto');
Route::view('/privacidad', 'privacidad')->name('privacidad');
Route::get('/reclamaciones', [ReclamacionController::class, 'create'])->name('reclamaciones.create');
Route::post('/reclamaciones', [ReclamacionController::class, 'store'])->middleware('throttle:5,10')->name('reclamaciones.store');
Route::get('/reclamaciones/{token}', [ReclamacionController::class, 'show'])->where('token', '[A-Za-z0-9]{64}')->name('reclamaciones.show');
Route::get('/productos/{id}', [ProductoController::class, 'show'])->whereNumber('id')->name('tienda.productos.show');
Route::get('/categoria/{id}', [ProductoController::class, 'categoria'])->whereNumber('id');

Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito');
Route::post('/carrito/agregar/{id}', [CarritoController::class, 'agregar'])->whereNumber('id')->middleware('throttle:60,1');
Route::post('/carrito/actualizar/{id}', [CarritoController::class, 'actualizar'])->whereNumber('id');
Route::post('/carrito/eliminar/{id}', [CarritoController::class, 'eliminar'])->whereNumber('id');
Route::get('/carrito/contenido', [CarritoController::class, 'contenido']);
Route::get('/carrito/vista', [CarritoController::class, 'contenido']);

Route::middleware('guest')->group(function () {
    Route::view('/login', 'login')->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
    Route::get('/registro', [LoginController::class, 'registrationForm'])->middleware('throttle:30,1')->block(10, 5)->name('register');
    Route::post('/registro', [LoginController::class, 'register'])->middleware('throttle:registration')->block(10, 5);
    Route::view('/olvide-contrasena', 'auth.forgot-password')->name('password.request');
    Route::post('/olvide-contrasena', [LoginController::class, 'sendResetLink'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [LoginController::class, 'resetForm'])->name('password.reset');
    Route::post('/restablecer-contrasena', [LoginController::class, 'resetPassword'])->middleware('throttle:6,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/verificar-correo', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('/verificar-correo', [EmailVerificationController::class, 'verify'])
        ->middleware('throttle:verification-code')->name('verification.verify');
    Route::post('/verificar-correo/reenviar', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:verification')->name('verification.send');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->middleware('throttle:account-profile')->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/checkout', [CheckoutController::class, 'index'])->middleware(['verified', EnsureAccountDni::class])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'procesar'])->middleware(['verified', EnsureAccountDni::class, 'throttle:12,1']);
    Route::get('/mis-pedidos', [PedidoClienteController::class, 'index'])->name('mis-pedidos.index');
    Route::get('/mis-pedidos/{pedido}', [PedidoClienteController::class, 'show'])->name('mis-pedidos.show');
    Route::get('/mis-pedidos/{pedido}/estado', [PedidoClienteController::class, 'estado'])->name('mis-pedidos.estado');
    Route::post('/mis-pedidos/{pedido}/cancelar', [PedidoClienteController::class, 'cancelar'])->name('mis-pedidos.cancelar');
    Route::post('/mis-pedidos/{pedido}/pago', [PedidoClienteController::class, 'enviarComprobante'])->middleware('throttle:10,1')->name('mis-pedidos.pago');
    Route::get('/mis-pedidos/{pedido}/boleta.pdf', [PedidoClienteController::class, 'boletaPdf'])->whereNumber('pedido')->name('mis-pedidos.boleta-pdf');
    Route::post('/mis-pedidos/{pedido}/productos/{producto}/calificar', [CalificacionController::class, 'store'])
        ->whereNumber('pedido')->whereNumber('producto')->middleware('throttle:10,1')->name('mis-pedidos.productos.calificar');
    Route::get('/mis-pedidos/{pedido}/comprobante', [PedidoClienteController::class, 'comprobante'])->name('mis-pedidos.comprobante');
    Route::get('/mis-pedidos/{pedido}/comprobante-venta', [PedidoClienteController::class, 'comprobanteVenta'])->name('mis-pedidos.comprobante-venta');
});

Route::middleware(['auth', EnsureAdmin::class])->prefix('admin')->group(function () {
    Route::get('tienda', [TiendaController::class, 'index'])->name('admin.tienda.index');
    Route::get('reclamaciones', [App\Http\Controllers\Admin\ReclamacionController::class, 'index'])->name('admin.reclamaciones.index');
    Route::post('reclamaciones/{id}/respuesta', [App\Http\Controllers\Admin\ReclamacionController::class, 'responder'])->name('admin.reclamaciones.responder');
    Route::put('tienda', [TiendaController::class, 'update'])->name('admin.tienda.update');
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::resource('banners', BannerController::class)->only(['index', 'store', 'update', 'destroy'])->names('admin.banners');
    Route::post('productos/{id}/reponer-stock', [AdminProductoController::class, 'reponerStock'])->whereNumber('id')->name('productos.reponer-stock');
    Route::resource('productos', AdminProductoController::class);
    Route::resource('categorias', CategoriaController::class)->except('show');
    Route::resource('usuarios', UserController::class)->except('show');
    Route::get('pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('pedidos/actualizaciones', [PedidoController::class, 'actualizaciones'])->name('pedidos.actualizaciones');
    Route::get('pedidos/{id}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::post('pedidos/{id}/aprobar', [PedidoController::class, 'aprobar'])->name('pedidos.aprobar');
    Route::post('pedidos/{id}/rechazar', [PedidoController::class, 'rechazar'])->name('pedidos.rechazar');
    Route::post('pedidos/{id}/estado', [PedidoController::class, 'cambiarEstado'])->name('pedidos.estado');
    Route::post('pedidos/{id}/cotizar', [PedidoController::class, 'cotizar'])->name('pedidos.cotizar');
    Route::post('pedidos/{id}/comprobante-venta', [ComprobanteController::class, 'store'])->name('pedidos.comprobante-venta');
});
