document.addEventListener("DOMContentLoaded", function () {
    const forgotPasswordForm = document.getElementById("forgotPasswordForm");
  
    if (forgotPasswordForm) {
    forgotPasswordForm.addEventListener("submit", function (event) {
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

      // Muteo intencional: Simulamos envío pero advertimos que aún no existe endpoint
      setTimeout(() => {
        if(window.showToast) {
            window.showToast(
                "Función en Desarrollo", 
                "Tu API aún no soporta recuperación de contraseñas. Construiremos este endpoint pronto.", 
                "info"
            );
        }
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }, 1500);
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