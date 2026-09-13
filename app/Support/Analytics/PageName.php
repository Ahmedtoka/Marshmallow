<?php

namespace App\Support\Analytics;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Turns tracked paths into names the owner recognises: "/classes/cupcake" → "Class: Cupcake". */
final class PageName
{
    private const STATIC = [
        '/' => 'Home',
        '/classes' => 'Classes',
        '/activities' => 'Activities',
        '/camps' => 'Camps',
        '/safety' => 'Safety & care',
        '/gallery' => 'Gallery',
        '/branches' => 'Branches',
        '/about' => 'About us',
        '/careers' => 'Careers',
        '/enroll' => 'Enrollment form',
        '/thank-you' => 'Thank-you page',
    ];

    private const DYNAMIC = [
        'classes' => ['Class', 'classrooms', 'name'],
        'activities' => ['Activity', 'activities', 'name'],
        'camps' => ['Camp', 'camps', 'title'],
        'gallery' => ['Album', 'gallery_albums', 'title'],
    ];

    /** @var array<string, array<string, string>> */
    private static array $lookups = [];

    public static function for(?string $path): string
    {
        $path = '/'.trim((string) $path, '/');

        if (isset(self::STATIC[$path])) {
            return self::STATIC[$path];
        }

        if (preg_match('#^/([a-z-]+)/([^/]+)$#', $path, $m) && isset(self::DYNAMIC[$m[1]])) {
            [$prefix, $table, $column] = self::DYNAMIC[$m[1]];
            $name = self::lookup($table, $column)[$m[2]] ?? Str::headline($m[2]);

            return $prefix.': '.$name;
        }

        return $path;
    }

    /** Homepage section key → readable name. */
    public static function section(?string $key): string
    {
        return Str::ucfirst(str_replace(['_', '-'], ' ', (string) $key));
    }

    private static function lookup(string $table, string $column): array
    {
        if (! isset(self::$lookups[$table])) {
            try {
                self::$lookups[$table] = DB::table($table)->pluck($column, 'slug')->all();
            } catch (\Throwable) {
                self::$lookups[$table] = [];
            }
        }

        return self::$lookups[$table];
    }
}
