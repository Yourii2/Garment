// إدارة الوضع الليلي المحسن
document.addEventListener('DOMContentLoaded', function() {
    initializeTheme();
    setupThemeToggle();
    addThemeTransitions();
});

// تهيئة الوضع
function initializeTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // استخدام الوضع المحفوظ أو تفضيل النظام
    const theme = savedTheme === 'auto' ? (systemPrefersDark ? 'dark' : 'light') : savedTheme;
    
    setTheme(theme);
    updateThemeIcon(theme);
}

// تعيين الوضع
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    
    // تحديث meta theme-color للمتصفحات المحمولة
    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (metaThemeColor) {
        metaThemeColor.setAttribute('content', theme === 'dark' ? '#252837' : '#ffffff');
    }
    
    // إضافة تأثير انتقالي
    document.body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
    
    // تحديث الرسوم البيانية إذا كانت موجودة
    if (typeof updateChartsTheme === 'function') {
        updateChartsTheme(theme);
    }
}

// إعداد زر التبديل
function setupThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
        
        // إضافة تأثير صوتي (اختياري)
        themeToggle.addEventListener('click', function() {
            if ('vibrate' in navigator) {
                navigator.vibrate(50);
            }
        });
    }
}

// تبديل الوضع
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // إضافة تأثير انتقالي سلس
    document.body.style.transition = 'all 0.3s ease';
    
    setTheme(newTheme);
    updateThemeIcon(newTheme);
    
    // إضافة تأثير بصري للتبديل
    const toggle = document.getElementById('theme-toggle');
    if (toggle) {
        toggle.style.transform = 'scale(0.9)';
        setTimeout(() => {
            toggle.style.transform = 'scale(1)';
        }, 150);
    }
    
    // إشعار المستخدم
    showThemeNotification(newTheme);
}

// تحديث أيقونة الوضع
function updateThemeIcon(theme) {
    const sunIcon = document.querySelector('#theme-toggle .fa-sun');
    const moonIcon = document.querySelector('#theme-toggle .fa-moon');
    
    if (sunIcon && moonIcon) {
        if (theme === 'dark') {
            sunIcon.style.opacity = '0.5';
            moonIcon.style.opacity = '1';
        } else {
            sunIcon.style.opacity = '1';
            moonIcon.style.opacity = '0.5';
        }
    }
}

// إضافة انتقالات سلسة
function addThemeTransitions() {
    const style = document.createElement('style');
    style.textContent = `
        * {
            transition: background-color 0.3s ease, 
                       color 0.3s ease, 
                       border-color 0.3s ease,
                       box-shadow 0.3s ease !important;
        }
        
        .theme-toggle {
            transition: all 0.3s ease !important;
        }
    `;
    document.head.appendChild(style);
}

// إشعار تغيير الوضع
function showThemeNotification(theme) {
    const notification = document.createElement('div');
    notification.className = 'theme-notification';
    notification.innerHTML = `
        <i class="fas fa-${theme === 'dark' ? 'moon' : 'sun'}"></i>
        تم التبديل إلى الوضع ${theme === 'dark' ? 'الليلي' : 'النهاري'}
    `;
    
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: ${theme === 'dark' ? '#252837' : '#ffffff'};
        color: ${theme === 'dark' ? '#ffffff' : '#212529'};
        padding: 12px 24px;
        border-radius: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid ${theme === 'dark' ? '#3d4465' : '#dee2e6'};
        animation: slideDown 0.3s ease-out;
    `;
    
    // إضافة CSS للحركة
    if (!document.getElementById('theme-notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'theme-notification-styles';
        styles.textContent = `
            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateX(-50%) translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
            }
            
            @keyframes slideUp {
                from {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(-50%) translateY(-20px);
                }
            }
        `;
        document.head.appendChild(styles);
    }
    
    document.body.appendChild(notification);
    
    // إزالة الإشعار بعد 3 ثوان
    setTimeout(() => {
        notification.style.animation = 'slideUp 0.3s ease-out';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// مراقبة تفضيلات النظام
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'auto') {
        setTheme(e.matches ? 'dark' : 'light');
        updateThemeIcon(e.matches ? 'dark' : 'light');
    }
});

// تصدير الدوال للاستخدام العام
window.themeManager = {
    setTheme,
    toggleTheme,
    getCurrentTheme: () => document.documentElement.getAttribute('data-theme')
};

