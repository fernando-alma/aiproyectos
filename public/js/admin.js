/**
 * admin.js — FASE 4: Súper Admin "Modo Dios"
 *
 * 3 pestañas: El Radar (stats), Hackathons (dashboards), El Tribunal (users)
 * JWT role verification, CRUD operations, role management.
 */

const API_BASE = CONFIG.API_BASE;

document.addEventListener('DOMContentLoaded', () => {
  const mainContent = document.getElementById('adminMainContent');
  const tabButtons = document.querySelectorAll('.adm-nav-btn');
  let currentTab = 'radar';

  // State
  let token = localStorage.getItem('token') || sessionStorage.getItem('token');
  let pendingDeleteSlug = null;

  // ─── Auth Gate ──────────────────────────────────────────────────────
  if (!token) {
    redirectOut();
    return;
  }

  verifyAdmin();

  async function verifyAdmin() {
    try {
      const res = await fetch(`${API_BASE}auth/me`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      const data = await res.json();

      if (!res.ok || !data.success || !data.user || data.user.role !== 'admin') {
        redirectOut();
        return;
      }

      // Admin verified → boot the panel
      initPanel();
    } catch {
      redirectOut();
    }
  }

  function redirectOut() {
    if (window.showToast) {
      window.showToast('Acceso denegado', 'No tienes privilegios de administrador.', 'error');
    }
    setTimeout(() => { window.location.href = 'project-list'; }, 800);
  }

  function getAuthHeaders() {
    return {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    };
  }

  // ─── Panel Init ─────────────────────────────────────────────────────

  function initPanel() {
    // Bind tab switching
    tabButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const tab = btn.dataset.tab;
        if (tab === currentTab) return;

        tabButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentTab = tab;
        renderTab(tab);
      });
    });

    // Load default tab
    renderTab('radar');

    // Bind modal events
    bindModalEvents();
  }

  // ─── Tab Router ─────────────────────────────────────────────────────

  function renderTab(tab) {
    mainContent.innerHTML = '<div class="adm-loading"><div class="adm-spinner"></div><p>Cargando...</p></div>';

    switch (tab) {
      case 'radar': loadRadar(); break;
      case 'dashboards': loadDashboards(); break;
      case 'users': loadUsers(); break;
    }
  }

  // ─── TAB 1: El Radar (Stats) ────────────────────────────────────────

  async function loadRadar() {
    try {
      const res = await fetch(`${API_BASE}admin/stats`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      const data = await res.json();

      if (!res.ok || !data.success) throw new Error(data.message || 'Error');

      const s = data.data;
      mainContent.innerHTML = `
        <div class="adm-tab-header">
          <div class="adm-tab-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19.07 4.93A10 10 0 0 0 6.99 3.34"/><path d="M4 6h.01"/><path d="M2.29 9.62A10 10 0 1 0 21.31 8.35"/><path d="M16.24 7.76A6 6 0 1 0 8.23 16.67"/><path d="M12 18h.01"/><path d="M17.99 11.66A6 6 0 0 1 15.77 16.67"/><circle cx="12" cy="12" r="2"/><path d="m13.41 10.59 5.66-5.66"/></svg>
            <div>
              <h2>El Radar</h2>
              <span class="adm-tab-subtitle">Métricas globales del sistema</span>
            </div>
          </div>
        </div>

        <div class="adm-stats-grid">
          <div class="adm-stat-card glass-effect">
            <div class="adm-stat-icon adm-stat-icon--users">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="adm-stat-number adm-stat-number--cyan">${s.total_users}</div>
            <span class="adm-stat-label">Usuarios Registrados</span>
          </div>

          <div class="adm-stat-card glass-effect">
            <div class="adm-stat-icon adm-stat-icon--admins">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <div class="adm-stat-number adm-stat-number--purple">${s.total_admins}</div>
            <span class="adm-stat-label">Administradores</span>
          </div>

          <div class="adm-stat-card glass-effect">
            <div class="adm-stat-icon adm-stat-icon--dashboards">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
            </div>
            <div class="adm-stat-number adm-stat-number--pink">${s.total_dashboards}</div>
            <span class="adm-stat-label">Hackathons</span>
          </div>

          <div class="adm-stat-card glass-effect">
            <div class="adm-stat-icon adm-stat-icon--projects">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>
            </div>
            <div class="adm-stat-number adm-stat-number--blue">${s.total_projects}</div>
            <span class="adm-stat-label">Proyectos Totales</span>
          </div>
        </div>
      `;
    } catch (err) {
      mainContent.innerHTML = `<div class="adm-empty">Error al cargar estadísticas: ${esc(err.message)}</div>`;
    }
  }

  // ─── TAB 2: Hackathons (Dashboards) ─────────────────────────────────

  async function loadDashboards() {
    try {
      const res = await fetch(`${API_BASE}dashboards`);
      const data = await res.json();

      if (!res.ok || !data.success) throw new Error(data.message || 'Error');

      const dashboards = data.data || [];

      let tableRows = '';
      if (dashboards.length === 0) {
        tableRows = `<tr><td colspan="5" class="adm-empty">No hay hackathons creados aún</td></tr>`;
      } else {
        tableRows = dashboards.map(d => `
          <tr>
            <td>
              <span class="adm-color-badge" data-color="${esc(d.color || 'purple')}"></span>
              <span class="adm-table-name" style="margin-left: 0.5rem;">${esc(d.title)}</span>
            </td>
            <td><span class="adm-table-slug">${esc(d.slug)}</span></td>
            <td class="adm-table-count">${d.total_projects ?? 0}</td>
            <td>${formatDate(d.created_at)}</td>
            <td><button class="adm-btn-delete" data-slug="${esc(d.slug)}" data-name="${esc(d.title)}">Eliminar</button></td>
          </tr>
        `).join('');
      }

      mainContent.innerHTML = `
        <div class="adm-tab-header">
          <div class="adm-tab-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
            <div>
              <h2>Creador de Mundos</h2>
              <span class="adm-tab-subtitle">Gestión de hackathons / eventos</span>
            </div>
          </div>
          <button class="adm-btn-primary" id="btnOpenCreateDash">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Crear Nuevo Hackathon
          </button>
        </div>

        <div class="adm-table-wrapper glass-effect">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Hackathon</th>
                <th>Slug</th>
                <th>Proyectos</th>
                <th>Creado</th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>${tableRows}</tbody>
          </table>
        </div>
      `;

      // Bind "Crear" button
      document.getElementById('btnOpenCreateDash')?.addEventListener('click', () => {
        const modal = document.getElementById('createDashModal');
        if (modal) modal.style.display = 'flex';
      });

      // Bind delete buttons
      mainContent.querySelectorAll('.adm-btn-delete').forEach(btn => {
        btn.addEventListener('click', () => {
          pendingDeleteSlug = btn.dataset.slug;
          const name = btn.dataset.name;
          document.getElementById('deleteConfirmTitle').textContent = `¿Eliminar "${name}"?`;
          document.getElementById('deleteConfirmMessage').textContent = 'Se borrarán todos los proyectos, tareas y miembros de este hackathon.';
          document.getElementById('deleteConfirmModal').style.display = 'flex';
        });
      });

    } catch (err) {
      mainContent.innerHTML = `<div class="adm-empty">Error al cargar hackathons: ${esc(err.message)}</div>`;
    }
  }

  // ─── TAB 3: El Tribunal (Users) ─────────────────────────────────────

  async function loadUsers() {
    try {
      const res = await fetch(`${API_BASE}admin/users`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      const data = await res.json();

      if (!res.ok || !data.success) throw new Error(data.message || 'Error');

      const users = data.data || [];

      let tableRows = '';
      if (users.length === 0) {
        tableRows = `<tr><td colspan="4" class="adm-empty">No hay usuarios registrados</td></tr>`;
      } else {
        tableRows = users.map(u => {
          const isAdmin = u.role === 'admin';
          return `
            <tr>
              <td>
                <div class="adm-table-name">${esc(u.name)}</div>
                <div class="adm-table-email">${esc(u.email)}</div>
              </td>
              <td>
                <select class="adm-role-select" data-user-id="${u.id}" ${isAdmin ? '' : ''}>
                  <option value="user" ${u.role === 'user' ? 'selected' : ''}>user</option>
                  <option value="admin" ${u.role === 'admin' ? 'selected' : ''}>admin</option>
                </select>
              </td>
              <td>${esc(u.avatar_initials || getInitials(u.name))}</td>
              <td>${formatDate(u.created_at)}</td>
            </tr>
          `;
        }).join('');
      }

      mainContent.innerHTML = `
        <div class="adm-tab-header">
          <div class="adm-tab-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <div>
              <h2>El Tribunal</h2>
              <span class="adm-tab-subtitle">Gestión de usuarios y privilegios</span>
            </div>
          </div>
        </div>

        <div class="adm-table-wrapper glass-effect">
          <table class="adm-table">
            <thead>
              <tr>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Iniciales</th>
                <th>Registro</th>
              </tr>
            </thead>
            <tbody>${tableRows}</tbody>
          </table>
        </div>
      `;

      // Bind role change selects
      mainContent.querySelectorAll('.adm-role-select').forEach(select => {
        select.addEventListener('change', (e) => handleRoleChange(e.target));
      });

    } catch (err) {
      mainContent.innerHTML = `<div class="adm-empty">Error al cargar usuarios: ${esc(err.message)}</div>`;
    }
  }

  // ─── Role Change Handler ────────────────────────────────────────────

  async function handleRoleChange(selectEl) {
    const userId = selectEl.dataset.userId;
    const newRole = selectEl.value;
    const originalValue = selectEl.dataset.original || (newRole === 'admin' ? 'user' : 'admin');

    selectEl.disabled = true;

    try {
      const res = await fetch(`${API_BASE}admin/changeRole`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ user_id: userId, role: newRole })
      });
      const data = await res.json();

      if (res.ok && data.success) {
        if (window.showToast) {
          window.showToast('Rol actualizado', `Rol cambiado a "${newRole}" correctamente.`, 'success');
        }
        selectEl.dataset.original = newRole;
      } else {
        throw new Error(data.message || 'Error al cambiar rol');
      }
    } catch (err) {
      selectEl.value = originalValue;
      if (window.showToast) {
        window.showToast('Error', err.message, 'error');
      }
    } finally {
      selectEl.disabled = false;
    }
  }

  // ─── Modal Events ───────────────────────────────────────────────────

  function bindModalEvents() {
    // Create Dashboard Modal
    const createModal = document.getElementById('createDashModal');
    const closeCreateBtn = document.getElementById('closeCreateDashModal');
    const createForm = document.getElementById('createDashForm');

    if (closeCreateBtn) {
      closeCreateBtn.addEventListener('click', () => createModal.style.display = 'none');
    }

    if (createModal) {
      createModal.addEventListener('click', (e) => {
        if (e.target === createModal) createModal.style.display = 'none';
      });
    }

    if (createForm) {
      createForm.addEventListener('submit', handleCreateDashboard);
    }

    // Delete Confirmation Modal
    const deleteModal = document.getElementById('deleteConfirmModal');
    const deleteCancelBtn = document.getElementById('deleteCancelBtn');
    const deleteConfirmBtn = document.getElementById('deleteConfirmBtn');

    if (deleteCancelBtn) {
      deleteCancelBtn.addEventListener('click', () => {
        deleteModal.style.display = 'none';
        pendingDeleteSlug = null;
      });
    }

    if (deleteConfirmBtn) {
      deleteConfirmBtn.addEventListener('click', handleDeleteDashboard);
    }

    if (deleteModal) {
      deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) {
          deleteModal.style.display = 'none';
          pendingDeleteSlug = null;
        }
      });
    }

    // Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        if (createModal?.style.display === 'flex') createModal.style.display = 'none';
        if (deleteModal?.style.display === 'flex') {
          deleteModal.style.display = 'none';
          pendingDeleteSlug = null;
        }
      }
    });
  }

  // ─── Create Dashboard Handler ───────────────────────────────────────

  async function handleCreateDashboard(e) {
    e.preventDefault();

    const title = document.getElementById('dashTitle').value.trim();
    const description = document.getElementById('dashDescription').value.trim();
    const color = document.getElementById('dashColor').value;
    const submitBtn = document.getElementById('submitCreateDash');

    if (!title || !description) return;

    submitBtn.disabled = true;
    submitBtn.textContent = 'Creando...';

    try {
      const formData = new URLSearchParams();
      formData.append('title', title);
      formData.append('description', description);
      formData.append('color', color);

      const res = await fetch(`${API_BASE}dashboard/create`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData.toString()
      });
      const data = await res.json();

      if (res.ok && data.success) {
        if (window.showToast) {
          window.showToast('¡Hackathon creado!', `Slug: ${data.slug || 'generado'}`, 'success');
        }
        document.getElementById('createDashModal').style.display = 'none';
        document.getElementById('createDashForm').reset();
        loadDashboards(); // Refresh the table
      } else {
        throw new Error(data.message || 'Error al crear');
      }
    } catch (err) {
      if (window.showToast) window.showToast('Error', err.message, 'error');
    } finally {
      submitBtn.disabled = false;
      submitBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        Crear Hackathon
      `;
    }
  }

  // ─── Delete Dashboard Handler ───────────────────────────────────────

  async function handleDeleteDashboard() {
    if (!pendingDeleteSlug) return;

    const confirmBtn = document.getElementById('deleteConfirmBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Eliminando...';

    try {
      const formData = new URLSearchParams();
      formData.append('slug', pendingDeleteSlug);

      const res = await fetch(`${API_BASE}dashboard/delete`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData.toString()
      });
      const data = await res.json();

      if (res.ok && data.success) {
        if (window.showToast) window.showToast('Eliminado', 'El hackathon ha sido eliminado.', 'success');
        loadDashboards();
      } else {
        throw new Error(data.message || 'Error al eliminar');
      }
    } catch (err) {
      if (window.showToast) window.showToast('Error', err.message, 'error');
    } finally {
      document.getElementById('deleteConfirmModal').style.display = 'none';
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Eliminar';
      pendingDeleteSlug = null;
    }
  }

  // ─── Utilities ──────────────────────────────────────────────────────

  function esc(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function formatDate(dateStr) {
    if (!dateStr) return '—';
    try {
      return new Date(dateStr).toLocaleDateString('es-ES', {
        day: 'numeric', month: 'short', year: 'numeric'
      });
    } catch {
      return dateStr;
    }
  }

  function getInitials(name) {
    if (!name) return '??';
    const parts = name.trim().split(/\s+/);
    return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || '?';
  }
});
