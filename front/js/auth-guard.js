// auth-check.js

/**
 * Базовый класс для проверки авторизации
 */
class AuthChecker {
    constructor() {
      this.protectedSelectors = [
        '.btn-buy', 
        '.btn-cart',
        '.user-menu',
        '.order-history'
      ];
      this.loginUrl = 'auth.html'; // Убедитесь, что путь правильный
      this.authCheckInterval = 300000; // 5 минут
      this.apiUrl = '../api/get_full_user.php'; // Полный путь к API
    }
  
    /**
     * Инициализация проверки авторизации
     */
    init() {
      this.checkAuthStatus();
      this.setupProtectedElements();
      this.setupAuthWatcher();
    }
  
    /**
     * Проверка статуса авторизации через API
     */
    async checkAuthStatus() {
      try {
        const response = await fetch(this.apiUrl, {
          credentials: 'include'
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
  
        const data = await response.json();
        
        if (data.status === 'success') {
          this.handleAuthorizedState(data.data);
        } else {
          this.handleUnauthorizedState();
        }
      } catch (error) {
        console.error('Auth check failed:', error);
        this.handleUnauthorizedState(true); // Передаем флаг ошибки
      }
    }
  
    /**
     * Обработка авторизованного состояния
     */
    handleAuthorizedState(userData) {
      // Обновление UI
      this.updateUserUI(userData);
      this.toggleProtectedElements(true);
      
      // Сохранение данных пользователя
      window.currentUser = userData;
    }
  
    /**
     * Обработка неавторизованного состояния
     * @param {boolean} isError - Флаг ошибки (404 и т.д.)
     */
    handleUnauthorizedState(isError = false) {
      this.toggleProtectedElements(false);
      
      // Если страница защищенная или произошла ошибка API
      if (this.isProtectedPage() || isError) {
        // Проверяем, чтобы не перенаправлять уже со страницы авторизации
        if (!window.location.pathname.includes('auth.html')) {
          window.location.href = this.loginUrl + '?redirect=' + encodeURIComponent(window.location.pathname);
        }
      }
    }
  
    /**
     * Обновление элементов интерфейса
     */
    updateUserUI(userData) {
      // Пример обновления элементов
      const userElements = document.querySelectorAll('.user-name, .user-avatar');
      userElements.forEach(element => {
        if (element.classList.contains('user-name')) {
          element.textContent = userData.user.name;
        }
        if (element.classList.contains('user-avatar')) {
          element.src = userData.user.photo || '/images/default-avatar.jpg';
        }
      });
    }
  
    /**
     * Управление защищенными элементами
     */
    toggleProtectedElements(enable) {
      this.protectedSelectors.forEach(selector => {
        document.querySelectorAll(selector).forEach(element => {
          if (enable) {
            element.style.display = '';
            element.removeAttribute('data-auth-only');
          } else {
            element.style.display = 'none';
            element.setAttribute('data-auth-only', 'true');
          }
        });
      });
    }
  
    /**
     * Проверка защищенной страницы
     */
    isProtectedPage() {
      return document.body.classList.contains('protected-page') || 
             document.querySelector('[data-protected-page]') !== null;
    }
  
    /**
     * Настройка защиты элементов
     */
    setupProtectedElements() {
      document.addEventListener('click', (e) => {
        const protectedElement = e.target.closest([
          ...this.protectedSelectors,
          '[data-auth-only]'
        ].join(','));
        
        if (protectedElement && !window.currentUser) {
          e.preventDefault();
          window.location.href = this.loginUrl + '?redirect=' + encodeURIComponent(window.location.pathname);
        }
      });
    }
  
    /**
     * Периодическая проверка авторизации
     */
    setupAuthWatcher() {
      setInterval(() => this.checkAuthStatus(), this.authCheckInterval);
    }
  }
  
  // Инициализация при загрузке страницы
  document.addEventListener('DOMContentLoaded', () => {
    const authChecker = new AuthChecker();
    authChecker.init();
  });