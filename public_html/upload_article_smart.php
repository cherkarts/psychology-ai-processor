<?php
/**
 * Умный скрипт для загрузки AI-сгенерированной статьи на сайт
 * Автоматически определяет структуру таблицы и использует только существующие колонки
 */

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

  'tags' => ['психическое здоровье', 'самопомощь', 'психология'],
  'category_id' => 1,
  'author' => 'AI Assistant',
  'is_active' => 1
];

echo "<h1>🤖 Умная загрузка AI-статьи</h1>";

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

  // Получаем структуру таблицы articles
  $sql = "DESCRIBE articles";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $existing_columns = [];
  foreach ($columns as $column) {
    $existing_columns[] = $column['Field'];
  }

  echo "<h2>📊 Найденные колонки в таблице articles:</h2>";
  echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<p>" . implode(', ', $existing_columns) . "</p>";
  echo "</div>";

  // Подготавливаем данные для вставки
  $insert_data = [];
  $insert_columns = [];
  $insert_values = [];

  // Маппинг данных статьи на колонки БД
  $column_mapping = [
    'title' => $article_data['title'],
    'content' => $article_data['content'],
    'excerpt' => $article_data['excerpt'],
    'meta_title' => $article_data['meta_title'],
    'meta_description' => $article_data['meta_description'],
    'tags' => json_encode($article_data['tags'], JSON_UNESCAPED_UNICODE),
    'category_id' => $article_data['category_id'],
    'is_active' => $article_data['is_active'],
    'author' => $article_data['author']
  ];

  // Добавляем временные колонки, если они существуют
  if (in_array('created_at', $existing_columns)) {
    $column_mapping['created_at'] = 'NOW()';
  }
  if (in_array('updated_at', $existing_columns)) {
    $column_mapping['updated_at'] = 'NOW()';
  }

  // Формируем SQL запрос только с существующими колонками
  foreach ($column_mapping as $column => $value) {
    if (in_array($column, $existing_columns)) {
      $insert_columns[] = $column;

      if ($value === 'NOW()') {
        $insert_values[] = 'NOW()';
      } else {
        $insert_values[] = ":$column";
        $insert_data[":$column"] = $value;
      }
    }
  }

  // Создаем SQL запрос
  $sql = "INSERT INTO articles (" . implode(', ', $insert_columns) . ") VALUES (" . implode(', ', $insert_values) . ")";

  echo "<h2>🔧 SQL запрос:</h2>";
  echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<pre style='background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto;'>";
  echo htmlspecialchars($sql);
  echo "</pre>";
  echo "</div>";

  // Выполняем запрос
  $stmt = $pdo->prepare($sql);
  $result = $stmt->execute($insert_data);

  if ($result) {
    $article_id = $pdo->lastInsertId();

    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h2>🎉 Статья успешно добавлена!</h2>";
    echo "<p><strong>ID статьи:</strong> {$article_id}</p>";
    echo "<p><strong>Название:</strong> {$article_data['title']}</p>";
    echo "<p><strong>Автор:</strong> {$article_data['author']}</p>";
    echo "<p><strong>Категория ID:</strong> {$article_data['category_id']}</p>";
    echo "<p><strong>Теги:</strong> " . implode(', ', $article_data['tags']) . "</p>";
    echo "<p><strong>Статус:</strong> " . ($article_data['is_active'] ? 'Активна' : 'Неактивна') . "</p>";
    echo "</div>";

    echo "<h2>🔗 Ссылки:</h2>";
    echo "<div style='background: #f0fff0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><a href='/articles/' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>📋 Список статей</a></p>";
    echo "<p><a href='/article.php?id={$article_id}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи</a></p>";
    echo "<p><a href='/admin/articles.php' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>⚙️ Админ панель</a></p>";
    echo "<p><a href='/check_ai_article.php' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>🔍 Проверить AI-статьи</a></p>";
    echo "</div>";

  } else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h2>❌ Ошибка при добавлении статьи</h2>";
    echo "<p>Не удалось добавить статью в базу данных.</p>";
    echo "</div>";
  }

} catch (PDOException $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h2>❌ Ошибка базы данных</h2>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "<p>Проверьте настройки подключения к базе данных в config.php</p>";
  echo "</div>";
} catch (Exception $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h2>❌ Общая ошибка</h2>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "</div>";
}

echo "<hr>";
echo "<h2>📊 Информация о статье:</h2>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<p><strong>Длина контента:</strong> " . strlen($article_data['content']) . " символов</p>";
echo "<p><strong>Длина excerpt:</strong> " . strlen($article_data['excerpt']) . " символов</p>";
echo "<p><strong>Количество тегов:</strong> " . count($article_data['tags']) . "</p>";
echo "<p><strong>Дата создания:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "</div>";
?>