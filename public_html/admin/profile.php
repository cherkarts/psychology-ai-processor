<?php
session_start();
require_once 'includes/auth.php';

// Проверка авторизации
if (!isLoggedIn()) {
  header('Location: login.php');
  exit;
}

$user = getCurrentUser();
$message = '';
$error = '';

// Обработка изменения пароля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'change_password') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Получаем текущего пользователя из конфигурации
    global $adminUsers;
    $currentUserData = $adminUsers[$user['username']] ?? null;

    if (!$currentUserData) {
      $error = 'Пользователь не найден';
    } elseif (!password_verify($currentPassword, $currentUserData['password'])) {
      $error = 'Неверный текущий пароль';
    } elseif (strlen($newPassword) < 6) {
      $error = 'Новый пароль должен содержать минимум 6 символов';
    } elseif ($newPassword !== $confirmPassword) {
      $error = 'Пароли не совпадают';
    } else {
      // Обновление пароля в конфигурации
      $configFile = __DIR__ . '/includes/config.php';
      $configContent = file_get_contents($configFile);

      // Создаем новый хеш пароля
      $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

      // Заменяем старый хеш на новый
      $oldHash = $currentUserData['password'];
      $configContent = str_replace($oldHash, $newHash, $configContent);

      // Сохраняем обновленную конфигурацию
      if (file_put_contents($configFile, $configContent)) {
        $message = 'Пароль успешно изменен. Перезагрузите страницу для применения изменений.';
      } else {
        $error = 'Ошибка при сохранении пароля. Проверьте права доступа к файлу конфигурации.';
      }
    }
  }
}

include 'includes/header.php';
?>

<div class="admin-container">
  <div class="admin-content">
    <section class="card">
      <div class="card-header">
        <h1>Профиль администратора</h1>
      </div>
      <div class="card-body">
        <?php if ($message): ?>
          <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="profile-info">
          <div class="info-group">
            <label>Имя пользователя:</label>
            <span><?= htmlspecialchars($user['username']) ?></span>
          </div>

          <div class="info-group">
            <label>Имя:</label>
            <span><?= htmlspecialchars($user['name']) ?></span>
          </div>

          <div class="info-group">
            <label>Роль:</label>
            <span><?= htmlspecialchars($user['role']) ?></span>
          </div>

          <div class="info-group">
            <label>Разрешения:</label>
            <span><?= implode(', ', $user['permissions']) ?></span>
          </div>

          <div class="info-group">
            <label>Время входа:</label>
            <span><?= date('d.m.Y H:i', $user['login_time']) ?></span>
          </div>
        </div>

        <hr style="margin: 30px 0;">

        <h2>Изменить пароль</h2>
        <form method="post" class="password-form">
          <input type="hidden" name="action" value="change_password">

          <div class="form-group">
            <label>Текущий пароль</label>
            <input type="password" name="current_password" required>
          </div>

          <div class="form-group">
            <label>Новый пароль</label>
            <input type="password" name="new_password" required minlength="6">
            <small>Минимум 6 символов</small>
          </div>

          <div class="form-group">
            <label>Подтвердите новый пароль</label>
            <input type="password" name="confirm_password" required minlength="6">
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Изменить пароль</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>

<style>
  .profile-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
  }

  .info-group {
    display: flex;
    margin-bottom: 15px;
    align-items: center;
  }

  .info-group:last-child {
    margin-bottom: 0;
  }

  .info-group label {
    font-weight: 600;
    min-width: 150px;
    color: #495057;
  }

  .info-group span {
    color: #212529;
  }

  .password-form {
    max-width: 400px;
  }

  .password-form .form-group {
    margin-bottom: 20px;
  }

  .password-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
  }

  .password-form input {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
  }

  .password-form small {
    color: #6c757d;
    font-size: 12px;
    margin-top: 5px;
    display: block;
  }

  .form-actions {
    margin-top: 30px;
  }

  .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-block;
  }

  .btn-primary {
    background: #007bff;
    color: white;
  }

  .btn-primary:hover {
    background: #0056b3;
  }

  .alert {
    padding: 12px 16px;
    border-radius: 4px;
    margin-bottom: 20px;
  }

  .alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
</style>

<?php include 'includes/footer.php'; ?>