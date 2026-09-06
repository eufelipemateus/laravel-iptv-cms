<?php

namespace App\Rules;

use App\Services\StreamMonitoring\SsrfGuard;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Throwable;

class ValidEpgUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || mb_strlen($value) > 2048 || preg_match('/[\x00-\x1F\x7F]/', $value)) {
            $fail('The :attribute must be a safe HTTP or HTTPS URL.');

            return;
        }

        try {
            app(SsrfGuard::class)->assertAllowed($value);
        } catch (Throwable) {
            $fail('The :attribute must point to a public HTTP or HTTPS address.');
        }
    }
}
