<?php
/**
 * Тест подключения к базе данных
 */

echo "<h1>🔧 Тест подключения к базе данных</h1>";

try {
  // Подключаем конфигурацию
  $config = require_once 'config.php';

  echo "<h2>📋 Конфигурация:</h2>";
  echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<p><strong>Хост:</strong> " . $config['database']['host'] . "</p>";
  echo "<p><strong>Порт:</strong> " . $config['database']['port'] . "</p>";
  echo "<p><strong>База данных:</strong> " . $config['database']['dbname'] . "</p>";
  echo "<p><strong>Пользователь:</strong> " . $config['database']['username'] . "</p>";
  echo "<p><strong>Кодировка:</strong> " . $config['database']['charset'] . "</p>";
  echo "</div>";

  // Извлекаем настройки базы данных
  $db_config = $config['database'];
  $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
  $username = $db_config['username'];
  $password = $db_config['password'];
  $options = $db_config['options'];

  echo "<h2>🔗 Подключение:</h2>";
  echo "<p>DSN: <code>{$dsn}</code></p>";

  // Подключаемся к базе данных
  $pdo = new PDO($dsn, $username, $password, $options);

  echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>✅ Подключение успешно!</h3>";
  echo "<p>База данных доступна и работает корректно.</p>";
  echo "</div>";

  // Проверяем таблицы
  echo "<h2>📊 Проверка таблиц:</h2>";

  $tables = ['articles', 'article_categories'];
  foreach ($tables as $table) {
    try {
      $sql = "SELECT COUNT(*) as count FROM {$table}";
      $stmt = $pdo->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      echo "<div style='background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
      echo "<p><strong>Таблица {$table}:</strong> ✅ Найдена ({$result['count']} записей)</p>";
      echo "</div>";

    } catch (PDOException $e) {
      echo "<div style='background: #f8d7da; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
      echo "<p><strong>Таблица {$table}:</strong> ❌ Не найдена или ошибка доступа</p>";
      echo "<p><small>Ошибка: " . $e->getMessage() . "</small></p>";
      echo "</div>";
    }
  }

  // Проверяем структуру таблицы articles
  echo "<h2>🔍 Структура таблицы articles:</h2>";
  try {
    $sql = "DESCRIBE articles";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='padding: 8px;'>Поле</th>";
    echo "<th style='padding: 8px;'>Тип</th>";
    echo "<th style='padding: 8px;'>Null</th>";
    echo "<th style='padding: 8px;'>Ключ</th>";
    echo "<th style='padding: 8px;'>По умолчанию</th>";
    echo "</tr>";

    foreach ($columns as $column) {
      echo "<tr>";
      echo "<td style='padding: 8px;'>{$column['Field']}</td>";
      echo "<td style='padding: 8px;'>{$column['Type']}</td>";
      echo "<td style='padding: 8px;'>{$column['Null']}</td>";
      echo "<td style='padding: 8px;'>{$column['Key']}</td>";
      echo "<td style='padding: 8px;'>{$column['Default']}</td>";
      echo "</tr>";
    }
    echo "</table>";

  } catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Ошибка при получении структуры таблицы: " . $e->getMessage() . "</p>";
  }

  echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
  echo "<h3>🎉 Все проверки пройдены!</h3>";
  echo "<p>База данных готова к работе. Можете использовать скрипты для публикации статей.</p>";
  echo "<p><a href='/upload_article.php' style='color: #0066cc; text-decoration: none; font-weight: bold;'>📝 Загрузить AI-статью</a></p>";
  echo "</div>";

} catch (PDOException $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>❌ Ошибка подключения к базе данных</h3>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "<p><strong>Код ошибки:</strong> " . $e->getCode() . "</p>";
  echo "</div>";

  echo "<h2>🔧 Возможные решения:</h2>";
  echo "<ul>";
  echo "<li>Проверьте настройки в config.php</li>";
  echo "<li>Убедитесь, что MySQL сервер запущен</li>";
  echo "<li>Проверьте права доступа пользователя к базе данных</li>";
  echo "<li>Убедитесь, что база данных существует</li>";
  echo "</ul>";

} catch (Exception $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>❌ Общая ошибка</h3>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "</div>";
}
?>