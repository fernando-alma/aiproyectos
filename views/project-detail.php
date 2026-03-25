<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIWKND - Detalle de Proyecto</title>
    <link rel="stylesheet" href="public/css/nav-styles.css">
    <link rel="stylesheet" href="public/css/project-detail.css">
    <link rel="stylesheet" href="public/css/footer.css">
</head>

<body>
    <?php require_once("components/nav.php"); ?>
    <div class="container">

        <section id="project-detail" class="form-section">
            <span class="back-btn" style="cursor:pointer; position: absolute; top: 20px; left: 20px;" onclick="window.location.href='project-list'">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left-icon lucide-arrow-left">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
            </span>
            <div class="form-container">
                <div class="project-detail-content" id="projectDetailContent">
                    <!-- Project details will be loaded here via JavaScript -->
                </div>
            </div>
        </section>

        <!-- Modal de confirmaci��n de eliminaci��n de proyecto -->
        <div id="deleteModal" class="delete-modal-overlay" style="display: none;">
            <div class="delete-modal-content" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
                <div style="margin-bottom: 1rem;">
                    <h3 id="deleteModalTitle" class="delete-modal-title">�0�7Est��s seguro?</h3>
                    <p class="delete-modal-message" style="color:#c4c4c4; line-height:1.7;">
                        Se eliminar�� permanentemente el proyecto y todos sus datos.
                    </p>
                </div>
                <div class="delete-modal-buttons">
                    <button id="deleteCancelBtn" class="delete-modal-cancel-btn">Cancelar</button>
                    <button id="deleteConfirmBtn" class="delete-modal-confirm-btn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once("components/footer.php"); ?>
</body>

<script src="public/js/config.js"></script>
<script src="public/js/session-check.js"></script>
<script src="public/js/project-detail.js"></script>

</html>