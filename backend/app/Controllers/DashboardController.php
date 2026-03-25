<?php

namespace App\Backend\Controllers;

use App\Backend\Models\DashboardModel;
use App\Backend\Models\ProjectModel;

class DashboardController
{
    private $dashboardModel;
    private $projectModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->projectModel = new ProjectModel();
    }

    public function create()
    {
        // Asegurarse de que solo se acepten POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $color = $_POST['color'] ?? 'blue';
        $created_by = $_POST['created_by'] ?? '';

        if (empty($title) || empty($description)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Título y descripción son requeridos'], 400);
        }

        $validColors = ['blue', 'green', 'purple', 'red', 'orange', 'yellow', 'pink', 'indigo'];
        if (!in_array($color, $validColors)) {
            $color = 'blue';
        }

        $slug = $this->dashboardModel->createDashboard($title, $description, $color, $created_by);

        if ($slug) {
            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Dashboard creado exitosamente',
                'slug' => $slug
            ], 201);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al crear el dashboard'], 500);
        }
    }
//este se usa en cuando se buscan todos
    public function getDashboards()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $dashboards = $this->dashboardModel->getAllDashboards();
        
        if ($dashboards !== null) {
            $this->sendJsonResponse(['success' => true, 'data' => $dashboards], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'No se pudieron obtener los dashboards.'], 500);
        }
    }
    //este se usa en el dashboard en si mismo
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

    public function update()
    {
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

    public function delete()
    {
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
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
