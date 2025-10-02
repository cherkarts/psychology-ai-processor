<?php
require_once __DIR__ . '/../includes/Models/Order.php';
require_once __DIR__ . '/../includes/Models/Article.php';
require_once __DIR__ . '/../includes/Models/Meditation.php';
require_once __DIR__ . '/../includes/Models/Review.php';
require_once __DIR__ . '/../includes/Models/Product.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../includes/admin-functions.php';

requirePermission('products');
$pageTitle = 'Редактор товаров';

$productId = $_GET['id'] ?? '';
$isEdit = !empty($productId);
$product = null;

// Очищаем старые сообщения при загрузке страницы (не POST запрос)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    unset($_SESSION['success_message']);
    unset($_SESSION['error_message']);
}

if ($isEdit) {
    $product = getProductById($productId);
    if (!$product) {
        $_SESSION['error_message'] = 'Товар не найден';
        header('Location: products.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Неверный токен безопасности';
    } else {
        // Отладочная информация
        error_log('POST data for description: ' . ($_POST['description'] ?? 'NOT SET'));
        error_log('POST description length: ' . strlen($_POST['description'] ?? ''));

        $result = saveProduct($_POST, $_FILES);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
            if (!$isEdit && isset($result['slug'])) {
                header('Location: product-edit.php?id=' . urlencode($result['slug']));
                exit();
            }
            // PRG: redirect to avoid resubmission and reload updated data
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

// Загружаем данные товара после POST запроса (если это редактирование)
if ($isEdit && !$product) {
    $product = getProductById($productId);
    if (!$product) {
        $_SESSION['error_message'] = 'Товар не найден';
        header('Location: products.php');
        exit();
    }
}

$categories = getProductCategories();
require_once __DIR__ . '/includes/header.php';
?>

<!-- CKEditor 5 for product description -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
<?php
?>

<div class="product-editor-container">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo sanitizeOutput($_SESSION['success_message']);
            unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo sanitizeOutput($_SESSION['error_message']);
            unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <div class="page-header">
        <div class="header-content">
            <h1>
                <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus'; ?>"></i>
                <?php echo $isEdit ? 'Редактировать товар' : 'Новый товар'; ?>
            </h1>
            <p><?php echo $isEdit ? 'Редактирование существующего товара' : 'Создание нового товара'; ?></p>
        </div>
        <div class="header-actions">
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
            <?php if ($isEdit): ?>
                <a href="../product.php?slug=<?php echo urlencode($product['slug']); ?>" class="btn btn-info"
                    target="_blank">
                    <i class="fas fa-eye"></i> Предварительный просмотр
                </a>
            <?php endif; ?>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="product-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="product_id" value="<?php echo sanitizeOutput($productId); ?>">
        <input type="hidden" name="action" value="<?php echo $isEdit ? 'update' : 'create'; ?>">

        <div class="form-layout">
            <div class="main-content">
                <div class="form-section">
                    <h3>Основная информация</h3>

                    <div class="form-group">
                        <label for="title">Название товара *</label>
                        <input type="text" id="title" name="title"
                            value="<?php echo sanitizeOutput($product['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="slug">URL-адрес (slug)</label>
                        <input type="text" id="slug" name="slug"
                            value="<?php echo sanitizeOutput($product['slug'] ?? ''); ?>">
                        <small>Оставьте пустым для автоматической генерации</small>
                    </div>

                    <div class="form-group">
                        <label for="short_description">Краткое описание</label>
                        <textarea id="short_description" name="short_description" rows="3" maxlength="200"
                            placeholder="Краткое описание для карточки товара (до 200 символов)"><?php echo sanitizeOutput($product['short_description'] ?? ''); ?></textarea>
                        <small>Для списка/карточки товара</small>
                        <div class="char-counter"><span id="shortDescriptionCounter">0</span>/200</div>
                    </div>

                    <div class="form-group">
                        <label for="description">Полное описание *</label>
                        <textarea id="description" name="description" rows="12" placeholder="Подробное описание товара"><?php
                        $description = $product['description'] ?? '';
                        echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
                        ?></textarea>
                        <?php if ($isEdit && isset($product['description'])): ?>
                            <!-- Отладочная информация -->
                            <script>
                                console.log('PHP Debug - Product description length:', <?php echo strlen($product['description'] ?? ''); ?>);
                                console.log('PHP Debug - Product description preview:', '<?php echo addslashes(substr($product['description'] ?? '', 0, 100)); ?>...');
                                console.log('PHP Debug - Description variable length:', <?php echo strlen($description); ?>);
                                console.log('PHP Debug - Description variable preview:', '<?php echo addslashes(substr($description, 0, 100)); ?>...');

                                // Проверяем содержимое textarea
                                setTimeout(() => {
                                    const textarea = document.getElementById('description');
                                    console.log('Textarea content length:', textarea.value.length);
                                    console.log('Textarea content preview:', textarea.value.substring(0, 100));
                                }, 100);
                            </script>
                        <?php endif; ?>
                        <small>Поддерживает форматирование</small>
                        <div class="field-error" id="description-error"
                            style="display: none; color: #dc3545; font-size: 0.875rem; margin-top: 5px;">
                            Поле "Полное описание" обязательно для заполнения
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="features">Характеристики</label>
                        <div id="featuresList">
                            <?php
                            $featuresArr = [];
                            if (!empty($product['features'])) {
                                $featuresArr = is_string($product['features']) ? (json_decode($product['features'], true) ?: []) : $product['features'];
                            }
                            $featurePairs = [];
                            if (!empty($featuresArr)) {
                                foreach ($featuresArr as $f) {
                                    if (is_array($f) && (isset($f['name']) || isset($f['value']))) {
                                        $featurePairs[] = [
                                            'name' => (string) ($f['name'] ?? ''),
                                            'value' => (string) ($f['value'] ?? ''),
                                        ];
                                    } elseif (is_string($f)) {
                                        // try split by ':' if provided as single string
                                        if (strpos($f, ':') !== false) {
                                            [$n, $v] = array_map('trim', explode(':', $f, 2));
                                            $featurePairs[] = ['name' => $n, 'value' => $v];
                                        } else {
                                            $featurePairs[] = ['name' => $f, 'value' => ''];
                                        }
                                    }
                                }
                            }
                            if (!empty($featurePairs)):
                                foreach ($featurePairs as $pair): ?>
                                    <div class="feature-item"
                                        style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                                        <input type="text" name="features_name[]"
                                            value="<?php echo sanitizeOutput($pair['name']); ?>" class="feature-input"
                                            placeholder="Название (напр. Длительность)" style="flex:1;">
                                        <input type="text" name="features_value[]"
                                            value="<?php echo sanitizeOutput($pair['value']); ?>" class="feature-input"
                                            placeholder="Значение (напр. 45 минут)" style="flex:1;">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick="removeFeature(this)"
                                            title="Удалить"><i class="fas fa-trash"></i></button>
                                    </div>
                                <?php endforeach;
                            endif; ?>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addFeaturePair()"><i
                                class="fas fa-plus"></i> Добавить характеристику</button>
                        <small>Два поля: название и значение</small>
                    </div>

                    <div class="form-group">
                        <label for="tags">Теги</label>
                        <input type="text" id="tags" name="tags" value="<?php
                        $tagArr = [];
                        if (!empty($product['tags'])) {
                            $tagArr = is_string($product['tags']) ? (json_decode($product['tags'], true) ?: []) : $product['tags'];
                        }
                        echo sanitizeOutput(implode(', ', $tagArr));
                        ?>" placeholder="тег1, тег2, тег3">
                        <small>Через запятую</small>
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <div class="form-section">
                    <h3>Параметры товара</h3>

                    <div class="form-group">
                        <label for="price">Цена (₽) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0"
                            value="<?php echo sanitizeOutput($product['price'] ?? '0'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="old_price">Старая цена (₽)</label>
                        <input type="number" id="old_price" name="old_price" step="0.01" min="0"
                            value="<?php echo sanitizeOutput($product['old_price'] ?? ''); ?>">
                        <small>Будет показана перечеркнутой</small>
                    </div>

                    <div class="form-group">
                        <label for="category">Категория</label>
                        <select id="category" name="category">
                            <option value="">Без категории</option>
                            <?php
                            // Получаем список категорий
                            $categories = [];
                            if ($db) {
                                try {
                                    $stmt = $db->query("SELECT id, name FROM product_categories WHERE is_active = 1 ORDER BY name");
                                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                } catch (Exception $e) {
                                    error_log("Error fetching categories: " . $e->getMessage());
                                }
                            }

                            foreach ($categories as $category) {
                                $selected = ($product['category_id'] ?? '') == $category['id'] ? 'selected' : '';
                                echo "<option value=\"{$category['id']}\" {$selected}>" . htmlspecialchars($category['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="type">Тип товара</label>
                        <select id="type" name="type" onchange="toggleTypeFields()">
                            <option value="digital" <?php echo ($product['type'] ?? 'digital') === 'digital' ? 'selected' : ''; ?>>Цифровой</option>
                            <option value="physical" <?php echo ($product['type'] ?? '') === 'physical' ? 'selected' : ''; ?>>Физический</option>
                            <option value="service" <?php echo ($product['type'] ?? '') === 'service' ? 'selected' : ''; ?>>Услуга</option>
                            <option value="free" <?php echo ($product['type'] ?? '') === 'free' ? 'selected' : ''; ?>>
                                Бесплатный</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo ($product['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Черновик</option>
                            <option value="active" <?php echo ($product['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Активный</option>
                            <option value="inactive" <?php echo ($product['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Неактивный</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ярлыки товара</label>
                        <div class="badges-container">
                            <?php
                            // Получаем текущие ярлыки
                            $currentBadges = [];
                            if (!empty($product['badges'])) {
                                $currentBadges = is_string($product['badges']) ? json_decode($product['badges'], true) : $product['badges'];
                                if (!is_array($currentBadges))
                                    $currentBadges = [];
                            }

                            // Доступные ярлыки
                            $availableBadges = [
                                'sale' => ['label' => 'Скидка', 'color' => '#dc3545', 'bg' => '#f8d7da'],
                                'new' => ['label' => 'Новинка', 'color' => '#28a745', 'bg' => '#d4edda'],
                                'hit' => ['label' => 'Хит продаж', 'color' => '#ffc107', 'bg' => '#fff3cd'],
                                'action' => ['label' => 'Акция', 'color' => '#17a2b8', 'bg' => '#d1ecf1'],
                                'limited' => ['label' => 'Ограниченное предложение', 'color' => '#6f42c1', 'bg' => '#e2d9f3']
                            ];

                            foreach ($availableBadges as $key => $badge) {
                                $checked = in_array($key, $currentBadges) ? 'checked' : '';
                                echo "<label class='badge-option'>";
                                echo "<input type='checkbox' name='badges[]' value='{$key}' {$checked}>";
                                echo "<span class='badge-preview' style='background: {$badge['bg']}; color: {$badge['color']}; border: 1px solid {$badge['color']};'>";
                                echo $badge['label'];
                                echo "</span>";
                                echo "</label>";
                            }
                            ?>
                        </div>
                        <small>Выберите ярлыки для отображения на карточке товара</small>
                    </div>

                    <div class="form-group">
                        <label for="category">Категория</label>
                        <select id="category" name="category">
                            <option value="">Выберите категорию</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($product['category_id'] ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group physical-only">
                        <label>
                            <input type="checkbox" name="in_stock" value="1" <?php echo ($product['in_stock'] ?? 1) ? 'checked' : ''; ?>> В наличии
                        </label>
                    </div>

                    <div class="form-group physical-only">
                        <label for="quantity">Количество</label>
                        <input type="number" id="quantity" name="quantity" min="0"
                            value="<?php echo sanitizeOutput($product['quantity'] ?? 0); ?>">
                    </div>
                </div>

                <div class="form-section">
                    <h3>Изображения</h3>
                    <div class="form-group">
                        <label for="image">Главное изображение</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <?php if (!empty($product['image'])): ?>
                            <div class="current-image">
                                <p>Текущее изображение:</p>
                                <img src="<?php echo sanitizeOutput($product['image']); ?>" alt="Current image"
                                    class="preview-image" style="max-width:120px; height:auto; border-radius:6px;">
                                <label><input type="checkbox" name="remove_image" value="1"> Удалить текущее
                                    изображение</label>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="imagePreview" class="image-preview" style="display:none;">
                        <p>Предпросмотр:</p>
                        <img id="previewImg" alt="Preview" style="max-width:120px; height:auto; border-radius:6px;">
                    </div>

                    <div class="form-group">
                        <label for="gallery">Галерея (изображения/видео)</label>
                        <input type="file" id="gallery" name="gallery[]" accept="image/*,video/*" multiple>
                        <small>Можно выбрать несколько файлов</small>
                    </div>

                    <?php if (!empty($product['gallery'])): ?>
                        <div class="current-gallery">
                            <p>Текущая галерея:</p>
                            <div class="gallery-grid">
                                <?php
                                $gallery = is_string($product['gallery']) ? json_decode($product['gallery'], true) : $product['gallery'];
                                if (is_array($gallery)):
                                    foreach ($gallery as $idx => $item):
                                        $path = is_array($item) ? ($item['path'] ?? '') : $item;
                                        $type = is_array($item) ? ($item['type'] ?? 'image') : 'image';
                                        ?>
                                        <div class="gallery-item">
                                            <?php if ($type === 'video'): ?>
                                                <video src="<?php echo sanitizeOutput($path); ?>" controls class="preview-image"
                                                    style="max-width:120px; height:auto; border-radius:6px;"></video>
                                            <?php else: ?>
                                                <img src="<?php echo sanitizeOutput($path); ?>" class="preview-image" alt="gallery"
                                                    style="max-width:120px; height:auto; border-radius:6px;">
                                            <?php endif; ?>
                                            <label><input type="checkbox" name="remove_gallery[]" value="<?php echo $idx; ?>">
                                                Удалить</label>
                                        </div>
                                    <?php endforeach; endif; ?>
                            </div>
                            <input type="hidden" name="existing_gallery"
                                value="<?php echo htmlspecialchars(json_encode($gallery ?? [])); ?>">
                        </div>
                    <?php endif; ?>

                    <div id="galleryPreview" class="gallery-preview" style="display:none;">
                        <p>Предпросмотр загружаемых файлов:</p>
                        <div id="galleryPreviewGrid" class="gallery-grid"
                            style="display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:10px;">
                        </div>
                    </div>
                </div>

                <div class="form-section digital-only">
                    <h3>Файл для скачивания</h3>
                    <div class="form-group">
                        <label for="download_file">Файл</label>
                        <input type="file" id="download_file" name="download_file">
                        <?php if (!empty($product['download_file'])): ?>
                            <div class="current-file">
                                <p>Текущий файл: <?php echo basename($product['download_file']); ?></p>
                                <label><input type="checkbox" name="remove_file" value="1"> Удалить файл</label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-section">
                    <h3>SEO параметры</h3>

                    <div class="form-group">
                        <label for="meta_title">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title"
                            value="<?php echo sanitizeOutput($product['meta_title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="meta_description">Meta Description</label>
                        <textarea id="meta_description" name="meta_description"
                            rows="3"><?php echo sanitizeOutput($product['meta_description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="meta_keywords">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords"
                            value="<?php echo sanitizeOutput($product['meta_keywords'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit_action" value="save" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?php echo $isEdit ? 'Обновить товар' : 'Создать товар'; ?>
            </button>
            <?php if (!$isEdit || ($product['status'] ?? 'draft') === 'draft'): ?>
                <button type="submit" name="submit_action" value="publish" class="btn btn-info">
                    <i class="fas fa-check"></i> Сохранить и опубликовать
                </button>
            <?php endif; ?>
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Отмена
            </a>
        </div>
    </form>
</div>

<style>
    .product-editor-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .header-content h1 {
        margin: 0;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .header-content p {
        margin: 5px 0 0 0;
        color: #666;
    }

    .header-actions {
        display: flex;
        gap: 10px;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .form-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 30px;
    }

    .main-content {
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .form-section h3 {
        margin: 0 0 20px 0;
        color: #333;
        font-size: 1.1em;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #333;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }

    .form-group small {
        display: block;
        margin-top: 5px;
        color: #666;
        font-size: 12px;
    }

    .field-error {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
        display: none;
    }

    .form-group.error input,
    .form-group.error textarea,
    .form-group.error select {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-weight: normal;
    }

    .checkbox-label input[type="checkbox"] {
        width: auto;
        margin: 0;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 30px;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background-color: #545b62;
    }

    .btn-info {
        background-color: #17a2b8;
        color: white;
    }

    .btn-info:hover {
        background-color: #138496;
    }

    @media (max-width: 768px) {
        .form-layout {
            grid-template-columns: 1fr;
        }

        .page-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .header-actions {
            width: 100%;
            justify-content: center;
        }

        .form-actions {
            flex-direction: column;
        }
    }

    /* Стили для ярлыков товара */
    .badges-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }

    .badge-option {
        display: flex;
        align-items: center;
        cursor: pointer;
        margin: 0;
    }

    .badge-option input[type="checkbox"] {
        margin-right: 8px;
        transform: scale(1.2);
    }

    .badge-preview {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .badge-option:hover .badge-preview {
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .badge-option input[type="checkbox"]:checked+.badge-preview {
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
    }
</style>

<script>
    // Normalize price input before submit (comma to dot)
    (function () {
        const form = document.querySelector('.product-form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            // Принудительная синхронизация данных из CKEditor перед отправкой
            if (descriptionEditor) {
                const editorData = descriptionEditor.getData();
                const textarea = document.getElementById('description');
                if (textarea) {
                    console.log('Before sync - Textarea value length:', textarea.value.length);
                    console.log('Before sync - Editor data length:', editorData.length);
                    console.log('Before sync - Editor data preview:', editorData.substring(0, 100));

                    // Принудительно устанавливаем данные
                    textarea.value = editorData;

                    // Дополнительная проверка
                    if (textarea.value !== editorData) {
                        console.error('Sync failed! Trying alternative method...');
                        // Альтернативный способ синхронизации
                        descriptionEditor.updateSourceElement();
                        textarea.value = descriptionEditor.getData();
                    }

                    console.log('After sync - Textarea value length:', textarea.value.length);
                    console.log('After sync - Textarea value preview:', textarea.value.substring(0, 100));
                    console.log('CKEditor data synced to textarea before submit');

                    // Финальная проверка синхронизации
                    if (textarea.value.length === 0 && editorData.length > 0) {
                        console.error('CRITICAL: Sync failed completely! Blocking form submission.');
                        e.preventDefault();
                        alert('Ошибка синхронизации данных. Попробуйте еще раз.');
                        return false;
                    }
                }
            }

            // Очищаем предыдущие ошибки
            const errorElements = document.querySelectorAll('.field-error');
            errorElements.forEach(el => el.style.display = 'none');

            let isValid = true;

            // Валидация цены
            const priceEl = document.getElementById('price');
            if (priceEl && priceEl.value) {
                priceEl.value = priceEl.value.replace(',', '.');
            }

            // Валидация описания (CKEditor)
            if (descriptionEditor) {
                const descriptionContent = descriptionEditor.getData();
                const descriptionText = descriptionContent.replace(/<[^>]*>/g, '').trim();

                if (!descriptionText) {
                    const errorEl = document.getElementById('description-error');
                    if (errorEl) {
                        errorEl.style.display = 'block';
                    }
                    isValid = false;
                }
            } else {
                // Fallback для случая, если CKEditor не загрузился
                const descriptionEl = document.getElementById('description');
                if (descriptionEl && !descriptionEl.value.trim()) {
                    const errorEl = document.getElementById('description-error');
                    if (errorEl) {
                        errorEl.style.display = 'block';
                    }
                    isValid = false;
                }
            }

            // Валидация названия
            const titleEl = document.getElementById('title');
            if (titleEl && !titleEl.value.trim()) {
                const errorEl = document.createElement('div');
                errorEl.className = 'field-error';
                errorEl.style.cssText = 'color: #dc3545; font-size: 0.875rem; margin-top: 5px;';
                errorEl.textContent = 'Поле "Название" обязательно для заполнения';
                titleEl.parentNode.appendChild(errorEl);
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                // Прокручиваем к первой ошибке
                const firstError = document.querySelector('.field-error[style*="block"]');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });
    })();
    // Initialize CKEditor for product description
    let descriptionEditor;

    // Ждем загрузки DOM и данных
    document.addEventListener('DOMContentLoaded', function () {
        // Добавляем задержку для гарантированной загрузки данных
        setTimeout(() => {
            const descriptionTextarea = document.querySelector('#description');
            if (descriptionTextarea) {
                console.log('Initializing CKEditor...');
                console.log('Textarea value before init:', descriptionTextarea.value);
                console.log('Textarea value length before init:', descriptionTextarea.value ? descriptionTextarea.value.length : 0);

                ClassicEditor
                    .create(descriptionTextarea, {
                        toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'undo', 'redo'],
                        language: 'ru'
                    })
                    .then(editor => {
                        descriptionEditor = editor;
                        console.log('CKEditor initialized successfully');

                        // Принудительно загружаем данные в редактор
                        const currentData = descriptionTextarea.value;
                        console.log('Textarea value after init:', currentData);
                        console.log('Textarea value length after init:', currentData ? currentData.length : 0);

                        if (currentData && currentData.trim()) {
                            editor.setData(currentData);
                            console.log('CKEditor data loaded:', currentData.substring(0, 100) + '...');
                        } else {
                            console.log('No data to load in CKEditor');
                        }

                        // Дополнительная проверка через 1 секунду
                        setTimeout(() => {
                            const delayedData = document.getElementById('description').value;
                            console.log('Delayed check - textarea value length:', delayedData.length);
                            console.log('Delayed check - textarea value preview:', delayedData.substring(0, 100));

                            if (delayedData && delayedData.trim() && delayedData !== editor.getData()) {
                                console.log('Loading delayed data into CKEditor...');
                                editor.setData(delayedData);
                            }
                        }, 1000);

                        // Добавляем автоматическую синхронизацию при изменении
                        editor.model.document.on('change:data', () => {
                            const editorData = editor.getData();
                            const textarea = document.getElementById('description');
                            if (textarea && textarea.value !== editorData) {
                                textarea.value = editorData;
                                console.log('Auto-sync: CKEditor data synced to textarea');
                            }
                        });

                        // Обновляем данные в редакторе после успешного сохранения
                        const successAlert = document.querySelector('.alert-success');
                        if (successAlert) {
                            // Если есть сообщение об успехе, обновляем данные в редакторе
                            setTimeout(() => {
                                const updatedData = descriptionTextarea.value;
                                if (updatedData && updatedData.trim()) {
                                    editor.setData(updatedData);
                                    console.log('CKEditor data updated after save');
                                }
                            }, 100);
                        }
                    })
                    .catch(error => {
                        console.error('CKEditor init failed:', error);
                    });
            }
        }, 500); // Задержка 500ms для гарантированной загрузки данных
    });

    // Функция для обновления данных в CKEditor
    function updateCKEditorData() {
        if (descriptionEditor) {
            const textarea = document.querySelector('#description');
            if (textarea && textarea.value) {
                descriptionEditor.setData(textarea.value);
                console.log('CKEditor data manually updated');
            }
        }
    }

    // Обновляем данные через небольшую задержку после загрузки страницы
    setTimeout(updateCKEditorData, 500);

    // Дополнительная функция для принудительной загрузки данных
    function forceLoadCKEditorData() {
        if (descriptionEditor) {
            const textarea = document.querySelector('#description');
            if (textarea && textarea.value) {
                console.log('Force loading data into CKEditor...');
                descriptionEditor.setData(textarea.value);
                console.log('Data force loaded:', textarea.value.substring(0, 100) + '...');
            }
        }
    }

    // Принудительная загрузка через 2 секунды
    setTimeout(forceLoadCKEditorData, 2000);

    // Добавляем кнопку для ручной загрузки данных (для отладки)
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(() => {
            const form = document.querySelector('.product-form');
            if (form) {
                const debugButton = document.createElement('button');
                debugButton.type = 'button';
                debugButton.textContent = 'Загрузить данные в CKEditor';
                debugButton.style.cssText = 'margin: 10px; padding: 5px 10px; background: #007cba; color: white; border: none; border-radius: 3px; cursor: pointer;';
                debugButton.onclick = forceLoadCKEditorData;
                form.insertBefore(debugButton, form.firstChild);
            }
        }, 1000);
    });

    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function () {
        const title = this.value;
        const slugField = document.getElementById('slug');

        if (slugField.value === '') {
            const slug = title
                .toLowerCase()
                .replace(/[а-яё]/g, function (char) {
                    const map = {
                        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e',
                        'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
                        'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
                        'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch', 'ъ': '',
                        'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
                    };
                    return map[char] || char;
                })
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/[\s-]+/g, '-')
                .trim();

            slugField.value = slug;
        }
    });

    // Счетчик для краткого описания
    (function () {
        const f = document.getElementById('short_description');
        const c = document.getElementById('shortDescriptionCounter');
        if (f && c) {
            const upd = () => { c.textContent = (f.value || '').length; };
            upd();
            f.addEventListener('input', upd);
        }
    })();

    // Предпросмотр главного изображения
    document.getElementById('image')?.addEventListener('change', function (e) {
        const file = e.target.files[0];
        const wrap = document.getElementById('imagePreview');
        const img = document.getElementById('previewImg');
        if (!wrap || !img) return;
        if (file) {
            const reader = new FileReader();
            reader.onload = ev => { img.src = ev.target.result; wrap.style.display = 'block'; };
            reader.readAsDataURL(file);
        } else { wrap.style.display = 'none'; }
    });

    // Переключение полей по типу товара
    function toggleTypeFields() {
        const type = document.getElementById('type')?.value || 'digital';
        document.querySelectorAll('.physical-only').forEach(el => { el.style.display = (type === 'physical') ? 'block' : 'none'; });
        document.querySelectorAll('.digital-only').forEach(el => { el.style.display = (type === 'digital' || type === 'free') ? 'block' : 'none'; });
        const priceGroup = document.getElementById('price');
        if (type === 'free' && priceGroup) priceGroup.value = 0;
    }
    document.getElementById('type')?.addEventListener('change', toggleTypeFields);
    toggleTypeFields();

    // Предпросмотр галереи
    document.getElementById('gallery')?.addEventListener('change', function (e) {
        const files = Array.from(e.target.files || []);
        const wrap = document.getElementById('galleryPreview');
        const grid = document.getElementById('galleryPreviewGrid');
        if (!wrap || !grid) return;
        if (!files.length) { wrap.style.display = 'none'; grid.innerHTML = ''; return; }
        grid.innerHTML = '';
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = ev => {
                const div = document.createElement('div');
                div.className = 'gallery-item';
                if (file.type.startsWith('video/')) {
                    div.innerHTML = `<video src="${ev.target.result}" controls class="preview-image" style="max-width:120px; height:auto; border-radius:6px;"></video>`;
                } else {
                    div.innerHTML = `<img src="${ev.target.result}" class="preview-image" alt="" style="max-width:120px; height:auto; border-radius:6px;">`;
                }
                grid.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
        wrap.style.display = 'block';
    });

    // Динамические характеристики (пары name/value)
    function addFeaturePair() {
        const list = document.getElementById('featuresList');
        const item = document.createElement('div');
        item.className = 'feature-item';
        item.style.cssText = 'display:flex; gap:8px; align-items:center; margin-bottom:8px;';
        item.innerHTML = '<input type="text" name="features_name[]" class="feature-input" placeholder="Название (напр. Длительность)" style="flex:1;">' +
            '<input type="text" name="features_value[]" class="feature-input" placeholder="Значение (напр. 45 минут)" style="flex:1;">' +
            ' <button type="button" class="btn btn-secondary btn-sm" onclick="removeFeature(this)" title="Удалить"><i class="fas fa-trash"></i></button>';
        list.appendChild(item);
    }
    function removeFeature(btn) { btn.closest('.feature-item')?.remove(); }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>