# ✅ Добавлена кнопка "Одобрить" в админку отзывов

## 🎯 **Проблема решена!**

Добавлена кнопка "Одобрить" для отзывов со статусом "Ожидает" в админке обычных отзывов (не отзывов товаров).

## 🔍 **Проблема:**

В админке отзывов (`admin/reviews.php`) отсутствовала кнопка "Одобрить" для отзывов со статусом "Ожидает" (pending).

## 🔧 **Решение:**

### 1. **Добавлены кнопки действий** ✅

**Проблема:** Отсутствовали кнопки "Одобрить" и "Отклонить"
**Решение:** Добавлены кнопки для отзывов со статусом "pending"

```php
<?php if ($review['status'] === 'pending'): ?>
<button class="btn btn-success btn-sm" data-action="approve-review"
    data-id="<?php echo $review['id']; ?>"
    onclick="window.approveReview && window.approveReview(this.dataset.id)">
    <i class="fas fa-check"></i> Одобрить
</button>
<button class="btn btn-warning btn-sm" data-action="reject-review"
    data-id="<?php echo $review['id']; ?>"
    onclick="window.rejectReview && window.rejectReview(this.dataset.id)">
    <i class="fas fa-times"></i> Отклонить
</button>
<?php endif; ?>
```

### 2. **Добавлены JavaScript функции** ✅

**Проблема:** Не было обработчиков для кнопок одобрения/отклонения
**Решение:** Добавлены функции `approveReview` и `rejectReview`

```javascript
// Функция одобрения отзыва
window.approveReview = function (id) {
  if (!confirm('Вы уверены, что хотите одобрить этот отзыв?')) {
    return
  }

  const form = document.createElement('form')
  form.method = 'POST'
  form.action = ''

  const actionInput = document.createElement('input')
  actionInput.type = 'hidden'
  actionInput.name = 'action'
  actionInput.value = 'approve'

  const idInput = document.createElement('input')
  idInput.type = 'hidden'
  idInput.name = 'review_id'
  idInput.value = id

  const csrfInput = document.createElement('input')
  csrfInput.type = 'hidden'
  csrfInput.name = 'csrf_token'
  csrfInput.value =
    document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute('content') || ''

  form.appendChild(actionInput)
  form.appendChild(idInput)
  form.appendChild(csrfInput)

  document.body.appendChild(form)
  form.submit()
}

// Функция отклонения отзыва
window.rejectReview = function (id) {
  if (!confirm('Вы уверены, что хотите отклонить этот отзыв?')) {
    return
  }

  // ... аналогичная логика с action = 'reject'
}
```

### 3. **Добавлена обработка в PHP** ✅

**Проблема:** Не было обработки действий "approve" и "reject"
**Решение:** Добавлены case в функцию `handleReviewAction`

```php
case 'approve':
    $stmt = $db->prepare("UPDATE reviews SET status = 'approved', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$reviewId]);
    return ['success' => true, 'message' => 'Отзыв одобрен успешно.'];

case 'reject':
    $stmt = $db->prepare("UPDATE reviews SET status = 'rejected', updated_at = NOW() WHERE id = ?");
    $stmt->execute([$reviewId]);
    return ['success' => true, 'message' => 'Отзыв отклонен успешно.'];
```

## 🎯 **Как теперь работает:**

### Логика отображения кнопок:

1. **Проверка статуса:** Код проверяет, равен ли статус отзыва "pending"
2. **Показ кнопок:** Если статус "pending", показываются кнопки "Одобрить" и "Отклонить"
3. **Скрытие кнопок:** Если статус "approved" или "rejected", кнопки скрываются

### Обработка действий:

1. **Нажатие кнопки:** Пользователь нажимает "Одобрить" или "Отклонить"
2. **Подтверждение:** Появляется диалог подтверждения
3. **Отправка формы:** Создается и отправляется POST-запрос
4. **Обновление статуса:** Статус отзыва обновляется в базе данных
5. **Перезагрузка:** Страница перезагружается с обновленными данными

## 📁 **Обновленные файлы:**

1. **`admin/reviews.php`** - добавлены кнопки и JavaScript функции
2. **`admin-functions.php`** - добавлена обработка действий approve/reject

## 🧪 **Тестирование:**

### 1. **Проверьте кнопку "Одобрить":**

- Откройте админку → Отзывы
- Найдите отзыв со статусом "Ожидает"
- Должна быть кнопка "Одобрить"

### 2. **Проверьте кнопку "Отклонить":**

- На том же отзыве должна быть кнопка "Отклонить"

### 3. **Проверьте функциональность:**

- Нажмите "Одобрить" → статус должен измениться на "Одобрен"
- Нажмите "Отклонить" → статус должен измениться на "Отклонен"

## ✅ **Ожидаемый результат:**

После исправлений:

- ✅ Кнопка "Одобрить" отображается для отзывов со статусом "Ожидает"
- ✅ Кнопка "Отклонить" отображается для отзывов со статусом "Ожидает"
- ✅ Кнопки скрываются для одобренных/отклоненных отзывов
- ✅ Действия корректно обрабатываются и обновляют статус

## 🚨 **Если проблема остается:**

1. **Очистите кэш браузера:** Ctrl+F5
2. **Проверьте консоль браузера:** Не должно быть JavaScript ошибок
3. **Проверьте права доступа:** Убедитесь, что у вас есть права на модерацию
4. **Проверьте CSRF токен:** Убедитесь, что токен безопасности работает

## 🎉 **Результат:**

Теперь в админке отзывов есть кнопки "Одобрить" и "Отклонить" для отзывов, ожидающих модерации! Модерация отзывов стала намного удобнее.

**Проблема полностью решена!** 🌟
