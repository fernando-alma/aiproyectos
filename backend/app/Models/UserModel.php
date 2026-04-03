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
    // Registro de usuario (Por defecto nace como 'user' en la DB)
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

        // No exponer el hash al exterior por seguridad
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
    // Obtener todos los usuarios (Exclusivo para Súper Admin)
    // ------------------------------------------------------------------
    public function findAll(): array
    {
        $stmt = $this->db->query(
            "SELECT id, name, email, role, avatar_initials, created_at 
             FROM users ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------------------------------
    // Actualizar perfil (Solo datos permitidos)
    // ------------------------------------------------------------------
    public function update(int $id, array $data): bool
    {
        // IMPORTANTE: 'role' NO está aquí para evitar escalada de privilegios
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
    // Actualizar ROL (Exclusivo para Súper Admin)
    // ------------------------------------------------------------------
    public function updateRole(int $id, string $role): bool
    {
        // Validar estrictamente los roles permitidos
        if (!in_array($role, ['user', 'admin'])) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
        return $stmt->execute([':role' => $role, ':id' => $id]);
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

    // ------------------------------------------------------------------
    // RECUPERACIÓN DE CONTRASEÑA
    // ------------------------------------------------------------------

    public function createPasswordResetToken(string $email): string|false
    {
        // Borramos cualquier token viejo de este email para que no se acumulen
        $stmtDel = $this->db->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmtDel->execute([':email' => $email]);

        // Generamos un token seguro
        $token = bin2hex(random_bytes(32));

        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token) VALUES (:email, :token)");
        if ($stmt->execute([':email' => $email, ':token' => $token])) {
            return $token;
        }
        return false;
    }

    public function verifyPasswordResetToken(string $token): array|false
    {
        // Buscamos el token y verificamos que se haya creado hace menos de 1 hora
        $stmt = $this->db->prepare("SELECT email FROM password_resets WHERE token = :token AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) LIMIT 1");
        $stmt->execute([':token' => $token]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    public function consumePasswordResetToken(string $token): void
    {
        $stmt = $this->db->prepare("DELETE FROM password_resets WHERE token = :token");
        $stmt->execute([':token' => $token]);
    }

    public function updatePasswordByEmail(string $email, string $newPassword): bool
    {
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE email = :email");
        return $stmt->execute([':hash' => $newHash, ':email' => $email]);
    }
}