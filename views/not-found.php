<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error 404 - Página no encontrada</title>
  <link rel="stylesheet" href="public/css/not-found.css">
  <link rel="stylesheet" href="public/css/footer.css"> 
</head>
<body>
  <!-- Error 404 Section -->
  <section id="error-404" class="form-section">
    <div class="form-container">
      <h1 class="form-title" style="color: #d9534f;">Error 404 - Página no encontrada</h1>
      <p style="text-align: center; font-size: 18px; margin-top: 12px;">
        Lo sentimos, la página que estás buscando no existe o ha sido movida.
      </p>
      <div style="display: flex; justify-content: center; margin-top: 32px;">
        <a href="index" class="submit-button" style="text-align: center; text-decoration: none;">Volver al inicio</a>
      </div>
    </div>
  </section>

  <?php require_once("components/nav.php"); ?>
  <?php require_once ("components/footer.php"); ?>
</body>
</html>
