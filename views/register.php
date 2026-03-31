<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIWKND - Registrarse</title>
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/register.css">
    <link rel="stylesheet" href="public/css/footer.css"> 
</head>
<body>
    <?php require_once("components/nav.php"); ?>
    <div class="container">

        
        <!-- Form Section -->
        <section class="form-section">
            <div class="form-container">
                <form class="capitals-form solid-dark" id="register-form">
                    <h1 class="page-title" style="text-align: center; color: white; margin-bottom: 25px;">Crear Cuenta</h1>
                    <div class="form-group">
                        <label for="name">Nombre completo</label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="Tu nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Dirección de email</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="ejemplo@aiweekend.com" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" min="1" maxlength="40" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirmar contraseña</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="••••••••" min="1" maxlength="40" required>
                    </div>
                    <button type="submit" class="submit-button">Crear Cuenta</button>
                </form>
                <div id="loading-overlay" style="display: none;">
                    <div class="loading-container">
                        <div class="loading-spinner" style="display: none;"></div>
                        <div class="success-icon" style="display: none;">✔</div>
                        <div class="error-icon" style="display: none;">✘</div>
                        <p class="loading-text">Procesando registro...</p>
                        <p class="countdown-text"></p>
                    </div>
                </div>
                <p class="form-link">
                    ¿Ya tienes cuenta? <a href="login">Accede aquí</a>
                </p>
                    <!-- Loading Overlay -->
            </div>
        </section>
        
    
        
        <script src="public/js/register.js"></script>
        <?php require_once ("components/footer.php"); ?>
        
    </div>
</body>
</html>