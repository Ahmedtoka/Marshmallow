<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Camp;
use App\Models\Classroom;
use App\Models\GalleryAlbum;
use App\Models\Photo;
use App\Models\Testimonial;
use App\Support\Media;
use Illuminate\Console\Command;

/**
 * Builds the small copies the site serves in grids and sliders.
 *
 *   php artisan photos:thumbs
 *
 * Run it locally after importing photos and commit public/media/thumbs: the server then serves
 * ready-made thumbnails and never has to resize anything itself.
 */
class GenerateThumbnails extends Command
{
    protected $signature = 'photos:thumbs {--widths=520 : Comma separated widths to build}';

    protected $description = 'Pre-build the thumbnails used by the photo grids and sliders';

    public function handle(): int
    {
        $widths = collect(explode(',', $this->option('widths')))->map(fn ($w) => (int) trim($w))->filter()->all();

        $paths = collect()
            ->concat(Photo::pluck('path'))
            ->concat(Classroom::pluck('cover_image'))
            ->concat(Activity::pluck('cover_image'))
            ->concat(Camp::pluck('cover_image'))
            ->concat(GalleryAlbum::pluck('cover_image'))
            ->filter()
            ->unique()
            ->values();

        $avatars = Testimonial::whereNotNull('photo')->pluck('photo')->unique()->values();

        $this->line($paths->count().' photos × '.count($widths).' sizes, plus '.$avatars->count().' profile pictures');
        $bar = $this->output->createProgressBar($paths->count() + $avatars->count());

        foreach ($paths as $path) {
            foreach ($widths as $width) {
                Media::thumb($path, $width);
            }
            $bar->advance();
        }

        foreach ($avatars as $path) {
            Media::thumb($path, 160);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Thumbnails ready in public/media/thumbs — commit them so the server serves them directly.');

        return self::SUCCESS;
    }
}
