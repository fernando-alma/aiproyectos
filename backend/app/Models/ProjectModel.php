<?php

namespace App\Backend\Models;

use PDO;
use App\Backend\Models\Database;
use App\Backend\Models\DashboardModel;
use App\Backend\Models\TaskModel;
use App\Backend\Models\MemberModel;

class ProjectModel
{
    private $conn;
    private $dashboardModel;
    private $taskModel;
    private $memberModel;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
        $this->dashboardModel = new DashboardModel();
        $this->taskModel = new TaskModel();
        $this->memberModel = new MemberModel();
    }

    /**
     * Crear un nuevo proyecto
     */
    public function createProject(
        string $pitch,
        string $dashboardSlug,
        string $title,
        string $description,
        string $status,
        string $groupName,
        string $linkVideo,
        string $linkDeploy,
        int $createdByUserId,
        ?string $imageData = null,
        ?string $linkRepository = null
    ): ?int {
        $dashboard = $this->dashboardModel->findBySlug($dashboardSlug);
        if (!$dashboard) {
            return null;
        }

        $sql = "INSERT INTO projects (
                    dashboard_id, 
                    title, 
                    description, 
                    status, 
                    image, 
                    pitch,
                    group_name,
                    link_video,
                    link_deploy,
                    link_repository,
                    created_by_user_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        
        $params = [
            $dashboard['id'],      // 1. dashboard_id
            $title,                // 2. title
            $description,          // 3. description
            $status,               // 4. status
            $imageData,            // 5. image (NULL si no hay)
            $pitch,                // 6. pitch
            $groupName,            // 7. group_name
            $linkVideo,            // 8. link_video
            $linkDeploy,           // 9. link_deploy
            $linkRepository,       // 10. link_repository
            $createdByUserId       // 11. created_by_user_id
        ];
        
        if ($stmt->execute($params)) {
            return (int) $this->conn->lastInsertId();
        }
        return null;
    }

    public function updateImage(int $projectId, string $imageUrl): bool 
    {
        $stmt = $this->conn->prepare("UPDATE projects SET image = ? WHERE id = ?");
        return $stmt->execute([$imageUrl, $projectId]);
    }

    public function getProjectsByDashboardSlug(string $dashboardSlug): array
    {
        $dashboard = $this->dashboardModel->findBySlug($dashboardSlug);
        if (!$dashboard) {
            return [];
        }

        $stmt = $this->conn->prepare("SELECT * FROM projects WHERE dashboard_id = ? ORDER BY created_at DESC");
        $stmt->execute([$dashboard['id']]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un proyecto por ID.
     */
    public function getProjectById(int $projectId): ?array
    {
        $stmt = $this->conn->prepare("SELECT p.*, d.slug as dashboard_slug FROM projects p JOIN dashboards d ON p.dashboard_id = d.id WHERE p.id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        return $project ?: null;
    }

    /**
     * Actualiza un proyecto existente (Ahora soporta todos los campos).
     */
    public function updateProject(
        int $projectId, 
        string $title, 
        string $description, 
        string $status, 
        string $pitch,
        string $groupName,
        string $linkVideo,
        string $linkDeploy,
        ?string $imageData = null,
        ?string $linkRepository = null
    ): bool {
        if ($imageData !== null) {
            $sql = "UPDATE projects 
                    SET title = ?, description = ?, pitch = ?, status = ?, group_name = ?, link_video = ?, link_deploy = ?, link_repository = ?, image = ? 
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$title, $description, $pitch, $status, $groupName, $linkVideo, $linkDeploy, $linkRepository, $imageData, $projectId]);
        } else {
            $sql = "UPDATE projects 
                    SET title = ?, description = ?, pitch = ?, status = ?, group_name = ?, link_video = ?, link_deploy = ?, link_repository = ? 
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$title, $description, $pitch, $status, $groupName, $linkVideo, $linkDeploy, $linkRepository, $projectId]);
        }
    }

    public function deleteProject(int $projectId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM projects WHERE id = ?");
        return $stmt->execute([$projectId]);
    }

    public function getProjectStats(int $projectId): array
    {
        $tasks = [];
        if (method_exists($this->taskModel, 'getTasksByProjectId')) {
            $tasks = $this->taskModel->getTasksByProjectId($projectId);
        }

        $totalTasks = count($tasks);
        $completedTasks = count(array_filter($tasks, fn($task) => isset($task['status']) && $task['status'] === 'completed'));

        $members = [];
        if (method_exists($this->memberModel, 'getMembersByProjectId')) {
            $members = $this->memberModel->getMembersByProjectId($projectId);
        }
        $totalMembers = count($members);
        
        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return [
            'progress' => $progress,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'total_members' => $totalMembers,
            'total_files' => 0
        ];
    }
}