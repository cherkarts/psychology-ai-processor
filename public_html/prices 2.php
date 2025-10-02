<?php
session_start();
require_once 'includes/functions.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
    header('Location: /maintenance.php');
    exit;
}

$meta = [
    'title' => 'Цены на услуги психолога Дениса Черкаса - Стоимость консультаций',
    'description' => 'Узнайте стоимость услуг психолога Дениса Черкаса. Бесплатная консультация 15 минут, полная сессия 50 минут - 2500₽. Прозрачные цены без скрытых доплат.',
    'keywords' => 'цены психолога, стоимость консультации, услуги психолога, Денис Черкас цены'
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

    <link rel="canonical" href="https://cherkas-therapy.ru/prices.php" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= e($meta['title']) ?>" />
    <meta property="og:description" content="<?= e($meta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/prices.php" />
    <meta property="og:site_name" content="Психолог Денис Черкас" />
    <meta property="og:image" content="https://cherkas-therapy.ru/image/23-1.jpg" />
    <!-- CSRF токен для AJAX-отправки форм в попапах -->
    <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>" />
    <meta property="og:image:width" content="1920" />
    <meta property="og:image:height" content="1080" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($meta['title']) ?>" />
    <meta name="twitter:image" content="https://cherkas-therapy.ru/image/23-1.jpg" />

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Психологические консультации",
        "description": "<?= e($meta['description']) ?>",
        "provider": {
            "@type": "Person",
            "name": "Денис Черкас",
            "jobTitle": "Психолог"
        },
        "offers": [
            {
                "@type": "Offer",
                "name": "Бесплатная консультация",
                "price": "0",
                "priceCurrency": "RUB",
                "description": "15-минутная консультация для оценки ситуации"
            },
            {
                "@type": "Offer",
                "name": "Полная консультация",
                "price": "2500",
                "priceCurrency": "RUB",
                "description": "50-минутная консультация с полным анализом проблемы"
            },
            {
                "@type": "Offer",
                "name": "Курс терапии",
                "price": "7000",
                "priceCurrency": "RUB",
                "description": "3 консультации по 50 минут"
            }
        ]
    }
    </script>

    <!-- Новые стили -->
    <link rel="stylesheet" href="/css/new-components.css" />
    <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" />
    <link rel="stylesheet" href="/css/fancybox.css" />
    <link rel="stylesheet" href="/css/font.css" />
    <link rel="stylesheet" href="/css/pages.css" />
    <link rel="stylesheet" href="/css/header-unification.css" />
    <link rel="stylesheet" href="/css/prices-enhanced.css" />



    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="favicon/ms-icon.png" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="yandex-verification" content="abe245cbb3b37351" />
</head>

<body class="page">
    <?php include 'includes/new-header.php'; ?>

    <section class="prices-hero">
        <div class="wrapper">
            <div class="prices-hero__content">
                <h1 class="prices-hero__title">
                    <span class="prices-hero__title-accent">Стоимость</span> консультаций
                </h1>
                <p class="prices-hero__subtitle">
                    Прозрачные цены без скрытых доплат.<br> Выбирайте удобный для вас формат работы.
                </p>
            </div>
        </div>
    </section>

    <section class="prices-content">
        <div class="wrapper">
            <div class="services__container">
                <div class="container-flex">

                    <div class="services__item price-card--free">
                        <div class="item-content">
                            <div class="item-title">
                                ЗНАКОМСТВО
                            </div>
                            <div class="item-text">
                                <p>Встреча онлайн по Telegram или WhatsApp</p>
                                <p>Длительность 15 минут</p>
                                <p>Знакомство и определение запроса</p>
                                <p>Подбираем время</p>
                            </div>
                            <div class="item-bottom">
                                <div class="item-bottom__price">
                                    <span class="price-amount">бесплатно</span>
                                </div>
                                <button class="item-bottom__btn md-main-color-btn" popupopen="call-back-popup"
                                    data-form-source="Цены: Знакомство">
                                    <span>ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="services__item price-card--main">
                        <div class="item-content">
                            <div class="item-title">
                                1 КОНСУЛЬТАЦИЯ
                            </div>
                            <div class="item-text">
                                <p>Встреча онлайн по Telegram или WhatsApp</p>
                                <p>Длительность 50 минут</p>
                                <p>Проработка запроса</p>
                                <p>Возможны домашние задания</p>
                            </div>
                            <div class="item-bottom">
                                <div class="item-bottom__price">
                                    <span class="price-amount">2500 p</span>
                                </div>
                                <button class="item-bottom__btn md-main-color-btn" popupopen="call-back-popup"
                                    data-form-source="Цены: 1 консультация">
                                    <span>ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="services__item price-card--premium">
                        <div class="item-content">
                            <div class="item-title">
                                3 КОНСУЛЬТАЦИИ
                            </div>
                            <div class="item-text">
                                <p>Встреча онлайн по Telegram или WhatsApp</p>
                                <p>Длительность 50 минут</p>
                                <p>Знакомство и определение запроса</p>
                                <p>Возможны домашние задания</p>
                            </div>
                            <div class="item-bottom">
                                <div class="item-bottom__price">
                                    <span class="price-amount">7000 p</span>
                                </div>
                                <button class="item-bottom__btn md-main-color-btn" popupopen="call-back-popup"
                                    data-form-source="Цены: 3 консультации">
                                    <span>ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ</span>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class="prices-info">
        <div class="wrapper">
            <div class="prices-info__container">
                <h2 class="prices-info__title">Важная информация</h2>
                <div class="prices-info__content">

                    <div class="info-item">
                        <div class="info-item__icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                            </svg>
                        </div>
                        <div class="info-item__content">
                            <h3>Формат консультаций</h3>
                            <p>
                                Консультации проводятся через Telegram, WhatsApp, сайт b17.
                            </p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-item__icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" />
                            </svg>
                        </div>
                        <div class="info-item__content">
                            <h3>Оплата</h3>
                            <p>
                                Работаю по предоплате. Оплата производится банковской картой
                                или переводом на карту. Никаких скрытых комиссий.
                            </p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-item__icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z" />
                            </svg>
                        </div>
                        <div class="info-item__content">
                            <h3>Конфиденциальность</h3>
                            <p>
                                Все консультации строго конфиденциальны. Ваша личная информация
                                не передается третьим лицам и не используется в других целях.
                            </p>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-item__icon">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" />
                            </svg>
                        </div>
                        <div class="info-item__content">
                            <h3>Отмена и перенос</h3>
                            <p>
                                Консультацию можно перенести или отменить не позднее чем за 12 часов
                                до назначенного времени. При поздней отмене консультация оплачивается.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class="prices-cta">
        <div class="wrapper">
            <div class="prices-cta__content">
                <div class="prices-cta__icon">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" />
                    </svg>
                </div>
                <h2 class="prices-cta__title">Остались вопросы о ценах?</h2>
                <p class="prices-cta__text">
                    Свяжитесь со мной, и я подробно расскажу о стоимости услуг и помогу выбрать подходящий тариф
                </p>
                <div class="prices-cta__features">
                    <div class="cta-feature">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        <span>Бесплатная консультация</span>
                    </div>
                    <div class="cta-feature">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        <span>Гибкие тарифы</span>
                    </div>
                    <div class="cta-feature">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
                        </svg>
                        <span>Онлайн формат</span>
                    </div>
                </div>
                <button class="prices-cta__btn" popupopen="call-back-popup"
                    data-form-source="Цены: Остались вопросы о ценах?">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" />
                    </svg>
                    <span>УЗНАТЬ ПОДРОБНЕЕ</span>
                </button>
            </div>
        </div>
    </section>

    <?php include 'includes/new-footer.php'; ?>

    <!-- Подключение всех необходимых скриптов в правильном порядке -->
    <script src="js/main.js"></script>
    <script src="js/new-components.js"></script>
    <script src="js/fancybox.umd.js"></script>
    <script src="js/script.js"></script>
    <script src="js/jquery.maskedinput.min.js"></script>
    <script src="js/form-handler.js"></script>
    <script src="js/prices-page-enhancement.js"></script>
    <script src="js/new-homepage.js?v=3.1"></script>

    <script>
        // Инициализация Fancybox
        if (typeof Fancybox !== 'undefined') {
            Fancybox.bind('[data-fancybox]');
            console.log('Fancybox initialized on prices page');
        } else {
            console.log('Fancybox not found on prices page');
        }

        // Дополнительная инициализация попапов для страницы цен
        $(document).ready(function () {
            console.log('Prices page scripts loaded');

            // Проверяем, что попапы работают
            $('body').on('click', '[popupopen]', function () {
                console.log('Popup trigger clicked:', $(this).attr('popupopen'));
            });

            // Применяем маски к полям телефонов
            if (typeof $ !== 'undefined' && $.fn.mask) {
                $('[phoneMask_JS]').mask('+7 (999) 999-99-99');
                console.log('Phone masks applied');
            }

            // Инициализация nice-select для попапов
            if (typeof $.fn.niceSelect !== 'undefined') {
                $('.nice-select').niceSelect();
                console.log('Nice select initialized');
            }
        });

        // Обработка ошибок для предотвращения проблем
        window.addEventListener('error', function (e) {
            console.warn('JavaScript error on prices page:', e.error);
            return false;
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