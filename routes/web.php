<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BillTemplateController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\BillingAuthController;
use App\Http\Controllers\BillingController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/billing/login', [BillingAuthController::class, 'showLogin']);
Route::post('/billing/login', [BillingAuthController::class, 'login']);
Route::post('/billing/logout', [BillingAuthController::class, 'logout']);

Route::middleware('auth')->group(function () {
    Route::get('/billing', [BillingController::class, 'index']);
    Route::post('/billing/checkout', [BillingController::class, 'store']);
    Route::post('/billing/lookup-barcode', [BillingController::class, 'lookupBarcode']);
    Route::get('/billing/search-products', [BillingController::class, 'searchProducts']);
    Route::get('/billing/held-bills', [BillingController::class, 'heldBills']);
    Route::post('/billing/hold', [BillingController::class, 'holdBill']);
    Route::post('/billing/held-bills/{heldBill}/restore', [BillingController::class, 'restoreHeldBill']);
    Route::delete('/billing/held-bills/{heldBill}', [BillingController::class, 'destroyHeldBill']);
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



// Admin login routes (NOT protected - this is how you log in)
Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
});

// Protected admin routes (must be logged in to access)

Route::prefix('admin')->middleware(['auth', 'owner'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('categories', CategoryController::class)
        ->except(['show', 'create', 'edit'])
        ->names('admin.categories');

    Route::resource('products', ProductController::class)
        ->except(['show', 'create', 'edit'])
        ->names('admin.products');  

    Route::post('/products/{product}/generate-barcode', [ProductController::class, 'generateBarcode'])->name('admin.products.generateBarcode');    
    Route::get('/products/{product}/label', [ProductController::class, 'printLabel'])->name('admin.products.label');
    Route::get('/settings/test-print', [SettingController::class, 'testPrint'])->name('admin.settings.testPrint');
    Route::get('/settings/test-label', [SettingController::class, 'testLabel'])->name('admin.settings.testLabel');

    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus'])->name('admin.users.updateStatus');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');    
        
    Route::get('/inventory', [InventoryController::class, 'index'])->name('admin.inventory.index');
    Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('admin.inventory.adjust');    
    Route::get('/bills', [SaleController::class, 'index'])->name('admin.bills.index');

    Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('admin.reports.export.csv');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('admin.reports.export.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('admin.reports.export.pdf');

    Route::get('/bill-templates', [BillTemplateController::class, 'index'])->name('admin.bill-templates.index');
    Route::post('/bill-templates/{billTemplate}/set-default', [BillTemplateController::class, 'setDefault'])->name('admin.bill-templates.setDefault');
    Route::delete('/bill-templates/{billTemplate}', [BillTemplateController::class, 'destroy'])->name('admin.bill-templates.destroy');

    Route::post('/bill-templates', [BillTemplateController::class, 'store'])->name('admin.bill-templates.store');
    Route::put('/bill-templates/{billTemplate}', [BillTemplateController::class, 'update'])->name('admin.bill-templates.update');
    Route::post('/bill-templates/{billTemplate}/duplicate', [BillTemplateController::class, 'duplicate'])->name('admin.bill-templates.duplicate');

    Route::get('/customers', [CustomerController::class, 'index'])->name('admin.customers.index');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('admin.customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('admin.customers.destroy');

    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('admin.settings.update');
    Route::post('/settings/backup-now', [SettingController::class, 'backupNow'])->name('admin.settings.backupNow');

    Route::get('/bill-templates/{billTemplate}/designer', [BillTemplateController::class, 'designer'])->name('admin.bill-templates.designer');
Route::post('/bill-templates/{billTemplate}/save-layout', [BillTemplateController::class, 'saveLayout'])->name('admin.bill-templates.saveLayout');
});