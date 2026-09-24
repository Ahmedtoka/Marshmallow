<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Imports the partner school logos collected from the schools' own websites.
 *
 *   php artisan partners:import-logos "C:/Users/AzzaB/Downloads/marshmallow-school-logos.csv"
 *
 * Each logo is downloaded, trimmed of its empty border and scaled to one height, so a row of ten
 * logos from ten different designers still reads as one tidy strip. A logo that turns out to be
 * white (some schools only publish that version) is flagged to sit on a dark tile instead.
 */
class ImportPartnerLogos extends Command
{
    protected $signature = 'partners:import-logos {csv : CSV with partner_name, logo_url, official_website, facebook_page}
        {--height=160 : Height in pixels every logo is scaled to}';

    protected $description = 'Download, trim and attach the partner school logos';

    private const HEADERS = ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0 Safari/537.36'];

    public function handle(): int
    {
        $path = $this->argument('csv');

        if (! is_file($path)) {
            $this->error('No CSV at '.$path);

            return self::FAILURE;
        }

        foreach ($this->rows($path) as $row) {
            $name = trim($row['partner_name'] ?? '');
            $partner = Partner::where('name', $name)->first()
                ?? Partner::all()->first(fn (Partner $p) => Str::slug($p->name) === Str::slug($name));

            if (! $partner) {
                $this->warn('No partner called "'.$name.'" — skipped');

                continue;
            }

            $website = trim($row['official_website'] ?? '') ?: trim($row['facebook_page'] ?? '');
            $partner->fill(['website' => $website ?: null]);

            $image = $this->download(trim($row['logo_url'] ?? ''))
                ?? $this->download($this->facebookAvatar($row['facebook_page'] ?? ''));

            if (! $image) {
                $partner->save();
                $this->warn($partner->name.' — could not download a logo');

                continue;
            }

            [$file, $onDark] = $this->prepare($image);

            if (! $file) {
                $partner->save();
                $this->warn($partner->name.' — the file was not a usable image');

                continue;
            }

            Media::delete($partner->logo);
            $stored = Media::store(new UploadedFile($file, Str::slug($partner->name).'.png', null, null, true), 'partners');
            @unlink($file);

            $partner->fill(['logo' => $stored['path'], 'on_dark' => $onDark])->save();
            $this->line($partner->name.' — logo saved'.($onDark ? ' (white logo: shown on a dark tile)' : ''));
        }

        $this->info(Partner::whereNotNull('logo')->count().' of '.Partner::count().' partners now have a logo.');

        return self::SUCCESS;
    }

    /** @return list<array<string, string>> */
    private function rows(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = array_map(fn ($h) => trim($h, "\xEF\xBB\xBF \t"), fgetcsv($handle));
        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) === count($header)) {
                $rows[] = array_combine($header, $line);
            }
        }
        fclose($handle);

        return $rows;
    }

    private function facebookAvatar(string $page): string
    {
        $handle = trim(parse_url($page, PHP_URL_PATH) ?? '', '/');

        return $handle ? 'https://graph.facebook.com/'.$handle.'/picture?width=1000&height=1000' : '';
    }

    private function download(string $url): ?string
    {
        if (! Str::startsWith($url, 'http')) {
            return null;
        }

        $context = stream_context_create(['http' => ['timeout' => 45, 'header' => implode("\r\n", self::HEADERS), 'follow_location' => 1]]);
        $data = @file_get_contents($url, false, $context);

        return $data && strlen($data) > 1024 ? $data : null;
    }

    /**
     * Trim the empty border, scale to one height, and report whether the logo is white.
     *
     * @return array{0: ?string, 1: bool} temp file path and whether it needs a dark tile
     */
    private function prepare(string $data): array
    {
        $image = @imagecreatefromstring($data);

        if (! $image) {
            return [null, false];
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        [$left, $top, $right, $bottom, $light] = $this->bounds($image);
        $width = $right - $left + 1;
        $height = $bottom - $top + 1;

        if ($width < 8 || $height < 8) {
            [$left, $top, $width, $height] = [0, 0, imagesx($image), imagesy($image)];
        }

        $targetHeight = (int) $this->option('height');
        $targetWidth = max(1, (int) round($width * ($targetHeight / $height)));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $image, 0, 0, $left, $top, $targetWidth, $targetHeight, $width, $height);

        $file = tempnam(sys_get_temp_dir(), 'logo').'.png';
        imagepng($canvas, $file, 9);

        return [$file, $light];
    }

    /**
     * Bounding box of the pixels that are neither transparent nor the background colour,
     * plus whether what is left is almost white (invisible on a white tile).
     *
     * @return array{0: int, 1: int, 2: int, 3: int, 4: bool}
     */
    private function bounds($image): array
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $background = imagecolorat($image, 0, 0);
        $left = $w;
        $top = $h;
        $right = 0;
        $bottom = 0;
        $sum = 0;
        $count = 0;

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgba = imagecolorat($image, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;

                if ($alpha > 100 || $rgba === $background) {
                    continue;
                }

                $left = min($left, $x);
                $top = min($top, $y);
                $right = max($right, $x);
                $bottom = max($bottom, $y);

                $sum += (0.2126 * (($rgba >> 16) & 0xFF) + 0.7152 * (($rgba >> 8) & 0xFF) + 0.0722 * ($rgba & 0xFF)) / 255;
                $count++;
            }
        }

        $transparent = ((imagecolorat($image, 0, 0) >> 24) & 0x7F) > 100;

        return [$left, $top, $right, $bottom, $count > 0 && $transparent && ($sum / $count) > 0.8];
    }
}
