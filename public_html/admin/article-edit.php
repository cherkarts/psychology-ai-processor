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

requirePermission('articles');
$pageTitle = 'Редактор статей';

$articleId = $_GET['id'] ?? '';
$isEdit = !empty($articleId);
$article = null;

if ($isEdit) {
    $articleId = trim($articleId);
    if (empty($articleId) || is_numeric($articleId)) {
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

    // Отладочная информация
    error_log("Article loaded - ID: " . ($article['id'] ?? 'no-id'));
    error_log("Article loaded - Title: " . ($article['title'] ?? 'no-title'));
    error_log("Article loaded - Slug: " . ($article['slug'] ?? 'no-slug'));
    error_log("Article loaded - Content length: " . strlen($article['content'] ?? ''));
    error_log("Article loaded - Requested slug: " . $articleId);
}

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
                <?php echo $isEdit ? 'Редактировать статью' : 'Новая статья'; ?>
            </h1>
            <p><?php echo $isEdit ? 'Редактирование существующей статьи' : 'Создание новой статьи'; ?></p>
        </div>
        <div class="header-actions">
            <a href="articles.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад к списку
            </a>
            <?php if ($isEdit): ?>
                <a href="../article.php?slug=<?php echo urlencode($article['slug']); ?>" class="btn btn-info"
                    id="previewLink" target="_blank">
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
                        <input type="text" id="title" name="title"
                            value="<?php echo sanitizeOutput($article['title'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="slug">URL-адрес (slug)</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="text" id="slug" name="slug" style="flex: 1;"
                                value="<?php echo sanitizeOutput($article['slug'] ?? ''); ?>">
                            <button type="button" id="generateSlug" class="btn btn-secondary btn-sm">
                                <i class="fas fa-magic"></i> Сгенерировать
                            </button>
                        </div>
                        <small>Оставьте пустым для автоматической генерации. Можно редактировать вручную для красивых
                            ЧПУ.</small>
                    </div>

                    <div class="form-group">
                        <label for="excerpt">Краткое описание</label>
                        <textarea id="excerpt" name="excerpt" rows="4"
                            placeholder="Краткое описание статьи"><?php echo sanitizeOutput($article['excerpt'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Содержание статьи *</label>
                        <?php if ($isEdit): ?>
                            <div style="background: #f0f0f0; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc;">
                                <strong>Отладка:</strong><br>
                                <strong>Запрашиваемый slug:</strong> <?php echo htmlspecialchars($articleId); ?><br>
                                <strong>Slug в БД:</strong> <?php echo htmlspecialchars($article['slug'] ?? 'нет'); ?><br>
                                <strong>Длина контента в БД:</strong> <?php echo strlen($article['content'] ?? ''); ?>
                                символов
                                <?php if (!empty($article['content'])): ?>
                                    <br><strong>Первые 100 символов:</strong>
                                    <?php echo htmlspecialchars(substr($article['content'], 0, 100)); ?>
                                <?php else: ?>
                                    <br><span style="color: red;">❌ Контент пустой!</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <!-- Скрытый div с контентом для JavaScript -->
                        <div id="hidden-content" style="display: none;">
                            <?php echo htmlspecialchars($article['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>

                        <textarea id="content" name="content" rows="15" placeholder="Содержание статьи"
                            data-content="<?php echo htmlspecialchars($article['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($article['content'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <div class="form-section">
                    <h3>Параметры публикации</h3>

                    <div class="form-group">
                        <label for="status">Статус</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo (($article['is_published'] ?? 0) ? '' : 'selected'); ?>>
                                Черновик</option>
                            <option value="published" <?php echo (($article['is_published'] ?? 0) ? 'selected' : ''); ?>>Опубликовано</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="category">Категория</label>
                        <div class="category-input-group" style="display:flex; gap:8px; align-items:center;">
                            <select id="category" name="category" style="flex:1;">
                                <option value="">Без категории</option>
                                <?php foreach ($categories as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo (string) ($article['category_id'] ?? '') === (string) $id ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-secondary btn-sm" id="openCategoryModalBtn"
                                title="Новая категория"><i class="fas fa-plus"></i></button>
                            <button type="button" class="btn btn-secondary btn-sm" id="deleteCategoryBtn"
                                title="Удалить выбранную категорию"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="date">Дата публикации</label>
                        <input type="datetime-local" id="date" name="date"
                            value="<?php echo isset($article['published_at']) ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : date('Y-m-d\TH:i'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="author">Автор</label>
                        <input type="text" id="author" name="author"
                            value="<?php echo sanitizeOutput($article['author'] ?? 'Администратор'); ?>">
                    </div>


                </div>

                <div class="form-section">
                    <h3>Изображение</h3>

                    <div class="form-group">
                        <label for="featured_image">Главное изображение</label>
                        <input type="file" id="featured_image" name="featured_image" accept="image/*">
                        <div id="imagePreview" class="image-preview" style="display:none; margin-top:10px;">
                            <img id="imagePreviewImg" alt="Предпросмотр"
                                style="max-width:120px; border-radius:4px; height:auto;">
                        </div>
                    </div>

                    <?php if (!empty($article['featured_image'])): ?>
                        <div class="current-image">
                            <label>Текущее изображение:</label>
                            <img src="<?php echo sanitizeOutput($article['featured_image']); ?>" alt="Текущее изображение"
                                style="max-width: 120px; height: auto; border-radius: 4px;">
                            <div style="margin-top:8px;">
                                <label class="checkbox-label"><input type="checkbox" name="remove_image" value="1"> Удалить
                                    изображение</label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-section">
                    <h3>SEO параметры</h3>

                    <div class="form-group">
                        <label for="meta_title">Meta Title</label>
                        <input type="text" id="meta_title" name="meta_title"
                            value="<?php echo sanitizeOutput($article['meta_title'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="meta_description">Meta Description</label>
                        <textarea id="meta_description" name="meta_description"
                            rows="3"><?php echo sanitizeOutput($article['meta_description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="meta_keywords">Meta Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" value="<?php
                        $tagsValue = '';
                        if (!empty($article['tags'])) {
                            $tagArray = json_decode($article['tags'], true);
                            if (is_array($tagArray)) {
                                $tagsValue = implode(', ', $tagArray);
                            } else {
                                $tagsValue = $article['tags'];
                            }
                        } elseif (!empty($article['meta_keywords'])) {
                            $tagsValue = $article['meta_keywords'];
                        }
                        echo sanitizeOutput($tagsValue);
                        ?>" placeholder="Введите слова через запятую">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <?php echo $isEdit ? 'Обновить статью' : 'Создать статью'; ?>
            </button>
            <a href="articles.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Отмена
            </a>
        </div>
    </form>
</div>

<style>
    .article-editor-container {
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
</style>

<script>
    // Передаем контент через JavaScript переменную
    const articleContent = <?php
    $content = $article['content'] ?? '';
    // Безопасное экранирование для JavaScript
    $content = str_replace(['\\', '"', "'", "\n", "\r", "\t"], ['\\\\', '\\"', "\\'", '\\n', '\\r', '\\t'], $content);
    echo '"' . $content . '"';
    ?>;
    console.log('Article content from PHP:', articleContent);
    console.log('Article content length:', articleContent.length);

    // CKEditor initialization
    let contentEditor;

    // Проверяем содержимое textarea перед инициализацией
    const textarea = document.querySelector('#content');
    console.log('Textarea content before CKEditor init:', textarea.value);
    console.log('Textarea content length:', textarea.value.length);
    console.log('Textarea innerHTML:', textarea.innerHTML);
    console.log('Textarea textContent:', textarea.textContent);

    ClassicEditor
        .create(textarea, {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'undo', 'redo'],
            language: 'ru',
            initialData: textarea.value // Явно передаем данные
        })
        .then(editor => {
            contentEditor = editor;
            console.log('CKEditor initialized successfully');
            console.log('CKEditor content after init:', editor.getData());
            console.log('CKEditor content length:', editor.getData().length);

            // Принудительно устанавливаем контент, если он пустой
            const textareaContent = textarea.value;
            const dataContent = textarea.dataset.content;

            console.log('Textarea value length:', textareaContent.length);
            console.log('Data-content length:', dataContent ? dataContent.length : 0);
            console.log('Editor data length:', editor.getData().length);

            // Принудительно устанавливаем контент всегда
            const hiddenContent = document.getElementById('hidden-content')?.textContent || '';
            const contentToSet = articleContent || textareaContent || dataContent || hiddenContent;

            console.log('All content sources:');
            console.log('- JavaScript variable:', articleContent ? articleContent.length : 0);
            console.log('- textarea.value:', textareaContent ? textareaContent.length : 0);
            console.log('- data-content:', dataContent ? dataContent.length : 0);
            console.log('- hidden div:', hiddenContent ? hiddenContent.length : 0);

            if (contentToSet) {
                console.log('Setting content to CKEditor from:',
                    articleContent ? 'JavaScript variable' :
                        textareaContent ? 'textarea.value' :
                            dataContent ? 'data-content' : 'hidden div');
                editor.setData(contentToSet);
                console.log('Content set, new length:', editor.getData().length);

                // Дополнительная проверка через 100ms
                setTimeout(() => {
                    if (editor.getData().length === 0) {
                        console.log('Content still empty, trying again...');
                        editor.setData(contentToSet);
                        console.log('Content set again, new length:', editor.getData().length);
                    }
                }, 100);
            } else {
                console.log('No content to set from any source');
            }
        })
        .catch(error => {
            console.error('CKEditor initialization failed:', error);
        });

    // Обновляем textarea перед отправкой формы
    document.querySelector('.article-form').addEventListener('submit', function () {
        if (contentEditor) {
            contentEditor.updateSourceElement();
        }
    });

    // Функция для создания slug
    function createSlug(text) {
        const transliteration = {
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
            'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
            'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
            'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
            'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
            'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo',
            'Ж': 'Zh', 'З': 'Z', 'И': 'I', 'Й': 'Y', 'К': 'K', 'Л': 'L', 'М': 'M',
            'Н': 'N', 'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U',
            'Ф': 'F', 'Х': 'H', 'Ц': 'Ts', 'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sch',
            'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu', 'Я': 'Ya'
        };

        return text
            .toLowerCase()
            .replace(/[а-яёА-ЯЁ]/g, function (match) {
                return transliteration[match] || match;
            })
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '')
            .substring(0, 50);
    }

    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function () {
        const title = this.value;
        const slugField = document.getElementById('slug');

        if (slugField.value === '') {
            slugField.value = createSlug(title);
        }
    });

    // Кнопка генерации slug
    document.getElementById('generateSlug').addEventListener('click', function () {
        const title = document.getElementById('title').value;
        const slugField = document.getElementById('slug');

        if (title) {
            slugField.value = createSlug(title);
        } else {
            alert('Сначала введите заголовок статьи');
        }
    });

    // Update preview link with current slug/title
    const previewLink = document.getElementById('previewLink');
    if (previewLink) {
        previewLink.addEventListener('click', function (e) {
            const slugField = document.getElementById('slug');
            const titleField = document.getElementById('title');
            let slug = (slugField?.value || '').trim();
            if (!slug && titleField?.value) {
                const map = { 'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e', 'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch', 'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya' };
                slug = titleField.value.toLowerCase().replace(/[а-яё]/g, c => map[c] || c)
                    .replace(/[^a-z0-9\s-]/g, '').replace(/[\s-]+/g, '-').trim();
            }
            if (slug) {
                this.href = '../article.php?slug=' + encodeURIComponent(slug);
            }
        });
    }

    // Image preview
    const imageInput = document.getElementById('featured_image');
    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            const preview = document.getElementById('imagePreview');
            const img = document.getElementById('imagePreviewImg');
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                img.src = '';
            }
        });
    }

    // Category modal (simple prompt-based for now)
    document.getElementById('openCategoryModalBtn')?.addEventListener('click', async function () {
        const name = prompt('Название категории');
        if (!name) return;
        const slug = prompt('Slug (опционально, можно оставить пустым)') || '';
        const description = prompt('Описание (опционально)') || '';
        try {
            const form = new FormData();
            form.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
            form.append('name', name);
            form.append('slug', slug);
            form.append('description', description);
            const res = await fetch('/admin/api/save-category.php', { method: 'POST', body: form, credentials: 'same-origin' });
            const data = await res.json();
            if (data.success) {
                await reloadCategories(data.category.id);
                alert('Категория создана');
            } else {
                alert(data.message || 'Ошибка создания категории');
            }
        } catch (e) {
            alert('Ошибка: ' + (e.message || 'сети'));
        }
    });

    document.getElementById('deleteCategoryBtn')?.addEventListener('click', async function () {
        const select = document.getElementById('category');
        const id = select?.value;
        if (!id) { alert('Выберите категорию'); return; }
        if (!confirm('Удалить выбранную категорию?')) return;
        try {
            const form = new FormData();
            form.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
            form.append('category_id', id);
            const res = await fetch('/admin/api/delete-category.php', { method: 'POST', body: form, credentials: 'same-origin' });
            const data = await res.json();
            if (data.success) {
                await reloadCategories('');
                alert('Категория удалена');
            } else {
                alert(data.message || 'Ошибка удаления категории');
            }
        } catch (e) {
            alert('Ошибка: ' + (e.message || 'сети'));
        }
    });

    async function reloadCategories(selectId) {
        try {
            const res = await fetch('/admin/api/get-categories.php', { credentials: 'same-origin' });
            const data = await res.json();
            if (!data.success) return;
            const select = document.getElementById('category');
            const current = selectId || select.value;
            select.innerHTML = '<option value="">Без категории</option>';
            data.categories.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                if (String(opt.value) === String(current)) opt.selected = true;
                select.appendChild(opt);
            });
        } catch (e) { }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>