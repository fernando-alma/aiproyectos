document.addEventListener("DOMContentLoaded", async function() {
    if (!window.isAuthenticated()) {
        window.location.href = 'login';
        return;
    }

    const { userName } = window.getUserData();
    const profileNameInput = document.getElementById("profileName");
    if (profileNameInput) profileNameInput.value = userName || '';

    // Intentamos traer datos frescos de la base de datos
    try {
        const response = await window.authFetch(window.CONFIG.API_BASE + 'auth/me');
        const result = await response.json();
        if (result.success && result.user) {
            profileNameInput.value = result.user.name;
        }
    } catch (e) {
        console.warn("No se pudo refrescar el perfil por red", e);
    }

    // Config form
    const form = document.getElementById("profileForm");
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const currentPassword = document.getElementById("currentPassword").value;
            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            
            const saveBtn = document.getElementById("saveProfileBtn");

            // Si intenta cambiar la contraseña
            if (currentPassword || newPassword || confirmPassword) {
                if (!currentPassword || !newPassword || !confirmPassword) {
                    if(window.showToast) window.showToast("Atención", "Para cambiar tu contraseña debes llenar los tres campos correspondientes.", "warning");
                    return;
                }
                
                if (newPassword.length < 8) {
                    if(window.showToast) window.showToast("Atención", "La nueva contraseña debe tener al menos 8 caracteres.", "warning");
                    return;
                }

                if (newPassword !== confirmPassword) {
                    if(window.showToast) window.showToast("Error", "Las nuevas contraseñas no coinciden.", "error");
                    return;
                }

                const originalBtnText = saveBtn.textContent;
                saveBtn.disabled = true;
                saveBtn.textContent = "Guardando...";

                try {
                    const response = await window.authFetch(window.CONFIG.API_BASE + 'auth/change-password', {
                        method: 'POST',
                        body: JSON.stringify({
                            current_password: currentPassword,
                            new_password: newPassword,
                            new_password_confirm: confirmPassword
                        })
                    });
                    const result = await response.json();

                    if (result.success) {
                        if(window.showToast) window.showToast("¡Contraseña Actualizada!", "Tu contraseña se ha cambiado correctamente.", "success");
                        // Resetear campos de password
                        document.getElementById("currentPassword").value = "";
                        document.getElementById("newPassword").value = "";
                        document.getElementById("confirmPassword").value = "";
                    } else {
                        if(window.showToast) window.showToast("Error", result.message || "Error al cambiar la contraseña.", "error");
                    }
                } catch (error) {
                    if(window.showToast) window.showToast("Error", "Error de conexión con el servidor.", "error");
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalBtnText;
                }

            } else {
                // No llenó contraseñas, solo quiere guardar el "Nombre"
                if(window.showToast) window.showToast("Función en Desarrollo", "La actualización de nombre y avatar estará disponible en la próxima versión de la API.", "info");
            }
        });
    }

    // Botón Logout directo sin modal
    const logoutButton = document.getElementById("logoutButton");
    
    if (logoutButton) {
        logoutButton.addEventListener("click", () => {
            if(window.logout) {
                window.logout(); // Esto ya avisa al endpoint y redirige al login internamente
            } else {
                localStorage.clear();
                sessionStorage.clear();
                window.location.href = 'login';
            }
        });
    }
});