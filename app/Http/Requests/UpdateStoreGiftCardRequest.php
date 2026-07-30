<?php

namespace App\Http\Requests;

use Gate;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreGiftCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('storeGiftCard'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'username' => $this->filled('username') ? trim((string) $this->input('username')) : null,
        ]);
    }

    /**
     * Deliberately no `balance` and no `currency_code`.
     *
     * The balance moves only through an adjustment, which writes a ledger row — an edit that set it
     * directly would leave the card's history disagreeing with its total. The currency is frozen
     * because the card may already have been part-spent against an order at a snapshot rate.
     */
    public function rules(): array
    {
        return [
            'username' => 'nullable|string|exists:users,username',
            // Unlike creating, backdating is allowed: it is how a card is retired while its ledger
            // is kept.
            'expires_at' => 'nullable|date',
            'is_enabled' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'username.exists' => __('No account with that username exists.'),
        ];
    }
}
