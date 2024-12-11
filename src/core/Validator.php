<?php

namespace Core;

class Validator {
    protected $data;
    protected $rules;
    protected $errors = [];

    public function __construct($data, $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function validate() {
        foreach ($this->rules as $field => $fieldRules) {
            $fieldRules = explode('|', $fieldRules);
            
            foreach ($fieldRules as $rule) {
                $params = [];
                
                if (strpos($rule, ':') !== false) {
                    list($rule, $param) = explode(':', $rule);
                    $params = explode(',', $param);
                }
                
                $method = 'validate' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    if (!$this->$method($field, $params)) {
                        $this->addError($field, $rule);
                    }
                }
            }
        }
        
        return empty($this->errors);
    }

    protected function validateRequired($field) {
        $value = $this->data[$field] ?? null;
        return $value !== null && $value !== '';
    }

    protected function validateEmail($field) {
        return filter_var($this->data[$field], FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateMin($field, $params) {
        $length = $params[0];
        return strlen($this->data[$field]) >= $length;
    }

    protected function validateMax($field, $params) {
        $length = $params[0];
        return strlen($this->data[$field]) <= $length;
    }

    protected function validateNumeric($field) {
        return is_numeric($this->data[$field]);
    }

    protected function validateDate($field) {
        return strtotime($this->data[$field]) !== false;
    }

    protected function addError($field, $rule) {
        $this->errors[$field][] = $this->getErrorMessage($field, $rule);
    }

    protected function getErrorMessage($field, $rule) {
        $messages = [
            'required' => 'The ' . $field . ' field is required',
            'email' => 'The ' . $field . ' must be a valid email address',
            'min' => 'The ' . $field . ' must be at least ' . $this->rules[$field][1] . ' characters',
            'max' => 'The ' . $field . ' may not be greater than ' . $this->rules[$field][1] . ' characters',
            'numeric' => 'The ' . $field . ' must be a number',
            'date' => 'The ' . $field . ' must be a valid date'
        ];
        
        return $messages[$rule] ?? 'The ' . $field . ' field is invalid';
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return !empty($this->errors);
    }
}
