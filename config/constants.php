<?php

return [
    // Available math functions to be used for rating & score calculation
    'math_functions_for_rating_and_score' => [
        'abs' => 'Returns the absolute (positive) value of a number',
        'acos (arccos)' => 'Returns the arc cosine of a number',
        'acosh' => 'Returns the inverse hyperbolic cosine of a number',
        'arcctg (arccot, arccotan)' => "don't know mate, figure it out yourself",
        'arcsec' => "don't know mate, figure it out yourself",
        'arccsc (arccosec)' => "don't know mate, figure it out yourself",
        'asin (arcsin)' => 'Returns the arc sine of a number',
        'atan (atn, arctan, arctg)' => 'Returns the arc tangent of a number in radians',
        'atan2' => 'Returns the arc tangent of two variables x and y',
        'atanh' => 'Returns the inverse hyperbolic tangent of a number',
        'avg' => 'Returns the average value of a set of values',
        'bindec' => 'Returns the decimal equivalent of a binary number',
        'ceil' => 'Returns the next highest integer value of a number',
        'cos' => 'Returns the cosine of a number',
        'cosec (csc)' => "don't know mate, figure it out yourself",
        'cosh' => 'Returns the hyperbolic cosine of a number',
        'ctg (cot, cotan, cotg, ctn)' => "don't know mate, figure it out yourself",
        'decbin' => 'Returns a string containing a binary representation of the given decimal number',
        'dechex' => 'Returns a string containing a hexadecimal representation of the given decimal number',
        'decoct' => 'Returns a string containing an octal representation of the given decimal number',
        'deg2rad' => 'Converts the given number from degrees to radians',
        'exp' => 'Calculates the exponent of e',
        'expm1' => 'Returns exp(x) - 1',
        'floor' => 'Rounds a number down to the nearest integer',
        'fmod' => 'Returns the remainder of x/y',
        'hexdec' => 'Returns the decimal equivalent of a hexadecimal number',
        'hypot' => 'Calculates the hypotenuse of a right-angle triangle',
        'if' => "don't know mate, figure it out yourself",
        'intdiv' => 'Performs integer division',
        'log (ln)' => 'Returns the natural logarithm of a number',
        'log10 (lg)' => 'Returns the base-10 logarithm of a number',
        'log1p' => 'Returns log(1+number)',
        'max' => 'Returns the highest value in an array, or the highest value of several specified values',
        'min' => 'Returns the lowest value in an array, or the lowest value of several specified values',
        'octdec' => 'Returns the decimal equivalent of an octal number',
        'pi' => 'Returns the value of PI',
        'pow' => 'Returns x raised to the power of y',
        'rad2deg' => 'Converts the given number from radians to degrees',
        'round' => 'Rounds a floating-point number',
        'sec' => "don't know mate, figure it out yourself",
        'sin' => 'Returns the sine of a number',
        'sinh' => 'Returns the hyperbolic sine of a number',
        'sqrt' => 'Returns the square root of a number',
        'tan (tn, tg)' => 'Returns the tangent of a number',
        'tanh' => 'Returns the hyperbolic tangent of a number',
        'e' => "Returns the value of Euler's number",
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
        '$total_items_placed' => 'Total number of items placed by the player',
        '$total_items_consumed' => 'Total number of items consumed by the player',
        '$total_items_enchanted' => 'Total number of items enchanted by the player',
        '$total_mob_kills' => 'Total number of mobs killed by the player',
        '$total_player_kills' => 'Total number of players killed by the player',
        '$total_deaths' => 'Total number of deaths by the player',
        '$total_fish_caught' => 'The total number of fish caught by the player',
        '$total_sleep_in_bed' => 'The number of times the player has slept in a bed.',
        '$total_leave_game' => 'Number of times player joined server.',
        '$raids_won' => 'The total number of raids won by the player',
        '$play_time' => 'The total time the player has played (in seconds). Inclusive of AFK time.',
        '$afk_time' => 'The total time the player has been AFK (in seconds)',
        '$distance_traveled' => 'The total distance the player has traveled (in blocks)',
        '$pvp_damage_given' => 'The total amount of damage the player has given to other players.',
        '$pvp_damage_taken' => 'The total amount of damage the player has taken from other players.',
        // Total Money if there is Vault
        '$total_money' => 'The total amount of money the player has (in Vault)',
    ],

    // Per Server Dynamic Stats.
    'variables_for_score_dynamic' => [
        '$total_used__server_{id}' => 'Total number of of blocks or items used by the player, on server with id {id}. Eg: $total_used__server_1',
        '$total_mined__server_{id}' => 'Total number of blocks or items mined by the player, on server with id {id}. Eg: $total_mined__server_1',
        '$total_picked_up__server_{id}' => 'Total number of items picked up by the player, on server with id {id}. Eg: $total_picked_up__server_1',
        '$total_dropped__server_{id}' => 'Total number of items dropped by the player, on server with id {id}. Eg: $total_dropped__server_1',
        '$total_broken__server_{id}' => 'Total number of blocks broken by the player, on server with id {id}. Eg: $total_broken__server_1',
        '$total_crafted__server_{id}' => 'Total number of items crafted by the player, on server with id {id}. Eg: $total_crafted__server_1',
        '$total_items_placed__server_{id}' => 'Total number of items placed by the player, on server with id {id}. Eg: $total_items_placed__server_1',
        '$total_items_consumed__server_{id}' => 'Total number of items consumed by the player, on server with id {id}. Eg: $total_items_consumed__server_1',
        '$total_items_enchanted__server_{id}' => 'Total number of items enchanted by the player, on server with id {id}. Eg: $total_items_enchanted__server_1',
        '$total_mob_kills__server_{id}' => 'Total number of mobs killed by the player, on server with id {id}. Eg: $total_mob_kills__server_1',
        '$total_player_kills__server_{id}' => 'Total number of players killed by the player, on server with id {id}. Eg: $total_player_kills__server_1',
        '$total_deaths__server_{id}' => 'Total number of deaths by the player, on server with id {id}. Eg: $total_deaths__server_1',
        '$total_fish_caught__server_{id}' => 'The total number of fish caught by the player, on server with id {id}. Eg: $total_fish_caught__server_1',
        '$total_sleep_in_bed__server_{id}' => 'The number of times the player has slept in a bed, on server with id {id}. Eg: $total_sleep_in_bed__server_1',
        '$raids_won__server_{id}' => 'The total number of raids won by the player, on server with id {id}. Eg: $raids_won__server_1',
        '$play_time__server_{id}' => 'The total time the player has played (in seconds). Inclusive of AFK time, on server with id {id}. Eg: $play_time__server_1',
        '$afk_time__server_{id}' => 'The total time the player has been AFK (in seconds), on server with id {id}. Eg: $afk_time__server_1',
        '$distance_traveled__server_{id}' => 'The total distance the player has traveled (in blocks), on server with id {id}. Eg: $distance_traveled__server_1',
        '$pvp_damage_given__server_{id}' => 'The total amount of damage the player has given to other players, on server with id {id}. Eg: $pvp_damage_given__server_1',
        '$pvp_damage_taken__server_{id}' => 'The total amount of damage the player has taken from other players, on server with id {id}. Eg: $pvp_damage_taken__server_1',
        // Total Money if there is Vault
        '$total_money__server_{id}' => 'The total amount of money the player has (in Vault), on server with id {id}. Eg: $total_money__server_1',
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
        '$total_items_placed' => 'Total number of items placed by the player',
        '$total_items_consumed' => 'Total number of items consumed by the player',
        '$total_items_enchanted' => 'Total number of items enchanted by the player',
        '$total_mob_kills' => 'Total number of mobs killed by the player',
        '$total_player_kills' => 'Total number of players killed by the player',
        '$total_deaths' => 'Total number of deaths by the player',
        '$total_fish_caught' => 'The total number of fish caught by the player',
        '$total_sleep_in_bed' => 'The number of times the player has slept in a bed.',
        '$total_leave_game' => 'Number of times player joined server.',
        '$raids_won' => 'The total number of raids won by the player',
        '$play_time' => 'The total time the player has played (in seconds). Inclusive of AFK time.',
        '$afk_time' => 'The total time the player has been AFK (in seconds)',
        '$distance_traveled' => 'The total distance the player has traveled (in blocks)',
        '$pvp_damage_given' => 'The total amount of damage the player has given to other players.',
        '$pvp_damage_taken' => 'The total amount of damage the player has taken from other players.',
        // Total Money if there is Vault
        '$total_money' => 'The total amount of money the player has (in Vault)',
    ],

    // Per Server Dynamic Stats.
    'variables_for_rating_dynamic' => [
        '$total_used__server_{id}' => 'Total number of of blocks or items used by the player, on server with id {id}. Eg: $total_used__server_1',
        '$total_mined__server_{id}' => 'Total number of blocks or items mined by the player, on server with id {id}. Eg: $total_mined__server_1',
        '$total_picked_up__server_{id}' => 'Total number of items picked up by the player, on server with id {id}. Eg: $total_picked_up__server_1',
        '$total_dropped__server_{id}' => 'Total number of items dropped by the player, on server with id {id}. Eg: $total_dropped__server_1',
        '$total_broken__server_{id}' => 'Total number of blocks broken by the player, on server with id {id}. Eg: $total_broken__server_1',
        '$total_crafted__server_{id}' => 'Total number of items crafted by the player, on server with id {id}. Eg: $total_crafted__server_1',
        '$total_items_placed__server_{id}' => 'Total number of items placed by the player, on server with id {id}. Eg: $total_items_placed__server_1',
        '$total_items_consumed__server_{id}' => 'Total number of items consumed by the player, on server with id {id}. Eg: $total_items_consumed__server_1',
        '$total_items_enchanted__server_{id}' => 'Total number of items enchanted by the player, on server with id {id}. Eg: $total_items_enchanted__server_1',
        '$total_mob_kills__server_{id}' => 'Total number of mobs killed by the player, on server with id {id}. Eg: $total_mob_kills__server_1',
        '$total_player_kills__server_{id}' => 'Total number of players killed by the player, on server with id {id}. Eg: $total_player_kills__server_1',
        '$total_deaths__server_{id}' => 'Total number of deaths by the player, on server with id {id}. Eg: $total_deaths__server_1',
        '$total_fish_caught__server_{id}' => 'The total number of fish caught by the player, on server with id {id}. Eg: $total_fish_caught__server_1',
        '$total_sleep_in_bed__server_{id}' => 'The number of times the player has slept in a bed, on server with id {id}. Eg: $total_sleep_in_bed__server_1',
        '$raids_won__server_{id}' => 'The total number of raids won by the player, on server with id {id}. Eg: $raids_won__server_1',
        '$play_time__server_{id}' => 'The total time the player has played (in seconds). Inclusive of AFK time, on server with id {id}. Eg: $play_time__server_1',
        '$afk_time__server_{id}' => 'The total time the player has been AFK (in seconds), on server with id {id}. Eg: $afk_time__server_1',
        '$distance_traveled__server_{id}' => 'The total distance the player has traveled (in blocks), on server with id {id}. Eg: $distance_traveled__server_1',
        '$pvp_damage_given__server_{id}' => 'The total amount of damage the player has given to other players, on server with id {id}. Eg: $pvp_damage_given__server_1',
        '$pvp_damage_taken__server_{id}' => 'The total amount of damage the player has taken from other players, on server with id {id}. Eg: $pvp_damage_taken__server_1',
        // Total Money if there is Vault
        '$total_money__server_{id}' => 'The total amount of money the player has (in Vault), on server with id {id}. Eg: $total_money__server_1',
    ],

    // Available input types for custom form
    'custom_form_input_types' => [
        'text',
        'textarea',
        'select',
        'multiselect',
        'radio',
        'checkbox',
        'email',
        'number',
        'password',
        'tel',
        'url',
        'week',
        'month',
        'time',
        'date',
        'datetime-local',
    ],
];
