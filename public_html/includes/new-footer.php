<?php
require_once __DIR__ . '/functions.php';
$config = getConfig();
?>
<footer class="footer">
    <div class="container">
        <div class="footer__content">
            <!-- Логотип и описание -->
            <div class="footer__section footer__section--main">
                <div class="footer__logo">
                    <h3 class="footer__logo-text">Денис Черкас</h3>
                    <p class="footer__description">Профессиональная психологическая помощь в Москве</p>
                </div>

                <!-- Социальные сети -->
                <div class="footer__social">
                    <h3 class="footer__section-title">Мы в соцсетях</h3>
                    <div class="social-links">
                        <a href="<?php echo getContactSettings()['telegram_url']; ?>" target="_blank"
                            aria-label="Telegram" class="social-link social-link--telegram">
                            <img src="/image/telegram.png" alt="Telegram" width="24" height="24">
                        </a>
                        <a href="https://wa.me/+79936202951" target="_blank" aria-label="WhatsApp"
                            class="social-link social-link--whatsapp">
                            <img src="/image/whats-app.png" alt="whats-app" width="24" height="24">
                        </a>
                        <a href="https://www.instagram.com/cherkas_therapy/" target="_blank" aria-label="Instagram"
                            class="social-link social-link--instagram">
                            <img src="/image/instagram.png" alt="instagram" width="24" height="24">
                        </a>
                        <a href="https://vk.com/cherkas_therapy" target="_blank" aria-label="VK"
                            class="social-link social-link--vk">
                            <img src="/image/vk.png" alt="vk" width="24" height="24">
                        </a>
                    </div>
                    <span class="meta-notice">(Meta, признана экстремистской организацией на территории РФ)</span>
                </div>
            </div>

            <!-- Услуги -->
            <div class="footer__section">
                <h3 class="footer__section-title">Услуги</h3>
                <ul class="footer__list">
                    <li><a href="/services" class="footer__link">Консультации психолога</a></li>
                    <li><a href="/dependencies.php" class="footer__link">Работа с зависимостями</a></li>
                    <li><a href="/codependency.php" class="footer__link">Созависимость</a></li>
                    <li><a href="/anxiety.php" class="footer__link">Тревожность и страхи</a></li>
                    <li><a href="/relationships.php" class="footer__link">Сложности в отношениях</a></li>
                </ul>
            </div>

            <!-- Полезные материалы -->
            <div class="footer__section">
                <h3 class="footer__section-title">Полезные материалы</h3>
                <ul class="footer__list">
                    <li><a href="/articles" class="footer__link">Статьи и блог</a></li>
                    <li><a href="/reviews" class="footer__link">Отзывы клиентов</a></li>
                    <li><a href="/prices" class="footer__link">Цены на услуги</a></li>
                    <!-- Временно скрыто на время настройки -->
                    <!-- <li><a href="/shop" class="footer__link">Материалы для самопомощи</a></li> -->
                </ul>
            </div>

            <!-- Страницы сайта -->
            <div class="footer__section">
                <h3 class="footer__section-title">Страницы сайта</h3>
                <ul class="footer__list">
                    <li><a href="/about" class="footer__link">Обо мне</a></li>
                    <li><a href="/contact" class="footer__link">Контакты</a></li>
                    <li><a href="/sitemap.xml" class="footer__link">Карта сайта</a></li>
                </ul>
            </div>

            <!-- Контактная информация -->
            <div class="footer__section footer__section--contacts">
                <h3 class="footer__section-title">Контакты</h3>
                <div class="contact-item">
                    <div class="contact-icon">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M18.3333 14.1V16.6C18.3343 16.8321 18.2867 17.0618 18.1937 17.2745C18.1008 17.4871 17.9644 17.678 17.7935 17.8349C17.6225 17.9918 17.4201 18.1113 17.2007 18.1854C16.9813 18.2595 16.7499 18.2866 16.5167 18.265C13.9523 17.988 11.4892 17.1118 9.32498 15.7083C7.31163 14.4289 5.60451 12.7218 4.32498 10.7083C2.91663 8.53426 2.04019 6.05908 1.76665 3.48333C1.74504 3.25082 1.77204 3.01977 1.84579 2.80081C1.91953 2.58185 2.03846 2.37978 2.19462 2.2089C2.35078 2.03802 2.54072 1.90147 2.75266 1.80824C2.9646 1.715 3.19367 1.66699 3.42498 1.66666H5.92498C6.32971 1.66268 6.72148 1.80589 7.02845 2.06945C7.33541 2.333 7.53505 2.69948 7.59165 3.09999C7.69736 3.89957 7.89294 4.68557 8.17498 5.44166C8.28796 5.73992 8.31137 6.06407 8.24165 6.37499C8.17193 6.68591 8.01205 6.96903 7.78332 7.19166L6.74165 8.23333C7.92791 10.3446 9.65535 12.072 11.7667 13.2583L12.8083 12.2167C13.0309 11.9879 13.3141 11.8281 13.625 11.7583C13.9359 11.6886 14.2601 11.712 14.5583 11.825C15.3144 12.107 16.1004 12.3026 16.9 12.4083C17.3047 12.4656 17.6746 12.6692 17.9389 12.9815C18.2032 13.2938 18.3438 13.6916 18.3333 14.1Z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="contact-details">
                        <a href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['phone']); ?>"
                            class="contact-link"><?php echo getContactSettings()['phone']; ?></a>
                        <span class="contact-label">Звоните c 9:00 до 22:00</span>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M17.5 3.33334H2.5C1.57952 3.33334 0.833336 4.07952 0.833336 5V15C0.833336 15.9205 1.57952 16.6667 2.5 16.6667H17.5C18.4205 16.6667 19.1667 15.9205 19.1667 15V5C19.1667 4.07952 18.4205 3.33334 17.5 3.33334Z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M0.833336 5L10 10.8333L19.1667 5" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="contact-details">
                        <a href="mailto:<?php echo getContactSettings()['email']; ?>"
                            class="contact-link"><?php echo getContactSettings()['email']; ?></a>
                        <span class="contact-label">Пишите мне</span>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M10 18.3333C14.6024 18.3333 18.3333 14.6024 18.3333 10C18.3333 5.39763 14.6024 1.66667 10 1.66667C5.39763 1.66667 1.66667 5.39763 1.66667 10C1.66667 14.6024 5.39763 18.3333 10 18.3333Z"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                stroke-linejoin="round" />
                            <path d="M10 5V10L13.3333 11.6667" stroke="currentColor" stroke-width="1.5"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="contact-details">
                        <span class="contact-text">c 9:00 до 22:00</span>
                        <span class="contact-label">выходной суббота воскресенье</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer__bottom">
            <div class="footer__bottom-content">
                <div class="footer__copyright">
                    <p>&copy; <?= date('Y') ?> Психолог Денис Черкас. Все права защищены</p>
                </div>
                <div class="footer__links">
                    <a href="https://www.b17.ru/cherkas_denis/?prt=1139485" target="_blank" rel="noopener"
                        class="footer__b17">
                        <img src="https://www.b17.ru/img/b17_88x31_b_retina.png" style="width: 88px; height: 31px"
                            alt="B17" />
                    </a>
                </div>
                <div class="footer__legal">
                    <a href="/privacy-policy.php" class="footer__link">Политика конфиденциальности</a>
                    <a href="#" class="footer__link" data-popup="agreement-popup">Терапевтическое соглашение</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Кнопка "Наверх" -->
<button class="scroll-to-top" id="scrollToTop" aria-label="Прокрутить наверх">
    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M10 3.33334L16.6667 10L15.245 11.4217L10.8333 7.01V16.6667H9.16667V7.01L4.755 11.4217L3.33334 10L10 3.33334Z"
            fill="currentColor" />
    </svg>
</button>

<!-- Попапы для нового дизайна -->
<!-- Политика конфиденциальности -->
<div class="popup" id="privacy-policy-popup">
    <div class="popup__overlay"></div>
    <div class="popup__content popup__content--large">
        <button class="popup__close" aria-label="Закрыть">×</button>
        <div class="privacy-policy-popup">
            <h3>Политика конфиденциальности</h3>
            <div class="privacy-policy-popup__content">
                <p><strong>Дата вступления в силу:</strong> 7 августа 2025 года</p>

                <h4>1. Общие положения</h4>
                <p>Настоящая Политика конфиденциальности определяет порядок обработки персональных данных пользователей
                    сайта <a href="https://cherkas-therapy.ru">https://cherkas-therapy.ru</a>, принадлежащего Черкасу
                    Денису Адамовичу.</p>

                <h4>2. Какие данные мы собираем</h4>
                <ul>
                    <li>Контактная информация: имя, телефон, email, username в мессенджерах</li>
                    <li>Информация о запросах: тема консультации, предпочтительное время</li>
                    <li>Техническая информация: IP-адрес, данные браузера</li>
                </ul>

                <h4>3. Цели обработки</h4>
                <ul>
                    <li>Обработка заявок на консультации</li>
                    <li>Предоставление психологических услуг</li>
                    <li>Отправка информационных материалов</li>
                    <li>Улучшение качества услуг</li>
                </ul>

                <h4>4. Конфиденциальность</h4>
                <p>Вся информация, полученная в ходе консультаций, является строго конфиденциальной и не подлежит
                    разглашению третьим лицам, за исключением случаев, предусмотренных законодательством РФ.</p>

                <h4>5. Ваши права</h4>
                <ul>
                    <li>Получать информацию об обработке данных</li>
                    <li>Требовать уточнения или удаления данных</li>
                    <li>Отзывать согласие на обработку</li>
                    <li>Обжаловать действия в области защиты данных</li>
                </ul>

                <h4>6. Контактная информация</h4>
                <p>По вопросам обработки персональных данных:</p>
                <ul>
                    <li><strong>Телефон:</strong> <a
                            href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['phone']); ?>"><?php echo getContactSettings()['phone']; ?></a>
                    </li>
                    <li><strong>Email:</strong> <a
                            href="mailto:<?php echo getContactSettings()['email']; ?>"><?php echo getContactSettings()['email']; ?></a>
                    </li>
                    <li><strong>Сайт:</strong> <a href="https://cherkas-therapy.ru">https://cherkas-therapy.ru</a></li>
                </ul>

                <p><em>Полная версия политики доступна на странице <a href="/privacy-policy.php"
                            target="_blank">Политика конфиденциальности</a></em></p>
            </div>
        </div>
    </div>
</div>

<!-- Соглашение -->
<div class="popup" id="agreement-popup">
    <div class="popup__overlay"></div>
    <div class="popup__content popup__content--large">
        <button class="popup__close" aria-label="Закрыть">×</button>
        <div class="agreement-popup">
            <h3>Информационно-терапевтическое соглашение</h3>
            <div class="agreement-popup__content">
                <p>
                    Настоящим подтверждается, что я, Клиент, ознакомлен(-а) и согласен(-на) с условиями оказания
                    консультационных услуг,
                    предоставляемых психологом через сайт
                    <strong><?= $config['site']['url'] ?? 'cherkas-therapy.ru' ?></strong>, на следующих
                    основаниях:
                </p>

                <h4>1. Согласие на консультирование</h4>
                <p>
                    1.1. Я даю добровольное согласие на участие в индивидуальном психологическом консультировании.<br>
                    1.2. Я понимаю, что цель консультирования – осознание психологических причин возникновения проблем и
                    расстройств,
                    совместный анализ моих эмоций, поведения, потребностей, способов их удовлетворения, а также
                    моделирование методов
                    преодоления трудностей в текущей жизненной ситуации.
                </p>

                <h4>2. Конфиденциальность</h4>
                <p>
                    2.1. Информация, полученная в ходе сеансов, является строго конфиденциальной и не подлежит
                    разглашению третьим лицам,
                    за исключением случаев, предусмотренных законодательством Российской Федерации (например, угроза
                    жизни или здоровью Клиента
                    или других лиц).
                </p>

                <h4>3. Обработка персональных данных</h4>
                <p>
                    3.1. Я даю добровольное согласие на сбор, хранение и обработку моих персональных данных, включая
                    фамилию, имя, отчество,
                    адрес проживания, номер телефона и адрес электронной почты.<br>
                    3.2. Указанные данные не подлежат передаче третьим лицам или использованию в иных целях без моего
                    явного согласия, за
                    исключением случаев, предусмотренных законом.
                </p>

                <h4>4. Формат и длительность встреч</h4>
                <p>
                    4.1. Длительность одной консультационной встречи составляет 50 минут, из которых 45 минут – основное
                    время консультирования,
                    5 минут – подведение итогов.<br>
                    4.2. Рекомендуемая регулярность встреч в рамках курса – один раз в неделю. В острых случаях возможно
                    увеличение до двух
                    встреч в неделю по согласованию с консультантом.
                </p>

                <h4>5. Экстренные консультации</h4>
                <p>
                    5.1. Возможность проведения экстренных телефонных консультаций обсуждается на первой встрече.<br>
                    5.2. Стоимость экстренной консультации равна стоимости стандартной встречи и оплачивается в полном
                    объеме.
                </p>

                <h4>6. Порядок оплаты и отмены встреч</h4>
                <p>
                    6.1. Пропуск запланированной встречи или ее отмена менее чем за 12 часов до начала подлежит полной
                    оплате.<br>
                    6.2. В случае неявки без уведомления плата за встречу также взимается в полном объеме.<br>
                    6.3. При повторных пропусках без предупреждения (более двух раз) консультант вправе прекратить
                    оказание услуг.<br>
                    6.4. Стоимость услуг определяется индивидуально на первой встрече и остается фиксированной в течение
                    6 месяцев с момента
                    заключения соглашения.
                </p>

                <h4>7. Особенности процесса консультирования</h4>
                <p>
                    7.1. Я осведомлен(-а) о том, что в процессе консультирования возможны кратковременные колебания
                    эмоционального состояния,
                    что является частью терапевтического процесса.<br>
                    7.2. Я обязуюсь открыто обсуждать с консультантом любые противоречия или вопросы, возникающие в ходе
                    работы, и воздерживаюсь
                    от действий, которые могут нанести ущерб репутации консультанта (распространение ложной информации,
                    клевета и т.д.).
                </p>

                <h4>8. Порядок расторжения соглашения</h4>
                <p>
                    8.1. Я вправе в любой момент отказаться от дальнейшего консультирования, уведомив консультанта не
                    менее чем за 24 часа до
                    очередной встречи.<br>
                    8.2. Консультант вправе расторгнуть соглашение в случае систематического нарушения Клиентом условий
                    настоящего соглашения
                    (например, неявка, отсутствие оплаты) или если продолжение работы противоречит профессиональной
                    этике.
                </p>

                <h4>9. Контактная информация</h4>
                <p>
                    Для связи с консультантом по вопросам консультирования:<br>
                    • Email: <a
                        href="mailto:<?php echo getContactSettings()['email']; ?>"><?php echo getContactSettings()['email']; ?></a><br>
                    • Телефон: <a
                        href="tel:<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['phone']); ?>"><?php echo getContactSettings()['phone']; ?></a><br>
                    <a href="<?php echo getContactSettings()['telegram_url']; ?>" target="_blank">• Telegram</a><br>
                    <a href="https://wa.me/<?php echo str_replace([' ', '(', ')', '-'], '', getContactSettings()['whatsapp']); ?>"
                        target="_blank">• WhatsApp</a>
                </p>

                <p><em>Дата вступления в силу: 06.03.2025</em></p>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно согласия на cookie -->
<div class="popup" id="cookie-consent-popup" aria-modal="true" role="dialog"
    aria-label="Согласие на использование файлов cookie">
    <div class="popup__overlay"></div>
    <div class="popup__content">
        <button class="popup__close" aria-label="Закрыть">×</button>
        <div class="cookie-consent">
            <h3>Мы используем файлы cookie</h3>
            <p>
                Для улучшения работы сайта мы используем файлы cookie. Продолжая пользоваться сайтом, вы соглашаетесь с
                их использованием.
                Подробности в <a href="/privacy-policy.php" target="_blank" rel="noopener">политике
                    конфиденциальности</a>.
            </p>
            <div class="cookie-consent__actions">
                <button type="button" class="btn btn--primary" id="cookie-accept-btn">Хорошо</button>
            </div>
        </div>
    </div>

</div>

<!-- Записаться на консультацию -->
<div class="popup" id="consultation-popup">
    <div class="popup__overlay"></div>
    <div class="popup__content">
        <button class="popup__close" aria-label="Закрыть">×</button>
        <h3>Записаться на консультацию</h3>
        <form class="popup__form md-standart-form" method="post">
            <div class="form-group">
                <label>Ваше имя</label>
                <input type="text" name="name" class="form-input" placeholder="Введите ваше имя" required>
            </div>
            <div class="form-group">
                <label>Ваш телефон</label>
                <input type="tel" name="phone" class="form-input" placeholder="+7 (___) ___-__-__" phoneMask_JS=""
                    required>
            </div>
            <div class="form-group">
                <label>Выберите время звонка</label>
                <div class="custom-select">
                    <select name="time" class="form-select js-native-select" required>
                        <option value="" disabled selected>Выберите удобное время</option>
                        <option value="now">Перезвоните сейчас</option>
                        <option value="morning">Утром (9-12)</option>
                        <option value="afternoon">Днем (12-18)</option>
                        <option value="evening">Вечером (18-22)</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="form_type" value="Попап консультация">
            <input type="hidden" name="form_source" value="Статья: Попап консультация">
            <input type="hidden" name="website" value="">
            <label class="form-checkbox" agreementcontrol_js>
                <input type="checkbox" name="agreement" agreementcontrolcheckbox_js checked required>
                <span>Согласен с <a href="#" class="privacy-policy-link" data-popup="privacy-policy-popup">политикой
                        конфиденциальности</a></span>
            </label>
            <button type="submit" class="btn btn--primary" agreementcontrolbtn_js>Записаться</button>
        </form>
    </div>
</div>
</div>

<!-- Заказать звонок -->
<div class="popup" id="call-back-popup">
    <div class="popup__overlay"></div>
    <div class="popup__content">
        <button class="popup__close" aria-label="Закрыть">×</button>
        <h3>Заказать звонок</h3>
        <form class="popup__form md-standart-form" method="post">
            <div class="form-group">
                <label>Ваше имя</label>
                <input type="text" name="name" class="form-input" placeholder="Введите ваше имя" required>
            </div>
            <div class="form-group">
                <label>Ваш телефон</label>
                <input type="tel" name="phone" class="form-input" placeholder="+7 (___) ___-__-__" phoneMask_JS=""
                    required>
            </div>
            <div class="form-group">
                <label>Удобное время для звонка</label>
                <div class="custom-select">
                    <select name="time" class="form-select js-native-select" required>
                        <option value="" disabled selected>Выберите удобное время</option>
                        <option value="now">Сейчас</option>
                        <option value="morning">Утром (9-12)</option>
                        <option value="afternoon">Днем (12-18)</option>
                        <option value="evening">Вечером (18-22)</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="form_type" value="Заказать звонок">
            <input type="hidden" name="form_source" value="Шапка: Заказать звонок">
            <input type="hidden" name="website" value="">
            <label class="form-checkbox" agreementcontrol_js>
                <input type="checkbox" name="agreement" agreementcontrolcheckbox_js checked required>
                <span>Согласен с <a href="#" class="privacy-policy-link" data-popup="privacy-policy-popup">политикой
                        конфиденциальности</a></span>
            </label>
            <button type="submit" class="btn btn--primary" agreementcontrolbtn_js>Заказать звонок</button>
        </form>
    </div>