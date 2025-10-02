<?php
$config = require_once 'config.php';
$db = $config['database'];

try {
  $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset={$db['charset']}";
  $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);

  echo "<h2>🔧 ФИНАЛЬНОЕ ИСПРАВЛЕНИЕ КАТЕГОРИЙ</h2>";

  echo "<h3>📊 Текущая ситуация:</h3>";
  echo "<p>✅ Таблица 'categories' - 6 чистых категорий</p>";
  echo "<p>⚠️ Таблица 'article_categories' - 11 записей с кракозябрами</p>";

  echo "<h3>🎯 План действий:</h3>";
  echo "<ol>";
  echo "<li>Удалить старую таблицу 'article_categories'</li>";
  echo "<li>Переименовать 'categories' → 'article_categories'</li>";
  echo "<li>Проверить результат</li>";
  echo "</ol>";

  echo "<h3>🚀 Выполняем исправление...</h3>";

  // 1. Удаляем старую таблицу
  echo "<p>1️⃣ Удаляем старую таблицу 'article_categories'...</p>";
  $pdo->exec("DROP TABLE IF EXISTS article_categories");
  echo "<p>✅ Старая таблица удалена</p>";

  // 2. Переименовываем чистую таблицу
  echo "<p>2️⃣ Переименовываем 'categories' → 'article_categories'...</p>";
  $pdo->exec("RENAME TABLE categories TO article_categories");
  echo "<p>✅ Таблица переименована</p>";

  // 3. Проверяем результат
  echo "<p>3️⃣ Проверяем результат...</p>";
  $stmt = $pdo->query("SELECT * FROM article_categories ORDER BY id");
  $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<p>✅ Найдено " . count($categories) . " категорий в таблице 'article_categories'</p>";

  echo "<h3>📋 Итоговые категории:</h3>";
  echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
  echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Название</th><th>Slug</th><th>Создано</th></tr>";

  foreach ($categories as $cat) {
    echo "<tr>";
    echo "<td>{$cat['id']}</td>";
    echo "<td><strong>{$cat['name']}</strong></td>";
    echo "<td>{$cat['slug']}</td>";
    echo "<td>{$cat['created_at']}</td>";
    echo "</tr>";
  }
  echo "</table>";

  echo "<h3>🎉 ГОТОВО!</h3>";
  echo "<p><strong>Теперь админка должна отображать все 6 категорий:</strong></p>";
  echo "<ul>";
  foreach ($categories as $cat) {
    echo "<li>✅ {$cat['name']}</li>";
  }
  echo "</ul>";

  echo "<p><strong>📤 Следующий шаг:</strong> Обновите страницу админки (F5) и перейдите в раздел 'Категории статей'</p>";

} catch (Exception $e) {
  echo "<h3>❌ Ошибка:</h3>";
  echo "<p>" . $e->getMessage() . "</p>";
}
?>