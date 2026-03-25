<?php

namespace App\Backend\Models;

use PDO;
use App\Backend\Models\Database;

class TaskModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function createTask(int $projectId, string $title, string $description, string $priority, ?string $assignedTo, string $dueDate): ?int
    {
        $stmt = $this->conn->prepare("
            INSERT INTO tasks (project_id, title, description, priority, assigned_to, due_date, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        if ($stmt->execute([
            $projectId,
            $title,
            $description,
            $priority,
            $assignedTo,
            $dueDate
        ])) {
            return (int) $this->conn->lastInsertId();
        }
        return null;
    }

    public function getTaskById(int $taskId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        return $task ?: null;
    }

    public function getTasksByProjectId(int $projectId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE project_id = ? ORDER BY created_at DESC");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTask(int $taskId, string $title, string $description, string $priority, ?string $assignedTo, string $dueDate, string $status): bool
    {
        $stmt = $this->conn->prepare("UPDATE tasks SET title = ?, description = ?, priority = ?, assigned_to = ?, due_date = ?, status = ? WHERE id = ?");
        return $stmt->execute([$title, $description, $priority, $assignedTo, $dueDate, $status, $taskId]);
    }

    public function deleteTask(int $taskId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$taskId]);
    }

    public function updateTaskStatus(int $taskId, string $status): bool
    {
        $stmt = $this->conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $taskId]);
    }
}
