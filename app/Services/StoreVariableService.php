<?php

namespace App\Services;

use App\Enums\StoreVariableType;
use App\Models\StorePackage;
use App\Models\StoreVariable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * The one place a buyer-supplied variable value is validated, normalised and turned into a command
 * parameter.
 *
 * Everything here exists because these values end up inside a command that runs on a live
 * Minecraft server. The buyer chooses the value, so it is untrusted input on its way to a
 * privileged console — validated against the variable's own definition on the server, never on the
 * strength of whatever the browser sent.
 */
class StoreVariableService
{
    /**
     * What a free-text value may contain.
     *
     * An allowlist rather than a blocklist, and deliberately narrow: letters, digits, spaces and
     * the punctuation a Minecraft prefix or nickname actually needs, including `&` and `§` for
     * colour codes. Everything else is refused — most importantly `{` and `}`, which would let a
     * buyer smuggle in another placeholder, and `;` and `|`, which some plugins treat as command
     * separators. An admin who needs a value outside this set should use a select instead.
     */
    private const FREE_TEXT_PATTERN = '/^[\p{L}\p{N} _\-&§.,!?\'\[\]()]*$/u';

    private const FREE_TEXT_MAX_LENGTH = 255;

    /**
     * FormKit field descriptors for a package's variables.
     *
     * Shaped for Composables/useFormKit.js, the same builder the custom forms use, so the
     * storefront renders these through FormKitSchema with no per-type frontend code.
     *
     * @return array<int, array<string, mixed>>
     */
    public function schemaForPackage(StorePackage $package): array
    {
        return $this->variablesFor($package)
            ->map(fn (StoreVariable $variable) => [
                'type' => $variable->type->value,
                'label' => $variable->name,
                'name' => $variable->identifier,
                'placeholder' => $variable->placeholder,
                'help' => $variable->description,
                'validation' => $this->clientValidationFor($variable),
                // The shared builder splits this on commas itself.
                'options' => $variable->type->hasOptions() ? $variable->options : null,
            ])
            ->all();
    }

    /**
     * Validate a submitted map of identifier => value against the package's variables.
     *
     * Returns only the values that belong to this package, normalised. Anything the package does
     * not define is dropped rather than stored, so a crafted payload cannot park data on a cart
     * row or reach a command through a variable the package never had.
     *
     * @param  array<string, mixed>  $submitted
     * @return array<string, mixed>|null
     *
     * @throws ValidationException
     */
    public function validate(StorePackage $package, array $submitted): ?array
    {
        $variables = $this->variablesFor($package);

        if ($variables->isEmpty()) {
            return null;
        }

        $values = [];
        $errors = [];

        foreach ($variables as $variable) {
            $raw = $submitted[$variable->identifier] ?? null;

            try {
                $value = $this->normalise($variable, $raw);
            } catch (ValidationException $exception) {
                $errors['variables.'.$variable->identifier] = $exception->validator->errors()->first();

                continue;
            }

            if ($value !== null) {
                $values[$variable->identifier] = $value;
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $values ?: null;
    }

    /**
     * The snapshot stored on the order item.
     *
     * Carries the name alongside the identifier so a completed order still reads correctly after
     * the variable is renamed or deleted — the same reason order items snapshot the package name.
     *
     * @param  array<string, mixed>|null  $values
     * @return array<int, array{identifier: string, name: string, value: mixed}>|null
     */
    public function snapshotFor(StorePackage $package, ?array $values): ?array
    {
        if (! $values) {
            return null;
        }

        $snapshot = $this->variablesFor($package)
            ->filter(fn (StoreVariable $variable) => array_key_exists($variable->identifier, $values))
            ->map(fn (StoreVariable $variable) => [
                'identifier' => $variable->identifier,
                'name' => $variable->name,
                'value' => $values[$variable->identifier],
            ])
            ->values()
            ->all();

        return $snapshot ?: null;
    }

    /**
     * Command parameters from an order item's snapshot.
     *
     * Keys are prefixed `variable_`, so an admin writes {VARIABLE_PREFIX_COLOR} and no variable can
     * shadow a built-in placeholder like {PLAYER_USERNAME} however it is named.
     *
     * @param  array<int, array{identifier: string, name: string, value: mixed}>|null  $snapshot
     * @return array<string, string>
     */
    public function parametersFrom(?array $snapshot): array
    {
        $parameters = [];

        foreach ($snapshot ?? [] as $entry) {
            if (! isset($entry['identifier'])) {
                continue;
            }

            $value = $entry['value'] ?? '';

            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $parameters['variable_'.$entry['identifier']] = (string) $value;
        }

        return $parameters;
    }

    /**
     * Identifiers that cannot be used, because a command placeholder of the same name already
     * means something. The `variable_` prefix makes a real collision impossible, but reusing a
     * built-in name would still be confusing to read in a command.
     *
     * @return array<int, string>
     */
    public function reservedIdentifiers(): array
    {
        return [
            'player_username', 'player_uuid', 'quantity', 'package_name', 'package_id',
            'order_id', 'order_uuid', 'currency',
        ];
    }

    /**
     * A package's enabled variables, in the order the admin arranged them.
     *
     * @return Collection<int, StoreVariable>
     */
    public function variablesFor(StorePackage $package): Collection
    {
        if (! $package->relationLoaded('variables')) {
            $package->load('variables');
        }

        // The ordering lives on the relation, so every eager load agrees on it.
        return $package->variables
            ->filter(fn (StoreVariable $variable) => $variable->is_enabled)
            ->values();
    }

    /**
     * @throws ValidationException
     */
    private function normalise(StoreVariable $variable, mixed $raw): mixed
    {
        if ($variable->type === StoreVariableType::CHECKBOX) {
            $checked = filter_var($raw, FILTER_VALIDATE_BOOLEAN);

            if ($variable->is_required && ! $checked) {
                $this->fail(__(':name is required.', ['name' => $variable->name]));
            }

            return $checked;
        }

        $value = is_scalar($raw) ? trim((string) $raw) : '';

        if ($value === '') {
            if ($variable->is_required) {
                $this->fail(__(':name is required.', ['name' => $variable->name]));
            }

            return null;
        }

        return match (true) {
            $variable->type->hasOptions() => $this->normaliseChoice($variable, $value),
            $variable->type === StoreVariableType::NUMBER => $this->normaliseNumber($variable, $value),
            default => $this->normaliseFreeText($variable, $value),
        };
    }

    /**
     * @throws ValidationException
     */
    private function normaliseChoice(StoreVariable $variable, string $value): string
    {
        $choices = $variable->choices();

        // Compared case-insensitively but stored as the admin wrote it, so the command receives the
        // canonical spelling rather than whatever casing the browser posted.
        foreach ($choices as $choice) {
            if (strcasecmp($choice, $value) === 0) {
                return $choice;
            }
        }

        $this->fail(__('Choose one of the available options for :name.', ['name' => $variable->name]));
    }

    /**
     * @throws ValidationException
     */
    private function normaliseNumber(StoreVariable $variable, string $value): string
    {
        if (! is_numeric($value)) {
            $this->fail(__(':name must be a number.', ['name' => $variable->name]));
        }

        return $value;
    }

    /**
     * @throws ValidationException
     */
    private function normaliseFreeText(StoreVariable $variable, string $value): string
    {
        $limit = min(self::FREE_TEXT_MAX_LENGTH, $variable->max_length ?: self::FREE_TEXT_MAX_LENGTH);

        if (mb_strlen($value) > $limit) {
            $this->fail(__(':name cannot be longer than :count characters.', [
                'name' => $variable->name,
                'count' => $limit,
            ]));
        }

        if (! preg_match(self::FREE_TEXT_PATTERN, $value)) {
            $this->fail(__(':name contains characters that are not allowed.', ['name' => $variable->name]));
        }

        return $value;
    }

    /**
     * The FormKit validation string. Client-side convenience only — every rule it expresses is
     * enforced again on the server above, because this one travels through the browser.
     */
    private function clientValidationFor(StoreVariable $variable): ?string
    {
        $rules = [];

        if ($variable->is_required) {
            $rules[] = $variable->type === StoreVariableType::CHECKBOX ? 'accepted' : 'required';
        }

        if ($variable->type->isFreeText() && $variable->max_length) {
            $rules[] = 'length:0,'.$variable->max_length;
        }

        if ($variable->type === StoreVariableType::NUMBER) {
            $rules[] = 'number';
        }

        return $rules ? implode('|', $rules) : null;
    }

    /**
     * @throws ValidationException
     */
    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['variable' => $message]);
    }
}
