<?php

namespace App\Backend\Models;

use App\Backend\Models\Database;
use PDO;

class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ------------------------------------------------------------------
    // Registro de usuario
    // ------------------------------------------------------------------
    public function create(string $name, string $email, string $password): array|false
    {
        // Verificar duplicado
        if ($this->findByEmail($email)) {
            return false;
        }

        $hash   = password_hash($password, PASSWORD_DEFAULT);
        $initials = $this->generateInitials($name);

        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password_hash, avatar_initials)
             VALUES (:name, :email, :hash, :initials)"
        );
        $stmt->execute([
            ':name'     => $name,
            ':email'    => $email,
            ':hash'     => $hash,
            ':initials' => $initials,
        ]);

        $id = (int) $this->db->lastInsertId();
        return $this->findById($id);
    }

    // ------------------------------------------------------------------
    // Login: devuelve el usuario si las credenciales son correctas
    // ------------------------------------------------------------------
    public function verifyCredentials(string $email, string $password): array|false
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // No exponer el hash al exterior
        unset($user['password_hash']);
        return $user;
    }

    // ------------------------------------------------------------------
    // Buscar por email
    // ------------------------------------------------------------------
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE email = :email LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    // ------------------------------------------------------------------
    // Buscar por ID
    // ------------------------------------------------------------------
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT id, name, email, role, avatar_initials, created_at
             FROM users WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    // ------------------------------------------------------------------
    // Actualizar perfil
    // ------------------------------------------------------------------
    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'avatar_initials'];
        $sets = [];
        $params = [':id' => $id];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $sets[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($sets)) {
            return false;
        }

        $sql  = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // ------------------------------------------------------------------
    // Cambiar contraseña (requiere contraseña actual)
    // ------------------------------------------------------------------
    public function changePassword(int $id, string $currentPassword, string $newPassword): bool
    {
        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
            return false;
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt2   = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        return $stmt2->execute([':hash' => $newHash, ':id' => $id]);
    }

    // ------------------------------------------------------------------
    // Helper: genera iniciales del nombre
    // ------------------------------------------------------------------
    private function generateInitials(string $name): string
    {
        $words  = preg_split('/\s+/', trim($name));
        $result = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $result .= mb_strtoupper(mb_substr($word, 0, 1));
        }
        return $result ?: 'US';
    }
}
