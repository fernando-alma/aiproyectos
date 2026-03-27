<?php

namespace App\Backend\Controllers;

use App\Backend\Models\ProjectModel;
use App\Backend\Models\DashboardModel;
use App\Backend\Models\TaskModel;
use App\Backend\Models\MemberModel;
use App\Backend\Middleware\AuthMiddleware; // IMPORTANTE: Agregamos el Middleware

class ProjectController
{
    private $projectModel;
    private $dashboardModel;
    private $taskModel;
    private $memberModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
        $this->dashboardModel = new DashboardModel();
        $this->taskModel = new TaskModel();
        $this->memberModel = new MemberModel();
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        // 1. Obtenemos la identidad del Token
        $userPayload = AuthMiddleware::require();
        $userId = (int) $userPayload['sub'];

        // 2. Recolección de datos
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'in_progress';
        $dashboardSlug = $_POST['slug'] ?? '';
        $pitch = $_POST['pitch'] ?? '';
        $groupName = $_POST['group_name'] ?? '';
        $linkVideo = $_POST['link_video'] ?? '';
        $linkDeploy = $_POST['link_deploy'] ?? '';

        if (empty($title) || empty($description) || empty($dashboardSlug)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Faltan datos requeridos'], 400);
        }

        $validStatuses = ['in_progress', 'completed'];
        if (!in_array($status, $validStatuses)) {
            $status = 'in_progress';
        }

        // 3. Crear el proyecto pasando el $userId como Creador
        $projectId = $this->projectModel->createProject(
            $pitch, $dashboardSlug, $title, $description, $status, 
            $groupName, $linkVideo, $linkDeploy, $userId, null
        );

        if (!$projectId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al crear el proyecto o dashboard no encontrado.'], 500);
        }

        // 4. Manejo de Imagen
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/projects/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = "project_{$projectId}." . strtolower($extension);
            $imagePath = $uploadDir . $imageName;

            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $imageUrl = '/uploads/projects/' . $imageName;
                $this->projectModel->updateImage($projectId, $imageUrl);
            }
        }

        $this->sendJsonResponse(['success' => true, 'message' => 'Proyecto creado exitosamente', 'project_id' => $projectId], 201);
    }

    public function update() 
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        // 1. Identidad y validación de Dueño
        $userPayload = AuthMiddleware::require();
        $userId = (int) $userPayload['sub'];
        $userRole = $userPayload['role'] ?? 'user';

        $projectId = $_POST['id'] ?? null;
        if (!$projectId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
        }

        $project = $this->projectModel->getProjectById((int)$projectId);
        if (!$project) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Proyecto no encontrado'], 404);
        }

        // REGLA DE ORO: Solo el Creador o un Admin pueden editar
        if ($project['created_by_user_id'] != $userId && $userRole !== 'admin') {
            $this->sendJsonResponse(['success' => false, 'message' => 'No tienes permiso para editar este proyecto.'], 403);
        }

        // 2. Recolección de datos
        $title = $_POST['title'] ?? $project['title'];
        $description = $_POST['description'] ?? $project['description'];
        $status = $_POST['status'] ?? $project['status'];
        $pitch = $_POST['pitch'] ?? $project['pitch'];
        $groupName = $_POST['group_name'] ?? $project['group_name'];
        $linkVideo = $_POST['link_video'] ?? $project['link_video'];
        $linkDeploy = $_POST['link_deploy'] ?? $project['link_deploy'];

        // 3. Manejo de Imagen Segura
        $imageUrl = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/projects/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = "project_{$projectId}_" . time() . "." . strtolower($extension);
            $imagePath = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                $imageUrl = '/uploads/projects/' . $imageName;
            }
        }

        // 4. Actualización
        $result = $this->projectModel->updateProject(
            (int)$projectId, $title, $description, $status, $pitch, 
            $groupName, $linkVideo, $linkDeploy, $imageUrl
        );
        
        if ($result) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Proyecto actualizado'], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al actualizar'], 500);
        }
    }

    public function delete() 
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        // 1. Identidad y validación de Dueño
        $userPayload = AuthMiddleware::require();
        $userId = (int) $userPayload['sub'];
        $userRole = $userPayload['role'] ?? 'user';

        $projectId = $_POST['id'] ?? null;
        if (!$projectId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
        }

        $project = $this->projectModel->getProjectById((int)$projectId);
        if (!$project) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Proyecto no encontrado'], 404);
        }

        // REGLA DE ORO: Solo el Creador o un Admin pueden eliminar
        if ($project['created_by_user_id'] != $userId && $userRole !== 'admin') {
            $this->sendJsonResponse(['success' => false, 'message' => 'No tienes permiso para eliminar este proyecto.'], 403);
        }

        if ($this->projectModel->deleteProject((int)$projectId)) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Proyecto eliminado']);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al eliminar'], 500);
        }
    }

    public function getProjects()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $dashboardSlug = $_GET['slug'] ?? null;
        if (!$dashboardSlug) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Slug de dashboard requerido.'], 400);
        }

        $projects = $this->projectModel->getProjectsByDashboardSlug($dashboardSlug);
        $this->sendJsonResponse(['success' => true, 'data' => $projects], 200);
    }

    public function getProject()
    {
        $projectId = $_GET['id'] ?? null;
        if (!$projectId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID requerido.'], 400);
        }
        $project = $this->projectModel->getProjectById((int)$projectId);
        if ($project) {
            $this->sendJsonResponse(['success' => true, 'project' => $project], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Proyecto no encontrado.'], 404);
        }
    }

    // --- MÉTODOS AUXILIARES ---
    public function createProjectsMember()
    {
        // Esto lo refactorizaremos en el PASO 3 (Gestión de miembros)
        $this->sendJsonResponse(['success' => true, 'message' => 'Pendiente de refactorización'], 200);
    }
    public function getTasks() { $this->sendJsonResponse(['success'=>true, 'tasks'=>[]]); }
    public function getStats() { $this->sendJsonResponse(['success'=>true, 'stats'=>[]]); }
    public function getFiles() { $this->sendJsonResponse(['success'=>true, 'files'=>[]]); }
    public function getMembers() { 
        $id = $_GET['id'] ?? 0;
        $members = $this->memberModel->getMembersByProjectId((int)$id);
        $this->sendJsonResponse(['success'=>true, 'members'=>$members]); 
    }
    public function getActivity() { $this->sendJsonResponse(['success'=>true, 'activity'=>[]]); }
    public function getJoinRequests() { $this->sendJsonResponse(['success'=>true, 'requests'=>[]]); }
    public function sendJoinRequest() { $this->sendJsonResponse(['success'=>true]); }
    public function approveJoinRequest() { $this->sendJsonResponse(['success'=>true]); }
    public function rejectJoinRequest() { $this->sendJsonResponse(['success'=>true]); }
    public function getFollowedProjects() { $this->sendJsonResponse(['success'=>true, 'projects'=>[]]); }

    private function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}