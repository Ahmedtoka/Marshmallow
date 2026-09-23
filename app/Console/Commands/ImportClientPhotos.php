<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Classroom;
use App\Models\ClassroomActivity;
use App\Models\GalleryAlbum;
use App\Models\Photo;
use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Imports the photo folders the nursery sends us into the site.
 *
 *   php artisan photos:import "public/Website"
 *
 * Folders are matched by name, so the nursery can keep sending the same structure:
 *   <Class name>/<Activity name>/*.jpg   photos of that activity inside that class
 *   <Class name>/*.jpg                   general photos of the class
 *   Gallery/<Album name>/*.jpg           gallery albums
 *   Parents Reviews/*.jpg                the parent review cards album
 *
 * Owners that already have photos are skipped, so the command is safe to re-run;
 * use --fresh to replace the photos of every folder being imported.
 */
class ImportClientPhotos extends Command
{
    protected $signature = 'photos:import {path : Folder to import} {--fresh : Replace existing photos of the folders being imported}';

    protected $description = 'Import class, activity and gallery photos from a folder of images';

    private const CLASSES = [
        'cupcakes' => 'cupcake',
        'cupcake' => 'cupcake',
        'popcorn' => 'popcorn',
        'candy' => 'candy',
        'icecream' => 'ice-cream',
        'lollipop' => 'lollipop',
        'cottoncandy' => 'cotton-candy',
    ];

    private const ACTIVITIES = [
        'arabic' => 'arabic',
        'booklet' => 'topic-booklet',
        'art' => 'art-craft',
        'craft' => 'art-craft',
        'artcraft' => 'art-craft',
        'english' => 'english-academic',
        'englishsession' => 'english-academic',
        'event' => 'events',
        'events' => 'events',
        'celebrationsevents' => 'events',
        'feelingmenners' => 'feelings',
        'feelingsmanners' => 'feelings',
        'finemotorskills' => 'motor-skills',
        'grossmotorskills' => 'motor-skills',
        'french' => 'french',
        'garden' => 'garden-time',
        'gymnastics' => 'gymnastics',
        'science' => 'science-experiments',
        'storytelling' => 'storytelling',
        'cooking' => 'cooking',
        'musicdrama' => 'music-dance',
        'musicdancing' => 'music-dance',
        'music' => 'music-dance',
        'trip' => 'monthly-trip',
        'trips' => 'monthly-trip',
    ];

    /** Gallery folder => existing album slug, or [title, category] for an album we still need. */
    private const ALBUMS = [
        '6thofoctcelebration' => '6th-of-october-celebrations',
        'graduation' => 'graduation-2026-under-the-sea',
        'solarsystem' => 'solar-system-competition',
        'summercamp' => 'summer-camp-2026',
        'triptodolphina' => 'trip-to-dolphina',
        'scienceday' => 'science-day',
        'animalsvisit' => ['Animals visit', 'trips'],
        'circus' => ['Circus day', 'trips'],
        'marshmallowexpo' => ['Marshmallow Schools Expo', 'celebrations'],
    ];

    private int $imported = 0;

    private array $unmatched = [];

    /** Owners already filled during this run, so two folders can feed one activity (Fine + Gross motor skills). */
    private array $touched = [];

    public function handle(): int
    {
        $root = realpath(base_path($this->argument('path'))) ?: realpath($this->argument('path'));

        if (! $root || ! is_dir($root)) {
            $this->error('Folder not found: '.$this->argument('path'));

            return self::FAILURE;
        }

        foreach ($this->directories($root) as $folder) {
            $key = $this->key($folder);
            $path = $root.DIRECTORY_SEPARATOR.$folder;

            match (true) {
                isset(self::CLASSES[$key]) => $this->importClass($path, self::CLASSES[$key]),
                $key === 'gallery' => $this->importGallery($path),
                $key === 'parentsreviews' => $this->importAlbum($path, 'parents-reviews', 'Parents reviews', 'reviews'),
                default => $this->unmatched[] = $folder,
            };
        }

        $this->newLine();
        $this->info($this->imported.' photos imported.');

        foreach ($this->unmatched as $folder) {
            $this->warn('Skipped (no match): '.$folder);
        }

        return self::SUCCESS;
    }

    private function importClass(string $path, string $slug): void
    {
        $classroom = Classroom::where('slug', $slug)->first();

        if (! $classroom) {
            $this->unmatched[] = $slug;

            return;
        }

        $this->line('<info>'.$classroom->name.'</info>');

        // Loose files directly inside the class folder become general photos of the class.
        $this->attach($classroom, $this->files($path), $classroom->name.' class at Marshmallow Nursery', '  Class photos');

        foreach ($this->directories($path) as $folder) {
            $activitySlug = self::ACTIVITIES[$this->key($folder)] ?? null;
            $activity = $activitySlug ? Activity::where('slug', $activitySlug)->first() : null;

            if (! $activity) {
                $this->unmatched[] = $classroom->name.' / '.$folder;

                continue;
            }

            // The nursery may send an activity we have not linked to this class yet.
            $classroom->activities()->syncWithoutDetaching([
                $activity->id => ['sort_order' => (int) $classroom->classroomActivities()->max('sort_order') + 1],
            ]);

            $pivot = ClassroomActivity::where('classroom_id', $classroom->id)
                ->where('activity_id', $activity->id)
                ->first();

            $this->attach(
                $pivot,
                $this->files($path.DIRECTORY_SEPARATOR.$folder),
                $activity->name.' in the '.$classroom->name.' class at Marshmallow Nursery',
                '  '.$activity->name
            );
        }

        if (! $classroom->cover_image) {
            $cover = $classroom->photos()->first()
                ?? Photo::where('photoable_type', 'classroom_activity')
                    ->whereIn('photoable_id', $classroom->classroomActivities()->pluck('id'))
                    ->orderBy('id')
                    ->first();

            if ($cover) {
                $classroom->update(['cover_image' => $cover->path]);
            }
        }
    }

    private function importGallery(string $path): void
    {
        foreach ($this->directories($path) as $folder) {
            $target = self::ALBUMS[$this->key($folder)] ?? null;

            if ($target === null) {
                $this->unmatched[] = 'Gallery / '.$folder;

                continue;
            }

            [$slug, $title, $category] = is_array($target)
                ? [Str::slug($target[0]), $target[0], $target[1]]
                : [$target, Str::headline(str_replace('-', ' ', $target)), 'activities'];

            $this->importAlbum($path.DIRECTORY_SEPARATOR.$folder, $slug, $title, $category);
        }
    }

    private function importAlbum(string $path, string $slug, string $title, string $category): void
    {
        $album = GalleryAlbum::firstOrCreate(['slug' => $slug], [
            'title' => $title,
            'category' => $category,
            'sort_order' => (int) GalleryAlbum::max('sort_order') + 1,
        ]);

        $this->attach($album, $this->files($path), $album->title.' at Marshmallow Nursery', $album->title);

        if (! $album->cover_image && $first = $album->photos()->first()) {
            $album->update(['cover_image' => $first->path]);
        }
    }

    /** @param  list<string>  $files */
    private function attach(mixed $owner, array $files, string $alt, ?string $label = null): void
    {
        if (! $owner || ! $files) {
            return;
        }

        $key = $owner->getMorphClass().':'.$owner->getKey();
        $appending = isset($this->touched[$key]);

        if (! $appending) {
            if ($this->option('fresh')) {
                $owner->photos()->get()->each->delete();
            } elseif ($owner->photos()->exists()) {
                $this->line(trim((string) $label).' - already has photos, skipped');

                return;
            }
        }

        $this->touched[$key] = true;
        $folder = $owner->getMorphClass().'/'.$owner->getKey();
        $sort = $appending ? (int) $owner->photos()->max('sort_order') + 1 : 0;

        foreach ($files as $file) {
            $stored = Media::store(new UploadedFile($file, basename($file), null, null, true), $folder);

            $owner->photos()->create([
                'path' => $stored['path'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'alt' => $alt,
                'sort_order' => $sort++,
            ]);

            $this->imported++;
        }

        $this->line($label.' - '.count($files).' photos');
    }

    /** @return list<string> */
    private function directories(string $path): array
    {
        $dirs = array_filter(scandir($path) ?: [], fn ($f) => ! str_starts_with($f, '.') && is_dir($path.DIRECTORY_SEPARATOR.$f));
        sort($dirs);

        return array_values($dirs);
    }

    /** @return list<string> full paths of image files, sorted by name */
    private function files(string $path): array
    {
        $files = array_filter(
            glob($path.DIRECTORY_SEPARATOR.'*') ?: [],
            fn ($f) => is_file($f) && in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true)
        );
        sort($files);

        return array_values($files);
    }

    private function key(string $name): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($name));
    }
}
