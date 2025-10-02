<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Политика конфиденциальности - Денис Черкас</title>
    <link rel="stylesheet" href="css/new-homepage.css">
    <meta name="description"
        content="Политика конфиденциальности сайта психолога Дениса Черкаса. Узнайте, как мы защищаем ваши персональные данные.">
    <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>" />
</head>

<body>
    <?php include 'includes/new-header.php'; ?>

    <main class="privacy-policy">
        <div class="container">
            <div class="privacy-policy__content">
                <h1>Политика конфиденциальности</h1>
                <p class="privacy-policy__date">Дата вступления в силу: 7 августа 2025 года</p>

                <section class="privacy-policy__section">
                    <h2>1. Общие положения</h2>
                    <p>Настоящая Политика конфиденциальности (далее — «Политика») определяет порядок обработки
                        персональных данных пользователей сайта <a
                            href="https://cherkas-therapy.ru">https://cherkas-therapy.ru</a> (далее — «Сайт»),
                        принадлежащего Черкасу Денису Адамовичу (далее — «Оператор», «мы»).</p>

                    <p>Используя Сайт и предоставляя свои персональные данные, вы соглашаетесь с условиями настоящей
                        Политики.</p>
                </section>

                <section class="privacy-policy__section">
                    <h2>2. Основные понятия</h2>
                    <ul>
                        <li><strong>Персональные данные</strong> — любая информация, относящаяся к прямо или косвенно
                            определенному или определяемому физическому лицу (субъекту персональных данных).</li>
                        <li><strong>Обработка персональных данных</strong> — любое действие (операция) или совокупность
                            действий (операций), совершаемых с использованием средств автоматизации или без
                            использования таких средств с персональными данными.</li>
                        <li><strong>Конфиденциальность персональных данных</strong> — обязательное для соблюдения
                            требование не допускать их распространения без согласия субъекта персональных данных или
                            наличия иного законного основания.</li>
                    </ul>
                </section>

                <section class="privacy-policy__section">
                    <h2>3. Какие персональные данные мы собираем</h2>
                    <p>Мы собираем следующие категории персональных данных:</p>
                    <ul>
                        <li><strong>Контактная информация:</strong> имя, номер телефона, адрес электронной почты,
                            username в мессенджерах</li>
                        <li><strong>Информация о запросах:</strong> тема консультации, предпочтительное время связи</li>
                        <li><strong>Техническая информация:</strong> IP-адрес, данные о браузере, время посещения сайта
                        </li>
                    </ul>
                </section>

                <section class="privacy-policy__section">
                    <h2>4. Цели обработки персональных данных</h2>
                    <p>Ваши персональные данные обрабатываются в следующих целях:</p>
                    <ul>
                        <li>Обработка заявок на консультации и обратная связь</li>
                        <li>Предоставление психологических консультаций</li>
                        <li>Отправка информационных материалов и уведомлений</li>
                        <li>Улучшение качества услуг и работы сайта</li>
                        <li>Соблюдение требований законодательства РФ</li>
                    </ul>
                </section>

                <section class="privacy-policy__section">
                    <h2>5. Правовые основания обработки</h2>
                    <p>Обработка персональных данных осуществляется на следующих правовых основаниях:</p>
                    <ul>
                        <li>Согласие субъекта персональных данных на обработку</li>
                        <li>Необходимость обработки для исполнения договора</li>
                        <li>Соблюдение требований законодательства РФ</li>
                    </ul>
                </section>

                <section class="privacy-policy__section">
                    <h2>6. Сроки хранения персональных данных</h2>
                    <p>Персональные данные хранятся в течение срока, необходимого для достижения целей обработки, или до
                        отзыва согласия на обработку. В случае заключения договора на оказание услуг — в течение срока
                        действия договора и 3 лет после его прекращения.</p>
                </section>

                <section class="privacy-policy__section">
                    <h2>7. Меры по защите персональных данных</h2>
                    <p>Мы принимаем необходимые и достаточные правовые, организационные и технические меры для защиты
                        персональных данных от неправомерного или случайного доступа, уничтожения, изменения,
                        блокирования, копирования, предоставления, распространения, а также от иных неправомерных
                        действий.</p>
                </section>

                <section class="privacy-policy__section">
                    <h2>8. Передача персональных данных третьим лицам</h2>
                    <p>Ваши персональные данные не передаются третьим лицам, за исключением случаев:</p>
                    <ul>
                        <li>Получения вашего явного согласия</li>
                        <li>Требований законодательства РФ</li>
                        <li>Необходимости защиты жизни, здоровья или иных жизненно важных интересов</li>
                    </ul>
                </section>

                <section class="privacy-policy__section">
                    <h2>9. Ваши права как субъекта персональных данных</h2>
                    <p>Вы имеете право:</p>
                    <ul>
                        <li>Получать информацию об обработке ваших персональных данных</li>
                        <li>Требовать уточнения, блокирования или уничтожения персональных данных</li>
                        <li>Отзывать согласие на обработку персональных данных</li>
                        <li>Обжаловать действия или бездействие в области защиты персональных данных</li>
                        <li>Получать информацию о том, какие персональные данные обрабатываются</li>
                    </ul>
                </section>

                <section class="privacy-policy__section">
                    <h2>10. Особенности обработки данных в психологической практике</h2>
                    <p>В соответствии с профессиональной этикой психолога и требованиями законодательства:</p>
                    <ul>
                        <li>Вся информация, полученная в ходе консультаций, является строго конфиденциальной</li>
                        <li>Данные о клиентах не передаются третьим лицам без их согласия</li>
                        <li>Исключения составляют случаи, предусмотренные законом (угроза жизни, здоровью и др.)</li>
                        <li>Документация ведется с соблюдением принципов конфиденциальности</li>
                    </ul>
                </section>

                <section class="privacy-policy__section">
                    <h2>11. Использование файлов cookie</h2>
                    <p>Сайт использует файлы cookie для улучшения пользовательского опыта. Вы можете отключить
                        использование cookie в настройках вашего браузера.</p>
                </section>

                <section class="privacy-policy__section">
                    <h2>12. Контактная информация</h2>
                    <p>По всем вопросам, связанным с обработкой персональных данных, вы можете обратиться:</p>
                    <ul>
                        <li><strong>Телефон:</strong> <a
                                href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['phone']); ?>"><?php echo getContactSettings()['phone']; ?></a>
                        </li>
                        <li><strong>Email:</strong> <a
                                href="mailto:<?php echo getContactSettings()['email']; ?>"><?php echo getContactSettings()['email']; ?></a>
                        </li>
                        <li><strong>Сайт:</strong> <a href="https://cherkas-therapy.ru">https://cherkas-therapy.ru</a>
                        </li>
                    </ul>
                </section>

                <section class="privacy-policy__section">
                    <h2>13. Изменения в Политике конфиденциальности</h2>
                    <p>Мы оставляем за собой право вносить изменения в настоящую Политику. О существенных изменениях мы
                        будем уведомлять вас через Сайт или по указанным контактным данным.</p>
                </section>

                <div class="privacy-policy__footer">
                    <p><strong>Оператор персональных данных:</strong><br>
                        Черкас Денис Адамович<br>
                        Телефон: <a
                            href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['phone']); ?>"><?php echo getContactSettings()['phone']; ?></a><br>
                        Email: <a
                            href="mailto:<?php echo getContactSettings()['email']; ?>"><?php echo getContactSettings()['email']; ?></a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/new-footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/js/jquery.maskedinput.min.js"></script>
    <script src="js/new-homepage.js"></script>
    
        <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m,e,t,r,i,k,a){
            m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
            m[i].l=1*new Date();
            for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
            k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
        })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=103948722', 'ym');

        ym(103948722, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true});
    </script>
    <noscript><div><img src="https://mc.yandex.ru/watch/103948722" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->
</body>

</html>