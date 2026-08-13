<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BillTemplateController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\BillingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/billing', [BillingController::class, 'index']);
Route::post('/billing/checkout', [BillingController::class, 'store']);

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('categories', CategoryController::class)
        ->except(['show', 'create', 'edit'])
        ->names('admin.categories');

    Route::resource('products', ProductController::class)
        ->except(['show', 'create', 'edit'])
        ->names('admin.products');  
        
    Route::get('/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');
    Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('admin.inventory.adjust');    
    Route::get('/bills', [SaleController::class, 'index'])->name('admin.bills.index');

    Route::get('/bill-templates', [BillTemplateController::class, 'index'])->name('admin.bill-templates.index');
    Route::post('/bill-templates/{billTemplate}/set-default', [BillTemplateController::class, 'setDefault'])->name('admin.bill-templates.setDefault');
    Route::delete('/bill-templates/{billTemplate}', [BillTemplateController::class, 'destroy'])->name('admin.bill-templates.destroy');

    Route::post('/bill-templates', [BillTemplateController::class, 'store'])->name('admin.bill-templates.store');
    Route::put('/bill-templates/{billTemplate}', [BillTemplateController::class, 'update'])->name('admin.bill-templates.update');
    Route::post('/bill-templates/{billTemplate}/duplicate', [BillTemplateController::class, 'duplicate'])->name('admin.bill-templates.duplicate');
});



