<?php
/**
 * Исправление последней AI-статьи
 */

echo "<h1>🔧 Исправление последней AI-статьи</h1>";

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

  // Находим последнюю AI-статью
  $sql = "SELECT id, title, content, excerpt, author, slug, featured_image FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC LIMIT 1";
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
  echo "<p><strong>Текущее название:</strong> " . htmlspecialchars($article['title']) . "</p>";
  echo "<p><strong>Текущий slug:</strong> " . ($article['slug'] ?: 'ПУСТОЙ') . "</p>";
  echo "<p><strong>Автор:</strong> {$article['author']}</p>";
  echo "<p><strong>Изображение:</strong> " . ($article['featured_image'] ?: 'НЕТ') . "</p>";

  // Правильные данные статьи
  $correct_data = [
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

  // Создаем правильный slug
  $title = $correct_data['title'];
  $slug = strtolower($title);
  $slug = preg_replace('/[^a-zа-я0-9\s\-]/u', '', $slug);
  $slug = preg_replace('/\s+/', '-', $slug);
  $slug = trim($slug, '-');

  echo "<p><strong>Правильный slug:</strong> {$slug}</p>";

  // Устанавливаем правильную кодировку для соединения
  $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
  $pdo->exec("SET CHARACTER SET utf8mb4");

  // Полностью перезаписываем статью
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
        updated_at = NOW() 
        WHERE id = :id";

  $update_stmt = $pdo->prepare($update_sql);

  $result = $update_stmt->execute([
    ':title' => $correct_data['title'],
    ':content' => $correct_data['content'],
    ':excerpt' => $correct_data['excerpt'],
    ':meta_title' => $correct_data['meta_title'],
    ':meta_description' => $correct_data['meta_description'],
    ':tags' => $correct_data['tags'],
    ':category_id' => $correct_data['category_id'],
    ':is_active' => $correct_data['is_active'],
    ':slug' => $slug,
    ':id' => $article['id']
  ]);

  if ($result) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Статья исправлена!</h3>";
    echo "<p><strong>ID статьи:</strong> {$article['id']}</p>";
    echo "<p><strong>Название:</strong> " . htmlspecialchars($correct_data['title']) . "</p>";
    echo "<p><strong>Slug:</strong> {$slug}</p>";
    echo "<p><strong>Автор:</strong> {$correct_data['author']}</p>";
    echo "<p><strong>Категория ID:</strong> {$correct_data['category_id']}</p>";
    echo "<p><strong>Теги:</strong> психическое здоровье, самопомощь, психология</p>";
    echo "<p><strong>Статус:</strong> Активна</p>";
    echo "</div>";

    // Проверяем результат
    $check_sql = "SELECT title, content, excerpt, slug FROM articles WHERE id = :id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([':id' => $article['id']]);
    $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h2>🔍 Проверка результата:</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Название в БД:</strong> " . htmlspecialchars($check_result['title']) . "</p>";
    echo "<p><strong>Slug в БД:</strong> " . ($check_result['slug'] ?: 'ПУСТОЙ') . "</p>";
    echo "<p><strong>Длина контента:</strong> " . strlen($check_result['content']) . " символов</p>";
    echo "<p><strong>Длина excerpt:</strong> " . strlen($check_result['excerpt']) . " символов</p>";
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