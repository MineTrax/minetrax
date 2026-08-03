<?php

namespace App\Rules;

use App\Services\CaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Turnstile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! CaptchaService::isEnabled()) {
            return;
        }

        if (! app(CaptchaService::class)->verify($value, request()->ip())) {
            $fail('The captcha challenge failed. Please try again.')->translate();
        }
    }
}
