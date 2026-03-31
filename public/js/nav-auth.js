document.addEventListener('DOMContentLoaded', () => {
    const desktopAuthContainer = document.getElementById('desktop-auth-links');
    const mobileAuthContainer = document.getElementById('mobile-auth-links');
    
    function renderAuthLinks() {
        // En lugar de leer el jwt_token local, leemos el global provisto por session-check
        const isLogged = window.isAuthenticated ? window.isAuthenticated() : !!localStorage.getItem('token');
        const token = localStorage.getItem('token') || sessionStorage.getItem('token');
        
        let dHtml = '';
        let mHtml = '';
        
        if (!isLogged) {
            dHtml = `
                <a href="login" class="bottom-nav-item"><span>Iniciar sesión</span></a>
                <a href="register" class="register-button nav-button">Regístrate gratis</a>
            `;
            mHtml = `
                <a href="login" class="register-button nav-button" style="text-align: center; background: transparent; border: 1px solid rgba(255,255,255,0.4);">Iniciar sesión</a>
                <a href="register" class="register-button nav-button" style="text-align: center;">Regístrate gratis</a>
            `;
        } else {
            let role = 'user';
            
            if (token) {
                try {
                    const base64Url = token.split('.')[1];
                    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
                    const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join(''));
                    const payload = JSON.parse(jsonPayload);
                    if (payload.data?.role) role = payload.data.role;
                    else if (payload.role) role = payload.role;
                } catch (error) { console.error("Error decodificando JWT:", error); }
            }
            
            const userData = window.getUserData ? window.getUserData() : { userName: localStorage.getItem("userName") };
            const firstName = userData.userName ? userData.userName.split(" ")[0] : "Usuario";
            
            let adminLinkD = '';
            let adminLinkM = '';
            if (role === 'admin' || role === 'superadmin') {
                adminLinkD = `<a href="admin-panel" class="bottom-nav-item"><span style="color: var(--aiw-cyan);">Panel Súper Admin</span></a>`;
                adminLinkM = `<a href="admin-panel" class="bottom-nav-item"><span style="color: var(--aiw-cyan);">⭐ Panel Súper Admin</span></a>`;
            }
            
            dHtml = `
                <div style="display: flex; align-items: center; gap: 8px; color: white;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--aiw-pink)"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span style="font-weight: 600;">Hola ${firstName}!</span>
                </div>
                ${adminLinkD}
                <a href="profile" class="bottom-nav-item"><span>Mi Perfil</span></a>
                <a href="#" onclick="logoutUser(event)" class="bottom-nav-item"><span style="color: rgba(255,100,100,0.8);">Cerrar Sesión</span></a>
            `;
            
            mHtml = `
                <div style="display: flex; align-items: center; gap: 8px; color: white; margin-bottom: 10px; padding: 0 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--aiw-pink)"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span style="font-weight: 600;">Hola ${firstName}!</span>
                </div>
                ${adminLinkM}
                <a href="profile" class="bottom-nav-item"><span>Mi Perfil</span></a>
                <a href="#" onclick="logoutUser(event)" class="bottom-nav-item"><span style="color: rgba(255,100,100,0.8);">Cerrar Sesión</span></a>
            `;
        }
        
        if (desktopAuthContainer) desktopAuthContainer.innerHTML = dHtml;
        if (mobileAuthContainer) mobileAuthContainer.innerHTML = mHtml;
    }

    renderAuthLinks();
});

window.logoutUser = function(e) {
    if(e) e.preventDefault();
    if(window.logout) {
        window.logout(); // Usa el engine seguro de session-check
    } else {
        localStorage.clear();
        sessionStorage.clear();
        window.location.href = 'login';
    }
};
