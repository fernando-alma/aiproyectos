<?php
// backend/public/index.php

require_once __DIR__ . '/../../vendor/autoload.php';

// 1. Cargar variables de entorno desde la raíz del proyecto
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
} catch (Exception $e) {
    // Fallback silencioso si no hay .env
}

use App\Backend\Router;

// 2. CONFIGURACIÓN CORS DINÁMICA
// En lugar de una lista fija, permitimos el origen definido en el .env (APP_URL)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/aiproyectos';

// Si el origen de la petición coincide con nuestra APP_URL o es local, lo permitimos
if ($origin === $appUrl || str_contains($origin, 'localhost') || str_contains($origin, '127.0.0.1')) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Credentials: true");
} else {
    // Fallback de seguridad para desarrollo
    header("Access-Control-Allow-Origin: *");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=utf-8");

// Manejo de peticiones pre-vuelo (Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 3. CONFIGURACIÓN DEL ROUTER DINÁMICA
/**
 * Detectamos automáticamente el Base Path.
 * Si estás en localhost/aiproyectos/backend/public/, el script lo detectará
 * para que las rutas de api.php (como 'dashboards') funcionen sin prefijos.
 */
$scriptName = $_SERVER['SCRIPT_NAME']; // ej: /aiproyectos/backend/public/index.php
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