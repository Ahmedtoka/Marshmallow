<?php

use App\Models\Setting;
use App\Support\Media;

if (! function_exists('setting')) {
    /** Read a site setting managed from Dashboard → Settings. */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('media_url')) {
    /** Public URL of an uploaded file, or null when nothing is uploaded yet. */
    function media_url(?string $path): ?string
    {
        return Media::url($path);
    }
}

if (! function_exists('tel_link')) {
    function tel_link(?string $phone): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', (string) $phone);
    }
}

if (! function_exists('whatsapp_link')) {
    /** Egyptian local numbers (01xxxxxxxxx) are converted to the international 201xxxxxxxxx form. */
    function whatsapp_link(?string $phone, ?string $text = null): string
    {
        $number = preg_replace('/\D/', '', (string) $phone);
        if (str_starts_with($number, '0')) {
            $number = '2'.$number;
        }

        return 'https://wa.me/'.$number.($text ? '?text='.rawurlencode($text) : '');
    }
}
