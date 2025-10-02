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
    $articles = $stmt->fetchAll();

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
                                            <td><?php echo $article['id']; ?></td>
                                            <td><?php echo htmlspecialchars($article['title']); ?></td>
                                            <td><?php echo htmlspecialchars($article['category_name'] ?? 'Без категории'); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($article['author'] ?? 'Не указан'); ?></td>
                                            <td>
                                                <span
                                                    class="badge badge-<?php echo $article['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $article['is_active'] ? 'Активна' : 'Неактивна'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d.m.Y H:i', strtotime($article['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-success mr-1"
                                                    onclick="togglePublish(<?php echo $article['id']; ?>, <?php echo $article['is_active'] ? 'false' : 'true'; ?>)"
                                                    title="<?php echo $article['is_active'] ? 'Снять с публикации' : 'Опубликовать'; ?>">
                                                    <i
                                                        class="fas fa-<?php echo $article['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="editArticle(<?php echo $article['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger"
                                                    onclick="deleteArticle(<?php echo $article['id']; ?>)">
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

    function showAddArticleModal() {
        document.getElementById('articleModalTitle').textContent = 'Добавить статью';
        document.getElementById('articleForm').reset();
        document.getElementById('articleId').value = '';
        openArticleModal();
        setTimeout(initEditor, 50);
    }

    function editArticle(id) {
        // Загружаем данные статьи
        fetch('api/get-article.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const article = data.article;
                    document.getElementById('articleModalTitle').textContent = 'Редактировать статью';
                    document.getElementById('articleId').value = article.id;
                    document.getElementById('articleTitle').value = article.title;
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
                    document.getElementById('articleTags').value = article.tags || '';
                    document.getElementById('articleActive').checked = article.is_active;
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
                    alert('Ошибка загрузки статьи: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка загрузки статьи');
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
    document.getElementById('articleForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // Собираем данные вручную (не отправляем файлы через JSON)
        const data = {};
        data.id = document.getElementById('articleId').value || null;
        data.title = (document.getElementById('articleTitle').value || '').trim();
        data.excerpt = document.getElementById('articleExcerpt').value || '';
        data.author = document.getElementById('articleAuthor').value || '';
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