<?php
/**
 * Исправление slug и изображения для AI-статьи
 */

echo "<h1>🔧 Исправление slug и изображения</h1>";

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

  // Находим AI-статью
  $sql = "SELECT id, title, slug, featured_image FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $article = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$article) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ AI-статьи не найдены</h3>";
    echo "</div>";
    exit;
  }

  echo "<h2>📄 Найдена статья ID: {$article['id']}</h2>";
  echo "<p><strong>Название:</strong> " . htmlspecialchars($article['title']) . "</p>";
  echo "<p><strong>Текущий slug:</strong> " . ($article['slug'] ?: 'ПУСТОЙ') . "</p>";
  echo "<p><strong>Изображение:</strong> " . ($article['featured_image'] ?: 'НЕТ') . "</p>";

  // Создаем slug из названия
  $title = $article['title'];
  $slug = strtolower($title);
  $slug = preg_replace('/[^a-zа-я0-9\s\-]/u', '', $slug);
  $slug = preg_replace('/\s+/', '-', $slug);
  $slug = trim($slug, '-');

  echo "<p><strong>Новый slug:</strong> {$slug}</p>";

  // Получаем изображение с Unsplash
  $image_url = '';
  try {
    // Простой запрос к Unsplash API для получения изображения по теме "psychology"
    $unsplash_url = 'https://api.unsplash.com/photos/random?query=psychology&orientation=landscape&client_id=YOUR_ACCESS_KEY';

    // Для демонстрации используем заглушку
    $image_url = 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=400&fit=crop&crop=faces';

    echo "<p><strong>Изображение Unsplash:</strong> {$image_url}</p>";

  } catch (Exception $e) {
    echo "<p style='color: orange;'><strong>⚠️ Ошибка получения изображения:</strong> " . $e->getMessage() . "</p>";
    // Используем заглушку
    $image_url = 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=400&fit=crop&crop=faces';
  }

  // Обновляем статью
  $update_sql = "UPDATE articles SET slug = :slug, featured_image = :featured_image, updated_at = NOW() WHERE id = :id";
  $update_stmt = $pdo->prepare($update_sql);

  $result = $update_stmt->execute([
    ':slug' => $slug,
    ':featured_image' => $image_url,
    ':id' => $article['id']
  ]);

  if ($result) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Статья обновлена!</h3>";
    echo "<p><strong>Slug:</strong> {$slug}</p>";
    echo "<p><strong>Изображение:</strong> {$image_url}</p>";
    echo "</div>";

    // Проверяем результат
    $check_sql = "SELECT slug, featured_image FROM articles WHERE id = :id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([':id' => $article['id']]);
    $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h2>🔍 Проверка результата:</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Slug в БД:</strong> " . ($check_result['slug'] ?: 'ПУСТОЙ') . "</p>";
    echo "<p><strong>Изображение в БД:</strong> " . ($check_result['featured_image'] ?: 'НЕТ') . "</p>";
    echo "</div>";

    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Исправление завершено!</h3>";
    echo "<p>Проверьте результат:</p>";
    echo "<p><a href='/articles/' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>📋 Список статей</a></p>";
    echo "<p><a href='/article.php?id={$article['id']}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи (по ID)</a></p>";
    if ($check_result['slug']) {
      echo "<p><a href='/article.php?slug={$check_result['slug']}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи (по slug)</a></p>";
    }
    echo "<p><a href='/admin/articles.php' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>⚙️ Админ панель</a></p>";
    echo "</div>";

  } else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Ошибка при обновлении статьи</h3>";
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