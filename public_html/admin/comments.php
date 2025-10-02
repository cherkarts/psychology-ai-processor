<?php
/**
 * Рабочая админ-панель для модерации комментариев
 */

session_start();
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Проверяем разрешения
requirePermission('comments');

$db = Database::getInstance();

// Обработка действий
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $bulkAction = $_POST['bulk_action'] ?? '';
  $commentId = (int) ($_POST['comment_id'] ?? 0);
  $commentIds = $_POST['comment_ids'] ?? '';

  try {
    // Массовые действия
    if ($bulkAction && $commentIds) {
      $ids = explode(',', $commentIds);
      $ids = array_map('intval', $ids);
      $ids = array_filter($ids);

      if (!empty($ids)) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';

        switch ($bulkAction) {
          case 'approve':
            $db->execute("UPDATE comments SET status = 'approved', updated_at = NOW() WHERE id IN ($placeholders)", $ids);
            $message = count($ids) . ' комментариев одобрено';
            break;

          case 'reject':
            $db->execute("UPDATE comments SET status = 'rejected', updated_at = NOW() WHERE id IN ($placeholders)", $ids);
            $message = count($ids) . ' комментариев отклонено';
            break;

          case 'delete':
            $db->execute("DELETE FROM comments WHERE id IN ($placeholders)", $ids);
            $message = count($ids) . ' комментариев удалено';
            break;
        }
      }
    }
    // Одиночные действия
    else if ($action && $commentId) {
      switch ($action) {
        case 'approve':
          $db->update(
            'comments',
            ['status' => 'approved', 'updated_at' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $commentId]
          );
          $message = 'Комментарий одобрен';
          break;

        case 'reject':
          $db->update(
            'comments',
            ['status' => 'rejected', 'updated_at' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $commentId]
          );
          $message = 'Комментарий отклонен';
          break;

        case 'delete':
          $db->delete('comments', 'id = :id', ['id' => $commentId]);
          $message = 'Комментарий удален';
          break;
      }
    }
  } catch (Exception $e) {
    $message = 'Ошибка: ' . $e->getMessage();
  }
}

// Получаем комментарии
$status = $_GET['status'] ?? 'all';
$where = '';
$params = [];

if ($status !== 'all') {
  $where = 'WHERE status = :status';
  $params['status'] = $status;
}

$sql = "SELECT * FROM comments $where ORDER BY created_at DESC LIMIT 50";
$comments = $db->fetchAll($sql, $params);

// Получаем статистику
$stats = $db->fetchAll("SELECT status, COUNT(*) as count FROM comments GROUP BY status");
$statsArray = [];
foreach ($stats as $stat) {
  $statsArray[$stat['status']] = $stat['count'];
}

$pageTitle = 'Модерация комментариев';
include __DIR__ . '/includes/header.php';
?>

<div class="admin-content">
  <div class="admin-header">
    <div class="header-content">
      <h1>💬 Модерация комментариев</h1>
      <div class="header-actions">
        <button class="btn btn-primary" onclick="selectAll()">Выбрать все</button>
        <button class="btn btn-secondary" onclick="clearSelection()">Снять выбор</button>
      </div>
    </div>

    <!-- Статистика -->
    <div class="stats-cards">
      <div class="stat-card">
        <div class="stat-icon pending">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number"><?= $statsArray['pending'] ?? 0 ?></div>
          <div class="stat-label">На модерации</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon approved">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number"><?= $statsArray['approved'] ?? 0 ?></div>
          <div class="stat-label">Одобрено</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon rejected">
          <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number"><?= $statsArray['rejected'] ?? 0 ?></div>
          <div class="stat-label">Отклонено</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon total">
          <i class="fas fa-list"></i>
        </div>
        <div class="stat-content">
          <div class="stat-number"><?= array_sum($statsArray) ?></div>
          <div class="stat-label">Всего комментариев</div>
        </div>
      </div>
    </div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <!-- Фильтры и поиск -->
  <div class="admin-filters">
    <div class="filters-row">
      <div class="filter-group">
        <label>🔍 Поиск:</label>
        <input type="text" id="searchInput" placeholder="Поиск по тексту или автору..." onkeyup="filterComments()">
      </div>

      <div class="filter-group">
        <label>📊 Статус:</label>
        <select onchange="window.location.href='?status=' + this.value">
          <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Все</option>
          <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>На модерации</option>
          <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Одобрено</option>
          <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Отклонено</option>
        </select>
      </div>

      <div class="filter-group">
        <label>📅 Сортировка:</label>
        <select onchange="sortComments(this.value)">
          <option value="newest">Сначала новые</option>
          <option value="oldest">Сначала старые</option>
          <option value="author">По автору</option>
        </select>
      </div>
    </div>

    <!-- Массовые действия -->
    <div class="bulk-actions" id="bulkActions" style="display: none;">
      <div class="bulk-info">
        <span id="selectedCount">0</span> комментариев выбрано
      </div>
      <div class="bulk-buttons">
        <button class="btn btn-success btn-sm" onclick="bulkAction('approve')">✅ Одобрить</button>
        <button class="btn btn-warning btn-sm" onclick="bulkAction('reject')">❌ Отклонить</button>
        <button class="btn btn-danger btn-sm" onclick="bulkAction('delete')">🗑️ Удалить</button>
      </div>
    </div>
  </div>

  <!-- Список комментариев -->
  <div class="comments-list">
    <?php if (empty($comments)): ?>
      <div class="empty-state">
        <p>Комментарии не найдены</p>
      </div>
    <?php else: ?>
      <?php foreach ($comments as $comment): ?>
        <div class="comment-item" data-comment-id="<?= $comment['id'] ?>" data-status="<?= $comment['status'] ?>">
          <div class="comment-checkbox">
            <input type="checkbox" class="comment-select" value="<?= $comment['id'] ?>" onchange="updateBulkActions()">
          </div>

          <div class="comment-content">
            <div class="comment-header">
              <div class="comment-author">
                <?php if ($comment['telegram_avatar']): ?>
                  <img src="<?= htmlspecialchars($comment['telegram_avatar']) ?>" alt="Avatar" class="comment-avatar">
                <?php else: ?>
                  <div class="comment-avatar-placeholder">
                    <?= strtoupper(substr($comment['telegram_first_name'], 0, 1)) ?>
                  </div>
                <?php endif; ?>

                <div class="author-info">
                  <div class="author-name">
                    <?= htmlspecialchars($comment['telegram_first_name'] . ' ' . $comment['telegram_last_name']) ?>
                  </div>
                  <div class="author-username">
                    @<?= htmlspecialchars($comment['telegram_username']) ?>
                  </div>
                </div>
              </div>

              <div class="comment-meta">
                <div class="comment-date">
                  <span class="date-icon">📅</span>
                  <?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?>
                </div>
                <div class="comment-status status-<?= $comment['status'] ?>">
                  <?php
                  $statusLabels = [
                    'pending' => '⏳ На модерации',
                    'approved' => '✅ Одобрено',
                    'rejected' => '❌ Отклонено'
                  ];
                  echo $statusLabels[$comment['status']] ?? $comment['status'];
                  ?>
                </div>
              </div>
            </div>

            <div class="comment-text">
              <?= nl2br(htmlspecialchars($comment['text'])) ?>
            </div>

            <div class="comment-details">
              <div class="comment-context">
                <span class="context-icon">📄</span>
                <strong>Контент:</strong>
                <?= htmlspecialchars($comment['content_type']) ?>
                (ID: <?= htmlspecialchars($comment['content_id']) ?>)
              </div>
              <?php if ($comment['ip_address']): ?>
                <div class="comment-ip">
                  <span class="ip-icon">🌐</span>
                  IP: <?= htmlspecialchars($comment['ip_address']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="comment-actions">
            <?php if ($comment['status'] === 'pending'): ?>
              <form method="post" style="display: inline;">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Одобрить комментарий?')">
                  ✅ Одобрить
                </button>
              </form>

              <form method="post" style="display: inline;">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Отклонить комментарий?')">
                  ❌ Отклонить
                </button>
              </form>
            <?php endif; ?>

            <form method="post" style="display: inline;">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Удалить комментарий навсегда?')">
                🗑️ Удалить
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<style>
  .admin-header {
    margin-bottom: 30px;
  }

  .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .header-content h1 {
    margin: 0;
    color: #2c3e50;
  }

  .header-actions {
    display: flex;
    gap: 10px;
  }

  .stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: white;
    border-radius: 12px;
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

  .stat-icon.approved {
    background: #28a745;
  }

  .stat-icon.rejected {
    background: #dc3545;
  }

  .stat-icon.total {
    background: #6c757d;
  }

  .stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #333;
  }

  .stat-label {
    font-size: 14px;
    color: #666;
  }

  .admin-filters {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
  }

  .filters-row {
    display: flex;
    gap: 20px;
    align-items: center;
    flex-wrap: wrap;
  }

  .filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 200px;
  }

  .filter-group label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
  }

  .filter-group input,
  .filter-group select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
  }

  .bulk-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #e3f2fd;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
    border: 1px solid #bbdefb;
  }

  .bulk-info {
    font-weight: 600;
    color: #1976d2;
  }

  .bulk-buttons {
    display: flex;
    gap: 10px;
  }

  .comment-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    border: 1px solid #e1e5e9;
    border-radius: 12px;
    margin-bottom: 15px;
    background: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
  }

  .comment-item:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
  }

  .comment-checkbox {
    display: flex;
    align-items: flex-start;
    padding-top: 5px;
  }

  .comment-checkbox input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }

  .comment-content {
    flex: 1;
  }

  .comment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
  }

  .comment-author {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .comment-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e1e5e9;
  }

  .comment-avatar-placeholder {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
  }

  .author-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 16px;
  }

  .author-username {
    font-size: 14px;
    color: #6c757d;
    margin-top: 2px;
  }

  .comment-meta {
    text-align: right;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .comment-date {
    font-size: 12px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .date-icon {
    font-size: 14px;
  }

  .comment-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-align: center;
  }

  .status-pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
  }

  .status-approved {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .status-rejected {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  .comment-text {
    margin: 15px 0;
    line-height: 1.6;
    color: #2c3e50;
    font-size: 15px;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007bff;
  }

  .comment-details {
    font-size: 13px;
    color: #6c757d;
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .comment-context,
  .comment-ip {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .context-icon,
  .ip-icon {
    font-size: 14px;
  }

  .comment-actions {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 120px;
  }

  .empty-state {
    text-align: center;
    padding: 40px;
    color: #6c757d;
  }

  .alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
  }

  .alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  /* Адаптивность */
  @media (max-width: 768px) {
    .header-content {
      flex-direction: column;
      gap: 15px;
      align-items: stretch;
    }

    .admin-stats {
      flex-direction: column;
    }

    .filters-row {
      flex-direction: column;
    }

    .filter-group {
      min-width: auto;
    }

    .comment-item {
      flex-direction: column;
    }

    .comment-actions {
      flex-direction: row;
      justify-content: center;
      min-width: auto;
    }
  }
</style>

<script>
  // Функции для массовых действий
  function selectAll() {
    const checkboxes = document.querySelectorAll('.comment-select');
    checkboxes.forEach(checkbox => checkbox.checked = true);
    updateBulkActions();
  }

  function clearSelection() {
    const checkboxes = document.querySelectorAll('.comment-select');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    updateBulkActions();
  }

  function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.comment-select:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');

    selectedCount.textContent = checkboxes.length;

    if (checkboxes.length > 0) {
      bulkActions.style.display = 'flex';
    } else {
      bulkActions.style.display = 'none';
    }
  }

  function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.comment-select:checked');
    const commentIds = Array.from(checkboxes).map(cb => cb.value);

    if (commentIds.length === 0) {
      alert('Выберите комментарии для действия');
      return;
    }

    const actionLabels = {
      'approve': 'одобрить',
      'reject': 'отклонить',
      'delete': 'удалить'
    };

    if (confirm(`Вы уверены, что хотите ${actionLabels[action]} ${commentIds.length} комментариев?`)) {
      // Создаем форму для массового действия
      const form = document.createElement('form');
      form.method = 'POST';
      form.style.display = 'none';

      const actionInput = document.createElement('input');
      actionInput.type = 'hidden';
      actionInput.name = 'bulk_action';
      actionInput.value = action;

      const idsInput = document.createElement('input');
      idsInput.type = 'hidden';
      idsInput.name = 'comment_ids';
      idsInput.value = commentIds.join(',');

      form.appendChild(actionInput);
      form.appendChild(idsInput);
      document.body.appendChild(form);
      form.submit();
    }
  }

  // Поиск комментариев
  function filterComments() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const commentItems = document.querySelectorAll('.comment-item');

    commentItems.forEach(item => {
      const text = item.textContent.toLowerCase();
      if (text.includes(searchTerm)) {
        item.style.display = 'flex';
      } else {
        item.style.display = 'none';
      }
    });
  }

  // Сортировка комментариев
  function sortComments(sortBy) {
    const container = document.querySelector('.comments-list');
    const items = Array.from(container.querySelectorAll('.comment-item'));

    items.sort((a, b) => {
      switch (sortBy) {
        case 'newest':
          return new Date(b.dataset.createdAt || 0) - new Date(a.dataset.createdAt || 0);
        case 'oldest':
          return new Date(a.dataset.createdAt || 0) - new Date(b.dataset.createdAt || 0);
        case 'author':
          const nameA = a.querySelector('.author-name').textContent.toLowerCase();
          const nameB = b.querySelector('.author-name').textContent.toLowerCase();
          return nameA.localeCompare(nameB);
        default:
          return 0;
      }
    });

    items.forEach(item => container.appendChild(item));
  }

  // Инициализация
  document.addEventListener('DOMContentLoaded', function () {
    // Добавляем даты создания для сортировки
    const commentItems = document.querySelectorAll('.comment-item');
    commentItems.forEach(item => {
      const dateText = item.querySelector('.comment-date').textContent;
      const date = new Date(dateText.split(' ')[0].split('.').reverse().join('-') + ' ' + dateText.split(' ')[1]);
      item.dataset.createdAt = date.toISOString();
    });
  });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>