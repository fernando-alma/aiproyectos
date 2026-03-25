<nav class="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <a href="http://localhost/aiday-utn-sanrafael-2025/" class="nav-logo">
            <img src="public/images/AIWKND-negro-solo.png" alt="AIWKND Logo" class="logo-img">
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
            <a href="#cronograma" class="bottom-nav-item"><span>Cronograma</span></a>
            <a href="#comunidad" class="bottom-nav-item"><span>Comunidad</span></a>
            <a href="login" class="bottom-nav-item"><span>Cuenta</span></a>

            <a href="register" class="register-button">
                Regístrate gratis
            </a>
        </div>

        <!-- Menú oculto -->
        <div class="menu-toggle-container" id="menu-toggle-container">
            <span class="close-btn" id="close-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            </span>
            <div class="navigation-menu">
                <a href="project-list" class="bottom-nav-item"><span>Proyectos</span></a>
                <a href="#cronograma" class="bottom-nav-item"><span>Cronograma</span></a>
                <a href="#comunidad" class="bottom-nav-item"><span>Comunidad</span></a>
                <a href="login" class="bottom-nav-item"><span>Cuenta</span></a>

                <a href="register" class="register-button">
                    Regístrate gratis
                </a>
            </div>
        </div>
    </div>
</nav>
<script>
    const menuContainer = document.getElementById("menu-toggle-container");
    const menuButton = document.getElementById("menu-btn");
    const closeButton = document.getElementById("close-btn");

    if (menuButton) {
        menuButton.onclick = () => {
            menuContainer.style.display = "flex";
        }
    }

    if (closeButton) {
        closeButton.onclick = () => {
            menuContainer.style.display = "none";
        }
    }

    const links = menuContainer.querySelectorAll("a");
    links.forEach((link) => {
        link.style.textDecoration = "none";
        link.style.color = "#fff";
        link.onclick = () => {
            menuContainer.style.display = "none";
        }
    })
</script>