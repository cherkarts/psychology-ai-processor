<?php
$config = require_once 'config.php';
$db = $config['database'];

try {
  $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset={$db['charset']}";
  $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);

  echo "<h2>🔧 ИСПРАВЛЕНИЕ ВРЕМЕНИ ЧТЕНИЯ</h2>";

  // Улучшенная функция расчета времени чтения
  function calculateReadingTimeFixed($content)
  {
    if (empty($content)) {
      return '1 минута';
    }

    $wordsPerMinute = 200; // Average reading speed

    // Удаляем HTML теги
    $text = strip_tags($content);

    // Если текст пустой после удаления тегов
    if (empty(trim($text))) {
      return '1 минута';
    }

    // Проверяем на проблемы с кодировкой
    if (strpos($text, 'Р') !== false && strpos($text, 'С') !== false) {
      // Пытаемся исправить кодировку
      $fixed = @iconv('CP1251', 'UTF-8', $text);
      if ($fixed !== false) {
        $text = $fixed;
      }
    }

    // Подсчитываем слова с поддержкой русского языка
    $wordCount = str_word_count($text, 0, 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя');

    // Если не удалось подсчитать слова, используем приблизительный подсчет
    if ($wordCount === 0) {
      $wordCount = ceil(strlen($text) / 6); // Примерно 6 символов на слово
    }

    $minutes = ceil($wordCount / $wordsPerMinute);

    // Минимум 1 минута
    if ($minutes < 1) {
      $minutes = 1;
    }

    if ($minutes == 1) {
      return '1 минута';
    } elseif ($minutes < 5) {
      return $minutes . ' минуты';
    } else {
      return $minutes . ' минут';
    }
  }

  // Получаем статьи
  $stmt = $pdo->query("
        SELECT a.id, a.title, a.content, a.excerpt, ac.name as category_name
        FROM articles a 
        LEFT JOIN article_categories ac ON a.category_id = ac.id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");
  $articles = $stmt->fetchAll();

  echo "<h3>📊 Сравнение времени чтения:</h3>";

  foreach ($articles as $article) {
    echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 15px;'>";
    echo "<h4>Статья ID: {$article['id']}</h4>";
    echo "<p><strong>Заголовок:</strong> " . htmlspecialchars(mb_substr($article['title'], 0, 80)) . "...</p>";

    $content = $article['content'];

    // Старая функция
    $oldTime = calculateReadingTime($content);

    // Новая функция
    $newTime = calculateReadingTimeFixed($content);

    echo "<p><strong>Старое время:</strong> <span style='color: red;'>{$oldTime}</span></p>";
    echo "<p><strong>Новое время:</strong> <span style='color: green;'>{$newTime}</span></p>";

    // Детали
    $text = strip_tags($content);
    $wordCount = str_word_count($text, 0, 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя');
    echo "<p><strong>Количество слов:</strong> {$wordCount}</p>";

    echo "</div>";
  }

  echo "<h3>✅ РЕШЕНИЕ:</h3>";
  echo "<p>Нужно заменить функцию <code>calculateReadingTime</code> в файлах:</p>";
  echo "<ul>";
  echo "<li><code>articles.php</code></li>";
  echo "<li><code>article.php</code></li>";
  echo "<li><code>articles/index.php</code></li>";
  echo "</ul>";

} catch (Exception $e) {
  echo "<h3>❌ Ошибка:</h3>";
  echo "<p>" . $e->getMessage() . "</p>";
}
?>