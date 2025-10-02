<?php
/**
 * API для работы с комментариями (исправленная версия)
 * Поддерживает комментарии к статьям и товарам с Telegram авторизацией
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';

// Запускаем сессию
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getInstance();

// Получаем метод и действие
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGetComments($db, $action);
            break;
        case 'POST':
            handlePostComment($db, $action);
            break;
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Обработка GET запросов
 */
function handleGetComments($db, $action)
{
    switch ($action) {
        case 'list':
            getCommentsList($db);
            break;
        case 'count':
            getCommentsCount($db);
            break;
        default:
            throw new Exception('Invalid action');
    }
}

/**
 * Обработка POST запросов
 */
function handlePostComment($db, $action)
{
    switch ($action) {
        case 'add':
            addComment($db);
            break;
        case 'like':
            likeComment($db);
            break;
        case 'report':
            reportComment($db);
            break;
        default:
            throw new Exception('Invalid action');
    }
}

/**
 * Получить список комментариев
 */
function getCommentsList($db)
{
    $contentType = $_GET['content_type'] ?? '';
    $contentId = $_GET['content_id'] ?? '';
    $page = (int) ($_GET['page'] ?? 1);
    $limit = (int) ($_GET['limit'] ?? 10);
    $offset = ($page - 1) * $limit;

    if (empty($contentType) || empty($contentId)) {
        // Возвращаем пустой список вместо ошибки
        echo json_encode([
            'success' => true,
            'data' => [],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'has_more' => false
            ]
        ]);
        return;
    }

    $sql = "SELECT c.*, 
                   COUNT(cl.id) as likes_count,
                   CASE WHEN cl_user.id IS NOT NULL THEN 1 ELSE 0 END as user_liked
            FROM comments c
            LEFT JOIN comment_likes cl ON c.id = cl.comment_id
            LEFT JOIN comment_likes cl_user ON c.id = cl_user.comment_id AND cl_user.user_id = ?
            WHERE c.content_type = ? AND c.content_id = ? AND c.status = 'approved'
            GROUP BY c.id
            ORDER BY c.created_at DESC
            LIMIT ? OFFSET ?";

    $params = [
        $_SESSION['telegram_user']['id'] ?? null,
        $contentType,
        $contentId,
        $limit,
        $offset
    ];

    $comments = $db->fetchAll($sql, $params);

    // Форматируем комментарии
    foreach ($comments as &$comment) {
        $comment['created_at_formatted'] = formatDate($comment['created_at']);
        $comment['user_avatar'] = $comment['telegram_avatar'] ?? 'https://via.placeholder.com/40x40/6a7e9f/ffffff?text=' . substr($comment['telegram_username'], 0, 1);
    }

    echo json_encode([
        'success' => true,
        'data' => $comments,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'has_more' => count($comments) === $limit
        ]
    ]);
}

/**
 * Получить количество комментариев
 */
function getCommentsCount($db)
{
    $contentType = $_GET['content_type'] ?? '';
    $contentId = $_GET['content_id'] ?? '';

    if (empty($contentType) || empty($contentId)) {
        echo json_encode([
            'success' => true,
            'count' => 0
        ]);
        return;
    }

    $sql = "SELECT COUNT(*) as count FROM comments 
            WHERE content_type = ? AND content_id = ? AND status = 'approved'";

    $result = $db->fetchOne($sql, [$contentType, $contentId]);

    echo json_encode([
        'success' => true,
        'count' => (int) $result['count']
    ]);
}

/**
 * Добавить комментарий
 */
function addComment($db)
{
    // Проверяем авторизацию через Telegram
    if (!isset($_SESSION['telegram_user'])) {
        throw new Exception('Telegram authorization required');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    // Отладочная информация
    error_log('Comments API: Получены данные: ' . json_encode($input));

    // Валидация
    $required = ['content_type', 'content_id', 'text'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Field {$field} is required");
        }
    }

    // Проверяем длину комментария
    if (strlen($input['text']) < 3) {
        throw new Exception('Comment must be at least 3 characters long');
    }

    if (strlen($input['text']) > 1000) {
        throw new Exception('Comment must be no more than 1000 characters');
    }

    // Проверяем, не спамит ли пользователь
    $telegramUserId = $_SESSION['telegram_user']['id'];
    $sql = "SELECT COUNT(*) as count FROM comments 
            WHERE telegram_user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";

    $result = $db->fetchOne($sql, [$telegramUserId]);
    if ($result['count'] >= 5) {
        throw new Exception('Too many comments. Please wait before posting again.');
    }

    // Подготавливаем данные
    $data = [
        'content_type' => $input['content_type'],
        'content_id' => $input['content_id'],
        'text' => trim($input['text']),
        'telegram_user_id' => $telegramUserId,
        'telegram_username' => $_SESSION['telegram_user']['username'] ?? '',
        'telegram_first_name' => $_SESSION['telegram_user']['first_name'] ?? '',
        'telegram_last_name' => $_SESSION['telegram_user']['last_name'] ?? '',
        'telegram_avatar' => $_SESSION['telegram_user']['photo_url'] ?? '',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'status' => 'pending' // Комментарии требуют модерации
    ];

    // Сохраняем комментарий
    $commentId = $db->insert('comments', $data);

    // Отправляем уведомление администратору
    try {
        $notification = "💬 <b>Новый комментарий</b>\n\n";
        $notification .= "👤 <b>Пользователь:</b> " . $data['telegram_first_name'] . " " . $data['telegram_last_name'] . "\n";
        $notification .= "📝 <b>Текст:</b> " . substr($data['text'], 0, 100) . "...\n";
        $notification .= "📍 <b>Тип:</b> " . $data['content_type'] . "\n";
        $notification .= "🆔 <b>ID:</b> " . $data['content_id'] . "\n";
        $notification .= "📅 <b>Дата:</b> " . date('d.m.Y H:i:s');

        $config = require_once __DIR__ . '/../config.php';
        if (!empty($config['telegram']['bot_token']) && !empty($config['telegram']['chat_id'])) {
            $telegramUrl = "https://api.telegram.org/bot{$config['telegram']['bot_token']}/sendMessage";
            $telegramData = [
                'chat_id' => $config['telegram']['chat_id'],
                'text' => $notification,
                'parse_mode' => 'HTML'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $telegramUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $telegramData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            curl_close($ch);
        }
    } catch (Exception $e) {
        // Логируем ошибку, но не прерываем процесс
        error_log('Telegram notification error: ' . $e->getMessage());
    }

    error_log('Comments API: Комментарий успешно сохранен с ID: ' . $commentId);

    echo json_encode([
        'success' => true,
        'message' => 'Комментарий отправлен на модерацию',
        'comment_id' => $commentId
    ]);
}

/**
 * Лайкнуть комментарий
 */
function likeComment($db)
{
    if (!isset($_SESSION['telegram_user'])) {
        throw new Exception('Telegram authorization required');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $commentId = $input['comment_id'] ?? null;
    if (!$commentId) {
        throw new Exception('comment_id is required');
    }

    $telegramUserId = $_SESSION['telegram_user']['id'];

    // Проверяем, не лайкал ли уже пользователь этот комментарий
    $sql = "SELECT id FROM comment_likes WHERE comment_id = ? AND user_id = ?";
    $existing = $db->fetchOne($sql, [$commentId, $telegramUserId]);

    if ($existing) {
        // Убираем лайк
        $db->delete('comment_likes', 'id = ?', [$existing['id']]);
        $action = 'unliked';
    } else {
        // Добавляем лайк
        $db->insert('comment_likes', [
            'comment_id' => $commentId,
            'user_id' => $telegramUserId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $action = 'liked';
    }

    // Получаем новое количество лайков
    $sql = "SELECT COUNT(*) as count FROM comment_likes WHERE comment_id = ?";
    $result = $db->fetchOne($sql, [$commentId]);

    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes_count' => (int) $result['count']
    ]);
}

/**
 * Пожаловаться на комментарий
 */
function reportComment($db)
{
    if (!isset($_SESSION['telegram_user'])) {
        throw new Exception('Telegram authorization required');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $commentId = $input['comment_id'] ?? null;
    $reason = $input['reason'] ?? '';

    if (!$commentId) {
        throw new Exception('comment_id is required');
    }

    $telegramUserId = $_SESSION['telegram_user']['id'];

    // Проверяем, не жаловался ли уже пользователь на этот комментарий
    $sql = "SELECT id FROM comment_reports WHERE comment_id = ? AND user_id = ?";
    $existing = $db->fetchOne($sql, [$commentId, $telegramUserId]);

    if ($existing) {
        throw new Exception('You have already reported this comment');
    }

    // Добавляем жалобу
    $db->insert('comment_reports', [
        'comment_id' => $commentId,
        'user_id' => $telegramUserId,
        'reason' => $reason,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // Отправляем уведомление администратору
    try {
        $notification = "⚠️ <b>Жалоба на комментарий</b>\n\n";
        $notification .= "🆔 <b>ID комментария:</b> " . $commentId . "\n";
        $notification .= "👤 <b>Жалоба от:</b> " . $_SESSION['telegram_user']['first_name'] . "\n";
        $notification .= "📝 <b>Причина:</b> " . ($reason ?: 'Не указана') . "\n";
        $notification .= "📅 <b>Дата:</b> " . date('d.m.Y H:i:s');

        $config = require_once __DIR__ . '/../config.php';
        if (!empty($config['telegram']['bot_token']) && !empty($config['telegram']['chat_id'])) {
            $telegramUrl = "https://api.telegram.org/bot{$config['telegram']['bot_token']}/sendMessage";
            $telegramData = [
                'chat_id' => $config['telegram']['chat_id'],
                'text' => $notification,
                'parse_mode' => 'HTML'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $telegramUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $telegramData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_exec($ch);
            curl_close($ch);
        }
    } catch (Exception $e) {
        // Логируем ошибку, но не прерываем процесс
        error_log('Telegram notification error: ' . $e->getMessage());
    }

    echo json_encode([
        'success' => true,
        'message' => 'Жалоба отправлена'
    ]);
}


/**
 * Форматировать дату
 */
function formatDate($date)
{
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 60) {
        return 'только что';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' мин назад';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' ч назад';
    } elseif ($diff < 2592000) {
        return floor($diff / 86400) . ' дн назад';
    } else {
        return date('d.m.Y', $timestamp);
    }
}
?>