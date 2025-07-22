<?php
// تعريف الأذونات والصلاحيات

$permissions_list = [
    'inventory' => [
        'inventory_view' => 'عرض المخزون',
        'inventory_add' => 'إضافة للمخزون',
        'inventory_edit' => 'تعديل المخزون',
        'inventory_delete' => 'حذف من المخزون'
    ],
    'production' => [
        'production_view' => 'عرض الإنتاج',
        'production_add' => 'إضافة إنتاج',
        'production_edit' => 'تعديل الإنتاج',
        'production_delete' => 'حذف إنتاج'
    ],
    'financial' => [
        'financial_view' => 'عرض المالية',
        'financial_add' => 'إضافة معاملة مالية',
        'financial_edit' => 'تعديل المعاملات المالية',
        'financial_delete' => 'حذف معاملة مالية'
    ],
    'hr' => [
        'hr_view' => 'عرض الموارد البشرية',
        'hr_add' => 'إضافة موظف',
        'hr_edit' => 'تعديل بيانات الموظفين',
        'hr_delete' => 'حذف موظف'
    ],
    'reports' => [
        'reports_view' => 'عرض التقارير',
        'reports_export' => 'تصدير التقارير'
    ],
    'system' => [
        'system_settings' => 'إعدادات النظام',
        'user_management' => 'إدارة المستخدمين',
        'backup_restore' => 'النسخ الاحتياطي والاستعادة'
    ]
];

// دالة الحصول على جميع الأذونات
function getAllPermissions() {
    global $permissions_list;
    $all_permissions = [];
    
    foreach ($permissions_list as $group => $permissions) {
        $all_permissions = array_merge($all_permissions, array_keys($permissions));
    }
    
    return $all_permissions;
}

// دالة الحصول على أذونات مجموعة معينة
function getGroupPermissions($group) {
    global $permissions_list;
    return $permissions_list[$group] ?? [];
}
?>