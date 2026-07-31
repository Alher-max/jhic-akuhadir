<?php

namespace App\Helpers;

class TimezoneHelper
{
    public static function format($timezone)
    {
        $map = [
            'Asia/Jakarta'   => 'WIB (UTC+7)',
            'Asia/Makassar'  => 'WITA (UTC+8)',
            'Asia/Jayapura'  => 'WIT (UTC+9)',
        ];

        return $map[$timezone] ?? $timezone;
    }

    public static function getAbbreviation($timezone)
    {
        $map = [
            'Asia/Jakarta'   => 'WIB',
            'Asia/Makassar'  => 'WITA',
            'Asia/Jayapura'  => 'WIT',
        ];

        return $map[$timezone] ?? 'WIB';
    }
}
