<?php
// تحديد الصفحة الحالية
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-2">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php'  ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    لوحة التحكم
                </a>
            </li>
        </ul>

        <!-- المخزن والخزينة -->
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>المخزن والخزينة</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#warehouseTreasurySubmenu" 
                   aria-expanded="false" aria-controls="warehouseTreasurySubmenu">
                    <i class="fas fa-warehouse me-2"></i>
                    المخزن والخزينة
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="warehouseTreasurySubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'warehouse/warehouses.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/warehouse/warehouses.php">
                                <i class="fas fa-building me-2"></i>إدارة المخازن
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'warehouse/treasuries.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/warehouse/treasuries.php">
                                <i class="fas fa-cash-register me-2"></i>إدارة الخزائن
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>مخزون المصنع</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#inventorySubmenu" 
                   aria-expanded="false" aria-controls="inventorySubmenu">
                    <i class="fas fa-warehouse me-2"></i>
                    إدارة المخزون
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="inventorySubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'inventory/fabrics.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/inventory/fabrics.php">
                                <i class="fas fa-cut me-2"></i>الأقمشة
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'inventory/fabric_types.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/inventory/fabric_types.php">
                                <i class="fas fa-list me-2"></i>أنواع الأقمشة
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'inventory/accessories.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/inventory/accessories.php">
                                <i class="fas fa-tools me-2"></i>الإكسسوارات
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'inventory/accessory_types.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/inventory/accessory_types.php">
                                <i class="fas fa-list me-2"></i>أنواع الإكسسوارات
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'inventory/invoices.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/inventory/invoices.php">
                                <i class="fas fa-file-invoice me-2"></i>فواتير المخزون
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>الإنتاج</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#productionSubmenu" 
                   aria-expanded="false" aria-controls="productionSubmenu">
                    <i class="fas fa-industry me-2"></i>
                    إدارة الإنتاج
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="productionSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'inventory/products.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/inventory/products.php">
                                <i class="fas fa-tshirt me-2"></i>منتجات التصنيع
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'production/manufacturing_stages.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/production/manufacturing_stages.php">
                                <i class="fas fa-cogs me-2"></i>مراحل التصنيع
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'production/cutting.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/production/cutting.php">
                                <i class="fas fa-cut me-2"></i>مرحلة قص القماش
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'production/stages.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/production/stages.php">
                                <i class="fas fa-tasks me-2"></i>مراحل الإنتاج
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'production/create_sales_invoice.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/production/create_sales_invoice.php">
                                <i class="fas fa-shipping-fast me-2"></i>إرسال للمبيعات
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>المبيعات</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#salesSubmenu" 
                   aria-expanded="false" aria-controls="salesSubmenu">
                    <i class="fas fa-shopping-cart me-2"></i>
                    المبيعات
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="salesSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'production/sales_invoices.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/production/sales_invoices.php">
                                <i class="fas fa-file-invoice me-2"></i>فواتير الإرسال
                            </a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'sales/inventory_management.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/sales/inventory_management.php">
                                <i class="fas fa-boxes me-2"></i>إدارة المنتجات
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'operations/orders.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/operations/orders.php">
                                <i class="fas fa-list-alt me-2"></i>إدارة الطلبيات
                            </a>
                        </li>                       
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'production/product_tracking.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/production/product_tracking.php">
                                <i class="fas fa-search me-2"></i>الكشف عن منتج
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
        <?php if (checkPermission('operations_management') || $_SESSION['role'] === 'admin'): ?>
        <!-- إدارة التشغيل -->
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>إدارة التشغيل</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#operationsSubmenu" 
                   aria-expanded="false" aria-controls="operationsSubmenu">
                    <i class="fas fa-shipping-fast me-2"></i>
                    إدارة التشغيل
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="operationsSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'operations/delivery_management.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/operations/delivery_management.php">
                                <i class="fas fa-truck me-2"></i>إدارة التوصيل
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'operations/delivery_status.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/operations/delivery_status.php">
                                <i class="fas fa-clipboard-check me-2"></i>حالة التوصيل
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
        <?php endif; ?>
        <?php if (checkPermission('hr_management') || $_SESSION['role'] === 'admin'): ?>
        <!-- الموارد البشرية -->
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>الموارد البشرية</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#hrSubmenu" 
                   aria-expanded="false" aria-controls="hrSubmenu">
                    <i class="fas fa-users me-2"></i>
                    الموارد البشرية
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="hrSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'hr/customers.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/hr/customers.php">
                                <i class="fas fa-user-tie me-2"></i>إدارة العملاء
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'hr/suppliers.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/hr/suppliers.php">
                                <i class="fas fa-truck me-2"></i>إدارة الموردين
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'hr/employees.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/hr/employees.php">
                                <i class="fas fa-user-friends me-2"></i>إدارة الموظفين
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'hr/workers.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/hr/workers.php">
                                <i class="fas fa-hard-hat me-2"></i>إدارة العمال
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'hr/representatives.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/hr/representatives.php">
                                <i class="fas fa-user-friends me-2"></i>إدارة المناديب
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
        <?php endif; ?>

        <?php if (checkPermission('financial_management') || $_SESSION['role'] === 'admin'): ?>
        <!-- الإدارة المالية -->
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>الإدارة المالية</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#financialSubmenu" 
                   aria-expanded="false" aria-controls="financialSubmenu">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    الإدارة المالية
                    <i class="fas fa-chevron-down ms-auto"></i>
                </a>
                <div class="collapse" id="financialSubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'financial/expenses.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/financial/expenses.php">
                                <i class="fas fa-receipt me-2"></i>المصروفات
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'financial/deposits.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/financial/deposits.php">
                                <i class="fas fa-plus-circle me-2"></i>إيداع في الخزينة
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'financial/customer_collections.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/financial/customer_collections.php">
                                <i class="fas fa-hand-holding-usd me-2"></i>تحصيل من العملاء
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'financial/supplier_payments.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/financial/supplier_payments.php">
                                <i class="fas fa-truck me-2"></i>دفعات الموردين
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'financial/employee_withdrawals.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/financial/employee_withdrawals.php">
                                <i class="fas fa-user-tie me-2"></i>مسحوبات الموظفين
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'financial/worker_withdrawals.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/financial/worker_withdrawals.php">
                                <i class="fas fa-hard-hat me-2"></i>مسحوبات العمال
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'financial/representative_withdrawals.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/financial/representative_withdrawals.php">
                                <i class="fas fa-user-friends me-2"></i>مسحوبات المناديب
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'financial/treasury_transfer.php') !== false ? 'active' : '' ?>" 
                               href="<?= BASE_URL ?>/financial/treasury_transfer.php">
                                <i class="fas fa-exchange-alt me-2"></i>تحويل بين الخزائن
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'admin'): ?>
        <!-- إدارة النظام -->
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>إدارة النظام</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'admin/users.php') !== false ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/users.php">
                    <i class="fas fa-users-cog me-2"></i>
                    إدارة المستخدمين
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'admin/settings.php') !== false ? 'active' : '' ?>" 
                   href="<?= BASE_URL ?>/admin/settings.php">
                    <i class="fas fa-cogs me-2"></i>
                    إعدادات النظام
                </a>
            </li>
        </ul>
        <?php endif; ?>

        <!-- إضافة قائمة التقارير المنسدلة الشاملة -->
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-chart-bar me-2"></i>التقارير
            </a>
            <ul class="dropdown-menu dropdown-menu-lg" aria-labelledby="reportsDropdown" style="max-height: 400px; overflow-y: auto;">
                <!-- تقارير المخزون -->
                <li><h6 class="dropdown-header text-primary">تقارير المخزون</h6></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/inventory_summary.php">ملخص المخزون</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/fabric_report.php">تقرير الأقمشة</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/low_stock_report.php">المخزون المنخفض</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- التقارير المالية -->
                <li><h6 class="dropdown-header text-success">التقارير المالية</h6></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/treasury_report.php">تقرير الخزائن</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/profits_report.php">تقرير الأرباح حسب التاريخ</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/customer_collections_report.php">تحصيلات العملاء</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/expenses_report.php">المصروفات</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- تقارير المبيعات -->
                <li><h6 class="dropdown-header text-info">تقارير المبيعات</h6></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/sales_summary.php">ملخص المبيعات</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/orders_report.php">تقرير الطلبيات</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/sales_reps_report.php">تقرير المناديب</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- تقارير الإنتاج -->
                <li><h6 class="dropdown-header text-warning">تقارير الإنتاج</h6></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/production_summary.php">ملخص الإنتاج</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/cutting_orders_report.php">أوامر التقطيع</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/quality_report.php">تقرير الجودة</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- تقارير الجودة والمراقبة -->
                <li><h6 class="dropdown-header text-danger">تقارير الجودة والمراقبة</h6></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/production_defects_report.php">عيوب الإنتاج</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/quality_control_report.php">مراقبة الجودة</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- تقارير التكاليف والربحية -->
                <li><h6 class="dropdown-header" style="color: #6f42c1;">تقارير التكاليف والربحية</h6></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/production_cost_report.php">تكلفة الإنتاج</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/profit_margin_report.php">هامش الربح</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- تقارير الأداء والكفاءة -->
                <li><h6 class="dropdown-header" style="color: #20c997;">تقارير الأداء والكفاءة</h6></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/production_efficiency_report.php">كفاءة الإنتاج</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/kpi_report.php">مؤشرات الأداء</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- تقارير التخطيط والتنبؤ -->
                <li><h6 class="dropdown-header" style="color: #6610f2;">تقارير التخطيط والتنبؤ</h6></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/sales_forecast_report.php">التنبؤ بالمبيعات</a></li>
                <li><a class="dropdown-item" href="<?= BASE_URL ?>/reports/production_planning_report.php">التخطيط للإنتاج</a></li>
                <li><hr class="dropdown-divider"></li>
                
                <!-- عرض جميع التقارير -->
                <li><a class="dropdown-item fw-bold text-center" href="<?= BASE_URL ?>/reports/index.php">
                    <i class="fas fa-list me-2"></i>جميع التقارير (<?= count(glob('../reports/*.php')) ?> تقرير)
                </a></li>
            </ul>
        </li>

        
    </div>
</nav>

<style>
.nav-link.collapsed .fa-chevron-down {
    transform: rotate(-90deg);
    transition: transform 0.2s ease;
}

.nav-link:not(.collapsed) .fa-chevron-down {
    transform: rotate(0deg);
    transition: transform 0.2s ease;
}

.collapse .nav-link {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.collapse .nav-link:hover {
    background-color: rgba(0,0,0,0.05);
}

.collapse .nav-link.active {
    background-color: #007bff;
    color: white;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // فتح القائمة المنسدلة التي تحتوي على الصفحة النشطة
    const activeLink = document.querySelector('.collapse .nav-link.active');
    if (activeLink) {
        const parentCollapse = activeLink.closest('.collapse');
        if (parentCollapse) {
            parentCollapse.classList.add('show');
            const toggleButton = document.querySelector(`[data-bs-target="#${parentCollapse.id}"]`);
            if (toggleButton) {
                toggleButton.classList.remove('collapsed');
                toggleButton.setAttribute('aria-expanded', 'true');
            }
        }
    }
});
</script>






















