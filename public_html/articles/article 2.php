<?php
session_start();

// Process tags from JSON to array
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
$rootPath = dirname(__DIR__);
require_once $rootPath . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';

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
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}

$articleData = null;

if ($pdo && !empty($slug)) {
    // Получаем статью из базы данных
    $stmt = $pdo->prepare("SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                        FROM articles a 
                        LEFT JOIN article_categories ac ON a.category_id = ac.id 
                        WHERE a.slug = ? AND a.is_published = 1");
    $stmt->execute([$slug]);
    $articleData = $stmt->fetch();

    if ($articleData) {
        // Исправляем кодировку в данных статьи
        if (isset($articleData['title'])) {
            $articleData['title'] = iconv('UTF-8', 'Windows-1251//IGNORE', $articleData['title']);
        }
        if (isset($articleData['content'])) {
            $articleData['content'] = iconv('UTF-8', 'Windows-1251//IGNORE', $articleData['content']);
        }
        if (isset($articleData['excerpt'])) {
            $articleData['excerpt'] = iconv('UTF-8', 'Windows-1251//IGNORE', $articleData['excerpt']);
        }
        if (isset($articleData['category_name'])) {
            $articleData['category_name'] = @iconv('UTF-8', 'Windows-1251//IGNORE', $articleData['category_name']) ?: $articleData['category_name'];
        }
        if (isset($articleData['author'])) {
            $articleData['author'] = @iconv('UTF-8', 'Windows-1251//IGNORE', $articleData['author']) ?: $articleData['author'];
        }

        // Преобразуем данные для совместимости
        $articleData['date'] = $articleData['created_at'];
        $articleData['image'] = $articleData['featured_image'];
        $articleData['category'] = $articleData['category_name'] ?? 'Общее';
        // Process tags from JSON to array
        $articleData['tags'] = processTags($articleData['tags']);

        // Исправляем кодировку в тегах
        if (isset($articleData['tags']) && is_array($articleData['tags'])) {
            foreach ($articleData['tags'] as &$tag) {
                $tag = @iconv('UTF-8', 'Windows-1251//IGNORE', $tag) ?: $tag;
            }
        }
    }
}

if (!$articleData) {
    header("HTTP/1.0 404 Not Found");
    include $rootPath . '/404.php';
    exit;
}

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
        '@id' => "https://cherkas-therapy.ru/articles/{$slug}/"
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

    <link rel="canonical" href="https://cherkas-therapy.ru/articles/<?= e($slug) ?>/" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?= e($articleMeta['title']) ?>" />
    <meta property="og:description" content="<?= e($articleMeta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/articles/<?= e($slug) ?>/" />
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
    <link rel="stylesheet" href="/css/mobile.css" />
    <link rel="stylesheet" href="/css/articles.css" />



    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="/favicon/ms-icon.png" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="yandex-verification" content="abe245cbb3b37351" />
</head>

<body class="page article-page">
    <?php include $rootPath . '/includes/new-header.php'; ?>
    <?php require_once $rootPath . '/includes/spotlight-render.php'; ?>

    <!-- Хлебные крошки -->
    <section class="breadcrumbs-section">
        <div class="wrapper">
            <nav class="breadcrumbs">
                <div class="breadcrumbs__container">
                    <div class="breadcrumbs__item">
                        <a href="/">Главная</a>
                    </div>
                    <div class="breadcrumbs__item">
                        <a href="/articles/">Статьи</a>
                    </div>
                    <div class="breadcrumbs__item active">
                        <span><?= e($articleData['title']) ?></span>
                    </div>
                </div>
            </nav>
        </div>
    </section>

    <!-- Основной контент статьи -->
    <section class="article-main">
        <div class="wrapper">
            <div class="article-main__container">
                <!-- Основная статья -->
                <article class="article-content">
                    <header class="article-header">
                        <h1 class="article-title"><?= e($articleData['title']) ?></h1>

                        <div class="article-meta">
                            <div class="article-meta__left">
                                <span class="article-date">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M8 1C11.866 1 15 4.134 15 8C15 11.866 11.866 15 8 15C4.134 15 1 11.866 1 8C1 4.134 4.134 1 8 1Z"
                                            stroke="currentColor" stroke-width="1.5" />
                                        <path d="M8 4V8L10.5 10.5" stroke="currentColor" stroke-width="1.5"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <?= date('d.m.Y', strtotime($articleData['date'])) ?>
                                </span>
                                <?php if (!empty($articleData['category'])): ?>
                                    <span class="article-category"><?= e($articleData['category']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="article-meta__right">
                                <span class="article-author">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M8 8C10.21 8 12 6.21 12 4C12 1.79 10.21 0 8 0C5.79 0 4 1.79 4 4C4 6.21 5.79 8 8 8Z"
                                            stroke="currentColor" stroke-width="1.5" />
                                        <path d="M0 14C0 10.69 3.58 8 8 8C12.42 8 16 10.69 16 14" stroke="currentColor"
                                            stroke-width="1.5" stroke-linecap="round" />
                                    </svg>
                                    Денис Черкас
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($articleData['image'])): ?>
                            <div class="article-hero-image">
                                <img src="<?= e($articleData['image']) ?>" alt="<?= e($articleData['title']) ?>" />
                            </div>
                        <?php endif; ?>
                    </header>

                    <div class="article-body">
                        <?php
                        $content = $articleData['content'];

                        // Обработка заголовков
                        $content = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $content);
                        $content = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $content);
                        $content = preg_replace('/^#### (.+)$/m', '<h4>$1</h4>', $content);

                        // Обработка списков
                        $content = preg_replace('/^- (.+)$/m', '<li>$1</li>', $content);
                        $content = preg_replace('/^(\d+)\. (.+)$/m', '<li>$2</li>', $content);

                        // Обработка цитат
                        $content = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $content);

                        // Обработка жирного текста
                        $content = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $content);

                        // Обработка курсива
                        $content = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $content);

                        // Обработка ссылок
                        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $content);

                        // Обработка параграфов
                        $content = preg_replace('/\n\n/', '</p><p>', $content);
                        $content = '<p>' . $content . '</p>';

                        // Обработка списков
                        $content = preg_replace('/<p><li>(.+?)<\/li><\/p>/s', '<ul><li>$1</li></ul>', $content);
                        $content = preg_replace('/<\/ul><ul>/', '', $content);

                        echo $content;
                        ?>
                    </div>

                    <footer class="article-footer">
                        <div class="article-tags">
                            <?php if (!empty($articleData['tags'])): ?>
                                <span class="tags-label">Ключевые слова:</span>
                                <?php
                                foreach ($articleData['tags'] as $tag):
                                    if (!empty($tag)):
                                        ?>
                                        <span class="tag"><?= e($tag) ?></span>
                                        <?php
                                    endif;
                                endforeach;
                                ?>
                            <?php endif; ?>
                        </div>

                        <div class="article-share">
                            <span class="share-label">Поделиться статьей:</span>
                            <div class="share-buttons">
                                <a href="https://vk.com/share.php?url=<?= urlencode("https://cherkas-therapy.ru/articles/{$slug}/") ?>&title=<?= urlencode($articleData['title']) ?>"
                                    target="_blank" class="share-btn share-btn--vk">
                                    <img src="/image/vk.png" alt="vk" width="24" height="24">
                                    VK
                                </a>
                                <a href="https://t.me/share/url?url=<?= urlencode("https://cherkas-therapy.ru/articles/{$slug}/") ?>&text=<?= urlencode($articleData['title']) ?>"
                                    target="_blank" class="share-btn share-btn--telegram">
                                    <img src="/image/telegram.png" alt="Telegram" width="24" height="24">
                                    Telegram
                                </a>
                                <a href="https://wa.me/?text=<?= urlencode($articleData['title'] . ' - https://cherkas-therapy.ru/articles/' . $slug . '/') ?>"
                                    target="_blank" class="share-btn share-btn--whatsapp">
                                    <img src="/image/whats-app.png" alt="whats-app" width="24" height="24">
                                    WhatsApp
                                </a>
                            </div>
                        </div>
                    </footer>
                </article>

                <!-- Боковая панель -->
                <aside class="article-sidebar">
                    <?php // Sidebar spotlight placeholder
                    $unitHtml = get_spotlights_html('article_sidebar');
                    if (!empty($unitHtml)) {
                        echo '<div class="sidebar-widget side-unit">' . $unitHtml . '</div>';
                    }
                    ?>
                    <div class="sidebar-widget sidebar-widget--consultation">
                        <div class="widget-icon">
                            <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M24 4C12.95 4 4 12.95 4 24C4 35.05 12.95 44 24 44C35.05 44 44 35.05 44 24C44 12.95 35.05 4 24 4Z"
                                    fill="#7A91B7" />
                                <path
                                    d="M24 12C17.37 12 12 17.37 12 24C12 30.63 17.37 36 24 36C30.63 36 36 30.63 36 24C36 17.37 30.63 12 24 12Z"
                                    fill="white" />
                                <path d="M20 18H28V22H20V18Z" fill="#7A91B7" />
                                <path d="M20 26H24V30H20V26Z" fill="#7A91B7" />
                            </svg>
                        </div>
                        <h3 class="widget-title">Нужна консультация?</h3>
                        <p>Получите профессиональную помощь психолога. Первая консультация бесплатно.</p>
                        <button class="widget-btn md-main-color-btn" data-popup="call-back-popup">
                            <span>ЗАПИСАТЬСЯ</span>
                            <img src="/image/phone.svg" alt="" />
                        </button>
                    </div>

                    <div class="sidebar-widget sidebar-widget--recent">
                        <h3 class="widget-title">Последние статьи</h3>
                        <?php
                        if ($pdo) {
                            // Получаем последние 3 статьи из базы данных (исключая текущую)
                            $stmt = $pdo->prepare("SELECT a.slug, a.title, a.created_at 
                                                   FROM articles a 
                                                   WHERE a.is_published = 1 AND a.slug != ? 
                                                   ORDER BY a.created_at DESC 
                                                   LIMIT 3");
                            $stmt->execute([$slug]);
                            $recentArticles = $stmt->fetchAll();

                            foreach ($recentArticles as $recentData) {
                                ?>
                                <div class="recent-article">
                                    <h4><a
                                            href="/article.php?slug=<?= e($recentData['slug']) ?>"><?= e($recentData['title']) ?></a>
                                    </h4>
                                    <span class="recent-date"><?= date('d.m.Y', strtotime($recentData['created_at'])) ?></span>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </aside>
            </div>
        </div>
    </section>

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

    <script src="/js/main.js"></script>
    <script src="/js/fancybox.umd.js"></script>
    <script src="/js/script.js"></script>
    <script src="/js/jquery.maskedinput.min.js"></script>
    <script src="/js/new-homepage.js?v=3.1"></script>
    <script>
        // Инициализация Fancybox
        if (typeof Fancybox !== 'undefined') {
            Fancybox.bind('[data-fancybox]');
        }
    </script>
</body>

</html>