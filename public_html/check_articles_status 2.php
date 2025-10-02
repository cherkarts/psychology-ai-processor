<?php
/**
 * Проверка статуса статей в базе данных
 */

echo "<h1>🔍 Проверка статуса статей</h1>";

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

  // Проверяем все статьи
  $sql = "SELECT id, title, author, slug, created_at, is_active FROM articles ORDER BY created_at DESC LIMIT 10";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h2>📋 Последние 10 статей:</h2>";

  if (empty($articles)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Статьи не найдены</h3>";
    echo "</div>";
  } else {
    echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>ID</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Название</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Автор</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Slug</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Дата</th>";
    echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Активна</th>";
    echo "</tr>";

    foreach ($articles as $article) {
      $title = htmlspecialchars($article['title'] ?? 'Без названия');
      $author = htmlspecialchars($article['author'] ?? 'Не указан');
      $slug = htmlspecialchars($article['slug'] ?? 'Не указан');
      $date = $article['created_at'] ? date('d.m.Y H:i', strtotime($article['created_at'])) : 'Не указана';
      $active = $article['is_active'] ? '✅' : '❌';

      echo "<tr>";
      echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$article['id']}</td>";
      echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$title}</td>";
      echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$author}</td>";
      echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$slug}</td>";
      echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$date}</td>";
      echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$active}</td>";
      echo "</tr>";
    }

    echo "</table>";
  }

  // Проверяем статьи с автором "AI Assistant"
  echo "<h2>🤖 Статьи с автором 'AI Assistant':</h2>";
  $sql = "SELECT id, title, author, slug, created_at FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $aiArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (empty($aiArticles)) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>⚠️ Статьи с автором 'AI Assistant' не найдены</h3>";
    echo "</div>";
  } else {
    echo "<p>Найдено статей: " . count($aiArticles) . "</p>";
    foreach ($aiArticles as $article) {
      echo "<div style='background: #f0f8ff; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #b3d9ff;'>";
      echo "<p><strong>ID:</strong> {$article['id']}</p>";
      echo "<p><strong>Название:</strong> " . htmlspecialchars($article['title'] ?? 'Без названия') . "</p>";
      echo "<p><strong>Автор:</strong> " . htmlspecialchars($article['author']) . "</p>";
      echo "<p><strong>Slug:</strong> " . htmlspecialchars($article['slug'] ?? 'Не указан') . "</p>";
      echo "<p><strong>Дата:</strong> " . ($article['created_at'] ? date('d.m.Y H:i', strtotime($article['created_at'])) : 'Не указана') . "</p>";
      echo "</div>";
    }
  }

  // Проверяем статьи с автором "Денис Черкас"
  echo "<h2>👨‍⚕️ Статьи с автором 'Денис Черкас':</h2>";
  $sql = "SELECT id, title, author, slug, created_at FROM articles WHERE author = 'Денис Черкас' ORDER BY created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $denisArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (empty($denisArticles)) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>⚠️ Статьи с автором 'Денис Черкас' не найдены</h3>";
    echo "</div>";
  } else {
    echo "<p>Найдено статей: " . count($denisArticles) . "</p>";
    foreach ($denisArticles as $article) {
      echo "<div style='background: #f0f8ff; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #b3d9ff;'>";
      echo "<p><strong>ID:</strong> {$article['id']}</p>";
      echo "<p><strong>Название:</strong> " . htmlspecialchars($article['title'] ?? 'Без названия') . "</p>";
      echo "<p><strong>Автор:</strong> " . htmlspecialchars($article['author']) . "</p>";
      echo "<p><strong>Slug:</strong> " . htmlspecialchars($article['slug'] ?? 'Не указан') . "</p>";
      echo "<p><strong>Дата:</strong> " . ($article['created_at'] ? date('d.m.Y H:i', strtotime($article['created_at'])) : 'Не указана') . "</p>";
      echo "</div>";
    }
  }

  // Проверяем последнюю статью
  echo "<h2>📄 Последняя статья:</h2>";
  $sql = "SELECT id, title, author, slug, created_at, content FROM articles ORDER BY created_at DESC LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $lastArticle = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($lastArticle) {
    echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #0066cc;'>";
    echo "<p><strong>ID:</strong> {$lastArticle['id']}</p>";
    echo "<p><strong>Название:</strong> " . htmlspecialchars($lastArticle['title'] ?? 'Без названия') . "</p>";
    echo "<p><strong>Автор:</strong> " . htmlspecialchars($lastArticle['author'] ?? 'Не указан') . "</p>";
    echo "<p><strong>Slug:</strong> " . htmlspecialchars($lastArticle['slug'] ?? 'Не указан') . "</p>";
    echo "<p><strong>Дата:</strong> " . ($lastArticle['created_at'] ? date('d.m.Y H:i', strtotime($lastArticle['created_at'])) : 'Не указана') . "</p>";
    echo "<p><strong>Длина контента:</strong> " . strlen($lastArticle['content'] ?? '') . " символов</p>";

    // Показываем первые 200 символов контента
    if (!empty($lastArticle['content'])) {
      echo "<p><strong>Начало контента:</strong></p>";
      echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #dee2e6; max-height: 200px; overflow-y: auto;'>";
      echo htmlspecialchars(mb_substr($lastArticle['content'], 0, 500)) . "...";
      echo "</div>";
    }

    echo "</div>";

    // Ссылки для проверки
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🔗 Ссылки для проверки:</h3>";
    echo "<p><a href='/article.php?id={$lastArticle['id']}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи (по ID)</a></p>";
    if (!empty($lastArticle['slug'])) {
      echo "<p><a href='/article.php?slug={$lastArticle['slug']}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи (по slug)</a></p>";
    }
    echo "<p><a href='/admin/articles.php' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>⚙️ Админ панель</a></p>";
    echo "</div>";
  } else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Статьи не найдены</h3>";
    echo "</div>";
  }

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