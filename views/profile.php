<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | AIWKND</title>
    <meta name="description" content="Gestiona tu perfil, proyectos y solicitudes en AIWKND.">
    <link rel="stylesheet" href="public/css/nav-styles.css?v=4.5">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/profile.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>

<body>
    <?php require_once("components/nav.php"); ?>

    <main class="prof-page">
        <div class="prof-shell">

            <!-- ═══ Header ═══ -->
            <div class="prof-header">
                <h1 class="prof-title">Mi Perfil</h1>
                <p class="prof-subtitle">Gestiona tu información, proyectos y solicitudes</p>
            </div>

            <!-- ═══ Section 1: Mis Datos ═══ -->
            <section class="prof-card glass-effect" id="sectionDatos">
                <div class="prof-card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <span>Mis Datos</span>
                </div>

                <div class="prof-datos-grid">
                    <!-- Left: avatar + name -->
                    <div class="prof-datos-left">
                        <div class="prof-avatar" id="profileAvatar">
                            <span id="profileInitials">?</span>
                        </div>
                        <div class="prof-group">
                            <label class="prof-label" for="profileName">Nombre</label>
                            <small class="prof-hint">Tu nombre visible en la plataforma</small>
                            <input type="text" id="profileName" class="prof-input" value="Cargando...">
                        </div>
                        <div class="prof-group">
                            <label class="prof-label">Email</label>
                            <small class="prof-hint">Tu dirección de correo (no editable)</small>
                            <input type="email" id="profileEmail" class="prof-input" readonly disabled>
                        </div>
                        <button type="button" class="prof-btn prof-btn--save" id="saveNameBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            Guardar Nombre
                        </button>
                    </div>

                    <!-- Right: change password -->
                    <div class="prof-datos-right">
                        <h3 class="prof-section-title">Cambiar Contraseña</h3>
                        <form id="changePasswordForm">
                            <div class="prof-group">
                                <label class="prof-label" for="currentPassword">Contraseña Actual</label>
                                <small class="prof-hint">Ingresa tu contraseña actual</small>
                                <input type="password" id="currentPassword" class="prof-input"
                                    placeholder="••••••••">
                            </div>
                            <div class="prof-group">
                                <label class="prof-label" for="newPassword">Nueva Contraseña</label>
                                <small class="prof-hint">Mínimo 8 caracteres</small>
                                <input type="password" id="newPassword" class="prof-input"
                                    placeholder="Mínimo 8 caracteres">
                            </div>
                            <div class="prof-group">
                                <label class="prof-label" for="confirmPassword">Confirmar</label>
                                <small class="prof-hint">Repite la nueva contraseña</small>
                                <input type="password" id="confirmPassword" class="prof-input"
                                    placeholder="Repite la contraseña">
                            </div>
                            <button type="submit" class="prof-btn prof-btn--primary" id="savePasswordBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                Cambiar Contraseña
                            </button>
                        </form>
                    </div>
                </div>
            </section>

            <!-- ═══ Create Project CTA ═══ -->
            <div id="createProjectCta"></div>

            <!-- ═══ Section 2: Mis Proyectos ═══ -->
            <section class="prof-card glass-effect" id="sectionProyectos">
                <div class="prof-card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4" />
                        <path d="M9 18c-4.51 2-5-2-7-2" />
                    </svg>
                    <span>Mis Proyectos</span>
                </div>
                <div id="myProjectsList" class="prof-projects-list">
                    <div class="prof-loading">Cargando proyectos...</div>
                </div>
            </section>

            <!-- ═══ Section 3: Mis Solicitudes ═══ -->
            <section class="prof-card glass-effect" id="sectionSolicitudes">
                <div class="prof-card-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <line x1="19" x2="19" y1="8" y2="14" />
                        <line x1="22" x2="16" y1="11" y2="11" />
                    </svg>
                    <span>Mis Solicitudes</span>
                </div>
                <div id="myRequestsList" class="prof-requests-list">
                    <div class="prof-loading">Cargando solicitudes...</div>
                </div>
            </section>

            <!-- ═══ Logout ═══ -->
            <div class="prof-logout-zone">
                <button type="button" class="prof-btn prof-btn--logout" id="logoutButton">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" x2="9" y1="12" y2="12" />
                    </svg>
                    Cerrar Sesión
                </button>
            </div>

        </div>
    </main>

    <?php require_once("components/footer.php"); ?>
    <script src="public/js/config.js"></script>
    <script src="public/js/session-check.js"></script>
    <script src="public/js/profile.js"></script>
</body>

</html>