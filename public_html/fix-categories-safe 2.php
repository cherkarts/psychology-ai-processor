<?php
$config = require_once 'config.php';
$db = $config['database'];

try {
  $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset={$db['charset']}";
  $pdo = new PDO($dsn, $db['username'], $db['password'], $db['options']);

  echo "<h2>🔧 БЕЗОПАСНОЕ ИСПРАВЛЕНИЕ КАТЕГОРИЙ</h2>";

  echo "<h3>❌ Проблема:</h3>";
  echo "<p>Нельзя удалить 'article_categories' - на неё ссылаются статьи через внешний ключ</p>";

  echo "<h3>🎯 Безопасный план:</h3>";
  echo "<ol>";
  echo "<li>Скопировать чистые категории из 'categories' в 'article_categories'</li>";
  echo "<li>Обновить ссылки в статьях на правильные ID</li>";
  echo "<li>Удалить старую таблицу 'categories'</li>";
  echo "</ol>";

  echo "<h3>🚀 Выполняем исправление...</h3>";

  // 1. Очищаем старую таблицу article_categories
  echo "<p>1️⃣ Очищаем старую таблицу 'article_categories'...</p>";
  $pdo->exec("DELETE FROM article_categories");
  echo "<p>✅ Старая таблица очищена</p>";

  // 2. Копируем чистые категории
  echo "<p>2️⃣ Копируем чистые категории из 'categories' в 'article_categories'...</p>";
  $pdo->exec("INSERT INTO article_categories (id, name, slug, created_at) SELECT id, name, slug, created_at FROM categories");
  echo "<p>✅ Категории скопированы</p>";

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

  // 4. Проверяем статьи
  echo "<h3>📊 Проверяем статьи:</h3>";
  $stmt = $pdo->query("SELECT a.id, a.title, a.category_id, ac.name as category_name FROM articles a LEFT JOIN article_categories ac ON a.category_id = ac.id WHERE a.category_id IS NOT NULL ORDER BY a.id");
  $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<p>Найдено " . count($articles) . " статей с категориями:</p>";
  echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
  echo "<tr style='background: #f0f0f0;'><th>ID статьи</th><th>Заголовок</th><th>Category ID</th><th>Название категории</th></tr>";

  foreach ($articles as $article) {
    $title = mb_substr($article['title'] ?: 'Без заголовка', 0, 50) . '...';
    $category_name = $article['category_name'] ?: 'НЕ НАЙДЕНА';
    $style = $article['category_name'] ? '' : 'style="background: #ffcccc;"';

    echo "<tr {$style}>";
    echo "<td>{$article['id']}</td>";
    echo "<td>{$title}</td>";
    echo "<td>{$article['category_id']}</td>";
    echo "<td><strong>{$category_name}</strong></td>";
    echo "</tr>";
  }
  echo "</table>";

  // 5. Удаляем временную таблицу
  echo "<p>4️⃣ Удаляем временную таблицу 'categories'...</p>";
  $pdo->exec("DROP TABLE categories");
  echo "<p>✅ Временная таблица удалена</p>";

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