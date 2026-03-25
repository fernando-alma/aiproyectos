<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar proyecto</title>
    <link rel="stylesheet" href="public/css/project-edit.css">
    <link rel="stylesheet" href="public/css/footer.css"> 
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <img src="public/images/AIWKND-negro-solo.png" alt="AIWKND" class="logo">
                <nav class="desktop-nav">
                    <a href="index" class="nav-link">
                        <span class="nav-icon"></span>
                        <span class="nav-text">Inicio</span>
                    </a>
                    <a href="project-list" class="nav-link">
                        <span class="nav-icon"></span>
                        <span class="nav-text">Proyectos</span>
                    </a>
                </nav>
            </div>
            <nav class="desktop-nav-right">
                <a href="profile" class="nav-link">
                    <span class="nav-icon"></span>
                    <span class="nav-text">Cuenta</span>
                </a>
            </nav>
        </header>
        
        <section class="form-section">
        <div class="form-container">
            <h1 class="form-title">Editar proyecto</h1>
            <form class="capitals-form" id="editProjectForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title"></label>
                    <input type="text" id="title" name="title" placeholder="Ingresa el título del proyecto" required>
                </div>
                <div class="form-group">
                    <label for="description"></label>
                    <textarea id="description" name="description" placeholder="Ingresa la descripción del proyecto. Puedes usar formato estructurado con viñetas (•) y secciones (Descripción:, Valor diferencial:)" required rows="12"></textarea>
                    <div class="formatting-help">
                        <small> Usa "•" o "-" para viñetas, y ":" para títulos de sección, ejemplo: "Descripción:"</small>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pitch"></label>
                    <input type="text" id="pitch" name="pitch" placeholder="Ingresa el enlace de tu pitch">
                </div>
                <div class="form-group">
                    <label for="image"></label>
                    <p class="image-label-text">Seleccionar imagen de proyecto</p>
                    <input type="file" id="image" name="image" accept="image/*">
                    <div id="imagePreview" style="margin-top: 10px;"></div>
                    <p class="resolution-info">Resoluciones recomendadas de imagen: 640×480, 800×600, 1024×768, 1280×688</p>
                </div>
                <div>
                    <button type="submit" class="submit-button">Guardar cambios</button>
                    <button type="button" class="submit-button" id="previewButton">Vista previa</button>
                    <button type="button" class="submit-button" id="cancelButton">Cancelar</button>
                </div>
            </form>
        </div>
    </section>

        <!-- Desktop Bottom Nav -->
        <nav class="desktop-bottom-nav">
            <img src="public/images/AIWKND-negro-solo.png" alt="AIWKND" class="desktop-bottom-logo">
        </nav>
        
        <?php require_once("components/nav.php"); ?>
        <?php require_once ("components/footer.php"); ?>
    </div>
    <script src="public/js/config.js"></script>
    <script src="public/js/session-check.js"></script>
    <script src="public/js/project-edit.js"></script>
</body>
</html>
