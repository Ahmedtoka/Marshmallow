<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Camp;
use App\Models\Section;
use Illuminate\Contracts\View\View;

class CampController extends Controller
{
    public function index(): View
    {
        return view('site.camps.index', [
            'seoKey' => 'camps',
            'camps' => Camp::active()->with('photos')->get(),
            'section' => Section::for('camps'),
        ]);
    }

    public function show(Camp $camp): View
    {
        abort_unless($camp->is_active, 404);

        $camp->load('photos');

        return view('site.camps.show', [
            'seoKey' => 'camps',
            'camp' => $camp,
            'others' => Camp::active()->whereKeyNot($camp->id)->with('photos')->get(),
        ]);
    }
}
