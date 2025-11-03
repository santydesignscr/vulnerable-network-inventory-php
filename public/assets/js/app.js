// Net Inventory - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if Bootstrap tooltips are present
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('[data-action="delete"]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // Search input auto-focus on Ctrl+K
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[type="search"]');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
    });

    // Form validation helper
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Loading indicator for AJAX operations
    window.showLoading = function(message = 'Loading...') {
        const loadingHtml = `
            <div id="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                 background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="mb-0">${message}</p>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', loadingHtml);
    };

    window.hideLoading = function() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    };

    // Copy to clipboard helper
    window.copyToClipboard = function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showNotification('Copied to clipboard!', 'success');
            });
        } else {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            showNotification('Copied to clipboard!', 'success');
        }
    };

    // Show notification
    window.showNotification = function(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 250px;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', alertHtml);
        
        // Auto-remove after 3 seconds
        setTimeout(function() {
            const alert = document.querySelector('.position-fixed.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 3000);
    };

    // Table sorting helper
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    sortableHeaders.forEach(function(header) {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const column = this.dataset.sort;
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('sort', column);
            
            // Toggle order
            const currentOrder = currentUrl.searchParams.get('order');
            currentUrl.searchParams.set('order', currentOrder === 'ASC' ? 'DESC' : 'ASC');
            
            window.location = currentUrl;
        });
    });

    // Debounce helper for search inputs
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // Live search functionality
    const liveSearchInputs = document.querySelectorAll('[data-live-search]');
    liveSearchInputs.forEach(function(input) {
        input.addEventListener('keyup', debounce(function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const targetSelector = input.dataset.liveSearch;
            const items = document.querySelectorAll(targetSelector);
            
            items.forEach(function(item) {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }, 300));
    });

    // Console warning for security testing
    console.log('%c⚠️ SECURITY WARNING', 'color: red; font-size: 20px; font-weight: bold;');
    console.log('%cThis application is INTENTIONALLY VULNERABLE for educational purposes.', 'color: orange; font-size: 14px;');
    console.log('%cDO NOT use in production environments.', 'color: red; font-size: 14px; font-weight: bold;');
    console.log('%c\nVulnerabilities include:', 'color: yellow; font-size: 12px;');
    console.log('- SQL Injection in all database queries');
    console.log('- No CSRF token validation');
    console.log('- Weak session management');
    console.log('- No input sanitization');
    console.log('- Information disclosure in error messages');
});
