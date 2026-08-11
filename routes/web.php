<?php

use Illuminate\Support\Facades\Route;

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

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    });
});