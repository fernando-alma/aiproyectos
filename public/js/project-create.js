/**
 * project-create.js — AIWEEKEND
 *
 * Handles: FormData capture, Bearer auth, image preview,
 * char counter, Toast notifications, redirect.
 */

const API_BASE = CONFIG.API_BASE;
const CURRENT_SLUG = CONFIG.SLUG;

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('projectCreateForm');
  const submitBtn = document.getElementById('submitBtn');
  const cancelBtn = document.getElementById('cancelBtn');
  const imageInput = document.getElementById('image');
  const uploadZone = document.getElementById('uploadZone');
  const imagePreview = document.getElementById('imagePreview');
  const descArea = document.getElementById('description');
  const charCount = document.getElementById('descCharCount');

  const token = localStorage.getItem('token') || sessionStorage.getItem('token');

  // ─── Char Counter ────────────────────────────────────────────────────
  if (descArea && charCount) {
    const maxLen = parseInt(descArea.getAttribute('maxlength')) || 2000;

    descArea.addEventListener('input', () => {
      const len = descArea.value.length;
      charCount.textContent = `${len} / ${maxLen}`;
      charCount.classList.toggle('near-limit', len > maxLen * 0.8 && len < maxLen);
      charCount.classList.toggle('at-limit', len >= maxLen);
    });
  }

  // ─── Image Preview ───────────────────────────────────────────────────
  if (imageInput) {
    imageInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (ev) => {
          imagePreview.src = ev.target.result;
          uploadZone.classList.add('has-preview');
        };
        reader.readAsDataURL(file);
      } else {
        uploadZone.classList.remove('has-preview');
        imagePreview.src = '';
      }
    });
  }

  // ─── Form Submit ─────────────────────────────────────────────────────
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const title = document.getElementById('title').value.trim();
      const description = descArea.value.trim();

      if (!title || !description) {
        if (window.showToast) window.showToast('Campos requeridos', 'Completa título y descripción.', 'error');
        return;
      }

      // Build FormData
      const formData = new FormData(form);
      formData.append('slug', CURRENT_SLUG);
      formData.append('status', 'in_progress');

      submitBtn.disabled = true;
      submitBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 0.8s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        Creando...
      `;

      try {
        const headers = {};
        if (token) headers['Authorization'] = `Bearer ${token}`;

        const res = await fetch(`${API_BASE}project/create`, {
          method: 'POST',
          headers,
          body: formData
        });
        const data = await res.json();

        if (res.ok && data.success) {
          if (window.showToast) window.showToast('¡Proyecto creado!', 'Tu proyecto se registró correctamente.', 'success');

          form.reset();
          uploadZone.classList.remove('has-preview');

          setTimeout(() => {
            window.location.href = 'project-list';
          }, 1500);
        } else {
          throw new Error(data.message || 'Error al crear el proyecto');
        }
      } catch (err) {
        if (window.showToast) window.showToast('Error', err.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
          Crear Proyecto
        `;
      }
    });
  }

  // ─── Cancel ──────────────────────────────────────────────────────────
  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      window.history.back();
    });
  }
});
