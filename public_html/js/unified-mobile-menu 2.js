/**
 * Универсальное мобильное меню для всех страниц
 * Совместимо с новой структурой шапки
 * Версия: 1.0
 */

class UnifiedMobileMenu {
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
      console.warn('UnifiedMobileMenu: Не найдены необходимые элементы')
      return
    }

    this.setupEventListeners()
    this.setActiveLink()
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

    // Обработка свайпов на мобильных
    this.setupSwipeHandlers()
  }

  toggleMenu() {
    if (this.isOpen) {
      this.closeMenu()
    } else {
      this.openMenu()
    }
  }

  openMenu() {
    this.isOpen = true
    this.menuBtn.classList.add('open')
    this.nav.classList.add('open')
    this.header.classList.add('open')

    // Блокируем скролл body
    document.body.classList.add('menu-open')
    document.body.style.overflow = 'hidden'

    // Анимация открытия
    this.nav.style.display = 'block'
    setTimeout(() => {
      this.nav.classList.add('active')
    }, 10)

    console.log('UnifiedMobileMenu: Меню открыто')
  }

  closeMenu() {
    this.isOpen = false
    this.menuBtn.classList.remove('open')
    this.nav.classList.remove('open', 'active')
    this.header.classList.remove('open')

    // Разблокируем скролл body
    document.body.classList.remove('menu-open')
    document.body.style.overflow = ''

    // Анимация закрытия
    setTimeout(() => {
      if (!this.isOpen) {
        this.nav.style.display = 'none'
      }
    }, 300)

    console.log('UnifiedMobileMenu: Меню закрыто')
  }

  setActiveLink() {
    const currentPath = window.location.pathname

    this.navLinks.forEach((link) => {
      const href = link.getAttribute('href')

      // Убираем старые классы
      link.classList.remove('active')

      // Проверяем соответствие текущему пути
      if (
        href === currentPath ||
        (currentPath === '/' && href === '/') ||
        (currentPath.includes(href.replace('/', '')) && href !== '/')
      ) {
        link.classList.add('active')
      }
    })
  }

  setupSwipeHandlers() {
    let startX = 0
    let startY = 0
    let isSwiping = false

    // Начало свайпа
    document.addEventListener('touchstart', (e) => {
      if (!this.isOpen) return

      startX = e.touches[0].clientX
      startY = e.touches[0].clientY
      isSwiping = true
    })

    // Движение свайпа
    document.addEventListener('touchmove', (e) => {
      if (!isSwiping || !this.isOpen) return

      const currentX = e.touches[0].clientX
      const currentY = e.touches[0].clientY
      const diffX = startX - currentX
      const diffY = startY - currentY

      // Проверяем, что это горизонтальный свайп
      if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
        // Свайп влево - закрываем меню
        if (diffX > 0) {
          this.closeMenu()
        }
        isSwiping = false
      }
    })

    // Конец свайпа
    document.addEventListener('touchend', () => {
      isSwiping = false
    })
  }
}

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
  new UnifiedMobileMenu()
})

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
  module.exports = UnifiedMobileMenu
}

