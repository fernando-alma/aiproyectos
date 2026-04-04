/**
 * project-detail.js — FASE 3: El Corazón del Sistema
 * 
 * Renderizado condicional inteligente, interacción basada en roles JWT,
 * y sidebar de control para el dueño/admin.
 */

const API_BASE = CONFIG.API_BASE;

document.addEventListener('DOMContentLoaded', () => {
  const mainContent = document.getElementById('projectDetailContent');
  const pdLoading = document.getElementById('pdLoading');
  const urlParams = new URLSearchParams(window.location.search);
  const projectId = urlParams.get('id');

  // State
  let project = null;
  let currentUser = null;  // { id, email, name, role }
  let members = [];
  let isCreator = false;
  let isMember = false;
  let isAdmin = false;

  // ─── Bootstrap ──────────────────────────────────────────────────────
  if (!projectId) {
    mainContent.innerHTML = `<p style="color: rgba(255,255,255,0.5); text-align:center; padding: 3rem;">No se especificó ningún ID de proyecto.</p>`;
    return;
  }

  init();

  async function init() {
    try {
      // Fetch project + user data in parallel
      const [projectData, userData] = await Promise.all([
        fetchProject(projectId),
        fetchCurrentUser()
      ]);

      project = projectData;
      currentUser = userData;

      if (!project) {
        mainContent.innerHTML = `<p style="color: rgba(255,255,255,0.5); text-align:center; padding: 3rem;">Proyecto no encontrado.</p>`;
        return;
      }

      // Update page title
      document.title = `AIWKND — ${project.title}`;

      // Determine user roles
      if (currentUser) {
        isCreator = String(currentUser.id) === String(project.created_by_user_id);
        isAdmin = currentUser.role === 'admin';
      }

      // Fetch members
      members = await fetchMembers(projectId);

      // Check if current user is a member
      if (currentUser) {
        isMember = members.some(m => String(m.user_id) === String(currentUser.id));
      }

      // Render everything
      renderMainContent();
      setupSidebar();
      setupDeleteModal();

    } catch (err) {
      console.error('Error initializing project detail:', err);
      mainContent.innerHTML = `<p style="color: rgba(255,255,255,0.5); text-align:center; padding: 3rem;">Error al cargar la información del proyecto.</p>`;
    }
  }

  // ─── API Helpers ────────────────────────────────────────────────────

  async function fetchProject(id) {
    const res = await fetch(`${API_BASE}project/get?id=${id}`);
    if (!res.ok) return null;
    const data = await res.json();
    return data.success && data.project ? data.project : null;
  }

  async function fetchCurrentUser() {
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    if (!token) return null;

    try {
      const res = await fetch(`${API_BASE}auth/me`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      if (!res.ok) return null;
      const data = await res.json();
      return data.success && data.user ? data.user : null;
    } catch {
      // Fallback to localStorage
      const userId = localStorage.getItem('userId');
      const email = localStorage.getItem('userEmail');
      const name = localStorage.getItem('userName');
      if (userId && email) {
        return { id: userId, email, name, role: 'user' };
      }
      return null;
    }
  }

  async function fetchMembers(projectId) {
    try {
      const res = await fetch(`${API_BASE}project/members?id=${projectId}`);
      if (!res.ok) return [];
      const data = await res.json();
      return data.success && Array.isArray(data.members) ? data.members : [];
    } catch {
      return [];
    }
  }

  async function fetchJoinRequests(projectId) {
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    if (!token) return [];

    try {
      const res = await fetch(`${API_BASE}project/getJoinRequests?project_id=${projectId}`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      if (!res.ok) return [];
      const data = await res.json();
      return data.success && Array.isArray(data.requests) ? data.requests : [];
    } catch {
      return [];
    }
  }

  function getAuthHeaders() {
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');
    return {
      'Authorization': token ? `Bearer ${token}` : '',
      'Content-Type': 'application/json'
    };
  }

  // ─── Utilities ──────────────────────────────────────────────────────

  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function hasValue(val) {
    return val !== null && val !== undefined && String(val).trim() !== '';
  }

  function formatDate(dateStr) {
    if (!dateStr) return '';
    try {
      return new Date(dateStr).toLocaleDateString('es-ES', {
        day: 'numeric', month: 'long', year: 'numeric'
      });
    } catch {
      return dateStr;
    }
  }

  function formatStatus(status) {
    const map = {
      'in_progress': 'En progreso',
      'completed': 'Completado',
      'active': 'Activo',
      'paused': 'Pausado'
    };
    return map[status] || status || 'Sin estado';
  }

  function getInitials(name) {
    if (!name) return '??';
    const parts = name.trim().split(/\s+/);
    return (parts[0]?.[0] || '').toUpperCase() + (parts[1]?.[0] || '').toUpperCase() || parts[0]?.[0]?.toUpperCase() || '?';
  }

  function extractYouTubeId(url) {
    if (!url) return null;
    const match = url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]{11})/);
    return match?.[1] || null;
  }

  // ─── Main Render ────────────────────────────────────────────────────

  function renderMainContent() {
    let html = '';

    // 1. Hero Image
    if (hasValue(project.image)) {
      html += `<img class="pd-hero-image" src="backend/public${esc(project.image)}" alt="${esc(project.title)}" loading="lazy">`;
    }

    // 2. Header (always rendered — title is required)
    html += `
      <div class="pd-header">
        <span class="pd-header-label">Proyecto:</span>
        <h1 class="pd-title">${esc(project.title)}</h1>
      </div>
    `;

    // 3. Badges (status + date + group)
    html += renderBadges();

    // 4. Action buttons (Deploy / Repository)
    html += renderActionButtons();

    // 5. Description
    if (hasValue(project.description)) {
      html += `
        <div class="pd-section">
          <span class="pd-section-label">Descripción:</span>
          <div class="pd-description">
            ${formatProjectDescription(project.description)}
          </div>
        </div>
      `;
    }

    // 6. Pitch / Video
    html += renderVideoSection();

    // 7. Team
    html += renderTeamSection();

    // 8. Interaction Button
    html += renderInteractionButton();

    // Replace loading with real content
    mainContent.innerHTML = html;

    // Bind interaction events
    bindInteractionEvents();
  }

  // ─── Badges ─────────────────────────────────────────────────────────

  function renderBadges() {
    let badges = '';

    if (hasValue(project.status)) {
      const isCompleted = project.status === 'completed';
      const statusClass = isCompleted ? 'pd-badge--completed' : 'pd-badge--active';
      badges += `
        <span class="pd-badge ${statusClass}">
          <span class="pd-badge-dot"></span>
          ${esc(formatStatus(project.status))}
        </span>
      `;
    }

    if (hasValue(project.created_at)) {
      badges += `
        <span class="pd-badge pd-badge--date">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
          ${esc(formatDate(project.created_at))}
        </span>
      `;
    }

    if (hasValue(project.group_name)) {
      badges += `
        <span class="pd-badge pd-badge--group">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          ${esc(project.group_name)}
        </span>
      `;
    }

    if (!badges) return '';
    return `<div class="pd-badges">${badges}</div>`;
  }

  // ─── Action Buttons ────────────────────────────────────────────────

  function renderActionButtons() {
    let buttons = '';

    if (hasValue(project.link_deploy)) {
      buttons += `
        <a href="${esc(project.link_deploy)}" target="_blank" rel="noopener noreferrer" class="pd-btn-demo" id="btnDemoLive">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
          Ver Demo en Vivo
        </a>
      `;
    }

    if (hasValue(project.link_repository)) {
      buttons += `
        <a href="${esc(project.link_repository)}" target="_blank" rel="noopener noreferrer" class="pd-btn-repo" id="btnRepo">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>
          Ver Repositorio
        </a>
      `;
    }

    if (!buttons) return '';
    return `<div class="pd-actions">${buttons}</div>`;
  }

  // ─── Description Formatter ─────────────────────────────────────────

  function formatProjectDescription(description) {
    if (!description) return '';
    const lines = description.split('\n').filter(l => l.trim());
    let html = '';
    let bulletPoints = [];
    let inList = false;

    for (let i = 0; i < lines.length; i++) {
      const line = lines[i].trim();

      // Bullet point
      if (/^[•\-]\s/.test(line)) {
        if (!inList) {
          html += '<ul class="pd-desc-list">';
          inList = true;
        }
        bulletPoints.push(esc(line.replace(/^[•\-]\s/, '')));
        continue;
      }

      // Close open list
      if (inList) {
        html += bulletPoints.map(b => `<li>${b}</li>`).join('') + '</ul>';
        bulletPoints = [];
        inList = false;
      }

      // Section heading (line ending with ":")
      if (line.endsWith(':')) {
        html += `<div class="pd-desc-section-title">${esc(line)}</div>`;
        continue;
      }

      // Normal text
      html += `<div class="pd-desc-text">${esc(line)}</div>`;
    }

    // Close trailing list
    if (inList) {
      html += bulletPoints.map(b => `<li>${b}</li>`).join('') + '</ul>';
    }

    return html;
  }

  // ─── Video Section ─────────────────────────────────────────────────

  function renderVideoSection() {
    // Check both pitch and link_video for YouTube URLs
    const videoUrl = (project.link_video || '').trim();
    const pitch = (project.pitch || '').trim();

    let youtubeId = extractYouTubeId(pitch) || extractYouTubeId(videoUrl);

    if (!youtubeId) {
      // If pitch exists but is not a YouTube URL, render as text
      if (pitch) {
        const isUrl = /^https?:\/\//i.test(pitch);
        const content = isUrl
          ? `<a href="${esc(pitch)}" target="_blank" rel="noopener noreferrer" style="color: var(--aiw-cyan); word-break: break-all;">${esc(pitch)}</a>`
          : esc(pitch);

        return `
          <div class="pd-section">
            <span class="pd-section-label">Pitch:</span>
            <div class="pd-description">${content}</div>
          </div>
        `;
      }
      return '';
    }

    return `
      <div class="pd-video-section">
        <span class="pd-section-label">Pitch / Video:</span>
        <div class="pd-video-container">
          <iframe
            src="https://www.youtube.com/embed/${youtubeId}"
            title="Video del proyecto"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            allowfullscreen
            loading="lazy">
          </iframe>
        </div>
      </div>
    `;
  }

  // ─── Team Section ──────────────────────────────────────────────────

  function renderTeamSection() {
    // Build the display list, ensuring the creator is always present
    let displayMembers = [...members];

    // Check if the creator is already in the members list
    const creatorInList = displayMembers.some(
      m => String(m.user_id) === String(project.created_by_user_id)
    );

    // If creator is not in the list, add a fallback entry
    if (!creatorInList && project.created_by_user_id) {
      // Try to figure out the creator's name from currentUser (if they are the creator)
      let creatorName = 'Creador del proyecto';
      if (currentUser && String(currentUser.id) === String(project.created_by_user_id)) {
        creatorName = currentUser.name || creatorName;
      }

      displayMembers.unshift({
        user_id: project.created_by_user_id,
        user_name: creatorName,
        role: 'owner',
        _isCreatorFallback: true
      });
    }

    if (displayMembers.length === 0) return '';

    // Sort: Creator first, then rest
    const sortedMembers = displayMembers.sort((a, b) => {
      const aIsCreator = String(a.user_id) === String(project.created_by_user_id);
      const bIsCreator = String(b.user_id) === String(project.created_by_user_id);
      if (aIsCreator && !bIsCreator) return -1;
      if (!aIsCreator && bIsCreator) return 1;
      return 0;
    });

    const cards = sortedMembers.map(m => {
      const memberIsCreator = String(m.user_id) === String(project.created_by_user_id);
      const initials = getInitials(m.user_name || m.name || 'U');
      const name = esc(m.user_name || m.name || 'Usuario');
      const creatorClass = memberIsCreator ? 'pd-team-card--creator' : '';
      const roleClass = memberIsCreator ? 'pd-team-role--creator' : '';
      const roleText = memberIsCreator ? 'Creador' : (m.role || 'Miembro');

      return `
        <div class="pd-team-card ${creatorClass}">
          <div class="pd-team-avatar">${initials}</div>
          <span class="pd-team-name">${name}</span>
          <span class="pd-team-role ${roleClass}">${esc(roleText)}</span>
        </div>
      `;
    }).join('');

    return `
      <div class="pd-team-section">
        <span class="pd-team-title">Equipo</span>
        <div class="pd-team-grid">${cards}</div>
      </div>
    `;
  }

  // ─── Interaction Button ────────────────────────────────────────────

  function renderInteractionButton() {
    // Creator: No button
    if (isCreator) return '';

    // Visitor (no token)
    if (!currentUser) {
      return `
        <div class="pd-interaction">
          <button class="pd-btn-join" id="btnJoinProject">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            Unirse al Proyecto
          </button>
        </div>
      `;
    }

    // Logged in, is member → Leave button
    if (isMember) {
      return `
        <div class="pd-interaction">
          <button class="pd-btn-leave" id="btnLeaveProject">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            Abandonar este proyecto
          </button>
        </div>
      `;
    }

    // Logged in, not member → Join button
    return `
      <div class="pd-interaction">
        <button class="pd-btn-join" id="btnJoinProject">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
          Unirse al Proyecto
        </button>
      </div>
    `;
  }

  // ─── Interaction Event Handlers ────────────────────────────────────

  function bindInteractionEvents() {
    const btnJoin = document.getElementById('btnJoinProject');
    const btnLeave = document.getElementById('btnLeaveProject');

    if (btnJoin) {
      btnJoin.addEventListener('click', handleJoinClick);
    }

    if (btnLeave) {
      btnLeave.addEventListener('click', handleLeaveClick);
    }
  }

  async function handleJoinClick() {
    // Visitor → redirect to login
    if (!currentUser) {
      if (window.showToast) {
        window.showToast('Inicia sesión', 'Necesitas una cuenta para unirte a este proyecto.', 'info');
      }
      setTimeout(() => {
        window.location.href = `login?redirect=project-detail?id=${projectId}`;
      }, 1200);
      return;
    }

    // Logged in → send join request
    const btn = document.getElementById('btnJoinProject');
    if (!btn) return;

    btn.disabled = true;
    btn.innerHTML = `
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="spin-icon"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
      Enviando solicitud...
    `;

    try {
      const res = await fetch(`${API_BASE}project/sendJoinRequest`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ project_id: project.id })
      });
      const data = await res.json();

      if (res.ok && data.success) {
        if (window.showToast) {
          window.showToast('¡Solicitud enviada!', 'El creador del proyecto revisará tu solicitud.', 'success');
        }
        btn.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Solicitud enviada
        `;
        btn.style.opacity = '0.6';
        btn.style.cursor = 'not-allowed';
      } else {
        throw new Error(data.message || 'Error al enviar la solicitud');
      }
    } catch (err) {
      btn.disabled = false;
      btn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
        Unirse al Proyecto
      `;
      if (window.showToast) {
        window.showToast('Error', err.message || 'No se pudo enviar la solicitud.', 'error');
      }
    }
  }

  function handleLeaveClick() {
    // Show leave confirmation modal
    const leaveModal = document.getElementById('leaveConfirmModal');
    if (leaveModal) {
      leaveModal.style.display = 'flex';
    }
  }

  // ─── Leave Confirmation Modal ───────────────────────────────────────

  const leaveConfirmModal = document.getElementById('leaveConfirmModal');
  const leaveCancelBtn = document.getElementById('leaveCancelBtn');
  const leaveConfirmBtn = document.getElementById('leaveConfirmBtn');

  if (leaveCancelBtn) {
    leaveCancelBtn.addEventListener('click', () => {
      if (leaveConfirmModal) leaveConfirmModal.style.display = 'none';
    });
  }

  if (leaveConfirmBtn) {
    leaveConfirmBtn.addEventListener('click', async () => {
      if (!currentUser) return;

      leaveConfirmBtn.disabled = true;
      leaveConfirmBtn.textContent = 'Abandonando...';

      try {
        const formData = new FormData();
        formData.append('project_id', project.id);
        formData.append('email', currentUser.email);

        const res = await fetch(`${API_BASE}member/delete`, {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        if (res.ok && data.success) {
          if (window.showToast) {
            window.showToast('Proyecto abandonado', 'Has abandonado el proyecto correctamente.', 'info');
          }
          setTimeout(() => window.location.reload(), 1200);
        } else {
          throw new Error(data.message || 'Error al abandonar');
        }
      } catch (err) {
        if (window.showToast) {
          window.showToast('Error', err.message || 'No se pudo abandonar el proyecto.', 'error');
        }
        leaveConfirmBtn.disabled = false;
        leaveConfirmBtn.textContent = 'Sí, abandonar';
      } finally {
        if (leaveConfirmModal) leaveConfirmModal.style.display = 'none';
      }
    });
  }

  // Click outside to close leave modal
  if (leaveConfirmModal) {
    leaveConfirmModal.addEventListener('click', (e) => {
      if (e.target === leaveConfirmModal) leaveConfirmModal.style.display = 'none';
    });
  }

  // ─── Sidebar Setup ─────────────────────────────────────────────────

  async function setupSidebar() {
    const sidebar = document.getElementById('ownerSidebar');
    if (!sidebar) return;

    // Only show for creator or admin
    if (!isCreator && !isAdmin) return;

    sidebar.style.display = 'flex';

    // Set edit link
    const editBtn = document.getElementById('editProjectBtn');
    if (editBtn) {
      editBtn.href = `project-edit?id=${project.id}`;
    }

    // Load join requests
    await loadJoinRequests();

    // Load sidebar members
    renderSidebarMembers();
  }

  // ─── Join Requests Panel ───────────────────────────────────────────

  async function loadJoinRequests() {
    const requestsList = document.getElementById('requestsList');
    const requestsCount = document.getElementById('requestsCount');
    if (!requestsList) return;

    try {
      const requests = await fetchJoinRequests(project.id);

      if (requests.length === 0) {
        requestsList.innerHTML = `<p class="pd-sidebar-empty">No hay solicitudes pendientes</p>`;
        if (requestsCount) requestsCount.style.display = 'none';
        return;
      }

      if (requestsCount) {
        requestsCount.textContent = requests.length;
        requestsCount.style.display = 'inline-flex';
      }

      requestsList.innerHTML = requests.map(req => {
        const initials = getInitials(req.user_name || req.name || 'U');
        const name = esc(req.user_name || req.name || 'Usuario');
        const email = esc(req.email || '');

        return `
          <div class="pd-request-card" data-request-id="${req.id}">
            <div class="pd-request-avatar">${initials}</div>
            <div class="pd-request-info">
              <div class="pd-request-name">${name}</div>
              <div class="pd-request-email">${email}</div>
            </div>
            <div class="pd-request-actions">
              <button class="pd-btn-approve" title="Aprobar" data-action="approve" data-id="${req.id}">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </button>
              <button class="pd-btn-reject" title="Rechazar" data-action="reject" data-id="${req.id}">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
          </div>
        `;
      }).join('');

      // Bind approve/reject events
      requestsList.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', handleRequestAction);
      });
    } catch {
      requestsList.innerHTML = `<p class="pd-sidebar-empty">Error cargando solicitudes</p>`;
    }
  }

  async function handleRequestAction(e) {
    const btn = e.currentTarget;
    const action = btn.dataset.action;
    const requestId = btn.dataset.id;
    const card = btn.closest('.pd-request-card');

    btn.disabled = true;

    try {
      const endpoint = action === 'approve'
        ? 'project/approveJoinRequest'
        : 'project/rejectJoinRequest';

      const res = await fetch(`${API_BASE}${endpoint}`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ request_id: requestId })
      });
      const data = await res.json();

      if (res.ok && data.success) {
        // Animate removal
        card.style.opacity = '0';
        card.style.transform = 'translateX(20px)';
        card.style.transition = 'all 0.3s ease';

        setTimeout(() => {
          card.remove();
          updateRequestsCount();

          // Refresh members if approved
          if (action === 'approve') {
            refreshMembers();
          }
        }, 300);

        const msg = action === 'approve' ? 'Solicitud aprobada' : 'Solicitud rechazada';
        if (window.showToast) window.showToast('Hecho', msg, action === 'approve' ? 'success' : 'info');
      } else {
        throw new Error(data.message || 'Error al procesar');
      }
    } catch (err) {
      btn.disabled = false;
      if (window.showToast) window.showToast('Error', err.message, 'error');
    }
  }

  function updateRequestsCount() {
    const requestsList = document.getElementById('requestsList');
    const requestsCount = document.getElementById('requestsCount');
    if (!requestsList || !requestsCount) return;

    const remaining = requestsList.querySelectorAll('.pd-request-card').length;
    if (remaining === 0) {
      requestsList.innerHTML = `<p class="pd-sidebar-empty">No hay solicitudes pendientes</p>`;
      requestsCount.style.display = 'none';
    } else {
      requestsCount.textContent = remaining;
    }
  }

  // ─── Sidebar Members ───────────────────────────────────────────────

  function renderSidebarMembers() {
    const list = document.getElementById('sidebarMembersList');
    if (!list) return;

    if (members.length === 0) {
      list.innerHTML = `<p class="pd-sidebar-empty">No hay miembros aún</p>`;
      return;
    }

    // Sort: creator first
    const sorted = [...members].sort((a, b) => {
      const aCreator = String(a.user_id) === String(project.created_by_user_id);
      const bCreator = String(b.user_id) === String(project.created_by_user_id);
      if (aCreator && !bCreator) return -1;
      if (!aCreator && bCreator) return 1;
      return 0;
    });

    list.innerHTML = sorted.map(m => {
      const memberIsCreator = String(m.user_id) === String(project.created_by_user_id);
      const initials = getInitials(m.user_name || m.name || 'U');
      const name = esc(m.user_name || m.name || 'Usuario');
      const roleText = memberIsCreator ? 'Creador' : (m.role || 'Miembro');
      const roleClass = memberIsCreator ? 'creator' : '';

      // Kick button (not for creator)
      const kickBtn = !memberIsCreator
        ? `<button class="pd-btn-kick" data-user-id="${m.user_id}" data-action="kick" title="Expulsar">Expulsar</button>`
        : '';

      return `
        <div class="pd-smember-card" data-member-id="${m.user_id}">
          <div class="pd-smember-avatar">${initials}</div>
          <div class="pd-smember-info">
            <div class="pd-smember-name">${name}</div>
            <div class="pd-smember-role ${roleClass}">${esc(roleText)}</div>
          </div>
          ${kickBtn}
        </div>
      `;
    }).join('');

    // Bind kick events
    list.querySelectorAll('[data-action="kick"]').forEach(btn => {
      btn.addEventListener('click', handleKickMember);
    });
  }

  async function handleKickMember(e) {
    const btn = e.currentTarget;
    const targetUserId = btn.dataset.userId;
    const card = btn.closest('.pd-smember-card');

    if (!confirm('¿Expulsar a este miembro del proyecto?')) return;

    btn.disabled = true;
    btn.textContent = '...';

    try {
      const res = await fetch(`${API_BASE}project/removeMember`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({
          project_id: project.id,
          user_id: targetUserId
        })
      });
      const data = await res.json();

      if (res.ok && data.success) {
        card.style.opacity = '0';
        card.style.transform = 'translateX(20px)';
        card.style.transition = 'all 0.3s ease';

        setTimeout(() => {
          card.remove();
          refreshMembers();
        }, 300);

        if (window.showToast) window.showToast('Miembro expulsado', 'El usuario ha sido removido del equipo.', 'info');
      } else {
        throw new Error(data.message || 'Error al expulsar');
      }
    } catch (err) {
      btn.disabled = false;
      btn.textContent = 'Expulsar';
      if (window.showToast) window.showToast('Error', err.message, 'error');
    }
  }

  // ─── Refresh Members (after approve or kick) ──────────────────────

  async function refreshMembers() {
    members = await fetchMembers(projectId);

    // Check if current user membership status changed
    if (currentUser) {
      isMember = members.some(m => String(m.user_id) === String(currentUser.id));
    }

    // Re-render team in main content
    const teamSection = mainContent.querySelector('.pd-team-section');
    const newTeamHtml = renderTeamSection();

    if (teamSection && newTeamHtml) {
      teamSection.outerHTML = newTeamHtml;
    } else if (!teamSection && newTeamHtml) {
      // Insert before interaction section
      const interaction = mainContent.querySelector('.pd-interaction');
      if (interaction) {
        interaction.insertAdjacentHTML('beforebegin', newTeamHtml);
      } else {
        mainContent.insertAdjacentHTML('beforeend', newTeamHtml);
      }
    }

    // Re-render sidebar members
    renderSidebarMembers();
  }

  // ─── Delete Modal ──────────────────────────────────────────────────

  function setupDeleteModal() {
    const deleteBtn = document.getElementById('deleteProjectBtn');
    const deleteModal = document.getElementById('deleteModal');
    const cancelBtn = document.getElementById('deleteCancelBtn');
    const confirmBtn = document.getElementById('deleteConfirmBtn');

    if (!deleteBtn || !deleteModal) return;

    deleteBtn.addEventListener('click', () => {
      deleteModal.style.display = 'flex';
    });

    if (cancelBtn) {
      cancelBtn.addEventListener('click', () => {
        deleteModal.style.display = 'none';
      });
    }

    if (confirmBtn) {
      confirmBtn.addEventListener('click', async () => {
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Eliminando...';

        try {
          const fd = new FormData();
          fd.append('id', project.id);
          fd.append('project_id', project.id);

          const token = localStorage.getItem('token') || sessionStorage.getItem('token');
          const res = await fetch(`${API_BASE}project/delete`, {
            method: 'POST',
            headers: { Authorization: token ? `Bearer ${token}` : '' },
            body: fd
          });
          const data = await res.json();

          if (res.ok && data.success) {
            if (window.showToast) window.showToast('Eliminado', 'El proyecto ha sido eliminado.', 'success');
            setTimeout(() => window.location.href = 'project-list', 1200);
          } else {
            throw new Error(data.message || 'Error al eliminar');
          }
        } catch (err) {
          if (window.showToast) window.showToast('Error', err.message, 'error');
          confirmBtn.disabled = false;
          confirmBtn.textContent = 'Eliminar';
        } finally {
          deleteModal.style.display = 'none';
        }
      });
    }

    // Close on overlay click
    deleteModal.addEventListener('click', (e) => {
      if (e.target === deleteModal) deleteModal.style.display = 'none';
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (deleteModal.style.display === 'flex') deleteModal.style.display = 'none';
        if (leaveConfirmModal?.style.display === 'flex') leaveConfirmModal.style.display = 'none';
      }
    });
  }
});

// ─── Legacy Notification Support ────────────────────────────────────

function showNotification(message, type = 'success') {
  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  notification.textContent = message;
  document.body.appendChild(notification);
  setTimeout(() => notification.remove(), 2600);
}