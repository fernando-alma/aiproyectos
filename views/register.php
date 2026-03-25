<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIWKND - Registrarse</title>
    <link rel="stylesheet" href="public/css/register.css">
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
        
        <!-- Form Section -->
        <section class="form-section">
            <div class="form-container">
                <h1 class="form-title">Crear Cuenta</h1>
                <form class="capitals-form" id="register-form">
                    <div class="form-group">
                        <label for="email"></label>
                        <input type="email" id="email" name="email" placeholder="Direccion de Email" required>
                    </div>
                    <div class="form-group">
                        <label for="password"></label>
                        <input type="password" id="password" name="password" placeholder="Contraseña" min="1" maxlength="40" required>
                    </div>
                    <div class="form-group">
                        <label for="password2"></label>
                        <input type="password" id="password2" name="password2" placeholder="Confirmar contraseña" min="1" maxlength="40" required>
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
        <?php require_once("components/nav.php"); ?>
        <?php require_once ("components/footer.php"); ?>
        
    </div>
</body>
</html>