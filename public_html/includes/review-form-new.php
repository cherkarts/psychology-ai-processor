<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// CSRF токен больше не используется
?>

<div class="review-form-container" id="reviewFormContainer"
  style="display: none !important; visibility: hidden !important; opacity: 0 !important;">
  <div class="review-form-header">
    <h3>Оставить отзыв</h3>
    <p>Поделитесь своим опытом работы с психологом Денисом Черкасом</p>
    <button type="button" class="close-form-btn"
      onclick="console.log('🔴 Кнопка закрытия нажата'); hideReviewForm(); setTimeout(() => { if(document.getElementById('reviewFormContainer') && document.getElementById('reviewFormContainer').style.display !== 'none') { console.log('🔴 Форма не закрылась, используем принудительное удаление'); forceHideReviewForm(); } }, 200);">
      <svg viewBox="0 0 24 24" fill="currentColor">
        <path
          d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
      </svg>
    </button>
  </div>

  <form class="review-form" id="reviewForm" action="#" enctype="multipart/form-data" novalidate data-no-validate>
    <!-- CSRF токен больше не используется -->
    <input type="hidden" name="website" style="display: none;"> <!-- Honeypot -->

    <div class="form-group">
      <label for="reviewName">Ваше имя *</label>
      <input type="text" id="reviewName" name="name" required maxlength="100" autocomplete="off">
    </div>

    <div class="form-group">
      <label for="reviewAge">Возраст (необязательно)</label>
      <input type="text" id="reviewAge" name="age" autocomplete="off" placeholder="Укажите возраст">
    </div>

    <div class="form-group">
      <label for="reviewRating">Оценка *</label>
      <div class="rating-input">
        <div class="stars">
          <input type="radio" id="star5" name="rating" value="5" required>
          <label for="star5">★</label>
          <input type="radio" id="star4" name="rating" value="4">
          <label for="star4">★</label>
          <input type="radio" id="star3" name="rating" value="3">
          <label for="star3">★</label>
          <input type="radio" id="star2" name="rating" value="2">
          <label for="star2">★</label>
          <input type="radio" id="star1" name="rating" value="1">
          <label for="star1">★</label>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label for="reviewText">Ваш отзыв *</label>
      <textarea id="reviewText" name="text" required minlength="10" maxlength="2000"
        placeholder="Расскажите о своем опыте работы с психологом..."></textarea>
      <div class="char-counter">
        <span id="charCount">0</span> / 2000 символов
      </div>
    </div>

    <div class="form-group">
      <label for="reviewType">Тип отзыва</label>
      <select id="reviewType" name="type" class="no-nice-select" required onchange="toggleMediaFields()">
        <option value="text" selected>Текстовый отзыв</option>
        <option value="photo">Фото отзыв</option>
        <option value="video">Видео отзыв</option>
      </select>
    </div>

    <div class="form-group media-field" id="photoField" style="display: none;">
      <label for="reviewImage">Фото отзыва</label>
      <input type="file" id="reviewImage" name="image" accept="image/*">
      <small>Загрузите фото отзыва (JPG, PNG, до 5MB)</small>
      <div class="image-preview" id="imagePreview" style="display: none;">
        <img id="previewImg" src="" alt="Предпросмотр">
      </div>
    </div>

    <div class="form-group media-field" id="videoField" style="display: none;">
      <label for="reviewVideo">Видео отзыва</label>
      <input type="file" id="reviewVideo" name="video" accept="video/*">
      <small>Загрузите видео отзыва (MP4, до 50MB)</small>
      <div class="video-preview" id="videoPreview" style="display: none;">
        <video id="previewVideo" controls>
          <source id="previewVideoSrc" src="" type="video/mp4">
        </video>
      </div>
    </div>

    <div class="form-group media-field" id="thumbnailField" style="display: none;">
      <label for="reviewThumbnail">Превью видео</label>
      <input type="file" id="reviewThumbnail" name="thumbnail" accept="image/*">
      <small>Загрузите превью для видео (JPG, PNG, до 2MB)</small>
      <div class="thumbnail-preview" id="thumbnailPreview" style="display: none;">
        <img id="previewThumbnail" src="" alt="Предпросмотр превью">
      </div>
    </div>

    <!-- Поле tags временно отключено из-за проблем с ограничениями БД -->
    <!--
    <div class="form-group">
      <label for="reviewTags">Теги (необязательно)</label>
      <input type="text" id="reviewTags" name="tags" placeholder="Например: тревожность, депрессия, отношения">
      <small>Укажите темы, которые затрагивались в работе</small>
    </div>
    -->

    <!-- Telegram виджет -->
    <div class="form-group telegram-widget">
      <label>Telegram авторизация *</label>
      <div class="telegram-widget-container">
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
            <button type="button" class="logout-btn" onclick="logoutTelegram()">Выйти</button>
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
      <input type="hidden" id="telegramUsername" name="telegram_username"
        value="<?= htmlspecialchars($_SESSION['telegram_user']['username'] ?? '') ?>">
      <input type="hidden" id="telegramUserId" name="telegram_user_id"
        value="<?= htmlspecialchars($_SESSION['telegram_user']['id'] ?? '') ?>">
      <input type="hidden" id="telegramUserAvatar" name="telegram_avatar"
        value="<?= htmlspecialchars($_SESSION['telegram_user']['photo_url'] ?? '') ?>">
    </div>

    <div class="form-actions">
      <button type="submit" class="submit-btn" id="submitReview">
        <span class="btn-text">Отправить отзыв</span>
        <span class="btn-loading" style="display: none;">
          <svg class="spinner" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" stroke-dasharray="31.416"
              stroke-dashoffset="31.416">
              <animate attributeName="stroke-dasharray" dur="2s" values="0 31.416;15.708 15.708;0 31.416"
                repeatCount="indefinite" />
              <animate attributeName="stroke-dashoffset" dur="2s" values="0;-15.708;-31.416" repeatCount="indefinite" />
            </circle>
          </svg>
          Отправка...
        </span>
      </button>
    </div>
  </form>

  <div class="form-success" id="formSuccess" style="display: none;">
    <div class="success-icon">
      <svg viewBox="0 0 24 24" fill="currentColor">
        <path
          d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
      </svg>
    </div>
    <h3>Спасибо за отзыв!</h3>
    <p id="successMessage">Ваш отзыв успешно отправлен и будет опубликован после модерации.</p>
    <button type="button" class="new-review-btn" onclick="resetForm()">Оставить еще один отзыв</button>
  </div>

  <div class="form-error" id="formError" style="display: none;">
    <div class="error-icon">
      <svg viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
      </svg>
    </div>
    <h3>Ошибка</h3>
    <p id="errorMessage"></p>
    <button type="button" class="try-again-btn" onclick="showForm()">Попробовать снова</button>
  </div>
</div>

<style>
  .review-form-container {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background: rgba(0, 0, 0, 0.8) !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: center !important;
    z-index: 99999 !important;
    animation: fadeIn 0.3s ease-out !important;
    padding: 20px !important;
    box-sizing: border-box !important;
    overflow: auto !important;
  }

  .review-form-container .review-form {
    background: white !important;
    border-radius: 12px !important;
    padding: 30px !important;
    max-width: 500px !important;
    width: 100% !important;
    max-height: 85vh !important;
    overflow-y: auto !important;
    position: relative !important;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4) !important;
    margin: auto !important;
    transform: translateZ(0) !important;
  }

  .review-form-container.form-hidden {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
    }

    to {
      opacity: 1;
    }
  }

  .review-form {
    background: white;
    border-radius: 12px;
    padding: 30px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    margin: 0 auto;
  }

  .review-form-header {
    display: block;
    text-align: center;
    margin-bottom: 25px;
    position: relative;
    padding-right: 40px;
  }

  .review-form-header h3 {
    margin: 0;
    color: #333;
    font-size: 24px;
    text-align: center;
  }

  .review-form-header p {
    margin: 8px 0 0 0;
    color: #666;
    font-size: 14px;
    text-align: center;
  }

  .close-form-btn {
    position: absolute;
    top: -10px;
    right: -10px;
    background: rgba(0, 0, 0, 0.1);
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background-color 0.3s;
    color: #666;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1;
  }

  .close-form-btn:hover {
    background: #f0f0f0;
  }

  .close-form-btn svg {
    width: 20px;
    height: 20px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
  }

  .form-group input[type="text"],
  .form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
    box-sizing: border-box;
  }

  .form-group input[type="text"]:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: #007bff;
  }

  .form-group textarea {
    min-height: 120px;
    resize: vertical;
  }

  .char-counter {
    text-align: right;
    font-size: 12px;
    color: #666;
    margin-top: 5px;
  }

  .rating-input {
    display: flex;
    justify-content: center;
  }

  .stars {
    display: flex;
    flex-direction: row-reverse;
    gap: 5px;
  }

  .stars input[type="radio"] {
    display: none;
  }

  .stars label {
    font-size: 30px;
    color: #ddd;
    cursor: pointer;
    transition: color 0.3s;
  }

  .stars label:hover,
  .stars label:hover~label,
  .stars input[type="radio"]:checked~label {
    color: #ffd700;
  }

  /* Стили для стандартного select */
  .no-nice-select {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    background: white;
    font-size: 16px;
    color: #333;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
  }

  .no-nice-select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
  }

  .no-nice-select:hover {
    border-color: #007bff;
  }

  /* Стили для полей загрузки медиа */
  .media-field {
    margin-top: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
    animation: slideDown 0.3s ease-out;
  }

  .media-field label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
  }

  .media-field input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 2px dashed #007bff;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .media-field input[type="file"]:hover {
    border-color: #0056b3;
    background: #f8f9fa;
  }

  .media-field small {
    display: block;
    margin-top: 8px;
    color: #666;
    font-size: 14px;
  }

  /* Стили для превью */
  .image-preview,
  .video-preview,
  .thumbnail-preview {
    margin-top: 15px;
    padding: 10px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
  }

  .image-preview img,
  .thumbnail-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    display: block;
    margin: 0 auto;
  }

  .video-preview video {
    max-width: 100%;
    max-height: 300px;
    border-radius: 8px;
    display: block;
    margin: 0 auto;
  }

  /* Анимация появления полей */
  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .telegram-widget-container {
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    padding: 20px;
    background: #f8f9fa;
  }

  .telegram-user-info {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e1e5e9;
  }

  .user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #0088cc;
  }

  .user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .user-details {
    flex: 1;
  }

  .user-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
  }

  .user-username {
    color: #0088cc;
    font-size: 14px;
    margin-bottom: 4px;
  }

  .subscription-status {
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 12px;
    display: inline-block;
  }

  .subscription-status.subscribed {
    background: #d4edda;
    color: #155724;
  }

  .subscription-status.not-subscribed {
    background: #f8d7da;
    color: #721c24;
  }

  .logout-btn {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
  }

  .logout-btn:hover {
    background: #c82333;
  }

  .verification-info {
    margin-top: 10px;
    text-align: center;
  }

  .verification-info p {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 14px;
  }

  .telegram-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #0088cc;
    color: white;
    text-decoration: none;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    transition: background-color 0.3s;
  }

  .telegram-link:hover {
    background: #006699;
  }

  .telegram-link svg {
    width: 16px;
    height: 16px;
  }

  .form-actions {
    margin-top: 30px;
    text-align: center;
  }

  .submit-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }

  .submit-btn:hover {
    background: #0056b3;
  }

  .submit-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
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

  .form-success,
  .form-error {
    text-align: center;
    padding: 40px 20px;
  }

  .success-icon,
  .error-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .success-icon {
    background: #d4edda;
    color: #155724;
  }

  .error-icon {
    background: #f8d7da;
    color: #721c24;
  }

  .success-icon svg,
  .error-icon svg {
    width: 30px;
    height: 30px;
  }

  .form-success h3,
  .form-error h3 {
    margin: 0 0 10px 0;
    color: #333;
  }

  .form-success p,
  .form-error p {
    margin: 0 0 20px 0;
    color: #666;
  }

  .new-review-btn,
  .try-again-btn {
    background: #007bff;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s;
  }

  .new-review-btn:hover,
  .try-again-btn:hover {
    background: #0056b3;
  }

  /* Мобильные стили */
  @media (max-width: 768px) {
    .review-form-container {
      padding: 10px !important;
      align-items: flex-start !important;
      padding-top: 60px !important;
    }

    .review-form-container .review-form {
      padding: 20px !important;
      max-width: calc(100% - 20px) !important;
      width: calc(100% - 20px) !important;
      max-height: calc(100vh - 80px) !important;
      border-radius: 8px !important;
      margin: 10px auto !important;
    }

    .review-form-header {
      display: block !important;
      text-align: center !important;
      padding-right: 40px !important;
    }

    .review-form-header h3 {
      font-size: 18px !important;
      margin-bottom: 8px !important;
      text-align: center !important;
    }

    .review-form-header p {
      font-size: 14px !important;
      margin-bottom: 15px !important;
      text-align: center !important;
    }

    .form-group {
      margin-bottom: 15px !important;
    }

    .form-group label {
      font-size: 14px !important;
      margin-bottom: 5px !important;
      display: block !important;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      padding: 12px !important;
      font-size: 16px !important;
      width: 100% !important;
      box-sizing: border-box !important;
      border-radius: 6px !important;
    }

    .close-form-btn {
      position: fixed !important;
      top: 15px !important;
      right: 15px !important;
      width: 40px !important;
      height: 40px !important;
      background: rgba(0, 0, 0, 0.8) !important;
      color: white !important;
      border-radius: 50% !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      z-index: 99999 !important;
    }

    .submit-btn {
      padding: 15px 20px !important;
      font-size: 16px !important;
      width: 100% !important;
      border-radius: 8px !important;
    }

    .rating-input .stars {
      justify-content: center !important;
      gap: 8px !important;
    }

    .rating-input .stars label {
      font-size: 24px !important;
    }
  }

  @media (max-width: 480px) {
    .review-form-container {
      padding: 5px !important;
      padding-top: 70px !important;
    }

    .review-form-container .review-form {
      padding: 15px !important;
      max-width: calc(100% - 10px) !important;
      width: calc(100% - 10px) !important;
      max-height: calc(100vh - 80px) !important;
      border-radius: 6px !important;
      margin: 5px auto !important;
    }

    .review-form-header h3 {
      font-size: 16px !important;
      margin-bottom: 6px !important;
    }

    .review-form-header p {
      font-size: 13px !important;
      margin-bottom: 12px !important;
    }

    .form-group {
      margin-bottom: 12px !important;
    }

    .form-group label {
      font-size: 13px !important;
      margin-bottom: 4px !important;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      padding: 10px !important;
      font-size: 14px !important;
      border-radius: 4px !important;
    }

    .close-form-btn {
      position: fixed !important;
      top: 10px !important;
      right: 10px !important;
      width: 36px !important;
      height: 36px !important;
    }

    .submit-btn {
      padding: 12px 16px !important;
      font-size: 14px !important;
      border-radius: 6px !important;
    }

    .rating-input .stars {
      gap: 6px !important;
    }

    .rating-input .stars label {
      font-size: 20px !important;
    }
  }
</style>

<script src="https://telegram.org/js/telegram-widget.js?22"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if (window.reviewFormInitialized) {
      return;
    }
    window.reviewFormInitialized = true;

    // Принудительно скрываем форму при загрузке страницы
    const container = document.getElementById('reviewFormContainer');
    if (container) {
      container.style.setProperty('display', 'none', 'important');
      container.style.setProperty('visibility', 'hidden', 'important');
      container.style.setProperty('opacity', '0', 'important');
      container.style.setProperty('pointer-events', 'none', 'important');
      container.style.setProperty('z-index', '-1', 'important');
      container.classList.add('form-hidden');
      console.log('🔴 Форма принудительно скрыта при загрузке страницы');
    }

    const form = document.getElementById('reviewForm');
    const textarea = document.getElementById('reviewText');
    const charCount = document.getElementById('charCount');

    if (form) {
      form.setAttribute('data-no-validate', 'true');
      form.setAttribute('data-isolated', 'true');

      if (typeof $ !== 'undefined') {
        $(form).off();
        $(form).find('input, textarea, select').off();
      }
    }

    // Инициализация Telegram виджета
    initTelegramWidget();

    // Предотвращаем инициализацию nice-select на нашем select
    const reviewTypeSelect = document.getElementById('reviewType');
    if (reviewTypeSelect) {
      reviewTypeSelect.classList.add('no-nice-select');
      // Удаляем любые существующие nice-select обертки
      const niceSelectWrapper = reviewTypeSelect.nextElementSibling;
      if (niceSelectWrapper && niceSelectWrapper.classList.contains('nice-select')) {
        niceSelectWrapper.remove();
      }
    }

    // Счетчик символов
    if (textarea && charCount) {
      textarea.addEventListener('input', function () {
        const length = this.value.length;
        charCount.textContent = length;
        if (length > 1800) {
          charCount.style.color = '#dc3545';
        } else if (length > 1500) {
          charCount.style.color = '#ffc107';
        } else {
          charCount.style.color = '#666';
        }
      });
    }

    // Обработка отправки формы
    if (form) {
      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        console.log('🔍 Начинаем валидацию формы...');

        const name = document.getElementById('reviewName').value.trim();
        const text = document.getElementById('reviewText').value.trim();
        const rating = document.querySelector('input[name="rating"]:checked');
        const age = document.getElementById('reviewAge').value.trim();
        const telegramUsername = document.getElementById('telegramUsername').value.trim();
        const telegramUserId = document.getElementById('telegramUserId').value.trim();

        console.log('📝 Данные формы:', { name, text, rating: rating?.value, age, telegramUsername, telegramUserId });

        // Валидация
        if (!name) {
          showError('Пожалуйста, укажите ваше имя');
          return;
        }

        if (!telegramUsername || !telegramUserId) {
          showError('Пожалуйста, авторизуйтесь через Telegram');
          return;
        }

        if (!text || text.length < 10) {
          showError('Пожалуйста, напишите отзыв (минимум 10 символов)');
          return;
        }

        if (!rating) {
          showError('Пожалуйста, поставьте оценку');
          return;
        }

        // Проверяем обязательные поля для медиа
        const type = document.getElementById('reviewType').value;
        if (type === 'photo') {
          const imageFile = document.getElementById('reviewImage').files[0];
          if (!imageFile) {
            showError('Пожалуйста, загрузите фото отзыва');
            return;
          }
        } else if (type === 'video') {
          const videoFile = document.getElementById('reviewVideo').files[0];
          const thumbnailFile = document.getElementById('reviewThumbnail').files[0];
          if (!videoFile) {
            showError('Пожалуйста, загрузите видео отзыва');
            return;
          }
          if (!thumbnailFile) {
            showError('Пожалуйста, загрузите превью для видео');
            return;
          }
        }

        // Показываем индикатор загрузки
        const submitBtn = document.getElementById('submitReview');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        submitBtn.disabled = true;

        try {
          const formData = new FormData(form);
          
          // Поле tags временно отключено
          // const tagsValue = formData.get('tags');
          // if (!tagsValue || tagsValue.trim() === '') {
          //   formData.delete('tags');
          // }
          
          const response = await fetch('/api/add-review.php', {
            method: 'POST',
            body: formData
          });

          const result = await response.json();

          if (result.success) {
            showSuccess(result.message || 'Отзыв успешно отправлен!');
          } else {
            showError(result.error || 'Ошибка при отправке отзыва');
          }
        } catch (error) {
          console.error('Error submitting form:', error);
          showError('Ошибка при отправке отзыва. Пожалуйста, попробуйте позже.');
        } finally {
          // Восстанавливаем кнопку
          btnText.style.display = 'inline';
          btnLoading.style.display = 'none';
          submitBtn.disabled = false;
        }
      });
    }

    // Обработка нажатия Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        const container = document.getElementById('reviewFormContainer');
        if (container && container.style.display === 'flex') {
          hideReviewForm();
        }
      }
    });

    // Обработка клика вне формы для закрытия
    document.addEventListener('click', function (e) {
      const container = document.getElementById('reviewFormContainer');
      const form = document.getElementById('reviewForm');
      if (container && e.target === container) {
        hideReviewForm();
      }
    });
  });

  function toggleMediaFields() {
    const type = document.getElementById('reviewType').value;
    const photoField = document.getElementById('photoField');
    const videoField = document.getElementById('videoField');
    const thumbnailField = document.getElementById('thumbnailField');

    // Скрываем все поля
    photoField.style.display = 'none';
    videoField.style.display = 'none';
    thumbnailField.style.display = 'none';

    // Очищаем все превью
    hideAllMediaPreviews();

    // Показываем соответствующие поля
    if (type === 'photo') {
      photoField.style.display = 'block';
      // Делаем поле фото обязательным
      document.getElementById('reviewImage').required = true;
      document.getElementById('reviewVideo').required = false;
      document.getElementById('reviewThumbnail').required = false;
    } else if (type === 'video') {
      videoField.style.display = 'block';
      thumbnailField.style.display = 'block';
      // Делаем поля видео обязательными
      document.getElementById('reviewImage').required = false;
      document.getElementById('reviewVideo').required = true;
      document.getElementById('reviewThumbnail').required = true;
    } else {
      // Для текстового отзыва убираем обязательность
      document.getElementById('reviewImage').required = false;
      document.getElementById('reviewVideo').required = false;
      document.getElementById('reviewThumbnail').required = false;
    }
  }

  function hideAllMediaPreviews() {
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('videoPreview').style.display = 'none';
    document.getElementById('thumbnailPreview').style.display = 'none';
  }

  document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('reviewImage');
    const thumbnailInput = document.getElementById('reviewThumbnail');
    const videoInput = document.getElementById('reviewVideo');

    if (imageInput) {
      imageInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
          // Валидация размера файла (5MB)
          if (file.size > 5 * 1024 * 1024) {
            alert('Размер файла не должен превышать 5MB');
            e.target.value = '';
            return;
          }

          // Валидация типа файла
          if (!file.type.startsWith('image/')) {
            alert('Пожалуйста, выберите изображение');
            e.target.value = '';
            return;
          }

          const reader = new FileReader();
          reader.onload = function (e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
          };
          reader.readAsDataURL(file);
        }
      });
    }

    if (thumbnailInput) {
      thumbnailInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
          // Валидация размера файла (2MB)
          if (file.size > 2 * 1024 * 1024) {
            alert('Размер файла не должен превышать 2MB');
            e.target.value = '';
            return;
          }

          // Валидация типа файла
          if (!file.type.startsWith('image/')) {
            alert('Пожалуйста, выберите изображение');
            e.target.value = '';
            return;
          }

          const reader = new FileReader();
          reader.onload = function (e) {
            document.getElementById('previewThumbnail').src = e.target.result;
            document.getElementById('thumbnailPreview').style.display = 'block';
          };
          reader.readAsDataURL(file);
        }
      });
    }

    if (videoInput) {
      videoInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
          // Валидация размера файла (50MB)
          if (file.size > 50 * 1024 * 1024) {
            alert('Размер файла не должен превышать 50MB');
            e.target.value = '';
            return;
          }

          // Валидация типа файла
          if (!file.type.startsWith('video/')) {
            alert('Пожалуйста, выберите видео файл');
            e.target.value = '';
            return;
          }

          const url = URL.createObjectURL(file);
          document.getElementById('previewVideoSrc').src = url;
          document.getElementById('previewVideo').load();
          document.getElementById('videoPreview').style.display = 'block';
        }
      });
    }
  });

  // Telegram виджет функции
  function initTelegramWidget() {
    const widgetContainer = document.getElementById('telegram-login-widget');
    if (!widgetContainer) {
      console.log('❌ telegram-login-widget не найден');
      return;
    }

    // Создаем официальный Telegram виджет
    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://telegram.org/js/telegram-widget.js?22';
    script.setAttribute('data-telegram-login', 'Cherkas_psybot');
    script.setAttribute('data-size', 'medium');
    script.setAttribute('data-onauth', 'onTelegramAuth(user)');
    script.setAttribute('data-request-access', 'write');
    widgetContainer.appendChild(script);

    console.log('✅ Telegram виджет инициализирован');
  }

  // Глобальная функция для обработки авторизации Telegram
  window.onTelegramAuth = function (user) {
    console.log('Telegram auth success:', user);

    // Отправляем данные на сервер для проверки и сохранения
    fetch('/api/telegram-auth.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(user)
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Перезагружаем страницу для обновления интерфейса
          location.reload();
        } else {
          alert('Ошибка авторизации: ' + data.error);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при авторизации');
      });
  };

  function handleTelegramAuth(user) {
    // Используем глобальную функцию
    window.onTelegramAuth(user);
  }

  function logoutTelegram() {
    // Очищаем данные пользователя
    document.getElementById('telegramUsername').value = '';
    document.getElementById('telegramUserId').value = '';
    document.getElementById('telegramUserAvatar').value = '';

    // Показываем виджет снова
    const userInfo = document.getElementById('telegramUserInfo');
    const widgetContainer = document.getElementById('telegram-login-widget');

    if (userInfo && widgetContainer) {
      userInfo.style.display = 'none';
      widgetContainer.style.display = 'block';
    }

    // Переинициализируем виджет
    initTelegramWidget();
  }

  function showSuccess(message) {
    const form = document.getElementById('reviewForm');
    const success = document.getElementById('formSuccess');
    const error = document.getElementById('formError');

    form.style.display = 'none';
    error.style.display = 'none';
    success.style.display = 'block';

    document.getElementById('successMessage').textContent = message;
  }

  function showError(message) {
    const form = document.getElementById('reviewForm');
    const success = document.getElementById('formSuccess');
    const error = document.getElementById('formError');

    form.style.display = 'none';
    success.style.display = 'none';
    error.style.display = 'block';

    document.getElementById('errorMessage').textContent = message;
  }

  function showForm() {
    const form = document.getElementById('reviewForm');
    const success = document.getElementById('formSuccess');
    const error = document.getElementById('formError');

    success.style.display = 'none';
    error.style.display = 'none';
    form.style.display = 'block';
  }

  function resetForm() {
    document.getElementById('reviewForm').reset();
    document.getElementById('charCount').textContent = '0';
    hideAllMediaPreviews();
    logoutTelegram();
    showForm();
  }

  // Альтернативная функция для принудительного закрытия формы
  window.forceHideReviewForm = function () {
    console.log('🔴 forceHideReviewForm вызвана');
    const container = document.getElementById('reviewFormContainer');
    if (container) {
      console.log('🔴 Принудительное удаление формы из DOM');
      container.remove();
      console.log('🔴 Форма удалена из DOM');
    } else {
      console.log('🔴 container не найден для удаления');
    }
  };

  // Функция для закрытия формы
  window.hideReviewForm = function () {
    console.log('🔴 hideReviewForm вызвана');
    const container = document.getElementById('reviewFormContainer');
    console.log('🔴 container найден:', container);

    if (container) {
      console.log('🔴 Текущий display:', container.style.display);

      // Принудительно скрываем форму
      container.style.setProperty('display', 'none', 'important');
      container.style.setProperty('visibility', 'hidden', 'important');
      container.style.setProperty('opacity', '0', 'important');
      container.style.setProperty('pointer-events', 'none', 'important');
      container.style.setProperty('transform', 'scale(0)', 'important');
      container.style.setProperty('z-index', '-1', 'important');

      console.log('🔴 display изменен на none (important)');
      console.log('🔴 visibility изменен на hidden (important)');
      console.log('🔴 opacity изменен на 0 (important)');
      console.log('🔴 pointerEvents изменен на none (important)');
      console.log('🔴 transform изменен на scale(0) (important)');
      console.log('🔴 z-index изменен на -1 (important)');

      // Дополнительно скрываем через CSS класс
      container.classList.add('form-hidden');

      // Восстанавливаем прокрутку body
      document.body.style.overflow = '';

      console.log('🔴 Добавлен класс form-hidden');

      // Принудительно обновляем стили
      container.offsetHeight; // Force reflow

      console.log('🔴 Принудительное обновление стилей выполнено');

      // Если форма все еще видна, используем принудительное удаление
      setTimeout(() => {
        if (container.style.display !== 'none') {
          console.log('🔴 Форма все еще видна, используем принудительное удаление');
          forceHideReviewForm();
        }
      }, 100);
    } else {
      console.log('🔴 container не найден!');
    }
  };

  // Функция для открытия формы
  window.showReviewForm = function () {
    console.log('🟢 showReviewForm вызвана');
    const container = document.getElementById('reviewFormContainer');
    console.log('🟢 container найден:', container);

    if (container) {
      console.log('🟢 Текущий display:', container.style.display);

      // Полностью сбрасываем все стили скрытия
      container.style.removeProperty('transform');
      container.style.removeProperty('z-index');

      // Показываем форму с правильным центрированием
      container.style.setProperty('display', 'flex', 'important');
      container.style.setProperty('visibility', 'visible', 'important');
      container.style.setProperty('opacity', '1', 'important');
      container.style.setProperty('pointer-events', 'auto', 'important');
      container.style.setProperty('justify-content', 'center', 'important');
      container.style.setProperty('align-items', 'center', 'important');
      container.style.setProperty('position', 'fixed', 'important');
      container.style.setProperty('top', '0', 'important');
      container.style.setProperty('left', '0', 'important');
      container.style.setProperty('right', '0', 'important');
      container.style.setProperty('bottom', '0', 'important');
      container.style.setProperty('width', '100%', 'important');
      container.style.setProperty('height', '100%', 'important');
      container.style.setProperty('z-index', '99999', 'important');
      container.style.setProperty('background', 'rgba(0, 0, 0, 0.8)', 'important');

      // Для мобильных устройств настраиваем отдельно
      if (window.innerWidth <= 768) {
        container.style.setProperty('align-items', 'flex-start', 'important');
        container.style.setProperty('padding-top', '60px', 'important');
        container.style.setProperty('overflow-y', 'auto', 'important');
      }

      console.log('🟢 display изменен на flex (important)');
      console.log('🟢 visibility изменен на visible (important)');
      console.log('🟢 opacity изменен на 1 (important)');
      console.log('🟢 pointerEvents изменен на auto (important)');
      console.log('🟢 Центрирование применено (important)');
      console.log('🟢 z-index установлен на 99999 (important)');

      // Убираем класс скрытия
      container.classList.remove('form-hidden');

      // Блокируем прокрутку body
      document.body.style.overflow = 'hidden';

      console.log('🟢 Убран класс form-hidden');

      // Принудительно обновляем стили
      container.offsetHeight; // Force reflow

      console.log('🟢 Принудительное обновление стилей выполнено');
    } else {
      console.log('🟢 container не найден!');
    }
  };
</script>