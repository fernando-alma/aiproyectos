<?php

namespace App\Backend;

use App\Backend\Middleware\AuthMiddleware;

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

        // Ajustar la ruta quitando el basePath
        if (strpos($requestUri, $this->basePath) === 0) {
            $route = substr($requestUri, strlen($this->basePath));
        } else {
            $route = $requestUri;
        }

        $route = trim($route, '/');

        foreach ($this->routes as $path => $handler) {
            if ($path === $route && $handler['httpMethod'] === $requestMethod) {
                
                // --- VALIDACIÓN DE SEGURIDAD (Middleware) ---
                // Si la ruta está marcada como protegida, exigimos un token válido
                if (isset($handler['protected']) && $handler['protected'] === true) {
                    $user = AuthMiddleware::require(); 
                    // Si el token es inválido, AuthMiddleware::require() corta la ejecución con un 401
                }

                $controllerClass = $handler['controller'];
                $methodName = $handler['method'];

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $methodName)) {
                        // Ejecutamos el método del controlador
                        $controller->$methodName();
                        return;
                    } else {
                        $this->sendJsonResponse(['success' => false, 'message' => "Método '$methodName' no encontrado."], 404);
                    }
                } else {
                    $this->sendJsonResponse(['success' => false, 'message' => "Controlador '$controllerClass' no encontrado."], 404);
                }
            }
        }

        $this->sendJsonResponse(['success' => false, 'message' => 'Ruta no encontrada o método no permitido.'], 404);
    }

    private function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}