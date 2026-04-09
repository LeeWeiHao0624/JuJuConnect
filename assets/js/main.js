/**
 * JuJuConnect - Main JavaScript File
 * Handles interactivity and dynamic features
 */

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize popovers
    initializePopovers();
    
    // Auto-dismiss alerts
    autoDismissAlerts();
    
    // Form validation
    initializeFormValidation();
    
    // Smooth scroll
    initializeSmoothScroll();
    
    // Search functionality
    initializeSearchFeatures();
    
    // Notification features
    initializeNotifications();
    
    // Initialize animations
    initializeAnimations();
});

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

/**
 * Initialize Bootstrap popovers
 */
function initializePopovers() {
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
}

/**
 * Auto-dismiss alerts after 5 seconds
 */
function autoDismissAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

/**
 * Form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

/**
 * Smooth scroll for anchor links
 */
function initializeSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Search functionality enhancements
 */
function initializeSearchFeatures() {
    // Live search with debounce
    const searchInputs = document.querySelectorAll('.search-live');
    
    searchInputs.forEach(input => {
        let timeout = null;
        input.addEventListener('keyup', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                performSearch(this.value);
            }, 500);
        });
    });
}

/**
 * Perform search (placeholder - implement based on needs)
 */
function performSearch(query) {
    console.log('Searching for:', query);
    // Implement AJAX search here if needed
}

/**
 * Notification features
 */
function initializeNotifications() {
    // Check for new notifications periodically (if logged in)
    if (document.querySelector('#notificationDropdown')) {
        setInterval(checkNewNotifications, 60000); // Check every minute
    }
}

/**
 * Check for new notifications
 */
function checkNewNotifications() {
    // Get the base path from the current page location
    const basePath = window.location.pathname.substring(0, window.location.pathname.indexOf('/RWDD/') + 6);
    const apiUrl = basePath + 'api/notifications/check.php';
    
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.count > 0) {
                updateNotificationBadge(data.count);
            }
        })
        .catch(error => {
            // Silently fail - don't spam console with errors
        });
}

/**
 * Update notification badge
 */
function updateNotificationBadge(count) {
    const badge = document.querySelector('#notificationDropdown .badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'inline' : 'none';
    }
}

/**
 * Initialize scroll animations
 */
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return 'RM ' + parseFloat(amount).toFixed(2);
}

/**
 * Format date
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-MY', options);
}

/**
 * Format time
 */
function formatTime(timeString) {
    return new Date('1970-01-01 ' + timeString).toLocaleTimeString('en-MY', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Show loading spinner
 */
function showLoading(element) {
    if (element) {
        element.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    }
}

/**
 * Hide loading spinner
 */
function hideLoading(element) {
    if (element) {
        element.innerHTML = '';
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toastHTML = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }
    
    container.insertAdjacentHTML('beforeend', toastHTML);
    const toastElement = container.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

/**
 * Confirm dialog
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Calculate distance between two points (simple approximation)
 */
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius of the Earth in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

/**
 * Validate email format
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Validate phone number (Malaysian format)
 */
function validatePhone(phone) {
    const re = /^(\+?6?01)[0-9]{8,9}$/;
    return re.test(phone.replace(/[\s\-\(\)]/g, ''));
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    }).catch(() => {
        showToast('Failed to copy', 'danger');
    });
}

/**
 * Share ride (Web Share API)
 */
function shareRide(title, text, url) {
    if (navigator.share) {
        navigator.share({
            title: title,
            text: text,
            url: url
        }).catch(error => console.log('Error sharing:', error));
    } else {
        copyToClipboard(url);
        showToast('Link copied to clipboard!', 'success');
    }
}

/**
 * Get user location
 */
function getUserLocation(callback) {
    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(
            position => {
                callback({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                });
            },
            error => {
                console.log('Error getting location:', error);
                showToast('Could not get your location', 'warning');
            }
        );
    } else {
        showToast('Geolocation is not supported', 'warning');
    }
}

/**
 * Initialize image preview for file uploads
 */
function initializeImagePreview() {
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const preview = document.querySelector(`#${input.id}-preview`);
                    if (preview) {
                        preview.src = event.target.result;
                        preview.style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Debounce function
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

/**
 * Throttle function
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Export functions for global use
 */
window.JuJuConnect = {
    showToast,
    confirmAction,
    formatCurrency,
    formatDate,
    formatTime,
    validateEmail,
    validatePhone,
    copyToClipboard,
    shareRide,
    getUserLocation,
    showLoading,
    hideLoading,
    calculateDistance,
    formatFileSize,
    debounce,
    throttle
};

// Console welcome message
console.log('%c🌿 JuJuConnect', 'font-size: 24px; color: #28a745; font-weight: bold;');
console.log('%cSustainable Carpooling Platform', 'font-size: 14px; color: #666;');
console.log('%cVersion 1.0.0', 'font-size: 12px; color: #999;');

