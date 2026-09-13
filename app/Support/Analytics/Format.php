<?php

namespace App\Support\Analytics;

/** Number formatting for the dashboards. PHP here has no intl, so everything uses number_format. */
final class Format
{
    public static function num(int|float|null $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals);
    }

    /** 75 → "1:15", 3725 → "1:02:05". */
    public static function duration(int|float|null $seconds): string
    {
        $seconds = (int) round((float) $seconds);
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
    }

    /** 75 → "1m 15s" – for sentences rather than table columns. */
    public static function humanDuration(int|float|null $seconds): string
    {
        $seconds = (int) round((float) $seconds);
        if ($seconds < 60) {
            return $seconds.'s';
        }
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        return $h > 0 ? $h.'h '.$m.'m' : $m.'m'.($s ? ' '.$s.'s' : '');
    }

    public static function pct(int|float|null $value, int $decimals = 1): string
    {
        return rtrim(rtrim(number_format((float) $value, $decimals), '0'), '.').'%';
    }

    /** Safe percentage of a part in a whole. */
    public static function ratio(int|float|null $part, int|float|null $whole): float
    {
        return $whole ? ((float) $part / (float) $whole) * 100 : 0.0;
    }
}
