// register.js — Apunta al backend propio (auth/register)
// Ya no depende del sistema externo alphadocere.cl

const form = document.querySelector("form");
const submitBtn = form ? form.querySelector("[type=submit]") : null;

// La antigua función local showNotification fue reemplazada por la global window.showToast()

if (form) {
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const name     = document.getElementById("name")?.value.trim()     || "";
    const email    = document.getElementById("email")?.value.trim()    || "";
    const password = document.getElementById("password")?.value        || "";
    const confirm  = document.getElementById("password_confirm")?.value || "";

    // Validaciones cliente
    if (!name || !email || !password) {
      window.showToast("Faltan datos", "Completa todos los campos obligatorios.", "error");
      return;
    }
    if (password.length < 8) {
      window.showToast("Contraseña débil", "La contraseña debe tener al menos 8 caracteres.", "error");
      return;
    }
    if (password !== confirm) {
      window.showToast("Las contraseñas no coinciden", "Verifica haberla escrito correctamente.", "error");
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Procesando...';
    }

    try {
      const response = await fetch(CONFIG.API_BASE + "auth/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password, password_confirm: confirm }),
      });

      const result = await response.json();

      if (result.success) {
        // Guardar token y datos de sesión
        localStorage.setItem("token",       result.token);
        localStorage.setItem("userLoggedIn","true");
        localStorage.setItem("userId",      result.user.id);
        localStorage.setItem("userEmail",   result.user.email);
        localStorage.setItem("userName",    result.user.name);

        window.showToast("¡Cuenta creada!", "Redirigiendo a tus proyectos...", "success");
        setTimeout(() => { window.location.href = "project-list"; }, 1500);
      } else {
        window.showToast("Error de registro", result.message || "No se pudo crear la cuenta.", "error");
      }
    } catch (err) {
      console.error("Error en registro:", err);
      window.showToast("Error", "Ocurrió un error de conexión con el servidor.", "error");
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Crear Cuenta';
      }
    }
  });
}
