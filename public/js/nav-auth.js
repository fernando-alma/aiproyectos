/**
 * nav-auth.js — FASE 4.5: Navbar Inteligente
 *
 * Desktop: Avatar + nombre → dropdown flotante glass-effect
 * Mobile: links ordenados dentro del menú hamburguesa
 * Condicional: "Crear Proyecto" solo si admin O 0 proyectos
 * Condicional: "Panel Súper Admin" solo si admin
 */
document.addEventListener('DOMContentLoaded', async () => {
    const desktopAuthContainer = document.getElementById('desktop-auth-links');
    const mobileAuthContainer = document.getElementById('mobile-auth-links');

    const isLogged = window.isAuthenticated ? window.isAuthenticated() : !!localStorage.getItem('token');
    const token = localStorage.getItem('token') || sessionStorage.getItem('token');

    if (!isLogged) {
        renderGuest();
        return;
    }

    // ─── Decode JWT ────────────────────────────────────────────────────
    let role = 'user';
    if (token) {
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join(''));
            const payload = JSON.parse(jsonPayload);
            role = payload.data?.role || payload.role || 'user';
        } catch (e) { console.error("Error JWT:", e); }
    }

    const userData = window.getUserData ? window.getUserData() : { userName: localStorage.getItem("userName") };
    const fullName = userData.userName || "Usuario";
    const firstName = fullName.split(" ")[0];
    const initials = getInitials(fullName);
    const isAdmin = role === 'admin' || role === 'superadmin';

    // ─── Check project ownership (for "Crear Proyecto" visibility) ─────
    let hasProjects = false;
    try {
        const res = await fetch(`${CONFIG.API_BASE}projects/user`, {
            headers: { Authorization: `Bearer ${token}` }
        });
        if (res.ok) {
            const data = await res.json();
            const projects = data.projects || data.data || [];
            hasProjects = Array.isArray(projects) && projects.length > 0;
        }
    } catch { /* silently fail, default to no projects */ }

    const showCreateProject = isAdmin || !hasProjects;

    renderDesktop(firstName, initials, isAdmin, showCreateProject);
    renderMobile(firstName, initials, isAdmin, showCreateProject);

    // ─── Renderers ─────────────────────────────────────────────────────

    function renderGuest() {
        if (desktopAuthContainer) {
            desktopAuthContainer.innerHTML = `
                <a href="login" class="bottom-nav-item"><span>Iniciar sesión</span></a>
                <a href="register" class="register-button nav-button">Regístrate gratis</a>
            `;
        }
        if (mobileAuthContainer) {
            mobileAuthContainer.innerHTML = `
                <a href="login" class="register-button nav-button" style="text-align:center;background:transparent;border:1px solid rgba(255,255,255,0.4);">Iniciar sesión</a>
                <a href="register" class="register-button nav-button" style="text-align:center;">Regístrate gratis</a>
            `;
        }
    }

    function renderDesktop(name, initials, admin, showCreate) {
        if (!desktopAuthContainer) return;

        desktopAuthContainer.innerHTML = `
            <div class="nav-user-trigger" id="navUserTrigger">
                <div class="nav-avatar">${esc(initials)}</div>
                <span class="nav-user-name">${esc(name)}</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="nav-chevron" id="navChevron">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </div>

            <div class="nav-dropdown glass-effect" id="navDropdown">
                <a href="profile" class="nav-dd-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Mi Perfil
                </a>
                ${showCreate ? `
                <a href="project-create" class="nav-dd-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                    Crear Proyecto
                </a>` : ''}
                ${admin ? `
                <a href="admin-panel" class="nav-dd-item nav-dd-item--admin">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                    Panel Súper Admin
                </a>` : ''}
                <div class="nav-dd-divider"></div>
                <a href="#" class="nav-dd-item nav-dd-item--logout" onclick="logoutUser(event)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Cerrar Sesión
                </a>
            </div>
        `;

        // Dropdown toggle
        const trigger = document.getElementById('navUserTrigger');
        const dropdown = document.getElementById('navDropdown');
        const chevron = document.getElementById('navChevron');

        if (trigger && dropdown) {
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const open = dropdown.classList.toggle('active');
                chevron.classList.toggle('rotated', open);
            });

            document.addEventListener('click', (e) => {
                if (!trigger.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                    chevron.classList.remove('rotated');
                }
            });
        }
    }

    function renderMobile(name, initials, admin, showCreate) {
        if (!mobileAuthContainer) return;

        mobileAuthContainer.innerHTML = `
            <div class="nav-mobile-user">
                <div class="nav-avatar nav-avatar--mobile">${esc(initials)}</div>
                <div>
                    <div class="nav-mobile-name">${esc(name)}</div>
                    <div class="nav-mobile-role">${admin ? 'Administrador' : 'Weekener'}</div>
                </div>
            </div>

            <a href="profile" class="bottom-nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Mi Perfil</span>
            </a>

            ${showCreate ? `
            <a href="project-create" class="bottom-nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                <span>Crear Proyecto</span>
            </a>` : ''}

            ${admin ? `
            <a href="admin-panel" class="bottom-nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                <span style="color: var(--aiw-cyan);">Panel Súper Admin</span>
            </a>` : ''}

            <div style="height:1px;background:rgba(255,255,255,0.08);margin:0.5rem 0;"></div>

            <a href="#" onclick="logoutUser(event)" class="bottom-nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                <span style="color: var(--aiw-pink);">Cerrar Sesión</span>
            </a>
        `;
    }

    // ─── Utilities ─────────────────────────────────────────────────────
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

window.logoutUser = function(e) {
    if(e) e.preventDefault();
    if(window.logout) {
        window.logout();
    } else {
        localStorage.clear();
        sessionStorage.clear();
        window.location.href = 'login';
    }
};
