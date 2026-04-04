<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIWKND — Centro de Control Admin</title>
    <meta name="description" content="Panel de administración del sistema AIWKND. Gestión de usuarios, hackathons y métricas.">
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/admin.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>

<body>
    <?php require_once("components/nav.php"); ?>

    <div class="adm-wrapper">

        <!-- Sidebar Navigation -->
        <aside class="adm-sidebar glass-effect" id="adminSidebar">
            <div class="adm-sidebar-brand">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
                <span>Modo Dios</span>
            </div>

            <nav class="adm-sidebar-nav">
                <button class="adm-nav-btn active" data-tab="radar" id="tabRadar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19.07 4.93A10 10 0 0 0 6.99 3.34" />
                        <path d="M4 6h.01" />
                        <path d="M2.29 9.62A10 10 0 1 0 21.31 8.35" />
                        <path d="M16.24 7.76A6 6 0 1 0 8.23 16.67" />
                        <path d="M12 18h.01" />
                        <path d="M17.99 11.66A6 6 0 0 1 15.77 16.67" />
                        <circle cx="12" cy="12" r="2" />
                        <path d="m13.41 10.59 5.66-5.66" />
                    </svg>
                    <span>El Radar</span>
                </button>
                <button class="adm-nav-btn" data-tab="dashboards" id="tabDashboards">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="7" height="7" x="3" y="3" rx="1" />
                        <rect width="7" height="7" x="14" y="3" rx="1" />
                        <rect width="7" height="7" x="14" y="14" rx="1" />
                        <rect width="7" height="7" x="3" y="14" rx="1" />
                    </svg>
                    <span>Hackathons</span>
                </button>
                <button class="adm-nav-btn" data-tab="users" id="tabUsers">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    <span>El Tribunal</span>
                </button>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="adm-main" id="adminMainContent">
            <div class="adm-loading" id="admLoading">
                <div class="adm-spinner"></div>
                <p>Verificando privilegios...</p>
            </div>
        </main>
    </div>

    <!-- Create Dashboard Modal -->
    <div id="createDashModal" class="adm-modal-overlay" style="display: none;">
        <div class="adm-modal glass-effect" role="dialog" aria-modal="true">
            <div class="adm-modal-header">
                <h3>Crear Nuevo Hackathon</h3>
                <button class="adm-modal-close" id="closeCreateDashModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>
            <form id="createDashForm">
                <div class="adm-form-group">
                    <label for="dashTitle">Título del Hackathon</label>
                    <input type="text" id="dashTitle" placeholder="Ej: AIWKND San Rafael 2026" required>
                </div>
                <div class="adm-form-group">
                    <label for="dashDescription">Descripción</label>
                    <textarea id="dashDescription" rows="3" placeholder="Descripción breve del evento..." required></textarea>
                </div>
                <div class="adm-form-group">
                    <label for="dashColor">Color del tema</label>
                    <select id="dashColor">
                        <option value="purple">Púrpura</option>
                        <option value="blue">Azul</option>
                        <option value="green">Verde</option>
                        <option value="pink">Rosa</option>
                        <option value="orange">Naranja</option>
                        <option value="red">Rojo</option>
                        <option value="indigo">Índigo</option>
                        <option value="yellow">Amarillo</option>
                    </select>
                </div>
                <button type="submit" class="adm-btn-primary" id="submitCreateDash">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14" />
                        <path d="M12 5v14" />
                    </svg>
                    Crear Hackathon
                </button>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="adm-modal-overlay" style="display: none;">
        <div class="adm-modal glass-effect adm-modal-sm" role="dialog" aria-modal="true">
            <div class="adm-modal-icon-warn">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                </svg>
            </div>
            <h3 class="adm-modal-title" id="deleteConfirmTitle">¿Eliminar este hackathon?</h3>
            <p class="adm-modal-message" id="deleteConfirmMessage">Esta acción eliminará todos los proyectos, tareas y miembros asociados.</p>
            <div class="adm-modal-buttons">
                <button class="adm-modal-btn adm-modal-btn-cancel" id="deleteCancelBtn">Cancelar</button>
                <button class="adm-modal-btn adm-modal-btn-danger" id="deleteConfirmBtn">Eliminar</button>
            </div>
        </div>
    </div>

    <?php require_once("components/footer.php"); ?>
</body>

<script src="public/js/config.js"></script>
<script src="public/js/session-check.js"></script>
<script src="public/js/admin.js"></script>

</html>
