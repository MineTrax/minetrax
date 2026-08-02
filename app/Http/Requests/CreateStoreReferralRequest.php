<?php

namespace App\Http\Requests;

use App\Enums\StoreReferralAttributionMode;
use App\Models\StoreReferral;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CreateStoreReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', StoreReferral::class);
    }

    /**
     * Codes are stored uppercase with no whitespace, so KAKAMORA, kakamora and " Kakamora " are the
     * same code. A buyer copies these off a video description by hand.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(preg_replace('/\s+/', '', (string) $this->input('code'))),
            ]);
        }

        if ($this->has('username')) {
            $this->merge(['username' => $this->filled('username') ? trim((string) $this->input('username')) : null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->baseRules() + [
            'code' => [...$this->codeRules(), Rule::unique('store_referrals', 'code')->whereNull('deleted_at')],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function codeRules(): array
    {
        return ['required', 'string', 'max:64', 'regex:/^[A-Z0-9_-]+$/'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function baseRules(): array
    {
        return [
            'referrer_name' => 'required|string|max:255',
            // Named rather than picked: a select over every account on the site is unusable, and
            // this is the same choice store bans made.
            'username' => 'nullable|string|exists:users,username',
            // Basis points, so the 100% ceiling is 10000. Zero is allowed: a code can be pure
            // attribution with a discount attached and nothing owed.
            'share_bp' => 'required|integer|min:0|max:10000',
            // Stackable coupons only. An exclusive one here would displace whatever the buyer
            // already holds, so the reward for using a creator code would cost them their own
            // voucher — which is the opposite of an incentive.
            'store_coupon_id' => [
                'nullable', 'integer',
                Rule::exists('store_coupons', 'id')->where('is_stackable', true),
            ],

            'is_url_tracking_enabled' => 'required|boolean',
            // Blank is a lifetime window, which is a choice rather than a missing value.
            'attribution_window_days' => 'nullable|integer|min:1|max:3650',
            'attribution_mode' => ['required', Rule::enum(StoreReferralAttributionMode::class)],

            'is_command_execution_enabled' => 'required|boolean',
            'is_enabled' => 'required|boolean',
            'notes' => 'nullable|string|max:255',

            'commands' => 'nullable|array',
            'commands.*.id' => 'nullable|integer',
            'commands.*.command' => 'required|string|max:1000',
            'commands.*.delay_seconds' => 'nullable|integer|min:0|max:86400',
            'commands.*.is_player_online_required' => 'nullable|boolean',
            'commands.*.sort_order' => 'nullable|integer',
            'commands.*.servers' => 'nullable|array',
            'commands.*.servers.*.id' => 'required|integer|exists:servers,id',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => __('A referral code may only contain letters, numbers, hyphens and underscores.'),
            'share_bp.max' => __('A referral cannot earn more than 100% of a sale.'),
            'store_coupon_id.exists' => __('A referral reward must be a stackable coupon.'),
            'username.exists' => __('No account with that username exists.'),
        ];
    }
}
