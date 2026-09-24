<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Faq;
use App\Models\Highlight;
use App\Models\Partner;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('site.pages.about', [
            'seoKey' => 'about',
            'why' => Highlight::group('why')->get(),
            'credentials' => Highlight::group('credentials')->get(),
            'partners' => Partner::visible()->get(),
            'testimonials' => Testimonial::visible()->get(),
            'branches' => Branch::active()->get(),
        ]);
    }

    public function safety(): View
    {
        $groups = Highlight::whereIn('group', array_keys(Highlight::GROUPS))
            ->where('group', '!=', 'why')
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        return view('site.pages.safety', [
            'seoKey' => 'safety',
            'groups' => $groups,
        ]);
    }

    public function branches(): View
    {
        return view('site.pages.branches', [
            'seoKey' => 'branches',
            'branches' => Branch::active()->get(),
        ]);
    }

    /**
     * Everything a parent needs before walking in: where we are, what keeps their child safe,
     * what they eat, how they get here, the questions everyone asks, and the booking form.
     */
    public function visit(): View
    {
        $order = ['safety', 'health', 'meals', 'logistics', 'services'];

        $groups = Highlight::whereIn('group', $order)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group')
            ->sortBy(fn ($items, $group) => array_search($group, $order, true));

        return view('site.pages.visit', [
            'seoKey' => 'visit',
            'branches' => Branch::active()->get(),
            'groups' => $groups,
            'faqs' => Faq::visible()->get(),
        ]);
    }
}
