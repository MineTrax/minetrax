<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Translation Chunk Size
    |--------------------------------------------------------------------------
    |
    | Number of translation keys to send per AI request. Smaller chunks are
    | more reliable but slower. Larger chunks are faster but may hit token
    | limits depending on the model.
    |
    */

    'chunk_size' => 25,

    /*
    |--------------------------------------------------------------------------
    | Retry Attempts
    |--------------------------------------------------------------------------
    |
    | Number of times to retry a failed chunk before skipping it.
    |
    */

    'retries' => 5,

    /*
    |--------------------------------------------------------------------------
    | Locale Names
    |--------------------------------------------------------------------------
    |
    | Human-readable names for locales. Standard codes (es, fr, de, etc.) are
    | recognized automatically. Add entries here only for ambiguous or custom
    | locale codes that the AI might not recognize.
    |
    */

    'locale_names' => [
        'zh-cn' => 'Chinese (Simplified)',
        'zh-hk' => 'Chinese (Traditional, Hong Kong)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional Translation Rules
    |--------------------------------------------------------------------------
    |
    | Extra instructions passed to the AI when translating. The 'default' rules
    | apply to all languages. You can add locale-specific rules that will be
    | merged with the defaults (e.g., 'ko', 'ja', etc.).
    |
    */

    'rules' => [
        'default' => [
            'This is a Minecraft server management web application called MineTrax.',
            'Use a friendly and clear tone appropriate for a gaming community.',
            'Preserve any Minecraft-specific terminology (e.g., server, player, skin, etc.) without translating them if they are commonly used in the target language gaming community.',
        ],
    ],
];
