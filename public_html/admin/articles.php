<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Чистая версия страницы управления статьями
session_start();
require_once __DIR__ . '/includes/auth.php';

// Проверка авторизации (унифицированная)
requireLogin();

// Подключение к БД
$config = require '../config.php';

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

    // Получаем статьи
    $stmt = $pdo->query("
        SELECT a.*, ac.name as category_name 
        FROM articles a 
        LEFT JOIN article_categories ac ON a.category_id = ac.id 
        ORDER BY a.created_at DESC
    ");
    $rawArticles = $stmt->fetchAll();

    // Декодируем данные статей
    $articles = [];
    foreach ($rawArticles as $article) {
        // Функция для декодирования текста
        $decodeText = function ($text) {
            if (empty($text))
                return $text;

            // Попробуем разные способы декодирования
            $decoded = $text;

            // 1. Если это Unicode escape sequences
            if (strpos($text, '\\u') !== false) {
                $decoded = json_decode('"' . $text . '"');
                if ($decoded !== null) {
                    return $decoded;
                }
            }

            // 2. Если это двойное UTF-8 кодирование
            $utf8Decoded = utf8_decode($text);
            if ($utf8Decoded !== false && $utf8Decoded !== $text) {
                $decoded = $utf8Decoded;
            }

            // 3. Если это CP1251 в UTF-8
            $iconvDecoded = @iconv('CP1251', 'UTF-8', $text);
            if ($iconvDecoded !== false && $iconvDecoded !== $text) {
                $decoded = $iconvDecoded;
            }

            return $decoded;
        };

        // Декодируем поля
        $article['title'] = $decodeText($article['title']);
        $article['author'] = $decodeText($article['author']);
        $article['excerpt'] = $decodeText($article['excerpt']);
        $article['content'] = $decodeText($article['content']);
        $article['meta_title'] = $decodeText($article['meta_title']);
        $article['meta_description'] = $decodeText($article['meta_description']);

        // Теги декодируем по-особому (они в JSON)
        if (!empty($article['tags'])) {
            $tags = json_decode($article['tags'], true);
            if (is_array($tags)) {
                $decodedTags = [];
                foreach ($tags as $tag) {
                    $decodedTags[] = $decodeText($tag);
                }
                $article['tags'] = json_encode($decodedTags, JSON_UNESCAPED_UNICODE);
            }
        }

        $articles[] = $article;
    }

    // Получаем категории статей
    $stmt = $pdo->query("SELECT * FROM article_categories WHERE is_active = 1 ORDER BY sort_order, name");
    $categories = $stmt->fetchAll();

} catch (Exception $e) {
    $articles = [];
    $categories = [];
    $error = $e->getMessage();
}

$pageTitle = 'Управление статьями';
include 'includes/header.php';
?>

<style>
    .table th {
        background-color: #f8f9fa;
        border-top: none;
        font-weight: 600;
        color: #495057;
    }

    .table td {
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }

    .badge-pill {
        border-radius: 50px;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .table-responsive {
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .card {
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 25px;
        padding: 10px 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Управление статьями</h4>
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
                            <h5 class="card-title mb-0">Список статей</h5>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary" onclick="showAddArticleModal()">
                                <i class="fas fa-plus"></i> Добавить статью
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($articles)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">Статьи не найдены</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Название</th>
                                        <th>Категория</th>
                                        <th>Автор</th>
                                        <th>Статус</th>
                                        <th>Дата создания</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($articles as $article): ?>
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-secondary"><?php echo $article['id']; ?></span>
                                            </td>
                                            <td>
                                                <?php if (!empty($article['title'])): ?>
                                                    <strong><?php echo htmlspecialchars(mb_substr($article['title'], 0, 50)); ?></strong>
                                                    <?php if (mb_strlen($article['title']) > 50): ?>
                                                        <span class="text-muted">...</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted"><em>Без названия</em></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($article['category_name'])): ?>
                                                    <span
                                                        class="badge badge-info"><?php echo htmlspecialchars($article['category_name']); ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Без категории</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($article['author'])): ?>
                                                    <span
                                                        class="text-primary"><?php echo htmlspecialchars($article['author']); ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted"><em>Не указан</em></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="mb-2">
                                                    <span
                                                        class="badge badge-<?php echo $article['is_active'] ? 'success' : 'secondary'; ?> badge-pill">
                                                        <?php echo $article['is_active'] ? 'Активна' : 'Неактивна'; ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fa fa-calendar"></i>
                                                    <?php echo date('d.m.Y', strtotime($article['created_at'])); ?>
                                                    <br>
                                                    <i class="fa fa-clock-o"></i>
                                                    <?php echo date('H:i', strtotime($article['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button
                                                        class="btn btn-sm btn-outline-<?php echo $article['is_active'] ? 'warning' : 'success'; ?>"
                                                        onclick="togglePublish(<?php echo $article['id']; ?>, <?php echo $article['is_active'] ? 'false' : 'true'; ?>)"
                                                        title="<?php echo $article['is_active'] ? 'Снять с публикации' : 'Опубликовать'; ?>">
                                                        <i
                                                            class="fas fa-<?php echo $article['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-primary"
                                                        onclick="editArticle(<?php echo $article['id']; ?>)"
                                                        title="Редактировать">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="deleteArticle(<?php echo $article['id']; ?>)" title="Удалить">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
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

<!-- Модальное окно добавления/редактирования статьи -->
<div class="modal fade" id="articleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="articleModalTitle">Добавить статью</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="articleForm">
                <div class="modal-body">
                    <input type="hidden" id="articleId" name="id">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="articleTitle">Название *</label>
                                <input type="text" class="form-control" id="articleTitle" name="title" required>
                            </div>

                            <div class="form-group">
                                <label for="articleContent">Содержание *</label>
                                <textarea class="form-control" id="articleContent" name="content" rows="12"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="articleExcerpt">Краткое описание</label>
                                <textarea class="form-control" id="articleExcerpt" name="excerpt" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="articleAuthor">Автор</label>
                                <input type="text" class="form-control" id="articleAuthor" name="author">
                            </div>

                            <div class="form-group">
                                <label for="articleSlug">URL-адрес (slug)</label>
                                <input type="text" class="form-control" id="articleSlug" name="slug"
                                    placeholder="автоматически генерируется">
                            </div>

                            <div class="form-group">
                                <label for="articleDate">Дата публикации</label>
                                <input type="datetime-local" class="form-control" id="articleDate" name="date">
                            </div>

                            <div class="form-group">
                                <label for="articleCategory">Категория</label>
                                <select class="form-control" id="articleCategory" name="category_id">
                                    <option value="">Выберите категорию</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="articleTags">Теги</label>
                                <input type="text" class="form-control" id="articleTags" name="tags"
                                    placeholder="тег1, тег2, тег3">
                            </div>

                            <div class="form-group">
                                <label for="articleImage">Изображение</label>
                                <input type="file" class="form-control" id="articleImage" name="image" accept="image/*">
                                <div id="articleImagePreview" style="margin-top:8px; display:none;">
                                    <small>Текущее изображение:</small>
                                    <div style="display:flex; align-items:center; gap:10px; margin-top:6px;">
                                        <img id="articleImageThumb" src="" alt="preview"
                                            style="max-width:120px; max-height:90px; border:1px solid #ddd; padding:2px; background:#fff;">
                                        <a id="articleImageLink" href="#" target="_blank"
                                            style="word-break:break-all; max-width:300px;"></a>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            id="articleImageClear">Очистить</button>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="articleActive" name="is_active"
                                        checked>
                                    <label class="form-check-label" for="articleActive">Активна</label>
                                </div>
                            </div>
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

<!-- CKEditor (WYSIWYG, без ключей) -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    let articleEditor = null;
    function initEditor() {
        const el = document.querySelector('#articleContent');
        if (!el) return;
        if (articleEditor) { try { articleEditor.destroy(); } catch (e) { } articleEditor = null; }
        ClassicEditor
            .create(el, {
                toolbar: [
                    'undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'underline', '|', 'link', 'bulletedList', 'numberedList', '|', 'insertTable', 'blockQuote', 'code'
                ]
            })
            .then(ed => { articleEditor = ed; })
            .catch(err => { console.error('CKEditor init error', err); });
    }

    // Простая клиентская починка «кракозябр»: если нет кириллицы и много 'Р'/'С', пробуем decodeURIComponent(escape(...))
    function repairStringClient(str) {
        if (!str || /[А-Яа-яЁё]/.test(str)) return str || '';
        const bad = (str.match(/[РС]/g) || []).length;
        if (bad >= 3) {
            try {
                const fixed = decodeURIComponent(escape(str));
                if (/[А-Яа-яЁё]/.test(fixed)) return fixed;
            } catch (e) { /* ignore */ }
        }
        return str;
    }

    function openArticleModal() {
        var m = document.getElementById('articleModal');
        if (!m) return;
        m.style.display = 'block';
        m.classList.add('show');
        document.body.classList.add('modal-open');
        // backdrop
        var bd = document.createElement('div');
        bd.className = 'modal-backdrop fade show';
        bd.id = 'articleModalBackdrop';
        document.body.appendChild(bd);
    }

    function closeArticleModal() {
        var m = document.getElementById('articleModal');
        if (!m) return;
        m.classList.remove('show');
        m.style.display = 'none';
        document.body.classList.remove('modal-open');
        var bd = document.getElementById('articleModalBackdrop');
        if (bd) bd.remove();
    }

    // Кнопка закрытия в шапке модалки
    (function () {
        var closeBtn = document.querySelector('#articleModal .close');
        if (closeBtn) closeBtn.onclick = closeArticleModal;
    })();

    // Функция для автогенерации slug
    function generateSlug(title) {
        if (!title) return '';

        // Транслитерация кириллицы в латиницу
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

        let slug = title;

        // Применяем транслитерацию
        for (let cyrillic in transliteration) {
            slug = slug.replace(new RegExp(cyrillic, 'g'), transliteration[cyrillic]);
        }

        // Убираем все символы кроме букв, цифр, пробелов и дефисов
        slug = slug.replace(/[^a-zA-Z0-9\s\-]/g, '');

        // Заменяем пробелы на дефисы
        slug = slug.replace(/\s+/g, '-');

        // Убираем множественные дефисы
        slug = slug.replace(/-+/g, '-');

        // Убираем дефисы в начале и конце
        slug = slug.replace(/^-+|-+$/g, '');

        // Переводим в нижний регистр
        slug = slug.toLowerCase();

        return slug;
    }

    function showAddArticleModal() {
        document.getElementById('articleModalTitle').textContent = 'Добавить статью';
        document.getElementById('articleForm').reset();
        document.getElementById('articleId').value = '';
        openArticleModal();
        setTimeout(initEditor, 50);
    }

    // Автогенерация slug при изменении названия
    document.addEventListener('DOMContentLoaded', function () {
        const titleInput = document.getElementById('articleTitle');
        const slugInput = document.getElementById('articleSlug');

        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function () {
                const slug = generateSlug(this.value);
                slugInput.value = slug;
            });
        }
    });

    function editArticle(id) {
        // Загружаем данные статьи
        fetch('api/get-article-ultra-simple.php?id=' + id)
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const article = data.article;
                    document.getElementById('articleModalTitle').textContent = 'Редактировать статью';
                    document.getElementById('articleId').value = article.id;
                    document.getElementById('articleTitle').value = article.title || '';
                    setTimeout(function () {
                        initEditor();
                        setTimeout(function () {
                            if (articleEditor) {
                                articleEditor.setData(article.content || '');
                            } else {
                                document.getElementById('articleContent').value = article.content || '';
                            }
                        }, 120);
                    }, 50);
                    document.getElementById('articleExcerpt').value = article.excerpt || '';
                    document.getElementById('articleAuthor').value = article.author || '';
                    document.getElementById('articleCategory').value = article.category_id || '';
                    document.getElementById('articleSlug').value = article.slug || '';

                    // Автогенерация slug если поле пустое
                    const title = article.title || '';
                    const currentSlug = article.slug || '';
                    if (!currentSlug) {
                        const slug = generateSlug(title);
                        document.getElementById('articleSlug').value = slug;
                    }

                    // Обрабатываем теги - конвертируем JSON в читаемую строку
                    let tagsValue = '';
                    if (article.tags) {
                        try {
                            // Проверяем, что это валидный JSON
                            if (article.tags.startsWith('[') && article.tags.endsWith(']')) {
                                const tagsArray = JSON.parse(article.tags);
                                if (Array.isArray(tagsArray)) {
                                    tagsValue = tagsArray.join(', ');
                                } else {
                                    tagsValue = '';
                                }
                            } else {
                                // Если это не JSON, используем как есть
                                tagsValue = article.tags;
                            }
                        } catch (e) {
                            // В случае ошибки парсинга, используем пустую строку
                            tagsValue = '';
                        }
                    }
                    document.getElementById('articleTags').value = tagsValue;

                    document.getElementById('articleActive').checked = article.is_active;
                    // Превью изображения
                    try {
                        const imgUrl = article.featured_image || article.image || '';
                        const wrap = document.getElementById('articleImagePreview');
                        const link = document.getElementById('articleImageLink');
                        const img = document.getElementById('articleImageThumb');
                        if (imgUrl) {
                            wrap.style.display = 'block';
                            img.src = imgUrl;
                            link.href = imgUrl;
                            link.textContent = imgUrl;
                            // Кнопка очистить
                            const clearBtn = document.getElementById('articleImageClear');
                            if (clearBtn) {
                                clearBtn.onclick = function () {
                                    // Помечаем очистку изображения
                                    img.src = '';
                                    link.href = '#';
                                    link.textContent = '';
                                    wrap.style.display = 'none';
                                    // Сигнал серверу очистить поле
                                    window.__clearArticleImage = true;
                                };
                            }
                        } else {
                            wrap.style.display = 'none';
                            img.src = '';
                            link.href = '#';
                            link.textContent = '';
                        }
                    } catch (e) { /* ignore */ }
                    // дата (без ошибок Safari по pattern)
                    try {
                        const input = document.getElementById('articleDate');
                        const ts = article.published_at || article.created_at;
                        if (input && ts) {
                            // Принимаем ISO, "YYYY-MM-DD HH:MM:SS" и т.п.
                            const iso = ts.includes('T') ? ts : ts.replace(' ', 'T');
                            const d = new Date(iso);
                            if (!isNaN(d.getTime())) {
                                const z = n => (n < 10 ? '0' + n : n);
                                const v = d.getFullYear() + '-' + z(d.getMonth() + 1) + '-' + z(d.getDate()) + 'T' + z(d.getHours()) + ':' + z(d.getMinutes());
                                // Только если совпадает с pattern datetime-local
                                if (/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(v)) {
                                    input.value = v;
                                }
                            }
                        }
                    } catch (e) { /* ignore */ }
                    openArticleModal();
                } else {
                    alert('Ошибка загрузки статьи: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Ошибка загрузки статьи:', error);
                alert('Ошибка загрузки статьи: ' + error.message);
            });
    }

    function togglePublish(id, publish) {
        fetch('api/save-article.php', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, publish })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) { location.reload(); }
                else { alert('Ошибка: ' + data.message); }
            })
            .catch(err => { console.error(err); alert('Ошибка сети'); });
    }

    function deleteArticle(id) {
        if (confirm('Вы уверены, что хотите удалить эту статью?')) {
            fetch('api/delete-article.php', {
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
                    alert('Ошибка удаления статьи');
                });
        }
    }

    // Обработка формы
    document.getElementById('articleForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        // Собираем данные вручную (не отправляем файлы через JSON)
        const data = {};
        data.id = document.getElementById('articleId').value || null;
        data.title = (document.getElementById('articleTitle').value || '').trim();
        data.excerpt = document.getElementById('articleExcerpt').value || '';
        data.author = document.getElementById('articleAuthor').value || '';
        data.slug = document.getElementById('articleSlug').value || '';
        data.category_id = document.getElementById('articleCategory').value || '';
        data.tags = document.getElementById('articleTags').value || '';
        data.is_active = document.getElementById('articleActive').checked;
        // Контент
        data.content = articleEditor ? articleEditor.getData() : (document.getElementById('articleContent').value || '');
        // Дата публикации → YYYY-MM-DD HH:MM:SS
        (function () {
            const dv = document.getElementById('articleDate').value || '';
            if (dv) {
                // Ожидаем формат datetime-local (YYYY-MM-DDTHH:MM)
                const norm = dv.replace('T', ' ');
                data.date = /\d{4}-\d{2}-\d{2} \d{2}:\d{2}/.test(norm) ? (norm + ':00') : dv;
            }
        })();

        // Простая валидация
        if (!data.title) { alert('Введите название'); return; }
        if (!data.content || data.content.replace(/<[^>]*>/g, '').trim() === '') { alert('Введите содержимое'); return; }

        // Если выбран файл картинки — загружаем и подставляем URL
        try {
            const fileInput = document.getElementById('articleImage');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                const f = fileInput.files[0];
                const fd = new FormData();
                fd.append('file', f);
                fd.append('type', 'image');
                fd.append('scope', 'articles');
                const up = await fetch('/api/upload-media.php', { method: 'POST', body: fd });
                const uj = await up.json();
                if (uj && uj.success && uj.filepath) {
                    data.image = uj.filepath;
                } else {
                    alert('Ошибка загрузки изображения: ' + (uj?.error || '')); return;
                }
            } else if (window.__clearArticleImage) {
                // Пользователь нажал "Очистить" — отправляем пустую строку, чтобы удалить
                data.image = '';
                window.__clearArticleImage = false;
            }
        } catch (err) { console.error(err); alert('Ошибка загрузки изображения'); return; }

        fetch('api/save-article.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeArticleModal();
                    location.reload();
                } else {
                    alert('Ошибка сохранения: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка сохранения статьи');
            });
    });
</script>

<?php include 'includes/footer.php'; ?>