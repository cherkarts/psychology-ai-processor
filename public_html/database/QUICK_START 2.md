# Быстрый старт: Миграция на MySQL

## Шаг 1: Подготовка

1. Убедитесь, что XAMPP запущен (Apache + MySQL)
2. Откройте терминал в корневой папке проекта

## Шаг 2: Автоматическая настройка

Запустите автоматический скрипт настройки:

```bash
php database/setup.php
```

Скрипт автоматически:

- ✅ Создаст базу данных `cherkas_therapy`
- ✅ Импортирует схему таблиц
- ✅ Мигрирует данные из база данных MySQLов
- ✅ Протестирует подключение
- ✅ Создаст резервную копию

## Шаг 3: Проверка

После успешной настройки проверьте:

```bash
# Тест подключения
php database/test_connection.php

# Проверка в phpMyAdmin
# Откройте http://localhost/phpmyadmin
# База данных: cherkas_therapy
```

## Шаг 4: Обновление сайта

Теперь обновите файлы сайта для работы с базой данных:

1. **API файлы**: Используйте новые API (например, `api/products-db.php`)
2. **Страницы**: Подключите классы моделей
3. **Тестирование**: Проверьте работу всех функций

## Полезные команды

```bash
# Резервная копия
mysqldump -u root -p cherkas_therapy > backup.sql

# Восстановление
mysql -u root -p cherkas_therapy < backup.sql

# Просмотр логов
tail -f /Applications/XAMPP/xamppfiles/logs/mysql_error.log
```

## Структура файлов

```
database/
├── schema.sql          # Схема базы данных
├── setup.php           # Автоматическая настройка
├── migrate.php         # Миграция данных
├── test_connection.php # Тест подключения
├── README.md           # Подробная документация
└── QUICK_START.md      # Эта инструкция

includes/
├── Database.php        # Класс для работы с БД
└── Models/
    ├── Product.php     # Модель продуктов
    └── Review.php      # Модель отзывов

api/
└── products-db.php     # Пример API для БД
```

## Настройка для продакшена

В файле `config.php` измените:

```php
'environment' => 'production',
'production' => [
    'database' => [
        'host' => 'your_host',
        'username' => 'your_username',
        'password' => 'your_password',
    ]
]
```

## Поддержка

При проблемах:

1. Проверьте логи XAMPP
2. Убедитесь в правильности настроек
3. Проверьте права доступа к файлам
4. Обратитесь к подробной документации: `database/README.md`





