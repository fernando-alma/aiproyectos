<?php

namespace App\Backend\Middleware;

/**
 * AuthMiddleware
 *
 * Vigilante global de seguridad. Verifica JWT y roles.
 */
class AuthMiddleware
{
    private static string $jwtSecret = '';

    /**
     * Inicializa la clave desde el entorno
     */
    public static function init(): void
    {
        self::$jwtSecret = $_ENV['JWT_SECRET'] ?? 'CAMBIA_ESTA_CLAVE_EN_ENV';
    }

    /**
     * Require: Aborta con 401 si no hay token válido.
     * @return array Payload del JWT
     */
    public static function require(): array
    {
        $payload = self::extractAndVerify();

        if (!$payload) {
            self::abort(401, 'Token requerido o inválido. Por favor, inicia sesión.');
        }

        return $payload;
    }

    /**
     * RequireAdmin: El ESCUDO del Super Admin.
     * Aborta si el usuario no tiene role = 'admin'.
     */
    public static function requireAdmin(): array
    {
        $payload = self::require();

        if (($payload['role'] ?? '') !== 'admin') {
            self::abort(403, 'Acceso denegado. Se requieren permisos de Súper Administrador.');
        }

        return $payload;
    }

    /**
     * Optional: Devuelve el payload o null (no bloquea la ejecución)
     */
    public static function optional(): array|null
    {
        return self::extractAndVerify();
    }

    /**
     * RequireRole: Permite validar cualquier rol específico (ej: 'mentor', 'user')
     */
    public static function requireRole(string $role): array
    {
        $payload = self::require();

        if (($payload['role'] ?? '') !== $role) {
            self::abort(403, 'No tienes permisos suficientes para realizar esta acción.');
        }

        return $payload;
    }

    // ==================================================================
    // Métodos Internos de Procesamiento
    // ==================================================================

    private static function extractAndVerify(): array|null
    {
        // Soporte para diferentes configuraciones de servidor (Apache/Nginx)
        $header = $_SERVER['HTTP_AUTHORIZATION']
               ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
               ?? '';

        if (!str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $token = substr($header, 7);
        return self::decode($token);
    }

    private static function decode(string $token): array|null
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $sig] = $parts;

        // Verificar firma HMAC-SHA256
        $expectedSig = self::base64url(
            hash_hmac('sha256', "$header.$payload", self::$jwtSecret, true)
        );

        if (!hash_equals($expectedSig, $sig)) {
            return null;
        }

        $data = json_decode(self::base64urlDecode($payload), true);

        // Verificar expiración
        if (!$data || !isset($data['exp']) || $data['exp'] < time()) {
            return null;
        }

        return $data;
    }

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64urlDecode(string $data): string
    {
        return base64_decode(
            strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4)
        );
    }

    private static function abort(int $status, string $message): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}