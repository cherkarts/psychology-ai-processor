/**
 * Универсальная шапка с анимацией скрытия/показа
 * Автоматически скрывается при скролле вниз и появляется при скролле вверх
 */

class UniversalHeader {
  constructor() {
    this.header = document.getElementById('main-header')
    this.lastScrollTop = 0
    this.scrollThreshold = 100 // Порог для начала скрытия
    this.hideThreshold = 200 // Порог для полного скрытия
    this.collapseThreshold = 150 // Порог для сворачивания
    this.isScrolling = false
    this.scrollTimeout = null

    // Элементы для мобильного меню
    this.menuBtn = document.querySelector('.header__menu-btn')
    this.nav = document.querySelector('.header__nav')
    this.navClose = document.querySelector('.header__nav-close')
    this.navLinks = document.querySelectorAll('.nav-link')

    this.init()
  }

  init() {
    if (!this.header) {
      console.warn('Header element not found')
      return
    }

    this.setupScrollListener()
    this.setupMobileMenu()
    this.setupCartCounter()
    this.setupAccessibility()

    // Инициализация при загрузке
    this.updateHeaderState()

    console.log('Universal Header initialized')
  }

  /**
   * Настройка обработчика скролла
   */
  setupScrollListener() {
    let ticking = false

    const handleScroll = () => {
      if (!ticking) {
        requestAnimationFrame(() => {
          this.updateHeaderState()
          ticking = false
        })
        ticking = true
      }
    }

    window.addEventListener('scroll', handleScroll, { passive: true })

    // Обработка изменения размера окна
    window.addEventListener('resize', () => {
      this.updateHeaderState()
    })
  }

  /**
   * Обновление состояния шапки в зависимости от позиции скролла
   */
  updateHeaderState() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop
    const scrollDirection = scrollTop > this.lastScrollTop ? 'down' : 'up'
    const scrollDelta = Math.abs(scrollTop - this.lastScrollTop)

    // Игнорируем небольшие изменения скролла
    if (scrollDelta < 5) {
      return
    }

    // Вверху страницы - всегда показываем полную шапку
    if (scrollTop <= this.scrollThreshold) {
      this.showFullHeader()
    }
    // Скролл вниз - скрываем шапку
    else if (scrollDirection === 'down' && scrollTop > this.hideThreshold) {
      this.hideHeader()
    }
    // Скролл вверх - показываем шапку
    else if (scrollDirection === 'up') {
      if (scrollTop > this.collapseThreshold) {
        this.showCollapsedHeader()
      } else {
        this.showFullHeader()
      }
    }

    this.lastScrollTop = scrollTop
  }

  /**
   * Показать полную шапку
   */
  showFullHeader() {
    this.header.classList.remove('header--hidden', 'header--collapsed')
    this.header.style.transform = 'translateY(0)'
  }

  /**
   * Показать свернутую шапку (только меню)
   */
  showCollapsedHeader() {
    this.header.classList.remove('header--hidden')
    this.header.classList.add('header--collapsed')
    this.header.style.transform = 'translateY(-60px)'
  }

  /**
   * Скрыть шапку полностью
   */
  hideHeader() {
    this.header.classList.remove('header--collapsed')
    this.header.classList.add('header--hidden')
    this.header.style.transform = 'translateY(-100%)'
  }

  /**
   * Настройка мобильного меню
   */
  setupMobileMenu() {
    if (!this.menuBtn || !this.nav) return

    // Открытие меню
    this.menuBtn.addEventListener('click', () => {
      this.openMobileMenu()
    })

    // Закрытие меню
    if (this.navClose) {
      this.navClose.addEventListener('click', () => {
        this.closeMobileMenu()
      })
    }

    // Закрытие при клике на ссылку
    this.navLinks.forEach((link) => {
      link.addEventListener('click', () => {
        this.closeMobileMenu()
      })
    })

    // Закрытие при клике вне меню
    this.nav.addEventListener('click', (e) => {
      if (e.target === this.nav) {
        this.closeMobileMenu()
      }
    })

    // Закрытие по Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.nav.classList.contains('active')) {
        this.closeMobileMenu()
      }
    })
  }

  /**
   * Открыть мобильное меню
   */
  openMobileMenu() {
    this.nav.classList.add('active')
    this.menuBtn.classList.add('active')
    document.body.style.overflow = 'hidden'

    // Анимация появления
    requestAnimationFrame(() => {
      this.nav.style.opacity = '1'
    })
  }

  /**
   * Закрыть мобильное меню
   */
  closeMobileMenu() {
    this.nav.classList.remove('active')
    this.menuBtn.classList.remove('active')
    document.body.style.overflow = ''

    // Анимация исчезновения
    this.nav.style.opacity = '0'
  }

  /**
   * Настройка счетчика корзины
   */
  setupCartCounter() {
    // Обновление счетчика корзины
    this.updateCartCounter()

    // Обновление при изменениях в корзине
    document.addEventListener('cartUpdated', () => {
      this.updateCartCounter()
    })
  }

  /**
   * Обновить счетчик корзины
   */
  updateCartCounter() {
    const counter = document.querySelector('.cart-counter')
    if (!counter) return

    fetch('/api/cart.php')
      .then((response) => response.json())
      .then((data) => {
        if (data.count !== undefined) {
          counter.textContent = data.count

          // Анимация обновления
          if (data.count > 0) {
            counter.style.animation = 'none'
            requestAnimationFrame(() => {
              counter.style.animation = 'pulse 0.3s ease-in-out'
            })
          }
        }
      })
      .catch((error) => {
        console.error('Error updating cart counter:', error)
      })
  }

  /**
   * Настройка доступности
   */
  setupAccessibility() {
    // Управление с клавиатуры
    this.menuBtn.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault()
        this.openMobileMenu()
      }
    })

    // ARIA атрибуты
    if (this.menuBtn) {
      this.menuBtn.setAttribute('aria-expanded', 'false')
      this.menuBtn.setAttribute('aria-controls', 'main-navigation')
    }

    if (this.nav) {
      this.nav.setAttribute('id', 'main-navigation')
      this.nav.setAttribute('aria-hidden', 'true')
    }
  }

  /**
   * Публичные методы для внешнего управления
   */

  // Принудительно показать шапку
  forceShow() {
    this.showFullHeader()
  }

  // Принудительно скрыть шапку
  forceHide() {
    this.hideHeader()
  }

  // Получить текущее состояние
  getState() {
    return {
      isHidden: this.header.classList.contains('header--hidden'),
      isCollapsed: this.header.classList.contains('header--collapsed'),
      scrollTop: window.pageYOffset || document.documentElement.scrollTop,
    }
  }

  // Обновить настройки
  updateSettings(settings) {
    if (settings.scrollThreshold)
      this.scrollThreshold = settings.scrollThreshold
    if (settings.hideThreshold) this.hideThreshold = settings.hideThreshold
    if (settings.collapseThreshold)
      this.collapseThreshold = settings.collapseThreshold
  }
}

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
  // Создаем глобальный экземпляр
  window.universalHeader = new UniversalHeader()
})

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
  module.exports = UniversalHeader
}
