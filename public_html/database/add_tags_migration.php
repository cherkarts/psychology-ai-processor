<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$status = '';
$message = '';
$tagsExists = false;
$columns = [];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_column'])) {
    try {
        $config = require __DIR__ . '/../config.php';
        
        $socket = $config['database']['socket'] ?? null;
        if ($socket) {
            $dsn = "mysql:unix_socket={$socket};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
        } else {
            $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
        }
        
        $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);
        
        // Check if column exists
        $stmt = $pdo->query("DESCRIBE articles");
        $currentColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('tags', $currentColumns)) {
            $status = 'warning';
            $message = 'Колонка tags уже существует в таблице articles';
        } else {
            // Add the column
            $pdo->exec("ALTER TABLE articles ADD COLUMN tags JSON NULL AFTER featured_image");
            $status = 'success';
            $message = 'Колонка tags успешно добавлена!';
        }
        
    } catch (Exception $e) {
        $status = 'error';
        $message = 'Ошибка: ' . $e->getMessage();
    }
}

// Check current status
try {
    if (!isset($config)) {
        $config = require __DIR__ . '/../config.php';
    }
    
    $socket = $config['database']['socket'] ?? null;
    if ($socket) {
        $dsn = "mysql:unix_socket={$socket};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
    } else {
        $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
    }
    
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);
    
    $stmt = $pdo->query("DESCRIBE articles");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $tagsExists = in_array('tags', $columns);
    
    if (!$status) {
        $status = 'info';
        $message = $tagsExists ? 'Колонка tags уже существует' : 'Колонка tags отсутствует';
    }
    
} catch (Exception $e) {
    if (!$status) {
        $status = 'error';
        $message = 'Ошибка подключения: ' . $e->getMessage();
    }
}
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление колонки tags</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #27ae60; background: #d5f4e6; padding: 15px; border-radius: 4px; margin: 15px 0; border-left: 4px solid #27ae60; }
        .error { color: #e74c3c; background: #fadbd8; padding: 15px; border-radius: 4px; margin: 15px 0; border-left: 4px solid #e74c3c; }
        .info { color: #3498db; background: #ebf3fd; padding: 15px; border-radius: 4px; margin: 15px 0; border-left: 4px solid #3498db; }
        .warning { color: #f39c12; background: #fef5e7; padding: 15px; border-radius: 4px; margin: 15px 0; border-left: 4px solid #f39c12; }
        .btn { background: #3498db; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; }
        .btn:hover { background: #2980b9; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #219a52; }
        h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .columns { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Добавление колонки tags</h1>
        
        <?php if ($status === 'success'): ?>
            <div class="success">✅ <?php echo htmlspecialchars($message); ?></div>
        <?php elseif ($status === 'error'): ?>
            <div class="error">❌ <?php echo htmlspecialchars($message); ?></div>
        <?php elseif ($status === 'warning'): ?>
            <div class="warning">⚠️ <?php echo htmlspecialchars($message); ?></div>
        <?php else: ?>
            <div class="info">ℹ️ <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($columns)): ?>
            <div class="columns">
                <h4>Колонки в таблице articles:</h4>
                <?php foreach ($columns as $column): ?>
                    <div><?php echo $column === 'tags' ? "<strong style='color: #27ae60;'>✓ $column</strong>" : $column; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$tagsExists && $status !== 'error'): ?>
            <form method="POST" style="margin: 20px 0;">
                <button type="submit" name="add_column" value="1" class="btn btn-success">
                    🚀 Добавить колонку tags
                </button>
            </form>
        <?php elseif ($tagsExists): ?>
            <div class="success">
                🎉 Готово! Колонка tags существует. Теперь можно включить функциональность тегов в редакторе статей.
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <a href="../admin/articles.php" class="btn">← Вернуться к статьям</a>
        </div>
    </div>
</body>
</html>