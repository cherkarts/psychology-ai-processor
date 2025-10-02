/**
 * Исправления для мобильного меню
 * Решает проблему с отображением меню только в пределах высоты шапки
 * Версия: 1.0
 */

class MobileMenuFix {
  constructor() {
    this.header = document.querySelector('.header')
    this.menuBtn = document.querySelector('.header__menu-btn')
    this.nav = document.querySelector('.header__nav')
    this.navClose = document.querySelector('.header__nav-close')
    this.navLinks = document.querySelectorAll('.nav-link')
    this.isOpen = false

    this.init()
  }

  init() {
    if (!this.menuBtn || !this.nav) {
      console.warn('[MobileMenuFix] Required elements not found')
      return
    }

    console.log('[MobileMenuFix] Initializing mobile menu fix')
    this.setupEventListeners()
    this.ensureMenuStyles()
  }

  setupEventListeners() {
    // Обработчик клика по кнопке меню
    this.menuBtn.addEventListener('click', (e) => {
      e.preventDefault()
      e.stopPropagation()
      this.toggleMenu()
    })

    // Обработчик клика по кнопке закрытия
    if (this.navClose) {
      this.navClose.addEventListener('click', (e) => {
        e.preventDefault()
        e.stopPropagation()
        this.closeMenu()
      })
    }

    // Обработчик клика по ссылкам меню
    this.navLinks.forEach((link) => {
      link.addEventListener('click', (e) => {
        const href = link.getAttribute('href')
        if (href && href !== '#' && !href.startsWith('javascript:')) {
          // Закрываем меню перед переходом
          this.closeMenu()
        }
      })
    })

    // Закрытие по клику вне меню
    document.addEventListener('click', (e) => {
      if (
        this.isOpen &&
        !this.nav.contains(e.target) &&
        !this.menuBtn.contains(e.target)
      ) {
        this.closeMenu()
      }
    })

    // Закрытие по Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.isOpen) {
        this.closeMenu()
      }
    })

    // Обработка изменения размера окна
    window.addEventListener('resize', () => {
      if (window.innerWidth > 900 && this.isOpen) {
        this.closeMenu()
      }
    })
  }

  toggleMenu() {
    if (this.isOpen) {
      this.closeMenu()
    } else {
      this.openMenu()
    }
  }

  openMenu() {
    console.log('[MobileMenuFix] Opening menu')

    // Применяем стили для открытия меню
    this.nav.style.transform = 'translateY(0)'
    this.nav.style.opacity = '1'
    this.nav.style.visibility = 'visible'
    this.nav.style.height = '100vh'
    this.nav.style.overflowY = 'auto'
    this.nav.style.overflowX = 'hidden'

    // Добавляем классы
    this.nav.classList.add('active', 'open')
    if (this.header) {
      this.header.classList.add('open')
    }

    // Блокируем скролл страницы ТОЛЬКО при открытом меню
    document.body.classList.add('menu-open')
    document.body.style.overflow = 'hidden'

    this.isOpen = true
    console.log('[MobileMenuFix] Menu opened')
  }

  closeMenu() {
    console.log('[MobileMenuFix] Closing menu')

    // Применяем стили для закрытия меню
    this.nav.style.transform = 'translateY(-100%)'
    this.nav.style.opacity = '0'
    this.nav.style.visibility = 'hidden'

    // Убираем классы
    this.nav.classList.remove('active', 'open')
    if (this.header) {
      this.header.classList.remove('open')
    }

    // Разблокируем скролл страницы
    document.body.classList.remove('menu-open')
    document.body.style.overflow = ''

    this.isOpen = false
    console.log('[MobileMenuFix] Menu closed')
  }

  ensureMenuStyles() {
    // Убеждаемся, что меню имеет правильные стили
    if (window.innerWidth <= 900) {
      this.nav.style.position = 'fixed'
      this.nav.style.top = '0'
      this.nav.style.left = '0'
      this.nav.style.right = '0'
      this.nav.style.width = '100vw'
      this.nav.style.height = '100vh'
      this.nav.style.background = 'white'
      this.nav.style.zIndex = '9999'
      this.nav.style.transform = 'translateY(-100%)'
      this.nav.style.opacity = '0'
      this.nav.style.visibility = 'hidden'
      this.nav.style.transition = 'all 0.3s ease'
      this.nav.style.overflowY = 'auto'
      this.nav.style.overflowX = 'hidden'
      this.nav.style.paddingTop = '80px'
      this.nav.style.paddingBottom = '20px'
      this.nav.style.boxSizing = 'border-box'

      // Убеждаемся что скролл страницы работает
      document.body.style.overflow = 'auto'
      document.body.classList.remove('menu-open')
    }
  }
}

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
  // Небольшая задержка для совместимости с другими скриптами
  setTimeout(() => {
    new MobileMenuFix()
  }, 100)
})

// Экспорт для использования в других модулях
window.MobileMenuFix = MobileMenuFix
