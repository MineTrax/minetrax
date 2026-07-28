<?php

namespace App\Models;

use App\Enums\StoreVariableType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A reusable input the buyer fills in while ordering — a name prefix, a colour, a message.
 *
 * Defined once and attached to any number of packages. The value the buyer supplies is substituted
 * into that package's commands as {VARIABLE_<IDENTIFIER>}.
 */
class StoreVariable extends BaseModel
{
    use HasFactory;

    protected $appends = ['command_placeholder'];

    protected $casts = [
        'type' => StoreVariableType::class,
        'is_required' => 'boolean',
        'max_length' => 'integer',
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(StorePackage::class, 'store_package_variable', 'store_variable_id', 'store_package_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * What an admin types into a command to receive this variable's value.
     *
     * The `VARIABLE_` prefix is not decoration: it keeps the identifier out of the same namespace
     * as {PLAYER_USERNAME} and friends, so no variable can ever shadow a built-in placeholder.
     */
    public function getCommandPlaceholderAttribute(): string
    {
        return '{VARIABLE_'.strtoupper((string) $this->identifier).'}';
    }

    /**
     * The parameter key StoreCommandDispatchService passes to Helper::replacePlaceholders().
     */
    public function parameterKey(): string
    {
        return 'variable_'.$this->identifier;
    }

    /**
     * The choices for a select or radio, as a list.
     *
     * Stored comma separated to match how custom form fields store theirs, which is also the shape
     * the shared FormKit schema builder expects.
     *
     * @return array<int, string>
     */
    public function choices(): array
    {
        if (! $this->type->hasOptions() || ! $this->options) {
            return [];
        }

        return collect(explode(',', $this->options))
            ->map(fn (string $choice) => trim($choice))
            ->filter()
            ->values()
            ->all();
    }
}
