<?php

namespace App\Http\Requests\Admin\Content;

use App\Models\GalleryAlbum;
use Illuminate\Validation\Rule;

class AlbumRequest extends ContentRequest
{
    protected array $booleans = ['is_visible'];

    protected array $images = ['cover_image'];

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'alpha_dash', 'max:255', Rule::unique('gallery_albums', 'slug')->ignore($this->route('album')?->id)],
            'category' => ['required', Rule::in(array_keys(GalleryAlbum::CATEGORIES))],
            'description' => ['nullable', 'string', 'max:5000'],
            'event_date' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'is_visible' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ] + $this->imageRules();
    }
}
