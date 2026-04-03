<?php
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$mail = new PHPMailer(true);

try {
    // Activar salida de debug
    $mail->SMTPDebug = 3; 
    $mail->Debugoutput = 'echo';

    $mail->isSMTP();
    $mail->Host       = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['SMTP_USER'];
    $mail->Password   = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
    $mail->Port       = $_ENV['SMTP_PORT'];

    $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
    $mail->addAddress('fg.almadileo@gmail.com', 'Fernando');

    $mail->isHTML(true);
    $mail->Subject = 'Test de Servidor SMTP';
    $mail->Body    = 'Este es un correo de prueba de AI Proyectos.';

    $mail->send();
    echo "¡Mensaje enviado correctamente con PHPMailer!\n";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n";
}
