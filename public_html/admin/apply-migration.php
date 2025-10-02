<?php
/**
 * Веб-интерфейс для применения миграции комментариев
 * Доступен только администраторам
 */

session_start();
require_once '../includes/functions.php';

// Проверяем авторизацию администратора
if (!isAdminAccess()) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_migration'])) {
    try {
        require_once '../includes/Database.php';
        $db = Database::getInstance();
        
        // Читаем SQL файл
        $sqlFile = __DIR__ . '/../database/comments.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("Файл миграции не найден: {$sqlFile}");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Разбиваем на отдельные запросы
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        $appliedQueries = [];
        $skippedQueries = [];
        
        foreach ($queries as $query) {
            if (empty($query)) continue;
            
            try {
                $db->exec($query);
                $appliedQueries[] = substr($query, 0, 50) . '...';
            } catch (Exception $e) {
                // Игнорируем ошибки "таблица уже существует" и "столбец уже существует"
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate column') !== false) {
                    $skippedQueries[] = substr($query, 0, 50) . '... (уже существует)';
                } else {
                    throw $e;
                }
            }
        }
        
        $message = "Миграция успешно применена!<br>";
        $message .= "Применено запросов: " . count($appliedQueries) . "<br>";
        $message .= "Пропущено запросов: " . count($skippedQueries) . "<br>";
        $message .= "Созданы таблицы: comments, comment_likes, comment_reports, telegram_users<br>";
        $message .= "Обновлена таблица: reviews (добавлены поля Telegram)";
        
    } catch (Exception $e) {
        $error = "Ошибка при применении миграции: " . $e->getMessage();
    }
}

// Проверяем, применена ли уже миграция
$migrationApplied = false;
try {
    require_once '../includes/Database.php';
    $db = Database::getInstance();
    
    // Проверяем существование таблицы comments
    $result = $db->fetchOne("SHOW TABLES LIKE 'comments'");
    $migrationApplied = !empty($result);
} catch (Exception $e) {
    // Игнорируем ошибки подключения
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Применение миграции - Админ панель</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
        }
        .status {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-box h3 {
            margin-top: 0;
            color: #495057;
        }
        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Назад в админ панель</a>
        
        <h1>Применение миграции комментариев</h1>
        
        <?php if ($migrationApplied): ?>
            <div class="status info">
                <strong>Статус:</strong> Миграция уже применена. Таблица 'comments' существует в базе данных.
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="status success">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="status error">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <h3>Что делает эта миграция:</h3>
            <ul>
                <li>Создает таблицу <code>comments</code> для хранения комментариев</li>
                <li>Создает таблицу <code>comment_likes</code> для лайков комментариев</li>
                <li>Создает таблицу <code>comment_reports</code> для жалоб на комментарии</li>
                <li>Создает таблицу <code>telegram_users</code> для пользователей Telegram</li>
                <li>Обновляет таблицу <code>reviews</code> (добавляет поля Telegram)</li>
            </ul>
        </div>
        
        <div class="info-box">
            <h3>Безопасность:</h3>
            <ul>
                <li>Миграция безопасна - не удаляет существующие данные</li>
                <li>Если таблицы уже существуют, они будут пропущены</li>
                <li>Можно запускать несколько раз без вреда</li>
            </ul>
        </div>
        
        <form method="POST">
            <button type="submit" name="apply_migration" class="btn">
                Применить миграцию
            </button>
        </form>
        
        <div class="info-box">
            <h3>После применения миграции:</h3>
            <ul>
                <li>Комментарии будут доступны на страницах статей и товаров</li>
                <li>Пользователи смогут авторизоваться через Telegram</li>
                <li>Администратор будет получать уведомления о новых комментариях</li>
                <li>Все комментарии будут проходить модерацию</li>
            </ul>
        </div>
    </div>
</body>
</html>
