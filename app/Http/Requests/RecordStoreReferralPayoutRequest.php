<?php

namespace App\Http\Requests;

use App\Models\StoreReferral;
use App\Services\StoreCurrencyService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RecordStoreReferralPayoutRequest extends FormRequest
{
    /**
     * A permission of its own, and deliberately not part of the admin role's curated set. Everything
     * else about a referral manages a promotion; this books money leaving the business.
     */
    public function authorize(): bool
    {
        return Gate::allows('payout', StoreReferral::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'paid_at' => 'nullable|date',
        ];
    }

    /**
     * Refuse a payout larger than what is outstanding.
     *
     * Checked here rather than clamped silently: recording more than is owed is nearly always a
     * typo, and the alternative is a negative balance nobody asked for. A refund landing *after* a
     * payout can still push the balance negative — that is real, and shown, and different from
     * this.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                /** @var StoreReferral $referral */
                $referral = $this->route('storeReferral');
                $owed = $referral->owed();

                if ($this->integer('amount') > $owed) {
                    $validator->errors()->add('amount', __('Only :amount is outstanding.', [
                        'amount' => app(StoreCurrencyService::class)->format(max(0, $owed)),
                    ]));
                }
            },
        ];
    }
}
