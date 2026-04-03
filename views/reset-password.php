<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña</title>
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/login.css">
    <link rel="stylesheet" href="public/css/footer.css"> 
</head>
<body>
    <?php require_once("components/nav.php"); ?>
    <div class="container">
        <main class="main-content">
            <form class="login-form capitals-form solid-dark" id="resetPasswordForm">
                <h1 class="page-title" style="text-align: center;">Nueva contraseña</h1>
                <p style="color: rgba(255, 255, 255, 0.8); text-align: center; margin-bottom: 30px; line-height: 1.5;">
                    Ingresa tu nueva contraseña para acceder nuevamente a tu cuenta.
                </p>
                
                <!-- Oculto para capturar el token de la URL -->
                <input type="hidden" id="resetToken" name="token">

                <div class="form-group">
                    <label for="new_password">Nueva contraseña</label>
                    <input type="password" id="new_password" name="new_password" class="form-input" placeholder="Mínimo 8 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Repite la nueva contraseña" required>
                </div>
                
                <button type="submit" class="login-btn" style="margin-top: 20px;">Actualizar contraseña</button>
            </form>
        </main>
        
        <?php require_once ("components/footer.php"); ?>
    </div>
    <script src="public/js/config.js"></script>
    <script src="public/js/session-check.js"></script>
    <script src="public/js/reset-password.js"></script>
</body>
</html>
