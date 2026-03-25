<?php

namespace App\Backend\Controllers;

use App\Backend\Models\ProjectModel;
use App\Backend\Models\DashboardModel;
use App\Backend\Models\TaskModel;
use App\Backend\Models\MemberModel;

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

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'in_progress';
        $dashboardSlug = $_POST['slug'] ?? '';
        $pitch = $_POST['pitch'] ?? '';

        // ************************************************
        // LEYENDO LOS 4 NUEVOS CAMPOS DEL FRONTEND
        // ************************************************
        $groupName = $_POST['group_name'] ?? '';
        $linkVideo = $_POST['link_video'] ?? '';
        $linkDeploy = $_POST['link_deploy'] ?? '';
        $membersData = $_POST['members_data'] ?? '';
        // ************************************************

        if (empty($title) || empty($description) || empty($dashboardSlug)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Faltan datos requeridos'], 400);
        }

        $validStatuses = ['in_progress', 'completed'];
        if (!in_array($status, $validStatuses)) {
            $status = 'in_progress';
        }

        // --- 1. Crear el proyecto sin imagen primero ---
        $projectId = $this->projectModel->createProject($pitch, $dashboardSlug, $title, $description, $status, $groupName, $linkVideo, $linkDeploy, $membersData, null);

        if (!$projectId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al crear el proyecto o dashboard no encontrado.'], 500);
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/projects/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Obtener la extensión del archivo
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = "project_{$projectId}." . strtolower($extension);
            $imagePath = $uploadDir . $imageName;

            // Si ya existe, se sobrescribe
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                // Guardar la URL relativa
                $imageUrl = '/uploads/projects/' . $imageName;

                // --- 3. Actualizar el proyecto con la URL de la imagen ---
                $this->projectModel->updateImage($projectId, $imageUrl);
            } else {
                $this->sendJsonResponse(['success' => false, 'message' => 'Error al mover la imagen al servidor.'], 500);
            }
        }

        if ($projectId) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Proyecto creado exitosamente', 'project_id' => $projectId], 201);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al crear el proyecto o dashboard no encontrado.'], 500);
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

        // Intentar obtener proyectos, capturando cualquier error fatal del modelo
        try {
            $projects = $this->projectModel->getProjectsByDashboardSlug($dashboardSlug);
            $this->sendJsonResponse(['success' => true, 'data' => $projects], 200);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al obtener proyectos: ' . $e->getMessage()], 500);
        }
    }

    // --- MÉTODOS EXISTENTES MANTENIDOS ---

    public function createProjectsMember()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        $role = $_POST['role'] ?? 'member';
        $email = $_POST['email'] ?? '';
        $name = $_POST['name'] ?? '';
        $projectId = $_POST['project_id'] ?? null;

        if (!$projectId || empty($email) || empty($name)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Faltan datos requeridos'], 400);
        }

        $result = $this->memberModel->addProjectMember((int)$projectId, $name, $email, $role);

        if ($result['success']) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Miembro creado', 'member_id' => $result['id']], 201);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => $result['message']], 403);
        }
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

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }
        $projectId = $_POST['id'] ?? null;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'in_progress';
        $pitch = $_POST['pitch'] ?? null;

        if (!$projectId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID requerido'], 400);
        }

        $imageData = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
        }

        $result = $this->projectModel->updateProject((int)$projectId, $title, $description, $status, $imageData, $pitch);
        
        if ($result) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Proyecto actualizado'], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al actualizar'], 500);
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->sendJsonResponse(['error'], 405);
        $projectId = $_POST['id'] ?? null;
        if ($this->projectModel->deleteProject((int)$projectId)) {
            $this->sendJsonResponse(['success' => true]);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al eliminar'], 500);
        }
    }

    // Métodos auxiliares para evitar errores de ruta
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
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}