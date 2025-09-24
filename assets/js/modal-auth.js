/**
 * ============================================
 * Модальные окна авторизации - StormHosting UA
 * ============================================
 */

class AuthModal {
    constructor() {
        this.currentModal = null;
        this.csrfToken = null;
        this.init();
    }

    init() {
        this.createModals();
        this.bindEvents();
        this.loadCSRFToken();
        console.log('AuthModal initialized');
    }

    /**
     * Загрузка CSRF токена
     */
    async loadCSRFToken() {
        try {
            const response = await fetch('/api/get-csrf-token.php');
            const data = await response.json();
            
            if (data.success && data.csrf_token) {
                this.csrfToken = data.csrf_token;
                this.updateCSRFTokens();
            }
        } catch (error) {
            console.error('Failed to load CSRF token:', error);
        }
    }

    /**
     * Обновление CSRF токенов в формах
     */
    updateCSRFTokens() {
        if (!this.csrfToken) return;
        
        const tokenInputs = document.querySelectorAll('input[name="csrf_token"]');
        tokenInputs.forEach(input => {
            input.value = this.csrfToken;
        });
    }

    /**
     * Создание HTML модальных окон
     */
    createModals() {
        // Проверяем, не существуют ли уже модальные окна
        if (document.getElementById('authModals')) {
            return;
        }

        // Создаем контейнер для модальных окон
        const modalContainer = document.createElement('div');
        modalContainer.id = 'authModals';
        modalContainer.innerHTML = this.getModalsHTML();
        document.body.appendChild(modalContainer);
    }

    /**
     * HTML модальных окон
     */
    getModalsHTML() {
        return `
            <!-- Модальное окно регистрации -->
            <div id="registerModal" class="auth-modal">
                <div class="auth-modal-content">
                    <button type="button" class="auth-modal-close" data-close="registerModal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    
                    <div class="auth-modal-header">
                        <h2><i class="bi bi-person-plus me-2"></i>Реєстрація</h2>
                        <p>Створіть обліковий запис у StormHosting UA</p>
                    </div>
                    
                    <div id="registerAlertContainer"></div>
                    
                    <form id="registerForm" class="auth-form" novalidate>
                        <input type="hidden" name="csrf_token" value="">
                        <input type="hidden" name="action" value="register">
                        
                        <div class="form-group">
                            <label for="reg_full_name" class="form-label">
                                <i class="bi bi-person"></i>Повне ім'я
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" 
                                       id="reg_full_name" 
                                       name="full_name" 
                                       class="form-control" 
                                       placeholder="Введіть ваше повне ім'я"
                                       required
                                       autocomplete="name">
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_email" class="form-label">
                                <i class="bi bi-envelope"></i>Email адреса
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" 
                                       id="reg_email" 
                                       name="email" 
                                       class="form-control" 
                                       placeholder="Введіть ваш email"
                                       required
                                       autocomplete="email">
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_phone" class="form-label">
                                <i class="bi bi-telephone"></i>Телефон (опціонально)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-telephone"></i>
                                </span>
                                <input type="tel" 
                                       id="reg_phone" 
                                       name="phone" 
                                       class="form-control" 
                                       placeholder="+380 67 123 45 67"
                                       autocomplete="tel">
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_password" class="form-label">
                                <i class="bi bi-lock"></i>Пароль
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" 
                                       id="reg_password" 
                                       name="password" 
                                       class="form-control" 
                                       placeholder="Створіть надійний пароль"
                                       required
                                       autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary" data-toggle-password="reg_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                            <div class="form-text">
                                Мінімум 8 символів, включаючи великі та малі літери, цифри
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="reg_password_confirm" class="form-label">
                                <i class="bi bi-lock-fill"></i>Підтвердіть пароль
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" 
                                       id="reg_password_confirm" 
                                       name="password_confirm" 
                                       class="form-control" 
                                       placeholder="Повторіть пароль"
                                       required
                                       autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary" data-toggle-password="reg_password_confirm">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="reg_agree_terms" name="agree_terms" class="form-check-input" required>
                            <label for="reg_agree_terms" class="form-check-label">
                                Я погоджуюсь з <a href="/pages/info/rules.php" target="_blank">умовами використання</a> 
                                та <a href="/pages/info/legal.php" target="_blank">політикою конфіденційності</a>
                            </label>
                            <div class="invalid-feedback">
                                Необхідно погодитися з умовами використання
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-auth" id="registerSubmitBtn">
                            <div class="spinner-border spinner-border-sm loading-spinner" role="status"></div>
                            <span class="btn-text">Зареєструватись</span>
                        </button>
                    </form>
                    
                    <div class="auth-switch">
                        <p>Вже маєте обліковий запис? <a href="#" data-switch-to="loginModal">Увійдіть тут</a></p>
                    </div>
                </div>
            </div>

            <!-- Модальное окно входа -->
            <div id="loginModal" class="auth-modal">
                <div class="auth-modal-content">
                    <button type="button" class="auth-modal-close" data-close="loginModal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    
                    <div class="auth-modal-header">
                        <h2><i class="bi bi-box-arrow-in-right me-2"></i>Вхід</h2>
                        <p>Увійдіть в ваш обліковий запис</p>
                    </div>
                    
                    <div id="loginAlertContainer"></div>
                    
                    <form id="loginForm" class="auth-form" novalidate>
                        <input type="hidden" name="csrf_token" value="">
                        <input type="hidden" name="action" value="login">
                        
                        <div class="form-group">
                            <label for="login_email" class="form-label">
                                <i class="bi bi-envelope"></i>Email адреса
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope"></i>
                                </span>
                                <input type="email" 
                                       id="login_email" 
                                       name="email" 
                                       class="form-control" 
                                       placeholder="Введіть ваш email"
                                       required
                                       autocomplete="email">
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="login_password" class="form-label">
                                <i class="bi bi-lock"></i>Пароль
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>
                                <input type="password" 
                                       id="login_password" 
                                       name="password" 
                                       class="form-control" 
                                       placeholder="Введіть ваш пароль"
                                       required
                                       autocomplete="current-password">
                                <button type="button" class="btn btn-outline-secondary" data-toggle-password="login_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="login_remember_me" name="remember_me" class="form-check-input">
                            <label for="login_remember_me" class="form-check-label">
                                Запам'ятати мене
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-auth" id="loginSubmitBtn">
                            <div class="spinner-border spinner-border-sm loading-spinner" role="status"></div>
                            <span class="btn-text">Увійти</span>
                        </button>
                    </form>
                    
                    <div class="forgot-password">
                        <a href="#" data-forgot-password>Забули пароль?</a>
                    </div>
                    
                    <div class="auth-switch">
                        <p>Немає облікового запису? <a href="#" data-switch-to="registerModal">Зареєструйтеся тут</a></p>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Привязка событий
     */
    bindEvents() {
        // Клик по кнопкам открытия модальных окон
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-open-register]') || e.target.closest('[data-open-register]')) {
                e.preventDefault();
                this.openModal('registerModal');
            }
            
            if (e.target.matches('[data-open-login]') || e.target.closest('[data-open-login]')) {
                e.preventDefault();
                this.openModal('loginModal');
            }
            
            // Переключение между модальными окнами
            if (e.target.matches('[data-switch-to]')) {
                e.preventDefault();
                const targetModal = e.target.getAttribute('data-switch-to');
                this.switchModal(targetModal);
            }
            
            // Закрытие модальных окон
            if (e.target.matches('[data-close]') || e.target.closest('[data-close]')) {
                e.preventDefault();
                const closeBtn = e.target.matches('[data-close]') ? 
                    e.target : e.target.closest('[data-close]');
                const modalId = closeBtn.getAttribute('data-close');
                this.closeModal(modalId);
            }
            
            // Показ/скрытие пароля
            if (e.target.matches('[data-toggle-password]') || e.target.closest('[data-toggle-password]')) {
                e.preventDefault();
                const btn = e.target.matches('[data-toggle-password]') ? 
                    e.target : e.target.closest('[data-toggle-password]');
                const targetId = btn.getAttribute('data-toggle-password');
                this.togglePasswordVisibility(targetId);
            }
            
            // Обработка забытого пароля
            if (e.target.matches('[data-forgot-password]')) {
                e.preventDefault();
                this.handleForgotPassword();
            }
        });

        // Закрытие модального окна по клику на фон
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('auth-modal')) {
                this.closeModal(e.target.id);
            }
        });

        // Закрытие модального окна по нажатию ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.currentModal) {
                this.closeModal(this.currentModal);
            }
        });

        // Привязка обработчиков форм
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'registerForm') {
                this.handleRegisterSubmit(e);
            } else if (e.target.id === 'loginForm') {
                this.handleLoginSubmit(e);
            }
        });

        // Валидация в реальном времени
        document.addEventListener('input', (e) => {
            if (e.target.closest('#registerForm')) {
                this.validateField(e.target);
                
                // Специальная проверка для подтверждения пароля
                if (e.target.name === 'password_confirm' || e.target.name === 'password') {
                    this.validatePasswordMatch('register');
                }
            }
        });
    }

    /**
     * Открытие модального окна
     */
    openModal(modalId) {
        this.closeAllModals();
        
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        this.currentModal = modalId;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // Фокус на первое поле ввода
        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([type="hidden"])');
            if (firstInput) firstInput.focus();
        }, 300);
        
        // Обновляем CSRF токены
        this.updateCSRFTokens();
    }

    /**
     * Закрытие модального окна
     */
    closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        modal.classList.remove('show');
        this.currentModal = null;
        document.body.style.overflow = '';
        
        // Очищаем форму и ошибки
        setTimeout(() => {
            this.resetForm(modalId);
        }, 300);
    }

    /**
     * Переключение между модальными окнами
     */
    switchModal(targetModalId) {
        this.closeAllModals();
        setTimeout(() => {
            this.openModal(targetModalId);
        }, 150);
    }

    /**
     * Закрытие всех модальных окон
     */
    closeAllModals() {
        const modals = document.querySelectorAll('.auth-modal');
        modals.forEach(modal => {
            modal.classList.remove('show');
        });
        this.currentModal = null;
        document.body.style.overflow = '';
    }

    /**
     * Показать/скрыть пароль
     */
    togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = document.querySelector(`[data-toggle-password="${inputId}"]`);
        
        if (!input || !button) {
            console.error('Password toggle elements not found:', inputId);
            return;
        }
        
        const icon = button.querySelector('i');
        if (!icon) {
            console.error('Icon not found in password toggle button');
            return;
        }
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
            button.setAttribute('title', 'Приховати пароль');
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
            button.setAttribute('title', 'Показати пароль');
        }
        
        // Возвращаем фокус на поле ввода
        input.focus();
    }

    /**
     * Валидация поля
     */
    validateField(field) {
        const value = field.value.trim();
        const name = field.name;
        let isValid = true;
        let message = '';

        // Очищаем предыдущие ошибки
        this.clearFieldError(name, this.getModalIdFromField(field));

        switch (name) {
            case 'full_name':
                if (!value) {
                    isValid = false;
                    message = 'Вкажіть повне ім\'я';
                } else if (value.length < 2) {
                    isValid = false;
                    message = 'Ім\'я повинно містити мінімум 2 символи';
                }
                break;

            case 'email':
                if (!value) {
                    isValid = false;
                    message = 'Вкажіть email адресу';
                } else if (!this.isValidEmail(value)) {
                    isValid = false;
                    message = 'Невірний формат email';
                }
                break;

            case 'phone':
                if (value && !this.isValidPhone(value)) {
                    isValid = false;
                    message = 'Невірний формат телефону';
                }
                break;

            case 'password':
                if (!value) {
                    isValid = false;
                    message = 'Вкажіть пароль';
                } else if (value.length < 8) {
                    isValid = false;
                    message = 'Пароль повинен містити мінімум 8 символів';
                } else if (!this.isValidPassword(value)) {
                    isValid = false;
                    message = 'Пароль повинен містити великі та малі літери, цифри';
                }
                break;

            case 'password_confirm':
                const passwordField = document.querySelector(`#${field.closest('form').id} input[name="password"]`);
                if (passwordField && value && value !== passwordField.value) {
                    isValid = false;
                    message = 'Паролі не співпадають';
                }
                break;
        }

        if (!isValid) {
            this.showFieldError(name, message, this.getModalIdFromField(field));
        }

        return isValid;
    }

    /**
     * Валидация совпадения паролей
     */
    validatePasswordMatch(formType) {
        const passwordInput = document.getElementById(`${formType === 'register' ? 'reg' : 'login'}_password`);
        const confirmInput = document.getElementById('reg_password_confirm');
        
        if (passwordInput && confirmInput && confirmInput.value) {
            if (confirmInput.value !== passwordInput.value) {
                this.showFieldError('password_confirm', 'Паролі не співпадають', 'registerModal');
            } else {
                this.clearFieldError('password_confirm', 'registerModal');
            }
        }
    }

    /**
     * Валидация формы регистрации
     */
    validateRegisterForm(form) {
        const formData = new FormData(form);
        let isValid = true;

        // Проверяем все поля
        const fields = form.querySelectorAll('input:not([type="hidden"])');
        fields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        // Проверяем согласие с условиями
        const agreeTerms = form.querySelector('input[name="agree_terms"]');
        if (!agreeTerms.checked) {
            this.showFieldError('agree_terms', 'Необхідно погодитися з умовами використання', 'registerModal');
            isValid = false;
        }

        return isValid;
    }

    /**
     * Валидация формы входа
     */
    validateLoginForm(form) {
        const email = form.querySelector('input[name="email"]').value.trim();
        const password = form.querySelector('input[name="password"]').value;
        let isValid = true;

        if (!email) {
            this.showFieldError('email', 'Вкажіть email адресу', 'loginModal');
            isValid = false;
        } else if (!this.isValidEmail(email)) {
            this.showFieldError('email', 'Невірний формат email', 'loginModal');
            isValid = false;
        }

        if (!password) {
            this.showFieldError('password', 'Вкажіть пароль', 'loginModal');
            isValid = false;
        }

        return isValid;
    }

    /**
     * Обработка отправки формы регистрации
     */
    async handleRegisterSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = document.getElementById('registerSubmitBtn');
        
        // Валидация
        if (!this.validateRegisterForm(form)) {
            return;
        }
        
        // Показываем загрузку
        this.setLoadingState(submitBtn, true, 'Реєстрація...');
        this.clearErrors('registerModal');
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch('/api/auth/register.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', data.message, 'registerModal');
                
                // Перенаправляем или закрываем модальное окно
                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        this.closeModal('registerModal');
                        // Показываем успешное уведомление на главной странице
                        this.showPageNotification('success', 'Реєстрація успішна! Ласкаво просимо!');
                    }
                }, 1500);
            } else {
                if (data.errors) {
                    this.showFieldErrors(data.errors, 'registerModal');
                }
                if (data.message) {
                    this.showAlert('danger', data.message, 'registerModal');
                }
            }
        } catch (error) {
            console.error('Registration error:', error);
            this.showAlert('danger', 'Виникла помилка під час реєстрації. Спробуйте ще раз.', 'registerModal');
        } finally {
            this.setLoadingState(submitBtn, false, 'Зареєструватись');
        }
    }

    /**
     * Обработка отправки формы входа
     */
    async handleLoginSubmit(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = document.getElementById('loginSubmitBtn');
        
        // Валидация
        if (!this.validateLoginForm(form)) {
            return;
        }
        
        // Показываем загрузку
        this.setLoadingState(submitBtn, true, 'Вхід...');
        this.clearErrors('loginModal');
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch('/api/auth/login.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showAlert('success', data.message, 'loginModal');
                
                // Перенаправляем или закрываем модальное окно
                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.reload(); // Перезагружаем страницу для обновления состояния
                    }
                }, 1500);
            } else {
                if (data.errors) {
                    this.showFieldErrors(data.errors, 'loginModal');
                }
                if (data.message) {
                    this.showAlert('danger', data.message, 'loginModal');
                }
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showAlert('danger', 'Виникла помилка під час входу. Спробуйте ще раз.', 'loginModal');
        } finally {
            this.setLoadingState(submitBtn, false, 'Увійти');
        }
    }

    /**
     * Обработка забытого пароля
     */
    handleForgotPassword() {
        // Пока что просто показываем алерт
        // В будущем можно добавить отдельное модальное окно для восстановления пароля
        alert('Функція відновлення пароля буде додана найближчим часом. Зверніться до підтримки: info@stormhosting.ua');
    }

    /**
     * Показ ошибки поля
     */
    showFieldError(fieldName, message, modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        const field = modal.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        const feedback = field.closest('.form-group')?.querySelector('.invalid-feedback') ||
                        field.closest('.form-check')?.querySelector('.invalid-feedback');
        
        if (feedback) {
            feedback.textContent = message;
            feedback.style.display = 'block';
        }
        
        field.classList.add('is-invalid');
    }

    /**
     * Очистка ошибки поля
     */
    clearFieldError(fieldName, modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        const field = modal.querySelector(`[name="${fieldName}"]`);
        if (!field) return;
        
        const feedback = field.closest('.form-group')?.querySelector('.invalid-feedback') ||
                        field.closest('.form-check')?.querySelector('.invalid-feedback');
        
        if (feedback) {
            feedback.style.display = 'none';
        }
        
        field.classList.remove('is-invalid');
    }

    /**
     * Показ ошибок полей
     */
    showFieldErrors(errors, modalId) {
        Object.keys(errors).forEach(fieldName => {
            this.showFieldError(fieldName, errors[fieldName], modalId);
        });
    }

    /**
     * Показ общего алерта
     */
    showAlert(type, message, modalId) {
        const container = document.getElementById(`${modalId.replace('Modal', '')}AlertContainer`);
        if (!container) return;
        
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        
        container.innerHTML = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bi ${iconClass} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Автоматически скрываем через 5 секунд
        setTimeout(() => {
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }

    /**
     * Очистка всех ошибок
     */
    clearErrors(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        // Очищаем общие алерты
        const alertContainer = modal.querySelector('[id$="AlertContainer"]');
        if (alertContainer) {
            alertContainer.innerHTML = '';
        }
        
        // Очищаем ошибки полей
        modal.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
        });
        
        modal.querySelectorAll('.invalid-feedback').forEach(feedback => {
            feedback.style.display = 'none';
        });
    }

    /**
     * Установка состояния загрузки кнопки
     */
    setLoadingState(button, loading, text = '') {
        if (!button) return;
        
        const spinner = button.querySelector('.loading-spinner');
        const btnText = button.querySelector('.btn-text');
        
        if (loading) {
            button.disabled = true;
            if (spinner) spinner.style.display = 'inline-block';
            if (btnText && text) btnText.textContent = text;
        } else {
            button.disabled = false;
            if (spinner) spinner.style.display = 'none';
            if (btnText && text) btnText.textContent = text;
        }
    }

    /**
     * Сброс формы
     */
    resetForm(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            this.clearErrors(modalId);
        }
    }

    /**
     * Показ уведомления на странице
     */
    showPageNotification(type, message) {
        // Создаем контейнер для уведомлений если его нет
        let container = document.getElementById('page-notifications');
        if (!container) {
            container = document.createElement('div');
            container.id = 'page-notifications';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }
        
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
        
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show`;
        notification.style.cssText = 'margin-bottom: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        notification.innerHTML = `
            <i class="bi ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        
        container.appendChild(notification);
        
        // Автоматически скрываем через 5 секунд
        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    /**
     * Получение ID модального окна из поля
     */
    getModalIdFromField(field) {
        const form = field.closest('form');
        if (!form) return null;
        
        if (form.id === 'registerForm') return 'registerModal';
        if (form.id === 'loginForm') return 'loginModal';
        
        return null;
    }

    /**
     * Валидация email
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Валидация телефона
     */
    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,20}$/;
        return phoneRegex.test(phone);
    }

    /**
     * Валидация пароля
     */
    isValidPassword(password) {
        // Минимум 8 символов, включая большие и малые буквы, цифры
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
        return passwordRegex.test(password);
    }
}

// Автоматическая инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, не инициализован ли уже AuthModal
    if (!window.authModal) {
        window.authModal = new AuthModal();
    }
});

// Экспорт для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AuthModal;
}