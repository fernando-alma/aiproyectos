const API_BASE = CONFIG.API_BASE;

// SOLUCIÓN FRONTEND: Función global para verificar si un usuario es creador de un proyecto
window.isProjectCreator = function (projectId, userEmail) {
  const creators = JSON.parse(localStorage.getItem("projectCreators") || "[]");
  return creators.some((c) => c.projectId == projectId && c.email === userEmail);
};

// SOLUCIÓN FRONTEND: Función global para obtener el rol funcional de un usuario
window.getUserFunctionalRole = function (projectId, userEmail, backendRole) {
  if (window.isProjectCreator(projectId, userEmail)) return "owner";
  return backendRole || "member";
};

document.addEventListener("DOMContentLoaded", () => {
  const projectDetailContent = document.getElementById("projectDetailContent");
  const urlParams = new URLSearchParams(window.location.search);
  const projectId = urlParams.get("id");
  let project = null;

  if (projectId) fetchProjectDetails(projectId);
  else projectDetailContent.innerHTML = "<p>No se especificó ningún ID de proyecto.</p>";

  async function fetchProjectDetails(id) {
    try {
      const response = await fetch(`${API_BASE}project/get?id=${id}`);
      if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
      const data = await response.json();

      if (data.success && data.project) {
        project = data.project;
        renderProjectDetails(project);
      } else {
        projectDetailContent.innerHTML = `<p>Error al cargar detalles: ${data.message || "sin detalles"}.</p>`;
      }
    } catch (error) {
      console.error("Error fetching project detail:", error);
      projectDetailContent.innerHTML =
        "<p>No se pudieron cargar los detalles del proyecto. Inténtalo de nuevo más tarde.</p>";
    }
  }

  function truncateTitleForMobile(title, maxLength = 154) {
    const isMobile = window.innerWidth < 768;
    if (isMobile && title && title.length > maxLength) return title.substring(0, maxLength) + "...";
    return title;
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function renderProjectDetails(project) {
    if (!project) return;

    const pitchEmbed = (() => {
      const pitch = (project.pitch || "").trim();
      const videoUrl = (project.link_video || "").trim();
      let embedUrl = "";

      if (pitch) {
        const ytMatch = pitch.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([\w-]{11})/);
        if (ytMatch?.[1]) embedUrl = `https://www.youtube.com/embed/${ytMatch[1]}`;
      } else if (videoUrl) {
        const ytMatch = videoUrl.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([\w-]{11})/);
        if (ytMatch?.[1]) embedUrl = `https://www.youtube.com/embed/${ytMatch[1]}`;
      }

      if (embedUrl) {
        return `
          <div class="project-section">
            <div class="section-title">Pitch</div>
            <div class="section-content" style="margin-top:10px; display:flex; justify-content:center;">
              <iframe width="560" height="315"
                src="${embedUrl}"
                title="Pitch" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen></iframe>
            </div>
          </div>`;
      }

      const safeText = escapeHtml(pitch || videoUrl);
      const isUrl = /^(https?:)\/\//i.test(safeText);
      const linkHtml = isUrl
        ? `<a href="${safeText}" target="_blank" rel="noopener noreferrer" style="color:#fafafa; font-weight:600;">${safeText}</a>`
        : safeText;

      return pitch || videoUrl ? `
        <div class="project-section">
          <div class="section-title">Pitch</div>
          <div class="section-content" style="margin-top:10px; text-align:left;">${linkHtml}</div>
        </div>` : "";
    })();

    const formatProjectDescription = (description) => {
      if (!description) return "";
      const lines = description.split("\n").filter((line) => line.trim());

      let html = "";
      let currentSection = "";
      let bulletPoints = [];
      let isFirstLine = true;
      let hasProcessedInitialBullets = false;

      for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();

        if (isFirstLine) {
          html += `<div class="project-title-numbered">${escapeHtml(line)}</div>`;
          isFirstLine = false;
          continue;
        }

        if (/^[•\-]\s/.test(line) && !hasProcessedInitialBullets) {
          bulletPoints.push(escapeHtml(line.replace(/^[•\-]\s/, "")));
          continue;
        }

        if (bulletPoints.length > 0 && !hasProcessedInitialBullets) {
          html += `<ul class="project-bullet-points">${bulletPoints.map((p) => `<li>${p}</li>`).join("")}</ul>`;
          bulletPoints = [];
          hasProcessedInitialBullets = true;
        }

        if (line.endsWith(":")) {
          currentSection = escapeHtml(line);
          html += `<div class="project-section-title">${currentSection}</div>`;
          continue;
        }

        if (/^[•\-]\s/.test(line)) {
          if (bulletPoints.length === 0) html += `<ul class="project-bullet-points">`;
          bulletPoints.push(escapeHtml(line.replace(/^[•\-]\s/, "")));
          continue;
        }

        if (bulletPoints.length > 0) {
          html += bulletPoints.map((p) => `<li>${p}</li>`).join("") + `</ul>`;
          bulletPoints = [];
        }

        if (currentSection && line) html += `<div class="project-section-content">${escapeHtml(line)}</div>`;
        else if (line) html += `<div class="project-general-text">${escapeHtml(line)}</div>`;
      }

      if (bulletPoints.length > 0) {
        html += bulletPoints.map((p) => `<li>${p}</li>`).join("") + `</ul>`;
      }

      return html;
    };

    const parseMembersData = (membersData) => {
      if (!membersData) return [];
      return membersData.split('\n').map(line => {
        const trimmed = line.trim();
        if (!trimmed) return null;
        const parts = trimmed.split(', ');
        const name = parts[0]?.trim();
        const linkedin = parts[1]?.trim();
        return name && linkedin ? { name: escapeHtml(name), linkedin: escapeHtml(linkedin) } : null;
      }).filter(Boolean);
    };

    const members = parseMembersData(project.members_data);
    
    projectDetailContent.innerHTML = `
      <div class="project-section">
          <div class="section-content">
            ${project.image ? `<img src="backend/public${escapeHtml(project.image)}" alt="Project Image" style="max-width:100%; height:auto; border-radius: 12px;">` : '<img src="public/images/fondos/fondo.jpg" alt="Project Image" style="max-width:100%; height:auto; border-radius: 12px;">'}
          </div>
        </div>

      <h2 class="form-title project-detail-title">${escapeHtml(project.title)}</h2>

      <div class="project-details">
        <div class="project-description-formatted">
          ${formatProjectDescription(project.description)}
        </div>
        ${pitchEmbed}

        <div class="project-section">
          <div class="section-title">Estado</div>
          <div class="section-content">${escapeHtml(project.status || 'N/A')}</div>
        </div>

        <div class="project-section">
          <div class="section-title">Grupo</div>
          <div class="section-content">${escapeHtml(project.group_name || 'N/A')}</div>
        </div>

        <div class="project-section">
          <div class="section-title">Enlace de Despliegue</div>
          <div class="section-content">
            ${project.link_deploy ? `<a href="${escapeHtml(project.link_deploy)}" target="_blank" rel="noopener noreferrer">${escapeHtml(project.link_deploy)}</a>` : 'No disponible'}
          </div>
        </div>

        <div class="project-section">
          <div class="section-title">Creado</div>
          <div class="section-content">${project.created_at ? new Date(project.created_at).toLocaleString('es-ES') : 'N/A'}</div>
        </div>

        <div class="project-section">
          <div class="section-title">Actualizado</div>
          <div class="section-content">${project.updated_at ? new Date(project.updated_at).toLocaleString('es-ES') : 'N/A'}</div>
        </div>
      </div>
    `;

    const joinButton = document.getElementById("joinButton");
    const leaveButton = document.getElementById("leaveButton");
    const editButton = document.getElementById("editButton");
    const deleteButton = document.getElementById("deleteButton");

    async function fetchWithTimeout(url, options, timeout = 10000) {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), timeout);
      try {
        return await fetch(url, { ...options, signal: controller.signal });
      } finally {
        clearTimeout(timeoutId);
      }
    }

    function updateButtonStates(activeButton, inactiveButton, activeText, isDisabled) {
      activeButton.disabled = isDisabled;
      activeButton.textContent = activeText;
      activeButton.style.display = "inline-block";
      inactiveButton.style.display = "none";
    }

    function getUserCredentials() {
      return {
        userName: localStorage.getItem("userName") || "",
        userEmail: localStorage.getItem("userEmail") || "",
        userId: localStorage.getItem("userId") || "",
      };
    }

    function setupProjectButton(button, action, otherButton, apiEndpoint) {
      if (!button) return;

      button.addEventListener("click", async () => {
        if (!window.isAuthenticated || !window.isAuthenticated()) {
          if (typeof window.showLoginModal === "function") window.showLoginModal();
          else document.getElementById("joinStatus").textContent = "No se puede mostrar el modal de inicio de sesión.";
          return;
        }

        const { userName, userEmail } = getUserCredentials();
        const joinStatus = document.getElementById("joinStatus");

        if (!userEmail || !userName) {
          joinStatus.textContent = "Por favor, inicia sesión para unirte a un proyecto.";
          return;
        }

        updateButtonStates(button, otherButton, action === "join" ? "Uniéndose..." : "Abandonando...", true);
        joinStatus.textContent = "";

        const formData = new FormData();
        if (action === "join") {
          formData.append("project_id", project.id);
          formData.append("name", userName);
          formData.append("email", userEmail);
          formData.append("role", "member");
        } else {
          formData.append("project_id", project.id);
          formData.append("email", userEmail);
        }

        try {
          const res = await fetchWithTimeout(`${API_BASE}${apiEndpoint}`, { method: "POST", body: formData });
          const data = await res.json();

          if (!res.ok) throw new Error(data.message || `Error HTTP: ${res.status}`);
          if (!data.success) throw new Error(data.message || "Error desconocido.");

          joinStatus.textContent = action === "join"
            ? "Solicitud enviada / unido correctamente."
            : "Has abandonado el proyecto.";

          updateButtonStates(otherButton, button, action === "join" ? "Abandonar" : "Unirse", false);
          loadProjectMembers(project.id);
        } catch (err) {
          console.error(err);
          joinStatus.textContent =
            err.name === "AbortError" ? "Tiempo de espera agotado. Verifica tu conexión." : (err.message || "Error al procesar.");
        } finally {
          if (button.style.display !== "none") {
            updateButtonStates(button, otherButton, action === "join" ? "Unirse" : "Abandonar", false);
          }
        }
      });
    }

    async function checkMembershipAndSetState() {
      const userEmail = localStorage.getItem("userEmail");
      if (!userEmail || !joinButton) return;

      try {
        const resp = await fetch(`${API_BASE}project/members?id=${project.id}`);
        if (!resp.ok) return;
        const data = await resp.json();

        if (data.success && Array.isArray(data.members)) {
          const already = data.members.some((m) => m.email === userEmail);
          joinButton.style.display = already ? "none" : "inline-block";
          leaveButton.style.display = already ? "inline-block" : "none";

          const me = data.members.find((m) => m.email === userEmail);
          const backendRole = (me?.role ? String(me.role).toLowerCase() : "");
          const functionalRole = window.getUserFunctionalRole(project.id, userEmail, backendRole);
          const isOwner = functionalRole === "owner";

          editButton.style.display = isOwner ? "inline-block" : "none";
          deleteButton.style.display = isOwner ? "inline-block" : "none";
        }
      } catch (_) {}
    }

    checkMembershipAndSetState();
    loadProjectMembers(project.id);

    // Endpoints según tu backend actual
    setupProjectButton(joinButton, "join", leaveButton, "project/createProjectMember");
    setupProjectButton(leaveButton, "leave", joinButton, "member/delete");

    // Delete modal
    const deleteModal = document.getElementById("deleteModal");
    const deleteCancelBtn = document.getElementById("deleteCancelBtn");
    const deleteConfirmBtn = document.getElementById("deleteConfirmBtn");

    if (deleteButton) {
      deleteButton.addEventListener("click", () => {
        if (deleteModal) deleteModal.style.display = "flex";
      });
    }

    if (deleteCancelBtn) {
      deleteCancelBtn.addEventListener("click", () => {
        if (deleteModal) deleteModal.style.display = "none";
      });
    }

    if (deleteConfirmBtn) {
      deleteConfirmBtn.addEventListener("click", async () => {
        try {
          const fd = new FormData();
          fd.append("project_id", project.id);
          fd.append("id", project.id); // compatibilidad
          const res = await fetch(`${API_BASE}project/delete`, { method: "POST", body: fd });
          const data = await res.json();
          if (res.ok && data?.success) {
            showNotification("Proyecto eliminado exitosamente.", "success");
            setTimeout(() => (window.location.href = "project-list"), 1200);
          } else {
            showNotification(data.message || "No se pudo eliminar el proyecto.", "error");
          }
        } catch (err) {
          console.error(err);
          showNotification("Error de conexión al eliminar el proyecto.", "error");
        } finally {
          if (deleteModal) deleteModal.style.display = "none";
        }
      });
    }

    if (deleteModal) {
      deleteModal.addEventListener("click", (e) => {
        if (e.target === deleteModal) deleteModal.style.display = "none";
      });
    }

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && deleteModal?.style.display === "flex") deleteModal.style.display = "none";
    });
  }

  async function loadProjectMembers(projectId) {
    const membersList = document.getElementById("membersList");
    if (!membersList) return;

    try {
      const response = await fetch(`${API_BASE}project/members?id=${projectId}`);
      if (!response.ok) {
        if (response.status === 404) {
          membersList.innerHTML =
            '<p style="color:#a1a1a1; font-style: italic; text-align: center;">La funcionalidad de miembros no está disponible.</p>';
          return;
        }
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      if (data.success && Array.isArray(data.members)) displayProjectMembers(data.members);
      else membersList.innerHTML = '<p style="color:#a1a1a1; text-align:center;">No hay miembros en este proyecto.</p>';
    } catch (error) {
      console.error("Error cargando miembros:", error);
      membersList.innerHTML = '<p style="color:#a1a1a1; text-align:center;">No se pudieron cargar los miembros.</p>';
    }
  }

  function displayProjectMembers(members) {
    const membersList = document.getElementById("membersList");
    if (!membersList) return;

    if (members.length === 0) {
      membersList.innerHTML = '<p style="text-align: center; color:#a1a1a1;">No hay miembros en este proyecto.</p>';
      return;
    }

    const membersHTML = members
      .map((member) => {
        const joinDate = member.joined_at
          ? new Date(member.joined_at).toLocaleDateString("es-ES", { dateStyle: "medium" })
          : "Fecha no disponible";

        const memberName = escapeHtml(member.name || member.user_name || "Usuario");
        const memberEmail = escapeHtml(member.email || "");

        const backendRole = member.role || "Miembro";
        const functionalRole = window.getUserFunctionalRole(project.id, member.email, backendRole);
        const memberRole = functionalRole === "owner" ? "Owner" : escapeHtml(backendRole);

        const initials = memberName
          .split(" ")
          .map((n) => n.charAt(0))
          .join("")
          .toUpperCase()
          .substring(0, 2);

        return `
          <div class="member-avatar-container" onclick="showMemberInfo('${memberName}', '${memberEmail}', '${memberRole}', '${joinDate}')">
            <div class="member-avatar-large">
              <span class="member-initials">${initials}</span>
            </div>
            <div class="member-name-overlay">${memberName}</div>
          </div>`;
      })
      .join("");

    membersList.innerHTML = `<div class="members-avatars-inline">${membersHTML}</div>`;
  }

  window.showMemberInfo = function (name, email, role, joinedDate) {
    let modal = document.getElementById("memberInfoModal");
    if (!modal) {
      modal = document.createElement("div");
      modal.id = "memberInfoModal";
      modal.className = "modal-overlay";
      modal.innerHTML = `
        <div class="modal-content member-info-modal" role="dialog" aria-modal="true">
          <div class="modal-title">Información del miembro</div>
          <div class="modal-body">
            <div class="member-details"></div>
          </div>
          <div class="modal-buttons">
            <button class="modal-cancel-btn" onclick="closeMemberInfo()">Cerrar</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
    }

    modal.querySelector(".member-details").innerHTML = `
      <div class="member-detail-item"><strong>Nombre:</strong> ${name}</div>
      <div class="member-detail-item"><strong>Email:</strong> ${email}</div>
      <div class="member-detail-item"><strong>Rol:</strong> ${role}</div>
      <div class="member-detail-item"><strong>Se unió:</strong> ${joinedDate}</div>
    `;

    modal.style.display = "flex";
  };

  window.closeMemberInfo = function () {
    const modal = document.getElementById("memberInfoModal");
    if (modal) modal.style.display = "none";
  };

  document.addEventListener("click", (event) => {
    const modal = document.getElementById("memberInfoModal");
    if (modal && event.target === modal) modal.style.display = "none";
  });

  window.addEventListener("resize", () => {
    const titleElement = document.querySelector(".project-detail-title");
    if (titleElement && project) titleElement.textContent = truncateTitleForMobile(project.title);
  });
});

function showNotification(message, type = "success") {
  const notification = document.createElement("div");
  notification.className = `notification ${type}`;
  notification.textContent = message;
  document.body.appendChild(notification);

  setTimeout(() => {
    notification.remove();
  }, 2600);
}