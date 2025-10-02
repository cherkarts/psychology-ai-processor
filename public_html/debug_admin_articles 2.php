<?php
/**
 * Отладка отображения статей в админ-панели
 */

echo "<h1>🔍 Отладка админ-панели статей</h1>";

try {
  // Подключаем конфигурацию
  $config = require_once 'config.php';

  // Извлекаем настройки базы данных
  $db_config = $config['database'];
  $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
  $username = $db_config['username'];
  $password = $db_config['password'];
  $options = $db_config['options'];

  // Подключаемся к базе данных
  $pdo = new PDO($dsn, $username, $password, $options);

  echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>✅ Подключение к базе данных успешно!</h3>";
  echo "</div>";

  // Проверяем количество статей
  $count_sql = "SELECT COUNT(*) as total FROM articles";
  $count_stmt = $pdo->prepare($count_sql);
  $count_stmt->execute();
  $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);

  echo "<h2>📊 Статистика статей:</h2>";
  echo "<p><strong>Всего статей в БД:</strong> {$count_result['total']}</p>";

  // Получаем все статьи
  $sql = "SELECT a.*, ac.name as category_name 
            FROM articles a 
            LEFT JOIN article_categories ac ON a.category_id = ac.id 
            ORDER BY a.created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $rawArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<p><strong>Статей получено из БД:</strong> " . count($rawArticles) . "</p>";

  if (empty($rawArticles)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Статьи не найдены в БД</h3>";
    echo "</div>";
    exit;
  }

  // Показываем первые 5 статей
  echo "<h2>📋 Первые 5 статей из БД:</h2>";
  echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
  echo "<tr style='background: #f8f9fa;'>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>ID</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Название</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Автор</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Категория</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Активна</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Дата</th>";
  echo "</tr>";

  for ($i = 0; $i < min(5, count($rawArticles)); $i++) {
    $article = $rawArticles[$i];
    $title = htmlspecialchars($article['title'] ?? 'Без названия');
    $author = htmlspecialchars($article['author'] ?? 'Не указан');
    $category = htmlspecialchars($article['category_name'] ?? 'Без категории');
    $active = $article['is_active'] ? '✅' : '❌';
    $date = $article['created_at'] ? date('d.m.Y H:i', strtotime($article['created_at'])) : 'Не указана';

    echo "<tr>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$article['id']}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$title}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$author}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$category}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$active}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$date}</td>";
    echo "</tr>";
  }

  echo "</table>";

  // Проверяем, есть ли проблемы с кодировкой
  echo "<h2>🔍 Проверка кодировки:</h2>";
  $test_article = $rawArticles[0];
  echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #dee2e6;'>";
  echo "<p><strong>Название (первые 100 символов):</strong></p>";
  echo "<pre>" . htmlspecialchars(mb_substr($test_article['title'] ?? '', 0, 100)) . "</pre>";
  echo "<p><strong>Автор:</strong></p>";
  echo "<pre>" . htmlspecialchars($test_article['author'] ?? '') . "</pre>";
  echo "<p><strong>Контент (первые 200 символов):</strong></p>";
  echo "<pre>" . htmlspecialchars(mb_substr($test_article['content'] ?? '', 0, 200)) . "</pre>";
  echo "</div>";

  // Проверяем, есть ли проблемы с JSON
  echo "<h2>🔍 Проверка JSON полей:</h2>";
  if (isset($test_article['tags']) && !empty($test_article['tags'])) {
    echo "<p><strong>Теги (raw):</strong> " . htmlspecialchars($test_article['tags']) . "</p>";
    $tags_decoded = json_decode($test_article['tags'], true);
    if ($tags_decoded !== null) {
      echo "<p><strong>Теги (decoded):</strong> " . implode(', ', $tags_decoded) . "</p>";
    } else {
      echo "<p><strong>Ошибка декодирования тегов:</strong> " . json_last_error_msg() . "</p>";
    }
  }

  // Проверяем структуру таблицы
  echo "<h2>🔍 Структура таблицы articles:</h2>";
  $structure_sql = "DESCRIBE articles";
  $structure_stmt = $pdo->prepare($structure_sql);
  $structure_stmt->execute();
  $structure = $structure_stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
  echo "<tr style='background: #f8f9fa;'>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Поле</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Тип</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Null</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Ключ</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>По умолчанию</th>";
  echo "</tr>";

  foreach ($structure as $field) {
    echo "<tr>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$field['Field']}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$field['Type']}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$field['Null']}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$field['Key']}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$field['Default']}</td>";
    echo "</tr>";
  }

  echo "</table>";

  echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
  echo "<h3>🎯 Диагностика завершена!</h3>";
  echo "<p>Проверьте результаты выше. Если статьи есть в БД, но не отображаются в админ-панели, проблема может быть в:</p>";
  echo "<ul>";
  echo "<li>Обработке данных в admin/articles.php</li>";
  echo "<li>Проблемах с кодировкой при отображении</li>";
  echo "<li>Ошибках в JavaScript</li>";
  echo "<li>Проблемах с сессией или авторизацией</li>";
  echo "</ul>";
  echo "</div>";

} catch (PDOException $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>❌ Ошибка базы данных</h3>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "</div>";
} catch (Exception $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>❌ Общая ошибка</h3>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "</div>";
}
?>