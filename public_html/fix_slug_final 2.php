<?php
/**
 * Финальное исправление slug для AI-статьи
 */

echo "<h1>🔧 Финальное исправление slug</h1>";

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
  $sql = "SELECT id, title, slug FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC LIMIT 1";
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
  echo "<p><strong>Текущий slug:</strong> " . htmlspecialchars($article['slug']) . "</p>";

  // Создаем правильный slug
  $title = $article['title'];

  // Транслитерация кириллицы в латиницу
  $transliteration = [
    'а' => 'a',
    'б' => 'b',
    'в' => 'v',
    'г' => 'g',
    'д' => 'd',
    'е' => 'e',
    'ё' => 'yo',
    'ж' => 'zh',
    'з' => 'z',
    'и' => 'i',
    'й' => 'y',
    'к' => 'k',
    'л' => 'l',
    'м' => 'm',
    'н' => 'n',
    'о' => 'o',
    'п' => 'p',
    'р' => 'r',
    'с' => 's',
    'т' => 't',
    'у' => 'u',
    'ф' => 'f',
    'х' => 'h',
    'ц' => 'ts',
    'ч' => 'ch',
    'ш' => 'sh',
    'щ' => 'sch',
    'ъ' => '',
    'ы' => 'y',
    'ь' => '',
    'э' => 'e',
    'ю' => 'yu',
    'я' => 'ya',
    'А' => 'A',
    'Б' => 'B',
    'В' => 'V',
    'Г' => 'G',
    'Д' => 'D',
    'Е' => 'E',
    'Ё' => 'Yo',
    'Ж' => 'Zh',
    'З' => 'Z',
    'И' => 'I',
    'Й' => 'Y',
    'К' => 'K',
    'Л' => 'L',
    'М' => 'M',
    'Н' => 'N',
    'О' => 'O',
    'П' => 'P',
    'Р' => 'R',
    'С' => 'S',
    'Т' => 'T',
    'У' => 'U',
    'Ф' => 'F',
    'Х' => 'H',
    'Ц' => 'Ts',
    'Ч' => 'Ch',
    'Ш' => 'Sh',
    'Щ' => 'Sch',
    'Ъ' => '',
    'Ы' => 'Y',
    'Ь' => '',
    'Э' => 'E',
    'Ю' => 'Yu',
    'Я' => 'Ya'
  ];

  // Применяем транслитерацию
  $slug = strtr($title, $transliteration);

  // Убираем все символы кроме букв, цифр, пробелов и дефисов
  $slug = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $slug);

  // Заменяем пробелы на дефисы
  $slug = preg_replace('/\s+/', '-', $slug);

  // Убираем множественные дефисы
  $slug = preg_replace('/-+/', '-', $slug);

  // Убираем дефисы в начале и конце
  $slug = trim($slug, '-');

  // Переводим в нижний регистр
  $slug = strtolower($slug);

  echo "<p><strong>Новый slug:</strong> {$slug}</p>";

  // Обновляем slug в базе данных
  $update_sql = "UPDATE articles SET slug = :slug, updated_at = NOW() WHERE id = :id";
  $update_stmt = $pdo->prepare($update_sql);

  $result = $update_stmt->execute([
    ':slug' => $slug,
    ':id' => $article['id']
  ]);

  if ($result) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Slug исправлен!</h3>";
    echo "<p><strong>ID статьи:</strong> {$article['id']}</p>";
    echo "<p><strong>Новый slug:</strong> {$slug}</p>";
    echo "</div>";

    // Проверяем результат
    $check_sql = "SELECT slug FROM articles WHERE id = :id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([':id' => $article['id']]);
    $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h2>🔍 Проверка результата:</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Slug в БД:</strong> " . htmlspecialchars($check_result['slug']) . "</p>";
    echo "</div>";

    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Исправление завершено!</h3>";
    echo "<p>Проверьте результат:</p>";
    echo "<p><a href='/articles/' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>📋 Список статей</a></p>";
    echo "<p><a href='/article.php?id={$article['id']}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи (по ID)</a></p>";
    echo "<p><a href='/article.php?slug={$check_result['slug']}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи (по slug)</a></p>";
    echo "<p><a href='/admin/articles.php' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>⚙️ Админ панель</a></p>";
    echo "</div>";

  } else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Ошибка при обновлении slug</h3>";
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