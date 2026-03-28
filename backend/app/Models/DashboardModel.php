<?php

namespace App\Backend\Models;

use PDO;
use App\Backend\Models\Database;

class DashboardModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Crear Dashboard inyectando el nombre y el ID del Súper Admin
     */
    public function createDashboard(string $title, string $description, string $color, string $createdByName, int $createdByUserId): ?string
    {
        // Generamos el slug de forma automática a partir del título
        $slug = $this->generateUniqueSlug($title);

        // Insertamos guardando tanto el nombre como el ID real del creador
        $stmt = $this->conn->prepare("INSERT INTO dashboards (slug, title, description, color, created_by, created_by_user_id) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$slug, $title, $description, $color, $createdByName, $createdByUserId])) {
            return $slug;
        }
        return null;
    }
    
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM dashboards WHERE slug = ?");
        $stmt->execute([$slug]);
        $dashboard = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dashboard ?: null;
    }

    private function generateUniqueSlug(string $title): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        $originalSlug = $slug;
        $counter = 1;
        while ($this->findBySlug($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        return $slug;
    }

    public function updateDashboard(string $slug, string $title, string $description, string $color): bool
    {
        $stmt = $this->conn->prepare("UPDATE dashboards SET title = ?, description = ?, color = ? WHERE slug = ?");
        return $stmt->execute([$title, $description, $color, $slug]);
    }

    public function deleteDashboard(string $slug): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM dashboards WHERE slug = ?");
        return $stmt->execute([$slug]);
    }

    public function getAllDashboards(): array
    {
        // Agregamos created_by_user_id a la consulta para el panel de administración
        $stmt = $this->conn->query("
            SELECT d.slug, d.title, d.description, d.color, d.created_at, d.created_by, d.created_by_user_id,
                   (SELECT Count(*) FROM `projects` t where t.dashboard_id = d.id) as total_projects 
            FROM dashboards d 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}