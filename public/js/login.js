const loginF = document.querySelector("form");

// Configuración bloqueo login por intentos fallidos
const loginBtn = document.querySelector(".login-btn");
let intentosFallidos = parseInt(localStorage.getItem("intentosFallidos")) || 0;
const maxIntentos = 5;
const bloqueoTiempo = 120; //Segundos

// Verificación de bloqueo activo
window.addEventListener("load", verificarBloqueo);

function verificarBloqueo() {
  const bloqueoHasta = parseInt(localStorage.getItem("bloqueoLoginHasta"));
  const ahora = Date.now();
  if (bloqueoHasta && bloqueoHasta > ahora) {
    loginBtn.disabled = true;
    actualizarMensajeBloqueo();
  } else {
    localStorage.removeItem("bloqueoLoginHasta");
    loginBtn.disabled = false;
    intentosFallidos = 0;
    localStorage.setItem("intentosFallidos", "0");
  }
}

// Timer bloqueo
function actualizarMensajeBloqueo() {
  const bloqueoHasta = parseInt(localStorage.getItem("bloqueoLoginHasta"));
  const ahora = Date.now();
  if (bloqueoHasta > ahora) {
    const segundosRestantes = Math.ceil((bloqueoHasta - ahora) / 1000);
    loginBtn.textContent = `Bloqueado (${segundosRestantes}s)`;
    setTimeout(actualizarMensajeBloqueo, 1000);
  } else {
    loginBtn.disabled = false;
    loginBtn.textContent = "Iniciar sesión";
    localStorage.removeItem("bloqueoLoginHasta");
    localStorage.setItem("intentosFallidos", "0");
    intentosFallidos = 0;
  }
}

function mostrarMensajeLogin(mensaje) {
  window.showToast("Atención", mensaje, "info");
}

// Función de ayuda para verificar roles
function hasRole(roleName) {
  const storedRoles = localStorage.getItem("roles");
  if (!storedRoles) {
    return false;
  }
  try {
    const roles = JSON.parse(storedRoles);
    return roles.some(role => role.nombre_rol === roleName);
  } catch (e) {
    console.error("Error parsing roles from localStorage:", e);
    return false;
  }
}

loginF.addEventListener("submit", async (event) => {
  event.preventDefault();
  const username = document.querySelector("#username").value;
  const password = document.querySelector("#password").value;
  const mantenerSesion = document.querySelector("#mantenerSesion").checked;

  if (!username || !password) {
    window.showToast("Datos incompletos", "Por favor ingresa ambos campos: usuario y contraseña.", 'error');
    return;
  }

  loginBtn.disabled = true;
  const originalText = loginBtn.textContent;
  loginBtn.textContent = "Accediendo...";

  try {
    const response = await fetch(CONFIG.API_BASE + 'auth/login', {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          email: username,
          password: password,
        }),
      });

    const result = await response.json();
    console.log("Respuesta del servidor:", result);

      if (result.success === true) {
      // Reset de intentos fallidos
      intentosFallidos = 0;
      localStorage.setItem("intentosFallidos", "0");
      localStorage.removeItem("bloqueoLoginHasta");

      // Guardado estandarizado (siempre guardamos en localStorage para evitar lios, sessionStorage se puede implementar después si es necesario)
      localStorage.setItem("userLoggedIn", "true");
      
      if (result.token) {
        localStorage.setItem("token", result.token);
      }
      if (result.user) {
        localStorage.setItem("userId", result.user.id);
        localStorage.setItem("userEmail", result.user.email);
        localStorage.setItem("userName", result.user.name || result.user.nombre);
      }

      window.showToast("¡Login exitoso!", "Redirigiendo a tu espacio de trabajo...", 'success');
      setTimeout(() => {
        window.location.href = "project-list";
      }, 1500);
      
    } else {
      window.showToast("Error de acceso", result.message || "Usuario o contraseña incorrectos.", 'error');

      // Agrega intento fallido y si es igual o supera los intentos empieza el timer
      intentosFallidos++;
      localStorage.setItem("intentosFallidos", intentosFallidos);
      if (intentosFallidos >= maxIntentos) {
        const bloqueoHasta = Date.now() + bloqueoTiempo * 1000;
        localStorage.setItem("bloqueoLoginHasta", bloqueoHasta);
        loginBtn.disabled = true;
        actualizarMensajeBloqueo();
      }
    }
  } catch (error) {
    console.error("Error completo:", error);
    window.showToast("Error de servidor", "Ha ocurrido un problema de conexión. Inténtalo más tarde.", 'error');
  } finally {
    if (!loginBtn.disabled || loginBtn.textContent === "Accediendo...") {
      loginBtn.disabled = false;
      loginBtn.textContent = originalText;
    }
  }
});

// La antigua función local showNotification fue reemplazada por window.showToast()