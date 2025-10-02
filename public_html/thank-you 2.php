<?php
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Мета-данные страницы
$meta = [
  'title' => 'Спасибо за заказ! - Магазин психолога Дениса Черкаса',
  'description' => 'Ваш заказ успешно оформлен. Мы свяжемся с вами в ближайшее время.',
  'keywords' => 'заказ оформлен, спасибо, покупка'
];
?>
<!DOCTYPE html>
<html class="js" lang="ru">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title><?php echo $meta['title']; ?></title>
  <meta name="description" content="<?php echo $meta['description']; ?>" />
  <meta name="keywords" content="<?php echo $meta['keywords']; ?>" />

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="/favicon.ico" />

  <!-- Styles -->
  <link rel="stylesheet" href="/css/main.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="/css/new-components.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="/css/shop.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="/css/shop-mobile-header.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="/css/shop-mobile-menu.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="/css/shop-mobile.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="/css/shop-mobile-cart.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="/css/shop-mobile-product.css?v=<?php echo time(); ?>" />
  <link rel="stylesheet" href="/css/shop-mobile-checkout.css?v=<?php echo time(); ?>" />
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

  <!-- Thank You Hero Section -->
  <section class="about-hero">
    <div class="wrapper">
      <div class="about-hero__content">
        <div class="thank-you-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h1 class="about-hero__title">
          <span class="about-hero__title-accent">СПАСИБО</span> ЗА ЗАКАЗ!
        </h1>
        <p class="about-hero__subtitle">
          Ваш заказ успешно оформлен и принят в обработку. Мы свяжемся с вами в ближайшее время.
        </p>
      </div>
    </div>
  </section>

  <!-- Thank You Content Section -->
  <section class="about-content">
    <div class="wrapper">
      <div class="about-content__inner">
        <div class="about-content__container">
          <div class="about-content__text">
            <h2>Что происходит дальше?</h2>

            <div class="thank-you-details">
              <div class="detail-item">
                <i class="fas fa-envelope"></i>
                <span>Мы отправили подтверждение на вашу электронную почту</span>
              </div>

              <div class="detail-item">
                <i class="fas fa-phone"></i>
                <span>Наш менеджер свяжется с вами в течение 30 минут</span>
              </div>

              <div class="detail-item">
                <i class="fas fa-clock"></i>
                <span>Обработка заказа займет 1-2 рабочих дня</span>
              </div>
            </div>

            <p>
              <strong>Номер вашего заказа:</strong>
              #<?php echo isset($_GET['order']) ? htmlspecialchars($_GET['order']) : '0001'; ?>
            </p>

            <p>
              Если у вас есть вопросы по заказу, вы можете связаться с нами по телефону
              <strong>+7 (993) 620-29-51</strong> или написать на
              <strong>info@cherkas-therapy.ru</strong>
            </p>
          </div>

          <div class="about-content__image">
            <div class="thank-you-image-placeholder">
              <i class="fas fa-shopping-bag"></i>
              <p>Ваш заказ в обработке</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Thank You CTA Section -->
  <section class="about-cta">
    <div class="wrapper">
      <div class="about-cta__content">
        <h2 class="about-cta__title">Продолжайте знакомство с нами</h2>
        <p class="about-cta__text">
          Пока мы обрабатываем ваш заказ, вы можете изучить наши статьи,
          медитации и другие полезные материалы для личностного роста.
        </p>
        <div class="thank-you-actions">
          <a href="/shop.php" class="about-cta__btn">
            <span>Продолжить покупки</span>
            <i class="fas fa-shopping-cart"></i>
          </a>
          <a href="/" class="about-cta__btn about-cta__btn--secondary">
            <span>На главную</span>
            <i class="fas fa-home"></i>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Scripts -->
  <script src="js/main.js?v=<?php echo time(); ?>"></script>
  <script src="js/new-components.js"></script>
  <script src="js/new-homepage.js?v=3.1"></script>
  <script src="js/shop-mobile-menu.js?v=1.0"></script>

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

    /* Thank You Specific Styles */
    .thank-you-icon {
      font-size: 80px;
      color: #28a745;
      margin-bottom: 30px;
      animation: bounce 2s infinite;
    }

    @keyframes bounce {

      0%,
      20%,
      50%,
      80%,
      100% {
        transform: translateY(0);
      }

      40% {
        transform: translateY(-10px);
      }

      60% {
        transform: translateY(-5px);
      }
    }

    .thank-you-details {
      margin: 30px 0;
    }

    .detail-item {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 15px;
      border-left: 4px solid #6a7e9f;
      transition: all 0.3s ease;
    }

    .detail-item:hover {
      transform: translateX(5px);
      box-shadow: 0 10px 30px rgba(106, 126, 159, 0.2);
    }

    .detail-item i {
      font-size: 24px;
      color: #6a7e9f;
      margin-right: 20px;
      width: 40px;
      text-align: center;
    }

    .detail-item span {
      color: #2c3e50;
      font-weight: 500;
      font-size: 16px;
    }

    .thank-you-image-placeholder {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-radius: 20px;
      padding: 60px 40px;
      text-align: center;
      border: 2px dashed #6a7e9f;
    }

    .thank-you-image-placeholder i {
      font-size: 60px;
      color: #6a7e9f;
      margin-bottom: 20px;
    }

    .thank-you-image-placeholder p {
      font-size: 18px;
      color: #2c3e50;
      font-weight: 600;
      margin: 0;
    }

    .thank-you-actions {
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

      .thank-you-icon {
        font-size: 60px;
        margin-bottom: 20px;
      }

      .detail-item {
        padding: 15px;
        margin-bottom: 15px;
      }

      .detail-item i {
        font-size: 20px;
        margin-right: 15px;
        width: 30px;
      }

      .detail-item span {
        font-size: 14px;
      }

      .thank-you-image-placeholder {
        padding: 40px 20px;
      }

      .thank-you-image-placeholder i {
        font-size: 40px;
        margin-bottom: 15px;
      }

      .thank-you-image-placeholder p {
        font-size: 16px;
      }

      .thank-you-actions {
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

      .thank-you-icon {
        font-size: 50px;
        margin-bottom: 15px;
      }

      .detail-item {
        padding: 12px;
        margin-bottom: 12px;
      }

      .detail-item i {
        font-size: 18px;
        margin-right: 12px;
        width: 25px;
      }

      .detail-item span {
        font-size: 13px;
      }

      .thank-you-image-placeholder {
        padding: 30px 15px;
      }

      .thank-you-image-placeholder i {
        font-size: 35px;
        margin-bottom: 12px;
      }

      .thank-you-image-placeholder p {
        font-size: 14px;
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
</body>

</html>