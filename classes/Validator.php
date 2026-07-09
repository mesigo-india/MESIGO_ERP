<?php
declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Validator class for input validation
 */
class Validator
{
    private array $errors = [];
    
    /**
     * Validate data against rules
     */
    public function validate(array $data, array $rules): array
    {
        $this->errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $rulesArray = explode('|', $ruleString);
            $value = $data[$field] ?? '';
            
            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $value, $rule, $data);
            }
        }
        
        return $this->errors;
    }
    
    /**
     * Apply single validation rule
     */
    private function applyRule(string $field, mixed $value, string $rule, array $data): void
    {
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $paramsString] = explode(':', $rule, 2);
            $params = explode(',', $paramsString);
        }
        
        $method = 'validate' . ucfirst($rule);
        
        if (method_exists($this, $method)) {
            $this->$method($field, $value, $params, $data);
        }
    }
    
    /**
     * Required validation
     */
    private function validateRequired(string $field, mixed $value, array $params, array $data): void
    {
        if (empty($value) && $value !== '0') {
            $this->errors[$field][] = "{$field} is required";
        }
    }
    
    /**
     * Email validation
     */
    private function validateEmail(string $field, mixed $value, array $params, array $data): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "{$field} must be a valid email";
        }
    }
    
    /**
     * Min length validation
     */
    private function validateMin(string $field, mixed $value, array $params, array $data): void
    {
        $min = (int) ($params[0] ?? 0);
        if (strlen((string) $value) < $min) {
            $this->errors[$field][] = "{$field} must be at least {$min} characters";
        }
    }
    
    /**
     * Max length validation
     */
    private function validateMax(string $field, mixed $value, array $params, array $data): void
    {
        $max = (int) ($params[0] ?? 0);
        if (strlen((string) $value) > $max) {
            $this->errors[$field][] = "{$field} must not exceed {$max} characters";
        }
    }
    
    /**
     * Unique validation
     */
    private function validateUnique(string $field, mixed $value, array $params, array $data): void
    {
        if (empty($value)) {
            return;
        }
        
        $table = $params[0] ?? '';
        $column = $params[1] ?? $field;
        
        $stmt = Database::getInstance()->prepare("
            SELECT COUNT(*) FROM {$table} 
            WHERE {$column} = :value 
            AND deleted_at IS NULL
        ");
        
        $stmt->execute(['value' => $value]);
        
        if ($stmt->fetchColumn() > 0) {
            $this->errors[$field][] = "{$field} already exists";
        }
    }
    
    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }
}