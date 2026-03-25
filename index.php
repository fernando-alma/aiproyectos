<?php

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/Router.php';

// Cargar variables para que las vistas puedan acceder a configs si es necesario
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
try {
    $dotenv->load();
} catch (Exception $e) {}

// IMPORTANTE: Dejar vacío porque el proyecto está en la raíz del subdominio
if ($_ENV === "PRODUCTION") {
    $basePath = '';
} 
$basePath = '/aiproyectos'; 

// Cargar las definiciones de rutas
$routes = require_once __DIR__ . '/routes.php';

$router = new \App\Frontend\Router($routes, $basePath);
$router->dispatch();