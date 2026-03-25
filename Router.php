<?php

namespace App\Frontend;

class Router
{
    private $routes;
    private $basePath;

    public function __construct(array $routes, string $basePath)
    {
        $this->routes = $routes;
        $this->basePath = $basePath;
    }

    public function dispatch()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        // Normaliza basePath
        $basePath = rtrim($this->basePath, '/');
        if ($basePath === '/') {
            $basePath = '';
        }

        // Recorta basePath solo si no está vacío
        if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
            $route = substr($requestUri, strlen($basePath));
        } else {
            $route = $requestUri;
        }

        $route = trim($route, '/');

        // Normaliza index
        if ($route === '' || $route === 'index' || $route === 'index.php') {
            $route = '';
        }

        // DEBUG: comentar cuando funcione
        error_log("URI={$requestUri} basePath={$basePath} route={$route}");

        if (array_key_exists($route, $this->routes)) {
            $viewPath = $this->routes[$route];

            $slug = $_GET['slug'] ?? null;
            $projectId = $_GET['id'] ?? null;
            $dashboardSlug = $_GET['dashboardSlug'] ?? null;

            require $viewPath;
            return;
        }

        http_response_code(404);
        require $this->routes['not-found'];
    }
}