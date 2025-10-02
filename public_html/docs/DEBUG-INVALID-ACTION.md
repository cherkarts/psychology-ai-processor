# 🔍 Отладка ошибки "Invalid action"

## 🎯 **Проблема:**

После исправления CSRF токена появилась новая ошибка **"Invalid action."** при нажатии на кнопки "Одобрить" и "Отклонить".

## 🔧 **Добавлена отладка:**

Я добавил подробное логирование для диагностики проблемы:

### 1. **В файле `admin/reviews.php`:**

```php
// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    error_log("POST request received. Action: " . ($_POST['action'] ?? 'not set') . ", Data: " . json_encode($_POST));

    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        error_log("CSRF token verification failed");
        $_SESSION['error_message'] = 'Неверный токен безопасности.';
    } else {
        error_log("CSRF token verified, calling handleReviewAction");
        error_log("Function exists: " . (function_exists('handleReviewAction') ? 'YES' : 'NO'));
        try {
            $result = handleReviewAction($_POST);
            error_log("handleReviewAction result: " . json_encode($result));
        } catch (Throwable $th) {
            error_log("Exception in handleReviewAction: " . $th->getMessage());
            $result = ['success' => false, 'message' => 'Server error: ' . $th->getMessage(), 'trace' => $th->getTraceAsString()];
        }
```

### 2. **В файле `admin-functions.php`:**

```php
function handleReviewAction($data)
{
    // Отладочная информация
    error_log("handleReviewAction called with data: " . json_encode($data));

    $action = $data['action'] ?? '';
    $reviewId = $data['review_id'] ?? '';

    error_log("Action: '$action', Review ID: '$reviewId'");

    // ... остальной код ...

    error_log("Processing action: '$action'");
    switch ($action) {
        // ... cases ...
        default:
            error_log("Unknown action: '$action'");
            return ['success' => false, 'message' => 'Invalid action.'];
    }
}
```

## 🧪 **Тестирование:**

### 1. **Попробуйте нажать кнопку "Одобрить":**

- Откройте админку → Отзывы
- Найдите отзыв со статусом "Ожидает"
- Нажмите "Одобрить"
- Подтвердите действие

### 2. **Проверьте логи сервера:**

После нажатия кнопки в логах сервера должны появиться записи:

- `POST request received. Action: approve, Data: {...}`
- `CSRF token verified, calling handleReviewAction`
- `Function exists: YES` или `Function exists: NO`
- `handleReviewAction called with data: {...}`
- `Action: 'approve', Review ID: '...'`
- `Processing action: 'approve'`

### 3. **Возможные результаты:**

#### ✅ **Если функция найдена:**

```
Function exists: YES
handleReviewAction called with data: {"action":"approve","review_id":"123","csrf_token":"..."}
Action: 'approve', Review ID: '123'
Processing action: 'approve'
```

#### ❌ **Если функция не найдена:**

```
Function exists: NO
```

**Решение:** Проблема с подключением файла `admin-functions.php`

#### ❌ **Если данные не передаются:**

```
Action: '', Review ID: ''
```

**Решение:** Проблема с JavaScript функциями

#### ❌ **Если действие не распознается:**

```
Unknown action: 'approve'
```

**Решение:** Проблема с switch statement

## 📋 **Что проверить:**

1. **Логи сервера** - должны показать, где именно происходит сбой
2. **Передаваемые данные** - правильность action и review_id
3. **Существование функции** - загружается ли `handleReviewAction`
4. **Обработка действия** - попадает ли в правильный case

## 🎯 **Ожидаемый результат:**

После добавления отладки мы сможем точно определить:

- ✅ Передаются ли данные корректно
- ✅ Загружается ли функция `handleReviewAction`
- ✅ Обрабатывается ли действие правильно
- ✅ Где именно происходит сбой

## 📝 **Следующие шаги:**

1. **Протестируйте кнопку** "Одобрить"
2. **Проверьте логи сервера** на предмет отладочных сообщений
3. **Сообщите результаты** - какие сообщения появились в логах
4. **На основе логов** исправим конкретную проблему

**Отладка добавлена! Теперь можно точно определить причину ошибки.** 🔍
