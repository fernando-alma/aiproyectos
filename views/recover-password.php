<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
    <link rel="stylesheet" href="public/css/recover-password.css">
    <link rel="stylesheet" href="public/css/footer.css"> 
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <img src="public/images/AIWKND-negro-solo.png" alt="AIWKND" class="logo">
                <nav class="desktop-nav">
                    <a href="index" class="nav-link">
                        <span class="nav-icon"></span>
                        <span class="nav-text">Inicio</span>
                    </a>
                    <a href="project-list" class="nav-link">
                        <span class="nav-icon"></span>
                        <span class="nav-text">Proyectos</span>
                    </a>
                </nav>
            </div>
            <nav class="desktop-nav-right">
                <a href="profile" class="nav-link">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Cuenta</span>
                </a>
            </nav>
        </header>
        
        <section class="form-section">
        <div class="form-container">
            <h1 class="form-title">Restablecer contraseña</h1>
            <p class="form-description">
                Por favor, introduce tu correo electrónico para restablecer tu contraseña.
            </p>
            <form class="capitals-form" id="forgotPasswordForm">
                <div class="form-group">
                    <label for="email"></label>
                    <input type="email" id="email" name="email" placeholder="Ingresa tu correo electrónico" required>
                </div>
                <div>
                    <button type="submit" class="submit-button">Enviar formulario</button>
                    <button type="button" class="submit-button" id="cancelButton">Volver</button>
                </div>
            </form>
        </div>
    </section>

        <!-- Desktop Bottom Nav -->
        <nav class="desktop-bottom-nav">
            <img src="public/images/AIWKND-negro-solo.png" alt="AIWKND" class="desktop-bottom-logo">
        </nav>
        
        <?php require_once("components/nav.php"); ?>
        <?php require_once ("components/footer.php"); ?>
    </div>
    <script src="public/js/config.js"></script>
    <script src="public/js/session-check.js"></script>
    <script src="public/js/recover-password.js"></script>
</body>
</html>