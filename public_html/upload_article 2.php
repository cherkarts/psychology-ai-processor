<?php
/**
 * Скрипт для загрузки AI-сгенерированной статьи на сайт
 * Загрузите этот файл на ваш сайт и запустите через браузер
 */

// Настройки
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

// Подключаем конфигурацию базы данных
$config = require_once 'config.php';

try {
    // Извлекаем настройки базы данных
    $db_config = $config['database'];
    $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
    $username = $db_config['username'];
    $password = $db_config['password'];
    $options = $db_config['options'];

    // Подключаемся к базе данных
    $pdo = new PDO($dsn, $username, $password, $options);

    // Подготавливаем данные
    $title = $article_data['title'];
    $content = $article_data['content'];
    $excerpt = $article_data['excerpt'];
    $meta_title = $article_data['meta_title'];
    $meta_description = $article_data['meta_description'];
    $tags = json_encode($article_data['tags'], JSON_UNESCAPED_UNICODE);
    $category_id = $article_data['category_id'];
    $author = $article_data['author'];
    $is_active = $article_data['is_active'];

    // SQL запрос для вставки
    $sql = "INSERT INTO articles (
        title, content, excerpt, meta_title, meta_description, 
        tags, category_id, is_active, author, created_at, updated_at
    ) VALUES (
        :title, :content, :excerpt, :meta_title, :meta_description,
        :tags, :category_id, :is_active, :author, NOW(), NOW()
    )";

    $stmt = $pdo->prepare($sql);

    // Выполняем запрос
    $result = $stmt->execute([
        ':title' => $title,
        ':content' => $content,
        ':excerpt' => $excerpt,
        ':meta_title' => $meta_title,
        ':meta_description' => $meta_description,
        ':tags' => $tags,
        ':category_id' => $category_id,
        ':is_active' => $is_active,
        ':author' => $author
    ]);

    if ($result) {
        $article_id = $pdo->lastInsertId();
        echo "<h1>✅ Статья успешно добавлена!</h1>";
        echo "<p><strong>ID статьи:</strong> {$article_id}</p>";
        echo "<p><strong>Название:</strong> {$title}</p>";
        echo "<p><strong>Автор:</strong> {$author}</p>";
        echo "<p><strong>Категория ID:</strong> {$category_id}</p>";
        echo "<p><strong>Теги:</strong> " . implode(', ', $article_data['tags']) . "</p>";
        echo "<p><strong>Статус:</strong> " . ($is_active ? 'Активна' : 'Неактивна') . "</p>";

        echo "<h2>🔗 Ссылки:</h2>";
        echo "<p><a href='/articles/' target='_blank'>Список статей</a></p>";
        echo "<p><a href='/article.php?id={$article_id}' target='_blank'>Просмотр статьи</a></p>";
        echo "<p><a href='/admin/articles.php' target='_blank'>Админ панель</a></p>";

    } else {
        echo "<h1>❌ Ошибка при добавлении статьи</h1>";
        echo "<p>Не удалось добавить статью в базу данных.</p>";
    }

} catch (PDOException $e) {
    echo "<h1>❌ Ошибка базы данных</h1>";
    echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Проверьте настройки подключения к базе данных в config.php</p>";
} catch (Exception $e) {
    echo "<h1>❌ Общая ошибка</h1>";
    echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>📊 Информация о статье:</h2>";
echo "<p><strong>Длина контента:</strong> " . strlen($content) . " символов</p>";
echo "<p><strong>Длина excerpt:</strong> " . strlen($excerpt) . " символов</p>";
echo "<p><strong>Количество тегов:</strong> " . count($article_data['tags']) . "</p>";
echo "<p><strong>Дата создания:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>