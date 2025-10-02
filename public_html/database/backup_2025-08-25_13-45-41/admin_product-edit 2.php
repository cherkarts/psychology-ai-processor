<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

requirePermission('products');
$pageTitle = 'Редактор товаров';

$productId = $_GET['id'] ?? '';
$isEdit = !empty($productId);
$product = null;

if ($isEdit) {
    $product = getProductById($productId);
    if (!$product) {
        $_SESSION['error_message'] = 'Товар не найден';
        header('Location: products.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Form submission started");
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Неверный токен безопасности';
        error_log("CSRF token verification failed");
    } else {
        error_log("CSRF token verified, calling saveProduct");
        $result = saveProduct($_POST, $_FILES);
        error_log("saveProduct returned: " . print_r($result, true));
        
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
            if (!$isEdit && isset($result['slug'])) {
                header('Location: product-edit.php?id=' . urlencode($result['slug']));
                exit();
            }
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
    
    // Always redirect to avoid resubmission
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

$categories = getProductCategories();

require_once __DIR__ . '/includes/header.php';
?>

<div class="product-editor-container">
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
                <a href="../product.php?slug=<?php echo urlencode($product['slug']); ?>" class="btn btn-info" target="_blank">
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
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="<?php echo sanitizeOutput($product['title'] ?? ''); ?>" 
                            required
                            placeholder="Введите название товара"
                        >
                    </div>

                    <div class="form-group">
                        <label for="slug">URL-адрес (slug)</label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            value="<?php echo sanitizeOutput($product['slug'] ?? ''); ?>"
                            placeholder="Автоматически генерируется из названия"
                        >
                        <small>Оставьте пустым для автоматической генерации</small>
                    </div>

                    <div class="form-group">
                        <label for="short_description">Краткое описание</label>
                        <textarea 
                            id="short_description" 
                            name="short_description" 
                            rows="3"
                            placeholder="Краткое описание товара для списка товаров"
                        ><?php echo sanitizeOutput($product['short_description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="description">Полное описание *</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="20" 
                            required
                            placeholder="Введите полное описание товара в формате Markdown или HTML"
                        ><?php echo sanitizeOutput($product['description'] ?? ''); ?></textarea>
                        <small>Поддерживается форматирование Markdown</small>
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <div class="form-section">
                    <h3>Настройки товара</h3>
                    
                    <div class="form-group">
                        <label for="type">Тип товара</label>
                        <select id="type" name="type" onchange="toggleTypeFields()">
                            <option value="digital" <?php echo ($product['type'] ?? 'digital') === 'digital' ? 'selected' : ''; ?>>
                                Цифровой товар
                            </option>
                            <option value="physical" <?php echo ($product['type'] ?? '') === 'physical' ? 'selected' : ''; ?>>
                                Физический товар
                            </option>
                            <option value="service" <?php echo ($product['type'] ?? '') === 'service' ? 'selected' : ''; ?>>
                                Услуга
                            </option>
                            <option value="free" <?php echo ($product['type'] ?? '') === 'free' ? 'selected' : ''; ?>>
                                Бесплатный
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo ($product['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>
                                Черновик
                            </option>
                            <option value="published" <?php echo ($product['status'] ?? '') === 'published' ? 'selected' : ''; ?>>
                                Опубликовано
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category">Категория</label>
                        <select id="category" name="category">
                            <option value="">Без категории</option>
                            <?php foreach ($categories as $cat): ?>
                                <option 
                                    value="<?php echo sanitizeOutput($cat['slug']); ?>"
                                    <?php echo ($product['category'] ?? '') === $cat['slug'] ? 'selected' : ''; ?>
                                >
                                    <?php echo sanitizeOutput($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group price-group">
                        <label for="price">Цена (₽)</label>
                        <input 
                            type="number" 
                            id="price" 
                            name="price" 
                            value="<?php echo sanitizeOutput($product['price'] ?? 0); ?>"
                            min="0"
                            step="0.01"
                        >
                    </div>

                    <div class="form-group physical-only">
                        <label>
                            <input 
                                type="checkbox" 
                                name="in_stock" 
                                value="1"
                                <?php echo ($product['in_stock'] ?? true) ? 'checked' : ''; ?>
                            >
                            В наличии
                        </label>
                    </div>

                    <div class="form-group physical-only">
                        <label for="quantity">Количество</label>
                        <input 
                            type="number" 
                            id="quantity" 
                            name="quantity" 
                            value="<?php echo sanitizeOutput($product['quantity'] ?? 0); ?>"
                            min="0"
                        >
                    </div>
                </div>

                <div class="form-section">
                    <h3>Изображения товара</h3>
                    
                    <div class="form-group">
                        <label for="image">Главное изображение</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        
                        <?php if (!empty($product['image'])): ?>
                            <div class="current-image">
                                <p>Текущее изображение:</p>
                                <img src="<?php echo sanitizeOutput($product['image']); ?>" alt="Current image" class="preview-image">
                                <label>
                                    <input type="checkbox" name="remove_image" value="1">
                                    Удалить текущее изображение
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <p>Предварительный просмотр:</p>
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                </div>

                <div class="form-section digital-only">
                    <h3>Цифровой контент</h3>
                    
                    <div class="form-group">
                        <label for="download_file">Файл для скачивания</label>
                        <input type="file" id="download_file" name="download_file">
                        
                        <?php if (!empty($product['download_file'])): ?>
                            <div class="current-file">
                                <p>Текущий файл: <?php echo basename($product['download_file']); ?></p>
                                <label>
                                    <input type="checkbox" name="remove_file" value="1">
                                    Удалить текущий файл
                                </label>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit_action" value="save" class="btn btn-primary">
                <i class="fas fa-save"></i> Сохранить
            </button>
            
            <?php if (!$isEdit || ($product['status'] ?? '') === 'draft'): ?>
                <button type="submit" name="submit_action" value="publish" class="btn btn-success">
                    <i class="fas fa-check"></i> Сохранить и опубликовать
                </button>
            <?php endif; ?>
            
            <a href="products.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Отмена
            </a>
        </div>
    </form>
</div>

<script>
// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    const title = this.value;
    const slugField = document.getElementById('slug');
    
    if (!slugField.dataset.userModified) {
        const slug = title
            .toLowerCase()
            .replace(/[а-я]/g, function(char) {
                const translit = {
                    'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
                    'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
                    'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
                    'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
                    'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
                };
                return translit[char] || char;
            })
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        
        slugField.value = slug;
    }
});

// Mark slug as user-modified if user types in it
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.userModified = 'true';
});

// Image preview
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Toggle fields based on product type
function toggleTypeFields() {
    const type = document.getElementById('type').value;
    const physicalFields = document.querySelectorAll('.physical-only');
    const digitalFields = document.querySelectorAll('.digital-only');
    const priceGroup = document.querySelector('.price-group');
    
    // Show/hide physical product fields
    physicalFields.forEach(field => {
        field.style.display = type === 'physical' ? 'block' : 'none';
    });
    
    // Show/hide digital product fields
    digitalFields.forEach(field => {
        field.style.display = type === 'digital' ? 'block' : 'none';
    });
    
    // Handle price field for free products
    if (type === 'free') {
        priceGroup.style.display = 'none';
        document.getElementById('price').value = 0;
    } else {
        priceGroup.style.display = 'block';
    }
}

// Initialize field visibility
document.addEventListener('DOMContentLoaded', function() {
    toggleTypeFields();
});
</script>

<style>
.product-editor-container {
    max-width: 1400px;
    margin: 0 auto;
}

.form-layout {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    margin-bottom: 2rem;
}

.form-section {
    background: white;
    padding: 1.5rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.5rem;
}

.form-section h3 {
    margin: 0 0 1rem 0;
    color: var(--gray-800);
    font-size: 1.1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--gray-700);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius-md);
    font-size: var(--font-size-sm);
}

.form-group textarea {
    resize: vertical;
}

.form-group small {
    display: block;
    margin-top: 0.25rem;
    color: var(--gray-600);
    font-size: 0.8rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

.current-image,
.current-file {
    margin-top: 0.5rem;
    padding: 0.75rem;
    background: var(--gray-50);
    border-radius: var(--border-radius-md);
}

.preview-image {
    max-width: 200px;
    height: auto;
    border-radius: var(--border-radius-md);
    margin: 0.5rem 0;
}

.image-preview {
    margin-top: 0.5rem;
}

.image-preview img {
    max-width: 100%;
    height: auto;
    border-radius: var(--border-radius-md);
}

.form-actions {
    background: white;
    padding: 1.5rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .form-layout {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<?php
require_once __DIR__ . '/includes/footer.php';

// Helper functions
function getProductById($productId) {
    $db = getAdminDB();
    
    if ($db) {
        try {
            // First try to find by ID or slug
            $stmt = $db->prepare("SELECT p.*, pc.slug as category_slug FROM products p LEFT JOIN product_categories pc ON p.category_id = pc.id WHERE p.id = ? OR p.slug = ?");
            $stmt->execute([$productId, $productId]);
            $product = $stmt->fetch();
            
            // If we found a product, map status field for form compatibility
            if ($product) {
                // Ensure status field exists, default to 'draft' if not set
                if (!isset($product['status'])) {
                    $product['status'] = 'draft';
                }
                return $product;
            }
        } catch (PDOException $e) {
            error_log("Get product by ID error: " . $e->getMessage());
        }
    }
    
    // Fallback to JSON file if database fails
    $productsFile = __DIR__ . '/../data/products.json';
    if (file_exists($productsFile)) {
        $productsData = json_decode(file_get_contents($productsFile), true);
        $products = $productsData['products'] ?? [];
        
        foreach ($products as $product) {
            if (($product['id'] ?? $product['slug']) == $productId) {
                // Ensure status field exists for form compatibility
                if (!isset($product['status'])) {
                    $product['status'] = 'draft';
                }
                return $product;
            }
        }
    }
    
    return null;
}

function saveProduct($data, $files) {
    // Validation
    $requiredFields = ['title', 'description', 'type'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            return ['success' => false, 'message' => "Поле '$field' обязательно для заполнения"];
        }
    }
    
    $productData = [
        'title' => sanitizeInput($data['title']),
        'slug' => generateProductSlug($data['slug'] ?: $data['title']),
        'short_description' => sanitizeInput($data['short_description'] ?? ''),
        'description' => sanitizeInput($data['description']),
        'type' => sanitizeInput($data['type']),
        'status' => sanitizeInput($data['status'] ?? 'draft'),  // Use the status field directly
        'price' => floatval($data['price'] ?? 0),
        'in_stock' => isset($data['in_stock']) ? (bool)$data['in_stock'] : true,
        'quantity' => intval($data['quantity'] ?? 0)
    ];
    
    // Handle category - convert slug to ID
    $categorySlug = sanitizeInput($data['category'] ?? '');
    if (!empty($categorySlug)) {
        $db = getAdminDB();
        if ($db) {
            try {
                $categoryStmt = $db->prepare("SELECT id FROM product_categories WHERE slug = ?");
                $categoryStmt->execute([$categorySlug]);
                $category = $categoryStmt->fetch();
                if ($category) {
                    $productData['category_id'] = $category['id'];
                }
            } catch (PDOException $e) {
                error_log("Category lookup error: " . $e->getMessage());
            }
        }
    }
    
    // Set price to 0 for free products
    if ($productData['type'] === 'free') {
        $productData['price'] = 0;
    }
    
    // Handle file uploads
    $imagePath = handleImageUpload($files['image'] ?? null, $data);
    if ($imagePath) {
        $productData['image'] = $imagePath;
    }
    
    $filePath = handleFileUpload($files['download_file'] ?? null, $data);
    if ($filePath) {
        $productData['download_file'] = $filePath;
    }
    
    $productId = $data['product_id'] ?? '';
    $action = $data['action'] ?? 'create';
    
    $db = getAdminDB();
    
    if (!$db) {
        return ['success' => false, 'message' => 'Ошибка подключения к базе данных'];
    }
    
    try {
        if ($action === 'create') {
            $productData['created_at'] = date('Y-m-d H:i:s');
            $productData['updated_at'] = date('Y-m-d H:i:s');
            
            $stmt = $db->prepare("
                INSERT INTO products (title, slug, short_description, description, type, status, category_id, price, in_stock, quantity, image, download_file, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $productData['title'],
                $productData['slug'],
                $productData['short_description'],
                $productData['description'],
                $productData['type'],
                $productData['status'],
                $productData['category_id'] ?? null,
                $productData['price'],
                $productData['in_stock'],
                $productData['quantity'],
                $productData['image'] ?? null,
                $productData['download_file'] ?? null,
                $productData['created_at'],
                $productData['updated_at']
            ]);
            
            if ($result) {
                logAdminActivity('create', "Created new product: {$productData['title']}");
                return ['success' => true, 'message' => 'Товар успешно создан', 'slug' => $productData['slug']];
            } else {
                return ['success' => false, 'message' => 'Ошибка создания товара'];
            }
        } else if ($action === 'update' && $productId) {
            $productData['updated_at'] = date('Y-m-d H:i:s');
            
            $sql = "UPDATE products SET title = ?, slug = ?, short_description = ?, description = ?, type = ?, status = ?, category_id = ?, price = ?, in_stock = ?, quantity = ?, updated_at = ?";
            $params = [
                $productData['title'],
                $productData['slug'],
                $productData['short_description'],
                $productData['description'],
                $productData['type'],
                $productData['status'],
                $productData['category_id'] ?? null,
                $productData['price'],
                $productData['in_stock'],
                $productData['quantity'],
                $productData['updated_at']
            ];
            
            // Handle image removal
            if (isset($data['remove_image']) && $data['remove_image'] == '1') {
                // Get current image path to delete the file
                $currentStmt = $db->prepare("SELECT image FROM products WHERE id = ? OR slug = ?");
                $currentStmt->execute([$productId, $productId]);
                $currentProduct = $currentStmt->fetch();
                if ($currentProduct && !empty($currentProduct['image'])) {
                    $imagePath = __DIR__ . '/..' . $currentProduct['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            } else if (isset($productData['image'])) {
                $sql .= ", image = ?";
                $params[] = $productData['image'];
            }
            
            // Handle file removal
            if (isset($data['remove_file']) && $data['remove_file'] == '1') {
                // Get current file path to delete the file
                $currentStmt = $db->prepare("SELECT download_file FROM products WHERE id = ? OR slug = ?");
                $currentStmt->execute([$productId, $productId]);
                $currentProduct = $currentStmt->fetch();
                if ($currentProduct && !empty($currentProduct['download_file'])) {
                    $filePath = __DIR__ . '/..' . $currentProduct['download_file'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            } else if (isset($productData['download_file'])) {
                $sql .= ", download_file = ?";
                $params[] = $productData['download_file'];
            }
            
            $sql .= " WHERE id = ? OR slug = ?";
            $params[] = $productId;
            $params[] = $productId;
            
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                logAdminActivity('update', "Updated product: {$productData['title']}");
                return ['success' => true, 'message' => 'Товар успешно обновлен'];
            } else {
                return ['success' => false, 'message' => 'Ошибка обновления товара'];
            }
        }
    } catch (PDOException $e) {
        error_log("Save product error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()];
    } catch (Exception $e) {
        error_log("Save product error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Ошибка сохранения товара: ' . $e->getMessage()];
    }
    
    return ['success' => false, 'message' => 'Неизвестная ошибка сохранения товара'];
}

function generateProductSlug($title) {
    $slug = mb_strtolower($title, 'UTF-8');
    
    // Create a transliteration array
    $translit = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];
    
    // Transliterate Cyrillic characters
    $slug = strtr($slug, $translit);
    
    // Remove unwanted characters
    $slug = preg_replace('/[^a-z0-9\s-]/u', '', $slug);
    
    // Replace spaces with hyphens
    $slug = preg_replace('/\s+/', '-', $slug);
    
    // Remove multiple hyphens
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Trim hyphens from beginning and end
    return trim($slug, '-');
}

function handleImageUpload($file, $data) {
    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        return null;
    }
    
    $uploadDir = __DIR__ . '/../uploads/products';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: " . $uploadDir);
            return null;
        }
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'product_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return '/uploads/products/' . $filename;
    }
    
    error_log("Failed to move uploaded file to: " . $uploadPath);
    return null;
}

function handleFileUpload($file, $data) {
    if (!$file || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $uploadDir = __DIR__ . '/../uploads/products/files';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: " . $uploadDir);
            return null;
        }
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'file_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return '/uploads/products/files/' . $filename;
    }
    
    error_log("Failed to move uploaded file to: " . $uploadPath);
    return null;
}

// Get product categories function
function getProductCategories() {
    $db = getAdminDB();
    
    if (!$db) {
        return [];
    }
    
    try {
        $stmt = $db->query("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY sort_order, name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getProductCategories: " . $e->getMessage());
        return [];
    }
}
?>