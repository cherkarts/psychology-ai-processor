<?php
/**
 * Комплексный скрипт для исправления всех проблем с AI-статьями
 */

echo "<h1>🔧 Комплексное исправление AI-статей</h1>";

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

  // Правильные данные статьи
  $correct_article_data = [
    'title' => 'Психология: Ключ к пониманию себя и других',
    'content' => '<h1>Психология: Ключ к пониманию себя и других</h1>
<p>В самом центре нашей жизни, как воздух, окружающий нас, находится психология. Она влияет на наше поведение, решения и отношения, оставаясь зачастую незамеченной. Но насколько важно понимать психологические процессы для собственного благополучия и отношений с окружающими!</p>

<h2>Взгляд на проблему через российский контекст</h2>
<p>Психология в России и Беларуси часто воспринимается с опаской и недоверием. Многие считают, что обращение к психологу – признак слабости или даже позора. Это стереотип, который мешает людям получить необходимую помощь в решении своих проблем.</p>

<h2>Скрытые истины и неудобные факты</h2>
<p>Одной из главных тайн, о которой молчат психологи, является то, что большинство психологических проблем решаемы. Не важно, страдаете ли вы от тревожности, депрессии или конфликтов в отношениях – есть способы выйти из этого состояния и изменить свою жизнь к лучшему.</p>

<h2>Конкретные советы и упражнения</h2>
<p>Первый шаг к изменениям – обратиться к специалисту. Психотерапевт поможет вам разобраться в своих эмоциях, мыслях и поведенческих паттернах. Кроме того, практика самопомощи играет важную роль. Начните с ведения дневника эмоций, практики медитации или физических упражнений для снятия стресса.</p>

<h2>Мотивирующий призыв к действию</h2>
<p>Помните, что психология – это нечто большее, чем просто наука. Это ключ к пониманию себя и других, к гармонии внутри и вокруг нас. Решение любой проблемы начинается с осознания ее существования. Не стесняйтесь обращаться за помощь и помните, что каждый шаг к изменениям приближает вас к лучшей жизни.</p>',

    'excerpt' => 'Психология: Ключ к пониманию себя и других. В самом центре нашей жизни, как воздух, окружающий нас, находится психология. Она влияет на наше поведение, решения и отношения, оставаясь зачастую незамеченной...',

    'meta_title' => 'Психология: Ключ к пониманию себя и других',
    'meta_description' => 'Психология: Ключ к пониманию себя и других. В самом центре нашей жизни, как воздух, окружающий нас, находится психология. Она влияет на наше поведение, решения и отношения...',

    'tags' => json_encode(['психическое здоровье', 'самопомощь', 'психология'], JSON_UNESCAPED_UNICODE),
    'category_id' => 1,
    'author' => 'AI Assistant',
    'is_active' => 1
  ];

  // Ищем AI-статьи
  $sql = "SELECT id, title, content, excerpt, meta_title, meta_description, tags, category_id, author, is_active FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo "<h2>📊 Найденные AI-статьи:</h2>";
  echo "<p>Найдено статей: " . count($articles) . "</p>";

  if (empty($articles)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ AI-статьи не найдены</h3>";
    echo "</div>";
    exit;
  }

  $fixed_count = 0;
  $deleted_count = 0;

  foreach ($articles as $index => $article) {
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #007bff;'>";
    echo "<h3>📄 Статья ID: {$article['id']}</h3>";
    echo "<p><strong>Автор:</strong> {$article['author']}</p>";
    echo "<p><strong>Название (текущее):</strong> " . htmlspecialchars($article['title']) . "</p>";
    echo "<p><strong>Категория ID:</strong> {$article['category_id']}</p>";

    // Если это первая статья, исправляем её
    if ($index === 0) {
      echo "<p style='color: blue;'><strong>🔄 Исправляем первую статью...</strong></p>";

      // Обновляем статью правильными данными
      $update_sql = "UPDATE articles SET 
                title = :title, 
                content = :content, 
                excerpt = :excerpt, 
                meta_title = :meta_title, 
                meta_description = :meta_description, 
                tags = :tags, 
                category_id = :category_id, 
                is_active = :is_active, 
                updated_at = NOW() 
                WHERE id = :id";

      $update_stmt = $pdo->prepare($update_sql);

      $result = $update_stmt->execute([
        ':title' => $correct_article_data['title'],
        ':content' => $correct_article_data['content'],
        ':excerpt' => $correct_article_data['excerpt'],
        ':meta_title' => $correct_article_data['meta_title'],
        ':meta_description' => $correct_article_data['meta_description'],
        ':tags' => $correct_article_data['tags'],
        ':category_id' => $correct_article_data['category_id'],
        ':is_active' => $correct_article_data['is_active'],
        ':id' => $article['id']
      ]);

      if ($result) {
        echo "<p style='color: green;'><strong>✅ Статья исправлена!</strong></p>";
        echo "<p><strong>Название (исправленное):</strong> " . htmlspecialchars($correct_article_data['title']) . "</p>";
        $fixed_count++;
      } else {
        echo "<p style='color: red;'><strong>❌ Ошибка при исправлении статьи</strong></p>";
      }

    } else {
      // Остальные дублирующие статьи удаляем
      echo "<p style='color: orange;'><strong>🗑️ Удаляем дублирующую статью...</strong></p>";

      $delete_sql = "DELETE FROM articles WHERE id = :id";
      $delete_stmt = $pdo->prepare($delete_sql);

      $result = $delete_stmt->execute([':id' => $article['id']]);

      if ($result) {
        echo "<p style='color: green;'><strong>✅ Дублирующая статья удалена</strong></p>";
        $deleted_count++;
      } else {
        echo "<p style='color: red;'><strong>❌ Ошибка при удалении статьи</strong></p>";
      }
    }

    echo "</div>";
  }

  echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
  echo "<h3>🎉 Исправление завершено!</h3>";
  echo "<p><strong>Исправлено статей:</strong> {$fixed_count}</p>";
  echo "<p><strong>Удалено дублирующих статей:</strong> {$deleted_count}</p>";
  echo "<p>Проверьте результат:</p>";
  echo "<p><a href='/articles/' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>📋 Список статей</a></p>";
  echo "<p><a href='/admin/articles.php' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>⚙️ Админ панель</a></p>";
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