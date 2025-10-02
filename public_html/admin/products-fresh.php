<?php
// Свежая версия страницы управления товарами - БЕЗ КЭША
session_start();
require_once __DIR__ . '/includes/auth.php';

// Принудительное отключение кэширования
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Проверка авторизации
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
        <h4 class="page-title">Управление товарами (FRESH - БЕЗ КЭША)</h4>
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
                      <td><?php echo htmlspecialchars($product['category_name'] ?? 'Без категории'); ?></td>
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

<!-- Модальное окно -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="productModalTitle">Добавить товар</h5>
        <button type="button" class="close" onclick="closeProductModal()">
          <span>&times;</span>
        </button>
      </div>
      <form id="productForm">
        <div class="modal-body">
          <input type="hidden" id="productId" name="id">

          <div class="form-group">
            <label for="productTitle">Название *</label>
            <input type="text" class="form-control" id="productTitle" name="title" required>
          </div>

          <div class="form-group">
            <label for="productDescription">Описание</label>
            <textarea class="form-control" id="productDescription" name="description" rows="4"></textarea>
          </div>

          <div class="form-group">
            <label for="productShortDescription">Краткое описание</label>
            <textarea class="form-control" id="productShortDescription" name="short_description" rows="2"></textarea>
            <small class="form-text text-muted">Отобразится на карточке товара в списке.</small>
          </div>

          <div class="form-group">
            <label for="productPrice">Цена *</label>
            <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="productOldPrice">Старая цена</label>
              <input type="number" class="form-control" id="productOldPrice" step="0.01" min="0">
            </div>
            <div class="form-group col-md-6">
              <label for="productCurrency">Валюта</label>
              <select id="productCurrency" class="form-control">
                <option value="RUB">RUB</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="productType">Тип товара</label>
              <select id="productType" class="form-control">
                <option value="digital">Цифровой</option>
                <option value="physical">Физический</option>
                <option value="service">Услуга</option>
                <option value="free">Бесплатный</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="productStatus">Статус</label>
              <select id="productStatus" class="form-control">
                <option value="active">Активен</option>
                <option value="inactive">Неактивен</option>
                <option value="draft">Черновик</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="productInStock">
                <label class="form-check-label" for="productInStock">В наличии</label>
              </div>
            </div>
            <div class="form-group col-md-3">
              <label for="productQuantity">Количество</label>
              <input type="number" class="form-control" id="productQuantity" min="0" value="0">
            </div>
            <div class="form-group col-md-3">
              <label for="productSort">Порядок</label>
              <input type="number" class="form-control" id="productSort" value="0">
            </div>
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

          <!-- Изображение товара -->
          <div class="form-group">
            <label for="productImage">Изображение товара</label>
            <input type="file" class="form-control" id="productImage" accept="image/*">
            <small class="form-text text-muted">Загрузите основное изображение товара</small>
            <div id="imagePreview" class="mt-2" style="display: none;">
              <img id="previewImg" src="" alt="Превью" style="max-width: 200px; max-height: 200px;">
              <button type="button" class="btn btn-sm btn-danger ml-2" onclick="clearImage()">Очистить</button>
            </div>
          </div>

          <!-- Галерея изображений -->
          <div class="form-group">
            <label for="productGallery">Галерея изображений</label>
            <input type="file" class="form-control" id="productGallery" accept="image/*" multiple>
            <small class="form-text text-muted">Загрузите дополнительные изображения товара</small>
            <div id="galleryPreview" class="mt-2"></div>
          </div>

          <div class="form-group">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="productActive" name="is_active" checked>
              <label class="form-check-label" for="productActive">Активен</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="closeProductModal()">Отмена</button>
          <button type="submit" class="btn btn-primary">Сохранить</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  console.log('=== FRESH PRODUCTS SCRIPT LOADED ===', new Date().toISOString());

  // Принудительная очистка кэша
  if ('caches' in window) {
    caches.keys().then(function (names) {
      for (let name of names) {
        caches.delete(name);
      }
    });
  }

  function openProductModal() {
    console.log('Opening modal...');
    var modal = document.getElementById('productModal');
    if (!modal) {
      console.error('Modal not found!');
      return;
    }
    modal.style.display = 'block';
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    var backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop fade show';
    backdrop.id = 'productModalBackdrop';
    document.body.appendChild(backdrop);
  }

  function closeProductModal() {
    console.log('Closing modal...');
    var modal = document.getElementById('productModal');
    if (!modal) return;

    modal.classList.remove('show');
    modal.style.display = 'none';
    document.body.classList.remove('modal-open');

    var backdrop = document.getElementById('productModalBackdrop');
    if (backdrop) backdrop.remove();
  }

  function showAddProductModal() {
    console.log('Showing add product modal...');
    document.getElementById('productModalTitle').textContent = 'Добавить товар';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('galleryPreview').innerHTML = '';
    openProductModal();
  }

  function editProduct(id) {
    console.log('=== EDITING PRODUCT ID:', id, '===');

    fetch('api/get-product.php?id=' + id + '&nocache=' + Date.now())
      .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        console.log('Product data:', data);

        if (data.success && data.product) {
          var product = data.product;

          document.getElementById('productModalTitle').textContent = 'Редактировать товар';
          document.getElementById('productId').value = product.id || '';
          document.getElementById('productTitle').value = product.title || '';
          document.getElementById('productDescription').value = product.description || '';
          document.getElementById('productShortDescription').value = product.short_description || '';
          document.getElementById('productPrice').value = product.price || '';
          document.getElementById('productOldPrice').value = product.old_price || '';
          document.getElementById('productCurrency').value = product.currency || 'RUB';
          document.getElementById('productType').value = product.type || 'digital';
          document.getElementById('productStatus').value = product.status || 'active';
          document.getElementById('productInStock').checked = !!(product.in_stock);
          document.getElementById('productQuantity').value = product.quantity || 0;
          document.getElementById('productSort').value = product.sort_order || 0;
          document.getElementById('productCategory').value = product.category_id || '';
          document.getElementById('productActive').checked = !!(product.is_active);

          // Показываем изображение если есть
          if (product.image || product.featured_image) {
            var imageUrl = product.featured_image || product.image;
            document.getElementById('previewImg').src = imageUrl;
            document.getElementById('imagePreview').style.display = 'block';
          } else {
            document.getElementById('imagePreview').style.display = 'none';
          }

          // Показываем галерею если есть
          if (product.gallery) {
            try {
              var gallery = JSON.parse(product.gallery);
              var galleryHtml = '';
              gallery.forEach(function (img, index) {
                galleryHtml += '<div class="d-inline-block mr-2 mb-2">';
                galleryHtml += '<img src="' + img + '" style="width: 80px; height: 80px; object-fit: cover;" class="border rounded">';
                galleryHtml += '<button type="button" class="btn btn-sm btn-danger" onclick="removeGalleryImage(' + index + ')">×</button>';
                galleryHtml += '</div>';
              });
              document.getElementById('galleryPreview').innerHTML = galleryHtml;
            } catch (e) {
              console.error('Error parsing gallery:', e);
            }
          }

          openProductModal();
        } else {
          alert('Ошибка загрузки товара: ' + (data.message || 'Неизвестная ошибка'));
        }
      })
      .catch(error => {
        console.error('Fetch error:', error);
        alert('Ошибка загрузки товара: ' + error.message);
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
          console.error('Delete error:', error);
          alert('Ошибка удаления товара');
        });
    }
  }

  function clearImage() {
    document.getElementById('productImage').value = '';
    document.getElementById('imagePreview').style.display = 'none';
  }

  function removeGalleryImage(index) {
    // Удаляем изображение из галереи
    var galleryDiv = document.getElementById('galleryPreview');
    var imgDiv = galleryDiv.children[index];
    if (imgDiv) {
      imgDiv.remove();
    }
  }

  // Обработка загрузки изображения
  document.getElementById('productImage').addEventListener('change', function (e) {
    var file = e.target.files[0];
    if (file) {
      var reader = new FileReader();
      reader.onload = function (e) {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('imagePreview').style.display = 'block';
      };
      reader.readAsDataURL(file);
    }
  });

  // Обработка загрузки галереи
  document.getElementById('productGallery').addEventListener('change', function (e) {
    var files = e.target.files;
    var galleryHtml = '';

    for (var i = 0; i < files.length; i++) {
      var file = files[i];
      var reader = new FileReader();
      reader.onload = (function (file) {
        return function (e) {
          galleryHtml += '<div class="d-inline-block mr-2 mb-2">';
          galleryHtml += '<img src="' + e.target.result + '" style="width: 80px; height: 80px; object-fit: cover;" class="border rounded">';
          galleryHtml += '</div>';
          document.getElementById('galleryPreview').innerHTML += galleryHtml;
        };
      })(file);
      reader.readAsDataURL(file);
    }
  });

  // Обработка формы
  document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM loaded, setting up form...');

    var form = document.getElementById('productForm');
    if (!form) {
      console.error('Form not found!');
      return;
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      console.log('Form submitted...');

      var data = {
        id: document.getElementById('productId').value || null,
        title: document.getElementById('productTitle').value || '',
        description: document.getElementById('productDescription').value || '',
        short_description: document.getElementById('productShortDescription').value || '',
        price: document.getElementById('productPrice').value || '',
        old_price: document.getElementById('productOldPrice').value || null,
        currency: document.getElementById('productCurrency').value || 'RUB',
        type: document.getElementById('productType').value || 'digital',
        status: document.getElementById('productStatus').value || 'active',
        in_stock: document.getElementById('productInStock').checked,
        quantity: document.getElementById('productQuantity').value || 0,
        sort_order: document.getElementById('productSort').value || 0,
        category_id: document.getElementById('productCategory').value || null,
        is_active: document.getElementById('productActive').checked
      };

      console.log('Sending data:', data);

      // Создаем FormData для загрузки файлов
      var formData = new FormData();

      // Добавляем все поля
      for (var key in data) {
        if (data[key] !== null && data[key] !== '') {
          formData.append(key, data[key]);
        }
      }

      // Добавляем изображение если выбрано
      var imageFile = document.getElementById('productImage').files[0];
      if (imageFile) {
        formData.append('image', imageFile);
      }

      // Добавляем галерею если выбрана
      var galleryFiles = document.getElementById('productGallery').files;
      for (var i = 0; i < galleryFiles.length; i++) {
        formData.append('gallery[]', galleryFiles[i]);
      }

      fetch('api/save-product.php', {
        method: 'POST',
        body: formData
      })
        .then(response => {
          console.log('Save response status:', response.status);
          if (!response.ok) {
            throw new Error('HTTP ' + response.status);
          }
          return response.json();
        })
        .then(data => {
          console.log('Save response:', data);
          if (data.success) {
            closeProductModal();
            location.reload();
          } else {
            alert('Ошибка сохранения: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Save error:', error);
          alert('Ошибка сохранения товара: ' + error.message);
        });
    });
  });

  console.log('=== FRESH SCRIPT SETUP COMPLETE ===');
</script>

<?php include 'includes/footer.php'; ?>