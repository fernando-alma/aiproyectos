const API_BASE = CONFIG.API_BASE;
const CURRENT_SLUG = CONFIG.SLUG;

document.addEventListener('DOMContentLoaded', () => {
    const allProjectsGrid = document.getElementById('allProjectsGrid');
    const paginationControls = document.getElementById('paginationControls');
    const searchInput = document.getElementById('searchProjectsInput');
    let currentPage = 1;
    const projectsPerPage = 8;
    let cachedProjects = [];
    let filteredProjects = [];

    async function fetchDashboardsAndProjects() {
        try {
            allProjectsGrid.innerHTML = '<p>Cargando proyectos...</p>';
            const dashboardsResp = await fetch(`${API_BASE}dashboards`);
            if (!dashboardsResp.ok) {
                throw new Error(`Error dashboards ${dashboardsResp.status}`);
            }
            const dashboardsData = await dashboardsResp.json();
            if (!dashboardsData.success || !Array.isArray(dashboardsData.data) || dashboardsData.data.length === 0) {
                allProjectsGrid.innerHTML = '<p>No hay dashboards disponibles.</p>';
                return;
            }

            const firstDashboard = dashboardsData.data[0];
            const slug = firstDashboard.slug || firstDashboard.dashboard_slug || firstDashboard.id || '';
            if (!slug) {
                allProjectsGrid.innerHTML = '<p>No se pudo determinar el slug del dashboard.</p>';
                return;
            }

            const projectsResp = await fetch(`${API_BASE}project/getProjects?slug=${CURRENT_SLUG}`);
            if (!projectsResp.ok) {
                throw new Error(`Error projects ${projectsResp.status}`);
            }
            const projectsData = await projectsResp.json();
            if (!projectsData.success || !Array.isArray(projectsData.data)) {
                allProjectsGrid.innerHTML = '<p>Error al cargar proyectos.</p>';
                return;
            }

            cachedProjects = projectsData.data;
            filteredProjects = cachedProjects;
            currentPage = 1;
            renderPage();
        } catch (error) {
            console.error('Error fetching dashboards/projects:', error);
            allProjectsGrid.innerHTML = '<p>No se pudieron cargar los proyectos. Inténtalo de nuevo más tarde.</p>';
        }
    }

    function renderPage() {
        const source = filteredProjects && Array.isArray(filteredProjects) ? filteredProjects : cachedProjects;
        const totalPages = Math.max(1, Math.ceil(source.length / projectsPerPage));
        const start = (currentPage - 1) * projectsPerPage;
        const end = start + projectsPerPage;
        const pageItems = source.slice(start, end);
        renderProjects(pageItems);
        renderPagination(totalPages, currentPage);
    }

    function renderProjects(projects) {
        allProjectsGrid.innerHTML = '';
        if (!projects || projects.length === 0) {
            allProjectsGrid.innerHTML = '<p>No hay proyectos disponibles en este momento.</p>';
            return;
        }

        projects.forEach(project => {
            const projectCard = document.createElement('div');
            projectCard.className = 'project-card';
            const status = project.status === 'completed' ? 'status-completed' : 'status-in-progress';
            const statusText = project.status === 'completed' ? 'Completado' : 'En Progreso';
            
            // Verificar si el usuario es miembro del proyecto
            let membershipBadge = '';
            if (window.isAuthenticated && window.isProjectMember && window.isProjectMember(project.id)) {
                membershipBadge = '<span class="membership-badge">Ya eres miembro</span>';
            }
            
            // Obtener la primera letra del título en mayúscula
            const firstLetter = (project.title || 'P').charAt(0).toUpperCase();
            
            // Limitar la descripción a 65 caracteres
            const description = project.description || '';
            const truncatedDescription = description.length > 65 ? description.substring(0, 65) + '...' : description;
            
             // Detectar si es móvil o tablet (< 1024px)
             const isMobile = window.innerWidth < 1024;
             const imageSrc = project.image ? `data:image/jpeg;base64,${project.image}` : null;
             const backgroundStyle = imageSrc ? `background-image: url('${imageSrc}'); background-size: cover; background-position: center; background-repeat: no-repeat;` : '';
             projectCard.innerHTML = `
                 <div class="project-icon">
                 <div class="project-icon-letter" style="${backgroundStyle}">${firstLetter}</div>
</div>
                 <div class="project-info">
                     <h3>${escapeHtml(truncateTitle(project.title || ''))}</h3>
                     <p class="project-description">${escapeHtml(truncatedDescription)}</p>
                     ${!isMobile ? `<a href="project-detail?id=${encodeURIComponent(project.id)}" class="btn-ver-mas">Ver más</a>` : ''}
                     ${membershipBadge}
                 </div>
             `;
             
             // Agregar evento click a toda la tarjeta en móvil
             if (isMobile) {
                 projectCard.style.cursor = 'pointer';
                 projectCard.addEventListener('click', () => {
                     window.location.href = `project-detail?id=${encodeURIComponent(project.id)}`;
                 });
             }
            allProjectsGrid.appendChild(projectCard);
        });
    }

    function renderPagination(totalPages, page) {
        paginationControls.innerHTML = '';
        if (totalPages <= 1) return;

        const prevButton = document.createElement('button');
        prevButton.className = 'pagination-button';
        prevButton.disabled = page === 1;
        prevButton.textContent = 'Anterior';
        prevButton.addEventListener('click', () => {
            currentPage = Math.max(1, currentPage - 1);
            renderPage();
        });
        paginationControls.appendChild(prevButton);

        // Lógica de truncamiento de páginas
        // Detectar si es móvil (ancho < 1024px)
        const isMobile = window.innerWidth < 1024;
        let startPage, endPage;

        if (isMobile) {
            // Versión móvil: SIEMPRE mostrar solo 2 páginas
            if (totalPages <= 2) {
                // Si hay 2 o menos páginas, mostrar todas
                startPage = 1;
                endPage = totalPages;
            } else {
                // Siempre mostrar la página actual y la siguiente
                startPage = page;
                endPage = Math.min(page + 1, totalPages);
            }
        } else {
            // Versión desktop: mostrar hasta 4 páginas
            const maxVisiblePages = 4;
            if (totalPages <= maxVisiblePages) {
                // Si hay 4 o menos páginas, mostrar todas
                startPage = 1;
                endPage = totalPages;
            } else {
                // Calcular qué páginas mostrar basándose en la página actual
                if (page <= 2) {
                    // Si estamos en las primeras páginas, mostrar páginas 1-4
                    startPage = 1;
                    endPage = maxVisiblePages;
                } else if (page >= totalPages - 1) {
                    // Si estamos en las últimas páginas, mostrar las últimas 4
                    startPage = totalPages - maxVisiblePages + 1;
                    endPage = totalPages;
                } else {
                    // Si estamos en el medio, mostrar la página actual y 3 más (2 a cada lado)
                    startPage = page - 1;
                    endPage = page + 2;
                }
            }
        }

        // Mostrar páginas calculadas
        for (let i = startPage; i <= endPage; i++) {
            const pageButton = document.createElement('button');
            pageButton.className = 'pagination-button';
            if (i === page) {
                pageButton.classList.add('active');
            }
            pageButton.textContent = i;
            pageButton.addEventListener('click', () => {
                currentPage = i;
                renderPage();
            });
            paginationControls.appendChild(pageButton);
        }

        const nextButton = document.createElement('button');
        nextButton.className = 'pagination-button';
        nextButton.disabled = page === totalPages;
        nextButton.textContent = 'Siguiente';
        nextButton.addEventListener('click', () => {
            currentPage = Math.min(totalPages, currentPage + 1);
            renderPage();
        });
        paginationControls.appendChild(nextButton);
    }

    function normalizeString(value) {
        return String(value || '')
            .normalize('NFKD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function applySearchFilter(query) {
        const normalizedQuery = normalizeString(query);
        if (!normalizedQuery) {
            filteredProjects = cachedProjects.slice();
        } else {
            filteredProjects = cachedProjects.filter(p => {
                const title = normalizeString(p.title || '');
                return title.includes(normalizedQuery);
            });
        }
        currentPage = 1;
        renderPage();
    }

    function debounce(fn, delay) {
        let t;
        return function(...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function firstSafe(obj, key) {
        return obj && obj[key] ? obj[key] : '';
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function truncateTitle(title) {
        if (!title) return '';
        
        // Truncamiento más agresivo para títulos muy largos
        const maxLength = 20; // Reducido para ser más estricto
        
        // Forzar truncamiento para cualquier título que supere la longitud máxima
        if (title.length > maxLength) {
            const truncated = title.substring(0, maxLength) + '...';
            console.log(`FORZANDO truncamiento: "${title}" (${title.length} chars) -> "${truncated}"`);
            return truncated;
        }
        
        console.log(`Título no truncado: "${title}" (${title.length} chars)`);
        return title;
    }

     // Función para re-renderizar cuando cambie el tamaño de ventana
    function handleResize() {
        if (cachedProjects.length > 0) {
            renderPage();
        }
    }

     // Escuchar cambios de tamaño de ventana
    window.addEventListener('resize', handleResize);

    if (searchInput) {
        const debouncedSearch = debounce((e) => applySearchFilter(e.target.value), 250);
        searchInput.addEventListener('input', debouncedSearch);
    }
    
    fetchDashboardsAndProjects();
});
