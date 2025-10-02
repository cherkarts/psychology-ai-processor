<?php
require_once '../includes/Models/Order.php';
require_once '../includes/Models/Article.php';
require_once '../includes/Models/AIGenerationTask.php';
require_once '../includes/Models/Meditation.php';
require_once '../includes/Models/Review.php';
require_once '../includes/Models/Product.php';
require_once '../includes/Database.php';
require_once '../includes/admin-functions.php';
require_once '../includes/functions.php';
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

// Check permissions
requirePermission('articles');

$pageTitle = 'AI Генератор статей';

// Инициализация
$db = Database::getInstance();
$aiTask = new AIGenerationTask($db);

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error_message'] = 'Неверный токен безопасности.';
  } else {
    $result = handleAIAction($_POST);
    if ($result['success']) {
      $_SESSION['success_message'] = $result['message'];
    } else {
      $_SESSION['error_message'] = $result['message'];
    }
  }

  header('Location: ai-article-generator.php');
  exit();
}

// Получение данных
$page = intval($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'all';
$tasks = $aiTask->getTasks($page, $status, 20);
$stats = $aiTask->getTaskStats();
$categories = getArticleCategories();



require_once __DIR__ . '/includes/header.php';
?>

<div class="ai-generator-container">
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
      <h1><i class="fas fa-robot"></i> AI Генератор статей</h1>
      <p>Автоматическая генерация статей с помощью искусственного интеллекта</p>
    </div>
    <div class="header-actions">
      <button class="btn btn-primary" onclick="openCreateTaskModal()">
        <i class="fas fa-plus"></i> Создать задачу
      </button>
      <button class="btn btn-secondary" onclick="refreshTasks()">
        <i class="fas fa-sync"></i> Обновить
      </button>
    </div>
  </div>

  <!-- Статистика -->
  <div class="stats-section">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon pending">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
          <span class="stat-number"><?php echo $stats['pending']['count'] ?? 0; ?></span>
          <span class="stat-label">Ожидают</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon processing">
          <i class="fas fa-cog fa-spin"></i>
        </div>
        <div class="stat-content">
          <span class="stat-number"><?php echo $stats['processing']['count'] ?? 0; ?></span>
          <span class="stat-label">Обрабатываются</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon completed">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <span class="stat-number"><?php echo $stats['completed']['count'] ?? 0; ?></span>
          <span class="stat-label">Завершены</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon failed">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
          <span class="stat-number"><?php echo $stats['failed']['count'] ?? 0; ?></span>
          <span class="stat-label">Ошибки</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Список задач -->
  <div class="tasks-section">
    <div class="tasks-header">
      <h2>Задачи генерации</h2>
    </div>

    <div class="tasks-list" id="tasksList">
      <?php if (empty($tasks['tasks'])): ?>
        <div class="empty-state">
          <i class="fas fa-robot"></i>
          <h3>Нет задач</h3>
          <p>Создайте первую задачу для генерации статьи</p>
          <button class="btn btn-primary" onclick="openCreateTaskModal()">
            Создать задачу
          </button>
        </div>
      <?php else: ?>
        <?php foreach ($tasks['tasks'] as $task): ?>
          <div class="task-card" data-task-id="<?php echo $task['task_id']; ?>">
            <div class="task-header">
              <div class="task-title">
                <h3><?php echo sanitizeOutput($task['title']); ?></h3>
                <span class="task-topic"><?php echo sanitizeOutput($task['topic']); ?></span>
              </div>
              <div class="task-status">
                <span class="status-badge status-<?php echo $task['status']; ?>">
                  <?php echo getStatusLabel($task['status']); ?>
                </span>
              </div>
            </div>

            <div class="task-details">
              <div class="task-info">
                <div class="info-item">
                  <i class="fas fa-tag"></i>
                  <span><?php echo sanitizeOutput($task['category_name'] ?? 'Без категории'); ?></span>
                </div>
                <div class="info-item">
                  <i class="fas fa-sort-amount-up"></i>
                  <span><?php echo $task['word_count']; ?> слов</span>
                </div>
                <div class="info-item">
                  <i class="fas fa-clock"></i>
                  <span><?php echo formatDate($task['created_at']); ?></span>
                </div>
              </div>
            </div>

            <div class="task-actions">
              <?php if ($task['status'] === 'completed' && $task['generated_article_id']): ?>
                <a href="article-edit.php?id=<?php echo $task['generated_article_id']; ?>" class="btn btn-sm btn-primary">
                  <i class="fas fa-edit"></i> Редактировать
                </a>
              <?php endif; ?>

              <?php if ($task['status'] === 'failed'): ?>
                <button class="btn btn-sm btn-warning" onclick="retryTask('<?php echo $task['task_id']; ?>')">
                  <i class="fas fa-undo"></i> Вернуть в ожидание
                </button>
              <?php endif; ?>

              <button class="btn btn-sm btn-danger" onclick="deleteTask('<?php echo $task['task_id']; ?>')">
                <i class="fas fa-trash"></i> Удалить
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Модальное окно создания задачи -->
<div id="createTaskModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Создать задачу генерации</h2>
      <span class="close" onclick="closeCreateTaskModal()">&times;</span>
    </div>

    <form id="createTaskForm" method="POST">
      <input type="hidden" name="action" value="create_task">
      <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

      <div class="form-group">
        <label for="title">Заголовок статьи *</label>
        <input type="text" id="title" name="title" required>
      </div>

      <div class="form-group">
        <label for="topic">Тема статьи *</label>
        <textarea id="topic" name="topic" rows="3" required placeholder="Опишите тему статьи подробно..."></textarea>
      </div>

      <div class="form-group">
        <label for="category_id">Категория</label>
        <div style="display:flex; gap:10px; align-items:center;">
          <select id="category_id" name="category_id" style="flex:1;">
            <option value="">Выберите категорию</option>
            <?php if (is_array($categories)): ?>
              <?php foreach ($categories as $id => $name): ?>
                <option value="<?php echo $id; ?>">
                  <?php echo sanitizeOutput($name); ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
          <button type="button" class="btn btn-secondary" onclick="openNewCategory()">+ Новая</button>
        </div>
      </div>

      <div class="form-group">
        <label for="keywords">Ключевые слова</label>
        <input type="text" id="keywords" name="keywords" placeholder="Введите ключевые слова через запятую">
      </div>

      <div class="form-group">
        <label for="target_audience">Целевая аудитория</label>
        <input type="text" id="target_audience" name="target_audience" placeholder="Например: женщины 25-35 лет">
      </div>

      <div class="form-group">
        <label for="tone">Тон статьи</label>
        <select id="tone" name="tone">
          <option value="professional">Профессиональный</option>
          <option value="friendly">Дружелюбный</option>
          <option value="academic">Академический</option>
          <option value="conversational">Разговорный</option>
        </select>
      </div>

      <div class="form-group">
        <label for="word_count">Количество слов</label>
        <input type="number" id="word_count" name="word_count" value="1500" min="500" max="5000">
      </div>

      <div class="form-group">
        <label>Опции генерации</label>
        <div class="checkbox-group">
          <label class="checkbox-item">
            <input type="checkbox" name="include_faq" value="1">
            <span>Включить FAQ раздел</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="include_quotes" value="1" checked>
            <span>Включить цитаты</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="include_internal_links" value="1" checked>
            <span>Включить внутренние ссылки</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="include_table_of_contents" value="1" checked>
            <span>Включить оглавление</span>
          </label>
          <label class="checkbox-item">
            <input type="checkbox" name="seo_optimization" value="1" checked>
            <span>SEO оптимизация</span>
          </label>
        </div>
      </div>

      <div class="form-group">
        <label for="priority">Приоритет</label>
        <select id="priority" name="priority">
          <option value="low">Низкий</option>
          <option value="normal" selected>Обычный</option>
          <option value="high">Высокий</option>
          <option value="urgent">Срочный</option>
        </select>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn btn-secondary" onclick="closeCreateTaskModal()">
          Отмена
        </button>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-plus"></i> Создать задачу
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openCreateTaskModal() {
    document.getElementById('createTaskModal').style.display = 'block';
  }

  function openNewCategory() {
    const name = prompt('Введите название новой категории:');
    if (!name) return;
    fetch('/api/ai-generation.php', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'create_category', name })
    }).then(r => r.json()).then(d => {
      if (d.success) {
        const sel = document.getElementById('category_id');
        const opt = document.createElement('option');
        opt.value = d.id; opt.textContent = name; sel.appendChild(opt); sel.value = d.id;
        alert('Категория создана');
      } else {
        alert('Не удалось создать категорию');
      }
    }).catch(() => alert('Ошибка запроса'));
  }

  function closeCreateTaskModal() {
    document.getElementById('createTaskModal').style.display = 'none';
  }

  function refreshTasks() {
    location.reload();
  }

  function deleteTask(taskId) {
    if (confirm('Вы уверены, что хотите удалить эту задачу?')) {
      fetch('/api/ai-generation.php?task_id=' + taskId, {
        method: 'DELETE'
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Ошибка при удалении задачи');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Ошибка при удалении задачи');
        });
    }
  }

  function retryTask(taskId) {
    if (confirm('Перевести задачу в статус Ожидание для повторной обработки?')) {
      fetch('/api/ai-generation.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'retry_task', task_id: taskId })
      })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); else alert('Не удалось изменить статус'); })
        .catch(() => alert('Ошибка запроса'))
    }
  }

  window.onclick = function (event) {
    const modal = document.getElementById('createTaskModal');
    if (event.target === modal) {
      closeCreateTaskModal();
    }
  }
</script>

<style>
  .ai-generator-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
  }

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 15px;
  }

  .stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
  }

  .stat-icon.pending {
    background: #ffc107;
  }

  .stat-icon.processing {
    background: #17a2b8;
  }

  .stat-icon.completed {
    background: #28a745;
  }

  .stat-icon.failed {
    background: #dc3545;
  }

  .task-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    background: #fafafa;
  }

  .status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
  }

  .status-pending {
    background: #fff3cd;
    color: #856404;
  }

  .status-processing {
    background: #d1ecf1;
    color: #0c5460;
  }

  .status-completed {
    background: #d4edda;
    color: #155724;
  }

  .status-failed {
    background: #f8d7da;
    color: #721c24;
  }

  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
  }

  .modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 10px;
    width: 90%;
    max-width: 600px;
  }

  .modal-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .form-group {
    margin-bottom: 20px;
    padding: 0 20px;
  }

  .form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
  }

  .checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
  }

  .checkbox-item {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
  }

  .checkbox-item input[type="checkbox"] {
    width: auto;
  }

  .alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 30px;
    padding: 20px;
    border-top: 1px solid #e0e0e0;
  }
</style>

<?php
function getStatusLabel($status)
{
  $labels = [
    'pending' => 'Ожидает',
    'processing' => 'Обрабатывается',
    'completed' => 'Завершена',
    'failed' => 'Ошибка',
    'cancelled' => 'Отменена'
  ];
  return $labels[$status] ?? $status;
}

function formatDate($date)
{
  return date('d.m.Y H:i', strtotime($date));
}

function handleAIAction($data)
{
  global $aiTask;

  switch ($data['action']) {
    case 'create_task':
      $taskData = [
        'title' => $data['title'],
        'topic' => $data['topic'],
        'category_id' => $data['category_id'] ?: null,
        'target_audience' => $data['target_audience'] ?? null,
        'tone' => $data['tone'] ?? 'professional',
        'word_count' => intval($data['word_count'] ?? 1500),
        'include_faq' => isset($data['include_faq']),
        'include_quotes' => isset($data['include_quotes']),
        'include_internal_links' => isset($data['include_internal_links']),
        'include_table_of_contents' => isset($data['include_table_of_contents']),
        'seo_optimization' => isset($data['seo_optimization']),
        'priority' => $data['priority'] ?? 'normal',
        'keywords' => $data['keywords'] ? explode(',', $data['keywords']) : [],
        'created_by' => $_SESSION['user_id'] ?? null
      ];

      $result = $aiTask->createTask($taskData);
      if ($result['success']) {
        return ['success' => true, 'message' => 'Задача создана успешно'];
      } else {
        return ['success' => false, 'message' => $result['message']];
      }
      break;

    default:
      return ['success' => false, 'message' => 'Неизвестное действие'];
  }
}
?>