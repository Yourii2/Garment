// وظائف JavaScript محسنة للنظام
document.addEventListener('DOMContentLoaded', function() {
    // إخفاء الرسائل تلقائ
    autoHideAlerts();
    
    // تفعيل tooltips
    initializeTooltips();
    
    // تفعيل popovers
    initializePopovers();
    
    // تحسين النماذج
    enhanceForms();
    
    // تحسين الجداول
    enhanceTables();
    
    // إضافة تأثيرات التحميل
    addLoadingEffects();
    
    // تحسين التنقل
    enhanceNavigation();
});

// إخفاء الرسائل تلقائ
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.remove();
                    }
                }, 500);
            }
        }, 5000);
    });
}

// تفعيل tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { show: 500, hide: 100 }
        });
    });
}

// تفعيل popovers
function initializePopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// تحسين النماذج
function enhanceForms() {
    // إضافة تأكيد قبل الحذف
    const deleteButtons = document.querySelectorAll('.btn-delete, [data-action="delete"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('هل أنت متأكد من الحذف؟ هذا الإجراء لا يمكن التراجع عنه.')) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // تحسين حقول الأرقام
    const numberInputs = document.querySelectorAll('input[type="number"]');
    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value < 0) {
                this.value = 0;
            }
        });
    });
    
    // تحسين حقول النص
    const textInputs = document.querySelectorAll('input[type="text"], textarea');
    textInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.trim();
        });
    });
    
    // إضافة تأثيرات التركيز
    const formControls = document.querySelectorAll('.form-control, .form-select');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        control.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

// تحسين الجداول
function enhanceTables() {
    // إضافة فرز للجداول
    const tables = document.querySelectorAll('.table-sortable');
    tables.forEach(table => {
        makeSortable(table);
    });
    
    // إضافة بحث للجداول
    const searchInputs = document.querySelectorAll('.table-search');
    searchInputs.forEach(input => {
        const tableId = input.getAttribute('data-table');
        const table = document.getElementById(tableId);
        if (table) {
            addTableSearch(input, table);
        }
    });
}

// جعل الجدول قابل للفرز
function makeSortable(table) {
    const headers = table.querySelectorAll('th[data-sortable]');
    headers.forEach(header => {
        header.style.cursor = 'pointer';
        header.innerHTML += ' <i class="fas fa-sort text-muted"></i>';
        
        header.addEventListener('click', function() {
            sortTable(table, this);
        });
    });
}

// فرز الجدول
function sortTable(table, header) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const columnIndex = Array.from(header.parentNode.children).indexOf(header);
    const isAscending = !header.classList.contains('sort-asc');
    
    // إزالة أيقونات الفرز السابقة
    table.querySelectorAll('th i').forEach(icon => {
        icon.className = 'fas fa-sort text-muted';
    });
    
    // تحديث أيقونة الفرز
    const icon = header.querySelector('i');
    icon.className = isAscending ? 'fas fa-sort-up text-primary' : 'fas fa-sort-down text-primary';
    
    // إزالة فئات الفرز السابقة
    table.querySelectorAll('th').forEach(th => {
        th.classList.remove('sort-asc', 'sort-desc');
    });
    
    // إضافة فئة الفرز الجديدة
    header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
    
    // فرز الصفوف
    rows.sort((a, b) => {
        const aValue = a.children[columnIndex].textContent.trim();
        const bValue = b.children[columnIndex].textContent.trim();
        
        // محاولة تحويل إلى رقم
        const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return isAscending ? aNum - bNum : bNum - aNum;
        } else {
            return isAscending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
        }
    });
    
    // إعادة ترتيب الصفوف
    rows.forEach(row => tbody.appendChild(row));
}

// إضافة بحث للجدول
function addTableSearch(input, table) {
    input.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
}

// إضافة تأثيرات التحميل
function addLoadingEffects() {
    // إضافة تأثير تحميل للنماذج
    const forms = document.querySelectorAll('form[data-loading]');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            showLoading();
        });
    });
    
    // إضافة تأثير تحميل للروابط
    const loadingLinks = document.querySelectorAll('a[data-loading]');
    loadingLinks.forEach(link => {
        link.addEventListener('click', function() {
            showLoading();
        });
    });
}

// عرض شاشة التحميل
function showLoading(message = 'جاري التحميل...') {
    let loadingScreen = document.getElementById('loadingScreen');
    if (!loadingScreen) {
        loadingScreen = document.createElement('div');
        loadingScreen.id = 'loadingScreen';
        loadingScreen.className = 'loading-overlay';
        loadingScreen.innerHTML = `
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin fa-3x"></i>
                <div class="mt-3">${message}</div>
            </div>
        `;
        document.body.appendChild(loadingScreen);
    }
    loadingScreen.style.display = 'flex';
}

// إخفاء شاشة التحميل
function hideLoading() {
    const loadingScreen = document.getElementById('loadingScreen');
    if (loadingScreen) {
        loadingScreen.style.display = 'none';
    }
}

// تحسين التنقل
function enhanceNavigation() {
    // إضافة تأثيرات للروابط النشطة
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
    
    // إضافة تأثير smooth scroll
    const smoothLinks = document.querySelectorAll('a[href^="#"]');
    smoothLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// وظائف مساعدة
function formatNumber(number, decimals = 2) {
    return new Intl.NumberFormat('ar-EG', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

function formatCurrency(amount, currency = 'EGP') {
    return new Intl.NumberFormat('ar-EG', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

function formatDate(date, options = {}) {
    const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    return new Intl.DateTimeFormat('ar-EG', {...defaultOptions, ...options}).format(new Date(date));
}

// وظائف AJAX محسنة
function makeRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    return fetch(url, {...defaultOptions, ...options})
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Request failed:', error);
            showAlert('حدث خطأ في الاتصال بالخادم', 'danger');
            throw error;
        });
}

// عرض تنبيه
function showAlert(message, type = 'info', duration = 5000) {
    const alertContainer = document.getElementById('alert-container') || document.body;
    const alertId = 'alert-' + Date.now();
    
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${getAlertIcon(type)} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML('afterbegin', alertHTML);
    
    // إخفاء تلقائي
    if (duration > 0) {
        setTimeout(() => {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.remove();
            }
        }, duration);
    }
}

function getAlertIcon(type) {
    const icons = {
        'success': 'check-circle',
        'danger': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// تصدير الوظائف للاستخدام العام
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showAlert = showAlert;
window.makeRequest = makeRequest;
window.formatNumber = formatNumber;
window.formatCurrency = formatCurrency;
window.formatDate = formatDate;

// وظائف التحكم في القائمة الجانبية
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('show');
}

// إغلاق القائمة عند النقر خارجها في الشاشات الصغيرة
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const toggleButton = document.querySelector('.navbar-toggler');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggleButton.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    }
});

// تحديث حالة القائمة عند تغيير حجم الشاشة
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    if (window.innerWidth > 768) {
        sidebar.classList.remove('show');
    }
});

// تفعيل القوائم المنسدلة
document.addEventListener('DOMContentLoaded', function() {
    // إضافة تأثيرات الحركة للعناصر
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
    
    // تفعيل tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // تحسين القوائم المنسدلة
    const collapseElements = document.querySelectorAll('.collapse');
    collapseElements.forEach(collapse => {
        collapse.addEventListener('show.bs.collapse', function() {
            const trigger = document.querySelector(`[href="#${this.id}"]`);
            if (trigger) {
                trigger.classList.remove('collapsed');
                const icon = trigger.querySelector('.fa-chevron-down');
                if (icon) {
                    icon.style.transform = 'rotate(180deg)';
                }
            }
        });
        
        collapse.addEventListener('hide.bs.collapse', function() {
            const trigger = document.querySelector(`[href="#${this.id}"]`);
            if (trigger) {
                trigger.classList.add('collapsed');
                const icon = trigger.querySelector('.fa-chevron-down');
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        });
    });
});

