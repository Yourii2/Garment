<?php
class Validator {
    private $errors = [];
    
    public function required($value, $field_name) {
        if (empty($value) && $value !== '0') {
            $this->errors[] = "حقل $field_name مطلوب";
        }
        return $this;
    }
    
    public function numeric($value, $field_name, $min = null, $max = null) {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[] = "حقل $field_name يجب أن يكون رقم";
        } elseif (is_numeric($value)) {
            if ($min !== null && $value < $min) {
                $this->errors[] = "حقل $field_name يجب أن يكون أكبر من أو يساوي $min";
            }
            if ($max !== null && $value > $max) {
                $this->errors[] = "حقل $field_name يجب أن يكون أصغر من أو يساوي $max";
            }
        }
        return $this;
    }
    
    public function email($value, $field_name) {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "حقل $field_name يجب أن يكون بريد إلكتروني صحيح";
        }
        return $this;
    }
    
    public function phone($value, $field_name) {
        if (!empty($value) && !preg_match('/^[0-9+\-\s()]+$/', $value)) {
            $this->errors[] = "حقل $field_name يجب أن يحتوي على أرقام فقط";
        }
        return $this;
    }
    
    public function minLength($value, $field_name, $min) {
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[] = "حقل $field_name يجب أن يكون على الأقل $min أحرف";
        }
        return $this;
    }
    
    public function maxLength($value, $field_name, $max) {
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[] = "حقل $field_name يجب أن يكون أقل من $max حرف";
        }
        return $this;
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    public function getFirstError() {
        return $this->errors[0] ?? null;
    }
}

function validateInventoryMovement($data) {
    $validator = new Validator();
    
    $validator->required($data['quantity'] ?? '', 'الكمية')
              ->numeric($data['quantity'] ?? '', 'الكمية', 0.01);
    
    if (empty($data['fabric_id']) && empty($data['accessory_id'])) {
        $validator->errors[] = 'يجب اختيار قماش أو إكسسوار';
    }
    
    return $validator;
}

function validateUserData($data, $is_edit = false) {
    $validator = new Validator();
    
    $validator->required($data['username'] ?? '', 'اسم المستخدم')
              ->minLength($data['username'] ?? '', 'اسم المستخدم', 3)
              ->maxLength($data['username'] ?? '', 'اسم المستخدم', 50);
    
    $validator->required($data['full_name'] ?? '', 'الاسم الكامل')
              ->maxLength($data['full_name'] ?? '', 'الاسم الكامل', 100);
    
    if (!$is_edit || !empty($data['password'])) {
        $validator->required($data['password'] ?? '', 'كلمة المرور')
                  ->minLength($data['password'] ?? '', 'كلمة المرور', 6);
    }
    
    $validator->email($data['email'] ?? '', 'البريد الإلكتروني')
              ->phone($data['phone'] ?? '', 'رقم الهاتف');
    
    return $validator;
}
?>