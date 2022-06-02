<?php


namespace App\Utils\PlayerRating;

use App\Models\Player;
use Illuminate\Support\Str;
use NXP\MathExecutor;


class PlayerScoreCalculator implements PlayerRSCalculatorContract
{
    protected MathExecutor $executor;

    public function __construct()
    {
        $this->executor = new MathExecutor();
    }

    public function calculate(string $expression, object $player): float|null
    {
        $variablesForScoreStatic = array_keys(config('constants.variables_for_score_static'));

        // Regex for PHP Variable: [$a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*
        // Get list of all variables that user has passed in the expression
        $variablesList = Str::of($expression)->matchAll('/[$a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*/');

        // Loop thru each variables, validates and create math executor variable for it.
        foreach ($variablesList as $variable) {
            // Validate
            if (!in_array($variable, $variablesForScoreStatic)) {
                continue;
            }

            // Replace
            $variableNameWithoutDollar = str_replace('$', '', $variable);
            $variableValue = $player->{$variableNameWithoutDollar};
            // Set Variable Value
            $this->executor->setVar($variableNameWithoutDollar, $variableValue);
        }

        return $this->executor->execute($expression);
    }
}
