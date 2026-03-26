<?php

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/Router.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
try {
    $dotenv->load();
} catch (Exception $e) {
    // Si no hay .env, el sistema usará valores por defecto
}

/**
 * CONFIGURACIÓN DEL BASE PATH
 * Detectamos si estamos en subcarpeta (XAMPP) o raíz (Producción)
 */
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = str_replace('/index.php', '', $scriptName);

// Cargar las definiciones de rutas de las vistas
$routes = require_once __DIR__ . '/routes.php';

$router = new \App\Frontend\Router($routes, $basePath);
$router->dispatch();