<?php

return [
    // Available math functions to be used for rating & score calculation
    'math_functions_for_rating_and_score' => [
        "abs" => "Returns the absolute (positive) value of a number",
        "acos (arccos)" => "Returns the arc cosine of a number",
        "acosh" => "Returns the inverse hyperbolic cosine of a number",
        "arcctg (arccot, arccotan)" => "don't know mate, figure it out yourself",
        "arcsec" => "don't know mate, figure it out yourself",
        "arccsc (arccosec)" => "don't know mate, figure it out yourself",
        "asin (arcsin)" => "Returns the arc sine of a number",
        "atan (atn, arctan, arctg)" => "Returns the arc tangent of a number in radians",
        "atan2" => "Returns the arc tangent of two variables x and y",
        "atanh" => "Returns the inverse hyperbolic tangent of a number",
        "avg" => "Returns the average value of a set of values",
        "bindec" => "Returns the decimal equivalent of a binary number",
        "ceil" => "Returns the next highest integer value of a number",
        "cos" => "Returns the cosine of a number",
        "cosec (csc)" => "don't know mate, figure it out yourself",
        "cosh" => "Returns the hyperbolic cosine of a number",
        "ctg (cot, cotan, cotg, ctn)" => "don't know mate, figure it out yourself",
        "decbin" => "Returns a string containing a binary representation of the given decimal number",
        "dechex" => "Returns a string containing a hexadecimal representation of the given decimal number",
        "decoct" => "Returns a string containing an octal representation of the given decimal number",
        "deg2rad" => "Converts the given number from degrees to radians",
        "exp" => "Calculates the exponent of e",
        "expm1" => "Returns exp(x) - 1",
        "floor" => "Rounds a number down to the nearest integer",
        "fmod" => "Returns the remainder of x/y",
        "hexdec" => "Returns the decimal equivalent of a hexadecimal number",
        "hypot" => "Calculates the hypotenuse of a right-angle triangle",
        "if" => "don't know mate, figure it out yourself",
        "intdiv" => "Performs integer division",
        "log (ln)" => "Returns the natural logarithm of a number",
        "log10 (lg)" => "Returns the base-10 logarithm of a number",
        "log1p" => "Returns log(1+number)",
        "max" => "Returns the highest value in an array, or the highest value of several specified values",
        "min" => "Returns the lowest value in an array, or the lowest value of several specified values",
        "octdec" => "Returns the decimal equivalent of an octal number",
        "pi" => "Returns the value of PI",
        "pow" => "Returns x raised to the power of y",
        "rad2deg" => "Converts the given number from radians to degrees",
        "round" => "Rounds a floating-point number",
        "sec" => "don't know mate, figure it out yourself",
        "sin" => "Returns the sine of a number",
        "sinh" => "Returns the hyperbolic sine of a number",
        "sqrt" => "Returns the square root of a number",
        "tan (tn, tg)" => "Returns the tangent of a number",
        "tanh" => "Returns the hyperbolic tangent of a number",
        "e" => "Returns the value of Euler's number",
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Variables for Score
    |--------------------------------------------------------------------------
    |
    | These will be variables which admin and choose to create his own rating algorithm
    |
    | Eg: total_kills, total_deaths etc
    |
    */
    'variables_for_score_static' => [
        // Overall Player
        '$total_used' => 'Total number of of blocks or items used by the player',
        '$total_mined' => 'Total number of blocks or items mined by the player',
        '$total_picked_up' => 'Total number of items picked up by the player',
        '$total_dropped' => 'Total number of items dropped by the player',
        '$total_broken' => 'Total number of blocks broken by the player',
        '$total_crafted' => 'Total number of items crafted by the player',
        '$total_mob_kills' => 'Total number of mobs killed by the player',
        '$total_player_kills' => 'Total number of players killed by the player',
        '$total_deaths' => 'Total number of deaths by the player',
        '$total_damage_dealt' => 'The amount of damage the player has dealt in tenths 1(♥). Includes only melee attacks',
        '$total_damage_dealt_absorbed' => 'The amount of damage the player has dealt that were absorbed, in tenths of 1(♥)',
        '$total_damage_dealt_resisted' => 'The amount of damage the player has dealt that were resisted, in tenths of 1(♥)',
        '$total_damage_absorbed' => 'The amount of damage the player has absorbed in tenths of 1(♥)',
        '$total_damage_blocked_by_shield' => 'The amount of damage the player has blocked with a shield in tenths of 1(♥)',
        '$total_damage_resisted' => 'The amount of damage the player has resisted in tenths of 1(♥)',
        '$total_damage_taken' => 'The amount of damage the player has taken in tenths of 1(♥)',
        '$total_walk_one_cm' => 'The total distance walked by the player',
        '$total_fish_caught' => 'The total number of fish caught by the player',
        '$total_enchant_item' => 'The total number of items enchanted by the player',
        '$total_play_one_minute' => 'The total time the player has played (in ticks)',
        '$total_sleep_in_bed' => 'The number of times the player has slept in a bed.',
        '$total_jumps' => 'The total number of jumps performed by the player',
        '$total_leave_game' => 'The number of times "Save and quit to title" has been clicked',
        // Total Money if there is Vault
        '$total_money' => 'The total amount of money the player has (in Vault)',

    ],

    // Todo: Per Server Dynamic Stats. NOT USED YET
    'variables_for_score_dynamic' => [
        '$server_{id}__total_used' => 'lorem ipsum dolor sit amet, consectetur adip lorem ipsum dolor sit',
        '$server_{id}__total_mined' => 'lorem ipsum dolor sit amet, consectetur adip lorem ipsum dolor sit',
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Variables for Rating
    |--------------------------------------------------------------------------
    |
    | These will be variables which admin and choose to create his own rating algorithm
    |
    | Eg: total_kills, total_deaths etc
    |
    */
    'variables_for_rating_static' => [
        // Overall Player
        '$total_score' => 'The total score of the player',
        '$total_used' => 'Total number of of blocks or items used by the player',
        '$total_mined' => 'Total number of blocks or items mined by the player',
        '$total_picked_up' => 'Total number of items picked up by the player',
        '$total_dropped' => 'Total number of items dropped by the player',
        '$total_broken' => 'Total number of blocks broken by the player',
        '$total_crafted' => 'Total number of items crafted by the player',
        '$total_mob_kills' => 'Total number of mobs killed by the player',
        '$total_player_kills' => 'Total number of players killed by the player',
        '$total_deaths' => 'Total number of deaths by the player',
        '$total_damage_dealt' => 'The amount of damage the player has dealt in tenths 1(♥). Includes only melee attacks',
        '$total_damage_dealt_absorbed' => 'The amount of damage the player has dealt that were absorbed, in tenths of 1(♥)',
        '$total_damage_dealt_resisted' => 'The amount of damage the player has dealt that were resisted, in tenths of 1(♥)',
        '$total_damage_absorbed' => 'The amount of damage the player has absorbed in tenths of 1(♥)',
        '$total_damage_blocked_by_shield' => 'The amount of damage the player has blocked with a shield in tenths of 1(♥)',
        '$total_damage_resisted' => 'The amount of damage the player has resisted in tenths of 1(♥)',
        '$total_damage_taken' => 'The amount of damage the player has taken in tenths of 1(♥)',
        '$total_walk_one_cm' => 'The total distance walked by the player',
        '$total_fish_caught' => 'The total number of fish caught by the player',
        '$total_enchant_item' => 'The total number of items enchanted by the player',
        '$total_play_one_minute' => 'The total time the player has played (in ticks)',
        '$total_sleep_in_bed' => 'The number of times the player has slept in a bed.',
        '$total_jumps' => 'The total number of jumps performed by the player',
        '$total_leave_game' => 'The number of times "Save and quit to title" has been clicked',
        // Total Money if there is Vault
        '$total_money' => 'The total amount of money the player has (in Vault)',
    ],

    // Todo: Per Server Dynamic Stats. NOT USED YET
    'variables_for_rating_dynamic' => [
        '$server_{id}__total_used' => 'lorem ipsum dolor sit amet, consectetur adip lorem ipsum dolor sit',
        '$server_{id}__total_mined' => 'lorem ipsum dolor sit amet, consectetur adip lorem ipsum dolor sit',
    ],
];
