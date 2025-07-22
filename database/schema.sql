-- قاعدة بيانات نظام إدارة مصنع الملابس
CREATE DATABASE garment_factory_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE garment_factory_system;

-- جدول المستخدمين والصلاحيات
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    role ENUM('admin', 'supervisor', 'accountant', 'worker', 'sales_rep', 'limited_user') NOT NULL,
    permissions JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول الفروع والمخازن
CREATE TABLE branches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('factory', 'warehouse', 'sales_office') NOT NULL,
    address TEXT,
    manager_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(id)
);

-- جدول أنواع الأقمشة
CREATE TABLE fabric_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    type VARCHAR(50),
    color VARCHAR(50),
    unit VARCHAR(20) DEFAULT 'متر',
    cost_per_unit DECIMAL(10,2),
    current_quantity DECIMAL(10,2) DEFAULT 0,
    min_quantity DECIMAL(10,2) DEFAULT 0,
    branch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- جدول حركات المخزون
CREATE TABLE IF NOT EXISTS inventory_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fabric_id INT NULL,
    accessory_id INT NULL,
    movement_type ENUM('in', 'out') NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_cost DECIMAL(10,2) DEFAULT 0,
    total_cost DECIMAL(10,2) DEFAULT 0,
    reference_type VARCHAR(50),
    reference_id INT,
    notes TEXT,
    user_id INT,
    branch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fabric_id) REFERENCES fabric_types(id),
    FOREIGN KEY (accessory_id) REFERENCES accessories(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- جدول الإكسسوارات
CREATE TABLE IF NOT EXISTS accessories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    type VARCHAR(50),
    unit VARCHAR(20) DEFAULT 'قطعة',
    cost_per_unit DECIMAL(10,2),
    current_quantity DECIMAL(10,2) DEFAULT 0,
    min_quantity DECIMAL(10,2) DEFAULT 0,
    branch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- جدول أنواع الإكسسوارات
CREATE TABLE IF NOT EXISTS accessory_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- إدراج بيانات أولية لأنواع الإكسسوارات
INSERT INTO accessory_types (name, description) VALUES 
('أزرار', 'جميع أنواع الأزرار المختلفة'),
('سحابات', 'السحابات والسوست'),
('خيوط', 'خيوط الحياكة والتطريز'),
('شرائط', 'الشرائط والأربطة'),
('دانتيل', 'الدانتيل والكروشيه'),
('كلف', 'الكلف والزخارف'),
('أحجار', 'الأحجار والخرز'),
('أخرى', 'أنواع أخرى من الإكسسوارات');

-- جدول المقاسات
CREATE TABLE sizes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
);

-- جدول مراحل التصنيع
CREATE TABLE manufacturing_stages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_paid BOOLEAN DEFAULT TRUE,
    cost_per_unit DECIMAL(10,2) DEFAULT 0,
    estimated_time_minutes INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE
);

-- جدول المنتجات
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    category VARCHAR(50),
    fabric_consumption DECIMAL(10,2),
    estimated_cost DECIMAL(10,2),
    selling_price DECIMAL(10,2),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول مراحل المنتج
CREATE TABLE product_stages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    stage_id INT,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (stage_id) REFERENCES manufacturing_stages(id)
);

-- جدول إكسسوارات المنتج
CREATE TABLE product_accessories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    accessory_id INT,
    quantity_needed DECIMAL(10,2),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (accessory_id) REFERENCES accessories(id)
);

-- جدول أوامر الإنتاج
CREATE TABLE production_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    product_id INT,
    total_quantity INT,
    fabric_id INT,
    fabric_quantity_used DECIMAL(10,2),
    fabric_cost_per_unit DECIMAL(10,2),
    status ENUM('cutting', 'manufacturing', 'completed', 'cancelled') DEFAULT 'cutting',
    start_date DATE,
    target_completion_date DATE,
    actual_completion_date DATE,
    created_by INT,
    branch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (fabric_id) REFERENCES fabric_types(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- جدول توزيع المقاسات في أوامر الإنتاج
CREATE TABLE production_order_sizes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    production_order_id INT,
    size_id INT,
    quantity INT,
    completed_quantity INT DEFAULT 0,
    FOREIGN KEY (production_order_id) REFERENCES production_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (size_id) REFERENCES sizes(id)
);

-- جدول مراحل الإنتاج الفعلية
CREATE TABLE production_stage_records (
    id INT PRIMARY KEY AUTO_INCREMENT,
    production_order_id INT,
    stage_id INT,
    worker_id INT,
    quantity_assigned INT,
    quantity_completed INT DEFAULT 0,
    quantity_defective INT DEFAULT 0,
    assigned_at TIMESTAMP,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    cost_per_unit DECIMAL(10,2),
    total_cost DECIMAL(10,2),
    notes TEXT,
    status ENUM('assigned', 'in_progress', 'completed', 'on_hold') DEFAULT 'assigned',
    FOREIGN KEY (production_order_id) REFERENCES production_orders(id),
    FOREIGN KEY (stage_id) REFERENCES manufacturing_stages(id),
    FOREIGN KEY (worker_id) REFERENCES users(id)
);

-- جدول المخزون النهائي
CREATE TABLE finished_inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    production_order_id INT,
    size_id INT,
    quantity INT,
    unit_cost DECIMAL(10,2),
    qr_code VARCHAR(100) UNIQUE,
    status ENUM('in_stock', 'shipped', 'sold', 'returned') DEFAULT 'in_stock',
    branch_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (production_order_id) REFERENCES production_orders(id),
    FOREIGN KEY (size_id) REFERENCES sizes(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- جدول العملاء
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    company_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    credit_limit DECIMAL(12,2) DEFAULT 0,
    current_balance DECIMAL(12,2) DEFAULT 0,
    customer_type ENUM('individual', 'company', 'retailer', 'wholesaler') DEFAULT 'individual',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الموردين
CREATE TABLE suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    company_name VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    current_balance DECIMAL(12,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول الموظفين والعمال
CREATE TABLE employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    employee_code VARCHAR(50) UNIQUE NOT NULL,
    department VARCHAR(50),
    position VARCHAR(50),
    salary_type ENUM('daily', 'weekly', 'monthly', 'per_piece') NOT NULL,
    base_salary DECIMAL(10,2),
    piece_rate DECIMAL(10,2),
    hire_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    branch_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- جدول الحضور والانصراف
CREATE TABLE attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT,
    date DATE,
    check_in TIME,
    check_out TIME,
    total_hours DECIMAL(4,2),
    overtime_hours DECIMAL(4,2) DEFAULT 0,
    status ENUM('present', 'absent', 'late', 'half_day') DEFAULT 'present',
    notes TEXT,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    UNIQUE KEY unique_employee_date (employee_id, date)
);

-- جدول المسحوبات
CREATE TABLE employee_advances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT,
    amount DECIMAL(10,2),
    date DATE,
    description TEXT,
    approved_by INT,
    status ENUM('pending', 'approved', 'deducted') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- جدول الخزن المالية
CREATE TABLE cash_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('cash', 'bank', 'safe') NOT NULL,
    current_balance DECIMAL(12,2) DEFAULT 0,
    branch_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

-- جدول المعاملات المالية
CREATE TABLE financial_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_number VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('income', 'expense', 'transfer') NOT NULL,
    category VARCHAR(50),
    amount DECIMAL(12,2) NOT NULL,
    from_account_id INT,
    to_account_id INT,
    reference_type VARCHAR(50),
    reference_id INT,
    description TEXT,
    transaction_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_account_id) REFERENCES cash_accounts(id),
    FOREIGN KEY (to_account_id) REFERENCES cash_accounts(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- جدول سجل النشاط
CREATE TABLE activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- جدول عمليات القص
CREATE TABLE cutting_operations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cutting_number VARCHAR(50) UNIQUE NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    fabric_consumption_per_unit DECIMAL(10,2) NOT NULL,
    total_fabric_used DECIMAL(10,2) NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    notes TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- إدراج بيانات أولية
INSERT INTO users (username, password, full_name, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام', 'admin');

INSERT INTO sizes (name, code, sort_order) VALUES 
('صغير', 'S', 1), ('متوسط', 'M', 2), ('كبير', 'L', 3), ('كبير/<', 'XL', 4), ('كبير/<', 'XXL', 5);

INSERT INTO manufacturing_stages (name, description, is_paid, sort_order) VALUES 
('القص', 'قص القماش حسب الباترون', FALSE, 1),
('الحياكة', 'حياكة القطع الأساسية', TRUE, 2),
('التطريز', 'إضافة التطريز والزخارف', TRUE, 3),
('التشطيب', 'التشطيب النهائي والكي', TRUE, 4),
('التعبئة', 'تعبئة المنتج النهائي', FALSE, 5);



