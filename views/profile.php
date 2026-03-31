<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/login.css">
    <link rel="stylesheet" href="public/css/account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="public/css/footer.css"> 
</head>
<body>
    <?php require_once("components/nav.php"); ?>


    <style>
        .profile-wide-card {
            max-width: 800px !important;
            width: 100%;
            background: rgba(77, 26, 88, 0.6) !important; /* Medio rosa oscuro transparente */
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 105, 180, 0.2);
            padding: 40px !important;
            margin: 0 auto;
        }
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }
        .profile-input-override {
            width: 100% !important;
            max-width: 100% !important;
        }
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .profile-wide-card {
                padding: 25px !important;
            }
            .profile-actions-row {
                flex-direction: column;
            }
        }
    </style>

    <main class="main-content" style="display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 80px); padding: 20px;">
        <form class="login-form capitals-form solid-dark profile-wide-card" id="profileForm">
            <h1 class="page-title" style="text-align: center; margin-bottom: 30px;">Mi Perfil</h1>
            
            <div class="profile-grid">
                <!-- Columna Izquierda (Avatar y Nombre) -->
                <div>
                    <div style="display: flex; justify-content: center; margin-bottom: 30px;">
                        <img src="public/images/weekeners.png" alt="Avatar" style="width: 120px; height: 120px; border-radius: 50%; border: 3px solid var(--aiw-cyan);">
                    </div>

                    <div class="form-group">
                        <label for="profileName">Nombre del perfil</label>
                        <input type="text" id="profileName" class="form-input profile-input-override" value="Cargando..." readonly>
                    </div>
                </div>

                <!-- Columna Derecha (Cambiar Contraseña) -->
                <div>
                    <h3 style="color: white; text-align: center; font-family: 'Raleway', sans-serif; font-size: 18px; font-weight: 600; margin-bottom: 25px;">
                        Cambiar Contraseña
                    </h3>

                    <div class="form-group">
                        <label for="currentPassword">Contraseña Actual</label>
                        <input type="password" id="currentPassword" name="currentPassword" class="form-input profile-input-override" placeholder="••••••••">
                    </div>

                    <div class="form-group">
                        <label for="newPassword">Nueva Contraseña</label>
                        <input type="password" id="newPassword" name="newPassword" class="form-input profile-input-override" placeholder="Min. 8 caracteres">
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirmar Nueva Contraseña</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" class="form-input profile-input-override" placeholder="Repite la nueva contraseña">
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 30px 0;">

            <!-- Fila de Botones unificada -->
            <div class="profile-actions-row" style="display: flex; gap: 15px; justify-content: center; max-width: 600px; margin: 0 auto;">
                <button type="button" class="login-btn" id="logoutButton" style="background: transparent; border: 1px solid var(--aiw-pink); color: var(--aiw-pink); flex: 1; min-width: 140px; margin: 0;">Cerrar Sesión</button>
                <button type="submit" class="login-btn" style="flex: 1; min-width: 140px; margin: 0;" id="saveProfileBtn">Cambiar contraseña</button>
            </div>
        </form>
    </main>
        
    <?php require_once ("components/footer.php"); ?> 
    
    <script src="public/js/config.js"></script>
    <script src="public/js/session-check.js"></script>
    <script src="public/js/profile.js"></script>
</body>
</html>