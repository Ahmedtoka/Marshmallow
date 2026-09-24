<?php

namespace App\Console\Commands;

use App\Models\Testimonial;
use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Imports the parent recommendations exported from the nursery's Facebook page.
 *
 *   php artisan reviews:import "C:/Users/AzzaB/Downloads/marshmallow-reviews-*.csv"
 *
 * Reviews are matched on their Facebook permalink, so re-running updates instead of duplicating.
 * Profile pictures are fetched once and stored with the site's other images; clearing a review's
 * photo in the dashboard falls back to the parent's initial, which is what we do if anyone objects.
 */
class ImportFacebookReviews extends Command
{
    protected $signature = 'reviews:import {csv* : One or more CSV files exported from the page}
        {--include-negative : Also import reviews marked as not recommending}
        {--skip-avatars : Do not download profile pictures}';

    protected $description = 'Import Facebook recommendations as testimonials';

    public function handle(): int
    {
        $imported = $updated = $skipped = $avatars = 0;

        foreach ($this->files() as $file) {
            $this->line('reading '.basename($file));

            foreach ($this->rows($file) as $row) {
                $name = trim($row['reviewer_name'] ?? '');
                $text = trim($row['review_text'] ?? '');
                $url = trim($row['review_permalink'] ?? '');

                if ($name === '' || $text === '') {
                    $skipped++;

                    continue;
                }

                if (! $this->option('include-negative') && Str::lower(trim($row['recommends'] ?? 'yes')) === 'no') {
                    $skipped++;

                    continue;
                }

                $existing = $url ? Testimonial::where('source_url', $url)->first() : Testimonial::where('parent_name', $name)->where('quote', $text)->first();

                $testimonial = $existing ?: new Testimonial([
                    'is_visible' => true,
                    'sort_order' => (int) Testimonial::max('sort_order') + 1,
                ]);

                $testimonial->fill([
                    'parent_name' => $name,
                    'quote' => $text,
                    'rating' => 5,
                    'source' => 'facebook',
                    'source_url' => $url ?: null,
                    'reviewed_at' => $this->date($row['review_date'] ?? null),
                ])->save();

                $existing ? $updated++ : $imported++;

                if (! $this->option('skip-avatars') && ! $testimonial->photo && filled($row['avatar_url'] ?? null)) {
                    if ($path = $this->avatar($row['avatar_url'], $name)) {
                        $testimonial->update(['photo' => $path]);
                        $avatars++;
                    }
                }
            }
        }

        $this->info("imported {$imported}, updated {$updated}, skipped {$skipped}, profile pictures saved {$avatars}");
        $this->line('total reviews on the site: '.Testimonial::count());

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function files(): array
    {
        $files = [];

        foreach ((array) $this->argument('csv') as $pattern) {
            $files = array_merge($files, glob($pattern) ?: (is_file($pattern) ? [$pattern] : []));
        }

        sort($files);

        return $files;
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

    private function date(?string $value): ?string
    {
        return $value && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($value)) ? trim($value) : null;
    }

    /** Facebook profile pictures are signed links that expire, so we keep our own copy. */
    private function avatar(string $url, string $name): ?string
    {
        $temp = tempnam(sys_get_temp_dir(), 'avatar');

        try {
            $context = stream_context_create(['http' => ['timeout' => 30, 'header' => "User-Agent: Mozilla/5.0\r\n"]]);
            $data = @file_get_contents($url, false, $context);

            if (! $data || strlen($data) < 512) {
                return null;
            }

            file_put_contents($temp, $data);

            return Media::store(new UploadedFile($temp, Str::slug($name).'.jpg', null, null, true), 'reviews')['path'];
        } catch (\Throwable $e) {
            $this->warn('avatar failed for '.$name.': '.$e->getMessage());

            return null;
        } finally {
            @unlink($temp);
        }
    }
}
