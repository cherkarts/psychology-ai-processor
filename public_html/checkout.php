<?php
require_once 'includes/Models/Order.php';
require_once 'includes/Models/Article.php';
require_once 'includes/Models/Meditation.php';
require_once 'includes/Models/Review.php';
require_once 'includes/Models/Product.php';
require_once 'includes/Database.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once 'includes/functions.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
  header('Location: /maintenance.php');
  exit;
}

// Получаем товары из корзины
$cartItems = $_SESSION['cart'] ?? [];
$total = 0;

// Если корзина пустая, перенаправляем в магазин
if (empty($cartItems)) {
  header('Location: /shop.php');
  exit;
}

// Вычисляем общую сумму
foreach ($cartItems as $item) {
  $total += $item['price'] * $item['quantity'];
}

// Получаем примененный промокод из сессии
$appliedPromo = $_SESSION['applied_promo'] ?? null;
$discount = $appliedPromo['discount'] ?? 0;
$finalTotal = $total - $discount;

// Мета-данные страницы
$meta = [
  'title' => 'Оформление заказа - Магазин психолога Дениса Черкаса',
  'description' => 'Оформление заказа семинаров, книг и курсов по психологии.',
  'keywords' => 'оформление заказа, покупка, оплата'
];
?>
<!DOCTYPE html>
<html class="js" lang="ru">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  <title><?= e($meta['title']) ?></title>
  <meta content="<?= e($meta['description']) ?>" name="description" />
  <meta content="<?= e($meta['keywords']) ?>" name="keywords" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta content="telephone=no" name="format-detection" />
  <meta name="HandheldFriendly" content="true" />
  <meta name="robots" content="noindex, nofollow" />

  <!-- Стили -->
  <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" type="text/css" media="all" />
  <link rel="stylesheet" href="/css/pages.css" type="text/css" media="all" />
  <link rel="stylesheet" href="/css/shop.css?v=2.1" type="text/css" media="all" />
  <link rel="stylesheet" href="/css/checkout.css" type="text/css" media="all" />
  <link rel="stylesheet" href="/css/new-components.css" />
  <link rel="stylesheet" href="/css/shop-mobile-header.css?v=1.0" type="text/css" media="all" />
</head>

<body class="checkout-page">
  <?php include 'includes/new-header.php'; ?>

  <!-- Main Content Container -->
  <main class="main-content">
    <section class="checkout-hero">
      <div class="wrapper">
        <h1 class="checkout-title">Оформление заказа</h1>
        <p class="checkout-subtitle">Заполните форму для завершения покупки</p>
      </div>
    </section>

    <section class="checkout-content">
      <div class="wrapper">
        <div class="checkout-layout">
          <!-- Левая часть - форма заказа -->
          <div class="checkout-form-section">
            <h2 class="section-title">Данные для заказа</h2>

            <form class="checkout-form" method="POST" action="/process-order.php">
              <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

              <div class="form-group">
                <label for="name" class="form-label">Имя *</label>
                <input type="text" id="name" name="name" class="form-input" placeholder="Введите ваше имя" required>
              </div>

              <div class="form-group">
                <label for="email" class="form-label">Email *</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="example@email.com" required>
              </div>

              <div class="form-group">
                <label for="phone" class="form-label">Телефон *</label>
                <input type="tel" id="phone" name="phone" class="form-input" placeholder="+7 (999) 123-45-67" required>
              </div>

              <div class="form-group">
                <label for="comment" class="form-label">Комментарий к заказу</label>
                <textarea id="comment" name="comment" class="form-textarea" rows="4"></textarea>
              </div>

              <div class="form-group">
                <label class="form-label">Способ оплаты *</label>
                <div class="payment-methods">
                  <label class="payment-method">
                    <input type="radio" name="payment_method" value="card" checked>
                    <span class="payment-method-text">Банковская карта</span>
                  </label>
                  <label class="payment-method">
                    <input type="radio" name="payment_method" value="sbp">
                    <span class="payment-method-text">СБП</span>
                  </label>
                </div>
              </div>

              <button type="submit" class="checkout-btn">Оплатить <?= number_format($finalTotal, 0, ',', ' ') ?>
                ₽</button>
            </form>
          </div>

          <!-- Правая часть - корзина -->
          <div class="checkout-cart-section">
            <h2 class="section-title">Ваш заказ</h2>

            <div class="cart-items">
              <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                  <div class="cart-item-image">
                    <img src="<?= e($item['image']) ?>" alt="<?= e($item['title']) ?>">
                  </div>
                  <div class="cart-item-info">
                    <h3 class="cart-item-title"><?= e($item['title']) ?></h3>
                    <p class="cart-item-price"><?= number_format($item['price'], 0, ',', ' ') ?> ₽</p>
                  </div>
                  <div class="cart-item-quantity">
                    <span class="quantity"><?= $item['quantity'] ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div class="cart-total">
              <div class="total-row">
                <span>Подытог:</span>
                <span class="subtotal-amount"><?= number_format($total, 0, ',', ' ') ?> ₽</span>
              </div>

              <?php if ($appliedPromo): ?>
                <div class="total-row discount-row">
                  <span>Скидка (<?= e($appliedPromo['code']) ?>):</span>
                  <span class="discount-amount">-<?= number_format($discount, 0, ',', ' ') ?> ₽</span>
                </div>
              <?php endif; ?>

              <div class="total-row final-total">
                <span>Итого:</span>
                <span class="total-amount"><?= number_format($finalTotal, 0, ',', ' ') ?> ₽</span>
              </div>
            </div>

            <div class="cart-actions">
              <a href="/shop.php" class="continue-shopping">Продолжить покупки</a>
              <a href="/cart.php" class="edit-cart">Изменить корзину</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include 'includes/new-footer.php'; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/main.js?v=<?php echo time(); ?>"></script>
  <script src="js/new-components.js"></script>
  <script src="js/form-handler.js?v=1.5"></script>
  <script src="js/cart.js?v=<?php echo time(); ?>"></script>
  <script src="js/new-homepage.js?v=3.1"></script>
  <script src="js/shop-mobile-menu.js?v=1.0"></script>

  <script>

    // Показываем сообщения об успехе/ошибке
    <?php if (isset($_SESSION['success_message'])): ?>
      showNotification('<?php echo addslashes($_SESSION['success_message']); ?>', 'success');
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
      showNotification('<?php echo addslashes($_SESSION['error_message']); ?>', 'error');
      <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    // Функция показа уведомлений
    function showNotification(message, type = 'info') {
      const notification = document.createElement('div');
      notification.className = `notification notification-${type}`;
      notification.textContent = message;

      // Добавляем стили для разных типов
      if (type === 'success') {
        notification.style.backgroundColor = '#d4edda';
        notification.style.color = '#155724';
        notification.style.border = '1px solid #c3e6cb';
      } else if (type === 'error') {
        notification.style.backgroundColor = '#f8d7da';
        notification.style.color = '#721c24';
        notification.style.border = '1px solid #f5c6cb';
      }

      notification.style.position = 'fixed';
      notification.style.top = '20px';
      notification.style.right = '20px';
      notification.style.padding = '15px 20px';
      notification.style.borderRadius = '5px';
      notification.style.zIndex = '9999';
      notification.style.maxWidth = '400px';
      notification.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';

      document.body.appendChild(notification);

      setTimeout(() => {
        notification.remove();
      }, 5000);
    }

    // Маска для телефона
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', function (e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 0) {
        if (value.length <= 3) {
          value = '+7 (' + value;
        } else if (value.length <= 6) {
          value = '+7 (' + value.substring(0, 3) + ') ' + value.substring(3);
        } else if (value.length <= 8) {
          value = '+7 (' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6);
        } else {
          value = '+7 (' + value.substring(0, 3) + ') ' + value.substring(3, 6) + '-' + value.substring(6, 8) + '-' + value.substring(8, 10);
        }
      }
      e.target.value = value;
    });
  </script>

  <!-- Yandex.Metrika counter -->
  <script type="text/javascript">
    (function (m, e, t, r, i, k, a) {
      m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments) };
      m[i].l = 1 * new Date();
      for (var j = 0; j < document.scripts.length; j++) { if (document.scripts[j].src === r) { return; } }
      k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
    })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js?id=103948722', 'ym');

    ym(103948722, 'init', { ssr: true, webvisor: true, clickmap: true, ecommerce: "dataLayer", accurateTrackBounce: true, trackLinks: true });
  </script>
  <noscript>
    <div><img src="https://mc.yandex.ru/watch/103948722" style="position:absolute; left:-9999px;" alt="" /></div>
  </noscript>
  <!-- /Yandex.Metrika counter -->
</body>

</html>