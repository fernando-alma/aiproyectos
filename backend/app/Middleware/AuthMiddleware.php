<?php

namespace App\Backend\Middleware;

/**
 * AuthMiddleware
 *
 * Uso en cualquier Controller que requiera autenticación:
 *
 *   use App\Backend\Middleware\AuthMiddleware;
 *
 *   public function create(): void {
 *       $user = AuthMiddleware::require();
 *       // $user['sub'] = id del usuario autenticado
 *       // ...
 *   }
 *
 * Para rutas que sólo muestran info extra si el usuario está logueado
 * (sin bloquear si no lo está):
 *
 *   $user = AuthMiddleware::optional();
 *   $isLoggedIn = $user !== null;
 */
class AuthMiddleware
{
    private static string $jwtSecret = '';

    // ------------------------------------------------------------------
    // Inicializa la clave desde el entorno (llamar una sola vez en bootstrap)
    // ------------------------------------------------------------------
    public static function init(): void
    {
        self::$jwtSecret = $_ENV['JWT_SECRET'] ?? 'CAMBIA_ESTA_CLAVE_EN_ENV';
    }

    // ------------------------------------------------------------------
    // Require: aborta con 401 si no hay token válido
    // ------------------------------------------------------------------
    public static function require(): array
    {
        $payload = self::extractAndVerify();

        if (!$payload) {
            self::abort(401, 'Token requerido o inválido.');
        }

        return $payload;
    }

    // ------------------------------------------------------------------
    // Optional: devuelve el payload o null (no aborta)
    // ------------------------------------------------------------------
    public static function optional(): array|null
    {
        return self::extractAndVerify();
    }

    // ------------------------------------------------------------------
    // RequireRole: require + comprueba el rol
    // ------------------------------------------------------------------
    public static function requireRole(string $role): array
    {
        $payload = self::require();

        if (($payload['role'] ?? '') !== $role) {
            self::abort(403, 'No tenés permisos para realizar esta acción.');
        }

        return $payload;
    }

    // ==================================================================
    // Privados
    // ==================================================================

    private static function extractAndVerify(): array|null
    {
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

        $expectedSig = self::base64url(
            hash_hmac('sha256', "$header.$payload", self::$jwtSecret, true)
        );

        if (!hash_equals($expectedSig, $sig)) {
            return null;
        }

        $data = json_decode(self::base64urlDecode($payload), true);

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
