<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 驗證語言行
    |--------------------------------------------------------------------------
    |
    | 下面的語言行包含驗證器類使用的默認錯誤信息。
    | 其中一些規則有多個版本，如尺寸規則。
    | 請隨意調整這裡的每一條信息。
    |
    */

    'accepted' => ':attribute 必須被接受',
    'active_url' => ':attribute 不是一個有效的網址。',
    'after' => ':attribute 必須是在 :date 以後。',
    'after_or_equal' => ':attribute 必須是在 :date 當天或以後。',
    'alpha' => ':attribute 只能包含字母。',
    'alpha_dash' => ':attribute 只能包含字母、數字、破折號和下劃線。',
    'alpha_num' => ':attribute 屬性只能包含字母和數字。',
    'array' => ':attribute 必須是一個數組。',
    'before' => ':attribute 必須是在 :date 之前的日期。',
    'before_or_equal' => ':attribute 必須是在 :date 當天或以後。',
    'between' => [
        'numeric' => ':attribute 必須在 :min 和 :max 之間。',
        'file' => ':attribute 必須在 :min 和 :max kb 之間',
        'string' => ':attribute 必須介于 :min 和 :max 字符之間。',
        'array' => ':attribute 必須介於 :min 和 :max 項之間。',
    ],
    'boolean' => ':attribute 字段必須為 true 或 false。',
    'confirmed' => ':attribute 確認不匹配。',
    'date' => ':attribute 不是有效日期。',
    'date_equals' => ':attribute 必須是等於 :date 的日期。',
    'date_format' => ':屬性與格式 :format 不匹配。',
    'different' => ':attribute 和 :other 必須不同。',
    'digits' => ':attribute 必須是 :digits 數字',
    'digits_between' => ':attribute 必須介於 :min 和 :max 數字之間。',
    'dimensions' => ':attribute 具有無效的圖像尺寸。',
    'distinct' => ':attribute 字段有一個重複的值。',
    'email' => ':attribute 必須是一個有效的電子郵件地址。',
    'ends_with' => ':attribute 必須以 :values 之一結尾。',
    'exists' => '選擇的 :attribute 是無效的。',
    'file' => ':attribute 必須是一個文件。',
    'filled' => ':attribute 字段必須有一個值。',
    'gt' => [
        'numeric' => ':attribute 必須大於 :value。',
        'file' => ':attribute 必須大於 :value kb。',
        'string' => ':attribute 必須大於 :value 字符。',
        'array' => ':attribute 必須大於 :value 項。',
    ],
    'gte' => [
        'numeric' => ':attribute 必須大於或等於 :value。',
        'file' => ':attribute 必須大於或等於 :value kb。',
        'string' => ':attribute 必須大於或等於 :value 字符。',
        'array' => ':attribute 必須大於或等於 :value 值。',
    ],
    'image' => ':attribute 必須是圖像。',
    'in' => '所選的 :attribute 是無效的。',
    'in_array' => ':attribute 字段不存在於 :other 中。',
    'integer' => ':attribute 必須是一個整數。',
    'ip' => ':attribute 必須是一個有效的 IP 地址。',
    'ipv4' => ':attribute 必須是一個有效的 IPv4 地址。',
    'ipv6' => ':attribute 必須是一個有效的 IPv6 地址。',
    'json' => ':attribute 必須是一個有效的 JSON 字符串。',
    'lt' => [
        'numeric' => ':attribute 必須小於 :value。',
        'file' => ':attribute 必須小於 :value kb。',
        'string' => ':attribute 必須小於 :value 字符。',
        'array' => ':attribute 必須小於 :value 項。',
    ],
    'lte' => [
        'numeric' => ':attribute 必須小於或等於 :value.',
        'file' => ':attribute 必須小於或等於 :value kb。',
        'string' => ':attribute 必須小於或等於 :value 字符。',
        'array' => ':attribute 不能有超過 :value 項。',
    ],
    'max' => [
        'numeric' => ':attribute 不得大於 :max。',
        'file' => ':attribute 不得大於 :max kb。',
        'string' => ':attribute 不得大於 :max 字符數。',
        'array' => ':attribute 不得超過 :max 項。',
    ],
    'mimes' => ':attribute 的文件類型必須為： :values。',
    'mimetypes' => ':attribute 的文件類型必須為： :values。',
    'min' => [
        'numeric' => ':attribute 必須至少有 :min。',
        'file' => ':attribute 必須至少有 :min kb。',
        'string' => ':attribute 必須至少有 :min 字符。',
        'array' => ':attribute 必須至少有 :min 項。',
    ],
    'multiple_of' => ':attribute 必須是 :value 的倍數。',
    'not_in' => '所選的 :attribute 是無效的。',
    'not_regex' => ':attribute 格式無效。',
    'numeric' => ':attribute 必須是一個數字。',
    'password' => '該密碼不正確。',
    'present' => ':attribute 字段必須存在。',
    'regex' => ':attribute 格式無效。',
    'required' => ':attribute 字段是必需的。',
    'required_if' => '當 :other 是 :value 時，:attribute 字段是必需的。',
    'required_unless' => ':attribute 字段是必須的，除非 :other 是在 :values 之中。',
    'required_with' => '當 :values 存在時，:attribute 字段是必須的。',
    'required_with_all' => '當 :values 都存在時，:attribute 字段是必須的。',
    'required_without' => '當 :values 不存在時，:attribute 字段是必須的。',
    'required_without_all' => '當 :values 都不存在時，:attribute 字段是必須的。',
    'same' => ':attribute 和 :other 必須匹配。',
    'size' => [
        'numeric' => ':attribute 必須是 :size。',
        'file' => ':attribute 必須是 :size kb。',
        'string' => ':attribute 必須是 :size 字符。',
        'array' => ':attribute 必須包含 :size 項。',
    ],
    'starts_with' => ':attribute 必須以 :values 之一開頭。',
    'string' => ':attribute 必須是一個字符串。',
    'timezone' => ':attribute 必須是一個有效的區域。',
    'unique' => ':attribute 已經被佔用。',
    'uploaded' => ':attribute 上傳失敗。',
    'url' => ':attribute 格式無效。',
    'uuid' => ':attribute 必須是一個有效的 UUID。',

    /*
    |--------------------------------------------------------------------------
    | 自定義驗證語言行
    |--------------------------------------------------------------------------
    |
    | 在這裡，你可以使用 "attribute.rule "的約定來命名行，
    | 為屬性指定自定義的驗證信息。這樣可以快速為給定的屬性
    | 規則指定特定的自定義語言行。
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => '自定義消息',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 自定義驗證屬性
    |--------------------------------------------------------------------------
    |
    | 下面的語言行是用來把我們的屬性佔位符換成對讀者更友好的東西，
    | 如 "E-Mail Address "而不是 "email"。這只是幫助我們
    | 使我們的信息更具表現力。
    |
    */

    'attributes' => [],

];
