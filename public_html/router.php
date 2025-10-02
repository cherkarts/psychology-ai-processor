<?php
// Простой роутер для PHP встроенного сервера
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Убираем слеш в конце
$path = rtrim($path, '/');

// Если путь пустой, это главная страница
if ($path === '' || $path === '/') {
    include 'index.php';
    exit;
}

// Убираем начальный слеш
$path = ltrim($path, '/');

// Специальная обработка для статей
if (strpos($path, 'articles') === 0) {
    $slug = trim(substr($path, strlen('articles')), '/');
    // Пусто → список статей
    if ($slug === '') {
        include 'articles/index.php';
        exit;
    }
    // Если есть .php — отдаем как файл
    if (substr($slug, -4) === '.php' && file_exists('articles/' . $slug)) {
        include 'articles/' . $slug;
        exit;
    }
    // Иначе считаем, что это slug статьи
    $_GET['slug'] = $slug;
    include 'articles/article.php';
    exit;
}

// Проверяем, существует ли файл как есть
if (file_exists($path)) {
    // Если это PHP файл, включаем его
    if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
        include $path;
        exit;
    } else {
        // Для статических файлов возвращаем как есть
        return false;
    }
}

// Если файл не найден, пробуем добавить .php
if (!pathinfo($path, PATHINFO_EXTENSION)) {
    $php_path = $path . '.php';
    if (file_exists($php_path)) {
        include $php_path;
        exit;
    }
}

// Файл не найден - показываем 404
http_response_code(404);
include '404.php';
?> 