<?php

namespace App\Settings;

use App\Enums\ShowPlayerIntelTo;
use Spatie\LaravelSettings\Settings;

class PlayerSettings extends Settings
{
    public bool $is_custom_rating_enabled;

    public ?string $custom_rating_expression;

    public int $last_seen_day_for_active; // last X day for player to be count as active for rating calculation -1 to disable.

    public bool $is_custom_score_enabled;

    public ?string $custom_score_expression;

    public string $show_player_intel_to; // none, staff, self, login, all

    public static function group(): string
    {
        return 'player';
    }

    public static function encrypted(): array
    {
        return [
            'custom_rating_expression',
            'custom_score_expression',
        ];
    }
}
