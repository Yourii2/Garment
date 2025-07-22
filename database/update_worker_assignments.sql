-- تحديث جدول workers لإضافة أرصدة العمال
ALTER TABLE workers 
ADD COLUMN pending_balance DECIMAL(10,2) DEFAULT 0 COMMENT 'الرصيد المستحق (مهام جارية)',
ADD COLUMN earned_balance DECIMAL(10,2) DEFAULT 0 COMMENT 'الرصيد المكتسب (مهام مكتملة)';
