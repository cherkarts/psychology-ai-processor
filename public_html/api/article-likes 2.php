<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Определяем пути к файлам
$likesFile = __DIR__ . '/../data/article-likes.json';
$userLikesFile = __DIR__ . '/../data/user-likes.json';

// Создаем директорию data если её нет
if (!is_dir(dirname($likesFile))) {
    mkdir(dirname($likesFile), 0755, true);
}

function getArticleLikes($slug)
{
    global $likesFile;

    if (!file_exists($likesFile)) {
        return 0;
    }

    $likes = json_decode(file_get_contents($likesFile), true);
    return $likes[$slug] ?? 0;
}

/**
 * Поставить или убрать лайк
 */
function toggleArticleLike($slug, $action)
{
    global $likesFile, $userLikesFile;

    // Загружаем существующие лайки
    $likes = [];
    if (file_exists($likesFile)) {
        $likes = json_decode(file_get_contents($likesFile), true) ?: [];
    }

    // Получаем текущее количество лайков для статьи
    $currentLikes = $likes[$slug] ?? 0;

    // Проверяем IP пользователя для предотвращения спама
    $userIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $userLikes = [];
    if (file_exists($userLikesFile)) {
        $userLikes = json_decode(file_get_contents($userLikesFile), true) ?: [];
    }

    $userKey = $userIP . '_' . $slug;
    $userHasLiked = isset($userLikes[$userKey]);

    // Обрабатываем действие
    if ($action === 'like' && !$userHasLiked) {
        $likes[$slug] = $currentLikes + 1;
        $userLikes[$userKey] = time();
    } elseif ($action === 'unlike' && $userHasLiked) {
        $likes[$slug] = max(0, $currentLikes - 1);
        unset($userLikes[$userKey]);
    } else {
        // Пользователь уже лайкал или пытается убрать несуществующий лайк
        return [
            'success' => true,
            'likes' => $currentLikes,
            'message' => $action === 'like' ? 'Вы уже лайкали эту статью' : 'Вы не лайкали эту статью'
        ];
    }

    // Сохраняем обновленные данные
    $success = file_put_contents($likesFile, json_encode($likes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $userSuccess = file_put_contents($userLikesFile, json_encode($userLikes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    if ($success === false || $userSuccess === false) {
        return ['success' => false, 'message' => 'Не удалось сохранить лайк'];
    }

    return ['success' => true, 'likes' => $likes[$slug] ?? 0];
}

// Обработка запросов
try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $articleSlug = $_GET['article_slug'] ?? '';

        if (empty($articleSlug)) {
            http_response_code(400);
            echo json_encode(['error' => 'Не указан slug статьи']);
            exit;
        }

        $likes = getArticleLikes($articleSlug);
        echo json_encode(['success' => true, 'likes' => $likes]);

    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $input = $_POST;
        }

        $articleSlug = $input['article_slug'] ?? '';
        $action = $input['action'] ?? '';

        if (empty($articleSlug) || empty($action)) {
            http_response_code(400);
            echo json_encode(['error' => 'Не указаны обязательные параметры']);
            exit;
        }

        if (!in_array($action, ['like', 'unlike'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Неверное действие']);
            exit;
        }

        $result = toggleArticleLike($articleSlug, $action);
        echo json_encode($result);

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Метод не поддерживается']);
    }

} catch (Exception $e) {
    error_log('Article likes API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Внутренняя ошибка сервера: ' . $e->getMessage()]);
}
?>