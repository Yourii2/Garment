// تفعيل القائمة الجانبية
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sidebar script loaded');
    
    // تحديد الرابط النشط
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('#sidebarMenu .nav-link');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href)) {
            link.classList.add('active');
            
            // فتح القائمة الفرعية إذا كان الرابط بداخلها
            const parentCollapse = link.closest('.collapse');
            if (parentCollapse) {
                parentCollapse.classList.add('show');
                const toggleButton = document.querySelector(`[data-bs-target="#${parentCollapse.id}"]`);
                if (toggleButton) {
                    toggleButton.setAttribute('aria-expanded', 'true');
                    toggleButton.classList.remove('collapsed');
                }
            }
        }
    });
});

// وظيفة تبديل القائمة الجانبية
function toggleSidebar() {
    console.log('Toggle sidebar called');
    const sidebar = document.getElementById('sidebarMenu');
    const mainContent = document.querySelector('.main-content');
    const backdrop = document.getElementById('sidebarBackdrop');
    
    if (!sidebar) {
        console.error('Sidebar element not found');
        return;
    }
    
    const isOpen = sidebar.classList.contains('show');
    console.log('Sidebar is open:', isOpen);
    
    if (isOpen) {
        closeSidebar();
    } else {
        openSidebar();
    }
}

function openSidebar() {
    console.log('Opening sidebar');
    const sidebar = document.getElementById('sidebarMenu');
    const mainContent = document.querySelector('.main-content');
    const backdrop = document.getElementById('sidebarBackdrop');
    
    if (sidebar) {
        sidebar.classList.add('show');
        console.log('Sidebar show class added');
    }
    
    if (window.innerWidth > 767 && mainContent) {
        mainContent.classList.add('sidebar-open');
    } else if (backdrop) {
        backdrop.classList.add('show');
    }
}

function closeSidebar() {
    console.log('Closing sidebar');
    const sidebar = document.getElementById('sidebarMenu');
    const mainContent = document.querySelector('.main-content');
    const backdrop = document.getElementById('sidebarBackdrop');
    
    if (sidebar) {
        sidebar.classList.remove('show');
    }
    if (mainContent) {
        mainContent.classList.remove('sidebar-open');
    }
    if (backdrop) {
        backdrop.classList.remove('show');
    }
}

// إغلاق القائمة عند تغيير حجم الشاشة
window.addEventListener('resize', function() {
    if (window.innerWidth > 767) {
        const backdrop = document.getElementById('sidebarBackdrop');
        if (backdrop) {
            backdrop.classList.remove('show');
        }
    }
});

// إغلاق القائمة عند النقر على رابط في الشاشات الصغيرة
document.addEventListener('click', function(event) {
    if (window.innerWidth <= 767) {
        const sidebar = document.getElementById('sidebarMenu');
        const clickedLink = event.target.closest('.nav-link');
        
        if (clickedLink && sidebar && sidebar.classList.contains('show') && 
            !clickedLink.hasAttribute('data-bs-toggle')) {
            closeSidebar();
        }
    }
});

// تصدير الوظائف للاستخدام العام
window.toggleSidebar = toggleSidebar;
window.openSidebar = openSidebar;
window.closeSidebar = closeSidebar;


