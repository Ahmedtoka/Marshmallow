@extends('layouts.site')

@section('content')
    @php $tinted = false; @endphp
    @foreach ($sections as $key => $section)
        @continue(! view()->exists('site.home.'.$key))
        <div data-track-section="{{ $key }}">
            @include('site.home.'.$key, ['section' => $section, 'tint' => $tinted])
        </div>
        @php
            // The hero and the closing call to action bring their own background, so they do not
            // take part in the white / blush alternation.
            if (! in_array($key, ['hero', 'visit_cta'], true)) {
                $tinted = ! $tinted;
            }
        @endphp
    @endforeach
@endsection
