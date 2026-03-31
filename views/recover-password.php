<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/login.css">
    <link rel="stylesheet" href="public/css/footer.css"> 
</head>
<body>
    <?php require_once("components/nav.php"); ?>
    <div class="container">

        
        <main class="main-content">
            <form class="login-form capitals-form solid-dark" id="forgotPasswordForm">
                <h1 class="page-title" style="text-align: center;">Restablecer contraseña</h1>
                <p style="color: rgba(255, 255, 255, 0.8); text-align: center; margin-bottom: 30px; line-height: 1.5;">
                    Por favor, introduce tu correo electrónico para restablecer tu contraseña.
                </p>
                <div class="form-group">
                    <label for="email">Dirección de email</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="Ingresa tu correo electrónico" required>
                </div>
                
                <button type="submit" class="login-btn" style="margin-top: 20px;">Enviar mensaje de recuperación</button>
                <button type="button" class="login-btn" id="cancelButton" style="background: transparent; border: 1px solid rgba(255,255,255,0.3); margin-top: 10px;">Volver</button>
            </form>
        </main>


        
        <?php require_once ("components/footer.php"); ?>
    </div>
    <script src="public/js/config.js"></script>
    <script src="public/js/session-check.js"></script>
    <script src="public/js/recover-password.js"></script>
</body>
</html>