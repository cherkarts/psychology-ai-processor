# Инструкция по установке на продакшн сервер

## 🌐 Установка на продакшн

### 1. Загрузить файлы на сервер
Загрузите следующие файлы в корневую папку сайта:
- generate-sitemap.php
- auto-update-sitemap.php
- sitemap-config.php
- cron-production.txt

### 2. Настроить права доступа
```bash
chmod 755 logs/
chmod 644 sitemap.xml robots.txt
chmod 644 *.php
```

### 3. Установить cron задачу
```bash
# Подключиться к серверу по SSH
ssh username@cherkas-therapy.ru

# Перейти в папку сайта
cd /path/to/website

# Установить cron задачу
crontab cron-production.txt

# Проверить установку
crontab -l
```

### 4. Протестировать работу
```bash
# Запустить обновление вручную
php auto-update-sitemap.php

# Проверить логи
cat logs/sitemap-update.log

# Проверить создание файлов
ls -la sitemap.xml robots.txt
```

## 🔧 Альтернативные способы

### Webhook (если cron недоступен)
Добавить в админку сайта:
```php
// После добавления/редактирования контента
file_get_contents("https://cherkas-therapy.ru/auto-update-sitemap.php");
```

### Ручное обновление
```bash
php auto-update-sitemap.php
```

## 📋 Проверка работы

### Yandex.Webmaster
1. Добавить сайт в Yandex.Webmaster
2. Загрузить sitemap.xml: https://cherkas-therapy.ru/sitemap.xml
3. Проверить robots.txt: https://cherkas-therapy.ru/robots.txt

### Мониторинг
- Проверять логи: logs/sitemap-update.log
- Контролировать размер sitemap.xml
- Следить за временем последнего обновления

## 🚨 Устранение проблем

### Cron не работает
1. Проверить права пользователя: `whoami`
2. Проверить путь к PHP: `which php`
3. Проверить синтаксис cron: `crontab -l`

### Файлы не создаются
1. Проверить права доступа: `ls -la`
2. Проверить место на диске: `df -h`
3. Проверить логи ошибок

### База данных недоступна
1. Проверить подключение в includes/functions.php
2. Проверить права пользователя БД
3. Запустить скрипт вручную для диагностики

## 📞 Поддержка
При проблемах проверить:
1. Логи: logs/sitemap-update.log
2. Права доступа к файлам
3. Работу PHP и MySQL
4. Настройки cron
