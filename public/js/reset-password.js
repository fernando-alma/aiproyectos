document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    
    if (!token) {
        if(window.showToast) window.showToast("Error", "Enlace de recuperación inválido o inexistente.", "error");
        setTimeout(() => {
            window.location.href = 'login';
        }, 3000);
        return;
    }

    const tokenInput = document.getElementById('resetToken');
    if (tokenInput) tokenInput.value = token;

    const form = document.getElementById('resetPasswordForm');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!newPassword || !confirmPassword) {
                if(window.showToast) window.showToast("Advertencia", "Llena ambos campos de contraseña.", "warning");
                return;
            }

            if (newPassword.length < 8) {
                if(window.showToast) window.showToast("Advertencia", "La contraseña debe tener al menos 8 caracteres.", "warning");
                return;
            }

            if (newPassword !== confirmPassword) {
                if(window.showToast) window.showToast("Error", "Las contraseñas no coinciden.", "error");
                return;
            }

            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = "Guardando...";

            try {
                // Public endpoint, so no need for authFetch (no JWT yet)
                const res = await fetch(window.CONFIG.API_BASE + 'auth/reset-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        token: token,
                        new_password: newPassword,
                        new_password_confirm: confirmPassword
                    })
                });

                const data = await res.json();

                if (data.success) {
                    if(window.showToast) window.showToast("¡Éxito!", "Tu contraseña ha sido restablecida. Redirigiendo...", "success");
                    setTimeout(() => {
                        window.location.href = 'login';
                    }, 2000);
                } else {
                    if(window.showToast) window.showToast("Error", data.message || "No se pudo restablecer la contraseña.", "error");
                    btn.disabled = false;
                    btn.textContent = originalText;
                }

            } catch (err) {
                console.error(err);
                if(window.showToast) window.showToast("Error", "Error de red al contactar al servidor.", "error");
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });
    }
});
