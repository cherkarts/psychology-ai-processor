<?php
// Новая версия страницы управления товарами v3.0
session_start();
require_once __DIR__ . '/includes/auth.php';

// Принудительное отключение кэширования
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Проверка авторизации (унифицированная)
requireLogin();

// Подключение к БД
require_once '../config.php';

try {
  $pdo = new PDO(
    "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['dbname'],
    $config['database']['username'],
    $config['database']['password'],
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );

  // Получаем товары
  $stmt = $pdo->query("
        SELECT p.*, pc.name as category_name 
        FROM products p 
        LEFT JOIN product_categories pc ON p.category_id = pc.id 
        ORDER BY p.created_at DESC
    ");
  $products = $stmt->fetchAll();

  // Получаем категории товаров
  $stmt = $pdo->query("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY sort_order, name");
  $categories = $stmt->fetchAll();

} catch (Exception $e) {
  $products = [];
  $categories = [];
  $error = $e->getMessage();
}

$pageTitle = 'Управление товарами';
include 'includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-title-box">
        <h4 class="page-title">Управление товарами (v3.0)</h4>
      </div>
    </div>
  </div>

  <?php if (isset($error)): ?>
    <div class="alert alert-danger">
      <strong>Ошибка:</strong> <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="row align-items-center">
            <div class="col">
              <h5 class="card-title mb-0">Список товаров</h5>
            </div>
            <div class="col-auto">
              <button class="btn btn-primary" onclick="showAddProductModal()">
                <i class="fas fa-plus"></i> Добавить товар
              </button>
            </div>
          </div>
        </div>
        <div class="card-body">
          <?php if (empty($products)): ?>
            <div class="text-center py-4">
              <p class="text-muted">Товары не найдены</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Категория</th>
                    <th>Цена</th>
                    <th>Статус</th>
                    <th>Дата создания</th>
                    <th>Действия</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($products as $product): ?>
                    <tr>
                      <td><?php echo $product['id']; ?></td>
                      <td><?php echo htmlspecialchars($product['title']); ?></td>
                      <td><?php echo htmlspecialchars($product['category_name'] ?? 'Без категории'); ?>
                      </td>
                      <td><?php echo number_format($product['price'], 2); ?> ₽</td>
                      <td>
                        <span class="badge badge-<?php echo $product['is_active'] ? 'success' : 'secondary'; ?>">
                          <?php echo $product['is_active'] ? 'Активен' : 'Неактивен'; ?>
                        </span>
                      </td>
                      <td><?php echo date('d.m.Y H:i', strtotime($product['created_at'])); ?></td>
                      <td>
                        <button class="btn btn-sm btn-primary" onclick="editProduct(<?php echo $product['id']; ?>)">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Модальное окно добавления/редактирования товара -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalTitle">Добавить товар</h5>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form id="productForm">
        <div class="modal-body">
          <input type="hidden" id="productId" name="id">

          <div class="form-group">
            <label for="productTitle">Название *</label>
            <input type="text" class="form-control" id="productTitle" name="title" required>
            <small class="form-text text-muted">Короткое и понятное название товара.</small>
          </div>

          <div class="form-group">
            <label for="productDescription">Описание</label>
            <textarea class="form-control" id="productDescription" name="description" rows="4"></textarea>
            <small class="form-text text-muted">Полное описание: состав, особенности, для кого
              подходит.</small>
          </div>

          <div class="form-group">
            <label for="productPrice">Цена *</label>
            <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
            <small class="form-text text-muted">Для типа "Бесплатный" цена будет выставлена в 0.</small>
          </div>

          <div class="form-group">
            <label for="productCategory">Категория</label>
            <select class="form-control" id="productCategory" name="category_id">
              <option value="">Выберите категорию</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>">
                  <?php echo htmlspecialchars($category['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="productActive" name="is_active" checked>
              <label class="form-check-label" for="productActive">Активен</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
          <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Новая версия скрипта v3.0
  console.log('Products script v3.0 loaded at', new Date().toISOString());

  function openProductModal() {
    var m = document.getElementById('productModal');
    if (!m) return;
    m.style.display = 'block';
    m.classList.add('show');
    document.body.classList.add('modal-open');
    var bd = document.createElement('div');
    bd.className = 'modal-backdrop fade show';
    bd.id = 'productModalBackdrop';
    document.body.appendChild(bd);
  }

  function closeProductModal() {
    var m = document.getElementById('productModal');
    if (!m) return;
    m.classList.remove('show');
    m.style.display = 'none';
    document.body.classList.remove('modal-open');
    var bd = document.getElementById('productModalBackdrop');
    if (bd) bd.remove();
  }

  (function () {
    var closeBtn = document.querySelector('#productModal .close');
    if (closeBtn) closeBtn.onclick = closeProductModal;
  })();

  function showAddProductModal() {
    document.getElementById('productModalTitle').textContent = 'Добавить товар';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    openProductModal();
  }

  function editProduct(id) {
    console.log('Loading product ID:', id);
    fetch('api/get-product.php?id=' + id + '&v=' + Date.now())
      .then(response => response.json())
      .then(data => {
        console.log('Product data received:', data);
        if (data.success) {
          const product = data.product;
          document.getElementById('productModalTitle').textContent = 'Редактировать товар';
          document.getElementById('productId').value = product.id;
          document.getElementById('productTitle').value = product.title || '';
          document.getElementById('productDescription').value = product.description || '';
          document.getElementById('productPrice').value = product.price || '';
          document.getElementById('productCategory').value = product.category_id || '';
          document.getElementById('productActive').checked = !!(product.is_active ?? (product.status === 'active'));
          openProductModal();
        } else {
          alert('Ошибка загрузки товара: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Ошибка загрузки товара');
      });
  }

  function deleteProduct(id) {
    if (confirm('Вы уверены, что хотите удалить этот товар?')) {
      fetch('api/delete-product.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: id })
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Ошибка удаления: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Ошибка удаления товара');
        });
    }
  }

  // Обработка формы
  document.getElementById('productForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const data = {
      id: document.getElementById('productId').value || null,
      title: document.getElementById('productTitle').value || '',
      description: document.getElementById('productDescription').value || '',
      price: document.getElementById('productPrice').value || '',
      category_id: document.getElementById('productCategory').value || null,
      is_active: document.getElementById('productActive').checked
    };

    fetch('api/save-product.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data)
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          closeProductModal();
          location.reload();
        } else {
          alert('Ошибка сохранения: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Ошибка сохранения товара');
      });
  });
</script>

<?php include 'includes/footer.php'; ?>