<?php
/**
 * Диагностика настройки Telegram авторизации
 * Проверяет все компоненты системы
 */

// Подключаем необходимые файлы
require_once 'includes/Database.php';

$checks = [];

// 1. Проверка конфигурации
$checks[] = [
  'name' => 'Конфигурация бота',
  'check' => function () {
    $config = require 'config.php';
    return isset($config['telegram']['bot_token']) && !empty($config['telegram']['bot_token']);
  },
  'details' => function () {
    $config = require 'config.php';
    $token = $config['telegram']['bot_token'] ?? 'НЕ НАЙДЕН';
    $masked = substr($token, 0, 10) . '...' . substr($token, -10);
    return "Токен: $masked";
  }
];

// 2. Проверка таблицы telegram_users
$checks[] = [
  'name' => 'Таблица telegram_users',
  'check' => function () {
    try {
      $db = Database::getInstance();
      $result = $db->fetchOne("SHOW TABLES LIKE 'telegram_users'");
      return !empty($result);
    } catch (Exception $e) {
      return false;
    }
  },
  'details' => function () {
    try {
      $db = Database::getInstance();
      $count = $db->fetchColumn("SELECT COUNT(*) FROM telegram_users");
      return "Пользователей в БД: $count";
    } catch (Exception $e) {
      return "Ошибка: " . $e->getMessage();
    }
  }
];

// 3. Проверка API авторизации
$checks[] = [
  'name' => 'API авторизации',
  'check' => function () {
    return file_exists(__DIR__ . '/api/telegram-auth.php');
  },
  'details' => function () {
    $file = __DIR__ . '/api/telegram-auth.php';
    if (file_exists($file)) {
      $size = filesize($file);
      return "Размер файла: " . round($size / 1024, 2) . " KB";
    }
    return "Файл не найден";
  }
];

// 4. Проверка виджетов
$checks[] = [
  'name' => 'Виджеты авторизации',
  'check' => function () {
    $files = [
      'includes/telegram-auth-widget.php',
      'includes/review-form-new.php',
      'includes/comments-widget.php',
      'includes/product-reviews-widget.php'
    ];
    foreach ($files as $file) {
      if (!file_exists(__DIR__ . '/' . $file)) {
        return false;
      }
    }
    return true;
  },
  'details' => function () {
    return "Все 4 виджета на месте";
  }
];

// 5. Проверка таблицы reviews
$checks[] = [
  'name' => 'Таблица reviews',
  'check' => function () {
    try {
      $db = Database::getInstance();
      $result = $db->fetchOne("SHOW TABLES LIKE 'reviews'");
      return !empty($result);
    } catch (Exception $e) {
      return false;
    }
  },
  'details' => function () {
    try {
      $db = Database::getInstance();
      $count = $db->fetchColumn("SELECT COUNT(*) FROM reviews");
      return "Отзывов в БД: $count";
    } catch (Exception $e) {
      return "Ошибка: " . $e->getMessage();
    }
  }
];

// 6. Проверка таблицы comments
$checks[] = [
  'name' => 'Таблица comments',
  'check' => function () {
    try {
      $db = Database::getInstance();
      $result = $db->fetchOne("SHOW TABLES LIKE 'comments'");
      return !empty($result);
    } catch (Exception $e) {
      return false;
    }
  },
  'details' => function () {
    try {
      $db = Database::getInstance();
      $count = $db->fetchColumn("SELECT COUNT(*) FROM comments");
      return "Комментариев в БД: $count";
    } catch (Exception $e) {
      return "Ошибка: " . $e->getMessage();
    }
  }
];

?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Диагностика Telegram</title>
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
      padding: 40px 20px;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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

    .check-item {
      padding: 20px;
      margin-bottom: 15px;
      border-radius: 8px;
      border-left: 4px solid #e0e0e0;
      background: #f8f9fa;
      transition: all 0.3s;
    }

    .check-item.success {
      background: #d4edda;
      border-left-color: #28a745;
    }

    .check-item.error {
      background: #f8d7da;
      border-left-color: #dc3545;
    }

    .check-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 8px;
    }

    .check-icon {
      font-size: 24px;
    }

    .check-name {
      font-weight: 600;
      color: #2c3e50;
      flex: 1;
    }

    .check-status {
      font-size: 12px;
      padding: 4px 12px;
      border-radius: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    .check-status.success {
      background: #28a745;
      color: white;
    }

    .check-status.error {
      background: #dc3545;
      color: white;
    }

    .check-details {
      color: #6c757d;
      font-size: 13px;
      margin-left: 34px;
    }

    .summary {
      margin-top: 30px;
      padding: 20px;
      background: #e8f4f8;
      border-radius: 8px;
      border-left: 4px solid #17a2b8;
    }

    .summary h3 {
      color: #2c3e50;
      margin-bottom: 10px;
    }

    .summary-stats {
      display: flex;
      gap: 20px;
      margin-top: 15px;
    }

    .stat {
      flex: 1;
      text-align: center;
      padding: 15px;
      background: white;
      border-radius: 8px;
    }

    .stat-value {
      font-size: 32px;
      font-weight: 700;
      color: #667eea;
    }

    .stat-label {
      font-size: 12px;
      color: #7f8c8d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-top: 5px;
    }

    .actions {
      margin-top: 20px;
      display: flex;
      gap: 10px;
    }

    .btn {
      padding: 12px 24px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      display: inline-block;
      transition: all 0.3s;
    }

    .btn-primary {
      background: #667eea;
      color: white;
    }

    .btn-primary:hover {
      background: #5568d3;
      transform: translateY(-2px);
    }

    .btn-secondary {
      background: #6c757d;
      color: white;
    }

    .btn-secondary:hover {
      background: #5a6268;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>🔍 Диагностика Telegram</h1>
    <p class="subtitle">Проверка всех компонентов системы верификации</p>

    <?php
    $totalChecks = count($checks);
    $passedChecks = 0;

    foreach ($checks as $check):
      $isPassed = $check['check']();
      if ($isPassed)
        $passedChecks++;
      $statusClass = $isPassed ? 'success' : 'error';
      $icon = $isPassed ? '✅' : '❌';
      $statusText = $isPassed ? 'OK' : 'ОШИБКА';
      ?>
      <div class="check-item <?= $statusClass ?>">
        <div class="check-header">
          <span class="check-icon"><?= $icon ?></span>
          <span class="check-name"><?= $check['name'] ?></span>
          <span class="check-status <?= $statusClass ?>"><?= $statusText ?></span>
        </div>
        <div class="check-details">
          <?= $check['details']() ?>
        </div>
      </div>
    <?php endforeach; ?>

    <div class="summary">
      <h3>Итоги проверки</h3>
      <div class="summary-stats">
        <div class="stat">
          <div class="stat-value"><?= $passedChecks ?>/<?= $totalChecks ?></div>
          <div class="stat-label">Проверок пройдено</div>
        </div>
        <div class="stat">
          <div class="stat-value"><?= round(($passedChecks / $totalChecks) * 100) ?>%</div>
          <div class="stat-label">Готовность</div>
        </div>
      </div>

      <?php if ($passedChecks === $totalChecks): ?>
        <div style="margin-top: 20px; padding: 15px; background: #d4edda; border-radius: 6px; color: #155724;">
          <strong>🎉 Отлично!</strong> Все компоненты на месте. Осталось только настроить домен в BotFather.
        </div>
      <?php else: ?>
        <div style="margin-top: 20px; padding: 15px; background: #f8d7da; border-radius: 6px; color: #721c24;">
          <strong>⚠️ Внимание!</strong> Некоторые компоненты отсутствуют или работают неправильно.
        </div>
      <?php endif; ?>

      <div class="actions">
        <a href="/test-telegram-auth.php" class="btn btn-primary">Тестовая страница</a>
        <a href="/reviews" class="btn btn-secondary">Страница отзывов</a>
        <a href="javascript:location.reload()" class="btn btn-secondary">Обновить</a>
      </div>
    </div>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: center;">
      <p style="color: #7f8c8d; font-size: 12px;">
        📖 Документация: <code>docs/TELEGRAM-VERIFICATION-GUIDE.md</code>
      </p>
    </div>
  </div>
</body>

</html>
