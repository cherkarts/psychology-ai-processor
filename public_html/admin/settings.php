<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

requirePermission('admin');
$pageTitle = 'Настройки системы';

// Подключение к базе данных для настроек
$db = getAdminDB();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Неверный токен безопасности';
    } else {
        $result = saveSettings($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
}

$settings = getSystemSettings();

require_once __DIR__ . '/includes/header.php';
?>

<div class="settings-container">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-cogs"></i> Настройки системы</h1>
            <p>Управление конфигурацией сайта и административной панели</p>
        </div>
    </div>

    <form method="POST" class="settings-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <div class="settings-sections">
            <!-- General Settings -->
            <div class="settings-section">
                <h3><i class="fas fa-globe"></i> Общие настройки</h3>

                <div class="form-group">
                    <label for="site_title">Название сайта</label>
                    <input type="text" id="site_title" name="site_title"
                        value="<?php echo sanitizeOutput($settings['site_title'] ?? 'Черкас Терапия'); ?>">
                </div>

                <div class="form-group">
                    <label for="site_description">Описание сайта</label>
                    <textarea id="site_description" name="site_description"
                        rows="3"><?php echo sanitizeOutput($settings['site_description'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="admin_email">Email администратора</label>
                    <input type="email" id="admin_email" name="admin_email"
                        value="<?php echo sanitizeOutput($settings['admin_email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="contact_phone">Контактный телефон</label>
                    <input type="tel" id="contact_phone" name="contact_phone"
                        value="<?php echo sanitizeOutput($settings['contact_phone'] ?? '+7 (993) 620-29-51'); ?>">
                </div>

                <div class="form-group">
                    <label for="contact_email">Email для связи</label>
                    <input type="email" id="contact_email" name="contact_email"
                        value="<?php echo sanitizeOutput($settings['contact_email'] ?? 'info@cherkas-therapy.ru'); ?>">
                </div>

                <div class="form-group">
                    <label for="whatsapp_number">Номер WhatsApp</label>
                    <input type="tel" id="whatsapp_number" name="whatsapp_number"
                        value="<?php echo sanitizeOutput($settings['whatsapp_number'] ?? '+7 (993) 620-29-51'); ?>">
                </div>

                <div class="form-group">
                    <label for="telegram_username">Имя пользователя Telegram</label>
                    <input type="text" id="telegram_username" name="telegram_username"
                        value="<?php echo sanitizeOutput($settings['telegram_username'] ?? 'Cherkas_therapy'); ?>">
                </div>

                <div class="form-group">
                    <label for="telegram_url">Ссылка на Telegram</label>
                    <input type="url" id="telegram_url" name="telegram_url"
                        value="<?php echo sanitizeOutput($settings['telegram_url'] ?? 'https://t.me/Cherkas_therapy'); ?>">
                </div>

                <div class="form-group">
                    <label for="telegram_channel">Имя канала Telegram</label>
                    <input type="text" id="telegram_channel" name="telegram_channel"
                        value="<?php echo sanitizeOutput($settings['telegram_channel'] ?? 'taterapia'); ?>">
                </div>

                <div class="form-group">
                    <label for="telegram_channel_url">Ссылка на канал Telegram</label>
                    <input type="url" id="telegram_channel_url" name="telegram_channel_url"
                        value="<?php echo sanitizeOutput($settings['telegram_channel_url'] ?? 'https://t.me/taterapia'); ?>">
                </div>
            </div>

            <!-- Security Settings -->
            <div class="settings-section">
                <h3><i class="fas fa-shield-alt"></i> Настройки безопасности</h3>

                <div class="form-group">
                    <label for="session_timeout">Время сессии (минуты)</label>
                    <input type="number" id="session_timeout" name="session_timeout" min="5" max="1440"
                        value="<?php echo intval($settings['session_timeout'] ?? 60); ?>">
                    <small>От 5 до 1440 минут</small>
                </div>

                <div class="form-group">
                    <label for="max_login_attempts">Максимальное количество попыток входа</label>
                    <input type="number" id="max_login_attempts" name="max_login_attempts" min="3" max="10"
                        value="<?php echo intval($settings['max_login_attempts'] ?? 3); ?>">
                </div>

                <div class="form-group">
                    <label for="lockout_time">Время блокировки (минуты)</label>
                    <input type="number" id="lockout_time" name="lockout_time" min="5" max="60"
                        value="<?php echo intval($settings['lockout_time'] ?? 15); ?>">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="require_strong_passwords" value="1" <?php echo !empty($settings['require_strong_passwords']) ? 'checked' : ''; ?>>
                        Требовать сложные пароли
                    </label>
                </div>
            </div>

            <!-- File Upload Settings -->
            <div class="settings-section">
                <h3><i class="fas fa-upload"></i> Настройки загрузки файлов</h3>

                <div class="form-group">
                    <label for="max_file_size">Максимальный размер файла (MB)</label>
                    <input type="number" id="max_file_size" name="max_file_size" min="1" max="100"
                        value="<?php echo intval($settings['max_file_size'] ?? 10); ?>">
                </div>

                <div class="form-group">
                    <label for="allowed_image_types">Разрешенные типы изображений</label>
                    <input type="text" id="allowed_image_types" name="allowed_image_types"
                        value="<?php echo sanitizeOutput($settings['allowed_image_types'] ?? 'jpg,jpeg,png,gif,webp'); ?>">
                    <small>Разделяйте типы запятыми</small>
                </div>

                <div class="form-group">
                    <label for="allowed_audio_types">Разрешенные типы аудио</label>
                    <input type="text" id="allowed_audio_types" name="allowed_audio_types"
                        value="<?php echo sanitizeOutput($settings['allowed_audio_types'] ?? 'mp3,wav,ogg,m4a'); ?>">
                    <small>Разделяйте типы запятыми</small>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="settings-section">
                <h3><i class="fas fa-envelope"></i> Настройки email</h3>

                <div class="form-group">
                    <label for="smtp_host">SMTP хост</label>
                    <input type="text" id="smtp_host" name="smtp_host"
                        value="<?php echo sanitizeOutput($settings['smtp_host'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="smtp_port">SMTP порт</label>
                    <input type="number" id="smtp_port" name="smtp_port"
                        value="<?php echo intval($settings['smtp_port'] ?? 587); ?>">
                </div>

                <div class="form-group">
                    <label for="smtp_username">SMTP пользователь</label>
                    <input type="text" id="smtp_username" name="smtp_username"
                        value="<?php echo sanitizeOutput($settings['smtp_username'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="smtp_password">SMTP пароль</label>
                    <input type="password" id="smtp_password" name="smtp_password"
                        value="<?php echo sanitizeOutput($settings['smtp_password'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="smtp_secure" value="1" <?php echo !empty($settings['smtp_secure']) ? 'checked' : ''; ?>>
                        Использовать шифрование SSL/TLS
                    </label>
                </div>
            </div>

            <!-- Content Settings -->
            <div class="settings-section">
                <h3><i class="fas fa-file-alt"></i> Настройки контента</h3>

                <div class="form-group">
                    <label for="articles_per_page">Статей на страницу</label>
                    <input type="number" id="articles_per_page" name="articles_per_page" min="5" max="50"
                        value="<?php echo intval($settings['articles_per_page'] ?? 10); ?>">
                </div>

                <div class="form-group">
                    <label for="reviews_per_page">Отзывов на страницу</label>
                    <input type="number" id="reviews_per_page" name="reviews_per_page" min="5" max="50"
                        value="<?php echo intval($settings['reviews_per_page'] ?? 12); ?>">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="auto_approve_reviews" value="1" <?php echo !empty($settings['auto_approve_reviews']) ? 'checked' : ''; ?>>
                        Автоматически одобрять отзывы
                    </label>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="enable_comments" value="1" <?php echo !empty($settings['enable_comments']) ? 'checked' : ''; ?>>
                        Включить комментарии к статьям
                    </label>
                </div>
            </div>

            <!-- Maintenance Settings -->
            <div class="settings-section">
                <h3><i class="fas fa-tools"></i> Обслуживание</h3>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="maintenance_mode" name="maintenance_mode" value="1" <?php echo !empty($settings['maintenance_mode']) ? 'checked' : ''; ?>
                            onchange="toggleMaintenanceMode()">
                        Режим обслуживания
                    </label>
                    <small>Отключает доступ к сайту для всех, кроме администраторов</small>
                </div>

                <div class="form-group">
                    <label for="maintenance_message">Сообщение в режиме обслуживания</label>
                    <textarea id="maintenance_message" name="maintenance_message"
                        rows="3"><?php echo sanitizeOutput($settings['maintenance_message'] ?? 'Сайт временно недоступен по техническим причинам.'); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="backup_frequency">Частота резервного копирования</label>
                    <select id="backup_frequency" name="backup_frequency">
                        <option value="daily" <?php echo ($settings['backup_frequency'] ?? '') === 'daily' ? 'selected' : ''; ?>>Ежедневно</option>
                        <option value="weekly" <?php echo ($settings['backup_frequency'] ?? '') === 'weekly' ? 'selected' : ''; ?>>Еженедельно</option>
                        <option value="monthly" <?php echo ($settings['backup_frequency'] ?? '') === 'monthly' ? 'selected' : ''; ?>>Ежемесячно</option>
                        <option value="manual" <?php echo ($settings['backup_frequency'] ?? '') === 'manual' ? 'selected' : ''; ?>>Вручную</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- System Info -->
        <div class="system-info-section">
            <h3><i class="fas fa-info-circle"></i> Информация о системе</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Версия PHP:</label>
                    <span><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="info-item">
                    <label>Версия MySQL:</label>
                    <span><?php echo getMySQLVersion(); ?></span>
                </div>
                <div class="info-item">
                    <label>Свободное место:</label>
                    <span><?php echo formatBytes(disk_free_space('.')); ?></span>
                </div>
                <div class="info-item">
                    <label>Максимальный размер загрузки:</label>
                    <span><?php echo ini_get('upload_max_filesize'); ?></span>
                </div>
                <div class="info-item">
                    <label>Лимит памяти:</label>
                    <span><?php echo ini_get('memory_limit'); ?></span>
                </div>
                <div class="info-item">
                    <label>Максимальное время выполнения:</label>
                    <span><?php echo ini_get('max_execution_time'); ?>s</span>
                </div>
            </div>
        </div>

        <!-- Yandex Metrika Settings -->
        <div class="settings-section">
            <h3><i class="fas fa-chart-line"></i> Яндекс Метрика</h3>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="yandex_metrika_enabled" value="1" <?php echo !empty($settings['yandex_metrika_enabled']) ? 'checked' : ''; ?>>
                    Включить Яндекс Метрику
                </label>
                <small>Активирует отслеживание посещений сайта</small>
            </div>

            <div class="form-group">
                <label for="yandex_metrika_id">ID счетчика Яндекс Метрики</label>
                <input type="text" id="yandex_metrika_id" name="yandex_metrika_id"
                    value="<?php echo sanitizeOutput($settings['yandex_metrika_id'] ?? ''); ?>" placeholder="12345678">
                <small>Номер счетчика из Яндекс Метрики (например: 12345678)</small>
            </div>

            <div class="form-group">
                <label for="yandex_metrika_webvisor">Включить Вебвизор</label>
                <select id="yandex_metrika_webvisor" name="yandex_metrika_webvisor">
                    <option value="1" <?php echo ($settings['yandex_metrika_webvisor'] ?? '1') === '1' ? 'selected' : ''; ?>>Включен</option>
                    <option value="0" <?php echo ($settings['yandex_metrika_webvisor'] ?? '1') === '0' ? 'selected' : ''; ?>>Выключен</option>
                </select>
                <small>Запись действий пользователей на сайте</small>
            </div>

            <div class="form-group">
                <label for="yandex_metrika_clickmap">Включить Карту кликов</label>
                <select id="yandex_metrika_clickmap" name="yandex_metrika_clickmap">
                    <option value="1" <?php echo ($settings['yandex_metrika_clickmap'] ?? '1') === '1' ? 'selected' : ''; ?>>Включена</option>
                    <option value="0" <?php echo ($settings['yandex_metrika_clickmap'] ?? '1') === '0' ? 'selected' : ''; ?>>Выключена</option>
                </select>
                <small>Отслеживание кликов по элементам страницы</small>
            </div>

            <div class="form-group">
                <label for="yandex_metrika_track_hash">Отслеживать хеши в URL</label>
                <select id="yandex_metrika_track_hash" name="yandex_metrika_track_hash">
                    <option value="1" <?php echo ($settings['yandex_metrika_track_hash'] ?? '1') === '1' ? 'selected' : ''; ?>>Включено</option>
                    <option value="0" <?php echo ($settings['yandex_metrika_track_hash'] ?? '1') === '0' ? 'selected' : ''; ?>>Выключено</option>
                </select>
                <small>Отслеживание изменений хеша в URL (для SPA)</small>
            </div>

            <div class="form-group">
                <label for="yandex_metrika_track_links">Отслеживать внешние ссылки</label>
                <select id="yandex_metrika_track_links" name="yandex_metrika_track_links">
                    <option value="1" <?php echo ($settings['yandex_metrika_track_links'] ?? '1') === '1' ? 'selected' : ''; ?>>Включено</option>
                    <option value="0" <?php echo ($settings['yandex_metrika_track_links'] ?? '1') === '0' ? 'selected' : ''; ?>>Выключено</option>
                </select>
                <small>Отслеживание переходов по внешним ссылкам</small>
            </div>

            <div class="form-group">
                <label for="yandex_metrika_accurate_track_bounce">Точный подсчет отказов</label>
                <select id="yandex_metrika_accurate_track_bounce" name="yandex_metrika_accurate_track_bounce">
                    <option value="1" <?php echo ($settings['yandex_metrika_accurate_track_bounce'] ?? '1') === '1' ? 'selected' : ''; ?>>Включен</option>
                    <option value="0" <?php echo ($settings['yandex_metrika_accurate_track_bounce'] ?? '1') === '0' ? 'selected' : ''; ?>>Выключен</option>
                </select>
                <small>Более точный подсчет отказов от посещений</small>
            </div>

            <div class="form-group">
                <label for="yandex_metrika_defer">Отложенная загрузка</label>
                <select id="yandex_metrika_defer" name="yandex_metrika_defer">
                    <option value="1" <?php echo ($settings['yandex_metrika_defer'] ?? '1') === '1' ? 'selected' : ''; ?>>
                        Включена</option>
                    <option value="0" <?php echo ($settings['yandex_metrika_defer'] ?? '1') === '0' ? 'selected' : ''; ?>>
                        Выключена</option>
                </select>
                <small>Загрузка метрики после загрузки страницы (улучшает скорость)</small>
            </div>

            <div class="form-group">
                <label for="yandex_metrika_ecommerce">Включить E-commerce</label>
                <select id="yandex_metrika_ecommerce" name="yandex_metrika_ecommerce">
                    <option value="1" <?php echo ($settings['yandex_metrika_ecommerce'] ?? '1') === '1' ? 'selected' : ''; ?>>Включен</option>
                    <option value="0" <?php echo ($settings['yandex_metrika_ecommerce'] ?? '1') === '0' ? 'selected' : ''; ?>>Выключен</option>
                </select>
                <small>Отслеживание покупок и конверсий</small>
            </div>

            <div class="form-group">
                <label for="yandex_metrika_custom_events">Пользовательские события</label>
                <textarea id="yandex_metrika_custom_events" name="yandex_metrika_custom_events" rows="4"
                    placeholder="// Пример пользовательских событий
ym(12345678, 'reachGoal', 'button_click');
ym(12345678, 'reachGoal', 'form_submit');"><?php echo sanitizeOutput($settings['yandex_metrika_custom_events'] ?? ''); ?></textarea>
                <small>Дополнительный JavaScript код для отслеживания пользовательских событий</small>
            </div>

            <div class="form-group">
                <label for="yandex_metrika_debug">Режим отладки</label>
                <select id="yandex_metrika_debug" name="yandex_metrika_debug">
                    <option value="0" <?php echo ($settings['yandex_metrika_debug'] ?? '0') === '0' ? 'selected' : ''; ?>>
                        Выключен</option>
                    <option value="1" <?php echo ($settings['yandex_metrika_debug'] ?? '0') === '1' ? 'selected' : ''; ?>>
                        Включен</option>
                </select>
                <small>Показывать отладочную информацию в консоли браузера</small>
            </div>
        </div>

        <!-- User Management Section -->
        <div class="settings-section">
            <h3><i class="fas fa-users"></i> Управление пользователями и ролями</h3>

            <div class="user-management-tabs">
                <button type="button" class="tab-btn active" onclick="switchTab('users')">Пользователи</button>
                <button type="button" class="tab-btn" onclick="switchTab('roles')">Роли</button>
                <button type="button" class="tab-btn" onclick="switchTab('profile')">Мой профиль</button>
            </div>

            <!-- Users Tab -->
            <div id="users-tab" class="tab-content active">
                <div class="users-list">
                    <div class="users-header">
                        <h4>Список администраторов</h4>
                        <div class="header-actions">
                            <a href="users.php" class="btn btn-secondary btn-sm">
                                <i class="fas fa-external-link-alt"></i> Полное управление пользователями
                            </a>
                            <button type="button" class="btn btn-primary btn-sm" onclick="openUserModal()">
                                <i class="fas fa-plus"></i> Добавить пользователя
                            </button>
                        </div>
                    </div>
                    <div id="users-table-container">
                        <!-- Users will be loaded here via AJAX -->
                    </div>
                </div>
            </div>

            <!-- Roles Tab -->
            <div id="roles-tab" class="tab-content">
                <div class="roles-list">
                    <div class="roles-header">
                        <h4>Список ролей</h4>
                        <button type="button" class="btn btn-primary btn-sm" onclick="openRoleModal()">
                            <i class="fas fa-plus"></i> Добавить роль
                        </button>
                    </div>
                    <div id="roles-table-container">
                        <!-- Roles will be loaded here via AJAX -->
                    </div>
                </div>
            </div>

            <!-- Profile Tab -->
            <div id="profile-tab" class="tab-content">
                <div class="profile-form">
                    <h4>Изменение логина и пароля</h4>
                    <form id="profile-form" onsubmit="updateProfile(event)">
                        <div class="form-group">
                            <label for="current_username">Текущий логин</label>
                            <input type="text" id="current_username"
                                value="<?php echo $_SESSION['admin_username'] ?? ''; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="new_username">Новый логин</label>
                            <input type="text" id="new_username" name="new_username" placeholder="Введите новый логин">
                        </div>
                        <div class="form-group">
                            <label for="current_password">Текущий пароль</label>
                            <input type="password" id="current_password" name="current_password"
                                placeholder="Введите текущий пароль">
                        </div>
                        <div class="form-group">
                            <label for="new_password">Новый пароль</label>
                            <input type="password" id="new_password" name="new_password"
                                placeholder="Введите новый пароль">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Подтвердите новый пароль</label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                placeholder="Подтвердите новый пароль">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Обновить профиль
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Сохранить настройки
            </button>

            <button type="button" class="btn btn-secondary" onclick="resetToDefaults()">
                <i class="fas fa-undo"></i> Сбросить к умолчаниям
            </button>

            <button type="button" class="btn btn-info" onclick="testEmail()">
                <i class="fas fa-envelope"></i> Тестировать email
            </button>

            <button type="button" class="btn btn-warning" onclick="clearCache()">
                <i class="fas fa-broom"></i> Очистить кэш
            </button>
        </div>
    </form>
</div>

<script>
    function resetToDefaults() {
        showConfirmModal(
            'Сброс настроек',
            'Вы уверены, что хотите сбросить все настройки к значениям по умолчанию?',
            function () {
                // Implementation would reset form fields to defaults
                showToast('Настройки сброшены к умолчаниям', 'info');
            }
        );
    }

    function testEmail() {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';

        fetch('api/test-email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.adminCSRFToken
            },
            body: JSON.stringify({
                smtp_host: document.getElementById('smtp_host').value,
                smtp_port: document.getElementById('smtp_port').value,
                smtp_username: document.getElementById('smtp_username').value,
                smtp_password: document.getElementById('smtp_password').value,
                smtp_secure: document.getElementById('smtp_secure').checked
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Тестовое сообщение отправлено успешно!', 'success');
                } else {
                    showToast('Ошибка отправки: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Ошибка сети', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    }

    function clearCache() {
        showConfirmModal(
            'Очистка кэша',
            'Вы уверены, что хотите очистить весь кэш системы?',
            function () {
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Очистка...';

                fetch('api/clear-cache.php', {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': window.adminCSRFToken }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Кэш очищен успешно', 'success');
                        } else {
                            showToast('Ошибка очистки кэша: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Ошибка сети', 'error');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
            }
        );
    }

    // User Management Functions
    function switchTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabName + '-tab').classList.add('active');
        event.target.classList.add('active');

        // Load content based on tab
        if (tabName === 'users') {
            loadUsers();
        } else if (tabName === 'roles') {
            loadRoles();
        }
    }

    function loadUsers() {
        fetch('api/get-admin-users.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayUsers(data.users);
                } else {
                    showToast('Ошибка загрузки пользователей: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Ошибка сети', 'error');
            });
    }

    function loadRoles() {
        fetch('api/get-admin-roles.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayRoles(data.roles);
                } else {
                    showToast('Ошибка загрузки ролей: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Ошибка сети', 'error');
            });
    }

    function displayUsers(users) {
        const container = document.getElementById('users-table-container');
        let html = '<table class="data-table"><thead><tr><th>Логин</th><th>Email</th><th>Роль</th><th>Статус</th><th>Действия</th></tr></thead><tbody>';
        users.forEach(user => {
            html += `
                <tr>
                    <td>${user.username}</td>
                    <td>${user.email}</td>
                    <td>${user.role_name || 'Не назначена'}</td>
                    <td><span class="status-badge ${user.is_active ? 'active' : 'inactive'}">${user.is_active ? 'Активен' : 'Неактивен'}</span></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="editUser(${user.id})">Редактировать</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">Удалить</button>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function displayRoles(roles) {
        const container = document.getElementById('roles-table-container');
        let html = '<table class="data-table"><thead><tr><th>Название</th><th>Описание</th><th>Разрешения</th><th>Действия</th></tr></thead><tbody>';

        roles.forEach(role => {
            const permissions = JSON.parse(role.permissions || '[]').join(', ');
            html += `
                <tr>
                    <td>${role.name}</td>
                    <td>${role.description || ''}</td>
                    <td>${permissions}</td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="editRole(${role.id})">Редактировать</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteRole(${role.id})">Удалить</button>
                    </td>
                </tr>
            `;
        });

        html += '</tbody></table>';
        container.innerHTML = html;
    }

    function openUserModal() {
        // Implementation for user modal
        showToast('Функция добавления пользователя будет реализована', 'info');
    }

    function openRoleModal() {
        // Implementation for role modal
        showToast('Функция добавления роли будет реализована', 'info');
    }

    function editUser(userId) {
        // Implementation for editing user
        showToast('Функция редактирования пользователя будет реализована', 'info');
    }

    function deleteUser(userId) {
        showConfirmModal(
            'Удаление пользователя',
            'Вы уверены, что хотите удалить этого пользователя?',
            function () {
                fetch('api/delete-admin-user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.adminCSRFToken
                    },
                    body: JSON.stringify({ user_id: userId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Пользователь удален успешно', 'success');
                            loadUsers();
                        } else {
                            showToast('Ошибка удаления: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Ошибка сети', 'error');
                    });
            }
        );
    }

    function editRole(roleId) {
        // Implementation for editing role
        showToast('Функция редактирования роли будет реализована', 'info');
    }

    function deleteRole(roleId) {
        showConfirmModal(
            'Удаление роли',
            'Вы уверены, что хотите удалить эту роль?',
            function () {
                fetch('api/delete-admin-role.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.adminCSRFToken
                    },
                    body: JSON.stringify({ role_id: roleId })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Роль удалена успешно', 'success');
                            loadRoles();
                        } else {
                            showToast('Ошибка удаления: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        showToast('Ошибка сети', 'error');
                    });
            }
        );
    }

    function updateProfile(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const data = Object.fromEntries(formData.entries());

        if (data.new_password !== data.confirm_password) {
            showToast('Пароли не совпадают', 'error');
            return;
        }

        fetch('api/update-admin-profile.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.adminCSRFToken
            },
            body: JSON.stringify(data)
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Профиль обновлен успешно', 'success');
                    event.target.reset();
                } else {
                    showToast('Ошибка обновления: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showToast('Ошибка сети', 'error');
            });
    }

    // Load users on page load
    document.addEventListener('DOMContentLoaded', function () {
        loadUsers();
    });

    // Maintenance Mode Functions
    function toggleMaintenanceMode() {
        const maintenanceCheckbox = document.getElementById('maintenance_mode');
        const maintenanceMessage = document.getElementById('maintenance_message');

        if (!maintenanceCheckbox) {
            showToast('Элемент управления режимом обслуживания не найден', 'error');
            return;
        }

        // Отладочная информация
        console.log('Toggle maintenance mode:', {
            checked: maintenanceCheckbox.checked,
            csrfToken: window.adminCSRFToken,
            message: maintenanceMessage ? maintenanceMessage.value : 'default'
        });

        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Обновление...';

        fetch('api/toggle-maintenance-simple.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.adminCSRFToken
            },
            body: JSON.stringify({
                maintenance_mode: maintenanceCheckbox.checked,
                maintenance_message: maintenanceMessage ? maintenanceMessage.value : 'Сайт временно недоступен по техническим причинам.'
            })
        })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showToast(data.message, 'success');

                    // Если режим включен, показываем предупреждение
                    if (data.maintenance_mode) {
                        showConfirmModal(
                            'Режим обслуживания включен',
                            'Сайт теперь недоступен для обычных посетителей. Администраторы могут продолжать работу. Хотите протестировать страницу обслуживания?',
                            function () {
                                window.open('/maintenance.php', '_blank');
                            }
                        );
                    }
                } else {
                    showToast('Ошибка: ' + data.message, 'error');
                    // Возвращаем чекбокс в предыдущее состояние
                    maintenanceCheckbox.checked = !maintenanceCheckbox.checked;
                }
            })
            .catch(error => {
                showToast('Ошибка сети', 'error');
                // Возвращаем чекбокс в предыдущее состояние
                maintenanceCheckbox.checked = !maintenanceCheckbox.checked;
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
    }
</script>

<style>
    .settings-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .settings-sections {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .settings-section,
    .system-info-section {
        background: white;
        padding: 1.5rem;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-sm);
    }

    .settings-section h3,
    .system-info-section h3 {
        margin: 0 0 1.5rem 0;
        color: var(--gray-800);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--gray-700);
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--border-radius-md);
        font-size: var(--font-size-sm);
    }

    .form-group small {
        display: block;
        margin-top: 0.25rem;
        color: var(--gray-600);
        font-size: 0.8rem;
    }

    .form-group input[type="checkbox"] {
        width: auto;
        margin-right: 0.5rem;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem;
        background: var(--gray-50);
        border-radius: var(--border-radius-sm);
    }

    .info-item label {
        font-weight: 500;
        color: var(--gray-700);
    }

    .form-actions {
        background: white;
        padding: 1.5rem;
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-sm);
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    /* User Management Styles */
    .user-management-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--gray-300);
    }

    .tab-btn {
        padding: 0.75rem 1.5rem;
        border: none;
        background: none;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        color: var(--gray-600);
        font-weight: 500;
        transition: all 0.2s;
    }

    .tab-btn.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .users-header,
    .roles-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .header-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .users-header h4,
    .roles-header h4 {
        margin: 0;
        color: var(--gray-700);
    }

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .profile-form {
        max-width: 500px;
    }

    .profile-form h4 {
        margin-bottom: 1.5rem;
        color: var(--gray-700);
    }

    @media (max-width: 768px) {
        .settings-sections {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .user-management-tabs {
            flex-direction: column;
        }

        .users-header,
        .roles-header {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
    }
</style>

<?php
require_once __DIR__ . '/includes/footer.php';

// Функции getSystemSettings() и saveSettings() теперь определены в includes/functions.php

function getMySQLVersion()
{
    try {
        $db = getAdminDB();
        if ($db) {
            $stmt = $db->query('SELECT VERSION() as version');
            $result = $stmt->fetch();
            return $result['version'] ?? 'Unknown';
        }
    } catch (Exception $e) {
        // Ignore errors
    }
    return 'Not connected';
}

function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}
?>