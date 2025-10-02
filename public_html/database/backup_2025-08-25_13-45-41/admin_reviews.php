<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

// Check permissions
requirePermission('reviews');

$pageTitle = 'Управление отзывами';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid request token.';
    } else {
        $result = handleReviewAction($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
    
    header('Location: reviews.php');
    exit();
}

// Get reviews with pagination
$page = intval($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$reviews = getReviews($page, $status, $search);

require_once __DIR__ . '/includes/header.php';
?>

<div class="reviews-container">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-comments"></i> Управление отзывами</h1>
            <p>Управление отзывами клиентов и рекомендациями</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openReviewModal()">
                <i class="fas fa-plus"></i> Добавить отзыв
            </button>
            <button class="btn btn-secondary" onclick="exportReviews()">
                <i class="fas fa-download"></i> Экспорт
            </button>
        </div>
    </div>
    
    <!-- Filters and Search -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="status">Статус:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Все отзывы</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Ожидают модерации</option>
                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Одобренные</option>
                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Отклоненные</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Поиск:</label>
                <input type="text" name="search" id="search" value="<?php echo sanitizeOutput($search); ?>" placeholder="Поиск по имени или содержанию...">
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
        <?php if (empty($reviews['items'])): ?>
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h3>Отзывов не найдено</h3>
                <p>Нет отзывов, соответствующих текущим фильтрам.</p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews['items'] as $review): ?>
                <div class="review-card" data-review-id="<?php echo $review['id']; ?>">
                    <div class="review-header">
                        <div class="review-meta">
                            <h4 class="reviewer-name"><?php echo sanitizeOutput($review['name']); ?></h4>
                            <span class="review-date"><?php 
                                $reviewDate = $review['created_at'] ?? $review['date'] ?? null;
                                if ($reviewDate) {
                                    echo date('d.m.Y H:i', strtotime($reviewDate));
                                } else {
                                    echo 'Дата не указана';
                                }
                            ?></span>
                            <span class="review-status status-<?php echo $review['status'] ?? 'approved'; ?>">
                                <?php echo ucfirst($review['status'] ?? 'approved'); ?>
                            </span>
                        </div>
                        <div class="review-actions">
                            <button class="btn btn-info btn-sm" onclick="editReview(<?php echo $review['id']; ?>)">
                                <i class="fas fa-edit"></i> Редактировать
                            </button>
                            <?php $reviewStatus = $review['status'] ?? 'approved'; ?>
                            <?php if ($reviewStatus === 'pending'): ?>
                                <button class="btn btn-success btn-sm" onclick="updateReviewStatus(<?php echo $review['id']; ?>, 'approved')">
                                    <i class="fas fa-check"></i> Одобрить
                                </button>
                                <button class="btn btn-warning btn-sm" onclick="updateReviewStatus(<?php echo $review['id']; ?>, 'rejected')">
                                    <i class="fas fa-times"></i> Отклонить
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" onclick="updateReviewStatus(<?php echo $review['id']; ?>, 'pending')">
                                    <i class="fas fa-undo"></i> Сбросить
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-danger btn-sm" onclick="deleteReview(<?php echo $review['id']; ?>)">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                        </div>
                    </div>
                    
                    <div class="review-content">
                        <?php if ($review['type'] === 'photo' && !empty($review['photo'] ?? $review['image'])): ?>
                            <div class="review-photo">
                                <img src="<?php echo sanitizeOutput($review['photo'] ?? $review['image']); ?>" alt="Review photo" onclick="viewImage(this.src)">
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($review['content'] ?? $review['text'])): ?>
                            <div class="review-text">
                                <p><?php echo nl2br(sanitizeOutput($review['content'] ?? $review['text'])); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($review['rating'])): ?>
                            <div class="review-rating">
                                <span class="rating-label">Оценка:</span>
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'active' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-value"><?php echo $review['rating']; ?>/5</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="review-footer">
                        <small class="text-muted">
                            <?php if (!empty($review['email'])): ?>
                                Email: <?php echo sanitizeOutput($review['email']); ?>
                            <?php endif; ?>
                            <?php if (!empty($review['phone'])): ?>
                                <?php if (!empty($review['email'])): ?>| <?php endif; ?>Телефон: <?php echo sanitizeOutput($review['phone']); ?>
                            <?php endif; ?>
                            <?php if (empty($review['email']) && empty($review['phone'])): ?>
                                Контактная информация не указана
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($reviews['pagination']['total_pages'] > 1): ?>
        <div class="pagination-wrapper">
            <nav class="pagination">
                <?php if ($reviews['pagination']['current_page'] > 1): ?>
                    <a href="?page=<?php echo $reviews['pagination']['current_page'] - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i> Предыдущая
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $reviews['pagination']['total_pages']; $i++): ?>
                    <?php if ($i == $reviews['pagination']['current_page']): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($reviews['pagination']['current_page'] < $reviews['pagination']['total_pages']): ?>
                    <a href="?page=<?php echo $reviews['pagination']['current_page'] + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                        Следующая <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Image Viewer Modal -->
<div class="modal-overlay" id="imageModal">
    <div class="modal-content image-modal">
        <div class="modal-header">
            <h3>Изображение отзыва</h3>
            <button class="modal-close" onclick="closeImageModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <img id="modalImage" src="" alt="Review image">
        </div>
    </div>
</div>

<!-- Review Edit/Add Modal -->
<div class="modal-overlay" id="reviewModal">
    <div class="modal-content review-modal">
        <div class="modal-header">
            <h3 id="reviewModalTitle">Добавить отзыв</h3>
            <button class="modal-close" onclick="closeReviewModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="reviewForm" class="review-form">
                <input type="hidden" name="review_id" id="reviewId">
                <input type="hidden" name="action" id="reviewAction" value="create">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="reviewName">Имя *</label>
                    <input type="text" id="reviewName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="reviewEmail">Email</label>
                    <input type="email" id="reviewEmail" name="email">
                </div>
                
                <div class="form-group">
                    <label for="reviewContent">Отзыв *</label>
                    <textarea id="reviewContent" name="content" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="reviewRating">Оценка</label>
                    <select id="reviewRating" name="rating">
                        <option value="">Не указано</option>
                        <option value="1">1 звезда</option>
                        <option value="2">2 звезды</option>
                        <option value="3">3 звезды</option>
                        <option value="4">4 звезды</option>
                        <option value="5">5 звезд</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="reviewStatus">Статус</label>
                    <select id="reviewStatus" name="status">
                        <option value="pending">Ожидает модерации</option>
                        <option value="approved">Одобрен</option>
                        <option value="rejected">Отклонен</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="reviewType">Тип отзыва</label>
                    <select id="reviewType" name="type">
                        <option value="text">Текстовый</option>
                        <option value="photo">С фото</option>
                    </select>
                </div>
                
                <div class="form-group" id="photoGroup" style="display: none;">
                    <label for="reviewPhoto">Фото</label>
                    <input type="file" id="reviewPhoto" name="photo" accept="image/*">
                    <div id="currentPhoto" style="display: none;">
                        <p>Текущее фото:</p>
                        <img id="currentPhotoImg" src="" alt="Current photo" style="max-width: 200px; max-height: 200px;">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Отмена</button>
            <button type="button" class="btn btn-primary" onclick="saveReview()">Сохранить</button>
        </div>
    </div>
</div>

<script>
function updateReviewStatus(reviewId, status) {
    const actionText = status === 'approved' ? 'approve' : status === 'rejected' ? 'reject' : 'reset';
    
    showConfirmModal(
        `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Review`,
        `Are you sure you want to ${actionText} this review?`,
        function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="review_id" value="${reviewId}">
                <input type="hidden" name="status" value="${status}">
                <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    );
}

function deleteReview(reviewId) {
    showConfirmModal(
        'Delete Review',
        'Are you sure you want to permanently delete this review? This action cannot be undone.',
        function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="review_id" value="${reviewId}">
                <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    );
}

function viewImage(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').style.display = 'flex';
}

function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}

// Review Modal Functions
function openReviewModal(reviewData = null) {
    const modal = document.getElementById('reviewModal');
    const form = document.getElementById('reviewForm');
    const title = document.getElementById('reviewModalTitle');
    const action = document.getElementById('reviewAction');
    
    // Reset form
    form.reset();
    document.getElementById('currentPhoto').style.display = 'none';
    document.getElementById('photoGroup').style.display = 'none';
    
    if (reviewData) {
        // Edit mode
        title.textContent = 'Редактировать отзыв';
        action.value = 'update';
        
        // Populate form
        document.getElementById('reviewId').value = reviewData.id;
        document.getElementById('reviewName').value = reviewData.name || '';
        document.getElementById('reviewEmail').value = reviewData.email || '';
        document.getElementById('reviewContent').value = reviewData.content || reviewData.text || '';
        document.getElementById('reviewRating').value = reviewData.rating || '';
        document.getElementById('reviewStatus').value = reviewData.status || 'approved';
        document.getElementById('reviewType').value = reviewData.type || 'text';
        
        // Show photo section if needed
        if (reviewData.type === 'photo') {
            document.getElementById('photoGroup').style.display = 'block';
            const photoUrl = reviewData.photo || reviewData.image;
            if (photoUrl) {
                document.getElementById('currentPhoto').style.display = 'block';
                document.getElementById('currentPhotoImg').src = photoUrl;
            }
        }
    } else {
        // Add mode
        title.textContent = 'Добавить отзыв';
        action.value = 'create';
    }
    
    modal.style.display = 'flex';
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}

function editReview(reviewId) {
    // Fetch review data via AJAX
    fetch(`api/review-details.php?id=${reviewId}`, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Cache-Control': 'no-cache'
        }
    })
        .then(response => {
            if (response.status === 401) {
                showToast('Сессия истекла. Пожалуйста, войдите в систему заново.', 'error');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
                return null;
            }
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data) return; // Skip if redirecting due to auth error
            
            if (data.success) {
                openReviewModal(data.review);
            } else {
                showToast(data.message || 'Ошибка загрузки данных отзыва', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Ошибка сети: ' + error.message, 'error');
        });
}

function saveReview() {
    const form = document.getElementById('reviewForm');
    const formData = new FormData(form);
    
    // Show loading state
    const saveBtn = document.querySelector('#reviewModal .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
    
    fetch('api/save-review.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.status === 401) {
            // Authentication error
            showToast('Сессия истекла. Пожалуйста, войдите в систему заново.', 'error');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
            return null;
        }
        if (!response.ok) {
            throw new Error(`Ошибка сервера: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (!data) return; // Skip if redirecting due to auth error
        
        if (data.success) {
            showToast(data.message || 'Отзыв сохранен успешно', 'success');
            closeReviewModal();
            // Reload page to show changes
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message || 'Ошибка сохранения', 'error');
            // Handle redirect if provided
            if (data.action === 'redirect' && data.redirect_url) {
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 2000);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Ошибка сети: ' + error.message, 'error');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    });
}

// Handle review type change
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('reviewType');
    const photoGroup = document.getElementById('photoGroup');
    
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            if (this.value === 'photo') {
                photoGroup.style.display = 'block';
            } else {
                photoGroup.style.display = 'none';
            }
        });
    }
});

function exportReviews() {
    window.open(`api/export-reviews.php?status=${encodeURIComponent('<?php echo $status; ?>')}&search=${encodeURIComponent('<?php echo $search; ?>')}`, '_blank');
}

// Add styles for reviews page
const reviewsStyles = `
    <style>
        .reviews-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .header-content h1 {
            color: var(--gray-800);
            margin-bottom: 0.5rem;
        }
        
        .header-content p {
            color: var(--gray-600);
            margin: 0;
        }
        
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        
        .filters-form {
            display: flex;
            gap: 2rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-group label {
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius-md);
            font-size: 0.875rem;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-item {
            background: white;
            padding: 1rem;
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .review-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: all var(--transition-normal);
        }
        
        .review-card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            background: var(--gray-50);
            border-bottom: 1px solid var(--gray-200);
        }
        
        .review-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .reviewer-name {
            margin: 0;
            color: var(--gray-800);
            font-size: 1rem;
        }
        
        .review-date {
            color: var(--gray-600);
            font-size: 0.875rem;
        }
        
        .review-status {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 500;
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
        
        .review-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .review-content {
            padding: 1.5rem;
        }
        
        .review-photo {
            margin-bottom: 1rem;
        }
        
        .review-photo img {
            max-width: 200px;
            max-height: 200px;
            border-radius: var(--border-radius-md);
            cursor: pointer;
            transition: transform var(--transition-fast);
        }
        
        .review-photo img:hover {
            transform: scale(1.05);
        }
        
        .review-text p {
            line-height: 1.6;
            color: var(--gray-700);
            margin: 0;
        }
        
        .review-rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .rating-label {
            font-weight: 500;
            color: var(--gray-700);
        }
        
        .stars {
            display: flex;
            gap: 0.125rem;
        }
        
        .stars i {
            color: var(--gray-300);
            font-size: 0.875rem;
        }
        
        .stars i.active {
            color: #ffc107;
        }
        
        .rating-value {
            font-weight: 500;
            color: var(--gray-700);
        }
        
        .review-footer {
            padding: 1rem 1.5rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray-600);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-400);
        }
        
        .pagination-wrapper {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }
        
        .pagination {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .page-link {
            padding: 0.5rem 1rem;
            background: white;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: var(--border-radius-md);
            border: 1px solid var(--gray-300);
            transition: all var(--transition-fast);
        }
        
        .page-link:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .page-link.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .image-modal .modal-body {
            text-align: center;
        }
        
        .image-modal img {
            max-width: 100%;
            max-height: 70vh;
            border-radius: var(--border-radius-md);
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filters-form {
                flex-direction: column;
                gap: 1rem;
            }
            
            .review-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }
            
            .review-actions {
                justify-content: center;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
`;

document.head.insertAdjacentHTML('beforeend', reviewsStyles);
</script>

<?php
require_once __DIR__ . '/includes/footer.php';

// Helper functions
function getReviews($page = 1, $status = 'all', $search = '') {
    $db = getAdminDB();
    $itemsPerPage = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $itemsPerPage;
    
    $reviews = [];
    $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    
    if ($db) {
        // Build query conditions
        $conditions = [];
        $params = [];
        
        if ($status !== 'all') {
            $conditions[] = "status = ?";
            $params[] = $status;
        }
        
        if (!empty($search)) {
            $conditions[] = "(name LIKE ? OR content LIKE ? OR email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM reviews " . $whereClause;
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $totalItems = $stmt->fetch()['total'];
        
        // Get reviews
        $sql = "SELECT * FROM reviews " . $whereClause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $itemsPerPage;
        $params[] = $offset;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $reviews = $stmt->fetchAll();
        
        // Get stats
        $statsQueries = [
            'total' => "SELECT COUNT(*) as count FROM reviews",
            'pending' => "SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'",
            'approved' => "SELECT COUNT(*) as count FROM reviews WHERE status = 'approved'",
            'rejected' => "SELECT COUNT(*) as count FROM reviews WHERE status = 'rejected'"
        ];
        
        foreach ($statsQueries as $key => $query) {
            $stmt = $db->query($query);
            $stats[$key] = $stmt->fetch()['count'];
        }
        
    } else {
        // Database connection failed - return empty results
        $reviews = [];
        $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        $totalItems = 0;
    }
    
    return [
        'items' => $reviews,
        'stats' => $stats,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $itemsPerPage),
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage
        ]
    ];
}

function handleReviewAction($data) {
    $action = $data['action'] ?? '';
    $reviewId = $data['review_id'] ?? '';
    
    if (empty($reviewId)) {
        return ['success' => false, 'message' => 'Review ID is required.'];
    }
    
    $db = getAdminDB();
    
    if ($db) {
        try {
            switch ($action) {
                case 'update_status':
                    $status = $data['status'] ?? '';
                    if (!in_array($status, ['pending', 'approved', 'rejected'])) {
                        return ['success' => false, 'message' => 'Invalid status.'];
                    }
                    
                    $stmt = $db->prepare("UPDATE reviews SET status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$status, $reviewId]);
                    
                    logAdminActivity('update', "Review #{$reviewId} status changed to {$status}");
                    return ['success' => true, 'message' => "Review {$status} successfully."];
                    
                case 'delete':
                    $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
                    $stmt->execute([$reviewId]);
                    
                    logAdminActivity('delete', "Review #{$reviewId} deleted");
                    return ['success' => true, 'message' => 'Review deleted successfully.'];
                    
                default:
                    return ['success' => false, 'message' => 'Invalid action.'];
            }
        } catch (PDOException $e) {
            logAdminError("Review action failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred.'];
        }
    } else {
        return ['success' => false, 'message' => 'Database connection failed.'];
    }
}


?>