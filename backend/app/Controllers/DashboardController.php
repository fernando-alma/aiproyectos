<?php

namespace App\Backend\Controllers;

use App\Backend\Models\DashboardModel;
use App\Backend\Models\ProjectModel;
use App\Backend\Middleware\AuthMiddleware;

class DashboardController
{
    private $dashboardModel;
    private $projectModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->projectModel = new ProjectModel();
    }

    /**
     * PRIVADO: Solo el Súper Admin puede crear eventos
     */
    public function create()
    {
        // EL ESCUDO: Bloquea si no hay token o si el rol no es 'admin'
        $user = AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $color = $_POST['color'] ?? 'blue';

        // Ya NO pedimos el slug aquí, porque el Modelo lo generará automáticamente.
        if (empty($title) || empty($description)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Título y descripción son requeridos'], 400);
        }

        $validColors = ['blue', 'green', 'purple', 'red', 'orange', 'yellow', 'pink', 'indigo'];
        if (!in_array($color, $validColors)) {
            $color = 'blue';
        }

        // Extraemos la identidad del creador desde el Token
        $createdByName = $user['name'];
        $createdByUserId = (int) $user['sub'];

        // Llamamos al método correcto del modelo
        $slug = $this->dashboardModel->createDashboard($title, $description, $color, $createdByName, $createdByUserId);

        if ($slug) {
            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Dashboard creado exitosamente',
                'slug' => $slug // Devolvemos el slug automático generado
            ], 201);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al crear el dashboard'], 500);
        }
    }

    /**
     * PÚBLICO: Cualquier usuario puede ver la lista de eventos
     */
    public function getDashboards()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        // Llamamos al método correcto del modelo
        $dashboards = $this->dashboardModel->getAllDashboards();
        
        if ($dashboards !== null) {
            $this->sendJsonResponse(['success' => true, 'data' => $dashboards], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'No se pudieron obtener los dashboards.'], 500);
        }
    }

    /**
     * PÚBLICO: Cualquier usuario puede ver los proyectos de un evento
     */
    public function getDashboard()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $slug = $_GET['slug'] ?? null;
        if (!$slug) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Slug de dashboard requerido.'], 400);
        }

        $dashboard = $this->dashboardModel->findBySlug($slug);
        
        if ($dashboard) {
            // Obtener proyectos asociados al dashboard
            $dashboard['projects'] = $this->projectModel->getProjectsByDashboardSlug($slug);
            $this->sendJsonResponse(['success' => true, 'dashboard' => $dashboard], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Dashboard no encontrado.'], 404);
        }
    }

    /**
     * PRIVADO: Solo el Súper Admin puede editar eventos
     */
    public function update()
    {
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $slug = $_POST['slug'] ?? null;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $color = $_POST['color'] ?? 'blue';

        if (empty($slug) || empty($title) || empty($description)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Slug, título y descripción son requeridos'], 400);
        }

        $validColors = ['blue', 'green', 'purple', 'red', 'orange', 'yellow', 'pink', 'indigo'];
        if (!in_array($color, $validColors)) {
            $color = 'blue';
        }

        if ($this->dashboardModel->updateDashboard($slug, $title, $description, $color)) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Dashboard actualizado exitosamente'], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al actualizar el dashboard o no se encontró.'], 500);
        }
    }

    /**
     * PRIVADO: Solo el Súper Admin puede eliminar eventos
     */
    public function delete()
    {
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $slug = $_POST['slug'] ?? null;

        if (empty($slug)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Slug de dashboard requerido para eliminar.'], 400);
        }

        if ($this->dashboardModel->deleteDashboard($slug)) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Dashboard eliminado exitosamente'], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al eliminar el dashboard o no se encontró.'], 500);
        }
    }

    private function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}