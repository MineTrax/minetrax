<?php

namespace App\Http\Requests\Fortify;

use App\Rules\Turnstile;
use Laravel\Fortify\Http\Requests\SendPasswordResetLinkRequest as BaseRequest;

class SendPasswordResetLinkRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), $this->captchaRules());
    }

    /**
     * @return array<string, mixed>
     */
    protected function captchaRules(): array
    {
        if (! config('services.turnstile.enabled', false) || config('auth.disable_email_password_auth', false)) {
            return [];
        }

        return [
            'turnstile_response' => ['required', 'string', new Turnstile],
        ];
    }
}
