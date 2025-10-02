<?php
require_once 'includes/Models/Order.php';
require_once 'includes/Models/Article.php';
require_once 'includes/Models/Meditation.php';
require_once 'includes/Models/Review.php';
require_once 'includes/Models/Product.php';
require_once 'includes/Database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
    header('Location: /maintenance.php');
    exit;
}

// Отключаем кэширование для этой страницы
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$meta = [
    'title' => 'Отзывы клиентов психолога Дениса Черкаса - Реальные истории выздоровления',
    'description' => 'Читайте отзывы клиентов психолога Дениса Черкаса. Реальные истории людей, которые преодолели зависимости, тревожность и другие проблемы.',
    'keywords' => 'отзывы психолога, отзывы Дениса Черкаса, истории выздоровления, отзывы клиентов'
];
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

    <link rel="canonical" href="https://cherkas-therapy.ru/reviews.php" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= e($meta['title']) ?>" />
    <meta property="og:description" content="<?= e($meta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/reviews.php" />
    <meta property="og:site_name" content="Психолог Денис Черкас" />
    <meta property="og:image" content="https://cherkas-therapy.ru/image/reviews-bg.jpg" />
    <meta property="og:image:width" content="1920" />
    <meta property="og:image:height" content="1080" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($meta['title']) ?>" />
    <meta name="twitter:image" content="https://cherkas-therapy.ru/image/reviews-bg.jpg" />

    <!-- CSRF токен для форм -->
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32)) ?>" />

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "Денис Черкас",
        "jobTitle": "Психолог",
        "description": "Специалист по зависимостям и созависимости",
        "image": "https://cherkas-therapy.ru/image/445-1.png",
        "review": [
            {
                "@type": "Review",
                "reviewRating": {
                    "@type": "Rating",
                    "ratingValue": "5",
                    "bestRating": "5"
                },
                "author": {
                    "@type": "Person",
                    "name": "Анна"
                },
                "reviewBody": "Денис помог мне справиться с тревожностью, которая мучила меня годами. Его подход уникален - он не только профессионал, но и человек, который сам прошел через подобные проблемы."
            },
            {
                "@type": "Review",
                "reviewRating": {
                    "@type": "Rating",
                    "ratingValue": "5",
                    "bestRating": "5"
                },
                "author": {
                    "@type": "Person",
                    "name": "Михаил"
                },
                "reviewBody": "Благодаря Денису я смог преодолеть алкогольную зависимость. Его личный опыт и профессиональные знания дали мне надежду и силы для выздоровления."
            }
        ]
    }
    </script>

    <!-- Новые стили -->
    <link rel="stylesheet" href="/css/new-components.css" />
    <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/fancybox.css" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/pages.css" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/container-width-fix.css?v=1.0" type="text/css" media="all" />
    <link rel="stylesheet" href="/css/header-unification.css" />

    <style>
        /* Стили для модального окна формы отзывов */
        .review-form-container {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background: rgba(0, 0, 0, 0.8) !important;
            display: none !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: center !important;
            z-index: 99999 !important;
            padding: 20px !important;
            box-sizing: border-box !important;
            overflow: auto !important;
        }

        .review-form-container .review-form {
            background: white !important;
            border-radius: 20px !important;
            padding: 30px !important;
            max-width: 500px !important;
            width: 100% !important;
            max-height: 85vh !important;
            overflow-y: auto !important;
            position: relative !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4) !important;
            margin: auto !important;
            transform: translateZ(0) !important;
        }

        /* Анимации */
        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes modalFadeOut {
            from {
                opacity: 1;
                transform: scale(1) translateY(0);
            }

            to {
                opacity: 0;
                transform: scale(0.9) translateY(-20px);
            }
        }

        .review-form-container {
            animation: modalFadeIn 0.3s ease-out;
        }

        /* Исправления для мобильных устройств */
        @media (max-width: 768px) {
            .review-form-container {
                padding: 10px !important;
                align-items: flex-start !important;
                padding-top: 60px !important;
            }

            .review-form-container .review-form {
                padding: 20px !important;
                margin: 10px auto !important;
                max-width: calc(100% - 20px) !important;
                width: calc(100% - 20px) !important;
                max-height: calc(100vh - 80px) !important;
                border-radius: 12px !important;
            }

            .review-form-header {
                display: block !important;
                text-align: center !important;
                padding-right: 40px !important;
            }
        }

        @media (max-width: 480px) {
            .review-form-container {
                padding: 5px !important;
                padding-top: 70px !important;
            }

            .review-form-container .review-form {
                padding: 15px !important;
                margin: 5px auto !important;
                max-width: calc(100% - 10px) !important;
                width: calc(100% - 10px) !important;
                max-height: calc(100vh - 80px) !important;
                border-radius: 8px !important;
            }
        }

        /* Additional header layout fixes */
        .review-form-header {
            display: block !important;
            text-align: center !important;
            margin-bottom: 25px !important;
            position: relative !important;
            padding-right: 40px !important;
        }

        .review-form-header h3 {
            margin: 0 !important;
            color: #333 !important;
            font-size: 24px !important;
            text-align: center !important;
        }

        .review-form-header p {
            margin: 8px 0 0 0 !important;
            color: #666 !important;
            font-size: 14px !important;
            text-align: center !important;
        }

        /* Гарантированное центрирование */
        .review-form-container[style*="display: flex"] {
            justify-content: center !important;
            align-items: center !important;
        }
    </style>
</head>

<body>
    <?php include 'includes/new-header.php'; ?>

    <section class="reviews-hero">
        <div class="wrapper">
            <div class="reviews-hero__content">
                <h1 class="reviews-hero__title md-main-title">
                    <span style="color: #6a7e9f">ОТЗЫВЫ КЛИЕНТОВ</span>
                </h1>
                <p class="reviews-hero__subtitle">
                    Реальные истории людей, которые преодолели зависимости и другие проблемы
                </p>
            </div>
        </div>
    </section>

    <section class="reviews-content">
        <div class="wrapper">
            <div class="reviews-content__container">
                <!-- Фильтры по типу отзывов -->
                <div class="reviews-filters">
                    <button class="filter-btn active" data-type="all">Все отзывы</button>
                    <button class="filter-btn" data-type="text">Текстовые</button>
                    <button class="filter-btn" data-type="photo">Фото</button>
                    <button class="filter-btn" data-type="video">Видео</button>
                </div>

                <div class="reviews-carousel">
                    <div class="reviews-carousel__container">
                        <div class="swiper-container reviews-swiper">
                            <div class="swiper-wrapper" id="reviewsContainer">
                                <!-- Отзывы будут загружены динамически -->
                            </div>
                            <div class="swiper-notification" aria-live="assertive" aria-atomic="true"></div>
                        </div>
                        <div class="slider-btn slider-prev-btn reviews-prev-btn">
                            <img src="image/slider-prev.svg" alt="Предыдущий">
                        </div>
                        <div class="slider-btn slider-next-btn reviews-next-btn">
                            <img src="image/slider-next.svg" alt="Следующий">
                        </div>
                        <div class="slider-pagination swiper-pagination-bullets" id="carouselIndicators">
                            <!-- Индикаторы будут созданы динамически -->
                        </div>
                    </div>
                </div>

                <!-- Кнопка добавления отзыва -->
                <div class="reviews-add-btn">
                    <button class="add-review-btn md-main-color-btn" id="addReviewBtn">
                        <span>Добавить отзыв</span>
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                        </svg>
                    </button>
                </div>

                <!-- Форма добавления отзыва (скрыта по умолчанию) -->
                <?php include 'includes/review-form-new.php'; ?>
            </div>
        </div>
    </section>

    <section class="reviews-stats">
        <div class="wrapper">
            <div class="reviews-stats__container">
                <div class="stat-item">
                    <div class="stat-item__icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                    </div>
                    <div class="stat-item__number" data-count="150">0</div>
                    <div class="stat-item__label">Довольных клиентов</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                    </div>
                    <div class="stat-item__number" data-count="5.0">0</div>
                    <div class="stat-item__label">Средняя оценка</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                    </div>
                    <div class="stat-item__number" data-count="95">0</div>
                    <div class="stat-item__label">Успешных случаев</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__icon">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z" />
                            <path d="M12.5 7H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
                        </svg>
                    </div>
                    <div class="stat-item__number" data-count="8">0</div>
                    <div class="stat-item__label">Лет опыта</div>
                </div>
            </div>
        </div>
    </section>

    <section class="reviews-cta">
        <div class="wrapper">
            <div class="reviews-cta__content">
                <div class="reviews-cta__icon">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                </div>
                <h2 class="reviews-cta__title">Готовы присоединиться к успешным историям?</h2>
                <p class="reviews-cta__text">
                    Запишитесь на консультацию и начните свой путь к изменениям.
                    Первый шаг к новой жизни - это решение обратиться за помощью.
                </p>
                <div class="reviews-cta__features">
                    <div class="cta-feature">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        <span>Бесплатная первая консультация</span>
                    </div>
                    <div class="cta-feature">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        <span>Индивидуальный подход</span>
                    </div>
                    <div class="cta-feature">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        <span>Конфиденциальность</span>
                    </div>
                </div>
                <button class="reviews-cta__btn" popupOpen="call-back-popup"
                    data-form-source="Отзывы: Готовы присоединиться к успешным историям?">
                    <span>ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ</span>
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" />
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <?php include 'includes/new-footer.php'; ?>

    <!-- Загружаем jQuery первым -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>

    <!-- Исправление ошибки o.call перед загрузкой main.js -->
    <script>
        // Исправление ошибки o.call в main.js
        if (typeof Object.prototype.toString !== 'undefined') {
            window.o = Object.prototype.toString;
        }

        // Дополнительная защита от ошибок в main.js
        window.addEventListener('error', function (e) {
            if (e.message.includes('o.call is not a function')) {
                console.warn('Перехвачена ошибка o.call, применяем исправление...');
                if (typeof Object.prototype.toString !== 'undefined') {
                    window.o = Object.prototype.toString;
                }
                return false; // Предотвращаем дальнейшую обработку ошибки
            }
        });

        // Переопределение функции isPlainObject после загрузки jQuery
        window.addEventListener('load', function () {
            if (typeof $ !== 'undefined' && $.isPlainObject) {
                var originalIsPlainObject = $.isPlainObject;
                $.isPlainObject = function (obj) {
                    try {
                        return originalIsPlainObject.call(this, obj);
                    } catch (e) {
                        if (e.message.includes('o.call is not a function')) {
                            // Исправляем ошибку и повторяем вызов
                            if (typeof Object.prototype.toString !== 'undefined') {
                                window.o = Object.prototype.toString;
                            }
                            return originalIsPlainObject.call(this, obj);
                        }
                        throw e;
                    }
                };
                console.log('✅ Функция isPlainObject защищена от ошибок');
            }
        });
    </script>

    <!-- Подключаем main.js для работы попапов -->
    <script src="js/main.js?v=<?php echo time(); ?>"></script>
    <script src="js/new-components.js"></script>
    <script src="js/form-handler.js?v=1.5"></script>
    <script src="js/fancybox.umd.js?v=<?php echo time(); ?>"></script>
    <script src="js/jquery.maskedinput.min.js?v=<?php echo time(); ?>"></script>
    <script src="js/script.js?v=<?php echo time(); ?>"></script>
    <script src="js/cart.js?v=<?php echo time(); ?>"></script>
    <script src="js/carousel.js?v=<?php echo time(); ?>"></script>
    <script src="js/new-homepage.js?v=3.1"></script>

    <script>
        // Инициализация Fancybox
        if (typeof Fancybox !== 'undefined') {
            Fancybox.bind('[data-fancybox]');
        }

        // Инициализация корзины
        if (typeof CartManager !== 'undefined') {
            new CartManager();
        }

        // Упрощенная инициализация для совместимости
        $(document).ready(function () {
            console.log('✅ Страница отзывов загружена успешно');

            // Проверяем наличие форм
            const forms = document.querySelectorAll('.md-standart-form');
            console.log('Найдено форм для отправки в Telegram:', forms.length);

            // Проверяем наличие попапов
            const popups = document.querySelectorAll('[popupID]');
            console.log('Найдено попапов:', popups.length);

            // Проверяем работу form-handler
            if (typeof initFormHandler === 'function') {
                console.log('✅ Form handler доступен');
            } else {
                console.warn('⚠️ Form handler не найден');
            }

            // Проверяем исправление ошибки o.call
            if (typeof window.o === 'function') {
                console.log('✅ Исправление o.call применено');
            } else {
                console.warn('⚠️ Исправление o.call не применено');
            }

            // Финальная проверка всех компонентов
            console.log('=== ФИНАЛЬНАЯ ПРОВЕРКА ===');
            console.log('jQuery:', typeof $ !== 'undefined' ? '✅ Загружен' : '❌ Не загружен');
            console.log('Fancybox:', typeof Fancybox !== 'undefined' ? '✅ Загружен' : '❌ Не загружен');
            console.log('Form Handler:', typeof initFormHandler === 'function' ? '✅ Доступен' : '❌ Не доступен');
            console.log('o.call исправление:', typeof window.o === 'function' ? '✅ Применено' : '❌ Не применено');
            console.log('Формы для Telegram:', document.querySelectorAll('.md-standart-form').length);
            console.log('Попапы:', document.querySelectorAll('[popupID]').length);
            console.log('========================');
        });
    </script>

    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function (m, e, t, r, i, k, a) {
            m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments) };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) { if (document.scripts[j].src === r) { return; } }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
        })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js?id=103948722', 'ym');

        ym(103948722, 'init', { ssr: true, webvisor: true, clickmap: true, ecommerce: "dataLayer", accurateTrackBounce: true, trackLinks: true });
    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/103948722" style="position:absolute; left:-9999px;" alt="" /></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
</body>

</html>