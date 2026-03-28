<?php

namespace App\Backend\Controllers;

use App\Backend\Models\ProjectModel;
use App\Backend\Models\DashboardModel;
use App\Backend\Models\TaskModel;
use App\Backend\Models\MemberModel;
use App\Backend\Models\JoinRequestModel; 
use App\Backend\Middleware\AuthMiddleware;

class ProjectController
{
    private $projectModel;
    private $dashboardModel;
    private $taskModel;
    private $memberModel;
    private $joinRequestModel;

    public function __construct()
    {
        $this->projectModel = new ProjectModel();
        $this->dashboardModel = new DashboardModel();
        $this->taskModel = new TaskModel();
        $this->memberModel = new MemberModel();
        $this->joinRequestModel = new JoinRequestModel(); // Inicializamos
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $userPayload = AuthMiddleware::require();
        $userId = (int) $userPayload['sub'];

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

        $projectId = $this->projectModel->createProject(
            $pitch, $dashboardSlug, $title, $description, $status, 
            $groupName, $linkVideo, $linkDeploy, $userId, null
        );

        if (!$projectId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al crear el proyecto o dashboard no encontrado.'], 500);
        }

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

        if ($project['created_by_user_id'] != $userId && $userRole !== 'admin') {
            $this->sendJsonResponse(['success' => false, 'message' => 'No tienes permiso para editar este proyecto.'], 403);
        }

        $title = $_POST['title'] ?? $project['title'];
        $description = $_POST['description'] ?? $project['description'];
        $status = $_POST['status'] ?? $project['status'];
        $pitch = $_POST['pitch'] ?? $project['pitch'];
        $groupName = $_POST['group_name'] ?? $project['group_name'];
        $linkVideo = $_POST['link_video'] ?? $project['link_video'];
        $linkDeploy = $_POST['link_deploy'] ?? $project['link_deploy'];

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

    // =========================================================================
    // PASO 3: GESTIÓN DE SOLICITUDES Y MIEMBROS
    // =========================================================================

    public function sendJoinRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->sendJsonResponse(['success' => false], 405);
        
        $userPayload = AuthMiddleware::require();
        $userId = (int) $userPayload['sub'];
        $userName = $userPayload['name'];
        $userEmail = $userPayload['email'];

        // Recibir los datos en JSON o POST normal
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;
        $projectId = $data['project_id'] ?? null;

        if (!$projectId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID de proyecto requerido.'], 400);
        }

        $result = $this->joinRequestModel->createRequest((int)$projectId, $userId, $userName, $userEmail);
        
        if ($result['success']) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Solicitud enviada correctamente.'], 201);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => $result['message']], 400);
        }
    }

    public function getJoinRequests()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') $this->sendJsonResponse(['success' => false], 405);

        $userPayload = AuthMiddleware::require();
        $userId = (int) $userPayload['sub'];
        $userRole = $userPayload['role'] ?? 'user';

        $projectId = $_GET['project_id'] ?? null;
        if (!$projectId) $this->sendJsonResponse(['success' => false, 'message' => 'ID de proyecto requerido.'], 400);

        // Validación de Dueño
        $project = $this->projectModel->getProjectById((int)$projectId);
        if (!$project || ($project['created_by_user_id'] != $userId && $userRole !== 'admin')) {
            $this->sendJsonResponse(['success' => false, 'message' => 'No tienes permiso para ver estas solicitudes.'], 403);
        }

        $requests = $this->joinRequestModel->getPendingRequests((int)$projectId);
        $this->sendJsonResponse(['success' => true, 'requests' => $requests], 200);
    }

    public function approveJoinRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->sendJsonResponse(['success' => false], 405);

        $userPayload = AuthMiddleware::require();
        $userId = (int) $userPayload['sub'];
        $userRole = $userPayload['role'] ?? 'user';

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;
        $requestId = $data['request_id'] ?? null;

        if (!$requestId) $this->sendJsonResponse(['success' => false, 'message' => 'ID de solicitud requerido.'], 400);

        // Buscar la solicitud
        $request = $this->joinRequestModel->getRequestById((int)$requestId);
        if (!$request) $this->sendJsonResponse(['success' => false, 'message' => 'Solicitud no encontrada.'], 404);

        // Validar que el usuario que aprueba es el DUEÑO del proyecto
        $project = $this->projectModel->getProjectById($request['project_id']);
        if (!$project || ($project['created_by_user_id'] != $userId && $userRole !== 'admin')) {
            $this->sendJsonResponse(['success' => false, 'message' => 'No tienes permiso para aprobar solicitudes en este proyecto.'], 403);
        }

        // 1. Cambiar estado
        $this->joinRequestModel->updateStatus((int)$requestId, 'approved');
        
        // 2. Insertar como miembro
        $memberResult = $this->memberModel->addProjectMember(
            $request['project_id'], 
            $request['user_id'], 
            $request['user_name'], 
            $request['email']
        );

        if ($memberResult['success']) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Solicitud aprobada y usuario añadido al equipo.']);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Solicitud aprobada, pero hubo un error al añadir al usuario.'], 500);
        }
    }

    public function rejectJoinRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->sendJsonResponse(['success' => false], 405);

        $userPayload = AuthMiddleware::require();
        $userId = (int) $userPayload['sub'];
        $userRole = $userPayload['role'] ?? 'user';

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;
        $requestId = $data['request_id'] ?? null;

        if (!$requestId) $this->sendJsonResponse(['success' => false, 'message' => 'ID de solicitud requerido.'], 400);

        $request = $this->joinRequestModel->getRequestById((int)$requestId);
        if (!$request) $this->sendJsonResponse(['success' => false, 'message' => 'Solicitud no encontrada.'], 404);

        $project = $this->projectModel->getProjectById($request['project_id']);
        if (!$project || ($project['created_by_user_id'] != $userId && $userRole !== 'admin')) {
            $this->sendJsonResponse(['success' => false, 'message' => 'No tienes permiso para rechazar solicitudes en este proyecto.'], 403);
        }

        $this->joinRequestModel->updateStatus((int)$requestId, 'rejected');
        $this->sendJsonResponse(['success' => true, 'message' => 'Solicitud rechazada.']);
    }

    public function getFollowedProjects() 
    { 
        $userPayload = AuthMiddleware::require();
        $userId = (int) $userPayload['sub'];
        $projects = $this->joinRequestModel->getFollowedProjectsByUserId($userId);
        
        $this->sendJsonResponse(['success' => true, 'projects' => $projects]); 
    }

    // --- MÉTODOS EXISTENTES INTACTOS ---
    public function createProjectsMember() { $this->sendJsonResponse(['success'=>true, 'message'=>'Refactorizado. Usa approveJoinRequest.']); }
    public function getTasks() { $this->sendJsonResponse(['success'=>true, 'tasks'=>[]]); }
    public function getStats() { $this->sendJsonResponse(['success'=>true, 'stats'=>[]]); }
    public function getFiles() { $this->sendJsonResponse(['success'=>true, 'files'=>[]]); }
    public function getMembers() { 
        $id = $_GET['id'] ?? 0;
        $members = $this->memberModel->getMembersByProjectId((int)$id);
        $this->sendJsonResponse(['success'=>true, 'members'=>$members]); 
    }
    public function getActivity() { $this->sendJsonResponse(['success'=>true, 'activity'=>[]]); }

    private function sendJsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}