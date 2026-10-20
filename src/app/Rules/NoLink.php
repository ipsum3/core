<?php

namespace Ipsum\Core\app\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoLink implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (
            str_contains(strtolower($value), 'https://')
            || str_contains(strtolower($value), 'http://')
            || str_contains(strtolower($value), 'www.')
            || str_contains(strtolower($value), 'hxxp://')
        ) {
            $fail("Pour éviter le spam, il n'est pas autorisé de poster des liens.");
        }
    }
}
