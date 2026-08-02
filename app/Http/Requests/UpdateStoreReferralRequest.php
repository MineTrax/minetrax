<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateStoreReferralRequest extends CreateStoreReferralRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('storeReferral'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->baseRules() + [
            'code' => [
                ...$this->codeRules(),
                Rule::unique('store_referrals', 'code')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('storeReferral')->id),
            ],
        ];
    }
}
