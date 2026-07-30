<?php

namespace App\Http\Requests;

use App\Models\StoreGiftCard;
use Gate;
use Illuminate\Foundation\Http\FormRequest;

class CreateStoreGiftCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', StoreGiftCard::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => $this->filled('username') ? trim((string) $this->input('username')) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            // Minor units of `currency_code`, built client-side from that currency's own exponent —
            // never a hardcoded 100, which would misprice JPY and KWD.
            'balance' => 'required|integer|min:1',
            'currency_code' => 'required|string|size:3|exists:store_currencies,code',

            // Optional: a card can be handed to somebody or left bearer-style for a giveaway.
            'username' => 'nullable|string|exists:users,username',
            'expires_at' => 'nullable|date|after:now',
            'note' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'username.exists' => __('No account with that username exists.'),
            'expires_at.after' => __('A card that has already expired can never be spent. Leave it empty for no expiry.'),
        ];
    }
}
