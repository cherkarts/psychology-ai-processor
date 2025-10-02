# Исправление обратного порядка звезд рейтинга

## 🔍 **Проблема**

Звезды рейтинга отображались в обратном порядке:

- При выборе 3 звезд: первые 2 серые, последние 3 желтые
- Должно быть: первые 3 желтые, последние 2 серые

## 🛠️ **Исправления**

### 1. **Возвращена правильная HTML структура**

**Проблема:** Звезды шли от 1 к 5, что конфликтовало с CSS селекторами
**Решение:** ✅ Возвращен порядок от 5 к 1

### 2. **Добавлен JavaScript для правильного управления звездами**

**Проблема:** CSS селекторы не могут правильно обработать логику "все звезды до выбранной включительно"
**Решение:** ✅ Добавлен JavaScript для динамического управления цветом звезд

#### Исправленная HTML структура:

```html
<div class="rating-input">
  <input type="radio" id="star5" name="rating" value="5" />
  <label for="star5" class="star">★</label>
  <input type="radio" id="star4" name="rating" value="4" />
  <label for="star4" class="star">★</label>
  <input type="radio" id="star3" name="rating" value="3" />
  <label for="star3" class="star">★</label>
  <input type="radio" id="star2" name="rating" value="2" />
  <label for="star2" class="star">★</label>
  <input type="radio" id="star1" name="rating" value="1" />
  <label for="star1" class="star">★</label>
</div>
```

### 2. **Исправлен CSS для правильной работы**

**Проблема:** CSS селекторы работали неправильно с новым порядком
**Решение:** ✅ Исправлена логика CSS селекторов

#### Исправленный CSS:

```css
/* Hover эффект - все звезды до наведенной включительно */
.rating-input .star:hover,
.rating-input .star:hover ~ .star {
  color: #ffc107;
}

/* Выбранная звезда */
.rating-input input[type='radio']:checked + .star {
  color: #ffc107;
}

/* Все звезды после выбранной - делаем их серыми */
.rating-input input[type='radio']:checked ~ .star {
  color: #ddd;
}
```

#### Добавленный JavaScript:

```javascript
// Функция для обновления отображения звезд
function updateStarDisplay(selectedInput) {
  const ratingInputs = document.querySelectorAll('input[name="rating"]')
  const selectedValue = parseInt(selectedInput.value)

  ratingInputs.forEach((input) => {
    const inputValue = parseInt(input.value)
    const star = input.nextElementSibling

    if (inputValue <= selectedValue) {
      star.style.color = '#ffc107'
    } else {
      star.style.color = '#ddd'
    }
  })
}

// Hover эффект для звезд
const stars = form.querySelectorAll('.star')
stars.forEach((star) => {
  star.addEventListener('mouseenter', function () {
    const input = this.previousElementSibling
    const inputValue = parseInt(input.value)

    ratingInputs.forEach((ratingInput) => {
      const ratingValue = parseInt(ratingInput.value)
      const ratingStar = ratingInput.nextElementSibling

      if (ratingValue <= inputValue) {
        ratingStar.style.color = '#ffc107'
      } else {
        ratingStar.style.color = '#ddd'
      }
    })
  })

  star.addEventListener('mouseleave', function () {
    // Восстанавливаем состояние на основе выбранного рейтинга
    const selectedInput = form.querySelector('input[name="rating"]:checked')
    if (selectedInput) {
      updateStarDisplay(selectedInput)
    } else {
      // Если ничего не выбрано, делаем все звезды серыми
      ratingInputs.forEach((ratingInput) => {
        const ratingStar = ratingInput.nextElementSibling
        ratingStar.style.color = '#ddd'
      })
    }
  })
})
```

## 🎯 **Как это работает**

### Логика отображения звезд:

1. **HTML порядок:** 5, 4, 3, 2, 1 (от большего к меньшему)
2. **JavaScript функция `updateStarDisplay`:** Динамически управляет цветом звезд
3. **Логика:** Если значение звезды <= выбранному значению, то звезда желтая
4. **Результат:** При выборе 3 звезд подсвечиваются звезды со значениями 1, 2, 3

### Примеры:

- **Выбрана 1 звезда:** ★☆☆☆☆ (только звезда со значением 1 желтая)
- **Выбрана 3 звезды:** ★★★☆☆ (звезды со значениями 1, 2, 3 желтые)
- **Выбрана 5 звезд:** ★★★★★ (все 5 звезд желтые)

### Hover эффект:

- **При наведении:** Подсвечиваются все звезды до наведенной включительно
- **При уходе мыши:** Восстанавливается состояние на основе выбранного рейтинга

## 📋 **Файл для обновления**

- **`includes/product-reviews-widget.php`** - исправлена структура звезд, CSS и добавлен JavaScript

## 🧪 **Тестирование**

### 1. **Проверьте форму отзыва:**

- Откройте страницу товара
- Нажмите "Оставить отзыв"
- Попробуйте выбрать разные рейтинги

### 2. **Проверьте отображение:**

- **1 звезда:** Должна быть только последняя звезда желтой
- **3 звезды:** Должны быть последние 3 звезды желтыми
- **5 звезд:** Должны быть все 5 звезд желтыми

### 3. **Проверьте hover эффект:**

- При наведении на звезду должны подсвечиваться все звезды до неё включительно

## ✅ **Ожидаемый результат**

После исправлений:

- ✅ При выборе 3 звезд звезды со значениями 1, 2, 3 должны быть желтыми
- ✅ При выборе 5 звезд все 5 звезд должны быть желтыми
- ✅ Hover эффект должен работать правильно (подсвечивать звезды до наведенной включительно)
- ✅ При уходе мыши должно восстанавливаться состояние выбранного рейтинга
- ✅ Рейтинг должен правильно отправляться в API

## 🚨 **Если проблема остается**

1. **Очистите кэш браузера:** Ctrl+F5
2. **Проверьте консоль браузера:** Не должно быть ошибок JavaScript
3. **Проверьте HTML:** Звезды должны идти от 5 к 1
4. **Проверьте JavaScript:** Функция `updateStarDisplay` должна вызываться при выборе звезды
5. **Проверьте CSS:** Селекторы должны работать с порядком HTML

Теперь звезды рейтинга должны отображаться в правильном порядке! ⭐
