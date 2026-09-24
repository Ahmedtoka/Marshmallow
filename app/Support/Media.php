<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores uploads on the configured media disk (public locally, S3-compatible in the cloud)
 * and downscales large photos so parents on mobile data are not served 8MB camera files.
 */
class Media
{
    public const MAX_EDGE = 1920;

    public static function disk(): string
    {
        return config('filesystems.media_disk', 'public');
    }

    /** @return array{path: string, width: ?int, height: ?int} */
    public static function store(UploadedFile $file, string $folder): array
    {
        $folder = 'uploads/'.trim($folder, '/');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $name = Str::random(24).'.'.$extension;
        $width = $height = null;

        $resized = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? self::downscale($file->getRealPath(), $extension) : null;

        if ($resized) {
            [$contents, $width, $height] = $resized;
            Storage::disk(self::disk())->put($folder.'/'.$name, $contents, 'public');
        } else {
            Storage::disk(self::disk())->putFileAs($folder, $file, $name, 'public');
            if ($size = @getimagesize($file->getRealPath())) {
                [$width, $height] = $size;
            }
        }

        return ['path' => $folder.'/'.$name, 'width' => $width, 'height' => $height];
    }

    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return Storage::disk(self::disk())->url($path);
    }

    /**
     * URL of a smaller copy of an uploaded photo, generated the first time it is asked for.
     * A wall of twenty photos would otherwise cost a parent on mobile data several megabytes.
     */
    public static function thumb(?string $path, int $width = 600): ?string
    {
        if (! $path || Str::startsWith($path, ['http://', 'https://', '/'])) {
            return self::url($path);
        }

        $disk = Storage::disk(self::disk());
        $thumb = 'thumbs/'.$width.'/'.$path;

        if ($disk->exists($thumb)) {
            return $disk->url($thumb);
        }

        if (! $disk->exists($path) || ! function_exists('imagecreatefromstring')) {
            return self::url($path);
        }

        $image = @imagecreatefromstring($disk->get($path));

        if (! $image) {
            return self::url($path);
        }

        $height = (int) round(imagesy($image) * ($width / imagesx($image)));

        if (imagesx($image) <= $width) {
            $disk->put($thumb, $disk->get($path), 'public');

            return $disk->url($thumb);
        }

        $canvas = imagecreatetruecolor($width, $height);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        // Progressive JPEG at a modest quality: these are grid tiles, not prints, and a parent on
        // mobile data pays for every kilobyte.
        imageinterlace($canvas, true);

        ob_start();
        imagejpeg($canvas, null, 68);
        $disk->put($thumb, (string) ob_get_clean(), 'public');

        return $disk->url($thumb);
    }

    public static function delete(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://', '/'])) {
            Storage::disk(self::disk())->delete($path);
        }
    }

    /** @return array{0: string, 1: int, 2: int}|null */
    private static function downscale(string $source, string $extension): ?array
    {
        if (! function_exists('imagecreatefromstring') || ! ($size = @getimagesize($source))) {
            return null;
        }

        [$width, $height] = $size;
        $image = @imagecreatefromstring((string) file_get_contents($source));
        if (! $image) {
            return null;
        }

        if (function_exists('exif_read_data') && in_array($extension, ['jpg', 'jpeg'], true)) {
            $orientation = @exif_read_data($source)['Orientation'] ?? 1;
            $image = match ($orientation) {
                3 => imagerotate($image, 180, 0),
                6 => imagerotate($image, -90, 0),
                8 => imagerotate($image, 90, 0),
                default => $image,
            };
            if (in_array($orientation, [6, 8], true)) {
                [$width, $height] = [$height, $width];
            }
        }

        $scale = min(1, self::MAX_EDGE / max($width, $height));
        $newWidth = (int) round($width * $scale);
        $newHeight = (int) round($height * $scale);

        if ($scale < 1) {
            $canvas = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $image = $canvas;
        }

        ob_start();
        match ($extension) {
            'png' => imagepng($image, null, 8),
            'webp' => imagewebp($image, null, 82),
            default => imagejpeg($image, null, 82),
        };

        return [(string) ob_get_clean(), $newWidth, $newHeight];
    }
}
