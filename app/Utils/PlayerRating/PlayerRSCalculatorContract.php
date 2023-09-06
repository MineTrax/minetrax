<?php


namespace App\Utils\PlayerRating;

use App\Models\Player;

interface PlayerRSCalculatorContract
{
    public function calculate(string $expression, Player $player, array $serverIds);
}
