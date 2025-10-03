<?php
/**
 * Тестовый скрипт для проверки загрузки AI-статей
 */

echo "<h1>🧪 Тест загрузки AI-статей</h1>";

// Тестовые данные
$test_article = [
    'title' => 'Тестовая статья: Психология стресса',
    'content' => '<h1>Тестовая статья: Психология стресса</h1><p>Это тестовая статья для проверки загрузки AI-статей. Она содержит информацию о стрессе и способах его преодоления.</p><h2>Что такое стресс?</h2><p>Стресс - это естественная реакция организма на внешние раздражители. В малых дозах он может быть полезным, но хронический стресс вреден для здоровья.</p><h2>Способы преодоления стресса</h2><ul><li>Дыхательные упражнения</li><li>Медитация</li><li>Физическая активность</li><li>Правильное питание</li></ul><p>Регулярное применение этих техник поможет снизить уровень стресса и улучшить качество жизни.</p>',
    'excerpt' => 'Тестовая статья о психологии стресса и способах его преодоления. Содержит практические советы и техники.',
    'author' => 'AI Assistant',
    'tags' => 'стресс, психология, самопомощь, медитация',
    'category_id' => 1,
    'featured_image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
    'meta_title' => 'Тестовая статья: Психология стресса',
    'meta_description' => 'Тестовая статья о психологии стресса и способах его преодоления'
];

echo "<h2>📝 Тестовые данные:</h2>";
echo "<pre>" . json_encode($test_article, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

// Отправляем запрос к API
$url = 'https://cherkas-therapy.ru/upload_ai_article.php';
$data = json_encode($test_article, JSON_UNESCAPED_UNICODE);

$options = [
    'http' => [
        'header' => [
            'Content-Type: application/json',
            'User-Agent: Test-Script/1.0'
        ],
        'method' => 'POST',
        'content' => $data
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "<h2>📤 Результат загрузки:</h2>";

if ($result === FALSE) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Ошибка загрузки</h3>";
    echo "<p>Не удалось отправить запрос к API</p>";
    echo "</div>";
} else {
    $response = json_decode($result, true);
    
    if ($response && $response['success']) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ Статья успешно загружена!</h3>";
        echo "<p><strong>ID:</strong> {$response['id']}</p>";
        echo "<p><strong>Slug:</strong> {$response['slug']}</p>";
        echo "<p><strong>Название:</strong> {$response['title']}</p>";
        echo "<p><strong>Сообщение:</strong> {$response['message']}</p>";
        echo "</div>";
        
        echo "<h3>🔗 Ссылки:</h3>";
        echo "<p><a href='https://cherkas-therapy.ru/article.php?slug={$response['slug']}' target='_blank'>Открыть статью на сайте</a></p>";
        echo "<p><a href='https://cherkas-therapy.ru/admin/articles.php' target='_blank'>Админ панель</a></p>";
        
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>❌ Ошибка API</h3>";
        echo "<p><strong>Сообщение:</strong> " . ($response['message'] ?? 'Неизвестная ошибка') . "</p>";
        echo "<p><strong>Полный ответ:</strong></p>";
        echo "<pre>" . htmlspecialchars($result) . "</pre>";
        echo "</div>";
    }
}

echo "<h2>🔍 Проверка в базе данных:</h2>";

try {
    $config = require 'config.php';
    $dsn = "mysql:host={$config['database']['host']};port={$config['database']['port']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);
    
    $stmt = $pdo->prepare("SELECT id, title, slug, author, created_at FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($articles) {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Название</th><th>Slug</th><th>Автор</th><th>Дата</th></tr>";
        
        foreach ($articles as $article) {
            echo "<tr>";
            echo "<td>{$article['id']}</td>";
            echo "<td>" . htmlspecialchars($article['title']) . "</td>";
            echo "<td>" . htmlspecialchars($article['slug']) . "</td>";
            echo "<td>{$article['author']}</td>";
            echo "<td>{$article['created_at']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>AI-статьи не найдены в базе данных</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Ошибка подключения к базе данных: " . $e->getMessage() . "</p>";
}

echo "<h2>🔄 Повторить тест:</h2>";
echo "<p><a href='test_ai_upload.php'>Запустить тест снова</a></p>";
?>
