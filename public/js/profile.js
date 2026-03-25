// Profile management
window.addEventListener("load", function() {
    // Verificar autenticación usando las funciones globales
    if (!window.isAuthenticated()) {
        window.location.href = 'login';
        return;
    }

    // Obtener datos del usuario usando las funciones globales
    const userData = window.getUserData();
    const username = userData.userName || userData.username;
    const userEmail = userData.userEmail;

    // Llenar los campos del perfil
    document.getElementById("profileName").value = username || '';
    //document.getElementById("profileEmail").value = userEmail || '';

    // Configurar botón de logout con modal de confirmación
    const logoutButton = document.getElementById("logoutButton");
    const logoutModal = document.getElementById("logoutModal");
    const logoutCancelBtn = document.getElementById("logoutCancelBtn");
    const logoutConfirmBtn = document.getElementById("logoutConfirmBtn");

    if (logoutButton) {
        logoutButton.addEventListener("click", function() {
            // Mostrar modal de confirmación
            logoutModal.style.display = 'flex';
        });
    }

    // Configurar botones del modal
    if (logoutCancelBtn) {
        logoutCancelBtn.addEventListener("click", function() {
            logoutModal.style.display = 'none';
        });
    }

    if (logoutConfirmBtn) {
        logoutConfirmBtn.addEventListener("click", function() {
            // Ejecutar logout usando la función global
            window.logout();
        });
    }

    // Cerrar modal al hacer clic fuera de él
    if (logoutModal) {
        logoutModal.addEventListener("click", function(e) {
            if (e.target === logoutModal) {
                logoutModal.style.display = 'none';
            }
        });
    }

    // Cerrar modal con tecla Escape
    document.addEventListener("keydown", function(e) {
        if (e.key === 'Escape' && logoutModal.style.display === 'flex') {
            logoutModal.style.display = 'none';
        }
    });
});