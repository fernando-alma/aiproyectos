<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear proyecto | AI Weekend</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/project-create.css">
    <link rel="stylesheet" href="public/css/footer.css"> 
</head>

<body class="create-project-page">
    <main class="main-content">
        <section class="form-section">
            <div class="form-container">
                <div class="form-header">
                    <h1 class="form-title">Nuevo Proyecto</h1>
                    <p class="form-subtitle">Completa la información para registrar tu equipo</p>
                </div>

                <form class="project-form" id="capitalsForm">
                    
                    <div class="form-group">
                        <label for="group_name">Nombre del Grupo</label>
                        <input type="text" id="group_name" name="group_name" placeholder="Ej: Equipo Alfa" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="title">Título del Proyecto</label>
                        <input type="text" id="title" name="title" placeholder="El nombre de tu increíble idea" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <textarea id="description" name="description" placeholder="¿De qué trata el proyecto? (Máx. 1000 caracteres)" rows="5" maxlength="1000" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Portada del Proyecto (16:9)</label>
                        <div class="file-input-wrapper">
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="link_video">Video Pitch (YouTube)</label>
                        <input type="url" id="link_video" name="link_video" placeholder="https://youtu.be/..." required>
                    </div>

                    <div class="form-group">
                        <label for="members_data">Integrantes</label>
                        <textarea id="members_data" name="members_data" placeholder="Nombre: Juan Pérez, Link: linkedin.com/in/juan..." rows="3" required></textarea>
                        <small>Lista los nombres y enlaces de LinkedIn de tu equipo.</small>
                    </div>

                    <div class="form-group">
                        <label for="link_deploy">URL del Deploy</label>
                        <input type="url" id="link_deploy" name="link_deploy" placeholder="https://mi-proyecto.vercel.app" required>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" class="btn btn-secondary" id="cancelButton">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Crear proyecto</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <?php require_once ("components/footer.php"); ?>

    <script src="public/js/config.js"></script>
    <script src="public/js/session-check.js"></script>
    <script src="public/js/project-create.js"></script>
</body>
</html>