<?php
// backend/public/index.php

require_once __DIR__ . '/../../vendor/autoload.php';

// 1. Cargar variables de entorno
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
} catch (Exception $e) {
    // Si no hay .env, el sistema usará los fallbacks de Database.php
}

use App\Backend\Router;
use App\Backend\Middleware\AuthMiddleware;

// 2. INICIALIZAR SEGURIDAD
// Cargamos la clave secreta del .env en el Middleware antes de que el Router trabaje
AuthMiddleware::init();

// 3. CONFIGURACIÓN CORS DINÁMICA
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/aiproyectos';

if ($origin === $appUrl || str_contains($origin, 'localhost') || str_contains($origin, '127.0.0.1')) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 4. CONFIGURACIÓN DEL ROUTER DINÁMICA
$scriptName = $_SERVER['SCRIPT_NAME']; 
$basePath = str_replace('/index.php', '', $scriptName); 

// Cargar las definiciones de rutas
$routes = require_once __DIR__ . '/../app/Routes/api.php';

try {
    $router = new Router($routes, $basePath);
    $router->dispatch();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Router Error: ' . $e->getMessage()
    ]);
}