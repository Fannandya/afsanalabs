<?php

namespace App\Services;

use App\Models\Order;

class TrackingCodeService
{
    public const PREFIX = 'ORD-';

    public const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    public static function generate(int $length = 6, int $maxAttempts = 5): string
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = self::PREFIX.self::randomStringFromAlphabet($length);
            if (! Order::where('tracking_code', $code)->exists()) {
                return $code;
            }
        }

        abort(500, 'Gagal membuat kode pelacakan.');
    }

    protected static function randomStringFromAlphabet(int $length): string
    {
        $alphabet = self::ALPHABET;
        $max = strlen($alphabet) - 1;
        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }

        return $out;
    }
}
