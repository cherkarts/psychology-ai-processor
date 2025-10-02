<?php
// Чистая версия страницы управления товарами v2.2
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
                <h4 class="page-title">Управление товарами (v2.3 - ВСЕ ПОЛЯ)</h4>
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
                        <label for="productShortDescription">Краткое описание</label>
                        <textarea class="form-control" id="productShortDescription" name="short_description"
                            rows="2"></textarea>
                        <small class="form-text text-muted">Отобразится на карточке товара в списке.</small>
                    </div>

                    <div class="form-group">
                        <label for="productPrice">Цена *</label>
                        <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0"
                            required>
                        <small class="form-text text-muted">Для типа "Бесплатный" цена будет выставлена в 0.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="productOldPrice">Старая цена</label>
                            <input type="number" class="form-control" id="productOldPrice" step="0.01" min="0">
                        </div>
                        <small class="form-text text-muted">Для акций укажите старую цену и валюту.</small>
                        <div class="form-group col-md-6">
                            <label for="productCurrency">Валюта</label>
                            <select id="productCurrency" class="form-control">
                                <option value="RUB">RUB</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                            </select>
                        </div>
                        <small class="form-text text-muted">Тип влияет на отображение и требования к полям (цифровой —
                            можно указать ссылку/файл).</small>
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
                        <small class="form-text text-muted">Черновик не отображается на сайте.</small>
                        <div class="form-group col-md-6">
                            <label for="productStatus">Статус</label>
                            <select id="productStatus" class="form-control">
                                <option value="active">Активен</option>
                                <option value="inactive">Неактивен</option>
                                <option value="draft">Черновик</option>
                            </select>
                        </div>
                        <small class="form-text text-muted">Если товар физический, включите наличие и укажите
                            количество.</small>
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
                        <label for="productImage">Изображение</label>
                        <input type="file" class="form-control" id="productImage" name="image" accept="image/*">
                        <div id="productImagePreview" style="margin-top:8px; display:none;">
                            <small>Текущее изображение:</small>
                            <div style="display:flex; align-items:center; gap:10px; margin-top:6px;">
                                <img id="productImageThumb" src="" alt="preview"
                                    style="max-width:120px; max-height:90px; border:1px solid #ddd; padding:2px; background:#fff;">
                                <a id="productImageLink" href="#" target="_blank"
                                    style="word-break:break-all; max-width:300px;"></a>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    id="productImageClear">Очистить</button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Рекомендуемый размер 1200×800, формат JPG/PNG.</small>
                    </div>

                    <div class="form-group">
                        <label for="productGallery">Галерея (несколько изображений)</label>
                        <input type="file" class="form-control" id="productGallery" accept="image/*" multiple>
                        <div id="productGalleryPreview" style="display:flex; gap:8px; flex-wrap:wrap; margin-top:8px;">
                        </div>
                        <small class="form-text text-muted">Можно выбрать сразу несколько файлов — появится
                            предпросмотр.</small>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="productActive" name="is_active" checked>
                            <label class="form-check-label" for="productActive">Активен</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ярлыки</label>
                        <div id="productBadgesBox" class="form-row" style="gap:10px;">
                            <?php $badgeOptions = ['sale' => 'Распродажа', 'discount' => 'Скидка', 'new' => 'Новинка', 'bestseller' => 'Бестселлер', 'hit' => 'Хит', 'limited' => 'Ограниченно', 'promo' => 'Промо'];
                            foreach ($badgeOptions as $bv => $bl): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="badge_<?php echo $bv; ?>"
                                        value="<?php echo $bv; ?>">
                                    <label class="form-check-label"
                                        for="badge_<?php echo $bv; ?>"><?php echo $bl; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <small class="form-text text-muted">Выберите один или несколько ярлыков для карточки.</small>
                    </div>
                    <div class="form-group">
                        <label for="productTags">Теги (через запятую)</label>
                        <input type="text" class="form-control" id="productTags" placeholder="курс,семинар">
                        <small class="form-text text-muted">Используются для поиска и фильтрации.</small>
                    </div>

                    <div class="form-group">
                        <label for="productDownloadUrl">Ссылка для скачивания (digital)</label>
                        <input type="text" class="form-control" id="productDownloadUrl" placeholder="https://...">
                        <small class="form-text text-muted">Если товар цифровой, можно указать прямую ссылку или
                            загрузить файл ниже.</small>
                    </div>
                    <div class="form-group">
                        <label for="productDownloadFile">Файл для скачивания (загрузка)</label>
                        <input type="file" class="form-control" id="productDownloadFile" accept="*/*">
                        <div id="productDownloadPreview" style="margin-top:6px; display:none;"><a
                                id="productDownloadLink" href="#" target="_blank"></a></div>
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
    // Версия скрипта для принудительного обновления кэша
    console.log('Products script loaded v2.3 - ALL FIELDS at', new Date().toISOString());

    // Принудительное обновление кэша
    if ('caches' in window) {
        caches.keys().then(function (names) {
            for (let name of names) {
                caches.delete(name);
            }
        });
    }

    // Принудительная очистка кэша
    if ('caches' in window) {
        caches.keys().then(function (names) {
            for (let name of names) {
                caches.delete(name);
            }
        });
    }

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
        // Загружаем данные товара
        fetch('api/get-product.php?id=' + id + '&v=' + Date.now())
            .then(response => response.json())
            .then(data => {
                console.log('Product data received:', data);
                if (data.success) {
                    const product = data.product;

                    // Безопасная функция для валидации URL
                    function isValidUrl(url) {
                        if (!url || url === '#' || url === '') return false;
                        return url.startsWith('http') || url.startsWith('/') || url.startsWith('./');
                    }
                    document.getElementById('productModalTitle').textContent = 'Редактировать товар';
                    document.getElementById('productId').value = product.id;
                    document.getElementById('productTitle').value = product.title;
                    document.getElementById('productDescription').value = product.description || '';
                    document.getElementById('productShortDescription').value = product.short_description || '';
                    document.getElementById('productPrice').value = product.price;
                    document.getElementById('productCategory').value = product.category_id || '';
                    document.getElementById('productActive').checked = !!(product.is_active ?? (product.status === 'active'));
                    // Доп. поля
                    document.getElementById('productOldPrice').value = product.old_price ?? '';
                    document.getElementById('productCurrency').value = product.currency ?? 'RUB';
                    document.getElementById('productType').value = product.type ?? 'digital';
                    document.getElementById('productStatus').value = product.status ?? 'active';
                    document.getElementById('productInStock').checked = !!(product.in_stock ?? 1);
                    document.getElementById('productQuantity').value = product.quantity ?? 0;
                    document.getElementById('productSort').value = product.sort_order ?? 0;
                    // Ярлыки из ответа ставим на чекбоксы
                    (function () {
                        let badges = product.badges; if (typeof badges === 'string') { try { badges = JSON.parse(badges); } catch (e) { } }
                        const box = document.getElementById('productBadgesBox'); if (box) {
                            const set = new Set(Array.isArray(badges) ? badges : []);
                            box.querySelectorAll('input[type="checkbox"]').forEach(cb => { cb.checked = set.has(cb.value); });
                        }
                    })();
                    document.getElementById('productTags').value = Array.isArray(product.tags) ? product.tags.join(',') : (product.tags || '');
                    document.getElementById('productDownloadUrl').value = product.download_url || '';
                    // Превью изображения
                    (function () {
                        const wrap = document.getElementById('productImagePreview');
                        const link = document.getElementById('productImageLink');
                        const img = document.getElementById('productImageThumb');
                        const imgUrl = product.image || product.featured_image || '';
                        if (isValidUrl(imgUrl)) {
                            wrap.style.display = 'block';
                            img.src = imgUrl;
                            link.href = imgUrl;
                            link.textContent = imgUrl;
                        } else {
                            wrap.style.display = 'none';
                        }
                        const clearBtn = document.getElementById('productImageClear');
                        if (clearBtn) clearBtn.onclick = function () { wrap.style.display = 'none'; img.src = ''; link.href = '#'; link.textContent = ''; window.__clearProductImage = true; };
                    })();
                    // Превью скачиваемого файла
                    (function () {
                        const prev = document.getElementById('productDownloadPreview');
                        const a = document.getElementById('productDownloadLink');
                        const url = product.download_file || '';
                        if (isValidUrl(url)) {
                            prev.style.display = 'block';
                            a.href = url;
                            a.textContent = url;
                        } else {
                            prev.style.display = 'none';
                        }
                    })();
                    // Галерея
                    try {
                        const galWrap = document.getElementById('productGalleryPreview');
                        galWrap.innerHTML = '';
                        let gallery = product.gallery;
                        if (typeof gallery === 'string') { try { gallery = JSON.parse(gallery); } catch (e) { } }
                        if (Array.isArray(gallery)) {
                            gallery.forEach(u => {
                                if (isValidUrl(u)) {
                                    const im = document.createElement('img');
                                    im.src = u;
                                    im.style.maxWidth = '80px';
                                    im.style.maxHeight = '60px';
                                    im.style.border = '1px solid #ddd';
                                    im.style.padding = '2px';
                                    galWrap.appendChild(im);
                                }
                            });
                        }
                    } catch (e) { }
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
    document.getElementById('productForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const data = {};
        data.id = document.getElementById('productId').value || null;
        data.title = document.getElementById('productTitle').value || '';
        data.description = document.getElementById('productDescription').value || '';
        data.short_description = document.getElementById('productShortDescription').value || '';
        data.price = document.getElementById('productPrice').value || '';
        data.category_id = document.getElementById('productCategory').value || null;
        data.is_active = document.getElementById('productActive').checked;
        // Доп. поля
        data.old_price = document.getElementById('productOldPrice').value || null;
        data.currency = document.getElementById('productCurrency').value || 'RUB';
        data.type = document.getElementById('productType').value || 'digital';
        data.status = document.getElementById('productStatus').value || 'active';
        data.in_stock = document.getElementById('productInStock').checked;
        data.quantity = document.getElementById('productQuantity').value || 0;
        data.sort_order = document.getElementById('productSort').value || 0;
        // Ярлыки из чекбоксов
        (function () {
            const box = document.getElementById('productBadgesBox');
            if (box) {
                const arr = []; box.querySelectorAll('input[type="checkbox"]:checked').forEach(cb => arr.push(cb.value));
                data.badges = arr;
            }
        })();
        data.tags = (document.getElementById('productTags').value || '').split(',').map(s => s.trim()).filter(Boolean);
        data.download_url = document.getElementById('productDownloadUrl').value || '';

        // Загрузка изображения при необходимости
        try {
            const fileInput = document.getElementById('productImage');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                const f = fileInput.files[0];
                const fd = new FormData();
                fd.append('file', f);
                fd.append('type', 'image');
                fd.append('scope', 'products');
                const up = await fetch('/api/upload-media.php', { method: 'POST', body: fd });
                const uj = await up.json();
                if (uj && uj.success && uj.filepath) { data.image = uj.filepath; }
                else { alert('Ошибка загрузки изображения: ' + (uj?.error || '')); return; }
            } else if (window.__clearProductImage) { data.image = ''; window.__clearProductImage = false; }
            // Загрузка файла для скачивания
            const df = document.getElementById('productDownloadFile');
            if (df && df.files && df.files[0]) {
                const fd2 = new FormData();
                fd2.append('file', df.files[0]);
                fd2.append('type', 'image');
                fd2.append('scope', 'products');
                const up2 = await fetch('/api/upload-media.php', { method: 'POST', body: fd2 });
                const uj2 = await up2.json();
                if (uj2 && uj2.success && uj2.filepath) { data.download_file = uj2.filepath; }
            }
            // Галерея: мультизагрузка
            const galInput = document.getElementById('productGallery');
            if (galInput && galInput.files && galInput.files.length) {
                const urls = [];
                for (let i = 0; i < galInput.files.length; i++) {
                    const fdg = new FormData(); fdg.append('file', galInput.files[i]); fdg.append('type', 'image'); fdg.append('scope', 'products');
                    const upg = await fetch('/api/upload-media.php', { method: 'POST', body: fdg });
                    const ujg = await upg.json(); if (ujg && ujg.success && ujg.filepath) urls.push(ujg.filepath);
                }
                if (urls.length) data.gallery = urls;
            }
        } catch (err) { console.error(err); alert('Ошибка загрузки изображения'); return; }

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