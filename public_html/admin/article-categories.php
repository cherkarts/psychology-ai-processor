<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
requireLogin();
include 'includes/header.php';
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box">
        <h4 class="page-title">Категории статей</h4>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>Список категорий</span>
      <button class="btn btn-primary" id="btnAdd">Добавить категорию</button>
    </div>
    <div class="card-body">
      <div id="listContainer" class="table-responsive"></div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="catModal" tabindex="-1" style="display:none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="catModalTitle">Категория</h5>
        <button type="button" class="close" id="catCloseBtn"><span>&times;</span></button>
      </div>
      <form id="catForm">
        <div class="modal-body">
          <input type="hidden" id="catId" />
          <div class="form-group">
            <label>Название</label>
            <input type="text" class="form-control" id="catName" required />
          </div>
          <div class="form-group">
            <label>Slug (необязательно)</label>
            <input type="text" class="form-control" id="catSlug" />
          </div>
          <div class="form-group">
            <label>Порядок</label>
            <input type="number" class="form-control" id="catSort" value="0" />
          </div>
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="catActive" checked />
            <label class="form-check-label" for="catActive">Активна</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="catCancelBtn">Отмена</button>
          <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function openModal(data) {
    document.getElementById('catForm').reset();
    document.getElementById('catId').value = data?.id || '';
    document.getElementById('catName').value = data?.name || '';
    document.getElementById('catSlug').value = data?.slug || '';
    document.getElementById('catSort').value = data?.sort_order ?? 0;
    document.getElementById('catActive').checked = (data?.is_active ?? 1) ? true : false;
    showCatModal();
  }

  function renderList(items) {
    let html = '<table class="table table-striped"><thead><tr><th>ID</th><th>Название</th><th>Slug</th><th>Активна</th><th>Порядок</th><th>Действия</th></tr></thead><tbody>';
    items.forEach(it => {
      html += `<tr><td>${it.id}</td><td>${it.name}</td><td>${it.slug}</td><td>${it.is_active ? 'Да' : 'Нет'}</td><td>${it.sort_order ?? 0}</td><td>
    <button class="btn btn-sm btn-primary" onclick='openModal(${JSON.stringify(it)})'><i class="fas fa-edit"></i></button>
    <button class="btn btn-sm btn-danger" onclick='delCat(${it.id})'><i class="fas fa-trash"></i></button>
    </td></tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('listContainer').innerHTML = html;
  }

  async function loadList() {
    const r = await fetch('api/article-categories.php');
    const j = await r.json();
    if (j.success) { renderList(j.items); } else { alert('Ошибка: ' + j.message); }
  }

  async function saveCat(e) {
    e.preventDefault();
    const payload = {
      name: document.getElementById('catName').value.trim(),
      slug: document.getElementById('catSlug').value.trim(),
      sort_order: parseInt(document.getElementById('catSort').value || '0', 10),
      is_active: document.getElementById('catActive').checked
    };
    const id = document.getElementById('catId').value;
    const method = id ? 'PATCH' : 'POST';
    if (id) payload.id = parseInt(id, 10);
    const r = await fetch('api/article-categories.php', { method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const j = await r.json();
    if (j.success) { hideCatModal(); loadList(); } else { alert('Ошибка: ' + j.message); }
  }

  async function delCat(id) {
    if (!confirm('Удалить категорию?')) return;
    const r = await fetch('api/article-categories.php?id=' + id, { method: 'DELETE' });
    const j = await r.json();
    if (j.success) { loadList(); } else { alert('Ошибка: ' + j.message); }
  }

  document.getElementById('btnAdd').onclick = () => openModal();
  document.getElementById('catForm').addEventListener('submit', saveCat);
  // Простая реализация модалки без jQuery/Bootstrap
  function showCatModal(){
    var m = document.getElementById('catModal');
    if(!m) return;
    m.style.display = 'block';
    m.classList.add('show');
    document.body.classList.add('modal-open');
    var bd = document.createElement('div');
    bd.className = 'modal-backdrop fade show';
    bd.id = 'catModalBackdrop';
    document.body.appendChild(bd);
  }
  function hideCatModal(){
    var m = document.getElementById('catModal');
    if(!m) return;
    m.classList.remove('show');
    m.style.display = 'none';
    document.body.classList.remove('modal-open');
    var bd = document.getElementById('catModalBackdrop');
    if(bd) bd.remove();
  }
  (function(){
    var closeBtn = document.getElementById('catCloseBtn');
    if(closeBtn) closeBtn.onclick = hideCatModal;
    var cancelBtn = document.getElementById('catCancelBtn');
    if(cancelBtn) cancelBtn.onclick = hideCatModal;
  })();

  loadList();
</script>

<?php include 'includes/footer.php'; ?>
