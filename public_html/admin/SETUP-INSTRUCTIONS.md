# Инструкции по настройке чистой админки

## Что было сделано

### 1. Очистка проекта ✅

- Удалены все тестовые файлы (`test-*.php`)
- Удалены все отладочные файлы (`debug-*.php`)
- Удалены все файлы исправления (`fix-*.php`)
- Удалены лишние API файлы
- Удалены резервные копии и временные файлы

### 2. Создана чистая структура базы данных ✅

- Создан скрипт `setup-clean-database.php` для создания всех таблиц
- Добавлены базовые категории для медитаций, товаров и статей
- Добавлены базовые настройки системы

### 3. Созданы чистые страницы админки ✅

- `meditations.php` - управление медитациями
- `products.php` - управление товарами
- `articles.php` - управление статьями
- Все страницы используют единый стиль и функциональность

### 4. Созданы чистые API endpoints ✅

- `api/get-categories.php` - получение категорий медитаций
- `api/save-category.php` - сохранение категорий медитаций
- `api/get-meditation.php` - получение данных медитации
- `api/save-meditation.php` - сохранение медитации
- `api/get-product.php` - получение данных товара
- `api/save-product.php` - сохранение товара
- `api/delete-product.php` - удаление товара
- `api/get-article.php` - получение данных статьи
- `api/save-article.php` - сохранение статьи
- `api/delete-article.php` - удаление статьи

## Инструкции по настройке

### Шаг 1: Создание структуры базы данных

1. Загрузите файл `admin/setup-clean-database.php` на сервер
2. Откройте в браузере: `https://cherkas-therapy.ru/admin/setup-clean-database.php`
3. Дождитесь завершения создания таблиц
4. Удалите файл `setup-clean-database.php` после успешного выполнения

### Шаг 2: Загрузка файлов админки

Загрузите следующие файлы на сервер:

#### Основные страницы:

- `admin/meditations.php`
- `admin/products.php`
- `admin/articles.php`

#### API endpoints:

- `admin/api/get-categories.php`
- `admin/api/save-category.php`
- `admin/get-meditation.php`
- `admin/api/save-meditation.php`
- `admin/api/get-product.php`
- `admin/api/save-product.php`
- `admin/api/delete-product.php`
- `admin/api/get-article.php`
- `admin/api/save-article.php`
- `admin/api/delete-article.php`

### Шаг 3: Проверка работы

1. Откройте админку: `https://cherkas-therapy.ru/admin/`
2. Проверьте страницу медитаций: `https://cherkas-therapy.ru/admin/meditations.php`
3. Проверьте страницу товаров: `https://cherkas-therapy.ru/admin/products.php`
4. Проверьте страницу статей: `https://cherkas-therapy.ru/admin/articles.php`

## Структура базы данных

### Таблицы:

- `meditation_categories` - категории медитаций
- `meditations` - медитации
- `product_categories` - категории товаров
- `products` - товары
- `article_categories` - категории статей
- `articles` - статьи
- `reviews` - отзывы
- `product_reviews` - отзывы о товарах
- `promo_codes` - промокоды
- `orders` - заказы
- `order_items` - элементы заказов
- `admin_users` - пользователи админки
- `settings` - настройки системы

### Базовые категории:

- **Медитации**: Релаксация, Стресс, Сон, Фокус
- **Товары**: Курсы, Консультации, Материалы
- **Статьи**: Психология, Медитация, Здоровье

## Особенности

### Кодировка:

- Все таблицы используют `utf8mb4_unicode_ci`
- Все API возвращают JSON с флагом `JSON_UNESCAPED_UNICODE`
- Нет проблем с отображением русских символов

### Безопасность:

- Все API проверяют авторизацию
- Валидация входных данных
- Защита от SQL-инъекций через prepared statements

### Функциональность:

- Создание, редактирование, удаление записей
- Загрузка изображений
- Сортировка и фильтрация
- Активация/деактивация записей

## Поддержка

Если возникнут проблемы:

1. Проверьте, что все файлы загружены
2. Проверьте права доступа к файлам
3. Проверьте логи ошибок сервера
4. Убедитесь, что база данных создана корректно

Все файлы готовы к использованию и не требуют дополнительной настройки.
