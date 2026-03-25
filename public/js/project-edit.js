const API_BASE = CONFIG.API_BASE;

// Función para formato a la descripción del proyecto (igual que en project-detail.js)
function formatProjectDescription(description) {
    if (!description) return '';
    
    // Dividir la descripción en líneas
    const lines = description.split('\n').filter(line => line.trim());
    
    let formattedHTML = '';
    let currentSection = '';
    let bulletPoints = [];
    let isFirstLine = true;
    let hasProcessedInitialBullets = false;
    
    let i = 0;
    while (i < lines.length) {
        const line = lines[i].trim();
        
        // La primera línea siempre es el título
        if (isFirstLine) {
            formattedHTML += `<div class="project-title-numbered">${line}</div>`;
            isFirstLine = false;
            i++;
            continue;
        }
        
        // Si es una viñeta y no hemos procesado las viñetas iniciales aún
        if (/^[•\-]\s/.test(line) && !hasProcessedInitialBullets) {
            bulletPoints.push(line.replace(/^[•\-]\s/, ''));
            i++;
            continue;
        }
        
        // Si ya no es una viñeta, procesar las viñetas iniciales acumuladas
        if (bulletPoints.length > 0 && !hasProcessedInitialBullets) {
            formattedHTML += '<ul class="project-bullet-points">';
            bulletPoints.forEach(point => {
                formattedHTML += `<li>${point}</li>`;
            });
            formattedHTML += '</ul>';
            bulletPoints = [];
            hasProcessedInitialBullets = true;
        }
        
        // Detectar secciones (Descripción:, Valor diferencial:, etc.)
        if (line.endsWith(':')) {
            currentSection = line;
            formattedHTML += `<div class="project-section-title">${currentSection}</div>`;
        }
        // Si es una viñeta dentro de una sección o después de secciones
        else if (/^[•\-]\s/.test(line)) {
            // Si no hay viñetas acumuladas, empezar una nueva lista
            if (bulletPoints.length === 0) {
                formattedHTML += '<ul class="project-bullet-points">';
            }
            bulletPoints.push(line.replace(/^[•\-]\s/, ''));
        }
        // Contenido de sección
        else if (currentSection && line) {
            // Si hay viñetas pendientes, procesarlas antes del contenido
            if (bulletPoints.length > 0) {
                bulletPoints.forEach(point => {
                    formattedHTML += `<li>${point}</li>`;
                });
                formattedHTML += '</ul>';
                bulletPoints = [];
            }
            formattedHTML += `<div class="project-section-content">${line}</div>`;
        }
        // Texto general
        else if (line) {
            // Si hay viñetas pendientes, procesarlas antes del texto general
            if (bulletPoints.length > 0) {
                bulletPoints.forEach(point => {
                    formattedHTML += `<li>${point}</li>`;
                });
                formattedHTML += '</ul>';
                bulletPoints = [];
            }
            formattedHTML += `<div class="project-general-text">${line}</div>`;
        }
        
        i++;
    }
    
    // Si quedan viñetas sin procesar al final
    if (bulletPoints.length > 0) {
        bulletPoints.forEach(point => {
            formattedHTML += `<li>${point}</li>`;
        });
        formattedHTML += '</ul>';
    }
    
    return formattedHTML;
}

document.addEventListener("DOMContentLoaded", () => {
  const editProjectForm = document.getElementById("editProjectForm");
  const urlParams = new URLSearchParams(window.location.search);
  const projectId = urlParams.get('id');
  const imageInput = document.getElementById("image");
  const imagePreview = document.getElementById("imagePreview");

  if (!projectId) {
    alert("ID de proyecto no proporcionado.");
    window.history.back();
    return;
  }

  // Cargar datos en el formulario
  async function loadProjectData() {
    try {
      const response = await fetch(`${API_BASE}project/get?id=${projectId}`, {
        method: 'GET'
      });
      const data = await response.json();

      if (data.success && data.project) {
        document.getElementById("title").value = data.project.title;
        document.getElementById("description").value = data.project.description;
        document.getElementById("pitch").value = data.project.pitch;

        if (data.project.image) {
          imagePreview.innerHTML = `<img src="data:image/jpeg;base64,${data.project.image}" alt="Project Image" style="max-width: 200px; height: auto;" />`;
        } else {
          imagePreview.innerHTML = '<p></p>';
        }
      } else {
        alert(data.message || "Error al cargar los datos del proyecto.");
        window.history.back();
      }
    } catch (error) {
      console.error('Error:', error);
      alert("Error de conexión al cargar el proyecto. Inténtalo de nuevo.");
      window.history.back();
    }
  }

  loadProjectData();

  if (imageInput) {
    imageInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (event) => {
          imagePreview.innerHTML = `<img src="${event.target.result}" alt="Image Preview" style="max-width: 200px; height: auto;" />`;
        };
        reader.readAsDataURL(file);
      } else {
        loadProjectData();
      }
    });
  }

  if (editProjectForm) {
    editProjectForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      // Obtener datos del formulario
      const formData = new FormData(this);
      formData.append('id', projectId);

      // Validación simple
      if (!formData.get('title') || !formData.get('description')) {
        alert("Por favor, completa todos los campos requeridos.");
        return;
      }

      // Botón de envío
      const submitButton = this.querySelector("[type='submit']");
      const originalText = submitButton.textContent;

      submitButton.textContent = "Guardando...";
      submitButton.disabled = true;

      try {
        const response = await fetch(`${API_BASE}project/update`, {
          method: 'POST',
          body: formData
        });
        const data = await response.json();

        if (data.success) {
          alert("¡Proyecto actualizado exitosamente!");
          window.location.href = `project-list`;
        } else {
          alert(data.message || "Error al actualizar el proyecto.");
        }
      } catch (error) {
        console.error('Error:', error);
        alert("Error de conexión. Inténtalo de nuevo.");
      } finally {
        submitButton.textContent = originalText;
        submitButton.disabled = false;
      }
    });
  }

  // Botón cancelar
  const cancelButton = document.getElementById("cancelButton");
  if (cancelButton) {
    cancelButton.addEventListener("click", () => {
      editProjectForm.reset();
      window.history.back();
    });
  }

  // Botón vista previa
  const previewButton = document.getElementById("previewButton");
  if (previewButton) {
    previewButton.addEventListener("click", () => {
      const title = document.getElementById("title").value;
      const description = document.getElementById("description").value;
      const pitch = document.getElementById("pitch").value;
      const image = imageInput.files[0] ? 'Imagen seleccionada' : 'Sin nueva imagen';

      if (!title || !description) {
        alert("Por favor, completa título y descripción para vista previa.");
        return;
      }
      
      // Crear modal de vista previa 
      const modal = document.createElement('div');
      modal.className = 'modal-overlay';
      modal.innerHTML = `
        <div class="modal-content preview-modal">
          <div class="modal-title">Vista previa del proyecto</div>
          <div class="modal-body">
            <div class="preview-section">
              <strong>Título:</strong> ${title}
            </div>
            <div class="preview-section">
              <strong>Descripción:</strong>
              <div class="project-description-formatted">
                ${formatProjectDescription(description)}
              </div>
            </div>
            <div class="preview-section">
              <strong>Pitch:</strong> ${pitch || 'No especificado'}
            </div>
            <div class="preview-section">
              <strong>Imagen:</strong> ${image}
            </div>
          </div>
          <div class="modal-buttons">
            <button class="modal-cancel-btn" onclick="this.closest('.modal-overlay').remove()">Cerrar</button>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
    });
  }
});