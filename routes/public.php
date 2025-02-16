<?php

use App\Application\Router;
use App\Middleware\EnsureValidLogin;
use App\Middleware\EnsureInvalidLogin;

$router = Router::getInstance();

// Routes
$router->middleware(EnsureInvalidLogin::class, function () use ($router) {
    $router->get('/api', [App\Controllers\HomeController::class, 'index']);
    $router->post('/api/register', [App\Controllers\AuthController::class, 'register']);
    $router->post('/api/login', [App\Controllers\AuthController::class, 'login']);
});
$router->middleware(EnsureValidLogin::class, function () use ($router) {
    $router->get('/api/profile', [App\Controllers\ProfileController::class, 'index']);
    $router->post('/api/profile/update', [App\Controllers\ProfileController::class, 'update']);
});
