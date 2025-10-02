# 📤 Список файлов для загрузки на хостинг

**Дата:** 1 октября 2025  
**Изменения:** Оптимизация, Telegram, исправление статей

---

## 🔴 ОБЯЗАТЕЛЬНЫЕ ФАЙЛЫ (критичные изменения)

### Основные файлы сайта

```
public_html/
├── .htaccess                              # ⚡ GZIP, кэширование
├── index.php                              # ⚡ Lazy loading, кэширование
├── article.php                            # 🏷️ Исправление тегов и "Разделы статьи"
└── .gitignore                             # (новый) Git исключения
```

### Система кэширования (новые файлы)

```
public_html/includes/
├── Cache.php                              # 🆕 Класс кэширования
└── Database.php                           # ⚡ Добавлены методы кэширования
```

### Модели (изменения)

```
public_html/includes/Models/
└── Product.php                            # ⚡ Кэширование запросов
```

### Скрытие магазина

```
public_html/includes/
├── new-header.php                         # 🛍️ Магазин скрыт
└── new-footer.php                         # 🛍️ Магазин скрыт
```

### Исправление тегов

```
public_html/admin/
├── articles.php                           # 🏷️ Отображение тегов
└── api/
    └── save-article.php                   # 🏷️ Удаление дублей

public_html/includes/
└── admin-functions.php                    # 🏷️ Обработка тегов
```

---

## 🟡 ДОПОЛНИТЕЛЬНЫЕ ФАЙЛЫ (полезные, но не критичные)

### Утилиты для диагностики и исправления

```
public_html/
├── fix-tags-now.php                       # 🛠️ Исправление дублей тегов
├── test-telegram-auth.php                 # 🧪 Тест Telegram авторизации
├── check-telegram-setup.php               # 🧪 Диагностика Telegram
├── cron-cache-cleanup.php                 # 🗑️ Автоочистка кэша (для cron)
├── QUICKSTART.md                          # 📖 Общая инструкция
├── TELEGRAM-QUICK-START.md                # 📖 Telegram инструкция
└── TODAYS-WORK-SUMMARY.md                 # 📖 Сводка работ
```

### Документация

```
public_html/docs/
├── OPTIMIZATION-REPORT.md                 # 📖 Отчёт оптимизации
├── SHOP-HIDDEN-INSTRUCTIONS.md            # 📖 Инструкция по магазину
├── TELEGRAM-VERIFICATION-GUIDE.md         # 📖 Гайд Telegram (подробный)
├── ARTICLES-FIX-REPORT.md                 # 📖 Отчёт исправления статей
└── TAGS-FIX-GUIDE.md                      # 📖 Гайд по тегам
```

---

## 🟢 ДИРЕКТОРИИ (создать на сервере)

```bash
# Создайте эти директории с правами 755:
mkdir -p cache
mkdir -p logs
chmod 755 cache
chmod 755 logs
```

---

## ⚫ НЕ ЗАГРУЖАТЬ (временные файлы)

```
public_html/cleanup/                       # ❌ Временные файлы
public_html/index.php.bak                  # ❌ Backup файл
public_html/*.log                          # ❌ Локальные логи
```

---

## 📋 ПОШАГОВАЯ ИНСТРУКЦИЯ

### Шаг 1: Подготовка

Соберите файлы для загрузки:

**Обязательные (10 файлов):**

- .htaccess
- index.php
- article.php
- .gitignore
- includes/Cache.php
- includes/Database.php
- includes/Models/Product.php
- includes/new-header.php
- includes/new-footer.php
- includes/admin-functions.php

**Админка (2 файла):**

- admin/articles.php
- admin/api/save-article.php

**Утилиты (3 файла - рекомендуется):**

- fix-tags-now.php
- test-telegram-auth.php
- check-telegram-setup.php

---

### Шаг 2: Загрузка на хостинг

Через FTP/SFTP загрузите файлы в соответствующие директории:

```
/public_html/.htaccess
/public_html/index.php
/public_html/article.php
/public_html/includes/Cache.php
/public_html/includes/Database.php
... и т.д.
```

⚠️ **Важно:** Сохраняйте структуру директорий!

---

### Шаг 3: Создание директорий

Через SSH или файловый менеджер хостинга:

```bash
cd /path/to/public_html
mkdir -p cache logs
chmod 755 cache logs
```

---

### Шаг 4: Проверка

1. **Проверка оптимизации:**

   - Откройте сайт - должен грузиться быстрее
   - PageSpeed Insights - проверьте скорость

2. **Проверка Telegram:**

   - Откройте: `https://cherkas-therapy.ru/check-telegram-setup.php`
   - Все должно быть ✅

3. **Проверка тегов:**

   - Откройте любую статью
   - Прокрутите в конец
   - Теги без дублей ✓
   - Нет абзаца "Разделы статьи" ✓

4. **Проверка магазина:**
   - Ссылка скрыта из меню ✓
   - Доступен по /shop ✓

---

### Шаг 5: Исправление дублей тегов (опционально)

Если увидите дубли в тегах:

```
https://cherkas-therapy.ru/fix-tags-now.php
```

Запустите один раз - исправит все статьи.

---

## 🎯 БЫСТРЫЙ СПИСОК ДЛЯ КОПИРОВАНИЯ

### Минимальный набор (12 файлов):

```
.htaccess
index.php
article.php
.gitignore
includes/Cache.php
includes/Database.php
includes/Models/Product.php
includes/new-header.php
includes/new-footer.php
includes/admin-functions.php
admin/articles.php
admin/api/save-article.php
```

### Рекомендуемый набор (+ утилиты):

```
+ fix-tags-now.php
+ test-telegram-auth.php
+ check-telegram-setup.php
+ cron-cache-cleanup.php
+ TELEGRAM-QUICK-START.md
+ QUICKSTART.md
```

---

## ⚠️ ВАЖНЫЕ ЗАМЕЧАНИЯ

### 1. Не забудьте создать директории:

```bash
mkdir -p cache logs
chmod 755 cache logs
```

### 2. Проверьте Apache модули:

На хостинге должны быть включены:

- mod_deflate (сжатие)
- mod_expires (кэширование)
- mod_headers (заголовки)

### 3. Бэкап перед загрузкой:

Рекомендуется сделать бэкап текущих файлов:

```bash
cp .htaccess .htaccess.backup
cp index.php index.php.backup
```

### 4. После загрузки:

- Очистите кэш сайта (если есть)
- Проверьте что всё работает
- Запустите fix-tags-now.php для исправления дублей

---

## 📊 СТАТИСТИКА

- **Файлов к загрузке:** 12-18
- **Новых файлов:** 5
- **Измененных файлов:** 7-13
- **Директорий создать:** 2

---

## ✅ ЧЕКЛИСТ ПОСЛЕ ЗАГРУЗКИ

- [ ] Все файлы загружены
- [ ] Директории cache/ и logs/ созданы (chmod 755)
- [ ] Сайт открывается
- [ ] Скорость улучшилась
- [ ] Магазин скрыт из меню
- [ ] Статьи без "Разделы статьи"
- [ ] Теги без дублей
- [ ] check-telegram-setup.php показывает ✅
- [ ] Настроен домен в BotFather
- [ ] Telegram авторизация работает

---

Готово! Загрузите эти файлы и всё будет работать! 🚀
