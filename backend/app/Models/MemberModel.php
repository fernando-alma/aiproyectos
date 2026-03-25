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


 
public function addProjectMember(
    ?int $projectId,
    string $name,
    string $email,
    string $role = 'member'
): array {
    // Verifica si el usuario ya está en otro proyecto
    $stmt = $this->conn->prepare("
        SELECT COUNT(*) 
        FROM project_members 
        WHERE email = ? AND (project_id != ? OR ? IS NULL)
    ");
    $stmt->execute([$email, $projectId, $projectId]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        return [
            'success' => false,
            'message' => 'No puedes unirte a más de un proyecto.'
        ];
    }

    // Crear iniciales
    $names = preg_split('/\s+/', trim($name));
    $initials = strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));

    // Insertar nuevo miembro
    $stmt = $this->conn->prepare("
        INSERT INTO project_members (project_id, user_name, email, role, avatar_initials) 
        VALUES (?, ?, ?, ?, ?)
    ");

    if ($stmt->execute([$projectId, $name, $email, $role, $initials])) {
        return [
            'success' => true,
            'message' => 'Miembro creado exitosamente',
            'id' => (int)$this->conn->lastInsertId()
        ];
    }

    return [
        'success' => false,
        'message' => 'Error al insertar el miembro.'
    ];
}

  public function deleteMember(String $memberEmail, int $projectId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM project_members WHERE email = ? AND project_id = ?");
        return $stmt->execute([$memberEmail, $projectId]);
    }
  

    public function getAllMembers(): array
    {
        $stmt = $this->conn->query("SELECT `id`,`project_id`, `user_name`, `email`, `role`, `avatar_initials`, `joined_at` FROM `project_members` WHERE 1  ORDER BY joined_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // los que se muestran en proyectos
     public function getMembersByProjectId(int $projectId): array
    {
       

        $stmt = $this->conn->prepare("SELECT `id`,`project_id`, `user_name`, `email`, `role`, `avatar_initials`, `joined_at` FROM `project_members` WHERE  project_id = ?  ORDER BY joined_at DESC ");
        $stmt->execute([$projectId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $members ?: null;
    }

     public function getProjectById(int $projectId): ?array
    {
        $stmt = $this->conn->prepare("SELECT p.*, d.slug as dashboard_slug FROM projects p JOIN dashboards d ON p.dashboard_id = d.id WHERE p.id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        return $project ?: null;
    }

     public function getProjectByMailMember(string $mailMember): ?array
    {
        $stmt = $this->conn->prepare("SELECT `project_id`, `email`, `role`,  `joined_at` FROM `project_members` WHERE `email` = ?  ORDER BY joined_at DESC");
        $stmt->execute([$mailMember]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        return $project ?: null;
    }
}
