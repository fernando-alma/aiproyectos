document.addEventListener("DOMContentLoaded", function () {
    const forgotPasswordForm = document.getElementById("forgotPasswordForm");
  
    if (forgotPasswordForm) {
    forgotPasswordForm.addEventListener("submit", async function (event) {
      event.preventDefault();

      const email = document.getElementById("email").value;

      if (!email) {
        if(window.showToast) window.showToast("Atención", "Por favor ingresa tu correo electrónico", "warning");
        return;
      }

      const submitButton = forgotPasswordForm.querySelector('button[type="submit"]');
      const originalButtonText = submitButton.textContent;
      submitButton.disabled = true;
      submitButton.textContent = "Procesando...";

      try {
        const response = await fetch(window.CONFIG.API_BASE + 'auth/forgot-password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email })
        });
        
        const data = await response.json();

        if (data.success) {
            if(window.showToast) window.showToast("Correo Enviado", data.message || "Hemos enviado las instrucciones a tu correo.", "success");
            // Limpiar email por seguridad
            document.getElementById("email").value = "";
        } else {
            if(window.showToast) window.showToast("Error", data.message || "No se pudo procesar tu solicitud.", "error");
        }
      } catch (err) {
        console.error("Error al recuperar contraseña", err);
        if(window.showToast) window.showToast("Error de red", "Verifica tu conexión y vuelve a intentarlo.", "error");
      } finally {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
    });
  }

    // Botón cancelar/volver
    const cancelButton = document.getElementById("cancelButton");
    if (cancelButton) {
      cancelButton.addEventListener("click", () => {
        window.location.href = "login";
      });
    }
  });