<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIWKND - Acceder</title>
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/login.css">
    <link rel="stylesheet" href="public/css/footer.css"> 
</head>
<body>
    <?php require_once("components/nav.php"); ?>
    <div class="container">

        
        <main class="main-content">
            <form class="login-form solid-dark" id="loginForm">
                <h1 class="page-title" style="text-align: center;">Acceder</h1>
                <div class="form-group">
                    <label for="username">Dirección de email</label>
                    <input type="email" id="username" name="username" class="form-input" placeholder="ejemplo@aiweekend.com" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                </div>
                
                <div class="form-group-check">
                    <label class="checkbox-container">
                        <p type="checkbox" id="mantenerSesion" name="mantenerSesion">
                        <span class="checkbox-label"></span>
                    </label>
                </div>
                
                <div class="forgot-password">
                    <span class="forgot-text">¿Olvidaste tu contraseña?</span>
                    <a href="recover-password" class="forgot-link">Recupera tu contraseña aquí</a>
                </div>
                
                <button type="submit" class="login-btn">Acceder</button>
                
                <div class="register-link">
                    <span class="register-text">¿No tienes cuenta?</span>
                    <a href="register" class="register-link-btn">Regístrate aquí</a>
                </div>
            </form>
        </main>
        <?php require_once ("components/footer.php"); ?>
    </div>
    
    <script src="public/js/login.js"></script>
</body>
</html>