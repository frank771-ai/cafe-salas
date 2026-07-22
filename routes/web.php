<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Catálogo público: cualquier visitante puede explorar y consultar productos.
Route::get('/', HomeController::class)->name('home');
Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
Route::get('/productos/{product}', [ProductController::class, 'show'])->name('products.show');

// Autenticación: el middleware guest evita mostrar estos formularios a usuarios conectados.
Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->name('register.store');
    Route::get('/iniciar-sesion', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/iniciar-sesion', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
});

Route::post('/cerrar-sesion', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Carrito en sesión: puede prepararse antes de iniciar sesión.
Route::prefix('carrito')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/{product}', [CartController::class, 'store'])->name('store');
    Route::patch('/{product}', [CartController::class, 'update'])->name('update');
    Route::delete('/{product}', [CartController::class, 'destroy'])->name('destroy');
});

// Perfil, checkout y facturas requieren una identidad autenticada.
Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/comprar', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('/comprar', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
    Route::get('/pedidos/{order}/confirmacion', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');
    Route::get('/pedidos/{order}/factura', [InvoiceController::class, 'show'])->name('orders.invoice');
    Route::get('/pedidos/{order}/factura.pdf', [InvoiceController::class, 'pdf'])->name('orders.invoice.pdf');
});

// Panel interno: exige autenticación y rol administrador en toda la agrupación.
Route::prefix('administracion')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::patch('/pedidos/{order}/estado', [AdminController::class, 'updateStatus'])->name('orders.status');
    Route::get('/reportes', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reportes/mensual.pdf', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reportes/cliente.pdf', [ReportController::class, 'customer'])->name('reports.customer');
});
