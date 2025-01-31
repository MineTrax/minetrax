<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IpOrFqdn implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->isValidIP($value) && !$this->isValidFqdn($value)) {
            $fail(__('The :attribute must be a valid IP address or FQDN.', ['attribute' => $attribute]));
        }
    }

    private function isValidIP($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    private function isValidFqdn($value)
    {
        return filter_var(gethostbyname($value), FILTER_VALIDATE_IP) !== false;
    }
}
