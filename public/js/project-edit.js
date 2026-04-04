/**
 * project-edit.js — AIWEEKEND
 *
 * Handles: Load project data from API, populate all fields,
 * image preview, char counter, FormData submit with Bearer auth,
 * Toast notifications, redirect to project-detail.
 */

const API_BASE = CONFIG.API_BASE;

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('projectEditForm');
  const submitBtn = document.getElementById('submitBtn');
  const cancelBtn = document.getElementById('cancelBtn');
  const backBtn = document.getElementById('backBtn');
  const imageInput = document.getElementById('image');
  const uploadZone = document.getElementById('uploadZone');
  const imagePreview = document.getElementById('imagePreview');
  const descArea = document.getElementById('description');
  const charCount = document.getElementById('descCharCount');

  const token = localStorage.getItem('token') || sessionStorage.getItem('token');
  const urlParams = new URLSearchParams(window.location.search);
  const projectId = urlParams.get('id');

  // ─── Validate ID ─────────────────────────────────────────────────────
  if (!projectId) {
    if (window.showToast) window.showToast('Error', 'ID de proyecto no proporcionado.', 'error');
    setTimeout(() => window.history.back(), 1000);
    return;
  }

  // Set back button to project detail
  if (backBtn) backBtn.href = `project-detail?id=${projectId}`;

  // ─── Char Counter ────────────────────────────────────────────────────
  if (descArea && charCount) {
    const maxLen = parseInt(descArea.getAttribute('maxlength')) || 2000;

    const updateCounter = () => {
      const len = descArea.value.length;
      charCount.textContent = `${len} / ${maxLen}`;
      charCount.classList.toggle('near-limit', len > maxLen * 0.8 && len < maxLen);
      charCount.classList.toggle('at-limit', len >= maxLen);
    };

    descArea.addEventListener('input', updateCounter);
    // Will call updateCounter after load
  }

  // ─── Image Preview (new upload) ──────────────────────────────────────
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
      }
    });
  }

  // ─── Load Project Data ───────────────────────────────────────────────
  async function loadProjectData() {
    try {
      const headers = {};
      if (token) headers['Authorization'] = `Bearer ${token}`;

      const res = await fetch(`${API_BASE}project/get?id=${projectId}`, {
        method: 'GET',
        headers
      });
      const data = await res.json();

      if (!res.ok || !data.success || !data.project) {
        throw new Error(data.message || 'No se pudo cargar el proyecto');
      }

      const p = data.project;

      // Populate fields
      const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val || '';
      };

      setVal('title', p.title);
      setVal('group_name', p.group_name);
      setVal('description', p.description);
      setVal('pitch', p.pitch);
      setVal('link_video', p.link_video);
      setVal('link_deploy', p.link_deploy);
      setVal('link_repository', p.link_repository);
      setVal('status', p.status);

      // Trigger char counter update
      if (descArea) {
        descArea.dispatchEvent(new Event('input'));
      }

      // Show existing image
      if (p.image) {
        let imgSrc = '';
        if (p.image.startsWith('/uploads') || p.image.startsWith('uploads/')) {
          // Path-based image → build full URL
          imgSrc = `${API_BASE.replace('/backend/public/', '')}/${p.image.replace(/^\//, '')}`;
        } else if (p.image.startsWith('data:')) {
          imgSrc = p.image;
        } else {
          // Base64 raw
          imgSrc = `data:image/jpeg;base64,${p.image}`;
        }
        imagePreview.src = imgSrc;
        uploadZone.classList.add('has-preview');
      }

    } catch (err) {
      if (window.showToast) window.showToast('Error', err.message, 'error');
      setTimeout(() => window.history.back(), 1500);
    }
  }

  loadProjectData();

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

      const formData = new FormData(form);
      formData.append('id', projectId);

      submitBtn.disabled = true;
      submitBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 0.8s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
        Guardando...
      `;

      try {
        const headers = {};
        if (token) headers['Authorization'] = `Bearer ${token}`;

        const res = await fetch(`${API_BASE}project/update`, {
          method: 'POST',
          headers,
          body: formData
        });
        const data = await res.json();

        if (res.ok && data.success) {
          if (window.showToast) window.showToast('¡Guardado!', 'Proyecto actualizado correctamente.', 'success');

          setTimeout(() => {
            window.location.href = `project-detail?id=${projectId}`;
          }, 1500);
        } else {
          throw new Error(data.message || 'Error al actualizar');
        }
      } catch (err) {
        if (window.showToast) window.showToast('Error', err.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Guardar Cambios
        `;
      }
    });
  }

  // ─── Cancel ──────────────────────────────────────────────────────────
  if (cancelBtn) {
    cancelBtn.addEventListener('click', () => {
      window.location.href = `project-detail?id=${projectId}`;
    });
  }
});