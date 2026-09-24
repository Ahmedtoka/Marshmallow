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
 * Places the photos downloaded from the nursery's Facebook page onto the site.
 *
 *   python scripts/fetch-fb-photos.py "…/marshmallow-image-urls-*.csv"   (downloads + manifest)
 *   php artisan photos:import-facebook --dry-run                         (shows the plan)
 *   php artisan photos:import-facebook
 *
 * Each row of storage/client-photos/facebook/manifest.csv says where a photo belongs:
 *   album: <name>            → that gallery album
 *   an activity slug         → the activity's own page, or the activity inside a class when the
 *                              row names a class
 *   anything else (an event) → a gallery album named after the event
 *
 * Marketing posters are skipped, and photos already imported (matched on the Facebook id) are
 * left alone, so the command is safe to re-run as new batches arrive.
 */
class ImportFacebookPhotos extends Command
{
    protected $signature = 'photos:import-facebook
        {--manifest= : Path to manifest.csv (default: storage/client-photos/facebook/manifest.csv)}
        {--dry-run : Show what would be imported without touching the site}
        {--limit=30 : Keep at most this many photos per album or activity (0 = all). Parents scroll a few, not a hundred, and every photo ships with the repository.}
        {--min-width=800 : Skip images narrower than this}';

    protected $description = 'Import the photos downloaded from the Facebook page onto classes, activities and albums';

    /** Facebook album name (lowercase, letters and digits only) => album slug on the site. */
    private const ALBUMS = [
        'pyjamacampingpartybedtimestories' => 'pyjama-camping-party-bedtime-stories',
        'pyjamacampingparty5' => 'pyjama-camping-party-bedtime-stories',
        'pyjamapartycamping4' => 'pyjama-camping-party-bedtime-stories',
        'solarsystemcompetition' => 'solar-system-competition',
        '6thofoctoberwaroctoberbranch' => '6th-of-october-celebrations',
        '6thofoctoberwarhadayekalahrambranch' => '6th-of-october-celebrations',
        'graduation2026' => 'graduation-2026-under-the-sea',
        'summercamp2026' => 'summer-camp-2026',
        'triptodolphina2026' => 'trip-to-dolphina',
        'dolphinshowtrip2026' => 'trip-to-dolphina',
        'animalsvisit2025' => 'animals-visit',
        'marshmallowschoolsexpo2025' => 'marshmallow-schools-expo',
        'campus' => 'our-classrooms-garden',
        'reviews' => 'parents-reviews',
    ];

    /** Skipped: page branding, not gallery material. */
    private const SKIP_ALBUMS = ['coverphotos', 'profilepictures'];

    /** Word in the event name => album category. First match wins. */
    private const CATEGORY_HINTS = [
        'camp' => 'camps',
        'trip' => 'trips',
        'visit' => 'trips',
        'graduation' => 'graduation',
        'alumni' => 'graduation',
        'classroom' => 'campus',
        'branch' => 'campus',
        'review' => 'reviews',
    ];

    private array $stats = ['imported' => 0, 'skipped_existing' => 0, 'skipped_marketing' => 0, 'skipped_small' => 0, 'missing_file' => 0, 'unmapped' => 0];

    private array $plan = [];

    public function handle(): int
    {
        $manifest = $this->option('manifest') ?: storage_path('client-photos/facebook/manifest.csv');

        if (! is_file($manifest)) {
            $this->error('No manifest at '.$manifest.' — run scripts/fetch-fb-photos.py first.');

            return self::FAILURE;
        }

        $rows = $this->read($manifest);
        $this->line(count($rows).' rows in the manifest');

        $known = Photo::whereNotNull('source_ref')->pluck('source_ref')->flip();
        $dir = dirname($manifest);
        $counts = [];

        foreach ($rows as $row) {
            $ref = 'fb:'.$row['fbid'];

            if ($known->has($ref)) {
                $this->stats['skipped_existing']++;

                continue;
            }
            if (($row['is_marketing_frame'] ?? '') === 'true') {
                $this->stats['skipped_marketing']++;

                continue;
            }
            if ((int) ($row['width'] ?: 0) && (int) $row['width'] < (int) $this->option('min-width')) {
                $this->stats['skipped_small']++;

                continue;
            }

            $file = $dir.DIRECTORY_SEPARATOR.$row['file'];
            if (! is_file($file)) {
                $this->stats['missing_file']++;

                continue;
            }

            $owner = $this->owner($row);
            if (! $owner) {
                $this->stats['unmapped']++;
                $this->plan['(unmapped) '.($row['source_album_or_post'] ?: $row['event_or_activity'])][] = $row['fbid'];

                continue;
            }

            $label = $this->label($owner);
            $limit = (int) $this->option('limit');
            // Start from what the owner already has so the cap holds across runs, not per run.
            $counts[$label] = ($counts[$label] ?? $owner->photos()->count()) + 1;

            if ($limit && $counts[$label] > $limit) {
                continue;
            }

            $this->plan[$label][] = $row['fbid'];

            if (! $this->option('dry-run')) {
                $this->attach($owner, $file, $ref, $row);
            }

            $this->stats['imported']++;
        }

        $this->newLine();
        foreach ($this->plan as $label => $ids) {
            $this->line(sprintf('%-52s %d photos', $label, count($ids)));
        }

        $this->newLine();
        foreach ($this->stats as $key => $value) {
            $this->line(str_replace('_', ' ', $key).': '.$value);
        }

        if (! $this->option('dry-run')) {
            $this->covers();
            GalleryAlbum::doesntHave('photos')->update(['is_visible' => false]);
            GalleryAlbum::has('photos')->update(['is_visible' => true]);
            $this->info('Done. Regenerate the seeder so the server gets these too.');
        }

        return self::SUCCESS;
    }

    /** @return list<array<string, string>> */
    private function read(string $path): array
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

    private function owner(array $row): Classroom|ClassroomActivity|Activity|GalleryAlbum|null
    {
        $source = $row['source_album_or_post'] ?? '';
        $topic = trim($row['event_or_activity'] ?? '');
        $class = $this->key($row['class'] ?? '');

        // "album: Pyjama Camping Party [ 5 ]"
        if (Str::startsWith($source, 'album:')) {
            $name = $this->key(Str::after($source, 'album:'));

            if (in_array($name, self::SKIP_ALBUMS, true)) {
                return null;
            }

            if ($slug = self::ALBUMS[$name] ?? null) {
                return GalleryAlbum::where('slug', $slug)->first();
            }
        }

        if ($topic === '') {
            return null;
        }

        // An activity: on its own page, or inside a class when the row names one.
        if ($activity = Activity::where('slug', Str::slug($topic))->first()) {
            $classroom = $class ? Classroom::where('slug', Str::slug($class))->first() : null;

            if ($classroom) {
                $classroom->activities()->syncWithoutDetaching([
                    $activity->id => ['sort_order' => (int) $classroom->classroomActivities()->max('sort_order') + 1],
                ]);

                return ClassroomActivity::where('classroom_id', $classroom->id)->where('activity_id', $activity->id)->first();
            }

            return $activity;
        }

        if ($class && $topic === 'campus') {
            return Classroom::where('slug', Str::slug($class))->first();
        }

        // Anything else is an event: give it a gallery album.
        return $this->albumFor($topic);
    }

    private function albumFor(string $event): ?GalleryAlbum
    {
        if ($slug = self::ALBUMS[$this->key($event)] ?? null) {
            return GalleryAlbum::where('slug', $slug)->first();
        }

        $title = Str::of($event)->replace('_', ' ')->squish()->ucfirst()->toString();
        $slug = Str::slug($title);

        if ($slug === '') {
            return null;
        }

        $category = 'celebrations';
        foreach (self::CATEGORY_HINTS as $needle => $value) {
            if (Str::contains(Str::lower($title), $needle)) {
                $category = $value;

                break;
            }
        }

        return GalleryAlbum::firstOrCreate(['slug' => $slug], [
            'title' => $title,
            'category' => $category,
            'sort_order' => (int) GalleryAlbum::max('sort_order') + 1,
            'is_visible' => true,
        ]);
    }

    private function attach(mixed $owner, string $file, string $ref, array $row): void
    {
        $folder = $owner->getMorphClass().'/'.$owner->getKey();
        $stored = Media::store(new UploadedFile($file, basename($file), null, null, true), $folder);

        $owner->photos()->create([
            'path' => $stored['path'],
            'source_ref' => $ref,
            'alt' => $this->alt($owner),
            'width' => $stored['width'],
            'height' => $stored['height'],
            'sort_order' => (int) $owner->photos()->max('sort_order') + 1,
        ]);
    }

    private function label(mixed $owner): string
    {
        return match (true) {
            $owner instanceof GalleryAlbum => 'album: '.$owner->title,
            $owner instanceof Classroom => 'class: '.$owner->name,
            $owner instanceof Activity => 'activity: '.$owner->name,
            $owner instanceof ClassroomActivity => 'class activity: '.$owner->classroom->name.' / '.$owner->activity->name,
            default => 'other',
        };
    }

    private function alt(mixed $owner): string
    {
        return match (true) {
            $owner instanceof GalleryAlbum => $owner->title.' at Marshmallow Nursery',
            $owner instanceof Classroom => $owner->name.' class at Marshmallow Nursery',
            $owner instanceof Activity => $owner->name.' at Marshmallow Nursery',
            $owner instanceof ClassroomActivity => $owner->activity->name.' in the '.$owner->classroom->name.' class at Marshmallow Nursery',
            default => 'Marshmallow Nursery',
        };
    }

    private function covers(): void
    {
        foreach (GalleryAlbum::whereNull('cover_image')->has('photos')->get() as $album) {
            $album->update(['cover_image' => $album->photos()->first()->path]);
        }
        foreach (Activity::whereNull('cover_image')->has('photos')->get() as $activity) {
            $activity->update(['cover_image' => $activity->photos()->first()->path]);
        }
    }

    private function key(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower($value));
    }
}
