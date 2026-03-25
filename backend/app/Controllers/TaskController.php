<?php

namespace App\Backend\Controllers;

use App\Backend\Models\TaskModel;
use App\Backend\Models\ProjectModel;

class TaskController
{
    private $taskModel;
    private $projectModel;

    public function __construct()
    {
        $this->taskModel = new TaskModel();
        $this->projectModel = new ProjectModel();
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $projectId = $_POST['project_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';
        $assignedTo = trim($_POST['assigned_to'] ?? '');
        $dueDate = $_POST['due_date'] ?? null;

        if (!$projectId || empty($title) || empty($description) || empty($dueDate)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Faltan datos requeridos (ID de proyecto, título, descripción, fecha de vencimiento)'], 400);
        }

        $existingProject = $this->projectModel->getProjectById((int)$projectId);
        if (!$existingProject) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Proyecto no encontrado'], 404);
        }

        $validPriorities = ['low', 'medium', 'high'];
        if (!in_array($priority, $validPriorities)) {
            $priority = 'medium';
        }

        $taskId = $this->taskModel->createTask((int)$projectId, $title, $description, $priority, $assignedTo ?: null, $dueDate);

        if ($taskId) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Tarea creada correctamente', 'task_id' => $taskId], 201);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al crear la tarea'], 500);
        }
    }

    public function getTasks()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $projectId = $_GET['id'] ?? null;
        if (!$projectId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID de proyecto requerido.'], 400);
        }

        $tasks = $this->taskModel->getTasksByProjectId((int)$projectId);

        $this->sendJsonResponse(['success' => true, 'tasks' => $tasks], 200);
    }

    public function getTask()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $taskId = $_GET['id'] ?? null;
        if (!$taskId) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID de tarea requerido.'], 400);
        }

        $task = $this->taskModel->getTaskById((int)$taskId);

        if ($task) {
            $this->sendJsonResponse(['success' => true, 'task' => $task], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Tarea no encontrada.'], 404);
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $taskId = $_POST['task_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';
        $assignedTo = trim($_POST['assigned_to'] ?? '');
        $dueDate = $_POST['due_date'] ?? null;
        $status = $_POST['status'] ?? 'pending';

        if (empty($taskId) || empty($title) || empty($description) || empty($dueDate)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Faltan datos requeridos (ID de tarea, título, descripción, fecha de vencimiento)'], 400);
        }

        $validPriorities = ['low', 'medium', 'high'];
        if (!in_array($priority, $validPriorities)) {
            $priority = 'medium';
        }

        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            $status = 'pending';
        }

        if ($this->taskModel->updateTask((int)$taskId, $title, $description, $priority, $assignedTo ?: null, $dueDate, $status)) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Tarea actualizada correctamente'], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al actualizar la tarea o no se encontró.'], 500);
        }
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $taskId = $_POST['id'] ?? null;

        if (empty($taskId)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID de tarea requerido para eliminar.'], 400);
        }

        if ($this->taskModel->deleteTask((int)$taskId)) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Tarea eliminada exitosamente'], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al eliminar la tarea o no se encontró.'], 500);
        }
    }

    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
        }

        $taskId = $_POST['task_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if (empty($taskId) || empty($status)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'ID de tarea y estado son requeridos.'], 400);
        }

        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            $this->sendJsonResponse(['success' => false, 'message' => 'Estado inválido.'], 400);
        }

        if ($this->taskModel->updateTaskStatus((int)$taskId, $status)) {
            $this->sendJsonResponse(['success' => true, 'message' => 'Estado de tarea actualizado exitosamente.'], 200);
        } else {
            $this->sendJsonResponse(['success' => false, 'message' => 'Error al actualizar el estado de la tarea o no se encontró.'], 500);
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
