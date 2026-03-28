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

    /**
     * Crear una solicitud de unión vinculada al ID real del usuario.
     */
    public function createRequest(int $projectId, int $userId, string $userName, string $email): array
    {
        // 1. Verificamos por user_id en lugar de solo email (Más seguro)
        $stmt = $this->conn->prepare("SELECT * FROM join_requests WHERE project_id = ? AND user_id = ? AND status = 'pending'");
        $stmt->execute([$projectId, $userId]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Ya has enviado una solicitud pendiente a este proyecto.'];
        }

        // 2. Insertamos la solicitud incluyendo la clave foránea user_id
        $stmt = $this->conn->prepare("
            INSERT INTO join_requests (project_id, user_id, user_name, email, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");

        $success = $stmt->execute([$projectId, $userId, $userName, $email]);

        return $success
            ? ['success' => true, 'id' => $this->conn->lastInsertId()]
            : ['success' => false, 'message' => 'Error al crear la solicitud.'];
    }

    /**
     * Obtener solicitudes pendientes de un proyecto.
     */
    public function getPendingRequests(int $projectId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM join_requests WHERE project_id = ? AND status = 'pending'");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el estado ('pending', 'approved', 'rejected').
     */
    public function updateStatus(int $requestId, string $status): bool
    {
        // Actualizamos el estado y marcamos la fecha de decisión
        $stmt = $this->conn->prepare("UPDATE join_requests SET status = ?, decision_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $requestId]);
    }

    /**
     * Obtener solicitud por ID.
     */
    public function getRequestById(int $requestId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM join_requests WHERE id = ?");
        $stmt->execute([$requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Refactorizado: Obtener proyectos seguidos usando el user_id (más robusto).
     */
    public function getFollowedProjectsByUserId(int $userId): array
    {
        $stmt = $this->conn->prepare('
            SELECT p.id, p.title, p.description, jr.status as join_status
            FROM join_requests jr
            INNER JOIN projects p ON jr.project_id = p.id
            WHERE jr.user_id = :user_id
        ');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}