<?php

namespace App\Support;

/**
 * Classifies where a visit came from, and parses the user agent into device / browser / OS.
 * Kept dependency-free so it runs on any host.
 */
class TrafficSource
{
    public static function classify(?string $referrer, array $utm, ?string $clickId, ?string $ownHost = null): string
    {
        $utmSource = strtolower((string) ($utm['utm_source'] ?? ''));
        $utmMedium = strtolower((string) ($utm['utm_medium'] ?? ''));
        $paid = in_array($utmMedium, ['cpc', 'ppc', 'paid', 'paid_social', 'paidsocial', 'ads'], true);

        if ($clickId === 'gclid' || ($paid && str_contains($utmSource, 'google'))) {
            return 'google_ads';
        }
        if ($clickId === 'fbclid' && $paid || ($paid && preg_match('/facebook|fb|instagram|ig|meta/', $utmSource))) {
            return 'meta_ads';
        }

        foreach (['facebook' => '/facebook|fb/', 'instagram' => '/instagram|ig/', 'whatsapp' => '/whatsapp|wa/', 'tiktok' => '/tiktok/', 'google' => '/google/', 'youtube' => '/youtube/', 'email' => '/mail|newsletter/'] as $source => $pattern) {
            if ($utmSource && preg_match($pattern, $utmSource)) {
                return $source;
            }
        }

        if ($clickId === 'fbclid') {
            return 'facebook';
        }

        $host = strtolower((string) parse_url((string) $referrer, PHP_URL_HOST));
        if (! $host || ($ownHost && str_ends_with($host, $ownHost))) {
            return 'direct';
        }

        return match (true) {
            (bool) preg_match('/(^|\.)(facebook\.com|fb\.com|fb\.me|messenger\.com)$/', $host) => 'facebook',
            (bool) preg_match('/instagram\.com$/', $host) => 'instagram',
            (bool) preg_match('/(whatsapp\.com|wa\.me)$/', $host) => 'whatsapp',
            (bool) preg_match('/tiktok\.com$/', $host) => 'tiktok',
            (bool) preg_match('/(youtube\.com|youtu\.be)$/', $host) => 'youtube',
            (bool) preg_match('/(^|\.)google\./', $host) => 'google',
            (bool) preg_match('/(bing\.com|yahoo\.|duckduckgo\.com|yandex\.)/', $host) => 'search',
            (bool) preg_match('/(mail\.|outlook\.)/', $host) => 'email',
            default => 'referral',
        };
    }

    /** @return array{device_type: string, browser: string, os: string} */
    public static function parseUserAgent(?string $ua): array
    {
        $ua = (string) $ua;

        $device = match (true) {
            (bool) preg_match('/iPad|Tablet|(Android(?!.*Mobile))/i', $ua) => 'tablet',
            (bool) preg_match('/Mobi|iPhone|Android/i', $ua) => 'mobile',
            default => 'desktop',
        };

        $os = match (true) {
            (bool) preg_match('/iPhone|iPad|iPod/i', $ua) => 'iOS',
            (bool) preg_match('/Android/i', $ua) => 'Android',
            (bool) preg_match('/Windows/i', $ua) => 'Windows',
            (bool) preg_match('/Mac OS X|Macintosh/i', $ua) => 'macOS',
            (bool) preg_match('/Linux/i', $ua) => 'Linux',
            default => 'Other',
        };

        $browser = match (true) {
            (bool) preg_match('/FBAN|FBAV|FB_IAB/i', $ua) => 'Facebook app',
            (bool) preg_match('/Instagram/i', $ua) => 'Instagram app',
            (bool) preg_match('/Edg\//i', $ua) => 'Edge',
            (bool) preg_match('/OPR\/|Opera/i', $ua) => 'Opera',
            (bool) preg_match('/SamsungBrowser/i', $ua) => 'Samsung Internet',
            (bool) preg_match('/Chrome|CriOS/i', $ua) => 'Chrome',
            (bool) preg_match('/Firefox|FxiOS/i', $ua) => 'Firefox',
            (bool) preg_match('/Safari/i', $ua) => 'Safari',
            default => 'Other',
        };

        return ['device_type' => $device, 'browser' => $browser, 'os' => $os];
    }

    public static function isBot(?string $ua): bool
    {
        return ! $ua || (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|curl|wget|python|headless|lighthouse|pingdom|uptime/i', $ua);
    }
}
