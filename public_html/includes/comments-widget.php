<?php
/**
 * Виджет комментариев с Telegram авторизацией
 * Поддерживает комментарии к статьям и товарам
 */

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$contentType = $contentType ?? 'article';
$contentId = $contentId ?? '';
$showTitle = $showTitle ?? true;

// Отладочная информация
if (empty($contentId)) {
  error_log("Comments widget: contentId is empty. contentType: " . $contentType);
}
?>

<div class="comments-widget" data-content-type="<?= htmlspecialchars($contentType) ?>"
  data-content-id="<?= htmlspecialchars($contentId) ?>">
  <?php if ($showTitle): ?>
    <div class="comments-header">
      <h3 class="comments-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path
            d="M21 15C21 15.5304 20.7893 16.0391 20.4142 16.4142C20.0391 16.7893 19.5304 17 19 17H7L3 21V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H19C19.5304 3 20.0391 3.21071 20.4142 3.58579C20.7893 3.96086 21 4.46957 21 5V15Z"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        Комментарии
        <span class="comments-count" id="commentsCount">0</span>
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
          <p>Авторизуйтесь через Telegram, чтобы оставлять комментарии</p>
        </div>
        <!-- Официальный Telegram Login Widget -->
        <div id="telegram-login-widget"></div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Форма добавления комментария -->
  <div class="comment-form-section" id="commentFormSection" style="display: none;">
    <form class="comment-form" id="commentForm">
      <div class="form-group">
        <textarea id="commentText" name="text" placeholder="Напишите ваш комментарий..." required maxlength="1000"
          rows="3"></textarea>
        <div class="char-counter">
          <span id="commentCharCount">0</span> / 1000 символов
        </div>
      </div>
      <div class="form-actions">
        <button type="button" class="cancel-btn" onclick="hideCommentForm()">Отмена</button>
        <button type="submit" class="submit-btn" id="submitComment">
          <span class="btn-text">Отправить</span>
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

  <!-- Кнопка добавления комментария -->
  <div class="add-comment-section" id="addCommentSection">
    <button class="add-comment-btn" onclick="showCommentForm()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
        <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round"
          stroke-linejoin="round" />
      </svg>
      Добавить комментарий
    </button>
  </div>

  <!-- Список комментариев -->
  <div class="comments-list" id="commentsList">
    <div class="loading" id="commentsLoading">
      <svg class="spinner" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="31.416"
          stroke-dashoffset="31.416">
          <animate attributeName="stroke-dasharray" dur="2s" values="0 31.416;15.708 15.708;0 31.416"
            repeatCount="indefinite" />
          <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416" repeatCount="indefinite" />
        </circle>
      </svg>
      Загрузка комментариев...
    </div>
  </div>

  <!-- Кнопка загрузки еще -->
  <div class="load-more-section" id="loadMoreSection" style="display: none;">
    <button class="load-more-btn" onclick="loadMoreComments()">
      Загрузить еще
    </button>
  </div>
</div>

<style>
  .comments-section {
    background: #0d1117 !important;
  }

  .comments-widget {
    background: #0d1117;
    border: 1px solid #30363d;
    border-radius: 12px;
    padding: 24px;
  }

  .comments-header {
    margin-bottom: 24px;
  }

  .comments-title {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
    font-size: 20px;
    font-weight: 600;
    color: #24292f;
  }

  .comments-count {
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
    color: var(--brand-text);
    margin-bottom: 2px;
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

  .telegram-auth-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #0088cc;
    color: white;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .telegram-auth-btn:hover {
    background: #006699;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 136, 204, 0.3);
  }

  .add-comment-section {
    margin-bottom: 24px;
  }

  .add-comment-btn {
    display: flex !important;
    align-items: center;
    gap: 8px;
    background: var(--brand-primary);
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

  .add-comment-btn:hover {
    background: #d2afa0;
    transform: translateY(-2px);
    opacity: 1 !important;
    visibility: visible !important;
  }

  .comment-form-section {
    margin-bottom: 24px;
    padding: 20px;
    border-radius: 8px;
  }

  .comment-form .form-group {
    margin-bottom: 16px;
  }

  .comment-form textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--brand-gray);
    border-radius: 8px;
    font-size: 16px;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
  }

  .comment-form textarea:focus {
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
    background: var(--brand-primary);
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
    background: #d2afa0;
    opacity: 1 !important;
    visibility: visible !important;
  }

  .submit-btn:disabled {
    background: var(--brand-gray);
    cursor: not-allowed;
    opacity: 0.6 !important;
  }

  .comments-list {
    margin-bottom: 24px;
  }

  .comment-item {
    display: flex;
    gap: 12px;
    padding: 16px 0;
    border-bottom: 1px solid var(--brand-gray);
  }

  .comment-item:last-child {
    border-bottom: none;
  }

  .comment-avatar {
    flex-shrink: 0;
  }

  .comment-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid var(--brand-gray);
  }

  .comment-content {
    flex: 1;
  }

  .comment-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
  }

  .comment-author {
    font-weight: 600;
    color: #c3d8f2;
  }

  .comment-username {
    color: var(--brand-primary);
    font-size: 14px;
  }

  .comment-date {
    color: var(--brand-text-light);
    font-size: 12px;
  }

  .comment-text {
    color: #24292f;
    line-height: 1.5;
    margin-bottom: 12px;
  }

  .comment-actions {
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
    .comments-widget {
      padding: 16px;
      margin: 16px 0;
    }

    .comments-title {
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

    .comment-form-section {
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

    .comment-item {
      gap: 10px;
      padding: 12px 0;
    }

    .comment-avatar img {
      width: 35px;
      height: 35px;
    }

    .comment-actions {
      flex-wrap: wrap;
      gap: 12px;
    }
  }

  /* Светлая тема для комментариев */
  .comments-section {
    background: transparent !important;
  }

  .comments-widget {
    background: transparent;
    border-color: #e1e5e9;
    color: #24292f;
    margin: 24px 0;
  }

  /* Темная тема для комментариев */
  .dark-mode .comments-section {
    background: #0d1117 !important;
  }

  .dark-mode .comments-widget {
    background: #0d1117 !important;
    border-color: #30363d !important;
    color: #e6edf3 !important;
    margin: 0 !important;
  }

  .dark-mode .comments-title {
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

  .dark-mode .comment-form {
    background: #21262d !important;
    border-color: #30363d !important;
  }

  .dark-mode .comment-form textarea {
    background: #0d1117 !important;
    border-color: #30363d !important;
    color: #e6edf3 !important;
  }

  .dark-mode .comment-form textarea::placeholder {
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

  .dark-mode .comment-item {
    background: #21262d !important;
    border-color: #30363d !important;
  }

  .dark-mode .comment-author h4 {
    color: #ffffff !important;
  }

  .dark-mode .comment-author p {
    color: #8b949e !important;
  }

  .dark-mode .comment-text {
    color: #ffffff !important;
  }

  .dark-mode .comment-meta {
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

  /* Темная тема для уведомлений */
  .dark-mode .notification.success {
    background: #1e4d2b !important;
    color: #a8d5a8 !important;
    border-color: #2d5a3d !important;
  }

  .dark-mode .notification.error {
    background: #4d1e1e !important;
    color: #d5a8a8 !important;
    border-color: #5a2d2d !important;
  }

  .
</style>

<script>
  // Глобальные переменные
  let currentPage = 1;
  let isLoading = false;
  let hasMoreComments = true;

  // Инициализация виджета комментариев
  document.addEventListener('DOMContentLoaded', function () {
    const widget = document.querySelector('.comments-widget');
    if (!widget) return;

    // Инициализируем Telegram виджет
    initTelegramWidget();

    // Проверяем авторизацию и показываем соответствующие элементы
    checkAuthStatus();

    // Загружаем комментарии
    loadComments();

    // Инициализируем форму
    initCommentForm();
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
    const addCommentSection = document.getElementById('addCommentSection');
    const commentFormSection = document.getElementById('commentFormSection');

    // Проверяем, есть ли данные пользователя в сессии
    const isAuthenticated = authSection.querySelector('.telegram-user-info') !== null;

    if (isAuthenticated) {
      addCommentSection.style.display = 'block';
    } else {
      addCommentSection.style.display = 'none';
      commentFormSection.style.display = 'none';
    }
  }

  // Загрузка комментариев
  async function loadComments(page = 1) {
    if (isLoading) return;

    isLoading = true;
    const widget = document.querySelector('.comments-widget');
    const contentType = widget.dataset.contentType;
    const contentId = widget.dataset.contentId;

    try {
      const response = await fetch(`/api/comments.php?action=list&content_type=${contentType}&content_id=${contentId}&page=${page}&limit=10`);
      const result = await response.json();

      if (result.success) {
        if (page === 1) {
          document.getElementById('commentsList').innerHTML = '';
        }

        displayComments(result.data);
        updateCommentsCount();

        // Проверяем, есть ли еще комментарии
        hasMoreComments = result.pagination.has_more;
        const loadMoreSection = document.getElementById('loadMoreSection');
        loadMoreSection.style.display = hasMoreComments ? 'block' : 'none';

        currentPage = page;
      } else {
        throw new Error(result.error);
      }
    } catch (error) {
      console.error('Error loading comments:', error);
      showError('Ошибка загрузки комментариев');
    } finally {
      isLoading = false;
      const loadingElement = document.getElementById('commentsLoading');
      if (loadingElement) {
        loadingElement.style.display = 'none';
      }
    }
  }

  // Отображение комментариев
  function displayComments(comments) {
    const commentsList = document.getElementById('commentsList');

    if (comments.length === 0 && currentPage === 1) {
      commentsList.innerHTML = '<div class="no-comments">Пока нет комментариев. Будьте первым!</div>';
      return;
    }

    comments.forEach(comment => {
      const commentElement = createCommentElement(comment);
      commentsList.appendChild(commentElement);
    });
  }

  // Создание элемента комментария
  function createCommentElement(comment) {
    const div = document.createElement('div');
    div.className = 'comment-item';
    div.dataset.commentId = comment.id;

    const avatar = comment.telegram_avatar || `https://via.placeholder.com/40x40/6a7e9f/ffffff?text=${comment.telegram_username.charAt(0).toUpperCase()}`;

    div.innerHTML = `
        <div class="comment-avatar">
            <img src="${avatar}" alt="${comment.telegram_first_name}">
        </div>
        <div class="comment-content">
            <div class="comment-header">
                <span class="comment-author">${comment.telegram_first_name} ${comment.telegram_last_name}</span>
                <span class="comment-username">@${comment.telegram_username}</span>
                <span class="comment-date">${comment.created_at_formatted}</span>
            </div>
            <div class="comment-text">${escapeHtml(comment.text)}</div>
            <div class="comment-actions">
                <button class="like-btn ${comment.user_liked ? 'liked' : ''}" onclick="toggleLike(${comment.id})">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.5783 8.50903 2.9987 7.05 2.9987C5.59096 2.9987 4.19169 3.5783 3.16 4.61C2.1283 5.6417 1.5487 7.04097 1.5487 8.5C1.5487 9.95903 2.1283 11.3583 3.16 12.39L12 21.23L20.84 12.39C21.351 11.8792 21.7563 11.2728 22.0329 10.6053C22.3095 9.93789 22.4518 9.22248 22.4518 8.5C22.4518 7.77752 22.3095 7.06211 22.0329 6.39467C21.7563 5.72723 21.351 5.1208 20.84 4.61Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="likes-count">${comment.likes_count}</span>
                </button>
                <button class="report-btn" onclick="reportComment(${comment.id})">Пожаловаться</button>
            </div>
        </div>
    `;

    return div;
  }

  // Обновление счетчика комментариев
  async function updateCommentsCount() {
    const widget = document.querySelector('.comments-widget');
    const contentType = widget.dataset.contentType;
    const contentId = widget.dataset.contentId;

    try {
      const response = await fetch(`/api/comments.php?action=count&content_type=${contentType}&content_id=${contentId}`);
      const result = await response.json();

      if (result.success) {
        document.getElementById('commentsCount').textContent = result.count;
      }
    } catch (error) {
      console.error('Error updating comments count:', error);
    }
  }

  // Инициализация формы комментария
  function initCommentForm() {
    const form = document.getElementById('commentForm');
    const textarea = document.getElementById('commentText');
    const charCount = document.getElementById('commentCharCount');

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

    // Обработка отправки формы
    form.addEventListener('submit', async function (e) {
      e.preventDefault();

      const text = textarea.value.trim();
      if (text.length < 3) {
        showError('Комментарий должен содержать минимум 3 символа');
        return;
      }

      const submitBtn = document.getElementById('submitComment');
      const btnText = submitBtn.querySelector('.btn-text');
      const btnLoading = submitBtn.querySelector('.btn-loading');

      btnText.style.display = 'none';
      btnLoading.style.display = 'inline-flex';
      submitBtn.disabled = true;

      try {
        const widget = document.querySelector('.comments-widget');
        const contentType = widget.dataset.contentType;
        const contentId = widget.dataset.contentId;

        console.log('Отправка комментария:', { contentType, contentId, text });

        // Проверяем, что contentId не пустой
        if (!contentId || contentId.trim() === '') {
          throw new Error('Content ID is required');
        }

        const response = await fetch('/api/comments.php?action=add', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            content_type: contentType,
            content_id: contentId,
            text: text
          })
        });

        const result = await response.json();

        if (result.success) {
          showSuccess('Комментарий отправлен на модерацию');
          textarea.value = '';
          charCount.textContent = '0';
          hideCommentForm();
          // Не перезагружаем комментарии сразу, так как комментарий на модерации
          // loadComments(1); // Перезагружаем комментарии
        } else {
          throw new Error(result.error);
        }
      } catch (error) {
        console.error('Error submitting comment:', error);
        showError('Ошибка при отправке комментария');
      } finally {
        btnText.style.display = 'inline';
        btnLoading.style.display = 'none';
        submitBtn.disabled = false;
      }
    });
  }

  // Показать форму комментария
  function showCommentForm() {
    document.getElementById('addCommentSection').style.display = 'none';
    document.getElementById('commentFormSection').style.display = 'block';
    document.getElementById('commentText').focus();
  }

  // Скрыть форму комментария
  function hideCommentForm() {
    document.getElementById('commentFormSection').style.display = 'none';
    document.getElementById('addCommentSection').style.display = 'block';
  }

  // Загрузить еще комментарии
  function loadMoreComments() {
    if (!hasMoreComments || isLoading) return;
    loadComments(currentPage + 1);
  }

  // Лайк/анлайк комментария
  async function toggleLike(commentId) {
    try {
      const response = await fetch('/api/comments.php?action=like', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          comment_id: commentId
        })
      });

      const result = await response.json();

      if (result.success) {
        const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
        const likeBtn = commentElement.querySelector('.like-btn');
        const likesCount = commentElement.querySelector('.likes-count');

        likesCount.textContent = result.likes_count;

        if (result.action === 'liked') {
          likeBtn.classList.add('liked');
        } else {
          likeBtn.classList.remove('liked');
        }
      } else {
        throw new Error(result.error);
      }
    } catch (error) {
      console.error('Error toggling like:', error);
      showError('Ошибка при обработке лайка');
    }
  }

  // Пожаловаться на комментарий
  async function reportComment(commentId) {
    const reason = prompt('Укажите причину жалобы (необязательно):');

    try {
      const response = await fetch('/api/comments.php?action=report', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          comment_id: commentId,
          reason: reason || ''
        })
      });

      const result = await response.json();

      if (result.success) {
        showSuccess('Жалоба отправлена');
      } else {
        throw new Error(result.error);
      }
    } catch (error) {
      console.error('Error reporting comment:', error);
      showError('Ошибка при отправке жалобы');
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
    // Улучшенное уведомление об успехе
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

    // Добавляем анимацию появления
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
    // Улучшенное уведомление об ошибке
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

    // Добавляем анимацию появления
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