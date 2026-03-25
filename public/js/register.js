// register.js — Apunta al backend propio (auth/register)
// Ya no depende del sistema externo alphadocere.cl

const form = document.querySelector("form");
const submitBtn = form ? form.querySelector("[type=submit]") : null;

function showNotification(message, type = "success") {
  const existing = document.querySelector(".notification");
  if (existing) existing.remove();

  const n = document.createElement("div");
  n.className = `notification ${type}`;
  n.textContent = message;
  document.body.appendChild(n);

  setTimeout(() => {
    if (n.parentNode) {
      n.style.animation = "slideOutNotification 0.3s ease";
      setTimeout(() => n.parentNode?.removeChild(n), 300);
    }
  }, 4000);
}

// Ver/ocultar contraseña
document.querySelectorAll("[data-toggle-password]").forEach((btn) => {
  const targetId = btn.dataset.togglePassword;
  const input = document.getElementById(targetId);
  if (!input) return;
  btn.addEventListener("click", () => {
    const isPass = input.type === "password";
    input.type = isPass ? "text" : "password";
    btn.classList.toggle("bi-eye", !isPass);
    btn.classList.toggle("bi-eye-slash", isPass);
  });
});

if (form) {
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const name     = document.getElementById("name")?.value.trim()     || "";
    const email    = document.getElementById("email")?.value.trim()    || "";
    const password = document.getElementById("password")?.value        || "";
    const confirm  = document.getElementById("password_confirm")?.value || "";

    // Validaciones cliente
    if (!name || !email || !password) {
      showNotification("Completá todos los campos.", "error");
      return;
    }
    if (password.length < 8) {
      showNotification("La contraseña debe tener al menos 8 caracteres.", "error");
      return;
    }
    if (password !== confirm) {
      showNotification("Las contraseñas no coinciden.", "error");
      return;
    }

    if (submitBtn) submitBtn.disabled = true;

    try {
      const response = await fetch(CONFIG.API_BASE + "auth/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ name, email, password, password_confirm: confirm }),
      });

      const result = await response.json();

      if (result.success) {
        // Guardar token y datos de sesión igual que en login.js
        localStorage.setItem("token",       result.token);
        localStorage.setItem("userLoggedIn","true");
        localStorage.setItem("userId",      result.user.id);
        localStorage.setItem("userEmail",   result.user.email);
        localStorage.setItem("userName",    result.user.name);

        showNotification("¡Cuenta creada! Redirigiendo...", "success");
        setTimeout(() => { window.location.href = "project-list"; }, 1500);
      } else {
        showNotification(result.message || "No se pudo crear la cuenta.", "error");
      }
    } catch (err) {
      console.error("Error en registro:", err);
      showNotification("Error de conexión con el servidor.", "error");
    } finally {
      if (submitBtn) submitBtn.disabled = false;
    }
  });
}
