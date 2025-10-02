<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

requirePermission('articles');
$pageTitle = 'Редактор статей';

$articleId = $_GET['id'] ?? '';
$isEdit = !empty($articleId);
$article = null;

if ($isEdit) {
    // Clean and validate the article ID
    $articleId = trim($articleId);
    
    if (empty($articleId) || is_numeric($articleId)) {
        // Handle invalid or numeric IDs
        error_log("Invalid article ID provided: " . $articleId);
        $_SESSION['error_message'] = "Некорректный ID статьи. Используйте слаг статьи, а не числовой ID.";
        header('Location: articles.php');
        exit();
    }
    
    $article = getArticleById($articleId);
    if (!$article) {
        error_log("Article not found: " . $articleId);
        
        $_SESSION['error_message'] = "Статья не найдена: {$articleId}";
        header('Location: articles.php');
        exit();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Неверный токен безопасности';
    } else {
        $result = saveArticle($_POST, $_FILES);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
            if (!$isEdit && isset($result['slug'])) {
                header('Location: article-edit.php?id=' . urlencode($result['slug']));
                exit();
            }
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

$categories = getArticleCategories();

require_once __DIR__ . '/includes/header.php';
?>

<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>

<div class="article-editor-container">
    
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo sanitizeOutput($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo sanitizeOutput($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="page-header">
        <div class="header-content">
            <h1>
                <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus'; ?>"></i> 
                <?php echo $isEdit ? 'Редактировать статью' : 'Новая статья'; ?>
            </h1>
            <p><?php echo $isEdit ? 'Редактирование существующей статьи' : 'Создание новой статьи'; ?></p>
        </div>
        <div class="header-actions">
            <a href="articles.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
            <?php if ($isEdit): ?>
                <a href="../article.php?slug=<?php echo urlencode($article['slug']); ?>" class="btn btn-info" target="_blank">
                    <i class="fas fa-eye"></i> Предварительный просмотр
                </a>
            <?php endif; ?>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="article-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="article_id" value="<?php echo sanitizeOutput($articleId); ?>">
        <input type="hidden" name="action" value="<?php echo $isEdit ? 'update' : 'create'; ?>">

        <div class="form-layout">
            <div class="main-content">
                <div class="form-section">
                    <h3>Основная информация</h3>
                    
                    <div class="form-group">
                        <label for="title">Заголовок статьи *</label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="<?php echo sanitizeOutput($article['title'] ?? ''); ?>" 
                            required
                            placeholder="Введите заголовок статьи"
                        >
                    </div>

                    <div class="form-group">
                        <label for="slug">URL-адрес (slug)</label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            value="<?php echo sanitizeOutput($article['slug'] ?? ''); ?>"
                            placeholder="Автоматически генерируется из заголовка"
                        >
                        <small>Оставьте пустым для автоматической генерации</small>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Краткое описание</label>
                        <textarea 
                            id="excerpt" 
                            name="excerpt" 
                            rows="3"
                            placeholder="Краткое описание статьи для превью"
                        ><?php echo sanitizeOutput($article['excerpt'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Содержание статьи *</label>
                        <textarea 
                            id="content" 
                            name="content" 
                            rows="20" 
                            required
                            placeholder="Введите содержание статьи"
                        ><?php echo sanitizeOutput($article['content'] ?? ''); ?></textarea>
                        <small>Используйте визуальный редактор для форматирования текста</small>
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <div class="form-section">
                    <h3>Параметры публикации</h3>
                    
                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo ($article['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>
                                Черновик
                            </option>
                            <option value="published" <?php echo ($article['status'] ?? '') === 'published' ? 'selected' : ''; ?>>
                                Опубликовано
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category">Категория</label>
                        <div class="category-input-group">
                            <select id="category" name="category">
                                <option value="">Без категории</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option 
                                        value="<?php echo sanitizeOutput($cat['slug']); ?>"
                                        <?php echo ($article['category'] ?? '') === $cat['slug'] ? 'selected' : ''; ?>
                                    >
                                        <?php echo sanitizeOutput($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openCategoryModal()">
                                <i class="fas fa-plus"></i> Новая
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="author">Автор</label>
                        <input 
                            type="text" 
                            id="author" 
                            name="author" 
                            value="<?php echo sanitizeOutput($article['author'] ?? 'Denis Cherkas'); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="date">Дата публикации</label>
                        <input 
                            type="datetime-local" 
                            id="date" 
                            name="date" 
                            value="<?php echo isset($article['date']) ? date('Y-m-d\TH:i', strtotime($article['date'])) : date('Y-m-d\TH:i'); ?>"
                        >
                    </div>
                </div>

                <div class="form-section">
                    <h3>Изображение статьи</h3>
                    
                    <div class="form-group">
                        <label for="featured_image">Главное изображение</label>
                        <input type="file" id="featured_image" name="featured_image" accept="image/*">
                        
                        <?php if (!empty($article['featured_image'])): ?>
                            <div class="current-image">
                                <p>Текущее изображение:</p>
                                <?php 
                                    $imagePath = $article['featured_image'];
                                    // Ensure the path starts with / for proper web access
                                    if (!str_starts_with($imagePath, '/')) {
                                        $imagePath = '/' . $imagePath;
                                    }
                                    echo "<!-- Debug: Image path = $imagePath -->";
                                ?>
                                <img src="<?php echo sanitizeOutput($imagePath); ?>" 
                                     alt="Current image" 
                                     class="preview-image"
                                     onerror="console.error('Image failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='block';"
                                     onload="console.log('Image loaded successfully:', this.src); this.nextElementSibling.style.display='none';">
                                <div style="display:none; color: red; font-size: 12px; margin-bottom: 10px; background: #ffe6e6; padding: 8px; border-radius: 4px;">
                                    Ошибка загрузки изображения: <?php echo sanitizeOutput($imagePath); ?><br>
                                    <small>Проверьте, что файл существует и доступен</small>
                                </div>
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

                <div class="form-section">
                    <h3>SEO настройки</h3>
                    
                    <div class="form-group">
                        <label for="meta_title">Meta Title</label>
                        <input 
                            type="text" 
                            id="meta_title" 
                            name="meta_title" 
                            value="<?php echo sanitizeOutput($article['meta_title'] ?? ''); ?>"
                            maxlength="60"
                        >
                        <small>Рекомендуется до 60 символов</small>
                    </div>

                    <div class="form-group">
                        <label for="meta_description">Meta Description</label>
                        <textarea 
                            id="meta_description" 
                            name="meta_description" 
                            rows="3"
                            maxlength="160"
                        ><?php echo sanitizeOutput($article['meta_description'] ?? ''); ?></textarea>
                        <small>Рекомендуется до 160 символов</small>
                    </div>

                    <div class="form-group">
                        <label for="tags">Теги</label>
                        <input 
                            type="text" 
                            id="tags" 
                            name="tags" 
                            value="<?php echo sanitizeOutput($article['tags'] ?? ''); ?>"
                            placeholder="тег1, тег2, тег3"
                        >
                        <small>Разделяйте теги запятыми</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="submit_action" value="save" class="btn btn-primary">
                <i class="fas fa-save"></i> Сохранить
            </button>
            
            <?php if (!$isEdit || ($article['status'] ?? '') === 'draft'): ?>
                <button type="submit" name="submit_action" value="publish" class="btn btn-success">
                    <i class="fas fa-check"></i> Сохранить и опубликовать
                </button>
            <?php endif; ?>
            
            <a href="articles.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Отмена
            </a>
        </div>
    </form>
</div>

<!-- Category Management Modal -->
<div class="modal-overlay" id="categoryModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Управление категориями</h3>
            <button class="modal-close" onclick="closeCategoryModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="category-management-tabs">
                <button type="button" class="tab-btn active" onclick="switchCategoryTab('create')">Создать</button>
                <button type="button" class="tab-btn" onclick="switchCategoryTab('manage')">Управление</button>
            </div>
            
            <div id="createCategoryTab" class="tab-content active">
                <form id="categoryForm">
                    <div class="form-group">
                        <label for="categoryName">Название категории *</label>
                        <input type="text" id="categoryName" name="name" required placeholder="Например: Психология">
                    </div>
                    <div class="form-group">
                        <label for="categorySlug">URL-адрес (slug)</label>
                        <input type="text" id="categorySlug" name="slug" placeholder="Автоматически генерируется">
                        <small>Оставьте пустым для автоматической генерации</small>
                    </div>
                    <div class="form-group">
                        <label for="categoryDescription">Описание</label>
                        <textarea id="categoryDescription" name="description" rows="3" placeholder="Краткое описание категории"></textarea>
                    </div>
                </form>
            </div>
            
            <div id="manageCategoryTab" class="tab-content">
                <div class="form-group">
                    <label for="existingCategories">Существующие категории</label>
                    <select id="existingCategories" class="form-control" size="5">
                        <!-- Categories will be populated by JavaScript -->
                    </select>
                    <div class="category-actions">
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteCategory()" disabled id="deleteCategoryBtn">
                            <i class="fas fa-trash"></i> Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">Отмена</button>
            <button type="button" class="btn btn-primary" id="saveCategoryBtn" onclick="saveCategory()">Сохранить</button>
        </div>
    </div>
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
document.getElementById('featured_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    console.log('Image file selected:', file ? file.name : 'none');
    
    if (file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        const fileType = file.type.toLowerCase();
        
        if (!allowedTypes.includes(fileType)) {
            showToast('Неподдерживаемый тип файла. Разрешены: JPG, PNG, GIF, WEBP', 'error');
            e.target.value = '';
            preview.style.display = 'none';
            return;
        }
        
        // Validate file size (5MB)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            showToast('Файл слишком большой. Максимальный размер: 5MB', 'error');
            e.target.value = '';
            preview.style.display = 'none';
            return;
        }
        
        console.log('File validation passed, creating preview');
        
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            console.log('Image preview created successfully');
            showToast('Предпросмотр изображения создан', 'success');
        };
        reader.onerror = function() {
            console.error('Failed to read file');
            showToast('Ошибка чтения файла', 'error');
            preview.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        console.log('No file selected, hiding preview');
    }
});

// Character counters
function updateCounter(input, maxLength) {
    const counter = input.nextElementSibling;
    if (counter && counter.tagName === 'SMALL') {
        const remaining = maxLength - input.value.length;
        counter.textContent = `${input.value.length}/${maxLength} символов`;
        if (remaining < 10) {
            counter.style.color = '#f44336';
        } else {
            counter.style.color = '#666';
        }
    }
}

document.getElementById('meta_title').addEventListener('input', function() {
    updateCounter(this, 60);
});

document.getElementById('meta_description').addEventListener('input', function() {
    updateCounter(this, 160);
});

// Category Management Functions
function openCategoryModal() {
    document.getElementById('categoryModal').style.display = 'flex';
    document.getElementById('categoryForm').reset();
    loadExistingCategories(); // Load existing categories for management
}

function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

// Switch between category tabs
function switchCategoryTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    // Show selected tab content
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.getElementById(tabName + 'CategoryTab').classList.add('active');
    
    // Update save button text based on active tab
    const saveBtn = document.getElementById('saveCategoryBtn');
    if (tabName === 'create') {
        saveBtn.textContent = 'Сохранить';
        saveBtn.onclick = saveCategory;
    } else {
        saveBtn.textContent = 'Обновить список';
        saveBtn.onclick = loadExistingCategories;
    }
}

// Load existing categories for management
function loadExistingCategories() {
    fetch('api/get-categories.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('existingCategories');
            select.innerHTML = '';
            
            data.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.id;
                option.textContent = category.name;
                option.dataset.slug = category.slug;
                option.dataset.description = category.description || '';
                select.appendChild(option);
            });
            
            // Enable/disable delete button based on selection
            select.addEventListener('change', function() {
                document.getElementById('deleteCategoryBtn').disabled = !this.value;
            });
        }
    })
    .catch(error => {
        console.error('Error loading categories:', error);
        showToast('Ошибка загрузки категорий', 'error');
    });
}

// Auto-generate category slug
document.getElementById('categoryName').addEventListener('input', function() {
    const name = this.value;
    const slugField = document.getElementById('categorySlug');
    
    if (!slugField.dataset.userModified) {
        const slug = name
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

// Mark category slug as user-modified
document.getElementById('categorySlug').addEventListener('input', function() {
    this.dataset.userModified = 'true';
});

function saveCategory() {
    const form = document.getElementById('categoryForm');
    const formData = new FormData(form);
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
    
    // Disable submit button
    const submitBtn = document.getElementById('saveCategoryBtn');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
    
    fetch('api/save-category.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload categories from server to get the most up-to-date list
            loadCategories(data.category.slug);
            loadExistingCategories(); // Also update the management list
            showToast(data.message || 'Категория успешно создана', 'success');
        } else {
            showToast(data.message || 'Ошибка создания категории', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Ошибка сервера при создании категории', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

// Delete category function
function deleteCategory() {
    const select = document.getElementById('existingCategories');
    const selectedOption = select.options[select.selectedIndex];
    
    if (!selectedOption || !selectedOption.value) {
        showToast('Выберите категорию для удаления', 'error');
        return;
    }
    
    const categoryId = selectedOption.value;
    const categoryName = selectedOption.textContent;
    
    // Confirm deletion
    if (!confirm(`Вы уверены, что хотите удалить категорию "${categoryName}"?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('category_id', categoryId);
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
    
    // Disable delete button during request
    const deleteBtn = document.getElementById('deleteCategoryBtn');
    const originalText = deleteBtn.innerHTML;
    deleteBtn.disabled = true;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Удаление...';
    
    fetch('api/delete-category.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload categories from server
            loadCategories();
            loadExistingCategories(); // Also update the management list
            showToast(data.message || 'Категория успешно удалена', 'success');
        } else {
            showToast(data.message || 'Ошибка удаления категории', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Ошибка сервера при удалении категории', 'error');
    })
    .finally(() => {
        deleteBtn.disabled = false;
        deleteBtn.innerHTML = originalText;
    });
}

// Function to reload categories from server
function loadCategories(selectSlug = null) {
    fetch('api/get-categories.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const select = document.getElementById('category');
            const currentValue = select.value;
            
            // Clear existing options except "Без категории"
            select.innerHTML = '<option value="">Без категории</option>';
            
            // Add all categories
            data.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.slug;
                option.textContent = category.name;
                if (selectSlug === category.slug || currentValue === category.slug) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }
    })
    .catch(error => {
        console.error('Error loading categories:', error);
    });
}

function showToast(message, type) {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 4px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Initialize CKEditor 5
ClassicEditor
    .create(document.querySelector('#content'), {
        language: 'ru',
        toolbar: {
            items: [
                'undo', 'redo',
                '|', 'heading',
                '|', 'fontfamily', 'fontsize', 'fontColor', 'fontBackgroundColor',
                '|', 'bold', 'italic', 'underline', 'strikethrough',
                '|', 'link', 'uploadImage', 'insertTable', 'blockQuote', 'codeBlock',
                '|', 'alignment',
                '|', 'bulletedList', 'numberedList', 'outdent', 'indent',
                '|', 'horizontalLine', 'pageBreak',
                '|', 'sourceEditing'
            ],
            shouldNotGroupWhenFull: true
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Параграф', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Заголовок 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Заголовок 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Заголовок 3', class: 'ck-heading_heading3' },
                { model: 'heading4', view: 'h4', title: 'Заголовок 4', class: 'ck-heading_heading4' }
            ]
        },
        fontFamily: {
            options: [
                'default',
                'Arial, Helvetica, sans-serif',
                'Georgia, serif',
                'Times New Roman, serif',
                'Verdana, Geneva, sans-serif'
            ]
        },
        fontSize: {
            options: [
                9, 11, 13, 'default', 17, 19, 21, 27, 35
            ]
        },
        image: {
            toolbar: [
                'imageTextAlternative', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side',
                'linkImage'
            ],
            resizeUnit: 'px',
            resizeOptions: [
                {
                    name: 'resizeImage:original',
                    value: null,
                    label: 'Оригинальный размер'
                },
                {
                    name: 'resizeImage:50',
                    value: '50',
                    label: '50%'
                },
                {
                    name: 'resizeImage:75',
                    value: '75',
                    label: '75%'
                }
            ]
        },
        table: {
            contentToolbar: [
                'tableColumn', 'tableRow', 'mergeTableCells',
                'tableProperties', 'tableCellProperties'
            ]
        },
        link: {
            decorators: {
                openInNewTab: {
                    mode: 'manual',
                    label: 'Открыть в новой вкладке',
                    attributes: {
                        target: '_blank',
                        rel: 'noopener noreferrer'
                    }
                }
            }
        },
        simpleUpload: {
            uploadUrl: 'api/upload-image.php',
            withCredentials: true,
            headers: {
                'X-CSRF-TOKEN': '<?php echo generateCSRFToken(); ?>'
            }
        },
        mediaEmbed: {
            previewsInData: true
        }
    })
    .then(editor => {
        window.editor = editor;
        
        // Enhanced upload error handling
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
            return {
                upload: () => {
                    return loader.file.then(file => {
                        console.log('Uploading image:', file.name, file.size + ' bytes');
                        
                        const formData = new FormData();
                        formData.append('upload', file);
                        formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
                        
                        return fetch('api/upload-image.php', {
                            method: 'POST',
                            body: formData,
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': '<?php echo generateCSRFToken(); ?>'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(result => {
                            if (result.error) {
                                console.error('Upload error:', result.error.message);
                                showToast('Ошибка загрузки: ' + result.error.message, 'error');
                                throw new Error(result.error.message);
                            }
                            if (result.url) {
                                console.log('Image uploaded successfully:', result.url);
                                showToast('Изображение успешно загружено', 'success');
                                return { default: result.url };
                            }
                            throw new Error('Неверный ответ сервера');
                        })
                        .catch(error => {
                            console.error('Upload failed:', error);
                            showToast('Ошибка загрузки изображения: ' + error.message, 'error');
                            throw error;
                        });
                    });
                },
                abort: () => {
                    console.log('Upload aborted');
                }
            };
        };
        
        // Auto-save functionality with error handling
        let autoSaveTimer;
        editor.model.document.on('change:data', () => {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                try {
                    const content = editor.getData();
                    document.querySelector('#content').value = content;
                    console.log('Контент автоматически сохранен, длина:', content.length);
                } catch (error) {
                    console.error('Ошибка автосохранения:', error);
                }
            }, 1000);
        });
        
        // Set initial height
        editor.editing.view.change(writer => {
            writer.setStyle('min-height', '400px', editor.editing.view.document.getRoot());
        });
        
        console.log('CKEditor 5 инициализирован успешно');
    })
    .catch(error => {
        console.error('Ошибка инициализации редактора:', error);
        showToast('Ошибка загрузки редактора', 'error');
    });

// Form submission handling - direct submission without any confirmation
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up form handlers');
    
    // PREVENT AUTOMATIC MODAL FROM SHOWING
    const confirmModal = document.getElementById('confirmModal');
    if (confirmModal) {
        confirmModal.style.display = 'none';
        console.log('Hiding any automatically shown confirmation modal');
    }
    
    // Override showConfirmModal to prevent automatic calls
    const originalShowConfirmModal = window.showConfirmModal;
    window.showConfirmModal = function(title, message, callback) {
        console.log('showConfirmModal called with:', title, message);
        console.trace('Call stack:');
        
        // Only show modal for explicit user actions, not on page load
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            if (originalShowConfirmModal) {
                originalShowConfirmModal(title, message, callback);
            }
        } else {
            console.log('Preventing modal show during page load');
        }
    };
    
    const form = document.querySelector('.article-form');
    if (form) {
        console.log('Article form found, adding submit listener');
        
        // Override any potential global form handlers
        form.addEventListener('submit', function(e) {
            console.log('Form submission started - no confirmation needed');
            
            // Prevent any other form handlers from interfering
            e.stopImmediatePropagation();
            
            // Update editor content before submission
            if (window.editor) {
                const editorData = window.editor.getData();
                document.querySelector('#content').value = editorData;
                console.log('Editor content updated, length:', editorData.length);
            } else {
                console.warn('CKEditor instance not found');
            }
            
            // Show loading state
            const submitButtons = this.querySelectorAll('button[type="submit"]');
            console.log('Found submit buttons:', submitButtons.length);
            
            submitButtons.forEach((btn, index) => {
                console.log('Disabling button', index);
                btn.disabled = true;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
                
                // Store original text to restore on error
                btn.dataset.originalText = originalText;
            });
            
            console.log('Form will submit normally without confirmation');
            // Let form submit normally - no preventDefault needed
        }, true); // Use capture phase to override other handlers
    } else {
        console.error('Form not found: .article-form');
    }
    
    // Debug: List all forms on the page
    const allForms = document.querySelectorAll('form');
    console.log('All forms found on page:', allForms.length);
    allForms.forEach((form, index) => {
        console.log(`Form ${index}:`, form.className, form.id);
    });
    
    // Override any potential global form confirmation handlers
    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('article-form')) {
            console.log('Overriding any global form handlers for article form');
            e.stopImmediatePropagation();
            // Let it submit normally
        }
    }, true);
});
</script>

<style>
/* Alert messages */
.alert {
    padding: 12px 16px;
    margin: 20px 0;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
    border: 1px solid transparent;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-error {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.alert i {
    font-size: 16px;
}

.article-editor-container {
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

/* Category Management Styles */
.category-management-tabs {
    display: flex;
    margin-bottom: 1rem;
    border-bottom: 1px solid var(--gray-300);
}

.tab-btn {
    padding: 0.5rem 1rem;
    background: transparent;
    border: none;
    cursor: pointer;
    font-weight: 500;
    color: var(--gray-600);
    border-bottom: 2px solid transparent;
}

.tab-btn.active {
    color: var(--primary-color);
    border-bottom: 2px solid var(--primary-color);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

#existingCategories {
    height: 150px;
}

.category-actions {
    margin-top: 0.5rem;
    text-align: right;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}

.modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-500);
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gray-200);
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.current-image {
    margin-top: 0.5rem;
    padding: 0.5rem;
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius-md);
    background: var(--gray-50);
}

.preview-image {
    max-width: 200px;
    max-height: 200px;
    border-radius: var(--border-radius-sm);
    margin: 0.5rem 0;
}

.image-preview {
    margin-top: 1rem;
    padding: 1rem;
    border: 1px solid var(--gray-200);
    border-radius: var(--border-radius-md);
    background: var(--gray-50);
}

.image-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: var(--border-radius-sm);
}

.form-actions {
    background: white;
    padding: 1.5rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    display: flex;
    gap: 1rem;
    align-items: center;
}

/* CKEditor 5 Styling */
.ck-editor {
    border: 1px solid var(--gray-300) !important;
    border-radius: var(--border-radius-md) !important;
}

.ck-editor__editable {
    min-height: 400px !important;
    border: none !important;
    border-radius: 0 0 var(--border-radius-md) var(--border-radius-md) !important;
}

.ck-toolbar {
    border: none !important;
    border-bottom: 1px solid var(--gray-300) !important;
    border-radius: var(--border-radius-md) var(--border-radius-md) 0 0 !important;
    background: #f8f9fa !important;
}

.ck-content {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 14px;
    line-height: 1.6;
}

/* Enhanced CKEditor content styling */
.ck-content blockquote {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 1rem 1.5rem;
    margin: 1rem 0;
    font-style: italic;
    border-radius: 0 4px 4px 0;
}

.ck-content blockquote p {
    margin: 0;
    color: #495057;
}

.ck-content h1, .ck-content h2, .ck-content h3, .ck-content h4, .ck-content h5, .ck-content h6 {
    margin: 1.5rem 0 1rem 0;
    font-weight: 600;
    line-height: 1.3;
}

.ck-content h1 { font-size: 2rem; color: #212529; }
.ck-content h2 { font-size: 1.75rem; color: #212529; }
.ck-content h3 { font-size: 1.5rem; color: #495057; }
.ck-content h4 { font-size: 1.25rem; color: #495057; }

.ck-content p {
    margin: 1rem 0;
}

.ck-content ul, .ck-content ol {
    margin: 1rem 0;
    padding-left: 2rem;
}

.ck-content li {
    margin: 0.5rem 0;
}

.ck-content a {
    color: #007bff;
    text-decoration: none;
}

.ck-content a:hover {
    text-decoration: underline;
}

.ck-content img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
}

.ck-content table {
    border-collapse: collapse;
    width: 100%;
    margin: 1rem 0;
}

.ck-content table th,
.ck-content table td {
    border: 1px solid #dee2e6;
    padding: 0.5rem;
    text-align: left;
}

.ck-content table th {
    background-color: #f8f9fa;
    font-weight: 600;
}
</style>

<?php
// Save article function
function saveArticle($postData, $files) {
    try {
        $db = getAdminDB();
        
        if (!$db) {
            return ['success' => false, 'message' => 'Ошибка подключения к базе данных'];
        }
        
        // Process form data
        $articleId = trim($postData['article_id'] ?? '');
        $isEdit = !empty($articleId);
        
        $title = sanitizeInput($postData['title'] ?? '');
        $slug = sanitizeInput($postData['slug'] ?? '');
        $excerpt = sanitizeInput($postData['excerpt'] ?? '');
        $content = $postData['content'] ?? '';
        $author = sanitizeInput($postData['author'] ?? 'Denis Cherkas');
        $categoryId = sanitizeInput($postData['category'] ?? '');
        $status = sanitizeInput($postData['status'] ?? 'draft');
        $metaTitle = sanitizeInput($postData['meta_title'] ?? '');
        $metaDescription = sanitizeInput($postData['meta_description'] ?? '');
        $tags = sanitizeInput($postData['tags'] ?? '');
        
        // Convert category slug to ID
        $categoryId = null;
        if (!empty($categoryId)) {
            $stmt = $db->prepare("SELECT id FROM article_categories WHERE slug = ?");
            $stmt->execute([$categoryId]);
            $category = $stmt->fetch();
            if ($category) {
                $categoryId = $category['id'];
            }
        }
        
        // Process tags - convert comma-separated string to JSON array
        $tagsJson = null;
        if (!empty($tags)) {
            $tagsArray = array_map('trim', explode(',', $tags));
            $tagsJson = json_encode($tagsArray);
        }
        
        // Handle image upload
        $imageResult = handleImageUpload($files, $postData, $isEdit ? $articleId : null);
        if (!$imageResult['success']) {
            return $imageResult;
        }
        $featuredImage = $imageResult['image_path'];
        
        // Generate slug if not provided
        if (empty($slug)) {
            $slug = generateSlug($title);
        }
        
        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        
        if ($isEdit) {
            // For editing, check if slug is being changed to avoid conflicts
            $stmt = $db->prepare("SELECT slug FROM articles WHERE slug = ? AND slug != ?");
            $stmt->execute([$slug, $articleId]);
        } else {
            // For new articles, check for any existing slug
            $stmt = $db->prepare("SELECT slug FROM articles WHERE slug = ?");
            $stmt->execute([$slug]);
        }
        
        while ($stmt->fetch()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
            
            if ($isEdit) {
                $stmt = $db->prepare("SELECT slug FROM articles WHERE slug = ? AND slug != ?");
                $stmt->execute([$slug, $articleId]);
            } else {
                $stmt = $db->prepare("SELECT slug FROM articles WHERE slug = ?");
                $stmt->execute([$slug]);
            }
        }
        
        // Prepare article data
        $articleData = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'author' => $author,
            'category_id' => $categoryId,
            'is_published' => $status === 'published' ? 1 : 0,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'featured_image' => $featuredImage,
            'tags' => $tagsJson
        ];
        
        // Handle published date
        $publishedAt = null;
        if ($articleData['is_published'] && !empty($postData['date'])) {
            $publishedAt = date('Y-m-d H:i:s', strtotime($postData['date']));
        }
        
        // Determine action (create or update)
        $action = $isEdit ? 'update' : 'create';
        
        if ($action === 'create') {
            error_log("Creating new article");
            // Create new article
            if ($articleData['is_published'] && !empty($publishedAt)) {
                error_log("Creating as published with published_at: $publishedAt");
                $sql = "INSERT INTO articles (slug, title, excerpt, content, author, category_id, is_published, published_at, meta_title, meta_description, featured_image, tags, created_at, updated_at) 
                        VALUES (:slug, :title, :excerpt, :content, :author, :category_id, :is_published, :published_at, :meta_title, :meta_description, :featured_image, :tags, NOW(), NOW())";
                
                $insertData = [
                    'slug' => $articleData['slug'],
                    'title' => $articleData['title'],
                    'excerpt' => $articleData['excerpt'],
                    'content' => $articleData['content'],
                    'author' => $articleData['author'],
                    'category_id' => $articleData['category_id'],
                    'is_published' => $articleData['is_published'],
                    'meta_title' => $articleData['meta_title'],
                    'meta_description' => $articleData['meta_description'],
                    'featured_image' => $articleData['featured_image'],
                    'tags' => $articleData['tags']
                ];
                
                error_log("Insert SQL: $sql");
                error_log("Insert data keys: " . implode(', ', array_keys($insertData)));
                
                // Count placeholders in SQL
                preg_match_all('/:[a-zA-Z_]+/', $sql, $matches);
                error_log("Placeholders in SQL: " . implode(', ', $matches[0]) . " (count: " . count($matches[0]) . ")");
                error_log("Parameters provided: " . count($insertData));
                
                $stmt = $db->prepare($sql);
                $stmt->execute($insertData);
            }
            error_log("Article created successfully");
        } else {
            error_log("Updating existing article");
            // Update existing article
            if ($articleData['is_published'] && !empty($publishedAt)) {
                error_log("Updating to published status with published_at: $publishedAt");
                $sql = "UPDATE articles SET 
                        title = :title, 
                        excerpt = :excerpt, 
                        content = :content, 
                        author = :author, 
                        category_id = :category_id, 
                        is_published = :is_published, 
                        published_at = :published_at, 
                        meta_title = :meta_title, 
                        meta_description = :meta_description, 
                        featured_image = :featured_image, 
                        tags = :tags, 
                        updated_at = NOW() 
                        WHERE slug = :old_slug";
                
                $updateData = [
                    'title' => $articleData['title'],
                    'excerpt' => $articleData['excerpt'],
                    'content' => $articleData['content'],
                    'author' => $articleData['author'],
                    'category_id' => $articleData['category_id'],
                    'is_published' => $articleData['is_published'],
                    'published_at' => $publishedAt,
                    'meta_title' => $articleData['meta_title'],
                    'meta_description' => $articleData['meta_description'],
                    'featured_image' => $articleData['featured_image'],
                    'tags' => $articleData['tags'],
                    'old_slug' => $articleId
                ];
                
                error_log("Update SQL: $sql");
                error_log("Update data keys: " . implode(', ', array_keys($updateData)));
                
                // Count placeholders in SQL
                preg_match_all('/:[a-zA-Z_]+/', $sql, $matches);
                error_log("Placeholders in SQL: " . implode(', ', $matches[0]) . " (count: " . count($matches[0]) . ")");
                error_log("Parameters provided: " . count($updateData));
                
                $stmt = $db->prepare($sql);
                $stmt->execute($updateData);
            } else {
                error_log("Updating to draft status (published_at will be NULL)");
                // When not published, set published_at to NULL
                $sql = "UPDATE articles SET 
                        title = :title, 
                        excerpt = :excerpt, 
                        content = :content, 
                        author = :author, 
                        category_id = :category_id, 
                        is_published = :is_published, 
                        published_at = NULL, 
                        meta_title = :meta_title, 
                        meta_description = :meta_description, 
                        featured_image = :featured_image, 
                        tags = :tags, 
                        updated_at = NOW() 
                        WHERE slug = :old_slug";
                
                // For draft articles, we need to make sure we have all the parameters
                // but exclude published_at since it's explicitly set to NULL in the query
                $updateData = [
                    'title' => $articleData['title'],
                    'excerpt' => $articleData['excerpt'],
                    'content' => $articleData['content'],
                    'author' => $articleData['author'],
                    'category_id' => $articleData['category_id'],
                    'is_published' => $articleData['is_published'],
                    'meta_title' => $articleData['meta_title'],
                    'meta_description' => $articleData['meta_description'],
                    'featured_image' => $articleData['featured_image'],
                    'tags' => $articleData['tags'],
                    'old_slug' => $articleId
                ];
                
                error_log("Update SQL: $sql");
                error_log("Update data keys: " . implode(', ', array_keys($updateData)));
                
                // Count placeholders in SQL
                preg_match_all('/:[a-zA-Z_]+/', $sql, $matches);
                error_log("Placeholders in SQL: " . implode(', ', $matches[0]) . " (count: " . count($matches[0]) . ")");
                error_log("Parameters provided: " . count($updateData));
                
                $stmt = $db->prepare($sql);
                $stmt->execute($updateData);
            }
            
            error_log("Article updated successfully");
            
            // If slug changed, update it
            if ($slug !== $articleId) {
                error_log("Updating slug from $articleId to $slug");
                $updateSlugSql = "UPDATE articles SET slug = :new_slug WHERE slug = :old_slug";
                $updateSlugStmt = $db->prepare($updateSlugSql);
                $updateSlugStmt->execute(['new_slug' => $slug, 'old_slug' => $articleId]);
            }
        }
        
        logAdminActivity($action, "Article '{$title}' " . ($action === 'create' ? 'created' : 'updated'));
        
        $message = $action === 'create' ? 'Статья успешно создана' : 'Статья успешно обновлена';
        if ($articleData['is_published']) {
            $message .= ' и опубликована';
        }
        
        return ['success' => true, 'message' => $message, 'slug' => $slug];
        
    } catch (PDOException $e) {
        error_log("Database error in saveArticle: " . $e->getMessage());
        error_log("PDO Error Code: " . $e->getCode());
        if (isset($stmt)) {
            $errorInfo = $stmt->errorInfo();
            error_log("PDO Error Info: " . print_r($errorInfo, true));
        }
        return ['success' => false, 'message' => 'Ошибка сохранения в базе данных: ' . $e->getMessage()];
    }
}

function generateSlug($title) {
    $slug = mb_strtolower($title, 'UTF-8');
    
    // Transliterate Cyrillic to Latin
    $translit = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
        'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
    ];
    
    $slug = strtr($slug, $translit);
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/\s+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    return $slug;
}

function handleImageUpload($files, $postData, $existingArticleId = null) {
    error_log("handleImageUpload called with existingArticleId: " . ($existingArticleId ?? 'null'));
    
    // Check if user wants to remove existing image
    if (!empty($postData['remove_image']) && $existingArticleId) {
        error_log("User requested to remove existing image");
        $existingArticle = getArticleById($existingArticleId);
        if ($existingArticle && !empty($existingArticle['featured_image'])) {
            $imagePath = __DIR__ . '/..' . $existingArticle['featured_image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
                error_log("Deleted existing image file: " . $imagePath);
            }
        }
        return ['success' => true, 'image_path' => null];
    }
    
    // Check if new image was uploaded
    if (!isset($files['featured_image']) || $files['featured_image']['error'] === UPLOAD_ERR_NO_FILE) {
        error_log("No new image uploaded");
        
        // No new image uploaded, return existing image path if updating
        if ($existingArticleId) {
            $existingArticle = getArticleById($existingArticleId);
            if ($existingArticle && !empty($existingArticle['featured_image'])) {
                error_log("Returning existing image path: " . $existingArticle['featured_image']);
                return ['success' => true, 'image_path' => $existingArticle['featured_image']];
            }
        }
        
        error_log("No existing image to preserve");
        return ['success' => true, 'image_path' => null];
    }
    
    if ($files['featured_image']['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error: " . $files['featured_image']['error']);
        return ['success' => false, 'message' => 'Ошибка загрузки изображения'];
    }
    
    $file = $files['featured_image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    error_log("Uploading new image: " . $file['name'] . ", type: " . $file['type'] . ", size: " . $file['size']);
    
    if (!in_array($file['type'], $allowedTypes)) {
        error_log("Invalid file type: " . $file['type']);
        return ['success' => false, 'message' => 'Неподдерживаемый тип файла. Разрешены: JPG, PNG, GIF, WEBP'];
    }
    
    if ($file['size'] > $maxSize) {
        error_log("File too large: " . $file['size'] . " bytes");
        return ['success' => false, 'message' => 'Файл слишком большой. Максимальный размер: 5MB'];
    }
    
    $uploadDir = __DIR__ . '/../uploads/articles';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: " . $uploadDir);
            return ['success' => false, 'message' => 'Ошибка создания директории для загрузки'];
        }
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'article_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . '/' . $filename;
    $webPath = '/uploads/articles/' . $filename;
    
    error_log("Attempting to save file to: " . $uploadPath);
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        error_log("File uploaded successfully to: " . $webPath);
        return ['success' => true, 'image_path' => $webPath];
    } else {
        error_log("Failed to move uploaded file");
        return ['success' => false, 'message' => 'Ошибка загрузки файла'];
    }
}

// Get article by ID function
function getArticleById($articleId) {
    $db = getAdminDB();
    
    if (!$db) {
        return null;
    }
    
    try {
        $stmt = $db->prepare("SELECT a.*, ac.slug as category FROM articles a LEFT JOIN article_categories ac ON a.category_id = ac.id WHERE a.slug = ?");
        $stmt->execute([$articleId]);
        $article = $stmt->fetch();
        
        if ($article) {
            // Process tags from JSON to comma-separated string for form display
            if (!empty($article['tags'])) {
                $tagsArray = json_decode($article['tags'], true);
                if (is_array($tagsArray)) {
                    $article['tags'] = implode(', ', $tagsArray);
                }
            }
            
            return $article;
        }
        
        return null;
    } catch (PDOException $e) {
        error_log("Error in getArticleById: " . $e->getMessage());
        return null;
    }
}

// Get article categories function
function getArticleCategories() {
    $db = getAdminDB();
    
    if (!$db) {
        return [];
    }
    
    try {
        $stmt = $db->query("SELECT * FROM article_categories WHERE is_active = 1 ORDER BY sort_order, name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error in getArticleCategories: " . $e->getMessage());
        return [];
    }
}
?>