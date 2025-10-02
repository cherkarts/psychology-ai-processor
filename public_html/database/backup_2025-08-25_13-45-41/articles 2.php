<?php
session_start();

// Calculate reading time based on content
function calculateReadingTime($content) {
    $wordsPerMinute = 200; // Average reading speed
    $text = strip_tags($content);
    $wordCount = str_word_count($text, 0, 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯабвгдеёжзийклмнопрстуфхцчшщъыьэюя');
    $minutes = ceil($wordCount / $wordsPerMinute);
    
    if ($minutes < 1) {
        return '1 минута';
    } elseif ($minutes == 1) {
        return '1 минута';
    } elseif ($minutes < 5) {
        return $minutes . ' минуты';
    } else {
        return $minutes . ' минут';
    }
}

// Get category name from database
function getCategoryName($categorySlug, $pdo = null) {
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

// Get all categories from database
function getAllCategories($pdo = null) {
    if ($pdo) {
        $stmt = $pdo->query("SELECT slug, name FROM article_categories WHERE is_active = 1 ORDER BY sort_order, name");
        return $stmt->fetchAll();
    }
    return [];
}

// Определяем корневую папку
$rootPath = __DIR__;
require_once $rootPath . '/includes/functions.php';

$meta = generateMetaTags('articles');
$schema = generateSchemaMarkup('person');
?>
<!DOCTYPE html>
<html class="js" lang="ru">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="x-ua-compatible" content="ie=edge" />
  <title><?= e($meta['title']) ?></title>
  <meta content="<?= e($meta['description']) ?>" name="description" />
  <meta content="<?= e($meta['keywords']) ?>" name="keywords" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta content="telephone=no" name="format-detection" />
  <meta name="HandheldFriendly" content="true" />
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />

  <link rel="canonical" href="https://cherkas-therapy.ru/articles.php" />
  <meta property="og:locale" content="ru_RU" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="<?= e($meta['title']) ?>" />
  <meta property="og:description" content="<?= e($meta['description']) ?>" />
  <meta property="og:url" content="https://cherkas-therapy.ru/articles.php" />
  <meta property="og:site_name" content="Психолог Денис Черкас" />
  <meta property="og:image" content="https://cherkas-therapy.ru/image/23-1.jpg" />
  <meta property="og:image:width" content="1920" />
  <meta property="og:image:height" content="1080" />
  <meta property="og:image:type" content="image/jpeg" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?= e($meta['title']) ?>" />
  <meta name="twitter:image" content="https://cherkas-therapy.ru/image/23-1.jpg" />

  <!-- CSRF токен для AJAX-отправки форм в попапах -->
  <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>" />

  <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Blog",
        "name": "Блог психолога Дениса Черкаса",
        "description": "<?= e($meta['description']) ?>",
        "url": "https://cherkas-therapy.ru/articles.php",
        "publisher": {
            "@type": "Person",
            "name": "Денис Черкас",
            "jobTitle": "Психолог"
        }
    }
    </script>

  <!-- Универсальные стили -->
  <link rel="stylesheet" href="/css/unified-styles.css" />
  <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" />
  <link rel="stylesheet" href="/css/fancybox.css" />
  <link rel="stylesheet" href="/css/font.css" />
  <link rel="stylesheet" href="/css/unified-mobile-menu.css" />
  <link rel="stylesheet" href="/css/articles.css" />
  <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />



  <meta name="msapplication-TileColor" content="#ffffff" />
  <meta name="msapplication-TileImage" content="/favicon/ms-icon.png" />
  <meta name="theme-color" content="#ffffff" />
  <meta name="yandex-verification" content="abe245cbb3b37351" />
</head>

<body class="page">
  <?php include $rootPath . '/includes/new-header.php'; ?>

  <?php
  // Подключение к базе данных
  require_once $rootPath . '/config.php';
  $config = require $rootPath . '/config.php';
  
  try {
    $socket = $config['database']['socket'] ?? null;
    if ($socket) {
        $dsn = "mysql:unix_socket={$socket};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
    } else {
        $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
    }
    $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);
  } catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
  }
  
  // Получение параметров фильтрации и пагинации
  $search = $_GET['search'] ?? '';
  $category = $_GET['category'] ?? '';
  $page = max(1, intval($_GET['page'] ?? 1));
  $perPage = 18; // Количество статей на странице
  
  $articles = [];
  $categories = [];
  $totalArticles = 0;
  
  if ($pdo) {
    // Получение категорий
    $stmt = $pdo->query("SELECT DISTINCT ac.slug, ac.name FROM article_categories ac 
                         INNER JOIN articles a ON ac.id = a.category_id 
                         WHERE a.is_published = 1 AND ac.is_active = 1
                         ORDER BY ac.name");
    $categories = $stmt->fetchAll();
    
    // Построение условий для запроса
    $conditions = ['a.is_published = 1'];
    $params = [];
    
    if (!empty($category)) {
      $conditions[] = 'ac.slug = ?';
      $params[] = $category;
    }
    
    if (!empty($search)) {
      $conditions[] = '(a.title LIKE ? OR a.excerpt LIKE ? OR a.content LIKE ?)';
      $searchTerm = "%{$search}%";
      $params[] = $searchTerm;
      $params[] = $searchTerm;
      $params[] = $searchTerm;
    }
    
    $whereClause = 'WHERE ' . implode(' AND ', $conditions);
    
    // Получение общего количества статей
    $countSql = "SELECT COUNT(*) as total FROM articles a 
                 LEFT JOIN article_categories ac ON a.category_id = ac.id 
                 {$whereClause}";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $totalArticles = $stmt->fetch()['total'];
    
    // Получение статей для текущей страницы
    $offset = ($page - 1) * $perPage;
    $sql = "SELECT a.*, ac.name as category_name, ac.slug as category_slug
            FROM articles a 
            LEFT JOIN article_categories ac ON a.category_id = ac.id 
            {$whereClause}
            ORDER BY a.created_at DESC 
            LIMIT {$perPage} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();
  }
  
  // Пагинация
  $totalPages = ceil($totalArticles / $perPage);
  $page = min($page, max(1, $totalPages)); // Не выходить за пределы количества страниц
  ?>

  <section class="articles-hero">

    <div class="wrapper">
      <div class="first__content">
        <div class="first__text">
          <h1 class="first__title md-main-title">
            <span style="color: #6a7e9f">СТАТЬИ</span><br />
            <span style="color: #d2afa0">И МАТЕРИАЛЫ</span>
          </h1>
          <p class="first__subtitle">
            Полезные материалы по психологии, зависимостям и созависимости.
            Практические советы от профессионального психолога.
          </p>
        </div>
        <div class="first__form">
          <div class="form-title">
            <p>Найдите нужную<br />статью для<br />развития</p>
          </div>
          <form class="form search-form" method="GET">
            <div class="search-container">
              <input type="text" name="search" class="form-input search-input" placeholder="Поиск статей..."
                value="<?= e($search) ?>" />
              <button type="submit" class="form-btn search-btn md-main-color-btn">
                <span>Найти</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <section class="shop-categories">
    <div class="wrapper">
      <h2 class="advantages__title md-main-title">
        <span style="color: #6a7e9f">КАТЕГОРИИ</span> СТАТЕЙ
      </h2>
      <div class="filters__slider md-standart-slider" filtersslider_js="">
        <div class="slider-hint">
          <span>Листайте влево/вправо</span>
          <svg width="13" height="14" viewBox="0 0 13 14" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M11.7603 5.82319C11.5048 5.82319 11.2697 5.9028 11.0724 6.03549C10.904 5.54602 10.4425 5.19514 9.90269 5.19514C9.64728 5.19514 9.41218 5.27475 9.21482 5.40744C9.04647 4.91797 8.58499 4.56708 8.04514 4.56708C7.81875 4.56708 7.60687 4.629 7.42692 4.73515V2.67702C7.42692 1.9841 6.87255 1.41797 6.18758 1.41797C5.5026 1.41797 4.94824 1.98115 4.94824 2.67702V8.02285L3.87143 7.20314C3.2329 6.71662 2.32734 6.78149 1.76136 7.35647C1.39856 7.72504 1.39856 8.32361 1.76136 8.68924L6.17016 13.1682C6.6955 13.7019 7.39789 13.9967 8.14092 13.9967H9.59213C11.47 13.9967 12.9967 12.4458 12.9967 10.538V7.0793C12.9996 6.38637 12.4423 5.82319 11.7603 5.82319ZM12.3785 10.541C12.3785 12.1008 11.1275 13.3716 9.59213 13.3716H8.14092C7.56043 13.3716 7.01767 13.1416 6.60843 12.7259L2.19963 8.24695C2.07773 8.12311 2.07773 7.92555 2.19963 7.80171C2.39119 7.6071 2.6466 7.50685 2.90492 7.50685C3.1139 7.50685 3.32577 7.57172 3.49992 7.70735L5.07014 8.90449C5.16302 8.97525 5.29072 8.98705 5.39521 8.93397C5.4997 8.8809 5.56645 8.7718 5.56645 8.65385V2.67702C5.56645 2.32909 5.84509 2.04897 6.18467 2.04897C6.52426 2.04897 6.80289 2.33204 6.80289 2.67702V7.3948C6.80289 7.56877 6.94221 7.7103 7.11345 7.7103C7.2847 7.7103 7.42401 7.56877 7.42401 7.3948V5.82319C7.42401 5.47525 7.70265 5.19514 8.04223 5.19514C8.38182 5.19514 8.66045 5.4782 8.66045 5.82319V7.3948C8.66045 7.56877 8.79977 7.7103 8.97101 7.7103C9.14226 7.7103 9.28157 7.56877 9.28157 7.3948V6.45124C9.28157 6.10331 9.56021 5.82319 9.89979 5.82319C10.2394 5.82319 10.518 6.10626 10.518 6.45124V7.3948C10.518 7.56877 10.6573 7.7103 10.8286 7.7103C10.9998 7.7103 11.1391 7.56877 11.1391 7.3948V7.0793C11.1391 6.73136 11.4178 6.45124 11.7573 6.45124C12.0969 6.45124 12.3756 6.73431 12.3756 7.0793L12.3785 10.541Z"
              fill="#1C1C1C"></path>
            <path
              d="M11.3053 2.21146L12.3705 1.10573L11.3053 0L11.1573 0.153328L11.97 0.99663H8.0459V1.21483H11.97L11.1573 2.05813L11.3053 2.21146Z"
              fill="#31B939"></path>
            <path
              d="M1.06519 0L0 1.10573L1.06519 2.21146L1.21322 2.05813L0.400536 1.21483H4.32463V0.99663H0.400536L1.21322 0.153328L1.06519 0Z"
              fill="#31B939"></path>
          </svg>
        </div>
        <div class="swiper-container">
          <div class="swiper-wrapper">
            <div class="swiper-slide">
              <a href="<?= $search ? '?search=' . urlencode($search) : '?' ?>"
                class="filters-item<?= empty($category) ? ' active' : '' ?>">Все</a>
            </div>
            <?php foreach ($categories as $cat): ?>
              <div class="swiper-slide">
                <a href="<?= $search ? '?search=' . urlencode($search) . '&category=' . urlencode($cat['slug']) : '?category=' . urlencode($cat['slug']) ?>"
                  class="filters-item<?= $category === $cat['slug'] ? ' active' : '' ?>"><?= e($cat['name']) ?></a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="slider-prev-btn slider-btn swiper-button-disabled">
          <img src="/image/slider-prev.svg" alt="">
        </div>
        <div class="slider-next-btn slider-btn">
          <img src="/image/slider-next.svg" alt="">
        </div>
      </div>
    </div>
  </section>

  <section class="articles-count">
    <div class="wrapper">
      <p>Найдено <strong><?= $totalArticles ?></strong> статей</p>
    </div>
  </section>

  <section class="articles-grid">
    <div class="wrapper">
      <div class="articles__grid">
        <?php foreach ($articles as $article): ?>
          <?php
          $slug = $article['slug'];
          $readingTime = calculateReadingTime($article['content'] ?? '');
          $categoryName = $article['category_name'] ?? 'Общее';
          ?>
          <div class="article-card">
            <div class="article-card__top">
              <div class="article-card__image">
                <?php if (!empty($article['featured_image'])): ?>
                  <img src="<?= e($article['featured_image']) ?>" alt="<?= e($article['title']) ?>">
                <?php else: ?>
                  <img src="/image/23-1.jpg" alt="<?= e($article['title']) ?>">
                <?php endif; ?>
              </div>
            </div>
            <div class="article-card__content">
              <div class="article-card__category"><?= e($categoryName) ?></div>
              <h3 class="article-card__title">
                <a href="/article.php?slug=<?= urlencode($slug) ?>"><?= e($article['title']) ?></a>
              </h3>
              <p class="article-card__excerpt"><?= e($article['excerpt']) ?></p>
              <div class="article-card__meta">
                <span class="article-card__date"><?= date('d.m.Y', strtotime($article['created_at'])) ?></span>
                <span class="article-card__read-time"><?= e($readingTime) ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $category ? '&category=' . urlencode($category) : '' ?>"
              class="pagination__prev">← Назад</a>
          <?php endif; ?>

          <div class="pagination__pages">
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
              <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $category ? '&category=' . urlencode($category) : '' ?>"
                class="pagination__page<?= $i === $page ? ' active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
          </div>

          <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $category ? '&category=' . urlencode($category) : '' ?>"
              class="pagination__next">Вперед →</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <?php include $rootPath . '/includes/new-footer.php'; ?>

  <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
  <script src="/js/unified-mobile-menu.js"></script>
  <script src="/js/new-homepage.js?v=3.2"></script>

  <script>
    // Инициализация Swiper для карусели категорий
    document.addEventListener('DOMContentLoaded', function () {
      const filtersSlider = new Swiper('.filters__slider .swiper-container', {
        slidesPerView: 'auto',
        spaceBetween: 10,
        navigation: {
          nextEl: '.filters__slider .slider-next-btn',
          prevEl: '.filters__slider .slider-prev-btn',
        },
        breakpoints: {
          320: {
            slidesPerView: 2,
            spaceBetween: 8,
          },
          480: {
            slidesPerView: 3,
            spaceBetween: 10,
          },
          768: {
            slidesPerView: 4,
            spaceBetween: 12,
          },
          1024: {
            slidesPerView: 5,
            spaceBetween: 15,
          },
          1200: {
            slidesPerView: 6,
            spaceBetween: 15,
          }
        }
      });
    });
  </script>
</body>

</html>