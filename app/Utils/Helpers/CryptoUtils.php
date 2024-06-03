<?php

namespace App\Utils\Helpers;

class CryptoUtils
{
    public static function validateHmacSignature(string $data, string $signature, string $secret, $algo = 'sha256'): bool
    {
        return hash_equals(hash_hmac($algo, $data, $secret), $signature);
    }

    public static function generateHmacSignature(string $data, string $secret, $algo = 'sha256'): string
    {
        return hash_hmac($algo, $data, $secret);
    }
}
