<?php

namespace App\Support;

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function validate(array $rules): bool
    {
        foreach ($rules as $field => $fieldRules) {
            $value = $this->data[$field] ?? null;
            $rulesArray = explode('|', $fieldRules);

            foreach ($rulesArray as $ruleStr) {
                $parts = explode(':', $ruleStr);
                $rule = $parts[0];
                $param = $parts[1] ?? null;

                if ($rule === 'required' && ($value === null || $value === '')) {
                    $this->addError($field, "The $field field is required.");
                }

                if ($value !== null && $value !== '') {
                    if ($rule === 'numeric' && !is_numeric($value)) {
                        $this->addError($field, "The $field must be a number.");
                    }

                    if ($rule === 'min') {
                        if (is_numeric($value) && $value < $param) {
                            $this->addError($field, "The $field must be at least $param.");
                        } elseif (is_string($value) && strlen($value) < $param) {
                            $this->addError($field, "The $field must be at least $param characters.");
                        }
                    }

                    if ($rule === 'max') {
                        if (is_numeric($value) && $value > $param) {
                            $this->addError($field, "The $field must not be greater than $param.");
                        } elseif (is_string($value) && strlen($value) > $param) {
                            $this->addError($field, "The $field must not exceed $param characters.");
                        }
                    }

                    if ($rule === 'in') {
                        $allowed = explode(',', $param);
                        if (!in_array($value, $allowed, true)) {
                            $this->addError($field, "The selected $field is invalid.");
                        }
                    }
                }
            }
        }
        return empty($this->errors);
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
