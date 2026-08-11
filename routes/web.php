<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ProductController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/billing', function () {
    return view('billing.index');
});

Route::get('/inventory', function () {
    return view('inventory.index');
});

Route::get('/bill-history', function () {
    return view('bill-history.index');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
});

Route::get('/settings', function () {
    return view('settings.index');
});

// Admin login routes (NOT protected - this is how you log in)
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

// Protected admin routes (must be logged in to access)

Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    });

    Route::resource('categories', CategoryController::class)
        ->except(['show', 'create', 'edit'])
        ->names('admin.categories');

    Route::resource('products', ProductController::class)
        ->except(['show', 'create', 'edit'])
        ->names('admin.products');  
        
    Route::get('/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');
    Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('admin.inventory.adjust');    
});



