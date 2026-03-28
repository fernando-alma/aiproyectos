<?php

namespace App\Backend\Controllers;

use App\Backend\Models\UserModel;
use App\Backend\Models\DashboardModel;
use App\Backend\Models\ProjectModel;
use App\Backend\Middleware\AuthMiddleware;

class AdminController
{
    private $userModel;
    private $dashboardModel;
    private $projectModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->dashboardModel = new DashboardModel();
        $this->projectModel = new ProjectModel();
    }

    /**
     * Obtener el listado de todos los usuarios registrados en la plataforma.
     */
    public function getUsers()
    {
        // ESCUDO DE ACERO: Si no eres admin, ni siquiera ves quién está registrado.
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $users = $this->userModel->findAll();
        
        $this->sendJsonResponse(['success' => true, 'data' => $users], 200);
    }

    /**
     * Promover o degradar a un usuario (Cambiar Role).
     */
    public function changeRole()
    {
        // ESCUDO DE ACERO: Solo un Dios puede crear a otro Dios.
        $currentUser = AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;

        $targetUserId = $data['user_id'] ?? null;
        $newRole = $data['role'] ?? null;

        if (!$targetUserId || !$newRole) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Se requiere el ID del usuario y el nuevo rol.'], 400);
        }

        // Validación extra: Un admin no debería poder degradarse a sí mismo por error
        if ((int)$targetUserId === (int)$currentUser['sub']) {
            $this->sendJsonResponse(['success' => false, 'message' => 'No puedes cambiar tu propio nivel de privilegios.'], 403);
        }

        if ($this->userModel->updateRole((int)$targetUserId, $newRole)) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Privilegios actualizados correctamente.'], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al cambiar los privilegios. Verifica que el rol sea válido.'], 500);
        }
    }

    /**
     * Obtener estadísticas globales para pintar gráficas en el Panel.
     */
    public function getStats()
    {
        AuthMiddleware::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $allUsers = $this->userModel->findAll();
        $allDashboards = $this->dashboardModel->getAllDashboards();
        
        // Sumamos los proyectos de todos los dashboards
        $totalProjects = array_reduce($allDashboards, function($carry, $dash) {
            return $carry + (int)$dash['total_projects'];
        }, 0);

        // Contamos cuántos administradores hay
        $totalAdmins = count(array_filter($allUsers, function($user) {
            return $user['role'] === 'admin';
        }));

        $stats = [
            'total_users' => count($allUsers),
            'total_admins' => $totalAdmins,
            'total_dashboards' => count($allDashboards),
            'total_projects' => $totalProjects
        ];

        $this->sendJsonResponse(['success' => true, 'data' => $stats], 200);
    }

    private function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}