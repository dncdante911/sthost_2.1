/**
 * ============================================
 * Скрипт регистрации - StormHosting UA
 * ============================================
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Registration page loaded');
    
    // Получаем элементы формы
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const strengthBar = document.querySelector('.password-strength');
    const strengthFill = document.querySelector('.strength-fill');
    const emailInput = document.getElementById('email');
    const fullNameInput = document.getElementById('full_name');
    
    // Инициализация
    initializeForm();
    
    /**
     * Инициализация формы
     */
    function initializeForm() {
        // Показать/скрыть пароль
        if (togglePasswordBtn) {
            togglePasswordBtn.addEventListener('click', togglePasswordVisibility);
        }
        
        // Проверка силы пароля
        if (passwordInput) {
            passwordInput.addEventListener('input', checkPasswordStrength);
            passwordInput.addEventListener('input', validatePasswordMatch);
        }
        
        // Проверка совпадения паролей
        if (passwordConfirmInput) {
            passwordConfirmInput.addEventListener('input', validatePasswordMatch);
        }
        
        // Валидация email в реальном времени
        if (emailInput) {
            emailInput.addEventListener('blur', validateEmail);
        }
        
        // Валидация имени
        if (fullNameInput) {
            fullNameInput.addEventListener('input', validateFullName);
        }
        
        // Обработка отправки формы
        if (form) {
            form.addEventListener('submit', handleFormSubmit);
        }
        
        // Убираем валидацию при вводе
        const inputs = form.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentNode.querySelector('.invalid-feedback');
                    if (feedback) {
                        feedback.textContent = '';
                    }
                }
            });
        });
    }
    
    /**
     * Показать/скрыть пароль
     */
    function togglePasswordVisibility() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        if (icon) {
            icon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }
    }
    
    /**
     * Проверка силы пароля
     */
    function checkPasswordStrength() {
        const password = this.value;
        
        if (strengthBar) {
            strengthBar.style.display = password.length > 0 ? 'block' : 'none';
        }
        
        if (password.length === 0) return;
        
        let strength = 0;
        
        // Проверки силы пароля
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z\d]/.test(password)) strength++;
        
        // Установка класса силы
        if (strengthFill) {
            strengthFill.className = 'strength-fill';
            if (strength === 1) strengthFill.classList.add('strength-weak');
            else if (strength === 2) strengthFill.classList.add('strength-fair');
            else if (strength === 3) strengthFill.classList.add('strength-good');
            else if (strength >= 4) strengthFill.classList.add('strength-strong');
        }
    }
    
    /**
     * Проверка совпадения паролей
     */
    function validatePasswordMatch() {
        if (passwordConfirmInput && passwordConfirmInput.value) {
            if (passwordConfirmInput.value !== passwordInput.value) {
                passwordConfirmInput.setCustomValidity('Паролі не співпадають');
                showFieldError('password_confirm', 'Паролі не співпадають');
            } else {
                passwordConfirmInput.setCustomValidity('');
                clearFieldError('password_confirm');
            }
        }
    }
    
    /**
     * Валидация email
     */
    function validateEmail() {
        const email = this.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            showFieldError('email', 'Введіть коректну email адресу');
        } else {
            clearFieldError('email');
        }
    }
    
    /**
     * Валидация полного имени
     */
    function validateFullName() {
        const name = this.value.trim();
        
        if (name.length > 0 && name.length < 2) {
            showFieldError('full_name', 'Ім\'я повинно містити мінімум 2 символи');
        } else if (name.length > 0 && !/^[a-zA-Zа-яА-ЯіїєґІЇЄҐ\s\-\'\.]+$/u.test(name)) {
            showFieldError('full_name', 'Ім\'я може містити тільки літери, пробіли та дефіси');
        } else {
            clearFieldError('full_name');
        }
    }
    
    /**
     * Обработка отправки формы
     */
    function handleFormSubmit(e) {
        e.preventDefault();
        console.log('Form submission started');
        
        // Проверяем валидность формы
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            showAlert('warning', 'Будь ласка, заповніть всі обов\'язкові поля коректно');
            return;
        }
        
        // Дополнительная валидация
        if (!validateFormData()) {
            return;
        }
        
        submitForm();
    }
    
    /**
     * Дополнительная валидация данных формы
     */
    function validateFormData() {
        let isValid = true;
        
        // Проверка email
        const email = emailInput.value.trim();
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showFieldError('email', 'Введіть коректну email адресу');
            isValid = false;
        }
        
        // Проверка пароля
        const password = passwordInput.value;
        if (password.length < 8) {
            showFieldError('password', 'Пароль повинен містити мінімум 8 символів');
            isValid = false;
        } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/.test(password)) {
            showFieldError('password', 'Пароль повинен містити великі і малі літери та цифри');
            isValid = false;
        }
        
        // Проверка совпадения паролей
        if (password !== passwordConfirmInput.value) {
            showFieldError('password_confirm', 'Паролі не співпадають');
            isValid = false;
        }
        
        // Проверка согласия с условиями
        const acceptTerms = document.getElementById('accept_terms');
        if (!acceptTerms.checked) {
            showFieldError('accept_terms', 'Необхідно прийняти умови використання');
            isValid = false;
        }
        
        return isValid;
    }
    
    /**
     * Отправка формы
     */
    function submitForm() {
        const formData = new FormData(form);
        
        // Показываем загрузку
        setLoadingState(true);
        clearErrors();
        
        console.log('Sending registration request...');
        
        fetch('/auth/register.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                showAlert('success', data.message);
                
                // Перенаправляем в личный кабинет
                setTimeout(() => {
                    window.location.href = data.redirect || '/client/dashboard.php';
                }, 1500);
            } else {
                if (data.errors) {
                    showFieldErrors(data.errors);
                }
                if (data.message) {
                    showAlert('danger', data.message);
                }
            }
        })
        .catch(error => {
            console.error('Registration error:', error);
            showAlert('danger', 'Виникла помилка під час реєстрації. Перевірте підключення до інтернету та спробуйте ще раз.');
        })
        .finally(() => {
            setLoadingState(false);
        });
    }
    
    /**
     * Установка состояния загрузки
     */
    function setLoadingState(loading) {
        if (submitBtn) {
            submitBtn.disabled = loading;
            
            const spinner = submitBtn.querySelector('.loading-spinner');
            const btnText = submitBtn.querySelector('.btn-text');
            
            if (spinner) {
                spinner.style.display = loading ? 'inline-block' : 'none';
            }
            
            if (btnText) {
                btnText.textContent = loading ? 'Реєстрація...' : 'Зареєструватися';
            }
        }
    }
    
    /**
     * Показать уведомление
     */
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        if (!alertContainer) return;
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${getAlertIcon(type)} me-2"></i>
                <div>${message}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        alertContainer.appendChild(alert);
        
        // Автоматически скрыть через 5 секунд
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
        
        // Прокрутить к уведомлению
        alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    /**
     * Получить иконку для уведомления
     */
    function getAlertIcon(type) {
        const icons = {
            'success': 'check-circle-fill',
            'danger': 'exclamation-triangle-fill',
            'warning': 'exclamation-circle-fill',
            'info': 'info-circle-fill'
        };
        return icons[type] || 'info-circle-fill';
    }
    
    /**
     * Показать ошибки полей
     */
    function showFieldErrors(errors) {
        form.classList.add('was-validated');
        
        Object.keys(errors).forEach(field => {
            showFieldError(field, errors[field]);
        });
    }
    
    /**
     * Показать ошибку поля
     */
    function showFieldError(fieldName, message) {
        const input = form.querySelector(`[name="${fieldName}"]`);
        if (input) {
            input.classList.add('is-invalid');
            
            let feedback = input.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = input.nextElementSibling;
                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    input.parentNode.appendChild(feedback);
                }
            }
            
            if (feedback) {
                feedback.textContent = message;
            }
        }
    }
    
    /**
     * Очистить ошибку поля
     */
    function clearFieldError(fieldName) {
        const input = form.querySelector(`[name="${fieldName}"]`);
        if (input) {
            input.classList.remove('is-invalid');
            
            const feedback = input.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.textContent = '';
            }
        }
    }
    
    /**
     * Очистить все ошибки
     */
    function clearErrors() {
        form.classList.remove('was-validated');
        
        form.querySelectorAll('.is-invalid').forEach(input => {
            input.classList.remove('is-invalid');
        });
        
        form.querySelectorAll('.invalid-feedback').forEach(feedback => {
            feedback.textContent = '';
        });
        
        const alertContainer = document.getElementById('alertContainer');
        if (alertContainer) {
            alertContainer.innerHTML = '';
        }
    }
    
    /**
     * Утилиты для улучшения UX
     */
    
    // Предотвращение отправки формы по Enter в полях (кроме кнопки submit)
    form.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.type !== 'submit') {
            e.preventDefault();
            
            // Переходим к следующему полю
            const inputs = Array.from(form.querySelectorAll('input, select, textarea'));
            const currentIndex = inputs.indexOf(e.target);
            const nextInput = inputs[currentIndex + 1];
            
            if (nextInput) {
                nextInput.focus();
            } else {
                // Если это последнее поле, отправляем форму
                form.requestSubmit();
            }
        }
    });
    
    // Автофокус на первое поле при загрузке
    setTimeout(() => {
        const firstInput = form.querySelector('input[type="text"], input[type="email"]');
        if (firstInput) {
            firstInput.focus();
        }
    }, 300);
    
    // Сохранение прогресса заполнения в localStorage (опционально)
    const saveFormProgress = () => {
        const formData = new FormData(form);
        const data = {};
        
        // Сохраняем только безопасные поля (не пароли)
        const safeFields = ['full_name', 'email', 'phone', 'language'];
        
        safeFields.forEach(field => {
            if (formData.has(field)) {
                data[field] = formData.get(field);
            }
        });
        
        localStorage.setItem('registerFormProgress', JSON.stringify(data));
    };
    
    // Восстановление прогресса заполнения
    const restoreFormProgress = () => {
        try {
            const saved = localStorage.getItem('registerFormProgress');
            if (saved) {
                const data = JSON.parse(saved);
                
                Object.keys(data).forEach(field => {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input && data[field]) {
                        input.value = data[field];
                    }
                });
            }
        } catch (e) {
            console.warn('Could not restore form progress:', e);
        }
    };
    
    // Автосохранение при изменении полей
    form.addEventListener('input', debounce(saveFormProgress, 1000));
    
    // Восстанавливаем прогресс при загрузке
    restoreFormProgress();
    
    // Очищаем сохраненный прогресс при успешной регистрации
    form.addEventListener('submit', () => {
        localStorage.removeItem('registerFormProgress');
    });
    
    /**
     * Debounce функция для оптимизации
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    console.log('Registration form initialized successfully');
});