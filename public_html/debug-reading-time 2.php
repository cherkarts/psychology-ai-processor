<?php
$config = require_once 'config.php';
$db = $config['database'];

try {
  $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset={$db['charset']}";
  $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);

  echo "<h2>🔍 ДЕБАГ ВРЕМЕНИ ЧТЕНИЯ</h2>";

  // Функция расчета времени чтения (как в коде)
  function calculateReadingTime($content)
  {
    $wordsPerMinute = 200; // Average reading speed
    $text = strip_tags($content);
    $wordCount = str_word_count($text, 0, 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя');
    $minutes = ceil($wordCount / $wordsPerMinute);

    if ($minutes < 1) {
      return '1 минута';
    } elseif ($minutes == 1) {
      return '1 минута';
    } elseif ($minutes < 5) {
      return $minutes . ' минуты';
    } else {
      return $minutes . ' минут';
    }
  }

  // Получаем несколько статей
  $stmt = $pdo->query("
        SELECT a.id, a.title, a.content, a.excerpt, ac.name as category_name
        FROM articles a 
        LEFT JOIN article_categories ac ON a.category_id = ac.id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");
  $articles = $stmt->fetchAll();

  echo "<h3>📊 Анализ времени чтения для статей:</h3>";

  foreach ($articles as $article) {
    echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 15px;'>";
    echo "<h4>Статья ID: {$article['id']}</h4>";
    echo "<p><strong>Заголовок:</strong> " . htmlspecialchars(mb_substr($article['title'], 0, 100)) . "...</p>";

    // Анализируем content
    $content = $article['content'];
    echo "<p><strong>Content длина:</strong> " . strlen($content) . " символов</p>";
    echo "<p><strong>Content первые 200 символов:</strong> " . htmlspecialchars(mb_substr($content, 0, 200)) . "...</p>";

    // Проверяем, есть ли проблемы с кодировкой
    if (strpos($content, 'Р') !== false && strpos($content, 'С') !== false) {
      echo "<p style='color: red;'><strong>⚠️ Проблема с кодировкой в content!</strong></p>";
    }

    // Рассчитываем время чтения
    $readingTime = calculateReadingTime($content);
    echo "<p><strong>Время чтения:</strong> {$readingTime}</p>";

    // Детальный анализ
    $text = strip_tags($content);
    $wordCount = str_word_count($text, 0, 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя');
    echo "<p><strong>Количество слов:</strong> {$wordCount}</p>";
    echo "<p><strong>Текст без тегов (первые 300 символов):</strong> " . htmlspecialchars(mb_substr($text, 0, 300)) . "...</p>";

    echo "</div>";
  }

} catch (Exception $e) {
  echo "<h3>❌ Ошибка:</h3>";
  echo "<p>" . $e->getMessage() . "</p>";
}
?>