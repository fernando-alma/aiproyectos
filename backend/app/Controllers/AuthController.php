<?php

namespace App\Backend\Controllers;

use App\Backend\Models\UserModel;
use App\Backend\Middleware\AuthMiddleware;

/**
 * AuthController
 *
 * Endpoints:
 * POST  auth/register        — Crear cuenta
 * POST  auth/login           — Obtener JWT
 * GET   auth/me              — Perfil del usuario autenticado   [requiere JWT]
 * POST  auth/logout          — Invalidar token (client-side)
 * POST  auth/change-password — Cambiar contraseña               [requiere JWT]
 */
class AuthController
{
    private UserModel $userModel;
    private string    $jwtSecret;
    private int       $jwtTtl = 86400; // 24 horas en segundos

    public function __construct()
    {
        $this->userModel = new UserModel();
        // La clave se lee del .env — nunca hardcodeada
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'CAMBIA_ESTA_CLAVE_EN_ENV';
    }

    // ------------------------------------------------------------------
    // POST auth/register
    // ------------------------------------------------------------------
    public function register(): void
    {
        $data = $this->getJsonBody();

        $name     = trim($data['name']     ?? '');
        $email    = trim($data['email']    ?? '');
        $password = $data['password']      ?? '';
        $confirm  = $data['password_confirm'] ?? '';

        if (!$name || !$email || !$password) {
            $this->json(['success' => false, 'message' => 'Todos los campos son requeridos.'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'El email no es válido.'], 400);
        }

        if (strlen($password) < 8) {
            $this->json(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'], 400);
        }

        if ($password !== $confirm) {
            $this->json(['success' => false, 'message' => 'Las contraseñas no coinciden.'], 400);
        }

        $user = $this->userModel->create($name, $email, $password);

        if ($user === false) {
            $this->json(['success' => false, 'message' => 'El email ya está registrado.'], 409);
        }

        $token = $this->jwtEncode($user);

        $this->json([
            'success' => true,
            'message' => 'Usuario creado correctamente.',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    // ------------------------------------------------------------------
    // POST auth/login
    // ------------------------------------------------------------------
    public function login(): void
    {
        $data = $this->getJsonBody();

        $email    = trim($data['email']    ?? '');
        $password = $data['password']      ?? '';

        if (!$email || !$password) {
            $this->json(['success' => false, 'message' => 'Email y contraseña requeridos.'], 400);
        }

        $user = $this->userModel->verifyCredentials($email, $password);

        if (!$user) {
            $this->json(['success' => false, 'message' => 'Credenciales incorrectas.'], 401);
        }

        $token = $this->jwtEncode($user);

        $this->json([
            'success' => true,
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    // ------------------------------------------------------------------
    // GET auth/me
    // ------------------------------------------------------------------
    public function me(): void
    {
        // 1. Delegamos la validación al Middleware Centralizado
        $payload = AuthMiddleware::require();

        // 2. Buscamos al usuario usando el ID del payload (sub)
        $user = $this->userModel->findById((int) $payload['sub']);

        if (!$user) {
            $this->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        $this->json(['success' => true, 'user' => $user]);
    }

    // ------------------------------------------------------------------
    // POST auth/logout
    // ------------------------------------------------------------------
    public function logout(): void
    {
        $this->json([
            'success' => true,
            'message' => 'Sesión cerrada. Por favor eliminá el token del cliente.',
        ]);
    }

    // ------------------------------------------------------------------
    // POST auth/change-password
    // ------------------------------------------------------------------
    public function changePassword(): void
    {
        // Delegamos la validación al Middleware Centralizado
        $payload = AuthMiddleware::require();
        $data    = $this->getJsonBody();

        $current = $data['current_password']     ?? '';
        $new     = $data['new_password']         ?? '';
        $confirm = $data['new_password_confirm'] ?? '';

        if (!$current || !$new) {
            $this->json(['success' => false, 'message' => 'Campos requeridos.'], 400);
        }

        if (strlen($new) < 8) {
            $this->json(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres.'], 400);
        }

        if ($new !== $confirm) {
            $this->json(['success' => false, 'message' => 'Las contraseñas nuevas no coinciden.'], 400);
        }

        $ok = $this->userModel->changePassword((int) $payload['sub'], $current, $new);

        if (!$ok) {
            $this->json(['success' => false, 'message' => 'Contraseña actual incorrecta.'], 401);
        }

        $this->json(['success' => true, 'message' => 'Contraseña actualizada correctamente.']);
    }

    // ==================================================================
    // Helpers privados
    // ==================================================================

    private function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Genera un JWT firmado.
     * Aquí incrustamos explícitamente la columna ROLE de la DB.
     */
    private function jwtEncode(array $user): string
    {
        $header  = $this->base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        
        $payload = $this->base64url(json_encode([
            'sub'   => $user['id'],
            'email' => $user['email'],
            'name'  => $user['name'],
            'role'  => $user['role'] ?? 'user', // Fallback seguro a 'user'
            'iat'   => time(),
            'exp'   => time() + $this->jwtTtl,
        ]));
        
        $sig = $this->base64url(hash_hmac('sha256', "$header.$payload", $this->jwtSecret, true));
        return "$header.$payload.$sig";
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        // Se eliminaron los headers CORS duplicados (ahora es responsabilidad de index.php)
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}