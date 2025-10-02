<?php
session_start();
require_once 'includes/functions.php';

// Проверка режима обслуживания
if (isMaintenanceMode() && !isAdminAccess()) {
    header('Location: /maintenance.php');
    exit;
}

$meta = [
    'title' => 'Контакты психолога Дениса Черкаса - Связаться с психологом',
    'description' => 'Свяжитесь с психологом Денисом Черкасом. Телефон: ' . getContactSettings()['phone'] . ', WhatsApp, Telegram. Онлайн консультации в удобное время.',
    'keywords' => 'контакты психолога, связаться с психологом, Денис Черкас контакты, телефон психолога'
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

    <link rel="canonical" href="https://cherkas-therapy.ru/contact.php" />
    <meta property="og:locale" content="ru_RU" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= e($meta['title']) ?>" />
    <meta property="og:description" content="<?= e($meta['description']) ?>" />
    <meta property="og:url" content="https://cherkas-therapy.ru/contact.php" />
    <meta property="og:site_name" content="Психолог Денис Черкас" />
    <meta property="og:image" content="https://cherkas-therapy.ru/image/23-1.jpg" />
    <meta property="og:image:width" content="1920" />
    <meta property="og:image:height" content="1080" />
    <meta property="og:image:type" content="image/jpeg" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= e($meta['title']) ?>" />
    <meta name="twitter:image" content="https://cherkas-therapy.ru/image/23-1.jpg" />
    <!-- CSRF токен для AJAX-отправки форм -->
    <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>" />

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "Денис Черкас",
        "jobTitle": "Психолог",
        "description": "Специалист по зависимостям и созависимости",
        "image": "https://cherkas-therapy.ru/image/445-1.png",
        "telephone": "+79936202951",
        "email": "cherkarts.denis@gmail.com",
        "sameAs": [
                            getContactSettings()['telegram_url'],
            "https://wa.me/79936202951"
        ],
        "address": {
            "@type": "PostalAddress",
            "addressCountry": "RU"
        }
    }
    </script>

    <link rel="stylesheet" href="css/new-homepage.css?v=7.6" type="text/css" media="all" />
    <link rel="stylesheet" href="css/fancybox.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/font.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/pages.css" type="text/css" media="all" />
    <link rel="stylesheet" href="css/container-width-fix.css?v=1.0" type="text/css" media="all" />
    <link rel="stylesheet" href="css/unified-mobile-menu.css" type="text/css" media="all" />
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />
    <link rel="stylesheet" href="css/header-unification.css" />



    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="favicon/ms-icon.png" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="yandex-verification" content="abe245cbb3b37351" />
</head>

<body class="page">
    <?php include 'includes/new-header.php'; ?>

    <section class="contact-hero">
        <div class="wrapper">
            <div class="contact-hero__content">
                <h1 class="contact-hero__title">
                    <span class="contact-hero__title-accent">КОНТАКТЫ</span>
                </h1>
                <p class="contact-hero__subtitle">
                    Свяжитесь со мной любым удобным способом. Я отвечу в течение 30 минут.
                </p>
            </div>
        </div>
    </section>

    <section class="contact-content">
        <div class="wrapper">
            <div class="contact-content__container">
                <div class="contact-info">
                    <h2>Свяжитесь со мной</h2>
                    <p>
                        Я доступен для связи в любое время. Выберите наиболее удобный для вас способ связи,
                        и я отвечу в кратчайшие сроки.
                    </p>

                    <div class="contact-details">
                        <div class="contact-detail">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M18.3333 14.1V16.6C18.3343 16.8321 18.2867 17.0618 18.1937 17.2745C18.1008 17.4871 17.9644 17.678 17.7935 17.8349C17.6225 17.9918 17.4201 18.1113 17.2007 18.1854C16.9813 18.2595 16.7499 18.2866 16.5167 18.265C13.9523 17.988 11.4892 17.1118 9.32498 15.7083C7.31163 14.4289 5.60451 12.7218 4.32498 10.7083C2.91663 8.53426 2.04019 6.05908 1.76665 3.48333C1.74504 3.25082 1.77204 3.01977 1.84579 2.80081C1.91953 2.58185 2.03846 2.37978 2.19462 2.2089C2.35078 2.03802 2.54072 1.90147 2.75266 1.80824C2.9646 1.715 3.19367 1.66699 3.42498 1.66666H5.92498C6.32971 1.66268 6.72148 1.80589 7.02845 2.06945C7.33541 2.333 7.53505 2.69948 7.59165 3.09999C7.69736 3.89957 7.89294 4.68557 8.17498 5.44166C8.28796 5.73992 8.31137 6.06407 8.24165 6.37499C8.17193 6.68591 8.01205 6.96903 7.78332 7.19166L6.74165 8.23333C7.92791 10.3446 9.65535 12.072 11.7667 13.2583L12.8083 12.2167C13.0309 11.9879 13.3141 11.8281 13.625 11.7583C13.9359 11.6886 14.2601 11.712 14.5583 11.825C15.3144 12.107 16.1004 12.3026 16.9 12.4083C17.3047 12.4656 17.6746 12.6692 17.9389 12.9815C18.2032 13.2938 18.3438 13.6916 18.3333 14.1Z"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                            </svg>
                            <a
                                href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['phone']); ?>"><?php echo getContactSettings()['phone']; ?></a>
                        </div>
                        <div class="contact-detail">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M17.5 3.33334H2.5C1.57952 3.33334 0.833336 4.07952 0.833336 5V15C0.833336 15.9205 1.57952 16.6667 2.5 16.6667H17.5C18.4205 16.6667 19.1667 15.9205 19.1667 15V5C19.1667 4.07952 18.4205 3.33334 17.5 3.33334Z"
                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M0.833336 5L10 10.8333L19.1667 5" stroke="currentColor" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <a href="mailto:cherkarts.denis@gmail.com">cherkarts.denis@gmail.com</a>
                        </div>
                        <div class="contact-detail">
                            <img src="/image/whats-app.png" alt="Telegram" width="24" height="24">
                            <a href="https://wa.me/+79936202951" target="_blank">WhatsApp</a>
                        </div>
                        <div class="contact-detail">
                            <img src="/image/telegram.png" alt="Telegram" width="24" height="24">
                            <a href="<?php echo getContactSettings()['telegram_url']; ?>" target="_blank">Telegram</a>
                        </div>

                        <div class="contact-detail">
                            <img src="/image/vk.png" alt="vk" width="24" height="24">
                            <a href="https://vk.com/cherkas_therapy" target="_blank">VK</a>
                        </div>

                        <div class="contact-detail">
                            <img src="/image/instagram.png" alt="instagram" width="24" height="24">
                            <a href="https://www.instagram.com/cherkas_therapy/" target="_blank">Instagram</a>
                        </div>
                        <span class="meta-notice">(Meta, признана экстремистской организацией на территории РФ)</span>



                    </div>
                </div>

                <div class="contact-form">
                    <h3>Напишите мне</h3>

                    <p class="contact-form__subtitle">
                        Заполните форму, и я свяжусь с вами в удобное время
                    </p><br>

                    <form class="contact-form__form" id="contactForm" method="post">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>" />
                        <input type="hidden" name="form_type" value="Контактная форма" />
                        <input type="hidden" name="form_source" value="Страница контактов" />
                        <input type="hidden" name="page_url" value="<?= $_SERVER['REQUEST_URI'] ?>" />

                        <div class="form-group">
                            <label for="name">Ваше имя *</label>
                            <input type="text" id="name" name="name" required />
                        </div>

                        <div class="form-group">
                            <label for="phone">Телефон *</label>
                            <input type="tel" id="phone" name="phone" required />
                        </div>

                        <div class="form-group">
                            <label for="message">Сообщение</label>
                            <textarea id="message" name="message" rows="5"
                                placeholder="Опишите вашу проблему или вопрос..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="preferred_time">Удобное время для звонка</label>
                            <select id="preferred_time" name="time" class="time-select no-nice-select">
                                <option value="">Выберите время</option>
                                <option value="9:00-12:00">9:00-12:00</option>
                                <option value="12:00-15:00">12:00-15:00</option>
                                <option value="15:00-18:00">15:00-18:00</option>
                                <option value="18:00-21:00">18:00-21:00</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="agreement" required />
                                <span class="checkmark"></span>
                                Я согласен с <a href="#" class="privacy-policy-link"
                                    data-popup="privacy-policy-popup">политикой конфиденциальности</a>
                            </label>
                        </div>

                        <button type="submit" class="contact-form__btn md-main-color-btn">
                            <span>ОТПРАВИТЬ СООБЩЕНИЕ</span>
                            <img src="image/phone.svg" alt="" />
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-schedule">
        <div class="wrapper">
            <div class="contact-schedule__container">
                <h2 class="contact-schedule__title">Режим работы</h2>
                <div class="contact-schedule__content">

                    <div class="schedule-item">
                        <div class="schedule-item__day">Понедельник - Пятница</div>
                        <div class="schedule-item__time">9:00 - 22:00</div>
                        <div class="schedule-item__description">
                            Основное время для консультаций и звонков
                        </div>
                    </div>

                    <div class="schedule-item schedule-item--weekend">
                        <div class="schedule-item__day">Суббота - Воскресенье</div>
                        <div class="schedule-item__time">Выходной</div>
                        <div class="schedule-item__description">
                            Заявки обрабатываются, но консультации не проводятся
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>





    <?php include 'includes/new-footer.php'; ?>

    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script src="js/new-components.js"></script>
    <script src="js/fancybox.umd.js"></script>
    <script src="js/script.js"></script>
    <script src="js/jquery.maskedinput.min.js"></script>
    <script src="js/unified-mobile-menu.js"></script>

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

        // Меню инициализируется в js/new-homepage.js

        // Дополнительная инициализация для бургер меню
        $(document).ready(function () {
            // Убеждаемся, что бургер меню работает
            console.log('Document ready - checking burger menu');

            // Проверяем наличие элементов бургер меню
            if ($('[headerBtn_JS]').length > 0) {
                console.log('Burger menu button found');
            } else {
                console.log('Burger menu button not found');
            }

            // Проверяем наличие попапов
            if ($('[popupID]').length > 0) {
                console.log('Popups found:', $('[popupID]').length);
            } else {
                console.log('No popups found');
            }

            // Принудительная инициализация современного меню
            setTimeout(function () {
                // Меню инициализируется в js/new-homepage.js
            }, 500);
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