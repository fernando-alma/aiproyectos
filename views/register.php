<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | AIWKND</title>
    
    <!-- Estilos Unificados -->
    <link rel="stylesheet" href="public/css/nav-styles.css?v=4.5">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/auth-modern.css">
    <link rel="stylesheet" href="public/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once("components/nav.php"); ?>

    <main class="auth-page">
        <div class="auth-card register-wide">
            <h1 class="auth-title">Crear Cuenta</h1>
            <p class="auth-subtitle">Únete a la comunidad de IA más grande de la región</p>

            <form id="register-form" class="auth-grid">
                <!-- Columna Izquierda: Datos Personales -->
                <div class="col-left">
                    <div class="form-group">
                        <label for="name">Nombre completo</label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="Tu nombre" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Dirección de email</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="ejemplo@aiweekend.com" required>
                    </div>
                </div>

                <!-- Columna Derecha: Seguridad -->
                <div class="col-right">
                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirm">Confirmar contraseña</label>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="••••••••" required>
                    </div>
                </div>

                <!-- Botón (Ocupa ambas columnas en PC) -->
                <div style="grid-column: 1 / -1;">
                    <button type="submit" class="auth-submit">Crear Cuenta</button>
                </div>
            </form>

            <!-- Loading Overlay -->
            <div id="loading-overlay" style="display: none;">
                <div class="loading-container" style="text-align: center; margin-top: 2rem; color: #fff;">
                    <p class="loading-text">Procesando registro...</p>
                    <p class="countdown-text"></p>
                </div>
            </div>

            <div class="auth-footer">
                <span>¿Ya tienes cuenta?</span>
                <a href="login" class="auth-link">Accede aquí</a>
            </div>
        </div>
    </main>

    <?php require_once ("components/footer.php"); ?>
    <script src="public/js/register.js"></script>
</body>
</html>