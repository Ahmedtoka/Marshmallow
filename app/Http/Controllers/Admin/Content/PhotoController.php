<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Support\Media;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PhotoController extends Controller
{
    /** Morph map keys that can own photos (see AppServiceProvider). */
    public const TYPES = ['classroom', 'activity', 'classroom_activity', 'camp', 'album'];

    public function store(Request $request)
    {
        $data = $request->validate([
            'photoable_type' => ['required', Rule::in(self::TYPES)],
            'photoable_id' => ['required', 'integer'],
            'files' => ['required', 'array', 'max:30'],
            'files.*' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'files.required' => 'Choose at least one photo.',
            'files.max' => 'You can upload up to 30 photos at a time.',
            'files.*.image' => 'Only photos can be uploaded (JPG, PNG or WebP).',
            'files.*.mimes' => 'Only JPG, PNG or WebP photos can be uploaded.',
            'files.*.max' => 'Each photo must be 10 MB or smaller.',
            'files.*.uploaded' => 'A photo failed to upload. It may be too large for the server.',
        ]);

        $type = $data['photoable_type'];
        $class = Relation::getMorphedModel($type);
        $owner = $class ? $class::query()->whereKey($data['photoable_id'])->first() : null;

        if (! $owner) {
            throw ValidationException::withMessages(['photoable_id' => 'The item these photos belong to no longer exists.']);
        }

        $max = Photo::query()->where('photoable_type', $type)->where('photoable_id', $owner->getKey())->max('sort_order');
        $order = $max === null ? 0 : $max + 1;

        $photos = [];
        foreach ($request->file('files') as $file) {
            $stored = Media::store($file, $type.'/'.$owner->getKey());
            $photos[] = Photo::create([
                'photoable_type' => $type,
                'photoable_id' => $owner->getKey(),
                'path' => $stored['path'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'sort_order' => $order++,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['photos' => array_map(fn (Photo $p) => $this->present($p), $photos)]);
        }

        return back()->with('success', count($photos) === 1 ? 'Photo uploaded.' : count($photos).' photos uploaded.');
    }

    public function update(Request $request, Photo $photo)
    {
        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        foreach (['caption', 'alt'] as $field) {
            if ($request->exists($field)) {
                $photo->{$field} = $data[$field] ?? null;
            }
        }

        if ($request->exists('is_featured')) {
            $featured = $request->boolean('is_featured');
            if ($featured) {
                Photo::query()
                    ->where('photoable_type', $photo->photoable_type)
                    ->where('photoable_id', $photo->photoable_id)
                    ->whereKeyNot($photo->id)
                    ->update(['is_featured' => false]);
            }
            $photo->is_featured = $featured;
        }

        $photo->save();

        return $request->expectsJson()
            ? response()->json(['photo' => $this->present($photo)])
            : back()->with('success', 'Photo saved.');
    }

    public function destroy(Request $request, Photo $photo)
    {
        $photo->delete(); // the model removes the file

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Photo deleted.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate(['ids' => ['required', 'array', 'max:1000'], 'ids.*' => ['integer']]);

        foreach (array_values($data['ids']) as $i => $id) {
            Photo::query()->whereKey($id)->update(['sort_order' => $i]);
        }

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Photo order saved.');
    }

    private function present(Photo $photo): array
    {
        return [
            'id' => $photo->id,
            'url' => $photo->url(),
            'caption' => $photo->caption,
            'alt' => $photo->alt,
            'is_featured' => (bool) $photo->is_featured,
        ];
    }
}
