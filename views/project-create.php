<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Proyecto | AIWKND</title>
    <meta name="description" content="Crea un nuevo proyecto para el hackathon AIWKND.">
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/styles.css">
    <link rel="stylesheet" href="public/css/project-form.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>

<body>
    <?php require_once("components/nav.php"); ?>

    <main class="pf-page">
        <div class="pf-shell">

            <!-- Header -->
            <div class="pf-header">
                <a href="project-list" class="pf-back-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7" />
                        <path d="M19 12H5" />
                    </svg>
                    Volver
                </a>
                <h1 class="pf-title">Nuevo Proyecto</h1>
                <p class="pf-subtitle">Completa la información de tu equipo y proyecto</p>
            </div>

            <!-- Form -->
            <form id="projectCreateForm" class="glass-effect" style="border-radius: 20px; overflow: hidden;">
                <div class="pf-grid">

                    <!-- ═══ LEFT COLUMN: Esencial ═══ -->
                    <div class="pf-col">
                        <div class="pf-col-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4" />
                                <path d="M9 18c-4.51 2-5-2-7-2" />
                            </svg>
                            <span>Información Esencial</span>
                        </div>

                        <div class="pf-group">
                            <label class="pf-label" for="title">Título del Proyecto</label>
                            <small class="pf-hint">El nombre oficial de tu proyecto</small>
                            <input type="text" id="title" name="title" class="pf-input"
                                placeholder="Ej: CapyGaming" required>
                        </div>

                        <div class="pf-group">
                            <label class="pf-label" for="group_name">Nombre del Equipo</label>
                            <small class="pf-hint">El nombre de tu grupo o equipo de trabajo</small>
                            <input type="text" id="group_name" name="group_name" class="pf-input"
                                placeholder="Ej: Los Carpinchos" required>
                        </div>

                        <div class="pf-group">
                            <label class="pf-label" for="description">Descripción</label>
                            <small class="pf-hint">Explica de qué trata y qué problema resuelve</small>
                            <textarea id="description" name="description" class="pf-textarea" rows="5"
                                maxlength="2000" placeholder="Describe tu proyecto..." required></textarea>
                            <div class="pf-char-count" id="descCharCount">0 / 2000</div>
                        </div>

                        <div class="pf-group">
                            <label class="pf-label">Portada del Proyecto</label>
                            <small class="pf-hint">Imagen 16:9 recomendada · JPG o PNG</small>
                            <div class="pf-upload-zone" id="uploadZone">
                                <input type="file" id="image" name="image" accept="image/*">
                                <div class="pf-upload-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7" />
                                        <line x1="16" x2="22" y1="5" y2="5" />
                                        <line x1="19" x2="19" y1="2" y2="8" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                    <span>Haz clic o arrastra una imagen</span>
                                    <small>Resoluciones: 1280×720, 1024×576</small>
                                </div>
                                <img class="pf-upload-preview" id="imagePreview" alt="Vista previa">
                            </div>
                        </div>
                    </div>

                    <!-- ═══ RIGHT COLUMN: Extras ═══ -->
                    <div class="pf-col">
                        <div class="pf-col-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                            </svg>
                            <span>Links y Recursos</span>
                        </div>

                        <div class="pf-group">
                            <label class="pf-label" for="link_video">Video Pitch</label>
                            <small class="pf-hint">Link de YouTube con tu presentación</small>
                            <input type="url" id="link_video" name="link_video" class="pf-input"
                                placeholder="https://youtu.be/...">
                        </div>

                        <div class="pf-group">
                            <label class="pf-label" for="link_deploy">URL del Deploy</label>
                            <small class="pf-hint">El link donde tu proyecto está publicado</small>
                            <input type="url" id="link_deploy" name="link_deploy" class="pf-input"
                                placeholder="https://mi-proyecto.vercel.app">
                        </div>

                        <div class="pf-group">
                            <label class="pf-label" for="link_repository">Repositorio</label>
                            <small class="pf-hint">Pega el link de tu repo en GitHub o GitLab</small>
                            <input type="url" id="link_repository" name="link_repository" class="pf-input"
                                placeholder="https://github.com/usuario/proyecto">
                        </div>

                        <div class="pf-group">
                            <label class="pf-label" for="pitch">Pitch en texto</label>
                            <small class="pf-hint">Un resumen corto de tu elevator pitch</small>
                            <input type="text" id="pitch" name="pitch" class="pf-input"
                                placeholder="Resuelve X para Y usando Z">
                        </div>
                    </div>

                </div>

                <!-- Actions -->
                <div class="pf-actions" style="padding: 0 1.75rem 1.75rem;">
                    <button type="button" class="pf-btn pf-btn-cancel" id="cancelBtn">Cancelar</button>
                    <button type="submit" class="pf-btn pf-btn-primary" id="submitBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                        Crear Proyecto
                    </button>
                </div>
            </form>

        </div>
    </main>

    <?php require_once("components/footer.php"); ?>
    <script src="public/js/config.js"></script>
    <script src="public/js/session-check.js"></script>
    <script src="public/js/project-create.js"></script>
</body>

</html>