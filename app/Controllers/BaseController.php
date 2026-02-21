<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Exceptions\ValidationException;

abstract class BaseController
{
    /**
     * Validate input against rules.
     * Rules format: 'required|min:2|max:100|email|in:a,b,c'
     *
     * @return array The validated data (same as input)
     * @throws ValidationException
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value     = $data[$field] ?? null;
            $ruleList  = explode('|', $ruleString);
            $isPresent = $value !== null && $value !== '';

            foreach ($ruleList as $rule) {
                if ($rule === 'required') {
                    if (!$isPresent) {
                        $errors[$field][] = "{$field} is required";
                    }
                } elseif ($rule === 'nullable') {
                    if ($value === null || $value === '') break; // skip further rules
                } elseif ($rule === 'email') {
                    if ($isPresent && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = "{$field} must be a valid email";
                    }
                } elseif ($rule === 'numeric') {
                    if ($isPresent && !is_numeric($value)) {
                        $errors[$field][] = "{$field} must be numeric";
                    }
                } elseif ($rule === 'date') {
                    if ($isPresent && !strtotime($value)) {
                        $errors[$field][] = "{$field} must be a valid date";
                    }
                } elseif (str_starts_with($rule, 'min:')) {
                    $min = (int) substr($rule, 4);
                    if ($isPresent && mb_strlen((string) $value) < $min) {
                        $errors[$field][] = "{$field} must be at least {$min} characters";
                    }
                } elseif (str_starts_with($rule, 'max:')) {
                    $max = (int) substr($rule, 4);
                    if ($isPresent && mb_strlen((string) $value) > $max) {
                        $errors[$field][] = "{$field} must not exceed {$max} characters";
                    }
                } elseif (str_starts_with($rule, 'in:')) {
                    $allowed = explode(',', substr($rule, 3));
                    if ($isPresent && !in_array($value, $allowed, true)) {
                        $errors[$field][] = "{$field} must be one of: " . implode(', ', $allowed);
                    }
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }

        return $data;
    }

    protected function success(mixed $data = null, string $message = 'OK', int $status = 200, array $meta = []): void
    {
        Response::success($data, $message, $status, $meta);
    }

    protected function created(mixed $data = null, string $message = 'Created'): void
    {
        Response::created($data, $message);
    }

    protected function noContent(): void
    {
        Response::noContent();
    }

    protected function paginated(array $data, int $total, int $page, int $perPage): void
    {
        Response::paginated($data, $total, $page, $perPage);
    }
}
