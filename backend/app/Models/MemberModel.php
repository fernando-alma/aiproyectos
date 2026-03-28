<?php

namespace App\Backend\Models;

use PDO;
use App\Backend\Models\Database;

class MemberModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Añade un usuario a la tabla project_members usando su ID real.
     */
    public function addProjectMember(
        int $projectId,
        int $userId,
        string $name,
        string $email,
        string $role = 'member'
    ): array {
        // Verifica si el usuario ya está en otro proyecto (usando user_id)
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) 
            FROM project_members 
            WHERE user_id = ? AND project_id != ?
        ");
        $stmt->execute([$userId, $projectId]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            return [
                'success' => false,
                'message' => 'No puedes unirte a más de un proyecto simultáneamente.'
            ];
        }

        // Crear iniciales (soportando caracteres multibyte como acentos)
        $names = preg_split('/\s+/', trim($name));
        $initials = mb_strtoupper(mb_substr($names[0], 0, 1) . (isset($names[1]) ? mb_substr($names[1], 0, 1) : ''));

        // Insertar nuevo miembro (Añadido user_id)
        $stmt = $this->conn->prepare("
            INSERT INTO project_members (project_id, user_id, user_name, email, role, avatar_initials) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        if ($stmt->execute([$projectId, $userId, $name, $email, $role, $initials])) {
            return [
                'success' => true,
                'message' => 'Miembro añadido exitosamente.',
                'id' => (int)$this->conn->lastInsertId()
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al insertar el miembro.'
        ];
    }

    /**
     * Eliminar a un miembro del proyecto por su user_id.
     */
    public function deleteMember(int $userId, int $projectId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM project_members WHERE user_id = ? AND project_id = ?");
        return $stmt->execute([$userId, $projectId]);
    }

    public function getAllMembers(): array
    {
        $stmt = $this->conn->query("
            SELECT `id`, `project_id`, `user_id`, `user_name`, `email`, `role`, `avatar_initials`, `joined_at` 
            FROM `project_members` 
            ORDER BY joined_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMembersByProjectId(int $projectId): array
    {
        $stmt = $this->conn->prepare("
            SELECT `id`, `project_id`, `user_id`, `user_name`, `email`, `role`, `avatar_initials`, `joined_at` 
            FROM `project_members` 
            WHERE project_id = ? 
            ORDER BY joined_at DESC
        ");
        $stmt->execute([$projectId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Retornar array vacío si no hay resultados para respetar el tipo de retorno estricto
        return $members ?: [];
    }

    public function getProjectById(int $projectId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT p.*, d.slug as dashboard_slug 
            FROM projects p 
            JOIN dashboards d ON p.dashboard_id = d.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        return $project ?: null;
    }

    /**
     * Refactorizado: Buscar por user_id en lugar de email
     */
    public function getProjectByUserId(int $userId): ?array
    {
        $stmt = $this->conn->prepare("
            SELECT `project_id`, `email`, `role`, `joined_at` 
            FROM `project_members` 
            WHERE `user_id` = ? 
            ORDER BY joined_at DESC
        ");
        $stmt->execute([$userId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        return $project ?: null;
    }
}