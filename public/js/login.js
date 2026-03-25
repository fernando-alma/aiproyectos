const loginF = document.querySelector("form");

// Configuración bloqueo login por intentos fallidos
const loginBtn = document.querySelector(".login-button");
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
  const mensajeDiv = document.getElementById("mensajeLogin");
  if (mensajeDiv) {
    mensajeDiv.textContent = mensaje;
    mensajeDiv.style.display = "block";
    // Oculta el mensaje 
    setTimeout(() => {
      mensajeDiv.style.display = "none";
    }, 1500);
  }
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
    showNotification("Por favor ingresa ambos campos: usuario y contraseña.", 'error');
    return;
  }

  try {
    // Antes de subir a producción https://systemauth.alphadocere.cl/login.php o a https://test-systemauth.alphadocere.cl/login.php
    const response = await fetch('https://test-systemauth.alphadocere.cl/login.php', {  // endpoint local
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
      console.log("Login exitoso");

      // Almacenar información del login
      if (mantenerSesion) {
        localStorage.setItem("userLoggedIn", "true");
        localStorage.setItem("username", username);
        localStorage.setItem("sessionPermanent", "true");
        
        // Almacenar datos adicionales del usuario si están disponibles
        if (result.token) {
          localStorage.setItem("token", result.token);
        }
        if (result.user) {
          localStorage.setItem("userId", result.user.id);
          localStorage.setItem("userEmail", result.user.email);
          localStorage.setItem("userName", result.user.nombre);
          localStorage.setItem("userCiudad", result.user.ciudad);
        }
        // Guardar los roles asociados al usuario si están disponibles
        if (Array.isArray(result.roles)) {
          localStorage.setItem("roles", JSON.stringify(result.roles));
        }
      } else {
        sessionStorage.setItem("userLoggedIn", "true");
        sessionStorage.setItem("username", username);
        sessionStorage.setItem("sessionPermanent", "false");
        
        // También guardamos en localStorage como respaldo
        localStorage.setItem("userLoggedIn", "true");
        localStorage.setItem("username", username);
        localStorage.setItem("sessionPermanent", "false");
        
        // Almacenar datos adicionales del usuario si están disponibles
        if (result.token) {
          sessionStorage.setItem("token", result.token);
          localStorage.setItem("token", result.token);
        }
        if (result.user) {
          sessionStorage.setItem("userId", result.user.id);
          localStorage.setItem("userId", result.user.id);
          sessionStorage.setItem("userEmail", result.user.email);
          localStorage.setItem("userEmail", result.user.email);
          sessionStorage.setItem("userName", result.user.nombre);
          localStorage.setItem("userName", result.user.nombre);
          sessionStorage.setItem("userCiudad", result.user.ciudad);
          localStorage.setItem("userCiudad", result.user.ciudad);
        }
        // Guardar los roles asociados al usuario si están disponibles
        if (Array.isArray(result.roles)) {
          sessionStorage.setItem("roles", JSON.stringify(result.roles));
          localStorage.setItem("roles", JSON.stringify(result.roles));
        }
      }

      showNotification("¡Inicio de sesión exitoso!", 'success');
      setTimeout(() => {
        window.location.href = "profile";
      }, 1500);
      
    } else {
      showNotification("Usuario o contraseña incorrectos.", 'error');

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
    showNotification("Haz intentado demasiadas veces. Inténtalo de nuevo mas tarde.", 'error');
  }
});

// Ver/Ocultar contraseña
const togglePassword = document.querySelector("#togglePassword");
const password = document.querySelector("#password");

togglePassword.addEventListener("click", () => {
  const esPassword = password.getAttribute("type") === "password";
  password.setAttribute("type", esPassword ? "text" : "password");

  togglePassword.classList.toggle("bi-eye");
  togglePassword.classList.toggle("bi-eye-slash");
});

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Auto-dismiss after 4 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutNotification 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }, 4000);
}