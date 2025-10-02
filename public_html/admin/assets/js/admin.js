// Admin Panel JavaScript - Common functionality

// Global admin object
window.Admin = {
    config: {
        baseUrl: window.location.origin,
        adminPath: '/admin',
        apiPath: '/admin/api'
    },
    
    // Initialize admin functionality
    init: function() {
        this.setupEventListeners();
        this.setupFormValidation();
        this.setupDataTables();
        this.checkBrowserSupport();
    },
    
    // Set up global event listeners
    setupEventListeners: function() {
        // Handle all form submissions with loading states
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.matches('.admin-form')) {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Обработка...';
                    
                    // Re-enable after 10 seconds as fallback
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.dataset.originalText || 'Отправить';
                    }, 10000);
                }
            }
        });
        
        // Handle delete confirmations
        document.addEventListener('click', function(e) {
            if (e.target.matches('[data-confirm-delete]')) {
                e.preventDefault();
                const message = e.target.dataset.confirmDelete || 'Вы уверены, что хотите удалить этот элемент?';
                Admin.confirmAction(message, function() {
                    if (e.target.tagName === 'A') {
                        window.location.href = e.target.href;
                    } else if (e.target.closest('form')) {
                        e.target.closest('form').submit();
                    }
                });
            }
        });
        
        // Handle bulk actions
        document.addEventListener('change', function(e) {
            if (e.target.matches('.bulk-select-all')) {
                const checkboxes = document.querySelectorAll('.bulk-select-item');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
                Admin.updateBulkActions();
            }
            
            if (e.target.matches('.bulk-select-item')) {
                Admin.updateBulkActions();
            }
        });
    },
    
    // Form validation
    setupFormValidation: function() {
        const forms = document.querySelectorAll('.validate-form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!Admin.validateForm(form)) {
                    e.preventDefault();
                    Admin.showMessage('Пожалуйста, исправьте ошибки в форме.', 'error');
                }
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    Admin.validateField(this);
                });
            });
        });
    },
    
    // Data tables functionality
    setupDataTables: function() {
        const tables = document.querySelectorAll('.data-table');
        tables.forEach(table => {
            // Add sorting functionality
            const headers = table.querySelectorAll('th[data-sort]');
            headers.forEach(header => {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    Admin.sortTable(table, this.dataset.sort);
                });
            });
        });
    },
    
    // Check browser support
    checkBrowserSupport: function() {
        if (!window.fetch) {
            Admin.showMessage('Ваш браузер не полностью поддерживается. Пожалуйста, обновите его до современной версии.', 'warning');
        }
    },
    
    // Utility functions
    validateForm: function(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        
        inputs.forEach(input => {
            if (!Admin.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    validateField: function(field) {
        const value = field.value.trim();
        const type = field.type;
        let isValid = true;
        let message = '';
        
        // Required validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            message = 'Это поле обязательно для заполнения.';
        }
        
        // Email validation
        if (isValid && type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Пожалуйста, введите корректный email адрес.';
            }
        }
        
        // URL validation
        if (isValid && type === 'url' && value) {
            try {
                new URL(value);
            } catch {
                isValid = false;
                message = 'Пожалуйста, введите корректную URL-ссылку.';
            }
        }
        
        // Phone validation
        if (isValid && field.dataset.validate === 'phone' && value) {
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            if (!phoneRegex.test(value.replace(/[\s\-\(\)]/g, ''))) {
                isValid = false;
                message = 'Пожалуйста, введите корректный номер телефона.';
            }
        }
        
        // Update field display
        Admin.updateFieldValidation(field, isValid, message);
        
        return isValid;
    },
    
    updateFieldValidation: function(field, isValid, message) {
        const wrapper = field.closest('.form-group') || field.parentElement;
        const errorElement = wrapper.querySelector('.field-error');
        
        // Remove existing error
        if (errorElement) {
            errorElement.remove();
        }
        
        // Update field classes
        field.classList.remove('field-valid', 'field-error');
        
        if (!isValid) {
            field.classList.add('field-error');
            
            // Add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.textContent = message;
            wrapper.appendChild(errorDiv);
        } else if (field.value.trim()) {
            field.classList.add('field-valid');
        }
    },
    
    sortTable: function(table, column) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isAscending = table.dataset.sortOrder !== 'asc';
        
        rows.sort((a, b) => {
            const aValue = a.querySelector(`td[data-sort="${column}"]`)?.textContent || '';
            const bValue = b.querySelector(`td[data-sort="${column}"]`)?.textContent || '';
            
            if (isAscending) {
                return aValue.localeCompare(bValue, undefined, { numeric: true });
            } else {
                return bValue.localeCompare(aValue, undefined, { numeric: true });
            }
        });
        
        // Update table
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        
        // Update sort order
        table.dataset.sortOrder = isAscending ? 'asc' : 'desc';
        
        // Update header indicators
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.classList.remove('sort-asc', 'sort-desc');
            if (header.dataset.sort === column) {
                header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
            }
        });
    },
    
    updateBulkActions: function() {
        const selectedItems = document.querySelectorAll('.bulk-select-item:checked');
        const bulkActions = document.querySelector('.bulk-actions');
        
        if (bulkActions) {
            if (selectedItems.length > 0) {
                bulkActions.style.display = 'block';
                bulkActions.querySelector('.selected-count').textContent = selectedItems.length;
            } else {
                bulkActions.style.display = 'none';
            }
        }
    },
    
    confirmAction: function(message, callback) {
        if (typeof showConfirmModal === 'function') {
            showConfirmModal('Подтвердите действие', message, callback);
        } else {
            if (confirm(message)) {
                callback();
            }
        }
    },
    
    showMessage: function(message, type = 'info') {
        if (typeof showToast === 'function') {
            showToast(message, type);
        } else {
            // Fallback to alert
            alert(message);
        }
    },
    
    // API helper functions
    apiRequest: function(endpoint, options = {}) {
        const url = this.config.apiPath + '/' + endpoint.replace(/^\//, '');
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.adminCSRFToken || ''
            }
        };
        
        const mergedOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        };
        
        if (mergedOptions.body && typeof mergedOptions.body === 'object' && !(mergedOptions.body instanceof FormData)) {
            mergedOptions.body = JSON.stringify(mergedOptions.body);
        }
        
        return fetch(url, mergedOptions)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('API request failed:', error);
                Admin.showMessage('Произошла ошибка. Пожалуйста, попробуйте еще раз.', 'error');
                throw error;
            });
    },
    
    // File upload helper
    uploadFile: function(file, endpoint = 'upload.php', onProgress = null) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('csrf_token', window.adminCSRFToken || '');
            
            const xhr = new XMLHttpRequest();
            
            if (onProgress) {
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        onProgress(percentComplete);
                    }
                });
            }
            
            xhr.addEventListener('load', function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (e) {
                        reject(new Error('Неверный формат ответа'));
                    }
                } else {
                    reject(new Error(`Ошибка загрузки со статусом: ${xhr.status}`));
                }
            });
            
            xhr.addEventListener('error', function() {
                reject(new Error('Ошибка загрузки'));
            });
            
            xhr.open('POST', this.config.apiPath + '/' + endpoint.replace(/^\//, ''));
            xhr.send(formData);
        });
    },
    
    // Format helpers
    formatDate: function(dateString, format = 'dd.mm.yyyy hh:mm') {
        const date = new Date(dateString);
        
        if (isNaN(date.getTime())) {
            return 'Неверная дата';
        }
        
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        
        return format
            .replace('dd', day)
            .replace('mm', month)
            .replace('yyyy', year)
            .replace('hh', hours)
            .replace('mm', minutes);
    },
    
    formatFileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Байт', 'КБ', 'МБ', 'ГБ'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    formatNumber: function(number, decimals = 0) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    },
    
    // Local storage helpers
    saveToStorage: function(key, value) {
        try {
            localStorage.setItem('admin_' + key, JSON.stringify(value));
        } catch (e) {
            console.warn('Failed to save to localStorage:', e);
        }
    },
    
    getFromStorage: function(key, defaultValue = null) {
        try {
            const item = localStorage.getItem('admin_' + key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            console.warn('Failed to read from localStorage:', e);
            return defaultValue;
        }
    },
    
    removeFromStorage: function(key) {
        try {
            localStorage.removeItem('admin_' + key);
        } catch (e) {
            console.warn('Failed to remove from localStorage:', e);
        }
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    Admin.init();
});

// Export for global use
window.Admin = Admin;