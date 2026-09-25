<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Admin\Content\Concerns\HandlesUploads;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\MetaConversions;
use App\Support\Media;
use App\Support\SettingsSchema;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use HandlesUploads;

    public function edit(?string $group = 'general')
    {
        $group ??= 'general';
        abort_unless(SettingsSchema::has($group), 404);

        return view('admin.content.settings.edit', [
            'groups' => SettingsSchema::groups(),
            'group' => $group,
            'schema' => SettingsSchema::group($group),
            'values' => Setting::query()->pluck('value', 'key')->all(),
            // Health of the server-side Meta events, shown next to the pixel id.
            'capi' => $group === 'tracking' ? MetaConversions::summary() : null,
        ]);
    }

    public function update(Request $request, string $group)
    {
        abort_unless(SettingsSchema::has($group), 404);

        $request->validate(SettingsSchema::rules($group), [
            '*.image' => 'Please choose an image file (JPG, PNG or WebP).',
            'ga4_id.regex' => 'This doesn’t look like a GA4 measurement ID. It starts with G-, e.g. G-AB12CD34EF.',
            'meta_pixel_id.regex' => 'The Meta Pixel ID is a number, e.g. 123456789012345.',
            'tel.regex' => 'Use digits only (spaces, + and dashes are fine).',
        ], collect(SettingsSchema::fields($group))->pluck('label', 'key')->map(fn ($l) => mb_strtolower($l))->all());

        $current = Setting::query()->pluck('value', 'key')->all();
        $values = [];

        foreach (SettingsSchema::fields($group) as $field) {
            $key = $field['key'];

            switch ($field['type']) {
                case 'toggle':
                    $values[$key] = $request->boolean($key) ? '1' : '0';
                    break;

                case 'image':
                    if ($request->hasFile($key)) {
                        $values[$key] = Media::store($request->file($key), 'settings')['path'];
                        Media::delete($current[$key] ?? null);
                    } elseif ($request->boolean('remove_'.$key)) {
                        $values[$key] = '';
                        Media::delete($current[$key] ?? null);
                    }
                    break;

                default:
                    $value = trim((string) $request->input($key, ''));
                    if ($key === 'admission_years') {
                        $value = implode(',', SettingsSchema::splitYears($value));
                    }
                    $values[$key] = $value;
            }
        }

        Setting::put($values);

        return redirect()
            ->route('admin.content.settings.edit', $group)
            ->with('success', SettingsSchema::group($group)['label'].' settings saved.');
    }
}
