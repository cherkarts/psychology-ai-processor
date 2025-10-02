<?php
/**
 * Виджет авторизации через Telegram
 * Позволяет пользователям авторизоваться через Telegram Bot
 */

// Проверяем, авторизован ли пользователь через Telegram
function isTelegramAuthenticated()
{
  return isset($_SESSION['telegram_user']) && !empty($_SESSION['telegram_user']);
}

// Получаем данные пользователя Telegram
function getTelegramUser()
{
  return $_SESSION['telegram_user'] ?? null;
}

// Выход из системы
function telegramLogout()
{
  unset($_SESSION['telegram_user']);
  session_destroy();
}

// Проверяем подпись Telegram
function verifyTelegramAuth($auth_data)
{
  $check_hash = $auth_data['hash'];
  unset($auth_data['hash']);

  $data_check_arr = [];
  foreach ($auth_data as $key => $value) {
    $data_check_arr[] = $key . '=' . $value;
  }
  sort($data_check_arr);

  $data_check_string = implode("\n", $data_check_arr);
  $secret_key = hash('sha256', '7657713367:AAEDdQSD1K1g8ckRI-R-ePB7s1AtXc4OuyE', true);
  $hash = hash_hmac('sha256', $data_check_string, $secret_key);

  if (strcmp($hash, $check_hash) !== 0) {
    return false;
  }

  if ((time() - $auth_data['auth_date']) > 86400) {
    return false;
  }

  return true;
}

// Обработка авторизации
if (isset($_GET['telegram_auth'])) {
  $auth_data = $_GET;

  if (verifyTelegramAuth($auth_data)) {
    $_SESSION['telegram_user'] = [
      'id' => $auth_data['id'],
      'first_name' => $auth_data['first_name'] ?? '',
      'last_name' => $auth_data['last_name'] ?? '',
      'username' => $auth_data['username'] ?? '',
      'photo_url' => $auth_data['photo_url'] ?? '',
      'auth_date' => $auth_data['auth_date']
    ];

    // Сохраняем пользователя в базу данных
    saveTelegramUser($_SESSION['telegram_user']);

    header('Location: ' . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
  }
}
?>

<!-- Виджет авторизации через Telegram -->
<div id="telegram-auth-widget" class="telegram-auth-widget">
  <?php if (isTelegramAuthenticated()): ?>
    <!-- Пользователь авторизован -->
    <div class="telegram-user-info">
      <div class="user-avatar">
        <?php
        $user = getTelegramUser();
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
        <p>Авторизуйтесь через Telegram, чтобы оставлять отзывы и комментарии</p>
      </div>
      <!-- Официальный Telegram Login Widget -->
      <div id="telegram-login-widget"></div>
    </div>
  <?php endif; ?>
</div>

<style>
  .telegram-auth-widget {
    background: var(--brand-white);
    border: 1px solid var(--brand-gray);
    border-radius: 12px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }

  .telegram-user-info {
    display: flex;
    align-items: center;
    gap: 15px;
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
  }

  .auth-icon {
    color: var(--brand-primary);
    margin-bottom: 15px;
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

  .telegram-auth-btn svg {
    width: 20px;
    height: 20px;
  }

  /* Мобильные стили */
  @media (max-width: 768px) {
    .telegram-auth-widget {
      padding: 15px;
      margin: 15px 0;
    }

    .telegram-user-info {
      gap: 12px;
    }

    .user-avatar img {
      width: 35px;
      height: 35px;
    }

    .user-name {
      font-size: 16px;
    }

    .user-username {
      font-size: 13px;
    }

    .telegram-auth-btn {
      padding: 10px 20px;
      font-size: 14px;
    }
  }
</style>

<!-- Официальный Telegram Login Widget -->
<script async src="https://telegram.org/js/telegram-widget.js?22"></script>

<script>
  // Функция обработки авторизации Telegram
  function onTelegramAuth(user) {
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
  }

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

  // Инициализация Telegram виджета
  document.addEventListener('DOMContentLoaded', function () {
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
  });
</script>