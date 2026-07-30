<?php

namespace App\Http\Requests;

use App\Models\StoreBan;
use App\Utils\Helpers\MinecraftUuidUtils;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateStoreBanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', StoreBan::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // Staff paste UUIDs from wherever they found them, and Mojang hands out the undashed
            // 32-char form while every store row stores the dashed one. A ban that does not match
            // the format the order was written in blocks nobody.
            'player_uuid' => MinecraftUuidUtils::toDashed($this->input('player_uuid')) ?: $this->input('player_uuid'),
            // Matching is already case-insensitive; storing one case as well keeps two bans that
            // read identically in the listing from both existing.
            'email' => $this->filled('email') ? mb_strtolower(trim((string) $this->input('email'))) : null,
            'username' => $this->filled('username') ? trim((string) $this->input('username')) : null,
        ]);
    }

    public function rules(): array
    {
        return array_merge($this->baseRules(), [
            // Creating a ban that has already lapsed would store something inert. Editing one to
            // lapse is a legitimate way to lift it while keeping the record, so the update request
            // drops this.
            'expires_at' => 'nullable|date|after:now',
        ]);
    }

    /**
     * Shared with UpdateStoreBanRequest so the two cannot drift apart.
     *
     * @return array<string, mixed>
     */
    public function baseRules(): array
    {
        return [
            // The account, by name rather than by id: staff are looking at an order or a profile
            // when they decide to ban, and neither shows a numeric id.
            'username' => 'nullable|string|exists:users,username',
            'player_uuid' => 'nullable|uuid',
            'ip_address' => 'nullable|ip',
            'email' => 'nullable|email|max:255',

            'reason' => 'nullable|string|max:255',
            // Null is a permanent ban, which is the common case for a chargeback.
            'expires_at' => 'nullable|date',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                // A row with no identity at all would match nothing, and StoreBanService guards
                // against the all-null query rather than banning everybody — so it is silently
                // inert. Refuse it here instead of storing a ban that cannot work.
                $identities = ['username', 'player_uuid', 'ip_address', 'email'];

                if (collect($identities)->every(fn (string $field) => ! $this->filled($field))) {
                    $validator->errors()->add('username', __('A ban needs at least one of: account, player UUID, IP address or email.'));
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'username.exists' => __('No account with that username exists.'),
            'player_uuid.uuid' => __('That does not look like a Minecraft player UUID.'),
            'expires_at.after' => __('An expiry in the past would lift the ban immediately. Leave it empty for a permanent ban.'),
        ];
    }
}
