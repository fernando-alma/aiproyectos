<?php

require_once __DIR__ . '/../../vendor/autoload.php';

// Cargar variables de entorno desde la raíz del proyecto (dos niveles arriba)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
try {
    $dotenv->load();
} catch (Exception $e) {
    // Si no encuentra el .env, confiamos en variables del servidor o defaults
}

use App\Backend\Router;

// --- CONFIGURACIÓN CORS ---
$allowedOrigins = [
    'https://aiday-utn-sanrafael-2025.alphadocere.cl', // Producción
    'http://localhost:5500', // Desarrollo
    'http://127.0.0.1:5500',
    'http://localhost'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
    header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- CONFIGURACIÓN DEL ROUTER ---

// IMPORTANTE: Definir el Base Path correcto.
// Si tu dominio apunta a la raíz del proyecto, la API está en /backend/public
$basePath = '/backend/public';

// Cargar las definiciones de rutas
$routes = require_once __DIR__ . '/../app/Routes/api.php';

$router = new Router($routes, $basePath);
$router->dispatch();