<?php
// app/Http/Kernel.php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use App\Http\Middleware\CheckAdmin; 
use App\Http\Middleware\Authenticate;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        // ... autres middlewares existants
        'auth' => \App\Http\Middleware\Authenticate::class,
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        // ...
    ];
}