<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIWKND - Detalle de Proyecto</title>
    <meta name="description" content="Vista detallada del proyecto en AIWKND — Plataforma de colaboración en proyectos de IA.">
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/project-detail.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>

<body>
    <?php require_once("components/nav.php"); ?>

    <div class="pd-wrapper">
        <!-- Back Button -->
        <a href="project-list" class="pd-back-btn" id="backBtn" aria-label="Volver a la lista de proyectos">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 19-7-7 7-7" />
                <path d="M19 12H5" />
            </svg>
            <span>Proyectos</span>
        </a>

        <!-- Main Layout: Content + Sidebar -->
        <div class="pd-layout">

            <!-- Main Content Area -->
            <main class="pd-main" id="projectDetailContent">
                <!-- Loading Skeleton -->
                <div class="pd-loading" id="pdLoading">
                    <div class="pd-skeleton pd-skeleton-img"></div>
                    <div class="pd-skeleton pd-skeleton-title"></div>
                    <div class="pd-skeleton pd-skeleton-text"></div>
                    <div class="pd-skeleton pd-skeleton-text short"></div>
                </div>
            </main>

            <!-- Owner Sidebar (hidden by default, shown via JS) -->
            <aside id="ownerSidebar" class="pd-sidebar glass-effect" style="display: none;" aria-label="Panel de control del proyecto">
                <div class="pd-sidebar-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <h3>Centro de Control</h3>
                </div>

                <!-- Edit Button -->
                <a href="#" id="editProjectBtn" class="pd-sidebar-edit-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z" />
                        <path d="m15 5 4 4" />
                    </svg>
                    Editar Proyecto
                </a>

                <!-- Join Requests Panel -->
                <div class="pd-sidebar-panel" id="requestsPanel">
                    <h4 class="pd-sidebar-panel-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <line x1="19" x2="19" y1="8" y2="14" />
                            <line x1="22" x2="16" y1="11" y2="11" />
                        </svg>
                        Solicitudes Pendientes
                        <span class="pd-badge-count" id="requestsCount" style="display:none;">0</span>
                    </h4>
                    <div id="requestsList" class="pd-sidebar-list">
                        <p class="pd-sidebar-empty">Cargando...</p>
                    </div>
                </div>

                <!-- Members Panel -->
                <div class="pd-sidebar-panel" id="membersPanel">
                    <h4 class="pd-sidebar-panel-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                        Miembros del Equipo
                    </h4>
                    <div id="sidebarMembersList" class="pd-sidebar-list">
                        <p class="pd-sidebar-empty">Cargando...</p>
                    </div>
                </div>

                <!-- Delete Button -->
                <button id="deleteProjectBtn" class="pd-sidebar-delete-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18" />
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                        <line x1="10" x2="10" y1="11" y2="17" />
                        <line x1="14" x2="14" y1="11" y2="17" />
                    </svg>
                    Eliminar Proyecto
                </button>
            </aside>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="pd-modal-overlay" style="display: none;">
        <div class="pd-modal glass-effect" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
            <div class="pd-modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                </svg>
            </div>
            <h3 id="deleteModalTitle" class="pd-modal-title">¿Estás seguro?</h3>
            <p class="pd-modal-message">Se eliminará permanentemente el proyecto y todos sus datos. Esta acción no se puede deshacer.</p>
            <div class="pd-modal-buttons">
                <button id="deleteCancelBtn" class="pd-modal-btn pd-modal-btn-cancel">Cancelar</button>
                <button id="deleteConfirmBtn" class="pd-modal-btn pd-modal-btn-danger">Eliminar</button>
            </div>
        </div>
    </div>

    <!-- Leave Confirmation Floating Modal -->
    <div id="leaveConfirmModal" class="pd-leave-confirm" style="display: none;">
        <div class="pd-leave-confirm-content glass-effect">
            <p>¿Estás seguro de abandonar este proyecto?</p>
            <div class="pd-leave-confirm-buttons">
                <button id="leaveCancelBtn" class="pd-leave-btn pd-leave-btn-cancel">Cancelar</button>
                <button id="leaveConfirmBtn" class="pd-leave-btn pd-leave-btn-confirm">Sí, abandonar</button>
            </div>
        </div>
    </div>

    <?php require_once("components/footer.php"); ?>
</body>

<script src="public/js/config.js"></script>
<script src="public/js/session-check.js"></script>
<script src="public/js/project-detail.js"></script>

</html>