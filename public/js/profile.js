/**
 * profile.js — FASE 4.5: Perfil de Usuario
 *
 * Loads user data, projects, requests.
 * Shows "Crear Proyecto" CTA if 0 projects.
 * Password change with toast feedback.
 */

document.addEventListener('DOMContentLoaded', async () => {
    if (!window.isAuthenticated || !window.isAuthenticated()) {
        window.location.href = 'login';
        return;
    }

    const API = window.CONFIG.API_BASE;
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');

    // DOM elements
    const profileName = document.getElementById('profileName');
    const profileEmail = document.getElementById('profileEmail');
    const profileAvatar = document.getElementById('profileAvatar');
    const profileInitials = document.getElementById('profileInitials');
    const saveNameBtn = document.getElementById('saveNameBtn');
    const passwordForm = document.getElementById('changePasswordForm');
    const savePasswordBtn = document.getElementById('savePasswordBtn');
    const projectsList = document.getElementById('myProjectsList');
    const requestsList = document.getElementById('myRequestsList');
    const ctaContainer = document.getElementById('createProjectCta');
    const logoutBtn = document.getElementById('logoutButton');

    // ─── 1. Load User Data ─────────────────────────────────────────────
    let currentUser = null;
    try {
        const res = await window.authFetch(`${API}auth/me`);
        const data = await res.json();

        if (data.success && data.user) {
            currentUser = data.user;
            profileName.value = data.user.name || '';
            profileEmail.value = data.user.email || '';
            const initials = getInitials(data.user.name);
            profileInitials.textContent = initials;
        }
    } catch (e) {
        const ud = window.getUserData();
        profileName.value = ud.userName || '';
        profileEmail.value = ud.userEmail || '';
        profileInitials.textContent = getInitials(ud.userName);
    }

    // ─── 2. Load Projects ──────────────────────────────────────────────
    let userProjects = [];
    try {
        const res = await fetch(`${API}projects/user`, {
            headers: { Authorization: `Bearer ${token}` }
        });
        if (res.ok) {
            const data = await res.json();
            userProjects = data.projects || data.data || [];
        }
    } catch { /* silent */ }

    renderProjects(userProjects);

    // ─── 3. CTA: Crear Proyecto ────────────────────────────────────────
    if (userProjects.length === 0 && ctaContainer) {
        ctaContainer.innerHTML = `
            <a href="project-create" class="prof-cta">
                <div class="prof-cta-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="M12 5v14"/>
                    </svg>
                </div>
                <div>
                    <div class="prof-cta-text">+ Crear un Proyecto Nuevo</div>
                    <div class="prof-cta-sub">Aún no tienes proyectos, ¡empieza uno ahora!</div>
                </div>
            </a>
        `;
    }

    // ─── 4. Load Join Requests ─────────────────────────────────────────
    // Try to load user's pending requests by checking all known projects
    // We'll show only if projects have pending requests
    let allRequests = [];
    try {
        // Use user projects to check for requests
        for (const p of userProjects) {
            const res = await fetch(`${API}project/getJoinRequests?project_id=${p.id}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.success && Array.isArray(data.requests)) {
                    data.requests.forEach(r => {
                        allRequests.push({
                            ...r,
                            projectTitle: p.title
                        });
                    });
                }
            }
        }
    } catch { /* silent */ }

    renderRequests(allRequests);

    // ─── 5. Save Name ──────────────────────────────────────────────────
    if (saveNameBtn) {
        saveNameBtn.addEventListener('click', async () => {
            const newName = profileName.value.trim();
            if (!newName) {
                if (window.showToast) window.showToast('Atención', 'El nombre no puede estar vacío.', 'warning');
                return;
            }

            saveNameBtn.disabled = true;
            const original = saveNameBtn.innerHTML;
            saveNameBtn.textContent = 'Guardando...';

            try {
                const res = await window.authFetch(`${API}auth/update-profile`, {
                    method: 'POST',
                    body: JSON.stringify({ name: newName })
                });
                const data = await res.json();

                if (data.success) {
                    if (window.showToast) window.showToast('¡Listo!', 'Nombre actualizado correctamente.', 'success');
                    localStorage.setItem('userName', newName);
                    sessionStorage.setItem('userName', newName);
                    profileInitials.textContent = getInitials(newName);
                } else {
                    throw new Error(data.message || 'Error al actualizar');
                }
            } catch (err) {
                if (window.showToast) window.showToast('Error', err.message, 'error');
            } finally {
                saveNameBtn.disabled = false;
                saveNameBtn.innerHTML = original;
            }
        });
    }

    // ─── 6. Change Password ────────────────────────────────────────────
    if (passwordForm) {
        passwordForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const curr = document.getElementById('currentPassword').value;
            const newP = document.getElementById('newPassword').value;
            const conf = document.getElementById('confirmPassword').value;

            if (!curr || !newP || !conf) {
                if (window.showToast) window.showToast('Atención', 'Completa los tres campos de contraseña.', 'warning');
                return;
            }
            if (newP.length < 8) {
                if (window.showToast) window.showToast('Atención', 'La nueva contraseña debe tener al menos 8 caracteres.', 'warning');
                return;
            }
            if (newP !== conf) {
                if (window.showToast) window.showToast('Error', 'Las contraseñas no coinciden.', 'error');
                return;
            }

            savePasswordBtn.disabled = true;
            const original = savePasswordBtn.innerHTML;
            savePasswordBtn.textContent = 'Guardando...';

            try {
                const res = await window.authFetch(`${API}auth/change-password`, {
                    method: 'POST',
                    body: JSON.stringify({
                        current_password: curr,
                        new_password: newP,
                        new_password_confirm: conf
                    })
                });
                const data = await res.json();

                if (data.success) {
                    if (window.showToast) window.showToast('¡Contraseña Actualizada!', 'Tu contraseña se ha cambiado correctamente.', 'success');
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmPassword').value = '';
                } else {
                    throw new Error(data.message || 'Error al cambiar contraseña');
                }
            } catch (err) {
                if (window.showToast) window.showToast('Error', err.message, 'error');
            } finally {
                savePasswordBtn.disabled = false;
                savePasswordBtn.innerHTML = original;
            }
        });
    }

    // ─── 7. Logout ─────────────────────────────────────────────────────
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            if (window.logout) window.logout();
            else {
                localStorage.clear();
                sessionStorage.clear();
                window.location.href = 'login';
            }
        });
    }

    // ═══ Renderers ═══════════════════════════════════════════════════════

    function renderProjects(projects) {
        if (!projectsList) return;

        if (projects.length === 0) {
            projectsList.innerHTML = '<div class="prof-empty">No tienes proyectos aún</div>';
            return;
        }

        projectsList.innerHTML = projects.map(p => {
            const statusClass = p.status === 'completed' ? 'prof-badge--completed' : 'prof-badge--progress';
            const statusText = p.status === 'completed' ? 'Completado' : 'En progreso';
            const desc = esc((p.description || '').substring(0, 80));

            return `
                <div class="prof-project-card">
                    <div class="prof-project-info">
                        <div class="prof-project-name">${esc(p.title)}</div>
                        <div class="prof-project-desc">${desc}${(p.description || '').length > 80 ? '...' : ''}</div>
                    </div>
                    <div class="prof-project-actions">
                        <span class="prof-badge ${statusClass}">${statusText}</span>
                        <a href="project-detail?id=${p.id}" class="prof-project-link">
                            Ver
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
                        </a>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderRequests(requests) {
        if (!requestsList) return;

        if (requests.length === 0) {
            requestsList.innerHTML = '<div class="prof-empty">No hay solicitudes</div>';
            return;
        }

        requestsList.innerHTML = requests.map(r => {
            let badgeClass = 'prof-badge--pending';
            let badgeText = 'Pendiente';
            if (r.status === 'approved') { badgeClass = 'prof-badge--approved'; badgeText = 'Aprobada'; }
            if (r.status === 'rejected') { badgeClass = 'prof-badge--rejected'; badgeText = 'Rechazada'; }

            return `
                <div class="prof-request-card">
                    <div>
                        <div class="prof-request-name">${esc(r.name || r.email || 'Usuario')}</div>
                        <div class="prof-project-desc">${esc(r.projectTitle || '')}</div>
                    </div>
                    <span class="prof-badge ${badgeClass}">${badgeText}</span>
                </div>
            `;
        }).join('');
    }

    // ═══ Utilities ═══════════════════════════════════════════════════════

    function esc(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function getInitials(name) {
        if (!name) return '?';
        const parts = name.trim().split(/\s+/);
        return ((parts[0]?.[0] || '') + (parts[1]?.[0] || '')).toUpperCase() || '?';
    }
});