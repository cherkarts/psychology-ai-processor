/**
 * JavaScript для работы с корзиной
 */

// Глобальная переменная для хранения экземпляра
let cartManagerInstance = null

// Глобальная функция для обновления счетчика корзины
async function updateCartCounter() {
  try {
    const response = await fetch('/api/cart.php?action=count')
    if (response.ok) {
      const data = await response.json()
      if (data.success && data.data && typeof data.data.count === 'number') {
        const cartCounter = document.querySelector('.cart-counter')
        if (cartCounter) {
          cartCounter.textContent = data.data.count
          cartCounter.style.display = data.data.count > 0 ? 'block' : 'none'

          // Добавляем анимацию при изменении
          if (data.data.count > 0) {
            cartCounter.style.animation = 'pulse 0.5s ease-in-out'
            setTimeout(() => {
              cartCounter.style.animation = ''
            }, 500)
          }
        }
      }
    }
  } catch (error) {
    console.error('Ошибка обновления счетчика корзины:', error)
  }
}

class CartManager {
  constructor() {
    // Проверяем, не создан ли уже экземпляр
    if (cartManagerInstance) {
      return cartManagerInstance
    }

    this.init()
    cartManagerInstance = this
  }

  init() {
    this.bindEvents()
    this.updateCartCount()
  }

  bindEvents() {
    // Защита от множественных кликов
    let isProcessing = false

    // Обработчики для кнопок количества в корзине - используем capture для более высокого приоритета
    document.addEventListener(
      'click',
      (e) => {
        if (e.target.closest('.quantity-btn')) {
          console.log('Cart: quantity button clicked', e.target)

          // Предотвращаем множественные клики
          if (isProcessing) {
            console.log('Cart: ignoring multiple clicks')
            return false
          }

          // Предотвращаем всплытие события
          e.preventDefault()
          e.stopPropagation()
          e.stopImmediatePropagation()

          const btn = e.target.closest('.quantity-btn')
          const productId = btn.dataset.productId
          const isPlus = btn.classList.contains('quantity-btn--plus')
          const isMinus = btn.classList.contains('quantity-btn--minus')

          console.log('Cart: button info', { productId, isPlus, isMinus })

          if (isPlus || isMinus) {
            isProcessing = true

            const input = document.querySelector(
              `.quantity-input[data-product-id="${productId}"]`
            )
            if (input) {
              let currentQuantity = parseInt(input.value) || 1
              if (isPlus) {
                currentQuantity = Math.min(currentQuantity + 1, 99)
              } else if (isMinus) {
                currentQuantity = Math.max(currentQuantity - 1, 1)
              }

              input.value = currentQuantity
              this.updateItemTotal(productId, currentQuantity)
              this.showConfirmButton(productId)

              console.log('Cart: quantity updated', {
                productId,
                newQuantity: currentQuantity,
              })
            }

            // Снимаем блокировку через небольшую задержку
            setTimeout(() => {
              isProcessing = false
            }, 300)
          }

          // Возвращаем false для дополнительной блокировки
          return false
        }
      },
      true
    ) // Используем capture phase

    // Обработчики для изменения количества через input
    document.addEventListener('input', (e) => {
      if (e.target.classList.contains('quantity-input')) {
        const input = e.target
        const productId = input.dataset.productId
        const quantity = parseInt(input.value) || 1
        this.updateItemTotal(productId, quantity)
        this.showConfirmButton(productId)
      }
    })

    // Обработчики для кнопки подтверждения количества
    document.addEventListener('click', (e) => {
      if (e.target.closest('.quantity-confirm-btn')) {
        const btn = e.target.closest('.quantity-confirm-btn')
        const productId = btn.dataset.productId
        const input = document.querySelector(
          `.quantity-input[data-product-id="${productId}"]`
        )

        if (input) {
          const quantity = parseInt(input.value) || 1
          this.updateQuantity(productId, quantity)
          this.hideConfirmButton(productId)
        }
      }
    })

    // Обработчики для кнопок удаления из корзины
    document.addEventListener('click', (e) => {
      // Product page: add to cart
      const addBtn = e.target.closest('.add-to-cart-btn')
      if (addBtn && document.body.classList.contains('product-page')) {
        e.preventDefault()
        const productId = addBtn.dataset.productId
        if (productId) {
          this.addToCart(productId)
        }
        return
      }

      // Product page: buy now (add then redirect)
      const buyBtn = e.target.closest('.buy-now-btn')
      if (buyBtn && document.body.classList.contains('product-page')) {
        e.preventDefault()
        const productId = buyBtn.dataset.productId
        if (productId) {
          this.addToCart(productId).then(() => {
            setTimeout(() => {
              window.location.href = '/checkout.php'
            }, 400)
          })
        }
        return
      }

      if (e.target.closest('.remove-item-btn')) {
        const btn = e.target.closest('.remove-item-btn')
        const productId = btn.dataset.productId

        if (confirm('Удалить товар из корзины?')) {
          this.removeFromCart(productId)
        }
      }
    })

    // Обработчики для кнопки очистки корзины
    document.addEventListener('click', (e) => {
      if (e.target.closest('.clear-cart-btn')) {
        if (confirm('Очистить корзину?')) {
          this.clearCart()
        }
      }
    })

    // Обработчики для промокода
    document.addEventListener('click', (e) => {
      if (e.target.closest('#applyPromoBtn')) {
        this.applyPromoCode()
      }
    })

    // Enter key для промокода
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && e.target.id === 'promoCodeInput') {
        e.preventDefault()
        this.applyPromoCode()
      }
    })
  }

  // Добавить в корзину
  async addToCart(productId, quantity = 1) {
    try {
      // Проверяем, что productId является строкой
      const productIdStr = String(productId)

      const response = await fetch('/api/cart.php?action=add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          product_id: productIdStr,
          quantity: parseInt(quantity) || 1,
        }),
      })

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()
      console.log('API Response:', data)

      if (data.success) {
        this.showNotification('Товар добавлен в корзину', 'success')
        this.updateCartCount()

        // Обновляем кнопку
        const btn = document.querySelector(
          `[data-product-id="${productIdStr}"]`
        )
        if (btn) {
          btn.textContent = 'В корзине'
          btn.disabled = true
          btn.style.background = '#6a7e9f'
        }
      } else {
        this.showNotification(data.error || 'Ошибка добавления товара', 'error')
      }
    } catch (error) {
      console.error('Ошибка добавления в корзину:', error)
      this.showNotification('Ошибка добавления товара', 'error')
    }
  }

  // Обновить количество
  async updateQuantity(productId, quantity) {
    try {
      const response = await fetch('/api/cart.php?action=update', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          product_id: productId,
          quantity: quantity,
        }),
      })

      const data = await response.json()

      if (data.success) {
        this.updateCartCount()
        this.updateCartTotal()
      } else {
        this.showNotification(
          data.error || 'Ошибка обновления количества',
          'error'
        )
      }
    } catch (error) {
      console.error('Ошибка обновления количества:', error)
      this.showNotification('Ошибка обновления количества', 'error')
    }
  }

  // Удалить из корзины
  async removeFromCart(productId) {
    try {
      const response = await fetch('/api/cart.php?action=remove', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          product_id: productId,
        }),
      })

      const data = await response.json()

      if (data.success) {
        this.showNotification('Товар удален из корзины', 'success')
        this.updateCartCount()
        this.removeCartItem(productId)
      } else {
        this.showNotification(data.error || 'Ошибка удаления товара', 'error')
      }
    } catch (error) {
      console.error('Ошибка удаления из корзины:', error)
      this.showNotification('Ошибка удаления товара', 'error')
    }
  }

  // Очистить корзину
  async clearCart() {
    try {
      const response = await fetch('/api/cart.php?action=clear', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
      })

      const data = await response.json()

      if (data.success) {
        this.showNotification('Корзина очищена', 'success')
        this.updateCartCount()
        this.clearCartItems()
      } else {
        this.showNotification(data.error || 'Ошибка очистки корзины', 'error')
      }
    } catch (error) {
      console.error('Ошибка очистки корзины:', error)
      this.showNotification('Ошибка очистки корзины', 'error')
    }
  }

  // Получить количество товаров в корзине
  async getCartCount() {
    try {
      const response = await fetch('/api/cart.php?action=count')

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }

      const data = await response.json()

      if (data.success && data.data && typeof data.data.count === 'number') {
        return data.data.count
      }
    } catch (error) {
      console.error('Ошибка получения количества товаров:', error)
    }

    return 0
  }

  // Обновить счетчик корзины в шапке
  async updateCartCount() {
    const count = await this.getCartCount()
    const cartCounter = document.querySelector('.cart-counter')

    if (cartCounter) {
      cartCounter.textContent = count
      cartCounter.style.display = count > 0 ? 'block' : 'none'

      // Добавляем анимацию при изменении
      if (count > 0) {
        cartCounter.style.animation = 'pulse 0.5s ease-in-out'
        setTimeout(() => {
          cartCounter.style.animation = ''
        }, 500)
      }
    }
  }

  // Обновить общую сумму корзины
  updateCartTotal() {
    const cartItems = document.querySelectorAll('.cart-item')
    let total = 0

    cartItems.forEach((item) => {
      const price = parseFloat(
        item.querySelector('.item-total').textContent.replace(/[^\d.]/g, '')
      )
      total += price
    })

    const totalElement = document.querySelector('.total-amount')
    if (totalElement) {
      totalElement.textContent = total.toLocaleString('ru-RU') + ' ₽'
    }
  }

  // Показать кнопку подтверждения количества
  showConfirmButton(productId) {
    const confirmBtn = document.querySelector(
      `.quantity-confirm-btn[data-product-id="${productId}"]`
    )
    if (confirmBtn) {
      confirmBtn.style.display = 'inline-flex'
      confirmBtn.style.alignItems = 'center'
      confirmBtn.style.gap = '4px'
      confirmBtn.style.padding = '6px 12px'
      confirmBtn.style.background = '#d2afa0'
      confirmBtn.style.color = 'white'
      confirmBtn.style.border = 'none'
      confirmBtn.style.borderRadius = '4px'
      confirmBtn.style.cursor = 'pointer'
      confirmBtn.style.fontSize = '12px'
      confirmBtn.style.marginTop = '8px'
    }
  }

  // Скрыть кнопку подтверждения количества
  hideConfirmButton(productId) {
    const confirmBtn = document.querySelector(
      `.quantity-confirm-btn[data-product-id="${productId}"]`
    )
    if (confirmBtn) {
      confirmBtn.style.display = 'none'
    }
  }

  // Обновить стоимость товара в реальном времени
  updateItemTotal(productId, quantity) {
    // Получаем цену товара
    const itemTotal = document.querySelector(
      `.item-total[data-product-id="${productId}"]`
    )
    if (!itemTotal) return

    const price = parseFloat(itemTotal.dataset.price) || 0
    const total = price * quantity

    // Обновляем стоимость в карточке товара
    itemTotal.textContent = total.toLocaleString('ru-RU') + ' ₽'

    // Обновляем стоимость в сайдбаре
    const summaryPrice = document.querySelector(
      `.summary-item__price[data-product-id="${productId}"]`
    )
    if (summaryPrice) {
      summaryPrice.textContent = total.toLocaleString('ru-RU') + ' ₽'
    }

    // Обновляем количество в сайдбаре
    const summaryQuantity = document.querySelector(
      `.summary-item[data-product-id="${productId}"] .summary-item__quantity`
    )
    if (summaryQuantity) {
      summaryQuantity.textContent = 'x' + quantity
    }

    // Обновляем общую сумму
    this.updateCartTotal()
  }

  // Удалить товар из DOM
  removeCartItem(productId) {
    const cartItem = document
      .querySelector(`[data-product-id="${productId}"]`)
      ?.closest('.cart-item')
    if (cartItem) {
      cartItem.remove()
      this.updateCartTotal()

      // Если корзина пуста, показываем сообщение
      const cartItems = document.querySelectorAll('.cart-item')
      if (cartItems.length === 0) {
        this.showEmptyCart()
      }
    }
  }

  // Очистить товары из DOM
  clearCartItems() {
    const cartItems = document.querySelectorAll('.cart-item')
    cartItems.forEach((item) => item.remove())
    this.showEmptyCart()
  }

  // Показать пустую корзину
  showEmptyCart() {
    const cartContainer = document.querySelector('.cart-content__container')
    if (cartContainer) {
      cartContainer.innerHTML = `
                <div class="cart-empty">
                    <div class="cart-empty__icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 22C9.55228 22 10 21.5523 10 21C10 20.4477 9.55228 20 9 20C8.44772 20 8 20.4477 8 21C8 21.5523 8.44772 22 9 22Z" stroke="#6a7e9f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M20 22C20.5523 22 21 21.5523 21 21C21 20.4477 20.5523 20 20 20C19.4477 20 19 20.4477 19 21C19 21.5523 19.4477 22 20 22Z" stroke="#6a7e9f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M1 1H5L7.68 14.39C7.77144 14.8504 8.02191 15.264 8.38755 15.5583C8.75318 15.8526 9.2107 16.009 9.68 16H19.4C19.8693 16.009 20.3268 15.8526 20.6925 15.5583C21.0581 15.264 21.3086 14.8504 21.4 14.39L23 6H6" stroke="#6a7e9f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h2 class="cart-empty__title">Корзина пуста</h2>
                    <p class="cart-empty__text">Добавьте товары в корзину, чтобы оформить заказ</p>
                    <a href="/shop.php" class="cart-empty__btn md-main-color-btn">
                        <span>Перейти в магазин</span>
                    </a>
                </div>
            `
    }
  }

  // Применить промокод
  async applyPromoCode() {
    const promoInput = document.getElementById('promoCodeInput')
    const applyBtn = document.getElementById('applyPromoBtn')
    const messageDiv = document.getElementById('promoMessage')

    const promoCode = promoInput.value.trim()

    if (!promoCode) {
      this.showPromoMessage('Введите промокод', 'error')
      return
    }

    // Показываем загрузку
    applyBtn.disabled = true
    applyBtn.textContent = 'Применяется...'

    try {
      const response = await fetch('/api/apply-promo.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({
          promo_code: promoCode,
          cart_total: this.getCartTotal(),
        }),
      })
      const raw = await response.text()
      let data = null
      try {
        data = JSON.parse(raw)
      } catch (e) {
        console.error('Promo API non-JSON response:', raw)
        throw new Error('Invalid JSON from server')
      }

      if (data.success) {
        this.showPromoMessage(data.message || 'Промокод применен', 'success')
        promoInput.value = ''
        // Вставляем/обновляем блок скидки и итог без перезагрузки
        try {
          const subtotalEl = document.querySelector('.subtotal-amount')
          const totalEl = document.querySelector('.total-amount')
          const discountWrapSel = '.cart-summary__discount'
          let discountWrap = document.querySelector(discountWrapSel)
          const discount = Number(data.discount || 0)
          const finalTotal = Number(data.final_total || 0)

          if (!discountWrap) {
            // Создаём блок скидки под подытогом
            const parent =
              subtotalEl?.closest('.cart-summary') ||
              document.querySelector('.cart-summary__items')?.parentElement
            if (parent) {
              discountWrap = document.createElement('div')
              discountWrap.className = 'cart-summary__discount'
              discountWrap.innerHTML =
                '<span class="discount-label">Скидка:</span> <span class="discount-amount">-0 ₽</span>'
              // Вставляем после подытога
              const subtotalRow = subtotalEl?.closest('.cart-summary__subtotal')
              if (subtotalRow && subtotalRow.nextSibling) {
                subtotalRow.parentNode.insertBefore(
                  discountWrap,
                  subtotalRow.nextSibling
                )
              } else {
                parent.appendChild(discountWrap)
              }
            }
          }
          if (discountWrap) {
            const amount =
              discountWrap.querySelector('.discount-amount') ||
              discountWrap.appendChild(document.createElement('span'))
            amount.className = 'discount-amount'
            amount.textContent =
              '-' + (discount || 0).toLocaleString('ru-RU') + ' ₽'
            const label =
              discountWrap.querySelector('.discount-label') ||
              discountWrap.insertBefore(document.createElement('span'), amount)
            label.className = 'discount-label'
            label.textContent =
              'Скидка' +
              (data.promo_code ? ' (' + data.promo_code + ')' : '') +
              ':'
          }
          if (totalEl && finalTotal >= 0) {
            totalEl.textContent = finalTotal.toLocaleString('ru-RU') + ' ₽'
          }

          // Показываем примененный промокод (если есть контейнер)
          const appliedBox = document.querySelector('.applied-promo')
          if (appliedBox) {
            appliedBox.innerHTML =
              'Применен: <strong>' +
              (data.promo_code || '') +
              '</strong>' +
              (data.description ? ' - ' + data.description : '')
            appliedBox.style.display = 'block'
          }
        } catch (e) {
          console.warn('Не удалось обновить суммы после промокода:', e)
        }
        // Обновим внутренние суммы cart.js (без учета скидки)
        this.updateCartTotal()
      } else {
        this.showPromoMessage(
          (data && (data.error || data.message)) ||
            'Ошибка применения промокода',
          'error'
        )
      }
    } catch (error) {
      console.error('Ошибка применения промокода:', error)
      this.showPromoMessage('Ошибка применения промокода', 'error')
    } finally {
      // Восстанавливаем кнопку
      applyBtn.disabled = false
      applyBtn.textContent = 'Применить'
    }
  }

  // Показать сообщение о промокоде
  showPromoMessage(message, type = 'info') {
    const messageDiv = document.getElementById('promoMessage')
    if (messageDiv) {
      messageDiv.textContent = message
      messageDiv.className = `promo-message promo-message--${type}`
      messageDiv.style.display = 'block'

      // Скрываем сообщение через 5 секунд
      setTimeout(() => {
        messageDiv.style.display = 'none'
      }, 5000)
    }
  }

  // Получить общую сумму корзины
  getCartTotal() {
    const totalElement = document.querySelector('.total-amount')
    if (totalElement) {
      const totalText = totalElement.textContent.replace(/[^\d]/g, '')
      return parseInt(totalText) || 0
    }
    return 0
  }

  // Показать уведомление
  showNotification(message, type = 'info') {
    // Создаем элемент уведомления
    const notification = document.createElement('div')
    notification.className = `notification notification--${type}`
    notification.innerHTML = `
            <div class="notification__content">
                <span class="notification__message">${message}</span>
                <button class="notification__close">&times;</button>
            </div>
        `

    // Добавляем стили
    notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${
              type === 'success'
                ? '#d2afa0'
                : type === 'error'
                ? '#dc3545'
                : '#6a7e9f'
            };
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 10000;
            max-width: 300px;
            animation: slideIn 0.3s ease-out;
        `

    // Добавляем в DOM
    document.body.appendChild(notification)

    // Обработчик закрытия
    const closeBtn = notification.querySelector('.notification__close')
    closeBtn.addEventListener('click', () => {
      notification.remove()
    })

    // Автоматическое закрытие через 5 секунд
    setTimeout(() => {
      if (notification.parentNode) {
        notification.style.animation = 'slideOut 0.3s ease-in'
        setTimeout(() => notification.remove(), 300)
      }
    }, 5000)
  }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
  window.cartManager = new CartManager()
})

// Добавляем стили для анимаций
const style = document.createElement('style')
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification__content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    
    .notification__close {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .notification__close:hover {
        opacity: 0.8;
    }
`
document.head.appendChild(style)
