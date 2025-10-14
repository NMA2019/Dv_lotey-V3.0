<?php
// app/Http/Kernel.php

protected $routeMiddleware = [
    // ... autres middlewares existants
    'auth' => \App\Http\Middleware\Authenticate::class,
    'admin' => \App\Http\Middleware\CheckAdmin::class,
    // ...
];