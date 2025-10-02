<?php
/**
 * Пересоздание AI-статьи
 */

echo "<h1>🤖 Пересоздание AI-статьи</h1>";

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

  // Данные статьи
  $article_data = [
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

  // Создаем slug из названия
  $title = $article_data['title'];
  $slug = strtolower($title);
  $slug = preg_replace('/[^a-zа-я0-9\s\-]/u', '', $slug);
  $slug = preg_replace('/\s+/', '-', $slug);
  $slug = trim($slug, '-');

  // Добавляем изображение
  $image_url = 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=400&fit=crop&crop=faces';

  echo "<h2>📄 Создаем статью:</h2>";
  echo "<p><strong>Название:</strong> " . htmlspecialchars($article_data['title']) . "</p>";
  echo "<p><strong>Slug:</strong> {$slug}</p>";
  echo "<p><strong>Автор:</strong> {$article_data['author']}</p>";
  echo "<p><strong>Категория ID:</strong> {$article_data['category_id']}</p>";
  echo "<p><strong>Изображение:</strong> {$image_url}</p>";

  // Проверяем, есть ли уже такая статья
  $check_sql = "SELECT id FROM articles WHERE author = 'AI Assistant' AND title = :title";
  $check_stmt = $pdo->prepare($check_sql);
  $check_stmt->execute([':title' => $article_data['title']]);
  $existing = $check_stmt->fetch();

  if ($existing) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>⚠️ Статья уже существует (ID: {$existing['id']})</h3>";
    echo "<p>Обновляем существующую статью...</p>";
    echo "</div>";

    // Обновляем существующую статью
    $update_sql = "UPDATE articles SET 
            title = :title, 
            content = :content, 
            excerpt = :excerpt, 
            meta_title = :meta_title, 
            meta_description = :meta_description, 
            tags = :tags, 
            category_id = :category_id, 
            is_active = :is_active, 
            slug = :slug,
            featured_image = :featured_image,
            updated_at = NOW() 
            WHERE id = :id";

    $update_stmt = $pdo->prepare($update_sql);

    $result = $update_stmt->execute([
      ':title' => $article_data['title'],
      ':content' => $article_data['content'],
      ':excerpt' => $article_data['excerpt'],
      ':meta_title' => $article_data['meta_title'],
      ':meta_description' => $article_data['meta_description'],
      ':tags' => $article_data['tags'],
      ':category_id' => $article_data['category_id'],
      ':is_active' => $article_data['is_active'],
      ':slug' => $slug,
      ':featured_image' => $image_url,
      ':id' => $existing['id']
    ]);

    if ($result) {
      $article_id = $existing['id'];
      echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
      echo "<h3>✅ Статья обновлена!</h3>";
      echo "<p><strong>ID статьи:</strong> {$article_id}</p>";
      echo "</div>";
    } else {
      echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
      echo "<h3>❌ Ошибка при обновлении статьи</h3>";
      echo "</div>";
      exit;
    }

  } else {
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>🆕 Создаем новую статью...</h3>";
    echo "</div>";

    // Создаем новую статью
    $insert_sql = "INSERT INTO articles (
            title, content, excerpt, meta_title, meta_description, 
            tags, category_id, is_active, author, slug, featured_image, created_at, updated_at
        ) VALUES (
            :title, :content, :excerpt, :meta_title, :meta_description,
            :tags, :category_id, :is_active, :author, :slug, :featured_image, NOW(), NOW()
        )";

    $insert_stmt = $pdo->prepare($insert_sql);

    $result = $insert_stmt->execute([
      ':title' => $article_data['title'],
      ':content' => $article_data['content'],
      ':excerpt' => $article_data['excerpt'],
      ':meta_title' => $article_data['meta_title'],
      ':meta_description' => $article_data['meta_description'],
      ':tags' => $article_data['tags'],
      ':category_id' => $article_data['category_id'],
      ':is_active' => $article_data['is_active'],
      ':author' => $article_data['author'],
      ':slug' => $slug,
      ':featured_image' => $image_url
    ]);

    if ($result) {
      $article_id = $pdo->lastInsertId();
      echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
      echo "<h3>✅ Статья создана!</h3>";
      echo "<p><strong>ID статьи:</strong> {$article_id}</p>";
      echo "</div>";
    } else {
      echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
      echo "<h3>❌ Ошибка при создании статьи</h3>";
      echo "</div>";
      exit;
    }
  }

  echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
  echo "<h3>🎉 Статья готова!</h3>";
  echo "<p>Проверьте результат:</p>";
  echo "<p><a href='/articles/' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>📋 Список статей</a></p>";
  echo "<p><a href='/article.php?id={$article_id}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи (по ID)</a></p>";
  echo "<p><a href='/article.php?slug={$slug}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи (по slug)</a></p>";
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