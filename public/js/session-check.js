// session-check.js — Versión con JWT real en Authorization header
// Reemplaza la validación anterior basada sólo en localStorage
(function () {
  "use strict";

  // ------------------------------------------------------------------
  // Helpers de sesión
  // ------------------------------------------------------------------
  function getToken() {
    return localStorage.getItem("token") || sessionStorage.getItem("token");
  }

  function getUserData() {
    return {
      userId:    localStorage.getItem("userId")    || sessionStorage.getItem("userId"),
      userEmail: localStorage.getItem("userEmail") || sessionStorage.getItem("userEmail"),
      userName:  localStorage.getItem("userName")  || sessionStorage.getItem("userName"),
      token:     getToken(),
    };
  }

  function isUserLoggedIn() {
    return !!getToken() && !!localStorage.getItem("userLoggedIn");
  }

  function clearSession() {
    ["token","userLoggedIn","userId","userEmail","userName","userProjectMemberships"].forEach(k => {
      localStorage.removeItem(k);
      sessionStorage.removeItem(k);
    });
  }

  function redirectToLogin() {
    clearSession();
    window.location.href = "login";
  }

  // ------------------------------------------------------------------
  // Verificación asíncrona contra auth/me (valida el token en el servidor)
  // ------------------------------------------------------------------
  async function verifySessionWithServer() {
    const token = getToken();
    if (!token) return false;

    try {
      const res = await fetch(CONFIG.API_BASE + "auth/me", {
        headers: { Authorization: `Bearer ${token}` },
      });

      if (!res.ok) {
        return false;
      }

      const data = await res.json();

      if (data.success && data.user) {
        // Actualizar datos en caso de que hayan cambiado
        localStorage.setItem("userId",    data.user.id);
        localStorage.setItem("userEmail", data.user.email);
        localStorage.setItem("userName",  data.user.name);
        return true;
      }
      return false;
    } catch {
      // Si el server no responde, confiamos en el token local
      // (evita desloguear al usuario por problemas de red puntuales)
      return !!getToken();
    }
  }

  // ------------------------------------------------------------------
  // Protección de páginas que requieren login
  // ------------------------------------------------------------------
  const PUBLIC_PAGES = [
    "/login", "/register", "/recover-password", "/project-list", "/project-detail", "/",
  ];

  function isPublicPage() {
    const path = window.location.pathname;
    return PUBLIC_PAGES.some((p) => path.includes(p));
  }

  async function checkSession() {
    if (isPublicPage()) return;

    if (!isUserLoggedIn()) {
      redirectToLogin();
      return;
    }

    // Verificación suave: si el token expiró en el servidor, desloguear
    const valid = await verifySessionWithServer();
    if (!valid) {
      redirectToLogin();
    }
  }

  document.addEventListener("DOMContentLoaded", checkSession);
  document.addEventListener("visibilitychange", () => {
    if (!document.hidden) checkSession();
  });

  // ------------------------------------------------------------------
  // API pública global (igual que antes para no romper el JS existente)
  // ------------------------------------------------------------------
  window.isAuthenticated = isUserLoggedIn;
  window.getUserData     = getUserData;

  window.logout = function () {
    // Notificar al servidor (fire-and-forget, no bloqueamos)
    const token = getToken();
    if (token) {
      fetch(CONFIG.API_BASE + "auth/logout", {
        method: "POST",
        headers: { Authorization: `Bearer ${token}` },
      }).catch(() => {});
    }
    clearSession();
    window.location.href = "login";
  };

  // Helper para hacer fetch autenticado desde cualquier JS del proyecto
  window.authFetch = function (url, options = {}) {
    const token = getToken();
    const headers = Object.assign({}, options.headers || {}, {
      Authorization: token ? `Bearer ${token}` : "",
      "Content-Type": options.contentType || "application/json",
    });
    return fetch(url, { ...options, headers });
  };

  window.isProjectMember = function (projectId) {
    const memberships = JSON.parse(localStorage.getItem("userProjectMemberships") || "[]");
    const email = getUserData().userEmail;
    return memberships.some((m) => String(m.projectId) === String(projectId) && m.userEmail === email);
  };

  window.updateProjectMembership = function (projectId, isMember) {
    const { userEmail, userName } = getUserData();
    if (!userEmail) return;
    let memberships = JSON.parse(localStorage.getItem("userProjectMemberships") || "[]");
    if (isMember) {
      if (!memberships.find((m) => m.projectId === projectId && m.userEmail === userEmail)) {
        memberships.push({ projectId, userEmail, userName, role: "member", joinedAt: new Date().toISOString() });
      }
    } else {
      memberships = memberships.filter((m) => !(m.projectId === projectId && m.userEmail === userEmail));
    }
    localStorage.setItem("userProjectMemberships", JSON.stringify(memberships));
  };

  // Modal de login (sin cambios para compatibilidad con project-detail.js)
  window.showLoginModal = function () {
    const existing = document.querySelector(".modal-overlay");
    if (existing) existing.remove();
    const overlay = document.createElement("div");
    overlay.className = "modal-overlay";
    overlay.innerHTML = `
      <div class="modal-content">
        <div style="margin-bottom:1.5rem">
          <h3 class="modal-title">Iniciá sesión</h3>
          <p class="modal-message">Para unirte a este proyecto necesitás una cuenta.</p>
        </div>
        <div class="modal-buttons">
          <button id="modalCancelBtn" class="modal-cancel-btn">Cancelar</button>
          <button id="modalLoginBtn"  class="modal-login-btn">Ir al login</button>
        </div>
      </div>`;
    document.body.appendChild(overlay);
    document.getElementById("modalCancelBtn").addEventListener("click", () => overlay.remove());
    document.getElementById("modalLoginBtn").addEventListener("click",  () => { window.location.href = "login"; });
    overlay.addEventListener("click", (e) => { if (e.target === overlay) overlay.remove(); });
  };
})();
