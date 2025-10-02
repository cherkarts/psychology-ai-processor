<?php
require_once 'includes/functions.php';

// Если режим обслуживания выключен, перенаправляем на главную
if (!isMaintenanceMode()) {
  header('Location: /');
  exit;
}

// Если пользователь является администратором, разрешаем доступ
if (isAdminAccess()) {
  // Администратор может видеть сайт даже в режиме обслуживания
  // Но показываем уведомление
  $adminMode = true;
} else {
  $adminMode = false;
}

$maintenanceMessage = getMaintenanceMessage();
?>
<!DOCTYPE html>
<html class="js" lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Сайт на обслуживании - Cherkas Therapy</title>
  <meta name="robots" content="noindex, nofollow">

  <!-- Styles -->
  <link rel="stylesheet" href="/css/main.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="/css/new-components.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="/css/pages.css?v=<?php echo time(); ?>" />

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="page">
  <!-- Simple Header -->
  <header class="simple-header">
    <div class="wrapper">
      <div class="simple-header__content">
        <div class="simple-header__logo">
          <a href="/">
            <h2>психолог ДЕНИС ЧЕРКАС</h2>
          </a>
        </div>
        <div class="simple-header__nav">
          <a href="/" class="simple-header__link">На главную</a>
        </div>
      </div>
    </div>
  </header>

  <!-- Maintenance Hero Section -->
  <section class="about-hero">
    <div class="wrapper">
      <div class="about-hero__content">
        <div class="maintenance-icon">
          <i class="fas fa-tools"></i>
        </div>
        <h1 class="about-hero__title">
          <span class="about-hero__title-accent">САЙТ</span> НА ОБСЛУЖИВАНИИ
        </h1>
        <p class="about-hero__subtitle">
          <?php echo htmlspecialchars($maintenanceMessage); ?>
        </p>
      </div>
    </div>
  </section>

  <!-- Maintenance Content Section -->
  <section class="about-content">
    <div class="wrapper">
      <div class="about-content__inner">
        <div class="about-content__container">
          <div class="about-content__text">
            <h2>Что происходит?</h2>

            <p>
              Мы проводим техническое обслуживание сайта для улучшения его работы.
              Это займет совсем немного времени, и мы вернемся к работе в ближайшее время.
            </p>

            <?php if ($adminMode): ?>
              <div class="admin-notice">
                <h3><i class="fas fa-user-shield"></i> Режим администратора</h3>
                <p>Вы видите эту страницу, потому что вошли в админ панель. Обычные посетители видят сообщение об
                  обслуживании.</p>
              </div>
            <?php endif; ?>

            <h2>Нужна срочная помощь?</h2>
            <p>
              Если вам нужна срочная психологическая помощь, вы можете связаться с нами
              любым удобным способом:
            </p>

            <div class="contact-details">
              <div class="contact-item">
                <i class="fas fa-phone"></i>
                <div class="contact-info">
                  <strong>Телефон</strong>
                  <span><?php
                  $contactSettings = getContactSettings();
                  echo $contactSettings['phone'];
                  ?></span>
                </div>
              </div>

              <div class="contact-item">
                <i class="fab fa-telegram"></i>
                <div class="contact-info">
                  <strong>Telegram</strong>
                  <a href="<?php echo $contactSettings['telegram_url']; ?>" target="_blank">Написать в Telegram</a>
                </div>
              </div>

              <div class="contact-item">
                <i class="fab fa-whatsapp"></i>
                <div class="contact-info">
                  <strong>WhatsApp</strong>
                  <a href="https://wa.me/<?php echo str_replace([' ', '(', ')', '-'], '', $contactSettings['whatsapp']); ?>"
                    target="_blank">Написать в WhatsApp</a>
                </div>
              </div>
            </div>

            <div class="countdown-info">
              <p><strong>Ожидаемое время восстановления:</strong> <span id="countdown">скоро</span></p>
            </div>
          </div>

          <div class="about-content__image">
            <div class="maintenance-image-placeholder">
              <i class="fas fa-cog fa-spin"></i>
              <p>Техническое обслуживание</p>
              <div class="progress-bar">
                <div class="progress-fill"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Maintenance CTA Section -->
  <section class="about-cta">
    <div class="wrapper">
      <div class="about-cta__content">
        <h2 class="about-cta__title">Мы скоро вернемся!</h2>
        <p class="about-cta__text">
          Пока мы работаем над улучшением сайта, вы можете связаться с нами напрямую
          для записи на консультацию или получения психологической помощи.
        </p>
        <div class="maintenance-actions">
          <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', $contactSettings['phone']); ?>"
            class="about-cta__btn">
            <span>Позвонить сейчас</span>
            <i class="fas fa-phone"></i>
          </a>
          <a href="<?php echo $contactSettings['telegram_url']; ?>" class="about-cta__btn about-cta__btn--secondary"
            target="_blank">
            <span>Написать в Telegram</span>
            <i class="fab fa-telegram"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <style>
    /* Simple Header Styles */
    .simple-header {
      background: white;
      padding: 20px 0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
    }

    .simple-header__content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }

    .simple-header__logo h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      color: #2c3e50;
    }

    .simple-header__logo a {
      text-decoration: none;
      color: inherit;
    }

    .simple-header__link {
      background: linear-gradient(135deg, #6a7e9f 0%, #5a6e8f 100%);
      color: white;
      padding: 10px 20px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .simple-header__link:hover {
      background: white;
      color: #6a7e9f;
      border: 2px solid #6a7e9f;
      transform: translateY(-2px);
    }

    /* Maintenance Specific Styles */
    .maintenance-icon {
      font-size: 80px;
      color: #ff6b35;
      margin-bottom: 30px;
      animation: rotate 3s linear infinite;
    }

    @keyframes rotate {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }

    .admin-notice {
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      border-radius: 15px;
      padding: 20px;
      margin: 30px 0;
      color: #856404;
    }

    .admin-notice h3 {
      margin-bottom: 10px;
      color: #856404;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .contact-details {
      margin: 30px 0;
    }

    .contact-item {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 15px;
      border-left: 4px solid #ff6b35;
      transition: all 0.3s ease;
    }

    .contact-item:hover {
      transform: translateX(5px);
      box-shadow: 0 10px 30px rgba(255, 107, 53, 0.2);
    }

    .contact-item i {
      font-size: 24px;
      color: #ff6b35;
      margin-right: 20px;
      width: 40px;
      text-align: center;
    }

    .contact-info {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .contact-info strong {
      color: #2c3e50;
      font-weight: 600;
      font-size: 16px;
    }

    .contact-info span,
    .contact-info a {
      color: #2c3e50;
      font-size: 16px;
      text-decoration: none;
    }

    .contact-info a:hover {
      color: #ff6b35;
    }

    .countdown-info {
      background: #e8f4fd;
      border: 1px solid #b8daff;
      border-radius: 15px;
      padding: 20px;
      margin: 30px 0;
      text-align: center;
    }

    .countdown-info p {
      margin: 0;
      color: #2c3e50;
    }

    .countdown-info strong {
      color: #2c3e50;
    }

    #countdown {
      color: #ff6b35;
      font-weight: 700;
    }

    .maintenance-image-placeholder {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-radius: 20px;
      padding: 60px 40px;
      text-align: center;
      border: 2px dashed #ff6b35;
      position: relative;
    }

    .maintenance-image-placeholder i {
      font-size: 60px;
      color: #ff6b35;
      margin-bottom: 20px;
    }

    .maintenance-image-placeholder p {
      font-size: 18px;
      color: #2c3e50;
      font-weight: 600;
      margin: 0 0 30px 0;
    }

    .progress-bar {
      width: 100%;
      height: 8px;
      background: #e9ecef;
      border-radius: 4px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #ff6b35, #ff8c42);
      border-radius: 4px;
      animation: progress 3s ease-in-out infinite;
    }

    @keyframes progress {
      0% {
        width: 0%;
      }

      50% {
        width: 70%;
      }

      100% {
        width: 100%;
      }
    }

    .maintenance-actions {
      display: flex;
      gap: 20px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .about-cta__btn--secondary {
      background: white !important;
      color: #6a7e9f !important;
      border: 2px solid #6a7e9f !important;
    }

    .about-cta__btn--secondary:hover {
      background: #6a7e9f !important;
      color: white !important;
    }

    .about-cta__btn--secondary i {
      filter: none !important;
    }

    .about-cta__btn--secondary:hover i {
      filter: brightness(0) invert(1) !important;
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
      .simple-header__content {
        padding: 0 15px;
      }

      .simple-header__logo h2 {
        font-size: 16px;
      }

      .simple-header__link {
        padding: 8px 16px;
        font-size: 14px;
      }

      .maintenance-icon {
        font-size: 60px;
        margin-bottom: 20px;
      }

      .contact-item {
        padding: 15px;
        margin-bottom: 15px;
      }

      .contact-item i {
        font-size: 20px;
        margin-right: 15px;
        width: 30px;
      }

      .contact-info strong,
      .contact-info span,
      .contact-info a {
        font-size: 14px;
      }

      .maintenance-image-placeholder {
        padding: 40px 20px;
      }

      .maintenance-image-placeholder i {
        font-size: 40px;
        margin-bottom: 15px;
      }

      .maintenance-image-placeholder p {
        font-size: 16px;
        margin-bottom: 20px;
      }

      .maintenance-actions {
        flex-direction: column;
        align-items: center;
        gap: 15px;
      }

      .about-cta__btn {
        width: 100%;
        max-width: 300px;
      }
    }

    @media (max-width: 480px) {
      .simple-header {
        padding: 15px 0;
      }

      .simple-header__content {
        flex-direction: column;
        gap: 10px;
      }

      .maintenance-icon {
        font-size: 50px;
        margin-bottom: 15px;
      }

      .contact-item {
        padding: 12px;
        margin-bottom: 12px;
      }

      .contact-item i {
        font-size: 18px;
        margin-right: 12px;
        width: 25px;
      }

      .contact-info strong,
      .contact-info span,
      .contact-info a {
        font-size: 13px;
      }

      .maintenance-image-placeholder {
        padding: 30px 15px;
      }

      .maintenance-image-placeholder i {
        font-size: 35px;
        margin-bottom: 12px;
      }

      .maintenance-image-placeholder p {
        font-size: 14px;
        margin-bottom: 15px;
      }
    }

    /* Adjust hero section for fixed header */
    .about-hero {
      margin-top: 80px;
    }

    @media (max-width: 768px) {
      .about-hero {
        margin-top: 100px;
      }
    }
  </style>

  <script>
    // Простая анимация для обратного отсчета
    let countdown = 30; // 30 минут
    const countdownElement = document.getElementById('countdown');

    function updateCountdown() {
      if (countdown > 0) {
        const minutes = Math.floor(countdown / 60);
        const seconds = countdown % 60;
        countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        countdown--;
        setTimeout(updateCountdown, 1000);
      } else {
        countdownElement.textContent = 'скоро';
      }
    }

    // Запускаем обратный отсчет
    updateCountdown();

    // Автоматическая проверка статуса каждые 30 секунд
    setInterval(() => {
      fetch(window.location.href, { method: 'HEAD' })
        .then(response => {
          if (response.status === 200) {
            // Если сайт доступен, перезагружаем страницу
            window.location.reload();
          }
        })
        .catch(() => {
          // Игнорируем ошибки
        });
    }, 30000);
  </script>
</body>

</html>
</html>