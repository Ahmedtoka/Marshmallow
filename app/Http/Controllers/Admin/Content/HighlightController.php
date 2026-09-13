<?php

namespace App\Http\Controllers\Admin\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Content\HighlightRequest;
use App\Models\Highlight;
use Illuminate\Http\Request;

class HighlightController extends Controller
{
    /** Where each group appears on the website. */
    public const WHERE = [
        'why' => 'Homepage → “Why Marshmallow” cards.',
        'safety' => 'Safety page, and the safety strip on the homepage.',
        'health' => 'Safety page → health & hygiene, and the homepage safety strip.',
        'meals' => 'Safety page → meals, and the homepage safety strip.',
        'logistics' => 'Safety page → transport & communication.',
        'credentials' => 'Safety page → credentials & training (also on the About page).',
        'services' => 'Safety page → services & hours.',
    ];

    public function index(Request $request)
    {
        $group = array_key_exists((string) $request->query('group'), Highlight::GROUPS) ? $request->query('group') : 'why';

        return view('admin.content.highlights.index', [
            'group' => $group,
            'highlights' => Highlight::query()->where('group', $group)->orderBy('sort_order')->get(),
            'counts' => Highlight::query()->selectRaw('`group`, count(*) as total')->groupBy('group')->pluck('total', 'group'),
            'where' => self::WHERE,
        ]);
    }

    public function create(Request $request)
    {
        $group = array_key_exists((string) $request->query('group'), Highlight::GROUPS) ? $request->query('group') : 'why';

        return view('admin.content.highlights.form', [
            'highlight' => new Highlight([
                'group' => $group, 'icon' => 'star', 'color' => '#E8177F', 'is_visible' => true,
                'sort_order' => (int) Highlight::where('group', $group)->max('sort_order') + 1,
            ]),
            'where' => self::WHERE,
        ]);
    }

    public function store(HighlightRequest $request)
    {
        $highlight = Highlight::create($request->saveData());

        return redirect()->route('admin.content.highlights.index', ['group' => $highlight->group])->with('success', "Highlight “{$highlight->title}” saved.");
    }

    public function edit(Highlight $highlight)
    {
        return view('admin.content.highlights.form', ['highlight' => $highlight, 'where' => self::WHERE]);
    }

    public function update(HighlightRequest $request, Highlight $highlight)
    {
        $highlight->update($request->saveData());

        return redirect()->route('admin.content.highlights.index', ['group' => $highlight->group])->with('success', "Highlight “{$highlight->title}” saved.");
    }

    public function destroy(Highlight $highlight)
    {
        $group = $highlight->group;
        $highlight->delete();

        return redirect()->route('admin.content.highlights.index', ['group' => $group])->with('success', 'Highlight deleted.');
    }
}
