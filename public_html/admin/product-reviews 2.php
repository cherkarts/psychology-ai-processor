<?php
/**
 * Страница модерации отзывов товаров
 * Позволяет просматривать, одобрять, отклонять отзывы и управлять жалобами
 */

require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once '../includes/Database.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
  header('Location: login.php');
  exit;
}

// Проверяем права доступа
if (!hasPermission('reviews')) {
  header('Location: index.php');
  exit;
}

try {
  $db = Database::getInstance();
  $pdo = $db->getConnection();

  // Получаем параметры фильтрации
  $status = $_GET['status'] ?? 'all';
  $page = max(1, intval($_GET['page'] ?? 1));
  $limit = 20;
  $offset = ($page - 1) * $limit;

  // Строим запрос с фильтрами
  $whereConditions = [];
  $params = [];

  if ($status !== 'all') {
    $whereConditions[] = "pr.status = :status";
    $params[':status'] = $status;
  }

  $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

  // Получаем отзывы
  $sql = "
        SELECT 
            pr.*,
            tu.telegram_username,
            tu.telegram_first_name,
            tu.telegram_last_name,
            tu.telegram_avatar,
            p.title as product_title,
            p.slug as product_slug,
            COALESCE(l.likes_count, 0) as likes_count,
            COALESCE(r.reports_count, 0) as reports_count
        FROM product_reviews pr
        LEFT JOIN telegram_users tu ON pr.telegram_user_id = tu.telegram_id
        LEFT JOIN products p ON pr.product_id = p.id
        LEFT JOIN (
            SELECT review_id, COUNT(*) as likes_count
            FROM product_review_likes
            GROUP BY review_id
        ) l ON pr.id = l.review_id
        LEFT JOIN (
            SELECT review_id, COUNT(*) as reports_count
            FROM product_review_reports
            GROUP BY review_id
        ) r ON pr.id = r.review_id
        {$whereClause}
        ORDER BY pr.created_at DESC
        LIMIT :limit OFFSET :offset
    ";

  $stmt = $pdo->prepare($sql);
  foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
  }
  $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();

  $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Получаем общее количество отзывов
  $countSql = "SELECT COUNT(*) as total FROM product_reviews pr {$whereClause}";
  $countStmt = $pdo->prepare($countSql);
  foreach ($params as $key => $value) {
    $countStmt->bindValue($key, $value);
  }
  $countStmt->execute();
  $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

  // Получаем статистику
  $statsSql = "
        SELECT 
            status,
            COUNT(*) as count
        FROM product_reviews
        GROUP BY status
    ";
  $statsStmt = $pdo->query($statsSql);
  $stats = [];
  while ($row = $statsStmt->fetch(PDO::FETCH_ASSOC)) {
    $stats[$row['status']] = $row['count'];
  }

} catch (Exception $e) {
  $error = "Ошибка загрузки данных: " . $e->getMessage();
  $reviews = [];
  $stats = [];
  $totalCount = 0;
}

$pageTitle = 'Модерация отзывов товаров';
include 'includes/header.php';
?>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="page-header">
        <h1 class="page-title">
          <i class="fas fa-star"></i>
          Модерация отзывов товаров
        </h1>
        <div class="page-actions">
          <button class="btn btn-primary" onclick="refreshData()">
            <i class="fas fa-sync-alt"></i>
            Обновить
          </button>
        </div>
      </div>

      <?php if (isset($error)): ?>
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <!-- Статистика -->
      <div class="stats-cards">
        <div class="stat-card">
          <div class="stat-icon pending">
            <i class="fas fa-clock"></i>
          </div>
          <div class="stat-content">
            <div class="stat-number">
              <?= ($stats['pending'] ?? 0) + ($stats['waiting'] ?? 0) + ($stats['ожидает'] ?? 0) ?>
            </div>
            <div class="stat-label">На модерации</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon approved">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="stat-content">
            <div class="stat-number"><?= $stats['approved'] ?? 0 ?></div>
            <div class="stat-label">Одобрено</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon rejected">
            <i class="fas fa-times-circle"></i>
          </div>
          <div class="stat-content">
            <div class="stat-number"><?= $stats['rejected'] ?? 0 ?></div>
            <div class="stat-label">Отклонено</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon total">
            <i class="fas fa-list"></i>
          </div>
          <div class="stat-content">
            <div class="stat-number"><?= $totalCount ?></div>
            <div class="stat-label">Всего отзывов</div>
          </div>
        </div>
      </div>

      <!-- Фильтры -->
      <div class="filters-section">
        <div class="filters-row">
          <div class="filter-group">
            <label for="statusFilter">Статус:</label>
            <select id="statusFilter" class="form-control" onchange="applyFilters()">
              <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Все</option>
              <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>На модерации</option>
              <option value="waiting" <?= $status === 'waiting' ? 'selected' : '' ?>>Ожидает</option>
              <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Одобрено</option>
              <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Отклонено</option>
            </select>
          </div>
          <div class="filter-actions">
            <button class="btn btn-secondary" onclick="clearFilters()">
              <i class="fas fa-times"></i>
              Сбросить
            </button>
          </div>
        </div>
      </div>

      <!-- Список отзывов -->
      <div class="reviews-list">
        <?php if (empty($reviews)): ?>
          <div class="empty-state">
            <i class="fas fa-star"></i>
            <h3>Отзывы не найдены</h3>
            <p>Нет отзывов, соответствующих выбранным фильтрам</p>
          </div>
        <?php else: ?>
          <?php foreach ($reviews as $review): ?>
            <div class="review-card" data-review-id="<?= $review['id'] ?>">
              <div class="review-header">
                <div class="review-user">
                  <div class="user-avatar">
                    <?php
                    $avatar = $review['telegram_avatar'] ?? 'https://via.placeholder.com/40x40/6a7e9f/ffffff?text=' . substr($review['telegram_first_name'], 0, 1);
                    ?>
                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar" width="40" height="40">
                  </div>
                  <div class="user-info">
                    <div class="user-name">
                      <?= htmlspecialchars($review['telegram_first_name'] . ' ' . $review['telegram_last_name']) ?>
                    </div>
                    <div class="user-username">
                      @<?= htmlspecialchars($review['telegram_username']) ?>
                    </div>
                  </div>
                </div>
                <div class="review-meta">
                  <div class="review-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <span class="star <?= $i <= intval($review['rating']) ? 'filled' : '' ?>">★</span>
                    <?php endfor; ?>
                    <span class="rating-value"><?= intval($review['rating']) ?>/5</span>
                  </div>
                  <div class="review-date">
                    <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>
                  </div>
                  <div class="review-status status-<?= $review['status'] ?>">
                    <?php
                    $statusLabels = [
                      'pending' => 'На модерации',
                      'waiting' => 'Ожидает',
                      'ожидает' => 'Ожидает',
                      'approved' => 'Одобрено',
                      'одобрено' => 'Одобрено',
                      'rejected' => 'Отклонено',
                      'отклонено' => 'Отклонено'
                    ];
                    echo $statusLabels[$review['status']] ?? $review['status'];
                    ?>
                  </div>
                </div>
              </div>

              <div class="review-content">
                <div class="product-info">
                  <strong>Товар:</strong>
                  <a href="/product.php?slug=<?= htmlspecialchars($review['product_slug']) ?>" target="_blank">
                    <?= htmlspecialchars($review['product_title']) ?>
                  </a>
                </div>
                <div class="review-text">
                  <?= nl2br(htmlspecialchars($review['text'])) ?>
                </div>
              </div>

              <div class="review-stats">
                <div class="stat-item">
                  <i class="fas fa-heart"></i>
                  <span><?= $review['likes_count'] ?> лайков</span>
                </div>
                <div class="stat-item">
                  <i class="fas fa-flag"></i>
                  <span><?= $review['reports_count'] ?> жалоб</span>
                </div>
              </div>

              <?php if (!empty($review['moderator_comment'])): ?>
                <div class="moderator-comment">
                  <strong>Комментарий модератора:</strong>
                  <p><?= nl2br(htmlspecialchars($review['moderator_comment'])) ?></p>
                </div>
              <?php endif; ?>

              <div class="review-actions">
                <!-- Отладочная информация -->
                <small style="color: #666; font-size: 10px;">
                  Debug: status='<?= htmlspecialchars($review['status']) ?>'
                  (trimmed='<?= htmlspecialchars(trim($review['status'])) ?>'),
                  in_array=<?= in_array(trim($review['status']), ['pending', 'waiting', 'ожидает', 'Pending', 'Waiting', 'Ожидает']) ? 'true' : 'false' ?>
                </small>
                <?php
                $status = trim($review['status']);
                $waitingStatuses = ['pending', 'waiting', 'ожидает', 'Pending', 'Waiting', 'Ожидает'];
                if (in_array($status, $waitingStatuses)):
                  ?>
                  <button class="btn btn-success btn-sm" onclick="moderateReview(<?= $review['id'] ?>, 'approved')">
                    <i class="fas fa-check"></i>
                    Одобрить
                  </button>
                  <button class="btn btn-danger btn-sm" onclick="moderateReview(<?= $review['id'] ?>, 'rejected')">
                    <i class="fas fa-times"></i>
                    Отклонить
                  </button>
                <?php else: ?>
                  <small style="color: red; font-size: 10px;">
                    Кнопки скрыты: статус '<?= htmlspecialchars($review['status']) ?>'
                    (trimmed='<?= htmlspecialchars(trim($review['status'])) ?>') не входит в список ожидающих
                  </small>
                <?php endif; ?>

                <button class="btn btn-info btn-sm" onclick="viewReviewDetails(<?= $review['id'] ?>)">
                  <i class="fas fa-eye"></i>
                  Подробнее
                </button>

                <button class="btn btn-warning btn-sm" onclick="deleteReview(<?= $review['id'] ?>)" title="Удалить отзыв">
                  <i class="fas fa-trash"></i>
                  Удалить
                </button>

                <?php if ($review['reports_count'] > 0): ?>
                  <button class="btn btn-warning btn-sm" onclick="viewReports(<?= $review['id'] ?>)">
                    <i class="fas fa-flag"></i>
                    Жалобы (<?= $review['reports_count'] ?>)
                  </button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Пагинация -->
      <?php if ($totalCount > $limit): ?>
        <div class="pagination-section">
          <?php
          $totalPages = ceil($totalCount / $limit);
          $currentPage = $page;
          ?>
          <nav aria-label="Пагинация отзывов">
            <ul class="pagination">
              <?php if ($currentPage > 1): ?>
                <li class="page-item">
                  <a class="page-link" href="?page=<?= $currentPage - 1 ?>&status=<?= $status ?>">
                    <i class="fas fa-chevron-left"></i>
                  </a>
                </li>
              <?php endif; ?>

              <?php
              $startPage = max(1, $currentPage - 2);
              $endPage = min($totalPages, $currentPage + 2);

              for ($i = $startPage; $i <= $endPage; $i++):
                ?>
                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                  <a class="page-link" href="?page=<?= $i ?>&status=<?= $status ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>

              <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                  <a class="page-link" href="?page=<?= $currentPage + 1 ?>&status=<?= $status ?>">
                    <i class="fas fa-chevron-right"></i>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Модальное окно для детального просмотра отзыва -->
<div class="modal fade" id="reviewDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Детали отзыва</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="reviewDetailsContent">
        <!-- Контент загружается динамически -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>

<!-- Модальное окно для просмотра жалоб -->
<div class="modal fade" id="reportsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Жалобы на отзыв</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="reportsContent">
        <!-- Контент загружается динамически -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
      </div>
    </div>
  </div>
</div>

<style>
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
    color: #666;
    font-size: 14px;
  }

  .filters-section {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  .filters-row {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
  }

  .filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }

  .filter-group label {
    font-weight: 600;
    color: #333;
  }

  .reviews-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .review-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #e9ecef;
  }

  .review-card.status-pending {
    border-left-color: #ffc107;
  }

  .review-card.status-approved {
    border-left-color: #28a745;
  }

  .review-card.status-rejected {
    border-left-color: #dc3545;
  }

  .review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 15px;
  }

  .review-user {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .user-avatar img {
    border-radius: 50%;
    border: 2px solid #e9ecef;
  }

  .user-name {
    font-weight: 600;
    color: #333;
  }

  .user-username {
    color: #666;
    font-size: 14px;
  }

  .review-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
  }

  .review-rating {
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .star {
    color: #ddd;
    font-size: 16px;
  }

  .star.filled {
    color: #ffc107;
  }

  .rating-value {
    margin-left: 5px;
    font-weight: 600;
    color: #333;
  }

  .review-date {
    color: #666;
    font-size: 12px;
  }

  .review-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
  }

  .status-pending {
    background: #fff3cd;
    color: #856404;
  }

  .status-approved {
    background: #d4edda;
    color: #155724;
  }

  .status-rejected {
    background: #f8d7da;
    color: #721c24;
  }

  .review-content {
    margin-bottom: 15px;
  }

  .product-info {
    margin-bottom: 10px;
    color: #666;
  }

  .product-info a {
    color: #007bff;
    text-decoration: none;
  }

  .product-info a:hover {
    text-decoration: underline;
  }

  .review-text {
    color: #333;
    line-height: 1.6;
  }

  .review-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    padding: 10px 0;
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
  }

  .stat-item {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #666;
    font-size: 14px;
  }

  .moderator-comment {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    border-left: 4px solid #007bff;
  }

  .moderator-comment strong {
    color: #333;
  }

  .moderator-comment p {
    margin: 5px 0 0 0;
    color: #666;
  }

  .review-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }

  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #666;
  }

  .empty-state i {
    font-size: 48px;
    color: #ddd;
    margin-bottom: 20px;
  }

  .empty-state h3 {
    margin-bottom: 10px;
    color: #333;
  }

  .pagination-section {
    display: flex;
    justify-content: center;
    margin-top: 30px;
  }

  @media (max-width: 768px) {
    .review-header {
      flex-direction: column;
      align-items: flex-start;
    }

    .review-meta {
      align-items: flex-start;
    }

    .filters-row {
      flex-direction: column;
      align-items: stretch;
    }

    .review-actions {
      flex-direction: column;
    }

    .review-actions .btn {
      width: 100%;
    }
  }
</style>

<script>
  // Применение фильтров
  function applyFilters() {
    const status = document.getElementById('statusFilter').value;
    const url = new URL(window.location);
    url.searchParams.set('status', status);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
  }

  // Сброс фильтров
  function clearFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('status');
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
  }

  // Обновление данных
  function refreshData() {
    window.location.reload();
  }

  // Модерация отзыва
  async function moderateReview(reviewId, action) {
    const comment = prompt('Комментарий модератора (необязательно):');

    try {
      const response = await fetch('api/moderate-product-review.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          review_id: reviewId,
          action: action,
          comment: comment || ''
        })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Отзыв успешно обработан', 'success');
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      } else {
        showNotification('Ошибка: ' + result.error, 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showNotification('Ошибка при обработке отзыва', 'error');
    }
  }

  async function deleteReview(reviewId) {
    if (!confirm('Вы уверены, что хотите удалить этот отзыв? Это действие нельзя отменить.')) {
      return;
    }

    const comment = prompt('Причина удаления (необязательно):');

    try {
      const response = await fetch('api/moderate-product-review.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          review_id: reviewId,
          action: 'delete',
          comment: comment || ''
        })
      });

      const result = await response.json();

      if (result.success) {
        showNotification('Отзыв успешно удален', 'success');
        setTimeout(() => {
          window.location.reload();
        }, 1000);
      } else {
        showNotification('Ошибка: ' + result.error, 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showNotification('Ошибка при удалении отзыва', 'error');
    }
  }

  // Просмотр деталей отзыва
  async function viewReviewDetails(reviewId) {
    try {
      const response = await fetch(`api/get-product-review-details.php?id=${reviewId}`);
      const result = await response.json();

      if (result.success) {
        document.getElementById('reviewDetailsContent').innerHTML = result.html;
        new bootstrap.Modal(document.getElementById('reviewDetailsModal')).show();
      } else {
        showNotification('Ошибка загрузки деталей отзыва', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showNotification('Ошибка при загрузке деталей', 'error');
    }
  }

  // Просмотр жалоб
  async function viewReports(reviewId) {
    try {
      const response = await fetch(`api/get-product-review-reports.php?review_id=${reviewId}`);
      const result = await response.json();

      if (result.success) {
        document.getElementById('reportsContent').innerHTML = result.html;
        new bootstrap.Modal(document.getElementById('reportsModal')).show();
      } else {
        showNotification('Ошибка загрузки жалоб', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showNotification('Ошибка при загрузке жалоб', 'error');
    }
  }

  // Показ уведомлений
  function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';

    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.remove();
    }, 5000);
  }
</script>

<?php include 'includes/footer.php'; ?>