<?php
$config = require_once 'config.php';
$db = $config['database'];

try {
  $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset={$db['charset']}";
  $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);

  // Убеждаемся, что соединение использует UTF-8
  $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

  echo "<h2>🧪 ТЕСТ СОХРАНЕНИЯ СТАТЬИ</h2>";

  // Тестовые данные
  $testData = [
    'title' => 'Тестовая статья с русским текстом',
    'content' => 'Это тестовое содержимое статьи с русскими символами: привет, мир!',
    'author' => 'Тестовый автор',
    'excerpt' => 'Краткое описание статьи',
    'tags' => 'тест, русский, кодировка',
    'category_id' => 1
  ];

  echo "<h3>📝 Тестовые данные:</h3>";
  echo "<pre>" . print_r($testData, true) . "</pre>";

  // Функция для обработки текста (как в API)
  $processText = function ($text) {
    if (empty($text))
      return $text;

    $text = trim($text);

    if (mb_check_encoding($text, 'UTF-8')) {
      return $text;
    }

    $fixed = mb_convert_encoding($text, 'UTF-8', 'auto');
    return $fixed;
  };

  // Обрабатываем данные
  $processedData = [
    'title' => $processText($testData['title']),
    'content' => $processText($testData['content']),
    'author' => $processText($testData['author']),
    'excerpt' => $processText($testData['excerpt']),
    'tags' => $processText($testData['tags']),
    'category_id' => $testData['category_id']
  ];

  echo "<h3>🔧 Обработанные данные:</h3>";
  echo "<pre>" . print_r($processedData, true) . "</pre>";

  // Обрабатываем теги
  $tagsValue = null;
  if ($processedData['tags'] !== '') {
    $parts = array_values(array_filter(array_map('trim', explode(',', $processedData['tags'])), fn($v) => $v !== ''));
    $parts = array_values(array_unique($parts));
    $tagsValue = json_encode($parts, JSON_UNESCAPED_UNICODE);
  }

  echo "<h3>🏷️ Обработанные теги:</h3>";
  echo "<p>Исходные: {$processedData['tags']}</p>";
  echo "<p>JSON: {$tagsValue}</p>";

  // Сохраняем тестовую статью
  echo "<h3>💾 Сохранение в базу...</h3>";

  $sql = "INSERT INTO articles (title, content, author, excerpt, tags, category_id, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
  $stmt = $pdo->prepare($sql);
  $result = $stmt->execute([
    $processedData['title'],
    $processedData['content'],
    $processedData['author'],
    $processedData['excerpt'],
    $tagsValue,
    $processedData['category_id']
  ]);

  if ($result) {
    $newId = $pdo->lastInsertId();
    echo "<p>✅ Статья сохранена с ID: {$newId}</p>";

    // Проверяем, что сохранилось
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$newId]);
    $saved = $stmt->fetch();

    echo "<h3>📖 Что сохранилось в базе:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Поле</th><th>Значение</th></tr>";
    foreach ($saved as $field => $value) {
      if (in_array($field, ['title', 'content', 'author', 'excerpt', 'tags'])) {
        $display = htmlspecialchars($value);
        echo "<tr><td><strong>{$field}</strong></td><td>{$display}</td></tr>";
      }
    }
    echo "</table>";

    // Удаляем тестовую статью
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$newId]);
    echo "<p>🗑️ Тестовая статья удалена</p>";

  } else {
    echo "<p>❌ Ошибка сохранения</p>";
  }

} catch (Exception $e) {
  echo "<h3>❌ Ошибка:</h3>";
  echo "<p>" . $e->getMessage() . "</p>";
}
?>