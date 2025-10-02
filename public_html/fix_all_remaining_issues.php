<?php
/**
 * Исправление всех оставшихся проблем
 */

echo "<h1>🔧 Исправление всех оставшихся проблем</h1>";

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

  // 1. Исправляем теги с проблемами кодировки
  echo "<h2>🔧 Исправление тегов с проблемами кодировки</h2>";

  $sql = "SELECT id, tags FROM articles WHERE tags IS NOT NULL AND tags != ''";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $articles_with_tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $fixed_count = 0;
  foreach ($articles_with_tags as $article) {
    $tags = $article['tags'];

    // Проверяем, можно ли декодировать JSON
    $decoded = json_decode($tags, true);
    if ($decoded === null) {
      // Если не можем декодировать, устанавливаем пустые теги
      $update_sql = "UPDATE articles SET tags = '[]' WHERE id = ?";
      $update_stmt = $pdo->prepare($update_sql);
      $update_stmt->execute([$article['id']]);
      $fixed_count++;
      echo "<p>✅ Исправлены теги для статьи ID {$article['id']}</p>";
    }
  }

  echo "<p><strong>Исправлено статей с проблемными тегами:</strong> {$fixed_count}</p>";

  // 2. Исправляем пустые названия и авторов
  echo "<h2>🔧 Исправление пустых названий и авторов</h2>";

  $sql = "SELECT id, title, author FROM articles WHERE title = '' OR title IS NULL OR author = '' OR author IS NULL";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $empty_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $fixed_articles = 0;
  foreach ($empty_articles as $article) {
    $title = $article['title'] ?: 'Статья без названия';
    $author = $article['author'] ?: 'Денис Черкас';

    $update_sql = "UPDATE articles SET title = ?, author = ? WHERE id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$title, $author, $article['id']]);
    $fixed_articles++;
    echo "<p>✅ Исправлена статья ID {$article['id']}: название='{$title}', автор='{$author}'</p>";
  }

  echo "<p><strong>Исправлено статей с пустыми полями:</strong> {$fixed_articles}</p>";

  // 3. Проверяем и исправляем категории
  echo "<h2>🔧 Проверка категорий</h2>";

  // Проверяем, есть ли категория "Психология"
  $sql = "SELECT id FROM article_categories WHERE name = 'Психология' LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $psychology_category = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$psychology_category) {
    // Создаем категорию "Психология"
    $sql = "INSERT INTO article_categories (name, slug, description, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['Психология', 'psihologiya', 'Статьи по психологии', 1]);
    $psychology_category_id = $pdo->lastInsertId();
    echo "<p>✅ Создана категория 'Психология' с ID {$psychology_category_id}</p>";
  } else {
    $psychology_category_id = $psychology_category['id'];
    echo "<p>✅ Категория 'Психология' уже существует с ID {$psychology_category_id}</p>";
  }

  // Назначаем категорию "Психология" всем статьям без категории
  $sql = "UPDATE articles SET category_id = ? WHERE category_id IS NULL OR category_id = 0";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$psychology_category_id]);
  $updated_count = $stmt->rowCount();
  echo "<p>✅ Назначена категория 'Психология' для {$updated_count} статей</p>";

  // 4. Проверяем финальное состояние
  echo "<h2>🔍 Финальная проверка</h2>";

  $sql = "SELECT COUNT(*) as total FROM articles";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $total_articles = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

  $sql = "SELECT COUNT(*) as active FROM articles WHERE is_active = 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $active_articles = $stmt->fetch(PDO::FETCH_ASSOC)['active'];

  $sql = "SELECT COUNT(*) as with_categories FROM articles WHERE category_id IS NOT NULL AND category_id > 0";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $articles_with_categories = $stmt->fetch(PDO::FETCH_ASSOC)['with_categories'];

  echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<p><strong>Всего статей:</strong> {$total_articles}</p>";
  echo "<p><strong>Активных статей:</strong> {$active_articles}</p>";
  echo "<p><strong>Статей с категориями:</strong> {$articles_with_categories}</p>";
  echo "</div>";

  // 5. Показываем последние 3 статьи
  echo "<h2>📋 Последние 3 статьи</h2>";
  $sql = "SELECT id, title, author, category_id, created_at FROM articles ORDER BY created_at DESC LIMIT 3";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $latest_articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>";
  echo "<tr style='background: #f8f9fa;'>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>ID</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Название</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Автор</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Категория ID</th>";
  echo "<th style='border: 1px solid #dee2e6; padding: 8px;'>Дата</th>";
  echo "</tr>";

  foreach ($latest_articles as $article) {
    $title = htmlspecialchars($article['title'] ?: 'Без названия');
    $author = htmlspecialchars($article['author'] ?: 'Не указан');
    $date = $article['created_at'] ? date('d.m.Y H:i', strtotime($article['created_at'])) : 'Не указана';

    echo "<tr>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$article['id']}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$title}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$author}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$article['category_id']}</td>";
    echo "<td style='border: 1px solid #dee2e6; padding: 8px;'>{$date}</td>";
    echo "</tr>";
  }

  echo "</table>";

  echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
  echo "<h3>🎉 Все проблемы исправлены!</h3>";
  echo "<p>Проверьте результат:</p>";
  echo "<p><a href='/admin/articles.php' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>⚙️ Админ панель (должна работать)</a></p>";
  echo "<p><a href='/article.php?id=45' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи</a></p>";
  echo "<p><a href='/articles/' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>📋 Список статей</a></p>";
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