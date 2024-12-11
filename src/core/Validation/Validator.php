<?php

namespace Core\Validation;

class Validator {
    private $data;
    private $rules;
    private $errors = [];

    public function __construct($data, $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function validate() {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            foreach ($rules as $rule) {
                if (strpos($rule, ':') !== false) {
                    list($ruleName, $parameter) = explode(':', $rule);
                } else {
                    $ruleName = $rule;
                    $parameter = null;
                }

                $method = 'validate' . ucfirst($ruleName);
                if (method_exists($this, $method)) {
                    if (!$this->$method($field, $parameter)) {
                        return false;
                    }
                }
            }
        }
        return empty($this->errors);
    }

    private function validateRequired($field) {
        if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
            $this->errors[$field] = ucfirst($field) . ' is required';
            return false;
        }
        return true;
    }

    private function validateMin($field, $length) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field] = ucfirst($field) . ' must be at least ' . $length . ' characters';
            return false;
        }
        return true;
    }

    private function validateDate($field) {
        if (isset($this->data[$field])) {
            $date = \DateTime::createFromFormat('Y-m-d', $this->data[$field]);
            if (!$date || $date->format('Y-m-d') !== $this->data[$field]) {
                $this->errors[$field] = ucfirst($field) . ' must be a valid date in Y-m-d format';
                return false;
            }
        }
        return true;
    }

    private function validateNumeric($field) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = ucfirst($field) . ' must be numeric';
            return false;
        }
        return true;
    }

    public function getErrors() {
        return $this->errors;
    }
}
