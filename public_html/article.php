<?php
require_once 'includes/Models/Order.php';
require_once 'includes/Models/Article.php';
require_once 'includes/Models/Meditation.php';
require_once 'includes/Models/Review.php';
require_once 'includes/Models/Product.php';
require_once 'includes/Database.php';
session_start();

// Calculate reading time based on content
function calculateReadingTime($content)
{
    if (empty($content)) {
        return '1 минута';
    }

    $wordsPerMinute = 200; // Average reading speed

    // Удаляем HTML теги
    $text = strip_tags($content);

    // Если текст пустой после удаления тегов
    if (empty(trim($text))) {
        return '1 минута';
    }

    // Проверяем на проблемы с кодировкой
    if (strpos($text, 'Р') !== false && strpos($text, 'С') !== false) {
        // Пытаемся исправить кодировку
        $fixed = @iconv('CP1251', 'UTF-8', $text);
        if ($fixed !== false) {
            $text = $fixed;
        }
    }

    // Подсчитываем слова с поддержкой русского языка
    $wordCount = str_word_count($text, 0, 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя');

    // Если подсчет слов не работает (мало слов при большом тексте), используем приблизительный подсчет
    if ($wordCount < 50 && strlen($text) > 1000) {
        // Приблизительный подсчет: 1 слово = 6 символов для русского текста
        $wordCount = ceil(strlen($text) / 6);
    } elseif ($wordCount === 0) {
        $wordCount = ceil(strlen($text) / 6);
    }

    $minutes = ceil($wordCount / $wordsPerMinute);

    // Минимум 1 минута
    if ($minutes < 1) {
        $minutes = 1;
    }

    if ($minutes == 1) {
        return '1 минута';
    } elseif ($minutes < 5) {
        return $minutes . ' минуты';
    } else {
        return $minutes . ' минут';
    }
}

// Get category name from database
function getCategoryName($categorySlug, $pdo = null)
{
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT name FROM article_categories WHERE slug = ?");
        $stmt->execute([$categorySlug]);
        $result = $stmt->fetch();
        if ($result) {
            return $result['name'];
        }
    }

    // Fallback to formatted slug if category not found
    return ucfirst(str_replace('-', ' ', $categorySlug));
}

function processTags($tagsJson)
{
    if (empty($tagsJson)) {
        return [];
    }

    $tagsArray = json_decode($tagsJson, true);
    if (is_array($tagsArray)) {
        return $tagsArray;
    }

    return [];
}

// Определяем корневую папку
$rootPath = __DIR__;
require_once $rootPath . '/includes/functions.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
    header('Location: /maintenance.php');
    exit;
}

// Перенаправляем на ЧПУ URL если нужно
redirectToSeoUrl();

$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? '';

// Подключение к базе данных
require_once $rootPath . '/config.php';
$config = require $rootPath . '/config.php';

try {
    $socket = $config['database']['socket'] ?? null;
    if ($socket) {
        $dsn = "mysql:unix_socket={$socket};dbname={$config['database']['dbname']}";
    } else {
        $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']}";
    }
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);

    // Устанавливаем кодировку для корректной работы с UTF-8
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}

$articleData = null;

if ($pdo && (!empty($slug) || !empty($id))) {
    // Получаем статью из базы данных
    // Если передан preview=1 и есть доступ админа, показываем даже черновик
    $isPreview = isset($_GET['preview']) && $_GET['preview'] == '1' && isAdminAccess();

    if (!empty($slug)) {
        // Поиск по slug
        if ($isPreview) {
            $stmt = $pdo->prepare("SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                                FROM articles a 
                                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                                WHERE a.slug = ? LIMIT 1");
            $stmt->execute([$slug]);
        } else {
            $stmt = $pdo->prepare("SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                                FROM articles a 
                                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                                WHERE a.slug = ? AND (IFNULL(a.is_active,0)=1 OR IFNULL(a.is_published,0)=1)");
            $stmt->execute([$slug]);
        }
    } elseif (!empty($id)) {
        // Поиск по ID
        if ($isPreview) {
            $stmt = $pdo->prepare("SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                                FROM articles a 
                                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                                WHERE a.id = ? LIMIT 1");
            $stmt->execute([$id]);
        } else {
            $stmt = $pdo->prepare("SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                                FROM articles a 
                                LEFT JOIN article_categories ac ON a.category_id = ac.id 
                                WHERE a.id = ? AND (IFNULL(a.is_active,0)=1 OR IFNULL(a.is_published,0)=1)");
            $stmt->execute([$id]);
        }
    }

    $articleData = $stmt->fetch();

    if ($articleData) {
        // БЕЗ любых преобразований кодировок — выводим как хранится в БД (UTF-8)

        // Если статья найдена по ID, обновляем slug для корректных ссылок
        if (!empty($id) && !empty($articleData['slug'])) {
            $slug = $articleData['slug'];
        }

        // Преобразуем данные для совместимости
        $articleData['date'] = $articleData['created_at'];
        $articleData['image'] = $articleData['featured_image'] ?: ($articleData['image'] ?? null);
        if (empty($articleData['image'])) {
            $articleData['image'] = 'https://cherkas-therapy.ru/image/23-1.jpg';
        }
        $articleData['category'] = $articleData['category_slug'] ?? 'psihologiya';

        // Теги: декодируем JSON и нормализуем (уникальные, без пустых)
        $tagsRaw = $articleData['tags'] ?? '';
        $tags = processTags($tagsRaw);
        if (is_array($tags)) {
            $clean = [];
            foreach ($tags as $t) {
                $t = trim((string) $t);
                if ($t !== '' && !in_array($t, $clean, true)) {
                    $clean[] = $t;
                }
            }
            $articleData['tags'] = $clean;
        } else {
            $articleData['tags'] = [];
        }

        // Удаляем служебный абзац "Разделы статьи:" из контента
        if (isset($articleData['content'])) {
            $articleData['content'] = preg_replace('/<p>\*\*Разделы статьи:\*\*.+?<\/p>/is', '', $articleData['content']);
            $articleData['content'] = preg_replace('/<p>Разделы статьи:.+?<\/p>/is', '', $articleData['content']);
        }
    }
}

if (!$articleData) {
    header("HTTP/1.0 404 Not Found");
    include $rootPath . '/404.php';
    exit;
}

// Calculate reading time and get category name
$readingTime = calculateReadingTime($articleData['content'] ?? '');
$categoryName = $articleData['category_name'] ?? 'Общее';

// Генерация мета-тегов для статьи
$articleMeta = [
    'title' => $articleData['title'] . ' - Психолог Денис Черкас',
    'description' => $articleData['excerpt'],
    'keywords' => !empty($articleData['tags']) ? implode(', ', $articleData['tags']) : ($articleData['keywords'] ?? 'психология, ' . $articleData['category'] ?? 'статьи психолога')
];

// Schema.org разметка для статьи
$articleSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $articleData['title'],
    'description' => $articleData['excerpt'],
    'image' => $articleData['image'] ?? 'https://cherkas-therapy.ru/image/23-1.jpg',
    'author' => [
        '@type' => 'Person',
        'name' => 'Денис Черкас',
        'jobTitle' => 'Психолог'
    ],
    'publisher' => [
        '@type' => 'Person',
        'name' => 'Денис Черкас'
    ],
    'datePublished' => $articleData['date'],
    'dateModified' => $articleData['date'],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => "https://cherkas-therapy.ru/article.php?slug={$slug}"
    ]
];
?>
<!DOCTYPE html>
<html class="js" lang="ru">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title><?= e($articleMeta['title']) ?></title>
    <meta content="<?= e($articleMeta['description']) ?>" name="description" />
    <meta content="<?= e($articleMeta['keywords']) ?>" name="keywords" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta content="telephone=no" name="format-detection" />
    <meta name="HandheldFriendly" content="true" />
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />

    <link rel="canonical" href="https://cherkas-therapy.ru/article.php?slug=<?= e($slug) ?>" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?= e($articleMeta['title']) ?>" />
    <meta property="og:description" content="<?= e($articleMeta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/article.php?slug=<?= e($slug) ?>" />
    <meta property="og:site_name" content="Психолог Денис Черкас" />
    <meta property="og:image"
        content="<?= e($articleData['image'] ?? 'https://cherkas-therapy.ru/image/23-1.jpg') ?>" />
    <meta property="og:image:width" content="1920" />
    <meta property="og:image:height" content="1080" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta property="article:published_time" content="<?= e($articleData['date']) ?>" />
    <meta property="article:author" content="Денис Черкас" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($articleMeta['title']) ?>" />
    <meta name="twitter:image"
        content="<?= e($articleData['image'] ?? 'https://cherkas-therapy.ru/image/23-1.jpg') ?>" />
    <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>" />

    <script type="application/ld+json">
    <?= json_encode($articleSchema, JSON_UNESCAPED_UNICODE) ?>
    </script>

    <!-- Универсальные стили -->
    <link rel="stylesheet" href="/css/unified-styles.css" />
    <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" />
    <link rel="stylesheet" href="/css/fancybox.css" />
    <link rel="stylesheet" href="/css/font.css" />
    <link rel="stylesheet" href="/css/unified-mobile-menu.css" />
    <link rel="stylesheet" href="/css/articles.css" />
    <link rel="stylesheet" href="/css/article-mobile.css" />

    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="/favicon/ms-icon.png" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="yandex-verification" content="abe245cbb3b37351" />
</head>

<body class="page">
    <?php include $rootPath . '/includes/new-header.php'; ?>
    <?php require_once $rootPath . '/includes/spotlight-render.php'; ?>

    <article class="article-single">
        <div class="wrapper">
            <div class="article-layout">
                <!-- Основной контент статьи (80%) -->
                <div class="article-main">
                    <!-- Мета-информация -->
                    <div class="article-meta">
                        <span class="article-category"><?= e($categoryName) ?></span>
                        <span class="article-read-time">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            <?= e($readingTime) ?>
                        </span>
                        <span class="article-date"><?= date('d.m.Y', strtotime($articleData['date'])) ?></span>
                        <span class="article-author">Автор:
                            <strong><?= e($articleData['author'] ?? 'Денис Черкас') ?></strong></span>
                    </div>

                    <!-- Заголовок -->
                    <h1 class="article-title"><?= e($articleData['title']) ?></h1>

                    <?php // Краткое описание используется только в карточках и в админке; на опубликованной странице скрываем ?>

                    <!-- Изображение -->
                    <?php if (!empty($articleData['image'])): ?>
                        <div class="article-image">
                            <img src="<?= e($articleData['image']) ?>" alt="<?= e($articleData['title']) ?>">
                        </div>
                    <?php endif; ?>

                    <!-- Содержание статьи -->
                    <div class="article-content">
                        <?= $articleData['content'] ?>
                    </div>

                    <!-- Хештеги -->
                    <?php
                    // Отладка тегов
                    error_log("Displaying tags. Empty check: " . (empty($articleData['tags']) ? 'yes' : 'no'));
                    error_log("Tags value: " . var_export($articleData['tags'], true));
                    ?>
                    <?php if (!empty($articleData['tags']) && is_array($articleData['tags']) && count($articleData['tags']) > 0): ?>
                        <div class="article-tags">
                            <span>Теги:</span>
                            <?php foreach ($articleData['tags'] as $tag): ?>
                                <span class="article-tag"><?= e($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Debug: Теги пусты или не массив -->
                        <?php if (isAdminAccess()): ?>
                            <div
                                style="background: #fff3cd; padding: 10px; border: 1px solid #ffc107; border-radius: 4px; margin: 10px 0;">
                                <strong>Debug (только для админа):</strong><br>
                                Теги не найдены или пусты.<br>
                                Тип: <?= gettype($articleData['tags'] ?? null) ?><br>
                                Значение: <?= htmlspecialchars(print_r($articleData['tags'] ?? 'null', true)) ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Кнопки лайк и поделиться -->
                    <div class="article-actions">
                        <button class="article-like-btn" data-article="<?= e($slug) ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                </path>
                            </svg>
                            <span>Нравится</span>
                            <span class="like-counter">0</span>
                        </button>
                        <button class="article-share-btn"
                            data-url="<?= 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="18" cy="5" r="3"></circle>
                                <circle cx="6" cy="12" r="3"></circle>
                                <circle cx="18" cy="19" r="3"></circle>
                                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                            </svg>
                            <span>Поделиться</span>
                        </button>

                        <!-- Кнопки поделиться в соцсетях -->
                        <div class="article-share-social">
                            <a href="https://t.me/share/url?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($articleData['title']) ?>"
                                target="_blank" class="article-share-btn--telegram">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.94-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06-.01.13-.02.2z" />
                                </svg>
                                <span>Telegram</span>
                            </a>
                            <a href="https://wa.me/?text=<?= urlencode($articleData['title'] . ' - http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>"
                                target="_blank" class="article-share-btn--whatsapp">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488" />
                                </svg>
                                <span>WhatsApp</span>
                            </a>
                            <a href="https://vk.com/share.php?url=<?= urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($articleData['title']) ?>"
                                target="_blank" class="article-share-btn--vk">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1.01-1.49-.888-1.744-.888-.358 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.593-.491h1.744c.441 0 .61.203.78.677.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.204.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.254-.44 0 .78.186.254.796.779 1.203 1.253.744.847 1.32 1.558 1.473 2.05.203.525-.085.791-.576.791z" />
                                </svg>
                                <span>VK</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Сайдбар (20%) -->
                <aside class="article-sidebar">
                    <?php // Витрина в боковой колонке
                    $unitHtml = get_spotlights_html('article_sidebar');
                    if (!empty($unitHtml)) {
                        echo '<div class="sidebar-block side-unit">' . $unitHtml . '</div>';
                    }
                    ?>
                    <!-- Блок записи на консультацию -->
                    <div class="sidebar-block consultation-block">
                        <h3>Нужна консультация специалиста?</h3>
                        <p>Свяжитесь со мной — отвечу в течение 30 минут.</p>
                        <a class="btn btn-primary" href="https://cherkas-therapy.ru/contact">Связаться</a>
                    </div>

                    <!-- Последние статьи -->
                    <div class="sidebar-block recent-articles-block">
                        <h3>Последние статьи</h3>
                        <?php
                        $recentArticles = [];
                        if ($pdo) {
                            // Получаем последние 5 статей из базы данных (исключая текущую)
                            $stmt = $pdo->prepare("SELECT a.slug, a.title, a.featured_image, a.created_at 
                                                   FROM articles a 
                                                   WHERE (a.is_published = 1 OR a.is_active = 1) AND a.slug != ? 
                                                   ORDER BY a.created_at DESC 
                                                   LIMIT 5");
                            $stmt->execute([$slug]);
                            $recentArticles = $stmt->fetchAll();
                        }
                        ?>
                        <div class="recent-articles-list">
                            <?php foreach ($recentArticles as $article): ?>
                                <div class="recent-article-item">
                                    <a href="/article.php?slug=<?= e($article['slug']) ?>">
                                        <div class="recent-article-image">
                                            <img src="<?= e($article['featured_image'] ?? '/image/23-1.jpg') ?>"
                                                alt="<?= e($article['title']) ?>">
                                        </div>
                                        <div class="recent-article-info">
                                            <h4><?= e($article['title']) ?></h4>
                                            <span
                                                class="recent-article-date"><?= date('d.m.Y', strtotime($article['created_at'])) ?></span>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Подписка на Telegram -->
                    <div class="sidebar-block telegram-block">
                        <h3>Подпишитесь на Telegram канал</h3>
                        <p>Там вы найдете все анонсы и полезные материалы</p>
                        <a href="<?php echo getContactSettings()['telegram_channel_url']; ?>" target="_blank"
                            class="telegram-btn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.94-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06-.01.13-.02.2z" />
                            </svg>
                            Подписаться на канал
                        </a>
                    </div>


                </aside>
            </div>
        </div>
    </article>

    <!-- Раздел комментариев -->
    <section class="comments-section">
        <div class="wrapper">
            <?php
            // Подключаем виджет комментариев
            $contentType = 'article';
            $contentId = $articleData['id']; // ID статьи из базы данных
            $showTitle = true;
            include $rootPath . '/includes/comments-widget.php';
            ?>
        </div>
    </section>

    <?php include $rootPath . '/includes/new-footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/jquery.maskedinput.min.js"></script>
    <script src="/js/unified-mobile-menu.js"></script>
    <script>
        // Простая и надежная инициализация маски для телефона
        $(document).ready(function () {
            console.log('Инициализация маски телефона...');

            // Функция применения маски
            function applyMask() {
                $('input[type="tel"]').each(function () {
                    if (!$(this).data('mask-applied')) {
                        console.log('Применяю маску к полю:', this);
                        $(this).mask('+7 (999) 999-99-99');
                        $(this).data('mask-applied', true);
                    }
                });
            }

            // Применяем маску сразу
            applyMask();

            // Применяем маску при клике на кнопки попапов
            $(document).on('click', '[data-popup]', function () {
                console.log('Клик по кнопке попапа');
                setTimeout(applyMask, 100);
                setTimeout(applyMask, 300);
                setTimeout(applyMask, 500);
            });

            // Применяем маску при появлении новых элементов
            const observer = new MutationObserver(function (mutations) {
                console.log('DOM изменился');
                applyMask();
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            // Применяем маску при фокусе
            $(document).on('focus', 'input[type="tel"]', function () {
                console.log('Фокус на поле телефона');
                if (!$(this).data('mask-applied')) {
                    $(this).mask('+7 (999) 999-99-99');
                    $(this).data('mask-applied', true);
                }
            });
        });
    </script>
    <script src="/js/new-homepage.js?v=3.2"></script>
    <script src="/js/article-actions.js"></script>
    <script src="/js/article-enhancements.js"></script>
</body>

</html>