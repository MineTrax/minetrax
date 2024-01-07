<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'この :attribute は許可されなければなりません。',
    'accepted_if' => 'この :attribute mは :other が :value の時許可されてなければいけません。',
    'active_url' => 'この :attribute は有効なURLではありません。',
    'after' => 'この :attribute は :date 以降の日付でなければなりません。',
    'after_or_equal' => 'この :attribute は :date　以降もしくは、同じ日付でなければなりません。',
    'alpha' => 'この :attribute はアルファベットのみ使用可能です。',
    'alpha_dash' => 'この :attribute はアルファベット、数字、ダッシュ、アンダースコア、のみ使用可能です。',
    'alpha_num' => 'この :attribute はアルファベット、数字のみ使用可能です。',
    'array' => 'この :attribute は配列でなければなりません。',
    'before' => 'この :attribute は :date 以前の日付でなければなりません。',
    'before_or_equal' => 'この :attribute は :date 以前もしくは、同じ日付でなければなりません。',
    'between' => [
        'array' => 'この :attribute は :min から :max までのアイテムでなければなりません。',
        'file' => 'この :attribute は :min から :max Kbでなければなりません。',
        'numeric' => 'この :attribute は :min から :max の間でなければなりません。',
        'string' => 'この :attribute は :min から :max までの文字数でなければなりません。',
    ],
    'boolean' => 'この :attribute 入力欄は true もしくは false でなければなりません。',
    'confirmed' => 'この :attribute の認証がマッチしません。',
    'current_password' => 'パスワードが違います。',
    'date' => 'この :attribute は有効な日付ではありません。',
    'date_equals' => 'この :attribute は :date とイコールな日付でなければなりません。',
    'date_format' => 'この :attribute は :format このフォーマットに合っていません。',
    'declined' => 'この :attribute 拒否しなければなりません。',
    'declined_if' => 'この :attribute mは :other が :value の時拒否されなければなりません。',
    'different' => 'この :attribute と :other は違わなければいけません。',
    'digits' => 'この :attribute は :digits digitsでなければなりません。',
    'digits_between' => 'この :attribute は :min から :max の間の digits でなければなりません。',
    'dimensions' => 'この :attribute has invalid image dimensions.',
    'distinct' => 'この :attribute field has a duplicate value.',
    'doesnt_start_with' => 'この :attribute は以下の物で開始されてはいけません。: :values.',
    'email' => 'この :attribute は有効なメールアドレスでなければいけません。',
    'ends_with' => 'この :attribute は以下のもので終わらなければいけません。: :values.',
    'enum' => '選択された :attribute は無効です。',
    'exists' => '選択された :attribute は無効です。',
    'file' => 'この :attribute はファイルでなければなりません。',
    'filled' => 'この :attribute 欄はvalueでなければなりません。',
    'gt' => [
        'array' => 'この :attribute は :value よりも多いitemsでなければなりません。',
        'file' => 'この :attribute は :value kilobytes以上でなければなりません。',
        'numeric' => 'この :attribute は :value 以上でなければなりません。',
        'string' => 'この :attribute は :value 文字以上でなければなりません。',
    ],
    'gte' => [
        'array' => 'この :attribute は :value もしくはそれ以上なければいけません。',
        'file' => 'この :attribute は :value kilobytes以上もしくは、同じでなければいけません。',
        'numeric' => 'この :attribute は :value と同じかそれ以上でなければいけません。',
        'string' => 'The :attribute must be greater than or equal to :value characters.',
    ],
    'image' => 'この :attribute は画像でなければなりません。.',
    'in' => '選択された :attribute は無効です。',
    'in_array' => 'この :attribute フィールドは :other 内に存在しません。',
    'integer' => 'この :attribute は integer でなければなりません。',
    'ip' => 'この :attribute は有効なIPアドレスでなければなりません。',
    'ipv4' => 'この :attribute は有効なIPv4アドレスでなければなりません。',
    'ipv6' => 'この :attribute は有効なIPv6アドレスでなければなりません。',
    'json' => 'この :attribute は有効なJSON stringでなければなりません。',
    'lt' => [
        'array' => 'この :attribute は :value 以下のitemsでなければなりません。',
        'file' => 'この :attribute は :value kilobytes以下でなければなりません。',
        'numeric' => 'この :attribute は :value 以下でなければなりません。',
        'string' => 'この :attribute は :value 文字以下でなければなりません。',
    ],
    'lte' => [
        'array' => 'この :attribute は :value 以下のitemsでなければなりません。',
        'file' => 'この :attribute は :value kilobytes以下もしくは同等でなければなりません。',
        'numeric' => 'この :attribute は :value 以下もしくは同等でなければなりません。',
        'string' => 'この :attribute は :value 文字以下でなければなりません。',
    ],
    'mac_address' => 'この :attribute は有効なMAC addressでなければなりません。',
    'max' => [
        'array' => 'この :attribute は :max 以下のitemsでなければなりません。',
        'file' => 'この :attribute は :max kilobytes以下でなければなりません。',
        'numeric' => 'この :attribute は :max 以下でなければなりません。',
        'string' => 'この :attribute は :max 以下の文字数でなければなりません。',
    ],
    'mimes' => 'この :attribute は以下のファイルタイプでなければなりません。: :values.',
    'mimetypes' => 'この :attribute は以下のファイルタイプでなければなりません。: :values.',
    'min' => [
        'array' => 'この :attribute は最低でも :min itemsでなければなりません。',
        'file' => 'この :attribute は最低でも :min kilobytesでなければなりません。',
        'numeric' => 'この :attribute は最低でも :min でなければなりません。',
        'string' => 'The :attribute は最低でも :min 文字数でなければなりません。',
    ],
    'multiple_of' => 'この :attribute は :value の倍でなければなりません。',
    'not_in' => '選択された :attribute は無効です',
    'not_regex' => 'この :attribute フォーマットは無効です。',
    'numeric' => 'この :attribute 文字数は無効です。',
    'password' => [
        'letters' => 'この :attribute は最低でも一文字アルファベットを使用してください。',
        'mixed' => 'この :attribute は大文字、小文字一文字ずつ使用してください。',
        'numbers' => 'この :attribute は最低でも一文字数字を使用してください。',
        'symbols' => 'この :attribute は最低でも一文字記号を使用してください。',
        'uncompromised' => '使用した :attribute はデータリークにて流出した過去があります。別の :attribute を使用してください。',
    ],
    'present' => 'この :attribute はなければいけません。',
    'prohibited' => 'この :attribute ',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'array' => 'The :attribute must contain :size items.',
        'file' => 'The :attribute must be :size kilobytes.',
        'numeric' => 'The :attribute must be :size.',
        'string' => 'The :attribute must be :size characters.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute must be a valid URL.',
    'uuid' => 'The :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'カスタムメッセージ',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
