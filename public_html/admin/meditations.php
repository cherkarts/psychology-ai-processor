<?php
require_once __DIR__ . '/../includes/Models/Order.php';
require_once __DIR__ . '/../includes/Models/Article.php';
require_once __DIR__ . '/../includes/Models/Review.php';
require_once __DIR__ . '/../includes/Models/Product.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

requirePermission('products');
$pageTitle = 'Управление медитациями';

// Подключение к базе данных
try {
    $config = require '../config.php';
    $dsn = "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['dbname'];
    $charset = trim($config['database']['charset'] ?? '');
    if ($charset !== '') {
        $dsn .= ";charset=" . $charset;
    }
    $pdo = new PDO(
        $dsn,
        $config['database']['username'],
        $config['database']['password'],
        $config['database']['options']
    );
    $pdo->exec("USE `{$config['database']['dbname']}`");
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Неверный токен безопасности.';
    } else {
        $result = handleMeditationAction($_POST, $pdo);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
    header('Location: meditations.php');
    exit();
}

$page = intval($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$meditations = getMeditations($pdo, $page, $status, $search);

require_once __DIR__ . '/includes/header.php';
?>

<div class="meditations-container">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-spa"></i> Управление медитациями</h1>
            <p>Управление медитациями и аудиоконтентом</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openMeditationModal()">
                <i class="fas fa-plus"></i> Добавить медитацию
            </button>
            <button class="btn btn-secondary" onclick="openCategoryModal()">
                <i class="fas fa-folder"></i> Управление категориями
            </button>
            <button class="btn btn-secondary" onclick="exportMeditations()">
                <i class="fas fa-download"></i> Экспорт
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="status">Статус:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Все медитации</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Активные</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Неактивные</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="search">Поиск:</label>
                <input type="text" name="search" id="search" value="<?php echo sanitizeOutput($search); ?>"
                    placeholder="Поиск по названию или описанию...">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Найти
                </button>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-item">
            <span class="stat-number"><?php echo $meditations['stats']['total'] ?? 0; ?></span>
            <span class="stat-label">Всего медитаций</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $meditations['stats']['active'] ?? 0; ?></span>
            <span class="stat-label">Активные</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $meditations['stats']['total_duration'] ?? 0; ?></span>
            <span class="stat-label">Общая продолжительность (мин)</span>
        </div>
    </div>

    <!-- Meditations List -->
    <div class="meditations-list">
        <?php if (empty($meditations['items'])): ?>
            <div class="empty-state">
                <i class="fas fa-spa"></i>
                <h3>Медитаций не найдено</h3>
                <p>Нет медитаций, соответствующих текущим фильтрам.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Длительность</th>
                            <th>Статус</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meditations['items'] as $meditation): ?>
                            <tr>
                                <td><?php echo $meditation['id']; ?></td>
                                <td>
                                    <div class="meditation-title">
                                        <strong><?php echo sanitizeOutput($meditation['title']); ?></strong>
                                        <?php if (!empty($meditation['description'])): ?>
                                            <div class="meditation-description">
                                                <?php echo sanitizeOutput(substr($meditation['description'], 0, 100)); ?>...
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo sanitizeOutput($meditation['category_name'] ?? 'Без категории'); ?></td>
                                <td><?php echo formatDuration($meditation['duration']); ?></td>
                                <td>
                                    <span
                                        class="status-badge status-<?php echo $meditation['is_published'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $meditation['is_published'] ? 'Активна' : 'Неактивна'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d.m.Y H:i', strtotime($meditation['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-primary"
                                            onclick="editMeditation(<?php echo $meditation['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="deleteMeditation(<?php echo $meditation['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($meditations['pagination']['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $meditations['pagination']['total_pages']; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>"
                            class="page-link <?php echo $i === $meditations['pagination']['current_page'] ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Meditation Modal -->
<div id="meditationModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Добавить медитацию</h2>
            <span class="close" onclick="closeMeditationModal()">&times;</span>
        </div>
        <form id="meditationForm" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="meditation_id" id="meditationId">

            <div class="form-group">
                <label for="title">Название * (максимум 50 символов)</label>
                <input type="text" id="title" name="title" maxlength="50" required>
                <small class="form-text">Осталось символов: <span id="titleCounter">50</span></small>
            </div>

            <div class="form-group">
                <label for="description">Описание (максимум 130 символов)</label>
                <textarea id="description" name="description" rows="4" maxlength="130"></textarea>
                <small class="form-text">Осталось символов: <span id="descriptionCounter">130</span></small>
            </div>

            <div class="form-group">
                <label for="category_id">Категория</label>
                <select id="category_id" name="category_id">
                    <option value="">Без категории</option>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM meditation_categories WHERE is_active = 1 ORDER BY sort_order, name");
                    $categories = $stmt->fetchAll();
                    foreach ($categories as $category):
                        ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo sanitizeOutput($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="duration">Длительность (секунды)</label>
                <input type="number" id="duration" name="duration" min="0" value="0">
            </div>

            <div class="form-group">
                <label for="audio_file">Аудио файл</label>
                <input type="file" id="audio_file" name="audio_file" accept="audio/*">
                <div id="currentAudioFile" style="display: none; margin-top: 8px;">
                    <!-- Здесь будет отображаться текущий файл -->
                </div>
                <small class="form-text">Поддерживаемые форматы: MP3, WAV, OGG, M4A. Длительность будет вычислена
                    автоматически.</small>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" id="is_published" name="is_published" value="1" checked>
                    Активна
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить</button>
                <button type="button" class="btn btn-secondary" onclick="closeMeditationModal()">Отмена</button>
            </div>
        </form>
    </div>
</div>

<!-- Category Modal -->
<div id="categoryModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="categoryModalTitle">Управление категориями</h2>
            <span class="close" onclick="closeCategoryModal()">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Add Category Form -->
            <form id="categoryForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="save_category">
                <input type="hidden" name="category_id" id="categoryId">

                <div class="form-group">
                    <label for="category_name">Название категории *</label>
                    <input type="text" id="category_name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="category_description">Описание</label>
                    <textarea id="category_description" name="description" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="sort_order">Порядок сортировки</label>
                    <input type="number" id="sort_order" name="sort_order" min="0" value="0">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="category_is_active" name="is_active" value="1" checked>
                        Активна
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Сохранить категорию</button>
                    <button type="button" class="btn btn-secondary" onclick="resetCategoryForm()">Новая
                        категория</button>
                </div>
            </form>

            <!-- Categories List -->
            <div class="categories-list" style="margin-top: 30px;">
                <h3>Существующие категории</h3>
                <div id="categoriesList">
                    <!-- Categories will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function openMeditationModal() {
        document.getElementById('modalTitle').textContent = 'Добавить медитацию';
        document.getElementById('meditationForm').reset();
        document.getElementById('meditationId').value = '';
        document.getElementById('currentAudioFile').style.display = 'none';
        document.getElementById('meditationModal').style.display = 'block';
    }

    function closeMeditationModal() {
        document.getElementById('meditationModal').style.display = 'none';
    }

    function openCategoryModal() {
        document.getElementById('categoryModalTitle').textContent = 'Управление категориями';
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryModal').style.display = 'block';
        loadCategories();
    }

    function closeCategoryModal() {
        document.getElementById('categoryModal').style.display = 'none';
    }

    function editMeditation(id) {
        console.log('Editing meditation with ID:', id);

        // AJAX запрос для загрузки данных медитации
        fetch('get-meditation.php?id=' + id)
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    const meditation = data.meditation;
                    document.getElementById('modalTitle').textContent = 'Редактировать медитацию';
                    document.getElementById('meditationId').value = meditation.id;
                    document.getElementById('title').value = meditation.title || '';
                    document.getElementById('description').value = meditation.description || '';
                    document.getElementById('category_id').value = meditation.category_id || '';
                    document.getElementById('duration').value = meditation.duration || 0;
                    document.getElementById('is_published').checked = meditation.is_published == 1;

                    // Показываем текущий файл если есть
                    const currentFileDiv = document.getElementById('currentAudioFile');
                    if (meditation.audio_file) {
                        currentFileDiv.innerHTML = `
                        <div class="current-file">
                            <i class="fas fa-music"></i>
                            <span>Текущий файл: ${meditation.audio_file.split('/').pop()}</span>
                        </div>
                    `;
                        currentFileDiv.style.display = 'block';
                    } else {
                        currentFileDiv.style.display = 'none';
                    }

                    document.getElementById('meditationModal').style.display = 'block';
                } else {
                    console.error('Server error:', data.message);
                    alert('Ошибка загрузки данных: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Ошибка загрузки данных медитации: ' + error.message);
            });
    }

    function deleteMeditation(id) {
        if (confirm('Вы уверены, что хотите удалить эту медитацию?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="meditation_id" value="${id}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function loadCategories() {
        fetch('api/get-categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const categoriesList = document.getElementById('categoriesList');
                    categoriesList.innerHTML = '';

                    data.categories.forEach(category => {
                        const categoryDiv = document.createElement('div');
                        categoryDiv.className = 'category-item';
                        categoryDiv.innerHTML = `
                        <div class="category-info">
                            <strong>${category.name}</strong>
                            ${category.description ? `<p>${category.description}</p>` : ''}
                            <small>Порядок: ${category.sort_order || 0} | Медитаций: ${category.meditation_count || 0}</small>
                        </div>
                        <div class="category-actions">
                            <button class="btn btn-sm btn-primary" onclick="editCategory(${category.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteCategory(${category.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                        categoriesList.appendChild(categoryDiv);
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function editCategory(id) {
        fetch('get-category.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const category = data.category;
                    document.getElementById('categoryModalTitle').textContent = 'Редактировать категорию';
                    document.getElementById('categoryId').value = category.id;
                    document.getElementById('category_name').value = category.name || '';
                    document.getElementById('category_description').value = category.description || '';
                    document.getElementById('sort_order').value = category.sort_order || 0;
                    document.getElementById('category_is_active').checked = category.is_active == 1;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function deleteCategory(id) {
        if (confirm('Вы уверены, что хотите удалить эту категорию?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="delete_category">
            <input type="hidden" name="category_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function resetCategoryForm() {
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryModalTitle').textContent = 'Добавить категорию';
    }

    function exportMeditations() {
        window.open('export-meditations.php', '_blank');
    }

    // Закрытие модальных окон при клике вне их
    window.onclick = function (event) {
        const meditationModal = document.getElementById('meditationModal');
        const categoryModal = document.getElementById('categoryModal');

        if (event.target === meditationModal) {
            closeMeditationModal();
        }
        if (event.target === categoryModal) {
            closeCategoryModal();
        }
    }

    // Счетчики символов для формы медитации
    document.addEventListener('DOMContentLoaded', function () {
        const titleInput = document.getElementById('title');
        const descriptionTextarea = document.getElementById('description');
        const titleCounter = document.getElementById('titleCounter');
        const descriptionCounter = document.getElementById('descriptionCounter');

        if (titleInput && titleCounter) {
            titleInput.addEventListener('input', function () {
                const remaining = 50 - this.value.length;
                titleCounter.textContent = remaining;
                titleCounter.style.color = remaining < 10 ? 'red' : 'inherit';
            });
        }

        if (descriptionTextarea && descriptionCounter) {
            descriptionTextarea.addEventListener('input', function () {
                const remaining = 130 - this.value.length;
                descriptionCounter.textContent = remaining;
                descriptionCounter.style.color = remaining < 20 ? 'red' : 'inherit';
            });
        }
    });
</script>

<?php
require_once __DIR__ . '/includes/footer.php';

function getMeditations($pdo, $page = 1, $status = 'all', $search = '')
{
    $itemsPerPage = 20; // ADMIN_ITEMS_PER_PAGE
    $offset = ($page - 1) * $itemsPerPage;

    // Базовый запрос
    $sql = "SELECT m.*, mc.name as category_name 
            FROM meditations m 
            LEFT JOIN meditation_categories mc ON m.category_id = mc.id";

    $whereConditions = [];
    $params = [];

    // Фильтр по статусу
    if ($status !== 'all') {
        $whereConditions[] = "m.is_published = ?";
        $params[] = ($status === 'active') ? 1 : 0;
    }

    // Фильтр по поиску
    if (!empty($search)) {
        $whereConditions[] = "(m.title LIKE ? OR m.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($whereConditions)) {
        $sql .= " WHERE " . implode(" AND ", $whereConditions);
    }

    $sql .= " ORDER BY m.created_at DESC";

    // Получаем общее количество
    $countSql = "SELECT COUNT(*) FROM meditations m";
    if (!empty($whereConditions)) {
        $countSql .= " WHERE " . implode(" AND ", $whereConditions);
    }

    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalItems = $stmt->fetchColumn();

    // Получаем данные для текущей страницы
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $itemsPerPage;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $meditations = $stmt->fetchAll();

    // Исправляем кодировку в данных медитаций
    foreach ($meditations as &$meditation) {
        if (isset($meditation['title'])) {
            $meditation['title'] = @iconv('UTF-8', 'Windows-1251//IGNORE', $meditation['title']) ?: $meditation['title'];
        }
        if (isset($meditation['description'])) {
            $meditation['description'] = @iconv('UTF-8', 'Windows-1251//IGNORE', $meditation['description']) ?: $meditation['description'];
        }
        if (isset($meditation['category_name'])) {
            $meditation['category_name'] = @iconv('UTF-8', 'Windows-1251//IGNORE', $meditation['category_name']) ?: $meditation['category_name'];
        }
    }

    // Получаем статистику
    $stats = getMeditationStats($pdo);

    return [
        'items' => $meditations,
        'stats' => $stats,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $itemsPerPage),
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage
        ]
    ];
}

function getMeditationStats($pdo)
{
    $stats = ['total' => 0, 'active' => 0, 'total_duration' => 0];

    // Общее количество
    $stmt = $pdo->query("SELECT COUNT(*) FROM meditations");
    $stats['total'] = $stmt->fetchColumn();

    // Активные
    $stmt = $pdo->query("SELECT COUNT(*) FROM meditations WHERE is_published = 1");
    $stats['active'] = $stmt->fetchColumn();

    // Общая длительность
    $stmt = $pdo->query("SELECT SUM(duration) FROM meditations WHERE is_published = 1");
    $totalSeconds = $stmt->fetchColumn();
    $stats['total_duration'] = floor($totalSeconds / 60);

    return $stats;
}

function handleMeditationAction($data, $pdo)
{
    $action = $data['action'] ?? '';

    if ($action === 'delete') {
        $meditationId = $data['meditation_id'] ?? '';
        if (empty($meditationId)) {
            return ['success' => false, 'message' => 'ID медитации обязателен.'];
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM meditations WHERE id = ?");
            $stmt->execute([$meditationId]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Медитация удалена успешно.'];
            } else {
                return ['success' => false, 'message' => 'Медитация не найдена.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Ошибка при удалении: ' . $e->getMessage()];
        }
    }

    if ($action === 'save_category') {
        $categoryId = $data['category_id'] ?? '';
        $name = $data['name'] ?? '';
        $description = $data['description'] ?? '';
        $sortOrder = intval($data['sort_order'] ?? 0);
        $isActive = isset($data['is_active']) ? 1 : 0;

        if (empty($name)) {
            return ['success' => false, 'message' => 'Название категории обязательно.'];
        }

        try {
            // Генерируем уникальный slug
            $slug = generateUniqueSlug($name, 'meditation_categories', empty($categoryId) ? null : $categoryId);

            if (empty($categoryId)) {
                // Создание новой категории
                $stmt = $pdo->prepare("
                    INSERT INTO meditation_categories (name, description, sort_order, is_active, slug, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $description, $sortOrder, $isActive, $slug]);
                return ['success' => true, 'message' => 'Категория создана успешно.'];
            } else {
                // Обновление существующей категории
                $stmt = $pdo->prepare("
                    UPDATE meditation_categories 
                    SET name = ?, description = ?, sort_order = ?, is_active = ?, slug = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$name, $description, $sortOrder, $isActive, $slug, $categoryId]);
                return ['success' => true, 'message' => 'Категория обновлена успешно.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Ошибка при сохранении: ' . $e->getMessage()];
        }
    }

    if ($action === 'delete_category') {
        $categoryId = $data['category_id'] ?? '';
        if (empty($categoryId)) {
            return ['success' => false, 'message' => 'ID категории обязателен.'];
        }

        try {
            // Проверяем, есть ли медитации в этой категории
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM meditations WHERE category_id = ?");
            $stmt->execute([$categoryId]);
            $meditationCount = $stmt->fetchColumn();

            if ($meditationCount > 0) {
                return ['success' => false, 'message' => 'Нельзя удалить категорию, в которой есть медитации.'];
            }

            $stmt = $pdo->prepare("DELETE FROM meditation_categories WHERE id = ?");
            $stmt->execute([$categoryId]);

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Категория удалена успешно.'];
            } else {
                return ['success' => false, 'message' => 'Категория не найдена.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Ошибка при удалении: ' . $e->getMessage()];
        }
    }

    if ($action === 'save') {
        $meditationId = $data['meditation_id'] ?? '';
        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';
        $categoryId = !empty($data['category_id']) ? $data['category_id'] : null;
        $duration = intval($data['duration'] ?? 0);
        $isPublished = isset($data['is_published']) ? 1 : 0;
        $audioFile = null;

        if (empty($title)) {
            return ['success' => false, 'message' => 'Название обязательно.'];
        }

        // Обработка загрузки аудио файла
        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../audio/meditations/';

            // Создаем директорию если не существует
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileInfo = pathinfo($_FILES['audio_file']['name']);
            $extension = strtolower($fileInfo['extension']);

            // Проверяем допустимые форматы
            $allowedFormats = ['mp3', 'wav', 'ogg', 'm4a'];
            if (!in_array($extension, $allowedFormats)) {
                return ['success' => false, 'message' => 'Недопустимый формат файла. Разрешены: ' . implode(', ', $allowedFormats)];
            }

            // Генерируем уникальное имя файла
            $fileName = uniqid() . '_' . generateSlug($title) . '.' . $extension;
            $uploadPath = $uploadDir . $fileName;

            // Загружаем файл
            if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $uploadPath)) {
                $audioFile = '/audio/meditations/' . $fileName;

                // Автоматически вычисляем длительность
                $duration = getAudioDuration($uploadPath);
            } else {
                return ['success' => false, 'message' => 'Ошибка при загрузке файла.'];
            }
        }

        try {
            // Генерируем уникальный slug
            $baseSlug = generateSlug($title);
            $slug = $baseSlug;
            $counter = 1;

            // Проверяем уникальность slug
            while (true) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM meditations WHERE slug = ? AND id != ?");
                $stmt->execute([$slug, $meditationId ?: 0]);
                if ($stmt->fetchColumn() == 0) {
                    break;
                }
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            if (empty($meditationId)) {
                // Создание новой медитации
                $stmt = $pdo->prepare("
                    INSERT INTO meditations (title, description, category_id, duration, audio_file, is_published, slug, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$title, $description, $categoryId, $duration, $audioFile, $isPublished, $slug]);
                return ['success' => true, 'message' => 'Медитация создана успешно.'];
            } else {
                // Обновление существующей медитации
                $updateFields = "title = ?, description = ?, category_id = ?, duration = ?, is_published = ?, slug = ?";
                $params = [$title, $description, $categoryId, $duration, $isPublished, $slug];

                // Добавляем audio_file если файл был загружен
                if ($audioFile !== null) {
                    $updateFields .= ", audio_file = ?";
                    $params[] = $audioFile;
                }

                $params[] = $meditationId; // для WHERE id = ?

                $stmt = $pdo->prepare("
                    UPDATE meditations 
                    SET {$updateFields}
                    WHERE id = ?
                ");
                $stmt->execute($params);
                return ['success' => true, 'message' => 'Медитация обновлена успешно.'];
            }
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Ошибка при сохранении: ' . $e->getMessage()];
        }
    }

    return ['success' => false, 'message' => 'Неизвестное действие.'];
}

function formatDuration($seconds)
{
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    return sprintf('%02d:%02d', $minutes, $remainingSeconds);
}

// Audio duration functions moved to includes/functions.php

// Audio duration helper functions moved to includes/functions.php
?>