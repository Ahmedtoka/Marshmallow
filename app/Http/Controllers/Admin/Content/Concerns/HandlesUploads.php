<?php

namespace App\Http\Controllers\Admin\Content\Concerns;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait HandlesUploads
{
    /**
     * Replace or remove an image column. Pairs with <x-admin.image name="...">, which sends
     * the file as {field} and a "Remove image" checkbox as remove_{field}.
     * When nothing changed the column is left out of $data so the current file is kept.
     */
    protected function applyImage(Request $request, array &$data, string $field, string $folder, ?Model $model = null): void
    {
        $current = $model?->getAttribute($field);
        unset($data[$field], $data['remove_'.$field]);

        if ($request->hasFile($field)) {
            $data[$field] = Media::store($request->file($field), $folder)['path'];
            Media::delete($current);
        } elseif ($request->boolean('remove_'.$field)) {
            $data[$field] = null;
            Media::delete($current);
        }
    }

    /** Slug from the given value, or from $source when empty, made unique within the model's table. */
    protected function uniqueSlug(string $modelClass, ?string $slug, string $source, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $source) ?: Str::lower(Str::random(8));
        $candidate = $base;
        $i = 2;

        while ($modelClass::query()
            ->where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $candidate = $base.'-'.$i++;
        }

        return $candidate;
    }

    /** Delete the files stored in the given image columns (used when a record is deleted). */
    protected function deleteImages(Model $model, array $fields): void
    {
        foreach ($fields as $field) {
            Media::delete($model->getAttribute($field));
        }
    }
}
