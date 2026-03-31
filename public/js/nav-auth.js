document.addEventListener('DOMContentLoaded', () => {
    const desktopAuthContainer = document.getElementById('desktop-auth-links');
    const mobileAuthContainer = document.getElementById('mobile-auth-links');
    
    function renderAuthLinks() {
        const token = localStorage.getItem('jwt_token');
        let dHtml = '';
        let mHtml = '';
        
        if (!token) {
            dHtml = `
                <a href="login" class="bottom-nav-item" style="color: white; text-decoration: none; font-weight: 500;">Iniciar Sesión</a>
                <a href="register" class="aiw-btn btn-gradient">Regístrate gratis</a>
            `;
            mHtml = `
                <a href="login" class="aiw-btn btn-outline btn-full">Iniciar Sesión</a>
                <a href="register" class="aiw-btn btn-gradient btn-full">Regístrate gratis</a>
            `;
        } else {
            let role = 'user';
            
            try {
                const base64Url = token.split('.')[1];
                const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
                const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join(''));
                const payload = JSON.parse(jsonPayload);
                if (payload.data?.role) role = payload.data.role;
                else if (payload.role) role = payload.role;
            } catch (error) { console.error("Error decodificando JWT:", error); }
            
            let adminLinkD = '';
            let adminLinkM = '';
            if (role === 'admin' || role === 'superadmin') {
                adminLinkD = `<a href="admin-panel" class="bottom-nav-item" style="color: var(--aiw-cyan); text-decoration:none; font-weight:500;">Panel Admin</a>`;
                adminLinkM = `<a href="admin-panel" class="aiw-btn btn-outline btn-full" style="color: var(--aiw-cyan); border-color: rgba(0,245,234,0.3);">💎 Panel Admin</a>`;
            }
            
            dHtml = `
                ${adminLinkD}
                <a href="profile" class="bottom-nav-item" style="text-decoration:none; color:white; font-weight:500;">Mi Perfil</a>
                <a href="#" onclick="logoutUser(event)" class="bottom-nav-item" style="text-decoration:none; color: var(--aiw-pink); font-weight:500;">Cerrar Sesión</a>
            `;
            
            mHtml = `
                ${adminLinkM}
                <a href="profile" class="aiw-btn btn-outline btn-full">🙋‍♂️ Mi Perfil</a>
                <a href="#" onclick="logoutUser(event)" class="aiw-btn btn-outline btn-full" style="color: var(--aiw-pink); border-color: rgba(237,30,121,0.3);">🔴 Cerrar Sesión</a>
            `;
        }
        
        if (desktopAuthContainer) desktopAuthContainer.innerHTML = dHtml;
        if (mobileAuthContainer) mobileAuthContainer.innerHTML = mHtml;
    }

    renderAuthLinks();
});

window.logoutUser = function(e) {
    if(e) e.preventDefault();
    localStorage.removeItem('jwt_token');
    window.location.href = 'login';
};
