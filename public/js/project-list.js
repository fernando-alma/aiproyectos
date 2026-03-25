const API_BASE = "https://aiday-utn-sanrafael-2025.alphadocere.cl/backend/public/";
const CURRENT_SLUG = "hola";

document.addEventListener("DOMContentLoaded", () => {
  const allProjectsGrid = document.getElementById("allProjectsGrid");
  const paginationControls = document.getElementById("paginationControls");
  const searchBar = document.getElementById("search-bar");

  const initLoader = loader()
  initLoader.show();

  let currentPage = 1;
  const projectsPerPage = 6;
  let cachedProjects = [];

  async function fetchDashboardsAndProjects() {
    try {
      const dashboardsResp = await fetch(`${API_BASE}dashboards`);
      if (!dashboardsResp.ok) {
        throw new Error(`Error dashboards ${dashboardsResp.status}`);
      }
      console.log(await dashboardsResp);
      const dashboardsData = await dashboardsResp.json();
      if (
        !dashboardsData.success ||
        !Array.isArray(dashboardsData.data) ||
        dashboardsData.data.length === 0
      ) {
        allProjectsGrid.innerHTML = "<p>No hay dashboards disponibles.</p>";
        return;
      }

      const firstDashboard = dashboardsData.data[0];
      const slug =
        firstDashboard.slug ||
        firstDashboard.dashboard_slug ||
        firstDashboard.id ||
        "";
      if (!slug) {

        allProjectsGrid.innerHTML =
          "<p>No se pudo determinar el slug del dashboard.</p>";
        return;
      }

      const projectsResp = await fetch(
        `${API_BASE}project/getProjects?slug=${CURRENT_SLUG}`
      );
      if (!projectsResp.ok) {
        throw new Error(`Error projects ${projectsResp.status}`);
      }
      const projectsData = await projectsResp.json();
      if (!projectsData.success || !Array.isArray(projectsData.data)) {

        allProjectsGrid.innerHTML = "<p>Error al cargar proyectos.</p>";
        return;
      }

      cachedProjects = projectsData.data;
      currentPage = 1;
      renderPage();
    } catch (error) {
      console.error("Error fetching dashboards/projects:", error);

      allProjectsGrid.innerHTML =
        "<p>No se pudieron cargar los proyectos. Inténtalo de nuevo más tarde.</p>";
    } finally {
      initLoader.hide();
    }
  }

  function renderPage() {
    const totalPages = Math.max(
      1,
      Math.ceil(cachedProjects.length / projectsPerPage)
    );
    const start = (currentPage - 1) * projectsPerPage;
    const end = start + projectsPerPage;
    const pageItems = cachedProjects.slice(start, end);
    renderProjects(pageItems);
    renderPagination(totalPages, currentPage);
  }

  const limitCharacters = (str, maxLength) => {
    if (str.length <= maxLength) return str;
    return str.slice(0, maxLength) + "...";
  }

  function renderProjects(projects) {
    allProjectsGrid.innerHTML = "";
    if (!projects || projects.length === 0) {
      allProjectsGrid.innerHTML =
        "<p>No hay proyectos disponibles en este momento.</p>";
      return;
    }

    projects.forEach((project) => {
      const projectCard = document.createElement("div");
      const projectImg = document.createElement("img");
      projectCard.classList.add("project-card");
      projectImg.classList.add("project-image");
      projectCard.appendChild(projectImg);

      const status =
        project.status === "completed"
          ? "status-completed"
          : "status-in-progress";
      const statusText = "";
      const dashSlug =
        project.dashboard_slug || firstSafe(project.dashboard, "slug") || "";
      const dashName =
        project.dashboard_name ||
        firstSafe(project.dashboard, "title") ||
        dashSlug ||
        "Dashboard";

      // HTML structure for the new catalog design
      projectCard.innerHTML += projectImg;
      projectCard.innerHTML = `
                <div class="project-header" style="width: 100%;">
                </div>
                <div class="project-meta">
                    <h3>${escapeHtml(
        project.title || "Proyecto sin Título"
      )}</h3>
                    <p>${escapeHtml(
        project.description || "Sin descripción."
      )}</p>
                    <span class="status ${status}">${statusText}</span>
                </div>
                <a href="project-detail?id=${encodeURIComponent(
        project.id
      )}" class="btn-ver-mas">Ver detalles</a>
            `;

      projectImg.src = project.image ? `backend/public/${project.image}` : "public/images/fondos/fondo2.jpg"
      const firstChildProject = projectCard.querySelector(".project-header");

      firstChildProject.appendChild(projectImg);
      allProjectsGrid.appendChild(projectCard);
    });
  }

  function renderPagination(totalPages, page) {
    paginationControls.innerHTML = "";
    if (totalPages <= 1) return;

    const prevButton = document.createElement("button");
    prevButton.className = "pagination-button";
    prevButton.disabled = page === 1;
    prevButton.textContent = "Anterior";
    prevButton.addEventListener("click", () => {
      currentPage = Math.max(1, currentPage - 1);
      renderPage();
    });
    paginationControls.appendChild(prevButton);

    for (let i = 1; i <= totalPages; i++) {
      const pageButton = document.createElement("button");
      pageButton.className = "pagination-button";
      if (i === page) {
        pageButton.classList.add("active");
      }
      pageButton.textContent = i;
      pageButton.addEventListener("click", () => {
        currentPage = i;
        renderPage();
      });
      paginationControls.appendChild(pageButton);
    }

    const nextButton = document.createElement("button");
    nextButton.className = "pagination-button";
    nextButton.disabled = page === totalPages;
    nextButton.textContent = "Siguiente";
    nextButton.addEventListener("click", () => {
      currentPage = Math.min(totalPages, currentPage + 1);
      renderPage();
    });
    paginationControls.appendChild(nextButton);
  }

  function firstSafe(obj, key) {
    return obj && obj[key] ? obj[key] : "";
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function searchProjects(query) {
    const filteredProjects = cachedProjects.filter((project) =>
      project.title.toLowerCase().includes(query.toLowerCase())
    );
    currentPage = 1;
    renderProjects(filteredProjects);
    renderPagination(
      Math.max(1, Math.ceil(filteredProjects.length / projectsPerPage)),
      currentPage
    );
  }

  searchBar.addEventListener("input", (e) => {
    const query = e.target.value;
    searchProjects(query);
  });

  function loader() {
    const loaderContainer = document.createElement("div");
    loaderContainer.classList.add("loader-container");
    loaderContainer.innerHTML = `<svg 
    xmlns="http://www.w3.org/2000/svg"
     width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" 
    stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-loader-circle-icon lucide-loader-circle">
    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
    </svg>`;
    return {
      show() {
        document.body.appendChild(loaderContainer);
      },
      hide() {
        loaderContainer.remove();
      }
    };
  };

  fetchDashboardsAndProjects();
});
