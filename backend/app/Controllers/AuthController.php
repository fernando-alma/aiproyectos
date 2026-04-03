<?php

namespace App\Backend\Controllers;

use App\Backend\Models\UserModel;
use App\Backend\Middleware\AuthMiddleware;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    // ------------------------------------------------------------------
    // POST auth/update-profile
    // ------------------------------------------------------------------
    public function updateProfile(): void
    {
        $payload = AuthMiddleware::require();
        $data    = $this->getJsonBody();

        $name = trim($data['name'] ?? '');

        if (!$name) {
            $this->json(['success' => false, 'message' => 'El nombre es requerido.'], 400);
        }

        // Si UserModel->update actualiza los initiales automáticos, solo le pasamos 'name'
        // Pero nuestra función update no regenera iniciales por sí sola, lo hace el create.
        // Simularemos las iniciales:
        $words  = preg_split('/\s+/', $name);
        $initials = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_strtoupper(mb_substr($word, 0, 1));
        }
        $initials = $initials ?: 'US';

        $ok = $this->userModel->update((int) $payload['sub'], [
            'name' => $name,
            'avatar_initials' => $initials
        ]);

        if (!$ok) {
            $this->json(['success' => false, 'message' => 'No se pudo actualizar el perfil.'], 500);
        }

        $this->json(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
    }

    // ------------------------------------------------------------------
    // POST auth/forgot-password
    // ------------------------------------------------------------------
    public function forgotPassword(): void
    {
        $data  = $this->getJsonBody();
        $email = trim($data['email'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Debe proveer un correo válido.'], 400);
        }

        $user = $this->userModel->findByEmail($email);

        // Prevenir enumeración de correos
        if (!$user) {
            $this->json(['success' => true, 'message' => 'Si el correo existe, se han enviado las instrucciones.']);
        }

        $token = $this->userModel->createPasswordResetToken($email);
        if (!$token) {
            $this->json(['success' => false, 'message' => 'Error al generar el token.'], 500);
        }

        $resetLink = ($_ENV['APP_URL'] ?? 'http://localhost/aiproyectos') . "/reset-password?token=" . $token;

        $sent = $this->sendRecoveryEmail($email, $user['name'], $resetLink);

        if (!$sent) {
            $this->json(['success' => false, 'message' => 'No se pudo enviar el correo de recuperación.'], 500);
        }

        $this->json(['success' => true, 'message' => 'Si el correo existe, se han enviado las instrucciones.']);
    }

    // ------------------------------------------------------------------
    // POST auth/reset-password
    // ------------------------------------------------------------------
    public function resetPassword(): void
    {
        $data    = $this->getJsonBody();
        $token   = $data['token'] ?? '';
        $new     = $data['new_password'] ?? '';
        $confirm = $data['new_password_confirm'] ?? '';

        if (!$token || !$new || !$confirm) {
            $this->json(['success' => false, 'message' => 'Parámetros incompletos.'], 400);
        }

        if (strlen($new) < 8 || $new !== $confirm) {
            $this->json(['success' => false, 'message' => 'Contraseña inválida o no coinciden.'], 400);
        }

        $resetData = $this->userModel->verifyPasswordResetToken($token);

        if (!$resetData) {
            $this->json(['success' => false, 'message' => 'Token inválido o expirado.'], 400);
        }

        $email = $resetData['email'];
        $ok = $this->userModel->updatePasswordByEmail($email, $new);

        if ($ok) {
            $this->userModel->consumePasswordResetToken($token);
            $this->json(['success' => true, 'message' => 'Contraseña restablecida correctamente.']);
        }

        $this->json(['success' => false, 'message' => 'Error al actualizar contraseña.'], 500);
    }

    // ==================================================================
    // Helpers privados
    // ==================================================================

    private function sendRecoveryEmail(string $toEmail, string $toName, string $link): bool
    {
        // SI ESTAMOS EN MODO TEST (Iniciado por Guzzle en TestCase.php) SALTAR ENVÍO REAL
        if (isset($_SERVER['HTTP_X_TESTING_MODE']) && $_SERVER['HTTP_X_TESTING_MODE'] === '1') {
            return true;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'] ?? 'localhost';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'] ?? '';
            $mail->Password   = $_ENV['SMTP_PASS'] ?? '';
            $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? 'tls';
            $mail->Port       = $_ENV['SMTP_PORT'] ?? 2525;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'] ?? 'no-reply@aiproyectos.com', $_ENV['SMTP_FROM_NAME'] ?? 'AI Proyectos');
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de Contraseña';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; background: #210d35; padding: 40px; color: #fff; text-align: center; border-radius: 10px; border: 1px solid #480a9a;'>
                    <h2 style='color: #fff;'>Recuperación de Acceso</h2>
                    <p style='color: #ccc; margin-bottom: 30px;'>Has solicitado restablecer tu contraseña. Haz clic en el botón debajo para elegir una nueva.</p>
                    <a href='{$link}' style='background: linear-gradient(90deg, #ff007f, #00f0ff); padding: 12px 25px; border-radius: 5px; color: #fff; text-decoration: none; font-weight: bold;'>Restablecer Contraseña</a>
                    <p style='color: #777; margin-top: 30px; font-size: 12px;'>Si no solicitaste esto, puedes ignorar este correo de forma segura.</p>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("No se pudo enviar correo: {$mail->ErrorInfo}");
            return false;
        }
    }

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