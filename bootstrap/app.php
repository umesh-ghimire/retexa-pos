<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    
    ->withMiddleware(function (Middleware $middleware): void {
        

        $middleware->web(prepend: [
            \App\Http\Middleware\ApplySystemPreferences::class,
        ]);
    
        $middleware->redirectGuestsTo(function ($request) {
            return $request->is('billing*') ? '/billing/login' : '/admin/login';
        });
        
        $middleware->alias(['owner' => \App\Http\Middleware\EnsureUserIsOwner::class]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
