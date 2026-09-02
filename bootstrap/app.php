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
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        // залогиненного со страниц входа/регистрации — на его дашборд
        $middleware->redirectUsersTo(function () {
            $user = auth()->user();
            if ($user && $user->isTeacher()) return route('teacher.dashboard');
            if ($user && $user->isStudent()) return route('student.dashboard');
            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
