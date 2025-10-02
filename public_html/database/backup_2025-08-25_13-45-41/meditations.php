<?php
session_start();

// Загружаем данные медитаций ПЕРЕД functions.php
try {
    require_once 'includes/meditations-manager.php';
    $meditationsManager = new MeditationsManager();
    $categories = $meditationsManager->getAllCategories();
    $meditations = $meditationsManager->getAllMeditations();
    $stats = $meditationsManager->getStats();

    // Отладочная информация
    error_log("Загружено категорий: " . count($categories));
    error_log("Загружено медитаций: " . count($meditations));
    error_log("Статистика: " . json_encode($stats));

} catch (Exception $e) {
    error_log("Ошибка загрузки медитаций: " . $e->getMessage());
    $categories = [];
    $meditations = [];
    $stats = [
        'total_meditations' => 0,
        'total_categories' => 0,
        'total_duration' => 0,
        'total_duration_formatted' => '0 мин'
    ];
}

// Теперь загружаем functions.php
require_once 'includes/functions.php';

// Получение параметров пагинации и фильтрации
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 18; // Количество медитаций на странице
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

// Фильтры для получения медитаций
$filters = [];
if ($category) {
    $filters['category_id'] = $category;
}
if ($search) {
    $filters['search'] = $search;
}

// Получение всех медитаций с фильтрами
$allMeditations = $meditationsManager->getAllMeditations($filters);

// Пагинация
$totalMeditations = count($allMeditations);
$totalPages = ceil($totalMeditations / $perPage);
$page = min($page, max(1, $totalPages)); // Не выходить за пределы количества страниц

// Получение медитаций для текущей страницы
$offset = ($page - 1) * $perPage;
$currentPageMeditations = array_slice($allMeditations, $offset, $perPage);

// Отладочная информация
error_log("DEBUG: totalMeditations = " . $totalMeditations);
error_log("DEBUG: perPage = " . $perPage);
error_log("DEBUG: totalPages = " . $totalPages);
error_log("DEBUG: currentPageMeditations count = " . count($currentPageMeditations));

// Функция для построения URL с параметрами
function buildMeditationUrl($page = null, $category = null, $search = null)
{
    $params = [];
    if ($page !== null && $page > 1)
        $params[] = 'page=' . $page;
    if ($category)
        $params[] = 'category=' . urlencode($category);
    if ($search)
        $params[] = 'search=' . urlencode($search);

    return 'meditations.php' . (!empty($params) ? '?' . implode('&', $params) : '');
}

$meta = generateMetaTags('meditations');
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

    <link rel="canonical" href="https://cherkas-therapy.ru/meditations.php" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= e($meta['title']) ?>" />
    <meta property="og:description" content="<?= e($meta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/meditations.php" />
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
        "@type": "CollectionPage",
        "name": "Аудио медитации",
        "description": "<?= e($meta['description']) ?>",
        "url": "https://cherkas-therapy.ru/meditations.php",
        "mainEntity": {
            "@type": "ItemList",
            "itemListElement": [
                {
                    "@type": "AudioObject",
                    "name": "Медитация для снятия тревоги",
                    "description": "Расслабляющая медитация для уменьшения тревожности"
                },
                {
                    "@type": "AudioObject", 
                    "name": "Медитация для повышения уверенности",
                    "description": "Медитация для укрепления уверенности в себе"
                }
            ]
        }
    }
    </script>

    <!-- Новые стили -->
    <link rel="stylesheet" href="/css/new-components.css" />
    <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" />
    <link rel="stylesheet" href="/css/fancybox.css" />
    <link rel="stylesheet" href="/css/font.css" />
    <link rel="stylesheet" href="/css/pages.css" />
    <link rel="stylesheet" href="/css/unified-mobile-menu.css" />
    <link rel="stylesheet" href="/css/meditations.css" />

    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="favicon/ms-icon.png" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="yandex-verification" content="abe245cbb3b37351" />
</head>

<body class="page">
    <?php include 'includes/new-header.php'; ?>

    <!-- Hero секция -->
    <section class="meditations-hero">
        <div class="wrapper">
            <div class="meditations-hero__content">
                <div class="meditations-hero__badge">
                    <span>Аудио медитации</span>
                </div>
                <h1 class="meditations-hero__title md-main-title">
                    <span class="meditations-hero__title-accent">МЕДИТАЦИИ</span> ДЛЯ ДУШИ
                </h1>
                <p class="meditations-hero__subtitle">
                    Расслабляющие аудио медитации для снятия стресса, тревоги и обретения внутреннего спокойствия.
                    Профессионально записанные медитации помогут вам найти гармонию и баланс в повседневной жизни.
                </p>
                <div class="meditations-hero__stats">
                    <div class="hero-stat">
                        <span class="hero-stat__number"><?= $stats['total_meditations'] ?? 0 ?>+</span>
                        <span class="hero-stat__label">Медитаций</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat__number"><?= $stats['total_categories'] ?? 0 ?></span>
                        <span class="hero-stat__label">Категорий</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat__number">1000+</span>
                        <span class="hero-stat__label">Слушателей</span>
                    </div>
                </div>


            </div>
        </div>
    </section>

    <!-- Категории медитаций -->
    <section class="meditations-categories section">
        <div class="wrapper">
            <div class="section__header text-center">
                <h2 class="section__title">Категории медитаций</h2>
                <p class="section__subtitle">Выберите категорию, которая подходит именно вам</p>
            </div>

            <div class="categories-carousel" role="region" aria-label="Категории медитаций" tabindex="0">
                <button class="carousel-btn carousel-btn--prev" onclick="moveCarousel(-1)"
                    aria-label="Предыдущий слайд">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>

                <div class="categories-carousel__container">
                    <div class="categories-carousel__track">
                        <?php foreach ($categories as $category): ?>
                            <div class="category-card" data-category="<?= e($category['id']) ?>">
                                <div class="category-card__icon">
                                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M24 4C12.95 4 4 12.95 4 24C4 35.05 12.95 44 24 44C35.05 44 44 35.05 44 24C44 12.95 35.05 4 24 4ZM24 40C15.18 40 8 32.82 8 24C8 15.18 15.18 8 24 8C32.82 8 40 15.18 40 24C40 32.82 32.82 40 24 40Z"
                                            fill="currentColor" />
                                        <path
                                            d="M24 12C17.37 12 12 17.37 12 24C12 30.63 17.37 36 24 36C30.63 36 36 30.63 36 24C36 17.37 30.63 12 24 12ZM24 32C19.59 32 16 28.41 16 24C16 19.59 19.59 16 24 16C28.41 16 32 19.59 32 24C32 28.41 28.41 32 24 32Z"
                                            fill="currentColor" />
                                    </svg>
                                </div>
                                <h3 class="category-card__title"><?= e($category['name']) ?></h3>
                                <p class="category-card__description"><?= e($category['description']) ?></p>
                                <span class="category-card__count"><?= $category['meditation_count'] ?? 0 ?>
                                    медитаций</span>
                            </div>
                        <?php endforeach; ?>


                    </div>
                </div>

                <button class="carousel-btn carousel-btn--next" onclick="moveCarousel(1)" aria-label="Следующий слайд">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>

            <div class="carousel-dots" role="tablist" aria-label="Навигация по слайдам">
                <button class="carousel-dot active" onclick="goToSlide(0)" role="tab" aria-selected="true"
                    aria-label="Слайд 1"></button>
                <button class="carousel-dot" onclick="goToSlide(1)" role="tab" aria-selected="false"
                    aria-label="Слайд 2"></button>
                <button class="carousel-dot" onclick="goToSlide(2)" role="tab" aria-selected="false"
                    aria-label="Слайд 3"></button>
            </div>
        </div>
    </section>

    <!-- Медитации по категориям -->
    <section class="meditations-list section section--light">
        <div class="wrapper">
            <div class="section__header text-center">
                <h2 class="section__title">Все медитации</h2>
                <p class="section__subtitle">Выберите медитацию для прослушивания</p>
            </div>

            <!-- Фильтр категорий -->
            <div class="meditations-filter">
                <button class="filter-btn active" data-category="all">Все</button>
                <?php foreach ($categories as $category): ?>
                    <button class="filter-btn"
                        data-category="<?= e($category['id']) ?>"><?= e($category['name']) ?></button>
                <?php endforeach; ?>
                <button class="filter-btn" data-category="favorites">Избранное</button>
            </div>

            <!-- Список медитаций -->
            <div class="meditations-grid" id="meditationsGrid">
                <?php if (empty($currentPageMeditations)): ?>
                    <div class="no-meditations">
                        <p>Медитации не найдены</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($currentPageMeditations as $meditation): ?>
                        <div class="meditation-card" data-category="<?= e($meditation['category_id']) ?>">
                            <div class="meditation-card__header">
                                <div class="meditation-card__icon">
                                    <svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true" focusable="false">
                                        <circle cx="16" cy="16" r="14" />
                                    </svg>
                                </div>
                                <div class="meditation-card__info">
                                    <h3 class="meditation-card__title"><?= e($meditation['title']) ?></h3>
                                    <div class="meditation-card__meta">
                                        <p class="meditation-card__duration">
                                            <?= $meditationsManager->formatDuration($meditation['duration']) ?>
                                        </p>
                                        <span class="meditation-card__category">
                                            <?= e($meditation['category_name'] ?? 'Без категории') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="meditation-card__player">
                                <audio controls class="audio-player">
                                    <source src="<?= e($meditation['audio_file']) ?>" type="audio/mpeg">
                                    Ваш браузер не поддерживает аудио элемент.
                                </audio>
                            </div>
                            <div class="meditation-card__description">
                                <div class="meditation-card__description-text">
                                    <?php if (strlen($meditation['description']) > 130): ?>
                                        <span
                                            class="description-short"><?= e(substr($meditation['description'], 0, 130)) ?>...</span>
                                        <span class="description-full"
                                            style="display: none;"><?= e($meditation['description']) ?></span>
                                    <?php else: ?>
                                        <?= e($meditation['description']) ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (strlen($meditation['description']) > 130): ?>
                                    <button class="meditation-card__read-more" onclick="toggleDescription(this)">
                                        Далее
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="meditation-card__actions">
                                <div class="meditation-card__stats">
                                    <button class="meditation-card__like-btn"
                                        onclick="toggleLike('<?= e($meditation['id']) ?>')"
                                        data-meditation-id="<?= e($meditation['id']) ?>">
                                        <svg class="like-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                        <span class="like-count"><?= $meditation['likes'] ?></span>
                                    </button>
                                    <button class="meditation-card__favorite-btn"
                                        onclick="toggleFavorite('<?= e($meditation['id']) ?>')"
                                        data-meditation-id="<?= e($meditation['id']) ?>">
                                        <svg class="favorite-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                                <button class="meditation-card__share-btn"
                                    onclick="shareMeditation('<?= e($meditation['id']) ?>', '<?= e($meditation['title']) ?>')">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12 6C13.1046 6 14 5.10457 14 4C14 2.89543 13.1046 2 12 2C10.8954 2 10 2.89543 10 4C10 4.28862 10.0587 4.56479 10.1659 4.8186L6.46482 6.66688C6.02247 6.24892 5.43744 6 4.8 6C3.80589 6 3 6.80589 3 7.8C3 8.79411 3.80589 9.6 4.8 9.6C5.43744 9.6 6.02247 9.35108 6.46482 8.93312L10.1659 10.7814C10.0587 11.0352 10 11.3114 10 11.6C10 12.7046 10.8954 13.6 12 13.6C13.1046 13.6 14 12.7046 14 11.6C14 10.4954 13.1046 9.6 12 9.6C11.3626 9.6 10.7776 9.84892 10.3352 10.2669L6.63418 8.41858C6.74137 8.16479 6.8 7.88862 6.8 7.6C6.8 7.31138 6.74137 7.03521 6.63418 6.78142L10.3352 4.93312C10.7776 5.35108 11.3626 5.6 12 5.6C12.3314 5.6 12.6495 5.53273 12.9419 5.40818L12.9419 5.40818Z"
                                            fill="currentColor" />
                                    </svg>
                                    Поделиться
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            
            <!-- Пагинация -->

            <!-- Информация о медитациях -->        <div class="pagination-wrapper">
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?= buildMeditationUrl($page - 1, $category, $search) ?>" class="pagination__prev">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            Назад
                        </a>
                    <?php endif; ?>

                    <div class="pagination__pages">
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);

                        if ($startPage > 1): ?>
                            <a href="<?= buildMeditationUrl(1, $category, $search) ?>" class="pagination__page">1</a>
                            <?php if ($startPage > 2): ?>
                                <span class="pagination__dots">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="pagination__page pagination__page--active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= buildMeditationUrl($i, $category, $search) ?>" class="pagination__page"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <span class="pagination__dots">...</span>
                            <?php endif; ?>
                            <a href="<?= buildMeditationUrl($totalPages, $category, $search) ?>"
                                class="pagination__page"><?= $totalPages ?></a>
                        <?php endif; ?>
                    </div>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= buildMeditationUrl($page + 1, $category, $search) ?>" class="pagination__next">
                            Вперед
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="pagination__info">
                <?php if ($totalPages > 1): ?>
                    Показано <?= ($offset + 1) ?>-<?= min($offset + $perPage, $totalMeditations) ?> из
                    <?= $totalMeditations ?> медитаций
                <?php else: ?>
                    Показано <?= $totalMeditations ?> медитаций
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA секция -->
    <section class="meditations-cta section">
        <div class="wrapper">
            <div class="cta-content text-center">
                <h2 class="cta-title">Нужна индивидуальная консультация?</h2>
                <p class="cta-subtitle">
                    Если медитации не решают вашу проблему полностью,
                    запишитесь на индивидуальную консультацию с психологом
                </p>
                <div class="cta-buttons">
                    <button class="btn btn--white btn--large" data-popup="call-back-popup"
                        data-form-source="Медитации: Нужна индивидуальная консультация?">
                        ПОЛУЧИТЬ КОНСУЛЬТАЦИЮ
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M18.3333 14.1V16.6C18.3343 16.8321 18.2867 17.0618 18.1937 17.2745C18.1008 17.4871 17.9644 17.6776 17.7935 17.8329C17.6225 17.9882 17.4201 18.1047 17.2001 18.1759C16.9801 18.2471 16.7478 18.2714 16.5167 18.2467C13.9523 17.788 11.4892 16.8595 9.24167 15.5083C7.18694 14.3159 5.35067 12.7446 3.81667 10.8583C2.45833 8.61726 1.53733 6.16226 1.08333 3.60833C1.05852 3.37723 1.08272 3.14489 1.15381 2.92489C1.2249 2.70489 1.34118 2.50249 1.49633 2.33156C1.65148 2.16063 1.84179 2.02427 2.05431 1.93128C2.26683 1.83829 2.49638 1.79077 2.72833 1.79166H5.22833C5.69944 1.78891 6.16044 1.92291 6.55372 2.17612C6.947 2.42933 7.25456 2.79082 7.44167 3.21666C7.70333 3.83666 8.04167 4.41666 8.44167 4.94166C8.55667 5.09166 8.61667 5.28333 8.60833 5.47833C8.55833 6.00833 8.36667 6.51666 8.04999 6.95833C7.91666 7.14166 7.76667 7.30833 7.59999 7.45833C7.48667 7.55833 7.39999 7.68333 7.34999 7.82499C7.29999 7.96666 7.28833 8.11666 7.31667 8.26166C7.49999 9.10833 7.93333 9.89166 8.57499 10.5333C9.21666 11.175 10 11.6083 10.8467 11.7917C10.9917 11.82 11.1417 11.8083 11.2833 11.7583C11.425 11.7083 11.55 11.6217 11.65 11.5083C11.8 11.3417 11.9667 11.1917 12.15 11.0583C12.5917 10.7417 13.1083 10.55 13.6383 10.5C13.8333 10.4917 14.025 10.5517 14.175 10.6667C14.7 11.0667 15.28 11.405 15.9 11.6667C16.3258 11.8538 16.6873 12.1613 16.9405 12.5546C17.1937 12.9479 17.3277 13.4089 17.325 13.88L18.3333 14.1Z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                    <a href="/services.php" class="btn btn--accent btn--large">Узнать об услугах</a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/new-footer.php'; ?>

    <script src="/js/main.js"></script>
    <script src="js/new-components.js"></script>
    <script src="/js/jquery.maskedinput.min.js"></script>
    <script src="/js/script.js"></script>
    <script src="/js/form-handler.js"></script>
    <script src="/js/meditations.js"></script>
    <script src="/js/unified-mobile-menu.js"></script>
    <script src="/js/new-homepage.js?v=3.1"></script>

    <script>
        // Функция для переключения описания медитации
        function toggleDescription(button) {
            const descriptionText = button.previousElementSibling;
            const shortText = descriptionText.querySelector('.description-short');
            const fullText = descriptionText.querySelector('.description-full');

            if (shortText && fullText) {
                if (shortText.style.display === 'none') {
                    // Показываем короткий текст
                    shortText.style.display = 'inline';
                    fullText.style.display = 'none';
                    button.textContent = 'Далее';
                } else {
                    // Показываем полный текст
                    shortText.style.display = 'none';
                    fullText.style.display = 'inline';
                    button.textContent = 'Скрыть';
                }
            }
        }
    </script>
</body>

</html>