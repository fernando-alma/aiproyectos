<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectos</title>
    
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/project-list.css"> 
    <link rel="stylesheet" href="public/css/footer.css"> 
</head>
<body>

    <section class="projects-section">
        <div class="projects-container container"> 
            <h1 class="projects-title section-title">Catálogo de Proyectos</h1>

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

    <?php require_once("components/nav.php"); ?>
    <?php require_once ("components/footer.php"); ?> 

    <script src="public/js/project-list.js"></script>
</body>
</html>