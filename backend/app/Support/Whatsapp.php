<?php

namespace App\Support;

class Whatsapp
{
    public const DEFAULT_TEMPLATE = 'Halo, saya {{nama}} ingin memesan paket {{paket}} dengan kode booking {{kode}}.';

    public static function normalize(?string $number): ?string
    {
        if ($number === null || $number === '') {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $number);
        if ($digits === '' || $digits === null) {
            return null;
        }
        if (str_starts_with($digits, '08')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }

        return $digits;
    }

    /** @param array{kode?:string,paket?:string,nama?:string} $data */
    public static function fillTemplate(?string $template, array $data): string
    {
        $t = $template ?: self::DEFAULT_TEMPLATE;

        return strtr($t, [
            '{{kode}}' => $data['kode'] ?? '',
            '{{paket}}' => $data['paket'] ?? '',
            '{{nama}}' => $data['nama'] ?? '',
        ]);
    }

    public static function url(string $normalizedNumber, string $message): string
    {
        return 'https://wa.me/'.$normalizedNumber.'?text='.rawurlencode($message);
    }
}
