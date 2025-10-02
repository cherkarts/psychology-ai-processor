<?php
/**
 * Виджет отзывов для товаров с Telegram авторизацией
 * Поддерживает отзывы к товарам с модерацией
 */

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$productId = $productId ?? '';
$showTitle = $showTitle ?? true;

// Отладочная информация
if (empty($productId)) {
  error_log("Product reviews widget: productId is empty");
}
?>

<div class="product-reviews-widget" data-product-id="<?= htmlspecialchars($productId) ?>">
  <?php if ($showTitle): ?>
    <div class="reviews-header">
      <h3 class="reviews-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        Отзывы о товаре
        <span class="reviews-count" id="reviewsCount">0</span>
      </h3>
    </div>
  <?php endif; ?>

  <!-- Виджет авторизации через Telegram -->
  <div class="telegram-auth-section" id="telegramAuthSection">
    <?php if (isset($_SESSION['telegram_user']) && !empty($_SESSION['telegram_user'])): ?>
      <!-- Пользователь авторизован -->
      <div class="telegram-user-info">
        <div class="user-avatar">
          <?php
          $user = $_SESSION['telegram_user'];
          $avatar_url = $user['photo_url'] ?? 'https://via.placeholder.com/40x40/6a7e9f/ffffff?text=' . substr($user['first_name'], 0, 1);
          ?>
          <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Avatar" width="40" height="40">
        </div>
        <div class="user-details">
          <div class="user-name">
            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
          </div>
          <div class="user-username">
            @<?= htmlspecialchars($user['username']) ?>
          </div>
        </div>
        <button class="logout-btn" onclick="telegramLogout()" title="Выйти">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
            <path
              d="M17 16L21 12M21 12L17 8M21 12H9M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </button>
      </div>
    <?php else: ?>
      <!-- Пользователь не авторизован -->
      <div class="telegram-auth-prompt">
        <div class="auth-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path
              d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
              fill="currentColor" />
          </svg>
        </div>
        <div class="auth-text">
          <h4>Верификация через Telegram</h4>
          <p>Авторизуйтесь через Telegram, чтобы оставлять отзывы</p>
        </div>
        <!-- Официальный Telegram Login Widget -->
        <div id="telegram-login-widget"></div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Форма добавления отзыва -->
  <div class="review-form-section" id="reviewFormSection" style="display: none;">
    <form class="review-form" id="reviewForm">
      <div class="form-group">
        <label for="reviewRating">Оценка товара:</label>
        <div class="rating-input">
          <input type="radio" id="star1" name="rating" value="1">
          <label for="star1" class="star">★</label>
          <input type="radio" id="star2" name="rating" value="2">
          <label for="star2" class="star">★</label>
          <input type="radio" id="star3" name="rating" value="3">
          <label for="star3" class="star">★</label>
          <input type="radio" id="star4" name="rating" value="4">
          <label for="star4" class="star">★</label>
          <input type="radio" id="star5" name="rating" value="5">
          <label for="star5" class="star">★</label>
        </div>
      </div>

      <div class="form-group">
        <label for="reviewText">Ваш отзыв:</label>
        <textarea id="reviewText" name="text" placeholder="Расскажите о вашем опыте использования товара..." required
          maxlength="1000" rows="4"></textarea>
        <div class="char-counter">
          <span id="reviewCharCount">0</span> / 1000 символов
        </div>
      </div>

      <div class="form-actions">
        <button type="button" class="cancel-btn" onclick="hideReviewForm()">Отмена</button>
        <button type="submit" class="submit-btn" id="submitReview">
          <span class="btn-text">Отправить отзыв</span>
          <span class="btn-loading" style="display: none;">
            <svg class="spinner" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"
                stroke-dasharray="31.416" stroke-dashoffset="31.416">
                <animate attributeName="stroke-dasharray" dur="2s" values="0 31.416;15.708 15.708;0 31.416"
                  repeatCount="indefinite" />
                <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416"
                  repeatCount="indefinite" />
              </circle>
            </svg>
            Отправка...
          </span>
        </button>
      </div>
    </form>
  </div>

  <!-- Кнопка добавления отзыва -->
  <div class="add-review-section" id="addReviewSection">
    <button class="add-review-btn" onclick="showReviewForm()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
          stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      Оставить отзыв
    </button>
  </div>

  <!-- Список отзывов -->
  <div class="reviews-list" id="reviewsList">
    <div class="loading" id="reviewsLoading">
      <svg class="spinner" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="31.416"
          stroke-dashoffset="31.416">
          <animate attributeName="stroke-dasharray" dur="2s" values="0 31.416;15.708 15.708;0 31.416"
            repeatCount="indefinite" />
          <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416" repeatCount="indefinite" />
        </circle>
      </svg>
      Загрузка отзывов...
    </div>
  </div>

  <!-- Кнопка загрузки еще -->
  <div class="load-more-section" id="loadMoreSection" style="display: none;">
    <button class="load-more-btn" onclick="loadMoreReviews()">
      Загрузить еще
    </button>
  </div>
</div>

<style>
  .product-reviews-widget {
    background: transparent;
    border: 1px solid #e1e5e9;
    border-radius: 12px;
    padding: 24px;
    margin: 24px 0;
  }

  .reviews-header {
    margin-bottom: 24px;
  }

  .reviews-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #24292f;
  }

  .reviews-count {
    background: var(--brand-primary);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
  }

  .telegram-auth-section {
    margin-bottom: 24px;
  }

  .telegram-user-info {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 16px;
    background: var(--brand-light);
    border-radius: 8px;
    border: 1px solid var(--brand-gray);
  }

  .user-avatar img {
    border-radius: 50%;
    border: 2px solid var(--brand-primary);
  }

  .user-details {
    flex: 1;
  }

  .user-name {
    font-weight: 600;
    color: #c3d8f2;
  }

  .user-username {
    font-size: 14px;
    color: var(--brand-text-light);
  }

  .logout-btn {
    background: none;
    border: none;
    color: var(--brand-text-light);
    cursor: pointer;
    padding: 8px;
    border-radius: 6px;
    transition: all 0.3s ease;
  }

  .logout-btn:hover {
    background: var(--brand-light);
    color: var(--brand-danger);
  }

  .telegram-auth-prompt {
    text-align: center;
    padding: 24px;
    background: var(--brand-light);
    border-radius: 8px;
    border: 1px solid var(--brand-gray);
  }

  .auth-icon {
    color: var(--brand-primary);
    margin-bottom: 16px;
  }

  .auth-text h4 {
    margin: 0 0 8px 0;
    color: var(--brand-text);
    font-size: 18px;
  }

  .auth-text p {
    margin: 0 0 20px 0;
    color: var(--brand-text-light);
    font-size: 14px;
    line-height: 1.5;
  }

  .add-review-section {
    margin-bottom: 24px;
  }

  .add-review-btn {
    display: flex !important;
    align-items: center;
    gap: 8px;
    background: #6a7e9f !important;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .add-review-btn:hover {
    background: #5a6e8f !important;
    transform: translateY(-2px);
    opacity: 1 !important;
    visibility: visible !important;
  }

  .review-form-section {
    margin-bottom: 24px;
    padding: 20px;
    border-radius: 8px;
  }

  .review-form .form-group {
    margin-bottom: 16px;
  }

  .review-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--brand-text);
  }

  .rating-input {
    display: flex;
    gap: 4px;
    margin-bottom: 16px;
  }

  .rating-input input[type="radio"] {
    display: none;
  }

  .rating-input .star {
    font-size: 24px;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
  }

  .star.filled {
    color: #ffc107;
  }

  .star.empty {
    color: #ddd;
  }

  /* Hover эффект - все звезды до наведенной включительно */
  .rating-input .star:hover,
  .rating-input .star:hover~.star {
    color: #ffc107;
  }

  /* Выбранная звезда */
  .rating-input input[type="radio"]:checked+.star {
    color: #ffc107;
  }

  /* Все звезды после выбранной - делаем их серыми */
  .rating-input input[type="radio"]:checked~.star {
    color: #ddd;
  }

  .review-form textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--brand-gray);
    border-radius: 8px;
    font-size: 16px;
    font-family: inherit;
    resize: vertical;
    min-height: 100px;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
  }

  .review-form textarea:focus {
    outline: none;
    border-color: var(--brand-primary);
  }

  .char-counter {
    text-align: right;
    font-size: 12px;
    color: var(--brand-text-light);
    margin-top: 4px;
  }

  .form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
  }

  .cancel-btn {
    display: inline-block !important;
    background: none;
    border: 1px solid var(--brand-gray);
    color: var(--brand-text);
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .cancel-btn:hover {
    background: var(--brand-light);
    opacity: 1 !important;
    visibility: visible !important;
  }

  .submit-btn {
    display: flex !important;
    align-items: center;
    gap: 8px;
    background: #6a7e9f !important;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .submit-btn:hover {
    background: #5a6e8f !important;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .submit-btn:disabled {
    background: var(--brand-gray);
    cursor: not-allowed;
    opacity: 0.6 !important;
  }

  .reviews-list {
    margin-bottom: 24px;
  }

  .review-item {
    display: flex;
    gap: 12px;
    padding: 16px 0;
    border-bottom: 1px solid var(--brand-gray);
  }

  .review-item:last-child {
    border-bottom: none;
  }

  .review-avatar {
    flex-shrink: 0;
  }

  .review-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid var(--brand-gray);
  }

  .review-content {
    flex: 1;
  }

  .review-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
  }

  .review-author {
    font-weight: 600;
    color: #c3d8f2;
  }

  .review-username {
    color: var(--brand-primary);
    font-size: 14px;
  }

  .review-rating {
    display: flex;
    gap: 2px;
    margin-left: auto;
  }

  .review-rating .star {
    color: #ffc107;
    font-size: 16px;
  }

  .review-rating .star.empty {
    color: #ddd;
  }

  .review-date {
    color: var(--brand-text-light);
    font-size: 12px;
  }

  .review-text {
    color: #24292f;
    line-height: 1.5;
    margin-bottom: 12px;
  }

  .moderator-comment {
    margin: 12px 0;
    padding: 12px;
    background: #f8f9fa;
    border-left: 3px solid #28a745;
    border-radius: 4px;
  }

  .moderator-comment-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    font-weight: 600;
    color: #28a745;
  }

  .moderator-comment-label {
    font-size: 14px;
  }

  .moderator-comment-text {
    color: #495057;
    line-height: 1.5;
    font-size: 14px;
  }

  .user-already-reviewed {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 24px;
  }

  .already-reviewed-icon {
    flex-shrink: 0;
    width: 48px;
    height: 48px;
    background: #28a745;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
  }

  .already-reviewed-text h4 {
    margin: 0 0 8px 0;
    color: #28a745;
    font-size: 18px;
    font-weight: 600;
  }

  .already-reviewed-text p {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
    line-height: 1.5;
  }

  .review-actions {
    display: flex;
    gap: 16px;
    align-items: center;
  }

  .like-btn {
    display: flex;
    align-items: center;
    gap: 4px;
    background: none;
    border: none;
    color: var(--brand-text-light);
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.3s ease;
    font-size: 14px;
  }

  .like-btn:hover {
    background: var(--brand-light);
  }

  .like-btn.liked {
    color: var(--brand-danger);
  }

  .report-btn {
    background: none;
    border: none;
    color: var(--brand-text-light);
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.3s ease;
    font-size: 12px;
  }

  .report-btn:hover {
    background: var(--brand-light);
    color: var(--brand-danger);
  }

  .loading {
    text-align: center;
    padding: 40px;
    color: var(--brand-text-light);
  }

  .load-more-section {
    text-align: center;
  }

  .load-more-btn {
    background: var(--brand-light);
    border: 1px solid var(--brand-gray);
    color: var(--brand-text);
    padding: 12px 24px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .load-more-btn:hover {
    background: var(--brand-gray);
  }

  .spinner {
    width: 20px;
    height: 20px;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    from {
      transform: rotate(0deg);
    }

    to {
      transform: rotate(360deg);
    }
  }

  /* Мобильные стили */
  @media (max-width: 768px) {
    .product-reviews-widget {
      padding: 16px;
      margin: 16px 0;
    }

    .reviews-title {
      font-size: 18px;
    }

    .telegram-user-info {
      gap: 12px;
      padding: 12px;
    }

    .user-avatar img {
      width: 35px;
      height: 35px;
    }

    .review-form-section {
      padding: 16px;
    }

    .form-actions {
      flex-direction: column;
    }

    .cancel-btn,
    .submit-btn {
      width: 100%;
      justify-content: center;
    }

    .review-item {
      gap: 10px;
      padding: 12px 0;
    }

    .review-avatar img {
      width: 35px;
      height: 35px;
    }

    .review-actions {
      flex-wrap: wrap;
      gap: 12px;
    }
  }

  /* Темная тема для отзывов */
  .dark-mode .product-reviews-widget {
    background: #0d1117 !important;
    border-color: #30363d !important;
    color: #e6edf3 !important;
    margin: 0 !important;
  }

  .dark-mode .reviews-title {
    color: #ffffff !important;
  }

  .dark-mode .telegram-user-info {
    background: #21262d !important;
    border-color: #30363d !important;
  }

  .dark-mode .telegram-user-info h4 {
    color: #e6edf3 !important;
  }

  .dark-mode .telegram-user-info p {
    color: #8b949e !important;
  }

  .dark-mode .logout-btn {
    color: #8b949e !important;
  }

  .dark-mode .logout-btn:hover {
    background: #30363d !important;
    color: #f85149 !important;
  }

  .dark-mode .auth-prompt {
    background: #21262d !important;
    border-color: #30363d !important;
  }

  .dark-mode .auth-prompt h4 {
    color: #e6edf3 !important;
  }

  .dark-mode .auth-prompt p {
    color: #8b949e !important;
  }

  .dark-mode .review-form textarea {
    background: #0d1117 !important;
    border-color: #30363d !important;
    color: #e6edf3 !important;
  }

  .dark-mode .review-form textarea::placeholder {
    color: #6e7681 !important;
  }

  .dark-mode .char-count {
    color: #8b949e !important;
  }

  .dark-mode .cancel-btn {
    background: #21262d !important;
    border-color: #30363d !important;
    color: #e6edf3 !important;
  }

  .dark-mode .cancel-btn:hover {
    background: #30363d !important;
  }

  .dark-mode .review-item {
    background: #21262d !important;
    border-color: #30363d !important;
  }

  .dark-mode .review-author h4 {
    color: #ffffff !important;
  }

  .dark-mode .review-author p {
    color: #8b949e !important;
  }

  .dark-mode .review-text {
    color: #ffffff !important;
  }

  .dark-mode .review-meta {
    color: #6e7681 !important;
  }

  .dark-mode .like-btn,
  .dark-mode .report-btn {
    color: #8b949e !important;
  }

  .dark-mode .like-btn:hover,
  .dark-mode .report-btn:hover {
    background: #30363d !important;
  }

  .dark-mode .load-more-btn {
    background: #21262d !important;
    border-color: #30363d !important;
    color: #e6edf3 !important;
  }

  .dark-mode .load-more-btn:hover {
    background: #30363d !important;
  }

  .dark-mode .empty-state {
    color: #8b949e !important;
  }

  .dark-mode .loading-spinner {
    border-color: #30363d !important;
    border-top-color: #58a6ff !important;
  }
</style>

<script>
  // Глобальные переменные
  let currentPage = 1;
  let isLoading = false;
  let hasMoreReviews = true;

  // Инициализация виджета отзывов
  document.addEventListener('DOMContentLoaded', function () {
    const widget = document.querySelector('.product-reviews-widget');
    if (!widget) return;

    // Инициализируем Telegram виджет
    initTelegramWidget();

    // Проверяем авторизацию и показываем соответствующие элементы
    checkAuthStatus();

    // Загружаем отзывы
    loadReviews();

    // Инициализируем форму
    initReviewForm();
  });

  // Инициализация Telegram виджета
  function initTelegramWidget() {
    const widgetContainer = document.getElementById('telegram-login-widget');
    if (widgetContainer && !document.querySelector('.telegram-user-info')) {
      // Создаем виджет только если пользователь не авторизован
      const script = document.createElement('script');
      script.async = true;
      script.src = 'https://telegram.org/js/telegram-widget.js?22';
      script.setAttribute('data-telegram-login', 'Cherkas_psybot');
      script.setAttribute('data-size', 'medium');
      script.setAttribute('data-onauth', 'onTelegramAuth(user)');
      script.setAttribute('data-request-access', 'write');
      widgetContainer.appendChild(script);
    }
  }

  // Проверка статуса авторизации
  function checkAuthStatus() {
    const authSection = document.getElementById('telegramAuthSection');
    const addReviewSection = document.getElementById('addReviewSection');
    const reviewFormSection = document.getElementById('reviewFormSection');

    // Проверяем, есть ли данные пользователя в сессии
    const isAuthenticated = authSection.querySelector('.telegram-user-info') !== null;

    if (isAuthenticated) {
      // Проверяем, не оставлял ли пользователь уже отзыв
      checkExistingReview();
    } else {
      addReviewSection.style.display = 'none';
      reviewFormSection.style.display = 'none';
    }
  }

  // Проверка существующего отзыва пользователя
  async function checkExistingReview() {
    const widget = document.querySelector('.product-reviews-widget');
    const productId = widget.dataset.productId;
    const addReviewSection = document.getElementById('addReviewSection');

    try {
      const response = await fetch(`/api/product-reviews.php?action=check_user_review&product_id=${productId}`);
      const result = await response.json();

      if (result.success) {
        if (result.has_review) {
          // Пользователь уже оставлял отзыв
          addReviewSection.innerHTML = `
            <div class="user-already-reviewed">
              <div class="already-reviewed-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
              <div class="already-reviewed-text">
                <h4>Спасибо за отзыв!</h4>
                <p>Вы уже оставили отзыв на этот товар. Он будет опубликован после модерации.</p>
              </div>
            </div>
          `;
        } else {
          // Пользователь может оставить отзыв
          addReviewSection.style.display = 'block';
        }
      }
    } catch (error) {
      console.error('Error checking existing review:', error);
      // В случае ошибки показываем кнопку добавления отзыва
      addReviewSection.style.display = 'block';
    }
  }

  // Загрузка отзывов
  async function loadReviews(page = 1) {
    if (isLoading) return;

    isLoading = true;
    const widget = document.querySelector('.product-reviews-widget');
    const productId = widget.dataset.productId;

    try {
      const response = await fetch(`/api/product-reviews.php?action=list&product_id=${productId}&page=${page}&limit=10`);
      const result = await response.json();

      if (result.success) {
        if (page === 1) {
          document.getElementById('reviewsList').innerHTML = '';
        }

        displayReviews(result.data);
        updateReviewsCount();

        // Проверяем, есть ли еще отзывы
        hasMoreReviews = result.data.pagination.has_more;
        const loadMoreSection = document.getElementById('loadMoreSection');
        loadMoreSection.style.display = hasMoreReviews ? 'block' : 'none';

        currentPage = page;
      } else {
        throw new Error(result.error);
      }
    } catch (error) {
      console.error('Error loading reviews:', error);
      showError('Ошибка загрузки отзывов');
    } finally {
      isLoading = false;
      const loadingElement = document.getElementById('reviewsLoading');
      if (loadingElement) {
        loadingElement.style.display = 'none';
      }
    }
  }

  // Отображение отзывов
  function displayReviews(data) {
    const reviewsList = document.getElementById('reviewsList');

    // Проверяем, что data существует и содержит reviews
    if (!data || !data.reviews) {
      console.error('Invalid data format:', data);
      reviewsList.innerHTML = '<div class="no-reviews">Ошибка загрузки отзывов</div>';
      return;
    }

    const reviews = data.reviews;

    if (!Array.isArray(reviews)) {
      console.error('Reviews is not an array:', reviews);
      reviewsList.innerHTML = '<div class="no-reviews">Ошибка формата данных</div>';
      return;
    }

    if (reviews.length === 0 && currentPage === 1) {
      reviewsList.innerHTML = '<div class="no-reviews">Пока нет отзывов. Будьте первым!</div>';
      return;
    }

    reviews.forEach(review => {
      const reviewElement = createReviewElement(review);
      reviewsList.appendChild(reviewElement);
    });
  }

  // Создание элемента отзыва
  function createReviewElement(review) {
    const div = document.createElement('div');
    div.className = 'review-item';
    div.dataset.reviewId = review.id;

    const avatar = review.telegram_avatar || `https://via.placeholder.com/40x40/6a7e9f/ffffff?text=${review.telegram_username.charAt(0).toUpperCase()}`;

    // Создаем звезды рейтинга
    let starsHtml = '';
    const rating = parseInt(review.rating) || 0; // Преобразуем в число

    // Логируем для отладки
    console.log('Creating review element:', {
      reviewId: review.id,
      ratingRaw: review.rating,
      ratingParsed: rating,
      ratingType: typeof review.rating,
      telegramAvatar: review.telegram_avatar,
      telegramUsername: review.telegram_username
    });

    for (let i = 1; i <= 5; i++) {
      const starClass = i <= rating ? 'star filled' : 'star empty';
      starsHtml += `<span class="${starClass}">★</span>`;
    }

    div.innerHTML = `
        <div class="review-avatar">
            <img src="${avatar}" alt="${review.telegram_first_name}">
        </div>
        <div class="review-content">
            <div class="review-header">
                <span class="review-author">${review.telegram_first_name} ${review.telegram_last_name}</span>
                <span class="review-username">@${review.telegram_username}</span>
                <div class="review-rating">
                    ${starsHtml}
                </div>
                <span class="review-date">${review.created_at_formatted}</span>
            </div>
            <div class="review-text">${escapeHtml(review.text)}</div>
            ${review.moderator_comment ? `
            <div class="moderator-comment">
                <div class="moderator-comment-header">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="moderator-comment-label">Комментарий модератора:</span>
                </div>
                <div class="moderator-comment-text">${escapeHtml(review.moderator_comment)}</div>
            </div>
            ` : ''}
            <div class="review-actions">
                <button class="like-btn ${review.user_liked ? 'liked' : ''}" onclick="toggleLike(${review.id})">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.5783 8.50903 2.9987 7.05 2.9987C5.59096 2.9987 4.19169 3.5783 3.16 4.61C2.1283 5.6417 1.5487 7.04097 1.5487 8.5C1.5487 9.95903 2.1283 11.3583 3.16 12.39L12 21.23L20.84 12.39C21.351 11.8792 21.7563 11.2728 22.0329 10.6053C22.3095 9.93789 22.4518 9.22248 22.4518 8.5C22.4518 7.77752 22.3095 7.06211 22.0329 6.39467C21.7563 5.72723 21.351 5.1208 20.84 4.61Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="likes-count">${review.likes_count}</span>
                </button>
                <button class="report-btn" onclick="reportReview(${review.id})">Пожаловаться</button>
            </div>
        </div>
    `;

    return div;
  }

  // Обновление счетчика отзывов
  async function updateReviewsCount() {
    const widget = document.querySelector('.product-reviews-widget');
    const productId = widget.dataset.productId;

    try {
      const response = await fetch(`/api/product-reviews.php?action=count&product_id=${productId}`);
      const result = await response.json();

      if (result.success) {
        const reviewsCountElement = document.getElementById('reviewsCount');
        if (reviewsCountElement) {
          reviewsCountElement.textContent = result.data.count;
        }
      }
    } catch (error) {
      console.error('Error updating reviews count:', error);
    }
  }

  // Функция для обновления отображения звезд
  function updateStarDisplay(selectedInput) {
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const selectedValue = parseInt(selectedInput.value);

    ratingInputs.forEach(input => {
      const inputValue = parseInt(input.value);
      const star = input.nextElementSibling;

      // Логика: если значение звезды меньше или равно выбранному значению, то она должна быть желтой
      if (inputValue <= selectedValue) {
        star.style.color = '#ffc107';
      } else {
        star.style.color = '#ddd';
      }
    });
  }

  // Инициализация формы отзыва
  function initReviewForm() {
    const form = document.getElementById('reviewForm');
    const textarea = document.getElementById('reviewText');
    const charCount = document.getElementById('reviewCharCount');

    // Счетчик символов
    textarea.addEventListener('input', function () {
      const length = this.value.length;
      charCount.textContent = length;

      if (length > 900) {
        charCount.style.color = '#dc3545';
      } else if (length > 700) {
        charCount.style.color = '#ffc107';
      } else {
        charCount.style.color = 'var(--brand-text-light)';
      }
    });

    // Обработка звезд рейтинга
    const ratingInputs = form.querySelectorAll('input[name="rating"]');
    ratingInputs.forEach(input => {
      input.addEventListener('change', function () {
        updateStarDisplay(this);
      });
    });

    // Hover эффект для звезд
    const stars = form.querySelectorAll('.star');
    stars.forEach(star => {
      star.addEventListener('mouseenter', function () {
        const input = this.previousElementSibling;
        const inputValue = parseInt(input.value);

        ratingInputs.forEach(ratingInput => {
          const ratingValue = parseInt(ratingInput.value);
          const ratingStar = ratingInput.nextElementSibling;

          if (ratingValue <= inputValue) {
            ratingStar.style.color = '#ffc107';
          } else {
            ratingStar.style.color = '#ddd';
          }
        });
      });

      star.addEventListener('mouseleave', function () {
        // Восстанавливаем состояние на основе выбранного рейтинга
        const selectedInput = form.querySelector('input[name="rating"]:checked');
        if (selectedInput) {
          updateStarDisplay(selectedInput);
        } else {
          // Если ничего не выбрано, делаем все звезды серыми
          ratingInputs.forEach(ratingInput => {
            const ratingStar = ratingInput.nextElementSibling;
            ratingStar.style.color = '#ddd';
          });
        }
      });
    });

    // Обработка отправки формы
    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      const rating = form.querySelector('input[name="rating"]:checked');
      const text = textarea.value.trim();

      if (!rating) {
        showError('Пожалуйста, выберите оценку товара');
        return;
      }

      if (text.length < 10) {
        showError('Отзыв должен содержать минимум 10 символов');
        return;
      }

      const submitBtn = document.getElementById('submitReview');
      const btnText = submitBtn.querySelector('.btn-text');
      const btnLoading = submitBtn.querySelector('.btn-loading');

      btnText.style.display = 'none';
      btnLoading.style.display = 'inline-flex';
      submitBtn.disabled = true;

      try {
        const widget = document.querySelector('.product-reviews-widget');
        const productId = widget.dataset.productId;

        const ratingValue = parseInt(rating.value);
        console.log('Отправка отзыва:', { productId, rating: ratingValue, text });

        if (!productId || productId.trim() === '') {
          throw new Error('Product ID is required');
        }

        const response = await fetch('/api/product-reviews.php?action=add', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            product_id: productId,
            rating: ratingValue,
            text: text
          })
        });

        const result = await response.json();

        if (result.success) {
          showSuccess('Отзыв отправлен на модерацию');
          form.reset();
          charCount.textContent = '0';
          hideReviewForm();
        } else {
          throw new Error(result.error);
        }
      } catch (error) {
        console.error('Error submitting review:', error);
        showError('Ошибка при отправке отзыва');
      } finally {
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
        submitBtn.disabled = false;
      }
    });
  }

  // Показать форму отзыва
  function showReviewForm() {
    document.getElementById('addReviewSection').style.display = 'none';
    document.getElementById('reviewFormSection').style.display = 'block';
    document.getElementById('reviewText').focus();
  }

  // Скрыть форму отзыва
  function hideReviewForm() {
    document.getElementById('reviewFormSection').style.display = 'none';
    document.getElementById('addReviewSection').style.display = 'block';
  }

  // Загрузить еще отзывы
  function loadMoreReviews() {
    if (!hasMoreReviews || isLoading) return;
    loadReviews(currentPage + 1);
  }

  // Лайк/анлайк отзыва
  async function toggleLike(reviewId) {
    try {
      console.log('Attempting to like review:', reviewId);

      const response = await fetch('/api/product-reviews.php?action=like', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          review_id: reviewId
        })
      });

      console.log('Response status:', response.status);
      const result = await response.json();
      console.log('Response result:', result);

      if (result.success) {
        const reviewElement = document.querySelector(`[data-review-id="${reviewId}"]`);
        const likeBtn = reviewElement.querySelector('.like-btn');
        const likesCount = reviewElement.querySelector('.likes-count');

        likesCount.textContent = result.data.likes_count;

        if (result.data.action === 'liked') {
          likeBtn.classList.add('liked');
        } else {
          likeBtn.classList.remove('liked');
        }
      } else {
        throw new Error(result.error);
      }
    } catch (error) {
      console.error('Error toggling like:', error);
      showError('Ошибка при обработке лайка: ' + error.message);
    }
  }

  // Пожаловаться на отзыв
  async function reportReview(reviewId) {
    const reason = prompt('Укажите причину жалобы (необязательно):');

    try {
      console.log('Attempting to report review:', reviewId);

      const response = await fetch('/api/product-reviews.php?action=report', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          review_id: reviewId,
          reason: reason || ''
        })
      });

      console.log('Report response status:', response.status);
      const result = await response.json();
      console.log('Report response result:', result);

      if (result.success) {
        showSuccess('Жалоба отправлена');
      } else {
        throw new Error(result.error);
      }
    } catch (error) {
      console.error('Error reporting review:', error);
      showError('Ошибка при отправке жалобы: ' + error.message);
    }
  }

  // Выход из Telegram
  function telegramLogout() {
    if (confirm('Вы уверены, что хотите выйти?')) {
      fetch('/api/telegram-logout.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        }
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            location.reload();
          } else {
            alert('Ошибка при выходе из системы');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Ошибка при выходе из системы');
        });
    }
  }

  // Вспомогательные функции
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function showSuccess(message) {
    const notification = document.createElement('div');
    notification.className = 'notification success';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #d4edda;
        color: #155724;
        padding: 20px 30px;
        border-radius: 12px;
        z-index: 99999;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        border: 2px solid #c3e6cb;
        font-size: 16px;
        font-weight: 600;
        text-align: center;
        min-width: 300px;
        max-width: 90vw;
        word-wrap: break-word;
    `;

    document.body.appendChild(notification);

    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s ease-in-out';

    setTimeout(() => {
      notification.style.opacity = '1';
    }, 10);

    setTimeout(() => {
      notification.style.opacity = '0';
      setTimeout(() => {
        notification.remove();
      }, 3000);
    }, 3000);
  }

  function showError(message) {
    const notification = document.createElement('div');
    notification.className = 'notification error';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #f8d7da;
        color: #721c24;
        padding: 20px 30px;
        border-radius: 12px;
        z-index: 99999;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        border: 2px solid #f5c6cb;
        font-size: 16px;
        font-weight: 600;
        text-align: center;
        min-width: 300px;
        max-width: 90vw;
        word-wrap: break-word;
    `;

    document.body.appendChild(notification);

    notification.style.opacity = '0';
    notification.style.transition = 'opacity 0.3s ease-in-out';

    setTimeout(() => {
      notification.style.opacity = '1';
    }, 10);

    setTimeout(() => {
      notification.style.opacity = '0';
      setTimeout(() => {
        notification.remove();
      }, 5000);
    }, 3000);
  }
</script>