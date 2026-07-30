<?php

namespace App\Http\Requests;

use Gate;

class UpdateStoreBanRequest extends CreateStoreBanRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('storeBan'));
    }

    /**
     * The create rules minus `expires_at` having to be in the future.
     *
     * Backdating an expiry is how a ban is lifted while its row is kept as history, and an
     * already-lapsed ban still has to be editable at all.
     */
    public function rules(): array
    {
        return $this->baseRules();
    }
}
