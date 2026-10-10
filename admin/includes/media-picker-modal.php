<?php
/**
 * Media Picker Modal
 * Include this in your page form to enable media library selection
 */
if (!defined('NIIB_INIT')) { http_response_code(403); exit; }
?>

<!-- Media Picker Modal -->
<div id="mediaPicker" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select from Media Library</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <input type="text" id="mediaSearch" class="form-control" placeholder="Search media...">
        </div>
        <div id="mediaGrid" class="row g-2" style="max-height:500px;overflow-y:auto">
          <div class="text-center text-muted py-5 w-100">Loading...</div>
        </div>
        <nav class="mt-3" id="mediaPagination"></nav>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="mediaSelect" disabled>Select</button>
      </div>
    </div>
  </div>
</div>

<style>
  .media-item {
    cursor: pointer;
    position: relative;
    border: 2px solid transparent;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.2s;
  }
  .media-item:hover {
    border-color: #0d6efd;
    transform: scale(1.02);
  }
  .media-item.selected {
    border-color: #0d6efd;
    background: rgba(13, 110, 253, 0.1);
  }
  .media-item img {
    width: 100%;
    height: 140px;
    object-fit: cover;
    display: block;
  }
  .media-item.non-image {
    width: 100%;
    height: 140px;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: #666;
    font-size: 12px;
  }
  .media-item .media-name {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 4px 8px;
    font-size: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
</style>

<script>
(function() {
  const modal = document.getElementById('mediaPicker');
  if (!modal) return;

  const grid = document.getElementById('mediaGrid');
  const search = document.getElementById('mediaSearch');
  const pagination = document.getElementById('mediaPagination');
  const selectBtn = document.getElementById('mediaSelect');

  let selectedItem = null;
  let currentPage = 1;
  let currentSearch = '';
  let callbackFn = null;
  let mediaType = 'image';

  function loadMedia(page = 1, searchTerm = '') {
    const url = new URL('<?= ADMIN_URL ?>/modules/media-picker.php', window.location.origin);
    url.searchParams.set('action', 'list');
    url.searchParams.set('type', mediaType);
    url.searchParams.set('page', page);
    if (searchTerm) url.searchParams.set('search', searchTerm);

    fetch(url)
      .then(r => r.json())
      .then(data => {
        if (!data.success) throw new Error(data.error);

        grid.innerHTML = '';

        if (data.data.length === 0) {
          grid.innerHTML = '<div class="text-center text-muted py-5 w-100">No media found</div>';
          pagination.innerHTML = '';
          return;
        }

        data.data.forEach(item => {
          const col = document.createElement('div');
          col.className = 'col-sm-6 col-md-4';

          const div = document.createElement('div');
          div.className = 'media-item';
          div.dataset.id = item.id;
          div.dataset.path = item.path;
          div.dataset.url = item.url;
          div.dataset.name = item.name;

          if (item.isImage) {
            div.innerHTML = `
              <img src="${item.thumbnail}" alt="${item.name}">
              <div class="media-name">${item.name}</div>
            `;
          } else {
            div.className += ' non-image';
            div.innerHTML = `
              <i class="bi bi-file-earmark" style="font-size:2rem;margin-bottom:8px"></i>
              <span>${item.type.toUpperCase()}</span>
            `;
          }

          div.addEventListener('click', () => selectMediaItem(div));
          col.appendChild(div);
          grid.appendChild(col);
        });

        // Pagination
        renderPagination(data);
      })
      .catch(err => {
        grid.innerHTML = '<div class="text-center text-danger py-5 w-100">Error: ' + err.message + '</div>';
      });
  }

  function selectMediaItem(element) {
    document.querySelectorAll('.media-item').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    selectedItem = {
      id: element.dataset.id,
      path: element.dataset.path,
      url: element.dataset.url,
      name: element.dataset.name,
    };
    selectBtn.disabled = false;
  }

  function renderPagination(data) {
    pagination.innerHTML = '';
    if (data.pages <= 1) return;

    const nav = document.createElement('nav');
    const ul = document.createElement('ul');
    ul.className = 'pagination pagination-sm';

    for (let i = 1; i <= data.pages; i++) {
      const li = document.createElement('li');
      li.className = 'page-item' + (i === data.page ? ' active' : '');
      const a = document.createElement('a');
      a.className = 'page-link';
      a.href = '#';
      a.textContent = i;
      a.addEventListener('click', (e) => {
        e.preventDefault();
        loadMedia(i, currentSearch);
        currentPage = i;
      });
      li.appendChild(a);
      ul.appendChild(li);
    }

    nav.appendChild(ul);
    pagination.appendChild(nav);
  }

  search.addEventListener('input', (e) => {
    currentSearch = e.target.value;
    currentPage = 1;
    loadMedia(1, currentSearch);
  });

  selectBtn.addEventListener('click', () => {
    if (selectedItem && callbackFn) {
      callbackFn(selectedItem);
      const bsModal = bootstrap.Modal.getInstance(modal);
      if (bsModal) bsModal.hide();
    }
  });

  // Expose API
  window.openMediaPicker = function(callback, type = 'image') {
    callbackFn = callback;
    mediaType = type;
    selectedItem = null;
    selectBtn.disabled = true;
    currentPage = 1;
    currentSearch = '';
    search.value = '';
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    loadMedia(1, '');
  };
})();
</script>
