<?php
session_start();
require_once 'includes/functions.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
    header('Location: /maintenance.php');
    exit;
}

$meta = [
    'title' => 'О психологе Денисе Черкасе - Опыт, образование, подход',
    'description' => 'Узнайте больше о психологе Денисе Черкасе: образование, опыт работы, личная история выздоровления и профессиональный подход к терапии.',
    'keywords' => 'психолог Денис Черкас, образование психолога, опыт работы, личная история, подход к терапии'
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

    <link rel="canonical" href="https://cherkas-therapy.ru/about.php" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= e($meta['title']) ?>" />
    <meta property="og:description" content="<?= e($meta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/about.php" />
    <meta property="og:site_name" content="Психолог Денис Черкас" />
    <meta property="og:image" content="https://cherkas-therapy.ru/image/445-1.png" />
    <meta property="og:image:width" content="1920" />
    <meta property="og:image:height" content="1080" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($meta['title']) ?>" />
    <meta name="twitter:image" content="https://cherkas-therapy.ru/image/445-1.png" />

    <!-- CSRF токен для AJAX-отправки форм в попапах -->
    <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>" />

    <script type="application/ld+json">
    <?= $schema ?>
    </script>

    <!-- Новые стили -->
    <link rel="stylesheet" href="/css/new-components.css" />
    <link rel="stylesheet" href="/css/new-homepage.css?v=7.6" />
    <link rel="stylesheet" href="/css/fancybox.css" />
    <link rel="stylesheet" href="/css/font.css" />
    <link rel="stylesheet" href="/css/pages.css" />
    <link rel="stylesheet" href="/css/header-unification.css" />



    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="favicon/ms-icon.png" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="yandex-verification" content="abe245cbb3b37351" />
</head>

<body class="page">
    <?php include 'includes/new-header.php'; ?>

    <section class="about-hero">
        <div class="wrapper">
            <div class="about-hero__content">
                <h1 class="about-hero__title">
                    <span class="about-hero__title-accent">ОБО</span> МНЕ
                </h1>
                <p class="about-hero__subtitle">
                    Меня зовут Денис Черкас. Я профессиональный психолог с опытом работы
                    более 5 лет, специализирующийся на работе с зависимостями и созависимостью.
                </p>
            </div>
        </div>
    </section>

    <section class="about-content">
        <div class="wrapper">
            <div class="about-content__inner">
                <div class="about-content__container">
                    <div class="about-content__text">
                        <h2>Моя история и миссия</h2>
                        <p>
                            Мой путь в психологии начался с личного опыта преодоления зависимости.
                            Я знаю, каково это - чувствовать себя в ловушке собственных проблем,
                            когда кажется, что выхода нет. Именно этот опыт стал моим главным
                            учителем и мотивацией помогать другим.
                        </p>
                        <p>
                            После собственного выздоровления я получил высшее психологическое
                            образование и более 5 лет практикую, сочетая академические знания
                            с глубоким пониманием того, что переживают мои клиенты.
                            Я не просто теоретик - я человек, который прошел этот путь сам.
                        </p>
                        <p>
                            <em>"Тот, кто прошел через ад, лучше других знает, как помочь тем,
                                кто все еще там находится. Я не просто понимаю ваши проблемы -
                                я чувствую их всем сердцем."</em>
                        </p>

                        <h2>Профессиональная квалификация</h2>
                        <p>
                            <strong>Образование:</strong> Психологический факультет с отличием, специализация
                            "Клиническая психология"
                        </p>
                        <p>
                            <strong>Опыт работы:</strong> Более 5 лет успешной практики, более 500 довольных клиентов
                        </p>
                        <p>
                            <strong>Специализация:</strong> Зависимости, созависимость, тревожные расстройства, работа с
                            травмой, кризисные состояния
                        </p>
                        <p>
                            <strong>Методология:</strong> Интегративный подход, сочетающий когнитивно-поведенческую
                            терапию, методы работы с зависимостями и современные техники психотерапии
                        </p>
                        <p>
                            <strong>Результаты:</strong> 95% клиентов отмечают значительные улучшения уже после первых
                            3-5 сессий
                        </p>
                    </div>
                    <div class="about-content__image">
                        <img src="image/445-1.png" alt="Денис Черкас - Психолог" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="about-approach">
        <div class="wrapper">
            <div class="about-approach__container">
                <h2 class="about-approach__title">Мой подход к работе</h2>
                <div class="about-approach__content">

                    <div class="approach-item">
                        <h3>Индивидуальный подход</h3>
                        <p>
                            Каждый человек уникален, и его проблемы требуют персонального решения.
                            Я не использую шаблонные методы, а разрабатываю индивидуальную
                            стратегию для каждого клиента, учитывая его личность, опыт и цели.
                        </p>
                    </div>

                    <div class="approach-item">
                        <h3>Безопасное пространство</h3>
                        <p>
                            Я создаю атмосферу полного принятия, где можно говорить о любых проблемах
                            без страха осуждения. Мой личный опыт помогает мне понимать
                            чувства и переживания клиентов на глубоком уровне.
                        </p>
                    </div>

                    <div class="approach-item">
                        <h3>Практические результаты</h3>
                        <p>
                            Моя цель - не просто выслушать, а дать конкретные инструменты
                            для решения проблем. Каждая консультация включает практические
                            техники, упражнения и домашние задания для закрепления результатов.
                        </p>
                    </div>

                    <div class="approach-item">
                        <h3>Поддержка на всем пути</h3>
                        <p>
                            Я сопровождаю клиентов на всем пути к выздоровлению,
                            поддерживая их в трудные моменты и отмечая каждый успех.
                            Вместе мы работаем над долгосрочными изменениями и устойчивыми результатами.
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <section class="about-cta">
        <div class="wrapper">
            <div class="about-cta__content">
                <h2 class="about-cta__title">Готовы изменить свою жизнь?</h2>
                <p class="about-cta__text">
                    Запишитесь на бесплатную консультацию и получите персональный план работы.
                    Первая встреча - это возможность познакомиться и понять, как я могу вам помочь.
                </p>
                <button class="about-cta__btn md-main-color-btn" popupOpen="call-back-popup"
                    data-form-source="Обо мне: Готовы изменить свою жизнь?">

                    <span>ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ</span>
                    <img src="image/phone.svg" alt="" />
                </button>
            </div>
        </div>
    </section>

    <?php include 'includes/new-footer.php'; ?>

    <script src="js/main.js"></script>
    <script src="js/new-components.js"></script>
    <script src="js/fancybox.umd.js"></script>
    <script src="js/script.js"></script>
    <script src="js/jquery.maskedinput.min.js"></script>
    <script src="js/form-handler.js?v=1.5"></script>
    <script src="js/new-homepage.js?v=3.1"></script>
    <script>
        // Инициализация Fancybox
        if (typeof Fancybox !== 'undefined') {
            Fancybox.bind('[data-fancybox]');
        }
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