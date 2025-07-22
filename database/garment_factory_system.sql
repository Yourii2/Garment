/*
 Navicat Premium Dump SQL

 Source Server         : Dragon
 Source Server Type    : MySQL
 Source Server Version : 50718 (5.7.18-log)
 Source Host           : localhost:3306
 Source Schema         : garment_factory_system

 Target Server Type    : MySQL
 Target Server Version : 50718 (5.7.18-log)
 File Encoding         : 65001

 Date: 19/07/2025 23:41:13
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for accessories
-- ----------------------------
DROP TABLE IF EXISTS `accessories`;
CREATE TABLE `accessories`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `unit` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `cost_per_unit` decimal(10, 2) NULL DEFAULT NULL,
  `current_quantity` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `min_quantity` decimal(10, 2) NULL DEFAULT 0.00,
  `branch_id` int(11) NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE,
  INDEX `branch_id`(`branch_id`) USING BTREE,
  CONSTRAINT `accessories_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of accessories
-- ----------------------------
INSERT INTO `accessories` VALUES (1, 'قماش جديد', 'A1', 'أزرار', 'قطعة', 1.00, 2405.00, 0.00, 1, '2025-07-18 20:16:02');

-- ----------------------------
-- Table structure for accessory_types
-- ----------------------------
DROP TABLE IF EXISTS `accessory_types`;
CREATE TABLE `accessory_types`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of accessory_types
-- ----------------------------
INSERT INTO `accessory_types` VALUES (1, 'أزرار', 'جميع أنواع الأزرار المختلفة', '2025-07-19 06:26:13');
INSERT INTO `accessory_types` VALUES (2, 'سحابات', 'السحابات والسوست', '2025-07-19 06:26:13');
INSERT INTO `accessory_types` VALUES (3, 'خيوط', 'خيوط الحياكة والتطريز', '2025-07-19 06:26:13');
INSERT INTO `accessory_types` VALUES (4, 'شرائط', 'الشرائط والأربطة2', '2025-07-19 06:26:13');
INSERT INTO `accessory_types` VALUES (5, 'دانتيل', 'الدانتيل والكروشيه', '2025-07-19 06:26:13');
INSERT INTO `accessory_types` VALUES (7, 'أحجار', 'الأحجار والخرز', '2025-07-19 06:26:13');
INSERT INTO `accessory_types` VALUES (8, 'أخرى', 'أنواع أخرى من الإكسسوارات', '2025-07-19 06:26:13');

-- ----------------------------
-- Table structure for activity_log
-- ----------------------------
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `record_id` int(11) NULL DEFAULT NULL,
  `old_values` json NULL,
  `new_values` json NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of activity_log
-- ----------------------------
INSERT INTO `activity_log` VALUES (1, 1, 'تسجيل دخول', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-17 19:35:51');
INSERT INTO `activity_log` VALUES (2, 1, 'تسجيل دخول', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-17 22:42:11');
INSERT INTO `activity_log` VALUES (3, 1, 'تسجيل دخول', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-17 22:55:58');
INSERT INTO `activity_log` VALUES (4, 1, 'تسجيل دخول', NULL, NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-18 02:44:41');

-- ----------------------------
-- Table structure for attendance
-- ----------------------------
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NULL DEFAULT NULL,
  `date` date NULL DEFAULT NULL,
  `check_in` time NULL DEFAULT NULL,
  `check_out` time NULL DEFAULT NULL,
  `total_hours` decimal(4, 2) NULL DEFAULT NULL,
  `overtime_hours` decimal(4, 2) NULL DEFAULT 0.00,
  `status` enum('present','absent','late','half_day') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'present',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unique_employee_date`(`employee_id`, `date`) USING BTREE,
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of attendance
-- ----------------------------

-- ----------------------------
-- Table structure for branches
-- ----------------------------
DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('factory','warehouse','sales_office') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `manager_id` int(11) NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `manager` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `manager_id`(`manager_id`) USING BTREE,
  CONSTRAINT `branches_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of branches
-- ----------------------------
INSERT INTO `branches` VALUES (1, 'مخزن جديد', 'factory', NULL, 1, '2025-07-19 02:36:19', 'المحل', 'ممدوح', '01150006289');

-- ----------------------------
-- Table structure for cash_accounts
-- ----------------------------
DROP TABLE IF EXISTS `cash_accounts`;
CREATE TABLE `cash_accounts`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('cash','bank','safe') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `current_balance` decimal(12, 2) NULL DEFAULT 0.00,
  `branch_id` int(11) NULL DEFAULT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `branch_id`(`branch_id`) USING BTREE,
  CONSTRAINT `cash_accounts_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cash_accounts
-- ----------------------------

-- ----------------------------
-- Table structure for customers
-- ----------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `credit_limit` decimal(12, 2) NULL DEFAULT 0.00,
  `current_balance` decimal(12, 2) NULL DEFAULT 0.00,
  `customer_type` enum('individual','company','retailer','wholesaler') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'individual',
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of customers
-- ----------------------------

-- ----------------------------
-- Table structure for employee_advances
-- ----------------------------
DROP TABLE IF EXISTS `employee_advances`;
CREATE TABLE `employee_advances`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NULL DEFAULT NULL,
  `amount` decimal(10, 2) NULL DEFAULT NULL,
  `date` date NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `approved_by` int(11) NULL DEFAULT NULL,
  `status` enum('pending','approved','deducted') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `employee_id`(`employee_id`) USING BTREE,
  INDEX `approved_by`(`approved_by`) USING BTREE,
  CONSTRAINT `employee_advances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `employee_advances_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of employee_advances
-- ----------------------------

-- ----------------------------
-- Table structure for employees
-- ----------------------------
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NULL DEFAULT NULL,
  `employee_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `position` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `salary_type` enum('daily','weekly','monthly','per_piece') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_salary` decimal(10, 2) NULL DEFAULT NULL,
  `piece_rate` decimal(10, 2) NULL DEFAULT NULL,
  `hire_date` date NULL DEFAULT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `branch_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `employee_code`(`employee_code`) USING BTREE,
  UNIQUE INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `branch_id`(`branch_id`) USING BTREE,
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of employees
-- ----------------------------

-- ----------------------------
-- Table structure for fabric_categories
-- ----------------------------
DROP TABLE IF EXISTS `fabric_categories`;
CREATE TABLE `fabric_categories`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of fabric_categories
-- ----------------------------
INSERT INTO `fabric_categories` VALUES (1, 'قطن', 'أقمشة القطن الطبيعية', '2025-07-19 06:31:42');
INSERT INTO `fabric_categories` VALUES (2, 'حرير', 'أقمشة الحرير الطبيعي والصناعي', '2025-07-19 06:31:42');
INSERT INTO `fabric_categories` VALUES (3, 'صوف', 'أقمشة الصوف والخامات الدافئة', '2025-07-19 06:31:42');
INSERT INTO `fabric_categories` VALUES (4, 'كتان', 'أقمشة الكتان الطبيعية 2', '2025-07-19 06:31:42');
INSERT INTO `fabric_categories` VALUES (5, 'بوليستر', 'الأقمشة الصناعية', '2025-07-19 06:31:42');
INSERT INTO `fabric_categories` VALUES (7, 'دانتيل', 'أقمشة الدانتيل والتول', '2025-07-19 06:31:42');
INSERT INTO `fabric_categories` VALUES (8, 'جينز', 'أقمشة الجينز والدنيم', '2025-07-19 06:31:42');
INSERT INTO `fabric_categories` VALUES (9, 'شيفون', 'أقمشة الشيفون الخفيفة', '2025-07-19 06:31:42');
INSERT INTO `fabric_categories` VALUES (10, 'ساتان', 'أقمشة الساتان اللامعة', '2025-07-19 06:31:42');

-- ----------------------------
-- Table structure for fabric_types
-- ----------------------------
DROP TABLE IF EXISTS `fabric_types`;
CREATE TABLE `fabric_types`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `unit` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'متر',
  `cost_per_unit` decimal(10, 2) NULL DEFAULT NULL,
  `current_quantity` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `min_quantity` decimal(10, 2) NULL DEFAULT 0.00,
  `branch_id` int(11) NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE,
  INDEX `branch_id`(`branch_id`) USING BTREE,
  CONSTRAINT `fabric_types_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of fabric_types
-- ----------------------------
INSERT INTO `fabric_types` VALUES (2, 'قماش تجريبي', 'F2', 'اي نوع', 'اسود', 'متر', 10.00, 501.00, 1.00, 1, '2025-07-18 03:04:52');
INSERT INTO `fabric_types` VALUES (3, 'قماش تجريبي', 'F3', 'حاجه جديده', 'اسود', 'متر', 10.00, 102.00, 1.00, 1, '2025-07-18 03:04:57');
INSERT INTO `fabric_types` VALUES (4, 'قماش تجريبي', 'F4', 'اي نوع', 'اسود', 'متر', 10.00, 103.00, 1.00, 1, '2025-07-18 03:05:00');
INSERT INTO `fabric_types` VALUES (6, 'قماش مختلف', 'F6', 'حرير', 'ابيض', 'متر', 10.00, 104.00, 50.00, 1, '2025-07-18 19:02:31');

-- ----------------------------
-- Table structure for financial_transactions
-- ----------------------------
DROP TABLE IF EXISTS `financial_transactions`;
CREATE TABLE `financial_transactions`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('income','expense','transfer') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `amount` decimal(12, 2) NOT NULL,
  `from_account_id` int(11) NULL DEFAULT NULL,
  `to_account_id` int(11) NULL DEFAULT NULL,
  `reference_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `reference_id` int(11) NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `transaction_date` date NULL DEFAULT NULL,
  `created_by` int(11) NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `transaction_number`(`transaction_number`) USING BTREE,
  INDEX `from_account_id`(`from_account_id`) USING BTREE,
  INDEX `to_account_id`(`to_account_id`) USING BTREE,
  INDEX `created_by`(`created_by`) USING BTREE,
  CONSTRAINT `financial_transactions_ibfk_1` FOREIGN KEY (`from_account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `financial_transactions_ibfk_2` FOREIGN KEY (`to_account_id`) REFERENCES `cash_accounts` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `financial_transactions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of financial_transactions
-- ----------------------------

-- ----------------------------
-- Table structure for finished_inventory
-- ----------------------------
DROP TABLE IF EXISTS `finished_inventory`;
CREATE TABLE `finished_inventory`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NULL DEFAULT NULL,
  `production_order_id` int(11) NULL DEFAULT NULL,
  `size_id` int(11) NULL DEFAULT NULL,
  `quantity` int(11) NULL DEFAULT NULL,
  `unit_cost` decimal(10, 2) NULL DEFAULT NULL,
  `qr_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('in_stock','shipped','sold','returned') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'in_stock',
  `branch_id` int(11) NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `qr_code`(`qr_code`) USING BTREE,
  INDEX `product_id`(`product_id`) USING BTREE,
  INDEX `production_order_id`(`production_order_id`) USING BTREE,
  INDEX `size_id`(`size_id`) USING BTREE,
  INDEX `branch_id`(`branch_id`) USING BTREE,
  CONSTRAINT `finished_inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `finished_inventory_ibfk_2` FOREIGN KEY (`production_order_id`) REFERENCES `production_orders` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `finished_inventory_ibfk_3` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `finished_inventory_ibfk_4` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of finished_inventory
-- ----------------------------

-- ----------------------------
-- Table structure for inventory_invoice_items
-- ----------------------------
DROP TABLE IF EXISTS `inventory_invoice_items`;
CREATE TABLE `inventory_invoice_items`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NULL DEFAULT NULL,
  `fabric_id` int(11) NULL DEFAULT NULL,
  `accessory_id` int(11) NULL DEFAULT NULL,
  `quantity` decimal(10, 2) NOT NULL,
  `unit_cost` decimal(10, 2) NULL DEFAULT 0.00,
  `total_cost` decimal(10, 2) NULL DEFAULT 0.00,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `invoice_id`(`invoice_id`) USING BTREE,
  INDEX `fabric_id`(`fabric_id`) USING BTREE,
  INDEX `accessory_id`(`accessory_id`) USING BTREE,
  CONSTRAINT `inventory_invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `inventory_invoices` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `inventory_invoice_items_ibfk_2` FOREIGN KEY (`fabric_id`) REFERENCES `fabric_types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `inventory_invoice_items_ibfk_3` FOREIGN KEY (`accessory_id`) REFERENCES `accessories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of inventory_invoice_items
-- ----------------------------
INSERT INTO `inventory_invoice_items` VALUES (1, 1, 2, NULL, 100.00, 10.00, 1000.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (2, 1, NULL, 1, 100.00, 5.00, 500.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (3, 2, NULL, 1, 1000.00, 5.00, 5000.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (4, 3, 2, NULL, 100.00, 10.00, 1000.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (5, 3, NULL, 1, 100.00, 10.00, 1000.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (6, 4, 2, NULL, 100.00, 10.00, 1000.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (7, 4, NULL, 1, 100.00, 10.00, 1000.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (8, 5, 2, NULL, 100.00, 10.00, 1000.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (9, 5, NULL, 1, 1000.00, 10.00, 10000.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (10, 6, 2, NULL, 101.00, 10.00, 1010.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (11, 6, 3, NULL, 102.00, 10.00, 1020.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (12, 6, 4, NULL, 103.00, 10.00, 1030.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (13, 6, 6, NULL, 104.00, 10.00, 1040.00, NULL);
INSERT INTO `inventory_invoice_items` VALUES (14, 6, NULL, 1, 105.00, 10.00, 1050.00, NULL);

-- ----------------------------
-- Table structure for inventory_invoices
-- ----------------------------
DROP TABLE IF EXISTS `inventory_invoices`;
CREATE TABLE `inventory_invoices`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_type` enum('purchase','return','damage') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` int(11) NULL DEFAULT NULL,
  `branch_id` int(11) NULL DEFAULT NULL,
  `user_id` int(11) NULL DEFAULT NULL,
  `total_amount` decimal(12, 2) NULL DEFAULT 0.00,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `invoice_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `invoice_number`(`invoice_number`) USING BTREE,
  INDEX `supplier_id`(`supplier_id`) USING BTREE,
  INDEX `branch_id`(`branch_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  CONSTRAINT `inventory_invoices_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `inventory_invoices_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `inventory_invoices_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of inventory_invoices
-- ----------------------------
INSERT INTO `inventory_invoices` VALUES (1, '20250212201', 'purchase', 1, 1, 1, 1500.00, '', '2025-07-19', '2025-07-19 02:41:05');
INSERT INTO `inventory_invoices` VALUES (2, 'INV-2025-0002', 'purchase', 1, 1, 1, 5000.00, '', '2025-07-19', '2025-07-19 21:31:51');
INSERT INTO `inventory_invoices` VALUES (3, 'INV-2025-0003', 'purchase', 1, 1, 1, 2000.00, '', '2025-07-19', '2025-07-19 21:56:33');
INSERT INTO `inventory_invoices` VALUES (4, 'INV-2025-0004', 'purchase', 1, 1, 1, 2000.00, '', '2025-07-19', '2025-07-19 22:01:32');
INSERT INTO `inventory_invoices` VALUES (5, 'INV-2025-0005', 'purchase', 1, 1, 1, 11000.00, '11123123123123', '2025-07-19', '2025-07-19 22:06:06');
INSERT INTO `inventory_invoices` VALUES (6, 'INV-2025-0006', 'purchase', 1, 1, 1, 5150.00, '', '2025-07-19', '2025-07-19 22:40:48');

-- ----------------------------
-- Table structure for inventory_movements
-- ----------------------------
DROP TABLE IF EXISTS `inventory_movements`;
CREATE TABLE `inventory_movements`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fabric_id` int(11) NULL DEFAULT NULL,
  `accessory_id` int(11) NULL DEFAULT NULL,
  `movement_type` enum('in','out','transfer','adjustment') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10, 2) NOT NULL,
  `unit_cost` decimal(10, 2) NULL DEFAULT NULL,
  `total_cost` decimal(10, 2) NULL DEFAULT NULL,
  `reference_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `reference_id` int(11) NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `user_id` int(11) NULL DEFAULT NULL,
  `branch_id` int(11) NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `fabric_id`(`fabric_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `branch_id`(`branch_id`) USING BTREE,
  INDEX `accessory_id`(`accessory_id`) USING BTREE,
  CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`fabric_id`) REFERENCES `fabric_types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `inventory_movements_ibfk_3` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `inventory_movements_ibfk_4` FOREIGN KEY (`accessory_id`) REFERENCES `accessories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of inventory_movements
-- ----------------------------
INSERT INTO `inventory_movements` VALUES (1, 2, NULL, 'in', 100.00, 10.00, 1000.00, 'invoice', 1, NULL, 1, 1, '2025-07-19 02:41:05');
INSERT INTO `inventory_movements` VALUES (2, NULL, 1, 'in', 100.00, 5.00, 500.00, 'invoice', 1, NULL, 1, 1, '2025-07-19 02:41:05');
INSERT INTO `inventory_movements` VALUES (3, NULL, 1, 'in', 1000.00, 5.00, 5000.00, 'invoice', 2, NULL, 1, 1, '2025-07-19 21:31:51');
INSERT INTO `inventory_movements` VALUES (4, 2, NULL, 'in', 100.00, 10.00, 1000.00, 'invoice', 3, NULL, 1, 1, '2025-07-19 21:56:33');
INSERT INTO `inventory_movements` VALUES (5, NULL, 1, 'in', 100.00, 10.00, 1000.00, 'invoice', 3, NULL, 1, 1, '2025-07-19 21:56:33');
INSERT INTO `inventory_movements` VALUES (6, 2, NULL, 'in', 100.00, 10.00, 1000.00, 'invoice', 4, NULL, 1, 1, '2025-07-19 22:01:32');
INSERT INTO `inventory_movements` VALUES (7, NULL, 1, 'in', 100.00, 10.00, 1000.00, 'invoice', 4, NULL, 1, 1, '2025-07-19 22:01:32');

-- ----------------------------
-- Table structure for manufacturing_stages
-- ----------------------------
DROP TABLE IF EXISTS `manufacturing_stages`;
CREATE TABLE `manufacturing_stages`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `is_paid` tinyint(1) NULL DEFAULT 1,
  `cost_per_unit` decimal(10, 2) NULL DEFAULT 0.00,
  `estimated_time_minutes` int(11) NULL DEFAULT 0,
  `sort_order` int(11) NULL DEFAULT 0,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of manufacturing_stages
-- ----------------------------
INSERT INTO `manufacturing_stages` VALUES (1, 'القص', 'قص القماش حسب الباترون', 0, 0.00, 0, 1, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (2, 'الحياكة', 'حياكة القطع الأساسية', 1, 0.00, 0, 2, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (3, 'التطريز', 'إضافة التطريز والزخارف', 1, 0.00, 0, 3, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (4, 'التشطيب', 'التشطيب النهائي والكي', 1, 0.00, 0, 4, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (5, 'التعبئة', 'تعبئة المنتج النهائي', 0, 0.00, 0, 5, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (6, 'القص', 'قص القماش حسب الباترون', 0, 0.00, 0, 1, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (7, 'الحياكة', 'حياكة القطع الأساسية', 1, 0.00, 0, 2, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (8, 'التطريز', 'إضافة التطريز والزخارف', 1, 0.00, 0, 3, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (9, 'التشطيب', 'التشطيب النهائي والكي', 1, 0.00, 0, 4, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (10, 'التعبئة', 'تعبئة المنتج النهائي', 0, 0.00, 0, 5, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (11, 'القص', 'قص القماش حسب الباترون', 0, 0.00, 0, 1, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (12, 'الحياكة', 'حياكة القطع الأساسية', 1, 0.00, 0, 2, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (13, 'التطريز', 'إضافة التطريز والزخارف', 1, 0.00, 0, 3, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (14, 'التشطيب', 'التشطيب النهائي والكي', 1, 0.00, 0, 4, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (15, 'التعبئة', 'تعبئة المنتج النهائي', 0, 0.00, 0, 5, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (16, 'القص', 'قص القماش حسب الباترون', 0, 0.00, 0, 1, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (17, 'الحياكة', 'حياكة القطع الأساسية', 1, 0.00, 0, 2, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (18, 'التطريز', 'إضافة التطريز والزخارف', 1, 0.00, 0, 3, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (19, 'التشطيب', 'التشطيب النهائي والكي', 1, 0.00, 0, 4, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (20, 'التعبئة', 'تعبئة المنتج النهائي', 0, 0.00, 0, 5, 1, '2025-07-19 07:07:19', '2025-07-19 07:07:19');
INSERT INTO `manufacturing_stages` VALUES (21, 'مرحله تصنيع جديده', '123123', 0, 0.00, 0, 0, 1, '2025-07-19 20:15:08', '2025-07-19 20:15:29');

-- ----------------------------
-- Table structure for product_accessories
-- ----------------------------
DROP TABLE IF EXISTS `product_accessories`;
CREATE TABLE `product_accessories`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NULL DEFAULT NULL,
  `accessory_id` int(11) NULL DEFAULT NULL,
  `quantity_needed` decimal(10, 2) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `product_id`(`product_id`) USING BTREE,
  INDEX `accessory_id`(`accessory_id`) USING BTREE,
  CONSTRAINT `product_accessories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `product_accessories_ibfk_2` FOREIGN KEY (`accessory_id`) REFERENCES `accessories` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of product_accessories
-- ----------------------------

-- ----------------------------
-- Table structure for product_stages
-- ----------------------------
DROP TABLE IF EXISTS `product_stages`;
CREATE TABLE `product_stages`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NULL DEFAULT NULL,
  `stage_id` int(11) NULL DEFAULT NULL,
  `sort_order` int(11) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `product_id`(`product_id`) USING BTREE,
  INDEX `stage_id`(`stage_id`) USING BTREE,
  CONSTRAINT `product_stages_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `product_stages_ibfk_2` FOREIGN KEY (`stage_id`) REFERENCES `manufacturing_stages` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of product_stages
-- ----------------------------
INSERT INTO `product_stages` VALUES (11, 2, 21, 0);
INSERT INTO `product_stages` VALUES (12, 2, 1, 0);
INSERT INTO `product_stages` VALUES (13, 2, 6, 0);
INSERT INTO `product_stages` VALUES (14, 2, 2, 0);
INSERT INTO `product_stages` VALUES (15, 2, 3, 0);
INSERT INTO `product_stages` VALUES (16, 2, 8, 0);

-- ----------------------------
-- Table structure for production_order_sizes
-- ----------------------------
DROP TABLE IF EXISTS `production_order_sizes`;
CREATE TABLE `production_order_sizes`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `production_order_id` int(11) NULL DEFAULT NULL,
  `size_id` int(11) NULL DEFAULT NULL,
  `quantity` int(11) NULL DEFAULT NULL,
  `completed_quantity` int(11) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `production_order_id`(`production_order_id`) USING BTREE,
  INDEX `size_id`(`size_id`) USING BTREE,
  CONSTRAINT `production_order_sizes_ibfk_1` FOREIGN KEY (`production_order_id`) REFERENCES `production_orders` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `production_order_sizes_ibfk_2` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of production_order_sizes
-- ----------------------------

-- ----------------------------
-- Table structure for production_orders
-- ----------------------------
DROP TABLE IF EXISTS `production_orders`;
CREATE TABLE `production_orders`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` int(11) NULL DEFAULT NULL,
  `total_quantity` int(11) NULL DEFAULT NULL,
  `fabric_id` int(11) NULL DEFAULT NULL,
  `fabric_quantity_used` decimal(10, 2) NULL DEFAULT NULL,
  `fabric_cost_per_unit` decimal(10, 2) NULL DEFAULT NULL,
  `status` enum('cutting','manufacturing','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'cutting',
  `start_date` date NULL DEFAULT NULL,
  `target_completion_date` date NULL DEFAULT NULL,
  `actual_completion_date` date NULL DEFAULT NULL,
  `created_by` int(11) NULL DEFAULT NULL,
  `branch_id` int(11) NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `order_number`(`order_number`) USING BTREE,
  INDEX `product_id`(`product_id`) USING BTREE,
  INDEX `fabric_id`(`fabric_id`) USING BTREE,
  INDEX `created_by`(`created_by`) USING BTREE,
  INDEX `branch_id`(`branch_id`) USING BTREE,
  CONSTRAINT `production_orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `production_orders_ibfk_2` FOREIGN KEY (`fabric_id`) REFERENCES `fabric_types` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `production_orders_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `production_orders_ibfk_4` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of production_orders
-- ----------------------------

-- ----------------------------
-- Table structure for production_stage_records
-- ----------------------------
DROP TABLE IF EXISTS `production_stage_records`;
CREATE TABLE `production_stage_records`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `production_order_id` int(11) NULL DEFAULT NULL,
  `stage_id` int(11) NULL DEFAULT NULL,
  `worker_id` int(11) NULL DEFAULT NULL,
  `quantity_assigned` int(11) NULL DEFAULT NULL,
  `quantity_completed` int(11) NULL DEFAULT 0,
  `quantity_defective` int(11) NULL DEFAULT 0,
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cost_per_unit` decimal(10, 2) NULL DEFAULT NULL,
  `total_cost` decimal(10, 2) NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `status` enum('assigned','in_progress','completed','on_hold') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'assigned',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `production_order_id`(`production_order_id`) USING BTREE,
  INDEX `stage_id`(`stage_id`) USING BTREE,
  INDEX `worker_id`(`worker_id`) USING BTREE,
  CONSTRAINT `production_stage_records_ibfk_1` FOREIGN KEY (`production_order_id`) REFERENCES `production_orders` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `production_stage_records_ibfk_2` FOREIGN KEY (`stage_id`) REFERENCES `manufacturing_stages` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `production_stage_records_ibfk_3` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of production_stage_records
-- ----------------------------

-- ----------------------------
-- Table structure for products
-- ----------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `fabric_consumption` decimal(10, 2) NULL DEFAULT NULL,
  `estimated_cost` decimal(10, 2) NULL DEFAULT NULL,
  `selling_price` decimal(10, 2) NULL DEFAULT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of products
-- ----------------------------
INSERT INTO `products` VALUES (2, 'منتج جديد', 'P1', 'منتج جديد للتجربه', 'قمصان', 10.00, 100.00, 1500.00, 1, '2025-07-19 20:16:04');

-- ----------------------------
-- Table structure for sizes
-- ----------------------------
DROP TABLE IF EXISTS `sizes`;
CREATE TABLE `sizes`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int(11) NULL DEFAULT 0,
  `is_active` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 28 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sizes
-- ----------------------------
INSERT INTO `sizes` VALUES (1, 'صغير', 'S', 1, 1);
INSERT INTO `sizes` VALUES (2, 'متوسط', 'M', 2, 1);
INSERT INTO `sizes` VALUES (3, 'كبير', 'L', 3, 1);
INSERT INTO `sizes` VALUES (4, 'كبير جداً', 'XL', 4, 1);
INSERT INTO `sizes` VALUES (5, 'كبير جداً جداً', 'XXLS', 5, 1);
INSERT INTO `sizes` VALUES (21, 'صغير جداً', 'XS', 1, 1);

-- ----------------------------
-- Table structure for suppliers
-- ----------------------------
DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `current_balance` decimal(12, 2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of suppliers
-- ----------------------------
INSERT INTO `suppliers` VALUES (1, 'mamdouh hisham', 'mamdouh hisham mamdouh', '0115000628911111', 'mamdouh.hisham89@gmail.com', '15st elmasry1111', 20150.00, 1, '2025-07-19 02:34:23');

-- ----------------------------
-- Table structure for system_settings
-- ----------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `setting_key`(`setting_key`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of system_settings
-- ----------------------------
INSERT INTO `system_settings` VALUES (1, 'system_name', 'نظام إدارة مصنع الملابس', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (2, 'company_name', 'شركة الملابس المتطورة', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (3, 'country', 'مصر', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (4, 'currency', 'جنيه مصري', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (5, 'currency_symbol', 'ج.م', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (6, 'timezone', 'Africa/Cairo', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (7, 'language', 'ar', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (8, 'phone', '', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (9, 'email', '', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (10, 'address', '', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (11, 'logo', '', '2025-07-18 20:10:14', '2025-07-18 20:10:14');
INSERT INTO `system_settings` VALUES (12, 'version', '1.0.0', '2025-07-18 20:10:14', '2025-07-18 20:10:14');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `role` enum('admin','supervisor','accountant','worker','sales_rep','limited_user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `permissions` json NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `branch_id` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE,
  INDEX `branch_id`(`branch_id`) USING BTREE,
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'admin', '$2y$10$rNJ8i8dOanrzP5MPpl0LROohd1bYegUgx974TpMB9QjtyQXWPXrT.', 'مدير النظام', '', '', 'admin', NULL, 1, '2025-07-17 19:34:40', '2025-07-18 20:10:14', NULL);

SET FOREIGN_KEY_CHECKS = 1;
