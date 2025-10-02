<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

// Проверка авторизации
if (!isLoggedIn()) {
  header('Location: login.php');
  exit();
}

// Проверка прав
if (!hasPermission('articles')) {
  die('Недостаточно прав для доступа к этой странице');
}

$pageTitle = 'AI Генератор статей';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
  <div class="page-header">
    <h1><i class="fas fa-robot"></i> AI Генератор статей</h1>
    <p>Автоматическая генерация статей с помощью искусственного интеллекта</p>
  </div>

  <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i>
      <?php echo htmlspecialchars($_SESSION['success_message']);
      unset($_SESSION['success_message']); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error">
      <i class="fas fa-exclamation-circle"></i>
      <?php echo htmlspecialchars($_SESSION['error_message']);
      unset($_SESSION['error_message']); ?>
    </div>
  <?php endif; ?>

  <!-- Статистика -->
  <div class="stats-section">
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon pending">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
          <span class="stat-number">0</span>
          <span class="stat-label">Ожидают</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon processing">
          <i class="fas fa-cog fa-spin"></i>
        </div>
        <div class="stat-content">
          <span class="stat-number">0</span>
          <span class="stat-label">Обрабатываются</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon completed">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <span class="stat-number">0</span>
          <span class="stat-label">Завершены</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon failed">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
          <span class="stat-number">0</span>
          <span class="stat-label">Ошибки</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Список задач -->
  <div class="tasks-section">
    <div class="tasks-header">
      <h2>Задачи генерации</h2>
      <button class="btn btn-primary" onclick="openCreateTaskModal()">
        <i class="fas fa-plus"></i> Создать задачу
      </button>
    </div>

    <div class="tasks-list" id="tasksList">
      <div class="empty-state">
        <i class="fas fa-robot"></i>
        <h3>Нет задач</h3>
        <p>Создайте первую задачу для генерации статьи</p>
        <button class="btn btn-primary" onclick="openCreateTaskModal()">
          Создать задачу
        </button>
      </div>
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

    <form id="createTaskForm" method="POST" action="ai-article-generator-simple.php">
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
        <label for="word_count">Количество слов</label>
        <input type="number" id="word_count" name="word_count" value="1500" min="500" max="5000">
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

  function closeCreateTaskModal() {
    document.getElementById('createTaskModal').style.display = 'none';
  }

  window.onclick = function (event) {
    const modal = document.getElementById('createTaskModal');
    if (event.target === modal) {
      closeCreateTaskModal();
    }
  }
</script>

<style>
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

  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
  }

  .empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    color: #ddd;
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
// Обработка POST запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error_message'] = 'Неверный токен безопасности.';
  } else {
    if ($_POST['action'] === 'create_task') {
      // Простая обработка создания задачи
      $title = $_POST['title'] ?? '';
      $topic = $_POST['topic'] ?? '';
      $word_count = intval($_POST['word_count'] ?? 1500);
      $tone = $_POST['tone'] ?? 'professional';

      if (empty($title) || empty($topic)) {
        $_SESSION['error_message'] = 'Заголовок и тема обязательны.';
      } else {
        // Здесь можно добавить логику сохранения задачи
        $_SESSION['success_message'] = "Задача создана: $title";
      }
    }
  }

  header('Location: ai-article-generator-simple.php');
  exit();
}

require_once __DIR__ . '/includes/footer.php';
?>
