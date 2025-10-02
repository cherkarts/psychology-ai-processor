<?php
session_start();
require_once 'includes/functions.php';

$meta = [
  'title' => 'Тревожность и страхи - Психолог Денис Черкас | Москва',
  'description' => 'Помощь в преодолении тревожности, панических атак, фобий и страхов. Эффективные методики для снижения тревоги и восстановления спокойствия.',
  'keywords' => 'тревожность, страхи, фобии, панические атаки, тревожное расстройство, психолог, Москва'
];
$schema = generateSchemaMarkup('person');
?>
<!DOCTYPE html>
<html class="js" lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title><?= e($meta['title']) ?></title>
  <meta name="description" content="<?= e($meta['description']) ?>">
  <meta name="keywords" content="<?= e($meta['keywords']) ?>">

  <!-- Open Graph -->
  <meta property="og:title" content="Тревожность и страхи - Психолог Денис Черкас">
  <meta property="og:description" content="Помощь в преодолении тревожности, панических атак и фобий">
  <meta property="og:image" content="https://cherkas-therapy.ru/image/23-1.jpg">
  <meta property="og:url" content="https://cherkas-therapy.ru/anxiety.php">

  <!-- CSRF токен -->
  <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>">

  <!-- Стили -->
  <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
  <link rel="stylesheet" href="css/new-homepage.css?v=7.6">
  <link rel="stylesheet" href="css/fancybox.css">
  <link rel="stylesheet" href="css/header-unification.css">
  <link rel="stylesheet" href="css/pages.css">
  <link rel="stylesheet" href="css/specialization-pages.css">

  <!-- Шрифты -->
  <link rel="preload" href="fonts/Inter/Inter-Regular.woff" as="font" type="font/woff" crossorigin>
  <link rel="preload" href="fonts/Inter/Inter-Bold.woff" as="font" type="font/woff" crossorigin>

  <script type="application/ld+json"><?= $schema ?></script>
</head>

<body class="page anxiety-page">
  <?php include 'includes/new-header.php'; ?>

  <!-- Hero секция -->
  <section class="anxiety-hero">
    <div class="wrapper">
      <div class="anxiety-hero__content">
        <div class="anxiety-hero__badge">
          <span>Специализация</span>
        </div>
        <h1 class="anxiety-hero__title md-main-title">
          <span class="anxiety-hero__title-accent">ТРЕВОЖНОСТЬ</span> И СТРАХИ
        </h1>
        <p class="anxiety-hero__subtitle">
          Помогаю преодолеть тревожность, панические атаки и фобии.
          Использую проверенные методики для снижения тревоги и восстановления внутреннего спокойствия.
        </p>
        <div class="anxiety-hero__stats">
          <div class="hero-stat">
            <span class="hero-stat__number">92%</span>
            <span class="hero-stat__label">Снижение тревоги</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat__number">7+</span>
            <span class="hero-stat__label">Лет опыта</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat__number">300+</span>
            <span class="hero-stat__label">Клиентов</span>
          </div>
        </div>
      </div>
    </div>
    <div class="anxiety-hero__decoration">
      <div class="hero-shape hero-shape--1"></div>
      <div class="hero-shape hero-shape--2"></div>
      <div class="hero-shape hero-shape--3"></div>
    </div>
  </section>

  <!-- Виды тревожности -->
  <section class="anxiety-types">
    <div class="wrapper">
      <div class="anxiety-types__header">
        <h2 class="anxiety-types__title">Виды тревожности и страхов</h2>
        <p class="anxiety-types__subtitle">
          Каждый вид тревожности требует особого подхода и специализированных методик
        </p>
      </div>
      <div class="anxiety-types__grid">

        <div class="anxiety-card">
          <div class="anxiety-card__icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="#6a7e9f" opacity="0.1" />
              <path
                d="M24 8C15.163 8 8 15.163 8 24s7.163 16 16 16 16-7.163 16-16S32.837 8 24 8zm0 28c-6.627 0-12-5.373-12-12S17.373 8 24 8s12 5.373 12 12-5.373 12-12 12z"
                fill="#6a7e9f" />
              <path
                d="M24 16c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zm0 12c-2.206 0-4-1.794-4-4s1.794-4 4-4 4 1.794 4 4-1.794 4-4 4z"
                fill="#6a7e9f" />
            </svg>
          </div>
          <h3 class="anxiety-card__title">Генерализованное тревожное расстройство</h3>
          <p class="anxiety-card__description">
            Постоянная тревога по поводу различных аспектов жизни,
            сопровождающаяся физическими симптомами и нарушением сна.
          </p>
          <ul class="anxiety-card__features">
            <li>Постоянное беспокойство</li>
            <li>Мышечное напряжение</li>
            <li>Нарушения сна</li>
            <li>Концентрация внимания</li>
          </ul>
        </div>

        <div class="anxiety-card">
          <div class="anxiety-card__icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="#d2afa0" opacity="0.1" />
              <path
                d="M24 4C12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20S35.046 4 24 4zm0 36c-8.837 0-16-7.163-16-16S15.163 8 24 8s16 7.163 16 16-7.163 16-16 16z"
                fill="#d2afa0" />
              <path d="M20 16h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="#d2afa0" />
            </svg>
          </div>
          <h3 class="anxiety-card__title">Панические атаки</h3>
          <p class="anxiety-card__description">
            Внезапные приступы интенсивного страха с физическими симптомами:
            учащенное сердцебиение, одышка, головокружение.
          </p>
          <ul class="anxiety-card__features">
            <li>Внезапные приступы страха</li>
            <li>Физические симптомы</li>
            <li>Страх смерти</li>
            <li>Избегание ситуаций</li>
          </ul>
        </div>

        <div class="anxiety-card">
          <div class="anxiety-card__icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="#6a7e9f" opacity="0.1" />
              <path
                d="M24 8C15.163 8 8 15.163 8 24s7.163 16 16 16 16-7.163 16-16S32.837 8 24 8zm0 28c-6.627 0-12-5.373-12-12S17.373 8 24 8s12 5.373 12 12-5.373 12-12 12z"
                fill="#6a7e9f" />
              <path
                d="M24 16c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zm0 12c-2.206 0-4-1.794-4-4s1.794-4 4-4 4 1.794 4 4-1.794 4-4 4z"
                fill="#6a7e9f" />
            </svg>
          </div>
          <h3 class="anxiety-card__title">Социальная тревожность</h3>
          <p class="anxiety-card__description">
            Страх социальных ситуаций, публичных выступлений,
            боязнь осуждения и негативной оценки окружающих.
          </p>
          <ul class="anxiety-card__features">
            <li>Страх публичных выступлений</li>
            <li>Боязнь социальных ситуаций</li>
            <li>Страх осуждения</li>
            <li>Избегание общения</li>
          </ul>
        </div>

        <div class="anxiety-card">
          <div class="anxiety-card__icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="#d2afa0" opacity="0.1" />
              <path
                d="M24 4C12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20S35.046 4 24 4zm0 36c-8.837 0-16-7.163-16-16S15.163 8 24 8s16 7.163 16 16-7.163 16-16 16z"
                fill="#d2afa0" />
              <path d="M20 16h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="#d2afa0" />
            </svg>
          </div>
          <h3 class="anxiety-card__title">Специфические фобии</h3>
          <p class="anxiety-card__description">
            Интенсивный страх перед конкретными объектами или ситуациями:
            высота, закрытые пространства, животные, медицинские процедуры.
          </p>
          <ul class="anxiety-card__features">
            <li>Страх конкретных объектов</li>
            <li>Избегание ситуаций</li>
            <li>Физические реакции</li>
            <li>Иррациональный страх</li>
          </ul>
        </div>

      </div>
    </div>
  </section>

  <!-- Методы работы -->
  <section class="anxiety-methods">
    <div class="wrapper">
      <div class="anxiety-methods__header">
        <h2 class="anxiety-methods__title">Методы работы с тревожностью</h2>
        <p class="anxiety-methods__subtitle">
          Использую научно обоснованные методики, доказавшие свою эффективность
        </p>
      </div>
      <div class="anxiety-methods__grid">

        <div class="method-item">
          <div class="method-item__number">01</div>
          <h3 class="method-item__title">Когнитивно-поведенческая терапия</h3>
          <p class="method-item__description">
            Работаю с искаженными убеждениями и автоматическими мыслями,
            которые вызывают тревогу. Учу заменять их на более реалистичные.
          </p>
        </div>

        <div class="method-item">
          <div class="method-item__number">02</div>
          <h3 class="method-item__title">Техники релаксации</h3>
          <p class="method-item__description">
            Обучаю дыхательным техникам, прогрессивной мышечной релаксации
            и медитации для снижения физического напряжения.
          </p>
        </div>

        <div class="method-item">
          <div class="method-item__number">03</div>
          <h3 class="method-item__title">Экспозиционная терапия</h3>
          <p class="method-item__description">
            Постепенно и безопасно помогаю столкнуться с пугающими ситуациями,
            чтобы снизить интенсивность страха и тревоги.
          </p>
        </div>

        <div class="method-item">
          <div class="method-item__number">04</div>
          <h3 class="method-item__title">Осознанность (Mindfulness)</h3>
          <p class="method-item__description">
            Учу техникам осознанности для управления тревожными мыслями,
            развития навыков присутствия в настоящем моменте.
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- Симптомы тревожности -->
  <section class="anxiety-symptoms">
    <div class="wrapper">
      <div class="anxiety-symptoms__header">
        <h2 class="anxiety-symptoms__title">Симптомы тревожности</h2>
        <p class="anxiety-symptoms__subtitle">
          Узнайте, есть ли у вас признаки тревожного расстройства
        </p>
      </div>
      <div class="anxiety-symptoms__grid">

        <div class="symptom-group">
          <h3 class="symptom-group__title">Физические симптомы</h3>
          <ul class="symptom-group__list">
            <li>Учащенное сердцебиение</li>
            <li>Одышка и чувство нехватки воздуха</li>
            <li>Мышечное напряжение и дрожь</li>
            <li>Потливость и озноб</li>
            <li>Головокружение и тошнота</li>
            <li>Нарушения сна</li>
          </ul>
        </div>

        <div class="symptom-group">
          <h3 class="symptom-group__title">Эмоциональные симптомы</h3>
          <ul class="symptom-group__list">
            <li>Постоянное беспокойство</li>
            <li>Чувство надвигающейся опасности</li>
            <li>Раздражительность</li>
            <li>Трудности с концентрацией</li>
            <li>Страх потерять контроль</li>
            <li>Ощущение нереальности</li>
          </ul>
        </div>

        <div class="symptom-group">
          <h3 class="symptom-group__title">Поведенческие симптомы</h3>
          <ul class="symptom-group__list">
            <li>Избегание тревожных ситуаций</li>
            <li>Поиск постоянного подтверждения</li>
            <li>Повторяющиеся проверки</li>
            <li>Изоляция от общества</li>
            <li>Навязчивые действия</li>
            <li>Зависимость от других</li>
          </ul>
        </div>

      </div>
    </div>
  </section>

  <!-- FAQ секция -->
  <section class="anxiety-faq">
    <div class="wrapper">
      <div class="anxiety-faq__header">
        <h2 class="anxiety-faq__title">Часто задаваемые вопросы</h2>
        <p class="anxiety-faq__subtitle">
          Ответы на популярные вопросы о тревожности и лечении
        </p>
      </div>
      <div class="faq-accordion">

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Можно ли полностью избавиться от тревожности?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Да, тревожность поддается лечению! При правильном подходе большинство людей значительно снижают уровень
              тревоги и учатся эффективно управлять ею. Цель терапии - не полное устранение тревоги (это нормальная
              эмоция), а снижение ее интенсивности и обучение навыкам управления.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Сколько времени нужно для лечения тревожности?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Время лечения зависит от тяжести симптомов и готовности к изменениям. При регулярной работе первые
              улучшения заметны через 2-4 недели. Полный курс обычно составляет 3-6 месяцев. При панических атаках и
              фобиях может потребоваться больше времени для закрепления результатов.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Нужно ли принимать лекарства при тревожности?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Медикаментозное лечение может быть рекомендовано при тяжелых формах тревожности, но не является
              обязательным. Психотерапия часто эффективна сама по себе. Решение о приеме лекарств принимается
              индивидуально после консультации с психиатром.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Что делать при панической атаке?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>При панической атаке важно помнить, что она не опасна для жизни и пройдет через 10-20 минут.
              Сосредоточьтесь на дыхании: медленно вдыхайте через нос (4 счета) и выдыхайте через рот (6 счетов).
              Постарайтесь отвлечься, сосчитав предметы вокруг или описав их детально.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Можно ли лечить тревожность онлайн?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Да, онлайн-терапия эффективна для лечения тревожности. Она особенно удобна для регулярных встреч и
              позволяет работать из комфортной обстановки. При панических атаках и тяжелых фобиях может потребоваться
              очная встреча для экспозиционной терапии.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- CTA секция -->
  <section class="anxiety-cta">
    <div class="wrapper">
      <div class="anxiety-cta__content">
        <div class="anxiety-cta__icon">
          <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
            <circle cx="32" cy="32" r="32" fill="#d2afa0" opacity="0.1" />
            <path
              d="M32 8C18.745 8 8 18.745 8 32s10.745 24 24 24 24-10.745 24-24S45.255 8 32 8zm0 44c-11.046 0-20-8.954-20-20S20.954 12 32 12s20 8.954 20 20-8.954 20-20 20z"
              fill="#d2afa0" />
            <path
              d="M32 20c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm0 20c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z"
              fill="#d2afa0" />
          </svg>
        </div>
        <h2 class="anxiety-cta__title">Готовы избавиться от тревожности?</h2>
        <p class="anxiety-cta__text">
          Запишитесь на бесплатную консультацию и получите персональный план лечения.
          Первый шаг к спокойствию - это обращение за помощью.
        </p>
        <button class="anxiety-cta__btn md-main-color-btn" popupOpen="call-back-popup"
          data-form-source="Тревожность: Готовы избавиться от тревожности?">
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

    // FAQ аккордеон
    function toggleFaq(header) {
      const item = header.parentElement;
      const content = header.nextElementSibling;
      const icon = header.querySelector('.faq-item__icon');

      if (item.classList.contains('active')) {
        item.classList.remove('active');
        content.style.maxHeight = '0px';
        icon.textContent = '+';
      } else {
        // Закрываем все открытые элементы
        document.querySelectorAll('.faq-item.active').forEach(activeItem => {
          activeItem.classList.remove('active');
          activeItem.querySelector('.faq-item__content').style.maxHeight = '0px';
          activeItem.querySelector('.faq-item__icon').textContent = '+';
        });

        // Открываем текущий элемент
        item.classList.add('active');
        content.style.maxHeight = content.scrollHeight + 'px';
        icon.textContent = '−';
      }
    }

    // Инициализация FAQ при загрузке страницы
    document.addEventListener('DOMContentLoaded', function () {
      // Устанавливаем начальную высоту для всех FAQ элементов
      document.querySelectorAll('.faq-item__content').forEach(content => {
        content.style.maxHeight = '0px';
      });
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
</body>

</html>