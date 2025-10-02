<?php
require_once '../includes/Models/Order.php';
require_once '../includes/Models/Article.php';
require_once '../includes/Models/Meditation.php';
require_once '../includes/Models/Review.php';
require_once '../includes/Models/Product.php';
require_once '../includes/Database.php';
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
// Temporary debug to surface errors while fixing
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once __DIR__ . '/../includes/admin-functions.php';



// Check permissions
requirePermission('reviews');

$pageTitle = 'Управление отзывами';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Записываем в файл на хостинге для отладки
    $debugFile = __DIR__ . '/../debug-reviews.log';
    $debugMessage = "[" . date('Y-m-d H:i:s') . "] POST request received. Action: " . ($_POST['action'] ?? 'not set') . ", Data: " . json_encode($_POST) . PHP_EOL;
    file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);

    error_log("POST request received. Action: " . ($_POST['action'] ?? 'not set') . ", Data: " . json_encode($_POST));

    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $debugMessage = "[" . date('Y-m-d H:i:s') . "] CSRF token verification failed" . PHP_EOL;
        file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);
        error_log("CSRF token verification failed");
        $_SESSION['error_message'] = 'Неверный токен безопасности.';
    } else {
        $debugMessage = "[" . date('Y-m-d H:i:s') . "] CSRF token verified, calling handleReviewAction" . PHP_EOL;
        file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);

        $debugMessage = "[" . date('Y-m-d H:i:s') . "] Function exists: " . (function_exists('handleReviewAction') ? 'YES' : 'NO') . PHP_EOL;
        file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);

        error_log("CSRF token verified, calling handleReviewAction");
        error_log("Function exists: " . (function_exists('handleReviewAction') ? 'YES' : 'NO'));
        try {
            // Добавляем отладку перед вызовом функции
            $debugMessage = "[" . date('Y-m-d H:i:s') . "] About to call handleReviewAction with: " . json_encode($_POST) . PHP_EOL;
            file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);

            $result = handleReviewAction($_POST);

            $debugMessage = "[" . date('Y-m-d H:i:s') . "] handleReviewAction result: " . json_encode($result) . PHP_EOL;
            file_put_contents($debugFile, $debugMessage, FILE_APPEND | LOCK_EX);
            error_log("handleReviewAction result: " . json_encode($result));
        } catch (Throwable $th) {
            error_log("Exception in handleReviewAction: " . $th->getMessage());
            $result = ['success' => false, 'message' => 'Server error: ' . $th->getMessage(), 'trace' => $th->getTraceAsString()];
        }
        if (!empty($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit();
        } else {
            if ($result['success']) {
                $_SESSION['success_message'] = $result['message'];
            } else {
                $_SESSION['error_message'] = $result['message'];
            }
        }
    }

    if (!empty($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        exit();
    } elseif (!headers_sent()) {
        header('Location: /admin/reviews.php');
        exit();
    } else {
        echo '<!doctype html><html><head><meta http-equiv="refresh" content="0;url=/admin/reviews.php"></head><body><script>window.location.replace("/admin/reviews.php");</script></body></html>';
        exit();
    }
}

// Get reviews with pagination
$page = intval($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$reviews = getReviews($page, $status, $search);

require_once __DIR__ . '/includes/header.php';
?>
<script>
    // Ensure modal helpers exist early
    window.adminCSRFToken = '<?php echo generateCSRFToken(); ?>';

    function ensureReviewFormMarkup() {
        var form = document.getElementById('reviewForm');
        if (!form) return null;
        // try reuse token from any existing hidden input
        var existingTokenInput = document.querySelector('#reviewForm input[name="csrf_token"]');
        var token = existingTokenInput ? existingTokenInput.value : window.adminCSRFToken;
        if (form.children && form.children.length > 0) return form;
        form.innerHTML = '' +
            '<input type="hidden" name="csrf_token" value="' + (token || '') + '">' +
            '<input type="hidden" name="action" value="edit">' +
            '<input type="hidden" name="review_id" id="reviewId">' +
            '<div class="form-group">' +
            '  <label for="reviewName">Имя *</label>' +
            '  <input type="text" id="reviewName" name="name" required>' +
            '</div>' +
            '<div class="form-group">' +
            '  <label for="reviewEmail">Email</label>' +
            '  <input type="email" id="reviewEmail" name="email">' +
            '</div>' +
            '<div class="form-group">' +
            '  <label for="reviewRating">Оценка</label>' +
            '  <select id="reviewRating" name="rating">' +
            '    <option value="5">5 звезд</option>' +
            '    <option value="4">4 звезды</option>' +
            '    <option value="3">3 звезды</option>' +
            '    <option value="2">2 звезды</option>' +
            '    <option value="1">1 звезда</option>' +
            '  </select>' +
            '</div>' +
            '<div class="form-group">' +
            '  <label for="reviewContent">Отзыв *</label>' +
            '  <textarea id="reviewContent" name="content" rows="6" required></textarea>' +
            '</div>' +
            '<div class="form-group">' +
            '  <label for="reviewStatus">Статус</label>' +
            '  <select id="reviewStatus" name="status">' +
            '    <option value="pending">Ожидает модерации</option>' +
            '    <option value="approved">Одобрен</option>' +
            '    <option value="rejected">Отклонен</option>' +
            '  </select>' +
            '</div>' +
            '<div class="form-actions">' +
            '  <button type="submit" class="btn btn-primary">Сохранить</button>' +
            '  <button type="button" class="btn btn-secondary" data-action="close-review-modal" onclick="window.closeReviewModal && window.closeReviewModal()">Отмена</button>' +
            '</div>';
        return form;
    }

    window.openReviewModal = window.openReviewModal || function () {
        var modal = document.getElementById('reviewModal');
        if (!modal) return;
        var title = document.getElementById('modalTitle');
        var form = ensureReviewFormMarkup() || document.getElementById('reviewForm');
        var id = document.getElementById('reviewId');
        if (title) title.textContent = 'Добавить отзыв';
        if (form) form.reset();
        if (id) id.value = '';
        if (form) {
            form.style.display = 'block';
            form.style.visibility = 'visible';
            form.style.opacity = '1';
        }
        modal.style.display = 'block';
    };

    window.closeReviewModal = window.closeReviewModal || function () {
        var modal = document.getElementById('reviewModal');
        if (modal) modal.style.display = 'none';
    };

    window.editReview = window.editReview || function (reviewId, name, email, rating, content, status) {
        var modal = document.getElementById('reviewModal');
        if (!modal) return;
        var t = document.getElementById('modalTitle');
        if (t) t.textContent = 'Редактировать отзыв';
        ensureReviewFormMarkup();
        var id = document.getElementById('reviewId');
        if (id) id.value = reviewId || '';
        var n = document.getElementById('reviewName');
        if (n) n.value = name || '';
        var e = document.getElementById('reviewEmail');
        if (e) e.value = email || '';
        var r = document.getElementById('reviewRating');
        if (r) r.value = String(rating || '');
        var c = document.getElementById('reviewContent');
        if (c) c.value = content || '';
        var s = document.getElementById('reviewStatus');
        if (s) s.value = status || 'approved';
        if (document.getElementById('reviewForm')) {
            var f = document.getElementById('reviewForm');
            f.style.display = 'block';
            f.style.visibility = 'visible';
            f.style.opacity = '1';
        }
        modal.style.display = 'block';
    };

    window.resetReview = window.resetReview || function (reviewId) {
        if (!reviewId) return;
        if (!confirm('Сбросить статус отзыва в "Ожидает модерации"?')) return;
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="csrf_token" value="' + (window.adminCSRFToken || '') + '">' +
            '<input type="hidden" name="action" value="update_status">' +
            '<input type="hidden" name="review_id" value="' + reviewId + '">' +
            '<input type="hidden" name="status" value="pending">';
        document.body.appendChild(form);
        form.submit();
    };

    window.deleteReview = window.deleteReview || function (reviewId) {
        if (!reviewId) return;
        if (!confirm('Удалить отзыв? Это действие нельзя отменить.')) return;
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="csrf_token" value="' + (window.adminCSRFToken || '') + '">' +
            '<input type="hidden" name="action" value="delete">' +
            '<input type="hidden" name="review_id" value="' + reviewId + '">';
        document.body.appendChild(form);
        form.submit();
    };

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-action]');
        if (!btn) return;
        var id = btn.dataset.id;
        switch (btn.dataset.action) {
            case 'submit-review':
                (function () {
                    var form = document.getElementById('reviewForm');
                    if (!form) return;
                    var formData = new FormData(form);
                    formData.append('ajax', '1');
                    fetch(window.location.pathname, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(function (r) { return r.json(); }).then(function (data) {
                        if (data && data.success) { window.location.reload(); }
                        else { alert((data && data.message) || 'Ошибка сохранения'); }
                    }).catch(function () { form.submit(); });
                })();
                break;
            case 'open-review-modal':
                window.openReviewModal();
                break;
            case 'close-review-modal':
                window.closeReviewModal();
                break;
            case 'edit-review':
                window.editReview(id, btn.dataset.name || '', btn.dataset.email || '', btn.dataset.rating || '', btn.dataset.content || '', btn.dataset.status || 'approved');
                break;
            case 'reset-review':
                window.resetReview(id);
                break;
            case 'delete-review':
                window.deleteReview(id);
                break;
            case 'export-reviews':
                if (typeof exportReviews === 'function') exportReviews();
                break;
        }
    });
</script>

<div class="reviews-container">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-comments"></i> Управление отзывами</h1>
            <p>Управление отзывами клиентов и рекомендациями</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" data-action="open-review-modal"
                onclick="window.openReviewModal && window.openReviewModal()">
                <i class="fas fa-plus"></i> Добавить отзыв
            </button>
            <button class="btn btn-secondary" data-action="export-reviews">
                <i class="fas fa-download"></i> Экспорт
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
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

    <!-- Filters and Search -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="status">Статус:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Все отзывы</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Ожидают модерации
                    </option>
                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Одобренные</option>
                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Отклоненные</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="search">Поиск:</label>
                <input type="text" name="search" id="search" value="<?php echo sanitizeOutput($search); ?>"
                    placeholder="Поиск по имени или содержанию...">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Найти
                </button>
            </div>
        </form>
    </div>

    <!-- Reviews Stats -->
    <div class="stats-row">
        <div class="stat-item">
            <span class="stat-number"><?php echo $reviews['stats']['total'] ?? 0; ?></span>
            <span class="stat-label">Всего отзывов</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $reviews['stats']['pending'] ?? 0; ?></span>
            <span class="stat-label">Ожидают</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $reviews['stats']['approved'] ?? 0; ?></span>
            <span class="stat-label">Одобренные</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $reviews['stats']['rejected'] ?? 0; ?></span>
            <span class="stat-label">Отклоненные</span>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="reviews-list">
        <?php if (!empty($reviews['items'])): ?>
            <?php foreach ($reviews['items'] as $review): ?>
                <div class="review-item" data-review-id="<?php echo $review['id']; ?>">
                    <div class="review-header">
                        <div class="review-info">
                            <h3 class="review-name"><?php echo sanitizeOutput($review['name']); ?></h3>
                            <div class="review-meta">
                                <span
                                    class="review-date"><?php echo date('d.m.Y H:i', strtotime($review['created_at'])); ?></span>
                                <span class="review-status status-<?php echo $review['status']; ?>">
                                    <?php
                                    switch ($review['status']) {
                                        case 'pending':
                                            echo 'Ожидает';
                                            break;
                                        case 'approved':
                                            echo 'Одобрен';
                                            break;
                                        case 'rejected':
                                            echo 'Отклонен';
                                            break;
                                        default:
                                            echo ucfirst($review['status']);
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="review-actions">
                            <?php if ($review['status'] === 'pending'): ?>
                                <button class="btn btn-success btn-sm" data-action="approve-review"
                                    data-id="<?php echo $review['id']; ?>"
                                    onclick="window.approveReview && window.approveReview(this.dataset.id)">
                                    <i class="fas fa-check"></i> Одобрить
                                </button>
                                <button class="btn btn-warning btn-sm" data-action="reject-review"
                                    data-id="<?php echo $review['id']; ?>"
                                    onclick="window.rejectReview && window.rejectReview(this.dataset.id)">
                                    <i class="fas fa-times"></i> Отклонить
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-primary btn-sm" data-action="edit-review"
                                data-id="<?php echo $review['id']; ?>"
                                data-name="<?php echo htmlspecialchars($review['name'], ENT_QUOTES); ?>"
                                data-email="<?php echo htmlspecialchars($review['email'] ?? '', ENT_QUOTES); ?>"
                                data-phone="<?php echo htmlspecialchars($review['phone'] ?? '', ENT_QUOTES); ?>"
                                data-rating="<?php echo (int) ($review['rating'] ?? 0); ?>"
                                data-age="<?php echo (int) ($review['age'] ?? 0); ?>"
                                data-content="<?php echo htmlspecialchars(($review['content'] ?? ($review['text'] ?? '')), ENT_QUOTES); ?>"
                                data-tags="<?php echo htmlspecialchars($review['tags'] ?? '', ENT_QUOTES); ?>"
                                data-type="<?php echo htmlspecialchars($review['type'] ?? 'text', ENT_QUOTES); ?>"
                                data-image="<?php echo htmlspecialchars($review['image'] ?? '', ENT_QUOTES); ?>"
                                data-video="<?php echo htmlspecialchars($review['video'] ?? '', ENT_QUOTES); ?>"
                                data-avatar="<?php echo htmlspecialchars($review['telegram_avatar'] ?? '', ENT_QUOTES); ?>"
                                data-created-at="<?php echo htmlspecialchars($review['created_at'] ?? '', ENT_QUOTES); ?>"
                                data-status="<?php echo $review['status']; ?>"
                                onclick="window.editReview && window.editReview(this.dataset.id, this.dataset.name, this.dataset.email, this.dataset.phone, this.dataset.rating, this.dataset.age, this.dataset.content, this.dataset.tags, this.dataset.type, this.dataset.image, this.dataset.video, this.dataset.avatar, this.dataset.createdAt, this.dataset.status)">
                                <i class="fas fa-edit"></i> Редактировать
                            </button>
                            <button class="btn btn-secondary btn-sm" data-action="reset-review"
                                data-id="<?php echo $review['id']; ?>"
                                onclick="window.resetReview && window.resetReview(this.dataset.id)">
                                <i class="fas fa-undo"></i> Сбросить
                            </button>
                            <button class="btn btn-danger btn-sm" data-action="delete-review"
                                data-id="<?php echo $review['id']; ?>"
                                onclick="window.deleteReview && window.deleteReview(this.dataset.id)">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                        </div>
                    </div>

                    <div class="review-content">
                        <div class="review-text">
                            <?php $__content = $review['content'] ?? ($review['text'] ?? '');
                            echo nl2br(sanitizeOutput($__content)); ?>
                        </div>

                        <div class="review-details">
                            <div class="review-rating">
                                <span class="rating-label">Оценка:</span>
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="rating-text"><?php echo $review['rating']; ?>/5</span>
                                </div>
                            </div>

                            <?php if (!empty($review['email'])): ?>
                                <div class="review-email">
                                    <span class="email-label">Email:</span>
                                    <a href="mailto:<?php echo sanitizeOutput($review['email']); ?>">
                                        <?php echo sanitizeOutput($review['email']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-reviews">
                <i class="fas fa-comments"></i>
                <p>Отзывы не найдены</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php
    $hasPagination = isset($reviews['pagination'])
        && isset($reviews['pagination']['total_pages'])
        && isset($reviews['pagination']['current_page'])
        && (int) $reviews['pagination']['total_pages'] > 1;
    ?>
    <?php if ($hasPagination): ?>
        <div class="pagination">
            <?php if ($reviews['pagination']['current_page'] > 1): ?>
                <a href="?page=<?php echo $reviews['pagination']['current_page'] - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"
                    class="page-link">
                    <i class="fas fa-chevron-left"></i> Предыдущая
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $reviews['pagination']['total_pages']; $i++): ?>
                <?php if ($i == $reviews['pagination']['current_page']): ?>
                    <span class="page-link active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"
                        class="page-link"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($reviews['pagination']['current_page'] < $reviews['pagination']['total_pages']): ?>
                <a href="?page=<?php echo $reviews['pagination']['current_page'] + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"
                    class="page-link">
                    Следующая <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Добавить отзыв</h2>
            <span class="close" data-action="close-review-modal"
                onclick="window.closeReviewModal && window.closeReviewModal()">&times;</span>
        </div>
        <form id="reviewForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="review_id" id="reviewId">

            <div class="form-group">
                <label for="reviewName">Имя *</label>
                <input type="text" id="reviewName" name="name" required>
            </div>

            <div class="form-group">
                <label for="reviewEmail">Email</label>
                <input type="email" id="reviewEmail" name="email">
            </div>

            <div class="form-group">
                <label for="reviewPhone">Телефон</label>
                <input type="tel" id="reviewPhone" name="phone">
            </div>

            <div class="form-group">
                <label for="reviewRating">Оценка</label>
                <select id="reviewRating" name="rating">
                    <option value="5">5 звезд</option>
                    <option value="4">4 звезды</option>
                    <option value="3">3 звезды</option>
                    <option value="2">2 звезды</option>
                    <option value="1">1 звезда</option>
                </select>
            </div>

            <div class="form-group">
                <label for="reviewAge">Возраст</label>
                <input type="number" id="reviewAge" name="age" min="1" max="120">
            </div>

            <div class="form-group">
                <label for="reviewContent">Отзыв *</label>
                <textarea id="reviewContent" name="content" rows="6" required></textarea>
            </div>

            <div class="form-group">
                <label for="reviewTags">Теги</label>
                <input type="text" id="reviewTags" name="tags" placeholder="Введите теги через запятую">
            </div>

            <div class="form-group">
                <label for="reviewType">Тип отзыва</label>
                <select id="reviewType" name="type">
                    <option value="text">Текстовый</option>
                    <option value="photo">С фото</option>
                    <option value="video">Видео</option>
                </select>
            </div>

            <div class="form-group">
                <label for="reviewImage">Изображение</label>
                <input type="file" id="reviewImage" name="image" accept="image/*">
                <div id="currentImage" style="margin-top: 10px;"></div>
            </div>

            <div class="form-group">
                <label for="reviewVideo">Видео</label>
                <input type="file" id="reviewVideo" name="video" accept="video/*">
                <div id="currentVideo" style="margin-top: 10px;"></div>
            </div>

            <div class="form-group">
                <label for="reviewAvatar">Аватар</label>
                <input type="file" id="reviewAvatar" name="avatar" accept="image/*">
                <div id="currentAvatar" style="margin-top: 10px;"></div>
            </div>

            <div class="form-group">
                <label for="reviewCreatedAt">Дата создания</label>
                <input type="datetime-local" id="reviewCreatedAt" name="created_at">
            </div>

            <div class="form-group">
                <label for="reviewStatus">Статус</label>
                <select id="reviewStatus" name="status">
                    <option value="pending">Ожидает модерации</option>
                    <option value="approved">Одобрен</option>
                    <option value="rejected">Отклонен</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-primary" data-action="submit-review"
                    onclick="window.submitReview && window.submitReview()">Сохранить</button>
                <button type="button" class="btn btn-secondary" data-action="close-review-modal"
                    onclick="window.closeReviewModal && window.closeReviewModal()">Отмена</button>
            </div>
        </form>
    </div>
</div>

<style>
    .reviews-container {
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

    .filters-section {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .filters-form {
        display: flex;
        gap: 20px;
        align-items: end;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .filter-group label {
        font-weight: 500;
        color: #333;
    }

    .filter-group input,
    .filter-group select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-item {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #28a745;
    }

    .stat-number {
        display: block;
        font-size: 2.5em;
        font-weight: bold;
        color: #28a745;
        margin-bottom: 5px;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
    }

    .reviews-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .review-item {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        border-left: 4px solid #007bff;
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        gap: 20px;
    }

    .review-info {
        flex: 1;
    }

    .review-name {
        margin: 0 0 5px 0;
        color: #333;
        font-size: 1.2em;
    }

    .review-meta {
        display: flex;
        gap: 15px;
        color: #666;
        font-size: 14px;
    }

    .review-status {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }

    .status-approved {
        background-color: #d4edda;
        color: #155724;
    }

    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
    }

    .review-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 8px 16px;
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

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background-color: #c82333;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 12px;
    }

    .review-content {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }

    .review-text {
        flex: 1;
        line-height: 1.6;
        color: #333;
    }

    .review-details {
        display: flex;
        flex-direction: column;
        gap: 15px;
        min-width: 200px;
    }

    .review-rating,
    .review-email {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .rating-label,
    .email-label {
        font-weight: 500;
        color: #666;
        font-size: 14px;
    }

    .stars {
        display: flex;
        align-items: center;
        gap: 2px;
    }

    .stars .fa-star {
        color: #ddd;
        font-size: 16px;
    }

    .stars .fa-star.filled {
        color: #ffc107;
    }

    .rating-text {
        margin-left: 8px;
        color: #666;
        font-size: 14px;
    }

    .review-email a {
        color: #007bff;
        text-decoration: none;
    }

    .review-email a:hover {
        text-decoration: underline;
    }

    .no-reviews {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .no-reviews i {
        font-size: 3em;
        margin-bottom: 20px;
        color: #ddd;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
    }

    .page-link {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #007bff;
        transition: all 0.3s ease;
    }

    .page-link:hover {
        background-color: #007bff;
        color: white;
    }

    .page-link.active {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    /* Modal styles */
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
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .modal-header h2 {
        margin: 0;
        color: #333;
    }

    .close {
        color: #aaa;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
    }

    .close:hover {
        color: #000;
    }

    #reviewForm {
        padding: 20px;
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

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 30px;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .header-actions {
            width: 100%;
            justify-content: center;
        }

        .filters-form {
            flex-direction: column;
            gap: 15px;
        }

        .filter-group {
            width: 100%;
        }

        .review-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .review-actions {
            width: 100%;
            justify-content: flex-end;
        }

        .review-details {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>




<script>
    // Modal functions (attach to window to be callable from inline onclick)
    window.openReviewModal = function () {
        var t = document.getElementById("modalTitle");
        if (t) t.textContent = "Добавить отзыв";
        var f = document.getElementById("reviewForm");
        if (f) f.reset();
        var i = document.getElementById("reviewId");
        if (i) i.value = "";
        var m = document.getElementById("reviewModal");
        if (m) m.style.display = "block";
    }

    window.closeReviewModal = function () {
        document.getElementById("reviewModal").style.display = "none";
    }

    window.editReview = function (reviewId, name, email, phone, rating, age, content, tags, type, image, video, avatar, createdAt, status) {
        var t = document.getElementById("modalTitle");
        if (t) t.textContent = "Редактировать отзыв";
        var id = document.getElementById("reviewId");
        if (id) id.value = reviewId || '';
        var n = document.getElementById("reviewName");
        if (n) n.value = name || '';
        var e = document.getElementById("reviewEmail");
        if (e) e.value = email || '';
        var p = document.getElementById("reviewPhone");
        if (p) p.value = phone || '';
        var r = document.getElementById("reviewRating");
        if (r) r.value = String(rating || '');
        var a = document.getElementById("reviewAge");
        if (a) a.value = age || '';
        var c = document.getElementById("reviewContent");
        if (c) c.value = content || '';
        var tg = document.getElementById("reviewTags");
        if (tg) tg.value = tags || '';
        var ty = document.getElementById("reviewType");
        if (ty) ty.value = type || 'text';
        var dt = document.getElementById("reviewCreatedAt");
        if (dt && createdAt) {
            // Преобразуем дату в формат datetime-local
            var date = new Date(createdAt);
            var localDateTime = date.toISOString().slice(0, 16);
            dt.value = localDateTime;
        }
        var s = document.getElementById("reviewStatus");
        if (s) s.value = status || 'approved';

        // Показываем текущие файлы
        var currentImage = document.getElementById("currentImage");
        if (currentImage && image) {
            currentImage.innerHTML = '<p>Текущее изображение: <a href="' + image + '" target="_blank">' + image + '</a></p>';
        }
        var currentVideo = document.getElementById("currentVideo");
        if (currentVideo && video) {
            currentVideo.innerHTML = '<p>Текущее видео: <a href="' + video + '" target="_blank">' + video + '</a></p>';
        }
        var currentAvatar = document.getElementById("currentAvatar");
        if (currentAvatar && avatar) {
            currentAvatar.innerHTML = '<p>Текущий аватар: <a href="' + avatar + '" target="_blank">' + avatar + '</a></p>';
        }

        var m = document.getElementById("reviewModal");
        if (m) m.style.display = "block";
    }

    window.resetReview = function (reviewId) {
        if (confirm("Сбросить статус отзыва в \"Ожидает модерации\"?")) {
            const form = document.createElement("form");
            form.method = "POST";
            form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="review_id" value="${reviewId}">
            <input type="hidden" name="status" value="pending">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    window.deleteReview = function (reviewId) {
        if (confirm("Удалить отзыв? Это действие нельзя отменить.")) {
            const form = document.createElement("form");
            form.method = "POST";
            form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="review_id" value="${reviewId}">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Delegate button clicks to avoid inline errors
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        const id = btn.dataset.id;
        switch (btn.dataset.action) {
            case 'submit-review': {
                const form = document.getElementById('reviewForm');
                if (!form) return;
                const formData = new FormData(form);
                formData.append('ajax', '1');
                fetch(window.location.pathname, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(r => r.json()).then(data => {
                    if (data && data.success) {
                        window.location.reload();
                    } else {
                        alert((data && data.message) || 'Ошибка сохранения');
                    }
                }).catch(() => {
                    form.submit();
                });
                break;
            }
            case 'open-review-modal':
                window.openReviewModal();
                break;
            case 'close-review-modal':
                window.closeReviewModal();
                break;
            case 'edit-review':
                window.editReview(id, btn.dataset.name || '', btn.dataset.email || '', btn.dataset.rating || '', btn.dataset.content || '', btn.dataset.status || 'approved');
                break;
            case 'reset-review':
                window.resetReview(id);
                break;
            case 'delete-review':
                window.deleteReview(id);
                break;
            case 'export-reviews':
                exportReviews();
                break;
        }
    });

    // Intercept review form submit to avoid page navigation
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form && form.id === 'reviewForm') {
            e.preventDefault();
            const formData = new FormData(form);
            formData.append('ajax', '1');
            fetch(window.location.pathname, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(r => r.json())
                .then(data => {
                    if (data && data.success) {
                        window.location.reload();
                    } else {
                        alert((data && data.message) || 'Ошибка сохранения');
                    }
                })
                .catch(() => {
                    // Fallback to normal submit if AJAX fails
                    form.removeEventListener('submit', arguments.callee);
                    form.submit();
                });
        }
    });

    function exportReviews() {
        alert("Функция экспорта будет добавлена позже");
    }

    // Close modal when clicking outside
    window.onclick = function (event) {
        const modal = document.getElementById("reviewModal");
        if (event.target == modal) {
            closeReviewModal();
        }
    }

    // Close modal with Escape key
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeReviewModal();
        }
    });

    // Функция одобрения отзыва
    window.approveReview = function (id) {
        console.log('approveReview called with id:', id);
        console.log('adminCSRFToken:', window.adminCSRFToken);

        if (!confirm('Вы уверены, что хотите одобрить этот отзыв?')) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'approve';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'review_id';
        idInput.value = id;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = window.adminCSRFToken || '';

        form.appendChild(actionInput);
        form.appendChild(idInput);
        form.appendChild(csrfInput);

        console.log('Form data:', {
            action: actionInput.value,
            review_id: idInput.value,
            csrf_token: csrfInput.value
        });

        document.body.appendChild(form);
        form.submit();
    };

    // Функция отклонения отзыва
    window.rejectReview = function (id) {
        console.log('rejectReview called with id:', id);
        console.log('adminCSRFToken:', window.adminCSRFToken);

        if (!confirm('Вы уверены, что хотите отклонить этот отзыв?')) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'reject';

        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'review_id';
        idInput.value = id;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = window.adminCSRFToken || '';

        form.appendChild(actionInput);
        form.appendChild(idInput);
        form.appendChild(csrfInput);

        console.log('Form data:', {
            action: actionInput.value,
            review_id: idInput.value,
            csrf_token: csrfInput.value
        });

        document.body.appendChild(form);
        form.submit();
    };
</script>
<?php $additionalJS = $additionalJS ?? [];
$additionalJS[] = 'assets/js/reviews.js';

// inline bootstrap in case external file doesn't load for any reason
$inlineJS = ($inlineJS ?? '') . "\n;(function(){function q(s,r){return (r||document).querySelector(s);} if(typeof window.openReviewModal!=='function'){window.openReviewModal=function(){var m=q('#reviewModal');if(!m)return;var t=q('#modalTitle'),f=q('#reviewForm'),i=q('#reviewId');if(t)t.textContent='Добавить отзыв';if(f)f.reset();if(i)i.value='';m.style.display='block';};} if(typeof window.closeReviewModal!=='function'){window.closeReviewModal=function(){var m=q('#reviewModal');if(m)m.style.display='none';};} if(typeof window.editReview!=='function'){window.editReview=function(id,n,e,r,c,s){var m=q('#reviewModal');if(!m)return;q('#modalTitle').textContent='Редактировать отзыв';q('#reviewId').value=(id||'');q('#reviewName').value=(n||'');q('#reviewEmail').value=(e||'');q('#reviewRating').value=String(r||'');q('#reviewContent').value=(c||'');q('#reviewStatus').value=(s||'approved');m.style.display='block';};} if(typeof window.resetReview!=='function'){window.resetReview=function(id){if(!id)return;if(!confirm('Сбросить статус отзыва в \\\"Ожидает модерации\\\"?'))return;var f=document.createElement('form');f.method='POST';f.innerHTML='<input type=\\\"hidden\\\" name=\\\"csrf_token\\\" value=\\\"'+(window.adminCSRFToken||'')+'\\\">'+'<input type=\\\"hidden\\\" name=\\\"action\\\" value=\\\"update_status\\\">'+'<input type=\\\"hidden\\\" name=\\\"review_id\\\" value=\\\"'+id+'\\\">'+'<input type=\\\"hidden\\\" name=\\\"status\\\" value=\\\"pending\\\">';document.body.appendChild(f);f.submit();};} if(typeof window.deleteReview!=='function'){window.deleteReview=function(id){if(!id)return;if(!confirm('Удалить отзыв? Это действие нельзя отменить.'))return;var f=document.createElement('form');f.method='POST';f.innerHTML='<input type=\\\"hidden\\\" name=\\\"csrf_token\\\" value=\\\"'+(window.adminCSRFToken||'')+'\\\">'+'<input type=\\\"hidden\\\" name=\\\"action\\\" value=\\\"delete\\\">'+'<input type=\\\"hidden\\\" name=\\\"review_id\\\" value=\\\"'+id+'\\\">';document.body.appendChild(f);f.submit();};} document.addEventListener('click',function(e){var b=e.target.closest('[data-action]');if(!b)return;var id=b.dataset.id;switch(b.dataset.action){case 'open-review-modal':window.openReviewModal();break;case 'close-review-modal':window.closeReviewModal();break;case 'edit-review':window.editReview(id,b.dataset.name||'',b.dataset.email||'',b.dataset.rating||'',b.dataset.content||'',b.dataset.status||'approved');break;case 'reset-review':window.resetReview(id);break;case 'delete-review':window.deleteReview(id);break;case 'export-reviews':if(typeof exportReviews==='function')exportReviews();break;}});})();\n";
?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>