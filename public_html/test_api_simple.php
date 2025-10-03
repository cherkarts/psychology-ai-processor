<?php
/**
 * Простой тест API
 */

echo "<h1>🧪 Простой тест API</h1>";

// Тестовые данные
$test_data = [
    'title' => 'Простой тест API',
    'content' => '<h1>Простой тест</h1><p>Это простой тест API.</p>',
    'excerpt' => 'Простой тест API',
    'author' => 'Test',
    'tags' => 'тест',
    'category_id' => 1
];

echo "<h2>📝 Тестовые данные:</h2>";
echo "<pre>" . json_encode($test_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";

// Тестируем API напрямую
echo "<h2>🔧 Тест API напрямую:</h2>";

try {
    // Подключаем API файл напрямую
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['HTTP_USER_AGENT'] = 'Test-Script/1.0';
    
    // Имитируем POST данные
    $json_data = json_encode($test_data, JSON_UNESCAPED_UNICODE);
    
    // Создаем временный файл с данными
    $temp_file = tempnam(sys_get_temp_dir(), 'api_test');
    file_put_contents($temp_file, $json_data);
    
    // Перенаправляем php://input
    $original_input = 'php://input';
    
    // Включаем API файл
    ob_start();
    include 'upload_ai_article.php';
    $output = ob_get_clean();
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ API работает!</h3>";
    echo "<p><strong>Ответ:</strong></p>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    echo "</div>";
    
    // Очищаем временный файл
    unlink($temp_file);
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Ошибка API</h3>";
    echo "<p><strong>Сообщение:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}

// Тестируем cURL
echo "<h2>🌐 Тест через cURL:</h2>";

$url = 'https://cherkas-therapy.ru/upload_ai_article.php';
$data = json_encode($test_data, JSON_UNESCAPED_UNICODE);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: Test-Script/1.0'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Ошибка cURL</h3>";
    echo "<p><strong>Сообщение:</strong> $error</p>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ cURL работает!</h3>";
    echo "<p><strong>HTTP код:</strong> $http_code</p>";
    echo "<p><strong>Ответ:</strong></p>";
    echo "<pre>" . htmlspecialchars($result) . "</pre>";
    echo "</div>";
}

// Проверяем базу данных
echo "<h2>🗄️ Проверка базы данных:</h2>";

try {
    $config = require 'config.php';
    $dsn = "mysql:host={$config['database']['host']};port={$config['database']['port']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);
    
    // Проверяем последние AI статьи
    $stmt = $pdo->prepare("SELECT id, title, slug, author, created_at FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($articles) {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Название</th><th>Slug</th><th>Автор</th><th>Дата</th></tr>";
        
        foreach ($articles as $article) {
            echo "<tr>";
            echo "<td>{$article['id']}</td>";
            echo "<td>" . htmlspecialchars($article['title']) . "</td>";
            echo "<td>" . htmlspecialchars($article['slug'] ?: 'ПУСТОЙ') . "</td>";
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
echo "<p><a href='test_api_simple.php'>Запустить тест снова</a></p>";
?>
