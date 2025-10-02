<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once 'includes/functions.php';

$meta = [
    'title' => '404 - Страница не найдена | Психолог Денис Черкас',
    'description' => 'Страница, которую вы ищете, ушла в бессознательное. Вернитесь к сознанию на главную страницу.',
    'keywords' => '404, страница не найдена, психолог Денис Черкас'
];
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
    <meta name="robots" content="noindex, nofollow" />

    <link rel="canonical" href="https://cherkas-therapy.ru/404.php" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= e($meta['title']) ?>" />
    <meta property="og:description" content="<?= e($meta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/404.php" />
    <meta property="og:site_name" content="Психолог Денис Черкас" />

    	<!-- Универсальные стили -->
	<link rel="stylesheet" href="/css/unified-styles.css" />
	<link rel="stylesheet" href="/css/new-homepage.css?v=7.6" />
	<link rel="stylesheet" href="/css/fancybox.css" />
	<link rel="stylesheet" href="/css/font.css" />
	<link rel="stylesheet" href="/css/mobile.css" />
	<link rel="stylesheet" href="/css/pages.css" />
	


    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="/favicon/ms-icon.png" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="yandex-verification" content="abe245cbb3b37351" />
    <!-- CSRF токен -->
    <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>" />
</head>

<body class="page">
    <section class="error-404">
        <div class="wrapper">
            <div class="error-404__content">
                <div class="error-404__image">
                    <img src="/image/freud-404-l.png" alt="Фрейд с сигарой" />
                </div>
                <div class="error-404__text">
                    <h1 class="error-404__title">
                    Всё, что не проработано, возвращается как ошибка 404. 
                        <span class="error-404__title-accent">Но эта страница — точно не то, что вы искали.</span>
                    </h1>
                    <div class="error-404__description">
                        <p>Страница, которую вы ищете, ушла в бессознательное.</p>
                        <p>Возможно, вы ошиблись адресом, или эта ссылка давно утратила связь с реальностью.</p>
                    </div>
                    <div class="error-404__actions">
                        <a href="/" class="error-404__btn">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                            </svg>
                            <span>Вернуться к сознанию</span>
                        </a>
                        <div class="error-404__links">
                            <a href="/services.php" class="error-404__link">Услуги</a>
                            <a href="/about.php" class="error-404__link">Обо мне</a>
                            <a href="/reviews.php" class="error-404__link">Отзывы</a>
                            <a href="/contact.php" class="error-404__link">Контакты</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="/js/main.js"></script>
    <script src="/js/fancybox.umd.js"></script>
    <script src="/js/script.js"></script>
    <script src="/js/jquery.maskedinput.min.js"></script>
    <script src="/js/new-homepage.js?v=3.1"></script>
</body>
</html> 