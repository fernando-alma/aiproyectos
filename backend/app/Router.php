<?php

namespace App\Backend;

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
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        if (strpos($requestUri, $this->basePath) === 0) {
            $route = substr($requestUri, strlen($this->basePath));
        } else {
            $route = $requestUri;
        }

        $route = trim($route, '/');

        foreach ($this->routes as $path => $handler) {
            if ($path === $route && $handler['httpMethod'] === $requestMethod) {
                $controllerClass = $handler['controller'];
                $methodName = $handler['method'];

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $methodName)) {
                        $controller->$methodName();
                        return;
                    } else {
                        $this->sendJsonResponse(['success' => false, 'message' => 'Método no encontrado en el controlador.'], 404);
                    }
                } else {
                    $this->sendJsonResponse(['success' => false, 'message' => 'Controlador no encontrado.'], 404);
                }
            }
        }

        $this->sendJsonResponse(['success' => false, 'message' => 'Ruta no encontrada o método no permitido.'], 404);
    }

    private function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
