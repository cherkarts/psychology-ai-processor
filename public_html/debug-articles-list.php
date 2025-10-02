<?php
$config = require_once 'config.php';
$db = $config['database'];

try {
  $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset={$db['charset']}";
  $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);

  echo "<h2>🔍 ДЕБАГ СПИСКА СТАТЕЙ</h2>";

  // Получаем статьи как в админке
  $stmt = $pdo->query("
        SELECT a.*, ac.name as category_name 
        FROM articles a 
        LEFT JOIN article_categories ac ON a.category_id = ac.id 
        ORDER BY a.created_at DESC
        LIMIT 5
    ");
  $articles = $stmt->fetchAll();

  echo "<h3>📊 Сырые данные из базы (первые 5 статей):</h3>";

  foreach ($articles as $i => $article) {
    echo "<h4>Статья ID: {$article['id']}</h4>";
    echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";

    // Показываем все поля
    foreach ($article as $field => $value) {
      $display_value = $value;
      if (is_null($value)) {
        $display_value = '<em>NULL</em>';
      } elseif (empty($value)) {
        $display_value = '<em>ПУСТО</em>';
      } else {
        // Показываем первые 100 символов
        $display_value = htmlspecialchars(mb_substr($value, 0, 100));
        if (mb_strlen($value) > 100) {
          $display_value .= '...';
        }
      }

      echo "<tr>";
      echo "<td style='background: #f0f0f0; padding: 5px;'><strong>{$field}</strong></td>";
      echo "<td style='padding: 5px;'>{$display_value}</td>";
      echo "</tr>";
    }
    echo "</table>";
  }

  echo "<h3>🔧 Проверка кодировки:</h3>";
  if (!empty($articles)) {
    $first_article = $articles[0];
    echo "<p><strong>Title (raw):</strong> " . bin2hex($first_article['title']) . "</p>";
    echo "<p><strong>Author (raw):</strong> " . bin2hex($first_article['author']) . "</p>";

    // Попробуем разные способы декодирования
    echo "<h4>Попытки декодирования title:</h4>";
    echo "<p>1. Как есть: " . htmlspecialchars($first_article['title']) . "</p>";
    echo "<p>2. UTF-8 decode: " . htmlspecialchars(utf8_decode($first_article['title'])) . "</p>";
    echo "<p>3. JSON decode: " . htmlspecialchars(json_decode('"' . $first_article['title'] . '"')) . "</p>";
    echo "<p>4. Iconv CP1251->UTF8: " . htmlspecialchars(iconv('CP1251', 'UTF-8', $first_article['title'])) . "</p>";
  }

} catch (Exception $e) {
  echo "<h3>❌ Ошибка:</h3>";
  echo "<p>" . $e->getMessage() . "</p>";
}
?>