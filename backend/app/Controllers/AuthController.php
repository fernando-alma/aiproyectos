<?php

namespace App\Backend\Controllers;

use App\Backend\Models\UserModel;

/**
 * AuthController
 *
 * Endpoints:
 *   POST  auth/register       — Crear cuenta
 *   POST  auth/login          — Obtener JWT
 *   GET   auth/me             — Perfil del usuario autenticado   [requiere JWT]
 *   POST  auth/logout         — Invalidar token (client-side)
 *   POST  auth/change-password — Cambiar contraseña              [requiere JWT]
 *
 * JWT mínimo implementado sin dependencias externas (HMAC-SHA256).
 * Si el proyecto ya tiene composer.json podés agregar firebase/php-jwt
 * y reemplazar los métodos jwt* por esa librería.
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
    // Body JSON: { name, email, password, password_confirm }
    // ------------------------------------------------------------------
    public function register(): void
    {
        $data = $this->getJsonBody();

        $name     = trim($data['name']     ?? '');
        $email    = trim($data['email']    ?? '');
        $password = $data['password']      ?? '';
        $confirm  = $data['password_confirm'] ?? '';

        // Validaciones básicas
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
    // Body JSON: { email, password }
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
            // Mensaje genérico a propósito (no indicar si el email existe)
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
    // GET auth/me    [requiere Authorization: Bearer <token>]
    // ------------------------------------------------------------------
    public function me(): void
    {
        $payload = $this->requireAuth();

        $user = $this->userModel->findById((int) $payload['sub']);

        if (!$user) {
            $this->json(['success' => false, 'message' => 'Usuario no encontrado.'], 404);
        }

        $this->json(['success' => true, 'user' => $user]);
    }

    // ------------------------------------------------------------------
    // POST auth/logout
    // JWT es stateless: el cliente simplemente borra el token.
    // Este endpoint existe para compatibilidad y para documentación Swagger.
    // ------------------------------------------------------------------
    public function logout(): void
    {
        $this->json([
            'success' => true,
            'message' => 'Sesión cerrada. Por favor eliminá el token del cliente.',
        ]);
    }

    // ------------------------------------------------------------------
    // POST auth/change-password   [requiere JWT]
    // Body JSON: { current_password, new_password, new_password_confirm }
    // ------------------------------------------------------------------
    public function changePassword(): void
    {
        $payload = $this->requireAuth();
        $data    = $this->getJsonBody();

        $current = $data['current_password']      ?? '';
        $new     = $data['new_password']          ?? '';
        $confirm = $data['new_password_confirm']  ?? '';

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

    /**
     * Lee el body JSON y devuelve un array.
     */
    private function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Fallback a $_POST si el frontend manda form-urlencoded
            $data = $_POST;
        }

        return is_array($data) ? $data : [];
    }

    /**
     * Extrae y valida el JWT del header Authorization.
     * Termina con 401 si no es válido.
     */
    private function requireAuth(): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
               ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
               ?? '';

        if (!str_starts_with($header, 'Bearer ')) {
            $this->json(['success' => false, 'message' => 'Token requerido.'], 401);
        }

        $token   = substr($header, 7);
        $payload = $this->jwtDecode($token);

        if (!$payload) {
            $this->json(['success' => false, 'message' => 'Token inválido o expirado.'], 401);
        }

        return $payload;
    }

    /**
     * Genera un JWT firmado con HMAC-SHA256.
     * Payload mínimo: sub (user id), email, role, iat, exp.
     */
    private function jwtEncode(array $user): string
    {
        $header  = $this->base64url(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode([
            'sub'   => $user['id'],
            'email' => $user['email'],
            'name'  => $user['name'],
            'role'  => $user['role'],
            'iat'   => time(),
            'exp'   => time() + $this->jwtTtl,
        ]));
        $sig = $this->base64url(hash_hmac('sha256', "$header.$payload", $this->jwtSecret, true));
        return "$header.$payload.$sig";
    }

    /**
     * Verifica y decodifica un JWT.
     * Devuelve el payload como array o false si es inválido/expirado.
     */
    private function jwtDecode(string $token): array|false
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        [$header, $payload, $sig] = $parts;

        $expectedSig = $this->base64url(hash_hmac('sha256', "$header.$payload", $this->jwtSecret, true));

        if (!hash_equals($expectedSig, $sig)) {
            return false;
        }

        $data = json_decode($this->base64urlDecode($payload), true);

        if (!$data || !isset($data['exp']) || $data['exp'] < time()) {
            return false;
        }

        return $data;
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }

    /**
     * Envía respuesta JSON y termina la ejecución.
     */
    private function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
