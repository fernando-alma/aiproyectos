<?php

namespace App\Backend\Models;

use PDO;

class JoinRequestModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function createRequest(int $projectId, string $userName, string $email): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM join_requests WHERE project_id = ? AND email = ? AND status = 'pending'");
        $stmt->execute([$projectId, $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Ya has enviado una solicitud pendiente a este proyecto.'];
        }

        $stmt = $this->conn->prepare("
            INSERT INTO join_requests (project_id, user_name, email)
            VALUES (?, ?, ?)
        ");

        $success = $stmt->execute([$projectId, $userName, $email]);

        return $success
            ? ['success' => true, 'id' => $this->conn->lastInsertId()]
            : ['success' => false, 'message' => 'Error al crear la solicitud.'];
    }

    public function getPendingRequests(int $projectId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM join_requests WHERE project_id = ? AND status = 'pending'");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus(int $requestId, string $status): bool
    {
        $stmt = $this->conn->prepare("UPDATE join_requests SET status = ?, decision_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $requestId]);
    }

    public function getRequestById(int $requestId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM join_requests WHERE id = ?");
        $stmt->execute([$requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public function getFollowedProjectsByEmail(string $email): array
    {
        $stmt = $this->conn->prepare('
        SELECT p.id, p.title , p.description
        FROM join_requests jr
        INNER JOIN projects p ON jr.project_id = p.id
        WHERE jr.email = :email
    ');
    $stmt->execute(['email' => $email]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}


