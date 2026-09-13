<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Camp;
use App\Models\Classroom;
use App\Models\GalleryAlbum;
use App\Models\Section;
use App\Models\Setting;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $contentUpdated = collect([
            Setting::max('updated_at'),
            Section::max('updated_at'),
        ])->filter()->max();

        $urls = collect([
            [route('home'), $contentUpdated, '1.0'],
            [route('classes.index'), Classroom::max('updated_at'), '0.9'],
            [route('enroll'), $contentUpdated, '0.9'],
            [route('activities.index'), Activity::max('updated_at'), '0.8'],
            [route('camps.index'), Camp::max('updated_at'), '0.8'],
            [route('safety'), $contentUpdated, '0.7'],
            [route('branches'), $contentUpdated, '0.8'],
            [route('about'), $contentUpdated, '0.6'],
            [route('gallery.index'), GalleryAlbum::max('updated_at'), '0.6'],
            [route('careers'), $contentUpdated, '0.4'],
        ]);

        Classroom::active()->get(['slug', 'updated_at'])->each(fn ($m) => $urls->push([route('classes.show', $m), $m->updated_at, '0.8']));
        Activity::active()->get(['slug', 'updated_at'])->each(fn ($m) => $urls->push([route('activities.show', $m), $m->updated_at, '0.6']));
        Camp::active()->get(['slug', 'updated_at'])->each(fn ($m) => $urls->push([route('camps.show', $m), $m->updated_at, '0.7']));
        GalleryAlbum::visible()->get(['slug', 'updated_at'])->each(fn ($m) => $urls->push([route('gallery.show', $m), $m->updated_at, '0.5']));

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as [$loc, $lastmod, $priority]) {
            $xml .= '  <url><loc>'.e($loc).'</loc>';
            if ($lastmod) {
                $xml .= '<lastmod>'.\Illuminate\Support\Carbon::parse($lastmod)->toAtomString().'</lastmod>';
            }
            $xml .= '<priority>'.$priority.'</priority></url>'."\n";
        }
        $xml .= '</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /t/',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ]);

        return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
