<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos</title>
    
    <link rel="stylesheet" href="public/css/nav-styles.css?v=4.5">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/project-list.css"> 
    <link rel="stylesheet" href="public/css/footer.css"> 
</head>
<body>
    <?php require_once("components/nav.php"); ?>
    <section class="projects-section">
        <div class="projects-container container"> 
            <h1 class="projects-title section-title">Galería de AiProyectos</h1>
            <p class="projects-subtitle">Explora las soluciones creadas por nuestra comunidad. Prototipos, ideas y demos funcionales nacidos en 48 horas de pura creatividad.</p>

            <div class="search-bar" id="search-bar">
            <div class="position: relative;">
                <input
                id="q"
                name="q"
                type="search"
                placeholder="Buscar..."
                autocomplete="off"
                aria-label="Término de búsqueda"
                />
            </div>
                <button type="submit" class="search-btn" aria-label="Buscar">Buscar</button>
            </div>
            
            <div id="projects-overview" class="projects-overview">
                <div class="projects-grid" id="allProjectsGrid">
                    <p>Cargando proyectos...</p>
                </div>
                <div class="pagination-controls" id="paginationControls"></div>
            </div>
        </div>
    </section>

    <?php require_once ("components/footer.php"); ?> 

    <script src="public/js/project-list.js"></script>
</body>
</html>