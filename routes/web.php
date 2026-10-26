<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/low-stock', [DashboardController::class, 'lowStock'])->name('low-stock.index');

    // All authenticated roles can view master data; mutations are admin-only below.
    Route::resource('categories', CategoryController::class)->only(['index']);
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->whereNumber('product')->name('products.show');
    Route::resource('suppliers', SupplierController::class)->only(['index']);

    // Staff can create inbound/outbound transactions and view only their own transactions.
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->whereNumber('purchase')->name('purchases.show');

    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->whereNumber('sale')->name('sales.show');

    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['index', 'show']);
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::resource('suppliers', SupplierController::class)->except(['index', 'show']);
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->whereNumber('purchase')->name('purchases.cancel');
        Route::patch('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->whereNumber('sale')->name('sales.cancel');
    });
});
