<?php

namespace App\Enums;

enum ServerVersion: string
{
    case v1_12 = '1.12';
    case v1_13 = '1.13';
    case v1_14 = '1.14';
    case v1_15 = '1.15';
    case v1_16 = '1.16';
    case v1_17 = '1.17';
    case v1_18 = '1.18';
    case v1_19 = '1.19';
    case v1_20 = '1.20';
    case v1_21 = '1.21';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
