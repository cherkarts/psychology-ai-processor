<?php
// Версия 5.0 - полностью новая страница управления товарами
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

<style>
    .image-preview-container,
    .gallery-preview-container,
    .file-preview-container {
        margin-top: 10px;
    }

    .preview-item,
    .file-preview-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: #f9f9f9;
    }

    .gallery-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }

    .gallery-preview-item {
        position: relative;
        border: 1px solid #ddd;
        border-radius: 5px;
        overflow: hidden;
        background: #f9f9f9;
    }

    .gallery-preview-item img,
    .gallery-preview-item video {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }

    .gallery-preview-item .preview-actions {
        position: absolute;
        top: 5px;
        right: 5px;
    }

    .gallery-preview-item .preview-actions button {
        padding: 2px 6px;
        font-size: 12px;
    }

    .file-preview-item i {
        font-size: 20px;
        color: #666;
    }

    .file-preview-item span {
        flex: 1;
        font-size: 14px;
    }

    .badges-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 8px;
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }

    .badge-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        position: relative;
        min-height: 40px;
        width: 100%;
    }

    .badge-item:hover {
        background: #f8f9fa;
        border-color: #007bff;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.15);
        transform: translateY(-1px);
    }

    .badge-item.selected {
        background: #007bff;
        color: white;
        border-color: #007bff;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }

    .badge-item.selected:hover {
        background: #0056b3;
        border-color: #0056b3;
    }

    .badge-item input[type="checkbox"] {
        margin-right: 10px;
        transform: scale(1.1);
        cursor: pointer;
    }

    .badge-item label {
        cursor: pointer;
        margin: 0;
        font-weight: 500;
        font-size: 14px;
        flex: 1;
    }

    .badge-preview {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 8px;
        min-width: 60px;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
    }

    .badge-item.selected .badge-preview {
        background: rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    /* Стили для секции ярлыков */
    .badges-section {
        margin-top: 10px;
    }

    .badges-loading {
        text-align: center;
        padding: 20px;
        color: #6c757d;
        font-style: italic;
    }

    .badges-loading i {
        margin-right: 8px;
    }

    .badges-error {
        text-align: center;
        padding: 20px;
        color: #dc3545;
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .badges-error i {
        font-size: 24px;
        margin-bottom: 5px;
    }

    .badges-error span {
        font-weight: 500;
    }

    .badges-error button {
        margin-top: 5px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    .form-label i {
        margin-right: 8px;
        color: #007bff;
    }

    /* Адаптивность для мобильных устройств */
    @media (max-width: 768px) {
        .badges-container {
            grid-template-columns: 1fr;
            gap: 8px;
            padding: 10px;
        }

        .badge-item {
            padding: 10px 12px;
        }

        .badge-item label {
            font-size: 13px;
        }

        .badge-preview {
            font-size: 10px;
            min-width: 60px;
            margin-left: 8px;
        }

        .form-label {
            font-size: 14px;
        }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Управление товарами (v5.0 - БЕЗ КЭША)</h4>
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
                                                <span
                                                    class="badge badge-<?php echo $product['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $product['is_active'] ? 'Активен' : 'Неактивен'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($product['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="editProduct(<?php echo $product['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="deleteProduct(<?php echo $product['id']; ?>)">
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
                        <textarea class="form-control" id="productShortDescription" name="short_description"
                            rows="2"></textarea>
                        <small class="form-text text-muted">Отобразится на карточке товара в списке.</small>
                    </div>

                    <div class="form-group">
                        <label for="productPrice">Цена *</label>
                        <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0"
                            required>
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

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-tags"></i> Ярлыки товара
                        </label>
                        <div class="badges-section">
                            <div id="badgesContainer" class="badges-container">
                                <!-- Ярлыки будут загружены через JavaScript -->
                                <div class="badges-loading">
                                    <i class="fas fa-spinner fa-spin"></i> Загрузка ярлыков...
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Выберите ярлыки для отображения на карточке товара.
                                Можно выбрать несколько ярлыков одновременно.
                            </small>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="productActive" name="is_active" checked>
                            <label class="form-check-label" for="productActive">Активен</label>
                        </div>
                    </div>

                    <hr>
                    <div class="form-group">
                        <label for="productImage">Главное изображение</label>
                        <input type="file" class="form-control" id="productImage" name="image" accept="image/*">
                        <small class="form-text text-muted">JPG, PNG, WEBP, GIF. До 5 МБ.</small>
                        <div id="mainImagePreview" class="image-preview-container"
                            style="margin-top: 10px; display: none;">
                            <div class="preview-item">
                                <img id="mainImagePreviewImg" src="" alt="Превью"
                                    style="max-width: 200px; max-height: 150px; border-radius: 5px;">
                                <div class="preview-actions">
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="removeMainImage()">Удалить</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="productGallery">Галерея (несколько изображений/видео)</label>
                        <input type="file" class="form-control" id="productGallery" name="gallery[]"
                            accept="image/*,video/mp4" multiple>
                        <small class="form-text text-muted">Можно загрузить несколько файлов. Изображения и MP4
                            видео.</small>
                        <div id="galleryPreview" class="gallery-preview-container"
                            style="margin-top: 10px; display: none;">
                            <div class="gallery-preview-grid" id="galleryPreviewGrid">
                                <!-- Превью галереи будут добавлены здесь -->
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="productDownloadFile">Файл для скачивания (для бесплатных/цифровых товаров)</label>
                        <input type="file" class="form-control" id="productDownloadFile" name="download_file"
                            accept="application/zip,application/pdf,application/octet-stream,application/x-zip-compressed,audio/mpeg,audio/mp3">
                        <small class="form-text text-muted">Например: ZIP/PDF/MP3. До 200 МБ.</small>
                        <div id="downloadFilePreview" class="file-preview-container"
                            style="margin-top: 10px; display: none;">
                            <div class="file-preview-item">
                                <i class="fas fa-file"></i>
                                <span id="downloadFileName"></span>
                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="removeDownloadFile()">Удалить</button>
                            </div>
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
    console.log('=== PRODUCTS SCRIPT V5.0 LOADED ===', new Date().toISOString());

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

        // Очищаем превью
        clearAllPreviews();
    }

    function showAddProductModal() {
        console.log('Showing add product modal...');
        document.getElementById('productModalTitle').textContent = 'Добавить товар';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        openProductModal();
    }

    function editProduct(id) {
        console.log('=== EDITING PRODUCT ID:', id, '===');

        // Простой fetch без сложной логики
        fetch('../test-api-simple.php?id=' + id + '&nocache=' + Date.now())
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Invalid JSON response:', text);
                        throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                    }
                });
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

                    // Очищаем поля файлов при редактировании
                    document.getElementById('productImage').value = '';
                    document.getElementById('productGallery').value = '';
                    document.getElementById('productDownloadFile').value = '';

                    // Показываем превью существующих файлов
                    showExistingFiles(product);

                    // Загружаем ярлыки товара
                    loadProductBadges(product.id);

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

            var fd = new FormData();
            var id = document.getElementById('productId').value || '';
            if (id) fd.append('id', id);
            fd.append('title', document.getElementById('productTitle').value || '');
            fd.append('description', document.getElementById('productDescription').value || '');
            fd.append('short_description', document.getElementById('productShortDescription').value || '');
            fd.append('price', document.getElementById('productPrice').value || '');
            var oldPrice = document.getElementById('productOldPrice').value;
            if (oldPrice !== '') fd.append('old_price', oldPrice);
            fd.append('currency', document.getElementById('productCurrency').value || 'RUB');
            fd.append('type', document.getElementById('productType').value || 'digital');
            fd.append('status', document.getElementById('productStatus').value || 'active');
            fd.append('in_stock', document.getElementById('productInStock').checked ? '1' : '0');
            fd.append('quantity', document.getElementById('productQuantity').value || '0');
            fd.append('sort_order', document.getElementById('productSort').value || '0');
            var categoryId = document.getElementById('productCategory').value;
            if (categoryId !== '') fd.append('category_id', categoryId);
            fd.append('is_active', document.getElementById('productActive').checked ? '1' : '0');

            // files
            var imageInput = document.getElementById('productImage');
            if (imageInput && imageInput.files && imageInput.files[0]) {
                fd.append('image', imageInput.files[0]);
            }
            var galleryInput = document.getElementById('productGallery');
            if (galleryInput && galleryInput.files) {
                for (var i = 0; i < galleryInput.files.length; i++) {
                    fd.append('gallery[]', galleryInput.files[i]);
                }
            }
            var downloadInput = document.getElementById('productDownloadFile');
            if (downloadInput && downloadInput.files && downloadInput.files[0]) {
                fd.append('download_file', downloadInput.files[0]);
            }

            fetch('../test-save-simple.php', {
                method: 'POST',
                body: fd
            })
                .then(response => {
                    console.log('Save response status:', response.status);
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Invalid JSON response:', text);
                            throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                        }
                    });
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

    // Функция для отображения существующих файлов
    function showExistingFiles(product) {
        // Главное изображение
        if (product.image_preview) {
            showMainImagePreview(product.image_preview);
        }

        // Галерея
        if (product.gallery_preview && product.gallery_preview.length > 0) {
            showGalleryPreview(product.gallery_preview);
        }

        // Файл для скачивания
        if (product.download_file_preview) {
            showDownloadFilePreview(product.download_file_preview);
        }
    }

    // Показать превью главного изображения
    function showMainImagePreview(imageUrl) {
        const preview = document.getElementById('mainImagePreview');
        const img = document.getElementById('mainImagePreviewImg');
        img.src = imageUrl;
        preview.style.display = 'block';
    }

    // Показать превью галереи
    function showGalleryPreview(gallery) {
        const preview = document.getElementById('galleryPreview');
        const grid = document.getElementById('galleryPreviewGrid');

        grid.innerHTML = '';

        gallery.forEach((item, index) => {
            const div = document.createElement('div');
            div.className = 'gallery-preview-item';
            div.dataset.index = index;
            div.dataset.url = item;

            const isVideo = item.toLowerCase().includes('.mp4') || item.toLowerCase().includes('.webm');

            if (isVideo) {
                div.innerHTML = `
                <video src="${item}" controls style="width: 100%; height: 120px; object-fit: cover;"></video>
                <div class="preview-actions">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeGalleryItem(${index})">×</button>
                </div>
            `;
            } else {
                div.innerHTML = `
                <img src="${item}" alt="Превью" style="width: 100%; height: 120px; object-fit: cover;">
                <div class="preview-actions">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeGalleryItem(${index})">×</button>
                </div>
            `;
            }

            grid.appendChild(div);
        });

        preview.style.display = 'block';
    }

    // Показать превью файла для скачивания
    function showDownloadFilePreview(fileUrl) {
        const preview = document.getElementById('downloadFilePreview');
        const fileName = document.getElementById('downloadFileName');
        const fileNameFromUrl = fileUrl.split('/').pop();
        fileName.textContent = fileNameFromUrl;
        preview.style.display = 'block';
    }

    // Удалить главное изображение
    function removeMainImage() {
        const preview = document.getElementById('mainImagePreview');
        preview.style.display = 'none';
        // Добавляем скрытое поле для удаления
        let hiddenInput = document.getElementById('removeMainImage');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'removeMainImage';
            hiddenInput.name = 'remove_main_image';
            hiddenInput.value = '1';
            document.getElementById('productForm').appendChild(hiddenInput);
        }
    }

    // Удалить элемент галереи
    function removeGalleryItem(index) {
        const item = document.querySelector(`[data-index="${index}"]`);
        if (item) {
            item.remove();
        }

        // Проверяем, есть ли еще элементы в галерее
        const remainingItems = document.querySelectorAll('.gallery-preview-item');
        if (remainingItems.length === 0) {
            const preview = document.getElementById('galleryPreview');
            preview.style.display = 'none';
        }

        // Добавляем скрытое поле для удаления
        let hiddenInput = document.getElementById('removeGalleryItem');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'removeGalleryItem';
            hiddenInput.name = 'remove_gallery_item';
            document.getElementById('productForm').appendChild(hiddenInput);
        }
        hiddenInput.value = (hiddenInput.value ? hiddenInput.value + ',' : '') + index;
    }

    // Удалить файл для скачивания
    function removeDownloadFile() {
        const preview = document.getElementById('downloadFilePreview');
        preview.style.display = 'none';
        // Добавляем скрытое поле для удаления
        let hiddenInput = document.getElementById('removeDownloadFile');
        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = 'removeDownloadFile';
            hiddenInput.name = 'remove_download_file';
            hiddenInput.value = '1';
            document.getElementById('productForm').appendChild(hiddenInput);
        }
    }

    // Очистить все превью при закрытии модального окна
    function clearAllPreviews() {
        document.getElementById('mainImagePreview').style.display = 'none';
        document.getElementById('galleryPreview').style.display = 'none';
        document.getElementById('downloadFilePreview').style.display = 'none';
        document.getElementById('galleryPreviewGrid').innerHTML = '';

        // Очищаем ярлыки
        setProductBadges([]);

        // Удаляем скрытые поля
        const hiddenInputs = ['removeMainImage', 'removeGalleryItem', 'removeDownloadFile'];
        hiddenInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) input.remove();
        });
    }

    // Загружаем ярлыки при загрузке страницы
    console.log('Loading badges...');
    loadBadges();

    // Функция загрузки ярлыков
    function loadBadges() {
        console.log('Fetching badges from api/get-badges.php...');
        fetch('api/get-badges.php')
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Badges data received:', data);
                if (data.success) {
                    console.log('Rendering badges:', data.badges);
                    renderBadges(data.badges);
                } else {
                    console.error('Ошибка загрузки ярлыков:', data.message);
                    showBadgesError(data.message);
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки ярлыков:', error);
                showBadgesError('Ошибка подключения к серверу');
            });
    }

    // Функция загрузки ярлыков товара
    function loadProductBadges(productId) {
        console.log('Loading product badges for ID:', productId);
        fetch(`api/get-product-badges.php?product_id=${productId}`)
            .then(response => {
                console.log('Product badges response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Product badges data received:', data);
                if (data.success) {
                    console.log('Setting product badges:', data.badges);
                    // Устанавливаем выбранные ярлыки
                    data.badges.forEach(badge => {
                        const checkbox = document.getElementById(`badge_${badge.id}`);
                        if (checkbox) {
                            checkbox.checked = true;
                        }
                    });
                } else {
                    console.error('Ошибка загрузки ярлыков товара:', data.message);
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки ярлыков товара:', error);
            });
    }

    // Функция отображения ярлыков
    function renderBadges(badges) {
        console.log('renderBadges called with:', badges);
        const container = document.getElementById('badgesContainer');
        console.log('Container found:', container);

        if (!container) {
            console.error('badgesContainer not found!');
            return;
        }

        // Очищаем контейнер и убираем индикатор загрузки
        container.innerHTML = '';

        badges.forEach(badge => {
            const div = document.createElement('div');
            div.className = 'badge-item';
            div.dataset.badgeId = badge.id;

            div.innerHTML = `
            <input type="checkbox" id="badge_${badge.id}" name="badges[]" value="${badge.id}">
            <label for="badge_${badge.id}">${badge.name}</label>
            <span class="badge-preview" style="color: ${badge.color}; background-color: ${badge.background_color};">
                ${badge.name}
            </span>
        `;

            // Добавляем обработчик клика
            div.addEventListener('click', function (e) {
                if (e.target.type !== 'checkbox') {
                    const checkbox = div.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                    updateBadgeSelection(div, checkbox.checked);
                }
            });

            // Обработчик изменения чекбокса
            const checkbox = div.querySelector('input[type="checkbox"]');
            checkbox.addEventListener('change', function () {
                updateBadgeSelection(div, this.checked);
            });

            container.appendChild(div);
        });
    }

    // Функция показа ошибки загрузки ярлыков
    function showBadgesError(message) {
        const container = document.getElementById('badgesContainer');
        if (container) {
            container.innerHTML = `
                <div class="badges-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Ошибка загрузки ярлыков: ${message}</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadBadges()">
                        <i class="fas fa-redo"></i> Повторить
                    </button>
                </div>
            `;
        }
    }

    // Функция обновления визуального состояния ярлыка
    function updateBadgeSelection(badgeItem, isSelected) {
        if (isSelected) {
            badgeItem.classList.add('selected');
        } else {
            badgeItem.classList.remove('selected');
        }
    }

    // Функция загрузки ярлыков товара
    function loadProductBadges(productId) {
        fetch(`api/get-product-badges.php?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badgeIds = data.badges.map(badge => badge.badge_id);
                    setProductBadges(badgeIds);
                } else {
                    console.error('Ошибка загрузки ярлыков товара:', data.message);
                    setProductBadges([]);
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки ярлыков товара:', error);
                setProductBadges([]);
            });
    }

    // Функция установки выбранных ярлыков для товара
    function setProductBadges(badgeIds) {
        // Сбрасываем все ярлыки
        document.querySelectorAll('#badgesContainer .badge-item').forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            checkbox.checked = false;
            updateBadgeSelection(item, false);
        });

        // Устанавливаем выбранные ярлыки
        if (badgeIds && badgeIds.length > 0) {
            badgeIds.forEach(badgeId => {
                const item = document.querySelector(`[data-badge-id="${badgeId}"]`);
                if (item) {
                    const checkbox = item.querySelector('input[type="checkbox"]');
                    checkbox.checked = true;
                    updateBadgeSelection(item, true);
                }
            });
        }
    }

    console.log('=== SCRIPT V5.0 SETUP COMPLETE ===');
</script>

<?php include 'includes/footer.php'; ?>