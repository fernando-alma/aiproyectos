<?php
/**
 * Inyección de variables de entorno al Frontend
 * Esto permite que JS conozca la API_BASE sin hardcodear URLs
 */
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/aiproyectos';
?>
<script>
    // Creamos un objeto global de configuración
    window.CONFIG = {
        APP_URL: "<?php echo rtrim($appUrl, '/'); ?>",
        API_BASE: "<?php echo rtrim($appUrl, '/'); ?>/backend/public/",
        SLUG: 'hola' // Puedes cambiar esto por una variable del .env si lo deseas a futuro
    };
    console.log('Fase 4: Configuración dinámica cargada:', window.CONFIG.API_BASE);
</script>

<!-- Animated Background Injection -->
<link rel="stylesheet" href="public/css/background.css">
<script src="public/js/background.js" defer></script>

<!-- Toast System Injection -->
<link rel="stylesheet" href="public/css/toast.css">
<script src="public/js/toast.js" defer></script>

<div id="animated-background">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<nav class="navbar glass-effect">
    <div class="nav-container">

        <!-- Logo -->
        <a href="<?php echo rtrim($appUrl, '/'); ?>/" class="nav-logo">
            <img src="public/images/aiwknd.png" alt="AIWKND Logo" class="logo-img">
        </a>

        <!-- Botón menú -->
        <span id="menu-btn" class="burger-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-text-align-end-icon lucide-text-align-end">
                <path d="M21 5H3" />
                <path d="M21 12H9" />
                <path d="M21 19H7" />
            </svg>
        </span>

        <!-- Menú -->
        <div class="nav-links">
            <a href="project-list" class="bottom-nav-item"><span>Proyectos</span></a>
            <a href="<?php echo rtrim($appUrl, '/'); ?>/#cronograma" class="bottom-nav-item"><span>Cronograma</span></a>
            <a href="https://www.aiweekend.tech/" target="_blank" class="bottom-nav-item"><span>Comunidad</span></a>
            
            <span id="desktop-auth-links" style="display:flex; gap:24px; align-items:center;">
                <a href="login" class="bottom-nav-item"><span>Cuenta</span></a>
                <a href="register" class="register-button nav-button">Regístrate gratis</a>
            </span>
        </div>

        <!-- Menú oculto -->
        <div class="menu-toggle-container" id="menu-toggle-container">
            <div class="mobile-menu-header">
                <a href="<?php echo rtrim($appUrl, '/'); ?>/" class="nav-logo">
                    <img src="public/images/aiwknd.png" alt="AIWKND Logo" class="logo-img">
                </a>
                <span class="close-btn" id="close-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </span>
            </div>
            <div class="navigation-menu">
                <a href="project-list" class="bottom-nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                    <span>Proyectos</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="arrow-right"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                </a>
                <a href="<?php echo rtrim($appUrl, '/'); ?>/#cronograma" class="bottom-nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <span>Cronograma</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="arrow-right"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                </a>
                <a href="https://www.aiweekend.tech/" target="_blank" class="bottom-nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Comunidad</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="arrow-right"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                </a>
                
                <div id="mobile-auth-links" style="width: 100%; margin-top: 1rem; display: flex; flex-direction: column; gap: 12px;">
                    <!-- Auth Links Dinámicos -->
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Auth Script -->
<script src="public/js/nav-auth.js?v=4.5" defer></script>

<script>
    const menuContainer = document.getElementById("menu-toggle-container");
    const menuButton = document.getElementById("menu-btn");
    const closeButton = document.getElementById("close-btn");

    if (menuButton) {
        menuButton.addEventListener('click', () => {
            menuContainer.classList.add("active");
        });
    }

    if (closeButton) {
        closeButton.addEventListener('click', () => {
            menuContainer.classList.remove("active");
        });
    }

    // Cerramos el menú al clickear un link (usamos event delegation para links dinámicos)
    menuContainer.addEventListener("click", (e) => {
        if (e.target.tagName === 'A' || e.target.closest('a')) {
            menuContainer.classList.remove("active");
        }
    });
</script>