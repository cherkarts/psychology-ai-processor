<?php
/**
 * Тестовая страница для проверки Telegram авторизации
 * После успешной настройки можно удалить этот файл
 */

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Проверяем авторизацию
$isAuth = isset($_SESSION['telegram_user']);
$user = $_SESSION['telegram_user'] ?? null;
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Тест Telegram авторизации</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .container {
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      max-width: 600px;
      width: 100%;
      padding: 40px;
    }

    h1 {
      color: #2c3e50;
      margin-bottom: 10px;
      font-size: 28px;
    }

    .subtitle {
      color: #7f8c8d;
      margin-bottom: 30px;
      font-size: 14px;
    }

    .status-box {
      background: #ecf0f1;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
    }

    .status-box.success {
      background: #d4edda;
      border-left: 4px solid #28a745;
    }

    .status-box.info {
      background: #d1ecf1;
      border-left: 4px solid #17a2b8;
    }

    .status-box h3 {
      color: #2c3e50;
      margin-bottom: 15px;
      font-size: 18px;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-top: 15px;
    }

    .user-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      object-fit: cover;
    }

    .user-details {
      flex: 1;
    }

    .user-name {
      font-size: 18px;
      font-weight: 600;
      color: #2c3e50;
    }

    .user-username {
      color: #7f8c8d;
      font-size: 14px;
    }

    .info-item {
      padding: 10px 0;
      border-bottom: 1px solid #ecf0f1;
    }

    .info-item:last-child {
      border-bottom: none;
    }

    .info-label {
      font-weight: 600;
      color: #7f8c8d;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .info-value {
      color: #2c3e50;
      margin-top: 5px;
    }

    .btn {
      display: inline-block;
      padding: 12px 24px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
      border: none;
      cursor: pointer;
      font-size: 14px;
    }

    .btn-primary {
      background: #667eea;
      color: white;
    }

    .btn-primary:hover {
      background: #5568d3;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-danger {
      background: #e74c3c;
      color: white;
    }

    .btn-danger:hover {
      background: #c0392b;
    }

    .telegram-widget {
      margin-top: 20px;
      padding: 20px;
      background: #f8f9fa;
      border-radius: 8px;
      text-align: center;
    }

    .steps {
      margin-top: 30px;
    }

    .step {
      display: flex;
      gap: 15px;
      margin-bottom: 20px;
    }

    .step-number {
      width: 30px;
      height: 30px;
      background: #667eea;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      flex-shrink: 0;
    }

    .step-content h4 {
      color: #2c3e50;
      margin-bottom: 5px;
    }

    .step-content p {
      color: #7f8c8d;
      font-size: 14px;
      line-height: 1.6;
    }

    .alert {
      padding: 15px;
      border-radius: 6px;
      margin-bottom: 20px;
    }

    .alert-warning {
      background: #fff3cd;
      border-left: 4px solid #ffc107;
      color: #856404;
    }

    .alert-info {
      background: #d1ecf1;
      border-left: 4px solid #17a2b8;
      color: #0c5460;
    }

    code {
      background: #f4f4f4;
      padding: 2px 6px;
      border-radius: 3px;
      font-family: 'Courier New', monospace;
      font-size: 13px;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>🔐 Тест Telegram авторизации</h1>
    <p class="subtitle">Проверка работы верификации через Telegram</p>

    <?php if ($isAuth): ?>
      <!-- Пользователь авторизован -->
      <div class="status-box success">
        <h3>✅ Авторизация работает!</h3>
        <div class="user-info">
          <img src="<?= htmlspecialchars($user['photo_url'] ?? 'https://via.placeholder.com/60') ?>" alt="Avatar"
            class="user-avatar">
          <div class="user-details">
            <div class="user-name">
              <?= htmlspecialchars($user['first_name'] . ' ' . ($user['last_name'] ?? '')) ?>
            </div>
            <div class="user-username">
              @<?= htmlspecialchars($user['username'] ?? 'no_username') ?>
            </div>
          </div>
        </div>
      </div>

      <div class="status-box">
        <h3>Данные пользователя</h3>
        <div class="info-item">
          <div class="info-label">Telegram ID</div>
          <div class="info-value"><code><?= htmlspecialchars($user['id']) ?></code></div>
        </div>
        <div class="info-item">
          <div class="info-label">Имя</div>
          <div class="info-value"><?= htmlspecialchars($user['first_name']) ?></div>
        </div>
        <?php if (!empty($user['last_name'])): ?>
          <div class="info-item">
            <div class="info-label">Фамилия</div>
            <div class="info-value"><?= htmlspecialchars($user['last_name']) ?></div>
          </div>
        <?php endif; ?>
        <?php if (!empty($user['username'])): ?>
          <div class="info-item">
            <div class="info-label">Username</div>
            <div class="info-value">@<?= htmlspecialchars($user['username']) ?></div>
          </div>
        <?php endif; ?>
        <div class="info-item">
          <div class="info-label">Дата авторизации</div>
          <div class="info-value"><?= date('d.m.Y H:i:s', $user['auth_date']) ?></div>
        </div>
      </div>

      <div class="alert alert-info">
        <strong>Отлично!</strong> Теперь вы можете оставлять отзывы и комментарии на сайте.
      </div>

      <a href="/reviews" class="btn btn-primary" style="margin-right: 10px;">Перейти к отзывам</a>
      <a href="/api/telegram-logout.php" class="btn btn-danger">Выйти</a>

    <?php else: ?>
      <!-- Пользователь не авторизован -->
      <div class="alert alert-warning">
        <strong>⚠️ Внимание!</strong> Перед тестированием убедитесь, что выполнили настройку в BotFather!
      </div>

      <div class="status-box info">
        <h3>Инструкция по настройке</h3>
        <div class="steps">
          <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">
              <h4>Откройте BotFather</h4>
              <p>Найдите <a href="https://t.me/BotFather" target="_blank">@BotFather</a> в Telegram</p>
            </div>
          </div>
          <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">
              <h4>Настройте домен</h4>
              <p>Отправьте команду <code>/setdomain</code>, выберите <code>@Cherkas_psybot</code> и введите
                <code>cherkas-therapy.ru</code></p>
            </div>
          </div>
          <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">
              <h4>Нажмите кнопку ниже</h4>
              <p>Авторизуйтесь через Telegram и проверьте работу</p>
            </div>
          </div>
        </div>
      </div>

      <div class="telegram-widget" id="telegram-widget-container">
        <p style="color: #7f8c8d; margin-bottom: 15px;">Нажмите на кнопку для авторизации:</p>
        <div id="telegram-login-widget"></div>
      </div>
    <?php endif; ?>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ecf0f1; text-align: center;">
      <p style="color: #7f8c8d; font-size: 12px;">
        📖 Подробная документация: <code>docs/TELEGRAM-VERIFICATION-GUIDE.md</code>
      </p>
    </div>
  </div>

  <?php if (!$isAuth): ?>
    <script>
      // Инициализация Telegram виджета
      window.onload = function () {
        const widgetContainer = document.getElementById('telegram-login-widget');
        if (widgetContainer) {
          const script = document.createElement('script');
          script.async = true;
          script.src = 'https://telegram.org/js/telegram-widget.js?22';
          script.setAttribute('data-telegram-login', 'Cherkas_psybot');
          script.setAttribute('data-size', 'large');
          script.setAttribute('data-onauth', 'onTelegramAuth(user)');
          script.setAttribute('data-request-access', 'write');
          widgetContainer.appendChild(script);
        }
      };

      // Обработка авторизации
      window.onTelegramAuth = function (user) {
        console.log('Telegram auth success:', user);

        // Отправляем данные на сервер
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
              // Перезагружаем страницу
              location.reload();
            } else {
              alert('Ошибка авторизации: ' + data.error);
            }
          })
          .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при авторизации. Проверьте консоль.');
          });
      };
    </script>
  <?php endif; ?>
</body>

</html>
