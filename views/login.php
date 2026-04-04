<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceder | AIWKND</title>
    
    <!-- Estilos Unificados -->
    <link rel="stylesheet" href="public/css/nav-styles.css?v=4.5">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/auth-modern.css">
    <link rel="stylesheet" href="public/css/footer.css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once("components/nav.php"); ?>

    <main class="auth-page">
        <div class="auth-card">
            <h1 class="auth-title">Acceder</h1>
            <p class="auth-subtitle">Ingresa tus credenciales para continuar</p>

            <form id="loginForm" class="auth-grid">
                <div class="form-group">
                    <label for="username">Dirección de email</label>
                    <input type="email" id="username" name="username" class="form-input" placeholder="ejemplo@aiweekend.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                    <a href="recover-password" class="forgot-link">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="auth-submit">Acceder</button>
            </form>

            <div class="auth-footer">
                <span>¿No tienes cuenta?</span>
                <a href="register" class="auth-link">Regístrate aquí</a>
            </div>
        </div>
    </main>

    <?php require_once ("components/footer.php"); ?>
    <script src="public/js/login.js"></script>
</body>
</html>